<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        // Honeypot: real visitors never fill this hidden field
        if ($request->filled('website')) {
            return redirect()->to(route('home') . '#contact');
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'project_type' => 'required|in:landing,business,webapp,ecommerce,redesign,other',
            'description' => 'required|string|max:3000',
            'style' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
            'features.*' => 'in:responsive,multilang,cms,forms,seo,animations',
            'budget' => 'nullable|in:small,medium,large,unsure',
            'deadline' => 'nullable|string|max:120',
        ]);

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
            return redirect()->to(route('home') . '#contact')
                ->withInput()
                ->with('contact_error', true);
        }

        return redirect()->to(route('home') . '#contact')->with('contact_sent', true);
    }
}
