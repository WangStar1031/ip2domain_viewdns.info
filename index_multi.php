<?php
	set_time_limit(-1);
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
	function _multiRequest($urls) {
		$curly = array();
		$result = array();

		$mh = curl_multi_init();
		foreach ($urls as $id => $url) {

			$curly[$id] = curl_init();
			curl_setopt_array($curly[$id], array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 3,
				// CURLOPT_PROXY => $proxy,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache",
				"user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36"
				),
			));
			// curl_setopt($curly[$id], CURLOPT_URL, $url);
			// curl_setopt($curly[$id], CURLOPT_HEADER, 0);
			curl_multi_add_handle($mh, $curly[$id]);
		}

		$running = null;
		do {
			curl_multi_exec($mh, $running);
		} while($running > 0);

		foreach($curly as $id => $c) {
			$result[$id] = curl_multi_getcontent($c);
			curl_multi_remove_handle($mh, $c);
		}

		curl_multi_close($mh); 
		return $result;
	}

	$ipContents = file_get_contents('ips.txt');
	$arrIps = explode(PHP_EOL, $ipContents);
	$results = '';
	$urls = array();
	for( $i = 0; $i < count($arrIps); $i ++){
		array_push($urls, "https://viewdns.info/reverseip/?host=".$arrIps[$i]."&t=1");
	}
	echo "Start request.<br/>";
	$curPos = 0;

	$fileContents = '';
	while( $curPos < count($urls)){
		$arrCurUrls = array();
		for( $i = 0; $i < 100; $i++){
			$arrCurUrls[$i] = $urls[$curPos];
			$curPos ++;
			if( $curPos >= count($urls)){
				break;
			}
		}
		$results = _multiRequest($arrCurUrls);
		$rst = [];
		for( $i = 0; $i < count($results); $i ++){
			$result = $results[$i];
			if(!$result)continue;
			$html = str_get_html($result);
			$records = $html->find("table table tr");
			foreach ($records as $record) {
				$domain = $record->find("td")[0]->innertext();
				if( strpos($domain, "Domain") !== false)continue;
				if(in_array($domain, $rst)) continue;
				if (filter_var($domain, FILTER_VALIDATE_IP)) continue;
				$rst[] = $domain;
				echo $domain . '<br/>';
			}
		}
		for( $i = 0; $i < count($rst); $i++){
			file_put_contents('result.txt', $rst[$i] . PHP_EOL , FILE_APPEND | LOCK_EX);
		}
	}
	
	echo "End request.<br/>";
?>