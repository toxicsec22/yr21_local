<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(2201,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');


$which=!isset($_GET['w'])?'List':$_GET['w'];
$showall=!isset($_POST['showall'])?0:$_POST['showall'];
$title='IT Dept To Do List';  $formdesc='<a href=it_todo.php?w=Upload>Upload Data</a></i><br><br>
    <form style="display: inline" method="post" action="it_todo.php?w=List">
    <input type=hidden name="showall" value='.($showall==1?0:1).'>
    <input type=submit name=submit value="'.($showall==1?'Hide Completed':'Show All').'">
    </form><i>';
$list='List';
$table='it_list'; $txnidname='TxnID';
$sql='SELECT i.*, Nickname AS AssignedTo, dept AS Dept,
Case when Status=2 then "Completed" when Status=1 then "Working" when Status=4 then "Moved" when Status=0 then "Unassigned" when Status=\'-1\' then "Cancelled" end as Status
FROM `it_list` i LEFT JOIN `1employees` e ON e.IDNo=i.AssignedToID LEFT JOIN `1departments` d ON d.deptid=i.deptid '.(($showall==1 OR $which=='EditSpecifics')?'':'WHERE Status NOT IN (2,-1,4) ');
$columnnameslist=array('Dept', 'Details', 'AssignedTo', 'DateAssigned', 'DateCompleted', 'Status');
$columnstoadd=array('Details');
$columnstoadd[]='deptid'; $columnstoadd[]='EncodedByNo'; $columnstoadd[]='FirstEncodeTS'; 
//$columnstoedit=$columnnameslist;
$columnstoedit=array_diff($columnnameslist,array('Dept','AssignedTo')); $columnstoedit[]='deptid'; $columnstoedit[]='AssignedToID';
if($which=='Edit') { $columnstoadd=$columnstoedit;}
$columnswithlists=array('AssignedTo','Dept','AssignedToID','deptid','Status');
$listsname=array('Dept'=>'depts','AssignedTo'=>'programmers','deptid'=>'depts','AssignedToID'=>'programmers','Status'=>'status');
$listssql=array(
    array('sql'=>'SELECT IDNo, LEFT(FullName,LOCATE(" -",FullName)-1) AS Nickname FROM attend_30currentpositions WHERE deptid=55 OR IDNo=1002', 'listvalue'=>'Nickname', 'label'=>'IDNo','listname'=>'programmers'),
	array('sql'=>'Select `deptid`, `dept` FROM `1departments`', 'listvalue'=>'dept', 'label'=>'deptid','listname'=>'depts'),
            array('sql'=>'SELECT 0 AS StatusID, "Unassiged" AS Status UNION SELECT 1, "Working" UNION SELECT 2, "Completed" UNION SELECT -1, "Cancelled"', 'listvalue'=>'Status', 'label'=>'StatusID','listname'=>'status')
);


if($which=='List') {
$columnentriesarray=array(
                    array('field'=>'deptid', 'caption'=>'Department', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'depts'),
                    array('field'=>'Details', 'type'=>'text','size'=>50, 'required'=>true),
                    array('field'=>'EncodedByNo', 'type'=>'hidden', 'size'=>0, 'value'=>$_SESSION['(ak0)']),
                    array('field'=>'FirstEncodeTS', 'type'=>'hidden', 'size'=>0, 'value'=>'Now()')
                    );
$sql.='ORDER BY Department, FirstEncodeTS';
}
    
            $file='it_todo.php?w='; $fieldsinrow=6; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editcommand2='Move'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=2201; $editallowed=2201; $editallowed2=2201; $delallowed=1500;

if (allowedToOpen(2201,'1rtc')) { $delprocess='it_todo.php?w=Delete&TxnID='; $editprocess='it_todo.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit'; $editprocess2='it_todo.php?w=Move&action_token='.$_SESSION['action_token'].'&TxnID='; $editprocesslabel2='Move to EOS';}

        
// set first field only if the first field should also be added/edited
$firstfield='Details';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

//move to eos
$sqlfetch='SELECT Details,IFNULL(AssignedToID,'.$_SESSION['(ak0)'].') AS AssignedToID FROM '.$table.'';
$getcolumn1='AssignedToID'; $getcolumn2='Details'; $requiredcol=$getcolumn1;

$columninsert1='Who';
$columninsert2='RockOrIssues';
$sqlnewinsert='INSERT INTO eos_2vtoqtrsub SET ';
$sqlnewinsertaddl='ManComOrdept=55,IsRock=2,EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW()';

$sqlupdate2='UPDATE '.$table.' SET Status=4 ';

$errornotif='<b>Not yet assigned.</b>';
	
include('../backendphp/layout/genlists.php');
