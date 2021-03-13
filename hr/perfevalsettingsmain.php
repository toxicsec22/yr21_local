<?php
include_once('perfevalsettingslinks.php');
$which=(!isset($_GET['w'])?'PercentPerEvaluator':$_GET['w']);

switch ($which) {
        case 'PercentPerEvaluator':
            $title='Weights per Evaluator'; $groupby='ToEvaluate'; $orderby=' ORDER BY Evaluator ';
            echo '<title>'.$title.'</title><h3>'.$title.'</h3>';
            echo '<style> table,td, th, tr {  border: 1px solid black; border-collapse: collapse; padding: 5px; font-size:9pt;margin-left:0%; border: 1px solid black; padding: 3px;}</style>';
            
			echo '<datalist id="evalmonth"><option value="3">3rd Month</option><option value="5">5th Month</option><option value="12">12th Month</option><option value="1">Annual Evaluation</option></datalist>';
			
			echo '<form method="POST" action="perfevalsettingsmain.php?w=PercentPerEvaluator"><br/> <input type="submit" value="3rd Month Distribution" name="btnEval3rd"> <input type="submit" value="5th Month Distribution" name="btnEval5th"> <input type="submit" value="12th Month Distribution" name="btnEval12th"> <input type="submit" value="Annual Evaluation" name="btnAnnual"></form>';
          
			if (isset($_POST['btnEval3rd']) OR isset($_POST['btnEval5th']) OR isset($_POST['btnEval12th']) OR isset($_POST['btnAnnual'])){
				if (isset($_POST['btnEval3rd'])){
					$EvalMonth = 3;
				} elseif (isset($_POST['btnEval5th'])) {
					$EvalMonth = 5;
				} elseif (isset($_POST['btnEval12th'])) {
					$EvalMonth = 12;
				} else {
					$EvalMonth = 1;
				}
			} elseif (isset($_SESSION['evalmonthses'])){
				$EvalMonth = $_SESSION['evalmonthses'];
			} else {
				$EvalMonth = 3;
			}
			
				if ($EvalMonth==3){
					$color = 'blue';
				} else if ($EvalMonth==5) {
					$color = 'green';
				} else if ($EvalMonth==1) {
					$color = 'maroon';
				} else {
					$color='yellow';
				}
			
			$_SESSION['evalmonthses']=$EvalMonth;
			$EvalMonth=$_SESSION['evalmonthses'];
			
			// echo $_SESSION['evalmonthses'];
			echo '<br/><h3>Percentage Distribution for <font color="'.$color.'">'.($EvalMonth==1?'Annual Evaluation':$EvalMonth.' months').'</font></h3>';
                $title=''; //$formdesc='Evaluations for 3, 5, and 12 months will be done only by the immediate supervisor.<br>Annual evaluations for performance bonus may have multiple evaluators.';
                echo comboBox($link,'SELECT PositionID, Position FROM attend_0positions ORDER BY Position','PositionID','Position','positionlist');
		$method='post';
				$columnnames=array(
				array('field'=>'ToEvaluate','type'=>'text','size'=>25,'required'=>true,'list'=>'positionlist'),
                                array('field'=>'Evaluator','type'=>'text','size'=>25,'required'=>true,'list'=>'positionlist'),
                                array('field'=>'EvalMonth','type'=>'text','size'=>25,'required'=>true,'list'=>'evalmonth')
                                    );
							
		$action='perfevalsettingsmain.php?w=AddEvaluator'; $fieldsinrow=4; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');
               echo '<form action="perfevalsettingsmain.php?w=PercentPerEvaluator" method="POST">';
			   if(!isset($_POST['btnShowEPID'])){
				echo '<input type="submit" value="Show EPID?" name="btnShowEPID">';
				$showepid=0;
			   } else {
				  echo '<input type="submit" value="Hide EPID?" name="btnHideEPID">';
				  $showepid=1;
			   }
			   echo '</form>'; 
            // End of Add Evaluator
            
            $sql0='CREATE TEMPORARY TABLE weightlist AS SELECT EPID, p.deptid, d.Department, ToEvaluate AS ToEvaluatePosID, p.Position AS ToEvaluate, p1.Position AS Evaluator, IFNULL((select SUM(Weight) FROM hr_1positionstatement WHERE EPID=ep.EPID),0) AS Percentage 
                FROM hr_1evaluatorpercentage ep JOIN attend_0positions p ON p.PositionID=ep.ToEvaluate JOIN `1departments` d ON d.deptid=p.deptid
                JOIN attend_0positions p1 ON p1.PositionID=ep.Evaluator WHERE EvalMonth='.$EvalMonth.' ORDER BY p.deptid ASC, p.JobGrade DESC, p1.JobGrade DESC';
        $stmt=$link->query($sql0); //echo $sql0;
        $sql0='SELECT DISTINCTROW deptid AS DeptID, Department FROM weightlist;';
	$stmt0=$link->query($sql0); $row0=$stmt0->fetchAll();
	
        echo '<div style="float:left;">';    
        foreach($row0 as $evalpos){
            echo '<br/><br/><h4>'.$evalpos['Department'].'</h4>';
            
            $sql1='SELECT COUNT(EPID) AS NoofEvaluators, ToEvaluate, ToEvaluatePosID,SUM(Percentage) AS TotalPercent FROM weightlist WHERE DeptID='.$evalpos['DeptID'].' GROUP BY ToEvaluatePosID';
            $stmt1=$link->query($sql1); $row1=$stmt1->fetchAll();
            $evallist='<br/><table><th>To Evaluate</th><th>Evaluator</th>'.($showepid==1?'<th>EPID</th>':'').'<th>Percentage</th>';
            
            foreach($row1 as $evalbypos){
            $sql2='SELECT @curRow := @curRow + 1 AS `No.`, EPID, Evaluator, Percentage FROM weightlist JOIN (SELECT @curRow := 0) r WHERE ToEvaluatePosID='.$evalbypos['ToEvaluatePosID'];
            $stmt2=$link->query($sql2);
            $rowspan=$evalbypos['NoofEvaluators']+1;
            $evallist.='<tr><td rowspan="'.$rowspan.'" >'.$evalbypos['ToEvaluate'].($rowspan<=2?'':'<br/><font color='.($evalbypos['TotalPercent']<>100?'red':'black').'>'.$evalbypos['TotalPercent'].'%</font>').'</td>';
            foreach ($stmt2->fetchAll() as $evalweights){                
                $evallist.='<tr><td>'.($rowspan<=2?'':$evalweights['No.'].'. ').'&nbsp; '.$evalweights['Evaluator'].'</td>'.($showepid==1?'<td><font color="blue">'.$evalweights['EPID'].'</font></td>':'').'<td '.($evalweights['Percentage']<>100?'style="color:red;font-weight:bold;"':'').'>'.$evalweights['Percentage'].'</td>'
                        .(allowedToOpen(6841,'1rtc')?'<td><form action=perfevalsettingsmain.php?w=DeleteEvaluator method=post OnClick="return confirm(\'Really delete this?\');">'
                        . '<input type=hidden name=action_token value='.$_SESSION['action_token'].'>'
                        . '<input type=hidden name=EPID value='.$evalweights['EPID'].'><input type=submit value=Delete></form></td>':'')
                        .(allowedToOpen(6844,'1rtc')?'<td><form action=perfevalsettingsmain.php?w=SetPercentPerEvaluator method=post><input type=text name=Percent size=5>'
                        . '<input type=hidden name=action_token value='.$_SESSION['action_token'].'>'
                        . '<input type=hidden name=EPID value='.$evalweights['EPID'].'><input type=submit value=Set></form></td>':'')
                        
                        .'</tr>';
            }
            
            if (allowedToOpen(6844,'1rtc')) { $editprocess=''; $editprocesslabel='Enter';}
            }  
            echo $evallist.'</table>';
        }
        
        $sql='SELECT p.Position AS ToEvaluate, p1.Position AS Evaluator, EvalMonth, SUM(Weight) AS TotalPercent FROM hr_1positionstatement ps JOIN hr_1evaluatorpercentage ep ON ep.EPID=ps.EPID JOIN attend_0positions p ON p.PositionID=ep.ToEvaluate JOIN attend_0positions p1 ON p1.PositionID=ep.Evaluator JOIN 1departments d ON d.deptid=p.deptid WHERE EvalMonth IN (1,2,5,12) GROUP BY ep.EPID,EvalMonth HAVING SUM(Weight)<>100;';
        $columnnames=array('ToEvaluate','Evaluator','EvalMonth','TotalPercent'); $subtitle='Total Percentages NOT 100% -- pls inform JYE asap';
        $width='25%'; $txnid='ToEvaluatePosID'; unset($editprocess,$editprocesslabel);
        echo '</div><div style="margin-left: 50%;">';
        include('../backendphp/layout/displayastableonlynoheaders.php');
        echo '</div>';
            
            break;
        
    /* 
    case 'SetPercentPerEvaluator':   
        if (allowedToOpen(6844,'1rtc')) {
        $sql='UPDATE hr_1evaluatorpercentage SET Percentage = '.$_POST['Percent'].', EncodedByNo='.$_SESSION['(ak0)'].',`TimeStamp`=Now() WHERE EPID='.$_POST['EPID'];
        $stmt=$link->prepare($sql); $stmt->execute();
		}
        
		header("Location:perfevalsettingsmain.php?w=PercentPerEvaluator");
            break;*/
            
    case 'AddEvaluator':
            $ToEvaluate=comboBoxValue($link,'attend_0positions','Position',addslashes($_POST['ToEvaluate']),'PositionID');
            $Evaluator=comboBoxValue($link,'attend_0positions','Position',addslashes($_POST['Evaluator']),'PositionID');
            $sql01='SELECT COUNT(EPID) AS cnt FROM hr_1evaluatorpercentage WHERE ToEvaluate='.$ToEvaluate.' AND Evaluator='.$Evaluator.' AND EvalMonth='.$_POST['EvalMonth'].' ';
			
		$stmt01=$link->query($sql01); $row01=$stmt01->fetch();
	
		if ($row01['cnt']==0)
		{
		$sql2 = "INSERT INTO hr_1evaluatorpercentage (ToEvaluate,Evaluator,Percentage,EvalMonth,EncodedByNo) VALUES (".$ToEvaluate.",".$Evaluator.",100,".$_POST['EvalMonth'].",".$_SESSION['(ak0)'].")"; 
		$stmt2=$link->prepare($sql2); $stmt2->execute();
		$_SESSION['evalmonthses']=$_POST['EvalMonth'];
		}
            header("Location:perfevalsettingsmain.php?w=PercentPerEvaluator");    
            break;
            
    case 'DeleteEvaluator':
	if (allowedToOpen(6841,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			
			$sql01='SELECT MAX(EvalMonth) AS maxno FROM hr_1evaluatorpercentage LIMIT 1';
			$stmt01=$link->query($sql01); $row01=$stmt01->fetch();
			
			if($row01['maxno']<100){
				$repid=100;
			} else {
				$repid=$row01['maxno']+1;
			}
			$sql='UPDATE `hr_1evaluatorpercentage` SET EvalMonth='.$repid.' WHERE EPID='.intval($_POST['EPID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;        
            
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->