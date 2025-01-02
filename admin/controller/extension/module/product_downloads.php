<?php
defined('EXTENSION_NAME')           || define('EXTENSION_NAME',            'Product Downloads PRO');
defined('EXTENSION_VERSION')        || define('EXTENSION_VERSION',         '6.2.0');
defined('EXTENSION_ID')             || define('EXTENSION_ID',              '4968');
defined('EXTENSION_COMPATIBILITY')  || define('EXTENSION_COMPATIBILITY',   'OpenCart 3.0.0.x, 3.0.1.x, 3.0.2.x and 3.0.3.x');
defined('EXTENSION_STORE_URL')      || define('EXTENSION_STORE_URL',       'https://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=' . EXTENSION_ID);
defined('EXTENSION_PURCHASE_URL')   || define('EXTENSION_PURCHASE_URL',    'https://www.opencart.com/index.php?route=marketplace/purchase&extension_id=' . EXTENSION_ID);
defined('EXTENSION_RATE_URL')       || define('EXTENSION_RATE_URL',        'https://www.opencart.com/index.php?route=account/rating/add&extension_id=' . EXTENSION_ID);
defined('EXTENSION_SUPPORT_EMAIL')  || define('EXTENSION_SUPPORT_EMAIL',   'support@opencart.ee');
defined('EXTENSION_SUPPORT_LINK')   || define('EXTENSION_SUPPORT_LINK',    'https://www.opencart.com/index.php?route=support/seller&extension_id=' . EXTENSION_ID);
defined('EXTENSION_SUPPORT_FORUM')  || define('EXTENSION_SUPPORT_FORUM',   'https://forum.opencart.com/viewtopic.php?f=123&t=53705');
defined('OTHER_EXTENSIONS')         || define('OTHER_EXTENSIONS',          'https://www.opencart.com/index.php?route=marketplace/extension&filter_member=bull5-i');
defined('EXTENSION_MIN_PHP_VERSION')|| define('EXTENSION_MIN_PHP_VERSION', '5.4.0');

defined('TA_PREFETCH')              || define('TA_PREFETCH', 1000);

class ControllerExtensionModuleProductDownloads extends Controller {
	private $error = array();
	protected $alert = array(
		'error'     => array(),
		'warning'   => array(),
		'success'   => array(),
		'info'      => array()
	);

	private static $config_defaults = array(
		// General
		'module_product_downloads_installed'                  => 1,
		'module_product_downloads_installed_version'          => EXTENSION_VERSION,
		'module_product_downloads_status'                     => 0,
		'module_product_downloads_force_download'             => 1,
		'module_product_downloads_require_login'              => 0,
		'module_product_downloads_show_login_required_text'   => 0,
		'module_product_downloads_add_to_previous_orders'     => 1,
		'module_product_downloads_update_previous_orders'     => 0,
		'module_product_downloads_delete_file_from_system'    => 0,
		'module_product_downloads_remove_sql_changes'         => 1,
		'module_product_downloads_delete_type'                => "DELETE",
		'module_product_downloads_noindex'                    => 0,
		'module_product_downloads_hash_chars'                 => 'lULg6SFGR0Kmp25HwrhCMJTy39x7ZuAtN1dBcQIzV8OjnqEskfvobW4XiaPDeY',
		'module_product_downloads_services'                   => "W10=",
		'module_product_downloads_as'                         => "WyIwIl0=",

		// Dashboard widget
		// 'module_product_downloads_db_widget_status'           => 0,
		// 'module_product_downloads_db_display_downloads'       => 0,

		// Downloads page
		'module_product_downloads_dp_status'                  => 0,
		'module_product_downloads_dp_header_link'             => 1,
		'module_product_downloads_dp_footer_link'             => 1,
		'module_product_downloads_dp_disable_seo_url'         => 0,
		'module_product_downloads_dp_seo_keywords'            => array(),
		'module_product_downloads_dp_downloads_per_page'      => 25,
		'module_product_downloads_dp_show_search_bar'         => 1,
		'module_product_downloads_dp_show_filter_tags'        => 1,
		'module_product_downloads_dp_show_file_size'          => 1,
		'module_product_downloads_dp_show_date_added'         => 0,
		'module_product_downloads_dp_show_date_modified'      => 0,
		'module_product_downloads_dp_show_icon'               => 1,
		'module_product_downloads_dp_name_as_link'            => 0,
		'module_product_downloads_dp_delay_download'          => 3000,

		// Custom account downloads page
		'module_product_downloads_cadp_status'                => 0,
		'module_product_downloads_cadp_downloads_per_page'    => 25,
		'module_product_downloads_cadp_show_icon'             => 1,
		'module_product_downloads_cadp_show_expired_downloads'=> 0,

		// Free downloads
		'module_product_downloads_show_free_download_count'   => 0,
		'module_product_downloads_require_login_free'         => 0,
		'module_product_downloads_show_download_without_link' => 0,
		'module_product_downloads_add_free_downloads_to_order'=> 0,
		'module_product_downloads_differentiate_customers'    => 0,
		'module_product_downloads_customer_groups'            => array(),

		// Commercial downloads
		'module_product_downloads_show_purchased_downloads'   => 0,
		'module_product_downloads_show_downloads_remaining'   => 1,
		'module_product_downloads_show_purchasable_downloads' => 0,
		'module_product_downloads_require_login_commercial'   => 0,

		// Download samples
		'module_product_downloads_show_sample_constraint'     => 1,
		'module_product_downloads_require_login_sample'       => 0,
		'module_product_downloads_delay_download_sample'      => 3000,
		'module_product_downloads_ds_disable_seo_url'         => 0,
		'module_product_downloads_ds_seo_keywords'            => array(),

		// Icons
		'module_product_downloads_use_fa_icons'               => 0,

		// Auto Add
		'module_product_downloads_aa_status'                  => 0,
		'module_product_downloads_aa_directory'               => DIR_DOWNLOAD,
		'module_product_downloads_aa_constraint'              => 0,
		'module_product_downloads_aa_duration'                => 0,
		'module_product_downloads_aa_duration_unit'           => 60,
		'module_product_downloads_aa_total_downloads'         => -1,
		'module_product_downloads_aa_all_types'               => 0,
		'module_product_downloads_aa_file_types'              => "pdf,rar,zip",
		'module_product_downloads_aa_excludes'                => "",
		'module_product_downloads_aa_file_tags'               => "",
		'module_product_downloads_aa_free_download'           => 0,
		'module_product_downloads_aa_path_to_tags'            => 0,
		'module_product_downloads_aa_recursive'               => 0,
		'module_product_downloads_aa_login'                   => 0,
		'module_product_downloads_aa_sort_order'              => 0,
		'module_product_downloads_aa_download_status'         => 1,
		'module_product_downloads_aa_stores'                  => array("0"),

		// Batch Add
		'module_product_downloads_ba_status'                  => 0,
		'module_product_downloads_ba_directory'               => DIR_DOWNLOAD,
		'module_product_downloads_ba_constraint'              => 0,
		'module_product_downloads_ba_duration'                => 0,
		'module_product_downloads_ba_duration_unit'           => 60,
		'module_product_downloads_ba_total_downloads'         => -1,
		'module_product_downloads_ba_all_types'               => 0,
		'module_product_downloads_ba_file_types'              => "pdf,rar,zip",
		'module_product_downloads_ba_excludes'                => "",
		'module_product_downloads_ba_file_tags'               => "",
		'module_product_downloads_ba_free_download'           => 0,
		'module_product_downloads_ba_path_to_tags'            => 0,
		'module_product_downloads_ba_recursive'               => 0,
		'module_product_downloads_ba_login'                   => 0,
		'module_product_downloads_ba_sort_order'              => 0,
		'module_product_downloads_ba_download_status'         => 1,
		'module_product_downloads_ba_stores'                  => array("0"),

		// Hide 'Add to Cart'
		'module_product_downloads_product_atc_action'         => 0,
		'module_product_downloads_product_price_action'       => 0,
		'module_product_downloads_product_replace_price_with' => array(),
		'module_product_downloads_module_atc_action'          => 0,
		'module_product_downloads_module_price_action'        => 0,
		'module_product_downloads_module_replace_price_with'  => array(),
		'module_product_downloads_list_atc_action'            => 0,
		'module_product_downloads_list_price_action'          => 0,
		'module_product_downloads_list_replace_price_with'    => array(),
	);

	private static $module_defaults = array(
		'module_id'           => '',
		'name'                => '',
		'names'               => array(),
		'type'                => 'product',
		'show_in_tab'         => '0',
		'limit'               => '25',
		'downloads_per_page'  => '10',
		'show_file_size'      => '1',
		'show_date_added'     => '0',
		'show_date_modified'  => '0',
		'show_icon'           => '1',
		'name_as_link'        => '0',
		'show_empty_module'   => '0',
		'show_filter_tags'    => '0',
		'show_search_bar'     => '0',
		'lazy_load'           => '1',
		'status'              => '0',
	);

