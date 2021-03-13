<?php 
session_start();
$toPOST = $_SESSION['nb4A'];
$timestamp = strtotime($toPOST);
$sesDate = date('m', $timestamp);
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
include_once($path.'/acrossyrs/js/includesscripts.php');


$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;


//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';

$which=(!isset($_GET['w'])?'List':$_GET['w']);
$_GET['m'] = (!isset($_GET['m']))? $sesDate+1 : $_GET['m'];

$allowed=array(5454,5455); $allow=0;

foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1);
	goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:

if(isset($_GET['Print'])) { goto skipcontents;}
$showbranches=false;
include_once('../switchboard/contents.php');
skipcontents:

if (in_array($which,array('List','lookup_Month','lookup_final'))){
	echo '<h2>Month End Closing Checklist</h2><br>';
	echo '<form method="POST">';

include($path.'/acrossyrs/commonfunctions/selectmonth.php');
echo '</form>';

	if(allowedToOpen(5455,'1rtc')){
		echo '<a href = "closingchecklist.php?w=add">Add Statement </a>';
	} ?> 
<div>
		<title>Month End Closing Checklist</title><br/><br><br>
	<table style="padding:2px;font-size:10.5pt;background-color:#ffffff; display: inline-block; width:900"><thead style="font-weight:bold;">

<tbody><td>
</tbody>
<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>		
</td>
	<?php
	if(isset($_POST['submit'])){
		$monthValue = $_POST['month'];
		if(allowedToOpen(5454,'1rtc')){
		header('location:closingchecklist.php?w=lookup_Month&m='.$_POST['month'].'');
	} if(allowedToOpen(5455,'1rtc')){
		header('location:closingchecklist.php?w=lookup_final&m='.$_POST['month'].'');
	}}}

