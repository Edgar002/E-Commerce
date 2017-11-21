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
            $_SESSION['t4210']['type']=$r['type'];
            
            return $r['type'];
        }
        else
            return false;
    }
    else
        return false;


}

function ierg4210_auth_admin(){
    session_start();
    if($_SESSION['t4210']['type']=='admin')
        return true;
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
                if($realk == $t['k']){
                    $_SESSION['t4210'] = $t;
                    $_SESSION['t4210'] = $r['type'];
                    return $t['em'];
                }
            }

        }
    }
    return false;    
}

function csrf_getNonce($action){
    // Generate a nonce with mt_rand()
    $nonce = mt_rand() . mt_rand();
    
    // With regard to $action, save the nonce in $_SESSION 
    if (!isset($_SESSION['csrf_nonce'])) 
        $_SESSION['csrf_nonce'] = array();
    $_SESSION['csrf_nonce'][$action] = $nonce;
    
    // Return the nonce
    return $nonce;
}

// Check if the nonce returned by a form matches with the stored one.
function csrf_verifyNonce($action, $receivedNonce){
    // We assume that $REQUEST['action'] is already validated
    if (isset($receivedNonce) && $_SESSION['csrf_nonce'][$action] == $receivedNonce) {
        if ($_SESSION['authtoken']==null)
            unset($_SESSION['csrf_nonce'][$action]);
        return true;
    }
    return false;
}

function ierg4210_logout() {
    session_start();
    // clear the cookies and session
    session_destroy();
}


// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
//   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
if($_REQUEST['action']=='auth_token') {
  
    $returnVal = call_user_func('ierg4210_' . $_REQUEST['action']);
    echo  'while(1);' . json_encode(array('success' =>$returnVal));
    exit();
}

if($_REQUEST['action']=='logout') {
     call_user_func('ierg4210_' . $_REQUEST['action']);
     echo  'while(1);';
     exit();
}

if($_REQUEST['action']=='csrf_getNonce') {
    call_user_func('ierg4210_' . $_REQUEST['action']);
    echo  'while(1);';
    exit();
}

?>