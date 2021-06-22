<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(800,'1rtc')) {   echo 'No permission'; exit;}
$showbranches=false;
if(!isset($_REQUEST['print'])){ include_once('../switchboard/contents.php');}
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
?>
<html>
<head>
<title>Government Summaries</title>
<?php

include_once("../backendphp/layout/regulartablestyle.php");
$whichqry=$_GET['w'];
?>
<style>
@media print  
{
    table {
        page-break-inside: avoid;
    }
    
}
td {
        font-size: small;
    }
    thead{
      font-size: medium;
    }
</style>
<?php
if(!isset($_REQUEST['print'])){
	include_once '../payroll/govtdeductionlinks.php';


    $payrollid=(isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));
	if($whichqry<>'defaultpage'){
   ?>
<form method='post' action='#' enctype='multipart/form-data'>
    Lookup for Payroll ID<input type='text' name='payrollid' list='payperiods' autocomplete='off' value='<?php echo $payrollid?>'>
    <input type='submit' name='submit' value='Lookup'>
<?php
	}
include_once '../generalinfo/lists.inc';


renderlist('payperiods'); 
if (!isset($_POST['payrollid']) and !isset($_SESSION['payrollidses'])){ goto end;}
else { include('payrolllayout/setpayidsession.php');}
echo str_repeat('&nbsp;',7).'<a href="govtsummaries.php?w='.$whichqry.'&print=1">Print!</a>';
echo '<br/><br style="line-height:50px;" /><h4>From Payroll '.$_SESSION['payrollidses'].'</h4><br style="line-height:30px;" />';
}




     switch ($whichqry){
      case 'SSS':
         $reportname='SSS Summary for ';
         $cogovtno='SSSNo';
         $sqlfirst='SELECT ss.*,b.Branch AS RecordInBranch,`SSS-EE`+`SSS-ERTotal`-EC-MPFER as SS_Amt, EC AS EC_Amt, MPFER AS MPFER_Amt, FORMAT(SSECMPFCredit,0) AS TotalSalaryCredit,`Company` FROM payroll_40sss ss JOIN 1companies c ON c.CompanyNo=ss.CompanyNo LEFT JOIN 1branches b ON ss.RecordInBranchNo=b.BranchNo WHERE PayrollID='.$_SESSION['payrollidses'];
         
		 //removed EC and ss amt for easy view
         // $columnnames=array('SurName','FirstName','MI','RecordInBranch','SSSNo','SSS-EE','SSS-ERTotal','EC_Amt','SS_Amt','SSSTotal');
         $columnnames=array('SurName','FirstName','MI','RecordInBranch','SSSNo','SSS-EE','SSS-ERTotal','SSSTotal','TotalSalaryCredit');
       //  $sqlsumfirst='SELECT `Company`, Round(Sum(`SSS-EE`),2) as SSSEE,Round(Sum(`SSS-ER`),2) as SSSER,Sum(`SSSTotal`) as SSSTotal FROM payroll_40sss WHERE PayrollID='.$_SESSION['payrollidses'];
         $sqlsumfirst='SELECT `Company`, Round(Sum(`SSS-EE`+`SSS-ERTotal`-`EC`-MPFER),2) as SS_Amt, ROUND(Sum(`EC`),0) as EC_Amt, ROUND(Sum(`MPFER`),0) as MPFER_Amt,Sum(`SSSTotal`) as SSSTotal FROM payroll_40sss ss JOIN 1companies c ON c.CompanyNo=ss.CompanyNo WHERE PayrollID='.$_SESSION['payrollidses'];
         $columntotals=array('SS_Amt','EC_Amt','MPFER_Amt','SSSTotal');
      break;
   case 'PHIC':
         $reportname='PHIC Summary for ';
         $cogovtno='PHICNo';
         $sqlfirst='SELECT pc.*,b.Branch AS RecordInBranch FROM payroll_41phic pc LEFT JOIN 1branches b ON pc.RecordInBranchNo=b.BranchNo WHERE PayrollID='.$_SESSION['payrollidses'];
         
         $columnnames=array('SurName','FirstName','MI','RecordInBranch','PHICNo','PHIC-EE','PHIC-ER','PHICTotal');
         $sqlsumfirst='SELECT `Company`, Round(Sum(`PHIC-EE`),2) as PHICEE,Round(Sum(`PHIC-ER`),2) as PHICER,Sum(`PHICTotal`) as PHICTotal FROM payroll_41phic WHERE PayrollID='.$_SESSION['payrollidses'];
         $columntotals=array('PHICEE','PHICER','PHICTotal');
      break;
   case 'PagIbig':
         $reportname='PagIbig Summary for ';
         $cogovtno='PagIbigNo';
         $sqlfirst='SELECT pi.*,b.Branch AS RecordInBranch FROM payroll_42pagibig pi LEFT JOIN 1branches b ON pi.RecordInBranchNo=b.BranchNo WHERE PayrollID='.$_SESSION['payrollidses'];
         
         $columnnames=array('SurName','FirstName','MI','RecordInBranch','PagIbigNo','PagIbig-EE','PagIbig-ER','PagIbigTotal');
         $sqlsumfirst='SELECT `Company`, Round(Sum(`PagIbig-EE`),2) as PagIbigEE,Round(Sum(`PagIbig-ER`),2) as PagIbigER,Sum(`PagIbigTotal`) as PagIbigTotal FROM payroll_42pagibig WHERE PayrollID='.$_SESSION['payrollidses'];
         $columntotals=array('PagIbigEE','PagIbigER','PagIbigTotal');
      break;
   case 'WTax':
      $reportname='Withholding Tax Summary for ';
         $cogovtno='TIN';
         $sqlfirst='SELECT wt.*,b.Branch AS RecordInBranch FROM payroll_43wtax wt  LEFT JOIN 1branches b ON wt.RecordInBranchNo=b.BranchNo  WHERE PayrollID='.$_SESSION['payrollidses'];
         
         $columnnames=array('SurName','FirstName','MI','RecordInBranch','TIN','WTax');
         $sqlsumfirst='SELECT `Company`, Round(Sum(`WTax`),2) as WTax FROM payroll_43wtax WHERE PayrollID='.$_SESSION['payrollidses'];
         $columntotals=array('WTax');
      break;
   case 'SSSLoans':
      $reportname='SSSLoans-Salary Summary for ';
         $cogovtno='SSSNo';
         $sqlfirst='SELECT sl.*,b.Branch AS RecordInBranch FROM payroll_44sssloan sl LEFT JOIN 1branches b ON sl.RecordInBranchNo=b.BranchNo WHERE PayrollID='.$_SESSION['payrollidses'];
         
         $columnnames=array('SurName','FirstName','MI','RecordInBranch','SSSNo','SSSLoan');
         $sqlsumfirst='SELECT `Company`, Round(Sum(`SSSLoan`),2) as SSSLoan FROM payroll_44sssloan WHERE PayrollID='.$_SESSION['payrollidses'];
         $columntotals=array('SSSLoan');
      break;
   case 'SSSLoansCalamity':
      $reportname='SSSLoans-Calamity Summary for ';
         $cogovtno='SSSNo';
         $sqlfirst='SELECT slc.*,b.Branch AS RecordInBranch FROM payroll_44sssloancalamity slc LEFT JOIN 1branches b ON slc.RecordInBranchNo=b.BranchNo WHERE PayrollID='.$_SESSION['payrollidses'];
         
         $columnnames=array('SurName','FirstName','MI','RecordInBranch','SSSNo','SSSLoan');
         $sqlsumfirst='SELECT `Company`, Round(Sum(`SSSLoan`),2) as SSSLoan FROM payroll_44sssloancalamity WHERE PayrollID='.$_SESSION['payrollidses'];
         $columntotals=array('SSSLoan');
      break;
   case 'PagIbigLoans':
         $reportname='PagIbigLoans-Salary Summary for ';
         $cogovtno='PagIbigNo';
         $sqlfirst='SELECT pil.*,b.Branch AS RecordInBranch FROM payroll_45pagibigloan pil LEFT JOIN 1branches b ON pil.RecordInBranchNo=b.BranchNo WHERE PayrollID='.$_SESSION['payrollidses'];
         
         $columnnames=array('SurName','FirstName','MI','RecordInBranch','PagIbigNo','PagibigLoan');
         $sqlsumfirst='SELECT `Company`, Round(Sum(`PagibigLoan`),2) as PagibigLoan FROM payroll_45pagibigloan WHERE PayrollID='.$_SESSION['payrollidses'];
         $columntotals=array('PagibigLoan');
      break;
   case 'PagIbigLoansCalamity':
         $reportname='PagIbigLoans-Calamity Summary for ';
         $cogovtno='PagIbigNo';
         $sqlfirst='SELECT pilc.*,b.Branch AS RecordInBranch FROM payroll_45pagibigloancalamity pilc LEFT JOIN 1branches b ON pilc.RecordInBranchNo=b.BranchNo WHERE PayrollID='.$_SESSION['payrollidses'];
         
         $columnnames=array('SurName','FirstName','MI','RecordInBranch','PagIbigNo','PagibigLoan');
         $sqlsumfirst='SELECT `Company`, Round(Sum(`PagibigLoan`),2) as PagibigLoan FROM payroll_45pagibigloancalamity WHERE PayrollID='.$_SESSION['payrollidses'];
         $columntotals=array('PagibigLoan');
      break;
     }
