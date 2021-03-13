<!--<html>
<head>
    Calculate Bills
</head>    
<body>-->
<?php include_once('../backendphp/layout/regulartablestyle.php') ?>
<form name='calcbills' action='#' method='POST' enctype='multipart/form-data'>
<table>
<?php
    $denomvalues='<tr><td align="right">Denomination</td><td align="center">Qty</td><td align="right">Amount</td>';
    $sum=0;
    $bills=array();
    $denomination=array('1000','500','200','100','50','20','10','5','1','025','010','005');
    foreach ($denomination as $row){
        $name=$row.'qty';
        $qty=(isset($_POST[$name])?($_POST[$name]):0);
        $bills[]=array('denomination'=>$row,'qty'=>$qty);
        $denomvalues=$denomvalues.'<tr><td>'.$row.'  </td><td><input type="text"  name="'.$name.'" value="'.$qty.'" size="5" autocomplete="off" >';
        //if (isset($_POST['submit'])){
            // $value=(in_array($name,array('025','010','005'))?($row*$qty*0.01):$row*$qty);
			$value=(in_array($name,array('025qty','010qty','005qty'))?($row*$qty*0.01):$row*$qty);
            $denomvalues=$denomvalues. '<td align="right">'.number_format($value,2).'</td>';
        //}
        $denomvalues=$denomvalues.'</tr>';
        $sum=$sum+$value; //echo $qty. '  - '.$row;
        ?><!--<input type='hidden' name='<?php echo $row; ?>' value='<?php echo $qty; ?>'>-->
<?php
    }
   // echo $bills['denomination']*$bills['qty'];
    echo $denomvalues.'<tr><td>Total</td><td>'.number_format($sum,2).'</td></tr>';
    ?>
</table>

<input type='submit' name='submit' value='Calculate'>
</form>
<!--</body>
</html>
-->