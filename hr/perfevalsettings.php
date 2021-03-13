<?php
include_once('perfevalsettingslinks.php');
$which=(!isset($_GET['w'])?'CompetencyList':$_GET['w']);
$showbranches=false;
if (in_array($which,array('CompetencyList','EditSpecificsPC'))){
   $sql='SELECT *, PerfComID AS TxnID FROM hr_1competency';
   $columnnameslist=array('PerfComID', 'Competency');
   $columnstoadd=array('Competency');
}

//Add/Edit for Performance Statement
if (in_array($which,array('AddPS','EditPS'))){
	$PerfComID=comboBoxValue($link,'hr_1competency','Competency',addslashes($_POST['PerfComID']),'PerfComID');
	$columnstoadd=array('Statement');
}
//Add/Edit for Performance Statement Assign
if (in_array($which,array('AddSA','EditSA'))){
	$StatementID=comboBoxValue($link,'hr_1statement','Statement',addslashes($_POST['Statement']),'StatementID');
	$ToEvaluate=comboBoxValue($link,'attend_0positions','Position',addslashes($_POST['ToEvaluate']),'PositionID');
	$Evaluator=comboBoxValue($link,'attend_0positions','Position',addslashes($_POST['Evaluator']),'PositionID');
}

if (in_array($which,array('AddSA','AddMultiSA'))){
    $ToEvaluate=($which=='AddMultiSA'?$_REQUEST['ToEvaluate']:$ToEvaluate);
    $Evaluator=($which=='AddMultiSA'?$_REQUEST['Evaluator']:$Evaluator);
    $EvalMonth=$_POST['EvalMonth'];
	
    $sql01='SELECT EPID FROM hr_1evaluatorpercentage WHERE ToEvaluate='.$ToEvaluate.' AND Evaluator='.$Evaluator.' AND EvalMonth='.$EvalMonth.''; 
		$stmt01=$link->query($sql01); $row01=$stmt01->fetch();
                $epid=$row01['EPID'];
    if(empty($epid)) { // copied exactly from perfevalsettings.main
		echo 'No evaluator percentage for this position. Please contact JYE.'; exit();
               /*  $sql2 = "INSERT INTO hr_1evaluatorpercentage (ToEvaluate,Evaluator,Percentage,EvalMonth,EncodedByNo) VALUES (".$ToEvaluate.",".$Evaluator.",100,".$EvalMonth.",".$_SESSION['(ak0)'].")"; //echo $sql2; exit();
		$stmt2=$link->prepare($sql2); $stmt2->execute();
                $sql01='SELECT EPID FROM hr_1evaluatorpercentage WHERE ToEvaluate='.$ToEvaluate.' AND Evaluator='.$Evaluator;
		$stmt01=$link->query($sql01); $row01=$stmt01->fetch();
                $epid=$row01['EPID']; */
            }
}

if (in_array($which, array('Statement','StatementAssign','EditSpecificsPS'))){
    echo comboBox($link,'SELECT PerfComID, Competency FROM hr_1competency ORDER BY PerfComID','PerfComID','Competency','competencylist');
}

if (in_array($which, array('StatementAssign','EditSpecificsSA','StatementAssignMultiple', 'StatementSummary', 'StatementSumEvaluator'))){
    echo comboBox($link,'SELECT PositionID, Position FROM attend_0positions ORDER BY Position','PositionID','Position','positionlist');
	
	if (isset($_POST['submit'])){
		$EvalMonth = $_POST['EvalMonth'];
		} else {
			$EvalMonth = 3;
		}
    }
	if (isset($_REQUEST['EvalMonth'])){
		if ($_REQUEST['EvalMonth']==3){
			$color = 'blue';
		} else if ($_REQUEST['EvalMonth']==5) {
			$color = 'green';
		} else if ($_REQUEST['EvalMonth']==1) {
			$color = 'maroon';
		} else {
			$color='yellow';
		}
	} else {
		$color = 'blue';
	}
if (in_array($which, array('StatementAssign','EditSpecificsSA'))){
    echo comboBox($link,'SELECT StatementID, Statement FROM hr_1statement ORDER BY Statement','StatementID','Statement','statementlist');
}

if (in_array($which, array('StatementAssign','StatementSummary','StatementSumEvaluator'))){
    echo '<datalist id="evalmonth"><option value="3">3rd Month</option><option value="5">5th Month</option><option value="12">12th Month</option><option value="1">Annual Evaluation</option></datalist>';
}

//Add/Edit for Performance Competency
if (in_array($which,array('AddPC','EditPC'))){
   $PerfComID=comboBoxValue($link,'hr_1competency','Competency',addslashes($_POST['Competency']),'PerfComID');
   $columnstoadd=array('Competency');
}

if (in_array($which, array('StatementSummary', 'StatementSumEvaluator'))){
    echo '<style> table,td, th, tr {  border: 1px solid black; border-collapse: collapse; padding: 5px; font-size:9pt;margin-left:0%; border: 1px solid black; padding: 3px;}</style>';
}


