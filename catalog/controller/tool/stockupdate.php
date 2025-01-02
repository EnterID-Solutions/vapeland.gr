<?php
class ControllerToolStockupdate extends Controller
{
    private $api_token;
    private $octoken;
    private $language_id=1;
    public function __construct($registry){
      parent::__construct($registry);

      if($this->config->get('module_stockupdate_version') == '2'){
        $this->octoken = '&token=';
      } else{
        $this->octoken = '&api_token=';
      }

    }
    public function index()
    {
        echo "Stock Update Controller";
    }

    public function cron()
    {
      $this->load->model('catalog/product');
      $this->login();
      $updateUrl ='/index.php?route=api/stock/setQuantity'.$this->octoken.$this->session->data['stockupdate_token'];
      $remoteProductsUrl ='/index.php?route=api/stock/getProducts'.$this->octoken.$this->session->data['stockupdate_token'];
      $remote_products = $this->executeCall($remoteProductsUrl, ['ref'=>'vapeland']);
      $my_log = new Log('Product_update.log');
      $my_log->write("Running");
      $local_products = $this->getProducts();

      $diff = array();
      foreach ($remote_products['products'] as $id => $model) {
          if (in_array($model, $local_products)) {
            $local_id = array_search($model, $local_products);
            $diff[$local_id]=$id;
          }
      }
//echo "<pre>";
      foreach ($diff as $local_id => $product_data) {

        $product_info = $this->model_catalog_product->getProduct($local_id);

        $lpo = $this->getProductOptions($local_id);

        $params = array(
          'ref' => 'vapeland',
          'product_id' => $product_data,
          'quantity' => $product_info['quantity'],
          'options' => $lpo
        );
//print_r($params);
        $update = $this->executeCall($updateUrl, $params);

        if (isset($update['error']) && $update['error']) {
            echo "Product not found<br>\n";
        }
        if (isset($update['success']) && $update['success']) {
            echo "Product with id #".$product_data." successfully updated<br>\n";
            if(strlen($update['success']) > 2 ){
              echo $update['success']."<br>\n";
            }
        }
      }
//echo "</pre>";
    }

    private function login(){
      if(isset($this->session->data['stockupdate_token'])){
        $url = '/index.php?route=api/stock/checkToken'.$this->octoken.$this->session->data['stockupdate_token'];
        $postfields = array();
        $results = $this->executeLoginCall($url, $postfields);
        if(isset($results['error'])){
          $url = '/index.php?route=api/login';
          $postfields = array('username' => $this->config->get('module_stockupdate_username'),'key' => $this->config->get('module_stockupdate_password'), 'ref'=>'vapeland');
          $results = $this->executeLoginCall($url, $postfields);
        }
      } else {
        $url = '/index.php?route=api/login';
        $postfields = array('username' => $this->config->get('module_stockupdate_username'),'key' => $this->config->get('module_stockupdate_password'), 'ref'=>'vapeland');
        $results = $this->executeLoginCall($url, $postfields);
      }
      if(isset($results['api_token'])){
        $this->session->data['stockupdate_token'] = $results['api_token'];
      }

      return $this->session->data['stockupdate_token'];
    }

    private function executeCall($url, $postfields){

      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL => $this->config->get('module_stockupdate_url').$url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => http_build_query($postfields),

      ));

      $response = curl_exec($curl);

      curl_close($curl);
      return json_decode($response, true);

    }

    private function executeLoginCall($url, $postfields)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->config->get('module_stockupdate_url').$url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $postfields,

        ));

        $response = curl_exec($curl);
//$this->log->write($response);
        curl_close($curl);
        return json_decode($response, true);
    }


    private function getProducts(){
      $q = $this->db->query("SELECT product_id, model, quantity FROM ".DB_PREFIX."product ");

      $products = array();

      foreach($q->rows as  $row){
        $products[$row['product_id']]=$row['model'];
      }
      return $products;
    }

    private function getProductOptions($product_id){
      $this->load->model('catalog/product');
      $product_options=array();
      $prodOptions = $this->getProductRawOptions($product_id);
      if(count($prodOptions)){
        foreach ($prodOptions as $pr_option) {
          if(count($pr_option['product_option_value'])){
            foreach ($pr_option['product_option_value'] as $ov) {
              $product_options[]=array(
                'name' => $pr_option['name'],
                'value' => $this->getOptionValue($ov['option_value_id']),
                'quantity' => $ov['quantity']
              );
            }
          }
        }
      }

      return $product_options;
    }

    private function getOptionValue($option_value_id){
      $q = $this->db->query("SELECT name FROM ".DB_PREFIX."option_value_description WHERE option_value_id='".(int)$option_value_id."' AND language_id='".(int)$this->language_id."'");

      if($q->num_rows){
        return $q->row['name'];
      } else {
        return '';
      }
    }

    public function getProductRawOptions($product_id) {
  		$product_option_data = array();

  		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->language_id . "' ORDER BY o.sort_order");

  		foreach ($product_option_query->rows as $product_option) {
  			$product_option_value_data = array();

  			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->language_id . "' ORDER BY ov.sort_order");

  			foreach ($product_option_value_query->rows as $product_option_value) {
  				$product_option_value_data[] = array(
  					'product_option_value_id' => $product_option_value['product_option_value_id'],
  					'option_value_id'         => $product_option_value['option_value_id'],
  					'name'                    => $product_option_value['name'],
  					'image'                   => $product_option_value['image'],
  					'quantity'                => $product_option_value['quantity'],
  					'subtract'                => $product_option_value['subtract'],
  					'price'                   => $product_option_value['price'],
  					'price_prefix'            => $product_option_value['price_prefix'],
  					'weight'                  => $product_option_value['weight'],
  					'weight_prefix'           => $product_option_value['weight_prefix']
  				);
  			}

  			$product_option_data[] = array(
  				'product_option_id'    => $product_option['product_option_id'],
  				'product_option_value' => $product_option_value_data,
  				'option_id'            => $product_option['option_id'],
  				'name'                 => $product_option['name'],
  				'type'                 => $product_option['type'],
  				'value'                => $product_option['value'],
  				'required'             => $product_option['required']
  			);
  		}

  		return $product_option_data;
  	}

}
