<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(2206,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');



 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');
?>
<br><div id="section" style="display: block;">

    <div>
	<a id='link' href="checklist.php?w=RateSettings">Set Rate</a>
	<a id='link' href="checklist.php?w=List">Main List</a>
    </div><br/><br/>


<?php


$which=(!isset($_GET['w'])?'List':$_GET['w']);


if (in_array($which,array('RateSettings','EditSpecificsRate'))){
   $sql='SELECT *,
(CASE 
WHEN IsRate=1 THEN "Nth to Nth"
WHEN IsRate=0 THEN "Yes or No"
WHEN IsRate=-2 THEN "Text"
ELSE "N/A"
END)
	   AS Description, RYNID AS TxnID FROM systools_1clrateoryn';
   echo comboBox($link,'SELECT 1 AS IsRateID,"Nth to Nth" AS Description UNION SELECT 0,"Yes or No"','Description','IsRateID','isratelist');
  
   $columnnameslist=array('IsRate','Description','RYNMin','RYNMax');
}


if (in_array($which,array('AddRate','EditSpecificsRate','EditRate'))){
   $columnstoadd=array('IsRate','RYNMin','RYNMax');
}



if (in_array($which,array('List','EditSpecificsList'))){
   $sql='SELECT m.*,IF(deptid=-1,"All Dept",(SELECT department FROM 1departments WHERE deptid=m.deptid)) AS Department FROM systools_2clmain m';
   
  echo comboBox($link,'SELECT "-1" AS deptid,"All Dept" AS Department UNION SELECT deptid,department FROM 1departments','Department','deptid','deptlist');
	
   $columnnameslist=array('Title','Department','Agreement','FwdNxtYr');
}


if (in_array($which,array('AddList','EditSpecificsList','EditList'))){
   $columnstoadd=array('Title','deptid','Agreement','FwdNxtYr');
}

if (in_array($which,array('Lookup','EditQuestion'))){
   $sqlr='SELECT RYNID,
	(CASE
	WHEN IsRate=1 THEN CONCAT(RYNMin," - ",RYNMax)
	WHEN IsRate=0 THEN "Yes/No"
	WHEN IsRate=-2 THEN "Text"
	ELSE "N/A"
	END) AS Description 
	
	FROM systools_1clrateoryn ';
	$stmtr = $link->query($sqlr);
	$rowr=$stmtr->fetchAll();
}




switch ($which)
{
	case 'RateSettings':
	if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
		$title='Rates';
		$sqldatacheck='SELECT COUNT(RYNID) AS cnt FROM systools_1clrateoryn WHERE IsRate=0 AND (RYNMin<>-1 OR RYNMax<>-2)';
	$stmtdatacheck = $link->query($sqldatacheck);
	$rowdatacheck=$stmtdatacheck->fetch();
	
	$addlformdesc='';
	if ($rowdatacheck['cnt']>0){
		$addlformdesc='<br><br><font color="red">There\'s an error in Min/Max. Error Count: '.$rowdatacheck['cnt'].'</font>';
	}
	
		
                
                $formdesc='</i><br><b>IsRate:</b> <br>&nbsp; &nbsp; &nbsp;<b>1</b> = To be answered by <b>Nth</b> to <b>Nth</b> (Set Min, Set Max)<br> &nbsp; &nbsp; &nbsp;<b>0</b> = To be answered <b>Yes</b> or <b>No</b> (Set RYNMin to -1, Set RYNMax to -2)'.$addlformdesc.'<i>';
		$method='post';
				$columnnames=array(
				array('field'=>'IsRate','type'=>'text','size'=>10,'required'=>true,'list'=>'isratelist'),
				array('field'=>'RYNMin','type'=>'text','size'=>10,'required'=>true),
				array('field'=>'RYNMax','type'=>'text','size'=>10,'required'=>true)
				);
							
		$action='checklist.php?w=AddRate'; $fieldsinrow=4; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');
		
		
		$delprocess='checklist.php?w=DeleteRate&RYNID=';
				
		$title=''; $formdesc=''; 
                
                $txnid='TxnID';
		$columnnames=$columnnameslist;       
                if (allowedToOpen(2206,'1rtc')){ $editprocess='checklist.php?w=EditSpecificsRate&RYNID='; $editprocesslabel='Edit';}
		echo '<div style="width:45%">';
		include('../backendphp/layout/displayastable.php'); 
		echo '</div>';
	break;
	
	
	
	case 'AddRate':
		if (allowedToOpen(2206,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='';
			foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
			
			$sql='INSERT INTO systools_1clrateoryn SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now();';
			
			$stmt=$link->prepare($sql); $stmt->execute();
		
			
		}
		header('Location:checklist.php?w=RateSettings');
	break;
	
	
    case 'EditSpecificsRate':
                if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
		$title='Edit Specifics';
		$txnid=intval($_GET['RYNID']);
		
		$columnswithlists=array('IsRate');
		$listsname=array('IsRate'=>'isratelist');
		
		$sql=$sql.' WHERE RYNID='.$txnid;
		$columnstoedit=$columnstoadd;		
		$columnnames=$columnnameslist;
		
		$editprocess='checklist.php?w=EditRate&RYNID='.$txnid;		
		include('../backendphp/layout/editspecificsforlists.php');
	break;
	
	
	
    case 'EditRate':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `systools_1clrateoryn` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE RYNID='.intval($_GET['RYNID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
		header("Location:checklist.php?w=RateSettings");
    break;
	
	
    case 'DeleteRate':
        if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM `systools_1clrateoryn` WHERE RYNID='.intval($_GET['RYNID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		
        header("Location:checklist.php?w=RateSettings");
    break; 


case 'List':
	
		$title='Main Checklist';
		
                if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
                $formdesc='</i><i>';
		$method='post';
				$columnnames=array(
				array('field'=>'Title','type'=>'text','size'=>20,'required'=>true),
				array('field'=>'deptid','type'=>'text','size'=>10,'required'=>true,'list'=>'deptlist'),
				array('field'=>'Agreement','caption'=>'Agreement (Leave blank if no agreement)','type'=>'text','size'=>10),
				array('field'=>'FwdNxtYr','caption'=>'Forward Nxt Year? (0 - no, 1 - yes)','type'=>'text','size'=>10,'value'=>'0')
				);
							
		$action='checklist.php?w=AddList'; $fieldsinrow=4; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');
		
		
		$delprocess='checklist.php?w=DeleteList&TxnID=';
				
		$title=''; $formdesc=''; 
                
                $txnid='TxnID';
		$columnnames=$columnnameslist;       
                $editprocess='checklist.php?w=EditSpecificsList&TxnID='; $editprocesslabel='Edit';
				$addlprocess='checklist.php?w=Lookup&TxnID=';
		$addlprocesslabel='Look Up';
		echo '<div style="width:100%">';
		include('../backendphp/layout/displayastable.php'); 
		echo '</div>';
	break;
	
	
	case 'AddList':
		if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='';
			foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
			
			$sql='INSERT INTO systools_2clmain SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now();';
			
			$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:checklist.php?w=List');
	break;

	
    case 'EditSpecificsList':
                if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);
		
		$columnswithlists=array('deptid');
		$listsname=array('deptid'=>'deptlist');
		
		$sql=$sql.' WHERE TxnID='.$txnid;
		$columnstoedit=$columnstoadd;		
		$columnnames=$columnnameslist;
		
		$editprocess='checklist.php?w=EditList&TxnID='.$txnid;		
		include('../backendphp/layout/editspecificsforlists.php');
	break;
	
	case 'EditList':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `systools_2clmain` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE TxnID='.intval($_GET['TxnID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
		header("Location:checklist.php?w=List");
    break;
	
	
    case 'DeleteList':
        if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM `systools_2clmain` WHERE TxnID='.intval($_GET['TxnID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		
        header("Location:checklist.php?w=List");
    break; 
	
	
	case 'Lookup':
	$txnid=$_GET['TxnID'];
	$sql='SELECT * FROM systools_2clmain WHERE TxnID='.$txnid;
	$stmt = $link->query($sql);
	$row=$stmt->fetch();
	$agreement=$row['Agreement'];
	$title=$row['Title'];
	
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	
	
	$ryn='';
	foreach($rowr AS $rowryn){
		$ryn.='<option value="'.$rowryn['RYNID'].'">'.$rowryn['Description'].'</option>';
	}
	
	echo '<form action="checklist.php?w=AddQ&TxnID='.$txnid.'" method="POST">
	<br>QType: <select name="QType"><option value="1">Title Only</option><option value="2">Title With Rate</option><option value="3">As Sub</option></select> Rate/YesOrNo: <select name="RYNID">'.$ryn.'</select>
	 Question: <input type="text" name="Question" placeholder="Question" size="50">
	 Order By: <input type="text" name="OrderBy" placeholder="1" size="10" required>
	<input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">
	<input type="submit" value="Add Question" name="btnAddQuestion">
	</form>';
	
	// $sql='SELECT s.*,IsRate,RYNMin,RYNMax FROM systools_2clsub s LEFT JOIN systools_1clrateoryn r ON s.RYNID=r.RYNID WHERE TxnID='.$txnid.' ORDER BY `OrderBy`';
	$sql='SELECT s.*,IsRate,RYNMin,RYNMax FROM systools_2clsub s LEFT JOIN systools_1clrateoryn r ON s.RYNID=r.RYNID WHERE TxnID='.$txnid.' ORDER BY INET_ATON(CONCAT(OrderBy,".0.0"))';
	// INET_ATON Convert to byte
	$stmt = $link->query($sql);
	$row=$stmt->fetchAll();
	
	echo '<br><br><h4>Form Preview</h4><div style="width:60%;border:2px solid blue;padding:10px;background-color:white;">';
	echo '<h3 align="center">'.$title.'</h3>';
	echo '<br>';
	echo '<table>';
	$space=''; $answerd=''; $enter='';
	echo '<style>
	.pad {
    padding: 3px;
	}
	
  </style>';
	foreach($row AS $rowi){
		if($rowi['QType']==1){
			goto titleonly;
		}
		
		if($rowi['IsRate']==0){
			$answerd='<input type="radio">Yes <input type="radio">No';
		}
		
		if($rowi['IsRate']==-2){
			$answerd='<input type="text" size="6">';
		}
		
		if($rowi['IsRate']==1){
			$answerd='Rate: <input type="number" min="'.$rowi['RYNMin'].'" max="'.$rowi['RYNMax'].'">';
		}
		$enter='<input type="submit" value="Enter">';
		
		titleonly:
		
		
		echo '<tr><td class="pad"><div style="margin-left:'.($rowi['QType']==3?'30px;':'0px').'">'.$space.$rowi['OrderBy'].'. '.$rowi['Question'].'</div></td><td style="width:120px;text-align:right">'.$answerd.'</td><td style="width:60px;text-align:right;"><font style="text-decoration:none;font-size:9pt;"><a href="checklist.php?w=EditQuestion&QID='.$rowi['QID'].'">Edit</a> <a href="checklist.php?w=DeleteQuestion&TxnID='.$rowi['TxnID'].'&QID='.$rowi['QID'].'&action_token='.$_SESSION['action_token'].'" OnClick="return confirm(\'Really delete this?\');">Delete</a></font></td></tr>';
		$space=''; $answerd=''; $enter='';
	}
	if($agreement<>''){
		echo '<tr><td colspan=2 style="font-size:10pt;"><br><hr>* '.$agreement.'</td></tr>';
	}
	echo '</table>';
	echo '</div>';
	
	
	break;
	
	
	case 'AddQ':
		if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$txnid=intval($_GET['TxnID']);
			$sql='';
			$columnstoadd=array('QType','RYNID','Question','OrderBy');
			foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
			
			$sql='INSERT INTO systools_2clsub SET TxnID='.$txnid.',EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now();';
			
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:checklist.php?w=Lookup&TxnID='.$txnid);
	break;
	
	case 'EditQuestion':
	
	$qid=$_GET['QID'];
	$sql='SELECT * FROM systools_2clsub WHERE QID='.$qid;
	$stmt = $link->query($sql);
	$row=$stmt->fetch();
	
	$title='Edit Question';
	
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3>';
	
	
	$ryn='';
	foreach($rowr AS $rowryn){
		$ryn.='<option value="'.$rowryn['RYNID'].'" '.($row['RYNID']==$rowryn['RYNID']?'selected':'').'>'.$rowryn['Description'].'</option>';
	}
	
	echo '<form action="checklist.php?w=EditQuestionProcess&TxnID='.$row['TxnID'].'&QID='.$qid.'" method="POST">
	<br>QType: <select name="QType"><option value="1" '.($row['QType']==1?'selected':'').'>Title Only</option><option value="2" '.($row['QType']==2?'selected':'').'>Title With Rate</option><option value="3" '.($row['QType']==3?'selected':'').'>As Sub</option></select> Rate/YesOrNo: <select name="RYNID">'.$ryn.'</select>
	 Question: <input type="text" name="Question" placeholder="Question" value="'.$row['Question'].'" size="50">
	 Order By: <input type="text" name="OrderBy" placeholder="1" value="'.$row['OrderBy'].'" size="10" required>
	<input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">
	<input type="submit" value="Edit Question" name="btnEditQuestion">
	</form>';
	
	
	break;
	
	
	case 'EditQuestionProcess':
		if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$qid=intval($_GET['QID']);
			$txnid=intval($_GET['TxnID']);
			$sql='';
			$columnstoadd=array('QType','RYNID','Question','OrderBy');
			foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
			
			$sql='UPDATE systools_2clsub SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE QID='.$qid;
			
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:checklist.php?w=Lookup&TxnID='.$txnid);
	break;
	
	
	case 'DeleteQuestion':
        if (!allowedToOpen(2206,'1rtc')){ echo 'No Permission'; exit(); }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM `systools_2clsub` WHERE QID='.intval($_GET['QID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		
        header('Location:checklist.php?w=Lookup&TxnID='.intval($_GET['TxnID']));
    break; 
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
