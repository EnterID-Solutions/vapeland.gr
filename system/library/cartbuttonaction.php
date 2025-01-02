<?php

/**
  * Handles 'Add To Cart' button replacement action
  *
  **/
class CartButtonAction {
	protected $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}

	public function get($product_info, &$replace_with, $action_setting) {
		if (!(int)$this->config->get("module_product_downloads_status") || !isset($product_info['price']) || !isset($product_info['total_downloads']) || !isset($product_info['free_downloads']) || !array_key_exists('download_id', $product_info) || !array_key_exists('login_required', $product_info) || !in_array($this->config->get('config_store_id'), json_decode(base64_decode($this->config->get('module_product_downloads_as'))))) {
			return 0;
		}

		$this->language->load('extension/module/download/download');

		$total_downloads = $product_info['total_downloads'];
		$free_downloads = $product_info['free_downloads'];

		if (!(float)$product_info['price']) {
			if ((int)$this->config->get($action_setting) == 3) {
				$logged = $this->customer->isLogged();
				$login_text = $this->config->get('module_product_downloads_show_login_required_text');
				$login = $this->config->get('module_product_downloads_require_login');
				$no_link = $this->config->get('module_product_downloads_show_download_without_link');
				$login_free = $this->config->get('module_product_downloads_require_login_free');
				$login_commercial = $this->config->get('module_product_downloads_require_login_commercial');
				$purchasable = $this->config->get('module_product_downloads_show_purchasable_downloads');

				$show_downloads = ($logged || $no_link || $purchasable && !$login_commercial && !$login || !$login && !$login_free || !$logged && $login_text);

				if ($show_downloads) {
					if ($free_downloads == 1 && $total_downloads == 1 && $product_info['download_id']) {
						if ($logged || !(int)$product_info['login_required'] && !$login && !$login_free) {
							$replace_with = array("link" => $this->url->link('extension/module/product_downloads/get', 'did=' . $product_info['download_id']), "button" => $this->language->get('button_download'));
						} else {
							$replace_with = array("link" => $this->url->link('account/login', 'redirect=' . urlencode('product/product&product_id='. $product_info['product_id']), 'SSL'), "button" => $this->language->get('button_login'));
						}
						return (int)$this->config->get($action_setting);
					} else if ($free_downloads == $total_downloads && $total_downloads > 1) {
						$replace_with = array("link" => $this->url->link('product/product', 'product_id=' . $product_info['product_id']), "button" => $this->language->get('button_view'));
						return (int)$this->config->get($action_setting);
					}
				}
			}

			if ((int)$this->config->get($action_setting) == 1) {
				return (int)$this->config->get($action_setting);
			} else if ((int)$this->config->get($action_setting) == 2 && $free_downloads == $total_downloads) {
				return (int)$this->config->get($action_setting);
			}
		}
		return 0;
	}

	public function product($product_id, $price, &$replace_with) {
		if (!(float)$price && (int)$this->config->get("module_product_downloads_status") && in_array($this->config->get('config_store_id'), json_decode(base64_decode($this->config->get('module_product_downloads_as'))))) {
			$this->load->model('extension/module/product_downloads');

			if ((int)$this->config->get("module_product_downloads_product_atc_action") == 3) { // replace
				$this->language->load('extension/module/product_downloads');

				$logged = $this->customer->isLogged();
				$login_text = $this->config->get('module_product_downloads_show_login_required_text');
				$login = $this->config->get('module_product_downloads_require_login');
				$no_link = $this->config->get('module_product_downloads_show_download_without_link');
				$login_free = $this->config->get('module_product_downloads_require_login_free');
				$login_commercial = $this->config->get('module_product_downloads_require_login_commercial');
				$purchasable = $this->config->get('module_product_downloads_show_purchasable_downloads');

				$show_downloads = ($logged || $no_link || $purchasable && !$login_commercial && !$login || !$login && !$login_free || !$logged && $login_text);

				if ($show_downloads) {
					if (isset($this->request->get['dsearch'])) {
						$search = html_entity_decode($this->request->get['dsearch'], ENT_QUOTES, 'UTF-8');
					} else {
						$search = '';
					}

					if (isset($this->request->get['dtags'])) {
						$tags = $this->request->get['dtags'];
					} else {
						$tags = array();
					}

					$filter_data = array(
						'sort'          => "date",
						'order'         => "DESC",
						'start'         => 0,
						'per_page'      => 0,
						'limit'         => 0,
						'search'        => explode(" ", $search),
						'filter_tag'    => $tags
					);

					$results = $this->model_extension_module_product_downloads->getProductDownloads($product_id, $filter_data);
					$total_downloads = $this->model_extension_module_product_downloads->getFilteredDownloadCount();

					$free_dl_count = $this->model_extension_module_product_downloads->getFreeDownloadsCount($product_id);
					$commercial_dl_count = $this->model_extension_module_product_downloads->getProductCommercialDownloadsCount($product_id);

					if (!$total_downloads && $free_dl_count && !$commercial_dl_count) {
						$replace_with = array("link" => $this->url->link('account/login', 'redirect=' . urlencode('product/product&product_id='. $product_id), true), "button" => $this->language->get('button_login'));
						return (int)$this->config->get("module_product_downloads_product_atc_action");
					} else if ($free_dl_count == 1 && !$commercial_dl_count) {
						if ($total_downloads == 1 && ($this->is_external($results[0]['filename']) || file_exists(DIR_DOWNLOAD . $results[0]['filename']))) {
							if ($logged || !(int)$results[0]['login'] && !$login && !$login_free) {
								$replace_with = array("link" => $this->url->link('extension/module/product_downloads/get', 'did=' . $results[0]['download_id']), "button" => $this->language->get('button_download'));
							} else {
								$replace_with = array("link" => $this->url->link('account/login', 'redirect=' . urlencode('product/product&product_id='. $product_id), true), "button" => $this->language->get('button_login'));
							}
							return (int)$this->config->get("module_product_downloads_product_atc_action");
						}
					} else if ($free_dl_count && !$commercial_dl_count) {
						$replace_with = array("link" => "view_downloads", "button" => $this->language->get('button_view_downloads'));
						return (int)$this->config->get("module_product_downloads_product_atc_action");
					}
				}
			}

			if ((int)$this->config->get("module_product_downloads_product_atc_action") == 1) { //hide
				return (int)$this->config->get("module_product_downloads_product_atc_action");
			} else if ((int)$this->config->get("module_product_downloads_product_atc_action") == 2) { //hide
				$free_dl_count = $this->model_extension_module_product_downloads->getFreeDownloadsCount($product_id);
				$commercial_dl_count = $this->model_extension_module_product_downloads->getProductCommercialDownloadsCount($product_id);

				if ($free_dl_count && !$commercial_dl_count) {
					return (int)$this->config->get("module_product_downloads_product_atc_action");
				}
			}
		}
		return 0;
	}

	protected function is_external($file) {
		return preg_match('/^(http|ftp|https):\/\//', $file) == 1;
	}

}
