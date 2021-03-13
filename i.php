<?php


phpinfo(); exit();
// $result = exec('command -v java >/dev/null && echo "yes" || echo "no"');
$result = exec('java -version > NUL && echo yes || echo no');


echo $result; exit();
// echo function_exists('curl_version'); 
// exit();

$curl = curl_init('127.0.0.1:44760/list');
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");  
		
		$request_body = '{}';
		curl_setopt($curl, CURLOPT_HTTPHEADER, 
			array('Content-Type: application/json', 'Content-Length: ' . strlen($request_body)));
			

		curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);
		var_dump( curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body)); exit();
		$curl_response = curl_exec($curl);
		// echo 
		curl_close($curl);	
		
		var_dump ($curl_response); exit();
		// return json_decode($curl_response, true)["ids"];
		var_dump( json_decode($curl_response, true)["ids"]);
		
?>