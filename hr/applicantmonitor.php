<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6489,'1rtc')) {   echo 'No permission'; exit;} 
$showbranches=false; include_once('../switchboard/contents.php');

 
?><form action='applicantmonitor.php' method=get><input type=hidden name=show value=<?php echo (isset($_GET['show']) AND $_GET['show']==1?0:1); ?>><input type=submit value='Show All / Show Active Only'></form>
<?php
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=(!isset($_GET['which'])?'Active':$_GET['which']);

if (in_array($which,array('Active','EditSpecifics'))){
   echo comboBox($link,'SELECT * FROM attend_0positions ORDER BY Position;','PositionID','Position','positions');
   $columnnameslist=array('Date', 'FirstName', 'MiddleName', 'SurName', 'Position', 'MobileNo', 'Email', 'DateofInterview', 'Status','ReferredBy','Hired?','EncodedBy','TimeStamp');
}
      
switch ($which){
   case 'Active':
         $title='Applicants Monitoring'; $method='POST';
         $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>10,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'FirstName','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'MiddleName','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'SurName','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Position','type'=>'text','size'=>10,'required'=>true, 'list'=>'positions'),
                    array('field'=>'MobileNo','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Email','type'=>'text','size'=>10,'required'=>false),
                    array('field'=>'DateofInterview','type'=>'date','size'=>10,'required'=>false),
                    array('field'=>'Status','type'=>'text','size'=>15,'required'=>false),
                    array('field'=>'ReferredBy', 'type'=>'text','size'=>10, 'required'=>false));
                     
      $action='applicantmonitor.php?which=Add';
      $liststoshow=array(); $fieldsinrow=6;
      
     include('../backendphp/layout/inputmainform.php');
      
      $title='';
      $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' a.Date, a.SurName'); $columnsub=$columnnames;
        $sql='SELECT a.*, Position, e.Nickname as EncodedBy, a.TimeStamp, IF(a.Hired=0,"",IF(a.Hired=1,"Hired","Rejected")) AS `Hired?` FROM hr_2applicants a
        JOIN attend_0positions p ON p.PositionID=a.PositionID        
        JOIN `1employees` e ON e.IDNo=a.EncodedByNo '.((isset($_GET['show']) AND $_GET['show']==1)?'':' WHERE HIDE=0').'
        ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
        
        $delprocess='applicantmonitor.php?which=Delete&TxnID=';
        $addlprocess='applicantmonitor.php?which=Hide&TxnID='; $addlprocesslabel='Hide/Show';
        $columnstoedit=array('Date', 'FirstName', 'MiddleName', 'SurName', 'MobileNo', 'Email', 'DateofInterview', 'Status','ReferredBy','Hired');
        $editprocess='applicantmonitor.php?which=EditSpecifics&TxnID='; $editprocesslabel='Edit'; $txnidname='TxnID';
        echo 'Hired values: 0-Pending, 1-Hired, 2-Rejected';
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $position=comboBoxValue($link,'attend_0positions','Position',addslashes($_POST['Position']),'PositionID');
        $columnstoadd=array('Date', 'FirstName', 'MiddleName', 'SurName', 'MobileNo', 'Email', 'DateofInterview', 'Status','ReferredBy'); $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `hr_2applicants` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' PositionID='.$position.', TimeStamp=Now()';
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `hr_2applicants` WHERE TxnID='.$_GET['TxnID'].' AND EncodedByNo='.$_SESSION['(ak0)'];
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'EditSpecifics':
         $title='Edit Specifics';
	 $txnid=intval($_GET['TxnID']); 
         $columnstoedit=array('Date', 'FirstName', 'MiddleName', 'SurName', 'Position', 'MobileNo', 'Email', 'DateofInterview', 'Status','ReferredBy','Hired');;
	 $sql='SELECT a.*, Position, e.Nickname as EncodedBy, a.TimeStamp, IF(a.Hired=0,"",IF(a.Hired=1,"Hired","Rejected")) AS `Hired?` FROM hr_2applicants a
        JOIN attend_0positions p ON p.PositionID=a.PositionID        
        JOIN `1employees` e ON e.IDNo=a.EncodedByNo 
        WHERE TxnID='.$txnid;
	 $columnnames=$columnnameslist;
	 $columnswithlists=array('Position');$listsname=array('Position'=>'positions');
	 $editprocess='applicantmonitor.php?which=Edit&TxnID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $position=comboBoxValue($link,'attend_0positions','Position',addslashes($_POST['Position']),'PositionID');
        $columnstoadd=array('Date', 'FirstName', 'MiddleName', 'SurName', 'MobileNo', 'Email', 'DateofInterview', 'Status','ReferredBy','Hired'); $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `hr_2applicants` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' PositionID='.$position.', TimeStamp=Now() WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:applicantmonitor.php");
        break;
    case 'Hide':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql0='SELECT Hide FROM `hr_2applicants` WHERE TxnID='.$_GET['TxnID']; $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        $sql='UPDATE `hr_2applicants` SET Hide='.($res0['Hide']==0?1:0).' WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    
}
  $link=null; $stmt=null;
?>
</body></html>