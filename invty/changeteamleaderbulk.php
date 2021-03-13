<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(7191,7192,7193,7194);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
include_once('../switchboard/contents.php');




$which=$_GET['w'];
switch ($which){
case 'Upload': 
$title='Change Team Leader via Upload'; 
echo '<a href="../jobdesc/salestagging.jpg" target="_blank">Read Instructions</a><br/><br/>'
    . 'Note: Tagged sales after the month data has been finalized will <b>NOT</b> be added to target scores for that month.<br/><br/>';
$colnames=array('BranchNo','txntype','InvNo','Amount','STLIDNo');
$requiredcol=array('BranchNo','txntype','InvNo','Amount','STLIDNo');
$required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
$allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
$specific_instruct='<i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
$tblname='invty_tempfortagging'; $firstcolumnname='BranchNo';
$DOWNLOAD_DIR="../../uploads/"; 
include('../backendphp/layout/uploaddata.php');
if(($row-1)>0){ echo '<a href="changeteamleaderbulk.php?w=List" target="_blank">Lookup Newly Imported Data</a>';}
break;

case 'List':
    $title='Uploaded List - Unprocessed';
    if(allowedToOpen(7194,'1rtc')){
    $action='<form method="post" style="display:inline" action="changeteamleaderbulk.php?w=';
    $formdesc='Tagged sales after the month data has been finalized will <b>NOT</b> be added to target scores for that month.<br/><br/>'.$action.'Truncate"><input type=submit value="Delete All Uploaded" ></form>&nbsp &nbsp';
    $formdesc.=$action.'Upload"><input type=submit value="Back to Upload" ></form>&nbsp &nbsp';
    $formdesc.=$action.'Apply"><input type=submit value="Apply Tagging of Sales for All" ></form>&nbsp &nbsp';}
    if(allowedToOpen(7194,'1rtc')){ $cond='';} else { $cond=' WHERE tt.STLIDNo='.$_SESSION['(ak0)'];}
    $sql='SELECT tt.*, FORMAT(tt.Amount,2) AS Amount, Branch, CONCAT(Nickname," ",Surname) AS STL, (SELECT FORMAT(SUM(ss.UnitPrice*ss.Qty),2) FROM `invty_2sale` sm JOIN `invty_2salesub` ss ON sm.TxnID=ss.TxnID WHERE tt.InvNo=sm.SaleNo AND tt.BranchNo=sm.BranchNo AND tt.txntype=sm.txntype ) AS RecordedAmt, (SELECT `Date` FROM `invty_2sale` sm WHERE tt.InvNo=sm.SaleNo AND tt.BranchNo=sm.BranchNo AND tt.txntype=sm.txntype ) AS RecordedDate, (SELECT `TeamLeader` FROM `invty_2sale` sm WHERE tt.InvNo=sm.SaleNo AND tt.BranchNo=sm.BranchNo AND tt.txntype=sm.txntype ) AS RecordedSTL, (SELECT `ClientName` FROM `invty_2sale` sm JOIN `1clients` c ON c.ClientNo=sm.ClientNo WHERE tt.InvNo=sm.SaleNo AND tt.BranchNo=sm.BranchNo AND tt.txntype=sm.txntype ) AS RecordedClient, (SELECT CONCAT("<a href=../invty/addeditsale.php?TxnID=",sm.TxnID,"&txntype=",sm.txntype," target=_blank>Lookup</a>") FROM `invty_2sale` sm JOIN `1clients` c ON c.ClientNo=sm.ClientNo WHERE tt.InvNo=sm.SaleNo AND tt.BranchNo=sm.BranchNo AND tt.txntype=sm.txntype ) AS Lookup 
FROM invty_tempfortagging tt JOIN `1branches` b ON b.BranchNo=tt.BranchNo JOIN `1employees` e ON e.IDNo=tt.STLIDNo '.$cond;
   /* $columnnames=array('BranchNo','Branch','txntype','STL','InvNo','Amount','RecordedClient','RecordedDate','RecordedAmt', 'RecordedSTL','Lookup');
    $txnidname='idtempfortagging';
    if(allowedToOpen(7194,'1rtc')){ 
        $delprocess='changeteamleaderbulk.php?w=Del&idtempfortagging=';
        $editprocess='changeteamleaderbulk.php?w=ApplySpecific&idtempfortagging='; $editprocesslabel='Tag_This'; 
        $width='80%';}
    include('../backendphp/layout/displayastable.php');*/
    
    echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3>'; echo $formdesc;
$stmtsp=$link->query($sql); $datatoshowsp=$stmtsp->fetchAll(); $sp=0;
$diraddress='../';
include($path.'/acrossyrs/js/includesscripts.php');

if ($stmtsp->rowCount()>0){
	echo '<div class="content" style="width:80%">';
		$msgcb='<br><form action="changeteamleaderbulk.php?w=ApplyCheckBox" method="post"><input type="submit" value="Tag Selected" name="btnSubmit" style="background-color:yellow;padding:3px;" OnClick="return confirm(\'Are you sure you want to TAG?\');"><table id="table1" class="display" style="width:100%; font-size: 10pt;" >'
				. '<thead><tr><th>All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></th><th>BranchNo</th><th>Branch</th><th>txntype</th><th>STL</th><th>InvNo</th><th>Amount</th><th>RecordedClient</th><th>RecordedDate</th><th>RecordedAmt</th><th>RecordedSTL</th><th>Lookup</th><th></th><th></th></tr></thead>';
	foreach($datatoshowsp as $rows){
		$sp++;
		$msgcb.='<tr><td align="right"><input type="checkbox" value="'.$rows['idtempfortagging'].'" name="applycheckbox[]" /></td><td>'.$rows['BranchNo'].'</td><td>'.$rows['Branch'].'</td><td>'.$rows['txntype'].'</td><td>'.$rows['STL'].'</td><td>'.$rows['InvNo'].'</td><td>'.$rows['Amount'].'</td><td>'.$rows['RecordedClient'].'</td><td>'.$rows['RecordedDate'].'</td><td>'.$rows['RecordedAmt'].'</td><td>'.$rows['RecordedSTL'].'</td><td>'.$rows['Lookup'].'</td>'.(allowedToOpen(7194,'1rtc')?'<td><a href="changeteamleaderbulk.php?w=ApplySpecific&idtempfortagging='.$rows['idtempfortagging'].'">Tag_This</a></td><td><form action="changeteamleaderbulk.php?w=Del&idtempfortagging='.$rows['idtempfortagging'].'" method="POST"><input type="submit" value="Delete" OnClick="return confirm(\'Really delete this?\');"></form></td>':'<td></td><td></td>').'</tr>';
   }
   $msgcb.='</table></div>';
   echo $msgcb;
}
    break;

case 'Del':
    $sql='DELETE FROM invty_tempfortagging WHERE idtempfortagging='.$_GET['idtempfortagging'];
    $stmt=$link->prepare($sql); $stmt->execute();
    header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
case 'ApplyCheckBox':
if (isset($_REQUEST['applycheckbox'])){
	foreach ($_REQUEST['applycheckbox'] AS $tid){
		 $sql='UPDATE `invty_2sale` sm JOIN invty_tempfortagging tt ON tt.InvNo=sm.SaleNo AND tt.BranchNo=sm.BranchNo AND tt.txntype=sm.txntype SET sm.`TeamLeader`=tt.STLIDNo, TLEncByNo=\''.$_SESSION['(ak0)'].'\', TLTS=Now() WHERE  `Date`>\''.$_SESSION['nb4'].'\' AND idtempfortagging='.$tid;
		$stmt=$link->prepare($sql); $stmt->execute();
		
		$sql='DELETE FROM invty_tempfortagging WHERE idtempfortagging='.$tid;
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:".$_SERVER['HTTP_REFERER']);
	}
} else {
	echo 'Please select at least 1.';
}
break;

case 'Apply':
    $sql='UPDATE `invty_2sale` sm JOIN invty_tempfortagging tt ON tt.InvNo=sm.SaleNo AND tt.BranchNo=sm.BranchNo AND tt.txntype=sm.txntype SET sm.`TeamLeader`=tt.STLIDNo, TLEncByNo=\''.$_SESSION['(ak0)'].'\', TLTS=Now() where `Date`>\''.$_COOKIE['nb4'].'\'';
    $stmt=$link->prepare($sql); $stmt->execute();
    // no break here so delete after processing

case 'Truncate':
    $sql='DELETE FROM invty_tempfortagging;';
    $stmt=$link->prepare($sql); $stmt->execute();
    header("Location:changeteamleaderbulk.php?w=List");
    break;

case 'ApplySpecific':
    $sql='UPDATE `invty_2sale` sm JOIN invty_tempfortagging tt ON tt.InvNo=sm.SaleNo AND tt.BranchNo=sm.BranchNo AND tt.txntype=sm.txntype SET sm.`TeamLeader`=tt.STLIDNo, TLEncByNo=\''.$_SESSION['(ak0)'].'\', TLTS=Now() WHERE  `Date`>\''.$_COOKIE['nb4'].'\' AND idtempfortagging='.$_GET['idtempfortagging'];
    $stmt=$link->prepare($sql); $stmt->execute();
    $sql='DELETE FROM invty_tempfortagging WHERE idtempfortagging='.$_GET['idtempfortagging'];
    $stmt=$link->prepare($sql); $stmt->execute();
    header("Location:".$_SERVER['HTTP_REFERER']);
    break;
}

    $link=null; $stmt=null; 
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