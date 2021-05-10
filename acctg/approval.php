<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(518,519,520,521,5211,522,5222,5221,523,2051,5200);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=true; include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT TypeID, BudgetDesc FROM acctg_1branchpreapprovedbudgetlist ORDER BY BudgetDesc','TypeID','BudgetDesc','typelist');
echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches where BranchNo>=0 AND Active=\'1\' ORDER BY Branch','BranchNo','Branch','branchlist');
echo comboBox($link,'SELECT AccountID, ShortAcctID FROM acctg_1chartofaccounts ORDER BY ShortAcctID','AccountID','ShortAcctID','accountlist');

$whichqry=!isset($_GET['w'])?'Lookup':$_GET['w'];

switch ($whichqry){
   case 'RequestForExpenseApproval':
      if (!allowedToOpen(523,'1rtc')) { echo 'No permission'; exit; }
$title='Encashment Request'; 
$formdesc='</br>This should not be used for expenses with pre-approved values. ';

include('../backendphp/layout/clickontabletoedithead.php');

	 	$radionamefield='Radio'; 
	 echo'<div style="border:1px solid black; padding:10px; width:500px;"><form id="form-id">
			<b>Beyond Pre-approved Budget</b> (approver: Finance Asst) <input type="radio" id="watch-me3" name="'.$radionamefield.'" value="Type"></br></br>
			<b>Overprice</b>  (approver: Acctg TL - GA) <input type="radio" id="watch-me5" name="'.$radionamefield.'" value="Type"></br></br>
			<b>Freight to CLIENTS ONLY</b> (approver: SC Dept Head) <input type="radio" id="watch-me2" name="'.$radionamefield.'" value="Type"></br></br>
			<b>Freight Interbranch (Warehouse to Branch)</b> (approver: Finance Asst) <input type="radio" id="watch-me4" name="'.$radionamefield.'" value="Type"></br></br>
			<b>Unbudgetted Cash Expenses - ALL OTHERS</b> (approver: Admin Dept Head) <input type="radio" id="watch-me1" name="'.$radionamefield.'" value="Type"></br></br>
			<b>SRS</b> (approver: SC Dept Head) <input type="radio" id="watch-me6" name="'.$radionamefield.'" value="Particulars">
		  </br>  </br>
			<b>Transfer Expense (Branch to Branch)</b> (approver: Planning Assoc) <input type="radio" id="watch-me7" name="'.$radionamefield.'" value="Type">
		  </form></br></div></br>';
	include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';
	
	 $date='Date: <input type="Date" name="Date" value="'.date('Y-m-d').'" required>';
	 $amount='Amount: <input type="text" name="Amount" size="5" required>';
	 $particulars='Particulars: <input type="text" name="Particulars" size="50" placeholder="Particulars">';
	 $waybill='WaybillNo: <input type="text" name="Particulars" size="10" placeholder="Waybill No">';
	 $txfrreceipt='Transfer Receipt: <input type="text" name="Particulars" size="10" placeholder="Transfer Receipt">';
	$token='<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">';
	$submit='<input type="submit" name="submit" value="Submit" OnClick="return confirm(\'Are you sure you want to submit?\');">';	
	
	//Unbudgetted Cash Expenses - ALL OTHERS
	//eeswitch=5
	 	echo'<div style="display:none" id="show-me1">
	 <form method="post" action="praddmain.php?w=RequestForExpenseApproval&eeswitch=5"> 
			'.$date.'
			'.$amount.'
			'.$particulars.'
			'.$token.'
			'.$submit.'
		  </form></div>';
	
	//SRS
	//eeswitch=6
	 	echo'<div style="display:none" id="show-me6">
	 <form method="post" action="praddmain.php?w=RequestForExpenseApproval&eeswitch=6"> 
			'.$date.'
			'.$amount.'
			'.$particulars.'
			'.$token.'
			'.$submit.'
		  </form></div>';
		  
	//Transfer Receipt
	//eeswitch=7
	 	echo'<div style="display:none" id="show-me7">
	 <form method="post" action="praddmain.php?w=RequestForExpenseApproval&eeswitch=7"> 
			'.$date.'
			'.$amount.'
			'.$txfrreceipt.'
			'.$token.'
			ToBranch: <input type="text" name="FromBudgetOf" list="branchlist">
			'.$submit.'
		  </form></div>';
		  
	//OverPrice 
	// eeswitch=2
	 	echo'<div style="display:none" id="show-me5">
	 <form method="post" action="praddmain.php?w=RequestForExpenseApproval&eeswitch=2">
			'.$date.'
			'.$amount.'
			'.$particulars.'
			'.$token.'
			'.$submit.'
		  </form></div>';
		  
	//Request for Approval for Freight to CLIENTS ONLY:
	// efswitch=3
		  	echo'<div style="display:none" id="show-me2">
	 <form method="post" action="praddmain.php?w=RequestForFreightClientApproval&efswitch=3">
			'.$date.'
			ForInvoiceNo: <input type="text" name="ForInvoiceNo" size="10" required>
			InvoiceType: Cash <input type="radio" name="txntype" size="3" value="1"> Charge <input type="radio" name="txntype" size="3" value="2"></br></br>
			'.$amount.'
			'.$particulars.'
			Item Price: SEPARATE Freight <input type="radio" name="PriceFreightInclusive" size="3" value="0"> INCLUDES Freight: <input type="radio" name="PriceFreightInclusive" size="3" value="1"></br></br>
			'.$token.'
			'.$submit.'
		  </form></div>';
	//Request for Approval for Freight Interbranch:
	//eeswitch=4
		  	echo'<div style="display:none" id="show-me4">
	 <form method="post" action="praddmain.php?w=RequestForExpenseApproval&eeswitch=4">
			'.$date.'
			'.$amount.'
			'.$waybill.'
			'.$token.'
			'.$submit.'
		  </form></div>';
		  
	//Beyond Pre-approved Budget  
	// eeswitch=1
		  	echo'<div style="display:none" id="show-me3">
	 <form method="post" action="praddmain.php?w=RequestForExpenseApproval&eeswitch=1">
			'.$date.'
			Type: <input type="text" name="Type" size="10" list="typelist"">
			'.$particulars.'
			'.$amount.'
			'.$token.'
			'.$submit.'
		  </form></div>';
	
	 
	 
     echo '</body></html>';
break;

case 'Approved':
    if (!allowedToOpen(2051,'1rtc')) { echo 'No permission'; exit; }
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    echo comboBox($link,'SELECT Branch, BranchNo FROM `1branches` WHERE Active=1','BranchNo','Branch','branchnames');
$title='Approval Number'; $formdesc='<a href="approval.php?w=Lookup">Add New</a>&nbsp; &nbsp; &nbsp;<a href="../approvals/budgets.php">Monthly Budgets</a><br><br>';
 $showbranches=false;
$approvalno=$_GET['Approval']; 
$sql='Select a.Date AS `Date`, b.Branch, ShortAcctID, Particulars, BudgetDesc as BudgetDescription,Amount, Approval from `approvals_2encashedexpenses` a left join acctg_1chartofaccounts ca on a.AccountID=ca.AccountID join `1branches` b on b.BranchNo=a.BranchNo
left join `acctg_1branchpreapprovedbudgetlist` l on l.TypeID=a.TypeID where Approval LIKE \''.$approvalno.'\'
 UNION
 Select a.Date AS `Date`, b.Branch, "FreightClients", concat(Particulars, " InvNo ", ForInvoiceNo),\'\' as BudgetDescription, Amount, Approval from `approvals_2freightclients` a join `1branches` b on b.BranchNo=a.BranchNo where Approval LIKE \''.$approvalno.'\''; 
$columnnames=array('Date','Branch','ShortAcctID','Particulars','BudgetDescription','Amount','Approval');
$liststoshow=array('branchnames');
include('../backendphp/layout/displayastable.php');
//include('../approvals/budgets.php?BranchNo=');
    break;

case 'Lookup':
    if (!allowedToOpen(518,'1rtc')) { echo 'No permission'; exit; }
 $fieldname='Month';

if (allowedToOpen(5181,'1rtc')) {
	$title='Encode Approvals';
	$radionamefield='Radio'; 
	 echo'<div style="border:1px solid black; padding:10px; width:1380px;"><title>'.$title.'</title><h3>'.$title.'</h3></br>
	 <form id="form-id">
			<b>Unbudgetted:</b> <input type="radio" id="watch-me1" name="'.$radionamefield.'" value="Particulars"> </br></br>
			<b>Beyond Pre-approved Budget:</b> <input type="radio" id="watch-me2" name="'.$radionamefield.'" value="Type">
		  </form></br>';
		  
	include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';
	
	$date='Date: <input type="Date" name="Date" value="'.date('Y-m-d').'" required>';
	$branch='Branch: <input type="text" name="Branch" list="branchlist" required>';
	$amount='Amount: <input type="text" name="Amount" value="0" required>';
	$approval='<input type="hidden" name="Approval" value="'.mt_rand(10000,999999).'">';
	$token='<input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">';
	$submit='<input type="submit" name="submit" value="Add New" OnClick="return confirm(\'Are you sure you want to Add?\');">';
	
	echo'<div style="display:none" id="show-me2">
	 <form method="post" action="praddmain.php?w=Approval">
			'.$date.'
			'.$branch.'
			Type: <input type="text" name="Type" list="typelist" required>
			'.$amount.'
			'.$approval.'
			'.$token.'
			'.$submit.'
		  </form></div>';
	
	 echo'<div style="display:none" id="show-me1">
	 <form method="post" action="praddmain.php?w=Approval">
			'.$date.'
			'.$branch.'
			Account: <input type="text" name="AccountID" list="accountlist" required>
			'.$amount.'
			Particulars: <input type="text" name="Particulars" size="50" placeholder="Particulars">
			'.$approval.'
			'.$token.'
			'.$submit.'
		  </form></div>
		  </div></br>';
		  
		  unset($title);
}
echo'<form method="post" action="approval.php?w=Lookup" enctype="multipart/form-data">
Choose Month (1 - 12):  <input type="text" name="'.$fieldname.'" value="'.date('m').'"></input>
<input type="submit" name="lookup" value="Lookup"> </form>';
$title='Lookup Approvals';
if (!isset($_REQUEST[$fieldname])){goto noform;} else {
   
   if (allowedToOpen(5182,'1rtc')){ 
    $condition='';
} elseif (allowedToOpen(5183,'1rtc')){ 
     $condition=' and a.BranchNo in (Select g.BranchNo from `attend_1branchgroups` g where TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') ';
} elseif (allowedToOpen(5184,'1rtc')){ 
     $condition=' and (a.BranchNo='.$_SESSION['bnum'].' OR a.EncodedByNo='.$_SESSION['(ak0)'].') ';
} else {
   $condition='';
     $columnnames=array();
}
//$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Date,Branch');   
$sql='Select ApprovalID as TxnID,Entity AS FromBudgetOf, Date, b.Branch, Particulars,BudgetDesc, Amount,Approval, ShortAcctID, concat(e.`Nickname`,\' \',e.`SurName`) AS `RequestedBy`, concat(e2.`Nickname`,\' \',e2.`SurName`) AS `ApprovedBy`, a.`TimeStamp` as `RequestTS`, a.`ApprovedTS` as `ApprovalTS` from `approvals_2encashedexpenses` a left join acctg_1chartofaccounts ca on a.AccountID=ca.AccountID
left join `1employees` e2 on a.ApprovedByNo=e2.IDNo
left join `1employees` e on a.EncodedByNo=e.IDNo
left join `acctg_1branchpreapprovedbudgetlist` l on l.TypeID=a.TypeID
join `1branches` b on b.BranchNo=a.BranchNo left join `acctg_1budgetentities` be on be.EntityID=a.FromBudgetOf where Month(Date)='.$_REQUEST[$fieldname].$condition.'
UNION ALL
Select concat(ApprovalID,"FC") as TxnID,Entity AS FromBudgetOf,  Date, b.Branch, concat(Particulars, " InvNo ", ForInvoiceNo, " ", IF(PriceFreightInclusive=1,"PriceINCLUDESFreight","PriceNOFreight")),\'\' as BudgetDesc, Amount,Approval, "FreightClients", concat(e.`Nickname`,\' \',e.`SurName`) AS `RequestedBy`, concat(e2.`Nickname`,\' \',e2.`SurName`) AS `ApprovedBy`, a.`TimeStamp` as `RequestTS`, a.`ApprovedTS` as `ApprovalTS` from `approvals_2freightclients` a 
left join `1employees` e2 on a.ApprovedByNo=e2.IDNo
left join `1employees` e on a.EncodedByNo=e.IDNo
left join `acctg_1budgetentities` be on be.EntityID=a.FromBudgetOf 
join `1branches` b on b.BranchNo=a.BranchNo where Month(Date)='.$_REQUEST[$fieldname].$condition.'
Order by Date,Branch';//.$sortfield;

} //echo $sql;break;
$columnnames=array('Date','Branch','ShortAcctID','Particulars','FromBudgetOf','BudgetDesc','Amount','Approval', 'RequestedBy', 'RequestTS', 'ApprovedBy','ApprovalTS');
$columnsub=$columnnames;

