<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
        
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        $showbranches=false;
include_once('../switchboard/contents.php');

$which=!isset($_GET['w'])?'List':$_GET['w'];

if (!allowedToOpen(8262,'1rtc')) { echo 'No permission'; exit;}


include_once('../backendphp/layout/showencodedbybutton.php');

include('payrolllayout/setpayidsession.php');
$payrollid=(isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));
$title='Payroll Adjustments on Payroll ID '.$payrollid;

$sql='SELECT Posted FROM payroll_1paydates WHERE PayrollID='.$payrollid;
$stmt=$link->query($sql); $res0=$stmt->fetch(); 
$sql='SELECT SUM(Approved) AS Approval FROM payroll_26approval WHERE PayrollID='.$payrollid;
$stmt=$link->query($sql); $res1=$stmt->fetch(); 
$editok=(($res0['Posted']==0) AND ($res1['Approval']==0))?TRUE:FALSE;
//echo $editok;

$file='payrolladjust.php?w='; 

if (in_array($which, array('List','EditAttend'))){
    $zeroblank=array('SpecDays','LWPDays','RegDaysActual','LegalHrsOT','SpecHrsOT','RestHrsOT','PaidLegalDays','RegOTHrs','ExcessRestHrsOT');
    $sqlattend='';
    foreach ($zeroblank as $field) { $sqlattend.='IF(`'.$field.'`=0,"",`'.$field.'`) AS `'.$field.'`, ';}
    $sqlattend='SELECT a.*,'.$sqlattend.' CONCAT(e.FirstName, " ", e.SurName) AS FullName, IFNULL(Branch,"No payroll yet") AS Branch, e2.Nickname AS EncodedBy FROM payroll_50adjattendance a JOIN 1employees e ON e.IDNo=a.IDNo LEFT JOIN payroll_25payroll p ON p.PayrollID=a.AdjInPayrollID AND p.IDNo=a.IDNo LEFT JOIN 1branches b ON b.BranchNo=p.`RecordInBranchNo` JOIN 1employees e2 ON e2.IDNo=a.EncodedByNo ';
    $columnnamesattend=array('LackInPayrollID','IDNo','FullName','Branch','Remarks','SpecDays','LWPDays','RegDaysActual','LegalHrsOT','SpecHrsOT','RestHrsOT','PaidLegalDays','RegOTHrs','ExcessRestHrsOT');
}

if (in_array($which, array('List','EditPay'))){
    $zeroblank=array('Basic', 'DeM', 'TaxSh', 'OT');
    $sqlpay='';
    foreach ($zeroblank as $field) { $sqlpay.='IF(`'.$field.'`=0,"",`'.$field.'`) AS `'.$field.'`, ';}
    $sqlpay='SELECT LackInPayrollID,AdjInPayrollID, a.IDNo, ap.*, CONCAT(e.FirstName, " ", e.SurName) AS FullName, IFNULL(Branch,"No payroll yet") AS Branch, IF(SentToPayroll=1,"Paid","") AS `Paid?`, e2.Nickname AS EncodedBy FROM payroll_55adjpayroll ap JOIN payroll_50adjattendance a ON a.TxnID=ap.TxnID JOIN 1employees e ON e.IDNo=a.IDNo LEFT JOIN payroll_25payroll p ON p.PayrollID=a.AdjInPayrollID AND p.IDNo=a.IDNo LEFT JOIN 1branches b ON b.BranchNo=p.`RecordInBranchNo` JOIN 1employees e2 ON e2.IDNo=ap.EncodedByNo ';
    $columnnamespay=array('LackInPayrollID','AdjInPayrollID','IDNo','FullName','Branch','Basic', 'DeM', 'TaxSh', 'OT','Paid?');
}


if (in_array($which, array('AddAttend','EditAttend','EditAttendPr','DelAttend','ProcessPer','EditPay','EditPayPr','DelPay','AddToPayroll','UnsetPayment'))){
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
     IF ($editok!=TRUE) { echo 'Payroll '.$payrollid.' is POSTED.'; exit();}
     $txnid=intval($_GET['TxnID']);
}

if (in_array($which, array('AddAttend','EditAttend','EditAttendPr','DelAttend'))){
    $columnstoadd=array('LackInPayrollID','IDNo','Remarks','SpecDays','LWPDays','RegDaysActual','LegalHrsOT','SpecHrsOT','RestHrsOT','PaidLegalDays','RegOTHrs','ExcessRestHrsOT');
    if(empty($_POST['Remarks'])) { $columnstoadd= array_diff($columnstoadd, array('Remarks'));}
    $table='payroll_50adjattendance';
}

