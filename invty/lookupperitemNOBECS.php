<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed 
$allowed=array(747,748,749,6053); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check

$whichqry=$_GET['w'];
if(!(isset($_GET['NotSessionBranch']))){
	if($whichqry=='Cost_List_Per_Item' OR $whichqry=='ItemsWtAveCostPL'){
		$showbranches=false;
	} else {
		$showbranches=true;
	}
	
	include_once('../switchboard/contents.php');
} else {
	$hidecontents=1;
}

include_once "../generalinfo/lists.inc";

$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;


switch ($whichqry){
case 'Item_Activity':

    if (!allowedToOpen(748,'1rtc')) {   echo 'No permission'; exit;}
		if(isset($_GET['NotSessionBranch'])){
			$_POST['itemcode']=$_GET['ItemCode'];
			$itemcode=$_POST['itemcode'];
			$defectcondi=' AND Defective<>0';
			include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
			 $title='Branch No. '.$_GET['BranchNo'].': '.comboBoxValue($link,'1branches','BranchNo',$_GET['BranchNo'],'Branch').'  Item Activity (Defective)';
			
		} else {
			$defectcondi='';
			 $title='Branch No. '.$_SESSION['bnum'].': '.$_SESSION['@brn'].'  Item Activity';
			
		}

    //$formdesc='This shows static data unless Supply Chain or Audit updates underlying table.';
    // $showbranches=true;

if (allowedToOpen(7481,'1rtc')) { $supplierincluded='gen_info_0unisupplierclientforitemact';} else {   $supplierincluded='gen_info_0unibranchclientforitemact';}
if(!isset($_GET['NotSessionBranch'])){
	include('invlayout/choosecatanditem.php');
}

if (isset($itemcode)){

    include('maketables/getasofmonth.php');
    include('maketables/createitemact.php');
        // $sql='SELECT a.*,s.SupplierName as `Supplier/Client/Branch`,if(Defective=1,"D",if(Defective=2,"DC","G")) as DefectiveOrGood FROM ItemAct a LEFT JOIN `'.$supplierincluded.'` s on a.SupplierNo=s.SupplierNo WHERE a.Date IS NOT NULL;';
        $sql='SELECT a.*,s.SupplierName as `Supplier/Client/Branch`,if(Defective=1,"D",if(Defective=2,"DC","G")) as DefectiveOrGood, MONTH(Date) AS `Month` FROM ItemAct a LEFT JOIN `'.$supplierincluded.'` s on a.SupplierNo=s.SupplierNo WHERE a.Date IS NOT NULL '.$defectcondi.';';
	$columnnames=array('Date', 'From', 'Number', 'Supplier/Client/Branch', 'Qty', 'UnitPrice', 'SerialNo', 'DefectiveOrGood', 'ActRemarks');
    
    $coltototal='Qty';$runtotal=true;
    //$showtotals=true; 
    $sql1='SELECT SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,Sum(Qty) as EndInvToday FROM ItemAct;';
    $stmt1=$link->query($sql1); $res1=$stmt1->fetch();

    $totalstext=((!isset($_GET['ItemCode']))?'Ending Invty: '.$res1['EndInvToday'].str_repeat('&nbsp',5).'Good Items: '.$res1['GoodItem'].str_repeat('&nbsp',3).'':'').'Defective: '.$res1['Defective'];
    if(isset($titleadd)) {$subtitle=$titleadd;}
    $changecolorfield='Month';
     include('../backendphp/layout/displayastablenosort.php');
    // include('invlayout/displayastableinv.php');
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
     $sql0='DROP TEMPORARY TABLE IF EXISTS ItemAct'; $stmt0=$link->prepare($sql0); $stmt0->execute(); 
  }
  break;
  
case 'LatestUnitPrice':
    if (!allowedToOpen(749,'1rtc')) {   echo 'No permission'; exit;}
    $title='Selling Prices';
	echo '<title>'.$title.'</title>';
	if (allowedToOpen(7496,'1rtc')) {
		include_once('../backendphp/layout/linkstyle.php');
		echo '<br><a id="link" href="branchpricelevels.php">Branch Price Levels</a><br>';
	}
	
	include('invlayout/choosecat.php');
echo '</form><br>';
if ((isset($_POST['Category'])) OR (isset($_SESSION['CatNo']))){
	if(isset($_POST['Category'])){
		$catno=getValue($link,'invty_1category','Category',$_POST['Category'],'CatNo');
		$_SESSION['Category']=$_POST['Category'];
		$_SESSION['CatNo']=$catno;
	} 
	$catno=$_SESSION['CatNo'];
    $category=$_SESSION['Category'];
    $colorcount=0;
	$rcolor[0]="FFFFCC";
	$rcolor[1]="FFFFFF";
		
        if (allowedToOpen(array(7491,7495),'1rtc')){
		
	$sql1='SELECT 
	(SELECT COUNT(PriceLevel1) FROM invty_5lastminprice lmp JOIN invty_54latestminpricestep1 lmps ON lmp.ItemCode=lmps.ItemCode AND lmp.Date=lmps.MaxOfDate JOIN invty_1items i ON lmp.ItemCode=i.ItemCode WHERE (PriceLevel1 IS NULL OR PriceLevel1=0)) AS cntpricelevel1,
	(SELECT COUNT(PriceLevel2) FROM invty_5lastminprice lmp JOIN invty_54latestminpricestep1 lmps ON lmp.ItemCode=lmps.ItemCode AND lmp.Date=lmps.MaxOfDate JOIN invty_1items i ON lmp.ItemCode=i.ItemCode WHERE (PriceLevel2 IS NULL OR PriceLevel2=0)) AS cntpricelevel2, 
	(SELECT COUNT(PriceLevel3) FROM invty_5lastminprice lmp JOIN invty_54latestminpricestep1 lmps ON lmp.ItemCode=lmps.ItemCode AND lmp.Date=lmps.MaxOfDate JOIN invty_1items i ON lmp.ItemCode=i.ItemCode WHERE (PriceLevel3 IS NULL OR PriceLevel3=0)) AS cntpricelevel3,
	(SELECT COUNT(PriceLevel4) FROM invty_5lastminprice lmp JOIN invty_54latestminpricestep1 lmps ON lmp.ItemCode=lmps.ItemCode AND lmp.Date=lmps.MaxOfDate JOIN invty_1items i ON lmp.ItemCode=i.ItemCode WHERE (PriceLevel4 IS NULL OR PriceLevel4=0)) AS cntpricelevel4,
	(SELECT COUNT(PriceLevel5) FROM invty_5lastminprice lmp JOIN invty_54latestminpricestep1 lmps ON lmp.ItemCode=lmps.ItemCode AND lmp.Date=lmps.MaxOfDate JOIN invty_1items i ON lmp.ItemCode=i.ItemCode WHERE (PriceLevel5 IS NULL OR PriceLevel5=0)) AS cntpricelevel5
	';
	$stmt1=$link->query($sql1); $result1=$stmt1->fetch();
	echo '<br><br><b>Number of Items with zero prices</b><br>';
	echo 'PriceLevel1: '.$result1['cntpricelevel1'].'<br>';
	echo 'PriceLevel2: '.$result1['cntpricelevel2'].'<br>';
	echo 'PriceLevel3: '.$result1['cntpricelevel3'].'<br>';
	echo 'PriceLevel4: '.$result1['cntpricelevel4'].'<br>';
	echo 'PriceLevel5: '.$result1['cntpricelevel5'].'<br><br>';
	
	echo '<br><h3>'.$category.'</h3>';
	if(isset($_GET['msg'])){
		echo'<center><b>Updated Successfully</b></center>';
	}
   	
		 $sql='SELECT lmp.*,ItemDesc, lc.UnitCost FROM `invty_5latestminprice` lmp JOIN invty_1items i ON lmp.ItemCode=i.ItemCode LEFT JOIN invty_52latestcost lc ON i.ItemCode=lc.ItemCode JOIN invty_1category c ON i.CatNo=c.CatNo WHERE i.CatNo='.$catno.';';
                 $columnnames=array('ItemCode','ItemDesc','UnitCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5');
                 
                 $txnidname='ItemCode';
                 if (allowedToOpen(array(7491),'1rtc')){ 
                     $columnstoedit=array('PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5');
                        $editprocess='lookupperitem.php?w=AddPriceLevel&ItemCode='; $editprocesslabel='Edit';
                     include('../backendphp/layout/displayastableeditcells.php'); 
                     
                 } else { include('../backendphp/layout/displayastablenosort.php');}
// echo $sql;
	} elseif (allowedToOpen(array(7492,7493),'1rtc')) { // sales office
      $sql='SELECT date_format(`Date`,\'%Y-%m-%d\') as `DateofPrice`, `PriceLevel1`, `PriceLevel2`, `PriceLevel3`, `PriceLevel4`, `PriceLevel5`, lmp.ItemCode, i.ItemDesc as Description, i.Unit FROM `invty_5latestminprice` lmp join `invty_1items` i on i.ItemCode=lmp.ItemCode WHERE i.CatNo='.$catno. ' order by `ItemCode`';
      if (allowedToOpen(7492,'1rtc')){ 
      $columnnames=array('ItemCode', 'Description', 'Unit', 'PriceLevel1', 'PriceLevel2', 'PriceLevel3', 'PriceLevel4', 'PriceLevel5', 'DateofPrice');
      } else { $columnnames=array('ItemCode', 'Description', 'Unit', 'PriceLevel2', 'PriceLevel3', 'PriceLevel4', 'PriceLevel5', 'DateofPrice');}
      
    $colwithcond='DateofPrice'; $tblcondition=''.$currentyr.'-01-02'; $condtype='date'; $colorneg='red'; // $colorpos='green';
    include('invlayout/displayastableinv.php');
    // end for Sales office
    
    } else { //branches, wh, stl etc
        include_once('invlayout/pricelevelcase.php');
        $sql='SELECT date_format(`Date`,\'%Y-%m-%d\') as `DateofPrice`, (SELECT 
						'.$plcase.'
					FROM `1branches` b1 where b1.BranchNo='.$_SESSION['bnum'].' AND b1.Pseudobranch IN (0,2)
				) AS UnitPrice, lmp.ItemCode, i.ItemDesc as Description, i.Unit FROM `invty_5latestminprice` lmp join `invty_1items` i on i.ItemCode=lmp.ItemCode WHERE i.CatNo='.$catno. ' order by `ItemCode`';
       $columnnames=array('ItemCode', 'Description', 'Unit', 'UnitPrice', 'DateofPrice');
      // echo $sql;
    $colwithcond='DateofPrice'; $tblcondition=''.$currentyr.'-01-02'; $condtype='date'; $colorneg='red'; // $colorpos='green';
    include('invlayout/displayastableinv.php');
    }
    
    
}
  break;
  
  case 'AddPriceLevel':
  if (!allowedToOpen(7491,'1rtc')){ echo 'No Permission'; exit(); }
  
 		?>
