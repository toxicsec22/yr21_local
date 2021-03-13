<?php
	//If sessions are enabled but there is no active one
	//Don't allow unsolicited output on prattend.php, will break extensions
   
	if(session_id()==''){
		session_start();
	}
	if(!isset($_GET['yr'])){
		$yrn=21;
	} else {
		$yrn=$_GET['yr'];
	}
        $currentyr='20'.$yrn; $url_folder='yr'.$yrn; 
if((isset($_SESSION['MyAttend']) OR (isset($_SESSION['In'])) OR (isset($_SESSION['Out'])))){
		if(isset($_SESSION['MyAttend'])){
			$_POST['MyAttend'] = $_SESSION['MyAttend'];
			unset($_SESSION['MyAttend']);
		} else if (isset($_SESSION['In'])){
			$_POST['In'] = $_SESSION['In'];
			unset($_SESSION['In']);
		} else {
			$_POST['Out']=$_SESSION['Out'];
			unset($_SESSION['Out']);
		}

		$_POST['attendid']=$_SESSION['attendid'];
		$_POST['attendpw']=$_SESSION['attendpw'];
		
		unset($_SESSION['attendid'],$_SESSION['attendpw']);
	} 
        $path=$_SERVER['DOCUMENT_ROOT'];
	$from_biometrics = function_exists("from_biometrics") and from_biometrics();
