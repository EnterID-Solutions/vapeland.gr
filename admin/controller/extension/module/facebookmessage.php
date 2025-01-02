<?php
use \vendor\isenselabs\facebookmessage\config as Config;

class ControllerExtensionModuleFacebookmessage extends Controller {

    private $moduleModel;
    private $extensionsLink;
    private $error = array(); 
    private $data = array();

    public function __construct($registry) {
        parent::__construct($registry);

        $this->extensionsLink = $this->url->link(Config::facebookmessage_extensionsLink, 'user_token=' . $this->session->data['user_token'], 'SSL');

        /* Module-specific declarations - Begin */
        $this->load->language(Config::facebookmessage_modulePath);
        $this->moduleModel = $this->{Config::facebookmessage_callModel};
        
        // Multi-Store
        $this->load->model('setting/store');
        // Settings
        $this->load->model('setting/setting');
        // Multi-Lingual
        $this->load->model('localisation/language');
        
        $this->load->model('catalog/category');

        $this->load->model('setting/module');

        /* Module-specific loaders - End */
    }

    public function index() {
        $data['moduleName'] = Config::facebookmessage_moduleName;
        $data['modulePath'] = Config::facebookmessage_modulePath;
        $data['moduleNameSmall'] = Config::facebookmessage_moduleName;
        $data['moduleModel'] = Config::facebookmessage_callModel;

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('view/stylesheet/'.Config::facebookmessage_moduleName.'/'.Config::facebookmessage_moduleName.'.css');
        $this->document->addStyle('view/javascript/'.Config::facebookmessage_moduleName.'/jquery.minicolors.css');
        $this->document->addScript('view/javascript/'.Config::facebookmessage_moduleName.'/jquery.minicolors.min.js');
        
        if(!isset($this->request->get['store_id'])) {
           $this->request->get['store_id'] = 0; 
        }
        
        $store = $this->getCurrentStore($this->request->get['store_id']);

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            if (!empty($_POST['OaXRyb1BhY2sgLSBDb21'])) {
                $this->request->post[Config::facebookmessage_moduleName]['LicensedOn'] = $_POST['OaXRyb1BhY2sgLSBDb21'];
            }
            if (!empty($_POST['cHRpbWl6YXRpb24ef4fe'])) {
                $this->request->post[Config::facebookmessage_moduleName]['License'] = json_decode(base64_decode($_POST['cHRpbWl6YXRpb24ef4fe']), 'SSL');
            }

            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('facebookmessage', $this->request->post);    
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
                $this->extensionsLink = $this->url->link(Config::facebookmessage_modulePath . ('&module_id=' . $this->request->get['module_id']), 'user_token=' . $this->session->data['user_token'], 'SSL'); // append the module id to redirect on the same page
            }

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->extensionsLink . Config::facebookmessage_extensionsLink_type);
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        $errors = array('warning', 'name', 'page_id');
        foreach ($errors as $error) {
            $data['error_' . $error] = '';
            if (isset($this->error[$error])) {
                $data['error_' . $error] = $this->error[$error];
            }
        }

        $data['breadcrumbs']   = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->extensionsLink
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link(Config::facebookmessage_modulePath, 'user_token=' . $this->session->data['user_token'], 'SSL'),
        );

        $languageVariables = array(
            'text_success', 
            'text_enabled', 
            'text_disabled'
        );
       
        $language_strings = $this->language->load(Config::facebookmessage_modulePath);
        foreach ($language_strings as $code => $languageVariable) {
             $data[$code] = $languageVariable;
        }

        $data['heading_title'] = $this->language->get('heading_title').' '.Config::facebookmessage_moduleVersion;
        $data['stores'] = array_merge(array(0 => array('store_id' => '0', 'name' => $this->config->get('config_name') . ' (Default)', 'url' => HTTP_SERVER, 'ssl' => HTTPS_SERVER)), $this->model_setting_store->getStores());

        $data['store'] = $store;
        $data['language_id'] = $this->config->get('config_language_id');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        foreach ($data['languages'] as $key => $value) {
            if(version_compare(VERSION, '2.2.0.0', "<")) {
                $data['languages'][$key]['flag_url'] = 'view/image/flags/'.$data['languages'][$key]['image'];

            } else {
                $data['languages'][$key]['flag_url'] = 'language/'.$data['languages'][$key]['code'].'/'.$data['languages'][$key]['code'].'.png"';
            }
        }

        $module_info = array();
        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = '';
        }

        $data['moduleSettings'] = $module_info;
        $data['moduleData'] = (isset($module_info[Config::facebookmessage_moduleName])) ? $module_info[Config::facebookmessage_moduleName] : array();

        if (empty($data['moduleData']) && isset($this->request->post['facebookmessage'])) {
            $data['moduleData'] = $this->request->post['facebookmessage'];
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link(Config::facebookmessage_modulePath, 'store_id='.$this->request->get['store_id'].'&user_token=' . $this->session->data['user_token'], 'SSL');
        } else {
            $data['action'] = $this->url->link(Config::facebookmessage_modulePath, 'module_id='.$this->request->get['module_id'].'&store_id='.$this->request->get['store_id'].'&user_token=' . $this->session->data['user_token'], 'SSL');
        }

        $data['cancel'] = $this->extensionsLink;
        $data['token']  = $this->session->data['user_token'];            
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $hostname = (!empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '' ;
        $hostname = (strstr($hostname,'http://') === false) ? 'http://'.$hostname: $hostname;

        $data['hostname'] = $hostname;
        $data['domain'] = base64_encode($hostname);
        $data['timenow'] = time();
        $data['base64'] = base64_decode('ICAgIDxkaXYgY2xhc3M9ImFsZXJ0IGFsZXJ0LWRhbmdlciBmYWRlIGluIj4NCiAgICAgICAgPGJ1dHRvbiB0eXBlPSJidXR0b24iIGNsYXNzPSJjbG9zZSIgZGF0YS1kaXNtaXNzPSJhbGVydCIgYXJpYS1oaWRkZW49InRydWUiPsOXPC9idXR0b24+DQogICAgICAgIDxoND5XYXJuaW5nISBVbmxpY2Vuc2VkIHZlcnNpb24gb2YgdGhlIG1vZHVsZSE8L2g0Pg0KICAgICAgICA8cD5Zb3UgYXJlIHJ1bm5pbmcgYW4gdW5saWNlbnNlZCB2ZXJzaW9uIG9mIHRoaXMgbW9kdWxlISBZb3UgbmVlZCB0byBlbnRlciB5b3VyIGxpY2Vuc2UgY29kZSB0byBlbnN1cmUgcHJvcGVyIGZ1bmN0aW9uaW5nLCBhY2Nlc3MgdG8gc3VwcG9ydCBhbmQgdXBkYXRlcy48L3A+PGRpdiBzdHlsZT0iaGVpZ2h0OjVweDsiPjwvZGl2Pg0KICAgICAgICA8YSBjbGFzcz0iYnRuIGJ0bi1kYW5nZXIiIGhyZWY9ImphdmFzY3JpcHQ6dm9pZCgwKSIgb25jbGljaz0iJCgnYVtocmVmPSNpc2Vuc2Vfc3VwcG9ydF0nKS50cmlnZ2VyKCdjbGljaycpIj5FbnRlciB5b3VyIGxpY2Vuc2UgY29kZTwvYT4NCiAgICA8L2Rpdj4=');

        if (isset($module_info[Config::facebookmessage_moduleName]) && !empty($module_info[Config::facebookmessage_moduleName]['LicensedOn'])) {
            $data['cHRpbWl6YXRpb24ef4fe'] = base64_encode(json_encode($module_info[Config::facebookmessage_moduleName]['License']));
            $data['expiresOn'] = date("F j, Y",strtotime($module_info[Config::facebookmessage_moduleName]['License']['licenseExpireDate']));
        }

        $data['openTicketUrl'] = 'http://isenselabs.com/tickets/open/'.base64_encode('Support Request').'/'.base64_encode('397').'/'. base64_encode($_SERVER['SERVER_NAME']);

        $this->response->setOutput($this->load->view(Config::facebookmessage_modulePath, $data));
    }
    
    protected function validateForm() {
        if (!$this->user->hasPermission('modify', Config::facebookmessage_modulePath)) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }

        if (!$this->error) {
            $post = $this->request->post;

            if (empty($post['name'])) {
                $this->error['warning'] = $this->language->get('error_required');
                $this->error['name'] = true;
            }
            if (empty($post['facebookmessage']['FacebookPageID'])) {
                $this->error['warning'] = $this->language->get('error_required');
                $this->error['page_id'] = true;
            }
        }

        return !$this->error;
    }

    public function install() {

    }
    
    public function uninstall() {
            
    }
    
    private function getCatalogURL() {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_CATALOG;
        } else {
            $storeURL = HTTP_CATALOG;
        } 
        
        return $storeURL;   
    }

    private function getServerURL() {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTPS_SERVER;
        } else {
            $storeURL = HTTP_SERVER;
        }

        return $storeURL;
    }

    private function getCurrentStore($store_id) {
        if($store_id && $store_id != 0) {
            $store = $this->model_setting_store->getStore($store_id);
        } else {
            $store['store_id'] = 0;
            $store['name'] = $this->config->get('config_name');
            $store['url'] = $this->getCatalogURL(); 
        }
        
        return $store;
    }   
}
