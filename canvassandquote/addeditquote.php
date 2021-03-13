<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6272,'1rtc')) {   echo 'No permission'; exit;}   
include_once('../switchboard/contents.php');
 
include_once('../backendphp/layout/showencodedbybutton.php');

$showbranches=false;
$method='POST';

$txnid=$_REQUEST['QuoteID'];
;
 //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="CEF6F5";
        $rcolor[1]="FFFFFF";
 
$title='Edit Quotation';
    $sqlmain='SELECT m.*, if(SirMaam=1,\'Sir\',\'Ma`am\') as SirMaam, e.Nickname as EncodedBy, CONCAT("'.substr($currentyr,2,2).'-",LPAD(m.QuoteID,4,"0")) AS QuoteNo FROM quotations_2quotemain m left join `1employees` as e on e.IDNo=m.EncodedByNo
WHERE m.QuoteID='.$txnid;
    if ($_SESSION['(ak0)']==1002) { echo $sqlmain;  }
    $stmt=$link->query($sqlmain); $result=$stmt->fetch();
 
    $columnnamesmain=array('QuoteNo','QuoteDate','ContactPerson','Payment','Note1','ClientName','SirMaam','Warranty','Note2','Position','Note3','FaxNo','Posted');
    $columnsub=array('ItemCode','Description','Qty','Unit','UnitPrice','Amount');
 
    $main='';
    
    if ($result['Posted']==0){
        $editmain='<td><a href="editspecificsquote.php?edit=2&calledfrom=6&QuoteID='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=praddcanvass.php?QuoteID='.$txnid.'&action_token='.$_SESSION['action_token'].'&calledfrom=8 OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true; $columnstoedit=array('QuoteDate','ClientName','ContactPerson','Position','SirMaam','FaxNo','Warranty','Payment','Note1','Note2','Note3'); $editok=true;
    } else {
        $editmain='<td><a href="printquote.php?QuoteID='.$txnid.'">Print Preview</a></td>';
        $editok=false;$editsub=false; $columnstoedit=array();
    }
    
    if ($showenc==1) { array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo'); array_push($columnsub,'EncodedBy','TimeStamp');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}    
    
    $colno=0;
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'</tr></table>';
    
    $sqlsub='Select s.*, Qty*UnitPrice as Amount, e.Nickname as EncodedBy from quotations_2quotesub s left join `1employees` as e on s.EncodedByNo=e.IDNo join quotations_2quotemain m on m.QuoteID=s.QuoteID
join `invty_1items` i on i.ItemCode=s.ItemCode 
WHERE m.QuoteID='.$txnid.' Order By Description';
    
    $sqlsum='Select sum(Qty*UnitPrice) as Total from  `quotations_2quotesub` s join `quotations_2quotemain` m on m.QuoteID=s.QuoteID Where m.QuoteID='.$txnid;
   
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $addlinfo='<br><br>Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10).'<a href="newcanvass.php?w=Quote">Add Quote</a>';
    
    $columnnames=array(
                    array('field'=>'ItemCode', 'type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Qty', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'UnitPrice', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0),
                    array('field'=>'Additional_Description', 'type'=>'text','size'=>20, 'required'=>false),
                    array('field'=>'QuoteID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                    );
        
    $action='praddcanvass.php?calledfrom=5&QuoteID='.$txnid;
    $editprocess='editspecificsquote.php?edit=2&calledfrom=7&QuoteID='.$txnid.'&QuoteSubID='; $editprocesslabel='Edit';
    $delprocess='praddcanvass.php?QuoteID='.$txnid.'&calledfrom=9&QuoteSubID=';
    $txnsubid='QuoteSubID'; $withsub=true;
    $liststoshow=array();
    // info for posting:
    $post='1'; $table='quotations_2quotemain'; $txntype='quote';
 // $left='90%'; $leftmargin='91%'; $right='9%'; 
 include('../backendphp/layout/inputsubform.php');
   $link=null; $stmt=null;
?>