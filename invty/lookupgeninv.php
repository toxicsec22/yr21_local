<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

include_once "../generalinfo/lists.inc";


// check if allowed
$allowed=array(717,718,719,720,721,722,723,724,725,726,727,728,729,730,731,732,733,734,735,7231,7271,7301,7302,7303,7304,7305,7331,7601,7132,7602);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check

$whichqry=$_GET['w'];
if(in_array($whichqry,array('AllDefective','AcceptMRR','STLScoreDetails','SalesAcrossBranches','YrScores','YrTargets','YrTargetsEditSpecs','PersonalTargets'))) { $showbranches=false;} else {$showbranches=true;}
include_once('../switchboard/contents.php');



$whichqry=$_GET['w'];
if(in_array($whichqry,array('FreonSold','FreonSoldPerYear'))){
	include_once('../backendphp/layout/linkstyle.php');
	echo '<br><a id="link" href="lookupgeninv.php?w=FreonSold">Freon Sold Per Tank</a>
        <a id="link" href="lookupgeninv.php?w=FreonSoldPerYear">Freon Sold Per Year</a><br>';
}
switch ($whichqry){
case 'FreonSold':
    if (!allowedToOpen(726,'1rtc')){   echo 'No permission'; exit;}

   $title='Freon Sold Per Tank';
   $columnnames=array('SerialNo','ItemCode', 'Description','Sold','Unit');
   
   $sql='SELECT s.SerialNo, i.ItemCode, Sum(s.Qty) AS Sold, i.ItemDesc as Description, i.Unit FROM invty_1items i JOIN invty_2salesub s ON i.ItemCode=s.ItemCode JOIN invty_2sale ON s.TxnId=invty_2sale.TxnId WHERE i.CatNo=90 and  s.SerialNo Is Not Null and BranchNo='.$_SESSION['bnum'].' GROUP BY s.SerialNo, i.ItemCode ORDER BY s.SerialNo'; 
    include('../backendphp/layout/displayastable.php');
   break;
   
case 'FreonSoldPerYear':
    if (!allowedToOpen(726,'1rtc')){   echo 'No permission'; exit;}
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
   $yrfrom=2014; $yrto=date('Y'); $yropt='';
		while($yrto>=$yrfrom){
			$yropt.='<option value="'.$yrto.'">'.$yrto.'</option>';
			$yrto--;
		}
		$branchno=$_SESSION['bnum'];
		$year=((isset($_POST['Yr']))?$_POST['Yr']:date('Y'));
		
		$sql='SELECT `Date`,s.ClientNo,ClientName,`SaleNo` AS InvNo,Qty AS Sold,(SELECT Unit FROM '.$year.'_1rtc.invty_1items WHERE ItemCode = ss.ItemCode) AS Unit,CONCAT("FREON",", ",(SELECT ItemDesc FROM '.$year.'_1rtc.invty_1items WHERE ItemCode = ss.ItemCode)) AS ItemSold from '.$year.'_1rtc.invty_2sale s JOIN '.$year.'_1rtc.invty_2salesub ss ON s.TxnID=ss.TxnID JOIN '.$year.'_1rtc.1clients c ON s.ClientNO=c.ClientNo WHERE s.txntype IN (1,2,5) AND ItemCode IN (select ItemCode from '.$year.'_1rtc.invty_1items WHERE CatNo=90) AND BranchNo='.$branchno.' ORDER BY `Date` ASC;';
		$columnnameslist=array('Date','InvNo','ClientNo','ClientName','ItemSold','Sold','Unit');
		
		$branch=comboBoxValue($link,'`1branches`','Branch',$_SESSION['bnum'],'BranchNo');
		
		$title='Freon Sold Per Year'; 
         $formdesc='</i><br><form action="lookupgeninv.php?w=FreonSoldPerYear" method="POST">Year: <select name="Yr">'.$yropt.'</select> <input type="submit" value="Select"></form><br><h3>Branch: '.$_SESSION['@brn'].'<br>Year: '.$year.'</h3><i>';
        $txnid='TxnID';
		$columnnames=$columnnameslist;       
         
		include('../backendphp/layout/displayastable.php'); 
   break;

case 'EndInvPerBranch':
if (!allowedToOpen(724,'1rtc')){   echo 'No permission'; exit;}
$title='End Inv Per Branch'; 
$formdesc='<form method=post action="lookupgeninv.php?w=EndInvPerBranch">
Type of report:  All Items <input type=radio size=2 name="allorzero" value=3> &nbsp &nbsp With Invty <input type=radio size=2 name="allorzero" value=1> &nbsp &nbsp Zero Invty <input type=radio size=2 name="allorzero" value=0> &nbsp &nbsp Negative Invty <input type=radio size=2 name="allorzero" value=-1> &nbsp &nbsp Defective Invty <input type=radio size=2 name="allorzero" value=2>&nbsp &nbsp 
'.((allowedToOpen(7241,'1rtc'))?'As of Date: <input type=checkbox size=2 name="asofdate" value=1 '.(isset($_POST['asofdate'])?'checked':'').'> <input type="date" name="datefilter" value="'.(isset($_POST['datefilter'])?$_POST['datefilter']:date('Y-m-d')).'">':'').' <input type=submit name="submit" value="Lookup">
</form>';
if (!isset($_REQUEST['allorzero'])) { include('../backendphp/layout/clickontabletoedithead.php'); goto noform;}

    $which=$_REQUEST['allorzero'];
    
      switch ($which){
         case 1: //with invty
            $title='End Inv Per Branch';
            $condition=' HAVING EndInvToday>0 ';
            break;
         case 0: //zero invty
            $title='Items with Zero Inv Per Branch';
            $condition=' HAVING EndInvToday=0 ';
            break;
         case -1: //negative invty
            $title='Negative End Inv Per Branch';
            $condition=' HAVING EndInvToday<0 ';
            break;
         case 2: //defective invty
            $title='Defective End Inv Per Branch';
            $condition=' HAVING Defective<>0 ';
            break;
         default://all items
            $title='End Inv Per Branch';
            $condition='';
            break;
      }
      include('maketables/getasofmonth.php');
      // include('maketables/createendinvperbranch.php'); 
	  include('maketables/createitemact.php');
	  
	  if(isset($_POST['asofdate'])){
		$datefilter='WHERE Date<="'.$_POST['datefilter'].'"';
	  } else {
		 $datefilter='';
	  }
        $sql0='Create Temporary Table endinv(
        BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
CatNo	smallint(6)	NOT NULL,
Category	varchar(100)	NOT NULL,
Description	varchar(100)	NOT NULL,
Unit		varchar(10)	NOT NULL,
GoodItem	double	NOT NULL,
Defective	double	NOT NULL,
EndInvToday	double	NOT NULL)
        SELECT BranchNo,a.ItemCode,i.CatNo,c.Category,i.ItemDesc as Description,i.Unit, SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,SUM(Qty) as EndInvToday FROM ItemAct a JOIN invty_1items i ON i.ItemCode=a.ItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo '.$datefilter.' GROUP BY a.ItemCode '.$condition;
        $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    $sql1='SELECT CatNo,Category from `endinv` group by CatNo';
    $sql2='SELECT * FROM endinv ';
    
    $groupby='CatNo';
    $orderby=' ORDER By Category';
    $columnnames1=array('Category');
    $columnnames2=array('ItemCode','Description','GoodItem','Defective','EndInvToday','Unit');
    
    include('../backendphp/layout/displayastablewithsub.php');
    $sql0='DROP TEMPORARY TABLE IF EXISTS endinvperbranch, endinv'; $stmt0=$link->prepare($sql0); $stmt0->execute(); 
   break;
case 'AllDefective':
if (!allowedToOpen(718,'1rtc')){   echo 'No permission'; exit;}    

$title='Defective Inventory'; 

    $sql0='CREATE TEMPORARY TABLE endinvperbranch (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Defective	double	NOT NULL,
EndInvToday	double	NOT NULL)
SELECT BranchNo,a.ItemCode, SUM(Qty) as Defective,Sum(Qty) as EndInvToday FROM invty_20uniallposted as a where Date is not null and Date<=Now() AND Defective<>0 group by BranchNo, a.ItemCode' ;    

    $stmt0=$link->prepare($sql0); $stmt0->execute();   

    $sql1='SELECT b.BranchNo, Branch from `1branches`  b JOIN endinvperbranch e ON e.BranchNo=b.BranchNo GROUP BY BranchNo ORDER BY Branch';
    $sql2='SELECT ei.*, Category, i.ItemDesc AS Description, Unit FROM endinvperbranch ei JOIN invty_1items i ON i.ItemCode=ei.ItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo ';
    $secondcondition=' AND Defective<>0 ';
    $groupby='BranchNo';
    $orderby=' ORDER By Category';
    $columnnames1=array('Branch');
    $columnnames2=array('Category','ItemCode','Description','Defective','EndInvToday','Unit');
    $newwindowprocess='lookupperitemdetails.php?w=Item_Activity_Defective&ItemCode=';
    $newwindowprocesslabel='Lookup Activity'; $txnid='ItemCode'; $txnid2='BranchNo'; $newwindowwidth='800'; $newwindowheight='600';
    include('../backendphp/layout/displayastablewithsub.php');
   break;

case 'EndInvPerBranchWithSort':
if (!allowedToOpen(725,'1rtc')){   echo 'No permission'; exit;}
$title='End Inv Per Branch'; 
$formdesc='<form method=get action="lookupgeninv.php?w=EndInvPerBranchWithSort">
Type of report:  All Items <input type=radio size=2 name="allorzero" value=3> &nbsp &nbsp With Invty <input type=radio size=2 name="allorzero" value=1 checked=true> &nbsp &nbsp Zero Invty <input type=radio size=2 name="allorzero" value=0> &nbsp &nbsp Negative Invty <input type=radio size=2 name="allorzero" value=-1> &nbsp &nbsp Defective Invty <input type=radio size=2 name="allorzero" value=2>&nbsp &nbsp <input type=hidden name="w" value="EndInvPerBranchWithSort">
<input type=submit name="submit" value="Lookup">
</form>';
if (!isset($_REQUEST['allorzero'])) { include('../backendphp/layout/clickontabletoedithead.php'); goto noform;}

    $sql0='CREATE TEMPORARY TABLE endinvperbranch (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
CatNo	smallint(6)	NOT NULL,
Category	varchar(100)	NOT NULL,
Description	varchar(100)	NOT NULL,
Unit		varchar(10)	NOT NULL,
GoodItem	double	NOT NULL,
Defective	double	NOT NULL,
EndInvToday	double	NOT NULL)
SELECT BranchNo,a.ItemCode,i.CatNo,c.Category,i.ItemDesc as Description,i.Unit, SUM(CASE WHEN Defective<>1 THEN Qty END) as GoodItem, SUM(CASE WHEN Defective=1 THEN Qty END) as Defective,Sum(Qty) as EndInvToday FROM invty_20uniallposted as a join invty_1items i on i.ItemCode=a.ItemCode join `invty_1category` c on c.CatNo=i.CatNo where Date is not null and Date<=Now() and BranchNo='.$_SESSION['bnum'].' group by a.ItemCode, a.BranchNo' ;    

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();   
    
    //$which=isset($_REQUEST['allorzero'])?$_REQUEST['allorzero']:3;
    $which=$_REQUEST['allorzero'];
    
      switch ($which){
         case 1: //with invty
            $title='End Inv Per Branch';
            $condition=' where EndInvToday>0 ';
            break;
         case 0: //zero invty
            $title='Items with Zero Inv Per Branch';
            $condition=' where EndInvToday=0 ';
            break;
         case -1: //negative invty
            $title='Negative End Inv Per Branch';
            $condition=' where EndInvToday<0 ';
            break;
         case 2: //defective invty
            $title='Defective End Inv Per Branch';
            $condition=' where Defective=1 ';
            break;
         default://all items
            $title='End Inv Per Branch';
            $condition='';
            break;
      }
    
        $sql0='Create Temporary Table endinv(
        BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
