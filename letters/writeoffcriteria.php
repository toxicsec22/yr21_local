<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$IDNo = $_SESSION['(ak0)'];
// check if allowed
$allowed=array(5901);$allow=0;


foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;
}
allowed:
// end of check
 
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

include_once "../generalinfo/lists.inc";

$which=(!isset($_GET['w'])?'List':$_GET['w']);
?>
<html>
<head>
<title>Customer Criteria notice</title>
<style>
a:link {
  text-decoration: none;
}

a:visited {
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}

a:active {
  text-decoration: underline;
}
.post-container {
    border-bottom: 2px solid;
    overflow: auto;  
	padding:5px;
}
.post-thumb {
    float: left
}
.post-thumb img {
    display: block
}
@media print
{
#printPageButton {
    display: none;
  }

}
</style>
<?php 

if (in_array($which,array('view','action','add&edit'))){
	if(isset($_POST['lookup'])){
		if(empty($_POST['ClientName'])){
			?>
			<script type="text/javascript">
				alert('No Client Selected');
				window.location = "viewletters.php";
				</script>

		<?php
	}else{
	$clientno=getValue($link,'1clients','Left(`ClientName`,20)',addslashes($_POST['ClientName']),'ClientNo');
	}
	}else{
		$clientno = $_SESSION["Client"];
	}
		$sql='Select ClientName,CONCAT(StreetAddress,", ",Barangay,", ",TownOrCity,", ",Province) AS CompleteAddress,ContactPerson,TelNo1,TelNo2,c.Mobile,EmailAddress,c.TIN,b.CompanyNo,CompanyName,Company,c.ClientNo as clientno, b.Branch as Branch FROM 1clients c JOIN gen_info_1branchesclientsjxn bc ON c.ClientNo=bc.ClientNo JOIN 1branches b on b.BranchNo=bc.BranchNo JOIN 1companies co ON co.CompanyNo=b.CompanyNo WHERE c.ClientNo='.$clientno;
	 	 $stmt=$link->query($sql);
	  	 $result=$stmt->fetch();
	  	 $count=$res=$stmt->rowCount();
	  	 $result1=$stmt->fetchAll();
	   
	$User = 'Select concat(FirstName, " " ,Left(MiddleName,1) ,". ",SurName) as FullName, Position from `1employees` e join `attend_30currentpositions` p on e.IDNo=p.IDNo where e.IDNo='.$IDNo.'';
	$stmt=$link->query($User);
	$userRes=$stmt->fetch();

	$Select = 'SELECT * FROM acctg_writeoffcriteria';
	$stmt=$link->query($Select);
	$fetchAll=$stmt->fetchAll();

	$Select1 = 'SELECT * FROM acctg_writeoffreco where ClientNo = '.$clientno.'';
	$stmt=$link->query($Select1);
	$fetch=$stmt->fetch();

	$_SESSION["Client"] = $result['clientno'];
	$i = 0;
	$c = 0;	
	 }
    ?>

    </form>
<?php

