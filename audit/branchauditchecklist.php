<?php
date_default_timezone_set('Asia/Manila');
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6230,'1rtc')){ echo 'No Permission'; exit(); }
$showbranches=false;
if(!isset($_GET['print'])){
	include_once('../switchboard/contents.php');
} else {
	include_once $path.'/acrossyrs/dbinit/userinit.php';
		$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
		echo '<style>
#recTable tr td {height:5px; font-size: 10.5pt;font-family:Arial;border:.1px solid black;}
@media print {
  #printPageButton {
    display: none;
  }
}
</style>';
}


$mainlistid=4; //Declaration of Pre-Existing Conditions


 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');

	$which=(!isset($_GET['w'])?'List':$_GET['w']);
	if(!isset($_GET['print'])){
		
	
echo '<br><a id="link" href="branchauditchecklist.php?w=List">Branch Audit</a> <br><br>';
}
$forminfo='';



if (in_array($which,array('Form','Form1'))){
	
	
	$sqlc='SELECT TxnID,CONCAT(e.Nickname," ",e.SurName) AS AuditedBy,IDNoOrBranchNo,Remarks,DateAnswered,Branch FROM systools_2clresults r JOIN 1branches b ON r.IDNoOrBranchNo=b.BranchNo LEFT JOIN 1employees e ON r.EncodedByNo=e.IDNo WHERE TxnID='.intval($_GET['clTxnID']);
	$stmtc = $link->query($sqlc);
	$rowc=$stmtc->fetch();
	$auditor=$rowc['AuditedBy'];
	$remarks=$rowc['Remarks'];
	
	if(allowedToOpen(6231,'1rtc') AND (!isset($_GET['print']))){
		
		$sqlb='SELECT BranchNo,Branch FROM 1branches WHERE Active=1 AND Pseudobranch=0';
		$stmtb = $link->query($sqlb);
		$rowbr=$stmtb->fetchAll(); $bl='';
		foreach($rowbr AS $rowb){
			$bl.='<option value="'.$rowb['BranchNo'].'" '.($rowb['BranchNo']==$rowc['IDNoOrBranchNo']?'selected':'').'>'.$rowb['Branch'].'</option>';
		}
		$forminfo='<div>
			<form action="branchauditchecklist.php?w=UpdateBranchDate&TxnID='.$rowc['TxnID'].'" method="POST">
			
			<div style="float:left"><b>Branch: <select name="BranchNo">'.$bl.'</select><br>Audit Date: <input type="date" value="'.$rowc['DateAnswered'].'" name="AuditDate"></b></div>
			<div style="float:right;"><input type="submit" value="Update" name="btnUpdate" style="background-color:blue;color:white;"> &nbsp; &nbsp; &nbsp; &nbsp; <input type="submit" value="Delete" name="btnDelete" style="background-color:red;color:white;" OnClick="return confirm(\'Really Delete This?\');"></form></div>
			</div><div style="clear: both; display: block; position: relative;height:30px;"></div>';
	}
	 else {
		$forminfo='<b>Branch: '.$rowc['Branch'].'<br>Audit Date: '.$rowc['DateAnswered'].'</b>';
	 }
	
	
	
	
	$txnid=$mainlistid; //set
	$sql='SELECT * FROM systools_2clmain WHERE TxnID='.$txnid;
	$stmt = $link->query($sql);
	$row=$stmt->fetch();
	
	$title=$row['Title'];
	$agreement=$row['Agreement'];
	
	
	echo '<title>'.$title.'</title>';
	
	if($which=='Form'){
		$btninput='btnSubmit';
		$btnval='Submit';
	} else {
		$btninput='btnSubmit1';
		$btnval='Update';
	}
	echo '<script>
    function changeBodyBg(color){
        document.body.style.background = color;
		document.getElementById("btnHide").style.visibility="hidden";
		document.getElementById("'.$btninput.'").style.display = "block";
		document.getElementById("areyousure").style.display = "block";
		document.getElementById("areyousure").style.background = "orange";
    }
	</script>';
	// echo '';
	if(!isset($_GET['print'])){
	$remarksinput='<tr><td colspan=2 align="center">Remarks:<br><textarea rows="3" cols="60" name="Remarks" placeholder="Remarks">'.$remarks.'</textarea></td></tr>';
	} else {
		$remarksinput='<tr><td colspan=2 align="left" style="padding:10px;"><b>Remarks: </b>'.$remarks.'</td></tr>';
	}
	$areyousuremsg='<div id="areyousure" style="display:none;text-align:center;padding:5px;"><h1>Are You Sure?</h1></div><br>';
	$hidebtn='<tr><td colspan=2 align="center"><button type="button" id="btnHide" style="background-color:blue;color:white;padding:5px;width:200px;border-radius:15px;" onclick="changeBodyBg(\'black\');">'.$btnval.'</button></td></tr>';
}


