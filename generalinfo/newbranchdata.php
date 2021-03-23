<html>
<head>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6063,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');
 

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$which=!isset($_POST['w'])?'Choose':$_POST['w'];
$branchno=!isset($_POST['BranchNo'])?'':$_POST['BranchNo'];
$branch= comboBoxValue($link, '1branches', 'BranchNo', $branchno, 'Branch');
$title='Add Required Data for New Branch'; 


echo comboBox($link,'SELECT BranchNo, Branch FROM `1branches`','Branch','BranchNo','branches');
echo comboBox($link,'SELECT FullName, IDNo FROM `attend_30currentpositions` WHERE PositionID IN (31,35,36) ORDER BY FullName;','FullName','IDNo','stl');
echo comboBox($link,'SELECT FullName, IDNo FROM `attend_30currentpositions` WHERE PositionID IN (31,61) ORDER BY FullName;','FullName','IDNo','sam');
echo comboBox($link,'SELECT FullName, IDNo FROM `attend_30currentpositions` WHERE PositionID IN (153,154) ORDER BY FullName;','FullName','IDNo','cso');
echo comboBox($link,'SELECT FullName, IDNo FROM `attend_30currentpositions` WHERE PositionID IN (71) ORDER BY FullName;','FullName','IDNo','ops');
echo comboBox($link,'SELECT Branch, BranchNo FROM `1branches` WHERE Active=1;','BranchNo','Branch','branchlist');

?>

<title><?php echo $title; ?></title>
</head>
<body>
    <div style='margin-left: 30%;'>
    <br/><br/><h4><?php echo $title; ?></h4><br/><br/>
    <form method='post' action='newbranchdata.php'>
        <input type='text' name='BranchNo' list='branches' size=5 value='<?php echo $branchno;?>'> &nbsp; &nbsp; <?php echo strtoupper($branch); ?><br/><br/>
        <input type='submit' value='Add ACCOUNTING Data' name='w'><br/><br/>
        <input type='submit' value='Add INVENTORY Data' name='w'><br/><br/>
        </form><br/><br/>
    <form method='post' action='newbranchdata.php'>
        For Branch <input type='text' name='BranchNo' list='branches' size=5 value='<?php echo $branchno;?>'> &nbsp; &nbsp; <?php echo strtoupper($branch); ?>
        Monthly Rent: <input type='text' name='Rent' size=5 value='0'> &nbsp; &nbsp; 
        Start on Month (1 - 12): <input type='text' name='StartMonth' size=5 value='<?php echo date('m');?>'> &nbsp; &nbsp; 
        <input type='submit' value='Add TARGETS' name='w'><br/><br/>
        </form><br/><br/>
    <form method='post' action='newbranchdata.php'>
        For Branch <input type='text' name='BranchNo' list='branches' size=5 value='<?php echo $branchno;?>'> &nbsp; &nbsp; <?php echo strtoupper($branch); ?>
        Credit Analyst: <input type='text' name='CSO' size=5 list='cso'> &nbsp; &nbsp; 
<!--        Acctg: <input type='text' name='Acctg' size=5 > &nbsp; &nbsp;-->
        STL: <input type='text' name='STL' size=5 list='stl'> &nbsp; &nbsp;
        SAM: <input type='text' name='SAM' size=5 list='sam'> &nbsp; &nbsp;
        Ops Specialist: <input type='text' name='Ops' size=5 list='ops'> &nbsp; &nbsp;
        Remarks: <input type='text' name='Remarks' size=10 > &nbsp; &nbsp; 
        <input type='submit' value='Add Default Assignments' name='w'><br/><br/>
        </form>
    <br/><br/>
    <form action="../admin/permits.php?w=ManualEncode" method="POST">
        For Branch (NOT for pseudo) <input type="text" name="Branch" list="branchlist" value='<?php echo $branch;?>'> &nbsp; &nbsp; <?php echo strtoupper($branch); ?>
        <input type="submit" name="btnAddManual" value="Encode permits for monitoring">
    </form><br/><br/>
    <form method='post' action='newbranchdata.php'>
        For Branch <input type='text' name='BranchNo' list='branches' size=5 value='<?php echo $branchno;?>'> &nbsp; &nbsp; <?php echo strtoupper($branch); ?>
        <input type='submit' value='Update _comkey - PER BRANCH' name='w'> &nbsp; &nbsp;
        <input type='submit' value='Update _comkey - ALL Branches' name='w'> &nbsp; &nbsp;
        <input type='submit' value='Update _comkey - Mobile and Warehouse Attendance' name='w'><br/><br/>
        </form>
    </div>
