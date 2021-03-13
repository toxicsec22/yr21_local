<html>
<head>
<title>Auto Create Request</title>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(708,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');
 
 
?>
<h3>Auto Create Request</h3><br>
</head>
<body>

<?php
//start enablebasedonradio	
			$radionamefield='radiolist';	
			echo '<form id="form-id">
			Per Warehouse <input type="radio" id="watch-me1" name="'.$radionamefield.'"> '.str_repeat('&nbsp;',20).'
			All  Branches<input type="radio" id="watch-me2" name="'.$radionamefield.'">
			</form>
			</br>';
			$formaction='<form method="post" action="autorequestperwarehouse.php">';
			$all='<input type="hidden" name="All">';
			$perwarehouseinput='Request from Warehouse <input type="text" name="wh" list="warehouses">
<datalist id="warehouses">
    <option value="40" label="Central"></option>
    <option value="27" label="CDO Warehouse"></option>
    <option value="65" label="Luzon Warehouse"></option>
</datalist>';
						
			//per warehouse
			echo '<div id="show-me1" style="display:none">
					'.$formaction.'
					From <input type="date" name="fromdate" ></input>&nbsp &nbsp &nbsp 
					To <input type="date" name="todate" ></input>&nbsp &nbsp &nbsp <br><br>
					'.$perwarehouseinput.'
					<input type="submit" name="create" value="Create">
				</form>
				</div>';
			
			//all
			echo '<div id="show-me2" style="display:none">
					'.$formaction.'
					From <input type="date" name="fromdate" ></input>&nbsp &nbsp &nbsp 
					To <input type="date" name="todate" ></input>&nbsp &nbsp &nbsp <br><br>
					'.$all.'
					<input type="submit" name="create" value="Create">
				</form>
				</div>';				
			
			include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';	
//end

if (!isset($_POST['create'])){
    goto noform;
} elseif(isset($_POST['wh'])){
    $sql0='CREATE TEMPORARY TABLE endinvperbranch (
ServedByWH	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
EndInvToday	double	NOT NULL)
select ServedByWH, ItemCode, Sum(Qty) as EndInvToday from invty_20uniallcodeandqty ucq join 1branches b on b.BranchNo=ucq.BranchNo where ServedByWH='.$_POST['wh'].' AND Defective<>1 group by ItemCode,ServedByWH';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
	// echo $sql0; exit();
   // echo 'EndInv Records: '.$stmt0->rowCount().'<br>';
    
    $sql0='CREATE TEMPORARY TABLE undelivered (
ServedByWH	smallint(6)	NOT NULL,
RequestNo	varchar(50)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Undelivered	double	NULL)

Select ServedByWH,br.RequestNo,brs.ItemCode, (brs.RequestQty-sum(ifnull(ts.QtyReceived,0))) as Undelivered from invty_3branchrequestsub as brs join invty_3branchrequest as br on br.TxnID=brs.TxnID 
left join invty_2transfersub as ts on  brs.ItemCode=ts.ItemCode join 1branches b on b.BranchNo=br.BranchNo
join invty_2transfer as t on t.TxnID=ts.TxnID and br.RequestNo=t.ForRequestNo
where ServedByWH='.$_POST['wh'].' group by ServedByWH,brs.ItemCode having Undelivered>0';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  //  echo 'Undelivered Records: '.$stmt0->rowCount().'<br>';
    $lastyrsql='';
    if (substr($_POST['fromdate'],0,4)==$lastyr){
        $sql1='CREATE TEMPORARY TABLE soldlastyr (
ServedByWH	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Sold	double	NOT NULL)

SELECT ServedByWH, s.ItemCode, Sum(s.QtySold) AS Sold
FROM `'.$currentyr.'_static`.`invty_soldlastyear` s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=s.BranchNo
where c.CatNo<>1 and ServedByWH='.$_POST['wh'].' and s.soldondate>=\''.$_POST['fromdate'].'\' and s.soldondate<=\''.$_POST['todate'].'\' 
GROUP BY s.ItemCode,ServedByWH;';

    $stmt1=$link->prepare($sql1);    $stmt1->execute(); $lastyrsql='+IFNULL((SELECT Sold FROM soldlastyr s where s.ItemCode=ss.ItemCode),0)';
    }
    $sql0='CREATE TEMPORARY TABLE totalsold (
