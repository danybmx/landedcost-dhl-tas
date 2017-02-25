<?php

require_once('types/LandedCostMultipleCommoditiesInput.php');
require_once('types/ProductDetails.php');

$client = new SoapClient(dirname(__FILE__) . '/WSDL/MultipleLandedCost.wsdl', array(
	'cache_wsdl' => WSDL_CACHE_NONE,
	'trace' => 1,
	'debug' => 1,
	'login' => 'DHLTAS_User',
	'password' => 'DHLTAS_Pass'
));

$data = file_get_contents('php://input');
$json = json_decode($data);

$products = array();
$country = $json->country;
$transport = $json->transport;

foreach ($json->products as $p) {
    array_push($products, array(
		'hs' => $p->hs,
		'price' => $p->price,
		'quantity' => $p->quantity
    ));
}

try {
	$input = new LandedCostMultipleCommoditiesInput();
	$input->domain = "EXPORT";
	$input->receiverCountry = $country;
	$input->shipToState = "";
	$input->insuranceCurrency = "EUR";
	$input->insuranceValue = 0;
	$input->shipmentCurrency = "EUR";
	$input->shipperCountry = "ES";
	$input->transportationCurrency = "EUR";
	$input->transportationValue = $transport;
	$input->type = "HS";
	$input->productInfo = array();

    foreach ($products as $p) {
        $product = new ProductDetails();
        $product->countryCode = "ES";
        $product->description = "Product";
        $product->productCode = $p['hs'];
        $product->priceCurrency = "EUR";
        $product->priceValue = $p['price'];
        $product->totalQuantity = $p['quantity']; 

        array_push($input->productInfo, $product);
    }

	$result = $client->getLandedCostEstimateForMultipleCommodities($input, "");

	$status = array();
	preg_match('/statusMessage(.*?)\>(.*)\<\/statusMessage\>/', $client->__getLastResponse(), $status);
	$status = $status[2];

	if ($status !== 'SUCCESS') {
		echo "An error has been ocurred\r\n";
		echo $status."\r\n";
	} else {
		foreach ($result->response->productFeeInfo as $fee) {
			echo $fee->product->totalQuantity." x ".$fee->product->description.": ".$fee->product->priceValue."EUR - ".($fee->product->priceValue * $fee->product->totalQuantity)."EUR\r\n";
			foreach ($fee->feeInfo as $fi) {
				echo "  |  ".$fi->feeName.": ".$fi->fee->formula." - ".$fi->fee->feeAmount."EUR (".$fi->fee->conditions.")\r\n";
			} 
            echo "*********\r\n";
		}
		echo "----------------------\r\n";
		echo "Total Estimated Fees: ".$result->response->totalEstimatedFees."EUR\r\n";
		echo "Total Landed Cost: ".$result->response->totalLandedCost."EUR\r\n";
	}
} catch (Exception $e) {
	echo "An error has been ocurred";
	var_dump($e);
}
