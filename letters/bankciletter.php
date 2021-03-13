<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (allowedToOpen(2201,'1rtc')){
        error_reporting(E_ALL);
	ini_set('display_errors', 1);
}
// check if allowed
$allowed=array(5912);$allow=0;
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
<title>Bank Authorization</title>
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
<body style="margin: 20mm 20mm 20mm 20mm;">

<?php
	// PRINT_R($_SESSION); EXIT();
	echo '<div style="text-align:justify;font-size:12.5pt"><center><b><a style="color:black;text-decoration:none;" href="" onClick="window.print();return false">BANK AUTHORIZATION</a></b></center><br><br><br>
		<div style="font-size:12.5pt">
			Date: '.date('F d, Y').'
			<br>
			<br>
			To whom it may concern:
			<br>
			<br><br>
			I, ________________________________________________________________, hereby authorize '.$_SESSION['*cname'].', with whom I have a pending credit line application, to conduct investigation of bank account number ____________________________ under the account name ____________________________________________________________ with branch __________________________________________________________ of bank __________________________________________________________.
			<br><br>
			Please disclose credit information required by the Company including, but not limited to, date of opening of account, duration of existence, average daily account balance and history of check payment for the past year, and its account holder’s credit standing.
			<br><br>
			I release you and your representatives should any damage arise from the disclosure.
			<br><br><br>
			Thank you in advance for your cooperation.
			<br><br><br><br>
			Very truly yours,
			<br><br>
			<br><br>_________________<br>
			'.str_repeat('&nbsp;',10).'Signature
			<br>Name:</br>
			Position:</br>
			Company:
		</div>
	</div>';

noform:
$link=null; $stmt=null;
?>
 
 
</body>
</html>