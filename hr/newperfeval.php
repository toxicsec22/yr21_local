<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

include_once('../backendphp/layout/linkstyle.php');

echo '
<br><div id="section" style="display: block;">
    <div>';
    
    echo '<a id=\'link\' href="newperfeval.php">Core Competencies Evaluation</a> ';
    if(allowedToOpen(686,'1rtc')) {
        echo '<a id=\'link\' href="newperfeval.php?w=AddSpecialEval">Encode Special Evaluation</a> ';
	    echo '<a id=\'link\' href="smsnewperfeval.php">Send SMS for Pending Evaluation</a> ';
    }
    if(allowedToOpen(array(686,100),'1rtc')) {
        echo '<a id=\'link\' href="newperfeval.php?w=FinishedEval">Finished Evaluations</a> ';
        echo '<a id=\'link\' href="newperfeval.php?w=History">Lookup Evaluation History</a> ';
    }
    if(allowedToOpen(array(686,100,685),'1rtc')) {
	    echo '<a id=\'link\' href="newperfeval.php?w=EvaluationSummary">Pending Evaluation Per Dept Summary</a> ';
    }
	echo '<a id=\'link\' href="newperfeval.php?w=MyEval">My Evaluation History</a>';

    echo '
    </div><br/><br/>';

    
    

$columnnameslist=array('IDNo','FullName','Company','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor','PendingStep','HREncodedBy');
$columnstoadd=array('EStat','Supervisor','SStat','DStat');

$which=(!isset($_GET['w'])?'CoreCompetenciesEvaluation':$_GET['w']);

if (in_array($which,array('AddSpecialEval','EditSpecifics','History'))){
   echo comboBox($link,'SELECT IDNo, CONCAT(Nickname, " ", Surname) AS FullName FROM `1employees` ORDER BY Nickname;','IDNo','FullName','employees');
   } 
   
if (in_array($which,array('MyEval','EvaluationSummary','History'))){   
   $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor','Emp_Response');
}

if (in_array($which,array('Add','Edit'))){
   $idno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['FullName']),'IDNo');
   }
	
$sql='SELECT pf.*,
(CASE
   WHEN EStat=0 AND SStat=0 AND DStat=0 AND Ack=0 AND HRStatus=0 THEN "SelfEval"
   WHEN EStat=1 AND SStat=0 AND DStat=0 AND Ack=0 AND HRStatus=0 THEN "SuperEval"
   WHEN EStat=1 AND SStat=1 AND DStat=0 AND Ack=0 AND HRStatus=0 THEN "DeptHead"
   WHEN EStat=1 AND SStat=1 AND DStat=1 AND Ack=0 AND HRStatus=0 THEN "EmpResponse"
   ELSE "HR"
END)

 AS PendingStep,CONCAT(e1.FirstName, " ", e1.Surname) AS FullName, e1.DateHired, TRUNCATE(((TO_DAYS(NOW()) - TO_DAYS(`e1`.`DateHired`)) / 365),2) AS `HowLongWithUsinYrs`, e.Nickname as HREncodedBy, Position AS CurrentPosition, b.Branch AS CurrentBranch, c.Company, CONCAT(e2.Nickname, " ", e2.Surname) AS Supervisor, IF(HRStatus=1,"Filed","Pending") AS HR_Status, IF(Ack=0,"",IF(Ack=1,"Agree","Disagree")) AS Emp_Response
	       FROM hr_82perfevalmain pf   
	       LEFT JOIN `1employees` e ON e.IDNo=pf.HREncodedByNo
	       JOIN `1employees` e1 ON e1.IDNo=pf.IDNo
	       LEFT JOIN `1employees` e2 ON e2.IDNo=pf.SIDNo
	       JOIN `1branches` b ON b.BranchNo=pf.CurrentBranchNo
	       JOIN `1companies` c ON c.CompanyNo=e1.RCompanyNo
	       LEFT JOIN `attend_0positions` p ON p.PositionID=pf.CurrentPositionID
	       '; 

