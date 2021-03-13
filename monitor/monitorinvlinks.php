<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if($_GET['which']!="PrintTransferSummary") { include_once('../switchboard/contents.php'); } else {
	goto nolinks;
}

	include_once('../backendphp/functions/editok.php');
	include_once "../generalinfo/lists.inc"; 
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	include_once('../backendphp/layout/linkstyle.php');
 $showbranches=false; $which=$_GET['which'];  $method='POST';
?>
<br>
<div id="section" style="display: block;">

    <div>
	<?php if (allowedToOpen(784,'1rtc')){ ?>
	<a id='link' href="fromsupplier.php?which=ReceiveNewPrint">Receive Newly Printed Invoices</a>
	
	<?php } if (allowedToOpen(783,'1rtc')){ ?>
        <a id='link' href="fromsupplier.php?which=ReceivedSummary">Summary Of Received Invoices</a>
		
		<?php } if (allowedToOpen(7882,'1rtc')){ ?>
        <a id='link' href="monitorinvsummary.php?which=TransferSummary">Transfer Summary</a>
		
	<?php } if (allowedToOpen(78831,'1rtc') OR allowedToOpen(78832,'1rtc')){ ?>	
		<a id='link' href="fromsupplier.php?which=ReceivedSummaryCentral">Issue To Branch</a>
		
		<?php } if (allowedToOpen(787,'1rtc')){ ?>
        <a id='link' href="monitorinvsummary.php?which=IssuanceSummary">Issuance Summary</a>
		
	<?php } if (allowedToOpen(781,'1rtc')){ ?>
	<a id='link' href="fromsupplier.php?which=ReceivedSummaryBranch">Accept Printed Invoices</a>
	
	<?php } if (allowedToOpen(782,'1rtc')){ ?>
        <a id='link' href="monitorinvsummary.php?which=AcceptSummary">Accept Summary</a><br><br>
		
	<?php } if (allowedToOpen(78832,'1rtc')){ ?>
        <a id='link' href="fromsupplier.php?which=SpecialTransfer">Special Transfer Case</a>
        <a id='link' href="monitorinvsummary.php?which=SpecialTransferHistory">Special Transfer History</a>
		
	<?php //} if (allowedToOpen(786,'1rtc')){?>
	<a id='link' href="invoicesonhand.php?which=OnStock">Ending Invty of Invoices On Stock</a>
	
	<?php //} if (allowedToOpen(785,'1rtc')){ ?>
     <a id='link' href="invoicesonhand.php?which=LastSeries">Latest Series At Branch</a>
	 
	<?php } if (allowedToOpen(785,'1rtc')){ ?>
     <a id='link' href="monitorinvsummary.php?which=ProcessFlow">Process Flow</a>
	 
	<?php } nolinks:?>
    </div><br/><br/>
