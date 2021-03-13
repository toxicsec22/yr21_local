<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(62692,'1rtc')) {   echo 'No permission'; exit;}   
 
include_once "../generalinfo/lists.inc";
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;


$sqluser='Select concat(FirstName, \' \',Left(MiddleName,1), \'. \',SurName) as FullName, u.email, u.mobilenumbers from `1employees` e join `1_gamit`.`1rtcusers` u on e.IDNo=u.IDNo where e.IDNo='.$_SESSION['(ak0)'];
$stmt=$link->query($sqluser);
$resultuser=$stmt->fetch();
$user=$resultuser['FullName'];
?>
<html>
<head>
<title>Quotation</title>
<style>

body  
{ 
    /* this affects the margin on the content before sending to printer */ 
    margin: 10px 10px 10px 10px;
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
@media print  
{
table {
border:1px solid black;
border-collapse:collapse;
padding: 3px;
font-size: 9pt;}
footer {
   page-break-after: always;
   position:absolute;
   bottom:0;
   width:90%;
}
}

</style>
</head>
<body>
<?php
$txnid=$_REQUEST['QuoteID'];
$title='Quotation';
$fieldname='Company';
$lookupprocess='printquote.php?QuoteID='.$txnid;
if (!isset($_POST[$fieldname])){
 ?> <br><br><br><center>
   <form method="POST" action="<?php echo $lookupprocess ?>" enctype="multipart/form-data">
Quote from Company<input type="text" name="<?php echo $fieldname ?>" list="companies" size=30 autocomplete="off" required="true" value="1Rotary"><br><br>
<!--Print or Download as pdf?<br>
Print<input type="radio" name="printordownload" value="Print" checked=true><br>
Download<input type="radio" name="printordownload" value="Download"><br><br>-->
<input type="submit" name="lookup" value="Preview"></center> 
<?php
renderlist('companies');
	goto noform;
    ?>
    </form>
 <?php
}
$printordownload='javascript:window.print()';//$_POST['printordownload']=='Print'?'javascript:window.print()':'downloadaspdf.php?filename=Quote'.$txnid;
include_once('../backendphp/functions/getnumber.php');
$sqlco='Select c.CompanyName,c.CompanyNo, c.Company, c.RegisteredAddress from `1companies` c where c.CompanyNo='.getNumber('Company',$_REQUEST['Company']);
// echo $sqlco;break;
$stmt=$link->query($sqlco);
$resultco=$stmt->fetch();

$letter='<center><img src="../generalinfo/logo/'.$resultco['Company'].'.png">';
//$letter=$letter. $company['RegisteredAddress'];
$letter=$letter. '</br><br>Main Office: (02) 519-8232  (02) 478-8394  <br><br>Q U O T A T I O N<br><br></center>';

 
    $sqlmain='SELECT m.*, CONCAT("'.substr($currentyr,2,2).'-",LPAD(m.QuoteID,4,"0")) AS QuoteNo, sum(s.Qty*s.UnitPrice) as Total FROM `quotations_2quotemain` m join `quotations_2quotesub` s on m.QuoteID=s.QuoteID WHERE m.Posted=1 AND m.QuoteID='.$txnid;
    $sqlsub='SELECT s.*, (Qty*UnitPrice) as Amount FROM `quotations_2quotesub` s JOIN `quotations_2quotemain` m ON m.QuoteID=s.QuoteID WHERE m.Posted=1 AND s.QuoteID='.$txnid;

    $stmt=$link->query($sqlmain); $result=$stmt->fetch();
    
    if ($result['Posted']==0) { goto noform;}
//<table width="100%" class="maintable"><tr><td width="40%">
$main='To:  <a href="'.$printordownload.'">'.$result['ClientName'].'</a>
<div style="float:right">Date: '.$result['QuoteDate'].'<br>Quote Ref. No. '.$result['QuoteNo'].'</div>
<br><i>Attention: '.$result['ContactPerson'].'<br>Position: '. $result['Position'].'</i><br><br>
Fax No.: '.$result['FaxNo'].'<br><br><br>Dear '.($result['SirMaam']==1?'Sir':'Ma\'am').':  <br><br>We are pleased to submit our quotation for your requirement/s.<br><br>';

    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    $sub='<table width="100%" class="subtable"><tr>
<td width=60%><center>Description</center></td>
<td width=10%>Qty</td>
<td width=5%>Unit</td>
<td width=10%>Unit Price</td>
<td width=15%>Amount</td></tr>';

foreach ($resultsub as $row){
$sub=$sub.'<tr>
<td width=60%>'.$row['Description'].'</td>
<td width=10%>'.$row['Qty'].'</td>
<td width=5%>'.$row['Unit'].'</td>
<td width=10%>'.$row['UnitPrice'].'</td>
<td width=15%>'.number_format($row['Amount'],2).'</td></tr>';

}
$sub=$sub.'</table><center>------   NOTHING FOLLOWS  ------</center><br>';

    
    $total='<div style="float:right">Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',7) .'</div><br>' ;

$letter=$letter.$main.$sub.'<br>'.$total.'<br>We hope our quotation merits your approval.<br><br>
<font style="font-size: 8pt">Warranty: '.$result['Warranty'].'<br>Terms of Payment: '.$result['Payment'].'<br>Note/s: '.$result['Note1'].'<br>'.$result['Note2'].'<br>'.$result['Note3'].'</font><br><br><br><br>
Prepared by:&nbsp &nbsp  '. $user.'
<br>'.$resultuser['email'].'<br>'.$resultuser['mobilenumbers'].'

<footer><div style="float:right">Conforme/Authorized Signatory:  _______________________</div><br><font size="1"><div style="float:right">Signature above printed name</div></font>
<br><br><hr><center><font style="font-size: 8pt">
Metro Manila  &diams;  Cebu  &diams;  Davao  &diams;  Rizal  &diams;  Cavite  &diams;  Iloilo  &diams;  Bacolod  &diams;  Cagayan de Oro 
<br> Tacloban  &diams;  Butuan  &diams;  Dagupan  &diams;  Naga  &diams;  Gen. Santos  &diams;  Pampanga
<br> Bataan  &diams;  Laguna  &diams;  Lipa  &diams;  Vigan  &diams;  Tarlac  &diams;  Lucena  &diams;  La Union
</font></center>
</footer><br><br><br>';
 echo $letter;
noform: 
     $link=null; $stmt=null;
    ?>
</body>
</html>