ServedByWH	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Sold	double	NOT NULL)

SELECT ServedByWH, ss.ItemCode, Sum(ss.Qty)'.$lastyrsql.' AS Sold
FROM invty_2sale as sm INNER JOIN invty_2salesub as ss ON sm.TxnID=ss.TxnID join invty_1items i on i.ItemCode=ss.ItemCode join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=sm.BranchNo
where c.CatNo<>1 and txntype<>3 and ServedByWH='.$_POST['wh'].' and sm.Date>=\''.$_POST['fromdate'].'\' and sm.Date<=\''.$_POST['todate'].'\' 
GROUP BY ss.ItemCode,ServedByWH';

    $stmt0=$link->prepare($sql0);    $stmt0->execute();
  //  echo 'Sold Records: '.$stmt0->rowCount().'<br>';
$lastyrsql='';
if (substr($_POST['fromdate'],0,4)==$lastyr){
    $sql1='CREATE TEMPORARY TABLE lostsaleslastyr (
ServedByWH	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
LostSales	double	NOT NULL)

SELECT ServedByWH, ls.ItemCode, Sum(Qty) as LostSales FROM '.$lastyr.'_1rtc.invty_6lostsales ls join invty_1items i on i.ItemCode=ls.ItemCode join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=ls.BranchNo
where c.CatNo<>1 and ServedByWH='.$_POST['wh'].' and ls.Date>=\''.$_POST['fromdate'].'\' and ls.Date<=\''.$_POST['todate'].'\' 
GROUP BY ls.ItemCode,ServedByWH';
    $stmt1=$link->prepare($sql1);    $stmt1->execute(); $lastyrsql='+IFNULL((SELECT LostSales FROM lostsaleslastyr s where s.ItemCode=ls.ItemCode),0)';
}    
    
$sql0='CREATE TEMPORARY TABLE lostsales (
ServedByWH	smallint(6)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
LostSales	double	NOT NULL)

SELECT ServedByWH, ls.ItemCode, Sum(Qty)'.$lastyrsql.' as LostSales FROM invty_6lostsales ls join invty_1items i on i.ItemCode=ls.ItemCode join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=ls.BranchNo
where c.CatNo<>1 and ServedByWH='.$_POST['wh'].' and ls.Date>=\''.$_POST['fromdate'].'\' and ls.Date<=\''.$_POST['todate'].'\' 
GROUP BY ls.ItemCode,ServedByWH';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  //  echo 'Sold Records: '.$stmt0->rowCount().'<br>';
  
$reqnoprefix=str_pad($_POST['wh'],2,'0',STR_PAD_LEFT).'-'.date('md').'-';
$sql='SELECT RequestNo FROM invty_3branchrequest where Left(RequestNo,8)=\''.$reqnoprefix.'\' order by RequestNo desc Limit 1;';
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
	    if (is_null($result['RequestNo'])){
		$reqno=$reqnoprefix.'1';
	    } else {
		$reqno=$reqnoprefix.(substr($result['RequestNo'],-1)+1);
	    }
$sql="SELECT DATE_FORMAT(Date_Add(Now(), INTERVAL b.LeadTimeinDays DAY),'%Y-%m-%d') as DateReq from `1branches` as b where ServedByWH=".$_POST['wh'];
$stmt=$link->query($sql);
$result=$stmt->fetch();

