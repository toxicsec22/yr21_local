<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; include_once('../switchboard/contents.php');

	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc"; 
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$showbranches=false; $which=$_GET['which'];  $method='POST';

include_once('monitorinvlinks.php');

switch($which){

	case 'TransferToCentralWarehouse':
	
	 if (!allowedToOpen(784,'1rtc')) { echo 'No permission'; exit;}
	 
	$txnid = intval($_GET['TxnID']);
	echo '<style style="text/css">
                        .hoverTable tr:hover {
                        background-color: #FFFFCC;
                }</style>';
	
	
	$title='Transfer to Central';
	
	$txnid = intval($_GET['TxnID']);
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br/>';
	
	$sqlhead='SELECT sm.*,SupplierName,txndesc,Branch FROM monitor_2fromsuppliermain sm JOIN `1suppliers` s ON sm.SupplierNo=s.SupplierNo JOIN `invty_0txntype` tt ON sm.InvType=tt.txntypeid JOIN `1branches` b ON sm.BranchNo=b.BranchNo WHERE sm.TxnID='.$txnid.'';
	$stmthead=$link->query($sqlhead);
	$rowhead=$stmthead->fetch();
	echo '<div style="display:inline-block;width:60%;"><div style="float:left;">';
	echo '<a href="monitorinvsummary.php?which=TransferSummary&BranchNo='.$rowhead['BranchNo'].'">View Transferred</a><br/><br/>';
	echo '<b>Supplier:</b> '. $rowhead['SupplierName'] .'<br/>';
	echo '<b>Branch:</b> ' . $rowhead['Branch'] . '<br/>';
	echo '<b>Inventory Type:</b> ' . $rowhead['txndesc'] . '<br/>';
	echo '<b>Remarks:</b> ' . $rowhead['Remarks'] . '<br/><br/>';
	echo '</div><div>';
	include_once('monitorinvnum.php');
	echo '</div>';
	
	$sql='SELECT ss.*, CEIL(SeriesFrom/50)*50 AS SeriesTo, txndesc AS InvType FROM monitor_2fromsuppliersub ss JOIN `invty_0txntype` tt ON ss.Invtype=tt.txntypeid WHERE ss.TxnID='.$txnid.' AND TransferredToCentralWarehouse=0 ORDER By BookletNo;';
	$stmt=$link->query($sql);
	
	
	echo '<form action="monitorinvsteps.php?which=Transfer" method="POST">';
	
	echo '<table class="hoverTable" style="border:1px solid;">';
	echo '<thead><tr><th>SeriesFrom</th><th>SeriesTo</th><th>BookletNo</th><th>InvType</th><th>Select</th></tr></thead>';
		while ($row = $stmt->fetch())
		{
            echo '<tr><td>'.$row['SeriesFrom'].'</td><td>'.$row['SeriesTo'].'</td><td>'.$row['BookletNo'].'</td><td>'.$row['InvType'].'</td><td><input type="checkbox" name="transfer[]" value="'.$row['TxnSubId'].'"/></td></tr>';
        }
	echo '</table>';
	echo '<br/><input type="hidden" value="'.$rowhead['BranchNo'].'" name="BranchNo"/><input type="submit" value="Tranfer To Central"/>';
	echo '</form>';
	
	break; 
	
	
	case 'Transfer':
          
            if (isset($_REQUEST['transfer'])){
	$transimp = implode(',', $_REQUEST['transfer']);
	$trans = explode(',', $transimp);

	foreach ($trans as $tran)
	{
		$sql = "UPDATE monitor_2fromsuppliersub SET TransferredToCentralWarehouse=1, DateTransferred=Now(), TransferredByNo=".$_SESSION['(ak0)'].", TransferredTS=Now() WHERE TxnSubId=?"; 
		$statement = $link->prepare($sql);
		$statement->execute(array($tran));
		
	}
	// break;
	header('Location:monitorinvsummary.php?which=TransferSummary&BranchNo='.$_POST['BranchNo']);
}
else
{
	echo 'Please select at least 1 series.';
}
            
    break;
	
	
	case 'IssueToBranch':
	if (!allowedToOpen(78832,'1rtc') AND !allowedToOpen(78831,'1rtc')) { echo 'No permission'; exit;}
	$txnid = intval($_GET['TxnID']);
	echo '<style style="text/css">
                        .hoverTable tr:hover {
                        background-color: #FFFFCC;
                }</style>';
	
	
	$title='Issue to Branch';
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br/>';
	
	$sqlhead='SELECT sm.*,SupplierName,txndesc,Branch FROM monitor_2fromsuppliermain sm JOIN `1suppliers` s ON sm.SupplierNo=s.SupplierNo JOIN `invty_0txntype` tt ON sm.InvType=tt.txntypeid JOIN `1branches` b ON sm.BranchNo=b.BranchNo WHERE sm.TxnID='.$txnid.'';
	$stmthead=$link->query($sqlhead);
	$rowhead=$stmthead->fetch();
	
	echo '<a href="monitorinvsummary.php?which=IssuanceSummary&BranchNo='.$rowhead['BranchNo'].'">View Issued</a><br/><br/>';
	
	echo '<b>Supplier:</b> '. $rowhead['SupplierName'] .'<br/>';
	echo '<b>Owned Branch:</b> ' . $rowhead['Branch'] . '<br/>';
	echo '<b>Inventory Type:</b> ' . $rowhead['txndesc'] . '<br/>';
	echo '<b>Remarks:</b> ' . $rowhead['Remarks'] . '<br/><br/>';
	
	$sql='SELECT ss.*, CEIL(SeriesFrom/50)*50 AS SeriesTo, txndesc AS InvType FROM monitor_2fromsuppliersub ss JOIN `invty_0txntype` tt ON ss.Invtype=tt.txntypeid WHERE ss.TxnID='.$txnid.' AND TransferredToCentralWarehouse=1 AND (DateIssued IS NULL OR DateIssued="0000-00-00")';
	$stmt=$link->query($sql);
	
	//pseudo
	$sql2='SELECT GROUP_CONCAT("\'",BranchNo,"\'") AS Pseudobranch FROM 1branches WHERE Pseudobranch=1;';
	$stmt2=$link->query($sql2);
	$row2 = $stmt2->fetch();
	
	//main branch
	$sql3='SELECT GROUP_CONCAT("\'",BranchNo,"\'") AS MainBranch FROM 1companies c JOIN 1branches b ON c.RepBranchNo=b.BranchNo WHERE c.Active<>0;';
	$stmt3=$link->query($sql3);
	$row3 = $stmt3->fetch();
	
	echo '<table class="hoverTable" style="border:1px solid;">';
	echo '<thead><tr><th>SeriesFrom</th><th>SeriesTo</th><th>BookletNo</th><th>InvType</th><th>IssueRemarks</th><th>Issue to Branch</th><th>Select</th></tr></thead>';
	
	while ($row = $stmt->fetch())
	{
		echo '<form action="monitorinvsteps.php?which=Issue" method="POST">';
		// $sql1='SELECT BranchNo, Branch from `1branches` where Active<>0 '.(allowedToOpen(78831,'1rtc')?(in_array($rowhead['BranchNo'],array($row2['Pseudobranch']))?($rowhead['InvType']==29?' AND CompanyNo=(SELECT CompanyNo FROM 1branches WHERE BranchNo='.$rowhead['BranchNo'].')':''):' AND BranchNo='.intval($rowhead['BranchNo']).''):'').' ORDER BY Branch';
		$sql1='SELECT BranchNo, Branch from `1branches` where Active<>0 '.(allowedToOpen(78831,'1rtc')?((strpos($row2['Pseudobranch'], ''.$rowhead['BranchNo'].'') !== false)?($rowhead['InvType']==29?' AND CompanyNo=(SELECT CompanyNo FROM 1branches WHERE BranchNo='.$rowhead['BranchNo'].')':''):' AND BranchNo='.intval($rowhead['BranchNo']).''.(((strpos($row3['MainBranch'], ''.$rowhead['BranchNo'].'') !== false) AND ($rowhead['InvType']==2 OR $rowhead['InvType']==29))?' UNION SELECT BranchNo, Branch FROM 1branches where Active<>0 AND Pseudobranch=1 AND BranchNo<>95':'').''):' ORDER BY Branch').''; //echo $sql1;
		$stmt1 = $link->query($sql1);
	
		$branchlist = '<select name="BranchNo">';
		while($row1= $stmt1->fetch()) {
			if ($rowhead['Branch']==$row1['Branch']) {
				$branchlist .= '<option value="'.$row1['BranchNo'].'" selected>'.$row1['Branch'].'</option>';
			} else {
				$branchlist .= '<option value="'.$row1['BranchNo'].'">'.$row1['Branch'].'</option>';
			}
		}
		$branchlist .= '</select>';
		
		echo '<tr><td>'.$row['SeriesFrom'].'</td><td>'.$row['SeriesTo'].'</td><td>'.$row['BookletNo'].'</td><td>'.$row['InvType'].'</td><td><input type="text" name="IssueRemarks" value="'.$row['IssueRemarks'].'" size="25"></td><td>'.$branchlist.'</td><td><input type="hidden" name="TxnSubId" value="'.$row['TxnSubId'].'"/><input type="submit" value="Issue"/></td></tr></form>';
	}
	echo '</table>';
	
	break; 
	
	
	case 'Issue':
        if (isset($_POST['TxnSubId'])){
			
		$sql = "UPDATE monitor_2fromsuppliersub SET IssueRemarks='".addslashes($_POST['IssueRemarks'])."',DateIssued=Now(), IssuedTo='".$_POST['BranchNo']."', IssuedByNo=".$_SESSION['(ak0)'].", IssuedTS=Now() WHERE TxnSubId=".$_POST['TxnSubId'].";";
		// echo $sql; exit();
		$statement = $link->prepare($sql);
		$statement->execute();
		
		header("Location:".$_SERVER['HTTP_REFERER']);
	}
	else
	{
		echo 'Please select at least 1 series.';
	}
    break;
	
	
	case 'AcceptByBranch':
	//Allowed To Open Condition Here
	if ((!allowedToOpen(781,'1rtc')) AND (!allowedToOpen(78835,'1rtc'))) { echo 'No permission'; exit;}
	$txnid = intval($_GET['TxnID']);
	echo '<style style="text/css">
                        .hoverTable tr:hover {
                        background-color: #FFFFCC;
                }</style>';
	
	
	$title='Accept By Branch'; 
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br/>';
	
	$sqlhead='SELECT sm.*,SupplierName,txndesc,Branch FROM monitor_2fromsuppliermain sm JOIN `1suppliers` s ON sm.SupplierNo=s.SupplierNo JOIN `invty_0txntype` tt ON sm.InvType=tt.txntypeid JOIN `1branches` b ON sm.BranchNo=b.BranchNo WHERE sm.TxnID='.$txnid.'';
	$stmthead=$link->query($sqlhead);
	$rowhead=$stmthead->fetch();
	
	echo '<a href="monitorinvsummary.php?which=AcceptSummary&BranchNo='.$rowhead['BranchNo'].'">View Accepted</a><br/><br/>';
	
	echo '<b>Supplier:</b> '. $rowhead['SupplierName'] .'<br/>';
	echo '<b>Branch:</b> ' . $rowhead['Branch'] . '<br/>';
	echo '<b>Inventory Type:</b> ' . $rowhead['txndesc'] . '<br/>';
	echo '<b>Remarks:</b> ' . $rowhead['Remarks'] . '<br/><br/>';
	
	$sql='SELECT ss.*, txndesc AS InvType FROM monitor_2fromsuppliersub ss JOIN `invty_0txntype` tt ON ss.Invtype=tt.txntypeid JOIN `monitor_2fromsuppliermain` sm ON ss.TxnID=sm.TxnID WHERE ss.TxnID='.$txnid.' AND (DateIssued IS NOT NULL AND DateIssued<>"0000-00-00")AND BranchNo = '.$_SESSION['bnum'].' AND (DateAccepted IS NULL OR DateAccepted="0000-00-00")';
	$stmt=$link->query($sql);
	
	
	
	
	echo '<table class="hoverTable" style="border:1px solid;">';
	echo '<thead><tr><th>SeriesFrom</th><th>BookletNo</th><th>InvType</th><th></th></tr></thead>';
		while ($row = $stmt->fetch())
		{
			echo '<form action="monitorinvsteps.php?which=Accept" method="POST">';
            echo '<tr><td>'.$row['SeriesFrom'].'</td><td>'.$row['BookletNo'].'</td><td>'.$row['InvType'].'</td><td><input type="hidden" name="TxnSubId" value="'.$row['TxnSubId'].'"/><input type="submit" value="Accept"/></td></tr>';
				echo '</form>';
        }
	echo '</table>';

	
	break; 
	
	
	case 'Accept':
            if (isset($_REQUEST['TxnSubId'])){
			
			$sql = "UPDATE monitor_2fromsuppliersub SET DateAccepted=Now(), AcceptedByNo=".$_SESSION['(ak0)'].", AcceptedTS=Now() WHERE TxnSubId=".$_GET['TxnSubId']." AND IssuedTo = ".$_SESSION['bnum']."";
			$statement = $link->prepare($sql);
			$statement->execute();
			header("Location:monitorinvsummary.php?which=AcceptSummary");
	}
	else
	{
		echo 'Please select at least 1 series.';
	}
    break;
	
	case 'SpecialTransferToBranch':
	//Allowed To Open Condition Here
	if (!allowedToOpen(784,'1rtc')) { echo 'No permission'; exit;}
	$txnid = intval($_GET['TxnID']);
	echo '<style style="text/css">
                        .hoverTable tr:hover {
                        background-color: #FFFFCC;
                }</style>';
	
	
	$title='Special Transfer To Branch'; 
	echo '<title>'.$title.'</title>';
	echo '<h3>'.$title.'</h3><br/>';
	
	$sqlhead='SELECT sm.*,SupplierName,txndesc,Branch FROM monitor_2fromsuppliermain sm JOIN `1suppliers` s ON sm.SupplierNo=s.SupplierNo JOIN `invty_0txntype` tt ON sm.InvType=tt.txntypeid JOIN `1branches` b ON sm.BranchNo=b.BranchNo WHERE sm.TxnID='.$txnid.'';
	$stmthead=$link->query($sqlhead);
	$rowhead=$stmthead->fetch();
	
	// echo '<a href="monitorinvsummary.php?which=AcceptSummary&BranchNo='.$rowhead['BranchNo'].'">View Accepted</a><br/><br/>';
	
	echo '<b>Supplier:</b> '. $rowhead['SupplierName'] .'<br/>';
	echo '<b>Branch:</b> ' . $rowhead['Branch'] . '<br/>';
	echo '<b>Inventory Type:</b> ' . $rowhead['txndesc'] . '<br/>';
	echo '<b>Remarks:</b> ' . $rowhead['Remarks'] . '<br/><br/>';
	
	
	$sql='SELECT ss.*,SpecialRemarks, txndesc AS InvType FROM monitor_2fromsuppliersub ss JOIN `invty_0txntype` tt ON ss.Invtype=tt.txntypeid JOIN `monitor_2fromsuppliermain` sm ON ss.TxnID=sm.TxnID WHERE ss.TxnID='.$txnid.' AND (DateIssued IS NOT NULL AND DateIssued<>"0000-00-00") AND BranchNo = '.$_SESSION['bnum'].' AND (DateAccepted  IS NOT NULL AND DateAccepted<>"0000-00-00")';
	$stmt=$link->query($sql);
	
	
	echo '<table class="hoverTable" style="border:1px solid;">';
	echo '<thead><tr><th>SeriesFrom</th><th>BookletNo</th><th>InvType</th><th>SpecialRemarks</th><th></th></tr></thead>';
		while ($row = $stmt->fetch())
		{
			$sql1='SELECT BranchNo, Branch from `1branches` where Active<>0 ORDER BY Branch';
			$stmt1 = $link->query($sql1);
		
			$branchlist = '<td><select name="BranchNo">';
	
			while($row1= $stmt1->fetch()) {
			
				if ($rowhead['Branch']==$row1['Branch']) {
					$branchlist .= '<option value="'.$row1['BranchNo'].'" selected>'.$row1['Branch'].'</option>';
				} else {
					$branchlist .= '<option value="'.$row1['BranchNo'].'">'.$row1['Branch'].'</option>';
				}
			}
			$branchlist .= '</select></td>';
			
			echo '<form action="monitorinvsteps.php?which=SpecTrans" method="POST">';
            echo '<tr><td>'.$row['SeriesFrom'].'</td><td>'.$row['BookletNo'].'</td><td>'.$row['InvType'].'</td><td><input type="text" name="SpecialRemarks" value="'.$row['SpecialRemarks'].'" size="25"></td><td>'.$branchlist.'</td><td><input type="hidden" name="TxnSubId" value="'.$row['TxnSubId'].'"/><input type="submit" value="Special Transfer"/></td></tr>';
				echo '</form>';
        }
	echo '</table>';

	
	break; 
	
	case 'SpecTrans':
            if (isset($_REQUEST['TxnSubId'])){
			
			$sql1='SELECT TxnID, IssuedTo, AcceptedTS FROM monitor_2fromsuppliersub WHERE TxnSubId='.$_POST['TxnSubId'];
			$stmt1 = $link->query($sql1);
			$row1= $stmt1->fetch(); 
			
			//insert into semi trail
			$sql='INSERT INTO `monitor_2specialtransfer` SET TxnID='.$row1['TxnID'].', TxnSubId='.$_REQUEST['TxnSubId'].', AcceptedByBranchNo='.$row1['IssuedTo'].', AcceptedTS="'.$row1['AcceptedTS'].'", SpecTransferredToBranchNo='.$_POST['BranchNo'].', SpecTransferredByNo='.$_SESSION['(ak0)'].', SpecTransferredTS=Now();';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
		
			
			$sql = "UPDATE monitor_2fromsuppliersub SET DateIssued=Now(), IssuedTo='".$_POST['BranchNo']."', IssuedByNo=".$_SESSION['(ak0)'].", IssuedTS=Now(),SpecialRemarks='".addslashes($_POST['SpecialRemarks'])."', DateAccepted=NULL, AcceptedByNo=NULL, AcceptedTS=NULL WHERE TxnSubId=".$_POST['TxnSubId'];
			$statement = $link->prepare($sql);
			$statement->execute();
			
			header("Location:".$_SERVER['HTTP_REFERER']);
		
	}
	else
	{
		echo 'Please select at least 1 series.';
	}
    break;
}

  $stmt=null; 


?>