$editprocess='approval.php?w=DelUnbudgetted&ApprovalID=';$editprocesslabel='Del';
if (allowedToOpen(5181,'1rtc')) { $addlprocess='approval.php?w=DelApprovedUnbudgetted&ApprovalID='; $addlprocesslabel='Del_Approved';}
include('../backendphp/layout/displayastable.php');
    break;

case 'DelUnbudgetted':
   if (strpos($_GET['ApprovalID'],'FC')!==FALSE){ $table='approvals_2freightclients'; } else {$table='approvals_2encashedexpenses';}
   $sql='DELETE from `'.$table.'` WHERE isnull(Approval) and `ApprovalID`='.rtrim($_GET['ApprovalID'],'FC').' and EncodedByNo='.$_SESSION['(ak0)'];
   // echo $sql;break;
   $stmt=$link->prepare($sql);
   $stmt->execute();
   header('Location:approval.php?w=Lookup');
   break;
   
case 'DelApprovedUnbudgetted':
    if (!allowedToOpen(5181,'1rtc')) {   echo 'No permission'; exit;}
   if (strpos($_GET['ApprovalID'],'FC')!==FALSE){ $table='approvals_2freightclients'; } else {$table='approvals_2encashedexpenses';}
   $sql='DELETE from `'.$table.'` WHERE `ApprovalID`='.rtrim($_GET['ApprovalID'],'FC'). ' AND `Date`>\''.$_SESSION['nb4A'].'\'';
   // echo $sql;break;
   $stmt=$link->prepare($sql);
   $stmt->execute();
   header('Location:approval.php?w=Lookup');
   break;

