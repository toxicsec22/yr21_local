<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(7496,'1rtc')) { echo 'No permission'; exit; }

$which=!isset($_GET['w'])?'Lists':$_GET['w'];

$showbranches=false;

include_once('../switchboard/contents.php');



switch($which){
	case'Lists':
	if (!allowedToOpen(7496,'1rtc')) { echo 'No permission'; exit; }
	
	echo '<style>
		th {
		  text-align:left;
		  background: white;
		  position: sticky;
		  top: 0;
		}
	</style>';
	
		$title='Branch Price Levels';
		
		echo '<title>'.$title.'</title>';
		echo'<br><h3>'.$title.'</h3></br>';
		
		$sqlarea='select AreaNo,Area FROM 0area WHERE AreaNo>=1 ORDER BY Area';
		$stmtarea=$link->query($sqlarea); $rowareas=$stmtarea->fetchAll();
		$optionarea='';
		foreach($rowareas AS $rowarea){
				$optionarea.='<option value="'.$rowarea['AreaNo'].'" '.((isset($_POST['AreaNo']) AND $_POST['AreaNo']==$rowarea['AreaNo'])?'selected':'').'>'.$rowarea['Area'].'</option>';
		}
		
		echo '<form action="#" method="POST">Area: <select name="AreaNo"><option value="">All Areas</option>'.$optionarea.'</select> Price Level: <select name="PLNo"><option value="">All Price Levels</option><option value="1" '.((isset($_POST['PLNo']) AND $_POST['PLNo']==1)?'selected':'').'>1</option><option value="2" '.((isset($_POST['PLNo']) AND $_POST['PLNo']==2)?'selected':'').'>2</option><option value="3" '.((isset($_POST['PLNo']) AND $_POST['PLNo']==3)?'selected':'').'>3</option><option value="4" '.((isset($_POST['PLNo']) AND $_POST['PLNo']==4)?'selected':'').'>4</option><option value="5" '.((isset($_POST['PLNo']) AND $_POST['PLNo']==5)?'selected':'').'>5</option></select> <input type="submit" name="btnLookup" value="Lookup"></form><br>';
		$condi='';
		if(isset($_POST['btnLookup'])){
			$condi='';
			if($_POST['AreaNo']<>''){
				$condi.=' AND b.AreaNo='.$_POST['AreaNo'];
			}
			if($_POST['PLNo']<>''){
				$condi.=' AND PriceLevel='.$_POST['PLNo'];
			}
		}
		$sql='select Area,b.BranchNo,Branch,PriceLevel,PriceLevelRemarks FROM 1branches b JOIN 0area a ON b.AreaNo=a.AreaNo WHERE Active=1 AND PseudoBranch=0 '.$condi.' ORDER BY Area,Branch';
		$stmt=$link->query($sql); $rows=$stmt->fetchAll();
		
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		
		echo '<table style="background-color:#ffffff;">';
		echo '<tr><th style="padding:3px;">Area</th><th style="padding:3px;">Branch</th><th style="padding:3px;width:110px;">Price Level<br>1 &nbsp;&nbsp;2 &nbsp;&nbsp;3 &nbsp;&nbsp;4 &nbsp;&nbsp;5</th><th style="padding:3px;">Price Level Remarks</th><th></th></tr>';
		foreach($rows AS $row){
			echo '<form action="branchpricelevels.php?w=UpdateBranchPriceLevel&BranchNo='.$row['BranchNo'].'&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off><tr bgcolor='. $rcolor[$colorcount%2].'><td style="padding:3px;">'.$row['Area'].'</td><td>'.$row['Branch'].'</td><td style="width:110px;padding:3px;"><input type="radio" name="PL'.$row['BranchNo'].'" '.($row['PriceLevel']==1?'checked':'').' value="1"> &nbsp;<input type="radio" name="PL'.$row['BranchNo'].'" '.($row['PriceLevel']==2?'checked':'').' value="2"> &nbsp;<input type="radio" name="PL'.$row['BranchNo'].'" '.($row['PriceLevel']==3?'checked':'').' value="3"> &nbsp;<input type="radio" name="PL'.$row['BranchNo'].'" '.($row['PriceLevel']==4?'checked':'').' value="4"> &nbsp;<input type="radio" name="PL'.$row['BranchNo'].'" '.($row['PriceLevel']==5?'checked':'').' value="5"> &nbsp;</td>'.((allowedToOpen(7491,'1rtc'))?'<td style="padding:3px;"><input type="text" name="PriceLevelRemarks" value="'.$row['PriceLevelRemarks'].'" size="50"></td><td style="padding:3px;"><input type="submit" value="Update" name="btnUpdate" OnClick="return confirm(\'Are you sure you want to update?\');"></td>':'<td style="padding:3px;">'.$row['PriceLevelRemarks'].'</td><td style="padding:3px;"></td>').'</tr></form>';
			$colorcount++;
		}
		echo '</table>';
	break;
	
	
	case 'UpdateBranchPriceLevel':
	if (!allowedToOpen(7491,'1rtc')) { echo 'No permission'; exit; }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$branchno=intval($_GET['BranchNo']);
			
			$sql='update 1branches set PriceLevel='.$_POST['PL'.$branchno].',PriceLevelRemarks="'.$_POST['PriceLevelRemarks'].'" where BranchNo='.$branchno.'';
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:branchpricelevels.php?w=Lists");
	break;
	
	
}
?>
