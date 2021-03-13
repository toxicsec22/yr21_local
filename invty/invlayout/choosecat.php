<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 

//include('../backendphp/functions/getallcolumnnames.inc');
    
    //$title='';//'Choose Category & Item';
include_once('../generalinfo/lists.inc');
$liststoshow=array('categories');
foreach ($liststoshow as $list){
renderlist($list);    
}
if (!isset($_POST['Category'])){ echo '<br><h3>'.$title.'</h3>'; }
?><br><br>
	<form action="#" method="POST" style="display: inline">
	Category <input type='text' name='Category' list='categories' size=40 autocomplete='off' value='<?php echo (isset($_POST['Category'])?$_POST['Category']:'');?>'>
	<?php 
	if (isset($_POST['Category'])){
	//	$catno=getValue($link,'invty_1category','Category',$_POST['Category'],'CatNo');
		$titleadd="&nbsp &nbsp". $_POST['Category'] ?><br><br>
		<!--<input type='hidden' name='CatNo' value=<?php echo $catno; ?>>-->
	</form>
	<?php } //end if
	?>