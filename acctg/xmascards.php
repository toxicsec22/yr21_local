<html>
<head>
<style>
body  
{ 
    margin-top: 5px;
    margin-left: -5px;
    margin-bottom: 2.5mm;
}
table,td {
border:0px solid black;
border-collapse:collapse;
}
a { text-decoration: none;   font-size: 11pt; font-weight: 600; padding-bottom: 10px;}
@media print  
{
    tr,td {
        page-break-inside:avoid; page-break-after:auto; 
    }
    
}
</style>
<title>Xmas Cards</title>
</head>
<body>
<table>
<?php
// error_reporting(E_ALL);
	// ini_set('display_errors', 1);
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

include_once($path.'/acrossyrs/dbinit/userinit.php'); $link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
	
// check if allowed
$allowed=array(602,603,604);$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$cards='';   $rowcount=0; 

if (!isset($_GET['Employees'])){
    
if (!allowedToOpen(602,'1rtc')) { echo 'No permission'; exit;}     
    
$sqlco='Select CompanyNo, Company from `1companies`';
   $stmt=$link->query($sqlco);
   $resultco=$stmt->fetchAll();

foreach ($resultco as $rowco){
$sql='Select x.GroupID, x.ClientNo, c.ClientName,  IF(COUNT(x.ClientNo)>1,"Central",b.Branch) AS Branch, b.CompanyNo from `acctg_xmascards` x join `acctg_1clientsperbranch` c on x.ClientNo=c.ClientNo join `1branches` b on b.BranchNo=c.BranchNo where x.Printed=0 and b.Active=1 and b.CompanyNo='.$rowco['CompanyNo'].' AND b.PseudoBranch<>1 group by x.ClientNo
union
Select x.GroupID, x.ClientNo, c.NameonCheck, "Supplier" as Branch, 0 as Company from `acctg_xmascards` x join `1suppliers` c on x.ClientNo=c.SupplierNo where x.Printed=0 group by x.ClientNo';
$stmt=$link->query($sql); $result=$stmt->fetchAll();

foreach ($result as $row){
    $rowcount++;
    $cards=$cards.($rowcount%2==0?'':'<tr style="height: 150px">')
            . ' <td style="padding-left: 25px; width:360px; color:003399;"><a href="javascript:window.print()"><font color=black>To:</font> &nbsp &nbsp &nbsp'
            . '<font style="style: Harrington;">'.$row['ClientName'].'</font></a><br>'
            . '<center><img src="../generalinfo/logo/'.$rowco['Company'].'Xmas.jpg" style="max-width: 280px;  height: auto; padding-top:8px;">'
            .' </center><p align="right"><font style="font-family: Arial, Helvetica, sans-serif;font-size:7pt">'.strtoupper($row['Branch']).' - '.$row['GroupID'].'</font></p></td>'.($rowcount%2==0?'</tr>':'');
}
}

} else {
    
    if (!allowedToOpen(603,'1rtc')) { echo 'No permission'; exit;}     
    $sql='Select cp.IDNo, CONCAT(`e`.`Nickname`," ",`e`.`SurName`) AS Name, BranchorDept FROM `attend_30currentpositions` cp  JOIN `1employees` e ON e.IDNo=cp.IDNo  ORDER BY BranchorDept,Name;';
$stmt=$link->query($sql); $result=$stmt->fetchAll();
// border:1px solid black;
foreach ($result as $row){
    $rowcount++;
	// echo ($rowcount%3)."<br>";
    $cards=$cards.(round(($rowcount%3),2)==0.33?'<tr style="height: 150px;">':'')
            . ' <td style="padding-left: 30px; width:250px; color:#003399;"><a href="javascript:window.print()"><table style="border:.5px solid black;"><tr><td style="padding:5px;"><font color=black>To:</font> &nbsp &nbsp &nbsp'
            . '<font style="style: Harrington;">'.$row['Name'].'</font></a><br>'
            . '<center><img src="../generalinfo/logo/1RotaryXmas.jpg" style="max-width: 250px;  height: auto; padding-top:8px;">'
            .' </center><p align="right"><font style="font-family: Arial, Helvetica, sans-serif;font-size:7pt">'.strtoupper($row['BranchorDept']).'&nbsp;</p> </font></td></tr></table></td>'.($rowcount%3==0?'</tr><tr><td><br><br></td><tr>':'');
}
    
}

echo $cards; 
 $link=null; $stmt=null;
?>   
</table>
</body>
</html>
