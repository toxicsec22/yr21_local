<?php
include_once('../switchboard/scrollcss.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
// UNBUDGETTED EXPENSES 
if (allowedToOpen(205,'1rtc')){
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT TypeID, BudgetDesc FROM acctg_1branchpreapprovedbudgetlist ','TypeID','BudgetDesc','typelist');	
echo comboBox($link,'SELECT EntityID, Entity FROM acctg_1budgetentities ','EntityID','Entity','entities');	
    $sqlsp1='Select a.*,Entity AS FromBudgetOf, eeswitch,IF(eeswitch IN (1,2,3,4,5,6,7),IF(eeswitch IN (1,4),"Finance Asst",IF(eeswitch=2,"Acctg TL - GA",IF(eeswitch IN (3,6),"SC Dept Head",IF(eeswitch=7,"Planning Assoc","Admin Dept Head")))),"") AS Approver,b.Branch,BudgetDesc from `approvals_2encashedexpenses` a  join `1branches` b on b.BranchNo=a.BranchNo left join acctg_1branchpreapprovedbudgetlist bl on bl.TypeID=a.TypeID left join acctg_1budgetentities be on be.EntityID=a.FromBudgetOf where isnull(Approval) ';
    $user=$_SESSION['&pos'];  $condition='';
    
      
    $stmtsp=$link->query($sqlsp1.$condition); $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>Expenses for approval:<table border="1px solid black" style="border-collapse:collapse;"><tr>';
    foreach($datatoshowsp as $rows){
        $msgsp=$msgsp.'<form method="post" action="/yr21/acctg/praddmain.php?w=ApproveRequestforExpense" enctype="multipart/form-data">'
                . '<td style="width:85px;">'.$rows['Date'].'</td><td>'.$rows['TimeStamp'].'</td><td>'.$rows['Branch'].'</td>
        <td>&nbsp &nbsp '.($rows['eeswitch']==4?'WaybillNo':($rows['eeswitch']==7?'Txfr Receipt':'Particulars')).' &nbsp<input type=text size=45 name=Particulars value="'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'" autocomplete="off" onclick="IsEmpty(Particulars);"></td>
		'.(!empty($rows['BudgetDesc'])?'<td style="white-space: pre;">&nbsp &nbsp Excess from Pre-approved for <b>'.$rows['BudgetDesc'].'</b></td>':'').'
        <td>&nbsp &nbsp Amount &nbsp<input type=text size=5 name=Amount value='.$rows['Amount'].' autocomplete="off" required=1 onclick="IsEmpty(Amount);"></td>'
                .((allowedToOpen(2051,'1rtc'))?'
					'.(empty($rows['BudgetDesc'])?'
				<td>&nbsp &nbsp Choose AccountID<input type=text size=10 name=AccountID list=accounts  autocomplete="off"  required=1 onclick="IsEmpty(AccountID);"></td><td>&nbsp &nbsp Change Branch To<input type=text size=10 name=Branch list="branches"  autocomplete="off"  required=1 value="'.$rows['Branch'].'"></td>':'').'
        <td>From Budget Of: <input type="text" name="FromBudgetOf" list="entities" value="'.($rows['eeswitch']==7?$rows['FromBudgetOf']:$rows['Branch']).'"></td><td>Approver: '.$rows['Approver'].'</td><td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" /><input type=hidden name=ApprovalID value='.$rows['ApprovalId'].'>
        <input type=hidden name=Approval value='.mt_rand(1000,99999).'>&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Approve">':'')
                .'</form></tr><tr>';
        
   }
   echo $msgsp.'</tr></table><br></div>';   
   include_once('../acctg/acctglists.inc');   renderotherlist('accounts','');
   }
   $stmtsp=null;
   }
   
 
 // FREIGHT-CLIENT EXPENSES 
if (allowedToOpen(206,'1rtc')){
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';	
echo comboBox($link,'SELECT EntityID, Entity FROM acctg_1budgetentities ','EntityID','Entity','entities');	
    $sqlsp='Select a.*, concat(Particulars,(if(a.txntype=2," Charge ", " Cash ")) , " Invoice No. ", ForInvoiceNo, " ", IF(PriceFreightInclusive=1,"PriceINCLUDESFreight","PriceNOFreight")) as Particulars,b.Branch from `approvals_2freightclients` a  join `1branches` b on b.BranchNo=a.BranchNo where isnull(Approval);';
     
    $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<div><br>Freight-Client for approval:<table><tr>';
    foreach($datatoshowsp as $rows){
        $msgsp=$msgsp.'<form method="post" action="/yr21/acctg/praddmain.php?w=ApproveFreightClientExpense" enctype="multipart/form-data"><td>'.$rows['Branch'].'</td>
        <td>&nbsp &nbsp &nbsp'.$rows['Date'].'</td><td>&nbsp &nbsp &nbsp'.htmlcharwithbr($fromBRtoN,$rows['Particulars']).'</td>
        <td>&nbsp &nbsp Amount &nbsp<input type=text size=5 name=Amount value='.$rows['Amount'].' autocomplete="off" required=1 onclick="IsEmpty(Amount);"></td>
        <td>From Budget Of: <input type="text" name="FromBudgetOf" list="entities" value="'.$rows['Branch'].'"></td><td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" /><input type=hidden name=ApprovalID value='.$rows['ApprovalId'].'>
        <input type=hidden name=Approval value="'.mt_rand(1000,99999).'FC">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Approve"></td></form></tr><tr>';
        
   }
   echo $msgsp.'</tr></table><br></div>';
   
   }
   $stmtsp=null;
 }
 
  
 // SPECIAL DISCOUNTS  
 
