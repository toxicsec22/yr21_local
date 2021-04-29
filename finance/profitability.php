<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5905,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');

?>
<title>Profitability per Item and Per Client</title>
<form method="post" action="profitability.php">
				Filtering: <input type="submit" name="filter" value="Per Item">
				<?php echo str_repeat('&nbsp;',5); ?><input type="submit" name="filter" value="Per Client">
				</form>
<?php
if(!isset($_POST['filter'])){
	goto nolist;
} else {
	$w=$_POST['filter'];
}

include_once $path.'/acrossyrs/commonfunctions/monthName.php';

switch($w){
	case'Per Item':
		$title='Profitability per Item ';
		
		$sql1='CREATE TEMPORARY TABLE TSales as SELECT MONTH(Date) AS `Month`,ItemCode, SUM(Qty) AS TotalQty, SUM(UnitPrice*Qty) AS Sales FROM invty_2sale m JOIN invty_2salesub s ON m.TxnID=s.TxnID GROUP BY ItemCode, MONTH(Date);';
		
		$stmt1=$link->prepare($sql1); $stmt1->execute();

		$months=array(1,2,3,4,5,6,7,8,9,10,11,12);
		$columnnames=array('ItemCode','ItemDesc');

		$sql='';
		foreach ($months as $month){
			$sql.=' FORMAT(SUM(CASE WHEN MONTH='.$month.' THEN Sales END),0) AS `'.monthName($month).'Sales`,  FORMAT(SUM(CASE WHEN MONTH='.$month.' THEN Sales-IFNULL(`'.str_pad($month,2,0,STR_PAD_LEFT).'`,0)*TotalQty END),0) AS `'.monthName($month).'Profit`, ';
			$columnnames[]=monthName($month).'Sales';
			$columnnames[]=monthName($month).'Profit';
		}
		$sql='SELECT ts.ItemCode, '.$sql.' ItemDesc FROM
			 TSales ts LEFT JOIN '.$currentyr.'_static.invty_weightedavecost wac ON wac.ItemCode=ts.ItemCode JOIN invty_1items i ON i.ItemCode=ts.ItemCode GROUP BY ts.ItemCode';
		// echo $sql; exit();	
		include('../backendphp/layout/displayastable.php');
		break;

case 'Per Client':
		$title='Profitability Per Client';

		$sql1='CREATE TEMPORARY TABLE TSales as SELECT MONTH(Date) AS `Month`, ItemCode, ClientNo, SUM(Qty) AS TotalQty, SUM(UnitPrice*Qty) AS Sales FROM invty_2sale m JOIN invty_2salesub s ON m.TxnID=s.TxnID GROUP BY ItemCode, ClientNo, MONTH(Date);';
		$stmt=$link->prepare($sql1); $stmt->execute();

		$months=array(1,2,3,4,5,6,7,8,9,10,11,12);
		$columnnames=array('ClientNo','ClientName');

		$sql='';
		foreach ($months as $month){
			$sql.=' FORMAT(SUM(CASE WHEN MONTH='.$month.' THEN Sales END),0) AS `'.monthName($month).'Sales`,  FORMAT(SUM(CASE WHEN MONTH='.$month.' THEN Sales-IFNULL(`'.str_pad($month,2,0,STR_PAD_LEFT).'`,0)*TotalQty END),0) AS `'.monthName($month).'Profit`, ';
			$columnnames[]=monthName($month).'Sales';
			$columnnames[]=monthName($month).'Profit';
		}
		$sql='SELECT ts.ClientNo, '.$sql.' ClientName FROM
			 TSales ts LEFT JOIN '.$currentyr.'_static.invty_weightedavecost wac ON wac.ItemCode=ts.ItemCode JOIN 1clients c ON c.ClientNo=ts.ClientNo GROUP BY ts.ClientNo';

		include('../backendphp/layout/displayastable.php');
		
		break;
}
	
nolist:
?>