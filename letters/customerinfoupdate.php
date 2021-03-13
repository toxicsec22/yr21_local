<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

// check if allowed
$allowed=array(5911);$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
 
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

include_once "../generalinfo/lists.inc";

?>
<html>
<head>
<title>Customer Information Update</title>
<style>
@media print {
    .pagebreak { page-break-before: always; }
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
</style>
</head>
<body>

<?php //print_r($_POST['ClientName']); exit();
	if(empty($_POST['ClientName'])){
      ?>
      <script type="text/javascript">
        alert('No Client Selected');
        window.location = "viewletters.php";
        </script>
 
<center>
<h2>Customer Information Update Form</h2>
<form method="POST" action="#" enctype="multipart/form-data">
Client <input type="text" name="Client" list="allclients" size=40 autocomplete="off" required="true">
 <input type="submit" name="lookup" value="Lookup"></center>
<?php
goto noform;
}

	else {

	$clientno=getValue($link,'1clients','Left(`ClientName`,20)',addslashes($_POST['ClientName']),'ClientNo');
		$sql='Select ClientName,CONCAT(StreetAddress,", ",Barangay,", ",TownOrCity,", ",Province) AS CompleteAddress,ContactPerson,TelNo1,TelNo2,c.Mobile,EmailAddress,c.TIN,b.CompanyNo,CompanyName,Company FROM 1clients c JOIN gen_info_1branchesclientsjxn bc ON c.ClientNo=bc.ClientNo JOIN 1branches b on b.BranchNo=bc.BranchNo JOIN 1companies co ON co.CompanyNo=b.CompanyNo WHERE c.ClientNo='.$clientno.' LIMIT 1';
	   $stmt=$link->query($sql);
	   $result=$stmt->fetch();
	   
	   $img=$result['Company'];
	   $msgregards=$result['CompanyName'];
	   
	   $compadd='<font size="2pt">Suite 1018 High Street South Corporate Plaza Tower 1<br>26th Street Bonifacio Global City, Taguig City<br>Tel no. 7751-2213<br><br>www.1rotary.com.ph</font>';
	   $imglink='<img  src="../generalinfo/logo/'.$img.'.png">';
}
	
    ?>
    </form>
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
	echo '<div style="margin-left:15px;font-size:12.5pt">Dear Valued Customer:<br><br><br>Thank you so much for our continuous partnership. To serve you better, we are now updating our company records.<br><br>Listed below are your Company’s Current Information:<br>
	<div style="margin-left:100px;font-size:12.5pt">
		<br>Company Name: '.$result['ClientName'].'
		<br>Complete Company Address: '.$result['CompleteAddress'].'
		<br>Contact Person: '.$result['ContactPerson'].'
		<br>Tel 1: '.$result['TelNo1'].'
		<br>Tel 2: '.$result['TelNo2'].'
		<br>Mobile Number: '.$result['Mobile'].'
		<br>Email Address: '.$result['EmailAddress'].'
		<br>TIN: '.$result['TIN'].'
	</div>';
	
	echo '<br>If any information has changed, kindly fill out the attached <b>Customer Information Update.</b><br><br>*For change of Company Name please provide the following updated Documents:<br>
			<div style="margin-left:35px;font-size:12.5pt">
				<ol style="line-height: 150%">
					<li>SEC/DTI Registration</li>
					<li>BIR Certificate of Registration*</li>
					<li>Latest Business/Mayor’s Permit*</li>
					<li>Lease of Contract (if applicable)</li>
					<li>Financial Statement*</li>
					<li>Proof of Billing (Utility Bill – Gas, Electricity, Water, Telephone)*</li>
					<li>Valid Ids of the authorized signatory</li>
				</ol>
			</div>
			*For change of Company Address, please provide Billing Statement.<br><br>Thank you so much!<br><br><br>Best Regards,<br><br>'.$msgregards.'</div>';

echo '<div class="pagebreak">';
echo '<div class="post-container">                
    <div class="post-thumb">
		<a href="customerinfoupdate.php">'.$imglink.'</a>
	</div>
    <div class="post-content" align="right">
        '.$compadd.'</div>
</div>';


			
 echo '<br><div style="margin-left:15px;font-size:12.5pt">
<div align="center"><b>Customer Information Update</b></div><br><div>
<table style="font-size:12.5pt">
<tr><td><b>1.</b></td><td><b>Company Information</b></td></tr>
<tr><td></td><td style="'.$marginintd.'">Company Name: __________________________________________</td></tr>
<tr><td></td><td style="'.$marginintd.'">Company Owner: _________________________________________</td></tr>
<tr><td></td><td style="'.$marginintd.'">Company Contact Numbers: ________________________________</td></tr>
<tr><td></td><td style="'.$marginintd.'">TIN: ____________________________________________________</td></tr>

<tr><td></td><td><br>Complete Billing Address:</td></tr>
<tr><td></td><td style="'.$marginintd.'">Street Address: ___________________________________________</td><tr>
<tr><td></td><td style="'.$marginintd.'">Barangay: _______________________________________________</td><tr>
<tr><td></td><td style="'.$marginintd.'">Town or City: _____________________________________________</td><tr>
<tr><td></td><td style="'.$marginintd.'">Province: ________________________________________________</td><tr>
<tr><td></td><td style="'.$marginintd.'">Zip Code: ________________________________________________</td><tr>

<tr><td><br><b>2.</b></td><td><br><b>Company Contact Persons:</b></td></tr>
<tr><td></td><td style="'.$marginintd.'">Purchaser: _______________________________________________</td></tr>
<tr><td></td><td style="'.$marginintd.'">Phone Numbers: __________________________________________</td></tr>
<tr><td></td><td style="'.$marginintd.'">Mobile Numbers: _________________________________________</td></tr>
<tr><td></td><td style="'.$marginintd.'">Email Address: ___________________________________________</td></tr>

<tr><td></td><td style="'.$marginintd.'"><br>Accounting / Check release Contact Person</td></tr>
<tr><td></td><td style="'.$marginintd.'">Phone Numbers: __________________________________________</td><tr>
<tr><td></td><td style="'.$marginintd.'">Mobile Numbers: _________________________________________</td><tr>
<tr><td></td><td style="'.$marginintd.'">Email Address: ___________________________________________</td><tr>

<tr><td><br><b>3.</b></td><td><br><b>Authorization to Update Customer Information:</b></td></tr>
<tr><td></td><td>By affixing my signature below, I am certifying that the above information is true and accurate to the best of my knowledge. I also certify that I am the authorized person and allowed to execute this customer update form.</td></tr>
<tr>
<td></td>
<td>
	<br><table style="margin-left:15px;font-size:12.5pt">
	<tr><td style="width:950px">______________________<br>'.str_repeat('&nbsp;',5).'Authorized Signature</td><td>___________________<br>'.str_repeat('&nbsp;',16).'Date</td></tr>
	</td></tr>
	<tr><td style="width:950px"><br><br>___________________<br>'.str_repeat('&nbsp;',10).'Print Name</td><td><br><br>___________________<br>'.str_repeat('&nbsp;',14).'Position</td></tr>
	</table>
</td>
</tr>
</table></div>';

noform:
$link=null; $stmt=null;
?>
 
 
</body>
</html>