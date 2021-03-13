<?php
if (allowedToOpen(310,'1rtc')){
$subtitle='Data Error';

include_once('sqlphp/cashsalesvsdeposits.php');
    $sql=$sqlcheck;
    $columnnames=array('Branch','SaleDate','CashSales','CashDep','Diff'); $hidecount=true;
    include('../backendphp/layout/displayastableonlynoheaders.php'); echo '<br>';
    unset ($subtitle);
}
 ?>