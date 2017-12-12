<?php
	session_start();
	include_once('lib/db.inc.php');
	include_once('auth-process.php');


	if(!ierg4210_auth_token()){
		header('Location: login.php');
		echo 'while(1);false';
		exit();
	}
	

	if($_SERVER["REQUEST_METHOD"] == "POST") {
        if(!csrf_verifyNonce($_REQUEST['action'], $_POST['nonce'])){
            echo 'while(1);false';
            exit();
        }
	}

    function ierg4210_changePassword(){

        if(empty($_POST['oldPW']) || empty($_POST['pw']) || empty($_POST['cpw'])
        ||  !preg_match("/^[\w@#$%\^\&\*\-]+$/",$_POST['oldPW'])
        || !preg_match("/^[\w@#$%\^\&\*\-]+$/",$_POST['pw'])
        || !preg_match("/^[\w@#$%\^\&\*\-]+$/",$_POST['cpw'])
        || $_POST['pw'] != $_POST['cpw'] )
        {
            header('Content-Type: text/html; charset=utf-8');
            echo 'invalid new password! Please try again!<br/><a href="javascript:history.back();">Back to User Portal.</a>';
            exit();
        }
        
        $email =  ierg4210_auth_token();
        
        if($email != false){
            global $db;
            $db = ierg4210_DB();
        
            $q=$db->prepare('SELECT * FROM account WHERE email = ?');
            $q->execute(array($email));
            
            if($r=$q->fetch()){		
                
                $saltPassword = hash_hmac('sha1', $_POST['oldPW'], $r['salt']);
        
                if($saltPassword == $r['password']){
                    $newSaltPassword = hash_hmac('sha1', $_POST['pw'], $r['salt']);
                    $sql="UPDATE account SET password = ?  WHERE email = ?;";
                    $q = $db->prepare($sql);
                    $q->execute(array($newSaltPassword,$email));
                    ierg4210_logout();
                    header('Content-Type: text/html; charset=utf-8');
                    echo 'Change password success!<br/><a href="https://secure.s19.ierg4210.ie.cuhk.edu.hk/login.php">Go to Login Page.</a>';
                    exit();
                    
                }
                else
                {
                    header('Content-Type: text/html; charset=utf-8');
                    echo 'Wrong old password! Please try again!<br/><a href="javascript:history.back();">Back to User Portal.</a>';
                    exit();
                }
            }
            else
                return false;
        }
    }


	function ierg4210_order_fetch() {

        $email =  ierg4210_auth_token();
        // DB manipulation
        if($email != false){
            global $db;
            $db = order_DB();
            
            $q = $db->prepare("SELECT * FROM orders WHERE userid = ? AND tid != 'notyet' ORDER BY oid DESC LIMIT 5;");
            $q->execute(array($email));
            $orders = $q->fetchAll();

            $j = 0;
            $db = ierg4210_DB();
            
            foreach($orders as $order){
  
                

                $productList = $order["productlist"];
                $pieces = explode("|", $productList);
                $pidList = $pieces[0];
                $quantityList = $pieces[1];
                $priceList = $pieces[2];
                
                $quantity = explode(",", $quantityList);
                $price = explode(",", $priceList);

                $query="SELECT name FROM products WHERE pid IN ($pidList);";
                $nameList=$db->query($query);
                $nameList=$nameList->fetchAll();

                $order["productlist"] = "";
                for($i = 0; $i < count($nameList); $i++)
                    $order["productlist"] .=  $nameList[$i]['name'] ." Quantity: ".$quantity[$i]." Price: $".$price[$i]."  |  ";

                
                $orders[$j++]["productlist"] = $order["productlist"];
            }
            return $orders;
        }    
            
	}

	

	header('Content-Type: application/json');

	// input validation
	if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
		echo json_encode(array('failed'=>'undefined'));
		exit();
	}

	// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
	//   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
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