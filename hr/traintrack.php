<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(690,'1rtc')) {   echo 'No permission'; exit;} 
$showbranches=false;
include_once('../switchboard/contents.php');

 
include_once('../backendphp/layout/regulartablestyle.php');
?><br><div id="section" style="display: block;"><?php

$which=(!isset($_GET['which'])?'List':$_GET['which']);
$month=(!isset($_REQUEST['Month'])?date('m'):$_REQUEST['Month']);

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$columnnamesmain=array('TrainingTitle','Venue','StartDate','EndDate','Trainor','TrainorTitle','Objectives','OtherNotes','TrainingLead','TrainingLeadPosition','FeedbackAfter','Posted');//,'EncodedByNo','TimeStamp');
$columnstoaddmain=array_diff($columnnamesmain,array('TrainingTitle','TrainingLead','TrainingLeadPosition','Posted'));
$columnsub=array('Trainee','Position','Completed?','Comments');
$columnstoaddsub=array_diff($columnsub,array('Trainee','Position','Completed?'));

if (in_array($which,array('List','EditFormMain'))){
   echo comboBox($link,'SELECT TrainingID, TrainingTitle FROM `hr_1trainings` ORDER BY TrainingTitle;','TrainingID','TrainingTitle','trainings');
   echo comboBox($link,'SELECT IDNo, CONCAT(FirstName, " ", Surname) AS Lead FROM `1employees` ORDER BY FirstName;','IDNo','Lead','employees');
}

if (in_array($which,array('List','Training','EditFormSub','HistoryPerPerson'))){
   echo comboBox($link,'SELECT IDNo, CONCAT(FirstName, " ", Surname) AS Trainee FROM `1employees` ORDER BY FirstName;','IDNo','Trainee','trainees');
   echo comboBox($link,'SELECT 0 AS Completed, "No" AS `Completed?` UNION SELECT 1, "Yes" ORDER BY Completed;','Completed','Completed?','yesno');
}

if (isset($_GET['TxnID'])){
   $txnid=intval($_GET['TxnID']); 
   $sqlmain='Select m.*, e.Nickname AS EncodedBy, TrainingTitle, CONCAT(e1.FirstName, " ", e1.Surname) AS TrainingLead, Position AS TrainingLeadPosition 
       FROM `hr_2trainsched` m 
	       JOIN `1employees` e ON e.IDNo=m.EncodedByNo 
	       JOIN `hr_1trainings` t ON t.TrainingID=m.TrainingID 
               LEFT JOIN `1employees` e1 ON e1.IDNo=m.LeadIDNo
               LEFT JOIN `attend_1positions` p ON p.PositionID=m.LeadPositionID
                WHERE m.TxnID='.$txnid;
   $sqlsub='Select s.*, CONCAT(e1.FirstName, " ", e1.Surname) AS Trainee, Position, e.Nickname AS EncodedBy, IF(s.Completed=1,"Yes","No") AS `Completed?`
	       FROM `hr_2trainsched` m JOIN `hr_2traintrack` s ON m.TxnID=s.TxnID 
	       JOIN `1employees` e ON e.IDNo=s.EncodedByNo
	       JOIN `1employees` e1 ON e1.IDNo=s.IDNo
	       JOIN `attend_1positions` p ON p.PositionID=s.PositionID
	       WHERE m.TxnID='.$txnid;
}


