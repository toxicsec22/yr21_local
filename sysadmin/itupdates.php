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
		echo '<br><h3><a href="../info/faq/itchecklists.php">IT Checklists</a> &nbsp; &nbsp; &nbsp; <a href="itupdates.php?w=UserComments">User Comments, Questions and Suggestions</a></h3>';
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
			$txnidname='TxnID';
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


	case 'UserComments':
$title='User Comments and Suggestions';

$sqlmain='select TxnID,Comments,DetailsOrReply,
(CASE
		WHEN Done=0 THEN "Pending"
		WHEN Done=1 THEN "Read and Agree"
		WHEN Done=2 THEN "Read and No Decision"
		WHEN Done=3 THEN "Read and Disagree"
		WHEN Done=4 THEN "Read and Done"
		WHEN Done=5 THEN "Read Comments"
END)

AS Status, slc.TimeStamp,FullName AS EncodedBy,Position,IF(deptid IN (2,10),Branch,dept) AS Branch FROM events_2syslayoutcomments slc left join attend_30currentpositions cp on cp.IDNo=slc.EncodedByNo  ';
$orderby='Order By slc.TimeStamp DESC';
$sql=$sqlmain.' WHERE Done=0 '.$orderby;
$columnnames=array('Comments','EncodedBy','Position','Branch','TimeStamp','DetailsOrReply','Status');
$columnstoedit=array('DetailsOrReply');

$columnstoeditselect=array('Status');

$sqlforselect = '
SELECT 0 AS Done, "Pending" AS `Status` UNION 
SELECT 1 AS Done, "Read and Agree" AS `Status` UNION 
SELECT 2 AS Done, "Read and No Decision" AS `Status` UNION 
SELECT 3 AS Done, "Read and Disagree" AS `Status` UNION 
SELECT 4 AS Done, "Read and Done" AS `Status` UNION
SELECT 5 AS Done, "Read Comments" AS `Status`
';
$stmtselect = $link->query($sqlforselect);
$options='';
while($rowselect = $stmtselect->fetch())
{
	$options .= '<option value="'.$rowselect['Done'].'">'.$rowselect['Done'].' - '.$rowselect['Status'].'</option>';
}


$txnidname='TxnID'; //$txnid='TxnID';

$editprocess='itupdates.php?w=Reply&TxnID='; $editprocesslabel='Enter';

include('../backendphp/layout/displayastableeditcells.php');



unset($editprocess, $editprocesslabel);
$columnstoedit=array(); $editprocess=''; $editprocesslabel=''; $title='';

$addlprocess='itupdates.php?w=Undo&TxnID='; $addlprocesslabel='Undo';
$editprocess='itupdates.php?w=EmailMessages&TxnID='; $editprocesslabel='Email';

$columnnames=array('Comments','EncodedBy','Position','Branch','TimeStamp','DetailsOrReply');

$formdesc='<br><br></i><b>Read and Agree<b><i>'; 
$sql=$sqlmain.' WHERE Done=1 '.$orderby;
include('../backendphp/layout/displayastablenosort.php');


$formdesc='<br><br></i><b>Read and No Decision<b><i>'; 
$sql=$sqlmain.' WHERE Done=2 '.$orderby;
include('../backendphp/layout/displayastablenosort.php');


$formdesc='<br><br></i><b>Read and Disagree<b><i>'; 
$sql=$sqlmain.' WHERE Done=3 '.$orderby;
include('../backendphp/layout/displayastablenosort.php');


$formdesc='<br><br></i><b>Read and Done<b><i>'; 
$sql=$sqlmain.' WHERE Done=4 '.$orderby;
include('../backendphp/layout/displayastablenosort.php');


$formdesc='<br><br></i><b>Read Comments<b><i>'; 
$sql=$sqlmain.' WHERE Done=5 '.$orderby;
include('../backendphp/layout/displayastablenosort.php');

	break;

	case 'Reply':
		if($_POST['Status']=='Pending'){
			$stat=0;
		} else {
			$stat=$_POST['Status'];
		}
		$sql='update events_2syslayoutcomments set Done='.$stat.',DetailsOrReply=\''.addslashes($_POST['DetailsOrReply']).'\' where TxnID=\''.$_GET['TxnID'].'\' ';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:itupdates.php?w=UserComments");
		break;

		
		case 'Undo':
			$sql='update events_2syslayoutcomments set Done=0 where TxnID=\''.$_GET['TxnID'].'\' ';
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:itupdates.php?w=UserComments");

		break;


