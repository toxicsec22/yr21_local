<html>
<head>
<title>Search</title>
</head>
<body>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(666,'1rtc')) {   echo 'No permission'; exit;}   
include_once('../switchboard/contents.php'); 
 
include_once('../backendphp/layout/regulartablestyle.php');
 
?><br>
<h3>Search Data</h3><br><p style="font-size:small"><i> Pls inform me if you want other searches - JYE</i></p><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search client:&nbsp &nbsp 
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Client'></form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search in all fields:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Particulars'>  </form><br><br>
&nbsp &nbsp <form style="display:inline" action='#' method='POST'>Search in calendar:
    <input type='text' name='stringsearch' autocomplete='off' size='10' >&nbsp &nbsp <input type='submit' name='submit' value='Calendar'>  </form><br><br>
<?php
if (!isset($_POST['submit'])){    goto noform;}

$sqlcall='SELECT m.TLIDNo, m.Date,s.*, IF(`QuoteType`=0,\'Verbal\',\'Formal\') AS `Quote_Type`, e.Nickname AS TeamLeader, s.TimeStamp AS Time_Stamp, 
       IF(ISNULL(qm.QuoteID),"",CONCAT(\'<a href=../canvassandquote/addeditquote.php?QuoteID=\',qm.QuoteID,\'>Lookup</a>\')) AS QuoteLink, 
       @curRow := @curRow + 1 AS `Call#` 
       FROM `calllogs_2telmain` m JOIN `calllogs_2telsub` s ON m.TxnID=s.TxnID
JOIN `1employees` e ON e.IDNo=m.TLIDNo JOIN    (SELECT @curRow := 0) c 
LEFT JOIN `quotations_2quotemain` qm ON qm.ClientName=s.ClientName AND qm.QuoteID=REPLACE(s.QuoteNo,\'17-\',\'\') ';
$sqlvisit='SELECT m.TLIDNo, m.VisitDate,s.*, "" AS `Quote_Type`, DetailsofMtg AS Notes, e.Nickname AS TeamLeader, s.TimeStamp AS Time_Stamp, 
       IF(ISNULL(qm.QuoteID),"",CONCAT(\'<a href=../canvassandquote/addeditquote.php?QuoteID=\',qm.QuoteID,\'>Lookup</a>\')) AS QuoteLink, 
       @curRow := @curRow + 1 AS `Visit#` 
       FROM `calllogs_2visitmain` m JOIN `calllogs_2visitsub` s ON m.TxnID=s.TxnID
JOIN `1employees` e ON e.IDNo=m.TLIDNo JOIN    (SELECT @curRow := 0) c 
LEFT JOIN `quotations_2quotemain` qm ON qm.ClientName=s.ClientName AND qm.QuoteID=REPLACE(s.QuoteNo,\'17-\',\'\') 
';

$columnnames=array('TeamLeader','ClientName','ContactPerson','Position','ContactNumber','Notes','Quote_Type','QuoteNo','QuoteLink','InvoiceNo','EncodedByNo','Time_Stamp');

switch ($_POST['submit']){

case 'Client':
   $txnid='TxnID'; $sqlcondition=' WHERE s.ClientName LIKE  \'%'.$_POST['stringsearch'].'%\' ';
   $sql=$sqlcall.$sqlcondition;
   $subtitle='<br><br>Results for: '.$_POST['submit'].' - Calls';
    include('../backendphp/layout/displayastableonlynoheaders.php');
   $sql=$sqlvisit.$sqlcondition; $subtitle=' - Visits';
   break;
   
case 'Particulars':
   $txnid='TxnID';
    $sql1='CREATE TEMPORARY TABLE calllist AS '.$sqlcall; $stmt1=$link->prepare($sql1); $stmt1->execute();
    
   $colstosearch=array('TeamLeader','ContactPerson','Position','ContactNumber','Notes','Quote_Type','QuoteNo','InvoiceNo','EncodedByNo');
   $sql0='';
   foreach ($colstosearch as $col){
       $sql0=$sql0.' OR `'.$col.'` LIKE  \'%'.$_POST['stringsearch'].'%\'';
   }
   $sql='SELECT * FROM calllist WHERE ClientName LIKE  \'%'.$_POST['stringsearch'].'%\' '.$sql0;
    $subtitle='<br><br>Results for: '.$_POST['submit'].' - Calls';
    include('../backendphp/layout/displayastableonlynoheaders.php');
    
    $sql1='CREATE TEMPORARY TABLE visitlist AS '.$sqlvisit; $stmt1=$link->prepare($sql1); $stmt1->execute();
    
   $colstosearch=array('TeamLeader','ContactPerson','Position','ContactNumber','DetailsofMtg','QuoteNo','InvoiceNo','EncodedByNo');
   $sql0='';
   foreach ($colstosearch as $col){
       $sql0=$sql0.' OR `'.$col.'` LIKE  \'%'.$_POST['stringsearch'].'%\'';
   }
   $sql='SELECT * FROM visitlist WHERE ClientName LIKE  \'%'.$_POST['stringsearch'].'%\' '.$sql0;
   $subtitle=' - Visits';
   break;

case 'Calendar':
   $txnid='TxnID'; $sqlcondition=' WHERE ClientName LIKE  \'%'.$_POST['stringsearch'].'%\' ';
   $colstosearch=array('TLIDNo','Nickname','Details');
   $columnnames=array('Event_Date','TLIDNo','Event');
   $sql0='';
   foreach ($colstosearch as $col){ $sql0=$sql0.' OR `'.$col.'` LIKE  \'%'.$_POST['stringsearch'].'%\'';   }
   $sql="SELECT u.TLIDNo, CONCAT(Nickname,': ',ClientName,' - ', Details) AS Event, DATE_FORMAT(`Date`,'%Y-%m-%d') AS `Event_Date` FROM calllogs_unicalendarevents u JOIN `1employees` e ON e.IDNo=u.TLIDNo ".$sqlcondition.$sql0." ORDER BY `Date`";
   $subtitle='';
   break;
} 

$subtitle='<br><br>Results for: '.$_POST['submit'].$subtitle;
include('../backendphp/layout/displayastableonlynoheaders.php');
noform:
     $link=null; $stmt=null;  
?>