if (allowedToOpen(6929,'1rtc')){ 
    $sqlsp='SELECT a.TxnID, SaleNo, txntype, b.Branch FROM `invty_7specdisctapproval` a join invty_2sale m on m.TxnID=a.TxnID join `1branches` b on b.BranchNo=m.BranchNo where Approved=0 group by m.TxnID;';
  // echo $sqlsp; break;
     $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div id="table-wrapper" >SPECIAL PRICE for approval:<div id="table-scroll"><table>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        // $msgsp=$msgsp.'<tr><td><a style="color:blue;text-decoration:none;" href="/yr21/invty/addeditsale.php?TxnID='.$rows['TxnID'].'&txntype='.$rows['txntype'].'" target=_blank>'.$rows['Branch'].' Inv '.$rows['SaleNo'].'</a></td></tr>';
        $msgsp=$msgsp.'<tr><td>'.$rows['Branch'].'</td><td>'.$rows['SaleNo'].'</td><td><a style="color:blue;text-decoration:none;" href="/yr21/invty/addeditsale.php?TxnID='.$rows['TxnID'].'&txntype='.$rows['txntype'].'&specprice=1" target=_blank>Look up</a></td></tr>';
        
   }
   echo $msgsp.'</tr></table><br></div></div>';
   }
   
 }
 // RETURNS 
if (allowedToOpen(208,'1rtc')){  
 $sqlsp='Select b.Branch,sm.Date from `approvals_2salesreturns` sr join invty_2sale sm on sm.TxnID=sr.TxnID join `1branches` b on b.BranchNo=sr.BranchNo where sr.Approval is null';
 
$sqlsp=$sqlsp.' UNION SELECT b.Branch,sm.Date from `approvals_2salesreturns` sr join `'.$lastyr.'_1rtc`.`invty_2sale` sm ON (Select CONCAT("'.substr($lastyr, -2).'00",sm.TxnID))=sr.TxnID join `1branches` b on b.BranchNo=sr.BranchNo where sr.Approval is null';


     
    
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>RETURNS for approval:<table><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="/yr21/invty/addsalemain.php?w=Return&saletype=5"</a>'.$rows['Branch'].' from Sale on '.$rows['Date'].'</td>'.($countsp%15==0?'</tr><tr>':'');
        
   }
   echo $msgsp.'</tr></table><br><br></div>';
   }
   
 }
 // Cancelled SRS 
if (allowedToOpen(208,'1rtc')){  
 $sqlsp='Select b.Branch,Reason from `approvals_2salesreturns` sr join `1branches` b on b.BranchNo=sr.BranchNo where TxnID=1 AND ApprovedByNo IS NULL';

    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>Cancelled SRS:<table><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="/yr21/invty/addsalemain.php?w=Cancel&saletype=cancel5"</a>'.$rows['Branch'].'</td>'.($countsp%15==0?'</tr><tr>':'');
        
   }
   echo $msgsp.'</tr></table><br><br></div>';
   }
   
 }
 
  // STORE USED  
