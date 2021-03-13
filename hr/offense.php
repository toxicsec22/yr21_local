<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(682,'1rtc')) {   echo 'No permission'; exit;} 
if (!isset($_REQUEST['print'])) { include_once('../switchboard/contents.php');}

 
include_once('../backendphp/layout/regulartablestyle.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$columnnameslist=array('IDNo','FullName','Position','AssignedBranch','DateNTE','NTECOCOffenseType','PotentialViolation','DateofIncident','FirstInfo','OtherInfoLines','NTESanction','WithSuspension?','NTEDateofSuspension','Posted?','EncodedBy','TimeStamp','Result');
$columnstoadd=array_diff($columnnameslist,array('IDNo','FullName','Position','AssignedBranch','OtherInfoLines','WithSuspension?','NTEDateofSuspension','Posted?','EncodedBy','TimeStamp','Result'));

$which=(!isset($_GET['w'])?'List':$_GET['w']);

if (!isset($_REQUEST['nofilter'])) {
$idfilter=(!isset($_REQUEST['idfilter'])?'':' AND mt.IDNo='.$_REQUEST['idfilter']);
$lettermonthfilter=(!isset($_REQUEST['lettermonthfilter'])?'':' AND MONTH(`DateNTE`)='.$_REQUEST['lettermonthfilter']);
} else { $idfilter=''; $lettermonthfilter='';}

if (in_array($which,array('New','EditSpecifics'))){
   echo comboBox($link,'SELECT IDNo, CONCAT(FirstName, " ", Surname) AS FullName FROM `1employees` WHERE Resigned=0 ORDER BY FirstName;','IDNo','FullName','employees');
} 

if (in_array($which,array('Add','Edit'))){
   $idno=comboBoxValue($link,'`1employees`','CONCAT(FirstName, " ", Surname)',addslashes($_POST['FullName']),'IDNo');
   $sql0='Select PositionID, BranchNo FROM `attend_30currentpositions` WHERE IDNo='.$idno; $stmt=$link->query($sql0); $result=$stmt->fetch();
   $positionid=$result['PositionID']; $assignedbranchno=$result['BranchNo'];
   $withsuspension=(($_POST['WithSuspension?']=='No' or $_POST['WithSuspension?']==0)?0:1);
   $ntedateofsuspension=(($_POST['WithSuspension?']<>'No' and $_POST['WithSuspension?']<>0)?', `NTEDateofSuspension`=\''.$_POST['NTEDateofSuspension'].'\'':', `NTEDateofSuspension`=null ');
        }
        
if (in_array($which,array('AddSub','EditSub','PrEditSub'))) { $columnstoaddsub=array('OrderOfInfo','OtherInfo'); }
	
$sqlfull='SELECT mt.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, e1.Nickname, Branch AS AssignedBranch, e.Nickname as EncodedBy, p.Position, IF(Posted=1,"Posted","") AS `Posted?`, (SELECT COUNT(TxnID) FROM hr_4offensesub WHERE TxnID=mt.TxnID) AS OtherInfoLines, IFNULL(`ResultDesc`,"") AS `Result`, IFNULL(e2.Nickname,"") AS ResultBy, ResultTS, cp.deptheadpositionid FROM hr_4offensemain mt 
	       JOIN `1employees` e ON e.IDNo=mt.EncodedByNo
	       LEFT JOIN `1employees` e1 ON e1.IDNo=mt.IDNo
               LEFT JOIN `attend_30currentpositions` cp ON cp.BranchNo=mt.AssignedBranchNo AND cp.IDNo=mt.IDNo
	       LEFT JOIN attend_0positions p ON p.PositionID=mt.PositionID '
        . ' LEFT JOIN `hr_0offenseresult` ofr ON ofr.ResultID=mt.ResultID LEFT JOIN `1employees` e2 ON e2.IDNo=mt.ResultByNo ';

if (in_array($which,array('LookupNTE','Letter'))){
       $txnid=intval($_REQUEST['TxnID']);
       $sqlmain=$sqlfull.' WHERE mt.TxnID='.$txnid; $stmt=$link->query($sqlmain); $result=$stmt->fetch();
       $sqlsub='SELECT * FROM `hr_4offensesub` WHERE TxnID='.$txnid.' ORDER BY OrderOfInfo'; $stmtsub=$link->query($sqlsub); $ressub=$stmtsub->fetchAll();
       $sqlby='SELECT `Position`, e.`IDNo`, CONCAT(`FirstName`," ",LEFT(`MiddleName`,1),". ",`Surname`) AS `Name` FROM `attend_30currentpositions` p JOIN `1employees` e ON e.IDNo=p.IDNo WHERE e.`IDNo`='.$result['EncodedByNo']; $stmtby=$link->query($sqlby); $by=$stmtby->fetch();
       $sqldepthead='SELECT p.`Position`, CONCAT(`FirstName`," ",LEFT(`MiddleName`,1),". ",`Surname`) AS `Name` FROM `attend_30currentpositions` p JOIN `1employees` e ON e.IDNo=p.IDNo WHERE p.PositionID='.$result['deptheadpositionid']; $stmtby=$link->query($sqldepthead); $depthead=$stmtby->fetch();
       $title=$result['Nickname'].' '.$result['DateNTE'];
       if ($result['Posted']==0 and $which=='LookupNTE') {
           $editmain='&nbsp; &nbsp;<a href="offense.php?w=EditSpecifics&TxnID='.$txnid.'"><i>Edit Main Letter</i></a>';
       } else {
           $editmain='';
       }
       $letter='Date: <b>'.date('d F Y',strtotime($result['DateNTE'])).'</b>'.$editmain.'<br><br>'
       .'To:  <b>'.(($result['Posted']<>0 and $which=='Letter')?'<a href="javascript:window.print()">'.$result['FullName'].'</a>':$result['FullName']).'</b><br>'
       .'Position:  <b>'.$result['Position'].', '.$result['AssignedBranch'].'</b><br><br>'
       .'From: Human Resources Department<br><br>'
       .'Subject: Notice to Explain'.($result['WithSuspension?']==1?'and Notice of Preventive Suspension':'').'<br><br><hr><br>'
       .'Dear <b>'.$result['Nickname'].'</b>:<br><br>'
       .'Please be informed that you are currently under investigation for a potential violation of the Company Code of Conduct '.$result['NTECOCOffenseType']
       .', specifically on: '.$result['PotentialViolation'].'<br><br>'
       .'Following the initial investigation, it has come to our attention that:<br><br>'
       .'<ul style="margin-left: 50px;"><li>'.$result['FirstInfo'].($result['Posted']==0?'&nbsp; &nbsp; <i>(To change, edit first info of main letter.)</i>':'').'</li>';
       $list='';
       foreach($ressub as $info){ $list=$list.'<li>'.$info['OtherInfo']; 
                if ($result['Posted']==0) { $list=$list.'&nbsp; &nbsp; <a href=offense.php?w=EditSub&TxnID='.$result['TxnID'].'&TxnSubId='.$info['TxnSubId'].'>Edit</a>'
                        . '&nbsp; &nbsp; <a href=offense.php?w=DelSub&TxnID='.$result['TxnID'].'&TxnSubId='.$info['TxnSubId']
                        .'&action_token='.html_escape($_SESSION['action_token']).' OnClick=\'return confirm("Really delete this?")\'>Delete</a>'; }
                $list=$list.'</li>';
                }
       if ($result['Posted']==0) { 
           $sqlsuborder='SELECT IF(ISNULL(MAX(OrderOfInfo)),2,(MAX(OrderOfInfo)+1)) AS NextNum FROM `hr_4offensesub` WHERE TxnID='.$txnid; 
            $stmtsuborder=$link->query($sqlsuborder); $ressuborder=$stmtsuborder->fetch();
           $list=$list.'<br><form action="offense.php?w=AddSub" method="post">
               Add:  Number (for order only) <input type="text" size="3" name="OrderOfInfo" value='.$ressuborder['NextNum']
               .'>&nbsp; &nbsp; Info on Investigation <input type="text" size="15" name="OtherInfo">'
               .'<input type="hidden" name="TxnID" value='.$txnid.'>'
               .'<input type="hidden" name="action_token" value='.html_escape($_SESSION['action_token'])
                   .' /> &nbsp; &nbsp; <input type="submit" name="add" value="Add Info"></form>';
        } 
        
        $list=$list.'</ul>  <br><br>';
       $letter.=$list.'Please give your explanation in writing <i>within 5 calendar days</i> from receipt of this notice.  Failure on your part to submit a written explanation within the given period shall constitute a waiver of your right to be heard, and you will abide with the interpretation and action of management.<br><br>';
       $letter.=($result['WithSuspension?']==1?'Due to the severity and seriousness of this alleged breach, you will be placed under <i><b>preventive suspension</b></i> beginning <u><b>'.(date('F d, Y',strtotime($result['NTEDateofSuspension']))).'</b></u>, up to maximum of 30 days or until we come up with the conclusion regarding your case. We will be collecting all the company issued properties and you will be notified regarding your scheduled investigation hearing.<br><br>':'');

       $letter.='Please note that the alleged breach is serious and may carry a maximum corrective action of '.$result['NTESanction'].'. Rest assured that you will be given all the opportunity to explain your side. Formal hearings may be conducted, if needed, and notice thereof shall be given to you in advance to afford you the full extent of due process, including the right to representation, if so desired.<br><br><br>Prepared by:<br><br><br><br>';
       
       $letter.=$by['Name'].'<br>'.$by['Position'].'<br><br>'.'Noted by:'.str_repeat('<br>', 4).$depthead['Name'].'<br>'.$depthead['Position'];      
       
}

switch ($which){
   case 'List':
         $title='Offense NTEs'; $method='POST'; $formdesc='</i><br><br><a href="offense.php?w=New">Add NEW NTE</a><br><i>';
         $formdesc.='<br><br>Notes:</i>
             <div style="margin: 15px"><ol><li>Entries must be posted before it can be printed.</li>
             <li>All unposted entries may be edited or deleted.</li>
             <li>Default filter of shown list is month of the date of NTE.</li>
             <li>Result must be encoded from NTE page after posting.</li>
             <li>Result must be posted to be tagged as resolved.</li>
     </ol></div><br><hr><br><br>&nbsp; &nbsp; FILTER BY: &nbsp; &nbsp;  
     <form method="post" action="offense.php?w=List" style="display:inline"><input type="submit" name="nofilter" size="5" value="Show All"></form>&nbsp; &nbsp; OR 
     &nbsp; &nbsp; &nbsp; <form method="get" action="offense.php?w=List" style="display:inline">Filter by ID No:<input type="text" name="idfilter" size="5"></form>
     &nbsp; OR &nbsp; &nbsp;  <form method="get" action="offense.php?w=List" style="display:inline">Filter by Month of Date of NTE:<input type="text" name="lettermonthfilter" size="3"></form>
     <br><br><hr> ';

         $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'DateNTE,FullName'); $columnsub=$columnnameslist;
        $sql=$sqlfull.'WHERE ((mt.ResultID IS NULL) OR (mt.ResultID NOT IN (1,2,3))) OR ResultPosted=0 '.$lettermonthfilter.$idfilter.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
      //  if($_SESSION['(ak0)']==1002) {echo $sql;}
        
        $editprocess='offense.php?w=LookupNTE&TxnID='; $editprocesslabel='LookupNTE'; $txnid='TxnID';
        if(allowedToOpen(6822,'1rtc')) { $addlprocess='offenseresult.php?w=LookupResult&TxnID='; $addlprocesslabel='Lookup_Result'; } 
        
      include('../backendphp/layout/displayastable.php');  
      $sql=$sqlfull.'WHERE ((mt.ResultID IS NOT NULL) AND (mt.ResultID IN (1,2,3))) AND ResultPosted<>0 '.$lettermonthfilter.$idfilter.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); // if($_SESSION['(ak0)']==1002) {echo $sql;}
      $subtitle='<br><br><hr><br><br>Resolved NTE\'s';
      $columnnames=array('IDNo','FullName','Position','AssignedBranch','DateNTE','NTECOCOffenseType','PotentialViolation','FirstInfo','NTESanction','WithSuspension?','NTEDateofSuspension','Posted?','EncodedBy','TimeStamp','Result','ResultBy', 'ResultTS');
      include('../backendphp/layout/displayastableonlynoheaders.php');  
      
        break;
    case 'New':
       $title='New NTE'; $fieldsinrow=1; $method='post';
    $columnnames=array(
                    array('field'=>'DateNTE', 'caption'=>'Date of NTE', 'type'=>'date','size'=>8, 'required'=>true, 'value'=>date('Y-m-d')),
		    array('field'=>'FullName', 'type'=>'text','size'=>25,'required'=>true,'list'=>'employees'),
                    array('field'=>'DateofIncident', 'caption'=>'Date of Incident',  'type'=>'date','size'=>8),
		    array('field'=>'NTECOCOffenseType', 'caption'=>'Offense Type', 'type'=>'text','size'=>80, 'required'=>true),
                    array('field'=>'PotentialViolation', 'caption'=>'Potential Offense/Violation', 'type'=>'text','size'=>100, 'required'=>true),
                    array('field'=>'FirstInfo', 'caption'=>'First info on initial investigation (other info on next step)', 'type'=>'text','size'=>100, 'required'=>true),
                    array('field'=>'NTESanction', 'caption'=>'Sanction as defined in the Code of Conduct', 'type'=>'text','size'=>100, 'required'=>true),
                    array('field'=>'WithSuspension?', 'caption'=>'With Preventive Suspension?', 'type'=>'check','size'=>2, 'list'=>'yesno', 'value'=>'No'),
                    array('field'=>'NTEDateofSuspension', 'caption'=>'Date of Suspension (keep blank if none)',  'type'=>'date','size'=>8)
                    );
    $action='offense.php?w=Add';
    $liststoshow=array('yesno');
    include('../backendphp/layout/inputmainform.php'); 
        break;
    case 'Add':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `hr_4offensemain` SET '.$sql.'IDNo='.$idno.', PositionID='.$positionid.', AssignedBranchNo='.$assignedbranchno.', `WithSuspension?`='.$withsuspension.$ntedateofsuspension.', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';
        if($_SESSION['(ak0)']==1002) {echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:offense.php?w=List");
        break;
    case 'Delete':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `hr_4offensemain` WHERE Posted=0 AND TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:offense.php?w=List");
        break;
    case 'Post':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if(allowedToOpen(6821,'1rtc')) {
            $stmt0=$link->query('SELECT Posted FROM `hr_4offensemain` WHERE TxnID='.$_GET['TxnID']); $res0=$stmt0->fetch();
            $posted=$res0['Posted'];
        $sql='UPDATE `hr_4offensemain` SET Posted='.($posted==0?1:0).' WHERE TxnID='.$_GET['TxnID'];
        } else { $sql='UPDATE `hr_4offensemain` SET Posted=1 WHERE TxnID='.$_GET['TxnID']; }
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
   case 'EditSpecifics':
         $title='Edit Specifics';
	 $txnid=intval($_GET['TxnID']); $main='hr_4offensemain'; $columnstoedit=$columnstoadd;
         $columnstoedit=array('FullName')+$columnstoedit; $columnstoedit[]='WithSuspension?'; $columnstoedit[]='NTEDateofSuspension';
	 $sql=$sqlfull.'WHERE Posted=0 AND mt.TxnID='.$txnid; if($_SESSION['(ak0)']==1002) {echo $sql;}
        $columnnames=$columnnameslist;
	 $columnswithlists=array('FullName');$listsname=array('FullName'=>'employees');
	 $editprocess='offense.php?w=Edit&TxnID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `hr_4offensemain` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'IDNo='.$idno.', PositionID='.$positionid.', AssignedBranchNo='.$assignedbranchno.', `WithSuspension?`='.$withsuspension.$ntedateofsuspension.', TimeStamp=Now() WHERE Posted=0 AND TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:offense.php?w=LookupNTE&TxnID=".$_GET['TxnID']);
        break;
   case 'LookupNTE': 
       ?><title><?php echo $title; ?></title>
       <h3>NTE for <?php echo $title; ?></h3><br>
       <a href="offense.php?w=New">Add New NTE</a>&nbsp; &nbsp;<a href="offense.php?w=List">Back to List</a>
       &nbsp; &nbsp;<a href='offense.php?w=Post&action_token=<?php echo html_escape($_SESSION['action_token']);?>&TxnID=<?php echo $txnid; ?>'><?php if(allowedToOpen(6821,'1rtc')) { echo 'Post_Unpost'; } else { echo 'Post'; } ?></a>
       <?php if ($result['Posted']<>0) { ?>&nbsp; &nbsp;<a href='offense.php?w=Letter&print=1&TxnID=<?php echo $txnid; ?>'>Print Preview</a><?php 
       if (in_array($result['ResultID'],array(1,2,3))) { ?>&nbsp; &nbsp;<a href='offenseresult.php?w=LookupResult&TxnID=<?php echo $txnid; ?>'>Lookup Result</a><?php }
        if(allowedToOpen(6822,'1rtc')) { ?><br><br><form method='post' style='display: inline;'
                            action='offenseresult.php?w=EnterResult&TxnID=<?php echo $txnid; ?>'> 
<!--            Short Summary of Violation (required if case decision or suspension): <input type=text size=30 name='ShortSummary'></input>
            Date of Employee's Response: <input type=date size=6 name='DateResponse'></input><br><br>
            Date of Result Letter: <input type=date size=6 name='DateResult' value='<?php echo date('Y-m-d'); ?>'></input>-->
            Enter Result: <input type=text size=8 name='Result' list='results'></input>
            <input type="hidden" name="action_token" value='<?php echo html_escape($_SESSION['action_token']);?>'></input>
            &nbsp; <input type=submit size=8 name='Submit'></input></form><?php 
            echo comboBox($link,'SELECT ResultID, ResultDesc AS Result FROM `hr_0offenseresult` ORDER BY ResultDesc;','ResultID','Result','results');
        }
            
       } 
       else { ?>&nbsp; &nbsp;<a href='offense.php?w=Delete&action_token=<?php echo html_escape($_SESSION['action_token']);?>&TxnID=<?php echo $txnid; ?>'  
                       OnClick='return confirm("Really delete this?");'>Delete NTE</a><?php }
       ?>
       <br><br><hr><br>
       <div style="margin-left: 50px; background-color: #FFFFFF; padding: 15px;">
       <?php echo $letter; ?>    
        </div>
        <?php
    break;

   case 'AddSub':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
       $sqlmain=$sqlfull.'WHERE mt.TxnID='.$_REQUEST['TxnID']; $stmt=$link->query($sqlmain); $result=$stmt->fetch();
       if ($result['Posted']==0) {
       $columnstoaddsub[]='TxnID'; $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `hr_4offensesub` SET '.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';
        if($_SESSION['(ak0)']==1002) {echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
       }
        header("Location:".$_SERVER['HTTP_REFERER']);
       break;
       
   case 'DelSub':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
       $sqlmain=$sqlfull.'WHERE mt.TxnID='.$_REQUEST['TxnID']; $stmt=$link->query($sqlmain); $result=$stmt->fetch();
       if ($result['Posted']==0) {
            $sql='DELETE FROM `hr_4offensesub` WHERE TxnSubId='.$_REQUEST['TxnSubId'];
        if($_SESSION['(ak0)']==1002) {echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
       }
        header("Location:".$_SERVER['HTTP_REFERER']);
       break;
       
   case 'EditSub':
       $title='';       
       $txnsubid=$_REQUEST['TxnSubId'];
       $main='hr_4offensesub'; $columnstoedit=$columnstoaddsub;
       $sql='SELECT m.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, s.* FROM hr_4offensemain m 
            JOIN hr_4offensesub s ON m.TxnID=s.TxnID
	       LEFT JOIN `1employees` e1 ON e1.IDNo=m.IDNo WHERE Posted=0 AND s.TxnSubId='.$txnsubid; if($_SESSION['(ak0)']==1002) {echo $sql;}
        $columnnames=array('IDNo','FullName','DateNTE','OrderOfInfo','OtherInfo');	 
	 $editprocess='offense.php?w=PrEditSub&TxnID='.$_REQUEST['TxnID'].'&TxnSubId='.$txnsubid; 
         include('../backendphp/layout/editspecificsforlists.php');
        break;
   case 'PrEditSub':
   require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `hr_4offensesub` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE TxnSubId='.$_GET['TxnSubId']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:offense.php?w=LookupNTE&TxnID=".$_GET['TxnID']);
        break;
            
   case 'Letter':       
              
       $sqlco='SELECT c.CompanyNo, c.Company,c.CompanyName FROM `hr_4offensemain` t JOIN `1employees` e ON e.IDNo=t.IDNo JOIN `1companies` c ON c.CompanyNo=e.RCompanyNo WHERE t.Posted=1 AND t.TxnID='.$txnid; $stmtco=$link->query($sqlco); $resultco=$stmtco->fetch();
 
    $letter='<br><br><style> a:link { color: darkblue; text-decoration: none; }
</style><center><img src="../generalinfo/logo/'.$resultco['Company'].'.png"></br></center><br><br><br>'.$letter;

    if ($result['Posted']<>0) { echo $letter; } else { echo 'NTE must be posted first.';}
       break;
    
}
  $link=null; $stmt=null;
?>
</body></html>