	private static $event_hooks = array(
		'admin_module_product_downloads_language_add'                   => array('trigger' => 'admin/model/localisation/language/addLanguage/after',    'action' => 'extension/module/product_downloads/language_add_hook'),
		'admin_module_product_downloads_language_delete'                => array('trigger' => 'admin/model/localisation/language/deleteLanguage/after', 'action' => 'extension/module/product_downloads/language_delete_hook'),
		'admin_module_product_downloads_order_delete'                   => array('trigger' => 'admin/model/sale/order/deleteOrder/before',              'action' => 'extension/module/product_downloads/order_delete_hook'),
		'admin_module_product_downloads_menu'                           => array('trigger' => 'admin/view/common/column_left/before',                   'action' => 'extension/module/product_downloads/menu_hook'),
		'admin_module_product_downloads_options_form'                   => array('trigger' => 'admin/view/catalog/option_form/before',                  'action' => 'extension/module/product_downloads/option_form_hook'),

		'catalog_module_product_downloads_order_add'                    => array('trigger' => 'catalog/model/checkout/order/addOrder/after',            'action' => 'extension/module/product_downloads/order_add_hook'),
		'catalog_module_product_downloads_order_edit'                   => array('trigger' => 'catalog/model/checkout/order/editOrder/after',           'action' => 'extension/module/product_downloads/order_edit_hook'),
		'catalog_module_product_downloads_order_history'                => array('trigger' => 'catalog/model/checkout/order/addOrderHistory/before',    'action' => 'extension/module/product_downloads/order_history_hook'),
		'catalog_module_product_downloads_account_download'             => array('trigger' => 'catalog/controller/account/download/before',             'action' => 'extension/module/product_downloads/account_download_hook'),
		'catalog_module_product_downloads_account_download_download'    => array('trigger' => 'catalog/controller/account/download/download/before',    'action' => 'extension/module/product_downloads/account_download_download_hook'),
		'catalog_module_product_downloads_header'                       => array('trigger' => 'catalog/view/common/header/before',                      'action' => 'extension/module/product_downloads/header_hook'),
		'catalog_module_product_downloads_footer'                       => array('trigger' => 'catalog/view/common/footer/before',                      'action' => 'extension/module/product_downloads/footer_hook'),
	);

	public function __construct($registry) {
		parent::__construct($registry);
		$this->config->load('pdp');
	}

	public function index() {
		$this->load->language('extension/module/product_downloads');
		$this->load->model('extension/module/product_downloads');

		$this->document->setTitle($this->language->get('extension_name'));

		$this->load->model('setting/setting');
		$this->load->model('setting/module');

		$ajax_request = isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

		if (isset($this->request->get['module_id'])) {
			$module_id = $this->request->get['module_id'];
		} else {
			$module_id = null;
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && !$ajax_request) {
			if (!is_null($module_id)) {
				if ($this->validateModuleForm($this->request->post)) {
					$this->model_setting_module->editModule($module_id, $this->request->post);

					$this->session->data['success'] = sprintf($this->language->get('text_success_update_module'), $this->request->post['name']);

					$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
				}
			} else {
				if ($this->validateForm($this->request->post)) {
					$original_settings = $this->model_setting_setting->getSetting('module_product_downloads');

					foreach (self::$config_defaults as $setting => $default) {
						$value = $this->config->get($setting);
						if ($value === null) {
							$original_settings[$setting] = $default;
						}
					}

					$modules = isset($this->request->post['modules']) ? $this->request->post['modules'] : array();
					unset($this->request->post['modules']);

					$settings = array_merge($original_settings, $this->request->post);
					$settings['module_product_downloads_installed_version'] = $original_settings['module_product_downloads_installed_version'];

					$settings['module_product_downloads_aa_file_tags'] = $this->processTags($settings['module_product_downloads_aa_file_tags']);
					$settings['module_product_downloads_ba_file_tags'] = $this->processTags($settings['module_product_downloads_ba_file_tags']);

					$this->model_setting_setting->editSetting('module_product_downloads', $settings);

					$previous_modules = $this->model_setting_module->getModulesByCode('product_downloads');
					$previous_modules = array_remap_key_to_id('module_id', $previous_modules);

					foreach ($modules as $module) {
						if (!empty($module['module_id'])) {
							$module_id = $module['module_id'];
							unset($previous_modules[$module_id]);
							$this->model_setting_module->editModule($module_id, $module);
						} else {
							$this->model_setting_module->addModule('product_downloads', $module);

							$module_id = $this->db->getLastId();
							$module['module_id'] = $module_id;
							$this->model_setting_module->editModule($module_id, $module);
						}
					}

					// Delete any modules left over
					foreach ($previous_modules as $module_id => $module) {
						$this->model_setting_module->deleteModule($module_id);
					}

					$this->session->data['success'] = $this->language->get('text_success_update');

					$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
				}
			}
		} else if ($this->request->server['REQUEST_METHOD'] == 'POST' && $ajax_request) {
			$response = array();

			if (!is_null($module_id)) {
				if ($this->validateModuleForm($this->request->post)) {
					$this->model_setting_module->editModule($module_id, $this->request->post);

					$this->alert['success']['updated'] = sprintf($this->language->get('text_success_update_module'), $this->request->post['name']);
				}
			} else {
				if ($this->validateForm($this->request->post)) {
					$original_settings = $this->model_setting_setting->getSetting('module_product_downloads');

					foreach (self::$config_defaults as $setting => $default) {
						$value = $this->config->get($setting);
						if ($value === null) {
							$original_settings[$setting] = $default;
						}
					}

					$modules = isset($this->request->post['modules']) ? $this->request->post['modules'] : array();
					unset($this->request->post['modules']);

					$settings = array_merge($original_settings, $this->request->post);
					$settings['module_product_downloads_installed_version'] = $original_settings['module_product_downloads_installed_version'];

					$settings['module_product_downloads_aa_file_tags'] = $this->processTags($settings['module_product_downloads_aa_file_tags']);
					$settings['module_product_downloads_ba_file_tags'] = $this->processTags($settings['module_product_downloads_ba_file_tags']);

					$response['values']['aa_file_types'] = $settings['module_product_downloads_aa_file_types'];
					$response['values']['ba_file_types'] = $settings['module_product_downloads_ba_file_types'];

					$response['values']['aa_excludes'] = $settings['module_product_downloads_aa_excludes'];
					$response['values']['ba_excludes'] = $settings['module_product_downloads_ba_excludes'];

					if ($settings['module_product_downloads_aa_file_tags'] != $this->request->post['module_product_downloads_aa_file_tags']) {
						$response['values']['aa_file_tags'] = $settings['module_product_downloads_aa_file_tags'];
					}

					if ($settings['module_product_downloads_ba_file_tags'] != $this->request->post['module_product_downloads_ba_file_tags']) {
						$response['values']['ba_file_tags'] = $settings['module_product_downloads_ba_file_tags'];
					}

					$this->model_setting_setting->editSetting('module_product_downloads', $settings);

					$previous_modules = $this->model_setting_module->getModulesByCode('product_downloads');
					$previous_modules = array_remap_key_to_id('module_id', $previous_modules);

					foreach ($modules as $idx => $module) {
						if (!empty($module['module_id'])) {
							$module_id = $module['module_id'];
							unset($previous_modules[$module_id]);
							$this->model_setting_module->editModule($module_id, $module);
						} else {
							$this->model_setting_module->addModule('product_downloads', $module);

							$module_id = $this->db->getLastId();

							$module['module_id'] = $module_id;
							$this->model_setting_module->editModule($module_id, $module);

							$response['values']['modules'][$idx]['module_id'] = $module_id;
						}
					}

					// Delete any modules left over
					foreach ($previous_modules as $module_id => $module) {
						$this->model_setting_module->deleteModule($module_id);
					}

					$this->alert['success']['updated'] = $this->language->get('text_success_update');

					if ((int)$original_settings['module_product_downloads_status'] != (int)$this->request->post['module_product_downloads_status']) {
						$response['reload'] = true;
						$this->session->data['success'] = $this->language->get('text_success_update');
					}
				} else {
					if (!$this->checkVersion()) {
						$response['reload'] = true;
					}
				}
			}

			$response = array_merge($response, array("errors" => $this->error), array("alerts" => $this->alert));

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($response, JSON_UNESCAPED_SLASHES));
			return;
		}

		$data['heading_title'] = $this->language->get('extension_name');
		$data['text_other_extensions'] = sprintf($this->language->get('text_other_extensions'), OTHER_EXTENSIONS);

		$image_dir = "../image/catalog/icons/";
		$data['icon_dir'] = DIR_IMAGE . "catalog/icons/";

		$data['ext_name'] = EXTENSION_NAME;
		$data['ext_version'] = EXTENSION_VERSION;
		$data['ext_id'] = EXTENSION_ID;
		$data['ext_compatibility'] = EXTENSION_COMPATIBILITY;
		$data['ext_store_url'] = EXTENSION_STORE_URL;
		$data['ext_rate_url'] = EXTENSION_RATE_URL;
		$data['ext_purchase_url'] = EXTENSION_PURCHASE_URL;
		$data['ext_support_email'] = EXTENSION_SUPPORT_EMAIL;
		$data['ext_support_link'] = EXTENSION_SUPPORT_LINK;
		$data['ext_support_forum'] = EXTENSION_SUPPORT_FORUM;
		$data['other_extensions_url'] = OTHER_EXTENSIONS;
		$data['oc_version'] = VERSION;
		$data['installed_extensions'] = (array)$this->config->get('pd_pro_extensions');

