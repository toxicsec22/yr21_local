<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6840,'1rtc')) { echo 'No permission'; exit; }
include_once('../switchboard/contents.php');



 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');
?>
<br><div id="section" style="display: block;">

    <div><a id='link' href="perfevalsettings.php">Competency List</a>
        <a id='link' href="perfevalsettings.php?w=Statement">Statement List</a>
        <a id='link' href="perfevalsettings.php?w=StatementAssign">Assign Statements to Positions</a>
	<a id='link' href="perfevalsettings.php?w=StatementSummary&PSID=21">Evaluation List per Position</a>
    </div><br/><br/>
<?php
$which=(!isset($_GET['w'])?'CompetencyList':$_GET['w']);

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
	// $PerfComID=comboBoxValue($link,'hr_1competency','Competency',addslashes($_POST['PerfComID']),'PerfComID');
	$StatementID=comboBoxValue($link,'hr_1statement','Statement',addslashes($_POST['Statement']),'PerfStateID');
	$ToEvaluate=comboBoxValue($link,'attend_0positions','Position',addslashes($_POST['ToEvaluate']),'PositionID');
	$Evaluator=comboBoxValue($link,'attend_0positions','Position',addslashes($_POST['Evaluator']),'PositionID');
	// $columnstoadd=array('StatementID');
}

if (in_array($which, array('Statement','StatementAssign','EditSpecificsPS'))){
    echo comboBox($link,'SELECT PerfComID, Competency FROM hr_1competency ORDER BY PerfComID','PerfComID','Competency','competencylist');
}

if (in_array($which, array('StatementAssign','EditSpecificsSA', 'StatementSummary'))){
    echo comboBox($link,'SELECT PositionID, Position FROM attend_0positions ORDER BY PositionID','PositionID','Position','positionlist');
    }

if (in_array($which, array('StatementAssign','EditSpecificsSA'))){
    echo comboBox($link,'SELECT PerfStateID, Statement FROM hr_1statement ORDER BY PerfStateID','PerfStateID','Statement','statementlist');
}

