<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$allowed=array(8100,811,820,816,814,810,815,817,8172,8171,8173,8175,809,8091,821,813,819,822,823, 83005, 83006); 
if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');
$whichqry=$_GET['w'];

if ($whichqry=='Bonuses'){ goto skipsession;}
if (in_array($whichqry,array('FutureAdj','AdjPerPayID'))){
	
	 include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	 echo comboBox($link,'SELECT AdjustTypeNo, AdjustType FROM `payroll_0acctid` ORDER BY AdjustType','AdjustTypeNo','AdjustType','adjusttypes'); 
	echo comboBox($link,'SELECT IDNo, CONCAT(Nickname,\' \',SurName) AS FullName FROM `1employees` ORDER BY FullName','IDNo','FullName','employees');
	if(isset($_POST['filter'])){
	$adjusttypeno=comboBoxValue($link, 'payroll_0acctid', 'AdjustType', $_POST['adjusttype'], 'AdjustTypeNo');	
	$employee=comboBoxValue($link, '1employees', 'CONCAT(Nickname,\' \',SurName)', $_POST['employee'], 'IDNo');
	}
	
	$formdesc='</br><table style="border:1px solid black;  padding: 3px;  font-size:9pt;"><tr><td><h3>Filtering</h3>
			<form method="post" action="lookupwithedit.php?w='.$_GET['w'].'&edit=0">
			<input type="text" name="employee" list="employees" placeholder="Employee">
			<input type="text" name="adjusttype" list="adjusttypes" placeholder="AdjustType">
			<input type="submit" name="filter">
			</form></tr></td></table>';
	
	
}
include('payrolllayout/setpayidsession.php');
skipsession:
    
    $fieldname='payrollid';
    $showbranches=false;
    $method='POST';
    
//    $columnslist=array();
//    $liststoshow=array();

     
     switch ($whichqry){
        case 'PayDates':
	 if (!allowedToOpen(816,'1rtc')) { echo 'No permission'; exit;}
            $title='Pay Dates';
            $sql='SELECT * FROM `payroll_1paydates`';
            $orderby='PayrollID';
	    $txnid='PayrollID';
            $columnnames=array('PayrollID','PayrollCode','PayrollDate','FromDate','ToDate','WorkDays','LegalHolidays','SpecHolidays','Remarks','Posted','PostedByNo','TimeStamp');
	    $columnstoedit=array('PayrollDate','WorkDays','LegalHolidays','SpecHolidays','Remarks');
	    if (allowedToOpen(8171,'1rtc')){	$columnstoedit[]='Posted';  }
	    $editprocess='prpayrolldata.php?w=PayDates&edit=2&PayrollID='; $editprocesslabel='Enter';
	    if ($_GET['edit']==2){
            $txnid=$_GET['PayrollID'];
            $sql='SELECT `payroll_1paydates`.* FROM `payroll_1paydates` WHERE (`payroll_1paydates`.PayrollID)=\''.$txnid.'\'';
            $editprocess='prpayrolldata.php?w=PayDates&edit=2&PayrollID='.$txnid;$editprocesslabel='Enter';
	    include('../backendphp/layout/displayastableeditcells.php');
	    } else {
	    $sql='SELECT * FROM `payroll_1paydates`';   
	    include('../backendphp/layout/displayastableeditcells.php');
	    }
            break;
        case 'FutureAdj':
	 if (!allowedToOpen(814,'1rtc')) { echo 'No permission'; exit;}
            $title='Future Adjustments';
			
	    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'`PayrollID`,`IDNo`');
            $sql='SELECT AdjID,a.IDNo, a.PayrollID, FirstName, Nickname, e.SurName, a.AdjustTypeNo, payroll_0acctid.AdjustType, payroll_0acctid.ShortAcctID, a.AdjustAmt, a.Remarks, a.EncodedByNo, IF(e.Resigned=0,"","Resigned") AS Resigned, Branch,a.BranchNo FROM `1employees` as e RIGHT JOIN (payroll_21scheduledpaydayadjustments as a LEFT JOIN payroll_0acctid ON a.AdjustTypeNo = payroll_0acctid.AdjustTypeNo) ON e.IDNo = a.IDNo JOIN `1branches` b ON b.BranchNo=a.BranchNo JOIN payroll_1paydates pd ON pd.PayrollID=a.PayrollID AND pd.Posted=0 AND pd.PayrollDate>=CURDATE()  ';
            //$orderby='PayrollID,IDNo';
	    $txnid='AdjID';
            $columnnames=array('PayrollID','IDNo','FirstName','Nickname','SurName','Branch','AdjustTypeNo','AdjustType','ShortAcctID','AdjustAmt','Remarks','EncodedByNo','Resigned'); $columnsub=$columnnames;
	    $columnstoedit=array('PayrollID','IDNo','BranchNo','AdjustTypeNo','AdjustAmt','Remarks');
	    $editprocess='lookupwithedit.php?w=FutureAdj&edit=2&AdjID='; $editprocesslabel='Edit';
	    $delprocess='prpayrolldata.php?w=DelFutureAdj&AdjID=';
	    if ($_GET['edit']==2){
            $txnid=$_GET['AdjID'];
            $sql=$sql.' WHERE a.AdjID=\''.$txnid.'\'';
            $action='prpayrolldata.php?w=FutureAdj&AdjID='.$txnid;
	    include('../backendphp/layout/rendersubform.php');
	    } 
	    else { 
                $sql=$sql.' '.(isset($_POST['filter'])?'WHERE a.AdjustTypeNo=\''.$adjusttypeno.'\' and a.IDNo=\''.$employee.'\'':'').' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
	    include('../backendphp/layout/displayastablewithedit.php');
	    }
            break;
        
        case 'AttendPerPayID':
	 if (!allowedToOpen(810,'1rtc')) { echo 'No permission'; exit;}
            $title='Attendance per Payroll ID';
	    $lookupprocess='lookupwithedit.php?w=AttendPerPayID&edit=0';
	    $editprocess='lookupwithedit.php?w=AttendPerPayID&edit=2&TxnID='; $editprocesslabel='Edit';
	    if (isset($_SESSION['payrollidses'])){
	    $addlmenu='&nbsp &nbsp &nbsp &nbsp<a href="prpayrolldata.php?w=DeleteAttendBasis&PayrollID='.$_SESSION['payrollidses'].'&action_token='.$_SESSION['action_token'].'" OnClick="return confirm(\'Really delete this (unposted and unapproved ONLY) attendance basis?\');"> Delete this Attendance Basis for Payroll </a>';
	    }
	    
            $sql='SELECT a.*, NickName,FirstName,SurName, IF(deptid IN (10,2,3,4),Branch,Dept) AS Branch,IF(e.Resigned=0,"","Resigned") AS `Resigned?`, IF((`SLDays` + `VLDays` + `LWPDays` + `QDays` + `RegDaysPresent`)=0,0,if(LatestDorM=0,(RegDaysActual+PaidLegalDays+a.SLDays+VLDays+LWPDays),(RegDaysActual+PaidLegalDays+a.SLDays+VLDays+LWPDays+SpecDays))) AS DaysToBePaid FROM `payroll_20fromattendance` a JOIN `1employees` `e` ON `a`.`IDNo` = `e`.`IDNo` LEFT JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo AND a.IDNo=cp.IDNo JOIN payroll_20latestrates lr ON a.IDNo=lr.IDNo ';
            $orderby='IDNo';
	    $txnid='TxnID';
            $columnnames=array('IDNo','NickName','FirstName','SurName','Branch','Resigned?','RegDaysPresent','LWOPDays','LegalDays','SpecDays','SLDays','VLDays','RWSDays','RestDays','LWPDays','QDays','RegDaysActual','PaidLegalDays','RegExShiftHrsOT','RestShiftHrsOT','SpecShiftHrsOT','LegalShiftHrsOT','RestExShiftHrsOT','SpecExShiftHrsOT','LegalExShiftHrsOT','DaysToBePaid');
            if ($_GET['edit']==2){
            $txnid=intval($_GET['TxnID']);
            $sql=$sql.' WHERE (a.TxnID)=\''.$txnid.'\'';
            $action='prpayrolldata.php?edit=2&w=AttendPerPayID&TxnID='.$txnid;
	    $columnstoedit=array('LWOPDays','LegalDays','SpecDays','SLDays','VLDays','RWSDays','RestDays','LWPDays','QDays','RegDaysActual','PaidLegalDays','RegExShiftHrsOT','RestShiftHrsOT','SpecShiftHrsOT','LegalShiftHrsOT','RestExShiftHrsOT','SpecExShiftHrsOT','LegalExShiftHrsOT');
	    include('../backendphp/layout/rendersubform.php');
	    } else { 
	    include('payrolllayout/displayandeditpayrolldata.php');
	    }
            break;
        
        case 'MissingDataForPayroll':
	 if (!allowedToOpen(815,'1rtc')) { echo 'No permission'; exit;}
         $payrollid=(isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));
         ?>
        <form method="POST" action="lookupwithedit.php?w=MissingDataForPayroll&edit=0" enctype="multipart/form-data">
            For Payroll ID<input type='text' name='payrollid' list='payperiods' value='<?php echo $payrollid?>'></input>
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" /> 
<input type="submit" name="lookup" value="Lookup">
<?php
		include_once('../generalinfo/lists.inc');
	    renderlist('payperiods');
            $title='No Payroll for Scheduled Adjustment';
            $sql='SELECT a.IDNo, FirstName, Surname, AdjustType, AdjustAmt FROM payroll_21scheduledpaydayadjustments a LEFT JOIN `payroll_20fromattendance` p ON p.PayrollID=a.PayrollID and p.IDNo=a.IDNo
                JOIN `1employees` e ON e.IDNo=a.IDNo JOIN `payroll_0acctid` i ON i.AdjustTypeNo=a.AdjustTypeNo
                WHERE a.payrollid='.$_SESSION['payrollidses'].' and p.IDNo IS NULL AND a.IDNo>1002 ORDER BY IDNo'; //echo $_SESSION['payrollidses'];
            $columnnames=array('IDNo','FirstName', 'Surname', 'AdjustType','AdjustAmt' );
            include('../backendphp/layout/displayastable.php'); //echo $sql;
            //unset($title);
            $subtitle='No Approved Rate';
            $sql='SELECT a.IDNo, FirstName, Surname FROM payroll_20latestrates r RIGHT JOIN `payroll_20fromattendance` a ON a.idno=r.idno '
                    . ' JOIN `1employees` e ON e.IDNo=a.IDNo WHERE r.idno IS NULL;';
            $columnnames=array('IDNo','FirstName', 'Surname'); 
            include('../backendphp/layout/displayastableonlynoheaders.php');
            
            $subtitle='<br><br>With Loans, No Payroll';
            $sql='SELECT e.* FROM payroll_31loansmain lm JOIN payroll_32loanssub ls ON lm.TxnID=ls.TxnID join 1employees e on e.idno=lm.idno WHERE Resigned=0 AND PayrollID IS NULL AND LoanTypeID IN (30,31,32,33) AND lm.IDNo NOT IN (SELECT IDNo FROM `payroll_20fromattendance` WHERE PayrollID='.$_SESSION['payrollidses'].') GROUP BY lm.TxnID';
            $columnnames=array('IDNo','FirstName', 'SurName'); 
            include('../backendphp/layout/displayastableonlynoheaders.php');
            
            $subtitle='<br><br>No ATM for Payroll '.$_SESSION['payrollidses'];
            $sql='SELECT p.IDNo, FirstName, Surname FROM payroll_25payroll p'
                    . ' JOIN `1employees` e ON e.IDNo=p.IDNo WHERE p.payrollid='.$_SESSION['payrollidses'].' and (e.UBPATM IS NULL OR e.UBPATM=0);';
            $columnnames=array('IDNo','FirstName', 'Surname'); 
            include('../backendphp/layout/displayastableonlynoheaders.php');
            echo '<br>End of report';
            break;
            
