<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6100,'1rtc')) { echo 'No permission'; exit;}
 
$showbranches=false; include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'Daily':$_GET['w']);


switch ($which){
case 'Daily':
    $fromdate=(!isset($_REQUEST['FromDate'])?date('Y-m-d',strtotime('last week')):$_REQUEST['FromDate']);
    $from=strtotime($fromdate);
    $todate=(!isset($_REQUEST['ToDate'])?date('Y-m-d'):$_REQUEST['ToDate']);
    $to=strtotime($todate);
    $title='Comparative Daily Summary Per SAM'; $formdesc='For the period '.$fromdate.' to '.$todate.'<br><br>';
    ?><title><?php echo $title; ?></title><h3><?php echo $title; ?></h3><h4><?php echo $formdesc; ?></h4>
        <br><br>
        <form method="post" style="display:inline" action="<?php echo 'asltools.php?w='.$which; ?>" enctype="multipart/form-data">
                From date: <input type="date" name="FromDate" value="<?php echo $fromdate; ?>"></input>&nbsp; &nbsp;
                To date:  <input type="date" name="ToDate" value="<?php echo $todate; ?>"></input>
                <input type="submit" name="lookup" value="Lookup">
        </form><br><br>
        <?php
        
        $days=0; $columnnames=array('Title'); $sqltbl=''; $sqltel=''; $sqlvisit=''; $sqlquote=''; $sqlsales=''; $sqlinv=''; $sqlinvamt=''; $sqltarget=''; $sqltargetscore='';
        
        while ($from<=$to) {
            $col=date('Y-m-d',$from);
            $columnnames[]=$col;
            $sqltbl.=', `'.$col.'` varchar(45) DEFAULT ""';
            $sqltel.=', COUNT(DISTINCT(CASE WHEN tm.`Date`=\''.$col.'\' THEN ClientName END)) AS `'.$col.'`';
            $sqlvisit.=', COUNT(DISTINCT(CASE WHEN tm.`VisitDate`=\''.$col.'\' THEN ClientName END)) AS `'.$col.'`';
            $sqlquote.=', COUNT(DISTINCT(CASE WHEN tm.`QuoteDate`=\''.$col.'\' THEN ClientName END)) AS `'.$col.'`';
            $sqlsales.=', FORMAT(ROUND(SUM(CASE WHEN tm.`Date`=\''.$col.'\' THEN Qty*UnitPrice END),0),0) AS `'.$col.'`';
            $sqlinv.=', COUNT(CASE WHEN tm.`Date`=\''.$col.'\' THEN SaleNo END) AS `'.$col.'`';
            $sqlinvamt.=', FORMAT(SUM(CASE WHEN tm.`Date`=\''.$col.'\' THEN Qty*UnitPrice END),0) AS `'.$col.'`';
            $sqltarget=$sqltarget.', FORMAT(SUM(CASE WHEN tm.`Date`=\''.$col.'\' THEN ProratedTarget END),0) AS `'.$col.'`';
            $sqltargetscore=$sqltargetscore.', CONCAT(FORMAT(SUM(CASE WHEN tm.`Date`=\''.$col.'\' THEN ((`SumOfInv`/ProratedTarget)*100) END),2),\'%\') AS `'.$col.'`';
            $days++;
            $from=strtotime($fromdate.' +'.$days.' days');
            
        }
        $columnnames[]='Total';
        $sqlsam='SELECT SAM, CONCAT(FirstName," ",SurName) AS FullName FROM `attend_1branchgroups` bg JOIN `1employees` e ON e.IDNo=bg.SAM '.(allowedToOpen(6101,'1rtc')?' WHERE SAM='.$_SESSION['(ak0)']:'').' GROUP BY bg.SAM';
        $stmt1=$link->query($sqlsam); $ressam=$stmt1->fetchAll();
        foreach ($ressam as $sam){
            $samid=$sam['SAM'];
            $condition=' AND tm.BranchNo IN (Select `BranchNo` FROM `attend_1branchgroups` WHERE SAM='.$samid.') ';
            $conditioncall=' AND tm.TLIDNo IN (Select `TeamLeader` FROM `attend_1branchgroups` WHERE SAM='.$samid.') ';
            $conditiontagged=' AND tm.TeamLeader IN (Select `TeamLeader` FROM `attend_1branchgroups` WHERE SAM='.$samid.') ';
        
       $sql1='CREATE TEMPORARY TABLE `Summary'.$samid.'` (
            `Title` varchar(60) NOT NULL '.$sqltbl.', `Total` varchar(45) NOT NULL)
SELECT \'Daily Sales\' AS Title '.$sqlsales.', FORMAT(ROUND(SUM(Qty*UnitPrice),0),0) AS `Total` FROM invty_2sale` tm JOIN invty_2salesub` ts ON tm.TxnID=ts.TxnID WHERE (tm.`Date`)>=\''.$fromdate.'\' AND (tm.`Date`)<=\''.$todate.'\' '.$condition;
        $stmt1=$link->prepare($sql1); $stmt1->execute(); 
        $sql1='INSERT INTO `Summary'.$samid.'` 
SELECT \'Telephone Calls\' AS Title '.$sqltel.', COUNT(DISTINCT(ClientName)) AS `Total` FROM `calllogs_2telmain` tm JOIN `calllogs_2telsub` ts ON tm.TxnID=ts.TxnID WHERE (tm.`Date`)>=\''.$fromdate.'\' AND (tm.`Date`)<=\''.$todate.'\' '.$conditioncall;
        $stmt1=$link->prepare($sql1); $stmt1->execute(); 
        $sql1='INSERT INTO `Summary'.$samid.'`  
SELECT \'Client Visits\' AS Title '.$sqlvisit.', COUNT(DISTINCT(ClientName)) AS `Total` FROM `calllogs_2visitmain` tm JOIN `calllogs_2visitsub` ts ON tm.TxnID=ts.TxnID WHERE (tm.`VisitDate`)>=\''.$fromdate.'\' AND (tm.`VisitDate`)<=\''.$todate.'\' '.$conditioncall;
        $stmt1=$link->prepare($sql1); $stmt1->execute();
        $sql1='INSERT INTO `Summary'.$samid.'`  
SELECT \'Formal Quotations\' AS Title '.$sqlquote.', COUNT(DISTINCT(ClientName)) AS `Total` FROM `quotations_2quotemain` tm WHERE (tm.`QuoteDate`)>=\''.$fromdate.'\' AND (tm.`QuoteDate`)<=\''.$todate.'\'  AND tm.EncodedByNo IN (Select `TeamLeader` FROM `attend_1branchgroups` WHERE SAM='.$samid.') ';
        $stmt1=$link->prepare($sql1); $stmt1->execute();
        $sql1='INSERT INTO `Summary'.$samid.'`  
SELECT \'No. of Tagged Invoices\' AS Title '.$sqlinv.', COUNT(DISTINCT(SaleNo)) AS `Total` FROM `invty_2sale` tm WHERE (tm.`Date`)>=\''.$fromdate.'\' AND (tm.`Date`)<=\''.$todate.'\' '.$conditiontagged;
        $stmt1=$link->prepare($sql1); $stmt1->execute();
        $sql1='INSERT INTO `Summary'.$samid.'`  
SELECT \'Total Amount of Tagged Invoices\' AS Title '.$sqlinvamt.', FORMAT(ROUND(SUM(Qty*UnitPrice),0),0) AS `Total` FROM `invty_2sale` tm JOIN `invty_2salesub` ts ON tm.TxnID=ts.TxnID WHERE (tm.`Date`)>=\''.$fromdate.'\' AND (tm.`Date`)<=\''.$todate.'\' '.$conditiontagged;
        $stmt1=$link->prepare($sql1); $stmt1->execute(); 
        $sql1='INSERT INTO `Summary'.$samid.'`  
SELECT CONCAT("&nbsp; &nbsp; &nbsp;","OP Approvals - ",b.Branch) AS Title '.$sqlinv.', COUNT(DISTINCT(SaleNo)) AS `Total` FROM `invty_2sale` tm JOIN `invty_7opapproval` oa ON tm.TxnID=oa.TxnID JOIN `1branches` b ON b.BranchNo=tm.BranchNo WHERE (tm.`Date`)>=\''.$fromdate.'\' AND (tm.`Date`)<=\''.$todate.'\' '.$condition.' GROUP BY tm.BranchNo';
        $stmt1=$link->prepare($sql1); $stmt1->execute();
        
        $sql='SELECT * FROM `Summary'.$samid.'`'; $hidecount=true;
        $subtitle='<br><br>'.$sam['FullName'];
        include('../backendphp/layout/displayastableonlynoheaders.php');
        
        }
    break;
}

?>