<?php $path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
?>
<script type="text/javascript" language="javascript" src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/jquery-3.3.1.js"></script>
<script>
jQuery(document).ready(function() {
	jQuery('.tabs .tab-links a').on('click', function(e) {
		var currentAttrValue = jQuery(this).attr('href');

		// Show/Hide Tabs
		jQuery('.tabs ' + currentAttrValue).show().siblings().hide();

		// Change/remove current tab to active
		jQuery(this).parent('li').addClass('active').siblings().removeClass('active');

		e.preventDefault();
	});
});
</script>
<style>

.tabs {
	width:100%;
	display:inline-block;
}

	.tab-links:after {
	display:block;
	clear:both;
	content:'';
}

.tab-links li {
	margin:0px 5px;
	float:left;
	list-style:none;
}

.tab-links a {
	padding:9px 15px;
	display:inline-block;
	border-radius:3px 3px 0px 0px;
	background:#7FB5DA;
	font-size:16px;
	font-weight:600;
	color:#4c4c4c;
	transition:all linear 0.15s;
	text-decoration: none;
}

.tab-links a:hover {
	background:#a7cce5;
	text-decoration:none;
}

li.active a, li.active a:hover {
	background:#fff;
	color:#4c4c4c;
}

.tab-content {
	padding:15px;
	border-radius:3px;
	box-shadow:-1px 1px 1px rgba(0,0,0,0.15);
	background:#fff;
}

.tab {
	display:none;
}

.tab.active {
	display:block;
}
</style>



<?php

// check if allowed 
$allowed=array(747,748); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
if (allowedToOpen(2201,'1rtc')){
        error_reporting(E_ALL);
	ini_set('display_errors', 1);
}

$hidecontents=1;



$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;


