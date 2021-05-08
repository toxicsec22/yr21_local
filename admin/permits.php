<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(8291,'1rtc')) { echo 'No permission'; exit; }

if(isset($_GET['w']) AND $_GET['w']=="Print"){ $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link; goto skipcontents;}
$showbranches=true; include_once('../switchboard/contents.php');
skipcontents:
 
include_once('../backendphp/layout/linkstyle.php');
include_once('../backendphp/layout/regulartablestyle.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';


$which=(!isset($_GET['w'])?'List':$_GET['w']);

if (in_array($which,array('PermitDetails','EditSpecifics','Edit'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
	
	$sql='SELECT ps.*, (CASE WHEN Original=1 THEN "YES" WHEN Original=0 THEN "NO" ELSE "" END) AS `Original?`, pm.BranchNo, ps.TxnSubID AS TxnID, Permit, Branch, pm.PID, pm.Remarks, StatusDetails, Amount, (1QPaidAmount+2QPaidAmount+3QPaidAmount+4QPaidAmount) AS TotalAmountQ, (1QPaidAmount) AS TotalAmountA, (1QPaidAmount+2QPaidAmount) AS TotalAmountSA, CONCAT(e.Nickname, " ", e.Surname) AS SubmittedBy, CONCAT(e1.Nickname, " ", e1.Surname) AS ReceivedBy FROM admin_2permitsub ps JOIN admin_2permitmain pm ON ps.TxnID=pm.TxnID JOIN admin_1permittype pt ON pm.PID=pt.PID JOIN `1branches` b ON pm.BranchNo=b.BranchNo JOIN admin_0permitstatus s ON pm.StatusID=s.StatusID LEFT JOIN `1employees` e ON ps.SubmittedByNo=e.IDNo LEFT JOIN `1employees` e1 ON ps.ReceivedByNo=e1.IDNo WHERE ps.TxnID='.intval($_GET['TxnID']).''; $stmt = $link->query($sql); $row=$stmt->fetch(); 
	
	
	$sql2='SELECT pf.FID, Frequency FROM admin_0permitfrequency pf JOIN admin_1permittype pt ON pf.FID=pt.FID WHERE pt.PID='.$row['PID'].''; $stmt = $link->query($sql); $row=$stmt->fetch();
	$stmt2 = $link->query($sql2); $row2=$stmt2->fetch(); 
	
	
	$columnnameslist=array('Permit','Original?', '1QPaidAmount', '1QDueDate', '2QPaidAmount', '2QDueDate', '3QPaidAmount', '3QDueDate', '4QPaidAmount', '4QDueDate', 'TotalAmountQ', 'TotalAmountSA', 'TotalAmountA', 'Amount', 'DueDate', 'DateSubmitted', 'DateReceived');
	
	if ($row2['FID']==1){
		$setmonthly=1;
	}
	
	else if ($row2['FID']==3){ //Quarterly
		$columnnameslist = array_diff($columnnameslist, array('Amount','DueDate', 'TotalAmountSA', 'TotalAmountA'));
		
		if (allowedToOpen(8293,'1rtc')){
			$columnstoadd=array('DateSubmitted');
		} else {
			$columnstoadd=array('Original', '1QPaidAmount', '1QDueDate', '2QPaidAmount', '2QDueDate', '3QPaidAmount', '3QDueDate', '4QPaidAmount', '4QDueDate', 'DateReceived');
		}
		
		
	} else if ($row2['FID']==4) { //Annualy
		$columnnameslist = array_diff($columnnameslist, array('Amount','DueDate', '2QPaidAmount', '2QDueDate', '3QPaidAmount', '3QDueDate', '4QPaidAmount', '4QDueDate', 'TotalAmountQ', 'TotalAmountSA'));
		
		// $columnstoadd=array('Original', '1QPaidAmount', '1QDueDate', (allowedToOpen(8293,'1rtc')?'DateSubmitted':'DateReceived'));
		if ((allowedToOpen(8293,'1rtc'))){
			$columnstoadd=array('DateSubmitted');
		} else {
			$columnstoadd=array('Original', '1QPaidAmount', '1QDueDate', 'DateReceived');
		}
		
		
	} else if ($row2['FID']==2) { //Semi-Annually
	
		$columnnameslist = array_diff($columnnameslist, array('Amount','DueDate', '3QPaidAmount', '3QDueDate', '4QPaidAmount', '4QDueDate', 'TotalAmountQ', 'TotalAmountA'));
		
		// $columnstoadd=array('Original', '1QPaidAmount', '1QDueDate', '2QPaidAmount', '2QDueDate', (allowedToOpen(8293,'1rtc')?'DateSubmitted':'DateReceived'));
		if (allowedToOpen(8293,'1rtc')){
			$columnstoadd=array('DateSubmitted');
		}
		else {
			$columnstoadd=array('Original', '1QPaidAmount', '1QDueDate', '2QPaidAmount', '2QDueDate', 'DateReceived');
		}
	} else { //Others Submitted Date and Date Received Only will appear
		$columnnameslist = array_diff($columnnameslist, array('1QPaidAmount', '1QDueDate','2QPaidAmount', '2QDueDate','3QPaidAmount', '3QDueDate', '4QPaidAmount', '4QDueDate', 'TotalAmountQ', 'TotalAmountA', 'TotalAmountSA'));
		if (allowedToOpen(8293,'1rtc')){
			$columnstoadd=array('DateSubmitted');
		} else {
			$columnstoadd=array('Original','Amount', 'DueDate', 'DateReceived');
		}
		
	}
	// echo $row2['FID'];
	 // if ($row2['FID']==1)
	
	if ($showenc==1) { array_push($columnnameslist,'SubmittedBy','SubmittedByTimeStamp','ReceivedBy','ReceivedByTimeStamp'); }
}

include_once('permitlinks.php');

switch ($which){
	
	case 'List':
	if (allowedToOpen(8291,'1rtc')) {
		$title='Permits';
		echo '<title>'.$title.'</title>';
		echo '<br><h3>'.$title.'</h3><br>';
		echo '<style>a.hovercolor:hover { background:yellow; }</style>';
		
		$br=' where b.CompanyNo='.$_SESSION['*cnum'].' ';
		if (allowedToOpen(8292,'1rtc')) {
			include_once('../backendphp/layout/showallbranchesbutton.php');
			$condition=($show==1)?' ':$br;
		} else {
			$condition=$br . 'AND b.BranchNo='.$_SESSION['bnum'].' ';
		}
		
		
		$sql1='CREATE TEMPORARY TABLE tempStatus AS SELECT pm.TxnID, FID, pm.PID, pm.StatusID, pt.Permit, b.BranchNo, StatusDetails FROM admin_2permitmain pm JOIN admin_1permittype pt ON pm.PID=pt.PID JOIN admin_0permitstatus ps ON pm.StatusID=ps.StatusID JOIN `1branches` b ON pm.BranchNo=b.BranchNo'.$condition.'GROUP BY BranchNo, PID ORDER BY pt.PID ASC;';
		$stmt1=$link->prepare($sql1); $stmt1->execute();
		// echo $sql1; exit();
		 $colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
			
		echo '<table style=\"display: inline-block; border: 1px solid;\">';
		
		
		echo '<thead><tr bgcolor="'. $rcolor[$colorcount%2].'"><td align="left">Branches</td>';
		
		echo '</tr></thead>';
		
		$sqlaa='SELECT pm.BranchNo,Branch FROM admin_2permitmain pm JOIN `1branches` b ON pm.BranchNo=b.BranchNo'.$condition.'GROUP BY pm.BranchNo ORDER BY BranchNo ASC;';
	
		$stmtaa = $link->query($sqlaa);
		include_once('../../acrossyrs/js/reportcharts/colors.php');
		while($rowaa=$stmtaa->fetch()) {
			$colorcount++;
			echo '<tbody><tr valign="top" bgcolor="'. $rcolor[$colorcount%2].'"><td>'.$rowaa['Branch'].'</td>';
			$sql='SELECT PID,TxnID,CONCAT(Permit,"<br>[",Frequency,"]") As Permit,StatusDetails,StatusID FROM `1branches` b JOIN tempStatus ts ON b.BranchNo=ts.BranchNo JOIN admin_0permitfrequency pf ON ts.FID=pf.FID WHERE ts.BranchNo='.$rowaa['BranchNo'].';';
			// echo $sql; exit();
			$stmt = $link->query($sql);
				while($row=$stmt->fetch()) {
					// echo '<td>'.str_replace(" ","",$row['Permit']).'<br><a class="hovercolor" href="permits.php?w=PermitDetails&TxnID='.$row['TxnID'].'" style=":hover {background: yellow};text-decoration: none;color:'.($row['StatusDetails']=='Pending'?'red':($row['StatusDetails']=='Done'?'blue':'green')).'">'.$row['StatusDetails'].'</a></td>';
					echo '<td>'.str_replace(" ","",$row['Permit']).'<br><a class="hovercolor" href="permits.php?w=PermitDetails&TxnID='.$row['TxnID'].'" style=":hover {background: yellow};text-decoration: none;color:'.$color[$row['StatusID']+4].'">'.$row['StatusDetails'].'</a></td>';
				}
				
			echo '<td><a href="permits.php?w=LookupSummary&BranchNo='.$rowaa['BranchNo'].'">Lookup Summary</a></td></tr></tbody>';
		}
		
		echo '</table>';
	} else {
		echo 'No Permission'; exit();
	}
	break;
	
	
	case 'AutoEncode':
	
	if (allowedToOpen(8294,'1rtc')) {
		echo comboBox($link,'SELECT Branch, BranchNo FROM `1branches`','BranchNo','Branch','branchlist');
	
		$sqlc='SELECT COUNT(TxnID) AS cnt FROM admin_2permitmain';
		$stmtc = $link->query($sqlc);
		$rowc=$stmtc->fetch();
		
		$title = 'Generate Data';
		echo '<title>'.$title.'</title>';
		echo '<br><h3>Auto '.$title.'</h3>';
		
			echo '<br><form action="#" method="POST"><i>Must auto generate data first.</i> <input type="submit" name="btnGenerate" value="Auto Generate Data"/></form>';
		// } else {
			echo '<br><br><h3>Generate Data Manually</h3>';
			echo '<br><form action="permits.php?w=ManualEncode" method="POST"><input type="text" name="Branch" list="branchlist"/><input type="submit" name="btnAddManual" value="Generate manually"></form>';
		// }
		if (isset($_POST['btnGenerate'])){
			$sql='SELECT PID FROM admin_1permittype';
			$stmt = $link->query($sql);

			while($row=$stmt->fetch()) {
				$sqlbranches = 'SELECT BranchNo FROM `1branches` WHERE Active = 1 AND BranchNo<>95';
				$stmtbranches = $link->query($sqlbranches);
				
				while($rowbranches= $stmtbranches->fetch()) {
					$sqlinsert = 'INSERT INTO admin_2permitmain (PID,BranchNo,EncodedByNo,TimeStamp) VALUE ("'.$row['PID'].'","'.$rowbranches['BranchNo'].'","'.$_SESSION['(ak0)'].'",Now())';
					$stmtinsert = $link->query($sqlinsert);
				}
			}
			
			$sqlinsertsub = 'INSERT INTO admin_2permitsub (TxnID) SELECT TxnID FROM admin_2permitmain;';
			$stmtinsertsub = $link->query($sqlinsertsub);
			
			header("Location:permits.php");
			
		}
	} else {
		echo 'No Permission'; exit();
	}
	break;
	
	case 'ManualEncode':
	if (allowedToOpen(8294,'1rtc')) {
		$sql='SELECT PID FROM admin_1permittype';
		$stmt = $link->query($sql);
		
		while($row=$stmt->fetch()) {
			
			$branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['Branch']),'BranchNo');
			
			$sqlmain = 'INSERT INTO admin_2permitmain (PID,BranchNo,EncodedByNo,TimeStamp) VALUE ("'.$row['PID'].'","'.$branchno.'","'.$_SESSION['(ak0)'].'",Now())';
			$stmtmain = $link->query($sqlmain);
			
			$sqllastid = 'SELECT LAST_INSERT_ID() AS lastid;';
			$stmtlastid = $link->query($sqllastid); $row=$stmtlastid->fetch();
			
			$sqlsub = 'INSERT INTO admin_2permitsub (TxnID) VALUES ('.$row['lastid'].');';
			$stmtsub = $link->query($sqlsub);
		}
		
		header("Location:permits.php");
	} else {
		echo 'No Permission'; exit();
	}
	break;
	
	
	case 'PermitDetails':
	
	if (allowedToOpen(8291,'1rtc')) {
	$txnval=intval($_GET['TxnID']);
	
	if (allowedToOpen(8292,'1rtc')) {
		
		
		echo '<br/><br/><a id=\'link\' href="permits.php?w=ManagePic">Manage Pics</a> ';
		echo '<br/><br/><form action="permitsuploadpic.php" method="POST" enctype="multipart/form-data">
					<font size="small">Upload Photo of '.$row['Branch'].' - '.$row['Permit'].'? (Only *.jpg files allowed.) <input type="hidden" name="UploadID" size=4 autocomplete="off" value='.$txnval.'>
					'.str_repeat('&nbsp;',10).' <input type="file" name="userfile" accept="image/jpg">  
					<input type="submit" name="submit" value="Submit"> 
					</font> </form><br/><br/>';
	}		
	$title=$row['Branch'].' - '.$row['Permit']. ' ('.$row2['Frequency'].')'; $formdesc=''; $txnidname='TxnID';
	
	if(!isset($setmonthly)){
		$columnnames=$columnnameslist;       
		
		$editprocess='permits.php?w=EditSpecifics&TxnID='.$txnval.'&TxnSubID='; $editprocesslabel='Edit';
		$addlprocess='permits.php?w=Print&TxnID='.$txnval.'&TxnSubID='; $addlprocesslabel='Print Permit';
		if (allowedToOpen(8294,'1rtc')){
			$delprocess='permits.php?w=DeletePermit&TxnID='.$txnval.'&TxnSubID=';
		}
		
		$width='100%';
		$stat = $row['StatusDetails'];
		include('../backendphp/layout/displayastablenosort.php');
	} else {
		
		
		// $row['StatusDetails'] = ;
		echo '<h4>'.$title.'</h4>';
		echo '<title>'.$title.'</title>';
		$stat = $row['StatusDetails'];
		$sql='SELECT *,TxnMonthlySubID AS TxnID FROM admin_2permitmonthlysub WHERE TxnID='.intval($_GET['TxnID']).'';
		
		echo '<form action="permits.php?w=DeletePermit&TxnID='.$txnval.'&Monthly=1&action_token='.$_SESSION['action_token'].'" method="POST"><input OnClick="return confirm(\'Really delete this? This action cannot be undone.\');" type="submit" value="Delete?"></form>';
		
		$columnnameslist=array('DueDate','Status');
		
		$editprocess='permits.php?w=PostMonthlyPermit&TxnID='.$_GET['TxnID'].'&TxnSubID='; $editprocesslabel='Post';
		$addlprocess='permits.php?w=EditMonthlyPermit&TxnID='.$_GET['TxnID'].'&TxnSubID='; $addlprocesslabel='Edit';
     
		$title=''; $formdesc=''; $txnidname='TxnMonthlySubID';
		$columnnames=$columnnameslist;       
		
		include('../backendphp/layout/displayastablenosort.php');
	}
		
		
		$sql3='SELECT StatusID, StatusDetails FROM admin_0permitstatus ORDER BY StatusID;';
		
		$stmt3 = $link->query($sql3);
		if (allowedToOpen(8292,'1rtc')) {
			echo '<br><br><form action="#" method="post"><h4 style="display:inline-block;">Status ('.$stat.')</h4>:<br/>Set: <select name="StatusID">';
			
			while($row3=$stmt3->fetch()) {
				if ($stat==$row3['StatusDetails']){
					$selected = 'selected';
				} else {
					$selected = '';
				}
				echo '<option value="'.$row3['StatusID'].'" '.$selected.'>'.$row3['StatusDetails'].'</option>';
			}
			echo '</select><br/>Remarks:<br/><textarea name="Remarks" rows="2" cols="50" placeholder="Leave empty if no remarks. (Up to 100 chars).">'.$row['Remarks'].'</textarea><br/><input type="submit" name="btnUpdateStatus" value="Update"></form>';
		}
		if (isset($_POST['btnUpdateStatus'])){
			$sqlup = 'UPDATE admin_2permitmain SET Remarks="'.$_POST['Remarks'].'", StatusID='.$_POST['StatusID'].' WHERE TxnID='.$txnval.'';
			$stmtup = $link->query($sqlup);
			header("Location:".$_SERVER['HTTP_REFERER']);
			
		}
	} else {
		echo 'No Permission'; exit();
	}
	break;
	
	case 'EditSpecifics':
        if (allowedToOpen(8291,'1rtc')) {
			$title='Edit Specifics';
			echo 'Date Format: <b>YYYY-MM-DD</b> (e.g. '.date('Y-m-d').')<br/>Original: <b>1</b>=Yes, <b>0</b>=No';
			$txnid=intval($_GET['TxnSubID']);
			$txnmainid=intval($_GET['TxnID']);

			$sql=$sql.' AND TxnSubID='.$txnid;
			
			$columnstoedit=$columnstoadd;
			
			$columnnames=$columnnameslist;
			
			$editprocess='permits.php?w=Edit&TxnID='.$txnmainid.'&TxnSubID='.$txnid;
			
			include('../backendphp/layout/editspecificsforlists.php');
		} else {
			echo 'No permission'; exit;
		}
	break;
	
	case 'Edit':
		if (allowedToOpen(8291,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnSubID']);
		$txnmainid=intval($_GET['TxnID']);
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		
		$sql='UPDATE `admin_2permitsub` SET TxnSubID='.$txnid.', '.$sql.' TxnSubID='.$txnid.' WHERE TxnSubID='.$txnid;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:permits.php?w=PermitDetails&TxnID=".$txnmainid."");
		} else {
		echo 'No permission'; exit;
		}
		
    break;
	
	case 'LookupSummary':
	
	if (allowedToOpen(8291,'1rtc')) {
		
	if(allowedToOpen(8294,'1rtc')){
		
		$pid='';
		
		$sqlpt='SELECT PID,CONCAT(Permit," - ",Frequency) AS PermitFreq FROM `admin_1permittype` pt JOIN admin_0permitfrequency pf ON pt.FID=pf.FID ORDER BY PermitFreq;';
		$stmtpt = $link->query($sqlpt); $rows=$stmtpt->fetchAll();
		
		foreach($rows AS $row){
			$pid.='<option value="'.$row['PID'].'">'.$row['PermitFreq'].'</option>';
		}
		// echo $pid;
		
		echo '<form action="permits.php?w=LookupSummary&BranchNo='.$_GET['BranchNo'].'" method="POST"><select name="PID">'.$pid.'</select><input type="submit" name="btnAdd" value="Add"></form>';
		
		if (isset($_POST['btnAdd'])){

				$sqlmain = 'INSERT INTO admin_2permitmain (PID,BranchNo,EncodedByNo,TimeStamp) VALUE ("'.$_POST['PID'].'","'.$_GET['BranchNo'].'","'.$_SESSION['(ak0)'].'",Now())';
				$stmtmain = $link->query($sqlmain);
				
				$sqllastid = 'SELECT LAST_INSERT_ID() AS lastid;';
				$stmtlastid = $link->query($sqllastid); $row=$stmtlastid->fetch();
				
				$sqlsub = 'INSERT INTO admin_2permitsub (TxnID) VALUES ('.$row['lastid'].');';
				$stmtsub = $link->query($sqlsub);
				
		}
		
		
	}
		
	$Branch = comboBoxValue($link,'`1branches`','BranchNo',intval($_GET['BranchNo']),'Branch');
	
	$sql='SELECT pm.*, Permit, (CASE WHEN Original=1 THEN "YES" WHEN Original=0 THEN "NO" ELSE "" END) AS `Original?`, StatusDetails AS Status FROM admin_2permitmain pm JOIN admin_1permittype pt ON pm.PID=pt.PID JOIN admin_0permitstatus s ON pm.StatusID=s.StatusID JOIN admin_2permitsub ps ON pm.TxnID=ps.TxnID WHERE BranchNo='.intval($_GET['BranchNo']).' AND pt.FID IN (SELECT FID FROM admin_0permitfrequency);';
	$columnnameslist=array('Permit','Original?','Status');
	
		$editprocess='permits.php?w=Print&TxnID='; $editprocesslabel='Print';
     
		$title=''.$Branch.' Permit Status'; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='50%';
		
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No Permission'; exit();
	}
	break;
	
	case 'SubmittedButNotReceived':
	if (allowedToOpen(8292,'1rtc')) {
		$sql='SELECT ps.*, Branch, Permit FROM admin_2permitsub ps JOIN admin_2permitmain pm ON ps.TxnID=pm.TxnID JOIN admin_1permittype pt ON pm.PID=pt.PID JOIN `1branches` b ON pm.BranchNo=b.BranchNo WHERE (DateSubmitted IS NOT NULL AND DateSubmitted<>"0000-00-00") AND (DateReceived IS NULL OR DateReceived="0000-00-00")';
		$columnnameslist=array('Branch','Permit','DateSubmitted','DateReceived');
		
		$editprocess='permits.php?w=PermitDetails&TxnID='; $editprocesslabel='Received?';
	 
		$title='List of Submitted Permits But Not Received'; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;       
		
		$width='50%';
		
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No Permission'; exit();
	}
	break;
	
	case 'DueDates':
	if (allowedToOpen(8292,'1rtc')) {
		
		$today=date('Y-m-d');
		
		echo '<br/><form method="POST" action="#">Month: <input type="number" min="1" name="MonthNo" max="12" value="'.date('m').'"/>';
		
		$sql3='SELECT PID, Permit FROM admin_1permittype ORDER BY PID;';
		
		$stmt3 = $link->query($sql3);
	
			echo '<select name="Permit">';
			echo '<option value="All">All</option>';
			while($row3=$stmt3->fetch()) {
				echo '<option value="'.$row3['Permit'].'" '.$selected.'>'.$row3['Permit'].'</option>';
			}
			echo '</select>';
		
		
		echo '<input type="submit" name="btnMonth" value="Filter"></form>';
		
		if (isset($_POST['btnMonth'])){
			if ($_POST['Permit']=='All'){
				$addlcondi = '';
			} else {
				$addlcondi = ' AND Permit="'.$_POST['Permit'].'"';
			}
			$condition = 'WHERE (((DueDate>="'.$currentyr.'-'.$_POST['MonthNo'].'-01" AND DueDate<=LAST_DAY("'.$currentyr.'-'.$_POST['MonthNo'].'-01")) OR (1QDueDate>="'.$currentyr.'-'.$_POST['MonthNo'].'-01" AND 1QDueDate<=LAST_DAY("'.$currentyr.'-'.$_POST['MonthNo'].'-01")) OR (2QDueDate>="'.$currentyr.'-'.$_POST['MonthNo'].'-01" AND 2QDueDate<=LAST_DAY("'.$currentyr.'-'.$_POST['MonthNo'].'-01")) OR (3QDueDate>="'.$currentyr.'-'.$_POST['MonthNo'].'-01" AND 3QDueDate<=LAST_DAY("'.$currentyr.'-'.$_POST['MonthNo'].'-01")) OR (4QDueDate>="'.$currentyr.'-'.$_POST['MonthNo'].'-01" AND 4QDueDate<=LAST_DAY("'.$currentyr.'-'.$_POST['MonthNo'].'-01"))) OR (CASE WHEN FID=3 /*Quarterly*/ THEN (`1QDuedate` IS NULL OR `1QDuedate`="0000-00-00" OR `2QDuedate` IS NULL OR `2QDuedate`="0000-00-00" OR `3QDuedate` IS NULL OR `3QDuedate`="0000-00-00" OR `4QDuedate` IS NULL OR `4QDuedate`="0000-00-00") WHEN FID=1 /*Annually*/ THEN (`1QDuedate` IS NULL OR `1QDuedate`="0000-00-00") WHEN FID=2 /*Semi-Annually*/ THEN (`1QDuedate` IS NULL OR `1QDuedate`="0000-00-00" OR `2QDuedate` IS NULL OR `2QDuedate`="0000-00-00") ELSE `Duedate` IS NULL OR `Duedate`="0000-00-00" END))'.$addlcondi.'';
			
			
			
			$sql='SELECT ps.*,Branch,Permit,(CASE
					WHEN (1QDueDate IS NOT NULL AND 1QDueDate<>"0000-00-00") THEN 1QDueDate
					WHEN (2QDueDate IS NOT NULL AND 2QDueDate<>"0000-00-00") THEN 2QDueDate
					WHEN (3QDueDate IS NOT NULL AND 3QDueDate<>"0000-00-00") THEN 3QDueDate
					WHEN (4QDueDate IS NOT NULL AND 3QDueDate<>"0000-00-00") THEN 4QDueDate
					ELSE DueDate
				END) AS DueDate
			FROM admin_2permitsub ps JOIN admin_2permitmain pm ON ps.TxnID=pm.TxnID JOIN admin_1permittype pt ON pm.PID=pt.PID JOIN `1branches` b ON pm.BranchNo=b.BranchNo JOIN admin_0permitstatus s ON pm.StatusID=s.StatusID '.$condition.''; //echo $sql;
			
			$columnnameslist=array('Branch','Permit','DueDate');
			
			$editprocess='permits.php?w=PermitDetails&TxnID='; $editprocesslabel='Lookup';
		 }
		 $title1 = 'Permit Due Dates';
		 
		 if (isset($_POST['btnMonth'])){	
			$title= $title1.' [MonthNo: ' . $_POST['MonthNo'] . ', Permit Type: ' . $_POST['Permit'].']'; $formdesc=''; $txnidname='TxnID';
			
			
			
			$columnnames=$columnnameslist;      
			$width='50%';
			include('../backendphp/layout/displayastable.php');
		} else {
			echo '<title>'.$title1.'</title>';
			echo '<br/><h3>'.$title1.'</h3>';
		}
		
	} else {
		echo 'No Permission'; exit();
	}
	break;
	
	case 'ManagePic':
	if (!allowedToOpen(8292,'1rtc')){ echo 'No Permission'; exit(); }
	include_once('../backendphp/js/includesscripts.php');
	$title = 'Manage Uploaded Images';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br/>';
	echo '<table border="0">
    <tr>
        <td>'; ?>    
            <?php
			if (array_key_exists('delete_file', $_POST)) {
			  $filename = $_POST['delete_file'];
			  if (file_exists($filename)) {
				unlink($filename);
				echo 'File '.str_replace('permitpics/','',$filename).' has been deleted.';
			  } else {
				echo 'Could not delete '.str_replace('permitpics/','',$filename).', file does not exist.';
			  }
			}

            $files = glob("permitpics/*");
			
            foreach ($files as $filename) {
                echo '<form method="post">';
				echo '<input type="hidden" value="'.$filename.'" name="delete_file" />';
				echo 'Filename: '.str_replace('permitpics/','',$filename).'<br/>';
				echo '<a href="'.$filename.'"><img width="100px" height="100px" src="'.$filename.'"/></a><br/>';
				echo '<input type="submit" OnClick="return confirm(\'Really delete this? This action cannot be undone.\');" value="Delete image" />';
				echo '</form>';
            }
			
            ?>
        </td>
		</tr>
	</table>
	<?php
	break;
	
	case 'Print':
	if (!allowedToOpen(8291,'1rtc')){ echo 'No Permission'; exit(); }
	echo '<title>Print</title>';
	echo '<img src="permitpics/'.$_GET['TxnID'].'.jpg" alt="No Uploaded Pic."/>';
	break;
	
	
	case 'DeletePermit':
	// exit();
	if (allowedToOpen(8294,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (isset($_GET['Monthly'])){
			$sql='DELETE FROM `admin_2permitmonthlysub` WHERE TxnID='.intval($_GET['TxnID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		} 
		
		$sql='DELETE FROM `admin_2permitsub` WHERE TxnID='.intval($_GET['TxnID']);
		$stmt=$link->prepare($sql); $stmt->execute();
		
		$sql='DELETE FROM `admin_2permitmain` WHERE TxnID='.intval($_GET['TxnID']);
		$stmt=$link->prepare($sql); $stmt->execute();
		
			header("Location:permits.php?w=List");
	} else {
		echo 'No permission'; exit;
		}
	
	
	break;
	
	
	case 'GenerateMonthlyFrequency':
	$title='Generate Monthly Frequency';
	echo '<title>'.$title.'</title>';
		echo '<form action="permits.php?w=GenerateMonthlyFrequency" method="POST"><input type="submit" name="btnGenMonth" value="Generate Data with Monthly Frequency"></form>';
		
		if (isset($_POST['btnGenMonth'])){
			
			$sql='SELECT TxnID FROM `admin_2permitmain` pm JOIN admin_1permittype pt ON pm.PID=pt.PID WHERE FID=1 AND TxnID NOT IN (SELECT TxnID FROM admin_2permitmonthlysub);';
			$stmt = $link->query($sql); $row=$stmt->fetchAll();
		
			foreach($row AS $field){
			
			$min=1; $max=12;
			while ($min<=$max){
				$sqlinsert='INSERT INTO admin_2permitmonthlysub SET MonthNo='.$min.',EncodedByNo='.$_SESSION['(ak0)'].',DueDate="'.date('Y-'.$min.'-01').'",TxnID='.$field['TxnID'].',Status=0;';
				
				$stmt=$link->prepare($sqlinsert); $stmt->execute();
				$min++;
			}
			
			}
			
			echo 'Data successfully generated.';
		}
		
		
	
	break;
	
	case 'PostMonthlyPermit':
	
	$id=intval($_GET['TxnSubID']);
	$sqlupdate='Update admin_2permitmonthlysub SET Status=IF(Status=1,0,1),EncodedByNo='.$_SESSION['(ak0)'].',Timestamp=NOW() WHERE TxnMonthlySubID='.$id.';';
	$stmt=$link->prepare($sqlupdate); $stmt->execute();
	
	header("Location:permits.php?w=PermitDetails&TxnID=".$_GET['TxnID']."");
	break;
	
	case 'EditMonthlyPermit':
	$id=intval($_GET['TxnSubID']);
	$sql='SELECT DueDate FROM `admin_2permitmonthlysub` WHERE TxnMonthlySubID='.$id.'';
		$stmt = $link->query($sql); $row=$stmt->fetch();
		
	
	echo '<form action="permits.php?w=EditMonthlyPermit&TxnID='.$_GET['TxnID'].'&TxnSubID='.$id.'" method="POST">DueDate: <input type="date" name="duedate" value="'.$row['DueDate'].'"><input type="submit" value="Edit" name="btnEdit"></form>';
	
	if(isset($_POST['btnEdit'])){
		$sqlupdate='Update admin_2permitmonthlysub SET DueDate="'.$_POST['duedate'].'",EncodedByNo='.$_SESSION['(ak0)'].',Timestamp=NOW() WHERE TxnMonthlySubID='.$id.';';
		// echo $sqlupdate; exit();
		$stmt=$link->prepare($sqlupdate); $stmt->execute();
		header("Location:permits.php?w=PermitDetails&TxnID=".$_GET['TxnID']."");
	}
	
	break;
}

 
?>