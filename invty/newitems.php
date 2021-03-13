<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(64342,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	$which=(!isset($_GET['w'])?'NewItems':$_GET['w']);
	include_once $path.'/acrossyrs/js/includesscripts.php';
echo comboBox($link,'SELECT ItemCode, ItemDesc FROM invty_1items ','ItemDesc','ItemCode','itemlist');
switch($which){
	case'NewItems':
		$title='New Items';
	$directory='../invty/newitems.php?w=NewItems';
	$sqla='select count(ItemCode) as Available from invty_1items i join invty_1category c on c.CatNo=i.CatNo where YEAR(ItemSince)='.$currentyr.'';
	$stmta=$link->query($sqla); $resulta=$stmta->fetch();
		
	echo '<title>'.$title.'</title></br><h3>'.$title.'</h3></br>';
	if (allowedToOpen(64343,'1rtc')) {  
	//upload image
	echo'<div style="border:1px solid black; padding:10px; width:520px;"><b>Upload Image:</b></br></br>
				<form action="../acctg/uploadreceipt.php" method="POST" enctype="multipart/form-data">
					<input type="hidden" name="directory" value='.$directory.' size=4"> 
					ItemCode: <input type="text" name="UploadID" list="itemlist" size="10" required>
					<input type="hidden" name="newdir" value="'.$path.'itempics/peritemcode">
					<input type="file" name="userfile" accept="image/jpg"><input type="submit" name="submit" value="Upload"> 
                </form></div></br>';
				 
	}		 
		$sql='select ItemCode,date(i.TimeStamp) as Date,Category,ItemDesc as ItemDescription from invty_1items i join invty_1category c on c.CatNo=i.CatNo where YEAR(ItemSince)='.$currentyr.' order by ItemSince Desc';
		// echo $sql; exit();
		$stmt=$link->query($sql); $result=$stmt->fetchAll();
		echo'<b>'.$resulta['Available'].' New Items This Year</b></br>
		<table id="table1" class="display" style="width:100%; font-size: 10pt; ">
		<thead><tr><th size=10>Date</th><th size=6>ItemCode</th><th>Category</th><th>ItemDescription</th><th>Image</th></tr></thead><tbody>';
		foreach($result as $res){
			echo '<tr><td>'.$res['Date'].'</td><td>'.$res['ItemCode'].'</td><td>'.$res['Category'].'</td><td>'.$res['ItemDescription'].'</td><td>
			<img src="../../itempics/peritemcode/'.$res['ItemCode'].'.jpg"  /></td></tr>';
		}
		echo'</tbody></table>';
	break;
		
}
?>