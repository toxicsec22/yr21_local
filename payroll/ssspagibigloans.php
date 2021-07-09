<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(array(8052,8053,8054),'1rtc')) { echo 'No permission'; exit; }

$showbranches=false;
include_once('../switchboard/contents.php');


include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
//DEFAULT TIMEZONE
if (!empty($_SERVER['HTTPS'])) {
	$https='s';
  } else {
	$https='';
  }
  $diraddress='../';
?>


<br>

<?php
include_once('../backendphp/layout/linkstyle.php');
		echo '<div>';
			if (allowedToOpen(8052,'1rtc')) {
				echo '<a id=\'link\' href="https://www.joinusat1rotarytrading.com/faq/calamityloan.html">FAQ on Calamity Loan</a> ';
				}
			if (allowedToOpen(8054,'1rtc')) {
				echo '<a id=\'link\' href="ssspagibigloans.php?w=SSSPagIbigLoans">SSS and Pag-Ibig Loans</a> ';
				echo '<a id=\'link\' href="ssspagibigloans.php?w=LoanReceipts">Loan Receipts</a> ';
				}
			if (allowedToOpen(8053,'1rtc')) {
				echo '<a id=\'link\' href="ssspagibigloans.php?w=LoansLog">Loans Logs</a> ';
				}
		echo '</div><br/>';
	
$which=(!isset($_GET['w'])?((allowedToOpen(8054,'1rtc'))?'SSSPagIbigLoans':'PaymentHistory'):$_GET['w']);


if (in_array($which,array('LoanReceipts','LookupLoanReceipt'))){
	$sqlshortlist='SELECT TxnSubID,CONCAT(Nickname," ",SurName) AS Borrower,pda.IDNo,FORMAT(Amount,2) AS Amount,Amount AS AmountValue from payroll_21paydayadjustments pda JOIN 1employees e ON pda.IDNo=e.IDNo JOIN payroll_31loansmain lm JOIN payroll_32loanssub ls ON lm.TxnID=ls.TxnID AND lm.LoanTypeID=pda.AdjustTypeNo AND lm.IDNo=pda.IDNo AND pda.PayrollID=ls.PayrollID ';
}
if (in_array($which,array('LookupLoanReceipt','EditSpecificsLoanReceipt'))){
	$sqlloanreceiptmain='SELECT lr.*,AdjustType AS LoanType,Company FROM payroll_31loanreceipts lr JOIN payroll_0acctid ai ON lr.LoanTypeID=ai.AdjustTypeNo JOIN 1companies c ON lr.CompanyNo=c.CompanyNo ';
}
if (in_array($which,array('LoanReceipts','SSSPagIbigLoans','EditSpecificsLoanReceipt'))){
	echo comboBox($link,'SELECT `AdjustTypeNo`,AdjustType from payroll_0acctid WHERE AdjustTypeNo IN (30,31,32,33)','AdjustTypeNo','AdjustType','loantypelist');
	echo comboBox($link,'SELECT CompanyNo,Company from 1companies WHERE CompanyNo IN (1,2,3,4,5)','CompanyNo','Company','companylist');
}
if (in_array($which,array('AddNewLoan','EditLoanReceipt','LoanReceipts'))){
	if(isset($_POST['btnCheck']) OR (in_array($which,array('AddNewLoan','EditLoanReceipt')))){
		$loantypeid=comboBoxValue($link,'payroll_0acctid','AdjustType',addslashes($_POST['LoanType']),'AdjustTypeNo');
		$companyno=comboBoxValue($link,'`1companies`','Company',addslashes($_POST['Company']),'CompanyNo');
	}
		
}
if (in_array($which,array('EditSpecificsLoanReceipt','EditLoanReceipt'))){
	$columnnameslist=array('DateofReceipt', 'LoanType', 'FromPayrollID', 'SBR', 'Company');
	$columnstoedit=array('DateofReceipt', 'LoanType', 'SBR', 'Company');
	$columnstoadd=array('DateofReceipt', 'SBR');
}

$method='POST';

if (in_array($which,array('EditSub','DeleteSub'))){
	if($which=='EditSub'){
		$act='UPDATED';
	}
	if($which=='DeleteSub'){
		$act='DELETED';
	}
	$sqltrailsub='INSERT INTO payroll_32loanslogs (ActionMade,EncodedByNo,TimeStamp) SELECT CONCAT("'.$act.' SUB<br>TxnSubId: ",TxnSubId,", TxnID: ",TxnID,", InstallmentNo: ",InstallmentNo,", Amount: ",Amount,", PayrollID: ",IFNULL(PayrollID,""),", RefNo: ",IFNULL(RefNo,""),", EncodedByNo: ",EncodedByNo,", TimeStamp: ",TimeStamp) AS ActionMade,'.$_SESSION['(ak0)'].',NOW() FROM payroll_32loanssub WHERE TxnSubID=';
}
if (in_array($which,array('EditMain','DeleteMain'))){
	if($which=='EditMain'){
		$act='UPDATED';
	}
	if($which=='DeleteMain'){
		$act='DELETED';
	}
	$sqltrailmain='INSERT INTO payroll_32loanslogs (ActionMade,EncodedByNo,TimeStamp) SELECT CONCAT("'.$act.' MAIN<br>TxnID: ",TxnID,", Borrower: ",IDNo,", LoanDate: ",LoanDate,", LoanTypeID: ",LoanTypeID,", StartDeductDate: ",IFNULL(StartDeductDate,""),", LoanAmount: ",LoanAmount,", GovtServiceCharge: ",GovtServiceCharge,", GovtLoanInterest: ",GovtLoanInterest,", Installments: ",Installments,", AmortAmount: ",AmortAmount,", Posted: ",Posted,", PostedByNo: ",IFNULL(PostedByNo,""),", PostedTS: ",IFNULL(PostedTS,""),", EncodedByNo: ",EncodedByNo,", TimeStamp: ",TimeStamp) AS ActionMade,'.$_SESSION['(ak0)'].',NOW() FROM payroll_31loansmain WHERE TxnID=';
}

