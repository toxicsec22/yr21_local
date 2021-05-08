<?php
switch ($which){
         case 'Sale': 
            $filetoopen='addeditclientside'; $which='Sale'; $txnidname='TxnID'; break;
         case 'Collect':
             $filetoopen='addeditclientside'; $which='Collect'; $txnidname='TxnID'; break;
        case 'Bounced': 
             $filetoopen='addeditclientside'; $which='Bounced'; $txnidname='TxnID'; break;
         case 'Interbranch': 
         case 'InterbranchAdj': 
         case 'InterbranchPaymt': 
             $filetoopen='addeditclientside'; $which='Interbranch'; $txnidname='TxnID'; break;
         case 'Deposit': 
             $filetoopen='addeditdep';  $txnidname='TxnID'; break;
         case 'Purchase': 
            $filetoopen='formpurch'; $txnidname='TxnID'; break;
         case 'CV': 
            $filetoopen='formcv'; $txnidname='CVNo'; break;
         case 'JV': 
             $filetoopen='formjv'; $txnidname='JVNo'; break;
         case 'AssetandDepr': $filetoopen='assetanddepr'; $txnidname='DeprID'; break;
         case 'PrepaidExpense': $filetoopen='prepaidandamort'; $txnidname='AmortID'; break;
         default: $filetoopen='lookupgenacctg'; $txnidname='TxnID'; break;
        }
?>