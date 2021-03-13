<?php
   $showenc=!isset($_POST['showenc'])?0:$_POST['showenc']; 
?><form style="display:inline" method="post" action="#">
   <input type=hidden name="showenc" value="<?php echo ($showenc==0?1:0); ?>">
    <input type="submit" name="submit" value="<?php echo ($showenc==0?'Show Encoded By and Timestamp':'Hide Encoded By and Timestamp'); ?>">
</form>&nbsp &nbsp
