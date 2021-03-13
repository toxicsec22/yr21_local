<html>
<head>
<title>Items with Ending Inventory</title>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(708,'1rtc')) { echo 'No permission'; exit;}
$showbranches=true; include_once('../switchboard/contents.php');
 
 
?>
Items with Ending Inventory<br><br>
</head>
<body>
<form action='itemswithendinv.php' method='post'>
From <input type='date' name='fromdate' ></input>&nbsp &nbsp &nbsp 
To <input type='date' name='todate' ></input>&nbsp &nbsp &nbsp <br><br>

<input type="submit" name="create" value="Lookup">
</form>
<?php
if (!isset($_POST['create'])){
    goto noform;
} else {
    
   $lastyrsql='';
    if (substr($_POST['fromdate'],0,4)==$lastyr){ 
        $lastyrsql=' UNION ALL SELECT s.ItemCode, sum(s.QtySold) as Sold
FROM `'.$currentyr.'_static`.`invty_soldlastyear` s where BranchNo='.$_SESSION['bnum'].' and SoldOnDate>=\''.$_POST['fromdate'].'\' and SoldOnDate<=\''.$_POST['todate'].'\' group by SoldOnDate,ItemCode';
    }
    $sql0='CREATE TEMPORARY TABLE unisoldandtxfr (
ItemCode	smallint(6)	NOT NULL,
Sold	double	NOT NULL)
SELECT ss.ItemCode, sum(ss.Qty) as Sold
FROM invty_2sale sm INNER JOIN invty_2salesub ss ON (sm.TxnId = ss.TxnId) where BranchNo='.$_SESSION['bnum'].' and Date>=\''.$_POST['fromdate'].'\' and Date<=\''.$_POST['todate'].'\' group by Date,ItemCode
UNION ALL SELECT ts.ItemCode, sum(ts.QtySent) as Sold
FROM invty_2transfer tm INNER JOIN invty_2transfersub ts ON (tm.TxnId = ts.TxnId) where BranchNo='.$_SESSION['bnum'].' and DateOut>=\''.$_POST['fromdate'].'\' and DateOut<=\''.$_POST['todate'].'\' group by DateOut,ItemCode '.$lastyrsql ;

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
    $sql0='CREATE TEMPORARY TABLE totalsold (
ItemCode	smallint(6)	NOT NULL,
Sold	double	NOT NULL)
Select s.ItemCode, Sum(ifnull(Sold,0)) as Sold from unisoldandtxfr s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo
where c.CatNo<>1 group by s.ItemCode;';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  //  echo 'Sold Records: '.$stmt0->rowCount().'<br>';
$sql0='CREATE TEMPORARY TABLE lostsales (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
LostSales	double	NOT NULL)

SELECT BranchNo, ls.ItemCode, Sum(Qty) as LostSales FROM invty_6lostsales ls join invty_1items i on i.ItemCode=ls.ItemCode join invty_1category c on c.CatNo=i.CatNo
where c.CatNo<>1 and ls.BranchNo='.$_SESSION['bnum'].' and ls.Date>=\''.$_POST['fromdate'].'\' and ls.Date<=\''.$_POST['todate'].'\' 
GROUP BY ls.ItemCode, ls.BranchNo';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
    $sql0='CREATE TEMPORARY TABLE endinvperbranch (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
EndInvToday	double	NOT NULL)
select BranchNo, ItemCode, Sum(Qty) as EndInvToday from invty_20uniallcodeandqty where BranchNo='.$_SESSION['bnum'].' AND Defective<>1 group by BranchNo,ItemCode' ;

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
   // echo 'EndInv Records: '.$stmt0->rowCount().'<br>';
    
    $sql0='CREATE TEMPORARY TABLE undelivered (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
SupplierUndelivered	double	NULL)

Select om.BranchNo,os.ItemCode, (os.Qty-sum(ifnull(ms.Qty,0))) as SupplierUndelivered from invty_3ordersub as os join invty_3order as om on om.TxnID=os.TxnID 
left join invty_2mrrsub as ms on  os.ItemCode=ms.ItemCode
join invty_2mrr as m on m.TxnID=ms.TxnID and om.PONo=m.ForPONo
where om.BranchNo='.$_SESSION['bnum'].' group by om.BranchNo,os.ItemCode having SupplierUndelivered>0;';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  //  echo 'Undelivered Records: '.$stmt0->rowCount().'<br>';
    
    $sql0='CREATE TEMPORARY TABLE unserved (
