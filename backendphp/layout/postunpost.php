<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

 
$postvalue=$_REQUEST['Post'];
$postfield=$_REQUEST['PostField'];
$table=$_REQUEST['Table'];
$txnid=intval($_REQUEST['TxnID']);
$txnidname=$_REQUEST['TxnIDName'];
$which=strstr($_REQUEST['Table'],'_',true);
$txntype=$_REQUEST['txntype'];
$datefield=$_REQUEST['DateField'];

    
    switch($txntype){
        case 7:
        case 'In':
            $dateout='DateOUT';
            break;
        
		case 'Order':
		
		$sqlm='select Posted from invty_3order WHERE TxnID='.$txnid.';'; 
		$stmtm=$link->query($sqlm);  $resultm=$stmtm->fetch();
		
		if($resultm['Posted']==0){
			//cannot post if pricelevels <= unitcost
			 $sql='select TxnSubID,ItemCode from invty_3ordersub WHERE TxnID='.$txnid.' AND (PriceLevel1<=UnitCost OR PriceLevel2<=UnitCost OR PriceLevel3<=UnitCost OR PriceLevel4<=UnitCost OR PriceLevel5<=UnitCost);'; 
			$stmt=$link->query($sql);  $result=$stmt->fetch();
			if ($stmt->rowCount()>0){
				echo '<font color="red">Error. Price level is less than or equal to unit cost.</font><br> ItemCode: '.$result['ItemCode'];
				exit();
			}
		}
		  
		break;
		
    }
	
   
    $sql='Select `'.$datefield.'`, '.(isset($dateout)?'`'.$dateout.'`,':'').' `'.$postfield.'` from `'.$table.'` where '.$txnidname.'='.$txnid;

    if ($_SESSION['(ak0)']==1002) { echo 'Txntype: '.$txntype.'<br><br>'.$sql;  }
    $stmt=$link->query($sql); $result=$stmt->fetch();
    