case 'PayrollPerPayID':
	 if (!allowedToOpen(817,'1rtc')) { echo 'No permission'; exit;}
            $payrollid=(isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));
            if ($payrollid%2==0 AND $payrollid<=24){ //SSS  
                $temp=''; include_once 'sssbasistemptable.php';
                $sqlsss=', FORMAT(sb.Basis,0) AS SSSBasis,ss.SSECCredit,CONCAT(esb.Nickname,\' \',esb.SurName) as EncodedBy,sb.TimeStamp '; $join='JOIN sssbasis sb ON sb.IDNo=p.IDNo left join 1employees esb on esb.IDNo=sb.EncodedByNo LEFT JOIN payroll_0ssstable ss ON `p`.`SSS-EE`=(SSEE+ECEE+MPFEE) ';
                $columnnames=array('Branch','FullName','IDNo','RegDayBasic','RegDayDeM','RegDayTaxSh','VLBasic','VLDeM','VLTaxSh','SLBasic','SLDeM','SLTaxSh',
                'LWPBasic','LWPDeM','LWPTaxSh','RHBasicforDaily','RHDeMforDaily','RHTaxShforDaily',
                'AbsenceBasicforMonthly','AbsenceDeMforMonthly','AbsenceTaxShforMonthly',
                'UndertimeBasic','UndertimeDeM','UndertimeTaxSh','RegDayOT','RestDayOT','SpecOT','RHOT','Remarks','SSS-EE','SSSBasis','SSECCredit','PhilHealth-EE','PagIbig-EE','WTax','TotalAdj','NetPay','DisburseVia','DaysPaid Calculated');
            } else { //Wtax
                $sqlsss='';$join='';
                $columnnames=array('Branch','FullName','IDNo','RegDayBasic','RegDayDeM','RegDayTaxSh','VLBasic','VLDeM','VLTaxSh','SLBasic','SLDeM','SLTaxSh',
                'LWPBasic','LWPDeM','LWPTaxSh','RHBasicforDaily','RHDeMforDaily','RHTaxShforDaily',
                'AbsenceBasicforMonthly','AbsenceDeMforMonthly','AbsenceTaxShforMonthly',
                'UndertimeBasic','UndertimeDeM','UndertimeTaxSh','RegDayOT','RestDayOT','SpecOT','RHOT','Remarks','SSS-EE','PhilHealth-EE','PagIbig-EE','WTax','TotalAdj','NetPay','DisburseVia','DaysPaid Calculated');
            }
            
            $sql0='SELECT SUM(NetPay) AS NetPay FROM `payroll_25payrolldatalookup` WHERE PayrollID='.$payrollid; $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
            $formdesc='';
            if (allowedToOpen(8171,'1rtc')){ $formdesc='<br><br></i>Total cash needed: '.number_format($res0['NetPay'], 2).'<i>';}
            if (allowedToOpen(8164,'1rtc')){$formdesc=$formdesc.'<br><br><a href="prpayrolldata.php?w=ApprovePayrollAll&action_token='.$_SESSION['action_token'].'&PayrollID='.$payrollid.'">Approve Payroll ID '.$payrollid.'</a><br>';}
            $title='Payroll Data - '.$payrollid;
            $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'`BranchNo`,e.`FirstName`');
	    include('../backendphp/layout/clickontabletoedithead.php');
            $lookupprocess='lookupwithedit.php?w=PayrollPerPayID&edit=0';
	    if (allowedToOpen(8172,'1rtc')){
	    $editprocess='lookupwithedit.php?w=PayrollPerPayID&edit=2&TxnID='; $editprocesslabel='Edit';
	//    if (isset($_SESSION['payrollidses'])){
	    echo '&nbsp &nbsp &nbsp &nbsp<a href=lookupwithedit.php?w=AdjPerPayID&edit=0 target=_blank>Lookup Adjustment Details</a>&nbsp &nbsp &nbsp &nbsp
	    <a href="prpayrolldata.php?w=DeletePayroll&PayrollID='.$payrollid.'&action_token='.$_SESSION['action_token'].'" OnClick="return confirm(\'Really delete this (unposted) payroll?\');"> Delete this Payroll </a>';
	  //  }
	    }
	    
	    ?>
	    <br><br><form method="POST" action="<?php echo $lookupprocess ?>" enctype="multipart/form-data">
	       For Payroll ID<input type='text' name='payrollid' list='payperiods' value="<?php echo $payrollid; ?>"></input>
	       <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" /> 
               <input type="submit" name="lookup" value="Lookup"><br><br>
	    <?php
	    include_once('../generalinfo/lists.inc');
	    renderlist('payperiods');
	    if (!isset($_SESSION['payrollidses']) and $_GET['edit']<>2){ goto nodata;  }
	    ?>
	    </form>
	    <?php
	    
            $columnsub=$columnnames; 
            if (allowedToOpen(8171,'1rtc')){ $coltototal='NetPay'; $showgrandtotal=true; }
            // $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'`PayrollID`,`IDNo`');
            	    
            if ($_GET['edit']==2){
                echo '<div style="border: box solid black 1px; background-color: f2f2f2; width: 200px;"><font style="font-weight: bold; color: maroon;">&nbsp; Disburse Via options:<ul><li>&nbsp; &nbsp; 0 - Cash</li><li>&nbsp; &nbsp; 1 - BPI</li><li>&nbsp; &nbsp; 3 - UBP </li></ul></font></div>';
            $txnid=intval($_GET['TxnID']);
            $sql='SELECT b1.Branch AS RecordInBranch, b.Branch, p.* FROM `payroll_25payrolldatalookup` p
	    JOIN `1branches` b ON b.BranchNo=p.BranchNo JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo WHERE (TxnID)=\''.$txnid.'\'';
	    $sql=$sql.((!allowedToOpen(8173,'1rtc'))?' AND p.IDNo>1002':'');
            $action='prpayrolldata.php?edit=2&w=PayrollPerPayID&TxnID='.$txnid;
	    $columnstoedit=array('BranchNo','RegDayBasic','RegDayDeM','RegDayTaxSh','VLBasic','VLDeM','VLTaxSh','SLBasic','SLDeM','SLTaxSh',
        'LWPBasic','LWPDeM','LWPTaxSh','RHBasicforDaily','RHDeMforDaily','RHTaxShforDaily',
        'AbsenceBasicforMonthly','AbsenceDeMforMonthly','AbsenceTaxShforMonthly',
        'UndertimeBasic','UndertimeDeM','UndertimeTaxSh','RegDayOT','RestDayOT','SpecOT','RHOT','Remarks','SSS-EE','SSS-ER','PhilHealth-EE','PhilHealth-ER','PagIbig-EE','PagIbig-ER','WTax','DisburseVia');
   	    if (allowedToOpen(8174,'1rtc')){ $columnstoedit=array(); }
	    include('../backendphp/layout/rendersubform.php');
	    } else {
	       
	    $stmtposted=$link->query('SELECT Posted, `Timestamp` FROM `payroll_1paydates` WHERE PayrollID='.$_SESSION['payrollidses']); 
            $resultposted=$stmtposted->fetch(); 
            $postedstatus=($resultposted['Posted']==1?'<h4 style="color: blue">POSTED</h4>':'<h4 style="color: red">UNPOSTED</h4>');
            echo $postedstatus.'<br><br>';
                
                
	    if (allowedToOpen(8172,'1rtc')) {
	       $sqlco='SELECT CompanyNo, CompanyName, Company FROM 1companies WHERE Active<>0';
	    } 
            $stmtco=$link->query($sqlco); $resultco=$stmtco->fetchAll();
	    
	    foreach ($resultco as $co){
                
            $stmtapproved=$link->query('SELECT IFNULL(SUM(Approved),0) AS Approval FROM `payroll_26approval` a WHERE PayrollID='.$_SESSION['payrollidses'].' AND a.CompanyNo='.$co['CompanyNo']); $resultapproved=$stmtapproved->fetch();    
                
            
            if (allowedToOpen(8175,'1rtc')){    
            // $stmtapproved=$link->query('SELECT IFNULL(SUM(Approved),0) AS Approval FROM `payroll_26approval` a WHERE PayrollID='.$_SESSION['payrollidses'].' AND a.CompanyNo='.$co['CompanyNo']); $resultapproved=$stmtapproved->fetch();}
            $approvestatus=($resultapproved['Approval']<>0?'<h4 style="color: blue">APPROVED</h4>':'<h4 style="color: red">FOR APPROVAL</h4>');
            echo $approvestatus.'<br><br>'; }
	    $sql='SELECT b1.Branch AS RecordInBranch, b.Branch, p.*'.$sqlsss.', TRUNCATE((`p`.`RegDayBasic` + `p`.`VLBasic` + `p`.`SLBasic` + `p`.`LWPBasic` + `p`.`RHBasicforDaily` - `p`.`AbsenceBasicforMonthly` - `p`.`UndertimeBasic`)/(SELECT IF(LatestDorM=0,LatestBasicRate,LatestBasicRate/13.04) FROM `payroll_20latestrates` lr WHERE lr.IDNo=p.IDNo ),2) AS `DaysPaid Calculated` FROM `payroll_25payrolldatalookup` p JOIN `1employees` `e` ON `p`.`IDNo` = `e`.`IDNo`
	    JOIN `1branches` b ON b.BranchNo=p.BranchNo
	    JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo '.$join.'
	    WHERE PayrollID='.$_SESSION['payrollidses'].' AND e.RCompanyNo='.$co['CompanyNo']
	    .((!allowedToOpen(8173,'1rtc'))?' AND p.IDNo>1002':'')
	    .' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
            $txnid='TxnID'; //echo $sql;
            
	    $addlmenu=($resultapproved['Approval']==0?'<a href="prpayrolldata.php?w=ApprovePayroll&Company='.$co['CompanyNo'].'&action_token='.$_SESSION['action_token'].'&PayrollID='.$_SESSION['payrollidses'].'">Approve '.$co['Company'].' Payroll ID '.$_SESSION['payrollidses'].'</a><br>':
                '&nbsp &nbsp <a href="prpayrolldata.php?w=RemoveApproval&Company='.$co['CompanyNo'].'&action_token='.$_SESSION['action_token'].'&PayrollID='.$_SESSION['payrollidses'].'">Remove Approval</a><br>');
	       $subtitle='<font color="darkblue">'.$co['CompanyName'].'</font>';
		   // echo $sql; exit();
	       include('../backendphp/layout/displayastableonlynoheaders.php');
	       echo '<br><br>';
	    }
	    }
            break;
            
