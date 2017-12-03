<?php
    include_once('lib/db.inc.php');
    function ierg4210_handle_checkout(){
        $list=json_decode($_REQUEST['list']);
        echo json_decode($list);
        
        $pid=array();
        $qty=array();

        $i = 0;
        foreach ($list as $key => $value) {
            $pid[$i]= (int)$key;                      
            $qty[$i]= (int)$value;
            $i++;    
        }
        global $db;
        $db = ierg4210_DB();

        $query="SELECT  pid ,name, price FROM products WHERE pid IN ({implode(',', $pid)})";


       
    }
  
    if($_REQUEST['action']=='handle_checkout') {
        
          $returnVal = call_user_func('ierg4210_' . $_REQUEST['action']);
          echo  'while(1);' . json_encode(array('success' =>$returnVal));
          exit();
    }
      
     
?>