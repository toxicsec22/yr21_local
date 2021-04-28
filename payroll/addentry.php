<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;

if (!allowedToOpen(array(791,8176,81761),'1rtc')) {   echo 'No permission'; exit;}
include_once('../switchboard/contents.php');

    
        $whichqry=$_GET['w'];
		
		if (in_array($whichqry,array('DeleteRate','RatesHistory'))){
			if (allowedToOpen(81761,'1rtc')) {
			include_once('../backendphp/layout/linkstyle.php');
			
			echo '<div>';
			
				echo '<a id=\'link\' href="addentry.php?w=RatesHistory">Rates History</a> ';
				echo '<a id=\'link\' href="addentry.php?w=DeleteRate">Delete Approved Rate</a> ';
				
		echo '</div><br/>';
		}
			$sql='select r.TxnId AS TxnID,r.IDNo,CONCAT(e.Nickname," ",e.SurName) AS Name,Branch,`DateofChange`, `BasicRate`, `DeMinimisRate`, `TaxShield`, `SSS-EE`, `Philhealth-EE`, `WTax`, r.`Remarks`, `DailyORMonthly`,CONCAT(e1.Nickname," ",e1.SurName) AS EncodedBy,r.TimeStamp,CONCAT(e2.Nickname," ",e2.SurName) AS ApprovedBy,r.ApprovalTS from payroll_22rates r JOIN attend_30latestpositionsinclresigned lpir ON r.IDNo=lpir.IDNo LEFT JOIN attend_0positions p ON lpir.PositionID=p.PositionID JOIN attend_1defaultbranchassign dba ON r.IDNo=dba.IDNo JOIN 1branches b ON dba.DefaultBranchAssignNo=b.BranchNo LEFT JOIN 1employees e ON r.IDNo=e.IDNo LEFT JOIN 1employees e1 ON r.EncodedByNo=e1.IDNo LEFT JOIN 1employees e2 ON r.ApprovedByNo=e2.IDNo ';
		}
     switch ($whichqry){
        case 'Rates':
	 if (!allowedToOpen(791,'1rtc')) {
   echo 'No permission'; exit;
}
 if (allowedToOpen(8176,'1rtc')) { echo '<h3><a href="addentry.php?w=RatesHistory">Rates History</a></h3><br>'; }
	    include('searchcontri.php');
            $title='Add New Rate';
	    include('payrolllayout/addentryhead.php');
	    if (!isset($_POST['submit'])){
	       $dateofchange=date('Y-m-d',time());
	       $idno='';
	       $monthly='0';
	       $basic='0';
	       // $cola='0';
	       $dem='0';
	       $allow='0';
	       $wtax='0'; $nontax='0';
	       $remarks='';
	       $action='addentry.php?w=Rates';
	    } else {

			$idno=$_POST['IDNo'];
			$basic=$_POST['BasicRate'];

			//check if max
			//monthly condition only
			$sqlmax='SELECT TRUNCATE(MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100),2) AS MAXIMUM FROM attend_1joblevel jl JOIN attend_0jobclass jc ON jc.JobClassNo=jl.JobClassNo JOIN attend_0positions p ON jl.JobLevelNo=p.JobLevelNo AND p.PositionID=(SELECT NewPositionID FROM attend_2changeofpositions WHERE IDNo='.$idno.' ORDER BY DateofChange LIMIT 1)';
			
			$stmtmax=$link->query($sqlmax); $rowmax=$stmtmax->fetch();
			if($basic>$rowmax['MAXIMUM']){
				echo '<br><font color="red">
				<b>ERROR! MAXIMUM LIMIT. Pls contact JYE if you want that salary rate. </b></font>
				<br><br>Encoded Rate = '.$basic.'<br>Max Rate = '.$rowmax['MAXIMUM'].''; 
				exit();
			}

	       $dateofchange=$_POST['DateofChange'];
	       $monthly=$_POST['DailyORMonthly'];
	       // $cola=$_POST['ColaRate'];
	       $dem=$_POST['DeMinimisRate'];
	       $allow=$_POST['TaxShield'];
	       $wtax=$_POST['WTax']; $nontax='0';
	       $remarks=htmlspecialchars($_POST['Remarks']);
	       $action='praddentry.php?w=Rates';
	    }
	    
	    ?>
    <form action='<?php echo $action; ?>' method='POST' enctype='multipart/form-data'>
        Date of Change<input type='date' name='DateofChange' value='<?php echo $dateofchange; ?>'><br>
        ID No<input type='text' name='IDNo' list='employeeid'  value='<?php echo $idno; ?>' autocomplete='off'><br>
	Monthly? (yes-Monthly, no-Daily)<input type='text' name='DailyORMonthly'  value='<?php echo $monthly; ?>' list='yesno' autocomplete='off'><br>
	 Basic Rate<input type='text' name='BasicRate'  value='<?php echo $basic; ?>' autocomplete='off'><br><br>
      	
        <!--If monthly, rates should be in values <b>PER PAYDAY</b>.<br>
	If daily, rates should be in Daily values.<br>
        Values for government-mandated benefits must be per Month.<br><br>-->
        <div style="border: box solid black 1px; background-color: f2f2f2; width: 60%;"><font style="font-weight: semi-bold; color: maroon;"><br>
            &nbsp; <b>DeMinimis</b> is only for Rank 5 and up; and tax shield for dept heads only.<br><br>
        &nbsp; <b>Tax-shield</b> must be covered with relevant receipts.  HR-Compenben must submit consolidated receipts to Admin WITHIN the month of payroll.<br><br>
        </font>
        </div>
        <br><br>
	
	 <!--Cola Rate<input type='text' name='ColaRate' value='<?php //echo $cola; ?>' autocomplete='off'><br>-->
	 DeMinimisRate (max of 6k per month) <input type='text' name='DeMinimisRate' value='<?php echo $dem; ?>' autocomplete='off'><br>
	 Tax Shield<input type='text' name='TaxShield' value='<?php echo $allow; ?>' autocomplete='off'><br>
	 Withholding Tax<input type='text' name='WTax'  value='<?php echo $wtax; ?>' autocomplete='off'><br><br>
<!--	 Minimum Wage Earner? (Tax Exempted)<input type='text' name='MinWageEarner'  value='<?php //echo $nontax; ?>' list='yesno' autocomplete='off' size=2><br><br>-->
        Remarks<input type='text' name='Remarks'  value='<?php echo $remarks; ?>' autocomplete='off'><br><br>
	
	<?php
	if (!isset($_POST['submit'])){ ?>
        <input type='submit' name='submit' value='Submit'>
	 <?php } else { ?>
	 
	    <?php
		
		$sql='SELECT LatestBasicRate, LatestDeMinimisRate FROM payroll_20latestrates WHERE IDNo='.$idno.';';
		$stmt=$link->query($sql); $row=$stmt->fetch();
		
		if($stmt->rowCount()>0){
			if($basic<$row['LatestBasicRate']){
				echo '<font color="red">ERROR! <b>NEW</b> basic rate must not be less than the <b>OLD</b> basic rate.</font>'; exit();
			}
			if(($basic+$dem)<($row['LatestBasicRate']+$row['LatestDeMinimisRate'])){
				echo '<font color="red">ERROR! <b>NEW</b> (Basic + DeMinimisRate) must not be less than the <b>OLD</b> (Basic + DeMinimisRate).</font>'; exit();
			}
		}
		
		
            if ($monthly==1){ $multiplier=1; } else { $multiplier=26.08; }
	    $grossbasic=$basic*$multiplier;
            // $grosscola=$cola*$multiplier;
            $grossdem=$dem*$multiplier;
            $grossallow=$allow*$multiplier;
            // $monthlybasicsss=($grossbasic+$grosscola);
            $monthlybasicsss=($grossbasic);
            // $monthlygrosstotal=($grossbasic+$grosscola+$grossdem+$grossallow);
            $monthlygrosstotal=($grossbasic+$grossdem+$grossallow);
	     ?>
	    SSS-EE  <input type='text' name='SSS-EE'  value='<?php echo getContriEE($monthlybasicsss,'sss'); ?>' >
	    &nbsp &nbsp &nbsp &nbsp SSS SalaryCredit <?php echo getSalaryCredit($monthlybasicsss,'sss'); ?><br>
	    Philhealth-EE  <input type='text' name='Philhealth-EE'  value='<?php echo getContriEE($grossbasic,'phic'); ?>' >
<!--	    &nbsp &nbsp &nbsp &nbsp Philhealth SalaryCredit--> <?php// echo getSalaryCredit($grossbasic,'phic'); ?><br>
            PagIbig-EE  <input type='text' name='PagIbig-EE'  value='100' ><br><br>
            Monthly Gross Basic <?php echo number_format($grossbasic,2); ?><br>
            <!--Monthly Gross COLA <?php //echo number_format($grosscola,2); ?><br>-->
            Monthly Gross De Minimis (must be max of 6k)  <?php echo number_format($grossdem,2); ?><br>
            Monthly Gross Tax Shield <?php echo number_format($grossallow,2); ?><br>
            Monthly Total Gross <?php echo number_format($monthlygrosstotal,2); ?><br>
	    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
	<input type='submit' name='confirm' value='Confirm and get approval'>
	 </form>
	 <?php } ?>
	 
   
<?php
$liststoshow=array('employeeid','yesno');
include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);    
}
	    
            break;
        
	case 'Adjust':
	 if (!allowedToOpen(790,'1rtc')) {   echo 'No permission'; exit;}
            $title='Add New Adjustment';
            echo '<br><br>';
	    include('payrolllayout/addentryhead.php');
	    if (!isset($_POST['submit'])){
	       $payrollid='';
	       $idno='';
	       $adjtypeno='';
	       $amt='0';
	       $remarks='';
	    } else {
	       $payrollid=$_POST['PayrollID'];
	       $idno=$_POST['IDNo'];
	       $adjtypeno=$_POST['AdjustTypeNo'];
	       $amt=$_POST['AdjustAmt'];
	       $remarks=htmlspecialchars($_POST['Remarks']);
	    }

            ?><br><br><div style='margin-left: 5%;'>
    <form action='praddentry.php?w=Adjust' method='POST' enctype='multipart/form-data'>
        Payroll ID<input type='text' name='PayrollID'  value='<?php echo $payrollid; ?>' list='activepayperiods' autocomplete='off'>
        &nbsp; &nbsp; &nbsp;
        ID No<input type='text' name='IDNo' list='employeeid'  value='<?php echo $idno; ?>' autocomplete='off'>
	&nbsp; &nbsp; &nbsp;
	Adjustment Type<input type='text' name='AdjustTypeNo'  value='<?php echo $adjtypeno; ?>' list='payrolladjtype' autocomplete='off'><br><br>
        <div style='font-weight: 400; color: yellow; font-style: italic;'>Note: Deductions must be recorded as negative values.</div><br><br>
	Amount<input type='text' name='AdjustAmt'  value='<?php echo $amt; ?>' autocomplete='off' size='6'>
        &nbsp; &nbsp; &nbsp;
        Remarks<input type='text' name='Remarks'  value='<?php echo $remarks; ?>' autocomplete='off'>
        &nbsp; &nbsp; &nbsp; &nbsp;
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
        <input type='submit' name='submit' value='Submit'>
	 </form>
	 <?php
	 	 if(isset($_GET['Message'])){
		echo $_GET['Message'];
		}
	 ?></div>
	 <br><br>
         <hr><br><br><h4>Upload Future Adjustments</h4><br><br><div style='margin-left: 5%;'>
	 <div style="background-color:#cccccc; width:33%; border: 1px solid black; padding:5px;">
		<b>Required Columns:</b><br><br>
		&nbsp; &nbsp; &nbsp; PayrollID, IDNo, AdjustTypeNo, AdjustAmt, Remarks, BranchNo<br><br>
                <b>Note:</b></br>
                &nbsp; &nbsp; &nbsp; <ol style='margin-left: 5%;'>
                    <li>Amount must have no commas.</li>
                    <li>Deductions must be in negative format.</li>
                    <li>File to be uploaded must be saved as a csv file.</li>
                </ol><br>
			 </div></br><br>
	 <!--<table style="border:1px solid black;  padding: 3px; font-size:9pt;"><tr><td><h3>Upload Adjustment</h3></br>-->
	<form method="post" action="addentry.php?w=upload" enctype="multipart/form-data">		
			<input type="file" name="userfile" accept="csv/text" required><input type="submit" name="upload" value="Upload" OnClick="return confirm('Are you sure you want to Upload?');"></form></td></tr><!--</table>-->