switch ($which){
   
   case 'CoreCompetenciesEvaluation':
       if (!allowedToOpen(684,'1rtc')) {   echo 'No permission'; exit;}   
       
if(allowedToOpen(686,'1rtc')) {
    $editprocess='newperfeval.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit'; 
    $addlprocess='newperfeval.php?w=SetasComplete&TxnID='; $addlprocesslabel='Completed';
    $formdesc='<br><a href="newperfeval.php?w=DeletePage">Click here to delete performance evaluation.</a><br>';
$evalcondi='';
} else {
    $evalcondi='AND (pf.IDNo='.$_SESSION['(ak0)'].' OR pf.SIDNo='.$_SESSION['(ak0)'].' OR pf.DIDNo='.$_SESSION['(ak0)'].')';
}

if(allowedToOpen(686,'1rtc')){
    echo '<div>';
    echo '<div style="float:left">';
   echo '<form method="post" action="newperfeval.php?w=AutoEncodeYrEval" enctype="multipart/form-data">
            <input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"></input>
            <input type="submit" name="autoencodesemiyrend" value="Encode Semi Annual Evaluations"OnClick="return confirm(\'Are you sure you want to semi-annual evaluations?\');"> </form>';
       echo '</div><div style="margin-left:300px;">';
       echo '<form method="post" action="newperfeval.php?w=AutoEncodeYrEval" enctype="multipart/form-data">
            <input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"></input>
            <input type="submit" name="autoencodeyrend" value="Encode Year End Evaluations" OnClick="return confirm(\'Are you sure you want to encode year end evaluations?\');"> </form>';
       echo '</div></div>';
}
      $title='Core Competencies Evaluation'; $columnnames=array_diff($columnnameslist,array('Company','EComment','HREncodedBy','HR_Status','HRStatusTS'));;
         $sql=$sql.' WHERE pf.HRStatus<>1 AND e1.Resigned<>1 '.$evalcondi.' ORDER BY EvalDueDate ASC'; 
		// echo $sql;
        $addlprocess2='newperfevalform.php?TxnID='; $addlprocesslabel2='Lookup';
        $txnid='TxnID';
        
      include('../backendphp/layout/displayastable.php'); 
    
        break;

        case 'AddSpecialEval':
            if(!allowedToOpen(686,'1rtc')) { echo 'No Permission'; exit(); }

         
             $title='Encode Special Evaluation'; $method='POST';
             $columnnames=array(
                        array('field'=>'FullName','caption'=>'Add Special Evaluation for ','type'=>'text','size'=>20,'required'=>true, 'list'=>'employees'),
                        array('field'=>'EvalDueDate','caption'=>'Evaluation Date','type'=>'date','size'=>6,'required'=>true)
                 );
          $action='newperfeval.php?w=Add';
          $liststoshow=array(); $fieldsinrow=4;
        

         include('../backendphp/layout/inputmainform.php');
          
            break;

        //special evaluation
    case 'Add':
        if(!allowedToOpen(686,'1rtc')) { echo 'No Permission'; exit(); }
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		
        $sql='INSERT INTO `hr_82perfevalmain` (`IDNo`,`CurrentPositionID`,`CurrentBranchNo`,`EvalDueDate`, `EvalAfterDays`,`HREncodedByNo`,`HRTimeStamp`)
	    SELECT '.$idno.', `PositionID`, `BranchNo`, \''.$_POST['EvalDueDate'].'\',  DateDiff(\''.$_POST['EvalDueDate'].'\',DateHired), '.$_SESSION['(ak0)'].', Now() FROM `attend_30currentpositions`  cp JOIN 1employees e ON e.IDNo=cp.IDNo WHERE cp.IDNo='.$idno;
		
        $stmt=$link->prepare($sql); $stmt->execute();
        
        $sql0='SELECT TxnID, CurrentPositionID FROM `hr_82perfevalmain` WHERE IDNo='.$idno.' AND EvalDueDate=\''.$_POST['EvalDueDate'].'\';';
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
        

        $sql='UPDATE hr_82perfevalmain pf JOIN attend_30currentpositions cp ON pf.IDNo=cp.IDNo SET 
        SIDNo=(IF((cp.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=pf.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=pf.IDNo))),
        DIDNo=(SELECT cp2.IDNo FROM `attend_30currentpositions` cp2 WHERE cp2.PositionID=(SELECT cp2.PositionID FROM `attend_30currentpositions` cp2 
        WHERE cp2.PositionID=(SELECT IF((cp1.deptheadpositionid=cp1.PositionID),cp1.supervisorpositionid,cp1.deptheadpositionid) FROM `attend_30currentpositions` cp1 WHERE cp1.IDNo=pf.IDNo))) WHERE pf.TxnID='.$res0['TxnID'].';';
          
              $stmt=$link->prepare($sql); $stmt->execute();

      
$sqlpopultatedtoday='SELECT pem.TxnID,
    
(SELECT FormID FROM hr_81perfevalforms WHERE FIND_IN_SET(cp.PositionID,Positions))

 AS FormID
  FROM attend_30currentpositions cp JOIN hr_82perfevalmain pem ON cp.IDNo=pem.IDNo WHERE pem.TxnID='.$res0['TxnID'].'';
  echo $sqlpopultatedtoday.'<br>';
$stmt=$link->query($sqlpopultatedtoday); $res=$stmt->fetchAll();
    foreach ($res AS $row){
        $sql='INSERT INTO `hr_82perfevalsub` (`TxnID`,`CID`) SELECT '.$row['TxnID'].',CID FROM hr_81corecompetencies WHERE FormID="'.$row['FormID'].'";'; 
        echo $sql.'<br>';
          $stmt=$link->prepare($sql); $stmt->execute();  
    }

//sub
$sql='INSERT IGNORE INTO hr_82perfevalmonthlymain (IDNo,MonthNo,SIDNo,EncodedByNo,TimeStamp) SELECT cp.IDNo,'.date('m',strtotime($_POST['EvalDueDate'])).',(IF((cp.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=cp.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=cp.IDNo))),0,NOW() from attend_30currentpositions cp JOIN hr_82perfevalmain pem ON cp.IDNo=pem.IDNo WHERE pem.TxnID='.$res0['TxnID'].'';
echo $sql.'<br>';
        $stmt=$link->prepare($sql); $stmt->execute();
          
  
      $sqlpopultatedtoday='SELECT pemm.TxnID,
      
      (SELECT FID FROM hr_82fcmain WHERE FIND_IN_SET(cp.PositionID,DefaultPositions))
      
       AS FID
        FROM attend_30currentpositions cp JOIN hr_82perfevalmonthlymain pemm ON cp.IDNo=pemm.IDNo';
  
      $stmt=$link->query($sqlpopultatedtoday); $res=$stmt->fetchAll();
              foreach ($res AS $row){
              $sql='INSERT IGNORE INTO `hr_82perfevalmonthlysub` (`TxnID`,`FCID`,`Weight`) SELECT '.$row['TxnID'].',FCID,DefaultWeight FROM hr_82fcsub WHERE FID="'.$row['FID'].'";'; 
                $stmt=$link->prepare($sql); $stmt->execute(); 
          }
        header("Location:newperfeval.php");
        break;
		
		
    case 'Delete':
        if (!allowedToOpen(686,'1rtc')) {   echo 'No permission'; exit;}
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	   
	   
	    $sqlcheck='SELECT TxnID FROM `hr_82perfevalmain` WHERE IDNo='.$_POST['IDNo'].' AND EvalDueDate="'.$_POST['EvalDueDate'].'" AND SStat=0';
		$stmtcheck=$link->query($sqlcheck); $rescheck=$stmtcheck->fetch();
		if($stmtcheck->rowCount()>0){
			
			$sql='DELETE FROM `hr_82perfevalsub` WHERE TxnID='.$rescheck['TxnID'].'';
			$stmt=$link->prepare($sql); $stmt->execute();
			
			$sql='DELETE FROM `hr_82perfevalmain` WHERE TxnID='.$rescheck['TxnID'].'';
			$stmt=$link->prepare($sql); $stmt->execute();
			
		} else {
			echo 'No Records'; exit();
		}
		echo '<h4 style="color:green;">Deleted Successfully.</h4>';
        break;

   case 'EditSpecifics':
       if (!allowedToOpen(686,'1rtc')) {   echo 'No permission'; exit;}
         $title='Edit Specifics';
	 $txnid=intval($_GET['TxnID']); $main='hr_82perfevalmain';
		 $columnstoedit=array('Supervisor','EvalAfterDays','EvalDueDate');
	 $sql=$sql.'WHERE pf.HRStatus<>1 AND TxnID='.$txnid;
	 $columnnames=$columnnameslist;
	 $columnswithlists=array('Supervisor');$listsname=array('Supervisor'=>'employees');
	 $editprocess='newperfeval.php?w=Edit&TxnID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        if (!allowedToOpen(686,'1rtc')) {   echo 'No permission'; exit;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $superidno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['Supervisor']),'IDNo');
        $sql=''; 
        
        
		$columnstoedit=array('EvalAfterDays','EvalDueDate');
        foreach ($columnstoedit as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; $supercond='';}
               
        $sql='UPDATE `hr_82perfevalmain` SET HREncodedByNo='.$_SESSION['(ak0)'].','.$sql.'SIDNo='.$superidno.', HRTimeStamp=Now() WHERE HRStatus<>1 '.$supercond.' AND TxnID='.$_GET['TxnID'];
		
        $stmt=$link->prepare($sql); $stmt->execute();
		
        header("Location:newperfeval.php");
        break;

        
   case 'SetasComplete':
       if (!allowedToOpen(686,'1rtc')) {   echo 'No permission'; exit;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        
        $sql='UPDATE `hr_82perfevalmain` SET HRStatus=IF(HRStatus=0,1,0), HRStatusTS=Now(), HREncodedByNo='.$_SESSION['(ak0)'].' WHERE Ack<>0 AND TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;


        case 'FunctionalEvaluations':
            if (!allowedToOpen(683,'1rtc')) {   echo 'No permission'; exit;}
           
            if(allowedToOpen(683,'1rtc')){
          $formdesc='<br></i><form action="#" method="POST"><select name="MyView">
          '.((allowedToOpen(686,'1rtc'))?'<option value="0">All</option>':'').'
          '.((allowedToOpen(100,'1rtc'))?'<option value="1">My Department</option>':'').'
          <option value="2">My Team</option>
          <option value="3">Myself</option>
          </select> <input type="submit" name="btbLookup" value="Lookup"></form><i>';
            }
 else {
     $formdesc='';
 }


          if(allowedToOpen(686,'1rtc')) {
            $evalcondi='';
            $myview=' - All';
        } elseif(allowedToOpen(685,'1rtc')){
            $evalcondi=' WHERE (pf.SIDNo='.$_SESSION['(ak0)'].') ';
            $myview=' - My Team';
        } else {
            $evalcondi=' WHERE (pf.IDNo='.$_SESSION['(ak0)'].') ';
            $myview=' - Myself';
        }

        if(isset($_POST['btbLookup'])){
            
            if($_POST['MyView']==0){
                $evalcondi='';
            } else if($_POST['MyView']==1){
                $evalcondi=' WHERE cp.deptheadpositionid='.$_SESSION['&pos'].'';
                $myview=' - My Department';
            } else if($_POST['MyView']==2){
                $evalcondi=' WHERE (pf.SIDNo='.$_SESSION['(ak0)'].') ';
                $myview=' - My Team';
            } else {
                $evalcondi=' WHERE (pf.IDNo='.$_SESSION['(ak0)'].') ';
                    $myview=' - Myself';
            }
        }


           $title='Functional Evaluations'.$myview; $method='POST';
         
           $arraymonthno=array('12'=>'Dec','11'=>'Nov','10'=>'Oct','09'=>'Sep','08'=>'Aug','07'=>'Jul','06'=>'Jun','05'=>'May','04'=>'Apr','03'=>'Mar','02'=>'Feb','01'=>'Jan');
  
           $sqlmonthno=''; $monthfield=''; $columnnames=array('IDNo','FullName');
           if((isset($_POST['MyView']) AND ($_POST['MyView']==1 OR $_POST['MyView']==0)) OR $evalcondi==''){
               array_push($columnnames,'Branch','Position');
           }
           array_push($columnnames,'Lookup');
           $cntaddlfield=0;
           foreach($arraymonthno AS $arrmonthno=>$arrmonthname){
             if($currentyr.'-'.$arrmonthno.'-01'<=date('Y-m-d')){
               $sqlmonthno.='(SELECT IF(pf.Posted=1,TRUNCATE(SUM(SuperScore*(Weight/100)),2),"") AS TotScore FROM hr_82perfevalmonthlymain pf2 JOIN hr_82perfevalmonthlysub ps2 ON pf2.TxnID=ps2.TxnID WHERE IDNo=pf.IDNo AND MonthNo='.$arrmonthno.') AS `'.$arrmonthname.'`,';
               $columnnames[].=$arrmonthname;
               $cntaddlfield++;
             }
           }
           
           
           $sql='SELECT pf.IDNo,CONCAT("<a href=\"newperfevalformmonthly.php?IDNo=",pf.IDNo,"\">Lookup</a>") AS Lookup,IF(cp.deptid IN (2,10),Branch,dept) AS Branch,cp.Position,pf.IDNo AS TxnID,'.$sqlmonthno.' CONCAT(e1.FirstName, " ", e1.Surname) AS FullName
            FROM hr_82perfevalmonthlymain pf   
            JOIN `1employees` e1 ON e1.IDNo=pf.IDNo

            LEFT JOIN `attend_30currentpositions` cp ON cp.IDNo=pf.IDNo
            LEFT JOIN `1employees` e2 ON e2.IDNo=pf.SIDNo

            JOIN `1companies` c ON c.CompanyNo=e1.RCompanyNo
            '; 
 
           if($cntaddlfield>=1 AND $cntaddlfield<=3){
             $width='55%';
           } else if($cntaddlfield>=4 AND $cntaddlfield<=6){
             $width='70%';
           } else if($cntaddlfield>=7 AND $cntaddlfield<=9){
             $width='85%';
           } else {
             $width='100%';
           }

          $sql=$sql.$evalcondi.' GROUP BY pf.IDNo'; 
          
        //   $addlprocess2='newperfevalformmonthly.php?IDNo='; $addlprocesslabel2='Lookup';
        //   $txnid='TxnID';
         
        include('../backendphp/layout/displayastable.php'); 
      
          break;

        
   case 'History':
       if (!allowedToOpen(685,'1rtc')) {   echo 'No permission'; exit;}
       
      $title='Evaluation History';
      if(!isset($_POST['FullName'])){
            echo '<title>'.$title.'</title>';
      }
      ?>
      <form method="post" action="newperfeval.php?w=History" enctype="multipart/form-data">
		Name:  <input type="text" name="FullName" list='employees'></input>
		<input type="submit" name="lookup" value="Lookup"><br>
      <?php
      if (isset($_POST['FullName'])){
	 $title=$title.' - '.$_POST['FullName'];
	 $idno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['FullName']),'IDNo');
	 $columnnames=array_diff($columnnameslist,array('IDNo','FullName','Company','EComment','HREncodedBy','HR_Status','HRStatusTS'));
         if(allowedToOpen(686,'1rtc')) { $cond='';}
         elseif (allowedToOpen(100,'1rtc')) { $cond=' AND pf.DIDNo='.$_SESSION['(ak0)'];}
         else { $cond=' AND pf.SIDNo='.$_SESSION['(ak0)'];}
	 $sql=$sql.'WHERE pf.IDNo='.$idno.$cond.' ORDER BY EvalDueDate DESC'; 
         $addlprocess='newperfevalform.php?TxnID='; $addlprocesslabel='Lookup';
       
	 include('../backendphp/layout/displayastablenosort.php'); 
      }
	  // echo $sql;
      break;
      
    case 'AutoEncodeYrEval':
       if (!allowedToOpen(686,'1rtc')) {   echo 'No permission'; exit;}

    //if papalitan evalduedate.
	 if(isset($_POST['autoencodesemiyrend'])){ //semiannual
         $datecheck=5;
         $gcond='';
         $month=5;
         $dividedby=2;
     } else { //annual
        $datecheck=11;
        $gcond='';
        $month=11;
        $dividedby=1;
     }
    if(date('m')<$datecheck){
		echo 'Month No should be greater than or equal to '.$datecheck.'.'; exit();
	}
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
      $sql='INSERT INTO `hr_82perfevalmain` 
          (`IDNo`,`CurrentPositionID`,`CurrentBranchNo`,`SIDNo`,`DIDNo`,`EvalDueDate`, `EvalAfterDays`,`HREncodedByNo`,`HRTimestamp`)  
          SELECT e.`IDNo`, `PositionID`, `DefaultBranchAssignNo`, (IF((p.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=p.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=p.IDNo))) AS SIDNo, 
          (SELECT IF(a.IDNo=e.IDNo,a.LatestSupervisorIDNo,a.IDNo) FROM `attend_30currentpositions` a 
          WHERE a.positionid=(SELECT `deptheadpositionid` FROM `attend_30currentpositions` b WHERE b.IDNo=e.IDNo)) AS `DIDNo`,
          \''.$currentyr.'-'.$month.'-27\' AS `EvalDueDate`, ((SELECT YEAR(`DataClosedBy`) FROM `00dataclosedby` WHERE `ForDB`=1)/'.$dividedby.') AS `EvalAfterDays`, 
          '.$_SESSION['(ak0)'].' AS `EncodedByNo`, Now() AS `Timestamp`
	    FROM `1employees` e JOIN `attend_30currentpositions` p ON e.IDNo=p.IDNo
	    JOIN `attend_1defaultbranchassign` dba ON e.IDNo=dba.IDNo WHERE e.Resigned=0 AND e.IDNo>1002;';
       $stmt=$link->prepare($sql); $stmt->execute();
      
    $sqlpopultatedtoday='SELECT pem.TxnID,
    
    (SELECT FormID FROM hr_81perfevalforms WHERE FIND_IN_SET(cp.PositionID,Positions))
    
     AS FormID
      FROM attend_30currentpositions cp JOIN hr_82perfevalmain pem ON cp.IDNo=pem.IDNo WHERE EvalAfterDays='.($currentyr/$dividedby).'';
    $stmt=$link->query($sqlpopultatedtoday); $res=$stmt->fetchAll();
		foreach ($res AS $row){
            $sql='INSERT INTO `hr_82perfevalsub` (`TxnID`,`CID`) SELECT '.$row['TxnID'].',CID FROM hr_81corecompetencies WHERE FormID="'.$row['FormID'].'";'; 
              $stmt=$link->prepare($sql); $stmt->execute();  
        }


        $sql='INSERT IGNORE INTO hr_82perfevalmonthlymain (IDNo,MonthNo,SIDNo,EncodedByNo,TimeStamp) SELECT cp.IDNo,'.$month.',(IF((cp.deptid<>10),(SELECT cp3.LatestSupervisorIDNo FROM `attend_30currentpositions` cp3 WHERE cp3.IDNo=cp.IDNo),(SELECT OpsSpecialist FROM attend_30currentpositions cp4 JOIN attend_1branchgroups bg ON cp4.BranchNo=bg.BranchNo WHERE cp4.IDNo=cp.IDNo))),0,NOW() from attend_30currentpositions cp JOIN hr_82perfevalmain pem ON cp.IDNo=pem.IDNo WHERE EvalAfterDays='.($currentyr/$dividedby).'';
        $stmt=$link->prepare($sql); $stmt->execute();
          
  
      $sqlpopultatedtoday='SELECT pemm.TxnID,
      
      (SELECT FID FROM hr_82fcmain WHERE FIND_IN_SET(cp.PositionID,DefaultPositions))
      
       AS FID
        FROM attend_30currentpositions cp JOIN hr_82perfevalmonthlymain pemm ON cp.IDNo=pemm.IDNo JOIN hr_82perfevalmain pem ON pemm.IDNo=pem.IDNo WHERE EvalAfterDays='.$currentyr.'';
  
      $stmt=$link->query($sqlpopultatedtoday); $res=$stmt->fetchAll();
              foreach ($res AS $row){
              $sql='INSERT IGNORE INTO `hr_82perfevalmonthlysub` (`TxnID`,`FCID`,`Weight`) SELECT '.$row['TxnID'].',FCID,DefaultWeight FROM hr_82fcsub WHERE FID="'.$row['FID'].'";'; 
                $stmt=$link->prepare($sql); $stmt->execute(); 
          }
 
      header("Location:".$_SERVER['HTTP_REFERER']);
      break;
      
      case 'EvaluationSummary':
          if (!allowedToOpen(array(686,100,685),'1rtc')) { echo 'No permission'; exit;}
	    $title='Evaluation Per Dept Summary';
	  ?><div style="margin: 15px; background: #FFFFFF;padding:5px;">
