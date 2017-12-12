(function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));  
    
var xmlHttp = new XMLHttpRequest();
xmlHttp.onreadystatechange = function() { 
    if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
        nonce = xmlHttp.responseText.split('while(1);');
        nonce = JSON.parse(nonce[1]);
        nonce = nonce["success"];	
        el("nonce").value = nonce;
               
    }
}            
xmlHttp.open("GET", "checkout-process.php?action=csrf_getNonce", false); // true for asynchronous 
xmlHttp.send();

fbname = null;
var xmlHttp = new XMLHttpRequest();
xmlHttp.onreadystatechange = function() { 
            if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
                
                userName = xmlHttp.responseText.split('while(1);');
                userName = JSON.parse(userName[1]);
                userName = userName["success"];	

                if(userName){
                    el("username").innerHTML = userName;
                    el('logoutbtn').show();
                }
                else{
                    el("username").innerHTML = "Guest";
                    el('loginbtn').show();
                }		
            }
}            

window.fbAsyncInit = function() {
    FB.init({
    appId      : '132032470808245',
    cookie     : true,
    xfbml      : true,
    version    : 'v2.11'
    });
    
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            var uid = response.authResponse.userID;
            FB.api('/'+uid, {fields: 'name'}, function(response) {
                fbname = response.name;
                el("username").innerHTML = fbname;
                el('logoutbtn').show();
              });
        }
        else{
        xmlHttp.open("GET", "auth-process.php?action=auth_token", true); // true for asynchronous 
        xmlHttp.send(); }
      } );

      

};

function fbLogoutUser() {
        FB.getLoginStatus(function(response) {
            if (response && response.status === 'connected') {
                FB.logout(function(response) {
                    document.location.reload();
                });
            }
        });
    };




var total = localStorage.getItem('total');
if(total){
    for(var i = 0; i < localStorage.length; i++) {
        if(localStorage.key(i) != 'total'){
            var jsonData = JSON.parse(localStorage.getItem(localStorage.key(i)));
            el("shoppingList").innerHTML += '<tr id="row'+parseInt(jsonData.id)+'" ><td>- '+jsonData.name.escapeHTML() +'</td> <td>Quantity: <input type="number" id="quantity'+parseInt(jsonData.id)+'"  class="quantity" min="0" value='+parseInt(jsonData.value) +'> @$<span class="price">'+parseFloat(jsonData.price)+'</span></td></tr>';
        }
    }
    $(".quantity").change(function(e){changeDetect(e);});   
}
else{
    localStorage.setItem('total', 0.0);
    total = 0.0;
}

el("totalbtn").innerHTML = parseFloat(total);
el("totallist").innerHTML = parseFloat(total);


function changeDetect(e){
    id = e.target.id.replace(/^quantity/, '');
    var total = localStorage.getItem('total');
    var jsonData = JSON.parse(localStorage.getItem(id));
    quantity = jsonData.value;
    price = e.target.parentNode.querySelector('.price').innerHTML;
    total -= parseFloat(price) * parseFloat(quantity);
    if(e.target.value == 0){
        localStorage.removeItem(id);
        var str = "row"+id;
        el(str).remove();
    }
    else{
        total += parseFloat(price) *  parseFloat(e.target.value);
        jsonData.value = e.target.value;
        localStorage.setItem(id , JSON.stringify(jsonData));   
    }
    localStorage.setItem('total' ,total.toFixed(1));
    el("totalbtn").innerHTML = total.toFixed(1);
    el("totallist").innerHTML =total.toFixed(1);
};

function updateShoppingCart(id,name,price){
    var jsonData = JSON.parse(localStorage.getItem(id));
    var total = localStorage.getItem('total');
    if(jsonData){
        jsonData.value++;
        localStorage.setItem(id, JSON.stringify(jsonData));
        var str = "quantity"+id;
        el(str).value++; 
    }
    else{
        el("shoppingList").innerHTML += '<tr id="row'+parseInt(id)+'" ><td>- '+name.escapeHTML() +'</td> <td>Quantity: <input type="number" id="quantity'+parseInt(id)+'"  class="quantity" min="0" value=1> @$<span class="price">'+parseFloat(price)+'</span></td></tr>';
        $(".quantity").change(function(e){changeDetect(e);});
        localStorage.setItem(id, JSON.stringify({id : id ,value : 1 ,name : name, price : price}));
    }

    total = parseFloat(total) + parseFloat(price);
    localStorage.setItem('total', total.toFixed(1));
    el("totalbtn").innerHTML =total.toFixed(1);
    el("totallist").innerHTML = total.toFixed(1);
};


el('loginbtn').onclick = function(e) {
    window.location = "https://secure.s19.ierg4210.ie.cuhk.edu.hk/login.php";
}

el('logoutbtn').onclick = function(e) {
    
    fbLogoutUser();

    var xmlHttp = new XMLHttpRequest();
            xmlHttp.onreadystatechange = function() { 
                if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
                    location.reload();
                }	
            }
        xmlHttp.open("GET", "auth-process.php?action=logout", true); // true for asynchronous 
        xmlHttp.send();
}

cartSubmit=function(form){
    var buyList={};
    el("shoppingCartList").innerHTML = "";
    var count = 1;
    for(var i = 0; i < localStorage.length; i++) {
        if(localStorage.key(i) != 'total'){
            var jsonData = JSON.parse(localStorage.getItem(localStorage.key(i)));
            buyList[localStorage.key(i)]=parseInt(jsonData.value); 
            el("shoppingCartList").innerHTML += '<li> <input type="hidden"  name="item_name_'+  count +'" value="'+jsonData.name.escapeHTML() +'"/> <input type="hidden"  name="item_number_'+ count+'" value="'+parseInt(jsonData.id) +'"/> <input type="hidden"  name="quantity_'+ count+'" value="'+parseInt(jsonData.value)+'"/>  <input type="hidden"  name="amount_'+ count+'" value="'+parseFloat(jsonData.price) +'"/> </li>';
            count++;
        }
    }


    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() { 
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
            result = xmlHttp.responseText.split('while(1);');
            result = JSON.parse(result[1]);
            result = result["success"];	

            
            if(!result){
                if(fbname)
                    result = fbname;
                else
                    result = "Guest";    
            }	           
              
            myLib.processJSON(
                "checkout-process.php",                                     
                {action: "handle_checkout", list:JSON.stringify(buyList), userId: result , nonce:  form.nonce.value},   
                function(returnValue){                                      
                    form.custom.value=returnValue.digest;
                    form.invoice.value=returnValue.invoice;
                    form.submit();    
                    localStorage.clear();
                },
                {method:"POST"});   
                
            	
        }
    }            
    xmlHttp.open("GET", "auth-process.php?action=auth_token", true); // true for asynchronous 
    xmlHttp.send();
    
    return false;
}
