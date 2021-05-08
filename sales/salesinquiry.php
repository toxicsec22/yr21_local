<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

include_once('../backendphp/layout/linkstyle.php');
echo'</br>';
echo'<a id="link" href="salesinquiry.php">Online Leads Monitoring</a> <a id="link" href="salesinquiry.php?w=ListofPlatforms">List of Platforms</a> '.str_repeat('&nbsp',5).'';
echo'</br>';

	$which=(!isset($_GET['w'])?'lists':$_GET['w']);

	if (in_array($which,array('lists','EditSpecifics','Lookup'))){
		$sql='SELECT s.*,Platform,`Description`,CityOrProvince AS Municipality,IF(s.CatNo=-1,"OTHERS",Category) AS Category,(SELECT GROUP_CONCAT(InquiryStatus) AS `Status` FROM sales_2inquirysub `isub` JOIN sales_0inquirystat istat ON isub.ISID=istat.ISID WHERE TxnID=s.TxnID) AS Stats FROM sales_2inquiry s JOIN sales_1platform f ON s.PID=f.PID JOIN 1_gamit.0cityorprovince cp ON cp.CPID=s.MunicipalityID LEFT JOIN invty_1category c ON c.CatNo=s.CatNo';
	}
		if (in_array($which,array('lists','EditSpecifics'))){
			echo comboBox($link,'SELECT PID,Platform FROM sales_1platform ORDER BY Platform','PID','Platform','platformlist');

			echo comboBox($link,'SELECT ClientName,ClientName FROM sales_2inquiry ORDER BY ClientName','ClientName','ClientName','clientlist');

			echo comboBox($link,'SELECT CatNo,Category FROM invty_1category UNION SELECT -1, "OTHERS" ORDER BY Category;','CatNo','Category','categorylist');

			echo comboBox($link,'SELECT CPID,CityOrProvince AS Municipality FROM 1_gamit.0cityorprovince cp ORDER BY CityOrProvince;','CPID','Municipality','municipalitylist');

			$columnnameslist=array('Platform','DateInquired','ClientName', 'ClientContactNo','Municipality','Category','ClientInquiry','Remarks','Stats'); 
			$columnstoadd=array('Platform','DateInquired','ClientName', 'ClientContactNo','Municipality','Category','ClientInquiry','Remarks');
		}
		if (in_array($which,array('addlists','editlists'))){
			$columnstoadd=array('DateInquired','ClientName', 'ClientContactNo','ClientInquiry','Remarks'); 
			$pid=comboBoxValue($link,'sales_1platform','Platform',addslashes($_POST['Platform']),'PID');
			$cpid=comboBoxValue($link,'1_gamit.0cityorprovince','CityOrProvince',addslashes($_POST['Municipality']),'CPID');
			if($_POST['Category']=="OTHERS"){
			$catno=-1;
			} else {
				$catno=comboBoxValue($link,'invty_1category','Category',addslashes($_POST['Category']),'CatNo');
			}
		
		}

		if (in_array($which,array('ListofPlatforms','EditSpecificsPlatform'))){
			$sql='SELECT *,Platform,PID AS TxnID FROM sales_1platform';
			$columnnameslist=array('Platform','Description'); 
			$columnstoadd=array('Platform','Description');
		}

	
	switch ($which){
		case 'lists':
			if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		$title='Online Leads Monitoring';
				$method='post';
				
				$formdesc=''; echo '<br>';
			
				
				$columnnames=array(
				array('field'=>'Platform','type'=>'text','list'=>'platformlist','size'=>15,'required'=>true),
				array('field'=>'DateInquired','caption'=>'Date Inquired','type'=>'date','size'=>15,'required'=>true),
				array('field'=>'ClientName','caption'=>'Client Name','list'=>'clientlist','type'=>'text','size'=>15,'required'=>true),
                array('field'=>'ClientContactNo','caption'=>'Contact Number','type'=>'text','size'=>15,'required'=>false),
				array('field'=>'Municipality','list'=>'municipalitylist','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'Category','list'=>'categorylist','type'=>'text','size'=>15,'required'=>true),
                array('field'=>'ClientInquiry','caption'=>'Inquiry','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'Remarks','type'=>'text','size'=>20,'required'=>false),
				);
				
				$action='salesinquiry.php?w=addlists'; $liststoshow=array();
				
				$buttonval='Add New'; $modaltitle='Add New Inquiry';
				include('../backendphp/layout/inputmainformmodal.php');
				
				$editprocess='salesinquiry.php?w=Lookup&TxnID='; $editprocesslabel='Lookup';
				$addlprocess='salesinquiry.php?w=EditSpecifics&TxnID='; $addlprocesslabel='Edit';
				$delprocess='salesinquiry.php?w=deletelists&TxnID='; $delprocesslabel='Delete'; 
				
				
						
				$columnnames=$columnnameslist;   
				$title=''; $formdesc=''; $txnidname='TxnID';
				
						
				$sql=$sql.' ORDER BY DateInquired DESC';
			
				include('../backendphp/layout/displayastable.php');
    break;

		case 'addlists':
			if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='INSERT INTO `sales_2inquiry` SET PID='.$pid.',CatNo='.$catno.',MunicipalityID='.$cpid.',EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  Timestamp=Now()';
// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: salesinquiry.php');
	
	break;
	
	case 'EditSpecifics':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);
		$sql=$sql.' WHERE s.TxnID='.$txnid;
		$columnstoedit=$columnstoadd;
			
		$columnswithlists=array('Platform','Category','Municipality','ClientName');
		$listsname=array('Platform'=>'platformlist','Category'=>'categorylist','Municipality'=>'municipalitylist','ClientName'=>'clientlist');
		
		$columnnames=$columnnameslist;

		$editprocess='salesinquiry.php?w=editlists&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	 
	break;
	
	
	case 'editlists':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='UPDATE `sales_2inquiry` SET PID='.$pid.',CatNo='.$catno.',MunicipalityID='.$cpid.',EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.'  Timestamp=Now() WHERE TxnID='.$txnid;
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: salesinquiry.php');
		
    break;


	case 'deletelists':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$txnid = intval($_GET['TxnID']);       
			$sql='DELETE FROM `sales_2inquiry` WHERE TxnID='.$txnid;
			$stmt=$link->prepare($sql);
			$stmt->execute();
			header("Location:salesinquiry.php?w=lists");
    break;



	case 'Lookup':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
	$txnid=intval($_GET['TxnID']);
	$sql.=' WHERE s.TxnID='.$txnid;
	$stmt=$link->query($sql); $result=$stmt->fetch();

	$title='Lookup Client Inquiry';
	echo '<title>'.$title.'</title>';
	echo '<br><br><h3>'.$title.'</h3>';
	echo '<div style="border:1px solid black;background-color:#fff;width:30%;padding:5px;">';
	echo 'Platform: '.$result['Platform'].($result['Description']<>''?' ('.$result['Description'].')':'').'<br>';
	echo 'DateInquired: '.$result['DateInquired'].'<br>';
	echo 'ClientName: '.$result['ClientName'].'<br>';
	echo 'ContactNo: '.$result['ClientContactNo'].'<br>';
	echo 'Municipality: '.$result['Municipality'].'<br>';
	echo 'Category: '.$result['Category'].'<br>';
	echo 'ClientInquiry:<br>&nbsp; &nbsp; &nbsp; '.$result['ClientInquiry'].'<br>';
	echo 'Remarks: '.$result['Remarks'].'<br>';
	echo '</div>';
	
	
	$sqlis='SELECT ISID,InquiryStatus FROM sales_0inquirystat;';
	$stmtis=$link->query($sqlis); $resultis=$stmtis->fetchAll();
	$optionis='';
	foreach($resultis AS $res){
		$optionis.='<option value="'.$res['ISID'].'">'.$res['InquiryStatus'].'</option>';
	}

	echo '<br><b>Inquiry Status</b>';
	echo '<form action="salesinquiry.php?TxnID='.$txnid.'&w=EncodeStatus&action_token='.$_SESSION['action_token'].'" method="POST" autocomplete=off>
	Status: <select name="ISID"><option>-- Select --</option>'.$optionis.'</select> Remarks: <input type="text" name="Remarks" placeholder="remarks" size="25" required> <input type="submit" name="btnSubmit" value="Encode Status">
	</form>';


	$sql='SELECT TxnSubID AS TxnID,Remarks,InquiryStatus AS `Status` FROM sales_2inquirysub `isub` JOIN sales_0inquirystat istat ON isub.ISID=istat.ISID WHERE TxnID='.$txnid.';';


	$delprocess='salesinquiry.php?w=DeleteStat&TxnID='.$txnid.'&TxnSubID='; $delprocesslabel='Delete'; 
		
				
	$columnnames=array('Status','Remarks');   
	$title=''; $formdesc=''; $txnidname='TxnID';


	include('../backendphp/layout/displayastablenosort.php');

	break;


	case 'DeleteStat':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnsubid = intval($_GET['TxnSubID']);		
		$sql='DELETE FROM `sales_2inquirysub` WHERE TxnSubID='.$txnsubid;
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:salesinquiry.php?w=Lookup&TxnID=".$_GET['TxnID']."");
	break;

	break;

	case 'EncodeStatus':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$txnid=intval($_GET['TxnID']);
		$sqlc='SELECT TxnSubID FROM sales_2inquirysub WHERE ISID='.$_POST['ISID'].' AND TxnID='.$txnid.';';
		
		$stmtc=$link->query($sqlc);
	

		if($stmtc->rowCount()==0){
			$sql='INSERT INTO `sales_2inquirysub` SET EncodedByNo='.$_SESSION['(ak0)'].', ISID='.$_POST['ISID'].',Remarks="'.addslashes($_POST['Remarks']).'", TxnID='.$txnid.', Timestamp=Now()';
		} else {
			$sql='UPDATE `sales_2inquirysub` SET EncodedByNo='.$_SESSION['(ak0)'].',Remarks="'.addslashes($_POST['Remarks']).'", Timestamp=Now() WHERE ISID='.$_POST['ISID'].' AND TxnID='.$txnid.'';
		}
		$stmt=$link->prepare($sql); $stmt->execute();

		header('Location: salesinquiry.php?w=Lookup&TxnID='.$txnid.'');
	break;



	case 'ListofPlatforms':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		$title='List of Platforms';
		$method='post';
		$formdesc=''; echo '<br>';
		$columnnames=array(
		array('field'=>'Platform','type'=>'text','size'=>15,'required'=>true),
		array('field'=>'Description','type'=>'text','size'=>15,'required'=>true)
		);
		
		$action='salesinquiry.php?w=AddPlatform'; $liststoshow=array();
		
		$buttonval='Add New'; $modaltitle='Add New Platform';
		include('../backendphp/layout/inputmainformmodal.php');
			
		$addlprocess='salesinquiry.php?w=EditSpecificsPlatform&TxnID='; $addlprocesslabel='Edit';
		$delprocess='salesinquiry.php?w=DeletePlatform&TxnID='; $delprocesslabel='Delete'; 
		
				
		$columnnames=$columnnameslist;   
		$title=''; $formdesc=''; $txnidname='TxnID';
		
		include('../backendphp/layout/displayastablenosort.php');
    break;


	case 'AddPlatform':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';	
		$sql='INSERT INTO `sales_1platform` SET EncodedByNo='.$_SESSION['(ak0)'].', Platform="'.$_POST['Platform'].'",Description="'.addslashes($_POST['Description']).'", Timestamp=Now()';
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: salesinquiry.php?w=ListofPlatforms');
	break;


	case 'EditSpecificsPlatform':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);
		$sql=$sql.' WHERE PID='.$txnid; 
		$columnstoedit=$columnstoadd;
		$columnnames=$columnstoadd;
		
		$editprocess='salesinquiry.php?w=EditPlatform&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	break;


	case 'EditPlatform':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);
		$sql='UPDATE `sales_1platform` SET Platform="'.$_POST['Platform'].'",Description="'.addslashes($_POST['Description']).'",EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE PID='.$txnid;
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header('Location: salesinquiry.php?w=ListofPlatforms');
		
    break;

	case 'DeletePlatform':
		if (!allowedToOpen(605,'1rtc')) { echo 'No permission'; exit();}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);		
		$sql='DELETE FROM `sales_1platform` WHERE PID='.$txnid;
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:salesinquiry.php?w=ListofPlatforms");
	break;

	}
	
		
		
?>