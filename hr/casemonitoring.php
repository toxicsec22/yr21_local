<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6506,'1rtc')) { echo 'No permission'; exit();}
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

include_once('../backendphp/layout/linkstyle.php');
echo'</br>';
if (allowedToOpen(6506,'1rtc')) {
	echo'<a id="link" href="casemonitoring.php">Active Civil Cases</a> <a id="link" href="casemonitoring.php?w=UpdatesDueTHISWeek">Updates Due THIS Week</a> <a id="link" href="casemonitoring.php?w=UpdatesDueNEXTWeek">Updates Due NEXT Week</a>  <a id="link" href="casemonitoring.php?w=CaseStatus">Case Status List</a>';
}
echo'</br>';


	$which=(!isset($_GET['w'])?'lists':$_GET['w']);

	if (in_array($which,array('lists','EditSpecifics','Lookup','UpdatesDueNEXTWeek'))){
		$sql='SELECT s.*,Branch,(SELECT GROUP_CONCAT(DISTINCT(CONCAT(NickName," ",SurName)) SEPARATOR "<br>") FROM 1employees WHERE FIND_IN_SET(IDNo,s.IDNo)) AS PersonInvolved,(SELECT CONCAT("(",CStatus," - ",DateOfNextUpdate,")") FROM hr_2casemonitoringsub `ebs` JOIN hr_0casestatus istat ON ebs.CID=istat.CID WHERE TxnID=s.TxnID ORDER BY DateOfNextUpdate DESC LIMIT 1) AS `Status - NxtUpdate` FROM hr_2casemonitoringmain s JOIN 1branches b ON s.BranchNo=b.BranchNo';
	}

		if (in_array($which,array('lists','EditSpecifics'))){
			$columnnameslist=array('Branch','PersonInvolved','Date','Details','Status - NxtUpdate'); 
			$columnstoadd=array('Branch','PersonInvolved','Date','Details');
            
            echo comboBox($link,'SELECT BranchNo,Branch FROM 1branches WHERE Pseudobranch IN (0,2) ORDER BY Branch','BranchNo','Branch','branchlist');
            
		}
		if (in_array($which,array('lists','EditSpecifics','Lookup'))){
			echo comboBox($link,'SELECT IDNo,CONCAT(Nickname," ",SurName) AS PersonInvolved FROM 1employees ORDER BY PersonInvolved','IDNo','PersonInvolved','idnolist');
		}
		if (in_array($which,array('addlists','editlists'))){
			$columnstoadd=array('Date','Details');
            $branchno=comboBoxValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo'); 
           
		}

		if (in_array($which,array('addlists','editlists','AddDelPersonInvolved'))){
			$idno=comboBoxValue($link,'1employees','CONCAT(Nickname," ",SurName)',addslashes($_POST['PersonInvolved']),'IDNo'); 

		}
        

	if (in_array($which,array('CaseStatus','EditSpecificsCaseStatus'))){
		$sql='SELECT * FROM hr_0casestatus';
		$columnnameslist=array('CStatus'); 
		$columnstoadd=$columnnameslist;

	}

	if (in_array($which,array('AddCaseStatus','EditCaseStatus'))){
		$columnstoadd=array('CStatus');
	}

	
	switch ($which){
		case 'lists':
			if (!allowedToOpen(6506,'1rtc')) { echo 'No Permissions'; exit(); }
				$title='Active Civil Cases';

				$method='post';
				
				$formdesc=''; echo '<br>';
			
				
				$columnnames=array(
				array('field'=>'Branch','type'=>'text','list'=>'branchlist','size'=>15,'required'=>true),
				array('field'=>'PersonInvolved','caption'=>'Person Involved (can add other persons later [in Lookup])','list'=>'idnolist','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'Date','type'=>'date','size'=>20,'required'=>true),
				array('field'=>'Details','type'=>'text','size'=>20,'required'=>true),
				);
				
				$action='casemonitoring.php?w=addlists'; $liststoshow=array();
				
				$buttonval='Add New'; $modaltitle='Add New Case';
				include('../backendphp/layout/inputmainformmodal.php');
				
				
				$editprocess='casemonitoring.php?w=Lookup&TxnID='; $editprocesslabel='Lookup';
				
				$addlprocess='casemonitoring.php?w=EditSpecifics&TxnID='; $addlprocesslabel='Edit';
				$delprocess='casemonitoring.php?w=deletelists&TxnID='; $delprocesslabel='Delete'; 
				
				$columnnames=$columnnameslist;   
				$formdesc=''; $txnidname='TxnID';
				$title='';
						
				$sql=$sql.' ORDER BY TimeStamp DESC';
				$width='75%';
				include('../backendphp/layout/displayastable.php');
    break;


    case 'addlists':
        if (!allowedToOpen(6506,'1rtc')) { echo 'No permission'; exit();}
     require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    
    $sql='';
    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
    $sql='INSERT INTO `hr_2casemonitoringmain` SET BranchNo='.$branchno.',IDNo='.$idno.',EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  TimeStamp=Now()';

    $stmt=$link->prepare($sql); $stmt->execute();
    header('Location: casemonitoring.php');
	
break;

case 'EditSpecifics':
    if (!allowedToOpen(6506,'1rtc')) { echo 'No permission'; exit();}
    $title='Edit Specifics';
    $txnid=intval($_GET['TxnID']);
    $sql=$sql.' WHERE s.TxnID='.$txnid;
    $columnstoedit=$columnstoadd;
        
    $columnswithlists=array('Branch','PersonInvolved');
    $listsname=array('Branch'=>'branchlist','PersonInvolved'=>'idnolist');
    
    $columnnames=$columnnameslist;

    $editprocess='casemonitoring.php?w=editlists&TxnID='.$txnid;
    
    include('../backendphp/layout/editspecificsforlists.php');
 
break;


case 'editlists':
    if (!allowedToOpen(6506,'1rtc')) { echo 'No permission'; exit();}
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid = intval($_GET['TxnID']);
    $sql='';
    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
    $sql='UPDATE `hr_2casemonitoringmain` SET BranchNo='.$branchno.',IDNo='.$idno.',EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  TimeStamp=Now() WHERE TxnID='.$txnid;
    $stmt=$link->prepare($sql); $stmt->execute();
    header('Location: casemonitoring.php');
    
break;


case 'deletelists':
    if (!allowedToOpen(6506,'1rtc')) { echo 'No permission'; exit();}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $txnid = intval($_GET['TxnID']);       
        $sql='DELETE FROM `hr_2casemonitoringmain` WHERE TxnID='.$txnid.' AND EncodedByNo='.$_SESSION['(ak0)'].' AND TxnID NOT IN (SELECT DISTINCT(TxnID) FROM hr_2casemonitoringsub WHERE TxnID='.$txnid.')';
        $stmt=$link->prepare($sql);
        $stmt->execute();
        header("Location:casemonitoring.php?w=lists");
break;


		case 'Lookup':
		
	$txnid=intval($_GET['TxnID']);
	$sql.=' WHERE s.TxnID='.$txnid;
    
	$stmt=$link->query($sql); $result=$stmt->fetch();

	$title='Status of Case';
	echo '<title>'.$title.'</title>';
    echo '<div style="margin-left:20%;">';
	echo '<br><br><h3>'.$title.'</h3>';
	echo '<form action="casemonitoring.php?w=AddDelPersonInvolved&TxnID='.$txnid.'" method="POST">PersonInvolved: <input type="text" name="PersonInvolved" list="idnolist" size="15"> &nbsp; Action: <select name="Action"><option type="Add">Add</option><option type="Del">Delete</option></select> &nbsp; <input type="submit" name="btnAdd" value="Add/Del"></form>';
	echo '<div style="border:1px solid black;background-color:#fff;width:30%;padding:5px;">';
    echo 'Branch: '.$result['Branch'].'<br>';
	echo 'Person Involved:<br> <div style="margin-left:5%;color:maroon;">'.$result['PersonInvolved'].'</div>';
	echo 'Date: '.$result['Date'].'<br>';
	echo 'Details:<br>&nbsp; &nbsp; &nbsp; '.$result['Details'].'<br>';
	echo '</div>';
	
	
	$sqlis='SELECT CID,CStatus FROM hr_0casestatus;';
	$stmtis=$link->query($sqlis); $resultis=$stmtis->fetchAll();
	$optionis='';
	foreach($resultis AS $res){
		$optionis.='<option value="'.$res['CID'].'">'.$res['CStatus'].'</option>';
	}
	if (allowedToOpen(6506,'1rtc')) { 
	echo '<br><b>Encode Status</b>';
	echo '<form action="casemonitoring.php?TxnID='.$txnid.'&w=EncodeStatus&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off>
	Status: <select name="CID"><option>-- Select --</option>'.$optionis.'</select> Remarks: <input type="text" name="Remarks" placeholder="remarks" size="33" required><br>Date of Next Update: <input type="date" name="DateOfNextUpdate" value="'.date('Y-m-d').'"> <input type="submit" name="btnSubmit" value="Encode Status">
	</form>';
	}

	$sql='SELECT TxnSubID AS TxnID,DateOfNextUpdate,Remarks,CStatus AS `Status`,CONCAT(Nickname," ",SurName) AS EncodedBy,ebs.TimeStamp FROM hr_2casemonitoringsub `ebs` JOIN hr_0casestatus istat ON ebs.CID=istat.CID LEFT JOIN 1employees e ON ebs.EncodedByNo=e.IDNo WHERE TxnID='.$txnid.' ORDER BY DateOfNextUpdate DESC;';


	$delprocess='casemonitoring.php?w=DeleteStat&TxnID='.$txnid.'&TxnSubID='; $delprocesslabel='Delete'; 
		
				
	$columnnames=array('Status','Remarks','DateOfNextUpdate','EncodedBy','TimeStamp');   
	$title=''; $formdesc=''; $txnidname='TxnID';


	include('../backendphp/layout/displayastablenosort.php');
    echo '</div>';
	break;

	case 'AddDelPersonInvolved':
		$txnid=intval($_GET['TxnID']);
		if($_POST['Action']=='Add'){
			$sql='UPDATE hr_2casemonitoringmain SET IDNo=CONCAT(IDNo,",'.$idno.'") WHERE TxnID='.$txnid;
		} else {
			$sql='SELECT TxnID, IDNo FROM `hr_2casemonitoringmain` WHERE TxnID='.$txnid;
			$stmt=$link->query($sql); $res=$stmt->fetch();
				$arr = array_diff(explode(",",$res['IDNo']),array($idno));
				
				$sql='UPDATE hr_2casemonitoringmain SET IDNo='.(!empty($arr)?"'".implode(',',$arr)."'":'NULL').' WHERE TxnID='.$txnid;
				// echo $sql; exit();
		}

		$stmt=$link->prepare($sql);
		$stmt->execute();

		header("Location:casemonitoring.php?w=Lookup&TxnID=".$txnid."");

	break;

	case 'DeleteStat':
		if (!allowedToOpen(6506,'1rtc')) { echo 'No Permissions'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnsubid = intval($_GET['TxnSubID']);		
		$sql='DELETE FROM `hr_2casemonitoringsub` WHERE TxnSubID='.$txnsubid;
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:casemonitoring.php?w=Lookup&TxnID=".$_GET['TxnID']."");
	break;

    

	case 'EncodeStatus':
		if (!allowedToOpen(6506,'1rtc')) { echo 'No Permissions'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid=intval($_GET['TxnID']);
		$sqlc='SELECT TxnSubID FROM hr_2casemonitoringsub WHERE CID='.$_POST['CID'].' AND TxnID='.$txnid.';';
		
		$stmtc=$link->query($sqlc);
	

		if($stmtc->rowCount()==0){
			$sql='INSERT INTO `hr_2casemonitoringsub` SET EncodedByNo='.$_SESSION['(ak0)'].', DateOfNextUpdate="'.addslashes($_POST['DateOfNextUpdate']).'",CID='.$_POST['CID'].',Remarks="'.addslashes($_POST['Remarks']).'", TxnID='.$txnid.', TimeStamp=Now()';
		} else {
			$sql='UPDATE `hr_2casemonitoringsub` SET EncodedByNo='.$_SESSION['(ak0)'].',DateOfNextUpdate="'.addslashes($_POST['DateOfNextUpdate']).'",Remarks="'.addslashes($_POST['Remarks']).'", TimeStamp=Now() WHERE CID='.$_POST['CID'].' AND TxnID='.$txnid.'';
		}
		$stmt=$link->prepare($sql); $stmt->execute();

		header('Location: casemonitoring.php?w=Lookup&TxnID='.$txnid.'');
	break;





	case 'UpdatesDueNEXTWeek':
    case 'UpdatesDueTHISWeek':
		if (!allowedToOpen(6506,'1rtc')) { echo 'No Permissions'; exit(); }

		$sql='SELECT ebm.TxnID,Branch,CONCAT(NickName," ",SurName) AS PersonInvolved,Details,CStatus AS StatusToday FROM hr_2casemonitoringmain ebm JOIN hr_2casemonitoringsub ebs ON ebm.TxnID=ebs.TxnID JOIN hr_0casestatus estat ON ebs.CID=estat.CID  JOIN 1employees e ON ebm.IDNo=e.IDNo JOIN 1branches b ON ebm.BranchNo=b.BranchNo WHERE (WEEK(CURDATE())+'.($which=='UpdatesDueNEXTWeek'?1:0).')=WEEK(DateOfNextUpdate);';


		$columnnames=array('Branch','PersonInvolved','Details','StatusToday');   
		$title=($which=='UpdatesDueNEXTWeek'?'Updates Due NEXT Week':'Updates Due THIS Week'); $formdesc=''; $txnidname='TxnID';
		
		$addlprocess='casemonitoring.php?w=Lookup&TxnID='; $addlprocesslabel='Lookup';

		include('../backendphp/layout/displayastablenosort.php');

		break;



case 'CaseStatus':
		if (!allowedToOpen(6506,'1rtc')) { echo 'No Permissions'; exit(); }
			$title='Case Status List';

			$method='post';
			
			$formdesc=''; echo '<br>';
		
			
			$columnnames=array(
			array('field'=>'CStatus','type'=>'text','size'=>15,'required'=>true),
			);
			
			$action='casemonitoring.php?w=AddCaseStatus'; $liststoshow=array();
			
			$buttonval='Add New'; $modaltitle='Add New Case Status';
			include('../backendphp/layout/inputmainformmodal.php');
			
		
			
			$addlprocess='casemonitoring.php?w=EditSpecificsCaseStatus&CID='; $addlprocesslabel='Edit';
			$delprocess='casemonitoring.php?w=deleteCaseStatus&CID='; $delprocesslabel='Delete'; 
			
			$columnnames=$columnnameslist;   
			$formdesc=''; $txnidname='CID';
			$title='';
					
			include('../backendphp/layout/displayastablenosort.php');
break;

case 'AddCaseStatus':
	if (!allowedToOpen(6506,'1rtc')) { echo 'No permission'; exit();}
 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';

$sql='';
foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
$sql='INSERT INTO `hr_0casestatus` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  TimeStamp=Now()';

$stmt=$link->prepare($sql); $stmt->execute();
header('Location: casemonitoring.php?w=CaseStatus');

break;

case 'EditSpecificsCaseStatus':
if (!allowedToOpen(6506,'1rtc')) { echo 'No permission'; exit();}
$title='Edit Specifics';
$txnid=intval($_GET['CID']);
$sql=$sql.' WHERE CID='.$txnid;
$columnstoedit=$columnstoadd;

$columnnames=$columnnameslist;

$editprocess='casemonitoring.php?w=EditCaseStatus&CID='.$txnid;

include('../backendphp/layout/editspecificsforlists.php');

break;


case 'EditCaseStatus':
if (!allowedToOpen(6506,'1rtc')) { echo 'No permission'; exit();}
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
$txnid = intval($_GET['CID']);
$sql='';
foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
$sql='UPDATE `hr_0casestatus` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  TimeStamp=Now() WHERE CID='.$txnid;

$stmt=$link->prepare($sql); $stmt->execute();
header('Location: casemonitoring.php?w=CaseStatus');

break;

case 'deleteCaseStatus':
	if (!allowedToOpen(6506,'1rtc')) { echo 'No permission'; exit();}
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnid = intval($_GET['CID']);       
	$sql='DELETE FROM `hr_0casestatus` WHERE CID NOT IN (0,-1) AND CID='.$txnid.' AND EncodedByNo='.$_SESSION['(ak0)'].' AND CID NOT IN (SELECT DISTINCT(CID) FROM hr_2casemonitoringsub WHERE CID='.$txnid.')';
	// echo $sql; exit();
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:casemonitoring.php?w=CaseStatus");

break;


	}
	
		
		
?>