<?php
if(isset($title) and isset($showtitle) and $showtitle==true){ echo '<title>'. $title.'</title><br><h3>'.$title.'</h3>';}
$fromdate=(!isset($_REQUEST['FromDate'])?date('Y-m-d',strtotime('first day of this month')):$_REQUEST['FromDate']); $todate=(!isset($_REQUEST['ToDate'])?date('Y-m-d'):$_REQUEST['ToDate']);
?>          <form action='<?php echo $pagetouse ?>' method='post'>
                From <input type='date' name='FromDate' value='<?php echo $fromdate; ?>'>&nbsp;
                To &nbsp;<input type='date' name='ToDate' value='<?php echo $todate; ?>'>
                &nbsp; &nbsp; <input type='submit' name='submit' value='Lookup'> 
            </form>