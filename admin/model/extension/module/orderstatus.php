<?php
class ModelExtensionModuleOrderStatus extends Model {

	public function getOrderStatusColor($order_status_id) {
	 	$this->createOrderStatusColor();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status_color WHERE order_status_id = '" . (int)$order_status_id . "'");
		if($query->num_rows) {
			return $query->row;
		} else {
			return array('bg'=>'#fff','fc'=>'#000');
		}
	}

	public function createOrderStatusColor() {
		if ($this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."order_status_color'")->num_rows == 0) {
         $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "order_status_color` (
			  `order_status_id` int(11) NOT NULL,
			  `bg` VARCHAR(32) NOT NULL,
			  `fc` varchar(32) NOT NULL
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8" ;
            $this->db->query($sql);
      	}
	}
}