<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once('../switchboard/contents.php');

  

?><br><div id="section" style="display: block;"><?php
if(!allowedToOpen(6509,'1rtc')) { echo 'No permission'; exit;}
$which=(!isset($_GET['w'])?'List':$_GET['w']);

$columnstoadd=array('BulkItemCode','RepackItemCode','RepackQtyPerBulkUnit');
if (in_array($which,array('List','EditSpecifics'))){
   $sql='SELECT rp.*, c.Category AS BulkItemCategory, i.ItemDesc AS BulkItemDesc, i.Unit AS BulkUnit, 
       c2.Category AS RepackItemCategory, i2.ItemDesc AS RepackItemDesc, i2.Unit AS RepackUnit, e.Nickname AS EncodedBy FROM invty_1itemsforrepack rp
        LEFT JOIN `invty_1items` i ON i.ItemCode=rp.BulkItemCode LEFT JOIN `invty_1items` i2 ON i2.ItemCode=rp.RepackItemCode 
        LEFT JOIN `invty_1category` c ON c.CatNo=i.CatNo LEFT JOIN `invty_1category` c2 ON c2.CatNo=i2.CatNo
        JOIN `1employees` e ON e.IDNo=rp.EncodedByNo';
   $columnnameslist=array('BulkItemCode','BulkItemCategory','BulkItemDesc','BulkUnit','RepackItemCode','RepackItemCategory','RepackItemDesc','RepackQtyPerBulkUnit','RepackUnit','EncodedBy','TimeStamp');
} 


switch ($which){
   case 'List':
         $title='Items for Repack'; $formdesc='Blank descriptions mean there is no such item code.';
         $columnnames=array(
                    array('field'=>'BulkItemCode', 'type'=>'text','size'=>5,'required'=>true),
                    array('field'=>'RepackItemCode','type'=>'text','size'=>5,'required'=>true),
                    array('field'=>'RepackQtyPerBulkUnit','type'=>'text','size'=>5,'required'=>true)
                    );
                     
      $action='bulkitemsforrepack.php?w=Add'; $fieldsinrow=9; $liststoshow=array(); 
      if (allowedToOpen(65091,'1rtc')){
	 $method='post';
	 include('../backendphp/layout/inputmainform.php');
	 $delprocess='bulkitemsforrepack.php?w=Delete&BulkItemCode=';
	 $editprocess='bulkitemsforrepack.php?w=EditSpecifics&BulkItemCode='; $editprocesslabel='Edit'; 
         $columnstoedit=$columnstoadd;
	 } else { $columnstoedit=array();}
      
      $title=''; $formdesc='';$txnidname='BulkItemCode';
      $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' c.Category'); $columnsub=$columnnames;
        $sql=$sql.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');    
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        if (allowedToOpen(65091,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `invty_1itemsforrepack` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now()'; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if (allowedToOpen(65091,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `invty_1itemsforrepack` WHERE BulkItemCode='.$_GET['BulkItemCode'];
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;   
    case 'EditSpecifics':
         if (allowedToOpen(65091,'1rtc')){
	 $title='Edit Specifics';
	 $txnid=$_GET['BulkItemCode']; 
	 $sql=$sql.' WHERE rp.BulkItemCode='.$txnid;
	 
	 $columnstoedit=$columnstoadd;	 
	 $columnnames=$columnnameslist;
	 $columnswithlists=array();$listsname=array();
	 $editprocess='bulkitemsforrepack.php?w=Edit&BulkItemCode='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
	 } else { header("Location:".$_SERVER['HTTP_REFERER']);}
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if (allowedToOpen(65091,'1rtc')){
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `invty_1itemsforrepack` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE BulkItemCode='.$_GET['BulkItemCode']; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:bulkitemsforrepack.php");
        break;
    
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body></html>