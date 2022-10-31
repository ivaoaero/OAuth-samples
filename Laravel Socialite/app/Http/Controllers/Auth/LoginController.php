<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\AccessList;
use App\User;
use App\UserStaffPosition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;

const ROLE_POSITION_MATCHING = [
    'DIV-HQ' => '/\-ADIR|\-DIR/',
    'DIV-TD' => '/(^TD)|(^TAD)|\-TC|\-TAC|\-TA[1-9]/',
    'DIV-ATC' => '/\-AOC|\-AOAC|\-AOA[1-9]/',
    'DIV-FIR' => '/\-CH|\-ACH|\-CHA[1-9]/',
    'DIV-SO' => '/\-SOC|\-SOAC|\-SOA[1-9]/',
    'DIV-FO' => '/\-FOC|\-FOAC|\-FOA[1-9]/',
    'HQ-ATC' => '/^(AOD|AOAD|AOA[1-9]+)/',
    'HQ-FO' => '/^(FOD|FOAD|FOA[1-9]+)/',
    'HQ-SO' => '/^(SOD|SOAD|SOA[1-9]+)/',
    'web_developer' => '/^WD([1-9]+|M)/',
    'software_developer' => '/^SD([1-9]+|M)/',
    'mtl_developer' => '/^MTL([1-9]+|M)/',
    'sector_developer' => '/\-CH|\-ACH|\-CHA[1-9]/'
];

class LoginController extends Controller
{
    private $cookie_name;
    private $login_url;
    private $api_url;

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);

        $this->cookie_name = env('IVAO_TOKEN_COOKIE_NAME', 'ivao_token');
        $this->login_url = env('IVAO_LOGIN_URL', 'https://login.ivao.aero');
        $this->api_url = env('IVAO_LOGIN_API_URL', 'https://login.ivao.aero/api.php');
    }

    public function callback(Request $request)
    {
        try
        {
            $ivaoUser = Socialite::driver('ivao')->user();

            // Check if logged in user IVAO Staff allowed to access Data Web
            if (!$ivaoUser->isStaff) abort(403);
            else Auth::login($ivaoUser);

            $access_list = AccessList::find($ivaoUser->id);

            if (!$access_list) {
                $access_list = new AccessList();
                $access_list->user_id = $ivaoUser->id;
                $access_list->active = true;
                $access_list->save();
            }

            //Get user staff positions
            $positions = $ivaoUser->userStaffPositions;

            // Give + Update user's permissions
            foreach (ROLE_POSITION_MATCHING as $perm => $match)
                foreach($positions as $position)
                    $access_list->add_role($perm, preg_match($match, $position['id']));

            $request->session()->put('token', $ivaoUser->token);
            $request->session()->put('refreshToken', $ivaoUser->refreshToken);
            $request->session()->put('ivaoData', $ivaoUser);
            $request->session()->put('ivaoPerms', $access_list->permissions(true));

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

    private function redirectToIvaoLogin(Request $request)
    {
        $cookie = Cookie::make('ivao_token', '', -3600);
        return redirect($this->login_url . '?url=' . $request->url())
            ->withCookie($cookie);
    }
}
