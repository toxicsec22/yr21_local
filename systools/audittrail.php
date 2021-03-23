<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(828,'1rtc')) {
   echo 'No permission'; exit;}
$showbranches=false;   
 include_once('../switchboard/contents.php'); include_once('../backendphp/layout/regulartablestyle.php');
 
 include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$sqlclient='SELECT ClientNo,ClientName FROM 1clients;';
echo comboBox($link,$sqlclient,'ClientName','ClientNo','client');

$sqlsupplier='SELECT SupplierNo,SupplierName FROM 1suppliers;';
echo comboBox($link,$sqlsupplier,'SupplierName','SupplierNo','supplier');




?><br><title>Encoding Audit Trail</title>
<h3>Encoding Audit Trail</h3><br><br>
&nbsp &nbsp <div><div style="float:left;"><form style="display:inline" action='#' method='POST'>Search for Txn ID (found in the url of the transaction):&nbsp &nbsp 
    <input type='text' name='stringsearch' autocomplete='off' size='10' >
    &nbsp &nbsp <input type='submit' name='submit' value='Inventory Data'>
    <?php 
    if (allowedToOpen(8281,'1rtc')) {
    ?>
    &nbsp &nbsp <input type='submit' name='submit' value='Accounting Data'>  </form>
	
	</div>
	<div>
	<form method="POST" action="audittrail.php">&nbsp; &nbsp;ClientNo: <input type="text" name="CNo" list="client"> <input type="submit" name="Clients" value="Clients"> 
	
	SupplierNo: <input type="text" name="SNo" list="supplier"> <input type="submit" name="Suppliers" value="Suppliers"></form></div></div> <br><br>
<?php
    }
if ((!isset($_POST['submit'])) AND (!isset($_POST['Clients'])) AND (!isset($_POST['Suppliers']))){ goto noform; }

if (isset($_POST['Clients'])){
	$_POST['submit']=$_POST['Clients'];
} elseif (isset($_POST['Suppliers'])) {
	$_POST['submit']=$_POST['Suppliers'];
}
else {
	
}

