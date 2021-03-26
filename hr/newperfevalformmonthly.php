<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
if(session_id()==''){
	session_start();
}
// if(!isset($_SESSION['oss'])){
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
if (!isset($_REQUEST['print'])){include_once('../switchboard/contents.php'); $printcondition='';} else { include_once $path.'/acrossyrs/dbinit/userinit.php'; $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
  
 
 echo '<div id="hidethis"><button onclick="printEvalForm()" style="background-color:green;color:white;padding:5px;">Print this form</button>
</div>
<script>
function printEvalForm() {
  window.print();
}
</script>';
echo '<style>@media print
	{
		#hidethis { display: none; }
	}</style>';
 }
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
  

$title='Monthly Functional Evaluation';
?>

<title><?php echo $title;?></title>
<center><h3><?php echo $title;?></h3></center><br><br>
<style>
    body {font-family:sans-serif;}
    #scores { font-family: sans-serif; align-content: center; font-size:14pt; font-weight: 400;}
    #values { font-family: sans-serif; align-content: center; font-size:14pt; font-weight: 400; color: blue;}
    #textboxid { height:30px; width:60px; font-size:14pt; text-align: center; }
    #wrap {  width:80%; margin: 0 20 0 20;}
    #left {   float:left;   width:55%; overflow: auto;}
    #right {   float:right;   width:45%; overflow: auto;}
    table,th,td {border:1px solid black;border-collapse:collapse; padding: 5px !important; font-size: small !important; overflow:auto;}
    #legendcell { font-size: 8pt;}
    #main {  width:100%;  margin:0 auto; clear: both;}
    #comments { width:55%; height:auto; font-size: 11pt; color:green; padding: 2px; display: inline;}
    #headings { font-size: 12pt; font-weight: bold; display: inline;}
	
</style>
<?php
$IDNo=intval($_REQUEST['IDNo']);  
$sql='SELECT pf.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName,CONCAT(e2.FirstName, " ", e2.Surname) AS Supervisor
FROM hr_82perfevalmonthlymain pf   
JOIN `1employees` e1 ON e1.IDNo=pf.IDNo
LEFT JOIN `1employees` e2 ON e2.IDNo=pf.SIDNo
JOIN `1companies` c ON c.CompanyNo=e1.RCompanyNo WHERE pf.IDNo='.$IDNo.$printcondition;
$stmt=$link->query($sql); $result=$stmt->fetch(); 


$sql0='SELECT DATE_FORMAT(MAX(SCommentTS),\'%Y %M %d\') AS LastEval FROM `hr_82perfevalmain` WHERE IDNo='.$result['IDNo'];  $stmt0=$link->query($sql0); $result0=$stmt0->fetch();

?>
<div id="wrap"><div>
        
<table bgcolor="white">
    <tr><td>Employee Number<br><h4><?php echo $result['IDNo'];?></h4></td><td>Name<br><h4><?php echo $result['FullName']; ?></h4></td>
    </tr>
</table>



<div id="main" >
   
<?php												
$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
$rcolor[1]="FFFFFF";
$colorcount=0;


echo '<br><br><b>Functional Competencies</b>';

echo '<br><br>';


	$actionprocess='SuperScoreMonthly';
	$showselfaction=1;
	$showsuperfaction=0;


$sqlmonths='SELECT pemm.TxnID,MonthNo,SIDNo,MONTHNAME(CONCAT("'.$currentyr.'-",MonthNo,"-01")) AS MonthName,CONCAT(e.Nickname," ",e.SurName) AS Supervisor,Posted FROM hr_82perfevalmonthlymain pemm LEFT JOIN 1employees e ON pemm.SIDNo=e.IDNo WHERE pemm.IDNo='.intval($_GET['IDNo']).' ORDER BY MonthNo DESC';
$stmtmonths=$link->query($sqlmonths); $rowmonths = $stmtmonths->fetchAll();
// echo $sqlmonths;

