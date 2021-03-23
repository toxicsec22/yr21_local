<?php
date_default_timezone_set('Asia/Manila');
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 

$showbranches=false;
include_once('../switchboard/contents.php');

$mainlistid=1; //daily health check form manually set

 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');

	$link1='<a id="link" href="dailyhealthcheckform.php?w=List">Health Check Lists</a>';
if(allowedToOpen(array(6224,6225,6227,6226,6228,6110),'1rtc')){
	$which=(!isset($_GET['w'])?'Reports':$_GET['w']);
?>
<br><div id="section" style="display: block;">

    <div><?php echo $link1; ?>
        <a id='link' href="dailyhealthcheckform.php?w=Reports">Daily Health Check Summary</a>
    </div><br/><br/>

<?php
} else {
	echo '<div>'.$link1.'
	 <a id="link" href="dailyhealthcheckform.php?w=ReportsPerPerson&IDNo='.$_SESSION['(ak0)'].'">Health Check Summary</a>
    </div><br/><br/>';
	$which=(!isset($_GET['w'])?'ReportsPerPerson':$_GET['w']);
}
$forminfo='';
if (in_array($which,array('Form','Form1'))){
	//check if self
	$sqlc='SELECT IDNoOrBranchNo,DateAnswered,FullName,IF(cp.deptid IN (10),Branch,dept) AS `Branch/Dept` FROM systools_2clresults r JOIN attend_30currentpositions cp ON r.IDNoOrBranchNo=cp.IDNo WHERE TxnID='.intval($_GET['clTxnID']);
	$stmtc = $link->query($sqlc);
	$rowc=$stmtc->fetch();
	
	if($rowc['IDNoOrBranchNo']<>$_SESSION['(ak0)']){
		$forminfo='<a href="dailyhealthcheckform.php">Back to list</a><br><br><b>Date: '.$rowc['DateAnswered'].'<br>FullName: '.$rowc['FullName'].'<br>Branch/Dept: '.$rowc['Branch/Dept'].'</b>';
	} else {
		$forminfo='<b>Date: '.date('Y-m-d').'<br>IDNo: '.$_SESSION['(ak0)'].'</b>';
	}
	
	$txnid=$mainlistid; //set
	$sql='SELECT * FROM systools_2clmain WHERE TxnID='.$txnid;
	$stmt = $link->query($sql);
	$row=$stmt->fetch();
	
	$agreement=$row['Agreement'];
	
	$title=$row['Title'];
	
	echo '<title>'.$title.'</title>';
}
if (in_array($which,array('List','Reports'))){
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
			$maincon='AND LatestSupervisorIDNo='.$_SESSION['(ak0)'].'';
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
if (in_array($which,array('ReportsPerPerson','Reports'))){
	$sqldeletetemp='DELETE FROM systools_clexplodedarraytempdata WHERE TxnID='.$mainlistid.' AND EncodedByNo='.$_SESSION['(ak0)'].'';
}
switch ($which)
{
	case 'List':
	
	if(allowedToOpen(array(6224,6225,6227,6226,6228,6110),'1rtc')){	
		echo '<form action="dailyhealthcheckform.php?w=Add" method="POST">FullName: <input type="text" name="IDNo" list="employees"> Date: <input type="date" name="Date" value="'.date('Y-m-d').'"> <input type="submit" value="Add" name="btnAdd" style="padding:2px;"></form>';
		$formdesc='<br></i><form action="#" method="POST">Date: <input type="date" value="'.date('Y-m-d').'" name="DateAnswered"> <input type="submit" name="btnLookupDate" value="Lookup"></form><i>';
		
		if(isset($_POST['btnLookupDate'])){
			$condidate='AND DateAnswered="'.$_POST['DateAnswered'].'"';
		} else {
			$condidate='AND DateAnswered="'.date('Y-m-d').'"';
		}
	} else {
		$condidate='';
	}
	$sqlmain='SELECT r.*,FullName,IDNoOrBranchNo AS IDNo,IF(QuestionsScoresArray IS NULL,"No","Yes") AS `Answered?`,IF(cp.deptid IN (10),Branch,dept) AS `Branch/Dept` FROM systools_2clresults r LEFT JOIN attend_30currentpositions cp ON r.IDNoOrBranchNo=cp.IDNo WHERE CTxnID='.$mainlistid.' '.$maincon.' '.$condidate.' ORDER BY DateAnswered DESC,`Branch/Dept`';
	// echo $sqlmain;
        $title='Daily Health Check'; 
		$columnnames=array('FullName','DateAnswered','Branch/Dept','Answered?');
		
		$editprocess='dailyhealthcheckform.php?w=Form1&clTxnID='; $editprocesslabel='Lookup';
		
		$sql=$sqlmain;
		
		$width='60%';
        include('../backendphp/layout/displayastable.php');
	
	break;
	
	case 'Form':
	
	echo '<script>
    function changeBodyBg(color){
        document.body.style.background = color;
		document.getElementById("btnHide").style.visibility="hidden";
		document.getElementById("btnSubmit").style.display = "block";
		document.getElementById("areyousure").style.display = "block";
		document.getElementById("areyousure").style.background = "orange";
    }
	</script>';
	
	

	$sql='SELECT s.*,IsRate,RYNMin,RYNMax FROM systools_2clsub s LEFT JOIN systools_1clrateoryn r ON s.RYNID=r.RYNID WHERE TxnID='.$txnid.' ORDER BY `OrderBy`';
	
	$stmt = $link->query($sql);
	$row=$stmt->fetchAll();
	
	echo '<br><br><div style="width:50%;border:2px solid blue;padding:10px;background-color:white;margin-left:23%;">';
	echo '<div id="areyousure" style="display:none;text-align:center;padding:5px;"><h1>Are You Sure?</h1></div>';
	echo $forminfo;
	
	echo '<h3 align="center">'.$title.'</h3>';
	echo '<br>';
	echo '<form action="dailyhealthcheckform.php?w=Update&clTxnID='.$_GET['clTxnID'].'" method="POST"><table>';
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
			$answerd='Rate: <input type="number" name="'.$rowi['QID'].'" min="'.$rowi['RYNMin'].'" max="'.$rowi['RYNMax'].'" step="0.01" required>';
		}
		
		titleonly:
		
		if($rowi['QType']==3){
			$space=str_repeat('&nbsp;','12');
		}
		
		echo '<tr><td class="pad">'.$space.$rowi['OrderBy'].'. '.$rowi['Question'].'</td><td style="width:120px;text-align:right">'.$answerd.'</td></tr>';
		$space=''; $answerd=''; $enter='';
	}
	if(allowedToOpen(array(6227,6225,6224,6110),'1rtc') AND $_SESSION['&pos']<>-1){
		echo '<tr><td colspan="2" align="center"><input type="time" name="TimeIn" value="08:00"><input type="hidden" name="IDNo" value="'.$rowc['IDNoOrBranchNo'].'"><input type="hidden" name="Date" value="'.$rowc['DateAnswered'].'"></td></tr>';
	} else {
		if($agreement<>''){
			echo '<tr><td colspan=2 style="font-size:10pt;"><br><hr>* '.$agreement.'</td></tr>';
		}
	}
	
	echo '<tr><td colspan=2 align="center"><input type="submit" id="btnSubmit" value="Submit to record Time In" name="btnSubmit" style="background-color:blue;color:white;padding:5px;width:200px;border-radius:15px;display:none;" OnClick="return confirm(\'Are you Sure?\');"></td></tr>';
	echo '<tr><td colspan=2 align="center"><button type="button" id="btnHide" style="background-color:blue;color:white;padding:5px;width:200px;border-radius:15px;" onclick="changeBodyBg(\'black\');">Submit to record Time In</button></td></tr>';
	
	echo '</table></form>';
	
	echo '</div>';
	
	
	break;
	
	case 'Form1':
	
	
	echo '<br><br><div style="width:50%;border:2px solid blue;padding:10px;background-color:white;margin-left:23%;">';
	echo $forminfo;
	
	echo '<h3 align="center">'.$title.'</h3>';
	
	echo '<br>';
	echo '<form action="dailyhealthcheckform.php?w=Update&clTxnID='.$_GET['clTxnID'].'" method="POST"><table>';
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
		header('Location:dailyhealthcheckform.php?w=Form&clTxnID='.intval($_GET['clTxnID']));
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
			$answerd='<input type="text" size="6" name="'.$rowi['QID'].'" value="'.$rowi['Score'].'" required>';
		}
		
		if($rowi['IsRate']==1){
			$answerd='Rate: <input type="number" name="'.$rowi['QID'].'" min="'.$rowi['RYNMin'].'" max="'.$rowi['RYNMax'].'" value="'.$rowi['Score'].'" step="0.01" required>';
		}
		
		titleonly1:
		
		if($rowi['QType']==3){
			$space=str_repeat('&nbsp;','12');
		}
		
		echo '<tr><td class="pad">'.$space.$rowi['OrderBy'].'. '.$rowi['Question'].'</td><td style="width:120px;text-align:right">'.$answerd.'</td></tr>';
		$space=''; $answerd=''; $enter='';
	}
	
	
	
	$sqlcheckifeditable='SELECT TIMESTAMPDIFF(SECOND,CONCAT(DateToday," ",TimeIn), CONCAT(CURDATE()," ","'.date('H:i:s').'")) AS Seconds FROM attend_2attendance a JOIN (SELECT IDNoOrBranchNo,DateAnswered FROM systools_2clresults WHERE TxnID='.intval($_GET['clTxnID']).') ibd ON a.IDNo=ibd.IDNoOrBranchNo AND a.DateToday=ibd.DateAnswered';
	// echo $sqlcheckifeditable;
	
	$stmtcheckifeditable = $link->query($sqlcheckifeditable);
	$rowcheckifeditable=$stmtcheckifeditable->fetch();
	
	if($rowcheckifeditable['Seconds']<=7200){ //atleast 2 hours
		echo '<tr><td colspan=2 align="center"><input type="submit" value="Update" name="btnSubmit1" style="background-color:blue;color:white;padding:5px;width:200px;border-radius:15px;"></td></tr>';
	}
	
	
	echo '</table></form>';
	echo '</div>';
	
	
	break;
	
	case 'Update':
			$txnid=intval($_GET['clTxnID']);
			$columnstoadd=$_POST;
			
			//get array keys
			$keys = array_keys($columnstoadd);
			if(isset($_POST['TimeIn'])){
				$minus=5; //hidden array timein etc
			} else {
				$minus=2; 
			}
			$cntend=count($columnstoadd)-$minus;
			
			$cnt=0;
			
			$qsarray=''; $yeses=0;
			//get array name
			while($cnt<=$cntend){
				if($_POST[$keys[$cnt]]==-1){
					$yeses++;
				}
				$qsarray.=$keys[$cnt].'>'.str_replace(",",".",$_POST[$keys[$cnt]]).',';
				$cnt++;
			}
			$qsarray=substr($qsarray, 0, -1);
			
			
			$sql='UPDATE systools_2clresults r SET EncodedByNo='.$_SESSION['(ak0)'].', QuestionsScoresArray="'.$qsarray.'", TimeStamp=Now() WHERE (EncodedByNo='.$_SESSION['(ak0)'].' OR IDNoOrBranchNo='.$_SESSION['(ak0)'].' OR '.$_SESSION['(ak0)'].'=(SELECT LatestSupervisorIDNo FROM attend_30currentpositions WHERE IDNo=r.IDNoOrBranchNo)) AND TxnID='.$txnid;
			$stmt=$link->prepare($sql); $stmt->execute();
			
			if(isset($_POST['btnSubmit'])){
				if($yeses>0){
					echo '<div style="background-color:white;padding:5px;border:1px solid blue;width:30%;margin-left:35%;">';
					echo 'Daily Health Check Recorded.<br><br>Please get health clearance from your barangay health center before coming to work.<br><br><h2 style="color:red;">Attendance NOT recorded.</h2>';
					echo '</div>';
					exit();
				} else {
					$rtctime = date("H:i",strtotime(date("Y-m-d H:i:s")));
					
					if(isset($_POST['TimeIn'])){
						
						if($_POST['TimeIn']>='17:00'){
							echo 'Invalid Time In. Time in is greater than 5PM.'; exit();
						}
						$addlsql='TimeIn="'.$_POST['TimeIn'].'",';
						$condisql='WHERE IDNo='.$_POST['IDNo'].' AND DateToday="'.$_POST['Date'].'"';
					} else {
						$addlsql='TimeIn=time("'.$rtctime.'"),';
						$condisql='WHERE IDNo='.$_SESSION['(ak0)'].' AND DateToday=CURDATE()';
					}
					$sql='UPDATE attend_2attendance SET '.$addlsql.'TIEncby='.$_SESSION['(ak0)'].', TInTS=Now(),LeaveNo=if(LeaveNo<>15,11,15) '.$condisql.' AND TimeIn IS NULL';
					$stmt=$link->prepare($sql); $stmt->execute();
				}
				
				
			}
		
		header('Location:dailyhealthcheckform.php?w=Form1&clTxnID='.$txnid);
	break;
	
	case 'Add':
	$sql='INSERT INTO systools_2clresults SET IDNoOrBranchNo='.$_POST['IDNo'].',CTxnID=1,DateAnswered="'.$_POST['Date'].'",EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now();';
	$stmt=$link->prepare($sql); $stmt->execute();
	
	$sqlhealthmain='SELECT TxnID FROM systools_2clresults WHERE IDNoOrBranchNo='.$_POST['IDNo'].' AND DateAnswered="'.$_POST['Date'].'";';
	$stmtdailyhealth=$link->query($sqlhealthmain);
	$resdailyhealth=$stmtdailyhealth->fetch(); 
 
	header('Location:../systools/dailyhealthcheckform.php?w=Form&clTxnID='.$resdailyhealth['TxnID']);
	
	break;
	
	case 'Reports':
	// if($_SESSION['(ak0)']<>1002){ exit(); }
	
	
	$title='Daily Health Check Summary';
	echo '<title>'.$title.'</title>';
	echo '<style>tr
	{
		line-height:19px;
	}