if (allowedToOpen(800,'1rtc')){ $sqlcompanies='SELECT * from `1companies` WHERE Active=1';}
else { 
     $sqlcompanies='SELECT c.* from `1companies` c WHERE Active=1 ';}
     
//$sqlcompanies='SELECT * from `1companies` WHERE Active=1';
$stmtcompanies=$link->query($sqlcompanies);
$resultcompanies=$stmtcompanies->fetchAll();
foreach ($resultcompanies as $co){
$Company='';
$Company=$co['Company'];
$titlepercompany='<br>'.$reportname.$co['CompanyName'].'&nbsp &nbsp'.$cogovtno.':  '.$co[$cogovtno].'<br>';
$sql=$sqlfirst.' and Company Like \''.$Company.'\'';
//echo $sql;
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
$colcount=count($columnnames)+1;

$sqlsum=$sqlsumfirst.' and Company Like \''.$Company.'\' Group By Company';
//echo $sqlsum;
$stmtsum=$link->query($sqlsum);
$resultsum=$stmtsum->fetch();

$tabletitle='<td>&nbsp</td>';
$tabledata='';
$govttotals='';
foreach($columnnames as $col){
      $tabletitle=$tabletitle.'<td>'.$col.'</td>';
   }
$records='0';
foreach($result as $row){
$records=$records+1;
  $tabledata=$tabledata.'<tr><td>'.$records.'</td>';
   foreach($columnnames as $col){
      $tabledata=$tabledata.'<td>'.$row[$col].'</td>';
   }
   $tabledata=$tabledata.'</tr>';
}
    
   foreach($columntotals as $total){ 
    $govttotals=$govttotals.$total.' : '.number_format($resultsum[$total],2).str_repeat('&nbsp;',8);

}
$govtdata='<table>'.'<thead><tr><td colspan="'.$colcount.'">'.$titlepercompany.'</td></tr><tr>'.$tabletitle.'</tr></thead><tbody>'.$tabledata.'</tbody><tfoot><tr><td colspan="'.$colcount.'"> Totals '.str_repeat('&nbsp',5).$govttotals.'</td></tr></tfoot></table><br><br>';
echo $govtdata;
}

end:
     $link=null; $stmt=null;
?>
</form></body></html>