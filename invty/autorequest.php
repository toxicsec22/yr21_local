<html>
<head>
<title>Auto Create Request</title>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(708,'1rtc')) { echo 'No permission'; exit;}
$showbranches=true; include_once('../switchboard/contents.php');
 
 
?>
Auto Create Request<br><br>
</head>
<body>
<form action='autorequest.php' method='post'>
From <input type='date' name='fromdate' ></input>&nbsp &nbsp &nbsp 
To <input type='date' name='todate' ></input>&nbsp &nbsp &nbsp <br><br>
Request from Warehouse <input type='text' name='wh' list='warehouses'></input><br>
<datalist id='warehouses'>
    <option value="40" label="Central"></option>
    <option value="27" label="CDO Warehouse"></option>
    <option value="65" label="Luzon Warehouse"></option>
</datalist>
<input type="submit" name="create" value="Create">
</form>
<?php
if (!isset($_POST['create'])){
    goto noform;
} else {
    $sql0='CREATE TEMPORARY TABLE endinvperbranch (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
EndInvToday	double	NOT NULL)
select BranchNo, ItemCode, Sum(Qty) as EndInvToday from invty_20uniallcodeandqty where BranchNo='.$_SESSION['bnum'].' AND Defective<>1 group by BranchNo,ItemCode';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
   // echo 'EndInv Records: '.$stmt0->rowCount().'<br>';
    
    $sql0='CREATE TEMPORARY TABLE undelivered (
BranchNo	smallint(6)	NOT NULL,
RequestNo	varchar(50)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Undelivered	double	NULL)

Select br.BranchNo,br.RequestNo,brs.ItemCode, (brs.RequestQty-sum(ifnull(ts.QtyReceived,0))) as Undelivered from invty_3branchrequestsub as brs join invty_3branchrequest as br on br.TxnID=brs.TxnID 
left join invty_2transfersub as ts on  brs.ItemCode=ts.ItemCode
join invty_2transfer as t on t.TxnID=ts.TxnID and br.RequestNo=t.ForRequestNo
where br.BranchNo='.$_SESSION['bnum'].' group by br.BranchNo,brs.ItemCode having Undelivered>0';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  //  echo 'Undelivered Records: '.$stmt0->rowCount().'<br>';
  
//addedcondition
$sqlch='select PseudoBranch from 1branches where BranchNo=\''. $_SESSION['bnum'].'\'';
$stmtch=$link->query($sqlch);$resultch=$stmtch->fetch();
if($resultch['PseudoBranch']==2){
	$sqlc='CREATE TEMPORARY TABLE transfer (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
transferQty	double	NULL)

select BranchNo,ItemCode,sum(QtySent) as transferQty from invty_2transfer t join invty_2transfersub ts on ts.TxnID=t.TxnID where BranchNo='.$_SESSION['bnum'].' and DateOUT between \''.$_POST['fromdate'].'\' and \''.$_POST['todate'].'\' group by BranchNo,ItemCode';
	$stmtc=$link->prepare($sqlc);	$stmtc->execute(); 
$leftjoin='left join transfer t on ts.BranchNo=t.BranchNo and ts.ItemCode=t.ItemCode';
$add='+ifnull(transferQty,0)';
}else{
	$leftjoin='';
	$add='';
}  
  
    $lastyrsql='';
    if (substr($_POST['fromdate'],0,4)==$lastyr){
        $sql1='CREATE TEMPORARY TABLE soldlastyr (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Sold	double	NOT NULL)

SELECT s.BranchNo, s.ItemCode, Sum(s.QtySold) AS Sold
FROM `'.$currentyr.'_static`.`invty_soldlastyear` s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo
where c.CatNo<>1 and s.BranchNo='.$_SESSION['bnum'].' and s.soldondate>=\''.$_POST['fromdate'].'\' and s.soldondate<=\''.$_POST['todate'].'\' 
GROUP BY s.ItemCode, s.BranchNo;';

    $stmt1=$link->prepare($sql1);    $stmt1->execute(); $lastyrsql='+IFNULL((SELECT Sold FROM soldlastyr s where s.ItemCode=ss.ItemCode),0)';
    }
    $sql0='CREATE TEMPORARY TABLE totalsold (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Sold	double	NOT NULL)

SELECT sm.BranchNo, ss.ItemCode, Sum(ss.Qty)'.$lastyrsql.' AS Sold
FROM invty_2sale as sm INNER JOIN invty_2salesub as ss ON sm.TxnID=ss.TxnID join invty_1items i on i.ItemCode=ss.ItemCode join invty_1category c on c.CatNo=i.CatNo
where c.CatNo<>1 and txntype<>3 and sm.BranchNo='.$_SESSION['bnum'].' and sm.Date>=\''.$_POST['fromdate'].'\' and sm.Date<=\''.$_POST['todate'].'\' 
GROUP BY ss.ItemCode, sm.BranchNo';

    $stmt0=$link->prepare($sql0);    $stmt0->execute();
  //  echo 'Sold Records: '.$stmt0->rowCount().'<br>';