<h4><br>Complete Evaluation Process</h4><ol style="margin: 20px; ">
            <li><b>Self-evaluation</b> - must end with "Set as Completed"</li>
            <li><b>Supervisor evaluation</b> - evaluation is available to supervisor only if employee has finished. Supervisor must also set as completed.</li>
<!--            <li><b>Other Departments</b> - some positions are evaluated by other departments. This can be done anytime before the employee acknowledges.</li>-->
            <li><b>Dept Head confirmation</b> - department head comments and confirms evaluation.</li>
            <li><b>Employee acknowledgement and commitment</b> - The employee must set as AGREE or DISAGREE to the evaluation of the supervisor. He/she is encouraged to write his/her commitment in reaction to the supervisor's evaluation.</li>
            <li><b>HR status</b> - HR must print, file into 201, and set status online evaluation as FINISHED. Both HR and respective department heads must sign on the print out so they are aware of improvement plans.</li>
			
</ol><hr><?php
if (allowedToOpen(100,'1rtc')){
$method='POST';
echo '<title>'.$title.'</title>';
echo '<br><h3>Add Special Evaluation</h3>';

 echo comboBox($link,'SELECT e.IDNo, CONCAT(Nickname, " ", Surname) AS FullName FROM `1employees` e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE deptheadpositionid='.$_SESSION['&pos'].' ORDER BY Nickname;','IDNo','FullName','employees');


echo '<form action="newperfeval.php?w=Add&action_token='.$_SESSION['action_token'].'" method="POST">Add Special Evaluation for <input type="text" value="" name="FullName" list="employees" required> Evaluation Date: <input type="date" value="" name="EvalDueDate" value="" required> <input type="submit" name="btnAdd" value="Add new"></form>';
	 
}
?></div>
<?php
		if (allowedToOpen(100,'1rtc')) 
		{
			$condi = ' WHERE (DIDNo='.$_SESSION['(ak0)'].' OR SIDNo='.$_SESSION['(ak0)'].')';
         
		}
		else {
			$condi = ' WHERE ((((SIDNo='.$_SESSION['(ak0)'].'))))';
		}
		if(allowedToOpen(686,'1rtc')){
			$addlprocess='newperfeval.php?w=EditSpecifics&TxnID='; $addlprocesslabel='Edit';
		}
			  $sql0 = $sql . $condi. ' AND e1.Resigned=0 '; 
		  
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor','Emp_Response','HR_Status');
         $editprocess='newperfevalform.php?TxnID='; $editprocesslabel='Lookup';
         $subtitle='Unfinished SELF-Evaluation';
         $sql=$sql0.' AND EStat=0';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor');
	 include('../backendphp/layout/displayastablenosort.php'); 
         $subtitle='<br/>Unfinished SUPERVISOR Evaluation';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor');
         if (allowedToOpen(100,'1rtc')){ unset($addlprocess,$addlprocesslabel);}
         $sql=$sql0.' AND EStat=1 AND SStat=0';
	 include('../backendphp/layout/displayastableonlynoheaders.php');

         $subtitle='<br/>Unfinished DEPT HEAD Confirmation';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor');
         $sql=$sql0.' AND EStat=1 AND SStat=1 AND DStat=0';
	 include('../backendphp/layout/displayastableonlynoheaders.php');
         $subtitle='<br/>No EMPLOYEE Acknowledgment';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor');
         $sql=$sql0.' AND EStat=1 AND SStat=1 AND DStat=1 AND Ack=0';
	 include('../backendphp/layout/displayastableonlynoheaders.php');
         $subtitle='<br/>HR to Finalize';
         $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor','Emp_Response','HR_Status');
         $sql=$sql0.' AND EStat=1 AND SStat=1 AND DStat=1 AND Ack<>0 AND HRStatus=0';
	 include('../backendphp/layout/displayastableonlynoheaders.php');
         
      break;
      
      
	  
  case 'MyEval':
      $title='My Evaluation History';
      $sql.=' WHERE pf.IDNo='.$_SESSION['(ak0)'];	
         $addlprocess='newperfevalform.php?TxnID='; $addlprocesslabel='Lookup';
	 include('../backendphp/layout/displayastablenosort.php'); 
      
      break;
  