case 'AdjPerPayID':
	 if (!allowedToOpen(809,'1rtc')) { echo 'No permission'; exit;}
            $title='Adjustments per Payroll ID';
	    if(!isset($_GET['f'])){ 
                $addlcondition=''; $inv='';
                $addlmenu='&nbsp &nbsp &nbsp &nbsp<a href=lookupwithedit.php?w=AdjPerPayID&edit=0&f=InvDisc target=_blank>Invty Discrepancies Only</a>';
            } else { 
                $addlcondition=' AND a.AdjustTypeNo=10'; $inv='&f=InvDisc'; 
                $addlmenu='&nbsp &nbsp &nbsp &nbsp<a href=lookupwithedit.php?w=AdjPerPayID&edit=0 target=_blank>Show All</a>';
            }
            $lookupprocess='lookupwithedit.php?w=AdjPerPayID&edit=0'.$inv;
            $sql='SELECT AdjID,a.IDNo, a.PayrollID, FirstName, Nickname, e.SurName, a.AdjustTypeNo, payroll_0acctid.AdjustType, payroll_0acctid.ShortAcctID, FORMAT(a.AdjustAmt,2) AS AdjustAmt, Remarks,BranchNo, a.EncodedByNo FROM `1employees` as e JOIN (`payroll_21paydayadjustments` as a JOIN payroll_0acctid ON a.AdjustTypeNo = payroll_0acctid.AdjustTypeNo '.$addlcondition.') ON e.IDNo = a.IDNo';
            $orderby='a.PayrollID,a.IDNo';
	    
	    $txnid='AdjID'; $sumfield='AdjustAmt';
            $sumsql='SELECT SUM(AdjustAmt) AS AdjustAmt FROM payroll_21paydayadjustments a ';
            $columnnames=array('PayrollID','IDNo','FirstName','Nickname','SurName','AdjustType','ShortAcctID','AdjustAmt','Remarks','BranchNo');
            
	    if (allowedToOpen(8091,'1rtc')) {
	    $editprocess='lookupwithedit.php?w=AdjPerPayID&edit=2&AdjID=';
	    $delprocess='prpayrolldata.php?w=DelAdjPerPayID&AdjID=';
	    $addlmenu.='&nbsp &nbsp &nbsp &nbsp<a href=addentry.php?w=AdjPerPayID target=_blank>Add Adjustments</a>';
	    }
	    if ($_GET['edit']==2){
            $txnid=$_GET['AdjID'];
	    if (allowedToOpen(8091,'1rtc')) { $columnstoedit=array('PayrollID','IDNo','AdjustTypeNo','AdjustAmt','Remarks','BranchNo');} else { $columnstoedit=array();}
            $sql=$sql.' WHERE (AdjID)=\''.$txnid.'\'';
            $action='prpayrolldata.php?w=AdjPerPayID&AdjID='.$txnid;
	    include('../backendphp/layout/rendersubform.php');
	    } else { 
                echo '<div><div style="float: left;">';
				
	    include('payrolllayout/displayandeditpayrolldata.php');
            if (!isset($_SESSION['payrollidses'])) { goto nodata;}
                echo '</div><br style="line-height:100px;" /><div style="margin-left: 60%;">';
            unset($addlmenu,$lookupprocess,$editprocess,$delprocess);
            $subtitle='Total per Adjustment Type';
            // $sql='SELECT AdjustType, FORMAT(SUM(AdjustAmt),2) AS AdjustAmt FROM payroll_21paydayadjustments a JOIN payroll_0acctid ai ON a.AdjustTypeNo = ai.AdjustTypeNo WHERE a.PayrollID='.$_SESSION['payrollidses'].$addlcondition.' GROUP BY AdjustType';
			$sql='SELECT AdjustType, FORMAT(SUM(AdjustAmt),2) AS AdjustAmt FROM payroll_21paydayadjustments a JOIN payroll_0acctid ai ON a.AdjustTypeNo = ai.AdjustTypeNo '.(!isset($_POST['filter'])? 'WHERE a.PayrollID='.$_SESSION['payrollidses'].$addlcondition.' GROUP BY AdjustType':'WHERE a.AdjustTypeNo=\''.$adjusttypeno.'\' and a.IDNo=\''.$employee.'\' ');  
            $columnnames=array('AdjustType','AdjustAmt'); $width='25%';
            include('../backendphp/layout/displayastableonlynoheaders.php');
            echo '<br/><br style="line-height:100px;" />';
            $subtitle='Total per Branch';
            // $sql='SELECT Branch, FORMAT(SUM(AdjustAmt),2) AS AdjustAmt FROM payroll_21paydayadjustments a JOIN 1branches b ON b.BranchNo = a.BranchNo WHERE a.PayrollID='.$_SESSION['payrollidses'].$addlcondition.' GROUP BY a.BranchNo';
			$sql='SELECT Branch, FORMAT(SUM(AdjustAmt),2) AS AdjustAmt FROM payroll_21paydayadjustments a JOIN 1branches b ON b.BranchNo = a.BranchNo '.(!isset($_POST['filter'])? 'WHERE a.PayrollID='.$_SESSION['payrollidses'].$addlcondition.' GROUP BY a.BranchNo':'WHERE a.AdjustTypeNo=\''.$adjusttypeno.'\' and a.IDNo=\''.$employee.'\' '); 
            $columnnames=array('Branch','AdjustAmt'); $width='25%';
			// echo $sql; exit();
            include('../backendphp/layout/displayastableonlynoheaders.php');
            echo '</div></div>';
	    }
            break;

