<?php
function renderotherlist($otherlist,$listcondition){
    global $currentyr;
$linklist=connect_db($currentyr.'_1rtc',0); 

switch($otherlist){
    case 'accounts':        
        $listname='accounts';
        $listsql='SELECT ShortAcctID as Account, CONCAT(AccountID," ",AccountDescription) AS AccountID  FROM acctg_1chartofaccounts '.$listcondition.' order by ShortAcctID;';
        $listvalue='Account';
        $listlabel='AccountID';
        $listcaption='Account';
        break;
    case 'collectorlist':	
        $listsql='Select a.IDNo, concat(e.FirstName,\' \', e.Surname) as FullName, p.Position from `attend_2attendance` a join `1employees` as e on e.IDNo=a.IDNo join attend_30currentpositions p on p.IDNo=a.IDNo  where (((a.DateToday=\''.date('Y-m-d').'\' and a.BranchNo='.$_SESSION['bnum'].') or p.PositionID in(1,11,12,32,37,81)) and (e.Resigned=0 and p.PositionID<>0)) group by a.IDNo order by p.JLID desc, e.FirstName asc';
        $listvalue='IDNo';
        $listlabel='FullName';
        $listcaption='Collector';
	break;
    case 'unpaidinvoices':	
	$listsql='Select Particulars, concat(InvBalance, \' \',`Branch`, \' \',`Date`) as Details from acctg_unpaidinv c join `1branches` as b on b.BranchNo= c.BranchNo where c.ClientNo='.$listcondition.' UNION
SELECT SaleNo AS Particulars, CONCAT(SUM(Qty*UnitPrice)+IFNULL(op.Amount,0), \' \',b.Branch) AS Details FROM `invty_2sale` sm JOIN `invty_2salesub` ss ON sm.TxnID=ss.TxnID LEFT JOIN `invty_7opapproval` op ON op.TxnID=sm.TxnID
JOIN `1branches` b ON b.BranchNo=sm.BranchNo
WHERE ClientNo='.$listcondition.' AND sm.PaymentType=2 AND sm.Date=CURDATE() AND sm.BranchNo='.$_SESSION['bnum'].' GROUP BY sm.TxnID ';
        $listvalue='Particulars';
        $listlabel='Details';
        $listcaption='Particulars';
	break;
    case 'unpaidsuppinvoices':	
	$listsql='Select SupplierInv, concat(date_format(`Date`,\'%Y-%m-%d\'),\' \', PayBalance,\' \', Branch) as Details from acctg_23balperinv i join `1branches` as b on b.BranchNo= i.BranchNo where i.PayBalance<>0 and i.BranchNo='.$_SESSION['bnum'].($listcondition==''?'':' and i.SupplierNo='.$listcondition);
	//echo $listsql;break;
        $listvalue='SupplierInv';
        $listlabel='Details';
        $listcaption='SupplierInv';
	break;   
    
}
?>
<datalist id="<?php echo $otherlist ?>" style="height: 150px; width: 150px; overflow: auto"> 
<?php  
		foreach ($linklist->query($listsql) as $row) {
                ?>
                <option value="<?php echo $row[$listvalue]; ?>" label="<?php echo $row[$listlabel]; ?>"></option>
                <?php
                } // end while
                ?>
</datalist id="<?php echo $otherlist ?>">
<?php
nolist:
}
