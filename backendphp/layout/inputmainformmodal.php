<?php
if (!empty($_SERVER['HTTPS'])) {
    $https='s';
  } else {
    $https='';
  }
?>
<head>
<title><?php echo $title; ?></title>
<link href="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
  <!-- Custom styles for this template-->
<link href="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/css/bootstrap.min.css" rel="stylesheet">
<!--<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">-->

</head>

<body>

<?php if (isset($title)) { echo '<h3>'.$title.'</h3>';}
?>
<?php echo (isset($formdesc)?$formdesc:'');?></i><br>
<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#Modal1"><i class="fas fa-plus-square"></i> <?php echo (isset($buttonval)?$buttonval:'Submit')?></button> 



<div class="modal fade" id="Modal1" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
<?php
echo '<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
    <h5 class="modal-title" id="modalTitle">'.$modaltitle.'</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
    </button>
</div>

<form method="'.(!isset($method)?'post':$method).'" action="'.$action.'" enctype="multipart/form-data">
<input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" /> 
<div class="modal-body">';

$nooffields=count($columnnames);$fieldsinrow=(isset($fieldsinrow)?$fieldsinrow:4); 
for ($row = 0; $row <  $nooffields; $row++) {
  $whichlist=isset($columnnames[$row]['list'])?$columnnames[$row]['list']:null;
  $value=isset($columnnames[$row]['value'])?' value='.$columnnames[$row]['value']:'';
  $readonly=isset($columnnames[$row]['readonly'])?'readonly':'';
  $checked=isset($columnnames[$row]['checked'])?' checked ':'';
  $autocomplete=isset($columnnames[$row]['autocomplete'])?' autocomplete="on" ':' autocomplete="off" ';
  $isreq=((!isset($columnnames[$row]['required'])) or ($columnnames[$row]['required']==false))?0:1;
  $required=($isreq==0)?'  >':' required='. $columnnames[$row]['required'] . ' onclick="IsEmpty('. $columnnames[$row]['field'].');">';
  
  $reqstyle=($isreq==1?' <font color="red">*</font>':'');
  $caption=isset($columnnames[$row]['caption'])?$columnnames[$row]['caption'].$reqstyle:$columnnames[$row]['field'].' '.$reqstyle.'';
  $style=isset($columnnames[$row]['style'])?(' style="'.$columnnames[$row]['style'].'"'):'';
  if($columnnames[$row]['type']=='textarea'){
      echo $columnnames[$row]['field'].'<textarea rows='.$columnnames[$row]['rows'].' cols='.$columnnames[$row]['cols'].' id='.$columnnames[$row]['formid'].' name='.$columnnames[$row]['field'].' required='. $columnnames[$row]['required'].'></textarea>';
  } else {
  
  echo  '<div class="form-group">
  <label class="control-label">'.($columnnames[$row]['type']=='hidden'?'':$caption).'</label>  <div><input class="form-control" '.(isset($columnnames[$row]['placeholder'])?'placeholder="'.$columnnames[$row]['placeholder'].'"':'').' '.(isset($columnnames[$row]['maxlength'])?'maxlength="'.$columnnames[$row]['maxlength'].'"':'').' '.(isset($columnnames[$row]['id'])?'id="'.$columnnames[$row]['id'].'"':'').' '.(isset($columnnames[$row]['input-mask'])?'input-mask="'.$columnnames[$row]['input-mask'].'"':'').' type='. $columnnames[$row]['type']. ' size='. $columnnames[$row]['size'].$style. ' name='. $columnnames[$row]['field']. $value. $checked . (is_null($whichlist)?'':' list='. $columnnames[$row]['list']). ' '.$readonly.' ' .$autocomplete.$required.'</div></div>';
  }
}

echo '</div>

<div class="modal-footer">
    <button type="submit" name="btnModal1" id ="btnModal1" class="btn btn-primary "> '.$buttonval.'</button>
</div>

</form>

    
</div>
</div>';

?>
</div>

<script src="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/vendor/jquery/jquery.min.js"></script>
<script src="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="http<?php echo $https;?>://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/bootstrapSBADMIN2/js/sb-admin-2.min.js"></script>