<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(220,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');
include_once('../backendphp/layout/linkstyle.php');
$path=$_SERVER['DOCUMENT_ROOT'];

$which=(!isset($_GET['w'])?'AllPermissions':$_GET['w']);
echo '<br><a id=\'link\' href="allprocesseslist.php">System Modules & Processes</a> ';
echo ' <a id=\'link\' href="allprocesseslist.php?w=ShowRequest">Show Requests</a> ';
echo ' <a id=\'link\' href="../sysadmin/assignpermission.php?w=OpenSystemTonight">Open System Tonight</a>';
if (allowedToOpen(3003,'1rtc')) {
	echo ' <a id=\'link\' href="../sysadmin/accesspercompany.php">Give Access Per Company</a>';
}
echo '<br><br>';
switch ($which)
{
case 'AllPermissions':

$sql='SELECT PositionID FROM attend_30currentpositions WHERE deptheadpositionid='.$_SESSION['&pos'].'';
$stmt=$link->query($sql); $rows=$stmt->fetchAll();

$conds='';
foreach($rows AS $row){
	$conds.='(FIND_IN_SET('.$row['PositionID'].',`AllowedPos`)) OR ';
}
$conds=substr($conds, 0, -4);


// $condallowed='((FIND_IN_SET('.$_SESSION['&pos'].',`AllowedPos`)) OR (FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`)))';
$condallowed='('.$conds.' OR (FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`)))';

$sqlalloptions='CREATE TEMPORARY TABLE `SwitchboardItems` AS
SELECT s.switchid AS SwitchID, switchname AS Switch, 0 AS MenuID, 0 AS Menu, ProcessID, ProcessTitle, ProcessDesc, OnSwitch, ap.OrderBy, 0 AS WithSub,SUBSTRING_INDEX(SUBSTRING_INDEX(ProcessAddress, ".php", 1), "/", -1) AS ProAdd FROM `permissions_2allprocesses` ap JOIN `permissions_00switch` s on s.switchid=ap.OnSwitch WHERE OnSwitch<>0 AND '.$condallowed.' AND (ProcessID<1500 OR ProcessID>1600)
    UNION SELECT s.switchid AS SwitchID, switchname AS Switch, MenuID, Menu, CONCAT(MenuID,"NOT") AS ProcessID, Menu, "#" AS ProcessDesc, l1.switchid, l1.OrderBy, 1 AS WithSub,SUBSTRING_INDEX(SUBSTRING_INDEX(ProcessAddress, ".php", 1), "/", -1) AS ProAdd FROM `permissions_2allprocesses` ap RIGHT JOIN `permissions_01level1` l1 ON l1.MenuID=ap.OnSwitch JOIN `permissions_00switch` s on s.switchid=l1.switchid WHERE OnSwitch<>0 AND '.$condallowed.' AND (ProcessID<1500 OR ProcessID>1600)
UNION SELECT s.switchid AS SwitchID, switchname AS Switch, MenuID, Menu, ProcessID, ProcessTitle, ProcessDesc, OnSwitch, ap.OrderBy, 1 AS WithSub,SUBSTRING_INDEX(SUBSTRING_INDEX(ProcessAddress, ".php", 1), "/", -1) AS ProAdd FROM `permissions_2allprocesses` ap JOIN `permissions_01level1` l1 ON l1.MenuID=ap.OnSwitch JOIN `permissions_00switch` s on s.switchid=l1.switchid WHERE OnSwitch<>0 AND '.$condallowed.' AND (ProcessID<1500 OR ProcessID>1600) ORDER BY OrderBy;';
$stmt=$link->prepare($sqlalloptions); $stmt->execute();

$sqlmenugroup='SELECT si.SwitchID, Switch FROM `SwitchboardItems` si JOIN `permissions_00switch` s ON s.switchid=si.SwitchID GROUP BY si.SwitchID ORDER BY switchorder;';

$stmt=$link->query($sqlmenugroup); $resultgroup=$stmt->fetchAll();
?><title>Program Modules & Processes</title>
<style>
    ul.level1 { font-size: large; font-weight: 500; position: relative; left: 20px; }                
    ul.level2 { font-size: medium; font-weight: 400; position: relative; left: 40px; }
    ul.level3 { font-size: small; font-weight: 300; position: relative; left: 60px; }
                
</style>
<body>
<div id="wrapper">
<?php
echo '<h3>System Modules & Processes</h3><br>';
$switch='<ul class="level1">';

if(!isset($_POST['btnRequest'])){ 
$sql11 ="SELECT PositionID, Position FROM attend_1positions ORDER BY Position;";
$stmt11=$link->query($sql11); 
$positionlist = 'Position: <select name="ForPositionID">';
while($row11 = $stmt11->fetch()) {
	$positionlist = $positionlist . '<option value="'.$row11['PositionID'].'">'.$row11['Position'].'</option>';
}
$positionlist.='</select>';
if(allowedToOpen(3000,'1rtc')){
	$positionlist='';
}
echo '<form action="#" method="POST">'.$positionlist;
foreach ($resultgroup as $group){
    $switch=$switch.'<li>'.$group['Switch'].'<ul class="level2">';
    $sqlmenu='SELECT MenuID, Menu, ProcessID, ProcessTitle, ProcessDesc, OrderBy, WithSub,ProAdd FROM `SwitchboardItems` si WHERE OnSwitch='.$group['SwitchID'].' ORDER BY OrderBy;';
    $stmt=$link->query($sqlmenu);    $result=$stmt->fetchAll();
    
    foreach ($result as $command){
            
            $switch=$switch."<li>".((!allowedToOpen(3000,'1rtc'))?(substr($command['ProcessID'], -3)<>'NOT'?"<input type='checkbox' name='requestaccess[]' value='".$command['ProcessID']."'/> ":""):"").$command['ProcessTitle']."";

			$sqlonswitch0='SELECT ProcessID,ProcessDesc FROM permissions_2allprocesses WHERE ProcessAddress LIKE "%'.$command['ProAdd'].'%" AND OnSwitch=0 GROUP BY ProcessAddress';
			$stmtonswitch0=$link->query($sqlonswitch0);    $resultsonswitch0=$stmtonswitch0->fetchAll();

			foreach($resultsonswitch0 AS $resultonswitch0){
				$switch=$switch."<li>".((!allowedToOpen(3000,'1rtc'))?"<input type='checkbox' name='requestaccess[]' value='".$resultonswitch0['ProcessID']."'/> ":"")."<font color='green'>".$resultonswitch0['ProcessDesc']."</font>";
			}
            if ($command['WithSub']==1){
                   $sqlmenusub='SELECT ProcessID, ProcessTitle, ProcessDesc, OrderBy FROM `SwitchboardItems` si WHERE OnSwitch='.$command['MenuID'].' ORDER BY OrderBy;';
                    $stmt=$link->query($sqlmenusub); $resultsub=$stmt->fetchAll();
                    $switch=$switch.'<ul class="level3">'; $switch3='';
                    foreach ($resultsub as $commandsub){ $switch3.='<li>'.((!allowedToOpen(3000,'1rtc'))?'<input type="checkbox" name="requestaccess[]" value="'.$commandsub['ProcessID'].'" /> ':'').$commandsub['ProcessTitle'].'</li>'; }
                    $switch.=$switch3.'</ul>';
            }
            $switch.='</li>';
    }
    
    $switch.='</ul></li>';
}
echo $switch;
	if(!allowedToOpen(3000,'1rtc')){
		echo '<input type="submit" name="btnRequest" value="Request Permission"></form>';
	}
}
if(isset($_POST['btnRequest'])){
	$requestedprocessid=implode(",",$_REQUEST['requestaccess']);
	
	
	$sqlinsert='INSERT INTO approvals_systempermission SET RequestedByNo='.$_SESSION['(ak0)'].',Timestamp=NOW(),ForPositionID='.$_POST['ForPositionID'].',ProcessIDs="'.$requestedprocessid.'"'; //echo $sqlinsert; exit();
	$stmtinsert = $link->prepare($sqlinsert);
	$stmtinsert->execute();
			
	echo '<h3 style="color:green;">Request successfully submitted.</h3>';
}


break;


case 'ShowRequest':
	if(allowedToOpen(3000,'1rtc')){
		$reqcondi='';
		goto here;
	} 
	if(allowedToOpen(220,'1rtc')){
		$reqcondi='WHERE RequestedByNo='.$_SESSION['(ak0)'].'';
		goto here;
	}
	here:
	$sql='select sp.*,ForPositionID AS TxnID,Position AS ForPosition,NickName As RequestedBy FROM approvals_systempermission sp JOIN 1employees id ON sp.RequestedByNo=id.IDNo JOIN attend_1positions p ON sp.ForPositionID=p.PositionID '.$reqcondi.' ORDER BY Position';
	$columnnames=array('ForPosition', 'RequestedBy', 'Timestamp');
   
    $title='List of Requests';
	$width='50%';
	// if(allowedToOpen(3000,'1rtc')){
		$editprocesslabel='Lookup';
		$editprocess='../sysadmin/assignpermission.php?w=AccessPerPosition&Request=1&ForPositionID=';
	// }
	$delprocess='allprocesseslist.php?w=DeleteRequest&PositionID=';
	$txnidname='TxnID';
    include('../backendphp/layout/displayastable.php');

break;

case 'DeleteRequest':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM approvals_systempermission WHERE ForPositionID='.intval($_GET['PositionID']).''; 
	$stmt = $link->prepare($sql);
	$stmt->execute();
	
	header("Location:allprocesseslist.php?w=ShowRequest");
	
break;

}
?>
</ul> <!-- end div navmenu -->
</div> <!-- end div wrapper -->
<?php
 $link=null; $stmt=null;
?>
<br class="clearFloat" />
</body></html>