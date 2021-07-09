<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(599,6001,5992,5994),'1rtc')) { echo 'No permission'; exit; }  
$showbranches=true; include_once('../switchboard/contents.php');
 
include_once('../backendphp/functions/editok.php');
include_once('../backendphp/layout/showencodedbybutton.php');


$method='POST';

$txnid=intval($_REQUEST['TxnID']);


$user=$_SESSION['(ak0)'];
 //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="e0eccf";
        $rcolor[1]="FFFFFF";

$title='Add/Edit Deposits';

if (allowedToOpen(6001,'1rtc')){
   $sqlcondition='';
} else {
$sqlcondition=' and m.Date>\''.$_SESSION['nb4'].'\'';
}
      $sqlmain='SELECT m.*, ca.ShortAcctID as DebitAccount,  e.Nickname as EncodedBy, concat(ca.ShortAcctID, "&nbsp &nbsp &nbsp ",left(ca.Remarks,if((instr(ca.Remarks,";"))=0,20,(instr(ca.Remarks,";")-1)))) as AccountNumber FROM acctg_2depositmain m
join acctg_1chartofaccounts ca on ca.AccountID=m.DebitAccountID
left join `1employees` as e on e.IDNo=m.EncodedByNo
WHERE m.TxnID='.$txnid.$sqlcondition; 

   $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();

