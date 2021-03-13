<html>
<head>
<title><?php echo $title; ?></title>
<?php
//
if (isset($hidecontents) AND $hidecontents==1){ goto skipcontents;} else {include_once('../switchboard/contents.php');}
skipcontents:
include_once('regulartablestyle.php');
?>
<style type="text/css">
#wrap {
   width:100%;
   margin:0 auto;
}
#left {
   float:left;
   width:50%; overflow: auto;
}
#right {
   float:right;
   width:50%;overflow: auto;
}
thead {color:darkblue;font-family:sans-serif;; font-size: small;}
tbody {color:black; font-family:sans-serif;; font-size: small;}
tfoot {color:darkblue;}
table,th,td
{
border:1px solid black;
border-collapse:collapse;
padding: 3px;
}
</style>
<br><h3><?php echo $title; ?></h3>
	<i><?php echo (isset($formdesc)?$formdesc:'');?></i><br><br>
</head>
<body><div id="wrap">
	<div id="left">
	<?php (isset($sortfield)?include('../backendphp/layout/sortbyform.php'):'');  ?>
    <form method="GET" action="<?php echo isset($leftprocess)?$leftprocess:'' ; ?>" enctype="multipart/form-data">
    <?php echo isset($lefttabletitle)?$lefttabletitle.'<br>':'';
    //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="lightgrey";
        $rcolor[1]="FFFFFF";
		$fromBRtoN = array("<br>", "<br/>", "<br />", "<BR>", "<BR/>", "<BR />");
    ?>
    <table><thead><tr>
<?php
$coltitlesleft='';
foreach($columnnamesleft as $col){
	$coltitlesleft=$coltitlesleft.'<td>'.$col.'</td>';
}
echo $coltitlesleft;
?>
</tr></thead><tbody>
<?php
foreach ($link->query($sqlleft) as $row){
	echo '<tr bgcolor='. $rcolor[$colorcount%2].'>';
	$colsleft='';
		foreach($columnnamesleft as $col){
		$colsleft=$colsleft.'<td'.($row[$col]<0?' bgcolor=ffb3b3 ':'').'>'.nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",$row[$col]))).'</td>';
		}
	echo $colsleft.(isset($lefteditprocess)?'<td><a href="'.$lefteditprocess.$row[$txnid].'">'.$lefteditprocesslabel.'</a></td>':'').'</tr>';
	$colorcount++;
}      
?>
    </tbody><tfoot>
 <?php
if (isset($totalleft)){
	echo '<tr>'.$totalleft.'</tr>';
}
?>   
    </tfoot></table>
    </form>
	
</div>

	<div id="right">
  <form method="POST" action="<?php echo isset($rightprocess)?$rightprocess:'' ; ?>" enctype="multipart/form-data">
  <?php echo isset($righttabletitle)?$righttabletitle.'<br>':'' ?>
 <table><thead><tr>
 
<?php
$coltitlesright='';
foreach($columnnamesright as $col){
	$coltitlesright=$coltitlesright.'<td>'.$col.'</td>';
}
echo $coltitlesright.(isset($runtotalrightcol)?'<td>Running Sum</td>':'');
?>
</tr></thead><tbody>
<?php
$runtotalright=0;
foreach ($link->query($sqlright) as $row){
        $runtotalright=(isset($coltototal)?$runtotalright+$row[$coltototal]:0);
	echo '<tr bgcolor='. $rcolor[$colorcount%2].'>';
	$colsright='';
		foreach($columnnamesright as $col){
		$colsright=$colsright.'<td'.($row[$col]<0?' bgcolor=ffb3b3 ':'').'>'.nl2br(htmlspecialchars(str_replace($fromBRtoN,"\n",$row[$col]))).'</td>';
		}
	echo $colsright.(isset($righteditprocess)?'<td><a href="'.$righteditprocess.$row[$txnid].'">'.$righteditprocesslabel.'</a></td>':'')
                .(isset($runtotalrightcol)?"<td>".number_format($runtotalright,2)."</td></tr>":"</tr>").'</tr>';
	$colorcount++;
}      
?>
 </tbody><tfoot>
<?php
if (isset($totalright)){
	echo '<tr>'.$totalright.'</tr>';
}
?> 
 </tfoot></table>
    </form>
	
	</div>
</div></body>
</html>