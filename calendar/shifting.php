<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$showbranches=false;
include_once('../switchboard/contents.php');
if (!empty($_SERVER['HTTPS'])) {
    $https='s';
  } else {
    $https='';
  }
?>
<head>
<link href="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <!-- Custom styles for this template-->
<link href="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/css/bootstrap.min.css" rel="stylesheet">
</head>

<?php
/*
* days_in_month($month, $year)
* Returns the number of days in a given month and year, taking into account leap years.
*
* $month: numeric month (integers 1-12)
* $year: numeric year (any integer)
*
* Prec: $month is an integer between 1 and 12, inclusive, and $year is an integer.
* Post: none
*/
// corrected by ben at sparkyb dot net
function days_in_month($month, $year)
{
// calculate number of days in a month
    return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}

$monthno=isset($_REQUEST['MonthNo'])?$_REQUEST['MonthNo']:date('m');
$areano=isset($_REQUEST['AreaNo'])?$_REQUEST['AreaNo']:1;

$daysno=days_in_month($monthno, $currentyr);
$arrdayno=array();
if(isset($_REQUEST['MonthNo']) AND $_REQUEST['MonthNo']<>date('m')){
    $daynocnt=1;
} else {
    $daynocnt=date('d');
}
while($daysno>=$daynocnt){
    $arrdayno[]=$daynocnt;
    $daynocnt++;
}

$monthnopad=str_pad($monthno,2,"0",STR_PAD_LEFT);
$title='Shifting Report';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3>';
$monthname=date('F',strtotime($currentyr.'-'.$monthnopad.'-01'));
echo comboBox($link,'SELECT AreaNo,Area FROM 0area WHERE AreaNo>0;','Area','AreaNo','arealist');

echo '<div style="background-color:#fff;padding:5px;"><h3><a href="shifting.php?MonthNo='.($monthno-1).'">&lt;&lt;</a> '.$monthname.' <a href="shifting.php?AreaNo='.$areano.'&MonthNo='.($monthno+1).'">&gt;&gt;</a> &nbsp; &nbsp; &nbsp;<form action="" style="display:inline" autocomplete=off>Area: <input type="hidden" name="MonthNo" value='.$monthno.'><input type="text" name="AreaNo" list="arealist" size="10"> <input type="submit" name="btnLookup" value="Lookup"></form></h3><br>';

if (allowedToOpen(2133,'1rtc')){
    echo '<button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#changeShift" style="position:sticky; top: 0; left:0;"><i class="fas fa-edit"></i> Change Shift</button><br><br>';
}
echo '<h4><b>'.comboBoxValue($link,'`0area`','AreaNo',$areano,'Area').'</b></h4>';
if (allowedToOpen(6362,'1rtc')){
    $deptincondition=' deptid=10';
} else {
    $deptincondition=' cp.BranchNo IN (SELECT BranchNo FROM attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].')';
}

$sql1='SELECT IDNo, CONCAT(cp.FullName," - ",cp.Branch) AS FullNameBranch FROM attend_30currentpositions cp JOIN 1branches b ON cp.BranchNo=b.BranchNo WHERE '.$deptincondition.' AND AreaNo='.$areano.' ORDER BY cp.Branch';
// echo $sql1; 
echo comboBox($link,$sql1,'FullNameBranch','IDNo','namesnoall');
// echo $sql1;
echo '<div class="modal fade "id="changeShift" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Change Shift</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>';
        $attendshift=date('Y-m-d',strtotime('tomorrow'));
        echo '<form action="../attendance/encodeattend.php?w=SetShiftByDept" method="POST" class="" autocomplete="off">
        <div class="modal-body">
            <div class="form-group">
                <label class="control-label">IDNo: </label>
                
                <div>
                    <input type="text" class="form-control" name="IDNo" list="namesnoall" size=7 autocomplete="off" required="true">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">Date From: </label>
                
                <div>
                <input type="date" class="form-control" name="sDate" value="'.$attendshift.'"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">Date To: </label>
                
                <div>
                <input type="date" class="form-control" name="eDate" value="'.$attendshift.'"/>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label">Shift: </label>
                
                <div>
                <font style="font-size:10pt;">
                    <input type=radio name="Shift"  value=7>  7:00 am to 4:00 pm
					&nbsp; &nbsp; <input type=radio name="Shift"  value=8>  8:00 am to 5:00 pm
					&nbsp; &nbsp; <input type=radio name="Shift"  value=9>  9:00 am to 6:00 pm
                </font>
					<input type="hidden" name="editby" value="sreport">
                    <input type="hidden" name="MonthNo" value="'.$monthno.'">
                    <input type="hidden" name="AreaNo" value="'.$areano.'">
                    <input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" name="btnChangeShift" id ="btnChangeShift" class="btn btn-success "> Change</button>
        </div>

                </form>

            
        </div>
    </div>
