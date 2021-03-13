<?php
function checkExists($stringtomatch,$field,$table,$txnid,$link){
    $sql='SELECT `'.$txnid.'` FROM `'.$table.'` WHERE '.$field.' LIKE \'%'.$stringtomatch.'%\'';	
    $stmt=$link->query($sql);
    $result=$stmt->fetch();
    if ($stmt->rowCount()>0){ return $result[$txnid]; } else {   return 0;	}
}
?>