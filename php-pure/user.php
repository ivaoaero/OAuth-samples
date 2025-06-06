<?php
// Load environment variables
$ENV = parse_ini_file('.env');

// Set cookie name
const cookie_name = 'ivao_tokens';

/**
 * @return int|false the HTTP response code from the given HTTP response header or false
 * if the header is not an array or does not contain a valid status code.
 */
function get_http_response_code($http_response_header) {
    if (is_array($http_response_header) && isset($http_response_header[0])) {
        $parts = explode(' ', $http_response_header[0]);
        if (count($parts) > 1) {
            return (int)$parts[1];
        }
    }
    return false;
}

// Get all URLs we need from the server
$openid_url = $ENV['OPENID_URL'];
$openid_result = file_get_contents($openid_url);
if ($openid_result === false) {
    /* Handle error */
    die('Error while getting openid data');
}
$openid_data = json_decode($openid_result, true);

// Now we can take care of the actual authentication
$client_id = $ENV['CLIENT_ID'];
$client_secret = $ENV['CLIENT_SECRET'];
$redirect_uri = $ENV['REDIRECT_URI'];

if (isset($_GET['code']) && isset($_GET['state'])) {
    // User has been redirected back from the login page

    $code = $_GET['code']; // Valid only 5 minutes

    $token_req_data = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => $redirect_uri,
    ];
    
    // use key 'http' even if you send the request to https://...
    $token_options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($token_req_data)
        ]
    ];
    $token_context  = stream_context_create($token_options);
    $token_result = file_get_contents($openid_data['token_endpoint'], false, $token_context);
    if ($token_result === FALSE) { 
        /* Handle error */
        die('Error while getting token');
    }
    
    $token_res_data = json_decode($token_result, true);

    $access_token = $token_res_data['access_token']; // Here is the access token
    $refresh_token = $token_res_data['refresh_token']; // Here is the refresh token
    
    setcookie(cookie_name, json_encode(compact('access_token', 'refresh_token')), time() + 60 * 60 * 24 * 30); // 30 days

    header('Location: ' . $redirect_uri); // Remove the code and state from URL since they aren't valid anymore 

} elseif (isset($_COOKIE[cookie_name])) {
    // User has already logged in

    $tokens = json_decode($_COOKIE[cookie_name], true);
    $access_token = $tokens['access_token'];
    $refresh_token = $tokens['refresh_token'];

    // Now we can use the access token to get the data

    $user_options = [
        'http' => [
            'header'  => "Authorization: Bearer $access_token\r\n",
            'method'  => 'GET',
            'ignore_errors' => true,
        ]
    ];
    $user_context  = stream_context_create($user_options);
    $user_result = file_get_contents($openid_data['userinfo_endpoint'], false, $user_context);
    if ($user_result === FALSE) {
        /* Handle error */
        die('Error while getting user data');
    }

    $user_result_response_code = get_http_response_code($http_response_header);
    if ($user_result_response_code === false) {
        /* Handle error */
        die('Error while getting user data response code');
    }

    if ($user_result_response_code >= 400) {
        // Access token expired or missing

        if (isset($refresh_token)) {
            // Using refresh token to get a new one

            $token_req_data = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token,
                'client_id' => $client_id,
                'client_secret' => $client_secret
            ];

            $token_options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($token_req_data),
                    'ignore_errors' => true,
                ]
            ];
            $token_context  = stream_context_create($token_options);
            $token_result = file_get_contents($openid_data['token_endpoint'], false, $token_context);
            if ($token_result === FALSE) {
                /* Handle error */
                die('Error while refreshing token');
            }

            $token_result_response_code = get_http_response_code($http_response_header);
            if ($token_result_response_code === false) {
                /* Handle error */
                die('Error while getting refresh token response code');
            }

            if ($token_result_response_code <= 299) {
                $token_res_data = json_decode($token_result, true);

                $access_token = $token_res_data['access_token']; // Here is the new access token
                $refresh_token = $token_res_data['refresh_token']; // Here is the new refresh token

                setcookie(cookie_name, json_encode(compact('access_token', 'refresh_token')), time() + 60 * 60 * 24 * 30); // 30 days

                header('Location: ' . $redirect_uri); // Try to use the access token again
            }

        }

        // Delete cookie and authenticate user again

        setcookie(cookie_name, '', time() - 3600); // reset cookie value to null and expire time to last hour
        header('Location: ' . $redirect_uri); // Try to log in again
    }

    $user_res_data = json_decode($user_result, true);

    var_dump($user_res_data); // Display user data (/v2/users/me in Core Doc) fetched with the access token
} else {
    // First visit: Unauthenticated user
    
    $base_url = $openid_data['authorization_endpoint'];
    $scopes = 'profile configuration email';
    $state = '1234567890'; // TODO: random string to prevent CSRF attacks

    $full_url = sprintf('%s?%s', $base_url, http_build_query([
        'response_type' => 'code',
        'client_id' => $client_id,
        'scope' => $scopes,
        'redirect_uri' => $redirect_uri,
        'state' => $state
    ]));

    echo "<a href=\"$full_url\">Login</a>";
}
