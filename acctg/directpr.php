<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true; include_once('../switchboard/contents.php'); 
 


$whichqry=$_REQUEST['w'];
$pagetouse='directpr.php?which='.$whichqry;
$title='Encode Late Date-IN in Acctg';
$method='POST';
include_once('../backendphp/layout/clickontabletoedithead.php');

switch ($whichqry){
case 'LateDateIn':
    if (!allowedToOpen(531,'1rtc')) { echo 'No permission'; exit; }
 
$columnnames=array('DateOUT','FromBranch','ClientBranch','Remarks', 'Particulars','Total','Posted','DateIN?');  
$sql='select s.TxnSubId, m.TxnID, m.Date as DateOUT, curdate() as `DateIN?`, b1.Branch as FromBranch, b.Branch as ClientBranch, m.Remarks, m.Posted,  s.Particulars, format(sum(s.Amount),2) as Total from acctg_2txfrmain m
join acctg_2txfrsub s on m.TxnID=s.TxnID join `1branches` b on b.BranchNo=s.ClientBranchNo
join `1branches` b1 on b1.BranchNo=m.FromBranchNo
where (isnull(DateIN) or (DateIN like "")) and ClientBranchNo='.$_SESSION['bnum'].' group by s.TxnSubId order by m.Date, s.Particulars ';
$txnid='TxnSubId'; $type='date';
$editprocess='directpr.php?w=SetDateIn&TxnSubId=';
$editprocesslabel='Accept';
$columnstoedit=array('DateIN?');
include_once('../backendphp/layout/displayastableeditcells.php');
    break; 
 
case 'SetDateIn':
    if (!allowedToOpen(531,'1rtc')) { echo 'No permission'; exit; }
	
	$txnsubid=$_REQUEST['TxnSubId'];
	$sql='UPDATE `acctg_2txfrsub` set DateIN=\''.$_POST['DateIN?'].'\', INEncodedByNo='.$_SESSION['(ak0)'].', INTimeStamp=Now() where TxnSubId='.$txnsubid;
	$stmt=$link->prepare($sql); $stmt->execute();
        $sql='UPDATE `invty_2transfer` tm JOIN `acctg_2txfrsub` ats ON tm.ToBranchNo=ats.ClientBranchNo AND tm.TransferNo=ats.Particulars AND tm.DateIN=ats.DateIN SET PostedIn=1, PostedInByNo='.$_SESSION['(ak0)'].' WHERE PostedIn=0 AND ats.TxnSubId='.$txnsubid; echo $sql;break;
        $stmt=$link->prepare($sql); $stmt->execute();
        
	header("Location:directpr.php?w=LateDateIn");
	break;
 
}
noform:
      $link=null; $stmt=null;
?>