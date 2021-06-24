<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php');



$which=(!isset($_GET['which'])?'Request':$_GET['which']);
switch ($which){
   case 'Request':
       if (!allowedToOpen(6490,'1rtc')) {   echo 'No permission'; exit;} 
            ?>
        <html><head><title>Personnel Request</title></head>
        <body><h4>Personnel Request</h4><br><br>
        <?php
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        ?>
        <form method='post' action='personnelrequest.php?which=Submit'>
            For Branch/Department <input type='text' name='Entity' required=true list='entities' size=8><font color='red'>*</font>&nbsp &nbsp
            Position <input type='text' name='Position' required=true list='positions' size=8><font color='red'>*</font>&nbsp &nbsp
            Specific Requests & Qualifications<input type='text' name='Remarks' size=20>&nbsp &nbsp &nbsp
            Target Date <input type='date' name='TargetDate' required=true value='<?php echo date('Y-m-d', strtotime('+1 month', (strtotime(date('Y-m-d'))))); ?>'><font color='red'>*</font>&nbsp &nbsp &nbsp
            <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" />
            <input type='submit' name='Submit' value='Submit'>
        </form>
        <?php
        echo comboBox($link,'SELECT * FROM `acctg_1budgetentities` ORDER BY Entity;','EntityID','Entity','entities');
        echo comboBox($link,'SELECT * FROM attend_1positions ORDER BY Position;','PositionID','Position','positions');
       
		$condc=(!allowedToOpen(64901,'1rtc')?' WHERE cp.`deptheadpositionid`=(SELECT cp2.deptheadpositionid FROM attend_30currentpositions cp2 WHERE IDNo='.$_SESSION['(ak0)'].') AND ':' WHERE ');
		
		
		
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Entity, Position');
		
		$sqlt='create temporary table checkifexists (`TxnID` INT(11) NOT NULL,JobDesc VARCHAR(100) DEFAULT NULL)';
		$stmtt=$link->prepare($sqlt); $stmtt->execute();
			
		$sqlfetch='SELECT TxnID,CONCAT("'.$path.'/acrossyrs/commonfiles/jobdesc/JD_",LPAD(PositionID,3,0),".pdf") AS FileExistsLink,CONCAT("<a href=\"../../acrossyrs/commonfiles/jobdesc/JD_",LPAD(PositionID,3,0),".pdf target=\"_blank\">JD_ ",LPAD(PositionID,3,0),"</a>") AS JobDescLink FROM hr_2personnelrequest';
		$stmtfetch=$link->query($sqlfetch); $resultfetch=$stmtfetch->fetchAll();
			
		foreach($resultfetch as $resfetch){
			$fileexists=((file_exists(addslashes(str_replace("//","/",$resfetch['FileExistsLink'])))?$resfetch['JobDescLink'].date (" Y-m-d", filemtime($resfetch['FileExistsLink'])):"No Job Desc"));
			
			$sqlit='INSERT INTO checkifexists set TxnID=\''.$resfetch['TxnID'].'\',JobDesc=\''.$fileexists.'\';';
			$stmtit=$link->prepare($sqlit); $stmtit->execute();
			
		}
		/* 
        $sqlmain='SELECT pr.*,CONCAT("'.$path.'acrossyrs/commonfiles/jobdesc/JD_",LPAD(p.PositionID,3,0),".pdf") AS JobDesc,CONCAT("<a href=\"../../acrossyrs/commonfiles/jobdesc/JD_",LPAD(p.PositionID,3,0),".pdf target=\"_blank\">JD_ ",LPAD(p.PositionID,3,0),"</a>") AS JobDescLink, Entity,(CASE
		WHEN RequestStat=0 THEN "Pending"
		WHEN RequestStat=1 THEN "For Final Interview"
		WHEN RequestStat=2 THEN "Pre Employment"
		WHEN RequestStat=3 THEN "Deployed"
		ELSE "Backed Out"
		END) AS RequestStat,DATE(pr.`TimeStamp`) AS RequestedOn,PersonHired AS Applicant,p.Position, TargetDate, CONCAT(e.Nickname," ",e.SurName) as RequestedBy, pr.TimeStamp as RequestTS FROM hr_2personnelrequest pr JOIN attend_1positions p ON p.PositionID=pr.PositionID LEFT JOIN `1employees` e ON e.IDNo=pr.EncodedByNo JOIN `acctg_1budgetentities` be ON be.EntityID=pr.EntityID 
LEFT JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN checkifexists cie ON pr.TxnID=cie.TxnID
'.$condc.' '; */
        $sqlmain='SELECT pr.*,JobDesc,Entity,(CASE
		WHEN RequestStat=0 THEN "Pending"
		WHEN RequestStat=1 THEN "For Final Interview"
		WHEN RequestStat=2 THEN "Pre Employment"
		WHEN RequestStat=3 THEN "Deployed"
		ELSE "Backed Out"
		END) AS RequestStat,
		
		
		DATEDIFF(IF(RequestStat=3,StartingDate,CURDATE()),DATE(pr.`TimeStamp`)) As AgeOfReqInDays,
		
		DATE(pr.`TimeStamp`) AS RequestedOn,PersonHired AS Applicant,p.Position, TargetDate, CONCAT(e.Nickname," ",e.SurName) as RequestedBy, pr.TimeStamp as RequestTS FROM hr_2personnelrequest pr JOIN attend_1positions p ON p.PositionID=pr.PositionID LEFT JOIN `1employees` e ON e.IDNo=pr.EncodedByNo JOIN `acctg_1budgetentities` be ON be.EntityID=pr.EntityID 
