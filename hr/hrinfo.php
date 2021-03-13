<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php');

 
$which=(!isset($_GET['w'])?'OffenseFlow':$_GET['w']);

switch ($which){
   case 'OffenseFlow':
       if (!allowedToOpen(679,'1rtc')) {   echo 'No permission'; exit;} 
         $title='Offense Flowchart';
         $content="<img src='pics/flowchart.jpg'><br><br>";
       break;
   case 'CaseTurnaround':
       if (!allowedToOpen(680,'1rtc')) {   echo 'No permission'; exit;} 
         $title='Case Processing Turnaround Time';
         $content='<style>
    table { border: 1px solid black; border-collapse: collapse; padding: length;}
    tr,td { border: 1px solid black; border-collapse: collapse;   background-color: #FFFFFF; text-align: center; padding: 5px;}
    th { background-color: #000099; color: white; padding: 5px;}
</style>
         <table ><thead>
                <tr><th colspan="2">Days Processing</th><th rowspan="2">Action</th></tr>
         <tr><th>Min</th><th>Max</th></tr></thead>
         <tr><td>1</td><td>1</td><td>Read and acknowledge incident report</td></tr>
         <tr><td>2</td><td>5</td><td>Conduct verifications and preliminary investigation</td></tr>
         <tr><td>1</td><td>1</td><td>Issue Notice to Explain Memo to employee in question</td></tr>
         <tr><td>5</td><td>10</td><td>Conduct investigation/hearing</td></tr>
         <tr><td>1</td><td>1</td><td>Interview Employee with potential offense</td></tr>
         <tr><td>2</td><td>5</td><td>Finalize Case Report Form with  findings and recommendations</td></tr>
         <tr><td>1</td><td>2</td><td>Management approval and sign off</td></tr>
         <tr><td>1</td><td>2</td><td>Issue Notice of Decision</td></tr>
         <tr><td>1</td><td>2</td><td>File documentations in Employee 201 file and update system monitoring</td></tr>
         <tr><td>15</td><td>29</td></tr>
         </table><br><br></center>
         <div style="margin-left: 25%;">
         NOTES:<br>
         <ol>
        <li>Days processing is dependent on complexity and seriousness of potential violation </li>
        <li>By Labor, usual process is to give employee 5 days to respond and explain. We are keeping with our 	
	existing 48Hours but will wait for 5 days to expire before officially writing off employee waiving right to explain</li>
        <li>Management approval and sign off, as needed.</li>
        </ol></div><center>';
         
       break;
}
 $link=null; $stmt=null;
?><title><?php echo $title; ?></title>
<br><br>
<h3><center><?php echo $title; ?></center></h3><br>
<center>
<?php echo $content; ?>
<br>
</center>
