</center>
<div id="content"><div style="position: relative; left: 20%;  width:80%; ">
        <style>
        ul, ol {  list-style-position: outside;   padding-left: 2em;}
        ul li {}
</style>
<h3><font style="color:maroon; " >Daily attendance:</font></h3><br>  
<ol type='i'>
    <li>Each employee is responsible for his/her attendance.  Time In/Out are done on the system.  If Time In/Out is successful, a page shows the time of attendance.<br></li>
    <li>Only the first entry of the time in/out is recorded.</li>
    <li>A message is shown for each employee who has missing attendance.  The employee must request HR to manually encode his/her missing attendance before the payroll cut-off.  HR must have confirmation from department heads before encoding. </li>
    <li>HR must edit the attendance of office personnel who go to branches with grace periods.</li>
    <li>HR must check completeness of attendance daily.  This includes tagging of overtime if approved by the respective department heads.</li>
    <li>It is recommended that completed attendance is posted immediately.</li>
</ol><br><br>
<h3><font style="color:maroon; " >Leave request process:</font></h3><br>  <i><a href='/<?php echo $url_folder; ?>/flowchart/flowchart.php?w=Preview&print=1&TxnID=10' target='_blank'>see flowchart</a></i>
<ol type='i'>
    <li>An employee must request for leave on the system.(<i><a href='/<?php echo $url_folder; ?>/attendance/leaverequest.php?w=RequestLeave' target='_blank'>Here</a></i>)<br></li>
    <li>The request shall be approved/denied by the supervisor and/or department head.</li>
    <li>The employee must acknowledge the reply of the supervisor and/or department head.</li>
    <li>If the leave request is approved, HR must verify if the employee has SIL to apply, then edits leave request as necessary.</li>
    <li>HR then edits the future attendance of the employee to record the approved leaves.  This will prevent unnecessary missing paid days, and unnecessary checking of missing attendance.</li>
    <li>HR must confirm that the leave has been recorded by clicking on Verified & Recorded on the leave request.</li>
</ol><br><br>

<h3><font style="color:maroon; " >Offsetting:</font></h3><br> 
    <i>Definition:  Offsetting is the exchange of a holiday or restday with a working day for the convenience of the employee.</i><br><br>
    <li>Offset requests must be initiated by the employee only. His/her supervisor/department head may not do so on their behalf.</li>
    <li>Requests must be done <u>before</u> the holiday or restday.  To keep HR in the loop, the information must be encoded in Dept Remarks on the employee's attendance for both days involved.</li>
    <li>Offset days must be within the same payroll period, and within five (5) days of each other.</li>
</ol><br><br>

<h3><font style="color:maroon; " >Shift Assignment:</font></h3><br> 
    <li>There are 3 shifts available:<br>
            Shift 7 : 7:00 am to 4:00 pm <br>
            Shift 8 : 8:00 am to 5:00 pm (default)<br>
            Shift 9 : 9:00 am to 6:00 pm <br>    
    </li>
    <li>Only branch personnel and cashiers are allowed to have different shifts.</li>
    <li>HR may edit shift assignments on unposted attendance dates.</li>
    <li>Operations/Accounting may edit shift assignments for <u>future</u> dates.</li>
</ol><br><br>

<h3><font style="color:maroon; " >To finalize attendance for payroll:</font></h3><br>  
<ol>
    <li>In the Attendance Menu, go to For Payroll section.</li>
</ol><br><br>
    <ul>
        <li>Check Attendance for the Payroll Period for any possible anomalies, such as
            <ul><li>unrecorded attendance or absences</li><li>holiday overtime</li><li>unclear restdays, etc.</li><li>excess in SIL usage</li></ul>
        </li>
        <li>Lookup Summary for Payroll. Check for incorrect totals, or missing figures.</li>
        <li>Once all are <i><b>complete and final</b></i>, send attendance summary to payroll (Send To Payroll).</li>
    </ul>
</div>
</div>
<center>

	
	

