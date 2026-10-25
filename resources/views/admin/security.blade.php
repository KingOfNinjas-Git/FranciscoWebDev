@extends('admin.layout')

@section('title', 'Security')

@section('content')
<div class="mb-8">
    <p class="eyebrow">Admin</p>
    <h1 class="section-title">Security &amp; backups</h1>
</div>

@if ($recoveryCodes)
    <section class="mb-8 rounded-3xl border border-amber-400/40 bg-amber-400/10 p-6 md:p-8" aria-labelledby="rc-title">
        <h2 id="rc-title" class="text-xl font-semibold text-amber-100">Your recovery codes</h2>
        <p class="mt-2 text-sm text-amber-100/90">Save these now, for example in a password manager. Each code works <strong>once</strong> if you lose your phone. They will not be shown again.</p>
        <ul class="mt-5 grid grid-cols-2 gap-3 font-mono text-lg text-white sm:grid-cols-5">
            @foreach ($recoveryCodes as $code)
                <li class="rounded-xl border border-white/15 bg-slate-950/50 px-3 py-2 text-center">{{ $code }}</li>
            @endforeach
        </ul>
    </section>
@endif

<div class="grid gap-8 lg:grid-cols-2">
    <section class="glass rounded-3xl p-6 md:p-8" aria-labelledby="tf-title">
        <div class="flex items-center justify-between gap-3">
            <h2 id="tf-title" class="text-xl font-semibold text-white">Two-factor login</h2>
            <span class="rounded-full border border-white/15 px-3 py-1 font-mono text-[11px] uppercase tracking-widest {{ $enabled ? 'text-emerald-300' : 'text-slate-300' }}">{{ $enabled ? 'On' : 'Off' }}</span>
        </div>
        <p class="mt-2 text-sm text-slate-300">After the password, the login also asks for a 6-digit code from an authenticator app on your phone (Google Authenticator, Microsoft Authenticator, Aegis, 1Password and similar). A stolen password alone is then not enough.</p>

        @if ($bypassed)
            <p class="mt-4 rounded-2xl border border-amber-400/40 bg-amber-400/10 px-4 py-3 text-sm text-amber-100">The emergency switch <code>ADMIN_2FA_DISABLED</code> is on in the server's <code>.env</code>, so the code is currently <strong>not</strong> being asked for. Remove that line when you no longer need it.</p>
        @endif

        @if ($errors->has('code'))
            <p role="alert" tabindex="-1" data-feedback class="mt-4 rounded-2xl border border-rose-400/40 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">{{ $errors->first('code') }}</p>
        @endif

        @if (! $enabled && ! $setupSecret)
            <form action="{{ route('admin.security.start') }}" method="POST" class="mt-6">
                @csrf
                <button class="btn btn-primary px-6 py-3 text-sm font-semibold">Set up two-factor login</button>
            </form>
        @endif

        @if (! $enabled && $setupSecret)
            <ol class="mt-6 list-decimal space-y-4 pl-5 text-sm text-slate-300 marker:text-sky-400">
                <li>Open your authenticator app and add a new account by scanning this code:
                    <div id="totp-qr" data-uri="{{ $otpauth }}" role="img" aria-label="QR code for the authenticator app" class="mt-3 inline-block rounded-2xl bg-white p-3"></div>
                </li>
                <li>Can't scan? Enter this key by hand (time-based):
                    <p class="mt-2 break-all rounded-xl border border-white/10 bg-slate-950/50 px-3 py-2 font-mono text-base tracking-widest text-white">{{ $setupSecret }}</p>
                </li>
                <li>Type the 6-digit code the app shows to finish:
                    <form action="{{ route('admin.security.enable') }}" method="POST" class="mt-3 flex flex-wrap gap-3">
                        @csrf
                        <label class="field w-40">
                            <span class="sr-only">6-digit code</span>
                            <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="8" required placeholder="123456">
                        </label>
                        <button class="btn btn-primary px-5 text-sm font-semibold">Turn on</button>
                    </form>
                </li>
            </ol>
            <form action="{{ route('admin.security.cancel') }}" method="POST" class="mt-4">
                @csrf
                <button class="text-sm text-slate-400 underline underline-offset-2 hover:text-slate-200">Cancel</button>
            </form>
        @endif

        @if ($enabled)
            <p class="mt-4 text-sm text-slate-300">You have <strong class="text-white">{{ $codesLeft }}</strong> unused recovery {{ \Illuminate\Support\Str::plural('code', $codesLeft) }}.</p>

            <div class="mt-6 space-y-6">
                <form action="{{ route('admin.security.recovery') }}" method="POST" class="space-y-3">
                    @csrf
                    <label class="field">
                        <span>Create new recovery codes (confirm with a current code)</span>
                        <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="20" required placeholder="123456">
                    </label>
                    <button class="btn btn-ghost px-5 py-2.5 text-sm font-medium">Create new codes</button>
                </form>

                <form action="{{ route('admin.security.disable') }}" method="POST" class="space-y-3 border-t border-white/10 pt-6" data-confirm="Turn off two-factor login?">
                    @csrf
                    <label class="field">
                        <span>Turn two-factor off (confirm with a current code)</span>
                        <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="20" required placeholder="123456">
                    </label>
                    <button class="btn btn-ghost px-5 py-2.5 text-sm font-medium text-rose-300 hover:!border-rose-400/60">Turn off</button>
                </form>
            </div>
        @endif
    </section>

    <section class="glass h-fit rounded-3xl p-6 md:p-8" aria-labelledby="bk-title">
        <h2 id="bk-title" class="text-xl font-semibold text-white">Backup</h2>
        <p class="mt-2 text-sm text-slate-300">Download everything you create here: projects, project images, blog posts and your uploaded pictures. Keep the file somewhere safe, away from the server.</p>
        <ul class="mt-4 list-disc space-y-1 pl-5 text-sm text-slate-300 marker:text-sky-400">
            <li><code>content.sql</code>: the text and settings of your projects and posts (importable in phpMyAdmin)</li>
            <li><code>public/imgs</code>: every uploaded picture</li>
            <li>A README with the restore steps</li>
        </ul>
        @unless ($zipAvailable)
            <p class="mt-3 text-sm text-amber-200">This server cannot make ZIP files, so only the <code>content.sql</code> text file will download (pictures need to be copied with the File Manager).</p>
        @endunless
        <form action="{{ route('admin.backup') }}" method="POST" class="mt-6">
            @csrf
            <button class="btn btn-primary px-6 py-3 text-sm font-semibold">Download backup</button>
        </form>
        <p class="mt-4 text-xs text-slate-400">Not included on purpose: your admin password, the two-factor secret and AI logs. Hostinger's own automatic backups (hPanel &rarr; Backups) are still worth keeping on.</p>
    </section>
</div>
@endsection
