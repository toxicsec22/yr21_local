<?php
$sql0='CREATE TEMPORARY TABLE latestcostperitemstep1 AS select 
        `lc`.`ItemCode` AS `ItemCode`,
        max(`lc`.`Date`) AS `MaxOfDate`,
	SupplierNo
    from
        `invty_50unibegandmrrforlatestcost` `lc`
    group by `lc`.`ItemCode`,`lc`.`SupplierNo`;';
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
	
/*$sql0='CREATE TEMPORARY TABLE costlistperitem AS SELECT m.Date as PurchaseDate,m.SupplierNo,n.SupplierName,s.ItemCode,s.UnitCost FROM invty_2mrr m join invty_2mrrsub s on m.TxnID=s.TxnID join latestcostperitemstep1 lc on s.ItemCode=lc.ItemCode and m.SupplierNo=lc.SupplierNo join `1suppliers` n on n.SupplierNo=m.SupplierNo where lc.ItemCode=s.ItemCode and m.Date=lc.MaxofDate;';*/
$sql0='CREATE TEMPORARY TABLE costlistperitem AS SELECT ms.Date as PurchaseDate,ms.SupplierNo,s.SupplierName,ms.ItemCode,ms.UnitCost FROM invty_50unibegandmrrforlatestcost ms join latestcostperitemstep1 lc on ms.ItemCode=lc.ItemCode and ms.SupplierNo=lc.SupplierNo join `1suppliers` s on s.SupplierNo=ms.SupplierNo where lc.ItemCode=ms.ItemCode and ms.Date=lc.MaxofDate;';
$stmt0=$link->prepare($sql0);
$stmt0->execute();

?>