<?php
// Load environment variables
$ENV = parse_ini_file('.env');

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
if ($openid_result === FALSE) { 
    /* Handle error */
    die('Error while getting openid data');
}
$openid_data = json_decode($openid_result, true);

$token_req_data = [
    'grant_type' => 'client_credentials',
    'client_id' => $ENV['CLIENT_ID'],
    'client_secret' => $ENV['CLIENT_SECRET'],
    'scope' => 'tracker'
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

// Now we can use the access token to get the data

$tracker_url = 'https://api.ivao.aero/v2/tracker/now/pilots/summary';
$tracker_options = [
    'http' => [
        'header'  => "Authorization: Bearer $access_token\r\n",
        'method'  => 'GET'
    ]
];
$tracker_context  = stream_context_create($tracker_options);
$tracker_result = file_get_contents($tracker_url, false, $tracker_context);
if ($tracker_result === FALSE) { 
    /* Handle error */
    die('Error while getting tracker data');
}
$tracker_res_data = json_decode($tracker_result, true);

var_dump($tracker_res_data); // Display data fetched with the application token
