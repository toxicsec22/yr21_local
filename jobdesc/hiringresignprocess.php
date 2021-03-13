</center>
<div id="content"><div style="position: relative; left: 20%;  width:80%; ">
<style>
        ul, ol {  list-style-position: outside;   padding-left: 2em;}
        ul li {}
urgent { font-weight: bold; color: red; font-style: italic;}        
</style>
<?php
switch ($_GET['process']){
    case 'new':
?>
1. Each applicant who has been hired must submit the following, which will be the starting files for his/her 201 file:<br><br>
<ol type='i'>
    <li>Updated resume</li>
    <li>Company information sheet</li>
    <li>NBI clearance</li>
    <li>Medical clearance with drug test</li>
    <li>Sketch of residence</li>
    <li>Passport-size id pictures (white background) - 2 copies</li>
    <li>1"x1" id pictures (white background) - 2 copies</li>
    <li>photocopy of BIR 1902 or BIR ID</li>
    <li>photocopy of SSS E-1 or SSS ID</li>
    <li>photocopy of Philhealth Member Data Record</li>
    <li>photocopy of Pag-Ibig Membership ID Number</li>
    <li>photocopy of driver's license, if required</li>
    <li>Form 2316 from previous employer, if applicable</li>
    <li>Latest SOA from SSS and Pagibig for loans, if applicable</li>
    <li>UBP ATM account number for payroll</li>
    <li>Signed conforme of the employee's handbook</li>
</ol><br><br>
&nbsp; &nbsp; &nbsp; HRD must prepare/accomplish the following:<br><br>
<ol type='i'>
    <li>Job offer (salary calculator can be found in Payroll\Salary Ranges</li>
    <li>Employment contract (type of contract depends on job level)</li>
    <li>Non-Disclosure Agreement with Non-Compete Clause</li>
    <li>Letter of endorsement to BPI</li>
    <li>Letter of endorsement to Branch/Warehouse (if assigned outside of Infinity office)</li>
    <li>Door access (if assigned at Infinity office)</li>
    <li>Company ID</li>
    <li>Create an email address, if applicable.</li>
    <li>Encode the new employee into the system.</li>
</ol><br><br>
2. Information for each new employee must be encoded COMPLETELY. There are 2 steps: a. basic information, b. salary rate.  It is best to have all the information at hand before encoding. This process creates the following entries: <br><br>
<ul>
<li>An employee record in the ID information table. This has all personal information of the employee.</li>
<li>An employee record in the Current Employees table. This is the shortlist of employees for the current year.</li>
<li>Access for the system, with their id number as the temporary password. </li>
<li>This employee is assigned to his encoded position.  This determines all his/her system access. </li>
<li>Branch/office assignment is set for this employee.  If this information is missing, he/she will not be included in payroll.</li>
<li>The new employee's blank attendance records for the whole year is encoded. If these records are missing, attendance of the employee cannot be recorded in any way.</li>
<li>Salary, tax, and government-mandated benefits for the employee are recorded.  This will be the basis for payroll. </li>
</ul>
<?php
    break;
    case 'promotion':
?>
<br><br>
The promotion process may start from <i><u>either</u></i> of the following conditions:<br><br>
<ul>
    <li> the supervisor initiates a <b>special performance evaluation</b>, and the department head makes a recommendation for promotion, or</li>
    <li> the department head sends an <b>email</b> to HR (Employee Relations) with the details of the recommendation. </li>
</ul>
<br><hr><br>
Upon receipt of the recommendation, the HR Department will do the following as part of verification:
<br><br>
<ol>
    <li>Employee Relations: </li><br>
    <ol type='a'>
        <li>Print the recommendation.</li>
        <li>Indicate the supervisor's average score in the last two evaluations of the employee.</li>
        <li>Note or attach relevant cases or memos from the employee's history.</li>
    </ol><br>
    <li>Compenben: Makes final recommendation, which may or may not include salary adjustment. </li><br>
    <li>Compenben: After securing final approval from the EVP, prepares the Notice for Personnel Action for routing.</li>
</ol>

<br><hr><br>The HR process will take a <b>maximum of 5 working days</b>.  Day 1 will be counted as: <br><br>
<ul>
    <li> via special evaluation - the date that the employee has acknowledged the completed evaluation</li>
    <li> via email - the date that HR has acknowledged receipt of the email </li>
</ul>
<br><br>If not specified by the employee's department head, the default effectivity date of the promotion is the date of the NOPA.
<?php
    break;
    case 'resign':
?>
<br><br>
The <b>EMPLOYEE</b> must submit the following:<br><br>
<ul>
    <li>Signed Resignation Letter</li><br>
    <li>All accountabilities, such as (there may be more, depending on position)</li>
    <ul>
        <li>ID</li>
        <li>HMO card, if any</li>
        <li>laptop/cpu and peripherals</li>
        <li>drawer keys</li><li>branch/office/warehouse keys</li>
        <li>all files</li><li>stapler, scissors, ruler, other non-consummable supplies</li><li>cellphone, charger, headset</li>
    </ul>
    <br><br>Special accountabilities per department:<br><br>
    &nbsp; &nbsp; &nbsp; &nbsp; <i><u>Accounting</u></i>: books of accounts, receipts and filespace, passwords<br>
    &nbsp; &nbsp; &nbsp; &nbsp; <i><u>Audit</u></i>: barcode scanner, printers<br>
    &nbsp; &nbsp; &nbsp; &nbsp; <i><u>Branches</u></i>: branch keys, inventory, motorcycle, receipts and forms<br>
    &nbsp; &nbsp; &nbsp; &nbsp; <i><u>Human Resources</u></i>: 201 files, pending cases, memos, passwords<br>
    &nbsp; &nbsp; &nbsp; &nbsp; <i><u>Sales</u></i>: calling cards, client calling cards<br>
    &nbsp; &nbsp; &nbsp; &nbsp; <i><u>Supply Chain</u></i>: vehicle, if any<br>
<br><br>
<li>The resigning employee must get the final resignation schedule from his/her immediate supervisor and department head.</li>
</ul>
<br>  
<b>HRD</b> must process the following: (those in red are <urgent>urgent</urgent>)<br><br>
<ul>
    <li>Route the clearance form.</li>
    <li>Secure all of the employee's accountabilities.</li>
    <li>Conduct an <a href="Exit_Interview.pdf" target="_blank"><b>exit interview</b></a>, results of which must be reported to management and filed into the 201.</li>
    <li><urgent>Remove from door access</urgent>, if applicable.</li>
    <li><urgent>Disenroll from HMO</urgent>, if applicable.</li>
</ul>
<br><br>Email
<ul>
    <li><urgent>Change email password</urgent> and give the email access to the department head for final checking for pending transactions or needed records.</li>
    <li>Delete his/her email address, after the department head's go signal.</li>
</ul>
<br><br>Attendance
<ul>
    <li><urgent>Change password in the system.</urgent></li>
    <li>Set as resigned in the system.</li>
    <li>Delete future attendance data.</li>
</ul>
<br><br>Last Pay
<ul>
    <li>Last payroll of the resigning employee must be set as cash payroll, and not to be released until after clearance.</li>
    <li>Calculate last pay to include: 1. last payroll, 2. prorated 13th month, 3. unused SIL, 4. deductions (inventory charges, uniforms, laptop, seminar, and other accounts).</li>
    <li>Submit to Accounting, if and only if the employee has been completely cleared by all departments.  The resigned employee may be contacted at this point to inform him/her of the availability of the last pay.</li>
</ul>
<br><br>Accounting shall release the last pay with a check.  This shall be given in person after the quit claim form has been signed.

<br><br>Should the resigned employee be unavailable to personally claim his/her check, a quit claim form may be emailed for them to print, sign, and send to the main office.  Only upon the receipt of the signed original quit claim shall the check be deposited into the resigned employee's account by the Accounting Department.  

<br><br>HRD is responsible for securing the signed quit claim form, and for filing into the 201.
<?php
    break;
case 'hrmonthly':
?>
    <b>Semi-monthly:</b><br>
    <ul>
    <li>Submit attendance to manpower agencies.</li>
    <li>Payroll.</li><br><br>
    </ul>
    <b>Monthly:</b><br>
    <ul>
    <li>Submit eligible employees to HMO provider.  Disenroll resigned employees.</li>
    <li>Encode performance evaluations due for the month.</li>
    <li>Process regularization or end-of-contracts.</li>
    </ul><br><br>
    <b>Quarterly:</b><br>
    <ul>
    <li>Verify if nationwide minimum wages are updated.</li>
    </ul>
    <br><br>
    <b>Annual:</b><br><br>
    <ul>
    <li>Service Awards for Tenure</li>
    </ul>
    <br>
    5 yrs : plaque only<br>
    10 yrs: plaque with cash 50% of present salary<br>
    15 yrs: plaque with cash 75% of present salary<br>
    20 yrs: plaque with cash 100% of present salary<br><br><br>

<?php
    break;
case 'perdiv':
?>
    <b>Recruitment</b><br>
    <ol>
    <li>Sourcing of applicants</li>
    <li>Job Fairs</li>
    <li>Onboarding</li>
    <ul style='margin-left: 10%; position: relative;'>
    <li>Encoding into the system</li>
    <li>Orientation</li>
    <li>Issuance of ID</li>
    <li>Door access enrollment</li>
    <li>Endorsement to requesting department</li>
    </ul>
    </ol>
    <br><br>    
    <b>Training</b><br>
    <ol>
    <li>Training Needs Assessment</li>
    <li>Training Analysis – Design</li>
    <li>Training Development – Resources, Schedule & Choosing Participants</li>
    <li>Actual Training – Implementation</li>
    <li>Training Evaluation – Effectiveness of Training</li>    
    </ol>
    <br><br>
    <b>Compensation and Benefits</b><br>
    <ol>
    <li>Attendance</li>
    <ul style='margin-left: 10%; position: relative;'>
    <li>Daily attendance monitoring</li>
    <li>SIL recording and monitoring</li>
    <li>AWOL monitoring and endorsement to ER</li>
    </ul>
    <li>Payroll (semi-monthly)</li>
    <li>SSS, Philhealth, and Pag-Ibig</li>
    <ul style='margin-left: 10%; position: relative;'>
    <li>Processing of payments</li>
    <li>Preparation and submission of monthly/quarterly/annual reports</li>
    <li>Securing annual clearance</li>
    <li>Loan facilitation for employees </li>
    </ul>
    <li>HMO maintenance</li>
    <li>Resignation and clearance</li>
    <ul style='margin-left: 10%; position: relative;'>
    <li>Documentation</li>
    <li>Routing of clearance</li>
    <li>2316</li>
    <li>Calculation and releasing of last pay</li>
    </ul>
    <li>Processing of sales incentives (calculation and special credits)</li>
    <li>Annual 2316 and BIR Alphalist</li>
    </ol>
    <br><br>
    <b>Employee Relations</b><br>
    <ol>
    <li>Performance evaluation (3 months, 6 months, annual)</li>
    <li>Regularization</li>
    <li>Projects to uplift employees</li>
    <li>Merits/Demerits</li>
    <li>Employee commendation</li>
    <li>Incident and Confidential Reports</li>
    <li>Issuance of NTE's</li>
    <li>Investigation, Suspension, and Termination</li>
    <li>Exit interview</li>
    <li>Company activities: xmas party, basketball, outing</li>
    <li>HSS Admin</li>
    <li>HR Dept admin - handling supplies, attendance, etc. for the department</li> 
    </ol>
    <br><br>
    <b>Department Head</b><br>
    <ol>
    <li>Salary review and alignment</li>
    <li>Recommendations for increases</li>
    <li>Recommendations for promotions</li>
    </ol>
<?php
    break;
}
?>
</div>
        
     
</div>
<center>

	
	

