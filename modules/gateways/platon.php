<?php

function platon_config() {
    $configarray = array(
        "FriendlyName" => array("Type" => "sytem", "Value" => "Platon"),
        "key" => array("FriendlyName" => "Client Key", "Type" => "text", "Size" => "50"),
        "password" => array("FriendlyName" => "Password", "Type" => "text", "Size" => "50"),
        "gateway_url" => array("FriendlyName" => "Gateway URL", "Type" => "text", "Size" => "50", "Value" => "https://secure.platononline.com/payment/auth")
    );
    return $configarray;
}

function platon_link($params) {
    $url = $params['systemurl'] . '/viewinvoice.php?id='.$params['invoiceid'];
    /* Prepare product data for coding */
    $data = base64_encode(
            json_encode(
                    array(
                        'amount' => sprintf("%01.2f", $params['amount']),
                        'name' => 'Order from ' . $params['companyname'],
                        'currency' => $params['currency']
                    )
            )
    );

    /* Calculation of signature */
    $sign = md5(
            strtoupper(
                    strrev($params['key']) .
                    strrev($data) .
                    strrev($url) .
                    strrev($params['password'])
            )
    );

    # Enter your code submit to the gateway...

    $code = '<form method=post action="'.$params['gateway_url'].'" >
		<input type="hidden" name="key" value="' . $params['key'] . '" />
		<input type="hidden" name="order" value="' . $params['invoiceid'] . '" />
		<input type="hidden" name="url" value="' . $url .'" />
		<input type="hidden" name="data" value="' . $data . '" />
        	<input type="hidden" name="sign" value="' . $sign . '" />
		<input type="submit" value="Pay Now" />		
		</form>
	';


    return $code;
}

?>