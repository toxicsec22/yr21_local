<?php
$path=$_SERVER['DOCUMENT_ROOT'];
if (allowedToOpen(array(6711,67111),'1rtc')){
	    echo '<br><form action="../generalinfo/employeeinfo.php?calledfrom=1" method="post"><input type=submit name="show" value="Show Current">&nbsp; &nbsp;<input type=submit name="show" value="Show Resigned This Year">&nbsp; &nbsp;<input type=submit name="show" value="Show Resigned But With System Access">&nbsp; &nbsp;<input type=submit name="show" value="Show Not Resigned And No System Access">&nbsp; &nbsp;<input type=submit name="show" value="Show Prelim Resign">
            </form>';
		}
	if (allowedToOpen(6714,'1rtc')){
echo '<br><form method="get" action="../attendance/empstatustagging.php">';
echo '<input type="submit" name="f" value="Probationary"/> ';
echo '<input type="submit" name="f" value="Regular"/> ';
echo '<input type="submit" name="f" value="Resigned with Clearance"/> ';
echo '<input type="submit" name="f" value="Resigned No Clearance"/>'.'<br/>';
echo '</form><br/>';
	}
?>