//POST
    if ($result[$postfield]==0 and $postvalue<>0){ 
        if ($_SESSION['(ak0)']==1002) { echo 'goes here POST';  }
        $sql='Update `'.$table.'` SET `'.$postfield.'`=1, `'.$postfield.'ByNo`='.$_SESSION['(ak0)'].' where '.$txnidname.'='.$txnid;
		if($txntype=='LoanType'){
			$stmt=$link->prepare($sql); $stmt->execute();
			$sql='Update `'.$table.'` SET `'.$postfield.'TS`=NOW() where '.$txnidname.'='.$txnid;
		}
        if ($_SESSION['(ak0)']==1002) { echo 'Txntype: '.$txntype.'<br><br>'.$sql;  }
        $stmt=$link->prepare($sql);
        $stmt->execute();   

    // additional for check vouchers
    if (in_array($table,array('acctg_2cvmain', 'acctg_4futurecvmain'))){
        if($postfield=='APVPosted'){
            $sql='SELECT CVNo FROM acctg_2cvmain WHERE CreditAccountID<>403 AND (CreditAccountID NOT IN (SELECT AccountID FROM banktxns_1maintaining)) AND `'.$txnidname.'`='.$txnid;
            $stmt=$link->query($sql); $result=$stmt->fetch();
            if($stmt->rowCount()==0) { goto nopermission;}
            $sql='UPDATE acctg_2cvmain SET Posted=1 WHERE CreditAccountID<>403 AND (CreditAccountID NOT IN (SELECT AccountID FROM banktxns_1maintaining)) AND `'.$txnidname.'`='.$txnid;  
            $stmt=$link->prepare($sql); $stmt->execute();
    }
    }

    } else {
        //UNPOST 
        if ($_SESSION['(ak0)']==1002) { echo 'goes here ';  }
        $closedate=(allowedToOpen(250,'1rtc'))?$_SESSION['nb4A']:$_SESSION['nb4']; 
        $datecondition=((($txntype==7 or $txntype=='In') and (is_null($result['DateIN']) or empty($result['DateIN'])))?($result[$dateout]>$closedate):(($result[$datefield])>($closedate))); 
        if ($_SESSION['(ak0)']==1002) {  echo $closedate; echo $result[$datefield].'<br>'.$datecondition;  }
		
        switch ($table) {
        case 'invty_3branchrequest':
                 $txnid=$txnid.' and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
                 echo "<font color='red'>Unposting can be done by the person who entered it.</font>";
            break;
        case 'invty_3extrequest':
            if (!allowedToOpen(400,'1rtc')){ goto nopermission; }
            break;

        case 'invty_2transfer':
        case 'invty_2sale':
            if (!allowedToOpen(array(401,40101,40102,314),'1rtc')){ goto nopermission; }
            if($_POST['Table']=='invty_2transfer'){
                $tobranhnocol=',ToBranchNo';
                $wheretobranchno=1;
            } else {
                $tobranhnocol='';
                $wheretobranchno=0;
            }
            $sqla='SELECT '.$datefield.',`BranchNo`'.$tobranhnocol.' FROM '.$_POST['Table'].' WHERE TxnID='.intval($_POST['TxnID']);
           
            $stmta=$link->query($sqla); $resulta=$stmta->fetch();

            if(allowedToOpen(40101,'1rtc')){ //handled branches of operations manager
                
                if($resulta[$datefield]==date('Y-m-d') OR (date('Y-m-d')==date('Y-m-d', strtotime("+1 day", strtotime($resulta[$datefield]))) AND date('H:i')<='12:00')){
                    $sqlcheckopsmanager='SELECT BranchNo FROM attend_1branchgroups WHERE (BranchNo='.$resulta['BranchNo'].''.($wheretobranchno==1?' OR BranchNo='.$resulta['ToBranchNo'].'':'').') AND OpsManager='.$_SESSION['(ak0)'].'';
                    $stmtcheckopsmanager=$link->query($sqlcheckopsmanager);
                    if($stmtcheckopsmanager->rowCount()>0){
                        //allowed
                    } else {
                        goto nopermission;  
                    }
                } else {
                    goto nopermission; 
                }
            } elseif(allowedToOpen(314,'1rtc')){ //invty planners
                $sqlcheckplanner='SELECT BranchNo FROM attend_1branchgroups WHERE (BranchNo='.$resulta['BranchNo'].''.($wheretobranchno==1?' OR BranchNo='.$resulta['ToBranchNo'].'':'').') AND InventoryPlanner='.$_SESSION['(ak0)'].'';
                
                $stmtcheckplanner=$link->query($sqlcheckplanner);
                if($stmtcheckplanner->rowCount()>0){
                    //allowed
                } else {
                    goto nopermission;  
                }
            }
            break;

        case 'invty_2mrr':
            if (!allowedToOpen(402,'1rtc')){
                 goto nopermission;
                } else{
                    $txnid=$txnid.' and SenttoAcctg=0'; 
                }
            
            break;
			case 'invty_2pr':
                
            if (!allowedToOpen(409,'1rtc')){
                 goto nopermission;
                } else{
                    $txnid=$txnid.' and SenttoAcctg=0';
                }
            
            break;
			case 'payroll_30othercreditsmain':
            if (!allowedToOpen(792,'1rtc')){
                 goto nopermission;
                } else{
                    $txnid=$txnid; 
                }
            
            break;
        case 'invty_3order':
            if (!allowedToOpen(403,'1rtc')){goto nopermission;} 
            break;
        case 'audit_2countmain':
        case 'audit_2toolscountmain':
        case 'audit_2countcash':
        case 'invty_4adjust':
            if (!allowedToOpen(404,'1rtc')){ goto nopermission; } 
            break;
        case 'acctg_2jvmain':
            if (!allowedToOpen(405,'1rtc')){ goto nopermission; } 
            break;
        case 'payroll_31loansmain':
			if (!allowedToOpen(8054,'1rtc')){ goto nopermission;}
			else {$datecondition=true;}
		break;
        case 'acctg_2purchasemain':
            if (!allowedToOpen(406,'1rtc')){ goto nopermission; } 
            break;
        case 'acctg_1assets':
            if (!allowedToOpen(408,'1rtc')){ goto nopermission; }
            $datecondition=true; 
            break;
        case 'acctg_2prepaid':
            if (!allowedToOpen(408,'1rtc')){ goto nopermission; }
            $datecondition=true; 
            break;
        
        case 'acctg_2depositmain':  
            if (!allowedToOpen(412,'1rtc')){ goto nopermission;}
            break;

        case 'acctg_4futurecvmain':   
            $datecondition=true;
        case 'acctg_2cvmain':   
            if($postfield=='APVPosted'){
                if (!allowedToOpen(413,'1rtc')){ goto nopermission;}
                $sql='SELECT CVNo FROM acctg_2cvmain WHERE  (CreditAccountID NOT IN (SELECT AccountID FROM banktxns_1maintaining)) AND `'.$txnidname.'`='.$txnid;
                $stmt=$link->query($sql); $result=$stmt->fetch();
                if($stmt->rowCount()==0) { goto nopermission;}
                $sql='UPDATE acctg_2cvmain SET Posted='.$postvalue.' WHERE  (CreditAccountID NOT IN (SELECT AccountID FROM banktxns_1maintaining)) AND `'.$txnidname.'`='.$txnid;  
                $stmt=$link->prepare($sql); $stmt->execute();
            } else {
                if (!allowedToOpen(414,'1rtc')){ goto nopermission;}
                $txnid=$txnid.' AND (CreditAccountID=403 OR (CreditAccountID  IN (SELECT AccountID FROM banktxns_1maintaining)))';
            }
                break;

        case 'acctg_2collectsubbounced':
        case 'acctg_2salemain':
        case 'acctg_2txfrmain':
            if (!allowedToOpen(407,'1rtc')){goto nopermission;} 
            break;       
        case 'acctg_3undepositedpdcfromlastperiodbounced':
            if (!allowedToOpen(407,'1rtc')){goto nopermission;} 
			$txnidname='UndepPDCId';
            break;        
        case 'acctg_2collectmain':
            if (!allowedToOpen(4091,'1rtc')){ goto nopermission;} 
            break;
        case 'quotations_2quotemain':
            if (!allowedToOpen(410,'1rtc')){ goto nopermission; }
            else {$datecondition=allowedToOpen(411,'1rtc')?true:'`EncodedByNo`='.$_SESSION['(ak0)'].' AND DATE_ADD(QuoteDate, INTERVAL 7 DAY)>=CURDATE() AND DATE_ADD(`TimeStamp`, INTERVAL 7 DAY)>=CURDATE()';}
            break;
        case 'hr_2trainsched':
            if (!allowedToOpen(68911,'1rtc')){ goto nopermission;} 
            $datecondition=' DATEDIFF(CURDATE(),ENDDATE)<2 ';
            break;
        default:
            goto nopermission;
        }
        $sql='Update `'.$table.'` SET `'.$postfield.'`=0, `'.$postfield.'ByNo`='.$_SESSION['(ak0)'].' where '.$datecondition.' and '.$txnidname.'='.$txnid;
		if($txntype=='LoanType'){
			$stmt=$link->prepare($sql); $stmt->execute();
			$sql='Update `'.$table.'` SET `'.$postfield.'TS`=NOW() where '.$txnidname.'='.$txnid;
		}
		
                if ($_SESSION['(ak0)']==1002) {echo $sql; }
                $stmt=$link->prepare($sql);
                $stmt->execute();  
		//addons		
		if($table=='invty_2sale'){
			$sqlchecker='select Posted from invty_2sale where TxnID='.$txnid.'';	
			$stmtchecker=$link->query($sqlchecker); $resultchecker=$stmtchecker->fetch();
				if($resultchecker['Posted']==0){
					$sqlu='Update invty_2salesubaddons set Qty=0,Approved=0,FApproved=0 where TxnID='.$txnid.'';
					// echo $sqlu; exit();
					$stmtu=$link->prepare($sqlu); $stmtu->execute();
				}
				
		}
		//
    }
nopermission:
header("Location:".$_SERVER['HTTP_REFERER']);
?>