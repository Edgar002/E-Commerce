<?php
    session_start();
    include_once('auth-process.php');
    
    $t = ierg4210_auth_token();
    if($t){
        header('Location: admin.php', true,302);
        exit();
    }
   
    function ierg4210_login() 
    {
      if(empty($_POST['email']) || empty($_POST['pw'])
        || !preg_match("/^[^@]+@[^@]+$/",$_POST['email'])
        || !preg_match("/^[\w@#$%\^\&\*\-]+$/",$_POST['pw']))
        {
            header('Content-Type: text/html; charset=utf-8');
            echo 'Wrong email or password! <br/><a href="javascript:history.back();">Back to Login Page.</a>';
            exit();
        }
      
        // Implement the login logic here
        $login_success = ierg4210_auth($_POST['email'],$_POST['pw']);
        
        if ($login_success=='admin'){
            session_regenerate_id();            
            // redirect to admin page
            header('Location: admin.php', true,302);
            exit();
        }
        elseif ($login_success=='user'){
            session_regenerate_id();   
            header('Location: main.html', true,302);
            exit();
        }
        else{
            header('Content-Type: text/html; charset=utf-8');
            echo 'Wrong email or password! <br/><a href="javascript:history.back();">Back to Login Page.</a>';
            exit();
        }
    }
    
    

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        if($_REQUEST['action']=="login" && csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']))
            ierg4210_login();
        else{
            header('Location: login.php', true,302);
            exit();
        }
    }

    
?>

<html>
<head>
	<meta charset="utf-8" />
	<title>Fourth-Dimensional Pocket - Sign-Up Page</title>
	<link href="incl/login.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<h1>Fourth-Dimensional Pocket - Sign-Up Page</h1>
<article id="main">
<section id="SignUpPanel">
	<fieldset>
        <legend>Login</legend>
        <form id="login" method="POST" action="login.php?action=<?php echo ($action = 'signUp'); ?>">
            
            <div><label>Email  :</label><input type = "email" name = "email" required:"true"/></div>
            <div><label>Password  :</label><input type = "password" name = "pw" required:"true"/></div>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
            <div><input  type="submit" value="Login" /></div>
            
           
		</form>
	</fieldset>
	
</section>


<div class="clear"></div>
</article>
</body>
</html>

