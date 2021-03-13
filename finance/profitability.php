<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5905,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
$which=(!isset($_GET['w'])?'lists':$_GET['w']);

switch($which){
	case'lists':
			echo'<title>Profitability per Item and Per Client</title>';
			echo'</br><h3>Profitability per Item and Per Client</h3></br>';
			echo '<form method="post" action="profitability.php?w=lists">
				Filtering: <input type="submit" name="filter" value="Per Item">
				'.str_repeat('&nbsp;',5).'<input type="submit" name="filter" value="Per Client">
				</form>';
		if(isset($_POST['filter'])){
if($_POST['filter']=='Per Item'){	
	
		$formdesc='Per ItemCode';
		$title='';
		$sql1='Create temporary table TSales as select Month(Date) as Month,ItemCode,sum(UnitPrice*Qty) as Sales from invty_2sale s join invty_2salesub ss on ss.TxnID=s.TxnID Group By ItemCode,month(Date)';
		// echo $sql1; exit();
		$stmt1=$link->prepare($sql1); $stmt1->execute();
		$sql='select ts.ItemCode as ItemCode,ItemDesc,
		format(sum(Case when Month=1 then Sales-ifnull(`01`,0) end ),2) as Jan,format(sum(Case when Month=2 then Sales-ifnull(`02`,0) end ),2) as  Feb,
		format(sum(Case when Month=3 then Sales-ifnull(`03`,0) end ),2) as Mar,format(sum(Case when Month=4 then Sales-ifnull(`04`,0) end ),2) as  Apr,
		format(sum(Case when Month=5 then Sales-ifnull(`05`,0) end ),2) as May,format(sum(Case when Month=6 then Sales-ifnull(`06`,0) end ),2) as  Jun,
		format(sum(Case when Month=7 then Sales-ifnull(`07`,0) end ),2) as Jul,format(sum(Case when Month=8 then Sales-ifnull(`08`,0) end ),2) as  Aug,
		format(sum(Case when Month=9 then Sales-ifnull(`09`,0) end ),2) as Sep,format(sum(Case when Month=10 then Sales-ifnull(`10`,0) end ),2) as  Oct,
		format(sum(Case when Month=11 then Sales-ifnull(`11`,0) end ),2) as Nov,format(sum(Case when Month=12 then Sales-ifnull(`12`,0) end ),2) as  `Dec`
		
		from TSales ts left join '.$currentyr.'_static.invty_weightedavecost wac on wac.ItemCode=ts.ItemCode join invty_1items i on i.ItemCode=ts.ItemCode Group By ts.ItemCode';
		// echo $sql; exit();	
		$columnnames=array('ItemCode','ItemDesc','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		include('../backendphp/layout/displayastablenosort.php');
}elseif($_POST['filter']=='Per Client'){

		$formdesc='Per Client';
		$title='';
		$sql1='Create temporary table TSales as select Month(Date) as Month,ItemCode,sum(UnitPrice*Qty) as Sales,ClientNo from invty_2sale s join invty_2salesub ss on ss.TxnID=s.TxnID Group By ItemCode,month(Date),ClientNo';
		// echo $sql1; exit();
		$stmt=$link->prepare($sql1); $stmt->execute();
		$sql='select ts.ClientNo,ClientName,
		format(sum(Case when Month=1 then Sales-ifnull(`01`,0) end ),2) as Jan,format(sum(Case when Month=2 then Sales-ifnull(`02`,0) end ),2) as  Feb,
		format(sum(Case when Month=3 then Sales-ifnull(`03`,0) end ),2) as Mar,format(sum(Case when Month=4 then Sales-ifnull(`04`,0) end ),2) as  Apr,
		format(sum(Case when Month=5 then Sales-ifnull(`05`,0) end ),2) as May,format(sum(Case when Month=6 then Sales-ifnull(`06`,0) end ),2) as  Jun,
		format(sum(Case when Month=7 then Sales-ifnull(`07`,0) end ),2) as Jul,format(sum(Case when Month=8 then Sales-ifnull(`08`,0) end ),2) as  Aug,
		format(sum(Case when Month=9 then Sales-ifnull(`09`,0) end ),2) as Sep,format(sum(Case when Month=10 then Sales-ifnull(`10`,0) end ),2) as  Oct,
		format(sum(Case when Month=11 then Sales-ifnull(`11`,0) end ),2) as Nov,format(sum(Case when Month=12 then Sales-ifnull(`12`,0) end ),2) as  `Dec`
		
		from TSales ts left join '.$currentyr.'_static.invty_weightedavecost wac on wac.ItemCode=ts.ItemCode join 1clients c on c.ClientNo=ts.ClientNo Group By ts.ClientNo';
		// echo $sql; exit();	
		$columnnames=array('ClientNo','ClientName','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
		include('../backendphp/layout/displayastablenosort.php');
		// $link=null; $stmt=null;
}
		}
		
		
	
	
	break;
	
	
}
?>