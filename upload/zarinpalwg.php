<?php
	
	$client = new SoapClient('https://de.zarinpal.com/pg/services/WebGate/wsdl', array('encoding'=>'UTF-8'));
	$res = $client->PaymentRequest(
					array(
						'MerchantID' 	=> $_POST['zp_mid'],
						'Amount' 	=> $_POST['zp_amount'],
						'Description' 	=> $_POST['zp_comments'],
						'Email' 	=> '',
						'Mobile' 	=> '',
						'CallbackURL' 	=> $_POST['zp_callback_url']
						)
					);
	
	if($res->Status == 100 ){
		Header('Location: https://www.zarinpal.com/pg/StartPay/' . $res->Authority);
	} else {
		echo'ERR: '. $res->Status;
	}
?>
