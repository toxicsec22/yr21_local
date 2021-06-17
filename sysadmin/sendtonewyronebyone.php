<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(606,'1rtc')) {   echo 'No permission'; exit();}  
$showbranches=false; include_once('../switchboard/contents.php');
$link=connect_db($currentyr.'_1rtc',1);

$title='Send To New Year';
echo '<head><title>'.$title.'</title></head>';
$w=!isset($_GET['w'])?'List':$_GET['w'];

echo '<div style="margin-left:5%;"><br><br>Forward beginning balances for '.$nextyr.'<br><br>';
echo '<form method="GET" action="createdbs.php"><input type="submit" value="Create '.$nextyr.' databases."></form><br><br>';
echo '<font style="color: maroon"><i>Run in Linux:
                    <br># chown apache:apache /var/www/html/php/arwan/yr'.substr($nextyr,-2).'/generalinfo/bgpics
                    <br># chown apache:apache /var/www/html/php/arwan/yr'.substr($nextyr,-2).'/admin/orcrpics
                    <br># cp /var/www/html/php/arwan/yr'.substr($currentyr,-2).'/generalinfo/employeepics/* /var/www/html/php/arwan/yr'.substr($nextyr,-2).'/generalinfo/employeepics
                    <br># chown apache:apache /var/www/html/php/arwan/yr'.substr($nextyr,-2).'/generalinfo/employeepics
                    </i></font><br><br><font style="color: yellow">Implementing annual changes on forwarded data for latest salaries, and calculation of philhealth. Check <a href="https://www.philhealth.gov.ph/news/2019/new_contri.php" target="_blank">link</a>.</font><br><br>';
$sql='SELECT tblInitialized,RemarksonConditions, IF(UpdateORRedo=2,"Truncate & Insert","Insert Unique Only") AS UpdateORRedo,StatusRemarks,TimeStamp FROM 1_gamit.sysadmin_0startofyr;';
$stmt=$link->query($sql); $res=$stmt->fetchAll();
echo '<table border="1px solid">';
echo '<thead>';
echo '<tr><th>tblInitialized</th><th>RemarksonConditions</th><th>UpdateORRedo</th><th>StatusRemarks</th><th>TimeStamp</th><th>Forward</th></tr>';
$tdstyle='style="padding:5px;"';
echo '</thead>';

foreach($res AS $result){
	echo '<tr><td '.$tdstyle.'>'.$result['tblInitialized'].'</td><td '.$tdstyle.'>'.$result['RemarksonConditions'].'</td><td '.$tdstyle.'>'.$result['UpdateORRedo'].'</td><td '.$tdstyle.'>'.(substr($result['StatusRemarks'],0,4)=="DONE"?'<font color="green">':'<font color="yellow">').''.$result['StatusRemarks'].'</font></td><td '.$tdstyle.'>'.$result['TimeStamp'].'</td><td '.$tdstyle.'><form action="sendtonewyronebyone.php?w='.$result['tblInitialized'].'" method="POST"><input type="submit" name="btnForward" value="Forward"></form></td></tr>';
}
echo '</table>';

echo '</div>';

function overwrite($link,$table){
    global $currentyr;
    $nextyr=$currentyr+1;
        $sql='Select * from ' . $nextyr . '_1rtc.'.$table;
        $stmt=$link->query($sql);
        $stmt->fetchAll();
        if ($stmt->rowCount()>0){
            $sql='TRUNCATE ' . $nextyr . '_1rtc.'.$table;
            $stmt=$link->prepare($sql);
            $stmt->execute();
            }
}

