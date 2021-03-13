<?php
$path=$_SERVER['DOCUMENT_ROOT'];include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(160,'1rtc')){ echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');

$which=!isset($_GET['w'])?'chart':$_GET['w'];

if(in_array($which, array('chart','Encode','Specific'))){
    $sqlf='SELECT f.*,f1.Function AS ReportsTo FROM eos_1functions f LEFT JOIN eos_1functions f1 ON f.ReportsToFxnID=f1.FxnID ';
    $sqlroles='SELECT * FROM eos_1roles ';
}

if(in_array($which, array('EncodeFxn','UploadFxn','EditFxn','EditFxnID','DelFxnID'))){
    $columnstoadd=array('FxnID','Function','deptid','MainOrSupport','ReportsToFxnID');
    $table='eos_1functions'; $primary='FxnID';
}

if(in_array($which, array('EncodeRole','UploadRole','EditRole','EditRoleID','DelRoleID'))){
    $columnstoadd=array('Role','FxnID');
    $table='eos_1roles'; $primary='RoleID';
}

if(in_array($which, array('EncodeFxn','UploadFxn','EditFxnID','DelFxnID','EncodeRole','UploadRole','EditRoleID','DelRoleID'))){
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
}

switch ($which){
	case'chart':
		
 ?><style>
 a{
  text-decoration:none;
 }</style>
    <title>EOS: Accountability Chart</title>
    <div style="float: right;"><a href='accountability.php?w=Encode' target=_blank>Add/Edit Functions & Roles</a></div>
    </br><center><p style="letter-spacing: 3px; font-size:25px;">THE ACCOUNTABILITY CHART</p></center></br><b>LMA means Lead + Manage = Accountability</b></br></br>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {packages:["orgchart"]});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('string', 'Manager');
        data.addColumn('string', 'ToolTip');

        // For each orgchart box, provide the name, manager, and tooltip to show.
  data.addRows([
  <?php
  //head
  $sql='SELECT f.* FROM eos_1functions f '.(!isset($_GET['FxnID'])?'WHERE FxnID=1':'WHERE FxnID=\''.$_GET['FxnID'].'\'').'';
  $stmt=$link->query($sql); $result=$stmt->fetch();

          echo'[{"v":"'.$result['FxnID'].'", "f":"'.$result['Function'].' </br>________________</br>LMA';
		  
			$sqlr=$sqlroles.' WHERE FxnID=\''.$result['FxnID'].'\'';
			$stmtr=$link->query($sqlr); $resultr=$stmtr->fetchAll();
			foreach($resultr as $resr){
				echo'</br>'.$resr['Role'].'';
			}
		  echo'"},"", ""]';
  //endhead

		  $sqlf.=' '.(!isset($_GET['FxnID'])?'WHERE f.FxnID<>1':'WHERE '.(($_GET['deptid']==1)?'f.deptid in (\'4\',\''.$_GET['deptid'].'\')':' f.deptid=\''.$_GET['deptid'].'\'').' and f.FxnID<>\''.$_GET['ReportsToFxnID'].'\' and f.ReportsToFxnID<>\''.$_GET['ReportsToFxnID'].'\' AND(f1.FxnID=\''.$_GET['FxnID'].'\' OR f1.ReportsToFxnID=\''.$_GET['FxnID'].'\' or f1.ReportsToFxnID in (SELECT f.ReportsToFxnID FROM eos_1functions f LEFT JOIN eos_1functions f1 ON f.ReportsToFxnID=f1.FxnID 
			WHERE  f1.ReportsToFxnID=\''.$_GET['FxnID'].'\')) ').'';
		  $stmts=$link->query($sqlf); $results=$stmts->fetchAll();
		  foreach($results as $ress){
			  	$sqlc='select * from eos_1functions where ReportsToFxnID='.$ress['FxnID'].'';
				$stmtc=$link->query($sqlc);
				
		  echo',[{"v":"'.$ress['FxnID'].'", "f":"'.(!isset($_GET['deptid'])?'<div '.(($ress['MainOrSupport']==1)?'style=background-color:#ffffe6;':'').'><a style=color:black;  href=accountability.php?w=chart&FxnID='.$ress['FxnID'].'&deptid='.$ress['deptid'].'&ReportsToFxnID='.$ress['ReportsToFxnID'].'>'.$ress['Function'].'</a>':''.$ress['Function'].'').'</br>________________'.(($stmtc->rowCount()!=0)?'</br>LMA':'').'';
		  
		  $sqlrs=$sqlroles.' WHERE FxnID=\''.$ress['FxnID'].'\'';
			$stmtrs=$link->query($sqlrs); $resultrs=$stmtrs->fetchAll();
			foreach($resultrs as $resrs){
				echo'</br>'.$resrs['Role'].'';
			}
		  echo'</div>"},"'.$ress['ReportsToFxnID'].'", ""]';
		  }
?>
        ]);

        // Create the chart.
		var chart = new google.visualization.OrgChart(document.querySelector('#chart_div'));
        // Draw the chart, setting the allowHtml option to true for the tooltips.
        chart.draw(data, {allowHtml: true});
      }
   </script>
    <center><div id="chart_div"></div></center>

