<?php
// Cron job to refresh the refresh tokens of the users in the database.
// Code author: HU-WM Roland Gaal - 218713

// If you want to receive an email after the script is done, uncomment the phpmailer related lines
// and fill the necessary fields.

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

include("../config/configurations_db.php");
include("../config/configurations_ivao.php");
// require '../PHPMailer/PHPMailer.php';
// require '../PHPMailer/Exception.php';
// require '../PHPMailer/SMTP.php';

$openid_result = file_get_contents($openid_url, false);
if ($openid_result === FALSE) {
    die('Error while getting openid data');
}
$openid_data = json_decode($openid_result, true);

$pdo = new PDO($dsn, $database_user, $database_password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM user_data WHERE TIMESTAMPDIFF(DAY,refresh_token_date,now()) > 10";

$user = $pdo->prepare($query);
$user->execute();

$rows = $user->fetchAll(PDO::FETCH_ASSOC);

$successful = array();
$failed = array();

foreach ($rows as $row) {
    $refresh_token = $row['refresh_token'];

    $token_req_data = array(
        'grant_type' => 'refresh_token',
        'refresh_token' => $refresh_token,
        'client_id' => $client_id_ivao,
        'client_secret' => $client_secret_ivao
    );

    $token_options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($token_req_data),
            'ignore_errors' => true,
        )
    );
    $token_context = stream_context_create($token_options);
    $token_result = file_get_contents($openid_data['token_endpoint'], false, $token_context);

    if ($token_result === FALSE) {
        $failed[] = $row['vid'];
        continue;
    }

    $token_res_data = json_decode($token_result, true);

    $access_token = $token_res_data['access_token'];
    $refresh_token = $token_res_data['refresh_token'];

    $query2 = "UPDATE user_data SET
    refresh_token = :refresh_token,
    refresh_token_date = NOW()
    WHERE vid = :vid";

    $updateUser = $pdo->prepare($query2);
    $updateUser->bindParam(':vid', $row['vid']);
    $updateUser->bindParam(':refresh_token', $refresh_token);

    if ($updateUser->execute()) {
        $successful[] = $row['vid'];
    }
}

// $mail = new PHPMailer();
// $mail->IsSMTP();
// $mail->Host = "yourhost.com";
// $mail->SMTPAuth = true;
// $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
// $mail->Port = 465;

// $mail->Username = 'yourmail@example.com';
// $mail->Password = '';

// $mail->From = 'noreply@example.com';
// $mail->FromName = 'From Jon Doe';
// $mail->AddAddress('yourmail@example.com', 'Jon Doe');
// $mail->AddReplyTo('yourmail@example.com', 'Jon Doe');

// $mail->IsHTML(true);

// $mail->Subject = 'CRON - Token refresh';

// $body = "All: " . count($rows) . "<br>";
// $body .= "Successful: " . count($successful) . "<br>";
// $body .= "Unsuccessful: " . count($failed) . "<br>";
// if (count($failed) != 0) {
//     $body .= "Unsuccessful VIDs: " . join(", ", $failed) . "<br>";
// }
// $body .= date("Y-m-d H:i:s")." UTC";

// $mail->Body = $body;

// if (!$mail->Send()) {
//     exit;
// }
