<?php
date_default_timezone_set('Asia/Manila');
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6229,'1rtc')){ echo 'No Permission'; exit(); }
$showbranches=false;
include_once('../switchboard/contents.php');

$mainlistid=3; //Declaration of Pre-Existing Conditions


 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');

	$link1='<a id="link" href="preexistingconditions.php?w=Checking">Entry Form</a>';
if(allowedToOpen(array(6224,6225,6227,6226,6228,6110),'1rtc')){
	$which=(!isset($_GET['w'])?'Reports':$_GET['w']);
?>
<br><div id="section" style="display: block;">

    <div><?php echo $link1; ?>
        <a id='link' href="preexistingconditions.php?w=Reports">Declaration of Pre-Existing Conditions Summary</a>
		<a id="link" href="preexistingconditions.php?w=NoDeclarationList">Pending Declaration</a>
    

<?php
} else {
	echo '<div>'.$link1.'
   ';
	$which=(!isset($_GET['w'])?'Checking':$_GET['w']);
}

echo '</div><br/><br/>';
$forminfo='';



if (in_array($which,array('Form','Form1'))){
	
	//check if self
	$sqlc='SELECT IDNoOrBranchNo,DateAnswered,FullName,IF(cp.deptid IN (10),Branch,dept) AS `Branch/Dept` FROM systools_2clresults r JOIN attend_30currentpositions cp ON r.IDNoOrBranchNo=cp.IDNo WHERE TxnID='.intval($_GET['clTxnID']);
	$stmtc = $link->query($sqlc);
	$rowc=$stmtc->fetch();
	
	
	$forminfo='';
	$txnid=$mainlistid; //set
	$sql='SELECT * FROM systools_2clmain WHERE TxnID='.$txnid;
	$stmt = $link->query($sql);
	$row=$stmt->fetch();
	
	$title=$row['Title'];
	$agreement=$row['Agreement'];
	echo '<title>'.$title.'</title>';
	
	if($which=='Form'){
		$btninput='btnSubmit';
		$btnval='Submit';
	} else {
		$btninput='btnSubmit1';
		$btnval='Update';
	}
	echo '<script>
    function changeBodyBg(color){
        document.body.style.background = color;
		document.getElementById("btnHide").style.visibility="hidden";
		document.getElementById("'.$btninput.'").style.display = "block";
		document.getElementById("areyousure").style.display = "block";
		document.getElementById("areyousure").style.background = "orange";
    }
	</script>';
	
	$areyousuremsg='<div id="areyousure" style="display:none;text-align:center;padding:5px;"><h1>Are You Sure?</h1></div><br>';
	$hidebtn='<tr><td colspan=2 align="center"><button type="button" id="btnHide" style="background-color:blue;color:white;padding:5px;width:200px;border-radius:15px;" onclick="changeBodyBg(\'black\');">'.$btnval.'</button></td></tr>';
}


if (in_array($which,array('List','Reports','NoDeclarationList'))){
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$withbranchesselect=''; $maincon='AND IDNoOrBranchNo='.$_SESSION['(ak0)'].'';
		if(allowedToOpen(6227,'1rtc')){
			$withbranchesselect=' OR (deptid=10 AND LatestSupervisorIDNo='.$_SESSION['(ak0)'].')';
		}
		$posin='';
		if(allowedToOpen(6225,'1rtc')){
			$stmtposids=$link->query('SELECT GROUP_CONCAT(DISTINCT(PositionID)) AS PositionIDs FROM attend_30currentpositions WHERE deptheadpositionid='.$_SESSION['&pos'].''); $resposids=$stmtposids->fetch();
			
			$posin=' OR PositionID IN ('.$resposids['PositionIDs'].')';
				
			$maincon='AND cp.PositionID IN ('.$resposids['PositionIDs'].')';
		}
		echo comboBox($link,'SELECT FullName,IDNo FROM `attend_30currentpositions` WHERE deptid = (SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].') '.$posin.' '.$withbranchesselect.' ORDER BY FullName;','FullName','IDNo','employees');
		
		if(allowedToOpen(array(6224,6227),'1rtc')){
			$maincon='AND (LatestSupervisorIDNo='.$_SESSION['(ak0)'].' OR IDNoOrBranchNo='.$_SESSION['(ak0)'].')';
		}
		
		if (allowedToOpen(6110,'1rtc')){ //ops liaison
           $stmt0=$link->query('SELECT deptid FROM `attend_30currentpositions` WHERE IDNo='.$_SESSION['(ak0)']);
           $res0=$stmt0->fetch();
		   
		   $maincon='AND deptid IN ('.(($res0['deptid']==70)?'70,10':$res0['deptid']).')';
			
		}
		
		if(allowedToOpen(array(6226,6228),'1rtc')){ //viewer
			$maincon='';
		}
}

