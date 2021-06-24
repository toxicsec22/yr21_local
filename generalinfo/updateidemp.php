<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php');
if (!allowedToOpen(64984,'1rtc')) { echo 'No Permission'; exit(); }
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila');



?>

<br><div id="section" style="display: block;">

<?php

$which=(!isset($_GET['w'])?'List':$_GET['w']);

switch ($which)
{
	case 'List':
	if (allowedToOpen(64984,'1rtc')) {
		$withstores='';
		if (allowedToOpen(64988,'1rtc')){
			$withstores=' OR PseudoBranch=0';
		}
		
		$deptcondition='';
		if (allowedToOpen(64987,'1rtc')) { //all
			$deptcondition='';
		} else { //dept
			$deptcondition=' AND (deptid IN (SELECT deptid FROM attend_1positions WHERE PositionID='.$_SESSION['&pos'].' UNION SELECT deptid FROM 1departments WHERE deptheadpositionid='.$_SESSION['&pos'].') '.$withstores.')';
		}
		
		$title='Uniform and Giveaway Shirt Sizes';
		
		if(isset($_POST['ShowAll'])){
         $sqlcond='';
		 $subtitle='';
		} else {
			$sqlcond=' AND (UniformSize IS NULL OR UniformSize="" OR ShirtSize IS NULL OR ShirtSize="") ';
			$subtitle=' (No Uniform/Giveaway Shirt Size)';
		}
		echo '<form action="#" method="POST">';
		if(!isset($_POST['ShowAll'])){
		echo '<input type="submit" value="Show All" name="ShowAll">';
		} else {
			echo '<input type="submit" value="Show No Sizes" name="ShowNoSizes">';
		}
		echo '</form>';
		$sql='SELECT IF(deptid IN (2,3,4,10),b.Branch,dept) AS Branch,e.IDNo,
UniformSize,ShirtSize,FullName FROM 1employees e JOIN attend_30currentpositions cp ON e.IDNo=cp.IDNo JOIN 1branches b ON b.BranchNo=cp.BranchNo WHERE 1=1 '.$sqlcond.$deptcondition.' ORDER BY b.Branch,FullName ';
		// echo $sql; 
		$stmtres=$link->query($sql);
		$results=$stmtres->fetchAll();   
	echo '<style>
		th {
		  text-align:left;
		  background: white;
		  position: sticky;
		  top: 0;
		  padding: 2px;
		  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
		}
	</style>';
	 $colorcount=0;
        $rcolor[0]="FFFFCC";
        $rcolor[1]="FFFFFF";
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.$subtitle.'</h3><br>';
		//<a href="pics/poloshirtchartsize.jpg"><font style="font-size:7pt">See uniform size chart</font></a>
		echo '<table border="1px solid black;"; style="width:45%;border-collapse:collapse;font-size:8.5pt;">';
		echo '<tr><th>Name</th><th>Branch/Dept</th><th>Uniform Size<br></th><th>Giveaway Shirt Size<br><a href="pics/shirtsize.jpg"><font style="font-size:7pt">See shirt size chart</font></a></th><th></th></tr>';
		$totalc=0;
		foreach($results AS $result){
			echo '<form action="updateidemp.php?w=UpdateEmpInfo&IDNo='.$result['IDNo'].'" method="POST" autocomplete="off"><tr bgcolor="'. $rcolor[$colorcount%2].'"><td style="padding:2px;">'.$result['FullName'].'</td><td style="padding:2px;">'.$result['Branch'].'</td>
			
			<td style="padding:2px;'.($result['UniformSize']==''?'background-color:red;':'').'">
			<select name="UniformSize">
					<option value=""  '.($result['UniformSize']==''?'selected':'').'> - Select - </option>
					<option value="XS" '.($result['UniformSize']=='XS'?'selected':'').'>XS</option>
					<option value="S" '.($result['UniformSize']=='S'?'selected':'').'>S</option>
					<option value="M" '.($result['UniformSize']=='M'?'selected':'').'>M</option>
					<option value="L" '.($result['UniformSize']=='L'?'selected':'').'>L</option>
					<option value="XL" '.($result['UniformSize']=='XL'?'selected':'').'>XL</option>
					<option value="XXL" '.($result['UniformSize']=='XXL'?'selected':'').'>XXL</option>
				</select>
			</td>
			<td style="padding:2px;'.($result['ShirtSize']==''?'background-color:red;':'').'">
			<select name="ShirtSize">
					<option value=""  '.($result['ShirtSize']==''?'selected':'').'> - Select - </option>
					<option value="XS" '.($result['ShirtSize']=='XS'?'selected':'').'>XS</option>
					<option value="S" '.($result['ShirtSize']=='S'?'selected':'').'>S</option>
					<option value="M" '.($result['ShirtSize']=='M'?'selected':'').'>M</option>
					<option value="L" '.($result['ShirtSize']=='L'?'selected':'').'>L</option>
					<option value="XL" '.($result['ShirtSize']=='XL'?'selected':'').'>XL</option>
					<option value="XXL" '.($result['ShirtSize']=='XXL'?'selected':'').'>XXL</option>
				</select>
			</td>
			<td style="padding:2px;"><input type="submit" value="Update" name="btnUpdate"  onClick="return confirm(\'Are You Sure?\');"></td>
			</tr></form>';
			$colorcount++;
			$totalc++;
		}
		echo '</table>';
		echo '<b>'.$totalc. " total record(s)</b>";
	} else {
		echo 'No permission'; exit;
	}
	break;
	
	
	case 'UpdateEmpInfo':

	
	$sql='UPDATE `1employees` SET UniformSize="'.$_POST['UniformSize'].'",ShirtSize="'.$_POST['ShirtSize'].'",EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE IDNo='.$_REQUEST['IDNo'];
	$stmt=$link->prepare($sql); $stmt->execute();
	
	
	header('Location:updateidemp.php?w=List');
	
	break;
	
	
}


 $link=null; $stmt=null;
?>
</div> <!-- end section -->
