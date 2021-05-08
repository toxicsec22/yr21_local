<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6487,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
$which=!isset($_REQUEST['w'])?'ClientBdayList':$_REQUEST['w'];
include('../backendphp/functions/getnumber.php');

switch ($which){

case 'ClientBdayList':
    $title='Client Birthdays';
    include_once('../switchboard/contents.php');
    include('../generalinfo/unionlists/clientbdayssql.php');
    
    
    $sql='SELECT bday.* FROM `gen_info_clientbdays` bday ORDER BY `ToSort`';      
    $columnnames=array('Company', 'Name', 'Birthday', 'Position','Branches','EncodedBy', 'TimeStamp'); $showbranches=false;
    $columnstoedit=array('Name', 'Position');
    $txnidname='TxnID';      
    if (allowedToOpen(64871,'1rtc')){
    ?><br><br>
		<form method='POST' action='clientbdays.php?w=Add' >
			Company<input type='text' name='Company' size=12 required=true list='clients'> &nbsp &nbsp 
			Name<input type='text' name='Name' size=10>&nbsp &nbsp
			Position<input type='text' name='Position' size=10> &nbsp &nbsp
			Birthday<input type='date' name='Bday' size=6> 
			<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />&nbsp &nbsp &nbsp
			<input type='submit' size=10 name='submit' value='Add'>
		</form>	

<?php
      
    $editprocess='clientbdays.php?w=Edit&TxnID=';$editprocesslabel='Enter';
    $delprocess='clientbdays.php?w=Del&TxnID=';
    $liststoshow=array('clients');
    
    include('../backendphp/layout/displayastableeditcells.php');
    } else { include('../backendphp/layout/displayastable.php');}
     
    break;
//include('../backendphp/layout/regularaddeditdel.php');
case 'Add':
    if (!allowedToOpen(64871,'1rtc')){ header('Location: clientbdays.php');}
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $clientno=getNumber('Client',addslashes($_POST['Company']));
    $columnstoadd=array('Name', 'Position','Bday'); $sql='';
    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
	$sql='INSERT INTO `gen_info_1clientbdays` SET ClientNo='.$clientno.', '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
        $stmt=$link->prepare($sql);	$stmt->execute();
    header('Location: clientbdays.php');
    break;

case 'Edit':
    if (!allowedToOpen(64871,'1rtc')){ header('Location: clientbdays.php');}
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid=intval($_REQUEST['TxnID']);
    $columnstoadd=array('Name', 'Position'); $sql='';
    foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
	$sql='UPDATE `gen_info_1clientbdays` SET '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() WHERE TxnID='.$txnid; 
        $stmt=$link->prepare($sql);	$stmt->execute();
    header('Location: clientbdays.php');
    break;

case 'Del':
    if (!allowedToOpen(64871,'1rtc')){ header('Location: clientbdays.php');}
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $txnid=intval($_REQUEST['TxnID']);
    $sql='DELETE FROM `gen_info_1clientbdays` WHERE TxnID='.$txnid; $stmt=$link->prepare($sql); $stmt->execute();
    header('Location: clientbdays.php');
    break;
    
}
  $link=null; $stmt=null;
?>