ItemCode	smallint(6)	NOT NULL,
UnservedBranchRequest	double	NULL)
SELECT ur.ItemCode,Sum(SendBal) as UnservedBranchRequest FROM invty_44undeliveredreq ur
join `invty_3extrequestsub` er on ur.ItemCode=er.ItemCode
where SendBal>0 group by ur.ItemCode;';
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();

$reqnoprefix=str_pad($_SESSION['bnum'],2,'0',STR_PAD_LEFT).'-'.date('md').'-';
$sql='SELECT RequestNo FROM invty_3extrequest where Left(RequestNo,8)=\''.$reqnoprefix.'\' order by RequestNo desc Limit 1;';
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
	    if (is_null($result['RequestNo'])){
		$reqno=$reqnoprefix.'1';
	    } else {
		$reqno=$reqnoprefix.(substr($result['RequestNo'],-1)+1);
	    }
$sql="SELECT DATE_FORMAT(Date_Add(Now(), INTERVAL b.LeadTimeinDays DAY),'%Y-%m-%d') as DateReq from `1branches` as b where BranchNo=".$_SESSION['bnum'];
$stmt=$link->query($sql);
$result=$stmt->fetch();

$sql0="CREATE TEMPORARY TABLE `invty_3extrequestTEMP` (
  `TxnID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` date NOT NULL,
  `RequestNo` varchar(10) NOT NULL,
  `Remarks` varchar(50) DEFAULT NULL,
  `DateReq` date DEFAULT NULL,
  `BranchNo` smallint(6) NOT NULL,
  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `EncodedByNo` smallint(6) NOT NULL,
  `Posted` tinyint(1) DEFAULT '0',
  `PostedByNo` smallint(6) NOT NULL,
  PRIMARY KEY (`TxnID`),
  UNIQUE KEY `RequestNo` (`RequestNo`),
  KEY `ExtReqIdx` (`RequestNo`),
  KEY `extreqbranch` (`BranchNo`)
);";
$stmt0=$link->prepare($sql0);
$stmt0->execute(); 

$sql1='INSERT INTO `invty_3extrequestTEMP`
(`Date`,`RequestNo`,`DateReq`,`BranchNo`,`TimeStamp`,`EncodedByNo`,PostedByNo) values 
(\'' . date("Y-m-d"). '\',\''. $reqno .'\',\''.$result['DateReq'].'\', '.$_SESSION['bnum'].',\'' . date("Y-m-d H:i:s"). '\','.$_SESSION['(ak0)'].','.$_SESSION['(ak0)'].')';
//echo $sql1;
$stmt1=$link->prepare($sql1);
$stmt1->execute(); 

$sql='SELECT TxnID FROM invty_3extrequestTEMP where RequestNo=\''.$reqno.'\' Limit 1;';
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
            $txnid=$result['TxnID'];

$sql='Select ts.ItemCode, if((ifnull(ts.Sold,0)+ifnull(ls.LostSales,0)+ifnull(ur.UnservedBranchRequest,0))<ifnull(u.SupplierUndelivered,0),0,(ifnull(ts.Sold,0)+ifnull(ls.LostSales,0)+ifnull(ur.UnservedBranchRequest,0)-abs(ifnull(u.SupplierUndelivered,0)))) as Qty, ifnull(ts.Sold,0) as Sold, end.EndInvToday, ifnull(u.SupplierUndelivered,0) as SupplierUndelivered, ifnull(ls.LostSales,0) as LostSales, +ifnull(ur.UnservedBranchRequest,0) as UnservedBranchRequest from totalsold as ts
join endinvperbranch as end on ts.ItemCode=end.ItemCode
left join unserved as ur on ts.ItemCode=ur.ItemCode
left join undelivered as u on ts.ItemCode=u.ItemCode
left join lostsales as ls on ts.ItemCode=ls.ItemCode';
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
//echo 'Request Records: '.$stmt->rowCount().'<br>';
//$sqlall='';


$sql0="CREATE TEMPORARY TABLE `invty_3extrequestsubTEMP` (
  `TxnSubId` int(11) NOT NULL AUTO_INCREMENT,
  `TxnID` int(11) NOT NULL,
  `ItemCode` smallint(6) NOT NULL DEFAULT '0',
  `Qty` double NOT NULL DEFAULT '0',
  `Sold` double DEFAULT '0',
  `EndInvToday` double DEFAULT '0',
  `SupplierUndelivered` double NOT NULL DEFAULT '0',
  `LostSales` double DEFAULT '0',
  `UnservedBranchRequest` double DEFAULT '0',
  `TimeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `EncodedByNo` smallint(6) NOT NULL,
  `PONo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`TxnSubId`)
);";
$stmt0=$link->prepare($sql0);
$stmt0->execute(); 


