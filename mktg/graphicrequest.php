<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(5357,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

?>

<br><div id="section" style="display: block;">

<?php
include_once('../backendphp/layout/linkstyle.php');
			echo '<a id=\'link\' href="graphicrequest.php">List of Graphic Requests</a> ';
			echo '<a id=\'link\' href="graphicrequest.php?w=AddNewRequest">Add New Request</a> <br>';

$which=(!isset($_GET['w'])?'Lists':$_GET['w']);

$note='<i>Note: Upon approval of your request, you will receive a confirmation call from our department representative.</i><br><br>';

switch ($which)
{
	
	case 'Lists':
	
	 echo '<br>
	   <div style="background-color:#cccccc; width:63%; border: 1px solid black; padding:10px;" >
		<b>Approval Process:</b><br>
&nbsp; &nbsp; &nbsp; &nbsp; 1. Requester encodes all details, then submit.<br>
&nbsp; &nbsp; &nbsp; &nbsp; 2. Marketing Dept Head shall approve/deny online.<br>
<font style="font-size:9pt;">
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &bull; If approved, you will see the requirement target completion date.<br> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &bull; The request will no longer be editable once approved.<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &bull; If denied, you will get an email rationale why the request was denied.</font><br>
&nbsp; &nbsp; &nbsp; &nbsp; 3. Marketing sets the request as <b>Finished</b> once the requirement is delivered. The material
    should be emailed to the requester.<br>
&nbsp; &nbsp; &nbsp; &nbsp; 4. The requester sets the request as <b>Received</b> once the material is acquired by the requester.<br></div>';

	$cond='';
	if(allowedToOpen(array(5358,5359),'1rtc')){
		$sqlcnt='select COUNT(TxnID) AS cntduereq FROM mktg_2graphicreq WHERE (DateNeeded<=CURDATE() OR DateNeeded=(CURDATE() + INTERVAL 1 DAY)) AND Received=0;';
		$stmtcnt=$link->query($sqlcnt); $rescnt=$stmtcnt->fetch();
		if($rescnt['cntduereq']>0){
		echo '<br><br><div style="font-size:14pt;background-color:white;text-align:center;padding:10px;color:red;">1 or more requests are due today/tomorrow.</div>';
		}
		
		$cond=' WHERE 1=1';
	} else {
		$cond='WHERE (deptid=(SELECT DISTINCT(deptid) FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].') OR cp.LatestSupervisorIDNo='.$_SESSION['(ak0)'].')';
	}
	
	$filtercond='';
	if(isset($_POST['btnShowAll'])){
		$filtercond='';
		$atitle=' (All Requests)';
	} else {
		$filtercond=' AND Received=0';
		$atitle=' (Unfinished Requests)';
	}
	
	echo '<br><br><form action="#" method="POST">';
	if(isset($_POST['btnUnfinished']) OR !isset($_POST['btnShowAll'])){
		echo '<input type="submit" name="btnShowAll" value="Show All Requests">';
	} else {
		echo '<input type="submit" name="btnUnfinished" value="Show Unfinished Requests">';
	}
	echo '</form>';
	
	if (allowedToOpen(5357,'1rtc')) {
		$sql='SELECT gr.*,dept AS Department,FullName AS Requester,(CASE 
			WHEN IOrDOrT=0 THEN "External-Digital"
			WHEN IOrDOrT=1 THEN "External-Traditional"
			ELSE "Internal"
		END) AS GraphicRequest,(CASE 
			WHEN Approved = 0 THEN ""
			WHEN Approved = 1 THEN "Yes"
			ELSE "Denied"
		END) AS `Approved?`,
		
		(CASE 
			WHEN Finished = 0 THEN ""
			WHEN Finished = 1 THEN "Yes"
			ELSE "Denied"
		END) AS `Finished?`,(CASE 
			WHEN Received = 0 THEN ""
			WHEN Received = 1 THEN "Yes"
			ELSE "Denied"
		END) AS `Received?` FROM mktg_2graphicreq gr LEFT JOIN attend_30currentpositions cp ON gr.RequestedByNo=cp.IDNo '.$cond.' '.$filtercond.' ORDER BY Approved,TargetDate,DateFinished,ReceivedDate';
		// echo $sql;
		$columnnameslist=array('GraphicRequest','DateNeeded','Concept','Department','Requester','Approved?','TargetDate','Finished?','Received?');
		$title='List of Graphic Requests '.$atitle; 
               
		$delprocess='graphicrequest.php?w=DeleteRequest&TxnID=';
		$editprocess='graphicrequest.php?w=Lookup&TxnID='; $editprocesslabel='Lookup';
		$addlprocess='graphicrequest.php?w=EditRequest&TxnID='; $addlprocesslabel='Edit';
     
		$formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;
		
		$width='100%';
		
		 
		include('../backendphp/layout/displayastable.php'); 
	} else {
		echo 'No permission'; exit;
		}
		
	
	break;
	
	
	case 'DeleteRequest':
	if (allowedToOpen(5357,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `mktg_2graphicreq` WHERE TxnID='.intval($_GET['TxnID']).' AND Approved=0 AND RequestedByNo='.$_SESSION['(ak0)'].'';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
	header("Location:graphicrequest.php?w=Lists");
	} else {
		echo 'No permission'; exit;
		}
    break;
	
	case 'EditRequest':
	$title='Edit Request';
	echo '<title>'.$title.'</title>';
	echo '<br><br><h3>'.$title.'</h3>';
	$txnid=intval($_GET['TxnID']);
	$sql = 'SELECT gr.*,GRType AS Type FROM mktg_2graphicreq gr JOIN mktg_1graphictype gt ON gr.GRTypeID=gt.GRTypeID WHERE TxnID = '.$txnid; 
		$stmt=$link->query($sql); $res=$stmt->fetch();
		
		if($res['RequestedByNo']<>$_SESSION['(ak0)']){
			echo '<br><br>Only requester can edit request.';
			exit();
		}
		
		if($res['Approved']<>0){
			echo '<br><br>You cannot edit approved/denied request.';
			exit();
		}
	
	?>
	<br><table frame="box" class="hoverTable" width="650px" bgcolor="white" style="padding:5px;">
					<tr><td colspan="4" align="center"><h3><?php if ($res['IOrDOrT']==0) { echo '<font color="blue">External-Digital</font>'; } else if ($res['IOrDOrT']==2) { echo '<font color="maroon">Internal</font>'; } else { echo ' <font color="green">External-Traditional</font>'; } ?></h3><br></td></tr>
					
					<tr><td colspan="4"><b>Date of Request:</b> <?php echo $res['RequestDate'];?></td></tr>
					<tr><td colspan="4"><b>Date Needed:</b> <u><?php echo $res['DateNeeded'];?></u></td></tr>
					<tr><td colspan="4"><b>Concept:</b> <?php echo $res['Concept'];?></td></tr>
					<tr><td colspan="4"><b>Details/Purpose:</b> <?php echo $res['Details'];?></td></tr>
					
					
					
					<tr><td colspan="4"><b>Preferences:</b> <?php echo $res['Preferences'];?></td></tr>
					
					<tr><td colspan="4"><b>Type:</b> <?php echo $res['Type'];?></td></tr>
				
				
					
					<?php
					
					if($res['IOrDOrT']==0){
						echo '<tr><td colspan="4"><b>For online publish?</b> '.($res['OnlinePublish']==1?'Yes':'No').'</td></tr>';
					}
			echo '</table>';
	
	echo '
				<br><div style="border:1px solid black;width:630px;background-color:white;padding:10px;"><form action="graphicrequest.php?w=PREditRequest&TxnID='.$txnid.'" method="POST" autocomplete="off">
				<input type="hidden" value="'.$_SESSION['action_token'].'" name="action_token">
				DateNeeded: <input type="date" name="DateNeeded" value="'.$res['DateNeeded'].'" required><br>
				Concept: <input type="text" name="Concept" value="'.$res['Concept'].'" required><br>
				Details/Purpose: <input type="text" name="Details" value="'.$res['Details'].'" required><br>
				Preferences:<br><textarea name="Preferences" rows="3" cols="35">'.$res['Preferences'].'</textarea><br>';
				$sqlt='SELECT GRTypeID,GRType,0 AS OrderBy FROM mktg_1graphictype WHERE InExDT='.$res['IOrDOrT'].'  AND GRTypeID<>0  UNION SELECT 0,"Others",1 AS OrderBy ORDER BY OrderBy,GRType';
				$stmtt=$link->query($sqlt); $rest=$stmtt->fetchAll();
				$toptions='';
				foreach($rest AS $rest0){
					$toptions.='<option value="'.$rest0['GRTypeID'].'" '.($rest0['GRTypeID']==$res['GRTypeID']?'selected':'').'>'.$rest0['GRType'].'</option>';
				}
				
				echo 'Type: <select name="GRTypeID" required>
					<option value="">-- Select --</option>
					'.$toptions.'
				</select><br>';
				if($res['IOrDOrT']==0){
						echo 'For online publish? <input type="radio" name="OnlinePublish" value="1" '.($res['OnlinePublish']==1?'checked':'').'> Yes <input type="radio" name="OnlinePublish" value="0" '.($res['OnlinePublish']==0?'checked':'').'> No';
					}
				echo '
				<br><br><input type="submit" name="btnSubmit" value="Edit Request">
				</div>
				</form>';

	
	break;
	
	case 'Lookup':
		if (!allowedToOpen(5357,'1rtc')){ echo 'No Permission'; exit(); }
		$txnid=intval($_GET['TxnID']);
		
		$sql = 'SELECT gr.*,dept AS Department,FullName AS Requester,(CASE 
			WHEN IOrDOrT=0 THEN "External-Digital"
			WHEN IOrDOrT=1 THEN "External-Traditional"
			ELSE "Internal"
		END) AS GraphicRequest,GRType AS Type,(CASE 
			WHEN Approved = 0 THEN ""
			WHEN Approved = 1 THEN "Yes"
			ELSE "Denied"
		END) AS `Approved?`,IF(Finished=1,"Yes","") AS `Finished?`,IF(Received=1,"Yes","") AS `Received?` FROM mktg_2graphicreq gr LEFT JOIN attend_30currentpositions cp ON gr.RequestedByNo=cp.IDNo JOIN mktg_1graphictype gt ON gr.GRTypeID=gt.GRTypeID WHERE gr.TxnID = '.$txnid; 
		$stmt=$link->query($sql); $res=$stmt->fetch();
		
		if(allowedToOpen(array(5358,5359),'1rtc') OR $res['RequestedByNo']==$_SESSION['(ak0)']){
			
		} else {
			echo '<br><br>No Permission'; exit();
		}
		$title = 'Lookup Request';
	    ?><title><?php  echo $title; ?></title>
		
		<div>
		<div style="float:left;">
                <br><h2><?php  echo $title; ?></h2><br>
                <?php echo $note; ?>
                <style>.hoverTable tr:hover {
                        background-color: transparent;
						tr.border_bottom td {
						  border-bottom:1pt solid black;
						}
						
				</style><div>
	
					<table frame="box" class="hoverTable" width="650px" bgcolor="white" style="padding:5px;">
					<tr><td colspan="4" align="center"><h3><?php if ($res['IOrDOrT']==0) { echo '<font color="blue">External-Digital</font>'; } else if ($res['IOrDOrT']==2) { echo '<font color="maroon">Internal</font>'; } else { echo ' <font color="green">External-Traditional</font>'; } ?></h3><br></td></tr>
					
					<tr><td colspan="2"><b>Date of Request:</b> <?php echo $res['RequestDate'];?></td><td colspan="2" align="right"><b>Requested By:</b> <?php echo $res['Department'].' / '.$res['Requester'];?></td></tr><td colspan="4"><b>Date Needed:</b> <u><?php echo $res['DateNeeded'];?></u></td></tr>
					<tr>
					<tr><td colspan="4"><br><b>Concept:</b> <?php echo $res['Concept'];?></td></tr>
					
					<tr><td colspan="4"><b>Details/Purpose:</b> <?php echo $res['Details'];?></td></tr>
					
					
					
					<tr><td colspan="4"><b>Preferences:</b> <?php echo $res['Preferences'];?></td></tr>
					
					
					<?php
					// if($res['IOrDOrT']=='Digital'){
						// echo '<tr><td colspan="4"><b>For Online Publish?</b> '.$res['Type'].'</td></tr>';
					// } else {
						echo '<tr><td colspan="4"><b>Type: </b> '.$res['Type'].'</td></tr>';
						if($res['IOrDOrT']==0){
							echo '<tr><td colspan="4"><b>For online publish? </b> '.($res['OnlinePublish']==1?'Yes':'No').'</td></tr>';
						}
					// }
					
					if($res['TargetDate']<>'' AND $res['TargetDate']<>'0000-00-00'){
						echo '<tr><td colspan="4"><b>Target Date:</b> '.$res['TargetDate'].'</td></tr>';
					}
					if($res['DateFinished']<>'' AND $res['DateFinished']<>'0000-00-00'){
						echo '<tr><td colspan="4"><b>Date Finished:</b> '.$res['DateFinished'].'</td></tr>';
					}
					
					if($res['ReceivedDate']<>'' AND $res['ReceivedDate']<>'0000-00-00'){
						echo '<tr><td colspan="4"><b>Received Date:</b> '.$res['ReceivedDate'].'</td></tr>';
						echo '<tr><td colspan="4"><br><b style="color:orange">Request Completed</b></td></tr>';
					}
					
					/* if($res['Approved']==2){
						echo '<tr><td colspan="4"><br><b style="color:red;">Denied</b></td></tr>';
						exit();
					} */
					
					$token='<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
					?>
					<?php if($res['Approved']==0 AND allowedToOpen(5358,'1rtc')) { ?>
					<form action="graphicrequest.php?w=ApproveReturn&Requester=<?php echo $res['RequestedByNo'];?>&TxnID=<?php echo $_GET['TxnID'];?>" method="POST">
					<?php echo $token; ?>
					<tr><td colspan="4"><br><hr><br><b>Target Date:</b> <input type="date" name="TargetDate" value="<?php echo date('Y-m-d');?>"></td></tr>
					<tr><td colspan="2"><input type="submit" value="Approve" name="btnApprove" style="background-color:maroon;color:white;"></td><td colspan="2" align="right"><textarea name="creason" placeholder="Reason"></textarea><br><input type="submit" value="Return to Requester" name="btnReturn"  OnClick="return confirm('Are you sure?');"></td></tr>
					</form>
				<?php } ?>
					<?php if($res['Approved']==1 AND $res['Finished']==0 AND allowedToOpen(5358,'1rtc')) { ?>
					<form action="graphicrequest.php?w=FinishedInc&TxnID=<?php echo $_GET['TxnID'];?>" method="POST">
					<?php echo $token; ?>
					<tr><td colspan="4"><br><hr><br><b>Date Finished:</b> <input type="date" name="DateFinished" value="<?php echo date('Y-m-d');?>"></td></tr>
					<tr><td colspan="2"><input type="submit" value="Set As Finished" name="btnDone" style="background-color:maroon;color:white;"></td><td colspan="2" align="right"><input type="submit" value="Set As Incomplete" name="btnInc1"></td></tr>
					</form>
				<?php } ?>
					<?php if($res['Approved']==1 AND $res['Finished']==1  AND $res['Received']==0 AND $res['RequestedByNo']==$_SESSION['(ak0)']) { ?>
						<form action="graphicrequest.php?w=ReceivedInc&TxnID=<?php echo $_GET['TxnID'];?>" method="POST">
					<?php echo $token; ?>
					<tr><td colspan="4"><br><hr><br><b>Received Date:</b> <input type="date" name="ReceivedDate" value="<?php echo date('Y-m-d');?>"></td></tr>
					<tr><td colspan="2"><input type="submit" value="Received" name="btnReceived" style="background-color:maroon;color:white;"></td><td colspan="2" align="right"><input type="submit" value="Set As Incomplete" name="btnInc2"></td></tr>
					</form>
				<?php } ?>
						
				
					<?php
			echo '</table></div>';
			
	break;
	
	
	case 'AddNewRequest':
      if (!allowedToOpen(5357,'1rtc')) { echo 'No permission'; exit; }
$title='Add New Request'; 

echo '<title>'.$title.'</title>';
echo '<br><br>';
echo '<div style="background-color:#cccccc; width:63%; border: 1px solid black; padding:10px;" >
		<b>Request Guidelines:</b><br>
&nbsp; &nbsp; &nbsp; &nbsp; 1. Requester must fill out all details in <b>Add New Request</b> form, then submit.<br>
&nbsp; &nbsp; &nbsp; &nbsp; 2. Marketing channel platforms are provided based on the requirement needed. (Internal,
     Digital and Traditional).<br>
<font style="font-size:9pt;">
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &bull; Input the overall idea of the requirement in the <b>Concept</b> section.<br> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &bull; Encode the initial information of your requirement in the <b>Details/Purpose</b> section.<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &bull; You may include your preferred style/color/size/mood/etc in the <b>Preferences</b> section.<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &bull; Select your desired execution of graphic design medium in the <b>Type</b> section.<br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &bull; If the desired graphic design execution is not included in the options, indicate the medium of your choice in the <b>Preferences</b> section.</font><br></div><br>';

	 	$radionamefield='Radio'; 
		echo '<h3>'.$title.'</h3><br>';
	 echo'<div style="border:1px solid black; padding:10px; width:450px;"><form id="form-id">
		
			<b>INTERNAL</b> (Artwork, Video, Uniform): <input type="radio" id="watch-me3" name="'.$radionamefield.'" value="Type"><br><br>
		<u>EXTERNAL</u><br>
			&nbsp; &nbsp; <b>DIGITAL</b> (Online Artwork, Video, GIF, etc.): <input type="radio" id="watch-me2" name="'.$radionamefield.'" value="Type"><br>
			&nbsp; &nbsp; <b>TRADITIONAL</b> (Flyer, Poster, Tarpauline, etc.): <input type="radio" id="watch-me1" name="'.$radionamefield.'" value="Type">
		  </form></div></br>';
	include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';
	
	 
	$token='<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">';
	$submit='<input type="submit" name="submit" value="Submit" OnClick="return confirm(\'Is this final?\');">';	
	$concept='Concept: <input type="text" name="Concept" required><br>';
	$dateneeded='DateNeeded: <input type="date" name="DateNeeded" required><br>';
	$details='Details/Purpose: <input type="text" name="Details" required><br>';
	$preferences='Preferences (color/style/etc):<br><textarea name="Preferences" rows=3 cols=35 required></textarea><br>';
	//Traditional
	 	echo'<div style="display:none" id="show-me1">'.$note.'
	 <form method="post" action="graphicrequest.php?w=IOrDOrT&IOrDOrT=1" autocomplete="off"> 
	 <div style="background-color:white;border:1px solid black;padding:6px;width:35%;">
	 <h3 align="center" style="color:green;">External-Traditional</h3><br>
		'.$dateneeded.'
		'.$concept.'
		'.$details.'
		'.$preferences.'
		'.$token.'';
		$sqlt='SELECT GRTypeID,GRType,0 AS OrderBy FROM mktg_1graphictype WHERE InExDT=1 UNION SELECT 0,"Others",1 AS OrderBy ORDER BY OrderBy,GRType';
		$stmtt=$link->query($sqlt); $rest=$stmtt->fetchAll();
		$toptions='';
		foreach($rest AS $rest0){
			$toptions.='<option value="'.$rest0['GRTypeID'].'">'.$rest0['GRType'].'</option>';
		}
		
		echo 'Type: <select name="GRTypeID" required>
			<option value="">-- Select --</option>
			'.$toptions.'
		</select><br>
		'.$submit.'
		</div>
		  </form></div>';
	
	
		  
	// Digital
	 	echo'<div style="display:none" id="show-me2">'.$note.'
	 <form method="post" action="graphicrequest.php?w=IOrDOrT&IOrDOrT=0" autocomplete="off">
	 <div style="background-color:white;border:1px solid black;padding:6px;width:35%;">
	 
		<h3 align="center" style="color:blue;">External-Digital</h3><br>
		'.$dateneeded.'
		'.$concept.'
		'.$details.'
		'.$preferences.'
		'.$token.'';
		$sqld='SELECT GRTypeID,GRType,0 AS OrderBy FROM mktg_1graphictype WHERE InExDT=0 AND GRTypeID<>0 UNION SELECT 0,"Others",1 AS OrderBy ORDER BY OrderBy,GRType';
		$stmtd=$link->query($sqld); $resd=$stmtd->fetchAll();
		$doptions='';
		foreach($resd AS $resd0){
			$doptions.='<option value="'.$resd0['GRTypeID'].'">'.$resd0['GRType'].'</option>';
		}
		
		echo 'Type: <select name="GRTypeID" required>
			<option value="">-- Select --</option>
			'.$doptions.'
		</select><br>
		For online publish? <input type="radio" name="OnlinePublish" value="1" checked> Yes <input type="radio" name="OnlinePublish" value="0"> No <br>
		'.$submit.'
		</div>
		  </form></div>';
		  
		  
		  
		 
	// Internal
	 	echo'<div style="display:none" id="show-me3">'.$note.'
	 <form method="post" action="graphicrequest.php?w=IOrDOrT&IOrDOrT=2" autocomplete="off"> 
	 <div style="background-color:white;border:1px solid black;padding:6px;width:35%;">
	 <h3 align="center" style="color:maroon;">Internal</h3><br>
	 '.$dateneeded.'
		'.$concept.'
		'.$details.'
		'.$preferences.'
		'.$token.'
		';
$sqli='SELECT GRTypeID,GRType,0 AS OrderBy FROM mktg_1graphictype WHERE InExDT=2 UNION SELECT 0,"Others",1 AS OrderBy ORDER BY OrderBy,GRType';
		$stmti=$link->query($sqli); $resi=$stmti->fetchAll();
		$ioptions='';
		foreach($resi AS $resi0){
			$ioptions.='<option value="'.$resi0['GRTypeID'].'">'.$resi0['GRType'].'</option>';
		}
		
		echo 'Type: <select name="GRTypeID" required>
			<option value="">-- Select --</option>
			'.$ioptions.'
		</select><br>
		'.$submit.'
		</div>
		  </form></div>';		
		  
		  
		  
break;

case 'IOrDOrT':
	if (allowedToOpen(5357,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$addlsql='';
		if(isset($_POST['OnlinePublish'])){
				$addlsql='OnlinePublish='.$_POST['OnlinePublish'].',';
		}
		$sql='INSERT INTO mktg_2graphicreq SET '.$addlsql.'DateNeeded="'.$_POST['DateNeeded'].'",RequestDate=CURDATE(),IOrDOrT='.intval($_GET['IOrDOrT']).',Concept="'.addslashes($_POST['Concept']).'",Details="'.addslashes($_POST['Details']).'",Preferences="'.addslashes($_POST['Preferences']).'",GRTypeID="'.$_POST['GRTypeID'].'",RequestedByNo='.$_SESSION['(ak0)'].',RequestedTS=NOW();';
		$stmt=$link->prepare($sql); $stmt->execute();
		
	header("Location:graphicrequest.php?w=Lists");
	} else {
		echo 'No permission'; exit;
		}

break;

case 'PREditRequest':
	if (allowedToOpen(5357,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$addlsql='';
		if(isset($_POST['OnlinePublish'])){
			$addlsql='OnlinePublish='.$_POST['OnlinePublish'].',';
		}
		$sql='UPDATE mktg_2graphicreq SET '.$addlsql.'DateNeeded="'.$_POST['DateNeeded'].'",Concept="'.addslashes($_POST['Concept']).'",Details="'.addslashes($_POST['Details']).'",Preferences="'.addslashes($_POST['Preferences']).'",GRTypeID="'.$_POST['GRTypeID'].'",RequestedByNo='.$_SESSION['(ak0)'].',RequestedTS=NOW() WHERE Approved=0 AND TxnID='.intval($_GET['TxnID']).';';
// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		
	header("Location:graphicrequest.php?w=Lists");
	} else {
		echo 'No permission'; exit;
		}

break;

case 'ApproveReturn':
	if (allowedToOpen(5358,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid=intval($_GET['TxnID']);
		if(isset($_POST['btnApprove'])){
			$addsql='TargetDate="'.$_POST['TargetDate'].'",Approved=1,';
		} else {
			$addsql='';
			
			//return
			require($path."acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php");
			
			
		$mail = new PHPMailer();
		$mail->IsSMTP();  // telling the class to use SMTP
		$mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
		$mail->Host = "smtp.gmail.com"; // SMTP server
		$mail->Port = '587';//'465';
		$mail->IsHTML(true);
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->SMTPSecure = 'tls';//'ssl';
		$mail->Username = '1rtcicon@gmail.com';                            // SMTP username
		$mail->Password = '1RotaRy1003$';                           // SMTP password

		$mail->From = '1rtcicon@gmail.com';
		$mail->FromName = 'Graphic Design Request';

		$mail->Subject  = "Cannot Process Request";
		$mail->WordWrap = 50;

	
			$sql='SELECT Email FROM `1_gamit`.`1rtcusers` where IDNo=\''.intval($_GET['Requester']).'\'';
			
			$stmt=$link->query($sql);
			$res=$stmt->fetch();
		$address='https://www.arwan.biz/yr'.date('y').'/mktg/graphicrequest.php?w=Lookup&TxnID='.$txnid;
		$msg='Cannot process your request due to one of the possible reasons:<br><br>
1) Lack of information.<br>
2) Requested date of deadline not feasible.<br>
3) Others ('.$_POST['creason'].').<br><br><a href="'.$address.'">'.$address.'</a>';
		
			$mail->AddAddress($res['Email']);
			$mail->Body     = $msg;
			$mail->AltBody     = $msg; 
			if(!$mail->Send()) {
			echo 'Message was not sent.';
			echo 'Mailer error: ' . $mail->ErrorInfo;
			} else {
			echo 'Message has been sent.';
			}
						 $mail->ClearAddresses(); 
			
			
			header("Location:graphicrequest.php?w=Lists"); 
			
			exit();
			
			
		}
		$sql='UPDATE mktg_2graphicreq SET '.$addsql.'ApprovedByNo='.$_SESSION['(ak0)'].',ApprovedTS=NOW() WHERE TxnID='.$txnid.' AND Approved=0;';
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
	header("Location:graphicrequest.php?w=Lookup&TxnID=".$txnid);
	} else {
		echo 'No permission'; exit;
		}

break;
	
case 'FinishedInc':
	if (allowedToOpen(5358,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid=intval($_GET['TxnID']);
		// print_r($_POST);
		if(isset($_POST['btnDone'])){
			$addsql='DateFinished="'.$_POST['DateFinished'].'",Finished=1,';
		} else {
			$addsql='Approved=0,TargetDate=NULL,ApprovedByNo=NULL,ApprovedTS=NULL,DateFinished=NULL,';
		}
		$sql='UPDATE mktg_2graphicreq SET '.$addsql.'FinishedByNo='.$_SESSION['(ak0)'].',FinishedTS=NOW() WHERE TxnID='.$txnid.' AND Finished=0;';
		// echo '<br>'.$sql; exit();
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
	header("Location:graphicrequest.php?w=Lookup&TxnID=".$txnid);
	} else {
		echo 'No permission'; exit;
		}

break;

case 'ReceivedInc':
	if (allowedToOpen(5357,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid=intval($_GET['TxnID']);
		// print_r($_POST);
		if(isset($_POST['btnReceived'])){
			$addsql='ReceivedDate="'.$_POST['ReceivedDate'].'",Received=1,';
		} else {
			$addsql='Finished=0,DateFinished=NULL,FinishedByNo=NULL,FinishedTS=NULL,DateFinished=NULL,';
		}
		$sql='UPDATE mktg_2graphicreq SET '.$addsql.'ReceivedTS=NOW() WHERE TxnID='.$txnid.' AND Received=0;';
		// echo '<br>'.$sql; exit();
		
		$stmt=$link->prepare($sql); $stmt->execute();
		
	header("Location:graphicrequest.php?w=Lookup&TxnID=".$txnid);
	} else {
		echo 'No permission'; exit;
		}

break;
	
	
}

 $link=null; $stmt=null;
?>
</div> <!-- end section -->