switch ($which)
{
/*For Performance Competency Setting*/
	//Start of Case List
	case 'CompetencyList':
	
		$title='List of Competencies'; 
                if (allowedToOpen(6841,'1rtc')){
                $formdesc='Add New Competency.';
		$method='post';
				$columnnames=array(
				array('field'=>'Competency','type'=>'text','size'=>25,'required'=>true));
							
		$action='perfevalsettings.php?w=AddPC'; $fieldsinrow=4; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');
		
		//Processes
		$delprocess='perfevalsettings.php?w=DeletePC&PerfComID=';
				
		$title=''; $formdesc=''; 
                }
                $txnid='TxnID';
		$columnnames=$columnnameslist;       
                if (allowedToOpen(6841,'1rtc')){ $editprocess='perfevalsettings.php?w=EditSpecificsPC&PerfComID='; $editprocesslabel='Edit';}
		echo '<div style="width:45%">';
		include('../backendphp/layout/displayastable.php'); 
		echo '</div>';
	break; //End of Case List
	
	//Start of Case AddPC
	case 'AddPC':
		if (allowedToOpen(6841,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql = 'INSERT INTO hr_1competency (Competency, EncodedByNo, TimeStamp) VALUES (\''.$_POST['Competency'].'\','.$_SESSION['(ak0)'].',Now())';
			$stmt=$link->prepare($sql); $stmt->execute();
		}
		header('Location:'.$_SERVER['HTTP_REFERER']);
	break; //End of Case AddPC
	
	//Start Of Case EditSpecificsPC
    case 'EditSpecificsPC':
                if (!allowedToOpen(6841,'1rtc')){ header("Location:".$_SERVER['HTTP_REFERER']);}
		$title='Edit Specifics';
		$txnid=intval($_GET['PerfComID']);

		//Condition For Edit Specifics
		$sql=$sql.' WHERE PerfComID='.$txnid;
		$columnstoedit=$columnstoadd;		
		$columnnames=$columnnameslist;
		
		$editprocess='perfevalsettings.php?w=EditPC&PerfComID='.$txnid;		
		include('../backendphp/layout/editspecificsforlists.php');
	break; //End of Case EditSpecificsPC
	
	//Start Of Case EditPC
    case 'EditPC':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(6841,'1rtc')){
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `hr_1competency` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE PerfComID='.intval($_GET['PerfComID']);
		$stmt=$link->prepare($sql); $stmt->execute();
		}
		header("Location:perfevalsettings.php");
    break; //End of Case EditPC
	
	//Start Of Case DeletePC
    case 'DeletePC':
	//access
        if (allowedToOpen(6841,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM `hr_1competency` WHERE PerfComID='.intval($_GET['PerfComID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:".$_SERVER['HTTP_REFERER']);
    break; //End of Case DeletePC
/*End of Performance Competency Setting*/


/*For Performance Statement Settings*/

	//Start of Case Statement
	case 'Statement':
	
	$sqlcompetency=' ';
	$orderby = ' ORDER BY pc.PerfComID,ps.StatementID ASC';
	 $sql1='SELECT ps.*, pc.Competency, ps.StatementID AS TxnID, StatementID FROM hr_1statement ps JOIN hr_1competency pc ON ps.PerfComID=pc.PerfComID';
	 
	 
	// $columnnameslist=array('Competency','StatementID', 'Statement');
	$columnnameslist=array('TxnID','Statement');
        $title='List of Statements'; $formdesc='Add New Statement.';
	$formdesc.='</i><br><a href="perfevalsettings.php?w=UploadStatements">Upload Statements</a><i>';
		$method='post';
				$columnnames=array(
				array('field'=>'PerfComID', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'competencylist'),
				array('field'=>'Statement','type'=>'text','size'=>60,'required'=>true));

		$action='perfevalsettings.php?w=AddPS'; $fieldsinrow=4; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');

		//Processes
		$delprocess='perfevalsettings.php?w=DeletePS&StatementID=';
		
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		$editprocess='perfevalsettings.php?w=EditSpecificsPS&StatementID='; $editprocesslabel='Edit';
		
		$sql='SELECT PerfComID FROM hr_1competency ORDER BY Competency';
		$stmtp = $link->query($sql);
		
		while($row = $stmtp->fetch()) {
			
			echo '<div style="float:left;width:100%">';
			echo '<br><h3 style="margin-bottom: -1%;">'.comboBoxValue($link,'hr_1competency','PerfComID',$row['PerfComID'],'Competency').'<h3>';
			$sqlcompetency=' WHERE ps.PerfComID='.$row['PerfComID'].'';
			$sql=$sql1.$sqlcompetency.$orderby;
			
			include('../backendphp/layout/displayastablenosort.php');
			echo '</div>';
		}
		 
	break; //End of Case Statement
	
	//Start of Case Statement
	case 'StatementAssign':
	
	$sql2='SELECT PositionID, Position FROM attend_0positions ORDER BY Position';
	$stmt2 = $link->query($sql2);
	
	echo '<form action="perfevalsettings.php?w=StatementAssign" method="GET">';
	echo '<input type="txt" name="w" value="StatementAssign" hidden/>';
	echo 'Position to Evaluate: <select name="positionid">';
	echo '<option value="-1">Default</option>';
	echo '<option value="All">All</option>';
	while($row2= $stmt2->fetch()) {
		echo '<option value="'.$row2['PositionID'].'">'.$row2['Position'].'</option>';
	}
	echo '</select>';
	echo '<input type="submit" name="filterbtn" value="Filter"/>';
	
	if (isset($_GET['filterbtn']))
	{
		if ($_GET['positionid']=='All')
		{
			$condi='';
		}
		else
		{
			$condi=' WHERE ep.ToEvaluate='.$_GET['positionid'];
		}
		$passposition= '&filterbtn=Filter&positionid=' . $_GET['positionid'];
	}
	else
	{
		$condi=' WHERE ps.EPID=1';
		$passposition='';
	}
	echo '</form>';
	
	$sql1='SELECT ps.*, IF(ep.EPID=1, "Default", p.Position) AS ToEvaluate, IF(ep.EPID=1, "Default", p1.Position) AS Evaluator, SUM(Weight) AS TotWeight, s.Statement, Weight AS WeightinPercent, c.Competency, PSID AS TxnID, EvalMonth FROM hr_1positionstatement ps JOIN hr_1statement s ON ps.StatementID=s.StatementID JOIN hr_1competency c ON s.PerfComID=c.PerfComID 
JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID
LEFT JOIN attend_0positions p ON p.PositionID=ep.ToEvaluate LEFT JOIN attend_0positions p1 ON p1.PositionID=ep.Evaluator ' . $condi;
	$stmt11 = $link->query($sql1);
	$row11=$stmt11->fetch(); 
	
	$columnnameslist=array('Competency', 'Statement','WeightinPercent');
		
		$title='Assign Statements to Position ';
		if (isset($_GET['positionid']))
		{
			if ($_GET['positionid']==-1)
			{
				$posi = '(Default)';
			}
			else if ($_GET['positionid']=='All')
			{
				$posi = '(All)';
			}
			else {
				$posi=comboBoxValue($link,'attend_0positions','PositionID',$_GET['positionid'],'Position');
			}
		} else { $posi = '(Default)'; }
		$title.=$posi; 
                $formdesc='</i><br><a href="perfevalsettings.php?w=UploadSA">Upload Assignment of Statements</a><i>';
                $formdesc.='<br/><br/><b>Notes before encoding:</b><br/>'
                        . '<ol style="margin-left: 20px;"><li>Positions where no statements are explicitly assigned will use the default statements.</li>'
                        . '<li>Positions with assigned statements will ONLY take on the assigned statements.</li>'
                        . '<li>After editing, ALWAYS check if competencies have 100% totals.</li>'
                        . '<li><a href="perfevalsettingsmain.php?w=PercentPerEvaluator">Percent Per Evaluator</a> must be established first.</li></ol><i>';
		
		$method='post';
			
				$columnnames=array(
				array('field'=>'Statement', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'statementlist'),
				array('field'=>'ToEvaluate', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'positionlist'),
				array('field'=>'Evaluator','type'=>'text','size'=>10,'required'=>true, 'list'=>'positionlist'),
				array('field'=>'Weight','caption'=>'Weight in Percent','type'=>'text','size'=>10,'required'=>true),
				array('field'=>'EvalMonth','caption'=>'Statement for: ','type'=>'text','size'=>10,'list'=>'evalmonth','required'=>true));

		$action='perfevalsettings.php?w=AddSA'.$passposition; $fieldsinrow=6; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');

		//Processes
		$delprocess='perfevalsettings.php?w=DeleteSA'.$passposition.'&PSID=';
		
			
		
		
		$title=''; $formdesc=''; $txnid='TxnID';
		
		$columnnames=$columnnameslist;       
		$editprocess='perfevalsettings.php?w=EditSpecificsSA'.$passposition.'&PSID='; $editprocesslabel='Edit';
		
		$groupcon = '  GROUP BY StatementID ORDER BY Competency';
		
		$sql=$sql1 . ' AND EvalMonth=3'.$groupcon;
		$stmt=$link->prepare($sql); $stmt->execute();
		$datatoshow=$stmt->fetch(PDO::FETCH_ASSOC);
		$addsubtitle=' To Evaluate: '.$datatoshow['ToEvaluate'].', Evaluator: '.$datatoshow['Evaluator'].'';
		echo '<br/><h3 style="color:blue">3rd Month<br>'.$addsubtitle.'</h3>';
		
		$showgrandtotal=true; $coltototal='TotWeight';
		include('../backendphp/layout/displayastablenosort.php');
		
		// echo '<br/><br/><h3 style="color:green">5th Month<br>'.$addsubtitle.'</h3>';
		$sql=$sql1 . ' AND EvalMonth=5'.$groupcon;
		$stmt=$link->prepare($sql); $stmt->execute();
		$datatoshow=$stmt->fetch(PDO::FETCH_ASSOC);
		$addsubtitle=' To Evaluate: '.$datatoshow['ToEvaluate'].', Evaluator: '.$datatoshow['Evaluator'].'';
		echo '<br/><br/><h3 style="color:green">5th Month<br>'.$addsubtitle.'</h3>';
		
		include('../backendphp/layout/displayastablenosort.php');
		
		// echo '<br/><br/><h3 style="color:yellow">12th Month<br>'.$addsubtitle.'</h3>';
		$sql=$sql1 . ' AND EvalMonth=12'.$groupcon;
		$stmt=$link->prepare($sql); $stmt->execute();
		$datatoshow=$stmt->fetch(PDO::FETCH_ASSOC);
		$addsubtitle=' To Evaluate: '.$datatoshow['ToEvaluate'].', Evaluator: '.$datatoshow['Evaluator'].'';
		echo '<br/><br/><h3 style="color:yellow">12th Month<br>'.$addsubtitle.'</h3>';
		
		include('../backendphp/layout/displayastablenosort.php');
		
		$addlprocess='perfevalsettings.php?w=StatementSummary&PSID=';
		$addlprocesslabel='Look Up';
			
		echo '<br/><br/><h3 style="color:maroon">Annual Evaluations</h3>';
		array_push($columnnameslist,'ToEvaluate','Evaluator');
		$columnnames=$columnnameslist;
		$sql=$sql1 . ' AND EvalMonth=1 GROUP BY StatementID,ep.Evaluator ORDER BY ep.Evaluator,Competency,Statement';
		unset($showgrandtotal,$coltototal);
		include('../backendphp/layout/displayastable.php');
		
		$sql0='CREATE TEMPORARY TABLE tempTotalWeight AS '.$sql;
		$stmt=$link->prepare($sql0);$stmt->execute();
		
		$sql='SELECT SUM(Weight) AS TotalPercentage, Evaluator FROM tempTotalWeight GROUP BY Evaluator;';
		$stmttotal=$link->query($sql); //$restotal=$stmttotal->fetch();
		
		$totals='<table style="background-color:FFFFFF;"><tr><td colspan=2>Total Percentage for Annual Evaluation</td></tr>';
		while($rowtotal= $stmttotal->fetch()) {
			$totals.='<tr><td>'.$rowtotal['Evaluator'].'</td><td>'.($rowtotal['TotalPercentage']<>100?'<font color="red">':'<font>').$rowtotal['TotalPercentage'].'%</font></td></tr>';
		}
		$totals.='</table>';
		echo $totals;
		
	break; //End of Case Statement
	
	
	//Start of Case Statement Multiple
	case 'StatementAssignMultiple':
	echo '<style style="text/css">
                        .hoverTable tr:hover {
                        background-color: #FFFFCC;
                }</style>';
	
	
	$title='Assign Multiple Statements to Positions'; 
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br/>';
	echo '<b>Notes before encoding:</b>
	<ol style="margin-left: 20px;"><li>Positions where no statements are explicitly assigned will use the default statements.</li>'
                        . '<li>Positions with assigned statements will ONLY take on the assigned statements.</li>'
                        . '<li>After editing, ALWAYS check if competencies have 100% totals.</li></ol><br/>';
						
	echo '<form action="#" method="POST">';
	
	echo '<input type="submit"  name="showencstmt" value="My Encoded Statements"/>';
	echo ' <input type="submit" name="showotherstmt" value="Statements of Others"/>';
	echo ' <input type="submit" name="showgeneral" value="Generalized Statements"/>';
	echo ' <input type="submit" name="showallstmt" value="All Statements"/>';
	
	echo '</form><br/>';
	echo '<h3>';
	if (isset($_POST['showotherstmt'])){
		echo 'Statements of Others';
		$condi='WHERE ps.EncodedByNo<>'.$_SESSION['(ak0)'].'';
	} else if (isset($_POST['showgeneral'])) {
		echo 'Generalized Statements';
		$condi='WHERE StatementID>=275 AND StatementID<=331';
	} else if (isset($_POST['showallstmt'])) {
		echo 'All Statements';
		$condi='';
	} else {
		echo 'My Encoded Statements';
		$condi='WHERE ps.EncodedByNo='.$_SESSION['(ak0)'].' OR StatementID<=23';
	}
	echo '</h3>';
	
	
	echo '<br/><form action="perfevalsettings.php?w=AddMultiSA" method="post">';
	
	$sql2='SELECT PositionID, Position FROM attend_0positions ORDER BY Position';
	$stmt2 = $link->query($sql2);
	
	echo 'To Evaluate: <select name="ToEvaluate">';
	echo '<option value="-1">Default</option>';
	while($row2= $stmt2->fetch()) {
		echo '<option value="'.$row2['PositionID'].'">'.$row2['Position'].'</option>';
	}
	echo '</select>';
	
	$stmt3 = $link->query($sql2);
	
	echo ' Evaluator: <select name="Evaluator">';
	echo '<option value="-1">Default</option>';
	while($row3= $stmt3->fetch()) {
		echo '<option value="'.$row3['PositionID'].'">'.$row3['Position'].'</option>';
	}
	echo '</select>';
	
	echo ' Statements For: <select name="EvalMonth">';
		echo '<option value="3">3rd Month</option>';
		echo '<option value="5">5th Month</option>';
		echo '<option value="12">12th Month</option>';
		echo '<option value="1">Annual Evaluation</option>';
	echo '</select>';
	echo str_repeat("&nbsp;",25);
	echo ' <input type="submit" value="Assign Statement"/>';
	
	$sql='SELECT ps.*, pc.Competency, ps.StatementID AS TxnID FROM hr_1statement ps JOIN hr_1competency pc ON ps.PerfComID=pc.PerfComID '. $condi .' ORDER BY pc.PerfComID,ps.Statement ASC';
	$stmt=$link->query($sql);
	$oldcompetency='';
	$backcolor='';
	echo '<br><br><table class="hoverTable">';
	echo '<thead><tr><th>Competency</th><th>Statement</th><th>Checkbox</th></tr></thead>';
		while ($row = $stmt->fetch())
		{
            echo '<tr>'.($oldcompetency<>$row['Competency']?'<td width="300px"><b>'.$row['Competency'].'</b>':'<td>').'</td><td style="background-color:'.$backcolor.';">'.$row['Statement'].'</td><td><input type="checkbox" name="perfstateid[]" value="'.$row['StatementID'].'"/></td></tr>';
			$oldcompetency=$row['Competency'];
        }
	echo '</table>';
	echo '</form>';
	
	break; 
        
        case 'AddMultiSA':
		// print_r($_POST); exit();
            //break;
            if (isset($_REQUEST['perfstateid'])){
	$perfstateidimp = implode(',', $_REQUEST['perfstateid']);
	$perfs = explode(',', $perfstateidimp);

	foreach ($perfs as $perf)
	{
		$statement = $link->prepare("INSERT INTO hr_1positionstatement (StatementID,EPID,EncodedByNo) VALUES (?,".$epid.",".$_SESSION['(ak0)'].")");		
		$statement->execute(array($perf));
	}
	
	// $_SESSION['toevaluate']=comboBoxValue($link,'attend_0positions','PositionID',$_GET['toevaluate'],'Position');
	$_SESSION['toevaluate']=comboBoxValue($link,'attend_0positions','PositionID',$_POST['ToEvaluate'],'Position');
        $_SESSION['submit']=true;

        if (empty($_SESSION['toevaluate']))
        {
                unset($_SESSION['toevaluate']);
                unset($_SESSION['submit']);

                header("Location:perfevalsettings.php?w=StatementSummary&PSID=1");
        }
        else
        {
                header("Location:perfevalsettings.php?w=StatementSummary");
        }
}
else
{
	echo 'Please select at least 1 statement.';
}
            
            break;
	
	//Start of Case Statement
	case 'StatementSummary':
            
		echo '<br/><font style=\'size:80%;margin-left:15px;\'>Red font: Incomplete Weight Percent. Please complete the 100% in weights.</font><br/><br/>';
                
            if (isset($_SESSION['toevaluate']))
                {
                        $_POST['ToEvaluate']=$_SESSION['toevaluate'];
                        $_POST['submit']=$_SESSION['submit'];

                        unset($_SESSION['toevaluate']);
                        unset($_SESSION['submit']);
                }
                echo '<form method="post" action="perfevalsettings.php?w=StatementSummary" enctype="multipart/form-data">
                    Position to Evaluate <input type=text size=10 name=ToEvaluate list=positionlist > 
					Statements for month <input type=text size=10 name=EvalMonth list=evalmonth >
                    <input type=submit name=submit value=Lookup></form><br/><br/>';
		
		
		
		$sql3 = 'SELECT Position, ToEvaluate, Evaluator FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID '
                        . 'LEFT JOIN attend_0positions p ON ep.ToEvaluate=p.PositionID WHERE '
                        .(isset($_POST['submit'])?' Position LIKE "'.addslashes($_POST['ToEvaluate']).'"':'PSID = '.$_GET['PSID']); //echo $sql3;
		$stmt3=$link->query($sql3); $row3 = $stmt3->fetch();
		
                if (empty($row3['Position'])) { $title='Default Evaluation'; $toevaluate="-1"; }
		else { $title='Evaluation for '.$row3['Position'].' (<font color="'.$color.'">'.($EvalMonth==1?'Annual Evaluation':$EvalMonth.' Months').'</font>)' ; $toevaluate=$row3['ToEvaluate']; }
		echo '<title>'.$title.'</title><h3>'.$title.'</h3>'; 
	
	
	$sql0='CREATE TEMPORARY TABLE statelist AS SELECT ps.PSID, s.StatementID, s.PerfComID, IF(ep.EPID=1, "-1", p.Position) AS ToEvaluate, IF(ep.EPID=1, "Default", p1.Position) AS Evaluator, ep.Evaluator AS EvaluatorID, s.Statement, Weight AS WeightinPercent, PSID AS TxnID FROM hr_1positionstatement ps JOIN hr_1statement s ON ps.StatementID=s.StatementID '
                . 'JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID LEFT JOIN attend_0positions p ON p.PositionID=ep.ToEvaluate LEFT JOIN attend_0positions p1 ON p1.PositionID=ep.Evaluator WHERE EvalMonth='.$EvalMonth.' AND ep.ToEvaluate='.$toevaluate;
        $stmt=$link->query($sql0); //echo $sql0;
        $sql0='SELECT DISTINCT EvaluatorID, Evaluator FROM statelist;';
	$stmt0=$link->query($sql0); $row0=$stmt0->fetchAll();
        // echo $sql0;
        $num=0;
		$overalltotal=0;
        foreach($row0 as $evalby){
			$sql01='SELECT SUM(Weight) AS TotSum FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate='.$toevaluate.' AND Evaluator='.$evalby['EvaluatorID'].' AND EvalMonth='.$EvalMonth.'';
			$stmt01=$link->query($sql01); $row01=$stmt01->fetch();
			
			$sql02='SELECT Percentage FROM hr_1evaluatorpercentage WHERE ToEvaluate='.$toevaluate.' AND Evaluator='.$evalby['EvaluatorID'].' AND EvalMonth='.$EvalMonth.'';
			$stmt02=$link->query($sql02); $row02=$stmt02->fetch();
			//Assigning percentage of each evaluator
			// if ($_SESSION['(ak0)']==1001 OR $_SESSION['(ak0)']==1002) { $style=''; $disabled='';} else { $style = 'display:none;'; $disabled='disabled';}
			if (allowedToOpen(6844,'1rtc')) { $disabled='';} else { $disabled='disabled';}
            $evallist='<br><br><h4>Evaluator: '.$evalby['Evaluator']. ', Total Weights: <font style=\'font-size:130%;font-weight:bold;'.(($row01['TotSum']<>100)?'color:red;':'').'\'>'.$row01['TotSum'].'%&nbsp;</font></td></h4>';
            //removed this from here
//            <p><form action="assignpercentperevaluator.php">Assign Percent per Evaluator <input type="text" name="percent" size="5" value="'.$row02['Percentage'].'" '.$disabled.'/><input type="text" name="toevaluate" value="'.$toevaluate.'" hidden/><input type="text" name="evaluator" value="'.$evalby['EvaluatorID'].'" hidden/><input type="submit" value="Submit" '.$disabled.'/></form></p>
            $sql1='SELECT ps.PerfComID, Competency, COUNT(ps.PerfComID) AS NoofStatements, SUM(ps.WeightinPercent) as WeightSum FROM statelist ps JOIN `hr_1competency` c ON c.PerfComID=ps.PerfComID WHERE EvaluatorID='.$evalby['EvaluatorID'].' GROUP BY ps.PerfComID, ps.EvaluatorID ; '; $stmt1=$link->query($sql1);
            $stmt1=$link->query($sql1);
        foreach	($stmt1->fetchAll() as $comp){
            $statementspercomp=$comp['NoofStatements']+1;
            $evallist.='<tr><td rowspan="'.$statementspercomp.'" >'.$comp['Competency'].'</td>';
            $sql2='SELECT * FROM statelist WHERE PerfComID='.$comp['PerfComID'].' AND EvaluatorID='.$evalby['EvaluatorID']; $stmt2=$link->query($sql2);
            $first=0;
            while ($row = $stmt2->fetch())
		{
                // $evallist.=(($first<$statementspercomp)?'<tr>':'').'<td valign=\'top\'>'.($num + 1).'. ' . $row['Statement'] .'</td>'
                        // . '<td valign=\'top\'>' . $row['Evaluator'] . '</td>'
                        // . '<td style="size: 15px;" valign=\'top\'>' . $row['WeightinPercent'] . '</td>'
                        // .(($first==0)?'<td  rowspan="'.$statementspercomp.'" align=\'center\' valign=\'top\'>'
                    // . '<font style=\'font-size:160%;font-weight:bold;'.(($comp['WeightSum']<>100)?'color:red;':'').'\'>'.$comp['WeightSum'].'%</font></td></tr>':'</tr>');
                $evallist.=(($first<$statementspercomp)?'<tr>':'').'<td valign=\'top\'>'.($num + 1).'. ' . $row['Statement'] .'</td>'
                       // . '<td valign=\'top\'>' . $row['Evaluator'] . '</td>'
                        . '<td align="right" style="size: 15px;" valign=\'top\'>' . $row['WeightinPercent'] . '</td>'
                        .(($first==0)?'</tr>':'</tr>');
                
                $num++; $first++;
            }
            $evallist.='';
            // $num=0;
        }
        		
		echo '<table><thead ><th>Competency</th><th>Statement</th>'
        . '<th align="center">Weight</th></thead><tbody>'.$evallist.'</tbody></table>'; //<th>Evaluator</th>
		
		$num=0;
		$overalltotal = $overalltotal + $row02['Percentage'];
        }
	// echo $overalltotal;
	if (allowedToOpen(6844,'1rtc'))
	{
		// echo '<br>Overall Total of Evaluator Distribution: <font style=\'font-size:160%;font-weight:bold;'.(($overalltotal<>100)?'color:red;':'').'\'>'.$overalltotal.'%</font>';
	}
	break; //End of Case Statement
	
	
	//Start of Case Statement
	case 'StatementSumEvaluator':
            
		echo '<br/><font style=\'size:80%;margin-left:15px;\'>Red font: Incomplete Weight Percent. Please complete the 100% per competency.</font><br/><br/>';
           
                echo '<form method="post" action="perfevalsettings.php?w=StatementSumEvaluator" enctype="multipart/form-data">
                    Position of Evaluator<input type=text size=10 name=Evaluator list=positionlist >
					Statements for month <input type=text size=10 name=EvalMonth list=evalmonth >
                    <input type=submit name=submit value=Lookup></form><br/><br/>';
		
		$sql3 = 'SELECT Position, ToEvaluate, Evaluator FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID LEFT JOIN attend_0positions p ON ep.Evaluator=p.PositionID WHERE '
                        .(isset($_POST['submit'])?' Position LIKE "'.addslashes($_POST['Evaluator']).'"':'PSID = '.$_GET['PSID']); 
		$stmt3=$link->query($sql3); $row3 = $stmt3->fetch();
		
                if (empty($row3['Position'])) { $title='Default Evaluation '.(isset($_POST['Evaluator'])?$_POST['Evaluator']:''); $evaluator="-1"; }
		else { $title='Evaluator: '.$row3['Position'].'  (<font color="'.$color.'">'.($EvalMonth==1?'Annual Evaluation':$EvalMonth.' Months').'</font>)'; $evaluator=$row3['Evaluator']; }
		echo '<title>'.$title.'</title><h3>'.$title.'</h3>'; 
	
	$sql0='CREATE TEMPORARY TABLE statelist AS SELECT ps.PSID, s.StatementID, s.PerfComID, IF(ep.EPID=1, "Default", p.Position) AS ToEvaluate, IF(ep.EPID=1, "Default", p1.Position) AS Evaluator, ep.Evaluator AS EvaluatorID, ep.ToEvaluate AS ToEvaluateID, s.Statement, Weight AS WeightinPercent, PSID AS TxnID FROM hr_1positionstatement ps JOIN hr_1statement s ON ps.StatementID=s.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID LEFT JOIN attend_0positions p ON p.PositionID=ep.ToEvaluate LEFT JOIN attend_0positions p1 ON p1.PositionID=ep.Evaluator WHERE EvalMonth='.$EvalMonth.'  AND ep.Evaluator='.$evaluator;
        $stmt=$link->query($sql0);
        $sql0='SELECT DISTINCT ToEvaluateID, ToEvaluate FROM statelist;';
	$stmt0=$link->query($sql0); $row0=$stmt0->fetchAll();
        
		
        $num=0;
        foreach($row0 as $evalby){
			$sql01='SELECT SUM(Weight) AS TotSum FROM hr_1positionstatement ps JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID WHERE ToEvaluate='.$evalby['ToEvaluateID'].' AND Evaluator='.$evaluator.' AND EvalMonth='.$EvalMonth.'';
			
			$stmt01=$link->query($sql01); $row01=$stmt01->fetch();
            $evallist='<br><br><h4>To Evaluate: '.$evalby['ToEvaluate'].', Total Weights: <font style=\'font-size:130%;font-weight:bold;'.(($row01['TotSum']<>100)?'color:red;':'').'\'>'.$row01['TotSum'].'%&nbsp;</font></h4>';
            $sql1='SELECT ps.PerfComID, Competency, COUNT(ps.PerfComID) AS NoofStatements, SUM(ps.WeightinPercent) as WeightSum FROM statelist ps JOIN `hr_1competency` c ON c.PerfComID=ps.PerfComID WHERE ToEvaluateID='.$evalby['ToEvaluateID'].' GROUP BY ps.PerfComID, ps.ToEvaluateID ; '; $stmt1=$link->query($sql1);
            $stmt1=$link->query($sql1);
        foreach	($stmt1->fetchAll() as $comp){
            $statementspercomp=$comp['NoofStatements']+1;
            $evallist.='<tr><td rowspan="'.$statementspercomp.'" >'.$comp['Competency'].'</td>';
            $sql2='SELECT * FROM statelist WHERE PerfComID='.$comp['PerfComID'].' AND ToEvaluateID='.$evalby['ToEvaluateID']; $stmt2=$link->query($sql2);
            $first=0;
            while ($row = $stmt2->fetch())
		{
                $evallist.=(($first<$statementspercomp)?'<tr>':'').'<td valign=\'top\'>'.($num + 1).'. ' . $row['Statement'] . '</td>'
                        . '<td valign=\'top\'>' . $row['ToEvaluate'] . '</td>'
                        . '<td style="size: 15px;" valign=\'top\'>' . $row['WeightinPercent'] . '</td>'
                        .(($first==0)?'</tr>':'</tr>');
                
                $num++; $first++;
            }
            $evallist.='';
            
        }
        		
		echo '<table><thead ><th>Competency</th><th>Statement</th>'
        . '<th>To Evaluate</th><th align="center">Weight</th></thead><tbody>'.$evallist.'</tbody></table>';
        }
	
	break; //End of Case Statement
	
	//Start Of Case AddSA
	case 'AddSA': //break;
		if (allowedToOpen(6840,'1rtc')){
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';  
                
			$sql='INSERT INTO `hr_1positionstatement` SET EPID='.$epid.', EncodedByNo='.$_SESSION['(ak0)'].', Weight='.$_POST['Weight'].', StatementID='.$StatementID.', TimeStamp=Now()';
			// echo $sql; exit();
			$link->query($sql);
		}
	// header('Location:'.$_SERVER['HTTP_REFERER']);
	if (isset($_GET['filterbtn']))
		{
			$passposition= '&filterbtn='.$_GET['filterbtn'].'&positionid=' . $_GET['positionid'];
		}
		else {$passposition='';}
	header("Location:perfevalsettings.php?w=StatementAssign".$passposition);
	break; //End of Case AddSA
	
	
	//Start Of Case AddPS
	case 'EditSA':
		if (allowedToOpen(6840,'1rtc')){
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            		$sql='UPDATE `hr_1positionstatement` SET EncodedByNo='.$_SESSION['(ak0)'].', Weight='.$_POST['Weight'].', StatementID='.$StatementID.', TimeStamp=Now() WHERE PSID='.$_GET['PSID'].'';
					
			// echo $sql; break;
			$link->query($sql);
		}
		if (isset($_GET['filterbtn']))
		{
			$passposition= '&filterbtn='.$_GET['filterbtn'].'&positionid=' . $_GET['positionid'];
		}
		else {$passposition='';}
		// $passposition= '&filterbtn='.$_GET['filterbtn'].'&positionid=' . $_GET['positionid'];
	// header('Location:'.$_SERVER['HTTP_REFERER']);
	header("Location:perfevalsettings.php?w=StatementAssign".$passposition);
	break; //End of Case AddPS
	
	
	//Start Of Case AddPS
	case 'AddPS':
		if (allowedToOpen(6840,'1rtc')){
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            $sql='';
			foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
			$sql='INSERT INTO `hr_1statement` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' PerfComID='.$PerfComID.', TimeStamp=Now()'; 
			// echo $sql;
			$link->query($sql);
		}
	header('Location:'.$_SERVER['HTTP_REFERER']);
	
	break; //End of Case AddPS
	
	
	//Start Of Case EditPS
	case 'EditPS':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(6840,'1rtc')){
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `hr_1statement` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' PerfComID='.$PerfComID.', TimeStamp=Now() WHERE StatementID='.intval($_GET['StatementID']);
		$sql.=allowedToOpen(6843,'1rtc')?' AND EncodedByNo='.$_SESSION['(ak0)']:'';
		$stmt=$link->prepare($sql);
		$stmt->execute();
		}
		
		header("Location:perfevalsettings.php?w=Statement");
    break; //End Of Case EditPS
	
	
	//Start Of Case DeletePS
    case 'DeletePS':
	//access
        if (allowedToOpen(6840,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `hr_1statement` WHERE StatementID='.intval($_GET['StatementID']);
                $sql.=allowedToOpen(6843,'1rtc')?' AND EncodedByNo='.$_SESSION['(ak0)']:'';
        $stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:".$_SERVER['HTTP_REFERER']);
    break; //End of Case DeletePS
	
	//Start Of Case DeletePS
    case 'DeleteSA':
	//access
        if (allowedToOpen(6840,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `hr_1positionstatement` WHERE PSID='.intval($_GET['PSID']);
        $stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:".$_SERVER['HTTP_REFERER']);
    break; //End of Case DeletePS
	
	//Start Of Case EditSpecificsPS
    case 'EditSpecificsPS':
		$title='Edit Specifics'; $formdesc='Dept Heads (except HR) may edit own statements only.';
		$txnid=intval($_GET['StatementID']);
                $sql='SELECT ps.*,pc.Competency AS PerfComID, ps.StatementID AS TxnID FROM hr_1statement AS ps JOIN hr_1competency AS pc ON pc.PerfComID = ps.PerfComID  '
                        . ' WHERE StatementID='.$txnid;
                $sql.=allowedToOpen(6843,'1rtc')?' AND ps.EncodedByNo='.$_SESSION['(ak0)']:'';
                echo comboBox($link,'SELECT PerfComID, Competency FROM hr_1competency ORDER BY PerfComID','PerfComID','Competency','competencylist');
                $columnnameslist=array('PerfComID','Statement');
                $columnstoadd=array('PerfComID','Statement');

		$columnstoedit=$columnstoadd;
		$columnnames=$columnnameslist;
		
		$columnswithlists=array('PerfComID');
		$listsname=array('PerfComID'=>'competencylist');
		
		$editprocess='perfevalsettings.php?w=EditPS&StatementID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break; //End of Case EditSpecificsPS
	
	//Start Of Case EditSpecificsPS
    case 'EditSpecificsSA':
		$title='Edit Specifics';
		$txnid=intval($_GET['PSID']);
                $sql='SELECT ps.*, s.Statement,pc.Competency AS PerfComID, IFNULL(p.Position,-1) AS ToEvaluate, IFNULL(p1.Position,-1) AS Evaluator, s.StatementID AS TxnID FROM hr_1statement AS s JOIN hr_1competency AS pc ON pc.PerfComID = s.PerfComID JOIN hr_1positionstatement ps ON ps.StatementID=s.StatementID JOIN `hr_1evaluatorpercentage` ep ON ps.EPID=ep.EPID LEFT JOIN attend_0positions p ON p.PositionID=ep.ToEvaluate LEFT JOIN attend_0positions p1 ON ep.Evaluator=p1.PositionID';
                
                $columnnameslist=array('Statement', 'ToEvaluate', 'Evaluator', 'Weight');
                $columnstoadd=array('Statement', 'ToEvaluate', 'Evaluator', 'Weight');

		//Condition For Edit Specifics
		$sql=$sql.' WHERE PSID='.$txnid;
		// $columnstoedit=array('PerfComID');
		$columnstoedit=$columnstoadd;
		// echo $sql;
		$columnnames=$columnnameslist;
		// echo $sql;
		$columnswithlists=array('Statement','ToEvaluate','Evaluator');
		$listsname=array('Statement'=>'statementlist','ToEvaluate'=>'positionlist','Evaluator'=>'positionlist');
		
		if (isset($_GET['filterbtn']))
		{
			$passposition= '&filterbtn='.$_GET['filterbtn'].'&positionid=' . $_GET['positionid'];
		}
		else {$passposition='';}
		$editprocess='perfevalsettings.php?w=EditSA'.$passposition.'&PSID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break; //End of Case EditSpecificsPS

    case 'UploadStatements': 
        $title='Upload Statements';
        $colnames=array('PerfComID','Statement');
        $requiredcol=array('PerfComID','Statement');
        $required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
        $specific_instruct='Text with commas must be enclosed in quotation marks, or else it will NOT be uploaded correctly.<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='hr_1statement'; $firstcolumnname='PerfComID';
        $DOWNLOAD_DIR="../../uploads/"; ; $requireencodedby=true; $requiredts=true;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="perfevalsettings.php?w=Statement" target="_blank">Lookup Newly Imported Data</a>';}
break;

    case 'UploadSA': 
        $title='Upload Assignment of Statements';
        $colnames=array('StatementID','EPID','Weight');
        $requiredcol=array('StatementID','EPID','Weight');
        $required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
        $specific_instruct='Lists of StatementID <a href="perfevalsettings.php?w=Statement" target="_blank">here</a>, List of EPID <a href="perfevalsettingsmain.php?w=PercentPerEvaluator" target="_blank">here</a>.<br/><br/>'
                . '<i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='hr_1positionstatement'; $firstcolumnname='StatementID';
        $DOWNLOAD_DIR="../../uploads/"; ; $requireencodedby=true; $requiredts=true;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="perfevalsettings.php?w=StatementAssign" target="_blank">Lookup Newly Imported Data</a>';}
break;

    case 'Missing':
        $title='Missing Evaluations for Existing Positions';
        $columnnames=array('Department','Position','EvalMonth');
        $sql0='SELECT Position, Department, ';        
        $sql1=' AS EvalMonth FROM attend_0positions p1 JOIN 1departments d ON d.deptid=p1.deptid WHERE PositionID not in (select ToEvaluate  FROM hr_1positionstatement ps JOIN hr_1evaluatorpercentage ep ON ep.EPID=ps.EPID JOIN attend_0positions p ON p.PositionID=ep.ToEvaluate JOIN attend_0positions p1 ON p1.PositionID=ep.Evaluator JOIN 1departments d ON d.deptid=p.deptid WHERE EvalMonth=';
        $sql2=' GROUP BY ep.EPID,ep.EvalMonth ) AND PositionID IN (SELECT positionid FROM attend_30currentpositions) AND PositionID NOT IN (99,100) ';
        
        $sql=$sql0.' "Annual" '.$sql1.'1'.$sql2.' UNION '.$sql0.' "3" '.$sql1.'3'.$sql2.' UNION '.$sql0.' "5" '.$sql1.'5'.$sql2.' UNION '.$sql0.' "12" '.$sql1.'12'.$sql2.' ORDER BY EvalMonth, Department, Position'; // echo $sql;
        $width='30%';
        include '../backendphp/layout/displayastable.php';
break;

}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
