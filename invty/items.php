<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

  
include_once('../switchboard/contents.php');include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
?><br><div id="section" style="display: block;"><?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);
if (!isset($_POST['ShowAll'])){
	$sqlcondition=' WHERE 1=1 ';
}
if (isset($_POST['ShowAll'])){
	$sqlcondition=' where Category like \'%%\' ';
}
if (isset($_POST['Unclassified'])){
	$sqlcondition=' WHERE (Ref=0) AND (Aircon=0) AND (Auto=0) AND (Category like \'%%\')';
}
if (isset($_POST['submit1']) OR (isset($_POST['Category']) AND $_POST['Category']<>'')){
	$sqlcondition=' where Category like \''.$_POST['Category'].'\' ';
}

if (!isset($_POST['Category'])){
$_POST['Category']='';
}

// print_r($_POST);
if(isset($_POST['btnLookup'])){
			if(isset($_POST['Auto']) and !isset($_POST['Ref']) and !isset($_POST['Aircon'])){
				$sqlcondition.='AND (Auto=1)';
				$subtitle='Auto';
			}elseif(isset($_POST['Ref']) and !isset($_POST['Auto']) and !isset($_POST['Aircon'])){
				$sqlcondition.='AND (Ref=1)';
				$subtitle='Ref';
			}elseif(isset($_POST['Aircon']) and !isset($_POST['Auto']) and !isset($_POST['Ref'])){
				$sqlcondition.='AND (Aircon=1)';
				$subtitle='Aircon';
			}elseif(isset($_POST['Auto']) and isset($_POST['Ref']) and !isset($_POST['Aircon'])){
				$sqlcondition.='AND (Auto=1 OR Ref=1)';
				$subtitle='Auto and Ref';
			}elseif(isset($_POST['Auto']) and isset($_POST['Aircon']) and !isset($_POST['Ref'])){
				$sqlcondition.='AND (Auto=1 OR Aircon=1)';
				$subtitle='Auto and Aircon';
			}elseif(isset($_POST['Auto']) and isset($_POST['Ref']) and isset($_POST['Aircon'])){
				$sqlcondition.='AND (Auto=1 OR Ref=1 OR Aircon=1)';
				$subtitle='Auto, Ref, and Aircon';
			}elseif(isset($_POST['Ref']) and isset($_POST['Aircon']) and !isset($_POST['Auto'])){
				$sqlcondition.='AND (Ref=1 OR Aircon=1)';
				$subtitle='Ref and Aircon';
			}
}



$columnstoadd=array('ItemCode','ItemDesc',  'Unit', 'Auto', 'Ref', 'Aircon', 'WholesaleUnit', 'Remarks', 'WithBarcode'); //'ItemDesc2',
if (in_array($which,array('List','EditSpecifics'))){
   //$sql0='CREATE TEMPORARY TABLE movementtype AS SELECT 0 AS MoveType, "Active" as MovementType UNION SELECT 1,"Non-Stock" UNION SELECT 3,"Non-Moving" UNION SELECT 5,"Obsolete"';
   //$stmt0=$link->prepare($sql0); $stmt0->execute();
   echo comboBox($link,'SELECT 0 AS YN,"No" AS YNCaption UNION SELECT 1 AS YN,"Yes" AS YNCaption;','YNCaption','YN','ynlist');
   echo comboBox($link,'SELECT * FROM `invty_0movetype` ORDER BY MovementType;','MoveType','MovementType','movetype');
   echo comboBox($link,'SELECT Category, CONCAT(CatNo, " - ",StdDesc) AS CatNo FROM `invty_1category` ORDER BY Category;','CatNo','Category','categories');
   
   $sql='SELECT i.*,IF(Auto=1,"Y","") AS `Auto?`,IF(Ref=1,"Y","") AS `Ref?`,IF(Aircon=1,"Y","") AS `Aircon?`, Category, e.Nickname as EncodedBy, i.ItemCode AS TxnID, MovementType, IF(WithBarcode<>0,"Yes","No") AS With_Barcode FROM invty_1items i
        JOIN `invty_1category` t ON t.CatNo=i.CatNo JOIN invty_0movetype m ON m.MoveType=i.MoveType
   LEFT JOIN `1employees` e ON e.IDNo=i.EncodedByNo '.$sqlcondition.' ';
   // echo $sql;
   $columnnameslist=array('ItemCode','Category', 'ItemDesc', 'Unit', 'Auto?', 'Ref?', 'Aircon?', 'WholesaleUnit', 'Remarks', 'MovementType','With_Barcode','ItemSince');//,'EncodedBy','TimeStamp');
   
} 

