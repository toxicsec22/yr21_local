<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;


include_once "../generalinfo/lists.inc"; include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
 $showbranches=false; $which=$_GET['which'];  $method='POST';
if ($_GET['which']!='ProcessFlow')
{
	include_once('monitorinvlinks.php');
} else {
	include_once('../backendphp/layout/linkstyle.php');
	include_once('../switchboard/contents.php');
}


switch($which){

case 'TransferSummary':
if (!allowedToOpen(7882,'1rtc')) { echo 'No permission'; exit;}
echo '<form method="POST" action="monitorinvsummary.php?which=TransferSummary">
	Date From: <input type="date" name="sDate" value="'.date('Y-m-d').'"/>
	Date To: <input type="date" name="eDate" value="'.date('Y-m-d').'">';
	
	$sql='SELECT BranchNo, Branch from `1branches` where Active<>0 ORDER BY Branch';
	$stmt = $link->query($sql);
	
	echo ' Branch: <select name="Branch">';
	echo '<option value="All">All</option>';
	while($row= $stmt->fetch()) {
		echo '<option value="'.$row['BranchNo'].'">'.$row['Branch'].'</option>';
	}
	echo '</select>';
	echo '
	<input type="submit" name="btnFilter" value="Filter">
</form>';


if (isset($_POST['btnFilter'])){
	if ($_POST['Branch']=="All"){
		$branchsql = '';
		$br = 'All';
	} else {
		$branchsql = ' AND fm.BranchNo='.$_POST['Branch'];
		$br = comboBoxValue($link,'`1branches`','BranchNo',$_POST['Branch'],'Branch');
	}
	$condi = ' WHERE DateTransferred>="'.$_POST['sDate'].'" AND DateTransferred<="'.$_POST['eDate'].'" AND TransferredToCentralWarehouse=1 '.$branchsql.'';
	echo  '<br><h3>From: '.$_POST['sDate']; echo ', To: '.$_POST['eDate']; echo ', Branch: '.$br. '</h3>'; 
	echo '<br/><a href="monitorinvsummary.php?which=PrintTransferSummary&sDate='.$_POST['sDate'].'&eDate='.$_POST['eDate'].'&BranchNo='.$_POST['Branch'].'" target="_blank">Print a Copy?</a></br>';
}
else {
	if (isset($_GET['BranchNo'])){
		$condi = ' WHERE fm.BranchNo='.intval($_GET['BranchNo']).' AND TransferredToCentralWarehouse=1';
	} else {
		$condi = '';
	}
}
    $title='Transfer Summary';
	$formdesc='';
    $columnnames=array('DateTransferred','Branch', 'InvoiceType', 'SeriesFrom', 'SeriesTo','IssuedToBranch');  
$sql='SELECT `DateTransferred`,b.`Branch`,b2.Branch AS IssuedToBranch, `Remarks`, `txndesc` as `InvoiceType`, 
SeriesFrom, CEIL(SeriesFrom/50)*50 AS SeriesTo,
concat(`Nickname`," ",`SurName`) as `IssuedBy`, fm.`TimeStamp`, fm.TxnID FROM `monitor_2fromsuppliermain` fm
JOIN `monitor_2fromsuppliersub` fs ON fm.TxnID=fs.TxnID
JOIN `1branches` b ON b.BranchNo=fm.BranchNo
LEFT JOIN `1branches` b2 ON b2.BranchNo=fs.IssuedTo
JOIN `1employees` e ON e.IDNo=fs.TransferredByNo
JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType'.$condi.' 
GROUP BY DateTransferred,fm.BranchNo,InvoiceType,SeriesFrom
ORDER BY DateTransferred,Branch,InvoiceType,SeriesFrom;'; 
// echo $sql;
$txnid='TxnID'; 
// $fieldname='TxnID';
$editprocess='fromsupplier.php?which=ListReceived&TxnID=';
$editprocesslabel='Lookup';
// $title='';
include_once('../backendphp/layout/displayastable.php');
// include_once('../backendphp/layout/clickontabletoeditbody.php');
    break;
	
case 'IssuanceSummary':

if (!allowedToOpen(787,'1rtc')) { echo 'No permission'; exit;}

if (isset($_GET['BranchNo'])){
	$condi = ' WHERE fm.BranchNo='.intval($_GET['BranchNo']).' AND TransferredToCentralWarehouse=1 AND DateIssued IS NOT NULL';
} else {
	$condi = '';
}
    $title='Issuance Summary'; $formdesc='';
    $columnnames=array('DateIssued','Branch', 'InvoiceType', 'SeriesFrom', 'SeriesTo','IssueRemarks');  
$sql='SELECT `DateIssued`,`IssueRemarks`,`Branch`, `Remarks`, `txndesc` as `InvoiceType`, 
SeriesFrom, CEIL(SeriesFrom/50)*50 AS SeriesTo,
concat(`Nickname`," ",`SurName`) as `IssuedBy`, fm.`TimeStamp`, fm.TxnID FROM `monitor_2fromsuppliermain` fm
JOIN `monitor_2fromsuppliersub` fs ON fm.TxnID=fs.TxnID
JOIN `1branches` b ON b.BranchNo=IssuedTo
JOIN `1employees` e ON e.IDNo=fs.IssuedByNo
JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType'.$condi.' 
GROUP BY DateIssued,fm.BranchNo,InvoiceType,SeriesFrom
ORDER BY DateIssued,Branch,InvoiceType,SeriesFrom;'; 
$width='55%';
include_once('../backendphp/layout/displayastable.php');
    break;
	
	
	case 'AcceptSummary':
	
	if ((!allowedToOpen(782,'1rtc')) AND (!allowedToOpen(78835,'1rtc'))) { echo 'No permission'; exit;}
	
if (isset($_GET['BranchNo'])){
	$condi = ' WHERE fm.BranchNo='.intval($_GET['BranchNo']).' AND TransferredToCentralWarehouse=1 AND DateIssued IS NOT NULL AND DateAccepted IS NOT NULL';
} else if (allowedToOpen(78835,'1rtc')){
	$condi = ' WHERE fs.IssuedTo IN (SELECT BranchNo FROM 1branches WHERE Pseudobranch=1)';
} else if (allowedToOpen(783,'1rtc')){
	$condi = ' ';
} else {
	$condi = ' WHERE fs.IssuedTo='.$_SESSION['bnum'];
}

    $title='Accept Summary'; $formdesc='';
    $columnnames=array('DateAccepted','OwnedBy','BranchorComp', 'Remarks', 'InvoiceType', 'SeriesFrom', 'SeriesTo');  
$sql='SELECT `DateAccepted`,b2.`Branch` AS OwnedBy,b.`Branch` AS BranchorComp, `Remarks`, `txndesc` as `InvoiceType`, 
SeriesFrom, CEIL(SeriesFrom/50)*50 AS SeriesTo,
concat(`Nickname`," ",`SurName`) as `AcceptedBy`, fm.`TimeStamp`, fm.TxnID, fs.TxnSubId FROM `monitor_2fromsuppliermain` fm
JOIN `monitor_2fromsuppliersub` fs ON fm.TxnID=fs.TxnID
JOIN `1branches` b ON b.BranchNo=fs.IssuedTo
JOIN `1branches` b2 ON b2.BranchNo=fm.BranchNo
JOIN `1employees` e ON e.IDNo=fs.AcceptedByNo
JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType'.$condi.' 
GROUP BY DateAccepted,fm.BranchNo,InvoiceType,SeriesFrom
ORDER BY DateAccepted,b.Branch,InvoiceType,SeriesFrom;'; //echo $sql;
$txnid='TxnID'; $fieldname='TxnID';
// $process1='acceptbybranch.php?which=ListAcceptedFromSummary&';
// $processlabel1='Lookup';
// $title=''; 
// include_once('../backendphp/layout/clickontabletoeditbody.php');
include_once('../backendphp/layout/displayastable.php');
    break;
	
	case 'SpecialTransferHistory':
	
	if (!allowedToOpen(783,'1rtc')) { echo 'No permission'; exit; }
    $title='Special Transfer History'; $formdesc='';
    $columnnames=array('AcceptedByBranch', 'TransferredToBranch', 'BookletNo', 'InvoiceType', 'SpecialRemarks','SpecTransferredTS', 'SpecTransferredBy');
	 
	$sql='SELECT b.Branch AS AcceptedByBranch, b2.Branch AS TransferredToBranch, BookletNo,txndesc AS InvoiceType,SpecTransferredTS,SpecialRemarks, CONCAT(Nickname," ",Surname) AS SpecTransferredBy  FROM monitor_2specialtransfer st JOIN monitor_2fromsuppliersub fs ON st.TxnSubId=fs.TxnSubId JOIN 1branches b ON st.AcceptedByBranchNo=b.BranchNo JOIN 1branches b2 ON st.SpecTransferredToBranchNo=b2.BranchNo JOIN 1_gamit.0idinfo id ON st.SpecTransferredByNo=id.IDNo JOIN monitor_2fromsuppliermain sm ON sm.TxnID=st.TxnID JOIN invty_0txntype tt ON sm.InvType=tt.txntypeid ORDER BY SpecTransferredTS DESC;'; 
	$txnid='TxnID'; $fieldname='TxnID';
	include_once('../backendphp/layout/displayastable.php');
	
    break;
	
	
	
	case 'PrintTransferSummary':
	
	if (!allowedToOpen(7882,'1rtc')) { echo 'No permission'; exit;}
	$title = 'Transferred Invoices';
	echo '<h3><a href="#" onClick="window.print();">'.$title.'</a></h3>';
	if ($_GET['BranchNo']=='All'){
		$condi = ' WHERE DateTransferred>="'.$_GET['sDate'].'" AND DateTransferred<="'.$_GET['eDate'].'" AND TransferredToCentralWarehouse=1';
	}
	else {
		$condi = ' WHERE DateTransferred>="'.$_GET['sDate'].'" AND DateTransferred<="'.$_GET['eDate'].'" AND TransferredToCentralWarehouse=1 AND fm.BranchNo='.$_GET['BranchNo'].'';
	}
	
	$sql='SELECT `DateTransferred`, `Branch`,`BookletNo`, `Remarks`, `txndesc` as `InvoiceType`, 
SeriesFrom, CEIL(SeriesFrom/50)*50 AS SeriesTo,
concat(`Nickname`," ",`SurName`) as `IssuedBy`, fm.`TimeStamp`, fm.TxnID FROM `monitor_2fromsuppliermain` fm
JOIN `monitor_2fromsuppliersub` fs ON fm.TxnID=fs.TxnID
JOIN `1branches` b ON b.BranchNo=fm.BranchNo
JOIN `1employees` e ON e.IDNo=fs.TransferredByNo
JOIN `invty_0txntype` tt ON tt.txntypeid=fm.InvType'.$condi.' 
GROUP BY DateTransferred,fm.BranchNo,InvoiceType,SeriesFrom
ORDER BY DateTransferred,Branch,BookletNo,InvoiceType,SeriesFrom;';

$stmt=$link->query($sql);
echo '<table border="1px solid;" style="border-collapse: collapse;"><tr><th>DateTransferred</th><th>SeriesFrom</th><th>SeriesTo</th><th>BookletNo</th><th>Branch</th><th>InvoiceType</th></tr>';
	while($row= $stmt->fetch()) {
		echo '<tr>';
		echo '<td>'.$row['DateTransferred'].'</td>'.'<td>'.$row['SeriesFrom'].'</td>'.'<td>'.$row['SeriesTo'].'</td><td>'.$row['BookletNo'].'</td><td>'.$row['Branch'].'</td><td>'.$row['InvoiceType'].'</td>';
		echo '</tr>';
	}
	
echo '</table>';

echo '<br/><br/>Transferred By: '.comboBoxValue($link,'attend_30currentpositions','IDNo',$_SESSION['(ak0)'],'FullName'); echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Transferred To: Central Warehouse';
    break;
	
	
	case 'ProcessFlow':
	
	if (!allowedToOpen(7883,'1rtc')) { echo 'No permission'; exit;}
	$title = 'Process Flow';
	echo '<title>'.$title.'</title>';
	
	$imglink = '<img src="arrow.png" alt="next-step" width="50" height="50"/><br/>';
	
	?>
	<center>
	<div style="display:inline-block;">
	
	<div style="float:left;">
	
	<h3>Monitoring of Printed Invoices Flowchart</h3><br/><br/>
	
	<?php
		$starta = '<a style="color:blue;"';
		$startspan = '<span style="color:red;"';
	?>
	
	<h4>From Supplier</h4><br> 
	<?php if (allowedToOpen(784,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
	
	<?php echo $starttag;?> id='link' href="fromsupplier.php?which=ReceiveNewPrint">Receive Newly Printed Invoices<?php echo $endtag;?><br/><br/><?php echo $imglink;?><h4>Admin Dept</h4><br/>
	
	<?php } if (allowedToOpen(783,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
        <?php echo $starttag;?> id='link' href="fromsupplier.php?which=ReceivedSummary">Summary Of Received Invoices<?php echo $endtag;?>
		
		
		<?php } if (allowedToOpen(7882,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
        <?php echo $starttag;?> id='link' href="monitorinvsummary.php?which=TransferSummary">Transfer Summary<?php echo $endtag;?><br/>
		
		<br><?php echo $imglink;?><h4>Central Warehouse</h4>
		<br/>
		
		
	 <?php //} if (allowedToOpen(788,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>	
	 <?php //} if (allowedToOpen(78831,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>	
	<?php } if (allowedToOpen(78831,'1rtc') OR allowedToOpen(78832,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>	
		<?php echo $starttag;?> id='link' href="fromsupplier.php?which=ReceivedSummaryCentral">Issue To Branch<?php echo $endtag;?>
		
		<?php } if (allowedToOpen(787,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
        <?php echo $starttag;?> id='link' href="monitorinvsummary.php?which=IssuanceSummary">Issuance Summary<?php echo $endtag;?><br/><br/><?php echo $imglink;?><h4>Branch</h4>
		<br/>
		
	<?php } if (allowedToOpen(781,'1rtc') OR allowedToOpen(78835,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
	<?php echo $starttag;?> id='link' href="fromsupplier.php?which=ReceivedSummaryBranch">Accept Printed Invoices<?php echo $endtag;?>
	
	<?php } if ((allowedToOpen(782,'1rtc')) OR (allowedToOpen(78835,'1rtc'))){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
        <?php echo $starttag;?> id='link' href="monitorinvsummary.php?which=AcceptSummary">Accept Summary<?php echo $endtag;?><br/><br/><br/><br/>
		
	<?php } if (allowedToOpen(78832,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
        <?php echo $starttag;?> id='link' href="fromsupplier.php?which=SpecialTransfer">Special Transfer Case<?php echo $endtag;?> 
		
	<?php } if (allowedToOpen(78832,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
        <?php echo $starttag;?> id='link' href="monitorinvsummary.php?which=SpecialTransferHistory">Special Transfer History<?php echo $endtag;?><br/><br/><br/><br/>
		
	<?php } if (allowedToOpen(786,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
	<?php echo $starttag;?> id='link' href="invoicesonhand.php?which=OnStock">Ending Invty of Invoices On Stock<?php echo $endtag;?>
	
	<?php } if (allowedToOpen(785,'1rtc')){ $starttag = $starta; $endtag = '</a>'; } else { $starttag = $startspan; $endtag = '</span>'; } {?>
     <?php echo $starttag;?> id='link' href="invoicesonhand.php?which=LastSeries">Latest Series At Branch<?php echo $endtag;?>
	 
	<?php } nolinks:?>
	
	
    </div>
	
	<div align="left" style="margin-left:120px;float:left;">
	<br/><br/><br/><h3>Process</h3><br/><br/>
	<ol>
		<li>Admin Dept encodes newly received invoices.</li>
		<li>Admin Dept transfers invoices to Central Warehouse.</li>
		<li>Central Warehouse issues invoices to Branch.</li>
		<li>Branch accepts the issued invoices.</li>
		
	</ol>
	<br>Special Case (can transfer from branch to branch).
	</div>
	</div>
	</center>
	
	<?php
	
    break;
	
	
}
  $stmt=null;

?>