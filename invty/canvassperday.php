<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6269,'1rtc')) {   echo 'No permission'; exit;}   
include_once('../switchboard/contents.php');



$user=$_SESSION['(ak0)'];
$group=$_SESSION['&pos'];

    ;
   
    $perday=!isset($_GET['perday'])?1:$_GET['perday'];
    $lookupprocess='canvassperday.php?edit=1&perday='.$perday;
    $fieldname=($perday==0?'Month':'Day');
$title='Canvass Per '.$fieldname;
    //$fieldname='CanvassDate';
    $processlabel1='Edit';
    $editprocess='canvassperday.php?edit=2&perday='.$perday.'&CanvassID=';$editprocesslabel='Edit';
    $listsname=array('Ordered?'=>'yesno','Go'=>'yesno','SupplierNo'=>'suppliers','Category'=>'categories','ItemCode'=>'items','ForBranch'=>'branches','ForClientName'=>'clients');
    $columnslist=array('Ordered?','Go','SupplierNo','Category','ItemCode','ForBranch','ForClientName');
    $liststoshow=array('yesno','branches','categories','items','suppliers','clients');
    $listcaption='';
    $processlabelblank='';
    $processblank='';
    $calledfrom='6';
    $txnidname='CanvassID';
    $method='POST';
    $orderby='ForBranch';
$columnnames=array('CanvassID','CanvassDate', 'ForBranch','Category','ItemCode','Description','MinPrice','QuotedPrice','PONo','Go','Downpayment','Ordered?','Delivered?','DeliveredByNo','Sold?','InvNo','CanvassRequestedByNo','CanvassRequestTS','CanvassedByNo','SCReplyTS','GoByNo','GoTimeStamp','OrderedByNo','OrderedTS','DeliveryEnteredByNo','ForClientName','DeliveryEnteredTS','SoldEntryByNo','SoldEntryTS');


$showbranches=true;

?>
<form method="post" action="<?php echo $lookupprocess; ?>" enctype="multipart/form-data">
<?php if ($perday==1){ ?>
                Choose Date:  <input type="date" name="<?php echo $fieldname; ?>" value="<?php echo date('Y-m-d'); ?>"></input>         
        <?php } else { ?>
                Choose Month (1 - 12):  <input type="text" name="<?php echo $fieldname; ?>" value="<?php echo date('m'); ?>"></input>
        <?php } ?>   
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
$edit=(!isset($_GET['edit'])?1:$_GET['edit']);

if($_REQUEST['perday']==1){
    if (!isset($_REQUEST[$fieldname])){ $fordate=date('Y-m-d'); } else {$fordate=$_POST[$fieldname];}
    $formdesc=$fordate.'<br>'; $txndate='CanvassDate=\''.$fordate.'\'';    
} else { // per month
    if (!isset($_REQUEST[$fieldname])){ $fordate=date('m'); } else {$fordate=$_POST[$fieldname];}
    $formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$fordate.'-1')).'<br>';   
$txndate='Month(CanvassDate)='.$fordate;
}