</div>
<?php
$liststoshow=array('employeeid','activepayperiods','payrolladjtype');
include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);    
}
	    
		
		echo '<br><hr>';
		$title='Adjustments To Be Forwarded to Next Year';
		$columnnames=array('PayrollID','IDNo','Name','AdjustTypeNo','AdjustType','AdjustAmt','Remarks','BranchNo','Branch');
		$sql='SELECT PayrollID, spa.IDNo,CONCAT(Nickname," ",Surname) AS Name, spa.AdjustTypeNo,AdjustType, AdjustAmt, Remarks, spa.BranchNo,Branch FROM payroll_21scheduledpaydayadjustments spa JOIN 1branches b ON spa.BranchNo=b.BranchNo JOIN payroll_0acctid ai ON spa.AdjustTypeNo=ai.AdjustTypeNo JOIN 1employees e ON spa.IDNo=e.IDNo WHERE spa.AdjustTypeNo IN (30,31) AND PayrollID IN (23,24)
		UNION ALL
		SELECT PayrollID, spa.IDNo,CONCAT(Nickname," ",Surname) AS Name, spa.AdjustTypeNo,AdjustType, AdjustAmt, Remarks, spa.BranchNo,Branch FROM payroll_21scheduledpaydayadjustments spa JOIN 1branches b ON spa.BranchNo=b.BranchNo JOIN payroll_0acctid ai ON spa.AdjustTypeNo=ai.AdjustTypeNo JOIN 1employees e ON spa.IDNo=e.IDNo WHERE spa.AdjustTypeNo in (11,41,27,28) and payrollid in (24)
		ORDER BY AdjustTypeNo, IDNo;';
		$formdesc='<br><div style="margin-left:35px;">List of adjustments in Payroll 24 for<br>1. SSS Loan<br>2. Pagibig Loan<br>3. OIC Allowance<br>4. Transportation Allowance<br>5. Relocation Allowance</div>';
		include_once('../backendphp/layout/displayastablenosort.php');
            break;
			
			case 'upload':
			
			$requireencodedby=true;
	// $requiredts=true;
	if(isset($_POST['upload'])){
$tblname='payroll_21scheduledpaydayadjustments'; $firstcolumnname='PayrollID';
$DOWNLOAD_DIR="../../uploads/"; 
    if (!isset($_FILES['userfile'])) { goto nodata; }
$maxsize = 10004800; //MAX Size 10MB


if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
       
                $csv_file=$_FILES['userfile']['name'];
				
				$ext = pathinfo($csv_file, PATHINFO_EXTENSION);
				if( $ext !== 'csv' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] > $maxsize)){ echo 'Error! Invalid File Size (MAX 10MB).'; exit(); }
				
                $file_to_use=$DOWNLOAD_DIR . $csv_file;
                if (file_exists($file_to_use)) {
                    unlink($file_to_use);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$file_to_use)) {
                 $good="Successfully_added_$csv_file";
                 }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
           } else {
             $good="Did not work  " . "Error: " . $_FILES["userfile"]["error"];            
            echo $csv_file . " is the file name";
            }


