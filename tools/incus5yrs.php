<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(829,'1rtc')) {   echo 'No permission'; exit;}  
include_once('../switchboard/contents.php'); 
 
include_once "../generalinfo/lists.inc";

// $link=connect_db("hist_incus",0);
$fiveyrsago=$currentyr-5; $yr=$fiveyrsago;

$which=$_REQUEST['w'];
switch ($which){
    case '5YrIncus':
    $showbranches=true;  $sql=''; $columnnames=array('ClientName');
    $title='Historical Incus ('.$fiveyrsago.'-'.$lastyr.') Per Branch';
    while ($yr<$currentyr) {
        $sql.='FORMAT(`'.$yr.'`,0) AS `'.$yr.'`, '; $columnnames[]=$yr;
        $yr++;
    }
    $sql='SELECT '.$sql.' cb.ClientName, Branch FROM hist_incus.incusyrtotals h
join `acctg_1clientsperbranch` cb on cb.ClientNo=h.ClientNo
join `1branches` b on b.BranchNo=cb.BranchNo where b.BranchNo='.$_SESSION['bnum'];
// if($_SESSION['(ak0)']){echo $sql.'<br>current '.$currentyr.'<br>last '.$lastyr.'<br>5 yrs '.$fiveyrsago; }
include('../backendphp/layout/displayastable.php');
        break;
    case 'IncusDetails':
    $showbranches=false;
    $fieldname='ClientName';
   $lookupprocess='incus5yrs.php?w='.$which;
if (!isset($_POST[$fieldname])){
 ?> <html><head><title>Historical Data</title></head><br><br><br><center>
   <form method="POST" action="<?php echo $lookupprocess ?>" enctype="multipart/form-data">
Client Name<input type="text" name="<?php echo $fieldname ?>" list="allclients" size=40 autocomplete="off" required="true">
<input type="submit" name="lookup" value="Lookup"></center> 
<?php
renderlist('allclients');
    goto noform;
    ?>
    </form></html>
 <?php
}
 $clientno=getValue($link,'1clients','Left(`ClientName`,20)',addslashes($_POST[$fieldname]),'ClientNo');
 $title='Historical Data of '.addslashes($_POST[$fieldname]);
 $columnnames=array('Date','Category','ItemCode','ItemDesc','Qty','Unit','UnitPrice','Branch');
 $sql='SELECT h.*, Branch, Category, ItemDesc, Unit FROM hist_incus.saledetails h 
join `1branches` b on b.BranchNo=h.BranchNo 
join `invty_1items` i on i.ItemCode=h.ItemCode
join `invty_1category` cat on cat.CatNo=i.CatNo
where h.ClientNo='.$clientno.' AND YEAR(Date)>='.$fiveyrsago.' order by Date';
include('../backendphp/layout/displayastable.php');
        break;
}
noform:
      $link=null; $stmt=null;
?>