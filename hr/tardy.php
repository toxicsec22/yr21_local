<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(688,'1rtc')) {   echo 'No permission'; exit;} 
$showbranches=false;
if (!isset($_REQUEST['print'])) { include_once('../switchboard/contents.php');} else {
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
}

 
// include_once('../backendphp/layout/regulartablestyle.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$columnnameslist=array('IDNo','FullName','Position','AssignedBranch','Date','MonthTardy','NumTardy','MinutesTardy','EncodedBy','TimeStamp','Posted');
$columnstoadd=array_diff($columnnameslist,array('IDNo','FullName','Position','AssignedBranch','EncodedBy','TimeStamp','Posted'));

$which=(!isset($_GET['w'])?'List':$_GET['w']);

if (!isset($_REQUEST['nofilter'])) {
$idfilter=(!isset($_REQUEST['idfilter'])?'':' AND mt.IDNo='.$_REQUEST['idfilter']);
$monthfilter=(!isset($_REQUEST['monthfilter'])?'':' AND `MonthTardy`='.$_REQUEST['monthfilter']);
$lettermonthfilter=(!isset($_REQUEST['lettermonthfilter'])?'':' AND MONTH(`Date`)='.$_REQUEST['lettermonthfilter']);
} else { $idfilter=''; $monthfilter=''; $lettermonthfilter='';}

if (in_array($which,array('List','EditSpecifics'))){
   echo comboBox($link,'SELECT IDNo, CONCAT(FirstName, " ", Surname) AS FullName FROM `1employees` ORDER BY FirstName;','IDNo','FullName','employees');
} 

if (in_array($which,array('Add','Edit'))){
   $idno=comboBoxValue($link,'`1employees`','CONCAT(FirstName, " ", Surname)',addslashes($_POST['FullName']),'IDNo');
   $sql0='Select PositionID, BranchNo FROM `attend_30currentpositions` WHERE IDNo='.$idno; $stmt=$link->query($sql0); $result=$stmt->fetch();
   $positionid=$result['PositionID']; $assignedbranchno=$result['BranchNo'];
        }
	
$sql='SELECT mt.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, Branch AS AssignedBranch, e.Nickname as EncodedBy, p.Position, IF(Posted=1,"Posted","") AS Posted FROM hr_4tardy mt   
	       JOIN `1employees` e ON e.IDNo=mt.EncodedByNo
	       LEFT JOIN `1employees` e1 ON e1.IDNo=mt.IDNo
               LEFT JOIN `attend_30currentpositions` cp ON cp.BranchNo=mt.AssignedBranchNo AND cp.IDNo=mt.IDNo
	       LEFT JOIN `attend_1positions` p ON p.PositionID=mt.PositionID ';

