<?php echo (isset($_GET['denied'])?'<b><font color="red">No permission</font></b>':''); ?>
<?php echo (isset($_GET['posted'])?'<b><font color="red">Data posted.</font></b>':''); ?>
<?php echo ((isset($_GET['done']) and ($_GET['done']==1))?'<b><font color="red">Done.</font></b>':''); ?>
<?php echo ((isset($_GET['done']) and ($_GET['done']==0))?'<b><font color="red">No data added. Pls check if posted.</font></b>':''); ?>