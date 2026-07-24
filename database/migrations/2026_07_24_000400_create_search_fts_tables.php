<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Content tables that are searched by the global search pages and the
     * columns each search inspects. FTS tables mirror exactly these columns.
     */
    private const TABLES = [
        'users_fts' => ['table' => 'users', 'columns' => ['name', 'email']],
        'courses_fts' => ['table' => 'courses', 'columns' => ['title', 'code', 'description']],
        'assignments_fts' => ['table' => 'assignments', 'columns' => ['name', 'description']],
        'assessments_fts' => ['table' => 'assessments', 'columns' => ['name', 'description', 'score']],
        'materials_fts' => ['table' => 'learning_materials', 'columns' => ['title', 'file_name', 'material_type']],
    ];

    /**
     * Run the migrations.
     *
     * Guarded to SQLite: FTS5 virtual tables are a SQLite feature here and
     * are only created on sqlite connections. Other drivers skip this
     * migration entirely; the search trait falls back to LIKE queries, so
     * search keeps working everywhere.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $hasFts5 = (bool) (DB::selectOne("SELECT sqlite_compileoption_used('ENABLE_FTS5') AS enabled")?->enabled ?? false);

        if (! $hasFts5) {
            return;
        }

        foreach (self::TABLES as $ftsTable => $config) {
            $baseTable = $config['table'];
            $columns = $config['columns'];
            $columnList = implode(', ', $columns);
            $newValues = implode(', ', array_map(fn (string $column): string => 'new.'.$column, $columns));
            $oldValues = implode(', ', array_map(fn (string $column): string => 'old.'.$column, $columns));

            DB::unprepared(
                "CREATE VIRTUAL TABLE {$ftsTable} USING fts5({$columnList}, content='{$baseTable}', content_rowid='id')"
            );

            DB::unprepared(
                "CREATE TRIGGER {$ftsTable}_ai AFTER INSERT ON {$baseTable} BEGIN
                    INSERT INTO {$ftsTable}(rowid, {$columnList}) VALUES (new.id, {$newValues});
                END"
            );

            DB::unprepared(
                "CREATE TRIGGER {$ftsTable}_ad AFTER DELETE ON {$baseTable} BEGIN
                    INSERT INTO {$ftsTable}({$ftsTable}, rowid, {$columnList}) VALUES ('delete', old.id, {$oldValues});
                END"
            );

            DB::unprepared(
                "CREATE TRIGGER {$ftsTable}_au AFTER UPDATE ON {$baseTable} BEGIN
                    INSERT INTO {$ftsTable}({$ftsTable}, rowid, {$columnList}) VALUES ('delete', old.id, {$oldValues});
                    INSERT INTO {$ftsTable}(rowid, {$columnList}) VALUES (new.id, {$newValues});
                END"
            );

            DB::unprepared("INSERT INTO {$ftsTable}({$ftsTable}) VALUES ('rebuild')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        foreach (self::TABLES as $ftsTable => $config) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$ftsTable}_ai");
            DB::unprepared("DROP TRIGGER IF EXISTS {$ftsTable}_ad");
            DB::unprepared("DROP TRIGGER IF EXISTS {$ftsTable}_au");
            DB::unprepared("DROP TABLE IF EXISTS {$ftsTable}");
        }
    }
};
