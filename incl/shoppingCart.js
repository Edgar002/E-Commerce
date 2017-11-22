
var xmlHttp = new XMLHttpRequest();
xmlHttp.onreadystatechange = function() { 
    if (xmlHttp.readyState == 4 && xmlHttp.status == 200){
        result = xmlHttp.responseText.split('while(1);');
        result = JSON.parse(result[1]);
        result = result["success"];
        if(result == 'false'){
            window.location = "https://secure.s19.ierg4210.ie.cuhk.edu.hk/login.php";
        }		
    }
}
xmlHttp.open("GET", "auth-process.php?action=auth_token", false); // true for asynchronous 
xmlHttp.send();

