<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(5907),'1rtc')) { echo 'No permission'; exit();}
include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'PriceDiff':$_GET['w']);
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
switch($which){
        case 'PriceDiff':
            if (!allowedToOpen(5907,'1rtc')) { echo 'No permission'; exit();}
            $branch=$_SESSION['@brn'];

            if(isset($_POST['filter'])){ $monthvalue=$_POST['Month']; } else { $monthvalue=date('m');}
?>
        <title>Online vs PriceLevel</title>
	</br><h3>Online Prices vs. Branch PriceLevel</h3></br>
        Purpose of this report is to study feasibility of one online price nationwide, with shipping charges to align with local stores.</br></br>
	<form method="post" action="specialreports.php?w=PriceDiff">
				Choose Month (1-12): <input type="text" name="Month" value="<?php echo $monthvalue; ?>" size="1">
				<input type="submit" name="filter" value="Preview"></form></br>
<?php
if(isset($_POST['filter'])){
	 $branchno=$_SESSION['bnum'];
	if($_POST['filter']=='Preview'){	

        $sql0='SELECT Branch,PriceLevel,MONTHNAME(\''.$currentyr.'-'.$monthvalue.'-1\') AS `ForMonth` FROM `1branches` WHERE BranchNo='.$branchno; 
        $stmt0=$link->query($sql0); $res0=$stmt0->fetch();
	$sql0='CREATE TEMPORARY TABLE compareprices AS SELECT round(sum(`'.str_pad($monthvalue,2,'0',STR_PAD_LEFT).'`*Qty),0) AS UnitCost,ROUND(SUM(UnitPrice*Qty),0) AS SaleAmount, ROUND(SUM(Pricelevel3*Qty),0) AS OnlinePrice, ROUND(SUM(`Pricelevel'.$res0['PriceLevel'].'`*Qty),0) AS BranchPriceLevel FROM invty_2sale s JOIN invty_2salesub ss ON ss.TxnID=s.TxnID JOIN invty_5latestminprice lmp ON lmp.ItemCode=ss.ItemCode  left JOIN '.$currentyr.'_static.invty_weightedavecost wac ON wac.ItemCode=ss.ItemCode JOIN 1branches b ON b.BranchNo=s.BranchNo WHERE MONTH(s.Date)=\''.$monthvalue.'\' AND s.BranchNo=\''.$branchno.'\' AND txntype in (1,2) AND ss.TxnID IS NOT NULL GROUP BY SaleNo,txntype HAVING SaleAmount<>0';
	// echo $sql0; exit();
        $stmt0=$link->prepare($sql0); $stmt0->execute();
        $sql='SELECT *, IF(BranchPriceLevel>OnlinePrice, BranchPriceLevel-OnlinePrice,0) AS `ShippingCost?`  FROM compareprices';
                                        
         $columnnames=array('UnitCost','SaleAmount','OnlinePrice','BranchPriceLevel','ShippingCost?');
        $title=$res0['Branch']. ' transactions for '.$res0['ForMonth'];
        include '../backendphp/layout/displayastablenosort.php';
	
}

	
	break;
	
	
}
}
?>