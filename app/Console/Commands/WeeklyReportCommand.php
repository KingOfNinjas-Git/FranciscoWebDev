<?php

namespace App\Console\Commands;

use App\Services\WeeklyReport;
use Illuminate\Console\Command;

class WeeklyReportCommand extends Command
{
    protected $signature = 'report:weekly {--print : Show the report here instead of emailing it}';

    protected $description = 'Emails you the weekly report: visits, where they came from, briefs waiting, posts published';

    public function handle(): int
    {
        if ($this->option('print')) {
            $report = WeeklyReport::build();
            $this->line($report['subject']);
            $this->line('');
            $this->line(implode("\n", $report['lines']));

            return self::SUCCESS;
        }

        return WeeklyReport::send() ? self::SUCCESS : self::FAILURE;
    }
}