switch ($which)
{
	case 'Checking':
	
	$sqlhealthmain='SELECT TxnID,QuestionsScoresArray FROM systools_2clresults WHERE IDNoOrBranchNo='.$_SESSION['(ak0)'].' AND CTxnID='.$mainlistid.';';
	
				// echo $sqlhealthmain; exit();			
	 $stmtdailyhealth=$link->query($sqlhealthmain);
	 $resdailyhealth=$stmtdailyhealth->fetch();
	 
	 
	if($stmtdailyhealth->rowCount()>0){
		
		
	} else { //CTxnID = Daily Health Check Form
		$sql='INSERT INTO systools_2clresults SET IDNoOrBranchNo='.$_SESSION['(ak0)'].',CTxnID='.$mainlistid.',DateAnswered=CURDATE(),EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now();';
		$stmt=$link->prepare($sql); $stmt->execute();
		
		
		$stmtdailyhealth=$link->query($sqlhealthmain);
		$resdailyhealth=$stmtdailyhealth->fetch(); 
	 
		
	}
	header('Location:preexistingconditions.php?w=Form1&clTxnID='.$resdailyhealth['TxnID']);
	exit();
	
	break;
	
	case 'Form':
	
	// echo $confirmstyle;
	
	$sql='SELECT s.*,IsRate,RYNMin,RYNMax FROM systools_2clsub s LEFT JOIN systools_1clrateoryn r ON s.RYNID=r.RYNID WHERE TxnID='.$txnid.' ORDER BY `OrderBy`';
	
	$stmt = $link->query($sql);
	$row=$stmt->fetchAll();
	
	echo '<br><br><div style="width:50%;border:2px solid blue;padding:10px;background-color:white;margin-left:23%;">';
	echo $areyousuremsg;
	echo $forminfo;
	
	echo '<h3 align="center">'.$title.'</h3>';
	echo '<br>';
	echo '<form action="preexistingconditions.php?w=Update&clTxnID='.$_GET['clTxnID'].'" method="POST"><table>';
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
			$answerd='<input type="radio" value="-1" name="'.$rowi['QID'].'" required>Yes <input type="radio" value="-2" name="'.$rowi['QID'].'" required>No';
		}
		
		if($rowi['IsRate']==-2){
			$answerd='<input type="text" size="6" name="'.$rowi['QID'].'">';
		}
		
		if($rowi['IsRate']==1){
			$answerd='Rate: <input type="number" name="'.$rowi['QID'].'" min="'.$rowi['RYNMin'].'" max="'.$rowi['RYNMax'].'" required>';
		}
		
		titleonly:
		
		if($rowi['QType']==3){
			$space=str_repeat('&nbsp;','12');
		}
		
		echo '<tr><td class="pad">'.$space.$rowi['OrderBy'].'. '.$rowi['Question'].'</td><td style="width:120px;text-align:right">'.$answerd.'</td></tr>';
		$space=''; $answerd=''; $enter='';
	}
	if(allowedToOpen(array(6227,6225,6224,6110),'1rtc')){
		
	} else {
		if($agreement<>''){
			echo '<tr><td colspan=2 style="font-size:10pt;"><br><hr>* '.$agreement.'</td></tr>';
		}
	}
	
	echo '<tr><td colspan=2 align="center"><input type="submit" id="btnSubmit" value="Submit" name="btnSubmit" style="background-color:blue;color:white;padding:5px;width:200px;border-radius:15px;display:none;" OnClick="return confirm(\'Are you Sure?\');"></td></tr>';
	echo $hidebtn;
	
	echo '</table></form>';
	echo '</div>';
	
	
	break;
	
	case 'Form1':
	// echo $confirmstyle;
	
	echo '<br><br><div style="width:50%;border:2px solid blue;padding:10px;background-color:white;margin-left:23%;">';
	echo $areyousuremsg;
	echo $forminfo;
	echo '<h3 align="center">'.$title.'</h3>';
	
	echo '<br>';
	echo '<form action="preexistingconditions.php?w=Update&clTxnID='.$_GET['clTxnID'].'" method="POST"><table>';
	$space=''; $answerd=''; $enter='';
	echo '<style>
	.pad {
    padding: 3px;
	}
  </style>';
  
	$sql0='CREATE TEMPORARY TABLE `ExplodedArray` (
	   `QIDarr` smallint(6) NULL,
	   `Score` VARCHAR(25) NULL
	 )';
	$stmt0=$link->prepare($sql0); $stmt0->execute();
	
	$sql2='SELECT QuestionsScoresArray,IDNoOrBranchNo,EncodedByNo FROM systools_2clresults WHERE TxnID='.intval($_GET['clTxnID']);
	$stmt2 = $link->query($sql2);
	$row2=$stmt2->fetch();
	
	$idnoorb=$row2['IDNoOrBranchNo'];
	$encby=$row2['EncodedByNo'];
	
	if($row2['QuestionsScoresArray']==NULL){
		header('Location:preexistingconditions.php?w=Form&clTxnID='.intval($_GET['clTxnID']));
		exit();
	}
	
	$arrayex=explode(",", $row2['QuestionsScoresArray']);
	foreach($arrayex as $arrex){
			$arr = explode(">", $arrex, 2);
			$qid = $arr[0];
			$score = $arr[1];
			$sql='INSERT INTO ExplodedArray SET QIDarr='.$qid.',Score="'.$score.'"';
			// echo $sql;
			$stmt=$link->prepare($sql); $stmt->execute();
	}
	
	$sql='SELECT s.*,QIDarr,Score,IsRate,RYNMin,RYNMax FROM systools_2clsub s LEFT JOIN systools_1clrateoryn r ON s.RYNID=r.RYNID LEFT JOIN ExplodedArray ea ON s.QID=ea.QIDarr WHERE TxnID='.$txnid.' ORDER BY `OrderBy`';
	
	$stmt = $link->query($sql);
	$row=$stmt->fetchAll();
	
	foreach($row AS $rowi){
		if($rowi['QType']==1){
			goto titleonly1;
		}
		
		if($rowi['IsRate']==0){
			$answerd='<input type="radio" value="-1" name="'.$rowi['QID'].'" '.($rowi['Score']==-1?'checked':'').' required>Yes <input type="radio" value="-2" name="'.$rowi['QID'].'" '.($rowi['Score']==-2?'checked':'').' required>No';
		}
		
		if($rowi['IsRate']==-2){
			$answerd='<input type="text" size="6" name="'.$rowi['QID'].'" value="'.$rowi['Score'].'" >';
		}
		
		if($rowi['IsRate']==1){
			$answerd='Rate: <input type="number" name="'.$rowi['QID'].'" min="'.$rowi['RYNMin'].'" max="'.$rowi['RYNMax'].'" value="'.$rowi['Score'].'" required>';
		}
		
		titleonly1:
		
		if($rowi['QType']==3){
			$space=str_repeat('&nbsp;','12');
		}
		
		echo '<tr><td class="pad">'.$space.$rowi['OrderBy'].'. '.$rowi['Question'].'</td><td style="width:120px;text-align:right">'.$answerd.'</td></tr>';
		$space=''; $answerd=''; $enter='';
	}
	
	
	
	
		echo '<tr><td colspan=2 align="center"><input type="submit" id="btnSubmit1" value="Update" name="btnSubmit1" style="background-color:blue;color:white;padding:5px;width:200px;border-radius:15px;display:none;"></td></tr>';
	echo $hidebtn;
	
	
	echo '</table></form>';
	echo '</div>';
	
	
	break;
	
	case 'Update':
			$txnid=intval($_GET['clTxnID']);
			$columnstoadd=$_POST;
			
			
			//get array keys
			$keys = array_keys($columnstoadd);
			// if(isset($_POST['TimeIn'])){
				// $minus=5; //hidden array timein etc
			// } else {
				$minus=2; 
			// }
			$cntend=count($columnstoadd)-$minus;
			
			$cnt=0;
			
			$qsarray=''; //$yeses=0;
			//get array name
			while($cnt<=$cntend){
				$qsarray.=$keys[$cnt].'>'.$_POST[$keys[$cnt]].',';
				$cnt++;
			}
			$qsarray=substr($qsarray, 0, -1);
			
			
			$sql='UPDATE systools_2clresults r SET EncodedByNo='.$_SESSION['(ak0)'].', QuestionsScoresArray="'.$qsarray.'", TimeStamp=Now() WHERE (EncodedByNo='.$_SESSION['(ak0)'].' OR IDNoOrBranchNo='.$_SESSION['(ak0)'].' OR '.$_SESSION['(ak0)'].'=(SELECT LatestSupervisorIDNo FROM attend_30currentpositions WHERE IDNo=r.IDNoOrBranchNo)) AND TxnID='.$txnid;
			
			$stmt=$link->prepare($sql); $stmt->execute();
			
		if(allowedToOpen(array(6224,6225,6227,6226,6228,6110),'1rtc')){
			header('Location:preexistingconditions.php?w=Reports');
		} else {
			header('Location:preexistingconditions.php?w=Form1&clTxnID='.$txnid);
		}
	break;
	
	
	case 'Reports':
	$title='Declaration of Pre-Existing Conditions';
	echo '<title>'.$title.'</title>';
	echo '<style>tr
	{
		line-height:19px;
	}
