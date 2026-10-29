<?php

use App\Services\WeeklyReport;
use Illuminate\Support\Facades\File;

/* Everything happens in a temporary folder, never in the real storage/app/backups. */
beforeEach(function () {
    $this->storage = sys_get_temp_dir() . '/safety-test-' . uniqid();
    File::ensureDirectoryExists($this->storage . '/app/backups');
    app()->useStoragePath($this->storage);
});

afterEach(fn () => File::deleteDirectory($this->storage));

it('says there is no copy yet, on the server or on your computer', function () {
    $text = implode("\n", WeeklyReport::safetyCopies());

    expect($text)->toContain('SAFETY COPIES')
        ->toContain('no database copy yet')
        ->toContain('no copy yet')
        ->toContain('scripts/pull-backups.sh');
});

it('reports a recent database copy and a recent copy on your computer without nagging', function () {
    File::put($this->storage . '/app/backups/database-20261029-031500.sql.gz', 'x');
    File::put($this->storage . '/app/backups/.last-offsite-pull', (string) now()->subDays(2)->timestamp);

    $text = implode("\n", WeeklyReport::safetyCopies());

    expect($text)->toContain('the last database copy was made')
        ->toContain('On your computer: copied 2 days ago')
        ->not->toContain('Run scripts/pull-backups.sh');
});

it('asks you to copy again when the last copy to your computer is more than ten days old', function () {
    File::put($this->storage . '/app/backups/.last-offsite-pull', (string) now()->subDays(25)->timestamp);

    $text = implode("\n", WeeklyReport::safetyCopies());

    expect($text)->toContain('the last copy was 3 weeks ago')->toContain('Run scripts/pull-backups.sh');
});

it('is part of the weekly report', function () {
    expect(implode("\n", WeeklyReport::build()['lines']))->toContain('SAFETY COPIES:');
});
