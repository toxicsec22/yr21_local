<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit();}
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

include_once('../backendphp/layout/linkstyle.php');
echo'</br>';

echo'<a id="link" href="interdeptmeeting.php">Interdepartment Meeting</a>';

echo'</br>';


	$which=(!isset($_GET['w'])?'lists':$_GET['w']);

	if (in_array($which,array('lists','EditSpecifics','Lookup','UpdatesDueNEXTWeek'))){
		$sql='SELECT s.*,(SELECT GROUP_CONCAT(DISTINCT(CONCAT(NickName," ",SurName)) SEPARATOR "<br>") FROM 1employees WHERE FIND_IN_SET(IDNo,s.IDNo)) AS PersonInvolved FROM eos_interdeptmain s';
	}

		if (in_array($which,array('lists','EditSpecifics'))){
			$columnnameslist=array('DateOfMeeting','Topic','PersonInvolved'); 
			$columnstoadd=array('DateOfMeeting','Topic');
        
            
		}
		if (in_array($which,array('lists','EditSpecifics','Lookup'))){
			echo comboBox($link,'SELECT IDNo,CONCAT(Nickname," ",SurName) AS PersonInvolved FROM 1employees e ORDER BY PersonInvolved','IDNo','PersonInvolved','idnolist');
		}
		if (in_array($which,array('addlists','editlists'))){
			$columnstoadd=array('DateOfMeeting','Topic');
           
		}

		if (in_array($which,array('addlists','AddDelPersonInvolved'))){
			$idno=comboBoxValue($link,'1employees','CONCAT(Nickname," ",SurName)',addslashes($_POST['PersonInvolved']),'IDNo'); 

		}
        


	
	switch ($which){
		case 'lists':
			if (!allowedToOpen(100,'1rtc')) { echo 'No Permissions'; exit(); }
				$title='Interdepartment Meeting';

				$method='post';
				
				$formdesc=''; echo '<br>';
			

				
				$columnnames=array(
				array('field'=>'PersonInvolved','caption'=>'Person Involved (can add other persons later [in Lookup])','list'=>'idnolist','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'DateOfMeeting','type'=>'date','size'=>20,'required'=>true),
				array('field'=>'Topic','type'=>'text','size'=>20,'required'=>true),
				);
				
				$action='interdeptmeeting.php?w=addlists'; $liststoshow=array();
				
				$buttonval='Add New'; $modaltitle='Add New Meeting';
				include('../backendphp/layout/inputmainformmodal.php');
				
				
				$editprocess='interdeptmeeting.php?w=Lookup&TxnID='; $editprocesslabel='Lookup';
				
				$addlprocess='interdeptmeeting.php?w=EditSpecifics&TxnID='; $addlprocesslabel='Edit';
				$delprocess='interdeptmeeting.php?w=deletelists&TxnID='; $delprocesslabel='Delete'; 
				
				$columnnames=$columnnameslist;   
				$formdesc=''; $txnidname='TxnID';
				$title='';
						
				$sql=$sql.' WHERE FIND_IN_SET('.$_SESSION['(ak0)'].',IDNo) ORDER BY DateOfMeeting DESC';
				$width='55%';
				include('../backendphp/layout/displayastablenosort.php');
    break;


    case 'addlists':
        if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit();}
     require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    
    $sql='';
    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
    $sql='INSERT INTO `eos_interdeptmain` SET IDNo='.$idno.',EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  TimeStamp=Now()';
    // echo $sql; exit();

    $stmt=$link->prepare($sql); $stmt->execute();
    header('Location: interdeptmeeting.php');
	
break;

case 'EditSpecifics':
    if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit();}
    $title='Edit Specifics';
    $txnid=intval($_GET['TxnID']);
    $sql=$sql.' WHERE s.TxnID='.$txnid;
    $columnstoedit=$columnstoadd;
        
    $columnswithlists=array('Branch','PersonInvolved');
    $listsname=array('Branch'=>'branchlist','PersonInvolved'=>'idnolist');
    
    $columnnames=$columnnameslist;

    $editprocess='interdeptmeeting.php?w=editlists&TxnID='.$txnid;
    
    include('../backendphp/layout/editspecificsforlists.php');
 
break;


case 'editlists':
    if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit();}
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid = intval($_GET['TxnID']);
    $sql='';
    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
    $sql='UPDATE `eos_interdeptmain` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  TimeStamp=Now() WHERE TxnID='.$txnid;

    // echo $sql; exit();
    $stmt=$link->prepare($sql); $stmt->execute();
    header('Location: interdeptmeeting.php');
    
break;


case 'deletelists':
    if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit();}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $txnid = intval($_GET['TxnID']);       
        $sql='DELETE FROM `eos_interdeptmain` WHERE TxnID='.$txnid.' AND EncodedByNo='.$_SESSION['(ak0)'].' AND DateOfMeeting>CURDATE()';
        $stmt=$link->prepare($sql);
        $stmt->execute();
        header("Location:interdeptmeeting.php?w=lists");
