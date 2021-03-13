<?php
if ($clientno>9999){ //client
    $sql0='SELECT Terms, CreditLimit, HoldonRecord+HoldfromTerms+HoldfromLimit AS CalcStatus,
(CASE WHEN HoldonRecord=2 THEN CONCAT("Temporarily Allowed by ",Nickname," on ",c.`TimeStamp`)
WHEN HoldonRecord=1 THEN "HOLD from Record<br>"
ELSE "" END) AS OnRecord,  IF(HoldfromTerms=1,"HOLD from Terms<br>","") AS OnTerms, IF(HoldfromLimit=1,"HOLD from Limit<br>","") AS OnLimit
FROM `1clients` c JOIN acctg_34holdstatus hs ON c.ClientNo=hs.ClientNo 
LEFT JOIN `1employees` e ON e.IDNo=c.EncodedByNo
WHERE c.ClientNo='.$clientno;
    $stmt=$link->query($sql0); $res0=$stmt->fetch();
    $addlformdesc='<br>Terms: '.$res0['Terms'].' days'.str_repeat('&nbsp;', 6).'Limit: '.number_format($res0['CreditLimit'],0).'<br><br>AR Status: '.(($res0['CalcStatus']==0)?'OK':($res0['OnRecord'].$res0['OnTerms'].$res0['OnLimit']));
} else { $addlformdesc='';}

