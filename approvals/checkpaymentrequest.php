<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
// check if allowed
$allowed=array(5236,100,1500,5237,5231); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
    if(isset($_GET['Print'])) { goto skipcontents;}
$showbranches=false;
include_once('../switchboard/contents.php');
skipcontents:

$diraddress='../';

include_once($path.'/acrossyrs/js/includesscripts.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
		
//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';
?>


<br><!--<div id="section" style="display: block;">-->

<?php

$which=(!isset($_GET['w'])?'List':$_GET['w']);
$txnid=(!isset($_REQUEST['TxnID']))?'TxnID':$_REQUEST['TxnID'];

if (in_array($which,array('List','EditSpecifics'))){
	echo '<br>';
	include_once('../backendphp/layout/showencodedbybutton.php');
	
   $sql='SELECT cpr.*,Entity AS FromBudgetOf, (CASE
		WHEN Approved = 1 THEN "Approved"
		WHEN Approved = 2 THEN "Denied"
		ELSE "Pending"
	END) AS Approved, (CASE
		WHEN Approved = 1 THEN "Approved"
		WHEN Approved = 2 THEN "Denied"
		ELSE "Pending"
	END) AS DeptHeadApproval, (CASE
		WHEN Approved2 = 1 THEN "Approved"
		WHEN Approved2 = 2 THEN "Denied"
		ELSE (IF(Recurring=1,"RegularExpense","Pending"))
	END) AS FinanceDeptHead_Approval, (CASE
		WHEN Acknowledge = 1 THEN "Done"
		WHEN Acknowledge = 2 THEN "Denied"
		ELSE "Pending"
	END) AS Acctg_Acknowledgement, (CASE
		WHEN CheckIssued = 1 THEN "Complete"
		ELSE "Incomplete"
	END) AS CheckPreparation, (CASE
		WHEN ReceiptReceived = 1 THEN "Received"
		WHEN ReceiptReceived = 2 THEN "Denied"
		ELSE "Pending"
	END) AS ReceiptStatus, (CASE
		WHEN CheckIssued = 1 THEN "Complete"
		ELSE "Incomplete"
	END) AS CheckPreparation, IF(Recurring=1,"Yes","No") AS RecurringDesc, IF(RequestCompleted<>0,"Done","") AS RequestCompleted, b.Branch, CONCAT(e.Nickname, " ", e.Surname) AS RequestedBy FROM approvals_4checkpayment cpr JOIN `1branches` b ON cpr.BranchNo=b.BranchNo LEFT JOIN `1employees` e ON cpr.RequestedByNo=e.IDNo LEFT JOIN `acctg_1budgetentities` be ON cpr.FromBudgetOf=be.EntityID';
   
   $columnnameslist=array('Payee', 'DateRequest', 'DatePayment', 'Particulars', 'Amount', 'FromBudgetOf','Branch', 'RequestCompleted', 'DeptHeadApproval', 'Recurring', 'RecurringDesc', 'FinanceDeptHead_Approval', 'Acctg_Acknowledgement','CheckPreparation','ReceiptStatus');
   $columnstoadd=array('Payee', 'DateRequest', 'DatePayment', 'Particulars', 'Amount', 'FromBudgetOf', 'Branch', 'RequestCompleted', 'Recurring');
   if ($showenc==1) { array_push($columnnameslist,'RequestedBy','TimeStamp');}
}