case 'EmailMessages':

$title='Email Message';
echo '<title>'.$title.'</title>';

	$sqlf = 'SELECT slc.EncodedByNo,Done,Comments,FullName,deptid,IF(deptid<>10,ru.Email,b.Email) AS EmailRUB,IF((SELECT EmailRUB)="",(SELECT Email FROM 1_gamit.1rtcusers WHERE IDNo=LatestSupervisorIDNo),(SELECT EmailRUB)) AS Email,`Position` FROM events_2syslayoutcomments slc JOIN 1_gamit.1rtcusers ru ON slc.EncodedByNo=ru.IDNo LEFT JOIN attend_30currentpositions cp ON ru.IDNo=cp.IDNo JOIN 1branches b ON cp.BranchNo=b.BranchNo WHERE TxnID='.$_GET['TxnID'].';
	';
	// echo $sqlf;
	$stmtf = $link->query($sqlf);
	$rowf = $stmtf->fetch();
	
	if($rowf['Done']==1){
		$msg='Thank you very much for your suggestion. We appreciate the time and effort you have spent to share your comments, which will be considered and implemented. We always welcome bright ideas from interested individuals like you and we hope that you continue to share them with us in the future.';
	} else if($rowf['Done']==2){
		$msg='Your suggestion is very interesting. We are going to study it thoroughly. Please keep on sharing ideas that will help us improve our processes. Thank you for your valued input.';
	} else if($rowf['Done']==3){
		$msg='Thank you for your suggestion. We reviewed and studied it carefully. Unfortunately, we cannot implement it at this time. Please continue to share more ideas. Thank you.';
	} else if($rowf['Done']==4){
		$msg='This is to inform you that your suggestion has been implemented. Thank you. We look forward to more new ideas for improvements.';
	}  else {
		$msg='Thank you! we really appreciate your comment.';
	}





	$message='';
if(isset($_POST['btnEmail'])){
	require $path.'/acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php';
    include_once $path.'/acrossyrs/dbinit/emailpassword.php';

    $mail = new PHPMailer;
    $mail->IsSMTP();								//Sets Mailer to send message using SMTP
    $mail->Host = 'smtp.gmail.com';		//Sets the SMTP hosts of your Email hosting, this for Godaddy
    $mail->Port = '587';								//Sets the default SMTP server port
    $mail->SMTPAuth = true;							//Sets SMTP authentication. Utilizes the Username and Password variables
    $mail->Username = '1rtcicon@gmail.com';					//Sets SMTP username
    $mail->Password = rtciconpass();					//Sets SMTP password
    $mail->SMTPSecure = 'tls';							//Sets connection prefix. Options are "", "ssl" or "tls"
    $mail->From = '1rtcicon@gmail.com';			//Sets the From email address for the message
    $mail->FromName = '1Rotary - The Industry Icon';			//Sets the From name of the message
    $mail->AddAddress($rowf['Email']);		//Adds a "To" address
    $mail->WordWrap = 50;							//Sets word wrapping on the body of the message to a given number of characters
    $mail->IsHTML(true);							//Sets message type to HTML			    				
    $mail->Subject = 'ARWAN Comments/Suggestions';			//Sets the Subject of the message
    $mail->Body = $msg.'<br><br><i>re: '.($rowf['EmailRUB']==""?$rowf['FullName'].': ':'').''.$rowf['Comments'].'</i><br><br>Regards,<br><br>IT Team';				//An HTML or plain text message body
    if($mail->Send())								//Send an Email. Return true on success or false on error
    {
        $message = '<font color="green">Email was sent successfully.</font>';
    } else {
		$message = '<font color="red">Email is not delivered.</font>';
	}

}

echo '<br><div style="background-color:#fff;border:1px solid black;padding:10px;">';
echo '<a href="itupdates.php?w=UserComments">Go back</a><br><br><b>Subject:</b> ARWAN Comments/Suggestions<br>';
	echo '<b>Body:</b> '.$msg.'';
	echo '<br><br>-- re: '.$rowf['Comments'];
	echo '<br><form action="#" method="POST"><input type="submit" name="btnEmail" value="Email to '.$rowf['Email'].'"></form><br>'.$message.'';
echo '</div>';
break;
		




		
}
?>