//Add/Edit for Performance Competency
if (in_array($which,array('AddPC','EditPC'))){
   $PerfComID=comboBoxValue($link,'hr_1competency','Competency',addslashes($_POST['Competency']),'PerfComID');
   $columnstoadd=array('Competency');
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
	
	$sql='SELECT ps.*, pc.Competency, ps.PerfStateID AS TxnID, ps.PerfStateID AS StatementID FROM hr_1statement ps JOIN hr_1competency pc ON ps.PerfComID=pc.PerfComID ORDER BY pc.PerfComID,ps.PerfStateID ASC';
	$columnnameslist=array('Competency','StatementID', 'Statement');
        $title='List of Statements'; $formdesc='Add New Statement.';
	$formdesc.='</i><br><a href="perfevalsettings.php?w=UploadStatements">Upload Statements</a><i>';
		$method='post';
				$columnnames=array(
				array('field'=>'PerfComID', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'competencylist'),
				array('field'=>'Statement','type'=>'text','size'=>60,'required'=>true));

		$action='perfevalsettings.php?w=AddPS'; $fieldsinrow=4; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');

		//Processes
		$delprocess='perfevalsettings.php?w=DeletePS&PerfStateID=';
		
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		$editprocess='perfevalsettings.php?w=EditSpecificsPS&PerfStateID='; $editprocesslabel='Edit';
		
		echo '<div style="float:left;width:70%">';
		include('../backendphp/layout/displayastable.php');
		echo '</div>';
		
	break; //End of Case Statement
	
	//Start of Case Statement
	case 'StatementAssign':
	
	$sql='SELECT ps.*, IF(ToEvaluate=-1, "Default", p.Position) AS ToEvaluate, IF(Evaluator=-1, "Default", p1.Position) AS Evaluator, s.Statement, Weight AS WeightinPercent, c.Competency, PSID AS TxnID FROM hr_1positionstatement ps JOIN hr_1statement s ON ps.StatementID=s.PerfStateID JOIN hr_1competency c ON s.PerfComID=c.PerfComID LEFT JOIN attend_0positions p ON p.PositionID=ps.ToEvaluate LEFT JOIN attend_0positions p1 ON p1.PositionID=ps.Evaluator';

	$columnnameslist=array('Competency', 'Statement', 'ToEvaluate', 'Evaluator', 'WeightinPercent');
		
		$title='Assign Statements to Positions'; 
                $formdesc='</i><br><a href="perfevalsettings.php?w=UploadSA">Upload Assignment of Statements</a><i>';
                $formdesc.='<br/><br/><b>Notes before encoding:</b><br/>'
                        . '<ol style="margin-left: 20px;"><li>Positions where no statements are explicitly assigned will use the default statements.</li>'
                        . '<li>Positions with assigned statements will ONLY take on the assigned statements.</li>'
                        . '<li>After editing, ALWAYS check if competencies have 100% totals.</li></ol><i>';
		
		$method='post';
				$columnnames=array(
				array('field'=>'Statement', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'statementlist'),
				array('field'=>'ToEvaluate', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'positionlist'),
				array('field'=>'Evaluator','type'=>'text','size'=>10,'required'=>true, 'list'=>'positionlist'),
				array('field'=>'Weight','caption'=>'Weight in Percent','type'=>'text','size'=>10,'required'=>true));

		$action='perfevalsettings.php?w=AddSA'; $fieldsinrow=5; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');

		//Processes
		$delprocess='perfevalsettings.php?w=DeleteSA&PSID=';
		
			$addlprocess='perfevalsettings.php?w=StatementSummary&PSID=';
			$addlprocesslabel='Look Up';
		
		
		$title=''; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		$editprocess='perfevalsettings.php?w=EditSpecificsSA&PSID='; $editprocesslabel='Edit';
		
		// echo '<div style="float:left;width:70%">';
		include('../backendphp/layout/displayastable.php');
		// echo '</div>';
		
	break; //End of Case Statement
	
	
	//Start of Case Statement
	case 'StatementSummary':
                echo '<form method="post" action="perfevalsettings.php?w=StatementSummary" enctype="multipart/form-data">
                    Position to Evaluate<input type=text size=10 name=ToEvaluate list=positionlist >
                    <input type=submit name=submit value=Lookup></form><br/><br/>';
		$sql3 = 'SELECT Position, ToEvaluate FROM hr_1positionstatement ps LEFT JOIN attend_0positions p ON ps.ToEvaluate=p.PositionID WHERE '
                        .(isset($_POST['submit'])?' Position LIKE "'.addslashes($_POST['ToEvaluate']).'"':'PSID = '.$_GET['PSID']); 
		$stmt3=$link->query($sql3); $row3 = $stmt3->fetch();
		
                if (empty($row3['Position'])) { $title='Default Evaluation'; $toevaluate="-1"; }
		else { $title='Evaluation for '.$row3['Position']; $toevaluate=$row3['ToEvaluate']; }
		echo '<title>'.$title.'</title><h3>'.$title.'</h3>'; 
		
	$sql0='CREATE TEMPORARY TABLE statelist AS SELECT ps.PSID, ps.StatementID, s.PerfStateID, s.PerfComID, IF(ToEvaluate=-1, "-1", p.Position) AS ToEvaluate, IF(Evaluator=-1, "Default", p1.Position) AS Evaluator, s.Statement, Weight AS WeightinPercent, PSID AS TxnID FROM hr_1positionstatement ps JOIN hr_1statement s ON ps.StatementID=s.PerfStateID LEFT JOIN attend_0positions p ON p.PositionID=ps.ToEvaluate LEFT JOIN attend_0positions p1 ON p1.PositionID=ps.Evaluator WHERE ps.ToEvaluate='.$toevaluate;
        $stmt=$link->query($sql0);
        $sql='SELECT * FROM statelist';
        

		echo '<style> table,td, th, tr {  border: 1px solid black; border-collapse: collapse; padding: 5px;}</style>';
		echo '<br/><font style=\'size:80%;margin-left:15px;\'>Red font: Incomplete Weight Percent. Please complete the 100% per competency.</font><br/><br/>';
		$num=0;
	$sql1='SELECT ps.PerfComID, Competency, COUNT(ps.PerfComID) AS NoofStatements, SUM(ps.WeightinPercent) as WeightSum FROM statelist ps JOIN `hr_1competency` c ON c.PerfComID=ps.PerfComID GROUP BY ps.PerfComID; '; $stmt1=$link->query($sql1);
	$evallist='';
        foreach	($stmt1->fetchAll() as $comp){
            $statementspercomp=$comp['NoofStatements']+1;
            $evallist.='<tr><td rowspan="'.$statementspercomp.'" >'.$comp['Competency'].'</td>';
            $sql2='SELECT * FROM statelist WHERE PerfComID='.$comp['PerfComID']; $stmt2=$link->query($sql2);
            $first=0;
            while ($row = $stmt2->fetch())
		{
                $evallist.=(($first<$statementspercomp)?'<tr>':'').'<td valign=\'top\'>'.($num + 1).'. ' . $row['Statement'] . '</td>'
                        . '<td valign=\'top\'>' . $row['Evaluator'] . '</td>'
                        . '<td style="size: 15px;" valign=\'top\'>' . $row['WeightinPercent'] . '</td>'
                        .(($first==0)?'<td  rowspan="'.$statementspercomp.'" align=\'center\' valign=\'top\'>'
                    . '<font style=\'font-size:160%;font-weight:bold;'.(($comp['WeightSum']<100)?'color:red;':'').'\'>'.$comp['WeightSum'].'%</font></td></tr>':'</tr>');
                
                $num++; $first++;
            }
            $evallist.='';
            
        }
		
		echo '<table style=\'font-size:9pt;margin-left:0%; border: 1px solid black; padding: 3px;\'><thead ><th>Competency</th><th>Statement</th>'
        . '<th>Evaluator</th><th align="center">Weight</th><th></th></thead><tbody>'.$evallist.'</tbody></table>';
	break; //End of Case Statement
	
	
	//Start Of Case AddSA
	case 'AddSA':
		if (allowedToOpen(6840,'1rtc')){
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            // $sql='';
			// foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
			if ($ToEvaluate=='')
			{
				$ToEvaluate = -1;
			}
			if ($Evaluator=='')
			{
				$Evaluator = -1;
			}
			
			$sql='INSERT INTO `hr_1positionstatement` SET EncodedByNo='.$_SESSION['(ak0)'].', Weight='.$_POST['Weight'].', StatementID='.$StatementID.', ToEvaluate='.$ToEvaluate.', Evaluator='.$Evaluator.', TimeStamp=Now()'; 
			$link->query($sql);
		}
	header('Location:'.$_SERVER['HTTP_REFERER']);
	break; //End of Case AddSA
	
	
	//Start Of Case AddPS
	case 'EditSA':
		if (allowedToOpen(6840,'1rtc')){
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            // $sql='';
                        if ($ToEvaluate=='') { $ToEvaluate = -1; } else { $ToEvaluate=$ToEvaluate;}
			if ($Evaluator=='') { $Evaluator = -1;} else { $Evaluator = $Evaluator;}
			
			// foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
			$sql='UPDATE `hr_1positionstatement` SET EncodedByNo='.$_SESSION['(ak0)'].', Weight='.$_POST['Weight'].', StatementID='.$StatementID.', ToEvaluate='.$ToEvaluate.', Evaluator='.$Evaluator.', TimeStamp=Now() WHERE PSID='.$_GET['PSID'].''; 
			// echo $sql; break;
			$link->query($sql);
		}
	// header('Location:'.$_SERVER['HTTP_REFERER']);
	header("Location:perfevalsettings.php?w=StatementAssign");
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
		
		$sql='UPDATE `hr_1statement` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' PerfComID='.$PerfComID.', TimeStamp=Now() WHERE PerfStateID='.intval($_GET['PerfStateID']);
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
		$sql='DELETE FROM `hr_1statement` WHERE PerfStateID='.intval($_GET['PerfStateID']);
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
		$txnid=intval($_GET['PerfStateID']);
                $sql='SELECT ps.*,pc.Competency AS PerfComID, ps.PerfStateID AS TxnID FROM hr_1statement AS ps JOIN hr_1competency AS pc ON pc.PerfComID = ps.PerfComID  '
                        . ' WHERE PerfStateID='.$txnid;
                $sql.=allowedToOpen(6843,'1rtc')?' AND ps.EncodedByNo='.$_SESSION['(ak0)']:'';
                echo comboBox($link,'SELECT PerfComID, Competency FROM hr_1competency ORDER BY PerfComID','PerfComID','Competency','competencylist');
                $columnnameslist=array('PerfComID','Statement');
                $columnstoadd=array('PerfComID','Statement');

		$columnstoedit=$columnstoadd;
		$columnnames=$columnnameslist;
		
		$columnswithlists=array('PerfComID');
		$listsname=array('PerfComID'=>'competencylist');
		
		$editprocess='perfevalsettings.php?w=EditPS&PerfStateID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break; //End of Case EditSpecificsPS
	
	//Start Of Case EditSpecificsPS
    case 'EditSpecificsSA':
		$title='Edit Specifics';
		$txnid=intval($_GET['PSID']);
                $sql='SELECT ps.*, s.Statement,pc.Competency AS PerfComID, IFNULL(p.Position,-1) AS ToEvaluate, IFNULL(p1.Position,-1) AS Evaluator, s.PerfStateID AS TxnID FROM hr_1statement AS s JOIN hr_1competency AS pc ON pc.PerfComID = s.PerfComID JOIN hr_1positionstatement ps ON ps.StatementID=s.PerfStateID LEFT JOIN attend_0positions p ON p.PositionID=ps.ToEvaluate LEFT JOIN attend_0positions p1 ON ps.Evaluator=p1.PositionID';
                
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
		
		$editprocess='perfevalsettings.php?w=EditSA&PSID='.$txnid;
		
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
        $colnames=array('StatementID','ToEvaluate','Evaluator','Weight');
        $requiredcol=array('StatementID','ToEvaluate','Evaluator');
        $required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
        $specific_instruct='ToEvaluate and Evaluator must be integers corresponding the position.<br/><br/>'
                . '<i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='hr_1positionstatement'; $firstcolumnname='StatementID';
        $DOWNLOAD_DIR="../../uploads/"; ; $requireencodedby=true; $requiredts=true;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="perfevalsettings.php?w=StatementAssign" target="_blank">Lookup Newly Imported Data</a>';}
break;
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