<style>
#table {
  border-collapse: collapse;
  font-size:10pt;
  padding: 5px;
  background-color:#FFFFCC;
}

#table td, #table th, #table tr {
  border: 1px solid black;
  padding: 5px;
}

#table tr:nth-child(even){background-color:#FFFFFF;}
</style>
<?php
 
  $ItemCode=intval($_GET['ItemCode']);
  
  $pl1=(empty($_POST['PriceLevel1'])?0:$_POST['PriceLevel1']);
  $pl2=(empty($_POST['PriceLevel2'])?0:$_POST['PriceLevel2']);
  $pl3=(empty($_POST['PriceLevel3'])?0:$_POST['PriceLevel3']);
  $pl4=(empty($_POST['PriceLevel4'])?0:$_POST['PriceLevel4']);
  $pl5=(empty($_POST['PriceLevel5'])?0:$_POST['PriceLevel5']);
  
  $sqlc='SELECT UnitCost FROM invty_52latestcost WHERE ItemCode='.$ItemCode;
	$stmtc=$link->query($sqlc); $resultcost=$stmtc->fetch(); $ucost=$resultcost['UnitCost'];
  
  if(($pl1<=$ucost) or ($pl2<=$ucost) or ($pl3<=$ucost) or ($pl4<=$ucost) or ($pl5<=$ucost)) { 
      echo '<br><br><h3 style="color: maroon; margin-left: 20%;">Check prices again. One is below or equal to cost.</h3>'; exit();}
        
        
  $sqlc='SELECT COUNT(ItemCode) AS cnt FROM invty_5lastminprice WHERE Date=CURDATE() AND ItemCode='.$ItemCode;
	$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
	
	if ($resultc['cnt']==0){
		
		//added condition
		$sqls='select ItemCode, ItemDesc from invty_1items where ItemCode=\''.$ItemCode.'\'';
		$stmts=$link->query($sqls); $results=$stmts->fetch();
		echo'</br><h3 style="display:inline;">Are you sure these are the correct prices?</h3>
		If Yes
			<form style=" display:inline;" method="POST" action="lookupperitem.php?w=AddPriceLevel&ItemCode='.$ItemCode.'">
			<input type="hidden" name="PriceLevel1" value="'.$pl1.'">
			<input type="hidden" name="PriceLevel2" value="'.$pl2.'">
			<input type="hidden" name="PriceLevel3" value="'.$pl3.'">
			<input type="hidden" name="PriceLevel4" value="'.$pl4.'">
			<input type="hidden" name="PriceLevel5" value="'.$pl5.'">
			<input type="hidden" name="c">
			<input type="submit" name="submit" value="Update">
			</form>
		If No <a href="lookupperitem.php?w=LatestUnitPrice">Back</a></br></br>
		<table id="table">
			<tr><th>ItemCode</th><th>ItemDesc</th><th>PriceLevel1</th><th>PriceLevel2</th><th>PriceLevel3</th><th>PriceLevel4</th><th>PriceLevel5</th></tr>
			<tr><td>'.$ItemCode.'</td><td>'.$results['ItemDesc'].'</td><td>'.$pl1.'</td><td>'.$pl2.'</td><td>'.$pl3.'</td><td>'.$pl4.'</td><td>'.$pl5.'</td></tr>
		</table>';
		if(!isset($_POST['c'])){exit();}
		//
		
	  $sql1='INSERT INTO invty_5lastminprice SET Date=CURDATE(),EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW(),PriceLevel1="'.$pl1.'",PriceLevel2="'.$pl2.'",PriceLevel3="'.$pl3.'",PriceLevel4="'.$pl4.'",PriceLevel5="'.$pl5.'",ItemCode='.$ItemCode.';';
	} else {
		//added condition
		$sqls='select ItemCode, ItemDesc from invty_1items where ItemCode=\''.$ItemCode.'\'';
		$stmts=$link->query($sqls); $results=$stmts->fetch();
		echo'</br><h3 style="display:inline;">Are you sure these are the correct prices?</h3>
		If Yes
			<form style=" display:inline;" method="POST" action="lookupperitem.php?w=AddPriceLevel&ItemCode='.$ItemCode.'">
			<input type="hidden" name="PriceLevel1" value="'.$pl1.'">
			<input type="hidden" name="PriceLevel2" value="'.$pl2.'">
			<input type="hidden" name="PriceLevel3" value="'.$pl3.'">
			<input type="hidden" name="PriceLevel4" value="'.$pl4.'">
			<input type="hidden" name="PriceLevel5" value="'.$pl5.'">
			<input type="hidden" name="c">
			<input type="submit" name="submit" value="Update">
			</form>
		If No <a href="lookupperitem.php?w=LatestUnitPrice">Back</a></br></br>
		<table id="table">
			<tr><th>ItemCode</th><th>ItemDesc</th><th>PriceLevel1</th><th>PriceLevel2</th><th>PriceLevel3</th><th>PriceLevel4</th><th>PriceLevel5</th></tr>
			<tr><td>'.$ItemCode.'</td><td>'.$results['ItemDesc'].'</td><td>'.$pl1.'</td><td>'.$pl2.'</td><td>'.$pl3.'</td><td>'.$pl4.'</td><td>'.$pl5.'</td></tr>
		</table>';
		if(!isset($_POST['c'])){exit();}
		//
		
		$sql1='UPDATE invty_5lastminprice SET EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW(),PriceLevel1="'.$pl1.'",PriceLevel2="'.$pl2.'",PriceLevel3="'.$pl3.'",PriceLevel4="'.$pl4.'",PriceLevel5="'.$pl5.'" WHERE ItemCode='.$ItemCode.';';
	}
  // echo $sql1; exit();
  	$stmt1=$link->prepare($sql1);
	$stmt1->execute();
  header("Location:lookupperitem.php?w=LatestUnitPrice&msg=1");
  
  break;