switch ($which) {

	case 'view':

echo '<form method  = "POST"> ';
		echo '<div style="margin-left:30px;font-size:12.5pt">';
echo'<br>' .date("  F d, Y"). '<br>';
echo '</div>';

		echo '<center><br><b><h3><a href="" onClick="window.print();return false">CRITERIA FOR WRITE OFF</a></h3></b></center>';
	echo '<div style="width: 700px;  border: 2px solid grey; padding: 25px; margin: 25px;">';
  echo $result['ClientName'].'<br>';

  echo '<br>Client No: '.$result['clientno'].'<br><br>';
  echo '<div style="margin-left:33px;font-size:12.5pt">';
echo '    <table> <tr>';
foreach($result1 as $brach){
 $i++;
    echo '<td> • '.$brach['Branch'].'</td>';

    if($i == 3) {
        echo '</tr><tr>';
        $i = 0;
    }
}
echo '    </tr>
        </table>';
  echo '</div>';
  echo '</div>';
	echo '<div style="margin-left:30px;font-size:12.5pt"><br> ';

	echo '<br><br> <div style="margin-left:50px;font-size:12.5pt"> An account is determined to be uncollectible if it meets one or more of the following criteria: </div>
		
		<br>';
	
		foreach ($fetchAll as $value) {
		 $c++;
			echo '<input type = "checkbox" name ="checkbox[]" value="'.$value['TxnID'].'" '.(in_array($value['TxnID'],explode(",",$fetch['Criteria'])) !== false ? 'checked = "checked"': '').'>';
			echo $c.'. '.$value['Statement'].'<br><br>';
		}
	echo '<br><br>
		Remarks :
		<br><textarea  type="text" style="border:none"  name="Remarks" rows="8" cols="200" >'.$fetch['Remark'].'</textarea><br><br>
		<button id="printpagebutton"  name = "update"  formaction ="writeoffcriteria.php?w=action&c='.$clientno.'"> Update/Save</button>
		<button id="printpagebutton"   formaction ="overduenotice.php"> Back</button>
		<table><th>
		Prepared by:
		</th><th>
		Recommending Approval:</th><th>	
		Approved by:
		</th><br><br><tr><td><br><center>'. $userRes['FullName'].'</center>
		</td><td><br>
		<center>__________________</center>
		</td><td><br>
		<center>__________________</center>
		</td></tr><tr><td></td><td ></td><td>
		<center>RCE/JYE</center></td></tr>
</table>
	</div>';
	echo '</form>';
break;

case 'action':

$remark = $_POST['Remarks'];
if ($fetch > 0){
 $update = 'UPDATE acctg_writeoffreco SET Criteria ="'.implode(',',$_POST['checkbox']).'",TimeStamp = Now(),EncodedBy = '.$IDNo.',Remark = \''.$remark.'\' WHERE ClientNo = '.$clientno.'';

 $stmt=$link->prepare($update); 
 $stmt->execute(); 
 header('location:writeoffcriteria.php?w=view');
}else{
$insert = 'INSERT INTO `acctg_writeoffreco` (ClientNo,Criteria,EncodedBy,`TimeStamp`,Remark) VALUES ('.$clientno.',"'.implode(',',$_POST['checkbox']).'",'.$IDNo.',Now(),\''.$remark.'\')';
$stmt=$link->prepare($insert);
$stmt->execute(); 
header('location:writeoffcriteria.php?w=view');
}
break;

case 'addedit':

echo '<div style="margin-left:50px;font-size:12.5pt">';
echo '<br><h2> ADD / EDIT Criteria Statement	</h2>';
echo '<i><p style = "color:red "> Note: Cannot Edit/Update Criteria that already been checked/Issued!</p> </i>';
	$Select = 'SELECT * FROM acctg_writeoffcriteria';
	$stmt=$link->query($Select);
	$count=$res=$stmt->rowCount();
	$stmt=$link->query($Select);
	$fetchAll=$stmt->fetchAll();
	$i = 0;
	echo '<br><form method = "POST">';
foreach ($fetchAll as $value) {
	$i++;
	echo '<input type = "hidden" name ="TxnID_'.$i.'" value="'.$value['TxnID'].'">';
	echo '<br> <textarea  type="text" style="border:none"  name="Criteria_'.$i.'" rows="2" cols="300" >'.$value['Statement'].'</textarea> ';

}

echo '<br><textarea  type="text" style="border:none"  name="Remarks" rows="4" cols="90" placeholder ="Add Criteria Here"></textarea><br>
<input type = "submit" name="submit" value="Add/Edit">
</form>
</div>';

if(isset($_POST['submit'])){

	if(!empty($add = $_POST['Remarks'])){
			$insert = 'INSERT INTO acctg_writeoffcriteria (Statement) VALUES ("'.$_POST['Remarks'].'")';
			
			$stmt=$link->prepare($insert);
			$stmt->execute(); 

		}
	$x = 1;
	while($x <=$count){
		$sql2='SELECT * FROM acctg_writeoffcriteria WHERE TxnID='.$_POST["TxnID_$x"].'';
		$stmt=$link->query($sql2);
		$res2=$stmt->fetch();
		if($_POST["Criteria_$x"] <> $res2['Statement']){
			$checker = 'SELECT * FROM  acctg_writeoffreco WHERE FIND_IN_SET('.$_POST["TxnID_$x"].',Criteria)';
			$stmt=$link->query($checker);
			$res3=$stmt->fetch();
	
			if($res3 > 0){
			}else{
			$update = 'UPDATE acctg_writeoffcriteria SET Statement = "'.$_POST["Criteria_$x"].'" where TxnID ='.$_POST["TxnID_$x"].'';
		
			$stmt=$link->prepare($update);
			$stmt->execute();
		
				
		}
		}
		
		$x++;
	}


	header('location:writeoffcriteria.php?w=addedit');
}
break;

}
		


noform:
$link=null; $stmt=null;
?>
 <style type="text/css">
 	 table {
  width: 90%;
}
th {
  height: 30px;
}
 </style>
