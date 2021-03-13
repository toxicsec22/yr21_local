<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(7005),'1rtc')) { echo 'No permission'; exit();}
$showbranches=false; 
include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'Addons':$_GET['w']);
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches WHERE PseudoBranch=0 and Active=1 ORDER BY Branch','BranchNo','Branch','branchlist');
echo comboBox($link,'SELECT txndesc,txntypeid FROM invty_0txntype s WHERE txntypeid in (1,2,32,33)','txntypeid','txndesc','typelists');
switch ($which){     
case 'Addons': 
?>
<style>
#table {
  border-collapse: collapse;
  font-size:10pt;
  width: auto;
  background-color:#cccccc;
}

#table td, #table th {
  border: 1px solid black;
  padding: 3px;
}
#table tr:nth-child(even){background-color:white;}
</style>
<?php
echo'<title>Add-on</title><h3>Add-on</h3>';

	echo'</br><div style="border:1px solid black; width:550px; padding:5px;"><h3>Lookup</h3></br>
		<form method="post" action="addons.php?w=Addons">
			Branch: <input type="text" name="Branch" size="10" list="branchlist">
			SaleNo: <input type="text" name="SaleNo" size="7">
			SaleType: <input type="text" name="txntype" size="7" list="typelists">
			<input type="submit" name="Lookup" value="Lookup">
		</form></div>';

