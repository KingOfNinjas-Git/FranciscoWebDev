<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LeadAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Lead::latest();
        if (array_key_exists($status, Lead::STATUSES)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        return view('admin.leads.index', [
            'leads' => $query->get(),
            'status' => $status,
            'counts' => Lead::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(int $id)
    {
        $lead = Lead::with('replies')->findOrFail($id);

        return view('admin.leads.show', ['lead' => $lead, 'templates' => $this->templatesFor($lead)]);
    }

    public function update(Request $request, int $id)
    {
        $lead = Lead::findOrFail($id);
        $data = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Lead::STATUSES)),
            'notes' => 'nullable|string|max:5000',
        ]);

        $lead->update($data);

        return redirect()->route('admin.leads.show', $lead->id)->with('success', 'Lead updated.');
    }

    /** Sends an email to the person who wrote, from here, and keeps a copy of it on the lead. */
    public function reply(Request $request, int $id)
    {
        $lead = Lead::findOrFail($id);
        $data = $request->validate([
            'subject' => 'required|string|max:200',
            'body' => 'required|string|max:10000',
        ]);

        // The templates have [bracketed] gaps (price, time...) that must not go out by mistake
        if (preg_match('/\[[^\]\n]{1,80}\](?!\()/u', $data['subject'] . "\n" . $data['body'])) {
            return back()->withInput()->withErrors(['body' => 'Fill in or remove the [bracketed] parts before sending.']);
        }

        $owner = config('portfolio.contact_email');
        $error = null;
        try {
            Mail::raw($data['body'], function ($message) use ($lead, $data, $owner) {
                $message->to($lead->email, $lead->name)->bcc($owner)->replyTo($owner)->subject($data['subject']);
            });
        } catch (\Throwable $e) {
            Log::error('Lead reply failed: ' . $e->getMessage());
            $error = mb_substr($e->getMessage(), 0, 250);
        }

        LeadReply::create(['lead_id' => $lead->id, 'subject' => $data['subject'], 'body' => $data['body'], 'mail_error' => $error]);

        if ($error !== null) {
            return back()->withInput()->withErrors(['body' => 'The email could not be sent. It was saved below; try again in a moment or use your own email app.']);
        }

        if ($lead->status === 'new') {
            $lead->update(['status' => 'replied']);
        }

        return redirect()->route('admin.leads.show', $lead->id)->with('success', 'Reply sent to ' . $lead->email . ' (you got a copy too).');
    }

    /** The reply templates in the language the person wrote in, with their name and project type filled in. */
    private function templatesFor(Lead $lead): array
    {
        $locale = in_array($lead->locale, ['en', 'pt', 'es'], true) ? $lead->locale : 'en';
        $type = mb_strtolower((string) (__('site.contact.types', [], $locale)[$lead->project_type] ?? $lead->project_type));
        $fill = fn (string $text) => strtr($text, [':name' => $lead->name, ':type' => $type]);

        $out = [];
        foreach (__('lead_replies.templates', [], 'en') as $key => $english) {
            $t = __('lead_replies.templates.' . $key, [], $locale);
            $out[$key] = ['label' => $english['label'], 'subject' => $fill($t['subject']), 'body' => $fill($t['body'])];
        }

        return $out;
    }

    public function destroy(int $id)
    {
        Lead::findOrFail($id)->delete();

        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.');
    }
}
