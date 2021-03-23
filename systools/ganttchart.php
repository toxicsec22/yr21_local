<?php
$path=$_SERVER['DOCUMENT_ROOT'];include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(83009,'1rtc')){ echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');

$which=!isset($_GET['w'])?'Project':$_GET['w'];
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT ProjectID, Project FROM systools_2ganttchart','ProjectID','Project','projects');
echo comboBox($link,'SELECT deptid, department FROM 1departments','deptid','department','departments');
if (in_array($which,array('addsub','editsubprocess'))){
		$deptid=comboBoxValue($link, '1departments', 'department', $_POST['Department'], 'deptid');
}
switch ($which){
	
	case'Project':
		?>
<style>
table {
  border-collapse: collapse;
  font-size:10pt;
  padding: 5px;
}

table td, table th, table tr {
  border: 1px solid black;
  padding: 5px;
}


</style>
<title>Projects</title></br>
<table><tr><td style="width: 280px;"><h3>Encode Project</h3></br>
    <form method="post" action="ganttchart.php?w=add">
			Project: <input type="text" name="Project" required> &nbsp;
                       <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
			<input type="submit" name="submit">
    </form></td>
</table></br>

<table><tr><td style="width: 1200px;"><h3>Encode Tasks</h3></br>
            <form method="post" action="ganttchart.php?w=addsub">
			Task: <input type="text" name="Task" size="30px" required> &nbsp;
			From: <input type="date" name="From" value="<?php echo date('Y-m-d') ?>" required> &nbsp;
			To: <input type="date" name="To" value="<?php echo date('Y-m-d',strtotime(date('Y-m-d').'+1 day')) ?>" required> &nbsp;
			Project: <input type="text" name="Project" list="projects" required> &nbsp;
			Department: <input type="text" name="Department" list="departments" required> &nbsp;
                     <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
			<input type="submit" name="submit">
		</form></td>
	</table>
<?php
	$sql1='select * from systools_2ganttchart';
	$stmt1=$link->query($sql1); $result1=$stmt1->fetchAll();
	echo'</br></br><table id="table">';
	foreach($result1 as $res1){
		echo'<tr><td><b>Project: '.$res1['Project'].'</b></td>
		<td><a href="ganttchart.php?w=edit&ProjectID='.$res1['ProjectID'].'">Edit</a> &nbsp;
		<a href="ganttchart.php?w=GanttChart&ProjectID='.$res1['ProjectID'].'">Lookup</a> &nbsp;
        <form method="post" style="display: inline" action="ganttchart.php?w=delete&ProjectID='.$res1['ProjectID'].'">
        <input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /><input type="submit" name="delete" value="Delete" OnClick="return confirm(\'Are you sure you want to Delete?\');"></form>
		</td></tr>';
		$sql2='select s.*,department from systools_2ganttchartsub s join 1departments d on d.deptid=s.deptid WHERE ProjectID=\''.$res1['ProjectID'].'\'';
		$stmt2=$link->query($sql2); $result2=$stmt2->fetchAll();
		foreach($result2 as $res2){
		echo'<tr><td>'.$res2['Task'].' <b>From:</b> '.$res2['FromDate'].' <b>To:</b> '.$res2['ToDate'].' <b>PercentDone:</b> '.$res2['PercentDone'].'% <b>Department:</b> '.$res2['department'].'</td>
		<td>
			<a href="ganttchart.php?w=editsub&ProjectSubId='.$res2['ProjectSubId'].'">Edit</a> &nbsp; 
            <form method="post" style="display: inline" action="ganttchart.php?w=deletesub&ProjectSubId='.$res2['ProjectSubId'].'">
			<input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'">
			<input type="submit" name="Delete" value="Delete" OnClick="return confirm(\'Are you sure you want to Delete?\');"></form>
	  </td>
	  </tr>';
		}
		
	}
	echo'</table>';
	
	break;
	
	case'editsub':
	$sql='select s.*,department from systools_2ganttchartsub s join 1departments d on d.deptid=s.deptid where ProjectSubId=\''.$_GET['ProjectSubId'].'\'';
		$stmt=$link->query($sql); $result=$stmt->fetch();
		echo'<title>Edit?</title><h3>Edit?</h3></br><form method="post" action="ganttchart.php?w=editsubprocess&ProjectSubId='.$_GET['ProjectSubId'].'">
		 Task: <input type="text" name="Task" value="'.$result['Task'].'">
		 	From: <input type="text" name="From" value="'.$result['FromDate'].'" required> 
			To: <input type="text" name="To" value="'.$result['ToDate'].'" required> 
			PercentDone: <input type="text" name="PercentDone" value="'.$result['PercentDone'].'" size="1" required> 
			Department: <input type="text" name="Department" value="'.$result['department'].'" required list="departments"> 
		 <input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		 <input type="submit" name="submit">';
	break;
	
	case'editsubprocess';
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$projectsubid = intval($_GET['ProjectSubId']);
		$sql='update systools_2ganttchartsub set PercentDone=\''.$_POST['PercentDone'].'\',Task=\''.$_POST['Task'].'\',FromDate=\''.$_POST['From'].'\',ToDate=\''.$_POST['To'].'\',deptid=\''.$deptid.'\' where ProjectSubId=\''.$projectsubid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:ganttchart.php?w=Project");
	break;
	
	case'deletesub':
		$projectsubid=intval($_GET['ProjectSubId']);
		$sql='delete from systools_2ganttchartsub where ProjectSubId=\''.$projectsubid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:ganttchart.php?w=Project");
	break;
	
	case'addsub';
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$projectid=comboBoxValue($link,'systools_2ganttchart','Project',addslashes($_POST['Project']),'ProjectID');
		$sql='Insert into systools_2ganttchartsub set Task=\''.$_POST['Task'].'\',ProjectID=\''.$projectid.'\',FromDate=\''.$_POST['From'].'\',ToDate=\''.$_POST['To'].'\',deptid=\''.$deptid.'\'';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:ganttchart.php?w=Project");
	break;
	
	case'add';
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='Insert into systools_2ganttchart set Project=\''.$_POST['Project'].'\'';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:ganttchart.php?w=Project");
	break;
	
	case'edit':
		$sql='select * from systools_2ganttchart where ProjectID=\''.$_GET['ProjectID'].'\'';
		$stmt=$link->query($sql); $result=$stmt->fetch();
		echo'<title>Edit?</title><h3>Edit?</h3></br><form method="post" action="ganttchart.php?w=editprocess&ProjectID='.$_GET['ProjectID'].'">
		 ProjectID: <input type="text" name="ProjectID" value="'.$result['ProjectID'].'" readonly>
		 Project: <input type="text" name="Project" value="'.$result['Project'].'">
		 <input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		 <input type="submit" name="submit">';
	break;
	
	case'editprocess';
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$projectid = intval($_GET['ProjectID']);
		$sql='update systools_2ganttchart set Project=\''.$_POST['Project'].'\' where ProjectID=\''.$projectid.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:ganttchart.php?w=Project");
	break;
	
	case'delete':
	$projectid=intval($_GET['ProjectID']);
	$sql='delete from systools_2ganttchart where ProjectID=\''.$projectid.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:ganttchart.php?w=Project");
	break;
	
	case'GanttChart':		
	$sql='select * from systools_2ganttchart where ProjectID=\''.$_GET['ProjectID'].'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<title>'.$result['Project'].'</title></br><div style="background-color:white; padding:5px;"><h3>Project: '.$result['Project'].'</h3></div>
		';
 ?>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <script type="text/javascript">
    google.charts.load('current', {'packages':['gantt']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {

      var data = new google.visualization.DataTable();
      data.addColumn('string', 'ID');
      data.addColumn('string', 'Details');
      data.addColumn('string', 'Resource');
      data.addColumn('date', 'Start Date');
      data.addColumn('date', 'End Date');
      data.addColumn('number', 'Duration');
      data.addColumn('number', 'Percent Complete');
      data.addColumn('string', 'Dependencies');

      data.addRows([
	  <?php
	  $sqls='select PercentDone,Task,ProjectSubId,replace(date_add(FromDate,INTERVAL -1 month),\'-\',\',\') as FromDate,replace(date_add(ToDate,INTERVAL -1 month),\'-\',\',\') as ToDate,department from systools_2ganttchartsub s join 1departments d on d.deptid=s.deptid where ProjectID=\''.$_GET['ProjectID'].'\'';
	  $stmts=$link->query($sqls); $results=$stmts->fetchAll();
	  $task='';
	  foreach($results as $ress){
		  // echo'[\'1\', \'Find Sources\', \'\',
         
		 $task.='[\''.$ress['ProjectSubId'].'\', \''.$ress['Task'].'\', \''.$ress['department'].'\',
         new Date('.$ress['FromDate'].'), new Date('.$ress['ToDate'].'), null, '.$ress['PercentDone'].', null],';
	  }
	  $task=substr($task, 0, -1);
	  echo $task;
	  
	  ?> 
      ]);

      var options = {
        height: 400,
		gantt: {
            criticalPathEnabled: false
          }
      };

      var chart = new google.visualization.Gantt(document.getElementById('chart_div'));

      chart.draw(data, options);
    }
  </script>

  <div id="chart_div"></div>
<?php
break;

}

?>
