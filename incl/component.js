
var xmlHttp = new XMLHttpRequest();
xmlHttp.onreadystatechange = function() { 
            if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
                result = xmlHttp.responseText.split('while(1);');
                result = JSON.parse(result[1]);
                result = result["success"];	
                if(result){
                    el("username").innerHTML = result;
                    el('logoutbtn').show();
                }
                else{
                    el("username").innerHTML = "Guest";
                    el('loginbtn').show();
                }		
            }
}            
xmlHttp.open("GET", "auth-process.php?action=auth_token", true); // true for asynchronous 
xmlHttp.send();

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
    var xmlHttp = new XMLHttpRequest();
            xmlHttp.onreadystatechange = function() { 
                if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
                    location.reload();
                }	
            }
        xmlHttp.open("GET", "auth-process.php?action=logout", true); // true for asynchronous 
        xmlHttp.send();
}