&nbsp &nbsp<form method="post" style="display: inline" action="<?php echo $action.$txnid; ?>&exist=<?php echo ($stmtcash->rowCount()>0?1:0); ?>" enctype="multipart/form-data">
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>">
<?php
$input='';

foreach ($bills as $bill){
    $input=$input.'<input type="hidden" name="'.$bill['denomination'].'" value="'.$bill['qty'].'">';
}
echo $input.'<input type="submit" value="Save Cash Count" name="submit"></form>';
     
?>