CatNo	smallint(6)	NOT NULL,
Category	varchar(100)	NOT NULL,
Description	varchar(100)	NOT NULL,
Unit		varchar(10)	NOT NULL,
GoodItem	double	NOT NULL,
Defective	double	NOT NULL,
EndInvToday	double	NOT NULL)
        SELECT * FROM endinvperbranch '.$condition;
        $stmt0=$link->prepare($sql0); $stmt0->execute();
        
    $sql='SELECT Category,e.*, lc.UnitCost, lc.UnitCost*(e.EndInvToday) AS TotalValue, FORMAT(lc.UnitCost,2) as Unit_Cost, FORMAT(lc.UnitCost*(e.EndInvToday),2) AS Total_Value FROM endinv e JOIN `invty_52latestcost` lc ON e.ItemCode=lc.ItemCode';
    
    $columnnames=array('Category','ItemCode','Description','GoodItem','Defective','EndInvToday','Unit');
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Category,ItemCode'); $columnsub=$columnnames; $columnsub[]='UnitCost'; $columnsub[]='TotalValue';
    if (allowedToOpen(6921,'1rtc')){ $columnnames[]='Unit_Cost'; $columnnames[]='Total_Value';}
    $sql=$sql.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');    
    include('../backendphp/layout/displayastable.php'); 
   
   break;   
   
case 'SupplierUndelivered':
    if (!allowedToOpen(729,'1rtc')){   echo 'No permission'; exit;}
    
        ?>
<form>
<input name="print" TYPE="button" onClick="window.print()" value="Print!">
</form><?php
    
    $title='Undelivered Orders by Suppliers';

?>
<form method='post' action='lookupgeninv.php?w=SupplierUndelivered'>
<input name="wh" TYPE="radio" value='40'>Central
<input name="wh" TYPE="radio" value='27'>CDO Warehouse
<input name="wh" TYPE="radio" value='65'>Luzon Warehouse
<input type=submit name=submit value=Submit>
</form>
<?php
if (!isset($_POST['wh'])){
    goto noform;
} else {
$wh=$_POST['wh'];
}
    $sql1='SELECT s.SupplierName, ud.PONo, concat(\'As of \',Now()) as AsOf from invty_41supplierundelivered ud join `1suppliers` s on s.SupplierNo=ud.SupplierNo where BranchNo='.$wh.' group by PONo order by SupplierName';
    $sql2='Select ud.*, c.Category,i.ItemDesc as Description,i.Unit from invty_41supplierundelivered ud join `invty_1items` i on i.ItemCode=ud.ItemCode join `invty_1category` c on c.CatNo=i.CatNo ';
    $showbranches=false;
    $groupby='PONo';
    $orderby=' ORDER By PONo';
    $columnnames1=array('SupplierName','PONo', 'AsOf');
    $columnnames2=array('ItemCode','Category','Description','Ordered','Received','SupplierUndelivered','Unit');
  
    include('../backendphp/layout/displayastablewithsub.php');
    break;

case 'UndeliveredTxfr':
//added branch view
 if (allowedToOpen(7602,'1rtc')){
$sql0='Create Temporary Table undeliveredrequests (
        RequestNo varchar(15) not null,
        RequestDate date not null,
        FromBranch varchar(20) not null,
        ToBranch varchar(20) not null,
        ItemCode smallint(6) not null,
        RequestQty double not null,
        BalancetoSend double not null,
		BalanceToReceive double not null
    )
    SELECT RequestNo, Date as RequestDate, b1.Branch as FromBranch, b2.Branch as ToBranch, ItemCode, `RequestQty`, SendBal as BalancetoSend,RcvBal as BalanceToReceive FROM invty_44undeliveredreq ud 
	join `1branches` b1 on b1.BranchNo=ud.SupplierBranchNo
	join `1branches` b2 on b2.BranchNo=ud.BranchNo where (ud.SupplierBranchNo='.$_SESSION['bnum'].' and ud.SendBal<>0) or (RcvBal<>0 and ud.BranchNo='.$_SESSION['bnum'] . ')';
$stmt=$link->prepare($sql0);
$stmt->execute();
	
	$title='Undelivered Items per Request';
	$sql1='SELECT RequestNo,concat("RequestNo: ",RequestNo) as RequestNoValue, concat("RequestDate: ",RequestDate) as RequestDate,concat("SupplierBranch: ",FromBranch) as FromBranch, concat("RequestingBranch: ",ToBranch) as ToBranch,ToBranch as ToBranchValue FROM undeliveredrequests group by RequestNo order by ToBranchValue, RequestNo;';
    $sql2='SELECT RequestNo, ud.ItemCode, Category, `RequestQty`, BalancetoSend,BalanceToReceive,i.ItemDesc as Description,i.Unit FROM undeliveredrequests ud join `invty_1items` i on i.ItemCode=ud.ItemCode join `invty_1category` c on c.CatNo=i.CatNo ';
    $groupby='RequestNo';
    $orderby=' ORDER By RequestNo';
    $columnnames1=array('FromBranch','RequestNoValue','RequestDate', 'ToBranch');
    $columnnames2=array('ItemCode','Category','Description','RequestQty','BalancetoSend','BalanceToReceive','Unit');
	
	include('../backendphp/layout/displayastablewithsub.php');
	 exit();
 }
// end branch view
   $g=(!isset($_POST['g']))?2:$_POST['g'];  
    if (allowedToOpen(7601,'1rtc')){
        ?>
<form>
<input name="print" TYPE="button" onClick="window.print()" value="Print!">
</form>
<form method='post' action='lookupgeninv.php?w=UndeliveredTxfr'>
Group By Branch<input name="g" TYPE="radio" value='1'>
Group By RequestNo<input name="g" TYPE="radio" value='2'>
<input type=submit name=submit value=Submit>
</form><?php
    }
	switch($g){
        case 1:
    $title='Undelivered Requests for Interbranch Transfers';
    $sql0='Create Temporary Table undeliveredrequests (
        RequestNo varchar(15) not null,
        RequestDate date not null,
        FromBranch varchar(20) not null,
        ToBranch varchar(20) not null,
        ItemCode smallint(6) not null,
        RequestQty double not null,
        BalancetoSend double not null
    )
    SELECT RequestNo, Date as RequestDate, b1.Branch as FromBranch, b2.Branch as ToBranch, ItemCode, `RequestQty`, SendBal as BalancetoSend FROM invty_44undeliveredreq ud 
join `1branches` b1 on b1.BranchNo=ud.SupplierBranchNo
join `1branches` b2 on b2.BranchNo=ud.BranchNo where ud.SupplierBranchNo='.$_SESSION['bnum'].' and ud.SendBal<>0';
    $stmt=$link->prepare($sql0);
    $stmt->execute();
    
    $sql1='SELECT RequestNo, RequestDate,FromBranch, ToBranch FROM undeliveredrequests group by RequestNo order by ToBranch, RequestNo;';
    $sql2='SELECT RequestNo, ud.ItemCode, Category, `RequestQty`, BalancetoSend,i.ItemDesc as Description,i.Unit FROM undeliveredrequests ud join `invty_1items` i on i.ItemCode=ud.ItemCode join `invty_1category` c on c.CatNo=i.CatNo ';
    $groupby='FromBranch';
    $orderby=' ORDER By RequestNo';
    $columnnames1=array('FromBranch','RequestDate', 'ToBranch');
    $columnnames2=array('RequestNo','ItemCode','Category','Description','RequestQty','BalancetoSend','Unit');
    
    include('../backendphp/layout/displayastablewithsub.php');
	break;
	
	case 2:
    $title='Undelivered Requests for Interbranch Transfers';
    $sql0='Create Temporary Table undeliveredrequests (
		ReqTxnID int(11) not null,
        RequestNo varchar(15) not null,
        RequestDate date not null,
        FromBranch varchar(20) not null,
        ToBranch varchar(20) not null,
        ItemCode smallint(6) not null,
        RequestQty double not null,
        BalancetoSend double not null
    )
    SELECT ReqTxnID,RequestNo, Date as RequestDate, b1.Branch as FromBranch, b2.Branch as ToBranch, ItemCode, `RequestQty`, SendBal as BalancetoSend FROM invty_44undeliveredreq ud 
join `1branches` b1 on b1.BranchNo=ud.SupplierBranchNo
join `1branches` b2 on b2.BranchNo=ud.BranchNo where ud.SupplierBranchNo='.$_SESSION['bnum'].' and ud.SendBal<>0';
    $stmt=$link->prepare($sql0);
    $stmt->execute();
    
    $sql1='SELECT concat(\'<a href="addedittxfr.php?w=Request&TxnID=\',ReqTxnID,\'">Lookup</a>\') as Lookup,RequestNo, RequestDate,FromBranch, ToBranch FROM undeliveredrequests group by RequestNo order by ToBranch, RequestNo;';
    $sql2='SELECT RequestNo, ud.ItemCode, Category, `RequestQty`, BalancetoSend,i.ItemDesc as Description,i.Unit FROM undeliveredrequests ud join `invty_1items` i on i.ItemCode=ud.ItemCode join `invty_1category` c on c.CatNo=i.CatNo ';
    $groupby='RequestNo';
    $orderby=' ORDER By RequestNo';
    $columnnames1=array('FromBranch','RequestNo','RequestDate', 'ToBranch','Lookup');
    $columnnames2=array('ItemCode','Category','Description','RequestQty','BalancetoSend','Unit');
    
    include('../backendphp/layout/displayastablewithsub.php');
	break;
	}
    break;

case 'AcceptMRR':
    if (!allowedToOpen(717,'1rtc')){   echo 'No permission'; exit;}
$title='Accept MRR'; $showbranches=false; 

$temporarytable='CREATE TEMPORARY TABLE MRRpendinglist AS
SELECT MONTHNAME(`Date`) AS `Month`,COUNT(`TxnID`) AS `NoofMRRs` FROM `invty_2mrr` WHERE SenttoAcctg=0 '.((!isset($_GET['hidePR']))?'':'AND txntype=6 ').' AND txntype NOT IN (9) GROUP BY MONTH(`Date`) UNION SELECT  (MONTHNAME(`Date`)) AS `Month`,COUNT(p.`TxnID`) AS `NoofMRRs` FROM `invty_2pr` p join invty_2prsub ps on ps.TxnID=p.TxnID WHERE SenttoAcctg=0 AND p.Posted=1 AND DecisionNo=1 GROUP BY MONTH(`Date`)';
// echo $temporarytable; 
$stmtt=$link->prepare($temporarytable);
    $stmtt->execute();
	$sql0='SELECT *,SUM(NoofMRRs) AS Total FROM MRRpendinglist GROUP BY `Month` ';