<?php
break;

case'Encode':
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
<title>EOS: Encode Accountability</title>
<h3 style="display:inline;">EOS: Accountability Entries</h3>
<a style="display:inline; margin-left:100px;" href="accountability.php">ACCOUNTABILITY CHART</a></BR>
</br><table><tr><td style="width: 900px;"><h3>Encode Functions</h3></br>
    <form method="post" action="accountability.php?w=EncodeFxn">
			Function: <input type="text" name="Function" required> &nbsp;
			FxnID: <input type="text" name="FxnID" size="1" required> &nbsp;
                        DepartmentID: <input type="text" name="deptid" size="1" list="depts" required> &nbsp;
			ReportsToFxnID: <input type="text" name="ReportsToFxnID" size="1"  list="functions" required> &nbsp;
				MainOrSupport: <select name="MainOrSupport">
										<option value="0">0 - Main</option>
										<option value="1">1 - Support</option>
									   </select>&nbsp;
                        <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
			<input type="submit" name="submit">
    </form></td>
<td><h3> OR  Upload Functions</h3></br>
	<form method="post" action="accountability.php?w=UploadFxn" enctype="multipart/form-data">
            Columns: FxnID, Function, deptid, ReportsToFxnID, MainOrSupport
		</br></br><input type="file" name="userfile" accept="csv/text" required>
                <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
                <input type="submit" name="upload" value="Upload" OnClick="return confirm('Are you sure you want to upload?');"></form></td></tr></table>

</br>
</br><table><tr><td style="width: 700px;"><h3>Encode Roles</h3></br>
            <form method="post" action="accountability.php?w=EncodeRole">
			Role: <input type="text" name="Role" size="30px" required> &nbsp;
			Function: <input type="text" name="FxnID" list="functions" required> &nbsp;
                        <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
			<input type="submit" name="submit">
		</form></td>
                <td><h3> OR  Upload Roles</h3></br>
	<form method="post" action="accountability.php?w=UploadRole" enctype="multipart/form-data">	
            Column names: Role, FxnID
	</br></br><input type="file" name="userfile" accept="csv/text" required>
        <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
        <input type="submit" name="upload" value="Upload" OnClick="return confirm('Are you sure you want to upload?');"></form></td></tr></table>
