<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(4100,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');
?>
<br><div id="section" style="display: block;">
<?php
include_once('../backendphp/layout/linkstyle.php');
echo '<div>';
		echo '<a id=\'link\' href="uniform.php?w=List">Uniform Info</a> ';
		echo '<a id=\'link\' href="uniform.php?w=SizePriceStocks">Uniform Size/Price/Stocks</a> ';
                echo '<a id=\'link\' href="uniform.php?w=UniformAssign">Uniform Assign</a> ';
		echo '<a id=\'link\' href="uniform.php?w=UniformStocksNew">Uniform Stocks (OnHand)</a> ';
		echo '<a id=\'link\' href="uniform.php?w=UniformAssignLogs">Uniform Assign Logs</a>';
		echo str_repeat('&nbsp;',20).'<a id=\'link\' href="uniformsummary.php">Uniform Summary</a>';
                echo '</div><br/><br/>';


$which=(!isset($_GET['w'])?'UniformStocksNew':$_GET['w']);

if (in_array($which,array('List'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
   $sql='SELECT ui.*, UID AS TxnID, CONCAT(Nickname, " ",Surname) AS EncodedBy FROM hr_61uniforminfo AS ui JOIN 1_gamit.0idinfo e ON e.IDNo=ui.EncodedByNo ';
   $columnnameslist=array('UniformType','Positions', 'Description', 'Supplier');
   $columnstoadd=array('UniformType', 'Description', 'Supplier');
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
}
if (in_array($which,array('AddNewUniformProcess','UpdateUniformProcess'))){
	$new1 = '';
	
	if (isset($_POST['allowed'])){
		foreach($_POST['allowed'] as $selected1){
			$selected1 = "".$selected1.",";
			$new1 = $new1 . ''.$selected1.'';
		}
		$trimlastcomma = substr($new1,0,-1);
		
	}
	else {
		$trimlastcomma='';
	}
}
//Add/Edit for UniformType
if (in_array($which,array('AddUniformInfo','EditUniformInfo'))){
   $columnstoadd=array('UniformType','Description', 'Supplier');
}


if (in_array($which,array('SizePriceStocks','EditSpecificsSAP'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
	
	 $sql='SELECT usp.*, USAPID AS TxnID, UniformType, CONCAT(Nickname, " ",Surname) AS EncodedBy, SizeShortCode As Size, (Price * Stocks) AS TotalPrice FROM hr_61uniformsps AS usp JOIN 1_gamit.0idinfo e ON e.IDNo=usp.EncodedByNo JOIN hr_61uniforminfo AS ui ON usp.UniformTypeID = ui.UID JOIN hr_60sizes AS s ON usp.SizeID = s.SizeID';
		echo comboBox($link,'SELECT UID, UniformType FROM hr_61uniforminfo ORDER BY UID','UID','UniformType','uniformtypelist');
		echo comboBox($link,'SELECT SizeID, SizeShortCode FROM hr_60sizes ORDER BY SizeID','SizeID','SizeShortCode','sizelist');
		$columnnameslist=array('UniformType', 'Size', 'Price', 'Stocks', 'TotalPrice');
	    $columnstoadd=array('UniformType', 'Size', 'Price', 'Stocks');
		
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
}

//Add/Edit for UniformSizeAndPrice
if (in_array($which,array('AddSAP','EditSAP'))){
   $UniformID=comboBoxValue($link,'hr_61uniforminfo','UniformType',addslashes($_POST['UniformType']),'UID');
   $SizeID=comboBoxValue($link,'hr_60sizes','SizeShortCode',addslashes($_POST['Size']),'SizeID');
   $columnstoadd=array('Price', 'Stocks');
}


if (in_array($which,array('UniformAssign','EditSpecificsUniformAssign'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
	echo comboBox($link,'SELECT IDNo, CONCAT(Nickname, " ", Surname) AS Name FROM `1employees` ORDER BY Name','IDNo','Name','employeelist');
	
	$sql='SELECT ua.Posted, DATEDIFF(\''.$today.'\', DateReceived) AS AgeByDay, b.Branch, ua.*, SizeShortCode, CONCAT(UniformType, " - [",SizeShortCode,"]") AS UniformInfo, CONCAT(e1.Nickname, " ", e1.Surname) AS AssignedTo, department AS Department, ui.Description, ui.UniformType, CONCAT(e.Nickname, " ",e.Surname) AS EncodedBy, SizeShortCode As Size FROM hr_62uniformassign AS ua JOIN `1employees` e ON e.IDNo=ua.EncodedByNo JOIN hr_61uniformsps AS usp ON ua.USAPID = usp.USAPID JOIN hr_60sizes AS s ON usp.SizeID = s.SizeID JOIN hr_61uniforminfo AS ui ON ui.UID = usp.UniformTypeID JOIN 1_gamit.0idinfo AS e1 ON e1.IDNo = ua.IDNo JOIN 1departments AS d ON d.deptid=ua.DeptID JOIN `1branches` AS b ON b.BranchNo = ua.BranchID';
	
	echo comboBox($link,'SELECT USAPID, UniformTypeID, CONCAT(UniformType, " - [",SizeShortCode,"]") AS UniformInfo FROM hr_61uniformsps AS usp JOIN hr_61uniforminfo AS ui ON usp.UniformTypeID=ui.UID JOIN hr_60sizes AS s ON s.SizeID = usp.SizeID','USAPID','UniformInfo','usaplist');
	
	$columnnameslist=array('AssignedTo', 'Department', 'Branch', 'UniformType', 'Description', 'Size', 'Quantity', 'DateDelivered', 'DateReceived', 'AgeByDay', 'Posted');
	
	 if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
}

//Add/Edit for UniformAssign
if (in_array($which,array('AddUniformAssign','EditUniformAssign'))){
	
	$string = $_POST['UniformInfo'];
	//To get the string before ' - ['
	$UniformType = substr($string, 0, strrpos($string, ' - ['));
	
	//To get the string inside the bracket
	preg_match("/\[(.*)\]/", $string , $matches);
	$UniformSize = $matches[1];
	
	//To get the USAPID Based on the previous string record
	$sql1 = 'SELECT USAPID FROM hr_61uniformsps AS usp JOIN hr_61uniforminfo AS ui ON usp.UniformTypeID = ui.UID JOIN hr_60sizes AS s ON usp.SizeID = s.SizeID WHERE ui.UniformType=\''.addslashes($UniformType).'\' AND s.SizeShortCode = \''.$UniformSize.'\'';
	$stmt=$link->query($sql1); $res=$stmt->fetch();
	
	$USAPID = $res['USAPID'];
	// $Stock = $res['Stock'];
	
	$idno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['AssignedTo']),'IDNo');
	
	$sql2='SELECT deptid,BranchNo FROM attend_30currentpositions WHERE IDNo='.$idno;
   // echo $idno;
   // echo $sql2;
   $stmt=$link->query($sql2); $res=$stmt->fetch();
   $columnstoadd=array('Quantity', 'DateDelivered', 'DateReceived');
   // $columnstoadd=array('Quantity', 'DateDelivered', 'DateReceived');
   
}

switch ($which)
{
/*For UniformType Setting*/
	//Start of Case List
	case 'List':
	
		$title='Uniforms List';
		
		echo '<title>'.$title.'</title>';
		echo '<br><br><h3><a href="uniform.php?w=AddNewUniform">Add New Uniform</a></h3>';
		
		//Processes
		$delprocess='uniform.php?w=DeleteUniformInfo&UID=';
		
		/* $title=''; $formdesc=''; */ $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		$editprocess='uniform.php?w=AddNewUniform&UID='; $editprocesslabel='Lookup';
		
		include('../backendphp/layout/displayastable.php'); 
	break; //End of Case List
	
	
	case 'AddNewUniform':
	
		$sql0='CREATE TEMPORARY TABLE groupdept AS SELECT p.deptid, d.Department, p.JobLevelID, p.Position, p.PositionID FROM attend_1positions p JOIN `1departments` d ON d.deptid=p.deptid JOIN `attend_0joblevels` jl ON jl.JobLevelID=p.JobLevelID ORDER BY JobLevelID DESC,jl.JobLevelID DESC;';
  	
		$stmt=$link->query($sql0);
		$sql0='SELECT DISTINCTROW deptid AS DeptID, Department FROM groupdept;';
		$stmt0=$link->query($sql0); $row0=$stmt0->fetchAll();
		
			
			if (isset($_GET['UID'])){
				
				$title1 = 'Update Uniform';
				echo '<title>'.$title1.'</title>';
				
				$uid = intval($_GET['UID']);
				$sqlvalue ="SELECT * FROM `hr_61uniforminfo` WHERE UID=".$uid.";";
				$stmtvalue=$link->query($sqlvalue); $rowvalue=$stmtvalue->fetch();
				
				$UID = $rowvalue['UID'];
				$UniformType = $rowvalue['UniformType'];
				$Description = $rowvalue['Description'];
				$Supplier = $rowvalue['Supplier'];
				$Positions = ($rowvalue['Positions']==''?-1:$rowvalue['Positions']);
				$path = 'UpdateUniformProcess&UID='.$uid.'';
				$submitlabel = "Update menu";
				
			}
			else {
				$title1 = 'Add New Uniform';
				echo '<title>'.$title1.'</title>';
				
				$UID='';
				$UniformType='';
				$Description='';
				$Supplier='';
				$Positions='-1';
				$path='AddNewUniformProcess';
				$submitlabel = "Add new menu";
			}
			echo '<h2>'.$title1.'</h2><br/>';
			
			
			echo '<div>';
			echo '<div style="float:left;">';
			echo '<form action="uniform.php?w='.$path.'" method="post"><table>';
			echo '<input name="action_token" type="hidden" value="'.$_SESSION['action_token'].'"/><input name="UID" type="hidden" size="20" placeholder="" value="'.$UID.'"/>';
			echo '<tr><td>Uniform Type:</td><td><input name="UniformType" type="text" size="50" placeholder="" value="'.$UniformType.'" required /></td></tr>';
			echo '<tr><td>Description:</td><td><input name="UniformDescription" type="text" size="50" placeholder="" value="'.$Description.'" require/></td></tr>';
			echo '<tr><td>Supplier:</td><td><input name="Supplier" type="text" size="50" placeholder="" value="'.$Supplier.'" required/></td></tr>';
			echo '<tr><td valign="top">Positions:</td><td>';
			
			echo '<div style="float:left;">';    
			foreach($row0 as $pos){
            echo '<h4>'.$pos['Department'].'</h4>';
            $sql1='SELECT Position, PositionID FROM groupdept WHERE DeptID='.$pos['DeptID'].' GROUP BY PositionID ';
			
            $stmt1=$link->query($sql1); $row1=$stmt1->fetchAll();
            $deptlist='<table>';
            
            foreach($row1 as $row2){
				$deptlist.='<tr><td><input type="checkbox" name="allowed[]" value="'.$row2['PositionID'].'"
				'.(in_array($row2['PositionID'],explode(",",$Positions)) !== false ? 'checked = "checked"': '').';/><td>'.$row2['Position'].' ('.$row2['PositionID'].')</td></td>';
            }  
				echo $deptlist.'</table>';
			}
        
			echo '</div>';
			echo '</td></tr>';
			
				echo '<tr><td style="padding:10px;"></td></tr><tr><td></td><td align="right"><input type="submit" value="'.$submitlabel.'"/></td></tr>';
		
			echo '</table></form>';
			echo '</div>';
			
		
	break;
	
	
	case 'AddNewUniformProcess':

	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='INSERT INTO `hr_61uniforminfo` (UniformType,Description,Supplier,Positions,EncodedByNo) VALUES ("'.$_POST['UniformType'].'","'.$_POST['UniformDescription'].'","'.$_POST['Supplier'].'",'.(empty($trimlastcomma) ? 'DEFAULT':'"'.$trimlastcomma.'"').','.$_SESSION['(ak0)'].')';
		$stmt = $link->prepare($sql);
		$stmt->execute();
		
		header("Location:uniform.php?w=List");
		
	break;
	
	
	case 'UpdateUniformProcess':

	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
		$sql='UPDATE `hr_61uniforminfo` SET UniformType="'.$_POST['UniformType'].'", Description="'.$_POST['UniformDescription'].'", Supplier="'.$_POST['Supplier'].'",Positions='.(empty($trimlastcomma) ? 'DEFAULT':'"'.$trimlastcomma.'"').', EncodedByNo="'.$_SESSION['(ak0)'].'" WHERE UID='.intval($_GET['UID']);
		// echo $sql; exit();
		$stmt = $link->prepare($sql);
		$stmt->execute();
		header("Location:uniform.php?w=List&UID=".$_POST['UID']);
	
	break;
	
	
	//Start Of Case DeleteUniformInfo
    case 'DeleteUniformInfo':
	//access
        if (allowedToOpen(4100,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM `hr_61uniforminfo` WHERE UID='.intval($_GET['UID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:".$_SERVER['HTTP_REFERER']);
    break; //End of Case DeleteUniformInfo
/*End of UniformInfo Setting*/

/*For SizePriceStocks Setting*/
	//Start of Case SAP
	case 'SizePriceStocks':
		
		$title='List of Uniform Size/Price/Stocks'; $formdesc='Add New Uniform Size/Price/Stocks.';
		$method='post';
			$columnnames=array(
			array('field'=>'UniformType', 'type'=>'text','size'=>10,'required'=>true,'list'=>'uniformtypelist'),
			array('field'=>'Size', 'type'=>'text','size'=>10,'required'=>true,'list'=>'sizelist'),
			array('field'=>'Price','type'=>'text','size'=>15,'required'=>true),
			array('field'=>'Stocks','type'=>'text','size'=>15,'required'=>true));
							
		$action='uniform.php?w=AddSAP'; $fieldsinrow=5; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');
		
		//Processes
		$delprocess='uniform.php?w=DeleteSAP&USAPID=';
		
		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		$editprocess='uniform.php?w=EditSpecificsSAP&USAPID='; $editprocesslabel='Edit';
		
		include('../backendphp/layout/displayastable.php'); 
	break; //End of Case SAP
	
	//Start of Case AddSAP
	case 'AddSAP':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$sql='';
			foreach ($columnstoadd as $field) {$sql.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
			
			$sql='INSERT INTO `hr_61uniformsps` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' UniformTypeID='.$UniformID.', SizeID='.$SizeID.', TimeStamp=Now()'; 
                        $stmt=$link->prepare($sql); $stmt->execute();
		
		header('Location:'.$_SERVER['HTTP_REFERER']);
	break; //End of Case AddSAP
	
	//Start Of Case EditSpecificsSAP
    case 'EditSpecificsSAP':
		$sql='SELECT usp.*, USAPID AS TxnID, UniformType, CONCAT(Nickname, " ",Surname) AS EncodedBy, SizeShortCode As Size FROM hr_61uniformsps AS usp JOIN `1employees` e ON e.IDNo=usp.EncodedByNo JOIN hr_61uniforminfo AS ui ON usp.UniformTypeID = ui.UID JOIN hr_60sizes AS s ON usp.SizeID = s.SizeID';
		echo comboBox($link,'SELECT UID, UniformType FROM hr_61uniforminfo ORDER BY UID','UID','UniformType','uniformtypelist');
		echo comboBox($link,'SELECT SizeID, SizeShortCode FROM hr_60sizes ORDER BY SizeID','SizeID','SizeShortCode','sizelist');
		
	    $columnnameslist=array('UniformType', 'Size', 'Price', 'Stocks');
	    
			 
		$title='Edit Specifics';
		$txnid=intval($_GET['USAPID']);

		$sql=$sql.' WHERE USAPID='.$txnid;
		
		$columnstoedit=array_diff($columnnameslist,array('UniformTypeID','SizeID'));	
        $columnnames=$columnnameslist;
			
			
		$columnswithlists=array('UniformType','Size');
		$listsname=array('UniformType'=>'uniformtypelist', 'Size'=>'sizelist');
		
		$editprocess='uniform.php?w=EditSAP&USAPID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break; //End of Case EditSpecificsSAP
	
	//Start Of Case EditUniformSAP
    case 'EditSAP':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(4100,'1rtc')){
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `hr_61uniformsps` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' UniformTypeID='.$UniformID.', SizeID='.$SizeID.', TimeStamp=Now() WHERE USAPID='.$_GET['USAPID'].'';
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		}
		header("Location:uniform.php?w=SizePriceStocks");
    break; //End of Case EditUniformSAP
	
	//Start Of Case DeleteSAP
    case 'DeleteSAP':
	//access
        if (allowedToOpen(4101,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM `hr_61uniformsps` WHERE USAPID='.intval($_GET['USAPID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:".$_SERVER['HTTP_REFERER']);
    break; //End of Case DeleteSAP
/*End of SAP Setting*/

	case 'UniformAssign':
		
		$title='Uniform Assign'; $formdesc='Assigning of Uniform.<br/>Posted: 1 = Posted, 0 = Unposted';
		$method='post';
			$columnnames=array(
			array('field'=>'AssignedTo', 'type'=>'text','size'=>15,'required'=>true,'list'=>'employeelist'),
			array('field'=>'UniformInfo', 'type'=>'text','size'=>10,'required'=>true,'list'=>'usaplist'),
			array('field'=>'Quantity', 'type'=>'text','size'=>10,'required'=>true),
			array('field'=>'DateDelivered', 'type'=>'date','size'=>10,'required'=>true),
			array('field'=>'DateReceived', 'type'=>'date','size'=>10,'required'=>true));
							
		$action='uniform.php?w=AddUniformAssign'; $fieldsinrow=6; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');
		
		
		
		$editprocess='uniform.php?w=PostUnpost&action_token='.$_SESSION['action_token'].'&TxnID=';
		$editprocesslabel='Post/Unpost';
		
		$addlprocess='uniform.php?w=EditSpecificsUniformAssign&TxnID=';
		$addlprocesslabel='Edit';
		
		$addlprocess2='uniform.php?w=DeleteUniformConfirm&TxnID=';
		$addlprocesslabel2 = 'Delete';
		
		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;     
		
			
		include('../backendphp/layout/displayastable.php'); 
	
	break; //End of Case UniformAssign
	
	//Start of Case AddUniformAssign
	case 'AddUniformAssign':
		if (allowedToOpen(4100,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$sql='';
			foreach ($columnstoadd as $field) {$sql.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		
			$sql3 = 'SELECT Stocks FROM hr_61uniformsps WHERE USAPID = '.$USAPID.'';
			$stmt3=$link->query($sql3); $res3=$stmt3->fetch();
			
			$sql4 = 'SELECT SUM(Quantity) AS TotAssign FROM hr_62uniformassign WHERE USAPID = '.$USAPID.'';
			$stmt4=$link->query($sql4); $res4=$stmt4->fetch();
			
			if ($res3['Stocks'] >= ($_POST['Quantity'] + $res4['TotAssign']))
			{
				$sql='INSERT INTO `hr_62uniformassign` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' USAPID='.$USAPID.', IDNo='.$idno.', DeptID='.$res['deptid'].', TimeStamp=Now()'; 
              // echo $res3['Stocks'];
			  // echo $sql;
			// break;
				$stmt=$link->prepare($sql); $stmt->execute();
				header('Location:'.$_SERVER['HTTP_REFERER']);
			}
			else
			{
				echo '<h3>No Available Stocks.</h3>';
			}
		}
		
	break; //End of Case AddUniformAssign
	
	
	case 'DeleteUniformConfirm';
	
	echo '<script>
		if (confirm("Really Delete This?"))
		{
			window.location.href = "uniform.php?w=DeleteUniformAssign&TxnID='.intval($_GET['TxnID']).'";
		}
		else {
			window.location.href = "uniform.php?w=UniformAssign";
		}
		</script>';
	break;
	//Start Of Case DeleteUniformAssign
	
    case 'DeleteUniformAssign':
	
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$columnstoadd=array('Quantity', 'DateReceived', 'IDNo', 'DeptID', 'BranchID', 'USAPID', 'EncodedByNo', 'TimeStamp');
			$sql='';
		
			$sql='SELECT * FROM `hr_62uniformassign` WHERE TxnID='.intval($_GET['TxnID']).'';
			$stmt = $link->query($sql);
			$row = $stmt->fetch();
			
			$sql2 ='';
			foreach ($columnstoadd as $field) {$sql2.=' `' . $field. '`=\''.addslashes($row[$field]).'\', '; }
			
                            $sql2='INSERT INTO `hr_65uniformassign_logs` SET '.$sql2.'  Action=0, ActionByNo='.$_SESSION['(ak0)'].'';
			 // break;
			$stmt2=$link->prepare($sql2);
			$stmt2->execute();
			
			$sql='DELETE FROM `hr_62uniformassign` WHERE Posted=0 AND TxnID='.intval($_GET['TxnID']);
			$stmt=$link->prepare($sql); $stmt->execute();
			
		
       header("Location:uniform.php?w=UniformAssign");
	   
    break; //End of Case DeleteUniformAssign
	
	//Start Of Case EditSpecificsUniformAssign
    case 'EditSpecificsUniformAssign':
		
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);

		$sql=$sql.' WHERE TxnID='.$txnid;
		
		$columnnameslist=array('AssignedTo', 'UniformInfo', 'Department', 'Branch', 'UniformType', 'Description', 'Size', 'Quantity', 'DateDelivered', 'DateReceived', 'AgeByDay');
		
		$columnstoedit=array_diff($columnnameslist,array('TxnID', 'Department', 'Branch', 'Description', 'UniformType', 'Brand', 'Size', 'AgeByDay'));	
        $columnnames=$columnnameslist;
		
		 // if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
		 
		$columnswithlists=array('UniformInfo', 'AssignedTo');
		$listsname=array('UniformInfo'=>'usaplist', 'AssignedTo'=>'employeelist');
		
		$editprocess='uniform.php?w=EditUniformAssign&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break; //End of Case EditSpecificsUniformAssign
	
	//Start Of Case EditUniformStocks
    case 'EditUniformAssign':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(4100,'1rtc')){
		$sql='';
		
		$sql='SELECT * FROM `hr_62uniformassign` WHERE TxnID='.intval($_GET['TxnID']).'';
		$stmt = $link->query($sql);
		$row = $stmt->fetch();
		
		$sql2 ='';
		foreach ($columnstoadd as $field) {$sql2.=' `' . $field. '`=\''.addslashes($row[$field]).'\', '; }
		
		$sql2='INSERT INTO `hr_65uniformassign_logs` SET '.$sql2.' IDNo='.$idno.', DeptID='.$row['DeptID'].', BranchID='.$row['BranchID'].', USAPID='.$row['USAPID'].', EncodedByNo='.$row['EncodedByNo'].', TimeStamp=\''.$row['TimeStamp'].'\', Action=1, ActionByNo='.$_SESSION['(ak0)'].'';
		// echo $sql2; break;
		$stmt2=$link->prepare($sql2);
		$stmt2->execute();
		
		$sql = '';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `hr_62uniformassign` SET Posted=1, EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' USAPID='.$USAPID.', IDNo='.$idno.', DeptID='.$res['deptid'].', BranchID='.$res['BranchNo'].', TimeStamp=Now() WHERE Posted=0 AND TxnID='.$_GET['TxnID'].'';
		
		// echo $sql; break;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		}
		header("Location:uniform.php?w=UniformAssign");
    break; //End of Case EditUniformAssign
	
	
/*For UniformStocks View*/
	//Start of Case UniformStocksNew
	case 'UniformStocksNew':
		// include_once('../backendphp/layout/showencodedbybutton.php');
		
	   
	  //  $sql='SELECT usp.*, ((usp.Stocks - (SELECT SUM(Quantity) FROM hr_62uniformassign WHERE USAPID=usp.USAPID))) AS AvailableStock, UniformType, ui.Description, ui.Supplier, SizeShortCode As Size, Price AS SinglePrice FROM hr_61uniformsps AS usp JOIN 1_gamit.0idinfo e ON e.IDNo=usp.EncodedByNo JOIN hr_61uniforminfo AS ui ON usp.UniformTypeID = ui.UID JOIN hr_60sizes AS s ON usp.SizeID = s.SizeID';
            ?>
            <div style='background-color: #e6e6e6;
            width: 1100px;
            border: 2px solid grey;
            padding: 25px;
            margin: 25px;'>
            <b>Issuance Guidelines:</b><br/><br/>
            <ul>
                <li>Regular employees will be given four (4) pieces of uniform tops.</li><br/>
                <li>New employees reaching a tenure of 3 months and with a performance score of 3.5 will receive two (2) pieces.  The balance of two (2) pieces will be given upon regularization. <br/><br/> They may opt to purchase additional pieces while not yet regular, for their convenience.  Upon regularization, they will either be issued 2 additional pieces, or refunded a maximum value of two pieces that they purchased.</li><br/>
                <li>Employees may request to purchase additional uniforms, to replace worn or damaged issued uniforms.  Worn or damaged uniforms must be returned to HR.</li><br/>
                <li>ALL uniforms must be surrendered as part of clearance upon resignation.</li>
            </ul>
            </div>
            
            <?php
            $sql='SELECT usp.*, IFNULL(((usp.Stocks - (SELECT SUM(Quantity) FROM
                    hr_62uniformassign WHERE USAPID=usp.USAPID))),usp.Stocks) AS AvailableStock,
                    UniformType, ui.Description, ui.Supplier, SizeShortCode As Size, Price AS
                    SinglePrice FROM hr_61uniformsps AS usp JOIN 1_gamit.0idinfo e ON
                    e.IDNo=usp.EncodedByNo JOIN hr_61uniforminfo AS ui ON usp.UniformTypeID =
                    ui.UID JOIN hr_60sizes AS s ON usp.SizeID = s.SizeID';
	    $columnnameslist=array('UniformType', 'Description', 'Supplier', 'Size', 'SinglePrice', 'AvailableStock');
	    
		$title='Uniform Stocks (OnHand)'; $formdesc='List of Available Stocks'; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		include('../backendphp/layout/displayastable.php'); 
	break; //End of Case UniformStocksNew
	
	
	case 'UniformAssignLogs':
		// include_once('../backendphp/layout/showencodedbybutton.php');
	echo comboBox($link,'SELECT IDNo, CONCAT(Nickname, " ", Surname) AS Name FROM `1employees` ORDER BY Name','IDNo','Name','employeelist');
		
	$sql='SELECT DATEDIFF(\''.$today.'\', DateReceived) AS AgeByDay, b.Branch, ua.*, SizeShortCode, CONCAT(UniformType, " - [",SizeShortCode,"]") AS UniformInfo, CONCAT(e1.Nickname, " ", e1.Surname) AS AssignedTo, CONCAT(e2.Nickname, " ", e2.Surname) AS ActionBy, department AS Department, ui.Description, ui.UniformType, CONCAT(e.Nickname, " ",e.Surname) AS EncodedBy, SizeShortCode As Size FROM hr_65uniformassign_logs AS ua JOIN `1employees` e ON e.IDNo=ua.EncodedByNo JOIN hr_61uniformsps AS usp ON ua.USAPID = usp.USAPID JOIN hr_60sizes AS s ON usp.SizeID = s.SizeID JOIN hr_61uniforminfo AS ui ON ui.UID = usp.UniformTypeID JOIN 1_gamit.0idinfo AS e1 ON e1.IDNo = ua.IDNo JOIN 1departments AS d ON d.deptid=ua.DeptID JOIN `1branches` AS b ON b.BranchNo = ua.BranchID JOIN 1_gamit.0idinfo AS e2 ON e2.IDNo = ua.ActionByNo ORDER BY ActionTimeStamp DESC';
	// echo $sql;
	$columnnameslist=array('AssignedTo', 'Department', 'Branch', 'UniformType', 'Description', 'Size', 'Quantity', 'DateDelivered', 'DateReceived', 'AgeByDay', 'EncodedBy', 'TimeStamp', 'Action', 'ActionBy', 'ActionTimeStamp');
	
	 // if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
	 
		
		//Processes
	
		$title='Uniform Assign Logs'; $formdesc='Action: 0 = Deleted, 1 = Edited'; $txnidname='TxnID';
		$columnnames=$columnnameslist;  
		
		
		include('../backendphp/layout/displayastable.php'); 
	break; //End of Case UniformAssignLogs
	
	//Start Of Case PostUnpost
    case 'PostUnpost':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='UPDATE `hr_62uniformassign` SET PostedByNo='.$_SESSION['(ak0)'].',  Posted=IF(Posted=1 AND '.(allowedToOpen(4101,'1rtc')).',0,1), PostedTimeStamp=Now() WHERE TxnID='.$_GET['TxnID'].'';
		$stmt=$link->prepare($sql); $stmt->execute();
		
		header("Location:uniform.php?w=UniformAssign");
    break; //End of Case PostUnpost
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