if (in_array($which,array('List','EditSpecifics','EditSpecifics','NewRequest','Lookup'))){
	if (!isset($_GET['Print'])){ $printstyle='';
    ?>
    <div style='background-color: #e6e6e6;
  width: 900px;
  border: 2px solid grey;
  padding: 25px;
  margin: 25px;'>
    <b>When to use:</b><br/><br/>
    <ol>
        <li>Payment requests for non-employees or other companies.</li>
        <li>Payment requests for employees that do not need liquidation, such as initial petty cash assignment.</li>
    </ol><br/><br/>
    <b>Process:</b><br/><br/>    
    <ol>
        <li>Requester encodes all details, and sets request as complete.</li>
        <li>Dept Head approves/denies.</li>
        <li>If approved by Dept Head, and if not regular and recurring expenses, Finance Dept Head approves/denies.</li>
        <li>If approved by Finance Dept Head, Acctg acknowledges receipt of the request, and proceeds to make the check payment.</li>
        <li>Accounting sets the request as done with the check issuance.</li>
		<li>Accounting gives the signed check to requester.  Requester must return the voucher and attachments, together with the receipt from the check payee, to Accounting.</li>
        <li>Accounting must set Receipt Status as Received when all documents have been returned.  This ends the RCP process.</li>
    </ol><br/>
	<ul><li>
	Note: Each step can set previous step as incomplete in order to edit the request, if necessary.</li></ul>
    </div>
    <?php
	} else {
		$printstyle = 'style="background-color:transparent;border-color:transparent;color:black;text-align: right;"'; 
	}
}


if (in_array($which,array('Add','Edit'))){
	$branchno=comboBoxValue($link, ' `1branches` ', 'Branch', $_POST['Branch'], 'BranchNo');
	$frombudgetof=comboBoxValue($link, ' `acctg_1budgetentities` ', 'Entity', $_POST['FromBudgetOf'], 'EntityID');
   $columnstoadd=array('DateRequest','DatePayment','Payee','Particulars','Amount','Recurring');
}
if (in_array($which,array('EditSpecifics','NewRequest'))){
	echo comboBox($link,'SELECT BranchNo, Branch FROM `1branches` WHERE Active=1 AND BranchNo>=0 AND BranchNo NOT IN (95)','BranchNo','Branch','branchnames');
	echo comboBox($link,'SELECT DISTINCT Payee FROM `approvals_4checkpayment` ORDER BY Payee','Payee','Payee','payeelist');
	echo comboBox($link,'SELECT EntityID,Entity FROM `acctg_1budgetentities` ORDER BY Entity;','EntityID','Entity','entities');
}

if (in_array($which,array('Lookup','Print'))){
	$sql = 'SELECT cpr.*,Entity AS FromBudgetOf, b.Branch, CONCAT(e.Nickname, " ", e.Surname) AS PreparedBy,CONCAT(e1.Nickname, " ", e1.Surname) AS ApprovedBy,CONCAT(e2.Nickname, " ", e2.Surname) AS Approved2By, CONCAT(e3.Nickname, " ", e3.Surname) AS AcknowledgeBy, CONCAT(e4.Nickname, " ", e4.Surname) AS ReceiptReceivedBy, CONCAT(e5.Nickname, " ", e5.Surname) AS CheckIssuedBy FROM approvals_4checkpayment cpr JOIN `1branches` b ON cpr.BranchNo=b.BranchNo LEFT JOIN `1employees` e ON cpr.RequestedByNo=e.IDNo LEFT JOIN `1employees` e1 ON cpr.ApprovedByNo=e1.IDNo LEFT JOIN `1employees` e2 ON cpr.Approved2ByNo=e2.IDNo LEFT JOIN `1employees` e3 ON cpr.AcknowledgeByNo=e3.IDNo LEFT JOIN `1employees` e4 ON cpr.ReceiptReceivedByNo=e4.IDNo LEFT JOIN `1employees` e5 ON cpr.CheckIssuedByNo=e5.IDNo LEFT JOIN `acctg_1budgetentities` be ON cpr.FromBudgetOf=be.EntityID WHERE TxnID = '.$txnid;
	$stmt=$link->query($sql); $res=$stmt->fetch();
	
	$title='Request for Check Payment';
	
	
}


