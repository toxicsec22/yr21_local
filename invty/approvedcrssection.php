<?php
$editprocess=null;$coltototal=null;$showgrandtotal=null;
$sql='Select sr.*, e.Nickname as ApprovedBy from `approvals_2salesreturns` sr join invty_2sale sm on sm.PONo=sr.Approval left join `1employees` e on e.IDNo=sr.ApprovedByNo where sm.TxnID='.$txnid;

$columnnames=array('ItemCode','AmountofReturn','Reason','ApprovedBy');
include('../backendphp/layout/displayastableonlynoheaders.php');
?>