foreach($result as $row){
$sqlinsert='INSERT INTO `invty_3extrequestsubTEMP` SET TxnID='.$txnid.', ';
        $sql='';
        $columnstoadd=array('ItemCode','Qty','Sold','EndInvToday','SupplierUndelivered','LostSales','UnservedBranchRequest');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($row[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();';
$stmt=$link->prepare($sql);
$stmt->execute();
}
//echo '<br>'.$sqlall;
//to make alternating rows have different colors
        $colorcount=0;
        $color1="DBFFDB";
        $color2="FFFFFF";
        //delete the next 2 when editing is direct in cell
        $rcolor[0]="DBFFDB";
        $rcolor[1]="FFFFFF";
		
$title='Items with Ending Inventory';
        $formdesc='Date From: '.$_POST['fromdate'].', Date To: '.$_POST['todate'].'<br>Qty = Sold + LostSales + Unserved Branch Requests - Undelivered By Suppliers';
        $txntype='Request';
    $sqlmain='select rm.*, b.Branch as RequestingBranch, e.Nickname as EncodedBy from invty_3extrequestTEMP as rm
    join `1branches` as b on rm.BranchNo=b.BranchNo
left join `1employees` as e on rm.EncodedByNo=e.IDNo where TxnID='.$txnid;
   
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $main='';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.TimeStamp');
    
    $columnnamesmain=array();
     
        $editsub=false;
        $columnsub=array('ItemCode','Category','ItemDesc','Unit','Qty','Sold','EndInvToday','SupplierUndelivered','UnservedBranchRequest','LostSales');
 
    
    $main='';
	$editmain='';
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
    $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy from invty_3extrequestsubTEMP s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo where TxnID='.$txnid.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
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
        
        $sub=$sub.($editsub?'<td><a href="editextspecifics.php?edit=2&w=RequestSubEdit&txntype='.$txntype.'&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href=praddext.php?TxnID='.$txnid.'&TxnSubId='.$row['TxnSubId'].'&action_token='.$_SESSION['action_token'].'&w=RequestSubDel&txntype='.$txntype.' OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
       // $sub=$sub;
    $colorcount++;    
    }
    
    $sub='<table><tr>'.$subcol.'</tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select count(s.ItemCode) as LineItems,sum(c.UnitCost*Qty) as ReqValue from `invty_3extrequestsubTEMP` s 
join `invty_3extrequestTEMP` m on m.TxnID=s.TxnID
left join `invty_52latestcost` c on s.ItemCode=c.ItemCode
join `1branches` b on b.BranchNo=m.BranchNo
Where m.TxnID='.$txnid;
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Line Items with No PO:  '.number_format($result['LineItems'],0).'&nbsp &nbsp &nbsp &nbsp Total Value:  '.number_format($result['ReqValue'],2);
    
       $columnnames=array(); 
    
    $liststoshow=array();
    // info for posting:
    $nopost='';
    $table='invty_3extrequestTEMP';


 $left='90%'; $leftmargin='91%'; $right='9%';
 include('../backendphp/layout/inputsubform.php');
 

}
noform:
     $link=null; $stmt=null;
?>
</body>
</html>