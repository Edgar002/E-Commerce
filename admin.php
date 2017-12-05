<?php
	session_start();
	include_once('auth-process.php');
	if(!ierg4210_auth_token()){
		header('Location: login.php');
		exit();
	}
	if(!ierg4210_auth_admin()){
		header('Location: main.html');
		echo 'while(1);false';
		exit();
	} 
?>
<html>
<head>
	<meta charset="utf-8" />
	<title>IERG4210 Shop - Admin Panel</title>
	<link href="incl/admin.css" rel="stylesheet" type="text/css"/>
</head>

<body>
<div id="headerbar">
		
	<h1>IERG4210 Shop - Admin Panel (Demo)</h1>
	<div id="userInfo">
			<p id ="username"><?php echo $_SESSION['t4210']['em']?></p> 
			<button id="logoutbtn">Logout</button>
	</div>
	
</div>
<article id="main">

<section id="categoryPanel">
	<fieldset>
		<legend>New Category</legend>
		<form id="cat_insert" method="POST" action="admin-process.php?action=cat_insert" onsubmit="return false;">
			<label for="cat_insert_name">Name</label>
			<div><input id="cat_insert_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('cat_insert'); ?>"/>
			<input type="submit" value="Submit" />
		</form>
	</fieldset>
	
	<!-- Generate the existing categories here -->
	<ul id="categoryList"></ul>
</section>

<section id="categoryEditPanel" class="hide">
	<fieldset>
		<legend>Editing Category</legend>
		<form id="cat_edit" method="POST" action="admin-process.php?action=cat_edit" onsubmit="return false;">
			<label for="cat_edit_name">Name</label>
			<div><input id="cat_edit_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>
			<input type="hidden" id="cat_edit_catid" name="catid" />
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('cat_edit'); ?>"/>
			<input type="submit" value="Submit" /> <input type="button" id="cat_edit_cancel" value="Cancel" />
		</form>
	</fieldset>
</section>

<section id="productPanel">
	<fieldset>
		<legend>New Product</legend>
		<form id="prod_insert" method="POST" action="admin-process.php?action=prod_insert" enctype="multipart/form-data" onsubmit="return false;">
			<label for="prod_insert_catid">Category *</label>
			<div><select id="prod_insert_catid" name="catid"></select></div>

			<label for="prod_insert_name">Name *</label>
			<div><input id="prod_insert_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>

			<label for="prod_insert_price">Price *</label>
			<div><input id="prod_insert_price" type="text" name="price" required="true" pattern="^[\d\.]+$" /></div>

			<label for="prod_insert_description">Description</label>
			<div><textarea id="prod_insert_description" name="description" pattern="^[\w\- ]*$"></textarea></div>

			<label for="prod_insert_name">Image *</label>
			<div><input type="file" name="file" required="true" accept="image/jpeg" /></div>

			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('prod_insert'); ?>"/>
			<input type="submit" value="Submit" />
		</form>
	</fieldset>
	
	<!-- Generate the corresponding products here -->
	<ul id="productList"></ul>

</section>

<section id="productEditPanel" class="hide">
	<!-- 
		Design your form for editing a product's catid, name, price, description and image	
		- the original values/image should be prefilled in the relevant elements (i.e. <input>, <select>, <textarea>, <img>)
		- prompt for input errors if any, then submit the form to admin-process.php (AJAX is not required)
	-->
	<fieldset>
		<legend>Editing Product</legend>
		<form id="prod_edit" method="POST" action="admin-process.php?action=prod_edit" enctype="multipart/form-data" onsubmit="return false;">
			
			<label for="prod_edit_catid">Category *</label>
			<div><select id="prod_edit_catid" name="catid"></select></div>

			<label for="prod_edit_name">Name *</label>
			<div><input id="prod_edit_name" type="text" name="name" required="true" pattern="^[\w\- ]+$" /></div>

			<label for="prod_edit_price">Price *</label>
			<div><input id="prod_edit_price" type="text" name="price" required="true" pattern="^[\d\.]+$" /></div>

			<label for="prod_edit_description">Description</label>
			<div><textarea id="prod_edit_description" name="description" pattern="^[\w\- ]*$"></textarea></div>

			<label for="prod_insert_name">Image</label>
			<div><img id="prod_edit_image" src=""></img></div>
			<div><input type="file" name="file" accept="image/jpeg"/></div>
			
			<input type="hidden" id="prod_edit_pid" name="pid" />
			<input type="hidden" name="nonce" value="<?php echo csrf_getNonce('prod_edit'); ?>"/>

			<input type="submit" value="Submit" /> <input type="button" id="prod_edit_cancel" value="Cancel" />
		</form>
	</fieldset>
</section>


<div class="clear"></div>
<section id="orderPanel">
	<fieldset>
		<legend>Latest 50 Transcation Records</legend>
		<table id="orderList">
			<tr><td><span>OrderID</span></td> <td><span>User Account</span></td> <td><span>Digest</span></td> <td><span>Salt</span></td> <td><span>Transaction ID</span></td> </tr>
		</table>
	</fieldset>	