$stmt0=$link->query($sql0);  $result0=$stmt0->fetchAll(); $formdesc='';
if($stmt0->rowCount()>0){
foreach ($result0 as $res) { $formdesc.='<tr><td>'.$res['Month'].'</td><td>'.$res['Total'].'</td></tr>';}
$formdesc='<br><br></i><h3><a href=lookupgeninv.php?w=AcceptMRR&hidePR=1><u>Hide Purchase Returns</u></a> &nbsp; &nbsp;<a href=lookupgeninv.php?w=AcceptMRR><u>Show All</u></a></h3><br><br>Unaccepted MRR\'s Per Month<table>'.$formdesc.'</table><i>';
}
include('../backendphp/layout/clickontabletoedithead.php');
include ('../backendphp/layout/standardprintsettings.php');


$sqlpr='select p.*, s.SupplierName,Date, if(isnull(RCompany), \'\', c.Company) as RCo from invty_2pr p
        join `1suppliers` as s on p.SupplierNo=s.SupplierNo left join `1companies` c on p.RCompany=c.CompanyNo join invty_2prsub ps on ps.TxnID=p.TxnID where DecisionNo=1 AND p.SenttoAcctg=0 AND p.Posted<>0 '.((!isset($_GET['hidePR']))?'':'AND txntype=6 ').' Group By PRNo  ORDER BY p.PRNo';

    $stmtpr=$link->query($sqlpr);
    $resultpr=$stmtpr->fetchAll();
foreach ($resultpr as $prmain){
$mainpr='<div class="keeptog"><font face="arial" size="3"><table width="80%" class="maintable" bgcolor="FFFFF">
<tr>
<td width="20%">PR No. '.$prmain['PRNo'].'</td>
<td width="30%">Supplier:  '.$prmain['SupplierName'].'</td>
<td width="10%">RCo: '.$prmain['RCo'].'</td></tr><tr>
<td >Date Released:  '.$prmain['Date'].'</td>
<td> '.$prmain['Remarks'].'</td>
</tr></table></font>';

$sqlprsub='Select ps.ItemCode, ps.UnitCost*-1 as UnitCost,ps.Qty,(ps.UnitCost*ps.Qty) as Total,concat(c.Category,\' \', i.ItemDesc) as Description, i.Unit, ps.UnitCost*ps.Qty as Amount from invty_2prsub ps join invty_1items i on i.ItemCode=ps.ItemCode join invty_1category c on c.CatNo=i.CatNo join invty_2pr p on p.TxnID=ps.TxnID where (txntype=6 or txntype=8) '.((!isset($_GET['hidePR']))?'':'AND txntype=6 ').' and p.PRNo=\''.($prmain['PRNo']).'\'';
    // echo $sqlprsub; exit();
    $stmtprsub=$link->query($sqlprsub);
    $resultprsub=$stmtprsub->fetchAll();
    $prsub='<table width="80%" class="subtable" bgcolor="FFFFF"><tr>
<td width=5%>ItemCode</td>
<td width=65%>Description</td>
<td width=5%>Qty</td>
<td width=5%>Unit</td>
<td width=5%>UnitCost</td></tr>';
foreach ($resultprsub as $rowprsub){    
$prsub=$prsub.'<tr>
<td width=5%>'.$rowprsub['ItemCode'].'</td>
<td width=65%>'.$rowprsub['Description'].'</td>
<td width=5%>'.$rowprsub['Qty'].'</td>
<td width=5%>'.$rowprsub['Unit'].'</td>
<td width=5%>'.$rowprsub['UnitCost'].'</td></tr>';
}
$sqlsumpr='Select count(ItemCode) as LineItems, sum(ps.UnitCost*ps.Qty) as Total from invty_2prsub ps join invty_2pr p on p.TxnID=ps.TxnID where (txntype=6 or txntype=8) and p.PRNo=\''.($prmain['PRNo']).'\'';
    $stmt3=$link->query($sqlsumpr);
    $result3=$stmt3->fetch();
    $total='<div style="float:right">Line Items: '.$result3['LineItems'].str_repeat('&nbsp',10).'Total:  '.number_format($result3['Total'],2).str_repeat('&nbsp',10).'<a href="praddmrr.php?w=AcceptPR&action_token='.$_SESSION['action_token'].'&txntype=8&TxnID='.$prmain['TxnID'].'">Accept</a></div>';

echo $mainpr.$prsub.'</table>'.$total.'</div><br><hr><br></body></html>';

}
//END OF PR
$sqlmain='select m.*, s.SupplierName, DATE_ADD(`Date`,INTERVAL mod(6-DAYOFWEEK(`Date`)+7,7)+m.`Terms` DAY) as DueDate, if(isnull(RCompany), \'\', c.Company) as RCo from invty_2mrr m
        join `1suppliers` as s on m.SupplierNo=s.SupplierNo left join `1companies` c on m.RCompany=c.CompanyNo where (txntype=6 or txntype=8)'.((!isset($_GET['hidePR']))?'':'AND txntype=6 ').' and m.SenttoAcctg=0 AND m.Posted<>0  AND (SuppInvNo IS NOT NULL OR SuppInvNo<>"" OR SuppInvNo<>0) ORDER BY m.MRRNo';
// echo $sqlmain; exit();
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetchAll();
foreach ($result as $mainrow){
$main='<div class="keeptog"><font face="arial" size="3"><table width="80%" class="maintable" bgcolor="FFFFF">
<tr>
<td width="20%">MRR No. '.$mainrow['MRRNo'].'</td>
<td width="30%">Supplier:  '.$mainrow['SupplierName'].'</td>
<td width="20%">For PO No: '.$mainrow['ForPONo'].'</td>
<td width="20%">Delivery Receipt: '.$mainrow['SuppDRNo'].'</td>
<td width="20%">Supplier Inv Details: '.$mainrow['SuppInvNo'].' ('.$mainrow['SuppInvDate'].')'.'</td>
</tr><tr><td width="10%">RCo: '.$mainrow['RCo'].'</td>
<td >Date Received:  '.$mainrow['Date'].'</td>
<td> '.$mainrow['Remarks'].'</td><td>Terms: '.$mainrow['Terms'].'D</td>
<td colspan=2>Due: '.$mainrow['DueDate'].'</td></tr></table></font>';

$sqlsub='Select s.ItemCode, s.UnitCost,s.Qty,concat(c.Category,\' \', i.ItemDesc) as Description, i.Unit, s.UnitCost*s.Qty as Amount from invty_2mrrsub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo join invty_2mrr m on m.TxnID=s.TxnID where (txntype=6 or txntype=8) '.((!isset($_GET['hidePR']))?'':'AND txntype=6 ').' and m.MRRNo=\''.($mainrow['MRRNo']).'\' Order by Category';
    
    $stmt=$link->query($sqlsub);
    $resultsub=$stmt->fetchAll();
    $sub='<table width="80%" class="subtable" bgcolor="FFFFF"><tr>
<td width=5%>ItemCode</td>
<td width=65%>Description</td>
<td width=5%>Qty</td>
<td width=5%>Unit</td>
<td width=5%>UnitCost</td></tr>';
foreach ($resultsub as $row){    
$sub=$sub.'<tr>
<td width=5%>'.$row['ItemCode'].'</td>
<td width=65%>'.$row['Description'].'</td>
<td width=5%>'.$row['Qty'].'</td>
<td width=5%>'.$row['Unit'].'</td>
<td width=5%>'.$row['UnitCost'].'</td></tr>';
}
$sqlsum='Select count(ItemCode) as LineItems, sum(s.UnitCost*s.Qty) as Total from invty_2mrrsub s join invty_2mrr m on m.TxnID=s.TxnID where (txntype=6 or txntype=8) and m.MRRNo=\''.($mainrow['MRRNo']).'\'';
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='<div style="float:right">Line Items: '.$result['LineItems'].str_repeat('&nbsp',10).'Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10).'<a href="praddmrr.php?w=AcceptMRR&action_token='.$_SESSION['action_token'].'&txntype=6&TxnID='.$mainrow['TxnID'].'">Accept</a></div>';

echo $main.$sub.'</table>'.$total.'</div><br><hr><br></body></html>';
    
 }   
      break;

   
      
case 'DiffTxfrAmts':
if (!allowedToOpen(723,'1rtc')) { echo 'No permission'; exit;}
$fieldname='Month'; $title='Unequal Transfer Amounts';
$lefttabletitle='Unequal Transfer Amounts (Invty) and No Date In';
$righttabletitle='Transfer Out Totals: Invty vs. Acctg';
$showbranches=false;
//include_once('../backendphp/layout/clickontabletoedithead.php');
?>
<form method="post" action="lookupgeninv.php?w=DiffTxfrAmts&Month=<?php echo (!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]); ?>" enctype="multipart/form-data">
 Choose Month (1 - 12):  <input type="text" name="<?php echo $fieldname; ?>" value="<?php echo (!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]); ?>"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<?php

if (!isset($_REQUEST[$fieldname])){
goto noform;
} else {
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_REQUEST[$fieldname].'-1')).'<br>';
$txndate=$_REQUEST[$fieldname];
$branchcondition=allowedToOpen(7231,'1rtc')?' and (m.BranchNo='.$_SESSION['bnum'].' or m.ToBranchNo='.$_SESSION['bnum'].')' :'';
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'DateOUT, TransferNo');
$sqlleft='SELECT m.TxnID, m.DateOUT, m.DateIN, m.TransferNo, b.Branch AS ToBranch, b1.Branch AS FromBranch, Sum(`QtySent`*`UnitPrice`) AS Sent, m.Remarks, Sum(`QtyReceived`*`UnitCost`) AS Received, Sum(`QtyReceived`*`UnitCost`)-Sum(`QtySent`*`UnitPrice`) AS Diff
FROM ((invty_2transfer m INNER JOIN `1branches` b ON m.ToBranchNo=b.BranchNo) INNER JOIN `1branches` b1  ON m.BranchNo=b1.BranchNo) INNER JOIN invty_2transfersub s ON m.TxnID=s.TxnID
WHERE (Month(DateOUT)='.$txndate.' or Month(DateIN)='.$txndate.'  or (DateIN is null)) '.$branchcondition.'
GROUP BY m.TxnID
HAVING (((Sum(`QtyReceived`*`UnitCost`)-Sum(`QtySent`*`UnitPrice`))<-1 Or (Sum(`QtyReceived`*`UnitCost`)-Sum(`QtySent`*`UnitPrice`))>1))
ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
$sqlright='SELECT m.TxnID, m.DateOUT, m.DateIN, m.TransferNo, b.Branch AS ToBranch, b1.Branch AS FromBranch, Sum(`QtySent`*`UnitPrice`) AS InvtyTotal, m.Remarks, (SELECT SUM(ts.Amount) FROM  `acctg_2txfrsub` ts JOIN  `acctg_2txfrmain` tm ON tm.TxnID=ts.TxnID WHERE m.TransferNo=ts.Particulars AND tm.FromBranchNo=m.BranchNo) AS AcctgTotal
FROM invty_2transfer m JOIN invty_2transfersub s ON m.TxnID=s.TxnID
JOIN `1branches` b ON m.ToBranchNo=b.BranchNo 
JOIN `1branches` b1  ON m.BranchNo=b1.BranchNo 
WHERE (Month(m.DateOUT)='.$txndate.') '.$branchcondition.'
GROUP BY m.TxnID HAVING (InvtyTotal-AcctgTotal<-1) or (InvtyTotal-AcctgTotal>1) ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
}
$columnnamesleft=array('DateOUT','DateIN','TransferNo','FromBranch','ToBranch','Sent','Received','Diff','Remarks');
$columnnamesright=array('DateOUT','DateIN','TransferNo','FromBranch','ToBranch','InvtyTotal','AcctgTotal','Remarks');
$columnsub=$columnnamesleft;
//$editprocess='addedittxfr.php?w=Transfers&TxnID=';
//$editprocesslabel='Lookup';
    include('../backendphp/layout/twotablessidebyside.php');
    break;

