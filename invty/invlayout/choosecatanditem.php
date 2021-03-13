<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 

//include('../backendphp/functions/getallcolumnnames.inc');
    
   // $title='';//'Choose Category & Item';
include_once('../switchboard/contents.php');
include_once('../generalinfo/lists.inc');
$liststoshow=array('categories');
foreach ($liststoshow as $list){ renderlist($list);    }

if ((!isset($_POST['Category'])) and (!isset($_POST['ItemCode']))) {
    echo '<br><h3>'.$title.'</h3>';
            ?>
            <form action="#" method="POST" style="display: inline">
	Choose Category to limit Item List<input type='text' name='Category' list='categories' autocomplete='off'></form>
            <form  action='#' method="POST" style="display: inline">
                    &nbsp; or type Item Code directly <input type='text' name='itemcode' list='itemspercat' autocomplete='off'></form>
            <?php
        } elseif (isset($_POST['Category'])){
		$catno=getValue($link,'invty_1category','Category',$_POST['Category'],'CatNo'); ?>
                <form action="#" method="POST" style="display: inline">
                Choose Category to limit Item List<input type='text' name='Category' list='categories' autocomplete='off'>
		<input type='hidden' name='CatNo' value=<?php echo $catno; ?>> </form>
		<form  action='#' method="POST" style="display: inline">
		<?php
                echo '<br><br>'.$_POST['Category'];
		renderListWithCondition('items',$catno);
		?>
		Item Code<input type='text' name='itemcode' list='itemspercat' autocomplete='off'><!--<input type="submit" name="submit" value="Choose this item">-->
		<input type='hidden' name='CatNo' value=<?php echo $catno; ?>>
		<input type='hidden' name='Category' value='<?php echo $_POST['Category']; ?>'></form>
		
	<?php }  //end if
	
	if (isset($_REQUEST['itemcode'])){
		$itemcode=$_REQUEST['itemcode'];
		$sql='Select Category, ItemDesc, Unit from `invty_1items` i JOIN `invty_1category` c ON c.CatNo=i.CatNo where ItemCode='.$itemcode;
		$stmt=$link->query($sql); $result=$stmt->fetch();
		//$itemdesc=getValue($link,'invty_1items','ItemCode',$itemcode,'ItemDesc');
		$titleadd=" for (Item Code ". $itemcode . ") - &nbsp &nbsp &nbsp" . $result['Category'] . ", " . $result['ItemDesc']. "  (" . $result['Unit'].")";
	}
	?>
	<br>
