<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once('../switchboard/contents.php');

  

?><br><div id="section" style="display: block;"><?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);

$columnstoadd=array('CatNo','Category','StdDesc');
if (in_array($which,array('List','EditSpecifics'))){
   $sql='SELECT c.*, e.Nickname as EncodedBy, CatNo AS TxnID FROM invty_1category c
        JOIN `1employees` e ON e.IDNo=c.EncodedByNo ';
   $columnnameslist=array('CatNo','Category','StdDesc');
} 


switch ($which){
   case 'List':
         $title='Categories'; 
         $columnnames=array(
                    array('field'=>'CatNo', 'type'=>'text','size'=>5,'required'=>true),
                    array('field'=>'Category','caption'=>'Category','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'StdDesc','caption'=>'Standard Item Desc Format','type'=>'text','size'=>10,'required'=>true)
                    );
                     
      $action='categories.php?w=Add'; $fieldsinrow=9; $liststoshow=array(); 
      if (allowedToOpen(64351,'1rtc')){
	 $formdesc='BE CAREFUL.  This affects the entire inventory system.'; $method='post';
	 include('../backendphp/layout/inputmainform.php');
	 $delprocess='categories.php?w=Delete&CatNo=';
	 $editprocess='categories.php?w=EditSpecifics&CatNo='; $editprocesslabel='Edit'; 
         $columnstoedit=array('CatNo','Category');
	 } else { $columnstoedit=array();}
      
      $title=''; $formdesc='';$txnidname='TxnID';
      $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Category'); $columnsub=$columnnames;
        $sql=$sql.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');    
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        if (allowedToOpen(64351,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `invty_1category` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now()'; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if (allowedToOpen(64351,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `invty_1category` WHERE CatNo='.$_GET['CatNo'].' AND (SELECT CatNo FROM `invty_1items` WHERE CatNo='.$_GET['CatNo'].' GROUP BY CatNo) IS NULL';
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;   
    case 'EditSpecifics':
         if (allowedToOpen(64351,'1rtc')){
	 $title='Edit Specifics';
	 $txnid=intval($_GET['CatNo']); 
	 $sql=$sql.'WHERE c.CatNo='.$txnid;
	 
	 $stmt0=$link->query('SELECT CatNo FROM `invty_1items` WHERE CatNo='.$txnid.' GROUP BY CatNo');
	 $formdesc=$stmt0->rowCount()>0?'<h4><font color="red">There is an item with this category.</font></h4>':'';
	 echo $formdesc;
	 $columnstoedit=$columnstoadd;	 
	 $columnnames=$columnnameslist;
	 $columnswithlists=array('Category','MovementType');$listsname=array('Category'=>'categories','MovementType'=>'movetype');
	 $editprocess='categories.php?w=Edit&CatNo='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
	 } else { header("Location:".$_SERVER['HTTP_REFERER']);}
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if (allowedToOpen(64351,'1rtc')){
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `invty_1category` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE CatNo='.$_GET['CatNo']; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:categories.php");
        break;
    
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body></html>