if(isset($_REQUEST['Lookup'])){
$title='';
$branchno = comboBoxValue($link,'`1branches`','Branch',$_REQUEST['Branch'],'BranchNo');
$txntype = comboBoxValue($link,'`invty_0txntype`','txndesc',$_REQUEST['txntype'],'txntypeid');	
	$sql='select Posted,Date,TxnID,Branch,SaleNo,txndesc as TransactionType from invty_2sale s left join 1branches b on b.BranchNo=s.BranchNo left join invty_0txntype t on t.txntypeid=s.txntype where s.BranchNo=\''.$branchno.'\' and SaleNo=\''.$_REQUEST['SaleNo'].'\' and s.txntype=\''.$txntype.'\' ';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	// echo $sql; exit();
echo'</br><table id="table">
	<tr><th>Date</th><th>Branch</th><th>SaleNo</th><th>SaleType</th></tr>
	<tr><td>'.$result['Date'].'</td><td>'.$result['Branch'].'</td><td>'.$result['SaleNo'].'</td><td>'.$result['TransactionType'].'</td>
</table>';
//ADD-on
if($result['Posted']==0 and !allowedToOpen(7004,'1rtc') and !allowedToOpen(7006,'1rtc')){
echo'</i></br><div style="border:1px solid black; width:240px; padding:5px;"><h3>Encode Add-on</h3></br>
		<form method="post" action="addons.php?w=Add">
			ItemCode: <input type="text" name="ItemCode" size="7">
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
			<input type="hidden" name="TxnID" value="'.$result['TxnID'].'">
			<input type="hidden" name="Branch" value="'.$result['Branch'].'">
			<input type="hidden" name="SaleNo" value="'.$result['SaleNo'].'">
			<input type="hidden" name="txntype" value="'.$result['TransactionType'].'">
			<input type="submit" name="submit">
		</form></div>';	
}
if (allowedToOpen(7006,'1rtc')){
	$condition='and FApproved=1';
}else{
	$condition='';
}
	$sqlao='select Approved,ao.TxnSubId,ao.ItemCode,CONCAT(Category,\' \',ItemDesc) as ItemDesc,ao.Qty,
	case when Approved=0 then "For Approval" when Approved=1 then "Approved" when Approved=2 then "Rejected" end as SCstatus,
	case when FApproved=0 then "For Approval" when FApproved=1 then "Approved" when FApproved=2 then "Rejected" end as SAMstatus
	from invty_2salesubaddons ao left join invty_1items i on i.ItemCode=ao.ItemCode left join invty_1category c on c.CatNo=i.CatNo where ao.TxnID='.$result['TxnID'].' '.$condition.'';
	$stmtao=$link->query($sqlao); $resultao=$stmtao->fetchAll();
	
	if($stmtao->rowCount()!=0){
		
		
echo'</br><b>Encoded Add-on</b><table id="table"><tr><th>ItemCode</th><th>ItemDesc</th><th>Qty</th><th>SAMstatus</th><th>SCstatus</th></tr>';	
		foreach($resultao as $resao){
if($result['Posted']==0){			
		if (allowedToOpen(7004,'1rtc')){ //SAM
			$input=$resao['Qty'];
			$button='<td>
			<a href="addons.php?w=FApprove&TxnSubId='.$resao['TxnSubId'].'&TxnID='.$result['TxnID'].'&action_token='.$_SESSION['action_token'].'&Branch='.$result['Branch'].'&SaleNo='.$result['SaleNo'].'&txntype='.$result['TransactionType'].'">Approve</a>'.str_repeat('&nbsp;',5).'
			<a href="addons.php?w=FReject&TxnSubId='.$resao['TxnSubId'].'&TxnID='.$result['TxnID'].'&action_token='.$_SESSION['action_token'].'&Branch='.$result['Branch'].'&SaleNo='.$result['SaleNo'].'&txntype='.$result['TransactionType'].'">Reject</a> </td>';
		}elseif(allowedToOpen(7006,'1rtc')){ //SC
			if($resao['Approved']==0){
				$value=1;
			}else{
				$value=$resao['Qty'];
			}
			$input='<form method="post" action="addons.php?w=SApprove&TxnSubId='.$resao['TxnSubId'].'&TxnID='.$result['TxnID'].'&action_token='.$_SESSION['action_token'].'&Branch='.$result['Branch'].'&SaleNo='.$result['SaleNo'].'&txntype='.$result['TransactionType'].'"><input type="text" name="Qty" value="'.$value.'" size="1">';
			$button='<td>
			<input type="submit" name="SApprove" value="Approve"></form>
			<a href="addons.php?w=SReject&TxnSubId='.$resao['TxnSubId'].'&TxnID='.$result['TxnID'].'&action_token='.$_SESSION['action_token'].'&Branch='.$result['Branch'].'&SaleNo='.$result['SaleNo'].'&txntype='.$result['TransactionType'].'">Reject</a></td>';
		}else{ //STL
			$input=$resao['Qty'];
			$button='<td>
			<a href="addons.php?w=Edit&TxnSubId='.$resao['TxnSubId'].'&TxnID='.$result['TxnID'].'&action_token='.$_SESSION['action_token'].'&Branch='.$result['Branch'].'&SaleNo='.$result['SaleNo'].'&txntype='.$result['TransactionType'].'">Edit</a>'.str_repeat('&nbsp;',5).'
			<a href="addons.php?w=Delete&TxnSubId='.$resao['TxnSubId'].'&TxnID='.$result['TxnID'].'&action_token='.$_SESSION['action_token'].'&Branch='.$result['Branch'].'&SaleNo='.$result['SaleNo'].'&txntype='.$result['TransactionType'].'">Delete</a> </td>';
		}
}else{
	$input=$resao['Qty'];
	$button='';
}
			
			
			
			echo'<tr><td>'.$resao['ItemCode'].'</td><td>'.$resao['ItemDesc'].'</td><td>'.$input.'</td><td>'.$resao['SAMstatus'].'</td><td>'.$resao['SCstatus'].'</td>'.$button.'</tr>';
		}
echo'</table>';
	}
}
break;	

case'Add':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='INSERT INTO `invty_2salesubaddons` set TxnID='.$_POST['TxnID'].',ItemCode=\''.$_POST['ItemCode'].'\', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:addons.php?w=Addons&TxnID='.$_POST['TxnID'].'&Lookup=1&Branch='.$_POST['Branch'].'&SaleNo='.$_POST['SaleNo'].'&txntype='.$_POST['txntype'].'');
break;

