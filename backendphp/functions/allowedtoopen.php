<?php
include $path.'/'.$url_folder.'/backendphp/functions/allallowedid.php';

function allowedToOpen($processid, $db){
	global $currentyr;
	
    date_default_timezone_set('Asia/Manila');
    $path=$_SERVER['DOCUMENT_ROOT'];
    include_once $path.'/acrossyrs/dbinit/userinit.php';
    $linkinfunction=connect_db($currentyr.'_1rtc',0);
    
    if(is_array($processid)){
        $sqlprocess='SELECT ProcessID FROM `'.$currentyr.'_1rtc`.`permissions_2allprocesses` WHERE ProcessID IN ('.implode(',',$processid).') AND ((FIND_IN_SET('.$_SESSION['&pos'].',`AllowedPos`)) OR (FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`)));'; 
            
    } else {
    
    $sqlprocess='SELECT ProcessID FROM `'.$currentyr.'_1rtc`.`permissions_2allprocesses` WHERE ProcessID='.$processid.' AND ((FIND_IN_SET('.$_SESSION['&pos'].',`AllowedPos`)) OR (FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`)));';  
    }
    //if ($_SESSION['(ak0)']==1002){ echo $sqlprocess; exit();}
    $stmt=$linkinfunction->query($sqlprocess); $res=$stmt->fetch();
    if ($stmt->rowCount()>0) {
	    return true; 
	} else {
	    return false; 
	} 
    
$linkinfunction=null;
}