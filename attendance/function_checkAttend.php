<?php
function checkAttend($inout,$id,$date){
    global $currentyr; 
	$path=$_SERVER['DOCUMENT_ROOT'];
    include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
    $sql='Select `'.$inout.'` from attend_2attendance where `DateToday`=\''.$date.'\' and IDNo='.$id.' and (`'.$inout.'` is not null  and `'.$inout.'`<>\'0000\')';
	
    $stmt=$link->query($sql);
    if ($stmt->rowCount()==0){
        return true;
    } else {
        return false; //there is alrdy encoded attendance
    }
}
?>