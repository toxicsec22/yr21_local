<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(756,'1rtc')) {   echo 'No permission'; exit;}
include_once('../switchboard/contents.php');

  
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
?><br><div id="section" style="display: block;"><?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);

$columnstoadd=array('TransferNo','SaleNo','Remarks');
if (in_array($which,array('List','EditSpecifics'))){
   echo comboBox($link,'SELECT * FROM `1branches` WHERE Active=1;','BranchNo','Branch','branches');
   $sql='SELECT sm.Date AS InvoiceDate, ac.*, c.ClientName AS Client, b.Branch AS ItemsFromBranch, b2.Branch AS InvoiceFromBranch, 
        SUM(Qty*UnitPrice) AS Amount, e1.Nickname AS Team_Leader, e.Nickname as EncodedBy FROM invty_4salesacrosscompanies ac
        JOIN `1branches` b ON b.BranchNo=ac.ItemsFromBranchNo JOIN `1branches` b2 ON b2.BranchNo=ac.InvoiceFromBranchNo
        LEFT JOIN `invty_2transfer` tm ON tm.TransferNo=ac.TransferNo AND tm.BranchNo=ac.ItemsFromBranchNo AND tm.ToBranchNo=ac.InvoiceFromBranchNo
        LEFT JOIN `invty_2sale` sm ON sm.SaleNo=ac.SaleNo AND sm.BranchNo=ac.InvoiceFromBranchNo
        JOIN `invty_2salesub` ss ON sm.TxnID=ss.TxnID JOIN `1clients` c ON sm.ClientNo=c.ClientNo
        LEFT JOIN `1employees` e ON e.IDNo=ac.EncodedByNo LEFT JOIN `1employees` e1 ON e1.IDNo=sm.TeamLeader ';
   $columnnameslist=array('ItemsFromBranch','TransferNo','InvoiceFromBranch','InvoiceDate','SaleNo','Client','Amount','Team_Leader','Remarks');//,'EncodedBy','TimeStamp');
   
} 

if (in_array($which,array('Add','Edit'))){
   $branchitems=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['ItemsFromBranch']),'BranchNo');
   $branchinv=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['InvoiceFromBranch']),'BranchNo');
        }

switch ($which){
   case 'List':
         $title='Sales Across Companies'; 
         $formdesc='All transfers and sales invoices must be encoded FIRST.  This is for TARGETS CALCULATION ONLY.<br><br> If team leader is blank, the sale will be credited to the team leader of the branch where the INVOICE came from.  To set the correct team leader, assign the team leader in the sales invoice. This must be done BEFORE Accounting records the sale.'; 
         $method='post';
         $columnnames=array(
                    array('field'=>'ItemsFromBranch', 'type'=>'text','size'=>10,'required'=>true, 'list'=>'branches'),
                    array('field'=>'TransferNo','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'InvoiceFromBranch', 'type'=>'text','size'=>10,'required'=>true, 'list'=>'branches'),
                    array('field'=>'SaleNo','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Remarks','type'=>'text','size'=>10,'required'=>false));
                     
      $action='salesacrosscompanies.php?w=Add'; $fieldsinrow=4; $liststoshow=array();
      
	 include('../backendphp/layout/inputmainform.php');
	 $delprocess='salesacrosscompanies.php?w=Delete&TxnID=';
         $columnstoedit=array('ItemsFromBranch','TransferNo','InvoiceFromBranch','SaleNo','Remarks');
	
      $title=''; $formdesc='';$txnidname='TxnID';
      $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'InvoiceDate'); $columnsub=$columnnames;
        $sql=$sql.' WHERE sm.txntype=2 GROUP BY sm.TxnID UNION       
       SELECT "" AS InvoiceDate, ac.*, ""  AS Client, "" AS ItemsFromBranch,"" AS InvoiceFromBranch, 0 AS Amount, "" AS Team_Leader, e.Nickname as EncodedBy FROM invty_4salesacrosscompanies ac
       LEFT JOIN `invty_2transfer` tm ON tm.TransferNo=ac.TransferNo AND tm.BranchNo=ac.ItemsFromBranchNo AND tm.ToBranchNo=ac.InvoiceFromBranchNo
	   JOIN `1employees` e ON e.IDNo=ac.EncodedByNo WHERE tm.TxnID IS NULL ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' DESC');         
        $editprocess='salesacrosscompanies.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit'; 
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        if (allowedToOpen(7561,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `invty_4salesacrosscompanies` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' ItemsFromBranchNo='.$branchitems.', InvoiceFromBranchNo='.$branchinv.', TimeStamp=Now()'; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if (allowedToOpen(7561,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `invty_4salesacrosscompanies` WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;   
    case 'EditSpecifics':
         $title='Edit Specifics';
	 $txnid=intval($_GET['TxnID']); 
	 $sql=$sql.' WHERE sm.txntype=2 AND ac.TxnID='.$txnid;
	 if (allowedToOpen(7561,'1rtc')){
         $columnstoedit=array('ItemsFromBranch','TransferNo','InvoiceFromBranch','SaleNo','Remarks');}
	 else { $columnstoedit=array();}
	 $columnnames=$columnnameslist;
	 $columnswithlists=array('ItemsFromBranch','InvoiceFromBranch');$listsname=array('ItemsFromBranch'=>'branches','InvoiceFromBranch'=>'branches');
	 $editprocess='salesacrosscompanies.php?w=Edit&TxnID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if (allowedToOpen(7561,'1rtc')){
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `invty_4salesacrosscompanies` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' ItemsFromBranchNo='.$branchitems.', InvoiceFromBranchNo='.$branchinv.',  TimeStamp=Now() WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:salesacrosscompanies.php");
        break;
    
}
     $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body></html>