case 'DiscountedPrice':
   if (!allowedToOpen(743,'1rtc')) {   echo 'No permission'; exit;}
   echo 'Under construction'; exit();
include('invlayout/choosecat.php');
if (isset($_POST['Category'])){
   $catno=getValue($link,'invty_1category','Category',$_POST['Category'],'CatNo');
   // update list of repacked items
   // create temp table first
   $link=connect_db("".$currentyr."_1rtc",1);
   $sql1='create  table repackforlatestcost(
`Date` date 	NOT NULL,
ItemCode smallint(6)	NOT NULL,
Qty	double	NOT NULL default 0,
UnitCost	double	NOT NULL default 0,
BranchNo smallint(6)	NOT NULL
) 
   select  
        `tm`.`DateIN` AS `Date`, 
        `ts`.`ItemCode`, `ts`.`QtyReceived` as Qty, (lc.UnitCost/`ts`.`QtyReceived`) as UnitCost,
        `tm`.`BranchNo` AS `BranchNo`
    from
        `invty_2transfer` tm join `invty_2transfersub` ts ON `tm`.`TxnID` = `ts`.`TxnID`
        join `invty_1items` i ON `i`.`ItemCode` = `ts`.`ItemCode`
		join `invty_1itemsforrepack` r on `r`.`RepackItemCode`=`ts`.`ItemCode`
		join `invty_52latestcost` lc on `lc`.`ItemCode` = `r`.`BulkItemCode`
    where
        ((`ts`.`QtyReceived` <> 0) and (`i`.`CatNo` <> 1)  and (`tm`.`ToBranchNo` = `tm`.`BranchNo`));
';
$stmt1=$link->prepare($sql1);
$stmt1->execute();
// drop existing table; use admin permissions
   
   $sql0='drop table if exists invty_500repackforlatestcost';
   $stmt0=$link->prepare($sql0);
   $stmt0->execute();
// make the updated table
$sql1='
CREATE TABLE `invty_500repackforlatestcost` (
  `Date` date NOT NULL,
  `ItemCode` smallint(6) NOT NULL,
  `Qty` double NOT NULL DEFAULT 0,
  `UnitCost` double NOT NULL DEFAULT 0,
  `BranchNo` smallint(6) NOT NULL,
  KEY `repackcostidx` (`ItemCode`),
  KEY `repackbranchidx` (`BranchNo`)
)
   select  Date,ItemCode,Qty, UnitCost,BranchNo from repackforlatestcost;
';
$stmt1=$link->prepare($sql1);
$stmt1->execute();
//delete temp table
$sql0='drop table if exists repackforlatestcost';
   $stmt0=$link->prepare($sql0);
   $stmt0->execute();
