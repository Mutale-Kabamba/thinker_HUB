<?php

namespace App\Services;

use App\Models\User;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsExportService
{
    public function __construct(private readonly RegionalAnalyticsService $analyticsService)
    {
    }

    /**
     * Export any analytics dataset as CSV or native XLSX.
     *
     * @param  array<string, mixed>  $filters
     */
    public function download(string $dataset, string $format = 'csv', array $filters = [], ?User $actingUser = null): StreamedResponse|BinaryFileResponse
    {
        $payload = $this->analyticsService->datasetForExport($dataset, $filters, $actingUser);
        $safeDataset = preg_replace('/[^a-z0-9_\-]/i', '_', $dataset) ?: 'report';
        $dateStamp = now()->format('Y-m-d_His');

        if (strtolower($format) === 'xlsx') {
            return $this->downloadXlsx($payload['headers'], $payload['rows'], "thinkerhub_{$safeDataset}_{$dateStamp}.xlsx");
        }

        return $this->downloadCsv($payload['headers'], $payload['rows'], "thinkerhub_{$safeDataset}_{$dateStamp}.csv");
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<scalar|null>>  $rows
     */
    private function downloadCsv(array $headers, array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, array_map(fn ($v) => $v === null ? '' : (string) $v, $row));
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  list<string>  $headers
     * @param  list<list<scalar|null>>  $rows
     */
    private function downloadXlsx(array $headers, array $rows, string $filename): BinaryFileResponse
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'thub_xlsx_') . '.xlsx';

        $writer = new XlsxWriter;
        $writer->openToFile($tempPath);
        $writer->addRow(Row::fromValues($headers));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues(array_map(fn ($v) => $v === null ? '' : (string) $v, $row)));
        }

        $writer->close();

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
