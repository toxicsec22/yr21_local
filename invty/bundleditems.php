<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(6434,64346),'1rtc')) { echo 'No permission'; exit();}
include_once $path.'/acrossyrs/dbinit/userinit.php';

$which=(!isset($_GET['w'])?'lists':$_GET['w']);
if($which=='Lookup' AND (allowedToOpen(6434,'1rtc'))){
	$showbranches=true;
} else {
	$showbranches=false;
}
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

include_once('../backendphp/layout/linkstyle.php');
echo'</br>';
echo'<a id="link" href="bundleditems.php">Bundled Items</a>';
echo'</br>';


	if (in_array($which,array('lists','EditSpecifics','Lookup'))){
		$sql='SELECT BundleID,Posted,`BundleDescription`,ItemDesc,ValidFrom,ValidTo,i.ItemCode FROM invty_1bundleditems_main bim JOIN invty_1items i ON bim.ItemCode=i.ItemCode';
	}
		if (in_array($which,array('lists','EditSpecifics'))){
			echo comboBox($link,'SELECT * FROM `invty_1items` WHERE ItemCode>=30000;','ItemDesc','ItemCode','items');
			$columnnameslist=array('BundleID','BundleDescription','ItemDesc','ValidFrom','ValidTo'); 
			$columnstoadd=array('BundleID','BundleDescription','ValidFrom','ValidTo');
		}
		if (in_array($which,array('addlists','editlists'))){
			$columnstoadd=array('BundleID','BundleDescription','ValidFrom','ValidTo');
		}

	
	switch ($which){
		case 'lists':
			if (!allowedToOpen(array(6434,64346),'1rtc')) { echo 'No permission'; exit();}
				
				$title='Bundled Items';
				$method='post';
				
				$formdesc=''; echo '<br>';
				$columnnames=array(
				array('field'=>'BundleID','type'=>'number','size'=>15,'required'=>true,'list'=>'items'),
				array('field'=>'BundleDescription','required'=>true,'type'=>'text','size'=>15),
				array('field'=>'ValidFrom','type'=>'date','size'=>15,'required'=>true),
				array('field'=>'ValidTo','required'=>true,'type'=>'Date','size'=>15)
				);
				
				$action='bundleditems.php?w=addlists'; $liststoshow=array();
				
				$buttonval='Add New'; $modaltitle='Add New Bundle';
				if (allowedToOpen(64346,'1rtc')) {
					include('../backendphp/layout/inputmainformmodal.php');
					$title='';
				}
				
				$editprocess='bundleditems.php?w=Lookup&BundleID='; $editprocesslabel='Lookup';

				if (allowedToOpen(64346,'1rtc')) {
					$addlprocess='bundleditems.php?w=EditSpecifics&BundleID='; $addlprocesslabel='Edit';
					$delprocess='bundleditems.php?w=deletelists&BundleID='; $delprocesslabel='Delete'; 
				}
				
				$columnnames=$columnnameslist;   
				 $formdesc=''; $txnidname='BundleID';
				
						
				$sql=$sql.' ORDER BY BundleID';
                $width='85%';
				include('../backendphp/layout/displayastable.php');
    break;

		case 'addlists':
			if (!allowedToOpen(64346,'1rtc')) { echo 'No permission'; exit();}
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='INSERT INTO `invty_1bundleditems_main` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  Timestamp=Now(),ItemCode='.$_POST['BundleID'].'';
// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: bundleditems.php');
	
	break;
	
	case 'EditSpecifics':
		if (!allowedToOpen(64346,'1rtc')) { echo 'No permission'; exit();}
		$title='Edit Specifics';
		$txnid=intval($_GET['BundleID']);
		$sql=$sql.' WHERE BundleID='.$txnid;
		$columnstoedit=$columnstoadd;
			
		$columnswithlists=array('BundleID');
		$listsname=array('BundleID'=>'items');

		$columnnames=$columnnameslist;

		$editprocess='bundleditems.php?w=editlists&BundleID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	 
	break;
	
	
	case 'editlists':
		if (!allowedToOpen(64346,'1rtc')) { echo 'No permission'; exit();}
	
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['BundleID']);
		$sql='';

		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='UPDATE `invty_1bundleditems_main` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  Timestamp=Now(),ItemCode='.$txnid.' WHERE Posted=0 AND BundleID='.$txnid.' AND BundleID NOT IN (SELECT ItemCode FROM invty_2salesub WHERE ItemCode='.$txnid.')';

		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: bundleditems.php');
		
    break;


	case 'deletelists':
		if (!allowedToOpen(64346,'1rtc')) { echo 'No permission'; exit();}
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$txnid = intval($_GET['BundleID']);       
			$sql='DELETE FROM `invty_1bundleditems_main` WHERE Posted=0 AND BundleID='.$txnid.' AND EncodedByNo='.$_SESSION['(ak0)'].' AND BundleID NOT IN (SELECT ItemCode FROM invty_2salesub WHERE ItemCode='.$txnid.')';
			$stmt=$link->prepare($sql);
			$stmt->execute();
			header("Location:bundleditems.php?w=lists");
    break;



	case 'Lookup':
		if (!allowedToOpen(array(6434,64346),'1rtc')) { echo 'No permission'; exit();}
		 
	$txnid=intval($_GET['BundleID']);
	$sql.=' WHERE BundleID='.$txnid;
	$stmt=$link->query($sql); $result=$stmt->fetch();

	$title='Lookup Bundle';
	echo '<title>'.$title.'</title>';
	echo '<br><br><h3>'.$title.'</h3>';
	echo '<div style="border:1px solid black;background-color:lightyellow;width:30%;padding:5px;">';
	echo 'BundleID: '.$result['BundleID'].'<br>';
	echo 'Bundle Description: '.$result['BundleDescription'].'<br>';
	echo 'ItemDesc: '.$result['ItemDesc'].'<br>';
	echo 'Valid From: '.$result['ValidFrom'].'<br>';
	echo 'Valid To: '.$result['ValidTo'].'<br>';
    echo 'Posted: '.$result['Posted'].'<br>';
	echo '</div>';
	
	$sqlchecksale='SELECT COUNT(TxnSubID) AS cntsale FROM invty_2salesub WHERE ItemCode='.$txnid;
	$stmtchecksale=$link->query($sqlchecksale); $resultchecksale=$stmtchecksale->fetch();
	$cntsale=$resultchecksale['cntsale'];

	if($result['Posted']==0 AND $cntsale==0){
		if (allowedToOpen(64346,'1rtc')) {
		echo '<br><b>Add Item in Bundle</b>';
		echo '<div style="background-color:#ffffff;padding:5px;border:1px solid #000000;width:20%;"><form action="bundleditems.php?BundleID='.$txnid.'&w=EncodeItemCode&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off>
		Item Code: <input type="text" name="ItemCode" placeholder="ItemCode" size="6" required> &nbsp; &nbsp;Qty: <input type="text" name="Qty" placeholder="" size="3" required><br>
		DiscountedPriceLevel<b>1</b>: <input type="text" name="DiscountedPriceLevel1" placeholder="" size="8" required>
		<br>
		DiscountedPriceLevel<b>2</b>: <input type="text" name="DiscountedPriceLevel2" placeholder="" size="8" required> <br>
		DiscountedPriceLevel<b>3</b>: <input type="text" name="DiscountedPriceLevel3" placeholder="" size="8" required> <br>
		DiscountedPriceLevel<b>4</b>: <input type="text" name="DiscountedPriceLevel4" placeholder="" size="8" required> <br>
		DiscountedPriceLevel<b>5</b>: <input type="text" name="DiscountedPriceLevel5" placeholder="" size="8" required> <br><input type="submit" name="btnSubmit" value="Add Item Code" style="background-color:blue;color:#fff;padding:2px;">
		</form></div>';
	

		
			$delprocess='bundleditems.php?w=DeleteItemCode&BundleID='.$txnid.'&BSID='; $delprocesslabel='Delete'; 
		
		}
	}

	if (allowedToOpen(64346,'1rtc')) {			
	$columnnames=array('ItemCode','Category','ItemDesc','Qty','DiscountedPriceLevel1','DiscountedPriceLevel2','DiscountedPriceLevel3','DiscountedPriceLevel4','DiscountedPriceLevel5');
	
		$formdesc='</i><form action="bundleditems.php?w=PostUnpost&BundleID='.$txnid.'&action_token='.$_SESSION['action_token'].'" method="POST"><input type="submit" name="btnPostUnpost" value="'.($result['Posted']==1?'Unpost':'Post as Final').'" '.($result['Posted']==1?'':'OnClick="return confirm(\'Is this Final?\');"').'></form><i>';
	 } elseif (allowedToOpen(64347,'1rtc')) {
		$columnnames=array('ItemCode','Category','ItemDesc','Qty','DiscountedPrice-'.$_SESSION['@brn'].'','DiscountedPriceLevel1','DiscountedPriceLevel2','DiscountedPriceLevel3','DiscountedPriceLevel4','DiscountedPriceLevel5');
	 } else {
		$columnnames=array('ItemCode','Category','ItemDesc','Qty','DiscountedPrice');
	 }

	 $title=''; 
	$txnidname='BSID';
	
    $sql='SELECT BSID,(SELECT 
	(CASE 
				WHEN
					PriceLevel = 1 THEN bis.DiscountedPriceLevel1
				WHEN
					PriceLevel = 2 THEN bis.DiscountedPriceLevel2
				WHEN
					PriceLevel = 3 THEN bis.DiscountedPriceLevel3
				WHEN
					PriceLevel = 4 THEN bis.DiscountedPriceLevel4
				ElSE
				bis.DiscountedPriceLevel5
			END)
