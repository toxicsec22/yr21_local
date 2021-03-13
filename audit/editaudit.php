<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6401,'1rtc')) { echo 'No permission'; exit;}  


include_once('../backendphp/functions/editok.php');
$showbranches=true;
include_once('../switchboard/contents.php');
include_once('../backendphp/layout/showencodedbybutton.php');



$method='POST';


 //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="FEEDD5";
        $rcolor[1]="FFFFFF";

$whichqry=$_GET['w'];

switch ($whichqry){
    case 'InvCount':
$txnid=$_REQUEST['CountID'];      
      $title='Add/Edit Audit';
      $showbranches=true;
    $sqlmain='SELECT b.Branch, c.Date, e.Nickname as AuditedBy, c.CountID,  Posted,c.Remarks, c.PostedByNo FROM audit_2countmain c join `1branches` b on b.BranchNo=c.BranchNo
left join `1employees` e on e.IDNo=c.AuditedByNo
where  CountID='.$txnid;
    
    $stmt=$link->query($sqlmain); $result=$stmt->fetch();
    
    $main=''; $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Category, ItemCode');
    
    if (editOk('audit_2countmain',$txnid,$link,$whichqry)){
        $editmain='<td><a href="editauditspecifics.php?edit=2&w=InvCountMainEdit&CountID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=praddaudit.php?CountID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=InvCountMainDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
    } else {
        $editmain='<td><a href="printaudit.php?w=InvCount&CountID='.$txnid.'">Print Preview</a></td>';
        $editsub=false;
    }
    
      $columnnamesmain=array('Date','Branch','Remarks','Posted');
      $columnsub=array('ItemCode','Category','ItemDesc','Unit','ComputerEndGood','ComputerEndDefective','Count','Diff','Remarks','CountID');
    if ($showenc==1) { array_push($columnnamesmain,'AuditedBy','PostedByNo'); array_push($columnsub,'EncodedBy','TimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}    
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $hidedisc=isset($_GET['hidedisc']);
    $main='<table><tr>'.$main.$editmain.'<td><a href="editaudit.php?w=InvCount'.($hidedisc?'':'&hidedisc=true').'&CountID='.$txnid.'">'.($hidedisc?'Show All Items':'Hide No Discrepancies').'</a></td><td><a href="praddaudit.php?w=UpdateComputerEnd&CountID='.$txnid.'&action_token='.$_SESSION['action_token'].'">Update Computer End Inv Data</a></td>'.($result['Posted']==0?'<td><a href="editaudit.php?w=ScanItems&CountID='.$txnid.'">Enter Items with Scanner</a></td><td>Add Items for Category No: <form method="post" action="praddaudit.php?w=AddPerCat&CountID='.$txnid.'"><input type="text" size="8px" name="catno"><input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"> <input type="submit" value="&nbsp Add &nbsp" size="10px"></form></td>':'<td><a href=praddaudit.php?CountID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=InvCountAutoAdj OnClick="return confirm(\'Make an adjusting entry for '.$result['Branch'].' dated '.$result['Date'].'?\');">Auto Adjust</a></td>').'<tr></table>';
    
    if ($hidedisc){
      $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy, ComputerEndGood, ComputerEndDefective, (s.Count-ComputerEndGood-ComputerEndDefective) as Diff from audit_2countsub s join `invty_1items` i on i.ItemCode=s.ItemCode join `invty_1category` c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join audit_2countmain m on m.CountID=s.CountID
where m.CountID='.$txnid.' and (s.Count-ComputerEndGood-ComputerEndDefective)<>0 Order By '.$sortfield;   
    } else {
        $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit, e1.Nickname as EncodedBy, ComputerEndGood, ComputerEndDefective, (s.Count-ComputerEndGood-ComputerEndDefective) as Diff from audit_2countsub s join `invty_1items` i on i.ItemCode=s.ItemCode join `invty_1category` c on c.CatNo=i.CatNo left join `1employees` as e1 on s.EncodedByNo=e1.IDNo join audit_2countmain m on m.CountID=s.CountID where m.CountID='.$txnid.' Order By '.$sortfield; 
    }
     
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
        $sub=$sub.($editsub?'<td><a href="editauditspecifics.php?edit=2&w=InvCountSubEdit&CountSubID='.$row['CountSubID'].'&CountID='.$row['CountID'].'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href=praddaudit.php?CountID='.$txnid.'&CountSubID='.$row['CountSubID'].'&action_token='.$_SESSION['action_token'].'&w=InvCountSubDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
        $colorcount++;
    }
    $sub='<table><tr>'.$subcol.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select count(s.ItemCode) as LineItems from  `audit_2countsub` s 
join `audit_2countmain` m on m.CountID=s.CountID
join `1branches` b on b.BranchNo=m.BranchNo
Where m.CountID='.$txnid.' Group By m.CountID';
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Line Items:  '.number_format($result['LineItems'],0).'&nbsp &nbsp &nbsp &nbsp<a href="addcountmain.php?w='. $whichqry. '</a><br>';

$itemslist='';// removed this from audit so faster loading: 'items';
$liststoshow=array('');

    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'autofocus'=>true),
                    array('field'=>'Count', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'Remarks', 'type'=>'text','size'=>20, 'required'=>false),
                    array('field'=>'CountID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='praddaudit.php?w=CountInvSubAdd&CountID='.$txnid;
    // info for posting:
    $post='1';
    $table='audit_2countmain';
    $txntype='audit';
        break;

CASE 'ScanItems':
$txnid=$_REQUEST['CountID'];
$title='Scan Items for Audit';
    $showbranches=false;
    $sqlmain='SELECT b.Branch, c.Date, e.Nickname as AuditedBy, c.CountID,  Posted,c.Remarks, c.PostedByNo FROM audit_2countmain c join `1branches` b on b.BranchNo=c.BranchNo
left join `1employees` e on e.IDNo=c.AuditedByNo
where  CountID='.$txnid;
    
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    
    $main='';
    $columnnamesmain=array('Date','Branch','Remarks','AuditedBy');
   $columnsub=array('ItemCode','Category','ItemDesc','Unit','Count');
    $editmain=''; 
   if (editOk('audit_2countmain',$txnid,$link,$whichqry)){
       $editsub=true;
    } else {
        $editsub=false;
    }
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
    $sqlsub='Select s.*, c.Category, i.ItemDesc, i.Unit from audit_2countsubbarcode s left join `invty_1items` i on s.ItemCode=i.ItemCode left join `invty_1category` c on c.CatNo=i.CatNo where CountID='.$txnid.' order by s.`TS` DESC ';
    $stmt=$link->query($sqlsub);
    $result=$stmt->fetchAll();
    
    $sub='';$subcol='';
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $sub='<tr>'.$sub.($editsub?'<td><a href=praddaudit.php?CountID='.$txnid.'&BarcodeID='.$row['BarcodeID'].'&action_token='.$_SESSION['action_token'].'&w=InvCountSubScanDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'').'</tr>';
    }
    $sub='<table><tr>'.$subcol.'<td>Delete?</td></tr><tbody>'.$sub.'</tbody></table>';
    $sqlsum='Select Count(ItemCode) as CountofItemCode from audit_2countsubbarcode s where CountID='.$txnid. ' group by CountID';
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='Line Items:  '.$result['CountofItemCode'].'<br>';
   
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>true,'autofocus'=>true),
                    array('field'=>'Count', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>1),
                    array('field'=>'CountID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
   
    $action='praddaudit.php?w=InvCountSubScan&CountID='.$txnid;
    $liststoshow=array();
    $addlsubmit='<form method=POST action="praddaudit.php?w=InvCountSubScanSend&CountID='.$txnid.'" enctype="multipart/form-data">
<input type="hidden" name="action_token" value="'.html_escape($_SESSION['action_token']).'" />'.str_repeat('&nbsp',20).
'<input type="submit" name="send" value="Send Scanned Data to Count"> <br><br>';
   
      break;

}
    $left='90%'; $leftmargin='91%'; $right='9%';
     include('../backendphp/layout/inputsubform.php');
  $link=null; $stmt=null;
?>