<?php
include_once('lib/db.inc.php');

function ierg4210_auth($email,$password) {

    global $db;
	$db = ierg4210_DB();

    $q=$db->prepare('SELECT * FROM account WHERE email = ?');
    $q->execute(array($email));

    if($r=$q->fetch()){		
        //Check if the hash of the password is same as saved in database		
        //If yes, create authentication information in cookies and session		
        //program code on next slide
        //expected format: $pw=hash_hmac('sha1', $plainPW, $salt);
        $saltPassword = hash_hmac('sha1', $password, $r['salt']);
        echo $saltPassword;
        if($saltPassword == $r['password']){
            $exp = time() + 3600 * 24 * 3; // 3days
            $token = array(
                'em' => $r['email'],
                'exp' => $exp,
                'k' => hash_hmac('sha1',$exp.$r['password'],$r['salt'])
            );
        // create cookie, make it HTTP only
        //setcookie() must be called before printing anything out
            setcookie('t4210', json_encode($token), $exp,'','',false,true);
            
            //put it also in the session
            $_SESSION['t4210'] = $token;
            return true;
        }
        else
            return false;
    }
    else
        return false;


}




?>