$csv = array_map("str_getcsv", file($file_to_use,FILE_SKIP_EMPTY_LINES));
$keys = array_shift($csv);

$numcols = count($keys)-1;
$num=0;
$fieldlist="";
while ($num<$numcols) {
    $fieldlist=$fieldlist . $keys[$num].", ";
    $num=$num+1;
}
$fieldlist=$fieldlist . $keys[$numcols];


$query="";
$row = 1;
if (($handle = fopen($file_to_use,"r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num=0;
    $values=""; //echo $data[0];
if($data[0]!=$firstcolumnname){

        while ($num<=$numcols) {
          $values=$values."'". addslashes($data[$num]) . (($num<$numcols)?"', ":"'");
          $num=$num+1;
        } //end while 

        if(isset($requireencodedby) and $requireencodedby==true) { $fieldlist2=$fieldlist.",EncodedByNo"; $values.=",".$_SESSION['(ak0)']; } else { $fieldlist2=$fieldlist;}
        if(isset($requiredts) and $requiredts==true) { $fieldlist2.=",TimeStamp"; $values.=",Now()"; }
        if(isset($requireencodedby) OR isset($requiredts)) { $fields=$fieldlist2; } else { $fields=$fieldlist; }
$query="Insert into $tblname (" . $fields . ") values (" . $values . ");";
echo $query;
if($_SESSION['(ak0)']==1002 OR $_SESSION['(ak0)']==1003){ echo $query . "<br>" ; print_r($data). "<br>" ;}
  $row++;
$link->query($query);
} //end if        
  
    }
    fclose($handle);
}
// exit();
echo ($row-1) . " rows successfully imported to database!!";
$Message = urlencode("successfully added");
header("Location:addentry.php?w=Adjust&Message=".$Message."");
}
	  nodata:
	  echo 'walang laman';
			
			
			break;
	 
	 case 'AdjPerPayID':
	    if (!allowedToOpen(789,'1rtc')) {   echo 'No permission'; exit;}
            $title='Add Adjustment for Current Payroll';
	    include('payrolllayout/addentryhead.php');
	    if (!isset($_POST['submit'])){
	       $payrollid='';
	       $idno='';
	       $branchno='';
	       $adjtypeno='';
	       $amt='0';
	    } else {
	       $payrollid=$_POST['PayrollID'];
	       $idno=$_POST['IDNo'];
	       $branchno=$_POST['BranchNo'];
	       $adjtypeno=$_POST['AdjustTypeNo'];
	       $amt=$_POST['AdjustAmt'];
	    }

	    ?>
    <form action='praddentry.php?w=AdjPerPayID' method='POST' enctype='multipart/form-data'>
        Payroll ID<input type='text' name='PayrollID'  value='<?php echo $payrollid; ?>' list='activepayperiods' autocomplete='off'><br><br>
        ID No<input type='text' name='IDNo' list='employeeid'  value='<?php echo $idno; ?>' autocomplete='off'><br><br>
        BranchNo<input type='text' name='BranchNo' list='branchnames'  value='<?php echo $branchno; ?>' autocomplete='off'><br>
	<br>
	Adjustment Type<input type='text' name='AdjustTypeNo'  value='<?php echo htmlspecialchars($adjtypeno); ?>' list='payrolladjtype' autocomplete='off'><br><br>
	<i>Note: Deductions must be recorded as negative values.</i><br><br>
	Amount<input type='text' name='AdjustAmt'  value='<?php echo $amt; ?>' autocomplete='off'><br><br>
	Remarks<input type='text' name='Remarks'> <br><br>
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
        <input type='submit' name='submit' value='Submit'>
	 </form>
