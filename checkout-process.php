<?php
    session_start();
    include_once('lib/db.inc.php');
    include_once('auth-process.php');

    
    function ierg4210_handle_checkout(){

        ierg4210_csrf_verifyNonce($_REQUEST['nonce']);
        

        $list=json_decode($_REQUEST['list']);
        $userId = $_REQUEST['userId'];
        
        
        $pid=array();
        $quantity=array();
        $i = 0;
        
        foreach ($list as $key => $value) {
            $pid[$i]= (int)$key;                      
            $quantity[$i]= (int)$value;
            $i++;
        }
       global $db;
        $db = ierg4210_DB();
        $pidList = implode(',', $pid);
        $quantityList = implode(',', $quantity);
        $query="SELECT pid , price FROM products WHERE pid IN ($pidList)";
        $products=$db->query($query);
        
        $currency="HKD";
        $email="edgar6a28-facilitator@yahoo.com.hk";
        $salt = mt_rand() . mt_rand();
        
        $price=array();
        $totalPrice=0;   
        
        $i=0;
        foreach($products as $product){
            $price[$i] = (float)$product["price"];
            $totalPrice += ((float)$product["price"]) * ((int)$quantity[$i++]);
        }

        $priceList = implode(',', $price);        
        
        $digest=sha1($currency. $email. $salt. $pidList.'|'.$quantityList.'|'.$priceList.'|'.(float)$totalPrice);
        
        $db = order_DB();
        
        $q = $db->prepare("INSERT INTO orders (digest , salt , tid, userid) VALUES (?, ?, ?, ?)");

        $q->execute(array($digest , $salt , "notyet", $userId));
        
        $invoice=$db->lastInsertId() + 200;
        $returnValue=array("digest"=>$digest, "invoice"=>$invoice);
            
        return $returnValue;
      
       
    }

    function ierg4210_csrf_getNonce(){
        // Generate a nonce with mt_rand()
        $nonce = mt_rand() . mt_rand();
        
        // With regard to $action, save the nonce in $_SESSION 
        if (!isset($_SESSION['csrf_nonce'])) 
            $_SESSION['csrf_nonce'] = array();
        $_SESSION['csrf_nonce']['handle_checkout'] = $nonce;
        
        // Return the nonce
        echo  'while(1);' . json_encode(array('success' => $_SESSION['csrf_nonce']['handle_checkout']));
        return $nonce;
    }
    
    // Check if the nonce returned by a form matches with the stored one.
    function ierg4210_csrf_verifyNonce($receivedNonce){
        // We assume that $REQUEST['action'] is already validated
        if (isset($receivedNonce) && $_SESSION['csrf_nonce']['handle_checkout'] == $receivedNonce) {
            if ($_SESSION['t4210']==null)
                unset($_SESSION['csrf_nonce']['handle_checkout']);
            return true;
        }
        throw new Exception('csrf-attack');
    }
  
    header('Content-Type: application/json');


	// input validation
	if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
		echo json_encode(array('failed'=>'undefined'));
		exit();
    }
    

	// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
	// the return values of the functions are then encoded in JSON format and used as output
	try {
		if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
			if ($db && $db->errorCode()) 
				error_log(print_r($db->errorInfo(), true));
			echo json_encode(array('failed'=>'1'));
        }
        if($returnVal == false) echo json_encode(array('failed'=>'invalid-username'));

        else   echo  'while(1);' . json_encode(array('success' => $returnVal));
        
	} catch(PDOException $e) {
		error_log($e->getMessage(),0);
		echo json_encode(array('failed'=>'error-db'));
	} catch(Exception $e) {
		echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
	}
      
     
?>