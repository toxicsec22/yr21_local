<?php
if (isset($whichotherlist)){ 
	switch ($whichotherlist){
		case 'invty':
			include_once "../invty/undeliveredlist.inc";
                        foreach ($otherlist as $list){renderotherlistinv($list,$listcondition);}
			break;
		case 'acctg':
			include_once "../acctg/acctglists.inc";
                        foreach ($otherlist as $list){renderotherlist($list,$listcondition);}
			break;	
	
}	
}
?>