case 'DiffPriceQty':
    if (!allowedToOpen(722,'1rtc')) {   echo 'No permission'; exit;}
$fieldname='Month';
$title='Diff Price/Qty';

?>
<form method="post" action="lookupgeninv.php?w=DiffPriceQty&Month=<?php echo (!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]); ?>" enctype="multipart/form-data">
 Choose Month (1 - 12):  <input type="text" name="<?php echo $fieldname; ?>" value="<?php echo (!isset($_REQUEST[$fieldname])?date('m'):$_REQUEST[$fieldname]); ?>"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<?php
if (!isset($_REQUEST[$fieldname])){
    include_once('../backendphp/layout/clickontabletoedithead.php');
goto noform;
} else {
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_REQUEST[$fieldname].'-1')).'<br>';
$txndate=$_REQUEST[$fieldname];
$branchcondition=allowedToOpen(7231,'1rtc')?' and (m.BranchNo='.$_SESSION['bnum'].' or m.ToBranchNo='.$_SESSION['bnum'].')' :'';
$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'DateOUT, TransferNo');
$sql='SELECT m.DateOUT, m.DateIN, m.TransferNo, b.Branch AS ToBranch, b1.Branch AS FromBranch, s.ItemCode, s.QtySent, s.QtyReceived, `QtySent`-`QtyReceived` AS DiffQty, s.UnitCost, s.UnitPrice, `UnitPrice`-`UnitCost` AS DiffPrice
FROM ((invty_2transfer m INNER JOIN `1branches` b ON m.ToBranchNo = b.BranchNo) INNER JOIN `1branches` b1 ON m.BranchNo = b1.BranchNo) INNER JOIN invty_2transfersub s ON m.TxnID = s.TxnID
WHERE (((`QtySent`-`QtyReceived`)<>0) AND ((m.BranchNo)<>`ToBranchNo`) AND ((Month(DateOUT)='.$txndate.' or Month(DateIN)='.$txndate.')) '.$branchcondition.') OR (((`UnitPrice`-`UnitCost`)>0.1 Or (`UnitPrice`-`UnitCost`)<-0.1) AND ((m.BranchNo)<>`ToBranchNo`) AND ((Month(DateOUT)='.$txndate.' or Month(DateIN)='.$txndate.')) '.$branchcondition.') ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
}
$columnnames=array('DateOUT','DateIN','TransferNo','FromBranch','ToBranch','ItemCode','QtySent','QtyReceived','DiffQty','UnitCost','UnitPrice','DiffPrice');
$columnsub=$columnnames;
    include('../backendphp/layout/displayastable.php');
    break;

case 'DailySales':
   if (!allowedToOpen(721,'1rtc')) { echo 'No permission'; exit;}

$pagetouse='lookupgeninv.php?w='.$whichqry;
$fieldname='Date';
$title='Daily Sales';
$method='GET';
$showbranches=false;
include_once('../backendphp/layout/clickontabletoedithead.php');
?>
   <form method="post" action="<?php echo $pagetouse; ?>" enctype="multipart/form-data">
                Choose Date:  <input type="date" name="<?php echo $fieldname; ?>" value="<?php echo date('Y-m-d'); ?>"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<br>
<?php
if (!isset($_REQUEST[$fieldname])){
$date=date('Y-m-d');
} else {
$date=$_REQUEST[$fieldname]; 
}
$confirmcondition='';
echo 'Daily Sales on '. $date.'<br>';
if (allowedToOpen(7331,'1rtc')){ $condition=''; } else { //team leaders
     $condition=' WHERE BranchNo in (Select g.BranchNo from `attend_1branchgroups` g where TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') ';
}
include('../invty/dailysalesdata.php');
echo $msg;

   break;

case 'WklyCompareSales':
   if (!allowedToOpen(733,'1rtc')) { echo 'No permission'; exit;}

$pagetouse='lookupgeninv.php?w='.$whichqry;
$title='Compare Sales Per Week';
$formdesc='Cash Sales - Returns + Dated Collections (Cleared and Uncleared)<br>* Dated Collections can be found on Collection Receipts.<br>';
$method='GET';
$showbranches=false;
include_once('../backendphp/layout/clickontabletoedithead.php');
?>
   <form method="post" action="<?php echo $pagetouse; ?>" enctype="multipart/form-data">
                Sales From  <input type="date" name="FromDate" value="<?php echo date('Y-m-d',strtotime("-6 days")); ?>"></input> To <input type="date" name="ToDate" value="<?php echo date('Y-m-d'); ?>"></input> 
<input type="submit" name="lookup" value="Lookup"> </form>
<br>
<?php
if (allowedToOpen(7331,'1rtc')){ $condition='  '; } else { //team leaders
     $condition=' AND b.BranchNo in (Select g.BranchNo from `attend_1branchgroups` g where TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') ';
}
if (!isset($_REQUEST['FromDate'])){
$fromdate=date('Y-m-d',strtotime("-6 days"));
$todate=date('Y-m-d');
} else {
$fromdate=$_REQUEST['FromDate']; $todate=$_REQUEST['ToDate'];
}
echo 'Daily Sales from '. $fromdate.' to '.$todate.'<br>';   
$sql0='Create temporary table comparesales(
    BranchNo smallint(6) not null,
	Date date,
    SalesCollectLessReturns double default 0
) Select b.BranchNo, m.Date, truncate(ifnull(sum(case when (m.txntype in (1,5,10)) then (Qty*UnitPrice) end),0),0) as SalesCollectLessReturns
from `1branches` b left join `invty_2sale` m  on b.BranchNo=m.BranchNo and (m.Date>=\''.$fromdate.'\') and (m.Date<=\''.$todate.'\')
 join `invty_2salesub` s on m.TxnID=s.TxnID  WHERE b.Active=1 AND PseudoBranch=0 and b.BranchNo<97 '.$condition.' group by m.Date, m.BranchNo 
union all
SELECT b.BranchNo, m.DateofCheck, Sum(ifnull(Amount,0)) as Collections FROM acctg_2collectmain m join `acctg_2collectsub` b on m.TxnID=b.TxnID 
WHERE (DateofCheck>=\''.$fromdate.'\' and DateofCheck<=\''.$todate.'\') '.$condition.' group by b.BranchNo,m.Date';
// echo $sql0; 
$stmt=$link->prepare($sql0);
$stmt->execute();


if (allowedToOpen(7331,'1rtc')){
   $sql0='Create temporary table comparesalessum(
	Date date,
    SalesCollectLessReturns double default 0
) Select m.Date, truncate(ifnull(sum(case when (m.txntype in (1,5,10)) then (Qty*UnitPrice) end),0),0) as SalesCollectLessReturns
from `1branches` b left join `invty_2sale` m  on b.BranchNo=m.BranchNo and (m.Date>=\''.$fromdate.'\') and (m.Date<=\''.$todate.'\')
 join `invty_2salesub` s on m.TxnID=s.TxnID where b.Active=1 and b.BranchNo<97 group by m.Date
union all
SELECT m.Date, Sum(ifnull(Amount,0)) as Collections FROM acctg_2collectmain m join `acctg_2collectsub` s on m.TxnID=s.TxnID 
where (DateofCheck>=\''.$fromdate.'\' and DateofCheck<=\''.$todate.'\') group by m.Date';
   } else { //team leaders
     $sql0='Create temporary table comparesalessum(
	Date date,
    SalesCollectLessReturns double default 0
) ';
}

//echo $sql0; break;
$stmt=$link->prepare($sql0);
$stmt->execute();

$sql=''; $sqlsum=''; $columnnames=array('Branch');
$daily=$fromdate;
while ($daily<=$todate){   
   $sql=$sql.' format(sum(case when Date=\''.$daily.'\' then SalesCollectLessReturns end),0) as \''.$daily.'\' , ';
   $columnnames[]=$daily;
   $sqlsum=$sqlsum.' format(sum(case when Date=\''.$daily.'\' then SalesCollectLessReturns end),0) as \''.$daily.'\' , ';
   $daily=date('Y-m-d',strtotime("+ 1 days", strtotime($daily)));
}


array_push($columnnames,'CashSales - Returns','DatedCollections','SubTotal');

$sqltemp='CREATE TEMPORARY TABLE tempCollections AS SELECT b.BranchNo, Sum(ifnull(Amount,0)) as DatedCollections FROM acctg_2collectmain m join `acctg_2collectsub` b on m.TxnID=b.TxnID WHERE (DateofCheck>=\''.$fromdate.'\' and DateofCheck<=\''.$todate.'\') '.$condition.' group by b.BranchNo;';
$stmttemp=$link->prepare($sqltemp);
$stmttemp->execute();


$sql='select '.$sql.' format(sum(SalesCollectLessReturns),0) as "SubTotal",IFNULL((SELECT FORMAT(DatedCollections,0) FROM tempCollections WHERE BranchNo=cs.BranchNo),0) as DatedCollections,IFNULL(FORMAT((SUM(SalesCollectLessReturns) - (SELECT DatedCollections)),0),0) AS `CashSales - Returns`, b.BranchNo, Branch, b.AreaNo from comparesales cs '.((allowedToOpen(7331,'1rtc'))?'right':'') .' join `1branches` b on b.BranchNo=cs.BranchNo where Active=1 and PseudoBranch=0 and b.BranchNo<95 group by b.BranchNo 
union all
select '.$sqlsum.' "" as "SubTotal","" AS DatedCollections,"" AS `CashSales - Returns`, "" as BranchNo, "Total" as Branch, "999" as AreaNo from comparesalessum order by AreaNo, Branch';
// echo $sql; exit();
    include_once('../backendphp/layout/displayastable.php');
   break;

case 'Targets':
if (!allowedToOpen(730,'1rtc')) {   echo 'No permission'; exit;}


if (allowedToOpen(7301,'1rtc')){
    $condition='';
$columnnames=array('BranchNo','Branch','Class','TeamLeader','CashSales','ClearedCollections','OverdueAR','UndepPDC','NetforBranch','MonthTarget','PercentToReachTarget','TargetReached','Units','%Auto','%Aircon','%Ref','%Multi');
} elseif (allowedToOpen(7302,'1rtc')){
     $condition=' AND ts.BranchNo in (Select g.BranchNo from `attend_1branchgroups` g where TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') '; 
     if (allowedToOpen(2013,'1rtc')){ // SAM's
     $columnnames=array('BranchNo','Branch','Class','TeamLeader','CashSales','ClearedCollections','OverdueAR','UndepPDC','NetforBranch','MonthTarget','PercentToReachTarget','TargetReached','Units','%Auto','%Aircon','%Ref','%Multi');
     } else { $columnnames=array('Branch','Class','TeamLeader','MonthTarget','PercentToReachTarget','TargetReached');} // STL's
} elseif (allowedToOpen(7306,'1rtc')){
     $condition=' AND ts.BranchNo='.$_SESSION['bnum'] ;
     $columnnames=array('Branch','PercentToReachTarget','TargetReached');
} elseif (allowedToOpen(7303,'1rtc')){ //HR and C&C
     $condition='';
     $columnnames=array('Branch','Class','TeamLeader','TargetReached','Units');
} else {
   $condition='';
     $columnnames=array('Branch','PercentToReachTarget','TargetReached');
}

$pagetouse='lookupgeninv.php?w='.$whichqry;
$fieldname='Month';
$title=(!isset($_GET['PerBranch'])?'Target Summary':'Target Scores Per Branch');
$method='GET';
$showbranches=false;
if(!isset($_GET['PerBranch'])) {
?>
<br><form method="post" action="<?php echo $pagetouse; ?>" enctype="multipart/form-data" style="display: inline;">
                Choose Month (1 - 12):  <input type="text" name="<?php echo $fieldname; ?>" value="<?php echo date('m'); ?>"></input>
<input type="submit" name="lookup" value="Lookup">

<?php if (allowedToOpen(7308,'1rtc')) { echo '<input type="submit" name="lookup" value="Set Scores as Final"> ';}
if (allowedToOpen(7311,'1rtc')) { echo '<input type="submit" name="lookup" value="Redo Scores of Previous Month"> ';}
?>
<?php if (allowedToOpen(7132,'1rtc')){ echo '<input type="submit" name="btnUpdateData" value="Update Data of Graphs for Current Month">
'; }?>

</form>&nbsp; &nbsp;

<?php
    if (allowedToOpen(7308,'1rtc')) { echo '<a href="../generalinfo/loginmsgsettings.php">Open Scores for Login Page</a> ';}
} else {
    if (!allowedToOpen(731,'1rtc')){  echo 'No permission'; exit;}
    }

?><br><i>Note: Overdue AR values change when collected in the succeeding months. <br>
                Net for Branch= Cash Sales - Returns + Cleared Cash and Check Collections - Overdue AR (includes Terms + 10 days for collection) - Undeposited PDC (overdue)<br>
                </i>
<?php
if (allowedToOpen(2013,'1rtc') or allowedToOpen(2012,'1rtc') or allowedToOpen(201,'1rtc')){ 
    include_once('../backendphp/layout/linkstyle.php');
         echo '<br/><br/> &nbsp; &nbsp; <a id=\'link\' href="calctargetsnotes.php" target=_blank>Notes on Incentives</a> &nbsp; &nbsp;'
    . '<a id=\'link\' href="lookupgeninv.php?w=Tagged" target=_blank>Tagged Sales</a> &nbsp; &nbsp;<a id=\'link\' href="lookupgeninv.php?w=ClassPerQuarter" target=_blank>Class Per Quarter</a> &nbsp; &nbsp;<br/><br/>';
    
}
if (!isset($_REQUEST[$fieldname]) and !isset($_REQUEST['PerBranch'])) {  
 echo '<title>Target Reached?</title><br><br><h2>Choose month first</h2>'; goto noform;}//'For the month of '. date('F',strtotime(''.$currentyr.'-'.date('m').'-1')).'<br>';  $txndate=str_pad(date('m'),2,'0',STR_PAD_LEFT);}
    elseif (isset($_REQUEST['PerBranch'])) {   
        $formdesc=$_SESSION['@brn'];  
        $txndate=$currentyr;
    } else {
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_POST[$fieldname].'-1')).'<br>'; 
if (date('m')==$_POST[$fieldname]){
$sql0='SELECT `TimeStamp` FROM acctg_6targetscores WHERE DisplayType=5 ORDER BY TimeStamp DESC LIMIT 1;';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$formdesc.=' as of '.$res0['TimeStamp'];}
$txndate=str_pad($_REQUEST[$fieldname],2,'0',STR_PAD_LEFT);
}


