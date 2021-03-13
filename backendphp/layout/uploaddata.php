<html>
<head>
<title><?php echo (isset($title)?$title:''); ?></title>
</head>
<body>
    <h4><?php echo (isset($title)?$title:''); ?></h4><br><br><div style="margin: 0px 0px 0px 100px;">
        The file must be saved as a csv file.<br><br>
        The first row must contain the column names, written exactly as follows:<br><br>
        <?php echo (isset($specific_instruct)?$specific_instruct:''); ?></div><br><br>
<form action="#" method="post" enctype="multipart/form-data">
<input type="file" name="userfile" accept="csv/text"><input type="submit" name="submit" value="Import"><br>
<?php
//$path=$_SERVER['DOCUMENT_ROOT'];
include 'uploaddatanoheader.php';
?>
</body>
</html>

