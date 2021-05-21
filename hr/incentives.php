<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

// check if allowed
$allowed=array(2014);
$allow=0;
if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}
allowed:
// end of check

$showbranches=false; include_once('../switchboard/contents.php');
include_once('../backendphp/layout/linkstyle.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;


$whichqry=isset($_GET['w'])?$_GET['w']:'List';
echo '<br><div id="section" style="display: block;">

    <div><a id=\'link\' href="incentives.php">Incentives Per Month</a>
        <a id=\'link\' href="incentives.php?w=CalcIncentives">Calculate Incentives (Branch - Monthly)</a>
        ';
		//<a id=\'link\' href="incentives.php?w=ClassPerQuarter">Calculate Incentives (STL - Quarterly)</a>
	// $smonth='';
	// if(isset($_GET['MonthNo'])){
		// $smonth=$_GET['MonthNo'];
	// }	
if (allowedToOpen(2017,'1rtc')) { echo '<a id=\'link\' href="incentives.php?w=SendToVoucher&MonthNo=">Send to Voucher</a>';}
echo '<br><br><br>';

switch ($whichqry){

case 'List':
if (!allowedToOpen(2014,'1rtc')) {   echo 'No permission'; exit;}


echo '<form action="incentives.php" method="POST">';

if(!isset($_POST['MoreFields'])){
	$columnnames=array('Month','Total','Posted?','Approved?','Sent?');
	$button='<input type="submit" name="MoreFields" value="Show More Fields">';
} else {
	$columnnames=array('Month','Total','Posted?','PostedBy','PostedTS','Approved?','ApprovedBy','ApprovedTS','Sent?','SentBy','SentTS');
	$button='<input type="submit" name="LessFields" value="Show Less Fields">';
}
	
echo $button;

echo '</form>';
	
	$sql='SELECT im.TxnID,
			(CASE 
				WHEN im.MonthNo=1 THEN "January"
				WHEN im.MonthNo=2 THEN "February"
				WHEN im.MonthNo=3 THEN "March"
				WHEN im.MonthNo=4 THEN "April"
				WHEN im.MonthNo=5 THEN "May"
				WHEN im.MonthNo=6 THEN "June"
				WHEN im.MonthNo=7 THEN "July"
				WHEN im.MonthNo=8 THEN "August"
				WHEN im.MonthNo=9 THEN "September"
				WHEN im.MonthNo=10 THEN "October"
				WHEN im.MonthNo=11 THEN "November"
				ELSE "December"
			END)
			AS Month, IF(Posted=1,"Yes","") AS `Posted?`,IF(Approved=1,"Yes","") AS `Approved?`,IF(Sent=1,"Yes","") AS `Sent?`,IF(Posted=1,id.Nickname,"") AS PostedBy, IF(Posted=1,PostedTS,"") AS PostedTS,FORMAT(SUM(Amount),0) AS Total,IF(Approved=1,id2.Nickname,"") AS ApprovedBy, IF(Approved=1,ApprovedTS,"") AS ApprovedTS,IF(Sent=1,id3.Nickname,"") AS SentBy, IF(Sent=1,SentTS,"") AS SentTS
		FROM hr_1incentivemain im LEFT JOIN 1_gamit.0idinfo id ON im.PostedByNo=id.IDNo LEFT JOIN 1_gamit.0idinfo id2 ON im.ApprovedByNo=id2.IDNo LEFT JOIN 1_gamit.0idinfo id3 ON im.SentByNo=id3.IDNo LEFT JOIN hr_2incentivesub `is` ON im.MonthNo=`is`.MonthNo WHERE im.MonthNo<=12 GROUP BY im.MonthNo';
		
		
	$title='Incentives per Month (Branch)';
	$editprocess="incentives.php?w=CalcIncentives&MonthNo="; $editprocesslabel='Lookup';
	
	echo '<div><div style="'.((isset($_POST['MoreFields']))?"":"float:left;").'">';
	include('../backendphp/layout/displayastablenosort.php');
	echo '</div><br><div style="'.((isset($_POST['MoreFields']))?"":"margin-left:40%;").'">';
	// $sql='SELECT im.TxnID,
			// (CASE 
				// WHEN im.MonthNo=21 THEN "1st Quarter"
				// WHEN im.MonthNo=22 THEN "2nd Quarter"
				// WHEN im.MonthNo=23 THEN "3rd Quarter"
				// ELSE "4th Quarter"
			// END)
			// AS Month, IF(Posted=1,"Yes","") AS `Posted?`,IF(Approved=1,"Yes","") AS `Approved?`,IF(Sent=1,"Yes","") AS `Sent?`,IF(Posted=1,id.Nickname,"") AS PostedBy, IF(Posted=1,PostedTS,"") AS PostedTS,FORMAT(SUM(Amount),0) AS Total,IF(Approved=1,id2.Nickname,"") AS ApprovedBy, IF(Approved=1,ApprovedTS,"") AS ApprovedTS,IF(Sent=1,id3.Nickname,"") AS SentBy, IF(Sent=1,SentTS,"") AS SentTS
		// FROM hr_1incentivemain im LEFT JOIN 1_gamit.0idinfo id ON im.PostedByNo=id.IDNo LEFT JOIN 1_gamit.0idinfo id2 ON im.ApprovedByNo=id2.IDNo LEFT JOIN 1_gamit.0idinfo id3 ON im.SentByNo=id3.IDNo LEFT JOIN hr_2incentivesub `is` ON im.MonthNo=`is`.MonthNo WHERE im.MonthNo IN (21,22,23,24) GROUP BY im.MonthNo';
		
		// echo $sql;
	// $title='Incentives per Quarter (STL)';
	// $editprocess="incentives.php?w=CalcIncentivesSTL&MonthNo="; $editprocesslabel='Lookup';
	// include('../backendphp/layout/displayastablenosort.php'); 
	
echo '</div></div>';
   break;
   
   
   
   case 'CalcIncentives':
   if (!allowedToOpen(array(2014,2015,2016,20161),'1rtc')) { echo 'No permission'; exit;}
   echo ' <style>
      blink {
        animation: blinker 1s linear infinite;
        color: #FF0000;
       }
      @keyframes blinker {  
        50% { opacity: 0; }
       }
    </style>';
	
	$title='Calculate Incentives - Branch';
	
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br>';
	
	$txndate=(isset($_REQUEST['MonthNo'])?intval($_REQUEST['MonthNo']):(date('m')));
	$txndate=str_pad($txndate,2,'0',STR_PAD_LEFT);
   
	       echo '<form action="incentives.php?w=CalcIncentives" method="POST">MonthNo: <input type="text" name="MonthNo" size="5" value="'.$txndate.'"><input type="submit" name="btnLookup" value="Lookup"> <input type="submit" name="btnCalculate" value="Calculate"></form><br>';
	/* if($_REQUEST['MonthNo']>12){
		header('Location:incentives.php?w=CalcIncentivesSTL&MonthNo='.$_REQUEST['MonthNo']);
		exit();
	} */
	
	$sql='SELECT his.*,Resigned,NoOfSaleDays AS TotalDays,Units,Branch,Score,Position,NoOfDays,CONCAT(Nickname," ",Surname) AS FullName,(CASE
			WHEN (`his`.PositionID=32 OR (`his`.PositionID IN (37,81) AND (SELECT COUNT(IDNo) FROM attend_30currentpositions WHERE BranchNo=his.BranchNo AND PositionID=32)=0)) THEN IF(Score>=115,400,350)
			WHEN `his`.PositionID IN (37,81) THEN IF(Score>=115,300,250)
			ELSE 
				IF(Score>=115,200,150)
		END) AS RatePerUnit,IF(NoOfDays>=NoOfSaleDays,"100",TRUNCATE((((SELECT TRUNCATE(IFNULL(SUM(Qty*UnitPrice),0),2) FROM invty_2sale sm JOIN invty_2salesub ss ON sm.TxnID=ss.TxnID WHERE BranchNo=his.BranchNo AND MONTH(`Date`)=his.MonthNo AND `Date` IN (SELECT DateToday FROM attend_2attendance WHERE IDNo=his.IDNo AND BranchNo=his.BranchNo AND LeaveNo IN (11,15)))/(SELECT TRUNCATE(IFNULL(SUM(Qty*UnitPrice),0),2) FROM invty_2sale sm JOIN invty_2salesub ss ON sm.TxnID=ss.TxnID WHERE BranchNo=his.BranchNo AND MONTH(`Date`)=his.MonthNo))*100),2))
         AS PercentSale FROM hr_2incentivesub his JOIN 1branches b ON his.BranchNo=b.BranchNo JOIN acctg_6targetscores ts ON `his`.BranchNo=`ts`.BranchNo AND `his`.MonthNo=ts.MonthNo JOIN 1employees e ON `his`.IDNo=e.IDNo JOIN attend_0positions p ON his.PositionID=p.PositionID JOIN attend_1joblevel jl ON jl.JobLevelNo=p.JobLevelNo WHERE his.MonthNo='.$txndate.' ORDER BY Branch,JLID DESC,FullName';
        
	
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
	if(isset($_POST['btnCalculate'])){
		if($stmt->rowCount()==0){
		
		// History of all branch position and branches
		/* $sqltemp='CREATE TEMPORARY TABLE PositionBranchHistory AS
SELECT e.IDNo,cop.AssignedBranchNo,cop.NewPositionID,DateofChange FROM attend_2changeofpositions cop JOIN 1employees e ON e.IDNo=cop.IDNo JOIN 1branches b ON cop.AssignedBranchNo=b.BranchNo WHERE Pseudobranch=0 AND MONTH(DateofChange)<='.$txndate.' AND YEAR(DateofChange)='.$currentyr.' ORDER BY IDNo,DateofChange;'; */
		$sqltemp='CREATE TEMPORARY TABLE assignmenthistory AS
 SELECT 
        `e`.`IDNo`,AssignedBranchNo,NewPositionID,DateofChange AS DateofEffectivity
    FROM
    
        `attend_0positions` `p`
        JOIN `attend_2changeofpositions` `cp` ON (`p`.`PositionID` = `cp`.`NewPositionID`)
        JOIN `1employees` e ON e.IDNo=cp.IDNo
    WHERE
         `cp`.`TxnID` IN (SELECT 
                `cp`.`TxnID`
				FROM
                (`attend_2changeofpositions` `cp`
                JOIN (SELECT cop.`DateofChange` AS MaxOfDateofChange,cop.AssignedBranchNo, cp.`IDNo` AS `IDNo`,`NewPositionID` FROM `attend_2changeofpositions` cop JOIN attend_30latestpositionsinclresigned cp ON cop.IDNo=cp.IDNo AND cop.AssignedBranchNo=cp.AssignedBranchNo AND cop.NewPositionID=cp.PositionID WHERE cop.`DateofChange` <= LAST_DAY("'.$currentyr.'-'.$txndate.'-01") GROUP BY cop.`IDNo`,`NewPositionID`) `lp` ON (`cp`.`IDNo` = `lp`.`IDNo`))
            WHERE
                `cp`.`DateofChange` = `lp`.`MaxOfDateofChange`) AND deptid=10 AND AssignedBranchNo IN (SELECT BranchNo FROM acctg_6targetscores WHERE Score>=100 AND MonthNo='.$txndate.') ;';
// echo $sqltemp.'<br><br>';
		$stmttemp=$link->prepare($sqltemp); $stmttemp->execute();
		
$sqltemp='CREATE TEMPORARY TABLE unionall AS
SELECT t.IDNo,AssignedBranchNo,NewPositionID,"'.$currentyr.'-'.$txndate.'-01" AS DateofEffectivity FROM assignmenthistory t JOIN 
(SELECT MAX(`DateofEffectivity`) AS `MaxOfDateofChange`,`IDNo` FROM `assignmenthistory`
    WHERE  `DateofEffectivity` < "'.$currentyr.'-'.$txndate.'-01" GROUP BY `IDNo`) m ON t.IDNo=m.IDNo AND m.MaxOfDateofChange=t.DateofEffectivity
    UNION SELECT IDNo,AssignedBranchNo,NewPositionID,IFNULL(DateofEffectivity,NULL) AS DateofEffectivity FROM assignmenthistory WHERE YEAR(DateOfEffectivity)='.$currentyr.' AND MONTH(DateOfEffectivity)='.$txndate.';';
	// echo $sqltemp.'<br><br>';

		$stmttemp=$link->prepare($sqltemp); $stmttemp->execute();
		
		
		if($txndate==12){
			$sqltemp='CREATE TEMPORARY TABLE attend_2attendanceTEMP AS SELECT * FROM attend_2attendance WHERE MONTH(DateToday)='.$txndate.' AND YEAR(DateToday)='.$currentyr.' AND BranchNo IN (SELECT BranchNo FROM acctg_6targetscores WHERE Score>=100 AND MonthNo='.$txndate.') UNION SELECT * FROM '.$nextyr.'_1rtc.attend_2attendance WHERE MONTH(DateToday)='.$txndate.' AND YEAR(DateToday)='.$currentyr.' AND BranchNo IN (SELECT BranchNo FROM acctg_6targetscores WHERE Score>=100 AND MonthNo='.$txndate.');';
			$stmttemp=$link->prepare($sqltemp); $stmttemp->execute();
			$tabletouse='attend_2attendanceTEMP';
		} else {
			$tabletouse='attend_2attendance';
		}
		
		$sqltemp='CREATE TEMPORARY TABLE attend_2attendanceEdited AS
SELECT IDNo,TimeIn,DateToday,LeaveNo,BranchNo,if(isnull((SELECT NewPositionID FROM unionall WHERE IDNo=a.IDNo AND DateofEffectivity=a.DateToday)),@n,(@n:=(SELECT NewPositionID FROM unionall WHERE IDNo=a.IDNo AND DateofEffectivity=a.DateToday))) PositionID FROM '.$tabletouse.' a join(select @n:="") n WHERE MONTH(`DateToday`)='.$txndate.' AND YEAR(`DateToday`)='.$currentyr.' AND BranchNo IN (SELECT BranchNo FROM acctg_6targetscores WHERE Score>=100 AND MonthNo='.$txndate.') ORDER BY IDNo;';
		// echo $sqltemp.'<br><br>';
$stmttemp=$link->prepare($sqltemp); $stmttemp->execute();
	
		

$sql0='INSERT INTO hr_2incentivesub (MonthNo,BranchNo,IDNo,PositionID,NoOfDays,Amount,Remarks,EncodedByNo,TimeStamp)

 SELECT '.$txndate.' AS MonthNo, b.BranchNo,e.IDNo,PositionID, Count(DateToday) AS NoOfDays,
 
 
 ROUND(( (( ( (CASE 
 
 WHEN (PositionID=32 OR (PositionID IN (37,81) AND (SELECT COUNT(IDNo) FROM attend_2attendanceEdited WHERE BranchNo=b.BranchNo AND PositionID=32)=0)) THEN IF(Score>=115,400,350) 
 
 WHEN PositionID IN (37,81) THEN IF(Score>=115,300,250)

ELSE IF(Score>=115,200,150) END)


* Units))*

IF(COUNT(DateToday)>=NoOfSaleDays,1,



((SELECT TRUNCATE(IFNULL(SUM(Qty*UnitPrice),0),2) FROM invty_2sale sm JOIN invty_2salesub ss ON sm.TxnID=ss.TxnID WHERE BranchNo=a.BranchNo AND MONTH(`Date`)='.$txndate.' AND `Date` IN (SELECT DateToday FROM '.$tabletouse.' WHERE IDNo=e.IDNo AND BranchNo=a.BranchNo AND LeaveNo IN (11,15)))/(SELECT TRUNCATE(IFNULL(SUM(Qty*UnitPrice),0),2) FROM invty_2sale sm JOIN invty_2salesub ss ON sm.TxnID=ss.TxnID WHERE BranchNo=a.BranchNo AND MONTH(`Date`)='.$txndate.'))


))),0) AS Amount,

IF(e.Resigned=1,"Resigned","") AS Remarks,'.$_SESSION['(ak0)'].' AS EncodedByNo,NOW() AS TimeStamp FROM 1employees e  JOIN `attend_2attendanceEdited` a ON e.IDNo = a.IDNo
JOIN `1branches` as b ON b.BranchNo = a.BranchNo 
JOIN acctg_6targetscores ts ON b.BranchNo=ts.BranchNo
JOIN acctg_2salemain asm ON asm.Date=a.DateToday AND asm.BranchNo=a.BranchNo
WHERE MONTH(DateToday)='.$txndate.' AND PseudoBranch=0 AND Score>=100 AND ts.MonthNo='.$txndate.' AND ((TO_DAYS(CURRENT_TIMESTAMP()) - TO_DAYS(`e`.`DateHired`)) / 365.25)>.25 AND (TimeIn IS NOT NULL OR LeaveNo IN (12,13,15)) 
GROUP BY e.IDNo,a.BranchNo,PositionID ORDER BY IDNo,Branch;
';
	// echo $sql0; exit();
		$stmt0=$link->prepare($sql0); $stmt0->execute();
			echo '<h3 style="color:green;">Calculated Successfully.</h3><a href="incentives.php?w=CalcIncentives&MonthNo='.$txndate.'">Click here.</a>';
		
		exit();
		}
	} else {
		if($stmt->rowCount()==0){
			echo 'No records.'; exit();
		}
	}
	$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
	$rcolor[1]="FFFFFF";
	echo '<style>.hoverstyle tr:hover td{
			background-color:#fff5cd;
		}</style>';
						
	$oldbranch='';
	echo '<table style="font-size:9pt;"><thead>
	<td>Score</td><td>  100.00%  </td><td>  115.00%</td></thead>
	<tr><td>Branch Head, Branch OIC, & Junior Branch Head (if there is NO Branch Head)  </td><td>  350  </td><td>  400</td></tr>
	<tr><td>Junior Branch Head (if there is a Branch Head)  </td><td>  250  </td><td>  300</td></tr>
	<tr><td>Branch Personnel  </td><td>  150  </td><td>  200</td></tr>
	</table><br>';
	// echo 'Notes *<br>1. ALL absences of branch personnel will be deducted from incentive, except for BH, JBH, OIC who are allowed 2 absences for NCR, or 3 absences for Provincial.<br>2. Only people who have reached 15 days in a branch is eligible for incentives from that branch.<br><br>';
	echo 'Notes *<br>1. Prorated deductions will be done for all absences.  Restdays are not considered as absences.<br>2. If temporarily assigned to other branches, prorated values will be given based on days assigned. This can be a combination of different branches that have reached targets.<br>3. Prorated values are based on daily sales values, <u>not</u> attendance days.<br><br>';
	$sqlb='SELECT Posted,Approved,Sent FROM hr_1incentivemain WHERE MonthNo='.$txndate.'';
	$stmtb=$link->query($sqlb); $resultb=$stmtb->fetch();
	
	//Post
	if(allowedToOpen(2014,'1rtc')){
		if($resultb['Posted']==0 AND $resultb['Approved']==0 AND $resultb['Sent']==0){
			echo '<div style="width:90%;"><div style="float:left;"><form action="incentives.php?w=PostUnpost&MonthNo='.$txndate.'" method="POST"><input style="background-color:yellow;color:#000000;padding:2px;" type="submit" name="btnPost" value="I have verified the values. Post" OnClick="return confirm(\'Verified?\');"></form></div><div style="float:right">';
			
			if(allowedToOpen(2015,'1rtc')){
				echo '<form action="incentives.php?w=DeleteData&MonthNo='.$txndate.'" method="POST"><input style="background-color:#d8d8d8;color:#000000;padding:2px;" type="submit" name="btnDelete" value="Delete this Data" OnClick="return confirm(\'Delete this Data?\');"></form></div></div><br><br>';
			} else {
				echo '</div></div><br><br>';
			}
			goto here;
		}
	}
	
	//Approve
	if(allowedToOpen(2015,'1rtc')){
		if($resultb['Posted']==1 AND $resultb['Approved']==0 AND $resultb['Sent']==0){
			echo '<div style="width:90%;"><div style="float:left;"><form action="incentives.php?w=Approve&MonthNo='.$txndate.'" method="POST"><input style="background-color:blue;color:#ffffff;padding:2px;" type="submit" name="btnPost" value="Approve Incentives" OnClick="return confirm(\'Approve?\');"></form></div><div style="float:right">';
			
			
			echo '<form action="incentives.php?w=SetAsInc&MonthNo='.$txndate.'" method="POST"><input style="background-color:#d8d8d8;color:#000000;padding:2px;" type="submit" name="btnSetAsInc" value="Set As Incomplete" OnClick="return confirm(\'Set As Incomplete?\');"></form></div></div><br><br>';
			goto here;
		}
	}
	//Send to special credits
	if(allowedToOpen(2016,'1rtc')){
		if($resultb['Posted']==1 AND $resultb['Approved']==1 AND $resultb['Sent']==0){
			echo '<div style="width:90%;"><div style="float:left;"><form action="incentives.php?w=SendToSpecialCredits&MonthNo='.$txndate.'" method="POST">Batch: <input type="text" value="01" name="Batch" size="10"> <input style="background-color:green;color:#ffffff;padding:2px;" type="submit" name="btnSend" value="Send To Special Credits" OnClick="return confirm(\'Send To Special Credits?\');"></form></div><div style="float:right">';
			
			echo '<form action="incentives.php?w=SetAsIncApprove&MonthNo='.$txndate.'" method="POST"><input style="background-color:#d8d8d8;color:#000000;padding:2px;" type="submit" name="btnSetAsIncApprove" value="Set As Incomplete" OnClick="return confirm(\'Set As Incomplete?\');"></form></div></div><br><br>';
			goto here;
		}
	}
	
	//Send Inc final
	if(allowedToOpen(20161,'1rtc')){
		if($resultb['Posted']==1 AND $resultb['Approved']==1 AND $resultb['Sent']==1){
			echo '<div style="width:90%;"><div style="float:left;"></div><div style="float:right">';
			
			echo '<form action="incentives.php?w=SetIncFinal&MonthNo='.$txndate.'" method="POST"><input style="background-color:#d8d8d8;color:#000000;padding:2px;" type="submit" name="btnSetIncFinal" value="Set As Incomplete" OnClick="return confirm(\'Set As Incomplete?\');"></form></div></div><br><br>';
			goto here;
		}
	}
	
	here:
	
	$sqlfirst='SELECT GROUP_CONCAT(DISTINCT(Branch)) AS `MoreThanOne`,COUNT(PositionID) AS CountPositionID FROM hr_2incentivesub his JOIN 1branches b ON his.BranchNo=b.BranchNo WHERE MonthNo='.$txndate.' AND ';
	$sqllast='GROUP BY his.BranchNo HAVING CountPositionID>1;';
	$notes='<b>Notes:</b><br>Branch(es) with more than 1';
	
	
	
	//Branch Heads
	$sql1=$sqlfirst.'PositionID=32 '.$sqllast;
	$stmt1=$link->query($sql1); $result1=$stmt1->fetchAll();
	if($stmt1->rowCount()>0){
		$branch1='';
		foreach($result1 AS $res){
			$branch1 .= " <b>[</b> ".$res['MoreThanOne']. " <b>]</b> ";
		}
		echo $notes.' Branch Heads: '.$branch1;
		echo '<br>';
	}
	
	//Jr Branch Heads
	$sql1=$sqlfirst.'PositionID=81 '.$sqllast;
	$stmt1=$link->query($sql1); $result1=$stmt1->fetchAll();
	if($stmt1->rowCount()>0){
		$branch2='';
		foreach($result1 AS $res){
			$branch2 .= " <b>[</b> ".$res['MoreThanOne']. " <b>]</b> ";
		}
		echo $notes.' Junior Branch Heads: '.$branch2;
		echo '<br>';
	}
	
	//OICs
	$sql1=$sqlfirst.'PositionID=37 '.$sqllast;
	$stmt1=$link->query($sql1); $result1=$stmt1->fetchAll();
	if($stmt1->rowCount()>0){
		$branch3='';
		foreach($result1 AS $res){
			$branch3 .= " <b>[</b> ".$res['MoreThanOne']. " <b>]</b> ";
		}
		echo $notes.' OIC: '.$branch3;
		echo '<br>';
	}
	
	//OICs with Bh or JBH
	$sql1=$sqlfirst.'(PositionID=37 OR PositionID=81 OR PositionID=32) '.$sqllast;
	$stmt1=$link->query($sql1); $result1=$stmt1->fetchAll();
	if($stmt1->rowCount()>0){
		$branch4='';
		foreach($result1 AS $res){
			$branch4 .= " <b>[</b> ".$res['MoreThanOne']. " <b>]</b> ";
		}
		echo 'Branch(es) with OIC and (Branch Head or Jr Branch Head): '.$branch4;
		echo '<br>';
	}
	// echo $sql1;
	
	if($resultb['Posted']==1){
		$editallow=0;
	} else {
		$editallow=1;
	}
	
	echo '<br><h3>Incentives for the Month of '.date("F", mktime(0, 0, 0, $txndate, 10)).'</h3><br>';
	echo '<table class="hoverstyle" style="width:90%;font-size:11pt;border:1px solid black;border-collapse:collapse;background-color:#B7E2DB;">';
	echo '<tr style="font-weight:bold;font-size:12pt;"><td>Branch</td><td>TargetReached</td><td>IDNo</td><td>Name</td><td>Position</td><td>NoOfDays</td><td>Units</td><td>Rate/Unit</td><td>PercentSale</td><td align="right" style="padding-right:5px;">Incentives</td><td>Remarks</td><td></td></tr>';
	echo '<tbody style="overflow:auto;">';
	
	$color[1]='#FFEBCD';
	$color[2]='#FFFACD';
	$color[3]='#F0FFFF';
	$color[4]='#EAFAF1';
	$color[5]='#E0FFFF';
	$color[6]='#FFF8DC';
	$color[7]='#F5FFFA';
	$color[8]='#F5F5F5';
	$color[9]='#FEF9E7';
	// echo $sql;
	$cntc=0;
	$totamount=0; $totnum=0; $noofbranch=0;
	foreach($result AS $res){
		if($cntc%2==0){ $rcolor[0]="#FFFFFF"; }
		
		
		if($oldbranch<>$res['Branch']){
			$cntc++;
			$noofbranch++;
		}
		if($cntc==5){
			$cntc=1;
		}
		if($editallow==1){
			echo '<form action="incentives.php?w=Update&MonthNo='.$txndate.'&TxnSubId='.$res['TxnSubId'].'" method="POST"><tr style="padding:30px;" bgcolor="'.$color[$cntc].'">'.($oldbranch<>$res['Branch']?'<td>'.$res['Branch'].'</td><td>'.$res['Score'].'</td>':str_repeat('<td></td>',2)).'<td>'.$res['IDNo'].'</td><td>'.((($res['NoOfDays']<$res['TotalDays']) OR $res['Resigned']==1)?'<blink><b>':'').''.$res['FullName'].''.((($res['NoOfDays']<$res['TotalDays']) OR $res['Resigned']==1)?'</b></blink>':'').'</td><td>'.$res['Position'].'</td><td>'.($res['NoOfDays']<$res['TotalDays']?$res['NoOfDays']."/".$res['TotalDays']:'').'</td><td>'.$res['Units'].'</td><td>'.$res['RatePerUnit'].'</td><td>'.$res['PercentSale'].'</td><td align="right" style="padding-right:5px;"><input style="text-align:right;" type="text" size="5" name="Amount" value="'.$res['Amount'].'"></td><td><input type="text" name="Remarks" value="'.$res['Remarks'].'" size="15"></td><td><input style="background-color:#20B2AA;color:white;padding:4px;" type="submit" value="Edit"></td><tr></form>';
		} else {
			echo '<tr style="padding:30px;" bgcolor="'.$color[$cntc].'">'.($oldbranch<>$res['Branch']?'<td>'.$res['Branch'].'</td><td>'.$res['Score'].'</td>':str_repeat('<td></td>',2)).'<td>'.$res['IDNo'].'</td><td>'.((($res['NoOfDays']<$res['TotalDays']) OR $res['Resigned']==1)?'<blink><b>':'').''.$res['FullName'].''.((($res['NoOfDays']<$res['TotalDays']) OR $res['Resigned']==1)?'</b></blink>':'').'</td><td>'.$res['Position'].'</td><td>'.($res['NoOfDays']<$res['TotalDays']?$res['NoOfDays']."/".$res['TotalDays']:'').'</td><td>'.$res['Units'].'</td><td>'.$res['RatePerUnit'].'</td><td>'.$res['PercentSale'].'</td><td align="right" style="padding-right:5px;">'.$res['Amount'].'</td><td>'.$res['Remarks'].'</td><td style="padding:4px;"></td><tr>';
		}
		
		$oldbranch=$res['Branch'];
		
		
		$totamount=$totamount+$res['Amount'];
		$totnum++;
		
	}
	echo '<tr><td colspan=2><b>'.$noofbranch.' branches, '.$totnum.' records</b></td><td colspan="8" align="right" style="padding-right:5px;"><b>Total: P '.number_format($totamount,0).'</b></td><td></td><td></td></tr>';
	echo '</tbody>';
	echo '</table>';
	

   break;
   
   
   
   case 'PostUnpost':
   case 'SetAsInc':
   
   if($whichqry=='PostUnpost'){
	   if(!allowedToOpen(2014,'1rtc')){ echo 'No Permission'; exit(); }
	   $othercon='Posted=0 ';
   } else {
	   if(!allowedToOpen(2015,'1rtc')){ echo 'No Permission'; exit(); }
	   $othercon='Posted=1 ';
   }
   
   $sql1='UPDATE hr_1incentivemain SET Posted=IF(Posted=1,0,1),PostedByNo='.$_SESSION['(ak0)'].',PostedTS=NOW() WHERE '.$othercon.' AND Approved=0 AND Sent=0 AND MonthNo='.intval($_GET['MonthNo']).'';
	$stmt1=$link->prepare($sql1); $stmt1->execute();
	header('Location:incentives.php?w=CalcIncentives&MonthNo='.$_GET['MonthNo']);
   break;
   
   
   case 'Approve':
   case 'SetAsIncApprove':
   if($whichqry=='Approve'){
	   if(!allowedToOpen(2015,'1rtc')){ echo 'No Permission'; exit(); }
	   $othercon='Approved=0 ';
   } else {
	   if(!allowedToOpen(2016,'1rtc')){ echo 'No Permission'; exit(); }
	   $othercon='Approved=1 ';
   }
   $sql1='UPDATE hr_1incentivemain SET Approved=IF(Approved=1,0,1),ApprovedByNo='.$_SESSION['(ak0)'].',ApprovedTS=NOW() WHERE '.$othercon.' AND Posted=1 AND Sent=0 AND MonthNo='.intval($_GET['MonthNo']).'';
	$stmt1=$link->prepare($sql1); $stmt1->execute();
	header('Location:'.$_SERVER['HTTP_REFERER']);
   break;
   
   case 'Update':
   if(!allowedToOpen(2014,'1rtc')){ echo 'No Permission'; exit(); }
   $sql1='UPDATE hr_2incentivesub SET Amount="'.$_POST['Amount'].'",Remarks="'.$_POST['Remarks'].'",EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE TxnSubId='.intval($_GET['TxnSubId']).'';
	$stmt1=$link->prepare($sql1); $stmt1->execute();
	header('Location:incentives.php?w=CalcIncentives&MonthNo='.$_GET['MonthNo']);
   break;
   
   case 'DeleteData':
   if(!allowedToOpen(2015,'1rtc')){ echo 'No Permission'; exit(); }
   
   $monthno=intval($_GET['MonthNo']);
   $sqldelsub='DELETE `his`.* FROM hr_2incentivesub `his` JOIN hr_1incentivemain `im` ON `his`.MonthNo=`im`.MonthNo WHERE im.Posted=0 AND `his`.MonthNo='.$monthno.'';
	$stmtdelsub=$link->prepare($sqldelsub); $stmtdelsub->execute();
	
	header('Location:incentives.php?w=CalcIncentives&MonthNo='.$_GET['MonthNo']);
   break;
   
   case 'SetIncFinal':
   if(!allowedToOpen(20161,'1rtc')){ echo 'No Permission'; exit(); }
   
   $monthno=intval($_GET['MonthNo']);
   $sqlusub='UPDATE hr_1incentivemain SET Sent=0 WHERE MonthNo='.$monthno.'';
   // echo $sqlusub; exit();
	$stmtusub=$link->prepare($sqlusub); $stmtusub->execute();
	
	header('Location:incentives.php?w=CalcIncentives&MonthNo='.$_GET['MonthNo']);
   break;
   
   
   case 'SendToSpecialCredits':
   if(!allowedToOpen(2016,'1rtc')){ echo 'No Permission'; exit(); }
   $txndate=(intval($_GET['MonthNo']));
   $txndate=str_pad($txndate,2,'0',STR_PAD_LEFT);
   
   if($txndate==12){
	   $sqlinsertmain='INSERT INTO payroll_30othercreditsmain (DateOfCredit,Batch,Remarks,Posted,PostedByNo) VALUES ("'.$nextyr.'-01-15","'.$_POST['Batch'].'","Incentive '.date("F", mktime(0, 0, 0, $txndate, 10)).' '.$currentyr.'",1,'.$_SESSION['(ak0)'].')';
   } else {
		$sqlinsertmain='INSERT INTO payroll_30othercreditsmain (DateOfCredit,Batch,Remarks,Posted,PostedByNo) VALUES ("'.$currentyr.'-'.($txndate+1).'-15","'.$_POST['Batch'].'","Incentive '.date("F", mktime(0, 0, 0, $txndate, 10)).' '.$currentyr.'",1,'.$_SESSION['(ak0)'].')';
   }
	$stmtmain=$link->prepare($sqlinsertmain); $stmtmain->execute();
	
	if($txndate==12){
		$sqlb='SELECT TxnID FROM payroll_30othercreditsmain WHERE DateofCredit="'.$nextyr.'-01-15" AND Batch="'.$_POST['Batch'].'"';
	} else {
		$sqlb='SELECT TxnID FROM payroll_30othercreditsmain WHERE DateofCredit="'.$currentyr.'-'.($txndate+1).'-15" AND Batch="'.$_POST['Batch'].'"';
	}
	$stmtb=$link->query($sqlb); $resultb=$stmtb->fetch();
	
	if($txndate==12){
		$sqlc='INSERT INTO payroll_30othercreditssub (TxnID,IDNo,Amount,EncodedByNo,TimeStamp) SELECT "'.$resultb['TxnID'].'" AS TxnID,IDNo,SUM(Amount) AS Amount,"'.$_SESSION['(ak0)'].'" AS EncodedByNo,NOW() AS TimeStamp FROM hr_2incentivesub WHERE MonthNo="'.$txndate.'" GROUP BY IDNo';
	} else {
		$sqlc='INSERT INTO payroll_30othercreditssub (TxnID,IDNo,Amount,EncodedByNo,TimeStamp) SELECT "'.$resultb['TxnID'].'" AS TxnID,IDNo,SUM(Amount) AS Amount,"'.$_SESSION['(ak0)'].'" AS EncodedByNo,NOW() AS TimeStamp FROM hr_2incentivesub WHERE MonthNo="'.$txndate.'" GROUP BY IDNo';
	}
	$stmtmain=$link->prepare($sqlc); $stmtmain->execute();
   
   
	$sql1='UPDATE hr_1incentivemain SET Sent=1,SentByNo='.$_SESSION['(ak0)'].',SentTS=NOW() WHERE MonthNo='.$txndate.'';
	$stmt1=$link->prepare($sql1); $stmt1->execute();
	if($txndate<>12){
		header('Location:../payroll/addentry.php?w=SpecCredits&TxnID='.$resultb['TxnID']);
	} else {
		echo 'Sent To Special Credits. Pls check Jan '.$nextyr.' special credits.'; exit();
	}
	
	break;
	
	       
