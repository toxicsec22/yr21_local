<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

// check if allowed
//$allowed=array(586,587,588,589,590,591);$allow=0;
//foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
//if ($allow==0) { echo 'No permission'; exit;}
//allowed:
// end of check
if (!allowedToOpen(array(586,587,588,589,590,591),'1rtc')) { echo 'No permission'; exit; } 
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

include_once "../generalinfo/lists.inc";

if(empty($_REQUEST['ClientName'])){
      ?>
      <script type="text/javascript">
        alert('No Client Selected');
        window.location = "viewletters.php";
        </script>

    <?php
  }



$sqluser='Select (select group_concat(mobilenumbers separator \' • \') from attend_30currentpositions cp left join 1_gamit.1rtcusers i on i.IDNo=cp.IDNo where PositionID=(select PositionID from attend_30currentpositions where IDNo=\''.$_SESSION['(ak0)'].'\')) as MobileNo,concat(FirstName, \' \',Left(MiddleName,1), \'. \',SurName) as FullName, Position from `1employees` e join `attend_30currentpositions` p on e.IDNo=p.IDNo where e.IDNo='.$_SESSION['(ak0)'];
// echo $sqluser; exit();
$stmt=$link->query($sqluser);
$resultuser=$stmt->fetch();
$user=$resultuser['FullName']; $userposition=$resultuser['Position'];
$whichqry=$_GET['w'];
?>
<html>
<head>
<title>Print SOA and Client Info</title>
<style>

body  
{ 
    /* this affects the margin on the content before sending to printer */ 
    margin: 0px;
    font-size: 10pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
}
table,td {
        border:1px solid black;
border-collapse:collapse;
padding: 3px;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
    }
