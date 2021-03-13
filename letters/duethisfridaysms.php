<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(552,5511);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$showbranches=TRUE; include_once('../switchboard/contents.php');



$whichqry=isset($_GET['w'])?$_GET['w']:'ARThisWk';

switch ($whichqry){
	
case 'ARThisWk':
    if (!allowedToOpen(5511,'1rtc')) { echo 'No permission'; exit; }
$title='Collection List This Week'; $formdesc='All receivables due until this Friday';
$method='GET';
// if (allowedToOpen(5511,'1rtc')) {
   include('../backendphp/layout/showallbranchesbutton.php');
// }  
$condition=($show==1)?'':' where bal.ClientNo in (Select ClientNo from `acctg_1clientsperbranch` cb where cb.BranchNo='.$_SESSION['bnum'].') ';
$orderby=($show==1)?' order by b.Branch, c.ClientName ':' order by c.ClientName, b.Branch ';
$sql='select cc.CompanyName, c.ClientName,c.Mobile, FORMAT(sum(case when (`Due` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day) and DateDiff(Now(),Due) between 1 and 7) then ARAmount end),2) as `DueThisFri`,FORMAT(sum(case when DateDiff(Now(),Due)>7 then ARAmount end),2) as `DueImmediately`, CONCAT(c.Mobile,"?body=Greetings%20from%20",REPLACE(cc.CompanyName,"&","and"),"!%0D%0A%0D%0AA%20friendly%20reminder%20to%20our%20valued%20client,%20",REPLACE(c.ClientName,"&","and"),":%0D%0A%0D%0AKindly%20settle%20accounts%20on%20%20due%20date:%0D%0A%0D%0A",
     
     
	 
	 
     IF(FORMAT(sum(case when DateDiff(Now(),Due)>7 then ARAmount end),2) IS NOT NULL,CONCAT("P%20",FORMAT(sum(case when DateDiff(Now(),Due)>7 then ARAmount end),2),"%20due immediately%0D%0A"),""),
	 
	 
     IF(FORMAT(sum(case when (`Due` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day) and DateDiff(Now(),Due) between 1 and 7) then ARAmount end),2) IS NOT NULL,CONCAT("P%20",FORMAT(sum(case when (`Due` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day) and DateDiff(Now(),Due) between 1 and 7) then ARAmount end),2)),"P 0.00"),
	 
	 "%20on%20or%20before%20",DATE_FORMAT((DATE(now() + interval (((6 - dayofweek(now())) + 7) % 7) day)), "%b %d"),
     
     
     
	 
	 
	 
	 
	 ".%0D%0A%0D%0AFor%20details,%20visit%20www.1rtcportal.com.%0D%0A%0D%0AThis%20message%20is%20system%20generated.%20Please%20disregard%20if%20payment%20has%20been%20made.") AS SMSBody from acctg_34allarforaging bal JOIN `1clients` c ON (bal.ClientNo =c.ClientNo) join `1branches` b on b.BranchNo=bal.BranchNo JOIN 1companies cc ON b.CompanyNo=cc.CompanyNo 
'.$condition.' group by bal.ClientNo '.$orderby;
// $sql='select bal.ClientNo,cc.CompanyName, c.ClientName,c.Mobile, bal.BranchNo, b.Branch,
// sum(case when (`Due` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day)) then ARAmount end) as `DueThisFriVal`,sum(case when DateDiff(Now(),Due)<15 and DateDiff(Now(),Due)>=0 AND YEAR(Due)='.date('Y').' then ARAmount end) as `DueNow`,
// format(sum(case when (`Due` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day)) then ARAmount end),2) as `DueThisFri`,CONCAT("sms:",c.Mobile,"?body=Greetings%20from%20",cc.CompanyName,"!%0D%0A%0D%0AA%20friendly%20reminder%20to%20our%20valued%20client,%20",c.ClientName,":%0D%0A%0D%0AKindly%20settle%20accounts%20due%20on%20",DATE(now() + interval (((6 - dayofweek(now())) + 7) % 7) day),"%20amounting%20to%20P",format(sum(case when (`Due` <= (now() + interval (((6 - dayofweek(now())) + 7) % 7) day)) then ARAmount end),2),".%0D%0AFor%20details,%20visit%20www.1rtcportal.com.%0D%0A%0D%0AThis%20message%20is%20system%20generated.%20Please%20disregard%20if%20payment%20has%20been%20made.") AS smsbody
// from acctg_34allarforaging bal 
// JOIN `1clients` c ON (bal.ClientNo =c.ClientNo)
// join `1branches` b on b.BranchNo=bal.BranchNo 
// JOIN 1companies cc ON b.CompanyNo=cc.CompanyNo 
// '.$condition.' group by bal.ClientNo having `DueThisFri` is not null '.$orderby;
// echo $sql;break;
// $coltototal='DueThisFriVal';$showgrandtotal=true; 
$width='60%';
    $columnnames=array('ClientName','Mobile','DueImmediately','DueThisFri');
	
	$sendsmsprocess=true;
	$sendsmsprocesslabel='Send SMS';
	// $smsgreetingsfrom='';
	// $smstargetname='ClientName';
	// $smsdate='DueDate';
	// $smsamount='DueThisFri';
	// $smsnumber='Mobile'; 
	$smsbody='SMSBody';
	// $smsbody2='';
	// $smsbody3='';
	// $smsbody4='';
	// $smsbody5='';
	include('../backendphp/layout/displayastablesendsms.php');

break;

}
noform:
      $link=null; $stmt=null;
?>