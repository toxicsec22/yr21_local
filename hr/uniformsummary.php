<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(4100,'1rtc')) { echo 'No permission'; exit; }

$showbranches=false;
include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'UniformPerBranchPerArea':$_GET['w']);

include_once('../backendphp/layout/linkstyle.php');


if (in_array($which,array('UniformPerBranchPerArea','ShirtSizePerBranchPerArea'))){
	
	$condi='';
	if(isset($_POST['btnFilter'])){
		if($_POST['AreaNo']=='All'){
			$areacondi=' 1=1 ';
		} else {
			$areacondi = ' b.AreaNo = '.$_POST['AreaNo'];
		}
		if($_POST['BranchNo']=='All'){
			$branchcondi=' AND 1=1 ';
		} else {
			if($_POST['BranchNo']>=800){
				$branchcondi=' AND cp.deptid = '.ltrim(ltrim($_POST['BranchNo'], '8'),'0');
			} else {
				$branchcondi=' AND b.BranchNo = '.$_POST['BranchNo'];
			}
		}
		$condi=' WHERE '.$areacondi.$branchcondi;
	}

	$areas='';
	$stmtareas=$link->query('SELECT AreaNo,Area FROM 0area ORDER BY Area'); $resultareas=$stmtareas->fetchAll();
	foreach($resultareas as $resultarea){
		$areas.='<option value="'.$resultarea['AreaNo'].'" '.((isset($_POST['AreaNo']) AND $_POST['AreaNo']==$resultarea['AreaNo'] AND $_POST['AreaNo']<>'All')?'selected':'').'>'.$resultarea['Area'].'</option>';
	}
	$branches='';

	$stmtbranches=$link->query('select EntityID AS BranchNo,Entity AS Branch from acctg_1budgetentities WHERE (EntityID BETWEEN 1 AND 94) OR EntityID>=800 ORDER BY Branch'); $resultbranches=$stmtbranches->fetchAll();
	foreach($resultbranches as $resultbranch){
		$branches.='<option value="'.$resultbranch['BranchNo'].'" '.((isset($_POST['BranchNo']) AND $_POST['BranchNo']==$resultbranch['BranchNo'] AND $_POST['BranchNo']<>'All')?'selected':'').'>'.$resultbranch['Branch'].'</option>';
	}
}
if (in_array($which,array('UniformPerBranchPerArea','UniformPerQuantityPerType'))){
	/* $mbpwc 		= 1. Men's Blue Polo with collar 					UID = 5
	$wbpwc 		= 2. Women's Blue Polo with collar						UID = 6
	$mbpwwt 	= 3. Men's 3/4 Blue Polo with white tie					UID = 7
	$wbpwwt 	= 4. Women's 3/4 Blue Polo with white tie				UID = 8
	$mbpwc34 	= 5. Men's 3/4 Blue Polo with collar /					UID = 9
	$wbpwc34 	= 6. Women's 3/4 Blue Polo with collar /				UID = 10
	$mbpswws 	= 7. Men's Blue Polo Shirt with white stripe			UID = 11
	$mbps 		= 8. Men's Blue Polo Shirt								UID = 12
	$wbps 		= 9. Women's Blue Polo Shirt							UID = 13
	else {
		wala pa
	} */
/* 
	$mbpwc = '(deptid IN (30,15,50,20,60,40,55,1,2,3,4,70) OR PositionID IN (50,53,30,35)) AND Gender = 1';
	$wbpwc = '(deptid IN (30,15,50,20,60,40,55,1,2,3,4,70) OR PositionID IN (50,53,30,35)) AND Gender = 0';
	$mbpwwt = 'PositionID IN (SELECT PositionID FROM attend_1positions WHERE Position LIKE "%Manager%") AND Gender = 1';
	$wbpwwt = 'PositionID IN (SELECT PositionID FROM attend_1positions WHERE Position LIKE "%Manager%") AND Gender = 0';
	$mbpwc34 = 'PositionID IN (36) AND Gender = 1';
	$wbpwc34 = 'PositionID IN (36) AND Gender = 0';
	$mbpswws = 'PositionID IN (32,81)';
	$mbps = 'PositionID IN (37,33,38,51,52,55,2,63,68,4,3,141,0,7,8,1,133) AND Gender=1';
	$wbps = 'PositionID IN (37,33,38,51,52,55,2,63,68,4,3,141,0,7,8,1,133) AND Gender=0';
 */
	$sqlmain='SELECT Positions FROM hr_61uniforminfo';
	
	
	$sql=$sqlmain.' WHERE UID=5';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$mbpwc = '(PositionID IN ('.$result['Positions'].')) AND Gender = 1';
	
	$sql=$sqlmain.' WHERE UID=6';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$wbpwc = '(PositionID IN ('.$result['Positions'].')) AND Gender = 0';
	
	$sql=$sqlmain.' WHERE UID=7';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$mbpwwt = '(PositionID IN ('.$result['Positions'].')) AND Gender = 1';
	
	$sql=$sqlmain.' WHERE UID=8';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$wbpwwt = '(PositionID IN ('.$result['Positions'].')) AND Gender = 0';
	
	$sql=$sqlmain.' WHERE UID=9';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$mbpwc34 = '(PositionID IN ('.$result['Positions'].')) AND Gender = 1';
	
	$sql=$sqlmain.' WHERE UID=10';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$wbpwc34 = '(PositionID IN ('.$result['Positions'].')) AND Gender = 0';
	
	$sql=$sqlmain.' WHERE UID=11';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$mbpswws = '(PositionID IN ('.$result['Positions'].'))';
	
	$sql=$sqlmain.' WHERE UID=12';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$mbps = '(PositionID IN ('.$result['Positions'].')) AND Gender=1';
	
	$sql=$sqlmain.' WHERE UID=13';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	$wbps = '(PositionID IN ('.$result['Positions'].')) AND Gender=0';



	$sql0='CREATE TEMPORARY TABLE UniformStyle AS SELECT e.IDNo,UniformSize,
	(CASE 
		WHEN '.$mbpwc34.' THEN "9"
		WHEN '.$wbpwc34.' THEN "10"
		WHEN '.$mbpwwt.' THEN "7"
		WHEN '.$wbpwwt.' THEN "8"
		WHEN '.$mbpswws.' THEN "11"
		WHEN '.$mbps.' THEN "12"
		WHEN '.$wbps.' THEN "13"
		WHEN '.$mbpwc.' THEN "5"
		WHEN '.$wbpwc.' THEN "6"
		ELSE "0"
	END) AS `UniformType` FROM 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo AND PositionID NOT IN (SELECT DISTINCT(deptheadpositionid) FROM 1departments);';
	$stmt0=$link->prepare($sql0); $stmt0->execute();

}

