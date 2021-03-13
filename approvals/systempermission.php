<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if(!allowedToOpen(array(211,2111,6771),'1rtc')){ echo 'No permission'; exit();}
$showbranches=false; 
include_once('../switchboard/contents.php');
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
$which=!isset($_GET['w'])?'List':$_GET['w'];
echo '<title>System Requests</title>';
switch ($which){
    case 'List':
        if (!allowedToOpen(2111,'1rtc')){ echo 'No permission'; exit();}
        echo '<style>table,td,tr { padding: 3px;} </style>';
        
         $sqlsp='SELECT IF(FIND_IN_SET(PositionID,(SELECT AllowedPos FROM `permissions_2allprocesses` WHERE ProcessID=351)),800,
        su.BranchNo) AS BranchNo, IF(b.Pseudobranch=1,department,b.Branch) AS Branch, su.EncodedByNo, su.IPAdd, IF(FIND_IN_SET(PositionID,(SELECT AllowedPos FROM `permissions_2allprocesses` WHERE ProcessID=351)),"Mobile",b.IPAdd) as RecordedIP, su.BranchNo AS ActualBranchNo, PositionID, CONCAT(Nickname," ",Surname) as EncodedBy, p.PositionID, su.`TimeStamp` FROM approvals_2progpermission su JOIN `1branches` b on b.BranchNo=su.BranchNo join `1employees` e on e.IDNo=su.EncodedByNo JOIN `attend_30currentpositions` p on p.IDNo=su.EncodedByNo  WHERE Approval like "0";';
     
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
    if ($stmtsp->rowCount()>0){
$msgsp='<br><br><div><h3>Request for permission on Arwan system</h3><br><br><table bgcolor="FFFFF"><tr>';
foreach($datatoshowsp as $rows){
	$countsp++;
	$msgsp=$msgsp.'<td>Request From '.$rows['IPAdd'].',</td><td>Recorded IP '.$rows['RecordedIP'].'</td><td>'.$rows['TimeStamp'].'</td><td>';
	
	if (allowedToOpen(2111,'1rtc')){
	
	$linkurl = 'href="systempermission.php?w=Grant&ActualBranchNo='.$rows['ActualBranchNo'].'&BranchNo='.$rows['BranchNo'].'&NewIP='.$rows['IPAdd'].'&EncodedByNo='.$rows['EncodedByNo'].'';
//	$msgsp=$msgsp.' Give permission to '.$rows['EncodedBy'].' of '.$rows['Branch'].': [<a '.$link.'&Day=0">Regular</a>] [<a '.$link.'&Day=1">1 day</a>] &nbsp;  or &nbsp;<a href="systempermission.php?w=Unset&EncodedByNo='.$rows['EncodedByNo'].'">Unset</a></td></tr><tr>';
        
        echo '<script type="text/javascript">
     function delRequest () {
         return confirm("Really delete this?");
     }
        </script>';

        $msgsp=$msgsp.' Give permission to '.$rows['EncodedBy'].' of '.$rows['Branch'].': [<a '.$linkurl.'&Day=0">Regular</a>] [<a '.$linkurl.'&Day=1">1 day</a>] [<a '.$linkurl.'&Day=2">Regular with Attendance (No Bio)</a>] &nbsp; [<a '.$linkurl.'&Day=3">Regular with Attendance (Biometric)</a>] &nbsp; [<a '.$linkurl.'&Day=-1">Time In/Out Only</a>] &nbsp;  or &nbsp;<a href="systempermission.php?w=Unset&EncodedByNo='.$rows['EncodedByNo'].'">Unset</a> &nbsp;<a onClick="return delRequest();" href="systempermission.php?w=DeleteRequest&EncodedByNo='.$rows['EncodedByNo'].'">Delete Request</a></td></tr><tr>';        
	
	}
	$msgsp=$msgsp.($countsp%15==0?'</tr><tr>':'');    
}

   echo $msgsp.'</tr></table><br><br></div>';
   } else { echo 'No pending requests<br><br>';}
  // Unrecognized approvals 
    $sqlsp='SELECT su.*, Branch, Nickname as EncodedBy, su.`TimeStamp` AS `TS` FROM approvals_2progpermission su join `1branches` b on b.BranchNo=su.BranchNo join `1employees` e on e.IDNo=su.EncodedByNo where Approval not like "0"'
            . ' UNION SELECT su.*, "Mobile", Nickname as EncodedBy, su.`TimeStamp` AS `TS` FROM approvals_2progpermission su join `1employees` e on e.IDNo=su.EncodedByNo where Approval not like "0" AND BranchNo=800 ';
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br><h3>Unrecognized  permission on Arwan system</h3><table bgcolor="FFFFF"><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td>'.htmlcharwithbr($fromBRtoN,$rows['EncodedBy']).' of '.$rows['Branch'].'</td><td>Approved on '.$rows['TS'].'&nbsp;<a onClick="return delRequest();" href="systempermission.php?w=DeleteRequest&EncodedByNo='.$rows['EncodedByNo'].'">Delete Request</a></td>'.($countsp%15==0?'</tr><tr>':'');
   }
   echo $msgsp.'<br>'.$rows['IPAdd'].'</tr></table><br><br></div>';
   }  else { echo 'No unaccepted approvals<br><br>';}
   $stmtsp=null;
 
 break;
 
    case 'Grant':
        if (!allowedToOpen(2111,'1rtc')){ echo 'No permission'; exit();}
        include_once $path.'/acrossyrs/commonfunctions/fxngenrandpass.php';
		
        $approval=generatePassword(25);
		if (($_REQUEST['BranchNo']==800) and ($_GET['Day']<>2)){ // Mobile
			$sql='Update approvals_2progpermission Set BranchNo=800, Approval="'.$approval.'", Day = '.intval($_GET['Day']).', ApprovedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() where EncodedByNo='.$_REQUEST['EncodedByNo'];
			$stmt=$link->prepare($sql); $stmt->execute();	
		} else {
			$sql='Update approvals_2progpermission Set Approval="'.$approval.'", Day = '.intval($_GET['Day']).', ApprovedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() where BranchNo='.$_REQUEST['ActualBranchNo'] .' AND EncodedByNo='.$_REQUEST['EncodedByNo'];
			
			$stmt=$link->prepare($sql); $stmt->execute(); 
			$sql='Update `1branches` Set IPAdd="'.$_REQUEST['NewIP'].'" where BranchNo='.$_REQUEST['BranchNo'].' AND Pseudobranch=0'; 
			$stmt=$link->prepare($sql); $stmt->execute();
		}
		
        header("Location:systempermission.php");
        break;

    case 'Unset':
        if (!allowedToOpen(2111,'1rtc')){ echo 'No permission'; exit();}
        $sql='Update approvals_2progpermission Set Approval="Xunset", ApprovedByNo='.$_SESSION['(ak0)'].' where EncodedByNo='.$_REQUEST['EncodedByNo'];
            $stmt=$link->prepare($sql); $stmt->execute(); 
            header("Location:systempermission.php");
        break;
		
		
    case 'DeleteRequest':
        if (!allowedToOpen(2111,'1rtc')){ echo 'No permission'; exit();}
	$sql='DELETE FROM approvals_2progpermission where EncodedByNo='.$_REQUEST['EncodedByNo'];
            $stmt=$link->prepare($sql); $stmt->execute(); 
            header("Location:systempermission.php");
	break;
}
 $link=null; $stmt=null; 
?>