switch ($which)
{
	case 'SSSPagIbigLoans':
	if (allowedToOpen(8054,'1rtc')) {
		$title='SSS and Pag-Ibig Loans';
	echo '<title>'.$title.'</title>';
		$title='Add New Loan';
		
		 echo comboBox($link,'SELECT IDNo,CONCAT(Nickname," ",SurName) AS Employee FROM 1employees WHERE Resigned=0','Employee','IDNo','employeelist');
		 
		echo '<h3>Add New Loan</h3><br><form action="ssspagibigloans.php?w=AddNewLoan" method="POST" autocomplete="off">';
		echo '<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
		echo ' IDNo: <input type="text" name="IDNo" list="employeelist">';
		echo ' Loan Date: <input type="date" name="LoanDate" value="'.date('Y-m-d').'">';
		echo ' Loan Type: <input type="text" name="LoanType" size="10" list="loantypelist">';
		// echo ' Start of Deduction: <input type="date" name="StartDeductDate" value="'.date('Y-m-d').'"><br>';
		echo ' Start of Deduction: <input type="month" name="StartDeductDate" value="'.date('Y-m').'"><br>';
		echo ' Loan Amount: <input type="text" size="10" name="LoanAmount">';
		echo ' GovtServiceCharge: <input type="text" size="10" name="GovtServiceCharge">';
		echo ' GovtLoanInterest: <input type="text" size="10" name="GovtLoanInterest">';
		echo ' Installments (months to pay): <input type="text" size="10" name="Installments">';
		echo ' Amort Amount: <input type="text" size="10" name="AmortAmount">';
		echo ' <input type="submit" value="Add New" name="AddLoan">';
		
		echo '</form>';
		$editprocess='ssspagibigloans.php?w=PaymentHistory&IDNo='; $editprocesslabel='Payment History';
		
		$columnnames=array('IDNo','Borrower','LoanDate','LoanAmount','GovtServiceCharge','GovtLoanInterest','Total','Installments','AmortAmount','StartDeductDate','TxnID1'); 
		
		// $sqlmain='SELECT lm.*,lm.IDNo AS TxnID,CONCAT(e.Nickname," ",SurName) AS Borrower,LEFT(StartDeductDate,7) AS StartDeductDate FROM payroll_31loansmain lm JOIN 1employees e ON lm.IDNo=e.IDNo';
		$sqlmain='SELECT lm.*,TxnID AS TxnID1,lm.IDNo AS TxnID,CONCAT(e.Nickname," ",SurName) AS Borrower,CONCAT(MONTHNAME(StartDeductDate)," ",YEAR(StartDeductDate)) AS StartDeductDate,FORMAT((LoanAmount+GovtLoanInterest),2) AS Total FROM payroll_31loansmain lm JOIN 1employees e ON lm.IDNo=e.IDNo';
		
		
		$sql=$sqlmain.' WHERE LoanTypeID=30 ORDER BY IDNo,Posted DESC'; //SSS-Salary
		$title='SSS-Salary'; $formdesc=''; $txnidname='TxnID';
		echo '<div>';
		echo '<div>';
		include('../backendphp/layout/displayastablenosort.php');
		$title='SSS-Calamity';
		$sql=$sqlmain.' WHERE LoanTypeID=32 ORDER BY IDNo,Posted DESC'; //SSS-Calamity
		echo '</div>';
		echo '<div>';
		include('../backendphp/layout/displayastablenosort.php');
		
		echo '</div>';
		$title='Pag-Ibig';
		$sql=$sqlmain.' WHERE LoanTypeID=31 ORDER BY IDNo,Posted DESC'; //Pag-Ibig
		echo '<div>';
		include('../backendphp/layout/displayastablenosort.php');
		echo '</div></div><br>';
		
		$title='Pag-Ibig Calamity';
		$sql=$sqlmain.' WHERE LoanTypeID=33 ORDER BY IDNo,Posted DESC'; //Pag-Ibig Calamity
		echo '<div>';
		include('../backendphp/layout/displayastablenosort.php');
		echo '</div></div><br>';
		
		
	} else {
		echo 'No permission'; exit;
	}
	break;

	case 'AddNewLoan':
	if (allowedToOpen(8054,'1rtc')){
		// print_r($_POST);
		// exit();
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$columnstoadd=array('IDNo','LoanDate','Installments');
		$loanamount=str_replace(",","",$_POST['LoanAmount']);
		$amortamount=str_replace(",","",$_POST['AmortAmount']);
		$govtservicecharge=str_replace(",","",$_POST['GovtServiceCharge']);
		$govtloaninterest=str_replace(",","",$_POST['GovtLoanInterest']);
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='INSERT INTO `payroll_31loansmain` SET StartDeductDate="'.$_POST['StartDeductDate'].'-01",GovtServiceCharge="'.$govtservicecharge.'",GovtLoanInterest="'.$govtloaninterest.'",LoanAmount="'.$loanamount.'",AmortAmount="'.$amortamount.'",LoanTypeID='.$loantypeid.','.$sql.' EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=NOW()';
		$stmt=$link->prepare($sql); $stmt->execute();
		
		$sql='SELECT TxnID FROM payroll_31loansmain WHERE IDNo='.$_POST['IDNo'].' AND LoanTypeID='.$loantypeid.' AND LoanDate="'.$_POST['LoanDate'].'"';
		 $stmt=$link->query($sql); $result=$stmt->fetch();
		
		$inserttimes=$_POST['Installments'];
		$cnt=1;
		while($cnt<=$inserttimes){
			$sqlinsert='INSERT INTO payroll_32loanssub SET TxnID='.$result['TxnID'].',Amount="'.str_replace(",","",$_POST['AmortAmount']).'",InstallmentNo='.$cnt.',EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW();';
			$stmt=$link->prepare($sqlinsert); $stmt->execute();
			$cnt++;
		}
		
		header('Location:ssspagibigloans.php?w=SSSPagIbigLoans');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'PaymentHistory':
	if (!allowedToOpen(8052,'1rtc')){ echo 'No Permission'; exit(); }
	

		echo '<title>Payment History</title>';
	
	echo '<body style="background-color:#afcecf;"><link rel="stylesheet" type="text/css" href="http'.$https.'://'.$_SERVER['HTTP_HOST'].'/acrossyrs/js/bootstrapCOLLAPSE/css/bootstrap.min.css" />
    <script type="text/javascript" src="http'.$https.'://'.$_SERVER['HTTP_HOST'].'/acrossyrs/js/bootstrapCOLLAPSE/js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="http'.$https.'://'.$_SERVER['HTTP_HOST'].'/acrossyrs/js/bootstrapCOLLAPSE/js/bootstrap.min.js"></script>





    <div class="panel-group" id="accordion">
        
       
     
    
    
  

      '; 
	if((allowedToOpen(8052,'1rtc')) AND (!allowedToOpen(8054,'1rtc'))){ //session ako
		$idno=$_SESSION['(ak0)'];
		$nopost=true;
	}else {
		$idno=intval($_REQUEST['IDNo']);
		
	}
	
	
	$countoutof='SELECT CONCAT("LoanDate: ", LoanDate, " (",COUNT(PayrollID),"/",Installments,IF(Installments<>COUNT(InstallmentNo),") <font style=\"font-size:12pt\" color=\"red\">ERROR: Installments value is not equal to the count of InstallmentNo. Pls fix.</font>",")")," -- Balance: ",FORMAT(((LoanAmount+GovtLoanInterest)-IFNULL((SELECT SUM(Amount) FROM payroll_32loanssub WHERE PayrollID IS NOT NULL AND TxnID=ls.TxnID),0)),2)) FROM payroll_31loansmain lm JOIN payroll_32loanssub ls ON lm.TxnID=ls.TxnID WHERE lm.IDNo='.$idno.' AND';
	
	// $bgcolor='SELECT IF(((LoanAmount+GovtLoanInterest)-(IFNULL((SELECT SUM(Amount) FROM payroll_32loanssub WHERE PayrollID IS NOT NULL AND TxnID=ls.TxnID),0))<=0),1,0) AS bgcolor FROM payroll_31loansmain lm JOIN payroll_32loanssub ls ON lm.TxnID=ls.TxnID WHERE lm.IDNo='.$idno.' AND';
	
	
	/* $sqlinfo='SELECT IDNo,('.$countoutof.'  LoanTypeID=30) AS OutOfSSSS,('.$countoutof.'  LoanTypeID=32) AS OutOfSSSC,('.$countoutof.'  LoanTypeID=31) AS OutOfPagIbig,('.$countoutof.'  LoanTypeID=33) AS OutOfPagIbigCalamity FROM 1employees WHERE IDNo='.$idno;
	// echo $sqlinfo;
	
	$stmt=$link->query($sqlinfo); $result=$stmt->fetch(); */
	
	
	// echo '<h2>'.$result['IDNo'].": ".$result['Borrower'].'</h2>';
	
	// $outofssss=$result['OutOfSSSS'];
	/* $outofsssc=$result['OutOfSSSC'];
	$outofpagibig=$result['OutOfPagIbig'];
	$outofpagibigcalamity=$result['OutOfPagIbigCalamity']; */
	
	 //START Used this for the 4 loan type
    $sqlid='SELECT IDNo,CONCAT(Nickname," ",SurName) AS Borrower FROM 1employees WHERE IDNo='.$idno;
	$stmtid=$link->query($sqlid); $resultid=$stmtid->fetch();
	
	echo '<h2>'.$resultid['IDNo'].": ".$resultid['Borrower'].'</h2>';
	
    $sqlmain1='SELECT lm.*, concat(e.`Nickname`," ",e.`SurName`) as EncodedBy,concat(e2.`Nickname`," ",e2.`SurName`) as PostedBy, FORMAT(lm.`LoanAmount`,2) AS LoanAmount,LEFT(StartDeductDate,7) AS StartDeductDate, FORMAT(lm.`AmortAmount`,2) AS AmortAmount,FORMAT((LoanAmount+GovtLoanInterest),2) AS Total FROM payroll_31loansmain lm
LEFT JOIN `1employees` e ON e.IDNo=lm.EncodedByNo
LEFT JOIN `1employees` e2 ON e2.IDNo=lm.PostedByNo
WHERE lm.IDNo='.$idno;


$sqlsub1='SELECT ls.*,YearD AS `Year`,ls.TxnSubID,(Amount*-1) AS DiffAmount,PayrollID,SBR AS RefNo,CONCAT(e.Nickname," ",SurName) AS EncodedBy FROM payroll_31loansmain lm JOIN payroll_32loanssub ls ON lm.TxnID=ls.TxnID LEFT JOIN 1employees e ON ls.EncodedByNo=e.IDNo LEFT JOIN payroll_31loanreceipts lr ON ls.PayrollID=lr.FromPayrollID AND ls.YearD=YEAR(lr.DateofReceipt) WHERE lm.IDNo=';


$sqlunion='SELECT 0 AS TxnSubID,0 AS TxnID,"Beg Bal" AS InstallmentNo,"--" AS Amount,"" AS PayrollID,"--" AS YearD,"--" AS RefNo,"--" AS EncodedByNo,"--" AS TimeStamp,"--" AS `Year`,"--" AS TxnSubID,(LoanAmount+GovtLoanInterest) AS DiffAmount,"--" AS PayrollID,"--" AS RefNo,"--" AS EncodedBy FROM payroll_31loansmain lm WHERE lm.IDNo=';

$columnnamesmain=array('LoanDate', 'LoanAmount','GovtServiceCharge','GovtLoanInterest','Total', 'Installments', 'AmortAmount','StartDeductDate');
if(isset($_POST['btnshowallencpos'])){
	array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedBy','PostedTS');
}
    $columnsub=array('InstallmentNo','Year', 'PayrollID','RefNo', 'Amount');
if(isset($_POST['btnshowallencpos'])){
	array_push($columnsub,'EncodedBy','TimeStamp');
}

$colwithlistmain=array();		      
	$liststoshow=array();	
	
	
$inputsubarray=array(
                    array('field'=>'InstallmentNo', 'type'=>'text','size'=>15,'required'=>true,'autofocus'=>true),
                    array('field'=>'Amount', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0)
                    );
					
$action='ssspagibigloans.php?w=AddSub&IDNo='.$idno;
$divnewline='<div style="clear: both; display: block; position: relative;height:30px;"></div>';


		//used this in edit ok, always add else unset this value
			$editprocessmain1='ssspagibigloans.php?w=EditMain&IDNo='.$idno.'&TxnID='; 
			$editprocesslabelmain1='Enter'; 	
			$delprocessmain1='ssspagibigloans.php?w=DeleteMain&TxnID='; 
			$columnstoeditmain1=array('LoanDate','LoanAmount','GovtServiceCharge','GovtLoanInterest', 'Installments', 'AmortAmount','StartDeductDate');	      
			$colwithmonthmain1=array('StartDeductDate');	      
			$columnstoedit1=array('Year','PayrollID','Amount');
			$editprocess1='ssspagibigloans.php?IDNo='.$idno.'&w=EditSub&TxnSubID=';
			$editprocesslabel1='Enter';
			$delprocess1='ssspagibigloans.php?IDNo='.$idno.'&w=DeleteSub&TxnSubID=';
		//end used
			
			
 $txnidcol='TxnID'; 
 $txnsubid='TxnSubID'; 
 $withsub=true; $coltototal='DiffAmount'; $runtotal=true;
 // $withsub=true; $coltototal='Amount'; $runtotal=true;
$postvalue='1'; $table='payroll_31loansmain';   $txntype='LoanType';

 // info for posting:
//END Used


$h3title='Latest amortization of ';

//Salary
	$sqlltype=' AND lm.LoanTypeID=30';
	$sqlmain=$sqlmain1.$sqlltype.'';
    $stmt=$link->query($sqlmain); 
	$result1=$stmt->fetchAll();
	// $result=$stmt->fetch();



if($stmt->rowCount()>0){
	foreach($result1 AS $result) {
		$txnid=$result['TxnID'];
		
		$sqlinfo='SELECT IDNo,('.$countoutof.'  LoanTypeID=30 AND lm.TxnID='.$txnid.') AS OutOfSSSS FROM 1employees WHERE IDNo='.$idno;
		// echo $sqlinfo;
		$sqlmain=$sqlmain1.$sqlltype.' AND lm.TxnID='.$txnid;
	
	
	$stmtinfo=$link->query($sqlinfo); $resultinfo=$stmtinfo->fetch();
	$outofssss=$resultinfo['OutOfSSSS'];
	
	// $dbgcolor=$resultinfo['bgcolor'];
	
	
	echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <b><a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$txnid.'" >'.$h3title.'SSS-Salary: '.$outofssss.'</a></b>
                </h4>
            </div>';
			
			
	
	$title='<h3>'.$h3title.'SSS-Salary: '.$outofssss.'</h3>';
    $sqlsub=$sqlunion.$idno.$sqlltype.' GROUP BY IDNo UNION '.$sqlsub1.$idno.$sqlltype.' AND lm.TxnID='.$txnid;
	// echo $sqlsub;
    $main=''; $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%5==0?'</tr><tr>':'');
    }
    
    if(($result['Posted']==0) AND $idno<>$_SESSION['(ak0)']){
        $editok=true; $editsub=true;
		$editprocessmain=$editprocessmain1; 
		$editprocesslabelmain=$editprocesslabelmain1; 	
			$delprocessmain=$delprocessmain1; 
			$columnstoeditmain=$columnstoeditmain1;
			$colwithmonthmain=$colwithmonthmain1;	      	      
			$columnstoedit=$columnstoedit1;
			$editprocess=$editprocess1;
			$editprocesslabel=$editprocesslabel1;
			$delprocess=$delprocess1;
        } else {
            $editok=false; $editsub=false; $columnstoedit=array(); $editprocessmain=''; $editprocess =''; $columnstoeditmain=array(); $liststoshow=array();
            }

    $main='<table><tr>'.$main.'<tr></table>';
    $main=$main.'<br>'.(isset($_GET['msg'])?'<br><b><font color="maroon">'.strtoupper($_GET['msg']).'</font></b><br>':'');
    
    //to add records in sub
       $columnnames=$inputsubarray;
		array_push($columnnames,array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid));	
    // end add records in sub
	
   $width='150%';
   echo '
            <div id="collapse'.$txnid.'" class="panel-collapse collapse">
                <div class="panel-body">';
                     include('../backendphp/layout/inputsubeditmain.php');
                echo '</div>
            </div>
        </div>';
}
  
}
//End Salary

