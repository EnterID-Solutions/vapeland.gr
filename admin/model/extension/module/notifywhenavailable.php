<?php

use \vendor\isenselabs\notifywhenavailable\maillib as MailLib;

class ModelExtensionModuleNotifyWhenAvailable extends Model {

	public function install(){
		$query = $this->db->query(
			"CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "notifywhenavailable` (
			 	`notifywhenavailable_id` INT(11) NOT NULL AUTO_INCREMENT,
			 	`customer_email` VARCHAR(200) NULL DEFAULT NULL,
			 	`customer_name` VARCHAR(100) NULL DEFAULT NULL,
			 	`customer_comment` TEXT NULL DEFAULT NULL,
			 	`product_id` INT(11) NULL DEFAULT '0',
			 	`selected_options` TEXT NULL DEFAULT NULL,
			 	`date_created` DATETIME  NOT NULL DEFAULT '0000-00-00 00:00:00',
			 	`store_id` int(11) DEFAULT NULL,
			 	`customer_notified` TINYINT(1) NOT NULL DEFAULT '0',
			 	`language` VARCHAR(100) NULL DEFAULT '".$this->config->get('config_language')."',
	 		 	`privacy_policy` INT(11) NULL DEFAULT '0',
			  	PRIMARY KEY (`notifywhenavailable_id`)
		  	)
		  	ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;"
		);
	}

	public function updateDb() {
		// add 'comment' column if not exist
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "notifywhenavailable' AND COLUMN_NAME = 'customer_comment';");
		if (empty($query->row)) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "notifywhenavailable` ADD COLUMN `customer_comment` TEXT NULL AFTER `customer_name`;");
		}

		// add 'privacy_policy' column if not exist
		$query = $this->db->query("SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "notifywhenavailable' AND COLUMN_NAME = 'privacy_policy';");
		if (empty($query->row)) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "notifywhenavailable` ADD COLUMN `privacy_policy` INT(11) NULL DEFAULT '0' AFTER `language_id`;");
		}
	}

	public function uninstall()	{
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "notifywhenavailable`");
	}

	public function getAllSubscribedCustomers($store_id=0) {
		$query =  $this->db->query("SELECT DISTINCT super.*, product.name as product_name FROM `" . DB_PREFIX . "notifywhenavailable` super
			JOIN `" . DB_PREFIX . "product_description` product on super.product_id = product.product_id
			WHERE customer_notified=0
            and store_id = " . (int)$store_id . "
			ORDER BY `date_created` DESC");

		return $query->rows;
	}

	public function viewcustomers($page=1, $limit=8, $store_id=0) {
		if ($page) {
			$start = ($page - 1) * $limit;
		}

		$query =  $this->db->query("SELECT DISTINCT super.*, product.name as product_name FROM `" . DB_PREFIX . "notifywhenavailable` super
		LEFT JOIN `" . DB_PREFIX . "product_description` product on super.product_id = product.product_id
		WHERE customer_notified=0 AND product.language_id = (SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = super.language LIMIT 1)
        AND store_id = " . (int)$store_id . "
		ORDER BY `date_created` DESC
		LIMIT ".$start.", ".$limit);

		return $query->rows;
	}

	public function viewnotifiedcustomers($page=1, $limit=8, $store_id=0) {
		if ($page) {
			$start = ($page - 1) * $limit;
		}

		$query =  $this->db->query("SELECT DISTINCT super.*, product.name as product_name FROM `" . DB_PREFIX . "notifywhenavailable` super
		JOIN `" . DB_PREFIX . "product_description` product on super.product_id = product.product_id
		WHERE customer_notified=1 AND product.language_id = (SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = super.language LIMIT 1)
		AND store_id = " . (int)$store_id . "
		ORDER BY `date_created` DESC
		LIMIT ".$start.", ".$limit);

		return $query->rows;
	}

	public function getTotalCustomers($store_id=0){
			$query = $this->db->query("SELECT COUNT(*) as `count`  FROM `" . DB_PREFIX . "notifywhenavailable` WHERE customer_notified=0 AND store_id=".$store_id);
		return $query->row['count'];
	}

	public function getTotalNotifiedCustomers($store_id=0){
			$query = $this->db->query("SELECT COUNT(*) as `count`  FROM `" . DB_PREFIX . "notifywhenavailable` WHERE customer_notified=1 AND store_id=".$store_id);
		return $query->row['count'];
	}

	public function sendEmailWhenAvailable($product_id, $store_id, $rows) {
        $this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProduct($product_id);

		$store_id = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_to_store`
			WHERE product_id = ".$product_id." AND store_id='" . (int)$store_id . "'
			LIMIT 1");

		if ($store_id->num_rows == 0) return;

		$store_id = $store_id->row['store_id'];

		$this->load->model('tool/image');
		if ($product_info['image']) { $image = $this->model_tool_image->resize($product_info['image'], 200, 200); } else { $image = false; }

		$customers = $this->db->query("SELECT * FROM `" . DB_PREFIX . "notifywhenavailable`
			WHERE product_id = ".$product_id." AND customer_notified=0 AND store_id = ".$store_id." AND notifywhenavailable_id IN (".implode(',', $rows).")
			ORDER BY `date_created` DESC");

		$this->load->model('setting/setting');

		$NotifyWhenAvailable = $this->model_setting_setting->getSetting('notifywhenavailable', $store_id);

		$NotifyWhenAvailable = $NotifyWhenAvailable['notifywhenavailable'];

		if (empty($NotifyWhenAvailable['Enabled']) || $NotifyWhenAvailable['Enabled'] == 'no') return;

		foreach($customers->rows as $cust) {
			if(!isset($NotifyWhenAvailable['EmailText'][$cust['language']])){
				$EmailText = '';
				$EmailSubject = '';
			} else {
				$EmailText = $NotifyWhenAvailable['EmailText'][$cust['language']];
				$EmailSubject = $NotifyWhenAvailable['EmailSubject'][$cust['language']];
			}

			// find the localized name of the product
			$localized_product_name = $this->db->query('SELECT `'.DB_PREFIX.'product_description`.name FROM `'.DB_PREFIX.'product_description` ' .
				'LEFT JOIN `'.DB_PREFIX.'language` ON `'.DB_PREFIX.'language`.`language_id` = `'.DB_PREFIX.'product_description`.`language_id` ' .
				'WHERE `'.DB_PREFIX.'product_description`.`product_id` = '.$product_id.' and `'.DB_PREFIX.'language`.`code` = "'.$cust['language'].'"');
			if ($localized_product_name->num_rows == 1) {
				$localized_product_name = $localized_product_name->row['name'];
			} else {
				// else we have to fall back to the default language, already returned by model_catalog_product->getProduct()
				$localized_product_name = $product_info['name'];
			}

			$customer_store = $this->getCurrentStore($store_id);
			$store_config = $this->getStoreConfig($customer_store['store_id']);

			$string = html_entity_decode($EmailText);
			$patterns = array();
			$patterns[0] = '/{c_name}/';
			$patterns[1] = '/{p_name}/';
			$patterns[2] = '/{p_model}/';
			$patterns[3] = '/{p_image}/';
			$patterns[4] = '/http:\/\/{p_link}/';
			$replacements = array();
			$replacements[0] = $cust['customer_name'];
			$replacements[1] = "<a href=\"" . $customer_store['url'] . "index.php?route=product/product&product_id=".$product_id."\" target=\"_blank\">".$localized_product_name."</a>";
			$replacements[2] = $product_info['model'];
			$replacements[3] = "<img src=\"".$image."\" />";
			$replacements[4] =  $customer_store['url'] ."index.php?route=product/product&product_id=".$product_id;

			$text = preg_replace($patterns, $replacements, $string);

			$message  = '<html dir="ltr" lang="en">' . "\n";
			$message .= '  <head>' . "\n";
			$message .= '    <title>' . $EmailSubject . '</title>' . "\n";
			$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
			$message .= '  </head>' . "\n";
			$message .= '  <body>' . html_entity_decode($text, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
			$message .= '</html>' . "\n";

			$senderMail = html_entity_decode($store_config['config_name'], ENT_QUOTES, 'UTF-8');

			MailLib::send($cust['customer_email'], $senderMail, $EmailSubject, $message, $this->registry, $store_config);

			$update_customers = $this->db->query("UPDATE `" . DB_PREFIX . "notifywhenavailable`
				SET customer_notified=1
				WHERE notifywhenavailable_id = " . $cust['notifywhenavailable_id']);
		}
	}

	public function getCatalogURL() {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_CATALOG;
        } else {
            $storeURL = HTTP_CATALOG;
        }
        return $storeURL;
    }

    public function getCurrentStore($store_id) {
    	$this->load->model('setting/store');
        if($store_id && $store_id != 0) {
            $store = $this->model_setting_store->getStore($store_id);
        } else {
            $store['store_id'] = 0;
            $store['name'] = $this->config->get('config_name');
            $store['url'] = $this->getCatalogURL();
        }
        return $store;
    }

    public function getStoreConfig($store_id) {
    	$this->load->model('setting/setting');
    	return $this->model_setting_setting->getSetting('config', $store_id);
    }
}
