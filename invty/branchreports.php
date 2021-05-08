<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true; include_once('../switchboard/contents.php');
 

// check if allowed
$allowed=array(710,711,712,713);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=$allow+1; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
// end of check

$txnidname='TxnID';
$whichqry=$_GET['w'];
$pagetouse='branchreports.php?w='.$whichqry;
$method='GET';
$title='Branch Reports';
include_once('../backendphp/layout/clickontabletoedithead.php');
?><br>
<form method="post" action="<?php echo $pagetouse; ?>" enctype="multipart/form-data">
                Reports From  <input type="date" name="FromDate" value="<?php echo date('Y-m-d',strtotime("-6 days")); ?>"></input> To <input type="date" name="ToDate" value="<?php echo date('Y-m-d'); ?>"></input> 
<input type="submit" name="lookup" value="Lookup Per Branch"> </form>
<br>
<?php
if (!isset($_REQUEST['FromDate'])){
$fromdate=date('Y-m-d',strtotime("-6 days"));
$todate=date('Y-m-d');
} else {
$fromdate=$_REQUEST['FromDate']; $todate=$_REQUEST['ToDate'];
}
$formdesc='Reports FROM <b> '. $fromdate.'</b> TO <b>'.$todate.'</b>';
$datecondition=' and date(rc.TimeStamp) between \''. $fromdate.'\' and \''. $todate.'\'';
echo '<i>Branch Heads & OIC\'s can edit entries within the same day only.</i><br><br>';

switch ($whichqry){
case 'OnClients':
if (!allowedToOpen(711,'1rtc')) { echo 'No permission'; exit;}
if (allowedToOpen(7111,'1rtc')){
   $condition=' and ARComment<>0';
} else {    $condition='';}
if (allowedToOpen(7112,'1rtc')){
   $columnstoedit=array('CommentonReport');
   $columnnames=array('Client','ContactNameandNumber','ReportonClient','EncodedBy','TimeStamp','CommentonReport','CommentBy','CommentTimeStamp');
} else { //branchheads and branchoic
      $columnstoedit=array('ContactNameandNumber','ReportonClient','ARComment');
      $columnnames=array('Client','ContactNameandNumber','ReportonClient','ARComment','EncodedBy','TimeStamp','CommentonReport','CommentBy','CommentTimeStamp');
      $liststoshow=array('clients');
      ?><div style='width: 1000px;border: 1px solid'>
<form action='branchreports.php?w=AddOnClient' method='post' enctype='multipart/form-data'>
   <b>New Report </b><br><input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token']; ?>">
   Client:&nbsp &nbsp &nbsp<input type='text' name='Client' size=15 list='clients' required=true>&nbsp &nbsp
   Contact Person and Contact Number<input type='text' size=15 name='ContactNameandNumber' required=true>&nbsp &nbsp
   Type of Report: <div style='display: inline; border: groove'>&nbsp &nbsp Sales<input type='radio' name='ARComment' value=0 checked=true>&nbsp &nbsp AR(Accounts Receivable)<input type='radio' name='ARComment' value=1>&nbsp &nbsp</div><br><br>
   Report:&nbsp &nbsp<input type='textarea' name='ReportonClient' size=80 autocomplete=false required=true>&nbsp &nbsp
   <input type='submit'  name='Add' value='     Add     '>
</form></div>
<?php
   }
$title='Weekly Report on Clients';

$sql='SELECT rc.*, b.Branch, e.Nickname as EncodedBy, concat(e1.Nickname," ",e1.Surname) as CommentBy, c.ClientName as Client
FROM `comments_30branchreportonclients` rc join `1branches` b on b.BranchNo=rc.BranchNo
join `1clients` c on c.ClientNo=rc.ClientNo
join `1employees` e on e.IDNo=rc.EncodedByNo
left join `1employees` e1 on e1.IDNo=rc.CommentByNo  where rc.BranchNo='.$_SESSION['bnum'].$datecondition.$condition.' order by c.ClientName';
$txnidname='txnid';
$editprocess='branchreports.php?w=EditOnClient&TxnID=';
$editprocesslabel='Change!';
// echo $sql; break;
include_once('../backendphp/layout/displayastableeditcells.php');
    break;

case 'AddOnClient':
   require_once $path.'/acrossyrs/logincodes/confirmtoken.php';include_once('../backendphp/functions/getnumber.php');
   if (!allowedToOpen(7113,'1rtc')) { echo 'No permission'; exit;}
   // to get client no
	$clientno=getNumber('Client',addslashes($_POST['Client']));
        $sqlinsert='INSERT INTO `comments_30branchreportonclients` SET ClientNo='.$clientno.', ';
        $sql='';
        $columnstoadd=array('ContactNameandNumber','ReportonClient','ARComment');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
	 // echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header('Location:branchreports.php?w=OnClients');
   break;
case 'EditOnClient':
    // check if allowed
    $allowed=array(7112,7113);$allow=0;
    foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=$allow+1; } else { $allow=$allow; }}
    if ($allow==0) { echo 'No permission'; exit;}
    // end of check
   
      $sqlinsert='UPDATE `comments_30branchreportonclients` SET ';
        $sql='';
        if (allowedToOpen(7112,'1rtc')){
         $columnstoadd=array('CommentonReport');
         $encodedby=' CommentByNo=\''.$_SESSION['(ak0)'].'\', CommentTimeStamp=Now() ';
         $condition=' and (CommentByNo=\''.$_SESSION['(ak0)'].'\' OR isnull(CommentByNo)) ';
         }
        else { //branchheads and branchoic
        $columnstoadd=array('ContactNameandNumber','ReportonClient','ARComment');
        $encodedby='EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() ';
        $condition=' and date(TimeStamp)=date(Now()) and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
        }
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.$encodedby.' where TxnID='.$_REQUEST['TxnID'].$condition;
        // echo $sql.'<br>'.$sqlinsert; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header('Location:branchreports.php?w=OnClients');
   break;