//   approvals for overprice
case 'NewOP':
$title='Request for Overprice';
if (!allowedToOpen(5200,'1rtc')) {   echo 'No permission'; exit;}
$method='post';
$showbranches=false;
    $columnnames=array(
                 //   array('field'=>'Branch','type'=>'text','size'=>10,'required'=>true,'list'=>'branchnames'),
                    array('field'=>'InvNo', 'type'=>'text','size'=>20,'required'=>true),
                    array('field'=>'Amount','type'=>'text','size'=>10,'required'=>true,'value'=>0));
    
    $action='praddmain.php?w=OPRequest'; 
    $liststoshow=array();
     include('../backendphp/layout/inputmainform.php'); 
    
               //     array('field'=>'Approval','type'=>'hidden','size'=>0,'value'=>mt_rand(1000,99999))
break; 
 
case 'OPApproved':
    if (!allowedToOpen(5200,'1rtc')) {   echo 'No permission'; exit;}
$title='Approval Number for Overprice';

$showbranches=false;
$approvalno=$_GET['Approval'];
$sql='Select a.*, sm.txntype, b.Branch  from invty_7opapproval a join `1branches` b on b.BranchNo=a.BranchNo LEFT JOIN `invty_2sale` sm ON sm.TxnID=a.TxnID where Approval='.$approvalno; //echo $sql; break;
//$sql='Select a.*, b.Branch  from invty_7opapproval a join `1branches` b on b.BranchNo=a.BranchNo where Approval='.$approvalno;
$columnnames=array('Branch','InvNo','Amount','Approval');
include('../backendphp/layout/displayastable.php');
    break;
   
