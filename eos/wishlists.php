<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(2202,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');


$which=!isset($_GET['w'])?'List':$_GET['w'];
$showall=!isset($_POST['showall'])?0:$_POST['showall'];
$title='Wishlist';  $formdesc='<a href=wishlists.php?w=Upload>Upload Data</a></i>
    '.(allowedToOpen(2207,'1rtc')?'<br><br><form style="display: inline" method="post" action="wishlists.php?w=List">
    <input type=hidden name="showall" value='.($showall==1?0:1).'>
    <input type=submit name=submit value="'.($showall==1?'Hide Completed':'Show All').'">
    </form><i>':'').'';
$list='List';
$table='it_list'; $txnid='TxnID'; $txnidname='TxnID';
if (allowedToOpen(1500,'1rtc')) { 
$sqlc='select GROUP_CONCAT(d.deptid) as deptid from attend_30currentpositions cp join 1departments d on d.deptheadpositionid=cp.PositionID WHERE IDNo='.$_SESSION['(ak0)'].'';
// echo $sqlc;
$stmtc = $link->query($sqlc); $resultc = $stmtc->fetch();	 
}else{
$sqlc='select GROUP_CONCAT(d.deptid) as deptid from attend_30currentpositions cp join 1departments d on d.deptheadpositionid=cp.deptheadpositionid WHERE IDNo='.$_SESSION['(ak0)'].'';
// echo $sqlc;
$stmtc = $link->query($sqlc); $resultc = $stmtc->fetch();	 
}
 if (allowedToOpen(2207,'1rtc')) {  //IT
  $columnnameslist=array('Department', 'Details', 'AssignedTo', 'DateAssigned', 'DateCompleted', 'Status','EncodedBy','TimeStamp');
  $columnstoadd=array('Details','deptid','EncodedByNo');
  $columnstoedit=array('Details','DateAssigned','DateCompleted','Status','deptid','AssignedToID'); 
  $requiredcol=array('depitd','Details');
  $colnames=array('depitd','Details','AssignedToID');
  $condition='and i.switchdeptid is null ';
  $coldept='d.dept';
  $showallcondition=' and i.switchdeptid is null ';
  
   if (allowedToOpen(1500,'1rtc')) { 
		$condition='and (i.switchdeptid is null or  i.switchdeptid in ('.$resultc['deptid'].')) ';
		$coldept='if(d.dept is null,d1.dept,concat("IT (",d.dept,")"))';
		$showallcondition='';
   }
   
 $listcondition='';
 }else{
	 $columnnameslist=array('Department','Details', 'EncodedBy', 'TimeStamp'); 
	 $columnstoadd=array('Details','switchdeptid','EncodedByNo');
	 $columnstoedit=array('Details','switchdeptid'); 
	 $requiredcol=array('switchdeptid','Details');
	 $colnames=array('switchdeptid','Details');
	$condition='and i.switchdeptid in ('.$resultc['deptid'].') ';
	$listcondition='where deptid in ('.$resultc['deptid'].')';
	 $coldept='d1.dept';
 }

$sql='SELECT i.*, e.Nickname AS AssignedTo,e1.Nickname AS EncodedBy,i.TimeStamp as TimeStamp, '.$coldept.' AS Department,
Case when Status=2 then "Completed" when Status=1 then "Working" when Status=4 then "Moved" when Status=0 then "Unassigned" when Status=\'-1\' then "Cancelled" end as Status
FROM `it_list` i LEFT JOIN `1employees` e ON e.IDNo=i.AssignedToID LEFT JOIN `1employees` e1 ON e1.IDNo=i.EncodedByNo LEFT JOIN `1departments` d ON d.deptid=i.deptid LEFT JOIN `1departments` d1 ON d1.deptid=i.switchdeptid '.((allowedToOpen(1500,'1rtc') OR $which=='EditSpecifics')?(($showall==1 OR $which=='EditSpecifics')?'':'WHERE Status NOT IN (2,-1,4) '.$condition.''):(($showall==1)?'where i.switchdeptid is null ':'WHERE Status NOT IN (2,-1,4) '.$condition.'')).'';
// echo $sql;
$requiredts=true;
$requireencodedby=true;
if($which=='Edit') { $columnstoadd=$columnstoedit;}
$columnswithlists=array('AssignedTo','Dept','AssignedToID','deptid','Status');
$listsname=array('Dept'=>'depts','AssignedTo'=>'programmers','deptid'=>'depts','AssignedToID'=>'programmers','Status'=>'status');
$listssql=array(
    array('sql'=>'SELECT IDNo, LEFT(FullName,LOCATE(" -",FullName)-1) AS Nickname FROM attend_30currentpositions WHERE deptid=55 OR IDNo=1002', 'listvalue'=>'Nickname', 'label'=>'IDNo','listname'=>'programmers'),
	array('sql'=>'Select `deptid`, `dept` FROM `1departments` '.$listcondition.'', 'listvalue'=>'dept', 'label'=>'deptid','listname'=>'depts'),
            array('sql'=>'SELECT 0 AS StatusID, "Unassiged" AS Status UNION SELECT 1, "Working" UNION SELECT 2, "Completed" UNION SELECT -1, "Cancelled"', 'listvalue'=>'Status', 'label'=>'StatusID','listname'=>'status')
);


if($which=='List') {
	 if (allowedToOpen(2207,'1rtc')) {  //IT
		$columnentriesarray=array(
                    array('field'=>'deptid', 'caption'=>'DeptID', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'depts'),
                    array('field'=>'Details', 'type'=>'text','size'=>50, 'required'=>true),
                    array('field'=>'EncodedByNo', 'type'=>'hidden', 'size'=>0, 'value'=>$_SESSION['(ak0)'])
                    );
	 }else{
	$sqldept = 'SELECT deptid,PositionID FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].'';
	$stmtdept = $link->query($sqldept); $rowdept = $stmtdept->fetch();	 
	$defaultvalue=$rowdept['deptid'];
		 $columnentriesarray=array(
                    array('field'=>'switchdeptid', 'caption'=>'DeptID', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'depts','value'=>$defaultvalue),
                    array('field'=>'Details', 'type'=>'text','size'=>50, 'required'=>true),
                    array('field'=>'EncodedByNo', 'type'=>'hidden', 'size'=>0, 'value'=>$_SESSION['(ak0)'])
                    );
	 }
$sql.='ORDER BY Department, TimeStamp';
}
    
            $file='wishlists.php?w='; $fieldsinrow=6; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editcommand2='Move'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=2202; $editallowed=2202; $editallowed2=2202; $delallowed=2202;
$delcondition=' and EncodedByNo='.$_SESSION['(ak0)'].'';
$editcondition=' and EncodedByNo='.$_SESSION['(ak0)'].'';

if (allowedToOpen(2207,'1rtc')) { 
	 $editprocess='wishlists.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit'; $editprocess2='wishlists.php?w=Move&action_token='.$_SESSION['action_token'].'&TxnID='; $editprocesslabel2='Move to EOS';
	 $editprocess='wishlists.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit';
	$delprocess='wishlists.php?w=Delete&TxnID=';}
else{
	$editprocess='wishlists.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit';
	$delprocess='wishlists.php?w=Delete&TxnID=';
}

        
// set first field only if the first field should also be added/edited
$firstfield='Details';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

//move to eos
$sqlfetch='SELECT Details,IFNULL(AssignedToID,'.$_SESSION['(ak0)'].') AS AssignedToID FROM '.$table.'';
$getcolumn1='AssignedToID'; $getcolumn2='Details'; if($which==$editcommand2){ $requiredcol=$getcolumn1;}

$columninsert1='Who';
$columninsert2='RockOrIssues';
$sqlnewinsert='INSERT INTO eos_2vtoqtrsub SET ';
$sqlnewinsertaddl='ManComOrdept=55,IsRock=2,EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW()';

$sqlupdate2='UPDATE '.$table.' SET Status=4 ';

$errornotif='<b>Not yet assigned.</b>';
	
include('../backendphp/layout/genlists.php');