switch ($which)
{
	//Start of Case List
	case 'List':
		
		$formdesc='</i><form action="#" method="POST">Filter By:<select name="filterby">
		<option value="0">Unfinished Requests</option>
		<option value="1">Denied Requests</option>
		<option value="2">Pending Approvals - DeptHead</option>
		<option value="3">Pending Approvals - Finance Dept Head</option>
		<option value="4">To Be Issued</option>
		<option value="5">Waiting for Receipt</option>
		<option value="6">Done Requests</option>
		<option value="7">All Requests</option></select>
		<input type="submit" name="btnSubmit" value="Filter"></form><i>'; $txnidname='TxnID';
		
		if (allowedToOpen(5238,'1rtc')) {
			$defaultfilter = ' WHERE ReceiptReceived=0';
		} else { $defaultfilter = ' WHERE CheckIssued=0'; }
		
		if(!isset($_POST['btnSubmit'])){$_POST['filterby']=''; $filter = $defaultfilter; $_POST['filterby']=4;}
		else {
			if ($_POST['filterby']==0){ $subtitle1 = ' (Unfinished Requests)'; $filter = ' WHERE RequestCompleted=0';}
			else if ($_POST['filterby']==1){$subtitle1 = ' (Denied Requests)'; $filter = ' WHERE (Approved=2 OR Approved2=2 OR Acknowledge=2) GROUP BY TxnID';}
			else if ($_POST['filterby']==2){$subtitle1 = ' (Pending Approvals - DeptHead)'; $filter = ' WHERE RequestCompleted=1 AND (Approved=0)';}
			else if ($_POST['filterby']==3){$subtitle1 = ' (Pending Approvals - Finance Dept Head)'; $filter = ' WHERE RequestCompleted=1 AND Approved=1 AND (Approved2=0 AND Recurring<>1)';}
			else if ($_POST['filterby']==4){$subtitle1 = ' (To Be Issued)'; $filter =  ' WHERE RequestCompleted=1 AND ((Approved = 1 AND Approved2 = 1 AND Recurring=0) OR (Approved = 1 AND Approved2=0 AND Recurring=1)) AND Acknowledge = 1 AND CheckIssued=0';}
			else if ($_POST['filterby']==5){$subtitle1 = ' (Waiting for Receipt)'; $filter = ' WHERE CheckIssued=1 AND ReceiptReceived=0';}
			else if ($_POST['filterby']==6){$subtitle1 = ' (Done Requests)'; $filter = ' WHERE ReceiptReceived=1';}
			else if ($_POST['filterby']==7){$subtitle1 = ' (All Requests)'; $filter = '';}
		}
		
		// if(isset($_GET['filterby']) AND $_GET['filterby']==0 AND (!isset($_POST['btnSubmit']))) {$filter =  ' WHERE RequestCompleted=0';}
		
		$title='List of Check Payment Requests' . (isset($_POST['btnSubmit'])?$subtitle1:'');
		$columnnames=$columnnameslist;
		$sql .= $filter;
		
		echo '<br><br><h4><a href="checkpaymentrequest.php?w=NewRequest">Add New Request for Check Payment</a></h4>';
		
		if (allowedToOpen(5236,'1rtc')) {
		
			$fieldsinrow=3; $liststoshow=array();

			$delprocess='checkpaymentrequest.php?w=Delete&TxnID=';
			$addlprocess='checkpaymentrequest.php?w=EditSpecifics&filterby='.$_POST['filterby'].'&TxnID='; $addlprocesslabel='Edit';
			$editprocess='checkpaymentrequest.php?w=Lookup&TxnID='; $editprocesslabel='Lookup';
		}
		include('../backendphp/layout/displayastable.php'); 
	break; //End of Case List
	
	case 'NewRequest':
            $title='New Request for Check Payment';
            ?><title><?php  echo $title; ?></title>
                <br><br><h4><?php  echo $title; ?></h4><br>
                <style>.hoverTable tr:hover {
                        background-color: transparent;</style><div>
		<form method='POST' action='checkpaymentrequest.php?w=Add' style='display: inline;' >
					<table class="hoverTable">
                                            <tr><td></td><td align="right">Date of Request <input type='date' name='DateRequest' size=5 required=true value='<?php echo date('Y-m-d')?>'><td></tr>
					<input type='date' name='DateRequest' size=5 required=true value="<?php echo date('Y-m-d');?>" hidden>
                    <tr><td></td><td align="right">Date of Payment <input type='date' name='DatePayment' size=5 required=true><td></tr><tr><td style="padding:20px;"></td></tr>
                    <tr><td>Expense of Company/Branch <input type='text' name='Branch' size=11 required=true list='branchnames'></td><td></td></tr>
					<tr><td>Payee <input type='text' name='Payee' size=30 required=true list='payeelist' autocomplete='off'></td><td></td></tr>
					<tr><td>From Budget Of <input type='text' name='FromBudgetOf' size=20 required=true list='entities' autocomplete='off'></td><td></td></tr>
					<tr><td style="padding:15px"></td></tr>
                                            <tr><td>Particulars<br><textarea type='text' name='Particulars' required=true rows="6" cols="65"></textarea></td><td valign="top" align="right">Amount <input type='number' name='Amount' size=10 min="0" step="any" required=true><br>Recurring <select name="Recurring"><option value="0" selected>No</option><option value="1">Yes</option></select><br/><font size=2>Mark as recurring if this is a reqular expense <br/>that does not have to be approved by Finance Dept Head.</font></td></tr>
			<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>">
			<tr><td></td><td align="right"><input type='submit' name='submit' value='Submit Request'></td></tr></table> </form></div>
      
                
                <?php
            break;
		
		case 'Add':
		if (allowedToOpen(5236,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$sql=''; 
			foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; } 
			$sql='INSERT INTO `approvals_4checkpayment` SET BranchNo='.$branchno.', FromBudgetOf='.$frombudgetof.', '.$sql.' TimeStamp=Now(), RequestedByNo=\''.$_SESSION['(ak0)'].'\''; 
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute(); 
			
					$sql='SELECT TxnID FROM `approvals_4checkpayment` WHERE RequestedByNo='.$_SESSION['(ak0)'].' AND BranchNo='.$branchno.' AND DATE_FORMAT(TimeStamp,"%Y-%m-%d") LIKE \''.date('Y-m-d').'\' ORDER BY TimeStamp DESC';
					$stmt=$link->query($sql);$result=$stmt->fetch();
			header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$result['TxnID']);
		}
		break;
	
		case 'EditSpecifics':
	    
		$title='Edit Specifics';

		$sql=$sql.' WHERE TxnID='.$txnid;
		// echo $sql;
		$columnstoedit=array_diff($columnnameslist,array('RequestCompleted','DeptHeadApproval','RecurringDesc', 'FinanceDeptHead_Approval', 'Acctg_Acknowledgement','CheckPreparation','ReceiptStatus'));	
        $columnnames=$columnnameslist;
		
		$columnswithlists=array('Payee','Branch','FromBudgetOf');
		$listsname=array('Payee'=>'payeelist', 'Branch'=>'branchnames', 'FromBudgetOf'=>'entities');
		
		$editprocess='checkpaymentrequest.php?w=Edit&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
		
	break;
			
	case 'Edit':
		if (allowedToOpen(5236,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
					
			$sql='';
			foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
			$sql='UPDATE `approvals_4checkpayment` SET FromBudgetOf='.$frombudgetof.',BranchNo='.$branchno.', '.$sql.' TimeStamp=Now(), RequestedByNo=\''.$_SESSION['(ak0)'].'\' WHERE RequestCompleted=0 AND Approved=0  AND Approved2=0 AND Acknowledge=0 AND CheckIssued=0 AND RequestedByNo='.$_SESSION['(ak0)'].' AND TxnID='.$txnid.''; 
// echo $sql; exit();
					$stmt=$link->prepare($sql); $stmt->execute();
			header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
			break;
		}
    case 'Delete':
	
        if (allowedToOpen(5236,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$sql='DELETE FROM `approvals_4checkpayment` WHERE RequestCompleted=0 AND Approved=0  AND Approved2=0 AND Acknowledge=0 AND CheckIssued=0 AND RequestedByNo='.$_SESSION['(ak0)'].' AND TxnID='.$txnid;
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:".$_SERVER['HTTP_REFERER']);
		}
        
    break;
	
	case 'Lookup':
	
	    ?><title><?php  echo $title; ?></title>
		
                <br><br><h4><?php  echo $title;?></h4><br>
                <style>.hoverTable tr:hover {
                        background-color: transparent;
						tr.border_bottom td {
						  border-bottom:1pt solid black;
						}
</style><div>
					<table class="hoverTable">
					<tr><td></td><td align="right">Date of Request: <input <?php echo $printstyle;?> type='text' name='DateRequest' size=16 value="<?php echo date('Y-m-d' , strtotime($res['DateRequest']));?>" disabled><td></tr>
                    <tr><td></td><td align="right">Date of Payment: <input <?php echo $printstyle;?> type='text' name='DatePayment' size=16 value="<?php echo date('Y-m-d' , strtotime($res['DatePayment']));?>" disabled><td></tr>
                    <tr><td>Expense of Company/Branch: <input <?php echo $printstyle;?> type='text' name='Branch' size=11 value="<?php echo $res['Branch'];?>" disabled></td><td></td></tr>
					<tr><td>Payee: <input <?php echo $printstyle;?> type='text' name='Payee' size=30 value="<?php echo $res['Payee'];?>" disabled></td><td></td></tr>
					<tr><td>From Budget Of: <input <?php echo $printstyle;?> type='text' name='FromBudgetOf' size=30 value="<?php echo $res['FromBudgetOf'];?>" disabled></td><td></td></tr>
					<tr><td style="padding:15px"></td></tr>
					<tr class="border_bottom"><td>Particulars:<br><textarea type='text' name='Particulars' required=true rows="6" cols="55" disabled><?php echo $res['Particulars'];?></textarea></td><td valign="top" align="right">Amount: <input <?php echo $printstyle;?> type='text' name='Amount' size=20 value="<?php echo number_format($res['Amount'],2);?>" disabled></td></tr>
					<tr><td style="padding:10px;"></td></tr>
					<tr><td>Prepared By: <?php echo $res['PreparedBy'];?></td><td align="right"><?php if ($res['AcknowledgeByNo']<>0) {echo 'Acknowledged by Accounting: '.$res['AcknowledgeBy'];} else {echo '';}?></td></tr><tr><td style="padding:10px;"></td></tr>
					
					
			<?php
			if ($res['Approved']<>0){
				// echo '<tr><td>'.($res['Approved']==1?"Approved":"Denied") .' By: ' . $res['ApprovedBy'].($res['Approved2']<>0?($res['Approved2']==1?"/":"<td>Denied By: ").''.$res['Approved2By']:'').'</td><td>'.($res['CheckIssuedByNo']<>0?'Check Issued By: '.$res['CheckIssuedBy']:'').'</td></tr>
				echo '<tr><td>'.($res['Approved']==1?"Approved":"Denied") .' By: ' . $res['ApprovedBy'].($res['Approved2']==1?" / Noted By: ":(($res['Approved2']==2 AND $res['Approved2ByNo']==0)?'':'<td>Denied By: ')).$res['Approved2By'].'</td><td>'.($res['CheckIssuedByNo']<>0?'Check Issued By: '.$res['CheckIssuedBy']:'').'</td></tr>
				
				'.($res['ReceiptReceivedByNo']<>0?'<tr><td style="padding:30px;"></td><td>Receipt Received By: '.$res['ReceiptReceivedBy']:'').'</td></tr>';
				
				if ((!empty($res['ApprovedRemarks'])) OR (!empty($res['Approved2Remarks'])) OR (!empty($res['AcknowledgeRemarks'])) OR (!empty($res['AcknowledgeRemarks'])) OR (!empty($res['CheckIssuedRemarks'])) OR (!empty($res['ReceiptReceivedRemarks']))){
				
					echo '<tr><td style="color:blue;"><br/><b>Remark(s)</b></td><td style="padding:30px;"></td></tr>';
					echo (!empty($res['ApprovedRemarks']) ? '<tr><td><font color="maroon">Department Head:</font> '.$res['ApprovedRemarks'].'</td><td style="padding:15px;"></td></tr>':'');
					echo (!empty($res['Approved2Remarks']) ? '<tr><td><font color="maroon">Finance Dept Head:</font> '.$res['Approved2Remarks'].'</td><td style="padding:15px;"></td></tr>':'');
					echo (!empty($res['AcknowledgeRemarks']) ? '<tr><td><font color="maroon">Acknowledged:</font> '.$res['AcknowledgeRemarks'].'</td><td style="padding:15px;"></td></tr>':'');
					echo (!empty($res['CheckIssuedRemarks']) ? '<tr><td><font color="maroon">Check Issued:</font> '.$res['CheckIssuedRemarks'].'</td><td style="padding:15px;"></td></tr>':'');
					echo (!empty($res['ReceiptReceivedRemarks']) ? '<tr><td><font color="maroon">Receipt Received:</font> '.$res['ReceiptReceivedRemarks'].'</td><td style="padding:15px;"></td></tr>':'');
				}
				
			}
			//Requester
			if (allowedToOpen(5236,'1rtc')){
				if ($res['Approved']==0 AND $res['RequestCompleted']==0){
				echo '<tr><td><a href="checkpaymentrequest.php?w=RequestCompleted&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set_Request_As_Completed</a></td><td><a href="checkpaymentrequest.php?w=EditSpecifics&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Edit Request</a></td></tr>';}
			}
			if (!isset($_GET['Print'])){
				if ($res['RequestCompleted']==1){
					
					if ($res['Approved']==0 AND $res['Approved2ByNo']==0){
						if (allowedToOpen(100,'1rtc')){ //To approve by DeptHead
							echo '<tr><td align="left">';
							echo '<form action="checkpaymentrequest.php?w=Approve" method="POST"><br/>Remarks:<br/><textarea name="ApprovedRemarks" rows="2" cols="50" placeholder="Leave empty if no remarks.">'.$res['ApprovedRemarks'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnApprove" value="APPROVE"></div><div style="margin-left:30%;"><input type="submit" name="btnDeny" value="DENY"></div></div></form>';
							
							echo '</td><td valign="bottom" align="right"><a href="checkpaymentrequest.php?w=SetIncDeptHead&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set Incomplete?</a></td></tr>';
							
						}
					} else if (($res['Approved']==1 AND $res['Approved2ByNo']==0 AND $res['Recurring']==0)) {
						if (allowedToOpen(5240,'1rtc')){ //To finance head
							echo '<tr><td align="left">';
							echo '<form action="checkpaymentrequest.php?w=Approve2" method="POST"><br/>Remarks:<br/><textarea name="Approved2Remarks" rows="2" cols="50" placeholder="Leave empty if no remarks.">'.$res['Approved2Remarks'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnApprove2" value="APPROVE"></div><div style="margin-left:30%;"><input type="submit" name="btnDeny2" value="DENY"></div></div></form>';
							
							echo '</td><td valign="bottom" align="right"><a href="checkpaymentrequest.php?w=SetIncFinanceDeptHead&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set Incomplete?</a></td></tr>';
						}
					} else if ($res['Approved']==1 AND (($res['Approved2']==0 AND $res['Approved2ByNo']==0 AND $res['Recurring']==1) OR ($res['Approved2']==1 AND $res['Approved2ByNo']<>0 AND $res['Recurring']==0)) AND $res['Acknowledge']==0) {
						if (allowedToOpen(5237,'1rtc')){ //To Acknowledge by Acctg.
							echo '<tr><td>';
							
							echo '<form action="checkpaymentrequest.php?w=Ack" method="POST"><br/>Remarks:<br/><textarea name="AcknowledgeRemarks" rows="2" cols="50" placeholder="Leave empty if no remarks.">'.$res['AcknowledgeRemarks'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnAck" value="Acknowledge"></div></div></form>';
							
							echo '</td><td valign="bottom" align="right"><a href="checkpaymentrequest.php?w=SetIncAck&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set Incomplete?</a></td></tr>';
						}
					} else if ($res['Acknowledge']==1 AND $res['CheckIssued']==0) {
						if (allowedToOpen(5238,'1rtc')){ //To Set Done by Acctg.
							echo '<tr><td>';
							
							echo '<form action="checkpaymentrequest.php?w=SetAsDone" method="POST"><br/>Remarks:<br/><textarea name="CheckIssuedRemarks" rows="2" cols="50" placeholder="Leave empty if no remarks.">'.$res['CheckIssuedRemarks'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnCheckIssued" value="Check Has Been Issued"></div></div></form>';
							
							echo '</td><td valign="bottom" align="right"><a href="checkpaymentrequest.php?w=SetAsDoneInc&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set Incomplete?</a></td></tr>';
						}
					} else if ($res['CheckIssued']==1 AND $res['ReceiptReceived']==0) {
						if (allowedToOpen(5238,'1rtc')){ //receipt Received
							echo '<tr><td>';
							
							echo '<form action="checkpaymentrequest.php?w=ReceiptReceived" method="POST"><br/>Remarks:<br/><textarea name="ReceiptReceivedRemarks" rows="2" cols="50" placeholder="Leave empty if no remarks.">'.$res['ReceiptReceivedRemarks'].'</textarea><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="hidden" name="TxnID" value="'.$txnid.'"><br/><br/><div><div style="float:left;"><input type="submit" name="btnReceiptReceived" value="Receipt Received?"></div></div></form>';
							
							echo '</td><td valign="bottom" align="right"><a href="checkpaymentrequest.php?w=SetIncReceiptReceived&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$txnid.'">Set Incomplete?</a></td></tr>';
						}
					}
					// if ($res['Acknowledge']==1) { echo '<tr><td style="padding:10px;"></td></tr><tr><tr><td align="left"><a href="checkpaymentrequest.php?w=Print&TxnID='.$txnid.'">Print Preview</a></td>'; echo '<td align="right">';}
					
						if ($res['Acknowledge']==1) { echo '<tr><td style="padding:10px;"></td></tr><tr><tr><td align="left"><a href="checkpaymentrequest.php?w=Lookup&Print=1&TxnID='.$txnid.'">Print Preview</a></td>'; echo '<td align="right">';}
					
					echo '</td></tr>';
				}
			}
			echo '</table></div>';
			
	break;
	
	case 'RequestCompleted':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `approvals_4checkpayment` SET RequestCompleted=1 WHERE RequestedByNo='.$_SESSION['(ak0)'].' AND TxnID='.$txnid.''; 
	$stmt=$link->prepare($sql); $stmt->execute(); 
	header('Location:checkpaymentrequest.php');
	break;
	
	
	
	case 'Approve':
	
    if (allowedToOpen(100,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	
	if(isset($_POST['btnApprove'])){
		$sql='UPDATE `approvals_4checkpayment` SET Approved=1, ApprovedRemarks="'.$_POST['ApprovedRemarks'].'", ApprovedByNo='.$_SESSION['(ak0)'].', ApprovedTS=Now() WHERE TxnID='.$txnid.'';
	} else {
		$sql='UPDATE `approvals_4checkpayment` SET Approved=2, ApprovedByNo='.$_SESSION['(ak0)'].', ApprovedRemarks="'.$_POST['ApprovedRemarks'].'", ApprovedTS=Now(),Approved2=2,Acknowledge=2,CheckIssued=2,ReceiptReceived=2 WHERE TxnID='.$txnid.''; 
	}
	
    $stmt=$link->prepare($sql); $stmt->execute(); }
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;
	
	
	case 'Approve2':
	if (allowedToOpen(5240,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
    $sql='';
	
	if(isset($_POST['btnApprove2'])){
		$sql='UPDATE `approvals_4checkpayment` SET Approved2=1, Approved2Remarks="'.$_POST['Approved2Remarks'].'", Approved2ByNo='.$_SESSION['(ak0)'].', Approved2TS=Now() WHERE TxnID='.$txnid.'';
	}
	else {
		$sql='UPDATE `approvals_4checkpayment` SET Approved2=2, Approved2Remarks="'.$_POST['Approved2Remarks'].'", Approved2ByNo='.$_SESSION['(ak0)'].', Approved2TS=Now(),Acknowledge=2,CheckIssued=2,ReceiptReceived=2 WHERE TxnID='.$txnid.''; 
	}
	
    $stmt=$link->prepare($sql); $stmt->execute();}
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'Ack':
	if (allowedToOpen(5237,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `approvals_4checkpayment` SET Acknowledge=1, AcknowledgeRemarks="'.$_POST['AcknowledgeRemarks'].'", AcknowledgeByNo='.$_SESSION['(ak0)'].', AcknowledgeTS=Now() WHERE TxnID='.$txnid.''; 
        $stmt=$link->prepare($sql); $stmt->execute();}
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;	
	
	case 'SetAsDone':
        if (allowedToOpen(5238,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `approvals_4checkpayment` SET CheckIssued=1, CheckIssuedRemarks="'.$_POST['CheckIssuedRemarks'].'", CheckIssuedByNo='.$_SESSION['(ak0)'].', CheckIssuedTS=Now() WHERE TxnID='.$txnid.''; 
        $stmt=$link->prepare($sql); $stmt->execute();}
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'ReceiptReceived':
        if (allowedToOpen(5238,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `approvals_4checkpayment` SET ReceiptReceived=1, ReceiptReceivedRemarks="'.$_POST['ReceiptReceivedRemarks'].'", ReceiptReceivedByNo='.$_SESSION['(ak0)'].', ReceiptReceivedTS=Now() WHERE TxnID='.$txnid.''; 
        $stmt=$link->prepare($sql); $stmt->execute();}
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;
	
	
	case 'SetIncDeptHead':
        if (allowedToOpen(100,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `approvals_4checkpayment` SET RequestCompleted=0 WHERE TxnID='.$txnid.''; 
        $stmt=$link->prepare($sql); $stmt->execute();}
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'SetIncFinanceDeptHead':
        if (allowedToOpen(5240,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `approvals_4checkpayment` SET Approved=0,ApprovedByNo=0 WHERE TxnID='.$txnid.''; 
        $stmt=$link->prepare($sql); $stmt->execute();}
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'SetIncAck':
        if (allowedToOpen(5237,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	$sqlr='SELECT Recurring FROM `approvals_4checkpayment` WHERE TxnID='.$txnid;
	$stmtr=$link->query($sqlr); $rowr=$stmtr->fetch();
	
	
	if ($rowr['Recurring']==0){ $field='Approved2ByNo=0, Approved2'; }
	else { $field='ApprovedByNo=0, Approved'; }
	
	$sql='';
	$sql='UPDATE `approvals_4checkpayment` SET '.$field.'=0 WHERE TxnID='.$txnid.'';
	
        $stmt=$link->prepare($sql); $stmt->execute();}
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;
	
	
	case 'SetAsDoneInc':
        if (allowedToOpen(5238,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `approvals_4checkpayment` SET Acknowledge=0,AcknowledgeByNo=0 WHERE TxnID='.$txnid.''; 
        $stmt=$link->prepare($sql); $stmt->execute();}
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'SetIncReceiptReceived':
        if (allowedToOpen(5238,'1rtc')){
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	$sql='UPDATE `approvals_4checkpayment` SET CheckIssued=0,CheckIssuedByNo=0 WHERE TxnID='.$txnid.''; 
        $stmt=$link->prepare($sql); $stmt->execute();}
	header('Location:checkpaymentrequest.php?w=Lookup&TxnID='.$txnid);
	break;
	
}
$link=null; $stmt=null;
?>
</div> <!-- end section -->
