<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(64344,64345),'1rtc')) { echo 'No permission'; exit; }
$which=!isset($_GET['w'])?'SearchSimilar':$_GET['w'];
$showbranches=(in_array($which, array('ProductList','SubList'))?false:true); 
include_once('../switchboard/contents.php');


$choose=!isset($_GET['c'])?'SS':$_GET['c'];
$title='Products and Substitutions';  

$file='productandsubcodes.php?c='.$choose.'&w='; $fieldsinrow=4; $liststoshow=array(); 

$addcommand='Add'; $editcommand='Edit'; $editspecs='EditSpecifics'; $delcommand='Delete'; $addallowed=64344; $editallowed=64344; $delallowed=64344;

$columnswithlists=array();
    $listsname=array();
    $listssql=array();
  
if (in_array($which, array('ProductList','SubList'))){
    include_once('../backendphp/layout/linkstyle.php');
    ?>
    <div>
		<a id='link' href="productandsubcodes.php?w=ProductList&c=PC">Product Codes</a>
                <a id='link' href="productandsubcodes.php?w=SubList&c=SC">Substitution Codes</a>
    </div>
    <?php
    
}

if (in_array($which, array('ProductList','SubList','SearchSimilar'))){
 include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    echo comboBox($link,'SELECT ItemCode, ItemDesc FROM `invty_1items` ORDER BY ItemCode','ItemDesc','ItemCode','items'); 
    $input='Search for Item Code: <input type="text" name="ItemCode" list="items" size="10">';
    if ($which<>'SearchSimilar') { echo '<br><br> '.$input; } 
    else { echo '<title>Search Similar Products</title><form method=POST action=productandsubcodes.php?w=SearchSimilar>'.$input.'</form>';}
       
}
  

