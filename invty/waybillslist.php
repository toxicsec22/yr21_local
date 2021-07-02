<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(20611,20612,20613,20614,20615),'1rtc')) { echo 'No permission'; exit();}
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once $path.'/acrossyrs/js/includesscripts.php';

include_once('../backendphp/layout/linkstyle.php');
 if (allowedToOpen(array(20612,20615),'1rtc')) {
	 echo '<br><br>';
	echo '<a id=\'link\' href="waybillslist.php?w=lists">List of Waybills</a> '.str_repeat('&nbsp;',20).'';
	if (allowedToOpen(20612,'1rtc')) {
		
	echo '<a id=\'link\' href="waybillslist.php?w=ShippingRate">Rate Per Unit</a> ';
		echo '<a id=\'link\' href="waybillslist.php?w=Shipper">List of Shippers</a> ';
	}
	echo '<a id=\'link\' href="waybillslist.php?w=ShipperPrice">List of Shipper Prices</a>'; 
	
	

echo '<br><br>';
}
?>
	<style>
    .box{
        display: none;
    }
    label{ margin-right: 15px; }
</style>
<script type="text/javascript">
function fetch_select(val)
{
 $.ajax({
 type: 'post',
 url: 'waybills_ajax.php',
 data: {
  get_option:val
 },
 success: function (response) {
  document.getElementById("new_select").innerHTML=response; 
 }
 });
}
</script>

