<?php

namespace App\Providers;

use GuzzleHttp\Exception\GuzzleException;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use App\User;

class SocialiteIVAOProvider extends AbstractProvider implements ProviderInterface
{

    protected $openIdConfig = null;

    /**
     * @var  string
     */
    protected $scopes = [
        'openid',
        'profile',
        'configuration',
        'email',
    ];

    /**
     * @var  string
     */
    protected $scopeSeparator = ' ';

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = true;

    /**
     * @return object
     */
    public function getOpenIdConfig()
    {
        if (!$this->openIdConfig)
            $this->openIdConfig = json_decode(file_get_contents(config('services.ivao.openid_url')));
        return $this->openIdConfig;
    }

    /**
    *  @param  string $state
    *
    *  @return string
    */
    protected function getAuthUrl($state)
    {
        $authEndpoint = $this->getOpenIdConfig()->authorization_endpoint;

        return $this->buildAuthUrlFromBase($authEndpoint, $state);
    }

    /**
    *  @return  string
    */
    protected function getTokenUrl()
    {
        return $this->getOpenIdConfig()->token_endpoint;
    }

    /**
    *  @param  string $token
    *
    *  @throws  GuzzleException
    *
    *  @return  array|mixed
    */
    protected function getUserByToken($token)
    {
        $userInfoEndpoint = $this->getOpenIdConfig()->userinfo_endpoint;

        $response = $this->getHttpClient()->get($userInfoEndpoint, [
            'headers' => [
                'cache-control' => 'no-cache',
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
    *  @return  User
    */
    protected function mapUserToObject(array $user)
    {
        $newUser = new User();
        $newUser->id = $user['id'];
        $newUser->email = $user['email'];
        $newUser->first_name = $user['firstName'];
        $newUser->last_name = $user['lastName'];
        $newUser->division_id = $user['divisionId'];
        $newUser->country_id = $user['countryId'];
        $newUser->isStaff = $user['isStaff'];
        $newUser->userStaffPositions = $user['userStaffPositions'];

        return $newUser;
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id' => $this->clientId, 'client_secret' => $this->clientSecret,
            'code' => $code, 'redirect_uri' => $this->redirectUrl, 'grant_type' => 'authorization_code',
        ];
    }
}
