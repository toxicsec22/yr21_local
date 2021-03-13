<?php
   $show=!isset($_POST['show'])?0:$_POST['show'];
?><form style="display:inline" method="post" action="#">
   <input type=hidden name="show" value="<?php echo ($show==0?1:0); ?>">
    <input type="submit" name="submit" value="<?php echo ($show==0?'Show All Branches':'Per Branch'); ?>">
    <?php echo (isset($addlfield)?$addlfield:''); ?>
</form>&nbsp &nbsp