foreach($rowmonths AS $rowmonth){
	if($rowmonth['MonthNo']<=(date('m')+1)){
	echo '<br><b>'.$rowmonth['MonthName'].'</b>';
	if($rowmonth['Posted']==0 AND (allowedToOpen(100,'1rtc'))){
		echo ' <a href="newperfeval.php?w=EditStatements&TxnID='.$rowmonth['TxnID'].'">Edit</a>';
	}
	echo '<br><table>';
	echo '<tr><th colspan=3 style="background-color:#fff">Supervisor: '.$rowmonth['Supervisor'].'</th></tr>';
echo '<tr><th>Statement</th><th>Weight</th>';
$selfth='<th>Super-Score</th></tr>';

echo $selfth;
	$sqlcore='SELECT pems.TxnSubId,pemm.Posted,pems.FCID,SuperScore,Statement,`Weight` FROM hr_82perfevalmonthlymain pemm JOIN hr_82perfevalmonthlysub pems ON pemm.TxnID=pems.TxnID JOIN hr_81fcsub fv ON pems.FCID=fv.FCID WHERE pemm.IDNo = '.intval($_GET['IDNo']).' AND MonthNo='.$rowmonth['MonthNo'].' ORDER BY OrderBy'; 
	
	$stmtcore=$link->query($sqlcore);
	$rowcore = $stmtcore->fetchALL();
	
	
	$totalweight=0; $superscore=0;
	echo '<form action="newperfevalprocess.php?action_token='.$_SESSION['action_token'].'&IDNo='.$_GET['IDNo'].'&w='.$actionprocess.'" method="POST" autocomplete=off>';
	foreach($rowcore AS $rowco){
		echo '<tr bgcolor="'. $rcolor[$colorcount%2].'"><td>'.$rowco['Statement'].'</td><td>'.$rowco['Weight'].'%</td>';
		
		if($rowco['Posted']==0 AND $_SESSION['(ak0)']==$rowmonth['SIDNo']){
			echo '<input type="hidden" name="TxnSubId'.$colorcount.'" value="'.$rowco['TxnSubId'].'" />';
			echo '<td '.($rowco['SuperScore']==''?'style="background-color:red;"':'').'><input type="number" name="SuperScore'.$colorcount.'" size="5" value="'.$rowco['SuperScore'].'" min=0 max=5 step=".1" style="width:70px;"></td>';
		} else {
			echo '<td>'.$rowco['SuperScore'].'</td>';
		}
		
		echo '</tr>';
		$colorcount++;
		$totalweight=$totalweight+$rowco['Weight'];
		$superscore = $superscore + (($rowco['Weight']/100) * $rowco['SuperScore']);
	}
	if($rowco['Posted']==0){
		if($rowmonth['SIDNo']==$_SESSION['(ak0)']){
			echo '<input type="hidden" name="supernum" value="'.($colorcount).'">';
			echo '<tr><td colspan=3 align="right"><b>Step 1: </b><input type="submit" value="Enter Score" name="btnSubmit"></td></tr>';
			
		}
	}
	echo '</form>';

	$sqlcntscore='SELECT SUM(IF(SuperScore IS NULL,1,0)) AS MissingSuperScore FROM hr_82perfevalmonthlysub WHERE TxnID='.$rowmonth['TxnID'];
	$stmtcntscore=$link->query($sqlcntscore); $resultscore=$stmtcntscore->fetch();
	if($rowco['Posted']==0){
		if($resultscore['MissingSuperScore']==0 AND $rowmonth['SIDNo']==$_SESSION['(ak0)']){
			echo '<tr><td colspan=3><form method="POST" action="newperfevalprocess.php?action_token='.$_SESSION['action_token'].'&TxnID='.$rowmonth['TxnID'].'&w=PostMonthly"><b>Step 2:</b> <input type="submit" value="POST" name="btnPost" onclick="return confirm(\'Are you sure? This action cannot be undone.\')"></form></td></tr>';
		}
	}
	
	echo '</table>';
	$totalweight=0; $superscore=0; $colorcount=0;
}


}

?>

</div>  
