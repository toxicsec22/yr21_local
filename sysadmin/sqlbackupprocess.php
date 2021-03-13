<?php
$get_all_table_query = "SHOW FULL TABLES WHERE `Table_type` LIKE 'BASE TABLE';";
$statement = $connect->prepare($get_all_table_query);
$statement->execute();
$result = $statement->fetchAll();

if(isset($_POST['table']))
{
 $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
 $output = '';
 foreach($_POST["table"] as $table)
 {
  $show_table_query = "SHOW CREATE TABLE " . $table . "";
  $statement = $connect->prepare($show_table_query);
  $statement->execute();
  $show_table_result = $statement->fetchAll();

  foreach($show_table_result as $show_table_row)
  {
   $output .= "\n\n" . $show_table_row["Create Table"] . ";\n\n";
  }
  $select_query = "SELECT * FROM " . $table . "";
  $statement = $connect->prepare($select_query);
  $statement->execute();
  $total_row = $statement->rowCount();

  for($count=0; $count<$total_row; $count++)
  {
   $single_result = $statement->fetch(PDO::FETCH_ASSOC);
   $table_column_array = array_keys($single_result);
   $table_value_array = array_values($single_result);
   $output .= "\nINSERT INTO $table (";
   $output .= "" . implode(", ", $table_column_array) . ") VALUES (";
   $output .= "'" . implode("','", $table_value_array) . "');\n";
  }
 }
 
 
 $file_name = 'database_backup_on_' . date('y-m-d') . '.sql';
 // $file_handle = fopen($file_name, 'w+');
 // fwrite($file_handle, $output);
 // fclose($file_handle);
 // header('Content-Description: File Transfer');
 // header('Content-Type: application/octet-stream');
 // header('Content-Disposition: attachment; filename=' . basename($file_name));
 // header('Content-Transfer-Encoding: binary');
 // header('Expires: 0');
 // header('Cache-Control: must-revalidate');
    // header('Pragma: public');
    // header('Content-Length: ' . filesize($file_name));
    // ob_clean();
    // flush();
    // readfile($file_name);
    // unlink($file_name);
	
	ob_get_clean();
	header('Content-Type: application/octet-stream');
	header("Content-Transfer-Encoding: Binary");
	header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($output, '8bit'): strlen($output)) );
	header("Content-disposition: attachment; filename=\"".$file_name."\""); 
	echo $output; exit;
	

}
	
?>
<html>
 <head>
  <title>Export Database</title>
  
 <!--JS/Links here--><!--
<link rel="stylesheet" type="text/css" href="https://www.1rtc.biz/acrossyrs/js/fixedColumns.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://www.1rtc.biz/acrossyrs/js/cssfortables.css">
<script type="text/javascript" language="javascript" src="https://www.1rtc.biz/acrossyrs/js/jquery-3.3.1.js"></script>
<script type="text/javascript" language="javascript" src="https://www.1rtc.biz/acrossyrs/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://www.1rtc.biz/acrossyrs/js/tableStyle.js"></script>
<script type="text/javascript" language="javascript" src="https://www.1rtc.biz/acrossyrs/js/dataTables.fixedColumns.min.js"></script>-->

<?php $path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/js/includesscripts.php'; ?>
<!--End-->

 </head>
 <body>
  <br />
  <div class='container'>
   <div class='row'>
    <form method='post' id='export_form'>
     <h3>Database Name: <?php echo $dbname;?><br/><br/>Select Tables for Export</h3>
	<li style='list-style-type:none;'> 
		<input type="checkbox" name="title" id="title_1" /> <label for="title_1"><strong>Check All</strong></label>
	<ul>
    <?php
	$num=0;
    foreach($result as $table)
    {
    ?>
     
	  <li style='list-style-type:none;margin-left:1%;'><input type="checkbox" class="checkbox_table" name="table[]" id="box_<?php echo $num;?>" value="<?php echo $table["Tables_in_".$dbname]; ?>" /> <label for="box_<?php echo $num;?>"><?php echo $table["Tables_in_".$dbname]; ?></label></li>
    <?php
    $num++;
	}
    ?>
	</ul>
	</li>
     <div class="form-group">
      <input type="hidden" name="action_token" value="<?php echo $_SESSION['action_token']; ?>">   
      <input type="submit" name="submit" id="submit" class="btn btn-info" value="Export" />
     </div>
    </form>
   </div>
  </div>
 </body>
</html>
<script>
$(document).ready(function(){
$('input[name="all"],input[name="title"]').bind('click', function(){
var status = $(this).is(':checked');
$('input[type="checkbox"]', $(this).parent('li')).attr('checked', status);
});
});

$(document).ready(function(){
 $('#submit').click(function(){
  var count = 0;
  $('.checkbox_table').each(function(){
   if($(this).is(':checked'))
   {
    count = count + 1;
   }
  });
  if(count > 0)
  {
   $('#export_form').submit();
  }
  else
  {
   alert("Please select atleast one table for export.");
   return false;
  }
 });
});
</script>
