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
            
            return $r['type'];
        }
        else
            return false;
    }
    else
        return false;


}

function ierg4210_auth_token(){
    session_start();
    global $db;
    $db = ierg4210_DB();
    
    if(!empty($_SESSION['t4210']))
        return $_SESSION['t4210']['em'];
    if(!empty($COOKIE['t4210'])){
        //stripslashes() Returns a string with backslashes stripped off.
        // (\' becomes ' and so on.)
        if($t = json_decode(stripslashes($_COOKIE['t4210']),true)){
            if(time()>$t['exp']) return false; // to expire the user
            $q=$db->prepare('SELECT * FROM account WHERE email = ?');
            $q->execute(array($t['em']));
            if($r=$q->fetch()){
                //expected format: $pw=hash_hmac('sha1', $exp.$PW, $salt);
                $realk = hash_hmac('sha1', $t['exp'].$r['password'], $r['salt']);
                if($realk == $t['k'] && $r['type'] == 'admin'){
                    $_SESSION['t4210'] = $t;
                    return $t['em'];
                }
            }

        }
    }
    return false;    
}


// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
//   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
// the return values of the functions are then encoded in JSON format and used as output
if($_REQUEST['action']=='auth_token') {
  
    $returnVal = call_user_func('ierg4210_' . $_REQUEST['action']);
       
    echo  'while(1);' . json_encode(array('success' =>$returnVal));
    
}

?>