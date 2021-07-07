<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6462,'1rtc')) { echo 'No permission'; exit();}

$which=(!isset($_GET['w'])?'Spotcheck':$_GET['w']);
if($which=='Spotcheck'){
	$showbranches=true;
} else {
	$showbranches=false;
}
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'select CatNo, Category from invty_1category Order by Category','CatNo','Category','categorylist');
	
?>
<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>	
<?php
	


	switch ($which){
		case'Spotcheck':
		include_once('../backendphp/layout/showencodedbybutton.php');
		echo'<title>Branch Spotcheck</title><h3>Branch Spotcheck</h3>';
		
	
		
		if (allowedToOpen(6465,'1rtc')){
			$sqlb='select BranchNo, Branch from 1branches where Active=1 and PseudoBranch=0 AND BranchNo IN (SELECT BranchNo FROM attend_1branchgroups WHERE FieldSpecialist='.$_SESSION['(ak0)'].')';
			$stmtb=$link->query($sqlb); $resultb=$stmtb->fetchAll();
			$input='';
			foreach($resultb as $resb){
				$input.='<input type="checkbox" name="checkbox[]" value="'.$resb['BranchNo'].'">'.$resb['Branch'].'</br>';
			}

			echo'</br><b>Encoding:</b><div style="padding:5px; background-color:1b3d6d; color:white; width:440px;">
			<form method="post" action="branchspotcheck.php?w=AddCategory">
				Date: <input type="Date" name="Date" value="'.date('Y-m-d').'">
				Category: <input type="text" name="Category" list="categorylist" required></br>
				<input type="checkbox" class="chk_boxes" onclick="toggle(this);" />Check All</br>
				'.$input.'
				<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
				<input type="submit" name="submit">
			</form></div>';
			
			$delprocess='branchspotcheck.php?w=DeleteCategory&TxnID=';
		}
	
		$title='';

		if(isset($_POST['btnPerBranch'])){
			$monthno=$_POST['MonthNo'];
			$branchcondi=' AND bs.BranchNo='.$_SESSION['bnum'];
		} else if(isset($_POST['btnAllBranches'])){
			$monthno=$_POST['MonthNo'];
			$branchcondi='';
		}else {
			$monthno=date('m');
		}

if (allowedToOpen(array(6464,6465),'1rtc')){
	$columnnames=array('Branch','Date','Category','Posted');
	$othercon='WHERE MONTH(`Date`)='.$monthno.' '.$branchcondi;
}else{
	$columnnames=array('Date','Category','Posted');
	$othercon=' WHERE MONTH(`Date`)='.$monthno.' AND bs.BranchNo=\''.$_SESSION['bnum'].'\' and Posted=0';
}	
		$sql='select  bs.Posted,bs.TxnID,bs.Date, Category,Concat(Nickname, \' \', SurName) as EncodedBy, bs.TimeStamp, Branch from audit_2branchspotcheck bs left join invty_1category c on c.CatNo=bs.CatNo left join 1employees e on e.IDNo=bs.EncodedByNo left join 1branches b on b.BranchNo=bs.BranchNo '.$othercon.'';
		// echo $sql;
		$txnidname='TxnID';
		
			if ($showenc==1) {
				array_push($columnnames,'EncodedBy','TimeStamp');
			}
		$editprocess='branchspotcheck.php?w=Lookup&TxnID=';
		$editprocesslabel='Lookup';
		
		
		$formdesc='</i><form action="#" method="POST">MonthNo: <input type="text" name="MonthNo" value="'.$monthno.'"> '.((allowedToOpen(array(6464,6465),'1rtc'))?'<input type="submit" name="btnPerBranch" value="Per Branch"> <input type="submit" name="btnAllBranches" value="All Branches">':'<input type="submit" name="btnPerBranch" value="Filter">').'</form><i>';
		include('../backendphp/layout/displayastablenosort.php');
			
		break;
		
		case'Posting':
			$txnid=intval($_GET['TxnID']);
			
			if($_GET['value']==1){
				$value=1;
				$othercond='and Posted=0';
			}else{
				$value=0;
				$othercond='and Posted=1';
			}
			
			$sql='update audit_2branchspotcheck set Posted='.$value.', PostedByNo=\''.$_SESSION['(ak0)'].'\',PostedTS=Now() where TxnID=\''.$txnid.'\' '.$othercond.'';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header('Location:branchspotcheck.php?w=Lookup&TxnID='.$txnid.'');
		break;
		
		case'Lookup':
		echo'<title>Lookup</title><h3>Lookup</h3>';
		$sqls='select bs.CatNo,Category from audit_2branchspotcheck bs left join invty_1category c on c.CatNo=bs.CatNo where TxnID=\''.$_GET['TxnID'].'\'';
		$stmts=$link->query($sqls); $results=$stmts->fetch();
		
		echo comboBox($link,'SELECT i.ItemCode,i.ItemDesc FROM  invty_1items i where i.CatNo=\''.$results['CatNo'].'\'','ItemCode','ItemDesc','itemlist');
		$catno=$results['CatNo'];
//Encoding and Deleting of PhysicalCount

		//datechecker
		$sqldchecker='select Posted, Branch, date_add(Date,interval 7 day) as Date from audit_2branchspotcheck bs left join 1branches b on b.BranchNo=bs.BranchNo where TxnID=\''.$_GET['TxnID'].'\'';
		$stmtdchecker=$link->query($sqldchecker); $resultdchecker=$stmtdchecker->fetch();
		//		
		
		if (allowedToOpen(6463,'1rtc') and $resultdchecker['Posted']==0){
		echo'</br><b>Encoding</b><div style="padding:5px; background-color:1b3d6d; color:white; width:430px;">
			<form method="post" action="branchspotcheck.php?w=AddCount&CatNo='.$catno.'&TxnID='.$_GET['TxnID'].'">
				Item: <input type="text" name="Item" list="itemlist" required>
				PhysicalCount: <input type="text" name="PhysicalCount" size="5" required>
				<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
				<input type="submit" name="submit">
			</form></div>';
		$delprocess='branchspotcheck.php?w=DeleteCount&TxnID='.$_GET['TxnID'].'&TxnSubId=';	
		}
//

if (allowedToOpen(6464,'1rtc') and $resultdchecker['Date']>=date('Y-m-d')){
	$editprocess='branchspotcheck.php?w=AddRemarks&TxnID='.$_GET['TxnID'].'&TxnSubId=';
	$editprocesslabel='Add Remarks?';
}
		
	$title='';
	$formdesc='</i>';
if (allowedToOpen(array(6464,6465),'1rtc')){ //ops and audit
	$columnnames=array('ItemDesc','ItemCode','PhysicalCount','ComputerCount','Difference','Remarks');
	$formdesc.='<div style="background-color:#f2f2f2; width:430px; border: 1px solid #404040; padding:5px;">
			<b>NOTE:</b></br></br>
			TimeStamp is the time that the computer counts the stock of the item.
		</div></br>
		<b>Branch:</b>&nbsp '.$resultdchecker['Branch'].' '.str_repeat('&nbsp;',5).' <b>Category:</b>&nbsp; '.$results['Category'].'';
	if(allowedToOpen(6465,'1rtc')){	
		if($resultdchecker['Posted']==1){
		$formdesc.=' '.str_repeat('&nbsp;',10).' <a style="text-decoration:none;" href="branchspotcheck.php?w=Posting&value=0&TxnID='.$_GET['TxnID'].'" onClick="return confirm(\'Are You Sure?\');"> <b>UNPOST?</b> </a>';
		}else{
		$formdesc.=' '.str_repeat('&nbsp;',10).' <b>UNPOSTED</b>';
		}
	}
	
}elseif(allowedToOpen(6463,'1rtc')){ //branch
	$formdesc.='<b>Branch:</b>&nbsp '.$resultdchecker['Branch'].' '.str_repeat('&nbsp;',5).' <b>Category:</b>&nbsp; '.$results['Category'].'';
	if($resultdchecker['Posted']==0){
		$formdesc.=' '.str_repeat('&nbsp;',10).' <a style="text-decoration:none;" href="branchspotcheck.php?w=Posting&value=1&TxnID='.$_GET['TxnID'].'" onClick="return confirm(\'Are You Sure?\');"> <b>POST?</b> </a>';
	}else{
		$formdesc.=' '.str_repeat('&nbsp;',10).' <b>POSTED</b>';
	}
	$columnnames=array('ItemDesc','ItemCode','PhysicalCount');
}else{
	$columnnames=array('ItemDesc','ItemCode','PhysicalCount');
}		
		$sql='select bss.ItemCode, bss.Remarks,bss.TxnSubId, Branch, ItemDesc,Concat(Nickname, \' \', SurName) as EncodedBy, bss.TimeStamp, PhysicalCount,
		(select sum(Qty) from invty_20uniallposted where ItemCode=bss.ItemCode and BranchNo=bs.BranchNo and TimeStamp<=bss.TimeStamp) as ComputerCount,
		abs(PhysicalCount-(select sum(Qty) from invty_20uniallposted where ItemCode=bss.ItemCode and BranchNo=bs.BranchNo and TimeStamp<=bss.TimeStamp)) as Difference
		
		from audit_2branchspotcheck bs 
		join audit_2branchspotchecksub bss on bss.TxnID=bs.TxnID 
		join invty_1items i on i.ItemCode=bss.ItemCode
		join invty_1category c on c.CatNo=i.CatNo
		join 1branches b on b.BranchNo=bs.BranchNo
		left join 1employees e on e.IDNo=bss.EncodedByNo
		where bs.TxnID=\''.$_GET['TxnID'].'\'';
		$txnidname='TxnSubId';
		// echo $sql; exit();
				array_push($columnnames,'EncodedBy','TimeStamp');
		include('../backendphp/layout/displayastablenosort.php');
		
		break;
		
		case'AddRemarks':
		$sql='select Remarks from audit_2branchspotchecksub where TxnSubId=\''.$_GET['TxnSubId'].'\'';
		$stmt=$link->query($sql); $result=$stmt->fetch();
		echo '<title>Add Remarks?</title><h3>Add Remarks?</h3>
		<form method="post" action="branchspotcheck.php?w=AddRemarksProcess&TxnID='.$_GET['TxnID'].'&TxnSubId='.$_GET['TxnSubId'].'">
		<input type="text" name="Remarks" value="'.$result['Remarks'].'">
		<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
		<input type="submit" name="submit">
		
		</form>';
		
		break;
		
		case'AddRemarksProcess':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';   
            $txnsubid = intval($_GET['TxnSubId']);
			$sql='update audit_2branchspotchecksub set Remarks=\''.$_POST['Remarks'].'\', REncodedByNo=\''.$_SESSION['(ak0)'].'\', RTimestamp=Now() WHERE TxnSubId=\''.$txnsubid.'\'';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header('Location:branchspotcheck.php?w=Lookup&TxnID='.$_GET['TxnID'].'');
		
		break;
		
		case'DeleteCount':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';   
            $txnsubid = intval($_GET['TxnSubId']);
			$sql='DELETE FROM `audit_2branchspotchecksub` WHERE TxnSubId=\''.$txnsubid.'\' and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header('Location:branchspotcheck.php?w=Lookup&TxnID='.$_GET['TxnID'].'');
		
		break;
		
		case'AddCount':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
// $itemcode=comboBoxValue($link,'invty_1items','ItemDesc',addslashes($_POST['Item']),'ItemCode');


		
		$txnid = intval($_GET['TxnID']);	

		$sqlic='select ItemCode from invty_1items WHERE ItemDesc="'.$_POST['Item'].'" AND CatNo='.$_GET['CatNo'];
		$stmtic=$link->query($sqlic); $resultic=$stmtic->fetch();
		$itemcode=$resultic['ItemCode'];
		
		$sql='insert into audit_2branchspotchecksub set TxnID=\''.$txnid.'\', ItemCode=\''.$itemcode.'\', PhysicalCount=\''.$_POST['PhysicalCount'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', Timestamp=Now()';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: branchspotcheck.php?w=Lookup&TxnID='.$txnid.'');
		
		break;
		
		case'AddCategory':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
$catno=comboBoxValue($link,'invty_1category','Category',addslashes($_POST['Category']),'CatNo');		

foreach($_POST['checkbox'] as $branchno){
		$sql='insert into audit_2branchspotcheck set Date=\''.$_POST['Date'].'\', CatNo=\''.$catno.'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', Timestamp=Now(), BranchNo=\''.$branchno.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
}
		header('Location: branchspotcheck.php');
		
		break;
		
		case'DeleteCategory':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';      
		
            $txnid = intval($_GET['TxnID']);
			$sql='DELETE FROM `audit_2branchspotcheck` WHERE TxnID=\''.$txnid.'\' and EncodedByNo=\''.$_SESSION['(ak0)'].'\' and Posted=0';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:branchspotcheck.php");
		
		break;
	}
		
?>
		
	