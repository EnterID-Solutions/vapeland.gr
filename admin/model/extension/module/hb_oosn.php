<?php
class ModelExtensionModuleHbOosn extends Model {
	public function getReports($data = array()) {
		$sql = "SELECT a.*, b.name FROM `" . DB_PREFIX . "out_of_stock_notify` a, `" . DB_PREFIX . "product_description` b WHERE a.product_id = b.product_id and b.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        if ($data['notified'] == 'notified'){
            $sql.= " AND a.notified_date IS NOT NULL ";
        }elseif ($data['notified'] == 'awaiting'){
            $sql.= " AND a.notified_date IS NULL ";
        }
		
		if (!empty($data['search'])){
			$sql .= " AND (a.email LIKE '%".$this->db->escape($data['search'])."%' OR a.fname LIKE '%".$this->db->escape($data['search'])."%' OR a.product_id = '".$this->db->escape($data['search'])."' OR b.name LIKE '%".$this->db->escape($data['search'])."%')";
		}
        
        $sql.= "ORDER BY a.enquiry_date DESC ";
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}	

		$query = $this->db->query($sql);

		return $query->rows;
	}
	
	public function getTotalReports($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "out_of_stock_notify` a, `" . DB_PREFIX . "product_description` b WHERE a.product_id = b.product_id and b.language_id = '" . (int)$this->config->get('config_language_id') . "'";
        
        if ($data['notified'] == 'notified'){
            $sql.= " AND a.notified_date IS NOT NULL ";
        }elseif ($data['notified'] == 'awaiting'){
            $sql.= " AND a.notified_date IS NULL ";
        }
		
		if (!empty($data['search'])){
			$sql .= " AND (a.email LIKE '%".$this->db->escape($data['search'])."%' OR a.fname LIKE '%".$this->db->escape($data['search'])."%' OR a.product_id = '".$this->db->escape($data['search'])."' OR b.name LIKE '%".$this->db->escape($data['search'])."%')";
		}
        
        $query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function getStoreName($store_id){
		$query = $this->db->query("SELECT name FROM `".DB_PREFIX."store` WHERE store_id = '".(int)$store_id."'");
		if ($query->row) {
			return $query->row['name'];
		}else{
			return $this->config->get('config_name');
		}
	}
	
	public function validateCustomerAccount($email){
		$query = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."customer` WHERE `email` = '".$this->db->escape($email)."'");
		if ($query->row['total'] > 0) {
			return 'Existing Customer';
		}else{
			return 'Guest User';
		}
	}
	
	public function validateCustomerPurchases($email, $order_status_id){
		$query = $this->db->query("SELECT sum(total) as total FROM `".DB_PREFIX."order` WHERE `email` = '".$this->db->escape($email)."' AND order_status_id = '".(int)$order_status_id."'");
		if ($query->row) {
			$amount = $query->row['total'];
		}else{
			$amount = 0;
		}
		
		return $this->currency->format($amount, $this->config->get('config_currency'));
	}
	
	public function getCustomerDays($email){
		$query = $this->db->query("SELECT datediff(now(), date_added) as days FROM  `".DB_PREFIX."customer` WHERE `email` = '".$this->db->escape($email)."'");
		if ($query->row) {
			return $query->row['days']. ' days';
		}else{
			return ' - ';
		}
	}
	
	
	public function getProductDetail($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product` a, `" . DB_PREFIX . "product_description` b WHERE a.product_id = b.product_id AND b.product_id = '".(int)$product_id."' and b.language_id = '".(int)$this->config->get('config_language_id')."' LIMIT 1");
		return $query->row;
	}
	
	public function getProductQty($product_id) {
		$product_info = $this->getProductDetail($product_id);
		if ($product_info) {
			return (int)$product_info['quantity'];
		}else{
			return 0;
		}
	}
	
	public function getProductStockStatus($product_id) {
		$product_info = $this->getProductDetail($product_id);
		if ($product_info) {
			$stock_status_id = (int)$product_info['stock_status_id'];
			$stock_query = $this->db->query("SELECT name FROM `".DB_PREFIX."stock_status` WHERE stock_status_id = '".(int)$stock_status_id."' AND language_id = '".(int)$this->config->get('config_language_id')."' LIMIT 1");
			if ($stock_query->row) {
				return $stock_query->row['name'];
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}
	
	public function getCustomersOfProduct($product_id, $type){
		$sql = "SELECT * FROM `".DB_PREFIX."out_of_stock_notify` WHERE `product_id` = '".(int)$product_id."'";
        
        if ($type == 'notified'){
            $sql.= " AND notified_date IS NOT NULL";
        }else{
            $sql.= " AND notified_date IS NULL";
        }
		
		$sql.= " ORDER BY enquiry_date";
		
		$query = $this->db->query($sql);
		if ($query->rows) {
			return $query->rows;
		}else{
			return false;
		}
	}
	
	public function allyears($product_id){
		$results = $this->db->query("SELECT DISTINCT YEAR(enquiry_date) as years FROM `".DB_PREFIX."out_of_stock_notify` WHERE product_id = '".(int)$product_id."' ORDER BY years ASC");;
		$allyears = array();
		if ($results->rows) {
			foreach ($results->rows as $year){
				$allyears[] = $year['years'];
			}
		}
		return $allyears;
	}
	
	public function total_alert($product_id, $month = 1, $year = '2018'){
		$results = $this->db->query("SELECT sum(qty) as total FROM `".DB_PREFIX."out_of_stock_notify` WHERE product_id = '".(int)$product_id."' AND MONTH(enquiry_date) = '".(int)$month."' AND YEAR(enquiry_date) = '".(int)$year."'");
		return (int)$results->row['total'];
	}
	
	public function getReportsforExcel() {
		$sql = "SELECT a.*, b.name FROM `" . DB_PREFIX . "out_of_stock_notify` a, `" . DB_PREFIX . "product_description` b where a.product_id = b.product_id and b.language_id = (SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = (SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_admin_language')) ";
        $sql.= "ORDER BY a.enquiry_date DESC ";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function dailyAlertsStat(){
		 $query = $this->db->query("SELECT date(enquiry_date) as days, count(*) as total FROM `".DB_PREFIX."out_of_stock_notify` GROUP BY DATE(enquiry_date) ORDER BY days");
		 if ($query->rows) {
		 	return $query->rows;
		 }else{
		 	return false;
		 }
	}
    
    public function getTotalAlert() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "out_of_stock_notify");
        return (int)$query->row['total'];
	}
    
    public function getTotalResponded() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "out_of_stock_notify WHERE notified_date IS NOT NULL");
        return (int)$query->row['total'];
	}
    
    public function getCustomerNotified() {
        $query = $this->db->query("SELECT COUNT(distinct(email)) AS total FROM " . DB_PREFIX . "out_of_stock_notify WHERE notified_date IS NOT NULL");
    	return (int)$query->row['total'];
	}
    
    public function getTotalRequested() {
    	$query = $this->db->query("SELECT COUNT(distinct(product_id)) AS total FROM " . DB_PREFIX . "out_of_stock_notify WHERE notified_date IS NULL");
		return (int)$query->row['total'];
	}
    public function getAwaitingNotification() {
        $query = $this->db->query("SELECT COUNT(distinct(email)) AS total FROM " . DB_PREFIX . "out_of_stock_notify WHERE notified_date IS NULL");
		return (int)$query->row['total'];
	}
    
    public function getDemandedList() {
        $query = $this->db->query("SELECT distinct(a.product_id) as pid, b.name, 
        (SELECT sum(qty) from " . DB_PREFIX . "out_of_stock_notify 
        WHERE product_id = pid) AS count FROM " . DB_PREFIX . "out_of_stock_notify a, " . DB_PREFIX . "product_description b 
        where a.product_id = b.product_id and 
        b.language_id = '" . (int)$this->config->get('config_language_id') . "' and a.notified_date IS NULL ORDER BY count DESC LIMIT 100");

		return $query->rows;
	}
	
	public function deleteRecords($record_type) {
        if ($record_type == 'all'){
		    $this->db->query("DELETE FROM " . DB_PREFIX . "out_of_stock_notify");
        }elseif ($record_type == 'archive'){
            $this->db->query("DELETE FROM " . DB_PREFIX . "out_of_stock_notify WHERE notified_date IS NOT NULL");
        }elseif ($record_type == 'awaiting'){
            $this->db->query("DELETE FROM " . DB_PREFIX . "out_of_stock_notify WHERE notified_date IS NULL");
        }
	}	
	
	public function deleteSelected($id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "out_of_stock_notify` WHERE oosn_id = '" . (int)$id . "'");
	}
	
	public function resetSelected($id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "out_of_stock_notify` SET notified_date = NULL WHERE oosn_id = '" . (int)$id . "'");
	}
	
	public function scriptInstallStatus(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_stock_notify_pro_style_ocmod' LIMIT 1");
		if ($query->row) {
			if ($query->row['status'] == 1) {
				return '1'; //Installed
			}else{
				return '-1'; //Disabled
			}
		}else{
			return '0'; //Not Installed
		}
	}
	

	/////////////////////////////////////////
	public function checkInstallation() {
		$query = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."extension` WHERE `code`	= 'hb_oosn'");
		if ($query->row['total'] > 0) {
			return true;
		}else {
			return false;
		}
	}
	
	public function install(){		
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "out_of_stock_notify` (
			  `oosn_id` int(11) NOT NULL AUTO_INCREMENT,
			  `product_id` int(11) NOT NULL,
			  `selected_option_value` TEXT NULL,
			  `selected_option` TEXT NULL,
			  `all_selected_option` TEXT NULL,
			  `email` varchar(96) NOT NULL,
			  `fname` varchar(20) NULL,
			  `phone` varchar(20) NULL,
			  `qty` INT NOT NULL DEFAULT 1,
			  `language_id` INT DEFAULT 1,
			  `store_id` INT NOT NULL DEFAULT 0,
			  `store_url` VARCHAR(150) NULL,
			  `comment` TEXT NULL,
			  `ip` VARCHAR(40) NULL,
			  `enquiry_date` datetime NOT NULL,
			  `notified_date` datetime DEFAULT NULL,
			  PRIMARY KEY (`oosn_id`)
			)DEFAULT CHARSET=utf8");
			
		if ($this->checkInstallation() === false) {
			$this->db->query("INSERT INTO `".DB_PREFIX."extension` (`type`,`code`) VALUES ('module','hb_oosn')");
		}
		
		if (version_compare(VERSION,'2.2.0.0','<' )) {
			$theme = $this->config->get('config_template');
		}else if (version_compare(VERSION,'3.0.0.0','>' )) {
			$theme = $this->config->get('config_theme');
		} else {
			$theme = $this->config->get('theme_default_directory');
		}
		
		if (($theme == 'journal3') and (version_compare(VERSION,'2.3.0.0','>=' )) )  {
			$template_name = 'journal3';
		}else{
			$template_name = 'default';
		}
		
		if ((version_compare(VERSION,'2.0.0.0','>=' )) and (version_compare(VERSION,'2.3.0.0','<' ))) {
			$ocmod_filename = 'ocmod_stockalert_2000_2200_'.$template_name.'.txt';
			$ocmod_name = 'Product Back In-Stock Alert (Core) ['.$template_name.'] [2000-2200]';
		}else if ((version_compare(VERSION,'2.3.0.0','>=' )) and (version_compare(VERSION,'3.0.0.0','<' ))) {
			$ocmod_filename = 'ocmod_stockalert_23xx_'.$template_name.'.txt';
			$ocmod_name = 'Product Back In-Stock Alert (Core) ['.$template_name.'] [23xx]';
		}else if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$ocmod_filename = 'ocmod_stockalert_3xxx_'.$template_name.'.txt';
			$ocmod_name = 'Product Back In-Stock Alert (Core) ['.$template_name.'] [3xxx]';
		}
		
		$ocmod_version = EXTN_VERSION;
		$ocmod_code = 'huntbee_stock_notify_ocmod';	
		$ocmod_author = 'HuntBee OpenCart Services';
		$ocmod_link = 'https://www.huntbee.com';
		
		$file = DIR_APPLICATION . 'view/template/extension/module/ocmod/'.$ocmod_filename;
		if (file_exists($file)) {
			$ocmod_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			$this->db->query("INSERT INTO " . DB_PREFIX . "modification SET code = '" . $this->db->escape($ocmod_code) . "', name = '" . $this->db->escape($ocmod_name) . "', author = '" . $this->db->escape($ocmod_author) . "', version = '" . $this->db->escape($ocmod_version) . "', link = '" . $this->db->escape($ocmod_link) . "', xml = '" . $this->db->escape($ocmod_xml) . "', status = '1', date_added = NOW()");
		}
	}
	
	public function uninstall() {			
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "out_of_stock_notify`");
		$this->db->query("DELETE FROM `".DB_PREFIX."extension` WHERE `code` = 'hb_oosn'");	
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_stock_notify_ocmod'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_stock_notify_pro_style_ocmod'");
	}
	
	public function upgrade() {
		$language_code = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "out_of_stock_notify` LIKE 'language_code'");
		if ($language_code->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "out_of_stock_notify` CHANGE `language_code` `language_id` INT DEFAULT 1");
		}
		$server = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "out_of_stock_notify` LIKE 'server'");
		if ($server->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "out_of_stock_notify` CHANGE `server` `store_url` VARCHAR(150) NULL");
		}
		$store_id = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "out_of_stock_notify` LIKE 'store_id'");
		if (!$store_id->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "out_of_stock_notify` ADD `store_id` INT NOT NULL DEFAULT 0 AFTER `language_id`");
		}
		$qty = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "out_of_stock_notify` LIKE 'qty'");
		if (!$qty->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "out_of_stock_notify` ADD `qty` INT NOT NULL DEFAULT 1 AFTER `phone`");
		}
		$comment = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "out_of_stock_notify` LIKE 'comment'");
		if (!$comment->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "out_of_stock_notify` ADD `comment` TEXT NULL AFTER `store_url`");
		}
		$ip = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "out_of_stock_notify` LIKE 'ip'");
		if (!$ip->num_rows){
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "out_of_stock_notify` ADD `ip` VARCHAR(40) NULL AFTER `comment`");
		}
		
		$this->db->query("UPDATE `" . DB_PREFIX . "out_of_stock_notify` SET `store_url` = '".HTTPS_CATALOG."' WHERE `store_url` IS NULL");
		$this->db->query("UPDATE `" . DB_PREFIX . "out_of_stock_notify` SET `language_id` = '1' WHERE `language_id` = '0'");
	}
	
}
?>