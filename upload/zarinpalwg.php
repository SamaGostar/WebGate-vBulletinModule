<?php
$data = array(
    'merchant_id' => $_POST['zp_mid'],
    'amount' => $_POST['zp_amount'],
    'description' => $_POST['zp_comments'],
    'callback_url' => $_POST['zp_callback_url']
);
$jsonData = json_encode($data);

$ch = curl_init('https://api.zarinpal.com/pg/v4/payment/request.json');
curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
));


$result = curl_exec($ch);
$err = curl_error($ch);
$result = json_decode($result, true, JSON_PRETTY_PRINT);
curl_close($ch);

if ($result['data']['code'] == 100) {

    header('Location: https://www.zarinpal.com/pg/StartPay/' . $result['data']["authority"]);

} else {
    echo 'ERR: ' .  $result['errors']['code'];
}

?>






