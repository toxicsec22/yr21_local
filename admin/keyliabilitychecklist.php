<?php
date_default_timezone_set('Asia/Manila');
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(6232,'1rtc')){ echo 'No Permission'; exit(); }
$showbranches=false;
if(!isset($_GET['print'])){
	include_once('../switchboard/contents.php');
} else {
	include_once $path.'/acrossyrs/dbinit/userinit.php';
		$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
		echo '<style>
@media print {
  #printPageButton {
    display: none;
  }
}
</style>';
}



 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');

	$which=(!isset($_GET['w'])?'List':$_GET['w']);
	if(!isset($_GET['print'])){
		
	
	
		echo '<br><a id="link" href="keyliabilitychecklist.php?w=List">Key Liability</a><br><br>';
	
}



switch ($which)
{
	case 'List':
		echo comboBox($link,'SELECT Branch,BranchNo FROM `1branches` WHERE Active = 1 ORDER BY Branch;','Branch','BranchNo','branchlist');
		if (allowedToOpen(6233,'1rtc')){
		echo '<form action="keyliabilitychecklist.php?w=Add" method="POST">Branch/WH: <input type="text" name="BranchNo" list="branchlist"> Date: <input type="date" name="DateToday" value="'.date('Y-m-d').'"> <input type="submit" value="Add" name="btnAdd" style="padding:2px;"></form>';
		}
		$formdesc='';
		
	$sqlmain='SELECT km.*,Branch,DateToday AS Date,CONCAT(e.Nickname," ",e.SurName) AS Assignee,km.BranchNo FROM admin_2klformmain km LEFT JOIN 1branches b ON km.BranchNo=b.BranchNo LEFT JOIN 1employees e ON km.AssigneeIDNo=e.IDNo ORDER BY DateToday DESC,`Branch`';
	
        $title='Key Liability'; 
		$columnnames=array('Branch','Date','Assignee');
		
		$editprocess='keyliabilitychecklist.php?w=Form1&TxnID='; $editprocesslabel='Lookup';
		
		$sql=$sqlmain;
		
		$width='50%';
        include('../backendphp/layout/displayastable.php');
	
	break;
	
	
	case 'Add':
	
	$sql='INSERT INTO admin_2klformmain SET BranchNo='.$_POST['BranchNo'].',DateToday="'.$_POST['DateToday'].'",EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now();'; 
	
	$stmt=$link->prepare($sql); $stmt->execute();
	
	
	header('Location:keyliabilitychecklist.php');
	
	break;
	
	
	case 'Form1':
	
	$sqlc='SELECT TxnID,km.BranchNo,DateToday,Branch,AssigneeIDNo,CONCAT(e.Nickname," ",e.SurName) AS Assignee FROM admin_2klformmain km JOIN 1branches b ON km.BranchNo=b.BranchNo LEFT JOIN 1employees e ON km.AssigneeIDNo=e.IDno WHERE TxnID='.intval($_GET['TxnID']);
	$stmtc = $link->query($sqlc);
	$rowc=$stmtc->fetch();
	
	
	
		
		$sqlb='SELECT BranchNo,Branch FROM 1branches WHERE Active=1 ORDER BY Branch;';
		$stmtb = $link->query($sqlb);
		$rowbr=$stmtb->fetchAll(); $bl='';
		foreach($rowbr AS $rowb){
			$bl.='<option value="'.$rowb['BranchNo'].'" '.($rowb['BranchNo']==$rowc['BranchNo']?'selected':'').'>'.$rowb['Branch'].'</option>';
		}
		
		// $sqle='SELECT IDNo,FullName AS Employee FROM attend_30currentpositions ORDER BY dept,Branch';
		$sqle='SELECT IDNo,CONCAT(SurName, ", ", Nickname) AS Employee FROM 1employees ORDER BY SurName, Nickname';
		$stmte = $link->query($sqle);
		$rowe=$stmte->fetchAll(); $e='';
		$e.='<option value="" selected> - Pls select - </option>';
		foreach($rowe AS $rowem){
			
			$e.='<option value="'.$rowem['IDNo'].'" '.($rowem['IDNo']==$rowc['AssigneeIDNo']?'selected':'').'>'.$rowem['Employee'].'</option>';
		}
		
		$forminfo='<div>
			<form action="keyliabilitychecklist.php?w=UpdateBranchDate&TxnID='.$rowc['TxnID'].'" method="POST">
			
			<div style="float:left"><b>Branch: '.((allowedToOpen(6233,'1rtc'))?'<select name="BranchNo">'.$bl.'</select>':'<input type="hidden" value="'.$rowc['BranchNo'].'" name="BranchNo"><input type="text" value="'.$rowc['Branch'].'" disabled>').' Date: <input type="date" value="'.$rowc['DateToday'].'" name="Date"> Assignee: <select name="IDNo">'.$e.'</select></b></div>
			<div style="float:right;"><input type="submit" value="Update" name="btnUpdate" style="background-color:blue;color:white;"> &nbsp; &nbsp; &nbsp; &nbsp; '.((allowedToOpen(6233,'1rtc'))?'<input type="submit" value="Delete" name="btnDelete" style="background-color:red;color:white;" OnClick="return confirm(\'Really Delete This?\');">':'').'</form></div>
			</div><div style="clear: both; display: block; position: relative;height:30px;"></div>';

	
	
	
	$title='Key Liability Form';
	
	echo '<title>'.$title.'</title>';
	
	echo '<br><br><div style="width:60%;border:2px solid blue;padding:10px;background-color:white;margin-left:17%;">';

	echo '<a href="keyliabilitychecklist.php?w=PrintPreview&TxnID='.$_GET['TxnID'].'&print=1">Print Preview</a><br><br>';
	
	echo $forminfo;
	echo '<h3 align="left">'.$title.'</h3>';
	
	
	
	echo '<br><hr><br>';
	if (allowedToOpen(6233,'1rtc')){
	echo '<form action="keyliabilitychecklist.php?w=AddSub&TxnID='.$_GET['TxnID'].'" method="POST">';
	echo 'PadlockBrand: <input type="text" name="PadlockBrand" size="15"> NumberOfKeys: <input type="text" name="NumberOfKeys" size="6"> DateReceived: <input type="date" name="DateReceived" size="15" value="'.date('Y-m-d').'"> &nbsp; &nbsp; <input type="submit" value="Add" style="padding:2px;">';
	echo '</form><br>';
	}
	echo '<table style="width:100%;border:1px solid black;border-collapse;collapse;">';
	
	echo '<tr><th>Padlock Brand</th><th>Number Of Keys</th><th>Date Received</th><th>Date Returned</th><th></th></tr>';
	
	
	
	$sql='SELECT * FROM admin_2klformsub WHERE TxnID='.$_GET['TxnID'].' ORDER BY DateReceived';
	
	$stmt = $link->query($sql);
	$row=$stmt->fetchAll();
	
	foreach($row AS $res){
		echo '<form action="keyliabilitychecklist.php?w=UpdateSub&TxnID='.$res['TxnID'].'&TxnSubID='.$res['TxnSubID'].'" method="POST"><tr><td><input type="text" name="PadlockBrand" value="'.$res['PadlockBrand'].'"></td><td><input type="text" name="NumberOfKeys" value="'.$res['NumberOfKeys'].'"></td><td><input type="date" name="DateReceived" value="'.$res['DateReceived'].'"></td><td><input type="date" name="DateReturned" value="'.$res['DateReturned'].'"></td><td>'.((allowedToOpen(6233,'1rtc'))?'<input type="submit" value="Edit" name="btnEdit" style="padding:2px;"> <input type="submit" value="Del" name="btnDelete" style="background-color:red;color:white;padding:2px;" OnClick="return confirm(\'Really Delete This?\');">':'').' <a href="keyliabilitychecklist.php?w=ResetDR&TxnID='.$res['TxnID'].'&TxnSubID='.$res['TxnSubID'].'" style="font-size:9pt;">Reset DR</a></td></tr></form>';
	}
	
	echo '</table>';
	echo '</div>';
	
	
	break;
	
	case 'UpdateSub':
	$txnsubid=intval($_GET['TxnSubID']);
	
	if(isset($_POST['btnEdit'])){
		$sql='UPDATE admin_2klformsub SET EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now(),PadlockBrand="'.$_POST['PadlockBrand'].'",NumberOfKeys="'.$_POST['NumberOfKeys'].'",DateReceived="'.$_POST['DateReceived'].'",DateReturned="'.$_POST['DateReturned'].'"  WHERE TxnSubID='.$txnsubid;
		$stmt=$link->prepare($sql); $stmt->execute();
	} else {
		$sql='DELETE FROM admin_2klformsub WHERE TxnSubID='.$txnsubid;
		$stmt=$link->prepare($sql); $stmt->execute();
	}
	
	header('Location:keyliabilitychecklist.php?w=Form1&TxnID='.intval($_GET['TxnID']));
	break;
	
	case 'ResetDR':
	$txnsubid=intval($_GET['TxnSubID']);
	$sql='UPDATE admin_2klformsub SET EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now(),DateReturned=NULL WHERE TxnSubID='.$txnsubid;
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:keyliabilitychecklist.php?w=Form1&TxnID='.intval($_GET['TxnID']));
	break;
	
	case 'AddSub':
	
	$txnid=intval($_GET['TxnID']);
	
	$sql='INSERT INTO admin_2klformsub SET PadlockBrand="'.$_POST['PadlockBrand'].'",NumberOfKeys="'.$_POST['NumberOfKeys'].'",DateReceived="'.$_POST['DateReceived'].'",TxnID='.$txnid.',EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now();'; 
	
	$stmt=$link->prepare($sql); $stmt->execute();
	
	
	header('Location:keyliabilitychecklist.php?w=Form1&TxnID='.$txnid);
	
	break;
	
	case 'UpdateBranchDate':
	$txnid=intval($_GET['TxnID']);
	
	
	if(isset($_POST['btnUpdate'])){
		$addlsq='';
		if($_POST['IDNo']<>''){
			$addlsq=',AssigneeIDNo='.$_POST['IDNo'];
		} else {
			$addlsq=',AssigneeIDNo=NULL';
		}
		$sql='UPDATE admin_2klformmain SET EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now(),DateToday="'.$_POST['Date'].'",BranchNo='.$_POST['BranchNo'].' '.$addlsq.' WHERE TxnID='.$txnid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:keyliabilitychecklist.php?w=Form1&TxnID='.$txnid);
	} else {
		$sql='DELETE FROM admin_2klformmain WHERE TxnID='.$txnid;
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:keyliabilitychecklist.php?w=List');
	}
	
	break;
	
	case 'PrintPreview':
	
	echo '<title>Print Preview</title>';
	$txnid=intval($_GET['TxnID']);
	$sqlm='SELECT km.*,Branch,Company,CompanyName,DateToday AS Date,CONCAT(e.Nickname," ",e.SurName) AS Assignee,km.BranchNo FROM admin_2klformmain km LEFT JOIN 1branches b ON km.BranchNo=b.BranchNo LEFT JOIN 1employees e ON km.AssigneeIDNo=e.IDNo JOIN 1companies c ON b.CompanyNo=c.CompanyNo WHERE TxnID='.$txnid;
	$stmtm = $link->query($sqlm);
	$rowm=$stmtm->fetch();
	
	echo '<img  src="../generalinfo/logo/'.$rowm['Company'].'.png">';
	echo '<br><br>';
	echo '<font style="font-size:14pt;font-family:Arial">';
	echo '<b>Branch: '.$rowm['Branch'].'</b>';
	echo '<br><br><br>';
	echo '<b>KEY LIABILITY FORM</b>';
	echo '<br><br>';
	echo '<br><br>';
	
	
	
	echo '<div style="width:75%;text-align:justify;">';
	
	
	$sql='SELECT * FROM admin_2klformsub WHERE TxnID='.$_GET['TxnID'].' ORDER BY DateReceived';
	
	$stmt = $link->query($sql);
	$row=$stmt->fetchAll();
	echo '<table border="1px solid black" style="border-collapse:collapse;width:100%;">';
	echo '<tr><th >Padlock Brand</th><th>Number of keys issued</th><th>Date Received</th></tr>';
	$trnodata='<tr><td>&nbsp;</td><td></td><td></td></tr>'; 
	$padding='style="padding:5px;"';
	// $cntdata=0;
	foreach($row AS $res){
		echo '<tr><td '.$padding.'>'.$res['PadlockBrand'].'</td><td align="center" '.$padding.'>'.$res['NumberOfKeys'].'</td><td '.$padding.'>'.$res['DateReceived'].'</td></tr>';
		// $cntdata++;
	}
	// $cntcr=9-$cntdata;
	echo '<tr><td colspan=4 align="center">-- NOTHING FOLLOWS --</td></tr>';
	// echo str_repeat($trnodata,$cntcr);
	echo '</table>';
	echo '<br><br>';
	echo 'I, the undersigned, acknowledge receipt of the keys enumerated above. I take full responsibility for these. I agree not to transfer possesion of the keys, especially for misuse or modification.  I further agree not to cause, allow or contribute to the making of any unauthorized copies of the keys.<br><br>
I understand and agree that violation of this agreement or loss of these keys due to my negligence, may result in disciplinary action and may render me responsible for the expenses of a rekey or replacement of locks for the affected areas.
';
echo '<br><br><hr><br>';
echo 'Tinatanggap ko ang mga susing nakasaad dito. Sumasang-ayon ako na ako ang may pananagutan sa mga ito, at hindi ko maaaring ibigay o ipahiram sa iba, lalo na sa maling paggamit o pagbabago nito.  Sumasang-ayon din ako na hindi ako papayag o maging sanhi sa paggawa ng kopya nang walang pahintulot.<br><br>Nauunawaan ko at sumasang-ayon ako na maaaring may igawad sa akin na pagdidisiplina kung ako ay may nagawang paglabag sa kasunduang ito, o mawala ko ang mga susi dahil sa aking kapabayaan. Maaari ding ako ang managot sa mga gastos sa pagpapalit ng mga kandado para sa mga apektadong lugar.
';
echo '<br><br><br>';
echo 'Printed Name: &nbsp; &nbsp; '.strtoupper($rowm['Assignee']);
echo '<br><br>';
echo 'Signature: &nbsp; &nbsp; <u>'.str_repeat('&nbsp;',50).'</u>';
echo '<br><br>';
echo 'Date: &nbsp; &nbsp; '.$rowm['DateToday'];
echo '</div>';
echo '</font>';
	
	
	
	
	break;
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
