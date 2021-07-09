<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6411,'1rtc')) { echo 'No permission'; exit;}  
$showbranches=true; include_once('../switchboard/contents.php');
 
include_once('../backendphp/functions/editok.php');


$method='POST';


 //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="FEEDD5";
        $rcolor[1]="FFFFFF";

$whichqry=$_GET['w'];


if (in_array($whichqry,array('CashCount','AutoEncodeCashCount'))){
$txnid=$_REQUEST['CashCountID']; 
 $sqlmain='SELECT c.*, b.Branch, e.Nickname as AuditedBy FROM audit_2countcash c join `1branches` b on b.BranchNo=c.BranchNo
left join `1employees` e on e.IDNo=c.EncodedByNo
where  CashCountID='.$txnid;
    
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $datecounted=$result['DateCounted'];
    $branchno=$result['BranchNo'];
	
	
	$txndate='m.Date=\''.$datecounted.'\'';
		// $sqlc=$sql0.$txndate.$sql1.$txndate.$sql2.$txndate.$sql3;
		// 
		
		$sqlcommon=' m.TxnID,m.PaymentType,SaleNo,m.Remarks,IFNULL(Date,"0000-00-00") AS `Date`, m.BranchNo,CheckDetails,m.Posted,m.DateofCheck,m.PONo,m.txntype,`invty_0txntype`.txndesc as `Form`, pt.paytypedesc as PayType, e.Nickname as EncodedBy, ';
		$sql0='CREATE TEMPORARY TABLE 2salemaintoday 
		SELECT '.$sqlcommon.' ClientName, round(sum(s.UnitPrice*s.Qty),2)as Amount,
		round(ifnull(a.Amount,0)*0.12,0) as VATCollected,concat(date_format(Date,\'%Y %b %d\'),\' - \',txndesc) as DateForm, ifnull(a.Amount,"") AS Overprice FROM invty_2sale m join invty_2salesub as s on m.TxnID=s.TxnID 
		INNER JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1clients` as c on c.ClientNo=m.ClientNo
		join invty_0paytype pt on pt.paytypeid=m.PaymentType
		left join `1employees` as e on e.IDNo=m.EncodedByNo
		left join `invty_7opapproval` a on a.TxnID=m.TxnID
		WHERE (('.$txndate.') AND ((m.BranchNo)='.$branchno.')) Group by m.TxnID 
		union all
		SELECT '.$sqlcommon.' ClientName,0 as Amount, 0 as VATCollected, concat(date_format(Date,\'%Y %b %d\'),\' - \',txndesc) as DateForm, 0 AS Overprice FROM invty_2sale m left join invty_2salesub as s on m.TxnID=s.TxnID 
		INNER JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1clients` as c on c.ClientNo=m.ClientNo
		join invty_0paytype pt on pt.paytypeid=m.PaymentType
		left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE (('.$txndate.') AND ((m.BranchNo)='.$branchno.') and s.TxnSubId is null) Group by m.TxnID
		UNION ALL
		SELECT '.$sqlcommon.' CONCAT(c.FirstName," ",c.Surname) AS Employee, round(sum(s.UnitPrice*s.Qty),2)as Amount,
		0 as VATCollected,concat(date_format(Date,\'%Y %b %d\'),\' - \',txndesc) as DateForm, 0 AS Overprice FROM invty_2sale m join invty_2salesub as s on m.TxnID=s.TxnID
		LEFT JOIN invty_0txntype ON m.txntype = `invty_0txntype`.txntypeid join `1employees` as c on c.IDNo=m.ClientNo
		join invty_0paytype pt on pt.paytypeid=10
		left join `1employees` as e on e.IDNo=m.EncodedByNo
		WHERE (('.$txndate.') AND ((m.BranchNo)='.$branchno.')) Group by m.TxnID 
		;
		';
		// echo $sql0;
		$stmt=$link->prepare($sql0); $stmt->execute();
	
}

switch ($whichqry){
    case 'CashCount':
     
      $title='Add/Edit Cash Count';
   /*  $sqlmain='SELECT c.*, b.Branch, e.Nickname as AuditedBy FROM audit_2countcash c join `1branches` b on b.BranchNo=c.BranchNo
left join `1employees` e on e.IDNo=c.EncodedByNo
where  CashCountID='.$txnid;
    
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $datecounted=$result['DateCounted']; */
	
    $main='';
    
    if (editOk('audit_2countcash',$txnid,$link,'countcash')){
        $editmain='<td><a href="editauditspecifics.php?edit=2&w=CashCountMainEdit&CashCountID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=prcashtools.php?CashCountID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=CashCountMainDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
        $columnnamesmain=array('DateCounted','Branch','Remarks','NoOfUsedReceipts');
        $bills=array('1000','500','200','100','50','20','10','5','1','025','010','005');
        $columnsub=array('InvandPRCollectNo','Amount');
    } else {
        $editmain='<td><a href="printaudit.php?w=Cash&CountID='.$txnid.'">Print Preview</a></td>';
        $editsub=false;
        $columnnamesmain=array('DateCounted','Branch','Remarks','NoOfUsedReceipts','AuditedBy','TimeStamp','Posted','PostedByNo');
        $bills=array('1000','500','200','100','50','20','10','5','1','025','010','005');
        $columnsub=array('InvandPRCollectNo','Amount','EncodedBy','TimeStamp');
    }
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $billtable=''; $billsamt=0;
    foreach ($bills as $bill){
        $billtable=$billtable.'<td><font face="arial" size="2">'.$bill.'</font></td><td>'.$result[$bill].'</td></tr>';
        $billsamt=$billsamt+(($bill==='025' OR $bill==='010' OR $bill==='005')?($bill*.01):$bill)*$result[$bill];
    }
    $main='<table><tr>'.$main.$editmain.'<tr></table><br><table style="display: inline-block; border: 1px solid; float: left; "><tr><td>Denomination</td><td>No. of Bills</td></tr>'.$billtable.'<tr><td>Total Amt of Cash</td><td>'.number_format($billsamt,2).'</td></tr></table>';
    
    
        $sqlsub='Select s.*,e1.Nickname as EncodedBy from audit_2countcashsub s left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join audit_2countcash m on m.CashCountID=s.CashCountID
where m.CashCountID='.$txnid.' Order By InvandPRCollectNo';    
    
     
	$stmt=$link->query($sqlsub);
	$result=$stmt->fetchAll();
	
    $sub='';$subcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub=$sub.($editsub?'<td><a href="editauditspecifics.php?edit=2&w=CashCountSubEdit&CashCountSubID='.$row['CashCountSubID'].'&CashCountID='.$row['CashCountID'].'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href=prcashtools.php?CashCountID='.$txnid.'&CashCountSubID='.$row['CashCountSubID'].'&action_token='.$_SESSION['action_token'].'&w=CashCountSubDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
        $colorcount++;
    }
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select sum(s.Amount) as TotalInv from  `audit_2countcashsub` s 
join `audit_2countcash` m on m.CashCountID=s.CashCountID
Where m.CashCountID='.$txnid.' Group By m.CashCountID';
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Total of Invoices and Collections:  '.number_format($result['TotalInv'],2).' &nbsp &nbsp &nbsp &nbsp <a  href="addcountmain.php?w='. $whichqry. '">Add New Cash Count</a><br>Difference:  '.number_format($billsamt-$result['TotalInv'],2);

$liststoshow=array('');

    $columnnames=array(
                    array('field'=>'InvandPRCollectNo', 'type'=>'text','size'=>20,'required'=>true, 'autofocus'=>true),
                    array('field'=>'Amount', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'CashCountID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='prcashtools.php?w=CountCashSubAdd&CashCountID='.$txnid;
    // info for posting:
    $postvalue='1';
    $table='audit_2countcash'; $txntype='countcash';
	
	
	
	
	
	
	
	
    
        break;
    
case 'Tools':
$txnid=$_REQUEST['CountID'];      
      $title='Add/Edit Tools Audit';
    $sqlmain='SELECT c.*, b.Branch, e.Nickname as AuditedBy FROM audit_2toolscountmain c join `1branches` b on b.BranchNo=c.BranchNo
left join `1employees` e on e.IDNo=c.AuditedByNo
where  CountID='.$txnid;
    
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    
    $main='';
    
    if (editOk('audit_2toolscountmain',$txnid,$link,'toolscount')){
        $editmain='<td><a href="editauditspecifics.php?edit=2&w=ToolsCountMainEdit&CountID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=prcashtools.php?CountID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=ToolsCountMainDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
        $columnnamesmain=array('Date','DateofLastCount','Branch','Remarks');
        $columnsub=array('ToolID','ToolDesc','Unit','Count','Remarks');
    } else {
        $editmain='<td><a href="printaudit.php?w=Tools&CountID='.$txnid.'">Print Preview</a></td>';
        $editsub=false;
        $columnnamesmain=array('Date','DateofLastCount','Branch','Remarks','AuditedBy','Posted','PostedByNo');
        $columnsub=array('ToolID','ToolDesc','Unit','Count','Remarks','EncodedBy','TimeStamp');
    }
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'<td><a href="editaudit.php?w=ToolsCount&CountID='.$txnid.'"></a></td><tr></table>';
    
    
        $sqlsub='Select s.*,e1.Nickname as EncodedBy, t.ToolDesc, t.Unit from audit_2toolscountsub s left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join audit_2toolscountmain m on m.CountID=s.CountID join audit_1tools t on t.ToolID=s.ToolID where m.CountID='.$txnid.' Order By t.ToolDesc';    
    
     
	$stmt=$link->query($sqlsub);
	$result=$stmt->fetchAll();
	
    $sub='';$subcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub=$sub.($editsub?'<td><a href="editauditspecifics.php?edit=2&w=ToolsCountSubEdit&CountSubID='.$row['CountSubID'].'&CountID='.$row['CountID'].'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href=prcashtools.php?CountID='.$txnid.'&CountSubID='.$row['CountSubID'].'&action_token='.$_SESSION['action_token'].'&w=ToolsCountSubDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
        $colorcount++;
    }
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select count(s.ToolID) as NumberofTools from  `audit_2toolscountsub` s 
join `audit_2toolscountmain` m on m.CountID=s.CountID
Where m.CountID='.$txnid.' Group By m.CountID';
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Number of Tools:  '. $result['NumberofTools'].' &nbsp &nbsp &nbsp &nbsp <a  href="addcountmain.php?w=Tools">Add New Tools Audit</a>';

$liststoshow=array('');

    $columnnames=array(
                    array('field'=>'ToolID', 'type'=>'text','size'=>20,'required'=>true, 'autofocus'=>true, 'list'=>'tools'),
                    array('field'=>'Count', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'Remarks', 'type'=>'text','size'=>10),
                    array('field'=>'CountID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
   $liststoshow=array('tools');
    $action='prcashtools.php?w=CountToolsSubAdd&CountID='.$txnid;
    // info for posting:
    $postvalue='1';
    $table='audit_2toolscountmain';
    
        break;
		
		case 'AutoEncodeCashCount':
		
		$txnid=$_REQUEST['CashCountID'];
		$pk='CashCountID';$table='audit_2countcash';$date='DateCounted';
		//to check if editable
		include('../backendphp/functions/checkeditablesub.php');
		
		// echo $sql0;
		
		// exit();
		
		$sql='INSERT INTO audit_2countcashsub (CashCountID,InvandPRCollectNo,Amount,EncodedByNo,TimeStamp) Select '.$txnid.', CONCAT(MIN(SaleNo*1),"-",MAX(SaleNo*1)) AS InvandPRCollectNo,SUM(Amount) AS Amount,'.$_SESSION['(ak0)'].',NOW() from 2salemaintoday WHERE PaymentType=1 ';
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		header("Location:editcash.php?w=CashCount&CashCountID=".$txnid);
		
		
		break;


}
    $left='90%'; $leftmargin='91%'; $right='9%';
     include('../backendphp/layout/inputsubform.php');
	 
	 
	 if($whichqry=='CashCount'){
		 
		
		 echo '<div style="clear: both; display: block; position: relative;"></div><br><br>';
		 $sql='Select TxnID,txntype,SaleNo AS InvandPRCollectNo,Amount from 2salemaintoday WHERE PaymentType=1 Order By Form, SaleNo*1';
		
		
			$columnnames=array('InvandPRCollectNo','Amount');

			$columnsub=$columnnames; 
			$editprocess='../invty/addeditsale.php?TxnID=';$addlfield='txntype';$editprocesslabel='Lookup'; $width='80%';
			$title='Encoded Sales';
			$formdesc='</i><form action="editcash.php?w=AutoEncodeCashCount&CashCountID='.$_GET['CashCountID'].'" method="POST"><input type="submit" value="Add to CashCount" name="btnAddCashCount"></form><i>';
			$showgrandtotal=true; $coltototal='Amount';
			include('../backendphp/layout/displayastablenosort.php');
			
			

	 }
       $link=null; $stmt=null;
?>