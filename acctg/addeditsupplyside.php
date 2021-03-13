<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(999,5962,598,540,592,596);
if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit; }
$showbranches=true; include_once('../switchboard/contents.php');
 
include_once('../backendphp/functions/editok.php');
include_once('../backendphp/layout/showencodedbybutton.php');


$method='POST';

$listcondition=' WHERE AccountID IN (SELECT AccountID FROM `acctg_1begbal` WHERE BranchNo='.$_SESSION['bnum'].') ';

//$user=$_SESSION['(ak0)'];
 //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="FFE3E3";
        $rcolor[1]="FFFFFF";
        
$whichqry=$_GET['w'];
if (in_array($whichqry,array('CV','JV','FutureVch','FutureCV','Purchase'))){
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT EntityID,Entity FROM `acctg_1budgetentities` ORDER BY Entity;','EntityID','Entity','entities');
echo comboBox($link,'SELECT BranchNo,Branch from `1branches` where Active=1 ORDER BY Branch','BranchNo','Branch','allbranches');
$branchlist='allbranches';
$entities='entities';
}
switch ($whichqry){
CASE 'Purchase':
$txnid=intval($_REQUEST['TxnID']);
if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5962;}
        if (!allowedToOpen(array($allowed,596),'1rtc')) { echo 'No permission'; exit;} 
$title='Add/Edit Purchase'; $showbranches=true;
    $sqlmain='SELECT m.*,c.Company as RCompany,  s.SupplierName, ca.ShortAcctID as CreditAccount, b.Branch as Branch, e.Nickname as EncodedBy,
    s1.SupplierName AS RegisteredSupplier FROM `acctg_2purchasemain` m 
