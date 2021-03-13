<?php
if ((isset($_POST['ApprovalNo']) and !empty($_POST['ApprovalNo'])) or $approvalno<>0){
		$approval=(isset($_POST['ApprovalNo']))?$_POST['ApprovalNo']:$approvalno;
		$sql0='CREATE TEMPORARY TABLE withapproval AS SELECT `BranchNo`, `Particulars`, `Amount`, `AccountID` FROM `approvals_2encashedexpenses` where `Approval` Like \''.$approval. '\'
		UNION SELECT `BranchNo`, concat(`Particulars`, \' InvNo \', ForInvoiceNo) as `Particulars`, `Amount`, 925 as `AccountID` FROM `approvals_2freightclients` where `Approval` Like \''.$approval. '\''; 
		
		$stmt=$link->prepare($sql0); $result0=$stmt->execute();
		$sql0='SELECT `BranchNo`, CONCAT(\''.$details.'\', \' \', `Particulars`) AS `Particulars`, `Amount`, `AccountID` FROM withapproval WHERE BranchNo IS NOT NULL';
		$stmt=$link->query($sql0); $result0=$stmt->fetch(); //echo $sql0; BREAK;
		$branchno=$result0['BranchNo']; $details=$result0['Particulars']; $amt=$result0['Amount']; $acctid=$result0['AccountID'];
		//$approvalno='`ApprovalNo`='.$approval.', ';
		//ECHO $details;break;
		$sql0='create temporary table Spent as 
			SELECT  de.`BranchNo`, de.`ApprovalNo`, Sum(de.Amount) as `Spent` FROM `acctg_2depencashsub` de where `ApprovalNo` Like \''.$approval. '\' group by de.`ApprovalNo`;';
			//echo $sql0;break;
			$stmt=$link->prepare($sql0); $stmt->execute();
			$sql1='Select ROUND(ee.Amount-(Select ifnull(sum(s.Spent),0) from Spent s),2) as Available from `withapproval` ee ';
			$stmt1=$link->query($sql1); $result1=$stmt1->fetch();
			
			if ((($result1['Available'])<$_POST['Amount'])){
				$msg='Budget_exceeded_Please_get_approval_number';
				$encode=false;				
			} else {$encode=true; $amt=$result1['Available'];}
			
		
	} elseif ((isset($_POST['TypeID']) and !empty($_POST['TypeID'])) or $typeid<>0){
		
		$sqlbudget='SELECT TypeID,AccountID FROM acctg_1branchpreapprovedbudgetlist where '.(isset($_POST['TypeID'])?' BudgetDesc like \''.$_POST['TypeID'].'\'':'TypeID='.$typeid);
		//echo $sqlbudget;
		$stmt=$link->query($sqlbudget);
		$resultbudget=$stmt->fetch();
		$acctid=$resultbudget['AccountID'];
		$typeid=$resultbudget['TypeID'];
		// all should now follow encashment approval
		
			$sql0='select Month(dm.`Date`) as ExpenseMonth  from `acctg_2depositmain` dm where dm.TxnID='.$txnid;
			$stmt=$link->query($sql0); $result0=$stmt->fetch();
			$sql0='create temporary table Spent as 
			SELECT  de.`BranchNo`, de.`TypeID`, Sum(de.Amount) as `Spent` FROM `acctg_2depencashsub` de join `acctg_2depositmain` dm on dm.TxnID=de.TxnID 
			where (de.ApprovalNo=0 or isnull(de.ApprovalNo)) and de.BranchNo='.$_SESSION['bnum'].' and TypeID='.$typeid.' and
			(Month(dm.`Date`)='.$result0['ExpenseMonth'].')  group by de.`BranchNo`, de.`TypeID`,  Month(dm.`Date`);';
			//echo $sql0;break;
			$stmt=$link->prepare($sql0); $stmt->execute();
			$sql1='Select SUM(`'.str_pad($result0['ExpenseMonth'],2,'0',STR_PAD_LEFT).'`)-(Select ifnull(sum(s.Spent),0) from Spent s) as Available
			from `acctg_5branchpreapprovedbudgetspermonth` bm 
			 where bm.BranchNo='.$_SESSION['bnum']. ' and bm.TypeID='.$typeid;
			$stmt1=$link->query($sql1); $result1=$stmt1->fetch();
			// echo 'Available: '.$result1['Available'].'<br>Amount: '.$_POST['Amount'];
			if ((($result1['Available'])<$_POST['Amount'])){
			// if ('"'.$result1['Available'].'"'<'"'.$_POST['Amount'].'"'){
				// echo '</br>may mali'; exit();
				$msg='Budget_exceeded_Please_get_approval_number';
				$encode=false;				
			} else {$encode=true;$approval='null'; $typeidsql='TypeID='.$typeid.', ';}
		 //	$execom,$controller, $adminhead,$arofcr, $arstaff, $acctgstaff, $acctgofcrdata CAN ENCODE
		
	} else {
		if (allowedToOpen(5993,'1rtc')){
			$encode=true;$approval='null';
		}
	}
?>