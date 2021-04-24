<br><br><hr><br>
      Payroll Formulas for MONTHLY<br><br>
      <?php echo str_repeat('&nbsp',10)?><i>Notes:  1. Calculations start as if perfect attendance, before absences are deducted.<br>
      <?php echo str_repeat('&nbsp',20)?>2. The first payroll of a NEW employee will be counted as daily paid.  Remote Working Saturdays and Special Holidays are added to the counted regular days so these will be paid as well.<br>
      <?php echo str_repeat('&nbsp',20)?>3. The following calculations assume that Saturday <u>whole days</u> are paid for monthly employees.</i><br><br>
      <?php echo str_repeat('&nbsp',15)?>Days Per Year = Total Days Per Year less Sundays:  365-(365/7) = 313<br>
      <?php echo str_repeat('&nbsp',15)?>Average Days Per Month = Days Per Year divided by 12 = 26.08<br>
      <?php echo str_repeat('&nbsp',15)?>Daily Rate = (Monthly Basic + De Minimis + Tax Shield) / 26.08<br>
      <?php echo str_repeat('&nbsp',15)?>Hourly Rate = (Monthly Basic + De Minimis + Tax Shield) / 26.08 / 8<br><br>      
<?php echo str_repeat('&nbsp',15)?>Basic = (Monthly/2) Basic Rate<br>
<!--<?php //echo str_repeat('&nbsp',15)?>Cola = (Monthly/2) Cola Rate<br>-->
<?php echo str_repeat('&nbsp',15)?>De Minimis = (Monthly/2) De Minimis Rate<br>
<?php echo str_repeat('&nbsp',15)?>Tax Shield = (Monthly/2) Tax Shield Rate<br><br>
<?php echo str_repeat('&nbsp',15)?>Absence (Basic/Tax Shield) = Daily Rate x LWOP<br><br>