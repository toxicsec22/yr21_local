<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$IDNo = $_SESSION['(ak0)'];
if (!allowedToOpen(5901,'1rtc')) { echo 'No permission'; exit; }
 
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

include_once "../generalinfo/lists.inc";
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
?>
<html>
<head>

<style>
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
	p {page-break-before:always}
	q{
		display: none;
	}
	<?php if(isset($_GET['w']) AND ($_GET['w']=='PromissoryNote')){?>
	div { text-align:justify; }
	<?php } ?>
	#container{width:100%;}

	 html, body {
			height: 99%;    
		}
}
</style>
</head>
<body>

<?php


if(allowedToOpen(5901,'1rtc')){

	$which=$_GET['w'];
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
	
		
	$DeptHead = 'SELECT concat(e.FirstName, " " ,Left(e.MiddleName,1) ,". ",e.SurName) as FullName,p.Position,  IF(p.IDNo='.$IDNo.',p.LatestSupervisorIDNo,p.IDNo) AS DeptHead FROM  `1employees`  e join `attend_30currentpositions` p on e.IDNo=p.IDNo  WHERE p.PositionID=(SELECT c.supervisorpositionid from attend_30currentpositions as c where c.IDNo='.$IDNo.')';
	$stmt=$link->query($DeptHead);
	$deptRes=$stmt->fetch();

	$User = 'Select concat(FirstName, " " ,Left(MiddleName,1) ,". ",SurName) as FullName, p.IDNo,Position from `1employees` e join `attend_30currentpositions` p on e.IDNo=p.IDNo where e.IDNo='.$IDNo.'';
	$stmt=$link->query($User);
	$userRes=$stmt->fetch();


	$sql='SELECT format(Sum(a.InvBalance),2) as CurrentBalance,TRUNCATE(Sum(a.InvBalance),0) as CurrentBalanceVal,a.ClientNo,c.ClientName,CONCAT(c.StreetAddress,", ",c.Barangay,", ",c.TownOrCity,", ",c.Province) AS CompleteAddress,c.ContactPerson,d.CompanyNo,d.CompanyName,d.Company from acctg_33qrybalperrecpt a JOIN 1clients c ON c.ClientNo = a.ClientNo JOIN 1branches b on b.BranchNo = a.BranchNo JOIN 1companies d on b.CompanyNo = d.CompanyNo where a.ClientNo = '.$clientno.' group by d.CompanyNo asc';
	   $stmt=$link->query($sql);
	   $result=$stmt->fetchall();

	   

	switch ($which){

	case 'Overdue': 
	foreach($result as $company){
	   $img=$company['Company'];
	   $msgregards=$company['CompanyName'];
	   
	   $compadd='<font size="2pt">Suite 1018 High Street South Corporate Plaza Tower 1<br>26th Street Bonifacio Global City, Taguig City<br>Tel no. 7751-2213<br><br>www.1rotary.com.ph</font>';
	   $imglink='<img   src="../generalinfo/logo/'.$img.'.png">';
	    ?>
    </form>
    <title>Customer Overdue Notice</title>
<?php
$marginintd='padding-left: 50px;';
echo '<div class="post-container">                
    <div class="post-thumb">
		<a href="" onClick="window.print();return false">'.$imglink.'</a>
	</div>
    <div class="post-content" align="right">
        '.$compadd.'</div>
</div>';

	echo '<br><br>';
	echo '<div style="margin-left:15px;font-size:12.5pt">'.date("  F d, Y").'<br><br>
	
		<br> '.$company['ClientName'].'
		<br> '.$company['CompleteAddress'].'
		<br>Attn:  '.$company['ContactPerson'].' 
		<br><br><b><h2>SUBJECT: OVERDUE NOTICE </h2></b>

		<br><br>Dear Valued Customer:
		<br>
		<br>
		<br>
		
		Thank you for doing business with us.
		<br>
		<br>
		

		Our records to date show that your account has unsettled past due invoices, amounting to <strike>P</strike> '.$company['CurrentBalance'].'.  &nbsp; Attached is the updated statement of account for your reference.
		
		<br>
		<br>
	
		We understand that delays happen because of unforeseen situations. &nbsp; May we request that your overdue account be settled
		within seven (7) working days upon receipt of this letter.
		
		<br>
		<br>
		We highly appreciate your prompt action on this matter. &nbsp; Please do not hesitate to contact our Credit and Collections at (02) 734 07679/'.comboBoxValue($link,'1_gamit.1rtcusers','IDNo',$userRes['IDNo'],'mobilenumbers').' for any concerns.
		<br>
		<br>
		<br>
		
	
		Sincerely,
		<br>
		<br>
		<br>
		<br>
		'.$userRes['FullName'].'
		<br>
		'.$userRes['Position'].'
		<br>
		<br>
		Noted by: 
		<br>
		<br>
		<br>'.$deptRes['FullName'].'
		<br>
	
		'.$deptRes['Position'].'
		<br>
		<br>
			<i>* Kindly disregard this letter if payment has been made.</i>
	</div>
	
 <p>';

}
 break;

 case 'FinalDue':

 foreach($result as $company){
	   $img=$company['Company'];
	   $msgregards=$company['CompanyName'];
	   
	   $compadd='<font size="2pt">Suite 1018 High Street South Corporate Plaza Tower 1<br>26th Street Bonifacio Global City, Taguig City<br>Tel no. 7751-2213<br><br>www.1rotary.com.ph</font>';
	   $imglink='<img   src="../generalinfo/logo/'.$img.'.png">';

 ?>
<title>Customer FinalDue notice</title>

 <?php 
 echo '<div class="post-container">                
    <div class="post-thumb">
		<a href="" onClick="window.print();return false">'.$imglink.'</a>
	</div>
    <div class="post-content" align="right">
        '.$compadd.'</div>
</div>';
 echo '<div style="margin-left:15px;font-size:12.5pt">';	
echo '<br><br>';
	echo '<div style="margin-left:15px;font-size:12.5pt">'.date("  F d, Y").'<br>
		<br> '.$company['ClientName'].'
		<br> '.$company['CompleteAddress'].'
		<br>Attn:  '.$company['ContactPerson'].' 
		<br><b><h2>SUBJECT: FINAL NOTICE </h2></b>

		<br><br>Dear Valued Customer:
		<br>
		<br>
                
		Every customer is important to us.
		<br>
		<br>
		Please be informed that your account still remains overdue to date, amounting to <strike>P</strike> '.$company['CurrentBalance'].'.
		  &nbsp; Attached is the updated statement of account for your reference.
		<br>
		<br>
		You have been given more than ample time and notice regarding the unsettled outstanding balance. &nbsp; Please settle within seven (7) working days upon receipt of this letter.
		<br><br>
		If no payment is received thereafter, we will forward your account to our legal department and debt collection agency.
		<br>
		<br>
		We appreciate if you handle this matter with urgency.
		<br>
		<br>

		Please do not hesitate to contact our Credit and Collections for any concerns at telephone number (02) 734 07679/'.comboBoxValue($link,'1_gamit.1rtcusers','IDNo',$userRes['IDNo'],'mobilenumbers').'.
		<br>
		
		<br>
		
	
		Sincerely,
		
		<br>
		<br>
		<br>
		'.$userRes['FullName'].'
		<br>
		'.$userRes['Position'].'
		<br>
		<br>
		Noted by: 
		<br>
		<br>
		<br>'.$deptRes['FullName'].'
		<br>
	
		'.$deptRes['Position'].'
		<br>
	<br>
		<i>* Kindly disregard this letter if payment has been made.</i>
			
</div>
 <p>';
}	
 break;
 
 
 case 'PromissoryNote':
		include_once($path.'/acrossyrs/commonfunctions/numtowords.php');
		 foreach($result as $company){
			   $msgregards=$company['CompanyName'];
				$title='Promissory Note';
		 ?>
		 
		
		
		 <?php 
		 echo '<title>'.$title.'</title>';
		 echo '<br><h3 align="center"><a href="" style="text-decoration:none;color:black;" onClick="window.print();return false">'.strtoupper($title).'</a></h3><br>';
		
		 echo '<div style="margin-left:25px;font-size:12.5pt;">';	
		
			echo 'For value received, the undersigned ______________________________, of legal age and with address at ________________________________________________________________________ '.strtoupper($company['ClientName']).' hereby unconditionally promises to pay '.strtoupper($company['CompanyName']).' the full amount of <u>'.convert_number_to_words($company['CurrentBalanceVal'],2,'.','').'</u> PHILIPPINE PESOS (PHP '.$company['CurrentBalance'].') upon the following terms:<br><br>';
				
		echo '1. <b>Mode of payment</b><br><br>';
		echo '<div style="margin-left:17px;font-size:12.5pt">';
		echo '	<b>Full payment:</b> the full amount shall be due and must be paid in cash within one month of signing this Promissory Note.<br><br>';
		
		echo '	<b>Monthly instalment:</b> the full amount shall be due and must be paid in _______ monthly instalments until fully settled. The first instalment must be paid in cash within one month of signing this Promissory Note. An interest of 2% per month shall be charged on the outstanding balance until full settlement.<br><br>';
		
		echo '	<b>Other mode:</b> _________________________ (<i>Indicate payment scheme</i>.)<br><br>';
		
		 echo '<div style="margin-left:18px;font-size:12.5pt;">';
		echo 'In no case shall the terms of the other mode of payment be equal to or less onerous than those for monthly instalment under this clause.<br><br>';
		
		echo '</div></div>';
		echo '<b>2. Penalty clause</b><br><br>';
		
		echo '<div style="margin-left:17px;font-size:12.5pt">';
		echo 'If the Client selects the full payment option but fails to make full payment on the due date, an interest of 2% per month of delay shall be charged.<br><br>If the Client selects monthly instalment or other mode of payment but fails to pay two consecutive instalments or at least three instalments, the full outstanding balance of this Promissory Note, including interest incurred, shall immediately be due without need for demand, plus 2% interest per month of delay.<br><br>In any action to enforce this Promissory Note, the Company shall be entitled to recover all costs and expenses and reasonable attorneys\' fees in addition to any other relief to which it may be entitled.';
		echo '</div><br><br>';
		echo 'The Client hereby affirms and acknowledges having carefully read and understood this Promissory Note and having correctly filled out all the blank spaces when he signed it on _________________________.<br><br><br>';
		
		echo '<div style="text-align:center;">_________________________________________<br><b>NAME OF CLIENT</b><br>CLIENT</div>';
		
		echo '</div><p></p>';
		// exit();
		}	
 break;
 
}
}

noform:
$link=null; $stmt=null;
?>

<script>
function myFunction() {
  window.print();
}

</script>
 
</body>
</html>