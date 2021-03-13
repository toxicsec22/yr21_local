<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6401,'1rtc') and !allowedToOpen(6411,'1rtc')) { echo 'No permission'; exit;}    
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link; 
include_once('../backendphp/functions/editok.php');
 
$txnid=$_REQUEST['CountID'];
$whichqry=$_GET['w'];

switch ($whichqry){
CASE 'InvCount':

$sqlmain='SELECT b.Branch, n.`CompanyName`, REPLACE(b.RegisteredAddress,"<br>",", ") AS RegisteredAddress, c.Date, concat(e.FirstName,\' \', e.Surname) as FullName, c.Remarks FROM audit_2countmain c join `1branches` b on b.BranchNo=c.BranchNo
join `1companies` n on n.CompanyNo=b.CompanyNo
left join `1employees` e on e.IDNo=c.AuditedByNo
where  CountID='.$txnid;
 
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $auditor=$result['FullName'];


$sqllast='SELECT c.Date FROM audit_2countmain c where c.Date<\''.$result['Date'].'\' order by c.Date desc limit 1';
   $stmt=$link->query($sqllast);
    $resultlast=$stmt->fetch();
    
$title=$result['CompanyName'].'<br>'.$result['RegisteredAddress'].'<br>Inventory Report as of '.$result['Date'].'<br>Date of Last Audit:&nbsp &nbsp'.$resultlast['Date'];
$main=$title. '<br>Branch:&nbsp &nbsp<b>'.$result['Branch'].'</b>';

$sql0='Create Temporary Table invaudit(
   ItemCode smallint(6) not null,
   CatNo smallint(6) not null,
   Category varchar(100) not null,
   Description varchar(200) not null,
   Unit varchar(20) not null,
   Per_Records double not null,
   Excess_or_Short double not null,
   Remarks varchar(200) null
   )

Select s.ItemCode, i.CatNo, s.Count, s.Remarks, c.Category, i.ItemDesc as Description, i.Unit, s.ComputerEndGood as Per_Records, truncate((s.Count-s.ComputerEndGood),1) as `Excess_or_Short` from audit_2countsub s join `invty_1items` i on i.ItemCode=s.ItemCode join `invty_1category` c on c.CatNo=i.CatNo join audit_2countmain m on m.CountID=s.CountID where m.CountID='.$txnid.' Order By Category, ItemDesc'; 

$stmt=$link->prepare($sql0);
    $stmt->execute();

$columnsub=array('ItemCode','Description','Unit','Per_Records','Count','Excess_or_Short','Remarks'); 
$subhead='<table><tr>';$subcol='';$sub='';
    foreach ($columnsub as $colsub){ //column headings
            $subcol=$subcol.'<td>'.$colsub.'</td>';
        }
        $subhead=$subhead.$subcol.'</tr>';

    
$sqlcat='Select CatNo, Category from invaudit group by Category';
   $stmt=$link->query($sqlcat);
    $resultcat=$stmt->fetchAll();

foreach ($resultcat as $cat){
   $subcat='<br>'.$cat['Category'].'<br>';
   $sqlsub='Select * from invaudit where CatNo='.$cat['CatNo'];
   $stmt=$link->query($sqlsub);
   $resultsub=$stmt->fetchAll();
   $subitems='';
   foreach($resultsub as $row){
        $subitems=$subitems.'<tr>';
        foreach ($columnsub as $colsub){
            $subitems=$subitems.'<td>'.$row[$colsub].'</td>';
        }
        $subitems=$subitems.'</tr>';
}
$sub=$sub.$subcat.$subhead.$subitems.'</table>';
}


$sqlsum='Select count(s.ItemCode) as LineItems from  `audit_2countsub` s 
join `audit_2countmain` m on m.CountID=s.CountID
Where m.CountID='.$txnid.' Group By m.CountID';
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Line Items Counted:  '. $result['LineItems'];
    $total=$total.'<br><br>Counted and cleared by Auditor:<br><br><br><br><font style="text-decoration: overline;">'.$auditor.'&nbsp&nbsp&nbsp&nbspDate of Audit</font><br><br>';

    $total=$total.'<div style="border:0;"><table width=100% style="border:0;"><tr><td colspan=3>Items were counted in our presence.  We hereby declare all the above are true and correct.  
We acknowledge that we are liable for all unexplained discrepancies, and that these will all be <i>deducted from our salary</i>.</td></tr>'.getBranchPersonnel('audit_2countmain','Date','CountID',$currentyr,$txnid).'</tr></table></div>';
   break;
   
