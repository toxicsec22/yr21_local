<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(64323,64324),'1rtc')) { echo 'No permission'; exit();}
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT ReasonID,Reason FROM acctg_1callandtextreasons ORDER BY Reason','ReasonID','Reason','reasons'); 
echo comboBox($link,'SELECT TelMobileNo,CONCAT (Nickname,\' \',SurName) as Fullname FROM acctg_4billassignment m left join 1employees e on e.IDNo=m.AssigneeNo where Active=1 and TelMobileNo is not null and TelMobileNo<>0','Fullname','TelMobileNo','MobileNos'); 
include_once('../backendphp/layout/linkstyle.php');
echo'</br>';
if (allowedToOpen(64323,'1rtc')){
echo'<a id="link" href="callandtextrecords.php?w=Records">Call and Text Records</a>';
}
if (allowedToOpen(64324,'1rtc')){
echo' <a id="link" href="callandtextrecords.php?w=Reasons">Reasons</a>';
}
echo'</br>';
$which=(!isset($_GET['w'])?'Records':$_GET['w']);
if (in_array($which,array('Add','Delete','EditProcess'))){
	$reasonid=comboBoxValue($link, 'acctg_1callandtextreasons', 'Reason', $_REQUEST['Reason'], 'ReasonID');
	$Reason=', ReasonID=\''.$reasonid.'\'';
	if(isset($_GET['otherphone'])){
		$switch=', switch=\'1\'';
		$otherphonevalue='&otherphone=0';
	}else{
		$otherphonevalue='';
		$switch='';
	}
}
switch ($which){
	case'Records':
if (!allowedToOpen(64323,'1rtc')) { echo 'No permission'; exit();}	
	echo'<title>Call and Text Records</title></br>
</i><div style="background-color:#ededed; width:580px; padding:5px;">
<b>Note:</b></br></br>
	Log all calls or text messages to other networks that may result in mobile charges.</br> Calls that will not be charged on top of the monthly recurring fee need not be encoded here.</br> 
</div>';
		$radionamefield='radiolist';	
			echo '</br><form id="form-id"><h3 style="display:inline;">Encode Call or Text:</h3>
			Company Phone <input type="radio" id="watch-me1" name="'.$radionamefield.'">'.str_repeat('&nbsp;',5).'
			Personal Phone <input type="radio" id="watch-me2" name="'.$radionamefield.'">
			</form>
			';
			$companyphone='<div style="margin-top:0.5%; background-color:1b3d6d; padding:5px; color:white; width:max-content;"><form method="post" action="callandtextrecords.php?w=Add">
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
			Purpose <input type="text" name="Purpose" size="25" required>
			MobileNo <input type="text" name="MobileNo" size="10" list="MobileNos" required>
			ReceiverMobileNo <input type="text" name="ReceiverNo" size="10" required>
			DateTime <input type="datetime-local" name="DateTime" size="10" required>
			Reason <input type="text" name="Reason" size="25" list="reasons" required>
			<input  type="submit" name="submit">
			</form></div>			
			';
				
			$otherphone='<div style="margin-top:0.5%; background-color:1b3d6d; padding:5px; color:white; width:max-content;"><form method="post" action="callandtextrecords.php?w=Add&otherphone=1">
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
			Purpose <input type="text" name="Purpose" size="25" required>
			MobileNo <input type="text" name="MobileNo" size="10" required>
			ReceiverMobileNo <input type="text" name="ReceiverNo" size="10" required>
			DateTime <input type="datetime-local" name="DateTime" size="10" required>
			Reason <input type="text" name="Reason" size="25" list="reasons" required>
			<input  type="submit" name="submit"> 
			</form></div>
			';
			
			//companyphone
			echo '<div id="show-me1" style="display:none">
						'.$companyphone.'
				</div>';
			
			//otherphone
			echo '<div id="show-me2" style="display:none">
					'.$otherphone.'
				</div>';				
			
			include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';	
			
			$title='';
if(isset($_REQUEST['otherphone'])){
	if($_REQUEST['otherphone']==0){
		$value=1;
		$sqlcondition='where switch=1';
		$columnnames=array('Branch','Purpose','MobileNo','ReceiverMobileNo','DateTime','Reason','EncodedBy','TimeStamp');
		$otherphonevalue='&otherphone=0';
		$label='Personal Phone';		
	}else{
		$value=0;
		$sqlcondition='where switch=0';
		$columnnames=array('Branch','Purpose','MobileNo','ReceiverMobileNo','DateTime','Reason','EncodedBy','TimeStamp');
		$otherphonevalue='';	
		$label='Company Phone';		
	}
}else{
	$value=0;
	$columnnames=array('Branch','Purpose','MobileNo','ReceiverMobileNo','DateTime','Reason','EncodedBy','TimeStamp');
	$sqlcondition='where switch=0';
	$otherphonevalue='';
	$label='Company Phone';
}	
$formdesc='</i><h3>'.$label.' Call and Text Records</h3>';
$formdesc.='</br><form method="post" action="callandtextrecords.php?w=Records">
			<input type="submit" name="submit" value="CompanyPhone/PersonalPhone">
			<input type="hidden" name="otherphone" value="'.$value.'">
		</form>';
		$sql='select cr.TxnID,MobileNo,CONCAT(Nickname,\' \', SurName) as EncodedBy,b.Branch,Purpose,MobileNo,ReceiverNo as ReceiverMobileNo,DateTime,catr.Reason as Reason,cr.TimeStamp from acctg_4callandtextrecords cr left join 1employees e on e.IDNo=cr.EncodedByNo left join attend_30currentpositions cp on cp.IDNo=cr.EncodedByNo left join 1branches b on b.BranchNo=cp.BranchNo left join acctg_1callandtextreasons catr on catr.ReasonID=cr.ReasonID '.$sqlcondition.' and b.BranchNo=\''.$_SESSION['bnum'].'\'';
		$txnidname='TxnID';
		// echo $sql;
		$editprocess='callandtextrecords.php?w=Edit'.$otherphonevalue.'&TxnID=';
		$editprocesslabel='Edit';
		$delprocess='callandtextrecords.php?w=Delete'.$otherphonevalue.'&TxnID=';
		include('../backendphp/layout/displayastablenosort.php');
		
	break;
	
	case'Add':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='INSERT INTO `acctg_4callandtextrecords` set Purpose=\''.$_POST['Purpose'].'\',MobileNo=\''.$_POST['MobileNo'].'\',ReceiverNo=\''.$_POST['ReceiverNo'].'\', DateTime=\''.$_POST['DateTime'].'\',  EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() '.$Reason.' '.$switch.'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:callandtextrecords.php?w=Records'.$otherphonevalue.'');
	break;
	
	case'Delete':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnid=intval($_GET['TxnID']);
		$sql='delete from acctg_4callandtextrecords where TxnID=\''.$txnid.'\' and date(TimeStamp)=curdate()';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:callandtextrecords.php?w=Records'.$otherphonevalue.'');
	break;
	
	case'Edit':
	$txnid= intval($_GET['TxnID']);
	$sql='select Purpose,MobileNo,ReceiverNo,DateTime,Reason as Reason from `acctg_4callandtextrecords` cr left join acctg_1callandtextreasons catr on catr.ReasonID=cr.ReasonID where TxnID='.$txnid.'';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo '<title>Edit</title></br><h3>Edit?</h3>';
	if(isset($_GET['otherphone'])){
	$otherphonevalue='&otherphone=0';
	$listMobileNo='';	
	}else{
		$otherphonevalue='';
		$listMobileNo='list="MobileNos"';
	}
	echo'<form method="post" action="callandtextrecords.php?w=EditProcess'.$otherphonevalue.'&TxnID='.$txnid.'">
			Purpose <input type="text" name="Purpose" size="25" value="'.$result['Purpose'].'" required>
			MobileNo <input type="text" name="MobileNo" size="10" value="'.$result['MobileNo'].'" '.$listMobileNo.' required>
			ReceiverNo <input type="text" name="ReceiverNo" size="10" value="'.$result['ReceiverNo'].'" required>
			DateTime <input type="text" name="DateTime" size="17" value="'.$result['DateTime'].'" required>
			Reason <input type="text" name="Reason" size="25" value="'.$result['Reason'].'" list="reasons" required>
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
			<input type="submit" name="submit" value="Edit">
		</form>';
	break;
	
	case'EditProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnid= intval($_GET['TxnID']);
	$sql='Update `acctg_4callandtextrecords` set Purpose=\''.$_POST['Purpose'].'\',MobileNo=\''.$_POST['MobileNo'].'\',ReceiverNo=\''.$_POST['ReceiverNo'].'\',DateTime=\''.$_POST['DateTime'].'\', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() '.$Reason.' '.$switch.' where TxnID=\''.$txnid.'\' and date(TimeStamp)=curdate()';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:callandtextrecords.php?w=Records'.$otherphonevalue.'');
	break;
	