		if (!is_null($module_id) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($module_id);
			if (!$module_info) {
				$this->response->redirect($this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'], true));
				return;
			}
		} else {
			$module_info = null;
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
			'active'    => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
			'active'    => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('extension_name'),
			'href'      => $this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'], true),
			'active'    => is_null($module_id)
		);

		if (!is_null($module_id)) {
			$module_name = (!empty($module_info['name'])) ? $module_info['name'] : ((!empty($this->request->post['name'])) ? $this->request->post['name'] : EXTENSION_NAME);
			$data['breadcrumbs'][] = array(
				'text'      => $module_name,
				'href'      => $this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module_id, true),
				'active'    => true
			);
		}

		$data['save'] = $this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'] . (!is_null($module_id) ? '&module_id=' . $module_id : ''), true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		$data['delete'] = $this->url->link('extension/module/delete', 'user_token=' . $this->session->data['user_token'] . (!is_null($module_id) ? '&module_id=' . $module_id : ''), true);
		$data['upgrade'] = $this->url->link('extension/module/product_downloads/upgrade', 'user_token=' . $this->session->data['user_token'], true);
		$data['fix_db'] = $this->url->link('extension/module/product_downloads/fix_db', 'user_token=' . $this->session->data['user_token'], true);
		$data['general_settings'] = $this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'], true);
		$data['autocomplete'] = html_entity_decode($this->url->link('extension/module/product_downloads/autocomplete', 'type=%TYPE%&query=%QUERY&user_token=' . $this->session->data['user_token'], true));
		$data['get_tags_url'] = html_entity_decode($this->url->link('extension/module/product_downloads/get_tags', 'user_token=' . $this->session->data['user_token'], true));
		$data['set_tags_url'] = html_entity_decode($this->url->link('extension/module/product_downloads/set_tags', 'user_token=' . $this->session->data['user_token'], true));
		$data['statistics'] = html_entity_decode($this->url->link('extension/module/product_downloads/statistics', 'user_token=' . $this->session->data['user_token'], true));
		$data['filter'] = html_entity_decode($this->url->link('extension/module/product_downloads/autocomplete', '',true));
		$data['extension_installer'] = $this->url->link('marketplace/installer', 'user_token=' . $this->session->data['user_token'], true);
		$data['modifications'] = $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'], true);
		$data['events'] = $this->url->link('marketplace/event', 'user_token=' . $this->session->data['user_token'], true);
		$data['theme_editor'] = $this->url->link('design/theme', 'user_token=' . $this->session->data['user_token'], true);
		$data['dashboard'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
		$data['services'] = html_entity_decode($this->url->link('extension/module/product_downloads/services', 'user_token=' . $this->session->data['user_token'], true));

		if (!$this->checkPrerequisites()) {
			$this->showErrorPage($data);
			return;
		}

		$db_structure_ok = $this->checkVersion() && $this->model_extension_module_product_downloads->checkDatabaseStructure($this->alert);
		$db_errors = !($db_structure_ok && $this->model_extension_module_product_downloads->checkDatabaseConsistency($this->alert));

		$this->checkVersion(true);

		$this->alert = array_merge($this->alert, $this->model_extension_module_product_downloads->getAlerts());

		$data['update_pending'] = !$this->checkVersion();

		if (!$data['update_pending']) {
			$this->updateEventHooks();
		} else if (!is_null($module_id)) {
			$this->response->redirect($this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}

		$data['db_errors'] = $db_errors;

		$data['ssl'] = (
				(int)$this->config->get('config_secure') ||
				!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1') ||
				!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ||
				!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'
			) ? 's' : '';

		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$data['languages'] = array_remap_key_to_id('language_id', $languages);
		$data['default_language'] = $this->config->get('config_language_id');

		$data['seo_url'] = $this->config->get('config_seo_url');

		$this->load->model('customer/customer_group');
		$customer_groups = $this->model_customer_customer_group->getCustomerGroups();
		$data['customer_groups'] = array_remap_key_to_id('customer_group_id', $customer_groups);

		$this->load->model('extension/module/download/tag');
		$total_tags = $this->model_extension_module_download_tag->getTotalDownloadTags();
		$data['tags'] = array();

		$update_pending = !$this->validate();

		if ($total_tags < TA_PREFETCH && !$update_pending) {
			$tags = $this->model_extension_module_download_tag->getDownloadTags();
			foreach ($tags as $tag) {
				$data['tags'][] = array("id" => $tag["name"], "text" => $tag["name"]);
			}
			$data['tags'] = json_encode($data['tags']);
		}

		$this->load->model('extension/module/download/download');
		$total_downloads = $this->model_extension_module_download_download->getTotalDownloads();
		$data['downloads'] = array();

		if ($total_downloads < TA_PREFETCH && !$update_pending) {
			$downloads = $this->model_extension_module_download_download->getDownloads();
			foreach ($downloads as $download) {
				$data['downloads'][] = array("id" => $download["download_id"], "text" => html_entity_decode($download["name"]), ENT_QUOTES, 'UTF-8');
			}
			$data['downloads'] = json_encode($data['downloads']);
		}

		// Get icon listing
		$icons = array();
		if ($handler = opendir($data['icon_dir'])) {
			while (($sub = readdir($handler)) !== FALSE) {
				if ($sub != "." && $sub != ".." && is_file($data['icon_dir'] . $sub)) {
					$info = pathinfo($sub);
					if(mb_strtolower($info["extension"]) == "png") {
						$icons[] = array("type" => $info["filename"], "src" => $image_dir . $sub);
					}
				}
			}
			closedir($handler);
		}
		$data['icons'] = $icons;
		$data['fa_icons'] = get_fa_icons();

		$data['installed_version'] = $this->installedVersion();

		if (is_null($module_id)) {
			# Loop through all settings for the post/config values
			foreach (array_keys(self::$config_defaults) as $setting) {
				if (isset($this->request->post[$setting])) {
					$data[$setting] = $this->request->post[$setting];
				} else {
					$data[$setting] = $this->config->get($setting);
					if ($data[$setting] === null) {
						if (!isset($this->alert['warning']['unsaved']) && $this->checkVersion())  {
							$this->alert['warning']['unsaved'] = $this->language->get('error_unsaved_settings');
						}
						if (isset(self::$config_defaults[$setting])) {
							$data[$setting] = self::$config_defaults[$setting];
						}
					}
				}
			}
			if (isset($this->request->post['modules'])) {
				$data['modules'] = $this->request->post['modules'];
			} else {
				$modules = $this->model_setting_module->getModulesByCode('product_downloads');

				foreach ($modules as $idx => $module) {
					$module_settings = json_decode($module['setting'], true);
					unset($module['setting']);
					$module = array_merge($module, $module_settings);

					foreach (array_keys(self::$module_defaults) as $setting) {
						if (!isset($module[$setting])) {
							$module[$setting] = self::$module_defaults[$setting];

							if (!isset($this->alert['warning']['unsaved']) && $this->checkVersion())  {
								$this->alert['warning']['unsaved'] = $this->language->get('error_unsaved_settings');
							}
						}
						$modules[$idx] = $module;
					}
				}

				$data['modules'] = $modules;
			}

			foreach (array_keys(self::$module_defaults) as $setting) {
				$data["module_product_downloads_m_$setting"] = self::$module_defaults[$setting];
			}

			$this->load->model('setting/store');

			$stores = $this->model_setting_store->getStores();

			$data['stores'] = array();

			$data['stores'][0] = array(
				'store_id' => '0',
				'name' => $this->config->get('config_name'),
				'url'  => HTTP_CATALOG
			);

			foreach ($stores as $store) {
				$data['stores'][$store['store_id']] = array(
					'store_id' => $store['store_id'],
					'name' => $store['name'],
					'url'  => $store['url']
				);
			}

			$this->load->model('setting/extension');
			$installed_dashboards = $this->model_setting_extension->getInstalled('dashboard');
			if (in_array('downloads', $installed_dashboards)) {
				$data['dashboard_widget'] = array(
					'class'=> 'btn-default btn-nav-link',
					'icon' => 'fa-cog',
					'name' => $this->language->get('button_configure'),
					'loading' => $this->language->get('text_opening'),
					'link' => $this->url->link('extension/dashboard/downloads', 'user_token=' . $this->session->data['user_token'], true)
				);
			} else {
				$data['dashboard_widget'] = array(
					'class'=> 'btn-success btn-install',
					'icon' => 'fa-magic',
					'name' => $this->language->get('button_install'),
					'loading' => $this->language->get('text_installing'),
					'link' => $this->url->link('extension/module/product_downloads/install_dashboard', 'user_token=' . $this->session->data['user_token'], true)
				);
			}
		} else {
			foreach (array_keys(self::$module_defaults) as $setting) {
				if (isset($this->request->post[$setting])) {
					$data[$setting] = $this->request->post[$setting];
				} else if (isset($module_info[$setting])) {
					$data[$setting] = $module_info[$setting];
				} else {
					$data[$setting] = self::$module_defaults[$setting];
					if (!isset($this->alert['warning']['unsaved']) && $this->checkVersion())  {
						$this->alert['warning']['unsaved'] = $this->language->get('error_unsaved_settings');
					}
				}
			}
			$data['module_id'] = $module_id;

			if (isset($this->request->post['downloads'])) {
				$data['m_downloads'] = $this->request->post['downloads'];
			} else if (isset($module_info['downloads'])) {
				$data['m_downloads'] = $module_info['downloads'];
			} else {
				$data['m_downloads'] = "";
			}

			$modules = $this->model_setting_module->getModulesByCode('product_downloads');

			$tab_position_used = 0;

			foreach ($modules as $idx => $module) {
				$module_settings = json_decode($module['setting'], true);
				if ((int)$module_settings['show_in_tab'] && $module_id != $module['module_id']) {
					$tab_position_used = 1;
					break;
				}
			}
			$data['tab_position_used'] = $tab_position_used;
		}

		if (isset($this->session->data['error'])) {
			$this->error = $this->session->data['error'];

			unset($this->session->data['error']);
		}

		if (isset($this->error['warning'])) {
			$this->alert['warning']['warning'] = $this->error['warning'];
		}

		if (isset($this->error['error'])) {
			$this->alert['error']['error'] = $this->error['error'];
		}

		if (isset($this->session->data['success'])) {
			$this->alert['success']['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		}

		$this->document->addStyle('view/javascript/summernote/summernote.css');
		$this->document->addStyle('view/stylesheet/pd/module.min.css?v=' . EXTENSION_VERSION);

		$this->document->addScript('view/javascript/summernote/summernote.js');
		$this->document->addScript('view/javascript/pd/highcharts/highcharts.js?v=' . EXTENSION_VERSION);
		$this->document->addScript('view/javascript/pd/module.min.js?v=' . EXTENSION_VERSION);

		$data['errors'] = $this->error;

		$data['user_token'] = $this->session->data['user_token'];

		$data['alerts'] = $this->alert;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if (is_null($module_id)) {
			$template = 'extension/module/product_downloads';
		} else {
			$template = 'extension/module/product_downloads_module';
		}

		$this->response->setOutput($this->load->view($template, $data));
	}

	public function install_dashboard() {
		$this->load->language('extension/module/product_downloads');

		$ajax_request = isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

		$response = array();

		if ($this->validateDashboardInstall()) {
			$this->load->model('setting/extension');

			$this->model_setting_extension->install('dashboard', 'downloads');

			$this->load->model('user/user_group');

			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/dashboard/downloads');
			$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/dashboard/downloads');

			// Call install method if it exsits
			$this->load->controller('extension/dashboard/downloads/install');

			$this->alert['success']['fix_db'] = $this->language->get('text_success_install_dashboard');
			//$response['success'] = true;
			$response['url'] = html_entity_decode($this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'], true));
		}

		$response = array_merge($response, array("errors" => $this->error), array("alerts" => $this->alert));

		if (!$ajax_request) {
			$this->session->data['errors'] = $this->error;
			$this->session->data['alerts'] = $this->alert;
			$this->response->redirect($this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'], true));
		} else {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($response, JSON_UNESCAPED_SLASHES));
		}
	}

	public function install() {
		$this->load->language('extension/module/product_downloads');
		$this->load->model('extension/module/product_downloads');

		$this->registerEventHooks();

		$this->model_extension_module_product_downloads->applyDatabaseChanges();

		// Add Downloads layout
		$this->load->model('design/layout');
		$this->load->model('setting/store');

		$stores = $this->model_setting_store->getStores();

		$layout_data = array();
		$layout_data["name"] = $this->language->get("text_downloads_layout");
		$layout_data["layout_route"] = array(0 => array(
			"store_id"  => 0,
			"route"     => "extension/module/download/download"
		));

		foreach ($stores as $store) {
			$layout_data["layout_route"][] = array(
				"store_id"  => $store["store_id"],
				"route"     => "extension/module/download/download"
			);
		}

		$this->model_design_layout->addLayout($layout_data);

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();
		$languages = array_remap_key_to_id('language_id', $languages);
		foreach ($languages as $language_id => $language) {
			self::$config_defaults['module_product_downloads_dp_seo_keywords'][$language_id] = 'downloads';
			self::$config_defaults['module_product_downloads_ds_seo_keywords'][$language_id] = 'samples';
		}

		$this->load->model('setting/setting');

		$this->model_setting_setting->editSetting('module_product_downloads', self::$config_defaults);
	}

	public function uninstall() {
		$this->load->language('extension/module/product_downloads');
		$this->load->model('extension/module/product_downloads');

		$this->removeEventHooks();

		if ($this->config->get("module_product_downloads_remove_sql_changes")) {
			$this->model_extension_module_product_downloads->revertDatabaseChanges();
		}

		// Remove Downloads layout
		$query = $this->db->query("SELECT layout_id FROM " . DB_PREFIX . "layout_route WHERE route = 'extension/module/download/download' LIMIT 1");
		if ($query->num_rows) {
			$this->load->model('design/layout');
			$this->model_design_layout->deleteLayout($query->row['layout_id']);
		}

		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_product_downloads');

		$this->load->model('setting/module');
		$this->model_setting_module->deleteModulesByCode('product_downloads');
	}

	public function upgrade() {
		$this->load->language('extension/module/product_downloads');
		$this->load->model('extension/module/product_downloads');

		$ajax_request = isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

		$response = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateUpgrade()) {
			$this->load->model('setting/setting');

			if ($this->model_extension_module_product_downloads->upgradeDatabaseStructure($this->installedVersion(), $this->alert)) {
				$settings = array();

				// Go over all settings, add new values and remove old ones
				foreach (self::$config_defaults as $setting => $default) {
					$value = $this->config->get($setting);
					if ($value === null) {
						$settings[$setting] = $default;
					} else {
						$settings[$setting] = $value;
					}
				}

				// Add Downloads layout if needed
				$query = $this->db->query("SELECT layout_id FROM " . DB_PREFIX . "layout_route WHERE route = 'extension/module/download/download' LIMIT 1");
				if (!$query->num_rows) {
					$this->load->model('design/layout');
					$this->load->model('setting/store');

					$stores = $this->model_setting_store->getStores();

					$layout_data = array();
					$layout_data["name"] = $this->language->get("text_downloads_layout");
					$layout_data["layout_route"] = array(0 => array(
						"store_id"  => 0,
						"route"     => "extension/module/download/download"
					));

					foreach ($stores as $store) {
						$layout_data["layout_route"][] = array(
							"store_id"  => $store["store_id"],
							"route"     => "extension/module/download/download"
						);
					}

					$this->model_design_layout->addLayout($layout_data);
				}

				$settings['module_product_downloads_installed_version'] = EXTENSION_VERSION;

				$this->model_setting_setting->editSetting('module_product_downloads', $settings);

				$this->session->data['success'] = sprintf($this->language->get('text_success_upgrade'), EXTENSION_VERSION);
				$this->alert['success']['upgrade'] = sprintf($this->language->get('text_success_upgrade'), EXTENSION_VERSION);

				$response['success'] = true;
				$response['reload'] = true;
			} else {
				$this->alert = array_merge($this->alert, $this->model_extension_module_product_downloads->getAlerts());
				$this->alert['error']['database_upgrade'] = $this->language->get('error_upgrade_database');
			}
		}

		$response = array_merge($response, array("errors" => $this->error), array("alerts" => $this->alert));

		if (!$ajax_request) {
			$this->session->data['errors'] = $this->error;
			$this->session->data['alerts'] = $this->alert;
			$this->response->redirect($this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'], true));
		} else {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($response, JSON_UNESCAPED_SLASHES));
			return;
		}
	}

	public function services() {
		$services = base64_decode($this->config->get('module_product_downloads_services'));
		$response = json_decode($services, true);
		$force = isset($this->request->get['force']) && (int)$this->request->get['force'];

		if ($response && isset($response['expires']) && $response['expires'] >= strtotime("now") && !$force) {
			$response['cached'] = true;
		} else {
			$url = "https://www.opencart.ee/services/?eid=" . EXTENSION_ID . "&info=true&general=true&currency=" . $this->config->get('config_currency');
			$hostname = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '' ;

			if (function_exists('curl_init')) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt($ch, CURLOPT_TIMEOUT, 60);
				curl_setopt($ch, CURLOPT_USERAGENT, base64_encode("curl " . EXTENSION_ID));
				curl_setopt($ch, CURLOPT_REFERER, $hostname);
				$json = curl_exec($ch);
			} else {
				$json = false;
			}

			if ($json !== false) {
				$this->load->model('setting/setting');
				$settings = $this->model_setting_setting->getSetting('module_product_downloads');
				$settings['module_product_downloads_services'] = base64_encode($json);
				$this->model_setting_setting->editSetting('module_product_downloads', $settings);
				$response = json_decode($json, true);
			} else {
				$response = array();
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response, JSON_UNESCAPED_SLASHES));
	}

	public function autocomplete() {
		$this->load->language('extension/module/product_downloads');

		if ($this->request->server['REQUEST_METHOD'] == 'GET' && isset($this->request->get['type'])) {
			$response = array();
			switch ($this->request->get['type']) {
				case 'tag':
					$create = isset($this->request->get['create']);
					$query = isset($this->request->get['query']) ? $this->request->get['query'] : '';
					$exact_match = false;

					$this->load->model('extension/module/download/tag');

					$results = array();

					if (isset($this->request->get['query'])) {
						if (is_array($this->request->get['query']) && isset($this->request->get['multiple'])) {
							$results = array();

							foreach ((array)$this->request->get['query'] as $value) {
								$result =  $this->model_extension_module_download_tag->getDownloadTag($value);

								if ($result) {
									$results[] = $result;
								}
							}
						} else {
							$filter_data = array(
								'filter'        => array('name' => $this->request->get['query']),
								'sort'          => 'dtd.name',
								'start'         => 0,
								'limit'         => 20,
							);

							$results = $this->model_extension_module_download_tag->getDownloadTags($filter_data);

							if (!$create && stripos($this->language->get('text_none'), $this->request->get['query']) !== false) {
								$response[] = array(
										'value'     => $this->language->get('text_none'),
										'tokens'    => explode(' ', trim(str_replace('---', '', $this->language->get('text_none')))),
										'id'        => '*',
									);
							}
						}
					} else {
						$results = $this->model_extension_module_download_tag->getDownloadTags(array());

						$response[] = array(
								'value'     => $this->language->get('text_none'),
								'tokens'    => array_merge(explode(' ', trim(str_replace('---', '', $this->language->get('text_none')))), (array)trim($this->language->get('text_none'))),
								'id'        => '*'
							);
					}

					foreach ($results as $result) {
						$result['name'] = html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8');
						if (utf8_strtoupper($query) == utf8_strtoupper($result['name'])) {
							$exact_match = true;
						}
						$response[] = array(
							'value'     => $result['name'],
							'tokens'    => explode(' ', $result['name']),
							'id'        => $result['download_tag_id'],
							'admin'     => (int)$result['administrative']
						);
					}

					if ($create && !$exact_match) {
						array_unshift($response, array(
							'value'     => $query,
							'tokens'    => explode(' ', $query),
							'id'        => 0,
							'admin'     => 0
						));
					}
					break;
				case 'download':
					$this->load->model('extension/module/download/download');

					$results = array();

					if (isset($this->request->get['query'])) {
						if (is_array($this->request->get['query']) && isset($this->request->get['multiple'])) {
							$results = array();

							foreach ((array)$this->request->get['query'] as $value) {
								$result =  $this->model_extension_module_download_download->getDownload($value);
								if ($result) {
									$results[] = $result;
								}
							}
						} else {
							$filter_data = array(
								'filter'        => array('name' => $this->request->get['query']),
								'sort'          => 'dd.name',
								'start'         => 0,
								'limit'         => 20,
							);

							$results = $this->model_extension_module_download_download->getDownloads($filter_data);
						}
					} else {
						$results = $this->model_extension_module_download_download->getDownloads(array());
					}

					foreach ($results as $result) {
						$result['name'] = html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8');
						$response[] = array(
							'value'     => $result['name'],
							'tokens'    => explode(' ', $result['name']),
							'id'        => $result['download_id'],
							'free'      => ((int)$result['is_free']) ? utf8_strtolower($this->language->get('text_free')) : ''
						);
					}
					break;
				case 'downloads':
					$this->load->model('extension/module/download/download');

					$results = array();

					if (isset($this->request->get['query'])) {
						$filter_data = array(
							'filter'        => array('name' => $this->request->get['query']),
							'sort'          => 'dd.name',
							'start'         => 0,
							'limit'         => 20,
						);

						$results = $this->model_extension_module_download_download->getDownloads($filter_data);

						if (stripos($this->language->get('text_none'), $this->request->get['query']) !== false) {
							$response[] = array(
									'value'     => $this->language->get('text_none'),
									'tokens'    => explode(' ', trim(str_replace('---', '', $this->language->get('text_none')))),
									'id'        => '*',
								);
						}
					} else {
						$results = $this->model_extension_module_download_download->getDownloads(array());

						$response[] = array(
								'value'     => $this->language->get('text_none'),
								'tokens'    => array_merge(explode(' ', trim(str_replace('---', '', $this->language->get('text_none')))), (array)trim($this->language->get('text_none'))),
								'id'        => '*'
							);
					}

					foreach ($results as $result) {
						$result['name'] = html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8');
						$response[] = array(
							'value'     => $result['name'],
							'tokens'    => explode(' ', $result['name']),
							'id'        => $result['download_id']
						);
					}
					break;
				case 'customer':
					$this->load->model('customer/customer');

					$results = array();

					if (isset($this->request->get['query'])) {
						$filter_data = array(
							'filter_name'   => $this->request->get['query'],
							'sort'          => 'name',
							'start'         => 0,
							'limit'         => 20,
						);

						$results = $this->model_customer_customer->getCustomers($filter_data);
					} else {
						$results = $this->model_customer_customer->getCustomers(array());
					}

					foreach ($results as $result) {
						$result['name'] = html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8');
						$response[] = array(
							'value'     => $result['name'],
							'tokens'    => array_merge(explode(' ', $result['name']), array($result['email'])),
							'id'        => $result['customer_id'],
							'email'     => $result['email']
						);
					}
					break;
				case 'product':
					$this->load->model('extension/module/download/download');

					$results = array();

					if (isset($this->request->get['query'])) {
						$filter_data = array(
							'filter_name'   => $this->request->get['query'],
							'filter_model'  => $this->request->get['query'],
							'sort'          => 'pd.name',
							'start'         => 0,
							'limit'         => 20,
						);

						$results = $this->model_extension_module_download_download->getProducts($filter_data);
					} else {
						$results = $this->model_extension_module_download_download->getProducts(array());
					}

					foreach ($results as $result) {
						$result['name'] = html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8');
						$response[] = array(
							'value'     => $result['name'],
							'tokens'    => explode(' ', $result['name']),
							'id'        => $result['product_id'],
							'model'     => $result['model']
						);
					}
					break;
				default:
					break;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response, JSON_UNESCAPED_SLASHES));
	}

	public function statistics() {
		$this->load->language('extension/module/product_downloads');
		$this->load->model('extension/module/product_downloads');

		$response = array();

		$ajax_request = isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

		if (!$ajax_request) {
			$this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
			return;
		}

		if ($this->request->server['REQUEST_METHOD'] == 'GET' && isset($this->request->get['type']) && in_array($this->request->get['type'], array('daily', 'weekly', 'monthly', 'yearly', 'today_top', 'yesterday_top', 'week_top', 'month_top', 'year_top', 'most_downloaded'))) {
			$this->load->model('extension/module/product_downloads');

			$type = $this->request->get['type'];

			$response['categories'] = array();
			$response['series'] = array();
			$response['temporal'] = false;
			$response['title'] = $this->language->get("text_{$type}_downloads");

			$stats = $this->model_extension_module_product_downloads->getDownloadStats($type);
			$last_date = null;

			foreach ($stats as $key => $value) {
				switch ($type) {
					case 'daily':
						$response['temporal'] = true;
						$date = new DateTime();
						$date->setDate($value['year'], $value['month'], $value['day']);
						// if ($value['month'] == 1 && $value['day'] == 1) {
						//     $response['categories'][] = $date->format("M d, Y");
						// } else if ($value['day'] == 1 || !$key) {
						//     $response['categories'][] = $date->format("M d");
						// } else {
						//     $response['categories'][] = $date->format("d");
						// }
						if ($last_date) {
							$interval = new DateInterval('P1D'); // 1 day interval
							$begin = $last_date;
							$begin->modify('+1 day');
							$end = $date;
							$daterange = new DatePeriod($begin, $interval ,$end);

							foreach($daterange as $d) {
								$response['series'][] = array("date" => $d->format("Y-m-d"), "count" => "0");
							}
						}
						$response['series'][] = array("date" => $date->format("Y-m-d"), "count" => $value['count']);
						$last_date = $date;
						break;
					case 'weekly':
						$response['temporal'] = true;
						$week = (int)$value['week'];
						$date1 = new DateTime();
						//$date2 = new DateTime();
						$date1->setISODate($value['year'], $week, 1);
						// $start = $date1->format("M d");
						// $date2->setISODate((int)$value['year'], $week, 7);
						// if ($week == 1) {
						//     $end = $date2->format("M d, Y");
						// } else {
						//     $end = $date2->format("M d");
						// }
						// $response['categories'][] = "W$week: $start - $end";
						if ($last_date) {
							$interval = new DateInterval('P1W'); // 7 days interval
							$begin = $last_date;
							$begin->modify('+1 week');
							$end = $date1;
							$daterange = new DatePeriod($begin, $interval ,$end);

							foreach($daterange as $d) {
								$response['series'][] = array("date" => $d->format("Y-m-d"), "count" => "0");
							}
						}
						$response['series'][] = array("date" => $date1->format("Y-m-d"), "count" => $value['count']);
						$last_date = $date1;
						break;
					case 'monthly':
						$response['temporal'] = true;
						$date = new DateTime();
						$date->setDate($value['year'], $value['month'], 1);
						// if ($value['month'] == 1 || !$key) {
						//     $response['categories'][] = $date->format("Y M");
						// } else {
						//     $response['categories'][] = $date->format("M");
						// }
						if ($last_date) {
							$interval = new DateInterval('P1M'); // 1 month interval
							$begin = $last_date;
							$begin->modify('+1 month');
							$end = $date;
							$daterange = new DatePeriod($begin, $interval ,$end);

							foreach($daterange as $d) {
								$response['series'][] = array("date" => $d->format("Y-m-d"), "count" => "0");
							}
						}
						$response['series'][] = array("date" => $date->format("Y-m-d"), "count" => $value['count']);
						$last_date = $date;
						break;
					case 'yearly':
						$response['temporal'] = true;
						$date = new DateTime();
						$date->setDate($value['year'], 1, 1);
						if ($last_date) {
							$interval = new DateInterval('P1Y'); // 1 year interval
							$begin = $last_date;
							$begin->modify('+1 year');
							$end = $date;
							$daterange = new DatePeriod($begin, $interval ,$end);

							foreach($daterange as $d) {
								$response['series'][] = array("date" => $d->format("Y-m-d"), "count" => "0");
							}
						}
						// $response['categories'][] = $value['year'];
						$response['series'][] = array("date" => $date->format("Y-m-d"), "count" => $value['count']);
						$last_date = $date;
						break;
					case 'today_top':
					case 'yesterday_top':
					case 'week_top':
					case 'month_top':
					case 'year_top':
					case 'most_downloaded':
						$response['categories'][] = html_entity_decode($value['name'], ENT_QUOTES, 'UTF-8');
						$response['series'][] = $value['count'];
						break;
					default:
						break;
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response, JSON_UNESCAPED_SLASHES));
	}

	public function fix_db() {
		$this->load->language('extension/module/product_downloads');
		$this->load->model('extension/module/product_downloads');

		$ajax_request = isset($this->request->server['HTTP_X_REQUESTED_WITH']) && !empty($this->request->server['HTTP_X_REQUESTED_WITH']) && strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

		$response = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateFix()) {
			$this->model_extension_module_product_downloads->applyDatabaseChanges();
			if ($this->model_extension_module_product_downloads->fixDatabaseConsistency($this->alert)) {
				$this->alert['success']['fix_db'] = $this->language->get('text_success_fix_db');
				$response['success'] = true;
			} else {
				$this->alert = array_merge($this->alert, $this->model_extension_module_product_downloads->getAlerts());
			}
		}

		$response = array_merge($response, array("errors" => $this->error), array("alerts" => $this->alert));

		if (!$ajax_request) {
			$this->session->data['errors'] = $this->error;
			$this->session->data['alerts'] = $this->alert;
			$this->response->redirect($this->url->link('extension/module/product_downloads', 'user_token=' . $this->session->data['user_token'], true));
		} else {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($response, JSON_UNESCAPED_SLASHES));
		}
	}

	// Catalog > Downloads > Downloads
	public function view_downloads() {
		return $this->load->controller('extension/module/download/download');
	}

	public function delete_downloads() {
		return $this->load->controller('extension/module/download/download/delete');
	}

	public function copy_downloads() {
		return $this->load->controller('extension/module/download/download/copy');
	}

	public function add_download() {
		return $this->load->controller('extension/module/download/download/add');
	}

	public function edit_download() {
		return $this->load->controller('extension/module/download/download/edit');
	}

	public function change_download_type() {
		return $this->load->controller('extension/module/download/download/change_type');
	}

	public function reset_download_stats() {
		return $this->load->controller('extension/module/download/download/reset_download_stats');
	}

	public function get_auto_add_file_list() {
		return $this->load->controller('extension/module/download/download/get_auto_add_file_list');
	}

	public function get_file_list() {
		return $this->load->controller('extension/module/download/download/get_file_list');
	}

	public function get_directory_list() {
		return $this->load->controller('extension/module/download/download/get_directory_list');
	}

	public function check_download_link() {
		return $this->load->controller('extension/module/download/download/check_download_link');
	}

	public function upload() {
		return $this->load->controller('extension/module/download/download/upload');
	}

	public function auto_add_downloads() {
		return $this->load->controller('extension/module/download/download/auto_add');
	}

	public function batch_add_downloads() {
		return $this->load->controller('extension/module/download/download/batch_add');
	}

	// Catalog > Downloads > Samples
	public function view_samples() {
		return $this->load->controller('extension/module/download/sample');
	}

	public function delete_samples() {
		return $this->load->controller('extension/module/download/sample/delete');
	}

	public function copy_samples() {
		return $this->load->controller('extension/module/download/sample/copy');
	}

	public function add_sample() {
		return $this->load->controller('extension/module/download/sample/add');
	}

	public function edit_sample() {
		return $this->load->controller('extension/module/download/sample/edit');
	}

	public function send_sample() {
		return $this->load->controller('extension/module/download/sample/send');
	}

	// Catalog > Downloads > Tags
	public function view_tags() {
		return $this->load->controller('extension/module/download/tag');
	}

	public function delete_tags() {
		return $this->load->controller('extension/module/download/tag/delete');
	}

	public function copy_tags() {
		return $this->load->controller('extension/module/download/tag/copy');
	}

	public function add_tag() {
		return $this->load->controller('extension/module/download/tag/add');
	}

	public function edit_tag() {
		return $this->load->controller('extension/module/download/tag/edit');
	}

	// Event hooks
	public function language_add_hook($route='', $data=array(), $output=null) {
		$language_id = (int)$output;

		if ($language_id && $this->config->get('module_product_downloads_installed')) {
			$this->load->model('setting/module');

			$pd_modules = $this->model_setting_module->getModulesByCode('product_downloads');

			foreach((array)$pd_modules as $module) {
				$module_settings = json_decode($module['setting'], true);

				if (isset($module_settings['names'][$this->config->get('config_language_id')])) {
					$module_settings['names'][$language_id] = $module_settings['names'][$this->config->get('config_language_id')];
				} else {
					$module_settings['names'][$language_id] = '';
				}

				$module_settings['name'] = $module['name'];

				$this->model_setting_module->editModule($module['module_id'], $module_settings);
			}

			// Download Tags
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "download_tag_description WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'");

			foreach ($query->rows as $download_tag) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "download_tag_description SET download_tag_id = '" . (int)$download_tag['download_tag_id'] . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($download_tag['name']) . "'");
			}
		}
	}

	public function language_delete_hook($route='', $data=array(), $output=null) {
		if (is_array($data) && !empty($data[0])) {
			$language_id = (int)$data[0];
		} else {
			$language_id = (int)$data;
		}

		if ($language_id && $this->config->get('module_product_downloads_installed')) {
			$this->load->model('setting/module');

			$pd_modules = $this->model_setting_module->getModulesByCode('product_downloads');

			foreach((array)$pd_modules as $module) {
				$module_settings = json_decode($module['setting'], true);

				unset($module_settings['names'][$language_id]);

				$module_settings['name'] = $module['name'];

				$this->model_setting_module->editModule($module['module_id'], $module_settings);
			}

			// Download Tags
			$this->db->query("DELETE FROM " . DB_PREFIX . "download_tag_description WHERE language_id = '" . (int)$language_id . "'");
		}
	}

	public function order_delete_hook($route='', $data=array(), $output=null) {
		if (is_array($data) && !empty($data[0])) {
			$order_id = (int)$data[0];
		} else {
			$order_id = (int)$data;
		}

		if ($order_id) {
			$this->load->model('extension/module/product_downloads');
			$this->model_extension_module_product_downloads->deleteOrderDownloads($order_id);
		} else {
			$this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
		}
	}

	public function menu_hook(&$route, &$data) {
		if ($this->config->get('module_product_downloads_status') && $this->user->hasPermission('access', 'extension/module/product_downloads')) {
			$this->load->language('extension/module/product_downloads');
			foreach ($data['menus'] as $l1_key => $l1_menu) {
				if ($l1_menu['id'] == 'menu-catalog') {
					foreach ($l1_menu['children'] as $l2_key => $l2_menu) {
						if (strpos($l2_menu['href'], "route=catalog/download&") !== FALSE) {
							$downloads = array();

							$downloads[] = array(
								'name'     => $this->language->get('menu_download'),
								'href'     => $this->url->link('extension/module/product_downloads/view_downloads', 'user_token=' . $this->session->data['user_token'], true),
								'children' => array()
							);

							$downloads[] = array(
								'name'     => $this->language->get('menu_download_tag'),
								'href'     => $this->url->link('extension/module/product_downloads/view_tags', 'user_token=' . $this->session->data['user_token'], true),
								'children' => array()
							);

							$downloads[] = array(
								'name'     => $this->language->get('menu_download_sample'),
								'href'     => $this->url->link('extension/module/product_downloads/view_samples', 'user_token=' . $this->session->data['user_token'], true),
								'children' => array()
							);

							$l1_menu['children'][$l2_key] = array(
								'name'      => $this->language->get('menu_download'),
								'href'      => '',
								'children'  => $downloads
							);

							$data['menus'][$l1_key]['children'] = $l1_menu['children'];
						}
					}
				}
			}
		}
	}

	public function option_form_hook(&$route, &$data) {
		if ($this->config->get('module_product_downloads_status')) {
			$this->load->language('extension/module/product_downloads');
			$data['pd_status'] = $this->config->get('module_product_downloads_status');
			$data['text_autocomplete'] = $this->language->get('text_autocomplete');
			$data['text_remove_download'] = $this->language->get('text_remove_download');
			$data['entry_download'] = $this->language->get('entry_download');
			$data['download_autocomplete'] = html_entity_decode($this->url->link('extension/module/product_downloads/autocomplete', 'user_token=' . $this->session->data['user_token'] . '&type=download&query=', true));
		}
	}

	protected function showErrorPage($data = array()) {
		$this->document->addStyle('view/stylesheet/pd/module.min.css?v=' . EXTENSION_VERSION);

		$data['alerts'] = $this->alert;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$template = 'extension/module/product_downloads_error';

		$this->response->setOutput($this->load->view($template, $data));
	}

	// Private methods
	private function registerEventHooks() {
		$this->load->model('setting/event');

		if (isset($this->model_extension_module_product_downloads->getEventByCodeTriggerAction) && is_callable($this->model_extension_module_product_downloads->getEventByCodeTriggerAction)) {
			foreach (self::$event_hooks as $code => $hook) {
				$event = $this->model_extension_module_product_downloads->getEventByCodeTriggerAction($code, $hook['trigger'], $hook['action']);

				if (!$event) {
					$this->model_setting_event->addEvent($code, $hook['trigger'], $hook['action']);
				}
			}
		} else {
			$this->alert['warning']['ocmod'] = $this->language->get('error_ocmod_script');
		}
	}

	private function removeEventHooks() {
		$this->load->model('setting/event');

		foreach (self::$event_hooks as $code => $hook) {
			$this->model_setting_event->deleteEventByCode($code);
		}
	}

	private function updateEventHooks() {
		$this->load->model('setting/event');

		if (isset($this->model_extension_module_product_downloads->getEventByCodeTriggerAction) && is_callable($this->model_extension_module_product_downloads->getEventByCodeTriggerAction)) {
			foreach (self::$event_hooks as $code => $hook) {
				$event = $this->model_extension_module_product_downloads->getEventByCodeTriggerAction($code, $hook['trigger'], $hook['action']);

				if (!$event) {
					$this->model_setting_event->addEvent($code, $hook['trigger'], $hook['action']);

					if (empty($this->alert['success']['hooks_updated'])) {
						$this->alert['success']['hooks_updated'] = $this->language->get('text_success_hooks_update');
					}
				}
			}

			// Delete old triggers
			$query = $this->db->query("SELECT `code` FROM " . DB_PREFIX . "event WHERE `code` LIKE '%_module_product_downloads_%'");
			$events = array_keys(self::$event_hooks);

			foreach ($query->rows as $row) {
				if (!in_array($row['code'], $events)) {
					$this->model_setting_event->deleteEventByCode($row['code']);

					if (empty($this->alert['success']['hooks_updated'])) {
						$this->alert['success']['hooks_updated'] = $this->language->get('text_success_hooks_update');
					}
				}
			}
		} else {
			$this->alert['warning']['ocmod'] = $this->language->get('error_ocmod_script');
		}
	}

	protected function checkPrerequisites() {
		$errors = false;

		$this->load->language('extension/module/product_downloads', 'pd');

		if (!$this->config->get('pd_pro_ocmod_script_working')) {
			$errors = true;
			$this->alert['error']['ocmod'] = $this->language->get('pd')->get('error_ocmod_script');
		} else if ($this->checkVersion() && $this->installedVersion() != $this->config->get('pd_pro_version')) {
			$this->alert['warning']['ocmod_cache'] = sprintf($this->language->get('pd')->get('error_ocmod_cache'), $this->url->link('marketplace/modification/refresh', 'user_token=' . $this->session->data['user_token'], true));
		}

		if (version_compare(phpversion(), EXTENSION_MIN_PHP_VERSION) < 0) {
			$errors = true;
			$this->alert['error']['php'] = sprintf($this->language->get('pd')->get('error_php_version'), phpversion(), EXTENSION_MIN_PHP_VERSION);
		}

		return !$errors;
	}

	protected function checkVersion($display_error = false) {
		$errors = false;

		$installed_version = $this->installedVersion();

		if ($installed_version != EXTENSION_VERSION) {
			$errors = true;

			if ($display_error) {
				$this->alert['info']['version'] = sprintf($this->language->get('error_version'), EXTENSION_VERSION);
			}
		}

		return !$errors;
	}

	private function checkModulePermission() {
		$errors = false;

		if (!$this->user->hasPermission('modify', 'extension/module/product_downloads')) {
			$errors = true;
			$this->alert['error']['permission'] = $this->language->get('error_permission');
		}

		return $errors;
	}

	private function validateFix() {
		$errors = false;

		if (!$this->user->hasPermission('modify', 'extension/module/product_downloads')) {
			$errors = true;
			$this->alert['error']['permission'] = $this->language->get('error_permission');
		}

		if (!$errors) {
			return $this->checkVersion() && $this->checkPrerequisites();
		} else {
			return false;
		}
	}

	private function validateDashboardInstall() {
		$errors = false;

		if (!$this->user->hasPermission('modify', 'extension/extension/dashboard')) {
			$errors = true;
			$this->alert['error']['permission'] = $this->language->get('error_dashboard_permission');
		}

		if (!$errors) {
			return true;
		} else {
			return false;
		}
	}

	private function validate() {
		$errors = $this->checkModulePermission();

		if (!$errors) {
			$result = $this->checkPrerequisites() && $this->checkVersion(true) && $this->model_extension_module_product_downloads->checkDatabaseStructure($this->alert);
			$this->alert = array_merge($this->alert, $this->model_extension_module_product_downloads->getAlerts());
			return $result;
		} else {
			return false;
		}
	}

	private function validateUpgrade() {
		$errors = $this->checkModulePermission();

		return !$errors;
	}

	private function validateForm(&$data) {
		$errors = false;

		$data["module_product_downloads_aa_file_types"] = isset($data["module_product_downloads_aa_file_types"]) ? format_file_types($data["module_product_downloads_aa_file_types"]) : "";
		$data["module_product_downloads_ba_file_types"] = isset($data["module_product_downloads_ba_file_types"]) ? format_file_types($data["module_product_downloads_ba_file_types"]) : "";

		if (!(int)$data["module_product_downloads_aa_all_types"] && !$data["module_product_downloads_aa_file_types"]) {
			$errors = true;
			$this->error['aa_file_types'] = $this->language->get('error_filetype');
		}

		if (!(int)$data["module_product_downloads_ba_all_types"] && !$data["module_product_downloads_ba_file_types"]) {
			$errors = true;
			$this->error['ba_file_types'] = $this->language->get('error_filetype');
		}

		$data["module_product_downloads_aa_excludes"] = isset($data["module_product_downloads_aa_excludes"]) ? format_excludes($data["module_product_downloads_aa_excludes"]) : "";
		$data["module_product_downloads_ba_excludes"] = isset($data["module_product_downloads_ba_excludes"]) ? format_excludes($data["module_product_downloads_ba_excludes"]) : "";

		if (!isset($data['module_product_downloads_customer_groups']) || !is_array($data['module_product_downloads_customer_groups'])) {
			$data['module_product_downloads_customer_groups'] = array();
		}

		if (isset($data['modules'])) {
			foreach ((array)$data['modules'] as $idx => $module) {
				if (isset($module['names'])) {
					foreach ((array)$module['names'] as $language_id => $value) {
						if (!utf8_strlen($value)) {
							$errors = true;
							$this->error['modules'][$idx]['names'][$language_id]['name'] = $this->language->get('error_module_name');
						}
					}
				} else {
					$errors = true;
				}

				if ((int)$module['downloads_per_page'] < 0 || (string)((int)$module['downloads_per_page']) != $module['downloads_per_page']) {
					$errors = true;
					$this->error['modules'][$idx]['downloads_per_page'] = $this->language->get('error_positive_integer');
				}
			}
		}

		if ((int)$data['module_product_downloads_aa_duration'] < 0 || (string)((int)$data['module_product_downloads_aa_duration']) != $data['module_product_downloads_aa_duration']) {
			$errors = true;
			$this->error['aa_duration'] = $this->language->get('error_positive_integer');
		}

		if ((int)$data['module_product_downloads_aa_total_downloads'] < -1 || (string)((int)$data['module_product_downloads_aa_total_downloads']) != $data['module_product_downloads_aa_total_downloads']) {
			$errors = true;
			$this->error['aa_total_downloads'] = $this->language->get('error_integer');
		}

		if ((int)$data['module_product_downloads_ba_duration'] < 0 || (string)((int)$data['module_product_downloads_ba_duration']) != $data['module_product_downloads_ba_duration']) {
			$errors = true;
			$this->error['ba_duration'] = $this->language->get('error_positive_integer');
		}

		if ((int)$data['module_product_downloads_ba_total_downloads'] < -1 || (string)((int)$data['module_product_downloads_ba_total_downloads']) != $data['module_product_downloads_ba_total_downloads']) {
			$errors = true;
			$this->error['ba_total_downloads'] = $this->language->get('error_integer');
		}

		if (!file_exists($data['module_product_downloads_aa_directory']) || !is_dir($data['module_product_downloads_aa_directory']) ) {
			$errors = true;
			$this->error['aa_directory'] = $this->language->get('error_directory');
		}

		if (!file_exists($data['module_product_downloads_ba_directory']) || !is_dir($data['module_product_downloads_ba_directory']) ) {
			$errors = true;
			$this->error['ba_directory'] = $this->language->get('error_directory');
		}

		if (!isset($data["module_product_downloads_aa_file_tags"])) {
			$data["module_product_downloads_aa_file_tags"] = "";
		}

		if ($data["module_product_downloads_aa_file_tags"]) {
			$tags = array_map("trim", explode(",", $data["module_product_downloads_aa_file_tags"]));
			foreach ($tags as $tag) {
				if (utf8_strlen($tag) < 2 || utf8_strlen($tag) > 64) {
					$errors = true;
					$this->error['aa_file_tags'] = $this->language->get('error_tag_name');
					break;
				}
			}
		}

		if (!isset($data["module_product_downloads_ba_file_tags"])) {
			$data["module_product_downloads_ba_file_tags"] = "";
		}

		if ($data["module_product_downloads_ba_file_tags"]) {
			$tags = array_map("trim", explode(",", $data["module_product_downloads_ba_file_tags"]));
			foreach ($tags as $tag) {
				if (utf8_strlen($tag) < 2 || utf8_strlen($tag) > 64) {
					$errors = true;
					$this->error['ba_file_tags'] = $this->language->get('error_tag_name');
					break;
				}
			}
		}

		if ($this->config->get('config_seo_url') && !(int)$data['module_product_downloads_ds_disable_seo_url']) {
			foreach ((array)$data['module_product_downloads_ds_seo_keywords'] as $language_id => $value) {
				if (utf8_strlen(trim($value)) == 0) {
					$errors = true;
					$this->error['ds_seo_keyword'][$language_id]['value'] = $this->language->get('error_seo_keyword');
				}
			}
		}

		if ($data['module_product_downloads_dp_status']) {
			if ($this->config->get('config_seo_url') && !(int)$data['module_product_downloads_dp_disable_seo_url']) {
				foreach ((array)$data['module_product_downloads_dp_seo_keywords'] as $language_id => $value) {
					if (utf8_strlen(trim($value)) == 0) {
						$errors = true;
						$this->error['dp_seo_keyword'][$language_id]['value'] = $this->language->get('error_seo_keyword');
					}
				}
			}

			if ((int)$data['module_product_downloads_dp_downloads_per_page'] < 0 || (string)((int)$data['module_product_downloads_dp_downloads_per_page']) != $data['module_product_downloads_dp_downloads_per_page']) {
				$errors = true;
				$this->error['dp_downloads_per_page'] = $this->language->get('error_positive_integer');
			}
		}

		if ($errors) {
			$this->alert['warning']['warning'] = $this->language->get('error_warning');
		}

		if (!$errors) {
			return $this->validate();
		} else {
			return false;
		}
	}

	private function validateModuleForm(&$data) {
		$errors = false;

		if (isset($data['names'])) {
			foreach ((array)$data['names'] as $language_id => $value) {
				if (!utf8_strlen($value)) {
					$errors = true;
					$this->error['names'][$language_id]['name'] = $this->language->get('error_module_name');
				}
			}
		} else {
			$errors = true;
		}

		if ((int)$data['downloads_per_page'] < 0 || (string)((int)$data['downloads_per_page']) != $data['downloads_per_page']) {
			$errors = true;
			$this->error['downloads_per_page'] = $this->language->get('error_positive_integer');
		}

		if ($errors) {
			$this->alert['warning']['warning'] = $this->language->get('error_warning');
		}

		if (!$errors) {
			return $this->validate();
		} else {
			return false;
		}
	}

	private function processTags($tags) {
		$this->load->model('localisation/language');
		$this->load->model('extension/module/download/tag');

		$languages = $this->model_localisation_language->getLanguages();

		$tags = array_map("trim", explode(",", $tags));
		$processed_tags = array();

		foreach ($tags as $tag) {
			if ($tag) {
				$_tag = $this->model_extension_module_download_tag->getDownloadTagByName($tag);

				if (is_null($_tag)) {
					// This tag does not exist, let's add it to the database
					$descriptions = array();
					foreach ($languages as $language) {
						$descriptions[$language["language_id"]] = array("name" => $tag);
					}

					$tag_data = array(
						"sort_order" => 0,
						"administrative" => 0,
						"descriptions" => $descriptions
					);

					$this->model_extension_module_download_tag->addDownloadTag($tag_data);

					if (!in_array($tag, $processed_tags)) {
						$processed_tags[] = $tag;
					}
				} else {
					if (!in_array($_tag["name"], $processed_tags)) {
						$processed_tags[] = $_tag["name"];
					}
				}
			}
		}

		return implode(",", $processed_tags);
	}

	protected function installedVersion() {
		$installed_version = $this->config->get('module_product_downloads_installed_version');
		return $installed_version ? $installed_version : '6.0.0';
	}
}

