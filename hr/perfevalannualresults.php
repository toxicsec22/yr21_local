<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6863,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');



 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');
$which=(!isset($_GET['w'])?'List':$_GET['w']);

if (in_array($which, array('List'))){
    echo comboBox($link,'SELECT PositionID, Position FROM attend_0positions ORDER BY Position','PositionID','Position','positionlist');
}

switch ($which)
{
	case 'List':
		if (allowedToOpen(6863,'1rtc')) {
			$title1 = 'Annual Evaluation Results';
			echo '<title>'.$title1.'</title>';
			echo '<br/>';
			echo '<h3>'.$title1.'</h3>';
			echo '<br/>';
			echo '<form method="post" action="perfevalannualresults.php?w=List" enctype="multipart/form-data">
			Position <input type=text size=10 name=ToEvaluate list=positionlist >
			<input type=submit name=submit value=Lookup></form><br/><br/>';
			
			if (isset($_POST['submit'])){
				$sql3 = 'SELECT Position, ToEvaluate, Evaluator FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID '
								. 'LEFT JOIN attend_0positions p ON ep.ToEvaluate=p.PositionID WHERE Position LIKE "'.addslashes($_POST['ToEvaluate']).'"'; 
				$stmt3=$link->query($sql3); $row3 = $stmt3->fetch();
					
				$sql1='CREATE TEMPORARY TABLE perfevals AS SELECT pf.TxnID, pf.IDNo, CONCAT(cp.Branch,"<br>",cp.FullName) AS ColName, cp.BranchNo FROM hr_2perfevalmain pf JOIN attend_0positions p ON pf.CurrentPositionID=p.PositionID JOIN `attend_30currentpositions` cp ON cp.IDNo=pf.IDNo WHERE p.Position="'.$_POST['ToEvaluate'].'" AND SupervisorEval IS NOT NULL;';
				
				
				$stmt1=$link->prepare($sql1); $stmt1->execute();
				
				$sql11='SELECT * FROM perfevals';
				
				$stmt11=$link->query($sql11); $row11 = $stmt11->fetchAll();
				
				$sql0=''; $columnnames=array('Statement','Average');
				$title='Evaluation Summary for '.$_POST['ToEvaluate'];
				
				foreach ($row11 as $eval){
					$columnnames[]=$eval['ColName'];
					$sql0.=', TRUNCATE(AVG(CASE WHEN pe.IDNo='.$eval['IDNo'].' THEN IFNULL(pes.SuperScore,0) END),2) AS `'.$eval['ColName'].'`';
				}
				
				$sql='SELECT s.StatementID, Statement, TRUNCATE(AVG(IFNULL(pes.SuperScore,0)),2) AS Average '.$sql0.' FROM `hr_2perfevalsub` pes JOIN perfevals pe ON pe.TxnID=pes.TxnID LEFT JOIN hr_1positionstatement ps ON pes.PSID=ps.PSID JOIN hr_1statement s ON ps.StatementID=s.StatementID 
				GROUP BY s.StatementID;';
				
				include('../backendphp/layout/displayastablenosort.php');
			}
		}
	break; 
	

}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
