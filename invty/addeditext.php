<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(692,6920,6921,69201,69202);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
$showbranches=false;
// end of check
include_once('../switchboard/contents.php');
 
include_once('../backendphp/functions/editok.php');

include_once('../backendphp/layout/showencodedbybutton.php');

$method='POST';

$txnid=intval($_REQUEST['TxnID']);


$user=$_SESSION['(ak0)'];
 //to make alternating rows have different colors
        $colorcount=0;
        $color1="DBFFDB";
        $color2="FFFFFF";
        //delete the next 2 when editing is direct in cell
        $rcolor[0]="DBFFDB";
        $rcolor[1]="FFFFFF";
        
        
$whichqry=$_GET['w'];
switch ($whichqry){
/* CASE 'Request':
if (!allowedToOpen(6920,'1rtc')) { echo 'No permission'; exit;}
        $title='Add/Edit Request - External';
        $formdesc='Quantities must be finalized here.<br>Auto Request Qty = Sold + LostSales + Unserved Branch Requests - Undelivered By Suppliers';
        $txntype='Request';
    $sqlmain='select rm.*, b.Branch as RequestingBranch, e.Nickname as EncodedBy from invty_3extrequest as rm
    join `1branches` as b on rm.BranchNo=b.BranchNo
left join `1employees` as e on rm.EncodedByNo=e.IDNo where TxnID='.$txnid;
   
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $main='';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.TimeStamp');
    
    $columnnamesmain=array('Date','RequestNo','RequestingBranch','Remarks','DateReq');
        
    if (editOk('invty_3extrequest',$txnid,$link,$txntype)){
        $editmain='<td><a href="editextspecifics.php?edit=2&w=RequestMainEdit&txntype='.$txntype.'&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=praddext.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=RequestMainDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
        $columnsub=array('ItemCode','Category','ItemDesc','Unit','Qty','Sold','EndInvToday','SupplierUndelivered','UnservedBranchRequest','LostSales');
    } else {
        $editmain='<td><a href="praddext.php?w=SendforDistri&TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'">Choose Supplier Distribution</a></td>';
        $editsub=false;
        $columnsub=array('ItemCode','Category','ItemDesc','Unit','Qty','Sold','EndInvToday','SupplierUndelivered','UnservedBranchRequest','LostSales');
    }
    
    if ($showenc==1) { array_push($columnnamesmain,'TimeStamp','EncodedBy'); array_push($columnsub,'TimeStamp','EncodedBy');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
    $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy from invty_3extrequestsub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo where TxnID='.$txnid.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    
    $sub='';$subcol='';
   
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    
    foreach($result as $row){
      $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        
        $sub=$sub.($editsub?'<td><a href="editextspecifics.php?edit=2&w=RequestSubEdit&txntype='.$txntype.'&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href=praddext.php?TxnID='.$txnid.'&TxnSubId='.$row['TxnSubId'].'&action_token='.$_SESSION['action_token'].'&w=RequestSubDel&txntype='.$txntype.' OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
       // $sub=$sub;
    $colorcount++;    
    }
    
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select count(s.ItemCode) as LineItems,sum(c.UnitCost*Qty) as ReqValue from `invty_3extrequestsub` s 
join `invty_3extrequest` m on m.TxnID=s.TxnID
left join `invty_52latestcost` c on s.ItemCode=c.ItemCode
join `1branches` b on b.BranchNo=m.BranchNo
Where m.TxnID='.$txnid;
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Line Items with No PO:  '.number_format($result['LineItems'],0).'&nbsp &nbsp &nbsp &nbsp Total Value:  '.number_format($result['ReqValue'],2);
    
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'autofocus'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='praddext.php?w=RequestSubAdd&TxnID='.$txnid.'&txntype=Request';
    $liststoshow=array();
    // info for posting:
    $post='1';
    $table='invty_3extrequest';
    
        break;
  */     
case 'SendforDistri':
    if (!allowedToOpen(692,'1rtc')) { echo 'No permission'; exit;}
$title='Choose Suppliers';
$main='<a href="praddext.php?&w=MakePO&TxnID=1&action_token='.$_SESSION['action_token'].'">Make PO\'s </a><br>';
   $sql='SELECT d.*, c.Category, i.ItemDesc as Description, i.Unit, b.Branch as RequestingWH, s.SupplierName, ca.Company FROM invty_3distributeorders d 
join invty_1items i on i.ItemCode=d.ItemCode join invty_1category c on c.CatNo=i.CatNo
join `1branches` as b on d.BranchNo=b.BranchNo
left join `1companies` as ca on d.CompanyNo=ca.CompanyNo
left join `1suppliers` as s on d.SupplierNo=s.SupplierNo order by Category';
      $stmt=$link->query($sql);
      $result=$stmt->fetchAll();
      $columnsub=array('RequestNo','ItemCode','Category','Description','Unit','Qty','DateReq','UnitCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5','RequestingWH','SupplierName','Company');
      $editsub=true;
      $sub='';$subcol='';

    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    
    foreach($result as $row){
      $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        
        $sub=$sub.($editsub?'<td><a href="editextspecifics.php?edit=2&w=SendforDistriEdit&TxnSubId='.$row['TxnSubId'].'&TxnID=1">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href=praddext.php?TxnID=1&TxnSubId='.$row['TxnSubId'].'&action_token='.$_SESSION['action_token'].'&w=SendforDistriDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
       
    $colorcount++;    
    }
    
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select count(o.ItemCode) as LineItems,sum(UnitCost*Qty) as TotalCost from `invty_3distributeorders` o';
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Line Items:  '.number_format($result['LineItems'],0).'&nbsp &nbsp &nbsp &nbsp Total Cost:  '.number_format($result['TotalCost'],2);
    
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'list'=>'externalitems','autofocus'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'UnitCost', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'SupplierNo', 'type'=>'text', 'size'=>10,'required'=>true,'list'=>'suppliers'),
                    array('field'=>'RequestNo', 'type'=>'text', 'size'=>10,'required'=>true,'list'=>'externalreqno'),
                    array('field'=>'CompanyNo', 'type'=>'text', 'size'=>10,'list'=>'companies')
                    );
        
    $action='praddext.php?w=SendforDistriAdd&TxnID=1&txntype=Request';
    $liststoshow=array('suppliers','companies');
    $listcondition='';
    $whichotherlist='invty';
    $otherlist=array('externalreqno','externalitems');
    
