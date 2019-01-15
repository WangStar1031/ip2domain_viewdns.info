<?php
	set_time_limit(600);
	require_once("simple_html_dom.php");

	function __call_safe_url__($__url) {
	    $__url = str_replace("&amp;", "&", $__url);

	    $curl = curl_init();

	    curl_setopt_array($curl, array(
	      CURLOPT_URL => $__url,
	      CURLOPT_RETURNTRANSFER => true,
	      CURLOPT_ENCODING => "",
	      CURLOPT_MAXREDIRS => 10,
	      CURLOPT_TIMEOUT => 3,
	      CURLOPT_HEADER => 1,
	      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	      CURLOPT_SSL_VERIFYPEER => false,
	      CURLOPT_CUSTOMREQUEST => "GET",
	      CURLOPT_HTTPHEADER => array(
	        "cache-control: no-cache",
	        "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.37"
	      ),
	    ));

	    $response = curl_exec($curl);

	    return $response;
	}

	function __get_domain_names($__ip) {
		$str_url = "https://viewdns.info/reverseip/?host=".$__ip."&t=1";
		$data = __call_safe_url__($str_url);
		$html = str_get_html($data);
		$records = $html->find("table table tr");
		$result = [];
		foreach ($records as $record) {
			$domain = $record->find("td")[0]->innertext();
			if( strpos($domain, "Domain") !== false)continue;
			if(in_array($domain, $result)) continue;
			$result[] = $domain;
		}
		return $result;
	}
	$ipContents = file_get_contents('ips.txt');
	$arrIps = explode(PHP_EOL, $ipContents);
	$results = '';
	foreach ($arrIps as $ip) {
		echo $ip . '<br/>';
		$result = __get_domain_names($ip);
		for( $i = 0; $i < count($result); $i ++){
			echo $result[$i] . '<br/>';
			$results .= $result[$i] . '
';
		}
	}
	file_put_contents('result.txt', $results);
?>