case 'Summary_for_Bank':
      if (!allowedToOpen(821,'1rtc')) { echo 'No permission'; exit;}
//      $via=!isset($_GET['v'])?3:$_GET['v'];
//      switch ($via){ case 3: $bank='UBP'; $show='1>BPI';; break; case 1: $bank='BPI'; $show='3>UBP'; break; default: $bank='<i>CHOOSE BANK</i>'; $show='UBP';}
      $via=3; $bank='UBP';
      $title='Summary for '.$bank; 
      
      $a='lookupwithedit.php?w=Summary_for_Bank&edit=0&v=';
   //   $formdesc='Change bank to </i><a href='.$a.$show.'</a><i><br><br>';
      $lookupprocess=$a.$via;
      //$editprocess='lookupwithedit.php?w=Summary_for_BPI&edit=0';
      $sql='SELECT PayrollID, p.IDNo, concat(FirstName," ",SurName) as FullName,UBPATM, FORMAT(NetPay,2) as NetPay FROM `payroll_25payrolldatalookup` as p join `1employees` as e on p.IDNo=e.IDNo ';
      $addlcondition=((!allowedToOpen(8211,'1rtc'))?' AND p.IDNo>1002 AND DisburseVia='.$via.' ':' AND DisburseVia='.$via.' ');
      $addlcondition=$addlcondition.' AND (PayrollID NOT IN (Select PayrollID from payroll_26approval WHERE Approved=0 OR Approved IS NULL))'
                  .' AND (PayrollID IN (Select PayrollID from payroll_26approval))';
      $orderby='PayrollID,IDNo';
      
      $txnid='IDNo';
      $columnnames=array('PayrollID','IDNo','FullName','UBPATM','NetPay');
      $sumfield='NetPay';
      $sumsql='Select Sum(NetPay) as NetPay FROM `payroll_25payrolldatalookup` ';
      include('payrolllayout/displayandeditpayrolldata.php');
      break;
    case 'Summary_for_GCash':
         if(!allowedToOpen(83005, '1rtc')){
            echo 'No permission';
            exit;
         }
         $lookupprocess='lookupwithedit.php?w=Summary_for_GCash&edit=0';
         $sql='SELECT 
                  PayrollID,
                  p.IDNo,
                  CONCAT(e.FirstName, \' \', e.SurName) AS FullName,
                  GCashMobileNumber,
                  CompanyName,
                  FORMAT(NetPay, 2) AS NetPay
               FROM
                  `payroll_25payrolldatalookup` AS p
                  JOIN `1employees` AS e ON p.IDNo = e.IDNo 
                  LEFT JOIN `1_gamit`.`0idinfo` AS i ON e.IDNo = i.IDNo
                  LEFT JOIN 1companies AS c ON e.RCompanyNo = c.CompanyNo';

         $addlcondition=((!allowedToOpen(83006,'1rtc'))?' AND p.IDNo>1002 AND DisburseVia = 2 ':' AND DisburseVia = 2 ');
         $addlcondition=$addlcondition.'  AND (PayrollID NOT IN 
                                             (SELECT PayrollID
                                                FROM payroll_26approval
                                                WHERE Approved = 0 OR Approved IS NULL))
                                          AND (PayrollID IN 
                                             (SELECT PayrollID
                                                FROM payroll_26approval))';
         $orderby='PayrollID, CompanyName ,IDNo';
         
         $txnid='IDNo';
         $title = 'Lookup summary for GCash';
         $columnnames=array('PayrollID', 'CompanyName' ,'IDNo','FullName','GCashMobileNumber','NetPay');
         $sumfield='NetPay';
         $sumsql='Select Sum(NetPay) as NetPay FROM `payroll_25payrolldatalookup` ';
         include('payrolllayout/displayandeditpayrolldata.php');
         break;
    case 'Disbursement_Summary':
         if(!allowedToOpen(83007, '1rtc')){
            echo 'No permission';
            exit;
         }
         $sql = 'SELECT DisburseVia, (CASE WHEN DisburseVia=2 THEN "GCash" WHEN DisburseVia=3 THEN "UBP" WHEN DisburseVia=1 THEN "BPI" ELSE "Cash" END) AS Disburse_Via, COUNT(IDNo) AS `Employee_Count`, FORMAT(SUM(TRUNCATE(NetPay, 2)),2) AS `Amount` FROM payroll_25payrolldatalookup ';
            $title = 'Disbursement Summary';
            $lookupprocess='lookupwithedit.php?w=Disbursement_Summary';
            $addlcondition=' GROUP BY DisburseVia ';
            $orderby='Disburse_Via';
	    
	    $txnid='payrollid'; $hidecount=1;
            $columnnames=array('Disburse_Via','Employee_Count');
            if(allowedToOpen(83008, '1rtc')){ $columnnames[]='Amount';}
	    include('payrolllayout/displayandeditpayrolldata.php');
            $sumsql='Select Sum(TRUNCATE(NetPay, 2)) AS Amount, COUNT(IDNo) AS TotalPayees FROM `payroll_25payrolldatalookup` WHERE PayrollID='.$_SESSION['payrollidses'];
            $stmt=$link->query($sumsql); $datatoshow=$stmt->fetch(PDO::FETCH_ASSOC);
            echo '<br>Number of Employees: '.$datatoshow['TotalPayees'];
            if(allowedToOpen(83008, '1rtc')){ echo '<br>Total for this payroll: '.number_format($datatoshow['Amount'],2);}
         break;
	 case 'ExpenseDistri':
	    if (!allowedToOpen(813,'1rtc')) { echo 'No permission'; exit;}
	    $title='Expense Distribution for Adjustments';
	    $lookupprocess='lookupwithedit.php?w=ExpenseDistri&edit=0';
	    $editprocess='lookupwithedit.php?w=ExpenseDistri&edit=0';
            $sql='SELECT * FROM payroll_31empexpenseadjustamts ';
	    $orderby='PayrollID,Branch';
	    
	    $txnid='payrollid';
            $columnnames=array('PayrollID','Branch','DR','CR','Amt');
	    $sumfield='Amt';
	    $sumsql='Select Sum(Amt) as NetPay FROM `payroll_31empexpenseadjustamts` ';
            include('payrolllayout/displayandeditpayrolldata.php');
            break;
	 case 'SpecCredits':
	    if (!allowedToOpen(820,'1rtc')) { echo 'No permission'; exit;}
            $title='Special Credits';
            $sql='SELECT c.*,concat(e.`FirstName`,\' \',e.SurName) as FullName FROM payroll_30othercredits as c left join `1employees` as e on e.IDNo=c.IDNo';
            $orderby='Batch, FullName';
	    $txnid='idothercredits';
	    $fieldname='DateofCredit';
            $columnnames=array('idothercredits','DateofCredit','IDNo','FullName','Amount','Remarks','EncodedByNo', 'Batch');
	    $columnstoedit=array('DateofCredit','IDNo','Amount','Remarks', 'Batch');
	    $lookupprocess='lookupwithedit.php?w=SpecCredits&edit=0';
	    $editprocess='lookupwithedit.php?w=SpecCredits&edit=2&idothercredits=';$editprocesslabel='Edit';
	    $delprocess='prpayrolldata.php?w=DelSpecCredits&idothercredits=';
	    $sumfield='Amount';
	    $sumsql='Select Sum(Amount) as Amount FROM `payroll_30othercredits` ';
	    if ($_GET['edit']==2){
            $txnid=$_GET['idothercredits'];
            $sql=$sql.' WHERE (idothercredits)=\''.$txnid.'\'';
            $action='prpayrolldata.php?w=SpecCredits&idothercredits='.$txnid;
	    include('../backendphp/layout/rendersubform.php');
	    } 
	    else { 
	    include('../backendphp/layout/displayastablewithconditionandedit.php');
	    }
            break;
	 case 'Bonuses':
	    if (!allowedToOpen(811,'1rtc')) { echo 'No permission'; exit;}
	    $title='Planned Bonuses'; $addlmenu='';
            
            if(allowedToOpen(8112,'1rtc')){
            $formdesc='<a href="addentry.php?w=Bonuses">Add Bonus Data</a>&nbsp &nbsp &nbsp  &nbsp &nbsp<a href="lookupwithedit.php?w=Bonuses&filter=SendtoPayroll"  OnClick="return confirm(\'Bonuses are final?\');">Send Bonus Data To Payroll</a>&nbsp &nbsp &nbsp  &nbsp &nbsp<a href="/yr21/payroll/prpayrolldata.php?w=13th&action_token='.$_SESSION['action_token'].'"  OnClick="return confirm(\'Encode 13th month?\');">Encode 13th month</a><br><br>';
            $conddept=' ';
            //$conddept=' WHERE p.deptheadpositionid='.$_SESSION['&pos'].' AND c.IDNo<>'.$_SESSION['(ak0)'];
           // echo $conddept;
            } else { $conddept=' WHERE p.deptheadpositionid='.$_SESSION['&pos'].' AND c.IDNo<>'.$_SESSION['(ak0)'];}
            
            
            $sql0='CREATE TEMPORARY TABLE payroll_27bonussummary AS SELECT c.IDNo,p.FullName,p.Position, p.BranchorDept, pb.Evaluation,
 TRUNCATE(c.13thBasicCalc + c.13thTaxShCalc,0) AS Due13thisDec,TRUNCATE(ifnull((SELECT sum(ad.AdjustAmt) FROM payroll_21plannedbonuses ad WHERE ad.AdjustTypeNo = 23 AND ad.IDNo = c.IDNo GROUP BY ad.IDNo),0),0) AS PerformanceBonus,
 TRUNCATE(ifnull((SELECT sum(ad.AdjustAmt) FROM payroll_21plannedbonuses ad WHERE ad.AdjustTypeNo = 24 AND ad.IDNo = c.IDNo GROUP BY ad.IDNo),0),0) AS SpecBonus,
 pb.Remarks AS Remarks
 FROM attend_30currentpositions p JOIN payroll_26yrtotaland13thmonthcalc c ON p.IDNo = c.IDNo  LEFT JOIN payroll_21plannedbonuses pb ON c.IDNo = pb.IDNo
