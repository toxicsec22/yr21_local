<?php
$path=$_SERVER['DOCUMENT_ROOT'];include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(7494,'1rtc')) { echo 'No permission'; exit();}
$which=(!isset($_GET['w'])?'Discount':$_GET['w']);
include_once('../switchboard/contents.php');

switch ($which){	
	case'Discount':
	$title='Wholesale Discount';
	$sql1='SELECT PriceLevel FROM 1branches WHERE BranchNo='.$_SESSION['bnum'];
	$stmt1=$link->query($sql1); $result1=$stmt1->fetch(); 
	$formdesc='</i></br>
<div style="background-color:#f2f2f2; width:450px; border: 1px solid #404040; padding:5px;">
<b>Encoding:</b></br></br><form method="post" action="wholesalediscount.php?w=Add">
					ItemCode: <input type="text" name="ItemCode" size="10" required>
					MinQty: <input type="text" name="Qty" size="2" required>
					Discount: <input type="text" name="Discount" size="2" required> 
					<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"> 
					<input type="submit" name="submit"> 						
				</form></div>';
		$sql='select wd.TxnID,wd.ItemCode,wd.Qty as MinQty,CONCAT(wd.Discount,"%") as Disct,concat(Category,\' \',ItemDesc) as ItemDesc,
		format(PriceLevel'.$result1['PriceLevel'].'-(PriceLevel'.$result1['PriceLevel'].'*(wd.Discount/100)),2) as DisctdBranchPriceLevel,
		format(PriceLevel1-(PriceLevel1*(wd.Discount/100)),2) as DisctdPriceLevel1,
		format(PriceLevel2-(PriceLevel2*(wd.Discount/100)),2) as DisctdPriceLevel2,
		format(PriceLevel3-(PriceLevel3*(wd.Discount/100)),2) as DisctdPriceLevel3,
		format(PriceLevel4-(PriceLevel4*(wd.Discount/100)),2) as DisctdPriceLevel4,
		format(PriceLevel5-(PriceLevel5*(wd.Discount/100)),2) as DisctdPriceLevel5

		from invty_2itemswholesalediscount wd left join invty_1items i on i.ItemCode=wd.ItemCode left join invty_1category c on c.CatNo=i.CatNo left join invty_5latestminprice lmp on lmp.ItemCode=wd.ItemCode';
		$columnnames=array('ItemCode','ItemDesc','MinQty','Disct','DisctdBranchPriceLevel','DisctdPriceLevel1','DisctdPriceLevel2','DisctdPriceLevel3','DisctdPriceLevel4','DisctdPriceLevel5');
		$txnidname='TxnID';
		$editprocess='wholesalediscount.php?w=Edit&TxnID=';
		$editprocesslabel='Edit';
		$delprocess='wholesalediscount.php?w=Delete&TxnID=';
		include('../backendphp/layout/displayastablenosort.php');
	
	break;
	
	case'Add':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	$sql='INSERT INTO `invty_2itemswholesalediscount` set ItemCode=\''.$_POST['ItemCode'].'\',Qty=\''.$_POST['Qty'].'\', Discount=\''.$_POST['Discount'].'\',  EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:wholesalediscount.php?w=Discount');
	break;
	
	case'Delete':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$TxnID=intval($_GET['TxnID']);
		$sql='delete from invty_2itemswholesalediscount where TxnID=\''.$TxnID.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:wholesalediscount.php?w=Discount');
	break;
	
	case'Edit':
	$TxnID= intval($_GET['TxnID']);
	$sql='select * from `invty_2itemswholesalediscount` where TxnID=\''.$TxnID.'\'';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo '<title>Edit</title></br><h3>Edit?</h3>';
	echo'<form method="post" action="wholesalediscount.php?w=EditProcess&TxnID='.$TxnID.'">
			ItemCode <input type="text" name="ItemCode" value="'.$result['ItemCode'].'" size="10">
			Qty <input type="text" name="Qty" value="'.$result['Qty'].'" size="2" required>
			Discount <input type="text" name="Discount" value="'.$result['Discount'].'" size="2">
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
			<input type="submit" name="submit" value="Edit">
		</form>';
	break;
	
	case'EditProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$TxnID= intval($_GET['TxnID']);
	$sql='Update `invty_2itemswholesalediscount` set ItemCode =\''.$_POST['ItemCode'].'\',Qty=\''.$_POST['Qty'].'\',Discount=\''.$_POST['Discount'].'\', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() where TxnID=\''.$TxnID.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:wholesalediscount.php?w=Discount');
	break;
}

?>