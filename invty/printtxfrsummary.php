<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(755,'1rtc')) {   echo 'No permission'; exit;} 
$showbranches=false;
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
   
$title='Print Transfer Receipt Summary';
$whichqry=(!isset($_GET['w']))?'Lookup':$_GET['w'];
switch ($whichqry){
   case 'Lookup':
   
   include_once('../backendphp/layout/clickontabletoedithead.php');
   include_once('../generalinfo/lists.inc');
   ?>
   <b>From Branch - To Branch</b>
   <form method='post' action='printtxfrsummary.php?w=Preview'>
      Print for date <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>"> &nbsp; &nbsp; &nbsp;
      <span style="border:1px solid black;padding:5px;">From Specific Branch <input type=text size=10 name="FromBranch" list=branchnames  autocomplete="off">&nbsp; &nbsp; &nbsp;<b>OR</b>&nbsp; &nbsp; &nbsp;
      From All Branches <input type="checkbox" name="AllFromBranches"></span> &nbsp; &nbsp; &nbsp; Delivery to <input type=text size=10 name="ToBranch" list=branchnames  required=1  autocomplete="off"></input> &nbsp &nbsp &nbsp <input type=submit name='submit' value='Print Preview'>
   </form><br><br>
   <b>To Branch - From Branch</b>
   <form method='post' action='printtxfrsummary.php?w=Preview'>
      Print for date <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>"> &nbsp; &nbsp; &nbsp;
      <span style="border:1px solid black;padding:5px;">To Specific Branch <input type=text size=10 name="ToBranch" list=branchnames  autocomplete="off">&nbsp; &nbsp; &nbsp;<b>OR</b>&nbsp; &nbsp; &nbsp;
      To All Branches <input type="checkbox" name="AllToBranches"></span> &nbsp; &nbsp; &nbsp; Delivery from <input type=text size=10 name="FromBranch" list=branchnames  required=1  autocomplete="off"></input> &nbsp &nbsp &nbsp <input type=submit name='submit' value='Print Preview'>
   </form>
   <?php
   renderlist('branchnames');
   $main='';$sub='';
   break;

   case 'Preview':
   include('../backendphp/functions/getnumber.php');
   $date=$_POST['date']; 
   $tobranch=getNumber('Branch',addslashes($_POST['ToBranch']));
   $frombranch=getNumber('Branch',addslashes($_POST['FromBranch']));
   
   $allfrombranches=' AND `BranchNo`='.$frombranch.'';
   if(isset($_POST['AllFromBranches'])){
		$allfrombranches='';
   }
   $alltobranches=' AND `ToBranchNo`='.$tobranch.'';
   if(isset($_POST['AllToBranches'])){
		$alltobranches='';
   }
   
   $condition=' `DateOUT`=\''.$date.'\' '.$allfrombranches.$alltobranches;
   
   
   
   $sqllist='SELECT GROUP_CONCAT(TransferNo SEPARATOR \', \') AS TransferNumbers FROM  `invty_2transfer` WHERE '.$condition;
   
   // echo $sqllist;
   $stmt=$link->query($sqllist); $resultlist=$stmt->fetch();
   
   $sql='SELECT ts.ItemCode, CONCAT(Category, ItemDesc) AS Description, SUM(QtySent) AS TotalQty, Unit, GROUP_CONCAT(SerialNo SEPARATOR \',\') AS SerialNumbers
   FROM `invty_2transfersub` ts JOIN `invty_1items` i ON i.ItemCode=ts.ItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo
   WHERE TxnID IN (SELECT TxnID FROM  `invty_2transfer` WHERE '.$condition.') GROUP BY ts.ItemCode';
   $stmt=$link->query($sql); $result=$stmt->fetchAll();
   
   // echo $sql;
   
   $main='Delivery to <b>'.strtoupper($_POST['Branch']).'</b> on <b>'.$_POST['date'].'</b><br>Summary for Transfer Numbers:  '.$resultlist['TransferNumbers'].'<br>';
   $main=$main.'<br><br>Printed and verified by: ________________(sign above printed name)<br>';
   $sub='<table width="100%">';
   $lineitems=0; $qty=0;
   
   foreach ($result as $row){   // '<pre>&#9;</pre>'
   $sub=$sub.'<tr>
   <td width=12%>'.$row['TotalQty'].str_repeat('&nbsp',3).$row['Unit'].'</td>
   <td width=61%>'.$row['ItemCode'].str_repeat('&nbsp',3).$row['Description'].'</td>
   <td width=8%></td></tr>';
   $lineitems=$lineitems+1; $qty=$qty+$row['TotalQty'];
   }
   $sub=$sub.'</table><center>------   NOTHING FOLLOWS  ------</center>
   <br><br><br>Number of checked pages: ________  &nbsp Checked by: _______________________(sign above printed name)<br>
   <div style="margin-left:100px;display:inline;">Line Items: '.$lineitems.' &nbsp &nbsp &nbsp Total Pcs: '.$qty.'</div>';
   
  // $total='<footer></footer>';
   
      break;
}
?>
<html>
<head>
<title><?php echo $title; ?></title>
<!--<a href="javascript:window.print()">Print</a>-->
<style type="text/css">
 
body { 
    margin: 0mm 8mm 8mm 8mm;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
}
table,td {
        border:0px solid black;
border-collapse:collapse;
padding: 3px;
font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
}
footer {
   position:absolute;
   bottom:0;
   width:90%;
   height: 10mm;
   margin: 0mm 15mm 5mm 8mm;
}
@media print {
    footer {page-break-after: always;}
}
@page {
        margin-top: 80px;
        margin-left: 2px;
        margin-bottom: 40px;
        margin-right: 2px;
        counter-increment: page;

     @bottom-right {
padding-right:20px;
        content: "Page " counter(page);
      }

    }
</style>
</head>
<body>
<?php  echo $main; ?><br>
<?php  echo $sub.'<br>';
echo isset($total)?$total:'';


noform:
         $link=null; $stmt=null;
?>
</body>
</html>
