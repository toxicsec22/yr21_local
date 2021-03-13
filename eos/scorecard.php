<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(1611,'1rtc')){ echo 'No permission'; exit; }
$showbranches=FALSE;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'select * from eos_2scmain ORDER BY Measurables','SCID','Measurables','Measurableslist'); 
echo comboBox($link,'select *,CONCAT(Nickname,\' \',SurName) as Fullname from 1employees ORDER BY SurName','IDNo','Fullname','employees'); 
$which=!isset($_GET['w'])?'list':$_GET['w'];
switch($which){
case'list':
	?>
<style>
	#listtable {
	  border-collapse: collapse;
	  font-size:10pt;
	  width: auto;
	  background-color:#cccccc;
	}

	#listtable td, #listtable th {
	  border: 1px solid black;
	  padding: 3px;
	}
	#listtable tr:nth-child(even){background-color:white;}
	#listtable tr:hover {background-color:#ffff66;}
</style>
<?php
	$title='Scorecard';
	echo'<a href="scorecard.php?w=Encode">Add/Edit Measurables</a></br></br><title>'.$title.'</title><h3>'.$title.'</h3></br>';
	$sql1='Create temporary table Weeks as select Measurables,Goal,sm.Who as Who,sm.SCID as SCID, Amount,Date from eos_2scmain sm left join eos_2scsub ss on ss.SCID=sm.SCID where Date between DATE_ADD(curdate(), INTERVAL \'-13\' Week) and curdate() group by week(Date),sm.SCID';
	$stmt1=$link->prepare($sql1); $stmt1->execute();
	
		$sql2='select * from Weeks Group By week(Date) Order By Date desc';
		$stmt2=$link->query($sql2); $result2=$stmt2->fetchAll();
//thead		
	$table='<table id="listtable"><tr>';
		$columndata=array('Who','Measurables','Goal');
		foreach($columndata as $data){
			$table.='<th>'.$data.'</th>';			
		}
	$sql='Select Measurables,Goal,';
	foreach($result2 as $res2){
	$sql.='max(CASE WHEN Date=\''.$res2['Date'].'\' then Amount end) as `'.$res2['Date'].'`, ';
	$table.='<th>'.$res2['Date'].'</th>';
	$columndata[]=''.$res2['Date'].'';
	}
	$table.='</tr>';
//end thead	
//tbody
		$sql.='CONCAT(Nickname,\' \',SurName) as Who from Weeks w join 1employees e on e.IDNo=w.Who group by SCID';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		foreach($result as $res){
		  $table.='<tr>';
			foreach($columndata as $data){
				if(gettype($res[$data])!='string' and $res['Goal']>$res[$data]){
						$style='style="background-color:red; color:#fff; font-weight:bold;"';
				}else{
						$style='';
				}
			$table.='<td '.$style.'>'.$res[$data].'</td>';
			}
		  $table.='</tr>';
		}
//tbody		
		echo $table;
	
break;
	
case'Encode':
?>
<style>
#table {
  border-collapse: collapse;
  font-size:10pt;
  padding: 5px;
  background-color:#FFFFCC;
}

#table td, #table th, #table tr {
  border: 1px solid black;
  padding: 5px;
}

#table tr:nth-child(even){background-color:#FFFFFF;}
</style>
<?php
	echo'<title>Add/Edit Measurables</title>';
	echo'<h3>Add/Edit Measurables</h3></br>';
//encode measurable	
		echo'<div style="border:1px solid black; width:630px; padding:5px"><h4>ENCODE</h4>
		<form method="post" action="scorecard.php?w=EncodeProcess">
			Measurable: <input type="text" name="Measurables">
			Goal: <input type="text" name="Goal" size="5">
			Who: <input type="text" name="Fullname" list="employees">
			<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
			 <input type="submit" name="submit">
			</form></div>';
			
			$sql='select *,CONCAT(Nickname,\' \',SurName) as Who from eos_2scmain  sm left join 1employees e on e.IDNo=sm.Who';
			$stmt=$link->query($sql); $result=$stmt->fetchAll();
		echo'</br><table id="table">
			<tr><th>Who</th><th>Measurable</th><th>Goal</th><th>Edit | Delete</th></tr>';	
		foreach($result as $res){
			echo'<tr><td>'.$res['Who'].'</td><td>'.$res['Measurables'].'</td><td>'.$res['Goal'].'</td><td><a style="color:blue" href="scorecard.php?w=Edit&SCID='.$res['SCID'].'">Edit<a/> | <a style="color:blue" href="scorecard.php?w=Delete&SCID='.$res['SCID'].'">Delete<a/></td></tr>';
			
		}
		echo'</table></div>';
		
