<?php
$months=array();
$whichdata=(!isset($whichdata)?'all':$whichdata);
$begmonth=(!isset($begmonth)?1:$begmonth);
switch ($whichdata){
    case 'withcurrent':
        if ($closedmonth==12){ $month=12;} else {$month=isset($reportmonth)?$reportmonth:date('m');}
    break;
    case 'static':
    case 'staticfs':
        $month=$closedmonth;
    break;
    default:
        $month=12;
}
for ($i = $begmonth; $i <= $month; ++$i) { $months[]=$i; }

include_once $path.'/acrossyrs/commonfunctions/monthName.php';  

?>