case 'LookupOP':
if (!allowedToOpen(520,'1rtc')) { echo 'No permission'; exit; }
$title='Lookup Approvals for Overprice';

$showbranches=false;
$fieldname='Month';
?>
<form method="post" action="approval.php?w=LookupOP" enctype="multipart/form-data">
Choose Month (1 - 12):  <input type="text" name="<?php echo $fieldname; ?>" value="<?php echo date('m'); ?>"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
if(allowedToOpen(5222,'1rtc') or allowedToOpen(5221,'1rtc')) { $condition='';} elseif(allowedToOpen(522,'1rtc')){$condition=' AND a.BranchNo IN (Select g.BranchNo from `attend_1branchgroups` g where TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].')';} else { $condition=' AND a.BranchNo='.$_SESSION['bnum'];}
$sql0='Select m.TxnID as SaleTxnID,a.Approval as TxnID, c.ClientName, pt.paytypedesc as PayType, a.Approval, a.InvNo, CONCAT(a.InvNo, "&BranchNo=", a.BranchNo) AS BranchInvNo, a.Amount,OPClientName,OPClientMobile, m.Date as SaleDate,b.Branch, concat(e.`Nickname`,\' \',e.`SurName`) AS `RequestedBy`, concat(e2.`Nickname`,\' \',e2.`SurName`) AS `ApprovedBy`, a.`TimeStamp` AS RequestTS, `ApprovalTS`, if(isnull(a.TxnID),"not yet recorded","Done") as Recorded from invty_7opapproval a join `1employees` e on a.EncodedByNo=e.IDNo
LEFT JOIN `1employees` e2 on a.ApprovedByNo=e2.IDNo
join `1branches` b on b.BranchNo=a.BranchNo
left join invty_2sale m on m.TxnID=a.TxnID 
LEFT join `1clients` c on c.ClientNo=m.ClientNo
LEFT join invty_0paytype pt on pt.paytypeid=m.PaymentType '; // m.SaleNo=a.InvNo and m.BranchNo=a.BranchNo

