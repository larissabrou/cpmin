<?php



namespace App\Http\Controllers;



use App\Models\User;

use App\Services\LoginEmailTwoFactor;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\RateLimiter;

use Illuminate\View\View;



class AuthController extends Controller

{

    public function showLogin(): View

    {

        return view('auth.login');

    }



    public function login(Request $request, LoginEmailTwoFactor $twoFactor): RedirectResponse

    {

        $credentials = $request->validate([

            'email' => 'required|email',

            'password' => 'required',

        ]);



        $throttleKey = 'portal-login:'.$request->ip().':'.strtolower($credentials['email']);



        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {

            return back()->withErrors(['email' => 'Trop de tentatives. Réessayez dans une minute.'])->onlyInput('email');

        }



        RateLimiter::hit($throttleKey, 60);



        if (! Auth::validate($credentials)) {

            return back()->withErrors(['email' => 'Identifiants invalides.'])->onlyInput('email');

        }



        if (! config('security.email_2fa_enabled', true)) {

            return $this->completeLogin($request, User::where('email', $credentials['email'])->firstOrFail(), $request->boolean('remember'));

        }



        $user = User::where('email', $credentials['email'])->firstOrFail();

        $intentId = $twoFactor->start($user, $request->boolean('remember'));

        $request->session()->put('login_2fa_intent_id', $intentId);



        return redirect()->route('login.two-factor');

    }



    public function showTwoFactor(Request $request): View|RedirectResponse

    {

        if (! $request->session()->has('login_2fa_intent_id')) {

            return redirect()->route('login');

        }



        return view('auth.two-factor');

    }



    public function verifyTwoFactor(Request $request, LoginEmailTwoFactor $twoFactor): RedirectResponse

    {

        $request->validate([

            'code' => 'required|string|min:6|max:20',

        ]);



        $intentId = $request->session()->get('login_2fa_intent_id');



        if (! is_string($intentId) || $intentId === '') {

            return redirect()->route('login')->withErrors(['email' => 'Session expirée. Reconnectez-vous.']);

        }



        $throttleKey = 'portal-2fa:'.$request->ip().':'.$intentId;



        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {

            return back()->withErrors(['code' => 'Trop de tentatives. Demandez un nouveau code en vous reconnectant.']);

        }



        $result = $twoFactor->verify($intentId, $request->input('code'));



        if ($result === null) {

            RateLimiter::hit($throttleKey, 300);



            return back()->withErrors(['code' => 'Code invalide ou expiré.']);

        }



        $user = User::find($result['user_id']);



        if (! $user) {

            $request->session()->forget('login_2fa_intent_id');



            return redirect()->route('login')->withErrors(['email' => 'Compte introuvable.']);

        }



        $request->session()->forget('login_2fa_intent_id');



        $user->forceFill([

            'two_factor_enabled' => true,

            'two_factor_method' => 'email',

            'two_factor_confirmed_at' => now(),

        ])->save();



        return $this->completeLogin($request, $user, $result['remember'] ?? false);

    }



    public function logout(Request $request): RedirectResponse

    {

        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();



        return redirect()->route('login');

    }



    private function completeLogin(Request $request, User $user, bool $remember): RedirectResponse

    {

        Auth::login($user, $remember);

        $request->session()->regenerate();



        if (! $user->current_company_id) {

            $first = $user->companies()->first();



            if ($first) {

                $user->update(['current_company_id' => $first->id]);

            }

        }



        return redirect()->intended(
            $user->must_change_password ? route('password.change') : route('dashboard')
        );

    }

}


