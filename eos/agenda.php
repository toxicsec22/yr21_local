<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(1612,1616),'1rtc')){ echo 'No permission'; exit; }
$showbranches=FALSE;
include_once $path.'/acrossyrs/js/includesscripts.php';
include_once('../switchboard/contents.php');

$which=!isset($_GET['f'])?'Level 10 Weekly':$_GET['f'];

echo '<br><br><form method="get" action="agenda.php">';
echo '<input type="submit" name="f" value="Level 10 Weekly"/> ';
echo '<input type="submit" name="f" value="Quarterly"/> ';
echo '<input type="submit" name="f" value="Annual"/>'.'<br/>';
echo '</form><br/>';

?>
<style>
    
</style>
<?php
// style="border-collapse: 0px; border: 0px;"


switch ($which){
    case 'Level 10 Weekly'
        ?><title><?php echo $which; ?></title>
        <div style="margin-left: 30%;">
            <h1><?php echo $which; ?></h1><br>
            <h3>Schedule: Mondays 9am</h3><br>
            <h3>Agenda:</h3>
        </div><br>
            <table id="table1" class="display" style="width:40%; font-size: 10pt; ">
                <th width="80px">Topic</th><th>Time Allotted</th>
                <tr><td>Highlights in the last 7 days: 1 personal and 1 business</td><td>5 minutes</td></tr>
                <tr><td>Scorecard</td><td>5 minutes</td></tr>
                <tr><td>Rock Review</td><td>5 minutes</td></tr>
                <tr><td>Customer/Employee Headlines</td><td>5 minutes</td></tr>
                <tr><td>To-Do List</td><td>5 minutes</td></tr>
                <tr><td>I-Identify<br>D-Discuss<br>S-Solve</td><td>60 minutes</td></tr>
                <tr><td>Conclude:<br><br>Recap To-Do List<br>Cascading messages (who,when,how)<br>Rating (1-10)<br>One-word close</td><td>5 minutes</td></tr>
            </table>
            
        <?php
        break;
    case 'Quarterly'
        ?><title><?php echo $which; ?></title>
        <div style="margin-left: 30%;">
            <h1><?php echo $which; ?> (1 day offsite)</h1><br>
            <br>
            <h3>Prework:</h3>
            Bring completed Vision/Traction Organizer; Everyone brings his/her issues and proposed priorities for the coming quarter
            <br><br>
            <h3>Agenda:</h3>
        </div><br>
            <table id="table1" class="display" style="width:40%; font-size: 10pt; ">
                <th width="80px">Topic</th><th>Time Allotted</th>
                <tr><td>Highlights in the last 90 days:<br><br>1 personal and 1 business<br>what is working and not working<br>expectations for the day</td><td></td></tr>
                <tr><td>Review previous quarter (all numbers and rocks)</td><td></td></tr>
                <tr><td>Review the V/TO</td><td></td></tr>
                <tr><td>Establish next quarter's Rocks</td><td></td></tr>
                <tr><td>Tackle Key Issues (IDS)</td><td></td></tr>
                <tr><td>Next Steps</td><td></td></tr>
                <tr><td>Conclude:<br><br>Feedback on the meeting<br>Expectations met or not<br>Rating (1-10)</td><td></td></tr>
            </table>
        <?php
        break;
    case 'Annual'
        ?><title><?php echo $which; ?></title>
        <div style="margin-left: 30%;">
            <h1><?php echo $which; ?> (2 days offsite)</h1><br>
            <br>
            <h3>Prework:</h3>
            Bring completed Vision/Traction Organizer, proposed budget for next year, and thoughts on goals for next year
            <br><br>
            <h3>Agenda:</h3>
        </div><br>
		<div class="content" style="margin-left: 30%;width:40%">
            <table id="table1" class="display" style="width:100%; font-size: 10pt; ">
			<thead>
                <tr><th width="80px">Day 1</th><th>Time Allotted</th></tr>
			</thead>
			<tbody>
                <tr><td>Highlights in the last year:<br><br>1Rotary's 3 greatest accomplishments in the past yr<br>one greatest personal accomplishment for the year<br>expectations for the 2-day annual planning session</td><td></td></tr>
                <tr><td>Review previous year (all numbers and last quarter rocks)</td><td></td></tr>
                <tr><td>Team health building</td><td></td></tr>
                <tr><td>SWOT/Issues List</td><td></td></tr>
                <tr><td>V/TO (through one-year plan)</td><td></td></tr>
			</tbody>
            </table>
		</div>
        <br><br>
		<div class="content" style="margin-left: 30%;width:40%">
            <table id="table1" class="display" style="width:100%; font-size: 10pt; ">
			<thead>
                <tr><th width="80px">Day 2</th><th>Time Allotted</th></tr>
			</thead>
			<tbody>
                <tr><td>Establish next quarter's Rocks</td><td></td></tr>
                <tr><td>Tackle Key Issues (IDS)</td><td></td></tr>
                <tr><td>Next Steps</td><td></td></tr>
                <tr><td>Conclude:<br><br>Feedback on the meeting<br>Expectations met or not<br>Rating (1-10)</td><td></td></tr>
			</tbody>	
            </table>
		</div>
        <?php
        break;
}