<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6840,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');
?>
<br><div id="section" style="display: block;">

    <div><a id='link' href="newperfevalsettings.php">Core Competencies</a> <a id='link' href="newperfevalsettings.php?w=FunctionalCompetencies">Functional Competencies</a>
    </div><br/>

<?php
$which=(!isset($_GET['w'])?'CoreCompetencies':$_GET['w']);

if (in_array($which,array('FunctionalCompetencies','EditSpecificsFC'))){
	$sql='SELECT FID AS TxnID,dept AS Department,FormDescription,CONCAT("<a href=\"newperfevalsettings.php?w=FCFormID&FID=",FID,"\"",">Lookup Default Positions</a>") AS DefaultPositions FROM hr_82fcmain fcm JOIN 1departments d ON fcm.DeptID=d.deptid ';
	echo comboBox($link,'SELECT deptid, dept FROM 1departments ORDER BY dept','deptid','dept','deptlist');
	$columnnameslist=array('Department','FormDescription','DefaultPositions');
	$columnstoadd=array('Department','FormDescription');
 }
 if (in_array($which,array('AddFC','EditFC'))){
	$DeptID=comboBoxValue($link,'1departments','dept',addslashes($_POST['Department']),'deptid');
	$columnstoadd=array('FormDescription');
 }

 if (in_array($which,array('LookupFCStatements','EditSpecificsFCStatement'))){
	$sql='SELECT *,FCID AS TxnID FROM hr_82fcsub ';
	$columnnameslist=array('Statement','DefaultWeight','OrderBy','Active');
	$columnstoadd=array('Statement','DefaultWeight','OrderBy');
 }
 if (in_array($which,array('AddFCStatement','EditFCStatement'))){
	$columnstoadd=array('Statement','DefaultWeight','OrderBy');
 }
 
 if (in_array($which,array('FormID','FCFormID'))){
	$sql0='CREATE TEMPORARY TABLE groupdept AS SELECT p.deptid, d.Department, p.JobLevelNo, JLID, p.Position, p.PositionID FROM attend_0positions p JOIN `1departments` d ON d.deptid=p.deptid JOIN `attend_1joblevel` jl ON jl.JobLevelNo=p.JobLevelNo ORDER BY JobClassNo DESC,jl.JobLevelNo DESC;';
  	
	$stmt=$link->query($sql0);
	$sql0='SELECT DISTINCTROW deptid AS DeptID, Department FROM groupdept;';
	$stmt0=$link->query($sql0); $row0=$stmt0->fetchAll();
 }
 

