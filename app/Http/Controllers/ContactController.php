<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use App\Models\Lead;
use App\Services\Stats;
use App\Support\FormToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $back = SetLocale::homeUrl() . '#contact';

        // Honeypot: real visitors never fill this hidden field
        if ($request->filled('website')) {
            return redirect()->to($back);
        }

        // Signed token from the page the form was loaded on (replaces a session-based CSRF token)
        $token = FormToken::check($request->input('_ft'));
        if ($token === 'expired') {
            return redirect()->to($back)->withInput()->with('contact_expired', true);
        }
        if ($token !== 'ok') {
            return redirect()->to($back); // missing/forged token or sent faster than a human can type: ignore quietly
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'project_type' => 'required|in:landing,business,webapp,ecommerce,redesign,other',
            'description' => 'required|string|max:3000',
            'style' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
            'features.*' => 'in:responsive,multilang,cms,forms,seo,animations',
            'budget' => 'nullable|in:small,medium,large,unsure',
            'deadline' => 'nullable|string|max:120',
        ], [
            'required' => __('site.contact.val.required'),
            'email' => __('site.contact.val.email'),
            'max' => __('site.contact.val.max'),
            'in' => __('site.contact.val.invalid'),
        ], [
            'name' => __('site.contact.name'),
            'email' => __('site.contact.email'),
            'project_type' => __('site.contact.type'),
            'description' => __('site.contact.description'),
        ]);

        // Send errors back to the form section instead of the top of the page
        if ($validator->fails()) {
            return redirect()->to(SetLocale::homeUrl() . '#contact')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Link-stuffed messages are spam: drop them quietly, like the other bot checks above
        if (preg_match_all('~https?://|www\.~i', ($data['description'] ?? '') . ' ' . ($data['style'] ?? '') . ' ' . $data['name']) >= 3) {
            return redirect()->to($back);
        }

        // Keep the brief first, so it exists even if the email below fails (the table may be missing before the migration runs)
        $lead = null;
        try {
            $lead = Lead::create($data + ['locale' => app()->getLocale()]);
            Stats::hit('event', 'lead');
        } catch (\Throwable $e) {
            Log::error('Could not save contact brief: ' . $e->getMessage());
        }

        $lines = [
            'New website brief from the portfolio',
            '',
            'Name: ' . $data['name'],
            'Email: ' . $data['email'],
            'Project type: ' . $data['project_type'],
            'Budget: ' . ($data['budget'] ?? '-'),
            'Deadline: ' . ($data['deadline'] ?? '-'),
            'Features: ' . implode(', ', $data['features'] ?? []),
            'Language: ' . app()->getLocale(),
            '',
            'Description:',
            $data['description'],
            '',
            'Look and feel:',
            $data['style'] ?? '-',
        ];
        if ($lead) {
            $lines[] = '';
            $lines[] = 'In the admin: ' . route('admin.leads.show', $lead->id);
        }

        $mailError = null;
        try {
            Mail::raw(implode("
", $lines), function ($message) use ($data) {
                $message->to(config('portfolio.contact_email'))
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Portfolio brief from ' . $data['name']);
            });
        } catch (\Throwable $e) {
            Log::error('Contact form mail failed: ' . $e->getMessage());
            $mailError = mb_substr($e->getMessage(), 0, 250);
        }

        if ($lead) {
            $lead->update(['mail_sent' => $mailError === null, 'mail_error' => $mailError]);
        }

        // Without a saved copy a failed email means the brief is lost, so only then does the visitor see an error
        if ($mailError !== null && ! $lead) {
            return redirect()->to($back)->withInput()->with('contact_error', true);
        }

        $this->sendConfirmation($data);

        return redirect()->to($back)->with('contact_sent', true);
    }

    /** A short "I got your message" email to the sender, in the language of the page they used. Never blocks the form. */
    private function sendConfirmation(array $data): void
    {
        $summary = implode("
", array_filter([
            __('site.contact.type') . ': ' . (__('site.contact.types')[$data['project_type']] ?? $data['project_type']),
            ! empty($data['budget']) ? __('site.contact.budget') . ': ' . (__('site.contact.budgets')[$data['budget']] ?? $data['budget']) : null,
            ! empty($data['deadline']) ? __('site.contact.deadline') . ': ' . $data['deadline'] : null,
            '',
            $data['description'],
        ], fn ($line) => $line !== null));

        try {
            Mail::raw(__('site.contact.reply.body', ['name' => $data['name'], 'summary' => $summary]), function ($message) use ($data) {
                $message->to($data['email'], $data['name'])
                    ->replyTo(config('portfolio.contact_email'))
                    ->subject(__('site.contact.reply.subject'));
            });
        } catch (\Throwable $e) {
            Log::warning('Contact confirmation email failed: ' . $e->getMessage());
        }
    }
}
