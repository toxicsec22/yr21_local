<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(665,6651,6652);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$which=!isset($_REQUEST['w'])?'Month':$_REQUEST['w'];
$showbranches=($which=='Month')?false:true; include_once('../switchboard/contents.php');
  




switch ($which) {
    case 'Month':
        if (!allowedToOpen(665,'1rtc')) { echo 'No permission'; exit; }
        $fromdate=(!isset($_REQUEST['FromDate'])?date('Y-m-01'):$_REQUEST['FromDate']);
        $todate=(!isset($_REQUEST['ToDate'])?date('Y-m-t'):$_REQUEST['ToDate']);
        $title='Scores'; $formdesc='For the month of '. date('F',strtotime($fromdate));
        ?><br><br>
        <form method="post" style="display:inline" action="<?php echo 'scores.php?w='.$which.'&FromDate='.$fromdate; ?>" enctype="multipart/form-data">
                From date: (Month of this date is basis for calculated target.)  <input type="date" name="FromDate" value="<?php echo $fromdate; ?>"></input>&nbsp; &nbsp;
                To date:  <input type="date" name="ToDate" value="<?php echo $todate; ?>"></input>
                <input type="submit" name="lookup" value="Lookup">
        </form><br><br>
        <?php
        if (allowedToOpen(6651,'1rtc')) { 
        $sql0='SELECT e.IDNo, e.Nickname FROM `attend_30currentpositions` p JOIN `1employees` e ON e.IDNo=p.IDNo WHERE PositionID=36;';}
        else { $sql0='SELECT p.TeamLeader,IDNo, e.Nickname FROM `attend_1branchgroups` p JOIN `1employees` e ON e.IDNo=p.TeamLeader WHERE (p.TeamLeader='.$_SESSION['(ak0)'].' OR p.SAM='.$_SESSION['(ak0)'].') GROUP BY p.TeamLeader;';} 
        $stmt0=$link->query($sql0); $restl=$stmt0->fetchAll();
        //if($_SESSION['(ak0)']==1002){ echo $sql0.'<br><br>';}
        $sql1='CREATE TEMPORARY TABLE `SalesPerTL` AS SELECT `TeamLeader`,ROUND(SUM(Qty*UnitPrice),0) AS `SumOfInv` FROM `invty_2sale` tm JOIN `invty_2salesub` ts ON tm.TxnID=ts.TxnID WHERE tm.`TeamLeader` IS NOT NULL AND tm.`TeamLeader`<>0 AND (tm.`Date`)>=\''.$fromdate.'\' AND (tm.`Date`)<=\''.$todate.'\' GROUP BY tm.`TeamLeader`';        
        $stmt1=$link->prepare($sql1); $stmt1->execute();
		// echo $sql1; exit();
        //if($_SESSION['(ak0)']==1002){ echo $sql1.'<br><br>';}
        $sqltbl=''; $sqltel=''; $sqlquote=''; $sqlinv=''; $sqlinvamt=''; $sqltarget=''; $sqltargetscore='';
        $columnnames=array('Title');
        foreach ($restl as $tl){
            $columnnames[]=$tl['Nickname'];
            $sqltbl=$sqltbl.', `'.$tl['Nickname'].'` VARCHAR(100)';
            $sqltel=$sqltel.', COUNT(DISTINCT(CASE WHEN tm.`TLIDNo`='.$tl['IDNo'].' THEN ClientName END)) AS `'.$tl['Nickname'].'`';
            $sqlquote=$sqlquote.', COUNT(DISTINCT(CASE WHEN tm.`EncodedByNo`='.$tl['IDNo'].' THEN ClientName END)) AS `'.$tl['Nickname'].'`';
            $sqlinv=$sqlinv.', COUNT(CASE WHEN tm.`TeamLeader`='.$tl['IDNo'].' THEN SaleNo END) AS `'.$tl['Nickname'].'`';
            $sqlinvamt=$sqlinvamt.', FORMAT(IFNULL(SUM(CASE WHEN tm.`TeamLeader`='.$tl['IDNo'].' THEN Qty*UnitPrice END),0),0) AS `'.$tl['Nickname'].'`';
            $sqltarget=$sqltarget.', FORMAT(SUM(CASE WHEN tm.`TeamLeader`='.$tl['IDNo'].' THEN ProratedTarget END),0) AS `'.$tl['Nickname'].'`';
            $sqltargetscore=$sqltargetscore.', CONCAT(FORMAT(SUM(CASE WHEN tm.`TeamLeader`='.$tl['IDNo'].' THEN ((`SumOfInv`/ProratedTarget)*100) END),2),\'%\') AS `'.$tl['Nickname'].'`';
        }
        $sql1='CREATE TEMPORARY TABLE `Scores` (
            `Title` varchar(30) NOT NULL '.$sqltbl.')