case 'FinishedEval':
	 if (!allowedToOpen(array(100,686),'1rtc')) {   echo 'No permission'; exit;}
	 include_once '../backendphp/layout/linkstyle.php';
	
         if (allowedToOpen(686,'1rtc')) { $cond='';} else { $cond=' AND DIDNo='.$_SESSION['(ak0)']; }
	 $title='Finished Evaluations';
  
	 $editprocess='newperfevalform.php?TxnID='; $editprocesslabel='Lookup';
	 
	 $columnnames=array('IDNo','FullName','DateHired','CurrentBranch','CurrentPosition','EvalAfterDays','EvalDueDate','Supervisor','Emp_Response');
	
	 $monthno=date("m"); $ytoday=$currentyr;
	 if (isset($_POST['btnShow']))
	 {
		$monthno = $_POST["monthno"];
	 }
	 $formdesc='</i><form action="#" method="POST" style="display: inline;">Month: <input type="number" name="monthno" size=3 min="1" max="12" value="'.$monthno.'"/>&nbsp;<input type="submit" value="Show per Month" name="btnShow"></form>&nbsp;&nbsp;<form action="#" method="POST" style="display: inline;"><input type="submit" value="Show All" name="btnShowAll"></form><i>';
         
         if (isset($_POST['btnShowAll'])) { 
             $sql=$sql.' WHERE EStat=1 AND SStat=1 AND DStat=1 AND Ack<>0 AND HRStatus=1 '.$cond.' '; 
	 } else {
	 
	 $sql=$sql.' WHERE EStat=1 AND SStat=1 AND DStat=1 AND Ack<>0 AND HRStatus=1 '.$cond.' AND (EvalDueDate>="'.$ytoday.'-'.$monthno.'-01'.'" AND EvalDueDate<=LAST_DAY("'.$ytoday.'-'.$monthno.'-01'.'"))'; 
         }
	 $width='100%';
	 include('../backendphp/layout/displayastable.php'); 
	  
