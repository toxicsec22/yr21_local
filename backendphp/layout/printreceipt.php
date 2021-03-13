<html>
<head>
<title><?php echo $title; ?></title>
</head>
<body>
<?php str_repeat('<br>',$spacetop); ?>
<?php  echo $main; ?><br>
<?php  echo $sub.'<br>';
echo isset($total)?$total:'';
?><br>

<?php
if (isset($sqlsum)) { echo $sqlsum;}
?>
</body>
</html>