<?php
class BigCommerce {

  //declare properties
  private $bigcommerce_token;
  private $bigcommerce_client;
  private $bigcommerce_store;
  private $bigcommerce_api_version;

  public function __construct($token, $client, $store, $version){

    $this->bigcommerce_token = $token;
    $this->bigcommerce_client = $client;
    $this->bigcommerce_store = $store;
    $this->bigcommerce_api_version = $version;
    $this->bigcommerce_url = 'https://api.bigcommerce.com/stores/'.$this->bigcommerce_store.'/'.$this->bigcommerce_api_version.'/';

  }

  public function makeRequest($url_slug, $method, $query){

    $request_headers = array('X-Auth-Client: '.$this->bigcommerce_client,
                             'X-Auth-Token: '.$this->bigcommerce_token);

    $url = $this->bigcommerce_url.$url_slug;
    $url = $this->curlAppendQuery($url, $query);
    $ch = curl_init($url);
    $this->curlSetopts($ch, $method, '', $request_headers);

    $response = curl_exec($ch);
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		curl_close($ch);

    if ($errno) throw new BigcommerceCurlException($error, $errno);
    echo $response;
    return $response;

  }

  public function getFeed($filename){

    $products_count = $this->makeRequest('products/count.json', 'GET', array('published_status'=>'published'));
    $limit = 250;

    $products_count = json_decode($products_count, TRUE);
    $pages = ceil($products_count["count"]/$limit);

    $file = fopen($filename,"w");

    for($i=1; $i<=$pages; $i++) {

        $products = $this->makeRequest('/products.json', 'GET', array("limit" => $limit, "page" => $i, "published_status" => "published"));
        $products = json_decode($products, TRUE);

        foreach($products["products"] as $product) {
            $product_title = $product['title'];
            $product_image = $product['images'][0]['src'];
            $product_department = $product['product_type'];
            $product_url = 'https://'.$this->bigcommerce_store.'.mybigcommerce.com/products/'.$product['handle'];

            foreach($product['variants'] as $variant) {
                $product_variant_id = $variant['id'];
                $product_variant_price = $variant['price'];
                $product_variant_sku = $variant['sku'];

                $variant_data = array(
                  $product_title,
                  $product_image,
                  $product_url,
                  $product_department,
                  $product_variant_id,
                  $product_variant_price,
                  $product_variant_sku
                );

                fputcsv($file, $variant_data);
            }
        }
    }

    fclose($file);

  }

  private function curlSetopts($ch, $method, $payload, $request_headers)
	{
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'bigcommerce-php-api-client');
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, $method);
		if (!empty($request_headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

		if ($method != 'GET' && !empty($payload))
		{
			if (is_array($payload)) $payload = http_build_query($payload);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $payload);
		}
	}

  private function curlAppendQuery($url, $query)
	{
		if (empty($query)) return $url;
		if (is_array($query)) return "$url?".http_build_query($query);
		else return "$url?$query";
	}

}

class BigcommerceCurlException extends Exception { }
class BigcommerceApiException extends Exception
{
	protected $method;
	protected $path;
	protected $params;
	protected $response_headers;
	protected $response;

	function __construct($method, $path, $params, $response_headers, $response)
	{
		$this->method = $method;
		$this->path = $path;
		$this->params = $params;
		$this->response_headers = $response_headers;
		$this->response = $response;

		parent::__construct($response_headers['http_status_message'], $response_headers['http_status_code']);
	}
	function getMethod() { return $this->method; }
	function getPath() { return $this->path; }
	function getParams() { return $this->params; }
	function getResponseHeaders() { return $this->response_headers; }
	function getResponse() { return $this->response; }
}
?>
