<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(2205,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');


$which=!isset($_GET['w'])?'List':$_GET['w'];

switch($which){
	case'List':
	if(!isset($_POST['Filter'])){
		$from=date("Y-m-d", strtotime("-1 months"));
		$to=date('Y-m-d');	
	}else{
		$from=$_POST['From'];
		$to=$_POST['To'];	
	}	
		echo '<title>IT Updates</title>';
		echo '<br><h3><a href="../info/faq/itchecklists.php">IT Checklists</a></h3>';
		echo'</br><h3>IT Updates</h3></br>
		<div style="border:1px solid black; width:400px; padding:5px"> <h4>Filtering:</h4></br>
		<form method="post" action="itupdates.php?w=List">
		From: <input type="date" name="From" value="'.$from.'">
		To: <input type="date" name="To" value="'.$to.'">
		<input type="submit" name="Filter" value="Filter"> 
		</form>
		</br><h4>OR</h4></br>
		<form method="post" action="itupdates.php?w=List">
		Search For: <input type="text" name="table">
		<input type="submit" name="Search" value="Search"> 
		</form>
		</div></br>
		';
		echo'<div style="border:1px solid black; width:720px; padding:5px"><h4>Encoding:</h4></br>
		<form method="post" action="itupdates.php?w=Add">
			Date: <input type="date" name="date" value="'.date('Y-m-d').'">
			Table/File: <input type="text" name="TableFile">	
			Changes: <input type="text" name="changes">
			<input type="submit" name="submit">
			</form></div>';	
			$title='';
			$sql='select u.*,Nickname as EncodedBy,TableFile as `Table/File` from it_2updates u left join 1employees e on e.IDNo=u.EncodedByNo where '.(isset($_POST['Search'])?'TableFile like \'%'.$_POST['table'].'%\' OR Changes like \'%'.$_POST['table'].'%\'':' Date between \''.$from.'\' and \''.$to.'\'  Order By Date Desc,u.TimeStamp DESC').' ';
			// echo $sql; exit();
			$columnnames=array('Date','Table/File','Changes','EncodedBy','TimeStamp');
			$txnid='TxnID';
			$editprocess='itupdates.php?w=Edit&TxnID=';
			$editprocesslabel='Edit';
			
			include('../backendphp/layout/displayastablenosort.php');
	break;
	
	case'Add':
			$sql='insert into it_2updates set Date=\''.$_POST['date'].'\',TableFile=\''.$_POST['TableFile'].'\',Changes=\''.$_POST['changes'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:itupdates.php?w=List");
	break;
	
	case'Edit':
			 $title='Edit';
			 $txnid=intval($_GET['TxnID']);
			 $sql='select TxnID,Date,TableFile,Changes from it_2updates b  where b.TxnID=\''.$txnid.'\'';
			 // echo $sql; exit();
			 $columnnames=array('Date','TableFile','Changes');
			 $columnstoedit=array('Date','TableFile','Changes');
			 $editprocess='"itupdates.php?w=EditProcess&TxnID='.$txnid.'"'; 
			 include('../backendphp/layout/editspecificsforlists.php');
    break;	
	
	case'EditProcess':
			$txnid=intval($_GET['TxnID']);
			$sql='update it_2updates set Date=\''.$_POST['Date'].'\',TableFile=\''.$_POST['TableFile'].'\',Changes=\''.$_POST['Changes'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where TxnID=\''.$txnid.'\' and date(TimeStamp)=Curdate() and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:itupdates.php?w=List");
	break;
	
}
?>