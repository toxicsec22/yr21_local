<?php session_start(); ?>
<html>
<head>
<title>Request Access</title>
Request Program Access<br>
<script type="text/javascript" language="javascript" src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/disablerclick.js"></script>
</head>
<body>
<div>
<div style="float:left;">
    <br><br>Step 1. Send Request

<?php
if (strpos($_SERVER['HTTP_HOST'], '1rtc') !== false) {
    $base1rtc='1rtc';
} else {
	$base1rtc='';
}

$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/dbinit/userinit.php';

$link=!isset($link)?connect_db(date('Y').'_1rtc',0):$link;
$ip=$_SERVER['REMOTE_ADDR'];


include_once($path.'/acrossyrs/commonfunctions/hashandcrypt.php');
$command='Send Request'; $action='requestforpermission.php'; $addlcommand='<input type="submit" name="submit" value="Delete Request">';
include('../backendphp/layout/enteridandpasswd.php');
echo '<font color=blue>Inform your supervisor or dept head of this request, before this will be approved.  Pending requests may be <u>deleted</u> after 30 minutes of no action.</font><br><br>';

if (isset($_POST['submit'])){
// check if already authorized
if (isset($_COOKIE['_comkey'])){
    $stmtcomkey=$link->query('SELECT ProgCookie FROM `1branches` WHERE ProgCookie LIKE \''.$_COOKIE['_comkey'].'\'');
    $rescomkey=$stmtcomkey->fetch();
    if($stmtcomkey->rowCount()>0) {  header("Location:/".$base1rtc."index.php?nologin=0");}
}
include('../backendphp/layout/confirmwithpasswd.php');

        
	if (($_POST['submit']==$command)){
		if (($stmt->rowCount()>0) and (verify($pw,$row['uphashmayasin']))){ // and in_array($row['BranchNo'],array(0,10,15,20,22,27,30,31)) excluded not pi users
                    
                    $loginid=$row['IDNo']; $branchno=$row['BranchNo']; 
                        $sqlinsert='INSERT INTO `approvals_2progpermission` (`BranchNo`, `IPAdd`, `EncodedByNo`) VALUES ('.$branchno.', "'.$ip.'", '.$loginid.');';
			$stmt=$link->prepare($sqlinsert);$stmt->execute();
                        header('Location:/yr'.date('y').'/forms/errormsg.php?err=Sent');
		                
                } else {// no login id
			header("Location:/".$base1rtc."index.php?nologin=3");
		}

} elseif (($stmt->rowCount()>0) and (verify($pw,$row['uphashmayasin'])) and $_POST['submit']==='Delete Request'){ //delete request
    $sql='DELETE FROM `approvals_2progpermission` WHERE `EncodedByNo`='.$login; 
    $stmtsp=$link->prepare($sql); $stmtsp->execute();
    header("Location:/".$base1rtc."index.php?nologin=7");
} elseif (isset($_POST['submit']) and ($_POST['submit']==='Accept Approval')){
    goto accept;
} else {// no login id
			header("Location:/".$base1rtc."index.php?nologin=3");
		}
}
// Waiting for approvals
    $sqlsp='SELECT su.*, Branch, Nickname as EncodedBy FROM approvals_2progpermission su join `1branches` b on b.BranchNo=su.BranchNo join `1employees` e on e.IDNo=su.EncodedByNo where Approval like "0"';
    
    $stmtsp=$link->query($sqlsp);
   if ($stmtsp->rowCount()>0){
    ?>
    Step 2. Wait for approval.
    <?php
   }
    
// Unrecognized approvals 
    $sqlsp='SELECT su.* FROM approvals_2progpermission su where Approval not like "0"';
    
    $stmtsp=$link->query($sqlsp);
    $res=$stmtsp->fetchAll(PDO::FETCH_ASSOC);  
   if ($stmtsp->rowCount()>0){
    ?>
    Step 2. Wait for approval. <br><br><br><br>
    Step 3. Accept approval.
    <?php
$command='Accept Approval'; 
include('../backendphp/layout/enteridandpasswd.php');
accept:
if (isset($_POST['submit']) and ($_POST['submit']==='Accept Approval')){ 
include('../backendphp/layout/confirmwithpasswd.php'); 
		if (($stmt->rowCount()>0) and (verify($pw,$row['uphashmayasin']))){
			//from confirmwithpasswd.php
				$_SESSION['(ak0)']=$row['IDNo'];
				$_SESSION['&pos']=$row['PositionID'];
				$_SESSION['eod']=time();
				$_SESSION['LAST_ACTIVITY']=time();
				
                    $path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
                    			
                    $sqlsp='SELECT '.(allowedToOpen(351,'1rtc')?800:'su.BranchNo').' AS BranchNo, su.EncodedByNo, su.IPAdd, p.PositionID, su.Approval '
                            . ' FROM approvals_2progpermission su '
                            . ' JOIN `attend_30currentpositions` p on p.IDNo=su.EncodedByNo '
                            . ' WHERE su.EncodedByNo='.$_POST['login'].' and Approval not like "0"';
                    
                    $stmtsp=$link->query($sqlsp);
                    $res=$stmtsp->fetch(PDO::FETCH_ASSOC); 
					
                    //$ip=$_SERVER['REMOTE_ADDR'];
                    
		    $newip=$res['IPAdd'];
                     $ipnumbers=str_replace('.', '_', $res['IPAdd']);
                    $hashed=substr($res['Approval'],0,5).'_'.substr($res['Approval'],8,4).'Y73Y'.$ipnumbers.'X64X_'.substr($res['Approval'],5,4).'_'.substr($res['Approval'],20,6).substr($res['Approval'],17,3).'_'.substr($res['Approval'],12,5);//Approval has 25 characters
                    
                    header('Location:../approvals/acceptprogrampermission.php?Y='.$hashed.'&BranchNo='.$res['BranchNo'].'&EncodedByNo='.$res['EncodedByNo']);
                    
                } else {// no login id
		header("Location:/".$base1rtc."index.php?nologin=3");
		}

}// end $stmtsp rowCount >0   

   } // end $stmtsp rowCount >0
//  $stmt=null;   
?>
</div>
<div style="margin-left:50%">
<?php
	
	$stmtapp=$link->query('SELECT CONCAT(Nickname," ",Surname) AS FullName,Approval FROM `1employees` e JOIN approvals_2progpermission pp ON e.IDNo=pp.EncodedByNo ORDER BY FullName;');
    $resapp=$stmtapp->fetchAll();
	$countcb=0;
    if ($stmtapp->rowCount()>0){
		echo 'List of Permission Requests<br><br>';
		echo '<table><tr><th>Name</th><th>Status</th></tr>';
		foreach($resapp as $rows){
			$countcb++;
			echo '<tr><td>'.$countcb.'. '.$rows['FullName'].'</td><td>'.($rows['Approval']<>'0'?"<font color=maroon>Please accept!":"<font color=blue>For Approval").'</font></td></tr>';
		}
		echo '</table>';
	}
	
 $link=null; $stmt=null;  	
?></div>
</div>
</body>
</html>