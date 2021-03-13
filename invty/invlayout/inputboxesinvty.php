<form method='POST' action="<?php echo $addaction ?>" enctype="multipart/form-data">
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
<?php
foreach ($columnstoedit as $field) {
    $whichlist=isset($field['list'])?$field['list']:null;
    $value=isset($field['value'])?' value='.$field['value']:'';
    echo  ($field['type']=='hidden'?'':$field['field']).'<input type='. $field['type']. ' size='. $field['size']. ' name='. $field['field']. ' '.$value.' ' . (is_null($whichlist)?'':' list='. $field['list']) . '  autocomplete="off">'.(($field['required']==false)?'':'<font color="red">*</font>').'&nbsp &nbsp';
}
?>
<input type="submit" name="submit" value="Submit">
<?php
include_once "../generalinfo/lists.inc"; 
if (isset($liststoshow)){
foreach ($liststoshow as $list){
renderlist($list);    
}
}
?>

</form>