//Calamity
$sqlltype=' AND lm.LoanTypeID=32';
	/* $sqlmain=$sqlmain1.$sqlltype;

    $stmt=$link->query($sqlmain); $result=$stmt->fetch(); */
	$sqlmain=$sqlmain1.$sqlltype.'';
    $stmt=$link->query($sqlmain); 
	$result1=$stmt->fetchAll();

// $txnid=$result['TxnID'];

if($stmt->rowCount()>0){
	
	echo $divnewline;
	foreach($result1 AS $result) {
		$txnid=$result['TxnID'];
		
	$sqlinfo='SELECT IDNo,('.$countoutof.'  LoanTypeID=32 AND lm.TxnID='.$txnid.') AS OutOfSSSC FROM 1employees WHERE IDNo='.$idno;
		
		$sqlmain=$sqlmain1.$sqlltype.' AND lm.TxnID='.$txnid;
	
	
	$stmtinfo=$link->query($sqlinfo); $resultinfo=$stmtinfo->fetch();
	$outofsssc=$resultinfo['OutOfSSSC'];
	
	echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                   <b> <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$txnid.'" >'.$h3title.'SSS-Calamity: '.$outofsssc.'</a></b>
                </h4>
            </div>';
	$title='<h3>'.$h3title.'SSS-Calamity: '.$outofsssc.'</h3>';
     // $sqlsub=$sqlsub1.$idno.$sqlltype;
	 // $sqlsub=$sqlunion.$idno.$sqlltype.' UNION '.$sqlsub1.$idno.$sqlltype;
	 $sqlsub=$sqlunion.$idno.$sqlltype.' GROUP BY IDNo UNION '.$sqlsub1.$idno.$sqlltype.' AND lm.TxnID='.$txnid;
	 // echo $sqlsub;
    $main=''; $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%5==0?'</tr><tr>':'');
    }
    
    if(($result['Posted']==0) AND $idno<>$_SESSION['(ak0)']){
        $editok=true; $editsub=true;
		$editprocessmain=$editprocessmain1; 
		$editprocesslabelmain=$editprocesslabelmain1; 	
			$delprocessmain=$delprocessmain1; 
			$columnstoeditmain=$columnstoeditmain1;	      
			$colwithmonthmain=$colwithmonthmain1;	      
			$columnstoedit=$columnstoedit1;
			$editprocess=$editprocess1;
			$editprocesslabel=$editprocesslabel1;
			$delprocess=$delprocess1;
        } else {
            $editok=false; $editsub=false; $columnstoedit=array(); $editprocessmain=''; $editprocess =''; $columnstoeditmain=array(); $liststoshow=array();
            }

    $main='<table><tr>'.$main.'<tr></table>';
    $main=$main.'<br>'.(isset($_GET['msg'])?'<br><b><font color="maroon">'.strtoupper($_GET['msg']).'</font></b><br>':'');

    //to add records in sub
   $columnnames=$inputsubarray;
		array_push($columnnames,array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid));	
    // end add records in sub
	if(isset($total)){ unset($total); }
	$width='150%';
	echo '<div id="collapse'.$txnid.'" class="panel-collapse collapse">
                <div class="panel-body">';
				include('../backendphp/layout/inputsubeditmain.php');
				echo '
                </div>
            </div>
        </div>';
    
	
}
}
//End Calamity
// exit();

