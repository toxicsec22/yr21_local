<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(8000,'1rtc')) { echo 'No permission'; exit; }

$showbranches=false;
include_once('../switchboard/contents.php');
skipcontents:

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';
?>


<br><div id="section" style="display: block;">

<?php
include_once('../backendphp/layout/linkstyle.php');
		echo '<div>';
			if (allowedToOpen(8001,'1rtc')) {
				echo '<a id=\'link\' href="billduedates.php?w=BillType">Bill Type</a> ';
				}
			if (allowedToOpen(8002,'1rtc')) {
				echo '<a id=\'link\' href="billduedates.php?w=AssignUtilityBill">Assign Utility Bill</a> ';
				echo '<a id=\'link\' href="billduedates.php?w=TransferAssignees">Transfer Assignees</a> ';
				echo '<a id=\'link\' href="billduedates.php?w=AssignmentHistory">Assignment History</a> ';
				}
			if (allowedToOpen(8003,'1rtc')) {
				echo '<a id=\'link\' href="billduedates.php?w=ReceiveBill">Receive Bills</a> ';
				}
			if (allowedToOpen(8005,'1rtc')) {
				echo '<a id=\'link\' href="billduedates.php?w=SendToCV">Send To CV</a> ';
				}
			if (allowedToOpen(8000,'1rtc')) {
				echo '<a id=\'link\' href="billduedates.php?w=MonitorBills">Monitor Bills</a> ';
			}
			if (allowedToOpen(8005,'1rtc')) {
				echo ''.str_repeat('&nbsp;',25).' <a id=\'link\' href="billduedates.php?w=DeleteBills">Delete Bills Data</a> ';
			}
		echo '</div><br/>';
	
$which=(!isset($_GET['w'])?'MonitorBills':$_GET['w']);


if (in_array($which,array('BillType','EditSpecificsBillType'))){
   $sql='SELECT bt.*, BTID AS TxnID, CONCAT(Nickname," ",Surname) AS EncodedBy FROM acctg_0billtype bt LEFT JOIN 1_gamit.0idinfo id ON bt.EncodedByNo=id.IDNo';
   $columnnameslist=array('BillType', 'EncodedBy', 'Timestamp');
   $columnstoadd=array('BillType');
}

if (in_array($which,array('AddBillType','EditBillType'))){
   $columnstoadd=array('BillType');
}


if (in_array($which,array('ReceiveBill','MonitorBills','SendToCV'))){
	//SET SESSIONS HERE
	if (isset($_POST['btnLookUpCutOff']) OR isset($_POST['btnLookUpDue'])){
		if (isset($_POST['btnLookUpCutOff'])){
			$_SESSION['bill_cutoff']=$_POST['btnLookUpCutOff'];
			unset($_SESSION['bill_due']);
		} else if(isset($_POST['btnLookUpDue'])){
			$_SESSION['bill_due']=$_POST['btnLookUpDue'];
			unset($_SESSION['bill_cutoff']);
		} else{
		}
		$_SESSION['bill_branch']=$_POST['BranchNo'];
		$_SESSION['bill_biller']=$_POST['BillerID'];
		$_SESSION['bill_monthfrom']=$_POST['MonthFrom'];
		$_SESSION['bill_monthto']=$_POST['MonthTo'];
	} 
	
	if (isset($_SESSION['bill_cutoff'])){
		$filteredby='Filtered By: Cut Off Date<br>';
	} else if(isset($_SESSION['bill_due'])){
		$filteredby='Filtered By: Due Date<br>';
	} else{
		$filteredby='';
	}
	
	$sql='SELECT BranchNo,Branch FROM 1branches WHERE Active=1 AND BranchNo>0 ORDER BY Branch';
	$stmtb=$link->query($sql);
	$optionbranch='';
	while($row=$stmtb->fetch()) {
		$optionb = '<option value="'.$row['BranchNo'].'" '.(((isset($_SESSION['bill_branch'])) AND ($row['BranchNo']==$_SESSION['bill_branch']))?'selected':'').'>'.$row['Branch'].'</option>';
		$optionbranch=$optionbranch.$optionb;
	}
	
	$sql='SELECT SupplierNo,SupplierName FROM 1suppliers WHERE InvtySupplier=3 ORDER BY SupplierName';
	$stmtb = $link->query($sql);
	$optionbiller='';
	while($row=$stmtb->fetch()) {
		$optionbi = '<option value="'.$row['SupplierNo'].'" '.(((isset($_SESSION['bill_biller'])) AND ($row['SupplierNo']==$_SESSION['bill_biller']))?'selected':'').'>'.$row['SupplierName'].'</option>';
		$optionbiller=$optionbiller.$optionbi;
	}
	
	$filterform = '<form action="billduedates.php?w='.$which.'" method="POST">';
	$filterform .= 'Expense Of Branch: <select name="BranchNo"><option value="-1" '.(((isset($_SESSION['bill_branch'])) AND $_SESSION['bill_branch']==-1)?'selected':'').'>All</option>'.$optionbranch.'</select> ';
	$filterform .= 'Biller: <select name="BillerID"><option value="-1">All</option>'.$optionbiller.'</select> ';
	$filterform .= 'MonthFrom: (1-12) <input type="text" name="MonthFrom" size="5" autocomplete="off" value="'.(isset($_SESSION['bill_monthfrom'])?$_SESSION['bill_monthfrom']:date('m')).'" required> ';
	$filterform .= 'MonthTo: (1-12) <input type="text" name="MonthTo" size="5" value="'.(isset($_SESSION['bill_monthto'])?$_SESSION['bill_monthto']:date('m')).'" autocomplete="off" required> ';
	if ($which=='MonitorBills'){
		$filterform .= '<input type="submit" name="btnLookUpCutOff" value="Filter CUT OFF Date"> OR ';
	}
	$filterform .= '<input type="submit" name="btnLookUpDue" value="Filter DUE Date"> ';
	$filterform .= '</form>';

	$filtertitle=$filteredby;
	
	
}