if (allowedToOpen(6269,'1rtc')) {
        if ($edit==2){ //called at Edit per row
            if(allowedToOpen(62691,'1rtc')) {
                        
                    $columnnames=array('CanvassID','CanvassDate', 'ForBranch','Category','ItemCode','Description','UnitCost','MinPrice','QuotedPrice','PONo','CanvassRequestedByNo','CanvassRequestTS','CanvassedByNo','SCReplyTS','SupplierNo','Go','Downpayment','Ordered?','GoByNo','GoTimeStamp','DPAmtByNo','DPAmtTimeStamp','OrderedByNo','OrderedTS','Delivered?','DeliveredByNo','DeliveryEnteredByNo','ForClientName','DeliveryEnteredTS','Sold?','InvNo','SoldEntryByNo','SoldEntryTS');
                    $columnstoedit=array('CanvassDate', 'ForBranch','ForClientName','Category','ItemCode','Description','UnitCost','MinPrice','QuotedPrice','PONo','SupplierNo','Go','Ordered?','Delivered?','DeliveredByNo','Sold?','InvNo');
                    $hiddencolumns=array('CanvassID','CanvassRequestedByNo','CanvassRequestTS','CanvassedByNo','SCReplyTS','GoByNo','GoTimeStamp','DPAmtByNo','DPAmtTimeStamp','OrderedByNo','OrderedTS','DeliveryEnteredByNo','DeliveryEnteredTS','SoldEntryByNo','SoldEntryTS');
            } elseif (allowedToOpen(62692,'1rtc')){                
                $columnstoedit=array('CanvassDate', 'ForBranch','ForClientName','Category','ItemCode','Description','QuotedPrice','Go','Sold?','InvNo');
                $hiddencolumns=array('PONo','Ordered?','Delivered?','DeliveredByNo','CanvassRequestedByNo','CanvassRequestTS','CanvassedByNo','SCReplyTS','GoByNo','GoTimeStamp','OrderedByNo','OrderedTS','DeliveryEnteredByNo','DeliveryEnteredTS','SoldEntryByNo','SoldEntryTS');
            } elseif (allowedToOpen(62693,'1rtc')){
                $columnstoedit=array('ForClientName','Go','Sold?');
                $hiddencolumns=array('CanvassID','CanvassDate', 'ForBranch','Category','ItemCode','Description','UnitCost','MinPrice','QuotedPrice','PONo','CanvassRequestedByNo','CanvassRequestTS','CanvassedByNo','SCReplyTS','SupplierNo','Ordered?','GoByNo','GoTimeStamp','DPAmtByNo','DPAmtTimeStamp','OrderedByNo','OrderedTS','Delivered?','DeliveredByNo','DeliveryEnteredByNo','DeliveryEnteredTS','InvNo','SoldEntryByNo','SoldEntryTS');
            } elseif (allowedToOpen(62694,'1rtc')){   
                $columnstoedit=array('Delivered?','DeliveredByNo');
                $hiddencolumns=array('CanvassID','CanvassDate', 'ForBranch','Category','ItemCode','Description','UnitCost','MinPrice','QuotedPrice','PONo','CanvassRequestedByNo','CanvassRequestTS','CanvassedByNo','SCReplyTS','SupplierNo','Go','Ordered?','GoByNo','GoTimeStamp','DPAmtByNo','DPAmtTimeStamp','OrderedByNo','OrderedTS','DeliveryEnteredByNo','ForClientName','DeliveryEnteredTS','Sold?','InvNo','SoldEntryByNo','SoldEntryTS');
            } else { $columnstoedit=array(); }
            $txnid=$_GET['CanvassID'];
            //$delprocess='praddcanvass.php?calledfrom=3&CanvassID=';
            
            $sql='SELECT c.*, b.Branch as `ForBranch`,`invty_1category`.Category FROM quotations_2canvass c INNER JOIN `invty_1category` ON c.Category = `invty_1category`.CatNo join `1branches` b on b.BranchNo=c.BranchNo
WHERE (c.CanvassID)='.$txnid;
            $action='praddcanvass.php?calledfrom=2&perday='.$perday.'&CanvassID='.$txnid;
            include('../backendphp/layout/rendersubform.php');
            goto noform;
        } elseif ($edit==1){
                //$txnid=$_GET['CanvassID'];
                // $addlcondition=' and (c.Description) Not Like "REGULAR PO*" and '.$txndate;
            $sql='SELECT c.*, b.Branch as `ForBranch`,`invty_1category`.Category FROM quotations_2canvass c INNER JOIN `invty_1category` ON c.Category = `invty_1category`.CatNo join `1branches` b on b.BranchNo=c.BranchNo and (c.Description) Not Like "REGULAR PO*" and '.$txndate;
        
        $delprocess='praddcanvass.php?calledfrom=3&perday='.$perday.'&CanvassID=';
        }
            } else {
                $columnstoedit=array();
            }
include('../backendphp/layout/displayastablewithedit.php');            
noform:
      $link=null; $stmt=null;
?>