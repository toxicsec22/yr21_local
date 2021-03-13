<?php

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$IDNo = $_SESSION['(ak0)'];
if (!allowedToOpen(5902,'1rtc')) { echo 'No permission'; exit; }
 
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

include_once "../generalinfo/lists.inc";

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

#container{width:100%;}


}
</style>
</head>
<body>

<?php if(!isset($_POST['lookup'])) {
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	include_once($path.'/acrossyrs/js/includesscripts.php');
	include_once('../switchboard/contents.php');

	echo comboBox($link,'SELECT Left(`ClientName`,20) as `ClientName`, ClientNo FROM `1clients` WHERE Inactive<>1 ORDER BY ClientName','ClientNo','ClientName','allclients') ;
	?>

<center>
<?php 
if(isset($_GET['done'])){
	$ocss='background-color:white;padding:5px;';
	if($_GET['done']==1){
		echo '<h3 style="color:green;'.$ocss.'">Message has been sent.<h3>';
	} else {
		echo '<h3 style="color:red;'.$ocss.'">Message was not sent.<h3>';
	}
}

?>
	<br><br>
<h2>Letters to Clients</h2>
<title>Letters to Clients</title>
</center>

<div style="width:1175px;  border: 2px solid grey; padding: 25px; margin: 25px;">		
<form method="POST" required = "true">

<center>Client <input type="text" name="ClientName"  list="allclients" size=40 autocomplete="off"></center>

<?php

echo '<table><tr>';
	// if(allowedToOpen(5913,'1rtc') or allowedToOpen(5912,'1rtc') or allowedToOpen(586,'1rtc')){
	if(allowedToOpen(array(5913,5912,586),'1rtc')){ 

echo'<td>
<div style="width:115px;  border: 2px solid grey; padding:60px; margin: 25px; ">	

<p style="color:white; font-size: 13px;">New Accts:</p><br>';

 if(allowedToOpen(5913,'1rtc')){
echo '<input type="text" placeholder="Choose Company Here" name="Company" list="companies" size="19">
<input type="submit" name="add_edit" value="Credit Line Application" formaction="viewletters.php">'; 
//added choose company
if(isset($_POST['Company']) and !empty($_POST['Company'])){
	$companyno=companyandbranchValue($link, '1companies', 'CompanyName', $_REQUEST['Company'], 'Company');	
	header('Location:CLA'.$companyno.'.pdf');
	
}
//

echo '</br></br><p style="display:inline; font-size:12px;">New Client </p>'.str_repeat('&nbsp;',4).'<input type="checkbox"  name="recommendation" value="New"></br><p style="display:inline; font-size:9px;">OR</p></br>
<p style="display:inline; font-size:12px;">Existing Client</p> <input type="checkbox"  name="recommendation" value="Old">
<input type="submit" name="add_edit" value="Credit Recommendation" formaction="viewletters.php">'; 

//added credit recommendation
if(isset($_POST['recommendation'])){
	if($_POST['recommendation']=='New'){
		header('Location:CreditRecommendationNew.pdf');
	}else{
		header('Location:CreditRecommendationOld.pdf');
	}		
}
//

echo '<br><br>
<input type="submit" name="add_edit" value="Araw1 Application" formaction="Araw1.pdf">'; }
 if(allowedToOpen(5912,'1rtc')){
 // echo '<br><br> <input type="submit" name="lookup" value="Bank C.I Application" formaction="../generalinfo/bankciletter.php">';
 // echo '<br><br> <input type="submit" name="lookup" value="Bank C.I Application" formaction="BankCIApplication.pdf">';
}
  if(allowedToOpen(586,'1rtc')){ 
 	echo ' <br><br> <input type="submit" name="add_edit" value="Credit Reject Letter" formaction="printsoa.php?w=CreditReject">'; 
}
 echo '</div> </td> ';
}

  // if(allowedToOpen(586,'1rtc') or allowedToOpen(5911,'1rtc')){ 
  if(allowedToOpen(array(586,5911),'1rtc')){ 

echo '<td><div style="width:145px;  border: 2px solid grey; padding:60px; margin: 25px; ">';
 if(allowedToOpen(586,'1rtc')){ 

	echo '<p style="color:white; font-size: 13px;">Approved AR Acct:</p><br>';
		echo '<input type="submit" name="lookup" value="Credit Info Letter - New" formaction="printsoa.php?w=CreditInfoNew">';
		
		echo '<br><br > <input type="submit" name="add_edit" value="Credit Info Letter - Existing" formaction="printsoa.php?w=CreditInfoExist">'; 
		
		echo '<br><br>
		<input type="submit" name="lookup" value="Araw1 Credit Info" formaction="printsoa.php?w=Araw1CreditInfo">';
		
		echo '<br><br ><input type="submit" name="lookup" value="Credit Information" formaction="printsoa.php?w=CreditInformation">';
		
		echo '<br><br ><input type="submit" name="lookup" value="Credit Line Security Agreements" formaction="printsoa.php?w=CreditLineSecurityAgreements">';
	}
if(allowedToOpen(5911,'1rtc')){ 
		echo '<br><br > <input type="submit" name="lookup" value="Customer Information Update" formaction="customerinfoupdate.php"> ';
	}
	echo'</div> </td>';
}
 // if (allowedToOpen(591,'1rtc') or allowedToOpen(5901,'1rtc')){
	 if(allowedToOpen(array(591,5901),'1rtc')){
echo '<td>
<div style="width:115px;  border: 2px solid grey; padding:60px; margin: 25px; ">

		<p style="color:white; font-size: 13px;">Dunning Letters:</p><br>';
 

	 if (allowedToOpen(591,'1rtc')){
	echo '<input type="submit" name="lookup" value="Statement Of Account" formaction="printsoa.php?w=SOA">'; }
   if(allowedToOpen(5901,'1rtc')){
echo '
 	<br><br>  <input type="submit"  name="lookup" value="Overdue Notice" formaction="overduenotice.php?w=Overdue">
<br> <br><input type="submit"  name="lookup" value="Final Due Notice" formaction="overduenotice.php?w=FinalDue">';
}

 echo '</div> </td>';
}
// if(allowedToOpen(5901,'1rtc') or allowedToOpen(590,'1rtc')){
	
if(allowedToOpen(array(5901,590),'1rtc')){
echo '<td>
<div style="width:150px;  border: 2px solid grey; padding:40px; margin: 25px; ">
	<p style="color:white; font-size: 13px;">Other</p><br> ';
  
 if(allowedToOpen(5901,'1rtc')){
echo '
 <input type="submit" name="lookup" value="View Criteria Write-off" formaction="writeoffcriteria.php?w=view"> 
 <br> <br> <input type="submit" name="add_edit" value="Add/Edit Criteria Write-off" formaction="writeoffcriteria.php?w=addedit">';} 
 
 if(allowedToOpen(590,'1rtc')){
echo '<br> <br><input type="submit" name="lookup" value="Sample Letter For Hold Check" formaction="SampleLetterForHoldCheck.pdf"> 
 <br> <br> <input type="submit" name="lookup" value="Final Notice for Hold Check" formaction="printsoa.php?w=RemindHoldCheck"> 
 <br> <br> <input type="submit" name="lookup" value="Promissory Note" formaction="overduenotice.php?w=PromissoryNote">';
 }
 
}
}
?>
</div></td>

</tr>
<tr>
<td>
<div style="width:150px;  border: 2px solid grey; padding:40px; margin: 25px; ">
	<?php
	if(allowedToOpen(5511,'1rtc')){
	echo '
	 <p style="color:white; font-size: 13px;">SMS</p><br> <input type="submit" name="lookup" value="Due This Friday" formaction="duethisfridaysms.php">';
	 }
	?>
	</div>
</td>
<td>
</td>
<td>
</td>
<td>
</td>
</tr>
</table>
</body>
</html> 