break;

case 'DeletePage':

if (!allowedToOpen(686,'1rtc')) {   echo 'No permission'; exit;}           
          
		$title='Delete Performance Evaluation';
		$columnnames=array('IDNo','FullName','DateHired','CurrentBranch','EvalAfterDays','EvalDueDate','Supervisor');
		
		echo comboBox($link,$sql.' WHERE pf.SStat=0 AND e1.Resigned<>1 ORDER BY FullName;','FullName','IDNo','employeeswithsuperzero');
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3><br>';
		echo '<form action="newperfeval.php?w=Delete" method="POST">';
		echo '<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
		echo 'IDNo: <input type="text" name="IDNo" list="employeeswithsuperzero" autocomplete="off" required>';
		echo ' EvalDueDate: <input type="date" name="EvalDueDate" value="'.date('Y-m-d').'">';
		echo ' <input type="submit" value="Delete Evaluation" name="btnDelete" OnClick="return confirm(\'Are you sure you want to delete?\');">';
		echo '</form>';
		 
	  
break;


case 'EvaluatorGuide':

if (!allowedToOpen(685,'1rtc')) {   echo 'No permission'; exit;}

?>
        <title>Evaluator Guide</title>
        <div style=' background-color: FFFFFF;'>            
        <div style='margin-left: 30%;'><br><br>
	<h3>Evaluator Guide for One-on-One</h3><br><br>
        <h3 style='color: darkgreen;'>Every performance evaluation is an opportunity to help our people improve. Let us maximize this.</h3><br><br>
        <i>You may use these statements as starting points in discussing your evaluatee's performance for the year:</i><br><br>
        <div style='margin-left: 5%; font-size: large; line-height: 200%;'>
        <ol>
            <li>In general, you did well/not well because ...</li>
            <li>Specific examples this year that showed your performance are ...</li>
            <li>Your biggest achievement this year was ...</li>
            <li>For next year, I challenge you ...</li>
        </ol>
            <br><br>
        </div></div></div>
