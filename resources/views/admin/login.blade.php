@extends('admin.layout')

@section('title', 'Sign in')

@section('bare')
<div class="flex min-h-screen items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <span class="logo-mark mx-auto flex h-14 w-14 items-center justify-center rounded-full text-lg font-bold text-white">FC</span>
            <p class="eyebrow mt-5">Admin</p>
            <h1 class="mt-2 text-3xl font-bold text-white">Sign in</h1>
        </div>

        <div class="glass rounded-3xl p-7">
            @if (session('error'))
                <div role="alert" class="mb-4 rounded-2xl border border-rose-400/40 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
                @csrf
                <label class="field">
                    <span>Admin password</span>
                    <input type="password" name="password" placeholder="Enter password" required autofocus autocomplete="current-password">
                </label>
                <button class="btn btn-primary w-full px-4 py-3 text-sm font-semibold">Log in</button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-slate-500"><a href="{{ route('home') }}" class="hover:text-slate-300">&larr; Back to site</a></p>
    </div>
</div>
@endsection
