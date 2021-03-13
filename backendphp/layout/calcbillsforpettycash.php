<?php
$cashcalc='';
	    $bills=array('1000','500','200','100','50','20','10','5','1','025','010','005');
	    $billtable=''; $billsamt=0;
	    foreach ($bills as $bill){
                $billqty=((!isset($resultcash[$bill]) or $resultcash[$bill]=='')?0:$resultcash[$bill]);
                
                if(!isset($_REQUEST['print'])){
                    
            $billtable.='<tr><td><font face="arial" size="2">'.$bill.'</font></td><td><input type="text" name="'.$bill.'" size=3 value="'.$billqty.'"></td></tr>';}
                else {
                    $billtable.=($billqty==0 OR $billqty=='')?'':'<tr><td><font face="arial" size="2">'.$bill.'</font></td><td>'.$billqty.'</td></tr>';
                }
	    $billsamt=$billsamt+$bill*(($bill==='005' OR $bill==='010' OR $bill==='025')?($billqty*0.01):$billqty);
	    }
	    echo '<br><br>'.(!isset($cashcounttitle)?'Cash Count:':$cashcounttitle).'<table><tr><td>Denomination</td><td>No. of Bills'
            .(!isset($_REQUEST['print'])?'<form method="post" action="'.$action.'"><input type="submit" value="Enter">':'').'</td></tr>'.$billtable.'<tr style="font-size: 20;"><td>Total Amt of Cash</td><td>'.number_format($billsamt,2).'</td></tr></table>'.(!isset($_REQUEST['print'])?'</form>':'').'<br>';
	    $net=$billsamt-(($pcf)-$resultsum['TotalUsed']); $color=$net<0?'red':'black';
	    echo '<h4 style="color:'.$color.'">'.(!isset($differenceterm)?'Variance':$differenceterm).': '.number_format($net,2).'</h4>';