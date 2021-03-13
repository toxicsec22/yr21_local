<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(date('Y').'_1rtc',0):$link;
?>

<html>
<style>

</style>
<?php $title="Details"; ?>
<title><?php echo $title; ?></title>
<?php
if(isset($_GET['annce'])){
	$txnid=intval($_REQUEST['annce']);
	$sql='Select Filename as clickhere from `hr_1announcements` where AnnounceID='.$txnid;
} else {
	$txnid=intval($_REQUEST['TxnID']);
	$sql='Select bg.* from `gen_info_00indexbg` bg where TxnID='.$txnid;
}
    $stmt=$link->query($sql);
    $result=$stmt->fetch();
  $Filename=$result['clickhere'];
?>
<body style="position: relative;
	background:url(../generalinfo/<?php echo (isset($_GET['annce'])?'anncepics':'clickherepics'); ?>/<?php echo $Filename;?>.jpg) no-repeat center center fixed;
    -moz-background-size: cover;
    -webkit-background-size: cover;
    -o-background-size: cover;
    background-size: cover;">
</body>
</html>



