<?php
$per=!isset($_REQUEST['per'])?0:$_REQUEST['per']; 
$fieldname=($per==0?'Month':'Date');

$defaultdate=!isset($_REQUEST['Date'])?date('Y-m-d'):$_REQUEST['Date'];
if(!isset($_GET['Date']))
  $txndate = "m.Date = '{$defaultdate}'";
else
  $txndate = "m.Date = '{$_GET['Date']}'";

?>
  <form method="post" style="display:inline"
        action="<?php echo $file.'?per=1&Date='.(!isset($_REQUEST['Date'])?$defaultdate:$_REQUEST['Date']); ?>" enctype="multipart/form-data">
                  Choose Date:  <input type="date" name="Date" value="<?php echo $defaultdate; ?>"></input> 
                  <input type="submit" name="lookup" value="Lookup Per Day">        
  </form> &nbsp; &nbsp; &nbsp;
   
  <form method="post" style="display:inline"
        action="<?php echo $file.'?per=0&Date='.(!isset($_REQUEST['Month'])?$defaultdate:$_REQUEST['Month']); ?>" enctype="multipart/form-data">
                  Choose Month (1 - 12):  <input type="text" name="Month" value="<?php echo date('m'); ?>"></input>
                  <input type="submit" name="lookup" value="Lookup Per Month">
  </form>
  <?php echo str_repeat('<br>',2); ?><a href='<?php echo $file.'?w=AddMain'; ?>'  target=_blank>Add <?php echo $form; ?></a>
  <?php
  echo str_repeat('<br>',2); 
  
  if (!isset($_REQUEST[$fieldname])){
  $per=1;
  $formdesc=$defaultdate.'<br>';
  //goto noform;
  } else {
     
  if ($per==0){
  $formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_POST[$fieldname].'-1')).'<br>';   
  $txndate='Month(`m`.`Date`)='.$_REQUEST[$fieldname];
  } else {
  $formdesc=$_REQUEST[$fieldname].'<br>';
  $txndate='m.Date=\''.$_REQUEST[$fieldname].'\'';
  }
  if (allowedToOpen(6001,'1rtc')){
  $txndate=$txndate;
  } else {
      $txndate=$txndate.' and `m`.`Date`>Date_Add(Now(),interval -7 day)';
  }
  }  
?>