'.$conddept.' GROUP BY c.IDNo;
';
//             if($_SESSION['(ak0)']==1002){ echo $sql0;} 
//             exit();
            $stmt0=$link->prepare($sql0); $stmt0->execute();
            
            
	    $filter=(!isset($_GET['filter'])?'All':$_GET['filter']);
	    switch ($filter){

	    case 'SendtoPayroll':
	       $sqladj='INSERT INTO `payroll_21paydayadjustments` ( PayrollID, IDNo, AdjustTypeNo, AdjustAmt, EncodedByNo)
	    SELECT 23, s.IDNo, s.AdjustTypeNo, s.AdjustAmt, \''.$_SESSION['(ak0)'].'\' AS EncodedByNo
	    FROM `payroll_21plannedbonuses` as s JOIN `1employees` e ON e.IDNo=s.IDNo WHERE DirectORAgency=0 AND AdjustAmt<>0';
	    $stmtadj=$link->prepare($sqladj);
	    $stmtadj->execute();
	    header ('Location:/yr21/index.php?done=1');
	       break;
	    default: //All
	      // $condition='';
	      // $orderby=' order by (PerfBonus) desc';
	    break;
     }

            
     
	    $columnnames=array('IDNo','FullName','Position','BranchorDept','Due13thisDec','NetScore%','PerfBonus','SpecBonus','Remarks');
            
            
            $sql='SELECT s.IDNo, s.IDNo AS TxnID, s.FullName, s.Position, s.BranchorDept, Evaluation AS `NetScore%`, FORMAT(Due13thisDec,2) AS Due13thisDec,
                FORMAT(PerformanceBonus,2) AS PerfBonus, FORMAT(SpecBonus,2) AS SpecBonus, s.Remarks 
                FROM 
                payroll_27bonussummary s 
                ';
          //  if($_SESSION['(ak0)']==1002){ echo $sql;}
            if(allowedToOpen(8111,'1rtc')){
                $editprocess='bonuses.php?w=EditBonusSpecifics&TxnID=';
                $editprocesslabel='Edit';
            }     
            
            $sqltotal='SELECT FORMAT(SUM(PerformanceBonus),0) AS PerfBonus, FORMAT(SUM(SpecBonus),0) AS SpecBonus, '
                    . 'FORMAT(SUM(IFNULL(`Due13thisDec`,0)),0) AS `13TH` FROM payroll_27bonussummary s JOIN `attend_30currentpositions` p ON s.IDNo=p.IDNo '; 
            $stmttotal=$link->query($sqltotal);$restotal=$stmttotal->fetch();
            $totalstext='Performance Bonus:  '.$restotal['PerfBonus'].'<br>Special Bonus:  '.$restotal['SpecBonus'].'<br>13th Month:  '.$restotal['13TH'];
	    ?><input type="hidden" name="which" value="<?php echo $filter; ?>" /> 
