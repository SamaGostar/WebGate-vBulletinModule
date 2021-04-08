<?php
/*=============================================================================================*\
|| ########################################################################################### ||
|| # Product Name	: Zarin Pal Payment API Module for vBulletin		Version: 4.X.X
|| # By				: Zarinpal Development Team										WebSite: www.nixweb.ir
|| ########################################################################################### ||
\*=============================================================================================*/

if (!isset($GLOBALS['vbulletin']->db))
{
    exit;
}

/**
 * Class that provides payment verification and form generation functions
 *
 * @package	vBulletin
 * @version	$Revision: 20000 $
 * @date		$Date: 2012-03-25 01:24:45 +0350 (Sun, 25 March 2012) $
 */
class vB_PaidSubscriptionMethod_zarinpalqr extends vB_PaidSubscriptionMethod
{
    var $supports_recurring = false;
    var $display_feedback = true;

    function verify_payment()
    {
        $this->registry->input->clean_array_gpc('r', array(
            'item'		=> TYPE_STR,
            'Authority'	=> TYPE_STR,
            'Status'	=> TYPE_STR
        ));


        if (!$this->test())
        {
            $this->error = 'Payment processor not configured';
            return false;
        }

        $this->transaction_id = $this->registry->GPC['Authority'];
        if(!empty($this->registry->GPC['item']) AND !empty($this->registry->GPC['Authority']))
        {
            $this->paymentinfo = $this->registry->db->query_first("
				SELECT paymentinfo.*, user.username
				FROM " . TABLE_PREFIX . "paymentinfo AS paymentinfo
				INNER JOIN " . TABLE_PREFIX . "user AS user USING (userid)
				WHERE hash = '" . $this->registry->db->escape_string($this->registry->GPC['item']) . "'
			");
            if (!empty($this->paymentinfo) && $this->registry->GPC['Status'] == 'OK')
            {
                $sub = $this->registry->db->query_first("SELECT * FROM " . TABLE_PREFIX . "subscription WHERE subscriptionid = " . $this->paymentinfo['subscriptionid']);
                $cost = unserialize($sub['cost']);
                $amount = floor($cost[0][cost][usd]*$this->settings['d2t']);

                $data = array("merchant_id" => $this->settings['zpmid'], "authority" => $this->registry->GPC['Authority'], "amount" => $amount);
                $jsonData = json_encode($data);
                $ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
                curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData)
                ));

                $result = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
                $result = json_decode($result, true);
                
                if($result['data']['code'] == 100)
                {
                    $this->paymentinfo['currency'] = 'usd';
                    $this->paymentinfo['amount'] = $cost[0][cost][usd];
                    $this->type = 1;
                    return true;
                } else {
                    $this->error = 'ERR: '. $result['errors'];
                    return false;
                }
            } else {
                $this->error = 'Invalid trasaction';
                return false;
            }
        }else{
            $this->error = 'Duplicate transaction.';
            return false;
        }
    }

    function test()
    {
            if(!empty($this->settings['zpmid']) AND !empty($this->settings['d2t'])){
                return true;
            }
        
        return false;
    }

    function generate_form_html($hash, $cost, $currency, $subinfo, $userinfo, $timeinfo)
    {
        global $vbphrase, $vbulletin, $show;

        $item = $hash;
        $cost = floor($cost*$this->settings['d2t']);
        $merchantID = $this->settings['zpmid'];

        $form['action'] = 'zarinpalqr.php';
        $form['method'] = 'POST';

        $settings =& $this->settings;

        $templater = vB_Template::create('subscription_payment_zarinpalqr');
        $templater->register('merchantID', $merchantID);
        $templater->register('cost', $cost);
        $templater->register('item', $item);
        $templater->register('subinfo', $subinfo);
        $templater->register('settings', $settings);
        $templater->register('userinfo', $userinfo);
        $form['hiddenfields'] .= $templater->render();
        return $form;
    }
}

/*=============================================================================================*\
|| ########################################################################################### ||
|| # Product Name	: Zarin Pal Payment API Module for vBulletin		Version: 4.X.X
|| # By				: Zarinpal Development Team										WebSite: www.nixweb.ir
|| ########################################################################################### ||
\*=============================================================================================*/
?>
