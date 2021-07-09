<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$allowed=array(999,597,5971,5972,6001,6002,593,595,5951);
if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit; } 
$showbranches=true; include_once('../switchboard/contents.php');
 
include_once('../backendphp/functions/editok.php');
include_once('../backendphp/layout/showencodedbybutton.php');

$method='POST';

$txnid=intval($_REQUEST['TxnID']);
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;


$user=$_SESSION['(ak0)'];
 //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="e0eccf";
        $rcolor[1]="FFFFFF";
        
$whichqry=$_GET['w'];
switch ($whichqry){
CASE 'Sale':
    if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=597;}
if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit; }
$title='Add/Edit Sales';
$showbranches=true;
    $sqlmain='SELECT m.*, b.Branch as Branch, CONCAT(e.Nickname, " ",e.SurName) as EncodedBy, e1.Nickname AS TeamLead FROM acctg_2salemain m
join `1branches` as b on b.BranchNo=m.BranchNo
left join `1employees` as e on e.IDNo=m.EncodedByNo
left join `1employees` as e1 on e1.IDNo=m.TeamLeader
WHERE m.TxnID='.$txnid;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();

    $columnnamesmain=array('Date','Branch','Remarks','TeamLead','Posted');
    $columnsub=array('Particulars','ClientName','DebitAccount','CreditAccount','Amount');
    if ($showenc==1) { array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo'); array_push($columnsub,'EncodedBy','TimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}    
    
    $main='';
    if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5971;}
    $editok=!allowedToOpen($allowed,'1rtc')?false:editOk('acctg_2salemain',$txnid,$link,$whichqry);
    if ($editok){
        $editmain='<td><a href="editspecificsclient.php?edit=2&w=SaleMainEdit&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=acctg_2salemain&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
    } else {
        $editmain='';
        $editsub=false;
    }
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'</tr></table>';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Particulars');
    $sqlsub='Select s.*, cl.ClientName, ca.ShortAcctID as DebitAccount, ca1.ShortAcctID as CreditAccount, CONCAT(e.Nickname, " ",e.SurName) as EncodedBy from acctg_2salesub s left join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID left