/**
  * Return file extension and FontAwesome icon mapping
  *
  **/
if (!function_exists("get_fa_icons")) {
	function get_fa_icons() {
		$fa_icons = array(
			"unknown"   => "fa-file-o",
			// audio
			"mp3"       => "fa-file-audio-o",
			"ogg"       => "fa-file-audio-o",
			"wav"       => "fa-file-audio-o",
			"flac"      => "fa-file-audio-o",
			"midi"      => "fa-file-audio-o",
			// video
			"avi"       => "fa-file-video-o",
			"mkv"       => "fa-file-video-o",
			"mov"       => "fa-file-video-o",
			"mp4"       => "fa-file-video-o",
			// image
			"bmp"       => "fa-file-image-o",
			"tif"       => "fa-file-image-o",
			"jpg"       => "fa-file-image-o",
			"png"       => "fa-file-image-o",
			"gif"       => "fa-file-image-o",
			// archive
			"zip"       => "fa-file-archive-o",
			"rar"       => "fa-file-archive-o",
			"7z"        => "fa-file-archive-o",
			"tgz"       => "fa-file-archive-o",
			"tar"       => "fa-file-archive-o",
			// code
			"js"        => "fa-file-code-o",
			"c"         => "fa-file-code-o",
			"h"         => "fa-file-code-o",
			"cpp"       => "fa-file-code-o",
			"cs"        => "fa-file-code-o",
			"php"       => "fa-file-code-o",
			"html"      => "fa-file-code-o",
			"xml"       => "fa-file-code-o",
			// other
			"txt"       => "fa-file-text-o",
			"pdf"       => "fa-file-pdf-o",
			"ods"       => "fa-file-excel-o",
			"csv"       => "fa-file-excel-o",
			"xls"       => "fa-file-excel-o",
			"xlsx"      => "fa-file-excel-o",
			"odt"       => "fa-file-word-o",
			"doc"       => "fa-file-word-o",
			"docx"      => "fa-file-word-o",
			"odp"       => "fa-file-powerpoint-o",
			"ppt"       => "fa-file-powerpoint-o",
			"pptx"      => "fa-file-powerpoint-o",
		);
		ksort($fa_icons);
		return $fa_icons;
	}
}