case'Delete':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnsubid= intval($_GET['TxnSubId']);
	$sql='Delete from `invty_2salesubaddons` where TxnSubId='.$txnsubid.' and FApproved=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:addons.php?w=Addons&TxnID='.$_GET['TxnID'].'&Lookup=1&Branch='.$_GET['Branch'].'&SaleNo='.$_GET['SaleNo'].'&txntype='.$_GET['txntype'].'');
break;

case'Edit':
	$txnsubid= intval($_GET['TxnSubId']);
	$sql='select ItemCode from `invty_2salesubaddons` where TxnSubId='.$txnsubid.'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo '<title>Edit</title><h3>Edit?</h3>';
	echo'<form method="post" action="addons.php?w=EditProcess&TxnSubId='.$txnsubid.'">
			ItemCode: <input type="text" name="ItemCode" value="'.$result['ItemCode'].'" size="7">
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
			<input type="hidden" name="TxnID" value="'.$_GET['TxnID'].'">
			<input type="hidden" name="Branch" value="'.$_GET['Branch'].'">
			<input type="hidden" name="SaleNo" value="'.$_GET['SaleNo'].'">
			<input type="hidden" name="txntype" value="'.$_GET['txntype'].'">
			<input type="submit" name="submit" value="Edit">
		</form>';
break;

case'EditProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnsubid= intval($_GET['TxnSubId']);
	$sql='Update `invty_2salesubaddons` set ItemCode=\''.$_POST['ItemCode'].'\', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() where TxnSubId=\''.$txnsubid.'\' and FApproved=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:addons.php?w=Addons&TxnID='.$_POST['TxnID'].'&Lookup=1&Branch='.$_POST['Branch'].'&SaleNo='.$_POST['SaleNo'].'&txntype='.$_POST['txntype'].'');
break;

case'FApprove':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnsubid= intval($_GET['TxnSubId']);
	$sql='Update `invty_2salesubaddons` set FApproved=1, FApprovedByNo='.$_SESSION['(ak0)'].', FApprovedTS=Now() where TxnSubId=\''.$txnsubid.'\' and Approved=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:addons.php?w=Addons&TxnID='.$_GET['TxnID'].'&Lookup=1&Branch='.$_GET['Branch'].'&SaleNo='.$_GET['SaleNo'].'&txntype='.$_GET['txntype'].'');

break;

case'FReject':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnsubid= intval($_GET['TxnSubId']);
	$sql='Update `invty_2salesubaddons` set Qty=0,FApproved=2,Approved=2, FApprovedByNo='.$_SESSION['(ak0)'].', FApprovedTS=Now() where TxnSubId=\''.$txnsubid.'\' and Approved=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:addons.php?w=Addons&TxnID='.$_GET['TxnID'].'&Lookup=1&Branch='.$_GET['Branch'].'&SaleNo='.$_GET['SaleNo'].'&txntype='.$_GET['txntype'].'');

break;

case'SApprove':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnsubid= intval($_GET['TxnSubId']);
	$sql='Update `invty_2salesubaddons` set Qty=\''.$_POST['Qty'].'\',Approved=1, ApprovedByNo='.$_SESSION['(ak0)'].', ApprovedTS=Now() where TxnSubId=\''.$txnsubid.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:addons.php?w=Addons&TxnID='.$_GET['TxnID'].'&Lookup=1&Branch='.$_GET['Branch'].'&SaleNo='.$_GET['SaleNo'].'&txntype='.$_GET['txntype'].'');

break;

case'SReject':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnsubid= intval($_GET['TxnSubId']);
	$sql='Update `invty_2salesubaddons` set Qty=0,Approved=2, ApprovedByNo='.$_SESSION['(ak0)'].', ApprovedTS=Now() where TxnSubId=\''.$txnsubid.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:addons.php?w=Addons&TxnID='.$_GET['TxnID'].'&Lookup=1&Branch='.$_GET['Branch'].'&SaleNo='.$_GET['SaleNo'].'&txntype='.$_GET['txntype'].'');

break;
}

?>