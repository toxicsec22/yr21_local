<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(663,'1rtc')) {   echo 'No permission'; exit;}   
include_once('../switchboard/contents.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

include_once $path.'/acrossyrs/commonfunctions/listoptions.php'; 
?><br><div id="section" style="display: block;"><?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);
$defaultdate=(!isset($_REQUEST['Date'])?date('Y-m-d'):$_REQUEST['Date']); 
$txndate=!isset($_GET['Date'])?'`Date`=\''.$defaultdate.'\'':'`Date`=\''.$_GET['Date'].'\'';

$title='Credit Sales Report'; $formdesc='</i>Editable until 8 p.m. today<i><br>';
include_once('../backendphp/layout/clickontabletoedithead.php');
if (in_array($which,array('List','All'))){
?>
<form method="post" style="display:inline"
      action="<?php echo 'ar_report.php?w='.$which.'&Date='.(!isset($_REQUEST['Date'])?$defaultdate:$_REQUEST['Date']); ?>" enctype="multipart/form-data">
                Choose Date:  <input type="date" name="Date" value="<?php echo $defaultdate; ?>" ></input> 
 <input type="submit" name="lookup" value="Lookup"> 
</form>    
<?php
}
$columnstoadd=array('ContactPerson','Position','ContactNumber','Report','ActionDate');

if (in_array($which,array('List','EditSpecifics'))){
    if(allowedToOpen(6631,'1rtc')){  include_once "../generalinfo/lists.inc"; renderlist('allclients');    }
    echo comboBox($link,'SELECT 0 AS Action, "Collection" AS ActionDesc UNION SELECT 1, "Follow Up"','Action','ActionDesc','actions');
    
   $sql='SELECT m.*,s.*, c.ClientName, IF(s.Action=1,"Follow Up","Collection") AS Action, e1.Nickname AS ARStaff, e.Nickname AS EncodedBy, s.TimeStamp AS Time_Stamp, 
       @curRow := @curRow + 1 AS `Report#` FROM `calllogs_3armain` m JOIN `calllogs_3arsub` s ON m.TxnID=s.TxnID
JOIN `1employees` e ON e.IDNo=s.EncodedByNo JOIN `1employees` e1 ON e1.IDNo=m.ARIDNo 
JOIN `1clients` c ON c.ClientNo=s.ClientNo
JOIN    (SELECT @curRow := 0) cr ';
   $columnnameslist=array('Report#','ClientName','ContactPerson','Position','ContactNumber','Action','ActionDate','Report','ARStaff','EncodedBy','Time_Stamp');
   
} 

if(isset($_GET['TxnID'])){ $sql0='SELECT TxnID,`Date` FROM `calllogs_3armain` WHERE TxnID='.$_GET['TxnID'];}
elseif(isset($_GET['TxnSubId'])){ $sql0='SELECT m.TxnID,`Date` FROM `calllogs_3armain` m JOIN `calllogs_3arsub` s ON m.TxnID=s.TxnID WHERE TxnSubId='.$_GET['TxnSubId'];}
      else { $sql0='SELECT TxnID,`Date` FROM `calllogs_3armain` WHERE `Date`=\''.$defaultdate.'\';'; }
      $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
      
if ($res0['Date']==date('Y-m-d') and (allowedToOpen(6631,'1rtc'))){ 
            date_default_timezone_set('Asia/Manila'); 
            $today=getdate(); 
            if(time()<mktime(20, 0, 0, $today['mon'], $today['mday'], $today['year'])){ $editok=true; } else { $editok=false; }
        }else { $editok=false; }      


