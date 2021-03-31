<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
if (!allowedToOpen(670,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false; 
include_once('../switchboard/contents.php');


include_once "../generalinfo/lists.inc";

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_REQUEST['w'])?'List':($_REQUEST['w']);

$sqlbranch='SELECT Branch,BranchNo FROM 1branches WHERE Active<>0 AND BranchNo<>95 ORDER BY Branch;'; $stmtbranch=$link->query($sqlbranch);
// echo $sqlbranch; exit();
$branchlist='';
while ($rowbranch = $stmtbranch->fetch()){
	$branchlist.='<option value="'.$rowbranch['Branch'].'">'.$rowbranch['BranchNo'].'</option>';
}
echo '<datalist id="branchid">'.$branchlist.'</datalist>';

switch ($which){
case 'List':
$title='Current Positions';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3>';
	if (allowedToOpen(67011,'1rtc')) {
		
		$columnnames=array(
		array('field'=>'dateofchange','caption'=>'dateofchange','type'=>'date','size'=>'10','required'=>true),
		array('field'=>'IDNo','type'=>'text','list'=>'employeeid','size'=>'10','required'=>true),
		array('field'=>'newsupervisor','caption'=>'Supervisor','type'=>'text','list'=>'employeeid','size'=>'10','required'=>true),
		array('field'=>'remarks','type'=>'text','size'=>'20','required'=>false),
		);
		$title="";
		$action='currentpositions.php?superonly=1&w=Add'; 
		$modaltitle='Change Supervisor'; $buttonval='Change Supervisor Only'; 
		include('../backendphp/layout/inputmainformmodal.php');
		echo '<br><br>';
	
}
?>
Change of Position: &nbsp;
<?php $required='<font color="red">*</font>'; ?>
    <form action='currentpositions.php?w=Add' method='POST' enctype='multipart/form-data' style='display: inline'>
        Date of Change<input type='date' name='dateofchange' value=<?php echo date('Y-m-d',time()); ?>>
        ID No <?php echo $required;?><input type='text' name='IDNo' list='employeeid' autocomplete='off' required>
        New Position <?php echo $required;?><input type='text' name='newposition' list='positions' autocomplete='off' required><br>
        Branch <?php echo $required;?><input type='text' name='newbranch' list='branchid' autocomplete='off' required>
        Supervisor <?php echo $required;?><input type='text' name='newsupervisor' list='employeeid' autocomplete='off' required>
        Remarks<input type='text' name='remarks' autocomplete='off'>
        <input type='submit' name='submit' value='Submit'>
   </form><br>
<?php
$liststoshow=array('branches','employeeid','positions');
foreach ($liststoshow as $list){
renderlist($list);    
}
$title='';
$sortfield=(!isset($_POST['sortfield'])?'JLID ':$_POST['sortfield']);
$sql='SELECT `JobLevelNo`, `PositionID`,`Position`,`cp`.`IDNo`,`FullName`,`Branch`,`DateofChange`,CONCAT(id.Nickname," ",id.Surname) AS `Supervisor`, `Remarks` FROM attend_30currentpositions cp LEFT JOIN 1_gamit.0idinfo id ON cp.LatestSupervisorIDNo=id.IDNo ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC ');
$columnnames=array('JobLevelNo','PositionID','Position','IDNo','FullName','Branch','Supervisor','DateofChange', 'Remarks'); $columnsub=$columnnames;
$showbranches=false; 

     include('../backendphp/layout/displayastable.php');
	 
	 
	 $sqlencodetoday='SELECT cop.IDNo,CONCAT(Nickname," ",Surname) AS Name FROM attend_2changeofpositions cop JOIN 1_gamit.0idinfo id ON cop.IDNo=id.IDNo WHERE `Resigned?`<>1 AND (DateofChange>=CURDATE() - INTERVAL 7 DAY OR DATE(cop.TimeStamp)>= CURDATE() - INTERVAL 7 DAY);'; //7days na
	 $stmtencodetoday=$link->query($sqlencodetoday);
	$encodetoday='';
	while ($rowencodetoday = $stmtencodetoday->fetch()){
		$encodetoday.='<option value='.$rowencodetoday['IDNo'].'>'.$rowencodetoday['Name'].'</option>';
	}
	echo '<datalist id="encodeidtoday">'.$encodetoday.'</datalist>';

	 echo 'Delete IDNo (Current Date Encode Only)';
	 echo "<form action='currentpositions.php?w=Delete' method='POST' enctype='multipart/form-data' style='display: inline'>
        <input type='text' name='IDNo' list='encodeidtoday' autocomplete='off'>
        <input type='submit' name='btnDelete' value='Delete'>
   </form><br>";
     break;
     
	case 'Add':
    if (allowedToOpen(array(6701,67011),'1rtc')) { 

$datet=strtotime($_POST['dateofchange']);
$year=date("Y",$datet);
if($year<>date('Y')){
	echo '<br><br><b><font color="red">Date Error ['.$_POST['dateofchange'].']</font></b>! Current Year Only.'; exit();
}

if(isset($_GET['superonly']) AND $_GET['superonly']==1){
		$position=getValue($link,'attend_30currentpositions','IDNo',$_POST['IDNo'],'PositionID');
		$branch=getValue($link,'attend_30currentpositions','IDNo',$_POST['IDNo'],'BranchNo');
	} else {
		$position=getValue($link,'attend_0positions','Position',$_POST['newposition'],'PositionID');
        $branch=getValue($link,'1branches','Branch',$_POST['newbranch'],'BranchNo');
	}

	$sql='INSERT INTO `attend_2changeofpositions` (`IDNo`, `DateofChange`, `NewPositionID`, `AssignedBranchNo`,`SupervisorIDNo`,`Remarks`, `EncodedByNo`)
	VALUES ('.$_POST['IDNo'].', \''.$_POST['dateofchange'].'\', \''.$position.'\', \''.$branch.'\', \''.$_POST['newsupervisor'].'\', \''.$_POST['remarks'].'\', '.$_SESSION['(ak0)'].')';
	$stmt=$link->prepare($sql);
	$stmt->execute();
	

	if(!isset($_GET['superonly'])){
	 //check branch pseudocode
        $sqlcode = "SELECT Pseudobranch FROM 1branches WHERE BranchNo=".$branch."";
        $stmt = $link->query($sqlcode); $row = $stmt->fetch();

        if ($row['Pseudobranch']==0){ //cookie of branch
                $sqlcode = "SELECT ProgCookie FROM 1branches WHERE BranchNo=".$branch."";
        } else if ($row['Pseudobranch']==2){ //cookie of warehouses
                $sqlcode = "SELECT ProgCookie FROM 1_gamit.1rtcusers ru JOIN attend_30currentpositions cp ON ru.IDNo=cp.IDNo WHERE  
		cp.PositionID=50 AND cp.BranchNo=".$branch."";
				} else { //personal cookie
						// $sqlcode = "SELECT ProgCookie FROM 1_gamit.1rtcusers ru WHERE IDNo=".$_POST['IDNo'];
						$sqlcode = "SELECT ProgCookieOld AS ProgCookie FROM 1_gamit.1rtcusers ru WHERE IDNo=".$_POST['IDNo'];
				}
				$stmt = $link->query($sqlcode); $row = $stmt->fetch();

				$sql='UPDATE 1_gamit.1rtcusers SET `ProgCookieOld`=`ProgCookie`,`ProgCookie`="'.$row['ProgCookie'].'" WHERE `IDNo`='.$_POST['IDNo'];
				$stmt=$link->prepare($sql);
				$stmt->execute();
				//end check branch 
				
			$sql='Update `attend_2attendance` as a Set a.BranchNo='.$branch.' where DateToday>=CurDate() and `IDNo`='.$_POST['IDNo'];
			$stmt=$link->prepare($sql);
			$stmt->execute();
		}
    }
    header('Location:currentpositions.php');
    break;
	
	
	case 'Delete':
    if (allowedToOpen(array(6701,67011),'1rtc')) {
		$sql='DELETE FROM `attend_2changeofpositions` WHERE `IDNo`='.$_POST['IDNo'].' AND (DateofChange>=CURDATE() - INTERVAL 7 DAY OR DATE(`TimeStamp`)>= CURDATE() - INTERVAL 7 DAY);';
		$stmt=$link->prepare($sql);
		$stmt->execute();
	
		if(!isset($_GET['superonly'])){
			$sqlchecklastbranch = "SELECT COUNT(AssignedBranchNo) AS countno, AssignedBranchNo FROM attend_2changeofpositions WHERE IDNo=".$_POST['IDNo']." ORDER BY DateofChange DESC LIMIT 1";
			$stmtchecklastbranch = $link->query($sqlchecklastbranch); $rowchecklastbranch = $stmtchecklastbranch->fetch();
			
			if ($rowchecklastbranch['countno']>=1){
				goto proceed;
			} else {
				goto selfprogcookie;
			}
			proceed:
			$branch=$rowchecklastbranch['AssignedBranchNo'];
			
			$sqlcode = "SELECT Pseudobranch FROM 1branches WHERE BranchNo=".$branch."";
			$stmt = $link->query($sqlcode); $row = $stmt->fetch();

			if ($row['Pseudobranch']==0){ //cookie of branch
					$sqlcode = "SELECT ProgCookie FROM 1branches WHERE BranchNo=".$branch."";
			} else if ($row['Pseudobranch']==2){ //cookie of warehouses
					$sqlcode = "SELECT ProgCookie FROM 1_gamit.1rtcusers ru JOIN attend_30currentpositions cp ON ru.IDNo=cp.IDNo WHERE  
			cp.PositionID=50 AND cp.BranchNo=".$branch."";
					} else { //personal cookie
						goto selfprogcookie;
					}
					$stmt = $link->query($sqlcode); $row = $stmt->fetch();
					goto pass;
					
					selfprogcookie:
					$sqlcode1 = "SELECT ProgCookieOld FROM 1_gamit.1rtcusers ru WHERE IDNo=".$_POST['IDNo'];
					$stmt1 = $link->query($sqlcode1); $row1 = $stmt1->fetch();
					$row['ProgCookie']=$row1['ProgCookieOld'];
					
					pass:
					$sql='UPDATE 1_gamit.1rtcusers SET `ProgCookie`="'.$row['ProgCookie'].'" WHERE `IDNo`='.$_POST['IDNo'];
					$stmt=$link->prepare($sql);
					$stmt->execute();
					//end check branch
					
					
			if ($rowchecklastbranch['countno']>=1){		
				$sql='Update `attend_2attendance` as a Set a.BranchNo='.$rowchecklastbranch['AssignedBranchNo'].' where DateToday>=CurDate() and `IDNo`='.$_POST['IDNo'];
				$stmt=$link->prepare($sql);
				$stmt->execute();
			}
		}
		
    }
    header('Location:currentpositions.php');
    break;
}
  $link=null;  $stmt=null;
?>