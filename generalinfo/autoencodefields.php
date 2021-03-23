<?php
include_once('../backendphp/dbinit/userinit.php'); 


$table='invty_2salesub';
$cols=getColumnNames($table,$link);

foreach ($cols as $col){
$sql='insert into gen_info.00fieldstoenter (dbtouse,tbltouse,fldname,inputtype,required,inputsize)
select 0,\''.$table.'\',\''.$col['column_name'].'\',\'text\',\'true\',20';
echo $sql;
$stmt=$link->prepare($sql);
$stmt->execute();
}
 $link=null; $stmt=null;
?>
<html>
<head>
<title>Auto Encode Fields</title>
</head>
<body>
    Done!
</body>
</html>