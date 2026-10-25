@extends('admin.layout')

@section('title', 'Two-factor login')

@section('bare')
<main id="main" tabindex="-1" class="flex min-h-screen items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <span class="logo-mark mx-auto flex h-14 w-14 items-center justify-center rounded-full text-lg font-bold text-white">FC</span>
            <p class="eyebrow mt-5">Admin</p>
            <h1 class="mt-2 text-3xl font-bold text-white">Two-factor login</h1>
            <p class="mt-3 text-sm text-slate-400">Enter the 6-digit code from your authenticator app, or one of your recovery codes.</p>
        </div>

        <div class="glass rounded-3xl p-7">
            @if ($errors->any())
                <div role="alert" tabindex="-1" data-feedback class="mb-4 rounded-2xl border border-rose-400/40 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('admin.2fa.verify') }}" method="POST" class="space-y-5">
                @csrf
                <label class="field">
                    <span>Code</span>
                    <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="20" required autofocus placeholder="123456">
                </label>
                <button class="btn btn-primary w-full px-4 py-3 text-sm font-semibold">Verify</button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-slate-400"><a href="{{ route('admin.login') }}" class="hover:text-slate-300">&larr; Start again</a></p>
    </div>
</main>
@endsection