break;


		case 'Lookup':
		
	$txnid=intval($_GET['TxnID']);
	$sql.=' WHERE FIND_IN_SET('.$_SESSION['(ak0)'].',IDNo) AND s.TxnID='.$txnid;
    
	$stmt=$link->query($sql); $result=$stmt->fetch();

    if($stmt->rowCount()==0){
        echo '<br>Not allowed to view this meeting'; exit();
    }

	$title='Meeting Details';
	echo '<title>'.$title.'</title>';
    echo '<div style="margin-left:25%;">';
	echo '<br><br><h3>'.$title.'</h3>';
	echo '<form action="interdeptmeeting.php?w=AddDelPersonInvolved&TxnID='.$txnid.'" method="POST">Person Involved: <input type="text" name="PersonInvolved" list="idnolist" size="15"> &nbsp; Action: <select name="Action"><option type="Add">Add</option><option type="Del">Delete</option></select> &nbsp; <input type="submit" name="btnAdd" value="Add/Del"></form>';
	echo '<div style="border:1px solid black;background-color:#fff;width:30%;padding:5px;">';
    echo 'Person Involved:<br> <div style="margin-left:5%;color:maroon;">'.$result['PersonInvolved'].'</div>';
	echo 'Date Of Meeting: '.$result['DateOfMeeting'].'<br>';
	echo 'Topic:<br>&nbsp; &nbsp; &nbsp; '.$result['Topic'].'<br>';
	echo '</div>';
	
	

	if (allowedToOpen(100,'1rtc')) { 
	echo '<br><b>Encode Issue</b>';
	echo '<form action="interdeptmeeting.php?TxnID='.$txnid.'&w=EncodeIssue&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off>
	Issue: <input type="text" name="Issue" placeholder="Issue" size="33" required> <input type="submit" name="btnSubmit" value="Encode Issue">
	</form>';
	}

	$sql='SELECT TxnSubID,Issue,Resolution,CONCAT(Nickname," ",SurName) AS EncodedBy,ebs.TimeStamp FROM eos_interdeptsub `ebs` LEFT JOIN 1employees e ON ebs.EncodedByNo=e.IDNo WHERE TxnID='.$txnid.';';

	$delprocess='interdeptmeeting.php?w=DeleteIssue&TxnID='.$txnid.'&TxnSubID='; $delprocesslabel='Delete'; 
		
				
	$columnnames=array('Issue','Resolution','EncodedBy','TimeStamp');   
	$title=''; $formdesc=''; $txnidname='TxnSubID';

    $columnstoedit=array('Resolution');
	$editprocess='interdeptmeeting.php?w=EnterResolution&TxnID='.$txnid.'&TxnSubID='; $editprocesslabel='Enter';
	include_once('../backendphp/layout/displayastableeditcells.php');


    echo '</div>';
	break;

	case 'AddDelPersonInvolved':
		$txnid=intval($_GET['TxnID']);
		if($_POST['Action']=='Add'){
			$sql='UPDATE eos_interdeptmain SET IDNo=CONCAT(IDNo,",'.$idno.'") WHERE TxnID='.$txnid;
		} else {
			$sql='SELECT TxnID, IDNo FROM `eos_interdeptmain` WHERE TxnID='.$txnid;
			$stmt=$link->query($sql); $res=$stmt->fetch();
				$arr = array_diff(explode(",",$res['IDNo']),array($idno));
				$sql='UPDATE eos_interdeptmain SET IDNo='.(!empty($arr)?"'".implode(',',$arr)."'":'NULL').' WHERE TxnID='.$txnid;
		}

		$stmt=$link->prepare($sql);
		$stmt->execute();

		header("Location:interdeptmeeting.php?w=Lookup&TxnID=".$txnid."");

	break;

	case 'DeleteIssue':
		if (!allowedToOpen(100,'1rtc')) { echo 'No Permissions'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnsubid = intval($_GET['TxnSubID']);		
		$sql='DELETE FROM `eos_interdeptsub` WHERE EncodedByNo='.$_SESSION['(ak0)'].' AND TxnSubID='.$txnsubid;
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:interdeptmeeting.php?w=Lookup&TxnID=".$_GET['TxnID']."");
	break;

    

	case 'EncodeIssue':
		if (!allowedToOpen(100,'1rtc')) { echo 'No Permissions'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid=intval($_GET['TxnID']);
		
	

		$sql='INSERT INTO `eos_interdeptsub` SET EncodedByNo='.$_SESSION['(ak0)'].', Issue="'.addslashes($_POST['Issue']).'", TxnID='.$txnid.', TimeStamp=Now()';
		
		$stmt=$link->prepare($sql); $stmt->execute();

		header('Location: interdeptmeeting.php?w=Lookup&TxnID='.$txnid.'');
	break;



        case 'EnterResolution':
        if (!allowedToOpen(100,'1rtc')) { echo 'No Permissions'; exit(); }
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';

            $sql='UPDATE `eos_interdeptsub` SET Resolution="'.addslashes($_POST['Resolution']).'", TimeStamp=Now() WHERE TxnSubID='.$_GET['TxnSubID'].' AND EncodedByNo='.$_SESSION['(ak0)'].'';


            $stmt=$link->prepare($sql); $stmt->execute();

        header('Location: interdeptmeeting.php?w=Lookup&TxnID='.$_GET['TxnID'].'');

        break;


	}
	
?>