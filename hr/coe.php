<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(array(59022,59030),'1rtc')) { echo 'No permission'; exit; }

$which=(!isset($_GET['w'])?'NewCOE':$_GET['w']);

$showbranches=false;
if($which<>'PrintPreview'){
	include_once('../switchboard/contents.php');
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
} else {
	include_once($path.'/acrossyrs/dbinit/userinit.php');
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
}
?>

<br><div id="section" style="display: block;">

<?php


// $note='<i>Note: Upon approval of your request, you will receive a confirmation call from our department representative.</i><br><br>';

switch ($which)
{
	
	case 'NewCOE':
      // if (!allowedToOpen(5357,'1rtc')) { echo 'No permission'; exit; }
	  include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
       echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions WHERE IDNo>1002','FullName','IDNo','emploans');
       echo comboBox($link,'SELECT id.IDNo, CONCAT(id.NickName," ",id.SurName) AS FullName FROM 1_gamit.0idinfo id JOIN 1employees e ON id.IDNo=e.IDNo WHERE e.Resigned=1 AND EmpStatus=2','FullName','IDNo','empclearance');
	   
	   
	   $sql='SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID=59027';
	$stmt=$link->query($sql); $row=$stmt->fetch();
	
      echo comboBox($link,'SELECT IDNo, FullName FROM attend_30currentpositions WHERE PositionID IN ('.$row['AllowedPos'].')','FullName','IDNo','approver');
	  echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches WHERE Active<>0 AND Pseudobranch IN (0,2)','Branch','BranchNo','branches');
	   
	   
	   
	   
$title='Create New Certificate of Employment'; 

echo '<title>'.$title.'</title>';
echo '<br><br>';


	 	$radionamefield='Radio'; 
		echo '<h3 style="margin-left:33%;">'.$title.'</h3><br>';
	 	echo'<div style="border:1px solid black; padding:10px; width:450px;margin-left:33%;"><form id="form-id">
		
			<h4>PURPOSE:</h4><br>'.str_repeat('&nbsp ',3);
			if (allowedToOpen(array(59022),'1rtc')) { echo '
			<b>Final clearance </b> <input type="radio" id="watch-me1" name="'.$radionamefield.'" value=1>'.str_repeat('&nbsp ',3).'
			<b>Loan application </b> <input type="radio" id="watch-me2" name="'.$radionamefield.'" value=2>'.str_repeat('&nbsp ',3);
			}
			echo '
			<b>Travel pass </b> <input type="radio" id="watch-me3" name="'.$radionamefield.'" value=3>'.str_repeat('&nbsp ',3).'<br>
		  </form></div></br>';
	include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';
	
	$divandformfirst='<form method="post" action="coe.php?w=PrintPreview&coetype='; 
	$divandformsecond='" autocomplete="off">
	<div style="background-color:white;border:1px solid black;padding:6px;width:450px;">'.str_repeat('&nbsp ',3).'
	IDNo: <input type="text" name="IDNo" list="';
	$divandformthird='" size="10">'.str_repeat('&nbsp ',3);
	$approver='Approver: <input type="text" name="ApproveByNo" list="approver" size="10">';
	$submit='<input type="submit" name="submit" value="Print Preview">';	

	// clearance

		echo '<div  style="margin-left:33%;"><div style="display:none" id="show-me1"> '.$divandformfirst.'1'.$divandformsecond.'empclearance'.$divandformthird.$approver.str_repeat('&nbsp ',3).$submit.'
		</div></form></div></div>';

	// loans

	echo '<div  style="margin-left:33%;"><div style="display:none" id="show-me2"> '.$divandformfirst.'2'.$divandformsecond.'emploans'.$divandformthird.$approver.str_repeat('&nbsp ',3).$submit.'
	</div></form></div></div>';

	// travel pass

	echo '<div  style="margin-left:33%;"><div style="display:none" id="show-me3"> '.$divandformfirst.'3'.$divandformsecond.'emploans'.$divandformthird.
	'Branch: <input type="text" name="BranchNo" list="branches" size="10"><br><br>'.str_repeat('&nbsp ',3).$approver.str_repeat('&nbsp ',3).$submit.'
	</div></form></div></div>';

		  
break;

case 'PrintPreview':
echo '<title>Print COE</title>';
echo '<style>@media screen {
  div.divFooter {
    display: none;
  }
}
@media print {
  div.divFooter {
    position: fixed;
    bottom: 0;
	width: 100%;
  }
}</style>';
$cert='<center><font style="font-size:25pt;letter-spacing: 5px;font-weight:bold;">CERTIFICATION</font></center><br><br><br><br>To whom it may concern:<br><br>';

