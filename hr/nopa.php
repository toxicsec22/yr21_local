<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(59021,'1rtc')) { echo 'No permission'; exit; }
$which=(!isset($_GET['w'])?'Default':$_GET['w']);
if($which<>'Print' AND $which<>'PrintAll'){
$showbranches=false;
include_once('../switchboard/contents.php');

if($which=='ChangeinPosition'){
	if (!allowedToOpen(59022,'1rtc')) { echo 'No permission'; exit; }
}
if($which=='BranchTransfer'){
	if (!allowedToOpen(59023,'1rtc')) { echo 'No permission'; exit; }
}
if($which=='SalaryAdjustment'){
	if (!allowedToOpen(59024,'1rtc')) { echo 'No permission'; exit; }
}
if($which=='Regularization'){
	if (!allowedToOpen(59025,'1rtc')) { echo 'No permission'; exit; }
}
if($which=='LateralTransfer'){
	if (!allowedToOpen(59026,'1rtc')) { echo 'No permission'; exit; }
}


 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');


?>
<body>
<br><div id="section" style="display: block;">

    <div>
	<?php if (allowedToOpen(59022,'1rtc')) { ?>
		<a id='link' href="nopa.php?w=ChangeinPosition">Promotion</a>
	<?php } ?>
	<?php if (allowedToOpen(59023,'1rtc')) { ?>
        <a id='link' href="nopa.php?w=BranchTransfer">Branch Transfer</a>
	<?php } ?>
	<?php if (allowedToOpen(59024,'1rtc')) { ?>
			<a id='link' href="nopa.php?w=SalaryAdjustment">Salary Adjustment</a>
	<?php } ?>
	<?php if (allowedToOpen(59025,'1rtc')) { ?>
        <a id='link' href="nopa.php?w=Regularization">Regularization</a>
	<?php } ?>
	<?php if (allowedToOpen(59026,'1rtc')) { ?>
        <a id='link' href="nopa.php?w=LateralTransfer">Lateral Transfer</a>
	<?php } ?>
	
	<?php if (allowedToOpen(59022,'1rtc')) { ?>
        &nbsp; &nbsp; &nbsp; &nbsp; <a id='link' href="coe.php">Certificate of Employment</a>
	<?php } ?>
	<?php if (allowedToOpen(59028,'1rtc')) { ?>
        <a id='link' href="printawol.php">Print AWOL Letters</a>
	<?php } ?>
    </div><br/><br/>
	
<?php

} else {
	$sqlcheckothermain='SELECT 
	(CASE
		WHEN nopaID=1 THEN "Promotion"
		WHEN nopaID=2 THEN "Branch Transfer"
		WHEN nopaID=3 THEN "Salary Adjustment"
		WHEN nopaID=4 THEN "Regularization"
		WHEN nopaID=5 THEN "Change in Position"
		ELSE ""
	END)
		AS NOPAaction,
	(CASE
		WHEN nopaID IN (1,5) THEN p1.Position
		WHEN nopaID=2 THEN b1.Branch
		WHEN nopaID=3 THEN SalaryFrom
		WHEN nopaID=4 THEN "Probationary Employment"
		ELSE ""
	END)
		AS FromWhat, 
	(CASE
		WHEN nopaID IN (1,5) THEN p2.Position
		WHEN nopaID=2 THEN b2.Branch
		WHEN nopaID=3 THEN SalaryTo
		WHEN nopaID=4 THEN "Regular Employment"
		ELSE ""
	END) AS ToWhat,
	(CASE
		WHEN nopaID IN (1,2,4,5) THEN (SELECT Remarks FROM attend_2changeofpositions WHERE IDNo=ht.IDNo ORDER BY DateofChange DESC LIMIT 1)
		WHEN nopaID=3 THEN (SELECT Remarks FROM payroll_22rates WHERE IDNo=ht.IDNo ORDER BY DateofChange DESC LIMIT 1)
		ELSE ""
	END)
	AS Remarks
	 FROM hr_nopaholdingtable ht LEFT JOIN attend_0positions p1 ON ht.PositionIDFrom=p1.PositionID LEFT JOIN attend_0positions p2 ON ht.PositionIDTo=p2.PositionID LEFT JOIN 1branches b1 ON ht.BranchNoFrom=b1.BranchNo LEFT JOIN 1branches b2 ON ht.BranchNoTo=b2.BranchNo WHERE IDNo=';

	 echo '<title>Print NOPA Letters</title>';
		echo '<style>@media print {
			 html, body {
					height: 99%;    
		}
		}</style>';
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
}