CASE 'Cash':
$title='Audit Cash';
$sqlmain='select m.*, b.Branch, concat(e.FirstName,\' \', e.Surname) as FullName from audit_2countcash m join `1branches` b on b.BranchNo=m.BranchNo join `1employees` as e on e.IDNo=m.EncodedByNo
 where CashCountID='.$txnid;
 
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $auditor=$result['FullName'];

$main='<center>'. $title. '</center><br>Branch:&nbsp &nbsp<b>'.$result['Branch'].'</b>&nbsp &nbsp Date of Count: &nbsp &nbsp' . $result['DateCounted'].'<br><br>';

$bills=array('1000','500','200','100','50','20','10','5','1','025','010','005');
$billtable='<div id="left"><table><tr><td>Denomination</td><td>Quantity</td><td>Amount</td></tr>'; $billsamt=0;
    foreach ($bills as $bill){
        $billtable=$billtable.'<td><font face="arial" size="2">'.$bill.'&nbsp x &nbsp </font></td><td>'.$result[$bill].'</td><td>'.$bill*$result[$bill].'</td></tr>';
        $billsamt=$billsamt+$bill*$result[$bill];
    }
$billtable=$billtable.'<td colspan=2>Total Cash</td><td>'.number_format($billsamt,2).'</td></table>';
$main=$main.$billtable.'<br><div style="border:1px solid black;">---  For Branch Personnel Only  ---<br>Explanation for any discrepancy:<br><br><br><br><br><br><br></div></div><br>';
$sqlsub='Select s.* from audit_2countcashsub s join audit_2countcash m on m.CashCountID=s.CashCountID
where m.CashCountID='.$txnid.' Order By InvandPRCollectNo';
    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
$columnsub=array('InvandPRCollectNo','Amount');    
$sub='<div id="right"><table><td>Invoice/Collection/Encashment</td><td>Amount</td>';$subcol='';
    
    foreach($resultsub as $row){
        $sub=$sub.'<tr>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub=$sub.'</tr>';
}

$sqlsum='Select sum(s.Amount) as TotalInv from  `audit_2countcashsub` s 
join `audit_2countcash` m on m.CashCountID=s.CashCountID
Where m.CashCountID='.$txnid.' Group By m.CashCountID';
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    
$sub=$sub.'</table>Total of Invoices and OR:  '.number_format($result['TotalInv'],2).'<br>Difference:  '.number_format($billsamt-$result['TotalInv'],2).'<br><br></div>';

  
 $total='<br><br>Counted and cleared by Auditor:<br><br><br><br><font style="text-decoration: overline;">'.$auditor.'&nbsp&nbsp&nbsp&nbspDate of Audit</font><br><br>';

 $total=$total.'<div style="border:0;"><table width=100% style="border:0;"><tr><td colspan=3>Cash was counted in our presence.  We hereby declare all the above are true and correct. We acknowledge that we are liable for all unexplained discrepancies, and that these will all be <i>deducted from our salary</i>.</td></tr>'.getBranchPersonnel('audit_2countcash','DateCounted','CashCountID',$currentyr,$txnid).'</tr></table></div>';
      break;
case 'Tools':
$title='List of Tools';
$sqlmain='select m.*, b.Branch, concat(e.FirstName,\' \', e.Surname) as FullName from audit_2toolscountmain m join `1branches` b on b.BranchNo=m.BranchNo join `1employees` as e on e.IDNo=m.AuditedByNo
 where CountID='.$txnid;
 
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $auditor=$result['FullName'];

$main='<center>'. $title. '</center><br>Branch:&nbsp &nbsp<b>'.$result['Branch'].'</b>&nbsp &nbsp Date of Audit: &nbsp &nbsp' . $result['Date'];

$sqlsub='Select s.*,t.ToolDesc, t.Unit from audit_2toolscountsub s join audit_2toolscountmain m on m.CountID=s.CountID join audit_1tools t on t.ToolID=s.ToolID where m.CountID='.$txnid.' Order By t.ToolDesc';

    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
$columnsub=array('ToolDesc','Unit','Count','Remarks');   
$sub='<table><td>Tool</td><td>Unit</td><td>Count</td><td>Remarks</td>';$subcol='';
    
    foreach($resultsub as $row){
        $sub=$sub.'<tr>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub=$sub.'</tr>';
}
$sub=$sub.'</table>';

    $sqlsum='Select count(s.ToolID) as NumberofTools from  `audit_2toolscountsub` s 
