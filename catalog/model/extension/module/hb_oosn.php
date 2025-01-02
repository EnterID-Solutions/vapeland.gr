<?php
class ModelExtensionModuleHbOosn extends Model {	
	public function getUniqueId() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "out_of_stock_notify WHERE notified_date IS NULL");
		if (isset($query->rows)){
			return $query->rows;	
		}else {
			return false;
		}
	}
	
	public function getStockStatus($product_id) {
		$query = $this->db->query("SELECT quantity, stock_status_id FROM `" . DB_PREFIX . "product` WHERE status = 1 and product_id = '".(int)$product_id."' LIMIT 1");
		if (isset($query->row)){
			return $query->row;	
		}else {
			return false;
		}
	}
	
	public function getOptionStockStatus($product_id, $product_option_value_id, $product_option_id) {
		$query = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product_option_value WHERE product_id = '".(int)$product_id."' and product_option_id = '".(int)$product_option_id."' and product_option_value_id = '".(int)$product_option_value_id."' LIMIT 1");
		if ($query->row){
			return $query->row;	
		}else {
			return false;
		}
	}
	
	public function validateOptionExists($product_id, $product_option_id) {
		$query = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "product_option WHERE product_id = '".(int)$product_id."' and product_option_id = '".(int)$product_option_id."'");
		if ($query->row['total'] > 0){
			return true;	
		}else {
			return false;
		}
	}
	
	public function getProductDetails($product_id,$language_id) {
		$query = $this->db->query("SELECT a.* , b.name FROM `".DB_PREFIX."product` a, `" . DB_PREFIX . "product_description` b WHERE a.product_id = b.product_id and a.status = 1 and b.product_id = '".(int)$product_id."' and b.language_id = '".(int)$language_id."' LIMIT 1");
		if ($query->row) {
			return $query->row;
		}else{
			return false;
		}
	}
	
	public function getRecord($oosn_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "out_of_stock_notify WHERE notified_date IS NULL AND oosn_id = '".(int)$oosn_id."' LIMIT 1");
		if ($query->row) {
			return $query->row;
		}else{
			return false;
		}
	}
	
	public function updatenotifieddate($oosn_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "out_of_stock_notify SET notified_date = now() WHERE oosn_id = '".(int)$oosn_id."'");
	}
	
	 public function getDemandedList() {
        $query = $this->db->query("SELECT distinct(a.product_id) as pid, b.name, 
        (SELECT sum(qty) from " . DB_PREFIX . "out_of_stock_notify 
        WHERE product_id = pid) AS count FROM " . DB_PREFIX . "out_of_stock_notify a, " . DB_PREFIX . "product_description b 
        where a.product_id = b.product_id and 
        b.language_id = (SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = (SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_admin_language')) and a.notified_date IS NULL ORDER BY count DESC LIMIT 100");

		return $query->rows;
	}
	
	public function checkproduct($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` WHERE product_id = '".(int)$product_id."'");
		if ($query->num_rows > 0){
			return true;	
		}else {
			return false;
		}
	}
	
	public function deleterecord($oosn_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "out_of_stock_notify` WHERE oosn_id = '".(int)$oosn_id."'");
	}
}
?>