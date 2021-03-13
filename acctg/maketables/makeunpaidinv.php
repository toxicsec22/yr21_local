<?php
ini_set('display_errors', 'On');
include_once( $_SERVER['DOCUMENT_ROOT'].'/acrossyrs/logincodes/checkifloggedon.php');
include_once('../../backendphp/functions/allowedtoopen.php');
if (!allowedToOpen(6480,'1rtcinfo')) { echo 'No permission'; exit;}
include_once($_SERVER['DOCUMENT_ROOT'].'/acrossyrs/dbinit/userinit.php'); 
$link=connect_db("".$currentyr."_1rtc",1);


// makes temporary table unpaid inv for faster calculations
$sql0='drop table if exists `acctg_unpaidinv`';
$stmt=$link->prepare($sql0);
    $stmt->execute();
$sql0='Create Table acctg_unpaidinv (
    ClientNo smallint(6) not null,
    Particulars varchar(50) not null,
    Date date not null,
    InvBalance double not null,
    BranchNo smallint(6) not null,
    DebitAccountID smallint(6) not null,
    `UnpdInvID` INT(11) NOT NULL AUTO_INCREMENT,
 PRIMARY KEY (`UnpdInvID`)
)

select 
        `ar`.`ClientNo` AS `ClientNo`,
        `ar`.`Particulars` AS `Particulars`,
        `ar`.`Date` AS `Date`,
        (if(((not ((`ar`.`Particulars` like \'%return%\')))
                and (not ((`ar`.`Particulars` like \'%bal%\')))),
            `ar`.`SaleAmt`,
            if((`ar`.`SaleAmt` > 0),
                `ar`.`SaleAmt`,
                abs(`ar`.`SaleAmt`))) - ifnull(`r`.`RcdAmount`, 0)) AS `InvBalance`,
        `ar`.`BranchNo` AS `BranchNo`,
        ar.DebitAccountID
    from
        (`acctg_30uniar` `ar`
        left join `acctg_32qryrcdamtperinv` `r` ON (((`ar`.`Particulars` = `r`.`ForChargeInvNo`)
            and (`ar`.`BranchNo` = `r`.`BranchNo`))))
    group by `ar`.`ClientNo` , `ar`.`Particulars` , `ar`.`Date`
    having (`InvBalance` <> 0)';
// echo $sql0; break;
    $stmt=$link->prepare($sql0);
    $stmt->execute();

// makes temporary table undeposited pdc's for faster calculations
$sql0='drop table if exists `acctg_undepositedclientpdcs`';
$stmt=$link->prepare($sql0);
    $stmt->execute();

set_time_limit(30);   // did not prevent gateway timeout
    
$sql0='Create table acctg_undepositedclientpdcs (
ClientNo smallint(6) not null, CRNo  varchar(65) not null,
PDCBank varchar(65) not null,PDCNo varchar(65) not null,PDCBRSTN varchar(65) not null,
SaleDate date null,
DateofPDC date null,
Cash double null,
PDC double null,
BranchNo smallint(6) not null,BranchSeriesNo smallint(6) not null,
PDCID varchar(65) null, 
AtOffice tinyint(1) DEFAULT 0,
  `OfcAcceptedByNo` smallint(6) DEFAULT NULL,
  `AcctgAcceptedByNo` smallint(6) DEFAULT NULL,
  `OfcAcceptTS` datetime DEFAULT NULL,
  `AcctgAcceptTS` datetime DEFAULT NULL,`SendToBank` tinyint(1) DEFAULT 0, `SendToBankByNo` smallint(6) DEFAULT NULL,
`WithBank` tinyint(1) DEFAULT 0,  `WithBankByNo` smallint(6) DEFAULT NULL,  `WithBankTS` datetime DEFAULT NULL, `DebitAccountID` smallint(6) DEFAULT NULL, SendToBankTS datetime DEFAULT NULL,
ClientCheckBankAccountNo VARCHAR(20) DEFAULT NULL
)
SELECT ClientNo,CRNo,PDCBank,PDCNo,PDCBRSTN,SaleDate,DateofPDC,Cash,PDC,BranchNo,BranchSeriesNo, PDCID, `AtOffice`, `OfcAcceptedByNo` , `AcctgAcceptedByNo`, `OfcAcceptTS`, `AcctgAcceptTS`, SendToBank, SendToBankByNo,WithBank, WithBankByNo, WithBankTS, `DebitAccountID`, SendToBankTS, ClientCheckBankAccountNo FROM acctg_38undepositedclientpdcs';
// echo $sql0; break;
$stmt=$link->prepare($sql0);
$stmt->execute();
$stmt=$link->prepare('UPDATE `00staticdataasof` SET `DataAsOf`=CURRENT_TIMESTAMP() WHERE ForDB=1;'); $stmt->execute();
header("Location:".$_SERVER['HTTP_REFERER']."?done=1");
 
?>