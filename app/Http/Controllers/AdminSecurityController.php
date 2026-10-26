<?php

namespace App\Http\Controllers;

use App\Services\AdminPassword;
use App\Services\ContentBackup;
use App\Support\AdminAlert;
use App\Services\TwoFactor;
use App\Support\Totp;
use Illuminate\Http\Request;

class AdminSecurityController extends Controller
{
    public function index(Request $request, TwoFactor $twoFactor, ContentBackup $backup, AdminPassword $passwords)
    {
        $setupSecret = $request->session()->get('totp_setup_secret');

        return view('admin.security', [
            'passwordStored' => $passwords->stored(),
            'minLength' => AdminPassword::MIN_LENGTH,
            'recentLogins' => $passwords->recentLogins(),
            'enabled' => $twoFactor->enabled(),
            'bypassed' => $twoFactor->bypassed(),
            'codesLeft' => $twoFactor->enabled() ? $twoFactor->recoveryCodesLeft() : 0,
            'setupSecret' => $setupSecret,
            'otpauth' => $setupSecret ? Totp::uri($setupSecret, 'admin', config('app.name', 'Portfolio')) : null,
            'recoveryCodes' => $request->session()->pull('recovery_codes'),
            'zipAvailable' => $backup->zipAvailable(),
        ]);
    }

    public function changePassword(Request $request, AdminPassword $passwords)
    {
        $data = $request->validate([
            'current_password' => 'required|string|max:200',
            'password' => ['required', 'string', 'min:' . AdminPassword::MIN_LENGTH, 'max:200', 'confirmed', 'different:current_password'],
        ], [
            'password.min' => 'The new password needs at least ' . AdminPassword::MIN_LENGTH . ' characters. A few random words in a row work well.',
            'password.confirmed' => 'The two new passwords do not match.',
            'password.different' => 'The new password must be different from the current one.',
        ]);

        if (! $passwords->verify($data['current_password'])) {
            return redirect()->route('admin.security')->withErrors(['current_password' => 'The current password is not right, so nothing was changed.']);
        }

        $passwords->change($data['password']);
        $request->session()->put('admin_epoch', $passwords->epoch()); // this browser stays signed in
        AdminAlert::passwordChanged($request);

        return redirect()->route('admin.security')->with('success', 'Password changed. Every other signed-in browser was signed out.');
    }

    public function logoutEverywhere(Request $request, AdminPassword $passwords)
    {
        $passwords->bumpEpoch();
        AdminAlert::signedOutEverywhere($request);
        $request->session()->forget(['is_admin', 'admin_last_seen', 'admin_epoch']);

        return redirect()->route('admin.login')->with('error', 'Signed out everywhere. Sign in again.');
    }

    public function startSetup(Request $request, TwoFactor $twoFactor)
    {
        $request->session()->put('totp_setup_secret', $twoFactor->newSecret());

        return redirect()->route('admin.security');
    }

    public function enable(Request $request, TwoFactor $twoFactor)
    {
        $request->validate(['code' => 'required|string|max:12']);

        $secret = $request->session()->get('totp_setup_secret');
        $codes = $secret ? $twoFactor->confirmSetup($secret, $request->input('code')) : null;

        if ($codes === null) {
            return redirect()->route('admin.security')->withErrors(['code' => 'That code did not match. Check the code in your app and try again.']);
        }

        $request->session()->forget('totp_setup_secret');
        $request->session()->flash('recovery_codes', $codes);

        return redirect()->route('admin.security')->with('success', 'Two-factor login is on. Save your recovery codes now.');
    }

    public function cancelSetup(Request $request)
    {
        $request->session()->forget('totp_setup_secret');

        return redirect()->route('admin.security');
    }

    public function disable(Request $request, TwoFactor $twoFactor)
    {
        $request->validate(['code' => 'required|string|max:20']);

        if (! $twoFactor->verifyAny($request->input('code'))) {
            return redirect()->route('admin.security')->withErrors(['code' => 'That code did not match, so two-factor login stays on.']);
        }

        $twoFactor->disable();

        return redirect()->route('admin.security')->with('success', 'Two-factor login is off.');
    }

    public function newRecoveryCodes(Request $request, TwoFactor $twoFactor)
    {
        $request->validate(['code' => 'required|string|max:20']);

        if (! $twoFactor->enabled() || ! $twoFactor->verifyAny($request->input('code'))) {
            return redirect()->route('admin.security')->withErrors(['code' => 'That code did not match.']);
        }

        $request->session()->flash('recovery_codes', $twoFactor->regenerateRecoveryCodes());

        return redirect()->route('admin.security')->with('success', 'New recovery codes created. The old ones no longer work.');
    }

    public function backup(ContentBackup $backup)
    {
        $name = 'franciscowebdev-backup-' . now()->format('Y-m-d');

        if (! $backup->zipAvailable()) {
            return response($backup->sql(), 200, [
                'Content-Type' => 'application/sql; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $name . '.sql"',
            ]);
        }

        return response()->download($backup->zip(), $name . '.zip')->deleteFileAfterSend(true);
    }
}
