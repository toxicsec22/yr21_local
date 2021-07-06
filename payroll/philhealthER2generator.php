<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(9014,'1rtc')) { echo 'No permission'; exit; }

$showbranches=false;

include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_GET['w'])?'lists':$_GET['w'];

switch ($which){
	case'lists':
	$title='PhilHealth Er2 Generator';
	echo'<title>'.$title.'</title>';
	echo'<h3>'.$title.'</h3><br>';
       
   echo comboBox($link,'SELECT  Company, CompanyNo FROM 1companies WHERE `CompanyNo`<=6','CompanyNo','Company','companies');

   
		      
    echo '<form action="#" method="POST">Company: <input type="text" name="Company" list="companies"> <input type="submit" name="btnFilter" value="Lookup"></form><br>';
	
	
        if(isset($_POST['btnFilter'])){

            $companyno=comboBoxValue($link,'1companies','Company',$_POST['Company'],'CompanyNo');
            $sql='select id.IDNo,BranchorDept,PHICNo, CONCAT(SurName,", ",FirstName," ",MiddleName) AS FullName,IF (Position LIKE "% -%",LEFT(Position,LOCATE(" -",Position)-1),Position) AS Position,DATE_FORMAT(id.DateHired,"%m/%d/%Y") AS DateHiredF,DateHired FROM 1_gamit.0idinfo id JOIN attend_30currentpositions cp ON id.IDNo=cp.IDNo WHERE RCompanyNo='.$companyno.' ORDER BY DateHired DESC,SurName,FirstName,MiddleName';
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";

	echo'<form action="philhealthER2output.php?CompanyNo='.$companyno.'" method="POST"><table style="font-size:10.5pt;width:70%;background-color:white;padding:5px;">
    <tr><th colspan=6 style="font-size:14pt;">'.$_POST['Company'].'</th></tr>
 <tr><th width="50px">All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></th><th>Employee</th><th>BranchorDept</th><th>Position</th><th>PHICNo</th><th>DateHired</th></tr>';
		foreach($result as $res){
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td style="text-align:right;"><input type="checkbox" value="'.$res['IDNo'].'" name="IDNo[]" /></td><td>'.$res['FullName'].'</td><td>'.$res['BranchorDept'].'</td><td>'.$res['Position'].'</td><td>'.$res['PHICNo'].'</td><td>'.$res['DateHired'].'</td></tr>';
			$colorcount++;
		}
		echo '<tr><td colspan=6 align="center"><input style="background-color:green;color:white;width:200px" type="submit" value="Generate Er2" name="btnGenerate" OnClick="return confirm(\'Are you SURE?\');"></td></tr></table></form>';
    }
	break;
	
	
	
	
}

?>

<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>