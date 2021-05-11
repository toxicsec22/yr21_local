<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

// if ((!allowedToOpen(3000,'1rtc')) AND (!allowedToOpen(220,'1rtc'))) { echo 'No permission'; exit; }
if (!allowedToOpen(array(3000,220,2999,100),'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
	if (!isset($_GET['PagePreview'])){
		include_once('../switchboard/contents.php');
		
		
	}

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';

?>

<br><div id="section" style="display: block;">

<?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);
if($which=='ViewOnly'){
	$which='List';
}
if (allowedToOpen(array(3000,2999),'1rtc')){
include_once('../backendphp/layout/linkstyle.php');
if (allowedToOpen(3000,'1rtc')){
echo '<div>';
		echo '<a id=\'link\' href="assignpermission.php?w=ListSwitch">Switch Menu</a> ';
		echo '<a id=\'link\' href="assignpermission.php?w=ListLevel">Level Menu</a> ';
		echo '<a id=\'link\' href="assignpermission.php?w=AddPermissionToPage">Add New Menu Page</a> ';
		echo '<a id=\'link\' href="assignpermission.php">Assign Permission to Page</a> ';
		echo '<a id=\'link\' target="_blank" href="assignpermission.php?w=ViewSwitchboard&PagePreview=1">View Switchboard By Position</a> ';
		echo '<a id=\'link\' target="_blank" href="assignpermission.php?w=ViewSwitchboard&PagePreview=2">View Switchboard By IDNo</a> ';
		echo '&nbsp; &nbsp; &nbsp;<a id=\'link\' href="assignpermission.php?w=AccessPerPosition">Access Per Position (ADD)</a> ';
		echo '<a id=\'link\' href="assignpermission.php?w=AccessPerPositionRemove">Access Per Position (REMOVE)</a> ';
		echo '<a id=\'link\' href="assignpermission.php?w=PrevYrsPermission">Prev Yrs Permission</a> <br><br>';
		echo '<a id=\'link\' href="assignpermission.php?w=DataProtection">Data Protection</a> ';
echo '</div><br/>';



if (in_array($which,array('Add','Edit'))){
   $columnstoadd=array('ProcessTitle', 'ProcessDesc', 'ProcessAddress', 'OnSwitch', 'AllowedPos', 'AllowedPerID', 'OrderBy');
}

if (in_array($which,array('ListSwitch','EditSpecificsSwitch'))){
   $sql='SELECT *, switchid AS TxnID FROM permissions_00switch';
   $columnnameslist=array('switchid', 'switchname', 'switchorder');
   $columnstoadd=array('switchid', 'switchname', 'switchorder');
}

if (in_array($which,array('AddSwitch','EditSwitch'))){
   $columnstoadd=array('switchid', 'switchname', 'switchorder');
}

if (in_array($which,array('ListLevel','EditSpecificsLevel'))){
   $sql='SELECT *, MenuID AS TxnID, switchname FROM permissions_01level1 l JOIN permissions_00switch s ON l.switchid=s.switchid';
   echo comboBox($link,'SELECT switchid, switchname FROM permissions_00switch ORDER BY switchid','switchid','switchname','switchlist');
   $columnnameslist=array('MenuID', 'Menu', 'switchname', 'Remarks', 'OrderBy');
   $columnstoadd=array('MenuID', 'Menu', 'switchname', 'Remarks', 'OrderBy');
}

if (in_array($which,array('AddLevel','EditLevel'))){
   $switchid=comboBoxValue($link,'permissions_00switch','switchname',addslashes($_POST['switchname']),'switchid');
   $columnstoadd=array('MenuID', 'Menu', 'Remarks', 'OrderBy');
}

if (in_array($which,array('ViewSwitchboard','AddPermissionToPage', 'AddNewProgramCommand'))){
  	echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions ORDER BY IDNo','FullName','IDNo','idnolist');
  	echo comboBox($link,'SELECT switchid, switchname FROM permissions_00switch UNION ALL SELECT MenuID AS switchid, Menu AS switchname FROM permissions_01level1 WHERE MenuID > 90 ORDER BY switchid','switchname','switchid','onswitchlist');
}

}

if (in_array($which,array('AddPermissionToPage', 'AddNewProgramCommand'))){
  	$sql0='CREATE TEMPORARY TABLE groupdept AS SELECT p.deptid, d.Department, p.JobLevelNo, JLID, p.Position, p.PositionID FROM attend_0positions p JOIN `1departments` d ON d.deptid=p.deptid JOIN `attend_1joblevel` jl ON jl.JobLevelNo=p.JobLevelNo ORDER BY JobClassNo DESC,jl.JobLevelNo DESC;';
  	
	$stmt=$link->query($sql0);
	$sql0='SELECT DISTINCTROW deptid AS DeptID, Department FROM groupdept;';
	$stmt0=$link->query($sql0); $row0=$stmt0->fetchAll();
}

if (in_array($which,array('AddNewMenuProcess','UpdateMenuProcess'))){
	$new = '';
	if (isset($_POST['allowed'])){
		foreach($_POST['allowed'] as $selected){
			$selected = "".$selected.",";
			$new = $new . ''.$selected.'';
		}
		$trimlastcomma = substr($new,0,-1);
		
	}
	else {
		$trimlastcomma='';
	}
}
if (in_array($which,array('AddNewProgramCommandProcess','UpdateProgramCommandProcess'))){
	$new1 = '';
	$new2 = '';
	
	if (isset($_POST['allowed1'])){
		foreach($_POST['allowed1'] as $selected1){
			$selected1 = "".$selected1.",";
			$new1 = $new1 . ''.$selected1.'';
		}
		$trimlastcomma1 = substr($new1,0,-1);
		
	}
	else {
		$trimlastcomma1='';
	}
	
	if (isset($_POST['allowed2'])){
		foreach($_POST['allowed2'] as $selected2){
			$selected2 = "".$selected2.",";
			$new2 = $new2 . ''.$selected2.'';
		}
		$trimlastcomma2 = substr($new2,0,-1);
		
	}
	else {
		$trimlastcomma2='';
	}
}
}
switch ($which)
{
	case 'List':
	 if (allowedToOpen(array(3000,2999),'1rtc')) {
		 
		$sql='SELECT *, ProcessID AS TxnID FROM `permissions_2allprocesses`';
		$columnnameslist=array('ProcessID','ProcessTitle', 'ProcessDesc', 'ProcessAddress', 'OnSwitch', 'AllowedPos', 'AllowedPerID', 'OrderBy');
   
        $editprocess='assignpermission.php?w=AddPermissionToPage&ProcessID='; $editprocesslabel='Edit';
     
		$title='Permission Page'; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='100%';
		echo '<form action="#" method="POST"><input type="submit" name="Permissions" value="Download Permissions Table"> <input type="submit" name="ShowFunction" value="Show Functions"> <input type="submit" name="ShowProcedure" value="Show Procedures"> <input type="submit" name="ShowView" value="Show Views"> <input type="submit" name="ShowCreateTable" value="Show Tables"></form>';
		if(!isset($_POST['ShowView']) AND !isset($_POST['ShowCreateTable']) AND !isset($_POST['ShowFunction']) AND !isset($_POST['ShowProcedure'])){
			include('../backendphp/layout/displayastable.php'); 
		}
		
		
		if(isset($_POST['Permissions']))
			{
			$connect = connect_db($currentyr.'_1rtc',0);
			$permissionarray=array('permissions_00switch','permissions_01level1','permissions_2allprocesses');		
			 $output = '';
			 foreach($permissionarray as $table)
			 {
			  $show_table_query = "SHOW CREATE TABLE " . $table . "";
			  $statement = $connect->prepare($show_table_query);
			  $statement->execute();
			  $show_table_result = $statement->fetchAll();

			  foreach($show_table_result as $show_table_row)
			  {
			   $output .= "\n\n" . $show_table_row["Create Table"] . ";\n\n";
			  }
			  $select_query = "SELECT * FROM " . $table . "";
			  $statement = $connect->prepare($select_query);
			  $statement->execute();
			  $total_row = $statement->rowCount();

			  for($count=0; $count<$total_row; $count++)
			  {
			   $single_result = $statement->fetch(PDO::FETCH_ASSOC);
			   $table_column_array = array_keys($single_result);
			   $table_value_array = array_values($single_result);
			   $output .= "\nINSERT INTO $table (";
			   $output .= "" . implode(", ", $table_column_array) . ") VALUES (";
			   $output .= "'" . implode("','", $table_value_array) . "');\n";
			  }
			 }
			 
			 
			 $file_name = 'database_backup_on_' . date('y-m-d') . '.sql';
			 
				
				ob_get_clean();
				header('Content-Type: application/octet-stream');
				header("Content-Transfer-Encoding: Binary");
				header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($output, '8bit'): strlen($output)) );
				header("Content-disposition: attachment; filename=\"".$file_name."\""); 
				echo $output; 
				
				$connect=null;
				exit;
				

			}
			
                        if(isset($_POST['ShowFunction'])){
				echo '<br><h2>Functions</h2><br>';
				$connect = connect_db($currentyr.'_1rtc',1);
				
				$sql='SHOW FUNCTION STATUS WHERE `Type` LIKE "FUNCTION" AND Db LIKE "'.$currentyr.'_1rtc";';
				$statement = $connect->prepare($sql);
				$statement->execute();
				$result = $statement->fetchAll();
				
				foreach($result AS $resultview){
					$sql2='SHOW CREATE FUNCTION '.$resultview['Name'];
					$statement2 = $connect->query($sql2);
					//$statement2->execute();
					$result2 = $statement2->fetch();
					$create='CREATE '.strstr(trim($result2['Create Function']),'FUNCTION');
					echo '<b>-- '.$result2['Function'].'</b><br>'.$create.'<br><br><hr><br>';
				}
				
				$connect=null;
				
			}
                        if(isset($_POST['ShowProcedure'])){
				echo '<br><h2>Procedures</h2><br>';
				$connect = connect_db($currentyr.'_1rtc',0);
				
				$sql='SHOW PROCEDURE STATUS WHERE `Type` LIKE "PROCEDURE" AND Db LIKE "'.$currentyr.'_1rtc";';
				$statement = $connect->prepare($sql);
				$statement->execute();
				$result = $statement->fetchAll();
				
				foreach($result AS $resultview){
					$sql2='SHOW CREATE PROCEDURE '.$resultview['Name'];
					$statement2 = $connect->query($sql2);
					$result2 = $statement2->fetch();
                                        // echo $sql2;
                                        // echo $result2['Create'];
					$create='CREATE '.strstr(trim($result2['Create Procedure']),'PROCEDURE');
					echo '<b>-- '.$result2['Procedure'].'</b><br>'.$create.'<br><br><hr><br>';
				}
				
				$connect=null;
				
			}
                        
			if(isset($_POST['ShowView'])){
				echo '<br><h2>Views</h2><br>';
				$connect = connect_db($currentyr.'_1rtc',1);
				
				$sql='SHOW FULL TABLES WHERE `Table_type` LIKE "VIEW";';
				$statement = $connect->prepare($sql);
				$statement->execute();
				$result = $statement->fetchAll();
				
				foreach($result AS $resultview){
					$sql2='SHOW CREATE VIEW '.$resultview['Tables_in_'.$currentyr.'_1rtc'];
					$statement2 = $connect->prepare($sql2);
					$statement2->execute();
					$result2 = $statement2->fetch();
					
					
					echo '<b>-- '.$result2['View'].'</b><br>'.$result2['Create View'].'<br><br><hr><br>';
				}
				
				$connect=null;
				
			}
			if(isset($_POST['ShowCreateTable'])){
				echo '<br><h2>Tables</h2><br>';
				$connect = connect_db($currentyr.'_1rtc',0);
				
				$sql='SHOW FULL TABLES WHERE `Table_type` LIKE "BASE TABLE";';
				$statement = $connect->prepare($sql);
				$statement->execute();
				$result = $statement->fetchAll();
				
				foreach($result AS $resultview){
					$sql2='SHOW CREATE TABLE '.$resultview['Tables_in_'.$currentyr.'_1rtc'];
					$statement2 = $connect->prepare($sql2);
					$statement2->execute();
					$result2 = $statement2->fetch();
					
					
					echo '<b> --'.$result2['Table'].'</b><br>'.$result2['Create Table'].'<br><br><hr><br>';
				}
				
				$connect=null;
				
			}
				

	}
	break;
	
	
	case 'DataProtection':
		if (allowedToOpen(3000,'1rtc')) {
		$sql='select *,ForDB as TxnID from 00dataclosedby;';
		$columnnameslist=array('DataClosedBy','ForDB', 'FixedYrTarget', 'BonusRateBasedTargetReached', 'CurrentYear', 'ValuePerTargetUnit', 'BranchesUnprotected', 'UnprotectedAfterDate');
   
        $editprocess='assignpermission.php?w=EditProtection&ForDB='; $editprocesslabel='Edit';
     
		$title='Data Protection'; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='100%';
		
			include('../backendphp/layout/displayastable.php'); 
		}
	break;

	case 'EditProtection':
	
		if (allowedToOpen(3000,'1rtc')){
			
				$viewonly='';
			
				
				$title1 = 'Edit Data Protection';
				echo '<title>'.$title1.'</title>';
				
				$fordb = intval($_GET['ForDB']);
				$sqlvalue ="SELECT * FROM `00dataclosedby` WHERE ForDB=".$fordb.";";
				$stmtvalue=$link->query($sqlvalue); $rowvalue=$stmtvalue->fetch();
				



				$DataClosedBy = $rowvalue['DataClosedBy'];
				$FixedYrTarget = $rowvalue['FixedYrTarget'];
				$BonusRateBasedTargetReached = $rowvalue['BonusRateBasedTargetReached'];
				$CurrentYear = $rowvalue['CurrentYear'];
				$ValuePerTargetUnit = $rowvalue['ValuePerTargetUnit'];
				$UnprotectedAfterDate = $rowvalue['UnprotectedAfterDate'];
				$BranchesUnprotected = ($rowvalue['BranchesUnprotected']<>''?$rowvalue['BranchesUnprotected']:-100);
				$path = 'UpdateDataProtection&ForDB='.$fordb.'';
				$submitlabel = "Update data";
				
		
			echo '<h2>'.$title1.'</h2><br/>';
			
			
			echo '<div>';
			echo '<div style="float:left;">';
			echo '<form action="assignpermission.php?w='.$path.'" method="post"><table>';
			
			echo '<tr><td>ForDB: </td><td><input name="ForDB" type="text" size="20" placeholder="" value="'.$fordb.'" readonly=true/></td></tr>';
			echo '<tr><td>DataClosedBy:</td><td><input name="DataClosedBy" type="date"  placeholder="" value="'.$DataClosedBy.'" required '.$viewonly.'/></td></tr>';
			echo '<tr><td>FixedYrTarget:</td><td><input name="FixedYrTarget" type="text" size="10" placeholder="" value="'.$FixedYrTarget.'" '.$viewonly.'/></td></tr>';
			echo '<tr><td>BonusRateBasedTargetReached:</td><td><input name="BonusRateBasedTargetReached" type="text" size="10" placeholder="" value="'.$BonusRateBasedTargetReached.'" '.$viewonly.'/></td></tr>';
			echo '<tr><td>CurrentYear:</td><td><input name="CurrentYear" type="text" size="10" placeholder="" value="'.$CurrentYear.'" '.$viewonly.'/></td></tr>';
			echo '<tr><td>UnprotectedAfterDate:</td><td><input name="UnprotectedAfterDate" type="date" size="50" placeholder="" value="'.$UnprotectedAfterDate.'" '.$viewonly.'/></td></tr>';
			echo '<tr><td>ValuePerTargetUnit:</td><td><input name="ValuePerTargetUnit" type="text" size="10" placeholder="" value="'.$ValuePerTargetUnit.'" '.$viewonly.'/></td></tr>';


			echo '<tr><td valign="top">BranchesUnprotected:</td><td>';
			
			echo '<div style="float:left;">';    
			
			

            $sql1='SELECT Branch, BranchNo FROM 1branches ORDER BY Branch';
			
            $stmt1=$link->query($sql1); $row1=$stmt1->fetchAll();
            $branchlist='<table>';
            
            foreach($row1 as $row2){
				$branchlist.='<tr><td><input type="checkbox" name="allowed[]" '.$viewonly.' value="'.$row2['BranchNo'].'"
				'.(in_array($row2['BranchNo'],explode(",",$BranchesUnprotected)) !== false ? 'checked = "checked"': '').';/><td>'.$row2['Branch'].' ('.$row2['BranchNo'].')</td></td>';
            }  
				echo $branchlist.'</table>';
			
        
			echo '</div>';
			echo '</td></tr>';
			
			
			if (allowedToOpen(3000,'1rtc')){
				echo '<tr><td style="padding:10px;"></td></tr><tr><td></td><td align="right"><input type="submit" value="'.$submitlabel.'"/></td></tr>';
			}
			echo '</table></form>';
			echo '</div>';
			
		
				echo '<div style="margin-left:50%"><br><b>Branch(es) Unprotected</b><br>';
				$sql ="SELECT BranchesUnprotected FROM 00dataclosedby WHERE ForDB=".$fordb.";";
				$stmt=$link->query($sql); $rowh=$stmt->fetch();
				
				$sql ="SELECT BranchNo,Branch FROM 1branches WHERE BranchNo IN (".($rowh['BranchesUnprotected']<>''?$rowh['BranchesUnprotected']:-100).") ORDER BY Branch";
				$stmt=$link->query($sql); $row=$stmt->fetchAll();
				foreach($row AS $res){
					echo '&nbsp; &nbsp; '.$res['Branch'].'<br>';
				}
				
				echo '<br><form action="assignpermission.php?w=SetUnprotectedtoNull&ForDB='.$fordb.'" method="POST"><input type="submit" value="Set to NULL" name="SetNull" OnClick="return confirm(\'Are you sure?\');"></form></div></div>';
			
		}
		
	break;

	case 'SetUnprotectedtoNull':
		if (allowedToOpen(3000,'1rtc')){
		$sql='UPDATE `00dataclosedby` SET `BranchesUnprotected` = NULL, `UnprotectedAfterDate` = NULL WHERE ForDB='.intval($_GET['ForDB']);

			$stmt = $link->prepare($sql);
			$stmt->execute();
		}
			header("Location:assignpermission.php?w=EditProtection&ForDB=".$_GET['ForDB']);

	break;

	case 'UpdateDataProtection':

		if (allowedToOpen(3000,'1rtc')){
			$new = '';
			if (isset($_POST['allowed'])){
				foreach($_POST['allowed'] as $selected){
					$selected = "".$selected.",";
					$new = $new . ''.$selected.'';
				}
				$trimlastcomma = substr($new,0,-1);
				
			}
			else {
				$trimlastcomma='';
			}
		
			$columnnameslist=array('DataClosedBy','ForDB', '', '', '', '', 'BranchesUnprotected', 'UnprotectedAfterDate');

			$sql='UPDATE `00dataclosedby` SET ForDB="'.$_POST['ForDB'].'",DataClosedBy="'.$_POST['DataClosedBy'].'", FixedYrTarget="'.$_POST['FixedYrTarget'].'", BonusRateBasedTargetReached="'.$_POST['BonusRateBasedTargetReached'].'", CurrentYear="'.$_POST['CurrentYear'].'", BranchesUnprotected='.(empty($trimlastcomma) ? 'DEFAULT':'"'.$trimlastcomma.'"').', ValuePerTargetUnit="'.$_POST['ValuePerTargetUnit'].'", UnprotectedAfterDate="'.$_POST['UnprotectedAfterDate'].'" WHERE ForDB='.intval($_GET['ForDB']);
			// echo $sql; exit();

			$stmt = $link->prepare($sql);
			$stmt->execute();
			header("Location:assignpermission.php?w=EditProtection&ForDB=".$_POST['ForDB']);
		}
		break;
	
	case 'AddPermissionToPage':
	
		if (allowedToOpen(array(3000,2999),'1rtc')){
			if (allowedToOpen(2999,'1rtc')){
				$viewonly='disabled';
			} else {
				$viewonly='';
			}
			if (isset($_GET['ProcessID'])){
				
				$title1 = 'Update Menu Process';
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
				
				$sql11 ="SELECT CONCAT(Nickname,' ',Surname) AS Name FROM `1employees` e JOIN `permissions_2allprocesses` ap ON (FIND_IN_SET(e.IDNo,ap.AllowedPerID)) WHERE ProcessID=".$processid.";";
				
				$stmt11=$link->query($sql11); 
				$SPIDNo = '';
				while($row11 = $stmt11->fetch()) {
					$SPIDNo = $SPIDNo . '<br/>' . $row11['Name'];
				}
				
			}
			else {
				$title1 = 'Add New Menu Process';
				echo '<title>'.$title1.'</title>';
				
				$ProcessID='';
				$ProcessTitle='';
				$ProcessDesc='';
				$ProcessAddress='';
				$OnSwitch='';
				$AllowedPos=99;
				$OrderBy='';
				$AllowedPerID='';
				$path='AddNewMenuProcess';
				$submitlabel = "Add new menu";
				$SPIDNo='';
			}
			echo '<h2>'.$title1.'</h2><br/>';
			
			
			echo '<div>';
			echo '<div style="float:left;">';
			echo '<form action="assignpermission.php?w='.$path.'" method="post"><table>';
			
			echo '<tr><td>Processs ID:</td><td><input name="ProcessID" type="text" size="20" placeholder="" value="'.$ProcessID.'" required '.$viewonly.'/></td></tr>';
			echo '<tr><td>Processs Title:</td><td><input name="ProcessTitle" type="text" size="50" placeholder="" value="'.$ProcessTitle.'" required '.$viewonly.'/></td></tr>';
			echo '<tr><td>Processs Description:</td><td><input name="ProcessDesc" type="text" size="50" placeholder="" value="'.$ProcessDesc.'" required '.$viewonly.'/></td></tr>';
			echo '<tr><td>Processs Address:</td><td><input name="ProcessAddress" type="text" size="50" placeholder="" value="'.$ProcessAddress.'" required '.$viewonly.'/></td></tr>';
			echo '<tr><td>OnSwitch:</td><td><input name="OnSwitch" type="text" size="10" placeholder="" value="'.$OnSwitch.'" list="onswitchlist" required '.$viewonly.'/></td></tr>';
			echo '<tr><td>OrderBy:</td><td><input name="OrderBy" type="number" size="10" placeholder="" value="'.$OrderBy.'" required '.$viewonly.'/></td></tr>';
			echo '<tr><td valign="top">AllowedPos:</td><td>';
			
			echo '<div style="float:left;">';    
			foreach($row0 as $pos){
            echo '<h4>'.$pos['Department'].'</h4>';
            $sql1='SELECT Position, PositionID FROM groupdept WHERE DeptID='.$pos['DeptID'].' GROUP BY PositionID ORDER BY JLID DESC';
			
            $stmt1=$link->query($sql1); $row1=$stmt1->fetchAll();
            $deptlist='<table>';
            
            foreach($row1 as $row2){
				$deptlist.='<tr><td><input type="checkbox" name="allowed[]" '.$viewonly.' value="'.$row2['PositionID'].'"
				'.(in_array($row2['PositionID'],explode(",",$AllowedPos)) !== false ? 'checked = "checked"': '').';/><td>'.$row2['Position'].' ('.$row2['PositionID'].')</td></td>';
            }  
				echo $deptlist.'</table>';
			}
        
			echo '</div>';
			echo '</td></tr>';
			
			echo '<tr><td>AllowedPerID:<font size="2pt">'.$SPIDNo.'</font></td><td align="top"><input type="text" name="AllowedPerID" placeholder="E.g. 1001,1002" '.$viewonly.' value="'.$AllowedPerID.'"/> Search: <input type="text" '.$viewonly.' size="10" list="idnolist"></td></tr>';
			if (allowedToOpen(3000,'1rtc')){
				echo '<tr><td style="padding:10px;"></td></tr><tr><td></td><td align="right"><input type="submit" value="'.$submitlabel.'"/></td></tr>';
			}
			echo '</table></form>';
			echo '</div>';
			
			if($which=='AddPermissionToPage'){
				echo '<div style="margin-left:50%"><br><b>Allowed Position(s)</b><br>';
				$sql ="SELECT AllowedPos,IFNULL(AllowedPerID,0) AS AllowedPerID FROM permissions_2allprocesses WHERE ProcessID=".$ProcessID.";";
				$stmt=$link->query($sql); $rowh=$stmt->fetch();
				
				$sql ="SELECT PositionID,Position FROM attend_0positions p JOIN attend_1joblevel jl ON jl.JobLevelNo=p.JobLevelNo WHERE PositionID IN (".$rowh['AllowedPos'].") ORDER BY deptid,JLID DESC";
                                
				$stmt=$link->query($sql); $row=$stmt->fetchAll();
				foreach($row AS $res){
					echo '&nbsp; &nbsp; '.$res['Position'].'<br>';
				}
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
		}
		
	break;
	
	
	case 'AddNewMenuProcess':
	if (allowedToOpen(3000,'1rtc')){

		$sql='INSERT INTO `permissions_2allprocesses` (ProcessID,ProcessTitle,ProcessDesc,ProcessAddress,OnSwitch,AllowedPos,AllowedPerID,OrderBy) VALUES ("'.$_POST['ProcessID'].'","'.$_POST['ProcessTitle'].'","'.$_POST['ProcessDesc'].'","'.$_POST['ProcessAddress'].'","'.$_POST['OnSwitch'].'",'.(empty($trimlastcomma) ? 'DEFAULT':'"'.$trimlastcomma.'"').','.(empty($_POST['AllowedPerID']) ? 'DEFAULT':'"'.$_POST['AllowedPerID'].'"').',"'.$_POST['OrderBy'].'")';
		// echo $sql; exit();
		$stmt = $link->prepare($sql);
		$stmt->execute();
		
		header("Location:assignpermission.php?w=AddPermissionToPage&ProcessID=".$_POST['ProcessID']);
	}
	break;
	
	
	case 'UpdateMenuProcess':
	if (allowedToOpen(3000,'1rtc')){
	
		$sql='UPDATE `permissions_2allprocesses` SET ProcessID="'.$_POST['ProcessID'].'",ProcessTitle="'.$_POST['ProcessTitle'].'", ProcessDesc="'.$_POST['ProcessDesc'].'", ProcessAddress="'.$_POST['ProcessAddress'].'", OnSwitch="'.$_POST['OnSwitch'].'", AllowedPos='.(empty($trimlastcomma) ? 'DEFAULT':'"'.$trimlastcomma.'"').', AllowedPerID='.(empty($_POST['AllowedPerID']) ? 'DEFAULT':'"'.$_POST['AllowedPerID'].'"').', OrderBy="'.$_POST['OrderBy'].'" WHERE ProcessID='.intval($_GET['ProcessID']);
		
		$stmt = $link->prepare($sql);
		$stmt->execute();
		header("Location:assignpermission.php?w=AddPermissionToPage&ProcessID=".$_POST['ProcessID']);
	}
	break;
	
	case 'ViewSwitchboard':
	if (allowedToOpen(3000,'1rtc')){
			echo '<title>View Switchboard</title>';
			echo comboBox($link,'SELECT PositionID, Position FROM attend_0positions ORDER BY Position','Position','PositionID','positionlist');
			
			$pagesearch = $_GET['PagePreview'];
			echo '<form action="#" method="POST">';
			if ($pagesearch==1){
				echo 'Position to View: <input type="text" name="PositionToView" list="positionlist"> ';
			}
			else {
				echo 'Employee: <input type="text" name="SBIDNo" list="idnolist"/> ';
				$withdashboard=1;
			}
			echo '<input type="submit" name="ViewSB" value="View Switchboard"/></form><br/><br/>';
			
			


		if (!isset($_POST['ViewSB']))
		{
			//echo '<body style="background-color:#85e085;"></body>';
			goto nosb;
		}

		if ($pagesearch==1){
			$_POST['IDNo']=NULL;
			$name ='';
		}
		else if ($pagesearch==2){
			$sqlsearch='SELECT Fullname, IDNo, PositionID FROM attend_30currentpositions WHERE IDNo='.$_POST['SBIDNo'].'';
			
			$stmtsearch=$link->query($sqlsearch);
			$resultsearch = $stmtsearch->fetch();
			$name = $resultsearch['FullName'];
			$_POST['PositionToView'] = $resultsearch['PositionID'];
			$_POST['IDNo'] = $resultsearch['IDNo'];
		}
		
		
		
		$sqlalloptions='CREATE TEMPORARY TABLE `SwitchboardItems` AS
		SELECT s.switchid AS SwitchID, switchname AS Switch, 0 AS MenuID, 0 AS Menu, ProcessID, ProcessTitle, ProcessAddress, OnSwitch, ap.OrderBy, 0 AS WithSub FROM `permissions_2allprocesses` ap JOIN `permissions_00switch` s on s.switchid=ap.OnSwitch WHERE OnSwitch<>0 AND ((FIND_IN_SET('.$_POST['PositionToView'].',`AllowedPos`)) OR (FIND_IN_SET('.(isset($_POST['IDNo'])?$_POST['IDNo']:0).',`AllowedPerID`)))
			UNION SELECT s.switchid AS SwitchID, switchname AS Switch, MenuID, Menu, MenuID, Menu, "#" AS ProcessAddress, l1.switchid, l1.OrderBy, 1 AS WithSub FROM `permissions_2allprocesses` ap RIGHT JOIN `permissions_01level1` l1 ON l1.MenuID=ap.OnSwitch JOIN `permissions_00switch` s on s.switchid=l1.switchid WHERE OnSwitch<>0 AND ((FIND_IN_SET('.$_POST['PositionToView'].',`AllowedPos`)) OR (FIND_IN_SET('.(isset($_POST['IDNo'])?$_POST['IDNo']:0).',`AllowedPerID`))) 
		UNION SELECT s.switchid AS SwitchID, switchname AS Switch, MenuID, Menu, ProcessID, ProcessTitle, ProcessAddress, OnSwitch, ap.OrderBy, 1 AS WithSub FROM `permissions_2allprocesses` ap JOIN `permissions_01level1` l1 ON l1.MenuID=ap.OnSwitch JOIN `permissions_00switch` s on s.switchid=l1.switchid WHERE OnSwitch<>0 AND ((FIND_IN_SET('.$_POST['PositionToView'].',`AllowedPos`)) OR (FIND_IN_SET('.(isset($_POST['IDNo'])?$_POST['IDNo']:0).',`AllowedPerID`))) ORDER BY OrderBy;';

		$stmt=$link->prepare($sqlalloptions); $stmt->execute();

		$sqlmenugroup='SELECT si.SwitchID, Switch FROM `SwitchboardItems` si JOIN `permissions_00switch` s ON s.switchid=si.SwitchID GROUP BY si.SwitchID ORDER BY switchorder;';

		$stmt=$link->query($sqlmenugroup); $resultgroup=$stmt->fetchAll();

		include('../switchboard/switchstylehome.php') ?>

		<div id="wrapper">
		<?php
		echo '<h3>'.$name.'<br/>'.comboBoxValue($link,'attend_0positions','PositionID',$_POST['PositionToView'],'Position') .'</h3><br/>';
		if (isset($withdashboard) AND $withdashboard==1){
			include_once('../graphs/dashboardgraphs.php');
		}
echo '<br>';
		$switch='<div style="float:right; "><ul id="navmenu"><li><ul id="navmenu"><li><a href="/yr'.substr($lastyr,2,2).'/index.php">'.$lastyr.' Data</a></li>'
                        . '<li><a href="/yr'.substr($nextyr,2,2).'/index.php">'.$nextyr.' Data</a></li>';
                $switch.='<li><a href="/logout.php">Logout</a></li>';
                $switch.='</ul></div><ul id="navmenu">';   

		foreach ($resultgroup as $group){
			$switch=$switch.'<li><a href="#">'.$group['Switch'].'</a><ul class="sub1">';
			
			$sqlmenu='SELECT MenuID, Menu, ProcessID, ProcessTitle, ProcessAddress, OrderBy, WithSub FROM `SwitchboardItems` si WHERE OnSwitch='.$group['SwitchID'].' ORDER BY OrderBy;';
			
			$stmt=$link->query($sqlmenu);    $result=$stmt->fetchAll();
			
			foreach ($result as $command){
					$commandlink=(is_integer(strrpos($command['ProcessAddress'],'action_token'))?$command['ProcessAddress'].$_SESSION['action_token']:$command['ProcessAddress']);
					
					$switch=$switch."<li><a href='".$commandlink."'>".$command['ProcessTitle']."</a>";
					if ($command['WithSub']==1){
						   $sqlmenusub='SELECT ProcessID, ProcessTitle, ProcessAddress, OrderBy FROM `SwitchboardItems` si WHERE OnSwitch='.$command['MenuID'].' ORDER BY OrderBy;';
							$stmt=$link->query($sqlmenusub);
							$resultsub=$stmt->fetchAll();
							$switch=$switch.'<ul class="sub2">';
							foreach ($resultsub as $commandsub){
								$commandlinksub=(is_integer(strrpos($commandsub['ProcessAddress'],'action_token'))?$commandsub['ProcessAddress'].$_SESSION['action_token']:$commandsub['ProcessAddress']);
								
								$switch=$switch.'<li><a href="'.$commandlinksub.'">'.$commandsub['ProcessTitle'].'</a></li>';
							}
							$switch=$switch.'</ul>';
					}
					$switch=$switch.'</li>';
					
			}
			
			$switch=$switch.'</ul></li>';
		}

		echo $switch;

		echo '</ul></div>';

		nosb:
	}
	break;
	
	
	case 'ListSwitch':
	if (allowedToOpen(3000,'1rtc')) {
		$title='Switch'; 
                
                $formdesc='Add Switch Menu.';
				$method='post';
				$columnnames=array(
				array('field'=>'switchid','type'=>'number','size'=>20,'required'=>true),
				array('field'=>'switchname','type'=>'text','size'=>25,'required'=>true),
				array('field'=>'switchorder','type'=>'number','size'=>20,'required'=>true));
							
		$action='assignpermission.php?w=AddSwitch'; $fieldsinrow=4; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
		
                $delprocess='assignpermission.php?w=DeleteSwitch&switchid=';
                $editprocess='assignpermission.php?w=EditSpecificsSwitch&switchid='; $editprocesslabel='Edit';
     
				
		
		
		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php'); 
	}
	break;
	
	case 'AddSwitch':
	if (allowedToOpen(3000,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		$fieldarr = array();
		$columnstoadd = array_diff($columnstoadd,array('switchorder'));
		
		foreach ($columnstoadd as $field) {$sql.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; $fieldarr[] = $_POST[$field]; }
		
		$sql='INSERT INTO `permissions_00switch` SET '.$sql.' switchorder='.$_POST['switchorder'].'';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	
	}
	break;
	
	case 'EditSpecificsSwitch':
        if (!allowedToOpen(3000,'1rtc')) { header('Location:assignpermission.php?denied=true'); }
		$title='Edit Specifics';
		$txnidname=intval($_GET['switchid']);

		$sql=$sql.' WHERE switchid='.$txnidname;
		$columnstoedit=$columnstoadd;
		
		$columnnames=$columnnameslist;
		
		$editprocess='assignpermission.php?w=EditSwitch&switchid='.$txnidname;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break;
	
	case 'EditSwitch':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(3000,'1rtc')){
		$txnidname = intval($_GET['switchid']);
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `permissions_00switch` SET '.$sql.' switchid='.$txnidname.' WHERE switchid='.$txnidname;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		}
		header("Location:assignpermission.php?w=ListSwitch");
		
    break;
	
	case 'DeleteSwitch':
	if (allowedToOpen(3000,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `permissions_00switch` WHERE switchid='.intval($_GET['switchid']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
	}
	header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
	case 'ListLevel';
	$title='Level'; 
	if (allowedToOpen(3000,'1rtc')) {
		$formdesc='Add Level Menu.';
		$method='post';
		$columnnames=array(
		array('field'=>'MenuID','type'=>'number','size'=>20,'required'=>true),
		array('field'=>'Menu','type'=>'text','size'=>25,'required'=>true),
		array('field'=>'switchname','type'=>'text','size'=>20,'required'=>true, 'list'=>'switchlist'),
		array('field'=>'Remarks','type'=>'text','size'=>20,'required'=>false),
		array('field'=>'OrderBy','type'=>'number','size'=>25,'required'=>true));
					
		$action='assignpermission.php?w=AddLevel'; $fieldsinrow=3; $liststoshow=array();

		include('../backendphp/layout/inputmainform.php');

		$delprocess='assignpermission.php?w=DeleteLevel&MenuID=';
		$editprocess='assignpermission.php?w=EditSpecificsLevel&MenuID='; $editprocesslabel='Edit';

		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='70%';
		
		include('../backendphp/layout/displayastable.php');
	}
	break;
	
	case 'AddLevel':
	if (allowedToOpen(3000,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		foreach ($columnstoadd as $field) {$sql.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		
		$sql='INSERT INTO `permissions_01level1` SET '.$sql.' switchid='.$switchid.'';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	
	}
	break;
	
	case 'EditSpecificsLevel':
    if (!allowedToOpen(3000,'1rtc')) { header('Location:assignpermission.php?denied=true'); }
		$title='Edit Specifics';
		$txnidname=intval($_GET['MenuID']);

		$sql='SELECT l.*, s.switchname, l.MenuID AS TxnID FROM permissions_00switch AS s JOIN permissions_01level1 AS l ON s.switchid = l.switchid  '
                        . ' WHERE MenuID='.$txnidname;
						
		
		$columnnameslist=array('MenuID', 'Menu', 'switchname', 'Remarks', 'OrderBy');
        $columnstoadd=array('MenuID', 'Menu', 'switchname', 'Remarks', 'OrderBy');
		
		$columnstoedit=$columnstoadd;
		$columnnames=$columnnameslist;
		
		$columnswithlists=array('switchname');
		$listsname=array('switchname'=>'switchlist');
		
		$columnnames=$columnnameslist;
		
		$editprocess='assignpermission.php?w=EditLevel&MenuID='.$txnidname;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break; //End of Case EditSpecifics
	
	case 'EditLevel':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	if (allowedToOpen(3000,'1rtc')){
		$txnidname = intval($_GET['MenuID']);
		$sql='';
		
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `permissions_01level1` SET '.$sql.' switchid='.$switchid.' WHERE MenuID='.$txnidname;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
	
	}
	header("Location:assignpermission.php?w=ListLevel");
		
    break;
	
	case 'DeleteLevel':
	if (allowedToOpen(3000,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `permissions_01level1` WHERE MenuID='.intval($_GET['MenuID']);
		
		$stmt=$link->prepare($sql); $stmt->execute();
	}
	header("Location:".$_SERVER['HTTP_REFERER']);
    break;



	case 'AccessPerPosition':
	case 'AccessPerPositionRemove':
	if (allowedToOpen(array(3000,220),'1rtc')){
		if($_GET['w']=='AccessPerPosition'){
			$addtitle=' (ADD)';
			$disabledadd='disabled';
			$disabledremove='';
			$loc='AssignAccessPerPosition';
			$buttonval='Add';
		} else {
			$addtitle=' (REMOVE)';
			$disabledadd='';
			$disabledremove='disabled';
			$loc='AssignAccessPerPositionRemove';
			$buttonval='Remove';
		}
		$title='Access Per Position'.$addtitle;
			echo '<title>'.$title.'</title>';
			
			echo comboBox($link,'SELECT PositionID, Position FROM attend_0positions ORDER BY Position','Position','PositionID','positionlist');
			
			
			if(!isset($_GET['Request'])){
				echo '<h3>'.$title.'</h3>';
				echo '<form action="#" method="POST">';
				echo 'Position: <input type="text" name="PositionToView" list="positionlist"> ';
				echo '<input type="submit" name="ViewAccess" value="View Access"/></form><br/><br/>';
			} else {
				echo '<h3>Requested Access</h3>';
			}
			
			if((isset($_POST['ViewAccess'])) OR (isset($_GET['Request']))){
				if(isset($_GET['Request'])){
					$_POST['PositionToView']=intval($_GET['ForPositionID']);
					$sqlreq='SELECT ProcessIDs FROM approvals_systempermission WHERE ForPositionID='.$_POST['PositionToView'].'';
					$stmtreq=$link->query($sqlreq); $req=$stmtreq->fetch();
				}
				echo '<h3>'.comboBoxValue($link,'attend_0positions','PositionID',$_POST['PositionToView'],'Position') .'</h3><br/>';
				
				$sql='SELECT AllowedPerID,ProcessID,ProcessTitle, ProcessAddress, OnSwitch FROM permissions_2allprocesses WHERE (AllowedPerID IS NOT NULL AND AllowedPerID<>"");';
				$stmt=$link->query($sql); $row=$stmt->fetchAll();
				
				echo '<h4>AllowedPos</h4>';
				$sql0='SELECT PositionID FROM attend_0positions WHERE deptid=(SELECT deptid FROM attend_0positions WHERE PositionID='.$_POST['PositionToView'].');';
				$stmt0=$link->query($sql0); $row0=$stmt0->fetchAll();
				$addlc='';
				foreach ($row0 AS $field){
					$addlc.='FIND_IN_SET('.$field['PositionID'].',AllowedPos) OR ';
				}
				$addlc = substr($addlc,0,-3); 
				
				
				
				$sql='SELECT ProcessID, IF(FIND_IN_SET('.$_POST['PositionToView'].',AllowedPos),1,0) AS Ok,AllowedPos, ProcessTitle, ProcessAddress, OnSwitch FROM permissions_2allprocesses WHERE '.$addlc.' UNION SELECT ProcessID,1,"",ProcessTitle,ProcessAddress,OnSwitch FROM permissions_2allprocesses WHERE FIND_IN_SET(ProcessID,(SELECT ProcessIDs FROM approvals_systempermission WHERE ForPositionID='.$_GET['ForPositionID'].')) ORDER BY ProcessAddress;';

				// $sql='SELECT ProcessID, IF(FIND_IN_SET('.$_POST['PositionToView'].',AllowedPos),1,0) AS Ok,AllowedPos, ProcessTitle, ProcessAddress, OnSwitch FROM permissions_2allprocesses WHERE '.$addlc.' ORDER BY ProcessAddress;';
				$stmt=$link->query($sql); $row1=$stmt->fetchAll();
				
				
				$accesslist='<form action="assignpermission.php?w='.$loc.'" method="POST"><table>';
				$pid='';
				if(!isset($_GET['Request'])){
					foreach($row1 as $row2){
						$accesslist.='<tr '.($row2['OnSwitch']==0?'style="color:green;font-size:11pt;"':'').'><td><input type="checkbox" name="allowedaccess[]" value="'.$row2['ProcessID'].'" 
						'.(((in_array($_POST['PositionToView'],explode(",",$row2['AllowedPos'])) !== false)) ? ' '.$disabledadd.' checked = "checked"': ''.$disabledremove.'').' /><td>'.$row2['ProcessTitle'].'</td><td>'.$row2['ProcessAddress'].'</td></tr>';
							$pid.=$row2['ProcessID'].",";
					}  
				} else {
					foreach($row1 as $row2){
						$accesslist.='<tr '.($row2['OnSwitch']==0?'style="color:green;font-size:11pt;"':'').'><td><input type="checkbox" name="allowedaccess[]" value="'.$row2['ProcessID'].'" 
						'.(((in_array($row2['ProcessID'],explode(",",$req['ProcessIDs'])) !== false) ) ? ' checked = "checked" '.(((in_array($_POST['PositionToView'],explode(",",$row2['AllowedPos'])) !== false)) ?'disabled':'').'': (((in_array($_POST['PositionToView'],explode(",",$row2['AllowedPos'])) !== false)) ?'checked="checked" disabled':'')).' /><td>'.$row2['ProcessTitle'].'</td><td>'.$row2['ProcessAddress'].'</td></tr>';
							$pid.=$row2['ProcessID'].",";
					}  
				}
				echo $accesslist.'</table><input type="hidden" name="PosID" value="'.$_POST['PositionToView'].'"><input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">'.(allowedToOpen(3000,'1rtc')?'<input type="submit" value="'.$buttonval.' Access">':'').'</form>';
				echo '<br>'.(allowedToOpen(3000,'1rtc')?'<form action="assignpermission.php?w=Decline" method="POST"><input type="hidden" name="PosID" value="'.$_GET['ForPositionID'].'"><input style="color:red;" type="submit" value="Decline Request" OnClick="return confirm(\'Really delete this?\');"></form>':'');
				
			}
	}
	
	break;
	
	case 'Decline':
	if(!allowedToOpen(3000,'1rtc')){ echo 'No Permission'; exit(); }
	
	$sqldel='DELETE FROM approvals_systempermission WHERE ForPositionID='.$_POST['PosID'];
	$stmtdel = $link->prepare($sqldel);
	$stmtdel->execute();
		echo '<h3>Successfully Declined.</h3>';
	break;
	
	case 'AssignAccessPerPosition':
	
	if(!allowedToOpen(3000,'1rtc')){ echo 'No Permission'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
			foreach ($_REQUEST['allowedaccess'] AS $allowedprocessid){
				
				
				$sql='SELECT ProcessID FROM permissions_2allprocesses WHERE FIND_IN_SET('.$_POST['PosID'].',AllowedPos) AND ProcessID='.$allowedprocessid;
				$stmt=$link->query($sql); 
				
				if ($stmt->rowCount()==0){
					$sqlupdate='UPDATE permissions_2allprocesses SET AllowedPos=CONCAT(AllowedPos,",'.$_POST['PosID'].'") WHERE ProcessID='.$allowedprocessid;
					$stmtupdate = $link->prepare($sqlupdate);
					$stmtupdate->execute();
				} 
				
				
			} 
			
			$sqldel='DELETE FROM approvals_systempermission WHERE ForPositionID='.$_POST['PosID'];
			$stmtdel = $link->prepare($sqldel);
			$stmtdel->execute();
			
			echo '<h3>Updated Successfully.</h3>';
		
	break;
	
	
	case 'AssignAccessPerPositionRemove':
	
	if(!allowedToOpen(3000,'1rtc')){ echo 'No Permission'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$processidtoremove='';
			foreach ($_REQUEST['allowedaccess'] AS $allowedprocessid){
				
				$processidtoremove.=$allowedprocessid.",";
				
			}
			
			$sql='SELECT GROUP_CONCAT(ProcessID) AS ProcessIDs FROM permissions_2allprocesses WHERE FIND_IN_SET('.$_POST['PosID'].',AllowedPos) ORDER BY ProcessAddress;';
				
			$stmt=$link->query($sql); $row1=$stmt->fetch();
			$processidtoremove = substr($processidtoremove,0,-1);
			
			
			$sql='SELECT * FROM permissions_2allprocesses WHERE ProcessID IN ('.$row1['ProcessIDs'].') AND ProcessID NOT IN ('.$processidtoremove.')';
			$stmt=$link->query($sql); $row=$stmt->fetchAll();
			
			foreach($row AS $field){
				$arr = array_diff(explode(",",$field['AllowedPos']),array($_POST["PosID"]));
				$sql1='UPDATE `permissions_2allprocesses` SET `AllowedPos`='.(!empty($arr)?"'".implode(',',$arr)."'":'NULL').' WHERE ProcessID='.$field['ProcessID'].';';
				
				$stmt=$link->prepare($sql1); $stmt->execute();
			} 
			
			echo '<h3>Updated Successfully.</h3>';
		
	break;
        
        case 'OpenSystemTonight':
	
                if (!allowedToOpen(array(100),'1rtc')){ echo 'No permission'; exit();}	
                
			
				$title1 = 'Open System Tonight';
				echo '<title>'.$title1.'</title>';
				
				$processid = 247;
				$sqlvalue ="SELECT * FROM `permissions_2allprocesses` WHERE ProcessID=".$processid.";";
				$stmtvalue=$link->query($sqlvalue); $rowvalue=$stmtvalue->fetch();
				$AllowedPerID=$rowvalue['AllowedPerID'];
				
				
				$sql11 ="SELECT CONCAT(Nickname,' ',Surname) AS Name FROM `1employees` e JOIN `permissions_2allprocesses` ap ON (FIND_IN_SET(e.IDNo,ap.AllowedPerID)) WHERE ProcessID=".$processid.";";
				
				$stmt11=$link->query($sql11); 
								
			
			echo '<h2>'.$title1.'</h2><br/>';
			echo 'Permissions will revert at 12:30 a.m. PST<br/><br/>';
			
			echo '<div>';
			echo '<div style="float:left; margin-left: 10%;">';
			echo '<form action="assignpermission.php?w=OpenSystemTonight" method="post"><table>';
			
		
                        $sql0='SELECT IDNo, FullName FROM attend_30currentpositions WHERE DeptHeadPositionID='.$_SESSION['&pos'].' ORDER BY JLID DESC, FullName;';
				$stmt0=$link->query($sql0); $row0=$stmt0->fetchAll();
				
			
            $emplist='<table>'; $alllist=array();
            
            foreach($row0 as $row2){
				$emplist.='<tr><td><input type="checkbox" name="allowed[]" value="'.$row2['IDNo'].'"
				'.(in_array($row2['IDNo'],explode(",",$AllowedPerID)) !== false ? 'checked = "checked"': '').';/><td>'.$row2['FullName'].'</td></td>';
                                $alllist[]=$row2['IDNo'];
            }  
				echo $emplist.'</table>';
			//echo '</div>';
			
			if (allowedToOpen(100,'1rtc')){
				echo '<tr><td style="padding:10px;"></td></tr><tr><td></td><td align="right"><br /><input type="submit" value="Set Permissions"/></td></tr>';
			}
			echo '</table></form>';
			echo '</div>';
			
			if(!isset($_POST['allowed'])){ 
				
				if ($AllowedPerID<>0 or !empty($AllowedPerID)){
					echo '<div style="margin-left: 50%;"><br><b>Allowed Per IDNo</b><br>';
					
					$sql ='SELECT FullName AS Employee FROM attend_30currentpositions WHERE DeptHeadPositionID='.$_SESSION['&pos'].' AND IDNo IN ('.$rowvalue['AllowedPerID'].') ';
					$stmt=$link->query($sql); $row=$stmt->fetchAll();
					foreach($row AS $res){
						echo '&nbsp; &nbsp; '.$res['Employee'].'<br>';
					}
				}
				echo '</div>';
			} else {
                            
                            // first remove all of dept
                            $sql='UPDATE `permissions_2allprocesses` SET AllowedPerID="'.(implode(',',(array_diff(explode(",",$AllowedPerID),$alllist)))).'" WHERE ProcessID=247';
                            $stmt = $link->prepare($sql); $stmt->execute(); 
                            
                            // add allowed in dept -- it is not allowing an empty array
                            $sqlvalue ="SELECT * FROM `permissions_2allprocesses` WHERE ProcessID=".$processid.";";
				$stmtvalue=$link->query($sqlvalue); $rowvalue=$stmtvalue->fetch();
				$AllowedPerID=$rowvalue['AllowedPerID']; 
                            $sql='UPDATE `permissions_2allprocesses` SET AllowedPerID="'.(implode(',',(array_merge(explode(",",$AllowedPerID),$_POST['allowed'])))).'" WHERE ProcessID=247';
		
                            $stmt = $link->prepare($sql); $stmt->execute();
                            
                            
		header("Location:assignpermission.php?w=OpenSystemTonight");
                        }
		
		
	break;
	
	case 'PrevYrsPermission':
	$title='Give Permission to Prev Years';
	echo '<title>'.$title.'</title>';
	if(isset($_GET['Year'])){
		echo '<div style="color:green;background-color:white;text-align:center;">Successfully Copied (Year '.$_GET['Year'].'). Pls check to confirm.</div>';
	}
	echo '<h3>'.$title.'</h3>';
	echo comboBox($link,'SELECT PositionID, Position FROM attend_0positions ORDER BY Position','Position','PositionID','positionlist');
	
	$oplist=''; $startyr=2015;
	while($startyr<$currentyr){
		$oplist.='&nbsp; &nbsp; &nbsp; <input type="checkbox" name="Yr'.$startyr.'" value="'.$startyr.'"> '.$startyr;
		$startyr++;
	}
	echo '<form action="assignpermission.php?w=PrPrevYrsPermission" method="POST" autocomplete="off">Copy From <input type="text" name="PosFrom" list="positionlist" required> Copy To <input type="text" name="PosTo" list="positionlist" required> '.$oplist.' &nbsp; &nbsp; <input type="submit" value="Copy Permission" name="btnCopyPermission"></form>';
	
	break;
	
	case 'PrPrevYrsPermission':
	$link=connect_db($currentyr.'_1rtc',1);
	$copyfrom=$_POST['PosFrom'];
	$copyto=$_POST['PosTo'];
	
	$startyr=2015; $yearsupdated='';
	while($startyr<$currentyr){
		if(isset($_POST['Yr'.$startyr.''])){
			if (in_array($_POST['Yr'.$startyr.''],array(2015,2016,2017))){
				$sql="UPDATE `".$startyr."_1rtc`.`permissions_1commands` SET `permitted`=REPLACE(`permitted`, '\'".$copyfrom."\'', '\'".$copyfrom."\',\'".$copyto."\'') where `permitted` like '%\'".$copyfrom."\'%' AND `permitted` NOT like '%\'".$copyto."\'%';";
				$stmt=$link->prepare($sql); $stmt->execute();
				$sql="UPDATE `".$startyr."_1rtc`.`permissions_1commandssub` SET `permitted`=REPLACE(`permitted`, '\'".$copyfrom."\'', '\'".$copyfrom."\',\'".$copyto."\'') where `permitted` like '%\'".$copyfrom."\'%' AND `permitted` NOT like '%\'".$copyto."\'%';";
				$stmt=$link->prepare($sql); $stmt->execute();
			} else if($_POST['Yr'.$startyr.'']==2018){
				$sql="UPDATE `".$startyr."_1rtc`.`permissions_2allprocesses` SET `AllowedGroups`=REPLACE(`AllowedGroups`, '\'".$copyfrom."\'', '\'".$copyfrom."\',\'".$copyto."\'') where `AllowedGroups` like '%\'".$copyfrom."\'%' AND Not like `AllowedGroups` like '%\'".$copyto."\'%';";
				$stmt=$link->prepare($sql); $stmt->execute();
			} else {
				// $sql="UPDATE `".$startyr."_1rtc`.`permissions_2allprocesses` SET `AllowedPos`=REPLACE(`AllowedPos`, ',".$copyfrom.",', ',".$copyfrom.",".$copyto.",') where `AllowedPos` like '%,".$copyfrom.",%';";
				// $stmt=$link->prepare($sql); $stmt->execute();

				$sql='SELECT GROUP_CONCAT(ProcessID) AS ProcessID FROM '.$startyr.'_1rtc.`permissions_2allprocesses` WHERE (FIND_IN_SET('.$copyfrom.',AllowedPos)) AND (NOT FIND_IN_SET('.$copyto.',AllowedPos))';
					$stmt=$link->query($sql);
					$result=$stmt->fetch();
					
				$sql='UPDATE '.$startyr.'_1rtc.`permissions_2allprocesses` SET AllowedPos=CONCAT(AllowedPos,",'.$copyto.'") WHERE ProcessID IN ('.$result['ProcessID'].')';
					
				$stmt = $link->prepare($sql); $stmt->execute();
			}
			
			$yearsupdated.='_'.$_POST['Yr'.$startyr.''];
		}
		$startyr++;
	}
	header("Location:assignpermission.php?w=PrevYrsPermission&Year=".$yearsupdated."");
	
	break;
	
}
 $stmt=null; 

?>
</div> <!-- end section -->