switch ($which){
   case 'List':
       
       $title=''; $formdesc=''; $method='post'; 
       
         $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'ClientName','caption'=>'Client','type'=>'text','size'=>10,'required'=>true, 'list'=>'allclients'),
                    array('field'=>'ContactPerson','type'=>'text','size'=>10,'required'=>true),
		    array('field'=>'Position','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'ContactNumber','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Report','rows'=>'4','cols'=>'50','type'=>'textarea','formid'=>'visit','required'=>true),
                    array('field'=>'Action', 'caption'=>'Next Action', 'type'=>'text','size'=>10, 'list'=>'actions','required'=>true),
                    array('field'=>'ActionDate', 'type'=>'date','size'=>20,'required'=>true));
       
      if (allowedToOpen(6632,'1rtc')) { 
          echo comboBox($link,'SELECT IDNo, FullName FROM `attend_30currentpositions` WHERE PositionID IN (150,151,152,153,154);','IDNo','FullName','arstaff');
           $columnnames[]=array('field'=>'ARStaff','caption'=>'ARStaff','type'=>'text','size'=>10,'required'=>true, 'list'=>'arstaff');
         }   else { $columnnames[]=array('field'=>'ARStaff','type'=>'hidden','size'=>0,'required'=>true, 'value'=>$_SESSION['(ak0)']);}
        
         $sql.='WHERE `Date`=\''.$defaultdate.'\'';
      $action='ar_report.php?w=Add'; $fieldsinrow=5; $liststoshow=array();
      	 include('../backendphp/layout/inputmainform.php');
      
      
      if($editok){
      $editprocess='ar_report.php?w=EditSpecifics&TxnSubId='; $editprocesslabel='Edit';   
        $delprocess='ar_report.php?w=Delete&TxnSubId=';
        $columnstoedit=array('ClientName','ContactPerson','Position','ContactNumber','Notes','QuoteNo','InvoiceNo');
      }
      $columnnames=$columnnameslist;
     //$sql=$sql.' WHERE s.TxnID='.$txnid;
     
        if(!empty($txnid)){
        $sql1='SELECT COUNT(TxnSubId) AS Reports FROM `calllogs_3arsub` WHERE TxnID='.$txnid; $stmt1=$link->query($sql1); $res1=$stmt1->fetch();
        $totalstext='<br>Total Reports: '.$res1['Reports'].'<br>';}
      $txnidname='TxnSubId';
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        if (allowedToOpen(6631,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if (allowedToOpen(6632,'1rtc')) { $ar=comboBoxValue($link,'`attend_30currentpositions`','FullName',$_REQUEST['ARStaff'],'IDNo');} 
        else {$ar=$_SESSION['(ak0)'];}
        $sql0='SELECT TxnID FROM `calllogs_3armain` WHERE `Date`=\''.$defaultdate.'\' AND `ARIDNo`='.$ar; 
       // if ($_SESSION['(ak0)']==1002){ echo $sql0;}
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        if($stmt0->rowCount()>0){ $txnid=$res0['TxnID'];} else {
            
            $sql1='INSERT INTO `calllogs_3armain` (ARIDNo,`Date`,EncodedByNo,`TimeStamp`) SELECT '.$ar.', \''.$defaultdate.'\', '.$_SESSION['(ak0)'].', Now();';
            $stmt1=$link->prepare($sql1); $stmt1->execute();
            $sql0='SELECT TxnID FROM `calllogs_3armain` WHERE `Date`=\''.$defaultdate.'\';';
            $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
        }
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; } 
        $sql='INSERT INTO `calllogs_3arsub` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' `Action`='.($_POST['Action']=='Collection'?0:1).', ClientNo='
                .(comboBoxValue($link,'`1clients`','Left(`ClientName`,20)',addslashes($_POST['ClientName']),'ClientNo'))
                .', TimeStamp=Now(), TxnID='.$txnid; 
        if ($_SESSION['(ak0)']==1002){ echo $sql;} 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if ($editok==true and allowedToOpen(6631,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `calllogs_3arsub` WHERE TxnSubId='.$_GET['TxnSubId'].' AND TxnID IN (SELECT TxnID FROM `calllogs_3armain` WHERE `Date`=CURDATE())';
        $stmt=$link->prepare($sql); $stmt->execute();
        }
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;   
    
    case 'EditSpecifics':
         $title='Edit Specifics';
	 $txnsubid=$_GET['TxnSubId']; 
	 $sql=$sql.'WHERE TxnSubId='.$txnsubid;
         $columnnames=$columnnameslist;
         if ($editok==true and allowedToOpen(6631,'1rtc')){ 
             $columnstoedit=$columnstoadd; $columnstoedit[]='Action'; 
             
         $columnswithlists=array('Action');$listsname=array('Action'=>'actions');
         $editprocess='ar_report.php?w=Edit&TxnSubId='.$txnsubid; 
         } else { $columnstoedit=array();} 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if ($editok==true and allowedToOpen(6631,'1rtc')){ 
        $sql=''; 
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `calllogs_3arsub` SET '.$sql.' Action='.($_REQUEST['Action']=='Collection'?0:1).', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() WHERE TxnSubId='.$_GET['TxnSubId'].' AND TxnID IN (SELECT TxnID FROM `calllogs_3armain` WHERE DATE_ADD(`Date`, INTERVAL 7 DAY)>=CURDATE())';
        if ($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        $sql0='SELECT TxnID FROM `calllogs_3arsub` WHERE TxnSubId='.$_GET['TxnSubId']; $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
        header("Location:ar_report.php?w=List&TxnID=".$txnid);
        break;
}
 $link=null; $stmt=null;  
?>
</div> <!-- end section -->
</body></html>