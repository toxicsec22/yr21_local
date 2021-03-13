<script type="text/javascript" language="javascript">
 window.onload=blinkOn;
 
function blinkOn()
{
  document.getElementById("blink").style.color=<?php echo (!isset($colorOn)?'"#ff0000"':$colorOn); ?>
  
  setTimeout("blinkOff()",500) 
}

function blinkOff()
{
  document.getElementById("blink").style.color=<?php echo (!isset($colorOff)?'"#ffff00"':$colorOff); ?>
  
  setTimeout("blinkOn()",500)
}
  
 
</script>