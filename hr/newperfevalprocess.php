<?php
ob_start();
$path=$_SERVER['DOCUMENT_ROOT']; 
if(session_id()==''){
	session_start();
}

        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if(!isset($_SESSION['oss'])){	
        include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	} else {
		$currentyr=date('Y');
	}
		include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
 
$which=$_GET['w'];
if(isset($_REQUEST['TxnSubId'])){
    $txnsubid=intval($_REQUEST['TxnSubId']);
}
if(isset($_REQUEST['TxnID'])){
    $txnid=intval($_REQUEST['TxnID']);
}

switch ($which){
    case 'SelfScore':
        $num2 = 0;
		$num = $_POST['selfnum'];

        while ($num2 < $num) {
            $selfscore = $_POST['SelfScore'.$num2];
			$txnsubid = $_POST['TxnSubId'.$num2];
			
            if($selfscore==''){
                $selfscoresql="SelfScore = NULL";
            } else {
                $selfscoresql="SelfScore = '".$selfscore."'";
            }
            
            $sql = "UPDATE hr_82perfevalsub SET ".$selfscoresql." WHERE TxnSubId =  ".$txnsubid."";
			$stmt= $link->prepare($sql);
			$stmt->execute();


            $num2++;
        }
    
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;

    case 'FCSelfScore':
        $num2 = 0;
		$num = $_POST['selfnum'];

        while ($num2 < $num) {
            $selfscore = $_POST['SelfScore'.$num2];
			$txnsubid = $_POST['TxnSubId'.$num2];
			
            if($selfscore==''){
                $selfscoresql="SelfScore = NULL";
            } else {
                $selfscoresql="SelfScore = '".$selfscore."'";
            }
            
            $sql = "UPDATE hr_82perfevalsub SET ".$selfscoresql." WHERE TxnSubId =  ".$txnsubid."";
			$stmt= $link->prepare($sql);
			$stmt->execute();


            $num2++;
        }
    
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
    
    
    case 'SelfOverAllComment':
        $sql = "UPDATE hr_82perfevalmain SET EComment=\"".addslashes($_POST['EComment'])."\" WHERE TxnID =  ".$txnid." AND IDNo=".$_SESSION['(ak0)']." AND EStat=0";
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
    case 'SelfComplete':
        $sql = "UPDATE hr_82perfevalmain SET EStat=1,ECommentTS=NOW() WHERE TxnID =  ".$txnid." AND IDNo=".$_SESSION['(ak0)']." AND EStat=0";
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;

    case 'SetIncSuper':
        $sql = "UPDATE hr_82perfevalmain SET EStat=0 WHERE TxnID =  ".$txnid." AND SIDNo=".$_SESSION['(ak0)']." AND EStat=1";
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;

    case 'SuperScore':
        $num2 = 0;
		$num = $_POST['supernum'];

        while ($num2 < $num) {
            $superscore = $_POST['SuperScore'.$num2];
			$txnsubid = $_POST['TxnSubId'.$num2];
			
            if($superscore==''){
                $superscoresql="SuperScore = NULL";
            } else {
                $superscoresql="SuperScore = '".$superscore."'";
            }
            
            $sql = "UPDATE hr_82perfevalsub SET ".$superscoresql." WHERE TxnSubId =  ".$txnsubid."";
           
			$stmt= $link->prepare($sql);
			$stmt->execute();


            $num2++;
        }
    
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;

    case 'FCSuperScore':
        $num2 = 0;
		$num = $_POST['supernum'];

        while ($num2 < $num) {
            $superscore = $_POST['SuperScore'.$num2];
			$txnsubid = $_POST['TxnSubId'.$num2];
			
            if($superscore==''){
                $superscoresql="SuperScore = NULL";
            } else {
                $superscoresql="SuperScore = '".$superscore."'";
            }
            
            $sql = "UPDATE hr_82perfevalsub SET ".$superscoresql." WHERE TxnSubId =  ".$txnsubid."";
           
			$stmt= $link->prepare($sql);
			$stmt->execute();


            $num2++;
        }
    
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;

    case 'SuperOverAllComment':
        $sql = "UPDATE hr_82perfevalmain SET Recommendation=".$_POST['Recommendation'].",SComment=\"".addslashes($_POST['SComment'])."\" WHERE TxnID =  ".$txnid." AND EStat=1 AND SStat=0";
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
    case 'SuperComplete':
        $sql = "UPDATE hr_82perfevalmain SET SStat=1,SCommentTS=NOW() WHERE TxnID =  ".$txnid." AND EStat=1 AND SStat=0";
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;

	

    
    case 'SetIncDHead':
        $sql = "UPDATE hr_82perfevalmain SET SStat=0 WHERE TxnID =  ".$txnid." AND EStat=1 AND SStat=1 AND DStat=0";
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;




	case 'DeptHeadComment':
        $sql = "UPDATE hr_82perfevalmain SET DStat=1,DComment=\"".addslashes($_POST['DComment'])."\",DCommentTS=NOW() WHERE TxnID =  ".$txnid." AND EStat=1 AND SStat=1 AND Ack=0";
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
	case 'Acknowledge':
        $sql = "UPDATE hr_82perfevalmain SET Ack=".$_POST['Acknowledge'].",EmpRemarks=\"".addslashes($_POST['EmpRemarks'])."\", AckTS=NOW() WHERE TxnID =  ".$txnid." AND EStat=1 AND SStat=1 AND Ack=0";
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;

    case 'SuperScoreMonthly':
     
        $num2 = 0;
		$num = $_POST['supernum'];

        while ($num2 < $num) {
            $superscore = $_POST['SuperScore'.$num2];
			$txnsubid = $_POST['TxnSubId'.$num2];
			
            if($superscore==''){
                $superscoresql="SuperScore = NULL";
            } else {
                $superscoresql="SuperScore = '".$superscore."'";
            }
            
            $sql = "UPDATE hr_82perfevalmonthlysub SET ".$superscoresql." WHERE TxnSubId =  ".$txnsubid."";
       
			$stmt= $link->prepare($sql);
			$stmt->execute();


            $num2++;
        }

        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	

    case 'PostMonthly':
        $sql = "UPDATE hr_82perfevalmonthlymain SET Posted=1,PostedTS=NOW() WHERE TxnID =  ".$txnid." AND SIDNo=".$_SESSION['(ak0)']."";
        $stmt= $link->prepare($sql);  $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);

    break;
}
 
?>