case 'SendToVoucher':
   if(!allowedToOpen(2017,'1rtc')){ echo 'No Permission'; exit(); }
   
   $txndate=(($_REQUEST['MonthNo']=='')?(date('m')-1):intval($_REQUEST['MonthNo']));
   
   // echo $txndate; exit();
   
   $title='Send Incentives to Voucher';
	
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br>';
   echo '<form action="incentives.php?w=SendToVoucher" method="POST">MonthNo: <input type="text" name="MonthNo" size="5" value="'.$txndate.'"><input type="submit" name="btnLookup" value="Send"> </form><br>';
   
   if(!isset($_REQUEST['MonthNo'])) { exit();}
   
        $sql0='CREATE TEMPORARY TABLE incentives SELECT CompanyNo,i.BranchNo, SUM(Amount) AS BranchIncentive FROM hr_2incentivesub i JOIN 1branches b ON b.BranchNo=i.BranchNo WHERE `MonthNo`='.$txndate.' GROUP BY i.BranchNo';
        if($_SESSION['(ak0)']==1002){ echo $sql0.'<br>';}
        $stmt0=$link->prepare($sql0); $stmt0->execute();
        // echo $sql0; 
		// exit();
		//GET LAST VOUCHER NUMBER
		include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
    		$vchno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($currentyr.'-01-01'))).',2)')+1;
   
    $remarks='Incentives for the month of '.date('F', strtotime(($currentyr.'-'.$txndate.'-15')));  
    $sql='INSERT INTO `acctg_2cvmain` (`CVNo`,`CheckNo`,`Date`,`DateofCheck`,`Payee`,`CreditAccountID`,`Remarks`,`TimeStamp`,`EncodedByNo`) '
            . ' SELECT '.$vchno.', "Incentives'.$txndate.'", LAST_DAY(\''.($currentyr.'-'.($txndate).'-01').'\'), \''.($currentyr.'-'.($txndate+1).'-15').'\', "CASH", 128, "'.$remarks.'",Now(),'.$_SESSION['(ak0)'];
    if($_SESSION['(ak0)']==1002){ echo $sql.'<br>';}
    $stmt=$link->prepare($sql); $stmt->execute();
    
    $sql='SELECT CVNo FROM acctg_2cvmain where CVNo='.$vchno;
	   $stmt=$link->query($sql); $result=$stmt->fetch();
           if($_SESSION['(ak0)']==1002){ echo $sql.'<br>';}
	   $txnid=$result['CVNo'];
           
    $sql='INSERT INTO `acctg_2cvsub`(`CVNo`,`Particulars`,`DebitAccountID`,`Amount`,`TimeStamp`,`BranchNo`,`FromBudgetOf`,`EncodedByNo`) '
            . 'SELECT '.$txnid.',  "'.$remarks.'", 906, BranchIncentive, Now(), BranchNo, BranchNo,'.$_SESSION['(ak0)'].' FROM incentives';
    if($_SESSION['(ak0)']==1002){ echo $sql.'<br>';}
    $stmt=$link->prepare($sql); $stmt->execute();
    header('Location:../acctg/formcv.php?w=CV&CVNo='.$txnid);
    break;
	
	
  
}
     $link=null; $stmt=null;
?>