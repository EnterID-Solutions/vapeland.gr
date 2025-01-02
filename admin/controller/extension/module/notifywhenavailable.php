<?php

use \vendor\isenselabs\notifywhenavailable\config as Config;

class ControllerExtensionModuleNotifyWhenAvailable extends Controller
{
    // Module Unifier
    private $extensionsLink;
    private $moduleModel;
    private $data = array();
    private $error = array();
    // Module Unifier
    
    public function __construct($registry)
    {
        parent::__construct($registry);

        /* OC version-specific declarations - Begin */
        $this->extensionsLink  = $this->url->link(Config::notifywhenavailable_extensions_link, Config::notifywhenavailable_token_string . '=' . $this->session->data[Config::notifywhenavailable_token_string] . Config::notifywhenavailable_extensions_link_params, 'SSL');

        /* OC version-specific declarations - End */

        /* Module-specific declarations - Begin */
        $this->load->language(Config::notifywhenavailable_path);
        $this->load->model(Config::notifywhenavailable_path);
        $this->moduleModel   = $this->{Config::notifywhenavailable_model_call};
        /* Module-specific declarations - End */
        
        // Multi-Store
        $this->load->model('setting/store');
        // Settings
        $this->load->model('setting/setting');
        // Multi-Lingual
        $this->load->model('localisation/language');
        
        // Variables
        $this->data['modulePath'] = Config::notifywhenavailable_path;
        $this->data['moduleName'] = Config::notifywhenavailable_name;
        $this->data['moduleNameSmall'] = Config::notifywhenavailable_name_small;
        $this->data['moduleModel'] = $this->moduleModel;
        $this->data['tokenString'] = Config::notifywhenavailable_token_string;
        /* Module-specific loaders - End */

        /* Specific models required for notifywhenavailable */
        $this->load->model('catalog/product');

        // Update if route not module install/ uninstall
        if (strpos($this->request->get['route'], 'extension/extension/module/') === false) {
            $this->update();
        }
    }
    
    public function index()
    {
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('view/javascript/notifywhenavailable/cron.js');
        
        /* OpenCart resources */
        
        $this->document->addScript('view/javascript/summernote/summernote.js');
        $this->document->addScript('view/javascript/summernote/opencart.js');
        $this->document->addStyle('view/javascript/summernote/summernote.css');
        
        /* OpenCart resources */
        
        $this->document->addStyle('view/stylesheet/notifywhenavailable.css');
        
        if (!isset($this->request->get['store_id'])) {
            $this->request->get['store_id'] = 0;
        }
        
        $catalogURL = $this->getCatalogURL();
        $store      = $this->getCurrentStore($this->request->get['store_id']);
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            
            if (!empty($this->request->post['OaXRyb1BhY2sgLSBDb21'])) {
                $this->request->post['notifywhenavailable']['LicensedOn'] = $this->request->post['OaXRyb1BhY2sgLSBDb21'];
            }
            
            if (!empty($this->request->post['cHRpbWl6YXRpb24ef4fe'])) {
                $this->request->post['notifywhenavailable']['License'] = json_decode(base64_decode($this->request->post['cHRpbWl6YXRpb24ef4fe']), true);
            }
            
            $store = $this->getCurrentStore($this->request->post['store_id']);
            $this->model_setting_setting->editSetting('module_notifywhenavailable', $this->request->post, $this->request->post['store_id']);
            $this->model_setting_setting->editSetting('notifywhenavailable', $this->request->post, $this->request->post['store_id']);
            if ($this->request->post['notifywhenavailable']['Enabled'] == 'yes'){               
                $this->model_setting_setting->editSetting('notifywhenavailable_status', array('notifywhenavailable_status' => 1));
            } else{
                $this->model_setting_setting->editSetting('notifywhenavailable_status', array('notifywhenavailable_status' => 0));
            }
            
            if ($this->request->post['notifywhenavailable']["ScheduleEnabled"] == 'yes') {
                $this->editCron($this->request->post, $store['store_id']);
            }

            $this->setupEvent();

            $this->session->data['success'] = $this->language->get('text_nwa_success');
            
            $this->response->redirect($this->url->link(Config::notifywhenavailable_path, 'store_id=' . $this->request->post['store_id'] . '&' . Config::notifywhenavailable_token_string . '=' . $this->session->data[Config::notifywhenavailable_token_string], 'SSL'));
        }
        
        $languages = $this->model_localisation_language->getLanguages();
        
        $this->data['languages'] = $languages;
        
        foreach ($this->data['languages'] as $key => $value) {
            $this->data['languages'][$key]['flag_url'] = 'language/' . $this->data['languages'][$key]['code'] . '/' . $this->data['languages'][$key]['code'] . '.png"';
        }
        
        $firstLanguage = array_shift($languages);
        
        $this->data['firstLanguageCode'] = $firstLanguage['code'];
        
        $this->data['heading_title'] = $this->language->get('heading_title') . ' ' . Config::notifywhenavailable_version;
        
