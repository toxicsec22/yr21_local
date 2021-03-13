<?php

//delete.php
date_default_timezone_set('Asia/Manila');
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 


if(isset($_POST["id"]))
{
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;     
 $query = "
 DELETE from events_2roomsched WHERE id=:id AND EncodedByNo=".$_SESSION['(ak0)']." AND LEFT(start_event,10)>=CURDATE()
 ";
 $statement = $link->prepare($query);
 $statement->execute(
  array(
   ':id' => $_POST['id']
  )
 );
}

?>