<?php	    include('../backendphp/layout/displayastable.php');
break;
case 'YearTotals':
   if (!allowedToOpen(6475,'1rtc')) { echo 'No permission'; exit;}
   $title=!isset($_REQUEST['MonthLookup'])?'Payroll Totals for the Year': 'Payroll Totals for '.date('F',strtotime(''.$currentyr.'-'.$_REQUEST['Month'].'-01')); 
   $formdesc='<br><font color="brown">Tax deducted is based on projected annual tax. Negative Tax Due may be due to incomplete income before the last payroll for the year.</font><br><br>';
   $formdesc=$formdesc.'</i><form method="post" action="lookupwithedit.php?w=YearTotals">Choose Month (1 - 12):  '
           . '<input type="text" name="Month" value="'. date('m').'" size=3></input>&nbsp; &nbsp;'
           . '<input type=submit name="MonthLookup" value="Lookup Per MONTH">&nbsp; &nbsp; &nbsp;<input type=submit name="YearLookup" value="Lookup Per YEAR"></form><i>';
   $showbranches=false; 
   $condition=!isset($_REQUEST['MonthLookup'])?'': 'WHERE MONTH(pd.PayrollDate)='.$_REQUEST['Month']; //echo $condition;
   
    
    $sql2='Select *,format(`Basic`,2) as `Basic`,format(`DeM`,2) as `DeM`,format(`TaxSh`,2) as `TaxSh`,format(`OT`,2) as `OT`,
    format(`AbsenceBasic`,2) as `AbsenceBasic`,format(`UndertimeBasic`,2) as `UndertimeBasic`,
    format(`AbsenceTaxSh`,2) as `AbsenceTaxSh`,format(`UndertimeTaxSh`,2) as `UndertimeTaxSh`,
    format(`SSS-EE`,2) as `SSS-EE`,format(`PhilHealth-EE`,2) as `PhilHealth-EE`,format(`PagIbig-EE`,2) as `PagIbig-EE`,truncate(`WTax`,2) as `WTax`,format(`WTax`,2) as `TaxWithheld`, 
    format((`Basic`+`OT`-`AbsenceBasic`-`UndertimeBasic`),2) as TaxSalaries,
    format((`DeM`),2) as NonTaxSalaries,
    `13th`, `LeaveConversion`,
    format((`DeM` +`13th`+`LeaveConversion`),2) as TotalNotTax,
    ROUND((`SSS-EE`+`PhilHealth-EE`+`PagIbig-EE`),2) as TotalGovtDeduct, ((`Basic`+`OT`-`AbsenceBasic`-`UndertimeBasic`)-(`SSS-EE`+`PhilHealth-EE`+`PagIbig-EE`))  as Taxable, format(if(Taxable<0,0,Taxable),2) as NetTaxable, format(truncate(TaxDue(Taxable),2),2) as TotalDue, format((TaxDue(Taxable)-WTax),2) as NetTaxDue from yrgross yg ';
    $sql1='SELECT CompanyNo,`CompanyName` FROM 1companies;';
	include('yrtotalssql.php');
     $groupby='CompanyNo'; $orderby=' order by SurName';
    $columnnames1=array('CompanyName');
    $columnnames2=array('IDNo','FullName','Basic','DeM','TaxSh','OT','AbsenceBasic','UndertimeBasic','AbsenceTaxSh','UndertimeTaxSh','SSS-EE','PhilHealth-EE','PagIbig-EE','TaxWithheld', 'TaxSalaries','NonTaxSalaries','13th','LeaveConversion','TotalNotTax','TotalGovtDeduct','Taxable', 'NetTaxable', 'TotalDue', 'NetTaxDue');
    include('../backendphp/layout/displayastablewithsub.php');
	    break;
    