if(isset($_POST['btnForward'])){

switch ($_GET['w']){
	
	case'payroll_2requestoicallowance':
	
	$sql='INSERT INTO '.$nextyr.'_1rtc.payroll_2requestoicallowance
SELECT * FROM '.$currentyr.'_1rtc.payroll_2requestoicallowance WHERE YEAR(DATE_ADD(`Date`, INTERVAL `Duration` MONTH))=\''.$nextyr.'\' and Valid=1
AND IDNo NOT IN (SELECT IDNo FROM '.$nextyr.'_1rtc.payroll_2requestoicallowance);';
	$stmt=$link->prepare($sql); $stmt->execute();
	
	
	break;
    
    case '00dataclosedby':
        
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`00dataclosedby` (`DataClosedBy`,`ForDB`,`FixedYrTarget`,`BonusRateBasedTargetReached`) 
        SELECT \'' . $currentyr . '-11-30\', `ForDB`,`FixedYrTarget`,`BonusRateBasedTargetReached` FROM `' . $currentyr . '_1rtc`.`00dataclosedby`;';
        echo $sql0;
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
		
	
	
	case 'acctg_4billassignment':
	
	 // $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`acctg_4billsdue`;';
     // $stmt=$link->prepare($sql0); $stmt->execute();
	 // $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`acctg_4billassignment`;';
     // $stmt=$link->prepare($sql0); $stmt->execute();
	
	//bill assignment
		$sql='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_4billassignment` (`AssignID`, `BTID`, `BillerID`, `SubscriberName`, `AccountNo`, `ExpenseAccountID`, `TelMobileNo`, `MRF`, `AssigneeNo`, `ExpenseOfBranchNo`, `DeclaredforCompanyNo`, `Active`, `CutOffDay`, `DueDay`, `EncodedByNo`, `Timestamp`) SELECT `AssignID`, `BTID`, `BillerID`, `SubscriberName`, `AccountNo`, `ExpenseAccountID`, `TelMobileNo`, `MRF`, `AssigneeNo`, `ExpenseOfBranchNo`, `DeclaredforCompanyNo`, `Active`, `CutOffDay`, `DueDay`, '.$_SESSION['(ak0)'].', NOW() FROM `'.$currentyr.'_1rtc`.`acctg_4billassignment` WHERE Active=1;';
		$stmt=$link->prepare($sql); $stmt->execute();
		echo $sql.'<br>';
	
		//Get ALL AssignID
		$sql='SELECT AssignID,CutOffDay,DueDay FROM `'.$nextyr.'_1rtc`.acctg_4billassignment ORDER BY AssignID;';
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
		foreach ($result AS $res){
			if ($res['CutOffDay']<$res['DueDay']){
			$lessthan='yes';
			$monthco=1;
			} else {
				$lessthan='no';
				$monthco=2;
			}
			$monthdd=(($lessthan=='yes')?$monthco:$monthco+1);
			while ($monthco<=12){
				if ($monthdd==13){ //nxt yr january only for due date
					$monthdd=1;
					$nxtyr=1;
				} else { //for condition only
					$nxtyr=0;
				}
				$sqlauto='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_4billsdue` SET AssignID='.$res['AssignID'].', CutOffDate="'.$nextyr."-".(strlen($monthco)==1?'0':'').$monthco."-".(strlen($res['CutOffDay'])==1?'0':'').$res['CutOffDay'].'", DueDate="'.(($nextyr+1)+$nxtyr)."-".(strlen($monthdd)==1?'0':'').$monthdd."-".(strlen($res['DueDay'])==1?'0':'').$res['DueDay'].'", EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=NOW()';
				// echo $sqlauto;
				$stmtauto=$link->prepare($sqlauto); $stmtauto->execute();
				$monthco=$monthco+1;
				$monthdd=$monthdd+1;
			}
		}
	break;
	
    case 'acctg_8closinglist':
        $link=connect_db($currentyr.'_1rtc',1);
        overwrite($link,'`acctg_8closinglist`');
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`acctg_8closinglist` (TxnID,statement)
        SELECT TxnID,statement  FROM `' . $currentyr . '_1rtc`.`acctg_8closinglist`;';
        echo $sql0;
        $stmt=$link->prepare($sql0); $stmt->execute();
        break;
        
        
	case 'monitor_2fromsuppliermain':
	 // $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`monitor_2fromsuppliersub`;';
     // $stmt=$link->prepare($sql0); $stmt->execute();
	 // $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`monitor_2fromsuppliermain`;';
     // $stmt=$link->prepare($sql0); $stmt->execute();
	
	$sql='SELECT GROUP_CONCAT(DISTINCT(TxnID)) AS TxnIDs FROM '.$currentyr.'_1rtc.monitor_2fromsuppliersub WHERE AcceptedTS IS NULL;';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	
	$sql='INSERT INTO `'.$nextyr.'_1rtc`.`monitor_2fromsuppliermain` (`TxnID`, `Date`, `SupplierNo`, `BranchNo`, `InvType`, `Remarks`, `EncodedByNo`, `TimeStamp`) SELECT `TxnID`, `Date`, `SupplierNo`, `BranchNo`, `InvType`, `Remarks`, '.$_SESSION['(ak0)'].', NOW() FROM '.$currentyr.'_1rtc.monitor_2fromsuppliermain WHERE TxnID IN ('.$result['TxnIDs'].');';
	$stmt=$link->prepare($sql); $stmt->execute();
	
	$sql='INSERT INTO `'.$nextyr.'_1rtc`.`monitor_2fromsuppliersub` SELECT * FROM '.$currentyr.'_1rtc.monitor_2fromsuppliersub WHERE AcceptedTS IS NULL ORDER BY TxnSubId;';
	$stmt=$link->prepare($sql); $stmt->execute();
	
	break;
	
	case 'admin_1vehiclelist':
	
	 // $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`admin_2vehicleassign`;';
     // $stmt=$link->prepare($sql0); $stmt->execute();
	 // $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`admin_1vehiclelistsub`;';
     // $stmt=$link->prepare($sql0); $stmt->execute();
	 // $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`admin_1vehiclelist`;';
     // $stmt=$link->prepare($sql0); $stmt->execute();
	
	//vehicle list
	$sql='INSERT INTO `'.$nextyr.'_1rtc`.`admin_1vehiclelist` (`TxnID`, `Brand`, `VTID`, `FTID`, `Series`, `PlateNo`, `CRDate`, `Color`, `CRNo`, `CRPic`, `EncodedByNo`, `Timestamp`) SELECT `TxnID`, `Brand`, `VTID`, `FTID`, `Series`, `PlateNo`, `CRDate`, `Color`, `CRNo`, `CRPic`,'.$_SESSION['(ak0)'].',NOW() FROM `'.$currentyr.'_1rtc`.`admin_1vehiclelist`';
	$stmt=$link->prepare($sql); $stmt->execute();
	
	//vehicle list sub
	$sql0='CREATE TEMPORARY TABLE latestvehicles AS SELECT MAX(ORDate) AS MaxDate, TxnID FROM '.$currentyr.'_1rtc.admin_1vehiclelistsub GROUP BY TxnID ORDER BY TxnID;';
	$stmt0=$link->prepare($sql0); $stmt0->execute();
	
	$sql='INSERT INTO `'.$nextyr.'_1rtc`.`admin_1vehiclelistsub` (`TxnSubID`, `ORDate`, `ORNo`, `TxnID`, `ORPic`, `EncodedByNo`, `TimeStamp`) SELECT  `TxnSubID`, `ORDate`, `ORNo`, vls.`TxnID`, `ORPic`, '.$_SESSION['(ak0)'].', NOW() from '.$currentyr.'_1rtc.admin_1vehiclelistsub vls JOIN latestvehicles lv ON (vls.TxnID=lv.TxnID AND vls.ORDate=lv.MaxDate)';
	$stmt=$link->prepare($sql); $stmt->execute();
	
	//vehicle assignment
	$sql='INSERT INTO `'.$nextyr.'_1rtc`.`admin_2vehicleassign` (`TxnID`, `VehicleID`, `DateAssigned`, `BranchNo`, `CurrentlyAssignedIDNo`, `EncodedByNo`, `TimeStamp`, `Status`) SELECT `TxnID`, `VehicleID`, `DateAssigned`, `BranchNo`, `CurrentlyAssignedIDNo`, '.$_SESSION['(ak0)'].', NOW(), `Status` FROM '.$currentyr.'_1rtc.admin_2vehicleassign WHERE Status=1';
	$stmt=$link->prepare($sql); $stmt->execute();
	
	break;
	
    case 'gen_info_00indexbg':

        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`gen_info_00indexbg` 
        SELECT * FROM ' . $currentyr . '_1rtc.gen_info_00indexbg ORDER BY StartDate DESC LIMIT 10;';
        echo $sql0;
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'permissions_2allprocesses':
        overwrite($link,'`permissions_2allprocesses`');
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`permissions_2allprocesses`
        (ProcessID,ProcessTitle,ProcessDesc,ProcessAddress,OnSwitch,AllowedPos,AllowedPerID,OrderBy)
        SELECT ProcessID,ProcessTitle,ProcessDesc,REPLACE(`ProcessAddress`,"' . substr($currentyr,-2) . '","' . substr($nextyr,-2) . '"),OnSwitch,'
            . ' AllowedPos,AllowedPerID,OrderBy FROM `' . $currentyr . '_1rtc`.`permissions_2allprocesses`;';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case '1employees':
        
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`1employees` SELECT * FROM  `' . $currentyr . '_1rtc`.`1employees` WHERE `Resigned`=0 AND IDNo NOT IN (SELECT IDNo FROM `' . $nextyr . '_1rtc`.`1employees`);';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'attend_2changeofpositions':
        overwrite($link,'`attend_2changeofpositions`');
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`attend_2changeofpositions` (`IDNo`,`DateofChange`,`NewPositionID`,`AssignedBranchNo`,
        `SupervisorIDNo`,`Remarks`,`EncodedByNo`,`TimeStamp`) SELECT p.`IDNo`,p.`DateofChange`,p.`PositionID`,p.`BranchNo`,
        p.`LatestSupervisorIDNo`,p.`Remarks`,cp.`EncodedByNo`, cp.`TimeStamp` FROM `' . $currentyr . '_1rtc`.`attend_30currentpositions` p
        JOIN `' . $currentyr . '_1rtc`.`attend_2changeofpositions` cp ON p.IDNo=cp.IDNo AND p.`DateofChange`=cp.`DateofChange`;';
        echo $sql0;
        $stmt=$link->prepare($sql0); $stmt->execute();  
        
        break;
    
     case '1employeesSIL':
        
         // this must be changed for Yr21 since some fields are no longer the same.
         $sql0='UPDATE `' . $nextyr . '_1rtc`.`1employees` e LEFT JOIN `' . $currentyr . '_1rtc`.`attend_61silbal` a ON e.IDNo=a.IDNo JOIN `' . $nextyr . '_1rtc`.`attend_30currentpositions` cp ON e.IDNo=cp.IDNo JOIN `' . $nextyr . '_1rtc`.`attend_0positions` p ON p.PositionID=cp.PositionID JOIN `' . $currentyr . '_1rtc`.`attend_howlongwithus` h ON e.IDNo=h.IDNo 
SET e.SLDays=IF(InYears>=0.5,5,0), e.VLfromPosition=IF(InYears>=0.5,VLfromPosition,0), e.SLBalDecCutoff=IFNULL(IF(a.SILBal>5,5,a.SILBal),0), e.PaidSLBenefit=0, e.VLfromTenure=IF(InYears>1,IF((TRUNCATE(InYears,0)-1)>MaxVLfromTenure,MaxVLfromTenure,TRUNCATE(InYears,0)-1),0) ;';
        echo $sql0;
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'attend_2attendancedates':
        
        // Add the attendance dates and holidays

        $startdate=date_create($currentyr.'-12-21');
        $enddate=date_create($nextyr.'-12-20');
        $day=1;
        while ($startdate<=$enddate){
            $stmt=$link->prepare('INSERT INTO `' . $nextyr . '_1rtc`.`attend_2attendancedates` (`DateToday`,`TxnID`)
                VALUES (\''. date('Y-m-d', date_timestamp_get($startdate)).'\', '.$day.')');
            $stmt->execute();
            $startdate=date_add($startdate,date_interval_create_from_date_string('1 day'));
            $day++;
        }

        $stmt=$link->prepare('UPDATE ' . $nextyr . '_1rtc.attend_2attendancedates SET PayrollID=1 WHERE DateToday BETWEEN \'' . $currentyr . '-12-21\' AND \'' . $nextyr . '-01-05\';');
        $stmt->execute();

        $stmt=$link->prepare('UPDATE ' . $nextyr . '_1rtc.attend_2attendancedates SET PayrollID=2 WHERE DateToday BETWEEN \'' . $nextyr . '-01-06\' AND \'' . $nextyr . '-01-20\';');
        $stmt->execute();

        $months=array(2,3,4,5,6,7,8,9,10,11,12);
        foreach ($months as $month){
            $stmt=$link->prepare('UPDATE ' . $nextyr . '_1rtc.attend_2attendancedates SET PayrollID='.(($month*2)-1).' WHERE DateToday BETWEEN \'' . $nextyr . '-'.($month-1).'-21\' AND \'' . $nextyr . '-'.$month.'-05\';');
            $stmt->execute();
            $stmt=$link->prepare('UPDATE ' . $nextyr . '_1rtc.attend_2attendancedates SET PayrollID='.($month*2).' WHERE DateToday BETWEEN \'' . $nextyr . '-'.$month.'-06\' AND \'' . $nextyr . '-'.$month.'-20\';');
            $stmt->execute();
        }
        // holiday type: 2=Legal/Regular; 3=Special
        $regularholidays=array(
            array('dateofholiday'=>$currentyr.'-12-25','holiday'=>'Christmas','type'=>2,'CheckBefore'=>$currentyr.'-12-23','CheckAfter'=>$currentyr.'-12-26'),
            array('dateofholiday'=>$currentyr.'-12-30','holiday'=>'Rizal Day','type'=>2,'CheckBefore'=>$currentyr.'-12-29','CheckAfter'=>$nextyr.'-01-02'),
            array('dateofholiday'=>$nextyr.'-01-01','holiday'=>'New Year','type'=>2,'CheckBefore'=>$currentyr.'-12-29','CheckAfter'=>$nextyr.'-01-02'),
            array('dateofholiday'=>$nextyr.'-04-09','holiday'=>'Day of Valor','type'=>2,'CheckBefore'=>$nextyr.'-04-08','CheckAfter'=>$nextyr.'-04-10'),
            array('dateofholiday'=>$nextyr.'-05-01','holiday'=>'Labor Day','type'=>2,'CheckBefore'=>$nextyr.'-04-30','CheckAfter'=>$nextyr.'-05-02'),
            array('dateofholiday'=>$nextyr.'-06-12','holiday'=>'Independence Day','type'=>2,'CheckBefore'=>$nextyr.'-06-11','CheckAfter'=>$nextyr.'-06-13'),
            array('dateofholiday'=>$nextyr.'-11-01','holiday'=>'All Saints\' Day','type'=>3,'CheckBefore'=>$nextyr.'-10-30','CheckAfter'=>$nextyr.'-11-03'),
            array('dateofholiday'=>$nextyr.'-11-30','holiday'=>'Bonifacio Day','type'=>2,'CheckBefore'=>$nextyr.'-11-29','CheckAfter'=>$nextyr.'-12-01'),
            array('dateofholiday'=>$nextyr.'-12-08','holiday'=>'Taguig Day','type'=>3,'CheckBefore'=>$nextyr.'-12-07','CheckAfter'=>$nextyr.'-12-09'),
            array('dateofholiday'=>$nextyr.'-12-24','holiday'=>'Additional Holiday','type'=>3,'CheckBefore'=>$nextyr.'-12-23','CheckAfter'=>$nextyr.'-12-26'),
            array('dateofholiday'=>$nextyr.'-11-02','holiday'=>'Additional Holiday','type'=>3,'CheckBefore'=>$nextyr.'-10-31','CheckAfter'=>$nextyr.'-11-03')
        );
        foreach ($regularholidays as $holiday){
            $stmt=$link->prepare('UPDATE ' . $nextyr . '_1rtc.attend_2attendancedates SET TypeOfDayNo='.$holiday['type'].', '
                    . ' RemarksOnDates="'.$holiday['holiday'].'", CheckDateBefore=\''.$holiday['CheckBefore']
                    .'\', CheckDateAfter=\''.$holiday['CheckAfter'].'\' WHERE DateToday=\'' . $holiday['dateofholiday'].'\'');
            $stmt->execute();
        }

        // end of attendance dates
        
        break;
    
    case 'attend_2attendance':
        
        // populate attendance data
        
        $sql0=' SELECT DateToday FROM ' . $nextyr . '_1rtc.attend_2attendancedates;';
        $stmt=$link->query($sql0); $res0=$stmt->fetchAll();
        foreach ($res0 as $date){
            $stmt=$link->prepare('INSERT INTO `' . $nextyr . '_1rtc`.`attend_2attendance` (`DateToday`,`IDNo`,`BranchNo`,`HREncby`,`HRTS`)
        SELECT \''.$date['DateToday'].'\', IDNo, (SELECT DefaultBranchAssignNo FROM `attend_1defaultbranchassign` dba WHERE dba.IDNo=e.IDNo) AS BranchNo, '
                .$_SESSION['(ak0)'].' AS EncodedByNo, Now() FROM `' . $nextyr . '_1rtc`.`1employees` e WHERE IDNo>1002 AND Resigned=0;');
        $stmt->execute();
        }        
        

        // set restdays
        $stmt=$link->prepare('UPDATE ' . $nextyr . '_1rtc.`attend_2attendance` a JOIN `' . $nextyr . '_1rtc`.`1employees` e ON e.IDNo=a.IDNo SET LeaveNo=15, a.HRTS=Now(), a.HREncby='.$_SESSION['(ak0)'].' WHERE Weekday(DateToday)=e.RestDay;');
        $stmt->execute();

        // set holidays in attendance 
        $stmt=$link->prepare('UPDATE ' . $nextyr . '_1rtc.attend_2attendance a JOIN ' . $nextyr . '_1rtc.attend_2attendancedates ad on ad.DateToday=a.DateToday Set LeaveNo=TypeofDayNo+10 WHERE TypeofDayNo<>0;');
        $stmt->execute();
        
        break;
    
    case 'hr_2personnelrequest':
        $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`hr_2personnelrequest` WHERE `TimeStamp` IN (SELECT `TimeStamp` FROM `' . $currentyr . '_1rtc`.`hr_2personnelrequest`) AND YEAR(`TimeStamp`)<' . $nextyr . ';';
        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`hr_2personnelrequest` SELECT * FROM  `' . $currentyr . '_1rtc`.`hr_2personnelrequest` WHERE PersonHired IS NULL OR PersonHired="";';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'approvals_3budgetandliq':
        $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`approvals_3budgetrequestsub` WHERE TxnID IN (SELECT TxnID FROM approvals_3budgetandliq WHERE Year(DateNeeded)<' . $nextyr . ') ;';
        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`approvals_3budgetandliq` WHERE Year(DateNeeded)<' . $nextyr ;
        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`approvals_3budgetandliq` SELECT * FROM `' . $currentyr . '_1rtc`.`approvals_3budgetandliq` WHERE Approved=1 AND Liquidated<>1;';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();        
                
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`approvals_3budgetrequestsub` SELECT * FROM `' . $currentyr . '_1rtc`.`approvals_3budgetrequestsub` WHERE TxnID IN (SELECT TxnID FROM `' . $currentyr . '_1rtc`.`approvals_3budgetandliq` WHERE Approved=1 AND Liquidated<>1);';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'approvals_4checkpayment':
        
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`approvals_4checkpayment` SELECT * FROM `' . $currentyr . '_1rtc`.`approvals_4checkpayment` WHERE ReceiptReceived=0;';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'comments_5clientsonhold':
        $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`comments_5clientsonhold` WHERE YEAR(`TimeStamp`)<<' . $nextyr ;
        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`comments_5clientsonhold` (ClientNo, TimeStamp, Reason, Remarks, EncodedByNo, Hold)
SELECT ch.ClientNo, ch.TimeStamp, Reason, Remarks, EncodedByNo, Hold FROM `' . $currentyr . '_1rtc`.`comments_5clientsonhold` ch JOIN (SELECT ClientNo, MAX(TimeStamp) AS MaxTS FROM `' . $currentyr . '_1rtc`.`comments_5clientsonhold` GROUP BY ClientNo) ch2 ON ch.ClientNo=ch2.ClientNo AND ch.TimeStamp=ch2.MaxTS WHERE Hold<>0 ;';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'acctg_5branchpreapprovedbudgetspermonth':
        
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`acctg_5branchpreapprovedbudgetspermonth` (`budgetid`,`BranchNo`,`TypeID`,`Specifics`,`Remarks`,`BudgetPerMonth`,`EncodedByNo`,`TimeStamp`,`01`,`02`, `03`,`04`,`05`,`06`,`07`,`08`,`09`,`10`,`11`,`12`,`ApprovedByNo`,`ApprovedTS`) SELECT `budgetid`,`BranchNo`,`TypeID`,`Specifics`,`Remarks`,`BudgetPerMonth`,`EncodedByNo`,`TimeStamp`,BudgetPerMonth AS `01`,BudgetPerMonth AS `02`, BudgetPerMonth AS `03`,BudgetPerMonth AS `04`,BudgetPerMonth AS `05`,BudgetPerMonth AS `06`,BudgetPerMonth AS `07`,BudgetPerMonth AS `08`,BudgetPerMonth AS `09`,BudgetPerMonth AS `10`,BudgetPerMonth AS `11`,BudgetPerMonth AS `12`,`ApprovedByNo`,`ApprovedTS` FROM ' . $currentyr . '_1rtc.acctg_5branchpreapprovedbudgetspermonth WHERE `12`<>0 AND TypeID<>6;';
        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql='SELECT `budgetid`,`BranchNo`,`TypeID`,`Specifics`,`Remarks`,`BudgetPerMonth`,`EncodedByNo`,`TimeStamp`,`01`,`02`, `03`,`04`,`05`,`06`,`07`,`08`,`09`,`10`,`11`,`12`,`ApprovedByNo`,`ApprovedTS` FROM ' . $currentyr . '_1rtc.acctg_5branchpreapprovedbudgetspermonth WHERE `12`<>0 AND TypeID=6';
        if($_SESSION['(ak0)']==1002) { echo 'Run in db to update housing allowance:<br><br>'.$sql; exit();} 
        
        break;
    
case 'acctg_2prepaid':
        
        overwrite($link,'`acctg_2prepaidamort`');
        /* this can be used if data exists
        DELETE FROM `' . $nextyr . '_1rtc`.`acctg_2prepaidamort` WHERE PrepaidID IN (SELECT PrepaidID FROM `' . $nextyr . '_1rtc`.`acctg_2prepaid` WHERE Year(DatePaid)<' . $nextyr);
        DELETE FROM `' . $nextyr . '_1rtc`.`acctg_2prepaid` WHERE Year(DatePaid)<' . $nextyr;     
        */
        $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`acctg_2prepaid`;';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        $sql0='CREATE TEMPORARY TABLE amortized AS
SELECT `BranchNo`,m.`PrepaidID`,IFNULL(SUM(CASE WHEN Year(`AmortDate`)<=' . $currentyr . ' THEN  IFNULL(s.`Amount`,0) END),0) AS `TotalAmort` FROM `acctg_2prepaid` m LEFT JOIN `acctg_2prepaidamort` s ON m.PrepaidID=s.PrepaidID GROUP BY `BranchNo`,`PrepaidID`;';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='CREATE TEMPORARY TABLE PrepaidList AS
        SELECT m.PrepaidID, TRUNCATE(SUM(m.`Amount`)-IFNULL(a.`TotalAmort`,0),2) AS `NetValueThisYr` FROM `acctg_2prepaid` m LEFT JOIN `amortized` a ON m.`PrepaidID`=a.`PrepaidID`  GROUP BY m.PrepaidID HAVING Abs(`NetValueThisYr`)>2;';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        overwrite($link,'`acctg_2prepaid`');
        
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`acctg_2prepaid` SELECT * FROM `' . $currentyr . '_1rtc`.`acctg_2prepaid` WHERE PrepaidID IN (SELECT PrepaidID FROM PrepaidList);';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`acctg_2prepaidamort` SELECT * FROM `' . $currentyr . '_1rtc`.`acctg_2prepaidamort` WHERE PrepaidID IN (SELECT PrepaidID FROM PrepaidList);';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'acctg_2cvmain':
        
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`acctg_2cvmain` (`TxnID`,`VoucherNo`,`CheckNo`,`Date`,`DateofCheck`,`PayeeNo`,`Payee`,`CreditAccountID`,`Remarks`,
`TimeStamp`,`EncodedByNo`,`PostedByNo`,`Posted`) 
SELECT `TxnID`,`VoucherNo`,`CheckNo`,`Date`,`DateofCheck`,`PayeeNo`,`Payee`,`CreditAccountID`,`Remarks`,
`TimeStamp`,`EncodedByNo`,`PostedByNo`,`Posted` FROM `' . $currentyr . '_1rtc`.`acctg_4futurecvmain` WHERE YEAR(`Date`)=' . $nextyr . ';';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();

        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`acctg_2cvsub` (`TxnID`,`Particulars`,`ForInvoiceNo`,`TIN`,`DebitAccountID`,`Amount`,`TimeStamp`,`FromBudgetOf`,`BranchNo`,`EncodedByNo`) '
                . 'SELECT fvs.`TxnID`,`Particulars`,`ForInvoiceNo`,`TIN`,`DebitAccountID`,`Amount`,fvs.`TimeStamp`,`FromBudgetOf`,`BranchNo`,fvs.`EncodedByNo` FROM `' . $currentyr . '_1rtc`.`acctg_4futurevouchersub`  fvs JOIN `' . $currentyr . '_1rtc`.`acctg_4futurecvmain` fvm ON fvm.TxnID=fvs.TxnID WHERE YEAR(`Date`)=' . $nextyr . ';';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'acctg_4futurecvmain':
        
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`acctg_4futurecvmain` SELECT * FROM `' . $currentyr . '_1rtc`.`acctg_4futurecvmain` WHERE YEAR(`Date`)>' . $nextyr . ';';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();

        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`acctg_4futurevouchersub` SELECT fvs.* FROM `' . $currentyr . '_1rtc`.`acctg_4futurevouchersub`  fvs JOIN `' . $currentyr . '_1rtc`.`acctg_4futurecvmain` fvm ON fvm.TxnID=fvs.TxnID WHERE YEAR(`Date`)>' . $nextyr . ';';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'banktxns_branchdefaultbank':
        overwrite($link,'`banktxns_branchdefaultbank`');
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`banktxns_branchdefaultbank` SELECT d.* FROM `' . $currentyr . '_1rtc`.`banktxns_branchdefaultbank`d JOIN `' . $nextyr . '_1rtc`.`1branches` b on b.BranchNo=d.BranchNo WHERE Active=1;';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();

     break;
    
    case 'admin_2propertyassign':

        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`admin_2propertyassign` SELECT a.* FROM `' . $currentyr . '_1rtc`.`admin_2propertyassign` a JOIN (SELECT PropID,  MAX(AssignDate) AS MaxAssignDate FROM `' . $currentyr . '_1rtc`.`admin_2propertyassign` GROUP BY PropID ) b ON a.AssignDate=b.MaxAssignDate AND a.PropID=b.PropID;';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'payroll_22rates':
        
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`payroll_22rates`
(`IDNo`,`DateofChange`,`BasicRate`,`DeMinimisRate`,`TaxShield`,`SSS-EE`,`Philhealth-EE`,`PagIbig-EE`,`WTax`,`Remarks`,`DailyORMonthly`,`EncodedByNo`,
`TimeStamp`,`ApprovedByNo`,`ApprovalTS`)
SELECT `IDNo`, `LatestDateofChange`, IF(`LatestDorM`=0,`LatestBasicRate`,`LatestBasicRate`*2),
            IF(`LatestDorM`=0,`LatestDeMinimisRate`,`LatestDeMinimisRate`*2),
            IF(`LatestDorM`=0,`LatestTaxShield`,`LatestTaxShield`*2),`SSS-EE`, `Philhealth-EE`,`PagIbig-EE`, `WTax`,
    `Remarks`, `LatestDorM`, `EncodedByNo`, `TimeStamp`, `ApprovedByNo`, `ApprovalTS`
FROM `' . $currentyr . '_1rtc`.`payroll_20latestrates` lr WHERE Resigned=0 AND IDNo NOT IN (SELECT IDNo FROM `' . $nextyr . '_1rtc`.`payroll_22rates`);';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
        
		$sql='DROP  TEMPORARY TABLE IF EXISTS `payroll_ratesforapproval`;';
		 $stmt=$link->prepare($sql); $stmt->execute();
		 
		 $sql='CREATE TEMPORARY TABLE `payroll_ratesforapproval` AS
Select r.*, FullName, Position, Branch,
if(DailyORMonthly=1,"Semi-Monthly","Daily") AS DailyORSemiM,
if(DailyORMonthly=1,(BasicRate),(BasicRate*26.08)) AS Basic,
if(DailyORMonthly=1,(TaxShield),(TaxShield*26.08)) AS Allowance,
if(DailyORMonthly=1,(DeMinimisRate),(DeMinimisRate*26.08)) AS
DeMinimis,
(SELECT Employee FROM `payroll_0ssstable` WHERE
if(DailyORMonthly=1,((BasicRate)),((BasicRate)*26.08))
BETWEEN RangeMin AND RangeMax) AS CalcSSS,
if(DailyORMonthly=1,(DeMinimisRate),(DeMinimisRate*26.08)) AS
DeMinimis,
(SELECT Employer FROM `payroll_0ssstable` WHERE
if(DailyORMonthly=1,((BasicRate)),((BasicRate)*26.08))
BETWEEN RangeMin AND RangeMax) AS CalcSSSER,
TRUNCATE(getContriEE(if(DailyORMonthly=1,(BasicRate),(BasicRate*26.08)),"phic"),2)
AS CalcPHIC
FROM `payroll_22rates` r JOIN `attend_30currentpositions` p ON
r.IDNo=p.IDNo;';
 $stmt=$link->prepare($sql); $stmt->execute();

$sql="UPDATE ' . $nextyr . 'payroll_22rates r
join payroll_ratesforapproval ra ON r.IDNo=ra.IDNo
set r.`Philhealth-EE`=CalcPHIC,r.`SSS-EE`=CalcSSS,
r.Remarks=CONCAT(IF(r.Remarks LIKE '','', CONCAT(r.Remarks,'; ')),'with
PHIC ' . $nextyr . ' update');";
 $stmt=$link->prepare($sql); $stmt->execute();
		
        break;
    
    case 'payroll_1paydates':
        overwrite($link,'`payroll_1paydates`');       // exit();
        
        $sqlfrom=' FROM `'.$nextyr.'_1rtc`.`attend_2attendancedates` WHERE (DateToday BETWEEN \''.$currentyr . '-12-21\' AND \''.$nextyr . '-01-05\') ';
$sql0='INSERT INTO `' . $nextyr . '_1rtc`.`payroll_1paydates` (`PayrollID`,`PayrollCode`,`PayrollDate`,`FromDate`,`ToDate`,`WorkDays`,`LegalHolidays`,`SpecHolidays`,`Remarks`) '
        .' SELECT 1 AS `PayrollID`, \''.$nextyr.'-01-A\' AS `PayrollCode`, \''.$nextyr . '-01-10\' AS `PayrollDate`, '
        .' \''.$currentyr . '-12-21\' AS `FromDate`, \''.$nextyr . '-01-05\' AS `ToDate`, '
        .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND DAY(DateToday)<>6 AND TypeofDayNo=0) AS `WorkDays`, '
        .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND TypeofDayNo=2) AS `LegalHolidays`, '
        .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND TypeofDayNo=3) AS `SpecHolidays`, '
        .' (SELECT GROUP_CONCAT(remarksondates) '.$sqlfrom.' AND TypeofDayNo IN (2,3) ) AS `Remarks` ';

echo $sql0;
$stmt=$link->prepare($sql0); $stmt->execute();

$sqlfrom=' FROM `'.$nextyr.'_1rtc`.`attend_2attendancedates` WHERE (DateToday BETWEEN \''.$nextyr . '-01-06\' AND \''.$nextyr . '-01-20\') ';
$sql0='INSERT INTO `' . $nextyr . '_1rtc`.`payroll_1paydates` (`PayrollID`,`PayrollCode`,`PayrollDate`,`FromDate`,`ToDate`,`WorkDays`,`LegalHolidays`,`SpecHolidays`,`Remarks`) '
        .' SELECT 2 AS `PayrollID`, \''.$nextyr.'-01-B\' AS `PayrollCode`, \''.$nextyr . '-01-25\' AS `PayrollDate`, '
        .' \''.$nextyr . '-01-06\' AS `FromDate`, \''.$nextyr . '-01-20\' AS `ToDate`, '
        .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND DAY(DateToday)<>6 AND TypeofDayNo=0) AS `WorkDays`, '
        .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND TypeofDayNo=2) AS `LegalHolidays`, '
        .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND TypeofDayNo=3) AS `SpecHolidays`, '
        .' (SELECT GROUP_CONCAT(remarksondates) '.$sqlfrom.' AND TypeofDayNo IN (2,3) ) AS `Remarks` ';