if (allowedToOpen(5992,'1rtc')) { //acctg personnel
    
    $columnsub=array('Branch','ClientName','ForChargeInvNo','DepDetails','DepType','CRNo','CheckDraweeBank','CheckNo','CreditAccount','Amount','Forex','PHPAmount');
    $columnsubencash=array('Branch','FromBudgetOf','BudgettedExpense','EncashDetails','ApprovalNo','CompanyName','TIN','DebitAccount','Amount');
    if ($showenc==1) { array_push($columnsub,'EncodedBy','TimeStamp'); array_push($columnsubencash,'EncodedBy','TimeStamp');}
      else { $columnsub=$columnsub; $columnsubencash=$columnsubencash;}
  //  }
    } else { // non-acctg personnel
      $columnsub=array('Branch','ClientName','ForChargeInvNo','DepDetails','DepType','CRNo','CheckDraweeBank','CheckNo','Amount');
      $columnsubencash=array('Branch','FromBudgetOf','BudgettedExpense','EncashDetails','ApprovalNo','Amount');
      if ($showenc==1) { array_push($columnsub,'EncodedBy','TimeStamp'); array_push($columnsubencash,'EncodedBy','TimeStamp');}
      else { $columnsub=$columnsub; $columnsubencash=$columnsubencash;} 
      }
      $columnnamesmain=array('Date','DepositNo','DebitAccount','Cleared','Remarks','Posted');
      if ($showenc==1) { array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo');} else {$columnnamesmain=$columnnamesmain;}
    
    $main='';
    
    if (editOk('acctg_2depositmain',$txnid,$link,'deposit') AND allowedToOpen(5991,'1rtc')){
        $editmain='<td><a href="editspecificsclient.php?edit=2&w=DepMainEdit&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=acctg_2depositMain&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
    } else {
        $editmain='';
        $editsub=false;
    }
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table width="100%"><tr>'.$main.$editmain.'</tr></table>';
   // START DEPOSIT SUB 

    include_once '../generalinfo/unionlists/ECList.php';

    $sqlsub='Select s.*,(Amount*Forex) AS PHPAmount, DepType, CRNo AS CRNo, b.Branch, c.BECSName AS ClientName, ca.ShortAcctID as CreditAccount, e.Nickname as EncodedBy from acctg_2depositsub s join acctg_1chartofaccounts ca on ca.AccountID=s.CreditAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo
    join `1branches` b on b.BranchNo=s.BranchNo
    left join `ECList` c on c.BECSNo=s.ClientNo AND c.BECS=IF(s.`ClientNo`<9999,"E","C") LEFT JOIN acctg_1deptype dt ON dt.DepTypeID=s.Type
    join acctg_2depositmain m on m.TxnID=s.TxnID WHERE m.TxnID='.$txnid.$sqlcondition;
    
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    
    $sub='';$subcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub=$sub.($editsub?'<td><a href="editspecificsclient.php?edit=2&w=DepSubEdit&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&TxnSubId='.$row['TxnSubId'].'&w=acctg_2depositSub&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
        $colorcount++;
    }
    $sub='<table width="100%"><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select sum(Amount) as Total from  `acctg_2depositsub` s join `acctg_2depositmain` m on m.TxnID=s.TxnID
Where m.TxnID='.$txnid;
   
    $stmt=$link->query($sqlsum);
    $resulttotal=$stmt->fetch();
    $totalamt=$resulttotal['Total'];
   // $total='Subtotal:  '.number_format($resulttotal['Total'],2).'<br>';
  // END DEPOSIT SUB
  
  //START DEPOSIT ENCASH
 $sqlsub='Select de.*, b.Branch, ifnull(bl.BudgetDesc,"") as BudgettedExpense, ca.ShortAcctID as DebitAccount, e.Nickname as EncodedBy, CONCAT(t.CompanyName, " ",t.Address) as CompanyName, CONCAT(SUBSTR(t.TIN,1,3),"-",SUBSTR(t.TIN,4,3),"-",SUBSTR(t.TIN,7,3),"-",SUBSTR(t.TIN,10,3)) AS TIN,Entity as FromBudgetOf from acctg_2depencashsub de join acctg_1chartofaccounts ca on ca.AccountID=de.DebitAccountID left join `1employees` as e on de.EncodedByNo=e.IDNo LEFT JOIN `acctg_1budgetentities` be on be.EntityID=de.FromBudgetOf
    join `1branches` b on b.BranchNo=de.BranchNo left join acctg_1branchpreapprovedbudgetlist bl on bl.TypeID=de.TypeID
    LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=de.TIN join acctg_2depositmain m on m.TxnID=de.TxnID WHERE m.TxnID='.$txnid;
    
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    if ($stmt->rowCount()>0){
    $subencash='';$subcol='';
    foreach ($columnsubencash as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $subencash=$subencash.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsubencash as $colsub){
            $subencash=$subencash.'<td>'.$row[$colsub].'</td>';
        }
        
        $subencash=$subencash.($editsub?'<td>'.str_repeat('&nbsp',8).'<a href="editspecificsclient.php?edit=2&w=DepEncashSubEdit&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&TxnSubId='.$row['TxnSubId'].'&m=acctg_2depositMain&w=acctg_2depEncashSub&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
        $colorcount++;
    }
    $subencash='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$subencash.'</tbody></table>';
    $sqlsum='Select sum(Amount) as Encashed from  `acctg_2depencashsub` de join `acctg_2depositmain` m on m.TxnID=de.TxnID
Where m.TxnID='.$txnid;
   
    $stmt=$link->query($sqlsum);
    $resultencash=$stmt->fetch();
    $totalencashamt=$resultencash['Encashed'];
    $totalencash='Encashment Subtotal:  '.number_format($resultencash['Encashed'],2).str_repeat('&nbsp',10);   
    } 
   // END DEPOSIT ENCASH
   $netdep=$totalamt-(isset($totalencashamt)?$totalencashamt:0);
    $grandtotal='<br><br><h3>Net Deposit:  '.number_format($netdep,2).str_repeat('&nbsp',10).'</h3><br><br><a href="addmain.php?w=Deposit">Add New Deposit</a>';;
    $columnnames=array(
                    array('field'=>'BranchNo', 'type'=>'text','size'=>20,'required'=>true,'autofocus'=>true),
                    array('field'=>'DepDetails', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'ForChargeInvNo', 'type'=>'text','size'=>20),
                    array('field'=>'CreditAccountID', 'type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Amount', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='praddsub.php?w=DepSubAdd&TxnID='.$txnid;
    $liststoshow=array('branchnames');
    // info for posting:
    $postvalue='1';
    $table='acctg_2depositmain';
    

    
    $cashdep='Deposit cash sales for <form method=post style="display: inline" action="praddsub.php?w=DepSubAutoAddCash&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">
<input type=date name="CashSales" value="'.date('Y-m-d',strtotime("-1 days")).'"> <input type=submit name=submit value="Deposit">  
</form>';

$sqlundeposited='SELECT  `up`.`CRNo` AS `CRNo`,
        `up`.`PDCBank` AS `PDCBank`,
        `up`.`PDCNo` AS `PDCNo`,
        `up`.`PDCBRSTN` AS `PDCBRSTN`, b.Branch,b1.Branch AS BranchSeries, Left(c.`ClientName`,10) as Client, up.PDCID, SUM(up.Cash+up.PDC) as Amount FROM acctg_undepositedclientpdcs up 
left join `acctg_2depositsub` ds on up.CRNo=ds.CRNo and up.BranchNo=ds.BranchNo and up.ClientNo=ds.ClientNo
join `1branches` b on b.BranchNo=up.BranchNo join `1branches` b1 on b1.BranchNo=up.BranchSeriesNo
join `1clients` c on c.ClientNo=up.ClientNo
where (ds.CRNo is null  AND up.BranchSeriesNo='.$_SESSION['bnum'].' and up.DateofPDC<=Now()) OR up.`CRNo` in (SELECT `CRNo` FROM acctg_undepositedclientpdcs ups
WHERE ((ups.BranchSeriesNo='.$_SESSION['bnum'].') OR (ups.BranchNo='.$_SESSION['bnum'].') OR (ups.BranchNo IN (SELECT MovedBranch FROM `1branches` WHERE MovedBranch<>-1 AND BranchNo='.$_SESSION['bnum'].'))) AND up.DateofPDC<=Now()) GROUP BY PDCID';

$stmt=$link->query($sqlundeposited);
    $result=$stmt->fetchAll();
    $columnsub=array('CRNo','PDCBank','PDCNo','PDCBRSTN','Client','Amount','BranchSeries');
    $lookupdata='';$subcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $lookupdata=$lookupdata.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $lookupdata=$lookupdata.'<td>'.$row[$colsub].'</td>';
        }
        $lookupdata=$lookupdata.($editsub?'<td><a href="praddsub.php?w=DepSubAutoAdd&action_token='.$_SESSION['action_token'].'&PDCID='.$row['PDCID'].'&TxnID='.$txnid.'">Deposit</a></td>':'').'</tr>';
        $colorcount++;
    }
$show=!isset($_POST['show'])?0:$_POST['show'];
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
if (allowedToOpen(5994,'1rtc')){

$showunpaidbutton=1;
$showorhide='<form method="post" action="#">
   <input type=hidden name="show" value='.($show==0?1:0).'>
    <input type="submit" name="submit" value="Show/Hide Unpaid Invoices (static until refreshed at ofc)">
</form>';
$addsub='<form method=post style="display: inline" action="praddsub.php?w=DepSubAdd&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">
Branch <input type=text name="Branch" value="'.$_SESSION['@brn'].'" size=5 list="branchnames">
Client No <input type=text name="ClientNo" value="10000" size=3>
Dep Details <input type=text name="DepDetails" size=5>
Dep Type <input type=text name="Type" list="deptypes" size=8>
Check No <input type=text name="CheckNo" size=5>
CreditAccountID <input type=text name="CreditAccountID" list="accounts" size=10><br><br>
Amount <input type=text name="Amount" size=4>
Forex <input type=text name="Forex" size=4 value=1>
<input type=submit name=submit value="Add">  
</form>
<br><br>';
$addencash='Debit <input type=text name="DebitAccount" size=14 list="accounts" placeholder="Blank if budgetted">';
$addcondi='';
} else {
$showunpaidbutton=0;
$showorhide='';
$show=0;
$addsub='';
$addencash='';
$addcondi='where EntityID='.$_SESSION['bnum'].'';
}
echo comboBox($link,'SELECT EntityID,Entity FROM `acctg_1budgetentities` '.$addcondi.' ORDER BY Entity;','EntityID','Entity','entities');
echo comboBox($link,'SELECT * FROM acctg_1deptype;','DepTypeID','DepType','deptypes');
include_once $path.'/acrossyrs/commonfunctions/renderspeciallist.php';
genericList('SELECT * FROM acctg_1branchpreapprovedbudgetlist order by BudgetDesc',$link,'budgetlist','BudgetDesc','TypeID');

echo '<script>
        function show(){
            var encashdetailsinput = document.getElementById("encashdetailsid");
            var option = document.getElementById("category").value;
            if(option == "Gas")
                  {
                        encashdetailsinput.setAttribute("placeholder","Sales invoice");
                        document.getElementById("gasinputs").style.display="block";
                        document.getElementById("gasinputs1").style.display="block";
                  } else {
					  document.getElementById("gas").style.display="inline";
					  document.getElementById("gasinputs").style.display="none";
                                          document.getElementById("gasinputs1").style.display="none";
				  }

                  if(option == "Courier for Documents"){
                            encashdetailsinput.setAttribute("placeholder","Where it was sent from and what was sent");
                    } else if(option == "Disinfection Supplies"){
                        encashdetailsinput.setAttribute("placeholder","what supplies/items were bought");
                    } else if(option == "Electric Bill" || option == "Water Bill"){
                        encashdetailsinput.setAttribute("placeholder","billing period and due date");
                    } else if(option == "Fare (person only)"){
                        encashdetailsinput.setAttribute("placeholder","Fare used for? (deposit, courier, etc.)");
                    } else if(option == "Boarding House"){
                        encashdetailsinput.setAttribute("placeholder","month to be applied to");
                    } else if(option == "Ice"){
                        encashdetailsinput.setAttribute("placeholder","date of expense");
                    } else if(option == "Supplies"){
                        encashdetailsinput.setAttribute("placeholder","what supplies/items were bought");
                    }
        }
    </script>';

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
// echo comboBox($link,'SELECT CONCAT(Brand," ",Series,": ",PlateNo) AS ModelPlateNo, TxnID FROM `admin_1vehiclelist`','ModelPlateNo','TxnID','vehiclelist');
echo comboBox($link,'SELECT CONCAT("(",Branch,") ",Brand," ",Series,": ",PlateNo) AS ModelPlateNo, vl.TxnID FROM `admin_1vehiclelist` vl JOIN admin_2vehicleassign va ON vl.TxnID=va.VehicleID JOIN 1branches b ON va.BranchNo=b.BranchNo WHERE Status=1 ORDER BY Branch','ModelPlateNo','TxnID','vehiclelist');


$addencash='<form method=post style="display: inline" action="praddsub.php?w=DepEncashAdd&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">
    <div id="gasinputs1" style="display:none;"><br><i><font color=darkblue>Gas/Diesel expenses must have complete information.</font></i> <br/>If this will be edited or deleted, the corresponding information in the Vehicles module must be edited/deleted as well.<br/><br/></div>
Budgetted Expense Type <input type="text" list="budgetlist" id="category" name="TypeID" onchange="show()"/>
                                &nbsp &nbsp
FromBudgetOf <input type=text name="FromBudgetOf"  size=5 list="entities" value="'.$_SESSION['@brn'].'" '.(allowedToOpen(5994,'1rtc')?'':'readonly').'> &nbsp &nbsp
<div id="gas" style="display:inline;">Encash Details <b style="color:red"> * </b> <input type=text id="encashdetailsid" name="EncashDetails" size=20 required autocomplete=off></div>&nbsp &nbsp</br>
TIN (numbers only)<input type=text name="TIN" size=10>
&nbsp &nbsp Approval No<input type=text name="ApprovalNo" size=11 placeholder="Blank if budgetted">'.$addencash.'&nbsp &nbsp
Amount <input type=text name="Amount" size=5>&nbsp &nbsp
<div id="gasinputs" style="display:none;"><br>Date: <input type="date" name="Date" size="10" value="'.date('Y-m-d').'"/>&nbsp &nbsp Vehicle:<font color="red">*</font> <input type="text" size="10" name="VehicleID" list="vehiclelist" value="0" required/>&nbsp &nbsp Km Reading:<font color="red">*</font> <input type="text" size="10" value=0 name="KMperReading" required/>&nbsp &nbsp<div style="display:inline-block;">Qty (Liter):<font color="red">*</font> <input type="text" size="10" name="Liter" value=0 required/></div>&nbsp &nbspPrice per Liter:<font color="red">*</font> <input type="text" size="10" name="PriceperLiter" value=0 required/>&nbsp &nbspInvoiceNo:<font color="red">*</font> <input type="text" size="10" name="InvoiceNo" value=0 required/>&nbsp &nbspRemarks: <input type="text" size="10" name="Remarks"/></div>
<input type=submit name=submit value="Add">  
</form><br/><br/></div><br><br><br>';


$listcondition=' WHERE AccountID IN (SELECT AccountID FROM `acctg_1begbal` WHERE BranchNo='.$_SESSION['bnum'].') ';
include_once ('acctglists.inc');
$addencash=$addencash.renderotherlist('accounts','');



if ($show<>1){
   $lookunpaid='';$subcolunpd='';
         goto nounpaid;
      } else { 
$sqlundpd='SELECT up.Particulars as InvNo, b.Branch, Left(c.`BECSName`,10) as Client, InvBalance as Amount, DebitAccountID, UnpdInvID FROM acctg_unpaidinv up 
left join `acctg_2depositsub` ds on up.Particulars=ds.ForChargeInvNo 
join `1branches` b on b.BranchNo=up.BranchNo
left join `ECList` c on c.BECSNo=up.ClientNo AND c.BECS=IF(up.`ClientNo`<9999,"E","C")
where up.BranchNo='.$_SESSION['bnum'];

$stmt=$link->query($sqlundpd);
    $result=$stmt->fetchAll();
    $columnsub=array('InvNo','Client','Branch','Amount');
    $lookunpaid='';$subcolunpd='';
    foreach ($columnsub as $colsub){
        $subcolunpd=$subcolunpd.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $lookunpaid=$lookunpaid.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $lookunpaid=$lookunpaid.'<td>'.$row[$colsub].'</td>';
        }
      
$lookunpaid=$lookunpaid.($editsub?'<td><a href="praddsub.php?w=DepSubAutoAddInv&action_token='.$_SESSION['action_token'].'&UnpdInvID='.$row['UnpdInvID'].'&TxnID='.$txnid.'">Deposit</a></td>':'').'</tr>';
        $colorcount++;
    }
      }