        $language_variables = array(
            'text_default',
            'button_cancel',
            'preorder_enabled',
            'text_add',
            'text_enabled',
            'text_disabled',
            'text_remove',
            'text_remove_all',
            'text_status',
            'text_status_help',
            'text_scheduled',
            'text_scheduled_help',
            'text_scheduled_help_sec',
            'text_receive_notifications',
            'text_receive_notifications_help',
            'text_type',
            'text_type_help',
            'text_settings_help',
            'text_privacy_policy',
            'text_privacy_policy_help',
            'text_schedule',
            'text_schedule_help',
            'text_fixed',
            'text_periodic',
            'text_test_cron',
            'text_test_cron_help',
            'text_test_cron_help_sec',
            'text_test_cron_help_third',
            'text_cron_disabled',
            'text_close',
            'text_schedule_cron',
            'text_alternative_cron',
            'text_alternative_cron_help',
            'text_alternative_cron_help_two',
            'text_stock_status',
            'text_stock_status_help',
            'text_admin_notifications',
            'text_admin_notifications_help',
            'text_popup_width',
            'text_popup_width_help',
            'text_design',
            'text_design_help',
            'text_button_label',
            'text_button_label_help',
            'text_popup_title',
            'text_popup_title_help',
            'text_notification_customer',
            'text_notification_customer_help',
            'text_notification_mail',
            'text_notification_mail_help',
            'text_notification_subject',
            'text_email_text',
            'text_email_text_help',
            'text_email_text_help_sec',
            'text_email_subject',
            'text_custom_css',
            'text_custom_css_help',
            'text_most_wanted_ofs',
            'text_most_wanted_all_time',
            'text_count',
            'text_product',
            'text_button_options',
            'text_button_options_help',
            'text_statistic_option_note',
            'text_captcha',
            'text_captcha_help',
            'text_replace_cart',
            'text_new_button',
        );
        
        foreach ($language_variables as $language_variable) {
            $this->data[$language_variable] = $this->language->get($language_variable);
        }
        
        $this->load->model('localisation/stock_status');
        
        $this->data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
        
