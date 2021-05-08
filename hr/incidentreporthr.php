<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
if (!allowedToOpen(6505,'1rtc')) {   echo 'No permission'; exit;} 
 

$which=(!isset($_GET['w'])?'List':$_GET['w']);
if($which<>'Print'){include_once('../switchboard/contents.php');}
switch ($which){
   case 'List':
            ?>
        <html><head><title>Incident Reports</title></head>
        <body><h4>Incident Reports</h4><br><br>
        <?php
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        if (allowedToOpen(65051,'1rtc')) { $condition=' WHERE 1=1 ';}
        else { $condition=' JOIN `attend_30currentpositions` cp ON cr.ReIDNo=cp.IDNo WHERE deptheadpositionid='.$_SESSION['&pos'];}
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' `TimeStamp` ');

		
		$cases='(CASE 
			WHEN GravityofOffense=1 THEN "Ligth" 
			WHEN GravityofOffense=2 THEN "Moderate"
			WHEN GravityofOffense = 3 THEN "Major"
			WHEN GravityofOffense = 4 THEN "Serious"
			WHEN GravityofOffense = 5 THEN "Grave"
			ELSE "" 
			END)';
        $sql0='SELECT cr.*, DATEDIFF(CURDATE(),DATE(cr.`TimeStamp`)) AS AgeOfReportInDays, CONCAT(`e1`.`Nickname`, " - ",`e1`.`FirstName`," ",`e1`.`SurName`) AS ReportRegarding,'.$cases.' AS GravityofOffense, b.Branch, CONCAT(e.Nickname, " ",e.SurName) AS ReportedBy, HRNotes AS HR_Notes FROM `hr_3incidentreports` cr 
            LEFT JOIN `attend_1defaultbranchassign` eb ON cr.ReIDNo=eb.IDNo LEFT JOIN `1employees` e ON e.IDNo=cr.EncodedByNo '
                . ' LEFT JOIN `1employees` e1 ON e1.IDNo=cr.ReIDNo '
                . ' JOIN `1branches` `b` ON `eb`.`DefaultBranchAssignNo` = `b`.`BranchNo` '.$condition;
        $orderby=' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' DESC');
        $title='Pending Reports';
		$sqlcheckmax='SELECT MAX(DATEDIFF(CURDATE(),DATE(`TimeStamp`))) MaxAgeOfReportInDays,
			'.$cases.' AS GravityofOffense FROM hr_3incidentreports WHERE Resolved=0 GROUP BY GravityofOffense;';
		$stmtcheckmax=$link->query($sqlcheckmax);
		$datamaxall=$stmtcheckmax->fetchAll();
			$maxperoffense='Max Age Of Report In Days: <br>';
			foreach($datamaxall AS $datamax){
				$maxperoffense.='&nbsp; &nbsp; &nbsp; '.$datamax['GravityofOffense'].' - '.$datamax['MaxAgeOfReportInDays'].' <br>';
			}

		$formdesc='</i><div style="border:1px solid black;padding:4px;width:20%;background-color:#fff;">'.$maxperoffense.'</div><i>';
        $columnnames=array('ReIDNo', 'ReportRegarding', 'Branch', 'DateofIncident', 'TimeofIncident', 'Place', 'OtherPeople', 'Summary', 'ReportedBy', 'TimeStamp','AgeOfReportInDays','GravityofOffense','HR_Notes', 'HRNotes');
		
		//
		$sqlforselect = 'SELECT "Light" AS GravityofOffense,1 AS GOID UNION SELECT "Moderate" AS GravityofOffense,2 AS GOID UNION SELECT "Major" AS GravityofOffense,3 AS GOID UNION SELECT "Serious" AS GravityofOffense,4 AS GOID UNION SELECT "Grave" AS GravityofOffense,5 AS GOID';
		$stmtselect = $link->query($sqlforselect);
		$options='';

		while($rowselect = $stmtselect->fetch())
		{
			$options .= '<option value="'.$rowselect['GOID'].'">'.$rowselect['GOID'].' - '.$rowselect['GravityofOffense'].'</option>';
		}

        $columnstoedit=array('HRNotes'); $columnstoeditselect=array('GravityofOffense'); $columnsub=$columnnames;
        if (allowedToOpen(65051,'1rtc')) {
        $editprocess='incidentreporthr.php?w=Notes&TxnID='; $editprocesslabel='Enter'; $txnidname='TxnID';
        $addlprocess='incidentreporthr.php?w=SetAsResolved&Resolve=1&TxnID='; $addlprocesslabel='Resolved?';
			if (allowedToOpen(65053,'1rtc')){
				$delprocess='incidentreporthr.php?w=Delete&TxnID=';
			}
		}
        $sql=$sql0.'AND Resolved=0 '.$orderby;
         include('../backendphp/layout/displayastableeditcells.php');
         // include('../backendphp/layout/displayastableeditcellswithsorting.php');
        $sql=$sql0.'AND Resolved<>0 '.$orderby;
		$formdesc='';
        $title='<br><br>Resolved Reports'; 
        $columnnames=array_diff($columnnames,['HR_Notes','AgeOfReportInDays']);
        if (allowedToOpen(65052,'1rtc') OR in_array($_SESSION['(ak0)'],array(1741))) { 
            $addlprocess='incidentreporthr.php?w=SetAsResolved&Resolve=0&TxnID='; $addlprocesslabel='Unset_Resolved?';
            $addlprocess2='incidentreporthr.php?w=Print&TxnID='; $addlprocesslabel2='Print_Preview'; 
        }
        if (isset($editprocess)) {unset($editprocess);}
        include('../backendphp/layout/displayastable.php');
        break;
    case 'Notes':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$addlu='';
		// echo $_POST['GravityofOffense'];
		
		if(is_numeric($_POST['GravityofOffense'])){
			$addlu=',GravityofOffense='.$_POST['GravityofOffense'].'';
		}
        $sql='UPDATE `hr_3incidentreports` SET HRNotes=\''.addslashes($_POST['HRNotes']).'\''.$addlu.' WHERE TxnID='.$_REQUEST['TxnID']; 
		// echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:incidentreporthr.php");
        break;
    case 'SetAsResolved':
        if (!allowedToOpen(65052,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='UPDATE `hr_3incidentreports` SET Resolved='.$_GET['Resolve'].', ResolvedBy='.$_SESSION['(ak0)'].', ResolvedTS=Now() WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if (!allowedToOpen(65053,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `hr_3incidentreports` WHERE Resolved=0 '.((allowedToOpen(3000,'1rtc'))?'':'AND DATE(`TimeStamp`) > CURDATE() - INTERVAL 3 DAY').' AND TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'SetAsRead':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='UPDATE `hr_3incidentreports` SET ReadbyHR='.$_GET['ReadbyMgt'].' WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Print':
       $sql='SELECT CompanyName,Company,cr.*, CONCAT(`e1`.`FirstName`," ",`e1`.`SurName`, " (",`e1`.`Nickname`," - ", e1.IDNo,")") AS ReportRegarding, b.Branch, CONCAT(e.Nickname, " ",e.SurName) AS ReportedBy, HRNotes AS HR_Notes, CONCAT(e2.Nickname, " ",e2.SurName) AS `ResolvedBy` FROM `hr_3incidentreports` cr 
            LEFT JOIN `attend_1defaultbranchassign` eb ON cr.ReIDNo=eb.IDNo LEFT JOIN `1employees` e ON e.IDNo=cr.EncodedByNo '
                . ' LEFT JOIN `1employees` e1 ON e1.IDNo=cr.ReIDNo '
            . ' LEFT JOIN `1employees` e2 ON e2.IDNo=cr.ResolvedBy '
                . ' JOIN `1branches` `b` ON `eb`.`DefaultBranchAssignNo` = `b`.`BranchNo` '
           . ' JOIN `1companies` c ON c.CompanyNo=e1.RCompanyNo '
           . ' WHERE TxnID='.$_GET['TxnID'];
       $stmt0=$link->query($sql); $res=$stmt0->fetch();
       ?>
        <html><head><title>Print Incident Report</title>
                <style>
                    a:link { color: darkblue; text-decoration: none; }
                </style>
            </head>
            <body><center><img src="../generalinfo/logo/<?php echo$res['Company'];?>.png"></br></center><br><br><br>
            <h5>Incident Report Regarding: <a href="javascript:window.print()"><?php echo $res['ReportRegarding']; ?></a></h5>
            <br><br>Date of Incident: <?php echo $res['DateofIncident'].  str_repeat('&nbsp;', 10); ?>Time of Incident: <?php echo $res['TimeofIncident']; ?><br>
            <br>Place of Incident: <?php echo $res['Place']; ?><br><br>Other People Involved or Present: <?php echo $res['OtherPeople']; ?><br>
            <br><br>Report: <?php echo $res['Summary']; ?><br><br>
            <h5>Reported On System By: <?php echo $res['ReportedBy']; ?><br>Timestamp of Report: <?php echo $res['TimeStamp']; ?></h5><br>
            HR Notes:<br><br> <?php echo $res['HR_Notes']; ?><br><br><br><br>
            Set Resolved By (sign on name): <?php echo $res['ResolvedBy']; ?><br>
            Set Resolved On: <?php echo $res['ResolvedTS']; ?><br><br>
        <?php
       break;
}
  $link=null; $stmt=null;
?>
</body></html>