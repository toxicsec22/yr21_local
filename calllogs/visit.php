<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(668,'1rtc')) {   echo 'No permission'; exit;}   
$datefield='VisitDate';  $file='visit'; 
?><br><div id="section" style="display: block;"><?php

include_once('telvisitcommon.php');
    include('dailysalesvisitdata.php'); $sql=$sql2; $columnnames=$columnnames2; $columnnameslist=$columnnames2;

    // the ff is COPIED DIRECTLY into dailysalesvisitdata.php
  /* $sql='SELECT m.*,s.*, e.Nickname AS EncodedBy, s.TimeStamp AS Time_Stamp, IF(RequestedBy=0,"STL","Client") AS Requested_By,
       IF(ISNULL(qm.QuoteID),"",CONCAT(\'<a href=../canvassandquote/addeditquote.php?QuoteID=\',qm.QuoteID,\'>Lookup</a>\')) AS QuoteLink, VisitPurpose AS Purpose,
       @curRow := @curRow + 1 AS `Visit#` 
       FROM `calllogs_2visitmain` m JOIN `calllogs_2visitsub` s ON m.TxnID=s.TxnID
JOIN `1employees` e ON e.IDNo=s.EncodedByNo JOIN    (SELECT @curRow := 0) c JOIN `calllogs_0visitpurpose` v ON v.VisitID=s.VisitID
LEFT JOIN `quotations_2quotemain` qm ON qm.ClientName=s.ClientName AND qm.QuoteID=REPLACE(s.QuoteNo,\'18-\',\'\')';
   $columnnameslist=array('Visit#','ClientName','ContactPerson','Position','ContactNumber','Address','Purpose','DetailsofMtg','FollowUpAction','FollowUpActionDate','Requested_By','Attendees','QuoteNo','QuoteLink','InvoiceNo','Time_Stamp');*/
   
//
//if($which=='All'){ goto skipsql;}
//elseif(isset($_GET['TxnID'])){ $sql0='SELECT TxnID,`VisitDate` FROM `calllogs_2visitmain` WHERE TxnID='.$_GET['TxnID'];}
//elseif(isset($_GET['TxnSubId'])){ $sql0='SELECT m.TxnID,`VisitDate` FROM `calllogs_2visitmain` m JOIN `calllogs_2visitsub` s ON m.TxnID=s.TxnID WHERE TxnSubId='.$_GET['TxnSubId'];}
//      else { $sql0='SELECT TxnID,`VisitDate` FROM `calllogs_2visitmain` WHERE TLIDNo='.$tl.' AND `VisitDate`=\''.$defaultdate.'\';'; }
//     // if ($_SESSION['(ak0)']==1002){ echo $sql0;}
//      $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
//
//if ($res0['VisitDate']==date('Y-m-d') and (allowedToOpen(6641,'1rtc'))){ 
//            date_default_timezone_set('Asia/Manila'); $today=getdate(); 
//            if(time()<mktime(20, 0, 0, $today['mon'], $today['mday'], $today['year'])){ $editok=true; } else { $editok=false; }
//        }else { $editok=false; }
//        
$columnstoadd=array('ClientName','ContactPerson','Position','ContactNumber','Address','DetailsofMtg','FollowUpAction','FollowUpActionDate','Attendees','QuoteNo','InvoiceNo');

skipsql:
    
if(allowedToOpen(6643,'1rtc')){ include_once('telvisitcomment.php'); }
    
    
switch ($which){
   case 'List':
       if(isset($_POST['showall']) and ((!allowedToOpen(6641,'1rtc')))){ header('Location:visit.php?w=All&Date='.$defaultdate);}
       $title=''; $formdesc=''; $method='post'; $formid='visit';
       
       if (allowedToOpen(6641,'1rtc')){ 
           
         $columnnames=array(
                    array('field'=>'VisitDate', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'ClientName','caption'=>'Client','type'=>'text','size'=>10,'required'=>true, 'list'=>'clients'),
                    array('field'=>'ContactPerson','type'=>'text','size'=>10,'required'=>true),
		    array('field'=>'Position','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'ContactNumber','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Address','type'=>'text','size'=>25,'required'=>true),
                    array('field'=>'Attendees', 'caption'=>'Number of attendees','type'=>'text','size'=>3,'required'=>true),
                    array('field'=>'Purpose','caption'=>'Purpose of Visit','type'=>'text','size'=>10,'required'=>true, 'list'=>'purpose'),
                    array('field'=>'RequestedBy', 'caption'=>'Meeting requested by','type'=>'text','size'=>10,'required'=>true, 'list'=>'reqby'),
                    array('field'=>'DetailsofMtg','rows'=>'4','cols'=>'50','type'=>'textarea','formid'=>$formid,'required'=>true),
                    array('field'=>'FollowUpAction', 'type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'FollowUpActionDate', 'type'=>'date','size'=>20,'required'=>true),
                    array('field'=>'QuoteNo','type'=>'text','size'=>5,'required'=>false),
                    array('field'=>'InvoiceNo','type'=>'text','size'=>5,'required'=>false));
       
                        
      $action='visit.php?w=Add&TL='.$tl; $fieldsinrow=4; $liststoshow=array();
      	 include('../backendphp/layout/inputmainform.php');
      
      } 
      
      if($editok){
      $editprocess='visit.php?w=EditSpecifics&TL='.$tl.'&TxnSubId='; $editprocesslabel='Edit';   
        $delprocess='visit.php?w=Delete&TxnSubId=';
        $columnstoedit=array('ClientName','ContactPerson','Position','ContactNumber','Notes','QuoteNo','InvoiceNo');
      }
      $columnnames=$columnnameslist;
     $sql=$sql2.' WHERE s.TxnID='.$txnid;
   //  if ($_SESSION['(ak0)']==1002){ echo $sql;}
        if(!empty($txnid)){
        $sql1='SELECT COUNT(TxnSubId) AS Visits, (SELECT COUNT(TxnSubId) FROM `calllogs_2visitsub` WHERE NOT ISNULL(InvoiceNo) AND InvoiceNo NOT LIKE "") AS WithInvoice FROM `calllogs_2visitsub` WHERE TxnID='.$txnid;
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        $stmt1=$link->query($sql1); $res1=$stmt1->fetch();
        $totalstext='<br>Total Sales Visits: '.$res1['Visits'].'<br>Total Invoices: '.$res1['WithInvoice'].'<br>';}
      $txnidname='TxnSubId';
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        if (allowedToOpen(6641,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql0='SELECT TxnID FROM `calllogs_2visitmain` WHERE TLIDNo='.$tl.' AND `VisitDate`=\''.$defaultdate.'\';';
        if ($_SESSION['(ak0)']==1002){ echo $sql0;}
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        if($stmt0->rowCount()>0){ $txnid=$res0['TxnID'];} else {
            $sql1='INSERT INTO `calllogs_2visitmain` (TLIDNo,`VisitDate`,EncodedByNo,`TimeStamp`) SELECT '.$tl.', \''.$defaultdate.'\', '.$_SESSION['(ak0)'].', Now();';
            $stmt1=$link->prepare($sql1); $stmt1->execute();
            $sql0='SELECT TxnID FROM `calllogs_2visitmain` WHERE TLIDNo='.$tl.' AND `VisitDate`=\''.$defaultdate.'\';';
            $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
        }
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; } 
        $sql='INSERT INTO `calllogs_2visitsub` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' `RequestedBy`='.($_POST['RequestedBy']=='STL'?0:1).', '
                . '`VisitID`='.comboBoxValue($link,'`calllogs_0visitpurpose`','VisitPurpose',$_POST['Purpose'],'VisitID').', TimeStamp=Now(), TxnID='.$txnid; 
       // if ($_SESSION['(ak0)']==1002){ echo $sql.$_POST['Purpose'];} 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if ($editok==true and allowedToOpen(6641,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `calllogs_2visitsub` WHERE TxnSubId='.$_GET['TxnSubId'].' AND TxnID IN (SELECT TxnID FROM `calllogs_2visitmain` WHERE `VisitDate`=CURDATE())';
        $stmt=$link->prepare($sql); $stmt->execute();
        }
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;   
    
    case 'EditSpecifics':
         $title='Edit Specifics';
	 $txnsubid=$_GET['TxnSubId']; 
	 $sql=$sql2.'WHERE TxnSubId='.$txnsubid;
         $columnnames=$columnnameslist;
         if($editok==true){ 
             $columnstoedit=$columnstoadd; $columnstoedit[]='Requested_By'; $columnstoedit[]='Purpose';
         $columnswithlists=array('ClientName','Requested_By', 'Purpose');$listsname=array('ClientName'=>'clients','Requested_By'=>'reqby','Purpose'=>'purpose');
         $editprocess='visit.php?w=Edit&TL='.$tl.'&TxnSubId='.$txnsubid; 
         } else { $columnstoedit=array();}
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        
	if ($editok==true and allowedToOpen(6641,'1rtc')){
        $sql=''; 
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `calllogs_2visitsub` SET ClientNo=\''.(comboBoxValue($link,'`1clients`','LEFT(`ClientName`,20)',$_REQUEST['ClientName'],'ClientNo')).'\', '.$sql.' `RequestedBy`='.(strtoupper($_POST['Requested_By'])=='STL'?0:1).', EncodedByNo='.$_SESSION['(ak0)'].', `VisitID`='.comboBoxValue($link,'`calllogs_0visitpurpose`','VisitPurpose',$_POST['Purpose'],'VisitID').', TimeStamp=Now() WHERE TxnSubId='.$_GET['TxnSubId'].' AND TxnID IN (SELECT TxnID FROM `calllogs_2visitmain` WHERE DATE_ADD(`VisitDate`, INTERVAL 7 DAY)>=CURDATE())';
        if ($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        $sql0='SELECT TxnID FROM `calllogs_2visitsub` WHERE TxnSubId='.$_GET['TxnSubId']; $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
        header("Location:visit.php?w=List&TL=".$tl."&TxnID=".$txnid);
        break;
   case 'All':
       $title=''; $formdesc=''; $txnid='TxnSubId';
       //if ($_SESSION['(ak0)']==1002){ echo $sql;}
       include('dailysalesvisitdata.php');
       include('../backendphp/layout/displayastablewithsub.php');
       break;
}
 $link=null; $stmt=null;  $stmt0=null; $stmt1=null;
?>
</div> <!-- end section -->
</body></html>