switch ($which){
   case 'List':
         $title='Memos on Tardiness'; $method='POST'; $formdesc='<br><br>Notes:</i>
             <div style="margin: 15px"><ol><li>Entries must be posted before it can be printed.</li>
             <li>One memo per person per month tardy. Auto-encode may not push through if this condition is not met.</li>
             <li>All unposted entries may be edited or deleted.</li>
             <li>Default filter of shown list is month of the date of NTE.</li></ol></div><br><br><hr><br>
        <form method="post" action="tardy.php?w=AutoEncode" enctype="multipart/form-data">
		Auto Encode Tardy NTEs within the Month (1 - 12):  <input type="text" name="Month" size=3 value="'.date('m').'"></input>
                    <input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /> 
		<input type="submit" name="autoencode" value="Auto Encode"> </form><i>';
         $columnnames=array(
                    array('field'=>'Date','caption'=>'Date','type'=>'date','size'=>6,'required'=>true, 'value'=>date('Y-m-d')),
                    array('field'=>'FullName','caption'=>'Memo Issued To','type'=>'text','size'=>20,'required'=>true, 'list'=>'employees'),
                    array('field'=>'MonthTardy','caption'=>'Month when tardy (1-12)','type'=>'text','size'=>3,'required'=>true),
                    array('field'=>'MinutesTardy','caption'=>'Total minutes tardy','type'=>'text','size'=>4,'required'=>true),
                    array('field'=>'NumTardy','caption'=>'Number of times','type'=>'text','size'=>4,'required'=>true)
		     );
      $action='tardy.php?w=Add';
      $liststoshow=array(); $fieldsinrow=6;
     include('../backendphp/layout/inputmainform.php'); unset($formdesc);
     ?><br><br>&nbsp; &nbsp;
     FILTER BY: &nbsp; &nbsp;  
     <form method="post" action="tardy.php?w=List" style="display:inline"><input type="submit" name="nofilter" size="5" value="Show All"></form>&nbsp; &nbsp; OR 
     &nbsp; &nbsp; &nbsp; <form method="get" action="tardy.php?w=List" style="display:inline">Filter by ID No:<input type="text" name="idfilter" size="5"></form>
     &nbsp; OR &nbsp; &nbsp;  <form method="get" action="tardy.php?w=List" style="display:inline">Filter by Month Tardy:<input type="text" name="monthfilter" size="3"></form>
     &nbsp; OR &nbsp; &nbsp;  <form method="get" action="tardy.php?w=List" style="display:inline">Filter by Month of Date of Letter:<input type="text" name="lettermonthfilter" size="3"></form>
     <br><br><hr> 

<?php
      $title=''; $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Date,FullName'); $columnsub=$columnnameslist;
        $sql=$sql.'WHERE 1=1 '.$lettermonthfilter.$idfilter.$monthfilter.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
        
        $delprocess='tardy.php?w=Delete&TxnID=';
        $editprocess='tardy.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit'; $txnidname='TxnID';
        $addlprocess='tardy.php?w=Post&TxnID='; if(allowedToOpen(6881,'1rtc')) { $addlprocesslabel='Post_Unpost'; } else { $addlprocesslabel='Post'; }
        $addlprocess2='tardy.php?w=Letter&print=1&TxnID='; $addlprocesslabel2='PrintPreview'; 
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'AutoEncode':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='INSERT INTO `hr_4tardy` (`Date`,`IDNo`,`PositionID`,`AssignedBranchNo`,`MonthTardy`,`NumTardy`,`MinutesTardy`,`EncodedByNo`,`TimeStamp`) '
                . 'SELECT CURDATE() AS `Date`,`IDNo`,`PositionID`,`BranchNo` AS `AssignedBranchNo`,'.$_POST['Month'].' AS `MonthTardy`,`LatesPerMonth` AS `NumTardy`,'
                . ' `TotalMinutesLate` AS `MinutesTardy`, '.$_SESSION['(ak0)'].' AS `EncodedByNo`, Now() AS `TimeStamp` FROM `attend_62latescount` WHERE `ForMonth`='.$_POST['Month'].' AND (`LatesPerMonth`>=5 OR `TotalMinutesLate`>=120) ;';

        if($_SESSION['(ak0)']==1002) {echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Add':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `hr_4tardy` SET '.$sql.'IDNo='.$idno.', PositionID='.$positionid.', AssignedBranchNo='.$assignedbranchno.', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';
        if($_SESSION['(ak0)']==1002) {echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `hr_4tardy` WHERE Posted=0 AND TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Post':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if(allowedToOpen(6881,'1rtc')) {
            $stmt0=$link->query('SELECT Posted FROM `hr_4tardy` WHERE TxnID='.$_GET['TxnID']); $res0=$stmt0->fetch();
            $posted=$res0['Posted'];
        $sql='UPDATE `hr_4tardy` SET Posted='.($posted==0?1:0).' WHERE TxnID='.$_GET['TxnID'];
        } else { $sql='UPDATE `hr_4tardy` SET Posted=1 WHERE TxnID='.$_GET['TxnID']; }
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
   case 'EditSpecifics':
         $title='Edit Specifics';
	 $txnid=intval($_GET['TxnID']); $main='hr_4tardy'; $columnstoedit=$columnstoadd;$columnstoedit=array('FullName')+$columnstoedit;
	 $sql=$sql.'WHERE Posted=0 AND TxnID='.$txnid; if($_SESSION['(ak0)']==1002) {echo $sql;}
        $columnnames=$columnnameslist;
	 $columnswithlists=array('FullName');$listsname=array('FullName'=>'employees');
	 $editprocess='tardy.php?w=Edit&TxnID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `hr_4tardy` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'IDNo='.$idno.', PositionID='.$positionid.', AssignedBranchNo='.$assignedbranchno.', TimeStamp=Now() WHERE Posted=0 AND TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:tardy.php");
        break;
   case 'Letter':       
       $title=''; $txnid=intval($_GET['TxnID']);
       $sql='SELECT mt.*, e1.Nickname, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, p.Position, CONCAT(e.FirstName, " ", e.Surname) AS EncodedBy, cp.Position AS EncodedbyPosition, CONCAT(e2.FirstName, " ", e2.Surname) AS DeptHead, p2.Position AS DeptHeadPosition 
FROM hr_4tardy mt JOIN `1employees` e1 ON e1.IDNo=mt.IDNo LEFT JOIN attend_1positions p ON p.PositionID=mt.PositionID
 JOIN `1employees` e ON e.IDNo=mt.EncodedByNo JOIN `attend_30currentpositions` cp on cp.IDNo=mt.EncodedByNo 
 JOIN `1departments` d ON d.deptid=p.deptid
 JOIN `attend_30currentpositions` cp2 ON cp2.PositionID=d.deptheadpositionid
 JOIN `1employees` e2 ON e2.IDNo=cp2.IDNo LEFT JOIN attend_1positions p2 ON p2.PositionID=cp2.PositionID
 WHERE Posted=1 AND TxnID='.$txnid; 
       $stmt=$link->query($sql); $res=$stmt->fetch();
       //if($_SESSION['(ak0)']==1002) {echo $sql;}
       
       $sqlco='SELECT c.CompanyNo, c.Company,c.CompanyName FROM `hr_4tardy` t JOIN `1employees` e ON e.IDNo=t.IDNo JOIN `1companies` c ON c.CompanyNo=e.RCompanyNo WHERE t.Posted=1 AND t.TxnID='.$txnid; $stmtco=$link->query($sqlco); $resultco=$stmtco->fetch();

 // echo $sqlco;
/* $letter='<br><br><title>'.$res['Nickname'].'</title><style>a:link { color: darkblue; text-decoration: none; }
</style>'
        . '<center><img  src="../generalinfo/logo/'.$resultco['Company'].'.png"></center><br><br><br>'
        . 'Date:'.str_repeat('&nbsp;',8).date('d F Y',strtotime($res['Date'])).'<br><br> To: <a href="javascript:window.print()">'.str_repeat('&nbsp;',10).$res['FullName'].'</a><br>'
        .str_repeat('&nbsp;',16).$res['Position'].'<br><br>'
        . 'From:'.str_repeat('&nbsp;',6).'Human Resources Department<br><br>Subject: &nbsp Notice To Explain<br><br><hr><br><br>'
        .'Dear '.$res['Nickname'].':<br><br>It has come to our attention that in the month of '.date('F',strtotime(''.$currentyr.'-'.$res['MonthTardy'].'-01')).', you have been tardy on <i>'.$res['NumTardy'].'</i> instances with a total of  <i>'.$res['MinutesTardy'].'</i> minutes of tardiness.<br><br>This is to advise you that this is a violation of our Code of Conduct Article II: Offenses against Code of
Conduct specifically on habitual tardiness: Habitual tardiness is defined as a total of 120 minutes late or five times late within one calendar month, whichever comes first. <br><br>Please give your explanation in writing within 48 hours from receipt of this notice.  Failure on your part to submit a written explanation within the given period shall constitute a waiver of your right to be
heard, and you will abide with the interpretation and action of management.'
        .'<br><br><br>Prepared by:<br><br><br><br>'.$res['EncodedBy'].'<br>'.$res['EncodedbyPosition']
        .'<br><br><br>Noted by:<br><br><br><br>'.$res['DeptHead'].'<br>'.$res['DeptHeadPosition']; */

// $letter='';
// <tr><td>DATE<br><br></td><td>:<br><br></td><td>'.date('F d, Y',strtotime($res['Date'])).'<br><br></td></tr>
$letter='<title>'.$res['Nickname'].'</title><style>a:link { color: darkblue; text-decoration: none; }
</style>'
        . '<center><img  src="../generalinfo/logo/'.$resultco['Company'].'.png"></center><hr>'
        . '<table><tr><td>TO</td><td style="width:30px;">:</td><td> <a href="javascript:window.print()">'.strtoupper($res['FullName']).'</a></td></tr>
<tr><td></td><td></td><td>'.$res['Position'].'<br><br></td></tr>
<tr><td>FROM<br><br></td><td>:<br><br></td><td>Human Resources Department<br><br></td></tr>
<tr><td>DATE<br><br></td><td>:<br><br></td><td>'.date('F d, Y').'<br><br></td></tr>
<tr><td>RE</td><td>:</td><td>NOTICE TO EXPLAIN</td></tr>
</table><hr><br>'
        .'Dear '.$res['Nickname'].':<br><br>You are requested to explain in writing within 120 hours (or 5 days) from
receipt of this letter why no disciplinary action must be imposed on you
for the violation of the Company\'s Code of Conduct on attendance.  Your
tardiness for the month of <b><u>'.date('F',strtotime(''.$currentyr.'-'.$res['MonthTardy'].'-01')).'</u></b> are detailed below:';

$sql='select a.DateToday,LEFT(a.TimeIn,5) AS TimeIn,CONCAT(Shift,":00") AS Sched,round(((time_to_sec(`a`.`TimeIn`) - time_to_sec(CONCAT(Shift,":00"))) / 60),0) AS `TotalMinutesLate` from ((`1employees` `e` join `attend_2attendance` `a` on(`e`.`IDNo` = `a`.`IDNo`)) join `attend_30currentpositions` `p` on(`e`.`IDNo` = `p`.`IDNo`)) where hour(`a`.`TimeIn`) <> 12 and hour(`a`.`TimeIn`) + minute(`a`.`TimeIn`) / 60 > if(`p`.`JobLevelID` >= 6,8.5,8) and `e`.`Resigned` = 0 and `p`.`JobLevelID` < 6 and `e`.`IDNo` not in (1010,1014) AND a.IDNo='.$res['IDNo'].' AND MONTH(`DateToday`)='.$res['MonthTardy'].' order by `e`.`Nickname`,`e`.`SurName`,month(`a`.`DateToday`);';
$stmt=$link->query($sql); $restable=$stmt->fetchAll();
		
		$letter.='<br><br><table width="100%" border="1px solid black" style="border-collapse:collapse;text-align:center;"><tr><th>DATE</th><th>SCHEDULE</th><th>ACTUAL TIME</th><th>NO. OF MINS. LATE</th></tr>';
		$totalmin=0;
		foreach($restable AS $rest){
			$letter.='<tr><td>'.$rest['DateToday'].'</td><td>'.$rest['Sched'].'</td><td>'.$rest['TimeIn'].'</td><td>'.$rest['TotalMinutesLate'].' mins</td></tr>';
			$totalmin=$totalmin+$rest['TotalMinutesLate'];
		}
		$letter.='<tr><td><b>TOTAL</b></td><td></td><td></td><td><b>'.$totalmin.' mins</b></td></tr>';
		$letter.='</table>';
		
$letter.='<br><br>Failure on your part to submit the said written explanation within the prescribed period shall be deemed a waiver on your part of the opportunity to be heard rendering the Management to decide based on the existing documents/facts.<br><br>In the event that you are found guilty of the said charge(s), the company may issue a suspension order and/or impose other appropriate penalties.<br><br>For your guidance and strict compliance.<br>';
		$letter.='<br><br><table width="100%"><tr><td><b>Prepared by:</b></td><td><b>Noted by:</b></td></tr><tr><td><br><br><br><br>'.$res['EncodedBy'].'<br>'.$res['EncodedbyPosition']
        .'</td><td><br><br><br><br>'.$res['DeptHead'].'<br>'.$res['DeptHeadPosition'].'</td></tr><tr><td><br><br><font style="font-size:11pt;">Employee\'s Signature<br><table><tr><td width="120px">Received</td><td width="10px">:</td><td>'.str_repeat('_',20).'</td></tr><tr><td>Date</td><td>:</td><td>'.str_repeat('_',20).'</td></tr></table>';
		
		
        // $letter.='<br><br><br>Prepared by:<br><br><br><br>'.$res['EncodedBy'].'<br>'.$res['EncodedbyPosition']
        // .'<br><br><br>Noted by:<br><br><br><br>'.$res['DeptHead'].'<br>'.$res['DeptHeadPosition'];
		
		
echo $letter; 


       break;
    
}
  $link=null; $stmt=null;
?>
</body></html>