////////////////////////////////////////////////////// Reasons/////////////////////////////////////////	
	case'Reasons':
if (!allowedToOpen(64324,'1rtc')) { echo 'No permission'; exit();}	
	$title='Reasons';
	$formdesc='</i></br>
	<div style="margin-top:0.5%; background-color:1b3d6d; padding:5px; color:white; width:max-content;">
		<form method="post" action="callandtextrecords.php?w=AddReason">
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
			Reason: <input type="text" name="Reason" size="25" required>
			<input  type="submit" name="submit">
		</form>
	</div>';
	$sql='select ReasonID,Reason,CONCAT(Nickname,\' \', SurName) as EncodedBy,catr.TimeStamp from acctg_1callandtextreasons catr left join 1employees e on e.IDNo=catr.EncodedByNo';
	$txnidname='ReasonID';
	$editprocess='callandtextrecords.php?w=EditReason&ReasonID=';
	$editprocesslabel='Edit';
	$delprocess='callandtextrecords.php?w=DeleteReason&ReasonID=';
		$columnnames=array('Reason','EncodedBy','TimeStamp');
		include('../backendphp/layout/displayastablenosort.php');
	break;
	
	case'AddReason':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='INSERT INTO `acctg_1callandtextreasons` set Reason=\''.$_POST['Reason'].'\',  EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:callandtextrecords.php?w=Reasons');
	break;
	
	case'DeleteReason':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$reasonid=intval($_GET['ReasonID']);
	
		$sqlchecker='select * from acctg_4callandtextrecords where ReasonID=\''.$reasonid.'\'';
		$stmtcheker=$link->query($sqlchecker);
		
		if($stmtcheker->rowCount()!=0){
			echo '</br>This reason is being used in Call and Text Records. You cannot delete.';
			exit();
		}
	
		$sql='delete from acctg_1callandtextreasons where ReasonID=\''.$reasonid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:callandtextrecords.php?w=Reasons');
	break;
	
	case'EditReason':
	$reasonid= intval($_GET['ReasonID']);
	$sql='select Reason from `acctg_1callandtextreasons` where ReasonID='.$reasonid.'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo '<title>Edit</title></br><h3>Edit?</h3>';
	echo'<form method="post" action="callandtextrecords.php?w=EditProcessReason&ReasonID='.$reasonid.'">
			Reason: <input type="text" name="Reason" value="'.$result['Reason'].'" size="25">
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
			<input type="submit" name="submit" value="Edit">
		</form>';
break;

case'EditProcessReason':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$reasonid= intval($_GET['ReasonID']);
		
		$sqlchecker='select * from acctg_4callandtextrecords where ReasonID=\''.$reasonid.'\'';
		$stmtcheker=$link->query($sqlchecker);
		
		if($stmtcheker->rowCount()!=0){
			echo '</br>This reason is being used in Call and Text Records. You cannot edit.';
			exit();
		}
	
	$sql='Update `acctg_1callandtextreasons` set Reason=\''.$_POST['Reason'].'\', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() where ReasonID=\''.$reasonid.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:callandtextrecords.php?w=Reasons');
break;
	
}

?>