if (in_array($which, array('ProcessPer','EditPay','EditPayPr','DelPay'))){
    $columnstoadd=array('Basic', 'DeM', 'TaxSh', 'OT');
    $table='payroll_55adjpayroll';
}

switch ($which){

case 'List':
    ?>
<br><br><form method="POST" action="payrolladjust.php" enctype="multipart/form-data">
	       Adjustment to be Paid on Payroll ID<input type='text' name='payrollid' list='payperiods' value="<?php echo $payrollid; ?>"></input>
	       <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" /> 
               <input type="submit" name="lookup" value="Lookup"><br><br>
	    <?php
	    include_once('../generalinfo/lists.inc');
	    renderlist('payperiods');
	   // if (!isset($_SESSION['payrollidses'])){ goto nodata;  }
	    ?>
	    </form>
	    <?php

    if($editok){
       
    $columnnames=array(
                    array('field'=>'LackInPayrollID', 'caption'=>'<h4 style="color:lightgrey;">Please follow the format of Attendance Basis for Payroll</h4><br>Lacking in Payroll ID','type'=>'number min="1" max="'.($payrollid-1).'"','size'=>5,'required'=>true),
                    array('field'=>'IDNo', 'type'=>'text','size'=>10,'required'=>true,'list'=>'employeeid'),
                    array('field'=>'SpecDays', 'type'=>'number min="1" max="13"','size'=>5,'required'=>FALSE),
                    array('field'=>'LWPDays', 'type'=>'number min="1" max="13"','size'=>5,'required'=>FALSE),
                    array('field'=>'RegDaysActual', 'type'=>'text','size'=>5,'required'=>FALSE),
                    array('field'=>'PaidLegalDays', 'type'=>'text','size'=>5,'required'=>FALSE),
                    array('field'=>'LegalHrsOT', 'type'=>'text','size'=>5,'required'=>FALSE),
                    array('field'=>'SpecHrsOT', 'type'=>'text','size'=>5,'required'=>FALSE),
                    array('field'=>'RestHrsOT', 'type'=>'text','size'=>5,'required'=>FALSE),
                    array('field'=>'RegOTHrs', 'type'=>'text','size'=>5,'required'=>FALSE),
                    array('field'=>'ExcessRestHrsOT', 'type'=>'text','size'=>5,'required'=>FALSE),
                    array('field'=>'Remarks', 'caption'=>'Remarks regarding attendance', 'type'=>'text','size'=>50, 'required'=>false)
        
        );
                    
    
    $action=$file.'AddAttend';
    $liststoshow=array('employeeid'); $fieldsinrow=7; $fieldsetwidth='65%';
    $editprocess=$file.'EditAttend&action_token='.html_escape($_SESSION['action_token']).'&TxnID='; $editprocesslabel='Edit';
    $delprocess=$file.'DelAttend&TxnID=';
    $editprocess3=$file.'ProcessPer&action_token='.html_escape($_SESSION['action_token']).'&TxnID='; $editprocesslabel3='Process';
    include('../backendphp/layout/inputmainform.php');

}
    
    $sql=$sqlattend .' WHERE a.AdjInPayrollID='.$payrollid;    
    $columnnames=$columnnamesattend;
    $title=''; $subtitle='Adjustment Attendance Basis';    
    if ($showenc==1) { array_push($columnnames,'EncodedBy','TimeStamp'); } 
    include('../backendphp/layout/displayastablenosort.php');
    
    if($editok){
        $editprocess=$file.'EditPay&action_token='.html_escape($_SESSION['action_token']).'&TxnID='; $editprocesslabel='Edit';
        $delprocess=$file.'DelPay&TxnID=';
        $editprocess3=$file.'AddToPayroll&action_token='.html_escape($_SESSION['action_token']).'&TxnID='; $editprocesslabel3='Add To Payroll';
        $editprocess4=$file.'UnsetPayment&action_token='.html_escape($_SESSION['action_token']).'&TxnID='; $editprocesslabel4='Unset Payment';
    }
       
    $sql=$sqlpay .' WHERE a.AdjInPayrollID='.$payrollid;    
    $columnnames=$columnnamespay;
    $title=''; $subtitle='Adjustment in Payroll';    
    include('../backendphp/layout/displayastablenosort.php');
    
    break;
    
case 'AddAttend': 
    $sql='';
    foreach ($columnstoadd as $col){
        $sql.=$col.'=\''.addslashes(empty($_POST[$col])?0:$_POST[$col]).'\',';
    }
    $sql='INSERT INTO `'.$table.'` SET AdjInPayrollID='.$payrollid.', '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=now() ';
      echo $sql;
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$file.'List');
    