case 'Resigned13th':
   if (!allowedToOpen(819,'1rtc')) { echo 'No permission'; exit;}
   $title='Lookup 13th of Resigned';
   
   $formdesc="<br><br></i>13th month = Total Net Pay divided by 12<i><br><br>
          <form action='lookupwithedit.php?w=Resigned13th' method='POST'>Calculate 13th month for ID number?<input type=text name='IDNo'>
      <input type=submit name=submit value='Submit'></form>";
   
   $sql='SELECT 13th.IDNo, FORMAT(`WholeYrBasic`,2) AS WholeYrBasic, FORMAT(`WholeYrTaxSh`,2) AS WholeYrTaxSh, FORMAT(`13thBasicCalc`,2) AS 13thBasicCalc, FORMAT(`13thTaxShCalc`,2) AS 13thTaxShCalc, FORMAT(`13thBasicActual`,2) AS 13thBasicPaid, FORMAT(`13thTaxShActual`,2) AS 13thTaxShPaid, e.Nickname,FirstName,MiddleName,SurName FROM payroll_26yrtotaland13thmonthcalc 13th LEFT join `1employees` e on e.IDNo=13th.IDNo where (e.Resigned<>0 OR e.IDNo IS NULL) and 13th.IDNo='.$_POST['IDNo'];
   $columnnames=array('IDNo','Nickname','FirstName','MiddleName','SurName','WholeYrBasic','WholeYrTaxSh','13thBasicCalc','13thTaxShCalc','13thBasicPaid','13thTaxShPaid');
   include('../backendphp/layout/displayastable.php');
   
   break;

case 'TotalTaxAndNonTax':
   if (!allowedToOpen(822,'1rtc')) { echo 'No permission'; exit;}
   $title='Monthly Salaries Per Company';
  
   $sql='SELECT CompanyName,  concat(mid(Monthname(pd.PayrollDate),1,3),mid(Year(pd.PayrollDate),3,2)) as Month, 
format(sum(ifnull(p.Basic,0)+ifnull(p.DeM,0)+ifnull(p.OT,0)-ifnull(p.AbsenceBasic,0)-ifnull(p.UndertimeBasic,0)),2) AS TotalCompensation
FROM payroll_25payroll as p join `1employees` e on e.IDNo=p.IDNo
join `1companies` c on c.CompanyNo=e.RCompanyNo
join `payroll_1paydates` pd on pd.PayrollID=p.PayrollID 
GROUP BY Month(pd.PayrollDate), c.CompanyNo order by CompanyName, PayrollDate;';
   $columnnames=array('CompanyName','Month', 'TotalCompensation');
   include('../backendphp/layout/displayastable.php');
   break;
   
case 'PayrollPerPerson':
   if (!allowedToOpen(818,'1rtc')) { echo 'No permission'; exit;}
   $lookupprocess='lookupwithedit.php?w=PayrollPerPerson';
   include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
   $idno=(isset($_POST['Employee'])?comboBoxValue($link,'`1employees` e JOIN `1companies` c ON c.CompanyNo=e.RCompanyNo','CONCAT(FirstName," ",Surname," - ",Company)',addslashes($_POST['Employee']),'IDNo'):'');
   $title='Payroll History Per Person'.(isset($_POST['Employee'])?' - '.$_POST['Employee']:'');
  
   ?>
	    <br><br><form method="POST" action="<?php echo $lookupprocess ?>" enctype="multipart/form-data">
	       Employee <input type='text' name='Employee' list='employees'>
               <input type="submit" name="lookup" value="Lookup"><br><br>
   <?php
   
   echo comboBox($link,'SELECT IDNo, CONCAT(FirstName," ",Surname," - ",Company) AS FullName FROM `1employees` e JOIN `1companies` c ON c.CompanyNo=e.RCompanyNo ORDER BY FullName;','IDNo','FullName','employees');   
   if (!isset($_POST['Employee'])){goto nodata;}
   
   $sql='SELECT b1.Branch AS RecordInBranch, b.Branch, `PayrollID`,`Basic`,`DeM`,`TaxSh`,`OT`,`Remarks`,`AbsenceBasic`,`UndertimeBasic`,`AbsenceTaxSh`,`UndertimeTaxSh`,`SSS-EE`,`PhilHealth-EE`,`PagIbig-EE`,`WTax`,`TotalAdj`,`NetPay`,`DisburseVia` FROM `payroll_25payrolldatalookup` p JOIN `1employees` `e` ON `p`.`IDNo` = `e`.`IDNo`
	    JOIN `1branches` b ON b.BranchNo=p.BranchNo
	    JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo 
	    WHERE p.IDNo='.$idno.((!allowedToOpen(8173,'1rtc'))?' AND p.IDNo>1002':'').' '
           . 'UNION ALL
        SELECT "Total for the Year" AS RecordInBranch, "" AS Branch, 9999 AS `PayrollID`,FORMAT(SUM(`Basic`),2) AS `Basic`, FORMAT(SUM(`DeM`),2) AS `DeM`, 
        FORMAT(SUM(`TaxSh`),2) AS `TaxSh`, FORMAT(SUM(`OT`),2) AS `OT`, "" AS `Remarks`,FORMAT(SUM(`AbsenceBasic`),2) AS `AbsenceBasic`, FORMAT(SUM(`UndertimeBasic`),2) AS `UndertimeBasic`, 
        FORMAT(SUM(`AbsenceTaxSh`),2) AS `AbsenceTaxSh`, FORMAT(SUM(`UndertimeTaxSh`),2) AS `UndertimeTaxSh`, FORMAT(SUM(`SSS-EE`),2) AS `SSS-EE`, FORMAT(SUM(`PhilHealth-EE`),2) AS `PhilHealth-EE`,
        FORMAT(SUM(`PagIbig-EE`),2) AS `PagIbig-EE`, FORMAT(SUM(`WTax`),2) AS `WTax`, FORMAT(SUM(`TotalAdj`),2) AS `TotalAdj`,
        FORMAT(SUM(`NetPay`),2) AS `NetPay`,"" AS `DisburseVia` FROM `payroll_25payrolldatalookup` p WHERE p.IDNo='.$idno.((!allowedToOpen(8173,'1rtc'))?' AND p.IDNo>1002':'').' GROUP BY p.IDNo ORDER BY PayrollID';
   $columnnames=array('PayrollID','RecordInBranch','Branch','Basic','DeM','TaxSh','OT','Remarks','AbsenceBasic','UndertimeBasic','AbsenceTaxSh','UndertimeTaxSh','SSS-EE','PhilHealth-EE','PagIbig-EE','WTax','TotalAdj','NetPay','DisburseVia');
	    
    include('../backendphp/layout/displayastable.php');        
      break;
