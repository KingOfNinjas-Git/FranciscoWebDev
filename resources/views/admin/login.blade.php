<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Francisco Cavaco</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-950 p-4 text-slate-100">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.28rem] text-sky-300">Admin</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight text-white">Francisco</h1>
        </div>

        <div class="site-card rounded-2xl border border-slate-800 p-7">
            <h2 class="mb-6 text-2xl font-semibold text-white">Sign in</h2>

            @if(session('error'))
                <div class="mb-4 rounded-xl border border-red-700 bg-red-950/60 px-3 py-2 text-sm text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-200">Admin password</label>
                    <input type="password" name="password" class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-slate-100 placeholder:text-slate-500 focus:border-sky-500 focus:outline-none" placeholder="Enter password" required autofocus>
                </div>

                <button class="site-button site-button-primary w-full px-4 py-3 text-sm font-medium text-white">
                    Log in
                </button>
            </form>
        </div>
    </div>
</body>
</html>