</style>';


echo '<div style="clear: both; display: block; position: relative;height:20px;"></div>';
$sqldeletetemp='DELETE FROM systools_clexplodedarraytempdata WHERE TxnID='.$mainlistid.' AND EncodedByNo='.$_SESSION['(ak0)'].'';
	$stmt0=$link->prepare($sqldeletetemp); $stmt0->execute();
	
	$sql2='SELECT QuestionsScoresArray,IDNoOrBranchNo FROM systools_2clresults WHERE QuestionsScoresArray IS NOT NULL AND CTxnID="'.$mainlistid.'"';
	// echo $sql2;
	$stmt2 = $link->query($sql2);
	$row2=$stmt2->fetchAll();
	
	foreach($row2 AS $row22){
			$arrayex=explode(",", $row22['QuestionsScoresArray']);
			foreach($arrayex as $arrex){
					$arr = explode(">", $arrex, 2);
					$qid = $arr[0];
					$score = $arr[1];
					$sql='INSERT INTO systools_clexplodedarraytempdata SET TxnID='.$mainlistid.',IDNoOrBranchNo='.$row22['IDNoOrBranchNo'].',QIDarr='.$qid.',Score="'.$score.'",EncodedByNo='.$_SESSION['(ak0)'].';';
					$stmt=$link->prepare($sql); $stmt->execute();
			}

	}
	
	
	
	$sqlqids='select QID from systools_2clsub WHERE QType<>1 AND TxnID='.$mainlistid.' ORDER BY OrderBy;';
	$stmtqids = $link->query($sqlqids);
	$rowqids=$stmtqids->fetchAll();
	
	$sqltrhead='';
	$sqltrbody=''; $thcount='1';
	foreach($rowqids AS $rowqid){
		$sqltrhead.='(SELECT Question FROM systools_2clsub WHERE QID='.$rowqid['QID'].') AS "title'.$thcount.'",' ;
		$sqltrbody.='(SELECT 
		(CASE
		WHEN Score=-1 THEN "Yes"
		WHEN Score=-2 THEN "No"
		ELSE Score
		End) AS Score
		FROM systools_clexplodedarraytempdata WHERE QIDarr='.$rowqid['QID'].' AND IDNoOrBranchNo=r.IDNoOrBranchNo AND EncodedByNo='.$_SESSION['(ak0)'].'),';
		$thcount++;
	}
	
	
	
	$sqldata='SELECT "FullName","IDNo",'.$sqltrhead.'"Branch/Dept",1 AS th UNION SELECT FullName,IDNo,'.$sqltrbody.'IF(deptid IN (10),Branch,dept) AS `Branch/Dept`,0 AS th FROM systools_2clresults r LEFT JOIN attend_30currentpositions cp ON r.IDNoOrBranchNo=cp.IDNo WHERE CTxnID='.$mainlistid.' '.$maincon.' GROUP BY IDNoOrBranchNo ORDER BY th DESC,`Branch/Dept`';
	// echo $sqldata; exit();
	$stmtdata = $link->query($sqldata);
	$rowdata=$stmtdata->fetchAll();
	
	
	echo '<div style="border:2px solid blue;background-color:white;padding:5px;width:60%;margin-left:20%;"><h3 align="center">'.$title.'</h3><br>';
	echo '<table>';
	$thstart='1';
	foreach($rowdata AS $rowdat){
		if($rowdat['th']==1){
			echo '<tr style="font-size:10pt;background-color:maroon;color:white;">';
			echo '<th style="width:190px;">'.$rowdat['FullName'].'</th>';
			echo '<th style="padding:5px;">'.$rowdat['Branch/Dept'].'</th>';
			while($thstart<$thcount){
				echo '<th style="padding:5px;">'.$rowdat['title'.$thstart.''].'</th>';
				$thstart++;
			}
			echo '</tr>';
		} else {
			$thstart=1;
			echo '<tr style="font-size:9pt;background-color:limegreen;">';
			echo '<td style="text-align:center;font-weight:bold;width:190px;background-color:gray;">'.$rowdat['FullName'].'</td>';
			echo '<td style="text-align:center;font-weight:bold;background-color:gray;">'.$rowdat['Branch/Dept'].'</td>';
			while($thstart<$thcount){
				echo '<td style="text-align:center;font-weight:bold;'.(($rowdat['title'.$thstart.'']=="Yes" OR is_numeric($rowdat['title'.$thstart.'']) AND $rowdat['title'.$thstart.'']>37.5)?'background-color:red;':'').'">'.$rowdat['title'.$thstart.''].'</td>';
				$thstart++;
			}
			echo '</tr>';
		}
		$thstart=1;
	}
	echo '</table></div>';
	
	$stmt0=$link->prepare($sqldeletetemp); $stmt0->execute();
	
	break;
	
	
	case 'NoDeclarationList':
	
	
	$sql='SELECT FullName,IF(cp.deptid IN (10),Branch,dept) AS `Branch/Dept` FROM attend_30currentpositions cp WHERE IDNo NOT IN (SELECT IDNoOrBranchNo FROM systools_2clresults WHERE CTxnID='.$mainlistid.' AND QuestionsScoresArray IS NOT NULL) '.$maincon.' ORDER BY `Branch/Dept`';
	
        $title='Pending Declaration'; 
		$columnnames=array('FullName','Branch/Dept');
		
		
		// $sql=$sqlmain;
		
		$width='30%';
        include('../backendphp/layout/displayastable.php');
	
	break;
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
