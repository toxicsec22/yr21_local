<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(8196,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'Budget':$_GET['w']);
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions ORDER BY FullName','IDNo','FullName','employees'); 
echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches where Active=1 ORDER BY Branch','BranchNo','Branch','branches');
echo comboBox($link,'SELECT TypeID, BudgetDesc FROM acctg_1branchpreapprovedbudgetlist Order By BudgetDesc','TypeID','BudgetDesc','typelist');


if (in_array($which,array('editprocess','add'))){
	$idno=comboBoxValue($link, 'attend_30currentpositions', 'FullName', $_REQUEST['FullName'], 'IDNo');
	$branchno=companyandbranchValue($link, '1branches', 'Branch', $_REQUEST['BranchTransferred'], 'BranchNo');
	$type=comboBoxValue($link, 'acctg_1branchpreapprovedbudgetlist', 'BudgetDesc', $_REQUEST['BudgetDesc'], 'TypeID');
}
switch ($which){
	case'Budget':
	echo '<title>Request For Relocation Allowance</title>
		</br><h3>Request For Relocation Allowance</h3>';
	if (allowedToOpen(8197,'1rtc')) {
		$sqld='select BudgetDesc from acctg_1branchpreapprovedbudgetlist where TypeID=\'6\'';
		$stmtd=$link->query($sqld); $resultd=$stmtd->fetch();
	echo'</br><form method="post" action="relocation.php?w=add">
				Employee:<input type="text" name="FullName" size="10" placeholder="Employee" list="employees">
				BranchTransferred:<input type="text" name="BranchTransferred" size="10" placeholder="Branch" list="branches">
				AmountPerMonth:<input type="text" name="Amount" placeholder="Amount" size="10">
				Type:<input type="text" name="BudgetDesc" size="14" list="typelist" value="'.$resultd['BudgetDesc'].'"></br></br>
				DurationInMonths:<input type="text" name="Duration" placeholder="Duration" size="10">
				DateOfTransfer: <input type="date" name="Date" value="'.date('Y-m-d').'">
				Remarks: <input type="text" name="Remarks">
				<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
				<input type="submit" name="submit">
				</form>';
	}
	
	$title='';
	$sql='select *, Date as DateOfTransfer,Amount as AmountPerMonth,BudgetDesc,FullName,Duration as `DurationInMonths`,roa.Remarks,
	case when HRStatus=0 then "Pending" when HRStatus=1 then "Approved" when HRStatus=2 then "Rejected" end as HRStatus,
	case when OpsStatus=0 then "Pending" when OpsStatus=1 then "Approved" when OpsStatus=2 then "Rejected" end as OpsStatus,
	case when FinanceStatus=0 then "Pending" when FinanceStatus=1 then "Verified" when FinanceStatus=2 then "Rejected" end as FinanceStatus,
	b.Branch as BranchTransferred,b1.Branch as BranchOrigin from approvals_2requestbudget roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo left join 1branches b on b.BranchNo=roa.BranchNo left join 1branches b1 on b1.BranchNo=cp.BranchNo left join acctg_1branchpreapprovedbudgetlist bl on bl.TypeID=roa.TypeID';
	// echo $sql; exit();
	$txnidname='TxnID';
	$columnnames=array('FullName','BranchOrigin','BranchTransferred','AmountPerMonth','BudgetDesc','DurationInMonths','DateOfTransfer','Remarks','OpsStatus','HRStatus','FinanceStatus');
	
	if (allowedToOpen(8197,'1rtc')) {
	//delete button
	$delprocess='relocation.php?w=delete&TxnID=';
	$addlprocess='relocation.php?w=edit&TxnID=';
	$addlprocesslabel='Edit';
}

if (allowedToOpen(8198,'1rtc')) {
	//OPSapprove button
	$editprocess4='relocation.php?w=opsapprove&TxnID='; $editprocesslabel4='Approve'; $editprocess4onclick='OnClick="return confirm(\'Are you sure you want to Approve?\');"';
	//OPSreject button
	$editprocess5='relocation.php?w=opsreject&TxnID='; $editprocesslabel5='Reject'; $editprocess5onclick='OnClick="return confirm(\'Are you sure you want to Reject?\');"';
	
}

if (allowedToOpen(8199,'1rtc')) {
	//exec approve button
	$editprocess='relocation.php?w=approve&TxnID='; $editprocesslabel='Approve'; $editprocessonclick='OnClick="return confirm(\'Are you sure you want to Approve?\');"';
	//exec reject button
	$editprocess2='relocation.php?w=reject&TxnID='; $editprocesslabel2='Reject'; $editprocess2onclick='OnClick="return confirm(\'Are you sure you want to Reject?\');"';
}

if (allowedToOpen(8200,'1rtc')) {
	//finance approve button
	$editprocess='relocation.php?w=financeverified&TxnID='; $editprocesslabel='Verify'; $editprocessonclick='OnClick="return confirm(\'Are you sure you want to Verify?\');"';
	//finance reject button
	$editprocess2='relocation.php?w=financereject&TxnID='; $editprocesslabel2='Reject'; $editprocess2onclick='OnClick="return confirm(\'Are you sure you want to Reject?\');"';
}
	
	include('../backendphp/layout/displayastablenosort.php');
	break;
	
	case'add':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$amount=str_replace(',','',$_POST['Amount']);
		$sql='INSERT INTO approvals_2requestbudget set IDNo=\''.$idno.'\',BranchNo=\''.$branchno.'\',Amount=\''.$_POST['Amount'].'\',Duration=\''.$_POST['Duration'].'\',Date=\''.$_POST['Date'].'\',Remarks=\''.$_POST['Remarks'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now(),TypeID=\''.$type.'\' ';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("location:relocation.php?w=Budget");
	break;
	
	case 'edit':
     $title='Edit';
	 $txnid=intval($_GET['TxnID']);
	 $sql='select *, Date as DateOfTransfer,FullName,Amount as AmountPerMonth,BudgetDesc,Duration as `DurationInMonths`,roa.Remarks,b.Branch as BranchTransferred from approvals_2requestbudget roa left join attend_30currentpositions cp on cp.IDNo=roa.IDNo left join 1branches b on b.BranchNo=roa.BranchNo left join acctg_1branchpreapprovedbudgetlist bl on bl.TypeID=roa.TypeID where roa.TxnID=\''.$txnid.'\' ';
	 // echo $sql; exit();
	 $columnnames=array('FullName','BranchTransferred','Amount','BudgetDesc','DurationInMonths','DateOfTransfer','Remarks');
	 $columnswithlists=array('FullName','BranchTransferred','BudgetDesc');
	 $listsname=array('FullName'=>'employees','BranchTransferred'=>'branches','BudgetDesc'=>'typelist');
     $columnstoedit=array('FullName','BranchTransferred','AmountPerMonth','BudgetDesc','DurationInMonths','DateOfTransfer','Remarks');
     $editprocess='"relocation.php?w=editprocess&TxnID='.$txnid.'"'; 
     include('../backendphp/layout/editspecificsforlists.php');
    break;
	
	case'editprocess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnid=intval($_GET['TxnID']);

	$sql='update approvals_2requestbudget set  IDNo=\''.$idno.'\',BranchNo=\''.$branchno.'\',Amount=\''.$_POST['AmountPerMonth'].'\',Duration=\''.$_POST['DurationInMonths'].'\',Date=\''.$_POST['DateOfTransfer'].'\',Remarks=\''.$_POST['Remarks'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now(),TypeID=\''.$type.'\' where TxnID=\''.$txnid.'\' AND FinanceStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:relocation.php?w=Budget");
	break;
	
	case'delete':
	$txnid=intval($_GET['TxnID']);

	$sql='delete from approvals_2requestbudget where TxnID=\''.$txnid.'\' AND FinanceStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:relocation.php?w=Budget");
	break;
	
	case'opsapprove':
	$txnid=intval($_GET['TxnID']);

	$sql='update approvals_2requestbudget set OpsStatus=1 where TxnID=\''.$txnid.'\' and OpsStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header("Location:relocation.php?w=Budget");
	break;
	
	case'opsreject':
	$txnid=intval($_GET['TxnID']);

	$sql='update approvals_2requestbudget set OpsStatus=2,HRStatus=2,FinanceStatus=2 where TxnID=\''.$txnid.'\' and OpsStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:relocation.php?w=Budget");
	break;
	
	case'approve':
	$txnid=intval($_GET['TxnID']);

	$sql='update approvals_2requestbudget set HRStatus=1 where TxnID=\''.$txnid.'\' and HRStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header("Location:relocation.php?w=Budget");
	break;
	
	case'reject':
	$txnid=intval($_GET['TxnID']);

	$sql='update approvals_2requestbudget set HRStatus=2,FinanceStatus=2 where TxnID=\''.$txnid.'\' and HRStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:relocation.php?w=Budget");
	break;
	
	case'financeverified':
	$txnid=intval($_GET['TxnID']);

	$sql='update approvals_2requestbudget set FinanceStatus=1 where TxnID=\''.$txnid.'\' and FinanceStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	
	//preapprovedbudgetprocess
		$sqlt='select *,Branch from approvals_2requestbudget rb left join 1branches b on b.BranchNo=rb.BranchNo where TxnID=\''.$txnid.'\' ';
		$stmtt=$link->query($sqlt); $resultt=$stmtt->fetch();
		
		$month=date('m',strtotime($resultt['Date']));
		$c=1;
		$sqli='insert into acctg_5branchpreapprovedbudgetspermonth set BranchNo=\''.$resultt['BranchNo'].'\',';
		
			while($c<=$resultt['Duration']){
			$sqli.='`'.str_pad($month++,2,'0',STR_PAD_LEFT).'`=\''.$resultt['Amount'].'\',';
				// if($month>12){
					// $month=1;
				// }
			$c++;
			}
			
			$sqli.='TypeID=\''.$resultt['TypeID'].'\',Remarks=\''.$resultt['Remarks'].'\',BudgetPerMonth=\''.$resultt['Amount'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
			// echo $sqli; exit();
			$stmti=$link->prepare($sqli); $stmti->execute();
			
		$_SESSION['bnum']=$resultt['BranchNo'];
		$_SESSION['@brn'] = $resultt['Branch'];
	header("Location:../approvals/budgets.php");
	break;
	
	case'financereject':
	$txnid=intval($_GET['TxnID']);

	$sql='update approvals_2requestbudget set FinanceStatus=2 where TxnID=\''.$txnid.'\' and FinanceStatus=0';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:relocation.php?w=Budget");
	break;
}