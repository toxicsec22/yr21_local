<?php
$path=$_SERVER['DOCUMENT_ROOT'];
if ($_POST['Date']>date('Y-m-d')){ require_once $path.'/acrossyrs/logincodes/confirmtoken.php';} else { goto noedit;}
 include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
$which=isset($_POST['delete'])?'Delete':'Add';
$date=$_POST['Date']; $details=$_POST['Details']; $tl=comboBoxValue($link,'`attend_30currentpositions`','FullName',$_POST['TeamLeader'],'IDNo');
switch ($which){
    case 'Delete':
        $sql='DELETE FROM `calllogs_2calendarevents` WHERE `Date`=\''.$date.'\' AND `IDNo`='.$tl.' AND `Details` LIKE "'.$details.'" AND `EncodedByNo`='.$_SESSION['(ak0)'];
        if ($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
        break;
    default:
        $sql='INSERT INTO `calllogs_2calendarevents`(`Date`,`IDNo`,`Details`,`EncodedByNo`,`TimeStamp`)
            VALUES(\''.$date.'\','.$tl.',"'.$details.'",'.$_SESSION['(ak0)'].',Now());';
        if ($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
        break;
}
noedit:
     $link=null; $stmt=null; 
    header("Location:".$_SERVER['HTTP_REFERER']);
?>