if (!isset($_REQUEST[$fieldname])){
$month=date('m');
} else {
   $month=$_REQUEST[$fieldname];
}

$txnidname='BranchInvNo';

if (allowedToOpen(522,'1rtc') or allowedToOpen(5222,'1rtc')) {
    $sql1='SELECT JLID AS `Rank` FROM attend_0positions p JOIN `attend_1joblevel` jl ON jl.JobLevelNo=p.JobLevelNo WHERE `PositionID`='.$_SESSION['&pos'];
    $stmt1=$link->query($sql1); $res1=$stmt1->fetch();
    $sql=$sql0.' WHERE ISNULL(Approval) '.(allowedToOpen(5222,'1rtc')?'':' AND a.BranchNo IN (Select g.BranchNo from `attend_1branchgroups` g where TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') AND (a.EncodedByNo<>'.$_SESSION['(ak0)'].') AND (SELECT IF (PositionID IN (32,33,37,38,81),0,JLID) FROM `attend_30currentpositions` WHERE IDNo=a.EncodedByNo)<('.$res1['Rank'].')'); 
$columnnames=array('Branch','InvNo','Amount','RequestedBy','RequestTS','OPClientName','OPClientMobile'); 
$columnstoedit=array('Amount','OPClientName','OPClientMobile'); 
$editprocess='praddmain.php?w=ApproveOP&action_token='.$_SESSION['action_token'].'&InvNo=';$editprocesslabel='Approve';
include('../backendphp/layout/displayastableeditcellsnoheaders.php');    
}