</div>';


$sqlbranches='SELECT DISTINCT(cp.BranchNo) AS BranchNo,cp.Branch FROM attend_30currentpositions cp JOIN 1branches b ON cp.BranchNo=b.BranchNo WHERE '.$deptincondition.' AND deptid=10 AND b.AreaNo='.$areano.' ORDER BY cp.Branch;';
$stmtbranches=$link->query($sqlbranches); $resbranches=$stmtbranches->fetchAll();

$collapsetable='border="1px solid black" style="border-collapse:collapse;width:100%;"';
foreach($resbranches AS $resbranch){
    echo '<table '.$collapsetable.'>';
    echo '<tr><th align="left" colspan='.$daysno.' style="padding:3px;">'.$resbranch['Branch'].'</th></tr>';
    echo '<tr>';
    foreach($arrdayno AS $dayno){

        $daynopad=str_pad($dayno,2,"0",STR_PAD_LEFT);

        echo '<td align="center" valign="top"><div style="border:1px solid black;background-color:#90ee90;font-size:12pt;font-weight:bold;">'.$monthname.' '.$dayno.', '.date('l', strtotime($currentyr.'-'.$monthnopad.'-'.$daynopad)).'</div>
        <table border="3px solid black;" '.$collapsetable.'><tr style="text-align:center;"><th style="background-color:#FAEAEA;"><div style="width:70px;">7</div></th><th style="background-color:#E5FFF5;"><div style="width:70px;">8</div></th><th style="background-color:#F3E5FF;"><div style="width:70px;">9</div></th></tr>';
        echo '<tr>';

        $sqlshiftmain='SELECT a.IDNo,Nickname FROM attend_2attendance a JOIN 1employees e ON a.IDNo=e.IDNo WHERE BranchNo='.$resbranch['BranchNo'].' AND DateToday="'.$currentyr.'-'.$monthnopad.'-'.$daynopad.'" AND';

        $orderby=' ORDER BY Nickname';
        //7am
        $sql7=$sqlshiftmain.' Shift=7 '.$orderby;
        $stmt7=$link->query($sql7); $res7am=$stmt7->fetchAll();

            echo '<td valign="top" style="padding:5px;background-color:#FAEAEA;">';
                foreach($res7am AS $res7){
                    echo '<div>'.$res7['Nickname'].'</div>';
                }
            echo '</td>';

        //8am
        $sql8=$sqlshiftmain.' Shift=8 '.$orderby;
        $stmt8=$link->query($sql8); $res8am=$stmt8->fetchAll();
            echo '<td valign="top" style="padding:5px;background-color:#E5FFF5;">';
                foreach($res8am AS $res8){
                    echo '<div>'.$res8['Nickname'].'</div>';
                }
            echo '</td>';

            //9am
            $sql9=$sqlshiftmain.' Shift=9 '.$orderby;
            $stmt9=$link->query($sql9); $res9am=$stmt9->fetchAll();
            echo '<td valign="top" style="padding:5px;background-color:#F3E5FF;">';
                foreach($res9am AS $res9){
                    echo '<div>'.$res9['Nickname'].'</div>';
                }
            echo '</td>';
        echo '</tr></table></td>';
    }
    echo '</tr>';
    echo '</table><br><br>';
}
echo '</div>';
?>


<script src="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/vendor/jquery/jquery.min.js"></script>
<script src="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/js/sb-admin-2.min.js"></script>