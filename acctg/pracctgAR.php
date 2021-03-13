<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
        // check if allowed
        $allowed=array(728);$allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
        
        	
        $whichqry=$_GET['w'];
switch ($whichqry){


case 'SetasCharge':
    if (!allowedToOpen(728,'1rtc')) { echo 'No permission'; exit; }
	$txnid=intval($_REQUEST['TxnID']);
	
	$sql='Select PaymentType from `invty_2sale` where TxnID='.$txnid;
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	if ($result['PaymentType']==1) { $paytype=2; } else { $paytype=1;}
	$sqlupdate='UPDATE `invty_2sale` m Set PaymentType='.$paytype.', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() where m.TxnID='.$txnid;
	
        $stmt=$link->prepare($sqlupdate);
	$stmt->execute();
         $link=null; $stmt=null; 
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;

}

?>