<?php		 
	  
break;

case 'EditStatements':
    if (!allowedToOpen(100,'1rtc')) {   echo 'No permission'; exit; }

    $rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
    $rcolor[1]="FFFFFF";
    $colorcount=0;
    echo '<style>
    body {font-family:sans-serif;}
    
    table,th,td {border:1px solid black;border-collapse:collapse; padding: 5px !important; font-size: small !important; overflow:auto;}
    #legendcell { font-size: 8pt;}
    #main {  width:100%;  margin:0 auto; clear: both;}
   
</style>';
echo '<br>';

$title='Edit Monthly Evaluation';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3>';
$sqlmain='SELECT CONCAT(Nickname, " ", Surname) AS `Supervisor` FROM hr_82perfevalmonthlymain pemm JOIN 1employees e ON pemm.SIDNo=e.IDNo WHERE pemm.TxnID='.intval($_GET['TxnID']).'';
$stmtsuper=$link->query($sqlmain);
$rowsuper = $stmtsuper->fetch();

echo comboBox($link,'SELECT e.IDNo, CONCAT(Nickname, " ", Surname) AS FullName FROM `1employees` e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE deptheadpositionid='.$_SESSION['&pos'].' ORDER BY Nickname;','IDNo','FullName','supervisorlist');
echo '<br><form method="POST" action="newperfeval.php?w=EditSupervisor&TxnID='.intval($_GET['TxnID']).'"><input type="text" value="'.$rowsuper['Supervisor'].'" name="SIDNo" list="supervisorlist"> <input type="submit" value="Edit Supervisor" name="btnAdd"></form><br><br>';


    echo '<table>';
