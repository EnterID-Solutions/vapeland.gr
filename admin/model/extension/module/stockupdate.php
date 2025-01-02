<?php
class ModelExtensionModuleStockupdate extends Model {
  private $language_id=1;

  public function install(){

  }

  public function uninstall(){

  }

  public function getProducts(){
    $q = $this->db->query("SELECT product_id, model, quantity FROM ".DB_PREFIX."product ");

    $products = array();

    foreach($q->rows as  $row){
      $products[$row['product_id']]=$row['model'];
    }
    return $products;
  }

  public function getProductOptions($product_id){
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

		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->language_id . "'");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) WHERE pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY ov.sort_order ASC");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'points'                  => $product_option_value['points'],
					'points_prefix'           => $product_option_value['points_prefix'],
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
