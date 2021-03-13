<?php
date_default_timezone_set('Asia/Manila');
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;      

if(isset($_POST["title"]))
{
 $query = "
 INSERT INTO events_2roomsched 
 (title, start_event, end_event, RoomNo, EncodedByNo, TimeStamp) 
 VALUES (:title, :start_event, :end_event, :RoomNo, :EncodedByNo, :TimeStamp )
 ";
 $statement = $link->prepare($query);
 $statement->execute(
  array(
   ':title'  => $_POST['title'],
   ':start_event' => $_POST['start'],
   ':end_event' => $_POST['end'],
   ':EncodedByNo' => $_SESSION['(ak0)'],
   ':TimeStamp' => date("Y-m-d H:i:s"),
   ':RoomNo' => $_GET['roomno']
  )
 );
}


?>