switch ($_POST['submit']){
case 'Inventory Data':  
    include_once '../generalinfo/unionlists/BECSList.php';
    
    $sqlmain='SELECT (SUBSTR(whichtable, INSTR(whichtable, "_") + 2)) AS `Where`, m.*, 
    BECSName AS `Supplier/Client`, txndesc AS `Transaction`, Branch, 
        CONCAT(e.Nickname," ",e.Surname) AS EncodedBy,
 CONCAT(e1.Nickname," ",e1.Surname) AS EditOrDelBy, IF(EditOrDel=0,"Edit","Delete") AS `EditORDelete` 
 FROM '.$currentyr.'_trail.invtytxnsmain m LEFT JOIN `invty_0txntype` t ON t.txntypeid=m.txntype
 LEFT JOIN `1employees` e ON m.EncodedByNo=e.IDNo LEFT JOIN `1employees` e1 ON m.EditOrDelByNo=e1.IDNo 
 LEFT JOIN `1branches` b ON b.BranchNo=m.BranchNo
 LEFT JOIN `BECSList` s on m.`SuppNo/ClientNo`=s.BECSNo AND s.BECS=(CASE 
 WHEN whichtable LIKE "invty_2mrr" OR whichtable LIKE "invty_2pr" THEN "S"
 WHEN whichtable LIKE "invty_2transfer" OR whichtable LIKE "invty_4adjust" THEN "B"
 ELSE IF(`SuppNo/ClientNo`<999,"E","C")
 END)
 WHERE m.TxnID='.$_POST['stringsearch'];
    $columnnamesmain=array('Where','Date','ControlNo','Transaction','Supplier/Client','Branch','ForPO/Request','Remarks','EncodedBy','EditOrDelBy','EditORDelete','EditOrDelTS');
    $sqlsub='SELECT (SUBSTR(whichtable, INSTR(whichtable, "_") + 2)) AS `Where`, s.*, Category, ItemDesc, IF(Defective=0,"Good","Defective") AS `GoodORDefective`, 
        CONCAT(e.Nickname," ",e.Surname) AS EncodedBy,
 CONCAT(e1.Nickname," ",e1.Surname) AS EditOrDelBy, IF(EditOrDel=0,"Edit","Delete") AS `EditORDelete` FROM '.$currentyr.'_trail.invtytxnssub s
LEFT JOIN `1employees` e ON s.EncodedByNo=e.IDNo LEFT JOIN `1employees` e1 ON s.EditOrDelByNo=e1.IDNo 
LEFT JOIN `invty_1items` i ON i.ItemCode=s.ItemCode LEFT JOIN `invty_1category` c ON c.CatNo=i.CatNo WHERE s.TxnID='.$_POST['stringsearch'];
    $columnnamessub=array('Where','ControlNo','ItemCode','Category','ItemDesc','Qty');
    if (allowedToOpen(8283,'1rtc')) { array_push($columnnamessub,'UnitCost');}
    array_push($columnnamessub,'UnitPrice','SerialNo','Remarks','GoodORDefective','EncodedBy','EditOrDelBy','EditORDelete','EditOrDelTS');
    break;  
case 'Accounting Data': 
    if (!allowedToOpen(8281,'1rtc')) {  echo 'No permission'; exit;}
    $sqlmain='SELECT (SUBSTR(whichtable, INSTR(whichtable, "_") + 2)) AS `Where`, m.*, ShortAcctID AS `Account`, IF(`DRCR`=1,"Debit","Credit") AS `DebitOrCredit`, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy,
 CONCAT(e1.Nickname," ",e1.Surname) AS EditOrDelBy, IF(EditOrDel=0,"Edit","Delete") AS `EditORDelete` FROM '.$currentyr.'_trail.acctgtxnsmain m
LEFT JOIN `1employees` e ON m.EncodedByNo=e.IDNo LEFT JOIN `1employees` e1 ON m.EditOrDelByNo=e1.IDNo 
LEFT JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=m.AccountID WHERE m.TxnID='.$_POST['stringsearch'];
    if (allowedToOpen(8284,'1rtc')) { $sqlmain=$sqlmain.' AND whichtable IN ("acctg_2depositmain","acctg_2collectmain","acctg_2salemain")';}
    $columnnamesmain=array('Where','Date','ControlNo','Supplier/Customer/Branch','Particulars','Account','DebitOrCredit','EncodedBy','EditOrDelBy','EditORDelete','EditOrDelTS');
    $sqlsub='SELECT (SUBSTR(whichtable, INSTR(whichtable, "_") + 2)) AS `Where`, s.*, ca.ShortAcctID AS `Debit`, ca1.ShortAcctID AS `Credit`, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy,
 CONCAT(e1.Nickname," ",e1.Surname) AS EditOrDelBy, IF(EditOrDel=0,"Edit","Delete") AS `EditORDelete`, Branch FROM '.$currentyr.'_trail.acctgtxnssub s
LEFT JOIN `1employees` e ON s.EncodedByNo=e.IDNo LEFT JOIN `1employees` e1 ON s.EditOrDelByNo=e1.IDNo 
LEFT JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=s.DebitAccountID LEFT JOIN `acctg_1chartofaccounts` ca1 ON ca1.AccountID=s.CreditAccountID
LEFT JOIN `1branches` b ON b.BranchNo=s.BranchNo WHERE s.TxnID='.$_POST['stringsearch'];
    $columnnamessub=array('Where','ControlNo','Particulars','Debit','Credit','Amount','Branch','EncodedBy','EditOrDelBy','EditORDelete','EditOrDelTS');
    break;
	
case 'Clients':
case 'Suppliers':
if(isset($_POST['Clients'])){
	if (!allowedToOpen(8,'1rtc')) { echo 'No permission'; exit(); }
	$table=''.$currentyr.'_trail.clientedits';
	echo '<br><h3>Client - ('.$_POST['CNo'].')</h3>';
	$scno=intval($_POST['CNo']);
	$field='ClientNo';
} else {
	if (!allowedToOpen(6436,'1rtc')) { echo 'No permission'; exit(); }   
	$table=''.$currentyr.'_trail.supplieredits';
	echo '<br><h3>Supplier - ('.$_POST['SNo'].')</h3>';
	$scno=intval($_POST['SNo']);
	$field='SupplierNo';
}
$title='';
$sql = 'SHOW COLUMNS FROM '.$table.''; $result = $link->query($sql); $res=$result->fetchAll();
foreach( $res as $col ) { $columnnames[] = $col['Field']; }


$sql='SELECT sc.*,CONCAT(Nickname," ",Surname) AS EditOrDelByNo FROM '.$table.' sc JOIN 1_gamit.0idinfo id ON sc.EditOrDelByNo=id.IDNo WHERE '.$field.'='.$scno.' ORDER BY EditOrDelTS DESC';
include('../backendphp/layout/displayastable.php');
goto noform;

break;
	
default:
}


$subtitle='<br><br>Results for: '.$_POST['stringsearch'].' in '.$_POST['submit']. ' - MAIN';
$sql=$sqlmain; $columnnames=$columnnamesmain;
include('../backendphp/layout/displayastableonlynoheaders.php');
$subtitle='<br><br>Results for: '.$_POST['stringsearch'].' in '.$_POST['submit']. ' - SUB';
$sql=$sqlsub; $columnnames=$columnnamessub;
include('../backendphp/layout/displayastableonlynoheaders.php');
noform:
    
?>