if (in_array($which,array('AssignUtilityBill','EditSpecificsAssignUtilityBill','TransferAssignees','AssignmentHistory'))){
	echo comboBox($link,'SELECT DISTINCT(AccountNo) FROM `acctg_4billassignment` ORDER BY AccountNo;','AccountNo','AccountNo','accountnolist');
	
   echo comboBox($link,'SELECT IDNo, FullName FROM `attend_30currentpositions` UNION ALL SELECT BranchNo, Branch FROM 1branches WHERE Active<>0 AND BranchNo>=0 ORDER BY FullName','IDNo','FullName','empbranchlist');
}
if (in_array($which,array('AssignUtilityBill','ReceiveBill','EditSpecificsAssignUtilityBill','MonitorBills','SendToCV','AssignmentHistory'))){
	
	if ($which=='AssignUtilityBill' OR $which=='EditSpecificsAssignUtilityBill' OR $which=='AssignmentHistory'){
		$fieldastxn='ua.AssignID AS TxnID,'; 
	} else {//Others
		$fieldastxn='bd.TxnID,bd.CutOffDate,bd.DueDate,IF(bd.Paid<>0,"Yes","No") AS Paid,IF((bd.Amount IS NOT NULL),bd.Amount,MRF) AS BillAmount, bd.Amount,CONCAT(id2.Nickname," ",id2.Surname) AS ReceivedBy,bd.Received,bd.ReceivedTS,bd.Remarks,';
	}
   $sql1='SELECT ua.*,IF(ua.Active=1,"Yes","No") AS `Active?`,'.$fieldastxn.'BillType,ShortAcctID,SupplierName AS BillerName,b.Branch AS ExpenseOf, IF(AssigneeNo<1000,b2.Branch,CONCAT(id.Nickname," ",id.Surname)) AS Assignee,Company AS DeclaredforCompany FROM acctg_4billassignment ua LEFT JOIN acctg_0billtype bt ON ua.BTID=bt.BTID LEFT JOIN 1suppliers s ON ua.BillerID=s.SupplierNo LEFT JOIN acctg_1chartofaccounts coa ON ua.ExpenseAccountID=coa.AccountID LEFT JOIN 1_gamit.0idinfo id ON ua.AssigneeNo=id.IDNo LEFT JOIN 1companies c ON ua.DeclaredforCompanyNo=c.CompanyNo LEFT JOIN 1branches b ON ua.ExpenseOfBranchNo=b.BranchNo LEFT JOIN 1branches b2 ON ua.AssigneeNo=b2.BranchNo '; //echo $sql1;
   
   $columnnameslist=array('BillerName','BillType','SubscriberName', 'AccountNo','ShortAcctID', 'TelMobileNo', 'MRF','AddOns','PlanDescription', 'Assignee', 'ExpenseOf', 'DeclaredforCompany','CutOffDay','DueDay','DateAssigned','EndOfContract','Remarks','Active?');
   echo comboBox($link,'SELECT * FROM `acctg_0billtype` ORDER BY BillType;','BTID','BillType','billtypelist');
   echo comboBox($link,'SELECT SupplierNo,SupplierName FROM `1suppliers` WHERE InvtySupplier=3 ORDER BY SupplierName;','SupplierNo','SupplierName','billerlist');
   echo comboBox($link,'SELECT DISTINCT(SubscriberName) FROM `acctg_4billassignment` ORDER BY SubscriberName;','SubscriberName','SubscriberName','subscriberlist');
   
   echo comboBox($link,'SELECT AccountID,ShortAcctID FROM `acctg_1chartofaccounts` ORDER BY ShortAcctID;','AccountID','ShortAcctID','expenseaccountlist');
   echo comboBox($link,'SELECT BranchNo,Branch FROM `1branches` WHERE `Active`<>0 ORDER BY Branch;','BranchNo','Branch','branchlist');
   echo comboBox($link,'SELECT CompanyNo,Company FROM `1companies` WHERE `Active`<>0 ORDER BY Company;','CompanyNo','Company','companylist');
   $columnstoadd=array('BillerName','BillType','SubscriberName','AccountNo','ShortAcctID','TelMobileNo','ExpenseOf','DeclaredforCompany','MRF','AddOns','PlanDescription','Assignee','CutOffDay','DueDay','DateAssigned','EndOfContract','Remarks');
}
if (in_array($which,array('AddAssignUtilityBill','EditAssignUtilityBill'))){
   $columnstoadd=array('SubscriberName','AccountNo','TelMobileNo','MRF','AddOns','PlanDescription','CutOffDay','DueDay','DateAssigned','EndOfContract','Remarks');
   $BTID=comboBoxValue($link,'acctg_0billtype','BillType',addslashes($_POST['BillType']),'BTID');
   
 
   
   $CompanyNo=companyandbranchValue($link,'1companies','Company',addslashes($_POST['DeclaredforCompany']),'CompanyNo');
   $BranchNo=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['ExpenseOf']),'BranchNo');
   $BillerID=comboBoxValue($link,'1suppliers','SupplierName',addslashes($_POST['BillerName']),'SupplierNo');
   $ExpenseAccountID=comboBoxValue($link,'acctg_1chartofaccounts','ShortAcctID',addslashes($_POST['ShortAcctID']),'AccountID');
}
if (in_array($which,array('AddAssignUtilityBill','EditAssignUtilityBill','DeleteBills','Transfer'))){
	if(($which=='DeleteBills')){
		if(isset($_POST['Assignee'])){
			$go=1;
		} else {
			$go=0;
		}
	} else {
		$go=1;
	}
	if($go==1){
		 $sqlidno='SELECT IDNo FROM attend_30currentpositions WHERE FullName="'.$_POST['Assignee'].'" UNION ALL SELECT IDNo FROM 1_gamit.0idinfo WHERE CONCAT(Nickname," ",Surname)="'.$_POST['Assignee'].'" UNION ALL SELECT BranchNo AS IDNo FROM 1branches WHERE Branch="'.$_POST['Assignee'].'" AND BranchNo>=0';
		$stmtidno=$link->query($sqlidno);
		$residno=$stmtidno->fetch();
		$IDNo=$residno['IDNo'];
	}
	// echo $IDNo; exit();
}