echo $sql0;
$stmt=$link->prepare($sql0); $stmt->execute();

$months=array(2,3,4,5,6,7,8,9,10,11,12);
foreach ($months as $month){
        // 10th
        $fromdate=$nextyr . '-'.($month-1).'-21'; $todate=$nextyr . '-'.$month.'-05';
        $sqlfrom=' FROM `'.$nextyr.'_1rtc`.`attend_2attendancedates` WHERE (DateToday BETWEEN \''.$fromdate.'\' AND \''.$todate.'\') ';

        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`payroll_1paydates` (`PayrollID`,`PayrollCode`,`PayrollDate`,`FromDate`,`ToDate`,`WorkDays`,`LegalHolidays`,`SpecHolidays`,`Remarks`) '
            .' SELECT '.(($month*2)-1).' AS `PayrollID`, \''.$nextyr.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-A\' AS `PayrollCode`, \''.$nextyr . '-'.$month.'-10\' AS `PayrollDate`, '
            .' \''.$fromdate.'\' AS `FromDate`, \''.$todate.'\' AS `ToDate`, '
            .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND DAY(DateToday)<>6 AND TypeofDayNo=0) AS `WorkDays`, '
            .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND TypeofDayNo=2) AS `LegalHolidays`, '
            .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND TypeofDayNo=3) AS `SpecHolidays`, '
            .' (SELECT GROUP_CONCAT(remarksondates) '.$sqlfrom.' AND TypeofDayNo IN (2,3) ) AS `Remarks` ';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();

        // 25th
        $fromdate=$nextyr . '-'.($month).'-06'; $todate=$nextyr . '-'.$month.'-20';
        $sqlfrom=' FROM `'.$nextyr.'_1rtc`.`attend_2attendancedates` WHERE (DateToday BETWEEN \''.$fromdate.'\' AND \''.$todate.'\') ';

        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`payroll_1paydates` (`PayrollID`,`PayrollCode`,`PayrollDate`,`FromDate`,`ToDate`,`WorkDays`,`LegalHolidays`,`SpecHolidays`,`Remarks`) '
            .' SELECT '.($month*2).' AS `PayrollID`, \''.$nextyr.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-B\' AS `PayrollCode`, \''.$nextyr . '-'.$month.'-25\' AS `PayrollDate`, '
            .' \''.$fromdate.'\' AS `FromDate`, \''.$todate.'\' AS `ToDate`, '
            .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND DAY(DateToday)<>6 AND TypeofDayNo=0) AS `WorkDays`, '
            .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND TypeofDayNo=2) AS `LegalHolidays`, '
            .' (SELECT COUNT(DateToday) '.$sqlfrom.' AND TypeofDayNo=3) AS `SpecHolidays`, '
            .' (SELECT GROUP_CONCAT(remarksondates) '.$sqlfrom.' AND TypeofDayNo IN (2,3) ) AS `Remarks` ';
        if($_SESSION['(ak0)']==1002) { echo $sql0.'<br>';}
        $stmt=$link->prepare($sql0); $stmt->execute();
    }

        break;
    
    case 'payroll_26approval':
        
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`payroll_26approval` (PayrollID, CompanyNo, Approved )
SELECT PayrollID, CompanyNo, 0 AS Approved FROM `' . $currentyr . '_1rtc`.`payroll_1paydates` JOIN `' . $nextyr . '_1rtc`.`1companies` WHERE Active=1 AND CompanyNo<6;
';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'audit_2toolscountmain':
        
        $sql0='create temporary table lasttoolsdate(
        BranchNo smallint(6),
        LastDate date,
        PostedByNo smallint(6),
        AuditedByNo smallint(6))
        SELECT BranchNo, Max(Date) as LastDate, PostedByNo, AuditedByNo FROM audit_2toolscountmain  group by BranchNo;';
        $stmt=$link->prepare($sql0);
        $stmt->execute();
        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`audit_2toolscountmain`
        (`Date`,
        `Remarks`,
        `BranchNo`,
        `DateofLastCount`,
        `PostedByNo`,
        `AuditedByNo`)
        Select \''.$nextyr.'-01-01\' as Date, "Forwarded from last yr" as Remarks, ld.BranchNo, ld.LastDate as DateofLastCount, PostedByNo, AuditedByNo from lasttoolsdate ld;';
        $stmt=$link->prepare($sql0);
        $stmt->execute();

        $sql0='INSERT INTO `' . $nextyr . '_1rtc`.`audit_2toolscountsub`
        (`CountID`,
        `ToolID`,
        `Count`,
        `EncodedByNo`,
        `TimeStamp`,
        `Remarks`)
        Select newtm.CountID, ts.ToolID, ts.Count, ts.EncodedByNo, ts.`TimeStamp`, ts.Remarks from audit_2toolscountmain tm join audit_2toolscountsub ts on tm.CountID=ts.CountID join lasttoolsdate ld on ld.BranchNo=tm.BranchNo and ld.LastDate=tm.Date join `' . $nextyr . '_1rtc`.`audit_2toolscountmain` newtm on newtm.BranchNo=tm.BranchNo;';
        $stmt=$link->prepare($sql0);
        $stmt->execute();
        
        break;
    
    case 'invty_1beginv':
        
        $sql0='CREATE TEMPORARY TABLE yrendinv AS 
            SELECT e.`ItemCode`, e.`BranchNo`, TRUNCATE(e.EndInvToday,2) AS `BegInv`, IF(Pseudobranch=2, UnitCost,PriceLevel1) AS `BegCost`, PriceLevel1, PriceLevel2, PriceLevel3, PriceLevel4, PriceLevel5 FROM invty_21endinv e LEFT JOIN `invty_5latestminprice` lmp on e.ItemCode=lmp.ItemCode LEFT JOIN invty_52latestcost lc ON e.ItemCode=lc.ItemCode JOIN `1branches` b ON b.BranchNo=e.BranchNo