FROM `1branches` b1 where b1.BranchNo='.$_SESSION['bnum'].'
) AS `DiscountedPrice`,(SELECT DiscountedPrice) AS `DiscountedPrice-'.$_SESSION['@brn'].'`,ItemDesc,Category,bis.ItemCode,Qty,`DiscountedPriceLevel1`,`DiscountedPriceLevel2`,`DiscountedPriceLevel3`,`DiscountedPriceLevel4`,`DiscountedPriceLevel5` FROM invty_1bundleditems_sub bis JOIN invty_1items i ON bis.ItemCode=i.ItemCode JOIN invty_1category c ON i.CatNo=c.CatNo WHERE BundleID='.$txnid;
	


	if(((allowedToOpen(64346,'1rtc')) AND $result['Posted']==0) AND $cntsale==0){
		$columnstoedit=array('Qty','DiscountedPriceLevel1','DiscountedPriceLevel2','DiscountedPriceLevel3','DiscountedPriceLevel4','DiscountedPriceLevel5');
		$editprocess='bundleditems.php?w=EditPrice&BundleID='.$txnid.'&BSID='; $editprocesslabel='Enter';
		include_once('../backendphp/layout/displayastableeditcells.php');
	} else {
		$columnstoedit=array();
		
			include_once('../backendphp/layout/displayastablenosort.php');
		
	}

	$sqltotalbundle='SELECT SUM(DiscountedPriceLevel1) AS TotalDiscountedPriceLevel1,SUM(DiscountedPriceLevel2) AS TotalDiscountedPriceLevel2,SUM(DiscountedPriceLevel3) AS TotalDiscountedPriceLevel3,SUM(DiscountedPriceLevel4) AS TotalDiscountedPriceLevel4,SUM(DiscountedPriceLevel5) AS TotalDiscountedPriceLevel5 FROM invty_1bundleditems_sub WHERE BundleID='.$txnid;
	$stmttotalbundle=$link->query($sqltotalbundle); $resulttotalbundle=$stmttotalbundle->fetch();
	
	if (allowedToOpen(array(64346,64347),'1rtc')){
	echo '<br><br><div style="background-color:#ffffff;border:1px solid #000000;padding:4px;width:15%;">';
	echo '<b>Overall Total</b><br>Price Level 1: <b>'.$resulttotalbundle['TotalDiscountedPriceLevel1'].'</b><br>';
	echo 'Price Level 2: <b>'.$resulttotalbundle['TotalDiscountedPriceLevel2'].'</b><br>';
	echo 'Price Level 3: <b>'.$resulttotalbundle['TotalDiscountedPriceLevel3'].'</b><br>';
	echo 'Price Level 4: <b>'.$resulttotalbundle['TotalDiscountedPriceLevel4'].'</b><br>';
	echo 'Price Level 5: <b>'.$resulttotalbundle['TotalDiscountedPriceLevel5'].'</b></div>';
}
	break;

	case 'EditPrice':
		if (!allowedToOpen(64346,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid=intval($_GET['BundleID']);
		
		$sql='UPDATE `invty_1bundleditems_sub` SET EncodedByNo='.$_SESSION['(ak0)'].',Qty='.$_POST['Qty'].',DiscountedPriceLevel1='.$_POST['DiscountedPriceLevel1'].',DiscountedPriceLevel2='.$_POST['DiscountedPriceLevel2'].',DiscountedPriceLevel3='.$_POST['DiscountedPriceLevel3'].',DiscountedPriceLevel4='.$_POST['DiscountedPriceLevel4'].',DiscountedPriceLevel5='.$_POST['DiscountedPriceLevel5'].',Timestamp=Now() WHERE BSID='.intval($_GET['BSID']);
	
		$stmt=$link->prepare($sql); $stmt->execute();

		header('Location: bundleditems.php?w=Lookup&BundleID='.$txnid.'');

	break;


	case 'DeleteItemCode':
		if (!allowedToOpen(64346,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnsubid = intval($_GET['BSID']);		
		$sql='DELETE FROM `invty_1bundleditems_sub` WHERE BSID='.$txnsubid;
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:bundleditems.php?w=Lookup&BundleID=".$_GET['BundleID']."");
	break;

	break;

	case 'EncodeItemCode':
		if (!allowedToOpen(64346,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid=intval($_GET['BundleID']);
		
		$sql='INSERT INTO `invty_1bundleditems_sub` SET EncodedByNo='.$_SESSION['(ak0)'].', Qty='.$_POST['Qty'].',ItemCode='.$_POST['ItemCode'].',DiscountedPriceLevel1='.$_POST['DiscountedPriceLevel1'].',DiscountedPriceLevel2='.$_POST['DiscountedPriceLevel2'].',DiscountedPriceLevel3='.$_POST['DiscountedPriceLevel3'].',DiscountedPriceLevel4='.$_POST['DiscountedPriceLevel4'].',DiscountedPriceLevel5='.$_POST['DiscountedPriceLevel5'].',BundleID='.$txnid.',Timestamp=Now()';
		
		$stmt=$link->prepare($sql); $stmt->execute();

		header('Location: bundleditems.php?w=Lookup&BundleID='.$txnid.'');
	break;

    case 'PostUnpost':
        if (!allowedToOpen(64346,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $txnid=intval($_GET['BundleID']);
		
        $sql='UPDATE `invty_1bundleditems_main` SET Posted=IF(Posted=0,1,0),PostedByNo='.$_SESSION['(ak0)'].',PostedTS=Now() WHERE BundleID='.$txnid.' AND BundleID NOT IN (SELECT ItemCode FROM invty_2salesub WHERE ItemCode='.$txnid.')';
		
		$stmt=$link->prepare($sql); $stmt->execute();
        header('Location: bundleditems.php?w=Lookup&BundleID='.$txnid.'');

    break;


	}

?>