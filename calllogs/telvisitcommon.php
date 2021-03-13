<?php
include_once('../switchboard/contents.php');


include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
?><br><div id="section" style="display: block;"><?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);
$defaultdate=(!isset($_REQUEST[$datefield])?date('Y-m-d'):$_REQUEST[$datefield]); 
$txndate=!isset($_GET[$datefield])?'`'.$datefield.'`=\''.$defaultdate.'\'':'`'.$datefield.'`=\''.$_GET[$datefield].'\'';
if (allowedToOpen(6641,'1rtc')){ $tl=$_SESSION['(ak0)']; }
else { 
    $tlname=!isset($_REQUEST['TeamLeader'])?'Choose team leader':$_REQUEST['TeamLeader'];
    if(isset($_REQUEST['TeamLeader'])){ 
        $tl=comboBoxValue($link,'`attend_30currentpositions`','FullName',(!isset($_REQUEST['TeamLeader'])?0:$_REQUEST['TeamLeader']),'IDNo');
        header("Location:".$file.".php?w=List&TL=".$tl.'&'.$datefield.'='.$defaultdate);
    } else {    $tl=(!isset($_REQUEST['TL'])?0:$_REQUEST['TL']);}
    
}
$tlname=comboBoxValue($link,'`attend_30currentpositions`','IDNo',$tl,'FullName');

$title='Sales '. ucfirst($file).' Log'; $formdesc='</i>Team Leader: '.$tlname.' ('.$tl.')'.'<br><i>Editable until 8 p.m. today<br>';
include_once('../backendphp/layout/clickontabletoedithead.php');
if (in_array($which,array('List','All'))){
?>
<form method="post" style="display:inline"
      action="<?php echo $file.'.php?w='.$which.'&TL='.$tl.'&'.$datefield.'='.(!isset($_REQUEST[$datefield])?$defaultdate:$_REQUEST[$datefield]); ?>" enctype="multipart/form-data">
                Choose <?php echo $datefield; ?>:  <input type="date" name="<?php echo $datefield; ?>" value="<?php echo $defaultdate; ?>" ></input> 
<?php
if (!allowedToOpen(6641,'1rtc')){ 
    ?>&nbsp; &nbsp; &nbsp;
<form method="get" style="display:inline"
      action="<?php echo $file.'.php?w='.$which.'&'.$datefield.'='.$defaultdate; ?>" enctype="multipart/form-data">
                Team Leader:  <input type="text" name="TeamLeader" value="<?php echo ($tl<>0?$tlname:""); ?>" list="teamleaders"></input> 
                   <?php
if (allowedToOpen(6642,'1rtc')){
        $tllistsql='SELECT IDNo, FullName FROM `attend_30currentpositions` WHERE PositionID=36 AND IDNo  IN (SELECT TeamLeader FROM attend_1branchgroups WHERE SAM='.$_SESSION['(ak0)'].');';
    } else { 
        $tllistsql='SELECT IDNo, FullName FROM `attend_30currentpositions` WHERE PositionID=36;';
    }
echo comboBox($link,$tllistsql,'IDNo','FullName','teamleaders');
}
?>
 <input type="submit" name="lookup" value="Lookup"> 
     <?php if (!allowedToOpen(6641,'1rtc')){  ?>&nbsp; &nbsp; &nbsp; <a href="<?php echo $file.'.php?w=All&'.$datefield.'='.$defaultdate; ?>">Show All</a><?php } ?>
</form>    
<?php
}

if (in_array($which,array('List','EditSpecifics'))){
    if(allowedToOpen(6641,'1rtc')){
   echo comboBox($link,'SELECT `c`.`ClientNo` AS `ClientNo`,
        LEFT(`c`.`ClientName`,20) AS `ClientName` FROM
        (`1clients` `c`
        JOIN `gen_info_1branchesclientsjxn` `bc` ON ((`c`.`ClientNo` = `bc`.`ClientNo`)))
        WHERE (`bc`.`BranchNo` IN (SELECT BranchNo FROM `attend_1branchgroups` WHERE `c`.`ClientNo`>10001 AND TeamLeader='.$tl.'))  OR c.ClientClass=1 GROUP BY c.ClientNo;',
    'ClientNo','ClientName','clients');
   echo comboBox($link,'SELECT 0 AS ReqByNo, "STL" AS Requested_By UNION SELECT 1,"Client";','ReqByNo','Requested_By','reqby');
   echo comboBox($link,'SELECT * FROM `calllogs_0visitpurpose`','VisitID','VisitPurpose','purpose');
    }
}


if($which=='All'){ goto skipsqlfilter;}
elseif(isset($_GET['TxnID'])){ $sql0='SELECT TxnID,`'.$datefield.'` FROM `calllogs_2'.$file.'main` WHERE TxnID='.$_GET['TxnID'];}
elseif(isset($_GET['TxnSubId'])){ $sql0='SELECT m.TxnID,`'.$datefield.'` FROM `calllogs_2'.$file.'main` m JOIN `calllogs_2'.$file.'sub` s ON m.TxnID=s.TxnID WHERE TxnSubId='.$_GET['TxnSubId'];}
      else { $sql0='SELECT TxnID,`'.$datefield.'` FROM `calllogs_2'.$file.'main` WHERE TLIDNo='.$tl.' AND `'.$datefield.'`=\''.$defaultdate.'\';'; }
     // if ($_SESSION['(ak0)']==1002){ echo $sql0;}
      $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID']; $recorddate=$res0[$datefield];

if ($recorddate==date('Y-m-d') and (allowedToOpen(6641,'1rtc'))){ 
            date_default_timezone_set('Asia/Manila'); 
            $today=getdate(); 
            if(time()<mktime(20, 0, 0, $today['mon'], $today['mday'], $today['year'])){ $editok=true; } else { $editok=false; }
        } else { $editok=false; }


skipsqlfilter:        


        
?>