<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        // Honeypot: real visitors never fill this hidden field
        if ($request->filled('website')) {
            return redirect()->to(SetLocale::homeUrl() . '#contact');
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

        $lines = [
            'New website brief from the portfolio',
            '',
            'Name: ' . $data['name'],
            'Email: ' . $data['email'],
            'Project type: ' . $data['project_type'],
            'Budget: ' . ($data['budget'] ?? '-'),
            'Deadline: ' . ($data['deadline'] ?? '-'),
            'Features: ' . implode(', ', $data['features'] ?? []),
            '',
            'Description:',
            $data['description'],
            '',
            'Look and feel:',
            $data['style'] ?? '-',
        ];

        try {
            Mail::raw(implode("\n", $lines), function ($message) use ($data) {
                $message->to(config('portfolio.contact_email'))
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Portfolio brief from ' . $data['name']);
            });
        } catch (\Throwable $e) {
            Log::error('Contact form mail failed: ' . $e->getMessage());
            return redirect()->to(SetLocale::homeUrl() . '#contact')
                ->withInput()
                ->with('contact_error', true);
        }

        return redirect()->to(SetLocale::homeUrl() . '#contact')->with('contact_sent', true);
    }
}
