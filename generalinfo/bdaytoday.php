<style>
    table,td{
        font-family: sans-serif;
        font-size: small;
        font-weight: 400;
    }
</style>
<?php
date_default_timezone_set('Asia/Manila'); 
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/commonfunctions/ordinalnumber.php';
    $link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
    $sqlbday='Select d.* from `birthdays` d where Month(Birthdate)='.date("m").' AND Day(Birthdate)='.date("d").';';
    // echo $sql.'<br>'.$sqlbday; 
    $stmt=$link->query($sqlbday);
    $datatoshow=$stmt->fetchAll();    

$timefontcolor=isset($timefontcolor)?$timefontcolor:"white";
   if ($stmt->rowCount()>0){
    foreach($datatoshow as $rows){
        if ($rows['IDNo']<1000) {
            echo "<b><i><font color=".$timefontcolor.">Happy ".addOrdinalNumberSuffix((date('Y'))-$rows['Yr'])." Anniversary to ".$rows['Nickname']."!</font></i></b><br>";    
        } else {
        echo "<b><i><font color=".$timefontcolor.">Happy Birthday to ".$rows['Nickname']." ". $rows['SurName']." of ". $rows['Branch']."!</font></i></b><br>";
    }
   }
   }
   
 $link=null; $stmt=null;
    ?>