<br>
<form method="POST" action="<?php echo $addsub ?>" enctype="multipart/form-data" style="font-family:sans-serif;">
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" />
<?php
if (isset($lookup)){echo $lookup;}
$nooffields=count($columnnames); $fieldsinrow=(isset($fieldsinrow)?$fieldsinrow:4);
if ($editok){
for ($row = 0; $row <  $nooffields; $row++) {
    $whichlist=isset($columnnames[$row]['list'])?$columnnames[$row]['list']:null;
    $value=isset($columnnames[$row]['value'])?' value='.$columnnames[$row]['value']:' ';
    $required=((!isset($columnnames[$row]['required'])) or ($columnnames[$row]['required']==false))?'  autocomplete="off">':' required='. $columnnames[$row]['required'] . '  autocomplete="off"><font color="red">*</font>';
    $caption=isset($columnnames[$row]['caption'])?$columnnames[$row]['caption']:$columnnames[$row]['field'];
    $onchange=isset($columnnames[$row]['onchange'])?' onchange="'. $columnnames[$row]['onchange'].'"':'';
    echo  ($columnnames[$row]['type']=='hidden'?'':$caption).'<input type='. $columnnames[$row]['type']. ' size='. $columnnames[$row]['size']
    . ' name='. $columnnames[$row]['field']. $value . (is_null($whichlist)?'':' list='. $columnnames[$row]['list']). ' '
    .(isset($columnnames[$row]['autofocus'])?' autofocus ':'').$required.$onchange.(($row+1)%$fieldsinrow==0?'<br><br>':' &nbsp &nbsp');
}
?><input type="submit" name="submit" value="Add"><br><br>
</form>
<?php

if (isset($addlsubmit)) { echo $addlsubmit;}
} // end if editsub=true
if (!empty($sub)) { echo $sub.'<br>';}
echo isset($rowcount)?$rowcount." record/s shown &nbsp &nbsp":"";
echo isset($total)?$total:'';
?>
