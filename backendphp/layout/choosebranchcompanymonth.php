<!-- action, branch, company, reportmonth, and sumbit value must be specified -->
<br><br><form style="display:inline; height: 60px; padding: 10px; border: 2px solid black;" method="post" action="<?php echo $action; ?>">
<input type=radio name="groupby" value=0>Per Branch <input type=text size=8 name='branch' list='branchnames' size=10 autocomplete='off' value='<?php echo $branch; ?>'>
&nbsp &nbsp &nbsp &nbsp<input type=radio name="groupby" value=1  checked=true>Per Company <input type=text name='company' list='companies' size=10 autocomplete='off' value='<?php echo $company; ?>'>&nbsp &nbsp &nbsp &nbsp
Choose Month (1 - 12) or 0 for beg balances:  <input type="text" name="reportmonth" value="<?php echo $reportmonth; ?>" size=2></input>
<?php if (allowedToOpen(303,'1rtc')) {?>&nbsp &nbsp &nbsp &nbsp<input type=radio name="groupby" value=2> Show All
<?php } ?>
&nbsp &nbsp &nbsp &nbsp <input type=submit name="submit" value="<?php echo $submitvalue; ?>" size=100px>
</form>
<?php include_once "../generalinfo/lists.inc";
renderlist('companies');renderlist('branchnamesall'); ?>