WHERE Pseudobranch<>1 AND b.Active<>0 ';
        $stmt=$link->prepare($sql0);$stmt->execute();
        
        $sql0='UPDATE `'.$nextyr.'_1rtc`.`invty_1beginv` b JOIN `yrendinv` y ON b.ItemCode=y.ItemCode and b.BranchNo=y.BranchNo
 SET b.BegInv=y.BegInv, b.BegCost=IFNULL(y.BegCost,0), BegPriceLevel1=IFNULL(PriceLevel1,0), BegPriceLevel2=IFNULL(PriceLevel2,0), BegPriceLevel3=IFNULL(PriceLevel3,0), BegPriceLevel4=IFNULL(PriceLevel4,0), BegPriceLevel5=IFNULL(PriceLevel5,0) ';
        $stmt=$link->prepare($sql0);$stmt->execute();

        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`invty_1beginv`
        (`ItemCode`,`BranchNo`,`BegInv`,`BegCost`,`BegPriceLevel1`,`BegPriceLevel2`,`BegPriceLevel3`,`BegPriceLevel4`,`BegPriceLevel5`)  
        SELECT y.`ItemCode`,y.`BranchNo`,y.`BegInv`,y.`BegCost`,PriceLevel1, PriceLevel2, PriceLevel3, PriceLevel4, PriceLevel5 FROM `'.$nextyr.'_1rtc`.`invty_1beginv` b RIGHT JOIN `yrendinv` y 
        ON b.ItemCode=y.ItemCode and b.BranchNo=y.BranchNo WHERE b.ItemCode IS NULL;';
        $stmt=$link->prepare($sql0);$stmt->execute();
        
        break;
    
    case 'invty_1beginvDEFECTIVE':
        
        $sql0='DELETE FROM  `'.$nextyr.'_1rtc`.`invty_4adjust` WHERE `AdjNo` LIKE "BegDefective%";'; $stmt0=$link->prepare($sql0);$stmt0->execute();
        $sql0='DELETE FROM  `'.$nextyr.'_1rtc`.`invty_4adjust` WHERE `AdjNo` LIKE "BegFromGood%";'; $stmt0=$link->prepare($sql0);$stmt0->execute();
		$sql0='DELETE FROM  `'.$nextyr.'_1rtc`.`invty_4adjust` WHERE `AdjNo` LIKE "BegForCheckUp%";'; $stmt0=$link->prepare($sql0);$stmt0->execute();
        $sql0='CREATE TEMPORARY TABLE Defective AS SELECT Defective AS D, a.BranchNo,a.ItemCode, SUM(Qty) as DefectiveQty, UnitPrice, SerialNo FROM invty_20uniallposted as a JOIN `1branches` b ON b.BranchNo=a.BranchNo where Date is not null  AND Defective IN (1,2) AND Active<>0 group by a.BranchNo,a.ItemCode, Defective HAVING DefectiveQty<>0';
                
                //. 'as SELECT 1 AS D, a.BranchNo,a.ItemCode, SUM(Qty) as DefectiveQty, UnitPrice, SerialNo FROM invty_20uniallposted as a JOIN `1branches` b ON b.BranchNo=a.BranchNo where Date is not null  AND Defective=1 AND Active<>0 group by a.BranchNo,a.ItemCode HAVING DefectiveQty<>0 UNION SELECT 2 AS D,a.BranchNo,a.ItemCode, SUM(Qty) as DefectiveQty, UnitPrice, SerialNo FROM invty_20uniallposted as a JOIN `1branches` b ON b.BranchNo=a.BranchNo where Date is not null  AND Defective=2 AND Active<>0 group by a.BranchNo,a.ItemCode HAVING DefectiveQty<>0' ;
        $stmt0=$link->prepare($sql0);$stmt0->execute();
        $sql='SELECT BranchNo FROM Defective GROUP BY BranchNo;'; $stmt=$link->query($sql); $result=$stmt->fetchAll();
        foreach ($result as $branchadj){ //SET AS DEFECTIVE
           $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`invty_4adjust` (`Date`, `AdjNo`,`TimeStamp`,`BranchNo`,`EncodedByNo`,`PostedByNo`) 
     SELECT \''.$nextyr.'-01-01\',concat(\'BegDefective\',LPAD('.$branchadj['BranchNo'].', 2, \'0\')) as `AdjNo`, Now(), '.$branchadj['BranchNo'].', '.$_SESSION['(ak0)'].', '.$_SESSION['(ak0)'];
        $stmt0=$link->prepare($sql0);$stmt0->execute();
        $sql1='SELECT TxnID from `'.$nextyr.'_1rtc`.`invty_4adjust` WHERE `AdjNo` LIKE concat(\'BegDefective\',LPAD('.$branchadj['BranchNo'].', 2, \'0\'))';
        $stmt1=$link->query($sql1); $result1=$stmt1->fetch(); $txnid=$result1['TxnID'];
        $sql2='INSERT INTO `'.$nextyr.'_1rtc`.`invty_4adjustsub` (`TxnID`,`ItemCode`,`Qty`,`UnitPrice`,`SerialNo`,`Defective`,`TimeStamp`,`EncodedByNo`)
        SELECT '.$txnid.', ItemCode, DefectiveQty, UnitPrice, SerialNo, 1, Now(), '.$_SESSION['(ak0)'].' FROM Defective WHERE BranchNo='.$branchadj['BranchNo'].' AND D=1 ';
		// echo $sql2; exit();
        $stmt2=$link->prepare($sql2);$stmt2->execute();
		$sql003='INSERT INTO `'.$nextyr.'_1rtc`.`invty_4adjust` (`Date`, `AdjNo`,`TimeStamp`,`BranchNo`,`EncodedByNo`,`PostedByNo`) 
     SELECT \''.$nextyr.'-01-01\',concat(\'BegForCheckUp\',LPAD('.$branchadj['BranchNo'].', 2, \'0\')) as `AdjNo`, Now(), '.$branchadj['BranchNo'].', '.$_SESSION['(ak0)'].', '.$_SESSION['(ak0)'];
        $stmt003=$link->prepare($sql003);$stmt003->execute();
		$sql03='SELECT TxnID from `'.$nextyr.'_1rtc`.`invty_4adjust` WHERE `AdjNo` LIKE concat(\'BegForCheckUp\',LPAD('.$branchadj['BranchNo'].', 2, \'0\'))';
        $stmt03=$link->query($sql03); $result03=$stmt03->fetch(); $txnid03=$result03['TxnID'];
		$sql3='INSERT INTO `'.$nextyr.'_1rtc`.`invty_4adjustsub` (`TxnID`,`ItemCode`,`Qty`,`UnitPrice`,`SerialNo`,`Defective`,`TimeStamp`,`EncodedByNo`)
        SELECT '.$txnid03.', ItemCode, DefectiveQty, UnitPrice, SerialNo, 2, Now(), '.$_SESSION['(ak0)'].' FROM Defective WHERE BranchNo='.$branchadj['BranchNo'].' AND D=2 ';
		// echo $sql3; exit();
        $stmt3=$link->prepare($sql3);$stmt3->execute();
      
   }
   foreach ($result as $branchadj){ //REMOVE FROM GOOD ITEM
      $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`invty_4adjust` (`Date`, `AdjNo`,`TimeStamp`,`BranchNo`,`EncodedByNo`,`PostedByNo`) 
SELECT \''.$nextyr.'-01-01\',concat(\'BegFromGood\',LPAD('.$branchadj['BranchNo'].', 2, \'0\')) as `AdjNo`, Now(), '.$branchadj['BranchNo'].', '.$_SESSION['(ak0)'].', '.$_SESSION['(ak0)'];
      $stmt0=$link->prepare($sql0);$stmt0->execute();
      $sql1='SELECT TxnID from `'.$nextyr.'_1rtc`.`invty_4adjust` WHERE `AdjNo` LIKE concat(\'BegFromGood\',LPAD('.$branchadj['BranchNo'].', 2, \'0\'))';
      $stmt1=$link->query($sql1); $result1=$stmt1->fetch(); $txnid=$result1['TxnID'];
      $sql2='INSERT INTO `'.$nextyr.'_1rtc`.`invty_4adjustsub` (`TxnID`,`ItemCode`,`Qty`,`UnitPrice`,`SerialNo`,`Defective`,`TimeStamp`,`EncodedByNo`)
      SELECT '.$txnid.', ItemCode, sum(DefectiveQty*-1), UnitPrice, SerialNo, 0, Now(), '.$_SESSION['(ak0)'].' FROM Defective WHERE BranchNo='.$branchadj['BranchNo'].' GROUP BY ItemCode ';
      $stmt2=$link->prepare($sql2);$stmt2->execute();
   }
      
        
        break;
    
    case 'incusyrtotals':
        $link=connect_db($currentyr.'_1rtc',1);
        $sql='ALTER TABLE `hist_incus`.`incusyrtotals` ADD `'.$currentyr.'` DOUBLE NOT NULL DEFAULT \'0\' AFTER `'.($currentyr-1).'`;'; $stmt=$link->prepare($sql); $stmt->execute();
        $sql='UPDATE `hist_incus`.`incusyrtotals` SET `'.$currentyr.'`=0;'; $stmt=$link->prepare($sql); $stmt->execute();
        $sql='CREATE TEMPORARY TABLE incusthisyr AS SELECT m.ClientNo,SUM(Qty*UnitPrice) as TotalYrSales FROM `invty_2sale` m join `invty_2salesub` s on m.TxnID=s.TxnID WHERE (m.ClientNo>10001 and m.ClientNo not in (10000,10001,10004,15001,15002,15003,15004,15005)) GROUP BY ClientNo HAVING TotalYrSales>0;';
        $stmt=$link->prepare($sql); $stmt->execute();
        $sql='INSERT INTO hist_incus.incusyrtotals (`ClientNo`) SELECT c.ClientNo FROM incusthisyr c LEFT JOIN hist_incus.incusyrtotals h ON c.ClientNo=h.ClientNo WHERE h.ClientNo IS NULL;';
        $stmt=$link->prepare($sql); $stmt->execute();
        $sql='UPDATE `hist_incus`.`incusyrtotals` h join `incusthisyr` c on c.ClientNo=h.ClientNo SET h.`'.$currentyr.'`=truncate(c.TotalYrSales,0);';
        $stmt=$link->prepare($sql); $stmt->execute();

        break;
    
    case 'weightedavecost':
        // similar to /acctg/maketables/makeweightedavecosts.php, but for new year
        $link=connect_db($currentyr.'_1rtc',1);
        
        $sql='TRUNCATE `' . $nextyr . '_static`.`invty_weightedavecost`'; $stmt=$link->prepare($sql); $stmt->execute();
        $sql0='INSERT INTO `' . $nextyr . '_static`.`invty_weightedavecost` (`ItemCode`) SELECT `ItemCode` FROM `' . $nextyr . '_1rtc`.`invty_1items`;';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        $sql='CREATE TEMPORARY TABLE `' . $currentyr . '_static`.`invty_unibegmrrcostqty` (
                ItemCode smallint(6) not null,
                TotalValue double not null, Qty double not null
                )

            SELECT 
                `i`.`ItemCode` AS `ItemCode`,
                (`wac`.`12` * SUM(`a`.`Qty`)) AS `TotalValue`, SUM(`a`.`Qty`) AS `Qty`
            FROM
                (`invty_1items` i LEFT JOIN `' . $currentyr . '_static`.`invty_unialltxns` `a` ON ((`i`.`ItemCode` = `a`.`ItemCode`)))
                JOIN `' . $currentyr . '_static`.`invty_weightedavecost` wac ON `i`.`ItemCode` = `wac`.`ItemCode`
                JOIN `1branches` b ON b.BranchNo=a.BranchNo 
            WHERE
                ((`b`.`Pseudobranch`=2) and (`i`.`CatNo` <> 1) AND MONTH(a.`Date`)<12) 
            GROUP BY `i`.`ItemCode`
            UNION ALL    
            SELECT 
                `s`.`ItemCode` AS `ItemCode`,
                (`s`.`UnitCost` * `s`.`Qty`) AS `TotalValue`, SUM(`s`.`Qty`) AS `Qty`
            FROM
                ((`invty_1items` i
                join `invty_2mrrsub` s ON ((`i`.`ItemCode` = `s`.`ItemCode`)))
                join `invty_2mrr` m ON ((`s`.`TxnID` = `m`.`TxnID`)))
                JOIN `1branches` b ON b.BranchNo=m.BranchNo 
            WHERE
                ((`i`.`CatNo` <> 1)
                    and (`b`.`Pseudobranch`=2) and MONTH(`m`.`Date`)=12) 
            UNION ALL    
            SELECT  
                `rlc`.`ItemCode` AS `ItemCode`,
                (`rlc`.`UnitCost` * `rlc`.`Qty`) AS `TotalValue`, SUM(`rlc`.`Qty`) AS `Qty`
            FROM
                `invty_500repackforlatestcost` `rlc`
            WHERE
                (Month(`rlc`.`Date`)=12)';
        
        
        $stmt0=$link->prepare('DROP TEMPORARY TABLE IF EXISTS `' . $currentyr . '_static`.`invty_unibegmrrcostqty`;'); $stmt0->execute();
        $stmt=$link->prepare($sql);$stmt->execute();    
        
            
        $stmt0=$link->prepare('DROP TABLE IF EXISTS  `' . $currentyr . '_static`.`temp_wtdavecost`;');$stmt0->execute();

        $sql='CREATE TABLE  `' . $currentyr . '_static`.`temp_wtdavecost` (
        ItemCode smallint(6) not null,
        WtdAveCost double not null
        )
        SELECT bm.ItemCode, If(Sum(`Qty`)<>0,(round((Sum(`TotalValue`)/Sum(`Qty`)),2)),`12`) AS WtdAveCost
        FROM  `' . $currentyr . '_static`.`invty_unibegmrrcostqty` bm left join `' . $currentyr . '_static`.`invty_weightedavecost` wac on bm.ItemCode=wac.ItemCode GROUP BY bm.ItemCode;';
        $stmt=$link->prepare($sql);$stmt->execute();        

        // update wac of those with transactions
        $sql='UPDATE `' . $nextyr . '_static`.`invty_weightedavecost` wac JOIN `' . $currentyr . '_static`.`temp_wtdavecost` wc on wac.ItemCode=wc.ItemCode set `00`= wc.WtdAveCost';
        $stmt=$link->prepare($sql);$stmt->execute();

        // make wac same as last month for no transactions
        $sql='UPDATE `' . $nextyr . '_static`.`invty_weightedavecost` wac LEFT JOIN `' . $currentyr . '_static`.`temp_wtdavecost` wc on wac.ItemCode=wc.ItemCode '
                . ' SET `00`= `wac`.`12` WHERE wc.ItemCode IS NULL';
        $stmt=$link->prepare($sql);$stmt->execute();
       
        $stmt0=$link->prepare('DROP TEMPORARY TABLE IF EXISTS  `' . $currentyr . '_static`.`temp_wtdavecost`;');$stmt0->execute();    
            
              
        break;
    
    case 'invty_5lastminprice':
        $link=connect_db($currentyr.'_1rtc',1);
        overwrite($link,'`invty_5lastminprice`');
    
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`invty_5lastminprice`
        (`ItemCode`, `Date`, PriceLevel1, PriceLevel2, PriceLevel3, PriceLevel4, PriceLevel5, `EncodedByNo`)
        SELECT `ItemCode`, `Date`, PriceLevel1, PriceLevel2, PriceLevel3, PriceLevel4, PriceLevel5, '.$_SESSION['(ak0)'].' as `EncodedByNo`
        FROM `invty_5latestminprice`;
        ';
        $stmt=$link->prepare($sql0); $stmt->execute();
        header("Location:../invty/makelatestcostandminprice.php");        
        
        break;
    
    case 'soldlastyear':
        
        $link=connect_db($currentyr.'_1rtc',1);
        $sql='Delete from `'.$nextyr.'_static`.`invty_soldlastyear`';
        $stmt=$link->prepare($sql); $stmt->execute();
        $sql0='INSERT INTO `'.$nextyr.'_static`.`invty_soldlastyear`
