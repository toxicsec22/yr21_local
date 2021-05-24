<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
        
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        $showbranches=false;
include_once('../switchboard/contents.php');
 

if (!allowedToOpen(826,'1rtc')) { echo 'No permission'; exit;}
$title='Reprocess Payroll per IDNo';
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$sql0='SELECT IDNo, CONCAT(FullName," - ", Branch) AS Name FROM `attend_30currentpositions` p ';
echo comboBox($link, $sql0, 'Name', 'IDNo', 'emplist');

$payrollid=(isset($_SESSION['payrollidses'])?$_SESSION['payrollidses']:((date('m')*2)+(date('d')<15?-1:0)));

?><br>
<title><?php echo $title; ?></title>
<div style='margin-left: 30%;'>
<br><br>

<?php
if (!isset($_POST['submitID'])) {
?>
<form method='post' name='lookupIDNo' action='#' enctype='multipart/form-data'>
    Reprocess for Payroll <b><?php echo $payrollid;?></b> for :  <input type="text" name="IDNo" list="emplist"/>
    <input type='submit' name='submitID' value='Lookup' onClick="return confirm(saveValue())">
</form>
<?php 
}
if (!isset($_POST['submitID']) and !isset($_POST['submitIDNoforrecalc'])) { goto nodata; }

$idno=$_POST['IDNo'];

$sqlattend='SELECT a.*, FirstName,SurName FROM payroll_20fromattendance a JOIN 1employees e ON e.IDNo=a.IDNo WHERE a.PayrollID='.$payrollid.' AND e.IDNo='.$idno;
$stmtattend=$link->query($sqlattend); $resattend=$stmtattend->fetch();

$attendcolumns=array('RegDaysPresent', 'LWOPDays', 'LegalDays', 'SpecDays', 'SLDays','VLDays', 'RestDays', 'LWPDays','QDays', 'RegDaysActual','RegExShiftHrsOT','RestShiftHrsOT','SpecShiftHrsOT','LegalShiftHrsOT','RestExShiftHrsOT','SpecExShiftHrsOT','LegalExShiftHrsOT');
$attend=''; 
foreach ($attendcolumns as $attendcol){
    $attend.=$attendcol.'&nbsp; <input type=text size=4 name='.$attendcol.' value="'.$resattend[$attendcol].'" ><br>';
    }


echo '<h3>Reprocess Payroll ID '.$payrollid.' for IDNo '.$idno.' - '.$resattend['FirstName'].' '.$resattend['SurName'].'</h3>';
?>

<form method='post' name='reprocessIDNo' action='#' enctype='multipart/form-data' style='align-items: center;'>
    <input type="hidden" name="IDNo" value='<?php echo $idno;?>'  size=5></input>
    <input type='hidden' name='payrollid' value='<?php echo $payrollid;?>' size=5>
    <br><br><i>Payroll Adjustments are NOT recalculated.  These must be done manually if necessary.</i><br><br><u><b>Attendance Basis for Payroll</b></u><br><br>
    <?php echo $attend; ?>
<!--	readonly="readonly" style="background:#D6DBDF;"-->
    <input type='hidden' name='submit2' value='PREVIEW Payroll'> <br><br>
    <input type='submit' name='submitIDNoforrecalc' value='Reprocess!' onClick="saveValue()">
</form>
      <br><br><hr>
      
      <script>
    function saveValue(){
        return confirm("Reprocess payroll " + document.reprocessIDNo.payrollid.value +" for IDNo " + document.reprocessIDNo.IDNo.value);
        return false;
    }
</script>
      
<?php
if (!isset($_POST['submitIDNoforrecalc'])) { goto nodata;}

$update='';
foreach ($attendcolumns as $attendcol){
    $update.=$attendcol.'='.$_POST[$attendcol].', ';
}
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
$sql0='UPDATE payroll_20fromattendance a SET '.$update.' EncodedByNo='.$_SESSION['(ak0)'].', `TimeStamp`=Now() WHERE (SELECT Posted FROM payroll_1paydates pd WHERE pd.PayrollID=a.PayrollID)=0 AND PayrollID='.$payrollid.' AND IDNo='.$idno; 

if($_SESSION['(ak0)']==1002) { echo $sql0;}
$stmt0=$link->prepare($sql0); $stmt0->execute(); 

include 'processpayroll.php';

$sql='SELECT * FROM payroll_25payrolldatalookuptemp WHERE IDNo='.$idno;
$stmt=$link->query($sql); $res=$stmt->fetch();

// $paycolumns=array('Basic','DeM','TaxSh','OT','AbsenceBasic','UndertimeBasic','AbsenceTaxSh','UndertimeTaxSh','SSS-EE','PhilHealth-EE','PagIbig-EE','WTax','SSS-ER','PhilHealth-ER');
$paycolumns=array('RegDayBasic','RegDayDeM','RegDayTaxSh','VLBasic','VLDeM','VLTaxSh','SLBasic','SLDeM','SLTaxSh','LWPBasic','LWPDeM','LWPTaxSh','RHBasicforDaily','RHDeMforDaily','RHTaxShforDaily','AbsenceBasicforMonthly','AbsenceDeMforMonthly','AbsenceTaxShforMonthly','UndertimeBasic','UndertimeDeM','UndertimeTaxSh','RegDayOT','RestDayOT','SpecOT','RHOT','SSS-EE','PhilHealth-EE','PagIbig-EE','SSS-ER','PhilHealth-ER','PagIbig-ER','WTax');

$update='';
foreach ($paycolumns as $paycol){
    $update.='`'.$paycol.'`='.(empty($res[$paycol])?0:$res[$paycol]).', ';
}
        
$sql0='UPDATE payroll_25payroll p SET '.$update.' EncodedByNo='.$_SESSION['(ak0)'].' WHERE (SELECT Posted FROM payroll_1paydates pd WHERE pd.PayrollID=p.PayrollID)=0 AND PayrollID='.$payrollid.' AND IDNo='.$idno;
if($_SESSION['(ak0)']==1002) { echo $sql0;}
$stmt0=$link->prepare($sql0); $stmt0->execute(); 

$title='';
echo '<h4>Updated payroll of '.$idno.' - '.$resattend['FirstName'].' '.$resattend['SurName'].'</h4>';
$sql='SELECT * FROM payroll_25payroll p JOIN attend_30currentpositions e ON p.IDNo=e.IDNo WHERE PayrollID='.$payrollid.' AND p.IDNo='.$idno;
$columnnames= array_diff($columnnames, array('RecordInBranch','SSSBasis','SalaryCredit','TotalAdj','NetPay','DisburseVia'));
$hidecount=true;
include('../backendphp/layout/displayastableonlynoheaders.php');
nodata:
    ?>
</div>