// put back user permissions

   $sql='select dp.*, ItemDesc as Description, Unit  from invty_60discountedprices dp join `invty_1items` i on i.ItemCode=dp.ItemCode WHERE i.CatNo='.$catno;
   if (allowedToOpen(7431,'1rtc')){	 
	$columnnames=array('ItemCode', 'Description', 'Unit', 'UnitCost', 'MinPrice', 'DiscountedPrice', 'PMP', 'DiscountedPMP');
        } else { //grouphead & scmgr
      $columnnames=array('ItemCode', 'Description', 'Unit', 'MinPrice', 'DiscountedPrice', 'PMP', 'DiscountedPMP');
    }
    
     $title='Discounted Prices';
    include('../backendphp/layout/displayastable.php');
  }
  break;

case 'EndInvPerItemAllBranches':
    if (!allowedToOpen(745,'1rtc')) {   echo 'No permission'; exit;}
   $title='End Inv Per Item'; // $formdesc='This shows static data unless Supply Chain or Audit updates underlying table.';
   include('invlayout/choosecatanditem.php');
   if (!isset($_POST['itemcode'])){
      $sql='';
      goto noform;
   } else{
      $sql='Select lmp.ItemCode,CONCAT(ItemDesc,\' - \',Category) as Description,Date from `invty_5latestminprice` lmp join invty_1items i on i.ItemCode=lmp.ItemCode join invty_1category c on c.CatNo=i.CatNo where lmp.ItemCode='.$_POST['itemcode'];
      $stmt=$link->query($sql);
      $result=$stmt->fetch();
      $formdesc='Latest Date: '.date('Y-m-d',strtotime($result['Date'])).'<br>ItemCode: '.$result['ItemCode'].'<br>Description: '.$result['Description'].'';
 $sql0='CREATE TEMPORARY TABLE endinvperbranch as
SELECT a.BranchNo,b.Branch,a.ItemCode,i.ItemDesc as Description,SUM(CASE WHEN Defective<>1 THEN Qty END) as GoodItem, SUM(CASE WHEN Defective=1 THEN Qty END) as Defective,i.Unit,Sum(Qty) as EndInvToday FROM invty_20uniallposted as a join invty_1items i on i.ItemCode=a.ItemCode
join `1branches` b on a.BranchNo=b.BranchNo
where Date is not null and Date<=Now() and i.ItemCode='.$itemcode.' group by BranchNo,a.ItemCode order by b.Branch' ;    
  
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
    $sql='';
    $columnnames=array('Branch','GoodItem','Defective','Unit');
   
   $sql='Select * from endinvperbranch group by Branch,ItemCode';
   //echo $sql;
   }
    $showtotals=false; $width='50%';
    include('../backendphp/layout/displayastable.php');
   break;
case 'EndInvPerCatAllBranches':
    if (!allowedToOpen(744,'1rtc')) {   echo 'No permission'; exit;}
   $title='End Inv Per Category'; // $formdesc='This shows static data unless Supply Chain or Audit updates underlying table.';
   include('invlayout/choosecat.php');
   if (!isset($_POST['Category'])){
      $sql='';
      goto noform;
   } else{

$catno=getValue($link,'invty_1category','Category',$_POST['Category'],'CatNo');
    $sql0='CREATE TEMPORARY TABLE endinvperbranch (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Description	varchar(100)	NOT NULL,
Unit		varchar(10)	NOT NULL,
GoodItem	double	NOT NULL,
Defective	double	NOT NULL)
SELECT BranchNo,a.ItemCode,i.ItemDesc as Description,i.Unit,SUM(CASE WHEN Defective<>1 THEN Qty END) as GoodItem, SUM(CASE WHEN Defective=1 THEN Qty END) as Defective FROM invty_20uniallposted as a join invty_1items i on i.ItemCode=a.ItemCode left join invty_5latestminprice lmp on a.ItemCode=lmp.ItemCode where a.Date is not null and a.Date<=Now() and i.CatNo='.$catno.' group by BranchNo,a.ItemCode' ;    
  
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
    $sqlbranches='SELECT BranchNo,Branch from `1branches` where BranchNo not in (1,12) and BranchNo<99 order by Branch';
    $stmtbranch=$link->query($sqlbranches);
    $resultcount=$stmtbranch->rowCount();
    $resultbranch=$stmtbranch->fetchAll();
    $sql='';
    $columnnames=array('ItemCode', 'Description','Unit');
   for ($row = 0; $row <  $resultcount; $row++){
      $sql=$sql.'Max(Case when BranchNo='.$resultbranch[$row]['BranchNo'].' then GoodItem end) as `'.$resultbranch[$row]['Branch'].'GOOD`, Max(Case when BranchNo='.$resultbranch[$row]['BranchNo'].' then Defective end) as `'.$resultbranch[$row]['Branch'].'DEFECT`'.($row==($resultcount-1)?'':', ');
      $columnnames[]=$resultbranch[$row]['Branch'].'GOOD';
      $columnnames[]=$resultbranch[$row]['Branch'].'DEFECT';
   }
   $sql='Select ItemCode, Description, Unit, '. $sql.' from endinvperbranch group by ItemCode';
   //echo $sql;
   }
    $showtotals=false;
    include('../backendphp/layout/displayastable.php');
   break;
case 'Price_List_Per_Client':
   if (!allowedToOpen(750,'1rtc')) {   echo 'No permission'; exit;}
   
   
   //added condition
if(!isset($_POST['lookup'])){
	$_SESSION['taon']=1;
}else{
	if(isset($_POST['taon'])){
		if($_POST['taon']==0){	
			$_SESSION['taon']=0;
		}else{
			$_SESSION['taon']=1;
			}
	}
}
	if($_SESSION['taon']==1){
		$taon=$currentyr;
		$dbyr=''.$currentyr.'_1rtc.';
	}else{
		$taon=$lastyr;
		$dbyr=''.$lastyr.'_1rtc.';
	}
echo'<form style="display:inline" method="post" action="#">
			<input type="hidden" name="taon" value="'.($_SESSION['taon']==1?'0':'1').'">
			<input type="submit" name="lookup" value="'.($taon==$currentyr?$lastyr:$currentyr).'">
		</form>';
//

	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';   
   echo comboBox($link,'SELECT  ClientName,ClientNo FROM acctg_1clientsperbranch WHERE `BranchNo`='.$_SESSION['bnum'].' ORDER BY ClientName','ClientNo','ClientName','clients');
   // $calledfrom=10;
   // $listname='clients';
   // $liststoshow=array('clientswhole');
//           $listcaption='Client';
	   // $listcaption='Client Name';
	   $fieldname='ClientName';
	   // $table='1clients';
	   // $orderby='Category';
	  echo '<title>'.$taon.' Price List Per Client</title></br></br><h3>'.$taon.' Price List Per Client