<?php
//echo $which;
switch ($which){
    case 'Add ACCOUNTING Data':
        $sqlinsert='INSERT INTO `acctg_1begbal` (`AccountID`,`BegBalance`,`BranchNo`,`EncodedByNo`,`TimeStamp`)
            SELECT `AccountID`,0,'.$branchno.','.$_SESSION['(ak0)'].', Now() FROM `acctg_1chartofaccounts` WHERE ISNULL(OwnedByCoNo) OR FIND_IN_SET((SELECT CompanyNo FROM 1branches WHERE BranchNo='.$branchno.'),OwnedByCoNo);';
        $link->query($sqlinsert); if($_SESSION['(ak0)']==1002) { echo $sqlinsert;} 
        $sql='SELECT bb.BranchNo, bb.AccountID, ca.ShortAcctID, bb.BegBalance FROM `acctg_1begbal` bb JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=bb.AccountID WHERE BranchNo='.$branchno;
        $columnnames=array('BranchNo','AccountID','ShortAcctID','BegBalance');
        include('../backendphp/layout/displayastablenosort.php');
        break;
    case 'Add INVENTORY Data':
        // $sqlinsert='INSERT INTO `invty_1beginv` (`ItemCode`,`BranchNo`,`BegInv`,`BegCost`,`BegMinPrice`)
        $sqlinsert='INSERT INTO `invty_1beginv` (`ItemCode`,`BranchNo`,`BegInv`,`BegCost`,`BegPriceLevel1`,`BegPriceLevel2`,`BegPriceLevel3`,`BegPriceLevel4`,`BegPriceLevel5`)
            SELECT `ItemCode`,'.$branchno.',0,0,0,0,0,0,0 FROM `invty_1items` WHERE MoveType<>5;';
        $link->query($sqlinsert); if($_SESSION['(ak0)']==1002) { echo $sqlinsert;} 
        $sql='SELECT * FROM `invty_1beginv` WHERE BranchNo='.$branchno;
        $columnnames=array('ItemCode', 'BranchNo', 'BegInv', 'BegCost','BegPriceLevel1','BegPriceLevel2','BegPriceLevel3','BegPriceLevel4','BegPriceLevel5');
        include('../backendphp/layout/displayastable.php');
        break;
    case 'Add TARGETS':
        $begmonth=$_POST['StartMonth']; $columnnames=array(); $target=$_POST['Rent']*30;
        for ($i = $begmonth; $i <= 12; ++$i) { $columnnames[]=str_pad($i,2,'0',STR_PAD_LEFT); }
        $sqlinsert='';
        foreach ($columnnames as $col) { $sqlinsert.=',`'.$col.'`=\''.$target.'\'';}
        $sqlinsert='INSERT INTO `acctg_1yearsalestargets` SET `branchno`='.$branchno.$sqlinsert;
        $link->query($sqlinsert); if($_SESSION['(ak0)']==1002) { echo $sqlinsert;}
        $sql='SELECT * FROM `acctg_1yearsalestargets` WHERE BranchNo='.$branchno;
        include('../backendphp/layout/displayastablenosort.php');
        break;
    case 'Add Default Assignments':
		$insertarray=array('CSO','STL','SAM','Ops');
		
		
		foreach($insertarray AS $insert){
			$sqlinsert='INSERT INTO `attend_2changebranchgroup` SET DateofChange=CURDATE(),BranchNo='.$branchno.',EncodedByNo='.$_SESSION['(ak0)'].',`TimeStamp`=Now(),PositionID=(SELECT PositionID FROM attend_30currentpositions WHERE IDNo='.$_POST[$insert].'),'
            . '`IDNo`='.$_POST[$insert].', `Remarks`=\''.$_POST['Remarks'].'\'';
			$link->query($sqlinsert); if($_SESSION['(ak0)']==1002) { echo $sqlinsert;}
		}
		
        $sql='SELECT bg.*,CONCAT(e.Nickname," ",e.SurName) AS CNC,CONCAT(e2.Nickname," ",e2.SurName) AS TeamLeader,CONCAT(e3.Nickname," ",e3.SurName) AS SAM,CONCAT(e4.Nickname," ",e4.SurName) AS OpsSpecialist FROM `attend_1branchgroups` bg LEFT JOIN 1employees e ON bg.CNC=e.IDNo LEFT JOIN 1employees e2 ON bg.TeamLeader=e2.IDNo LEFT JOIN 1employees e3 ON bg.SAM=e3.IDNo LEFT JOIN 1employees e4 ON bg.OpsSpecialist=e4.IDNo WHERE BranchNo='.$branchno;
        $columnnames=array('DateofChange', 'BranchNo', 'CNC', 'TeamLeader', 'SAM', 'OpsSpecialist');
        include('../backendphp/layout/displayastablenosort.php');
        break;
    case 'Update _comkey - ALL Branches':
        $path=$_SERVER['DOCUMENT_ROOT'];
        include_once ($path.'/acrossyrs/commonfunctions/fxngenrandpass.php');
        // first edit offices/pseudo
        $newkey=generatePassword(45);
        $sqlupdate='UPDATE `1branches` SET ProgCookie=\''.$newkey.'\' WHERE PseudoBranch=1 AND BranchNo<>95';
        $link->query($sqlupdate); if($_SESSION['(ak0)']==1002) { echo $sqlupdate;}
        // end of offices/pseudo
        $sql='SELECT BranchNo, Branch, ProgCookie,PseudoBranch, Active FROM `1branches` WHERE PseudoBranch<>1 AND Active<>0; ';
        $stmt=$link->query($sql); $res=$stmt->fetchAll();
        foreach($res as $branch){
        $newkey=generatePassword(45);
        $sqlupdate='UPDATE `1branches` SET ProgCookie=\''.$newkey.'\' WHERE BranchNo='.$branch['BranchNo'];
        $link->query($sqlupdate); if($_SESSION['(ak0)']==1002) { echo $sqlupdate;}
        }
        $sql='SELECT BranchNo, Branch, ProgCookie,PseudoBranch, Active FROM `1branches` WHERE PseudoBranch<>1 AND Active<>0;';
        $columnnames=array('BranchNo', 'Branch', 'ProgCookie');
        include('../backendphp/layout/displayastablenosort.php');
        break;
    case 'Update _comkey - PER BRANCH':
        $path=$_SERVER['DOCUMENT_ROOT'];
        include_once ($path.'/acrossyrs/commonfunctions/fxngenrandpass.php');
        $newkey=generatePassword(45);
        $sqlupdate='UPDATE `1branches` SET ProgCookie=\''.$newkey.'\' WHERE BranchNo='.$_POST['BranchNo'];
        $link->query($sqlupdate); if($_SESSION['(ak0)']==1002) { echo $sqlupdate;}
        $sql='SELECT * FROM `1branches` WHERE BranchNo='.$branchno;
        $columnnames=array('BranchNo', 'Branch', 'ProgCookie');
        include('../backendphp/layout/displayastablenosort.php');
        break;
    case 'Update _comkey - Mobile and Warehouse Attendance': 
        $sql='SELECT * FROM `1specindex`;';
        $stmt=$link->query($sql); $res=$stmt->fetchAll();
        foreach($res as $branch){
        $newkey=generatePassword(45);
        $sqlupdate='UPDATE `1specindex` SET ProgCookie=\''.$newkey.'\' WHERE specid='.$branch['specid'];
        $link->query($sqlupdate); if($_SESSION['(ak0)']==1002) { echo $sqlupdate;}
        }
        $columnnames=array('specid', 'brand', 'ProgCookie');
        include('../backendphp/layout/displayastablenosort.php');
        break;
}


  $link=null; $stmt=null;

?>
</body>