(`itemcode`, `branchno`, `soldondate`, `qtysold`)
Select ItemCode, BranchNo, m.`Date` as SoldonDate, Qty from `invty_2sale` m join `invty_2salesub` s on m.TxnID=s.TxnID;';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        
        break;
    
    case 'Interbranch_NotYetReceived':
        
        $sql='DELETE FROM '.$nextyr.'_1rtc.invty_2transfer WHERE YEAR(DateOUT)<'.$nextyr.';'; $stmt=$link->prepare($sql); $stmt->execute();
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`invty_2transfer`
    (`DateOUT`,`DateIN`,`TransferNo`,`ToBranchNo`,`ForRequestNo`,`Remarks`,`BranchNo`,`FROMTimeStamp`,`FROMEncodedByNo`,`TOTimeStamp`,`TOEncodedByNo`,`PostedByNo`,`Posted`,
    `Checked`,`txntype`,`PostedIn`,`PostedInByNo`,`ReqTxnID`)
    SELECT `DateOUT`,`DateIN`,`TransferNo`,`ToBranchNo`,`ForRequestNo`,`Remarks`,`BranchNo`,`FROMTimeStamp`,`FROMEncodedByNo`,`TOTimeStamp`,`TOEncodedByNo`,`PostedByNo`,
    `Posted`,`Checked`,`txntype`,`PostedIn`,`PostedInByNo`,0 FROM '.$currentyr.'_1rtc.invty_2transfer tm WHERE (DateIN IS NULL OR YEAR(DateIN)>'.$currentyr.')
    AND tm.ToBranchNo in (Select BranchNo from '.$currentyr.'_1rtc.1branches)
    AND (SELECT COUNT(TxnID) FROM '.$currentyr.'_1rtc.invty_2transfersub ts WHERE tm.TxnID=ts.TxnID)>0;';
        $stmt=$link->prepare($sql0);  $stmt->execute();
        
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`invty_2transfersub`
    (`TxnID`, `ItemCode`,`QtySent`,`QtyReceived`,`UnitCost`,`UnitPrice`,`SerialNo`,`Defective`,`FROMTimeStamp`,`FROMEncodedByNo`,`TOTimeStamp`,`TOEncodedByNo`)
    SELECT ntm.`TxnID`,`ItemCode`,`QtySent`,`QtyReceived`,`UnitCost`,`UnitPrice`,`SerialNo`,`Defective`,ts.`FROMTimeStamp`,ts.`FROMEncodedByNo`,ts.`TOTimeStamp`,
    ts.`TOEncodedByNo` FROM '.$nextyr.'_1rtc.invty_2transfer ntm JOIN '.$currentyr.'_1rtc.invty_2transfer tm ON ntm.TransferNo=tm.TransferNo AND ntm.BranchNo=tm.BranchNo JOIN '.$currentyr.'_1rtc.invty_2transfersub ts on tm.TxnID=ts.TxnID where ntm.txntype<>12;';  $stmt=$link->prepare($sql0);  $stmt->execute();
    
        $sql='DELETE FROM `'.$nextyr.'_1rtc`.`acctg_2txfrmain` WHERE YEAR(Date)<'.$nextyr.';'; $stmt=$link->prepare($sql); $stmt->execute();
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_2txfrmain` ( `Date`, FromBranchNo, EncodedByNo, PostedByNo, `TimeStamp`, CreditAccountID )
    SELECT  `DateOUT`, BranchNo, atmold.EncodedByNo, atmold.PostedByNo, atmold.`TimeStamp`, CreditAccountID 
    FROM '.$nextyr.'_1rtc.invty_2transfer itmnew JOIN acctg_2txfrsub atsold on itmnew.TransferNo=atsold.Particulars 
    JOIN `acctg_2txfrmain` atmold on atmold.TxnID=atsold.TxnID
    WHERE Year(itmnew.DateOUT)=' . $currentyr . ' GROUP BY DateOUT,BranchNo;';
       // echo $sql0; exit();
        $stmt=$link->prepare($sql0);  $stmt->execute();
        
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_2txfrsub`
     (`TxnID`,`Particulars`,`ClientBranchNo`,`DebitAccountID`,`Amount`,`OUTTimeStamp`,`OUTEncodedByNo`,`DateIN`,`INTimeStamp`,`INEncodedByNo`,`DatePaid`,`PaidViaAcctID`,`Remarks`)
     SELECT atmnew.`TxnID`,`Particulars`,
     `ClientBranchNo`,`DebitAccountID`,`Amount`,`OUTTimeStamp`,`OUTEncodedByNo`,NULL,atsold.`INTimeStamp`,atsold.`INEncodedByNo`,`DatePaid`,`PaidViaAcctID`,
     atsold.`Remarks` FROM `'.$nextyr.'_1rtc`.`acctg_2txfrmain` atmnew JOIN '.$nextyr.'_1rtc.invty_2transfer itmnew ON atmnew.FromBranchNo=itmnew.BranchNo and atmnew.Date=itmnew.DateOUT
     JOIN acctg_2txfrsub atsold on itmnew.TransferNo=atsold.Particulars WHERE Year(itmnew.DateOUT)=' . $currentyr . ' ;'; $stmt=$link->prepare($sql0);  $stmt->execute();

        break;
    
    case 'acctg_3unpdclientinvlastperiod':
        
        overwrite($link,'`acctg_3unpdclientinvlastperiod`');
    
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_3unpdclientinvlastperiod`
        (`ClientNo`, `Particulars`, `Date`, `Balance`, `ARAccount`, `BranchNo`, `TeamLeader`, `PONo`)
        SELECT `ClientNo`, `Particulars`, `Date`, truncate(`InvBalance`,2) as `Balance`, `DebitAccountID` as `ARAccount`, `BranchNo`, `TeamLeader`, `PONo` FROM acctg_33qrybalperrecpt where InvBalance>0.1 OR InvBalance<-.1;
        ';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'acctg_3unpdsuppinvlastperiod':
        overwrite($link,'`acctg_3unpdsuppinvlastperiod`');
        
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_3unpdsuppinvlastperiod`
        (`SupplierNo`, `SupplierInv`, `Date`, `Balance`, `Terms`, `APAccount`, `BranchNo`, `RCompany`)
        SELECT `SupplierNo`, CONCAT("yr",'.substr($currentyr,-2).',"-",`SupplierInv`), `Date`, `PayBalance` as `Balance`, `PayTerms` as `Terms`, `CreditAccountID` as `APAccount`,
        `BranchNo`,`RCompany` FROM `acctg_23balperinv` where PayBalance<>0;
        ';
        $stmt=$link->prepare($sql0); $stmt->execute();   
        
        break;
    
    case 'acctg_3unpdinterbranchlastperiod':
        
        overwrite($link,'`acctg_3unpdinterbranchlastperiod`');
        
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_3unpdinterbranchlastperiod`
        (`TOBranchNo`,`Particulars`,`DateOUT`,`Balance`,`ARAccount`,`FROMBranchNo`,`DateIN`)
        SELECT iblp.TOBranchNo, iblp.Particulars, iblp.DateOUT, iblp.Balance, 204 as ARAccount, iblp.FromBranchNo, iblp.DateIN
        FROM acctg_3unpdinterbranchlastperiod iblp
        WHERE iblp.DatePaid Is Null
         UNION 
        SELECT ts.ClientBranchNo, ts.Particulars, tm.Date, ts.Amount, 204 as ARAccount, tm.FromBranchNo, ts.DateIN
        FROM acctg_2txfrmain tm INNER JOIN acctg_2txfrsub ts ON tm.TxnId=ts.TxnId
        WHERE ts.DatePaid Is Null AND Year(tm.Date)=' . $currentyr . '
        ORDER BY TOBranchNo,Particulars;
        ';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'acctg_4pettycash':
        
        $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`acctg_4pettycash` WHERE YEAR(`Date`)<' . $nextyr . ';';
        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_4pettycash` SELECT * FROM `'.$currentyr.'_1rtc`.`acctg_4pettycash`;';
        $stmt=$link->prepare($sql0); $stmt->execute();
