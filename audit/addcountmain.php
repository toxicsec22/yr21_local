<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(640,641,642,643);$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=true; include_once('../switchboard/contents.php');
 


    $method='POST';


$whichqry=$_GET['w'];

switch ($whichqry){
    case 'InvCount':
$title='Add New Inventory Count';
$columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'Remarks', 'type'=>'text','size'=>20,'required'=>false)
                    );
    
    $action='praddaudit.php?w=NewInvCount';
    $liststoshow=array('branches');
     include('../backendphp/layout/inputmainform.php');
    break;
case 'CashCount':
$title='Add New Cash Count';
include('../backendphp/layout/clickontabletoedithead.php');
// echo '<br><h3>'.$title.'</h3>';

include('../forms/calcbills.php');
?>
<form method='post' action='prcashtools.php?w=NewCashCount' enctype='multipart/form-data'>
    Date <input type='date' size=20 name ='DateCounted' required=true value='<?php echo date('Y-m-d'); ?>'>
    Remarks <input type='text' size=40 name ='Remarks'  autocomplete=off> NoOfUsedReceipts <input type='text' size=5 name ='NoOfUsedReceipts'  autocomplete=off>
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>">
<?php
$input='';
foreach ($bills as $bill){
    $input=$input.'<input type="hidden" name="'.$bill['denomination'].'" value="'.$bill['qty'].'">';
}
echo $input;
?>
<input type='submit' value='Add This Cash Count' name='submit'>
</form>
<?php
break;

case 'Weigh':
$title='Weigh Refrigerants';
include('../backendphp/layout/clickontabletoedithead.php');
echo '<br><h3>'.$title.'</h3>';
?>
<form method='post' action='prcashtools.php?w=NewWeighRef' enctype='multipart/form-data'>
    Date <input type='date' size=20 name ='Date' required=true value='<?php echo date('Y-m-d'); ?>'>
    Remarks <input type='text' size=40 name ='Remarks'  autocomplete=off>
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>">

<input type='submit' value='Add' name='submit'>
</form>
<?php
break;

case 'Tools': 
$title='New Tools Audit';
include('../backendphp/layout/clickontabletoedithead.php');
echo '<br><h3>'.$title.'</h3>';

?>
<form method='post' action='prcashtools.php?w=NewToolsCount' enctype='multipart/form-data'>
    Date of New Audit<input type='date' size=20 name ='Date' required=true value='<?php echo date('Y-m-d'); ?>'>
    Remarks <input type='text' size=40 name ='Remarks'  autocomplete=off>
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>">

<input type='submit' value='Add' name='submit'>
</form>
<?php
break;

case 'Adjust': 
$title='New Adjustment';
include('../backendphp/layout/clickontabletoedithead.php');
echo '<br><h3>'.$title.'</h3>';

?>
<form method='post' action='pradjust.php?w=NewAdjust' enctype='multipart/form-data'>
    Date<input type='date' size=20 name ='Date' required=true value='<?php echo date('Y-m-d'); ?>'>
    Adjustment Number <input type='text' size=40 name ='AdjNo'  autocomplete=off required=true>
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>">

<input type='submit' value='Add' name='submit'>
</form>
<?php
break;

}
  $link=null; $stmt=null;
    ?>