if (in_array($which,array('List','Reports','NoDeclarationList'))){
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	
		echo comboBox($link,'SELECT Branch,BranchNo FROM `1branches` WHERE Active = 1 AND Pseudobranch=0 ORDER BY Branch;','Branch','BranchNo','branchlist');
		
}

switch ($which)
{
	case 'List':
		
		echo '<form action="branchauditchecklist.php?w=Add" method="POST">Branch: <input type="text" name="BranchNo" list="branchlist"> Date: <input type="date" name="Date" value="'.date('Y-m-d').'"> <input type="submit" value="Audit" name="btnAdd" style="padding:2px;"></form>';
		$formdesc='<br></i><form action="#" method="POST">MonthNo: <input type="text" value="'.date('m').'" name="MonthNo"> <input type="submit" name="btnLookupDate" value="Lookup"></form><i>';
		
		if(isset($_POST['btnLookupDate'])){
			$condidate='AND MONTH(DateAnswered)="'.$_POST['MonthNo'].'"';
		} else {
			$condidate='AND MONTH(DateAnswered)="'.date('m').'"';
		}
	
	
	$sqlmain='SELECT r.*,Branch,DateAnswered AS AuditDate,CONCAT(e.Nickname," ",e.SurName) AS AuditedBy,IDNoOrBranchNo AS BranchNo,IF(QuestionsScoresArray IS NULL,"No","Yes") AS `Audited?` FROM systools_2clresults r LEFT JOIN 1branches b ON r.IDNoOrBranchNo=b.BranchNo LEFT JOIN 1employees e ON r.EncodedByNo=e.IDNo WHERE CTxnID='.$mainlistid.' '.$condidate.' ORDER BY DateAnswered DESC,`Branch`';
	
        $title='Branch Audit'; 
		$columnnames=array('Branch','AuditDate','Audited?','Remarks','AuditedBy');
		
		$editprocess='branchauditchecklist.php?w=Form1&clTxnID='; $editprocesslabel='Lookup';
		
		$sql=$sqlmain;
		
		$width='100%';
        include('../backendphp/layout/displayastable.php');
	
	break;
	
	
	case 'Add':
	$sql='INSERT INTO systools_2clresults SET IDNoOrBranchNo='.$_POST['BranchNo'].',CTxnID='.$mainlistid.',DateAnswered="'.$_POST['Date'].'",EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now();';
	$stmt=$link->prepare($sql); $stmt->execute();
	
	$sqlhealthmain='SELECT TxnID FROM systools_2clresults WHERE IDNoOrBranchNo='.$_POST['BranchNo'].' AND DateAnswered="'.$_POST['Date'].'";';
	$stmtdailyhealth=$link->query($sqlhealthmain);
	$resdailyhealth=$stmtdailyhealth->fetch(); 
 
	header('Location:branchauditchecklist.php?w=Form&clTxnID='.$resdailyhealth['TxnID']);
	
	break;
	
	
	case 'Form':
	
	$sql='SELECT s.*,IsRate,RYNMin,RYNMax FROM systools_2clsub s LEFT JOIN systools_1clrateoryn r ON s.RYNID=r.RYNID WHERE TxnID='.$txnid.' ORDER BY INET_ATON(CONCAT(OrderBy,".0.0"))';
	
	$stmt = $link->query($sql);
	$row=$stmt->fetchAll();
	
	echo '<br><br><div style="width:45%;border:2px solid blue;padding:10px;background-color:white;margin-left:23%;">';
	echo $areyousuremsg;
	echo $forminfo;
	
	echo '<h3 align="center">'.$title.'</h3>';
	echo '<br>';
	echo '<form action="branchauditchecklist.php?w=Update&clTxnID='.$_GET['clTxnID'].'" method="POST"><table>';
	$space=''; $answerd=''; $enter='';
	echo '<style>
	.pad {
    padding: 3px;
	}
  </style>';
	foreach($row AS $rowi){
		if($rowi['QType']==1){
			goto titleonly;
		}
		
		if($rowi['IsRate']==0){
			$answerd='<input type="radio" value="-1" name="'.$rowi['QID'].'" required>Yes <input type="radio" value="-2" name="'.$rowi['QID'].'" required>No';
		}
		
		if($rowi['IsRate']==-2){
			$answerd='<input type="text" size="6" name="'.$rowi['QID'].'">';
		}
		
		if($rowi['IsRate']==1){
			$answerd='Rate: <input type="number" name="'.$rowi['QID'].'" min="'.$rowi['RYNMin'].'" max="'.$rowi['RYNMax'].'" required>';
		}
		
		titleonly:
		
	
		
		echo '<tr><td class="pad"><div style="margin-left:'.($rowi['QType']==3?'30px;':'0px').'">'.$space.$rowi['OrderBy'].'. '.$rowi['Question'].'</div></td><td style="width:120px;text-align:right">'.$answerd.'</td></tr>';
		$space=''; $answerd=''; $enter='';
	}
	
		if($agreement<>''){
			echo '<tr><td colspan=2 style="font-size:10pt;"><br><hr>* '.$agreement.'</td></tr>';
		}
	
	echo $remarksinput;
	echo '<tr><td colspan=2 align="center"><input type="submit" id="btnSubmit" value="Submit" name="btnSubmit" style="background-color:blue;color:white;padding:5px;width:200px;border-radius:15px;display:none;" OnClick="return confirm(\'Are you Sure?\');"></td></tr>';
	echo $hidebtn;
	
	echo '</table></form>';
	echo '</div>';
	
	
	break;
	
	case 'Form1':
	
	if(!isset($_GET['print'])){
	echo '<br><br><div style="width:50%;border:2px solid blue;padding:10px;background-color:white;margin-left:23%;">';
	echo $areyousuremsg;
	echo '<a href="branchauditchecklist.php?w='.$_GET['w'].'&clTxnID='.$_GET['clTxnID'].'&print=1">Print Preview</a><br><br>';
	
	echo $forminfo;
	echo '<h3 align="center">'.$title.'</h3>';
} else {
	echo '<div style="width:50%;padding:10px;background-color:white;font-size:10pt;margin-left:25%;">';
	echo '<button onclick="window.print()" id="printPageButton" style="background-color:green;color:white;">Print</button><br>';
	echo $forminfo;
	echo '<br><center><b>'.$title.'</b></center>';
}
	
	
	echo '<br>';
	echo '<form action="branchauditchecklist.php?w=Update&clTxnID='.$_GET['clTxnID'].'" method="POST"><table id="recTable">';
	$space=''; $answerd=''; $enter='';
	echo '<style>
	.pad {
    padding: 3px;
	}
  </style>';
  
	$sql0='CREATE TEMPORARY TABLE `ExplodedArray` (
	   `QIDarr` smallint(6) NULL,
	   `Score` VARCHAR(25) NULL
	 )';
	$stmt0=$link->prepare($sql0); $stmt0->execute();
	
	$sql2='SELECT QuestionsScoresArray,IDNoOrBranchNo,EncodedByNo FROM systools_2clresults WHERE TxnID='.intval($_GET['clTxnID']);
	$stmt2 = $link->query($sql2);
	$row2=$stmt2->fetch();
	
	$idnoorb=$row2['IDNoOrBranchNo'];
	$encby=$row2['EncodedByNo'];
	
	if($row2['QuestionsScoresArray']==NULL){
		header('Location:branchauditchecklist.php?w=Form&clTxnID='.intval($_GET['clTxnID']));
		exit();
	}
	
	$arrayex=explode(",", $row2['QuestionsScoresArray']);
	
	foreach($arrayex as $arrex){
			$arr = explode(">", $arrex, 2);
			$qid = $arr[0];
			$score = $arr[1];
			$sql='INSERT INTO ExplodedArray SET QIDarr='.$qid.',Score="'.$score.'"';
			$stmt=$link->prepare($sql); $stmt->execute();
	}
	
	$sql='SELECT s.*,QIDarr,Score,IsRate,RYNMin,RYNMax FROM systools_2clsub s LEFT JOIN systools_1clrateoryn r ON s.RYNID=r.RYNID LEFT JOIN ExplodedArray ea ON s.QID=ea.QIDarr WHERE TxnID='.$txnid.' ORDER BY INET_ATON(CONCAT(OrderBy,".0.0"))';
	
	$stmt = $link->query($sql);
	$row=$stmt->fetchAll();
	
	foreach($row AS $rowi){
		if($rowi['QType']==1){
			goto titleonly1;
		}
		
		if($rowi['IsRate']==0){
			$answerd='<input type="radio" value="-1" name="'.$rowi['QID'].'" '.($rowi['Score']==-1?'checked':'').' required>Yes <input type="radio" value="-2" name="'.$rowi['QID'].'" '.($rowi['Score']==-2?'checked':'').' required>No';
		}
		
		if($rowi['IsRate']==-2){
			$answerd='<input type="text" size="6" name="'.$rowi['QID'].'" value="'.$rowi['Score'].'" >';
		}
		
		if($rowi['IsRate']==1){
			$answerd='Rate: <input type="number" name="'.$rowi['QID'].'" min="'.$rowi['RYNMin'].'" max="'.$rowi['RYNMax'].'" value="'.$rowi['Score'].'" required>';
		}
		
		titleonly1:
		
		if($rowi['QType']==3){
			$space=str_repeat('&nbsp;','12');
		}
		
		echo '<tr><td class="pad"><div style="margin-left:'.($rowi['QType']==3?'30px;':'0px').'">'.$space.$rowi['OrderBy'].'. '.$rowi['Question'].'</div></td><td style="width:120px;text-align:right">'.$answerd.'</td></tr>';
		$space=''; $answerd=''; $enter='';
	}

	
	
	
	
		echo $remarksinput;
		if(!isset($_GET['print'])){
		echo '<tr><td colspan=2 align="center"><input type="submit" id="btnSubmit1" value="Update" name="btnSubmit1" style="background-color:blue;color:white;padding:5px;width:200px;border-radius:15px;display:none;"></td></tr>';
	echo $hidebtn;
		}
	echo '</table></form>';
	
	echo 'Audited By: '.$auditor;
	echo '</div>';
	
	break;
	
	case 'Update':
			$txnid=intval($_GET['clTxnID']);
			$columnstoadd=$_POST;
			
			// print_r($columnstoadd); exit();
			//get array keys
			$keys = array_keys($columnstoadd);
				$minus=3; //except remarks
			$cntend=count($columnstoadd)-$minus;
			
			$cnt=0;
			
			$qsarray='';
			//get array name
			while($cnt<=$cntend){
				$qsarray.=$keys[$cnt].'>'.$_POST[$keys[$cnt]].',';
				$cnt++;
			}
			$qsarray=substr($qsarray, 0, -1);
			
			
			$sql='UPDATE systools_2clresults r SET Remarks="'.addslashes($_POST['Remarks']).'",EncodedByNo='.$_SESSION['(ak0)'].', QuestionsScoresArray="'.$qsarray.'", TimeStamp=Now() WHERE (EncodedByNo='.$_SESSION['(ak0)'].' OR IDNoOrBranchNo='.$_SESSION['(ak0)'].' OR '.$_SESSION['(ak0)'].'=(SELECT LatestSupervisorIDNo FROM attend_30currentpositions WHERE IDNo=r.IDNoOrBranchNo)) AND TxnID='.$txnid;
			
			$stmt=$link->prepare($sql); $stmt->execute();
			
		
			// header('Location:branchauditchecklist.php?w=List');
			header('Location:branchauditchecklist.php?w=Form1&clTxnID='.$txnid);
			
			
		
	break;
	
	
	case 'UpdateBranchDate':
	$txnid=intval($_GET['TxnID']);
	if(isset($_POST['btnUpdate'])){
		$sql='UPDATE systools_2clresults SET EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now(),DateAnswered="'.$_POST['AuditDate'].'",IDNoOrBranchNo='.$_POST['BranchNo'].' WHERE  TxnID='.$txnid;
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:branchauditchecklist.php?w=Form1&clTxnID='.$txnid);
	} else {
		$sql='DELETE FROM systools_2clresults WHERE  TxnID='.$txnid;
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:branchauditchecklist.php?w=List');
	}
	
	break;
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