$columnnames=array('SaleTxnID','SaleDate','Branch','InvNo','ClientName','PayType','Amount','OPClientName','OPClientMobile','RequestedBy','RequestTS','Approval', 'ApprovedBy','ApprovalTS','Recorded');
$sql=$sql0.' WHERE Month(a.`TimeStamp`)='.$month.$condition.' ORDER BY a.TimeStamp,b.Branch';
$editprocess='approval.php?w=DelOP&action_token='.$_SESSION['action_token'].'&InvNo=';$editprocesslabel='Del';
include('../backendphp/layout/displayastable.php');
    break;
   
case 'RecordOP':
   if (!allowedToOpen(6927,'1rtc')) {   echo 'No permission'; exit;}
   require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
   $sql='UPDATE `invty_7opapproval` SET `TxnID` = '.$_GET['TxnID'].' WHERE `Approval` ='.$_GET['Approval'];
   $stmt=$link->prepare($sql);
   $stmt->execute();
   header('Location:../invty/addeditsale.php?txntype='.$_GET['txntype'].'&TxnID='.$_GET['TxnID']);
   break;
   
case 'DelOP':
   if (!allowedToOpen(5200,'1rtc')) {   echo 'No permission'; exit;}
   require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
   $sql='DELETE from `invty_7opapproval` WHERE isnull(TxnID) AND `BranchNo`='.$_GET['BranchNo'].' AND `InvNo` like \'%'.$_GET['InvNo'].'%\' and EncodedByNo='.$_SESSION['(ak0)']; 
   // echo $sql;exit();
   $stmt=$link->prepare($sql);
   $stmt->execute();
   header('Location:approval.php?w=LookupOP');
   break;
   
case 'RemoveRecordedOP':
   if (!allowedToOpen(5221,'1rtc')) {   echo 'No permission'; exit;}
   require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
   // changed this to delete so mistakes in amounts can be corrected
   // $sql='UPDATE `invty_7opapproval` SET Amount=0, EncodedByNo='.$_SESSION['(ak0)'].', `TimeStamp`=Now() WHERE `TxnID` ='.$_GET['TxnID'];
   $sql='DELETE from `invty_7opapproval` WHERE TxnID IN (SELECT TxnID FROM `invty_2sale` WHERE Posted=0) AND `TxnID`='.$_GET['TxnID'];// echo $sql;break;
   $stmt=$link->prepare($sql);   $stmt->execute();
   header("Location:".$_SERVER['HTTP_REFERER']);
   break;

case 'LookupDD':
if (allowedToOpen(519,'1rtc')) {
    
    ?><br/><div style='border: 2px solid black; width:500px; padding: 5px;'>
Payments via pawnshops/remittance centers must be sent to:<br>
&nbsp; &nbsp; &nbsp; &nbsp; <b>Catherine T. Asturias<br>&nbsp; &nbsp; &nbsp; &nbsp;  (0917) 621 9020</b><br><br>
<!--Additional information for Palawan Pawnshop and Palawan Express Pera Padala:  Account No: <b>4215-8501-0462-9333</b><br><br>-->
<i>All other payments must be deposited to the company's bank accounts.</i></div><br/>
    <?php
    
    
// REQUEST APPROVAL
    if (allowedToOpen(521,'1rtc')){
    $title='Direct Deposits'; $showbranches=true; $method='post'; 
    
    $columnnames=array(
                  array('field'=>'DirectDepDate','type'=>'date','size'=>10,'required'=>true,'value'=>date("Y-m-d")),
                    array('field'=>'Client','type'=>'text','size'=>10,'required'=>true,'list'=>'clients'),
                    array('field'=>'Bank', 'type'=>'text','size'=>10,'required'=>true,'list'=>'banks'),
                    array('field'=>'RequestRemarks','type'=>'text','size'=>15,'required'=>false),
                    array('field'=>'Amount','type'=>'text','size'=>10,'required'=>true,'value'=>0),
                    array('field'=>'Approval','type'=>'hidden','size'=>0,'value'=>mt_rand(1000,99999)));
    
    $action='praddmain.php?w=NewDD'; $fieldsinrow=8;
    
    //to make list of banks
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    $listsql='SELECT m.ShortAcctID, m.AccountID, m.AcctNo, m.AcctName, '.$_SESSION['bnum'].' FROM `banktxns_1maintaining` as m 
WHERE (((m.OwnedByCompany)='.$_SESSION['*cnum'].')) UNION SELECT m.ShortAcctID, m.AccountID, m.AcctNo, m.AcctName, d.BranchNo FROM `banktxns_1maintaining` as m left JOIN `banktxns_branchdefaultbank` as d ON m.AccountID=d.BankAcctID WHERE (((d.BranchNo)='.$_SESSION['bnum'].')) UNION SELECT "Remittance",135,"","Remittance Centers",'.$_SESSION['bnum'].' ORDER BY ShortAcctID';
    ;
    echo comboBox($link, $listsql, 'ShortAcctID', 'AccountID', 'banks');    
    $liststoshow=array('clients');
    
    include('../backendphp/layout/inputmainform.php');}
// END OF REQUEST
// START OF LIST
$title='Approvals';

$fieldname='Month';
$month=(!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]);

