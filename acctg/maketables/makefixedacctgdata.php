<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$link1=connect_db("".$currentyr."_1rtc",1);
$sql0='SELECT MONTH(`DataClosedBy`) as ClosedMonth FROM `00dataclosedby` WHERE (`00dataclosedby`.`ForDB` = 1)';
$stmt=$link->query($sql0); $res0=$stmt->fetch(); $closedmonth=((substr($_SESSION['nb4A'],0,4)<$currentyr))?0:$res0['ClosedMonth'];
switch ($whichdata){
    case 'withcurrent':
        $sql0='drop table if exists `acctg_0unialltxns`'; $stmt=$link1->prepare($sql0); $stmt->execute();        
        $condition='Month(`Date`)>'.$closedmonth.' AND YEAR(`Date`)='.$currentyr.''; $conditionpaid='Month(`DatePaid`)>'.$closedmonth.' AND YEAR(`DatePaid`)='.$currentyr.'';
        $conditionin='Month(`DateIN`)>'.$closedmonth.' '; $conditionbounced='Month(`DateBounced`)>'.$closedmonth.' ';
        
        $conditiondepr='Month(`DeprDate`)>'.$closedmonth.' AND Month(`DeprDate`)<=MONTH(CURDATE()) AND YEAR(`DeprDate`)='.$currentyr.'';
        $conditionprepd='Month(`AmortDate`)>'.$closedmonth.' AND Month(`AmortDate`)<=MONTH(CURDATE()) AND YEAR(`AmortDate`)='.$currentyr.'';
        $sql0='CREATE TABLE `acctg_0unialltxns` (
           `Date` date NOT NULL,
          `ControlNo` varchar(200) NOT NULL DEFAULT \'\', `BECS` VARCHAR(1),
          `SuppNo/ClientNo`  smallint(6) DEFAULT NULL,
          `Supplier/Customer/Branch` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
          `Particulars` varchar(300) DEFAULT NULL,
          `AccountID` smallint(6) NOT NULL,
          `BranchNo` smallint(6) NOT NULL,
          `FromBudgetOf` smallint(6) NOT NULL DEFAULT \'0\',
          `Amount` double NOT NULL DEFAULT \'0\',
          `Forex` double NOT NULL DEFAULT \'1\',
          `PHPAmount` double NOT NULL DEFAULT \'0\',
          `Entry` varchar(2) CHARACTER SET utf8 NOT NULL DEFAULT \'\',
          `w` varchar(16) CHARACTER SET utf8 NOT NULL DEFAULT \'\',
          `TxnID` bigint(11) NOT NULL DEFAULT \'0\'
        ) AS 
        SELECT 
        `Date` AS `Date`,
        `ControlNo` AS `ControlNo`, `BECS`,
        `SuppNo/ClientNo` AS `SuppNo/ClientNo`,
        `Supplier/Customer/Branch` AS `Supplier/Customer/Branch`,
        `Particulars` AS `Particulars`,
        `AccountID` AS `AccountID`,
        `BranchNo` AS `BranchNo`,
		`FromBudgetOf` AS `FromBudgetOf`,
        IFNULL(`Amount`,0) AS `Amount`, IFNULL(Forex,1) AS Forex, IFNULL(`Amount`,0)*IFNULL(Forex,1) AS PHPAmount,
        `Entry` AS `Entry`,
        `w` AS `w`,
        `TxnID` AS `TxnID`
    FROM
        `'.$currentyr.'_static`.`acctg_unialltxns`  WHERE YEAR(`Date`)='.$currentyr.' AND (Month(`Date`)<='.$closedmonth.($closedmonth>0?')':') OR (`w` LIKE "BegBal") ');
       
        if ($reportmonth>$closedmonth) {
        include_once('sqlphp/sqlalltxnsforfixed.php'); 
        $sql0=$sql0.' UNION ALL '.$sql1; // if($_SESSION['(ak0)']==1002) {echo $sql0;}
        }
        $stmt=$link1->prepare($sql0); $stmt->execute(); 
        break;
        
        case 'static': //This must be done first before fsstatic.  Both are called in closedataautoadj.php upon protection of data.
        $month=!isset($month)?$closedmonth:$_REQUEST['month'];
        $sql0='drop table if exists `'.$currentyr.'_static`.`acctg_unialltxns`'; $stmt=$link1->prepare($sql0); $stmt->execute();
        $condition='Month(`Date`)<='.$month; $conditionpaid='Month(`DatePaid`)<='.$month; $conditionin='Month(`DateIN`)<='.$month; 
        $conditionbounced='Month(`DateBounced`)<='.$closedmonth.' ';
		
        $conditiondepr='Month(`DeprDate`)<='.$month.' AND YEAR(`DeprDate`)='.$currentyr.'';
        $conditionprepd='Month(`AmortDate`)<='.$month.' AND YEAR(`AmortDate`)='.$currentyr.'';
        include_once('sqlphp/sqlalltxnsforfixed.php');     
         $sql0='CREATE TABLE `'.$currentyr.'_static`.`acctg_unialltxns` (
   `Date` DATE NULL,
   `ControlNo` varchar(200)  NULL, `BECS` VARCHAR(1),
   `SuppNo/ClientNo` smallint(6)  NULL,
   `Supplier/Customer/Branch` varchar(200)  NULL,
   `Particulars` varchar(300)  NULL,
   `AccountID` smallint(6) NOT NULL DEFAULT "0",
   `BranchNo` smallint(6) NOT NULL DEFAULT "0",
   `FromBudgetOf` smallint(6) NOT NULL DEFAULT "0",
   `Amount` double DEFAULT 0,
   `Forex` double DEFAULT 1,
   `PHPAmount` double DEFAULT 0,
   `Entry` varchar(2) NOT NULL DEFAULT "",
   `w` varchar(16) NOT NULL DEFAULT "",
   `TxnID` int(11) NOT NULL DEFAULT "0",
   KEY `SuppClientidx` (`SuppNo/ClientNo`),
   KEY `AcctIDidx` (`AccountID`),
   KEY `Branchidx` (`BranchNo`),
   KEY `Entryidx` (`Entry`),
   KEY `TxnIDidx` (`TxnID`)
 )  ';
       
	
	// $sql1='CREATE TABLE `'.$currentyr.'_static`.`acctg_unialltxns` AS '
        $sql1=$sql0.' SELECT "'.$currentyr.'-01-01" AS `Date`, "BegBal" AS `ControlNo`, "B" AS `BECS`,
        "0" AS `SuppNo/ClientNo`,
        "-" AS `Supplier/Customer/Branch`,
        "Beginning Balance" AS `Particulars`,
        `bb`.`AccountID` AS `AccountID`,
        `bb`.`BranchNo` AS `BranchNo`,
	`bb`.`BranchNo` AS `FromBudgetOf`,
        `bb`.`BegBalance` AS `Amount`, `bb`.`Forex`, `bb`.`BegBalance`*`bb`.`Forex` AS `PHPAmount`, 
        "DR" AS `Entry`,
        "BegBal" AS `w`,
        0 AS `TxnID`
    FROM
        `acctg_1begbal` `bb`  join `1branches` `b` ON b.BranchNo=bb.BranchNo
    UNION ALL 
    '.$sql1;
	
    //if($_SESSION['(ak0)']==1002) { echo '<br><br>'.$month.'c'.$closedmonth.'r'.$_REQUEST['month'].$sql1.'<br><br>'; }
	
        $stmt=$link1->prepare($sql1); $stmt->execute();
        
        break;
    
        case 'staticfs': //static must be done first.  Both are called in closedataautoadj.php upon protection of data.
        $month=!isset($month)?$_REQUEST['month']:$closedmonth;
        $sql0='drop table if exists `'.$currentyr.'_static`.`acctg_fsvalues`'; $stmt=$link1->prepare($sql0); $stmt->execute();
        $sql0='CREATE TABLE `'.$currentyr.'_static`.`acctg_fsvalues` AS SELECT uni.AccountID, BranchNo, (CASE WHEN w LIKE "BegBal" THEN 0 ELSE (Month(Date)) END) as FSMonth, ROUND(SUM(Amount),2) as `Bal`
        FROM '.$currentyr.'_static.acctg_unialltxns uni JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=uni.AccountID WHERE YEAR(Date)='.$currentyr.' GROUP BY BranchNo, uni.AccountID, FSMonth;'; // echo $sql0; break;
        $stmt=$link1->prepare($sql0);$stmt->execute();
        break;
    
}

?>