</h3><form method="post" action="lookupperitem.php?w=Price_List_Per_Client">
				ClientName <input type="text" name="'.$fieldname.'" list="clients" size="60" autocomplete="off" required="true">
				<input type="submit" name="lookup" value="lookup">
			</form>';
	     	
	
   $title='';
   $lookupprocess='lookupperitem.php?w=Price_List_Per_Client';
   $c=1;
	 $selectmonth='';
	 $selectmonth2='';
	 while($c<=12){
	 $selectmonth.=',format(SUM(CASE when month(Date)=\''.$c.'\' then Qty end ),0) as  `'.$c.'`';
	 $selectmonth2.=',`'.$c.'`';
	 $c++;
	 }
   
 
   if (isset($_POST[$fieldname])){
	  $formdesc='</i> ClientName: &nbsp;'.$_POST[$fieldname].''; 
	$columnnames=array('Category','ItemCode', 'Description', 'UnitPrice', 'Unit','1','2','3','4','5','6','7','8','9','10','11','12','SaleDate','Branch');

   $clientno=getValue($link,'1clients',$fieldname,addslashes($_POST[$fieldname]),'ClientNo');
   $sql0='CREATE TEMPORARY TABLE latestpricestep1 as
SELECT Max(Date) as MaxofDate,m.ClientNo,ItemCode,BranchNo '.$selectmonth.' FROM '.$dbyr.'invty_2sale m join '.$dbyr.'invty_2salesub s on m.TxnID=s.TxnID where m.ClientNo='.$clientno.' group by ItemCode,BranchNo' ;
// echo $sql0; exit();

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  
  $sql0='CREATE TEMPORARY TABLE latestpriceperclient as
SELECT m.Date as SaleDate,m.ClientNo,c.ClientName,s.ItemCode,s.UnitPrice, b.Branch '.$selectmonth2.' FROM '.$dbyr.'invty_2sale m join '.$dbyr.'invty_2salesub s on m.TxnID=s.TxnID join latestpricestep1 lp on s.ItemCode=lp.ItemCode and m.ClientNo=lp.ClientNo and lp.BranchNo=m.BranchNo join '.$dbyr.'`1clients` c on c.ClientNo=m.ClientNo
join '.$dbyr.'`1branches` b on b.BranchNo=m.BranchNo
where m.ClientNo='.$clientno.' and m.Date=lp.MaxofDate Group By ItemCode,lp.BranchNo';
// echo $sql0; exit();
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
   $sql='Select lp.*,c.Category,i.ItemDesc as Description,i.Unit from latestpriceperclient lp join '.$dbyr.'invty_1items i on i.ItemCode=lp.ItemCode join '.$dbyr.'invty_1category c on c.CatNo=i.CatNo ORDER by Category ';
    include('../backendphp/layout/displayastable.php');
   }
  
   
   break;
case 'TotalSoldPerCat':
    if (!allowedToOpen(751,'1rtc')) {   echo 'No permission'; exit;}
    $subr=(!isset($_POST['subr']))?0:$_POST['subr']; 
   $title='Choose Category or Item<br>';
   if(!isset($_POST['fromdate'])){ $fromdate=date('Y-m-d',strtotime('02-01-'.$currentyr)); $todate=date('Y-m-t'); }
    else { $fromdate=$_REQUEST['fromdate']; $todate=$_REQUEST['todate']; }
   $areano='SELECT ServedByWH,Branch FROM 1branches where BranchNo='.$_SESSION['bnum'].'';
				$stmt=$link->query($areano);
				$result=$stmt->fetch();
   include_once('../generalinfo/lists.inc'); renderlist('categories');
   ?>
	<form action="#" method="POST" style="display: inline">
	Category <input type='text' name='Category' list='categories' size=40 autocomplete='off' value='<?php echo (isset($_POST['Category'])?$_POST['Category']:'');?>'>
        &nbsp; <i>or</i> Item Code (leave blank if per category) <input size="5" type='text' name='itemcode' autocomplete='off' value='<?php echo (isset($_POST['itemcode'])?$_POST['itemcode']:'');?>'>
         <input type="radio" name="subr" value="0"> Sold (All)
         <input type="radio" name="subr" value="1"> Sold (Branch)
		 <input type="radio" name="subr" value="4"> Sold (WareHouse) <input type="submit" value="Lookup"><br><br>
         From &nbsp<input type='date' name='fromdate' value="<?php echo $fromdate; ?>"></input>&nbsp &nbsp &nbsp 
            To &nbsp<input type='date' name='todate'value="<?php echo $todate; ?>" ></input>&nbsp &nbsp &nbsp &nbsp
         <input type="radio" name="subr" value="2">Sold & Paid (All)
         <input type="radio" name="subr" value="3">Sold & Paid (Branch)<input type="submit" value="Lookup (may take up to 1 minute)">
         </form><br><br>
        <?php    
   if (!isset($_POST['Category']) and !isset($_POST['itemcode'])){ goto noform;} 
   elseif (!isset($_REQUEST['itemcode']) or empty($_REQUEST['itemcode'])){ $catoritemno=' AND  i.CatNo='.getValue($link,'invty_1category','Category',$_REQUEST['Category'],'CatNo');}
   else { $catoritemno=' AND  i.ItemCode='.$_REQUEST['itemcode'];}   
   
   
    switch($subr){
        case 1:
            $title='Monthly Sold Per Category - '.$_SESSION['@brn']; $sqlcondition=' AND sm.BranchNo='.$_SESSION['bnum'];
            break;
        case 2:
            $title='Sold and Paid Per Category All Branches'; $sqlcondition='';
            break;
        case 3:
            $title='Sold and Paid Per Item - '.$_SESSION['@brn']; $sqlcondition=' AND sm.BranchNo='.$_SESSION['bnum'];
            break;
		case 4:
            $title='Monthly Sold Per Category - Total All Warehouse';$sqlcondition=' AND ServedByWH='.$result['ServedByWH'].' AND sm.BranchNo<>40';
            break;
        default: 
            $title='Monthly Sold Per Category - Total All Branches'; $sqlcondition='';
            break;
    }
  // $title.='<i>(Invty charges not counted)</i>';
   
