<?php
	include_once('lib/db.inc.php');
	error_reporting(E_ALL ^ E_NOTICE);

	$header = "";

	// Read the post from PayPal and add 'cmd' 
	$req = 'cmd=_notify-validate'; 
	if(function_exists('get_magic_quotes_gpc')) 
	{ 
		$get_magic_quotes_exists = true; 
	} 
	foreach ($_POST as $key => $value) 
	// Handle escape characters, which depends on setting of magic quotes
	{ 
		if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1){
			$value = urlencode(stripslashes($value)); 
		}
		else { 
			$value = urlencode($value); 
		} 
		$req .= "&$key=$value";
	}

	// Post back to PayPal to validate 
	$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
	$header .= "Host: www.paypal.com\r\n";
	$header .= "Content-Type:application/x-www-form-urlencoded\r\n"; 
	$header .= "Content-Length:" . strlen($req) . "\r\n";
	$header .= "Connection: close\r\n\r\n";


	$fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);

	// Process validation from PayPal
	// TODO: This sample does not test the HTTP response code. All 
	// HTTP response codes must be handles or you should use an HTTP library, such as cUrl 
	if (!$fp) { // HTTP ERROR

	} else { // NO HTTP ERROR 
		fputs ($fp, $header . $req); 

		while (!feof($fp)) {
			
			$res = trim(fgets ($fp, 1024));

			if (strcmp ($res, "VERIFIED") == 0) { 
				// TODO: Check the payment_status is Completed	

				if (empty($_POST['payment_status'])||$_POST['payment_status']!='Completed')
					break; //The payment is not complete
				
				// Check that txn_id has not been previously processed
				global $db;
				$db = order_DB();
				$q = $db->prepare("SELECT * FROM orders WHERE oid = ? LIMIT 100;");
				if ($q->execute(array($_POST['invoice'])))
					$order=$q->fetchAll();
				
				if ($q->rowCount() >= 1 && $order[0]['tid']==$_POST['txn_id'])
					break;
				

				// Check the txn_type is cart. 
				if($_POST['txn_type']!="cart")
					break;

				// Check that receiver_email is your Primary PayPal email
				if($_POST['receiver_email']!="edgar6a28-facilitator@yahoo.com.hk")
					break;
				
				// Check that payment_amount/payment_currency are correct
				$pidQuantity = "";
				$currentPrice = "";
				$i = 1;
				while ($_POST['item_number'.$i]) {
					$pidQuantity = $pidQuantity.((int)$_POST['item_number'.$i]). ((int)$_POST['quantity'.$i]);
					$currentPrice = $currentPrice.((float)$_POST['mc_gross_'.$i]);
					$i++;
				}
				$digest = sha1($_POST['mc_currency']. $_POST['business']. $order[0]['salt']. $pidQuantity . $currentPrice. (float)$_POST['mc_gross']);
				$q = $db->prepare("UPDATE orders SET tid = ? WHERE oid = ?");
				$q->execute(array($pidQuantity, 1));
				$q->execute(array($_POST['mc_currency']. $_POST['business']. $order[0]['salt']. $pidQuantity . $currentPrice. (float)$_POST['mc_gross'], 2));
				// Process payment
				if ($digest==$order[0]['digest'])
				{
					$q = $db->prepare("UPDATE orders SET tid = ? WHERE oid = ?");
					$q->execute(array($_POST['txn_id'], $_POST['invoice']));
					
				}else {
					$q = $db->prepare("UPDATE orders SET tid = ? WHERE oid = ?");
					$q->execute(array("Wrong Digest", $_POST['invoice']));
				}
				// If 'VERIFIED', send email of IPN variables and values to specified email address 
				foreach ($_POST as $key => $value){ 
					$emailtext .= $key . " = " .$value ."\n\n";
				} 
				error_log($email.'Live-VERIFIED IPN'.$emailtext .'\n\n'.$req);
				exit();
			} 
			else if (strcmp ($res, "INVALID") == 0) { 
				// If 'INVALID', send an email. TODO: Log for manual investigation.
				foreach ($_POST as $key => $value){ 
					$emailtext .= $key . " = " .$value ."\n\n"; 
					} 
				
				error_log($email.'Live-VERIFIED IPN'.$emailtext .'\n\n'.$req);
				exit();
			}
		}
	} fclose ($fp);
?>