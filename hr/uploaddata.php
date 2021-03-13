<html>
<head>
<title><?php echo (isset($title)?$title:''); ?></title>
</head>
<body>
    <h4><?php echo (isset($title)?$title:''); ?></h4><br><br><div style="margin: 0px 0px 0px 100px;">
        The first row must contain the column names, written exactly as follows:<br><br>
        <?php echo (isset($specific_instruct)?$specific_instruct:''); ?></div><br><br>
<form action="#" method="post" enctype="multipart/form-data">
<input type="file" name="userfile" accept="csv/text"><input type="submit" name="submit" value="Import"><br>
<?php
    if (!isset($_FILES['userfile'])) { goto nodata; }
/* The following variables must be set:
 * $link
 * $tblname
 * $firstcolumnname
 */
//$DOWNLOAD_DIR="/uploads/";       
        if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
       
                $csv_file=$_FILES['userfile']['name'];
                $file_to_use=$DOWNLOAD_DIR . $csv_file;
                if (file_exists($file_to_use)) {
                    unlink($file_to_use);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$file_to_use)) {
                 $good="Successfully_added_$csv_file";
                 }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
           } else {
             $good="Did not work  " . "Error: " . $_FILES["userfile"]["error"];            
            echo $csv_file . " is the file name";
            }


$csv = array_map("str_getcsv", file($file_to_use,FILE_SKIP_EMPTY_LINES));
$keys = array_shift($csv);

$numcols = count($keys)-1;
$num=0;
$fieldlist="";
while ($num<$numcols) {
    $fieldlist=$fieldlist . $keys[$num].", ";
    $num=$num+1;
}
$fieldlist=$fieldlist . $keys[$numcols];
//echo $fieldlist;

$query="";
$row = 1;
if (($handle = fopen($file_to_use,"r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num=0;
    $values="";
if($data[0]!=$firstcolumnname){

        while ($num<=$numcols) {
          $values=$values."'". addslashes($data[$num]) . (($num<$numcols)?"', ":"'");
          $num=$num+1;
        } //end while 

        if(isset($requireencodedby) and $requireencodedby==true) { $fieldlist.=",EncodedByNo"; $values.=",".$_SESSION['(ak0)']; }
        if(isset($requiredts) and $requiredts==true) { $fieldlist.=",TimeStamp"; $values.=",Now()"; }
        
$query="Insert into $tblname (" . $fieldlist . ") values (" . $values . ");";

if($_SESSION['(ak0)']==1002){ echo $query . "<br>" ; print_r($data). "<br>" ;}
  $row++;
$link->query($query);
} //end if        
  
    }
    fclose($handle);
}   
    

echo ($row-1) . " rows successfully imported to database!!";
?>
</form>
<?php
  nodata:
?>
</body>
</html>

