<?php

# Required File Includes
include("../../../dbconnect.php");
include("../../../includes/functions.php");
include("../../../includes/gatewayfunctions.php");
include("../../../includes/invoicefunctions.php");

$log = __DIR__ . '/callback.log';
// log callback data
file_put_contents($log, var_export($_POST, 1) . "\n\n", FILE_APPEND);


$gatewaymodule = "platon"; # Enter your gateway module name here replacing template
$GATEWAY = getGatewayVariables($gatewaymodule);

if (!$GATEWAY["type"])
    die("Module Not Activated");# Checks gateway module is active before accepting callback

logTransaction($GATEWAY["name"], $_POST, "Callback is called"); # Save to Gateway Log: name, data array, status

checkCbInvoiceID($_POST["order"], $GATEWAY["name"]); # Checks invoice ID is a valid invoice number or ends processing
// generate signature from callback params
$sign = md5(
        strtoupper(
                strrev($_POST['email']) .
                $GATEWAY['password'] .
                $_POST['order'] .
                strrev(substr($_POST['card'], 0, 6) . substr($_POST['card'], -4))
        )
);

// verify signature
if ($_POST['sign'] !== $sign) {
    die("ERROR: Bad signature");
}

switch ($_POST['status']) {
    case 'SALE':
        checkCbTransID($_POST["id"]); # Checks transaction number isn't already in the database and ends processing if it does
        addInvoicePayment($_POST["order"], $_POST["id"], $_POST["amount"], 0, $gatewaymodule); # Apply Payment to Invoice: invoiceid, transactionid, amount paid, fees, modulename
        break;
    case 'REFUND':
        $command = "updateinvoice";
        $adminuser = 1;
        $values["invoiceid"] = $_POST['order'];
        $values["status"] = "Refunded";
        $values["paymentmethod"] = "platon";

        $results = localAPI($command, $values, $adminuser);
        break;
    case 'CHARGEBACK':
        break;
    default:
        die("ERROR: Invalid callback data");
}

exit("OK");
?>