break;

CASE 'Order':
// if (!allowedToOpen(6920,'1rtc') AND !allowedToOpen(69201,'1rtc')) { echo 'No permission'; exit;}
if (!allowedToOpen(array(6920,69201,69202),'1rtc')) { echo 'No permission'; exit;}

        $title='Add/Edit Purchase Order';
		$formdesc='Note: Cannot post if price level is less than or equal to unit cost.';
		echo '<br>';
        $txnid=intval($_REQUEST['TxnID']);
        $txntype='Order';
    $sqlmain='select o.*, s.SupplierNo,s.SupplierName, b.Branch as RequestingBranch, Company, e.Nickname as EncodedBy from invty_3order as o
    join `1branches` as b on o.BranchNo=b.BranchNo
    join `1suppliers` as s on o.SupplierNo=s.SupplierNo
    LEFT JOIN `1companies` AS c ON c.CompanyNo=o.CompanyNo
left join `1employees` as e on o.EncodedByNo=e.IDNo where TxnID='.$txnid;
   
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $suppno=$result['SupplierNo'];
    $main='';
    $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.TimeStamp');
    $columnnamesmain=array('Date','PONo','SupplierName','RequestingBranch','Remarks','DateReq','Company','Posted');
    // // choose appropriate columns
        if (allowedToOpen(6921,'1rtc')) { $columnsub=array('ItemCode','Category','ItemDesc','Unit','Qty','UnitCost','PriceLevel1','PriceLevel2','PriceLevel3','PriceLevel4','PriceLevel5');}
        else { $columnsub=array('ItemCode','Category','ItemDesc','Unit','Qty');}
   // // end of columns
        
    if (editOk('invty_3order',$txnid,$link,$txntype)){
		if(!allowedToOpen(69202,'1rtc')){
			$editmain='<td><a href="editextspecifics.php?edit=2&w=OrderMainEdit&txntype='.$txntype.'&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=praddext.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=OrderMainDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
			$editsub=true;
		}
        
    } else {
         if ($result['Posted']==1 and $result['Approved']==1){
            $editmain='<td><a href="printpo.php?w=Order&TxnID='.$txnid.'">Print Preview</a></td>';
        } else {
            $editmain=(allowedToOpen(6921,'1rtc')?'<td>
                       <form method=post action="praddext.php?w=ApprovePO&TxnID='.$txnid.'">
                       <input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
           <input type="submit" name="Approval" value="Approve">  <input type="submit" name="Approval" value="Deny">
           </form></td>':'<td>'.($result['Approved']==2?'Denied':'Wait for approval').'</td>');
        }
      
        $editsub=false;
    }
    
    
    if ($showenc==1) { array_push($columnnamesmain,'TimeStamp','EncodedBy'); array_push($columnsub,'TimeStamp','EncodedBy');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
    $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy from invty_3ordersub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo where TxnID='.$txnid.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    
    $sub='';$subcol='';
   
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    
    foreach($result as $row){
      $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        if(!allowedToOpen(69202,'1rtc')){
			$sub=$sub.($editsub?'<td><a href="editextspecifics.php?edit=2&w=OrderSubEdit&txntype='.$txntype.'&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href=praddext.php?TxnID='.$txnid.'&TxnSubId='.$row['TxnSubId'].'&action_token='.$_SESSION['action_token'].'&w=OrderSubDel&txntype='.$txntype.' OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
		}
       // $sub=$sub;
    $colorcount++;    
    }
    
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table><br>';
	
		
    $sqlsum=(allowedToOpen(6921,'1rtc')?'Select count(s.ItemCode) as LineItems,sum(UnitCost*Qty) as POValue from `invty_3ordersub` s join `invty_3order` m on m.TxnID=s.TxnID Where m.TxnID='.$txnid:'Select count(s.ItemCode) as LineItems,0 as POValue from `invty_3ordersub` s join `invty_3order` m on m.TxnID=s.TxnID Where m.TxnID='.$txnid);
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Line Items Ordered:  '.number_format($result['LineItems'],0).'&nbsp &nbsp &nbsp &nbsp Sum:  '.number_format($result['POValue'],2);
    
    $columnnames=array(
                    // array('field'=>'ItemCode','id'=>'ItemCode','type'=>'text','size'=>20,'required'=>true,'list'=>'items','autofocus'=>true),
                    array('field'=>'ItemCode','id'=>'ItemCode','type'=>'text','size'=>20,'required'=>true,'autofocus'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    // array('field'=>'UnitCost', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid),
                   array('field'=>'SupplierNo', 'type'=>'hidden', 'size'=>0,'value'=>$suppno)
                    );
					
	// array_push($columnnames,array('field'=>'UnitCost', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0));
        
    $action='praddext.php?w=OrderSubAdd&TxnID='.$txnid.'&txntype=Order';
	
	// if(allowedToOpen(6921,'1rtc')){
	echo '<script type="text/javascript" language="javascript" src="https://'.$_SERVER['HTTP_HOST'].'/acrossyrs/js/jquery-3.3.1.js"></script>';
		echo '
			<br />
			<div id="result"></div>
		</div>
		<div style="clear:both"></div>
		'; 
	// }
	 include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
   $sqlsup='SELECT ItemCode, concat(ItemDesc,\' - \',Category) as ItemDesc from invty_1items join invty_1category on invty_1category.CatNo=invty_1items.CatNo order by Category,ItemDesc';
	echo comboBox($link,$sqlsup,'ItemDesc','ItemCode','items');
	
	

    // info for posting:
    $post='1';
	
    $table='invty_3order';
    
        break;
}
 $left='90%'; $leftmargin='91%'; $right='9%';
 include('../backendphp/layout/inputsubform.php');
       $link=null; $stmt=null;
?>

<script>
$(document).ready(function(){
	load_data();
	function load_data(query)
	{
		$.ajax({
			url:"fetchcostperitem.php",
			method:"post",
			data:{query:query},
			success:function(data)
			{
				$('#result').html(data);
			}
		});
	}
	
	$('#ItemCode').keyup(function(){
		var search = $(this).val();
		if(search != '')
		{
			load_data(search);
		}
		else
		{
			load_data();			
		}
	});
});
</script>