switch ($which)
{

	case 'CoreCompetencies':
	
		$title='Core Competencies'; 
		$txnid='TxnID';
		
		$columnnames=array('Competency','Interpretation','Weight');
		$sqlmain='SELECT *,CONCAT(`Weight`,"%") AS `Weight` FROM hr_81corecompetencies WHERE ';
		$orderby='ORDER BY OrderBy';

		$formidlink='newperfevalsettings.php?w=FormID&FormID=';
		$sql=$sqlmain.' FormID=1 '.$orderby;
		$formdesc='<br><br></i><b>HEAD OFFICE - MANAGERS/DEPT. HEAD/AREA OPS./SUPERVISOR <a href="'.$formidlink.'1" target="_blank">Lookup Positions</a></b><i>';
		include('../backendphp/layout/displayastablenosort.php');
		
		$title='';

		$sql=$sqlmain.' FormID=2 '.$orderby;
		$formdesc='<br><br></i><b>HEAD OFFICE - ALL RANK & FILE <a href="'.$formidlink.'2" target="_blank">Lookup Positions</a></b><i>';
		include('../backendphp/layout/displayastablenosort.php');

		
		$sql=$sqlmain.' FormID=3 '.$orderby;
		$formdesc='<br><br></i><b>SALES FORCE - BRANCH HEAD/ SALES TEAM LEADER/ BRANCH OIC/ ASSISTANT <a href="'.$formidlink.'3" target="_blank">Lookup Positions</a></b><i>';
		include('../backendphp/layout/displayastablenosort.php');

		
		$sql=$sqlmain.' FormID=4 '.$orderby;
		$formdesc='<br><br></i><b>DRIVERS/RIDERS/UTILITY/SKILLED WORKER/MESSENGER <a href="'.$formidlink.'4" target="_blank">Lookup Positions</a></b><i>';
		include('../backendphp/layout/displayastablenosort.php');

		
	break;
	

	case 'FormID':
		$title1 = 'Update Form';
		echo '<title>'.$title1.'</title>';
		
		$formid = intval($_GET['FormID']);
		$sqlvalue ="SELECT * FROM `hr_81perfevalforms` WHERE FormID=".$formid.";";
		
		$stmtvalue=$link->query($sqlvalue); $rowvalue=$stmtvalue->fetch();
		
		$FormID = $rowvalue['FormID'];
		$FormDescription = $rowvalue['FormDescription'];
		$Positions = $rowvalue['Positions'];
		$path = 'UpdatePositionProcess&FormID='.$FormID.'';
		$submitlabel = "Update position";
				
				
		
			echo '<h2>'.$title1.'</h2><br/>';
			
			
			echo '<div>';
			echo '<div style="float:left;">';
			echo '<form action="newperfevalsettings.php?w='.$path.'" method="post"><table>';
			
			echo '<tr><td>Form ID:</td><td><input name="FormID" type="text" size="20" placeholder="" value="'.$FormID.'" readonly/></td></tr>';
			echo '<tr><td>Form Description:</td><td><input name="FormDescription" type="text" size="60" placeholder="" value="'.$FormDescription.'" required/></td></tr>';
			
			echo '<tr><td valign="top">Positions:</td><td>';
			
			echo '<div style="float:left;">';    
			foreach($row0 as $pos){
            echo '<h4>'.$pos['Department'].'</h4>';
            $sql1='SELECT Position, PositionID FROM groupdept WHERE DeptID='.$pos['DeptID'].' GROUP BY PositionID ORDER BY JLID DESC';
			
            $stmt1=$link->query($sql1); $row1=$stmt1->fetchAll();
            $deptlist='<table>';
            
            foreach($row1 as $row2){
				$deptlist.='<tr><td><input type="checkbox" name="allowed[]" value="'.$row2['PositionID'].'"
				'.(in_array($row2['PositionID'],explode(",",$Positions)) !== false ? 'checked = "checked"': '').';/><td>'.$row2['Position'].' ('.$row2['PositionID'].')</td></td>';
            }  
				echo $deptlist.'</table>';
			}
        
			echo '</div>';
			echo '</td></tr>';
			
				echo '<tr><td style="padding:10px;"></td></tr><tr><td></td><td align="left"><input type="submit" value="'.$submitlabel.'"/></td></tr>';
		
			echo '</table></form>';
			echo '</div>';
			
		
				echo '<div style="margin-left:50%"><br><b>Positions</b><br>';
				$sql ="SELECT Positions FROM hr_81perfevalforms WHERE FormID=".$FormID.";";
				$stmt=$link->query($sql); $rowh=$stmt->fetch();
				
				$sql ="SELECT PositionID,Position FROM attend_0positions p JOIN attend_1joblevel jl ON jl.JobLevelNo=p.JobLevelNo WHERE PositionID IN (".$rowh['Positions'].") ORDER BY deptid,JLID DESC";
                                
				$stmt=$link->query($sql); $row=$stmt->fetchAll();
				foreach($row AS $res){
					echo '&nbsp; &nbsp; '.$res['Position'].'<br>';
				}
				
				echo '</div></div>';
		
	break;

	case 'UpdatePositionProcess':
			$new = '';
			if (isset($_POST['allowed'])){
				foreach($_POST['allowed'] as $selected){
					$selected = "".$selected.",";
					$new = $new . ''.$selected.'';
				}
				$trimlastcomma = substr($new,0,-1);
				
			}
			else {
				$trimlastcomma='';
			}

			$sql='UPDATE `hr_81perfevalforms` SET FormDescription="'.addslashes($_POST['FormDescription']).'", Positions='.(empty($trimlastcomma) ? 'DEFAULT':'"'.$trimlastcomma.'"').', EncodedByNo="'.$_SESSION['(ak0)'].'",TimeStamp=NOW() WHERE FormID='.intval($_GET['FormID']);
		
			$stmt = $link->prepare($sql);
			$stmt->execute();
			header("Location:newperfevalsettings.php?w=FormID&FormID=".$_POST['FormID']);

		break;



		case 'FunctionalCompetencies':
			
			$title='Functional Competencies'; 
			$txnid='FID';
        	 $formdesc='';
			
			 $method='post';
			 $columnnames=array(
				array('field'=>'Department','type'=>'text','size'=>10,'required'=>true,'list'=>'deptlist'),
			 array('field'=>'FormDescription','type'=>'text','size'=>40,'required'=>true)
			);

	 $action='newperfevalsettings.php?w=AddFC'; $fieldsinrow=4; $liststoshow=array();
	 include('../backendphp/layout/inputmainform.php');

	 $title='';

	 $sql.=' ORDER BY dept';

	 $columnnames=$columnnameslist;
			$width='70%';
			$editprocess='newperfevalsettings.php?w=LookupFCStatements&FID='; $editprocesslabel='Lookup Statements';
			$addlprocess='newperfevalsettings.php?w=EditSpecificsFC&FID='; $addlprocesslabel='Edit';
			$delprocess='newperfevalsettings.php?w=DeleteFC&FID=';
			include('../backendphp/layout/displayastable.php');

		break;
		

		case 'AddFC':
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='';
		
			foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
			
			$sql='INSERT INTO `hr_82fcmain` SET EncodedByNo='.$_SESSION['(ak0)'].',DeptID='.$DeptID.', '.$sql.' TimeStamp=Now()';
			$link->query($sql);
	header('Location:'.$_SERVER['HTTP_REFERER']);
	
	break;



	case 'EditSpecificsFC':
		
		$title='Edit Specifics'; $formdesc='';
		$txnid=intval($_GET['FID']);
                

		$columnstoedit=$columnstoadd;
		$columnnames=array_diff($columnnameslist,array('DefaultPositions'));
	
		$columnswithlists=array('Department');
		$listsname=array('Department'=>'deptlist');
		
		$editprocess='newperfevalsettings.php?w=EditFC&FID='.$txnid;
		$sql.=' WHERE FID='.intval($_GET['FID']);
		include('../backendphp/layout/editspecificsforlists.php');
	break;

	
case 'EditFC':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	$sql='';
	foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
	
	$sql='UPDATE `hr_82fcmain` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' DeptID='.$DeptID.', TimeStamp=Now() WHERE FID='.intval($_GET['FID']);
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	
	header("Location:newperfevalsettings.php?w=FunctionalCompetencies");
break; 


case 'DeleteFC':
	//access
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `hr_82fcmain` WHERE FID='.intval($_GET['FID']);
		
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
break;


case 'LookupFCStatements':

	$title='Functional Competency Statements'; 
			$txnid='FCID';
			$sqlm='SELECT dept,FormDescription,CONCAT("<a target=\"_blank\" href=\"newperfevalsettings.php?w=FCFormID&FID=",FID,"\"",">Lookup Default Positions</a>") AS DefaultPositions FROM hr_82fcmain fcm JOIN 1departments d ON fcm.DeptID=d.deptid WHERE FID='.intval($_GET['FID']).';';
	$stmtm=$link->query($sqlm); $rowm=$stmtm->fetch();

        	 $formdesc='</i><br><br>Department: <font style="color:blue"><b>'.$rowm['dept'].'</b></font><br>Form Description: <b>'.$rowm['FormDescription'].'</b><br>'.$rowm['DefaultPositions'].'<i>';
			 $method='post';
			 $columnnames=array(
			 array('field'=>'Statement','type'=>'text','size'=>50,'required'=>true),
			 array('field'=>'DefaultWeight','type'=>'text','size'=>10,'required'=>true),
			 array('field'=>'OrderBy','type'=>'text','size'=>5,'required'=>true)
			);

	 $action='newperfevalsettings.php?FID='.intval($_GET['FID']).'&w=AddFCStatement'; $fieldsinrow=4; $liststoshow=array();
	 include('../backendphp/layout/inputmainform.php');

	 $title='';

	 $sql.=' WHERE FID='.intval($_GET['FID']).' ORDER BY OrderBy,Active DESC';
$formdesc='';
	 $columnnames=$columnnameslist;
			$width='70%';
			$editprocess='newperfevalsettings.php?w=StatementActiveInactive&action_token='.$_SESSION['action_token'].'&FCID='; $editprocesslabel='Active/Inactive';
			$addlprocess='newperfevalsettings.php?w=EditSpecificsFCStatement&FID='.intval($_GET['FID']).'&FCID='; $addlprocesslabel='Edit';
			$delprocess='newperfevalsettings.php?w=DeleteFCStatement&FCID=';
			include('../backendphp/layout/displayastablenosort.php');
break;

case 'AddFCStatement':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';

	$sql='';
	foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
	$sql='INSERT INTO `hr_82fcsub` SET FID='.intval($_GET['FID']).',EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now()';
	$link->query($sql);
header('Location:'.$_SERVER['HTTP_REFERER']);

break;

case 'StatementActiveInactive':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
	
	$sql='UPDATE `hr_82fcsub` SET Active=IF(Active=1,0,1) WHERE FCID='.intval($_GET['FCID']);

	$link->query($sql);
header('Location:'.$_SERVER['HTTP_REFERER']);

break;

case 'EditSpecificsFCStatement':
		
	$title='Edit Specifics'; $formdesc='';
	$txnid=intval($_GET['FCID']);

	$columnstoedit=$columnstoadd;
	$columnnames=$columnnameslist;
	$editprocess='newperfevalsettings.php?w=EditFCStatement&FID='.intval($_GET['FID']).'&FCID='.$txnid;
	$sql.=' WHERE FCID='.intval($_GET['FCID']);
	include('../backendphp/layout/editspecificsforlists.php');
break;

case 'EditFCStatement':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	$sql='';
	foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
	
	$sql='UPDATE `hr_82fcsub` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE FCID='.intval($_GET['FCID']);
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	
	header("Location:newperfevalsettings.php?w=LookupFCStatements&FID=".intval($_GET['FID']));
break; 

case 'DeleteFCStatement':
	//access
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `hr_82fcsub` WHERE FCID='.intval($_GET['FCID']);
		
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
break;


case 'FCFormID':

	$title1 = 'Update Default Position for Functional Competencies';
	echo '<title>'.$title1.'</title>';
	
	$formid = intval($_GET['FID']);
	$sqlvalue ="SELECT fcm.*,dept FROM `hr_82fcmain` fcm JOIN 1departments d On fcm.DeptID=d.deptid WHERE FID=".$formid.";";
	
	$stmtvalue=$link->query($sqlvalue); $rowvalue=$stmtvalue->fetch();
	
	$FormID = $rowvalue['FID'];
	$FormDescription = $rowvalue['FormDescription'];
	$Positions = ($rowvalue['DefaultPositions']==''?'-1':$rowvalue['DefaultPositions']);
	$path = 'UpdatePositionProcessFID&FID='.$FormID.'';
	$submitlabel = "Update position";
			
		echo '<h2>'.$title1.'</h2><br/>';
		
		echo '<div>';
		echo '<div style="float:left;">';
		echo '<form action="newperfevalsettings.php?w='.$path.'" method="post"><table>';
		
		echo '<tr><td>FID:</td><td><input name="FormID" type="text" size="20" placeholder="" value="'.$FormID.'" readonly/></td></tr>';
		echo '<tr><td>Department:</td><td><input name="dept" type="text" size="20" placeholder="" value="'.$rowvalue['dept'].'" disabled/></td></tr>';
		echo '<tr><td>Form Description:</td><td><input name="FormDescription" type="text" size="60" placeholder="" value="'.$FormDescription.'" disabled/></td></tr>';
		
		echo '<tr><td valign="top">Positions:</td><td>';
		
		echo '<div style="float:left;">';    
		foreach($row0 as $pos){
		echo '<h4>'.$pos['Department'].'</h4>';
		$sql1='SELECT Position, PositionID FROM groupdept WHERE DeptID='.$pos['DeptID'].' GROUP BY PositionID ORDER BY JLID DESC';
		
		$stmt1=$link->query($sql1); $row1=$stmt1->fetchAll();
		$deptlist='<table>';
		
		foreach($row1 as $row2){
			$deptlist.='<tr><td><input type="checkbox" name="allowed[]" value="'.$row2['PositionID'].'"
			'.(in_array($row2['PositionID'],explode(",",$Positions)) !== false ? 'checked = "checked"': '').';/><td>'.$row2['Position'].' ('.$row2['PositionID'].')</td></td>';
		}  
			echo $deptlist.'</table>';
		}
	
		echo '</div>';
		echo '</td></tr>';
		
			echo '<tr><td style="padding:10px;"></td></tr><tr><td></td><td align="left"><input type="submit" value="'.$submitlabel.'"/></td></tr>';
	
		echo '</table></form>';
		echo '</div>';
		
	
			echo '<div style="margin-left:50%"><br><b>Positions</b><br>';
			$sql ="SELECT DefaultPositions FROM hr_82fcmain WHERE FID=".$FormID.";";
			$stmt=$link->query($sql); $rowh=$stmt->fetch();
			
			$sql ="SELECT PositionID,Position FROM attend_0positions p JOIN attend_1joblevel jl ON jl.JobLevelNo=p.JobLevelNo WHERE PositionID IN (".$rowh['DefaultPositions'].") ORDER BY deptid,JLID DESC";
							
			$stmt=$link->query($sql); $row=$stmt->fetchAll();
			foreach($row AS $res){
				echo '&nbsp; &nbsp; '.$res['Position'].'<br>';
			}
			
			echo '</div></div>';

break;

case 'UpdatePositionProcessFID':
	$new = '';
	if (isset($_POST['allowed'])){
		foreach($_POST['allowed'] as $selected){
			$selected = "".$selected.",";
			$new = $new . ''.$selected.'';
		}
		$trimlastcomma = substr($new,0,-1);
		
	}
	else {
		$trimlastcomma='';
	}

	$sql='UPDATE `hr_82fcmain` SET DefaultPositions='.(empty($trimlastcomma) ? 'DEFAULT':'"'.$trimlastcomma.'"').', EncodedByNo="'.$_SESSION['(ak0)'].'",TimeStamp=NOW() WHERE FID='.intval($_GET['FID']);

	$stmt = $link->prepare($sql);
	$stmt->execute();
	header("Location:newperfevalsettings.php?w=FCFormID&FID=".$_GET['FID']);

break;


}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