if (!isset($_REQUEST['PerBranch'])) {
    
        
// check if show rates
$showrates=array(201,2011,2012,2013);
$showrate=0;
foreach ($showrates as $ok) { if (allowedToOpen($ok,'1rtc')) { $showrate=($showrate+1); goto showrates; } else { $showrate=$showrate; }}
// end of check
showrates:
if ($showrate>0) { 
     
?>
<br>Branch Incentive Rates Per Unit<br>
<table><thead>
<td>Score</td><td>  100.00%  </td><td>  115.00%</td></thead>
<tr><td>Branch Head, Branch OIC, & Junior Branch Head (if there is NO Branch Head)  </td><td>  350  </td><td>  400</td></tr>
<tr><td>Junior Branch Head (if there is a Branch Head)  </td><td>  250  </td><td>  300</td></tr>
<tr><td>Branch Personnel  </td><td>  150  </td><td>  200</td></tr>
</table>
<br/><br/>

<?php
    }
    
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;


$sql='SELECT `'.$txndate.'` AS Month,ts.BranchNo,Units,FORMAT(CashSales,0) AS CashSales,FORMAT(ClearedCollections,0) AS ClearedCollections,FORMAT(OverdueAR,0) AS OverdueAR,FORMAT(UndepPDC,0) AS UndepPDC,Branch,Nickname AS TeamLeader,FORMAT(`'.$txndate.'`,0) AS MonthTarget, IF((ClassLastYr=3),\'Prime\',IF(ClassLastYr=2,\'Growth\',\'Seed\')) AS Class,FORMAT(TRUNCATE(Net,2),0) AS NetforBranch,if(Net<0,-100,if((Net>=`'.$txndate.'`),0,truncate((Net/`'.$txndate.'`)*100,2)-100)) as PercentToReachTarget,if ((Net/`'.$txndate.'`)<1,0,truncate((Net/`'.$txndate.'`)*100,2)) as TargetReached, (Auto+Aircon+Ref+Multi) AS TotalSales, TRUNCATE(Auto/(SELECT TotalSales)*100,2) AS `%Auto`, 
TRUNCATE(Aircon/(SELECT TotalSales)*100,2) AS `%Aircon`, TRUNCATE(Ref/(SELECT TotalSales)*100,2) AS `%Ref`, TRUNCATE(Multi/(SELECT TotalSales)*100,2) AS `%Multi` FROM acctg_6targetscores ts JOIN 1branches b ON ts.BranchNo=b.BranchNo JOIN attend_1branchgroups bg ON ts.BranchNo=bg.BranchNo LEFT JOIN 1_gamit.0idinfo id ON bg.TeamLeader=id.IDNo JOIN acctg_1yearsalestargets yst ON ts.BranchNo=yst.BranchNo WHERE MonthNo>0 AND MonthNo='.$txndate.' '.$condition.' ORDER BY TargetReached desc,PercentToReachTarget desc, Branch;';
// echo $sql; exit();  



if((allowedToOpen(7311,'1rtc')) AND ((isset($_POST['lookup'])) AND ($_POST['lookup']==='Redo Scores of Previous Month'))){
	// print_r($_POST);
	$sqlu='UPDATE acctg_6targetscores SET DisplayType=5 WHERE MonthNo='.$txndate.' AND DisplayType=1;';
	
	$stmtu=$link->prepare($sqlu); $stmtu->execute();
	
	echo '<font color="green"><h2>Done</h2></font>';
	
	exit();
}



if(((allowedToOpen(7308,'1rtc') and (((isset($_POST['lookup'])) AND ($_POST['lookup']==='Set Scores as Final'))) OR  ((allowedToOpen(7132,'1rtc')) and (isset($_POST['btnUpdateData']))) ))){

	if ((isset($_POST['btnUpdateData'])) OR ((((isset($_POST['lookup'])) AND ($_POST['lookup']==='Set Scores as Final') AND ($_POST['Month']==(date('m')==1?$txndate:date('m')-1)))))){
		
		$title='Data Updated Successfully.';
		echo '<title>'.$title.'</title>';
		$sqldel='DELETE FROM acctg_6targetscores WHERE MonthNo>0 AND MonthNo='.$txndate.' AND DisplayType=5';
		
		$stmt=$link->prepare($sqldel); $stmt->execute();
		if(isset($_POST['btnUpdateData'])){
			// $txndate=(strlen(date('m'))<>2?'0'.date('m'):date('m'));
		}
		// $txndate='02'; //WILL REMOVE AFTER TESTING
	}
	
    require ('calctargets.php');
    require ('insert6targets.php');
        
	
		if (isset($_POST['btnUpdateData'])){
			echo '<br><h3 style="color:green;">'.$title.'</h3>';
			if ((allowedToOpen(7136,'1rtc')) OR (allowedToOpen(7137,'1rtc'))){ //sales head/
				echo '<a href="../graphs/allreports.php?w=AggregateReports">View Graphs</a>';
			}
			exit();
		} else {
			$sql0='CREATE TEMPORARY TABLE tempnoofsales AS
			SELECT BranchNo,COUNT(DISTINCT(Date)) AS NoOfSaleDays FROM acctg_2salemain WHERE MONTH(Date)='.$txndate.' GROUP BY BranchNo;';
			$stmt=$link->prepare($sql0); $stmt->execute();
			
			$sql0='UPDATE acctg_6targetscores ts JOIN tempnoofsales tos ON ts.BranchNo=tos.BranchNo SET ts.NoOfSaleDays=tos.NoOfSaleDays WHERE MonthNo='.$txndate.';';
			$stmt=$link->prepare($sql0); $stmt->execute();
			header('Location:../generalinfo/loginmsgsettings.php');
			exit();
		}
}
  
} else {
    if (!allowedToOpen(7301,'1rtc')){  echo 'No permission'; exit;}
	
	//removed create temporary table
	$sql='SELECT * FROM acctg_6targetscores WHERE BranchNo='.$_SESSION['bnum'].' ORDER BY MonthNo';
    $columnnames=array('Month','CashSales','ClearedCollections','OverdueAR','UndepPDC','NetforBranch','MonthTarget','PercentToReachTarget','TargetReached','Units','%Auto','%Aircon','%Ref','%Multi');
}
$sql0='SELECT ValuePerTargetUnit FROM `00dataclosedby` WHERE ForDB=1';
$stmt0=$link->query($sql0); $res0=$stmt0->fetch(); $valueperunit=$res0['ValuePerTargetUnit'];
if(allowedToOpen(100, '1rtc')) { echo 'Value per Unit: P '.number_format($valueperunit,0).'<br><br>';}
include('../backendphp/layout/displayastablenosort.php');
  