case 'OnCompetitors':
   if (!allowedToOpen(712,'1rtc')) { echo 'No permission'; exit;}
if (allowedToOpen(7121,'1rtc')){
   $columnstoedit=array('CommentonReport');
} elseif (allowedToOpen(7113,'1rtc')) { //branchheads and branchoic
      $columnstoedit=array('NameofCompetitor','ReportonCompetitor');

      ?><div style='width: 1100px;border: 1px solid'>
<form action='branchreports.php?w=AddOnCompetitors' method='post' enctype='multipart/form-data'>
   <b>New Report </b><br><input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token']; ?>">
   Name of Competitor:&nbsp &nbsp &nbsp<input type='text' name='NameofCompetitor' size=15 required=true>&nbsp &nbsp
   Report:&nbsp &nbsp<input type='textarea' name='ReportonCompetitor' autocomplete=false size=80 required=true>&nbsp &nbsp
   <input type='submit'  name='Add' value='     Add     '>
</form></div>
<?php
} else {
   $columnstoedit=array();
}   
$title='Weekly Report on Competitors';

$columnnames=array('NameofCompetitor','ReportonCompetitor','EncodedBy','TimeStamp','CommentonReport','CommentBy','CommentTimeStamp');

$sql='SELECT rc.*, b.Branch, e.Nickname as EncodedBy, concat(e1.Nickname," ",e1.Surname) as CommentBy
FROM `comments_32branchreportoncompetitors` rc join `1branches` b on b.BranchNo=rc.BranchNo
join `1employees` e on e.IDNo=rc.EncodedByNo
left join `1employees` e1 on e1.IDNo=rc.CommentByNo  where rc.BranchNo='.$_SESSION['bnum'].$datecondition;
$txnidname='txnid';
$editprocess='branchreports.php?w=EditOnCompetitors&TxnID=';
$editprocesslabel='Change!';
// echo $sql; break;
include_once('../backendphp/layout/displayastableeditcells.php');
    break;

case 'AddOnCompetitors':
   require_once $path.'/acrossyrs/logincodes/confirmtoken.php';include_once('../backendphp/functions/getnumber.php');
   if (!allowedToOpen(7113,'1rtc')) { echo 'No permission'; exit;}
        $sqlinsert='INSERT INTO `comments_32branchreportoncompetitors` SET ';
        $sql='';
        $columnstoadd=array('NameofCompetitor','ReportonCompetitor');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
	 // echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header('Location:branchreports.php?w=OnCompetitors');
   break;
case 'EditOnCompetitors':
      $sqlinsert='UPDATE `comments_32branchreportoncompetitors` SET ';
        $sql='';
        if (allowedToOpen(7121,'1rtc')){
         $columnstoadd=array('CommentonReport');
         $encodedby='CommentByNo=\''.$_SESSION['(ak0)'].'\', CommentTimeStamp=Now() ';
         $condition=' and (CommentByNo=\''.$_SESSION['(ak0)'].'\' OR isnull(CommentByNo)) ';
         }
        else { //branchheads and branchoic
            if (!allowedToOpen(7113,'1rtc')) { echo 'No permission'; exit;}
        $columnstoadd=array('NameofCompetitor','ReportonCompetitor');
        $encodedby='EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() ';
        $condition=' and date(TimeStamp)=date(Now()) and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
        }
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.$encodedby.' where TxnID='.$_REQUEST['TxnID'].$condition;
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header('Location:branchreports.php?w=OnCompetitors');
   break;

