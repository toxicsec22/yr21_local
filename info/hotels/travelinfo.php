<?php
$info=$_REQUEST['info'];
switch ($info){
    case 'ferry':
        $title='Ferry Fares & Schedules';
        $text='ferry.php';
        break;
    case 'perdiem':
        $title='Per Diem Allowance & Hotel Budget';
        $text='perdiem.php';
        break;
    case 'airporttransfers':
        $title='Airport Transfers and Terminal Fees';
        $text='airporttransfers.php';
        break;
    case 'personalcar':
        $title='Use of Personal Car';
        $text='personalcar.php';
        break;
    default:
        break;
}

?>
<html><head><title><?php echo $title; ?></title>
<?php include ('header.php'); ?>
</head><body>
<div id="content">
<center>
    <h3><?php echo $title; ?></h3><br>
    <?php include ($text); ?>
<br>
<br>
</center>
</div id="content">
<?php include ('contents.php'); ?>

</body>
</html>