SELECT \'Telephone Calls\' AS Title '.$sqltel.' FROM `calllogs_2telmain` tm JOIN `calllogs_2telsub` ts ON tm.TxnID=ts.TxnID WHERE (tm.`Date`)>=\''.$fromdate.'\' AND (tm.`Date`)<=\''.$todate.'\'';
// echo $sql1; exit();
        $stmt1=$link->prepare($sql1); $stmt1->execute();// if($_SESSION['(ak0)']==1002){ echo $sql1.'<br><br>';}
        $sql1='INSERT INTO `Scores`  
SELECT \'Client Visits\' AS Title '.$sqltel.' FROM `calllogs_2visitmain` tm JOIN `calllogs_2visitsub` ts ON tm.TxnID=ts.TxnID WHERE (tm.`VisitDate`)>=\''.$fromdate.'\' AND (tm.`VisitDate`)<=\''.$todate.'\'';
        $stmt1=$link->prepare($sql1); $stmt1->execute();// if($_SESSION['(ak0)']==1002){ echo $sql1.'<br><br>';}
        $sql1='INSERT INTO `Scores`  
SELECT \'Formal Quotations\' AS Title '.$sqlquote.' FROM `quotations_2quotemain` tm WHERE (tm.`QuoteDate`)>=\''.$fromdate.'\' AND (tm.`QuoteDate`)<=\''.$todate.'\'';
        $stmt1=$link->prepare($sql1); $stmt1->execute();// if($_SESSION['(ak0)']==1002){ echo $sql1.'<br><br>';}
        $sql1='INSERT INTO `Scores`  
SELECT \'Number of Invoices\' AS Title '.$sqlinv.' FROM `invty_2sale` tm WHERE (tm.`Date`)>=\''.$fromdate.'\' AND (tm.`Date`)<=\''.$todate.'\'';
        $stmt1=$link->prepare($sql1); $stmt1->execute();// if($_SESSION['(ak0)']==1002){ echo $sql1.'<br><br>';}
        $sql1='INSERT INTO `Scores`  
