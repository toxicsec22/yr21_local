<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(64311,'1rtc')) { echo 'No permission'; exit; }
include_once('../switchboard/contents.php');

  
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
?><br><div id="section" style="display: block;"><?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);

$columnstoadd=array('ToolID','ToolDesc','Unit');
if (in_array($which,array('List','EditSpecifics'))){
   echo comboBox($link,'SELECT * FROM `audit_0tooltype` ORDER BY ToolTypeDesc;','ToolTypeID','ToolTypeDesc','ToolType');
   $sql='SELECT t.*, ToolTypeDesc, e.Nickname as EncodedBy, t.ToolID AS TxnID FROM `audit_1tools` t
        JOIN `audit_0tooltype` tt ON t.ToolTypeID=tt.ToolTypeID 
        LEFT JOIN `1employees` e ON e.IDNo=t.EncodedByNo ';
   $columnnameslist=array('ToolID','ToolDesc','Unit','ToolTypeDesc');//,'EncodedBy','TimeStamp');
   
} 

if (in_array($which,array('Add','Edit'))){
    $tooltype=comboBoxValue($link,'`audit_0tooltype`','ToolTypeDesc',addslashes($_POST['ToolTypeDesc']),'ToolTypeID');
        }

switch ($which){
   case 'List':
       if (!allowedToOpen(64311,'1rtc')) { echo 'No permission'; exit; } 
         $title='List of Tools'; $formdesc='BE CAREFUL.  This affects all counts of tools.'; $method='post';
         if (allowedToOpen(64312,'1rtc')){
         $columnnames=array(
                    array('field'=>'ToolID', 'type'=>'text','size'=>5,'required'=>true),
                    array('field'=>'ToolTypeDesc','caption'=>'Tool Type','type'=>'text','size'=>10,'required'=>true, 'list'=>'ToolType'),
                    array('field'=>'ToolDesc','type'=>'text','size'=>25,'required'=>true),
                    array('field'=>'Unit','type'=>'text','size'=>5,'required'=>false));
                     
      $action='tools.php?w=Add'; $fieldsinrow=4; $liststoshow=array();
      
	 include('../backendphp/layout/inputmainform.php');
	 $delprocess='tools.php?w=Delete&ToolID=';
         $columnstoedit=array('ToolTypeDesc', 'ToolID', 'ToolDesc', 'Unit');
         }
      $title=''; $formdesc='';$txnidname='ToolID';
      $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' ToolDesc'); $columnsub=$columnnames;
        $sql=$sql.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');         
        if (allowedToOpen(64312,'1rtc')){ $editprocess='tools.php?w=EditSpecifics&ToolID='; $editprocesslabel='Edit'; }
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        if (allowedToOpen(64312,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `audit_1tools` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' ToolTypeID='.$tooltype.', TimeStamp=Now()'; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if (allowedToOpen(64312,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `audit_1tools` WHERE ToolID='.$_GET['ToolID'];
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;   
    case 'EditSpecifics':
         $title='Edit Specifics';
	 $txnid=$_GET['ToolID']; 
	 $sql=$sql.'WHERE t.ToolID='.$txnid;
	 $columnstoedit=$columnstoadd;$columnstoedit[]='ToolTypeDesc';
	 $columnnames=$columnnameslist;
	 $columnswithlists=array('ToolTypeDesc');$listsname=array('ToolTypeDesc'=>'ToolType');
	 $editprocess='tools.php?w=Edit&ToolID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	if (allowedToOpen(64312,'1rtc')){
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `audit_1tools` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' ToolTypeID='.$tooltype.', TimeStamp=Now() WHERE ToolID='.$_GET['ToolID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:tools.php");
        break;
    
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body></html>