join `audit_2toolscountmain` m on m.CountID=s.CountID
Where m.CountID='.$txnid.' Group By m.CountID';
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Number of Tools:  '. $result['NumberofTools'];
    $total=$total.'<div id="right">Checked by Auditor:<br><br><br><br> <div style="text-decoration: overline;">'.$auditor.'&nbsp &nbsp&nbsp &nbsp &nbsp &nbspDate of Audit<br><br></div></div id="right">';

$total=$total.'<div style="border:0;"><table width=100% style="border:0;"><tr><td colspan=3>Tools, equipment, and supplies were checked in our presence.  We hereby declare all the above are true and correct.  
We acknowledge that we are liable for all unexplained discrepancies.</td></tr>'.getBranchPersonnel('audit_2toolscountmain','Date','CountID',$currentyr,$txnid).'</tr></table></div>';
   break;
}


function getBranchPersonnel($main,$date,$id,$currentyr,$txnid){
	global $currentyr;
    $linkinfunction=!isset($linkinfunction)?connect_db(''.$currentyr.'_1rtc',0):$linkinfunction;
   if ($_SESSION['bnum']<>0){
   $sql='Select a.IDNo, a.BranchNo, concat(e.FirstName,\' \', e.Surname) as FullName, p.Position from `attend_2attendance` a join `1employees` as e on e.IDNo=a.IDNo join attend_30currentpositions p on p.IDNo=a.IDNo  join `'.$main.'` c on c.BranchNo=a.BranchNo and c.'.$date.'=a.DateToday where a.BranchNo<>0 and c.'.$id.'='.$txnid;

   } else {
   $sql='Select a.IDNo, a.BranchNo, concat(e.FirstName,\' \', e.Surname) as FullName, p.Position from `attend_2attendance` a join `1employees` as e on e.IDNo=a.IDNo join attend_30currentpositions p on p.IDNo=a.IDNo  join `'.$main.'` c on c.BranchNo=a.BranchNo and c.'.$date.'=a.DateToday where a.BranchNo=0 and p.PositionID in(50,51,52,55) and c.'.$id.'='.$txnid;
   
   }
$stmt=$linkinfunction->query($sql);
    $result=$stmt->fetchAll();
    //$people=$stmt->rowCount();
    $branchpersonnel='<tr>';
    foreach ($result as $person){
      $branchpersonnel=$branchpersonnel.'<td><br><br>'. $person['FullName'].'</td>';
    }
     $branchpersonnel=$branchpersonnel.'</tr><tr>';
    foreach ($result as $person){
      $branchpersonnel=$branchpersonnel.'<td>'. $person['Position'].'&nbsp&nbsp(Employee No:&nbsp'. $person['IDNo'].')</td>';
    }
   return $branchpersonnel;
}
?>
<html>
<head>
<title><?php echo $title; ?></title>

<a href="javascript:window.print()">Print</a>
<style type="text/css">
 
body { 
    margin: 10mm 15mm 10mm 15mm;
    font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
    
}
table,td {
        border:1px solid black;
border-collapse:collapse;
padding: 3px;
font-size: 9pt;
    font-family: Arial, Helvetica, sans-serif;
    font-weight: 300;
}
#wrap {
   width:100%;
   margin:0 auto;
}
#left {
   float:left;
   width:50%;
}
#right {
   float:right;
   width:50%;
}
#boxed {
   border:1px solid black;
}

html, body {height: 100%;}

#wrap {min-height: 100%;}

#main {overflow:auto;
	padding-bottom: 100px;}  /* must be same height as the footer */

#footer {position: relative;
	margin-top: -100px; /* negative value of footer height */
	height: 100px;
	clear:both;} 


</style>
</head>
<body>
<div id="wrap"><?php  echo $main; ?><br>
<?php  echo $sub.'<br>';
?>
<footer>
<?php
echo isset($total)?$total:'';
?>
</footer></div>
<?php
if (!isset($_POST['print'])){
   goto noform;
} else {
$file = fopen($filename, "w+b");
fwrite($file, $sub);
fclose($file);
header("Content-disposition: attachment; filename=".$filename);
header("Content-type: application/pdf");
readfile($file);
}

noform:
     $link=null; $stmt=null;
?>
</body>
</html>