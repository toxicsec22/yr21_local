<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php');

if (!allowedToOpen(8112,'1rtc')) { header('Location:../index.php?denied=true'); }
  

$which=!isset($_GET['w'])?'CalcBonus':$_GET['w']; 

switch($which){
    case 'CalcBonus':

$title='Calculate Bonuses';

$minperfeval=3; //lowest performance evaluation score to get a bonus
$midperfeval=3.5; //below this will get 50% of calculated
$mintenure=0.25; //minimum tenure to get bonus this year

$sql0='SELECT BonusRateBasedTargetReached FROM 00dataclosedby WHERE ForDB=1'; $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$RateThisYr=$res0['BonusRateBasedTargetReached'];

// bec 14th is based on 13th, tenure has been taken into consideration.  Removed tenure in calculation.

$formdesc='</i></h4>'
        .'<br><br>Verify 13th month calculation in view per year!'
        . '<br>Bonus formula = ((Performance Evaluation)*0.95+(Attendance)*0.05)*(14thMonth*BonusRateBasedTargetReached)*(BonusRatePerBranchorDept) +/- NetMeritDemeritPeso<br>'
        .'<br><br>BonusRateBasedTargetReached This Year: '.($RateThisYr*100).'%'
        .'<br><br>Lowest performance evaluation score to get a bonus: '.$minperfeval
        . '<br>Below this, 50% of maximum is the basis: '.$midperfeval.'<br> '
        . '<br>Minimum tenure to get bonus this year: '.$mintenure.' year.<br><br><h4><i>';


$addlmenu='';
if (allowedToOpen(8111,'1rtc')) {

	    $formdesc.='<a href="bonuses.php?filter=AutoEncodeCalculatedBonus"  OnClick="return confirm(\'Auto encode calculated bonus?\');">Auto Encode Calculated Bonus PLANNED</a>&nbsp &nbsp &nbsp  &nbsp &nbsp<a href="addentry.php?w=Bonuses">Add Bonus Data</a>&nbsp &nbsp &nbsp  &nbsp &nbsp<a href="lookupwithedit.php?w=SendtoPayroll"  OnClick="return confirm(\'Bonuses are final?\');">Send Bonus Data To Payroll</a>&nbsp &nbsp &nbsp  &nbsp &nbsp<a href="/'.$url_folder.'/payroll/prpayrolldata.php?w=13th&action_token='.$_SESSION['action_token'].'"  OnClick="return confirm(\'Encode 13th month?\');">Encode 13th month</a><br><br><font style="font-size: 9pt"></font><br><br>';
}
           

if (allowedToOpen(8112,'1rtc')) {

echo '<div style="width: 100%; display: inline;">';
echo '<div style="width: 50%; display: inline;">';

echo '</div>';
//$title=''; unset($formdesc);

echo '<div style="width: 25%; display: inline;">';
$sql='SELECT Class, (BonusMultiplier*100) AS `BonusMultiplier%` FROM 0branchclass ORDER BY ClassID Desc;';
$columnnames=array('Class','BonusMultiplier%');
$width='50%'; $hidecount=true; 
include('../backendphp/layout/displayastablenosort.php'); 
echo '</div>';
echo '<div style="width: 25%; display: inline;">';
$subtitle='Merit/Demerit (+/-) equivalent';
$sql='SELECT WeightinPoints, PointDesc, PesoinBonus FROM hr_70points;';
$columnnames=array('WeightinPoints','PointDesc','PesoinBonus');
$width='25%';
$title=''; $formdesc='';
include('../backendphp/layout/displayastablenosort.php'); 
echo '</div>';
echo '<div style="width: 25%; display: inline;">';
$subtitle='Check the ff who went beyond tolerable invty charges';
$sql='SELECT e.IDNo, Fullname, BranchorDept, TRUNCATE(SUM(Amount),0) AS YrTotalCharges FROM acctg_2salesub ss JOIN attend_30currentpositions e ON e.IDNo=ss.ClientNo JOIN acctg_2salemain sm ON sm.TxnID=ss.TxnID GROUP BY e.IDNo HAVING YrTotalCharges>11000;';
$columnnames=array('IDNo','FullName','BranchorDept','YrTotalCharges');
$width='35%';
$title=''; $formdesc='';
include('../backendphp/layout/displayastablenosort.php'); 
echo '</div>';
echo '<div style="width: 25%; display: inline;">';
$subtitle='Bonus Rates per Dept';
$sql='SELECT deptid,department AS Department, BonusRate FROM `1departments` WHERE deptid NOT IN (0,3,4,10,99);';
$columnnames=array('deptid','Department','BonusRate');
$width='35%';
$title=''; $formdesc='';
include('../backendphp/layout/displayastablenosort.php'); 
echo '</div>';
echo '</div>';
$subtitle='';
}

EncodeCalculated:
   
    // SINGLE EVALUATOR
$stmt0=$link->prepare('DROP TEMPORARY TABLE IF EXISTS evaluation;'); $stmt0->execute(); 

$stmt0=$link->prepare('CREATE TEMPORARY TABLE evaluation AS
SELECT e.IDNo, e.Nickname, e.FirstName, e.SurName, deptid, Position, IFNULL(13thBasicCalc,0)+IFNULL(13thTaxShCalc,0) AS Total13th, p.JobLevelID, p.BranchNo,p.BranchorDept,
 IFNULL(SuperScore,0) AS SupervisorEval, 
e1.Nickname AS EvaluatedBy, e2.Nickname AS DeptHead, DeptHeadIDNo, ((TO_DAYS(\''.$currentyr.'-12-05\') - TO_DAYS(`e`.`DateHired`)) / 365) as InYears, IFNULL((SELECT SUM(AbsencesPerMonth) FROM `attend_62absences` WHERE IDNo=e.IDNo),0) AS Absences, 
IFNULL((SELECT SUM(LatesPerMonth) FROM `attend_62latescount` WHERE IDNo=e.IDNo),0) AS Lates, IFNULL((SELECT SUM(UndertimeCount) FROM `attend_62undertime` WHERE IDNo=e.IDNo),0) AS Undertime, (SELECT COUNT(IDNo) FROM attend_2attendance WHERE LeaveNo NOT IN (12,13,15) AND IDNo=e.IDNo) AS AttendDays, 0 AS `Attend%`, 1.0 AS BranchClassRate, 
IF(e.DirectORAgency=0,"","Agency") AS DirectORAgency
 FROM `payroll_26yrtotaland13thmonthcalc` t 
JOIN `1employees` e ON e.IDNo=t.IDNo 
JOIN `attend_30currentpositions` p ON e.IDNo=p.IDNo JOIN `attend_howlongwithus` h ON e.IDNo=h.IDNo
LEFT JOIN `hr_2perfevalmain` pe ON e.IDNo=pe.IDNo 
LEFT JOIN (SELECT TxnID,TRUNCATE(SUM((Weight/100)*SuperScore),2) AS SuperScore from  hr_2perfevalsub pes JOIN hr_1positionstatement ps ON pes.PSID=ps.PSID GROUP BY TxnID) ps ON pe.TxnID=ps.TxnID
LEFT JOIN `1employees` e1 ON e1.IDNo=pe.SupervisorIDNo 
LEFT JOIN `1employees` e2 ON e2.IDNo=pe.DeptHeadIDNo  WHERE e.IDNo>1002 AND e.DirectORAgency=0 AND EvalAfterDays='.$currentyr.' 
 GROUP BY e.IDNo;'); $stmt0->execute(); 
$stmt0=$link->prepare('UPDATE evaluation SET `Attend%`=(AttendDays-Absences-Lates-Undertime)/AttendDays*100;'); $stmt0->execute(); 
 
$stmt0=$link->prepare('DROP TEMPORARY TABLE IF EXISTS monthlysalesper;'); $stmt0->execute(); 
$stmt0=$link->prepare('CREATE TEMPORARY TABLE monthlysalesper AS    
 SELECT BranchNo, (SELECT BonusMultiplier FROM 0branchclass WHERE AVG(`MonthlySales`)>=CutOffMin ORDER BY CutOffMin DESC LIMIT 1) AS BranchClassRate FROM ( SELECT b.BranchNo,SUM(Amount) AS MonthlySales FROM `acctg_61unisalereturn` s JOIN `1branches` b ON b.BranchNo=s.BranchNo
    WHERE
        b.Pseudobranch=0
    GROUP BY MONTH(`Date`), s.BranchNo) AS T GROUP BY BranchNo; '); $stmt0->execute(); 
$stmt0=$link->prepare('UPDATE evaluation e JOIN monthlysalesper m ON  m.BranchNo=e.BranchNo SET e.BranchClassRate=m.BranchClassRate  WHERE e.deptid=10;'); 
$stmt0->execute(); 
// Update rates per dept except stores
$stmt0=$link->prepare('UPDATE evaluation e JOIN 1departments d ON  d.deptid=e.deptid SET e.BranchClassRate=d.BonusRate  WHERE e.deptid<>10;'); 
$stmt0->execute(); 
 
$stmt0=$link->prepare('DROP TEMPORARY TABLE IF EXISTS calbonus;'); $stmt0->execute(); 
$stmt0=$link->prepare('CREATE TEMPORARY TABLE calbonus AS
 SELECT ev.*, 0 AS NetMeritDemerit, FORMAT(Total13th*'.$RateThisYr.',0) AS BonusIfPerfect,  TRUNCATE((IF(InYears<1,InYears,1)-(IFNULL(Absences,0)/330)),2) AS TenureThisYr, 
     TRUNCATE(((0.95*(SupervisorEval/5)) + (0.05*((AttendDays-Absences-Lates-Undertime)/AttendDays)))*100,2) AS `NetScore%`, 
 TRUNCATE(
 (CASE WHEN SupervisorEval<'.$minperfeval.' THEN 0
     WHEN SupervisorEval<'.$midperfeval.' THEN 0.5*'.$RateThisYr.'*Total13th*BranchClassRate*((0.95*(SupervisorEval/5)) + (0.05*((AttendDays-Absences-Lates-Undertime)/AttendDays)))
     WHEN SupervisorEval>='.$midperfeval.' THEN '.$RateThisYr.'*Total13th*BranchClassRate*((0.95*(SupervisorEval/5)) + (0.05*((AttendDays-Absences-Lates-Undertime)/AttendDays)))
     ELSE 0 END)*IF(InYears<'.$mintenure.',0,1)
,2)       
     AS CalcPerfBonus FROM evaluation ev ;'); $stmt0->execute();  

$stmt0=$link->prepare('DROP TEMPORARY TABLE IF EXISTS netmeritdemerit;'); $stmt0->execute(); 

$stmt0=$link->prepare('CREATE TEMPORARY TABLE netmeritdemerit AS 
SELECT `ReporteeNo`, SUM(p.PesoinBonus*(CASE WHEN DecisionStatus=3 THEN 1 ELSE -1 END )) AS NetMeritDemerit FROM `hr_72scores` s JOIN hr_71scorestmt ss ON ss.SSID=s.SSID JOIN hr_70points p ON p.PointID=ss.PointID WHERE `DecisionStatus` IN (1,3) GROUP BY `ReporteeNo`;'); $stmt0->execute();  

$stmt0=$link->prepare('UPDATE calbonus SET CalcPerfBonus=CalcPerfBonus+(
CASE 
WHEN (NetMeritDemerit>0) OR (CalcPerfBonus+NetMeritDemerit>=0) THEN NetMeritDemerit 
WHEN (CalcPerfBonus+NetMeritDemerit<0) THEN CalcPerfBonus*-1 END)'); $stmt0->execute(); 
     
$columnnames=array('IDNo','Nickname','SurName','Position','BranchorDept','SupervisorEval','Attend%','NetScore%','TenureThisYr','BranchClassRate', 'Calc_13th','Calcd_Bonus','TOTAL','BonusIfPerfect');  
$columnsub=$columnnames;
$sortfield=(isset($_POST['sortfield'])?' ORDER BY '.$_POST['sortfield']:'');


$sql='SELECT c.*,FORMAT(Total13th,0) AS Calc_13th,FORMAT(CalcPerfBonus,0) AS Calcd_Bonus,ROUND(CalcPerfBonus+Total13th,0) AS TOTAL_VALUE,FORMAT(CalcPerfBonus+Total13th,2) AS TOTAL FROM calbonus c
JOIN  `1employees` e ON e.IDNo=c.IDNo JOIN `attend_30currentpositions` p ON e.IDNo=p.IDNo ORDER BY c.JobLevelID DESC,Position ASC,Branch, Nickname ASC '; //.$sortfield
$coltototal='TOTAL_VALUE'; //$showgrandtotal=true;
$sqltotal='SELECT SUM(CalcPerfBonus) AS `TotalPerfBonus`, SUM(Total13th) AS `Total13th` FROM calbonus '; 
$stmttotal=$link->query($sqltotal);
$restotal=$stmttotal->fetch();
$totalstext='Total Performance Bonus :'.number_format($restotal['TotalPerfBonus'],2).'<br>Total 13th :'.number_format($restotal['Total13th'],2).'<BR>Grand total: '.number_format($restotal['TotalPerfBonus']+$restotal['Total13th'],2);

goto Display;

    
Display:
$width='95%';
if(!isset($_GET['filter'])) { include('../backendphp/layout/displayastable.php');}
else {
if(($_GET['filter']=='AutoEncodeCalculatedBonus') and (allowedToOpen(8112,'1rtc'))) {
    $sqladj='INSERT INTO `payroll_21plannedbonuses` ( PayrollID, IDNo, AdjustTypeNo, AdjustAmt,Evaluation, EncodedByNo)
	    SELECT 23, s.IDNo, 23, CalcPerfBonus,`NetScore%`, '.$_SESSION['(ak0)'].' AS EncodedByNo
	    FROM `calbonus` as s JOIN `1employees` e ON e.IDNo=s.IDNo WHERE e.DirectORAgency=0 AND CalcPerfBonus>0;';
	    $stmtadj=$link->prepare($sqladj);
	    $stmtadj->execute(); 
            header ('Location:/'.$url_folder.'/payroll/lookupwithedit.php?w=Bonuses&done=1');
}
}  

break;

    case 'EditBonusSpecifics':
        $title='Edit Bonus Specifics';
        
        $sql='SELECT pb.*, FirstName, Surname, AdjustType FROM payroll_21plannedbonuses pb JOIN 1employees e ON e.IDNo=pb.IDNo JOIN payroll_0acctid pa ON pa.AdjustTypeNo=pb.AdjustTypeNo WHERE pb.IDNo='.$_REQUEST['TxnID'];
        
        $list='EditBonusSpecifics';
        $which=!isset($_GET['b'])?'EditBonusSpecifics':$_GET['b'];
        $table='payroll_21plannedbonuses'; $txnidname='AdjID'; 
        
        $columnnameslist=array('PayrollID','FirstName', 'Surname', 'AdjustType','AdjustTypeNo', 'AdjustAmt', 'Remarks', 'AdjID', 'Evaluation'); 
        $columnstoedit=array('PayrollID','AdjustTypeNo', 'AdjustAmt', 'Remarks'); 
        $columnstoadd=$columnstoedit;
        $columnswithlists=array('AdjustTypeNo');
        $listsname=array('AdjustTypeNo'=>'bonustype');
        $listssql=array(
            array('sql'=>'SELECT AdjustTypeNo,AdjustType FROM payroll_0acctid WHERE AdjustTypeNo IN (23,24)', 'listvalue'=>'AdjustType', 'label'=>'AdjustTypeNo','listname'=>'bonustype')
        );
        

        $sql='SELECT pb.*, FirstName, Surname, AdjustType FROM payroll_21plannedbonuses pb JOIN 1employees e ON e.IDNo=pb.IDNo JOIN payroll_0acctid pa ON pa.AdjustTypeNo=pb.AdjustTypeNo ' .($which==$list?'WHERE pb.IDNo='.$_REQUEST['TxnID']:' ');
$columnentriesarray=array(
                    array('field'=>'PayrollID', 'type'=>'text','size'=>5, 'required'=>true,'value'=>23),
                    array('field'=>'AdjustTypeNo', 'type'=>'text','size'=>5, 'required'=>true,'list'=>'bonustype'),
                    array('field'=>'AdjustAmt', 'type'=>'text','size'=>5, 'required'=>true),
                    array('field'=>'Remarks', 'type'=>'text','size'=>10, 'required'=>false)
                    );

    
            $file='bonuses.php?w=EditBonusSpecifics&TxnID='.$_REQUEST['TxnID'].'&b='; $fieldsinrow=2; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=8111; $editallowed=8111; $delallowed=8111;

if (allowedToOpen(8111,'1rtc')) { $delprocess='bonuses.php?w=EditBonusSpecifics&b=Delete&TxnID='.$_REQUEST['TxnID'].'&AdjID=';
$editprocess='bonuses.php?w=EditBonusSpecifics&TxnID='.$_REQUEST['TxnID'].'&b=EditSpecifics&AdjID='; $editprocesslabel='Edit';}

        
// set first field only if the first field should also be added/edited
$firstfield='PayrollID';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';
include('../backendphp/layout/genlists.php');
        break;
    

}
  $link=null; $stmt=null;
?>