<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
// $allowed=array(693,694);
$allowed=array(6920);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=$allow+1; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
// end of check
$showbranches=true; include_once('../switchboard/contents.php');
 


    $method='POST';
$txntype=$_GET['w'];
$whichqry=$_GET['w'];


$user=$_SESSION['(ak0)'];
switch ($whichqry){
	//removed
    /* case 'Request':
        $title='Add External Request';
        $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'RequestNo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'DateReq', 'caption'=>'Date Required', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
                    array('field'=>'BranchNo', 'type'=>'hidden','size'=>0, 'required'=>false)                  
                           );
        $liststoshow=array();
          break; */
   case 'Order':
       if (!allowedToOpen(6920,'1rtc')) {   echo 'No permission'; exit;}    
        $title='Add Purchase Order';
        $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    // array('field'=>'PONo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'SupplierNo', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'suppliers'),
                    // array('field'=>'RequestNo', 'type'=>'text','size'=>10, 'required'=>true),
                    array('field'=>'DateReq', 'caption'=>'Date Required', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
                    array('field'=>'BranchNo', 'type'=>'text','size'=>10, 'required'=>true,'list'=>'branches', 'value'=>$_SESSION['bnum']),
                    // array('field'=>'CompanyNo', 'type'=>'text', 'size'=>10,'list'=>'companies')
                    array('field'=>'CompanyNo', 'type'=>'text', 'size'=>10,'list'=>'companiesshortname')
                           );
        $liststoshow=array('suppliers','branches','companiesshortname');
          break;
}
         
    $action='praddext.php?w='.$whichqry.'&txntype='.$txntype;
    include('../backendphp/layout/inputmainform.php');
      $link=null; $stmt=null;
    ?>
