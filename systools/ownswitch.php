<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6064,'1rtc')) { echo 'No Permission'; exit(); }
$showbranches=false; include_once('../switchboard/contents.php');
$title='Personalize Switchboard';
echo '<title>'.$title.'</title>';
$which=(!isset($_GET['w'])?'List':$_GET['w']);
$ako=$_SESSION['(ak0)'];
            
switch ($which)
{
	case 'List':
	 
                $sql0='SELECT ProcessIDs FROM `permissions_4ownswitch` WHERE IDNo='.$_SESSION['(ak0)'];
                $stmt0=$link->query($sql0); $row0=$stmt0->fetch();
				
                if($stmt0->rowCount()>0) { $add='2';} else { $add='1'; }
                // put link to populate as DEFAULT
                include_once('../backendphp/layout/linkstyle.php');
                echo '<a id=\'link\' href="ownswitch.php?w=Defaults&Add='.$add.'">Set Back to Default</a><br/><br/>';
                
                $condition=' WHERE (`OnSwitch` > 0) AND ((FIND_IN_SET('.$_SESSION['&pos'].',`AllowedPos`)) OR (FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`)))';
                
		$sql1='SELECT '.($stmt0->rowCount()>0?'IF(ProcessID IN ('.$row0['ProcessIDs'].'),"TRUE","FALSE")':"FALSE").' as OnSwitch, ProcessID,ProcessTitle, switchname AS Menu, ap.OrderBy, s.switchorder SwitchOrderBy FROM permissions_2allprocesses ap JOIN `permissions_00switch` s ON s.switchid=ap.OnSwitch WHERE ProcessID IN (SELECT ProcessID FROM `permissions_2allprocesses` '.$condition.') UNION  SELECT '.($stmt0->rowCount()>0?'IF(ProcessID IN ('.$row0['ProcessIDs'].'),"TRUE","FALSE")':"FALSE").' as OnSwitch, ProcessID,ProcessTitle, CONCAT(switchname, " - ", Menu), ap.OrderBy, l1.OrderBy FROM permissions_2allprocesses ap JOIN `permissions_01level1` `l1` ON `l1`.MenuID=ap.OnSwitch JOIN `permissions_00switch` s ON s.switchid=`l1`.switchid WHERE ProcessID IN (SELECT ProcessID FROM `permissions_2allprocesses` '.$condition.') ORDER BY SwitchOrderBy, OrderBy ';
              //   echo $sql1;
                $stmt1=$link->query($sql1); $data=$stmt1->fetchAll(PDO::FETCH_ASSOC);
				
                if(count($data) > 0){
					
                include_once '../backendphp/layout/displayastablewithbooleandata.php';
                $headers = array('OnSwitch','ProcessTitle', 'Menu');
                createTableWithBooleanData($headers, $data, 
					'ownswitch.php?w=Update&Add='.$add, "POST", 
					$title, "", "Process", "ProcessID");
                }
                			
		
	
	break; 
	
        case 'Defaults':
            
            $sql='DELETE FROM `permissions_4ownswitch` WHERE IDNo = '.$_SESSION['(ak0)'].'';
            $stmt=$link->prepare($sql); $stmt->execute();
			unset($_SESSION['ownswitch']);
            header('Location:ownswitch.php');
			
        break;
        
        case 'Update':
            $arr=implode(',', $_POST['OnSwitch']);
            if ($_GET['Add']==1){
            $sql='INSERT INTO `permissions_4ownswitch` (IDNo,ProcessIDs,`TimeStamp`) VALUES ('.$ako.', \''.$arr.'\',Now())'; 
            } else { $sql='UPDATE `permissions_4ownswitch` SET `ProcessIDs`=\''.$arr.'\', `TimeStamp`=Now() WHERE IDNo='.$ako;}
            $stmt=$link->prepare($sql); $stmt->execute();
			$_SESSION['ownswitch']=1;
            header('Location:ownswitch.php');
        break;
	
}
 $link=null; $stmt=null; 
?>
</div> <!-- end section -->
