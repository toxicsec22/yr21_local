<?php
if((addslashes($_POST['Date']))<$_SESSION['nb4'] or date('Y', strtotime($_POST['Date']))<>$currentyr){
		echo '<title>Error!</title><h4><font color=red>The data you are trying to edit has been PROTECTED and is no longer available for editing.</font></h4></head>'
    . '<a href='.$_SERVER['HTTP_REFERER'].'>Go back</a>'; exit; 
	}
if(isset($_POST['Posted']) and $_POST['Posted']<>0){
		echo '<title>Error!</title><h4><font color=red>The data you are trying to edit is POSTED. Please unpost first.</font></h4></head>'
    . '<a href='.$_SERVER['HTTP_REFERER'].'>Go back</a>'; exit; 
	}
?>