switch ($which){
   case 'List':
		?><form method="post" action="traintrack.php" enctype="multipart/form-data">
		Choose Month (1 - 12):  <input type="text" name="Month" value="<?php echo date('m'); ?>"></input>
		<input type="submit" name="lookup" value="Lookup"> </form> <br><br> <a href='certificates.php'>See sample certificate</a>
		<?php
         $title='Trainings'; $method='POST'; 
         $columnnames=array(
                    array('field'=>'TrainingTitle', 'type'=>'text','size'=>15,'required'=>true, 'list'=>'trainings'),
                    array('field'=>'Venue','type'=>'text','size'=>15,'required'=>true),
		    array('field'=>'Trainor','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'TrainorTitle','type'=>'text','size'=>10,'required'=>true),
		    array('field'=>'StartDate','type'=>'date','size'=>5,'required'=>true),
		    array('field'=>'EndDate','type'=>'date','size'=>5,'required'=>true),
		    array('field'=>'Objectives','rows'=>'4','cols'=>'50','type'=>'textarea','formid'=>'visit','required'=>true),
		    array('field'=>'OtherNotes','rows'=>'4','cols'=>'50','type'=>'textarea','formid'=>'visit','required'=>false),
                    array('field'=>'TrainingLead', 'type'=>'text','size'=>10,'required'=>true,'autofocus'=>true, 'list'=>'trainees'));
                     
      $action='traintrack.php?which=Add'; $fieldsinrow=3; $liststoshow=array();
      include('../backendphp/layout/inputmainform.php');
      
      $title='';
	  $columnnames=$columnnamesmain;
      $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' StartDate,TrainingTitle'); $columnsub=$columnnames;
      $sql='SELECT m.*, count(sub.TxnID) AS Attendees, TrainingTitle, CONCAT(FirstName, " ", Surname) AS TrainingLead, Position AS TrainingLeadPosition  FROM hr_2trainsched m 
		JOIN hr_2traintrack sub on m.TxnID=sub.TxnID JOIN `hr_1trainings` t ON t.TrainingID=m.TrainingID
                 LEFT JOIN `1employees` e ON e.IDNo=m.LeadIDNo
                    LEFT JOIN `attend_1positions` p ON p.PositionID=m.LeadPositionID
        WHERE (Month(m.StartDate)='.$month.' OR Month(m.EndDate)='.$month.') GROUP BY TxnID 
	UNION  SELECT m.*, 0 as Attendees, TrainingTitle, CONCAT(FirstName, " ", Surname) AS TrainingLead, Position AS TrainingLeadPosition
	FROM hr_2trainsched m left JOIN hr_2traintrack sub on m.TxnID=sub.TxnID
	JOIN `hr_1trainings` t ON t.TrainingID=m.TrainingID 
        LEFT JOIN `1employees` e ON e.IDNo=m.LeadIDNo
                    LEFT JOIN `attend_1positions` p ON p.PositionID=m.LeadPositionID
        WHERE (Month(m.StartDate)='.$month.' OR Month(m.EndDate)='.$month.') AND sub.TxnID IS NULL	
	';   
        $editprocess='traintrack.php?which=Training&TxnID='; $editprocesslabel='Lookup'; $txnidname='TxnID';
	
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$trainid=comboBoxValue($link,'`hr_1trainings`','TrainingTitle',addslashes($_POST['TrainingTitle']),'TrainingID');
        $leadidno=comboBoxValue($link,'`1employees`','CONCAT(FirstName, " ", Surname)',addslashes($_POST['TrainingLead']),'IDNo');
	$leadpositionid=comboBoxValue($link,'`attend_30currentpositions`','IDNo',$leadidno,'PositionID');
        $sql='';
        foreach ($columnstoaddmain as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `hr_2trainsched` SET TrainingID='.$trainid.', EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'LeadIDNo='.$leadidno.', LeadPositionID='.$leadpositionid.', TimeStamp=Now()'; if($_SESSION['(ak0)']==1002){ echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
	$sql='SELECT TxnID FROM hr_2trainsched WHERE TrainingID='.$trainid.' AND StartDate=\''.$_POST['StartDate'].'\' AND EndDate=\''.$_POST['EndDate'].'\'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
        header("Location:traintrack.php?which=Training&TxnID=".$result['TxnID']);
        break;    
    
   case 'Training':
	 $title='Training';
	 $txnid=intval($_GET['TxnID']); $main='hr_2trainsched'; $table=$main; $txnidname='TxnID';
	 $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.Trainee');
	 $columnstoeditmain=$columnstoaddmain;
	 $columnnames=array(
                    array('field'=>'Trainee', 'type'=>'text','size'=>10,'required'=>true,'autofocus'=>true, 'list'=>'trainees'),
                    array('field'=>'Completed?', 'type'=>'text','size'=>2, 'required'=>true, 'list'=>'yesno', 'value'=>'No'),
		    array('field'=>'Comments', 'type'=>'text','size'=>20, 'required'=>false),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
	 $columnstoeditsub=$columnstoaddsub; $fieldsinrow=5;
	 $editprocess='traintrack.php?which=EditFormMain&TxnID='.$txnid; $delprocess='traintrack.php?which=DeleteMain&TxnID='.$txnid;
	 $addsub='traintrack.php?which=AddSub&TxnID='.$txnid; $fieldsinrowsub=12;
	 $editprocesssub='traintrack.php?which=EditFormSub&TxnID='.$txnid.'&TxnSubId='; $delprocesssub='traintrack.php?which=DeleteSub&TxnID='.$txnid.'&TxnSubId=';
	 $postedprocess='certificates.php?TxnID='.$txnid;$postedprocesslabel='Print Certificates';
	 $addlprocesssub='traintrack.php?which=SetCompleted&TxnID='.$txnid.'&TxnSubId='; $addlprocesssublabel='Completed?';
	 $dbname=$currentyr .'_1rtc'; //used??
	 $sqltotal='SELECT CONCAT(COUNT(IDNo)," Attendee/s") AS Total FROM `hr_2traintrack` WHERE TxnID='.$txnid;
	 include('../backendphp/layout/addeditform.php');
	 break;
      
   case 'DeleteMain':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `hr_2trainsched` WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:traintrack.php");
        break;
   
   case 'EditFormMain':
	 $title='Edit Training';
	 $txnid=intval($_GET['TxnID']); $main='hr_2trainsched';
	 $sql=$sqlmain;
	 $columnnames=$columnnamesmain; $columnstoedit=array_diff($columnnamesmain,array('TrainingLeadPosition','Posted'));;
	 $columnswithlists=array('TrainingTitle','TrainingLead'); $listsname=array('TrainingTitle'=>'trainings','TrainingLead'=>'employees');
	 $editprocess='traintrack.php?which=EditMain&TxnID='.$txnid; 
	 include('../backendphp/layout/editspecificsforlists.php');
	 break;
      
   case 'EditMain':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $trainid=comboBoxValue($link,'`hr_1trainings`','TrainingTitle',addslashes($_POST['TrainingTitle']),'TrainingID');
        $leadidno=comboBoxValue($link,'`1employees`','CONCAT(FirstName, " ", Surname)',addslashes($_POST['TrainingLead']),'IDNo');
	$leadpositionid=comboBoxValue($link,'`attend_30currentpositions`','IDNo',$leadidno,'PositionID');
	$sql='';
        foreach ($columnstoaddmain as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `hr_2trainsched` SET TrainingID='.$trainid.', LeadIDNo='.$leadidno.', LeadPositionID='.$leadpositionid.', EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:traintrack.php?which=Training&TxnID=".$_GET['TxnID']);
        break;
   
   case 'AddSub':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $idno=comboBoxValue($link,'`1employees`','CONCAT(FirstName, " ", Surname)',addslashes($_POST['Trainee']),'IDNo');
        $positionid=comboBoxValue($link,'`attend_30currentpositions`','IDNo',$idno,'PositionID');
        $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `hr_2traintrack` SET TxnID='.$_GET['TxnID'].', EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'IDNo='.$idno.', PositionID='.$positionid.', Completed='.(strtolower($_POST['Completed?'])=="yes"?1:0).', TimeStamp=Now()';
	$stmt=$link->prepare($sql); $stmt->execute();
        header("Location:traintrack.php?which=Training&TxnID=".$_GET['TxnID']);
        break;
      
   case 'DeleteSub':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `hr_2traintrack` WHERE TxnSubId='.$_REQUEST['TxnSubId'];
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:traintrack.php?which=Training&TxnID=".$_REQUEST['TxnID']);
        break;
   
   case 'EditFormSub':
	 $title='Edit Trainees';
	 $txnid=intval($_GET['TxnID']); $txnsubid=$_GET['TxnSubId']; $main='hr_2trainsched';
	 $sql=$sqlsub.' AND TxnSubId='.$txnsubid; $columnnames=$columnsub; $columnstoedit=array_diff($columnsub,array('Position'));;
	 $columnswithlists=array('Trainee','Position','Completed?');$listsname=array('Trainee'=>'trainees','Position'=>'positions','Completed?'=>'yesno');
	 $editprocess='traintrack.php?which=EditSub&TxnID='.$txnid.'&TxnSubId='.$txnsubid;
	 include('../backendphp/layout/editspecificsforlists.php');
	 break;
      
   case 'EditSub':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $idno=comboBoxValue($link,'`1employees`','CONCAT(FirstName, " ", Surname)',addslashes($_POST['Trainee']),'IDNo');
	$positionid=comboBoxValue($link,'`attend_30currentpositions`','IDNo',$idno,'PositionID');
        $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='UPDATE `hr_2traintrack` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' IDNo='.$idno.', PositionID='.$positionid.', Completed='.(strtolower($_POST['Completed?'])=="yes"?1:0).', TimeStamp=Now() WHERE TxnSubId='.$_GET['TxnSubId']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:traintrack.php?which=Training&TxnID=".$_GET['TxnID']);
        break;
      
   case 'SetCompleted':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnsubid=$_GET['TxnSubId'];
	$sql0='SELECT Completed FROM `hr_2traintrack` WHERE TxnSubId='.$txnsubid; $stmt=$link->query($sql0); $result=$stmt->fetch();
        $sql='UPDATE `hr_2traintrack` SET Completed='.($result['Completed']==1?0:1).' WHERE TxnSubId='.$_REQUEST['TxnSubId'];
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:traintrack.php?which=Training&TxnID=".$_REQUEST['TxnID']);
        break;
      
   case 'HistoryPerPerson':
       if (!allowedToOpen(691,'1rtc')) {   echo 'No permission'; exit;} 
      ?><form method="post" action="traintrack.php?which=HistoryPerPerson" enctype="multipart/form-data">
	 Trainee:  <input type="text" name="Trainee" list='trainees'></input><input type="submit" name="lookup" value="Lookup"> </form>
      <?php
      $title='Training History';
      if (!isset($_POST['Trainee'])){ goto noform;}
        $title=$title.' - '.$_POST['Trainee'];
        $idno=comboBoxValue($link,'`1employees`','CONCAT(FirstName, " ", Surname)',addslashes($_POST['Trainee']),'IDNo');
        $sql='SELECT StartDate,EndDate,TrainingTitle,Trainor,TrainorTitle,Venue,IF(Completed=1,"Yes","No") AS `Completed?`,Comments, Position
	FROM `hr_2traintrack` ts JOIN `hr_2trainsched` tm ON tm.TxnID=ts.TxnID JOIN `hr_1trainings` t ON t.TrainingID=tm.TrainingID
	 JOIN attend_1positions p ON p.PositionID=ts.PositionID WHERE IDNo='.$idno;
        $columnnames=array('StartDate','EndDate','TrainingTitle','Trainor','TrainorTitle','Venue','Completed?','Comments','Position');
        include('../backendphp/layout/displayastable.php');  
	noform:
        break;
		
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body></html>