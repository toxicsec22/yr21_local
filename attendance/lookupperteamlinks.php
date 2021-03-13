<?php
include_once('../backendphp/layout/linkstyle.php');
		echo '<br/><div>';
				if (allowedToOpen(628,'1rtc')) {
					echo '<a id=\'link\' href="lookupperteam.php?w=SLBal">Leave Balance</a> ';
					echo '<a id=\'link\' href="lookupperteam.php?w=SLDiscrepancies">Leave Discrepancies</a> ';
				}
				if (allowedToOpen(619,'1rtc')) {
					echo '<a id=\'link\' href="lookupperteam.php?w=LatesPerMonthAll">Lates Per Month</a> ';
				}
				if (allowedToOpen(608,'1rtc')) {
					echo '<a id=\'link\' href="lookupperteam.php?w=AbsencesPerMonthAll">Absences Per Month</a> ';
				}
				if (allowedToOpen(623,'1rtc')) {
					echo '<a id=\'link\' href="lookupperteam.php?w=PerDay">Attendance Per Day</a> ';
				}
				if ((allowedToOpen(622,'1rtc'))){
					echo '<a id=\'link\' href="lookupperteam.php?w=PerMonth">Attendance Per Month</a> ';
				}
                //                 if ((allowedToOpen(622,'1rtc'))){
				// 	echo '<a id=\'link\' href="lookupperteam.php?w=PerPayID">Summary Per Payroll</a> ';
				// }
				if (allowedToOpen(611,'1rtc')){
					echo '<a id=\'link\' href="lookupperteam.php?w=PerPerson">Attendance Per Person</a> ';
				}
				if (allowedToOpen(618,'1rtc')){
					echo '<a id=\'link\' href="lookupperteam.php?w=PerBranch">Attendance Per Branch</a> ';
				}
				// if (allowedToOpen(621,'1rtc')){
				// 	echo '<a id=\'link\' href="leaverequest.php?w=RequestLeave">Leave Request</a> ';
				// }
				if (allowedToOpen(620,'1rtc')){
					echo '<a id=\'link\' href="leaverequest.php?w=ApprovedLeaves">Approved Leaves</a> ';
				}
				if (allowedToOpen(608,'1rtc')){
					echo '<a id=\'link\' href="lookupperteam.php?w=ResignedAttendance">Attendance of Resigned Employee</a>';
				}
				if (allowedToOpen(623,'1rtc')){
					echo '<br><br><a id=\'link\' href="lookupperteam.php?w=PerfectAttendanceNoLate">Perfect Attendance and No Lates Per Month</a>';
					echo ' <a id=\'link\' href="lookupperteam.php?w=SummaryPayroll">Summary for Payroll</a>';
				}
				if (allowedToOpen(611,'1rtc')){
					echo ' <a id=\'link\' href="lookupperteam.php?w=AWOLCount">AWOL Count</a>';
				}
		echo '</div><br/>';
	
?>