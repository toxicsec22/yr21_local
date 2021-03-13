<html>
<head>
<title>Carpenter Schedule</title>
<?php
 
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

if (!allowedToOpen(6440,'1rtc')) { echo 'No permission'; exit;} 
$showbranches=false; include_once('../switchboard/contents.php');
include_once('../backendphp/layout/regulartablestyle.php');



$which=$_GET['w'];

?>
<style>
    table,td,tr {padding: 4px;}
</style>
</head>
<body>
<?php



$which=!isset($_GET['w'])?'Sched':$_GET['w'];
?>
<form method="post" action="carpsched.php?w=Sched" enctype="multipart/form-data">
Choose Month (1 - 12):  <input type="text" name="month" value="<?php echo date('m'); ?>"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
if (!isset($_REQUEST['month'])){
$month=date('m');
$formdesc='Schedule for the month '.strtoupper(date('F')).'<br>';
} else {
    $month=$_REQUEST['month'];
}
switch ($which){
case 'Sched':
include_once('../generalinfo/lists.inc');
$title='Carpenter Schedule';
if (allowedToOpen(6442,'1rtc')){

$sql='SELECT * FROM 1branches WHERE PseudoBranch<>1 ORDER BY Branch;';
$stmt = $link->query($sql);
	
$choosebranch='Branch<select name="BranchNo"><option value="0">Office</option>';
while($row= $stmt->fetch()) {
	$choosebranch.='<option value="'.$row['BranchNo'].'">'.$row['Branch'].'</option>';
}
$choosebranch.='</select>';

$addlmenu='<form method=post action="carpsched.php?w=AddSched">
    Date<input type=date name="DateSchedule" value="'.date('Y-m-d').'">
    Carpenter<input type=text name="FullName" list="employeesperposition">
	'.$choosebranch.'
    Details<input type=text name="Details">
    <input type=submit name=submit value="Submit">
</form>';
$addlmenu=$addlmenu.renderListWithCondition('employeesperposition','(3,141)');
} else {
   $addlmenu='';
}
$sql0='SELECT e.IDNo, e.Nickname FROM attend_30currentpositions cp join `1employees` e on cp.IDNo=e.IDNo where PositionID in (3,141)';
$stmt0=$link->query($sql0);
$resultadmin=$stmt0->fetchAll();
$sql='SELECT DateSchedule, ';
$columnnames=array('DateSchedule');
foreach ($resultadmin as $carpenter){
   $sql=$sql.'max(case when s.EmpIDNo='.$carpenter['IDNo'].' then CONCAT("(",IF(s.BranchNo=0,"Office",Branch),") ",s.Details) end) as '.$carpenter['Nickname'].', ';
   $columnnames[]=$carpenter['Nickname'];
}
$sql=$sql.' TxnID FROM calendar_2sched s join `1employees` e on s.EmpIDNo=e.IDNo JOIN 1branches b ON s.BranchNo=b.BranchNo where month(DateSchedule)='.$month.' group by DateSchedule;'; //echo $sql;break;
$txnid='TxnID';
//$editprocess='carpsched.php?w=EditSched&edit=2&TxnID=';
$columnstoedit=array_diff($columnnames,array('DateSchedule'));
$editprocesslabel='Commit';
    include('../backendphp/layout/displayastableonlynoheaders.php');

break;

case 'AddSched':
include_once('../backendphp/functions/getnumber.php');
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$sql0='Select IDNo from `1employees` where concat(`Nickname`,\' \',`SurName`) LIKE \''.addslashes($_POST['FullName']) .'\' and Resigned=0';
	$stmt0=$link->query($sql0);
        $result0=$stmt0->fetch();
	$idno=$result0['IDNo'];
        //$idno=getNumber('Employee',addslashes($_POST['FullName']));
	$month=substr($_POST['DateSchedule'],5,2);
	$sql0='Select TxnID from `calendar_2sched` where EmpIDNo='.$idno .' and DateSchedule=\''.$_POST['DateSchedule'].'\'';
	//echo $sql0;break;
	 $stmt0=$link->query($sql0);
	 $result0=$stmt0->fetch();
	if ($stmt0->rowCount()==0){ //no record
	$sqlinsert='Insert into `calendar_2sched` SET  ';
        $sql='';
        $columnstoedit=array('DateSchedule','BranchNo','Details');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EmpIDNo='.$idno; 
	
	} else { //edit	 
	 $txnid=$result0['TxnID'];
	 $sql='UPDATE `calendar_2sched` SET BranchNo=\''.$_POST['BranchNo'].'\', `Details`=\''.$_POST['Details'].'\' where TxnID='.$txnid;
	}
        //echo $sql;break;
	$stmt=$link->prepare($sql);
	$stmt->execute();
        
        header("Location:carpsched.php?w=Sched&month=".$month);
  
    break;

}
noform:
      $link=null; $stmt=null;
?>
</body>
</html>