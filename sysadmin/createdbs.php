<title>Create databases</title>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(606,'1rtc')){ echo 'No permission'; exit;}
include_once $path.'/acrossyrs/dbinit/userinit.php';

//include_once('../switchboard/contents.php');
//echo 'This year: '.$currentyr.'<br>Next year: '.$nextyr.'<br>Last year: '.$lastyr; exit();

$link=connect_db($currentyr.'_1rtc',1);
$link->setAttribute(PDO::ATTR_EMULATE_PREPARES,true);

if (allowedToOpen(2201,'1rtc')){
        error_reporting(E_ALL);
	ini_set('display_errors', 1);
}	


?>
<div style='margin-left: 30%; margin-top: 5%;'>
<h4>Create Databases for the New Year</h4><br>
<a href='sendtonewyr.php'>Back</a><br><br><br>
<form action='createdbs.php' method='POST'>
    <input type='submit' name='submit' value='Create dbs and copy schema'><br><br>
    <input type='submit' name='submit' value='Copy foreign keys'><br><br>
    <input type='submit' name='submit' value='Copy procedures and functions'><br><br>
    <input type='submit' name='submit' value='Create views in order'><br><br>
    <input type='submit' name='submit' value='Insert lists data'><br></form>
</div>
<?php

if (!isset($_POST['submit'])) { exit();}