echo '<br><a id=\'link\' href="uniformsummary.php?w=UniformPerBranchPerArea">Uniform Size (Per Branch Per Area)</a> ';
echo '<a id=\'link\' href="uniformsummary.php?w=UniformPerQuantityPerType">Uniform Size (Per Quantity Per Type)</a> ';
echo '<a id=\'link\' href="uniformsummary.php?w=ShirtSizePerBranchPerArea">Giveaway Shirt Size (Per Branch Per Area)</a> ';
echo '<a id=\'link\' href="uniformsummary.php?w=ShirtSize">Giveaway Shirt Size (Per Quantity)</a><br><br>';



$empstatsql='(CASE
WHEN EmpStatus=0 THEN "Probationary"
WHEN EmpStatus=1 THEN "Regular"
WHEN EmpStatus=2 THEN "Resigned With Clearance"
WHEN EmpStatus=3 THEN "Resigned No Clearance"
ELSE ""
END) AS EmploymentStatus,';

switch ($which)
{

case 'UniformPerBranchPerArea':



/* 
$sql='SELECT (CASE 
WHEN UniformType=5 THEN "Men\'s Blue Polo with collar"
WHEN UniformType=6 THEN "Women\'s Blue Polo with collar"
WHEN UniformType=7 THEN "Men\'s 3/4 Blue Polo with white tie"
WHEN UniformType=8 THEN "Women\'s 3/4 Blue Polo with white tie"
WHEN UniformType=9 THEN "Men\'s 3/4 Blue Polo with collar"
WHEN UniformType=10 THEN "Women\'s 3/4 Blue Polo with collar"
WHEN UniformType=11 THEN "Men\'s Blue Polo Shirt with white stripe"
WHEN UniformType=12 THEN "Men\'s Blue Polo Shirt"
WHEN UniformType=13 THEN "Women\'s Blue Polo Shirt"

ELSE "Not in Categories"
END) AS UniformCat,FullName,Position,IF(deptid IN (2,3,4,10),b.Branch,dept) AS `Branch/Dept`,UniformType,UniformSize FROM UniformStyle us JOIN attend_30currentpositions cp ON us.IDNo=cp.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo JOIN 0area a ON b.AreaNo=a.AreaNo '.$condi.' ORDER BY `Branch/Dept`'; */
$sql='SELECT IFNULL(ui.UniformType,"Not in categories") AS UniformCat,FullName,Position,IF(deptid IN (2,3,4,10),b.Branch,dept) AS `Branch/Dept`,us.UniformType,'.$empstatsql.'UniformSize FROM UniformStyle us JOIN attend_30currentpositions cp ON us.IDNo=cp.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo JOIN 0area a ON b.AreaNo=a.AreaNo LEFT JOIN hr_61uniforminfo ui ON us.UniformType=ui.UID JOIN 1_gamit.0idinfo id ON cp.IDNo=id.IDNo '.$condi.' ORDER BY `Branch/Dept`';
$stmt=$link->query($sql); $result=$stmt->fetchAll();

$title='Uniform Size (Quantity Per Branch Per Area)';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3>';
echo '<br><i>* Qty is 4 per person</i><br><br>';
echo '<form action="#" method="POST">Area: <select name="AreaNo"><option value="All">All</option>'.$areas.'</select> Branch: <select name="BranchNo"><option value="All">All</option>'.$branches.'</select> <input type="submit" value="Filter" name="btnFilter"></form>';
echo '<table border="1px solid black;" style="font-size:9pt;background-color:white;border-collapse:collapse;">';
echo '<tr><th style="padding:3px;">Branch</th><th style="padding:3px;">Name</th><th>Employment Status</th><th style="padding:3px;">Position</th><th style="padding:3px;">UniformType</th><th style="padding:3px;">UniformSize</th><th>Qty</th></tr>';
$total=0; $totqty=0;
foreach($result AS $res){
	echo '<tr><td style="padding:3px;">'.$res['Branch/Dept'].'</td><td style="padding:3px;">'.$res['FullName'].'</td><td style="padding:3px;">'.$res['EmploymentStatus'].'</td><td style="padding:3px;">'.$res['Position'].'</td><td style="padding:3px;">'.$res['UniformCat'].'</td><td style="padding:3px;">'.$res['UniformSize'].'</td><td style="padding:3px;text-align:right;">4</td></tr>';
	
}
echo '</table><br>';



break;


case 'UniformPerQuantityPerType':


$sql='SELECT IFNULL(ui.UniformType,"Not in Uniform Type") AS UniformCat,us.UniformType,UniformSize,COUNT(IDNo) AS Total,GROUP_CONCAT((SELECT CONCAT(Nickname," ",SurName) FROM 1employees WHERE IDNo=us.IDNo)) AS IDNos FROM UniformStyle us LEFT JOIN hr_61uniforminfo ui ON us.UniformType=ui.UID GROUP BY UniformType,UniformSize';
$stmt=$link->query($sql); $result=$stmt->fetchAll();
$title='Uniform Size';
echo '<title>'.$title.'</title>';

$title='Uniform Size (Quantity Per Size Per Type)';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3>';
echo '<table border="1px solid black;" style="font-size:9pt;background-color:white;border-collapse:collapse;">';
echo '<tr><th style="padding:3px;">UniformType</th><th style="padding:3px;">UniformSize</th><th style="padding:3px;">TotalPerson</th><th>TotalQty</th><th>IDNos with no Uniform Size</th></tr>';
$uniformcatno=''; $total=0; $totqty=0; $totpertr=0;
foreach($result AS $res){
	echo ''.(($total<>0 AND $uniformcatno<>$res['UniformType'])?'<tr><td colspan="4" align="right" style="background-color:green;color:white;padding:3px;"><b>Total : '.($totpertr).'</b></td><td></td></tr>':'').'';
	
	$qty=($res['Total']*4);
	
	
	echo '<tr><td style="padding:3px;"><b>'.($uniformcatno<>$res['UniformType']?$res['UniformCat']:'').'</b></td><td style="padding:3px;">'.$res['UniformSize'].'</td><td style="padding:3px;text-align:right;">'.$res['Total'].'</td><td style="padding:3px;text-align:right;">'.$qty.'</td><td '.($res['UniformSize']==''?'style="color:white;background-color:red;padding:3px;"':'').'>'.($res['UniformSize']==''?$res['IDNos']:'').'</td></tr>';
	
	
	
	if($uniformcatno<>$res['UniformType']){
		$totpertr=0;
	}
	$uniformcatno=$res['UniformType'];
	$total=$total+$res['Total'];
	$totqty=$totqty+$qty;
	$totpertr=$totpertr+$qty;
}
echo '<tr><td colspan="4" align="right" style="background-color:green;color:white;padding:3px;"><b>Total : '.($totpertr).'</b></td><td></td></tr>';
echo '<tr><td colspan=3 align="right" style="padding:3px;">'.$total.'</td><td align="right" style="padding:3px;">'.$totqty.'</td><td></td></tr>';
echo '</table>';
break;


case 'ShirtSizePerBranchPerArea':

$sql='SELECT FullName,Position,'.$empstatsql.'IF(deptid IN (2,3,4,10),b.Branch,dept) AS `Branch/Dept`,ShirtSize FROM 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo JOIN 0area a ON b.AreaNo=a.AreaNo JOIN 1_gamit.0idinfo id ON cp.IDNo=id.IDNo '.$condi.' ORDER BY `Branch/Dept`';
$stmt=$link->query($sql); $result=$stmt->fetchAll();

$title='Giveaway Shirt Size (Quantity Per Branch Per Area)';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3>';
echo '<br><i>* Qty is 1 per person</i><br><br>';
echo '<form action="#" method="POST">Area: <select name="AreaNo"><option value="All">All</option>'.$areas.'</select> Branch: <select name="BranchNo"><option value="All">All</option>'.$branches.'</select> <input type="submit" value="Filter" name="btnFilter"></form>';
echo '<table border="1px solid black;" style="font-size:9pt;background-color:white;border-collapse:collapse;">';
echo '<tr><th style="padding:3px;">Branch</th><th style="padding:3px;">Name</th><th style="padding:3px;">Employment Status</th><th style="padding:3px;">Position</th><th style="padding:3px;">ShirtSize</th><th>Qty</th></tr>';
$total=0; $totqty=0;
foreach($result AS $res){
	echo '<tr><td style="padding:3px;">'.$res['Branch/Dept'].'<td style="padding:3px;">'.$res['FullName'].'</td><td style="padding:3px;">'.$res['EmploymentStatus'].'</td><td style="padding:3px;">'.$res['Position'].'<td style="padding:3px;">'.$res['ShirtSize'].'</td><td style="padding:3px;text-align:right;">1</td></tr>';
}
echo '</table><br>';




break;


case 'ShirtSize':

$sql='SELECT ShirtSize,COUNT(e.IDNo) AS Total,'.$empstatsql.'GROUP_CONCAT(e.IDNo) AS IDNos FROM 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN 1_gamit.0idinfo id ON cp.IDNo=id.IDNo GROUP BY ShirtSize;';
$stmt=$link->query($sql); $result=$stmt->fetchAll();

$title='Giveaway Shirt Size (Per Quantity)';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3>';
echo '<table border="1px solid black;" style="font-size:9pt;background-color:white;border-collapse:collapse;">';
echo '<tr><th style="padding:3px;">Size</th><th style="padding:3px;">Total</th><th>IDNos with No Shirt Size</th></tr>';

$total=0;
foreach($result AS $res){
	echo '<tr><td style="padding:3px;">'.$res['ShirtSize'].'</td><td style="padding:3px;text-align:right;">'.$res['Total'].'</td><td '.($res['ShirtSize']==''?'style="background-color:red;padding:3px;"':'').'>'.($res['ShirtSize']==''?$res['IDNos']:'').'</td></tr>';
	
	$total=$total+$res['Total'];
}
echo '<tr><td colspan=2 align="right" style="padding:3px;background-color:green;color:white;"><b>Total : '.$total.'</b></td><td></td></tr>';
echo '</table>';

break;
}
?>