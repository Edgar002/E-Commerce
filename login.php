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
    <title>Fourth-Dimensional Pocket - Login Page</title>
    <link href="incl/login.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript">
        window.fbAsyncInit = function() {
            FB.init({
            appId      : '132032470808245',
            cookie     : true,
            xfbml      : true,
            version    : 'v2.11'
            });
            
            FB.AppEvents.logPageView(); 
            FB.getLoginStatus(function(response) {
            statusChangeCallback(response);
            FB.Event.subscribe('auth.login', function (response) {
                location.href = 'https://secure.s19.ierg4210.ie.cuhk.edu.hk/main.html';
            });
        });  
        };
        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        function statusChangeCallback(response) {
    
            if (response.status === 'connected') {
                location.href = 'https://secure.s19.ierg4210.ie.cuhk.edu.hk/main.html';
            } 
        }


        function checkLoginState() {
            FB.getLoginStatus(function(response) {
                statusChangeCallback(response);
            });
        }


    </script>    
</head>
<body>
<h1><a href="https://secure.s19.ierg4210.ie.cuhk.edu.hk/main.html">Fourth-Dimensional Pocket - Login Page</a></h1>
<article id="main">
<section id="loginPanel">
	<fieldset>
        <legend>Login</legend>
        <form id="login" method="POST" action="login.php?action=<?php echo ($action = 'login'); ?>">
            
            <div><label>Email  :</label><input type = "email" name = "email" required/></div>
            <div><label>Password  :</label><input type = "password" name = "pw" required/></div>
            <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
            <div id = "loginbtns">
                <input  id= "loginbtn" type="submit" value="Login" />
                <button id = "signupbtn" onclick="location.href = 'https://secure.s19.ierg4210.ie.cuhk.edu.hk/signup.php';" type="button">SignUp</button>
                <div class="fb-login-button" data-max-rows="1" data-size="medium" data-button-type="login_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="true"></div>
                
            </div>
           
		</form>
	</fieldset>
	
</section>


<div class="clear"></div>
</article>
</body>
</html>