if (allowedToOpen(7304,'1rtc')){   
if (allowedToOpen(7301,'1rtc')){
$columnnames=array('Total','CashSales','ClearedCollections','OverdueAR','UndepPDC','NetforBranch','MonthTarget','PercentToReachTarget','TargetReached','%Auto','%Aircon','%Ref','%Multi');}
elseif (allowedToOpen(7302,'1rtc')){ $columnnames=array('TargetReached');} 
else { $columnnames=array();}
   echo '<br>Sales of branches less than 4 months old are included in the calculation.  Targets are not.<br>'; 
if (!isset($_REQUEST['PerBranch'])) {
    $thisyr=$currentyr; $firstofmonth=$thisyr.'-'.$txndate.'-01';
   $sqltargetall='Select truncate(sum(yt.`'.$txndate.'`),0) as MonthTargetAll from  `1branches` b
join `acctg_1yearsalestargets` yt on b.BranchNo=yt.BranchNo where (datediff(\''.$firstofmonth.'\',b.Anniversary)>120 and Active=1) or (datediff(\''.$firstofmonth.'\',b.Anniversary)<=120 and MovedBranch<>-1) ;';
   $stmt=$link->query($sqltargetall);
$target=$stmt->fetch(); $targetall=$target['MonthTargetAll'];
$sql='SELECT MONTHNAME("'.$currentyr.'-'.$txndate.'-01") AS Total, FORMAT(SUM(`CashSales`),0) as `CashSales`, FORMAT(SUM(`ClearedCollections`),0) as `ClearedCollections`,  FORMAT(SUM(`OverdueAR`),0) as `OverdueAR`, FORMAT(SUM(`UndepPDC`),0) as `UndepPDC`, FORMAT(SUM(`Net`),0) as `NetforBranch`, FORMAT('.$targetall.',0) as MonthTarget,
truncate((((SUM(Net)/'.$targetall.')*100)-100),2) as PercentToReachTarget,
truncate((SUM(Net)/'.$targetall.')*100,2) as TargetReached, TRUNCATE(SUM(Auto)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `%Auto`, 
TRUNCATE(SUM(Aircon)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `%Aircon`, TRUNCATE(SUM(Ref)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `%Ref`, TRUNCATE(SUM(Multi)/(SUM(Auto+Aircon+Ref+Multi))*100,2) AS `%Multi`  
FROM acctg_6targetscores tc JOIN `acctg_1yearsalestargets` yt on tc.BranchNo=yt.BranchNo WHERE MonthNo>0 AND MonthNo='.$txndate.';';
} else {
   $sql0='SELECT SUM(`01`)+SUM(`02`)+SUM(`03`)+SUM(`04`)+SUM(`05`)+SUM(`06`)+SUM(`07`)+SUM(`08`)+SUM(`09`)+SUM(`10`)+SUM(`11`)+SUM(`12`) AS MonthTarget  FROM acctg_1yearsalestargets;';
   $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
   $monthtarget=$res0['MonthTarget'];
   
$sql='SELECT "'.$currentyr.'" AS `Year`, FORMAT(
SUM(CashSales),0) AS CashSales, FORMAT(SUM(ClearedCollections),0) AS ClearedCollections, FORMAT(SUM(OverdueAR),0) AS OverdueAR, FORMAT(SUM(UndepPDC),0) AS UndepPDC, FORMAT(SUM(Net),0) AS NetforBranch, FORMAT('.$monthtarget.',0) AS YearTarget, TRUNCATE((SUM(Net)/'.$monthtarget.')*100,2) AS TargetReached  FROM acctg_6targetscores WHERE MonthNo>0 AND BranchNo<1000';
 $columnnames=array('Year','CashSales','ClearedCollections','OverdueAR','UndepPDC','NetforBranch','YearTarget','TargetReached');
   
}
echo '<br>';
$hidecount=true;
include('../backendphp/layout/displayastableonlynoheaders.php');
  exit();
  
}


   break;
   
case 'STLScoreDetails':
    if (allowedToOpen(7301,'1rtc')){  $condition=''; } 
    elseif (allowedToOpen(7302,'1rtc')){ $condition=' WHERE TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].' ';} 
    else {  echo 'No permission'; exit;}
    $tl=$_REQUEST['TL'];
	$txndate=(strlen($_REQUEST['m'])<>2?'0'.$_REQUEST['m']:$_REQUEST['m']);	
	// $txndate=;
    $sql0='SELECT CONCAT(Nickname, " ", Surname) AS Name, IFNULL(sc.Timestamp,Now()) AS TaggedAsOf FROM `1employees` e LEFT JOIN acctg_6targetscores sc ON e.IDNo=sc.BranchNo AND MonthNo>0 AND sc.MonthNo='.$txndate.' AND sc.DisplayType<>5 WHERE e.IDNo='.$tl; // timestamp of current month is not counted
    $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
    $title='Details for '.$res0['Name'];
    $formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$txndate.'-1')).'<br>Data as of '. date('mmm dd, h:m:s',strtotime($res0['TaggedAsOf'])).'<br>';
    $sql='SELECT sm.`Date`, Branch, sm.`SaleNo`, ClientName AS Client,
        FORMAT((Sum(`Qty` * `UnitPrice`)-SUM(IF(ISNULL(fc.Amount),0,fc.Amount))),2) AS CashSalesLessReturns,
        (Sum(`Qty` * `UnitPrice`)-SUM(IF(ISNULL(fc.Amount),0,fc.Amount))) AS CashSalesLessReturnsAmount
    FROM
        `invty_2sale` sm JOIN `invty_2salesub` ss ON `sm`.`TxnID` = `ss`.`TxnID`
        JOIN `1clients` c ON c.ClientNo=sm.ClientNo JOIN `1branches` b ON b.BranchNo=sm.BranchNo
        LEFT JOIN `approvals_2freightclients` fc ON (fc.ForInvoiceNo=sm.SaleNo AND fc.BranchNo=sm.BranchNo AND fc.txntype=sm.txntype AND PriceFreightInclusive=1)
WHERE  PaymentType<>2 AND sm.txntype<>3 AND Month(sm.`Date`)='.$txndate.' AND sm.BranchNo<95 AND (sm.`TeamLeader` IS NOT NULL AND sm.`TeamLeader`<>0 
    AND sm.`TeamLeader`='.$tl.') AND (TLTS<=\''.$res0['TaggedAsOf'].'\') group by sm.TxnID;
'; 
    $columnnames=array('Date', 'Branch', 'SaleNo', 'Client', 'CashSalesLessReturns');
    $subtitle='Cash Sales'; $showgrandtotal=true; $coltototal='CashSalesLessReturnsAmount';
    include('../backendphp/layout/displayastablenosort.php');
    
    require ('calctargets.php');   
    
    $sql='SELECT Cleared AS DateCleared, Branch, ForChargeInvNo AS SaleNo, ClientName AS Client, Amount, FORMAT(Amount,2) AS ClearedCollections  FROM `InvoicesPaid` ip JOIN `1clients` c ON c.ClientNo=ip.ClientNo JOIN `1branches` b ON b.BranchNo=ip.BranchNo WHERE ip.TeamLeader='.$tl;
    
    $columnnames=array('DateCleared', 'Branch', 'SaleNo', 'Client', 'ClearedCollections');
    $subtitle='<br/><br/>Cleared Collections'; $showgrandtotal=true; $coltototal='Amount';
    include('../backendphp/layout/displayastableonlynoheaders.php');
    
   break;

// for team leaders
case 'SalesAcrossBranches':
if (!allowedToOpen(727,'1rtc')) {   echo 'No permission'; exit;}


$columnnamesleft=array('Branch','Date','Team_Leader','SaleNo','ClientName','Amount');
$columnnamesright=array('Branch','Date','Team_Leader','SaleNo','ClientName','Amount');

if (allowedToOpen(7271,'1rtc')){
    $conditionleft=' TeamLeader not in (Select TeamLeader from `attend_1branchgroups` where BranchNo=sm.BranchNo) and '; $conditionright='';
} else { //team leader
     $conditionleft=' TeamLeader<>'.$_SESSION['(ak0)'].'  and sm.BranchNo in (Select BranchNo from `attend_1branchgroups` where TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') and ';
     $conditionright=' TeamLeader='.$_SESSION['(ak0)'].'  and sm.BranchNo in (Select BranchNo from `attend_1branchgroups` where TeamLeader<>'.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') and ';
} 

$pagetouse='lookupgeninv.php?w='.$whichqry;
$fieldname='Month'; $showbranches=false;

?>
<form method="post" action="<?php echo $pagetouse; ?>" enctype="multipart/form-data">
                Choose Month (1 - 12):  <input type="text" name="<?php echo $fieldname; ?>" value="<?php echo date('m'); ?>"></input>
<input type="submit" name="lookup" value="Lookup"> </form>
<br><br>
<?php
if (!isset($_REQUEST[$fieldname])){
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.date('m').'-1')).'<br>';   
$txndate=str_pad(date('m'),2,'0',STR_PAD_LEFT);
} else {
$formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$_POST[$fieldname].'-1')).'<br>';   
$txndate=str_pad($_REQUEST[$fieldname],2,'0',STR_PAD_LEFT);
}
$title='Sales Across Branches';
$lefttabletitle='<h3>Others\' Sales in MY Branches</h3>';
$sqlleft='select  b.Branch, `Date`, Nickname as Team_Leader, SaleNo, ClientName, Round(Sum(`Qty` * `UnitPrice`),0) as Amount
    from (`invty_2sale` sm
        join `invty_2salesub` ss ON ((`sm`.`TxnID` = `ss`.`TxnID`))
		join `1employees` e on e.IDNo=sm.TeamLeader
		join `1branches` b on b.BranchNo=sm.BranchNo
		join `1clients` c on c.ClientNo=sm.ClientNo
		join `attend_30currentpositions` p on p.IDNo=sm.TeamLeader
)  where '.$conditionleft.' Month(sm.`Date`)='.$txndate.' and p.PositionID IN (35,36) group by sm.TxnID order by b.Branch, sm.`Date`;';

$righttabletitle='<h3>MY Sales in Others\' Branches</h3>';
$sqlright='select  b.Branch, `Date`, Nickname as Team_Leader, SaleNo, ClientName, Round(Sum(`Qty` * `UnitPrice`),0) as Amount
    from (`invty_2sale` sm
        join `invty_2salesub` ss ON ((`sm`.`TxnID` = `ss`.`TxnID`))
		join `1employees` e on e.IDNo=sm.TeamLeader
		join `1branches` b on b.BranchNo=sm.BranchNo
		join `1clients` c on c.ClientNo=sm.ClientNo
		join `attend_30currentpositions` p on p.IDNo=sm.TeamLeader
)  where '.$conditionright.' Month(sm.`Date`)='.$txndate.' and p.PositionID IN (35,36) group by sm.TxnID order by b.Branch, sm.`Date`;';

if (allowedToOpen(7271,'1rtc')){
    $sql=$sqlleft; $columnnames=$columnnamesleft;    include('../backendphp/layout/displayastable.php');
} else { //team leader
     include('../backendphp/layout/twotablessidebyside.php');
}


   break;
   
// Year
case 'YrScores':
if (!allowedToOpen(734,'1rtc')) {   echo 'No permission'; exit;}
 
$formdesc='<ul><li><li> Year scores will be actual Cash Sales + Collected (beyond terms or not, for current yr sales only)</li>
 <li> No doubling of AROld and no deduction of uncollected.  </li>
 <li> Purpose is to see if we actually met target in terms of collected Sales.</li>
 <li> Target for new branches (< 4 months) are <u>not</u> deducted.</li>
 <li> Total annual target is fixed value set at beginning of the year. (It is <I>NOT a total of all branches.)</I></li>
 <li> Data is fixed from "Target Reached?" page.</li></ul>';
 
 $columnnames=array('Branch','CashSales','ClearedCollections','Net','YrTarget','YrScore');

