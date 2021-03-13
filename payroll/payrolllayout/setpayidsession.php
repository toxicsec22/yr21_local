<?php
if (isset($_POST['payrollid'])){
					$_SESSION['payrollidses']=$_POST['payrollid'];
				} else if (isset($_SESSION['payrollidses'])){
					$_POST['payrollid']=$_SESSION['payrollidses'];
				}