echo '<tr><th>Statement</th><th>Weight in %</th><th colspan=2></th>';
	$sqlcore='SELECT pems.TxnSubId,PositionID,pemm.IDNo,pemm.Posted,pems.FCID,SuperScore,`Weight` FROM hr_82perfevalmonthlymain pemm JOIN hr_82perfevalmonthlysub pems ON pemm.TxnID=pems.TxnID JOIN hr_82fcsub fv ON pems.FCID=fv.FCID JOIN attend_30currentpositions cp ON pemm.IDNo=cp.IDNo WHERE pemm.TxnID = '.intval($_GET['TxnID']).' ORDER BY OrderBy'; 
	$stmtcore=$link->query($sqlcore);
	$rowcore = $stmtcore->fetchALL();
	

	foreach($rowcore AS $rowco){

        $sqlforstmt='SELECT FCID,Statement FROM hr_82fcsub fs JOIN hr_82fcmain fm ON fs.FID=fm.FID WHERE Active=1 AND FIND_IN_SET('.$rowco['PositionID'].',DefaultPositions) ORDER BY OrderBy';
       
        $stmtstmt=$link->query($sqlforstmt);
        $rowstmt = $stmtstmt->fetchALL();

        $statements='';
        foreach($rowstmt AS $statement){
            $statements.='<option value="'.$statement['FCID'].'" '.($statement['FCID']==$rowco['FCID']?'selected':'').'>'.$statement['Statement'].'</option>';
        }


		echo '<tr bgcolor="'. $rcolor[$colorcount%2].'"><td><form method="POST" action="newperfeval.php?w=EditSubMonthly"><select name="FCID">'.$statements.'</td><td><input type="text" name="Weight" value='.$rowco['Weight'].' size=5></td>';
			echo '<input type="hidden" name="TxnSubId" value="'.$rowco['TxnSubId'].'" /><td><input type="submit" name="btnEdit" value="Edit"></td><td><input type="submit" name="btnDel" value="Delete" onclick="return confirm(\'Are you sure? This action cannot be undone.\')"></td></form>';
		
		
		echo '</tr>';
		$colorcount++;
        $statements='';
		
	}
	
			

    break;


  case 'EditSupervisor':
if (!allowedToOpen(100,'1rtc')) {   echo 'No permission'; exit; }
    $sql = "UPDATE hr_82perfevalmonthlymain SET SIDNo=".comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['SIDNo']),'IDNo')." WHERE Posted=0 AND TxnID =  ".intval($_GET['TxnID'])."";

        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);

    break;


    case 'EditSubMonthly':
        if (!allowedToOpen(100,'1rtc')) {   echo 'No permission'; exit; }
        $txnsubid=$_POST['TxnSubId'];
        if(isset($_POST['btnEdit'])){
            $sql='UPDATE hr_82perfevalmonthlysub SET FCID='.$_POST['FCID'].',Weight="'.$_POST['Weight'].'" WHERE TxnSubId='.$txnsubid;
        } else {
            $sql='DELETE FROM hr_82perfevalmonthlysub WHERE TxnSubId='.$txnsubid;
        }
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
}
 
?>
</body></html>