$lastyrsql='';
if (substr($_POST['fromdate'],0,4)==$lastyr){
    $sql1='CREATE TEMPORARY TABLE lostsaleslastyr (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
LostSales	double	NOT NULL)

SELECT BranchNo, ls.ItemCode, Sum(Qty) as LostSales FROM '.$lastyr.'_1rtc.invty_6lostsales ls join invty_1items i on i.ItemCode=ls.ItemCode join invty_1category c on c.CatNo=i.CatNo
where c.CatNo<>1 and ls.BranchNo='.$_SESSION['bnum'].' and ls.Date>=\''.$_POST['fromdate'].'\' and ls.Date<=\''.$_POST['todate'].'\' 
GROUP BY ls.ItemCode, ls.BranchNo';
    $stmt1=$link->prepare($sql1);    $stmt1->execute(); $lastyrsql='+IFNULL((SELECT LostSales FROM lostsaleslastyr s where s.ItemCode=ls.ItemCode),0)';
}    
    
$sql0='CREATE TEMPORARY TABLE lostsales (
BranchNo	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
LostSales	double	NOT NULL)

SELECT BranchNo, ls.ItemCode, Sum(Qty)'.$lastyrsql.' as LostSales FROM invty_6lostsales ls join invty_1items i on i.ItemCode=ls.ItemCode join invty_1category c on c.CatNo=i.CatNo
where c.CatNo<>1 and ls.BranchNo='.$_SESSION['bnum'].' and ls.Date>=\''.$_POST['fromdate'].'\' and ls.Date<=\''.$_POST['todate'].'\' 
GROUP BY ls.ItemCode, ls.BranchNo';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  //  echo 'Sold Records: '.$stmt0->rowCount().'<br>';
  
$reqnoprefix=str_pad($_SESSION['bnum'],2,'0',STR_PAD_LEFT).'-'.date('md').'-';
$sql='SELECT RequestNo FROM invty_3branchrequest where Left(RequestNo,8)=\''.$reqnoprefix.'\' order by RequestNo desc Limit 1;';
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

$sql1='INSERT INTO `invty_3branchrequest`
(`Date`,`SupplierBranchNo`,`RequestNo`,`DateReq`,`TimeStamp`,`BranchNo`,`EncodedByNo`,`PostedByNo`) values 
(DATE_FORMAT(NOW(),\'%Y-%m-%d\'),'. $_POST['wh'] .',\''. $reqno .'\',\''.$result['DateReq'].'\', DATE_FORMAT(NOW(),\'%Y-%m-%d %k:%i:%s\'),'.$_SESSION['bnum'].','.$_SESSION['(ak0)'].','.$_SESSION['(ak0)'].')';
$stmt1=$link->prepare($sql1);
$stmt1->execute(); 

$sql='SELECT TxnID,RequestNo FROM invty_3branchrequest where RequestNo=\''.$reqno.'\' Limit 1;';
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
            $txnid=$result['TxnID'];


$sql='Select ts.ItemCode, if((ts.Sold+ifnull(ls.LostSales,0))<ifnull(u.Undelivered,0),0,(ts.Sold+ifnull(ls.LostSales,0)-abs(ifnull(u.Undelivered,0)))'.$add.') as RequestQty, ts.Sold'.$add.' as Sold, end.EndInvToday, ifnull(u.Undelivered,0) as Undelivered, ifnull(ls.LostSales,0) as LostSales from totalsold as ts
join endinvperbranch as end on ts.BranchNo=end.BranchNo and ts.ItemCode=end.ItemCode 
left join undelivered as u on ts.BranchNo=u.BranchNo and ts.ItemCode=u.ItemCode
left join lostsales as ls on ts.BranchNo=ls.BranchNo and ts.ItemCode=ls.ItemCode
'.$leftjoin.'
where ts.BranchNo='.$_SESSION['bnum'];
$stmt=$link->query($sql);
$result=$stmt->fetchAll();
//echo 'Request Records: '.$stmt->rowCount().'<br>';
//$sqlall='';
foreach($result as $row){
$sqlinsert='INSERT INTO `invty_3branchrequestsub` SET TxnID='.$txnid.', ';
        $sql='';
        $columnstoadd=array('ItemCode','RequestQty','Sold','EndInvToday','Undelivered','LostSales');
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.addslashes($row[$field]).'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();';
$stmt=$link->prepare($sql);
$stmt->execute();
}
//echo '<br>'.$sqlall;

header("Location:addedittxfr.php?w=Request&TxnID=".$txnid);
}
noform:
     $link=null; $stmt=null;
?>
</body>
</html>