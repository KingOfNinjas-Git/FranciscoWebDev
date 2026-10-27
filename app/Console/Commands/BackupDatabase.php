<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Writes the whole database to a compressed SQL file in storage/app/backups (outside the web root) and
 * keeps the newest copies. The host blocks mysqldump from PHP, so the dump is made through the database
 * connection. Pictures are files, not database rows, and are not included.
 */
class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--keep=14 : How many backups to keep}';

    protected $description = 'Saves a compressed copy of the database to storage/app/backups and removes the oldest ones';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        try {
            $file = $dir . '/database-' . now()->format('Ymd-His') . '.sql.gz';
            File::put($file, gzencode($this->dump(), 9));
            @chmod($file, 0600);
        } catch (\Throwable $e) {
            Log::error('Database backup failed: ' . $e->getMessage());
            $this->error('Backup failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $old = collect(File::glob($dir . '/database-*.sql.gz'))->sortDesc()->slice(max(1, (int) $this->option('keep')));
        $old->each(fn ($path) => File::delete($path));

        $this->info('Saved ' . basename($file) . ' (' . round(filesize($file) / 1024) . ' KB)' . ($old->isNotEmpty() ? ', removed ' . $old->count() . ' old' : ''));

        return self::SUCCESS;
    }

    /** The structure and the rows of every table, as SQL that can be fed back to the database. */
    private function dump(): string
    {
        $pdo = DB::connection()->getPdo();
        $mysql = DB::connection()->getDriverName() === 'mysql';
        $out = ['-- Backup of ' . config('app.name') . ' made ' . now()->toDateTimeString(), $mysql ? 'SET FOREIGN_KEY_CHECKS=0;' : 'PRAGMA foreign_keys=OFF;', ''];

        $tables = $mysql
            ? array_map(fn ($row) => array_values((array) $row)[0], DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"'))
            : array_column(DB::select("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"), 'name');

        foreach ($tables as $table) {
            $quoted = $mysql ? '`' . str_replace('`', '``', $table) . '`' : '"' . $table . '"';
            $create = $mysql
                ? (array) DB::selectOne('SHOW CREATE TABLE ' . $quoted)
                : (array) DB::selectOne("SELECT sql FROM sqlite_master WHERE name = ?", [$table]);

            $out[] = 'DROP TABLE IF EXISTS ' . $quoted . ';';
            $out[] = ($mysql ? $create['Create Table'] : $create['sql']) . ';';

            foreach (DB::table($table)->cursor() as $row) {
                $values = array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), (array) $row);
                $out[] = 'INSERT INTO ' . $quoted . ' VALUES (' . implode(', ', $values) . ');';
            }
            $out[] = '';
        }

        return implode("\n", $out) . "\n";
    }
}