$sql1='INSERT INTO `invty_3branchrequest`
(`Date`,`SupplierBranchNo`,`RequestNo`,`DateReq`,`TimeStamp`,`BranchNo`,`EncodedByNo`,`PostedByNo`) values 
(DATE_FORMAT(NOW(),\'%Y-%m-%d\'),'. $_POST['wh'] .',\''. $reqno .'\',\''.$result['DateReq'].'\', DATE_FORMAT(NOW(),\'%Y-%m-%d %k:%i:%s\'),'.$_POST['wh'].','.$_SESSION['(ak0)'].','.$_SESSION['(ak0)'].')';
$stmt1=$link->prepare($sql1);
$stmt1->execute(); 

$sql='SELECT TxnID FROM invty_3branchrequest where RequestNo=\''.$reqno.'\' Limit 1;';
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
            $txnid=$result['TxnID'];

$sql='Select ts.ItemCode, if((ts.Sold+ifnull(ls.LostSales,0))<ifnull(u.Undelivered,0),0,(ts.Sold+ifnull(ls.LostSales,0)-abs(ifnull(u.Undelivered,0)))) as RequestQty, ts.Sold as Sold, end.EndInvToday, ifnull(u.Undelivered,0) as Undelivered, ifnull(ls.LostSales,0) as LostSales from totalsold as ts
join endinvperbranch as end on ts.ServedByWH=end.ServedByWH and ts.ItemCode=end.ItemCode 
left join undelivered as u on ts.ServedByWH=u.ServedByWH and ts.ItemCode=u.ItemCode
left join lostsales as ls on ts.ServedByWH=ls.ServedByWH and ts.ItemCode=ls.ItemCode
where ts.ServedByWH='.$_POST['wh'];
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
//-------------------------------------------------------------------------------------------------------------------------All Branches
elseif(isset($_POST['All'])){
	 if (substr($_POST['fromdate'],0,4)==$lastyr){
		$yr=''.$lastyr.'_1rtc.';
	}else{
		$yr='';
	}
	
$sql0='CREATE TEMPORARY TABLE endinvperbranch (
ItemCode	smallint(6)	NOT NULL,
EndInvToday	double	NOT NULL)
select ItemCode, Sum(Qty) as EndInvToday from '.$yr.'invty_20uniallcodeandqty ucq join '.$yr.'1branches b on b.BranchNo=ucq.BranchNo where Defective<>1 and ucq.BranchNo Not in (27,40,65) group by ItemCode';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
	// echo $sql0; exit();
   // echo 'EndInv Records: '.$stmt0->rowCount().'<br>';
    
    $sql0='CREATE TEMPORARY TABLE undelivered (
RequestNo	varchar(50)	NOT NULL,
ItemCode	smallint(6)	NOT NULL,
Undelivered	double	NULL)

Select br.RequestNo,brs.ItemCode, (brs.RequestQty-sum(ifnull(ts.QtyReceived,0))) as Undelivered from '.$yr.'invty_3branchrequestsub as brs join '.$yr.'invty_3branchrequest as br on br.TxnID=brs.TxnID 
left join '.$yr.'invty_2transfersub as ts on  brs.ItemCode=ts.ItemCode join '.$yr.'1branches b on b.BranchNo=br.BranchNo
join '.$yr.'invty_2transfer as t on t.TxnID=ts.TxnID and br.RequestNo=t.ForRequestNo where br.BranchNo Not in (27,40,65)
 group by brs.ItemCode having Undelivered>0';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  //  echo 'Undelivered Records: '.$stmt0->rowCount().'<br>';
    $lastyrsql='';
    // if (substr($_POST['fromdate'],0,4)==$lastyr){
        // $sql1='CREATE TEMPORARY TABLE soldlastyr (
// ItemCode	smallint(6)	NOT NULL,
// Sold	double	NOT NULL)

// SELECT s.ItemCode, Sum(s.QtySold) AS Sold
// FROM `'.$currentyr.'_static`.`invty_soldlastyear` s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=s.BranchNo
// where c.CatNo<>1  and s.soldondate>=\''.$_POST['fromdate'].'\' and s.soldondate<=\''.$_POST['todate'].'\' and s.BranchNo Not in (27,40,65)
// GROUP BY s.ItemCode;';

    // $stmt1=$link->prepare($sql1);    $stmt1->execute(); $lastyrsql='+IFNULL((SELECT Sold FROM soldlastyr s where s.ItemCode=ss.ItemCode),0)';
    // }
    $sql0='CREATE TEMPORARY TABLE totalsold (
ItemCode	smallint(6)	NOT NULL,
Sold	double	NOT NULL)

SELECT ServedByWH, ss.ItemCode, Sum(ss.Qty)'.$lastyrsql.' AS Sold
FROM '.$yr.'invty_2sale as sm INNER JOIN '.$yr.'invty_2salesub as ss ON sm.TxnID=ss.TxnID join '.$yr.'invty_1items i on i.ItemCode=ss.ItemCode join '.$yr.'invty_1category c on c.CatNo=i.CatNo join '.$yr.'1branches b on b.BranchNo=sm.BranchNo
where c.CatNo<>1 and txntype<>3 and sm.Date>=\''.$_POST['fromdate'].'\' and sm.Date<=\''.$_POST['todate'].'\' and sm.BranchNo Not in (27,40,65)
GROUP BY ss.ItemCode';

    $stmt0=$link->prepare($sql0);    $stmt0->execute();
  //  echo 'Sold Records: '.$stmt0->rowCount().'<br>';
