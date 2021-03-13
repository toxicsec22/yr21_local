<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

if (!allowedToOpen(3003,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
	
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$diraddress='../';

?>

<br><div id="section" style="display: block;">

<?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);

$processids='306,307,308,309,311,312,313'; //access to company process ids
switch ($which)
{
	case 'List':
		 
		$sql='SELECT ProcessTitle,AllowedPerID, ProcessID AS TxnID FROM `permissions_2allprocesses` WHERE ProcessID IN ('.$processids.')';
		$columnnameslist=array('ProcessTitle', 'AllowedPerID');
   
        $editprocess='accesspercompany.php?w=UpdatePermissionToPage&ProcessID='; $editprocesslabel='Edit';
     
		$title='Permission Page'; $formdesc=''; $txnid='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='50%';
	
			include('../backendphp/layout/displayastable.php'); 
		

	
	break;
	
	
	
	case 'UpdatePermissionToPage':
        if (allowedToOpen(3003,'1rtc')){
            if (strpos($processids, $_GET['ProcessID']) !== false) {
                goto proceed;
            } else {
                echo 'Invalid Process ID'; exit();
            }
            proceed:
		
            echo '<br><br>';
            echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions WHERE deptid=20 ORDER BY IDNo','FullName','IDNo','idnolist');
            echo comboBox($link,'SELECT switchid, switchname FROM permissions_00switch UNION ALL SELECT MenuID AS switchid, Menu AS switchname FROM permissions_01level1 WHERE MenuID > 90 ORDER BY switchid','switchname','switchid','onswitchlist');

				$viewonly='';
			
				
				$title1 = 'Update Company Access';
				echo '<title>'.$title1.'</title>';
				
				$processid = intval($_GET['ProcessID']);
				$sqlvalue ="SELECT * FROM `permissions_2allprocesses` WHERE ProcessID=".$processid.";";
				$stmtvalue=$link->query($sqlvalue); $rowvalue=$stmtvalue->fetch();
				
				$ProcessID = $rowvalue['ProcessID'];
				$ProcessTitle = $rowvalue['ProcessTitle'];
				$ProcessDesc = $rowvalue['ProcessDesc'];
				$ProcessAddress = $rowvalue['ProcessAddress'];
				$OnSwitch = $rowvalue['OnSwitch'];
				$AllowedPos = $rowvalue['AllowedPos'];
				$OrderBy = $rowvalue['OrderBy'];
				$AllowedPerID = $rowvalue['AllowedPerID'];
				$path = 'UpdateMenuProcess&ProcessID='.$processid.'';
				$submitlabel = "Update menu";
				
			
				
			
			
			echo '<h2>'.$title1.'</h2><br/>';
			
			
			echo '<div>';
			echo '<div style="float:left;">';
			echo '<form action="accesspercompany.php?w='.$path.'" method="post"><table>';
			
			echo '<tr><td><input name="ProcessID" type="hidden" placeholder="" value="'.$ProcessID.'" /></td></tr>';
			echo '<tr><td>Processs Title:</td><td><input name="ProcessTitle" type="text" size="50" placeholder="" value="'.$ProcessTitle.'" disabled/></td></tr>';
			
		
			
			echo '<tr><td>AllowedPerID:</td><td align="top"><input type="text" name="AllowedPerID" placeholder="E.g. 1001,1002" value="'.$AllowedPerID.'"/> Search: <input type="text"  size="10" list="idnolist"></td></tr>';
			if (allowedToOpen(3003,'1rtc')){
				echo '<tr><td style="padding:10px;"></td></tr><tr><td></td><td align="right"><input type="submit" value="'.$submitlabel.'"/></td></tr>';
			}
			echo '</table></form>';
			echo '</div>';
			
			
				echo '<div style="margin-left:50%">';
				$sql ="SELECT IFNULL(AllowedPerID,0) AS AllowedPerID FROM permissions_2allprocesses WHERE ProcessID=".$ProcessID.";";
				$stmt=$link->query($sql); $rowh=$stmt->fetch();
				
				
				if ($rowh['AllowedPerID']<>0){
					echo '<br><b>Allowed Per IDNo</b><br>';
					
					$sql ="SELECT CONCAT(Nickname,' ',SurName) AS Employee FROM 1employees WHERE IDNo IN (".$rowh['AllowedPerID'].")";
					$stmt=$link->query($sql); $row=$stmt->fetchAll();
					foreach($row AS $res){
						echo '&nbsp; &nbsp; '.$res['Employee'].'<br>';
					}
				}
				echo '</div></div>';
			
		}
		
	break;
	
	
	
	case 'UpdateMenuProcess':
	if (allowedToOpen(3003,'1rtc')){
		$sql='UPDATE `permissions_2allprocesses` SET AllowedPerID='.(empty($_POST['AllowedPerID']) ? 'DEFAULT':'"'.$_POST['AllowedPerID'].'"').' WHERE ProcessID='.intval($_GET['ProcessID']);
		
		$stmt = $link->prepare($sql);
		$stmt->execute();
		header("Location:accesspercompany.php?w=UpdatePermissionToPage&ProcessID=".$_POST['ProcessID']);
	}
	break;
	
}
$link=null; $stmt=null; 

?>
</div> <!-- end section -->
