<?php

namespace App\Console\Commands;

use App\Models\Lead;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeadsRemind extends Command
{
    protected $signature = 'leads:remind {--hours=48 : How long a lead may wait before it is mentioned}';

    protected $description = 'Emails you about leads nobody has answered yet (each lead is mentioned once)';

    public function handle(): int
    {
        $waiting = Lead::where('status', 'new')
            ->whereNull('reminded_at')
            ->where('created_at', '<=', now()->subHours((int) $this->option('hours')))
            ->orderBy('created_at')
            ->get();

        if ($waiting->isEmpty()) {
            return self::SUCCESS;
        }

        $lines = [
            $waiting->count() === 1 ? 'One brief has been waiting for an answer:' : $waiting->count() . ' briefs have been waiting for an answer:',
            '',
        ];
        foreach ($waiting as $lead) {
            $lines[] = '- ' . $lead->name . ' (' . $lead->email . '), sent ' . $lead->created_at->diffForHumans();
            $lines[] = '  ' . route('admin.leads.show', $lead->id);
        }
        $lines[] = '';
        $lines[] = 'A quick reply, even just "got it, I will answer by Friday", makes a big difference. You get this reminder once per brief.';

        try {
            Mail::raw(implode("\n", $lines), fn ($m) => $m->to(config('portfolio.contact_email'))->subject($waiting->count() . ' lead(s) waiting for your answer'));
        } catch (\Throwable $e) {
            Log::warning('Could not send the lead reminder: ' . $e->getMessage());

            return self::FAILURE; // nothing is marked, so tomorrow's run tries again
        }

        Lead::whereIn('id', $waiting->pluck('id'))->update(['reminded_at' => now()]);

        return self::SUCCESS;
    }
}