LEFT JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN checkifexists cie ON pr.TxnID=cie.TxnID
'.$condc.' ';
		
		// $fileexistfield='JobDesc'; $fileexistmsg='No Job Desc'; $fileexistlink='JobDescLink';
		// echo $sqlmain;
		
		
		
		
		
        $columnnames=array('Entity', 'Position', 'Remarks','JobDesc', 'TargetDate', 'RequestedBy', 'RequestedOn','AgeOfReqInDays', 'HRComment','Applicant','StartingDate','RequestStat');
		$columnstoeditoption=array('HRComment','Applicant');
		
		$columnstoeditselect=array('RequestStat');

		$sqlforselect = 'SELECT 0 AS StatID,"Pending" AS RequestStat UNION SELECT 1 AS StatID,"For Final Interview" AS RequestStat UNION SELECT 2 AS StatID,"Pre Employment" AS RequestStat UNION SELECT 3 AS StatID,"Deployed" AS RequestStat UNION SELECT 4 AS StatID,"Backed Out" AS RequestStat;';
		$stmtselect = $link->query($sqlforselect);
		$options='';

		while($rowselect = $stmtselect->fetch())
		{
			$options .= '<option value="'.$rowselect['StatID'].'">'.$rowselect['StatID'].' - '.$rowselect['RequestStat'].'</option>';
		}
		
		
		$defaultview='';
		if(isset($_POST['ShowAll'])){
			$defaultview='';
			$inform='<input type="submit" value="Show Default" name="ShowDefault">';
		}
		else {
			if (allowedToOpen(64903,'1rtc')){ //Stores/warehouse Default
				$defaultview=' AND p.deptid IN (10) ';
			}
			if (allowedToOpen(64904,'1rtc')){
				$defaultview=' AND p.deptid NOT IN (10) ';
			}
			$inform='<input type="submit" value="Show ALL" name="ShowAll">';
		}
		
		if(isset($_POST['ShowBranches'])){
			$defaultview=' AND p.deptid IN (10) ';
		}
		if (allowedToOpen(64902,'1rtc')){
				$inform.='&nbsp; &nbsp; &nbsp; <input type="submit" value="Show Branches" name="ShowBranches">';
			}
		
		
        $title='Pending Requests';
        $delprocess='personnelrequest.php?which=DeleteRequest&TxnID=';
		$sql=$sqlmain.' RequestStat=0 '.$defaultview.' ORDER BY Entity, Position';
		
		
		// $sqlcheckmax='SELECT DATEDIFF(CURDATE(),DATE(pr.`TimeStamp`)) As AgeOfReqInDays FROM hr_2personnelrequest pr JOIN attend_1positions p ON p.PositionID=pr.PositionID JOIN 1employees e ON pr.EncodedByNo=e.EncodedByNo WHERE RequestStat=0 '.$defaultview.' ORDER BY AgeOfReqInDays DESC LIMIT 1';
		$sqlcheckmax='SELECT DATEDIFF(CURDATE(),DATE(pr.`TimeStamp`)) As AgeOfReqInDays FROM hr_2personnelrequest pr JOIN attend_1positions p ON p.PositionID=pr.PositionID LEFT JOIN `1employees` e ON e.IDNo=pr.EncodedByNo JOIN `acctg_1budgetentities` be ON be.EntityID=pr.EntityID 