switch ($which)
{
	case 'Default':
		$title='NOPA Forms'; 
        echo '<title>'.$title.'</title>';
		echo '<h3>Select Forms</h3>';
	break; 
	
	case 'ChangeinPosition':
	case 'BranchTransfer':
	case 'SalaryAdjustment':
	case 'Regularization':
	case 'LateralTransfer':
	
	
	
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		
			
			// $message='Really add?';
			
			if($which=='ChangeinPosition'){
				$title='NOPA - Promotion';
				$inputval='Create NOPA - Promotion';
				$fromwhatnopa=1;
			}
			if($which=='BranchTransfer'){
				$title='NOPA - Branch Transfer';
				$inputval='Create NOPA - Branch Transfer';
				$fromwhatnopa=2;
			}
			if($which=='SalaryAdjustment'){
				$title='NOPA - Salary Adjustment';
				$inputval='Create NOPA - Salary Adjustment';
				$fromwhatnopa=3;
			}
			if($which=='Regularization'){
				$title='NOPA - Regularization';
				$inputval='Create NOPA - Regularization';
				$fromwhatnopa=4;
			}
			if($which=='LateralTransfer'){
				$title='NOPA - Lateral Transfer';
				$inputval='Create NOPA - Lateral Transfer';
				$fromwhatnopa=5;
			}
			
			$between=''; $dfrom=date('Y-m-d'); $dto=$dfrom;
			if(isset($_SESSION['dDateFrom'])){
				$between=' BETWEEN "'.$_SESSION['dDateFrom'].'" AND "'.$_SESSION['dDateTo'].'"';
				$dfrom=$_SESSION['dDateFrom']; $dto=$_SESSION['dDateTo'];
			}
			
			echo '<form action="nopa.php?w=SetMonthSession" method="POST" autocomplete="off">DateFrom: <input type="date" name="DateFrom" value="'.$dfrom.'" size="5"> DateTo: <input type="date" name="DateTo" value="'.$dto.'" size="5"> <input type="submit" name="btnSetMonth" value="Set Date Filter"></form><br>';
			
			
			
			if($fromwhatnopa==1 OR $fromwhatnopa==2 OR $fromwhatnopa==5){
				$sql='SELECT cop.IDNo,FullName,Branch,`Position`,department AS Department,MAX(cop.DateofChange) AS DateofChange FROM attend_2changeofpositions cop JOIN attend_30currentpositions cp ON cop.IDNo=cp.IDNo GROUP BY IDNo HAVING COUNT(`cop`.IDNo)>1 AND DateofChange '.$between.' ORDER BY FullName';
				
				$sqlcheckbox='SELECT COUNT(IDNo) AS inpa FROM hr_2personnelaction WHERE ActionID IN (1,2,4,6,7) ';
			}
			if($fromwhatnopa==3){
				$sql='SELECT r.IDNo,FullName,Branch,`Position`,department AS Department,MAX(r.DateofChange) AS DateofChange FROM payroll_22rates r JOIN attend_30currentpositions cp ON r.IDNo=cp.IDNo WHERE (r.ApprovedByNo IS NOT NULL OR r.ApprovedByNo<>0) GROUP BY IDNo HAVING COUNT(`r`.IDNo)>1 AND DateofChange '.$between.' ORDER BY FullName;';
				
				$sqlcheckbox='SELECT COUNT(IDNo) AS inpa FROM hr_2personnelaction WHERE ActionID IN (3,5) ';
			}
			if($fromwhatnopa==4){
				$sql='SELECT e.IDNo,FullName,(DateHired + INTERVAL 5 MONTH + INTERVAL 1 DAY) AS DateofEffectivityReg,Branch,`Position`,department AS Department,(DateHired + INTERVAL 6 MONTH) AS DateofChange FROM 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE TIMESTAMPDIFF(DAY, DateHired, CURDATE())>=145 HAVING DateofEffectivityReg '.$between.';';
				
				$sqlcheckbox='SELECT COUNT(IDNo) AS inpa FROM hr_2personnelaction WHERE ActionID IN (0) ';
			}
			
		$stmt=$link->query($sql); $res=$stmt->fetchAll();
		echo '<title>'.$title.'</title>';
		
		echo '<h3>'.$title.'</h3>';
		
		if(isset($_SESSION['dDateFrom'])){
		echo '<form action="nopa.php?w='.$which.'&set=1#checkset" method="post">';
		echo '<br><table style="padding:4px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
		echo '<thead style="font-weight:bold;"><tr><td colspan=5 align="right"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input style="background-color:yellow;width:220px" type="submit" value="'.$inputval.'" /></td></tr><tr><th>All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></th><th>Employee</th><th>Branch</th><th>Position</th><th>Department</th></tr></thead><tbody style=\"overflow:auto;\">';
		foreach($res AS $row){
			$sqlch=$sqlcheckbox.' AND IDNo='.$row['IDNo'].' AND "'.$row['DateofChange'].'" IN (DateServed);';
			// echo $sqlch;
			
			$stmtch=$link->query($sqlch); $resch=$stmtch->fetch();
			
			if($resch['inpa']>0){
				$op=1; //open checkbox;
			} else {
				$op=0; //close checkbox;
			}
			// echo $op.'<br>';
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td align="right">'.($op==1?'<input type="checkbox" value="'.$row['IDNo'].'" name="idno[]" />':'<a href="personnelaction.php">Encode here</a>').'</td><td>'.$row['FullName'].'</td><td>'.$row['Branch'].'</td><td>'.$row['Position'].'</td><td>'.$row['Department'].'</td></tr>';
			$colorcount++;
		}
		echo '</tbody></table>';
		echo '</form>';
		}
		echo '<br><br><a href="nopa.php?w='.$which.'&set=1#checkset" style="color:green;">Show Latest History</a>';
		
		
	?>
	<div id="checkset" onclick="window.location.hash='back'; ">
		</div>
	<?php

		if(isset($_GET['set'])){
			if (isset($_REQUEST['idno'])){
				foreach ($_REQUEST['idno'] AS $idno){
					$sql='SELECT IDNo FROM hr_nopaholdingtable WHERE IDNo='.$idno.' AND nopaID='.$fromwhatnopa.'';
					$stmt=$link->query($sql);
					
					if($stmt->rowCount()>0){
						goto noinsert;
					}
					$verifiedbyno='(SELECT IDNo FROM attend_30latestpositionsinclresigned WHERE PositionID=(SELECT deptheadpositionid FROM 1departments WHERE deptid=30) AND Resigned=0)';
					if($fromwhatnopa==1 OR $fromwhatnopa==5){
						$sql1='INSERT INTO hr_nopaholdingtable (IDNo,PositionIDFrom,PositionIDTo,RecommendedByNo,VerifiedByNo,nopaID) SELECT IDNo,(select DISTINCT(NewPositionID) from attend_2changeofpositions WHERE IDNo='.$idno.' ORDER BY DateofChange DESC LIMIT 1,1) AS PositionIDFrom,(select NewPositionID from attend_2changeofpositions WHERE IDNo='.$idno.' ORDER BY DateofChange DESC LIMIT 1) AS PositionIDTo,(SELECT IDNo FROM attend_30latestpositionsinclresigned WHERE PositionID=deptheadpositionid AND Resigned=0) AS RecommendedByNo,'.$verifiedbyno.' AS VerifiedByNo,'.$fromwhatnopa.' FROM attend_30currentpositions WHERE IDNo='.$idno.'';
					}
					if($fromwhatnopa==2){
						
						$sql1='INSERT INTO hr_nopaholdingtable (IDNo,BranchNoFrom,BranchNoTo,RecommendedByNo,VerifiedByNo,nopaID) SELECT IDNo,(select DISTINCT(AssignedBranchNo) from attend_2changeofpositions WHERE IDNo='.$idno.' ORDER BY DateofChange DESC LIMIT 1,1) AS BranchNoFrom,(select AssignedBranchNo from attend_2changeofpositions WHERE IDNo='.$idno.' ORDER BY DateofChange DESC LIMIT 1) AS BranchNoTo,(SELECT IDNo FROM attend_30latestpositionsinclresigned WHERE PositionID=deptheadpositionid AND Resigned=0) AS RecommendedByNo,'.$verifiedbyno.' AS VerifiedByNo,'.$fromwhatnopa.' FROM attend_30currentpositions WHERE IDNo='.$idno.'';
					}
					if($fromwhatnopa==3){
						
						
						$sql1='INSERT INTO hr_nopaholdingtable (IDNo,SalaryFrom,SalaryTo,RecommendedByNo,VerifiedByNo,nopaID) SELECT IDNo,(select CONCAT("BasicRate: ",FORMAT(BasicRate,2),IF(DeMinimisRate=0 OR DeMinimisRate IS NULL,"",CONCAT("<br>DeMinimisRate: ",FORMAT(DeMinimisRate,2))),IF(TaxShield=0 OR TaxShield IS NULL,"",CONCAT("<br>TaxShield: ",FORMAT(TaxShield,2)))) from payroll_22rates WHERE IDNo='.$idno.' ORDER BY DateofChange DESC LIMIT 1,1) AS SalaryFrom,(select CONCAT("BasicRate: ",FORMAT(BasicRate,2),IF(DeMinimisRate=0 OR DeMinimisRate IS NULL,"",CONCAT("<br>DeMinimisRate: ",FORMAT(DeMinimisRate,2))),IF(TaxShield=0 OR TaxShield IS NULL,"",CONCAT("<br>TaxShield: ",FORMAT(TaxShield,2)))) FROM payroll_22rates WHERE IDNo='.$idno.' ORDER BY DateofChange DESC LIMIT 1) AS SalaryTo,IF((SELECT IDNo FROM attend_30latestpositionsinclresigned WHERE PositionID=deptheadpositionid AND Resigned=0)<>'.$idno.',(SELECT IDNo FROM attend_30latestpositionsinclresigned WHERE PositionID=deptheadpositionid AND Resigned=0),(SELECT SupervisorIDNo FROM attend_1defaultbranchassign WHERE IDNo='.$idno.')) AS RecommendedByNo,'.$verifiedbyno.' AS VerifiedByNo,'.$fromwhatnopa.' FROM attend_30currentpositions WHERE IDNo='.$idno.'';
					}
					if($fromwhatnopa==4){
						$sql1='INSERT INTO hr_nopaholdingtable (IDNo,RecommendedByNo,VerifiedByNo,nopaID) SELECT IDNo,IF((SELECT IDNo FROM attend_30latestpositionsinclresigned WHERE PositionID=deptheadpositionid AND Resigned=0)<>'.$idno.',(SELECT IDNo FROM attend_30latestpositionsinclresigned WHERE PositionID=deptheadpositionid AND Resigned=0),(SELECT SupervisorIDNo FROM attend_1defaultbranchassign WHERE IDNo='.$idno.')) AS RecommendedByNo,'.$verifiedbyno.' AS VerifiedByNo,'.$fromwhatnopa.' FROM attend_30currentpositions WHERE IDNo='.$idno.';';
					}
					
					$stmt=$link->prepare($sql1); $stmt->execute();
					
					noinsert:
				}
			
			}
			
			
			$sql='SELECT ht.TxnID,ht.IDNo,cp.Position,SalaryFrom,SalaryTo,FullName,BranchNoFrom,BranchNoTo,Branch,PositionIDFrom,PositionIDTo FROM hr_nopaholdingtable ht JOIN attend_30currentpositions cp ON ht.IDNo=cp.IDNo WHERE nopaID='.$fromwhatnopa.' ORDER BY FullName';
			// echo $sql; 
			$stmt=$link->query($sql); $res=$stmt->fetchAll();
			
			
			echo '<br><br><table style="padding:4px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
			echo '<thead style="font-weight:bold;"><tr><td colspan=8 align="right"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><form action="nopa.php?w=PrintAll&From='.$fromwhatnopa.'" method="post"><input style="background-color:green;color:white;width:270px" type="submit" value="PRINT ALL '.$title.'" /></form><form action="nopa.php?w=ClearData&From='.$fromwhatnopa.'" method="post"><input style="background-color:red;color:white;width:120px" type="submit" value="Clear Data" OnClick="return confirm(\'Clear all data?\');"/></form></td></tr>';
			if ($fromwhatnopa==1 OR $fromwhatnopa==5){
				echo '<tr><td>Employee</td><td>Branch</td><td>Position From</td><td>Position To</td></tr>';
			}
			if ($fromwhatnopa==2){
				echo '<tr><td>Employee</td><td>Position</td><td>Branch From</td><td>Branch To</td></tr>';
			}
			if ($fromwhatnopa==3){
				echo '<tr><td>Employee</td><td>Position/Branch</td><td>Salary From</td><td>Salary To</td></tr>';
			}
			if ($fromwhatnopa==4){
				echo '<tr><td>Employee</td><td>Position/Branch</td><td colspan=2></td></tr>';
			}
			echo '</thead><tbody style=\"overflow:auto;\">';
			
			
			foreach($res AS $row){
				
				if($fromwhatnopa==1 OR $fromwhatnopa==5){
					$sqlpos = 'SELECT PositionID, Position FROM attend_0positions ORDER BY Position';
					$stmtpos = $link->query($sqlpos);
					
					$posfrom='<select name="PositionIDFrom" disabled>';
					while($rowpos = $stmtpos->fetch())
					{
						if($row['PositionIDFrom'] == $rowpos['PositionID']){
							$selected = 'selected';
						} else { $selected = ''; }
						$posfrom.='<option value="'.$rowpos['PositionID'].'" '.$selected.'>'.$rowpos['Position'].'</option>';
					}
					$posfrom.='</select>';
					
					$stmtpos = $link->query($sqlpos);
					$posto='<select name="PositionIDTo" disabled>';
					while($rowpos = $stmtpos->fetch())
					{
						if($row['PositionIDTo'] == $rowpos['PositionID']){
							$selected = 'selected';
						} else { $selected = ''; }
						$posto.='<option value="'.$rowpos['PositionID'].'" '.$selected.'>'.$rowpos['Position'].'</option>';
					}
					$posto.='</select>';
				}
				
				if($fromwhatnopa==2){
					$sqlbranch = 'SELECT BranchNo, Branch FROM 1branches WHERE Active=1 AND BranchNo<>95 ORDER BY Branch';
					$stmtbranch = $link->query($sqlbranch);
					
					$branchfrom='<select name="BranchNoFrom" disabled>';
					while($rowbranch = $stmtbranch->fetch())
					{
						if($row['BranchNoFrom'] == $rowbranch['BranchNo']){
							$selected = 'selected';
						} else { $selected = ''; }
						$branchfrom.='<option value="'.$rowbranch['BranchNo'].'" '.$selected.'>'.$rowbranch['Branch'].'</option>';
					}
					$branchfrom.='</select>';
					
					$stmtbranch = $link->query($sqlbranch);
					$branchto='<select name="BranchNoTo" disabled>';
					while($rowbranch = $stmtbranch->fetch())
					{
						if($row['BranchNoTo'] == $rowbranch['BranchNo']){
							$selected = 'selected';
						} else { $selected = ''; }
						$branchto.='<option value="'.$rowbranch['BranchNo'].'" '.$selected.'>'.$rowbranch['Branch'].'</option>';
					}
					$branchto.='</select>';
				}
				
				echo '<form action="nopa.php?w=Update&From='.$fromwhatnopa.'&TxnID='.$row['TxnID'].'" method="POST"><tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row['FullName'].'</td>';
				
				if($fromwhatnopa==1 OR $fromwhatnopa==5){
					echo '<td>'.$row['Branch'].'</td><td>'.$posfrom.'</td><td>'.$posto.'</td>';
				}
				if($fromwhatnopa==2){
					echo '<td>'.$row['Position'].'</td><td>'.$branchfrom.'</td><td>'.$branchto.'</td>';
				}
				if($fromwhatnopa==3){
					echo '<td>'.$row['Position'].'/'.$row['Branch'].'</td><td>'.$row['SalaryFrom'].'</td><td>'.$row['SalaryTo'].'</td>';
				}
				if($fromwhatnopa==4){
					echo '<td>'.$row['Position'].'/'.$row['Branch'].'</td><td></td><td></td>';
				}
				
				// echo '<td>'.(($fromwhatnopa==1 OR $fromwhatnopa==2 OR $fromwhatnopa==5)?'<input type="submit" style="background-color:orange;color:white;padding:3px;" value="Edit">':'').'</td></form><td><form action="nopa.php?w=Print&From='.$fromwhatnopa.'&TxnID='.$row['TxnID'].'" method="post"><input type="submit" style="background-color:blue;color:white;padding:3px;" value="Print"></form></td><td><form action="nopa.php?w=Remove&TxnID='.$row['TxnID'].'" method="post"><input type="submit" style="background-color:red;color:white;padding:3px;" value="Remove" OnClick="return confirm(\'Really remove from list?\');"></form></td></tr>';
				echo '<td></td></form><td><form action="nopa.php?w=Print&From='.$fromwhatnopa.'&TxnID='.$row['TxnID'].'" method="post"><input type="submit" style="background-color:blue;color:white;padding:3px;" value="Print"></form></td><td><form action="nopa.php?w=Remove&TxnID='.$row['TxnID'].'" method="post"><input type="submit" style="background-color:red;color:white;padding:3px;" value="Remove" OnClick="return confirm(\'Really remove from list?\');"></form></td></tr>';
				$colorcount++;
			}
			echo '</tbody></table>';
			
		}
		
	break; 
	
	case 'ClearData':
	
	$sql1='DELETE FROM hr_nopaholdingtable WHERE nopaID='.$_GET['From'].'';
	$stmt=$link->prepare($sql1); $stmt->execute();
	
	header("Location:".$_SERVER['HTTP_REFERER']);
	
	break;
	
	case 'Remove':
	
	$sql1='DELETE FROM hr_nopaholdingtable WHERE TxnID='.$_GET['TxnID'].'';
	$stmt=$link->prepare($sql1); $stmt->execute();
	
	header("Location:".$_SERVER['HTTP_REFERER']);
	
	break;
	
	case 'PrintAll':
	
	// $sql='SELECT ht.TxnID,RecommendedByNo,(SELECT (DateHired) + INTERVAL 6 MONTH + INTERVAL 1 DAY) AS DateofEffectivityReg,cp.Position,ht.IDNo,SalaryFrom,SalaryTo,FullName,(SELECT CONCAT(Nickname," ",UPPER(LEFT(MiddleName,1)),". ",SurName) FROM 1employees WHERE IDNo=ht.RecommendedByNo) AS RecommendedBy,(SELECT Position FROM attend_30currentpositions WHERE IDNo=ht.RecommendedByNo) AS RecommendedByPos,(SELECT Position FROM attend_30currentpositions WHERE IDNo=ht.VerifiedByNo) AS VerifiedByPos,(SELECT CONCAT(Nickname," ",UPPER(LEFT(MiddleName,1)),". ",SurName) FROM 1employees WHERE IDNo=ht.VerifiedByNo) AS VerifiedBy,cp.Branch,(SELECT MAX(DateofChange) FROM attend_2changeofpositions WHERE IDNo=cp.IDNo) AS DateofEffectivity,(SELECT MAX(DateofChange) FROM payroll_22rates WHERE IDNo=cp.IDNo) AS DateofEffectivitySalary,(SELECT Remarks FROM attend_2changeofpositions WHERE IDNo=cp.IDNo ORDER BY DateofChange DESC LIMIT 1) AS Remarks,(SELECT Remarks FROM payroll_22rates WHERE IDNo=cp.IDNo ORDER BY DateofChange DESC LIMIT 1) AS RemarksSalary,dept,p1.Position AS PositionFrom,p2.Position AS PositionTo,b1.Branch AS BranchFrom,b2.Branch AS BranchTo FROM hr_nopaholdingtable ht JOIN attend_30currentpositions cp ON ht.IDNo=cp.IDNo LEFT JOIN attend_0positions p1 ON ht.PositionIDFrom=p1.PositionID LEFT JOIN attend_0positions p2 ON ht.PositionIDTo=p2.PositionID LEFT JOIN 1branches b1 ON ht.BranchNoFrom=b1.BranchNo LEFT JOIN 1branches b2 ON ht.BranchNoTo=b2.BranchNo JOIN 1employees e ON ht.IDNo=e.IDNo WHERE nopaID='.$_GET['From'].'';
	$sql='SELECT ht.TxnID,RecommendedByNo,(SELECT (DateHired) + INTERVAL 6 MONTH + INTERVAL 1 DAY) AS DateofEffectivityReg,cp.Position,ht.IDNo,SalaryFrom,SalaryTo,FullName,(SELECT CONCAT(Nickname," ",UPPER(LEFT(MiddleName,1)),". ",SurName) FROM 1employees WHERE IDNo=ht.RecommendedByNo) AS RecommendedBy,(SELECT Position FROM attend_30currentpositions WHERE IDNo=ht.RecommendedByNo) AS RecommendedByPos,(SELECT Position FROM attend_30currentpositions WHERE IDNo=ht.VerifiedByNo) AS VerifiedByPos,(SELECT CONCAT(Nickname," ",UPPER(LEFT(MiddleName,1)),". ",SurName) FROM 1employees WHERE IDNo=ht.VerifiedByNo) AS VerifiedBy,cp.Branch,(SELECT MAX(DateofChange) FROM attend_2changeofpositions WHERE IDNo=cp.IDNo) AS DateofEffectivity,(SELECT MAX(DateofChange) FROM payroll_22rates WHERE IDNo=cp.IDNo) AS DateofEffectivitySalary,(SELECT Remarks FROM attend_2changeofpositions WHERE IDNo=cp.IDNo ORDER BY DateofChange DESC LIMIT 1) AS Remarks,(SELECT Remarks FROM payroll_22rates WHERE IDNo=cp.IDNo ORDER BY DateofChange DESC LIMIT 1) AS RemarksSalary,dept,p1.Position AS PositionFrom,p2.Position AS PositionTo,b1.Branch AS BranchFrom,b2.Branch AS BranchTo FROM hr_nopaholdingtable ht JOIN attend_30currentpositions cp ON ht.IDNo=cp.IDNo LEFT JOIN attend_0positions p1 ON ht.PositionIDFrom=p1.PositionID LEFT JOIN attend_0positions p2 ON ht.PositionIDTo=p2.PositionID LEFT JOIN 1branches b1 ON ht.BranchNoFrom=b1.BranchNo LEFT JOIN 1branches b2 ON ht.BranchNoTo=b2.BranchNo JOIN 1employees e ON ht.IDNo=e.IDNo WHERE nopaID='.$_GET['From'].'';

	$stmt=$link->query($sql); $resall=$stmt->fetchAll();
	
	
	foreach($resall as $res){
	$nopaform='';
	$nopaform.='<table style="font-size:11.5pt;font-family:Calibri;border-collapse:collapse;border:1px solid black;">';
	$nopaform.='<tr><td colspan=5 align="center" style="border:1px solid black;"><font style="font-size:15pt;padding:3px;"><b>NOTICE OF PERSONNEL ACTION</b></font></td></tr>';
	$nopaform.='<tr><td colspan=3 style="padding:3px;border:1px solid black;">'.$res['FullName'].' ('.$res['IDNo'].')</td><td colspan=2 style="padding:3px;border:1px solid black;">DATE: '.date('Y-m-d').'</td></tr>';
	$nopaform.='<tr><td colspan=5 style="padding:3px;border:1px solid black;">'.$res['Position'].' / '.$res['dept'].' / '.$res['Branch'].'</td></tr>';
	$nopaform.='<tr><td colspan=2 align="center" style="padding:3px;width:25%;border-right:1px solid black;"><b>Nature of Action</b></td><td align="center" style="padding:3px;border-right:1px solid black;width:25%;"><b>From</b></td><td align="center" style="padding:3px;border-right:1px solid black;width:25%;"><b>To</b></td><td align="center" style="padding:3px;width:25%;border-right:1px solid black;"><b>Remarks</b></td></tr>';
	
	
	$sqlcheckother=$sqlcheckothermain.$res['IDNo'].'';
	$stmtothers=$link->query($sqlcheckother); $resothers=$stmtothers->fetchAll();
	// echo $sqlcheckother;
	foreach($resothers AS $resother){
		$nopaform.='<tr><td colspan=2 style="padding:3px;font-size:12pt;border-right:1px solid black;" align="center">'.$resother['NOPAaction'].'</td><td align="center" style="padding:3px;font-size:12pt;border-right:1px solid black;">'.$resother['FromWhat'].'</td><td style="padding:3px;font-size:12pt;border-right:1px solid black;" align="center">'.$resother['ToWhat'].'</td><td style="padding:3px;border-right:1px solid black;">'.$resother['Remarks'].'</td></tr>';
	}

		// if($_GET['From']==1){
		// 	$nopaform.='<tr><td colspan=2 style="padding:25px;" align="center"><b>Promotion</b></td><td align="center">'.$res['PositionFrom'].'</td><td align="center">'.$res['PositionTo'].'</td></tr>';
		// }
		// if($_GET['From']==5){
		// 	$nopaform.='<tr><td colspan=2 style="padding:25px;" align="center"><b>Change in Position</b></td><td align="center">'.$res['PositionFrom'].'</td><td align="center">'.$res['PositionTo'].'</td></tr>';
		// }
		// if($_GET['From']==2){
		// 	$nopaform.='<tr><td colspan=2 style="padding:25px;" align="center"><b>Branch Transfer</b></td><td align="center">'.$res['BranchFrom'].'</td><td align="center">'.$res['BranchTo'].'</td></tr>';
		// }
		// if($_GET['From']==3){
		// 	$nopaform.='<tr><td colspan=2 style="padding:25px;" align="center"><b>Salary Adjustment</b></td><td align="center">'.$res['SalaryFrom'].'</td><td align="center">'.$res['SalaryTo'].'</td></tr>';
		// }
		// if($_GET['From']==4){
		// 	$nopaform.='<tr><td colspan=2 style="padding:25px;" align="center"><b>Regularization</b></td><td align="center"><b>Probationary Employment</b></td><td align="center"><b>Regular Employment</b></td></tr>';
		// }
		
		$effdate='';
	if($_GET['From']==1 OR $_GET['From']==2 OR $_GET['From']==5){
		$effdate=$res['DateofEffectivity'];
		//(SELECT )
	}
	if($_GET['From']==3){
		$effdate=$res['DateofEffectivitySalary'];
	}
	if($_GET['From']==4){
		$effdate=$res['DateofEffectivityReg'];
	}
	$nopaform.='<tr><td colspan=5 style="padding:3px;border:1px solid black;">Effective Date: '.$effdate.'</td></tr>';
		// $nopaform.='<tr><td colspan=2>Remarks</td><td colspan=2>Effective Date:</td></tr>';
		// if($_GET['From']==1 OR $_GET['From']==2 OR $_GET['From']==5){
		// 	$nopaform.='<tr><td colspan=2>&nbsp; &nbsp;'.$res['Remarks'].'</td><td colspan=2>&nbsp; &nbsp;'.$res['DateofEffectivity'].'</td></tr>';
		// }
		// if($_GET['From']==3){
		// 	$nopaform.='<tr><td colspan=2>&nbsp; &nbsp;'.$res['RemarksSalary'].'</td><td colspan=2>&nbsp; &nbsp;'.$res['DateofEffectivitySalary'].'</td></tr>';
		// }
		// if($_GET['From']==4){
		// 	$nopaform.='<tr><td colspan=2></td><td colspan=2>&nbsp; &nbsp;'.$res['DateofEffectivityReg'].'</td></tr>';
		// }
		
		// $nopaform.='<tr><td>Recommended by:</td><td>Verified by:</td><td colspan=3>Approved by:</td></tr>';
		// $nopaform.='<tr><td style="padding-top:40px;" align="center"><b>'.strtoupper($res['RecommendedBy']).'</b></td><td  style="padding-top:40px;" align="center"><b>'.strtoupper($res['VerifiedBy']).'</b></td><td colspan=3 style="padding-top:40px;" align="center"><b>RC EUSEBIO'.($res['RecommendedByNo']<>'1002'?'/JY EUSEBIO':'').'</b></td></tr>';
		// $nopaform.='<tr><td align="center">'.strtoupper($res['RecommendedByPos']).'</td><td align="center">'.strtoupper($res['VerifiedByPos']).'</td><td colspan=3 align="center">PRESIDENT'.($res['RecommendedByNo']<>'1002'?'/EVP':'').'</td></tr>';

		$nopaform.='<tr>';
		$nopaform.='<td colspan=6>';
		$nopaform.='<table width="100%" style="border-collapse:collapse;">';
		$nopaform.='<tr><td style="padding:3px;border-right:1px solid black;">Recommended by:</td><td style="padding:3px;border-right:1px solid black;">Verified by:</td><td style="padding:3px;">Approved by:</td></tr>';
		$nopaform.='<tr><td style="padding:3px;border-right:1px solid black;" align="center"><br><br><b>'.strtoupper($res['RecommendedBy']).'</b></td><td  style="padding:3px;border-right:1px solid black;" align="center"><br><br><b>'.strtoupper($res['VerifiedBy']).'</b></td>';
		$nopaform.='<td style="padding:3px;" align="center"><br><br><b>RC EUSEBIO'.($res['RecommendedByNo']<>'1002'?'/JY EUSEBIO':'').'</b></td></tr>';
		$nopaform.='<tr><td align="center" style="padding:3px;border-right:1px solid black;">'.($res['RecommendedByPos']).'</td>';
		$nopaform.='<td align="center" style="padding:3px;border-right:1px solid black;">'.($res['VerifiedByPos']).'</td>';
		$nopaform.='<td align="center" style="padding:3px;">President'.($res['RecommendedByNo']<>'1002'?'/EVP':'').'</td></tr>';
		$nopaform.='</table>';
		$nopaform.='</td>';
		$nopaform.='</tr>';


		$nopaform.='<tr><td colspan="3" rowspan="2" style="padding:3px;border:1px solid black;"><font style="font-size:9pt;">Employee hereby expressly acknowledges receipt of and undertakes to abide by the provisions of his/her Job Description, Company Code of Conduct and such other policies, guidelines, rules and regulations the company may prescribe.</font></td><td colspan="2" rowspan="2" valign="top" style="padding:3px;border:1px solid black;">Acknowledged by:<br><br><br></td></tr>';
		$nopaform.='<tr></tr><tr><td colspan="3" style="padding:3px;border:1px solid black;"><font style="font-size:9pt;">Distribution: 1 – Employee 2 – 201 file</font></td><td colspan="2" align="center" style="padding:3px;border:1px solid black;">Employee</td></tr>';
		
		$nopaform.='</table>';
		
		echo '<br><br>';
		echo $nopaform;
		echo '<br><br><br><br><br><br>';
		echo $nopaform;
		echo '<p style="page-break-before: always"></p>';
	}
	break;
	
	
	case 'Print':
	$txnid=intval($_GET['TxnID']);
	
	// $sql='SELECT ht.TxnID,RecommendedByNo,(SELECT (DateHired) + INTERVAL 6 MONTH + INTERVAL 1 DAY) AS DateofEffectivityReg,cp.Position,ht.IDNo,SalaryFrom,SalaryTo,FullName,(SELECT CONCAT(Nickname," ",UPPER(LEFT(MiddleName,1)),". ",SurName) FROM 1employees WHERE IDNo=ht.RecommendedByNo) AS RecommendedBy,(SELECT Position FROM attend_30currentpositions WHERE IDNo=ht.RecommendedByNo) AS RecommendedByPos,(SELECT Position FROM attend_30currentpositions WHERE IDNo=ht.VerifiedByNo) AS VerifiedByPos,(SELECT CONCAT(Nickname," ",UPPER(LEFT(MiddleName,1)),". ",SurName) FROM 1employees WHERE IDNo=ht.VerifiedByNo) AS VerifiedBy,cp.Branch,(SELECT MAX(DateofChange) FROM attend_2changeofpositions WHERE IDNo=cp.IDNo) AS DateofEffectivity,(SELECT MAX(DateofChange) FROM payroll_22rates WHERE IDNo=cp.IDNo) AS DateofEffectivitySalary,(SELECT Remarks FROM attend_2changeofpositions WHERE IDNo=cp.IDNo ORDER BY DateofChange DESC LIMIT 1) AS Remarks,(SELECT Remarks FROM payroll_22rates WHERE IDNo=cp.IDNo ORDER BY DateofChange DESC LIMIT 1) AS RemarksSalary,dept,p1.Position AS PositionFrom,p2.Position AS PositionTo,b1.Branch AS BranchFrom,b2.Branch AS BranchTo FROM hr_nopaholdingtable ht JOIN attend_30currentpositions cp ON ht.IDNo=cp.IDNo LEFT JOIN attend_0positions p1 ON ht.PositionIDFrom=p1.PositionID LEFT JOIN attend_0positions p2 ON ht.PositionIDTo=p2.PositionID LEFT JOIN 1branches b1 ON ht.BranchNoFrom=b1.BranchNo LEFT JOIN 1branches b2 ON ht.BranchNoTo=b2.BranchNo JOIN 1employees e ON ht.IDNo=e.IDNo WHERE TxnID='.$txnid.'';
	// $stmt=$link->query($sql); $res=$stmt->fetch();

	$sql='SELECT ht.TxnID,RecommendedByNo,(SELECT (DateHired) + INTERVAL 6 MONTH + INTERVAL 1 DAY) AS DateofEffectivityReg,cp.Position,ht.IDNo,SalaryFrom,SalaryTo,FullName,(SELECT CONCAT(Nickname," ",UPPER(LEFT(MiddleName,1)),". ",SurName) FROM 1employees WHERE IDNo=ht.RecommendedByNo) AS RecommendedBy,(SELECT Position FROM attend_30currentpositions WHERE IDNo=ht.RecommendedByNo) AS RecommendedByPos,(SELECT Position FROM attend_30currentpositions WHERE IDNo=ht.VerifiedByNo) AS VerifiedByPos,(SELECT CONCAT(Nickname," ",UPPER(LEFT(MiddleName,1)),". ",SurName) FROM 1employees WHERE IDNo=ht.VerifiedByNo) AS VerifiedBy,cp.Branch,(SELECT MAX(DateofChange) FROM attend_2changeofpositions WHERE IDNo=cp.IDNo) AS DateofEffectivity,(SELECT MAX(DateofChange) FROM payroll_22rates WHERE IDNo=cp.IDNo) AS DateofEffectivitySalary,(SELECT Remarks FROM attend_2changeofpositions WHERE IDNo=cp.IDNo ORDER BY DateofChange DESC LIMIT 1) AS Remarks,(SELECT Remarks FROM payroll_22rates WHERE IDNo=cp.IDNo ORDER BY DateofChange DESC LIMIT 1) AS RemarksSalary,dept,p1.Position AS PositionFrom,p2.Position AS PositionTo,b1.Branch AS BranchFrom,b2.Branch AS BranchTo FROM hr_nopaholdingtable ht JOIN attend_30currentpositions cp ON ht.IDNo=cp.IDNo LEFT JOIN attend_0positions p1 ON ht.PositionIDFrom=p1.PositionID LEFT JOIN attend_0positions p2 ON ht.PositionIDTo=p2.PositionID LEFT JOIN 1branches b1 ON ht.BranchNoFrom=b1.BranchNo LEFT JOIN 1branches b2 ON ht.BranchNoTo=b2.BranchNo JOIN 1employees e ON ht.IDNo=e.IDNo WHERE TxnID='.$txnid.'';
	$stmt=$link->query($sql); $res=$stmt->fetch();
	
	
	$nopaform='';
	$nopaform.='<table style="font-size:11.5pt;font-family:Calibri;border-collapse:collapse;border:1px solid black;">';
	$nopaform.='<tr><td colspan=5 align="center" style="border:1px solid black;"><font style="font-size:15pt;padding:3px;"><b>NOTICE OF PERSONNEL ACTION</b></font></td></tr>';
	$nopaform.='<tr><td colspan=3 style="padding:3px;border:1px solid black;">'.$res['FullName'].' ('.$res['IDNo'].')</td><td colspan=2 style="padding:3px;border:1px solid black;">DATE: '.date('Y-m-d').'</td></tr>';
	$nopaform.='<tr><td colspan=5 style="padding:3px;border:1px solid black;">'.$res['Position'].' / '.$res['dept'].' / '.$res['Branch'].'</td></tr>';
	$nopaform.='<tr><td colspan=2 align="center" style="padding:3px;border-right:1px solid black;width:25%;"><b>Nature of Action</b></td><td align="center" style="padding:3px;border-right:1px solid black;width:25%;"><b>From</b></td><td align="center" style="padding:3px;border-right:1px solid black;width:25%;"><b>To</b></td><td align="center" style="padding:3px;width:25%;border-right:1px solid black;"><b>Remarks</b></td></tr>';
	
	
	$sqlcheckother=$sqlcheckothermain.$res['IDNo'].'';
	$stmtothers=$link->query($sqlcheckother); $resothers=$stmtothers->fetchAll();
	// echo $sqlcheckother;
	foreach($resothers AS $resother){
		$nopaform.='<tr><td colspan=2 style="padding:3px;font-size:12pt;border-right:1px solid black;" align="center">'.$resother['NOPAaction'].'</td><td align="center" style="padding:3px;font-size:12pt;border-right:1px solid black;">'.$resother['FromWhat'].'</td><td style="padding:3px;font-size:12pt;border-right:1px solid black;" align="center">'.$resother['ToWhat'].'</td><td style="padding:3px;border-right:1px solid black;">'.$resother['Remarks'].'</td></tr>';
	}
	
	$effdate='';
	if($_GET['From']==1 OR $_GET['From']==2 OR $_GET['From']==5){
		$effdate=$res['DateofEffectivity'];
		//(SELECT )
	}
	if($_GET['From']==3){
		$effdate=$res['DateofEffectivitySalary'];
	}
	if($_GET['From']==4){
		$effdate=$res['DateofEffectivityReg'];
	}
	$nopaform.='<tr><td colspan=5 style="padding:3px;border:1px solid black;">Effective Date: '.$effdate.'</td></tr>';
	
		
	// $nopaform.='<tr><td style="padding:3px;border-right:1px solid black;">Recommended by:</td><td style="padding:3px;border-right:1px solid black;">Verified by:</td><td colspan=3 style="padding:3px;">Approved by:</td></tr>';
	// $nopaform.='<tr><td style="padding:3px;width:250px;border-right:1px solid black;" align="center"><br><br><b>'.strtoupper($res['RecommendedBy']).'</b></td><td  style="padding:3px;width:250px;border-right:1px solid black;" align="center"><br><br><b>'.strtoupper($res['VerifiedBy']).'</b></td>';
	// $nopaform.='<td colspan=3 style="padding:3px;" align="center"><br><br><b>RC EUSEBIO'.($res['RecommendedByNo']<>'1002'?'/JY EUSEBIO':'').'</b></td></tr>';
	// $nopaform.='<tr><td align="center" style="padding:3px;border-right:1px solid black;">'.($res['RecommendedByPos']).'</td>';
	// $nopaform.='<td align="center" style="padding:3px;border-right:1px solid black;">'.($res['VerifiedByPos']).'</td>';
	// $nopaform.='<td colspan=3 align="center" style="padding:3px;">President'.($res['RecommendedByNo']<>'1002'?'/EVP':'').'</td></tr>';

	$nopaform.='<tr>';
	$nopaform.='<td colspan=6>';
	$nopaform.='<table width="100%" style="border-collapse:collapse;">';
	$nopaform.='<tr><td style="padding:3px;border-right:1px solid black;">Recommended by:</td><td style="padding:3px;border-right:1px solid black;">Verified by:</td><td style="padding:3px;">Approved by:</td></tr>';
	$nopaform.='<tr><td style="padding:3px;border-right:1px solid black;" align="center"><br><br><b>'.strtoupper($res['RecommendedBy']).'</b></td><td  style="padding:3px;border-right:1px solid black;" align="center"><br><br><b>'.strtoupper($res['VerifiedBy']).'</b></td>';
	$nopaform.='<td style="padding:3px;" align="center"><br><br><b>RC EUSEBIO'.($res['RecommendedByNo']<>'1002'?'/JY EUSEBIO':'').'</b></td></tr>';
	$nopaform.='<tr><td align="center" style="padding:3px;border-right:1px solid black;">'.($res['RecommendedByPos']).'</td>';
	$nopaform.='<td align="center" style="padding:3px;border-right:1px solid black;">'.($res['VerifiedByPos']).'</td>';
	$nopaform.='<td align="center" style="padding:3px;">President'.($res['RecommendedByNo']<>'1002'?'/EVP':'').'</td></tr>';
	$nopaform.='</table>';
	$nopaform.='</td>';
	$nopaform.='</tr>';
	
	
	
	$nopaform.='<tr><td colspan="3" rowspan="2" style="padding:3px;border:1px solid black;"><font style="font-size:9pt;">Employee hereby expressly acknowledges receipt of and undertakes to abide by the provisions of his/her Job Description, Company Code of Conduct and such other policies, guidelines, rules and regulations the company may prescribe.</font></td><td colspan="2" rowspan="2" valign="top" style="padding:3px;border:1px solid black;">Acknowledged by:<br><br><br></td></tr>';
	$nopaform.='<tr></tr><tr><td colspan="3" style="padding:3px;border:1px solid black;"><font style="font-size:9pt;">Distribution: 1 – Employee 2 – 201 file</font></td><td colspan="2" align="center" style="padding:3px;border:1px solid black;">Employee</td></tr>';
	
	$nopaform.='</table>';
	
	
	echo '<br><br>';
	echo $nopaform;
	echo '<br><br><br><br><br><br>';
	echo $nopaform;
	break;
	
	case 'Update':
	$txnid=intval($_GET['TxnID']);
	
	if($_GET['From']==1 OR $_GET['From']==5){
		$sql1='UPDATE hr_nopaholdingtable SET PositionIDFrom='.$_POST['PositionIDFrom'].',PositionIDTo='.$_POST['PositionIDTo'].' WHERE TxnID='.$txnid.'';
		$stmt=$link->prepare($sql1); $stmt->execute();
	}
	if($_GET['From']==2){
		$sql1='UPDATE hr_nopaholdingtable SET BranchNoFrom='.$_POST['BranchNoFrom'].',BranchNoTo='.$_POST['BranchNoTo'].' WHERE TxnID='.$txnid.'';
		$stmt=$link->prepare($sql1); $stmt->execute();
	}
	if($_GET['From']==3 OR $_GET['From']==4){
	}
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;
	
	case 'SetMonthSession':
	
	
	if (isset($_POST['btnSetMonth'])){
				$_SESSION['dDateFrom']=$_POST['DateFrom'];
				$_SESSION['dDateTo']=$_POST['DateTo'];
			}
			
	header("Location:".$_SERVER['HTTP_REFERER']);		
	
	break;
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body>
<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>
