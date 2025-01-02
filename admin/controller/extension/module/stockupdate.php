<?php
class controllerExtensionModuleStockupdate extends Controller
{
    private $error=array();
    private $api_token;
    private $octoken;

    public function __construct($registry)
    {
        parent::__construct($registry);

        if ($this->config->get('module_stockupdate_version') == '2') {
            $this->octoken = '&token=';
        } else {
            $this->octoken = '&api_token=';
        }
    }

    public function index()
    {
        $this->load->model('setting/setting');

        $this->load->language('extension/module/stockupdate');

        $this->document->setTitle(strip_tags($this->language->get('heading_title')));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_stockupdate', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/stockupdate', 'user_token=' . $this->session->data['user_token'], true)
        );

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['action'] = $this->url->link('extension/module/stockupdate', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);


        if (isset($this->request->post['module_stockupdate_status'])) {
            $data['module_stockupdate_status'] = $this->request->post['module_stockupdate_status'];
        } else {
            $data['module_stockupdate_status'] = $this->config->get('module_stockupdate_status');
        }

        if (isset($this->request->post['module_stockupdate_url'])) {
            $data['module_stockupdate_url'] = $this->request->post['module_stockupdate_url'];
        } else {
            $data['module_stockupdate_url'] = $this->config->get('module_stockupdate_url');
        }

        if (isset($this->request->post['module_stockupdate_username'])) {
            $data['module_stockupdate_username'] = $this->request->post['module_stockupdate_username'];
        } else {
            $data['module_stockupdate_username'] = $this->config->get('module_stockupdate_username');
        }

        if (isset($this->request->post['module_stockupdate_password'])) {
            $data['module_stockupdate_password'] = $this->request->post['module_stockupdate_password'];
        } else {
            $data['module_stockupdate_password'] = $this->config->get('module_stockupdate_password');
        }

        if (isset($this->request->post['module_stockupdate_version'])) {
            $data['module_stockupdate_version'] = $this->request->post['module_stockupdate_version'];
        } else {
            $data['module_stockupdate_version'] = $this->config->get('module_stockupdate_version');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/stockupdate', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/stockupdate')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install()
    {
        $this->load->model('extension/module/stockupdate');
        $this->load->model('user/user_group');
        $this->load->model('setting/event');
        $this->model_extension_module_stockupdate->install();
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/' . $this->request->get['extension']);
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/' . $this->request->get['extension']);

        $this->model_setting_event->addEvent('stockupdate_menu', 'admin/view/common/column_left/before', 'extension/module/stockupdate/eventMenu');
    }

    public function uninstall()
    {
        $this->load->model('extension/module/stockupdate');
        $this->load->model('user/user_group');
        $this->load->model('setting/event');
        $this->model_extension_module_stockupdate->uninstall();
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/stockupdate');
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/stockupdate');

        $this->model_setting_event->deleteEventByCode('stockupdate_menu');
    }

    public function eventMenu($route, &$data)
    {
        $stockupdate = array();

        $this->load->language('extension/module/stockupdate');

        if ($this->user->hasPermission('access', 'extension/module/stockupdate')) {
            $stockupdate[] = array(
                'name'	   => $this->language->get('text_menu_stockupdate'),
                'href'     => $this->url->link('extension/module/stockupdate/sync', 'user_token=' . $this->session->data['user_token'], true),
                'children' => array()
            );

            if ($stockupdate) {
                $data['menus'][] = array(
                    'id'       => 'menu-excel-importer',
                    'icon'	   => 'fa fa-stack-exchange',
                    'name'	   => $this->language->get('text_parent_menu'),
                    'href'     => '',
                    'children' => $stockupdate
                );
            }
        }
    }

    public function sync()
    {
        $this->load->language('extension/module/stockupdate');
        $this->load->model('extension/module/stockupdate');
        $this->login();

        $this->document->setTitle(strip_tags($this->language->get('sync_heading_title')));
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/stockupdate/sync', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['user_token'] = $this->request->get['user_token'];
        $remoteProductsUrl ='/index.php?route=api/stock/getProducts'.$this->octoken.$this->session->data['stockupdate_token'];
        $remote_products = $this->executeCall($remoteProductsUrl, ['ref'=>'vapeland']);

        $local_products = $this->model_extension_module_stockupdate->getProducts();

        $diff = array();
        foreach ($remote_products['products'] as $id => $model) {
            if (in_array($model, $local_products)) {
              $local_id = array_search($model, $local_products);
              $diff[$local_id]=$id;
            }
        }

        $data['products_to_update'] = $diff;
        $data['to_update_sum'] = count($diff);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/stockupdate_sync', $data));
    }

    public function setQuantity()
    {
		if ($this->request->post['product_id']==1778){
			return false;
		}
        $this->load->model('catalog/product');
        $this->load->model('extension/module/stockupdate');
        $this->login();
        $updateUrl ='/index.php?route=api/stock/setQuantity'.$this->octoken.$this->session->data['stockupdate_token'];
        $json=array();
        $product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);
        $lpo = $this->model_extension_module_stockupdate->getProductOptions($this->request->post['product_id']);

        $params = array(
          'ref' => 'vapeland',
          'product_id' => $this->request->post['remote_id'],
          'quantity' => $product_info['quantity'],
          'options' => $lpo
        );
        $update = $this->executeCall($updateUrl, $params);
        if (isset($update['error']) && $update['error']) {
            $json['error'] = 'Product not found <i class="fa fa-exclamation-circle" style="color:red"></i>';
        }
        if (isset($update['success']) && $update['success']) {
            $json['success'] = 'Product with code <a href="index.php?route=catalog/product/edit&product_id='.$product_info['product_id'].'&user_token='.$this->request->get['user_token'].'" target="_blank">#'.$product_info['model'].'</a> successfully updated <i class="fa fa-check" style="color:green;"></i>';
            if(strlen($update['success']) > 2 ){
              $json['success'] .= '<br>'.$update['success'];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function login()
    {
        if (isset($this->session->data['stockupdate_token'])) {
            $url = '/index.php?route=api/stock/checkToken'.$this->octoken.$this->session->data['stockupdate_token'];
            $postfields = array();
            $results = $this->executeLoginCall($url, $postfields);
            if (isset($results['error'])) {
                $url = '/index.php?route=api/login';
                $postfields = array('username' => $this->config->get('module_stockupdate_username'),'key' => $this->config->get('module_stockupdate_password'), 'ref'=>'vapeland');
                $results = $this->executeLoginCall($url, $postfields);
            }
        } else {
            $url = '/index.php?route=api/login';
            $postfields = array('username' => $this->config->get('module_stockupdate_username'),'key' => $this->config->get('module_stockupdate_password'), 'ref'=>'vapeland');
            $results = $this->executeLoginCall($url, $postfields);
        }
        if (isset($results['api_token'])) {
            $this->session->data['stockupdate_token'] = $results['api_token'];
        }

        return $this->session->data['stockupdate_token'];
    }

    private function executeCall($url, $postfields)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->config->get('module_stockupdate_url').$url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => http_build_query($postfields),

        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    private function executeLoginCall($url, $postfields)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->config->get('module_stockupdate_url').$url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => $postfields,

        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }


}
