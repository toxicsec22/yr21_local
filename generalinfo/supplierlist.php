<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6436,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}   
include_once('../switchboard/contents.php'); 
include_once 'trailgeninfo.php';

    $title='List of Suppliers';
    
    $txnid='SupplierNo';
    $fieldname='SupplierNo';
	
$which=(!isset($_GET['w'])?'List':$_GET['w']);	
	if($which=='PurgeInactiveSuppliers' OR $which=='RestorePurgedSuppliers'){
	$showbranches=false; 
} else {
	$showbranches=true; 
}

    $method='POST';
    
    $columnslist=array();
    $liststoshow=array();
    
$columnnames=array('SupplierNo', 'SupplierName', 'ContactPerson', 'TelNo1', 'TelNo2', 'TIN','Address','Terms','InvtySupplier','Inactive','NameonCheck');


switch ($which){
   case 'List': 
$addlmenu='&nbsp &nbsp &nbsp &nbsp<a href=addentrygeninfo.php?w=Supplier target=_blank>Add Supplier</a>&nbsp &nbsp &nbsp &nbsp<a href=addentrygeninfo.php?w=SupplierBranch target=_blank>Assign Supplier to Branches</a>&nbsp &nbsp &nbsp &nbsp<a href=supplierlist.php?w=PurgeInactiveSuppliers target=_blank>Purge Inactive Suppliers</a>&nbsp &nbsp &nbsp &nbsp<a href=supplierlist.php?w=RestorePurgedSuppliers target=_blank>Restore Purged Suppliers</a>';
$editprocess='supplierlist.php?w=EditSpecifics&SupplierNo=';$editprocesslabel='Edit';
       
        $sql='SELECT `1suppliers`.* FROM `1suppliers` WHERE Inactive=0';    
        include('../backendphp/layout/displayastablewithedit.php');
        $title='<br><br>Inactive Suppliers';
        $sql='SELECT `1suppliers`.* FROM `1suppliers` WHERE Inactive<>0';    
        unset($addlmenu);
        include('../backendphp/layout/displayastablewithedit.php');
  
 break;
   case 'EditSpecifics':
       
       $txnid=$_GET['SupplierNo'];
	 
            $sql='SELECT `1suppliers`.* FROM `1suppliers` WHERE (`1suppliers`.SupplierNo)=\''.$txnid.'\'';
            // $editprocess='supplierlist.php?w=Edit&SupplierNo='; $editprocesslabel='Edit';
            $editprocess='supplierlist.php?w=Edit&SupplierNo='.$txnid; $editprocesslabel='Edit';
        if (allowedToOpen(64361,'1rtc')){
            $columnstoedit=array('SupplierName','ContactPerson','TIN','Address','TelNo1','TelNo2','Terms','InvtySupplier','Inactive','NameonCheck');    
            } elseif(allowedToOpen(64362,'1rtc')){
            $columnstoedit=array('ContactPerson','TelNo1', 'TelNo2', 'Address', 'Inactive');      
            } else {
                $columnstoedit=array();
            }
            include('../backendphp/layout/editspecificsforlists.php');
            break;
            
   case 'Edit':
       $txnid=$_REQUEST['SupplierNo'];
	
	if (allowedToOpen(64361,'1rtc')){
            $columnstoedit=array('SupplierName','ContactPerson','TIN','Address','TelNo1','TelNo2','Terms','InvtySupplier','Inactive','NameonCheck');    
            } elseif(allowedToOpen(64362,'1rtc')){
            $columnstoedit=array('ContactPerson','TelNo1', 'TelNo2', 'Address', 'Inactive');      
            } else {
                $columnstoedit=array();
	    }
		
		$table='1suppliers';
		recordtrail($txnid,$table,$link,0);
		
	//$lastcol=end($columnstoedit);
	$sqlupdate='UPDATE 1suppliers SET ';
	$sql='';
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; //.($field==$lastcol?'':', ');
		
	}
	
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() WHERE SupplierNo=\''.$txnid . '\';'; 
	// echo $sql; exit();
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:supplierlist.php");
       break;
	   
	   
	   case 'PurgeInactiveSuppliers':
	case 'RestorePurgedSuppliers':
	// echo 'a';
	if(!allowedToOpen(64363,'1rtc')){ echo 'No Permission'; exit(); }
		$colorcount=0;
		$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
		$rcolor[1]="FFFFFF";
		
		if($which=='PurgeInactiveSuppliers'){
			$title='Purge Inactive Suppliers';
			$sql='SELECT *,(SELECT GROUP_CONCAT(Branch) FROM gen_info_1branchessuppliersjxn bsj LEFT JOIN 1branches b ON bsj.BranchNo=b.BranchNo WHERE bsj.SupplierNo=s.SupplierNo) AS Branches FROM 1suppliers s WHERE Inactive<>0;';
			
			$act='Purge';
			$message='Really purge suppliers?';
			$inputval='Purge Suppliers';
		} else {
			$title='Restore Purged Suppliers';
			$sql='SELECT *,(SELECT GROUP_CONCAT(Branch) FROM gen_info_1branchessuppliersjxn bsj LEFT JOIN 1branches b ON bsj.BranchNo=b.BranchNo WHERE bsj.SupplierNo=ps.SupplierNo) AS Branches,NickName AS PurgedBy FROM hist_incus.purgedsuppliers ps JOIN 1_gamit.0idinfo id ON ps.PurgedByNo=id.IDNo ORDER BY SupplierNo ASC;';
			$act='Restore';
			$message='Restore suppliers?';
			$inputval='Restore Suppliers';
		}
		
		$stmt=$link->query($sql); $res=$stmt->fetchAll();
		echo '<title>'.$title.'</title>';
		
		echo '<a href="supplierlist.php?w=PurgeInactiveSuppliers">Purge Inactive Suppliers</a> &nbsp; &nbsp; &nbsp; <a href="supplierlist.php?w=RestorePurgedSuppliers">Restore Purged Suppliers</a><br><br>';
		echo '<h3>'.$title.'</h3>';
		
		echo '<form action="supplierlist.php?w='.$act.'" method="post">';
		
		echo '<br><table style="padding:2px;font-size:10.5pt;background-color:#ffffff; display: inline-block; border: 1px solid">';
		echo '<thead style="font-weight:bold;"><tr><td colspan='.($which=='RestorePurgedSuppliers'?10:8).' align="right"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"><input style="background-color:yellow;width:120px" type="submit" value="'.$inputval.'" OnClick="return confirm(\''.$message.'\');"/></td></tr><tr><td>All? <input type="checkbox" class="chk_boxes" onclick="toggle(this);" /></td><td>SupplierNo</td><td>Supplier Name</td><td>Contact Person</td><td>TelNo1</td><td>Terms</td><td>SupplierSince</td><td>Branches</td>'.($which=='RestorePurgedSuppliers'?'<td>PurgedBy</td><td>PurgedTS</td>':'').'</tr></thead><tbody style=\"overflow:auto;\">';
		foreach($res AS $row){
			echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td align="right"><input type="checkbox" value="'.$row['SupplierNo'].'" name="supplierno[]" /></td><td>'.$row['SupplierNo'].'</td><td>'.$row['SupplierName'].'</td><td>'.$row['ContactPerson'].'</td><td>'.$row['TelNo1'].'</td><td>'.$row['Terms'].'</td><td>'.$row['SupplierSince'].'</td><td>'.$row['Branches'].'</td>'.($which=='RestorePurgedSuppliers'?'<td>'.$row['PurgedBy'].'</td><td>'.$row['PurgedTS'].'</td>':'').'</tr>';
			$colorcount++;
		}
		echo '</tbody></table>';
		echo '</form>';
		
	break;
	
	case 'Purge':
		if(!allowedToOpen(64363,'1rtc')){ echo 'No Permission'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		if (isset($_REQUEST['supplierno'])){
			foreach ($_REQUEST['supplierno'] AS $supplierno){
				//INSERT hist_incus
				$sql1='INSERT INTO hist_incus.purgedsuppliers SELECT *,'.$_SESSION['(ak0)'].',NOW() FROM 1suppliers WHERE SupplierNo='.$supplierno.'';
				$stmt=$link->prepare($sql1); $stmt->execute();
				
				//INSERT _trail 3=Purged
				$sql1='INSERT INTO '.$currentyr.'_trail.supplieredits SELECT *,3,'.$_SESSION['(ak0)'].',NOW(),NULL FROM 1suppliers WHERE SupplierNo='.$supplierno.'';
				$stmt=$link->prepare($sql1); $stmt->execute();
				
				//DELETE FROM 1suppliers
				$sql0='DELETE FROM 1suppliers WHERE SupplierNo='.$supplierno.'';
				$stmt=$link->prepare($sql0); $stmt->execute();
			}
			header("Location:supplierlist.php?w=PurgeInactiveSuppliers");
		}
		else
		{
			echo 'Please select at least 1.';
		}
	break;
	
	case 'Restore':
		if(!allowedToOpen(64363,'1rtc')){ echo 'No Permission'; exit(); }
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		if (isset($_REQUEST['supplierno'])){
			$sql='SELECT (SELECT GROUP_CONCAT(COLUMN_NAME) FROM information_schema.columns WHERE table_schema = "hist_incus" AND table_name = "purgedsuppliers" AND column_name NOT IN ("PurgedByNo","PurgedTS")) AS allfieldsexcept;';
			$stmt=$link->query($sql); $row=$stmt->fetch();
			$allfieldsexcept=$row['allfieldsexcept'];
			
			foreach ($_REQUEST['supplierno'] AS $supplierno){
				
				//INSERT 1suppliers
				$sql1='INSERT INTO 1suppliers SELECT '.$allfieldsexcept.' FROM hist_incus.purgedsuppliers WHERE SupplierNo='.$supplierno.'';
				$stmt=$link->prepare($sql1); $stmt->execute();
				
				//INSERT _trail 4=Restored
				$sql1='INSERT INTO '.$currentyr.'_trail.supplieredits SELECT '.$allfieldsexcept.',4,'.$_SESSION['(ak0)'].',NOW(),NULL FROM hist_incus.purgedsuppliers WHERE SupplierNo='.$supplierno.'';
				$stmt=$link->prepare($sql1); $stmt->execute();
				
				//DELETE FROM purgedsuppliers
				$sql0='DELETE FROM hist_incus.purgedsuppliers WHERE SupplierNo='.$supplierno.'';
				$stmt=$link->prepare($sql0); $stmt->execute();
			} 
			header("Location:supplierlist.php?w=RestorePurgedSuppliers");
		}
		else
		{
			echo 'Please select at least 1.';
		}
	break;
	
}
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
</body>
</html>