<?php
$liststoshow=array('employeeid','activepayperiods','payrolladjtype','branchnames');
include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);    
}
	    
            break;
	
	 
   case 'Bonuses':
      if (!allowedToOpen(8111,'1rtc')) { echo 'No permission'; exit;}
            $title='Add Bonus';
	    include('payrolllayout/addentryhead.php');
	    if (!isset($_POST['submit'])){
	       $payrollid='23';
	       $idno='';
	       $adjtypeno='23';
	       $amt='0';
	       $remarks='';
	    } else {
	       $payrollid=$_POST['PayrollID'];
	       $idno=$_POST['IDNo'];
	       $adjtypeno=$_POST['AdjustTypeNo'];
	       $amt=$_POST['AdjustAmt'];
	       $remarks=htmlspecialchars($_POST['Remarks']);
	    }

	    ?>
    <form action='praddentry.php?w=AddBonus' method='POST' enctype='multipart/form-data'>
        Payroll ID<input type='text' name='PayrollID'  value='<?php echo $payrollid; ?>' list='activepayperiods' autocomplete='off'><br>
        ID No<input type='text' name='IDNo' list='employeeid'  value='<?php echo $idno; ?>' autocomplete='off'><br>
	<br>
	Adjustment Type<input type='text' name='AdjustTypeNo'  value='<?php echo $adjtypeno; ?>' list='payrolladjtype' autocomplete='off'><br><br>
	23: Performance &nbsp &nbsp &nbsp 24: Special <br><br>
	Amount<input type='text' name='AdjustAmt'  value='<?php echo $amt; ?>' autocomplete='off'><br><br>
        Remarks<input type='text' name='Remarks'  value='<?php echo $remarks; ?>' autocomplete='off'><br><br>
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
        <input type='submit' name='submit' value='Submit'>
	 </form>