//Pag-Ibig
$sqlltype=' AND lm.LoanTypeID=31';
	$sqlmain=$sqlmain1.$sqlltype.'';
    $stmt=$link->query($sqlmain); 
	$result1=$stmt->fetchAll();

// $txnid=$result['TxnID'];

if($stmt->rowCount()>0){
	echo $divnewline;
	foreach($result1 AS $result) {
		$txnid=$result['TxnID'];
		
		$sqlinfo='SELECT IDNo,('.$countoutof.'  LoanTypeID=31 AND lm.TxnID='.$txnid.') AS OutOfPagIbig FROM 1employees WHERE IDNo='.$idno;
		// echo $sqlinfo;
		$sqlmain=$sqlmain1.$sqlltype.' AND lm.TxnID='.$txnid;
	
	
	$stmtinfo=$link->query($sqlinfo); $resultinfo=$stmtinfo->fetch();
	$outofpagibig=$resultinfo['OutOfPagIbig'];
	
	echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                   <b> <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$txnid.'">'.$h3title.'Pag-Ibig: '.$outofpagibig.'</a></b>
                </h4>
            </div>';
	$title='<h3>'.$h3title.'Pag-Ibig: '.$outofpagibig.'</h3>';
	// $sqlsub=$sqlsub1.$idno.$sqlltype;
    // $sqlsub=$sqlunion.$idno.$sqlltype.' UNION '.$sqlsub1.$idno.$sqlltype;
	$sqlsub=$sqlunion.$idno.$sqlltype.' GROUP BY IDNo UNION '.$sqlsub1.$idno.$sqlltype.' AND lm.TxnID='.$txnid;
	
    $main=''; $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%5==0?'</tr><tr>':'');
    }
    if(($result['Posted']==0) AND $idno<>$_SESSION['(ak0)']){
        $editok=true; $editsub=true;
		$editprocessmain=$editprocessmain1; 
		$editprocesslabelmain=$editprocesslabelmain1; 	
			$delprocessmain=$delprocessmain1; 
			$columnstoeditmain=$columnstoeditmain1;
			$colwithmonthmain=$colwithmonthmain1;	      	      
			$columnstoedit=$columnstoedit1;
			$editprocess=$editprocess1;
			$editprocesslabel=$editprocesslabel1;
			$delprocess=$delprocess1;
        } else {
            $editok=false; $editsub=false; $columnstoedit=array(); $editprocessmain=''; $editprocess =''; $columnstoeditmain=array(); $liststoshow=array();
            }

    $main='<table><tr>'.$main.'<tr></table>';
    $main=$main.'<br>'.(isset($_GET['msg'])?'<br><b><font color="maroon">'.strtoupper($_GET['msg']).'</font></b><br>':'');
  
    //to add records in sub
    $columnnames=$inputsubarray;
		array_push($columnnames,array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid));	
        
	if(isset($total)){ unset($total); }
	
	$width='150%';
   
	echo '<div id="collapse'.$txnid.'" class="panel-collapse collapse">
                <div class="panel-body">';
				 include('../backendphp/layout/inputsubeditmain.php');
				echo '
                </div>
            </div>
        </div>';

}
}

