<?php
ini_set('memory_limit', '1024M');
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6061,'1rtc')) {   echo 'No permission'; exit;}  
$showbranches=false;
include_once('../switchboard/contents.php');
 

 //user of this connection has full access
// $connect=connect_to_db("attend",1);
$connect=connect_db("".$currentyr."_1rtc",1);

$which=(!isset($_GET['w'])?'List':$_GET['w']);

switch ($which)
{
	case 'List':
	echo '<style>
	#link {
		border-radius: 5px;
		background: #cccccc;
		padding: 8px; 
		border: 1px solid #000;
		text-align: center;
		color: black;
		text-decoration: none;
		font-size: 10pt;
		display:inline-block;
		margin-bottom:3px;
	}
	</style>';
	$title='Select Database'; $formdesc='';
	echo '<title>'.$title.'</title>';
	
	echo '<h3>'.$title.'</h3><br/>';
	
	$sql = "SHOW DATABASES WHERE `Database` LIKE '%".$currentyr."%' OR `Database` LIKE '1_gamit';";
	
	$stmt = $connect->query($sql);
	while($row = $stmt->fetch())
	{
		echo '<a id=\'link\' href=\'sqlbackup.php?w=BackUp&Table='.$row['Database'].'\'>'.$row['Database'].'</a> ';
	}
	break; //End of Case List
	
	
	case 'BackUp':

	$db = $_GET['Table'];
	// echo $db;
	$dbname = $db;
	
	$connect = connect_db($db,0);
	
	include('sqlbackupprocess.php');
	
	break; //End of Case BackUp
}
$connect = null;
?>