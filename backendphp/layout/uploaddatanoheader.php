<?php
    if (!isset($_FILES['userfile'])) { goto nodata; }

$maxsize = !isset($maxsize)?204800:$maxsize; // default MAX Size 200KB
       
        if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
       
                $csv_file=$_FILES['userfile']['name'];
				
				$ext = pathinfo($csv_file, PATHINFO_EXTENSION);
				if( $ext !== 'csv' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] > $maxsize)){ echo 'Error! Invalid File Size (MAX '.($maxsize/1024).'KB).'; exit(); }
				
                $file_to_use=$DOWNLOAD_DIR . $csv_file;
                if($_SESSION['(ak0)']==1002){ echo pathinfo($csv_file, PATHINFO_DIRNAME).'<br>'.$file_to_use;}
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


$query="";
$row = 1;
if (($handle = fopen($file_to_use,"r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num=0;
    $values=""; //echo $data[0];
if($data[0]!=$firstcolumnname){

        while ($num<=$numcols) {
          $values=$values."'". addslashes($data[$num]) . (($num<$numcols)?"', ":"'");
          $num=$num+1;
        } //end while 

        if(isset($requireencodedby) and $requireencodedby==true) { $fieldlist2=$fieldlist.",EncodedByNo"; $values.=",".$_SESSION['(ak0)']; } else { $fieldlist2=$fieldlist;}
        if(isset($requiredts) and $requiredts==true) { $fieldlist2.=",TimeStamp"; $values.=",Now()"; }
        if(isset($requireencodedby) OR isset($requiredts)) { $fields=$fieldlist2; } else { $fields=$fieldlist; }
$query="Insert into $tblname (" . $fields . ") values (" . $values . ");";
// echo $query;
if($_SESSION['(ak0)']==1002 OR $_SESSION['(ak0)']==1003){ echo $query . "<br>" ; print_r($data). "<br>" ;}
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