$pagetouse='lookupgeninv.php?w='.$whichqry;
$title='Year Target Scores';
$method='GET';
$width='60%';
// require ('calctargetsyr.php');
$subtitle='Branches';
$sql0='CREATE TEMPORARY TABLE `BranchYrTargets` AS
    SELECT yt.BranchNo, (`yt`.`01`+`yt`.`02`+`yt`.`03`+`yt`.`04`+`yt`.`05`+`yt`.`06`+`yt`.`07`+`yt`.`08`+`yt`.`09`+`yt`.`10`+`yt`.`11`+`yt`.`12`) AS YrTarget 
    FROM `acctg_1yearsalestargets` yt;';
$stmt=$link->prepare($sql0);$stmt->execute();
// branch scores
$sql='SELECT `Branch`,FORMAT(sum(`CashSales`),0) as CashSales, FORMAT(sum(`ClearedCollections`),0) as ClearedCollections, FORMAT(SUM(`Net`),0) AS Net, FORMAT(YrTarget,0) AS YrTarget, CONCAT(FORMAT((SUM(`Net`)/YrTarget)*100,2),"%") as YrScore, (SUM(`Net`)/YrTarget) AS YrScoreValue FROM acctg_6targetscores ts join 1branches b on b.BranchNo=ts.BranchNo join BranchYrTargets yst on yst.BranchNo=ts.BranchNo WHERE MonthNo>0 group by Branch ORDER BY YrScoreValue DESC';

// $txnid='BranchNo';  
   include('../backendphp/layout/displayastable.php');
$title='';

// overall score
$thisyr=$currentyr;
$subtitle='';
$columnnames=array('Total','CashSales','ClearedCollections','Net','YrTarget','YrScore','%YrToDate'); 
$sql='SELECT "'.$thisyr.'" AS Total, FORMAT(SUM(`CashSales`),0) as `CashSales`, FORMAT(SUM(`ClearedCollections`),0) as `ClearedCollections`, FORMAT(SUM(`CashSales`)+SUM(`ClearedCollections`),0) as `Net`, FORMAT((SELECT `FixedYrTarget` FROM `00dataclosedby` WHERE `ForDB`=1),0) AS YrTarget,
CONCAT(FORMAT((SUM(`CashSales`)+SUM(`ClearedCollections`))/(SELECT `FixedYrTarget` FROM `00dataclosedby` WHERE `ForDB`=1)*100,2),"%") as YrScore , CONCAT(FORMAT(('.(date('Y')==$thisyr?date('z'):'365').'/365)*100,2),"%") AS `%YrToDate`
FROM acctg_6targetscores ts WHERE MonthNo>0 AND BranchNo<1000;';
   $hidecount=true; unset($width);
include('../backendphp/layout/displayastablenosort.php');

   break;

case 'YrTargets':
if (!allowedToOpen(735,'1rtc')) {    echo 'No permission'; exit;}

$formdesc='</i><br><a href="lookupgeninv.php?w=UploadYearTargets">Upload Year Targets</a><i>';

$columnnames=array('Branch','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

$pagetouse='lookupgeninv.php?w='.$whichqry;
$title='Year Targets in \'000s';
$method='GET';
$showbranches=false;

$sql='SELECT bt.BranchNo,Branch,format(`01`/1000,0) as Jan,format(`02`/1000,0) as Feb, format(`03`/1000,0) as Mar, format(`04`/1000,0) as Apr, format(`05`/1000,0) as May, format(`06`/1000,0) as Jun, format(`07`/1000,0) as Jul, format(`08`/1000,0) as Aug, format(`09`/1000,0) as Sep, format(`10`/1000,0) as Oct, format(`11`/1000,0) as Nov, format(`12`/1000,0) as `Dec` FROM acctg_1yearsalestargets bt join `1branches` b on b.BranchNo=bt.BranchNo '.(allowedToOpen(7351,'1rtc')? ' JOIN `attend_1branchgroups` bg ON b.BranchNo=bg.BranchNo WHERE bg.SAM='.$_SESSION['(ak0)']:'').' order by `b`.`Branch`';

// if (allowedToOpen(1700,'1rtc')) { 
if (allowedToOpen(73522,'1rtc')) { 
    $whichdata='default'; include '../backendphp/functions/monthsarray.php'; $sqltotal='';
    foreach ($months as $fsmonth){ $sqltotal.='SUM(IFNULL(`'.str_pad($fsmonth,2,'0',STR_PAD_LEFT).'`,0))+';}
$sqltotal='SELECT '.$sqltotal.'0 AS Total FROM acctg_1yearsalestargets'; $stmttotal=$link->query($sqltotal); $restotal=$stmttotal->fetch();
include_once('../backendphp/functions/getfixedyrtarget.php'); //gets $targetthisyear
$totalstext='Calculated Target Total : '.number_format($restotal['Total'],0).'<br>Set Target this Year : '.  number_format($targetthisyear);
 $editprocesslabel='Edit'; $editprocess='lookupgeninv.php?w=YrTargetsEditSpecs&BranchNo='; $txnidname='BranchNo';
}
$width='70%';
   include('../backendphp/layout/displayastable.php');    
   break; 
 
case 'UploadYearTargets':

        $title='Upload Year Targets';
        $colnames=array('branchno','`01`','`02`','`03`','`04`','`05`','`06`','`07`','`08`','`09`','`10`','`11`','`12`');
        $requiredcol=array('branchno','`01`','`02`','`03`','`04`','`05`','`06`','`07`','`08`','`09`','`10`','`11`','`12`');
        $required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
        $specific_instruct='<br/>'
                . '<i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='acctg_1yearsalestargets'; $firstcolumnname='branchno';
        $DOWNLOAD_DIR="../../uploads/"; ; $requireencodedby=true; $requiredts=true;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="lookupgeninv.php?w=YrTargets" target="_blank">Lookup Newly Imported Data</a>';}

break;
   
case 'YrTargetsEditSpecs':
    if (!allowedToOpen(73522,'1rtc')) {   header("Location:".$_SERVER['HTTP_REFERER']);}
    $txnidname='BranchNo'; $txnid=intval($_GET['BranchNo']); $title='Edit Target';
    $sql='SELECT b.Branch, bt.* FROM acctg_1yearsalestargets bt join `1branches` b on b.BranchNo=bt.BranchNo WHERE bt.BranchNo='.$txnid;
    $editprocess='lookupgeninv.php?w=YrTargetsEdit&BranchNo='.$txnid;
    $columnnames=array('Branch','01','02','03','04','05','06','07','08','09','10','11','12');
    $columnstoedit=$columnnames;
    include('../backendphp/layout/editspecificsforlists.php');
    break;
    
case 'YrTargetsEdit':
    if (allowedToOpen(73522,'1rtc')) {  
        $columnstoedit=array('01','02','03','04','05','06','07','08','09','10','11');
        $sql='';
        foreach ($columnstoedit as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `acctg_1yearsalestargets` SET '.$sql.' `12`='.addslashes($_REQUEST['12']).' WHERE BranchNo='.intval($_GET['BranchNo']); 
        $stmt=$link->prepare($sql); $stmt->execute();
    }
    header("Location:lookupgeninv.php?w=YrTargets");
    break;

   
  
  
case 'SetasCharge': 
$title='Change Payment Type';;
$txnid='TxnID';
if (allowedToOpen(728,'1rtc')) {
   $autoprocess='../acctg/pracctgAR.php?w='.$whichqry.'&TxnID=';
   $autoprocesslabel='Change_PayType';
   $editprocess='editinvspecifics.php?edit=2&w=SaleMainEdit&txntype=2&TxnID=';$editprocesslabel='Edit';
} 

$sql='select s.*, if(PaymentType=1,"Cash","Charge") as PaymentType, c.ClientName from invty_2sale s
join `1clients` c on c.ClientNo=s.ClientNo
where txntype=2 and Date>(Select DataClosedBy from `00dataclosedby` where ForDB=1) and s.BranchNo='.$_SESSION['bnum'];

    $columnnames=array('Date','SaleNo','ClientName','PaymentType');
    include_once('../backendphp/layout/displayastablewithedit.php');

break;

case 'ChangeTeamLeader': 
$title='Change Team Leader Per Invoice';
$txnid='TxnID';
if (allowedToOpen(719,'1rtc')) {   $editprocess='praddsale.php?w=ChangeTeamLeader&TxnID=';   $editprocesslabel='Change';} 
?><br>
<form method=post action='lookupgeninv.php?w=ChangeTeamLeader'>
Choose Month (1 - 12):  <input type="text" name="month" value="<?php echo date('m'); ?>"></input>
<input type=submit name='submit' value='Lookup'>
</form>
<?php
if (!isset($_POST['month'])){
$month=date('m');
} else {
$month=$_POST['month'];
}
$sql='select  `sm`.`TxnID`,`Date`, Nickname as Team_Leader, TeamLeader as TeamLeaderID, SaleNo, ClientName, Round(Sum(`Qty` * `UnitPrice`),0) as Amount, Posted
    from (`invty_2sale` sm
        join `invty_2salesub` ss ON ((`sm`.`TxnID` = `ss`.`TxnID`))
		left join `1employees` e on e.IDNo=sm.TeamLeader
		join `1clients` c on c.ClientNo=sm.ClientNo
		left join `attend_30currentpositions` p on p.IDNo=sm.TeamLeader
)  where  Month(sm.`Date`)='.$month.' and sm.BranchNo='.$_SESSION['bnum'].' group by sm.TxnID order by sm.`Date`, sm.SaleNo;';

    $columnnames=array('Date','SaleNo','ClientName','Amount','Posted','Team_Leader','TeamLeaderID');
    $columnstoedit=array('TeamLeaderID');
    include_once('../backendphp/layout/displayastableeditcells.php');

break;

case 'Turnover': 
$title='Turnover Rate Per Branch';
$formdesc='Qty Sold / Endinv Invty';
$txnid='TxnID';
if (!allowedToOpen(732,'1rtc')) {    echo 'No permission'; exit;} 
$from=!isset($_POST['frommonth'])?date('m'):($_POST['frommonth']);
$to=!isset($_POST['tomonth'])?date('m'):($_POST['tomonth']);
?>
<form method=post action='lookupgeninv.php?w=Turnover'>
From month (1-12)<input type=text size=2 name='frommonth' value="<?php echo $from; ?>"> To <input type=text size=2 name='tomonth' value="<?php echo $to; ?>">
<input type=submit name='perbranch' value='Per Branch'>&nbsp;&nbsp;&nbsp;<input type=submit name='all' value='All Branches'>
</form>
<?php
if (!isset($_POST['frommonth'])){
goto noform;
} else {
    $columnnames=array();
    if(!isset($_POST['perbranch'])){
        $title='Turnover Rate - ALL';
        $condition=''; $groupby=''; $branch=''; $joinbranch=''; $joinbranchendinv='';
        $sql0='CREATE TEMPORARY TABLE endinv AS SELECT `a`.`ItemCode`, SUM(`a`.`Qty`) AS `EndInvToday` FROM `invty_20uniallposted` `a` 
            WHERE ((`a`.`Date` IS NOT NULL) AND (`a`.`Date` <= NOW())) GROUP BY `a`.`ItemCode`';
        $stmt=$link->prepare($sql0); $stmt->execute();
        $endinvqry='endinv';
    } else {
        $condition='m.BranchNo='.$_SESSION['bnum'].' and '; $columnnames[]='Branch';
        $groupby=' m.BranchNo, '; $branch=' b.Branch AS Branch, '; $joinbranch=' join `1branches` b on b.BranchNo=m.BranchNo ';
        $joinbranchendinv=' end.BranchNo=m.BranchNo and '; $endinvqry='invty_21endinv';
    }
    
    
$sql0='create temporary table turnover (
BranchNo smallint(6) not null,
ItemCode smallint(6) not null,
Sold double not null
)
select m.BranchNo, s.ItemCode, TRUNCATE(sum(s.Qty),2) as Sold from invty_2sale m join invty_2salesub s on m.TxnID=s.TxnID where '.$condition.' (Month(m.Date)>='.$_POST['frommonth'].' and Month(m.Date)<='.$_POST['tomonth'].') group by '.$groupby.' s.ItemCode';
//echo $sql0; break;
$stmt=$link->prepare($sql0); $stmt->execute();
$sql='select m.*, TRUNCATE(end.EndInvToday,2) AS EndInvToday, i.ItemDesc,c.Category, '.$branch.' truncate(Sold/EndInvToday,2) as Turnover from turnover m join invty_1items i on i.ItemCode=m.ItemCode join invty_1category c on c.CatNo=i.CatNo '.$joinbranch.' join '.$endinvqry.' end on '.$joinbranchendinv.' end.ItemCode=m.ItemCode order by Sold/EndInvToday desc';
}
    array_push($columnnames,'ItemCode','Category','ItemDesc','Sold','EndInvToday','Turnover');
    include_once('../backendphp/layout/displayastable.php');

break;

case 'CompareTurnover': 
$title='Comparative Turnover Rates';
$formdesc='Qty Sold / Endinv Invty';
$showbranches=false;
$txnid='TxnID';
if (!allowedToOpen(720,'1rtc')) {    echo 'No permission'; exit;} 
?>
<form method=post action='lookupgeninv.php?w=CompareTurnover'>
From month (1-12)<input type=text size=2 name='frommonth'> To <input type=text size=2 name='tomonth'>
<?php
include('invlayout/choosecat.php');
?>
<input type=submit name='submit' value='Lookup'>
</form>
<?php
if (!isset($_POST['Category'])){
goto noform;
} else {
$catid=getValue($link,'invty_1category','Category',$_POST['Category'],'CatNo');
$sql0='create temporary table totalsold (
BranchNo smallint(6) not null,
ItemCode smallint(6) not null,
Sold double not null
)
select m.BranchNo, s.ItemCode, sum(s.Qty) as Sold from invty_2sale m join invty_2salesub s on m.TxnID=s.TxnID
join invty_1items i on i.ItemCode=s.ItemCode where i.CatNo='.$catid.' and (Month(m.Date)>='.$_POST['frommonth'].' and Month(m.Date)<='.$_POST['tomonth'].') group by m.BranchNo, s.ItemCode';
$stmt=$link->prepare($sql0);
$stmt->execute();
$sql='select t.*, end.EndInvToday, i.ItemDesc,c.Category,b.Branch, truncate(Sold/EndInvToday,2) as Turnover from totalsold t join invty_1items i on i.ItemCode=t.ItemCode join invty_1category c on c.CatNo=i.CatNo join `1branches` b on b.BranchNo=t.BranchNo join invty_21endinv end on end.BranchNo=t.BranchNo and end.ItemCode=t.ItemCode order by i.ItemCode asc, Sold/EndInvToday desc';
}
    $columnnames=array('Branch','ItemCode','Category','ItemDesc','Sold','EndInvToday','Turnover');
    include_once('../backendphp/layout/displayastable.php');

