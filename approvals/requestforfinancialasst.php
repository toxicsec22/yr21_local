<?php 
if(session_id()==''){
	session_start();
} 
$IDNo = $_SESSION['(ak0)'];
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$allowed=array(5352,5353,5354,5355,5233,5356); $allow=0;
// 5353 all head  5354 HR  5355 Ma'am JYE only 5233 if for acctg permission
 foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
 if ($allow==0) { echo 'No permission'; exit;}
allowed:

$which=(!isset($_GET['w'])?'List':$_GET['w']);
$txnid=(!isset($_REQUEST['TxnID']))?'TxnID':$_REQUEST['TxnID'];

$showbranches=false; 
include_once('../switchboard/contents.php');

if (in_array($which,array('List','Lookup','MakeCheckVoucher','HRorFinalAction'))){
    $Select = 'SELECT IF(IDNo='.$IDNo.',LatestSupervisorIDNo,IDNo) AS DeptHead,RCompanyNo,BranchNo FROM attend_30currentpositions WHERE PositionID=(SELECT deptheadpositionid from attend_30currentpositions where IDNo='.$IDNo.');' ;
	$stmt=$link->query($Select); 
	$res2=$stmt->fetch();
	$varHeadID = $res2['DeptHead'];
	$RCompanyNo = $res2['RCompanyNo'];

	$sql1='SELECT deptStatus,RequestTS,cpr.*, DATE_FORMAT(RequestTS,"%M %d, %Y") AS `DateRequested`,format(AmountSpent,0) as AmountSpent,format(FinalAmount,0) as FinalAmount,format(hrAmount,0) as hrAmount,
    (CASE WHEN FinalApproval = 1 THEN "Approved"
		WHEN FinalApproval = 2 THEN "Denied"
		
		ELSE "Pending"
		END) 
		 AS FinalStatus, 
		 (CASE
		WHEN deptStatus = 1 THEN "Approved"
		WHEN deptStatus = 2 THEN "Denied"
		ELSE "Pending"
		END) 
		 AS DeptStatus,
		 (CASE
		WHEN HRStatus = 1 THEN "Approved"
		WHEN HRStatus = 2 THEN "Denied"
		ELSE "Pending"
		END) 
		
		 AS HRStatus,
		  (CASE
		WHEN vchStatus = 1 THEN "ReleasedCheck"
		WHEN vchStatus = 2 THEN "Denied"
		ELSE "Pending"
		END) 
		
		 AS AcctgStatus,
		  CONCAT(e.Nickname, " ", e.Surname) AS RequestedBy, CONCAT(e1.Nickname, " ", e1.Surname) AS DeptHead, 
                  CONCAT(e2.Nickname, " ", e2.Surname) AS HRHead, CONCAT(e3.Nickname, " ", e3.Surname) AS ApprovedBy 
                  FROM approvals_4financial cpr  LEFT JOIN `1employees` e ON cpr.RequestByNo=e.IDNo 
                  LEFT JOIN `1employees` e1 ON cpr.DeptApproveByID=e1.IDNo LEFT JOIN `1employees` e2 ON cpr.HRByID=e2.IDNo LEFT JOIN `1employees` e3 ON cpr.Final_ID=e3.IDNo ';
	
}

?>
<?php 

