<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6561,'1rtc')){ echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_GET['w'])?'MissingForm':$_GET['w'];
switch($which){
case'MissingForm':
	$title='Missing 2306 and 2307 Forms';
	$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Date'); 
	$sql='select TxnSubId,CollectNo,Date,ClientName,case when csd.DebitAccountID=160 then "2307" when csd.DebitAccountID=161 then "2306" end as Form,Branch,Amount as Withheld from acctg_2collectmain cm join acctg_2collectsubdeduct csd on csd.TxnID=cm.TxnID left join 1clients c on c.ClientNo=cm.ClientNo left join 1branches b on b.BranchNo=cm.BranchSeriesNo where csd.DebitAccountID in (160,161) and Received=0 Order By '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC').'';
	// echo $sql;
	$columnnames=array('Branch','CollectNo','Date','ClientName','Form','Withheld');
	$columnsub=$columnnames;
	$txnidname='TxnSubId';
	if (allowedToOpen(6562,'1rtc')) { 
	$editprocess='missingforms.php?w=Receive&TxnSubId=';
	$editprocesslabel='Receive Form';
	$editprocessonclick='OnClick="return confirm(\'Are you sure you want to submit?\');"';
	}
	include('../backendphp/layout/displayastablenosort.php');
break;
case'Receive':
		$sql='Update acctg_2collectsubdeduct set Received=1, ReceivedBy=\''.$_SESSION['(ak0)'].'\',ReceivedByTS=Now() where TxnSubId=\''.$_GET['TxnSubId'].'\' and Received=0';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:missingforms.php?w=MissingForm');
	break;
}
?>