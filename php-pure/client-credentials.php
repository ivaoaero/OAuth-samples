<?php
// Get all URLs we need from the server
$openid_url = 'https://api.ivao.aero/.well-known/openid-configuration';
$openid_result = file_get_contents($openid_url, false);
if ($openid_result === FALSE) { 
    /* Handle error */
    die('Error while getting openid data');
}
$openid_data = json_decode($openid_result, true);


$token_req_data = array(
    'grant_type' => 'client_credentials',
    'client_id' => '57b2d957-38ff-4d1e-8d8f-7e5aa8d0d5fe',
    'client_secret' => 'VUFqej5bLDOBngOtUcQCF97U1o7MQDbu',
    'scope' => 'tracker'
);

// use key 'http' even if you send the request to https://...
$token_options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($token_req_data)
    )
);
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
$tracker_options = array(
    'http' => array(
        'header'  => "Authorization: Bearer $access_token\r\n",
        'method'  => 'GET'
    )
);
$tracker_context  = stream_context_create($tracker_options);
$tracker_result = file_get_contents($tracker_url, false, $tracker_context);
if ($tracker_result === FALSE) { 
    /* Handle error */
    die('Error while getting tracker data');
}
$tracker_res_data = json_decode($tracker_result, true);

var_dump($tracker_res_data); // Display data fetched with the application token
?>