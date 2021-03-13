<br><div style="display: inline; width:80%;">
<?php 
if (!isset($_POST['groupby'])) { echo '<h4>FS : '.$which.'</h4><br><br>'; }
if (isset($showinmonths) and $showinmonths){ ?>
    <div style="float:left; margin-left: 20px; width:300px; padding: 2px; border: 2px solid black;">
    <h5 style="text-align:center; color: darkblue;">MONTH Columns</h5>
<form style="display:inline;" method="post" action="<?php echo $actionmonth;?>">
<br>&nbsp &nbsp &nbsp &nbsp<input type=radio name="groupby" value=0>Per Branch 
        <input type=text size=8 name='branch' list='branchnames' size=10 autocomplete='off' value='<?php echo $branch; ?>'>
<?php 
if (allowedToOpen(5321,'1rtc')) {
?>
<br>&nbsp &nbsp &nbsp &nbsp<input type=radio name="groupby" value=1  checked=true>Per Company 
        <input type=text name='company' list='companies' size=10 autocomplete='off' value='<?php echo $company; ?>'>
<?php if (allowedToOpen(5321,'1rtc')) {?><br>&nbsp &nbsp &nbsp &nbsp<input type=radio name="groupby" value=10> Show All<?php } } ?>
<br><br>&nbsp &nbsp &nbsp &nbsp Choose Month (0 - 12):  <input type="text" name="reportmonth" value="<?php echo $reportmonth; ?>" size=2></input>
<?php if ($which<>'CFMonth'){ ?>
<br><br>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp <input type=submit name="submit" value="Grouped Accounts" size=100px>
<?php } ?>
&nbsp &nbsp &nbsp &nbsp <input type=submit name="submit" value="Detailed" size=100px><br><br></form></div>
<?php 
}
if (isset($showinbranches) and $showinbranches and $which<>'CFMonth'){ 
  //  $align=(!isset($showinmonths))?'left':'right';
    if (allowedToOpen(5321,'1rtc')) {
    ?>
<div style="display: inline; float:left; margin-left: 20px; width:500px; padding: 2px; border: 2px solid black; ">
<h5 style="text-align:center; color: darkblue;">BRANCH Columns</h5>
<form style="display: inline;" method="post" action="<?php echo $actionbranch;?>"><br>
&nbsp &nbsp &nbsp Choose Company <input type=text name='company' list='companies' size=10 autocomplete='off' value='<?php echo $company; ?>'>
&nbsp &nbsp &nbsp Choose Month(0 - 12):  <input type="text" name="reportmonth" value="<?php echo $reportmonth; ?>" size=2></input>
<br><br><div style="display: inline; float:right; margin-right: 40px;">&nbsp &nbsp &nbsp Per Month <input type=radio name="groupby" value=3  checked=true> 
    &nbsp As of Month <input type=radio name="groupby" value=4></div>
<br><br><div style="display: inline; float:left; margin-left: 100px; "><input type=submit name="submit" value="Grouped accounts" size=100px></div>
<div style="display: inline; float:right; margin-right: 100px;"><input type=submit name="submit" value="Grouped accounts - ALL" size=100px></div><br><br>
<div style="display: inline; float:left; margin-left: 120px; "><input type=submit name="submit" value="Detailed" size=100px></div>
<div style="display: inline; float:right; margin-right: 120px;"><input type=submit name="submit" value="Detailed - ALL" size=100px></div><br><br></form></div>
   
 <div style="display: inline; float:left; margin-left: 20px; width:300px; padding: 2px; border: 2px solid black;"><h5 style="text-align:center; color: darkblue;">COMPANY Columns</h5>
<form style="display: inline;" method="post" action="<?php echo $actionbranch;?>">
<input type=hidden name='company' value='<?php echo $company; ?>'>
<br><br><div style="display: inline; float:left; margin-left: 50px; "> Choose Month(0 - 12):  <input type="text" name="reportmonth" value="<?php echo $reportmonth; ?>" size=2></input></div>
<br><br><div style="display: inline; float:left; margin-left: 50px; ">Per Month <input type=radio name="groupby" value=2  checked=true> &nbsp As of Month <input type=radio name="groupby" value=5></div>
<br><br><br><div style="display: inline; float:left; margin-left: 50px; "><input type=submit name="submit" value="Grouped Accounts" size=100px></div>
<div style="display: inline; float:right; margin-right: 50px;"><input type=submit name="submit" value="Detailed" size=100px></div><br><br></form></div>
  
       
<?php } ?>
    
<?php } ?>
</div><br><br class="clearFloat" />
