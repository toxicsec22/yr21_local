<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        if (!allowedToOpen(555,'1rtc')) { echo 'No permission'; exit; }  
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        
        $whichqry=$_GET['w'];

switch ($whichqry){
case 'HoldHistory':
	if (!allowedToOpen(5561,'1rtc')) { echo 'No permission'; exit; }  
        switch($_POST['Submit']){ case 'Allow Temporarily': $hold=2; break; case 'Reset Hold': $hold=0; break; case 'Over Limit': $hold=4; break; default: $hold=1; }
	$sql='INSERT INTO `comments_5clientsonhold` SET ClientNo=\''.$_POST['ClientNo'].'\', Hold='.$hold.', Reason=\''.addslashes($_POST['Reason']).'\', Remarks=\''.addslashes($_POST['Remarks']).'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	 // echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;
case 'CommentsPerClient':    
	$arcomment=(allowedToOpen(5551,'1rtc'))?-1:0;
	$sql='INSERT INTO `comments_5commentsonclients` SET  ContactDate=\''.$_POST['ContactDate'].'\', ClientNo=\''.$_POST['ClientNo'].'\',Comment=\''.addslashes($_POST['Comment']).'\', ARComment='.$arcomment.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;
default:
		 header('Location:/'.$url_folder.'/index.php');
	

        }

?>