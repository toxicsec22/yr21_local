<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(682,'1rtc')) {   echo 'No permission'; exit;} 
if (!isset($_REQUEST['print'])) { include_once('../switchboard/contents.php');}


include_once('../backendphp/layout/regulartablestyle.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$columnnameslist=array('IDNo','FullName','Position','AssignedBranch','DateNTE','NTECOCOffenseType','PotentialViolation','FirstInfo','OtherInfoLines','NTESanction','WithSuspension?','NTEDateofSuspension','DateResult','Result');
$columnstoadd=array_diff($columnnameslist,array('IDNo','FullName','Position','AssignedBranch','OtherInfoLines','WithSuspension?','NTEDateofSuspension','Posted?','EncodedBy','TimeStamp','Result'));

$which=(!isset($_GET['w'])?'List':$_GET['w']);
        
if (in_array($which,array('AddSub','EditSub','PrEditSub'))) { $columnstoaddsub=array('OrderOfInfo','OtherInfo'); }
if (in_array($which,array('AddSub','EditSub','PrEditSub','DelSub'))) {  $table=($_GET['t']=='res'?'hr_4offenseresult':'hr_4offensefindings'); }
	// MUST ADD Posted=1
$sqlfull='SELECT mt.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, e1.Nickname, Branch AS AssignedBranch, e.Nickname as EncodedBy, p.Position, (SELECT COUNT(TxnID) FROM hr_4offenseresult WHERE TxnID=mt.TxnID) AS OtherInfoLines, IFNULL(`ResultDesc`,"") AS `Result`, cp.deptheadpositionid FROM hr_4offensemain mt 
	       JOIN `1employees` e ON e.IDNo=mt.EncodedByNo
	       LEFT JOIN `1employees` e1 ON e1.IDNo=mt.IDNo
               LEFT JOIN `attend_30currentpositions` cp ON cp.BranchNo=mt.AssignedBranchNo AND cp.IDNo=mt.IDNo
	       LEFT JOIN `attend_0positions` p ON p.PositionID=mt.PositionID LEFT JOIN hr_4offenseresult rs ON mt.TxnID=rs.TxnID '
        . ' LEFT JOIN `hr_0offenseresult` ofr ON ofr.ResultID=mt.ResultID ';

if (in_array($which,array('LookupResult','Letter'))){
       $txnid=intval($_REQUEST['TxnID']);
       $sqlmain=$sqlfull.' WHERE mt.Posted=1 AND mt.TxnID='.$txnid; $stmt=$link->query($sqlmain); $result=$stmt->fetch();
       
       $sqlby='SELECT `Position`, e.`IDNo`, CONCAT(`FirstName`," ",LEFT(`MiddleName`,1),". ",`Surname`) AS `Name` FROM `attend_30currentpositions` p JOIN `1employees` e ON e.IDNo=p.IDNo WHERE e.`IDNo`='.$result['ResultByNo']; $stmtby=$link->query($sqlby); $by=$stmtby->fetch();
       $sqldepthead='SELECT p.`Position`, CONCAT(`FirstName`," ",LEFT(`MiddleName`,1),". ",`Surname`) AS `Name` FROM `attend_30currentpositions` p JOIN `1employees` e ON e.IDNo=p.IDNo WHERE p.PositionID='.$result['deptheadpositionid']; $stmtby=$link->query($sqldepthead); $depthead=$stmtby->fetch();
       $title=$result['Nickname'].' '.$result['DateResult'];
       $letter='Date: <b>'.date('d F Y',strtotime($result['DateResult'])).'</b><br><br>'
       .'To:  <b>'.(($result['ResultPosted']<>0 and $which=='Letter')?'<a href="javascript:window.print()">'.$result['FullName'].'</a>':$result['FullName']).'</b><br>'
       .'Position:  <b>'.$result['Position'].', '.$result['AssignedBranch'].'</b><br><br>'
       .'From: Human Resources Department<br><br>'
       .'Subject: Notice of '.$result['Result'].'<br><br><hr><br>'
       .'Dear <b>'.$result['Nickname'].'</b>:<br><br>';
       
       if ($result['ResultID']==1) { // Exoneration
           $sqlsubnte='SELECT * FROM `hr_4offensesub` WHERE TxnID='.$txnid.' ORDER BY OrderOfInfo'; $stmtsubnte=$link->query($sqlsubnte); $ressubnte=$stmtsubnte->fetchAll();
           $ntelist=''; 
       foreach($ressubnte as $info){ $ntelist=$ntelist.'<li>'.$info['OtherInfo'].'</li>'; }
       $ntelist='<ul style="margin-left: 50px;"><li>'.$result['FirstInfo'].'</li>'.$ntelist.'</ul> <br><br>';
       } else {
       $sqlsubfindings='SELECT * FROM `hr_4offensefindings` WHERE TxnID='.$txnid.' ORDER BY OrderOfInfo'; $stmtsubfindings=$link->query($sqlsubfindings); $ressubfindings=$stmtsubfindings->fetchAll();
       $sqlsub='SELECT * FROM `hr_4offenseresult` WHERE TxnID='.$txnid.' ORDER BY OrderOfInfo'; $stmtsub=$link->query($sqlsub); $ressub=$stmtsub->fetchAll();
       
       $findingslist=''; // Findings
       if($stmtsubfindings->rowCount()>0){
       foreach($ressubfindings as $info){ $findingslist=$findingslist.'<li>'.$info['OtherInfo']; 
       if ($result['ResultPosted']==0) { 
           $findingslist=$findingslist.'&nbsp; &nbsp; <a href=offenseresult.php?w=EditSub&t=findings&TxnID='.$result['TxnID'].'&TxnSubId='.$info['TxnSubId'].'>Edit</a>'
                 . '&nbsp; &nbsp; <a href=offenseresult.php?w=DelSub&t=findings&TxnID='.$result['TxnID'].'&TxnSubId='.$info['TxnSubId']
                        .'&action_token='.html_escape($_SESSION['action_token']).' OnClick=\'return confirm("Really delete this?")\'>Delete</a>'; }
                $findingslist=$findingslist.'</li>';
                }
       }
       $findingslist='<ul style="margin-left: 50px;">'.$findingslist.'</ul>';
       
       $list=''; // Result
       if($stmtsub->rowCount()>0){
       foreach($ressub as $info){ $list=$list.'<li>'.$info['OtherInfo']; 
         if ($result['ResultPosted']==0) { $list=$list.'&nbsp; &nbsp; <a href=offenseresult.php?w=EditSub&t=res&TxnID='.$result['TxnID'].'&TxnSubId='.$info['TxnSubId'].'>Edit</a>'
                 . '&nbsp; &nbsp; <a href=offenseresult.php?w=DelSub&t=res&TxnID='.$result['TxnID'].'&TxnSubId='.$info['TxnSubId']
                        .'&action_token='.html_escape($_SESSION['action_token']).' OnClick=\'return confirm("Really delete this?")\'>Delete</a>'; }
                $list=$list.'</li>';
                }
       $list='<ul style="margin-left: 50px;">'.$list.'</ul>';
       }
       
       if ($result['ResultPosted']==0) { 
           
           $sqlsuborder='SELECT IF(ISNULL(MAX(OrderOfInfo)),1,(MAX(OrderOfInfo)+1)) AS NextNum FROM `hr_4offensefindings` WHERE TxnID='.$txnid; 
            $stmtsuborder=$link->query($sqlsuborder); $ressuborder=$stmtsuborder->fetch();
           $findingslist.='<br><br><form action="offenseresult.php?w=AddSub&t=findings" method="post">
               Add:  Number (for order only) <input type="text" size="3" name="OrderOfInfo" value='.$ressuborder['NextNum']
               .'>&nbsp; &nbsp; Findings <input type="text" size="15" name="OtherInfo">'
               .'<input type="hidden" name="TxnID" value='.$txnid.'>'
               .'<input type="hidden" name="action_token" value='.html_escape($_SESSION['action_token'])
                   .' /> &nbsp; &nbsp; <input type="submit" name="add" value="Add Findings"></form>'; 
           
           $sqlsuborder='SELECT IF(ISNULL(MAX(OrderOfInfo)),1,(MAX(OrderOfInfo)+1)) AS NextNum FROM `hr_4offenseresult` WHERE TxnID='.$txnid; 
            $stmtsuborder=$link->query($sqlsuborder); $ressuborder=$stmtsuborder->fetch();
           $list.='<br><br><form action="offenseresult.php?w=AddSub&t=res" method="post">
               Add:  Number (for order only) <input type="text" size="3" name="OrderOfInfo" value='.$ressuborder['NextNum']
               .'>&nbsp; &nbsp; Conclusions <input type="text" size="15" name="OtherInfo">'
               .'<input type="hidden" name="TxnID" value='.$txnid.'>'
               .'<input type="hidden" name="action_token" value='.html_escape($_SESSION['action_token'])
                   .' /> &nbsp; &nbsp; <input type="submit" name="add" value="Add Conclusion"></form>'; 
           
        } 
       
       }
       
       switch ($result['ResultID']){
           case 1: //Exoneration
              $letter.='Relative to the notice to explain memo issued to you '.date('d F Y',strtotime($result['DateNTE'])).', regarding
alleged violation of Code of Conduct '.$result['NTECOCOffenseType']
       .', specifically on: '.$result['PotentialViolation'].'.<br><br>This refers specifically to:<br><br>'.$ntelist.'Please be advised that after reviewing the facts of the case and your written explanation dated '.(date('F d, Y',strtotime($result['DateResponse']))).', we found <i>no substantive evidence</i> to support a violation.<br><br>Thank you for your full cooperation in processing this case. This notice hereby serves as letter of case decision and <b><u><i>clearance of your name in this allegation</i></u></b>.<br><br>For your guidance.'; 
               break;
           case 2: //Suspension
               $letter.='This is in relation to the Notice to Explain memo issued to you on '.date('d F Y',strtotime($result['DateNTE'])).' regarding
the incident on '.date('d F Y',strtotime($result['DateofIncident'])).' where '.$result['ShortSummary'].'.<br><br>Based on your written explanation dated '.(date('F d, Y',strtotime($result['DateResponse']))).', and, on the investigation conducted in relation to this, it was found out that:<br><br>'.$findingslist.'<br><br>From these findings, it was established that:<br><br>'.$list.'<br><br>Based on above, we regret to inform you that this letter serves as a notice of suspension in the matter
regarding your violation of our Code of Conduct specific Article in Code of Conduct "'.$result['ResultCOCArticle'].'".<br><br>You are hereby being placed on <b>'.$result['DaysSuspended'].' day/s suspension without pay</b>. Effectivity of the said sanction will be discussed by your Line Lead. You are expected to return to work right after the
suspension dates.<br><br>This is to also formally advise you that any further violation may lead to a longer suspension and/or termination of your employment. I hope that you understand the seriousness of this matter.';
               break;
           case 3: //Case Decision
               include_once $path.'/acrossyrs/commonfunctions/ordinalnumber.php';
               $letter.='This is in relation to the Notice to Explain memo issued to you on '.date('d F Y',strtotime($result['DateNTE'])).' regarding
the incident on '.date('d F Y',strtotime($result['DateofIncident'])).' where '.$result['ShortSummary'].'.<br><br>Based on your written explanation dated '.(date('F d, Y',strtotime($result['DateResponse']))).', and, on the investigation conducted in relation to this, it was found out that:<br><br>'.$findingslist.'<br><br>From these findings, it was established that:<br><br>'.$list.'<br><br>Based on above, we regret to inform you that this letter serves as a notice of '.$result['ResultSanction'].' in the
matter regarding your violation of our Code of Conduct "'.$result['ResultCOCArticle'].'".<br><br>This is your '.addOrdinalNumberSuffix($result['NthOffense']).' on the said violation.
This is to also formally advise you that any further violation may lead to a more serious sanction. I hope that you understand the seriousness of this matter.';
               break;
       }
       
     // THIS ENDS LETTER  
       $letter.='<br><br><br>Prepared by:<br><br><br><br>'.$by['Name'].'<br>'.$by['Position'].'<br><br>'.'Noted by:'.str_repeat('<br>', 4).$depthead['Name'].'<br>'.$depthead['Position'];      
       if ($result['ResultID']==2) { $letter.='<br><br><br>Suspension Dates: &nbsp; &nbsp; <b><u>'.$result['SuspensionDates']
               .'</u></b><br><br>Return to Work: &nbsp; &nbsp; <b><u>'.date('F d, Y',strtotime($result['ReturnToWork'])).'</u></b>';}
}

switch ($which){
   
    case 'EnterResult': 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $txnid=intval($_GET['TxnID']); 
        $result=comboBoxValue($link, 'hr_0offenseresult', 'ResultDesc', $_POST['Result'], 'ResultID');
        $sql='UPDATE `hr_4offensemain` SET ResultID='.$result.', ResultByNo='.$_SESSION['(ak0)'].', ResultTS=Now() WHERE TxnID='.$txnid;
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:offenseresult.php?w=LookupResult&TxnID=".$txnid);
        break;
    case 'EditResult': 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $txnid=intval($_GET['TxnID']); 
        $result=comboBoxValue($link, 'hr_0offenseresult', 'ResultDesc', $_POST['Result'], 'ResultID');
        $columnstoadd=array('ShortSummary','DateResponse','DateResult','ResultSanction','ResultCOCArticle','NthOffense','DaysSuspended','SuspensionDates','ReturnToWork'); 
        $sql='';
        foreach ($columnstoadd as $field) {
            if (!isset($_POST[$field]) or empty($_POST[$field])) { goto skipfield;}
            else { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
            skipfield:
        }
	$sql='UPDATE `hr_4offensemain` SET '.$sql.' ResultID='.$result.', ResultByNo='.$_SESSION['(ak0)'].', ResultTS=Now() WHERE TxnID='.$txnid;
        if($_SESSION['(ak0)']==1002) { echo $sql; }
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:offenseresult.php?w=LookupResult&TxnID=".$txnid);
        break;
    case 'Unset':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='UPDATE `hr_4offensemain` SET ResultID=NULL, ResultByNo='.$_SESSION['(ak0)'].', ResultTS=Now() WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        $sql='DELETE FROM `hr_4offenseresult` WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        $sql='DELETE FROM `hr_4offensefindings` WHERE TxnID='.$_GET['TxnID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:offense.php?w=LookupNTE&TxnID=".$_GET['TxnID']);
        break;
    case 'Post':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if(allowedToOpen(6821,'1rtc')) {
            $stmt0=$link->query('SELECT ResultPosted FROM `hr_4offensemain` WHERE TxnID='.$_GET['TxnID']); $res0=$stmt0->fetch();
            $posted=$res0['ResultPosted'];
        $sql='UPDATE `hr_4offensemain` SET ResultPosted='.($posted==0?1:0).' WHERE TxnID='.$_GET['TxnID'];
        } else { $sql='UPDATE `hr_4offensemain` SET ResultPosted=1 WHERE TxnID='.$_GET['TxnID']; } //if($_SESSION['(ak0)']==1002) { echo $sql; break;}
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
   case 'LookupResult': 
       ?><title><?php echo $title; ?></title>
       <h3>Result for <?php echo $title; ?></h3><br>
       <a href="offense.php?w=List">Back to NTE List</a>&nbsp; &nbsp;<a href="offense.php?w=LookupNTE&TxnID=<?php echo $txnid; ?>">Back to Corresponding NTE Letter</a>
       &nbsp; &nbsp;<a href='offenseresult.php?w=Post&action_token=<?php echo html_escape($_SESSION['action_token']);?>&TxnID=<?php echo $txnid; ?>'>
           <?php if(allowedToOpen(6821,'1rtc')) { echo 'Post_Unpost_Result'; } else { echo 'Post_Result'; } ?></a>
       <?php if ($result['ResultPosted']<>0) { ?>&nbsp; &nbsp;<a href='offenseresult.php?w=Letter&print=1&TxnID=<?php echo $txnid; ?>'>Print Preview</a><br><br><hr><br>
       <?php    
       } else { ?>&nbsp; &nbsp;<a href='offenseresult.php?w=Unset&action_token=<?php echo html_escape($_SESSION['action_token']);?>&TxnID=<?php echo $txnid; ?>'  
                       OnClick='return confirm("CAUTION!  Really delete result? This will remove ALL findings and results.");'>Unset Result (Delete results notes.)</a>
                       
         <br><br><hr><br><form method='post' action='offenseresult.php?w=EditResult&TxnID=<?php echo $txnid; ?>'>
           Change Result: <input type=text size=12 name='Result' list='results' value='<?php echo $result['Result']; ?>'></input>
       <?php 
       switch ($result['ResultID']){
           case 1://Exoneration ?>
                Date of Employee's Response: <input type=date size=6 name='DateResponse' value='<?php echo $result['DateResponse']; ?>'></input><br><br>
                Date of Result Letter: <input type=date size=6 name='DateResult' value='<?php echo (!isset($result['DateResult'])?date('Y-m-d'):$result['DateResult']); ?>'></input>
       <?php
               break;
           case 2: //Suspension ?>
                Short Summary of Violation: <input type=text size=50 name='ShortSummary' value='<?php echo $result['ShortSummary']; ?>'></input>&nbsp; &nbsp;
                Date of Employee's Response: <input type=date size=6 name='DateResponse' value='<?php echo (($result['DateResponse']=='0000-00-00')?date('Y-m-d'):$result['DateResponse']); ?>'></input><br><br>
                Date of Result Letter: <input type=date size=6 name='DateResult' value='<?php echo (($result['DateResult']=='0000-00-00')?date('Y-m-d'):$result['DateResult']); ?>'></input>&nbsp; &nbsp;
                COC Article: <input type=text size=80 name='ResultCOCArticle' value='<?php echo $result['ResultCOCArticle']; ?>'></input><br><br>
                Number of Days Suspended: <input type=text size=2 name='DaysSuspended' value='<?php echo $result['DaysSuspended']; ?>'></input>&nbsp; &nbsp;
                Suspension Dates: <input type=text size=10 name='SuspensionDates' value='<?php echo $result['SuspensionDates']; ?>'></input>&nbsp; &nbsp;
                Return-to-Work Date: <input type=date size=6 name='ReturnToWork' value='<?php echo $result['ReturnToWork']; ?>'></input>&nbsp; &nbsp; &nbsp; &nbsp;
       <?php
               break;
           case 3: //Case Decision 
               ?>
                Short Summary of Violation: <input type=text size=50 name='ShortSummary' value='<?php echo $result['ShortSummary']; ?>'></input>&nbsp; &nbsp;
                Date of Employee's Response: <input type=date size=6 name='DateResponse' value='<?php echo (($result['DateResponse']=='0000-00-00')?date('Y-m-d'):$result['DateResponse']); ?>'></input><br><br>
                Date of Result Letter: <input type=date size=6 name='DateResult' value='<?php echo (($result['DateResult']=='0000-00-00')?date('Y-m-d'):$result['DateResult']); ?>'></input>&nbsp; &nbsp;
                Type of Sanction: <input type=text size=50 name='ResultSanction' value='<?php echo $result['ResultSanction']; ?>'></input><br><br>
                COC Article: <input type=text size=80 name='ResultCOCArticle' value='<?php echo $result['ResultCOCArticle']; ?>'></input>&nbsp; &nbsp;
                Number of same offense: <input type=text size=3 name='NthOffense' value='<?php echo $result['NthOffense']; ?>'></input>&nbsp; &nbsp;
       <?php
               break;
       }
       ?>
        <input type="hidden" name="action_token" value='<?php echo html_escape($_SESSION['action_token']);?>'></input>
        &nbsp; <input type=submit size=8 name='Submit'></input></form><?php 
            echo comboBox($link,'SELECT ResultID, ResultDesc AS Result FROM `hr_0offenseresult` ORDER BY ResultDesc;','ResultID','Result','results');       
       
                       
                       
       }
            
       ?><br><hr><br>
       <div style="margin-left: 50px; background-color: #FFFFFF; padding: 15px;">
       <?php echo $letter; ?>    
        </div>
        <?php
    break;

   case 'AddSub':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
       $sqlmain=$sqlfull.'WHERE mt.TxnID='.$_REQUEST['TxnID']; $stmt=$link->query($sqlmain); $result=$stmt->fetch();
       if ($result['ResultPosted']==0) {
       $columnstoaddsub[]='TxnID'; $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `'.$table.'` SET '.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';
        if($_SESSION['(ak0)']==1002) {echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
       }
        header("Location:".$_SERVER['HTTP_REFERER']);
       break;
       
   case 'DelSub':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
       $sqlmain=$sqlfull.'WHERE mt.TxnID='.$_REQUEST['TxnID']; $stmt=$link->query($sqlmain); $result=$stmt->fetch();
       if ($result['ResultPosted']==0) {
            $sql='DELETE FROM `'.$table.'` WHERE TxnSubId='.$_REQUEST['TxnSubId'];
        if($_SESSION['(ak0)']==1002) {echo $sql;}
        $stmt=$link->prepare($sql); $stmt->execute();
       }
        header("Location:".$_SERVER['HTTP_REFERER']);
       break;
       
   case 'EditSub':
       $title='';       
       $txnsubid=$_REQUEST['TxnSubId'];
       $main='hr_4offenseresult'; $columnstoedit=$columnstoaddsub;
       $sql='SELECT m.*, CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, s.* FROM hr_4offensemain m 
            JOIN `'.$table.'` s ON m.TxnID=s.TxnID
	       LEFT JOIN `1employees` e1 ON e1.IDNo=m.IDNo WHERE ResultPosted=0 AND s.TxnSubId='.$txnsubid; if($_SESSION['(ak0)']==1002) {echo $sql;}
        $columnnames=array('IDNo','FullName','DateResult','OrderOfInfo','OtherInfo');	 
	 $editprocess='offenseresult.php?w=PrEditSub&t='.$_GET['t'].'&TxnID='.$_REQUEST['TxnID'].'&TxnSubId='.$txnsubid; 
         include('../backendphp/layout/editspecificsforlists.php');
        break;
   case 'PrEditSub':
   require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `'.$table.'` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE TxnSubId='.$_GET['TxnSubId']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:offenseresult.php?w=LookupResult&TxnID=".$_GET['TxnID']);
        break;
            
   case 'Letter':       
              
       $sqlco='SELECT c.CompanyNo, c.Company,c.CompanyName FROM `hr_4offensemain` t JOIN `1employees` e ON e.IDNo=t.IDNo JOIN `1companies` c ON c.CompanyNo=e.RCompanyNo WHERE t.Posted=1 AND t.ResultPosted=1 AND t.TxnID='.$txnid; $stmtco=$link->query($sqlco); $resultco=$stmtco->fetch();
 
    $letter='<br><br><style> a:link { color: darkblue; text-decoration: none; }
</style><center><img src="../generalinfo/logo/'.$resultco['Company'].'.png"></br></center><br><br><br>'.$letter;

    if ($result['ResultPosted']<>0) { echo $letter; } else { echo 'Result must be posted first.';}
       break;
    
}
  $link=null; $stmt=null;
?>
</body></html>