$subtitle='</h4>'.'<form method="post" action="approval.php?w=LookupDD" enctype="multipart/form-data">
Choose Month (1 - 12):  <input type="text" name="'. $fieldname.'" size=5 value="'. $month.'"></input>
<input type="submit" name="lookup" value="Lookup"> </form><br><br>'.'<h4>';


  
 if (allowedToOpen(521,'1rtc')){ 
    $condition=''; $editprocess='approval.php?w=DelDD&Approval=';$editprocesslabel='Del';
} elseif (allowedToOpen(5191,'1rtc')){ 
     $condition=' and a.BranchNo in (Select g.BranchNo from `attend_1branchgroups` g where TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].')';
} elseif (allowedToOpen(5192,'1rtc')){ 
     $condition=' and a.BranchNo='.$_SESSION['bnum'];
} else {
   $condition='';  $columnnames=array();
}

$sql='Select a.Approval as TxnID, IF(a.`Approved?`=-1,"DENIED",IF(a.`Approved?`=0,"Pending",a.Approval)) AS Approval, c.ClientName, mt.ShortAcctID as Bank, a.RequestRemarks, a.Amount, a.DirectDepDate,m.CollectNo, b.Branch, ApproveRemarks, concat(e1.`Nickname`,\' \',e1.`SurName`) AS `RequestedBy`,  concat(e2.`Nickname`,\' \',e2.`SurName`) AS `ApprovedBy`, a.`TimeStamp` as RequestTS, `ApprovalTS`, if(isnull(m.TxnID),"not yet recorded","Done") as Recorded from approvals_2directdeposits a 
join `1employees` e1 on a.EncodedByNo=e1.IDNo
join `1employees` e2 on a.ApprovedByNo=e2.IDNo
join `1branches` b on b.BranchNo=a.BranchNo
left join `acctg_2collectmain` m on m.TxnID=a.TxnID
join `banktxns_1maintaining` mt on mt.AccountID=a.Bank
left join `1clients` c on c.ClientNo=a.ClientNo
where Month(a.`TimeStamp`)='.$month.$condition.' Order by DirectDepDate DESC, a.TimeStamp DESC,b.Branch';
$txnidname='TxnID';
$columnnames=array('DirectDepDate','Branch','ClientName','Bank','RequestRemarks','RequestedBy','RequestTS','CollectNo','Amount','Approval', 'ApproveRemarks','ApprovedBy','ApprovalTS','Recorded');

include('../backendphp/layout/displayastable.php');
} else { echo 'No permission'; exit; }
    break;
   
case 'RecordDD'://This is done in praddmain.php when encoding ORMain
   break;
   
case 'DelDD':
   if (!allowedToOpen(521,'1rtc')) {   echo 'No permission'; exit;}
   $sql='DELETE from approvals_2directdeposits WHERE isnull(TxnID) and `Approval`='.$_GET['Approval'].' and EncodedByNo='.$_SESSION['(ak0)']; //echo $sql;break;
   $stmt=$link->prepare($sql);
   $stmt->execute();
   header('Location:approval.php?w=LookupDD');
   break; 
   

}
noform:
      $link=null; $stmt=null;
?>