// exit();
//End Pag-ibig
	
//Pag-Ibig Calamity
$sqlltype=' AND lm.LoanTypeID=33';
	$sqlmain=$sqlmain1.$sqlltype.'';
    $stmt=$link->query($sqlmain); 
	$result1=$stmt->fetchAll();

$txnid=$result['TxnID'];

if($stmt->rowCount()>0){
	echo $divnewline;
	foreach($result1 AS $result) {
		$txnid=$result['TxnID'];
		
		$sqlinfo='SELECT IDNo,('.$countoutof.'  LoanTypeID=33 AND lm.TxnID='.$txnid.') AS OutOfPagIbigCalamity FROM 1employees WHERE IDNo='.$idno;
		
		$sqlmain=$sqlmain1.$sqlltype.' AND lm.TxnID='.$txnid;
	
	
	$stmtinfo=$link->query($sqlinfo); $resultinfo=$stmtinfo->fetch();
	
	$outofpagibigcalamity=$resultinfo['OutOfPagIbigCalamity'];
	
	echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    <b><a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$txnid.'">'.$h3title.'Pag-Ibig Calamity: '.$outofpagibigcalamity.'</a></b>
                </h4>
            </div>';
	$title='<h3>'.$h3title.'Pag-Ibig Calamity: '.$outofpagibigcalamity.'</h3>';
	// $sqlsub=$sqlsub1.$idno.$sqlltype;
    // $sqlsub=$sqlunion.$idno.$sqlltype.' UNION '.$sqlsub1.$idno.$sqlltype;
	$sqlsub=$sqlunion.$idno.$sqlltype.' GROUP BY IDNo UNION '.$sqlsub1.$idno.$sqlltype.' AND lm.TxnID='.$txnid;
	
    $main=''; $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%5==0?'</tr><tr>':'');
    }
    if(($result['Posted']==0) AND $idno<>$_SESSION['(ak0)']){
        $editok=true; $editsub=true;
		$editprocessmain=$editprocessmain1; 
		$editprocesslabelmain=$editprocesslabelmain1; 	
			$delprocessmain=$delprocessmain1; 
			$columnstoeditmain=$columnstoeditmain1;
			$colwithmonthmain=$colwithmonthmain1;	      	      
			$columnstoedit=$columnstoedit1;
			$editprocess=$editprocess1;
			$editprocesslabel=$editprocesslabel1;
			$delprocess=$delprocess1;
        } else {
            $editok=false; $editsub=false; $columnstoedit=array(); $editprocessmain=''; $editprocess =''; $columnstoeditmain=array(); $liststoshow=array();
            }

    $main='<table><tr>'.$main.'<tr></table>';
    $main=$main.'<br>'.(isset($_GET['msg'])?'<br><b><font color="maroon">'.strtoupper($_GET['msg']).'</font></b><br>':'');
  
    //to add records in sub
    $columnnames=$inputsubarray;
		array_push($columnnames,array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid));	
        
	if(isset($total)){ unset($total); }
	
	$width='150%';
	echo '<div id="collapse'.$txnid.'" class="panel-collapse collapse">
                <div class="panel-body">';
				include('../backendphp/layout/inputsubeditmain.php');
                echo '</div>
            </div>
        </div>';
    

}
}
//End Pag-ibig
	
	echo '
		</br>
            
		</br>
        
		</br>
		 
            
		 
		 
		 </div>';
    break;
	
	
	case 'EditMain':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
    $txnid=intval($_REQUEST['TxnID']);
    
	$sqltrail=$sqltrailmain.$txnid;
	
	$stmt=$link->prepare($sqltrail); $stmt->execute();
	
	$columnstoedit=array('LoanDate','Installments');
	
    $sql='';
	$nocommafield="LoanAmount='".str_replace(",","",$_POST['LoanAmount'])."', AmortAmount='".str_replace(",","",$_POST['AmortAmount'])."',GovtServiceCharge='".str_replace(",","",$_POST['GovtServiceCharge'])."',GovtLoanInterest='".str_replace(",","",$_POST['GovtLoanInterest'])."',";
	foreach ($columnstoedit as $field) {$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; }
	$sql='UPDATE `payroll_31loansmain` SET '.$nocommafield.' '.$sql.' StartDeductDate="'.$_POST['StartDeductDate'].'-01",EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() WHERE TxnID='.$txnid . ''; 	
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header('Location:ssspagibigloans.php?w=PaymentHistory&IDNo='.$_GET['IDNo'].'');
    break;
	
	
	case 'DeleteMain':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
    $txnid=intval($_REQUEST['TxnID']);
	$sqltrail=$sqltrailmain.$txnid;
	$stmt=$link->prepare($sqltrail); $stmt->execute();
	
	$sql='Delete from `payroll_31loansmain` where Posted=0 AND TxnID='.$txnid;
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:ssspagibigloans.php');
    break;
	
	case 'EditSub':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
    $IDNo=$_REQUEST['IDNo']; $txnsubid=$_REQUEST['TxnSubID'];
	
	$sqltrail=$sqltrailsub.$txnsubid.';';
	$stmt=$link->prepare($sqltrail); $stmt->execute();
	
    $sql='';
	// $sql='UPDATE `payroll_32loanssub` SET Amount="'.str_replace(",","",$_POST['Amount']).'",RefNo="'.addslashes($_POST['RefNo']).'",TimeStamp=Now(),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() where TxnSubID='.$txnsubid . '';
	// $sql='UPDATE `payroll_32loanssub` SET YearD="'.addslashes($_POST['Year']).'",PayrollID="'.$_POST['PayrollID'].'",Amount="'.str_replace(",","",$_POST['Amount']).'",TimeStamp=Now(),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() where TxnSubID='.$txnsubid . '';
	$addlsql='';
	if($_POST['Year']<>''){
		$addlsql.='YearD="'.addslashes($_POST['Year']).'",';
	}
	if($_POST['PayrollID']<>'') {
		$addlsql.='PayrollID="'.$_POST['PayrollID'].'",';
	}
	
	$sql='UPDATE `payroll_32loanssub` SET '.$addlsql.' Amount="'.str_replace(",","",$_POST['Amount']).'",TimeStamp=Now(),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() where TxnSubID='.$txnsubid . '';
	// echo $sql; exit();
	// echo print_r($_POST); exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:ssspagibigloans.php?w=PaymentHistory&IDNo='.$IDNo.'');
    break;
	
	
	case 'AddSub':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
    $txnid=$_REQUEST['TxnID']; 
        $sql1='INSERT INTO `payroll_32loanssub` (`TxnID`,`InstallmentNo`,`Amount`, `EncodedByNo`,`TimeStamp`) SELECT '.$txnid.', '.$_POST['InstallmentNo'].','.str_replace("'","",$_POST['Amount']).','.$_SESSION['(ak0)'].',NOW();';

    $stmt=$link->prepare($sql1); $stmt->execute();
    header('Location:ssspagibigloans.php?w=PaymentHistory&IDNo='.$_GET['IDNo'].'');
    break;
	
	case 'DeleteSub':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
    $IDNo=$_REQUEST['IDNo']; $txnsubid=$_REQUEST['TxnSubID'];
	$sqltrail=$sqltrailsub.$txnsubid.';';
	$stmt=$link->prepare($sqltrail); $stmt->execute();
	
	$sql='Delete from `payroll_32loanssub` where TxnSubID='.$txnsubid;
	$stmt=$link->prepare($sql); $stmt->execute();
	  header('Location:ssspagibigloans.php?w=PaymentHistory&IDNo='.$_GET['IDNo'].'');
    break;
	
	
	case 'LoansLog':
	if (!allowedToOpen(8053,'1rtc')){ echo 'No Permission'; exit(); }
	$sql='SELECT ll.*,CONCAT(Nickname," ",SurName) AS EncodedBy FROM payroll_32loanslogs ll LEFT JOIN 1employees e ON ll.EncodedByNo=e.IDNo ORDER BY TimeStamp DESC';
	$columnnames=array('ActionMade','EncodedBy','TimeStamp');
	$title='Loans Logs';
	include('../backendphp/layout/displayastable.php');
	break;
	
	
	case 'LoanReceipts':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
	$title='Loan Receipts';
	echo '<title>'.$title.'</title>';
	
	echo comboBox($link,'SELECT PayrollID,CONCAT(FromDate," - ",ToDate) AS Dates FROM payroll_1paydates','Dates','PayrollID','payrollidlist');
	
	$payrollidtoday=(isset($_POST['FromPayrollID'])?$_POST['FromPayrollID']:((date('m')*2)+(date('d')<15?-1:0)));
	echo '<form action="ssspagibigloans.php?w=LoanReceipts" method="POST">';
	echo 'Date of Receipt: <input type="date" name="DateOfReceipt" value="'.date("Y-m-d").'"> ';
	echo 'Loan Type: <input type="text" name="LoanType" value="" size="15" list="loantypelist"> ';
	echo 'From PayrollID: <input type="text" name="FromPayrollID" value="'.$payrollidtoday.'" size="10" list="payrollidlist"> ';
	echo 'SBR/Acknowledgement: <input type="text" name="SBR" value="" size="20" required> ';
	echo 'Company: <input type="text" name="Company" value="" list="companylist" size="10"> ';
	echo '<input type="submit" value="Add" name="btnCheck">';
	echo '</form>';
	
	if(isset($_POST['btnCheck'])){
		echo '<br><h3>Verify</h3>';
		
		echo '<br><h4>Date of Receipt: '.$_POST['DateOfReceipt'].'</h4>';
		echo '<h4>Loan Type: '.$_POST['LoanType'].'</h4>';
		echo '<h4>From Payroll ID: '.$_POST['FromPayrollID'].'</h4>';
		echo '<h4>SBR/Acknowledgement: '.$_POST['SBR'].'</h4>';
		echo '<h4>Company: '.$_POST['Company'].'</h4>';
		$year=substr($_POST['DateOfReceipt'],0,4);
		$payrollid=$_POST['FromPayrollID'];
		
		
		
		$sql=$sqlshortlist.' WHERE e.RCompanyNo='.$companyno.' AND pda.AdjustTypeNo='.$loantypeid.' AND ls.YearD='.$year.' AND pda.PayrollID='.$payrollid.'';
		// echo $sql;
		
		$stmt=$link->query($sql); $res=$stmt->fetchAll();
		
		
		echo '<form action="ssspagibigloans.php?w=UpdateReceipt" method="post">';
		echo '<input type="hidden" value="'.$_POST['DateOfReceipt'].'" name="PrDateOfReceipt">';
		echo '<input type="hidden" value="'.$loantypeid.'" name="PrLoanType">';
		echo '<input type="hidden" value="'.$payrollid.'" name="PrPayrollID">';
		echo '<input type="hidden" value="'.$_POST['SBR'].'" name="PrSBR">';
		echo '<input type="hidden" value="'.$companyno.'" name="PrCompanyNo">';
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		$message='Add Data?';
		echo '<table style="padding:2px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
		echo '<thead style="font-weight:bold;"><tr><td colspan="4" align="right"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input style="background-color:yellow;width:120px" type="submit" value="Add data" OnClick="return confirm(\''.$message.'\');"/></td></tr><tr><td>All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></td><td>IDNo</td><td>Borrower</td><td>Amount</td></tr></thead><tbody style=\"overflow:auto;\">';
		$totamount=0;
		foreach($res AS $row){
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td align="right"><input type="checkbox" value="'.$row['TxnSubID'].'" name="txnsubid[]" checked/></td><td>'.$row['IDNo'].'</td><td>'.$row['Borrower'].'</td><td>'.$row['Amount'].'</td></tr>';
			$colorcount++;
			$totamount=$totamount+$row['AmountValue'];
		}
		echo '</tbody></table><br>';
		echo 'Total: '.number_format($totamount,2);
		echo '</form>';
	} else {
		$sql='SELECT lr.*,IF(Posted=1,"Yes","") AS `Posted?`,LRID AS TxnID,CONCAT(Nickname," ",SurName) AS EncodedBy,Company,AdjustType AS LoanType FROM payroll_31loanreceipts lr JOIN payroll_0acctid ai ON lr.LoanTypeID=ai.AdjustTypeNo JOIN 1companies c ON lr.CompanyNo=c.CompanyNo LEFT JOIN 1employees e ON lr.EncodedByNo=e.IDNo ORDER BY DateofReceipt DESC, Company';
		$columnnames=array('DateofReceipt','LoanType','FromPayrollID','SBR','Company','Posted?');
		$editprocess='ssspagibigloans.php?w=EditSpecificsLoanReceipt&LRID='; $editprocesslabel='Edit';
		$addlprocess='ssspagibigloans.php?w=LookupLoanReceipt&LRID='; $addlprocesslabel='Lookup';
		 $delprocess='ssspagibigloans.php?w=DeleteLoanReceipt&LRID=';
		$width='70%';
		include('../backendphp/layout/displayastable.php');
		
	}
	
	break;
	
	case 'UpdateReceipt':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		if (isset($_REQUEST['txnsubid'])){
			$txnsubids='';
			foreach ($_REQUEST['txnsubid'] AS $txnsubid){
				$txnsubids.=$txnsubid.',';
			}
			$txnsubids=substr($txnsubids, 0, -1);
			$sql='INSERT INTO payroll_31loanreceipts SET DateofReceipt="'.$_POST['PrDateOfReceipt'].'",LoanTypeID='.$_POST['PrLoanType'].',FromPayrollID='.$_POST['PrPayrollID'].',SBR="'.$_POST['PrSBR'].'",CompanyNo='.$_POST['PrCompanyNo'].',txnsubids="'.$txnsubids.'",EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW();';
			
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			
			$sql='SELECT LRID FROM payroll_31loanreceipts WHERE DateofReceipt="'.$_POST['PrDateOfReceipt'].'" AND LoanTypeID='.$_POST['PrLoanType'].' AND FromPayrollID='.$_POST['PrPayrollID'].'';
			$stmt=$link->query($sql); $result=$stmt->fetch();
			$lrid=$result['LRID'];
			header("Location:ssspagibigloans.php?w=LookupLoanReceipt&LRID=".$lrid."");
		}
		else
		{
			echo 'Please select at least 1.';
		}
	break;
	
	case 'LookupLoanReceipt':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
	$lrid=intval($_GET['LRID']);
	$sql1=$sqlloanreceiptmain.' WHERE LRID='.$lrid.'';
	$stmt1=$link->query($sql1); $row=$stmt1->fetch();
	
	echo '<title>Loan Receipt: (Payroll: '.substr($row['DateofReceipt'],0,4).' - '.$row['FromPayrollID'].' - '.$row['LoanType'].')</title>';
	echo '<div style="float:left;">';
	
	echo '<br><h4>Date of Receipt: '.$row['DateofReceipt'].'</h4>';
		echo '<h4>Loan Type: '.$row['LoanType'].'</h4>';
		echo '<h4>From Payroll ID: '.$row['FromPayrollID'].'</h4>';
		echo '<h4>SBR/Acknowledgement: '.$row['SBR'].'</h4>';
		echo '<h4>Company: '.$row['Company'].'</h4><br>';
		
	$sql1='SELECT TxnSubID,lm.IDNo,FORMAT(Amount,2) AS Amount,Amount AS AmountValue,CONCAT(Nickname," ",SurName) AS Borrower FROM payroll_31loansmain lm JOIN payroll_32loanssub ls ON lm.TxnID=ls.TxnID LEFT JOIN 1employees e ON lm.IDNo=e.IDNo WHERE TxnSubID IN ('.$row['txnsubids'].')';
	$stmt1=$link->query($sql1); $row1=$stmt1->fetchAll();
	$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
	
	$loantable='<table style="padding:2px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
	$loantable.='<tr><th>IDNo</th><th>Borrower</th><th>Amount</th><th></th></tr>';
	$totamount=0;
	
	$openedit=($row['Posted']==0?1:0);
	
	
	$sqlsl=$sqlshortlist.' WHERE e.RCompanyNo='.$row['CompanyNo'].' AND pda.AdjustTypeNo='.$row['LoanTypeID'].' AND ls.YearD='.substr($row['DateofReceipt'],0,4).' AND pda.PayrollID='.$row['FromPayrollID'].' AND TxnSubID NOT IN ('.$row['txnsubids'].')';
	$stmtsl=$link->query($sqlsl); $rowsl=$stmtsl->fetchAll();
	
	if($stmtsl->rowCount()>0){
		echo '<form action="ssspagibigloans.php?w=AddLoanReceipt&LRID='.$row['LRID'].'" method="POST">';
		echo 'Borrower: <select name="TxnSubID">';
		foreach($rowsl AS $rowslist){
			echo '<option value="'.$rowslist['TxnSubID'].'">'.$rowslist['Borrower'].'</option>';
		}
		echo '</select>';
		echo ' <input type="submit" value="Add" name="btnAddLoanReceipt">';
		echo '</form>';
	}
	echo '<form action="ssspagibigloans.php?w=PostUnpostReceipt&LRID='.$lrid.'" method="POST"><input type="submit" value="'.($row['Posted']==0?'Post':'Unpost').'" name="btnPostUnpost"></form>';
	foreach($row1 as $row2){
		$loantable.='<tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$row2['IDNo'].'</td><td>'.$row2['Borrower'].'</td><td>'.$row2['Amount'].'</td><td>'.($openedit==1?'<form action="ssspagibigloans.php?w=DeleteLoanReceiptList&LRID='.$lrid.'&TxnSubID='.$row2['TxnSubID'].'" method="POST"><input type="submit" value="Delete" name="btnDelete" OnClick="return confirm(\'Are you sure to DELETE this?\');"></form>':'').'</td></tr>';
		$totamount=$totamount+$row2['AmountValue'];
	}  
	echo $loantable.'</table><br>';
	echo 'Total Amount: '.number_format($totamount,2);
	echo '</div>';
	break;
	
	case 'PostUnpostReceipt':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
	$lrid=intval($_GET['LRID']);
	$sql1='UPDATE `payroll_31loanreceipts` SET Posted=IF(Posted=0,1,0),PostedByNo='.$_SESSION['(ak0)'].',PostedTS=NOW() WHERE LRID='.$lrid.';';
	$stmt=$link->prepare($sql1); $stmt->execute();
	header("Location:ssspagibigloans.php?w=LookupLoanReceipt&LRID=".$lrid."");
	break;
	
	
	case 'DeleteLoanReceiptList':
	$txnsubid=intval($_GET['TxnSubID']);
	$lrid=intval($_GET['LRID']);
	
	$sql='SELECT LRID, txnsubids FROM `payroll_31loanreceipts` WHERE FIND_IN_SET('.$txnsubid.',txnsubids) AND LRID='.$lrid.'';
	$stmt=$link->query($sql); $res=$stmt->fetch();
	
		
		$arr = array_diff(explode(",",$res['txnsubids']),array($txnsubid));
		$sql1='UPDATE `payroll_31loanreceipts` SET `txnsubids`='.(!empty($arr)?"'".implode(',',$arr)."'":'NULL').' WHERE LRID='.$res['LRID'].';';
		$stmt=$link->prepare($sql1); $stmt->execute();
		
	header("Location:ssspagibigloans.php?w=LookupLoanReceipt&LRID=".$lrid."");
	
	break;
	
	case 'AddLoanReceipt':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
	$lrid=intval($_GET['LRID']);
	$sql='UPDATE `payroll_31loanreceipts` SET txnsubids=CONCAT(txnsubids,",'.$_POST['TxnSubID'].'") WHERE LRID IN ('.$lrid.')';
	$stmt = $link->prepare($sql); $stmt->execute();
	header("Location:ssspagibigloans.php?w=LookupLoanReceipt&LRID=".$lrid."");
	break;
	
	
	case 'EditSpecificsLoanReceipt':
		$title='Edit Specifics';
		$lrid=intval($_GET['LRID']);
		$sql=$sqlloanreceiptmain.' WHERE LRID='.$lrid;

	   $columnnameslist=array('DateofReceipt', 'LoanType', 'FromPayrollID','SBR','Company');
   
		$columnnames=$columnnameslist;
		$columnswithlists=array('LoanType','Company');
		$listsname=array('LoanType'=>'loantypelist','Company'=>'companylist');
		
		
		$editprocess='ssspagibigloans.php?w=EditLoanReceipt&LRID='.$lrid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break;
	
	case 'EditLoanReceipt':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$lrid = intval($_GET['LRID']);
		$sql='';
		
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `payroll_31loanreceipts` SET '.$sql.' LoanTypeID='.$loantypeid.',CompanyNo='.$companyno.',EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE Posted=0 AND LRID='.$lrid;
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		header("Location:ssspagibigloans.php?w=LoanReceipts");
		
    break;
	
	case 'DeleteLoanReceipt':
	if (!allowedToOpen(8054,'1rtc')){ echo 'No Permission'; exit(); }
	$lrid=intval($_GET['LRID']);
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `payroll_31loanreceipts` WHERE Posted=0 AND LRID='.$lrid.'';
	
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:ssspagibigloans.php?w=LoanReceipts");
    break;
	
}


 $link=null; $stmt=null;
?>
<!-- end section -->
<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>


<style>
    .faqHeader {
        font-size: 27px;
        margin: 20px;
    }

    .panel-heading [data-toggle="collapse"]:after {
        font-family: 'Glyphicons Halflings';
        content: "\e072"; /* "play" icon */
        float: right;
        color: #F58723;
        font-size: 18px;
        line-height: 22px;
        /* rotate "play" icon from > (right arrow) to down arrow */
        -webkit-transform: rotate(-90deg);
        -moz-transform: rotate(-90deg);
        -ms-transform: rotate(-90deg);
        -o-transform: rotate(-90deg);
        transform: rotate(-90deg);
    }

    .panel-heading [data-toggle="collapse"].collapsed:after {
        /* rotate "play" icon from > (right arrow) to ^ (up arrow) */
        -webkit-transform: rotate(90deg);
        -moz-transform: rotate(90deg);
        -ms-transform: rotate(90deg);
        -o-transform: rotate(90deg);
        transform: rotate(90deg);
        color: #454444;
    }
</style>