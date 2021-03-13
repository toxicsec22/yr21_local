<?php
$path=$_SERVER['DOCUMENT_ROOT'];include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5261,'1rtc')) { echo 'No permission'; exit();}
$which=(!isset($_GET['w'])?'Controls':$_GET['w']);
$showbranches=false;
include_once('../switchboard/contents.php');

switch ($which){	
	case'Controls':
	$title='Add to Beginning Inventory';
	
	$formdesc='</i></br>
	<div style="background-color:#f2f2f2; width:190px; border: 1px solid #404040; padding:5px;">
	<b>Note:</b> This is for 2Central only.</div>
	</br>
<form method="post" action="beginvcontrols.php?w=Add">
	ItemCode: <input type="text" name="ItemCode" size="10" required>
	<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"> 
	<input type="submit" name="submit"> 						
</form>';
		$sql='select b.ItemCode,concat(Category,\' \',ItemDesc) as ItemDesc from invty_1beginv b left join invty_1items i on i.ItemCode=b.ItemCode left join invty_1category c on c.CatNo=i.CatNo where BranchNo=\'100\'';
		$columnnames=array('ItemCode','ItemDesc');
		$txnidname='ItemCode';
		$delprocess='beginvcontrols.php?w=Delete&ItemCode=';
		include('../backendphp/layout/displayastablenosort.php');
		
	
	break;
	
	case'Add':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='INSERT INTO `invty_1beginv` set ItemCode=\''.$_POST['ItemCode'].'\', BranchNo=\'100\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:beginvcontrols.php?w=Controls');
	break;
	
	case'Delete':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$ItemCode=intval($_GET['ItemCode']);
	
	$sqlchecker='select * from invty_20uniallposted where ItemCode=\''.$ItemCode.'\' and BranchNo=\'100\'';
	$stmtchecker=$link->query($sqlchecker);
		if($stmtchecker->rowCount()!=0){
			echo 'You cannot delete this item. Transaction exist.'; exit();
		}
		
		$sql='delete from invty_1beginv where ItemCode=\''.$ItemCode.'\' and BranchNo=\'100\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:beginvcontrols.php?w=Controls');
	break;
	
}

?>