join `1branches` as b on b.BranchNo=m.BranchNo
join `1suppliers` as s on s.SupplierNo=m.SupplierNo
join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID
left join `1employees` as e on e.IDNo=m.EncodedByNo 
left join `1companies` as c on c.CompanyNo=m.RCompany
LEFT join `1suppliers` s1 on s1.SupplierNo=m.RegisteredSupplierNo
WHERE m.TxnID='.$txnid;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();

    $columnnamesmain=array('Date','SupplierName','SupplierInv','DateofInv','MRRNo','Terms','CreditAccount','Branch','Remarks','RegisteredSupplier','RCompany','Posted');
    $columnsub=array('DebitAccount','Amount','FromBudgetOf');
    $main='';
    
    
    if (editOk('acctg_2purchasemain',$txnid,$link,$whichqry) and (allowedToOpen(5962,'1rtc')) ){
        $editmain='<td><a href="editspecificssupply.php?edit=2&w=PurchaseEdit&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=acctg_2purchasemain&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true; $columnstoedit=array('Particulars','DebitAccount','Amount','FromBudgetOf'); $editok=true;
    } else {
        $editmain=''; $editsub=false;$editok=false; $columnstoedit=array();
    }
    if ($showenc==1) { array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo'); array_push($columnsub,'EncodedBy','TimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'</tr></table>';
    
    $sqlsub='Select s.*, Entity AS FromBudgetOf, ca.ShortAcctID as DebitAccount, e.Nickname as EncodedBy from acctg_2purchasesub s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo
    join acctg_2purchasemain m on m.TxnID=s.TxnID LEFT JOIN `acctg_1budgetentities` be on be.EntityID=s.FromBudgetOf WHERE m.TxnID='.$txnid;
    
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();

    $sqlsum='Select sum(Amount) as Total from  `acctg_2purchasesub` s join `acctg_2purchasemain` m on m.TxnID=s.TxnID
Where m.TxnID='.$txnid;
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $addlinfo='Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10).'<a href="addmain.php?w='. $whichqry.'">Add '. $whichqry.'</a>';
      $editok=$editsub;//$columnnames=$columnsub;
    $columnnames=array(
                    array('field'=>'DebitAccount','type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                    array('field'=>'Amount','type'=>'text','size'=>15,'required'=>true),
                    array('field'=>'FromBudgetOf','type'=>'text','size'=>15,'value'=>$_SESSION['@brn'],'required'=>true,'list'=>'entities'));
    $action='praddsub.php?w=PurchaseSubAdd&TxnID='.$txnid;
    $editprocess='preditsupplyside.php?w=PurchaseSubEdit&TxnID='.$txnid.'&TxnSubId='; $editprocesslabel='Enter';
    $delprocess='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&w=acctg_2purchasesub&l=acctg'.'&TxnSubId=';
    $txnsubid='TxnSubId'; $showgrandtotal=true; $coltototal='Amount';
    $liststoshow=array(); $whichotherlist='acctg'; $otherlist=array('accounts'); 
    // info for posting:
    $post='1'; $table='acctg_2purchasemain'; $txntype='purchase'; 
    $withsub=true; $left='100%'; $leftmargin='101%'; $right='9%';
    include('../backendphp/layout/inputsubform.php');
    if (allowedToOpen(5962,'1rtc')) {
    ?><div style="margin-left:50%;">Transactions in this form are as follows:
    <table>
      <tr><th>Transaction</th><th>Debit</th><th>Credit</th></tr>
      <tr><td>Purchase from the Supplier</td><td>Inventory</td><td>APTrade</td></tr>
<!--      <tr><td>If there is a registered supplier <br><i>There should be a corresponding RCompany</i></td><td>Inventory-External</td><td>Inventory-Internal</td></tr>-->
    </table><br>
<!--    <i>If there is no actual purchase, but a supplier gives an invoice:</i><br>
      &nbsp &nbsp Credit Account in the main form must be Inventory-Internal.<br>
      &nbsp &nbsp Debit Account in the sub form must be Inventory-External.-->
    </div><?php
    }
    goto noform;
        break;

CASE 'CV':
    if (!allowedToOpen(598,'1rtc')) { echo 'No permission'; exit;}
    $txnid=intval($_REQUEST['CVNo']);	
$title='Add/Edit CV';
$showbranches=true;
    $sqlmain='SELECT m.*,PaymentMode, ca.ShortAcctID as CreditAccount, e.Nickname as EncodedBy FROM acctg_2cvmain m
join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID
JOIN acctg_0paymentmodes pm ON m.PaymentModeID=pm.PaymentModeID
left join `1employees` as e on e.IDNo=m.EncodedByNo
WHERE m.CVNo='.$txnid;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $suppno=$result['PayeeNo'];
    
    
    $columnnamesmain=array('Date','DueDate','CVNo','PaymentMode','CheckNo','DateofCheck','PayeeNo','Payee','ReleaseDate','Cleared','CreditAccount','Remarks','Posted');
    $columnsub=array('Particulars','Branch','FromBudgetOf','ForInvoiceNo','TIN','CompanyName','DebitAccount','Amount');
   
    $main=''; 
        
    if (editOk('acctg_2cvmain',$txnid,$link,$whichqry) and allowedToOpen(5401,'1rtc')){
        $editmain='<td><a href="editspecificssupply.php?edit=2&w=CVMainEdit&CVNo='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=acctg_2cvmain&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editok=true;$editsub=true; $columnstoedit=array('Particulars','Branch','FromBudgetOf','TIN','ForInvoiceNo','DebitAccount','Amount');//'RCompany',
		
    } else {
        $editmain='<td><a href="printvoucher.php?w='.$whichqry.'&FromVch='.$result['CVNo'].'&ToVch='.$result['CVNo'].'">Print CV</a></td><td><a href="printvoucher.php?w=Check&CheckNo='.$result['CheckNo'].'&Vch='.$result['CVNo'].'&TxnID='.$txnid.'">Print Check (mm-dd-yyyy)</a> &nbsp &nbsp <a href="printvoucher.php?w=Check&CheckNo='.$result['CheckNo'].'&Vch='.$result['CVNo'].'&TxnID='.$txnid.'&PrintCheck=short">Print Check (mm/dd/yy)</a></td>';
        $editsub=false;$editok=false; $columnstoedit=array();
    }
    if ($showenc==1) { array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo','ReleaseDateByNo'); array_push($columnsub,'EncodedBy','TimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'</tr></table>';
    
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'ForInvoiceNo');
    $sqlsub='Select s.*, format(s.Amount,2) as Amt, b.Branch, ca.ShortAcctID as DebitAccount, e.Nickname as EncodedBy, CONCAT(t.CompanyName, " ",t.Address) as CompanyName, CONCAT(SUBSTR(s.TIN,1,3),"-",SUBSTR(s.TIN,4,3),"-",SUBSTR(s.TIN,7,3),"-",SUBSTR(s.TIN,10,3)) AS TIN,Entity as FromBudgetOf from acctg_2cvsub s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo
    join `1branches` b on b.BranchNo=s.BranchNo LEFT JOIN `acctg_1budgetentities` be on be.EntityID=s.FromBudgetOf
    join acctg_2cvmain m on m.CVNo=s.CVNo LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=s.TIN
    LEFT JOIN `acctg_21unipurchasewithlastperiod` pm ON pm.SupplierNo=m.PayeeNo AND pm.SupplierInv=s.ForInvoiceNo AND pm.BranchNo=s.BranchNo
    WHERE m.CVNo='.$txnid.' Order By '.$sortfield;
    
    $sqlsum='Select sum(Amount) as Total from  `acctg_2cvsub` s join `acctg_2cvmain` m on m.CVNo=s.CVNo
Where m.CVNo='.$txnid;
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
   
    $addlinfo='Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10).'<a href="addmain.php?w='. $whichqry.'">Add '. $whichqry.'</a>';
    $sql=$sqlsub;
    $editprocess ='preditsupplyside.php?CVNo='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=CVSubEdit&TxnSubId=';
    $txnsubid='TxnSubId';
    $editprocesslabel='Enter';
    $delprocess='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&w=acctg_2cvsub&l=acctg'.'&TxnSubId=';
    
    // start of unpaid inv list
    if(is_null($suppno)){
    //  $lookupdata='';
      goto nounpaid;
    }
    $sqlunpd='Select SupplierInv, concat(date_format(`Date`,\'%Y-%m-%d\')) as Details, PayBalance,format(PayBalance,2) as Amount, DateDue, b.Branch from acctg_23balperinv i join `1branches` as b on b.BranchNo= i.BranchNo where i.PayBalance<>0 and i.SupplierNo='.$suppno.' order by DateDue, SupplierInv';

$stmt=$link->query($sqlunpd);
    $result=$stmt->fetchAll();
    
    if ($stmt->rowCount()>0){
    $columnsub2=array('SupplierInv','Details','Branch','DateDue','Amount');
    $lookupdata='';$subcol=''; $left='40%'; $leftmargin='41%'; $right='59%'; 
    foreach ($columnsub2 as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    } 
    foreach($result as $row){
        $lookupdata=$lookupdata.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub2 as $colsub){
            $lookupdata=$lookupdata.'<td>'.$row[$colsub].'</td>';
        }
        $lookupdata=$lookupdata.($editsub?'<td><a href="praddsub.php?w=CVSubAutoAdd&action_token='.$_SESSION['action_token'].'&SupplierInv='.$row['SupplierInv'].'&SupplierNo='.$suppno.'&TxnID='.$txnid.'">Pay</a></td>':'').'</tr>';
        $colorcount++;
    }
    $lookupdata='<br><br>Unpaid Invoices<br><table><tr>'.$subcol.'<td>Pay?</td></tr><tbody>'.$lookupdata.'</tbody></table>';
    } else { $left='100%'; $leftmargin='101%'; $right='9%';}
    // end of unpaid inv list
    nounpaid:
    $columnnames=array(
                     array('field'=>'Particulars', 'type'=>'text','size'=>40,'required'=>false, 'autofocus'=>true ),
                    array('field'=>'Branch', 'type'=>'text','size'=>15,'required'=>true,'list'=>'branchnames', 'value'=>$_SESSION['@brn']),
					array('field'=>'FromBudgetOf', 'type'=>'text','size'=>15,'required'=>true,'list'=>'entities','value'=>$_SESSION['@brn']),
                    //array('field'=>'RCompany', 'type'=>'text','size'=>10,'required'=>false,'list'=>'companies'),
                    array('field'=>'TIN', 'caption'=>'TIN (numbers only)', 'type'=>'text','size'=>10),
                     array('field'=>'DebitAccount', 'type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                     array('field'=>'Amount', 'type'=>'text','size'=>15,'required'=>true),
                    array('field'=>'CVNo', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='praddsub.php?w=CVSubAdd&CVNo='.$txnid;
    $liststoshow=array('branchnames','companies');
    
    $whichotherlist='acctg';
    $otherlist=array('accounts');
    // info for posting:
    $post='1';
    $table='acctg_2cvmain';
    
    $txntype='CV'; $withsub=true;
    
    $sqlsum='Select ShortAcctID AS DebitAccount, SUM(Amount) as Total from acctg_2cvsub s JOIN `acctg_1chartofaccounts` ca on ca.AccountID=s.DebitAccountID where s.CVNo='.$txnid. ' GROUP BY DebitAccountID';
    $stmt=$link->query($sqlsum); $resultsum=$stmt->fetchAll();
    $addlinfo2='';
    foreach ($resultsum as $drid){
        $addlinfo2=$addlinfo2.'<tr><td>'.$drid['DebitAccount'].'</td><td>'.number_format($drid['Total'],2).'</tr>';
    }
    
    $addlinfo=$addlinfo.'<br><br>Subtotals:<table>'.$addlinfo2.'</table>';
    $fieldsinrow=3;
    include('../backendphp/layout/inputsubform.php');    
    goto noform;
        break;

CASE 'FutureCV':
if (!allowedToOpen(540,'1rtc')) { echo 'No permission'; exit;}
$txnid=intval($_REQUEST['CVNo']);
$title='Add/Edit PDC'; $showbranches=true;
    $sqlmain='SELECT m.*, PaymentMode,ca.ShortAcctID as CreditAccount, e.Nickname as EncodedBy FROM acctg_4futurecvmain m join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID join acctg_0paymentmodes pm on m.PaymentModeID=pm.PaymentModeID
left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE m.CVNo='.$txnid;

    $stmt=$link->query($sqlmain);     $result=$stmt->fetch();    $suppno=$result['PayeeNo'];
    
    $columnnamesmain=array('Date','DueDate','CVNo','PaymentMode','CheckNo','DateofCheck','PayeeNo','Payee','CreditAccount','ReleaseDate','Remarks','Posted');
    $columnsub=array('Particulars','Branch','FromBudgetOf','TIN','CompanyName','DebitAccount','Amount');//'RCompany',
    
    $main='';
        
    if ($result['Date']>$currentyr and $result['Posted']==0 and allowedToOpen(5401,'1rtc')){
        $editmain='<td><a href="editspecificssupply.php?edit=2&w=FutureCVMainEdit&CVNo='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=acctg_4futurecvmain&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editok=true;$editsub=true; $columnstoedit=array('Particulars','Branch','FromBudgetOf','RCompany','TIN','DebitAccount','Amount');
    } else {
        $editmain='<td><a href="printvoucher.php?w='.$whichqry.'&FromVch='.$result['CVNo'].'&ToVch='.$result['CVNo'].'">Print CV</a></td><td><a href="printvoucher.php?w=FutureCheck&CheckNo='.$result['CheckNo'].'&Vch='.$result['CVNo'].'&TxnID='.$txnid.'">Print Check</a></td>';
        $editok=false;$editsub=false; $columnstoedit=array();
    }
    if ($showenc==1) { array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo','ReleaseDateByNo'); array_push($columnsub,'EncodedBy','TimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'</tr></table>';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'ForInvoiceNo');
    $sqlsub='Select s.*, format(s.Amount,2) as Amt, b.Branch,  ca.ShortAcctID as DebitAccount, e.Nickname as EncodedBy, CONCAT(t.CompanyName, " ",t.Address) as CompanyName, CONCAT(SUBSTR(s.TIN,1,3),"-",SUBSTR(s.TIN,4,3),"-",SUBSTR(s.TIN,7,3),"-",SUBSTR(s.TIN,10,3)) AS TIN,Entity as FromBudgetOf from acctg_4futurecvsub s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo join `1branches` b on b.BranchNo=s.BranchNo left join acctg_1budgetentities be on be.EntityID=s.FromBudgetOf
    join acctg_4futurecvmain m on m.CVNo=s.CVNo
    LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=s.TIN 
    WHERE m.CVNo='.$txnid.' Order By '.$sortfield;
    
    $sqlsum='Select sum(Amount) as Total from  `acctg_4futurecvsub` s join `acctg_4futurecvmain` m on m.CVNo=s.CVNo
Where m.CVNo='.$txnid;
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $addlinfo='Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10).'<a href="addmain.php?w='. $whichqry.'">Add '. $whichqry.'</a>';
    $sql=$sqlsub;
    $editprocess ='preditsupplyside.php?CVNo='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=FutureCVSubEdit&TxnSubId=';
    $txnsubid='TxnSubId';
    $editprocesslabel='Enter';
    $delprocess='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&w=acctg_4futurecvsub&l=acctg'.'&TxnSubId=';
    $columnnames=array(
                     array('field'=>'Particulars', 'type'=>'text','size'=>40,'required'=>false, 'autofocus'=>true ),
                    array('field'=>'Branch', 'type'=>'text','size'=>15,'required'=>true,'list'=>'branchnames', 'value'=>$_SESSION['@brn']),
                    array('field'=>'TIN', 'caption'=>'TIN (numbers only)', 'type'=>'text','size'=>10),
                     array('field'=>'DebitAccount', 'type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
					 array('field'=>'FromBudgetOf', 'type'=>'text','size'=>15,'required'=>true,'list'=>'entities','value'=>$_SESSION['@brn']),
                     array('field'=>'Amount', 'type'=>'text','size'=>15,'required'=>true),
                    array('field'=>'CVNo', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='praddsub.php?w=FutureSubAdd&CVNo='.$txnid;
    $liststoshow=array('branchnames','companies');
    
    $whichotherlist='acctg';
    $otherlist=array('accounts');
    // info for posting:
    $post='1';
    $table='acctg_4futurecvmain';
    
    $txntype='futurecv';
    $withsub=true; $left='100%'; $leftmargin='101%'; $right='9%';
    $fieldsinrow=3;
    include('../backendphp/layout/inputsubform.php');
    goto noform;
        break;
      
CASE 'JV':
if (!allowedToOpen(592,'1rtc')) { echo 'No permission'; exit;}
$txnid=intval($_REQUEST['JVNo']);
   $title='Add/Edit JV'; $table='acctg_2jvmain'; $subtable='acctg_2jvsub'; $coltototal='Amount'; $amttoedit='Amount';


    $sqlmain='SELECT m.*, e.Nickname as EncodedBy FROM `'.$table.'` m left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE m.JVNo='.$txnid;

    $stmt=$link->query($sqlmain);    $result=$stmt->fetch();
   
    $columnsub=array('Date','Particulars','Branch','FromBudgetOf','DebitAccount','CreditAccount',$amttoedit);
    if ($whichqry=='Forex'){array_push($columnsub,'Forex',$coltototal);}
    $columnnamesmain=array('JVDate','JVNo','Remarks','Posted');
    if ($showenc==1) {
      array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo');
      array_push($columnsub,'EncodedBy','TimeStamp');
      } else { $columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
    
    
    if ($result['Posted']==0){
         $columnstoedit=array('Date','Particulars','Branch','FromBudgetOf','DebitAccount','CreditAccount',$amttoedit,'Forex');
         
        $left='40%'; $leftmargin='41%'; $right='50%';  // 45,47,55
        $topmargin='10%';
    } else {
    
    $columnstoedit=array(); 
    $left='40%'; $leftmargin='40%'; $right='50%'; $topmargin='0%';
    }
    
    
    $main='';
        
    if (editOk($table,$txnid,$link,$whichqry) and allowedToOpen(5921,'1rtc')){
        $editmain='<td><a href="editspecificssupply.php?edit=2&w='.$whichqry.'MainEdit&JVNo='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w='.$table.'&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editok=true; $editsub=true;
    } else {
        $editmain=''; $editok=false; $editsub=false; 
    }
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'</tr></table>';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.TimeStamp'); 
    $sqlsub='Select s.*, b.Branch, ca.ShortAcctID as DebitAccount, ca1.ShortAcctID as CreditAccount, e.Nickname as EncodedBy,Entity as FromBudgetOf from '.$subtable.' s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID join acctg_1chartofaccounts ca1 on ca1.AccountID=s.CreditAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo LEFT JOIN `acctg_1budgetentities` be on be.EntityID=s.FromBudgetOf
    join `1branches` b on b.BranchNo=s.BranchNo join '.$table.' m on m.JVNo=s.JVNo
    WHERE m.JVNo='.$txnid.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
    
    $sqlsum='SELECT sum('.$coltototal.') as Total FROM  `'.$subtable.'` s JOIN `'.$table.'` m ON m.JVNo=s.JVNo WHERE m.JVNo='.$txnid;
    $stmt=$link->query($sqlsum); $result=$stmt->fetch();
    $addlinfo='Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10).'<a href="addmain.php?w='. $whichqry.'">Add '. $whichqry.'</a>'.'<br><br>';

    
    $columnnames=array(
                     array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true, 'autofocus'=>true ),
                     array('field'=>'Particulars', 'type'=>'text','size'=>20,'required'=>false ),
                    array('field'=>'Branch', 'type'=>'text','size'=>15,'required'=>true,'list'=>'branchnames', 'value'=>$_SESSION['@brn']),
					array('field'=>'FromBudgetOf', 'type'=>'text','size'=>15,'required'=>true,'list'=>'entities','value'=>$_SESSION['@brn']),
                     array('field'=>'DebitAccount', 'type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                     array('field'=>'CreditAccount', 'type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                     array('field'=>'Amount', 'type'=>'text','size'=>15,'required'=>true),
                     array('field'=>'Forex', 'type'=>'text','size'=>7,'value'=>1,'required'=>true),
                    array('field'=>'JVNo', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
   
    $action='praddsub.php?w='.$whichqry.'SubAdd&JVNo='.$txnid;
    $liststoshow=array('branchnames','companies');
    $whichotherlist='acctg'; $otherlist=array('accounts');
    // info for posting: $table has been defined
    $post='1';
      $fieldsinrow=2;
    $editprocesslabel='Enter'; $editprocess='preditsupplyside.php?w='.$whichqry.'SubEdit&JVNo='.$txnid.'&TxnSubId=';$txnsubid='TxnSubId';
    $delprocess='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&w='.$subtable.'&l=acctg'.'&TxnSubId=';
    $withsub=true; 
    include('../backendphp/layout/inputsubform.php');
    // to show totals
    $colamt=$coltototal;
    unset($textfordisplay,$sql,$columnnames,$editprocess,$delprocess,$coltototal,$addlprocess,$addlprocesslabel,$sortfield);
    
    $sql='SELECT FORMAT(SUM(`'.$colamt.'`),2) AS Total, Branch FROM '.$subtable.' s join `1branches` b on b.BranchNo=s.BranchNo WHERE s.JVNo='.$txnid.' GROUP BY s.BranchNo ORDER BY Branch';
    $subtitle='<br/><br/>Totals Per Branch'; $columnnames=array('Branch','Total'); $width='30%';
    echo '<div id="right">';
    include('../backendphp/layout/displayastableonlynoheaders.php');
    $sql0='CREATE TEMPORARY TABLE AdjTotal AS 
SELECT DebitAccountID AS AccountID, TRUNCATE(SUM(Amount),2) AS Amount FROM acctg_2jvsub s WHERE JVNo='.$txnid.' GROUP BY DebitAccountID
UNION ALL
SELECT CreditAccountID AS AccountID, TRUNCATE(SUM(Amount)*-1,2) AS Amount FROM acctg_2jvsub s WHERE JVNo='.$txnid.' GROUP BY CreditAccountID';
    $stmt=$link->prepare($sql0); $stmt->execute();
    $sql='SELECT FORMAT(SUM(`Amount`),2) AS NetDRLessCR, ShortAcctID AS Account FROM AdjTotal s join `acctg_1chartofaccounts` ca on ca.AccountID=s.AccountID  GROUP BY s.AccountID ORDER BY Account';
    $subtitle='Totals Per Account'; $columnnames=array('Account','NetDRLessCR'); $width='30%';
    include('../backendphp/layout/displayastableonlynoheaders.php');
    echo '</div id="right">'; 
    goto noform;
        break;
  
}

 include('../backendphp/layout/inputsubform.php');
 noform:
       $link=null; $stmt=null;
?>