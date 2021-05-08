<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6272,'1rtc')) {   echo 'No permission'; exit;}  
include_once('../switchboard/contents.php');

 



$txnidname='QuoteID';
$pagetouse='quotespermonth.php';
$fieldname='Month';
$title='Quotation Per '.$fieldname;
$method='GET';
$showbranches=false;
include_once('../backendphp/layout/clickontabletoedithead.php');
?>
<form method="post" action="<?php echo $pagetouse; ?>" enctype="multipart/form-data">
                Choose Month (1 - 12):  <input type="text" size=5 name="<?php echo $fieldname; ?>" value="<?php echo date('m'); ?>"></input>
                

<input type="submit" name="lookup" value="Lookup"> </form>
<?php str_repeat('&nbsp',10); ?><a href='newcanvass.php?w=Quote'  target=_blank>Add Quote</a>
<?php

if (!isset($_REQUEST[$fieldname])){
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.date('m').'-1')).'<br>';   
$txndate='Month(m.QuoteDate)='.date('m');
} else {
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_POST[$fieldname].'-1')).'<br>';   
$txndate='Month(m.QuoteDate)='.$_REQUEST[$fieldname];
}
 
$columnnames=array('QuoteDate','ClientNo','ClientName','ContactPerson','Position','EncodedBy','LineItems','TotalQuote');  
$sql='SELECT m.*, if(SirMaam=1,\'Sir\',\'Ma`am\') as SirMaam, e.Nickname as EncodedBy, sum(Qty*UnitPrice) as TotalQuote, count(Description) as LineItems FROM quotations_2quotemain m left join quotations_2quotesub s on m.QuoteID=s.QuoteID left join `1employees` as e on e.IDNo=m.EncodedByNo where '.$txndate .' group by m.QuoteID';

$process1='addeditquote.php?';
$processlabel1='Lookup';
include_once('../backendphp/layout/clickontabletoeditbody.php');

noform:
      $link=null; $stmt=null;
?>