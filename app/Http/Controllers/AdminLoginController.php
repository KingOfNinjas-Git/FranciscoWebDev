<?php

namespace App\Http\Controllers;

use App\Services\AdminPassword;
use App\Services\TwoFactor;
use App\Support\AdminAlert;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    private const PENDING_MINUTES = 10;
    private const MAX_CODE_TRIES = 5;

    public function index()
    {
        return view('admin.login');
    }

    public function login(Request $request, TwoFactor $twoFactor, AdminPassword $password)
    {
        if (! $password->verify((string) $request->input('password'))) {
            AdminAlert::wrongPassword($request);

            return redirect()->route('admin.login')->with('error', 'Invalid password');
        }

        $request->session()->regenerate();

        // Password correct: if two-factor is on, a code is still needed before the admin opens
        if ($twoFactor->required()) {
            $request->session()->put('admin_2fa_pending', now()->timestamp);
            $request->session()->forget('admin_2fa_fails');

            return redirect()->route('admin.2fa');
        }

        return $this->finishLogin($request);
    }

    public function challenge(Request $request)
    {
        if (! $this->pending($request)) {
            return redirect()->route('admin.login');
        }

        return view('admin.two-factor');
    }

    public function verify(Request $request, TwoFactor $twoFactor)
    {
        if (! $this->pending($request)) {
            return redirect()->route('admin.login')->with('error', 'Your sign-in took too long. Enter the password again.');
        }

        $request->validate(['code' => 'required|string|max:20']);

        if ($twoFactor->verifyAny($request->input('code'))) {
            $request->session()->forget(['admin_2fa_pending', 'admin_2fa_fails']);
            $request->session()->regenerate();

            return $this->finishLogin($request);
        }

        $fails = (int) $request->session()->get('admin_2fa_fails', 0) + 1;
        $request->session()->put('admin_2fa_fails', $fails);

        // Too many wrong codes: back to the password step
        if ($fails >= self::MAX_CODE_TRIES) {
            $request->session()->forget(['admin_2fa_pending', 'admin_2fa_fails']);

            return redirect()->route('admin.login')->with('error', 'Too many wrong codes. Enter the password again.');
        }

        return back()->withErrors(['code' => 'That code is not valid. Try the current code from your app, or a recovery code.']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['is_admin', 'admin_last_seen', 'admin_2fa_pending', 'admin_epoch']);

        return redirect()->route('admin.login');
    }

    private function finishLogin(Request $request)
    {
        $passwords = app(AdminPassword::class);

        $request->session()->put('is_admin', true);
        $request->session()->put('admin_last_seen', now()->timestamp);
        $request->session()->put('admin_epoch', $passwords->epoch());

        // Tell the owner, and keep a short history for the Security page
        try {
            $passwords->recordLogin((string) $request->ip(), mb_substr((string) $request->userAgent(), 0, 160));
        } catch (\Throwable $e) {
            // the history is a convenience, never a reason to refuse a login
        }
        AdminAlert::signedIn($request);

        return redirect()->route('admin.projects.index');
    }

    private function pending(Request $request): bool
    {
        $started = $request->session()->get('admin_2fa_pending');

        return $started && now()->timestamp - $started <= self::PENDING_MINUTES * 60;
    }
}
