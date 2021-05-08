<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6503,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
if($which<>'Print'){include_once('../switchboard/contents.php');}

 

$which=(!isset($_GET['w'])?'List':$_GET['w']);

switch ($which){
   case 'List':
            ?>
        <html><head><title>Confidential Reports</title></head>
        <body><h4>Confidential Reports</h4><br><br>
        <?php
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' `TimeStamp` '); 
        $sql0='SELECT cr.*, CONCAT(`e1`.`Nickname`, " - ",`e1`.`FirstName`," ",`e1`.`SurName`) AS ReportRegarding, b.Branch, CONCAT(e.Nickname, " ",e.SurName) AS ReportedBy FROM `hr_3confireports` cr LEFT JOIN `attend_1defaultbranchassign` eb ON cr.ReIDNo=eb.IDNo LEFT JOIN `1employees` e ON e.IDNo=cr.EncodedByNo'
                . ' LEFT JOIN `1employees` e1 ON e1.IDNo=cr.ReIDNo '
                . ' JOIN `1branches` `b` ON `eb`.`DefaultBranchAssignNo` = `b`.`BranchNo`';
        $orderby=' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' DESC');
        $title='Pending Reports';
        $columnnames=array('ReIDNo', 'ReportRegarding', 'Branch', 'Report', 'ReportedBy', 'TimeStamp', 'MgtNotes');
        $columnstoedit=array('MgtNotes'); $columnsub=$columnnames;
        $editprocess='confireportjye.php?w=Notes&TxnID='; $editprocesslabel='Enter'; $txnidname='TxnID';
        $addlprocess='confireportjye.php?w=SetAsResolved&Resolve=1&TxnID='; $addlprocesslabel='Resolved?';
        $sql=$sql0.'WHERE Resolved=0 '.$orderby;
         include('../backendphp/layout/displayastableeditcells.php');
        $sql=$sql0.'WHERE Resolved<>0 '.$orderby;
        $title='<br><br>Resolved Reports'; 
        $addlprocess='confireportjye.php?w=SetAsResolved&Resolve=0&TxnID='; $addlprocesslabel='Unset_Resolved?'; 
        $addlprocess2='confireportjye.php?w=Print&TxnID='; $addlprocesslabel2='Print_Preview'; 
        if (isset($editprocess)) {unset($editprocess);}
        include('../backendphp/layout/displayastable.php');
        break;
    case 'Notes':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='UPDATE `hr_3confireports` SET MgtNotes=\''.addslashes($_POST['MgtNotes']).'\' WHERE TxnID='.$_REQUEST['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:confireportjye.php");
        break;
    case 'SetAsResolved':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='UPDATE `hr_3confireports` SET Resolved='.$_GET['Resolve'].', ResolvedBy='.$_SESSION['(ak0)'].', ResolvedTS=Now() WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'SetAsRead':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if($_REQUEST['ReportType']==1){ $sql='UPDATE `hr_3confireports` SET ReadbyMgt='.$_GET['ReadbyMgt'].' WHERE TxnID='.$_GET['TxnID']; }
        else { $sql='UPDATE `hr_3incidentreports` SET ReadbyHR='.$_GET['ReadbyMgt'].' WHERE TxnID='.$_GET['TxnID']; }
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
   case 'Print':
       $sql='SELECT CompanyName,Company,cr.*, CONCAT(`e1`.`FirstName`," ",`e1`.`SurName`, " (",`e1`.`Nickname`," - ", e1.IDNo,")") AS ReportRegarding, b.Branch, CONCAT(e.Nickname, " ",e.SurName) AS ReportedBy, CONCAT(e2.Nickname, " ",e2.SurName) AS `ResolvedBy` FROM `hr_3confireports` cr LEFT JOIN `attend_1defaultbranchassign` eb ON cr.ReIDNo=eb.IDNo LEFT JOIN `1employees` e ON e.IDNo=cr.EncodedByNo'
                . ' LEFT JOIN `1employees` e1 ON e1.IDNo=cr.ReIDNo '
                . ' LEFT JOIN `1employees` e2 ON e2.IDNo=cr.ResolvedBy '
                . ' JOIN `1branches` `b` ON `eb`.`DefaultBranchAssignNo` = `b`.`BranchNo` '
           . ' JOIN `1companies` c ON c.CompanyNo=e1.RCompanyNo '
           . ' WHERE TxnID='.$_GET['TxnID'];
       $stmt0=$link->query($sql); $res=$stmt0->fetch();
       ?>
        <html><head><title>Print Confidential Report</title>
                <style>
                    a:link { color: darkblue; text-decoration: none; }
                </style>
            </head>
            <body><center><img src="../generalinfo/logo/<?php echo$res['Company'];?>.png"></br></center><br><br><br><h5>Confidential Report Regarding: <a href="javascript:window.print()"><?php echo $res['ReportRegarding']; ?></a></h5>
            <br><br>Report: <?php echo $res['Report']; ?><br><br>
            <h5>Reported On System By: <?php echo $res['ReportedBy']; ?><br>Timestamp of Report: <?php echo $res['TimeStamp']; ?></h5><br>
            Management Notes:<br><br> <?php echo $res['MgtNotes']; ?><br><br><br><br>
            Set Resolved By (sign on name): <?php echo $res['ResolvedBy']; ?><br>
            Set Resolved On: <?php echo $res['ResolvedTS']; ?><br><br>
        <?php
       break;
}
  $link=null; $stmt=null;
?>
</body></html>