$login=addslashes($_POST['attendid']);
	//Don't let these to produce an error when script came from biometrics
        
		 include_once($path.'/acrossyrs/dbinit/userinit.php');
		 include_once($path.'/acrossyrs/commonfunctions/hashandcrypt.php');
            
                 $link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
	
	
	date_default_timezone_set('Asia/Manila');
        
	if($from_biometrics) {
		//$cookie=$json['_comkey'];
                $attendfolder='../../'.$url_folder.'/attendance/';
        }
    else {
    	//$cookie=$_COOKIE['_comkey']; 
        $attendfolder='';
    }

    if (isset($_REQUEST['TxnID'])){
                $calledfrom=$_REQUEST['edit']; 
                $attenddate=$_REQUEST['AttendDate'];
                } else {
                        $calledfrom=0;
                        $attenddate=date('Y-m-d');
                }

	


		//check if cookie is in branch
		$stmtbc=$link->prepare('SELECT ProgCookie FROM 1branches b WHERE Pseudobranch=0 AND b.ProgCookie = "'.$_COOKIE['_comkey'].'"');
		$stmtbc->execute();
		
	switch ($calledfrom){
		case 1: //8 to 5 regular sched
			$txnid=intval($_REQUEST['TxnID']);
			$stmt=$link->prepare('UPDATE attend_2attendance INNER JOIN attend_2attendancedates ON `attend_2attendance`.DateToday = `attend_2attendancedates`.DateToday SET `attend_2attendance`.TimeIn = time(\'8:00\'), `attend_2attendance`.TimeOut = time(\'17:00\'), `attend_2attendance`.LeaveNo=\'11\', `attend_2attendance`.TIEncby=' . $_SESSION['(ak0)'] .', `attend_2attendance`.TOEncby=' . $_SESSION['(ak0)'] .',`attend_2attendance`.HREncby=' . $_SESSION['(ak0)'] .',attend_2attendance.HRTS=Now() WHERE TimeIn is null AND TimeOut is null AND Remarks is null AND Overtime=0 AND (((`attend_2attendance`.TxnID)=:TxnID) AND ((`attend_2attendancedates`.Posted)=0))');
			$stmt->bindValue(':TxnID', $txnid, PDO::PARAM_STR);
			$stmt->execute();
			header("Location:encodeattend.php?AttendDate=$attenddate&w=EncodeAttend");
			break;
		case 2: // edit specifics
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$txnid=intval($_REQUEST['TxnID']);
			
			$remarks=addslashes($_POST['RemarksHR']);
			$overtime=addslashes($_POST['Overtime']);
			$leaveno=addslashes($_POST['LeaveNo']);
			
			if($overtime>5){ echo 'Invalid Overtime Value.'; exit(); }
			if($overtime==2){
				$sqlpot='SELECT a.IDNo from attend_2attendance a JOIN approvals_5ot ot ON a.IDNo=ot.IDNo AND a.DateToday=ot.DateToday WHERE a.TxnID='.$txnid.' AND ot.Approved=1';
				$stmtpot = $link->query($sqlpot);
				if($stmtpot->rowCount()==0){
					echo 'No Pre-Approved OT Request'; exit();
				}
			}
			if($leaveno==14){ //SL
                                
				$sqlallow='SELECT IF(LeaveNo=14,SLBal+1,SLBal) AS SLBal from `attend_61leavebal` sb JOIN attend_2attendance a ON sb.IDNo=a.IDNo WHERE Resigned=0 AND TxnID='.$txnid.'';
				
				$stmtallow = $link->query($sqlallow);
				$rowallow = $stmtallow->fetch();
				
				if($rowallow['SLBal']<=0){ 
					echo 'No SIL Balance.';
					exit();
				} else { // has SL balance
                                    
                                    $sqlallow='SELECT LatestDorM, TypeofDayNo FROM  attend_2attendance a JOIN `payroll_20latestrates` lr ON a.IDNo=lr.IDNo JOIN `attend_2attendancedates` ad ON ad.DateToday=a.DateToday WHERE a.TxnID='.$txnid;
				
                                    $stmtallow = $link->query($sqlallow);
                                    $rowallow = $stmtallow->fetch();        
                                    if($rowallow['TypeofDayNo']==3){
                                            if($rowallow['LatestDorM']==0){ // daily paid
                                                
                                                goto allowsil;
                                            
                                            } else { 
                                                echo 'Not necessary for semi-monthly for special holidays.';
                                                exit();
                                            }
                                        
                                    } else { // not special holiday
                                        
					goto allowsil;
				}
			}
                        }
			allowsil:
			
			if (is_numeric($_POST['LeaveNo'])) {
				$leavesql='`attend_2attendance`.LeaveNo = '.$leaveno.',';
			} else {
				$leavesql='';
			} 
			
			$sql='UPDATE attend_2attendance INNER JOIN attend_2attendancedates ON `attend_2attendance`.DateToday = `attend_2attendancedates`.DateToday
			SET 
				`attend_2attendance`.RemarksHR = \''.$remarks.'\',
				`attend_2attendance`.Overtime = '.$overtime.',
				'.$leavesql.'
				`attend_2attendance`.HREncby = ' . $_SESSION['(ak0)'] .',
				`attend_2attendance`.HRTS=Now()
				WHERE (((`attend_2attendance`.TxnID)='.$txnid.') AND ((`attend_2attendancedates`.Posted)=0))';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:encodeattend.php?AttendDate=$attenddate&w=EncodeAttend");
			
			break;
		case 3: // set as blank
			$txnid=intval($_REQUEST['TxnID']);
			$sql='UPDATE attend_2attendance INNER JOIN attend_2attendancedates ON `attend_2attendance`.DateToday = `attend_2attendancedates`.DateToday
			SET `attend_2attendance`.TimeIn = null, `attend_2attendance`.TimeOut = null, 
				`attend_2attendance`.RemarksHR = null,
				`attend_2attendance`.Overtime = 0,
				`attend_2attendance`.LeaveNo = 18,
				`attend_2attendance`.TIEncby = ' . $_SESSION['(ak0)'] .',
				`attend_2attendance`.TOEncby = ' . $_SESSION['(ak0)'] .',
				`attend_2attendance`.HREncby = ' . $_SESSION['(ak0)'] .',
				`attend_2attendance`.HRTS=Now(), TInTS=Now(), TOTS=Now()
				WHERE (((`attend_2attendance`.TxnID)='.$txnid.') AND ((`attend_2attendancedates`.Posted)=0))';
				// echo $sql;
			$stmt=$link->prepare($sql);
			
			$stmt->execute();
			header("Location:encodeattend.php?AttendDate=$attenddate&w=EncodeAttend");
			break;
			
			case 4: // Manual
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$txnid=intval($_REQUEST['TxnID']);
			$remarks=addslashes($_POST['Remarks']);
			$overtime=addslashes($_POST['Overtime']);
			
			 // print_r($_POST); exit();
			if (addslashes($_POST['TimeIn'])){
				 // $addsql='`attend_2attendance`.TimeIn = STR_TO_DATE(\''.$_POST['TimeIn'].'\',\'%l:%i %p\'), `attend_2attendance`.LeaveNo = 11, `attend_2attendance`.TIEncby = ' . $_SESSION['(ak0)'] .', '
                                         // . 'TInTS=Now() ';
				 $addsql='`attend_2attendance`.TimeIn = \''.$_POST['TimeIn'].'\', `attend_2attendance`.LeaveNo = 11, `attend_2attendance`.TIEncby = ' . $_SESSION['(ak0)'] .', '
                                         . 'TInTS=Now() ';
			} elseif (addslashes($_POST['TimeOut'])) {
				$addsql='`attend_2attendance`.TimeOut = \''.$_POST['TimeOut'].'\',`attend_2attendance`.TOEncby = ' . $_SESSION['(ak0)'] .', '
                                        . 'TOTS=Now() ';            
			}
			
			$sql='UPDATE attend_2attendance INNER JOIN attend_2attendancedates ON `attend_2attendance`.DateToday = `attend_2attendancedates`.DateToday
			SET '.$addsql.'  
			WHERE (((`attend_2attendance`.TxnID)='.$txnid.') AND ((`attend_2attendancedates`.Posted)=0))';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:encodeattend.php?AttendDate=$attenddate&w=EncodeAttend");
			
			break;
			case 5: // RemarksDept
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$txnid=intval($_REQUEST['TxnID']);
			
			$remarks2=addslashes($_POST['RemarksDept']);
			$sql='UPDATE attend_2attendance INNER JOIN attend_2attendancedates ON `attend_2attendance`.DateToday = `attend_2attendancedates`.DateToday
			SET `attend_2attendance`.RemarksDept = \''.$remarks2.'\',
			`attend_2attendance`.DEPTTS=Now(),
			`attend_2attendance`.DEPTEncby=' . $_SESSION['(ak0)'] .'
				WHERE (((`attend_2attendance`.TxnID)='.$txnid.') AND ((`attend_2attendancedates`.Posted)=0) AND ((`attend_2attendancedates`.DateToday>CURDATE())))  ';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:encodeattend.php?w=RemarksOfDept");
			
			break;
			case 6: // RemarksHR
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$txnid=intval($_REQUEST['TxnID']);
			
			$remarks3=addslashes($_POST['RemarksHR']);
			$sql='UPDATE attend_2attendance INNER JOIN attend_2attendancedates ON `attend_2attendance`.DateToday = `attend_2attendancedates`.DateToday
			SET `attend_2attendance`.RemarksHR = \''.$remarks3.'\',
			`attend_2attendance`.HRTS=Now(),
			`attend_2attendance`.HREncby=' . $_SESSION['(ak0)'] .'
				WHERE (((`attend_2attendance`.TxnID)='.$txnid.') AND ((`attend_2attendancedates`.Posted)=0) AND ((`attend_2attendancedates`.DateToday>CURDATE())))  ';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:encodeattend.php?w=RemarksOfHR");
			
			break;
		case 0: //direct attendance entry
			if(isset($_POST['attendid']))
				$loginid=addslashes($_POST['attendid']);
			elseif($from_biometrics) {
				if (auth_biometrics())
					$loginid = addslashes($json["userId"]);
			}


	if((isset($_POST['In']) or isset($_POST['Out'])) or 
		//Condition for biometrics
		((isset($json["action"])) and 
		(($json["action"] == "Time In") or ($json["action"] == "Time Out"))))
		 { 
          
		 
            // $path=$_SERVER['DOCUMENT_ROOT'];
			include($path.'/acrossyrs/commonfunctions/isphone.php');
			
			include($path.'/acrossyrs/commonfunctions/isphoneloginattend.php');
			            
                 if($from_biometrics){
					$sqlrelieverpos='SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID=61102';
					$stmtrelieverpos=$link->prepare($sqlrelieverpos); $stmtrelieverpos->execute(); $rowrelieverpos=$stmtrelieverpos->fetch();
					$relieverpos=$rowrelieverpos['AllowedPos'];

					$stmt1=$link->prepare('SELECT IF(cp.PositionID IN ('.$relieverpos.'),(SELECT b2.ProgCookie FROM 1branches b2 JOIN attend_2attendance a ON b2.BranchNo=a.BranchNo WHERE IDNo='.$loginid.' AND DateToday=CURDATE()),p.ProgCookie) AS ProgCookie,Pseudobranch FROM 1_gamit.1rtcusers p JOIN attend_30currentpositions cp ON p.IDNo=cp.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo WHERE p.IDNo='.$loginid.'');
					$stmt1->execute();
					$row1 = $stmt1->fetch(PDO::FETCH_ASSOC);
					if($row1['Pseudobranch']<>0){
						goto allowattendance;
					} else {

						if ($row1['ProgCookie']==$_COOKIE['_comkey'] OR $_COOKIE['_comkey']=='timeinoutcookie'){
							goto allowattendance;
						}
						else {
							echo 'Unauthorized Login.'; exit();
						}
					}
				 }
                 
		// Warehouses will be allowed on certain computers only
		
		// Check if branch
		$stmt=$link->prepare('SELECT BranchNo FROM `1branches` WHERE Pseudobranch=0 AND Active=1 AND ProgCookie=\''.$_COOKIE['_comkey'].'\'');
		
		
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($stmt->rowCount()>0){ // branches
				goto allowattendance; 
			} else { // warehouse or office
			
			
			goto allowattendance;
			
			
			//open time in for all authorized devices temporary.
			
			/* $stmt2=$link->prepare('SELECT ProgCookie FROM `1_gamit`.`1specindex` WHERE specid=801;');
			$stmt2->execute(); $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
			
			if ($_COOKIE['_comkey2']===$row2['ProgCookie']){
				goto allowattendance;
			} else {
				header("Location:/index.php?noid=2");
				goto end;
			} */
		}
		
		allowattendance:
		
		
		// check passwords then record attendance
		if(isset($_POST['attendpw']))
			$pw=addslashes($_POST['attendpw']);
		elseif($from_biometrics)
			$pw = " "; //set to blank, just to make sure that there will be no warnings because it will not break the chrome extension

		
		$stmt=$link->prepare('SELECT p.IDNo, p.uphashmayasin, BranchNo, e.PositionID FROM `attend_30currentpositions` e JOIN `1_gamit`.`1rtcusers` p ON e.IDNo = p.IDNo WHERE p.IDNo = :UserID');
		
		$stmt->bindValue(':UserID', $loginid, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$stmt->rowCount()>0){
			// no login id
			header("Location:/index.php?noid=2");
			goto end;
		}

		
		if (verify($pw,$row['uphashmayasin']) or ($from_biometrics and auth_biometrics())) {

			// if (checkcookie($loginid,$cookie,1)) { 
			if (($_COOKIE['_comkey']=='timeinoutcookie') OR (isset($_COOKIE['_comkey2'])) OR ($stmtbc->rowCount()>0) OR (isset($_POST['In'])) OR (isset($_POST['Out']))) {
				// $rtctime = date("H:i",strtotime(date("Y-m-d H:i:s")." +10 minutes"));
				$rtctime = date("H:i",strtotime(date("Y-m-d H:i:s")));
				$sql='UPDATE attend_2attendance INNER JOIN attend_2attendancedates ON
				`attend_2attendance`.DateToday = `attend_2attendancedates`.DateToday ';
 
					include_once($path.'/'.$url_folder.'/attendance/function_checkAttend.php'); 
					
					if(isset($_POST['In']) || $json["action"] == "Time In"){
						if(checkAttend('TimeIn',$loginid,$attenddate)){
                                                    
                                        if($currentyr>date('Y')){            
                                        $sql1='SELECT (DateToday) FROM '.$currentyr.'_1rtc.attend_2attendance WHERE IDNo='.$loginid.' AND TimeIn IS NOT NULL;';
                                        $stmt0=$link->query($sql1); $res0=$stmt0->fetch();
                                        if($stmt0->rowCount()>0){ $db=$currentyr;} else { $db=$currentyr-1;}
                                        
                                        } else { $db=$currentyr;}
                                       // echo $currentyr.'<br>'.$db; exit();
                                                    // check if unapproved absence on previous working day
                            $sql0='SELECT LeaveNo FROM `'.$db.'_1rtc`.`attend_2attendance` WHERE IDNo='.$loginid
                                    .' AND DateToday IN (SELECT MAX(DateToday) FROM `'.$db.'_1rtc`.`attend_2attendance` WHERE IDNo='.$loginid
                                    .' AND LeaveNo NOT IN (12,13,14,15,16,19) AND DateToday<CURDATE());';
                            $stmt0=$link->query($sql0); $res0=$stmt0->fetch(); 
                            if($res0['LeaveNo']==17 OR $res0['LeaveNo']==18){ $timein=''; $msg='withUA/AWOL'; goto nologout; } 
                            else { 
							
							
							//will remove soon
							$_SESSION['LAST_ACTIVITY']=time();
							$_SESSION['eod']=strtotime("+1 hours");
							$_SESSION['&pos']=-1;
							$_SESSION['(ak0)']=$loginid;
							$_SESSION['bnum']=$row['BranchNo'];
										
							$sqlhealthmain='SELECT TxnID,QuestionsScoresArray FROM systools_2clresults WHERE IDNoOrBranchNo='.$loginid.' AND DateAnswered=CURDATE();';
							
							 $stmtdailyhealth=$link->query($sqlhealthmain);
                             $resdailyhealth=$stmtdailyhealth->fetch(); 
							if($stmtdailyhealth->rowCount()>0){
								//goto lookup form
								if($resdailyhealth['QuestionsScoresArray']==NULL){
									$form='Form';
								} else {
									$form='Form1';
								}
								
								
								
							} else { //CTxnID = Daily Health Check Form
								$sql='INSERT INTO systools_2clresults SET IDNoOrBranchNo='.$loginid.',CTxnID=1,DateAnswered=CURDATE(),EncodedByNo='.$loginid.', TimeStamp=Now();';
								$stmt=$link->prepare($sql); $stmt->execute();
								
								
								$stmtdailyhealth=$link->query($sqlhealthmain);
								$resdailyhealth=$stmtdailyhealth->fetch(); 
							 
								$form='Form';
								
							}
							header('Location:/'.$url_folder.'/systools/dailyhealthcheckform.php?w='.$form.'&clTxnID='.$resdailyhealth['TxnID']);
							goto end;
							
							
							
							$sql=$sql. 'SET `attend_2attendance`.TimeIn = time(\''.$rtctime.'\'),
						`attend_2attendance`.LeaveNo = if(`attend_2attendance`.LeaveNo<>15,11,15),
						`attend_2attendance`.TIEncby = ' . $loginid .
						',  `attend_2attendance`.TInTS=Now() ';
							 
							 
							 
                            }
							
						} else { //there is alrdy a recorded attendance
							header("Location:/index.php?noid=3");	
							}
					} else {
						
						
						if(checkAttend('TimeOut',$loginid,$attenddate)){
							
							
						//No Time Out if theres pending leave request.

						//pending ackowledgement
						$sqlc='select TxnID from attend_3leaverequest lr JOIN attend_30currentpositions cp ON lr.IDNo=cp.IDNo WHERE SupervisorApproved<>0 AND Approved<>0 AND MarkasReadByDeptHead<>0 AND Acknowledged=0 AND (lr.IDNo='.$loginid.' OR LatestSupervisorIDNo='.$loginid.');';
						 $stmtc=$link->query($sqlc);  
    
						if ($stmtc->rowCount()>0){
							header('Location:/'.$url_folder.'/attendance/attenderrorpage.php');
							exit();
						}
						//pending supervisor approval
						$sqlc='select TxnID from attend_3leaverequest lr JOIN attend_30currentpositions cp ON lr.IDNo=cp.IDNo WHERE LatestSupervisorIDNo='.$loginid.' AND SupervisorApproved=0;';
						 $stmtc=$link->query($sqlc);  
    
						if ($stmtc->rowCount()>0){
							header('Location:/'.$url_folder.'/attendance/attenderrorpage.php');
							exit();
						}
						//pending dept head approval
						$sqlc='SELECT TxnID FROM attend_3leaverequest lr JOIN `attend_30currentpositions` cp ON lr.IDNo=cp.IDNo
							WHERE SupervisorApproved<>0 AND Approved=0 AND MarkasReadByDeptHead=0 AND deptheadpositionid=(SELECT PositionID FROM attend_30latestpositionsinclresigned WHERE IDNo='.$loginid.');';
						 $stmtc=$link->query($sqlc);  
    
						if ($stmtc->rowCount()>0){
							header('Location:/'.$url_folder.'/attendance/attenderrorpage.php');
							exit();
						}
						
						//END
							
						

						
							
							
							
							
							
                                                    if(in_array(date('w'),array(0,6))){ goto skipcheck;}
                                                    $stmtcheck=$link->query('SELECT `BranchNo` FROM `attend_30currentpositions` p WHERE p.IDNo='.$loginid.' and p.PositionID in (32,37,81);');
                                                    $rescheck=$stmtcheck->fetch(); 
                                                    if ($stmtcheck->rowCount()>0) {
                                                        $conditionerr=' AND b.BranchNo='.$rescheck['BranchNo'];
                                                        
                                                        include_once($path.'/'.$url_folder.'/acctg/sqlphp/cashsalesvsdeposits.php');
                                                        
                                                        $stmtcheck=$link->query($sqlcheck); $rescheck=$stmtcheck->fetchAll(); 
                                                        if ($stmtcheck->rowCount()>0){ $nologout=true; goto nologout;}                                                        
                                                    }
                                                    skipcheck:
						$sql=$sql. 'SET `attend_2attendance`.TimeOut = time(\''.$rtctime.'\'),
						`attend_2attendance`.TOEncby = ' . $loginid .
						', `attend_2attendance`.TOTS=Now()';
						
						                    
						} else { //there is alrdy a recorded attendance
							header("Location:/index.php?noid=3");	
							}
					}
					
					
					
				$sql=$sql. ' WHERE (((`attend_2attendance`.IDNo)=' . $loginid.') AND (`attend_2attendance`.DateToday)=\'' . $attenddate.'\')
				AND ((`attend_2attendancedates`.Posted)=0)';
					//echo $sql; exit();  
					
					$stmt=$link->prepare($sql);
					$stmt->execute();
					
					
					
					// when attendance is recorded, it will open My Attendance
                                        nologout:
					//session_start();
                                        $_SESSION['LAST_ACTIVITY']=time();
                                        $_SESSION['eod']=strtotime("+1 hours");
                                        $_SESSION['&pos']=$row['PositionID'];
                                        $_SESSION['(ak0)']=$loginid;
                                        $_SESSION['bnum']=$row['BranchNo'];
					
					header('Location:/'.$url_folder.'/attendance/tocheckattendtoday.php?IDNo='.$loginid.(isset($nologout)?'&nologout=true':'').(isset($msg)?'&msg='.$msg:''));	
					
					
                                        goto end;					
					//header("Location:/index.php?noid=0");
					
					goto end;
					} else { // cookie incorrect
						header("Location:/index.php?noid=1");
						goto end;
						}
				} else {  //password incorrect
					header("Location:/index.php?noid=2");
					goto end;
				}
	} elseif (isset($_POST['MyAttend']) or 
				(isset($json["action"]) and $json["action"] == "My Attendance")){
		// check passwords then open my attendance THIS PORTION IS REPEATED!! :-(
		if(session_id()==''){
    session_start(); 
} else {
    
}
	
	
		require ('../../acrossyrs/logincodes/todayat7.php');	

		$_SESSION['eod']=$today7pm;
		$_SESSION['LAST_ACTIVITY']=time();

		if(isset($_POST['attendpw']))
			$pw=addslashes($_POST['attendpw']);
		else
			$pw = " "; 

		
		$stmt=$link->prepare('SELECT p.IDNo, p.uphashmayasin, BranchNo, e.PositionID FROM `attend_30currentpositions` e '
                        . ' JOIN 1_gamit.1rtcusers p ON e.IDNo = p.IDNo WHERE e.IDNo=:UserID');
		$stmt->bindValue(':UserID', $loginid, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$stmt->rowCount()>0){
			// no login id
			
			header("Location:/index.php?noid=2");
			goto end;
			
		} elseif (verify($pw,$row['uphashmayasin']) or ($from_biometrics and auth_biometrics())) {
			
		
			// if (checkcookie($loginid,$cookie,2)) {
			if ((isset($_COOKIE['_comkey2'])) OR ($stmtbc->rowCount()>0) OR (isset($_COOKIE['_comkey']))) {
				$_SESSION['(ak0)']=$loginid;
                                
				header('Location:'.$attendfolder.'tocheckattendance.php?qry=my_attendance&logout=1');                                
				goto end;
			
				} else { // cookie incorrect
						header("Location:/index.php?noid=1");
						goto end;
						}
			} else {  //password incorrect
					header("Location:/index.php?noid=2");
					goto end;
	}
	}
	}
	end:
             $link=null; $stmt=null;
            if($from_biometrics){
            	unset($_SESSION["from_biometrics"]);
            	unset($_SESSION["authenticated"]);
            }

?>