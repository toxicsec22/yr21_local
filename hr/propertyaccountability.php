<H1> ALL UNFINISHED</H1>ALL UNFINISHED
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6892,'1rtc')) {   echo 'No permission'; exit;} 
$showbranches=false;
include_once('../switchboard/contents.php');

 
include_once('../backendphp/layout/regulartablestyle.php');

if (allowedToOpen(68921,'1rtc')){ 
    $columnnameslist=array('IDNo','Nickname','FullName','Position','Department/Branch','DateServed','PersonnelAction','Details','BasicSalary','Allowances','EncodedBy','TimeStamp');
    $columnstoadd=array('DateServed','Details','BasicSalary','Allowances');
} else { 
    $columnnameslist=array('IDNo','Nickname','FullName','Position','Department/Branch','DateServed','PersonnelAction','Details','EncodedBy','TimeStamp');
    $columnstoadd=array('DateServed','Details');
}


$which=(!isset($_GET['w'])?'List':$_GET['w']);
$month=(!isset($_REQUEST['Month'])?date('m'):$_REQUEST['Month']);

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

if (in_array($which,array('List','EditSpecifics'))){
    $columnnames=$columnnameslist;
    $columnsub=$columnnameslist;
   echo comboBox($link,'SELECT ActionID, ActionDesc AS PersonnelAction FROM `hr_0personnelaction` ORDER BY ActionDesc;','ActionID','PersonnelAction','personnelaction');
   echo comboBox($link,'SELECT IDNo, CONCAT(FullName, " - ", Position, ", ", IF(deptid=10,Branch,department)) AS FullNamePosition FROM `attend_30currentpositions` ORDER BY FullName;','IDNo','FullNamePosition','employees');
   $sql='SELECT pa.*, e.Nickname, CONCAT(e.FirstName," ",e.SurName) AS FullName, Position, CONCAT(Department, " - ", Branch) AS `Department/Branch`, ActionDesc AS PersonnelAction, department AS Department, e2.Nickname AS EncodedBy FROM `2personnelaction` pa JOIN `hr_0personnelaction` po ON po.ActionID=pa.ActionID 
JOIN `1departments` d ON d.deptid=pa.deptID JOIN `1branches` b ON b.BranchNo=pa.BranchNo
JOIN attend_0positions p ON p.PositionID=pa.PositionID
JOIN `1employees` e ON e.IDNo=pa.IDNo JOIN `1employees` e2 ON e2.IDNo=pa.EncodedByNo ';
}

switch ($which){
   case 'List':
         $title='Personnel Action'; $method='POST';
         $columnnames=array(
                    array('field'=>'DateServed','type'=>'date','size'=>5,'required'=>true),
                    array('field'=>'Employee', 'type'=>'text','size'=>10,'required'=>true, 'list'=>'employees'),
                    array('field'=>'PersonnelAction','caption'=>'Personnel Action','type'=>'text','size'=>10,'required'=>true, 'list'=>'personnelaction'),
                    array('field'=>'Details','type'=>'text','size'=>30,'required'=>true));
         if (allowedToOpen(68921,'1rtc')){ 
                    $columnnames[]=array('field'=>'BasicSalary','type'=>'text','size'=>10,'required'=>true);
                    $columnnames[]=array('field'=>'Allowances','type'=>'text','size'=>10,'required'=>true);
         }
      $action='propertyaccountability.php?w=Add';
      $liststoshow=array(); $fieldsinrow=7;
     include('../backendphp/layout/inputmainform.php');
      
      $title=''; $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'`TimeStamp`'); $columnsub=$columnnameslist;
        $sql.=' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' DESC'); 
        
        if (allowedToOpen(68921,'1rtc')){ $delprocess='propertyaccountability.php?w=Delete&TxnID=';}
        $editprocess='propertyaccountability.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit'; $txnidname='TxnID';
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql0='SELECT IDNo, deptid, BranchNo, PositionID FROM `attend_30currentpositions` WHERE CONCAT(FullName, " - ", Position, ", ", IF(deptid=10,Branch,department)) LIKE \''.$_POST['Employee'].'\'';
        $stmt0=$link->query($sql0); $res=$stmt0->fetch();
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `2personnelaction` SET IDNo='.$res['IDNo'].', deptid='.$res['deptid'].', BranchNo='.$res['BranchNo'].', PositionID='.$res['PositionID'].', ActionID='.comboBoxValue($link,'hr_0personnelaction','ActionDesc',$_POST['PersonnelAction'],'ActionID').', '.$sql.'  EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
         if (!allowedToOpen(68921,'1rtc')){ goto nodelete;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `2personnelaction` WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        nodelete:
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
   case 'EditSpecifics':
         if (!allowedToOpen(68921,'1rtc') AND !allowedToOpen(68922,'1rtc')){ goto noedit;}
         $title='Edit Personnel Action';
	 $txnid=intval($_GET['TxnID']); $main='2personnelaction'; $columnstoedit=$columnstoadd;
         $sql.=' WHERE TxnID='.$txnid; 
	 $columnnames=$columnnameslist; $columnstoedit[]='PersonnelAction';
         $columnswithlists=array('PersonnelAction');$listsname=array('PersonnelAction'=>'personnelaction');
	 $editprocess='propertyaccountability.php?w=Edit&TxnID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        if (!allowedToOpen(68921,'1rtc') AND !allowedToOpen(68922,'1rtc')){ goto noedit;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `2personnelaction` SET ActionID='.comboBoxValue($link,'hr_0personnelaction','ActionDesc',$_POST['PersonnelAction'],'ActionID').', EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        noedit:
        header("Location:propertyaccountability.php");
        break;
    
}
  $link=null; $stmt=null;
?>
</body></html>