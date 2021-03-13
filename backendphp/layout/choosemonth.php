<select name="month">
<?php
$year=$currentyr;
  for ($i = 1; $i <= 12; ++$i) {
    //$time = strtotime(sprintf('-%d months', $i)); // --> originally allowed past 12 months, regardless of year
    $time = strtotime($year.'-'.$i);
    $time=strtotime(date('Y',$time).'-'.date('m',$time).'-'.date('t',$time));
    $value = date('Y-m-d', $time);
    $label = date('F Y', $time);
    printf('<option value="%s" '.($i==date('n')?'selected="selected"':'').'>%s</option>', $value, $label);
  }
  ?>
</select>