        if (isset($this->session->data['success'])) {
            $this->data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $this->data['success'] = '';
        }
        
        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }
        
        $this->data['breadcrumbs'] = array();
        
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', Config::notifywhenavailable_token_string . '=' . $this->session->data[Config::notifywhenavailable_token_string], 'SSL'),
            'separator' => false
        );
        
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->extensionsLink,
            'separator' => ' :: '
        );
        
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(Config::notifywhenavailable_path, Config::notifywhenavailable_token_string . '=' . $this->session->data[Config::notifywhenavailable_token_string], 'SSL'),
            'separator' => ' :: '
        );
        
        $this->data['action'] = $this->url->link(Config::notifywhenavailable_path, Config::notifywhenavailable_token_string . '=' . $this->session->data[Config::notifywhenavailable_token_string], 'SSL');
        
        $this->data['cancel'] = $this->extensionsLink;

        $this->data['cronCmdArgs'] = '';
        if ($_SERVER['HTTPS'] == 'ON' || $_SERVER['SERVER_PORT'] == 443) {
        	$this->data['cronCmdArgs'] .= 'https';
        } else {
        	$this->data['cronCmdArgs'] .= 'http';
        }
        
        if (isset($this->request->post['notifywhenavailable'])) {
            foreach ($this->request->post['notifywhenavailable'] as $key => $value) {
                $this->data['nwa_data']['notifywhenavailable'][$key] = $this->request->post['notifywhenavailable'][$key];
            }
        } else {
            $configValue                               = $this->config->get('notifywhenavailable');
            $this->data['nwa_data']['notifywhenavailable'] = $configValue;
        }
        
        $run_query = $this->db->query("SELECT `product_id`,`customer_notified`, COUNT(`customer_notified`) as cust_count FROM `" . DB_PREFIX . "notifywhenavailable` 
            WHERE store_id = " . $store['store_id'] . "
            GROUP BY `product_id`,`customer_notified`");
        
        $this->data['products'] = array();
        
        if (isset($run_query->rows)) {
            foreach ($run_query->rows as $row) {
                $this->data['products'][$row['product_id']][$row['customer_notified']] = $row['cust_count'];
            }
        }

        $this->data['most_wanted_products_ofs'] = array();

        $most_wanted_ofs_query = $this->db->query("SELECT product_id, customer_notified, COUNT(`customer_notified`) as cust_count 
            FROM `" . DB_PREFIX . "notifywhenavailable`
            WHERE store_id = " . $store['store_id'] . "
            AND customer_notified = 0
            GROUP BY product_id, customer_notified
            ORDER BY cust_count DESC
            LIMIT 20");

        if(!empty($most_wanted_ofs_query)) {
            foreach($most_wanted_ofs_query->rows as $key => $value) {
                $product_info = $this->model_catalog_product->getProduct($value['product_id']);
                $most_wanted_ofs_query->rows[$key]['name'] = $product_info['name'];
            }
            $this->data['most_wanted_products_ofs'] = $most_wanted_ofs_query->rows;
        }

        $this->data['most_wanted_products_all_time'] = array();

        $most_wanted_all_time_query = $this->db->query("SELECT product_id, customer_notified, COUNT(`customer_notified`) as cust_count 
            FROM `" . DB_PREFIX . "notifywhenavailable`
            WHERE store_id = " . $store['store_id'] . "
            GROUP BY product_id
            ORDER BY cust_count DESC
            LIMIT 20");

        if(!empty($most_wanted_all_time_query)) {
            foreach($most_wanted_all_time_query->rows as $key => $value) {
                $product_info = $this->model_catalog_product->getProduct($value['product_id']);
                $most_wanted_all_time_query->rows[$key]['name'] = $product_info['name'];
            }
            $this->data['most_wanted_products_all_time'] = $most_wanted_all_time_query->rows;
        }
        
        $this->data['stores']       = array_merge(array(
            0 => array(
                'store_id' => '0',
                'name' => $this->config->get('config_name') . ' (' . $this->data['text_default'] . ')',
                'url' => HTTP_SERVER,
                'ssl' => HTTPS_SERVER
            )
        ), $this->model_setting_store->getStores());

        $dataSettings = $this->model_setting_setting->getSetting('notifywhenavailable', $store['store_id']);
        $this->data['store']        = $store;
        $this->data['nwa_data']     = $dataSettings;
        $this->data['modules']      = $this->model_setting_setting->getSetting('notifywhenavailable_module', $store['store_id']);
        //$this->data['product_info'] = $this->model_catalog_product;
        $this->data['token']        = $this->session->data[Config::notifywhenavailable_token_string];
        $this->data['header']       = $this->load->controller('common/header');
        $this->data['column_left']  = $this->load->controller('common/column_left');
        $this->data['footer']       = $this->load->controller('common/footer');

        $pdis = array();
        foreach ($this->data['products'] as $pid => $notified) {
            $productInfo = $this->model_catalog_product->getProduct($pid);
            $pdis[] = "['" . htmlspecialchars_decode($productInfo['name'], ENT_QUOTES)."', ".(isset($notified[0]) ? $notified[0] : '0').", ".(isset($notified[1]) ? $notified[1] : '0').", ''],";
        }
        $this->data['product_infos'] = implode(',', $pdis);

        //Check if PreOrder is installed and enabled  
        $this->data['preorder'] = $this->model_setting_setting->getSetting('preorder', $store['store_id']);

        if (empty($dataSettings['notifywhenavailable']['LicensedOn'])) {
            $this->data['b64'] = base64_decode('ICAgIDxkaXYgY2xhc3M9ImFsZXJ0IGFsZXJ0LWRhbmdlciBmYWRlIGluIj4NCiAgICAgICAgPGJ1dHRvbiB0eXBlPSJidXR0b24iIGNsYXNzPSJjbG9zZSIgZGF0YS1kaXNtaXNzPSJhbGVydCIgYXJpYS1oaWRkZW49InRydWUiPsOXPC9idXR0b24+DQogICAgICAgIDxoND5XYXJuaW5nISBVbmxpY2Vuc2VkIHZlcnNpb24gb2YgdGhlIG1vZHVsZSE8L2g0Pg0KICAgICAgICA8cD5Zb3UgYXJlIHJ1bm5pbmcgYW4gdW5saWNlbnNlZCB2ZXJzaW9uIG9mIHRoaXMgbW9kdWxlISBZb3UgbmVlZCB0byBlbnRlciB5b3VyIGxpY2Vuc2UgY29kZSB0byBlbnN1cmUgcHJvcGVyIGZ1bmN0aW9uaW5nLCBhY2Nlc3MgdG8gc3VwcG9ydCBhbmQgdXBkYXRlcy48L3A+PGRpdiBzdHlsZT0iaGVpZ2h0OjVweDsiPjwvZGl2Pg0KICAgICAgICA8YSBjbGFzcz0iYnRuIGJ0bi1kYW5nZXIiIGhyZWY9ImphdmFzY3JpcHQ6dm9pZCgwKSIgb25jbGljaz0iJCgnYVtocmVmPSNpc2Vuc2Utc3VwcG9ydF0nKS50cmlnZ2VyKCdjbGljaycpIj5FbnRlciB5b3VyIGxpY2Vuc2UgY29kZTwvYT4NCiAgICA8L2Rpdj4=');
            $hostname = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '';
            $this->data['hostname'] = (strstr($hostname,'http://') === false) ? 'http://'.$hostname : $hostname;
            $this->data['domain'] = base64_encode($this->data['hostname']);
            $this->data['timenow'] = time();
        }

        if (!empty($dataSettings['notifywhenavailable']['LicensedOn'])) {
            $this->data['cHRpbWl6YXRpb24ef4fe'] = base64_encode(json_encode($dataSettings['notifywhenavailable']['License']));
        }

        $this->data['openTicketURL'] = 'http://isenselabs.com/tickets/open/'.base64_encode('Support Request').'/'.base64_encode('107').'/'.base64_encode($_SERVER['SERVER_NAME']);

        $this->data['serverURL'] = $this->getServerURL();

		$this->data['tab_viewcustomers_content']   = $this->load->view($this->data['modulePath'] . '/tab_viewcustomers', $this->data);
        $this->data['tab_controlpanel_content']    = $this->load->view($this->data['modulePath'] . '/tab_controlpanel', $this->data);
        $this->data['tab_settings_content'] = $this->load->view($this->data['modulePath'] . '/tab_settings', $this->data);
        $this->data['tab_chart_content']  = $this->load->view($this->data['modulePath'] . '/tab_chart', $this->data);
        $this->data['tab_archive_content']   = $this->load->view($this->data['modulePath'] . '/tab_archive', $this->data);
        $this->data['tab_support_content']   = $this->load->view($this->data['modulePath'] . '/tab_support', $this->data);
        
        $this->response->setOutput($this->load->view(Config::notifywhenavailable_path, $this->data));
    }
    
    private function editCron($data = array(), $store_id)
    {
        $cronCommands   = array();
        $cronFolder     = dirname(DIR_APPLICATION) . '/system/library/vendor/isenselabs/notifywhenavailable/';
        $dateForSorting = array();
        
        if (isset($data['notifywhenavailable']["ScheduleType"]) && $data['notifywhenavailable']["ScheduleType"] == 'F') {
            if (isset($data['notifywhenavailable']["FixedDates"])) {
                
                foreach ($data['notifywhenavailable']["FixedDates"] as $date) {
                    $buffer           = explode('/', $date);
                    $bufferDate       = explode('.', $buffer[0]);
                    $bufferTime       = explode(':', $buffer[1]);
                    $cronCommands[]   = (int) $bufferTime[1] . ' ' . (int) $bufferTime[0] . ' ' . (int) $bufferDate[0] . ' ' . (int) $bufferDate[1] . ' * php ' . $cronFolder . 'sendMails.php ' . $store_id;
                    $dateForSorting[] = $bufferDate[2] . '.' . $bufferDate[1] . '.' . $bufferDate[0] . '.' . $buffer[1];
                }
                
                asort($dateForSorting);
                
                $sortedDates = array();
                
                foreach ($dateForSorting as $date) {
                    $newDate       = explode('.', $date);
                    $sortedDates[] = $newDate[2] . '.' . $newDate[1] . '.' . $newDate[0] . '/' . $newDate[3];
                }
                
                $data = $sortedDates;
                
            }
            
        }
        
        if (isset($data['notifywhenavailable']["ScheduleType"]) && $data['notifywhenavailable']["ScheduleType"] == 'P') {
            $cronCommands[] = $data['notifywhenavailable']['PeriodicCronValue'] . ' php ' . $cronFolder . 'sendMails.php ' . $store_id;
            
        }
        
        if (isset($cronCommands) && $this->isEnabled('shell_exec')) {
            $cronCommands      = implode(PHP_EOL, $cronCommands);
            $currentCronBackup = shell_exec('crontab -l');
            $currentCronBackup = explode(PHP_EOL, $currentCronBackup);
            
            foreach ($currentCronBackup as $key => $command) {
                if (strpos($command, 'php ' . $cronFolder . 'sendMails.php ' . $store_id) || empty($command)) {
                    unset($currentCronBackup[$key]);
                }
            }
            
            $currentCronBackup = implode(PHP_EOL, $currentCronBackup);
            file_put_contents($cronFolder . 'cron.txt', $currentCronBackup . PHP_EOL . $cronCommands . PHP_EOL);
            exec('crontab -r');
            exec('crontab ' . $cronFolder . 'cron.txt');
        }
        
    }
    
    public function install()
    {
        $this->moduleModel->install();
        $this->setupEvent();
    }

    public function update()
    {
        if ($this->config->get('notifywhenavailable')) {
            $this->moduleModel->updateDb();
        }
    }
    
    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->load->model('setting/store');
        $this->model_setting_setting->deleteSetting('notifywhenavailable_module', 0);
        
        $stores = $this->model_setting_store->getStores();
        
        foreach ($stores as $store) {
            $this->model_setting_setting->deleteSetting('notifywhenavailable', $store['store_id']);
        }
        
        $this->moduleModel->uninstall();

        $this->removeEvent();
    }

    public function setupEvent() {
        $this->removeEvent();

        $moduleEvents = array(
            'admin/model/catalog/product/editProduct/after'   => 'extension/module/notifywhenavailable/adminModelCatalogProductEditAfter',
            'admin/view/catalog/product_form/after'           => 'extension/module/notifywhenavailable/adminViewCatalogProductFormAfter',
            'admin/controller/extension/module/productmanager/updateproduct/before' => 'extension/module/notifywhenavailable/productManagerCompatibleSingleUpdate',
            'admin/controller/extension/module/productmanager/updateproductbulk/before' => 'extension/module/notifywhenavailable/productManagerCompatibleBulkUpdate',

            'catalog/controller/product/product/before'       => 'extension/module/notifywhenavailable/catalogControllerProductProductBefore',
            'catalog/controller/product/product/after'        => 'extension/module/notifywhenavailable/catalogControllerProductProductAfter',
            'catalog/view/product/product/before'             => 'extension/module/notifywhenavailable/catalogViewProductProductBefore',
            'catalog/view/journal2/quickview/quickview/after' => 'extension/module/notifywhenavailable/catalogViewJournal2QuickviewAfter',
            'catalog/model/catalog/product/getProduct/after'  => 'extension/module/notifywhenavailable/catalogModelCatalogProductGetProductAfter'
        );

        $this->load->model('setting/event');
        foreach ($moduleEvents as $trigger => $handler) {
            $this->model_setting_event->addEvent(Config::notifywhenavailable_event_group, $trigger, $handler, 1, 0);
        }
    }

    public function removeEvent() {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode(Config::notifywhenavailable_event_group);
    }

    private function getCatalogURL()
    {
        
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_CATALOG;
        } else {
            $storeURL = HTTP_CATALOG;
        }
        
        return $storeURL;
        
    }
    
    private function getServerURL()
    {
        
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_SERVER;
        } else {
            $storeURL = HTTP_SERVER;
        }
        
        return $storeURL;
        
    }
    
    private function getCurrentStore($store_id)
    {
        
        if ($store_id && $store_id != 0) {
            $store = $this->model_setting_store->getStore($store_id);
        } else {
            $store['store_id'] = 0;
            $store['name']     = $this->config->get('config_name');
            $store['url']      = $this->getCatalogURL();
        }
        
        return $store;
        
    }
    
    public function getcustomers()
    {
        
        if (!empty($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }
        
        if (!empty($this->request->get['store_id'])) {
            $store_id = (int) $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        
        $this->data['store_id'] = $store_id;
        $this->data['token']    = $this->session->data[Config::notifywhenavailable_token_string];
        $this->data['limit']    = 8;
        $this->data['total']    = $this->moduleModel->getTotalCustomers($this->data['store_id']);
        $this->data['setting']  = $this->config->get('notifywhenavailable');
        
        $this->data['text_customer_email'] = $this->language->get('text_customer_email');
        $this->data['text_customer_name']  = $this->language->get('text_customer_name');
        $this->data['text_privacy_policy'] = $this->language->get('text_privacy_policy');
        $this->data['text_product']        = $this->language->get('text_product');
        $this->data['text_date']           = $this->language->get('text_date');
        $this->data['text_language']       = $this->language->get('text_language');
        $this->data['text_actions']        = $this->language->get('text_actions');
        $this->data['text_remove']         = $this->language->get('text_remove');
        $this->data['text_remove_all']     = $this->language->get('text_remove_all');
        $this->data['text_export_csv']     = $this->language->get('text_export_csv');
        $this->data['text_agree']          = $this->language->get('text_agree');
        
        $pagination        = new Pagination();
        $pagination->total = $this->data['total'];
        $pagination->page  = $page;
        $pagination->limit = $this->data['limit'];
        $pagination->url   = $this->url->link(Config::notifywhenavailable_path . '/getcustomers', Config::notifywhenavailable_token_string . '=' . $this->session->data[Config::notifywhenavailable_token_string] . '&page={page}&store_id=' . $this->data['store_id'], 'SSL');
        
        $this->data['pagination'] = $pagination->render();
        $this->data['sources']    = $this->moduleModel->viewcustomers($page, $this->data['limit'], $this->data['store_id']);

        foreach ($this->data['sources'] as $key => $source) {
            if ($source['selected_options'] != NULL) {
                $this->data['sources'][$key]['selected_options'] = unserialize($source['selected_options']);
            }
        }

        $this->data['results'] = sprintf($this->language->get('text_pagination'), ($this->data['total']) ? (($page - 1) * $this->data['limit']) + 1 : 0, ((($page - 1) * $this->data['limit']) > ($this->data['total'] - $this->data['limit'])) ? $this->data['total'] : ((($page - 1) * $this->data['limit']) + $this->data['limit']), $this->data['total'], ceil($this->data['total'] / $this->data['limit']));
        
        $this->template = Config::notifywhenavailable_path . '/viewcustomers';
        
        $this->response->setOutput($this->load->view($this->template, $this->data));
    }
    
    public function getarchive()
    {
        
        if (!empty($this->request->get['page'])) {
            $page = (int) $this->request->get['page'];
        } else {
            $page = 1;
        }
        
        if (!empty($this->request->get['store_id'])) {
            $store_id = (int) $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        
        $this->data['store_id'] = $store_id;
        $this->data['token']    = $this->session->data[Config::notifywhenavailable_token_string];
        $this->data['limit']    = 8;
        $this->data['total']    = $this->moduleModel->getTotalNotifiedCustomers($this->data['store_id']);
        $this->data['setting']  = $this->config->get('notifywhenavailable');
        
        $this->data['text_customer_email'] = $this->language->get('text_customer_email');
        $this->data['text_customer_name']  = $this->language->get('text_customer_name');
        $this->data['text_product']        = $this->language->get('text_product');
        $this->data['text_date']           = $this->language->get('text_date');
        $this->data['text_language']       = $this->language->get('text_language');
        $this->data['text_actions']        = $this->language->get('text_actions');
        $this->data['text_remove']         = $this->language->get('text_remove');
        $this->data['text_remove_all']     = $this->language->get('text_remove_all');
        $this->data['text_export_csv']     = $this->language->get('text_export_csv');
        $this->data['text_agree']          = $this->language->get('text_agree');
        
        $pagination        = new Pagination();
        $pagination->total = $this->data['total'];
        $pagination->page  = $page;
        $pagination->limit = $this->data['limit'];
        $pagination->url   = $this->url->link(Config::notifywhenavailable_path . '/getarchive', Config::notifywhenavailable_token_string . '=' . $this->session->data[Config::notifywhenavailable_token_string] . '&page={page}&store_id=' . $this->data['store_id'], 'SSL');
        
        $this->data['pagination'] = $pagination->render();
        $this->data['sources']    = $this->moduleModel->viewnotifiedcustomers($page, $this->data['limit'], $this->data['store_id']);

        foreach ($this->data['sources'] as $key => $source) {
            if ($source['selected_options'] != NULL) {
                $this->data['sources'][$key]['selected_options'] = unserialize($source['selected_options']);
            }
        }
        
        $this->data['results'] = sprintf($this->language->get('text_pagination'), ($this->data['total']) ? (($page - 1) * $this->data['limit']) + 1 : 0, ((($page - 1) * $this->data['limit']) > ($this->data['total'] - $this->data['limit'])) ? $this->data['total'] : ((($page - 1) * $this->data['limit']) + $this->data['limit']), $this->data['total'], ceil($this->data['total'] / $this->data['limit']));
        
        $this->template = Config::notifywhenavailable_path . '/archive';
        
        $this->response->setOutput($this->load->view($this->template, $this->data));
    }
    
    public function removecustomer()
    {
        
        if (isset($_POST['notifywhenavailable_id'])) {
            $run_query = $this->db->query("DELETE FROM `" . DB_PREFIX . "notifywhenavailable` WHERE `notifywhenavailable_id`=" . (int) $_POST['notifywhenavailable_id']);
            
            if ($run_query)
                echo "Success!";
        }
        
    }
    
    public function exportListToCsv()
    {
        $option   = '';
        $filename = fopen('php://memory', 'w');
        
        fputcsv($filename, array(
            'Customer Email',
            'Customer Name',
            'Product',
            'Option',
            'Date',
            'Language'
        ), ';');

        $sql = "SELECT DISTINCT n.customer_email, n.customer_name, pd.name, n.selected_options, n.date_created, n.language FROM `" . DB_PREFIX . "notifywhenavailable` AS n LEFT JOIN `" . DB_PREFIX . "product_description` AS pd ON (n.product_id=pd.product_id)";

        if (isset($this->request->get['waiting'])) {
            $sql .= " WHERE n.`customer_notified` = 0";
        }

        if (isset($this->request->get['archive'])) {
            $sql .= " WHERE n.`customer_notified` = 1";
        }

        $sql .= " ORDER BY n.`date_created` DESC";
        
        $query = $this->db->query($sql);
        
        foreach ($query->rows as $row) {
            if ($row['selected_options'] != NULL) {
                $options = unserialize($row['selected_options']);
                if (!empty($options)) {
                    foreach ($options as $item) {
                        $option = $item['name'];
                    }
                }
            } else {
                $option = '';
            }
            
            fputcsv($filename, array(
                $row['customer_email'],
                $row['customer_name'],
                $row['name'],
                $option,
                $row['date_created'],
                $row['language']
            ), ';');
        }
        
        fseek($filename, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="NotifyWhenAvailable_WaitingList_Export.csv"');
        fpassthru($filename);
        fclose($filename);
    }
    
    public function removeallcustomers()
    {
        
        if (isset($_POST['remove']) && ($_POST['remove'] == true)) {
            $run_query = $this->db->query("DELETE FROM `" . DB_PREFIX . "notifywhenavailable` WHERE `customer_notified`='0'");
            if ($run_query)
                echo "Success!";
        }
        
    }
    
    public function removeallarchive()
    {
        
        if (isset($_POST['remove']) && ($_POST['remove'] == true)) {
            $run_query = $this->db->query("DELETE FROM `" . DB_PREFIX . "notifywhenavailable` WHERE `customer_notified`='1'");
            if ($run_query)
                echo "Success!";
        }
        
    }
    
    protected function validateForm()
    {
        if (!$this->user->hasPermission('modify', Config::notifywhenavailable_path)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    public function testcron()
    {
        $data['cronjob_status'] = 'Disabled';
        $cronFolder             = dirname(DIR_APPLICATION) . '/system/library/vendor/isenselabs/notifywhenavailable/';
        
        if ($this->isEnabled('shell_exec')) {
            $data['shell_exec_status'] = 'Enabled';
        } else {
            $data['shell_exec_status'] = 'Disabled';
        }
        
        if ($data['shell_exec_status'] == 'Enabled') {
            if (shell_exec('crontab -l')) {
                $data['cronjob_status']    = 'Enabled';
                $curentCronjobs            = shell_exec('crontab -l');
                $data['current_cron_jobs'] = explode(PHP_EOL, $curentCronjobs);
                file_put_contents($cronFolder . 'cron.txt', '* * * * * echo "test" ' . PHP_EOL);
            } else {
                file_put_contents($cronFolder . 'cron.txt', '* * * * * echo "test" ' . PHP_EOL);
                
                if (file_exists($cronFolder . 'cron.txt')) {
                    exec('crontab ' . $cronFolder . 'cron.txt');
                    
                    if (shell_exec('crontab -l')) {
                        $data['cronjob_status'] = 'Enabled';
                        shell_exec('crontab -r');
                    } else {
                        $data['cronjob_status'] = 'Disabled';
                    }
                }
            }
        }
        
        if (file_exists($cronFolder . 'cron.txt')) {
            $data['folder_permission'] = "Writable";
            unlink($cronFolder . 'cron.txt');
        } else {
            if ($data['shell_exec_status'] == 'Disabled') {
                $data['folder_permission'] = "File does not exist and the shell_exec function is disabled.";
            } else {
                $data['folder_permission'] = "Unwritable";
            }
        }
        
        $data['cron_folder'] = $cronFolder;
        $data['token']       = $this->session->data[Config::notifywhenavailable_token_string];
        
        $this->response->setOutput($this->load->view(Config::notifywhenavailable_path . '/test_cron', $data));
    }
    
    private function isEnabled($func)
    {
        return is_callable($func) && false === stripos(ini_get('disable_functions'), $func);
    }

    /** Event handlers **/

    public function adminModelCatalogProductEditAfter(&$route, &$args, &$output) {
        // send emails after a product has been edited, $args[0] - product_id, $args[1] - new product data
        if (isset($args[1]['product_store'])) {
            foreach ($args[1]['product_store'] as $store_id) { 
                $rows = array();
                $NotifyWhenAvailable = $this->model_setting_setting->getSetting('notifywhenavailable',$store_id);
                if (!empty($NotifyWhenAvailable) && isset($NotifyWhenAvailable['notifywhenavailable']) && $NotifyWhenAvailable['notifywhenavailable']['Enabled']=='yes') {
					if (isset($NotifyWhenAvailable['notifywhenavailable']['stock_status'][$args[1]['stock_status_id']]) && $NotifyWhenAvailable['notifywhenavailable']['stock_status'][$args[1]['stock_status_id']] == 'on') {
						$users = $this->moduleModel->getAllSubscribedCustomers($store_id); // Get all subscribed users from DB
						foreach($users as $user) {
							$notify = false;
							$matchCount = 0;
							if(!empty($user['selected_options'])) { // Check if the key 'selected_options' is formatted correctly
								$user['selected_options'] = unserialize($user['selected_options']);
								$userCount = count($user['selected_options']);
							} else {
								$userCount = 0; // set the userCount var to 0 if the key 'selected_options' is empty
							}
							if($userCount == 0) { // if we don't have to check options quantities
								if ($args[1]['notifywhenavailable']=="yes" && (int)$args[1]['quantity']>0) {  
									$rows[] = $user['notifywhenavailable_id'];
								}
							} else if(!empty($user['selected_options']) && is_array($user['selected_options']) && isset($args[1]['product_option'])) { // check options quantitites
								foreach ($user['selected_options'] as $user_options) {
									foreach ($args[1]['product_option'] as $product_option) {
										if (!isset($product_option['product_option_value']) || !is_array($product_option['product_option_value'])) continue;
										foreach($product_option['product_option_value']  as $updated_options) {
											if((($updated_options['option_value_id'] == $user_options['option_value_id']) &&
												($updated_options['product_option_value_id'] == $user_options['product_option_value_id']) &&
												(($updated_options['quantity'] > 0))
											 ))
											{
												$matchCount++; // Do iteration if the quantity of selected option by user is > 0
											}
										}
									}
								}
								if($userCount > 0 && $matchCount == $userCount) { // Check if the user has selected at least 1 option and if all selected options meet user's requirements
									$rows[] = $user['notifywhenavailable_id'];
								}
							}          
						}
					}
                }

                if (!empty($rows)) {
                    $this->moduleModel->sendEmailWhenAvailable($args[0],$store_id,$rows);
                }
            }
        }
    }

    public function adminViewCatalogProductFormAfter(&$route, &$data, &$output) {
        // insert a hidden field to forward nwa settings
        $data['among_stock_status'] = false;
        $nwa_settings = $this->model_setting_setting->getSetting('notifywhenavailable', $this->config->get('config_store_id'));

        if (isset($nwa_settings['notifywhenavailable']['stock_status'][$data['stock_status_id']]) && $nwa_settings['notifywhenavailable']['stock_status'][$data['stock_status_id']] == 'on') {
            $footerAddition = '';
            if ($data['quantity'] <= 0) {
                $footerAddition = '<input type="hidden" form="form-product" name="notifywhenavailable" value="yes" />';
            } else {
                $footerAddition = '<input type="hidden" form="form-product" name="notifywhenavailable" value="no" />';
            }
            $output = str_replace('</form>', $footerAddition . '</form>', $output);
        }
    }

    // admin/controller/extension/module/productmanager/updateproduct/before
    public function productManagerCompatibleSingleUpdate(&$route, &$args) {
        if (isset($this->request->post)) {
            $product_id = $this->request->post['pid'];
            $field      = $this->request->post['pdata'];
            $value      = isset($this->request->post['pvalue']) ? $this->request->post['pvalue'] : '';

            if (($field == 'quantity' && (int)$value > 0) || $field == 'options') {
                $stores = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

                foreach ($stores->rows as $store) {
                    $rows  = array();
                    $store_id = $store['store_id'];
                    $NotifyWhenAvailable = $this->model_setting_setting->getSetting('notifywhenavailable', $store_id);

                    if (!empty($NotifyWhenAvailable) && isset($NotifyWhenAvailable['notifywhenavailable']) && $NotifyWhenAvailable['notifywhenavailable']['Enabled']=='yes') {
                        $users = $this->moduleModel->getAllSubscribedCustomers($store_id); // Get all subscribed users from DB
                        
                        foreach ($users as $user) { 
                            $notify = false;
                            $matchCount = 0;

                            if(!empty($user['selected_options'])) { // Check if the key 'selected_options' is formatted correctly
                                $user['selected_options'] = unserialize($user['selected_options']);
                                $userCount = count($user['selected_options']);
                            } else {
                                $userCount = 0; // set the userCount var to 0 if the key 'selected_options' is empty
                            }

                            if ($field == 'quantity' && (int)$value > 0 && $userCount == 0) { // if we don't have to check options quantities
                                $rows[] = $user['notifywhenavailable_id'];
                            } elseif ($field == 'options' && !empty($user['selected_options']) && is_array($user['selected_options'])) { // check options quantitites

                                foreach ($user['selected_options'] as $user_options) {
                                    if (isset($this->request->post['product_option'])) {
                                        foreach ($this->request->post['product_option'] as $product_option) {
                                            if (!isset($product_option['product_option_value']) || !is_array($product_option['product_option_value'])) continue;
                                            foreach($product_option['product_option_value']  as $updated_options) {
                                                if((($updated_options['option_value_id'] == $user_options['option_value_id']) &&
                                                    ($updated_options['product_option_value_id'] == $user_options['product_option_value_id']) &&
                                                    (($updated_options['quantity'] > 0))
                                                 ))
                                                {
                                                    $matchCount++; // Do iteration if the quantity of selected option by user is > 0
                                                }
                                                
                                            }
                                        }
                                    }
                                }
        
                                if($userCount > 0 && $matchCount == $userCount) { // Check if the user has selected at least 1 option and if all selected options meet user's requirements
                                    $rows[] = $user['notifywhenavailable_id'];
                                }
                            }
                        }
                    }
                    if (!empty($rows)) {
                        $this->moduleModel->sendEmailWhenAvailable($product_id, $store_id, $rows);
                    }
                }
            }
        }
    }

    // admin/controller/extension/module/productmanager/updateproductbulk/before
    public function productManagerCompatibleBulkUpdate(&$route, &$args) {
        if (isset($this->request->post)) {
            $product_id  = $this->request->post['pid'];
            $product_ids = explode(",", $product_id);
            $field       = $this->request->post['pdata'];
            $value       = isset($this->request->post['pvalue']) ? $this->request->post['pvalue'] : '';
            $ptype       = isset($this->request->post['ptype'])  ? $this->request->post['ptype']  : '';

            // Only check quantity due the nature of bulk-options is either add new or replace options; 
            // NWA user selected_options will always different, thus cannot be checked.

            if ($field == 'quantity') {
                $this->load->model('catalog/product');

                foreach ($product_ids as $pid) {
                    $product_id   = $pid;
                    $product_info = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
                    $stores       = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
                    $new_value    = str_replace('%value%', $product_info->row['quantity'], $value);
                    eval( '$quantity = (' . $new_value . ');' );

                    if ((int)$quantity < 1) {
                        continue;
                    }

                    foreach ($stores->rows as $store) {
                        $rows  = array();
                        $store_id = $store['store_id'];
                        $NotifyWhenAvailable = $this->model_setting_setting->getSetting('notifywhenavailable', $store_id);
                        
                        if (!empty($NotifyWhenAvailable) && isset($NotifyWhenAvailable['notifywhenavailable']) && $NotifyWhenAvailable['notifywhenavailable']['Enabled']=='yes') {
                            $users = $this->moduleModel->getAllSubscribedCustomers($store_id); // Get all subscribed users from DB
                            
                            foreach ($users as $user) { 
                                $notify     = false;
                                $matchCount = 0;
                                $userCount  = 0;

                                if(!empty($user['selected_options'])) { // Check if the key 'selected_options' is formatted correctly
                                    $user['selected_options'] = unserialize($user['selected_options']);
                                    $userCount = count($user['selected_options']);
                                }

                                if ($userCount == 0) { // Only
                                    $rows[] = $user['notifywhenavailable_id'];
                                }
                            }
                        }
                        if (!empty($rows)) {
                            $this->moduleModel->sendEmailWhenAvailable($product_id, $store_id, $rows);
                        }
                    }
                }
            }
        }
    }
}
