<?php
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

if (!allowedToOpen(626,'1rtc')){ echo 'No permission'; exit(); }
include_once('../switchboard/contents.php');

    $title='Resign Employee from System';
    $resignid=!isset($_POST['IDNo'])?'':intval($_POST['IDNo']);
    $which=!isset($_POST['w'])?'EnterInfo':($_POST['w']);
    
    
switch ($which) {
    
    case 'SetInactive':
    case 'SetActive':
        $sql='UPDATE `1_gamit`.`1rtcusers` SET `Active` = '.($_REQUEST['w']=='SetInactive'?'0':'1').', `uphashmayasin` = "prelimresign" WHERE (`IDNo` = '.$resignid.')';
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:resignemployee.php?done=".($_REQUEST['w']=='SetInactive'?'2':'3')."");
        break;
    
    case 'Resign':
        //DELETE in AllowedPerID
        $sql='';
        $columnnames=array('DateResigned','Resigned?','ResignedWithClearance','ResignReason');
		$sql='SELECT ProcessID, AllowedPerID FROM `permissions_2allprocesses` WHERE AllowedPerID LIKE "%'.$_POST["IDNo"].'%"';
		$stmt=$link->query($sql); $res=$stmt->fetchAll();
		foreach ($res as $row){
			$arr = array_diff(explode(",",$row['AllowedPerID']),array($_POST["IDNo"]));
			$sql1='UPDATE `permissions_2allprocesses` SET `AllowedPerID`='.(!empty($arr)?"'".implode(',',$arr)."'":'NULL').' WHERE ProcessID='.$row['ProcessID'].';';
			$stmt=$link->prepare($sql1); $stmt->execute();
		}
		
		$sql='';
        foreach ($columnnames as $col) { $sql=$sql. '`'.$col . '`=\'' .addslashes($_POST[$col]) . '\', '; }
        $sql='UPDATE `1_gamit`.`0idinfo` SET ' . $sql.'`EncodedByNo`=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now(),EmpStatus=IF('.$_POST['ResignedWithClearance'].'=1,2,3) where IDNo='.$resignid;
		// echo $sql; exit();
        //if ($ako==1002){ echo $sqll; exit();}
        $stmt=$link->prepare($sql); $stmt->execute();
        
		$sql='UPDATE `1employees` SET `Resigned`=' . addslashes($_POST['Resigned']).'  where IDNo='.$resignid; //echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
		
		
        $sql='DELETE `attend_2attendance`.* FROM `attend_2attendance` join 1employees where `Resigned`=1 and `attend_2attendance`.`DateToday`>\''. $_POST['DateResigned'].'\' and `attend_2attendance`.`IDNo`=\''.$resignid.'\'';
        $stmt=$link->prepare($sql);$stmt->execute();
		
        $sql='DELETE pu.* FROM `1_gamit`.`1rtcusers` pu JOIN `1employees` e ON pu.IDNo=e.IDNo WHERE `Resigned`=1 AND pu.IDNo='.$resignid;
        $stmt=$link->prepare($sql); $stmt->execute();

		//delete future payday adjustments
		$sql='DELETE spda.* FROM payroll_21scheduledpaydayadjustments spda JOIN payroll_1paydates pd ON spda.PayrollID=pd.PayrollID WHERE PayrollDate>CURDATE() AND IDNo='.$resignid;
        $stmt=$link->prepare($sql); $stmt->execute();
		
        header("Location:resignemployee.php?done=1");
        break;
    
	case 'RevertRecord':
            include_once $path.'/acrossyrs/commonfunctions/fxngenrandpass.php';
            include_once $path.'/acrossyrs/commonfunctions/hashandcrypt.php';
        
		$sql='';
        $sql='UPDATE `1_gamit`.`0idinfo` SET DateResigned=NULL,EmpStatus=0,`Resigned?`=0,ResignedWithClearance=NULL,ResignReason=NULL, `EncodedByNo`=\''.$_SESSION['(ak0)'].'\', `TimeStamp`=Now() where IDNo='.$resignid;
        $stmt=$link->prepare($sql); $stmt->execute();
        
		$sql='UPDATE `1employees` SET `Resigned`=0 where IDNo='.$resignid; //echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
		
       $newhash=generateHash($resignid);
       $saltforid=generateSaltforid(9);
       $progcookie=generatePassword(45);
       //$email=($_POST['RTCEmail']<>'@1rotarytrading.com')?$_POST['RTCEmail']:null; `Email`=\''.$email.'\',
        $sql='Insert into `1_gamit`.`1rtcusers` set `IDNo`='.$resignid .',`uphashmayasin`=\''.$newhash.'\', `saltforid`=\''.$saltforid.'\',`ProgCookie`=\''.$progcookie.'\', EncodedByNo='.$_SESSION['(ak0)'];
        $stmt=$link->prepare($sql);
       $stmt->execute();
		
        header("Location:resignemployee.php");
        break;
    
    default:
        
		$sqlresigned = "select e.`IDNo` AS `IDNo`,concat(e.`Nickname`,' ',e.`FirstName`,' ',e.`SurName`,' - ',b.`Branch`,' (',b.`BranchNo`,')') AS `NameandBranch` from ((`1employees` as e join `attend_1defaultbranchassign` as d on((e.`IDNo` = d.`IDNo`))) join `1branches` as b on((d.`DefaultBranchAssignNo` = b.`BranchNo`))) JOIN 1_gamit.1rtcusers pu ON e.IDNo=pu.IDNo where (e.`IDNo` > 1002) and (pu.Active=0) order by `NameandBranch`;";
		$stmt = $link->query($sqlresigned);
	
		echo '<datalist id="employeeidresigned">';
			while($row = $stmt->fetch()) {
				echo "<option value='". $row['IDNo']. "'>" . $row['NameandBranch'] ."</option>";
			}
		echo '</datalist>';
		
		
		$sqlrevert = "select e.`IDNo` AS `IDNo`,concat(e.`Nickname`,' ',e.`FirstName`,' ',e.`SurName`) AS `NameandBranch` from `1employees` as e WHERE e.Resigned=1 order by `NameandBranch`;";
		$stmt = $link->query($sqlrevert);
	
		echo '<datalist id="employeeidrevert">';
			while($row = $stmt->fetch()) {
				echo "<option value='". $row['IDNo']. "'>" . $row['NameandBranch'] ."</option>";
			}
		echo '</datalist>';
		
        $formdesc='</i><br/><br/><h4>Initial resignation process</h4><i>Remove system access</i><br/><br/><div><div style="float:left"><form method=post action=resignemployee.php>'
            . 'IDNo <input type=text size=4 name=IDNo list=employeeid  autocomplete="off"  required=1 onclick="IsEmpty(IDNo);"> &nbsp;'
        . '<input type=hidden name=w value=SetInactive><input type=submit name=submit value="Set Inactive in System" OnClick="return confirm(\'Are You Sure?\');"></form></div><div style="margin-left:30%"><form method=post action=resignemployee.php>'
            . 'IDNo <input type=text size=4 name=IDNo list=employeeidresigned autocomplete="off"  required=1 onclick="IsEmpty(IDNo);"> &nbsp;'
        . '<input type=hidden name=w value=SetActive><input type=submit name=submit value="Set Active in System"></form></div></div>'
            . '<br/><br/><h4>Final resignation process</h4><i>Set as resigned in all lists, and delete future attendance.';
        
        
    $columnnames=array(
                       array('field'=>'IDNo','type'=>'text','size'=>4,'required'=>true,'list'=>'employeeid'),
                       array('field'=>'DateResigned', 'caption'=>'Date Resigned (Last day of employment)', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d'),'list'=>true),
                       array('field'=>'Resigned?', 'type'=>'text','size'=>2,'list'=>'yesno','required'=>true),
                       array('field'=>'Resigned', 'type'=>'hidden','size'=>2,'value'=>'1','list'=>null, 'required'=>false),
                       array('field'=>'ResignedWithClearance', 'type'=>'text','size'=>2,'list'=>'yesno','required'=>true),
                       array('field'=>'ResignReason', 'type'=>'text','size'=>100,'required'=>true,'list'=>null),
                       array('field'=>'w', 'type'=>'hidden','size'=>0,'value'=>'Resign','required'=>false)
                       );
    $method='POST';
    $action='resignemployee.php?w=Resign';
    $showbranches=false;
    $liststoshow=array('yesno','employeeid');
    if (isset($_GET['done'])){
        if ($_GET['done']==1) { echo '<font color="red">Employee has been tagged as resigned.  Future attendance has been deleted.</font>';}
        else if ($_GET['done']==3) { echo '<font color="green">Employee has been activated. Please go to reset password page and set default pass.</font>'; } else { echo '<font color="red">Employee can no longer enter our system.  Inform Admin to suspend email, if applicable.</font>';}
    }
    
    $fieldsinrow=5;
    $confmsg='Really resigned employee? This action cannot be undone.';
     include('../backendphp/layout/inputmainform.php');
     
     echo '<br><b>Revert Employee Record</b><div><form method=post action=resignemployee.php>'
            . 'IDNo <input type=text size=4 name=IDNo list=employeeidrevert autocomplete="off"  required=1 onclick="IsEmpty(IDNo);"> &nbsp;'
        . '<input type=hidden name=w value=RevertRecord><input type=submit name=submit value="Revert Record" OnClick="return confirm(\'Revert Employee Record?\');"></form></div>';
     
     echo '<br/><br/>' ;
     
     
}
 $link=null; $stmt=null;
    ?>
