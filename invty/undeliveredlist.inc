<?php
function renderotherlistinv($otherlist,$listcondition){
    global $currentyr;
$linkinfunction=connect_db($currentyr.'_1rtc',0);
switch($otherlist){
    case 'internalout':
        $listsql='select ud.ItemCode, concat(c.Category,\' \', i.ItemDesc,\' \',ud.SendBal,\' \', i.Unit) as Description from invty_44undeliveredreq as ud join invty_1items i on i.ItemCode=ud.ItemCode join invty_1category c on c.CatNo=i.CatNo 
        where ud.Posted<>0 and SendBal<>0 and SupplierBranchNo='.$_SESSION['bnum']
	. ' and RequestNo=\''. $listcondition . '\'';
        $listvalue='ItemCode';
        $listlabel='Description';
        $listcaption='ItemCode';
	break;
    case 'internalin':
        $listsql='select ud.ItemCode, concat(c.Category,\' \', i.ItemDesc,\' \',ud.RcvBal,\' \', i.Unit) as Description from invty_44undeliveredreq as ud join invty_1items i on i.ItemCode=ud.ItemCode join invty_1category c on c.CatNo=i.CatNo 
        where ud.Posted<>0 and RcvBal<>0 and BranchNo='.$_SESSION['bnum']
	. ' and RequestNo=\''. $listcondition . '\'';	
        $listvalue='ItemCode';
        $listlabel='Description';
        $listcaption='ItemCode';
	break;
    case 'externalreqno':
	$listsql='SELECT RequestNo FROM invty_40pendingextrequests WHERE Posted<>0 and Pending<>0 group by RequestNo';
	$listvalue='RequestNo';
        $listlabel='RequestNo';
	break;
    case 'externalitems':
	$listsql='SELECT r.ItemCode,concat(r.RequestNo,\' \',c.Category,\' \', i.ItemDesc,\' \',r.Pending, \' \', i.Unit) as Description FROM invty_40pendingextrequests r join invty_1items i on i.ItemCode=r.ItemCode join invty_1category c on c.CatNo=i.CatNo WHERE r.Posted<>0 and r.Pending<>0 group by r.ItemCode,r.RequestNo';	
	$listvalue='ItemCode';
        $listlabel='Description';
	break;
    case 'openpo':
	$listsql='SELECT PONo, SupplierName FROM `invty_41supplierundelivered` ud
join `1suppliers` s on s.SupplierNo=ud.SupplierNo
WHERE SupplierUndelivered<>0 group by ud.SupplierNo,PONo';
	$listvalue='PONo';
        $listlabel='SupplierName';
	break;
    case 'mrritemsperpo':
        $listsql='select ud.ItemCode, concat(c.Category,\' \', i.ItemDesc,\' \',ud.SupplierUndelivered,\' \', i.Unit) as Description from invty_41supplierundelivered as ud join invty_1items i on i.ItemCode=ud.ItemCode join invty_1category c on c.CatNo=i.CatNo 
        where SupplierUndelivered<>0 '. ' and PONo=\''. $listcondition . '\'';
        $listvalue='ItemCode';
        $listlabel='Description';
        $listcaption='ItemCode';
	break;
    case 'unacceptedtransfermain':
        $listsql='SELECT TransferNo, Branch FROM invty_2transfer t join `1branches` b on b.BranchNo=t.BranchNo where ToBranchNo='.$_SESSION['bnum'].' and DateIN is null;';
        $listvalue='TransferNo';
        $listlabel='Branch';
        $listcaption='TransferNo';
	break;
    case 'solditemsperinv': //used for returns
        $listsql='SELECT s.ItemCode, concat(c.Category," ", i.ItemDesc," ",s.Qty," ", i.Unit," P ",s.UnitPrice) as Description FROM invty_2salesub s join invty_2sale m on m.TxnID=s.TxnID join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where SaleNo="'. $listcondition . '"';
	$listvalue='ItemCode';
        $listlabel='Description';
        $listcaption='ItemCode';
	break;
    case 'solditemsperinvlastyr': //used for returns from last year's sales WILL NOT USE THIS
        $dbsaleprefix=''.$lastyr.'_';
	$listsql='SELECT s.ItemCode, concat(c.Category,\' \', i.ItemDesc,\' \',s.Qty,\' \', i.Unit,\' P \',s.UnitPrice) as Description FROM ``'.$dbsaleprefix.'1rtc`.`invty_2salesub` s join ``'.$dbsaleprefix.'1rtc`.`invty_2sale` m on m.TxnID=s.TxnID join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo where SaleNo=\''. $listcondition . '\'';
        $listvalue='ItemCode';
        $listlabel='Description';
        $listcaption='ItemCode'; 
	break;
}
?>
<datalist id="<?php echo $otherlist ?>" style="height: 150px; width: 150px; overflow: auto"> 
<?php  
		foreach ($linkinfunction->query($listsql) as $row) {
                ?>
                <option value="<?php echo $row[$listvalue]; ?>" label="<?php echo $row[$listlabel]; ?>"></option>
                <?php
                } // end while
                ?>
</datalist id="<?php echo $otherlist ?>">
<?php
nolist:
 $link=null; $stmt=null;    
}