join acctg_1chartofaccounts ca1 on ca1.AccountID=s.CreditAccountID '
            . 'left join `1employees` as e on s.EncodedByNo=e.IDNo
    join `acctg_0uniclientsalesperson` cl on cl.ClientNo=s.ClientNo 
    join acctg_2salemain m on m.TxnID=s.TxnID and cl.BranchNo=m.BranchNo
    WHERE m.TxnID='.$txnid.' Order By '.$sortfield;
    
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    $rowcount=$stmt->rowCount();
    
    $sub='';$subcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub=$sub.($editsub?'<td><a href="editspecificsclient.php?edit=2&w=SaleSubEdit&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>'
                .str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&TxnSubId='.$row['TxnSubId'].'&w=acctg_2salesub&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
          
        $colorcount++;
    }
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sql0='CREATE TEMPORARY TABLE totals AS SELECT DebitAccountID AS AccountID,SUM(Amount) AS Subtotal '
            . 'FROM `acctg_2salesub` s WHERE s.TxnID='.$txnid.' GROUP BY DebitAccountID'
            . ' UNION ALL SELECT CreditAccountID,SUM(Amount)*-1 AS Subtotal FROM `acctg_2salesub` s WHERE s.TxnID='.$txnid.' GROUP BY CreditAccountID';
    $stmt0=$link->prepare($sql0);    $stmt0->execute();
    $sqlsum='SELECT ShortAcctID, SUM(Subtotal)*NormBal AS Total FROM  totals t JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=t.AccountID GROUP BY t.AccountID';
   
    $stmt=$link->query($sqlsum);  $result=$stmt->fetchAll();
    $total='<br><br>Totals:<br>  ';
    foreach($result as $totalper){
        $total=$total.$totalper['ShortAcctID'].str_repeat('&nbsp',5).number_format($totalper['Total'],2).'<br>';
    }
    $total=$total.'<br><br><a href="addmain.php?w='. $whichqry.'">Add '. $whichqry.'</a>';
    
    $columnnames=array(
                    array('field'=>'Particulars', 'type'=>'text','size'=>20,'required'=>true,'autofocus'=>true),
                    array('field'=>'ClientName', 'caption'=>'Client', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'clientsemployees'),
                    array('field'=>'DebitAccount', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'accounts'),
                    array('field'=>'CreditAccount', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'accounts'),
                    array('field'=>'Amount', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='praddsub.php?w=SaleSubAdd&TxnID='.$txnid;
    $liststoshow=array('clientsemployees');
    $listcondition=' WHERE AccountID IN (SELECT AccountID FROM `acctg_1begbal` WHERE BranchNo='.$_SESSION['bnum'].') ';
    $whichotherlist='acctg';
    $otherlist=array('accounts');
    // info for posting:
    $postvalue='1';
    $table='acctg_2salemain';
    
        break;

        
CASE 'Collect':
if (!allowedToOpen(6002,'1rtc')) {   echo 'No permission'; exit;}

$showbranches=true; $caviteencoder=true;

if (allowedToOpen(6001,'1rtc')){   $sqlcondition='';} else { $sqlcondition=' and m.Date>\''.$_SESSION['nb4'].'\'';}
    $sqlmain='SELECT m.*, concat(StreetAddress, \' \', Barangay,\' \', TownOrCity, \', \', Province) as Address, c.TIN, CollectNo AS CRNo, CollectNo AS CollectNo, CheckNo AS DDApprovalNo, CheckNo AS CanvassID, IF(VatTypeNo=3,CONCAT(c.ClientName," - Sale to govt"),c.ClientName) AS ClientName, ca.ShortAcctID as DebitAccount, b.Branch as BranchSeries, CONCAT(e.Nickname, " ",e.SurName) as EncodedBy, e2.Nickname as ReceivedBy,  if(m.DebitAccountID=201,(Select DepositNo from acctg_2depositmain dm join acctg_2depositsub ds on dm.TxnID=ds.TxnID where ds.CheckNo=m.CheckNo and ds.CRNo=CONCAT("C-",m.BranchSeriesNo,"-",m.CollectNo) and ds.CheckDraweeBank=m.CheckBank and ds.CreditAccountID=201 group by dm.DepositNo),(Select DepositNo from acctg_2depositmain dm join acctg_2depositsub ds on dm.TxnID=ds.TxnID where ds.CRNo=CONCAT("C-",m.BranchSeriesNo,"-",m.CollectNo) and ds.CreditAccountID=100  and ds.BranchNo=s.BranchNo group by dm.DepositNo)) as DepositNo, VatTypeNo, CollectTypeID,DebitAccountID, CONCAT(ct.`CollectTypeID`," - ",ct.`CollectTypeDesc`) AS CollectType  FROM acctg_2collectmain m
join `1branches` as b on b.BranchNo=m.BranchSeriesNo LEFT JOIN acctg_2collectsub s ON m.TxnID=s.TxnID
join acctg_1chartofaccounts ca on ca.AccountID=m.DebitAccountID
join `1clients` as c on c.ClientNo=m.ClientNo JOIN `acctg_1collecttype` ct ON ct.`CollectTypeID`=m.Type
left join `1employees` as e on e.IDNo=m.EncodedByNo
left join `1employees` as e2 on e2.IDNo=m.ReceivedBy
WHERE m.TxnID='.$txnid.$sqlcondition;
//echo $sqlmain;break;
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $type=$result['Type'];
    $clientno=$result['ClientNo']; $vattypeno=$result['VatTypeNo']; $collectionreceiptafterPR=0;
    if(in_array($result['CollectTypeID'],array(4,6)) and empty($result['CheckNo']) and ($result['DebitAccountID']<>100)){ 
        $formdesc='<font color=red size=6>Please encode check details.</font>';}
    if (editOk('acctg_2collectmain',$txnid,$link,$whichqry)){
        $editmain='<td><a href="editspecificsclient.php?edit=2&w=CollectMainEdit&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=acctg_2collectmain&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a>';
        $editmain=($type==3?$editmain.str_repeat('&nbsp',12).'<a href="praddmain.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=DirectDeposit">Record direct deposit</a></td>':$editmain.'</td>');
        $editsub=true;
    } else {
        $editmain='';
        $editsub=false;
    }
    
    
    if($collectionreceiptafterPR<>1 or $vattypeno==3){
        $title='Add/Edit Collection Receipts';
    $columnnamesmain=array('CollectNo','Date','ClientName','CollectType','CheckBank',($type==3?'DDApprovalNo':($type==5?'CanvassID':'CheckNo')),'CheckBRSTN', 'ClientCheckBankAccountNo','DateofCheck','DebitAccount','ReceivedBy','BranchSeries','Remarks','Posted','PostedByNo','DepositNo');
	// if (allowedToOpen(7142,'1rtc')) {
	array_push($columnnamesmain,'Address','TIN');		
	// }
    $columnsub=array('Branch','ForChargeInvNo','OtherORDetails','CreditAccount','Amount');
    $columnsubencash=array('Branch','DeductDetails','DebitAccount','Amount');
    } else {
        $title='Add/Edit Collection Receipts';
        $columnnamesmain=array('CollectNo','Date','ClientName','Type','CheckBank',($type==3?'DDApprovalNo':($type==5?'CanvassID':'CheckNo')),'CheckBRSTN', 'ClientCheckBankAccountNo','DateofCheck','DebitAccount','BranchSeries','Remarks','Posted','PostedByNo');
    $columnsub=array('Branch','CRNo','DepositNo','CreditAccount','Amount');
    $columnsubencash=array(); 
    }
    
    if ($showenc==1) {
      array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo');
      array_push($columnsub,'EncodedBy','TimeStamp');
      array_push($columnsubencash,'EncodedBy','TimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub; $columnsubencash=$columnsubencash;}
    
    $main='';
    
    
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'</tr></table>';
    
    $sqlsub='Select s.*, s.ForChargeInvNo AS CRNo, OtherORDetails AS DepositNo, b.Branch, ca.ShortAcctID as CreditAccount, CONCAT(e.Nickname, " ",e.SurName) as EncodedBy from acctg_2collectsub s join acctg_1chartofaccounts ca on ca.AccountID=s.CreditAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo
    join `1branches` b on b.BranchNo=s.BranchNo 
    join acctg_2collectmain m on m.TxnID=s.TxnID WHERE m.TxnID='.$txnid.' Order By ForChargeInvNo';
    
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    $rowcount=$stmt->rowCount();
    
    $sub='';$subcol='';
	//added new list
     $stmtc=$link->query($sqlmain);$resultc=$stmtc->fetch();
    // echo $resultc['CollectTypeID']; exit();
    
    if(($collectionreceiptafterPR<>1 or $vattypeno==3) and ($type<>5)){
    // inserted field for OR
    $sub='<form method="post" action="praddsub.php?w=CollectAddOther&TxnID='.$txnid.'">Add:&nbsp Type&nbsp <input type="text" name="AddType" size=10 list="addcharges" value="Freight">&nbsp Amount <input type="text" name="Amount" size=10 required=true> &nbsp for invoice no. <input type="text" name="inv" size=10 required=true><input type="submit" name="submit" value="Enter"><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'"></form>
        <datalist id="addcharges">
        <option value="Freight"></option>
        <option value="Delivery"></option>
        <option value="Bank Charge"></option>
        <option value="Others"></option>
    </datalist>
    
    <datalist id="deductions">
	    '.(($resultc['CollectTypeID']==4)?'<option value="CredWTax2306"></option><option value="CredWTax2307"></option>':'<option value="CredWTax2307"></option>').'
        <option value="Others"></option>
    </datalist>
    <br><br>';
    //end of inserted field
    } 
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
         
        $sub=$sub.($editsub?'<td>'.(($collectionreceiptafterPR<>1 or $vattypeno==3)?'<a href="editspecificsclient.php?edit=2&clientno='.$clientno.'&w=CollectSubEdit&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>'.str_repeat('&nbsp',8):'').'<a href=..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&TxnSubId='.$row['TxnSubId'].'&w=acctg_2collectsub&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
        $colorcount++;
    }
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select sum(Amount) as Total from  `acctg_2collectsub` s join `acctg_2collectmain` m on m.TxnID=s.TxnID
Where m.TxnID='.$txnid;
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();$totalamt=$result['Total'];
    if(($collectionreceiptafterPR<>1 or $vattypeno==3) and ($type<>5)){
    $total='Subtotal:  '.number_format($totalamt,2).'<br><br><br>'.(($resultc['CollectTypeID']==4)?'<b>Collections from the government must have both 2306 and 2307.</b></br></br>':'').'<form method="post" action="praddsub.php?w=CollectDeduct&TxnID='.$txnid.'">Deduct:&nbsp Type&nbsp <input type="text" name="DeductType" size=15 list="deductions">&nbsp Amount <input type="text" name="Amount" size=5 required=true> &nbsp for invoice no. <input type="text" name="inv" size=5><input type="submit" name="submit" value="Enter"><input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'"></form>';
    
    //START DEDUCT
 $sqlsub='Select s.*, b.Branch, ca.ShortAcctID as DebitAccount, CONCAT(e.Nickname, " ",e.SurName) as EncodedBy from acctg_2collectsubdeduct s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo
    join `1branches` b on b.BranchNo=s.BranchNo
    join acctg_2collectmain m on m.TxnID=s.TxnID WHERE m.TxnID='.$txnid;
    
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    if ($stmt->rowCount()>0){
    $subencash='';
    $subcol='';
    foreach ($columnsubencash as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $subencash=$subencash.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsubencash as $colsub){
            $subencash=$subencash.'<td>'.$row[$colsub].'</td>';
        }
        
        $subencash=$subencash.($editsub?'<td><a href="editspecificsclient.php?edit=2&w=CollectDeductSubEdit&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&TxnSubId='.$row['TxnSubId'].'&m=acctg_2collectmain&w=acctg_2collectsubdeduct&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
        $colorcount++;
		if($row['DebitAccountID']==160 or $row['DebitAccountID']==161 ){
			$message='<b>Attach the appropriate forms to the collection receipt.</b>';	
		}
		else{
			$message='';
		}
    }
    $subencash='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$subencash.'</tbody></table>';
    $sqlsum='Select sum(Amount) as Deducted from  `acctg_2collectsubdeduct` sd join `acctg_2collectmain` m on m.TxnID=sd.TxnID Where m.TxnID='.$txnid;
   
    $stmt=$link->query($sqlsum);
    $resultencash=$stmt->fetch();
    $totalencashamt=$resultencash['Deducted'];
    $totalencash=''.$message.'</br></br>Deducted:  '.number_format($resultencash['Deducted'],2).str_repeat('&nbsp',10);   
    } 
   // END DEDUCT
    }
   $netdep=$totalamt-(isset($totalencashamt)?$totalencashamt:0);
    $grandtotal='<br><br>Net Amount:  '.number_format($netdep,2).str_repeat('&nbsp',10).'<a href="addmain.php?w=Collect">Add Collection Receipt</a>';
    
    
    
    if($type==5){ // for downpayment
        $sqlunpd='SELECT CanvassID, 5 AS Type,CONCAT(cat.Category, " - ",Description) AS Particulars, `CanvassDate`, QuotedPrice as QuotedPriceValue, FORMAT(QuotedPrice,2) AS QuotedPrice, Branch, c.BranchNo AS BranchNo FROM `quotations_2canvass` c JOIN `1branches` b ON b.BranchNo=c.BranchNo JOIN `invty_1category`cat ON cat.CatNo=c.Category
WHERE c.Downpayment=0 AND c.BranchNo='.$_SESSION['bnum'].' AND c.`ForClientName`='.$clientno.' ORDER BY `CanvassDate` DESC, CanvassID;'; 
    $columnsub=array('CanvassID','CanvassDate','Particulars','Branch','QuotedPrice'); 
    } else {
    $sqlunpd='SELECT Particulars, `Date`, InvBalance as InvBalanceValue, format(InvBalance,2) as InvBalance, Branch, c.BranchNo, 2 AS Type from acctg_unpaidinv c join `1branches` as b on b.BranchNo=c.BranchNo where c.ClientNo='.$clientno.' UNION
SELECT SaleNo AS Particulars,sm.Date,(SUM(Qty*UnitPrice)+IFNULL(op.Amount,0)) AS InvBalanceValue,FORMAT(SUM(Qty*UnitPrice)+IFNULL(op.Amount,0),2) AS InvBalance, Branch, sm.BranchNo, sm.PaymentType
FROM `invty_2sale` sm JOIN `invty_2salesub` ss ON sm.TxnID=ss.TxnID LEFT JOIN `invty_7opapproval` op ON op.TxnID=sm.TxnID
JOIN `1branches` b ON b.BranchNo=sm.BranchNo WHERE ClientNo='.$clientno.' AND sm.PaymentType=2 AND sm.Date=CURDATE() GROUP BY sm.TxnID

order by `Date`, Particulars';
    $columnsub=array('Particulars','Date','Branch','InvBalance'); 
    }
    
$stmt=$link->query($sqlunpd);
    $result=$stmt->fetchAll();
    
    if ($stmt->rowCount()>0){
    
    $lookupdata='';$subcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    } 
    foreach($result as $row){
        $lookupdata=$lookupdata.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $lookupdata=$lookupdata.'<td>'.$row[$colsub].'</td>';
        }
        

        if($type==5) { //downpayment 
            $lookupdata.=($editsub?'<td><a href="praddsub.php?w=CollectSubCanvass&action_token='.$_SESSION['action_token'].'&CanvassID='.$row['CanvassID'].'&ClientNo='.$clientno.'&BranchNo='.$row['BranchNo'].'&TxnID='.$txnid.'">Add</a></td>':'').'</tr>';
        } else {
        $lookupdata=$lookupdata.($editsub?'<td><a href="praddsub.php?w=CollectSubAutoAdd&action_token='.$_SESSION['action_token'].'&Type='.$row['Type'].'&Inv='.$row['Particulars'].'&ClientNo='.$clientno.'&BranchNo='.$row['BranchNo'].'&TxnID='.$txnid.'">Add</a></td>':'').'</tr>'; }
        $colorcount++;
    }
    
    $lookupdata='<br><br>Unpaid Invoices<br><table><tr>'.$subcol.'<td>Paid?</td></tr><tbody>'.$lookupdata.'</tbody></table>';
    } else {$lookupdata='';}
    // end of unpaid inv list
    
    
    
    nounpaid:
    
    $columnnames=array();
        
    $action='praddsub.php?w=CollectSubAdd&ClientNo='.$clientno.'&TxnID='.$txnid;
    $liststoshow=array();
    $listcondition=$clientno;
    $whichotherlist='acctg';
    $otherlist=array('unpaidinvoices');
    // info for posting:
    $postvalue='1';
    $table='acctg_2collectmain'; $txntype=30;
    
    include('acctglayout/inputsubformwithinvlist.php');
    goto noform;
        break;


CASE 'BouncedfromCR': 
if (!allowedToOpen(593,'1rtc')) {   echo 'No permission'; exit;}
if(isset($_GET['fromlast'])){
	$whichqry='BouncedfromCRLast'; $fromlast='&fromlast=1';
    $txnidname='UndepPDCId';
	$tbname='acctg_3undepositedpdcfromlastperiodbounced';
	$sql='SELECT sb.*,PDCNo AS CheckNo, PDCBank AS CheckBank, c.ClientName, AmountOfPDC AS Amount, e.Nickname AS EncodedBy, ca.ShortAcctID AS BankWhereBounced FROM acctg_3undepositedpdcfromlastperiod m JOIN acctg_3undepositedpdcfromlastperiodbounced sb ON m.UndepPDCId=sb.UndepPDCId JOIN `1clients` c ON c.ClientNo=m.ClientNo JOIN `1employees` e ON e.IDNo=sb.EncodedByNo JOIN acctg_1chartofaccounts ca ON ca.AccountID=sb.CreditAccountID WHERE m.UndepPDCId='.$txnid;
	$upimg='FromLast';
} else {
	$whichqry='BouncedfromCR';
    $txnidname='TxnID';
// The sql for main may remain to be the basis for unpaid invoices	
	$tbname='acctg_2collectsubbounced';
	$sql='SELECT sb.*, m.CheckNo, m.CheckBank, c.ClientName, FORMAT(SUM(s.Amount)-(SELECT IFNULL(SUM(Amount),0) FROM `acctg_2collectsubdeduct` csd WHERE csd.TxnID=m.TxnID),2)  AS Amount, e.Nickname AS EncodedBy, ca.ShortAcctID AS BankWhereBounced FROM acctg_2collectmain m JOIN acctg_2collectsub s ON m.TxnID=s.TxnID JOIN acctg_2collectsubbounced sb ON m.TxnID=sb.TxnID
JOIN `1clients` c ON c.ClientNo=m.ClientNo
JOIN `1employees` e ON e.IDNo=sb.EncodedByNo
JOIN acctg_1chartofaccounts ca ON ca.AccountID=sb.CreditAccountID WHERE m.TxnID='.$txnid;
$upimg=''; $fromlast='';
}

$title='Add/Edit Bounced Checks from Collection Receipts'; 
$directory='addeditclientside.php?w=BouncedfromCR'.$fromlast.'&TxnID='.$txnid.'';
$formdesc='</i>
<div style="background-color:#f2f2f2; padding:5px; width:550px;"></i><h3>Note:</h3></br>
If the same bounced check will be redeposited (or a new check or cash will be used to replace the bounced check), deduction entries for <i>creditable withholding tax</i> must be MANUALLY ENCODED in the same deposit.  Basis is the original collection receipt used.
</div>
</br><form action="uploadreceipt.php" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="directory" value='.$directory.' size=4"> 
				 <input type="hidden" name="UploadID" value='.$txnid.$upimg.' size=4"> 
                <input type="file" name="userfile" accept="image/jpg">   
				<input type="submit" name="btnsubmit" value="Submit"> 
                 </form><i> </BR><img src="../../acrossyrs/unpaidarinv/'.$_GET['TxnID'].$upimg.'.jpg" width="100px" height="70px"/>';


if (editOk($tbname,$txnid,$link,$whichqry) and allowedToOpen(593,'1rtc')){
        $editmain='<td><a href="editspecificsclient.php?edit=2&w='.$whichqry.'&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w='.$tbname.'&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
    } else {
        $editmain=''; $editsub=false;
    }
    
    $stmt=$link->query($sql);    $result=$stmt->fetch();
    $columnnamesmain=array('DateBounced','ClientName','CheckNo','CheckBank','Remarks','BankWhereBounced','Amount','DateofFirstInv');  
    if ($showenc==1) { array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo'); } else {$columnnamesmain=$columnnamesmain; }
    $main='';
    foreach ($columnnamesmain as $rowmain){
        $main.='<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td></tr><tr>';
    }
    
    $main='<table><tr>'.$main.$editmain.'</tr></table>'; 
//added	sub
	if(!isset($_GET['fromlast'])){
		$sqls='
		SELECT s.ForChargeInvNo as ForChargeInv,s.CreditAccountID AS DebitAccountID, sb.CreditAccountID, Amount  FROM acctg_2collectmain m JOIN acctg_2collectsub s ON m.TxnID=s.TxnID JOIN acctg_2collectsubbounced sb ON m.TxnID=sb.TxnID WHERE m.TxnID='.$txnid.'
		UNION ALL
		SELECT DeductDetails as ForChargeInv,s.DebitAccountID, sb.CreditAccountID, (Amount)*-1  FROM acctg_2collectmain m JOIN acctg_2collectsubdeduct s ON m.TxnID=s.TxnID JOIN acctg_2collectsubbounced sb ON m.TxnID=sb.TxnID WHERE m.TxnID='.$txnid.' ';
		$stmts=$link->query($sqls);    $results=$stmts->fetchAll();
		$sub='<table><tr><td>ForChargeInv</td><td>DebitAccountID</td><td>CreditAccountID</td><td>Amount</td></tr>';
		foreach($results as $ress){
			$sub.='<tr><td>'.$ress['ForChargeInv'].'</td><td>'.$ress['DebitAccountID'].'</td><td>'.$ress['CreditAccountID'].'</td><td>'.$ress['Amount'].'</td></tr>';
		}
		$sub.='</table>';
		// echo $sqls;
		
	}else{
		$sub='';
	}
//	
    $columnnames=array(); $liststoshow=array();
    // info for posting:
    $postvalue='1'; $datefield='DateBounced';
    $table=$tbname; 
break;
        
CASE 'Interbranch':
CASE 'Txfr':
if (!allowedToOpen(595,'1rtc')) {   echo 'No permission'; exit;}
$title='Add/Edit Transfers';
$formdesc='<table style="width: 95%"><tr>
<td style="width: 25%;">
   Actual entries for every entry here:<br><br>
   <table><tr><td colspan=3>Interbranch transfers:</td></tr>
    <tr><td>Branch</td><td>Debit</td><td>Credit</td></tr>
    <tr><td>FROM Branch</td><td>ARTradeTxfr</td><td>From WH/Branch: Inventory</td></tr>
    <tr><td>TO Branch</td><td>Inventory  <i>OR</i><br>InTransit (if no DateIn)</td><td>APTradeTxfr</td></tr>
    <tr><td colspan=3>If different months for DateOut & DateIn:</td></tr>
    <tr><td>TO Branch</td><td>Inventory</td><td>InTransit</td></tr>
    </table>
   </td>
<td style="width: 25%; ">
   <table>
    <tr><td colspan=3>If paid:</td></tr><tr><td>Branch</td><td>Debit</td><td>Credit</td></tr>
    <tr><td>FROM Branch</td><td>BDOInfinity</td><td>ARTradeTxfr</td></tr>
    <tr><td>TO Branch</td><td>APTradeTxfr</td><td>BDOInfinity</td></tr></table>   
<td style="width: 25%; ">
    <table><tr><td colspan=3>If from warehouses:<br>Return profits from minprice to branch; remove from inventory value of branch:</td></tr><tr><td></td><td>Debit</td><td>Credit</td></tr>
    <tr><td>FROM Warehouse</td><td>Inventory</td><td>DueTo (if diff company)/Bank</td></tr>
    <tr><td>TO Branch</td><td>DueFrom (if diff company)/Bank</td><td>Inventory</td></tr>
    <tr><td colspan=3>If different months for DateOut & DateIn:</td></tr>
    <tr><td>TO Branch on DateOUT</td><td>DueFrom (if diff company)/Bank</td><td>InTransit</td></tr>
    <tr><td>TO Branch on DateIN</td><td>InTransit</td><td>Inventory</td></tr>
</table>    
</td>
</tr>
</table>';
$showbranches=true;
    $sqlmain='SELECT m.*, b.Branch as FromBranch, ca.ShortAcctID as CreditAccount, CONCAT(e.Nickname, " ",e.SurName) as EncodedBy FROM acctg_2txfrmain m
join `1branches` as b on b.BranchNo=m.FromBranchNo
join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID
left join `1employees` as e on e.IDNo=m.EncodedByNo
WHERE m.TxnID='.$txnid;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();

    $columnnamesmain=array('Date','FromBranch','CreditAccount','Remarks','Posted');
    $columnsub=array('ToBranch','Particulars','DebitAccount','DateIN','Amount','Remarks','DatePaid','PaidVia');

 if ($showenc==1) {
      array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo');
      array_push($columnsub,'OUTEncodedBy','OUTTimeStamp','INEncodedBy','INTimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub; }
    
    $main='';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Particulars');    
    
    if (editOk('acctg_2txfrmain',$txnid,$link,$whichqry) and allowedToOpen(5952,'1rtc')){
        $editmain='<td><a href="editspecificsclient.php?edit=2&w=TxfrMainEdit&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=acctg_2txfrmain&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
    } else {
        $editmain='';
        $editsub=false;
    }
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%8==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'</tr></table>';
    
    $sqlsub='Select s.*, if(isnull(DatePaid),"",PaidViaAcctID) as PaidVia, b.Branch as ToBranch, ca.ShortAcctID as DebitAccount, e.Nickname as OUTEncodedBy, e2.Nickname as INEncodedBy  from acctg_2txfrsub s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID left join `1employees` as e on s.OUTEncodedByNo=e.IDNo
    left join `1employees` as e2 on s.INEncodedByNo=e2.IDNo
    join `1branches` b on b.BranchNo=s.ClientBranchNo
    join acctg_2txfrmain m on m.TxnID=s.TxnID WHERE m.TxnID='.$txnid.' Order By '.$sortfield;
    
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    $rowcount=$stmt->rowCount();
    $sub='';$subcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub=$sub.($editsub?'<td><a href="editspecificsclient.php?edit=2&w=TxfrSubEdit&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&TxnSubId='.$row['TxnSubId'].'&w=acctg_2txfrsub&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
        $colorcount++;
    }
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select sum(Amount) as Total from  `acctg_2txfrsub` s join `acctg_2txfrmain` m on m.TxnID=s.TxnID
Where m.TxnID='.$txnid;
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10).'<a href="addmain.php?w='. $whichqry.'">Add '. $whichqry.'</a>';
    
    $columnnames=array(
                    array('field'=>'Particulars', 'type'=>'text','size'=>20,'required'=>true,'autofocus'=>true),
                    array('field'=>'ClientBranchNo', 'type'=>'text','size'=>20,'required'=>true),
                    array('field'=>'DebitAccountID', 'type'=>'hidden','size'=>0,'value'=>204),
                    array('field'=>'Amount', 'type'=>'text','size'=>10,'required'=>true, 'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='praddsub.php?w=TxfrSubAdd&TxnID='.$txnid;
    $liststoshow=array();
    // info for posting:
    $postvalue='1';
    $table='acctg_2txfrmain';
    
    //Removed. not used acctg_2txfrsubadj table
    // if (allowedToOpen(5951,'1rtc')){
    //   $subtitle='Adjustments: <br>1. Return profit  from minprices to Branch.<br>2. Cost of transfer is deducted from warehouse\'s inventory.<br>Net effect is a simple transfer of inventory from warehouse to branch.';
    //   $sqlsub2='Select s.*, b.Branch, ca.ShortAcctID as DebitAccount, ca1.ShortAcctID as CreditAccount, CONCAT(e.Nickname, " ",e.SurName) as EncodedBy from acctg_2txfrsubadj s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID join acctg_1chartofaccounts ca1 on ca1.AccountID=s.CreditAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo
    // join acctg_2txfrmain m on m.TxnID=s.TxnID join `1branches` as b on b.BranchNo=s.BranchNo WHERE m.TxnID='.$txnid;
    // $sqlsum='Select ca.ShortAcctID as DebitAccount, format(Sum(Amount),2) as Total from acctg_2txfrsubadj s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID 
    // join acctg_2txfrmain m on m.TxnID=s.TxnID join `1branches` as b on b.BranchNo=s.BranchNo WHERE m.TxnID='.$txnid.' GROUP BY DebitAccountID';
    // $stmt=$link->query($sqlsum); $result=$stmt->fetchAll();

    // $totalstext='<br><br>Totals Debits: &nbsp &nbsp';
    // foreach ($result as $res){
    //   $totalstext=$totalstext.$res['DebitAccount'].' '.$res['Total'].' &nbsp &nbsp';
    // }
    
    // $sqlsum='Select ca.ShortAcctID as CreditAccount, format(Sum(Amount),2) as Total from acctg_2txfrsubadj s join acctg_1chartofaccounts ca on ca.AccountID=s.CreditAccountID 
    // join acctg_2txfrmain m on m.TxnID=s.TxnID join `1branches` as b on b.BranchNo=s.BranchNo WHERE m.TxnID='.$txnid.' GROUP BY s.CreditAccountID';
    // $stmt=$link->query($sqlsum); $result=$stmt->fetchAll();
    
    // $totalstext.='<br><br>Totals Credits: &nbsp &nbsp';
    // foreach ($result as $res){
    //   $totalstext=$totalstext.$res['CreditAccount'].' '.$res['Total'].' &nbsp &nbsp';
    // }
    
    // if (allowedToOpen(5951,'1rtc')){  $columnnames2=array('Branch','DebitAccount','CreditAccount','Amount');   }
    //   else {$columnnames2=array('Branch','DebitAccount','CreditAccount');}
    // }
    // if ($showenc==1) { array_push($columnnames2,'EncodedBy','TimeStamp');} else {$columnnames2=$columnnames2; }
    $left='40%'; $leftmargin='41%'; $right='59%'; 
        break;
 

  
}
 include('../backendphp/layout/inputsubform.php');
   
 noform:
         $link=null; $stmt=null;
?>