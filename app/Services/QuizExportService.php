<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizExportService
{
    /**
     * Export a collection of quizzes to JSON format compatible with ImportQuizzesAction.
     */
    public function exportToJson(Collection $quizzes): string
    {
        $payload = $quizzes->map(function (Quiz $quiz): array {
            return [
                'course_code' => (string) ($quiz->course?->code ?? ''),
                'course_title' => (string) ($quiz->course?->title ?? ''),
                'title' => (string) $quiz->title,
                'description' => $quiz->description,
                'publish_at' => $quiz->publish_at?->format('Y-m-d H:i:s'),
                'time_limit_minutes' => $quiz->time_limit_minutes,
                'shuffle_questions' => (bool) $quiz->shuffle_questions,
                'show_results' => (bool) $quiz->show_results,
                'pass_percentage' => (int) $quiz->pass_percentage,
                'is_active' => (bool) $quiz->is_active,
                'questions' => $quiz->questions->map(function (QuizQuestion $question): array {
                    $item = [
                        'type' => (string) $question->type,
                        'question' => (string) $question->question,
                        'explanation' => $question->explanation,
                        'points' => (int) $question->points,
                        'sort_order' => (int) $question->sort_order,
                    ];

                    if ($question->isMultipleChoice()) {
                        $item['options'] = $question->options->map(function (QuizOption $option): array {
                            return [
                                'option_text' => (string) $option->option_text,
                                'is_correct' => (bool) $option->is_correct,
                                'sort_order' => (int) $option->sort_order,
                            ];
                        })->values()->all();
                    }

                    return $item;
                })->values()->all(),
            ];
        })->values()->all();

        return (string) json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Export a collection of quizzes to spreadsheet-friendly CSV format (with UTF-8 BOM).
     */
    public function exportToCsv(Collection $quizzes): string
    {
        $handle = fopen('php://temp', 'r+');

        // UTF-8 BOM for Microsoft Excel compatibility
        fwrite($handle, "\xEF\xBB\xBF");

        $headers = [
            'Quiz ID',
            'Quiz Title',
            'Course Code',
            'Course Title',
            'Active',
            'Publish At',
            'Time Limit (Mins)',
            'Pass %',
            'Shuffle Questions',
            'Show Results',
            'Question #',
            'Question Type',
            'Question Text',
            'Points',
            'Explanation',
            'Correct Answer(s)',
            'All Options',
            'Option 1',
            'Option 2',
            'Option 3',
            'Option 4',
            'Option 5',
            'Option 6',
        ];

        fputcsv($handle, $headers);

        foreach ($quizzes as $quiz) {
            $baseRow = [
                $quiz->id,
                $quiz->title,
                $quiz->course?->code ?? '',
                $quiz->course?->title ?? '',
                $quiz->is_active ? 'Yes' : 'No',
                $quiz->publish_at?->format('Y-m-d H:i:s') ?? 'Immediate',
                $quiz->time_limit_minutes ?? 'No limit',
                $quiz->pass_percentage . '%',
                $quiz->shuffle_questions ? 'Yes' : 'No',
                $quiz->show_results ? 'Yes' : 'No',
            ];

            if ($quiz->questions->isEmpty()) {
                fputcsv($handle, array_merge($baseRow, [
                    '', '', 'No questions configured', '', '', '', '', '', '', '', '', '', '',
                ]));
                continue;
            }

            foreach ($quiz->questions as $index => $question) {
                $options = $question->options;
                $correctList = [];
                $allOptionsList = [];
                $optionSlots = ['', '', '', '', '', ''];

                if ($question->isMultipleChoice()) {
                    foreach ($options->values() as $optIndex => $opt) {
                        $prefix = chr(65 + $optIndex); // A, B, C, D...
                        $marker = $opt->is_correct ? ' (CORRECT)' : '';
                        $allOptionsList[] = "{$prefix}) {$opt->option_text}{$marker}";

                        if ($opt->is_correct) {
                            $correctList[] = "{$prefix}) {$opt->option_text}";
                        }

                        if ($optIndex < 6) {
                            $optionSlots[$optIndex] = "{$prefix}) {$opt->option_text}" . ($opt->is_correct ? ' [CORRECT]' : '');
                        }
                    }
                }

                $questionRow = [
                    $index + 1,
                    $question->type,
                    $question->question,
                    $question->points,
                    $question->explanation ?? '',
                    implode(' | ', $correctList),
                    implode(' ; ', $allOptionsList),
                    $optionSlots[0],
                    $optionSlots[1],
                    $optionSlots[2],
                    $optionSlots[3],
                    $optionSlots[4],
                    $optionSlots[5],
                ];

                fputcsv($handle, array_merge($baseRow, $questionRow));
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv !== false ? $csv : '';
    }

    /**
     * Build a StreamedResponse for downloading exported quizzes in the requested format.
     */
    public function downloadResponse(
        Collection $quizzes,
        string $format = 'json',
        ?string $customFilename = null
    ): StreamedResponse {
        if ($quizzes instanceof \Illuminate\Database\Eloquent\Collection) {
            $quizzes->loadMissing(['course', 'questions.options']);
        } else {
            foreach ($quizzes as $quiz) {
                if ($quiz instanceof \Illuminate\Database\Eloquent\Model) {
                    $quiz->loadMissing(['course', 'questions.options']);
                }
            }
        }

        $normalizedFormat = strtolower($format) === 'csv' ? 'csv' : 'json';
        $filename = $customFilename ?: 'Quizzes_Export_' . date('Ymd_His') . '.' . $normalizedFormat;

        if ($normalizedFormat === 'csv') {
            $content = $this->exportToCsv($quizzes);

            return response()->streamDownload(
                static fn () => print($content),
                $filename,
                [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                ]
            );
        }

        $content = $this->exportToJson($quizzes);

        return response()->streamDownload(
            static fn () => print($content),
            $filename,
            [
                'Content-Type' => 'application/json',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]
        );
    }
}