switch ($_POST['submit']){
    
    case 'Create dbs and copy schema':
        $sql='CALL `copy_schema`( "'.$currentyr.'_1rtc", "'.$nextyr.'_1rtc" , 0 )';
        $stmt=$link->prepare($sql); $stmt->execute();
        $arrdbs=array('_static','_trail');
       // echo 'Run as jyesql on sql server';
        $msg=$nextyr.'_1rtc has been created.<br>';        
        foreach ($arrdbs as $db){
            $sql='CALL `copy_schema`( "'.$currentyr.$db.'", "'.$nextyr.$db.'" , 0 )'; echo $sql.'<br/>';
         //   $sql='CREATE SCHEMA `'.$db.'` ;';
            $stmt=$link->prepare($sql); $stmt->execute();
            $msg.=$nextyr.$db.' has been created.<br>';
        }
        echo $msg;
        break;
    
    case 'Copy foreign keys': 
        // First list tables with more than one column as combination of fk
        $sql0='CREATE TEMPORARY TABLE Morefk AS
SELECT TABLE_NAME, REFERENCED_TABLE_NAME from information_schema.key_column_usage WHERE TABLE_SCHEMA=\''.$currentyr.'_1rtc\' AND REFERENCED_TABLE_NAME IS NOT NULL AND ORDINAL_POSITION=2';
        $stmt=$link->prepare($sql0); $stmt->execute();
        
        // Single FK's
        $sql1='SELECT TABLE_NAME,CONCAT(TABLE_NAME," with FK ",REFERENCED_COLUMN_NAME) AS FK, CONCAT(\'ALTER TABLE '.$nextyr.'_1rtc.\',TABLE_NAME, \' ADD CONSTRAINT `\', `CONSTRAINT_NAME`, \'` FOREIGN KEY (`\',COLUMN_NAME,\'`)\', \' REFERENCES \',IF(REFERENCED_TABLE_SCHEMA=\''.$currentyr.'_1rtc\',\''.$nextyr.'_1rtc\',REFERENCED_TABLE_SCHEMA),\'.\', REFERENCED_TABLE_NAME,\' (\',REFERENCED_COLUMN_NAME,\') ON DELETE RESTRICT ON UPDATE CASCADE;\') AS ToAddForeignKey FROM information_schema.key_column_usage WHERE (TABLE_SCHEMA=\''.$currentyr.'_1rtc\' AND REFERENCED_TABLE_NAME IS NOT NULL) AND CONCAT(TABLE_NAME,REFERENCED_TABLE_NAME) NOT IN ( SELECT CONCAT(TABLE_NAME,REFERENCED_TABLE_NAME) FROM Morefk)

UNION 

SELECT TABLE_NAME, CONCAT(TABLE_NAME," with FK ",GROUP_CONCAT(\'`\',REFERENCED_COLUMN_NAME,\'`\')) AS FK, CONCAT(\'ALTER TABLE '.$nextyr.'_1rtc.\',TABLE_NAME, \' ADD CONSTRAINT `\', `CONSTRAINT_NAME`, \'` FOREIGN KEY (\',GROUP_CONCAT(\'`\',COLUMN_NAME,\'`\'),\')\', \' REFERENCES \',IF(REFERENCED_TABLE_SCHEMA=\''.$currentyr.'_1rtc\',\''.$nextyr.'_1rtc\',REFERENCED_TABLE_SCHEMA),\'.\', REFERENCED_TABLE_NAME,\' (\',GROUP_CONCAT(\'`\',REFERENCED_COLUMN_NAME,\'`\'),\') ON DELETE RESTRICT ON UPDATE CASCADE;\') AS ToAddForeignKey FROM information_schema.key_column_usage WHERE TABLE_SCHEMA=\''.$currentyr.'_1rtc\' AND REFERENCED_TABLE_NAME IS NOT NULL 
AND TABLE_NAME  IN ( SELECT TABLE_NAME FROM Morefk)  AND REFERENCED_TABLE_NAME  IN ( SELECT REFERENCED_TABLE_NAME FROM Morefk)
GROUP BY TABLE_NAME, REFERENCED_TABLE_NAME 
ORDER BY TABLE_NAME';
        
    //    $sql1='SELECT CONCAT(TABLE_NAME," with FK ",GROUP_CONCAT(REFERENCED_COLUMN_NAME)) AS FK, CONCAT(\'ALTER TABLE '.$nextyr.'_1rtc.\',TABLE_NAME, \' ADD CONSTRAINT `\', `CONSTRAINT_NAME`, \'` FOREIGN KEY (`\',GROUP_CONCAT(COLUMN_NAME),\'`)\', \' REFERENCES \',IF(REFERENCED_TABLE_SCHEMA=\''.$currentyr.'_1rtc\',\''.$nextyr.'_1rtc\',REFERENCED_TABLE_SCHEMA),\'.\', REFERENCED_TABLE_NAME,\' (\',GROUP_CONCAT(REFERENCED_COLUMN_NAME),\') ON DELETE RESTRICT ON UPDATE CASCADE;\') AS ToAddForeignKey FROM information_schema.key_column_usage WHERE TABLE_SCHEMA=\''.$currentyr.'_1rtc\' AND REFERENCED_TABLE_NAME IS NOT NULL GROUP BY TABLE_NAME, REFERENCED_TABLE_NAME ORDER BY TABLE_NAME;';
        echo $sql1;
        $stmt0=$link->query($sql1); $res0=$stmt0->fetchAll();
        $sql='USE  `'.$nextyr.'_1rtc`; ';
        $stmt=$link->prepare($sql); $stmt->execute();
        $cnt=0;
        foreach($res0 as $key){
            $sql=' '.$key['ToAddForeignKey'].' ';
            $stmt=$link->prepare($sql); $stmt->execute();
            $cnt++;
             echo  $cnt.' - '.$key['FK'].' has been created.<br>';
        }
        echo $cnt.' created';
        
        $title='Missing Foreign Keys -- run the ff: '; // RUN ON SQL ALL ToAddForeignKey; NOTE: 1_gamit is needed for some tables
        echo '<br><br>'.$title.'<br><br>'.$sq11;
//        $columnnames=array('ToAddForeignKey');
//        include('../backendphp/layout/displayastable.php');
        /*
         * FOR REFERENCE ONLY
         * 
         * To delete incorrect and incomplete foreign keys, run the resulting queries:
         * 
         * SELECT CONCAT(\'ALTER TABLE \', TABLE_SCHEMA,\'.\', TABLE_NAME, \' DROP FOREIGN KEY \', CONSTRAINT_NAME, \';\') AS ToDropForeignKey FROM information_schema.key_column_usage WHERE referenced_table_name IS NOT NULL AND TABLE_SCHEMA=\''.$nextyr.'_1rtc\' ORDER BY TABLE_NAME;
         */
        
        break;
 
    case 'Copy procedures and functions':
    
        $sql0='SELECT `routine_name`, routine_type as type FROM INFORMATION_SCHEMA.ROUTINES WHERE ROUTINE_SCHEMA LIKE "'.$currentyr.'_1rtc" ;';
        $stmt0=$link->query($sql0); $res0=$stmt0->fetchAll();
        //$linknext=connect_db($nextyr.'_1rtc',1);
        echo 'RUN ALL IN MYSQL (Some functions may not be completely shown):<BR><BR>USE '.$nextyr.'_1rtc;<BR><BR>';
        $msg='';
        foreach($res0 as $fxn){
            $sql1='SHOW CREATE '.$fxn['type'].' '.$currentyr.'_1rtc.'.$fxn['routine_name'];
            $stmt1=$link->query($sql1); $res1=$stmt1->fetch();
            $sql=' DELIMITER $$ <br>'.PHP_EOL.$res1['Create '. ucfirst(strtolower($fxn['type']))].'  $$ <br>'.PHP_EOL.'  DELIMITER ;';
            echo $sql.'<br><br>';
           // $stmt=$linknext->prepare($sql); $stmt->execute();
            $msg.=$fxn['type'].' '.$fxn['routine_name'].' created.<br><br>';
        }
       // echo $msg; REMOVED TEMP; CREATE FUNCTION statements must be run in mysql
        break;
    
    
case 'Create views in order':
    
    Echo 'RUN THIS IN WORKBENCH, THEN RUN THE RESULTING SQL  REPEATEDLY IN '.$nextyr.' DB, UNTIL ALL VIEWS ARE CREATED.<br><br>';
    $sql='SELECT CONCAT("CREATE VIEW '.$nextyr.'_1rtc.", TABLE_NAME, " AS ", REPLACE(REPLACE(VIEW_DEFINITION, "'.$currentyr.'", "'.$nextyr.'"),"'.$lastyr.'","'.$currentyr.'"),"; ") AS CreateStmt FROM INFORMATION_SCHEMA.VIEWS
       WHERE TABLE_SCHEMA LIKE "'.$currentyr.'_1rtc" AND TABLE_NAME NOT IN (SELECT TABLE_NAME FROM INFORMATION_SCHEMA.VIEWS
       WHERE TABLE_SCHEMA LIKE "'.$nextyr.'_1rtc") ORDER BY TRIM(LEADING LEFT(TABLE_NAME,POSITION("_" IN TABLE_NAME)) FROM TABLE_NAME)';
    
    ECHO $sql.'<br><br>';
    $stmt=$link->query($sql); $res0=$stmt->fetchAll(); 
    $msg='';
        foreach($res0 as $vw){ $msg.=$vw['CreateStmt'].'<br>'; }
        echo $msg;    
    
    /*REMOVED THIS FOR NOW:      INTO OUTFILE "/var/www/html/php/arwan/uploads"   LINES TERMINATED BY "\n";';
    $stmt=$link->prepare($sql); $stmt->execute();
    echo $sql;*/
    // In Windows, format of directory is C:/xampp/htdocs/arwan/uploads/createviews.sql
 break;
 
 case 'Insert lists data':
$sql='SELECT tblname FROM gen_info_000lists WHERE plainlist=1;'; $stmt=$link->query($sql); $res=$stmt->fetchAll(); 
$msg='RUN THESE REPEATEDLY IN '.$nextyr.' DB, UNTIL ALL LISTS ARE COMPLETE.<br><br>';
foreach ($res as $tbl){
    $sql='SELECT COUNT(*) AS Recorded FROM `'.$nextyr.'_1rtc`.`'.$tbl['tblname'].'`;';
    $stmt=$link->query($sql); $res0=$stmt->fetch();
    // echo $tbl['tblname'].' - '.$res0['Recorded'].'<br>';
    if ($res0['Recorded']==0){
    $sql='INSERT IGNORE INTO `'.$nextyr.'_1rtc`.`'.$tbl['tblname'].'` SELECT * FROM `'.$currentyr.'_1rtc`.`'.$tbl['tblname'].'`;';
    echo $sql.'<br>';
    $stmt=$link->prepare($sql); $stmt->execute(); 
    $msg.=$tbl['tblname'].' inserted.<br><br>';
    }
    
    
}
echo $msg.'<br><br>FINISHED';
 break;
        
    default:
        break;
        

}
 $link=null; $stmt=null; 