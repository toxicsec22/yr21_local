<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=false;
include_once('../switchboard/contents.php');
if (!allowedToOpen(2201,'1rtc')) { echo 'No permission'; exit;}

if($_SESSION['(ak0)']==1907 OR $_SESSION['(ak0)']==1002){

} else {

    exit();
}
$db = new SQLite3('/opt/fram/fram.db');
$which=isset($_GET['w'])?$_GET['w']:'list';

switch($which){
    case 'list':
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    $results = $db->query('SELECT * FROM FingerprintTable;');
    $title='Delete Fingerprint';
    echo '<title>'.$title.'</title>';
    echo '<h3>'.$title.'</h3>';
    echo '<table>';
    echo '<tr><td>Employee</td><td></td></tr>';
    while ($row = $results->fetchArray()) {
        // $employee=comboBoxValue($link,'attend_30currentpositions','IDNo',$row['id'],'FullName');
        echo '<form action="fpfetch.php?w=Delete&id='.$row['id'].'" method="POST"><tr><td>'.$row['id'].'</td><td><input type="submit" value="Delete" name="btnDelete" OnClick="return confirm(\'Are you sure you want to DELETE?\');"></td></tr></form>';
    }
    echo '</table>';

    break;

    case 'Delete':
        $statement = $db->prepare('DELETE FROM FingerprintTable WHERE id = :id;');
        $statement->bindValue(':id', $_GET['id']);

        $result = $statement->execute();

        header("Location:fpfetch.php");
    break;


}

?>