//        $sql0='DELETE FROM `' . $nextyr . '_1rtc`.`acctg_4pettycash` WHERE YEAR(`TimeStamp`)<' . $nextyr . ';';
//        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_4pettycashcount` SELECT * FROM `'.$currentyr.'_1rtc`.`acctg_4pettycashcount` WHERE PCBranchNo NOT IN '
                . '(SELECT PCBranchNo FROM `'.$nextyr.'_1rtc`.`acctg_4pettycashcount`);';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'acctg_3unclearedchecksfromlastperiod':
        overwrite($link, 'acctg_3unclearedchecksfromlastperiod');
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_3unclearedchecksfromlastperiod`
(`VoucherNo`, `CheckNo`, `DateofCheck`, `PayeeNo`, `Payee`, `AmountofCheck`, `FromAccount`, `BranchNo`,`ReleaseDate`,`ReleaseDateByNo`,`ReleaseDateTS`,`CheckReceivedBy`)
Select vm.`VoucherNo` AS `VoucherNo`, vm.`CheckNo` AS `CheckNo`,vm.`DateofCheck` AS `DateofCheck`,vm.`PayeeNo` AS `PayeeNo`, vm.`Payee` AS `Payee`,sum(vs.`Amount`) AS `AmountofCheck`, vm.`CreditAccountID` AS `FromAccount`, vs.BranchNo,`ReleaseDate`,`ReleaseDateByNo`,`ReleaseDateTS`,`CheckReceivedBy`
    from
        (`acctg_2cvmain` vm
        join `acctg_2cvsub` vs ON ((vm.`TxnID` = vs.`TxnID`)))
    where
        (isnull(vm.`Cleared`) = true)
    group by vm.`CreditAccountID` , vm.`CheckNo`, vs.BranchNo 
    union all 
select clp.`VoucherNo` AS `VoucherNo`,
        clp.`CheckNo` AS `CheckNo`,clp.`DateofCheck` AS `DateofCheck`,clp.`PayeeNo` AS `PayeeNo`, clp.`Payee` AS `Payee`, 
        sum(clp.`AmountofCheck`) AS `AmountofCheck`, clp.`FromAccount` AS `FromAccount`,clp.`BranchNo`,`ReleaseDate`,`ReleaseDateByNo`,`ReleaseDateTS`,`CheckReceivedBy` 
    from
        `acctg_3unclearedchecksfromlastperiod` clp
		 where (isnull(clp.`Cleared`) = true)
         group by `FromAccount`, clp.`CheckNo`, clp.`BranchNo`    
    order by `DateofCheck` , `CheckNo`;
';
        $stmt=$link->prepare($sql0); $stmt->execute();

        break;
    
    case 'acctg_3undepositedpdcfromlastperiod':
        
        overwrite($link,'`acctg_3undepositedpdcfromlastperiod`');
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_3undepositedpdcfromlastperiod`
        (ClientNo,CRNo,PDCBank,PDCNo,PDCBRSTN,DateofPDC,AmountofPDC,ForAccount,BranchNo,BranchSeriesNo,SaleDate,Particulars,AtOffice,OfcAcceptedByNo,AcctgAcceptedByNo,OfcAcceptTS,AcctgAcceptTS,SendToBank,SendToBankByNo,WithBank,WithBankByNo,WithBankTS)
        SELECT `up`.`ClientNo` AS ClientNo,up.CRNo,up.PDCBank,`up`.PDCNo,PDCBRSTN,`up`.DateofPDC,ifnull(`up`.`SumOfAmount`,0) as AmountofPDC,`up`.`DebitAccountID` AS ForAccount,`up`.BranchNo,`up`.BranchSeriesNo,`up`.SaleDate,`up`.`ForChargeInvNo` AS Particulars,AtOffice,OfcAcceptedByNo,AcctgAcceptedByNo,OfcAcceptTS,AcctgAcceptTS,SendToBank,SendToBankByNo,WithBank,WithBankByNo,WithBankTS
	from
        `acctg_31unionpdcs` `up`
        left join `acctg_2depositsub` `ds` ON (if(`up`.`DebitAccountID`=100,`up`.`CRNo` = `ds`.`CRNo`,`up`.`PDCNo` = `ds`.`CheckNo` AND `up`.`PDCBank` = `ds`.`CheckDraweeBank`))
            and (`up`.`BranchNo` = `ds`.`BranchNo`)
              WHERE (`up`.`DebitAccountID`=100 AND ISNULL(`ds`.`CRNo`)) OR (`up`.`DebitAccountID`<>100 AND ISNULL(`ds`.`CheckNo`)) OR isnull(`up`.`DateofPDC`)