case 'OnDailySales':
   if (!allowedToOpen(713,'1rtc')) { echo 'No permission'; exit;}
if (allowedToOpen(7121,'1rtc')){
   $columnstoedit=array('CommentonReport');
} elseif (allowedToOpen(7113,'1rtc')) { //branchheads and branchoic
      $columnstoedit=array('Date','DailySales','WeatherandConditions');
      ?><div style='width: 700px;border: 1px solid'>
<form action='branchreports.php?w=AddOnDailySales' method='post' enctype='multipart/form-data'>
   <b>New Report </b><br><input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token']; ?>">
   Date of Sales: &nbsp &nbsp <input type="date" name="Date" value="<?php echo date('Y-m-d'); ?>"></input> &nbsp &nbsp 
   Daily Sales:&nbsp &nbsp<input type='text' name='DailySales' size=15 required=true>&nbsp &nbsp
   <br>Report:&nbsp &nbsp<input type='textarea' name='WeatherandConditions' autocomplete=false size=60 required=true>&nbsp &nbsp
   
   <input type='submit'  name='Add' value='     Add     '><br>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<i>(Sunny, Rainy, Cloudy, Fiesta, Holiday, etc.)</i>
</form></div>
<?php
} else {
   $columnstoedit=array();
}   
$title='Weekly Report on DailySales';

$columnnames=array('SaleDate','DailySales','WeatherandConditions','EncodedBy','TimeStamp','CommentonReport','CommentBy','CommentTimeStamp');

$sql='SELECT rc.*, date_format(Date,\'%b %d  %a\') as SaleDate, b.Branch, e.Nickname as EncodedBy, concat(e1.Nickname," ",e1.Surname) as CommentBy
FROM `comments_31branchreportondailysales` rc join `1branches` b on b.BranchNo=rc.BranchNo
join `1employees` e on e.IDNo=rc.EncodedByNo
left join `1employees` e1 on e1.IDNo=rc.CommentByNo  where rc.BranchNo='.$_SESSION['bnum'].$datecondition;
$txnidname='txnid';
$editprocess='branchreports.php?w=EditOnDailySales&TxnID=';
$editprocesslabel='Change!';
// echo $sql; break;
include_once('../backendphp/layout/displayastableeditcells.php');
$sqlsum='SELECT format(sum(DailySales),0) as Total, format(avg(DailySales),0) as Average FROM `comments_31branchreportondailysales` rc where rc.BranchNo='.$_SESSION['bnum'].$datecondition;
$stmt=$link->query($sqlsum);
$result=$stmt->fetch();
echo '<br><br> Total: '.$result['Total'].'<br> Average: '.$result['Average'];
    break;

case 'AddOnDailySales':
   require_once $path.'/acrossyrs/logincodes/confirmtoken.php';include_once('../backendphp/functions/getnumber.php');
   if (!allowedToOpen(7113,'1rtc')) { echo 'No permission'; exit;}
        $sqlinsert='INSERT INTO `comments_31branchreportondailysales` SET ';
        $sql='';
        $columnstoadd=array('Date','DailySales','WeatherandConditions');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
	 // echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header('Location:branchreports.php?w=OnDailySales');
   break;
case 'EditOnDailySales':
      $sqlinsert='UPDATE `comments_31branchreportondailysales` SET ';
        $sql='';
        if (allowedToOpen(7121,'1rtc')){
         $columnstoadd=array('CommentonReport');
         $encodedby='CommentByNo=\''.$_SESSION['(ak0)'].'\', CommentTimeStamp=Now() ';
         $condition=' and (CommentByNo=\''.$_SESSION['(ak0)'].'\' OR isnull(CommentByNo)) ';
         }
        else { //branchheads and branchoic
        $columnstoadd=array('DailySales','WeatherandConditions');
        $encodedby='EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() ';
        $condition=' and date(TimeStamp)=date(Now()) and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
        }
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.$encodedby.' where TxnID='.$_REQUEST['TxnID'].$condition;
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header('Location:branchreports.php?w=OnDailySales');
   break;
case 'BranchConcerns':
   if (!allowedToOpen(710,'1rtc')) { echo 'No permission'; exit;}
