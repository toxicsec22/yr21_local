<?php
// define the ff variables: $col, $link, $listsql, $listid, $listdesc
echo "<datalist id=$col>";
$stmt=$link->prepare($listsql);
    $stmt->execute();
    $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($datatoshow as $row){
        
                echo "<option value=$row[$listid]>$row[$listdesc]</option>";
                
                } // end foreach
                
echo "</datalist>";
?>