</section>
</article>
<script type="text/javascript" src="incl/myLib.js"></script>
<script type="text/javascript">
(function(){

	function updateUI() {
		myLib.get({action:'cat_fetchall'}, function(json){
			// loop over the server response json
			//   the expected format (as shown in Firebug): 
			for (var options = [], listItems = [],
					i = 0, cat; cat = json[i]; i++) {
				options.push('<option value="' , parseInt(cat.catid) , '">' , cat.name.escapeHTML() , '</option>');
				listItems.push('<li id="cat' , parseInt(cat.catid) , '"><span class="name">' , cat.name.escapeHTML() , '</span> <span class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
			}
			el('prod_insert_catid').innerHTML = '<option></option>' + options.join('');
			el('prod_edit_catid').innerHTML = '<option></option>' + options.join('');
			el('categoryList').innerHTML = listItems.join('');
		});
		el('productList').innerHTML = '';

		myLib.get({action:'order_fetchall'}, function(json){
			// loop over the server response json
			//   the expected format (as shown in Firebug): 
			for (var listItems = [], i = 0, order; order = json[i]; i++) {
				if(order.userid == null) order.userid = "Guest";		
				listItems.push('<tr><td>', parseInt(order.oid) ,'</td> <td>', order.userid.escapeHTML() ,'</td> <td>', order.digest.escapeHTML() ,'</td> <td>', order.salt.escapeHTML() ,'</td> <td>', order.tid.escapeHTML() ,'</td> </tr>');
			}

			el('orderList').innerHTML += listItems.join('');
		});
		
	}
	updateUI();
	
	el('productList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;
		
		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^prod/, ''),
			name = target.parentNode.querySelector('.name').innerHTML;
			price = target.parentNode.querySelector('.price').innerHTML;
			catid = target.parentNode.querySelector('.catid').innerHTML;
			description = target.parentNode.querySelector('.description').innerHTML;
		// handle the delete click
		if ('delete' === target.className) {
			confirm('Sure?') && myLib.post({action: 'prod_delete', pid: id}, function(json){
				alert('"' + name + '" is deleted successfully!');
				updateUI();
			});
		
		// handle the edit click
		} else if ('edit' === target.className) {
			// toggle the edit/view display
			el('productEditPanel').show();
			el('productPanel').hide();
			
			// fill in the editing form with existing values
			el('prod_edit_name').value = name;
			el('prod_edit_pid').value = id;
			el('prod_edit_price').value = price;
			el('prod_edit_description').value = description;
			el('prod_edit_catid').value = catid;
			el('prod_edit_image').src = "incl/img/"+id+".jpg";
			
		
		//handle the click on the category name
		} 
	}

	el('categoryList').onclick = function(e) {
		if (e.target.tagName != 'SPAN')
			return false;
		
		var target = e.target,
			parent = target.parentNode,
			id = target.parentNode.id.replace(/^cat/, ''),
			name = target.parentNode.querySelector('.name').innerHTML;
		
		// handle the delete click
		if ('delete' === target.className) {
			confirm('Sure?') && myLib.post({action: 'cat_delete', catid: id}, function(json){
				alert('"' + name + '" is deleted successfully!');
				updateUI();
			});
		
		// handle the edit click
		} else if ('edit' === target.className) {
			// toggle the edit/view display
			el('categoryEditPanel').show();
			el('categoryPanel').hide();
			
			// fill in the editing form with existing values
			el('cat_edit_name').value = name;
			el('cat_edit_catid').value = id;
		
		//handle the click on the category name
		} else {
			var xmlHttp = new XMLHttpRequest();
			xmlHttp.onreadystatechange = function() { 
				if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
					result = xmlHttp.responseText.split('while(1);');
					result = JSON.parse(result[1]);
					result = result["success"];					
					for (var listItems = [], i = 0, prod; prod = result[i]; i++) {
						listItems.push('<li id="prod' , parseInt(prod.pid) , '"><span class="name">' , prod.name.escapeHTML() , 
						'</span> <span class="price hide">' , Number(prod.price) , '</span> <span class="catid hide">' , parseInt(prod.catid) , 
						'</span> <span class="description hide">' ,  prod.decription.escapeHTML() , '</span> <span class="delete">[Delete]</span> <span class="edit">[Edit]</span></li>');
					}
					el('productList').innerHTML = listItems.join('');
				}	
			}
			xmlHttp.open("GET", "admin-process.php?action=prod_fetchall&catid="+id, true); // true for asynchronous 
			xmlHttp.send();

			el('prod_insert_catid').value = id;
			// populate the product list or navigate to admin.php?catid=<id>
			
		}
	}
	
	
	el('cat_insert').onsubmit = function() {
		return myLib.submit(this, updateUI);
	}

	el('prod_insert').onsubmit = function() {
		form = this;
		for (var i = 0, p, el, els = form.elements; el = els[i]; i++) {
			// bypass any disabled controls
			if (el.disabled) continue;
			// validate empty field, radio and checkboxes
			if (el.hasAttribute('required')) {
				if( el.value == ''){
					alert("missing input: "+el.name); 
					return false;
				}	
			}
			if (p = el.getAttribute('pattern') && new RegExp(p).test(el.value)){
				alert("invalid input: "+el.name);  
				return false;
			}
		}
	}

	el('cat_edit').onsubmit = function() {
		return myLib.submit(this, function() {
			// toggle the edit/view display
			el('categoryEditPanel').hide();
			el('categoryPanel').show();
			updateUI();
		});
	}

	el('prod_edit').onsubmit = function() {
		form = this;
		for (var i = 0, p, el, els = form.elements; el = els[i]; i++) {
			// bypass any disabled controls
			if (el.disabled) continue;
			// validate empty field, radio and checkboxes
			if (el.hasAttribute('required')) {
				if(el.value == ''){
					alert("missing input: "+el.name); 
					return false;
				}	
			}
			if (p = el.getAttribute('pattern') && new RegExp(p).test(el.value)){
				alert("invalid input: "+el.name); 
				return false;
			}
		}
	}

	el('cat_edit_cancel').onclick = function() {
		// toggle the edit/view display
		el('categoryEditPanel').hide();
		el('categoryPanel').show();
	}


	el('prod_edit_cancel').onclick = function() {
		// toggle the edit/view display
		el('productEditPanel').hide();
		el('productPanel').show();
	}

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
