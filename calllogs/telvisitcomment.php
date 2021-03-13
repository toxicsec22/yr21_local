<?php
if (in_array($which,array('List','All'))){
        $txnidname='TxnSubId';
        if((isset($recorddate) and ($recorddate>=date('Y-m-d',strtotime('2 days ago')))) 
                or ((isset($_GET[$datefield])) and ($_GET[$datefield]>=date('Y-m-d',strtotime('2 days ago'))))){
        $columnstoedit=array('SAMComment'); $editprocess=$file.'.php?w=Comment&edit=1&TxnSubId='; $editprocesslabel='Comment';}
        
    }
//echo $recorddate.' '.date('Y-m-d',strtotime('2 days ago'));
if (in_array($which,array('Comment'))){
        
       $title='Edit Comment';
	 $txnsubid=$_GET['TxnSubId']; 
      //   $sql0='SHOW COLUMNS FROM `2'.$file.'sub`'; $stmt0=$link->query($sql0); $res0=$stmt0->fetchAll();
	 $sql=$sql2.' WHERE TxnSubId='.$txnsubid;
      //   $columnnames=$res0['Field'];
         if($_GET['edit']==true){ 
             $columnstoedit=array('SAMComment');
         $editprocess=$file.'.php?w=EditComment&TxnSubId='.$txnsubid; 
         } else { $columnstoedit=array();}
         include('../backendphp/layout/editspecificsforlists.php');
    }

if (in_array($which,array('EditComment'))){
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
       if (!allowedToOpen(6643,'1rtc')){  echo 'No permission'; exit;}
       if(isset($recorddate) and ($recorddate>=date('Y-m-d',strtotime('2 days ago'))))  {
        $sql='UPDATE `calllogs_2'.$file.'sub` SET SAMComment=\''.addslashes($_POST['SAMComment']).'\', SAMByNo='.$_SESSION['(ak0)'].', SAMTS=Now() WHERE TxnSubId='.$_GET['TxnSubId'].' AND TxnID IN (SELECT TxnID FROM `calllogs_2'.$file.'main` WHERE DATE_ADD(`'.$datefield.'`, INTERVAL 2 DAY)>=CURDATE()) AND ((SAMByNo IS NULL) OR (SAMByNo='.$_SESSION['(ak0)'].'))';
        if ($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
       }
       $sql0='SELECT s.TxnID, TLIDNo FROM `calllogs_2'.$file.'sub` s JOIN `calllogs_2'.$file.'main` m ON m.TxnID=s.TxnID WHERE TxnSubId='.$_GET['TxnSubId']; $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $txnid=$res0['TxnID'];
       header('Location:'.$file.'.php?w=List&TL='.$res0['TLIDNo'].'&TxnID='.$txnid);
    //    header("Location:".$file.'.php?w=List&'.$datefield.'='.$defaultdate);
//        header("Location:".$_SERVER['HTTP_REFERER']);
    }

    $link=null; $stmt=null; $stmt0=null;
?>