<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6499,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');} 
include_once('../switchboard/contents.php');


 
$which=(!isset($_GET['which'])?'Monthly':$_GET['which']);

switch ($which){
   case 'Monthly':
         $title='BIR Monthly Returns';
         $content='pics/BIRMonthlyReturns.png';
       break;
}
  $link=null; $stmt=null;
?>
<br><br>
<h3><center><?php echo $title; ?></center></h3>
<center>
<img src='<?php echo $content; ?>'><br><br>
<br>
</center>
