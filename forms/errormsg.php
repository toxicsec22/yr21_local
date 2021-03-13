<?php
$whicherror=$_REQUEST['err'];
switch ($whicherror){
    case 'Posted':
        $msg='The data you are trying to edit is POSTED. Please unpost first.';
        break;
    case 'Closed':
        $msg='The data you are trying to edit has been PROTECTED and is no longer available for editing.';
        break;
    case 'Permission':
        $msg='No permission.';
        break;
    case 'Password':
        $msg='Incorrect password';
        break;
    case 'Sent':
        $title='Sent';
        $msg='Report/request has been sent.  </b><br><br><a href=/index.php>Go to Login</a><b>';
        break;
    case 'ProvMainofDP':
        $title='Cannot delete';
        $msg='Delete all entries in subform before deleting main.';
        break;
}
$goback=$_SERVER['HTTP_REFERER'];
?>
<html>
    <head><title><?php echo (!isset($title)?'Error!':$title);?></title><h4><font color=red><?php echo $msg;?></font></h4></head><body><a href='<?php echo $goback; ?>'>Go back</a></body>
</html>