break;

case 'EditPay':
case 'EditAttend':
    $sql=($which=='EditPay'?$sqlpay:$sqlattend).' WHERE a.TxnID='.$txnid;
    $columnnames=($which=='EditPay'?$columnnamespay:$columnnamesattend); $columnstoedit=array_diff($columnstoadd, array('IDNo','AdjInPayrollID'));
    $editprocess=$file.($which=='EditPay'?'EditPayPr':'EditAttendPr').'&TxnID='.$txnid; 
    include('../backendphp/layout/editspecificsforlists.php');
    break;

case 'EditPayPr':
case 'EditAttendPr': 
    $columnstoadd=array_diff($columnstoadd, array('IDNo','AdjInPayrollID'));
    $sql='';
    foreach ($columnstoadd as $col){
        $sql.=$col.'=\''.addslashes(empty($_POST[$col])?0:$_POST[$col]).'\',';
    }
    $sql='UPDATE `'.$table.'` SET '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW() WHERE TxnID='.$txnid;
    //  echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$file.'List');
    
break;

case 'ProcessPer':
    $sql0='CREATE TEMPORARY TABLE payadj AS
        SELECT a.TxnID, (RegDaysActual+PaidLegalDays+SpecDays+LWPDays) AS DaysAdj,
TRUNCATE(if(r.LatestDorM=0,LatestBasicRate,LatestBasicRate/13.04)*(SELECT DaysAdj),2) AS `Basic`,
TRUNCATE(if(r.LatestDorM=0,LatestDeMinimisRate,LatestDeMinimisRate/13.04)*(SELECT DaysAdj),2) AS `DeM`,
TRUNCATE(if(r.LatestDorM=0,LatestTaxShield,LatestTaxShield/13.04)*(SELECT DaysAdj),2) AS `TaxSh`,
TRUNCATE(if(r.LatestDorM=0,LatestBasicRate/8,(LatestBasicRate/13.04/8))*((LegalHrsOT)+((SpecHrsOT+RestHrsOT)*1.3)+(RegOTHrs*1.25)+(ExcessRestHrsOT*1.3*1.3)),2) AS OT
FROM `payroll_50adjattendance` a JOIN `payroll_20latestrates` r ON a.IDNo=r.IDNo JOIN `1employees` e ON a.IDNo = e.IDNo WHERE (a.AdjInPayrollID='.$payrollid.' AND r.DirectOrAgency=0) AND a.TxnID='.$txnid;
    $stmt=$link->prepare($sql0); $stmt->execute();
    $sql='';
    foreach ($columnstoadd as $col){ $sql.=$col.',';}
    $sql='INSERT INTO `'.$table.'` (TxnID, '.$sql.' EncodedByNo, TimeStamp) SELECT TxnID, '.$sql.' \''.$_SESSION['(ak0)'].'\', NOW() FROM payadj'; echo $sql;
    		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$file.'List');
    break;

case 'AddToPayroll': 
    $sql='SELECT * FROM payroll_55adjpayroll ap JOIN payroll_50adjattendance a ON a.TxnID=ap.TxnID WHERE ap.SentToPayroll=0 AND ap.TxnID='.$txnid;
    $stmt=$link->query($sql); $res=$stmt->fetch(); 
    if($stmt->rowCount()==0){ echo '<h3>Adjustment has been recorded. Please unset.</h3>' ; exit();}
    
    $idno=$res['IDNo']; $payrollid=$res['AdjInPayrollID'];
    
    $sql='SELECT TxnID FROM payroll_25payroll WHERE IDNo='.$idno.' AND PayrollID='.$payrollid;
    $stmt=$link->query($sql);
    if($stmt->rowCount()==0){ echo '<h3>No target payroll record.</h3>' ; exit();}
    
    $columnstoadd=array('Basic','DeM','TaxSh','OT');
    $sql='';
    foreach ($columnstoadd as $col){
        $sql.=$col.'=('.$col.'+'.$res[$col].'),';
    }
    $sql='UPDATE payroll_25payroll SET '.$sql.' Remarks=CONCAT(IFNULL(CONCAT(Remarks,"; "),""),"With adjustment from payroll '.$res['LackInPayrollID'].'"), EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW() WHERE IDNo='.$idno.' AND PayrollID='.$payrollid; 
    $stmt=$link->prepare($sql); $stmt->execute();    
    
    header('Location:'.$file.'UpdateGovt&SetAs=1&TxnID='.$txnid.'&IDNo='.$idno.'&PayrollID='.$payrollid);
    break;
    
