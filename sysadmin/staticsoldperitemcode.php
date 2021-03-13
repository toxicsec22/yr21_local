<?php

$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(3002,'1rtc')) { echo 'No permission'; exit; }

include_once $path.'/acrossyrs/dbinit/userinit.php';

if (allowedToOpen(2201,'1rtc')){
        error_reporting(E_ALL);
	ini_set('display_errors', 1);
}

$link=connect_db(''.$currentyr.'_1rtc',1);

echo '<title>Create Static Data for Sold per Items</title>';
echo '<form action="staticsoldperitemcode.php" method="POST">Year: <input type="number" name="Yr" autocomplete="off"> <input type="submit" name="btnUpdate" value="Create Static Data for Sold per Item Code"></form>';

if (isset($_POST['btnUpdate'])){
	
	if ($_POST['Yr']>=date('Y')){
		echo 'Invalid Date. Should be less than '.date('Y').'.'; exit();
	}
	

 $sql0='
CREATE TABLE `datamonth` AS SELECT ss.ItemCode,Month(sm.`Date`) AS MonthNo,TRUNCATE(Sum(ss.Qty),2) AS Sold FROM '.$_POST['Yr'].'_1rtc.invty_2sale as sm INNER JOIN '.$_POST['Yr'].'_1rtc.invty_2salesub as ss ON sm.TxnID=ss.TxnID join '.$_POST['Yr'].'_1rtc.invty_1items i on i.ItemCode=ss.ItemCode JOIN '.$_POST['Yr'].'_1rtc.invty_1category c ON c.CatNo=i.CatNo where txntype in (1,2) GROUP BY ss.ItemCode,Month(sm.`Date`);';
$stmt=$link->prepare($sql0); $stmt->execute();

$sql0='
CREATE TABLE `hist_incus`.`'.$_POST['Yr'].'_soldperitemcode` AS SELECT dm.ItemCode,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=1),0) AS `01`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=2),0) AS `02`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=3),0) AS `03`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=4),0) AS `04`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=5),0) AS `05`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=6),0) AS `06`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=7),0) AS `07`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=8),0) AS `08`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=9),0) AS `09`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=10),0) AS `10`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=11),0) AS `11`,IFNULL((select Sold from datamonth WHERE ItemCode=dm.ItemCode AND MonthNo=12),0) AS `12` FROM datamonth dm GROUP BY dm.ItemCode;
';
$stmt=$link->prepare($sql0); $stmt->execute();


 $sql0='
DROP TABLE `datamonth`;';
$stmt=$link->prepare($sql0); $stmt->execute();


echo '<br>Static data created successfully.';
}

 $stmt=null; $link=null;
?>




