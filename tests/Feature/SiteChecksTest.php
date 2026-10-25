<?php

use Symfony\Component\Process\Process;

/*
 * Runs every script in tests/Site as its own PHP process. Each one boots the whole site against a throwaway in-memory
 * database and checks pages, forms, the admin, emails, images and translations the way a visitor or the admin would
 * meet them (see tests/Site/*.php for what each covers). A failing check prints "FAIL" lines.
 */
function runCheck(array $command): void
{
    $process = new Process($command, base_path(), null, null, 600);
    $process->run();

    $output = trim($process->getOutput() . $process->getErrorOutput());
    $failures = collect(explode("\n", $output))->filter(fn ($line) => str_contains($line, 'FAIL') || str_contains($line, 'Error'))->take(15)->implode("\n");

    expect($process->getExitCode())->toBe(0, $failures !== '' ? $failures : substr($output, -2000));
}

it('passes the site check :script', function (string $script) {
    runCheck([PHP_BINARY, base_path("tests/Site/$script"), base_path()]);
})->with(fn () => array_map('basename', glob(dirname(__DIR__) . '/Site/*.php')));

it('passes the JavaScript logic checks (AI job polling and the SEO checklist)', function () {
    $node = new Process(['node', '--version']);
    $node->run();
    if (! $node->isSuccessful()) {
        $this->markTestSkipped('node is not installed');
    }

    runCheck(['node', base_path('tests/Site/seo-and-jobs.mjs')]);
});
