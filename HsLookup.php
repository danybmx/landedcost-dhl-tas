<?php

ini_set("always_populate_raw_post_data", 1);

$client = new SoapClient(dirname(__FILE__) . '/WSDL/HsTree.wsdl', array(
	'cache_wsdl' => WSDL_CACHE_NONE,
	'trace' => 1,
	'debug' => 1,
	'login' => 'FLYBIKES_User',
	'password' => 'DHL123_Pass'
));

$data = file_get_contents('php://input');
$json = json_decode($data);

if ($json) {
	$argv = array(
		'',
		$json->country,
		$json->description
	);
}

try {
	if (count($argv) !== 3) {
		echo "Usage: php HsLookup.php ES \"Description\"\r\n";
		exit();
	} else {
		$Country = $argv[1];
		$Description = $argv[2];
		$rss = $client->runInteractiveClassifier($Country, $Description, "HS", "I");
		foreach ($rss as $r) {
			$rs = explode("\n", $r);
			foreach ($rs as $s) {
				if (strlen($s) > 5) {
					$c = explode("\t", $s);
					$string = "";
					for ($x = 1; $x < $c[1]; $x++) {
						$string .= "|____";
					}
					echo $string.$c[0].' - '.$c[2]."\r\n";
				}
			}	
		}
	}
} catch (Exception $e) {
	echo "An error has been ocurred";
}

?>