switch($_GET['coetype']){
	case 1: // final clearance
		if (!allowedToOpen(array(59022),'1rtc')) { echo 'No permission'; exit; }
		$sql='SELECT Gender,id.IDNo,DateResigned,Company,CompanyName,`Position`,CONCAT(id.FirstName," ",LEFT(id.MiddleName,1),". ",id.SurName) AS Name,if(p.deptid IN (1,2,3,4),"Supply Chain",if(p.deptid=10,"Operations",department)) AS department,id.DateHired FROM attend_30latestpositionsinclresigned cp JOIN 1_gamit.0idinfo id ON cp.IDNo=id.IDNo JOIN 1employees e ON e.IDNo=cp.IDNo JOIN 1companies c ON e.RCompanyNo=c.CompanyNo JOIN attend_0positions p ON cp.PositionID=p.PositionID JOIN 1departments d ON p.deptid=d.deptid WHERE e.Resigned=1 AND cp.IDNo='.$_POST['IDNo'];

		$stmt=$link->query($sql); $row=$stmt->fetch();

		echo '<center><img src="../generalinfo/logo/'.$row['Company'].'.png"></center><br><br><br><br>'.$cert.'';

	echo 'This is to certify that <b>'.($row['Gender']==1?'MR.':'MS.').' '.strtoupper($row['Name']).'</b> was employed by '.$row['CompanyName'].' from '.date('F d, Y', strtotime($row['DateHired'])).'
to '.date('F d, Y', strtotime($row['DateResigned'])).', with the last position as '.$row['Position'].' under the '.$row['department'].'
Department.
<br><br>
The aforementioned has no standing obligations or accountability to settle
with the company, and is cleared of all accountabilities from the company.
<br><br>
This Certificate of Clearance is issued for whatever legal purpose it may serve
best.';
break;

case 2: // loans
	if (!allowedToOpen(array(59022),'1rtc')) { echo 'No permission'; exit; }
	$sql='SELECT Gender,Position,Company,if(cp.deptid=10,"Operations",department) AS department,id.DateHired,IF(LatestDorM=1,LatestBasicRate*2,LatestBasicRate*26.08) AS BasicRate, CONCAT(id.FirstName," ",LEFT(id.MiddleName,1),". ",id.SurName) AS Name,CompanyName FROM attend_30currentpositions cp JOIN 1_gamit.0idinfo id ON cp.IDNo=id.IDNo JOIN 1employees e ON e.IDNo=cp.IDNo JOIN 1companies c ON e.RCompanyNo=c.CompanyNo JOIN payroll_20latestrates lr ON cp.IDNo=lr.IDNo WHERE cp.IDNo='.$_POST['IDNo'];

	$stmt=$link->query($sql); $row=$stmt->fetch();

	echo '<center><img src="../generalinfo/logo/'.$row['Company'].'.png"></center><br><br><br><br>'.$cert.'';

	
	echo 'This is to certify that <b>'.($row['Gender']==1?'MR.':'MS.').' '.strtoupper($row['Name']).'</b> has been an employee of '.$row['CompanyName'].' from '.date('F d, Y', strtotime($row['DateHired'])).' up to present. Currently, '.($row['Gender']==1?'he':'she').' holds the position of '.($row['Position']).' under the '.$row['department'].' Department, and receives a monthly gross salary amounting to Php '.(number_format($row['BasicRate'],2)).'.
<br><br>
This certification is issued upon the request of the aforementioned
employee for the purpose of acquiring a loan.
<br><br>
The information herewith is for reference only. The company does not accept
responsibility or liability from any transaction arising from this
certification.';
break;

case 3: // travel pass

	$sql='SELECT Gender,Position,Company,REPLACE(b.RegisteredAddress,"<br>",", ") AS RegisteredAddress,if(cp.deptid=10,"Operations",department) AS department, CONCAT(e.FirstName," ",LEFT(e.MiddleName,1),". ",e.SurName) AS Name, e.SurName,CompanyName, b.Branch FROM attend_30currentpositions cp JOIN 1employees e ON e.IDNo=cp.IDNo JOIN 1branches b ON b.BranchNo='.$_POST['BranchNo'].' JOIN 1companies c ON c.CompanyNo=b.CompanyNo WHERE cp.IDNo='.$_POST['IDNo'];

	$stmt=$link->query($sql); $row=$stmt->fetch();

	echo '<center><img src="../generalinfo/logo/'.$row['Company'].'.png"></center><br><br><br><br>'.$cert.'';

	
	echo 'This is to certify that <b>'.($row['Gender']==1?'MR.':'MS.').' '.strtoupper($row['Name']).'</b> is currently employed at '.$row['CompanyName'].' located at '.$row['RegisteredAddress'].'.<br><br> '.$row['CompanyName'].' is engaged in the  supply of parts and consummables for refrigeration and air-conditioning units of the essential sectors such as but not limited to hospitals, supermarkets, cold storages, hotels, ambulances, etc.
<br><br>Due to the nature of work, '.($row['Gender']==1?'he':'she').' is required to travel to different areas serviced by our company.';
	break;
default:
	break;
}


$sql='SELECT Position,CONCAT(id.FirstName," ",LEFT(id.MiddleName,1),". ",id.SurName) AS Name FROM 1_gamit.0idinfo id JOIN attend_30currentpositions cp ON id.IDNo=cp.IDNo WHERE id.IDNo='.$_POST['ApproveByNo'];
	$stmt=$link->query($sql); $row=$stmt->fetch();
	

echo '<br><br>Issued, signed, and sealed this '.date('jS').' of '.date('F').'
'.date('Y').' at Taguig City.<br><br><br><br><br>Sincerely,<br><br><br><br><b>'.strtoupper($row['Name']).'</b><br>'.$row['Position'].'';


echo '<div class="divFooter"<font style="font-size:9pt;">This document is not valid without dry seal.</font><br><br><br><br><br><i><table width="100%"><tr><td style="text-align:center;margin-left:10%;font-size:9.5pt;"><i>Unit 1018 High Street South Corporate Plaza Tower 1, 26th Street Bonifacio Global City, Taguig 1634.<br>Tel: (02) 7751 2213 www.1rotary.com.ph</i></td></tr></table></div>';
break;

	
	
}

 $link=null; $stmt=null;
?>
</div> <!-- end section -->
