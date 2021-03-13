<title>IT Checklists</title>
<style>
    body { margin-left: 5%; margin-right: 3%;}
    ol { margin-left: 3%;}
</style>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
?>
<br><br>
<h3>IT Checklists</h3>
<br>
<h4>List of items on server that needs to be backed up</h4>
<ol>
    <li>all databases (_1rtc, trail, hist_incus, 1_gamit, static)</li>
    <li>all files (php including images)</li>
    <li>fram files</li>
    <li>cronjobs</li>
    <li>firewalld</li>
    <li>my.cnf</li>
    <li>php.ini</li>
    <li>arwan.conf/arya.conf</li>
</ol>

<br>
<h4>Security Checklist</h4>

<ol>
    <li>intval()</li>
    <li>require confirm token</li>
    <li>accept jpg/png only</li>
    <li>database users (grant privileges)</li>
    <li>always check all file folders</li>
</ol>