<?php
$liststoshow=array('employeeid','activepayperiods','payrolladjtype');
include_once "../generalinfo/lists.inc";
foreach ($liststoshow as $list){
renderlist($list);    
}
	    
            break;
        
case 'RatesHistory':
		if (!allowedToOpen(8176,'1rtc')) { echo 'No permission'; exit;}
		$sql.=' ORDER BY DateofChange DESC;';
		$title='Rates History';
		$columnnames=array('IDNo','Name','Branch','DateofChange','BasicRate', 'DeMinimisRate', 'TaxShield', 'SSS-EE', 'Philhealth-EE', 'WTax', 'Remarks', 'DailyORMonthly','EncodedBy','TimeStamp','ApprovedBy','ApprovalTS');
		include_once('../backendphp/layout/displayastable.php');
		break; 

		
case 'DeleteRate':
		if (!allowedToOpen(81761,'1rtc')) { echo 'No permission'; exit;}
		$title="Delete Approved Rate";
			echo '<title>'.$title.'</title>';
			echo '<h3>'.$title.'</h3>';
			
			echo '<br>';
			include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
			echo comboBox($link,'SELECT IDNo,FullName FROM `attend_30currentpositions` ORDER BY FullName;','FullName','IDNo','employees');
			echo '<form action="#" method="POST" autocomplete="off">';
			echo 'IDNo: <input type="text" name="IDNo" list="employees" value="'.(isset($_POST['IDNo'])?$_POST['IDNo']:'').'">';
			echo ' DateOfEntry: <input type="date" name="DateOfEntry" value="'.(isset($_POST['DateOfEntry'])?$_POST['DateOfEntry']:'').'">';
			echo ' <input type="submit" value="Lookup" name="btnLookup">';
			echo '</form>';
		 if(isset($_POST['btnLookup'])){
			 $title='';
			 $sql.=' WHERE r.IDNo='.$_POST['IDNo'].' AND r.DateofChange="'.$_POST['DateOfEntry'].'";';
			 
			 $delprocess='addentry.php?w=PrDeleteRate&TxnID=';
			$columnnames=array('IDNo','Name','Branch','DateofChange','BasicRate', 'DeMinimisRate', 'TaxShield', 'SSS-EE', 'Philhealth-EE', 'WTax', 'Remarks', 'DailyORMonthly','EncodedBy','TimeStamp','ApprovedBy','ApprovalTS');
			include_once('../backendphp/layout/displayastable.php');
		 }
		
		
		
		
		break;
		
		
case 'PrDeleteRate':
		if (!allowedToOpen(81761,'1rtc')) { echo 'No permission'; exit;}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$condi='';
		if(!allowedToOpen(7913,'1rtc')){
			$condi=' MONTH(DateofChange) >= MONTH(NOW() - INTERVAL 1 MONTH) AND';
		}
		$sql='DELETE FROM `payroll_22rates` WHERE '.$condi.' TxnId='.intval($_GET['TxnID']);
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:addentry.php?w=RatesHistory");
		break;
		
        default:
            break;
     }
noform:
     $link=null; $stmt=null; 
?>
</body>
</html>