<?php
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        echo comboBox($link,'SELECT * FROM `1departments` ORDER BY department','department','deptid','depts'); 
	//$sql1='select * from eos_1functions';
        echo comboBox($link,$sqlf,'Function','FxnID','functions'); 
	$stmt1=$link->query($sqlf); $result1=$stmt1->fetchAll();
	echo'</br></br><table id="table">';
        if (allowedToOpen(1601,'1rtc')){ $editok=true;}
	foreach($result1 as $res1){
		echo'<tr><td><b>Function: '.$res1['Function'].'</b></td><td><b>FxnID: '.$res1['FxnID'].'</b></td><td><b>ReportsTo: '.$res1['ReportsTo'].'</b></td><td>'
                  .($editok?'<a href="accountability.php?w=EditFxn&FxnID='.$res1['FxnID'].'" target=blank>Edit</a> &nbsp; '
                        .'<form method="post" style="display: inline" action="accountability.php?w=DelFxnID&FxnID='
                        .$res1['FxnID'].'">'
                        .'<input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" />'
                        . '<input type="submit" name="del" value="Delete" OnClick="return confirm(\'Delete Function - '.$res1['Function'].'?\n\nAll roles for this function must be deleted first.\');"></form>':'').'</td></tr>';
		$sql2=$sqlroles.' WHERE FxnID=\''.$res1['FxnID'].'\'';
		$stmt2=$link->query($sql2); $result2=$stmt2->fetchAll();
		foreach($result2 as $res2){
		echo'<tr><td colspan="3">'.$res2['Role'].'</td><td>'
                   .($editok?'<a href="accountability.php?w=EditRole&RoleID='.$res2['RoleID'].'" target=blank>Edit</a> &nbsp; '
                        .'<form method="post" style="display: inline" action="accountability.php?w=DelRoleID&RoleID='
                        .$res2['RoleID'].'">'
                        .'<input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" />'
                        . '<input type="submit" name="del" value="Delete" OnClick="return confirm(\'Delete Role - '.$res2['Role'].'?\');"></form>':'').'</td></tr>';
		}
		
	}
	echo'</table>';
	

break;

case 'EncodeFxn':
case 'EncodeRole':
case 'EditFxnID':
case 'EditRoleID':
    if (allowedToOpen(1601,'1rtc')){
	$sql='';
        foreach ($columnstoadd as $col){
            $sql.='`'.$col.'`=\''.addslashes($_POST[$col]).'\''.(end($columnstoadd)==$col?'':', ');
        }
        
        if(in_array($which, array('EncodeFxn','EncodeRole'))){
        $sql='INSERT INTO '.$table.' SET '.$sql; }
        else {
            $sql='UPDATE '.$table.' SET '.$sql.' WHERE `'.$primary.'`='.$_GET[$primary];
        }
	$stmt=$link->prepare($sql); $stmt->execute();
        header("Location:accountability.php?w=Encode");
		} else {
		echo 'No permission'; exit;
		}

break;

case 'UploadFxn':
case 'UploadRole':
    if (allowedToOpen(1601,'1rtc')){
        
	if(isset($_POST['upload'])){
        $tblname=$table; $firstcolumnname=current($columnstoadd); 
        $DOWNLOAD_DIR="../../uploads/"; 

        include '../backendphp/layout/uploaddatanoheader.php';
        }
        header("Location:accountability.php?w=Encode");
		} else {
		echo 'No permission'; exit;
		}
    break;
    
case 'EditFxn':
case 'EditRole':
    if (allowedToOpen(1601,'1rtc')){
        $sql='SELECT * FROM '.$table.' WHERE `'.$primary.'`='.$_GET[$primary];
        $stmt=$link->query($sql); $res=$stmt->fetch();
        
        $title='EOS: Accountability Edit';
        echo'<title>'.$title.'</title><h3>'.$title.'</h3>';
	
        $editform='';
        foreach($columnstoadd as $col){
            $editform.=$col.'&nbsp; <input type=text name="'.$col.'" value="'.$res[$col].'">&nbsp; &nbsp; ';
        }
        $editform='<form action=accountability.php?w=Edit'.$primary.'&'.$primary.'='.$_GET[$primary].' method=POST>'.$editform
                .'<input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" />'
                .'<input type="submit" name="submit"></form>';
        echo $editform;
		} else {
		echo 'No permission'; exit;
		}
    break;

case 'DelFxnID':
case 'DelRoleID':
    if (allowedToOpen(1601,'1rtc')){
        $sql='DELETE FROM '.$table.' WHERE `'.$primary.'`='.$_GET[$primary];
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:accountability.php?w=Encode");
		} else {
		echo 'No permission'; exit;
		}
    break;

}?>