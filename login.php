<?php
    session_start();
    include_once('auth-process.php');
    $t = ierg4210_auth_token();
    echo $t;
    if($t[0] == "admin"){
        header('Location: main.html', true,302);
        exit();
    }
    if($t[0] == "user"){
        header('Location: admin.html', true,302);
        exit();
    }

    function ierg4210_login() 
    {
      if(empty($_POST['email']) || empty($_POST['pw'])
        //|| !preg_match("/[a-zA-Z0-9_-.+]+@[a-zA-Z0-9-]+.[a-zA-Z]+/",$_POST['email']))
        || !preg_match("/^[\w@#$%\^\&\*\-]+$/",$_POST['pw']))
        {
            header('Content-Type: text/html; charset=utf-8');
            echo 'Wrong email or password! <br/><a href="javascript:history.back();">Back to Login Page.</a>';
            exit();
        }
      
        // Implement the login logic here
        echo $_POST['email'].$_POST['pw'] ;
        $login_success = ierg4210_auth($_POST['email'],$_POST['pw']);
        
        if ($login_success=='admin'){
            // redirect to admin page
            header('Location: admin.html', true,302);
            exit();
        }
        elseif ($login_success=='user'){
            header('Location: main.html', true,302);
            exit();
        }
        else{
            header('Content-Type: text/html; charset=utf-8');
            echo 'Wrong email or password! <br/><a href="javascript:history.back();">Back to Login Page.</a>';
            exit();
        }
    }

    function ierg4210_logout() {
        // clear the cookies and session
        unset($_SESSION['t4210']);
        session_destroy();
        //redirect to login page after logout
        header('Location: login.php', true,302);
    }
    
    
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        ierg4210_login();
       
    }
    
?>

<html>
<head>
	<meta charset="utf-8" />
	<title>Fourth-Dimensional Pocket - Login Page</title>
	<link href="incl/login.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<h1>Fourth-Dimensional Pocket - Login Page</h1>
<article id="main">
<section id="loginPanel">
	<fieldset>
		<legend>Login</legend>
		<form id="login" method="POST" action="login.php?action=login">
        <div><label>Email  :</label><input type = "text" name = "email" class = "box"/></div>
        <div><label>Password  :</label><input type = "password" name = "pw" class = "box" /></div>
        <div id="loginbtn"><input type="submit" value="Login" /></div>
        <input type="hidden" name="token" value=<?php ?>/>
		</form>
	</fieldset>
	
</section>


<div class="clear"></div>
</article>
</body>
</html>

