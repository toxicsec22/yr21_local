<?php
if (!empty($_SERVER['HTTPS'])) {
    $https='s';
  } else {
    $https='';
  }
?>

<link href="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <!-- Custom styles for this template-->
<link href="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/css/sb-admin-2.min.css" rel="stylesheet">
<style>
body {
    color:black;
}
</style>
</head>

<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6703,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');

$which=!isset($_GET['w'])?'List':$_GET['w'];
$title='Add New Position';  $title='';  
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT Position,PositionID FROM `attend_1positions` ORDER BY Position','Position','PositionID','poslist');

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

    echo '<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPosition"><i class="fas fa-plus-square"></i> Add Position</button> 
    
    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addPermissionStandard"><i class="fas fa-plus-square"></i> Add Permissions for Standard Processes</button>  

    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#CopyPermissions"><i class="fas fa-clipboard"></i> Copy Permissions</button>

    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#RemovePosition"><i class="fas fa-trash"></i> Remove Position in AllowedPos</button>
    <br><br>';
                    $columnentriesarray=array();
    
    echo '<div class="modal fade "id="addPosition" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Add Position</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
    
        <form action="positions.php?w=Add&action_token='.$_SESSION['action_token'].'" method="POST" class="" autocomplete="off">
        <div class="modal-body">
        <div class="form-group">
            <label class="control-label">Position ID</label>
        <div>
            <input name="PositionID" class="form-control" />
        </div>
        </div>
                <label>Position</label> <input name="Position" class="form-control" />
           
             <label>Department</label> <input name="deptid" class="form-control" list="departments" />
            
                <label>Immediate Supervisor</label> <input name="supervisorpositionid" class="form-control" list="supervisors"/>
            
                <label>Rate Type (0-Daily, 1-Monthly)</label> <input name="PreferredRateType" class="form-control" />
                <label>JobLevelID</label> <input name="JobLevelID" class="form-control" list="joblevels"/>
                <label>VLfromPosition</label> <input name="VLfromPosition" class="form-control" />
                <label>MaxVLfromTenure</label> <input name="MaxVLfromTenure" class="form-control" />
                <label>Remarks</label> <input name="Remarks" class="form-control" />
        </div>

        <div class="modal-footer">
            <button type="submit" name="btnAddScore" id ="btnAddScore" class="btn btn-primary "> Add new</button>
        </div>

                </form>

            
        </div>
    </div>
</div>';

echo '<div class="modal fade "id="addPermissionStandard" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Add Permissions for Standard Processes for</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
    
        <form action="positions.php?w=AssignPermissions&action_token='.$_SESSION['action_token'].'" method="POST" class="" autocomplete="off">
        <div class="modal-body">
        <div class="form-group">
            <label class="control-label">New Position ID</label>
        <div>
            <input type=number name=NewPositionID class="form-control" size="3" >
        </div>
        </div>
    
        </div>

        <div class="modal-footer">
            <button type="submit" name="btnAddStandard" id ="btnAddStandard" class="btn btn-primary "> Add Standard Permissions</button>
        </div>

                </form>

            
        </div>
    </div>
</div>';


echo '<div class="modal fade "id="CopyPermissions" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Copy Permissions</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
    
        <form action="positions.php?w=CopyPermissions&action_token='.$_SESSION['action_token'].'" method="POST" class="" autocomplete="off">
        <div class="modal-body">

        <div class="form-group">
            <label class="control-label">Position FROM</label>
            <div>
                <input type=text name=PosFrom class="form-control" list="poslist" >
            </div>
        </div>

        <div class="form-group">
            <label class="control-label">Position TO</label>
            <div>
                <input type=text name=PosTo class="form-control" list="poslist" >
            </div>
        </div>
    
        </div>

        <div class="modal-footer">
            <button type="submit" name="btnCopy" id ="btnCopy" class="btn btn-info "> Copy Permission</button>
        </div>

                </form>

            
        </div>
    </div>
</div>';



echo '<div class="modal fade "id="RemovePosition" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Remove Position</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>



        <form action="positions.php?w=DelPosition&action_token='.$_SESSION['action_token'].'" method="POST" class="" autocomplete="off">
        <div class="modal-body">
        <div class="form-group">
            <label class="control-label">Position ID</label>
        <div>
            <input type=number name=PosToRemove class="form-control" list="poslist" >
        </div>
        </div>
    
        </div>

        <div class="modal-footer">
            <button type="submit" name="btnRemove" id ="btnRemove" class="btn btn-danger "> Remove Position</button>
        </div>

                </form>

            
        </div>
    </div>
</div>';


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




    
  <script src="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>


  <!-- Custom scripts for all pages-->
  <script src="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/js/sb-admin-2.min.js"></script>