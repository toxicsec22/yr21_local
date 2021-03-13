<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(5504,5505,5507);$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { header("Location:".$_SERVER['HTTP_REFERER']."?denied=true"); }
allowed:
// end of check

 $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;    
 

$txnid=substr($_REQUEST['PDCID'],2); 

switch(substr($_REQUEST['PDCID'],0,2)){
    case '1-': $pdctable='acctg_3undepositedpdcfromlastperiod'; $pdc='UndepPDCId'; $pdcdateofcheck='DateofPDC'; $checkfields='PDCBank,PDCNo,'; break;
    case '0-': $pdctable='acctg_2collectmain';  $pdc='TxnID'; $pdcdateofcheck='DateofCheck'; $checkfields='CheckBank AS PDCBank,CheckNo AS PDCNo,'; break;
}

$which=!isset($_REQUEST['w'])?'List':$_REQUEST['w'];

switch ($which){
   
case 'Edit':
    $sql0='Select PDCID FROM `acctg_2provcollectsubpdcremarks` WHERE PDCID LIKE \''.$_REQUEST['PDCID'].'\'';
    $stmt0=$link->query($sql0); $stmt0->fetch();
if($stmt0->rowCount()==0) { $sqlinsert='INSERT INTO `acctg_2provcollectsubpdcremarks`(`PDCID`) VALUES (\''.$_REQUEST['PDCID'].'\');'; $stmt0=$link->prepare($sqlinsert); $stmt0->execute();}
    $txnid='PDCID'; $title='Edit PDC Remarks';
    $columnnames=array('PDCBank','PDCNo','DepositOnDate','PDCRemarks','AcctgBy','ARPDCRemarks','ARBy');
    if (allowedToOpen(5505,'1rtc')) { $columnstoedit=array('ARPDCRemarks');} else { $columnstoedit=array('PDCRemarks'); } ;
    if (allowedToOpen(5507,'1rtc')) { $columnstoedit[]='DepositOnDate';}
    $sql='Select pdcr.*,'.$checkfields.' DepositOnDate,IFNULL(PDCRemarks,"") AS AcctgRemarks,  IFNULL(ARPDCRemarks,"") AS ARRemarks , e.Nickname AS AcctgBy, e1.Nickname AS ARBy FROM `acctg_2provcollectsubpdcremarks` pdcr 
        JOIN `'.$pdctable.'` pdcm ON pdcm.`'.$pdc.'`=MID(pdcr.PDCID,3) LEFT JOIN `1employees` e ON e.IDNo=pdcr.EncodedByNo
LEFT JOIN `1employees` e1 ON e1.IDNo=pdcr.AREncodedByNo WHERE pdcr.PDCID LIKE \''.$_REQUEST['PDCID'].'\'';
// if ($_SESSION['(ak0)']==1002) {echo $sql;print_r ($columnstoedit);}
$editprocess='pdcremarks.php?w=PrEdit&PDCID='; $editprocesslabel='Enter'; 
include_once('../backendphp/layout/displayastableeditcells.php'); echo '<br><br> Originally:<br>';
unset($editprocess,$editprocesslabel);
$columnnames=array('PDCBank','PDCNo','DepositOnDate','AcctgRemarks','AcctgBy','ARRemarks','ARBy');
include_once('../backendphp/layout/displayastableonlynoheaders.php');
break;
case 'PrEdit':
    $txnid=$_REQUEST['PDCID'];
    $sql0='Select * FROM `acctg_2provcollectsubpdcremarks` WHERE PDCID LIKE \''.$txnid.'\''; $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
    
    if (allowedToOpen(5507,'1rtc')) { 
        $sql='UPDATE `'.$pdctable.'` SET `DepositOnDate`=\''.($_POST['DepositOnDate']).'\' WHERE `'.$pdc.'`=MID(\''.$txnid.'\',3)';
       //  if ($_SESSION['(ak0)']==1002) {echo $sql; break;}
        $stmt=$link->prepare($sql); $stmt->execute();
    } 
    
    if ($stmt0->rowCount()==0){
         if (allowedToOpen(5505,'1rtc')) {
            $sql='INSERT INTO `acctg_2provcollectsubpdcremarks` SET `PDCID`=\''.$txnid.'\', '.$editdepdate.' `ARPDCRemarks`=\''.stripslashes($_POST['ARPDCRemarks']).'\',`ARTimeStamp`=Now(),`AREncodedByNo`='.$_SESSION['(ak0)'];
         } else {
            $sql='INSERT INTO `acctg_2provcollectsubpdcremarks` SET `PDCID`=\''.$txnid.'\', '.$editdepdate.' `PDCRemarks`=\''.stripslashes($_POST['PDCRemarks']).'\',`TimeStamp`=Now(),`EncodedByNo`='.$_SESSION['(ak0)']; 
         }
     } else {       
         if (allowedToOpen(5505,'1rtc')) {   
            $sql='UPDATE `acctg_2provcollectsubpdcremarks` SET ARPDCRemarks=concat(\''.stripslashes($_POST['ARPDCRemarks']).'\'," ",\''.stripslashes($res0['ARPDCRemarks']).'\'),`ARTimeStamp`=Now(),`AREncodedByNo`='.$_SESSION['(ak0)'].' WHERE PDCID LIKE \''.$txnid.'\'';
         } else {
            $sql='UPDATE `acctg_2provcollectsubpdcremarks` SET PDCRemarks=concat(\''.stripslashes($res0['PDCRemarks']).'\'," ",\''.stripslashes($_POST['PDCRemarks']).'\'),`TimeStamp`=Now(),`EncodedByNo`='.$_SESSION['(ak0)'].' WHERE PDCID LIKE \''.$txnid.'\'';
         }
    } //if ($_SESSION['(ak0)']==1002) {echo $sql; break;}
    $stmt=$link->prepare($sql); $stmt->execute();
    header("Location:lookupacctgAR.php?w=UndepositedPDCs");
    break;


case 'OfcAccept': 
    if (!allowedToOpen(5505,'1rtc')) { echo 'No permission'; exit; }
	$sqlupdate='UPDATE `'.$pdctable.'` m Set AtOffice=1, OfcAcceptedByNo='.$_SESSION['(ak0)'].', OfcAcceptTS=Now() WHERE `'.$pdc.'` LIKE \''.$txnid.'\'';
        // if($_SESSION['(ak0)']==1002){ echo $sqlupdate;break;}
	$stmt=$link->prepare($sqlupdate); 	$stmt->execute();
        $sql1='UPDATE `acctg_undepositedclientpdcs` SET AtOffice=1, OfcAcceptedByNo='.$_SESSION['(ak0)'].', OfcAcceptTS=Now() WHERE `PDCID` LIKE \''.$_REQUEST['PDCID'].'\''; 
    $stmt1=$link->prepare($sql1); $stmt1->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;

case 'AcctgAccept': 
    if (!allowedToOpen(5504,'1rtc')) { echo 'No permission'; exit; }
	$sqlupdate='UPDATE `'.$pdctable.'` Set AcctgAcceptedByNo='.$_SESSION['(ak0)'].', AcctgAcceptTS=Now() WHERE `'.$pdc.'` LIKE \''.$txnid.'\'';
        //if($_SESSION['(ak0)']==1002){ echo $sqlupdate;break;}
        $stmt=$link->prepare($sqlupdate);	$stmt->execute();
        $sql1='UPDATE `acctg_undepositedclientpdcs` SET AcctgAcceptedByNo='.$_SESSION['(ak0)'].', AcctgAcceptTS=Now() WHERE `PDCID` LIKE \''.$_REQUEST['PDCID'].'\''; 
    $stmt1=$link->prepare($sql1); $stmt1->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;
    
    
case 'SendToBank':
    if (!allowedToOpen(5505,'1rtc')) { echo 'No permission'; exit;}
    
    $sql='UPDATE `'.$pdctable.'` SET SendToBank=IF((SendToBank=1 OR DATEDIFF('.$pdcdateofcheck.',CURDATE())<=7),0,1),DepositOnDate='.$pdcdateofcheck.', SendToBankByNo='.$_SESSION['(ak0)'].' WHERE `'.$pdc.'` LIKE \''.$txnid.'\''; 
   // if ($_SESSION['(ak0)']==1002) {echo $sql; break;}
    $stmt=$link->prepare($sql); $stmt->execute(); 
    if ($stmt->rowCount()>0){
    $sql1='UPDATE `acctg_undepositedclientpdcs` SET SendToBank=IF((SendToBank=1 OR DATEDIFF(DateofPDC,CURDATE())<=7),0,1), SendToBankByNo='.$_SESSION['(ak0)'].' WHERE `PDCID` LIKE \''.$_REQUEST['PDCID'].'\''; 
    $stmt1=$link->prepare($sql1); $stmt1->execute();} 
    header("Location:lookupacctgAR.php?w=UndepositedPDCs");
    break;
    
case 'WithBank':
    if (!allowedToOpen(5504,'1rtc')) { echo 'No permission'; exit;}
    //REMOVED THIS CONDITION  AND `SendToBank`<>0
    $sql='UPDATE `'.$pdctable.'` SET WithBank=IF(WithBank=0,1,0), WithBankByNo='.$_SESSION['(ak0)'].', WithBankTS=Now() WHERE `'.$pdc.'` LIKE \''.$txnid.'\' '; 
    $stmt=$link->prepare($sql); $stmt->execute(); 
    if ($stmt->rowCount()>0){
    $sql1='UPDATE `acctg_undepositedclientpdcs` SET WithBank=IF(WithBank=0,1,0), WithBankByNo='.$_SESSION['(ak0)'].', WithBankTS=Now() WHERE `PDCID` LIKE \''.$_REQUEST['PDCID'].'\''; 
    $stmt1=$link->prepare($sql1); $stmt1->execute();} 
    header("Location:lookupacctgAR.php?w=UndepositedPDCs");
    break;
    
}
 $link=null; $stmt=null; 
?>