if (allowedToOpen(209,'1rtc')){  
 $sqlsp='SELECT su.*, ItemDesc,Category,Branch, Nickname as EncodedBy FROM approvals_2storeused su join invty_1items i on su.ItemCode=i.ItemCode join invty_1category c on c.CatNo=i.CatNo join `1branches` b on b.BranchNo=su.BranchNo join `1employees` e on e.IDNo=su.EncodedByNo where Approved=0';
     
    
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>STORE USED for approval:<table bgcolor="FFFFF"><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="/yr21/invty/addmrrmain.php?w=StoreUsed&saletype=9"</a>'.$rows['Branch'].' -  '.htmlcharwithbr($fromBRtoN,$rows['Category']).' '.htmlcharwithbr($fromBRtoN,$rows['ItemDesc']).'</td>'.($countsp%15==0?'</tr><tr>':'');
        
   }
   echo $msgsp.'</tr></table><br><br></div>';
   }
   
 }   

// DEFECTIVE  
if (allowedToOpen(210,'1rtc')){  
 $sqlsp='Select b.Branch,sd.ItemCode, Category, ItemDesc, sd.Qty, Unit from `approvals_2setasdefective` sd join `1branches` b on b.BranchNo=sd.BranchNo join invty_1items i on sd.ItemCode=i.ItemCode join invty_1category c on c.CatNo=i.CatNo 
        where sd.Approval is null';
     
    
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div><br>DEFECTIVE for approval:<table bgcolor="FFFFF"><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="/yr21/invty/setasdefective.php?w=RequestSetDefective"</a>'.$rows['Branch'].': '.$rows['Qty'].' '.$rows['Unit'].' '.htmlcharwithbr($fromBRtoN,$rows['Category']).' '.htmlcharwithbr($fromBRtoN,$rows['ItemDesc']).'</td>'.($countsp%15==0?'</tr><tr>':'');
        
   }
   echo $msgsp.'</tr></table><br><br></div>';
   }
   
 } 
 $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
  
 
 if (allowedToOpen(213,'1rtc')) {
	if (allowedToOpen(2131,'1rtc')) { //Rce/jye
		$cond=' AND LatestSupervisorIDNo IN (1001,1002) ';
	}
	else if (allowedToOpen(2133,'1rtc'))  { //Stores Branch Heads
		$stmt0=$link->query('SELECT IFNULL(GROUP_CONCAT(BranchNo),0) AS BranchNo FROM attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].'');
		$res0=$stmt0->fetch();
		// $cond2=' AND p.BranchNo IN ('.$res0['BranchNo'].')';
		// $cond=$cond2.' AND p.PositionID IN (32,37,81)';
		$cond=' AND (p.BranchNo IN ('.$res0['BranchNo'].'))';
		// $cond=$cond2.' AND p.PositionID IN (32,37,81)) OR lr.IDNo IN (SELECT IDNo FROM attend_30currentpositions WHERE LatestSupervisorIDNo='.$_SESSION['(ak0)'].' AND SupervisorApproved=0)';
	} else if (allowedToOpen(6110,'1rtc')){ //assistants
           $stmt0=$link->query('SELECT deptid FROM attend_0positions WHERE PositionID='.$_SESSION['&pos']);
           $res0=$stmt0->fetch();
           $cond=' AND p.deptid IN ('.$res0['deptid'].')'; }
	else { //LatestSupervisorIDNo
		$cond=' AND p.LatestSupervisorIDNo='.$_SESSION['(ak0)'];
	}
	//First Approval
	$stmtsuper1=$link->query('SELECT lr.*, FullName,LatestSupervisorIDNo, Branch, LeaveName as LeaveType, CONCAT("SLBal: ", IFNULL(SLBal,0)," VLBal: ",IFNULL(VLBal,0)," BirthdayBal: ",IFNULL(BirthdayBal,0)) AS LeaveBalBeforeThisLeave, lr.TimeStamp as RequestTS 
        FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo
        JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo WHERE SupervisorApproved=0 '.$cond);
    $datatoshowsuper=$stmtsuper1->fetchAll();
    if ($stmtsuper1->rowCount()>0){
        $colstoshow=array('FullName', 'Branch', 'FromDate', 'ToDate', 'LeaveType', 'Reason', 'LeaveBalBeforeThisLeave','RequestTS'); $coltitle='';
        foreach ($colstoshow as $field) {$coltitle=$coltitle.'<td>' . $field.'</td>'; }
        $msgsuper='<br><div><br>Leave Requests<table bgcolor="FFFFF"><tr>'.$coltitle.'</tr><tr>';
    
		foreach($datatoshowsuper as $rows){
			$cols='';
			foreach ($colstoshow as $field) {$cols=$cols.'<td>' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
				$msgsuper.='<form method="post" action="/yr21/attendance/leaverequest.php?w=SupervisorApprove&TxnID='.$rows['TxnID'].'">'.$cols
				.((($rows['LatestSupervisorIDNo']==$_SESSION['(ak0)']) OR (allowedToOpen(2131,'1rtc')))?'<td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" />
				Comments, if any: <input type="text" size=15 name="SupervisorComment" placeholder="blank if no comment"></td>
				<td>&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Approve">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Deny"></td>':'').'</form>';
			$msgsuper.='</tr><tr>';
			}
			
		echo $msgsuper.'<br></tr></table><br><br></div>';
	}
	
	$sqlall='SELECT lr.*,lr.TimeStamp AS RequestTS, FullName, Branch, LeaveName as LeaveType, CONCAT("SLBal: ", IFNULL(SLBal,0)," VLBal: ",IFNULL(VLBal,0)," BirthdayBal: ",IFNULL(BirthdayBal,0)) AS LeaveBalBeforeThisLeave, IF(SupervisorApproved=1,"Approved","Denied") AS SupervisorResponse';
	
	//Final approval 
	// leave requests status before dept head response
	if ((allowedToOpen(2135,'1rtc')) OR (allowedToOpen(2134,'1rtc'))){
			if (allowedToOpen(2135,'1rtc')){ //Ops Manager
				$cond=' AND p.deptid=10';
			}
			else if (allowedToOpen(2134,'1rtc')) { //Acctg Associate
				$cond=' AND p.PositionID IN (13,131,132)';
			}
			$stmtsuper1=$link->query(''.$sqlall.' FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo WHERE SupervisorApproved<>0 AND Approved=0 AND MarkasReadByDeptHead=0 AND (Acknowledged=0 OR (HRVerifiedByNo IS NULL)) '.$cond);
			
			$datatoshowsuper=$stmtsuper1->fetchAll();
			if ($stmtsuper1->rowCount()>0){
				$colstoshow=array('FullName', 'Branch', 'FromDate', 'ToDate','RequestTS', 'LeaveType', 'Reason','LeaveBalBeforeThisLeave','SupervisorComment','SupervisorResponse','SupervisorTS'); $coltitle='';
				foreach ($colstoshow as $field) {$coltitle=$coltitle.'<td>' . $field.'</td>'; }
				$msgsuper2='<br><div><br>Leave Requests<table bgcolor="FFFFF"><tr>'.$coltitle.'</tr><tr>';
			foreach($datatoshowsuper as $rows){
				$cols='';
				foreach ($colstoshow as $field) {$cols=$cols.'<td>' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
				$msgsuper2=$msgsuper2.'<form method="post" action="/yr21/attendance/leaverequest.php?w=Approve&TxnID='.$rows['TxnID'].'">'.$cols
		.'<td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" />
		Comments, if any: <input type="text" size=15 name="ApproveComment" placeholder="blank if no comment"></td>
		<td>&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Approve">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="Deny"></td></form></tr><tr>';
				}
			echo $msgsuper2.'<br></tr></table><br><br></div>';
			
		}
	}
	else { 
	$stmtsuper1=$link->query(''.$sqlall.', IF(p.PositionID IN (13,131,132),"Waiting for final approval","'.(!allowedToOpen(2133,'1rtc')?"Waiting for Dept Head response":"Waiting for Manager response").'") AS Next_Action_Must_Be FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo WHERE SupervisorApproved<>0 AND Approved=0 AND MarkasReadByDeptHead=0 AND (Acknowledged=0 OR (HRVerifiedByNo IS NULL)) '.$cond);
		$datatoshowsuper=$stmtsuper1->fetchAll();
		if ($stmtsuper1->rowCount()>0){
			$colstoshow=array('FullName', 'Branch', 'FromDate', 'ToDate','RequestTS', 'LeaveType', 'Reason','LeaveBalBeforeThisLeave','SupervisorComment','SupervisorResponse','SupervisorTS','Next_Action_Must_Be'); $coltitle='';
			foreach ($colstoshow as $field) {$coltitle=$coltitle.'<td>' . $field.'</td>'; }
			$msgsuper2='<br><div><br>Status of Leave Requests (for dept head response)<table bgcolor="FFFFF"><tr>'.$coltitle.'</tr><tr>';
		foreach($datatoshowsuper as $rows){
			$cols='';
			foreach ($colstoshow as $field) {$cols=$cols.'<td>' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
			$msgsuper2=$msgsuper2.$cols.'</tr><tr>';
			}
		echo $msgsuper2.'<br></tr></table><br><br></div>';
	   }
	}
}   

  
// LEAVE REQUESTS -- DEPT HEAD
if (allowedToOpen(214,'1rtc')) {
	$stmtsuper2=$link->query('SELECT lr.*, FullName, Branch, LeaveName as LeaveType, CONCAT("SLBal: ", IFNULL(SLBal,0)," VLBal: ",IFNULL(VLBal,0)," BirthdayBal: ",IFNULL(BirthdayBal,0)) AS LeaveBalBeforeThisLeave, IF(SupervisorApproved=1,"Approved",IF(SupervisorApproved=2,"Denied","")) AS SupervisorResponse,IF(Approved=1,"Approved",IF(Approved=2,"Denied","")) AS FinalResponse, e1.Nickname AS FinalApprover, e.Nickname AS Supervisor, lr.TimeStamp as RequestTS FROM attend_3leaverequest lr JOIN `attend_30currentpositions` p ON lr.IDNo=p.IDNo
		JOIN `attend_0leavetype` lt ON lt.LeaveNo=lr.LeaveNo JOIN `1employees` e on e.IDNo=lr.SupervisorByNo
		LEFT JOIN `attend_61leavebal` lb ON lb.IDNo=lr.IDNo LEFT JOIN `1employees` e1 on e1.IDNo=lr.ApprovedByNo
		WHERE SupervisorApproved<>0 AND IF(p.PositionID IN (13,131,132,32,37,50,81,33,38),Approved<>0,Approved<>1) AND MarkasReadByDeptHead=0 AND '.((allowedToOpen(2131,'1rtc'))?'deptheadpositionid IN (99,100)':'deptheadpositionid='.$_SESSION['&pos'].'').'');
		
		
	$datatoshowsuper=$stmtsuper2->fetchAll();
	if ($stmtsuper2->rowCount()>0){
		$colstoshow2=array('FullName', 'Branch', 'FromDate', 'ToDate', 'LeaveType', 'Reason','RequestTS', 'LeaveBalBeforeThisLeave', 'SupervisorComment', 'SupervisorResponse', 'Supervisor','SupervisorTS','FinalResponse','FinalApprover','ApproveTS'); $coltitle2='';
		foreach ($colstoshow2 as $field) {$coltitle2=$coltitle2.'<td>' . $field.'</td>'; }
		$msgsuper2='<br><div><br>Leave Requests<table bgcolor="FFFFF"><tr>'.$coltitle2.'</tr><tr>';
	foreach($datatoshowsuper as $rows){
		$cols2='';
		foreach ($colstoshow2 as $field) {$cols2=$cols2.'<td>' . htmlcharwithbr($fromBRtoN,$rows[$field]).'</td>'; }
		// $msgsuper2=$msgsuper2.'<form method="post" action="/yr21/attendance/leaverequest.php?w=Approve&TxnID='.$rows['TxnID'].'">'.$cols2
		$msgsuper2=$msgsuper2.'<form method="post" action="/yr21/attendance/leaverequest.php?w='.($rows['Approved']<>0?'MarkasReadByDeptHead':'Approve').'&TxnID='.$rows['TxnID'].'">'.$cols2
		.'<td><input type="hidden" name="action_token" value="'. html_escape($_SESSION['action_token']).'" />'.($rows['Approved']==0?'Comments, if any: <input type="text" size=15 name="ApproveComment" placeholder="blank if no comment">':'').'
		</td>
		<td>&nbsp &nbsp &nbsp<input type="submit" name="submit" value="'.($rows['Approved']<>0?'MarkasReadByDeptHead':'Approve').'">&nbsp &nbsp &nbsp<input type="submit" name="submit" value="'.($rows['Approved']<>0?'Set As Incomplete':'Deny').'"></td></form></tr><tr>';
   }
   echo $msgsuper2.'<br></tr></table><br><br></div>';
   }
   $stmtsuper2=null;
}
	

// Direct Deposits for approval
if (allowedToOpen(5211,'1rtc')){
    $sqlsp='Select TxnSubId, c.ClientName, mt.ShortAcctID as Bank, a.RequestRemarks, a.Amount, a.DirectDepDate, b.Branch, CONCAT(e2.Nickname, " ",Surname) AS RequestedBy from approvals_2directdeposits a 
join `1employees` e2 on a.EncodedByNo=e2.IDNo
join `1branches` b on b.BranchNo=a.BranchNo
join `banktxns_1maintaining` mt on mt.AccountID=a.Bank
left join `1clients` c on c.ClientNo=a.ClientNo
where a.`Approved?`=0 Order by DirectDepDate, a.TimeStamp, b.Branch';
     
    $stmtsp=$link->query($sqlsp); $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);  
    
    if ($stmtsp->rowCount()>0){
        $submitprocess='/yr21/acctg/praddmain.php?w=DDApproval';
        $columnnamessp=array('Branch','ClientName','Bank','DirectDepDate','Amount','RequestedBy','RequestRemarks'); 
        $fieldlist=''; $rows=''; $textfordisplay='';
        echo '<style>table, td, th {padding: 3px; border-collapse: collapse; border: 1px brown solid;"} </style>';
        foreach($columnnamessp as $field){ $fieldlist.='<th style="font-weight: bold; font-size: normal;">'.$field.'</th>';}
        $fieldlist.='<th>Response</th>';
        $msgcb='';
    foreach($datatoshowsp as $rows){        
        $textfordisplay.='<tr>';
        foreach($columnnamessp as $col){ $textfordisplay.='<td>'. htmlcharwithbr($fromBRtoN,addslashes($rows[$col])) . '</td>';}
        $textfordisplay.='<td><form method=post action="'.$submitprocess.'"><input type=text name=Remarks placeholder="Remarks (not required)">&nbsp; &nbsp;'
                . '<input type=hidden name=action_token value='.$_SESSION['action_token'].'>'
                . '<input type=hidden name=TxnSubId value='.$rows['TxnSubId'].'>'
                . '<input type=submit name=submit value=Approve>&nbsp; &nbsp;'
                . '<input type=submit name=submit value=Deny>'
                . '</form></td></tr>';
   }
   echo '<br><br><div style="background-color: fff2e6; font-size: normal; font-weight: bolder;" >Direct Deposits &nbsp; &nbsp;<table>'
            .$fieldlist.$textfordisplay.'</table></div><br><br>';
   }
    
   $stmtsp=null;
}

// PURCHASE ORDERS 
if (allowedToOpen(4031,'1rtc')){
    $sqlsp='Select m.TxnID, b.Branch,m.PONo, s.SupplierName AS Supplier, FORMAT(SUM(Qty*UnitCost),0) AS POAmount FROM invty_3order m join invty_3ordersub os on m.TxnID=os.TxnID
    JOIN `1branches` b ON b.BranchNo=m.BranchNo JOIN `1suppliers` AS s ON m.SupplierNo=s.SupplierNo
    WHERE m.Approved=0 AND m.Posted=1 GROUP BY m.TxnID ORDER BY m.BranchNo;';
    
    $stmtsp=$link->query($sqlsp);
    $datatoshowsp=$stmtsp->fetchAll(PDO::FETCH_ASSOC);    
    $countsp=0;
   if ($stmtsp->rowCount()>0){
    $msgsp='<br><div style="float:left"><br>PO\'s for approval:<table bgcolor="FFFFF"><tr>';
    foreach($datatoshowsp as $rows){
        $countsp++;
        $msgsp=$msgsp.'<td><a href="'.$path.'yr'.substr($currentyr,2).'/invty/praddext.php?w=ApprovePO&TxnID='.$rows['TxnID'].'"</a>'.$rows['Branch'].' PO No.: '.$rows['PONo'].' '.$rows['Supplier'].' <B>'.$rows['POAmount'].'</B></td>'.($countsp%5==0?'</tr><tr>':'');        
   }
   echo $msgsp.'</tr></table></div><br><br>';
   }
}

//Check Payment
include_once('../approvals/checkpaymentrequestapproval.php');

//List Of Request Repairs
include_once('../admin/motorvehiclesapproval.php');


 ?>