';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        break;
    
    case 'acctg_4creditcardmain':
        overwrite($link,'`acctg_4creditcardmain`');

        $sql='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_4creditcardmain` SELECT * FROM `' . $currentyr . '_1rtc`.`acctg_4creditcardmain`;';
        $stmt=$link->prepare($sql); $stmt->execute();
        $sql='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_4creditcardsub`
        (`CreditCardNo`,`ChargeDate`,`Particulars`,`TIN`,`DebitAccountID`,`Amount`,`BranchNo`,`TimeStamp`,`EncodedByNo`,`Reconciled`)
        SELECT `CreditCardNo`,`ChargeDate`,`Particulars`,`TIN`,`DebitAccountID`,`Amount`,`BranchNo`,`TimeStamp`,`EncodedByNo`,`Reconciled` FROM ' . $currentyr . '_1rtc.acctg_4creditcardsub;';
        $stmt=$link->prepare($sql); $stmt->execute();
        
        break;
    
    case 'ChartofAccts_BegBal':
        
        $link=connect_db($currentyr.'_1rtc',1);
        overwrite($link,'`acctg_1begbal`');
    
        $sql0='CREATE TEMPORARY TABLE active AS 
        SELECT b.BranchNo AS OldBranch, b.BranchNo AS NewBranch FROM `'.$nextyr.'_1rtc`.`1branches` b WHERE b.Active=1
        UNION SELECT b.MovedBranch AS OldBranch, BranchNo AS NewBranch  FROM `'.$nextyr.'_1rtc`.`1branches` b WHERE b.Active=1 AND MovedBranch<>-1  AND Pseudobranch=0
        UNION SELECT b1.BranchNo AS OldBranch, b2.BranchNo AS NewBranch  FROM `'.$nextyr.'_1rtc`.`1branches` b1 JOIN `'.$nextyr.'_1rtc`.`1branches` b2 ON b1.CompanyNo=b2.CompanyNo AND b2.Pseudobranch=1 AND b2.BranchNo<>95 WHERE b1.Active<>1 AND b1.MovedBranch=-1 AND b1.BranchNo NOT IN (SELECT MovedBranch from `'.$nextyr.'_1rtc`.`1branches`) '; 
        $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='CREATE TEMPORARY TABLE endbal 
        (`AccountID` int(4) not null, `BegBalance` double, `BranchNo` int(4) not null)
        SELECT t.`AccountID`, ROUND(SUM(IFNULL(Amount,0)),2) AS `BegBalance`, `NewBranch` AS `BranchNo`
        FROM `'.$currentyr.'_static`.`acctg_unialltxns` t JOIN `acctg_1chartofaccounts` c ON t.AccountID=c.AccountID 
        JOIN `active` ac ON ac.OldBranch=t.BranchNo
        WHERE YEAR(`Date`)='.$currentyr.' GROUP BY c.`AccountID`, `NewBranch`;';
        $stmt=$link->prepare($sql0);
        $stmt->execute();
        // add BS accounts
            $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_1begbal` (`AccountID`, `BegBalance`, `BranchNo`, EncodedByNo, `TimeStamp`)
        Select e.`AccountID`, Sum(`BegBalance`) AS `BegBalance`, BranchNo,'.$_SESSION['(ak0)'].',NOW() from endbal e join `acctg_1chartofaccounts` c on e.AccountID=c.AccountID where c.AccountType<=11 AND c.AccountID<>602 GROUP BY e.AccountID, BranchNo;
        ';
        $stmt=$link->prepare($sql0); $stmt->execute();
        // add IS accounts
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_1begbal` (`AccountID`, `BegBalance`, `BranchNo`, EncodedByNo, `TimeStamp`)
        SELECT e.`AccountID`, 0 as `BegBalance`, e.`BranchNo`,'.$_SESSION['(ak0)'].',NOW()
        FROM endbal e join `acctg_1chartofaccounts` c on e.AccountID=c.AccountID where c.AccountType>=100 GROUP BY c.AccountID, BranchNo;
        ';
        $stmt=$link->prepare($sql0); $stmt->execute();
        // add capital
        $sql0='INSERT INTO `'.$nextyr.'_1rtc`.`acctg_1begbal` (`AccountID`, `BegBalance`, `BranchNo`, EncodedByNo, `TimeStamp`)
        SELECT 602 as `AccountID`, truncate(Sum(`BegBalance`),2) as `BegBalance`, e.`BranchNo`,'.$_SESSION['(ak0)'].',NOW()
        FROM endbal e join `acctg_1chartofaccounts` c on e.AccountID=c.AccountID where c.AccountType>=100 OR c.AccountID=602 group by `BranchNo`;
        ';
        $stmt=$link->prepare($sql0); $stmt->execute();

        $sql0='drop table if exists `'.$nextyr.'_static`.`acctg_unialltxns`'; $stmt=$link->prepare($sql0); $stmt->execute();
        $sql0='CREATE TABLE `'.$nextyr.'_static`.`acctg_unialltxns` AS SELECT "'.$nextyr.'-01-01" AS `Date`, "BegBal" AS `ControlNo`,
        "-" AS `SuppNo/ClientNo`,
        "-" AS `Supplier/Customer/Branch`,
        "Beginning Balance" AS `Particulars`,
        `bb`.`AccountID` AS `AccountID`,
        `bb`.`BranchNo` AS `BranchNo`, `bb`.`BranchNo` AS  FromBudgetOf,
        `bb`.`BegBalance` AS `Amount`,
        "DR" AS `Entry`,
        "BegBal" AS `w`,
        0 AS `TxnID`
    FROM
        `'.$nextyr.'_1rtc`.`acctg_1begbal` `bb`' ; $stmt=$link->prepare($sql0); $stmt->execute();
        
        echo 'Static Data must be updated for Month 0!'; exit();
        
        break;
    
    case 'banktxns_bankbalancespermonth':
        overwrite($link,'`banktxns_bankbalancespermonth`');
        $sql='INSERT INTO `'.$nextyr.'_1rtc`.`banktxns_bankbalancespermonth` (`AccountID`, `DateofBal`, `BalPerMonth`)
