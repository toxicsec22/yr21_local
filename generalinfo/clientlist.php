<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';  
if (!allowedToOpen(array(2,7,8,9,10,11,91,1111,1112,1113,1114),'1rtc')) { echo 'No permission'; exit();}
if($_GET['w']=='PurgeInactiveClients' OR $_GET['w']=='RestorePurgedClients' OR $_GET['w']=='RecommendedClients'){
	$showbranches=false; 
} else {
	$showbranches=true; 
}
include_once('../switchboard/contents.php');        
 include_once 'trailgeninfo.php';
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=(!isset($_GET['w'])?'List':$_GET['w']);

 include_once('../backendphp/layout/linkstyle.php');
    echo '</br>';
    ?>
<!--buttons -->
    <div>
    <font size=4 face='sans-serif'>
        <?php if (allowedToOpen(8,'1rtc')) {?> 
        <a id="link" href='clientlist.php?per=branch'>Client List per Branch</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
        
        <?php if (allowedToOpen(7,'1rtc')) {?>
        <a id="link" href='clientlist.php?per=all'>All Clients</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>

        <?php if (allowedToOpen(3,'1rtc')) {?> 
        <a id="link" href='addentrygeninfo.php?w=ClientBranch'>Assigned Branches per Client</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
        
        <?php if (allowedToOpen(31,'1rtc')) {?>
        <a id='link' href='clientlist.php?w=ClientsNoBranch'>Clients with no Assigned Branches</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
		
        <?php if (allowedToOpen(1117,'1rtc')) {?>
        <a id='link' href='clientlist.php?w=RecommendedClients'>Recommended Clients</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
		
		<?php if (allowedToOpen(9,'1rtc')) {?>
        <a id='link' href='addentrygeninfo.php?w=Client'>Add Client</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
		
		<?php if (allowedToOpen(2,'1rtc')) {?>
        <a id='link' href='clientlist.php?w=List&per=Key'>Key Accounts</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
		
		<?php if (allowedToOpen(91,'1rtc')) {?>
        <a id='link' href='clientlist.php?w=PurgeInactiveClients'>Purge Inactive Clients</a><?php echo str_repeat('&nbsp',5)?>
		<a id='link' href='clientlist.php?w=RestorePurgedClients'>Restore Purged Clients</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>

        
    </font></div><br>
    <?php

    $title='List of Clients';
    
    
    
    $txnid='ClientNo';
    
switch ($which){
   case 'List': 
       $formdesc='';
	   // $purgerestoreurl='<a href="clientlist.php?w=PurgeInactiveClients">Purge Inactive Clients</a>&nbsp &nbsp &nbsp &nbsp<a href="clientlist.php?w=RestorePurgedClients">Restore Purged Clients</a>';
       // $formdesc=(allowedToOpen(2,'1rtc'))?'<a href="clientlist.php?w=List&per=Key">Key Accounts</a>&nbsp &nbsp &nbsp &nbsp'.(allowedToOpen(91,'1rtc')?$purgerestoreurl:'').'':null;
       // $formdesc=(allowedToOpen(2,'1rtc'))?'<a href="clientlist.php?w=List&per=Key">Key Accounts</a>&nbsp &nbsp &nbsp &nbsp':null;
       if ($_GET['per']=='Key'){ $condition=' AND ClientClass=1 ';} else { $condition='';}
    if ($_GET['per']=='branch'){
      
       $sql='SELECT `c`.*,if(c.ClientNo in (Select NewClientNo from 1clientsnewname),CONCAT(ClientName,\' \',"<font size=1pt> (Formerly ",(SELECT ClientName from 1clients where ClientNo=cnn.OldClientNo),")</font>"),ClientName) AS ClientName, IF(c.ClientNo IN (SELECT EmployedByClientNo FROM gen_info_1technicians),(SELECT GROUP_CONCAT(CONCAT(TechName," ",MobileNo," ",IFNULL(`Position`,"")) SEPARATOR "<br><br>") FROM gen_info_1technicians WHERE EmployedByClientNo=c.ClientNo),"") AS OtherNumbers,if(ARClientTypeID=0,\'\',ARClientDesc) as ARClientType, CASE WHEN ClientClass=1 THEN "KeyAccount" WHEN ClientClass=2 THEN "Strategic Account" ELSE 0 end as ClientClass, `c`.ClientNo AS TxnID, VatType ,ClientTypeShortName as ClientType FROM `1clients` c left join gen_info_0clienttype ct on ct.ClientTypeID=c.ClientType left join gen_info_0arclienttype act on act.ARClientTypeID=c.ARClientType INNER JOIN `gen_info_1branchesclientsjxn` ON `c`.ClientNo = `gen_info_1branchesclientsjxn`.ClientNo
        JOIN `gen_info_1vattype` v ON c.VatTypeNo=v.VatTypeNo left join 1clientsnewname cnn on cnn.NewClientNo=c.ClientNo
      WHERE (((`c`.ClientNo)>99) AND (`c`.ClientNo NOT IN (10000,10001,10004)) AND ((`gen_info_1branchesclientsjxn`.BranchNo)=' . $_SESSION['bnum'] . ')) '.$condition.' ORDER BY ClientName;';
	  // echo $sql; exit();
       $columnnames=array('ClientNo', 'ClientName', 'TelNo1', 'TelNo2', 'Mobile','OtherNumbers','ContactPerson','EmailAddress','ARClientType','ClientType','Terms','CreditLimit',  'PORequired','VatType','TIN','Remarks','StreetAddress','Barangay','TownOrCity','Province','Inactive','ClientSince');
        
    } else { 
       if (allowedToOpen(7,'1rtc')) { 
       $sql='SELECT c.*,if(c.ClientNo in (Select NewClientNo from 1clientsnewname),CONCAT(ClientName,\' \',"<font size=1pt> (Formerly ",(SELECT ClientName from 1clients where ClientNo=cnn.OldClientNo),")</font>"),ClientName) AS ClientName,if(ARClientTypeID=0,\'\',ARClientDesc) as ARClientType, c.ClientNo AS TxnID,ClientTypeShortName as ClientType FROM `1clients` c left join gen_info_0clienttype ct on ct.ClientTypeID=c.ClientType left join gen_info_0arclienttype act on act.ARClientTypeID=c.ARClientType left join 1clientsnewname cnn on cnn.NewClientNo=c.ClientNo WHERE (((c.ClientNo)>99) AND (c.ClientNo NOT IN (10000,10001,10004))) '.$condition.' ORDER BY ClientName;';
	   // echo $sql;
       $columnnames=array('ClientNo', 'ClientName', 'TelNo1', 'Mobile','ContactPerson','EmailAddress','ARClientType','ClientType','Remarks','Province','Inactive','ClientSince');	
       } else { echo 'No permission'; exit();}
    }

$editprocess='clientlist.php?w=EditSpecifics&ClientNo='; $editprocesslabel='Edit';
if (allowedToOpen(9,'1rtc')){
              $showbranches=true;
            if ($_GET['per']=='branch'){
              array_push($columnnames,'ClientClass');
               
              } 
			  // else { $formdesc=$formdesc.'&nbsp &nbsp &nbsp &nbsp<a href=addentrygeninfo.php?w=Client target=_blank>Add Client</a>&nbsp &nbsp &nbsp &nbsp<a href=addentrygeninfo.php?w=ClientBranch target=_blank>Assign Client to Branches</a>&nbsp &nbsp &nbsp &nbsp'.(allowedToOpen(91,'1rtc')?$purgerestoreurl:'').'';}    
            } elseif(allowedToOpen(10,'1rtc')){
              $showbranches=true;
            if ($_GET['per']=='branch'){$columnstoedit=array('ClientName','TelNo1', 'TelNo2', 'Mobile','ContactPerson','EmailAddress','TIN','StreetAddress', 'Barangay', 'TownOrCity','Province','Remarks', 'Inactive');     } 
            } else {
              $showbranches=false;
                $columnstoedit=array();
            }

        include('../backendphp/layout/displayastable.php');

  break;
case 'EditSpecifics':

// print_r($arr);
   if (allowedToOpen(11,'1rtc')){
	   echo comboBox($link,'SELECT `ARClientTypeID`, `ARClientDesc` FROM `gen_info_0arclienttype`','ARClientTypeID','ARClientDesc','ARClientLists');
	   echo comboBox($link,'SELECT `ClientTypeID`, `ClientDesc` FROM `gen_info_0clienttype`','ClientTypeID','ClientDesc','ClientLists');
   $title='Edit Client Specifics';
	 $txnid=intval($_GET['ClientNo']); $main='1clients';
         echo comboBox($link,'SELECT "Yes" AS YesNo, 1 AS YesNoValue UNION SELECT "No" AS YesNo, 0 AS YesNoValue','YesNoValue','YesNo','yesno');
         echo comboBox($link,'SELECT `VatTypeNo`, `VatType` FROM `gen_info_1vattype`','VatTypeNo','VatType','vattype');
	 $sql='SELECT `c`.*, if(ARClientTypeID=0,\'\',ARClientDesc) as ARClientType, IF(PORequired=1,"Yes","No") AS PORequired, IF(Inactive=1,"Yes","No") AS Inactive, VatType,ClientTypeShortName as ClientType FROM `1clients` c left join gen_info_0clienttype ct on ct.ClientTypeID=c.ClientType left join gen_info_0arclienttype act on act.ARClientTypeID=c.ARClientType JOIN `gen_info_1vattype` v ON c.VatTypeNo=v.VatTypeNo WHERE ClientNo='.$txnid;
         $columnstoedit=array('ClientName', 'TelNo1', 'TelNo2', 'Mobile','ContactPerson', 'EmailAddress', 'ARClientType','ClientType','Terms','CreditLimit', 'PORequired','VatType','TIN','StreetAddress', 'Barangay', 'TownOrCity','Province', 'Remarks', 'Inactive');
        
		
         if(allowedToOpen(10,'1rtc')){$columnstoedit=array('ClientName','TelNo1', 'TelNo2', 'Mobile','ContactPerson','EmailAddress','ClientType','TIN','StreetAddress', 'Barangay', 'TownOrCity','Province','Remarks', 'Inactive');}
		 if ((allowedToOpen(2,'1rtc')) OR (allowedToOpen(12,'1rtc'))){ $columnstoedit[]='ClientClass';}
	 $columnnames=$columnstoedit;
	 
	 $columnswithlists=array('ARClientType','ClientType','VatType','Inactive');
         $listsname=array('ARClientType'=>'ARClientLists','ClientType'=>'ClientLists','VatType'=>'vattype','Inactive'=>'yesno');
	 $editprocess='clientlist.php?w=Edit&ClientNo='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
    } else { echo 'No permission'; exit();}     
   break;
   
case 'Edit':
    if (allowedToOpen(11,'1rtc')){ 
    $txnid=$_REQUEST['ClientNo'];
	
	function getYesNo($yesno){
		if (strtolower($yesno)=='yes') { return 1;} else {return 0;}
	}
	
	if (allowedToOpen(1111,'1rtc')){
            $columnstoedit=array('ClientName', 'TelNo1', 'TelNo2', 'Mobile','ContactPerson', 'EmailAddress','Terms',  'PORequired','TIN','StreetAddress', 'Barangay', 'TownOrCity','Province', 'Remarks', 'Inactive');
	    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	    $vattypeno=comboBoxValue($link,'`gen_info_1vattype`','VatType',addslashes($_POST['VatType']),'VatTypeNo');
		$arclient=comboBoxValue($link, 'gen_info_0arclienttype', 'ARClientDesc', $_REQUEST['ARClientType'], 'ARClientTypeID');
		$sqlarclient=',ARClientType=\''.$arclient.'\'';
		$client=comboBoxValue($link, 'gen_info_0clienttype', 'ClientDesc', $_REQUEST['ClientType'], 'ClientTypeID');
		$sqlclient=',ClientType=\''.$client.'\'';
		
            // if($_POST['CreditLimit']>50000){ $sql2=(allowedToOpen(111,'1rtc')?' CreditLimit='.$_POST['CreditLimit'].',':' CreditLimit=0,');}
            // else { $sql2=' CreditLimit='.$_POST['CreditLimit'].',';}
	    // $sql2='`VatTypeNo`='.$vattypeno.', ARClient='.getYesNo($_POST['ARClient']).',PDCRequired='.getYesNo($_POST['PDCRequired']).',PORequired='.getYesNo($_POST['PORequired']).',Inactive='.getYesNo($_POST['Inactive']).','.$sql2;
            // }
            
            if ((allowedToOpen(111,'1rtc')) or ((allowedToOpen(1113,'1rtc')) AND ($_POST['CreditLimit']<=100000)) or ((allowedToOpen(1112,'1rtc')) AND ($_POST['CreditLimit']<=50000)) or ((allowedToOpen(1116,'1rtc')) AND ($_POST['CreditLimit']<=200000))) { 
                        $sql2=' CreditLimit='.$_POST['CreditLimit'].',';}
            else  { $sql2=' ';}
			
            $sql1 =' PORequired='.getYesNo($_POST['PORequired']).',';		
	    $sql2='`VatTypeNo`='.$vattypeno.','.$sql1.'Inactive='.getYesNo($_POST['Inactive']).','.$sql2;
			
            } elseif(allowedToOpen(10,'1rtc')){
				$sqlarclient='';
				$client=comboBoxValue($link, 'gen_info_0clienttype', 'ClientDesc', $_REQUEST['ClientType'], 'ClientTypeID');
				$sqlclient=',ClientType=\''.$client.'\'';
            $columnstoedit=array('TelNo1', 'TelNo2', 'Mobile','ContactPerson','EmailAddress','StreetAddress', 'Barangay', 'TownOrCity','Province','Remarks');
			// echo 'test'; exit();
	    // $sql2='Inactive='.getYesNo($_POST['Inactive']).','.((allowedToOpen(12,'1rtc'))?'ClientClass='.$_POST['ClientClass'].',':'').'';
	    $sql2=((allowedToOpen(12,'1rtc'))?'Inactive='.getYesNo($_POST['Inactive']).',TIN="'.$_POST['TIN'].'",ClientName="'.$_POST['ClientName'].'",ClientClass='.$_POST['ClientClass'].',':'');
            } else {
                $columnstoedit=array(); $sql2='';
				$sqlarclient=''; $sqlclient='';
	    }
		
		$table='1clients';
		recordtrail($txnid,$table,$link,0);

if(getYesNo($_POST['Inactive'])==0){		
		$sqlch='select * from 1clientsnewname where OldClientNo=\''.$txnid.'\'';
		// echo '</br>'.$sqlch.'';
		$stmtch=$link->query($sqlch); 
		if($stmtch->rowCount()!=0){
						echo 'This Client has a new ClientNo. You cannot set as active.';
						exit();
					}
}

//company tin condition
	$sqlchecker='select CompanyName,TIN from 1companies where TIN=\''.$_POST['TIN'].'\'';
	$stmtchecker=$link->query($sqlchecker); $resultchecker=$stmtchecker->fetch();
	if($stmtchecker->rowCount()!=0){
	 echo'This is the TIN of '.$resultchecker['CompanyName'].'.  Please check again.'; exit();
	}
//
	
	//$lastcol=end($columnstoedit);
	$sqlupdate='UPDATE 1clients SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.addslashes($_POST[$field]).'\', '; //.($field==$lastcol?'':', ');
		
	}
	
	$sql=$sqlupdate.$sql.$sql2.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() '.$sqlarclient.' '.$sqlclient.' WHERE ClientNo=\''.$txnid . '\';'; 
        //if($_SESSION['(ak0)']==1002) { echo $sql; break;}
		 // echo $sql; exit;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:clientlist.php?per=branch");
        } else { echo 'No permission'; exit();}
    break;
	
	case 'PurgeInactiveClients':
	case 'RestorePurgedClients':
	if(!allowedToOpen(91,'1rtc')){ echo 'No Permission'; exit(); }
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		if($which=='PurgeInactiveClients'){
			$title='Purge Inactive Clients';
			// $sql='SELECT *,IF(ARClient=1,"Yes","") AS ARClient,IF(CreditLimit<>0,FORMAT(CreditLimit,0),"") AS CreditLimit FROM 1clients WHERE Inactive<>0';
			$sql='SELECT c.*,if(c.ClientNo in (Select NewClientNo from 1clientsnewname),CONCAT(ClientName,\' \',"<font size=1pt> (Formerly ",(SELECT ClientName from 1clients where ClientNo=cnn.OldClientNo),")</font>"),ClientName) AS ClientName,if(ARClientTypeID=0,\'\',ARClientDesc) as ARClientType,IF(CreditLimit<>0,FORMAT(CreditLimit,0),"") AS CreditLimit,(SELECT GROUP_CONCAT(Branch) FROM gen_info_1branchesclientsjxn bcj LEFT JOIN 1branches b ON bcj.BranchNo=b.BranchNo WHERE bcj.ClientNo=c.ClientNo) AS Branches,ClientTypeShortName as ClientType FROM 1clients c left join gen_info_0clienttype ct on ct.ClientTypeID=c.ClientType left join gen_info_0arclienttype act on act.ARClientTypeID=c.ARClientType left join 1clientsnewname cnn on cnn.NewClientNo=c.ClientNo WHERE Inactive<>0;';
			$act='Purge';
			$message='Really purge clients?';
			$inputval='Purge Clients';
		} else {
			$title='Restore Purged Clients';
			$sql='SELECT c.*,if(pc.ClientNo in (Select NewClientNo from 1clientsnewname),CONCAT(ClientName,\' \',"<font size=1pt> (Formerly ",(SELECT ClientName from 1clients where ClientNo=cnn.OldClientNo),")</font>"),ClientName) AS ClientName,if(ARClientTypeID=0,\'\',ARClientDesc) as ARClientType,IF(CreditLimit<>0,FORMAT(CreditLimit,0),"") AS CreditLimit,(SELECT GROUP_CONCAT(Branch) FROM gen_info_1branchesclientsjxn bcj LEFT JOIN 1branches b ON bcj.BranchNo=b.BranchNo WHERE bcj.ClientNo=pc.ClientNo) AS Branches,NickName AS PurgedBy,ClientTypeShortName as ClientType FROM hist_incus.purgedclients pc left join gen_info_0clienttype ct on ct.ClientTypeID=pc.ClientType left join gen_info_0arclienttype act on act.ARClientTypeID=pc.ARClientType JOIN 1_gamit.0idinfo id ON pc.PurgedByNo=id.IDNo left join 1clientsnewname cnn on cnn.NewClientNo=pc.ClientNo ORDER BY ClientNo ASC';
			$act='Restore';
			$message='Restore clients?';
			$inputval='Restore Clients';
		}
		
		$stmt=$link->query($sql); $res=$stmt->fetchAll();
		echo '<title>'.$title.'</title>';
		
		// echo '<a href="clientlist.php?w=PurgeInactiveClients">Purge Inactive Clients</a> &nbsp; &nbsp; &nbsp; <a href="clientlist.php?w=RestorePurgedClients">Restore Purged Clients</a><br><br>';
		echo '<h3>'.$title.'</h3>';
		
		echo '<form action="clientlist.php?w='.$act.'" method="post">';
		
		echo '<br><table style="padding:2px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
		echo '<thead style="font-weight:bold;"><tr><td colspan='.($which=='RestorePurgedClients'?14:12).' align="right"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input style="background-color:yellow;width:120px" type="submit" value="'.$inputval.'" OnClick="return confirm(\''.$message.'\');"/></td></tr><tr><td>All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></td><td>ClientNo</td><td>Client Name</td><td>Contact Person</td><td>TelNo1</td><td>Mobile</td><td>EmailAddress</td><td>ARClientType</td><td>ClientType</td><td>CreditLimit</td><td>ClientSince</td><td>Branches</td>'.($which=='RestorePurgedClients'?'<td>PurgedBy</td><td>PurgedTS</td>':'').'</tr></thead><tbody style=\"overflow:auto;\">';
		foreach($res AS $row){
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td align="right"><input type="checkbox" value="'.$row['ClientNo'].'" name="clientno[]" /></td><td>'.$row['ClientNo'].'</td><td>'.$row['ClientName'].'</td><td>'.$row['ContactPerson'].'</td><td>'.$row['TelNo1'].'</td><td>'.$row['Mobile'].'</td><td>'.$row['EmailAddress'].'</td><td>'.$row['ARClientType'].'</td><td>'.$row['ClientType'].'</td><td>'.$row['CreditLimit'].'</td><td>'.$row['ClientSince'].'</td><td>'.$row['Branches'].'</td>'.($which=='RestorePurgedClients'?'<td>'.$row['PurgedBy'].'</td><td>'.$row['PurgedTS'].'</td>':'').'</tr>';
			$colorcount++;
		}
		echo '</tbody></table>';
		echo '</form>';
		
	break;
	
	case 'Purge':
		if(!allowedToOpen(91,'1rtc')){ echo 'No Permission'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		if (isset($_REQUEST['clientno'])){
			foreach ($_REQUEST['clientno'] AS $clientno){
				//INSERT hist_incus
				$sql1='INSERT INTO hist_incus.purgedclients SELECT *,'.$_SESSION['(ak0)'].',NOW() FROM 1clients WHERE ClientNo='.$clientno.'';
				$stmt=$link->prepare($sql1); $stmt->execute();
				
				//INSERT _trail 3=Purged
				$sql1='INSERT INTO '.$currentyr.'_trail.clientedits SELECT *,3,'.$_SESSION['(ak0)'].',NOW(),NULL FROM 1clients WHERE ClientNo='.$clientno.'';
				$stmt=$link->prepare($sql1); $stmt->execute();
				
				//DELETE FROM 1clients
				$sql0='DELETE FROM 1clients WHERE ClientNo='.$clientno.'';
				$stmt=$link->prepare($sql0); $stmt->execute();
			}
			header("Location:clientlist.php?w=PurgeInactiveClients");
		}
		else
		{
			echo 'Please select at least 1.';
		}
	break;
	
	case 'Restore':
		if(!allowedToOpen(91,'1rtc')){ echo 'No Permission'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		if (isset($_REQUEST['clientno'])){
			$sql='SELECT (SELECT GROUP_CONCAT(COLUMN_NAME) FROM information_schema.columns WHERE table_schema = "hist_incus" AND table_name = "purgedclients" AND column_name NOT IN ("PurgedByNo","PurgedTS")) AS allfieldsexcept;';
			$stmt=$link->query($sql); $row=$stmt->fetch();
			$allfieldsexcept=$row['allfieldsexcept'];
			
			foreach ($_REQUEST['clientno'] AS $clientno){
				
				//INSERT 1client
				$sql1='INSERT INTO 1clients SELECT '.$allfieldsexcept.' FROM hist_incus.purgedclients WHERE ClientNo='.$clientno.'';
				$stmt=$link->prepare($sql1); $stmt->execute();
				
				//INSERT _trail 4=Restored
				$sql1='INSERT INTO '.$currentyr.'_trail.clientedits SELECT '.$allfieldsexcept.',4,'.$_SESSION['(ak0)'].',NOW(),NULL FROM hist_incus.purgedclients WHERE ClientNo='.$clientno.'';
				$stmt=$link->prepare($sql1); $stmt->execute();
				
				//DELETE FROM purgedclients
				$sql0='DELETE FROM hist_incus.purgedclients WHERE ClientNo='.$clientno.'';
				$stmt=$link->prepare($sql0); $stmt->execute();
			} 
			header("Location:clientlist.php?w=RestorePurgedClients");
		}
		else
		{
			echo 'Please select at least 1.';
		}
	break;
   case 'NoTIN':
       if(!allowedToOpen(1114,'1rtc')){ echo 'No Permission'; exit(); }
       $title='No TIN - priority list'; $formdesc='with total sales >90k for the year</i><br><br>Please update contact details.<br><br><i>';
       $sql='SELECT Branch,if(c.ClientNo in (Select NewClientNo from 1clientsnewname),CONCAT(ClientName,\' \',"<font size=1pt> (Formerly ",(SELECT ClientName from 1clients where ClientNo=cnn.OldClientNo),")</font>"),ClientName) AS ClientName,m.ClientNo, TIN, TelNo1, TelNo2, c.Mobile, ContactPerson, CASE WHEN ClientClass=1 THEN "KeyAccount" WHEN ClientClass=2 THEN "Strategic Account" ELSE 0 end as ClientClass, VatType, sum(Qty*UnitPrice) as TotalYrSalesValue,format(sum(Qty*UnitPrice),0) as TotalYrSales FROM `invty_2sale` m join `invty_2salesub` s on m.TxnID=s.TxnID
join `1clients` c on c.ClientNo=m.ClientNo JOIN `1branches` b ON b.BranchNo=m.BranchNo LEFT JOIN `gen_info_1vattype` v ON c.VatTypeNo=v.VatTypeNo left join 1clientsnewname cnn on cnn.NewClientNo=c.ClientNo
where m.ClientNo not in (10000,10004,15001,15002,15003,15004,15005) and (TIN LIKE "%Get_TIN%" or TIN LIKE "" or ISNULL(TIN) OR TIN LIKE "0" OR (LENGTH(`TIN`) - LENGTH(REPLACE(`TIN`, "0", "")))>=6) 
group by ClientNo having TotalYrSalesValue>=90000 order by Sum(Qty*UnitPrice) DESC;';
/* $sql='SELECT Branch,m.ClientNo,ClientName, TIN, TelNo1, TelNo2, c.Mobile, ContactPerson, IF(KeyAccount=1,"Key Account","") AS Key_Account, VatType, sum(Qty*UnitPrice) as TotalYrSalesValue,format(sum(Qty*UnitPrice),0) as TotalYrSales FROM `invty_2sale` m join `invty_2salesub` s on m.TxnID=s.TxnID
join `1clients` c on c.ClientNo=m.ClientNo JOIN `1branches` b ON b.BranchNo=m.BranchNo LEFT JOIN `gen_info_1vattype` v ON c.VatTypeNo=v.VatTypeNo
where m.ClientNo not in (10000,10004,15001,15002,15003,15004,15005) and (TIN LIKE "" or ISNULL(TIN) OR TIN LIKE "0") 
group by ClientNo having TotalYrSalesValue>=90000 order by Sum(Qty*UnitPrice) DESC;'; */
       $columnnames=array('Branch','ClientNo', 'ClientName','TIN', 'TelNo1', 'TelNo2', 'Mobile','ContactPerson','VatType');
       if(allowedToOpen(1115,'1rtc')){ array_splice($columnnames,4,0,array('TotalYrSales'));}
       $columnsub=$columnnames;
       include('../backendphp/layout/displayastable.php');
       break;
	case'ClientsNoBranch':
		$title='Clients with no Assigned Branches';
		$sql='select *,if(c.ClientNo in (Select NewClientNo from 1clientsnewname),CONCAT(ClientName,\' \',"(Formerly ",(SELECT ClientName from 1clients where ClientNo=cnn.OldClientNo),")"),ClientName) AS ClientName from 1clients c left join 1clientsnewname cnn on cnn.NewClientNo=c.ClientNo where ClientNo not in (select ClientNo from gen_info_1branchesclientsjxn bc join 1branches b on b.BranchNo=bc.BranchNo where Active<>0) Order By ClientName';
		$columnnames=array('ClientNo','ClientName');
		 include('../backendphp/layout/displayastablenosort.php');
	break;
	
	
	case 'RecommendedClients':
	// print_r($_POST);
	if (!allowedToOpen(1117,'1rtc')) { echo 'No Permission'; exit(); }
	$title='Recommended Clients';
		
		// echo comboBox($link,'SELECT `ClientNo`, `ClientName` FROM `1clients` WHERE ClientNo in (select ClientNo from gen_info_1branchesclientsjxn bc join 1branches b on b.BranchNo=bc.BranchNo where Active<>0)','ClientNo','ClientName','clientlist');
		echo comboBox($link,'SELECT c.`ClientNo`, c.`ClientName` FROM `1clients` c LEFT JOIN gen_info_1branchesclientsjxn bcj ON c.ClientNo=bcj.ClientNo WHERE c.Inactive=0 AND c.ClientNo not in (select ClientNo from gen_info_1recommendedclients) GROUP BY c.ClientNo','ClientNo','ClientName','clientlist');
		
		echo '<h3>Add New Recommended Client</h3>';
		echo '<form action="clientlist.php?w=AddRecommendedClient" method="POST">Client: <input type="text" name="Client" list="clientlist"> Category: <select name="Category"><option value="1">Car Aircon (Big Shop)</option><option value="2">Car Aircon (Small Shop)</option><option value="3">Airconditioning (Big Shop)</option><option value="4">Airconditioning (Small Shop)</option></select> <input type="Submit" name="btnSubmit" value="Add"></form>';
		
		
		$conditions=''; $addtitle=''; $s=''; $sf='All';
$columnnames=array('ClientNo','ClientName','ContactPerson','Mobile','Address','ShopCategory');
if(isset($_POST['btnperarea'])){
	if($_POST['AreaNo']<>-1){
		$conditions=' WHERE b.AreaNo='.$_POST['AreaNo'].'';
		$addtitle=comboBoxValue($link,'0area','AreaNo',$_POST['AreaNo'],'Area');		
	} else {
		$addtitle='All';
		$s='s';
	}
	$addtitle.=' Area'.$s;
	$sf='';
	array_push($columnnames,'Branch');
}
if(isset($_POST['btnperbranch'])){
	if($_POST['BranchNo']<>-1){
		$conditions=' WHERE bcj.BranchNo='.$_POST['BranchNo'].'';
		$addtitle=companyandbranchValue($link,'1branches','BranchNo',$_POST['BranchNo'],'Branch');
	} else {
		$addtitle='All';
		$s='es';
	}
	$addtitle.=' Branch'.$s;
	$sf='';
	array_push($columnnames,'Area');
}

if($sf<>''){
	array_push($columnnames,'Area','Branch');
}
		
		$sqlarea='SELECT * FROM 0area';
		$stmtarea=$link->query($sqlarea); $resarea=$stmtarea->fetchAll();
		$areaselect='';
		foreach($resarea AS $resareas){
			$areaselect.='<option value="'.$resareas['AreaNo'].'">'.$resareas['Area'].'</option>';
		}
		
		$sqlbranch='SELECT BranchNo,Branch FROM 1branches WHERE Active=1 AND PseudoBranch=0 ORDER BY Branch';
		$stmtbranch=$link->query($sqlbranch); $resbranch=$stmtbranch->fetchAll();
		$branchselect='';
		foreach($resbranch AS $resbranch1){
			$branchselect.='<option value="'.$resbranch1['BranchNo'].'">'.$resbranch1['Branch'].'</option>';
		}
		
		$formdesc='</i>Search  <select id="rc_select">
    <option id="perarea_form">Per Area</option>
    <option id="client_form">Per Branch</option>
</select>

<div id="perbranch_form" align="left" style="margin:0 auto; display:none;">
  <form action="#" method="POST">
    Area: 
    <select name="AreaNo">
        <option value="-1">All</option>'.$areaselect
		
		.'</select>
   
    <input type="Submit" name="btnperarea" value="Lookup">
  </form>
</div>
  
<div id="per_branch_from" align="left"
style="margin:0 auto; display:none;">
  <form action="#" method="POST">
    Branch: 
    <select name="BranchNo">
       <option value="-1">All</option>'.$branchselect.'
    </select>
    <input type="Submit" name="btnperbranch" value="Lookup">
  </form>
</div><br><h3 align="center">Searh for '.$sf.$addtitle.'</h3><br><i>';


$sql='select rc.*,
(CASE
WHEN ShopCategory=1 THEN "Car Aircon (Big Shop)"
WHEN ShopCategory=2 THEN "Car Aircon (Small Shop)"
WHEN ShopCategory=3 THEN "Airconditioning (Big Shop)"
ELSE "Airconditioning (Small Shop)"
END) AS ShopCategory,
rc.ClientNo AS TxnID,b.Branch,Area,if(c.ClientNo in (Select NewClientNo from 1clientsnewname),CONCAT(ClientName,\' \',"<font size=1pt> (Formerly ",(SELECT ClientName from 1clients where ClientNo=cnn.OldClientNo),")</font>"),ClientName) AS ClientName,ContactPerson,c.Mobile,CONCAT(StreetAddress," ",Barangay," ",TownOrCity," ",Province) AS Address from gen_info_1recommendedclients rc JOIN 1clients c ON rc.ClientNo=c.ClientNo JOIN gen_info_1branchesclientsjxn bcj ON rc.ClientNo=bcj.ClientNo JOIN 1branches b ON bcj.BranchNo=b.BranchNo JOIN 0area a ON b.AreaNo=a.AreaNo left join 1clientsnewname cnn on cnn.NewClientNo=c.ClientNo '.$conditions.' Order By ClientName';
		// echo $sql; 
		$delprocess='clientlist.php?w=DeleteRC&TxnID=';
		 include('../backendphp/layout/displayastable.php');
		 
		 
	
	break;
	
	case 'AddRecommendedClient':
	if (!allowedToOpen(1117,'1rtc')) { echo 'No Permission'; exit(); }
	 $clientno=comboBoxValue($link,'`1clients`','ClientName',addslashes($_POST['Client']),'ClientNo');
	 $sql='INSERT INTO gen_info_1recommendedclients SET ShopCategory='.$_POST['Category'].',ClientNo='.$clientno.',TimeStamp=NOW(),EncodedByNo='.$_SESSION['(ak0)'].'';
				$stmt=$link->prepare($sql); $stmt->execute();
				
	header("Location:clientlist.php?w=RecommendedClients");
	break;
	
	
}
    endofform:
          $link=null; $stmt=null;
    ?>
	<script>
	function toggle(source) {
		var checkboxes = document.querySelectorAll('input[type="checkbox"]');
		for (var i = 0; i < checkboxes.length; i++) {
			if (checkboxes[i] != source)
				checkboxes[i].checked = source.checked;
		}
	}
</script>
<script>
$(function() {
  $("#rc_select").change(function() {
    if ($("#perarea_form").is(":selected")) {
      $("#perbranch_form").show();
      $("#per_branch_from").hide();
    } else {
      $("#perbranch_form").hide();
      $("#per_branch_from").show();
    }
  }).trigger('change');
});
</script>
</body>
</html>