if (allowedToOpen(7101,'1rtc')){
   $columnstoedit=array('CommentonReport'); ?>
       <form action='branchreports.php?w=BranchConcerns' method='post' enctype='multipart/form-data'>
           <input type="submit" name="lookup" value="Lookup All Unread">
           <input type="submit" name="lookup" value="Lookup All Concerns">
       </form><br><br><?php
} elseif (allowedToOpen(7113,'1rtc')) { //branchheads and branchoic
      $columnstoedit=array('BranchConcern');
      ?><div style='width: 1100px;border: 1px solid'>
<form action='branchreports.php?w=AddBranchConcerns' method='post' enctype='multipart/form-data'>
   <b>New Report</b> <i>(Kung may kailangan na gamit o may gustong sabihin)</i> <br><input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token']; ?>">
   Branch Concern:&nbsp &nbsp<input type='textarea' name='BranchConcern' autocomplete=false size=80 required=true>&nbsp &nbsp
   <input type='submit'  name='Add' value='     Add     '>
</form></div>
<?php
} else {
   $columnstoedit=array();
}
$title='Weekly Report on Branch Concerns';
$columnnames=array('BranchConcern','EncodedBy','TimeStamp','CommentonReport','CommentBy','CommentTimeStamp','Read_By_Ops');

if (!isset($_POST['lookup'])) { $condition=' WHERE ReadByOps=0 '; goto skipswitch;} 
switch ($_POST['lookup']){
    case 'Lookup All Unread': $condition=' WHERE ReadByOps=0 '; break;
    case 'Lookup All Concerns': $condition=''; break;
    default: $condition=' WHERE rc.BranchNo='.$_SESSION['bnum'].$datecondition; break;
}

skipswitch:
$sql='SELECT rc.*, b.Branch, e.Nickname as EncodedBy, concat(e1.Nickname," ",e1.Surname) as CommentBy, IF(ReadbyOps=0,"<font color=red>Unread</font>","<font color=green>Read</font>") AS Read_By_Ops
FROM `comments_33branchconcerns` rc join `1branches` b on b.BranchNo=rc.BranchNo
join `1employees` e on e.IDNo=rc.EncodedByNo
left join `1employees` e1 on e1.IDNo=rc.CommentByNo '.$condition;
// echo $sql;
$txnidname='TxnID';
$editprocess='branchreports.php?w=EditBranchConcerns&TxnID='; $editprocesslabel='Change!';
if (allowedToOpen(7102,'1rtc')){ $addlprocess='branchreports.php?w=SetAsRead&TxnID='; $addlprocesslabel='Set_as_Read/Unread';}
// echo $sql; break;
include_once('../backendphp/layout/displayastableeditcells.php');
    break;

case 'SetAsRead':
    if (!allowedToOpen(7102,'1rtc')) { echo 'No permission'; exit;}
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='UPDATE `comments_33branchconcerns` SET ReadbyOps=IF(ReadbyOps=0,1,0) WHERE TxnID='.$_GET['TxnID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;    
    
case 'AddBranchConcerns':
   require_once $path.'/acrossyrs/logincodes/confirmtoken.php';include_once('../backendphp/functions/getnumber.php');
   if (!allowedToOpen(7113,'1rtc')) { echo 'No permission'; exit;}
        $sqlinsert='INSERT INTO `comments_33branchconcerns` SET ';
        $sql='';
        $columnstoadd=array('BranchConcern');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' BranchNo=\''.$_SESSION['bnum'].'\',EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	
	 // echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header('Location:branchreports.php?w=BranchConcerns');
   break;
case 'EditBranchConcerns':
      $sqlinsert='UPDATE `comments_33branchconcerns` SET ';
        $sql='';
        if (allowedToOpen(7101,'1rtc')){
         $columnstoadd=array('CommentonReport');
         $encodedby='CommentByNo=\''.$_SESSION['(ak0)'].'\', CommentTimeStamp=Now() ';
         $condition=' and (CommentByNo=\''.$_SESSION['(ak0)'].'\' OR isnull(CommentByNo)) ';
         } elseif (allowedToOpen(7113,'1rtc')) {//branchheads and branchoic        
        $columnstoadd=array('BranchConcern');
        $encodedby='EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() ';
        $condition=' and date(TimeStamp)=date(Now()) and EncodedByNo=\''.$_SESSION['(ak0)'].'\'';
        } else { $columnstoadd=array();}
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.$encodedby.' where TxnID='.$_REQUEST['TxnID'].$condition;
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header('Location:branchreports.php?w=BranchConcerns');
   break;

}
 