switch ($which) {
	case 'add':?>
	<form method="POST">
		<br><textarea type='text' name='newStatement' required=true rows="6" cols="65" ></textarea>
		<br><input type="submit" name="Add">
	</form>
	<?php
	if (isset($_POST['Add'])){
		$newStatement = $_POST['newStatement'];
		$sqlinsert2='Insert into acctg_8closinglist SET statement = "'.$newStatement.'"'; 
							$stmt=$link->prepare($sqlinsert2);
									$stmt->execute(); 
						header('location:closingchecklist.php?w=List');
	}
	break;
case 'List':
if(allowedToOpen(5454,'1rtc') or allowedToOpen(5455,'1rtc')){?>
<p><strong>Month of <?php  echo'<i>' .date("F -  Y").'<i>'; ?></strong> </p>
<br><th>Statement</th>
<th><input type="checkbox" class="chk_boxes" onclick="toggle(this);" />Check All</th>
<th>Remarks</th>

	<?php
	
	$month = date('m');
	$getRemark = 'Remark'.$month.'';
	$selectPost = "SELECT Posted  from acctg_8closingstatus where Month =  ".$month."";
			$stmt=$link->query($selectPost);
			$resPost = $stmt->fetch();
			$postValue =	$resPost['Posted'];
	$select = "SELECT TxnID,statement,`".$getRemark."` as remark,`".$month."` from acctg_8closinglist";
			$stmt=$link->query($select); 	

			$a = 1;
			?>
				<form method="POST">

			<?php while($res=$stmt->fetch()){

			
				if($postValue==1){
				?>
				<tr bgcolor=FFFFFF>
				<td width="400">  <?php echo $res['statement']; ?> </td>
				<td width="300"><center><input type = "checkbox" name="checkbox[]" value="<?php echo $res['TxnID'];?> " <?php echo($res[$month] == 1? 'checked' : '') ?> disabled = "true"> </td>
				<td><input type = "Text" name="remark[]" value="<?php echo $res[$getRemark];?>" disabled = "true"></td></tr>
	<?php 	}else{ ?><tr bgcolor=FFFFFF>
				<form method="POST">
				<td width="400"> <?php echo $res['statement']; ?> </td>
				<input type="hidden" name="TxnID<?php echo $a;?>" value="<?php echo $res['TxnID'];?>">
				<td width="300"><center><input type = "checkbox" name="checkbox_<?php echo $a; ?>"
				  value="<?php echo $res['TxnID'];?> " <?php echo($res[$month] == 1? 'checked' : '') ?>> </td>
						
				<td><input type = "Text" name="remark_<?php echo $a; ?>" value="<?php echo $res['remark'];?>">

				</td>
				</tr><?php
	}
	$a = $a +1;

		}
if($postValue<>1){
		?>

		<tr><td></td><td></td><td><br>
	<input type = "Submit" name="Update" value="Update" style="float: right;">
	</td></tr></form>
</div>
		<?php 
}
		if(isset($_POST['Update'])){
			$a=1;
			$stmt=$link->query($select);
			$count=$res=$stmt->rowCount();
			


		while($a<=$count){
		if (isset($_POST['checkbox_'.$a.''])){
			$post=1;
		} else {
			$post=0;
		}
		$sql2='SELECT * FROM acctg_8closinglist WHERE TxnID='.$_POST['TxnID'.$a.''].'';
		$stmt=$link->query($sql2); 
		$res2=$stmt->fetch();
		if($post<>$res2[$month] OR $_POST['remark_'.$a.'']<>$res2['Remark'.$month.'']){

			$sqlUpdate = 'UPDATE acctg_8closinglist SET `EncodedBy'.$month.'` = '.$_SESSION['(ak0)'].',`'.$month.'` = '.$post.', `Remark'.$month.'` = "'.$_POST['remark_'.$a.''].'" ,`TimeStamp'.$month.'` = Now()  where TxnID = '.$_POST['TxnID'.$a.''];

			$stmt=$link->prepare($sqlUpdate); 
			$stmt->execute();
			echo $sqlUpdate;
			echo '<br>';
		}
	$a+1;
	}
	header('location:closingchecklist.php?w=List');
				

				}

			
}
	break;
	case 'lookup_Month':

	if(allowedToOpen(5454,'1rtc')){
	$month = (strlen($_GET['m'])==1?'0'.$_GET['m']:$_GET['m']);
	$getStatus = $_GET['m'];
	$year = $currentyr;
	$date=date_create($year."-".$month."-15");
	$displayMonth =date_format($date,"F - Y");

?>
<p><strong>Month of <?php  echo'<i>' .$displayMonth.'<i>'; ?></strong> </p>
<br>
<th>Statement</th>
<th><input type="checkbox" class="chk_boxes" onclick="toggle(this);" />Check All</th>
<th>Remarks</th>

	<?php

		$selectPost = "SELECT Posted  from acctg_8closingstatus where Month =  ".$month."";
	
			$stmt=$link->query($selectPost);
			$resPost = $stmt->fetch();
			$postValue =	$resPost['Posted'];

			$getRemark = 'Remark'.$month.'';

		$select = "SELECT TxnID,statement,`".$getRemark."` as remark,`".$month."` from acctg_8closinglist";
				$stmt=$link->query($select); 
				$a =1;
				while($res=$stmt->fetch()){
					
					if($postValue == 1){ ?>

					<tr bgcolor=FFFFFF>
				<form method="POST">
				<td width="400"> <?php echo $res['statement']; ?> </td>
				<td width="300"><center><input type = "checkbox" name="checkbox[]" value="<?php echo $res['TxnID'];?> " <?php echo($res[$month] == 1? 'checked' : '') ?> disabled = "true"> </td>
			
				<td><input type = "Text" name="remark[]" value="<?php echo $res[$getRemark];?>" disabled = "true"></td>
				</tr>
			<?php 	
		}
			else{?>

				<tr bgcolor=FFFFFF>
				<form method="POST">
				<td width="400"> <?php echo $res['statement']; ?> </td>
				<input type="hidden" name="TxnID<?php echo $a;?>" value="<?php echo $res['TxnID'];?>">
				<td width="300"><center><input type = "checkbox" name="checkbox_<?php echo $a; ?>"
				  value="<?php echo $res['TxnID'];?> " <?php echo($res[$month] == 1? 'checked' : '') ?>> </td>
				<td><input type = "Text" name="remark_<?php echo $a; ?>" value="<?php echo $res['remark'];?>"></td></tr>

<?php }
		$a = $a+1;
		} 


		if($postValue<>1){?>
			<tr><td></td><td></td><td><br>
			<input type = "Submit" name="Update" value="Update" style="float: right;">
			</td></tr></form>
		</div>
		<?php
		} 
			if(isset($_POST['Update'])){	
			$a=1;
			$stmt=$link->query($select);
			$count=$res=$stmt->rowCount();
			


		while($a<=$count){
		if (isset($_POST['checkbox_'.$a.''])){
			$post=1;
		} else {
			$post=0;
		}
		$sql2='SELECT * FROM acctg_8closinglist WHERE TxnID ='.$_POST['TxnID'.$a.''].'';
		$stmt=$link->query($sql2); 
		$res2=$stmt->fetch();
		echo $sql2;
		if($post<>$res2[$month] OR $_POST['remark_'.$a.'']<>$res2['Remark'.$month.'']){

			$sqlUpdate = 'UPDATE acctg_8closinglist SET `EncodedBy'.$month.'` = '.$_SESSION['(ak0)'].',`'.$month.'` = '.$post.', `Remark'.$month.'` = "'.$_POST['remark_'.$a.''].'" ,`TimeStamp'.$month.'` = Now()  where TxnID = '.$_POST['TxnID'.$a.''];

			$stmt=$link->prepare($sqlUpdate); 
			$stmt->execute();
			echo $sqlUpdate;
			echo '<br>';
		}
		$a = $a+1;
	}
	header('location:closingchecklist.php?w=lookup_Month&m='.$getStatus);

			}

}
	break;

	case 'lookup_final':
	

	if(allowedToOpen(5455,'1rtc')){
	$month = (strlen($_GET['m'])==1? '0'.$_GET['m']:$_GET['m']);
	$getStatus = $_GET['m'];
	$year =$currentyr;
	$date=date_create($year."-".$month."-15");
	$displayMonth =date_format($date,"F - Y");

	$selectEncodedBy = 'SELECT acc.EncodedBy'.$month.' as EncodedBy, MAX(acc.TimeStamp'.$month.') as TimeStamp, CONCAT(e.FirstName, " ", e.Surname) AS FullName FROM 1employees e JOIN acctg_8closinglist as acc ON e.IDNo= acc.EncodedBy'.$month.'';
	$stmt=$link->query($selectEncodedBy); 
	$result1=$stmt->fetch();
	$FN =$result1['FullName'];
	$TS  =$result1['TimeStamp'];

			$getRemark = 'Remark'.$month.'';


			$selectPost = "SELECT Posted,`TimeStamp`  from acctg_8closingstatus where Month =  ".$month."";
			$stmt=$link->query($selectPost);
			$resPost = $stmt->fetch();
			$postValue =$resPost['Posted'];
			$timestamp = strtotime(	$resPost['TimeStamp']);
			$dbDate = date('m', $timestamp);
			$timestamp = strtotime($toPOST);
			$sesDate = date('m', $timestamp);

			$select = 'SELECT TxnID, statement, `'.$getRemark.'` as remark,`'.$month.'`,`TimeStamp'.$month.'` as TS from acctg_8closinglist'; 

?>

<p><strong>Month of <?php  echo'<i>' .$displayMonth.'</i></strong>';
echo '<br> <br> Latest EncodedBy:<strong>'. $FN.'</strong> <br>Latest TimeStamp: <strong>' .$TS.'</strong>'; ?> </p>
<br>

<?php if($postValue==1){ 
		echo'<th>Statement</th>';
		echo'<th >Encoded By</th>';
		echo'<th>Remarks </th>';
		echo'<th>Time Stamp </th>';
		echo'</tr>';

	}
	else{ 
		echo'<th>Statement</th>';
		echo'<th ><input type="checkbox" class="chk_boxes" onclick="toggle(this);" />Check All</th>';
		echo'<th>Remarks </th></tr>';
 
		}

			
			$stmt=$link->query($select); 
			$a =1;

				while($res=$stmt->fetch()){					
				
				if($postValue == 1){?>

					<tr bgcolor=FFFFFF>
				<form method="POST">
							<td><?php echo $res['statement']; ?></td>
				<td width="400"><center><?php echo 	$FN; ?></center> </td>
				<td width="300"><center><?php echo $res['remark']; ?></center> </td>
				<td width="300"><center><?php echo $res['TS']; ?></center> </td>

			
		
				</tr>
			<?php 	
		}
			else{?>

				<tr bgcolor=FFFFFF>
				<form method="POST">
				<td width="400"> <?php echo $res['statement']; ?> </td>
				<input type="hidden" name="TxnID<?php echo $a;?>" value="<?php echo $res['TxnID'];?>">
				<td width="300"><center><input type = "checkbox" name="checkbox_<?php echo $a; ?>"
				  value="<?php echo $res['TxnID'];?> " <?php echo($res[$month] == 1? 'checked' : '') ?>> </td>
						
				<td><input type = "Text" name="remark_<?php echo $a; ?>" value="<?php echo $res['remark'];?>">

				</td>
				</tr>

<?php
		
			}
			$a = $a+1;
			
			
		}
		

		if(isset($_POST['Update'])){

			$a=1;
			$stmt=$link->query($select);
			$count=$res=$stmt->rowCount();

		while($a<=$count){
		if (isset($_POST['checkbox_'.$a.''])){
			$post=1;
		} else {
			$post=0;
		}
		$sql2='SELECT * FROM acctg_8closinglist WHERE TxnID='.$_POST['TxnID'.$a.''].'';
		$stmt=$link->query($sql2); 
		$res2=$stmt->fetch();
		if($post<>$res2[$month] OR $_POST['remark_'.$a.'']<>$res2['Remark'.$month.'']){

			$sqlUpdate = 'UPDATE acctg_8closinglist SET `EncodedBy'.$month.'` = '.$_SESSION['(ak0)'].',`'.$month.'` = '.$post.', `Remark'.$month.'` = "'.$_POST['remark_'.$a.''].'" ,`TimeStamp'.$month.'` = Now()  where TxnID = '.$_POST['TxnID'.$a.''];

			$stmt=$link->prepare($sqlUpdate); 
			$stmt->execute();
			
		}
	$a = $a+1;
	}
	header('location:closingchecklist.php?w=lookup_final&m='.$getStatus);
	
}

			$sql1='SELECT count(`'.$month.'`) as count from acctg_8closinglist where `'.$month.'` = 1';
			$stmt=$link->query($sql1);
			$result=$stmt->fetch();
			$b = $result['count'];
		
			

			
		if($b+1== $a){

		if($postValue == 1 ){
				
				if ($dbDate < $sesDate){

				}else{
				if($dbDate > $sesDate){
		echo '<td><br><tr><td></td><td></td><td><td>';
		echo '<input type = "Submit" name="UnPost" value="UnPost" style="float: right;">';
		echo '</td></tr></form></div>';
		}
	}
		}else{
		
		echo'<td><br><tr><td></td><td></td><td>';
		echo'<input type = "Submit" name="Post" value="Post" style="float: right;">';
		echo'</td></tr></form></div>';
		
		}
	}else{
		echo'<td><br><tr><td></td><td></td><td><br>';
		echo	'<input type = "Submit" name="Update" value="Update" style="float: right;">';
		echo'</td></tr></form></div></td></form></div>';
 }


				if(isset($_POST['Post'])){
					$sql = "UPDATE acctg_8closingstatus SET Posted = 1, EncodedByNo = '".$_SESSION['(ak0)']."',TimeStamp=Now() where Month  = ".	$getStatus." ";
	
				
						$stmt=$link->prepare($sql); 
						$stmt->execute();
						header('location:closingchecklist.php?w=lookup_final&m='.$getStatus);
				}
				if(isset($_POST['UnPost'])){
					
					$sql = "UPDATE acctg_8closingstatus SET Posted = 0, EncodedByNo = '".$_SESSION['(ak0)']."',TimeStamp=Now() where Month  = ".	$getStatus." ";

				
						$stmt=$link->prepare($sql); 
						$stmt->execute();
						header('location:cclosingchecklist.php?w=lookup_final&m='.$getStatus);
				}
}
	break;  
}
?>