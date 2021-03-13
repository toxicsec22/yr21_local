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
 

$which=(!isset($_REQUEST['w'])?'EditScores':$_REQUEST['w']);
$txnid=intval($_REQUEST['TxnID']);
if (isset($_REQUEST['who'])){
$who=$_REQUEST['who']; } 


switch ($which){
    case 'EditScores':
		//multiple updates
		$newavescore = 0;	
		$num2 = 0;
		$num = $_POST['num'];
	
		while ($num2 < $num) {
			$score = 'score'.$num2;
			$score = $_POST[$score];
			
			$sgid = 'sgid'.$num2;
			$sgid = $_POST[$sgid];
			
			$psid = 'psid'.$num2;
			$psid = $_POST[$psid];
			
			//weight
			$weight = 'weight'.$num2;
			$weight = $_POST[$weight];
			
			$sql = "UPDATE hr_2perfevalsub SET SelfScore = ".$score." WHERE TxnSubId =  ".$sgid."";
			$stmt= $link->prepare($sql);
			
			//Encoded by
			$encodedby = $_SESSION['(ak0)'];
			
			$idno = $_SESSION['(ak0)'];
		
			$stmt->execute();
			
			$weight = ($weight/100);
			$avescore = $score * $weight;
			$newavescore = $newavescore + $avescore;
			
			$num2++;
			
		}
		
		// $sql2='UPDATE `hr_2perfevalmain` SET SelfEval = '.number_format($newavescore,2).', `SelfScoreTS`=Now() WHERE TxnID='.$txnid; 
		$sql2='UPDATE `hr_2perfevalmain` SET SelfEval = '.number_format($newavescore,2).'  WHERE TxnID='.$txnid; 
		
        $stmt2=$link->prepare($sql2); $stmt2->execute();
		
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
		
		case 'EditScoresSuper':
		
		$newavescore = 0;	
		$num2 = 0;
		$num = $_POST['num'];
		//update multiple
		while ($num2 < $num) {
			$score = 'score2'.$num2;
			$score = $_POST[$score];
			
			$evaluator = 'evaluator'.$num2;
			$evaluator = $_POST[$evaluator];
			
			$sgid = 'sgid'.$num2;
			$sgid = $_POST[$sgid];
			
			$psid = 'psid'.$num2;
			$psid = $_POST[$psid];
			
			//weight
			$weight = 'weight'.$num2;
			$weight = $_POST[$weight];
			
			$sql = "UPDATE hr_2perfevalsub SET SuperScore = ".$score." WHERE TxnSubId =  ".$sgid."";
			$stmt= $link->prepare($sql);
			
			//Encoded by
			$encodedby = $_SESSION['(ak0)'];
			
			$idno = $_SESSION['(ak0)'];
		
			$stmt->execute();
			
			$weight = ($weight/100);
			$avescore = $score * $weight;
			$newavescore = $newavescore + $avescore;
			
			$num2++;
			
		}
		
		$sql2='UPDATE `hr_2perfevalmain` SET SupervisorEval = '.number_format($newavescore,2).' WHERE TxnID='.$txnid; 
		
        $stmt2=$link->prepare($sql2); $stmt2->execute();
		
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
	
	
   case 'InsertScore':
		
		$num2 = 0;
		$num = $_POST['num'];
		$txnid = $_POST['txnid'];
		$perfevalid = $_POST['perfevalid'];
		$ave = 0;
		$newavescore = 0;
		
		while ($num2 < $num) {
			$sql = "INSERT INTO hr_2perfevalsub (PSID, TxnID, SelfScore) VALUES (?,?,?)"; //echo $sql;
			$stmt= $link->prepare($sql);
			
			//TxnID
			$psid = 'psid'.$num2;
			$psid = $_POST[$psid];
			
			//PerfEvalID
			$perfevalid = 'perfevalid'.$num2;
			$perfevalid = $_POST[$perfevalid];
			
			//Score
			$score = 'score'.$num2;
			$score = $_POST[$score];
			
			//weight
			$weight = 'weight'.$num2;
			$weight = $_POST[$weight];
			
			//Encoded by
			$encodedby = $_SESSION['(ak0)'];
			
			$idno = $_SESSION['(ak0)'];
			
			$stmt->execute([$psid, $perfevalid, $score]);
			
			$weight = ($weight/100);
			$avescore = $score * $weight;
			$newavescore = $newavescore + $avescore;
			$num2++;
			
		}
		
		$sql2='UPDATE `hr_2perfevalmain` SET SelfEval = '.number_format($newavescore,2).' WHERE TxnID='.$txnid;
		
        $stmt2=$link->prepare($sql2); $stmt2->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
	
	/*
		case 'EditScoresOthers':
		
		$newavescore = 0;	
		$num2 = 0;
		$num = $_POST['num'];
	
		while ($num2 < $num) { 
                    
			$score = 'score'.$num2;
			$score = $_POST[$score];
			
//			$evaluator = 'evaluator'.$num2;
//			$evaluator = $_POST[$evaluator];
			
			$sgid = 'sgid'.$num2;
			$sgid = $_POST[$sgid];
			
//			$psid = 'psid'.$num2;
//			$psid = $_POST[$psid];
			
			//weight
//			$weight = 'weight'.$num2;
//			$weight = $_POST[$weight];
			
			$sql = "UPDATE hr_2perfevalsub SET SuperScore = ".$score.", EvaluatorIDNo=".$_SESSION['(ak0)'].", `Timestamp`=Now()  WHERE TxnSubId =  ".$sgid.""; //echo $sql;// exit;
			$stmt= $link->prepare($sql);
			$stmt->execute();
			
			$num2++;
			
		}
		
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
	
                case 'SetAsCompletedOthers':
		
        $sql='UPDATE `hr_2perfevalsub` SET SelfScore=-1, `Timestamp`=Now()  WHERE EvaluatorIDNo = '.$_SESSION['(ak0)'].' AND TxnID='.$_GET['TxnID'].'';
		
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    
                case 'UnsetAsCompletedOthers':
		
        $sql='UPDATE `hr_2perfevalsub` SET SelfScore=NULL, `Timestamp`=Now()  WHERE EvaluatorIDNo = '.$_SESSION['(ak0)'].' AND TxnID='.$_REQUEST['TxnID'].'';
		
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
		*/
		case 'Remarks':
		
		
        $sql='UPDATE `hr_2perfevalmain` SET '.$who.'Remarks=\''.addslashes($_POST[$who.'Remarks']).'\', '.$who.'CompletedTS=Now() WHERE '.$who.'Completed=0 AND TxnID='.$txnid; 
		// if (allowedToOpen(2201,'1rtc')) { echo $sql; exit;} 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
		
		case 'Completed':
        $sql='UPDATE `hr_2perfevalmain` SET '.$who.'Completed=1, '.$who.'CompletedTS=Now() WHERE '.$who.'Completed=0 AND TxnID='.$txnid; //echo $sql;break;
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
		break;
		
    case 'Improve': 
        $sql='UPDATE `hr_2perfevalmain` SET ToImprove=\''.addslashes($_POST['Improve']).'\' WHERE SupervisorIDNo='.$_SESSION['(ak0)'].' AND SuperCompleted=0 AND TxnID='.$txnid; 
        // echo $sql;break;
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Develop': 
        $sql='UPDATE `hr_2perfevalmain` SET ToDevelop=\''.addslashes($_POST['Develop']).'\' WHERE SuperCompleted=0 AND TxnID='.$txnid; //echo $sql;break;
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
		
	case 'DeptHeadComment': 
	$sql='UPDATE `hr_2perfevalmain` SET DeptHeadComment=\''.addslashes($_POST['DeptHeadComment']).'\' WHERE DeptHeadIDNo='.$_SESSION['(ak0)'].' AND HRStatus=0 AND TxnID='.$txnid; //echo $sql;
	// echo $sql; break;
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;
	
	case 'Recommendation': 
	$sql='UPDATE `hr_2perfevalmain` SET Recommendation=\''.addslashes($_POST['Recommendation']).'\' WHERE DeptHeadIDNo='.$_SESSION['(ak0)'].' AND HRStatus=0 AND TxnID='.$txnid; //echo $sql;
	// echo $sql; break;
	$stmt=$link->prepare($sql); $stmt->execute();
	
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;
	
    case 'DeptHeadConfirm':
        $sql='UPDATE `hr_2perfevalmain` SET DeptHeadConfirm=1, DeptHeadConfirmTS=Now() WHERE DeptHeadConfirm=0 AND TxnID='.$txnid; //echo $sql;break;
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
	 break;
	 
	case 'EmpResponse': 
        $sql='UPDATE `hr_2perfevalmain` SET EmpResponse=\''.addslashes($_POST['EmpResponse']).'\', `EmpResponseEncByIDNo`='.$_SESSION['(ak0)'].', EmpResponseTS=Now() WHERE IDNo='.$_SESSION['(ak0)'].' AND HRStatus=0 AND TxnID='.$txnid; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
		
	case 'SetIncompleteSuper': 
        $sql='UPDATE `hr_2perfevalmain` SET SelfCompleted=0 WHERE TxnID='.$txnid; //echo $sql;break;
        $stmt=$link->prepare($sql); $stmt->execute();
		header("Location:perfeval.php?w=ForEval");
    break;
	 
	case 'SetIncompleteDeptHead': 
        $sql='UPDATE `hr_2perfevalmain` SET SuperCompleted=0 WHERE TxnID='.$txnid; //echo $sql;break;
        $stmt=$link->prepare($sql); $stmt->execute();
		header("Location:perfeval.php?w=ForEval");
     break;
	 
	case 'SetIncompleteHR': 
        $sql='UPDATE `hr_2perfevalmain` SET DeptHeadConfirm=0, EmpResponse=0, `EmpResponseEncByIDNo`=NULL, EmpResponseTS=NULL, HRStatusTS=NULL, HRStatus=0 WHERE TxnID='.$txnid; 
        $stmt=$link->prepare($sql); $stmt->execute();
		header("Location:perfeval.php");
     break;
	
    case 'EmpRemarks': 
        $sql='UPDATE `hr_2perfevalmain` SET EmpRemarks=\''.addslashes($_POST['EmpRemarks']).'\' WHERE IDNo='.$_SESSION['(ak0)'].' AND HRStatus=0 AND TxnID='.$txnid; //echo $sql;break;
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
	
    case 'HRStatus':
        $sql='UPDATE `hr_2perfevalmain` SET HRStatus=1, HRStatusTS=Now(), HREncodedByNo='.$_SESSION['(ak0)'].' WHERE HRStatus=0 AND EmpResponse<>0 AND TxnID='.$txnid; 
		// echo $sql; break;
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
	 break;
	 
/*	case 'DeleteEvaluatorMulti':
		
	$sql='DELETE FROM `hr_2perfevalsub` WHERE EvaluatorIDNo = '.$_GET['EvaluatorIDNo'].' AND TxnID='.$_GET['TxnID'].'';
	
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:".$_SERVER['HTTP_REFERER']);
	break;*/
}
 
?>