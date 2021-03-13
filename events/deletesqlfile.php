<?php
$dir='sqlfile/';
		$files = scandir($dir);
		
		foreach($files as $file){
			$ext= pathinfo($file,PATHINFO_EXTENSION);
			if($file != '.' and $file != '..' and $ext =='sql'){
				unlink('sqlfile/'.$file.'');
			}
		}	
?>