switch ($which)
{
	case 'BillType':
	if (allowedToOpen(8001,'1rtc')) {
		$title='Type of Bills';
		$method='post';
		$columnnames=array(
		array('field'=>'BillType','type'=>'text','size'=>25,'required'=>true));
							
		$action='billduedates.php?w=AddBillType'; $fieldsinrow=4; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
		$delprocess='billduedates.php?w=DeleteBillType&BTID=';
		$editprocess='billduedates.php?w=EditSpecificsBillType&BTID='; $editprocesslabel='Edit';
     
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'AddBillType':
	if (allowedToOpen(8001,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		$sql='INSERT INTO `acctg_0billtype` SET BillType="'.$_POST['BillType'].'", EncodedByNo='.$_SESSION['(ak0)'];
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:billduedates.php?w=BillType');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'EditSpecificsBillType':
        if (allowedToOpen(8001,'1rtc')) {
			$title='Edit Specifics';
			$txnid=intval($_GET['BTID']);

			$sql=$sql.' WHERE BTID='.$txnid;
			$columnstoedit=$columnstoadd;
			
			$columnnames=$columnnameslist;
			
			$editprocess='billduedates.php?w=EditBillType&BTID='.$txnid;
			
			include('../backendphp/layout/editspecificsforlists.php');
		} else {
			echo 'No permission'; exit;
		}
	break;
	
	case 'EditBillType':
		if (allowedToOpen(8001,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['BTID']);
		$sql='';
		
		$sql='UPDATE `acctg_0billtype` SET BillType="'.$_REQUEST['BillType'].'", EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE BTID='.$txnid;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:billduedates.php?w=BillType");
		} else {
		echo 'No permission'; exit;
		}
		
    break;
	
	case 'DeleteBillType':
	if (allowedToOpen(8001,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `acctg_0billtype` WHERE BTID='.intval($_GET['BTID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:billduedates.php?w=BillType");
	} else {
		echo 'No permission'; exit;
		}

    break;
	
	
	case 'ReceiveBill':
	if (allowedToOpen(8003,'1rtc')) {
		$title='Receive Bills';
		echo '<title>'.$title.'</title>';
		echo '<h2>'.$title.'</h2><br>';
		echo $filterform;
		
		if ((isset($_SESSION['bill_due']))){
			$title=$filtertitle;
			$columnnameslist=array('BillerName','BillType','SubscriberName', 'AccountNo', 'TelMobileNo', 'MRF', 'Assignee', 'ExpenseOf', 'DeclaredforCompany','CutOffDate','DueDate','BillAmount');
			$txnid='TxnID';
			$columnnames=$columnnameslist;
			$columnstoedit=array('BillAmount');
			
			$addcondi='YEAR(DueDate)='.$currentyr.' AND MONTH(DueDate) ';
			
			$editprocess='billduedates.php?w=Received&action_token='.$_SESSION['action_token'].'&TxnID='; $editprocesslabel='Bill Received?';
			
			
			$sql=$sql1.' JOIN acctg_4billsdue bd ON ua.AssignID=bd.AssignID LEFT JOIN 1_gamit.0idinfo id2 ON bd.ReceivedByNo=id2.IDNo WHERE ua.Active=1 AND Received=0 '.(((!isset($_SESSION['bill_branch'])) OR ($_SESSION['bill_branch']==-1))?'':'AND ExpenseOfBranchNo='.$_SESSION['bill_branch'].'').' '.(((!isset($_SESSION['bill_biller'])) OR ($_SESSION['bill_biller']==-1))?'':'AND ua.BillerID='.$_SESSION['bill_biller'].'').' AND '.$addcondi.' BETWEEN '.(isset($_SESSION['bill_monthfrom'])?$_SESSION['bill_monthfrom']:date('m')).' AND '.(isset($_SESSION['bill_monthto'])?$_SESSION['bill_monthto']:date('m')).' ORDER BY DueDate';
			
			//disabling input
			$disablefield=true;
			$triggercolumn='Paid';
			$txtshouldbe='Yes';
			// include('../backendphp/layout/displayastableeditcells.php');
			include('../backendphp/layout/displayastableeditcellswithsorting.php');
		}
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'Received':
	if (allowedToOpen(8003,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		
		$sql='UPDATE `acctg_4billsdue` SET Amount="'.(!is_numeric($_POST['BillAmount'])?str_replace(',', '',$_POST['BillAmount']):$_POST['BillAmount']).'",Received=1, ReceivedByNo='.$_SESSION['(ak0)'].',ReceivedTS=NOW() WHERE TxnID='.intval($_REQUEST['TxnID']).';';
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:billduedates.php?w=ReceiveBill');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'AssignUtilityBill':
	if (allowedToOpen(8002,'1rtc')) {
		$title='Assign Utility Bill'; 
                $formdesc='<br></i><h4><a href="billduedates.php?w=UploadList" target="_blank">Upload new bills</a> then <a href="billduedates.php?w=GeneratePage" target="_blank">record bills</a> per month for monitoring.</h4><i>';
				$method='post';
				$columnnames=array(
				array('field'=>'BillType','caption'=>'Bill Type','type'=>'text','size'=>15,'required'=>true,'list'=>'billtypelist'),
				array('field'=>'BillerName','caption'=>'Biller','type'=>'text','size'=>25,'required'=>true,'list'=>'billerlist'),
				array('field'=>'SubscriberName','caption'=>'Subscriber Name','type'=>'text','size'=>25,'required'=>true,'list'=>'subscriberlist'),
				array('field'=>'AccountNo','caption'=>'Account number','type'=>'text','size'=>25,'required'=>true,'list'=>'accountnolist'),
				array('field'=>'ShortAcctID','caption'=>'Expense Account','type'=>'text','size'=>25,'required'=>true,'list'=>'expenseaccountlist'),
				array('field'=>'TelMobileNo','caption'=>'Telephone/Mobile No','type'=>'text','size'=>25),
				array('field'=>'MRF','caption'=>'MRF','type'=>'text','size'=>25,'required'=>true),
				array('field'=>'AddOns','type'=>'text','size'=>25,'value'=>'0','required'=>false),
				array('field'=>'PlanDescription','type'=>'text','size'=>25,'required'=>false),
				array('field'=>'Assignee','caption'=>'Assignee','type'=>'text','size'=>25,'required'=>true,'list'=>'empbranchlist'),
				array('field'=>'ExpenseOf','caption'=>'Expense Of','type'=>'text','size'=>25,'required'=>true,'list'=>'branchlist'),
				array('field'=>'DeclaredforCompany','caption'=>'Declared for Company','type'=>'text','size'=>25,'required'=>true,'list'=>'companylist'),
				array('field'=>'CutOffDay','caption'=>'CutOffDay (1-31)','type'=>'number','size'=>'5','required'=>true),
				array('field'=>'DueDay','caption'=>'Due Day (1-31)','type'=>'number','size'=>'5','required'=>true),
				array('field'=>'DateAssigned','type'=>'date','size'=>'10','required'=>true),
				array('field'=>'EndOfContract','type'=>'date','size'=>'10','required'=>true),
				array('field'=>'Remarks','type'=>'text','size'=>'45','required'=>false)
				);
							
		$action='billduedates.php?w=AddAssignUtilityBill'; $fieldsinrow=4; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
		$delprocess='billduedates.php?w=DeleteAssignUtilityBill&AssignID=';
		$editprocess='billduedates.php?w=EditSpecificsAssignUtilityBill&AssignID='; $editprocesslabel='Edit';
		$addlprocess='billduedates.php?w=SetActiveInactive&AssignID='; $addlprocesslabel='Active/Inactive';
     
		$title=''; $formdesc=''; $txnid='AssignID';
		$columnnames=$columnnameslist;       
		
		$width='100%';
		
		$sql=$sql1.' WHERE ua.Active=1 ORDER BY Active DESC';
		// echo $sql;
		include('../backendphp/layout/displayastable.php');
		
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	case 'AssignmentHistory':
	
	if (allowedToOpen(8002,'1rtc')) {
		$title='Assignment History';
		
		echo '<title>'.$title.'</title>';
		if(isset($_REQUEST['AccountNo'])){
			echo '<h3>History of Account No: '.$_REQUEST['AccountNo'].'</h3>';
		} else {
			echo '<h3>Assignment History</h3>';
		}
		echo '<form action="billduedates.php?w=AssignmentHistory" method="POST">Account Number: <input type="text" name="AccountNo" list="accountnolist" autocomplete="off"> <input type="submit" name="btnSubmit" value="Lookup"></form>';
		
		$title=''; $formdesc=''; $txnid='AssignID';
		   
		
		$width='100%';
		
		if(isset($_REQUEST['AccountNo'])){
			$columnnames=$columnnameslist;    
			$sql=$sql1.' WHERE AccountNo="'.$_REQUEST['AccountNo'].'" ORDER BY Active DESC,ua.TimeStamp DESC';
		
			include('../backendphp/layout/displayastable.php');
		}
		
		
	} else {
		echo 'No permission'; exit;
	}
	
	
	break;
	
	
	case 'SetActiveInactive':
		if (allowedToOpen(8002,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['AssignID']);
		$sql='';
	
		$sql='UPDATE `acctg_4billassignment` SET Active=IF(Active=1,0,1), EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=NOW() WHERE AssignID='.$txnid;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:billduedates.php?w=AssignUtilityBill");
		} else {
		echo 'No permission'; exit;
		}
		
    break;
	
		
	case 'AddAssignUtilityBill':
	if (allowedToOpen(8002,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='INSERT INTO `acctg_4billassignment` SET '.$sql.' BTID='.$BTID.', ExpenseAccountID='.$ExpenseAccountID.', BillerID='.$BillerID.', AssigneeNo='.$IDNo.', ExpenseOfBranchNo='.$BranchNo.', DeclaredforCompanyNo='.$CompanyNo.', EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=NOW()';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		
		//Last ID FROM acctg_4billassignment insert
		$sql='SELECT LAST_INSERT_ID() AS AssignID;';
		$stmt=$link->query($sql); $result=$stmt->fetch();
		
		//Auto Populate acctg_4billsdue
		if ($_POST['CutOffDay']<$_POST['DueDay']){
			$lessthan='yes';
			$monthco=1;
		} else {
			$lessthan='no';
			$monthco=1;
		}
		$monthdd=(($lessthan=='yes')?$monthco:$monthco+1);
			while ($monthco<=12){
				if ($monthdd==13){ //nxt yr january only for due date
					$monthdd=1;
					$nxtyr=1;
				} else { //for condition only
					$nxtyr=0;
				}
				$sqlauto='INSERT INTO `'.$currentyr.'_1rtc`.`acctg_4billsdue` SET AssignID='.$result['AssignID'].', CutOffDate=
					(CASE
					WHEN (
						('.$monthco.'=2 AND '.$_POST['CutOffDay'].'>=28)
						OR 
						('.$monthco.' IN (1,3,5,7,8,10,12) AND '.$_POST['CutOffDay'].'=31)
						OR
						('.$monthco.' IN (4,6,9,11) AND '.$_POST['CutOffDay'].' IN (30,31))
						
						) THEN 
						LAST_DAY("'.$currentyr."-".(strlen($monthco)==1?'0':'').$monthco.'-01")
					ELSE
						"'.$currentyr."-".(strlen($monthco)==1?'0':'').$monthco."-".(strlen($_POST['CutOffDay'])==1?'0':'').$_POST['CutOffDay'].'"
					END)
					, DueDate=
					(CASE
					WHEN (
						('.$monthdd.'=2 AND '.$_POST['DueDay'].'>=28)
						OR 
						('.$monthdd.' IN (1,3,5,7,8,10,12) AND '.$_POST['DueDay'].'=31)
						OR
						('.$monthdd.' IN (4,6,9,11) AND '.$_POST['DueDay'].' IN (30,31))
						
						) THEN 
						LAST_DAY("'.($currentyr+$nxtyr)."-".(strlen($monthdd)==1?'0':'').$monthdd.'-01")
					ELSE
						"'.($currentyr+$nxtyr)."-".(strlen($monthdd)==1?'0':'').$monthdd."-".(strlen($_POST['DueDay'])==1?'0':'').$_POST['DueDay'].'"
					END)

					
					
					, EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=NOW();';
					// echo $sqlauto.'<br>';
					$stmtauto=$link->prepare($sqlauto); $stmtauto->execute();
					$monthco=$monthco+1;
					$monthdd=$monthdd+1;
			}
		header('Location:billduedates.php?w=AssignUtilityBill');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'EditSpecificsAssignUtilityBill':
        if (allowedToOpen(8002,'1rtc')) {
			$title='Edit Specifics';
			$txnid=intval($_GET['AssignID']);

			$sql=$sql1.' WHERE AssignID='.$txnid; //echo $sql;
			$columnstoedit=$columnstoadd;
			$columnnames=$columnnameslist;
			
			$columnswithlists=array('BillerName','BillType','SubscriberName','ShortAcctID','AccountNo','ExpenseOf','DeclaredforCompany','Assignee');
			$listsname=array('BillType'=>'billtypelist','BillerName'=>'billerlist','ShortAcctID'=>'expenseaccountlist','SubscriberName'=>'subscriberlist','AccountNo'=>'accountnolist','ExpenseOf'=>'branchlist','DeclaredforCompany'=>'companylist','Assignee'=>'empbranchlist');
		
			$editprocess='billduedates.php?w=EditAssignUtilityBill&AssignID='.$txnid;
			
			include('../backendphp/layout/editspecificsforlists.php');
		} else {
			echo 'No permission'; exit;
		}
	break;
	
	case 'EditAssignUtilityBill':
		if (allowedToOpen(8002,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['AssignID']);
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='UPDATE `acctg_4billassignment` SET '.$sql.' BTID='.$BTID.', ExpenseAccountID='.$ExpenseAccountID.',BillerID='.$BillerID.', ExpenseOfBranchNo='.$BranchNo.', DeclaredforCompanyNo='.$CompanyNo.', EncodedByNo='.$_SESSION['(ak0)'].',AssigneeNo='.$IDNo.', Timestamp=NOW() WHERE AssignID='.$txnid;
		//echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:billduedates.php?w=AssignUtilityBill");
		} else {
		echo 'No permission'; exit;
		}
		
    break;
	
	
	
	case 'DeleteAssignUtilityBill':
	if (allowedToOpen(8002,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `acctg_4billassignment` WHERE AssignID='.intval($_GET['AssignID']);
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:billduedates.php?w=AssignUtilityBill");
	} else {
		echo 'No permission'; exit;
		}

    break;
	
	
	case 'UploadList':
		if (allowedToOpen(8002,'1rtc')){
             $title='Upload List of Assigned Bills';
			$colnames=array('BTID','BillerID','SubscriberName','AccountNo','ExpenseAccountID','TelMobileNo','MRF','AssigneeNo','ExpenseOfBranchNo','DeclaredforCompanyNo','Active','CutOffDay','DueDay','EncodedByNo');
			$requiredcol=array('BTID','BillerID','SubscriberName','AccountNo','ExpenseAccountID','TelMobileNo','MRF','AssigneeNo','ExpenseOfBranchNo','DeclaredforCompanyNo','Active','CutOffDay','DueDay','EncodedByNo');
			$required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
			$allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
			$specific_instruct='BTID: Bill Type, BillerID: Biller'
					. '<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
			$tblname='acctg_4billassignment'; $firstcolumnname='BTID';
			$DOWNLOAD_DIR="../../uploads/"; $link=$link;
			include('../backendphp/layout/uploaddata.php');
			if(($row-1)>0){ echo '<a href="billduedates.php?w=AssignUtilityBill" target="_blank">Lookup Newly Imported Data</a>';}
			} else {
			echo 'No permission'; exit;
		}
    break;
	
	case 'SendToCV':
	if (allowedToOpen(8005,'1rtc')){
		$title='Send To CV';
		echo '<title>'.$title.'</title>';
		echo '<h2>'.$title.'</h2><br>';
		echo $filterform;
		echo '<title>'.$title.'</title>';
		if(isset($_SESSION['bill_due'])){	
			
		$addcondi='YEAR(DueDate)='.$currentyr.' AND MONTH(DueDate) ';
				
		$sql=$sql1.' JOIN acctg_4billsdue bd ON ua.AssignID=bd.AssignID LEFT JOIN 1_gamit.0idinfo id2 ON bd.ReceivedByNo=id2.IDNo WHERE ua.Active=1 AND Paid=0 '.(((!isset($_SESSION['bill_branch'])) OR ($_SESSION['bill_branch']==-1))?'':'AND ExpenseOfBranchNo='.$_SESSION['bill_branch'].'').' '.(((!isset($_SESSION['bill_biller'])) OR ($_SESSION['bill_biller']==-1))?'':'AND ua.BillerID='.$_SESSION['bill_biller'].'').' AND '.$addcondi.' BETWEEN '.(isset($_SESSION['bill_monthfrom'])?$_SESSION['bill_monthfrom']:date('m')).' AND '.(isset($_SESSION['bill_monthto'])?$_SESSION['bill_monthto']:date('m')).' ORDER BY DueDate';
		$stmt=$link->query($sql);
		
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		echo '<br/><form action="billduedates.php?w=SendToCVProcess" method="post">';
		
		
		echo '<br><br><table style="padding:2px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
		echo '<thead style="font-weight:bold;"><tr><td>Biller</td><td>Subscriber Name</td><td>AccountNo</td><td>TelMobileNo</td><td>MRF</td><td>ReceivedBy</td><td>ReceivedTS</td><td>BillAmount</td><td>Assignee</td><td>ExpenseOf</td><td>DueDate</td><td><input type="checkbox" class="chk_boxes" onclick="toggle(this);" />Check All?</td></tr></thead><tbody style=\"overflow:auto;\">';
			
			while ($row = $stmt->fetch())
			{
				echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['BillerName'].'</td><td>'.$row['SubscriberName'].'</td><td>'.$row['AccountNo'].'</td><td>'.$row['TelMobileNo'].'</td><td>'.$row['MRF'].'</td><td>'.$row['ReceivedBy'].'</td><td>'.$row['ReceivedTS'].'</td><td>'.$row['BillAmount'].'</td><td>'.$row['Assignee'].'</td><td>'.$row['ExpenseOf'].'</td><td>'.$row['DueDate'].'</td><td><input type="checkbox" value="'.$row['TxnID'].'" name="tovouch[]" /></td></tr>';
				$colorcount++;
			}
				echo '<tr><td colspan=12 align="right"><input style="background-color:yellow;" type="submit" value="Send To CV"/></td></tr>';
		echo '</tbody></table>';
		echo '</form>';
		}
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	case 'SendToCVProcess':
	if (allowedToOpen(8005,'1rtc')){
		if (isset($_REQUEST['tovouch'])){
			$tovouchid = implode(',', $_REQUEST['tovouch']);
			
			
			include_once $path.'/acrossyrs/commonfunctions/lastnum.php';
			$CVNo=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($currentyr.'-01-01'))).',2)') + 1;
			
			
			$sqlchkno = 'SELECT CheckNo FROM acctg_2cvmain WHERE LEFT(CheckNo,4)="Bill" ORDER BY REPLACE(CheckNo,"Bill","")*1 DESC LIMIT 1;';
			$stmtchkno=$link->query($sqlchkno);
			$resultchkno=$stmtchkno->fetch();
			$CheckNo = ((substr($resultchkno['CheckNo'], 0, 4)=='Bill')?'Bill'.(str_replace("Bill","",$resultchkno['CheckNo'])+1):'Bill1');
			
			
			
			$link=connect_db("".$currentyr."_1rtc",1);
			$sqldroptemp='DROP TABLE IF EXISTS tempTxn'.$_SESSION['(ak0)'].';';
			$stmtdroptable=$link->prepare($sqldroptemp); $stmtdroptable->execute();


			// FOR TREASURY TO CHANGE
			// used banktxns first then update to 403 after select
			$sqlvouchmain = 'SELECT BillerID AS PayeeNo,SupplierName AS Payee,AccountID FROM acctg_4billsdue bd JOIN acctg_4billassignment ua ON bd.AssignID=ua.AssignID JOIN banktxns_1maintaining m ON ua.DeclaredforCompanyNo=m.RCompanyUse JOIN 1suppliers s ON ua.BillerID=s.SupplierNo WHERE TxnID IN ('.$tovouchid.') GROUP BY Payee,DeclaredforCompanyNo;';
			
			$stmtvouchmain=$link->query($sqlvouchmain);
			
			while($resvouchmain=$stmtvouchmain->fetch()) {
				
				//Insert Into Main
				$sqlexecmain='INSERT INTO acctg_2cvmain (CVNo,CheckNo,Date,DateofCheck,PayeeNo,Payee,CreditAccountID,TimeStamp,EncodedByNo) VALUES ('.$CVNo.',"'.$CheckNo.'",CURDATE(),CURDATE(),'.$resvouchmain['PayeeNo'].',"'.$resvouchmain['Payee'].'",'.$resvouchmain['AccountID'].',NOW(),'.$_SESSION['(ak0)'].');';
				$stmtexecmain=$link->prepare($sqlexecmain); $stmtexecmain->execute();
				
				$sqltemptable='CREATE TABLE tempTxn'.$_SESSION['(ak0)'].' AS SELECT CVNo,RCompanyUse,PayeeNo FROM acctg_2cvmain vm JOIN banktxns_1maintaining m ON vm.CreditAccountID=m.AccountID WHERE CVNo='.$CVNo.';';
				$stmttemptable=$link->prepare($sqltemptable); $stmttemptable->execute();
				
				
				$sqlvouchsub = 'SELECT bd.TxnID AS DueTxnID, t.CVNo,CONCAT(Nickname,"_",SurName,"/",AccountNo,"/",CutOffDate) AS Particulars,ua.ExpenseAccountID AS DebitAccountID,MRF AS BillAmount,MRF AS Amount, ExpenseOfBranchNo AS BranchNo FROM tempTxn'.$_SESSION['(ak0)'].' t JOIN acctg_4billassignment ua ON t.RCompanyUse=ua.DeclaredforCompanyNo JOIN acctg_4billsdue bd ON ua.AssignID=bd.AssignID JOIN `1_gamit`.`0idinfo` id ON ua.AssigneeNo=id.IDNo WHERE bd.TxnID IN ('.$tovouchid.') AND t.CVNo='.$CVNo.' UNION ALL SELECT bd.TxnID AS DueTxnID, t.CVNo,CONCAT(Nickname,"_",SurName,"/",AccountNo,"/",CutOffDate,"/ExcessUsage") AS Particulars,205 AS DebitAccountID,Amount AS BillAmount,(Amount-MRF) AS Amount, ExpenseOfBranchNo AS BranchNo FROM tempTxn'.$_SESSION['(ak0)'].' t JOIN acctg_4billassignment ua ON t.RCompanyUse=ua.DeclaredforCompanyNo JOIN acctg_4billsdue bd ON ua.AssignID=bd.AssignID JOIN `1_gamit`.`0idinfo` id ON ua.AssigneeNo=id.IDNo WHERE bd.TxnID IN ('.$tovouchid.') AND t.CVNo='.$CVNo.' HAVING Amount>0 UNION ALL SELECT bd.TxnID AS DueTxnID, t.CVNo,CONCAT(Branch,"/",AccountNo,"/",CutOffDate) AS Particulars,ua.ExpenseAccountID AS DebitAccountID,MRF AS BillAmount,MRF AS Amount, ExpenseOfBranchNo AS BranchNo FROM tempTxn'.$_SESSION['(ak0)'].' t JOIN acctg_4billassignment ua ON t.RCompanyUse=ua.DeclaredforCompanyNo JOIN acctg_4billsdue bd ON ua.AssignID=bd.AssignID JOIN `1branches` b ON ua.AssigneeNo=b.BranchNo WHERE bd.TxnID IN ('.$tovouchid.') AND t.CVNo='.$CVNo.' UNION ALL SELECT bd.TxnID AS DueTxnID, t.CVNo,CONCAT(Branch,"/",AccountNo,"/",CutOffDate,"/ExcessUsage") AS Particulars,205 AS DebitAccountID,Amount AS BillAmount,(Amount-MRF) AS Amount, ExpenseOfBranchNo AS BranchNo FROM tempTxn'.$_SESSION['(ak0)'].' t JOIN acctg_4billassignment ua ON t.RCompanyUse=ua.DeclaredforCompanyNo JOIN acctg_4billsdue bd ON ua.AssignID=bd.AssignID JOIN `1branches` b ON ua.AssigneeNo=b.BranchNo WHERE bd.TxnID IN ('.$tovouchid.') AND t.CVNo='.$CVNo.' HAVING Amount>0';
				$stmtvouchsub=$link->query($sqlvouchsub);
				
				
				while ($resvouchsub=$stmtvouchsub->fetch()) {
					$sqlexecsub='INSERT INTO acctg_2cvsub (CVNo,Particulars,DebitAccountID,Amount,Timestamp,BranchNo,FromBudgetOf,EncodedByNo) VALUES ('.$resvouchsub['CVNo'].',"'.$resvouchsub['Particulars'].'",'.$resvouchsub['DebitAccountID'].','.$resvouchsub['Amount'].',NOW(),'.$resvouchsub['BranchNo'].','.$resvouchsub['BranchNo'].','.$_SESSION['(ak0)'].');';
					$stmtexecsub=$link->prepare($sqlexecsub); $stmtexecsub->execute();
					
					
					//Set Paid
					$sqlpaid='UPDATE acctg_4billsdue SET Paid='.$resvouchsub['CVNo'].', SentToVouch=1, SentByNo='.$_SESSION['(ak0)'].', SentTS=NOW(), Amount='.$resvouchsub['BillAmount'].', MarkPaidTS=NOW(), MarkPaidByNo='.$_SESSION['(ak0)'].' WHERE TxnID='.$resvouchsub['DueTxnID'].'';
					$stmtpaid=$link->prepare($sqlpaid); $stmtpaid->execute();
					
					
				}
				$link=connect_db("".$currentyr."_1rtc",1);
				$sqldroptemp='DROP TABLE IF EXISTS tempTxn'.$_SESSION['(ak0)'].';';
				$stmtdroptable=$link->prepare($sqldroptemp); $stmtdroptable->execute();
				
				//update to 403 accountid
				$sqlcred='UPDATE acctg_2cvmain SET CreditAccountID=403 WHERE CVNo='.$CVNo.';';
				$stmtcred=$link->prepare($sqlcred); $stmtcred->execute();



				$CVNo=$CVNo+1;
				$CheckNo='Bill'.(str_replace("Bill","",$CheckNo)+1);




				
			}
			
			header("Location:billduedates.php?w=MonitorBills");
			
		}
		else
		{
			echo 'Please select at least 1.';
		}
		
	} else {
		echo 'No permission'; exit;
	}
	
	break;
	
	case 'MonitorBills':
	if (allowedToOpen(8000,'1rtc')){
	$title = 'Bills Monitoring';
	echo '<title>'.$title.'</title>';
	echo '<h2>'.$title.'</h2><br>';
	echo $filterform;
	
	echo '<br><h3>'.$filtertitle.'</h3>';
	
		if (isset($_POST['btnLookUpCutOff'])){
				$addcondi=' YEAR(CutOffDate)='.$currentyr.' AND MONTH(CutOffDate) ';
			} else {
				$addcondi=' YEAR(DueDate)='.$currentyr.' AND MONTH(DueDate) ';
			}
			
		$columnnames=array('BillerName','SubscriberName', 'AccountNo', 'TelMobileNo', 'MRF','ReceivedBy','ReceivedTS', 'BillAmount','Remarks','Assignee','ExpenseOf','DueDate');
		$title='';
		echo '<br><h4>Unpaid Bills</h4>';
		
		$sql2=' JOIN acctg_4billsdue bd ON ua.AssignID=bd.AssignID LEFT JOIN 1_gamit.0idinfo id2 ON bd.ReceivedByNo=id2.IDNo WHERE ua.Active=1 ';
		$sql3=(((!isset($_SESSION['bill_branch'])) OR ($_SESSION['bill_branch']==-1))?'':'AND ExpenseOfBranchNo='.$_SESSION['bill_branch'].'').' '.(((!isset($_SESSION['bill_biller'])) OR ($_SESSION['bill_biller']==-1))?'':'AND ua.BillerID='.$_SESSION['bill_biller'].'').' AND '.$addcondi.' BETWEEN '.(isset($_SESSION['bill_monthfrom'])?$_SESSION['bill_monthfrom']:date('m')).' AND '.(isset($_SESSION['bill_monthto'])?$_SESSION['bill_monthto']:date('m')).' ORDER BY ExpenseOf,DueDate';
		
		$sql=$sql1.$sql2.' AND Paid=0 AND ua.Active=1 '.$sql3;
		// echo $sql;
		if (allowedToOpen(8004,'1rtc')){
			$editprocess='billduedates.php?w=Paid&TxnID='; $editprocesslabel='Manually Set as Paid';
		}
		$columnstoedit=array('BillAmount','Remarks'); $txnid='TxnID';
		
		// include('../backendphp/layout/displayastableeditcells.php');
		include('../backendphp/layout/displayastableeditcellswithsorting.php');
		echo '<br><br><h4>Paid Bills</h4>';
		unset($editprocess);
		
		$sql1=str_replace("bd.TxnID","bd.Paid AS TxnID",$sql1);
		$sql=$sql1.$sql2.' AND Paid<>0 '.$sql3;
		
		
		$newtab=true;
		$txnid='TxnID';
		if (allowedToOpen(8005,'1rtc')){
			$editprocess='addeditsupplyside.php?w=CV&TxnID='; $editprocesslabel='Lookup';
		}
		// include('../backendphp/layout/displayastablenosort.php');
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'Paid':
	if (allowedToOpen(8004,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		$sql='UPDATE `acctg_4billsdue` SET Paid=1,Amount="'.(!is_numeric($_POST['BillAmount'])?str_replace(',', '',$_POST['BillAmount']):$_POST['BillAmount']).'",Remarks="'.$_POST['Remarks'].'", MarkPaidByNo='.$_SESSION['(ak0)'].',MarkPaidTS=NOW() WHERE TxnID='.intval($_GET['TxnID']).';';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:billduedates.php?w=MonitorBills');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'GeneratePage':
	if (allowedToOpen(8002,'1rtc')){
		
		$title = 'Bills To Be Recorded';
		
		$sql='SELECT ua.*,BillType,ShortAcctID,SupplierName AS BillerName,b.Branch AS ExpenseOf, IF(AssigneeNo<1000,b2.Branch,CONCAT(id.Nickname," ",id.Surname)) AS Assignee,Company AS DeclaredforCompany,"'.date('F').'" AS StartMonth,"December" AS EndMonth FROM acctg_4billassignment ua JOIN acctg_0billtype bt ON ua.BTID=bt.BTID JOIN 1suppliers s ON ua.BillerID=s.SupplierNo JOIN acctg_1chartofaccounts coa ON ua.ExpenseAccountID=coa.AccountID LEFT JOIN 1_gamit.0idinfo id ON ua.AssigneeNo=id.IDNo JOIN 1companies c ON ua.DeclaredforCompanyNo=c.CompanyNo JOIN 1branches b ON ua.ExpenseOfBranchNo=b.BranchNo LEFT JOIN 1branches b2 ON ua.AssigneeNo=b2.BranchNo WHERE ua.Active<>0 AND AssignID NOT IN (SELECT DISTINCT(AssignID) FROM acctg_4billsdue);'; 
		
		$columnnames=array('BillerName','BillType','SubscriberName', 'AccountNo','ShortAcctID', 'TelMobileNo', 'MRF', 'Assignee', 'ExpenseOf', 'DeclaredforCompany','CutOffDay','DueDay','StartMonth','EndMonth');
		include('../backendphp/layout/displayastablenosort.php');
		
		
		if (!isset($_POST['btnGenerate'])){
			echo '<br><br><form action="billduedates.php?w=GeneratePage" method="POST"><input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token"><input type="submit" style="background-color:yellow;" name="btnGenerate" value="Record All Data"></form>';
		
		}
		
		
		if (isset($_POST['btnGenerate'])){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='SELECT ua.* FROM acctg_4billassignment ua WHERE AssignID NOT IN (SELECT DISTINCT(AssignID) FROM acctg_4billsdue);';
			$stmt=$link->query($sql);
			
			while($row=$stmt->fetch()) {
				if ($row['CutOffDay']<$row['DueDay']){
					$lessthan='yes';
					$monthco=date('m');
				} else {
					$lessthan='no';
					$monthco=date('m')+1;
				}
				$monthdd=(($lessthan=='yes')?$monthco:$monthco+1);
				while ($monthco<=12){
					if ($monthdd==13){ //nxt yr january only for due date
						$monthdd=1;
						$nxtyr=1;
					} else { //for condition only
						$nxtyr=0;
					}
					$sqlauto='INSERT INTO `acctg_4billsdue` SET AssignID='.$row['AssignID'].', CutOffDate="'.$currentyr."-".(strlen($monthco)==1?'0':'').$monthco."-".(strlen($row['CutOffDay'])==1?'0':'').$row['CutOffDay'].'", DueDate="'.($currentyr+$nxtyr)."-".(strlen($monthdd)==1?'0':'').$monthdd."-".(strlen($row['DueDay'])==1?'0':'').$row['DueDay'].'", EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=NOW()';
					
					$stmtauto=$link->prepare($sqlauto); $stmtauto->execute();
					$monthco=$monthco+1;
					$monthdd=$monthdd+1;
				}
		}
		echo '<br><br><h3><font color="green">Data successfully recorded.</font></h3>';
		}
		
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'DeleteBills':
	if(!allowedToOpen(8005,'1rtc')){ echo 'No Permission'; exit(); }
	$title='Delete Bills Data';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br>';
	
	echo comboBox($link,'SELECT AssigneeNo,FullName FROM `acctg_4billassignment` ua JOIN attend_30currentpositions cp ON ua.AssigneeNo=cp.IDNo UNION SELECT AssigneeNo,Branch FROM `acctg_4billassignment` ua JOIN 1branches b ON ua.AssigneeNo=b.BranchNo;','AssigneeNo','FullName','empbranch');
	
	echo '<form action="billduedates.php?w=DeleteBills" method="POST">Assignee: <input type="text" name="Assignee" list="empbranch"> <input type="submit" name="btnLookup" value="Lookup"></form>';
	
	if (isset($_POST['btnLookup'])){
		
		 $sql1='SELECT ua.*,IF(AssigneeNo<1000,b2.Branch,CONCAT(id.Nickname," ",id.Surname)) AS Assignee FROM acctg_4billassignment ua LEFT JOIN 1_gamit.0idinfo id ON ua.AssigneeNo=id.IDNo LEFT JOIN 1branches b2 ON ua.AssigneeNo=b2.BranchNo WHERE ua.AssigneeNo='.$IDNo.'';
		// echo $sql1;
		 $stmt1=$link->query($sql1);
		$res=$stmt1->fetchAll();
		
		 $stmtmain=$link->query($sql1);
		$resmain=$stmtmain->fetch();
		
		echo '<br><h4>'.$resmain['Assignee'].'</h4>';
		
		
		echo '<form action="billduedates.php?w=DeleteBillsProcess" method="POST"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
		foreach ($res AS $field){
			$sql='SELECT SupplierName,TxnID,AccountNo,CutOffDate,DueDate,Paid FROM acctg_4billassignment ua JOIN acctg_4billsdue bd ON ua.AssignID=bd.AssignID LEFT JOIN 1suppliers s ON ua.BillerID=s.SupplierNo WHERE ua.AssignID='.$field['AssignID'].';';
			
			$stmt=$link->query($sql);
			$ressub=$stmt->fetchAll(); 
			
			$stmttitle=$link->query($sql);
			$restitle=$stmttitle->fetch();
			
			$cntrow=$stmttitle->rowCount();
			
			if($cntrow>0){
				echo '<br><table style="border:1px solid black;padding:5px">';
				echo '<tr><td></td><td>CutOffDate</td><td>DueDate</td></tr>';
				echo 'BillerName: <b>'.$restitle['SupplierName'].'</b>, AccountNo: <b>'.$restitle['AccountNo'].'</b>';
				foreach ($ressub AS $fieldsub){
					echo '<tr>';
						echo '<td>'.($fieldsub['Paid']==0?'<input type="checkbox" value="'.$fieldsub['TxnID'].'" name="txnid[]" />':'').'</td>';
						echo '<td>'.$fieldsub['CutOffDate'].'</td>';
						echo '<td>'.$fieldsub['DueDate'].'</td>';
					echo '</tr>';
				}
				echo '</table>';
			}
		}
		
			echo '<br><input style="background-color:blue;color:white;width:100px;" type="submit" name="DelData" value="Delete Data" OnClick="return confirm(\'Really delete data?\');">';
			echo '</form>';
		
	}
	
	break;
	
	
	case 'TransferAssignees':
	$title='Transfer Assignees';
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3><br>';
		echo '<form action="billduedates.php?w=TransferAssignees" method="POST">';
			echo 'Account Number: <input type="text" name="AccountNo" list="accountnolist" autocomplete="off">';
			echo ' <input type="submit" name="CheckAccountNo" value="Lookup">';
			
		echo '</form><br>';
		
		if(isset($_POST['CheckAccountNo'])){
			$sql='SELECT CutOffDay,DueDay,CONCAT(Nickname," ", SurName) AS CurrentlyAssignedTo FROM acctg_4billassignment ba JOIN 1_gamit.0idinfo id ON ba.AssigneeNo=id.IDNo WHERE Active=1 AND AccountNo="'.$_POST['AccountNo'].'";';
			$stmt=$link->query($sql);
			$res=$stmt->fetch(); 
			echo '<div style="border:1px solid blue;width:30%;padding:10px;">';
			echo '<b>Account Number: '.$_POST['AccountNo'].'<br>Currently Assigned To: '.$res['CurrentlyAssignedTo'].'</b>';
				echo '<div style="margin-left:3%;">';
				echo '<form action="billduedates.php?w=Transfer" method="POST">';
				echo 'Transfer to: <input type="text" name="Assignee" list="empbranchlist">';
				echo '<input type="hidden" name="CutOffDay" value="'.$res['CutOffDay'].'">';
				echo '<input type="hidden" name="DueDay" value="'.$res['DueDay'].'">';
				echo '<input type="hidden" name="AccountNo" value="'.$_POST['AccountNo'].'">';
				echo ' <input type="submit" name="Transfer" value="Transfer" OnClick="return confirm(\'Are you SURE?\');">';
				echo '</form>';
				echo '<div>';
			echo '<div>';
		}
	break;
	
	case 'Transfer':
	
	$sqlc='SELECT AssignID FROM acctg_4billassignment WHERE Active=1 AND AccountNo="'.$_POST['AccountNo'].'"';
	$stmtc=$link->query($sqlc); $resc=$stmtc->fetch(); 
	
	//update inactive first
	$sqlinc='UPDATE acctg_4billassignment SET Active=0 WHERE AssignID='.$resc['AssignID'];
	$stmtinc=$link->prepare($sqlinc); $stmtinc->execute();
	
	//insert new main
		$sql='SELECT (SELECT GROUP_CONCAT(COLUMN_NAME) FROM information_schema.columns WHERE table_schema = "'.$currentyr.'_1rtc" AND table_name = "acctg_4billassignment" AND column_name NOT IN ("AssignID","AssigneeNo","Active")) AS allfieldsexcept;';
		$stmt=$link->query($sql); $row=$stmt->fetch();
		$allfieldsexcept=$row['allfieldsexcept'];
		
		// echo $allfieldsexcept;
		
		// echo $_POST['Assignee'];
		$sqlinsert='INSERT INTO acctg_4billassignment ('.$row['allfieldsexcept'].',AssigneeNo,Active) SELECT '.$allfieldsexcept.',"'.$IDNo.'",1 FROM acctg_4billassignment WHERE AssignID='.$resc['AssignID'];
		// echo $sqlinsert;
		$stmtinsert=$link->prepare($sqlinsert); $stmtinsert->execute();
	
		//insert new sub
		//Last ID FROM acctg_4billassignment insert
		$sql='SELECT LAST_INSERT_ID() AS AssignID;';
		$stmt=$link->query($sql); $result=$stmt->fetch();
		
		//Auto Populate acctg_4billsdue
		if ($_POST['CutOffDay']<$_POST['DueDay']){
			$lessthan='yes';
			$monthco=date('m');
		} else {
			$lessthan='no';
			$monthco=date('m')+1;
		}
		$monthdd=(($lessthan=='yes')?$monthco:$monthco+1);
		while ($monthco<=12){
			if ($monthdd==13){ //nxt yr january only for due date
				$monthdd=1;
				$nxtyr=1;
			} else { //for condition only
				$nxtyr=0;
			}
			$sqlauto='INSERT INTO `acctg_4billsdue` SET AssignID='.$result['AssignID'].', CutOffDate="'.$currentyr."-".(strlen($monthco)==1?'0':'').$monthco."-".(strlen($_POST['CutOffDay'])==1?'0':'').$_POST['CutOffDay'].'", DueDate="'.($currentyr+$nxtyr)."-".(strlen($monthdd)==1?'0':'').$monthdd."-".(strlen($_POST['DueDay'])==1?'0':'').$_POST['DueDay'].'", EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=NOW()';
			
			$stmtauto=$link->prepare($sqlauto); $stmtauto->execute();
			$monthco=$monthco+1;
			$monthdd=$monthdd+1;
		}
	
	header("Location:billduedates.php?w=AssignmentHistory&AccountNo=".$_POST['AccountNo']);
	
	
	break;
	
	case 'DeleteBillsProcess':
		if(!allowedToOpen(8005,'1rtc')){ echo 'No Permission'; exit(); }
		$title='Delete Data';
		echo '<title>'.$title.'</title>';
	
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		if (isset($_REQUEST['txnid'])){
			foreach ($_REQUEST['txnid'] AS $txnid){
				//DELETE FROM acctg_4billsdue
				$sql0='DELETE FROM acctg_4billsdue WHERE TxnID='.$txnid.' AND Paid=0';
				$stmt=$link->prepare($sql0); $stmt->execute();
			}
			
			echo '<font color="green"><h3>Data deleted successfully.</h3></font>';
		}
		else
		{
			echo 'Please select at least 1.';
		}
	break;
	
}


 $link=null; $stmt=null;
?>
</div> <!-- end section -->