</style>';


echo '<div>';
echo '<div style="float:left;">';
echo '<form action="#" method="POST"><input type="date" value="'.date('Y-m-d').'" name="DateAnswered"> <input style="background-color:red;padding:2px;" type="submit" value="With Issues Only" name="btnHide"> <input style="background-color:limegreen;padding:2px;" type="submit" value="Show All" name="btnShowAll"></form>';
echo '</div><div style="float:right;">';
echo '<form action="dailyhealthcheckform.php?w=ReportsPerPerson" method="POST">FullName: <input type="text" name="IDNo" list="employees"> <input type="submit" value="Lookup Per Person" name="btnLookUp"></form>';
echo '</div>';
echo '</div>';
echo '<div style="clear: both; display: block; position: relative;height:20px;"></div>';
if(isset($_POST['DateAnswered'])){
		$dateanswered=$_POST['DateAnswered'];
	} else {
		$dateanswered=date('Y-m-d');
	}
	// $sql0='DELETE FROM systools_clexplodedarraytempdata WHERE TxnID='.$mainlistid.' AND EncodedByNo='.$_SESSION['(ak0)'].'';
	// $stmt0=$link->prepare($sql0); $stmt0->execute();
	$stmt0=$link->prepare($sqldeletetemp); $stmt0->execute();
	
	$sql2='SELECT QuestionsScoresArray,IDNoOrBranchNo FROM systools_2clresults WHERE (QuestionsScoresArray IS NOT NULL AND QuestionsScoresArray<>"") AND DateAnswered="'.$dateanswered.'" AND CTxnID="'.$mainlistid.'"';
	// echo $sql2;
	$stmt2 = $link->query($sql2);
	$row2=$stmt2->fetchAll();
	
	foreach($row2 AS $row22){
			$arrayex=explode(",", $row22['QuestionsScoresArray']);
			foreach($arrayex as $arrex){
					$arr = explode(">", $arrex, 2);
					$qid = $arr[0];
					$score = $arr[1];
					$sql='INSERT INTO systools_clexplodedarraytempdata SET TxnID='.$mainlistid.',IDNoOrBranchNo='.$row22['IDNoOrBranchNo'].',QIDarr='.$qid.',Score="'.($score<>''?$score:'0').'",EncodedByNo='.$_SESSION['(ak0)'].';';
					// echo $sql.'<br>';
					$stmt=$link->prepare($sql); $stmt->execute();
			}

	}
	
	$sqldef='select GROUP_CONCAT(DISTINCT(IDNoOrBranchNo)) AS defaultdisp from systools_clexplodedarraytempdata WHERE (Score>37.5 OR Score=-1) AND EncodedByNo='.$_SESSION['(ak0)'].' AND TxnID='.$mainlistid.'';
	// echo $sqldef; exit();
	$stmtdef = $link->query($sqldef);
	$rowdef=$stmtdef->fetch();
	
	if($rowdef['defaultdisp']<>''){
		$defaultlistidno=$rowdef['defaultdisp'];
	} else {
		$defaultlistidno='0';
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
	
	if(isset($_POST['btnShowAll'])){
		
		$defaultcondi='AND 1=1';
	} else {
		$defaultcondi='AND IDNoOrBranchNo IN ('.$defaultlistidno.')';
	}
	
	
	$sqldata='SELECT "FullName","IDNo",'.$sqltrhead.'"Branch/Dept","TimeStamp",1 AS th UNION SELECT FullName,IDNo,'.$sqltrbody.'IF(deptid IN (10),Branch,dept) AS `Branch/Dept`,r.TimeStamp,0 AS th FROM systools_2clresults r LEFT JOIN attend_30currentpositions cp ON r.IDNoOrBranchNo=cp.IDNo WHERE CTxnID='.$mainlistid.' '.$maincon.' '.$defaultcondi.' AND DateAnswered="'.$dateanswered.'" GROUP BY IDNoOrBranchNo ORDER BY th DESC,`Branch/Dept`';
	$stmtdata = $link->query($sqldata);
	$rowdata=$stmtdata->fetchAll();
	
	$dateanswered=strtotime($dateanswered);
	
	echo '<div style="border:2px solid blue;background-color:white;padding:5px;"><h3 align="center">'.$title.'<br>'.date('M d, Y', $dateanswered).' </h3><br>';
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
			echo '<th style="padding:5px;">'.$rowdat['TimeStamp'].'</th>';
			echo '</tr>';
		} else {
			$thstart=1;
			echo '<tr style="font-size:9pt;background-color:limegreen;">';
			echo '<td style="text-align:center;font-weight:bold;width:190px;background-color:gray;"><a href="dailyhealthcheckform.php?w=ReportsPerPerson&IDNo='.$rowdat['IDNo'].'" style="color:white;" target="_blank">'.$rowdat['FullName'].'</a></td>';
			echo '<td style="text-align:center;font-weight:bold;background-color:gray;">'.$rowdat['Branch/Dept'].'</td>';
			while($thstart<$thcount){
				echo '<td style="text-align:center;font-weight:bold;'.(($rowdat['title'.$thstart.'']=="Yes" OR is_numeric($rowdat['title'.$thstart.'']) AND $rowdat['title'.$thstart.'']>37.5)?'background-color:red;':'').'">'.$rowdat['title'.$thstart.''].'</td>';
				$thstart++;
			}
			echo '<td style="text-align:center;font-weight:bold;background-color:gray;">'.$rowdat['TimeStamp'].'</td>';
			echo '</tr>';
		}
		$thstart=1;
	}
	echo '</table></div>';
	
	$stmt0=$link->prepare($sqldeletetemp); $stmt0->execute();
	
	break;
	
	
	case 'ReportsPerPerson':
	$title='Daily Health Check Summary Per Person';
	echo '<title>'.$title.'</title>';
	echo '<style>tr
	{
		line-height:18px;
	}