SELECT bt.AccountID, "' . $currentyr . '-12-31" AS Today, (Select truncate(Balance,2) from banktxns_banktxns bt where bt.TxnNo=li.MaxOfTxnNo) AS LastOfBalance
FROM banktxns_banktxns bt JOIN (SELECT 
        `bt`.`AccountID` AS `AccountID`, MAX(`bt`.`TxnNo`) AS `MaxOfTxnNo` FROM  `' . $currentyr . '_1rtc`.`banktxns_banktxns` `bt`
    GROUP BY `bt`.`AccountID`) li ON (bt.AccountID = li.AccountID) AND (bt.TxnNo = li.MaxOfTxnNo) WHERE bt.AccountID<>0
GROUP BY bt.AccountID;'; 
        $stmt=$link->prepare($sql); $stmt->execute();
        
        break;
    
        case 'approvals_2requestbudget': //boarding house
        $sql0='INSERT INTO '.$nextyr.'_1rtc.approvals_2requestbudget
SELECT * FROM '.$currentyr.'_1rtc.approvals_2requestbudget WHERE
DATE_ADD(`Date`, INTERVAL Duration Month)>\''.$currentyr.'-12-31\';';
        
        $stmt=$link->prepare($sql0); $stmt->execute();
        break;

    case 'attend_2changebranchgroup':
        
        overwrite($link,'`attend_2changebranchgroup`');
        
        

        $sql='INSERT INTO `'.$nextyr.'_1rtc`.`attend_2changebranchgroup` (DateofChange,BranchNo,IDNo,PositionID,Remarks,EncodedByNo,TimeStamp) 
        select bg.DateofChange,bg.BranchNo,IDNo,PositionID,Remarks,'.$_SESSION['(ak0)'].',NOW() from `' . $currentyr . '_1rtc`.`attend_1branchgroups` bg JOIN `' . $currentyr . '_1rtc`.attend_30currentpositions cp ON bg.TeamLeader=cp.IDNo
        UNION
        select bg.DateofChange,bg.BranchNo,IDNo,PositionID,Remarks,'.$_SESSION['(ak0)'].',NOW() from `' . $currentyr . '_1rtc`.`attend_1branchgroups` bg JOIN `' . $currentyr . '_1rtc`.attend_30currentpositions cp ON bg.SAM=cp.IDNo
        UNION
        select bg.DateofChange,bg.BranchNo,IDNo,PositionID,Remarks,'.$_SESSION['(ak0)'].',NOW() from `' . $currentyr . '_1rtc`.`attend_1branchgroups` bg JOIN `' . $currentyr . '_1rtc`.attend_30currentpositions cp ON bg.CNC=cp.IDNo UNION
        select bg.DateofChange,bg.BranchNo,IDNo,PositionID,Remarks,'.$_SESSION['(ak0)'].',NOW() from `' . $currentyr . '_1rtc`.`attend_1branchgroups` bg JOIN `' . $currentyr . '_1rtc`.attend_30currentpositions cp ON bg.OpsSpecialist=cp.IDNo;';


        $stmt=$link->prepare($sql); $stmt->execute();
        
        break;
    
    case 'hr_1incentivemain':
        
        $month=1;
        if($month<=12){
            $sql='INSERT INTO `'.$nextyr.'_1rtc`.`hr_1incentivemain` (`TxnID`, `MonthNo`,`Posted`, `Approved`, `Sent`) VALUES ('.$month.', '.$month.', 0, 0, 0);';
            $stmt=$link->prepare($sql); $stmt->execute();
         $month++;   
        }
        
        break;
    
    case 'calllogs_3armain':
        
        overwrite($link,'`calllogs_3armain`');

        $sql0='DROP TEMPORARY TABLE IF EXISTS actionnewyr'; $stmt0=$link->prepare($sql0);$stmt0->execute();
        $sql0='CREATE TEMPORARY TABLE actionnewyr AS SELECT `ARIDNo`, s.* FROM `' . $currentyr . '_1rtc`.`calllogs_3armain` m JOIN `' . $currentyr . '_1rtc`.`calllogs_3arsub` s ON m.TxnID=s.TxnID WHERE YEAR(ActionDate)>' . $currentyr  ;
        $stmt0=$link->prepare($sql0);$stmt0->execute();

        $sql='INSERT INTO `'.$nextyr.'_1rtc`.`calllogs_3armain` (`ARIDNo`,`Date`,`EncodedByNo`,`TimeStamp`)
        SELECT `ARIDNo`, \''.$nextyr.'-01-01\' AS `Date`, `EncodedByNo`, `TimeStamp` FROM actionnewyr GROUP BY `ARIDNo`;
        ';
        $stmt=$link->prepare($sql); $stmt->execute();

        $sql='INSERT INTO `'.$nextyr.'_1rtc`.`calllogs_3arsub` (`TxnID`,`EncodedByNo`,`TimeStamp`,`ClientNo`,`ContactPerson`,`Position`,`ContactNumber`,`Report`,`Action`,`ActionDate`)
        SELECT m.`TxnID`,a.`EncodedByNo`,a.`TimeStamp`,`ClientNo`,`ContactPerson`,`Position`,`ContactNumber`,`Report`,`Action`,`ActionDate` FROM actionnewyr a JOIN `'.$nextyr.'_1rtc`.`calllogs_3armain` m ON m.`ARIDNo`=a.`ARIDNo`;
        ';
        $stmt=$link->prepare($sql); $stmt->execute();
        
        break;
    
     case 'it_list':
         overwrite($link,'`it_list`');
         $sql='INSERT INTO '.$nextyr.'_1rtc.it_list SELECT * FROM  '. $currentyr . '_1rtc.it_list WHERE Status<>2';
         $stmt=$link->prepare($sql); $stmt->execute();
         break;
    
    case 'acctg_6targetscores':
        overwrite($link,'`acctg_6targetscores`');
        $sql='INSERT INTO '.$nextyr.'_1rtc.acctg_6targetscores (BranchNo, Net, Score, MonthNo, DisplayType, CashSales, ClearedCollections, OverdueAR, UndepPDC, Units, EncodedByNo, `TimeStamp`)
SELECT BranchNo, Net, Score, 0 AS MonthNo, DisplayType, CashSales, ClearedCollections, OverdueAR, UndepPDC, Units, EncodedByNo, `TimeStamp` FROM '.$currentyr.'_1rtc.acctg_6targetscores WHERE DisplayType=1 AND MonthNo=12;';
        $stmt=$link->prepare($sql); $stmt->execute();
        break;
    
    case 'UpdateBranchClass':
	$sql0='CREATE TEMPORARY TABLE monthlysales AS
	SELECT 
        MONTH(`Date`) AS `Month`, s.BranchNo,
        SUM(`Amount`) AS `MonthlySales`
    FROM
        '.$currentyr.'_1rtc.`acctg_61unisalereturn` s JOIN '.$currentyr.'_1rtc.`1branches` b ON b.BranchNo=s.BranchNo
    WHERE
        b.Pseudobranch=0
    GROUP BY MONTH(`Date`), s.BranchNo;';
	$stmt=$link->prepare($sql0); $stmt->execute();
	
	$sql0='CREATE TEMPORARY TABLE monthlysalesper AS
    SELECT ms.BranchNo, 
    (SELECT ClassID FROM '.$currentyr.'_1rtc.0branchclass WHERE AVG(`MonthlySales`)>=CutOffMin ORDER BY CutOffMin DESC LIMIT 1) AS `CalculatedClassID` FROM monthlysales ms
    GROUP BY ms.BranchNo;';
	$stmt=$link->prepare($sql0); $stmt->execute();
	
	$sql0='UPDATE '.$nextyr.'_1rtc.1branches b JOIN monthlysalesper msp ON b.BranchNo=msp.BranchNo SET ClassLastYr=CalculatedClassID;';
	$stmt=$link->prepare($sql0); $stmt->execute();
	break;
    
    case 'EditProtectedDataofLastYr':
        
        $sql='UPDATE `permissions_2allprocesses` SET `AllowedPos` = NULL , `AllowedPerID` = NULL WHERE `ProcessID` IN (678,6781);';
        $stmt=$link->prepare($sql); $stmt->execute();
        
        break;
    
    case 'eos_2vtoqtrmain':
    
        overwrite($link,'`eos_2vtoqtrmain`');
        $sql='INSERT INTO '.$nextyr.'_1rtc.eos_2vtoqtrmain (`VTOQtrId`,`QtrFutureDate`)
                SELECT 1 AS VTOQtrId, LAST_DAY(\''.$nextyr.'-03-01\') AS QtrFutureDate
                UNION
                SELECT 2 AS VTOQtrId, LAST_DAY(\''.$nextyr.'-06-01\') AS QtrFutureDate
                UNION
                SELECT 3 AS VTOQtrId, LAST_DAY(\''.$nextyr.'-09-01\') AS QtrFutureDate
                UNION
                SELECT 4 AS VTOQtrId, LAST_DAY(\''.$nextyr.'-12-01\') AS QtrFutureDate;';
        $stmt=$link->prepare($sql); $stmt->execute();
        break;
    
}

$sql0='UPDATE 1_gamit.sysadmin_0startofyr SET StatusRemarks=CONCAT("DONE - ",IFNULL(StatusRemarks,"")),TimeStamp=NOW() WHERE tblInitialized="'.$w.'";';
    $stmt=$link->prepare($sql0); $stmt->execute();
	header("Location:sendtonewyronebyone.php");
}

?><br>
OTHER NEW YEAR ADJUSTMENTS:<br><br><br>
<ol>
    <li>Future payroll adjustments must be populated.</li>
    <li>Update /backendphp/logincodes/varpositions.php to close old years.  Latest year must be open to wh super, planner, AR, audit, acctg.</li>
    <li>Hide old database from specific positions.</li>    
    <li>Update all cron files.</li> 
</ol>