LEFT JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN checkifexists cie ON pr.TxnID=cie.TxnID
'.$condc.' RequestStat=0 '.$defaultview.' ORDER BY AgeOfReqInDays DESC LIMIT 1';


		$statementcheckmax = $link->prepare($sqlcheckmax);
		$statementcheckmax->execute();
		$result = $statementcheckmax->fetch();
		// echo $sqlcheckmax;
		// $txnidname='PositionID';
		// $addlprocess='test'; $addlprocesslabel='Lookup';
		// $formdesc='test';
		if (allowedToOpen(array(64901),'1rtc')){
			// echo $condc;
			$formdesc='</i><br>&nbsp; <b><u>Max Age Of Request In Days: <font style="color:red;">'.$result['AgeOfReqInDays'].'</font></u></b><br><br><form action="#" method="POST">'.$inform.'</form><i>';
		}
        if (allowedToOpen(64901,'1rtc')){
		$columnnames=array_diff($columnnames,array('StartingDate'));
         $columnstoedit=$columnstoeditoption; $columnsub=$columnnames;
         $columnsub=$columnnames;
         $editprocess='personnelrequest.php?which=HRNoted&TxnID='; $editprocesslabel='Enter'; $txnidname='TxnID';
		 
         include('../backendphp/layout/displayastableeditcellswithsorting.php');
        } else {
			include('../backendphp/layout/displayastable.php');
		}
		$formdesc='';
		echo '<br>';
		$sql=$sqlmain.' RequestStat=1 ORDER BY Entity, Position';
		
        $title='For Final Interview';
        $delprocess='personnelrequest.php?which=DeleteRequest&TxnID=';
        if (allowedToOpen(64901,'1rtc')){
			$columnnames=array_diff($columnnames,array('StartingDate'));
         $columnstoedit=$columnstoeditoption; $columnsub=$columnnames;
         $editprocess='personnelrequest.php?which=HRNoted&TxnID='; $editprocesslabel='Enter'; $txnidname='TxnID';
		 
         include('../backendphp/layout/displayastableeditcellswithsorting.php');
        } else {
			include('../backendphp/layout/displayastable.php');
		}
		$columnnames=array('Entity', 'Position', 'Remarks','JobDesc', 'TargetDate', 'RequestedBy', 'RequestedOn','AgeOfReqInDays','HRComment','PersonHired','StartingDate','RequestStat');
		$columnstoeditoption=array('HRComment','PersonHired','StartingDate');
		echo '<br>';
		$sql=$sqlmain.' RequestStat=2 ORDER BY Entity, Position';
        $title='Pre Employment';
        $delprocess='personnelrequest.php?which=DeleteRequest&TxnID=';
		$hidecount=true;
        if (allowedToOpen(64901,'1rtc')){
         $columnstoedit=$columnstoeditoption; $columnsub=$columnnames;
         $editprocess='personnelrequest.php?which=HRNoted&TxnID='; $editprocesslabel='Enter'; $txnidname='TxnID';
		 
        include('../backendphp/layout/displayastableeditcellswithsorting.php');
        } else {
			include('../backendphp/layout/displayastable.php');
		}
		echo '<br>';
		$sql=$sqlmain.' RequestStat=3 ORDER BY Entity, Position';
        $title='Deployed';
		unset($columnstoedit,$delprocess,$editprocess,$editprocesslabel,$columnstoeditselect);
		$columnnames=array_diff($columnnames,array('RequestStat','AgeOfReqInDays'));
        include('../backendphp/layout/displayastable.php');
		
		echo '<br>';
		$columnnames=array_diff($columnnames,array('StartingDate','AgeOfReqInDays'));
		$sql=$sqlmain.' RequestStat=4 ORDER BY Entity, Position';
        $title='Backed Out';
		include('../backendphp/layout/displayastable.php');
		
        break;
		
		
    case 'Submit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $entity=comboBoxValue($link,'`acctg_1budgetentities`','Entity',addslashes($_POST['Entity']),'EntityID');
        $position=comboBoxValue($link,'attend_1positions','Position',addslashes($_POST['Position']),'PositionID');
        $columnstoadd=array('Remarks','TargetDate'); $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `hr_2personnelrequest` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' EntityID='.$entity.', PositionID='.$position.', TimeStamp=Now()';
        $stmt=$link->prepare($sql); $stmt->execute();
        $sql='SELECT TxnID FROM `hr_2personnelrequest` WHERE EncodedByNo='.$_SESSION['(ak0)'].' AND PositionID='.$position.' AND EntityID='.$entity.' AND Remarks LIKE \''.addslashes($_POST['Remarks']).'\' AND TargetDate=\''.addslashes($_POST['TargetDate']).'\'';
        $stmt=$link->query($sql); $result=$stmt->fetch(); $txnid=$result['TxnID'];
        //header('Location:emailpersonnelrequest.php?which=SendForApproval&TxnID='.$txnid);
        header('Location:personnelrequest.php?which=Request');
        break;
    case 'DeleteRequest':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `hr_2personnelrequest` WHERE TxnID='.$_GET['TxnID'].' AND EncodedByNo='.$_SESSION['(ak0)'].' AND RequestStat=0';
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;    
  
    case 'HRNoted':
         require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		 
        $columnstoadd=array('HRComment');
		if(isset($_POST['StartingDate'])){
			 array_push( $columnstoadd,'StartingDate');
		 }
		if(is_numeric($_POST['RequestStat'])){
			array_push( $columnstoadd,'RequestStat');
		}
		$sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='UPDATE `hr_2personnelrequest` SET PersonHired="'.(isset($_POST['Applicant'])?$_POST['Applicant']:$_POST['PersonHired']).'",HRByNo='.$_SESSION['(ak0)'].', '.$sql.' HRTS=Now() WHERE TxnID='.$_GET['TxnID']; 
		
		// echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
}
  $link=null; $stmt=null;
?>
</body></html>