</style>';
if(allowedToOpen(array(6224,6225,6227,6226,6228,6110),'1rtc')){
$idno=intval($_REQUEST['IDNo']);
} else {
	$idno=$_SESSION['(ak0)'];
}
	// $sql0='';
	$stmt0=$link->prepare($sqldeletetemp); $stmt0->execute();
	
	$sql2='SELECT QuestionsScoresArray,IDNoOrBranchNo,DateAnswered FROM systools_2clresults WHERE IDNoOrBranchNo='.$idno;
	$stmt2 = $link->query($sql2);
	$row2=$stmt2->fetchAll();
	
	foreach($row2 AS $row22){
			$arrayex=explode(",", $row22['QuestionsScoresArray']);
			foreach($arrayex as $arrex){
					$arr = explode(">", $arrex, 2);
					$qid = $arr[0];
					$score = $arr[1];
					$sql='INSERT INTO systools_clexplodedarraytempdata SET DateAnswered="'.$row22['DateAnswered'].'",TxnID='.$mainlistid.',IDNoOrBranchNo='.$row22['IDNoOrBranchNo'].',QIDarr='.$qid.',Score="'.$score.'",EncodedByNo='.$_SESSION['(ak0)'].';';
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
		FROM systools_clexplodedarraytempdata WHERE QIDarr='.$rowqid['QID'].' AND IDNoOrBranchNo=r.IDNoOrBranchNo AND DateAnswered=r.DateAnswered AND EncodedByNo='.$_SESSION['(ak0)'].'),';
		$thcount++;
	}
	
	$sqldata='SELECT '.$sqltrhead.'1 AS th,"DateAnswered" UNION SELECT '.$sqltrbody.'0 AS th,DateAnswered FROM systools_2clresults r LEFT JOIN attend_30currentpositions cp ON r.IDNoOrBranchNo=cp.IDNo WHERE IDNoOrBranchNo='.$idno.' GROUP BY DateAnswered ORDER BY th DESC,DateAnswered DESC';
	
	
	$stmtdata = $link->query($sqldata);
	$rowdata=$stmtdata->fetchAll();
	
	$sqlnameandbranch='SELECT CONCAT(FullName," (",IF(deptid IN (1,2,3,10),Branch,dept),")") AS NameAndBranch FROM attend_30currentpositions WHERE IDNo='.$idno;
	$stmtnameandbranch = $link->query($sqlnameandbranch);
	$rownameandbranch=$stmtnameandbranch->fetch();
	
	echo '<div style="border:2px solid blue;background-color:white;padding:5px;"><h3 align="center">'.$title.'<br>'.$rownameandbranch['NameAndBranch'].' </h3><br>';
	echo '<table>';
	$thstart='1';
	foreach($rowdata AS $rowdat){
		if($rowdat['th']==1){
			echo '<tr style="font-size:10pt;background-color:maroon;color:white;">';
			echo '<th style="padding:5px;">'.$rowdat['DateAnswered'].'</th>';
			while($thstart<$thcount){
				
				echo '<th style="padding:5px;">'.$rowdat['title'.$thstart.''].'</th>';
				$thstart++;
			}
			echo '</tr>';
		} else {
			$thstart=1;
			echo '<tr style="font-size:9pt;background-color:limegreen;">';
			
				echo '<th style="padding:5px;">'.$rowdat['DateAnswered'].'</th>';
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
	
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
