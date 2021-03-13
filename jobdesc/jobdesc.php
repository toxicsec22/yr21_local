<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

$dept=$_REQUEST['dept'];
switch ($dept){
    case 'acctg':
        if (!allowedToOpen(772,'1rtc')) { echo 'No permission'; exit;}
        $title='Job Descriptions - Accounting Department';
        $text='acctg.php';
        break;
    case 'acctgnotes':
        if (!allowedToOpen(771,'1rtc')) { echo 'No permission'; exit;}
        $title='General Practices';
        $text='acctgnotes.php';
        break;
    case 'auditmanual':
        if (!allowedToOpen(773,'1rtc')) { echo 'No permission'; exit;}
        header('Location:../backendphp/layout/showpdf.php?filename=audit_process_manual.pdf&filepath=../../jobdesc/audit_process_manual.pdf');
        break;
    case 'auditquickguide':
        if (!allowedToOpen(774,'1rtc')) { echo 'No permission'; exit;}
        $title='Quick Guide for Audit';
        $text='auditquickguide.php';
        break;
    case 'itempics':
        $title='Representative Items Per Category';
        $text='itempics.php';
        break;
    case 'auditunits':
        if (!allowedToOpen(775,'1rtc')) { echo 'No permission'; exit;}
        $title='Notes on Units and Repack';
        $text='auditunits.php';
        break;
    case 'localfares':
        if (!allowedToOpen(778,'1rtc')) { echo 'No permission'; exit;}
        header('Location:../backendphp/layout/showpdf.php?filename=audit_process_manual.pdf&filepath=../../jobdesc/local_fares.pdf');
        break;
    case 'newemploy':
        if (!allowedToOpen(779,'1rtc')) { echo 'No permission'; exit;}
        $title='Employment Process'; $text='hiringresignprocess.php';
        break;
    case 'promotion':
        if (!allowedToOpen(7791,'1rtc')) { echo 'No permission'; exit;}
        $title='Promotion Process'; $text='hiringresignprocess.php';
        break;
    case 'resign':
        if (!allowedToOpen(780,'1rtc')) { echo 'No permission'; exit;}
        $title='Resignation Process'; $text='hiringresignprocess.php';
        break;
    case 'hrmonthly':
        if (!allowedToOpen(777,'1rtc')) { echo 'No permission'; exit;}
        $title='HR Monthly Tasks'; $text='hiringresignprocess.php';
        break;
    case 'perdiv':
        if (!allowedToOpen(7771,'1rtc')) { echo 'No permission'; exit;}
        $title='Job Assignments per Division'; $text='hiringresignprocess.php';
        break;
    case 'attendance':
        if (!allowedToOpen(6496,'1rtc')) { echo 'No permission'; exit;}
        $title='Notes on Attendance';
        $text='attendancenotes.php';
        break;
    case 'payrollprocess':
        if (!allowedToOpen(6495,'1rtc')) { echo 'No permission'; exit;}
        $title='Payroll Process';
        $text='payrollprocess.php';
        break;
    case 'govt':
        if (!allowedToOpen(6498,'1rtc')) { echo 'No permission'; exit;}
        $title='Government Processes';
        $text='govt/govtprocess.php';
        break;
    case 'invtyplanlarge':
        if (!allowedToOpen(831,'1rtc')) { echo 'No permission'; exit;}
        header('Location:../backendphp/layout/showpdf.php?filename=large_orders&filepath=../../jobdesc/invty/large_orders.pdf');
        break;
    default:
        break;
}

?>
<html><head><title><?php echo $title; ?></title>
<?php include ('infostyle.php'); $showbranches=false; include_once('../switchboard/contents.php');?>
<!--<div style="float:right;display:inline"><a href="/<?php echo $url_folder; ?>/index.php">Home</a></div>-->
</head><body>
<br><center><h3><?php echo $title; ?></h3></center><br><center>
<?php include ($text); ?></center>
</body>
</html>