if (in_array($subr,array(0,1,4))){ 
  
    $sql0='CREATE TEMPORARY TABLE totalsold (
ForMonth	tinyint(2)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Description	varchar(255) NOT NULL,
Unit		varchar(10)	NOT NULL,	
Purchased	double	NOT NULL,	
Sold	double	NOT NULL)
SELECT Month(sm.`Date`) as ForMonth,ss.ItemCode,CONCAT(c.Category," - ",i.ItemDesc) as Description,i.Unit, 0 AS Purchased, Sum(ss.Qty) AS Sold,ServedByWH
FROM invty_2sale as sm INNER JOIN invty_2salesub as ss ON sm.TxnID=ss.TxnID join invty_1items i on i.ItemCode=ss.ItemCode JOIN invty_1category c ON c.CatNo=i.CatNo join 1branches b on b.BranchNo=sm.BranchNo
where txntype<>3 '.$catoritemno.$sqlcondition.'
GROUP BY Month(sm.`Date`),ss.ItemCode';
// echo $sql0; exit();
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sqlmonth='SELECT ForMonth FROM totalsold GROUP BY ForMonth';
    $stmtmonth=$link->query($sqlmonth);
    $resultcount=$stmtmonth->rowCount();
    $resultmonth=$stmtmonth->fetchAll();
    $sql='';
    $columnnames=array('ItemCode', 'Description','Unit');
   for ($row = 0; $row <  $resultcount; $row++){
      $sql=$sql.' Max(Case when ForMonth='.$resultmonth[$row]['ForMonth'].' then Sold end) as \''.$resultmonth[$row]['ForMonth'].($row==($resultcount-1)?'\'':'\', ');
      $columnnames[]=$resultmonth[$row]['ForMonth'];
   }
   $sql='Select ts.ItemCode, ts.Description, ts.Unit, '. $sql.' from totalsold ts group by ts.ItemCode';
   
   } else {
       
       $sql0='CREATE TEMPORARY TABLE beginv AS SELECT `BranchNo`, sm.`ItemCode`, IFNULL(SUM(`Qty`),0) AS `BegInv`
    FROM `invty_20uniallposted` `sm` JOIN invty_1items i on i.ItemCode=sm.ItemCode WHERE
        ((`sm`.`Date` IS NOT NULL) AND (`sm`.`Date` < \''.$fromdate.'\')) '.$catoritemno.$sqlcondition.' GROUP BY `sm`.`BranchNo` , `sm`.`ItemCode`';
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    
    $sql0='CREATE TEMPORARY TABLE endinv AS SELECT `BranchNo`, sm.`ItemCode`, IFNULL(SUM(`Qty`),0) AS `EndInv`
    FROM `invty_20uniallposted` `sm` JOIN invty_1items i on i.ItemCode=sm.ItemCode WHERE
        ((`sm`.`Date` IS NOT NULL) AND (`sm`.`Date` <= \''.$todate.'\')) '.$catoritemno.$sqlcondition.' GROUP BY `sm`.`BranchNo` , `sm`.`ItemCode`';
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    $beginvforyr=($todate==$currentyr.'01-01'?'IFNULL(BegInv,0)+':'');
    $sql0='CREATE TEMPORARY TABLE acquired AS SELECT ts.ItemCode, ts.BranchNo, '.$beginvforyr.' (SELECT IFNULL(SUM(Qty),0) FROM invty_2mrr mm JOIN invty_2mrrsub ms ON mm.TxnID=ms.TxnID WHERE mm.Date BETWEEN \''.$fromdate.'\' AND \''.$todate.'\' AND ts.ItemCode=ms.ItemCode AND ts.BranchNo=mm.BranchNo  )+(SELECT IFNULL(SUM(QtyReceived),0) FROM invty_2transfer mm JOIN invty_2transfersub ms ON mm.TxnID=ms.TxnID WHERE mm.DateIN BETWEEN \''.$fromdate.'\' AND \''.$todate.'\' AND ts.ItemCode=ms.ItemCode AND ts.BranchNo=mm.ToBranchNo ) - (SELECT IFNULL(SUM(QtySent),0) FROM invty_2transfer mm JOIN invty_2transfersub ms ON mm.TxnID=ms.TxnID WHERE mm.DateOUT BETWEEN \''.$fromdate.'\' AND \''.$todate.'\' AND ts.ItemCode=ms.ItemCode AND ts.BranchNo=mm.BranchNo ) AS PurchaseLessTransferOut FROM invty_1beginv ts GROUP BY ts.ItemCode, ts.BranchNo ';
    $stmt0=$link->prepare($sql0); $stmt0->execute();   
    
       $sql='SELECT  Branch,ss.ItemCode,CONCAT(c.Category," - ",i.ItemDesc) as Description,i.Unit, FORMAT(Sum(ss.Qty),0) AS TotalSold, FORMAT(SUM(CASE WHEN txntype<>2 THEN Qty END),0) AS `Cash`, FORMAT(SUM(CASE WHEN txntype=2 THEN Qty END),0) AS `Charge`
, FORMAT(SUM(CASE WHEN txntype=2 AND (bal.InvBalance<=1) THEN Qty END),0) AS `PaidCharge`, FORMAT(SUM(CASE WHEN txntype=2 AND (bal.InvBalance>1) THEN Qty END),0) AS `UnpaidCharge`, (SELECT BegInv FROM beginv WHERE ItemCode=i.ItemCode AND BranchNo=sm.BranchNo) AS BegInv, (SELECT EndInv FROM endinv WHERE ItemCode=i.ItemCode AND BranchNo=sm.BranchNo) AS EndInv, (SELECT FORMAT(PurchaseLessTransferOut,0) FROM acquired WHERE ItemCode=i.ItemCode AND BranchNo=sm.BranchNo) AS PurchaseLessTransferOut
FROM invty_2sale as sm INNER JOIN invty_2salesub as ss ON sm.TxnID=ss.TxnID join invty_1items i on i.ItemCode=ss.ItemCode JOIN invty_1category c ON c.CatNo=i.CatNo
JOIN `1branches` as b on b.BranchNo=sm.BranchNo 
LEFT JOIN `acctg_33qrybalperrecpt` bal ON bal.BranchNo=sm.BranchNo AND bal.Particulars=sm.SaleNo AND txntype=2
WHERE sm.BranchNo<>999 AND txntype<>3  AND sm.Date>=\''.$fromdate.'\' AND sm.Date<=\''.$todate.'\''.$catoritemno.$sqlcondition.'
GROUP BY ss.ItemCode, sm.BranchNo;';
       $formdesc='From '.$fromdate.' To '.$todate.'<br><br>';
       $columnnames=array('Branch','ItemCode', 'Description','Unit','BegInv','PurchaseLessTransferOut','EndInv','TotalSold','Cash','Charge','PaidCharge','UnpaidCharge');
   }
 //   if($_SESSION['(ak0)']==1002) { echo $sql;}  
    $showbranches=true;
    include('../backendphp/layout/displayastable.php');
   break;
/* case 'ZeroEndInv':
    if (!allowedToOpen(752,'1rtc')) {   echo 'No permission'; exit;}
   $title='Zero End Inv, With Sale Past 6 Months';
   $columnnames=array('Category','ItemCode', 'Description','Unit','EndInvToday','TotalSoldin6months','DivBy6');
   
   $sql='SELECT c.Category,e.ItemCode,i.ItemDesc as Description,i.Unit, EndInvToday, TotalSoldin6months, DivBy6
FROM invty_39zeroendinvwithsale6months as e join invty_1items i on i.ItemCode=e.ItemCode
join invty_1category c on c.CatNo=i.CatNo
where e.BranchNo='.$_SESSION['bnum'].' ORDER BY Category, i.ItemCode';
  
    $showbranches=true;
    include('../backendphp/layout/displayastable.php');
   break; */
   
case 'ZeroEndInv':
    if (!allowedToOpen(752,'1rtc')) {   echo 'No permission'; exit;}
$subr=(!isset($_POST['subr']))?1:$_POST['subr']; 
		echo '<form method="post" action="lookupperitem.php?w=ZeroEndInv" enctype="multipart/form-data">
				Per Branch<input type="radio" name="subr" value="1">
				Show All Branches <input type="radio" name="subr" value="2"></input> 
				<input type="submit" name="lookup" value="Lookup"> </form>';
		  switch($subr){
        case 1:
            $title='Per Branch'; $sqlcondition='e.BranchNo='.$_SESSION['bnum'];
            break;
        case 2:
            $title='Show All Branches'; $sqlcondition='1=1';
            break;
		  }

    
   $title='Zero End Inv, With Sale Past 6 Months';
   $columnnames=array('Branch','Category','ItemCode', 'Description','Unit','EndInvToday','TotalSoldin6months','DivBy6');
   
   $sql='SELECT Branch,c.Category,e.ItemCode,i.ItemDesc as Description,i.Unit, EndInvToday, TotalSoldin6months, DivBy6
FROM invty_39zeroendinvwithsale6months as e join invty_1items i on i.ItemCode=e.ItemCode
join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=e.BranchNo
where '.$sqlcondition.' ORDER BY Category, i.ItemCode';
// echo $sql; exit();
  
    $showbranches=true;
    include('../backendphp/layout/displayastable.php');
   break;

case 'EndInvValuesBudget':
   if (!allowedToOpen(746,'1rtc')) {   echo 'No permission'; exit;}
   $title='Invty Values Per Month for Invty Planning Budget';
   $formdesc='Basis is latest cost to account for price increases. (in 000\'s)';
   
    $sql0='CREATE TEMPORARY TABLE endinvvaluechanges (
ForMonth	tinyint(2)	NOT NULL, 
BranchNo	smallint(6)	NOT NULL,
Branch		varchar(15)	NOT NULL,
EndInvValue	double	NOT NULL)
Select ForMonth, e.BranchNo, b.Branch, EndInvValue/1000 as EndInvValue from invty_52endinvvalueschangespermonth e
join `1branches` as b on b.BranchNo=e.BranchNo where ((`b`.`BranchNo` not in (99, 999)) and (`b`.`Active` = 1));';

    //echo $sql0;
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
   $sqlmonth='SELECT ForMonth from endinvvaluechanges group by ForMonth';
    $stmtmonth=$link->query($sqlmonth);
    $resultcount=$stmtmonth->rowCount();
    $resultmonth=$stmtmonth->fetchAll();
    $sql='';  
    $columnnames=array('Branch');
   for ($row = 0; $row <  $resultcount; $row++){
      $sql=$sql.'format(Sum(Case when ForMonth<='.$resultmonth[$row]['ForMonth'].' then EndInvValue end),0) as \''.$resultmonth[$row]['ForMonth'].($row==($resultcount-1)?'\'':'\', ');
      $columnnames[]=$resultmonth[$row]['ForMonth'];
   }
   $sql='Select Branch, '. $sql.' from endinvvaluechanges group by BranchNo order by Branch';
    $showbranches=false;
    include('../backendphp/layout/displayastable.php');
   break;


case 'EndInvValuesWtd':
   if (!allowedToOpen(747,'1rtc')) {   echo 'No permission'; exit;}
   $title='Invty Values Per Month for Acctg';
   $formdesc='Basis is weighted cost.';
   $months=array(1,2,3,4,5,6,7,8,9,10,11,12); $sql0=''; $sqlleft=''; $sqlright=''; $columnnamesleft=array('Branch'); $columnnamesright=array('Company');
   foreach ($months as $month){
      $monthcol=str_pad($month,2,'0',STR_PAD_LEFT);
      $columnnamesleft[]=$monthcol; $columnnamesright[]=$monthcol;
      $sqlleft=$sqlleft.', format(`'.$monthcol.'`,0) as `'.$monthcol.'`';  $sqlright=$sqlright.', format(sum(`'.$monthcol.'`),0) as `'.$monthcol.'`';
      $sql0=$sql0.', round(sum(case when  MONTH(`a`.`Date`)<='.$month.' then (`a`.`Qty` * `wac`.`'.$monthcol.'`) end),0) as `'.$monthcol.'` ';
   }
    $sql0='CREATE TEMPORARY TABLE endinvvalue as
    SELECT  `a`.`BranchNo` AS `BranchNo` '.$sql0.'
    FROM (`invty_20uniallposted` `a` JOIN `'.$currentyr.'_static`.`invty_weightedavecost` `wac` ON ((`a`.`ItemCode` = `wac`.`ItemCode`)))
    WHERE (`a`.`Date` IS NOT NULL) GROUP BY `a`.`BranchNo`;';

    //echo $sql0;
    $stmt0=$link->prepare($sql0); $stmt0->execute();
    $sqlleft='Select Branch'.$sqlleft.' from endinvvalue ev join `1branches` b on b.BranchNo=ev.BranchNo where b.Active<>0 order by Branch';
    $sqlright='Select ShortName as Company'.$sqlright.' from endinvvalue ev join `1branches` b on b.BranchNo=ev.BranchNo join `1companies` c on c.CompanyNo=b.CompanyNo where b.Active<>0 group by Company order by ShortName;';
    $showbranches=false; //echo $sqlleft.'<br><br>'. $sqlright;
    include('../backendphp/layout/twotablessidebyside.php');
   break;

case 'Cost_List_Per_Supplier':
   if (!allowedToOpen(742,'1rtc')) {   echo 'No permission'; exit;}$showbranches=false;
   $calledfrom=10;
   $listname='suppliers';
   $liststoshow=array('suppliers');
	   $listcaption='Supplier Name';
	   $fieldname='SupplierName';
	   $table='1suppliers';
	   $orderby='Category';
   $title='Cost List Per Supplier';
   $lookupprocess='lookupperitem.php?w=Cost_List_Per_Supplier';
   $columnnames=array('Category','ItemCode', 'Description', 'UnitCost','Unit','PurchaseDate','MoveType');
   
   if (!isset($_POST['SupplierName'])){
      $sql='';
   } else{
      $suppno=getValue($link,$table,$fieldname,addslashes($_POST[$fieldname]),'SupplierNo');
   $sql0='CREATE TEMPORARY TABLE latestcoststep1 (
MaxofDate	date  NOT NULL,
SupplierNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL)
     select 
        `lc`.`ItemCode` AS `ItemCode`,
        max(`lc`.`Date`) AS `MaxOfDate`,
	SupplierNo
    from
        `invty_50unibegandmrrforlatestcost` `lc` where SupplierNo='.$suppno.'
    group by `lc`.`ItemCode`' ;

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  
  $sql0='CREATE TEMPORARY TABLE costlistpersupplier (
PurchaseDate	date  NOT NULL,
SupplierNo	smallint(6)	NOT NULL,
SupplierName	varchar(50)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
UnitCost	double	NOT NULL)
SELECT m.Date as PurchaseDate,m.SupplierNo,n.SupplierName,s.ItemCode,s.UnitCost FROM invty_2mrr m join invty_2mrrsub s on m.TxnID=s.TxnID join latestcoststep1 lc on s.ItemCode=lc.ItemCode and m.SupplierNo=lc.SupplierNo join `1suppliers` n on n.SupplierNo=m.SupplierNo where m.SupplierNo='.$suppno.' and m.Date=lc.MaxofDate';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
   $sql='Select lc.*,c.Category,i.ItemDesc as Description,i.Unit, i.MoveType from costlistpersupplier lc join invty_1items i on i.ItemCode=lc.ItemCode join invty_1category c on c.CatNo=i.CatNo ';
   }
   include('../backendphp/layout/displayastablewithcondition.php');
   
   break;
   
case 'Cost_List_Per_Item':
   if (!allowedToOpen(7421,'1rtc')) {   echo 'No permission'; exit;} $showbranches=false; 
   
   $title='Cost List Per Item';
   echo '<title>'.$title.'</title>';
   echo '<h3>'.$title.'</h3>';
   include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
   $sqlsup='SELECT ItemCode, concat(ItemDesc,\' - \',Category) as ItemDesc from invty_1items join invty_1category on invty_1category.CatNo=invty_1items.CatNo order by Category,ItemDesc';
	echo comboBox($link,$sqlsup,'ItemDesc','ItemCode','items');

	echo '<form action="lookupperitem.php?w=Cost_List_Per_Item" method="POST">ItemCode: <input type="text" name="ItemCode" list="items" required/> <input type="submit" name="btnSubmit" value="Lookup"></form>';
   
   $table='invty_1items';
   if (isset($_POST['ItemCode'])){
	   	$itemcode=$_POST['ItemCode'];
		
		 $sql1='SELECT Category, (CASE 
	WHEN i.MoveType = 0 THEN "Active"
	WHEN i.MoveType = 1 THEN "NonStock"
	WHEN i.MoveType = 3 THEN "NonMoving"
	ELSE "Obsolete"
	END) AS MoveType FROM invty_1items i JOIN invty_1category c ON i.CatNo=c.CatNo WHERE ItemCode='.$itemcode.';';
		$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
	
	
	   $itemdesc=getValue($link,$table,'ItemCode',addslashes($_POST['ItemCode']),'ItemDesc');
	   echo '<br>ItemCode: '.$itemcode.'<br>Category: '.$res1['Category'].'<br>Description: '.$itemdesc.'<br>MoveType: '.$res1['MoveType'].'<br>';
	   
 
   include('sqlphp/costlistperitem.php');
   
   $sql='Select lc.*,c.Category,i.ItemDesc as Description,i.Unit
  

   from costlistperitem lc join invty_1items i on i.ItemCode=lc.ItemCode join invty_1category c on c.CatNo=i.CatNo WHERE lc.ItemCode='.$itemcode;
   $title='';
$columnnames=array('SupplierNo','SupplierName','UnitCost','Unit','PurchaseDate');
	include('../backendphp/layout/displayastablenosort.php');
   }
   break;
   
   case 'ItemsWtAveCostPL':
    if (!allowedToOpen(6053,'1rtc')) {   echo 'No permission'; exit;}
	   if(isset($_POST['MonthNo'])){
				$monthno=$_POST['MonthNo']; $sortby=$_POST['SortBy'];
			} else {
				$monthno=str_pad(date('m'),2,STR_PAD_LEFT,'0'); $sortby='Category';
			}
		echo '<br><form action="lookupperitem.php?w=ItemsWtAveCostPL" method="POST">';
		$months=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		$columnnames=array('ItemCode','Category','ItemDesc','WtdAveCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5');
		echo 'Month: <select name="MonthNo">';
		$cntm=1;
		foreach($months AS $month){
			$valsel=str_pad($cntm,2,STR_PAD_LEFT,'0');
			echo '<option value="'.$valsel.'" '.($monthno==''.$valsel.''?'selected':'').'>'.$month.'</option>'; $cntm++;
		}
		echo '</select>';
		echo ' Sort by: <select name="SortBy">';
		foreach($columnnames AS $colsort){
			echo '<option value="'.$colsort.'" '.($colsort==$sortby?'selected':'').'>'.$colsort.'</option>';
		}
		echo '</select>';
		echo ' <input type="submit" name="btnLookup" value="LookUp">';
		echo '</form>';
		
	   $title='Finance - Spread';
	   $sql='SELECT lmp.ItemCode,PriceLevel1,PriceLevel2,PriceLevel3,PriceLevel4,PriceLevel5,ItemDesc,Category,`'.$monthno.'` AS WtdAveCost FROM invty_5latestminprice lmp LEFT JOIN '.$currentyr.'_static.invty_weightedavecost wac ON lmp.ItemCode=wac.ItemCode JOIN invty_1items i ON lmp.ItemCode=i.ItemCode JOIN invty_1category cg ON i.CatNo=cg.CatNo ORDER BY '.$sortby.';';
	  
	   include('../backendphp/layout/displayastablenosort.php');
   break;
   
   
  }
  noform:
     
     $link=null; $stmt=null; 
     $stmt0=null;
     $link=null;
?>
