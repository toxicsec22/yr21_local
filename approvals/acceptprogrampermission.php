<?php
if(session_id()==''){
	session_start();
}
$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db('2021_1rtc',0):$link;

if (!isset($_GET['Y'])){ goto indexpage;}

    if(strpos($_GET['Y'],'unset')>0){
        $sql='Delete from approvals_2progpermission where `EncodedByNo`='.$_SESSION['(ak0)']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        
         setcookie('_comkey',"",time()-600,"/");
        include('/logout.php');
    } else {

include_once $path.'/acrossyrs/commonfunctions/getstringinmiddle.php';
$hashed=$_GET['Y'];


$newip=getMiddleString($hashed,'Y73Y','X64X');$ipnumbers=str_replace('_', '.', $newip); $iplength=strlen($newip)+10;
$approval=substr($hashed,0,5).substr($hashed,9+$iplength,4).substr($hashed,7,3).substr($hashed,23+$iplength,6).substr($hashed,19+$iplength,3).substr($hashed,14+$iplength,3).substr($hashed,$iplength+17,2);

$sql='Select su.*,p.deptid, p.PositionID, p.IDNo, ru.ProgCookie from approvals_2progpermission su JOIN `attend_30currentpositions` p on p.IDNo=su.EncodedByNo JOIN `1_gamit`.`1rtcusers` ru ON ru.IDNo=su.EncodedByNo where su.EncodedByNo='.$_REQUEST['EncodedByNo']. ' and su.Approval="'.$approval.'"'; 
$stmt=$link->query($sql); $res=$stmt->fetch();

$progcookie=$res['ProgCookie'];

if ($stmt->rowCount()>0){
    
    
	if (in_array($res['Day'],array(0,2,3))){ //regular approval
	
            $sqlmain='SELECT IF(FIND_IN_SET('.$res['PositionID'].',AllowedPos),100,10) AS Days FROM permissions_2allprocesses WHERE ProcessID=352 AND (FIND_IN_SET('.$res['PositionID'].',AllowedPos) OR FIND_IN_SET('.$_REQUEST['EncodedByNo'].',AllowedPerID))';
			
            // first check if phone
                $path=$_SERVER['DOCUMENT_ROOT'];
                include($path.'/acrossyrs/commonfunctions/isphone.php');
                
                if($isphone){ // mobile phone
                    $sql0=$sqlmain;
                    
                    $stmt0=$link->query($sql0); $res0=$stmt0->fetch(PDO::FETCH_ASSOC);
                    if($stmt0->rowCount()>0){$days=$res0['Days']; goto cookieset;} else { goto indexpage;}
                } else {
					
			 $sql0=$sqlmain;
			
             $stmt0=$link->query($sql0); $res0=$stmt0->fetch(PDO::FETCH_ASSOC);
			// echo $stmt0->rowCount(); exit();
				if($stmt0->rowCount()==0){ //not stores and not allowed mobile			
					
					
					if($res['deptid']==10){
						include_once $path.'/acrossyrs/commonfunctions/fxngenrandpass.php';
						$progcookie=generatePassword(45);
					
						$sql='UPDATE `1branches` SET ProgCookie="'.$progcookie.'" WHERE ProgCookie="'.$res['ProgCookie'].'"';
						$stmt=$link->prepare($sql); $stmt->execute();
						
						$sql='UPDATE `1_gamit`.`1rtcusers` SET EncodedByNo='.$_REQUEST['EncodedByNo'].', TimeStamp=Now(), ProgCookieOld=ProgCookie,ProgCookie="'.$progcookie.'" WHERE IDNo='.intval($_REQUEST['EncodedByNo']).' OR ProgCookie="'.$res['ProgCookie'].'"';
					
						$stmt=$link->prepare($sql); $stmt->execute();
					
					}
					
					
				}
						
            // laptop
		$sql0='SELECT ProcessID FROM permissions_2allprocesses WHERE ProcessID=351 AND (FIND_IN_SET('.$res['PositionID'].',AllowedPos) OR FIND_IN_SET('.$_REQUEST['EncodedByNo'].',AllowedPerID))';		
		$stmt0=$link->query($sql0); $res0=$stmt0->fetch(PDO::FETCH_ASSOC);
                if($stmt0->rowCount()>0){ //laptop allowed                    
                    $days=300; goto cookieset;                    
                } else { 
                    if ($res['BranchNo'] < 800){ $days=300; goto cookieset; }  else { goto indexpage;}
                        }
                
                }
		
		cookieset:
		setcookie('_comkey',$progcookie,time()+3600*24*$days,'/'); 
                
                if(in_array($res['Day'],array(2,3)) and (!$isphone)){
                // allow attendance for warehouses
                $sql='Select `ProgCookie` from `1_gamit`.`1specindex` where `specid`='.(($res['Day']==3)?'802':'801');
                $stmt=$link->prepare($sql); $stmt->execute(); $result=$stmt->fetch(PDO::FETCH_ASSOC);
                setcookie('_comkey2',$result['ProgCookie'],time()+(3600*24*$days),'/');
                // end attend
                }
                 
	} elseif($res['Day']==-1){
		setcookie('_comkey','timeinoutcookie',time()+3600*24*300,'/'); 
	} else { // one-day approval
		$login=$_SESSION['(ak0)'];
		;
		include_once('../backendphp/logincodes/todayat7.php'); 
		setcookie('_comkey',$result['ProgCookie'],$today7pm,'/');
	}
    // delete finished request
    $sql='Delete from approvals_2progpermission where Approval="'.$approval.'"';  //echo $sql; exit();
	
    $stmt=$link->prepare($sql); $stmt->execute();
	session_destroy();
	
} else { goto indexpage;}
    }
$link=null; $stmt=null;
indexpage:

header("Location:/".((strpos($_SERVER['HTTP_HOST'], '1rtc') !== false)?"1rtc":"")."index.php");
?>