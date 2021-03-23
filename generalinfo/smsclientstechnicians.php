<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
if (!allowedToOpen(915,'1rtc')) { echo 'No permission'; exit;}
$which=!isset($_REQUEST['w'])?'List':($_REQUEST['w']);
if($which=='SendSMS' OR $which=='StopReceiveSMS'){
	$showbranches=true;
} else {
	$showbranches=false;
}
include_once('../switchboard/contents.php');



include_once $path.'/acrossyrs/commonfunctions/listoptions.php';



if (in_array($which,array('List','ListAll','EditSpecifics','SendSMS','AddNew','ListofConditions','SMSTestReceiver','StopReceiveSMS'))){
	include_once '../backendphp/layout/linkstyle.php';
	echo '<br><a id="link" href="smsclientstechnicians.php?w=List">List of Messages (Date Today)</a> ';
	echo '<a id="link" href="smsclientstechnicians.php?w=ListAll">List of All Messages</a> ';
	if (allowedToOpen(916,'1rtc')) {
		echo '<a id="link" href="smsclientstechnicians.php?w=AddNew">Add New Text Message</a> ';
		echo '<a id="link" href="smsclientstechnicians.php?w=SMSTestReceiver">SMS Test Receiver</a> ';
		echo '<a id="link" href="smsclientstechnicians.php?w=StopReceiveSMS">Clients SMS Subscription</a> ';
	}
	echo '<br><br>';
}
if (in_array($which,array('List','ListAll','EditSpecifics','SendSMS'))){
   $sql='SELECT a.*,IF(`To`=0,"Clients","Technicians") AS `To`,IF(Final=1,"Yes","No") AS `Final?`,REPLACE(a.Msg, "\r\n", "<br>") AS Msg,SMSID AS TxnID, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy FROM gen_info_2smsclientstechnicians a 
LEFT JOIN 1employees e ON e.IDNo=a.EncodedByNo ';
$columnnames=array('Msg','To','Final?','EncodedBy','TimeStamp');
   $columnstoadd=array('Msg','To','MsgFor');
}
if (in_array($which,array('SMSTestReceiver','EditSpecificsSMSTestReceiver'))){
   $sql='SELECT SID AS TxnID,MobileNo FROM gen_info_1smstestreceiver ';
$columnnameslist=array('MobileNo');
   $columnstoadd=array('MobileNo');
}