//encode date,amount
		echo'</br><div style="border:1px solid black; width:630px; padding:5px">
		<form method="post" action="scorecard.php?w=EncodeProcessSub">
		<h4>ENCODE into</h4>
		Measurable: <input type="text" name="Measurables" list="Measurableslist">
			Date: <input type="date" name="Date" value="'.date('Y-m-d').'">
			Amount: <input type="text" name="Amount" size="5">
			<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
			 <input type="submit" name="submit">
			</form></div>';
			
			$sql='select * from eos_2scmain';
			$stmt=$link->query($sql); $result=$stmt->fetchAll();
		foreach($result as $res){
			echo'</br><table id="table"><tr><th>Measurable: '.$res['Measurables'].'</th></tr></table>';
			$sqls='select * from eos_2scsub where SCID=\''.$res['SCID'].'\'';
			$stmts=$link->query($sqls); $results=$stmts->fetchAll();
			echo'<table id="table">';
			foreach($results as $ress){
				echo'<tr><td><b>Date:</b> '.$ress['Date'].'</td><td><b>Amount:</b> '.$ress['Amount'].'</td><td><a style="color:blue" href="scorecard.php?w=EditSub&SCSubId='.$ress['SCSubId'].'">Edit<a/> | <a style="color:blue" href="scorecard.php?w=DeleteSub&SCSubId='.$ress['SCSubId'].'">Delete<a/></td></tr>';
				
			}
			echo'</table>';
		}
		echo'</div>';
		
		
		
break;

	case'EncodeProcessSub':
	$scid=comboBoxValue($link, 'eos_2scmain', 'Measurables', $_REQUEST['Measurables'], 'SCID');	
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='Insert into eos_2scsub set Date=\''.$_POST['Date'].'\',Amount=\''.$_POST['Amount'].'\',SCID=\''.$scid.'\' ';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:scorecard.php?w=Encode");
	break;
	
	case'EditSub':
	$sql='select * from eos_2scsub s left join eos_2scmain m on m.SCID=s.SCID  where SCSubId=\''.$_GET['SCSubId'].'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<h3>Edit?</h3></br><form method="post" action="scorecard.php?w=EditProcessSub&SCSubId='.$_GET['SCSubId'].'">
		 Measurable: <input type="text" name="Measurables" value="'.$result['Measurables'].'" list="Measurableslist">
		 Date: <input type="text" name="Date" value="'.$result['Date'].'">
		 Amount: <input type="text" name="Amount" value="'.$result['Amount'].'">
		 <input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		 <input type="submit" name="submit">
	';
	break;
	
	case'EditProcessSub':
	$scid=comboBoxValue($link, 'eos_2scmain', 'Measurables', $_REQUEST['Measurables'], 'SCID');	
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$scsubid = intval($_GET['SCSubId']);
		$sql='update eos_2scsub set Date=\''.$_POST['Date'].'\',Amount=\''.$_POST['Amount'].'\',SCID=\''.$scid.'\' where SCSubId=\''.$scsubid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:scorecard.php?w=Encode");
	break;
	
	case'DeleteSub':
	$scsubid = intval($_GET['SCSubId']);
	$sql='delete from eos_2scsub where SCSubId=\''.$scsubid.'\'';
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:scorecard.php?w=Encode");
	break;

	case'EncodeProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$who=comboBoxValue($link, '1employees', 'CONCAT(Nickname,\' \',SurName)', $_REQUEST['Fullname'], 'IDNo');	
		$sql='Insert into eos_2scmain set Measurables=\''.$_POST['Measurables'].'\',Goal=\''.$_POST['Goal'].'\',Who=\''.$who.'\' ';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:scorecard.php?w=Encode");
	break;

	case'Edit':
	$sql='select *,CONCAT(Nickname,\' \',SurName) as Who from eos_2scmain sm left join 1employees e on e.IDNo=sm.Who where SCID=\''.$_GET['SCID'].'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<h3>Edit?</h3></br><form method="post" action="scorecard.php?w=EditProcess&SCID='.$_GET['SCID'].'">
		 Measurable: <input type="text" name="Measurables" value="'.$result['Measurables'].'">
		 Goal: <input type="text" name="Goal" value="'.$result['Goal'].'">
		 Who: <input type="text" name="Fullname" value="'.$result['Who'].'" list="employees">
		 <input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		 <input type="submit" name="submit">
	';
	break;
	
	case'EditProcess':
		$who=comboBoxValue($link, '1employees', 'CONCAT(Nickname,\' \',SurName)', $_REQUEST['Fullname'], 'IDNo');	
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$scid = intval($_GET['SCID']);
		$sql='update eos_2scmain set Measurables=\''.$_POST['Measurables'].'\',Goal=\''.$_POST['Goal'].'\',Who=\''.$who.'\' where SCID=\''.$scid.'\'';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:scorecard.php?w=Encode");
	break;
	
	case'Delete':
	$scid = intval($_GET['SCID']);
	$sql='delete from eos_2scmain where SCID=\''.$scid.'\'';
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:scorecard.php?w=Encode");
	break;
}
?>