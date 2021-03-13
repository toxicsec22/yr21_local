<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5401,'1rtc')) { echo 'No permission'; exit;}  
 include_once $path.'/acrossyrs/dbinit/userinit.php';
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
include_once "../generalinfo/lists.inc";
 
include_once('../backendphp/functions/getuser.php'); 
$user=$resultuser['Nickname'];
//date_default_timezone_set('Asia/Manila'); 
?>
<html>
<head>
<title>Print Check Vouchers</title>
<style>

body  
{ 
    /* this affects the margin on the content before sending to printer */ 
    margin: 0px;
    font-size: 10pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
}
table,td {
        border:1px hidden;
border-collapse:collapse;
padding: 3px;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
    }

#voucher{   
page-break-inside:avoid;
}
a:link {
    color: black;
    text-decoration: none;
}
#check{
   
page-break-inside:avoid;
margin: 0mm 10mm 40mm 20mm;

font-family: Arial, Helvetica, sans-serif;
font-size: 11pt;
font-weight: 300;
   
}
/*#checkshort{
   
page-break-inside:avoid;
margin: 2mm 40mm 40mm 20mm;

font-family: Arial, Helvetica, sans-serif;
font-size: 11pt;
font-weight: 300;
   
}*/
</style>
</head>
<body>
<?php
$whichqry=$_GET['w'];
switch ($whichqry){
    CASE 'CV':
    Case 'FutureCV':
    if ($whichqry=='FutureCV'){$table='4future';} else {$table='2'; }
   $fromvch=$_REQUEST['FromVch'];
   $tovch=$_REQUEST['ToVch'];
   $title='CV';
   
   $sqlmain='SELECT m.*,PaymentMode, date_format(Date,\'%b %d, %Y\') AS Date, date_format(DateofCheck,\'%b %d, %Y\') AS DateofCheck, ca.ShortAcctID as CreditAccount, e.Nickname as EncodedBy, Sum(s.Amount) as Total FROM `acctg_'.$table.'cvmain` m
join acctg_1chartofaccounts ca on ca.AccountID=m.CreditAccountID
join acctg_0paymentmodes pm on m.PaymentModeID=pm.PaymentModeID
join `acctg_'.$table.'cvsub` s on m.CVNo=s.CVNo
left join `1employees` as e on e.IDNo=m.EncodedByNo
WHERE m.CVNo>='.$fromvch.' and m.CVNo<='.$tovch.' AND m.Posted=1 group by m.CVNo';
// echo $sqlmain;break;
$stmt=$link->query($sqlmain);
$result=$stmt->fetchAll();

foreach ($result as $mainrow){
	
$main='<div align="right"><img src="http://'.$_SERVER['HTTP_HOST'].'/favicon.ico" width="22px" height="22px"><font size="5"><b>1RTC</b></font></div><table width="90%" ><tr><td>Date:  '.$mainrow['Date'].'</td><td>CV No:  <a href="javascript:window.print()">'.$mainrow['CVNo'].'</a></td><td>PaymentMode: '.$mainrow['PaymentMode'].'</td></tr>
<tr><td>Check No: '.$mainrow['CheckNo'].'</td><td>Payee: '.$mainrow['Payee'].'</td><td>Credit :  '.$mainrow['CreditAccount'].'</td></tr>
<tr><td>Date of Check: '.$mainrow['DateofCheck'].'</td><td>Remarks: '.$mainrow['Remarks'].'</td></tr></table>';

$sqlsub='Select s.*, CONCAT(SUBSTR(s.TIN,1,3),"-",SUBSTR(s.TIN,4,3),"-",SUBSTR(s.TIN,7,3),"-",SUBSTR(s.TIN,10,3)) AS TIN, ca.ShortAcctID as DebitAccount, e.Nickname as EncodedBy,Entity as FromBudgetOf from `acctg_'.$table.'cvsub` s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo join acctg_1budgetentities be on be.EntityID=s.FromBudgetOf
    WHERE s.CVNo='.$mainrow['CVNo'].' Order By ForInvoiceNo';
    
    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    
// echo $sqlsub;break;
$sqlsubbranch='Select s.BranchNo, b.Branch from `acctg_'.$table.'cvsub` s 
    join `1branches` b on b.BranchNo=s.BranchNo
    WHERE s.CVNo='.$mainrow['CVNo'].' group by s.BranchNo Order By b.Branch';
    $stmt=$link->query($sqlsubbranch);
    $resultsubbranch=$stmt->fetchAll();    
    
    $sublabels='<table width="80%" class="subtable"><tr>
<td width=10%>Branch</td>
<td width=10%>FromBudgetOf</td>
<td width=30%>Particulars</td>
<td width=15%>For Invoice No</td><td width=10%>TIN</td>
<td width=15%>Debit </td>
<td width=20%>Amount</td></tr>';
$sub=''; 
foreach ($resultsubbranch as $rowbranch){   
$subbranch='<tr><td width=10%>'.$rowbranch['Branch'].'</td></tr>';
$subform='';
foreach ($resultsub as $row){
$subform=$subform.($row['BranchNo']==$rowbranch['BranchNo']?'<tr><td width=10%></td>
<td width=10%>'.$row['FromBudgetOf'].'</td>
<td width=30%>'.$row['Particulars'].'</td>
<td width=20%>'.$row['ForInvoiceNo'].'</td>
<td width=20%>'.$row['TIN'].'</td>
<td width=20%>'.$row['DebitAccount'].'</td>
<td width=20%>'.number_format($row['Amount'],2).'</td></tr>':'');

}
$sub=$sub.$subbranch.$subform;
}
$sub=$sublabels.$sub.'</table><center>------   NOTHING FOLLOWS  ------</center><br>';

    
    $total='<div style="float:right">Total:  '.number_format($mainrow['Total'],2).str_repeat('&nbsp',7) .'</div><br>' ;
	$sqlf='SELECT  CONCAT(left(FirstName,1),left(MiddleName,1),left(SurName,1)) as FinanceHeadNickName FROM attend_30currentpositions cp join 1employees e on e.IDNo=cp.IDNo WHERE PositionID=150;';
	// echo $sqlf; exit();
	$stmtf=$link->query($sqlf); $resultf=$stmtf->fetch();
	
	if($stmtf->rowCount()>0){
		$approvedby=$resultf['FinanceHeadNickName'].'/JYE';
	} else {
		$approvedby='';
	}
//$positionid=10; //get controller name
//include_once('../backendphp/functions/namefromposition.php');
$controllername='';//$resfrompos['Nickname']; TEMPORARILY BLANK
$voucher=$main.$sub.'<br>'.$total.'<br><br>
<table width="100%">
<tr><td width=20%">Prepared By:<br><br></td><td width="20%">Checked By:</td><td width="20%">Approved By:</td><td width="40%" align="right">Received By:__________________________</td>
</tr><tr><td>'. $mainrow['EncodedBy'].'</td><td>'. $controllername.'</td><td>'.$approvedby.'</td><td align="right"><font size="1">Signature above printed name</font></td></tr>
</table>
<br><div style="font-size: smaller;">Printed on '.date('m/d/y h:i:s l').' by '.$user.'</div><br><hr>
<br><br><br>';
 echo '<div id="voucher">'.$voucher.'</div>';
}  
      break;

CASE 'Check':
Case 'FutureCheck':
    if ($whichqry=='FutureCheck'){$table='4future';} else {$table='2'; }
// include_once('../backendphp/functions/numtowords.php');
include_once($path.'/acrossyrs/commonfunctions/numtowords.php');
   $checkno=$_REQUEST['CheckNo'];
   if (!isset($_REQUEST['Vch'])){ $condition='';} else {
   $vch=$_REQUEST['Vch'];
   $condition=' AND m.CVNo=\''.$vch.'\'';
   }
   
   $title='Check';
   $sql='SELECT date_format(DateofCheck,\'%m-%d-%Y\') as DateofCheck, date_format(DateofCheck,\'%m/%d/%y\') as ShortDateofCheck,ifnull(NameonCheck,Payee) as Payee, round(Sum(s.Amount),2) as Total FROM `acctg_'.$table.'cvmain` m
join `acctg_'.$table.'cvsub` s on m.CVNo=s.CVNo
left join `1suppliers` s2 on s2.SupplierNo=m.PayeeNo
WHERE m.CheckNo=\''.$checkno.'\' '.$condition.' AND m.Posted=1 group by m.CVNo;';
   // echo $sql;
$stmt=$link->query($sql);
$result=$stmt->fetch();

$dateofcheck=(!isset($_REQUEST['PrintCheck']) OR ($_REQUEST['PrintCheck']=='Print Check (mm-dd-yyyy)'))?$result['DateofCheck']:$result['ShortDateofCheck'];
   
$main='<table width="100%" ><tr><td width="80%" height="30px"></td><td width="20%">*** '.$dateofcheck.' ***</td><tr></tr><td width="80%" height="20px"><font size="3pt"><a href="javascript:window.print()">*** '.$result['Payee'].' ***</a></font></td><td>*** '.number_format($result['Total'],2).' ***</td></tr>
<tr><td height="25px" colspan="2">*** '.convert_number_to_words(number_format($result['Total'],2,'.','')).' only ***</td></tr></table>';
echo '<div id="check">'.$main.'</div>';
      break;
   
CASE 'Encashments':
   $fromdep=$_REQUEST['FromDep'];
   $todep=$_REQUEST['ToDep'];
   $title='Encashment Vouchers';
   
   $sqlmain='SELECT m.* FROM acctg_2depositmain m join `acctg_2depencashsub` s on m.TxnID=s.TxnID WHERE m.DepositNo>="'.$fromdep.'" and m.DepositNo<="'.$todep.'" AND m.Posted=1 group by DepositNo';
$stmt=$link->query($sqlmain);
$resultmain=$stmt->fetchAll();
   
$main='<center>Encashment Vouchers<br>From Deposit Numbers: <a href="javascript:window.print()">'.$fromdep.' To '.$todep.'</a></center><br><br>';

$columnnamesmain=array('Date','DepositNo');
$columnnames=array('Branch','EncashDetails','DebitAccount','Amount');

$sublabels='';
   
foreach ($columnnamesmain as $colmain){
   $sublabels=$sublabels.'<td>'.$colmain.'</td>';
}
foreach ($columnnames as $col){
   $sublabels=$sublabels.'<td>'.$col.'</td>';
}
$sublabels='<table width="100%" class="subtable"><tr>'.$sublabels.'</tr>';

$subform='';
foreach ($resultmain as $dep){
   $encashtotal=0;
   foreach ($columnnamesmain as $colmain){
   $subform=$subform.'<td>'.$dep[$colmain].'</td>';
}
   $sql='SELECT m.*, s.EncashDetails, b.Branch, ca.ShortAcctID as DebitAccount, s.Amount FROM acctg_2depositmain m
join `acctg_2depencashsub` s on m.TxnID=s.TxnID
join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID
left join `1employees` as e on e.IDNo=m.EncodedByNo
join `1branches` b on b.BranchNo=s.BranchNo
WHERE m.DepositNo="'.$dep['DepositNo'].'"';
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
$rownumber=0;
foreach ($result as $row){
   $rownumber=$rownumber + 1;
   $subform=$subform.(($rownumber<>1)?str_repeat('<td></td>',2):'');
   foreach ($columnnames as $col){
   $subform=$subform.'<td>'.$row[$col].'</td>';
}
$encashtotal=$encashtotal+$row['Amount'];
$subform=$subform.'</tr>';
}   
$subform=$subform.'<tr>'.str_repeat('<td></td>',4).'<td>Subtotal</td><td>'.number_format($encashtotal,2).'</td></tr>';
}
$sub=$subform.'</table><br><div style="float:left">Printed By:  '.$user.str_repeat('&nbsp',15) .'Approved By:  RCE/JYE</div><br>' ;

$voucher=$main.$sublabels.$sub.'<br>';
echo '<div id="voucher">'.$voucher.'</div>'; 
      break;
    
}
noform: 
     $stmt=null;  $link=null;
    ?>
</body>
</html>