SELECT \'Total Amount of Invoices\' AS Title '.$sqlinvamt.' FROM `invty_2sale` tm JOIN `invty_2salesub` ts ON tm.TxnID=ts.TxnID WHERE (tm.`Date`)>=\''.$fromdate.'\' AND (tm.`Date`)<=\''.$todate.'\'';
        $stmt1=$link->prepare($sql1); $stmt1->execute();// if($_SESSION['(ak0)']==1002){ echo $sql1.'<br><br>';}
        $txndate=substr($fromdate,5,2);  
        // GET PRORATED TARGETS
        include_once '../invty/calcproratedtargets.php'; 
        //Removed these because these sqls are already executed in $link, because there is only one $link.
        // execute the following in $link also
        // $stmt=$link->prepare($sql0);$stmt->execute();
        // $stmt=$link->prepare($sql1);$stmt->execute();
        // $stmt=$link->prepare($sql2);$stmt->execute();
        // $stmt=$link->prepare($sql3);$stmt->execute();
        // END OF PRORATED TARGET TABLES
        $sql1='INSERT INTO `Scores`  SELECT \'Month Target\' AS Title '.$sqltarget.' FROM `targettl` tm ';
        $stmt1=$link->prepare($sql1); $stmt1->execute(); //if($_SESSION['(ak0)']==1002){ echo $sql1.'<br><br>';}
        $sql1='INSERT INTO `Scores`  SELECT \'Target Reached\' AS Title '.$sqltargetscore.' FROM `SalesPerTL` tm JOIN `targettl` t ON t.TeamLeader=tm.TeamLeader';
        $stmt1=$link->prepare($sql1); $stmt1->execute(); //if($_SESSION['(ak0)']==1002){ echo $sql1.'<br><br>';}
        $sql='SELECT * FROM `Scores`'; $hidecount=true; //if($_SESSION['(ak0)']==1002){ echo $sql.'<br><br>';}
        include('../backendphp/layout/displayastablenosort.php');
        break;
        
    case 'SummPer':
        if (!allowedToOpen(6652,'1rtc')) { echo 'No permission'; exit; }
        $title='Contact Summary Per Client'; $fieldname='Client';
        ?><br><br>
        <form method="post" style="display:inline" action="<?php echo 'scores.php?w='.$which; ?>" enctype="multipart/form-data">
                For Client: <input type="text" name="Client" list="clients"></input>&nbsp; &nbsp;
                <input type="submit" name="lookup" value="Lookup">
        </form><br><br>
        <?php
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        if (allowedToOpen(6641,'1rtc')){ $tl=' AND TeamLeader='.$_SESSION['(ak0)']; } else { $tl='';}
        echo comboBox($link,'SELECT `c`.`ClientNo` AS `ClientNo`,
        c.`ClientName` FROM
        (`1clients` `c`
        JOIN `gen_info_1branchesclientsjxn` `bc` ON ((`c`.`ClientNo` = `bc`.`ClientNo`)))
        WHERE (`bc`.`BranchNo` IN (SELECT BranchNo FROM `attend_1branchgroups` WHERE `c`.`ClientNo`>10001 '.$tl.')) OR c.ClientClass=1 GROUP BY c.ClientNo ORDER BY `ClientName`;',
    'ClientNo','ClientName','clients');
        if (!isset($_REQUEST[$fieldname])){ include('../backendphp/layout/clickontabletoedithead.php'); goto noform;} else {
            $formdesc= '<i>'.$_POST[$fieldname].'</i>'; 
            
            $clientno=comboBoxValue($link,'`1clients`','ClientName',addslashes($_REQUEST[$fieldname]),'ClientNo'); 
        $sql0='CREATE TEMPORARY TABLE `clientcontact`
		( `ContactDate` DATETIME NOT NULL,
          `Sales_Report` varchar(255) NOT NULL,
          `Sales_TS`  DATETIME NOT NULL,
          `CSO_Report` varchar(255) DEFAULT NULL,
          `CSO_TS` DATETIME NOT NULL,
          `EncodedByNo` smallint(6) NOT NULL,
          `CommentType` TINYINT(1) NOT NULL DEFAULT 0, 
		  `TimeStamp` DATETIME NOT NULL ) AS 
SELECT date_format(cc.ContactDate,"%Y-%m-%d") as ContactDate, "" AS Sales_Report, "" AS `Sales_TS`, cc.`Comment` AS CSO_Report, cc.`TimeStamp` AS CSO_TS, cc.EncodedByNo, cc.ARComment AS CommentType, cc.`TimeStamp` FROM `comments_5commentsonclients` cc WHERE cc.ARComment<>0 AND ClientNo='.$clientno.'
UNION SELECT date_format(cc.ContactDate,"%Y-%m-%d") as ContactDate, cc.`Comment` AS Sales_Report, cc.`TimeStamp` AS `Sales_TS`, "" AS CSO_Report, "" AS CSO_TS, cc.EncodedByNo, cc.ARComment, cc.`TimeStamp` FROM `comments_5commentsonclients` cc WHERE cc.ARComment=0 AND ClientNo='.$clientno.'
UNION SELECT date_format(Date,"%Y-%m-%d") as ContactDate, "" AS Sales_Report, "" AS `Sales_TS`, `Report`, s.`TimeStamp`, s.EncodedByNo, -1 AS CommentType, s.`TimeStamp` FROM `calllogs_3armain` m JOIN `calllogs_3arsub` s ON m.TxnID=s.TxnID WHERE ClientNo='.$clientno.'
UNION SELECT date_format(Date,"%Y-%m-%d") as ContactDate, `Notes`, s.`TimeStamp`, "" AS CSO_Report, "" AS CSO_TS, s.EncodedByNo, 0 AS CommentType, s.`TimeStamp` FROM `calllogs_2telmain` m JOIN `calllogs_2telsub` s ON m.TxnID=s.TxnID WHERE ClientNo='.$clientno.'
UNION SELECT date_format(VisitDate,"%Y-%m-%d") as ContactDate, `DetailsofMtg`, s.`TimeStamp`, "" AS CSO_Report, "" AS CSO_TS, s.EncodedByNo, 0 AS CommentType, s.`TimeStamp` FROM `calllogs_2visitmain` m JOIN `calllogs_2visitsub` s ON m.TxnID=s.TxnID WHERE ClientNo='.$clientno.'
ORDER BY `TimeStamp` desc;'; 
        $stmt=$link->prepare($sql0);$stmt->execute();
        $sql='SELECT cc.*,(CASE WHEN CommentType=0 THEN CONCAT(e.Nickname, " ", e.Surname) END) AS STLEncodedBy,(CASE WHEN CommentType<>0 THEN CONCAT(e.Nickname, " ", e.Surname) END) AS CSOEncodedBy  FROM `clientcontact` cc LEFT JOIN `1_gamit`.`0idinfo` e on cc.EncodedByNo=e.IDNo';
        $columnnames=array('ContactDate','Sales_Report','STLEncodedBy','Sales_TS','CSO_Report','CSOEncodedBy','CSO_TS');
        include('../backendphp/layout/displayastable.php');
        }
        break;
}
noform:
 $link=null; $stmt=null;  
?>