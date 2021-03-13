<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(754,'1rtc')) {   echo 'No permission'; exit;} 
 
$link=connect_db("".$currentyr."_1rtc",1);

// makes  table invty_52latestcost for faster calculations
$sql0='drop table if exists `invty_52latestcost`';
$stmt=$link->prepare($sql0);
    $stmt->execute();
$sql0='CREATE TABLE `invty_52latestcost` (
  `ItemCode` smallint(6) NOT NULL DEFAULT "0",
  `Date` date DEFAULT NULL,
  `UnitCost` double DEFAULT NULL,
  KEY `itemlatestcostidx` (`ItemCode`)
)
SELECT 
        `lc`.`ItemCode` AS `ItemCode`,
        `lc`.`Date` AS `Date`,
        `lc`.`UnitCost` AS `UnitCost`
    FROM
        (`invty_50unibegandmrrforlatestcost` `lc`
        JOIN `invty_51latestcoststep1` `d` ON (((`lc`.`ItemCode` = `d`.`ItemCode`)
            AND (`lc`.`Date` = `d`.`MaxOfDate`))))
    GROUP BY `lc`.`ItemCode`;';
//IF($_SESSION['(ak0)']==1002){ echo $sql0; exit();}
    $stmt=$link->prepare($sql0);
    $stmt->execute();
    
// makes  table invty_5latestminprice for faster calculations
$sql0='drop table if exists `invty_5latestminprice`';
$stmt=$link->prepare($sql0);
    $stmt->execute();
$sql0='CREATE TABLE `invty_5latestminprice` (
  `ItemCode` smallint(6) NOT NULL DEFAULT "0",
  `Date` date NOT NULL ,
  `PriceLevel1` double NOT NULL DEFAULT "0",
  `PriceLevel2` double NOT NULL DEFAULT "0",
  `PriceLevel3` double NOT NULL DEFAULT "0",
  `PriceLevel4` double NOT NULL DEFAULT "0",
  `PriceLevel5` double NOT NULL DEFAULT "0",
  KEY `itemcodempidx` (`ItemCode`)
)
SELECT 
        `lmp`.`ItemCode` AS `ItemCode`,
        `lmp`.`Date` AS `Date`,
        `lmp`.`PriceLevel1` AS `PriceLevel1`,
        `lmp`.`PriceLevel2` AS `PriceLevel2`,
        `lmp`.`PriceLevel3` AS `PriceLevel3`,
        `lmp`.`PriceLevel4` AS `PriceLevel4`,
        `lmp`.`PriceLevel5` AS `PriceLevel5`
    FROM
        (`invty_53latestminpriceunion` `lmp`
        JOIN `invty_54latestminpricestep1` `d` ON (((`lmp`.`ItemCode` = `d`.`ItemCode`)
            AND (`lmp`.`Date` = `d`.`MaxOfDate`))))
    GROUP BY `lmp`.`ItemCode`;';
//IF($_SESSION['(ak0)']==1002){ echo $sql0; exit();}
    $stmt=$link->prepare($sql0);
    $stmt->execute();
 $link=null; $stmt=null;
header("Location:".$_SERVER['HTTP_REFERER']."?done=1");
 
?>