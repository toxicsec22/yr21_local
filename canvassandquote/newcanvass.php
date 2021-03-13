<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once('../switchboard/contents.php');
 

    ;
    $user=$_SESSION['(ak0)'];
 
$whichqry=$_GET['w'];
switch ($whichqry){
CASE 'Canvass':
$title='New Request for Canvass';
    if (!allowedToOpen(6268,'1rtc')) {   echo 'No permission'; exit;}

    $columnnames=array(
                       array('field'=>'CanvassDate','type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d'),'list'=>null),
                       array('field'=>'ForBranch','type'=>'text','size'=>20,'required'=>true,'list'=>'branches', 'value'=>$_SESSION['@brn']),
                       array('field'=>'ForClientName','type'=>'text','size'=>20,'required'=>true,'list'=>'clients'),
                       array('field'=>'Category','type'=>'text','size'=>20,'required'=>true,'list'=>'categories'), 
                       array('field'=>'ItemCode', 'type'=>'text','size'=>20,'required'=>false,'list'=>'items'),
                       array('field'=>'Description', 'type'=>'text','size'=>100,'required'=>true,'list'=>null),
                       array('field'=>'CanvassRequestedByNo', 'type'=>'hidden','size'=>2,'value'=>$user,'list'=>null, 'required'=>false),
                       array('field'=>'CanvassRequestTS', 'type'=>'hidden','size'=>2,'value'=>date('Y-m-d h:m:s'),'list'=>null, 'required'=>false));
    $method='POST';
    $action='praddcanvass.php?calledfrom=1';
    $showbranches=true;
    $liststoshow=array('branches','categories','clientswhole');
     include('../backendphp/layout/inputform.php');
 break;

CASE 'Quote':
    if (!allowedToOpen(6271,'1rtc')) {   echo 'No permission'; exit;}
 
    $title='New Quotation';
    $columnnames=array(
                       array('field'=>'QuoteDate','type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                       array('field'=>'ClientName','type'=>'text','size'=>20,'required'=>true,'list'=>'clients'),
                       array('field'=>'ContactPerson','type'=>'text','size'=>20,'required'=>true),
                       array('field'=>'Position','type'=>'text','size'=>20,'required'=>false), 
                       array('field'=>'SirMaam', 'caption'=>'Sir(1) or Maam(2)?', 'type'=>'text','size'=>5,'required'=>true,'value'=>1),
                       array('field'=>'FaxNo','type'=>'text','size'=>20,'required'=>false),
                       array('field'=>'Warranty', 'type'=>'text','size'=>20,'required'=>true,'value'=>'\'Not applicable\''),
                       array('field'=>'Payment', 'type'=>'text','size'=>20,'required'=>true,'value'=>'\'C.O.D.\''),
                       array('field'=>'Note1', 'type'=>'text','size'=>30,'required'=>true,'value'=>'\'Price validity: seven (7) days.\''),
                       array('field'=>'Note2', 'type'=>'text','size'=>50,'required'=>true,'value'=>'\'Availability of stock must be confirmed once approved.\''),
                       array('field'=>'Note3', 'type'=>'text','size'=>20,'required'=>false));
    $method='POST';
    $action='praddcanvass.php?calledfrom=4';
    $showbranches=false;
    $liststoshow=array('clientswhole');
     include('../backendphp/layout/inputform.php');
 break;

}
  $link=null; $stmt=null;
    ?>