$whichqry=$_GET['w'];
switch ($whichqry){
case 'Item_Activity_Defective':

    if (!allowedToOpen(748,'1rtc')) {   echo 'No permission'; exit;}
	$link=connect_db(''.$currentyr.'_1rtc',1); //so previous years are open.
			$itemcode=intval($_GET['ItemCode']);
			$defectcondi=' AND Defective<>0';
			include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
			 $title='Branch No. '.$_GET['BranchNo'].': '.comboBoxValue($link,'1branches','BranchNo',$_GET['BranchNo'],'Branch').'  Item Activity (Defective)';
			
// Kept this line for previous years
if (allowedToOpen(7481,'1rtc')) { $supplierincluded='gen_info_0unisupplierclientforitemact';} else {   $supplierincluded='gen_info_0unibranchclientforitemact';}


if (isset($itemcode)){

    include('maketables/getasofmonth.php');
    include('maketables/createitemactdefective.php');
    include_once '../generalinfo/unionlists/BECSList.php';
	
    
		echo '<div class="tabs">
		<ul class="tab-links">
			<li class="active"><a href="#tab1">'.$currentyr.'</a></li>
			<li><a href="#tab2">'.($currentyr-1).'</a></li>
			<li><a href="#tab3">'.($currentyr-2).'</a></li>
			<li><a href="#tab4">'.($currentyr-3).'</a></li>
			<li><a href="#tab5">'.($currentyr-4).'</a></li>
			<li><a href="#tab6">'.($currentyr-5).'</a></li>
		</ul>

		<div class="tab-content">
			<div id="tab1" class="tab active">';
			$sql='SELECT a.*,s.BECSName as `Supplier/Client/Branch`,if(Defective=1,"D",if(Defective=2,"DC","G")) as DefectiveOrGood,DAY(Date) AS Day1 FROM ItemAct'.$currentyr.' a LEFT JOIN `BECSList` s on a.BECSNo=s.BECSNo AND a.BECS=s.BECS WHERE a.Date IS NOT NULL '.$defectcondi.';';

			$sql1='SELECT SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,Sum(Qty) as EndInvToday FROM ItemAct'.$currentyr.';';
			$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
			 $coltototal='Qty';$runtotal=true;
			
			$columnnames=array('Date', 'From', 'Number', 'Supplier/Client/Branch', 'Qty', 'UnitPrice', 'SerialNo', 'DefectiveOrGood', 'ActRemarks');
			$totalstext='Defective: '.$res1['Defective'];
			include('../backendphp/layout/displayastablenosort.php');
			
			echo '</div>

			<div id="tab2" class="tab">';
				$sql='SELECT a.*,s.SupplierName as `Supplier/Client/Branch`,if(Defective=1,"D",if(Defective=2,"DC","G")) as DefectiveOrGood,DAY(Date) AS Day1 FROM ItemAct'.$lastyr.' a LEFT JOIN `'.$lastyr.'_1rtc`.`'.$supplierincluded.'` s on a.SupplierNo=s.SupplierNo WHERE a.Date IS NOT NULL';

			$sql1='SELECT SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,Sum(Qty) as EndInvToday FROM ItemAct'.$lastyr.';';
			$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
			 $coltototal='Qty';$runtotal=true;
			

			$totalstext='Defective: '.$res1['Defective'];
			$columnnames=array('Date', 'From', 'Number', 'Supplier/Client/Branch', 'Qty', 'UnitPrice', 'SerialNo', 'DefectiveOrGood', 'ActRemarks');
			include('../backendphp/layout/displayastablenosort.php');
			
			echo '
			</div>

			<div id="tab3" class="tab">';
			
			$sql='SELECT a.*,s.SupplierName as `Supplier/Client/Branch`,if(Defective=1,"D",if(Defective=2,"DC","G")) as DefectiveOrGood,DAY(Date) AS Day1 FROM ItemAct'.($currentyr-2).' a LEFT JOIN `'.($currentyr-2).'_1rtc`.`'.$supplierincluded.'` s on a.SupplierNo=s.SupplierNo WHERE a.Date IS NOT NULL';

			$sql1='SELECT SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,Sum(Qty) as EndInvToday FROM ItemAct'.($currentyr-2).';';
			$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
			 $coltototal='Qty';$runtotal=true;
			

			$totalstext='Defective: '.$res1['Defective'];
			$columnnames=array('Date', 'From', 'Number', 'Supplier/Client/Branch', 'Qty', 'UnitPrice', 'SerialNo', 'DefectiveOrGood', 'ActRemarks');
			include('../backendphp/layout/displayastablenosort.php');
			
			echo '
				
			</div>

			<div id="tab4" class="tab">';
				$sql='SELECT a.*,s.SupplierName as `Supplier/Client/Branch`,if(Defective=1,"D",if(Defective=2,"DC","G")) as DefectiveOrGood,DAY(Date) AS Day1 FROM ItemAct'.($currentyr-3).' a LEFT JOIN `'.($currentyr-3).'_1rtc`.`'.$supplierincluded.'` s on a.SupplierNo=s.SupplierNo WHERE a.Date IS NOT NULL';

			$sql1='SELECT SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,Sum(Qty) as EndInvToday FROM ItemAct'.($currentyr-3).';';
			$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
			 $coltototal='Qty';$runtotal=true;
			$totalstext='Defective: '.$res1['Defective'];
			$columnnames=array('Date', 'From', 'Number', 'Supplier/Client/Branch', 'Qty', 'MinPrice', 'SerialNo', 'DefectiveOrGood', 'ActRemarks');
			include('../backendphp/layout/displayastablenosort.php');
			
			echo '</div>
			
			<div id="tab5" class="tab">';
			$sql='SELECT a.*,s.SupplierName as `Supplier/Client/Branch`,if(Defective=1,"D",if(Defective=2,"DC","G")) as DefectiveOrGood,DAY(Date) AS Day1 FROM ItemAct'.($currentyr-4).' a LEFT JOIN `'.($currentyr-4).'_1rtc`.`'.$supplierincluded.'` s on a.SupplierNo=s.SupplierNo WHERE a.Date IS NOT NULL';

			$sql1='SELECT SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,Sum(Qty) as EndInvToday FROM ItemAct'.($currentyr-4).';';
			$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
			 $coltototal='Qty';$runtotal=true;
			$totalstext='Defective: '.$res1['Defective'];
			$columnnames=array('Date', 'From', 'Number', 'Supplier/Client/Branch', 'Qty', 'MinPrice', 'SerialNo', 'DefectiveOrGood', 'ActRemarks');
			include('../backendphp/layout/displayastablenosort.php');
			
				echo '
			</div>
			
			<div id="tab6" class="tab">';
			$sql='SELECT a.*,s.SupplierName as `Supplier/Client/Branch`,if(Defective=1,"D",if(Defective=2,"DC","G")) as DefectiveOrGood,DAY(Date) AS Day1 FROM ItemAct'.($currentyr-5).' a LEFT JOIN `'.($currentyr-5).'_1rtc`.`'.$supplierincluded.'` s on a.SupplierNo=s.SupplierNo WHERE a.Date IS NOT NULL';

			$sql1='SELECT SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,Sum(Qty) as EndInvToday FROM ItemAct'.($currentyr-5).';';
			$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
			 $coltototal='Qty';$runtotal=true;
			$totalstext='Defective: '.$res1['Defective'];
			$columnnames=array('Date', 'From', 'Number', 'Supplier/Client/Branch', 'Qty', 'MinPrice', 'SerialNo', 'DefectiveOrGood', 'ActRemarks');
			include('../backendphp/layout/displayastablenosort.php');
			
				echo '
			</div>
		</div>
	</div>';
	
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
     $sql0='DROP TEMPORARY TABLE IF EXISTS ItemAct'.$currentyr.''; $stmt0=$link->prepare($sql0); $stmt0->execute(); 
     $sql0='DROP TEMPORARY TABLE IF EXISTS ItemAct'.($currentyr-1).''; $stmt0=$link->prepare($sql0); $stmt0->execute(); 
     $sql0='DROP TEMPORARY TABLE IF EXISTS ItemAct'.($currentyr-2).''; $stmt0=$link->prepare($sql0); $stmt0->execute(); 
     $sql0='DROP TEMPORARY TABLE IF EXISTS ItemAct'.($currentyr-3).''; $stmt0=$link->prepare($sql0); $stmt0->execute(); 
     $sql0='DROP TEMPORARY TABLE IF EXISTS ItemAct'.($currentyr-4).''; $stmt0=$link->prepare($sql0); $stmt0->execute(); 
     $sql0='DROP TEMPORARY TABLE IF EXISTS ItemAct'.($currentyr-5).''; $stmt0=$link->prepare($sql0); $stmt0->execute(); 
  }
  break;
 
  }
  noform:
     
     $link=null; $stmt=null; 
     $stmt0=null;
     $link=null;
?>
