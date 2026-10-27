<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/* The backups go to a temporary folder (never the real storage/app/backups) and the database is the in-memory test one. */
beforeEach(function () {
    $this->storage = sys_get_temp_dir() . '/backup-test-' . uniqid();
    File::ensureDirectoryExists($this->storage);
    app()->useStoragePath($this->storage);
});

afterEach(fn () => File::deleteDirectory($this->storage));

it('saves a compressed copy of the database that can be restored', function () {
    Schema::create('notes', function ($table) {
        $table->id();
        $table->string('body')->nullable();
    });
    DB::table('notes')->insert([['body' => "O'Brien says \"hi\""], ['body' => null]]);

    $this->artisan('backup:database')->assertSuccessful();

    $files = glob($this->storage . '/app/backups/database-*.sql.gz');
    expect($files)->toHaveCount(1);

    $sql = gzdecode(file_get_contents($files[0]));
    expect($sql)->toContain('"notes"')->toContain("'O''Brien says \"hi\"'")->toContain('NULL');

    DB::statement('DROP TABLE notes');
    DB::connection()->getPdo()->exec($sql);
    expect(DB::table('notes')->count())->toBe(2)
        ->and(DB::table('notes')->where('id', 1)->value('body'))->toBe("O'Brien says \"hi\"")
        ->and(DB::table('notes')->where('id', 2)->value('body'))->toBeNull();
});

it('keeps only the newest backups', function () {
    $dir = $this->storage . '/app/backups';
    File::ensureDirectoryExists($dir);
    foreach (['20200101-000000', '20200102-000000', '20200103-000000'] as $stamp) {
        file_put_contents("$dir/database-$stamp.sql.gz", 'old');
    }

    $this->artisan('backup:database', ['--keep' => 2])->assertSuccessful();

    $left = array_map('basename', glob("$dir/database-*.sql.gz"));
    expect($left)->toHaveCount(2)->and($left)->not->toContain('database-20200101-000000.sql.gz');
});
