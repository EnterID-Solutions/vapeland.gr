<?php
class ControllerExtensionModuleAdminKey extends Controller {
	private $error = array();
	private $route = 'extension/module/adminkey';
	
	public function install() {
		

	}
	
	public function uninstall() {
		
	}

	public function index() {
		$this->load->language('extension/module/adminkey');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_setting_setting->editSetting('module_adminkey', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
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
			'href' => $this->url->link('extension/module/adminkey', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/adminkey', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_adminkey_status'])) {
			$data['module_adminkey_status'] = $this->request->post['module_adminkey_status'];
		} else {
			$data['module_adminkey_status'] = $this->config->get('module_adminkey_status');
		}
		if (isset($this->request->post['module_adminkey_key'])) {
			$data['module_adminkey_key'] = $this->request->post['module_adminkey_key'];
		} elseif ($this->config->has('module_adminkey_key')) {
			$data['module_adminkey_key'] = $this->config->get('module_adminkey_key');
		} else {
			$data['module_adminkey_key'] = '';
		}
			
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/adminkey', $data));
	}

	protected function validate() {
		
		$this->load->language($this->route);
		
		if (!$this->user->hasPermission('modify', $this->route)) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		}

		return true;
	}
}