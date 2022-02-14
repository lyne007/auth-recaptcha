<?php

namespace Lyne007\AuthRecaptcha\Http\Controllers;

use Encore\Admin\Controllers\AuthController as BaseAuthController;
use Encore\Admin\Facades\Admin;
use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Lyne007\AuthRecaptcha\AuthRecaptcha;

class AuthRecaptchaController extends BaseAuthController
{
    /**
     * @var string
     */
    protected $loginView = 'auth-recaptcha::login';

    /**
     * Show the login page.
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view('auth-recaptcha::login');
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $this->loginValidator($request->all())->validate();

        $credentials = $request->only([$this->username(), 'password']);
        $remember = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function googleApi(Request $request)
    {
        $apiUrl = 'https://www.recaptcha.net/recaptcha/api/siteverify';
        $params = [
            'secret' => AuthRecaptcha::config('secretKey'),
            'response' => $request->input('token')
        ];

        $response = Http::asForm()->withHeaders([
            // 请求头
            'Accept' => 'application/x-www-form-urlencoded',
        ])->timeout(6)->post($apiUrl, $params);
        $response->throw();
        if ($response->successful()) {
            $out = json_decode($response->body());
            if ($out->success == false) {
                return response()->json(['success'=>false]);
            }
            // 官方推荐 阈值 是 0.5 实际使用还是用0.4吧 太容易被干掉了
            if (intval($out->score * 10) >= 4) {
                return response()->json(['success'=>true]);
            }
        }
        return response()->json(['success'=>false]);
    }
}
