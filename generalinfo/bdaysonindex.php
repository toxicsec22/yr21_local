<?php
// include_once ('../backendphp/dbinit/userinit.php');


    $sql='Select CONCAT(Nickname," ",SurName) AS Name, Branch, Bday AS `Bday/Anniv`  FROM `birthdays` WHERE MONTH(Birthdate)=MONTH(CURDATE()) ORDER BY Bday';
    $columnnames=array('Name','Branch','Bday/Anniv');
    
    $stmt=$link->prepare($sql); $stmt->execute(); $datatoshow=$stmt->fetchAll(PDO::FETCH_ASSOC);    
    
if ($stmt->rowCount()>0){
 ?>
<div style=" position: absolute; 
    left: <?php echo $bdaypositionleft; ?>%;
    top: <?php echo $bdaypositiontop; ?>%;">
    
<h4><i><font style="color:<?php echo $loginbordercolor; ?>">&nbsp; &nbsp;  &nbsp;  &nbsp; Celebrants for the month!</font></i></h4>
    <table style="background-color:<?php echo $loginbgcolor; ?>; color:<?php echo $loginfontcolor; ?>; border-color: #<?php echo $loginbordercolor; ?>; border: solid; border-collapse: collapse;">
       
<?php
//to make alternating rows have different colors
        $colorcount=0; $rcolor[0]=($loginbgcolor=='false'?'transparent':$loginbgcolor);  $rcolor[1]=$color2;
    foreach($datatoshow as $rows){
        echo '<tr bgcolor='. $rcolor[$colorcount%2].'><td>'.$rows['Name'].'</td><td>'.$rows['Branch'].'</td><td>'.$rows['Bday/Anniv'].'</td><td></tr>';
        $colorcount++;
    }
    ?></table></div>
 <?php  }
 $link=null; $stmt=null;
 ?>
    