case 'CashPayroll':
	 if (!allowedToOpen(812,'1rtc')) { echo 'No permission'; exit;}
            $title='Cash Payroll';            
            echo '<title>'.$title.'</title>';
            $sql1='SELECT PayrollID,b.CompanyNo,CONCAT("Payroll ID ",PayrollID," - ",Company) AS PayrollIDLabel, p.BranchNo FROM `payroll_25payrolldatalookup` p
JOIN `1branches` b ON b.BranchNo=p.RecordInBranchNo JOIN `1companies` c ON c.CompanyNo=b.CompanyNo WHERE DisburseVia=0 
GROUP BY PayrollID, b.CompanyNo ORDER BY PayrollID DESC';
            $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
            
            $columnnames=array('RecordInBranch','FullName','Branch/Dept','NetPay'); $coltototal='NetPayValue';
            $showtotals=true; $showgrandtotal=true;
            $width='30%';
            foreach ($res1 as $payco){
                $subtitle=$payco['PayrollIDLabel'];
                $sql='SELECT b1.Branch AS RecordInBranch,IF(d.deptid IN (1,2,3,10),Branch,dept) AS `Branch/Dept`, p.IDNo, concat(FirstName," ",SurName) as FullName, FORMAT(NetPay,2) as NetPay, NetPay AS NetPayValue 
                FROM `payroll_25payrolldatalookup` p JOIN `1employees` `e` ON `p`.`IDNo` = `e`.`IDNo`
	    JOIN `1branches` b1 ON b1.BranchNo=p.RecordInBranchNo 
		JOIN `attend_30latestpositionsinclresigned` lir ON p.IDNo=lir.IDNo 
		JOIN `attend_0positions` ap ON ap.PositionID=lir.PositionID 
		JOIN `1departments` d ON d.deptid=ap.deptid 
            WHERE b1.CompanyNo='.$payco['CompanyNo'].' AND p.PayrollID='.$payco['PayrollID'].' AND DisburseVia=0 ORDER BY `Branch/Dept`,RecordInBranch, FullName';
                
            include('../backendphp/layout/displayastableonlynoheaders.php');
            echo '<br><br>';
            }
            
	    break;   

case 'VerifyGovt':
	 if (!allowedToOpen(823,'1rtc')) { echo 'No permission'; exit;}
            $title='Verify Govt-Mandated Deductions on Latest Rates';  
            $show=!isset($_POST['show'])?0:$_POST['show'];
            $formdesc='<style> .priority { background: #e60000; color: white; font-weight: bold; }</style>'
                    .'<br><form action="#" method="post"><input type=submit value="'.($show==0?'Show Discrepancies':'Show All').'">
            <input type=hidden name="show" value="'.($show==0?1:0).'"></form>';
            $showdisc=($show==1?' WHERE (ROUND(`SSS-EE`-`CalculatedSSS`,2)<>0) OR (ROUND(`PhilHealth-EE`-`CalculatedPHIC`,2)<>0)':'');
	    $addlcondition=((!allowedToOpen(8173,'1rtc'))?' WHERE p.IDNo>1002 ':'');
            // FORMAT(BasicMonthly,2) AS MonthlyBasic, '  <div class=priority >&nbsp P &nbsp</div>
            //        .'FORMAT(ColaMonthly,2) AS MonthlyCola, FORMAT(DeMMonthly,2) AS MonthlyDeM,
            $sql0='CREATE TEMPORARY TABLE `comparegovt` AS
SELECT r.IDNo, FullName, Position, Branch, if(r.LatestDorM=1,"Monthly","Daily") AS DorM, BasicMonthly AS Basic, TaxShieldMonthly AS TaxShield, DeMMonthly AS DeMinimis, lr.`SSS-EE`, lr.`PhilHealth-EE`, lr.`PagIbig-EE`, 
(SELECT (SSEE+ECEE+MPFEE) FROM `payroll_0ssstable` WHERE (BasicMonthly+DeMMonthly) BETWEEN RangeMin AND RangeMax) AS CalculatedSSS,
TRUNCATE(getContriEE(BasicMonthly,"phic"),2) AS CalculatedPHIC
FROM `payroll_21dailyandmonthly` r JOIN `attend_30currentpositions` p ON r.IDNo=p.IDNo JOIN `payroll_20latestrates` lr ON r.IDNo=lr.IDNo '.$addlcondition;
    $stmt0=$link->prepare($sql0); $stmt0->execute(); 
    
    $sql='SELECT *, IF(`SSS-EE`-`CalculatedSSS`=0,"",ROUND(`SSS-EE`-`CalculatedSSS`,2)) AS `SSS_Difference`, IF(`PhilHealth-EE`-`CalculatedPHIC`=0,"",ROUND(`PhilHealth-EE`-`CalculatedPHIC`,2)) AS `Philhealth_Difference` FROM `comparegovt` '.$showdisc;
    $columnnames=array('IDNo','FullName','Position','Branch','DorM','Basic','DeMinimis','SSS-EE','CalculatedSSS','SSS_Difference','PhilHealth-EE','CalculatedPHIC','Philhealth_Difference');
            include('../backendphp/layout/displayastable.php');
            break;   
   
	default:
            break;
     }
nodata:
      $link=null; $stmt=null;
?>