if (in_array($which,array('Add','Edit'))){
   $catno=comboBoxValue($link,'`invty_1category`','Category',addslashes($_POST['Category']),'CatNo');
   $movetype=comboBoxValue($link,'`invty_0movetype`','MovementType',addslashes($_POST['MovementType']),'MoveType');
   }
include_once('../backendphp/layout/linkstyle.php');
		if (allowedToOpen(5261,'1rtc')) {  
			echo ' <a id="link" href="beginvcontrols.php">Inventory list for 2Central</a> ';
			echo ' <a id="link" href="items.php?w=Purged">Lookup Purged Items</a>';
			
		}
switch ($which){
   case 'List':
   
   ?>
   </br></br><form style="display:inline;" method="POST" action="items.php">
					Category: <input type="text" name="Category" list="categories"/>
					 <input type="submit" name="submit1"/> 
					 <input type="submit" name="ShowAll" value="Show All"/>
                                         &nbsp; &nbsp;<input type="submit" name="Unclassified" value="Show Unclassified Auto/Ref/Aircon"/>
					 </form>
					 <?php

					 $postcategory=$_POST['Category'];
         $title='Items List';            
      $action='items.php?w=Add'; $fieldsinrow=9; $liststoshow=array(); 
      if (allowedToOpen(64341,'1rtc')){
	 $formdesc='BE CAREFUL.  This affects the entire inventory system.</i><div align="right"></div><i>
	 
	 '; 
	 $itemcode='select ItemCode from invty_1items  where ItemCode not IN (5257,8134) order by  ItemCode Desc  limit 1';
	 $stmt=$link->query($itemcode);
	 $result=$stmt->fetch();
	 if (!isset($_POST['ShowAll'])){
         $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>5,'value'=> $result['ItemCode']+1),
                    array('field'=>'Category','caption'=>'Category','type'=>'text','size'=>10,'required'=>true, 'list'=>'categories', 'value'=>'"'.$postcategory.'"'),
                    array('field'=>'ItemDesc','type'=>'text','size'=>15,'required'=>true),
//		    array('field'=>'ItemDesc2','type'=>'text','size'=>15,'required'=>false),
                    array('field'=>'Unit','type'=>'text','size'=>5,'required'=>true),
                    array('field'=>'Auto','type'=>'text','size'=>5,'required'=>true,'value'=>'0','list'=>'ynlist'),
                    array('field'=>'Ref','type'=>'text','size'=>5,'required'=>true,'value'=>'0','list'=>'ynlist'),
                    array('field'=>'Aircon','type'=>'text','size'=>5,'required'=>true,'value'=>'0','list'=>'ynlist'),
		    array('field'=>'WholesaleUnit','type'=>'text','size'=>5,'required'=>false),
                    array('field'=>'Remarks','type'=>'text','size'=>10,'required'=>false),
		    array('field'=>'MovementType','type'=>'text','size'=>5,'required'=>false, 'value'=>"Active", 'list'=>'movetype'),
                    array('field'=>'WithBarcode', 'caption'=>'With Barcode? (1=yes, 0=no)','type'=>'text','size'=>2,'required'=>true));
	 
	 $method='post'; $fieldsinrow=8;
	 include('../backendphp/layout/inputmainform.php');}
	 $delprocess='items.php?w=Delete&ItemCode=';
	 $editprocess='items.php?w=EditSpecifics&ItemCode='; $editprocesslabel='Edit'; 
         $columnstoedit=array('Category','ItemCode','ItemDesc', 'Unit', 'WholesaleUnit', 'Remarks', 'MovementType','WithBarcode');
	 } else { $columnstoedit=array();}
      echo '<form method="post" action="items.php">
	  <input type="hidden" name="Category" value="'.(isset($_POST['Category'])?$_POST['Category']:'').'">
				Auto: <input type="checkbox" name="Auto"> 
				Ref: <input type="checkbox" name="Ref"> 
				Aircon: <input type="checkbox" name="Aircon">
				<input type="submit" name="btnLookup" value="Lookup">
			</form>';
      $title=''; $formdesc='';$txnid='TxnID';
      $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Category,ItemCode'); $columnsub=$columnnames;
        $sql=$sql.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
		// $sql.=' limit 100';
		
		if(isset($_POST['submit1']) OR isset($_POST['ShowAll']) OR isset($_POST['Unclassified']) OR isset($_POST['btnLookup'])){
			include('../backendphp/layout/displayastable.php'); 
		}
        break;
    case 'Add':
        if (allowedToOpen(64341,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `invty_1items` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' CatNo='.$catno.', MoveType='.$movetype.', TimeStamp=Now(),ItemSince=CURDATE()'; 
		
		// echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if (allowedToOpen(64341,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `invty_1items` WHERE ItemCode='.$_GET['ItemCode'];
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;   
    case 'EditSpecifics':
         if (allowedToOpen(64341,'1rtc')){
	 $title='Edit Specifics';
	 $txnid=intval($_GET['ItemCode']); 
	 $sql=$sql.'AND i.ItemCode='.$txnid;
	 // echo $sql;
	 array_unshift($columnstoadd,'Category','MovementType');$columnstoedit=$columnstoadd;	 
	 $columnnames=$columnnameslist;
	 $columnswithlists=array('Category','MovementType','Auto','Ref','Aircon');
	 $listsname=array('Category'=>'categories','MovementType'=>'movetype','Auto'=>'ynlist','Ref'=>'ynlist','Aircon'=>'ynlist');
	 $editprocess='items.php?w=Edit&ItemCode='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
	 } else { header("Location:".$_SERVER['HTTP_REFERER']);}
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if (allowedToOpen(64341,'1rtc')){
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `invty_1items` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' CatNo='.$catno.', MoveType='.$movetype.', TimeStamp=Now() WHERE ItemCode='.$_GET['ItemCode']; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:items.php");
        break;
        
   case 'Purged':
       $title='Purged Items in '.$currentyr.''; $txnid='ItemCode';
       $columnnames=array('ItemCode','Category', 'ItemDesc', 'Unit', 'WholesaleUnit', 'Remarks', 'MovementType','With_Barcode','EncodedBy','TimeStamp');//'ItemDesc2', 
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Category,ItemCode'); $columnsub=$columnnames;
        $sql='SELECT i.*, Category, e.Nickname as EncodedBy, i.ItemCode AS TxnID, MovementType, IF(WithBarcode=1,"Yes","No") AS With_Barcode FROM purgedin'.$currentyr.' i
        LEFT JOIN `invty_1category` t ON t.CatNo=i.CatNo JOIN invty_0movetype m ON m.MoveType=i.MoveType
        LEFT JOIN `1employees` e ON e.IDNo=i.EncodedByNo ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');    
      include('../backendphp/layout/displayastable.php');       
       break;
	   
	/* case'InvtyPlanning':
			echo '<form method="post" action="items.php?w=InvtyPlanning">
				Auto: <input type="checkbox" name="Auto"> 
				Ref: <input type="checkbox" name="Ref"> 
				Aircon: <input type="checkbox" name="Aircon">
				<input type="submit" name="submit" value="Lookup">
			</form><br/>';
			
		if(isset($_POST['submit'])){
			if(isset($_POST['Auto']) and !isset($_POST['Ref']) and !isset($_POST['Aircon'])){
				$condition='Where Auto=1';
				$title='Auto';
			}elseif(isset($_POST['Ref']) and !isset($_POST['Auto']) and !isset($_POST['Aircon'])){
				$condition='Where Ref=1';
				$title='Ref';
			}elseif(isset($_POST['Aircon']) and !isset($_POST['Auto']) and !isset($_POST['Ref'])){
				$condition='Where Aircon=1';
				$title='Aircon';
			}elseif(isset($_POST['Auto']) and isset($_POST['Ref']) and !isset($_POST['Aircon'])){
				$condition='Where Auto=1 OR Ref=1';
				$title='Auto and Ref';
			}elseif(isset($_POST['Auto']) and isset($_POST['Aircon']) and !isset($_POST['Ref'])){
				$condition='Where Auto=1 OR Aircon=1';
				$title='Auto and Aircon';
			}elseif(isset($_POST['Auto']) and isset($_POST['Ref']) and isset($_POST['Aircon'])){
				$condition='Where Auto=1 OR Ref=1 OR Aircon=1';
				$title='Auto, Ref, and Aircon';
			}elseif(isset($_POST['Ref']) and isset($_POST['Aircon']) and !isset($_POST['Auto'])){
				$condition='Where Ref=1 OR Aircon=1';
				$title='Ref and Aircon';
			}
		$sql='select ItemCode,Category,ItemDesc,Unit,WholesaleUnit,Remarks,WithBarcode from invty_1items i left join invty_1category c on c.CatNo=i.CatNo '.$condition.'';
		$columnnames=array('ItemCode','Category','ItemDesc','Unit','WholesaleUnit','Remarks','WithBarcode');
		// echo $sql; exit();
		include('../backendphp/layout/displayastable.php'); 
			}
	
	
	break; */
    
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body></html>