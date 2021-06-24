<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6703,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');

$which=!isset($_GET['w'])?'List':$_GET['w'];
$title='Add New Position';  
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT Position,PositionID FROM `attend_1positions` ORDER BY Position','Position','PositionID','poslist');
$formdesc='</i>To add permissions for standard processes for <form method=post action=positions.php?w=AssignPermissions>'
        . 'New Position ID <input type=number name=NewPositionID size="3" ><input type=submit value="Add Standard Permissions"></form><i><br><br><form method=post action=positions.php?w=CopyPermissions>PositionFROM: <input type=text name=PosFrom list=poslist> PositionTO:<input type=text name=PosTo list=poslist><input type=submit value="Copy Permission"></form><br><br><form method=post action=positions.php?w=DelPosition>Remove Position in AllowedPos: <input type=text name=PosToRemove list=poslist><input type=submit value="Remove Position"></form>
';
$list='List';
$table='attend_1positions'; $txnidname='PositionID'; 
$sql='SELECT p.*, department AS Department, (SELECT Position FROM `attend_1positions` WHERE PositionID=p.supervisorpositionid) AS Supervisor, IF(p.PreferredRateType=1,"Monthly","Daily") AS RateType,p.JobLevelID
FROM `attend_1positions` p LEFT JOIN `attend_0joblevels` jl ON jl.JobLevelID=p.JobLevelID
JOIN `1departments` d ON d.deptid=p.deptid ';
$columnnameslist=array('PositionID', 'Position', 'Department', 'Supervisor', 'RateType', 'JobLevelID', 'VLfromPosition','MaxVLfromTenure'); 
$columnstoadd=array_diff($columnnameslist,array('Department','Supervisor','RateType'));
$columnstoadd[]='deptid'; $columnstoadd[]='supervisorpositionid'; $columnstoadd[]='PreferredRateType';
$columnstoedit=$columnstoadd;
$columnswithlists=array('Department','Supervisor','JobLevelID');
$listsname=array('Department'=>'departments','Supervisor'=>'supervisors','JobLevelID'=>'joblevels');
$listssql=array(
    array('sql'=>'SELECT * FROM `1departments`', 'listvalue'=>'department', 'label'=>'deptid','listname'=>'departments'),
    array('sql'=>'SELECT * FROM `attend_1positions`', 'listvalue'=>'Position', 'label'=>'PositionID','listname'=>'supervisors'),
    array('sql'=>'SELECT JobLevelID, CONCAT(JobClassification," Level ", RIGHT(JobLevelID,1)) AS JobLevel FROM `attend_0joblevels` jl JOIN `attend_0jobclass` jc ON jc.JobLevelID=jl.JobLevelID ORDER BY jc.JobLevelID,JobLevelID', 'listvalue'=>'JobLevel', 'label'=>'JobLevelID','listname'=>'joblevels')
);

if($which=='AssignPermissions') {
    $sql='CREATE TEMPORARY TABLE processlist AS (SELECT ProcessID FROM `permissions_2allprocesses` WHERE FIND_IN_SET(0,AllowedPos) AND (NOT FIND_IN_SET('.$_POST['NewPositionID'].',AllowedPos)))';
    $stmt = $link->prepare($sql); $stmt->execute();
    $sql='UPDATE `permissions_2allprocesses` SET '
            . 'AllowedPos=CONCAT(AllowedPos,",'.$_POST['NewPositionID'].'") WHERE ProcessID IN (SELECT ProcessID FROM processlist)';
    $stmt = $link->prepare($sql); $stmt->execute();
    header("Location:positions.php");
}

if($which=='CopyPermissions'){
	
	$sql='SELECT GROUP_CONCAT(ProcessID) AS ProcessID FROM `permissions_2allprocesses` WHERE (FIND_IN_SET('.$_POST['PosFrom'].',AllowedPos)) AND (NOT FIND_IN_SET('.$_POST['PosTo'].',AllowedPos))';
		$stmt=$link->query($sql);
		$result=$stmt->fetch();
		
	$sql='UPDATE `permissions_2allprocesses` SET '. 'AllowedPos=CONCAT(AllowedPos,",'.$_POST['PosTo'].'") WHERE ProcessID IN ('.$result['ProcessID'].')';
        if($_SESSION['(ak0)']==1002) {echo $sql;}
	$stmt = $link->prepare($sql); $stmt->execute();
	header("Location:positions.php");
}

if($which=='DelPosition') {
	
    $sql='SELECT ProcessID, AllowedPos FROM `permissions_2allprocesses` WHERE FIND_IN_SET('.$_POST['PosToRemove'].',AllowedPos)';
	$stmt=$link->query($sql); $res=$stmt->fetchAll();
	foreach ($res as $row){
		$arr = array_diff(explode(",",$row['AllowedPos']),array($_POST["PosToRemove"]));
		$sql1='UPDATE `permissions_2allprocesses` SET `AllowedPos`='.(!empty($arr)?"'".implode(',',$arr)."'":'NULL').' WHERE ProcessID='.$row['ProcessID'].';';
		
		$stmt=$link->prepare($sql1); $stmt->execute();
	}
    $stmt = $link->prepare($sql); $stmt->execute();
    header("Location:positions.php");
}



if($which=='List') {

    
$columnentriesarray=array(
                    array('field'=>'PositionID', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'Position', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'deptid','caption'=>'Department', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'departments'),
                    array('field'=>'supervisorpositionid','caption'=>'Immediate Supervisor','type'=>'text','size'=>10,'required'=>true,'list'=>'supervisors'),
                    array('field'=>'PreferredRateType','caption'=>'RateType', 'type'=>'text','size'=>5, 'required'=>true,'caption'=>'Rate Type (0-Daily, 1-Monthly)'),
                    array('field'=>'JobLevelID', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'joblevels'),
                    array('field'=>'VLfromPosition', 'type'=>'text', 'size'=>2, 'required'=>true),
                    array('field'=>'MaxVLfromTenure', 'type'=>'text','size'=>2, 'required'=>true),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false)
                    );
}
    
            $file='positions.php?w='; $fieldsinrow=5; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=6702; $editallowed=6702; $delallowed=6702;

if (allowedToOpen(6702,'1rtc')) { $delprocess='positions.php?w=Delete&PositionID=';$editprocess='positions.php?w=EditSpecifics&PositionID='; $editprocesslabel='Edit';}

        
// set first field only if the first field should also be added/edited
$firstfield='PositionID';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';
	
include('../backendphp/layout/genlists.php');


?>