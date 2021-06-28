<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(804,8051,8041,806,807,6473),'1rtc')) { echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');
 
 if(!isset($_GET['w'])){
	echo '<title>Government Payroll Tables</title>';
 }
    $showbranches=false;
    include_once('../backendphp/layout/linkstyle.php');
    echo '</br>';
    ?>
<!--buttons -->
    <div>
   <font size=4 face='sans-serif'>     
		<?php if (allowedToOpen(8032,'1rtc')) {?>
		 <a id="link" href='/acrossyrs/infoandfaq/govtminwages.php'> Minimum Wage Rates</a><?php echo str_repeat('&nbsp',5)?>
         <a id="link" href='lookupbasetables.php?w=StructureMonthly'>Salary Structure - Monthly Paid </a><?php echo str_repeat('&nbsp',5)?>
         <a id="link" href='lookupbasetables.php?w=StructureDailyPaid'>Salary Structure - Daily Paid </a><?php echo str_repeat('&nbsp',5)?>
		<?php } ?> 
        <?php if (allowedToOpen(805,'1rtc')) {?>
        <a id="link" href='lookupbasetables.php?w=PHIC'>Philhealth Table</a><?php echo str_repeat('&nbsp',5)?>
        <a id="link" href='lookupbasetables.php?w=SSS'>SSS Table</a><?php echo str_repeat('&nbsp',5)?>
        <a id='link' href='lookupbasetables.php?w=TaxRates'>Tax Rates Table</a><?php echo str_repeat('&nbsp',5)?>
        <a id='link' href='jobratingplan.php'>Job Rating Plan</a><?php echo str_repeat('&nbsp',5)?>
		<?php } ?>      
    </font></div><br>
    <?php
    $whichqry=!isset($_GET['w'])?'':$_GET['w'];
     switch ($whichqry){
        case 'SSS':
            if (!allowedToOpen(806,'1rtc')) { echo 'No permission'; exit;}
            $title='SSS Table';
            $sql='SELECT *,FORMAT(RangeMin,2) AS RangeMin,FORMAT(RangeMax,2) AS RangeMax,FORMAT(SSECCredit,0) AS SSECCredit,FORMAT(MPFCredit,0) AS MPFCredit,FORMAT(SSER,2) AS SSER, FORMAT(ECER,2) AS ECER, FORMAT(MPFER,2) AS MPFER, FORMAT(SSEE,2) AS SSEE, FORMAT(ECEE,2) AS ECEE, FORMAT(MPFEE,2) AS MPFEE,FORMAT(SSER+ECER+MPFER,2) AS `Total-ER`, FORMAT(SSEE+ECEE+MPFEE,2) AS `Total-EE`, FORMAT(SSER+ECER+MPFER+SSEE+ECEE+MPFEE,2) AS `TOTAL` FROM `payroll_0ssstable` ';
            $orderby='Bracket';    
            $columnnames=array('Bracket','RangeMin','RangeMax','SSECCredit','MPFCredit','SSER','ECER','MPFER','SSEE','ECEE','MPFEE','Total-ER','Total-EE','TOTAL');
            $width='55%';
            include('../backendphp/layout/displayastablenosort.php');
            break;
        case 'PHIC':
            if (!allowedToOpen(805,'1rtc')) { echo 'No permission'; exit;}
            include_once('../backendphp/layout/regulartablestyle.php');
            $sql='SELECT * FROM payroll_0phicrate WHERE ApplicableYear='.$currentyr;
            $stmt=$link->query($sql); $result=$stmt->fetch(); 
            ?>
            <title> Philhealth Table for <?php echo $currentyr;?></title>
            <h3>Philhealth Table</h3><br><br>
            <table>
            <?php
            echo '<tr><td>Monthly Basic Salary x '.$result['PremiumRate'].'%</td><td>Monthly Premium</td><td>Personal Share</td><td>Employer Share</td></tr>
                <tr><td>P '.number_format($result['MinBasic'],2).' and below</td><td>P '.$result['MinPremium'].'</td><td>P '.number_format($result['MinPremium']/2,2).'</td><td>P '.number_format($result['MinPremium']/2,2).'</td></tr>
                <tr><td>P '.number_format($result['MinBasic']+.01,2).' <br> to <br> P '.number_format($result['MinBasic']-.01,2).'</td><td>P '.number_format($result['MinPremium'],2).' <br> to <br> P '.number_format($result['MaxPremium'],0).'</td><td>P '.number_format($result['MinPremium']/2,2).' <br> to <br> P '.number_format($result['MaxPremium']/2,0).'</td><td>P '.number_format($result['MinPremium']/2,2).' <br> to <br> P '.number_format($result['MaxPremium']/2,0).'</td></tr>
                <tr><td>P '.number_format($result['MaxBasic'],2).' and above</td><td>P '.number_format($result['MaxPremium'],0).'</td><td>P '.number_format($result['MaxPremium']/2,0).'</td><td>P '.number_format($result['MaxPremium']/2,0).'</td></tr>'
                    ?>
            </table>
            <br><hr><br>
            <?php
            $subtitle='Premium Rates 2019 to 2025';
            $sql='SELECT * FROM payroll_0phicrate';
            $width='30%';
            $columnnames=array('ApplicableYear', 'MinBasic', 'MinPremium', 'PremiumRate', 'MaxBasic', 'MaxPremium');
            include('../backendphp/layout/displayastableonlynoheaders.php');
            break;
        case 'TaxExempt': // No longer relevant with TRAIN law
            if (!allowedToOpen(807,'1rtc')) { echo 'No permission'; exit;}
            $title='Tax Exemptions Table';
            $sql='SELECT * FROM `payroll_0taxexemptions`';
            $orderby='Classification';    
            $columnnames=array('Classification','Exemption');
            include('../backendphp/layout/displayastable.php');
            break;
        case 'TaxRates': 
            if (!allowedToOpen(808,'1rtc')) { echo 'No permission'; exit;}
            $title='Tax Rates Table';
            $sql='SELECT * FROM `payroll_0taxrates`';
            $orderby='MinTaxable';    
            $columnnames=array('MinTaxable','MaxTaxable','Rate','MinTax');
            $width='55%';
            include('../backendphp/layout/displayastable.php');
            $text='<br><br><br><br>Taxable Income = (Annual Gross Basic Income - Annual SSS-EE - Annual Philhealth-EE - Annual Pagibig-EE)';// - Tax Exemption
            echo $text.'<br><br>Tax Due = Minimum Tax + (Taxable Income - Minimum Taxable) * Tax Rate';
            break;
        case 'StructureREPLACED':
            if (!allowedToOpen(6473,'1rtc')) { echo 'No permission'; exit;}
            /*
	    $title='Salary Structure'; 

            $formdesc='</i><div style="background-color: #e6e6e6;
                width: 1100px;
                border: 2px solid grey;
                padding: 25px;
                margin: 25px;">
                <ol>
                    <li>This table represents salaries upon hiring, during probation period, and basis for regularization and computation of annual increases.</li>
                    <li>All branch personnel shall start with the entry/minimum salary rate, depending on the area/region in reference with the wage order, computed as daily rate, using 313 pay factor (6 days work per week).</li>
                    <li>Promotion is recommended for those employees who have reached the maximum rate through (succession plan) with Immediate Supervisor.(Management\'s approval)</li>
                    <li>Employee who is not recommended for promotion, and has reached the maximum salary, will no longer be entitled to an annual increase.</li>
            </ol></div><i>';
                        
            $sql='SELECT jl.*, JobLevel, FORMAT(MinRate,0) AS `MINIMUM`, FORMAT(MinRate*(1+PercentMintoMed/100),-1) AS MEDIAN, FORMAT(MinRate*(1+PercentMintoMed/100)*(1+PercentMedtoMax/100),-1) AS MAXIMUM  FROM attend_0joblevels jl JOIN attend_0jobclass jc ON jc.JobLevelID=jl.JobLevelID GROUP BY jl.JobLevelID ORDER BY JobLevelID,JobLevelID;';
            
            
            $columnnames=array('JobLevel','JobLevelID','MINIMUM','MEDIAN','MAXIMUM');
            echo '<div style="margin-left: 15%;">';
            include('../backendphp/layout/displayastablenosort.php'); 
            echo '</div><br><br><hr><br>';
            $subtitle='List of Positions Per Job Level';
            $formdesc='';
            $sql='SELECT jc.*, 
(SELECT GROUP_CONCAT(DISTINCT Position ORDER BY Position SEPARATOR "<BR>") FROM attend_0joblevels jl LEFT JOIN attend_1positions p ON jl.JobLevelID=p.JobLevelID WHERE RIGHT(jl.JobLevelID,1)=1 AND jl.JobLevelID=jc.JobLevelID GROUP BY jl.JobLevelID)  AS `Level 1`,
(SELECT GROUP_CONCAT(DISTINCT Position ORDER BY Position SEPARATOR "<BR>") FROM attend_0joblevels jl LEFT JOIN attend_1positions p ON jl.JobLevelID=p.JobLevelID WHERE RIGHT(jl.JobLevelID,1)=2 AND jl.JobLevelID=jc.JobLevelID GROUP BY jl.JobLevelID) AS `Level 2`,
(SELECT GROUP_CONCAT(DISTINCT Position ORDER BY Position SEPARATOR "<BR>") FROM attend_0joblevels jl LEFT JOIN attend_1positions p ON jl.JobLevelID=p.JobLevelID WHERE RIGHT(jl.JobLevelID,1)=3 AND jl.JobLevelID=jc.JobLevelID GROUP BY jl.JobLevelID) AS `Level 3`
 FROM attend_0jobclass jc ORDER BY JobLevelID;';
            
            $hidecount=TRUE;
            $columnnames=array('JobLevel','Level 1','Level 2','Level 3');
            echo '<div style="margin-left: 15%;">';
            include('../backendphp/layout/displayastableonlynoheaders.php'); 
            echo '</div>';
            ?>
<hr><div style="width:100%; margin:0 auto;"><div style="float:left; width:25%;">
            <?php
	    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	    
	    if (isset($_GET['Submit'])){
		      include_once('searchcontri.php');
		      //$taxexempt1=comboBoxValue($link,'payroll_0taxexemptions','Classification',$_GET['TaxClass'],'Exemption');
		      $basic1=$_GET['Basic1']; $allow1=$_GET['Allow1']; $dem1=$_GET['DeM1']; $sss1=getContriEE(($basic1+$dem1),'sss'); $phic1=getContriEE($basic1,'phic'); 
		      echo '<br><br><br><b>Option 1</b><br> Basic '.number_format($basic1,2).'<br>';
		      echo 'De Minimis '.number_format($dem1,2).'<br>';
		      echo 'Tax Shield '.number_format($allow1,2).'<br>';
		      echo 'Gross '.number_format($basic1+$dem1+$allow1,2).'<br><br>';
		      echo 'SSS '.number_format($sss1,2).'  (Salary Credit:'. getSalaryCredit(($basic1+$dem1),'sss').')<br>';
		      echo 'Philhealth '.number_format($phic1,2).' <br>';
		      echo 'Pagibig '.number_format(100,2).'<br><br>';
		      $taxable1=(($basic1-$sss1-$phic1-100)*12);//-$taxexempt1);
                      if($taxable1<=250000) {$wtax1=0;} else {
		      $sql='SELECT TaxDue('.$taxable1.')/12 AS TaxDue'; $stmt=$link->query($sql); $result=$stmt->fetch();
                      $wtax1=$result['TaxDue'];}
		      echo 'Tax (if annual basic >250k):  '.number_format($wtax1,2).'<br><i>This assumes one FULL YEAR of service.</i><br><br>';
		      $netpermonth1=$basic1+$dem1+$allow1-$sss1-$phic1-100-($wtax1);
		      echo 'Net Per Month: '.number_format($netpermonth1,2);
		      
		    //  $taxexempt2=comboBoxValue($link,'payroll_0taxexemptions','Classification',$_GET['TaxClass'],'Exemption');
		      $basic2=$_GET['Basic2']; $dem2=$_GET['DeM2']; $sss2=getContriEE(($basic2+$dem2),'sss'); $phic2=getContriEE($basic2,'phic'); $allow2=$_GET['Allow2']; 
		      echo '<br><br><br><b>Option 2</b><br> Basic '.number_format($basic2,2).'<br>';
		      echo 'De Minimis '.number_format($dem2,2).'<br>';
		      echo 'Tax Shield '.number_format($allow2,2).'<br>';
		      echo 'Gross '.number_format($basic2+$dem2+$allow2,2).'<br><br>';
		      echo 'SSS '.number_format($sss2,2).'  (Salary Credit:'. getSalaryCredit(($basic2+$dem2),'sss').')<br>';
		      echo 'Philhealth '.number_format($phic2,2).'<br>';
		      echo 'Pagibig '.number_format(100,2).'<br><br>';
		      $taxable2=(($basic2-$sss2-$phic2-100)*12);//-$taxexempt2);
                      if($taxable2<=250000) {$wtax2=0;} else {
		      $sql='SELECT TaxDue('.$taxable2.')/12 AS TaxDue'; $stmt=$link->query($sql); $result=$stmt->fetch();
                      $wtax2=$result['TaxDue'];}
		      echo 'Tax (if annual basic >250k):  '.number_format($wtax2,2).'<br><i>This assumes one FULL YEAR of service.</i><br><br>';
		      $netpermonth2=$basic2+$dem2+$allow2-$sss2-$phic2-100-($wtax2);
		      echo 'Net Per Month: '.number_format($netpermonth2,2);
		      
	    } else {
		//echo comboBox($link,'SELECT * FROM `payroll_0taxexemptions` ORDER BY Classification;','Classification','Classification','taxclass'); 
	    ?><br><br>Calculate salary:  <i>Please use whole month values.</i><br>
	    <form method='get' action='lookupbasetables.php?'><br>
<!--		      <b>Tax classification</b> <input type='text' required=true name='TaxClass' size=5 list='taxclass'><br>-->
		      <b><i>Option 1</i></b><br>
		      Basic <input type='text' value=0 required=true name='Basic1' size=5><br>
		      Tax-shield <input type='text' value=0 name='Allow1' size=5><br>
		      De Minimis <input type='text' value=0 name='DeM1' size=5> (82k/yr - 13th divided by 12)<br><br>
		      <b><i>Option 2</i></b><br>
		      Basic <input type='text' value=0 name='Basic2' size=5><br>
		      Tax-shield <input type='text' value=0 name='Allow2' size=5><br>
		      De Minimis <input type='text' value=0 name='DeM2' size=5> (Max 82k/yr - 13th divided by 12)<br><br>
		      <input type='hidden' name='w' value='Structure'>
		      <input type='submit' name='Submit' value='Calculate'>
	    </form>
	    <?php      
	    }
            ?></div><div style="margin-left: 500px; width:30%;"><br>
            <b>Guide for branch personnel:</b>
            <br><br>
            <u>Branch Assistants</u><ul>
                <li>Metro Manila:  NCR minimum wage</li>
                <li>Provinces:  local minimum wage + 10%</li></ul>
            
            <br><br>
            <u>Branch Head</u> (if branch monthly sales average is...)<ul>
                <i>Metro Manila </i>
            <li>Less than 1M    : 15k - 16k</li>
            <li>1M to 2M        : 16k - 17k</li>
            <li>More than 2M    : 17k-18k</li>
            </ul>
            <i>For Provinces</i><ul>
            <li>Less than 1M    : Min Wage + 30%</li>
            <li>1M to 2M        : Min Wage + 40%</li>
            <li>More than 2M    : Min Wage + 50%</li>
            </ul>
            </div></div><br><br><hr><br><br>
            <div style="margin-left: 20px;">
                <br><br>
<?php
$subtitle='Multipliers From Minimum to Maximum per Job Level';
$sql='SELECT *, FORMAT(MinRate,0) AS MinimumRateinPeso FROM attend_0joblevels ORDER BY JobLevelID,JobLevelID;';
// echo $sql; exit();
$txnidname='JobLevelID';
$columnnames=array('JobLevelID','MinimumRateinPeso','PercentMintoMed','PercentMedtoMax'); 
$width='20%';
$title=''; $formdesc='';

include '../backendphp/layout/displayastablenosort.php';
?>
</div>


            <?php */
            break;
            case 'StructureMonthly':
                if (!allowedToOpen(6473,'1rtc')) { echo 'No permission'; exit;}
            $title='Salary Structure - Monthly Paid'; 
                $sqls='SELECT MAX(DateEffective),TotalMinWage,TimeStamp from `1_gamit`.`payroll_4wageorders` where MinWageAreaID=\'1\' ';
                $stmts=$link->query($sqls); $results=$stmts->fetch();
                
                $sqla='SELECT DataClosedBy FROM 00dataclosedby WHERE ForDB=\'3\'';
                $stmta=$link->query($sqla); $resulta=$stmta->fetch();
                $minwage=$results['TotalMinWage']; $daysofmonth=26.08; 
                $formdesc='</i><div style="background-color: #e6e6e6;
                width: 1100px;
                border: 2px solid grey;
                padding: 25px;
                margin: 25px;">This serves as our guide. Note that all figures are based on current NCR daily rate of '.$minwage.' and '.$daysofmonth.' days per month. TimeStamp: '.$results['TimeStamp'].'
                '.(($_SESSION['(ak0)']=1002)?'<br><br>DataClosedBy:'.$resulta['DataClosedBy'].'
                <form style="display:inline;" method="post" action="lookupbasetables.php?w=DataClosedBy">
                    <input type="submit" name="Update" value="Update DataClosedBy?" OnClick="return confirm(\'Are you sure you want to update?\');">
                </form>':'').'</div><i>';

                $sql="SELECT jl.JobLevelID, JobLevel, (SELECT GROUP_CONCAT(DISTINCT `Position` ORDER BY `Position` SEPARATOR '<br>') FROM attend_1positions WHERE JobLevelID=jl.JobLevelID AND PreferredRateType=1 ORDER BY Position) AS Positions, 
                FORMAT(ROUND($minwage*$daysofmonth*PercentIncMinimum,0),0) AS `Hiring Rate`,  
 FORMAT(ROUND($minwage*$daysofmonth*PercentIncMinimum*REPLACE(RegStep,'0','1'),0),0) AS `Regularization Rate`,
 FORMAT(ROUND($minwage*$daysofmonth*PercentIncMinimum*REPLACE(RegStep,'0','1')*REPLACE(`Step1`,'0','1'),0),0) AS `Step 1/Midpoint`,
 FORMAT(ROUND($minwage*$daysofmonth*PercentIncMinimum*REPLACE(RegStep,'0','1')*REPLACE(`Step1`,'0','1')*REPLACE(`Step2`,'0','1'),0),0) AS `Step 2`,
 FORMAT(ROUND($minwage*$daysofmonth*PercentIncMinimum*REPLACE(RegStep,'0','1')*REPLACE(`Step1`,'0','1')*REPLACE(`Step2`,'0','1')*REPLACE(`Step3`,'0','1'),0),0) AS `Step 3`,
 FORMAT(ROUND($minwage*$daysofmonth*PercentIncMinimum*REPLACE(RegStep,'0','1')*REPLACE(`Step1`,'0','1')*REPLACE(`Step2`,'0','1')*REPLACE(`Step3`,'0','1')*REPLACE(`Step4`,'0','1'),0),0) AS `Step 4`,
 FORMAT(ROUND($minwage*$daysofmonth*PercentIncMinimum*REPLACE(RegStep,'0','1')*REPLACE(`Step1`,'0','1')*REPLACE(`Step2`,'0','1')*REPLACE(`Step3`,'0','1')*REPLACE(`Step4`,'0','1')*REPLACE(`Maximum`,'0','1'),0),0) AS `Maximum Rate`
 from `attend_0joblevels` jl left join payroll_0salarystructure ss on ss.JobLevelID=jl.JobLevelID";

                //include('salarystructureformula.php');
                $orderby='JobLevel';    
                $columnnames=array('JobLevelID','JobLevel','Positions','Hiring Rate','Regularization Rate','Step 1/Midpoint','Step 2','Step 3','Step 4','Maximum Rate');
                $hidecount=TRUE;
                include('../backendphp/layout/displayastable.php'); 
                ?>
    <hr><div style="width:100%; margin:0 auto;"><div style="float:left; width:25%;">
                <?php
            include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
            
            if (isset($_GET['Submit'])){
                  include_once('searchcontri.php');
                  //$taxexempt1=comboBoxValue($link,'payroll_0taxexemptions','Classification',$_GET['TaxClass'],'Exemption');
                  $basic1=$_GET['Basic1']; $allow1=$_GET['Allow1']; $dem1=$_GET['DeM1']; $sss1=getContriEE(($basic1+$dem1),'sss'); $phic1=getContriEE($basic1,'phic'); 
                  echo '<br><br><br><b>Option 1</b><br> Basic '.number_format($basic1,2).'<br>';
                  echo 'De Minimis '.number_format($dem1,2).'<br>';
                  echo 'Tax Shield '.number_format($allow1,2).'<br>';
                  echo 'Gross '.number_format($basic1+$dem1+$allow1,2).'<br><br>';
                  echo 'SSS '.number_format($sss1,2).'  (Salary Credit:'. getSalaryCredit(($basic1+$dem1),'sss').')<br>';
                  echo 'Philhealth '.number_format($phic1,2).' <br>';
                  echo 'Pagibig '.number_format(100,2).'<br><br>';
                  $taxable1=(($basic1-$sss1-$phic1-100)*12);//-$taxexempt1);
                          if($taxable1<=250000) {$wtax1=0;} else {
                  $sql='SELECT TaxDue('.$taxable1.')/12 AS TaxDue'; $stmt=$link->query($sql); $result=$stmt->fetch();
                          $wtax1=$result['TaxDue'];}
                  echo 'Tax (if annual basic >250k):  '.number_format($wtax1,2).'<br><i>This assumes one FULL YEAR of service.</i><br><br>';
                  $netpermonth1=$basic1+$dem1+$allow1-$sss1-$phic1-100-($wtax1);
                  echo 'Net Per Month: '.number_format($netpermonth1,2);
                  
                //  $taxexempt2=comboBoxValue($link,'payroll_0taxexemptions','Classification',$_GET['TaxClass'],'Exemption');
                  $basic2=$_GET['Basic2']; $dem2=$_GET['DeM2']; $sss2=getContriEE(($basic2+$dem2),'sss'); $phic2=getContriEE($basic2,'phic'); $allow2=$_GET['Allow2']; 
                  echo '<br><br><br><b>Option 2</b><br> Basic '.number_format($basic2,2).'<br>';
                  echo 'De Minimis '.number_format($dem2,2).'<br>';
                  echo 'Tax Shield '.number_format($allow2,2).'<br>';
                  echo 'Gross '.number_format($basic2+$dem2+$allow2,2).'<br><br>';
                  echo 'SSS '.number_format($sss2,2).'  (Salary Credit:'. getSalaryCredit(($basic2+$dem2),'sss').')<br>';
                  echo 'Philhealth '.number_format($phic2,2).'<br>';
                  echo 'Pagibig '.number_format(100,2).'<br><br>';
                  $taxable2=(($basic2-$sss2-$phic2-100)*12);//-$taxexempt2);
                          if($taxable2<=250000) {$wtax2=0;} else {
                  $sql='SELECT TaxDue('.$taxable2.')/12 AS TaxDue'; $stmt=$link->query($sql); $result=$stmt->fetch();
                          $wtax2=$result['TaxDue'];}
                  echo 'Tax (if annual basic >250k):  '.number_format($wtax2,2).'<br><i>This assumes one FULL YEAR of service.</i><br><br>';
                  $netpermonth2=$basic2+$dem2+$allow2-$sss2-$phic2-100-($wtax2);
                  echo 'Net Per Month: '.number_format($netpermonth2,2);
                  
            } else {
            //echo comboBox($link,'SELECT * FROM `payroll_0taxexemptions` ORDER BY Classification;','Classification','Classification','taxclass'); 
            ?><br><br>Calculate salary:  <i>Please use <b>whole month values</b>.</i><br>
            <form method='get' action='lookupbasetables.php?'><br>
    <!--		      <b>Tax classification</b> <input type='text' required=true name='TaxClass' size=5 list='taxclass'><br>-->
                  <b><i>Option 1</i></b><br>
                  Basic <input type='text' value=0 required=true name='Basic1' size=5><br>
                  Tax-shield <input type='text' value=0 name='Allow1' size=5><br>
                  De Minimis <input type='text' value=0 name='DeM1' size=5> (82k/yr - 13th divided by 12)<br><br>
                  <b><i>Option 2</i></b><br>
                  Basic <input type='text' value=0 name='Basic2' size=5><br>
                  Tax-shield <input type='text' value=0 name='Allow2' size=5><br>
                  De Minimis <input type='text' value=0 name='DeM2' size=5> (Max 82k/yr - 13th divided by 12)<br><br>
                  <input type='hidden' name='w' value='Structure'>
                  <input type='submit' name='Submit' value='Calculate'>
            </form>
            <?php      
            }
                ?></div><div style="margin-left: 500px; width:30%;"><br>
                <b>Guide for branch personnel:</b>
                <br><br>
                <u>Branch Assistants</u><ul>
                    <li>Metro Manila:  NCR minimum wage</li>
                    <li>Provinces:  local minimum wage + 10%</li></ul>
                
                <br><br>
                <u>Branch Head</u> (if branch monthly sales average is...)<ul>
                    <i>Metro Manila </i>
                <li>Less than 1M    : 15k - 16k</li>
                <li>1M to 2M        : 16k - 17k</li>
                <li>More than 2M    : 17k-18k</li>
                </ul>
                <i>For Provinces</i><ul>
                <li>Less than 1M    : Min Wage + 30%</li>
                <li>1M to 2M        : Min Wage + 40%</li>
                <li>More than 2M    : Min Wage + 50%</li>
                </ul>
                </div></div><br><br><hr><br><br>
                <div style="margin-left: 20px;"><b>Notes on salary structure:</b>
        <ol>
            <li>NCR Minimum Wage is P<?php echo $minwage; ?> per day </li>
            <li>There are 26.08 days in a month. Calculation in <i>Process Payroll</i> page</li>
            <li>To use multiplier table below: multiplier * NCR Min Wage * 26.08 days = monthly rate</li>
        </ol>
                    <br><br>
    <?php
    $subtitle='Multiplier From Minimum to Job Level (for Monthly Paid only)';
    $sql='SELECT ss.JobLevelID, JobLevel,PercentIncMinimum AS Hiring,CONCAT(REPLACE(RegStep,"0.",""),"%") AS `RegStep%`,
    IF(REPLACE(`Step1`,"0.","")="1",CONCAT(REPLACE(`Step1`,"0.",""),"0%"),CONCAT(REPLACE(`Step1`,"0.",""),"%")) AS `Step1%`
        ,IF(REPLACE(`Step2`,"0.","")="1",CONCAT(REPLACE(`Step2`,"0.",""),"0%"),CONCAT(REPLACE(`Step2`,"0.",""),"%")) AS `Step2%`
        ,IF(REPLACE(`Step3`,"0.","")="1",CONCAT(REPLACE(`Step3`,"0.",""),"0%"),CONCAT(REPLACE(`Step3`,"0.",""),"%")) AS `Step3%`
        ,IF(REPLACE(`Step4`,"0.","")="1",CONCAT(REPLACE(`Step4`,"0.",""),"0%"),CONCAT(REPLACE(`Step4`,"0.",""),"%")) AS `Step4%`
        ,IF(REPLACE(Maximum,"0.","")="1",CONCAT(REPLACE(Maximum,"0.",""),"0%"),CONCAT(REPLACE(Maximum,"0.",""),"%")) AS `Maximum%`
        
        , format((PercentIncMinimum*REPLACE(RegStep,"0","1")),4) AS `Regularization`
        ,format((PercentIncMinimum*REPLACE(RegStep,"0","1")*REPLACE(`Step1`,"0","1")),4) AS `Step1`
        ,format((PercentIncMinimum*REPLACE(RegStep,"0","1")*REPLACE(`Step1`,"0","1")*REPLACE(`Step2`,"0","1")),4) AS `Step2`
        ,format((PercentIncMinimum*REPLACE(RegStep,"0","1")*REPLACE(`Step1`,"0","1")*REPLACE(`Step2`,"0","1")*REPLACE(`Step3`,"0","1")),4) AS `Step3`
        ,format((PercentIncMinimum*REPLACE(RegStep,"0","1")*REPLACE(`Step1`,"0","1")*REPLACE(`Step2`,"0","1")*REPLACE(`Step3`,"0","1")*REPLACE(`Step4`,"0","1")),4) AS `Step4`
        ,format((PercentIncMinimum*REPLACE(RegStep,"0","1")*REPLACE(`Step1`,"0","1")*REPLACE(`Step2`,"0","1")*REPLACE(`Step3`,"0","1")*REPLACE(`Step4`,"0","1")*REPLACE(`Maximum`,"0","1")),4) AS `Maximum`
         FROM `payroll_0salarystructure` ss JOIN attend_0joblevels jl ON jl.JobLevelID=ss.JobLevelID;';
    // echo $sql; exit();
    $txnidname='JobLevelID';
    $columnnames=array('JobLevelID','JobLevel','Hiring','RegStep%','Regularization','Step1%','Step1','Step2%','Step2','Step3%','Step3','Step4%','Step4','Maximum%','Maximum'); //$width='20%';
    $title=''; $formdesc='';
    //$editprocess='lookupbasetables.php?w=EditSS&JobLevelID=';
    //$editprocesslabel='Edit';
    include '../backendphp/layout/displayastablenosort.php';
    ?>
    </div>
    
    
                <?php
                break;
case 'StructureDailyPaid':
                if (!allowedToOpen(6473,'1rtc')) { echo 'No permission'; exit;}
            $title='Salary Structure - Daily Paid'; 
                
              //  $daysofmonth=26.08; 
            $formdesc='</i><div style="background-color: #e6e6e6;
            width: 1100px;
            border: 2px solid grey;
            padding: 25px;
            margin: 25px;">This serves as our guide. Note that all figures are based on current daily rate recorded in <a href="/acrossyrs/infoandfaq/govtminwages.php" target="_blank">Minimum Wages Table</a>.  <br><br>Provincial hiring rate is 10% higher than the provincial minimum wage or NCR minimum wage rate, whichever is lower.</div><i></i>';

            include_once("tempdata/effectiveminwage.php");

            $sql1='SELECT wo.DateEffective, TotalMinWage AS EffectiveMinWage, CONCAT(RegionMinWageArea," ",Region) as Place, GROUP_CONCAT(" ", Branch) AS Branches FROM `1_gamit`.`payroll_4wageorders` wo JOIN `effectivedate` ed ON ed.DateEffective=wo.DateEffective AND ed.MinWageAreaID=wo.MinWageAreaID LEFT JOIN `1_gamit`.`payroll_0regionsminwageareas` r ON r.MinWageAreaID=wo.MinWageAreaID LEFT JOIN 1branches b ON b.EffectiveMinWageAreaID=r.MinWageAreaID AND Pseudobranch IN (0,2) WHERE Active="1" GROUP BY wo.MinWageAreaID ORDER BY wo.MinWageAreaID;';
            $stmt1=$link->query($sql1); $result1=$stmt1->fetchAll();

            foreach($result1 as $region){ 
                $regionalrate=$region['EffectiveMinWage'];
                $subtitle=$region['Place'].' &nbsp; (Effective Minimum Wage Rate: '.$regionalrate.
                    ')<br><h5>Branches: '.$region['Branches'].'</h5>';
                $startrate=($regionalrate*$multiplier)>=$ncrrate?$ncrrate:$regionalrate*$multiplier;

                $sql='SELECT jl.JobLevelID, JobLevel, GROUP_CONCAT(Position) AS Positions,
                TRUNCATE(SalaryStructureDaily('.$startrate.',jl.JobLevelID,'.$increaserate.',1,1),2) AS `Hiring Rate`, 
                TRUNCATE(SalaryStructureDaily('.$startrate.',jl.JobLevelID,'.$increaserate.','.$steprate.',1),2) AS `Performer 1 yr (2 to 4 years)`, 

                TRUNCATE(SalaryStructureDaily('.$startrate.',jl.JobLevelID,'.$increaserate.','.$steprate.',2),2) AS `Performer 2 yrs (4 to 6 years)`, 


                TRUNCATE(SalaryStructureDaily('.$startrate.',jl.JobLevelID,'.$increaserate.','.$steprate.',3),2) AS `Performer 3 yrs (6 to 8 years)`, 

                
                TRUNCATE(SalaryStructureDaily('.$startrate.',jl.JobLevelID,'.$increaserate.','.$steprate.',4),2) AS `Performer 4 yrs (8 to 10 years)`,
                
                TRUNCATE(SalaryStructureDaily('.$startrate.',jl.JobLevelID,'.$increaserate.','.$steprate.',5),2) AS `Maximum Rate`
                FROM attend_0joblevels jl LEFT JOIN attend_1positions p ON jl.JobLevelID=p.JobLevelID AND PreferredRateType=0
                WHERE jl.JobLevelID<=6 GROUP BY jl.JobLevelID ORDER BY jl.JobLevelID;';

                $columnnames=array('JobLevel','Hiring Rate','Performer 1 yr (2 to 4 years)','Performer 2 yrs (4 to 6 years)','Performer 3 yrs (6 to 8 years)','Performer 4 yrs (8 to 10 years)','Maximum Rate','Positions');
                $hidecount=TRUE; $columnsub=$columnnames; //echo $sql.'<br><br>';
                include('../backendphp/layout/displayastablenosort.php');
                $title='';$formdesc='';
            }
break;

case'DataClosedBy':
	$sql='Update 00dataclosedby set DataClosedBy=Curdate() where ForDB=\'3\'';
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:lookupbasetables.php?w=Structure");
break;			
         

	case 'Monthly':
            if (!allowedToOpen(804,'1rtc')) { echo 'No permission'; exit;}
	    $show=!isset($_POST['show'])?0:$_POST['show'];
	    $formdesc='<form action="#" method="post"><input type=submit value="'.($show==0?'Show Resigned This Year':'Current Only').'">
        <input type=hidden name="show" value="'.($show==0?1:0).'"></form>';
	    
	    if ($show==1){
	    $title='Daily and Monthly of Resigned'; //$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'IDNo');
	    $columnnames=array('IDNo','Nickname','SurName','Tenure','MonthlyTotal','MonthlyBasic','MonthlyDeM','MonthlyTaxShield','DailyTotal','DailyBasic','DailyDeM','DailyTaxShield','DailyORMonthly','Agency?');
            
            $sql='SELECT m.*, `How Long` AS Tenure, "Resigned"  AS CurrentBranch, FORMAT(TotalMonthly,2) AS MonthlyTotal, FORMAT(BasicMonthly,2) AS MonthlyBasic, '
                    .'FORMAT(DeMMonthly,2) AS MonthlyDeM, FORMAT(TaxShieldMonthly,2) AS MonthlyTaxShield, '
                    . 'FORMAT(TotalDaily,2) AS DailyTotal, FORMAT(BasicDaily,2) AS DailyBasic, '
                    .'FORMAT(DeMDaily,2) AS DailyDeM, FORMAT(TaxShieldDaily,2) AS DailyTaxShield, IF(LatestDorM=1,"Monthly","Daily") AS DailyORMonthly, '
                    . 'FORMAT(`SSS-EE`,2) AS SSS, FORMAT(`Philhealth-EE`,2) AS Philhealth, FORMAT(`PagIbig-EE`,2) AS PagIbig, IF(DirectOrAgency=1,"Agency","") AS `Agency?` FROM payroll_21dailyandmonthlyofresigned m ORDER BY Nickname';
					
			
            } else {
	    $columnnames=array('IDNo','Nickname','SurName','Position','CurrentBranch','Tenure','MonthlyTotal','MonthlyBasic','MonthlyDeM','MonthlyTaxShield','SSS','Philhealth','PagIbig','DailyTotal','DailyBasic','DailyDeM','DailyTaxShield','DailyORMonthly','Agency?','LatestDateofChange','CalculatedMonthlyTax');
	    $title='Current Daily and Monthly';
            
	    $sql='SELECT m.*, `How Long` AS Tenure,  IF(m.deptid IN (10,2,3,4),Branch,Dept) AS CurrentBranch, FORMAT(TotalMonthly,2) AS MonthlyTotal, FORMAT(BasicMonthly,2) AS MonthlyBasic, FORMAT(DeMMonthly,2) AS MonthlyDeM, FORMAT(TaxShieldMonthly,2) AS MonthlyTaxShield, 
                    FORMAT(TotalDaily,2) AS DailyTotal, FORMAT(BasicDaily,2) AS DailyBasic, 
                    FORMAT(DeMDaily,2) AS DailyDeM, FORMAT(TaxShieldDaily,2) AS DailyTaxShield, 
                   p.Position, IF(LatestDorM=1,"Monthly","Daily") AS DailyORMonthly, FORMAT(`SSS-EE`,2) AS SSS, FORMAT(`Philhealth-EE`,2) AS Philhealth, FORMAT(`PagIbig-EE`,2) AS PagIbig,  IF(DirectOrAgency=1,"Agency","") AS `Agency?` , FORMAT(CalculatedMonthlyTax,2) AS CalculatedMonthlyTax FROM payroll_21dailyandmonthly as m join `attend_1positions` p on p.PositionID=m.PositionID  
                   JOIN `1branches` b ON b.BranchNo=m.BranchNo JOIN `1departments` d ON d.deptid=m.deptid ';
	    $sql=$sql.(!allowedToOpen(8041,'1rtc')?' WHERE m.IDNo>1002':'');		
		
		}
            $columnsub=array('IDNo','Nickname','SurName','Position','CurrentBranch','Tenure','TotalMonthly','BasicMonthly','DeMMonthly','TaxShieldMonthly','SSS','Philhealth','PagIbig','TotalDaily','BasicDaily','DeMDaily','TaxShieldDaily','DailyORMonthly','Agency?','LatestDateofChange');
           
	    include('../backendphp/layout/displayastable.php');
		
		if ($show==1){
			$sqltotalall='SELECT FORMAT(SUM(TotalMonthly),2) AS TOTALMonthlyTotal, FORMAT(SUM(BasicMonthly),2) AS TOTALMonthlyBasic, '
                    .'FORMAT(SUM(DeMMonthly),2) AS TOTALMonthlyDeM, FORMAT(SUM(TaxShieldMonthly),2) AS TOTALMonthlyTaxShield, '
                    . 'FORMAT(SUM(TotalDaily),2) AS TOTALDailyTotal, '
                    . 'FORMAT(SUM(`SSS-EE`),2) AS TOTALSSS, FORMAT(SUM(`Philhealth-EE`),2) AS TOTALPhilhealth, FORMAT(SUM(`PagIbig-EE`),2) AS TOTALPagIbig FROM payroll_21dailyandmonthlyofresigned m';
				$stmttotalall=$link->query($sqltotalall); $resulttotalall=$stmttotalall->fetch();
				echo '<div>';
				echo '<b>TOTAL INFO</b>';
				echo '<br>&nbsp; &nbsp; MonthlyTotal: '.$resulttotalall['TOTALMonthlyTotal'];
				echo '<br>&nbsp; &nbsp; MonthlyBasic: '.$resulttotalall['TOTALMonthlyBasic'];
				echo '<br>&nbsp; &nbsp; MonthlyDeM: '.$resulttotalall['TOTALMonthlyDeM'];
				echo '<br>&nbsp; &nbsp; MonthlyTaxShield: '.$resulttotalall['TOTALMonthlyTaxShield'];
				echo '<br>&nbsp; &nbsp; DailyTotal: '.$resulttotalall['TOTALDailyTotal'];
				echo '<br>&nbsp; &nbsp; SSS: '.$resulttotalall['TOTALSSS'];
				echo '<br>&nbsp; &nbsp; Philhealth: '.$resulttotalall['TOTALPhilhealth'];
				echo '<br>&nbsp; &nbsp; PagIbig: '.$resulttotalall['TOTALPagIbig'];
				echo '</div>';
		}
		else {
			$sqltotalall='SELECT FORMAT(SUM(TotalMonthly),2) AS TOTALMonthlyTotal, FORMAT(SUM(BasicMonthly),2) AS TOTALMonthlyBasic, FORMAT(SUM(DeMMonthly),2) AS TOTALMonthlyDeM, FORMAT(SUM(TaxShieldMonthly),2) AS TOTALMonthlyTaxShield, 
                    FORMAT(SUM(TotalDaily),2) AS TOTALDailyTotal, FORMAT(SUM(`SSS-EE`),2) AS TOTALSSS, FORMAT(SUM(`Philhealth-EE`),2) AS TOTALPhilhealth, FORMAT(SUM(`PagIbig-EE`),2) AS TOTALPagIbig, FORMAT(SUM(CalculatedMonthlyTax),2) AS TOTALCalculatedMonthlyTax FROM payroll_21dailyandmonthly as m join `attend_1positions` p on p.PositionID=m.PositionID 
                   JOIN `1branches` b ON b.BranchNo=m.BranchNo JOIN `1departments` d ON d.deptid=m.deptid ';
				$sqltotalall.=(!allowedToOpen(8041,'1rtc')?' WHERE m.IDNo>1002':'');
			$stmttotalall=$link->query($sqltotalall); $resulttotalall=$stmttotalall->fetch();
					echo '<div>';
					echo '<b>TOTAL INFO</b>';
					echo '<br>&nbsp; &nbsp; MonthlyTotal: '.$resulttotalall['TOTALMonthlyTotal'];
					echo '<br>&nbsp; &nbsp; MonthlyBasic: '.$resulttotalall['TOTALMonthlyBasic'];
					echo '<br>&nbsp; &nbsp; MonthlyDeM: '.$resulttotalall['TOTALMonthlyDeM'];
					echo '<br>&nbsp; &nbsp; MonthlyTaxShield: '.$resulttotalall['TOTALMonthlyTaxShield'];
					echo '<br>&nbsp; &nbsp; DailyTotal: '.$resulttotalall['TOTALDailyTotal'];
					echo '<br>&nbsp; &nbsp; SSS: '.$resulttotalall['TOTALSSS'];
					echo '<br>&nbsp; &nbsp; Philhealth: '.$resulttotalall['TOTALPhilhealth'];
					echo '<br>&nbsp; &nbsp; PagIbig: '.$resulttotalall['TOTALPagIbig'];
					echo '<br>&nbsp; &nbsp; CalculatedMonthlyTax: '.$resulttotalall['TOTALCalculatedMonthlyTax'];
					echo '</div>';
		}
            break;
	 
        default:
            break;
     }
  $link=null; $stmt=null;         
?>