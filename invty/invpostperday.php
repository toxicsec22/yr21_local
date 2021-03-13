<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
?>
<html>
<head>
<title>Post per Day</title>
<head>
<?php
include_once ('../backendphp/functions/postperdate.php');
include_once('../switchboard/contents.php');
if (!allowedToOpen(716,'1rtc')) { echo 'No permission'; exit;}
?><div style="text-align: center">
<h3>Transactions to be posted per day:</h3><br><br>
Cash and Charge Sales<br>
Customer Returns<br>
Interbranch Transfers<br>
Store Used<br>
Repack<br>
Materials Receiving Reports<br>
Purchase Returns<br>
Adjustments<br><br><br><br>
</head>
<body>
    <form method="POST" action="#" enctype="multipart/form-data">
    For Date <input type='date' name='date' value="<?php echo date('Y-m-d',strtotime("-1 days")); ?>"></input>
    <input type="submit" name="post" value="Post and Protect">
    </form></div>
    <?php
if (!isset($_POST['date'])){
	goto noform; 
    } else {
        $date=$_POST['date'];
        
    
   $posttables=array('invty_2sale','invty_2mrr','invty_2pr','invty_3order','invty_4adjust');
   foreach ($posttables as $table){
    postperdate($link,$table,$date,false);
   }
   postperdate($link,'invty_2transfer',$date,'Out');
   postperdate($link,'invty_2transfer',$date,'In');
   
   }
   header('Location:/'.$url_folder.'/index.php?posted=true');
noform:
     $link=null; $stmt=null;
?>