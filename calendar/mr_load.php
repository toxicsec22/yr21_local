<?php
//load.php
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;      

$data = array();

$query = "SELECT rs.*,deptcolor FROM events_2roomsched rs LEFT JOIN attend_30currentpositions cp ON rs.EncodedByNo=cp.IDNo LEFT JOIN 1departments d ON cp.deptid=d.deptid WHERE RoomNo=".$_GET['roomno']." ORDER BY id";

$statement = $link->prepare($query);

$statement->execute();

$result = $statement->fetchAll();

foreach($result as $row)
{
 $data[] = array(
  'id'   => $row["id"],
  'title'   => $row["title"],
  'start'   => $row["start_event"],
  'end'   => $row["end_event"],
  'color'   => $row["deptcolor"]
 );
}

echo json_encode($data);

?>