switch ($choose){

case 'PC': //Product Codes
if (!allowedToOpen(64344,'1rtc')) {   echo 'No permission'; exit;}
$list='ProductList';
$table='invty_1productcode'; $txnid='ProductCode'; $txnidname='ProductCode'; 
$sql='SELECT * FROM  invty_1productcode ';
$columnnameslist=array('ProductCode', 'ProductGenericDescription', 'ItemCodes'); 
$columnstoadd=array('ProductGenericDescription', 'ItemCodes');
$columnstoedit=$columnstoadd;



if($which=='ProductList') {
    $title='Product Codes';  
    $formdesc='Same product, same size, different brands';

    $columnentriesarray=array(
                    array('field'=>'ProductGenericDescription', 'type'=>'text','size'=>30, 'required'=>true),
                    array('caption'=>'Item codes separated by commas','field'=>'ItemCodes', 'type'=>'text','size'=>30, 'required'=>false)
                    );
    
    
}
    
  if (allowedToOpen(64344,'1rtc')) { $delprocess=$file.'Delete&'.$txnidname.'=';$editprocess=$file.'EditSpecifics&'.$txnidname.'='; $editprocesslabel='Edit'; $upload=$file.'Upload';}          

        
// set first field only if the first field should also be added/edited
$firstfield='ProductGenericDescription';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

$specific_instruct='test'; // for upload

$width='60%';
include('../backendphp/layout/genlists.php');

break;

case 'SC':
    if (!allowedToOpen(64344,'1rtc')) {   echo 'No permission'; exit;}
$list='SubList';
$table='invty_1substitution'; $txnid='SubCode'; $txnidname='SubCode'; 
$sql='SELECT * FROM  invty_1substitution ';
$columnnameslist=array('SubCode', 'ProductSubstitutionDescription', 'ItemCodes'); 
$columnstoadd=array('ProductSubstitutionDescription', 'ItemCodes');
$columnstoedit=$columnstoadd;

if($which=='SubList') {
    $title='Substitution Codes';  
    $formdesc='Same product usage, different sizes, different brands';

    $columnentriesarray=array(
                    array('field'=>'ProductSubstitutionDescription', 'type'=>'text','size'=>30, 'required'=>true),
                    array('caption'=>'Item codes separated by commas','field'=>'ItemCodes', 'type'=>'text','size'=>30, 'required'=>false)
                    );
}
    
    if (allowedToOpen(64344,'1rtc')) { $delprocess=$file.'Delete&'.$txnidname.'=';$editprocess=$file.'EditSpecifics&'.$txnidname.'='; $editprocesslabel='Edit'; $upload=$file.'Upload';}       

        
// set first field only if the first field should also be added/edited
$firstfield='ProductSubstitutionDescription';
//set a first field so commas will work 
$sqlinsert='INSERT INTO `'.$table.'` SET ';   
$sqlupdate='UPDATE `'.$table.'` SET ';

$specific_instruct='test'; // for upload

$width='60%';
include('../backendphp/layout/genlists.php');
break;

case 'SS':
    if (!allowedToOpen(64345,'1rtc')) {   echo 'No permission'; exit;}
    
    $title='Search Similar Products'; 
    echo '<br><h3>'.$title.'</h3>';
    if (!isset($_POST['ItemCode'])){ echo '<br><br>No item chosen.'; goto noform;}
  
    $item=$_POST['ItemCode'];
    
    $sql0='SELECT * FROM invty_1productcode WHERE FIND_IN_SET('.$item.',ItemCodes);';
    $stmt0=$link->query($sql0); $respc=$stmt0->fetch();
    $sql0='SELECT * FROM invty_1substitution WHERE FIND_IN_SET('.$item.',ItemCodes);';
    $stmt0=$link->query($sql0); $ressc=$stmt0->fetch();
    
    include('maketables/getasofmonth.php');
    include('maketables/createitemact.php');
    
    $columnnames=array('ItemCode','Category','Description','GoodItem','Defective','EndInvToday','Unit');
    $showtotals=false; $title='';

    //echo $respc['ItemCodes'].'<br>';
    if ($respc['ItemCodes']==''){ echo '<h4>'.$subtitle.'</h4><br>No similar product'; goto substitute;}
      $sql= 'SELECT BranchNo,a.ItemCode,i.CatNo,c.Category,i.ItemDesc as Description,i.Unit, SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,SUM(Qty) as EndInvToday FROM ItemAct a JOIN invty_1items i ON i.ItemCode=a.ItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo WHERE i.ItemCode IN ('.$respc['ItemCodes'].') GROUP BY i.ItemCode, BranchNo' ;    

       $subtitle='Same Product and Size, Different Brands: '.$respc['ProductGenericDescription'];
   //    if ($_SESSION['(ak0)']==1002) { echo $respc['ItemCodes'].'<br>'.$sql;}
       include('../backendphp/layout/displayastable.php');
  

       substitute:
           $subtitle='Substitute Products: '.$ressc['ProductSubstitutionDescription'];
          //  echo $ressc['ItemCodes'].'<br>';
       if ($ressc['ItemCodes']==''){ echo '<h4>'.$subtitle.'</h4><br>No substitute'; goto noform;}
      $sql= 'SELECT BranchNo,a.ItemCode,i.CatNo,c.Category,i.ItemDesc as Description,i.Unit, SUM(CASE WHEN Defective<>1 AND Defective<>2 THEN Qty ELSE 0 END) as GoodItem, SUM(CASE WHEN Defective=1 OR Defective=2 THEN Qty ELSE 0 END) as Defective,SUM(Qty) as EndInvToday FROM ItemAct a JOIN invty_1items i ON i.ItemCode=a.ItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo WHERE a.ItemCode IN ('.$ressc['ItemCodes'].') GROUP BY a.ItemCode' ;    
 
   // if ($_SESSION['(ak0)']==1002) { echo $respc['ItemCodes'].'<br>'.$sql;}
    include('../backendphp/layout/displayastable.php');
   
    break;
}
noform:
?>