<?php
	session_start();
	
	include_once('auth-process.php');

	if(!ierg4210_auth_token()){
		header('Location: login.php');
		exit();
	}
?>
<html>
<head>
	<meta charset="utf-8" />
	<title>Fourth-Dimensional Pocket - User Portal</title>
	<link href="incl/user-portal.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<div id="headerbar">
		
	<h1><a href="https://secure.s19.ierg4210.ie.cuhk.edu.hk/main.html">Fourth-Dimensional Pocket - User Portal</a></h1>
	<div id="userInfo">
			<p id ="username"><?php echo $_SESSION['t4210']['em']?></p> 
			<button id="logoutbtn">Logout</button>
	</div>
	
</div>
<article id="main">

<section id="UserPanel">
	<fieldset>
        <legend>User Panel</legend>
        <div><label><b>Change Password</b></label></div>
		<form id="changePassword" method="POST" action="user-portal-process.php?action=<?php echo ($action = 'changePassword'); ?>">
        <div><label>Old Password  :</label><input type = "password" name = "oldPW" required/></div>
        <div><label>New Password  :</label><input type = "password" name = "pw" id="password" required/></div>
        <div><label>Confirm New Password :</label><input type="password" name = "cpw" id="confirm_password" required></div>
        <input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
        <div><input  type="submit" value="Submit" /></div>
        </form>
	</fieldset>
	
	<!-- Generate the existing categories here -->
	
</section>

<div class="clear"></div>

<section id="OrderPanel">
	<fieldset>
		<legend>Latest 5 Transcation Records</legend>
		<table id="orderList">
			<tr><td><span>OrderID</span></td> <td><span>Transaction ID</span></td> <td><span>Product-List</span></td> </tr>
		</table>
	</fieldset>
	

</section>

</article>
<script type="text/javascript" src="incl/myLib.js"></script>
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
<script type="text/javascript">
(function(){

	function updateUI() {
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function() { 
                if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
                        result = xmlHttp.responseText.split('while(1);');
                        result = JSON.parse(result[1]);
                        result = result["success"];
                        for (var listItems = [], i = 0, order; order =  result[i]; i++) {		
                            listItems.push('<tr><td>', parseInt(order.oid) ,'</td>  <td>', order.tid.escapeHTML() ,'</td> <td>', order.productlist.escapeHTML() ,'</td> </tr>');
                        }

                        el('orderList').innerHTML += listItems.join('');				
                        
                    }	
        }
        xmlHttp.open("GET", "user-portal-process.php?action=order_fetch", true);
        xmlHttp.send();    
		
	}
	updateUI();
	

	el('logoutbtn').onclick = function() {
		var xmlHttp = new XMLHttpRequest();
					xmlHttp.onreadystatechange = function() { 
						if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
							window.location = "https://secure.s19.ierg4210.ie.cuhk.edu.hk/login.php";
						}
					}
		xmlHttp.open("GET", "auth-process.php?action=logout", true); // true for asynchronous 
		xmlHttp.send();
	}

})();
</script>
</body>
</html>
