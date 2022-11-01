<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function callback(Request $request)
    {
        try
        {
            $loggedInUser = Socialite::driver('ivao')->user();

            // Check if logged in user allowed to access the website
            if (!$loggedInUser) abort(401);
            else Auth::login($loggedInUser);

            $request->session()->put('token', $ivaoUser->token);
            $request->session()->put('refreshToken', $ivaoUser->refreshToken);

            return redirect(route('home'));
        }
        catch (Exception $exception) { return redirect(route('login')); }
    }

    public function logout(Request $request)
    {
        $url = Socialite::driver('ivao')->getOpenIdConfig()->revocation_endpoint;
        $accessData = json_encode(array('token' => $request->session()->get('token'), 'token_type_hint' => 'access_token', 'client_id' => config('services.ivao.client_id')));
        $refreshData = json_encode(array('token' => $request->session()->get('refreshToken'), 'token_type_hint' => 'refresh_token', 'client_id' => config('services.ivao.client_id')));

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
        curl_setopt($curl, CURLOPT_POST, true);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $accessData);
        curl_exec($curl);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $refreshData);
        curl_exec($curl);

        curl_close($curl);

        Auth::logout();
        $request->session()->flush();

        return redirect('https://www.ivao.aero/')->withCookie(cookie($this->cookie_name, '', -3600));
    }
}
