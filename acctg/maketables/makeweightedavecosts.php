<?php
// MADE THIS RUN IN CRON EVERY 1ST OF THE MONTH
//$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
//if (!allowedToOpen(6455,'1rtc')) { echo 'No permission'; exit; }
$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/dbinit/userinit.php');
$url_array = parse_url($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
preg_match('@/(?<path>[^/]+)@', $url_array['path'], $m);
$url_folder = $m['path'];
$currentyr=!isset($currentyr)?'20'.substr($url_folder,2,2):$currentyr;
$month=!isset($month)?date('m'):$month;
//echo $currentyr.'<br>'.$month; exit();
 
$link=connect_db("".$currentyr."_1rtc",1);
$month=str_pad($month,2,'0',STR_PAD_LEFT);
$sql0='Select sum(`'.$month.'`) as SumAveCost from `' . $currentyr . '_static`.`invty_weightedavecost`';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
//if ($res0['SumAveCost']<>0){header("Location:/'.$url_folder.'/acctg/closedataautoadj.php?which=WtdAveCosts&done=2"); exit();}
$sqlcreate='create temporary table `' . $currentyr . '_static`.`invty_unibegmrrcostqty` (
    ItemCode smallint(6) not null,
    TotalValue double not null,
    Qty double not null
    )';
    if ($month==00){
    $sql='select 
            `i`.`ItemCode` AS `ItemCode`,
            (`b`.`BegCost` * `b`.`BegInv`) AS `TotalValue`,  `b`.`BegInv` AS `Qty`
        from
            (`invty_1items` i
            join `invty_1beginv` b ON ((`i`.`ItemCode` = `b`.`ItemCode`))) JOIN `1branches` br ON br.BranchNo=b.BranchNo
        where
            ((`br`.`Pseudobranch`=2)
                and (`i`.`CatNo` <> 1)) ';
    } else {
       
        $sql=$sqlcreate.' 
        select 
            `i`.`ItemCode` AS `ItemCode`,
            (`wac`.`'.str_pad(($month-1),2,'0',STR_PAD_LEFT).'` * SUM(`a`.`Qty`)) AS `TotalValue`, SUM(`a`.`Qty`) AS `Qty`
        from
            (`invty_1items` i LEFT JOIN `' . $currentyr . '_static`.`invty_unialltxns` `a` ON ((`i`.`ItemCode` = `a`.`ItemCode`)))
            JOIN `' . $currentyr . '_static`.`invty_weightedavecost` wac ON `i`.`ItemCode` = `wac`.`ItemCode`
            JOIN `1branches` b ON b.BranchNo=a.BranchNo 
        where
            ((`b`.`Pseudobranch`=2) and (`i`.`CatNo` <> 1) AND MONTH(a.`Date`)<'.$month.') 
        GROUP BY `i`.`ItemCode`
        UNION ALL    
        select 
            `s`.`ItemCode` AS `ItemCode`,
            (`s`.`UnitCost` * `s`.`Qty`) AS `TotalValue`, SUM(`s`.`Qty`) AS `Qty`
        from
            ((`invty_1items` i
            join `invty_2mrrsub` s ON ((`i`.`ItemCode` = `s`.`ItemCode`)))
            join `invty_2mrr` m ON ((`s`.`TxnID` = `m`.`TxnID`)))
            JOIN `1branches` b ON b.BranchNo=m.BranchNo 
        where
            ((`i`.`CatNo` <> 1)
                and (`b`.`Pseudobranch`=2) and MONTH(`m`.`Date`)='.$month.') 
        union all select 
            `rlc`.`ItemCode` AS `ItemCode`,
            (`rlc`.`UnitCost` * `rlc`.`Qty`) AS `TotalValue`, SUM(`rlc`.`Qty`) AS `Qty`
        from
            `invty_500repackforlatestcost` `rlc`
        where
            (Month(`rlc`.`Date`)='.$month.')';
    }

// echo $sql; break;
$stmt0=$link->prepare('drop temporary table if exists `' . $currentyr . '_static`.`invty_unibegmrrcostqty`;');$stmt0->execute();
$stmt=$link->prepare($sql);$stmt->execute();
$stmt0=$link->prepare('drop table if exists  `' . $currentyr . '_static`.`temp_wtdavecost`;');$stmt0->execute();

$sql='create table  `' . $currentyr . '_static`.`temp_wtdavecost` (
ItemCode smallint(6) not null,
WtdAveCost double not null
)
SELECT bm.ItemCode, If(Sum(`Qty`)<>0,(round((Sum(`TotalValue`)/Sum(`Qty`)),2)),`'.str_pad(($month==00?$month:($month-1)),2,'0',STR_PAD_LEFT).'`) AS WtdAveCost
FROM  `' . $currentyr . '_static`.`invty_unibegmrrcostqty` bm left join `' . $currentyr . '_static`.`invty_weightedavecost` wac on bm.ItemCode=wac.ItemCode GROUP BY bm.ItemCode;';
$stmt=$link->prepare($sql);$stmt->execute();

// update wac of those with transactions
$sql='update `' . $currentyr . '_static`.`invty_weightedavecost` wac join `' . $currentyr . '_static`.`temp_wtdavecost` wc on wac.ItemCode=wc.ItemCode set `'.$month.'`= wc.WtdAveCost';
$stmt=$link->prepare($sql);$stmt->execute();

// make wac same as last month for no transactions
$sql='update `' . $currentyr . '_static`.`invty_weightedavecost` wac LEFT join `' . $currentyr . '_static`.`temp_wtdavecost` wc on wac.ItemCode=wc.ItemCode '
        . ' SET `'.$month.'`= `wac`.`'.str_pad(($month-1),2,'0',STR_PAD_LEFT).'` WHERE wc.ItemCode IS NULL';
$stmt=$link->prepare($sql);$stmt->execute();

// insert wac of new items
$sql='insert into `' . $currentyr . '_static`.`invty_weightedavecost` (`ItemCode`,`'.$month.'`) select wc.ItemCode, wc.WtdAveCost from `' . $currentyr . '_static`.`temp_wtdavecost` wc left join `' . $currentyr . '_static`.`invty_weightedavecost` wac on wc.ItemCode=wac.ItemCode where wac.ItemCode is null';
$stmt=$link->prepare($sql);$stmt->execute();
$stmt0=$link->prepare('drop table if exists  `' . $currentyr . '_static`.`temp_wtdavecost`;');$stmt0->execute();
?>