break;

case 'ClassPerQuarter':
if (!allowedToOpen(730,'1rtc')) {   echo 'No permission'; exit;}

if(date('Y')==$currentyr AND date('m')<=3){
	$columnnames=array('BranchNo','Branch','ClassLastYr','Sale1Q','Target1Q','MonthlyAve1Q','Class1Q');
	
} else if(date('Y')==$currentyr AND date('m')>=4 AND date('m')<=6){
	$columnnames=array('BranchNo','Branch','ClassLastYr','Sale1Q','Target1Q','MonthlyAve1Q','Class1Q','Sale2Q','Target2Q','MonthlyAve2Q','Class2Q');
} else if(date('Y')==$currentyr AND date('m')>=7 AND date('m')<=9){
	$columnnames=array('BranchNo','Branch','ClassLastYr','Sale1Q','Target1Q','MonthlyAve1Q','Class1Q','Sale2Q','Target2Q','MonthlyAve2Q','Class2Q','Sale3Q','Target3Q','MonthlyAve3Q','Class3Q');
}
else {
	$columnnames=array('BranchNo','Branch','ClassLastYr','Sale1Q','Target1Q','MonthlyAve1Q','Class1Q','Sale2Q','Target2Q','MonthlyAve2Q','Class2Q','Sale3Q','Target3Q','MonthlyAve3Q','Class3Q','Sale4Q','Target4Q','MonthlyAve4Q','Class4Q');
}


if (!allowedToOpen(array(7302,7306),'1rtc')){
	$condition='';
}  else {
   if (allowedToOpen(7302,'1rtc')){
		$condition=' AND ts.BranchNo in (Select g.BranchNo from `attend_1branchgroups` g where TeamLeader='.$_SESSION['(ak0)'].' OR SAM='.$_SESSION['(ak0)'].') ';
	} else {
		 $condition=' AND ts.BranchNo='.$_SESSION['bnum'] ;
	}
	
	 if (!allowedToOpen(2013,'1rtc')){ // Not SAM's , STLs-Stores
		$columnnames=array_diff($columnnames,array('Sale1Q','Target1Q','MonthlyAve1Q','Sale2Q','Target2Q','MonthlyAve2Q','Sale3Q','Target3Q','MonthlyAve3Q','Sale4Q','Target4Q','MonthlyAve4Q'));
    }
}



	//ClassID: 2  - Growth 3 - Prime 4 - Mature
	$checkclass='';
	
	$quarters=array(1,2,3,4); $count=1;
	foreach($quarters AS $quarter){
		$checkclass.='(CASE 
		WHEN (SELECT MonthlyAve'.$quarter.'QValue)<(SELECT CutOffMin FROM 0branchclass WHERE ClassID='.($count+1).') THEN "Seed" 
		WHEN (SELECT MonthlyAve'.$quarter.'QValue)>=(SELECT CutOffMin FROM 0branchclass WHERE ClassID='.($count+1).') AND (SELECT MonthlyAve'.$quarter.'QValue)<(SELECT CutOffMin FROM 0branchclass WHERE ClassID='.($count+2).') THEN "Growth" 
		WHEN (SELECT MonthlyAve'.$quarter.'QValue)>=(SELECT CutOffMin FROM 0branchclass WHERE ClassID='.($count+2).') AND (SELECT MonthlyAve'.$quarter.'QValue)<(SELECT CutOffMin FROM 0branchclass WHERE ClassID='.($count+3).') THEN "Prime" 
		WHEN (SELECT MonthlyAve'.$quarter.'QValue)>=(SELECT CutOffMin FROM 0branchclass WHERE ClassID='.($count+3).') THEN "Mature" END) AS Class'.$quarter.'Q,';
	}
	
	
	$title='Class Per Quarter';
	$sql='SELECT b.BranchNo,Class AS ClassLastYr,
	
	IFNULL((SELECT TRUNCATE(SUM(Net),2) FROM acctg_6targetscores WHERE BranchNo=b.BranchNo AND MonthNo IN (1,2,3)),0) AS Sale1QValue,
	(SELECT FORMAT(Sale1QValue,0)) AS Sale1Q,
	IFNULL((SELECT TRUNCATE(SUM(`01`+`02`+`03`),2) FROM acctg_1yearsalestargets WHERE BranchNo=b.BranchNo),0) AS Target1QValue,
	(SELECT FORMAT(Target1QValue,0)) AS Target1Q,
	(SELECT TRUNCATE((Sale1QValue/3),2)) AS MonthlyAve1QValue,
	(SELECT FORMAT(MonthlyAve1QValue,0)) AS MonthlyAve1Q,
	
	IFNULL((SELECT TRUNCATE(SUM(Net),2) FROM acctg_6targetscores WHERE BranchNo=b.BranchNo AND MonthNo IN (4,5,6)),0) AS Sale2QValue,
	(SELECT FORMAT(Sale2QValue,0)) AS Sale2Q,
	IFNULL((SELECT TRUNCATE(SUM(`04`+`05`+`06`),2) FROM acctg_1yearsalestargets WHERE BranchNo=b.BranchNo),0) AS Target2QValue,
	(SELECT FORMAT(Target2QValue,0)) AS Target2Q,
	(SELECT TRUNCATE((Sale2QValue/3),2)) AS MonthlyAve2QValue,
	(SELECT FORMAT(MonthlyAve2QValue,0)) AS MonthlyAve2Q,
	
	IFNULL((SELECT TRUNCATE(SUM(Net),2) FROM acctg_6targetscores WHERE BranchNo=b.BranchNo AND MonthNo IN (7,8,9)),0) AS Sale3QValue,
	(SELECT FORMAT(Sale3QValue,0)) AS Sale3Q,
	IFNULL((SELECT TRUNCATE(SUM(`07`+`08`+`09`),2) FROM acctg_1yearsalestargets WHERE BranchNo=b.BranchNo),0) AS Target3QValue,
	(SELECT FORMAT(Target3QValue,0)) AS Target3Q,
	(SELECT TRUNCATE((Sale3QValue/3),2)) AS MonthlyAve3QValue,
	(SELECT FORMAT(MonthlyAve3QValue,0)) AS MonthlyAve3Q,
	
	IFNULL((SELECT TRUNCATE(SUM(Net),2) FROM acctg_6targetscores WHERE BranchNo=b.BranchNo AND MonthNo IN (10,11,12)),0) AS Sale4QValue,
	(SELECT FORMAT(Sale4QValue,0)) AS Sale4Q,
	IFNULL((SELECT TRUNCATE(SUM(`10`+`11`+`12`),2) FROM acctg_1yearsalestargets WHERE BranchNo=b.BranchNo),0) AS Target4QValue,
	(SELECT FORMAT(Target4QValue,0)) AS Target4Q,
	(SELECT TRUNCATE((Sale4QValue/3),2)) AS MonthlyAve4QValue,
	(SELECT FORMAT(MonthlyAve4QValue,0)) AS MonthlyAve4Q,
	
	'.$checkclass.'Branch
	

	FROM 1branches b JOIN acctg_6targetscores ts ON b.BranchNo=ts.BranchNo JOIN 0branchclass bc ON b.ClassLastYr=bc.ClassID WHERE 1=1 '.$condition.' GROUP BY BranchNo ORDER BY Branch;';
	
	$sql0='SELECT `TimeStamp` FROM acctg_6targetscores WHERE DisplayType=5 LIMIT 1;';
	$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
	$formdesc='As of '.$res0['TimeStamp'];

    include_once('../backendphp/layout/displayastablenosort.php');
break;
 
default:
    header("Location:".$_SERVER['HTTP_REFERER']);
}
noform:
    
	
	
		
     $link=null; $stmt=null; $stmt0=null;
?>