<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6402,'1rtc')) { echo 'No permission'; exit;} 
include_once('../switchboard/contents.php');
 ;
include_once('../backendphp/functions/editok.php');
include_once('../backendphp/layout/showencodedbybutton.php');

$method='POST';

if (allowedToOpen(6403,'1rtc')) { $adjcondition='';}
elseif (allowedToOpen(6404,'1rtc')) { $adjcondition=' AND m.AdjType<>0';}
else {$adjcondition=' AND m.AdjType=0';}
$txnid=intval($_REQUEST['TxnID']);


$user=$_SESSION['(ak0)'];
 //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="f9e7e9";
        $rcolor[1]="FFFFFF";
        
$whichqry=$_GET['w'];
switch ($whichqry){
CASE 'Adjust':
$title='Add/Edit Adjustments';

    $sqlmain='SELECT m.*, b.Branch, e.Nickname as EncodedBy, m.TimeStamp, m.Posted FROM invty_4adjust as m 
join `1employees` as e on e.IDNo=m.EncodedByNo
join `1branches` b on b.BranchNo=m.BranchNo
left join invty_2transfersub as s on m.TxnID=s.TxnID WHERE m.TxnID='.$txnid.$adjcondition;

    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $main='';

    if (editOk('invty_4adjust',$txnid,$link,'adjust')){
        $editmain='<td><a href="editauditspecifics.php?edit=2&w=AdjustMainEdit&TxnID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=invty_4adjust&l=invty OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
    } else {    
        $editmain='<td><a href="pradjust.php?w=ChargeSales&action_token='.$_SESSION['action_token'].'&TxnID='.$txnid.'">Send Charges</a></td>';
        $editsub=false;
    }
    
    $columnnamesmain=array('Date','AdjNo','Branch','Posted');
    $columnsub=array('ItemCode','Category','ItemDesc','Unit','Qty','UnitPrice','Amount','SerialNo','DefectiveOrGood');
    if ($showenc==1) { array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo'); array_push($columnsub,'EncodedBy','TimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}    
    
    $main='';
    
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    
    $main='<table><tr>'.$main.$editmain.'<td><a href="addeditadj.php?w=Adjust&TxnID='.$txnid.'"></a></td><tr></table>';
    
        $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit, Qty*UnitPrice as Amount, if(Defective=1,"Defective","Good Item") as DefectiveOrGood, e.Nickname as EncodedBy from invty_4adjustsub s join invty_1items i on i.ItemCode=s.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e on s.EncodedByNo=e.IDNo join invty_4adjust m on m.TxnID=s.TxnID
where m.TxnID='.$txnid.' Order By Category, ItemCode';    
    
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
        
        $defective=(allowedToOpen(6405,'1rtc')?str_repeat('&nbsp',8).'<a href="../invty/setasdefective.php?Defect='.$row['Defective'].'&TxnSubId='.$row['TxnSubId'].'&action_token='.$_SESSION['action_token'].'&w=SetDefectInAdj">Set_Defect</a>':'');
        $sub=$sub.($editsub?'<td><a href="editauditspecifics.php?edit=2&w=AdjustSubEdit&TxnSubId='.$row['TxnSubId'].'&TxnID='.$row['TxnID'].'">Edit</a>'
                .str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&TxnSubId='.$row['TxnSubId'].'&w=invty_4adjustSub&l=invty OnClick="return confirm(\'Really delete this?\');">Delete</a>'.$defective.'</td>':'').'</tr>';
        $colorcount++;
    }
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select count(s.ItemCode) as LineItems,sum(UnitPrice*Qty) as AdjustValue from  `invty_4adjustsub` s 
join `invty_4adjust` m on m.TxnID=s.TxnID 
Where m.TxnID='.$txnid.' Group By m.AdjNo';
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Line Items:  '.number_format($result['LineItems'],0).'&nbsp &nbsp &nbsp &nbsp Total Value:  '.number_format($result['AdjustValue'],2).str_repeat('&nbsp',4).'<a href="addcountmain.php?w=Adjust">Add New Adjustment</a><br>';
    
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>10,'required'=>true,'list'=>'items','autofocus'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'UnitPrice', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'Remarks', 'type'=>'text','size'=>10),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>10),
                    array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='pradjust.php?w=AdjustSubAdd&TxnID='.$txnid.'&txntype=Adjust';
    $liststoshow=array('items');
    // info for posting:
    $postvalue='1';
    $table='invty_4adjust';
    
        break;
    
}
 include('../backendphp/layout/inputsubform.php');
  $link=null; $stmt=null; 
?>