<script>
$(document).ready(function(){
    $('input[type="radio"]').click(function(){
        var inputValue = $(this).attr("value");
        var targetBox = $("." + inputValue);
        $(".box").not(targetBox).hide();
        $(targetBox).show();
    });
});
</script>
    <?php
	
	$which=(!isset($_GET['w'])?'lists':$_GET['w']);
	
	
	$link=connect_db("".$currentyr."_1rtc",1);
	$sqldroptemp='DROP TABLE IF EXISTS extrateandval'.$_SESSION['(ak0)'].';';
	$stmtdroptable=$link->prepare($sqldroptemp); $stmtdroptable->execute();
			
		if (in_array($which,array('lists','EditSpecifics'))){
			$columnnameslist=array('Branch','DateOfShipment','WaybillNo','Shipper','NoOfBoxes','DeclaredValue','Insurance','EstAmtBreakDownNoInsurance','EstAmt','Encashed');
			if($which=='lists'){
			echo '<br>';
			include_once('../backendphp/layout/showencodedbybutton.php');
			
				if (isset($showenc) AND $showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp'); }
				
			}
			 
			 
			 if (allowedToOpen(20614,'1rtc')) {
				 $columnnameslist=array_diff($columnnameslist,array('Insurance','EstAmtBreakDownNoInsurance','EstAmt','DeclaredValue'));
			 }
			 $addlsql='';
			 if (allowedToOpen(20613,'1rtc')) {
				 //handled branch
				 $stmt0=$link->query('SELECT GROUP_CONCAT(BranchNo) AS BranchNo FROM attend_1branchgroups WHERE '.$_SESSION['(ak0)'].' IN (FieldSpecialist,BranchCoordinator)');
					$res0=$stmt0->fetch();
				$addlsql=' AND (fib.BranchNo IN ('.$res0['BranchNo'].'))';
			 } else if(allowedToOpen(20614,'1rtc')){
				 //stores
				$addlsql=' AND fib.BranchNo='.$_SESSION['bnum'].''; 
			 }
			 
			 if(isset($_POST['btnShowAll'])){
				 $sqlcon=' WHERE 1=1 ';
			 } else {
				 $sqlcon=' WHERE Encashed=0 ';
			 }
			 
			
			/// from shiperprice first
			
			$sql0='CREATE TEMPORARY TABLE `ExplodedArrayShipperPrice` (
			   `SRIDarrsp1` smallint(6) NULL,
			   `Rate` DOUBLE NULL,
			   `BranchNo` SMALLINT(6) NULL,
			   `ShipperID` TINYINT(1) NULL
			 )';
			$stmt0=$link->prepare($sql0); $stmt0->execute();
			
			$sql2='SELECT SRIDAndRate,BranchNo,ShipperID FROM 1_gamit.invty_1shipperprice WHERE SRIDAndRate<>"" AND SRIDAndRate IS NOT NULL';
			
			$stmt2 = $link->query($sql2);
			$row2=$stmt2->fetchAll();
			
			foreach($row2 AS $rows2){	
				$branchno=$rows2['BranchNo'];
				$shipperid=$rows2['ShipperID'];
				
				
				$arrayex=explode(",", $rows2['SRIDAndRate']);
				foreach($arrayex as $arrex){
						$arr = explode(">", $arrex, 2);
						$srid = $arr[0];
						$rate = $arr[1];
						$sqlsp1='INSERT INTO ExplodedArrayShipperPrice SET SRIDarrsp1='.$srid.',BranchNo='.$branchno.',Rate="'.$rate.'",ShipperID='.$shipperid.';';
						// echo $sqlsp1.'<br>';
						$stmt=$link->prepare($sqlsp1); $stmt->execute();
				}
			}
			
			//waybills
			$sql0='CREATE TEMPORARY TABLE `ExplodedArrayWaybills` (
			   `TxnID` INT(11) NULL,
			   `SRIDarrwb1` smallint(6) NULL,
			   `Val` DOUBLE NULL,
			   `BranchNo` SMALLINT(6) NULL,
			   `ShipperID` TINYINT(1) NULL
			 )';
			$stmt0=$link->prepare($sql0); $stmt0->execute();
			
			$sql2='SELECT TxnID,SRIDAndValue,BranchNo,ShipperID FROM invty_2waybills WHERE SRIDAndValue<>"" AND SRIDAndValue IS NOT NULL ';
			
			$stmt2 = $link->query($sql2);
			$row2=$stmt2->fetchAll();
			
			foreach($row2 AS $rows2){	
				$txnid=$rows2['TxnID'];
				$branchno=$rows2['BranchNo'];
				$shipperid=$rows2['ShipperID'];
				
				
				$arrayex=explode(",", $rows2['SRIDAndValue']);
				foreach($arrayex as $arrex){
						$arr = explode(">", $arrex, 2);
						$srid = $arr[0];
						$val = $arr[1];
						$sqlsp1='INSERT INTO ExplodedArrayWaybills SET TxnID='.$txnid.',SRIDarrwb1='.$srid.',BranchNo='.$branchno.',Val="'.$val.'",ShipperID='.$shipperid.';';
						// echo $sqlsp1.'<br>';
						$stmt=$link->prepare($sqlsp1); $stmt->execute();
				}
			}
			
			
			$sqlex='create table extrateandval'.$_SESSION['(ak0)'].' select SRIDarrwb1,MinCharge,CoveredByMinCharge,TxnID,Rate,Val,InsurancePerDeclaredValue from ExplodedArrayShipperPrice easp JOIN ExplodedArrayWaybills eawb ON easp.ShipperID=eawb.ShipperID AND easp.BranchNo=eawb.BranchNo  AND easp.SRIDarrsp1=eawb.SRIDarrwb1  JOIN 1_gamit.invty_1shipperprice sp ON easp.ShipperID=sp.ShipperID AND easp.BranchNo=sp.BranchNo '.($which=='EditSpecifics'?' WHERE TxnID='.intval($_GET['TxnID']):'').';';
			$stmtex=$link->prepare($sqlex); $stmtex->execute();
			// echo $sqlex;
			//end
			// exit();
			
			// $sql='SELECT fib.*,(SELECT GROUP_CONCAT(RatePer," (",Val,") > ",FORMAT((((IF(RatePer="Kg",IF(MinCharge<>0,(Val-CoveredByMinCharge)*Rate+MinCharge,(Rate*Val)),(Val*Rate))+(fib.DeclaredValue*InsurancePerDeclaredValue))*IF(s.VAT<>0,s.VAT,1))+IF(s.VAT<>0,(IF(RatePer="Kg",IF(MinCharge<>0,(Val-CoveredByMinCharge)*Rate+MinCharge,(Rate*Val)),(Val*Rate))+(fib.DeclaredValue*InsurancePerDeclaredValue)),0)),2) SEPARATOR "<br>") FROM extrateandval erv JOIN 1_gamit.invty_0shippingrate sr ON erv.SRIDarrwb1=sr.SRID WHERE erv.TxnID=fib.TxnID) AS EstAmt,Shipper,Branch,CONCAT(e.Nickname," ",e.SurName) AS EncodedBy,IF(Encashed=1,"Yes","No") AS Encashed FROM invty_2waybills fib LEFT JOIN 1employees e ON fib.EncodedByNo=e.IDNo JOIN 1branches b ON fib.BranchNo=b.BranchNo JOIN `1_gamit`.`invty_1shipper` s ON fib.ShipperID=s.ShipperID';
			
			$sql='SELECT fib.*,FORMAT((SELECT InsurancePerDeclaredValue*fib.DeclaredValue FROM 1_gamit.invty_1shipperprice WHERE ShipperID=fib.ShipperID AND BranchNo=fib.BranchNo),2) AS Insurance,(SELECT
			IF(SUM(IF(RatePer IN ("Kg","CBM"),1,0))>=2,(GROUP_CONCAT(RatePer,">",FORMAT((((IF(RatePer="Kg",IF(MinCharge<>0,(Val-CoveredByMinCharge)*Rate+MinCharge,(Rate*Val)),(Val*Rate)))*IF(s.VAT<>0,s.VAT,1))+IF(s.VAT<>0,(IF(RatePer="Kg",IF(MinCharge<>0,(Val-CoveredByMinCharge)*Rate+MinCharge,(Rate*Val)),(Val*Rate))),0))+InsurancePerDeclaredValue*fib.DeclaredValue,2) SEPARATOR "<br>")),
			
			FORMAT(SUM((((IF(RatePer="Kg",IF(MinCharge<>0,(Val-CoveredByMinCharge)*Rate+MinCharge,(Rate*Val)),(Val*Rate)))*IF(s.VAT<>0,s.VAT,1))+IF(s.VAT<>0,(IF(RatePer="Kg",IF(MinCharge<>0,(Val-CoveredByMinCharge)*Rate+MinCharge,(Rate*Val)),(Val*Rate))),0)))+InsurancePerDeclaredValue*fib.DeclaredValue,2)
			
			) FROM extrateandval'.$_SESSION['(ak0)'].' erv JOIN 1_gamit.invty_0shippingrate sr ON erv.SRIDarrwb1=sr.SRID WHERE erv.TxnID=fib.TxnID) AS EstAmt,(SELECT GROUP_CONCAT(RatePer," (",Val,") > ",FORMAT((((IF(RatePer="Kg",IF(MinCharge<>0,(Val-CoveredByMinCharge)*Rate+MinCharge,(Rate*Val)),(Val*Rate)))*IF(s.VAT<>0,s.VAT,1))+IF(s.VAT<>0,(IF(RatePer="Kg",IF(MinCharge<>0,(Val-CoveredByMinCharge)*Rate+MinCharge,(Rate*Val)),(Val*Rate))),0)),2) SEPARATOR "<br>") FROM extrateandval'.$_SESSION['(ak0)'].' erv JOIN 1_gamit.invty_0shippingrate sr ON erv.SRIDarrwb1=sr.SRID WHERE erv.TxnID=fib.TxnID) AS EstAmtBreakDownNoInsurance,Shipper,Branch,CONCAT(e.Nickname," ",e.SurName) AS EncodedBy,IF(Encashed=1,"Yes","No") AS Encashed FROM invty_2waybills fib LEFT JOIN 1employees e ON fib.EncodedByNo=e.IDNo JOIN 1branches b ON fib.BranchNo=b.BranchNo JOIN `1_gamit`.`invty_1shipper` s ON fib.ShipperID=s.ShipperID';
			
			// echo $sql;
			
		}
		if (in_array($which,array('lists','EditSpecifics','ShipperPrice','EditSpecificsShipperPrice'))){
			 echo comboBox($link,'SELECT BranchNo, Branch FROM `1branches` WHERE PseudoBranch=0 ORDER BY Branch;','BranchNo','Branch','branchlist');
			 echo comboBox($link,'SELECT ShipperID, Shipper FROM `1_gamit`.`invty_1shipper` ORDER BY Shipper;','ShipperID','Shipper','shipperlist');
			 echo comboBox($link,'SELECT SRID, RatePer FROM `1_gamit`.`invty_0shippingrate` ORDER BY RatePer;','SRID','RatePer','rateperlist');
			
		}
		
		if (in_array($which,array('AddSP','EditSP'))){
			
			// $srid=comboBoxValue($link,'1_gamit.invty_0shippingrate','RatePer',addslashes($_POST['RatePer']),'SRID');
			$shipperid=comboBoxValue($link,'1_gamit.invty_1shipper','Shipper',addslashes($_POST['Shipper']),'ShipperID');
			$branchno=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
			
			
		}

		if (in_array($which,array('Shipper','EditSpecificsShipper'))){
		   $sql='SELECT *,ShipperID As TxnID FROM 1_gamit.invty_1shipper';
		   $columnnameslist=array('Shipper','VAT');
		   $columnstoadd=array('Shipper','VAT');
		}
		if (in_array($which,array('ShippingRate','EditSpecificsShippingRate'))){
		   $sql='SELECT *,SRID As TxnID FROM 1_gamit.invty_0shippingrate';
		   $columnnameslist=array('RatePer');
		   $columnstoadd=array('RatePer');
		}

	if (in_array($which,array('AddSP','EditShipper'))){
	   $columnstoadd=array('Shipper','VAT');
	}		
		
	if (in_array($which,array('ShipperPrice','EditSpecificsShipperPrice'))){
		    
		   $sql0='CREATE TEMPORARY TABLE `ExplodedArray` (
			   `SRIDarr` smallint(6) NULL,
			   `Rate` DOUBLE NULL,
			   `BranchNo` SMALLINT(6) NULL,
			   `ShipperID` TINYINT(1) NULL
			 )';
			$stmt0=$link->prepare($sql0); $stmt0->execute();
			
			$sql2='SELECT SRIDAndRate,BranchNo,ShipperID FROM 1_gamit.invty_1shipperprice WHERE SRIDAndRate<>"" AND SRIDAndRate IS NOT NULL '.($which=='EditSpecificsShipperPrice'?' AND SPID='.intval($_GET['SPID']):'').'';
			
			$stmt2 = $link->query($sql2);
			$row2=$stmt2->fetchAll();
			
			foreach($row2 AS $rows2){	
				$branchno=$rows2['BranchNo'];
				$shipperid=$rows2['ShipperID'];
				
				
				$arrayex=explode(",", $rows2['SRIDAndRate']);
				foreach($arrayex as $arrex){
						$arr = explode(">", $arrex, 2);
						$srid = $arr[0];
						$rate = $arr[1];
						$sql='INSERT INTO ExplodedArray SET SRIDarr='.$srid.',BranchNo='.$branchno.',Rate="'.$rate.'",ShipperID='.$shipperid.';';
						$stmt=$link->prepare($sql); $stmt->execute();
				}
			}
			
			$sql='SELECT sp.*,Shipper,Branch,SPID AS TxnID,(SELECT GROUP_CONCAT(RatePer," > ",IF(MinCharge<>0 AND RatePer="Kg",CONCAT("1st ",CoveredByMinCharge,"kilos = P",MinCharge," + P",Rate," per succeeding kilo"),CONCAT("P",Rate)) SEPARATOR "<br>") FROM ExplodedArray ea JOIN 1_gamit.invty_0shippingrate sr ON ea.SRIDarr=sr.SRID WHERE BranchNo=sp.BranchNo AND ShipperID=sp.ShipperID) AS RateAndPrice from 1_gamit.invty_1shipperprice sp JOIN 1_gamit.invty_1shipper s ON sp.ShipperID=s.ShipperID JOIN 1branches b ON sp.BranchNo=b.BranchNo';
			
			
		   
		   $columnnameslist=array('Shipper','Branch','RateAndPrice','InsurancePerDeclaredValue');
		   
		   
		}
		
		
	switch ($which){
		case 'lists':
		$title='List of Waybills';
				if (!allowedToOpen(20611,'1rtc')) { echo 'No Permission'; exit();  } 
				
				
				 if (allowedToOpen(20612,'1rtc')) {
				?>
					<br><br>
					<form action="#" method="POST">
					
					<div><div> <label>Shipper <span>*</span></label>

	 <select onchange="fetch_select(this.value);" name="ShipperID">
	  <option>Select Shipper</option>
	  <?php

	 $sqlshid = "SELECT ShipperID,Shipper FROM 1_gamit.invty_1shipper ORDER BY Shipper";
				$stmtshid=$link->query($sqlshid); $resshid=$stmtshid->fetchAll();
				
				foreach ($resshid AS $row){
					$spid = $row['ShipperID'];
					$spname = $row['Shipper'];
					
					echo "<option value='".$spid."' >".$spname."</option>";
				}
	 ?>
	 </select>

	</div>
			<div> <label>Branch <span>*</span></label>

	<select id="new_select" name="SPID">
	 </select>
		
	</div>
	
	<input type="submit" value="Add" name="btnCheck">
	</form>
	
	
	</div>
				
				<?php
				
				
				if(isset($_POST['btnCheck'])){
					
					$sql0='CREATE TEMPORARY TABLE `ExplodedArray` (
			   `SRIDarr` smallint(6) NULL,
			   `Rate` DOUBLE NULL,
			   `BranchNo` SMALLINT(6) NULL,
			   `ShipperID` TINYINT(1) NULL
			 )';
			$stmt0=$link->prepare($sql0); $stmt0->execute();
			
			$sql2='SELECT SRIDAndRate,BranchNo,ShipperID FROM 1_gamit.invty_1shipperprice WHERE SPID='.intval($_POST['SPID']).'';
			
			$stmt2 = $link->query($sql2);
			$row2=$stmt2->fetchAll();
			
			foreach($row2 AS $rows2){	
				$branchno=$rows2['BranchNo'];
				$shipperid=$rows2['ShipperID'];
				
				
				$arrayex=explode(",", $rows2['SRIDAndRate']);
				foreach($arrayex as $arrex){
						$arr = explode(">", $arrex, 2);
						$srid = $arr[0];
						$rate = $arr[1];
						$sql12='INSERT INTO ExplodedArray SET SRIDarr='.$srid.',BranchNo='.$branchno.',Rate="'.$rate.'",ShipperID='.$shipperid.';';
						// echo $sql;
						$stmt=$link->prepare($sql12); $stmt->execute();
				}
			}
			
			
			
			$sqltab='SELECT Branch,Shipper FROM 1_gamit.invty_1shipperprice sp JOIN 1_gamit.invty_1shipper s ON sp.ShipperID=s.ShipperID JOIN 1branches b ON sp.BranchNo=b.BranchNo WHERE SPID='.$_POST['SPID'];
			 $stmttab=$link->query($sqltab);
			$resulttab=$stmttab->fetch();
			echo '<br><div style="background-color:white;padding:5px;border:2px solid blue;width:30%;"><b>Shipper: '.$resulttab['Shipper'].'<br>Branch: ';
			echo $resulttab['Branch'].'</b>';
			echo '<form action="waybillslist.php?w=AddNew&SPID='.$_POST['SPID'].'" method="POST" autocomplete="off">';
			echo '<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
			
			$sqlsr='SELECT * FROM 1_gamit.invty_0shippingrate WHERE SRID IN (SELECT SRIDarr FROM ExplodedArray)';
			 $stmtsr=$link->query($sqlsr);
			$resultsr=$stmtsr->fetchAll();
			
			foreach($resultsr AS $ressr){
				echo '&nbsp; &nbsp; &nbsp; <input type="hidden" name="srid_'.$ressr['SRID'].'" checked> '.$ressr['RatePer'].' <input type="text" name="'.$ressr['SRID'].'" size="10"><br>';
			}
			echo 'WaybillNo: <input type="text" name="WaybillNo" size="10"><br>';
			echo 'NoOfBoxes: <input type="text" name="NoOfBoxes" size="10"><br>';
			echo 'DateOfShipment: <input type="date" name="DateOfShipment" value="'.date('Y-m-d').'"><br>';
			echo 'DeclaredValue: <input type="text" name="DeclaredValue" size="10"><br>';
			echo '<input type="submit" value="Add New" name="btnAdd"></div></form>';
		
		

			
					
				}
				 }
					$formdesc='</i><form action="#" method="POST">';
					if(!isset($_POST['btnShowAll'])){
						$formdesc.='<input type="submit" name="btnShowAll" value="Show All">';
					} else {
						$formdesc.='<input type="submit" name="btnShowDefault" value="Show Default">';
					}
					$formdesc.='</form><i>';
					
				$columnnames=$columnnameslist;
				if (allowedToOpen(20612,'1rtc')) {
					$delprocess='waybillslist.php?w=deletelists&TxnID=';
					$editprocess='waybillslist.php?w=EditSpecifics&TxnID='; $editprocesslabel='Edit';
				}
				$sql=$sql.$sqlcon.$addlsql.' ORDER BY DateOfShipment DESC,Branch;';
				// echo $sql;
				include_once('../backendphp/layout/displayastable.php');
				
				
				
    break;
	
	
	
	case 'AddNew':
	if (!allowedToOpen(20612,'1rtc')){ echo 'No Permission'; exit(); }
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='';
		
		$rateperarray='';
		foreach($_POST as $key => $value) {
			if (strpos($key, 'srid_') === 0 AND $key<>'') {
				// value starts with srid_
				$withval=str_replace('srid_','',$key);
				
				if($_POST[$withval]<>'' AND $_POST[$withval]<>0){
					$rateperarray.=$withval.'>'.$_POST[$withval].',';
				}
			}
		}
		$rateperarray=substr($rateperarray,0,-1);
		
		$sqlss='SELECT BranchNo,ShipperID FROM 1_gamit.invty_1shipperprice WHERE SPID = '.$_GET['SPID'].'';
		$stmtss=$link->query($sqlss);
		$resultss=$stmtss->fetch();
			
			
		$sql='INSERT INTO `invty_2waybills` SET SRIDAndValue="'.$rateperarray.'",NoOfBoxes="'.$_POST['NoOfBoxes'].'",DeclaredValue='.(!is_numeric($_POST['DeclaredValue'])?str_replace(',', '',$_POST['DeclaredValue']):$_POST['DeclaredValue']).',WaybillNo="'.$_POST['WaybillNo'].'",DateOfShipment="'.$_POST['DateOfShipment'].'",EncodedByNo='.$_SESSION['(ak0)'].', ShipperID='.$resultss['ShipperID'].',BranchNo='.$resultss['BranchNo'].', TimeStamp=Now()';
		
		
		
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: waybillslist.php');
	
	break;
		
	
	case 'EditSpecifics':
        if (allowedToOpen(20612,'1rtc')) {
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);
		echo '<title>'.$title.'</title>';
		echo '<h3>'.$title.'</h3>';
		$sql.=' WHERE fib.TxnID='.$txnid;
		
		
		$stmt=$link->query($sql);
			$result=$stmt->fetch();
			
			
			// echo $sql;
			echo '<div style="border:1px solid blue;width:30%;padding:3px;background-color:white;">Shipper: '.$result['Shipper'].'';
			echo '<br>Branch: '.$result['Branch'].'';
			echo '<br>EstAmtBreakDownNoInsurance: <br><div style="margin-left:2%;">'.$result['EstAmtBreakDownNoInsurance'].'</div>';
			echo 'EstAmt: <br><div style="margin-left:2%;">'.$result['EstAmt'].'</div>';
			echo 'WaybillNo: '.$result['WaybillNo'].'<br>';
			echo 'NoOfBoxes: '.$result['NoOfBoxes'].'<br>';
			echo 'DateOfShipment: '.$result['DateOfShipment'].'<br>';
			echo 'DeclaredValue: '.$result['DeclaredValue'].'';
			echo '</div>';
		echo '<form action="waybillslist.php?w=EditWB&TxnID='.$result['TxnID'].'" method="POST" autocomplete="off">';
			echo '<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><br>';
			echo 'Shipper: <input type="text" name="Shipper" autocomplete=off list="shipperlist" value="'.$result['Shipper'].'"><br>';
			echo 'Branch: <input type="text" name="Branch" size=15 list="branchlist" value="'.$result['Branch'].'"><br>';
			
			$sqlsr='SELECT * FROM 1_gamit.invty_0shippingrate WHERE SRID IN (SELECT SRIDarrsp1 FROM ExplodedArrayShipperPrice WHERE BranchNo='.$result['BranchNo'].' AND ShipperID='.$result['ShipperID'].')';
			 $stmtsr=$link->query($sqlsr);
			$resultsr=$stmtsr->fetchAll();
			
			foreach($resultsr AS $ressr){
				$sqls='SELECT SRIDarrwb1,Val FROM extrateandval'.$_SESSION['(ak0)'].' WHERE SRIDarrwb1='.$ressr['SRID'].'';
				$stmts=$link->query($sqls);
			    $results=$stmts->fetch();
				
				echo '&nbsp; &nbsp; &nbsp; <input type="hidden" name="srid_'.$ressr['SRID'].'" '.($ressr['SRID']==$results['SRIDarrwb1']?'checked':'').' checked> '.$ressr['RatePer'].' <input type="text" name="'.$ressr['SRID'].'" size="10" '.($ressr['SRID']==$results['SRIDarrwb1']?'value="'.$results['Val'].'"':'').'><br>';
			}
			
			
			
			$submitbutton='<input type="submit" value="Edit Data" name="btnSubmit"><br>';
		echo 'WaybillNo: <input type="text" name="WaybillNo" value="'.$result['WaybillNo'].'"><br>';
		echo 'NoOfBoxes: <input type="text" name="NoOfBoxes" value="'.$result['NoOfBoxes'].'"><br>';
		echo 'DateOfShipment: <input type="date" name="DateOfShipment" value="'.$result['DateOfShipment'].'"><br>';
		echo 'DeclaredValue: <input type="text" name="DeclaredValue" size="10" value="'.$result['DeclaredValue'].'"><br>'.$submitbutton.'</form>';
		
		
	} 
	break;
	 
	
	
	
	case 'EditWB':
		
		if (!allowedToOpen(20612,'1rtc')){ echo 'No Permission'; exit(); } 
		
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);
                
		
		$rateperarray='';
		foreach($_POST as $key => $value) {
			if (strpos($key, 'srid_') === 0 AND $key<>'') {
				// value starts with srid_
				$withval=str_replace('srid_','',$key);
				
				if($_POST[$withval]<>'' AND $_POST[$withval]<>0){
					$rateperarray.=$withval.'>'.$_POST[$withval].',';
				}
			}
		}
		$rateperarray=substr($rateperarray,0,-1);
		
		// echo $rateperarray;
		
		$sqlss='SELECT BranchNo,ShipperID FROM invty_2waybills WHERE TxnID = '.$txnid.'';
		$stmtss=$link->query($sqlss);
		$resultss=$stmtss->fetch();
			
			
		$sql='UPDATE `invty_2waybills` SET SRIDAndValue="'.$rateperarray.'",NoOfBoxes="'.$_POST['NoOfBoxes'].'",DeclaredValue='.(!is_numeric($_POST['DeclaredValue'])?str_replace(',', '',$_POST['DeclaredValue']):$_POST['DeclaredValue']).',WaybillNo="'.$_POST['WaybillNo'].'",DateOfShipment="'.$_POST['DateOfShipment'].'",EncodedByNo='.$_SESSION['(ak0)'].', ShipperID='.$resultss['ShipperID'].',BranchNo='.$resultss['BranchNo'].', TimeStamp=Now() WHERE TxnID='.$txnid;
		
		// echo $sql;
		// exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: waybillslist.php');
		
		
		
		
    break;
	
	case 'deletelists':
		if (!allowedToOpen(20612,'1rtc')){ echo 'No Permission'; exit(); }
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $txnid = intval($_GET['TxnID']);        
                       
			$sql='DELETE FROM `invty_2waybills` WHERE EncodedByNo='.$_SESSION['(ak0)'].' AND Encashed=0 AND TxnID='.$txnid;
			$stmt=$link->prepare($sql);
			$stmt->execute();
			header("Location:waybillslist.php?w=lists");
		 
    break;
	
	case 'Shipper':
	$title='List of Shippers'; 
			if (!allowedToOpen(20612,'1rtc')){ echo 'No Permission'; exit(); }
		$formdesc='Add New Shipper.';
		$method='post';
		$columnnames=array(
		array('field'=>'Shipper','type'=>'text','size'=>25,'required'=>true),
		array('field'=>'VAT','caption'=>'VAT (example: .12)','type'=>'text','size'=>10, 'value'=>'0','required'=>true)
		);
					
		$action='waybillslist.php?w=AddShipper'; $fieldsinrow=4; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');

		//Processes
		$delprocess='waybillslist.php?w=DeleteShipper&ShipperID=';

		$title=''; $formdesc='';
		$txnidname='TxnID';
		$columnnames=$columnnameslist;       
		if (allowedToOpen(20612,'1rtc')){ $editprocess='waybillslist.php?w=EditSpecificsShipper&ShipperID='; $editprocesslabel='Edit';}
		echo '<div style="width:45%">';
		include('../backendphp/layout/displayastable.php'); 
		echo '</div>';
	
	break;
	
	
	case 'AddShipper':
		if (allowedToOpen(20612,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql = 'INSERT INTO 1_gamit.invty_1shipper (Shipper,VAT, EncodedByNo, TimeStamp) VALUES (\''.$_POST['Shipper'].'\',\''.$_POST['VAT'].'\','.$_SESSION['(ak0)'].',Now())';
			$stmt=$link->prepare($sql); $stmt->execute();
		}
		header("Location:waybillslist.php?w=Shipper");
	break;
	
	case 'DeleteShipper':
	//access
        if (allowedToOpen(20612,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM 1_gamit.invty_1shipper WHERE ShipperID='.intval($_GET['ShipperID']);
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:waybillslist.php?w=Shipper");
    break;
	
	
	case 'EditSpecificsShipper':
        if (!allowedToOpen(20612,'1rtc')){ header("Location:".$_SERVER['HTTP_REFERER']);}
		$title='Edit Specifics';
		$txnid=intval($_GET['ShipperID']);

		//Condition For Edit Specifics
		$sql=$sql.' WHERE ShipperID='.$txnid;
		$columnstoedit=$columnstoadd;		
		$columnnames=$columnnameslist;
		
		$editprocess='waybillslist.php?w=EditShipper&ShipperID='.$txnid;		
		include('../backendphp/layout/editspecificsforlists.php');
	break;
	
	 case 'EditShipper':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(20612,'1rtc')){
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE 1_gamit.invty_1shipper SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE ShipperID='.intval($_GET['ShipperID']);
		$stmt=$link->prepare($sql); $stmt->execute();
		}
		header("Location:waybillslist.php?w=Shipper");
    break;
	
	case 'ShippingRate':
	$title='List of Rate per Unit'; 
			if (!allowedToOpen(20612,'1rtc')){ echo 'No Permission'; exit(); }
		$formdesc='Add New Rate per Unit.';
		$method='post';
		$columnnames=array(
		array('field'=>'RatePer','type'=>'text','size'=>25,'required'=>true)
		);
					
		$action='waybillslist.php?w=AddShippingRate'; $fieldsinrow=4; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');

		//Processes
		$delprocess='waybillslist.php?w=DeleteShippingRate&SRID=';

		$title=''; $formdesc='';
		$txnidname='TxnID';
		$columnnames=$columnnameslist;       
		if (allowedToOpen(20612,'1rtc')){ $editprocess='waybillslist.php?w=EditSpecificsShippingRate&SRID='; $editprocesslabel='Edit';}
		echo '<div style="width:45%">';
		include('../backendphp/layout/displayastable.php'); 
		echo '</div>';
	
	break;
	
	
	case 'AddShippingRate':
		if (allowedToOpen(20612,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql = 'INSERT INTO 1_gamit.invty_0shippingrate (RatePer) VALUES (\''.$_POST['RatePer'].'\')';
			$stmt=$link->prepare($sql); $stmt->execute();
		}
		header("Location:waybillslist.php?w=ShippingRate");
	break;
	
	case 'DeleteShippingRate':
	//access
        if (allowedToOpen(20612,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM 1_gamit.invty_0shippingrate WHERE SRID='.intval($_GET['SRID']).' AND RatePer NOT IN ("CBM","Kg")';
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:waybillslist.php?w=ShippingRate");
    break;
	
	
	case 'EditSpecificsShippingRate':
        if (!allowedToOpen(20612,'1rtc')){ header("Location:".$_SERVER['HTTP_REFERER']);}
		$title='Edit Specifics';
		$txnid=intval($_GET['SRID']);

		//Condition For Edit Specifics
		$sql=$sql.' WHERE SRID='.$txnid;
		$columnstoedit=$columnstoadd;		
		$columnnames=$columnnameslist;
		
		$editprocess='waybillslist.php?w=EditShippingRate&SRID='.$txnid;		
		include('../backendphp/layout/editspecificsforlists.php');
	break;
	
	 case 'EditShippingRate':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		if (allowedToOpen(20612,'1rtc')){
		$sql='';
		$sql='UPDATE 1_gamit.invty_0shippingrate SET RatePer="'.$_POST['RatePer'].'" WHERE SRID='.intval($_GET['SRID']);
		// echo $sql; print_r($_POST);
		$stmt=$link->prepare($sql); $stmt->execute();
		}
		header("Location:waybillslist.php?w=ShippingRate");
    break;
	
	
	case 'ShipperPrice':
		$title='List of Shipper Prices'; 
			if (!allowedToOpen(array(20612,20615),'1rtc')){ echo 'No Permission'; exit(); }
			$formdesc='Add New Shipper Price.';
			$method='post';
			if (allowedToOpen(20615,'1rtc')){
				goto noinput;
			}
			$title='List of Shipper Prices';
			echo '<title>'.$title.'</title>';
			echo '<h3>'.$title.'</h3>';
			echo '<form action="waybillslist.php?w=AddSP" method="POST" autocomplete="off">';
			echo '<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><br>';
			echo 'Shipper: <input type="text" name="Shipper" autocomplete=off list="shipperlist"><br>';
			echo 'Branch: <input type="text" name="Branch" size=15 list="branchlist"><br>';
			
			$sqlsr='SELECT * FROM 1_gamit.invty_0shippingrate';
			 $stmtsr=$link->query($sqlsr);
			$resultsr=$stmtsr->fetchAll();
			
			foreach($resultsr AS $ressr){
				echo '&nbsp; &nbsp; &nbsp; <input type="checkbox" name="srid_'.$ressr['SRID'].'"> '.$ressr['RatePer'].' <input type="text" name="'.$ressr['SRID'].'" size="10"><br>';
			}
			
			$submitbutton='<input type="submit" value="Add new" name="btnSubmit"><br>';
			echo '<div><b>For Kg:</b> 
        <label><input type="radio" name="sRadio" value="NoMinimum" checked> No Minimum Charge</label>
        <label><input type="radio" name="sRadio" value="WithMinimum"> With Minimum Charge</label>
    </div>
    <div class="NoMinimum box"></div>
    <div class="WithMinimum box">MinCharge: <input type="text" name="MinCharge"><br>CoveredByMinCharge: <input type="text" name="CoveredByMinCharge"><br></div>';
		echo 'InsurancePerDeclaredValue: <input type="text" name="InsurancePerDeclaredValue" placeholder=".01" size="10"><br>'.$submitbutton.'</form>';
			
			
			
			noinput:
			//Processes
			

			$title=''; $formdesc=''; 
			
			$txnidname='TxnID';
			$columnnames=$columnnameslist;       
			if (allowedToOpen(20612,'1rtc')){ $editprocess='waybillslist.php?w=EditSpecificsShipperPrice&SPID='; $editprocesslabel='Edit';
			$delprocess='waybillslist.php?w=DeleteShipperPrice&SPID=';
			}
			// echo '<div style="width:45%">';
			$sql.=' ORDER BY Shipper, Branch';
			include('../backendphp/layout/displayastable.php'); 
			// echo '</div>';
		
	
	break;
	
	case 'EditSpecificsShipperPrice':
        if (!allowedToOpen(20612,'1rtc')){ header("Location:".$_SERVER['HTTP_REFERER']);}
		$title='Edit Specifics';
		$txnid=intval($_GET['SPID']);
	echo '<title>'.$title.'</title>';
		//Condition For Edit Specifics
		$sql=$sql.' WHERE SPID='.$txnid;
		
		
		
		 $stmt=$link->query($sql);
			$result=$stmt->fetch();
			
			echo '<div style="border:1px solid blue;width:30%;padding:3px;background-color:white;">Shipper: '.$result['Shipper'].'';
			echo '<br>Branch: '.$result['Branch'].'';
			echo '<br>RateAndPrice: <br><div style="margin-left:2%;">'.$result['RateAndPrice'].'</div>';
			if($result['MinCharge']<>0){
				echo 'MinCharge: '.$result['MinCharge'].'';
				echo '<br>CoveredByMinCharge: '.$result['CoveredByMinCharge'].'<br>';
			}
			echo 'InsurancePerDeclaredValue: '.$result['InsurancePerDeclaredValue'].'';
			echo '</div>';
		echo '<form action="waybillslist.php?w=EditSP&SPID='.$result['SPID'].'" method="POST" autocomplete="off">';
			echo '<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><br>';
			echo 'Shipper: <input type="text" name="Shipper" autocomplete=off list="shipperlist" value="'.$result['Shipper'].'"><br>';
			echo 'Branch: <input type="text" name="Branch" size=15 list="branchlist" value="'.$result['Branch'].'"><br>';
			
			$sqlsr='SELECT * FROM 1_gamit.invty_0shippingrate';
			 $stmtsr=$link->query($sqlsr);
			$resultsr=$stmtsr->fetchAll();
			
			foreach($resultsr AS $ressr){
				$sqls='SELECT SRIDarr,Rate FROM ExplodedArray WHERE SRIDarr='.$ressr['SRID'];
				$stmts=$link->query($sqls);
			    $results=$stmts->fetch();
				// echo ;
				
				echo '&nbsp; &nbsp; &nbsp; <input type="checkbox" name="srid_'.$ressr['SRID'].'" '.($ressr['SRID']==$results['SRIDarr']?'checked':'').'> '.$ressr['RatePer'].' <input type="text" name="'.$ressr['SRID'].'" size="10" '.($ressr['SRID']==$results['SRIDarr']?'value="'.$results['Rate'].'"':'').'><br>';
			}
			
			$submitbutton='<input type="submit" value="Add new" name="btnSubmit"><br>';
			echo '<div><b>For Kg:</b> 
        <label><input type="radio" name="sRadio" value="NoMinimum" '.($result['MinCharge']==0?'checked':'').'> No Minimum Charge</label>
        <label><input type="radio" name="sRadio" value="WithMinimum"> With Minimum Charge</label>
    </div>
    <div class="NoMinimum box"></div>
    <div class="WithMinimum box">MinCharge: <input type="text" name="MinCharge" value="'.$result['MinCharge'].'"><br>CoveredByMinCharge: <input type="text" name="CoveredByMinCharge" value="'.$result['CoveredByMinCharge'].'"><br></div>';
	
		echo 'InsurancePerDeclaredValue: <input type="text" name="InsurancePerDeclaredValue" placeholder=".01" size="10" value="'.$result['InsurancePerDeclaredValue'].'"><br>'.$submitbutton.'</form>';
		
		
	break;
	
	
	
	
	case 'AddSP':
	if (!allowedToOpen(20612,'1rtc')){ echo 'No Permission'; exit(); } 
		
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                
		// $sql='';
		// foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		$rateperarray='';
		foreach($_POST as $key => $value) {
			if (strpos($key, 'srid_') === 0 AND $key<>'') {
				// value starts with srid_
				$withval=str_replace('srid_','',$key);
				$rateperarray.=$withval.'>'.$_POST[$withval].',';
			}
		}
		$rateperarray=substr($rateperarray,0,-1);
		// echo $rateperarray;
		
		
		
		if($_POST['sRadio']<>'NoMinimum'){
			$addsql='CoveredByMinCharge="'.$_POST['CoveredByMinCharge'].'",MinCharge="'.$_POST['MinCharge'].'",';
		}
		 
		$sql='INSERT INTO 1_gamit.invty_1shipperprice SET InsurancePerDeclaredValue="'.$_POST['InsurancePerDeclaredValue'].'",SRIDAndRate="'.$rateperarray.'",EncodedByNo='.$_SESSION['(ak0)'].','.$addsql.'ShipperID='.$shipperid.',BranchNo='.$branchno.', TimeStamp=Now()';
		
		
		// echo '<br><br>'.$sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		header("Location:waybillslist.php?w=ShipperPrice");
	
	
	break;
	
	
	
	case 'EditSP':
	if (!allowedToOpen(20612,'1rtc')){ echo 'No Permission'; exit(); } 
		
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                
		
		$rateperarray='';
		foreach($_POST as $key => $value) {
			if (strpos($key, 'srid_') === 0 AND $key<>'') {
				// value starts with srid_
				$withval=str_replace('srid_','',$key);
				$rateperarray.=$withval.'>'.$_POST[$withval].',';
			}
		}
		$rateperarray=substr($rateperarray,0,-1);
		
		
		
		if($_POST['sRadio']<>'NoMinimum'){
			$addsql='CoveredByMinCharge="'.$_POST['CoveredByMinCharge'].'",MinCharge="'.$_POST['MinCharge'].'",';
		}
		 
		$sql='UPDATE 1_gamit.invty_1shipperprice SET InsurancePerDeclaredValue="'.$_POST['InsurancePerDeclaredValue'].'",SRIDAndRate="'.$rateperarray.'",EncodedByNo='.$_SESSION['(ak0)'].','.$addsql.'ShipperID='.$shipperid.',BranchNo='.$branchno.', TimeStamp=Now() WHERE SPID='.intval($_GET['SPID']).'';
		
		
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		header("Location:waybillslist.php?w=ShipperPrice");
	
	
	break;
	
	
	case 'DeleteShipperPrice':
        if (allowedToOpen(20612,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$sql='DELETE FROM 1_gamit.invty_1shipperprice WHERE SPID='.intval($_GET['SPID']);
			// echo $sql; exit();
			$stmt=$link->prepare($sql); $stmt->execute();
		}
        header("Location:waybillslist.php?w=ShipperPrice");
    break;
	
	
	}
	
	$link=connect_db("".$currentyr."_1rtc",1);
	$sqldroptemp='DROP TABLE IF EXISTS extrateandval'.$_SESSION['(ak0)'].';';
	$stmtdroptable=$link->prepare($sqldroptemp); $stmtdroptable->execute();
	
	$link=null;	
?>	
	