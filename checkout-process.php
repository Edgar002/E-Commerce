<?php
    include_once('lib/db.inc.php');
    include_once('auth-process.php');

    
    function ierg4210_handle_checkout(){
        $list=json_decode($_REQUEST['list']);
        
        $pid=array();
        $quantity=array();
        $pidQuantity = "";
        $i = 0;
        
        foreach ($list as $key => $value) {
            $pid[$i]= (int)$key;                      
            $quantity[$i]= (int)$value;
            $pidQuantity = $pidQuantity.((int)$pid[$i]). ((int)$quantity[$i]);
            $i++;
        }
       global $db;
        $db = ierg4210_DB();
        $pidList = implode(', ', $pid);
        $query="SELECT pid , price FROM products WHERE pid IN ($pidList)";
        $products=$db->query($query);
        
        $currency="HKD";
        $email="edgar6a28-facilitator@yahoo.com.hk";
        $salt = mt_rand() . mt_rand();
        $currentPrice=""; 
        $totalPrice=0;   
        
        $i=0;
        foreach($products as $product){
            $currentPrice=$currentPrice.((float)$product["price"]);
            $totalPrice+=$product["price"]*$quantity[$i++];
        }
        $digest=sha1($currency. $email. $salt. $pidQuantity . $currentPrice. $totalPrice);
        
        $db = order_DB();
        //$query="INSERT INTO orders (digest, tid , salt) VALUES ($digest, "notyet", $salt)";
        $q = $db->prepare("INSERT INTO orders (digest , salt , tid) VALUES (?, ?, ?)");

        $q->execute(array($digest , $salt , "notyet"));
        
        $invoice=$db->lastInsertId();
        $returnValue=array("digest"=>$digest, "invoice"=>$invoice);
            
        return $returnValue;
      
       
    }
  
    header('Content-Type: application/json');

    if(!ierg4210_auth_token()){
		header('Location: login.php');
		echo 'while(1);false';
		exit();
    }

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
		echo  'while(1);' . json_encode(array('success' => $returnVal));
	} catch(PDOException $e) {
		error_log($e->getMessage(),0);
		echo json_encode(array('failed'=>'error-db'));
	} catch(Exception $e) {
		echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
	}
      
     
?>