<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(59028,'1rtc')) { echo 'No permission'; exit; }
$which=(!isset($_GET['w'])?'LookupAWOL':$_GET['w']);

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
if($which<>'Print' AND $which<>'PrintAll'){
$showbranches=false;
include_once('../switchboard/contents.php');

include_once('../backendphp/layout/linkstyle.php');


?>
<body>	
<?php

} else {
	 echo '<title>Print AWOL Letters</title>';
		echo '<style>@media print {
			 html, body {
					height: 99%;    
		}
		}</style>';
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
}


switch ($which)
{
	
	case 'LookupAWOL':
	
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		

		$txndate=(isset($_POST['DateToday'])?$_POST['DateToday']:date('Y-m-d'));
		if(strtotime(date('Y-m-d'))<strtotime($txndate)){
			echo '<br>Future date is not allowed.';
			exit();
		}
			
			$title='AWOL ('.$txndate.')';
			$inputval='Print AWOL letter';
		
			
			
			
			echo '<br><br><form action="printawol.php?w=LookupAWOL" method="POST" autocomplete="off">Date: <input type="date" name="DateToday" value="'.$txndate.'" size="5"> <input type="submit" name="btnSetMonth" value="Lookup AWOL"></form><br>';
			
			
			
				// $sql='select a.IDNo,FullName,IF(deptid IN (2,10),Branch,dept) AS Branch,Position FROM attend_2attendance a JOIN attend_30currentpositions cp ON a.IDNo=cp.IDNo WHERE DateToday=CURDATE() AND LeaveNo=18 AND HOUR(NOW())>=Shift  AND DateToday="'.$txndate.'"';

				$sql='select a.IDNo,FullName,IF(deptid IN (2,10),Branch,dept) AS Branch,Position FROM attend_2attendance a JOIN attend_30currentpositions cp ON a.IDNo=cp.IDNo WHERE LeaveNo=18 AND DateToday="'.$txndate.'" ORDER BY Branch';
			
		$stmt=$link->query($sql); $res=$stmt->fetchAll();
		echo '<title>'.$title.'</title>';
		
		echo '<h3>'.$title.'</h3>';


		$sqlpbpi='SELECT AllowedPos FROM permissions_2allprocesses WHERE ProcessID=59029';
		$stmtpbpi=$link->query($sqlpbpi); $respbpi=$stmtpbpi->fetch();


		echo comboBox($link,'SELECT IDNo,FullName FROM attend_30currentpositions WHERE PositionID IN ('.$respbpi['AllowedPos'].') ORDER BY FullName;','IDNo','FullName','preparedbylist');

		echo '<form action="printawol.php?w=PrintAll&DateToday='.$txndate.'" method="post">';
		echo '<br><table style="padding:4px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
		echo '<thead style="font-weight:bold;"><tr><td colspan=3 align="right">To be signed by: <input type="text" size="15" name="PreparedBy" list="preparedbylist" required></td><td align="right"><input style="background-color:yellow;width:220px" type="submit" value="'.$inputval.'" /></td></tr><tr><th>All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></th><th>Employee</th><th>Branch</th><th>Position</th></tr></thead><tbody style=\"overflow:auto;\">';

		foreach($res AS $row){
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td align="right"><input type="checkbox" value="'.$row['IDNo'].'" name="idno[]" /></td><td>'.$row['FullName'].'</td><td>'.$row['Branch'].'</td><td>'.$row['Position'].'</td></tr>';
			$colorcount++;
		}
		echo '</tbody></table>';
		echo '</form>';
		
		
		
	?>

	<?php

		
		
	break; 
	
	
	case 'PrintAll':
	
		$idnos='';
		foreach ($_REQUEST['idno'] AS $idno){
			$idnos.=$idno.',';
		}
		$idnos='(0,'.substr($idnos, 0, -1).')';

		$preparedbyidno=comboBoxValue($link,'`attend_30currentpositions`','FullName',addslashes($_POST['PreparedBy']),'IDNo');

		$sqlpreparedby='SELECT Position,CONCAT(e.Nickname," ",e.SurName) AS PreparedBy FROM 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo WHERE e.IDNo='.$preparedbyidno;
		
		$stmtpreparedby=$link->query($sqlpreparedby);
		$rowpreparedby = $stmtpreparedby->fetch();

		$preparedbypos=$rowpreparedby['Position'];
		$preparedby=$rowpreparedby['PreparedBy'];

		$sql='SELECT a.IDNo,Company,e.Gender,e.Surname,CONCAT(e.FirstName," ",e.MiddleName," ",e.SurName) AS FullName,cp2.Position AS NotedByPosition,CONCAT(e2.FirstName," ",e2.SurName) AS NotedBy,IF(cp.deptid IN (2,3,10),cp.Branch,cp.dept) AS Branch from `attend_2attendance` a JOIN attend_30currentpositions cp ON a.IDNo=cp.IDNo JOIN attend_2attendancedates ad ON a.DateToday=ad.DateToday JOIN 1employees e ON cp.IDNo=e.IDNo JOIN 1companies c ON e.RCompanyNo=c.CompanyNo 
		JOIN attend_30currentpositions cp2 ON cp.deptheadpositionid=cp2.PositionID
		LEFT JOIN 1employees e2 ON e2.IDNo=cp2.IDNo
		WHERE LeaveNo=18 AND a.DateToday="'.$_GET['DateToday'].'" AND a.IDNo IN '.$idnos.'';
		
		$stmt=$link->query($sql);
		$rows = $stmt->fetchALL();


$printoutput=''; $style='';
$awoldate=date('F d, Y',strtotime($_GET['DateToday']));
foreach($rows AS $row){
    $printoutput.='<div '.$style.'>
    <center><img  src="../generalinfo/logo/'.$row['Company'].'.png"></center> <br><br>
    <table>
        <tr><td>Date:</td><td>'.date('F d, Y').'</td></tr>
        <tr><td>To:</td><td>'.$row['FullName'].'</td></tr>
        <tr><td>Branch/Dept:</td><td>'.$row['Branch'].'</td></tr>
        <tr><td>Subject:</td><td>Notice to Explain for AWOL on '.$awoldate.'</td></tr>
    </table>
    
    <br><br>Dear '.($row['Gender']==1?'Mr':'Ms').'. '.$row['Surname'].':<br><br>It has come to our attention that you went on absence without leave (AWOL) on '.$awoldate.'.<br><br>Please give your explanation in writing <i>within 5 calendar days</i> from receipt of this notice. Failure on your part to submit a written
    explanation within the given period shall constitute a waiver of your right
    to be heard, and you will abide with the interpretation and action of
    management.<br><br>Please note that the alleged breach is serious and may carry a maximum
    corrective action of suspension or termination, depending on the number of
    occurrences. Rest assured that you will be given all the opportunity to
    explain your side. Formal hearings may be conducted, if needed, and notice
    thereof shall be given to you in advance to afford you the full extent of
    due process, including the right to representation, if so desired.<br><br><br><br>Sincerely,<br><br><br><br>'.$preparedby.'<br>'.$preparedbypos.'<br><br><hr>';

    $printoutput.='<br><br>
	<table width="100%">
		<tr>
			<td valign="top">Noted by:</td><td><table style="margin-left:100px;"><tr><td width="200px">Employee\'s Signature<br>Received</td><td width="10px">:</td><td>'.str_repeat('_',20).'</td></tr><tr><td>Date</td><td>:</td><td>'.str_repeat('_',20).'</td></tr></table></td></tr>
		<tr><td><br>'.$row['NotedBy'].'<br>'.$row['NotedByPosition'].'</td><td></td></tr><tr><td><br><br></table>
		
		</div>';

    $style='style="page-break-before: always;"';
}

echo $printoutput;
		
	break;
		
	
	
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body>
<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>
