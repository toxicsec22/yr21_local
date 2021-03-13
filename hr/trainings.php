<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(689,'1rtc')) {   echo 'No permission'; exit;} 
include_once('../switchboard/contents.php');

 
include_once('../backendphp/layout/regulartablestyle.php');

$columnnameslist=array('TrainingTitle','BasicDescription','Duration'); //,'EncodedBy','TimeStamp');
$columnstoadd=array_diff($columnnameslist,array('EncodedBy','TimeStamp'));

$which=(!isset($_GET['which'])?'List':$_GET['which']);
switch ($which){
   case 'List':
         $title='List of Trainings'; $method='POST';
         $columnnames=array(
                    array('field'=>'TrainingTitle','caption'=>'Training Title','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'BasicDescription','caption'=>'Basic Description','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Duration','caption'=>'Duration (in Days)','type'=>'text','size'=>10,'required'=>true));
                     
      $action='trainings.php?which=Add';
      $liststoshow=array(); $fieldsinrow=6;
     include('../backendphp/layout/inputmainform.php');
      
      $title=''; $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'TrainingTitle'); $columnsub=$columnnameslist;
        $sql='SELECT t.*, e.Nickname as EncodedBy, t.TimeStamp, t.TrainingID AS TxnID FROM hr_1trainings t   
        JOIN `1employees` e ON e.IDNo=t.EncodedByNo ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
        
        $delprocess='trainings.php?which=Delete&TrainingID=';
        $editprocess='trainings.php?which=EditSpecifics&TrainingID='; $editprocesslabel='Edit'; $txnid='TrainingID';
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `hr_1trainings` SET '.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
         if (!allowedToOpen(6891,'1rtc')){ goto nodelete;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `hr_1trainings` WHERE TrainingID='.$_GET['TrainingID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        nodelete:
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
   case 'EditSpecifics':
         if (!allowedToOpen(6891,'1rtc')){ goto noedit;}
         $title='Edit Training';
	 $txnid=$_GET['TrainingID']; $main='hr_1trainings'; $columnstoedit=$columnstoadd;
         $sql='SELECT t.*, e.Nickname as EncodedBy, t.TimeStamp FROM hr_1trainings t   
        JOIN `1employees` e ON e.IDNo=t.EncodedByNo WHERE TrainingID='.$txnid;
	 $columnnames=$columnnameslist; 
	 $editprocess='trainings.php?which=Edit&TrainingID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `hr_1trainings` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE TrainingID='.$_GET['TrainingID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        noedit:
        header("Location:trainings.php");
        break;
    
}
  $link=null; $stmt=null;
?>
</body></html>