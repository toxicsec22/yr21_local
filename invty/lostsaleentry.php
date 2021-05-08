<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

if (!allowedToOpen(753,'1rtc')) {    echo 'No permission'; exit;}    


$user=$_SESSION['(ak0)'];
$group=$_SESSION['&pos'];

    
    $title='Lost Sales';
    $formdesc='Information entered here will be considered for the next delivery.<br>If the client looks for an item not in our list, pls encode as Non-Inventory (Item No. 15) and put the details in Remarks.';
    $lookupprocess='lostsaleentry.php?edit=1';
    $fieldname='Date';
    $editprocesslabel='Edit';
    $editprocess='lostsaleentry.php?edit=2&TxnID=';
    $listsname=array('ItemCode'=>'items','Client'=>'clients');
    $columnslist=array('ItemCode','Client');
    $liststoshow=array('items','clients');
    //$listcaption='';
    $processlabelblank='';
    $processblank='';
    //$calledfrom='6';
    $txnidname='TxnID';
    $method='POST';
    $orderby='Category';
$columnnames=array('TxnID','Category','ItemCode','Description','Qty','Unit','Client','Remarks','Branch','EncodedBy','TimeStamp');
$showbranches=true;
//$main=''

//include('../backendphp/layout/displayastablewithconditionandedit.php');            
include_once('../backendphp/layout/clickontabletoedithead.php');

newentry:
?><br>
    <form method="POST" action="praddsale.php?w=AddLostSale" enctype="multipart/form-data" style="font-family:sans-serif;">
<input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token'];?>" />
Client <input type=text size=20 name=Client  list="clients"   autocomplete="off">
ItemCode<input type=text size=20 name=ItemCode  list="items"   autocomplete="off">
Qty<input type=text size=10 name=Qty value=1   autocomplete="off">
Reason/Remarks<input type=text size=30 name=Remarks autocomplete="off">
<input type="submit" name="submit" value="Add">
</form>
<?php
include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);    
}
$subr=(!isset($_POST['subr']))?2:$_POST['subr']; 
?>
   <form method="post" action="lostsaleentry.php?edit=1" enctype="multipart/form-data">
                Sales From  <input type="date" name="FromDate" value="<?php echo date('Y-m-d',strtotime("-6 days")); ?>"></input> To <input type="date" name="ToDate" value="<?php echo date('Y-m-d'); ?>">
				Per Branch<input type="radio" name="subr" value="1">
				All Branches <input type="radio" name="subr" value="2"></input> 
<input type="submit" name="lookup" value="Lookup"> </form>
<br>
<?php
switch($subr){
        case 1:
            $title='Per Branch - '; $sqlcondition=' AND s.BranchNo='.$_SESSION['bnum'];
            break;
        case 2:
            $title='All Branches'; $sqlcondition=' ' ;
            break;
}
if (!isset($_REQUEST['FromDate'])){
$fromdate=date('Y-m-d',strtotime("-6 days"));
$todate=date('Y-m-d');
} else {
$fromdate=$_REQUEST['FromDate']; $todate=$_REQUEST['ToDate'];
}


if (allowedToOpen(7531,'1rtc')) {
        if ($_GET['edit']==2){ //called at Edit per row
             $columnstoedit=array('Client','ItemCode','Qty','Remarks');
            $txnid=intval($_GET['TxnID']);
            $sql='SELECT s.*,c.Category,i.ItemDesc as Description,i.Unit, b.Branch, e.Nickname as EncodedBy, ClientName AS Client FROM invty_6lostsales s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo join `1branches` as b on b.BranchNo=s.BranchNo join `1employees` as e on e.IDNo=s.EncodedByNo JOIN `1clients` cl ON cl.ClientNo=s.ClientNo WHERE (s.TxnID)='.$txnid;
            $action='praddsale.php?w=EditLostSale&TxnID='.$txnid;
            include('../backendphp/layout/rendersubform.php');
            goto noform;
        } elseif ($_GET['edit']==1){
            while($todate>=$fromdate){ 
            $subtitle=$todate; 
            $sql='SELECT s.*,c.Category,i.ItemDesc as Description,i.Unit, b.Branch, e.Nickname as EncodedBy, ClientName AS Client FROM invty_6lostsales s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo join `1branches` as b on b.BranchNo=s.BranchNo join `1employees` as e on e.IDNo=s.EncodedByNo JOIN `1clients` cl ON cl.ClientNo=s.ClientNo WHERE `Date`=\''.$todate.'\' '.$sqlcondition.'';
        // echo $sql;
        $delprocess='praddsale.php?w=DelLostSale&TxnID=';
        include('../backendphp/layout/displayastableonlynoheaders.php');
        $todate=date('Y-m-d',strtotime($todate."-1 day"));
             }
        }
            } else {
                $columnstoedit=array();
            }




noform:
    
     $link=null; $stmt=null;
?>