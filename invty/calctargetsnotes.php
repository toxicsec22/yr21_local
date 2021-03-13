<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 ;
include_once "../generalinfo/lists.inc";

$showbranches=false; include_once('../switchboard/contents.php');
// check if allowed
$allowed=array(201, 2012, 2013);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
?>
<title>Notes on Incentives</title>
<style>
thead {color:black; font-family:sans-serif; font-size: small; font-weight: bold; background-color: white; }
tbody {color:black; font-family:sans-serif; font-size: small; overflow:auto;}
tfoot {color:black; background-color: #e6e6e6;}
table {border-collapse: collapse;}
td { padding: 3px; border:1px solid black;}
tr:hover {background-color: #ebebfa}
</style>
<br><br><h3>Notes on Incentives and Calculation of Targets</h3><br/><br/>

<div style='font-weight:bold; color: blue;'>SALES TEAM LEADERS</div><br/>
<div style='margin-left: 50px;'>
<ol>
    <li> Target scores are calculated per fiscal quarter: Jan to Mar, Apr to Jun,  Jul to Sep, Oct to Dec.</li>
    <li> For an STL to be eligible for incentive, he/she must be with the company for the entire quarter.</li>
    <li> All branches assigned to the STL must individually reach 100% score accumulated for the quarter.</li>
    <li> Incentives are based on the branch classification, as follows: </li>
        <br/><br/>
        <table><thead><td>Branch Classification<br>(Current Quarter)</td><td>Fixed Incentive<br>Per Quarter</td></thead>
        <tr><td>Seed</td><td>  2,000</td></tr>
        <tr><td>Growth</td><td>  3,000</td></tr>
        <tr><td>Prime</td><td>  4,000</td></tr>
        <tr><td>Mature</td><td>  10,000</td></tr>
        </table><br/><br/>
    <li>Additional incentive will be given to the eligible STL (from #2 & #3) for each assigned branch that moved to the next level of branch classification from the previous quarter.</li>
	 <li>If a branch is transferred to another STL, that branch will not be considered in requirement #3 for both STL's. If the transferred branch reaches their target, both STL's will get prorated incentive based on calendar days that the branch was assigned to them.</li>
	 <li>Incentives will be given 15 days after the end of each quarter: Apr 15, Jul 15, Oct 15, Jan 15 (of the next year).</li>
        <br/><br/>
        <table><thead><td>Movement</td><td>Additional Incentive</td></thead>
        <tr><td>From Seed to Growth</td><td>  500</td></tr>
        <tr><td>From Growth to Prime</td><td>  1,200</td></tr>
        <tr><td>From Prime to Mature</td><td>  4,000</td></tr>
        <tr><td>Up 2 levels within the year under the same STL</td><td>  5,000</td></tr>
        <tr><td>Up 3 levels within the year under the same STL</td><td>  7,000</td></tr>
        </table><br/><br/>    
</ol></div>
<br>


<u>GENERAL CONCERNS</u><br>
<br> 1. When another branch's invoice is used to satisfy the client's requirements, sales is adjusted to reflect the true transaction.  The branch who serves the item claims the sale.
<br> 2. Incentives for new employees will start on the THIRD month.  For example, if an employee starts on Jan 1, he/she will first receive an incentive on April 15 for the month of March.
<br> 3. When calculating overdue AR, ten (10) days are added after due date to account for collection/clearing.
<br> 4. Cash collections must also be cleared in deposits before they are counted. Cash collections on the 25th until the end of the month are considered in the next month if 
deposited in the next month.
<br> 5. All collections for freight will be disregarded since we do not earn from this. 
<br> 6. Sales of branches less than 4 months old are included in the calculation. Targets are not. 
