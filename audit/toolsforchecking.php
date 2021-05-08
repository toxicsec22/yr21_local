<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if(!allowedToOpen(7573,'1rtc')) { echo 'No permission'; exit;}
 

$showbranches=false; include_once('../switchboard/contents.php');


$which=$_GET['w'];

switch ($which){
case 'RepackQtyLess': 
    $title='Repack Qty <> Calculated';
    $sql0='CREATE TEMPORARY TABLE `unequalrepack` AS SELECT Branch, e.Nickname AS RepackEncodedBy, 
DateOUT, ts.TxnID, rp.BulkItemCode, rp.RepackItemCode, SUM(QtySent*rp.RepackQtyPerBulkUnit) AS RepackQtyShouldBe, 
ts2.RecordedRepacked FROM invty_2transfersub ts
JOIN `invty_2transfer` tm ON tm.TxnID=ts.TxnID AND tm.txntype=12
JOIN `invty_1itemsforrepack` rp ON rp.BulkItemCode=ts.ItemCode
JOIN (SELECT TxnID, ItemCode, FromEncodedByNo, SUM(QtyReceived) AS RecordedRepacked FROM invty_2transfersub GROUP BY ItemCode, TxnID) ts2  ON ts2.TxnID=ts.TxnID AND ts2.ItemCode=rp.RepackItemCode
JOIN `1branches` b ON b.BranchNo=tm.BranchNo JOIN `1employees` e ON e.IDNo=ts2.FromEncodedByNo
GROUP BY ts.TxnID,rp.BulkItemCode HAVING (RepackQtyShouldBe<>RecordedRepacked) ORDER BY Branch,DateOUT;';
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    $sql='SELECT *, FORMAT(((RepackQtyShouldBe-RecordedRepacked)/RepackQtyShouldBe)*100,2) AS `PercentDifference`  FROM `unequalrepack`';
    $columnnames=array('Branch', 'RepackEncodedBy', 'DateOUT', 'BulkItemCode', 'RepackItemCode', 'RepackQtyShouldBe', 'RecordedRepacked','PercentDifference');
    $txnidname='TxnID'; $editprocess='../invty/addedittxfr.php?w=Transfers&TxnID='; $editprocesslabel='Lookup';
    include('../backendphp/layout/displayastable.php'); 
    break;

case 'RepackFromToDate': 
    $title='Repack Summary'; $pagetouse='toolsforchecking.php?w=RepackFromToDate';
    include('../backendphp/layout/fromtodate.php');
    $formdesc='From '.$fromdate.' To '.$todate.'<br/><br/>';
    $sql0='CREATE TEMPORARY TABLE `unequalrepack` AS SELECT Branch, rp.BulkItemCode, rp.RepackItemCode, SUM(QtySent) AS BulkQtyValue, SUM(QtySent*rp.RepackQtyPerBulkUnit) AS RepackQtyShouldBeValue, 
SUM(ts2.RecordedRepacked) AS RecordedRepackedValue FROM invty_2transfersub ts
JOIN `invty_2transfer` tm ON tm.TxnID=ts.TxnID AND tm.txntype=12
JOIN `invty_1itemsforrepack` rp ON rp.BulkItemCode=ts.ItemCode
JOIN (SELECT TxnID, ItemCode, SUM(QtyReceived) AS RecordedRepacked FROM invty_2transfersub GROUP BY ItemCode, TxnID) ts2  ON ts2.TxnID=ts.TxnID AND ts2.ItemCode=rp.RepackItemCode
JOIN `1branches` b ON b.BranchNo=tm.BranchNo 
WHERE tm.DateOUT BETWEEN \''.$fromdate.'\' AND \''.$todate.'\'
GROUP BY rp.BulkItemCode ORDER BY Branch;'; 
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    $sql='SELECT ur.*, CONCAT(c.Category, " ",i.ItemDesc) AS BulkItem, '
            . 'CONCAT("<font color=blue><b>",IF(MOD(BulkQtyValue,1)<>0,FORMAT(BulkQtyValue,2),FORMAT(BulkQtyValue,0)),"</b></font>") AS BulkQty,'
            . 'i2.ItemDesc AS RepackItem, '
            . 'CONCAT("<font color=darkgreen><b>",IF(MOD(RecordedRepackedValue,1)<>0,FORMAT(RecordedRepackedValue,2),FORMAT(RecordedRepackedValue,0)),"</b></font>") AS RecordedRepacked,'
            . 'IF(MOD(RepackQtyShouldBeValue,1)<>0,FORMAT(RepackQtyShouldBeValue,2),RepackQtyShouldBeValue) AS RepackQtyShouldBe,  FORMAT(((RepackQtyShouldBeValue-RecordedRepackedValue)/RepackQtyShouldBeValue)*100,2) AS `PercentDifference`  FROM `unequalrepack` ur JOIN `invty_1items` i ON i.ItemCode=ur.BulkItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo'
            . ' JOIN `invty_1items` i2 ON i2.ItemCode=ur.RepackItemCode ';
    $columnnames=array('Branch',  'BulkItemCode', 'BulkItem','BulkQty', 'RepackItemCode','RepackItem', 'RepackQtyShouldBe', 'RecordedRepacked','PercentDifference');
    include('../backendphp/layout/displayastablenosort.php'); 
    break;
}
noform:
      $link=null; $stmt=null;
?>