<?php
$sqlcol = 'SHOW COLUMNS FROM '.$table; $result = $link->query($sqlcol); $res=$result->fetchAll();
$sqlcol=''; $colarray=array();
foreach( $res as $col  ) { $sqlcol.='`'.$col['Field']. '`,'; $colarray[]=$col['Field'];}
?>