switch ($which){
case 'List':
if (!allowedToOpen(915,'1rtc')) { echo 'No permission'; exit;}
$title='List of Messages (Date Today)';    

$editprocess='smsclientstechnicians.php?w=SendSMS&TxnID=';
$editprocesslabel='Send SMS';
$sql.=' WHERE DATE(a.TimeStamp)=CURDATE() ORDER BY a.TimeStamp DESC'; 
if (allowedToOpen(916,'1rtc')) {
	$addlprocess='smsclientstechnicians.php?w=EditSpecifics&TxnID=';
	$addlprocesslabel='Edit';
	$delprocess='smsclientstechnicians.php?w=Delete&TxnID=';
}
     include('../backendphp/layout/displayastablenosort.php');
	 
     break;
	 

case 'ListAll':
if (!allowedToOpen(915,'1rtc')) { echo 'No permission'; exit;}
$title='List of All Messages';

$width="85%";

$editprocess='smsclientstechnicians.php?w=SendSMS&TxnID=';
$editprocesslabel='Lookup';
$sql.=' ORDER BY a.TimeStamp DESC';
if (allowedToOpen(916,'1rtc')) {
$delprocess='smsclientstechnicians.php?w=Delete&TxnID=';
}
     include('../backendphp/layout/displayastable.php');
	 
     break;



case 'AddNew':
if (!allowedToOpen(916,'1rtc')) { echo 'No permission'; exit;}
$title='Add New Text Message';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3><br>';
echo '<div style="background-color:white;border:2px solid blue;width:40%;padding:5px;">
   
    <form action="smsclientstechnicians.php?w=Add" method="POST" enctype="multipart/form-data" style="display: inline">';
  
		echo 'To: <select name="To"><option value="0">Clients</option><option value="1">Technicians</option></select><br>
        Text Message: <br><textarea name="Msg" cols="50" rows="10"></textarea><br><br>
		<input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">
        <input type="submit" name="submit" value="Add Text">
   </form><br>
   
   </div>';
break;	
	 
	case 'EditSpecifics':
	if (!allowedToOpen(916,'1rtc')) { echo 'No permission'; exit;}
		$title='Edit Message';
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3>';
		$txnid=intval($_GET['TxnID']);

		$sql=$sql.' WHERE SMSID='.$txnid;
		
		$stmt=$link->query($sql); $result=$stmt->fetch();

		echo '<div style="border:1px solid blue;background-color:white;width:40%;padding:5px;">';
		echo 'To: <u>'.$result['To'].' &nbsp; </u><br>';
		// echo '<br>Condition: <u>'.$result['OptionLabel'].' &nbsp; </u>';
		echo 'Original Message:<br><div style="border:1px solid black;padding:4px;color:grey;width:68%;">'.$result['Msg'].'</div>';
		echo '</div>';
		
		echo '<br><div style="border:1px solid blue;background-color:white;width:40%;padding:5px;">';
		
		echo '<form action="smsclientstechnicians.php?w=Edit&TxnID='.$result['TxnID'].'" method="POST" enctype="multipart/form-data" style="display: inline">';
		
	

echo	'<br>To: <select name="To"><option value="0" '.($result['To']=='Clients'?'selected':'').'>Clients</option><option value="1"'.($result['To']=='Technicians'?'selected':'').'>Technicians</option></select><br>New Message: <br><textarea name="Msg" cols="50" rows="10">'.str_replace("<br>","\r\n",$result['Msg']).'</textarea><br><br>
		<input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">
        <input type="submit" name="submit" value="Update Text">';
		echo '</form></div>';
		
		
	break;
	
	
	case 'Edit':
	if (!allowedToOpen(916,'1rtc')) { echo 'No permission'; exit;}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$columnstoadd=array('To','Msg');
	
		
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		$sql='UPDATE `gen_info_2smsclientstechnicians` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE Final=0 AND '.(!allowedToOpen(2201,'1rtc')?'EncodedByNo='.$_SESSION['(ak0)'].' AND':'').' SMSID='.intval($_GET['TxnID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
		header("Location:smsclientstechnicians.php");
    break;
	
	
     
	case 'Add':
	if (!allowedToOpen(916,'1rtc')) { echo 'No permission'; exit;}
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='INSERT INTO `gen_info_2smsclientstechnicians` (`Msg`,`To`,`EncodedByNo`,`TimeStamp`)
	VALUES (\''.$_POST['Msg'].'\',\''.$_POST['To'].'\','.$_SESSION['(ak0)'].',NOW())';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sqllastid='select LAST_INSERT_ID() AS lastid;';
	$stmt=$link->query($sqllastid); $result=$stmt->fetch();
	
	
    header('Location:smsclientstechnicians.php?w=SendSMS&TxnID='.$result['lastid']);
	
	
    break;
	
	
	
	
	case 'Delete':
	if (!allowedToOpen(916,'1rtc')) { echo 'No permission'; exit;}
		$sql='DELETE FROM `gen_info_2smsclientstechnicians` WHERE SMSID='.$_REQUEST['TxnID'].' AND EncodedByNo='.$_SESSION['(ak0)'].' AND Final=0';
		$stmt=$link->prepare($sql);
		$stmt->execute();
    header('Location:smsclientstechnicians.php');
    break;
	
	
	case 'SetAsFinal':
	if (!allowedToOpen(916,'1rtc')) { echo 'No permission'; exit;}
		$txnid=$_REQUEST['TxnID'];
		$sql='UPDATE `gen_info_2smsclientstechnicians` SET Final=1 WHERE SMSID='.$txnid.' AND Final=0 AND EncodedByNo='.$_SESSION['(ak0)'].'';
		$stmt=$link->prepare($sql);
		$stmt->execute();
    header('Location:smsclientstechnicians.php?w=SendSMS&TxnID='.$txnid);
    break;
	
	case 'Unset':
	if (!allowedToOpen(916,'1rtc')) { echo 'No permission'; exit;}
		$txnid=$_REQUEST['TxnID'];
		$sql='UPDATE `gen_info_2smsclientstechnicians` SET Final=0 WHERE SMSID='.$txnid.' AND Final=1 AND EncodedByNo='.$_SESSION['(ak0)'].'';
		$stmt=$link->prepare($sql);
		$stmt->execute();
    header('Location:smsclientstechnicians.php?w=SendSMS&TxnID='.$txnid);
    break;
	
	
	case 'SendSMS':
	if (!allowedToOpen(915,'1rtc')) { echo 'No permission'; exit;}
	$title='SMS Text Blast';
	echo '<title>'.$title.'</title>';
	$txnid=intval($_GET['TxnID']);
	
	$sql=$sql.' WHERE SMSID='.$txnid;
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$txnid=$result['TxnID'];
	$sent=$result['Final'];
	$sender=$result['EncodedByNo'];
	$to=$result['To'];
	
	$getmsg=str_replace(" ","%20",str_replace("<br>","%0D%0A",$result['Msg']));
	
	


	$msg='1ROTARY:%20'.$getmsg.'%0D%0A%0D%0A-%201Rotary%20Sales%20Team%0D%0AReply%20STOP%20to%20unsubscribe.';

	echo '<b>To</b>: '.$to.'<br><b>Message:</b><br><div style="width:35%;padding:10px;border:1px solid black;background-color:white;">'.str_replace('%0D%0A','<br>',str_replace('%20',' ',$msg));
	echo '<br>';
	
	if($sent==0){
		if (allowedToOpen(916,'1rtc')) {
		echo '<br><form action="smsclientstechnicians.php?w=SetAsFinal&TxnID='.$txnid.'" method="POST"><a href="smsclientstechnicians.php?w=EditSpecifics&TxnID='.$txnid.'">Edit?</a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input style="background-color:blue;color:white;" type="submit" value="Set as Final" name="btnSubmit" OnClick="return confirm(\'Is this Final?\');"></form>';
		}
	}
	$sqlre='SELECT CONCAT(GROUP_CONCAT(MobileNo SEPARATOR ";"),";") AS mobiles FROM gen_info_1smstestreceiver;';
	$stmtre=$link->query($sqlre); $resultre=$stmtre->fetch();
	echo '<a href="sms:'.$resultre['mobiles'].'?body='.$msg.'">Send SMS to Test Receiver</a></div>';
	
	if($sent==1){
		if (allowedToOpen(916,'1rtc')) {
		echo '<form action="smsclientstechnicians.php?w=Unset&TxnID='.$txnid.'" method="POST"><input type="submit" value="Unpost" name="Unpost"></form>';
		}
		
		if(!isset($_POST['mobileno'])){
		echo '<br><form action="smsclientstechnicians.php?w=SendSMS&TxnID='.$txnid.'" method="POST"><table style="font-size:9.5pt;width:45%;background-color:white;padding:5px;">
 <tr><th width="50px">All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></th><th>'.($to=='Clients'?'Client':'Technician').' Name</th><th>Mobile Number</th></tr>';
 
 $colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		if($to=='Clients'){
			$sql='select ClientName,Mobile as MobileNo FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE Subscribed=1 AND BranchNo='.$_SESSION['bnum'].' AND LENGTH(Mobile)=11 AND c.ClientNo NOT IN (10000,10001,10004) ORDER BY ClientName ';
			$stmt=$link->query($sql); $result=$stmt->fetchAll();
			$toname='ClientName';
		} else {
			$sql='select TechName,MobileNo from gen_info_1technicians WHERE FIND_IN_SET('.$_SESSION['bnum'].',BranchNos) AND LENGTH(MobileNo)=11 AND Subscribed=1 ORDER BY TechName';
			$stmt=$link->query($sql); $result=$stmt->fetchAll();
			$toname='TechName';
		}
		
		foreach($result as $res){
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td style="text-align:right;"><input type="checkbox" value="'.$res['MobileNo'].'" name="mobileno[]" '.((isset($_POST['mobileno']) AND in_array($res['MobileNo'], $_POST['mobileno']))?'checked':'').'/></td><td>'.$res[$toname].'</td><td style="width:200px;">'.$res['MobileNo'].'</td></tr>';
			$colorcount++;
		}
		echo '<tr><td colspan=4 align="center"><input style="background-color:green;color:white;width:200px" type="submit" value="Confirm List?" name="btnCreateLink" OnClick="return confirm(\'Confirm List?\');"></td></tr></table></form>';
		}
		
		if(isset($_POST['mobileno'])){
			
				$sql0='CREATE TEMPORARY TABLE Mobiles (MobileNo VARCHAR(11));';
				$stmt0=$link->prepare($sql0); $stmt0->execute();
				
			foreach ($_POST['mobileno'] AS $mobile){
				$sqlinsert='INSERT INTO Mobiles SET MobileNo="'.$mobile.'";';
				$stmtinsert=$link->prepare($sqlinsert); $stmtinsert->execute();
			}
			
			
			$sqlmain='select MobileNo FROM Mobiles';
		$sqlcnt='select CEILING(COUNT(MobileNo)/40) AS trcount,COUNT(MobileNo) as totalidno FROM Mobiles;';
		
		
		$stmtcnt=$link->query($sqlcnt); $rescnt=$stmtcnt->fetch();
		
		
		$starttr=1;
		$startoffset=0;
		while($starttr<=$rescnt['trcount']){
			$sqlm=$sqlmain.' LIMIT 40 OFFSET '.$startoffset;
			
			$stmtno=$link->query($sqlm); $resno=$stmtno->fetchAll(); $numbers='';
			foreach ($resno as $rno){
				$numbers.=$rno['MobileNo'].';';
			}
			$numbers=substr($numbers, 0, -1);
			 
			$title='';
			
			echo '<br><b><a href="sms:'.$numbers.'?body='.$msg.'">Send SMS</a></b><b> ('.($startoffset+1).' to '.($starttr==$rescnt['trcount']?$rescnt['totalidno']:($startoffset+40)).')</b>';
			
			$sql=$sqlm;
			if($to=='Clients'){
				$columnnames=array('MobileNo');
			} else {
				$columnnames=array('MobileNo');
			}
			$hidecount=false;
			include('../backendphp/layout/displayastablenosort.php');
			
			
			$starttr++;
			
			$startoffset=$startoffset+40;
			
		}
		}
		
		
		 
	}

    break;
	
	
	case 'SMSTestReceiver':
	if (!allowedToOpen(916,'1rtc')) { echo 'No Permission'; exit(); }
	$title='SMS Test Receiver';    

	$columnnames=array(
				array('field'=>'MobileNo','type'=>'text','size'=>25,'required'=>true));
							
		$action='smsclientstechnicians.php?w=AddSMSTestReceiver'; $fieldsinrow=2; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');
		
		$editprocess='smsclientstechnicians.php?w=EditSpecificsSMSTestReceiver&TxnID=';
		$editprocesslabel='Edit';
		$delprocess='smsclientstechnicians.php?w=DeleteSMSTestReceiver&TxnID=';
$columnnames=$columnnameslist; 
	$width="20%"; $title='';
	 include('../backendphp/layout/displayastablenosort.php');
	break;
	
	case 'AddSMSTestReceiver':
		if (allowedToOpen(916,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql = 'INSERT INTO gen_info_1smstestreceiver (MobileNo) VALUES (\''.$_POST['MobileNo'].'\')';
			$stmt=$link->prepare($sql); $stmt->execute();
		}
		header('Location:smsclientstechnicians.php?w=SMSTestReceiver');
	break;
	
	case 'EditSpecificsSMSTestReceiver':
	
        if (!allowedToOpen(916,'1rtc')){ header("Location:".$_SERVER['HTTP_REFERER']);}
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);

		//Condition For Edit Specifics
		$sql=$sql.' WHERE SID='.$txnid;
		$columnstoedit=$columnstoadd;		
		$columnnames=$columnnameslist;
		
		$editprocess='smsclientstechnicians.php?w=EditSMSTestReceiver&TxnID='.$txnid;		
		include('../backendphp/layout/editspecificsforlists.php');
	
	break;
	
	case 'EditSMSTestReceiver':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(916,'1rtc')){
		$sql='UPDATE `gen_info_1smstestreceiver` SET MobileNo="'.$_POST['MobileNo'].'" WHERE SID='.intval($_GET['TxnID']);
		$stmt=$link->prepare($sql); $stmt->execute();
		}
		header('Location:smsclientstechnicians.php?w=SMSTestReceiver');
    break;
	

    case 'DeleteSMSTestReceiver':
        if (allowedToOpen(916,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM `gen_info_1smstestreceiver` WHERE SID='.intval($_GET['TxnID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header('Location:smsclientstechnicians.php?w=SMSTestReceiver');
    break;
	
    case 'StopReceiveSMS':
        if (allowedToOpen(916,'1rtc')){
			$title='Clients SMS Subscription';
			$sql='select c.ClientNo AS TxnID,ClientName,Mobile,IF(Subscribed=1,"Yes","No") AS `Subscribed` FROM gen_info_1branchesclientsjxn bcj JOIN 1clients c ON bcj.ClientNo=c.ClientNo WHERE BranchNo='.$_SESSION['bnum'].' AND LENGTH(Mobile)=11 AND c.ClientNo NOT IN (10000,10001,10004) ORDER BY ClientName';
			$editprocess='smsclientstechnicians.php?action_token='.$_SESSION['action_token'].'&w=SubsUnsubs&ClientNo=';
			$editprocesslabel='Subscribe/UNsubscribe';
			$columnnames=array('ClientName','Mobile','Subscribed');
			$width='50%';
     include('../backendphp/layout/displayastable.php');
		} else {
			echo 'No Permission'; exit();
		}
    break;
	
	case 'SubsUnsubs':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(916,'1rtc')){
		$sql='UPDATE `1clients` SET Subscribed=IF(Subscribed=1,0,1) WHERE ClientNo='.intval($_GET['ClientNo']);
		$stmt=$link->prepare($sql); $stmt->execute();
		}
		header('Location:smsclientstechnicians.php?w=StopReceiveSMS');
    break;
	
}
  $link=null;  $stmt=null;
?>
<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>