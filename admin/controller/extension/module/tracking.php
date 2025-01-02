<?php
class ControllerExtensionModuleTracking extends Controller {
  private $error = array();

  public function index(){
    $this->load->language('extension/module/tracking');
		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		$this->load->model('extension/module/tracking');
    $this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->log->write($this->request->post);
      $submited = $this->request->post;
      if(isset($this->request->post['module_tracking_couriers'])){
        $couriers = $this->request->post['module_tracking_couriers'];
        $this->model_extension_module_tracking->saveCouriers($couriers);
        unset($submited['module_tracking_couriers']);
      }
      $this->model_setting_setting->editSetting('module_tracking', $submited);
      $this->session->data['success'] = $this->language->get('text_success');
      $this->response->redirect($this->url->link('marketplace/extension', '&type=module&user_token=' . $this->session->data['user_token'], true));
    }

    $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
    $data['action'] = $this->url->link('extension/module/tracking', 'user_token=' . $this->session->data['user_token'], true);
    $data['user_token'] = $this->session->data['user_token'];

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
			'text' => strip_tags($this->language->get('heading_title')),
			'href' => $this->url->link('extension/module/tracking', 'user_token=' . $this->session->data['user_token'] , true)
		);



    $data['module_tracking_couriers'] = $this->model_extension_module_tracking->getCouriers();

    if (isset($this->request->post['module_tracking_status'])) {
			$data['module_tracking_status'] = $this->request->post['module_tracking_status'];
		} else {
			$data['module_tracking_status'] = $this->config->get('module_tracking_status');
		}


    if (isset($this->request->post['module_tracking_message'])) {
			$data['module_tracking_message'] = $this->request->post['module_tracking_message'];
		} elseif($this->config->get('module_tracking_message')) {
			$data['module_tracking_message'] = $this->config->get('module_tracking_message');
		} else {
      $data['module_tracking_message'] = $this->language->get('text_default_tracking_message');
    }

    $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/tracking', $data));
  }

  function install(){
		$this->load->model('extension/module/tracking');
    $this->model_extension_module_tracking->install();
	}

	function uninstall(){
		$this->load->model('extension/module/tracking');
		$this->model_extension_module_tracking->uninstall();
	}

  public function deleteCourier(){
    $this->load->model('extension/module/tracking');
    $this->model_extension_module_tracking->deleteCourier($this->request->post['courier_id']);
    $json['success'] = true;
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function ordercourier(){
    $this->load->model('extension/module/tracking');
    $courier = $this->model_extension_module_tracking->getCourier($this->request->post['courier_id']);

    $json=array();
    if($courier){
      $json['success'] = true;
      $json['courier']=$courier;
      $json['message'] = str_replace(array('[courier_name]', '[courier_url]'), array($courier['courier_name'], $courier['courier_url']), $this->config->get('module_tracking_message'));
    } else {
      $json['success'] = false;
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function addTracking(){
    $this->load->model('extension/module/tracking');
    $tracking_data['tracking_code'] = $this->request->post['tracking_code'];
    $tracking_data['courier_id'] = $this->request->post['courier_id'];
    $tracking_data['order_id'] = $this->request->post['order_id'];

    $json=array();
    if($tracking_data['order_id'] == '' || !is_numeric($tracking_data['order_id'])){
      $json['error']=true;
      $json['msg'] = "Error on order_id";
    } elseif($tracking_data['courier_id'] == '' || !is_numeric($tracking_data['courier_id'])){
      $json['error']=true;
      $json['msg'] = "Error on courier_id";
    } elseif($tracking_data['tracking_code'] == ''){
      $json['error']=true;
      $json['msg'] = "Error on tracking_code";
    } else {
      $this->model_extension_module_tracking->addOrderTracking($tracking_data);
      $json['error'] = false;
      $json['success'] = true;
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
  }

  public function checkOrder(){
    $this->load->model('extension/module/tracking');
    $order_id = $this->request->post['order_id'];
    $trackingData = $this->model_extension_module_tracking->getOrderTracking($order_id);
    if($trackingData){
      return $trackingData['tracking_code'];
    } else {
      return false;
    }

  }

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/tracking')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
?>