/**
  * Remaps an array keys to SQL id fields
  *
  **/
if (!function_exists("array_remap_key_to_id")) {
	function array_remap_key_to_id($key, $results) {
		$new_array = array();

		foreach ($results as $result) {
			if (isset($result[$key])) {
				$new_array[$result[$key]] = $result;
			}
		}

		return $new_array;
	}
}

if (!function_exists("format_file_types")) {
	function format_file_types($types) {
		$file_types = array_map("trim", explode(",", $types));
		$fts = array();
		foreach ($file_types as $ft) {
			if ($ft) {
				$t = utf8_strtolower($ft);
				if (strpos($t, ".") === 0)
					$t = substr($t, 1);
				$fts[] = $t;
			}
		}
		return implode(",", $fts);
	}
}

if (!function_exists("format_excludes")) {
	function format_excludes($excluded) {
		$excluded = array_map("trim", explode(",", $excluded));
		$excl = array();
		foreach ($excluded as $ex) {
			if ($ex) {
				$ex = utf8_strtolower($ex);
				$excl[] = $ex;
			}
		}
		return implode(",", $excl);
	}
}

/**
  * Sort columns by index key
  *
  **/
if (!function_exists("column_sort")) {
	function column_sort($a, $b) {
		if ($a['index'] == $b['index']) {
			return 0;
		}
		return ($a['index'] < $b['index']) ? -1 : 1;
	}
}

/**
  * Filter columns by display value
  *
  **/
if (!function_exists("column_display")) {
	function column_display($a) {
		return (isset($a['display'])) ? (int)$a['display'] : false;
	}
}
