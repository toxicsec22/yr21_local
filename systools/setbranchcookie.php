<?php
ob_start();
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';


 

if (!allowedToOpen(677,'1rtc')) { header('Location:../index.php?denied=true'); }
include_once('../switchboard/contents.php');
?>
<html>
<head>
<title>Give Permission</title>
<br>
</head>
<body>

<?php

if ($_GET['w']=='branchpermission'){
    include_once $path.'/acrossyrs/commonfunctions/renderspeciallist.php';
?>
    <form action='../systools/setbranchcookie.php?w=unsetpermission' method='POST'>
        <input type='submit' name='submitforcookie' value='UNSET Permission'><br><br>
    </form>
<form action='../systools/setbranchcookie.php?w=branchpermission' method='POST'>
    For Computer: <input type='text' name='mobileofcno'  autocomplete='off' list='mobileofc'>
    <input type='radio' name='days' value=300>One Year &nbsp &nbsp<input type='radio' name='days' value=14>Two Weeks &nbsp &nbsp<input type='radio' name='days' value=1>One Day
    <input type='submit' name='submitforcookie' value='Give Permission'><br>
    <?php genericList('SELECT BranchNo,Branch from 1branches where Active<>0 and BranchNo<95 and BranchNo<>999 UNION SELECT idmobileofc, concat(brand,\' \',model,\' \',basicdesc) FROM 1mobileofc',$link,'mobileofc','BranchNo','Branch') ?>
    CAUTION: This must be done while YOU are logged on at the REMOTE computer (you are physically there or via team viewer)!<br>
    This should be done once only, and for newly reformatted computers.    
</form><br><hr><br>
<form action='../systools/setbranchcookie.php?w=allowattendance' method='POST'>
    
    Allow attendance entry for Pasig warehouse &nbsp &nbsp
    <input type='radio' name='days' value=300>One Year &nbsp &nbsp<input type='radio' name='days' value=14>Two Weeks &nbsp &nbsp<input type='radio' name='days' value=1>One Day
    <input type='submit' name='setattendancecookie' value='Allow Attendance'><br>
    CAUTION: This must be done while YOU are logged on at the REMOTE computer (you are physically there or via team viewer)!<br>
    This should be done once only, and for newly reformatted computers.    
</form><hr>
<?php //echo json_encode($_COOKIE);
 if (!isset($_POST['submitforcookie']) and !isset($_POST['setattendancecookie'])){    goto noform;}
 
$days=$_POST['days']; 

if ($_POST['mobileofcno']<100){ 
    $sql='Select `ProgCookie` from `1branches` where `BranchNo`='. $_POST['mobileofcno'];
} else {
    $sql='Select `ProgCookie` from `1mobileofc` where `idmobileofc`='. $_POST['mobileofcno'];
} 
$stmt=$link->prepare($sql);
	$stmt->execute();
        $result=$stmt->fetch(PDO::FETCH_ASSOC);
if ($_POST['days']==1){
	$login=$_SESSION['(ak0)'];
	$link=$link;
	include_once('../backendphp/logincodes/todayat7.php'); 
	 setcookie('_comkey',$result['ProgCookie'],$today7pm,'/');
} else {
	 setcookie('_comkey',$result['ProgCookie'],time()+(3600*24*$days),'/'); //expires 14 OR 300 days
}

//echo $result['ProgCookie']; exit();
header('Location:../index.php?done=1&mobileofcno='.$_POST['mobileofcno']); 
goto noform;

} elseif ($_GET['w']=='editprotected'){
    if (!allowedToOpen(678,'1rtc')) { header('Location:../index.php?denied=true'); } 
    // $minutes=(allowedToOpen(677,'1rtc')?100:10);//ten minutes only for controller, 100 min for execom
    $_SESSION['nb4']=''.$currentyr.'-01-01';
    $_SESSION['nb4A']=''.$currentyr.'-01-01';
    
    header('Location:../index.php?done=1&w=editprotected');
} elseif ($_GET['w']=='allowattendance') {
    $days=$_POST['days']; 
    $sql='Select `ProgCookie` from `1mobileofc` where `idmobileofc`=801';
    $stmt=$link->prepare($sql); $stmt->execute(); $result=$stmt->fetch(PDO::FETCH_ASSOC);
     setcookie('_comkey2',$result['ProgCookie'],time()+(3600*24*$days),'/'); //expires in 1 OR 14 OR 300 days
//    include('../logout.php');
    header('Location:../index.php?done=1&mobileofcno='.$_POST['mobileofcno']);    
} elseif ($_GET['w']=='unsetpermission') {
     setcookie('_comkey',"",time()-600,"/");
    include('../logout.php');
    header('Location:/index.php');    
}
noform:
      $link=null; $stmt=null;
?>