nounpaid:    
    $lookupdata=$cashdep.'<br><br>Undeposited Collections<br><table><tr>'.$subcol.'<td>Deposit?</td></tr><tbody>'.$lookupdata.'</tbody></table><br><br>'.$showorhide.($showunpaidbutton==0?'':'<table><tr>'.$subcolunpd.'</tr><tbody>'.$lookunpaid.'</tbody></table>');

$sql='Select * from acctg_2depcashcountsub where TxnID='.$txnid;
$stmtcash=$link->query($sql);
$result=$stmtcash->fetch();
$cashcalc='';
$bills=array('1000','500','200','100','50','20','10','5','1','025','010','005');
    $billtable=''; $billsamt=0;
    foreach ($bills as $bill){
        $billqty=((!isset($result[$bill]) or $result[$bill]=='')?0:$result[$bill]);
		$name=$bill.'qty';
        $billtable=$billtable.(($billqty==0)?'':'<td><font face="arial" size="2">'.$bill.'</font></td><td>'.$billqty.'</td></tr>');
        $billsamt=$billsamt+($bill*(in_array($name,array('025qty','010qty','005qty'))?.01:1))*$billqty;
    }
    $cashcalc='Saved Cash Count:<table><tr><td>Denomination</td><td>No. of Bills</td></tr>'.$billtable.'<tr><td>Total Amt of Cash</td><td>'.number_format($billsamt,2).'</td></tr></table><br>';
    
include('acctglayout/depinputsubform.php');
  $link=null; $stmt=null; 
?>