switch ($which) {	
	case 'List':
	$formdesc='</i></br><form action="#" method="POST">Filter By:<select name="filterby" id ="filter">
		<option value="0">Unfinished Requests</option>
		<option value="1">Denied Requests</option>
		<option value="2">Pending Approvals - DeptHead</option>
		<option value="3">Pending HR Approvals</option>
		<option value="4">Pending Approvals - JYE</option>
		<option value="5">Approved Requests</option>
		<option value="6">All Requests</option></select>
		<input type="submit" name="btnSubmit" value="Filter"></form><i>'; 
		
		$maincondi='where '.$IDNo.' IN (DeptApproveByID,RequestByNo,Final_ID,HRByID)';
		if(!isset($_POST['btnSubmit'])){
			$_POST['filterby']='';
			$sql = '';
			
			$sql2 = $maincondi.' AND (DeptStatus=0 OR HRStatus=0 OR FinalApproval=0 OR vchStatus=0)';
		
			$sql .= $sql1 .=$sql2;
			$_POST['filterby']=0;
			}
		else {
			
		
			if ($_POST['filterby']==0){
				
			 $subtitle1 = ' (Unfinished Requests)'; 
			 $filter = $maincondi.' AND FinalApproval=0';
			}
			else if ($_POST['filterby']==1){
			
			$subtitle1 = ' (Denied Requests)';
			 $filter = $maincondi.' AND FinalApproval = 2 GROUP BY TxnID';
			}
			else if ($_POST['filterby']==2){
				
			$subtitle1 = ' (Pending Approvals - DeptHead)'; 
			$filter = $maincondi.' AND deptStatus=0 AND (HRStatus=0)';
			}else if ($_POST['filterby']==3){
				
				$subtitle1 = ' (Pending Approvals - HR)'; 
				$filter = $maincondi.' AND deptStatus=1 AND HRStatus=0';
			}
			else if ($_POST['filterby']==4){
				
				$subtitle1 = ' (Pending Approvals - JYE)'; 
				$filter = $maincondi.' AND (deptStatus=1 AND HRStatus=1 AND FinalApproval=0) or (deptStatus=2 and HRStatus=0 and FinalApproval=0)';
			}
			else if ($_POST['filterby']==5){
				$subtitle1 = ' (Approved Requests)'; 
				$filter =  $maincondi.' AND (FinalApproval=1) GROUP BY TxnID';
				$defaultfilter='';
			}
			else if ($_POST['filterby']==6){
				
				$subtitle1 = ' (All Requests)'; $filter = $maincondi;
			}
			
		}


		if(isset($_POST['btnSubmit'])){
			$defaultfilter='';
			$sql1 .= $filter;
			
		}

			$sql = $sql1;
			if(allowedToOpen(5233,'1rtc')){
			$sql= $sql1.' OR (vchStatus=0 and FinalApproval=1)';	
			}
			$stmt=$link->query($sql); 
		 	$res=$stmt->fetch();
			
	if (allowedToOpen(5356,'1rtc')){
            $editprocess='requestforfinancialasst.php?w=Lookup&TxnID=';
			$editprocesslabel='Lookup';
	}
			// echo $sql; exit();

    $columnnames = array('Reason', 'RequestedBy','AmountSpent','RequestTS','DeptStatus','HRStatus','FinalStatus','AcctgStatus','FinalAmount');
	$title='Financial Assistance';
	$columnnames=$columnnames;
	// echo $sql; exit();
		include('../backendphp/layout/displayastable.php'); 
		break;

	case 'Lookup':
	$sql = $sql1.'Where TxnID = '.$_GET['TxnID'].'';
// echo $sql; 
	$stmt=$link->query($sql);
	$res=$stmt->fetch(); 
 ?>
    <div style='background-color: #ffffe6;
                        width: 1100px;
                        border: 2px solid grey;
                        padding: 25px;
                        margin: 25px;'>

     Requested by: <b><?php echo $res['RequestedBy']; ?></b> 
            <?php echo str_repeat('&nbsp;', 15); ?>
            Date Requested: <b><?php  echo $res['DateRequested'];?></b><br>
            Reason: <b><?php echo $res['Reason']; ?></b> &nbsp; &nbsp; &nbsp; 

            Amount Spent: <b><?php echo $res['AmountSpent'];?> </b> 
            <br><br>Remarks by Dept Head (<?php echo $res['DeptHead'];?>): <b><?php echo $res['RemarksDept'];?></b><br>
            	
            <?php 
             echo 'Hr Remarks ('.$res['HRHead'].') :<b>'.$res['HRRemarks'].'</b><br>';
             echo 'Final Remarks ('.$res['ApprovedBy'].') :<b>'.$res['FinalRemark'].'</b><br><br>';

if(allowedToOpen(5233,'1rtc') && $res['FinalStatus'] == 'Approved' ){
	
    if( $res['vchStatus'] == 0 && $res['FinalStatus'] == 'Approved'){
        echo 'Amount to Give : <b> '.$res['FinalAmount'].'</b>';
        echo '<br><br>Approved by: <b>'.$res['ApprovedBy'].'</b><br><br><br><form method="post" action="requestforfinancialasst.php?w=MakeCheckVoucher&TxnID='.$_GET['TxnID'].'">
		<input type="hidden" name="action_token" value="'. $_SESSION['action_token'].'">
		<input type = "submit" name="decision" value ="Make Check Voucher"/> </b>';
		echo'<input type="hidden" name="action_token" value="'. $_SESSION['action_token'].'">
		 </form></div>';
    }else{
        echo 'HR Recommends : <b> '.$res['hrAmount'].'</b><br>';
        echo 'Amount Given : <b> '.$res['FinalAmount'].'</b>
        <br><br>Approved by: <b>'.$res['ApprovedBy'].'</b><br>
        <br>Status: <b>Released Check </b><br><br><br>
        <a href = "requestforfinancialasst.php?w=List">Back </a></b>';
    }
}

if(allowedToOpen(5353, '1rtc') and $res['FinalStatus']=='Pending' and $res['DeptApproveByID']==$IDNo and $res['HRStatus']=='Pending'){
		
	if ($res['deptStatus']==1 and $res['requesterStatus']<>0 and $res['HRStatus']=='0'){
		echo 'Dept Head ('.$res['DeptHead'].') Remarks:';
		echo "<strong>" .$res['RemarksDept']. "</strong>";
		echo '<br><br><a href = "requestforfinancialasst.php?w=List">Back</a>';
	}else if ($res['deptStatus']==0 and $res['requesterStatus']<>0 and $res['HRStatus']==0){
		echo 'Dept Head Remarks:<br>
		<form method="post" action="requestforfinancialasst.php?w=DeptHeadApproval&TxnID='.$_GET['TxnID'].'">
			<textarea type="text" name="DeptRemarks" rows="6" cols="65"></textarea><br><br><br>
		<input type="hidden" name="action_token" value="'. $_SESSION['action_token'].'">
        <input type="submit" name="decision"  value="Approve" />
		'.str_repeat('&nbsp;',40).'
		<input type="submit" name="decision"  value="Disapprove"/> 
		'.str_repeat('&nbsp;',40).'
        <input type="submit" name="decision"  value="Set As Incomplete" OnClick="return confirm(\'Are you SURE you want to set as INcomplete?\');" /><br>
        </form></div>';
	}
}


 if((allowedToOpen(5354,'1rtc') and $res['DeptStatus'] == 'Approved') or (allowedToOpen(5355,'1rtc') and $res['HRStatus']=='Approved')){
 
	if((allowedToOpen(5354,'1rtc') and $res['HRStatus']=='Pending') or (allowedToOpen(5355,'1rtc') and $res['FinalStatus']=='Pending')){
			?>
<?php if($res['HRStatus']=='Pending') { ?>
    <b><font color="red">HR to recommend amount.</font></b><br><br>
	<?php } else { ?>
	 Hr Amount: <b><?php echo $res['hrAmount']; ?></b><br><br>
	<?php
	}
	echo'<form method="post" action="requestforfinancialasst.php?w=HRorFinalAction&TxnID='.$_GET['TxnID'].'">
	<input type="hidden" name="action_token" value="'. $_SESSION['action_token'].'">
	Amount to Give: <input type="number" name = "newAmount"  min="0" step="any" value= "" required="true">';
	echo '<br><br>Remarks:  <br><br>
	
 	<textarea type="text" id = "txt_reg" name="Remarks" rows="6" cols="65" ></textarea><br> <br>';
 	echo '<input type="submit" name="decision"  value="Approve" />
	'.str_repeat('&nbsp;',40).'
	<input type="submit" name="decision"  value="Disapprove"/> 
	'.str_repeat('&nbsp;',40).'
        <input type="submit" name="decision" value="Set As Incomplete" OnClick="return confirm(\'Are you SURE you want to set as INcomplete?\');"/>';
        	
	echo '<br></form></div>';
	
}else if ((allowedToOpen(5354,'1rtc') and ($res['HRStatus']=='Approved' or $res['HRStatus']=='Denied'  or $res['deptStatus']<>1))){
	echo '<br><br><a href = "requestforfinancialasst.php?w=List">Back</a>';
}

}  
// echo $res['FinalAmount']; exit();            
break;

case'HRorFinalAction':
$sql = $sql1.'Where TxnID = '.$_GET['TxnID'].'';
$stmt=$link->query($sql);
$res=$stmt->fetch(); 
	
//HR ACTION

if(allowedToOpen(5354, '1rtc') and $res['FinalStatus']=='Pending' and $res['HRStatus']<>1 ){		
	
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    
    if($_POST['decision']=='Approve'){		
    $sql='UPDATE `approvals_4financial` SET HRStatus=1, HRRemarks = "'.$_POST['Remarks'].'", hrAmount = "'.$_POST['newAmount'].'" ,HRTS = Now(),HRByID = '.$IDNo.' WHERE TxnID='.$txnid.'';
     
     } 
    if($_POST['decision']=='Disapprove'){
    $sql='UPDATE `approvals_4financial` SET HRStatus=2, HRRemarks = "'.$_POST['Remarks'].'", hrAmount = "'.$_POST['newAmount'].'" ,HRTS = Now(),HRByID = '.$IDNo.' WHERE TxnID='.$txnid.'';  
    }

    if($_POST['decision']=='Set As Incomplete'){
    $sql='UPDATE `approvals_4financial` SET DeptStatus= 0, HRRemarks = "'.$_POST['Remarks'].'"  WHERE TxnID='.$txnid.'';
	}
	
	$stmt=$link->prepare($sql); $stmt->execute(); 
	
header('Location:requestforfinancialasst.php?w=Lookup&TxnID='.$txnid);	
}
//FINAL ACTION
if(allowedToOpen(5355, '1rtc') and $res['FinalStatus']=='Pending'){	
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
 
	if($_POST['decision']=='Approve'){
    $sql='UPDATE `approvals_4financial` SET FinalApproval=1, FinalRemark = "'.$_POST['Remarks'].'",  FinalAmount = '.$_POST['newAmount'].',Final_ID = "'.$IDNo.'", 	FinalTS = Now() WHERE TxnID='.$txnid.''; 
	} 
  
    if($_POST['decision']=='Disapprove'){
    $sql='UPDATE `approvals_4financial` SET FinalApproval=2, FinalRemark = "'.$_POST['Remarks'].'",Final_ID = "'.$IDNo.'", 	FinalTS = Now() WHERE TxnID='.$txnid.'';
    }
	
    if($_POST['decision']=='Set As Incomplete'){
    $sql='UPDATE `approvals_4financial` SET HRStatus = 0, FinalRemark = "'.$_POST['Remarks'].'" WHERE TxnID='.$txnid.'';
    }
	
	$stmt=$link->prepare($sql); $stmt->execute(); 
									
header('Location:requestforfinancialasst.php?w=List');	

}
break;

case'DeptHeadApproval':
$txnid=intval($_GET['TxnID']);
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
   
if($_POST['decision']=='Approve'){		
    $sql='UPDATE `approvals_4financial` SET DeptStatus=1,DeptApproveTS = Now(), RemarksDept = "'.$_POST['DeptRemarks'].'" WHERE TxnID='.$txnid.'';
} 
if($_POST['decision']=='Disapprove'){
    $sql='UPDATE `approvals_4financial` SET DeptStatus= 2,DeptApproveTS = Now(), RemarksDept = "'.$_POST['DeptRemarks'].'" WHERE TxnID='.$txnid.'';
}
if($_POST['decision']=='Set As Incomplete'){
        $sql='UPDATE `approvals_4financial` SET requesterStatus = 0, DeptApproveTS = Now(),RemarksDept = "'.$_POST['DeptRemarks'].'" WHERE TxnID= "'.$txnid.'"';      
}
		$stmt=$link->prepare($sql); $stmt->execute();
header('Location:requestforfinancialasst.php?w=Lookup&TxnID='.$txnid);
break;

case'MakeCheckVoucher':
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';

	$sql = $sql1.'Where TxnID = '.$_GET['TxnID'].'';
// echo $sql; exit();
	$stmt=$link->query($sql);
	$res=$stmt->fetch(); 	

	$getMonth = date('m'); 
	$select2=' SELECT   AccountID, a1.DefaultBranchAssignNo as BranchNo, CONCAT(e.FirstName, " ", e.Surname) AS FullName FROM 1employees e JOIN banktxns_1maintaining bm ON e.RCompanyNo=bm.RCompanyUse JOIN attend_1defaultbranchassign as a1 on e.IDNo= a1.IDNo  WHERE e.IDNo= '.$res['RequestByNo'].'';
				// echo $select2; exit();
	$stmt=$link->query($select2); $result1=$stmt->fetch(); 
				
	$AcctID = $result1['AccountID'];
	$FullName = $result1['FullName'];
	$BranchNo = $result1['BranchNo'];
						
 	$vchdate=strtotime($res['RequestTS']);
	include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
	$vchno=lastNum('CVNo','acctg_2cvmain',((date('Y',$vchdate))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',$vchdate)).',2)')+1;
			
	$sqlinsert='INSERT INTO acctg_2cvmain SET CVNo='.$vchno.', CheckNo=CONCAT(\'FA-'.$res['RequestByNo'].''.$getMonth.'\'), Date=\''.date('Y-m-d').'\', DateOfCheck=\''.date('Y-m-d').'\', PayeeNo='.$res['RequestByNo'].', Payee=\''.$FullName.'\', CreditAccountID='.$AcctID.','.' Remarks="Financial Assistance", TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'].', PostedByNo='.$_SESSION['(ak0)'].' ';
		 // echo $sqlinsert; exit();
 	$stmt=$link->prepare($sqlinsert); $stmt->execute();     
   
   	$sqlinsert2='Insert into acctg_2cvsub SET CVNo='.$vchno.', DebitAccountID=6412, Amount='.(!is_numeric($res['FinalAmount'])?str_replace(',', '',$res['FinalAmount']):$res['FinalAmount']).', BranchNo='.$BranchNo.', TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)']; 
    $stmt=$link->prepare($sqlinsert2); $stmt->execute();	

    $sql='UPDATE `approvals_4financial` SET vchStatus=1  WHERE TxnID='.$_REQUEST['TxnID'].''; 
    $stmt=$link->prepare($sql);  $stmt->execute(); 
				
header('Location:../acctg/formcv.php?w=CV&CVNo='.$vchno);
break;
}

?>
 <script type="text/javascript">
  document.getElementById('filter').value = "<?php echo $_POST['filterby']; ?>";
</script>
</body>
</html>