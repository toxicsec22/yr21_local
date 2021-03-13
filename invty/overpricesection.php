<?php
include_once('invlayout/pricelevelcase.php');

$sqlop='Select a.* from `invty_7opapproval` a where isnull(a.TxnID) and a.InvNo like \''.$saleno.'\' and a.BranchNo='.$branch. ' AND (a.Approval IS NOT NULL) AND a.Approval<>0 '; 
//if($_SESSION['(ak0)']==1002) { echo $sqlop; }

    $stmt=$link->query($sqlop);
    $resultop=$stmt->fetch();
    if ($stmt->rowCount()>0){
        $total=$total."<a href='../acctg/approval.php?w=RecordOP&txntype=".$txntype."&TxnID=". $txnid.'&action_token='.$_SESSION['action_token']."&Approval=". $resultop['Approval']."'>Record approved overprice: P". $resultop['Amount']."</a><br><br>";
    } else {
        $sqlop='Select a.*, concat(e2.`Nickname`,\' \',e2.`SurName`) AS `ApprovedBy` from `invty_7opapproval` a join `1employees` e2 on a.EncodedByNo=e2.IDNo where a.TxnID='.$txnid;
        $stmt=$link->query($sqlop);
        $resultop=$stmt->fetch();
        if ($stmt->rowCount()>0){
        $total=$total.'<div style="border: 1px solid; width:400px">Approval No: '.$resultop['Approval'].str_repeat('&nbsp',5).'Approval By: '.$resultop['ApprovedBy'].'<br>Total Overprice :'.$resultop['Amount'].'<br>For the Client: '.(round($resultop['Amount']*(1-0.12),0)).'<br>Deduction for VAT: '.(round($resultop['Amount']*(0.12),0)).'</div><br>';
        $totalwithop=$resultop['Amount'];
        if(allowedToOpen(5221,'1rtc') and $result['Posted']==0){ echo '<a href=../acctg/approval.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&txntype='.$txntype.'&w=RemoveRecordedOP  OnClick="return confirm(\'Really remove overprice?\');">Remove Recorded OP</a>';}
    }
    }
    

   $sqlfc='SELECT fc.*, IF(PriceFreightInclusive=1,"PriceINCLUDESFreight","PriceNOFreight") as `FreightInc?`, e.`Nickname` AS `ApprovedBy` FROM `approvals_2freightclients` fc JOIN `invty_2sale` s ON s.SaleNo=fc.ForInvoiceNo AND s.BranchNo=fc.BranchNo AND s.txntype=fc.txntype join `1employees` e on fc.ApprovedByNo=e.IDNo where s.TxnID='.$txnid;
   $stmtfc=$link->query($sqlfc); $resultfc=$stmtfc->fetch();
   if ($stmtfc->rowCount()>0){
    $total=$total.'<div style="border: 1px solid; width:800px">'.str_repeat('&nbsp',2).'Freight Expense: '.$resultfc['Amount'].str_repeat('&nbsp',5).'Particulars: '.$resultfc['Particulars'].str_repeat('&nbsp',3).$resultfc['FreightInc?'].str_repeat('&nbsp',5).'Approved By: '.$resultfc['ApprovedBy'].str_repeat('&nbsp',5).'Approval No: '.$resultfc['Approval'].'</div><br>';
    $totalinc='';
	
	// SUM(if('.$_SESSION['bnum'].' in (Select BranchNo from 1branches b where b.ProvincialBranch=0), PriceLevel3, PriceLevel4)*Qty)
    // check if freight expense is covered
    if ($resultfc['PriceFreightInclusive']==1){
        $sqlmin='Select 
		
		
		
		SUM((SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo='.$_SESSION['bnum'].'
				)*Qty)

			as MinPriceTotal FROM `invty_5latestminprice` lmp JOIN `invty_2salesub` s ON s.ItemCode=lmp.ItemCode WHERE s.TxnID='.$txnid;
        $stmtmin=$link->query($sqlmin); $resultmin=$stmtmin->fetch();
        $freightandmin=($resultfc['Amount']+(is_null($resultmin['MinPriceTotal'])?0:$resultmin['MinPriceTotal']));
        $sqlactual='Select SUM(UnitPrice*Qty) as SellPriceTotal FROM `invty_2salesub` s WHERE s.TxnID='.$txnid;
        $stmtactual=$link->query($sqlactual); $resultactual=$stmtactual->fetch(); //ECHO $resultactual['SellPriceTotal'].'  '.$freightandmin;
        $totalinc=($resultactual['SellPriceTotal']<$freightandmin)?'<p><b><font color=maroon>SELLING PRICE DOES NOT COVER FREIGHT EXPENSE. (Short by '.number_format((($freightandmin-$resultactual['SellPriceTotal'])),2,",",".").'.)</font></b><BR><BR></p>':'';
    }
    $total=$total.$totalinc;
   }
?>