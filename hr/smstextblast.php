<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
if (!allowedToOpen(73091,2201,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');



include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_REQUEST['w'])?'List':($_REQUEST['w']);


if (in_array($which,array('List','ListAll','EditSpecifics','SendSMS','AddNew','ListofConditions'))){
	include_once '../backendphp/layout/linkstyle.php';
	echo '<br><a id="link" href="smstextblast.php?w=List">List of Messages (Date Today)</a> ';
	echo '<a id="link" href="smstextblast.php?w=ListAll">List of All Messages</a> ';
	echo '<a id="link" href="smstextblast.php?w=AddNew">Add New Text Message</a> '.((allowedToOpen(2201,'1rtc'))?str_repeat("&nbsp;",10).' <a id="link" href="smstextblast.php?w=ListofConditions">List of Conditions</a>':'').' ';
	echo '<br><br>';
}
if (in_array($which,array('List','ListAll','EditSpecifics','SendSMS'))){
   $sql='SELECT a.*,OptionLabel,SQLCondition,OptionLabel AS `TxtCondition`,IF(Final=1,"Yes","No") AS `Final?`,REPLACE(a.Msg, "\r\n", "<br>") AS Msg,(CASE WHEN MsgFor = 1 THEN "All" WHEN MsgFor = 2 THEN "Office" ELSE "Branch/Warehouse" END) AS `To`,SMSID AS TxnID, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy FROM hr_2smstxtblast a 
LEFT JOIN 1employees e ON e.IDNo=a.EncodedByNo JOIN hr_0smscondition sc ON a.CID=sc.CID';
$columnnames=array('Msg','To','TxtCondition','Final?','EncodedBy','TimeStamp');
   $columnstoadd=array('Msg','MsgFor');
}


switch ($which){
case 'List':
$title='List of Messages (Date Today)';    

$editprocess='smstextblast.php?w=SendSMS&TxnID=';
$editprocesslabel='Send SMS';
$sql.=' WHERE DATE(a.TimeStamp)=CURDATE() ORDER BY a.TimeStamp DESC'; 
$addlprocess='smstextblast.php?w=EditSpecifics&TxnID=';
$addlprocesslabel='Edit';
$delprocess='smstextblast.php?w=Delete&TxnID=';
     include('../backendphp/layout/displayastablenosort.php');
	 
     break;
	 

case 'ListAll':
$title='List of All Messages';

$width="85%";
$editprocess='smstextblast.php?w=SendSMS&TxnID=';
$editprocesslabel='Lookup';
$sql.=' ORDER BY a.TimeStamp DESC';
$delprocess='smstextblast.php?w=Delete&TxnID=';
     include('../backendphp/layout/displayastable.php');
	 
     break;
	
case 'AddNew':
$title='Add New Text Message';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3><br>';
echo '<div style="background-color:white;border:2px solid blue;width:40%;padding:5px;">
   
    <form action="smstextblast.php?w=Add" method="POST" enctype="multipart/form-data" style="display: inline">
   
        To: <select name="MsgFor"><option value="1">1 - All</option><option value="2">2 - Office</option><option value="3">3 - Branch/Warehouse</option></select> <br>';
		$optcondi='';
		$sqloc='SELECT * FROM hr_0smscondition ORDER BY CID';
		$stmtoc=$link->query($sqloc); $resoc=$stmtoc->fetchAll();
		
		foreach($resoc AS $reso){
			$optcondi.='<option value="'.$reso['CID'].'">'.$reso['OptionLabel'].'</option>';
		}
		echo '
        Condition: <select name="CID">'.$optcondi.'</select><br>';
		echo '
        Text Message: <br><textarea name="Msg" cols="50" rows="10"></textarea><br><br>
		<input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">
        <input type="submit" name="submit" value="Add Text">
   </form><br>
   
   </div>';
break;	
	 
	case 'EditSpecifics':
		$title='Edit Message';
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3>';
		$txnid=intval($_GET['TxnID']);

		$sql=$sql.' WHERE SMSID='.$txnid;
		
		$stmt=$link->query($sql); $result=$stmt->fetch();

		echo '<div style="border:1px solid blue;background-color:white;width:40%;padding:5px;">';
		echo 'To: <u>'.$result['To'].' &nbsp; </u>';
		echo '<br>Condition: <u>'.$result['OptionLabel'].' &nbsp; </u>';
		echo '<br>Original Message:<br><div style="border:1px solid black;padding:4px;color:grey;width:68%;">'.$result['Msg'].'</div>';
		echo '</div>';
		
		echo '<br><div style="border:1px solid blue;background-color:white;width:40%;padding:5px;">';
		
		echo '<form action="smstextblast.php?w=Edit&TxnID='.$result['TxnID'].'" method="POST" enctype="multipart/form-data" style="display: inline">';
		
		echo 'To <select name="MsgFor"><option value="1" '.($result['MsgFor']==1?'selected':'').'>1 - All</option><option value="2" '.($result['MsgFor']==2?'selected':'').'>2 - Office</option><option value="3" '.($result['MsgFor']==3?'selected':'').'>3 - Branch/Warehouse</option></select>';

$optcondi='';
		$sqloc='SELECT * FROM hr_0smscondition ORDER BY CID';
		$stmtoc=$link->query($sqloc); $resoc=$stmtoc->fetchAll();
		
		foreach($resoc AS $reso){
			$optcondi.='<option value="'.$reso['CID'].'" '.($result['CID']==$reso['CID']?'selected':'').'>'.$reso['OptionLabel'].'</option>';
		}
		echo '
        <br>Condition: <select name="CID">'.$optcondi.'</select>';

echo	'<br>New Message: <br><textarea name="Msg" cols="50" rows="10">'.str_replace("<br>","\r\n",$result['Msg']).'</textarea><br><br>
		<input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">
        <input type="submit" name="submit" value="Update Text">';
		echo '</form></div>';
		
		
	break;
	
	
	case 'Edit':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$columnstoadd=array('Msg','MsgFor','CID');
	
		
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		$sql='UPDATE `hr_2smstxtblast` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE Final=0 AND '.(!allowedToOpen(2201,'1rtc')?'EncodedByNo='.$_SESSION['(ak0)'].' AND':'').' SMSID='.intval($_GET['TxnID']);
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		
		header("Location:smstextblast.php");
    break;
	
	
	case 'ListofConditions':
$title='List of Conditions';    



$formdesc='</i><br><form action="smstextblast.php?w=AddCondition" method="POST" enctype="multipart/form-data" style="display: inline">
        Option Label: <input type="text" name="OptionLabel" size="30"> SQL Condition: <input type="text" name="SQLCondition" size="30" placeholder="AND 1=1"> 
		<input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">
        <input type="submit" name="submit" value="Add new">
   </form>';
 
$formdesc.='<br><br><div style="background-color:white;padding:5px;border:1px solid black;">SQL:<br> FROM 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN 1_gamit.0idinfo id ON e.IDNo=id.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo WHERE LENGTH(id.MobileNo)=11 <b>[ADDITIONAL CONDITION HERE]</b> ORDER BY FullName</div><i><br>* Note: Use <b>single</b> quote (<b>\'</b>) instead of double quote in condition.'; 
 
 $sql='SELECT *,CID AS TxnID FROM hr_0smscondition;';
$columnnames=array('OptionLabel','SQLCondition');
   $columnstoadd=array('OptionLabel','SQLCondition');
   
$addlprocess='smstextblast.php?w=EditSpecificsCondi&TxnID=';
$addlprocesslabel='Edit';
$delprocess='smstextblast.php?w=DeleteCondi&TxnID=';

     include('../backendphp/layout/displayastablenosort.php');
	 
     break;
	 
	
	case 'EditSpecificsCondi':
		$title='Edit Condition';
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3>';
		$txnid=intval($_GET['TxnID']);

		$sql='SELECT * FROM hr_0smscondition WHERE CID='.$txnid;
		// echo $sql;
		$stmt=$link->query($sql); $result=$stmt->fetch();

		echo '<div style="border:1px solid blue;background-color:white;width:40%;padding:5px;">';
		echo 'Option Lalel: <u>'.$result['OptionLabel'].'</u> &nbsp;';
		echo '<br>SQL Condition: <u>'.$result['SQLCondition'].'</u></div>';
		echo '</div>';
		
		echo '<br><div style="border:1px solid blue;background-color:white;width:40%;padding:5px;">';
		
		echo '<form action="smstextblast.php?w=EditCondi&TxnID='.$result['CID'].'" method="POST" enctype="multipart/form-data" style="display: inline">';
		echo 'Option Label: <input type="text" name="OptionLabel" value="'.$result['OptionLabel'].'">';
		echo '<br>New SQL Condition: <input type="text" name="SQLCondition" value="'.$result['SQLCondition'].'"><br>
		<input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">
        <input type="submit" name="submit" value="Update Condition">';
		echo '</form></div>';
		
		
	break;
	
	case 'EditCondi':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='UPDATE `hr_0smscondition` SET OptionLabel="'.addslashes($_POST['OptionLabel']).'",SQLCondition="'.addslashes($_POST['SQLCondition']).'" WHERE CID='.intval($_GET['TxnID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
		header("Location:smstextblast.php?w=ListofConditions");
    break;
     
	case 'Add':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='INSERT INTO `hr_2smstxtblast` (`Msg`,`MsgFor`,`EncodedByNo`,`TimeStamp`,`CID`)
	VALUES (\''.$_POST['Msg'].'\',\''.$_POST['MsgFor'].'\', '.$_SESSION['(ak0)'].',NOW(),'.$_POST['CID'].')';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sqllastid='select LAST_INSERT_ID() AS lastid;';
	$stmt=$link->query($sqllastid); $result=$stmt->fetch();
	
	
    header('Location:smstextblast.php?w=SendSMS&TxnID='.$result['lastid']);
	
	
    break;
	
	case 'AddCondition':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='INSERT INTO `hr_0smscondition` (`OptionLabel`,`SQLCondition`)
	VALUES (\''.addslashes($_POST['OptionLabel']).'\',\''.addslashes($_POST['SQLCondition']).'\')';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
    header('Location:smstextblast.php?w=ListofConditions');
	
	
    break;
	
	
	case 'Delete':
		$sql='DELETE FROM `hr_2smstxtblast` WHERE SMSID='.$_REQUEST['TxnID'].' AND EncodedByNo='.$_SESSION['(ak0)'].' AND Final=0';
		$stmt=$link->prepare($sql);
		$stmt->execute();
    header('Location:smstextblast.php');
    break;
	case 'DeleteCondi':
		$sql='DELETE FROM `hr_0smscondition` WHERE CID='.$_REQUEST['TxnID'].'';
		$stmt=$link->prepare($sql);
		$stmt->execute();
    header('Location:smstextblast.php?w=ListofConditions');
    break;
	
	case 'SetAsFinal':
		$txnid=$_REQUEST['TxnID'];
		$sql='UPDATE `hr_2smstxtblast` SET Final=1 WHERE SMSID='.$txnid.' AND Final=0 AND EncodedByNo='.$_SESSION['(ak0)'].'';
		$stmt=$link->prepare($sql);
		$stmt->execute();
    header('Location:smstextblast.php?w=SendSMS&TxnID='.$txnid);
    break;
	
	
	case 'SendSMS':
	
	$title='SMS Text Blast';
	echo '<title>'.$title.'</title>';
	$txnid=intval($_GET['TxnID']);
	
	$sql=$sql.' WHERE SMSID='.$txnid;
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$txnid=$result['TxnID'];
	$optionlabel=$result['OptionLabel'];
	$msgfor=$result['To'];
	$msgforno=$result['MsgFor'];
	$sent=$result['Final'];
	$sender=$result['EncodedByNo'];
	$sqlcd=$result['SQLCondition'];
	
	$getmsg=str_replace(" ","%20",str_replace("<br>","%0D%0A",$result['Msg']));
	
	
	$addlcondi='';
	if($msgforno==2){
		$addlcondi=' AND PseudoBranch=1';
	}
	if($msgforno==3){
		$addlcondi=' AND PseudoBranch IN (0,2) ';
	}
	
	$sqlmain='select FullName,IF(deptid IN (10,2,3,4),b.Branch,dept) AS Branch,id.MobileNo from 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN 1_gamit.0idinfo id ON e.IDNo=id.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo WHERE LENGTH(id.MobileNo)=11 '.$addlcondi.$sqlcd.' ORDER BY FullName ';


	$sqlcnt='select CEILING(COUNT(e.IDNo)/40) AS trcount,COUNT(e.IDNo) as totalidno from 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN 1_gamit.0idinfo id ON e.IDNo=id.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo WHERE LENGTH(id.MobileNo)=11 '.$addlcondi.$sqlcd.' ;';
	$stmtcnt=$link->query($sqlcnt); $rescnt=$stmtcnt->fetch();


	$msg='1ROTARY:%20'.$getmsg.'%0D%0A%0D%0A-%20HR%20Team';

	echo 'To: <u>'.$msgfor.' </u><br>Condition: <u>'.$optionlabel.' </u><br><b>Message:</b><br><div style="width:35%;padding:10px;border:1px solid black;background-color:white;">'.str_replace('%0D%0A','<br>',str_replace('%20',' ',$msg));
	echo '<br>';
	
	if($sent==0){
		echo '<br><form action="smstextblast.php?w=SetAsFinal&TxnID='.$txnid.'" method="POST"><a href="smstextblast.php?w=EditSpecifics&TxnID='.$txnid.'">Edit?</a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input style="background-color:blue;color:white;" type="submit" value="Set as Final" name="btnSubmit" OnClick="return confirm(\'Is this Final?\');"></form>';
	}
	echo '</div>';
	
	if($sent==1){
		include_once $path.'/acrossyrs/commonfunctions/isphone.php';
		if(strstr($useragent,'iPhone') || strstr($useragent,'iPod')) //IOS
		{
			$symbol=';';
		} else {
			$symbol='?';
		}

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
			
			$formdesc='</i><br>'.($sender==$_SESSION['(ak0)']?'<b><a href="sms:'.$numbers.$symbol.'body='.$msg.'">Send SMS</a></b> ':'').'<b> ('.($startoffset+1).' to '.($starttr==$rescnt['trcount']?$rescnt['totalidno']:($startoffset+40)).')</b><i>';
			
			$sql=$sqlm;
			$columnnames=array('FullName','Branch','MobileNo');
			$hidecount=false;
			include('../backendphp/layout/displayastablenosort.php');
			
			
			$starttr++;
			
			$startoffset=$startoffset+40;
			
		}
	}

    break;
	
	
}
  $link=null;  $stmt=null;
?>