<?php

//update.php
date_default_timezone_set('Asia/Manila');
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;      


if(isset($_POST["id"]))
{
 $query = "
 UPDATE events_2roomsched 
 SET title=:title, start_event=:start_event, end_event=:end_event 
 WHERE id=:id AND EncodedByNo=:EncodedByNo AND LEFT(start_event,10)>=CURDATE()
 ";
 $statement = $link->prepare($query);
 $statement->execute(
  array(
   ':title'  => $_POST['title'],
   ':start_event' => $_POST['start'],
   ':end_event' => $_POST['end'],
   ':id'   => $_POST['id'],
   ':EncodedByNo'   => $_SESSION['(ak0)']
  )
 );
}

?>