case 'UpdateGovt' :
    $idno=$_GET['IDNo']; $payrollid=$_GET['PayrollID']; $txnid=$_GET['TxnID'];
    // Update govt deductions
    if ($payrollid%2==0 AND $payrollid<=24){ //SSS
        $sql='CREATE TEMPORARY TABLE sssbasis AS '
        . 'SELECT EncodedByNo,TimeStamp,IDNo, SUM(Basic+OT-UndertimeBasic-AbsenceBasic)+(SELECT IFNULL(SUM(Basic+OT-UndertimeBasic-AbsenceBasic),0) FROM `payroll_25payroll` pp WHERE pp.IDNo=p.IDNo AND pp.PayrollID='.($payrollid-1) . ') AS Basis FROM `payroll_25payroll` p WHERE IDNo='.$idno.' AND PayrollID='.$payrollid ;
        $stmt=$link->prepare($sql); $stmt->execute(); 

        $sql='UPDATE `payroll_25payroll` p JOIN sssbasis ss ON p.IDNo=ss.IDNo JOIN `1employees` as e ON e.IDNo = p.IDNo '
        . ' SET `SSS-EE`=getContriEE(Basis,"sss"), `SSS-ER`=getContriEE(Basis,"sser") WHERE p.IDNo='.$idno.' AND p.PayrollID='.$payrollid; 
        $stmt=$link->prepare($sql); $stmt->execute();
    } else { //WTax
        $sql='UPDATE `payroll_25payroll` p JOIN `1employees` as e ON e.IDNo = p.IDNo SET WTax=CalcTax(p.IDNo,'.$payrollid.') WHERE p.IDNo='.$idno.' AND PayrollID='.$payrollid . '  and CalcTax(p.IDNo,'.$payrollid.')>0 AND e.Resigned=0;'; 
$stmt=$link->prepare($sql); $stmt->execute();
    }
    
    $sql='UPDATE payroll_55adjpayroll SET SentToPayroll='.($_GET['SetAs']).', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW() WHERE TxnID='.$txnid;
    $stmt=$link->prepare($sql); $stmt->execute();
    header('Location:'.$file.'List');
    break;
    
case 'UnsetPayment' :
    $sql='SELECT * FROM payroll_55adjpayroll ap JOIN payroll_50adjattendance a ON a.TxnID=ap.TxnID WHERE ap.SentToPayroll=1 AND ap.TxnID='.$txnid;
    $stmt=$link->query($sql); $res=$stmt->fetch(); 
    if($stmt->rowCount()==0){ echo '<h3>Adjustment has not been paid.</h3>' ; exit();}
    
    $idno=$res['IDNo']; $payrollid=$res['AdjInPayrollID'];
    
    $sql='SELECT TxnID FROM payroll_25payroll WHERE IDNo='.$idno.' AND PayrollID='.$payrollid;
    $stmt=$link->query($sql);
    if($stmt->rowCount()==0){ echo '<h3>There is no such record in payroll.</h3>' ; exit();}
    
    $columnstoadd=array('Basic','DeM','TaxSh','OT');
    $sql='';
    foreach ($columnstoadd as $col){
        $sql.=$col.'=('.$col.'-'.$res[$col].'),';
    }
    $sql='UPDATE payroll_25payroll SET '.$sql.' Remarks=REPLACE(Remarks,\'With adjustment from payroll '.$res['LackInPayrollID'].'\',""), EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=NOW() WHERE IDNo='.$idno.' AND PayrollID='.$payrollid;
    $stmt=$link->prepare($sql); $stmt->execute();
    
    header('Location:'.$file.'UpdateGovt&SetAs=0&TxnID='.$txnid.'&IDNo='.$idno.'&PayrollID='.$payrollid);
    break;
    
case 'DelAttend': 
    $sql='DELETE FROM `'.$table.'` WHERE TxnID='.$txnid; 
		$stmt=$link->prepare($sql); $stmt->execute();
                header('Location:'.$file.'List');
                break;
            
case 'DelPay':
    $sql='DELETE FROM `'.$table.'` WHERE SentToPayroll=0 AND TxnID='.$txnid; 
		$stmt=$link->prepare($sql); $stmt->execute();
                header('Location:'.$file.'List');
    
break;

}
nodata: