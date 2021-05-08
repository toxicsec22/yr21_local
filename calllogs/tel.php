<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(667,'1rtc')) {   echo 'No permission'; exit;}  
$datefield='Date'; $file='tel';
?><br><div id="section" style="display: block;"><?php
include_once('telvisitcommon.php');
include('dailycalllogsdata.php'); $sql=$sql2; $columnnames=$columnnames2; $columnnameslist=$columnnames2;
//if (in_array($which,array('List','EditSpecifics','All'))){
   /*
   echo comboBox($link,'SELECT `c`.`ClientNo` AS `ClientNo`,
        c.`ClientName` FROM
        (`1clients` `c`
        JOIN `gen_info_1branchesclientsjxn` `bc` ON ((`c`.`ClientNo` = `bc`.`ClientNo`)))
        WHERE (`bc`.`BranchNo` IN (SELECT BranchNo FROM `attend_1branchgroups` WHERE `c`.`ClientNo`>10001 AND TeamLeader='.$tl.')) OR c.KeyAccount=1 GROUP BY c.ClientNo ORDER BY `ClientName`;',
    'ClientNo','ClientName','clients');*/
   
    // the ff is COPIED DIRECTLY into dailycalllogsdata.php
   /*$sql1='SELECT m.*,s.*, IF(`QuoteType`=0,\'Verbal\',\'Formal\') AS `Quote_Type`, e.Nickname AS EncodedBy, s.TimeStamp AS Time_Stamp, 
       IF(ISNULL(qm.QuoteID),"",CONCAT(\'<a href=../canvassandquote/addeditquote.php?QuoteID=\',qm.QuoteID,\'>Lookup</a>\')) AS QuoteLink, 
       @curRow := @curRow + 1 AS `Call#` 
       FROM `calllogs_2telmain` m JOIN `calllogs_2telsub` s ON m.TxnID=s.TxnID
JOIN `1employees` e ON e.IDNo=s.EncodedByNo JOIN    (SELECT @curRow := 0) c 
LEFT JOIN `quotations_2quotemain` qm ON (IF(ISNULL(qm.ClientNo),qm.ClientName=s.ClientName,qm.ClientNo=s.ClientNo)) AND qm.QuoteID=REPLACE(s.QuoteNo,\'18-\',\'\')';
   $columnnameslist=array('Call#','ClientNo','ClientName','ContactPerson','Position','ContactNumber','Notes','Quote_Type','QuoteNo','QuoteLink','InvoiceNo','Time_Stamp');
   */
//} 

//if($which=='All'){ goto skipsql;}
//elseif(isset($_GET['TxnID'])){ $sql0='SELECT TxnID,`Date` FROM `calllogs_2telmain` WHERE TxnID='.$_GET['TxnID'];}
//elseif(isset($_GET['TxnSubId'])){ $sql0='SELECT m.TxnID,`Date` FROM `calllogs_2telmain` m JOIN `calllogs_2telsub` s ON m.TxnID=s.TxnID WHERE TxnSubId='.$_GET['TxnSubId'];}
//      else { $sql0='SELECT TxnID,`Date` FROM `calllogs_2telmain` WHERE TLIDNo='.$tl.' AND `Date`=\''.$defaultdate.'\';'; }
//      $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
//
//if ($res0['Date']==date('Y-m-d') and (allowedToOpen(6641,'1rtc'))){ 
//            date_default_timezone_set('Asia/Manila'); $today=getdate(); 
//            if(time()<mktime(20, 0, 0, $today['mon'], $today['mday'], $today['year'])){ $editok=true; } else { $editok=false; }
//        }else { $editok=false; }
      
$columnstoadd=array('ClientName','ContactPerson','Position','ContactNumber','Notes','QuoteType','QuoteNo','InvoiceNo');
skipsql:
        
if(allowedToOpen(6643,'1rtc')){ include_once('telvisitcomment.php'); }
        
switch ($which){
   case 'List':
       if(isset($_POST['showall']) and ((!allowedToOpen(6641,'1rtc')))){ header('Location:tel.php?w=All&Date='.$defaultdate);}
       $title=''; $formdesc=''; $method='post';
       
       if (allowedToOpen(6641,'1rtc') or allowedToOpen(6642,'1rtc')){ 
           
         $columnnames=array(
                    array('field'=>'ClientName','caption'=>'Client','type'=>'text','size'=>10,'required'=>true, 'list'=>'clients'),
                    array('field'=>'ContactPerson','type'=>'text','size'=>10,'required'=>true),
		    array('field'=>'Position','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'ContactNumber','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Notes','type'=>'text','size'=>25,'required'=>true),
                    array('field'=>'QuoteType', 'caption'=>'Quote Type (0=verbal, 1=formal','type'=>'text','size'=>2,'required'=>false),
                    array('field'=>'QuoteNo','type'=>'text','size'=>5,'required'=>false),
                    array('field'=>'InvoiceNo','type'=>'text','size'=>5,'required'=>false));
       
                        
      $action='tel.php?w=Add&TL='.$tl; $fieldsinrow=4; $liststoshow=array();
      	 include('../backendphp/layout/inputmainform.php');
      } 
      
      if($editok){
      $editprocess='tel.php?w=EditSpecifics&TL='.$tl.'&TxnSubId='; $editprocesslabel='Edit';   
        $delprocess='tel.php?w=Delete&TxnSubId=';
        $columnstoedit=array('ClientName','ContactPerson','Position','ContactNumber','Notes','Quote_Type','QuoteNo','InvoiceNo');
      }
      $columnnames=$columnnameslist;
     $sql=$sql2.' WHERE s.TxnID='.$txnid;
     
     if(!empty($txnid)){
        $sql1='SELECT COUNT(TxnSubId) AS Calls, (SELECT COUNT(TxnSubId) FROM `calllogs_2telsub` WHERE QuoteType=1 AND TxnID='.$txnid.') AS Formal, (SELECT COUNT(TxnSubId) FROM `calllogs_2telsub` WHERE QuoteType=0 AND TxnID='.$txnid.') AS Verbal, (SELECT COUNT(TxnSubId) FROM `calllogs_2telsub` WHERE NOT ISNULL(InvoiceNo) AND InvoiceNo NOT LIKE "" AND TxnID='.$txnid.') AS WithInvoice FROM `calllogs_2telsub` WHERE TxnID='.$txnid;
        //if ($_SESSION['(ak0)']==1002){ echo $sql1;}
		$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        $stmt1=$link->query($sql1); $res1=$stmt1->fetch();
        $totalstext='<br>Total Calls: '.$res1['Calls'].'<br>Total Formal Quotations: '.$res1['Formal'].'<br>Total Verbal Quotes: '.$res1['Verbal'].'<br>Total Invoices: '.$res1['WithInvoice'].'<br>';
        
        }
      $txnidname='TxnSubId';
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        if (allowedToOpen(6641,'1rtc') or allowedToOpen(6642,'1rtc')){ 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql0='SELECT TxnID FROM `calllogs_2telmain` WHERE TLIDNo='.$tl.' AND `Date`=\''.$defaultdate.'\';';
       // if ($_SESSION['(ak0)']==1002){ echo $sql0;}
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        if($stmt0->rowCount()>0){ $txnid=$res0['TxnID'];} else {
            $sql1='INSERT INTO `calllogs_2telmain` (TLIDNo,`Date`,EncodedByNo,`TimeStamp`) SELECT '.$tl.', \''.$defaultdate.'\', '.$_SESSION['(ak0)'].', Now();';
            $stmt1=$link->prepare($sql1); $stmt1->execute();
            $sql0='SELECT TxnID FROM `calllogs_2telmain` WHERE TLIDNo='.$tl.' AND `Date`=\''.$defaultdate.'\';';
            $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
        }
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `calllogs_2telsub` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now(), TxnID='.$txnid; 
      //  if ($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if ($editok==true and (allowedToOpen(6641,'1rtc') or allowedToOpen(6642,'1rtc'))){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `calllogs_2telsub` WHERE TxnSubId='.$_GET['TxnSubId'].' AND TxnID IN (SELECT TxnID FROM `calllogs_2telmain` WHERE `Date`=CURDATE())';
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
             $columnstoedit=$columnstoadd; 
         $columnswithlists=array('ClientName');$listsname=array('ClientName'=>'clients');
         $editprocess='tel.php?w=Edit&TL='.$tl.'&TxnSubId='.$txnsubid; 
         } else { $columnstoedit=array();}
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        
	if ($editok==true and (allowedToOpen(6641,'1rtc') or allowedToOpen(6642,'1rtc'))){
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `calllogs_2telsub` SET ClientNo=\''.(comboBoxValue($link,'`1clients`','`ClientName`',$_REQUEST['ClientName'],'ClientNo')).'\', '.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() WHERE TxnSubId='.$_GET['TxnSubId'].' AND TxnID IN (SELECT TxnID FROM `calllogs_2telmain` WHERE DATE_ADD(`Date`, INTERVAL 7 DAY)>=CURDATE())';
      //  if ($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        $sql0='SELECT TxnID FROM `calllogs_2telsub` WHERE TxnSubId='.$_GET['TxnSubId']; $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
        header("Location:tel.php?w=List&TL=".$tl."&TxnID=".$txnid);
        break;
   case 'All':
       $title=''; $formdesc='';  $txnidname='TxnSubId';
       //if ($_SESSION['(ak0)']==1002){ echo $sql;}
       include('dailycalllogsdata.php');
       include('../backendphp/layout/displayastablewithsub.php');
       break;
}
 $link=null; $stmt=null;  
?>
</div> <!-- end section -->
</body></html>