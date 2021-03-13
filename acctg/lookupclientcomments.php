<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true; include_once('../switchboard/contents.php');

// check if allowed
$allowed=array(555,556); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit; }
allowed:
// end of check

 

//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="ddf4d7";
        $rcolor[1]="FFFFFF";

$whichqry=$_GET['w'];

switch ($whichqry){
case 'CommentsPerClient':
if (!allowedToOpen(555,'1rtc')) { echo 'No permission'; exit; }  
$title='Comments Per Client';
$fieldname='Client';


include_once('../generalinfo/lists.inc'); 
renderlist('clients');
   ?>
<form style="display: inline"  method="post" action="lookupclientcomments.php?w=CommentsPerClient" enctype="multipart/form-data">
For Client:  <input type="text" name="<?php echo $fieldname; ?>" list="clients"></input>
<input type="submit" name="lookup" value="Lookup"> </form> &nbsp &nbsp &nbsp
<?php
if (!isset($_REQUEST[$fieldname])){
include('../backendphp/layout/clickontabletoedithead.php');
goto noform;
} else {
$showprint=true;
$formdesc= '<i>'.$_POST[$fieldname].'</i>';
include('../backendphp/functions/getnumber.php');
$clientno=getNumber('Client',addslashes($_REQUEST[$fieldname]));
}
?>
<form style="display: inline" method="post" action="prclientcomments.php?w=<?php echo $whichqry; ?>" enctype="multipart/form-data">
   Comment Date<input type=date size=20 name="ContactDate" value="<?php echo date('Y-m-d'); ?>" required=true >
   Add Comment<input type=textarea name="Comment" required=true></input>
   <input type=hidden name="ClientNo" value="<?php echo $clientno; ?>"></input>
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" />
<input type="submit" name="Add" value="Submit"> </form> &nbsp &nbsp &nbsp
<?php
$lefttabletitle='<h3></h3>';
$sqlleft='SELECT date_format(cc.ContactDate,"%Y-%m-%d") as ContactDate, max(case when cc.ARComment<>0 then cc.`Comment` end) as ARComments, max(case when cc.ARComment=0 then cc.`Comment` end) as SalesComments, cc.`TimeStamp`, concat(e.Nickname, " ", e.Surname) as EncodedBy FROM `comments_5commentsonclients` cc left join `1_gamit`.`0idinfo` e on cc.EncodedByNo=e.IDNo WHERE cc.ClientNo='.$clientno.' group by cc.Comment ORDER BY cc.`TimeStamp` desc;';
 
$columnnamesleft=array('ContactDate','SalesComments', 'ARComments', 'EncodedBy','TimeStamp');

$righttabletitle='<h3>Client of these Branches</h3>';
$sqlright='SELECT b.Branch FROM `gen_info_1branchesclientsjxn` as j join `1branches` b on b.BranchNo=j.BranchNo where j.ClientNo='.$clientno ;
 
$columnnamesright=array('Branch');

    include('../backendphp/layout/twotablessidebyside.php');

break;

case 'HoldHistory':
if (!allowedToOpen(556,'1rtc')) { echo 'No permission'; exit; }  
$title='Hold History Per Client';
$fieldname='Client';

include_once('../generalinfo/lists.inc'); 
renderlist('clients');
   ?>
<form style="display: inline"  method="post" action="lookupclientcomments.php?w=<?php echo $whichqry; ?>" enctype="multipart/form-data">
For Client:  <input type="text" name="<?php echo $fieldname; ?>" list="clients"></input>
<input type="submit" name="lookup" value="Lookup"> </form> &nbsp &nbsp &nbsp
<?php
if (!isset($_REQUEST[$fieldname])){
include('../backendphp/layout/clickontabletoedithead.php');
goto noform;
} else {
$showprint=true;
$formdesc= '<i>'.$_POST[$fieldname].'</i>';
include('../backendphp/functions/getnumber.php');
$clientno=getNumber('Client',addslashes($_REQUEST[$fieldname]));
}
if (allowedToOpen(5561,'1rtc')) {
?>
<form style="display: inline" method="post" action="prclientcomments.php?w=HoldHistory" enctype="multipart/form-data">
   Reason<input type=textarea name="Reason"></input>Remarks<input type=textarea name="Remarks"></input>
   <input type=hidden name="ClientNo" value="<?php echo $clientno; ?>"></input>
<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" />
<input type="submit" name="Submit" value="Hold"> or <input type="submit" name="Submit" value="Reset Hold"> or <input type="submit" name="Submit" value="Allow Temporarily"> or <input type="submit" name="Submit" value="Over Limit"> </form> &nbsp &nbsp &nbsp
<?php
}
$lefttabletitle='<h3></h3>';
$sqlleft='SELECT h.*, CASE WHEN Hold=1 THEN "Hold" WHEN Hold=0 THEN "Allowed" WHEN Hold=4 THEN "Over Limit" ELSE "Allowed Temporarily" END AS Hold, date_format(h.`TimeStamp`,"%Y-%m-%d") as EncodeDate, concat(e.Nickname, " ", e.Surname) as EncodedBy FROM `comments_5clientsonhold` h left join `1_gamit`.`0idinfo` e on h.EncodedByNo=e.IDNo WHERE h.ClientNo='.$clientno.' ORDER BY h.`TimeStamp` desc;';
 
$columnnamesleft=array('EncodeDate','Reason', 'Remarks', 'Hold', 'EncodedBy');

$righttabletitle='<h3>Client of these Branches</h3>';
$sqlright='SELECT b.Branch FROM `gen_info_1branchesclientsjxn` as j join `1branches` b on b.BranchNo=j.BranchNo where j.ClientNo='.$clientno;
 
$columnnamesright=array('Branch');

    include('../backendphp/layout/twotablessidebyside.php');

break;

}
noform:
      $link=null; $stmt=null;
?>