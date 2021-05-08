<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6488,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
include_once('../switchboard/contents.php');
 

$which=!isset($_REQUEST['w'])?'ListofTIN':$_REQUEST['w'];
include('../backendphp/functions/getnumber.php');

switch ($which){

case 'ListofTIN':
    $title='List of TIN';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'CompanyName');
    $sql='SELECT TIN as TxnID,`CompanyName`, `Address`, CONCAT(SUBSTR(TIN,1,3),"-",SUBSTR(TIN,4,3),"-",SUBSTR(TIN,7,3),"-",SUBSTR(TIN,10,3)) AS TIN,
    `CompanyName` AS FullCompanyName, `Address` AS FullAddress, CONCAT(SUBSTR(TIN,1,3),"-",SUBSTR(TIN,4,3),"-",SUBSTR(TIN,7,3),"-",SUBSTR(TIN,10,3)) AS LookupTIN,
    e.Nickname as EncodedBy, t.TimeStamp FROM `gen_info_1tinforexpenses` t JOIN `1employees` e ON e.IDNo=t.EncodedByNo ORDER BY `'.$sortfield.'`';
    $columnnames=array('CompanyName', 'TIN', 'Address','EncodedBy', 'TimeStamp'); $showbranches=false;
    $columnsub=$columnnames;
    ?><br><br>
		<form method='POST' action='tinforexpenses.php?w=Add' >
			Company<input type='text' name='CompanyName' size=12 required=true> &nbsp &nbsp 
			TIN (numbers only)<input type='text' name='TIN' size=10 required=true>&nbsp &nbsp
			Address<input type='text' name='Address' size=20 required=true> 
			<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />&nbsp &nbsp &nbsp
			<input type='submit' size=10 name='submit' value='Add'>
		</form>	

<?php
    $txnidname='TIN'; 
    if (allowedToOpen(64881,'1rtc')){
	$columnstoedit=array('CompanyName', 'TIN', 'Address');
	$columnnames[]='FullCompanyName'; $columnnames[]='FullAddress'; $columnnames[]='LookupTIN';
	$editprocess='tinforexpenses.php?w=Edit&TxnID='; $editprocesslabel='Enter';
        $delprocess='tinforexpenses.php?w=Del&TxnID=';
	include('../backendphp/layout/displayastableeditcells.php');
    } else {    include('../backendphp/layout/displayastable.php');}
    break;

case 'Add':
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $tin=str_replace("-","",$_POST['TIN']);
    if (strlen($tin)==12){
    $columnstoadd=array('CompanyName', 'Address'); $sql='';
    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
	$sql='INSERT INTO `gen_info_1tinforexpenses` SET TIN="'.$tin.'", '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
        $stmt=$link->prepare($sql);	$stmt->execute();
    header('Location: tinforexpenses.php');
    } else { header('Location: tinforexpenses.php?error=Wrong_TIN'); }
    break;

case 'Edit':
    if (!allowedToOpen(64881,'1rtc')){ header('Location: tinforexpenses.php');}
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid=str_replace("-","",$_REQUEST['TxnID']);
    $tin=str_replace("-","",$_POST['TIN']);
    if (strlen($tin)==12){
    $columnstoadd=array('CompanyName', 'Address'); $sql='';
    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
	$sql='UPDATE `gen_info_1tinforexpenses` SET TIN="'.$tin.'", '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() WHERE TIN LIKE "'.$txnid.'"'; 
        $stmt=$link->prepare($sql);	$stmt->execute();
    header('Location: tinforexpenses.php');
    } else { header('Location: tinforexpenses.php?error=Wrong_TIN'); }
    break;

case 'Del':
    if (!allowedToOpen(64882,'1rtc')){ header('Location: tinforexpenses.php');}
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid=str_replace("-","",$_REQUEST['TxnID']);
    $sql='DELETE FROM `gen_info_1tinforexpenses` WHERE TIN LIKE "'.$txnid.'"'; $stmt=$link->prepare($sql); $stmt->execute();
    header('Location: tinforexpenses.php');
    break;
    
}
  $link=null; $stmt=null;
?>