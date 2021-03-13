<?php
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
if (allowedToOpen(6642,'1rtc')){
        $aslcond=' AND TLIDNo IN (SELECT TeamLeader FROM attend_1branchgroups WHERE ASL='.$_SESSION['(ak0)'].')';
        $tllistsql='SELECT IDNo, FullName FROM `attend_30currentpositions` WHERE PositionID=36 AND IDNo '.$aslcond;
        $condition=(!isset($_REQUEST['TeamLeader']) OR (strtoupper($_REQUEST['TeamLeader'])=='ALL'))?' AND u.TLIDNo '.$aslcond :' AND u.TLIDNo='.
            comboBoxValue($link,'`attend_30currentpositions`','FullName',$_REQUEST['TeamLeader'],'IDNo');
    } else {
        $aslcond=''; 
    }
$sql1='SELECT m.TxnID,m.TLIDNo, CONCAT(e.Nickname," - ", e.FirstName," ",e.SurName," (",e.IDNo,")") AS TeamLeader
       FROM `calllogs_2visitmain` m JOIN `1employees` e ON e.IDNo=m.TLIDNo WHERE m.VisitDate=\''.$defaultdate.'\' '.$aslcond;

$sql2='SELECT m.*,s.*, e.Nickname AS EncodedBy, s.TimeStamp AS Time_Stamp, IF(RequestedBy=0,"STL","Client") AS Requested_By, 
       IF(ISNULL(qm.QuoteID),"",CONCAT(\'<a href=../canvassandquote/addeditquote.php?QuoteID=\',qm.QuoteID,\'>Lookup</a>\')) AS QuoteLink, VisitPurpose AS Purpose, e1.Nickname AS ASL,
       @curRow := @curRow + 1 AS `Visit#` 
       FROM `calllogs_2visitmain` m JOIN `calllogs_2visitsub` s ON m.TxnID=s.TxnID
JOIN `1employees` e ON e.IDNo=s.EncodedByNo JOIN    (SELECT @curRow := 0) c JOIN `calllogs_0visitpurpose` v ON v.VisitID=s.VisitID
LEFT JOIN `quotations_2quotemain` qm ON qm.ClientName=s.ClientName AND qm.QuoteID=REPLACE(s.QuoteNo,\'18-\',\'\')
LEFT JOIN `1employees` e1 ON e1.IDNo=s.ASLByNo';

       $columnnames1=array('TeamLeader'); $groupby=('TLIDNo'); $orderby=' ORDER BY `Visit#` '; $secondcondition=' AND m.VisitDate=\''.$defaultdate.'\'';
       $columnnames2=array('Visit#','ClientName','ContactPerson','Position','ContactNumber','Address','Purpose','DetailsofMtg','FollowUpAction','FollowUpActionDate','Requested_By','Attendees','QuoteNo','QuoteLink','InvoiceNo','Time_Stamp','ASLComment','ASL','ASLTS');
if($which<>'All'){ goto skiptotal;}
       $nocount=true; $sql0='DROP TEMPORARY TABLE IF EXISTS visitsummary;';$stmt0=$link->prepare($sql0); $stmt0->execute();
       $sql0='CREATE TEMPORARY TABLE visitsummary AS '
               . 'SELECT m.TxnID, TLIDNo, COUNT(TxnSubId) AS Visits, 
       (SELECT COUNT(TxnSubId) FROM `calllogs_2visitsub` WHERE NOT ISNULL(QuoteNo) AND QuoteNo NOT LIKE "" AND TxnID=m.TxnID) AS Quotes, 
       (SELECT COUNT(TxnSubId) FROM `calllogs_2visitsub` WHERE NOT ISNULL(InvoiceNo) AND InvoiceNo NOT LIKE "" AND TxnID=m.TxnID) AS WithInvoice 
       FROM `calllogs_2visitmain` m JOIN `calllogs_2visitsub`  s ON m.TxnID=s.TxnID  WHERE m.VisitDate=\''.$defaultdate.'\' '.$aslcond.' GROUP BY TLIDNo';
	   
       $stmt0=$link->prepare($sql0); $stmt0->execute();
       $sqlsubtotal='SELECT * FROM visitsummary';
       $sqltotal='SELECT SUM(Visits) AS Visits, SUM(Quotes) AS Quotes, SUM(WithInvoice) AS WithInvoice FROM visitsummary';
        $stmttotal=$link->query($sqltotal); $restotal=$stmttotal->fetch();
        $totalstext='<br>Total Visits: '.$restotal['Visits'].'<br>Total Formal Quotations: '.$restotal['Quotes'].'<br>Total Invoices: '.$restotal['WithInvoice'].'<br>';
       $colsubtotals=array('Visits','Quotes','WithInvoice');
skiptotal:
       ?>