<?php if($whichqry<>'CreditInformation' AND $whichqry<>'Araw1CreditInfo'){
echo '
@media print  
{
table {
border:1px solid black;
border-collapse:collapse;
padding: 3px;
font-size: 9pt;}
footer {page-break-after: always;}

	#prev { display: none; }
}';

 } else { echo '@media print
{p {page-break-before:always}
div { text-align:justify; }
	q{
		display: none;
	}
	#container{width:100%;}
	 html, body {
			height: 99%;    
}}'; } ?>
img {
  
}
</style>
</head>
<body>
<?php
 



if ($whichqry=='HoldCheckSampleLetter'){
   // goto skipvariables;
}
   $fieldname='ClientName';
   $title='Statement of Account';
   $lookupprocess='printsoa.php?w='.$whichqry;
  $checker = isset($_POST[$fieldname])?2 :1;
if (!isset($_POST[$fieldname])){
 ?> <br><br><br><center>
   <form method="POST" action="<?php echo $lookupprocess ?>" enctype="multipart/form-data">
Client Name<input type="text" name="<?php echo $fieldname ?>" list="allclients" size=40 autocomplete="off" required="true">
<?php
if ($whichqry<>'SendToEmailPreview' and $whichqry<>'SOA' and $whichqry<>'RemindHoldCheck'){
//    ?>
 Choose Company<input type="text" name="Company" list="companies" size=30 autocomplete="off" required="true" value="1Rotary">
 <?php
} // end not SOA and not RemindHoldCheck
?>
 <input type="submit" name="lookup" value="Lookup"></center> 
<?php
renderlist('allclients');renderlist('companies');
	goto noform;

    ?>
    </form>
 <?php

}

// $clientno=!isset($_POST['clientno'])?getValue($link,'1clients','Left(`ClientName`,20)',addslashes($_POST[$fieldname]),'ClientNo'):$_POST['clientno'];

 if($checker == 2){
	 if($whichqry=="SendToEmailPreview"){
		 $clientno=$_REQUEST['ClientName'];
	 } else {
		$clientno=getValue($link,'1clients','Left(`ClientName`,20)',addslashes($_POST['ClientName']),'ClientNo');
	 }
}
if ($whichqry<>'SendToEmailPreview' AND $whichqry<>'SOA' and $whichqry<>'RemindHoldCheck'){
   $companycondition=!isset($_POST['companyno'])?' where `Company`=\''.addslashes($_POST['Company']).'\'':' where CompanyNo='.$_POST['companyno'];
   $sqlco='Select CompanyNo, `CompanyName` as CompanyName from `1companies` '.$companycondition;
   $stmt=$link->query($sqlco);
   $result=$stmt->fetch();
$companyno=$result['CompanyNo'];
$company=$result['CompanyName'];
}
if ($whichqry=='SendToEmailPreview' OR $whichqry=='SOA'){
   
//for PDC section
include('../acctg/ARfunctions.php');

makepdcs($clientno,$link);

// end of PDC temp table 
 
$sql0='CREATE TEMPORARY TABLE Receivables (
InvDate DATE NOT NULL,
ClientNo SMALLINT NOT NULL,
ClientName VARCHAR(100)  NULL,
EmailAddress VARCHAR(100)  NULL,
ContactPerson VARCHAR(100)  NULL,
Particulars VARCHAR(150) NOT NULL, PONo VARCHAR(20) NOT NULL,
SaleAmount DOUBLE NOT NULL,
RcdAmount DOUBLE NULL,
InvBalance DOUBLE NOT NULL,
DaysOverdue smallint(3) NOT NULL,
Terms smallint(3) NOT NULL,
CreditLimit double NOT NULL,
BranchNo smallint(6) NOT NULL,
Branch varchar(25) NOT NULL,
CompanyNo smallint(6) NOT NULL
)

SELECT r.Date as InvDate,
r.ClientNo, c.ClientName, c.EmailAddress,c.ContactPerson, c.TelNo1, c.TelNo2,
r.Particulars,ss.PONo,
round(r.SaleAmount,2) as SaleAmount,
round(r.RcdAmount,2) as RcdAmount,
round(r.InvBalance,2) as InvBalance,
DateDiff(Now(),r.Date)-ifnull(c.Terms,0) as DaysOverdue, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit, r.BranchNo,
b.Branch, b.CompanyNo FROM `acctg_33qrybalperrecpt` as r join `1clients` c on c.ClientNo=r.ClientNo
join `1branches` b on b.BranchNo=r.BranchNo
LEFT JOIN `invty_2sale` ss ON r.ClientNo=ss.ClientNo AND r.BranchNo=ss.BranchNo AND r.Particulars=ss.SaleNo AND ss.PaymentType=2
WHERE c.ClientNo='.$clientno.' and  InvBalance>1
order by r.Date;';
// echo $sql0."<br>";exit();
$stmt=$link->prepare($sql0); $stmt->execute();

$sqlco='Select c.CompanyNo, c.Company, c.CompanyName, c.RegisteredAddress from `1companies` c join Receivables r on c.CompanyNo=r.CompanyNo group by c.CompanyNo';
$stmt=$link->query($sqlco);
$resultco=$stmt->fetchAll();

$sqldataasof='SELECT `DataAsOf` FROM `00staticdataasof` WHERE `ForDB`=1';
$stmtasof=$link->query($sqldataasof);
$dataasof=$stmtasof->fetch();
}
skipvariables:
switch ($whichqry){
   
CASE 'SOA':
 /*    if (!allowedToOpen(591,'1rtc')) { echo 'No permission'; exit; } 
//for PDC section
include('../acctg/ARfunctions.php');
makepdcs($clientno,$link);

// end of PDC temp table 
 
$sql0='CREATE TEMPORARY TABLE Receivables (
InvDate DATE NOT NULL,
ClientNo SMALLINT NOT NULL,
ClientName VARCHAR(100)  NULL,
ContactPerson VARCHAR(100)  NULL,
Particulars VARCHAR(150) NOT NULL, PONo VARCHAR(20) NOT NULL,
SaleAmount DOUBLE NOT NULL,
RcdAmount DOUBLE NULL,
InvBalance DOUBLE NOT NULL,
DaysOverdue smallint(3) NOT NULL,
Terms smallint(3) NOT NULL,
CreditLimit double NOT NULL,
BranchNo smallint(6) NOT NULL,
Branch varchar(25) NOT NULL,
CompanyNo smallint(6) NOT NULL
)

SELECT r.Date as InvDate,
r.ClientNo, c.ClientName, c.ContactPerson, c.TelNo1, c.TelNo2,
r.Particulars,ss.PONo,
round(r.SaleAmount,2) as SaleAmount,
round(r.RcdAmount,2) as RcdAmount,
round(r.InvBalance,2) as InvBalance,
DateDiff(Now(),r.Date)-ifnull(c.Terms,0) as DaysOverdue, ifnull(c.Terms,0) as Terms, ifnull(c.CreditLimit,0) as CreditLimit, r.BranchNo,
b.Branch, b.CompanyNo FROM `acctg_33qrybalperrecpt` as r join `1clients` c on c.ClientNo=r.ClientNo
join `1branches` b on b.BranchNo=r.BranchNo
LEFT JOIN `invty_2sale` ss ON r.ClientNo=ss.ClientNo AND r.BranchNo=ss.BranchNo AND r.Particulars=ss.SaleNo AND ss.PaymentType=2
WHERE c.ClientNo='.$clientno.' and  InvBalance>1
order by r.Date;';
// echo $sql0."<br>";break;
$stmt=$link->prepare($sql0); $stmt->execute();

$sqlco='Select c.CompanyNo, c.Company, c.CompanyName, c.RegisteredAddress from `1companies` c join Receivables r on c.CompanyNo=r.CompanyNo group by c.CompanyNo';
$stmt=$link->query($sqlco);
$resultco=$stmt->fetchAll();

$sqldataasof='SELECT `DataAsOf` FROM `00staticdataasof` WHERE `ForDB`=1';
$stmtasof=$link->query($sqldataasof);
$dataasof=$stmtasof->fetch(); */
echo '<div id="prev"><form action="printsoa.php?w=SendToEmailPreview" method="POST"><input type="hidden" name="ClientName" value="'.$clientno.'"> <input type="submit" name="btnEmail" value="Email to client"></form></div>';
foreach ($resultco as $company){
$letter='<center><img src="../generalinfo/logo/'.$company['Company'].'.png"><br>';
$letter=$letter. '<br><font style="font-size: 80%;">Credit & Collections Office: &nbsp; collections@1rotarytrading.com <br/>(02) 734 07679 •  '.$resultuser['MobileNo'].'</font><br><br>Statement of Account<br><br></center>';
//$letter=$letter. '<br>Accounting Office: (02) 808-1569  (02) 808-1574    (0917) 571-2535<br><br>Statement of Account<br><br></center>';

//for PDC
$sqlpdc='SELECT up.* FROM undeppdcs up where up.CompanyNo='.$company['CompanyNo'];
$stmtpdc=$link->query($sqlpdc);
$resultpdc=$stmtpdc->fetchAll();

if ($stmtpdc->rowCount()>0){
   foreach ($resultpdc as $pdc){
      $pdctable='<tr><td>'.$pdc['DateofPDC'].'</td><td>'.$pdc['PDCNo'].'</td><td>'.number_format($pdc['PDC'],2).'</td></tr>';
   }
   $pdctable='<br><br>Postdated Checks:<br><table><tr><td>Date of PDC</td><td>PDC Number</td><td>Amount</td></tr>'.$pdctable.'</table>';
} else {
   $pdctable='';
}
// end of PDC 
 
    $sqlmain='SELECT ClientName, ContactPerson, TelNo1, TelNo2,concat("Terms: ",Terms," days") as Terms,concat("Credit Limit: ",format(CreditLimit,0)) as CreditLimit, Sum(InvBalance) as TotalDue FROM `Receivables` r where r.CompanyNo='.$company['CompanyNo'].' GROUP BY ClientName';
    $sqlsub='SELECT r.* FROM Receivables r  where r.CompanyNo='.$company['CompanyNo'];

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
//<table width="100%" class="maintable"><tr><td width="40%">
$main='<a href="javascript:window.print()">To:  '.$result['ClientName'].'</a>
<div style="float:right">Date: '.date('F d, Y').'</div>
<br><br><i>Attn: '.$result['ContactPerson'].'</i><div style="float:right">'.$result['Terms'].str_repeat('&nbsp',3).$result['CreditLimit'].'</div>
<br>Tel Nos: '.$result['TelNo1'].'&nbsp&nbsp'.$result['TelNo2'].''.'<br><br><br>';

    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    $sub='<table width="100%" class="subtable"><tr>
<td width=10%>Invoice Date</td>
<td width=20%>Particulars</td>
<td width=7%>PONo</td>
<td width=10%>From Branch</td>
<td width=10%>Invoice Amount</td>
<td width=10%>Paid Amount</td>
<td width=10%>Invoice Balance</td>
<td width=8%>Days Overdue</td>
<td width=15%>Running Bal</td></tr>';
$runsum=0;
foreach ($resultsub as $row){
$runsum=$runsum+$row['InvBalance'];
$sub=$sub.'<tr>
<td width=10%>'.$row['InvDate'].'</td>
<td width=20%>'.$row['Particulars'].'</td>
<td width=7%>'.$row['PONo'].'</td>
<td width=10%>'.$row['Branch'].'</td>
<td width=10%>'.number_format($row['SaleAmount'],2).'</td>
<td width=10%>'.number_format($row['RcdAmount'],2).'</td>
<td width=10%>'.number_format($row['InvBalance'],2).'</td>
<td width=8%>'.$row['DaysOverdue'].'</td>
<td width=10%>'.number_format($runsum,2).'</td></tr>';

}
$sub=$sub.'</table><center>------   NOTHING FOLLOWS  ------</center><br>';

    
    $total='<div style="float:right">Total:  '.number_format($result['TotalDue'],2).str_repeat('&nbsp',7) .'</div><br>' ;
//Verified by:&nbsp &nbsp  '. $controllername.'<br>
$letter=$letter.$main.$sub.'<br>'.$total.$pdctable.'<br><br><footer>
Prepared by:&nbsp &nbsp  '. $user.'<div style="float:right">Acknowledged By:  _______________________</div>
<br><font size="1"><div style="float:right">Signature above printed name</div></font>
<br><br>
<hr>
<i>
Please note that unpaid overdue or over-the-limit accounts (whichever comes first) may result into "on hold order", meaning that the client will be suspended from purchasing on credit.</i>
<font size="1"><br><i>*To help maintain competitive prices, a 2% per month interest will be charged for credit remaining unpaid after the due date.</i>
<br>System data as of '.$dataasof['DataAsOf'].'</font>
</footer><br><br><br>';
 echo $letter;
}   
      break; 
	  
	  
case 'SendToEmailPreview':
    
$letter=''; $cc='';
foreach ($resultco as $company){
$cc.=$company['Company'].",";

$letter=$letter. '<h3>'.$company['CompanyName'].'</h3>';

//for PDC
$sqlpdc='SELECT up.* FROM undeppdcs up where up.CompanyNo='.$company['CompanyNo'];
$stmtpdc=$link->query($sqlpdc);
$resultpdc=$stmtpdc->fetchAll();

if ($stmtpdc->rowCount()>0){
   foreach ($resultpdc as $pdc){
      $pdctable='<tr><td>'.$pdc['DateofPDC'].'</td><td>'.$pdc['PDCNo'].'</td><td>'.number_format($pdc['PDC'],2).'</td></tr>';
   }
   $pdctable='<br><br>Postdated Checks:<br><table><tr><td>Date of PDC</td><td>PDC Number</td><td>Amount</td></tr>'.$pdctable.'</table>';
} else {
   $pdctable='';
}
// end of PDC 
 
    $sqlmain='SELECT ClientName,EmailAddress, ContactPerson, TelNo1, TelNo2,concat("Terms: ",Terms," days") as Terms,concat("Credit Limit: ",format(CreditLimit,0)) as CreditLimit, Sum(InvBalance) as TotalDue FROM `Receivables` r where r.CompanyNo='.$company['CompanyNo'].' GROUP BY ClientName';
    $sqlsub='SELECT r.* FROM Receivables r  where r.CompanyNo='.$company['CompanyNo'];

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
$receiver=$result['EmailAddress'];
$main='
<div>'.$result['Terms'].' , '.$result['CreditLimit'].'</div>';

    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    $sub='<table width="100%" class="subtable" style="border:1px solid black;"><tr>
<td width=10%>Invoice Date</td>
<td width=8%>Days Overdue</td>
<td width=7%>PONo</td>
<td width=10%>From Branch</td>
<td width=10%>Invoice Amount</td></tr>';
$runsum=0;
foreach ($resultsub as $row){
$runsum=$runsum+$row['InvBalance'];
$sub=$sub.'<tr>
<td width=10%>'.$row['InvDate'].'</td>
<td width=8%>'.$row['DaysOverdue'].'</td>
<td width=7%>'.$row['PONo'].'</td>
<td width=10%>'.$row['Branch'].'</td>
<td width=10%>'.number_format($row['SaleAmount'],2).'</td></tr>';

}
$sub=$sub.'</table><br>';

    
    $total='<div style="float:right">Total:  '.number_format($result['TotalDue'],2).'</div><br>' ;


$letter.=$main.$sub.'<br>'.$total.$pdctable.'<br><hr>';

 
 
// $letter.=$letter;


} 

$letter.='<footer>

<br>
<i>
Please note that unpaid overdue or over-the-limit accounts (whichever comes first) may result into "on hold order", meaning that the client will be suspended from purchasing on credit.</i>
<font size="1"><br><i>*To help maintain competitive prices, a 2% per month interest will be charged for credit remaining unpaid after the due date.</i>
<br>System data as of '.$dataasof['DataAsOf'].'</font>
</footer><br><br><br>';



echo $letter;

// echo $email;

$sql='SELECT Email AS Sender FROM `1_gamit`.`1rtcusers` u where u.IDNo='.$_SESSION['(ak0)'];

$stmt=$link->query($sql);
$res=$stmt->fetch();
$cc=substr($cc, 0, -1);
// echo $clientno;
echo '<div style="background-color:orange;border:1px solid black;padding:5px;text-align:center;">';
 echo '<form action="emailsoa.php" method="POST">Receiver: '.$receiver.'<input type="hidden" name="Receiver" value="'.$receiver.'"><h3>Webmail Account:</h3> Sender: '.$res['Sender'].'<br> <input type="hidden" name="Sender" value="'.$res['Sender'].'"><input type="hidden" name="From" value="'.$cc.'"><textarea name="msg" style="display:none;">'.$letter.'</textarea>Password: <input type="password" name="Password"><input type="submit" value="Send"></form>';
echo '</div>';
 
      break;
	  
	  
	  
	  

CASE 'CreditInfoNew':
CASE 'CreditInfoExist':
case 'CreditReject':
    if (!allowedToOpen(586,'1rtc')) { echo 'No permission'; exit; }

 
 
$sqlmain='SELECT c.*,Company, CompanyName FROM `1clients` c JOIN gen_info_1branchesclientsjxn bcj ON c.ClientNo=bcj.ClientNo JOIN 1branches b ON bcj.branchNo=b.BranchNo JOIN 1companies cc ON b.CompanyNo=cc.CompanyNo where c.ClientNo='.$clientno.' LIMIT 1';

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    
$company=$result['CompanyName'];	

$letter='<center><img src="../generalinfo/logo/'.$result['Company'].'.png"></br>';

$letter=$letter. '<font style="font-size: 80%;">Credit & Collections Office: &nbsp; collections@1rotarytrading.com <br/>(02) 734 07679 •  '.$resultuser['MobileNo'].'</font><br><br><br>Credit Information<br><br></center>';	
	
$letter=$letter.'<a href="javascript:window.print()">To:  '.$result['ClientName'].'</a>
<div style="float:right">Date: '.date('F d, Y').'</div>
<br><br><i>Attn: '.$result['ContactPerson'].'</i><br><br>'.$result['TelNo1'].'<br>'.$result['TelNo2'].'<br><br><hr>';

$main=($whichqry=='CreditInfoNew'?'<br>It is our pleasure to welcome you as a <i>'.$company.'</i> CREDIT CUSTOMER!  Your approved credit details are as follows:<br><br>':($whichqry=='CreditInfoExist'?'<br>Thank you for being a loyal customer of  <i>'.$company.'</i>.  We would like to inform you that presently, your credit limit and terms are as follows:<br><br>':'<br>Thank you for your interest in becoming a credit client of  <i>'.$company.'</i>.<br><br>After a thorough evaluation of the documents you have submitted, together with your purchase history from our company, we regret to inform you that we cannot grant you a credit line at this time.  You may re-apply after one year.<br><br>'));

if ($whichqry<>'CreditReject'){
$main=$main.'<center><h3>Credit Limit: P '.number_format($result['CreditLimit'],2).str_repeat('&nbsp',10).'Credit Terms: '.
$result['Terms']. ' days' .str_repeat('&nbsp',3).($result['PORequired']<>0?'PO Required':'').str_repeat('&nbsp',3).($result['ARClientType']==2?'PDC Required':'').'</h3></center>';

$main=$main.'<center><H4 style="border-style:solid; border-width: 1px;padding-top: 10px;
    padding-right: 30px;
    padding-bottom: 10px;
    padding-left: 30px;
    width:400px;">To ensure that ALL your payments are acknowledged, 
always ask for a COLLECTION RECEIPT whenever you pay.</H4></center>';

$main=$main.'Terms and Conditions of this service:<br>'
.'1. Credit terms start on the actual date of purchase indicated on the charge invoice or delivery receipt.<br>'
.'2. Requests for a higher credit limit or for longer terms will be evaluated based on the client\'s purchase/payment performance over a 12-month period.<br>'
.'3. Account statements will be emailed/faxed/delivered to the client on a regular basis as a means to keep track of the credit balance.<br>'
.'4. The client will not be granted a new credit purchase that will result in the total outstanding credit exceeding the credit limit.<br>'
.'5. The client must keep all their accounts updated.  If any one of the outstanding invoices has not been settled after the due date, it is assumed that the client is aware that this service will  immediately be forfeited until all due invoices have been paid.*<br>'
.'6. Payments made with postdated checks will be posted as soon as the checks have cleared.<br>'
.'7. In the event of a returned check, the client will automatically revert back to COD status, without any need of notification from the company.<br>'
.'8. For extra security of both parties, payee on checks must indicate the complete company name:  <b><i>'.$company.'</i></b>.  If purchased from an affiliate, the company name of the affiliate must be the payee.<br><br>'
.'<font size=1><i>*To help maintain competitive prices, a 2% per month interest will be charged for credit remaining unpaid after the due date.</i></font>'
.'<br><br>Thank you and we look forward to serving you for many years to come.<br><br>';
} else {
$main=$main.'Possible reasons for disapproved credit application:<br>'
.'<br>&nbsp &nbsp &nbsp 1. Incomplete documents and/or information'
.'<br>&nbsp &nbsp &nbsp 2. Company is less than two (2) years in existence'
.'<br>&nbsp &nbsp &nbsp 3. Insufficient purchase history '
.'<br>&nbsp &nbsp &nbsp 4. Unfavorable credit history with other suppliers and banks'
.'<br>&nbsp &nbsp &nbsp 5. No existing credit history'
.'<br><br>Thank you and we look forward to serving you as one of our cash clients.<br><br>';
}
$ack='<p style="display:inline; float:left;border-style:solid; border-width: 1px;padding-top: 15px;
    padding-right: 10px;
    padding-bottom: 10px;
    padding-left: 10px;
    width:300px;">Acknowledged By:  _______________________<br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
    <font size="1">Signature above printed name</font><br><br><br>
    Conforme: _______________________<br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<font size="1">Signature above printed name</font></p>';
    
$vty='<p style="display:inline; float:right;">Very truly yours,<br><br><br><br>__________________<br>'.$user.'<br><font size="2">'.$userposition.'</font>';
//<br><br><br><br>__________________<br>'.$controllername.'<br><font size="2">Controller</font>
$letter=$letter.$main.'<br><footer>'.$ack.$vty. '
<br>
</footer><br><br><br>';
 echo $letter;
 break;
 
 
CASE 'Araw1CreditInfo':
    if (!allowedToOpen(586,'1rtc')) { echo 'No permission'; exit; }
include_once($path.'/acrossyrs/commonfunctions/numtowords.php');

$sqlmain='SELECT c.*,Company, CompanyName FROM `1clients` c JOIN gen_info_1branchesclientsjxn bcj ON c.ClientNo=bcj.ClientNo JOIN 1branches b ON bcj.branchNo=b.BranchNo JOIN 1companies cc ON b.CompanyNo=cc.CompanyNo where c.ClientNo='.$clientno.' LIMIT 1';

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    
$company=$result['CompanyName'];	

$letter='<center><img src="../generalinfo/logo/'.$result['Company'].'.png"></br>';

$letter=$letter. '<font style="font-size: 80%;">Credit & Collections Office: &nbsp; collections@1rotarytrading.com <br/>(02) 734 07679 •  '.$resultuser['MobileNo'].'</font><br><br><br>Credit Information<br><br></center>';	
	
$letter=$letter.'<a href="javascript:window.print()">To:  '.$result['ClientName'].'</a>
<div style="float:right">Date: '.date('F d, Y').'</div>
<br><br><i>Attn: '.$result['ContactPerson'].'</i><br><br>'.$result['TelNo1'].'<br>'.$result['TelNo2'].'<br><br><hr>';

$main='<br>Dear valued client,<br><br>We are pleased to provide you a credit of <b>10,000</b> pesos for <b>1 day</b> under the following terms:<br><br>';


$main=$main.'<center>CREDIT LIMIT: <b>10,000</b> CREDIT TERM: <b>1 day</b> </center></br>';


$main=$main.'1. By using this credit, the client represents his ability to pay <i>'.$company.'</i>.<br>'
.'2. Credit term starts on the date and time of purchase indicated on the charge invoice.<br>'
.'3. Accounts must be settled within the day. A collection receipt will be provided to ensure acknowledgment of payment. Client must always ask for receipt.<br>'
.'4. Cash payment only.<br>'
.'5. No new credit purchase will be allowed in excess of the credit.<br>'
.'6. If the payment incur delay, Php 100 penalty will be charged per day for each day that the amount remains unsettled.<br>'
.'7. If an account is not settled within seven days from the date of purchased, the client shall be in default and the company may take the necessary steps to ensure collection.<br>'
.'<br><br>If you agree to these terms, please sign below.'
.'<br><br>Thank you and we look forward to serving you for many years to come.<br><br>';

$ack='<p style="display:inline; float:right;padding-top: 15px;
    padding-right: 10px;
    padding-bottom: 10px;
    padding-left: 10px;
    width:300px;">Conforme: '.str_repeat('&nbsp;',25).'Date:__/__/___<br><br>'.str_repeat('&nbsp;',7).'_______________________<br>'.str_repeat('&nbsp;',25).'Name:<br><br><br>
    '.str_repeat('&nbsp;',7).'_______________________<br>'.str_repeat('&nbsp;',23).'Witness#2</p>';
    
$vty='<p style="display:inline; float:left;">Very truly yours,<br><br><br>'.str_repeat('&nbsp;',18).'__________________<br>'.str_repeat('&nbsp;',31).'Name</br>'.str_repeat('&nbsp;',26).'Designation<br></br></br>'.str_repeat('&nbsp;',18).'__________________<br>'.str_repeat('&nbsp;',27).'Witness#1';
$letter=$letter.$main.'<br><footer>'.$ack.$vty. '
<br>
</footer><br><br><br>';
 echo $letter;
 break; 
 
 
 CASE 'CreditInformation':
    if (!allowedToOpen(586,'1rtc')) { echo 'No permission'; exit; }
// echo '<style>@media print
// {p {page-break-before:always}
	// q{
		// display: none;
	// }
	// #container{width:100%;}

	 // html, body {
			// height: 99%;    
// }}</style>';
 
 
$sqlmain='SELECT c.*,Company, CompanyName FROM `1clients` c JOIN gen_info_1branchesclientsjxn bcj ON c.ClientNo=bcj.ClientNo JOIN 1branches b ON bcj.branchNo=b.BranchNo JOIN 1companies cc ON b.CompanyNo=cc.CompanyNo where c.ClientNo='.$clientno.' LIMIT 1';

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    
$company=$result['CompanyName'];	

$letter='<center><img src="../generalinfo/logo/'.$result['Company'].'.png"></br>';

$letter=$letter. '<font style="font-size: 80%;">Credit & Collections Office: &nbsp; collections@1rotarytrading.com <br/>(02) 734 07679 •  '.$resultuser['MobileNo'].'</font><br><br><br>Credit Information<br><br></center>';	
	
$letter=$letter.'<a href="javascript:window.print()">To:  '.$result['ClientName'].'</a>
<div style="float:right">Date: '.date('F d, Y').'</div>
<br><br><i>Attn: '.$result['ContactPerson'].'</i><br><br>'.$result['TelNo1'].'<br>'.$result['TelNo2'].'<br><br><hr>';

$main='<br>Dear valued client,<br><br>';
$main.='We are pleased to provide you a credit line of P '.$result['CreditLimit'].' and '.$result['Terms'].' days under the following terms:<br><br><center>'.(($result['PORequired']==1)?'P.O REQUIRED':'').' '.(($result['PORequired']==1 and $result['ARClientType']==2)?str_repeat('&nbsp;',10):'').' '.(($result['ARClientType']==2)?'PDC REQUIRED':'').'</center><br>';
$main.='<br><ol>'
.'<li>By using this credit line, the client represents his ability to pay '.$company.'.<//li>'
.'<li>Credit term starts on the date of purchase indicated on the charge invoice or delivery receipt.<//li>'
.'<li>The Company will provide regular update of its credit balance. At the same time, the client agrees to monitor his remaining credit line.</li>'
.'<li>Accounts must be settled within five days from due date. A collection receipt will be provided to ensure acknowledgment of payment. Client must always ask for receipt.</li>'
.'<li>Payment by check will be deemed to have been made upon clearing.</li>'
.'<li>No new credit purchase will be allowed in excess of the credit line.</li>'
.'<li>After the fifth day, 2% interest will be charged for the outstanding unpaid amount, which shall continue every month thereafter.</li>'
.'<li>In the event of a bounced check, the client must pay the equivalent amount in cash within five days from notice. Otherwise, his account will automatically return to COD status.</li>'
.'<li>If an account is not settled within three months from the due date, the client shall be in default and the company may
take the necessary steps to ensure collection. In case of litigation, the venue shall be in the exclusive jurisdiction of
the courts of the City of Taguig, to the exclusion of all other courts.</li>'
.'<li>Request for a higher credit limit or for longer terms will be evaluated based on the client’s purchase/payment performance over a 12-month period.</li></ol>'
.'<br><br>If you agree to these terms, please sign below and accomplish the following security agreements.<br><br>'
.'<br>Thank you and we look forward to serving you for many years to come.<br><br>';

$ack='<p style="display:inline; float:left;
padding-right: 10px;
padding-bottom: 10px;
padding-left: 10px;
width:300px;">Very truly yours,<br><br><br><br>______________________________<br><b>'.$user.'</b></br>'.$userposition.'</p>';
  
$vty='<p style="display:inline; float:right;">Conforme:<br><br><br><br>__________________<br>Date:</p>';
$letter=$letter.$main.'<br><footer>'.$ack.$vty. '
<br>
</footer><br><br><br>';
 echo $letter;
 
 break;
 
 
 
 CASE 'CreditLineSecurityAgreements':
    if (!allowedToOpen(586,'1rtc')) { echo 'No permission'; exit; }
// echo '<style></style>';
 
 
$sqlmain='SELECT c.*,Company, CompanyName FROM `1clients` c JOIN gen_info_1branchesclientsjxn bcj ON c.ClientNo=bcj.ClientNo JOIN 1branches b ON bcj.branchNo=b.BranchNo JOIN 1companies cc ON b.CompanyNo=cc.CompanyNo where c.ClientNo='.$clientno.' LIMIT 1';

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    
$company=$result['CompanyName'];	


echo '<center><a href="javascript:window.print()"><img src="../generalinfo/logo/'.$result['Company'].'.png"></a></br></center>';

echo '<br><br>';
	echo '<div>
		<b><center>Co-maker Statement</center></b>

		<br><p><blockquote>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;I, ________________________________, of legal age, with residence at _____________________ _______________________________________________, for valuable consideration, hereby agree to be the solidary co-maker under the foregoing credit line, whose contents I read and understood. I promise to pay '.$company.' the full amount in case of default upon demand.</blockquote></p>
		
		<br>
		<br>
		<center><div><div>________________________________<br><b>Signature</b></div><div style="margin-left:35%;" align="left">Date:<br>Contact no.:</div></div></center>
		<br><br><br>
		
		<b><center>Security Agreement for Deposit Account</center></b><br>
		
		
		<p><blockquote>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;I, ________________________________, of legal age, with address at _____________________ _______________________________________________, with due authority, (the ‘Grantor’) hereby executes this Security Agreement with '.$company.' on ___________________ upon the following terms:</blockquote></p><br>
		<blockquote>
		<ol>
		
		<li>The Grantor hereby creates a security interest over deposit account number _________________ under account name _________________________________________________________ (the ‘account’) with ____________________________________ to secure payment of the credit line with the Company.</li>
		
		<li>The Grantor grants special power of attorney (‘SPA’) to the Company to appropriate the collateral to settle the outstanding liability that has defaulted default under the credit line.</li>
		
		<li>The SPA shall be extinguished when the total principal obligation is paid in full, or when there is no default and the term of the credit line expires.</li>
		
		</ol>
		</blockquote></div><br><br><br><br><br>';
		/* $ack='<p style="display:inline; float:left;
			padding-right: 10px;
			padding-bottom: 10px;
			padding-left: 10px;
			width:300px;">______________________________<br>Name<br><b>Grantor</b></p>'; */
		$ack='<center>______________________________<br>Name<br><b>Grantor</b><br><br><br>';
			  
			// $vty='<p style="display:inline; float:right;"><u>'.$user.', '.$userposition.'</u><br><b>'.$company.'</b></p>';
			$vty='______________________________<br>'.$user.'<br>'.$userposition.'<br><b>'.$company.'</b></center>';
		
		echo '<div style="text-align:center;width:100%;align:center;">'.$ack.$vty.'</div><p>';
 break;
 
 
 
case 'RemindHoldCheck':
    if (!allowedToOpen(590,'1rtc')) { echo 'No permission'; exit; }
   include('../acctg/ARfunctions.php');
   makepdcs($clientno,$link);
   if (!isset($_POST['check1'])){
      
   ?><form method="post" action="printsoa.php?w=RemindHoldCheck">
 Check/s To Include in the letter:<br>1.&nbsp<input type="text" name="check1" list="undeposited" size=30 autocomplete="off" required="true">
 <br>2.&nbsp<input type="text" name="check2" list="undeposited" size=30 autocomplete="off">
 <br>3.&nbsp<input type="text" name="check3" list="undeposited" size=30 autocomplete="off">
 <br>4.&nbsp<input type="text" name="check4" list="undeposited" size=30 autocomplete="off">
 <br>5.&nbsp<input type="text" name="check5" list="undeposited" size=30 autocomplete="off">
 <input type="hidden" name="clientno" value="<?php echo $clientno ?>" ><input type="hidden" name="<?php echo $fieldname;?>" value="<?php echo $_POST[$fieldname];?>" >
  
<datalist id="undeposited" style="height: 150px;width: 150px; overflow: auto"> 
<?php  
		foreach ($link->query('Select PDCNo, concat(PDCNo, " dated ",DateofPDC,":  P", PDC) as Particulars from undeppdcs p ') as $row) {
                ?>
                <option value="<?php echo $row['PDCNo']; ?>" label="<?php echo $row['Particulars']; ?>"></option>
                <?php
                } // end while
                ?>
</datalist id="<?php echo $otherlist ?>">
   <input type="submit" name="makeletter" value="Make Letter"></form>
<?php
 }
 if (!isset($_POST['makeletter'])){
   goto noform;
 }
 $sqlco='Select c.CompanyNo, c.Company,c.CompanyName from undeppdcs p join `1companies` c on c.CompanyNo=p.CompanyNo where PDCNo in ("'.$_POST['check1'].'","'.$_POST['check2'].'","'.$_POST['check3'].'","'.$_POST['check4'].'","'.$_POST['check5'].'") group by c.CompanyNo';
// echo $sqlco; break;
$stmt=$link->query($sqlco);
$resultco=$stmt->fetchAll();

foreach ($resultco as $company){
$letter='<center><img src="../generalinfo/logo/'.$company['Company'].'.png"></br>';
 
$letter=$letter. '<font style="font-size: 80%;">Credit & Collections Office: &nbsp; collections@1rotarytrading.com <br/>(02) 734 07679 •  '.$resultuser['MobileNo'].'</font><br><br><br>Final Notice<br><br></center>';
$sqlmain='SELECT * FROM `1clients` c where ClientNo='.$clientno;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
$letter=$letter.'<a href="javascript:window.print()">To:  '.$result['ClientName'].'</a>
<div style="float:right">Date: '.date('F d, Y').'</div>
<br><br><i>Attn: '.$result['ContactPerson'].'</i><br><br>'.$result['TelNo1'].'<br>'.$result['TelNo2'].'<br><br><hr>';

$main='Dear Sir/Ma\'am:<br><br>This serves as a final reminder for your overdue accounts with postdated checks.  Please be informed that the check/s listed below will be deposited on <u>'.date('F d, Y',strtotime('next week')).'</u>:<br><br>';

$sql='Select concat(PDCNo, " dated ",DateofPDC,":  P", PDC) as Particulars from undeppdcs p where CompanyNo='.$company['CompanyNo'].' and PDCNo in ("'.$_POST['check1'].'","'.$_POST['check2'].'","'.$_POST['check3'].'","'.$_POST['check4'].'","'.$_POST['check5'].'")';
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
$checkcount=0;
foreach ($result as $check){
   $checkcount=$checkcount+1;
   $main=$main.'<center>'.$checkcount.'. '. $check['Particulars'].'</center><br>'; 
}
$main=$main.'<br><br>Please settle your accounts on time to avoid any convenience caused by the temporary suspension of your credit line.';
$main=$main.'<br><br>If payments have been made, kindly disregard this reminder.<br><br>Thank you very much.<br><br>';

$ack='<p style="display:inline; float:left;border-style:solid; border-width: 1px;padding-top: 20px;
    padding-right: 10px;
    padding-bottom: 10px;
    padding-left: 10px;
    width:300px;">Acknowledged By:  _______________________<br>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp
    <font size="1">Signature above printed name</font></p>';

$sqluser='Select concat(FirstName, \' \',Left(MiddleName,1), \'. \',SurName) as FullName, Position from `1employees` e join `attend_30currentpositions` p on e.IDNo=p.IDNo where p.PositionID=11';
$stmt=$link->query($sqluser);
$result=$stmt->fetch();
$creditofficer=$result['FullName'];
    
$vty='<p style="display:inline; float:right;">Very truly yours,<br><br><br><br>__________________<br>'.$creditofficer.'<br><font size="2">'.$result['Position'].'</font>';

$letter=$letter.$main.'<br><footer>'.$ack.$vty. '
<br>
</footer>';
 echo $letter;
}
break;
case 'HoldCheckSampleLetter':
    if (!allowedToOpen(589,'1rtc')) { echo 'No permission'; exit; }
$letter='<center>Company Logo<br><br></center>';
$letter=$letter.'<a href="javascript:window.print()">To:  1Rotary Trading Corporation</a><br><font style="font-size: 80%;">Credit & Collections Office: &nbsp; collections@1rotarytrading.com <br/>(02) 734 07679 •  '.$resultuser['MobileNo'].'</font><br>
<div style="float:right">Date: '.date('F d, Y').'</div>
<br><br><i>Attn: Credit & Collections Department</i><br><hr><br>';

$main='To the Credit & Collections Officer:<br><br><br>We would like to request that the following check be deposited on _____________________________:<br><br>';

$main=$main.'<center>Bank and Check No:  ______________________________<br>Date of Check:  ______________________________<br>Amount of Check:  ______________________________<br></center><br><br>';

$main=$main.'I am aware that the check will be <i>automatically deposited</i> on the said date, without any notice to me.<br><br>';
$vty='<p style="display:inline; float:right;">Very truly yours,<br><br><br><br>__________________<br>(Name of authorized signatory)<br><font size="2">(Position)</font>';

$letter=$letter.$main.'<br><footer>'.$vty. '
<br>
</footer><br><br><br>';
 echo $letter;
 break;   
}


noform: 
     $stmt=null;  $link=null;
    ?>
</body>
</html>