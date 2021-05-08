<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6273,'1rtc')){   echo 'No permission'; exit;}
include_once('../switchboard/contents.php');
 
 

    
    $title='Verbal Quotations';
    $whichqry=$_GET['w'];
    $showbranches=false;

switch ($whichqry){
case 'Quotes':
$show=!isset($_POST['show'])?0:$_POST['show'];
?>
<form style="display: inline;" method="post" action="#" action="verbalquote.php?w=Quotes">
   <input type=hidden name="show" value=<?php echo ($show==0?1:0); ?>>
                Choose Date:  <input type="date" name="Date" value="<?php echo date('Y-m-d'); ?>"></input>
   <input type="submit" name="submit" value="Show Per Day">
   <input type="submit" name="submit" value="Show Per Month">
</form>
<?php
include_once('../generalinfo/lists.inc');
$addlmenu='<form method="POST" action="verbalquote.php?w=AddVerbalQuote" enctype="multipart/form-data" style="font-family:sans-serif;">
<input type="hidden" name="action_token" value="'. $_SESSION['action_token'].'" />
Client<input type=text size=20 name="Client"  list="clients"   autocomplete="off">
ItemCode<input type=text size=20 name="ItemCode"  list="items"   autocomplete="off">
Quoted Price<input type=text size=10 name="QuotedPrice" value=0   autocomplete="off">
Remarks<input type=text size=30 name="Remarks" autocomplete="off">
<input type="submit" name="submit" value="Add">
</form>';
$addlmenu=$addlmenu.renderlist('clientswhole').renderlist('items');
$txnidname='TxnID';
$date=!isset($_POST['Date'])?date('Y-m-d'):date('Y-m-d');

    $condition=' where '.($show==0?'s.Date=\''.$date.'\'':'Month(s.Date)=Month(\''.$date.'\')');

$sql='SELECT s.*,cl.ClientName,c.Category,i.ItemDesc as Description,i.Unit, b.Branch, e.Nickname as EncodedBy FROM invty_6verbalquotes s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo join `1branches` as b on b.BranchNo=s.BranchNo join `1clients` as cl on cl.ClientNo=s.ClientNo join `1employees` as e on e.IDNo=s.EncodedByNo'.$condition;
// echo $sql; break;
$editprocess='verbalquote.php?w=EditVerbalQuote&edit=2&TxnID=';$editprocesslabel='Edit';
$delprocess='verbalquote.php?w=DelVerbalQuote&TxnID=';
$columnnames=array('Date','ClientNo','ClientName','Category','ItemCode','Description','QuotedPrice','Unit','Remarks','Branch','EncodedBy','TimeStamp');
    include('../backendphp/layout/displayastablewithedit.php');

break;

case 'AddVerbalQuote':
include_once('../backendphp/functions/getnumber.php');
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $clientno=comboBoxValue($link,'`1clients`','`ClientName`',$_POST['Client'],'ClientNo');
	$sqlinsert='Insert into `invty_6verbalquotes` SET Date=\''.date("Y-m-d").'\', ClientNo='.$clientno.', ';
        $sql='';
        $columnstoedit=array('ItemCode','QuotedPrice','Remarks');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	//echo $sql;
        
        $stmt=$link->prepare($sql);
	$stmt->execute();
        
        header("Location:verbalquote.php?w=Quotes");
break;

case 'DelVerbalQuote':
$txnid=intval($_REQUEST['TxnID']);
$sql='Delete from `invty_6verbalquotes` where TxnID='.$txnid . ' and BranchNo=\''.$_SESSION['bnum'].'\' and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
	echo "<font color='red'>Deletions can be done by the person who entered it.</font>";

$stmt=$link->prepare($sql);
	$stmt->execute();
        header("Location:verbalquote.php?w=Quotes");
break;

case 'EditVerbalQuote':
$txnid=intval($_REQUEST['TxnID']);

$columnnames=array('Date','ClientName','Category','ItemCode','Description','QuotedPrice','Unit','Remarks','Branch','EncodedBy','TimeStamp');
$columnstoedit=array('Date','ClientName','ItemCode','QuotedPrice','Remarks');
	
$columnslist=array('ClientName','ItemCode');
$listsname=array('ClientName'=>'clients','ItemCode'=>'items');
$liststoshow=array('clients','items');
$method='POST';
$action='verbalquote.php?w=ProcessEditVerbalQuote&edit=2&TxnID='.$txnid;

$sql='SELECT s.*,cl.ClientName,c.Category,i.ItemDesc as Description,i.Unit, b.Branch, e.Nickname as EncodedBy FROM invty_6verbalquotes s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo join `1branches` as b on b.BranchNo=s.BranchNo join `1clients` as cl on cl.ClientNo=s.ClientNo join `1employees` as e on e.IDNo=s.EncodedByNo where TxnID='.$txnid;

$processblank='';
$processlabelblank='';
include('../backendphp/layout/rendersubform.php');
    
    break;

case 'ProcessEditVerbalQuote':
    $txnid=intval($_REQUEST['TxnID']);
        include_once('../backendphp/functions/getnumber.php');
        $clientno=getNumber('Client',addslashes($_POST['ClientName']));
	$sqlupdate='UPDATE `invty_6verbalquotes` SET ClientNo='.$clientno.', ';
        $sql='';
        $columnstoedit=array('Date', 'ItemCode','QuotedPrice','Remarks');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.'  EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()  where TxnID='.$txnid;
	//echo $sql; BranchNo=\''.$_SESSION['bnum'].'\',
        
        $stmt=$link->prepare($sql);
	$stmt->execute();
        
        header("Location:verbalquote.php?w=Quotes");
        
    break;
         

}
noform:
      $link=null; $stmt=null;
?>