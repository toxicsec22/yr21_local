<?php
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
if (allowedToOpen(6642,'1rtc')){
        $samcond=' AND TLIDNo IN (SELECT TeamLeader FROM attend_1branchgroups WHERE SAM='.$_SESSION['(ak0)'].')';
        $tllistsql='SELECT IDNo, FullName FROM `attend_30currentpositions` WHERE PositionID=36 AND IDNo '.$samcond;
        $condition=(!isset($_REQUEST['TeamLeader']) OR (strtoupper($_REQUEST['TeamLeader'])=='ALL'))?' AND u.TLIDNo '.$samcond :' AND u.TLIDNo='.
            comboBoxValue($link,'`attend_30currentpositions`','FullName',$_REQUEST['TeamLeader'],'IDNo');
    } else {
        $samcond=''; 
    }
    
$sql1='SELECT m.TxnID,m.TLIDNo, CONCAT(e.Nickname," - ", e.FirstName," ",e.SurName," (",e.IDNo,")") AS TeamLeader
       FROM `calllogs_2telmain` m JOIN `1employees` e ON e.IDNo=m.TLIDNo WHERE m.Date=\''.$defaultdate.'\' '.$samcond;
$sql2='SELECT m.*,s.*, IF(`QuoteType`=0,\'Verbal\',\'Formal\') AS `Quote_Type`, e.Nickname AS EncodedBy, s.TimeStamp AS Time_Stamp, 
       IF(ISNULL(qm.QuoteID),"",CONCAT(\'<a href=/'.$url_folder.'/canvassandquote/addeditquote.php?QuoteID=\',qm.QuoteID,\'>Lookup</a>\')) AS QuoteLink, e1.Nickname AS SAM, 
       @curRow := @curRow + 1 AS `Call#` 
       FROM `calllogs_2telmain` m JOIN `calllogs_2telsub` s ON m.TxnID=s.TxnID
JOIN `1employees` e ON e.IDNo=s.EncodedByNo JOIN    (SELECT @curRow := 0) c 
LEFT JOIN `quotations_2quotemain` qm ON qm.ClientName=s.ClientName AND qm.QuoteID=REPLACE(s.QuoteNo,\'18-\',\'\')
LEFT JOIN `1employees` e1 ON e1.IDNo=s.SAMByNo';
       $columnnames1=array('TeamLeader'); $groupby=('TLIDNo'); $orderby=' ORDER BY `Call#` '; $secondcondition=' AND m.Date=\''.$defaultdate.'\'';
       $columnnames2=array('Call#','ClientName','ContactPerson','Position','ContactNumber','Notes','Quote_Type','QuoteNo','QuoteLink','InvoiceNo','Time_Stamp','SAMComment','SAM','SAMTS');
       $nocount=true; $sql0='DROP TEMPORARY TABLE IF EXISTS callsummary;';$stmt0=$link->prepare($sql0); $stmt0->execute();
       $sql0='CREATE TEMPORARY TABLE callsummary AS '
               . 'SELECT m.TxnID, TLIDNo, COUNT(TxnSubId) AS Calls, 
       (SELECT COUNT(TxnSubId) FROM `calllogs_2telsub` WHERE QuoteType=1 AND TxnID=m.TxnID) AS Formal, 
       (SELECT COUNT(TxnSubId) FROM `calllogs_2telsub` WHERE QuoteType=0 AND TxnID=m.TxnID) AS Verbal, 
       (SELECT COUNT(TxnSubId) FROM `calllogs_2telsub` WHERE NOT ISNULL(QuoteNo) AND QuoteNo NOT LIKE "" AND TxnID=m.TxnID) AS Quotes, 
       (SELECT COUNT(TxnSubId) FROM `calllogs_2telsub` WHERE NOT ISNULL(InvoiceNo) AND InvoiceNo NOT LIKE "" AND TxnID=m.TxnID) AS WithInvoice 
       FROM `calllogs_2telmain` m JOIN `calllogs_2telsub`  s ON m.TxnID=s.TxnID  WHERE m.Date=\''.$defaultdate.'\' '.$samcond.' GROUP BY TLIDNo';
      // if($_SESSION['(ak0)']==1002) {echo $sql0; exit();}
       $stmt0=$link->prepare($sql0); $stmt0->execute();
       $sqlsubtotal='SELECT * FROM callsummary';
       $sqltotal='SELECT SUM(Calls) AS Calls, SUM(Formal) AS Formal, SUM(Verbal) AS Verbal, SUM(Quotes) AS Quotes, SUM(WithInvoice) AS WithInvoice FROM callsummary';
        $stmttotal=$link->query($sqltotal); $restotal=$stmttotal->fetch();
        $totalstext='<br>Total Calls: '.$restotal['Calls'].'<br>Total Formal Quotations: '.$restotal['Formal'].'<br>Total Verbal Quotes: '.$restotal['Verbal'].'<br>Total Invoices: '.$restotal['WithInvoice'].'<br>';
       $colsubtotals=array('Calls','Formal','Verbal','Quotes','WithInvoice');
       
       ?>