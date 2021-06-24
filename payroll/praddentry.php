<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
		include_once $path.'/acrossyrs/dbinit/userinit.php';
		$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
        // check if allowed 
        $allowed=array(7915,791,7912,7912,7914,6281,820,811); $allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
        
        
        $whichqry=$_GET['w'];
        switch ($whichqry){
        case 'Rates':
		if (!allowedToOpen(791,'1rtc')) {  echo 'No permission'; exit;}
	$sqlinsert='INSERT INTO `payroll_22rates` SET ';
        $sql='';
        $columnstoadd=array('IDNo', 'DateofChange', 'BasicRate', 'DeMinimisRate', 'TaxShield', 'SSS-EE', 'Philhealth-EE', 'WTax', 'Remarks', 'DailyORMonthly');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addentry.php?w=Rates&done=1");
        break;
    
        case 'ApproveRate':
	    if (!allowedToOpen(7912,'1rtc')) { echo 'No permission'; exit; }
            if (allowedToOpen(7913,'1rtc')) { $condition='';} 
            // else { $condition='  AND (if(DailyORMonthly=1,((BasicRate+ColaRate+AllowRate+DeMinimisRate)*2),(BasicRate*26.08)))<=50000 ';}
            else { $condition='  AND (if(DailyORMonthly=1,((BasicRate+TaxShield+DeMinimisRate)),(BasicRate*26.08)))<=50000 ';}
	    $sql='UPDATE `payroll_22rates` SET `ApprovedByNo`=\''.$_SESSION['(ak0)'].'\', `ApprovalTS` = NOW() WHERE `TxnId`='.$_GET['TxnID'].$condition;
	    $stmt=$link->prepare($sql);	$stmt->execute();
	    header("Location:".$_SERVER['HTTP_REFERER']);
	    break;
            
        case 'DeleteRate':
	    if (!allowedToOpen(7914,'1rtc')) { echo 'No permission'; exit; }
	    $sql='DELETE FROM `payroll_22rates` WHERE `TxnId`='.$_GET['TxnID'];
	    $stmt=$link->prepare($sql);	$stmt->execute();
	    header("Location:".$_SERVER['HTTP_REFERER']);
	    break;    
            
	case 'Adjust':
	if (!allowedToOpen(7915,'1rtc')) { echo 'No permission'; exit; }
	if (in_array($_POST['AdjustTypeNo'],array(30,31,32,33))){
		echo 'Error. should be added on Loans module.'; exit();
	}
        $stmt0=$link->query('SELECT IF(a.`DefaultBranchAssignNo` IN (SELECT BranchNo FROM `1branches` WHERE CompanyNo=e.RCompanyNo),a.`DefaultBranchAssignNo`,(SELECT BranchNo FROM `1branches` WHERE PseudoBranch=1 AND BranchNo<>95 AND CompanyNo=e.RCompanyNo)) AS `BranchNo` FROM `attend_1defaultbranchassign` a JOIN `1employees` e ON e.IDNo=a.IDNo WHERE a.IDNo='.$_POST['IDNo']);   
        $res0=$stmt0->fetch();
	$sqlinsert='INSERT INTO `payroll_21scheduledpaydayadjustments` SET ';
        $sql='';
        $columnstoadd=array('PayrollID', 'IDNo', 'AdjustTypeNo', 'AdjustAmt','Remarks');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo='.$res0['BranchNo'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\';'; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addentry.php?w=Adjust&done=1");
        break;
        
         case 'UpdateSLBalDecCutoff':
            if (!allowedToOpen(6281,'1rtc')) { echo 'No permission'; exit; }
            
            $sql0='UPDATE `1employees` e JOIN `'.$lastyr.'_1rtc`.`attend_61silbal` a ON e.IDNo=a.IDNo 
        JOIN `attend_30currentpositions` cp ON e.IDNo=cp.IDNo JOIN attend_1positions p ON p.PositionID=cp.PositionID 
        JOIN `attend_howlongwithus` h ON e.IDNo=h.IDNo 
        SET e.SLBalDecCutoff=IFNULL(IF(a.SLBal>5,5,a.SLBal),0), e.PaidSLBenefit=0, e.VLfromPosition=IF(InYears>1,p.VLfromPosition,0), 
        e.VLfromTenure=IF(InYears>1,TRUNCATE(InYears,0)-1,0);'; 
            if($_SESSION['(ak0)']==1002){ echo 'Run sql if this does not work on php:<br><br> '.$sql0.'<br><br>';}
            $stmt=$link->prepare($sql0); $stmt->execute();
            header("Location:".$_SERVER['HTTP_REFERER']);
        break;
        case 'LeaveConversion':
            if (!allowedToOpen(6281,'1rtc')) { echo 'No permission'; exit; }
            
            $sql='INSERT INTO `payroll_21paydayadjustments` (`PayrollID`, `IDNo`,`AdjustTypeNo`,`AdjustAmt`,`EncodedByNo`,`TimeStamp`,`BranchNo`)
Select 1 AS PayrollID, e.`IDNo`, 25 as `AdjustTypeNo`, TRUNCATE((d.TotalDaily*e.SLBalDecCutoff),2) as `AdjustAmt`, \''.$_SESSION['(ak0)'] . '\' as `EncodedByNo`, Now(),BranchNo
FROM payroll_21dailyandmonthly d JOIN `1employees` e on e.`IDNo`=d.`IDNo` WHERE SLBalDecCutoff>0 AND Resigned=0 AND e.DirectOrAgency=0 AND 
(SELECT Posted from `payroll_1paydates` WHERE PayrollID=1)=0;';
            if($_SESSION['(ak0)']==1002){ echo 'Run sql if this does not work on php:<br><br> '.$sql.'<br><br>';}
            $stmt=$link->prepare($sql);	$stmt->execute();
            $sql='UPDATE `1employees` SET `PaidSLBenefit`=SLBalDecCutoff WHERE SLBalDecCutoff>0 AND Resigned=0 AND DirectOrAgency=0;'; $stmt=$link->prepare($sql);	$stmt->execute();
            if($_SESSION['(ak0)']==1002){ echo 'Run sql if this does not work on php:<br><br> '.$sql.'<br><br>';}
	header("Location:".$_SERVER['HTTP_REFERER']);
    
    case 'AdjPerPayID':
	if (!allowedToOpen(7915,'1rtc')) { echo 'No permission'; exit; }
      $sql='Select Posted from payroll_1paydates where PayrollID='.$_POST['PayrollID'];
      $stmt=$link->query($sql);
      $result=$stmt->fetch();
      if ($result['Posted']==0){    
	$sqlinsert='INSERT INTO `payroll_21paydayadjustments` SET ';
        $sql='';
        $columnstoadd=array('PayrollID', 'IDNo', 'AdjustTypeNo', 'AdjustAmt','Remarks');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['BranchNo']),'BranchNo');
	
	$sql=$sqlinsert.$sql.' BranchNo='.$branchno.', EncodedByNo=\''.$_SESSION['(ak0)'].'\';'; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addentry.php?w=AdjPerPayID&done=1");
      }
	header("Location:addentry.php?w=AdjPerPayID&done=0");
        break;
      
     
      

case 'AddBonus':
	if (!allowedToOpen(811,'1rtc')) { echo 'No permission'; exit;}
	$sql='';
        $columnstoadd=array('PayrollID', 'IDNo', 'AdjustTypeNo', 'AdjustAmt', 'Remarks');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql='INSERT INTO `payroll_21plannedbonuses` SET '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\';'; 
	//echo $sql;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addentry.php?w=Bonuses&done=1");
	       break;
	
        
        }
 $stmt=null;
?>