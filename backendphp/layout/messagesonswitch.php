<?php echo (isset($_GET['denied'])?'<font color="red">No permission</font>':''); ?>
<?php echo (isset($_GET['posted'])?'<font color="red">Data posted.</font>':''); ?>
<?php echo ((isset($_GET['done']) and ($_GET['done']==1))?'<font color="red">Done.</font>':''); ?>
<?php echo ((isset($_GET['done']) and ($_GET['done']==0))?'<font color="red">No data added. Pls check if posted.</font>':''); ?>