$lastyrsql='';
// if (substr($_POST['fromdate'],0,4)==$lastyr){
    // $sql1='CREATE TEMPORARY TABLE lostsaleslastyr (
// ItemCode	smallint(6)	NOT NULL,
// LostSales	double	NOT NULL)

// SELECT ls.ItemCode, Sum(Qty) as LostSales FROM '.$lastyr.'_1rtc.invty_6lostsales ls join invty_1items i on i.ItemCode=ls.ItemCode join invty_1category c on c.CatNo=i.CatNo join 1branches b on b.BranchNo=ls.BranchNo
// where c.CatNo<>1 and ls.Date>=\''.$_POST['fromdate'].'\' and ls.Date<=\''.$_POST['todate'].'\'  and ls.BranchNo Not in (27,40,65)
// GROUP BY ls.ItemCode';
    // $stmt1=$link->prepare($sql1);    $stmt1->execute(); $lastyrsql='+IFNULL((SELECT LostSales FROM lostsaleslastyr s where s.ItemCode=ls.ItemCode),0)';
// }    
    
$sql0='CREATE TEMPORARY TABLE lostsales (
ItemCode	smallint(6)	NOT NULL,
LostSales	double	NOT NULL)

SELECT ls.ItemCode, Sum(Qty)'.$lastyrsql.' as LostSales FROM '.$yr.'invty_6lostsales ls join '.$yr.'invty_1items i on i.ItemCode=ls.ItemCode join '.$yr.'invty_1category c on c.CatNo=i.CatNo join '.$yr.'1branches b on b.BranchNo=ls.BranchNo
where c.CatNo<>1 and ls.Date>=\''.$_POST['fromdate'].'\' and ls.Date<=\''.$_POST['todate'].'\' and ls.BranchNo Not in (27,40,65)
GROUP BY ls.ItemCode';

    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
  //  echo 'Sold Records: '.$stmt0->rowCount().'<br>';
  
$reqnoprefix=str_pad(0,2,'0',STR_PAD_LEFT).'-'.date('md').'-';
$sql='SELECT RequestNo FROM invty_3branchrequest where Left(RequestNo,8)=\''.$reqnoprefix.'\' order by RequestNo desc Limit 1;';
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
	    if (is_null($result['RequestNo'])){
		$reqno=$reqnoprefix.'1';
	    } else {
		$reqno=$reqnoprefix.(substr($result['RequestNo'],-1)+1);
	    }
$sql='SELECT curdate() as DateReq ';
$stmt=$link->query($sql);
$result=$stmt->fetch();

$sql1='INSERT INTO `invty_3branchrequest`
(`Date`,`SupplierBranchNo`,`RequestNo`,`DateReq`,`TimeStamp`,`BranchNo`,`EncodedByNo`,`PostedByNo`) values 
(DATE_FORMAT(NOW(),\'%Y-%m-%d\'),\'0\',\''. $reqno .'\',\''.$result['DateReq'].'\', DATE_FORMAT(NOW(),\'%Y-%m-%d %k:%i:%s\'),\'0\','.$_SESSION['(ak0)'].','.$_SESSION['(ak0)'].')';
$stmt1=$link->prepare($sql1);
$stmt1->execute(); 

$sql='SELECT TxnID FROM invty_3branchrequest where RequestNo=\''.$reqno.'\' Limit 1;';
	    $stmt=$link->query($sql);
	    $result=$stmt->fetch();
            $txnid=$result['TxnID'];

$sql='Select ts.ItemCode, if((ts.Sold+ifnull(ls.LostSales,0))<ifnull(u.Undelivered,0),0,(ts.Sold+ifnull(ls.LostSales,0)-abs(ifnull(u.Undelivered,0)))) as RequestQty, ts.Sold as Sold, end.EndInvToday, ifnull(u.Undelivered,0) as Undelivered, ifnull(ls.LostSales,0) as LostSales from totalsold as ts
join endinvperbranch as end on ts.ItemCode=end.ItemCode 
left join undelivered as u on  ts.ItemCode=u.ItemCode
left join lostsales as ls on ts.ItemCode=ls.ItemCode';
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
     $stmt=null;  $stmt=null;
?>
</body>
</html>