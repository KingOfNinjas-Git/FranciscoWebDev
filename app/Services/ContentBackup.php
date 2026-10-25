<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Builds a backup of everything you create in the admin: projects, project images, blog posts (as an SQL file
 * that phpMyAdmin can import) plus the uploaded pictures. Admin settings (two-factor secret), AI logs and
 * sessions are deliberately left out.
 */
class ContentBackup
{
    private const TABLES = ['projects', 'project_images', 'posts'];

    public function zipAvailable(): bool
    {
        return class_exists(\ZipArchive::class);
    }

    public function sql(): string
    {
        $pdo = DB::getPdo();
        $out = "-- Backup of franciscowebdev.com content, " . now()->toDateTimeString() . "\n"
            . "-- Import with phpMyAdmin (database > Import). Existing rows with the same id are replaced.\n\n"
            . "SET NAMES utf8mb4;\n\n";

        foreach (self::TABLES as $table) {
            try {
                $rows = DB::table($table)->orderBy($table === 'project_images' ? 'id' : 'id')->get();
            } catch (\Throwable $e) {
                continue; // table missing (e.g. blog tables not created yet)
            }

            $out .= "-- {$table}: " . count($rows) . " row(s)\n";
            foreach ($rows as $row) {
                $row = (array) $row;
                $columns = implode(', ', array_map(fn ($c) => "`{$c}`", array_keys($row)));
                $values = implode(', ', array_map(fn ($v) => $v === null ? 'NULL' : (is_int($v) || is_float($v) ? (string) $v : $pdo->quote((string) $v)), $row));
                $out .= "REPLACE INTO `{$table}` ({$columns}) VALUES ({$values});\n";
            }
            $out .= "\n";
        }

        return $out;
    }

    /**
     * Writes the backup ZIP and returns its path (the caller deletes it after sending).
     */
    public function zip(): string
    {
        @set_time_limit(300);

        $path = storage_path('app/backup-' . bin2hex(random_bytes(6)) . '.zip');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create the backup file.');
        }

        $zip->addFromString('content.sql', $this->sql());
        $zip->addFromString('README.txt', $this->readme());

        $images = public_path('imgs');
        if (is_dir($images)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($images, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $zip->addFile($file->getPathname(), 'public/imgs/' . str_replace('\\', '/', substr($file->getPathname(), strlen($images) + 1)));
                }
            }
        }

        $zip->close();

        return $path;
    }

    private function readme(): string
    {
        return "How to restore this backup\n"
            . "==========================\n\n"
            . "1. Database: in hPanel open phpMyAdmin, choose your database, click Import and select content.sql.\n"
            . "   (The tables must already exist: they do on a working site. Rows with the same id are replaced.)\n"
            . "2. Pictures: upload the 'imgs' folder inside 'public' to your site's public/imgs folder, replacing files.\n\n"
            . "Not included on purpose: admin password, two-factor secret, AI logs, sessions. Those stay in the server's .env and database.\n";
    }
}
