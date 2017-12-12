<?php
    session_start();
    include_once('auth-process.php');
    include_once('lib/db.inc.php');
    
    if(ierg4210_auth_token()){
		header('Location: user-portal.php');
		exit();
	}
	
    function ierg4210_signUp() 
    {
      if(empty($_POST['email']) || empty($_POST['pw']) || empty($_POST['cpw'])
        || !preg_match("/^[^@]+@[^@]+$/",$_POST['email'])
        || !preg_match("/^[\w@#$%\^\&\*\-]+$/",$_POST['pw'])
        || !preg_match("/^[\w@#$%\^\&\*\-]+$/",$_POST['cpw'])
        || $_POST['pw'] != $_POST['cpw'] )
        {
            header('Content-Type: text/html; charset=utf-8');
            echo 'invalid email or password! <br/><a href="javascript:history.back();">Back to Sign-Up Page.</a>';
            exit();
        }
        
        // Implement the sign-up logic here
        $salt = mt_rand() . mt_rand();
        $saltPassword = hash_hmac('sha1', $_POST['pw'], $salt);
        global $db;
        $db = ierg4210_DB();
        $q = $db->prepare("INSERT INTO account (email, password, salt, type) VALUES (?,?,?,?);");
       
        return $q->execute(array($_POST['email'] , $saltPassword, $salt, "user"));
       
    }
    
    

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        if($_REQUEST['action']=="signUp" && csrf_verifyNonce($_REQUEST['action'], $_POST['nonce'])){
            if(ierg4210_signUp()){
                header('Content-Type: text/html; charset=utf-8');
                echo 'Registration Success !!!<br/><a href="https://secure.s19.ierg4210.ie.cuhk.edu.hk/login.php">Go to Login Page.</a>';
                exit();
            }
            else{
                header('Content-Type: text/html; charset=utf-8');
                echo 'invalid email or password! <br/><a href="javascript:history.back();">Back to Sign-Up Page.</a>';
                exit();
            }
        }    
        else{
            header('Location: signup.php', true,302);
            exit();
        }
    }

    
?>

<html>
<head>
	<meta charset="utf-8" />
	<title>Fourth-Dimensional Pocket - Sign-Up Page</title>
	<link href="incl/signup.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<h1><a href="https://secure.s19.ierg4210.ie.cuhk.edu.hk/main.html">Fourth-Dimensional Pocket - Sign-Up Page</a></h1>
<article id="main">
<section id="SignUpPanel">
	<fieldset>
        <legend>Sign-Up</legend>
        <form id="signUp" method="POST" action="signup.php?action=<?php echo ($action = 'signUp'); ?>">
            
            <div><label>Email  :</label><input type = "email" name = "email" required/></div>
            <div><label>Password  :</label><input type = "password" name = "pw" id="password" required/></div>
            <div><label>Confirm Password :</label><input type="password" name = "cpw" id="confirm_password" required></div>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
            <div><input  type="submit" value="Submit" /></div>
            
           
		</form>
	</fieldset>
	
</section>


<div class="clear"></div>
</article>
<script type="text/javascript">
var password = document.getElementById("password")
  , confirm_password = document.getElementById("confirm_password");

function validatePassword(){
  if(password.value != confirm_password.value) {
    confirm_password.setCustomValidity("Passwords Don't Match");
  } else {
    confirm_password.setCustomValidity('');
  }
}

password.onchange = validatePassword;
confirm_password.onkeyup = validatePassword;
</script>
</body>
</html>


