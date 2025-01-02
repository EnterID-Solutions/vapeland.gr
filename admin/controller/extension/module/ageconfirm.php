<?php
class ControllerExtensionModuleAgeConfirm extends Controller {
	private $error = array();

	public function index() {
		if(isset($this->request->get['store_id'])) {
			$data['store_id'] = $this->request->get['store_id'];
		}else{
			$data['store_id'] = 0;
		}

		$this->load->language('extension/module/ageconfirm');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		$this->load->model('tool/image');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_ageconfirm', $this->request->post, $data['store_id']);

			$this->session->data['success'] = $this->language->get('text_success');

			if(!empty($this->request->post['savetype']) && $this->request->post['savetype'] == 'savechanges') {
				$this->response->redirect($this->url->link('extension/module/ageconfirm', 'user_token=' . $this->session->data['user_token'].'&store_id='. $data['store_id'], true));
			} else { 
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
			}
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}


		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['link'])) {
			$data['error_link'] = $this->error['link'];
		} else {
			$data['error_link'] = '';
		}

		if (isset($this->error['days'])) {
			$data['error_days'] = $this->error['days'];
		} else {
			$data['error_days'] = '';
		}

		if (isset($this->error['reopen'])) {
			$data['error_reopen'] = $this->error['reopen'];
		} else {
			$data['error_reopen'] = '';
		}

		if (isset($this->error['description'])) {
			$data['error_ageconfirm_description'] = $this->error['description'];
		} else {
			$data['error_ageconfirm_description'] = array();
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
			'href' => $this->url->link('extension/module/ageconfirm', 'user_token=' . $this->session->data['user_token'], true)
		);


		if(isset($data['store_id'])) {
			$data['action'] = $this->url->link('extension/module/ageconfirm', 'user_token=' . $this->session->data['user_token'].'&store_id='. $data['store_id'], true);
		} else{
			$data['action'] = $this->url->link('extension/module/ageconfirm', 'user_token=' . $this->session->data['user_token'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$module_info = $this->model_setting_setting->getSetting('module_ageconfirm', $data['store_id']);

		if (isset($this->request->post['module_ageconfirm_days'])) {
			$data['module_ageconfirm_days'] = $this->request->post['module_ageconfirm_days'];
		} else if(isset($module_info['module_ageconfirm_days'])) {
			$data['module_ageconfirm_days'] = $module_info['module_ageconfirm_days'];
		} else {
			$data['module_ageconfirm_days'] = '30';
		}

		if (isset($this->request->post['module_ageconfirm_reopen'])) {
			$data['module_ageconfirm_reopen'] = $this->request->post['module_ageconfirm_reopen'];
		} else if(isset($module_info['module_ageconfirm_reopen'])) {
			$data['module_ageconfirm_reopen'] = $module_info['module_ageconfirm_reopen'];
		} else {
			$data['module_ageconfirm_reopen'] = 0;
		}

		if (isset($this->request->post['module_ageconfirm_border_width'])) {
			$data['module_ageconfirm_border_width'] = $this->request->post['module_ageconfirm_border_width'];
		} else if(isset($module_info['module_ageconfirm_border_width'])) {
			$data['module_ageconfirm_border_width'] = $module_info['module_ageconfirm_border_width'];
		} else {
			$data['module_ageconfirm_border_width'] = '4';
		}
		
		if (isset($this->request->post['module_ageconfirm_border_color'])) {
			$data['module_ageconfirm_border_color'] = $this->request->post['module_ageconfirm_border_color'];
		} else if(isset($module_info['module_ageconfirm_border_color'])) {
			$data['module_ageconfirm_border_color'] = $module_info['module_ageconfirm_border_color'];
		} else {
			$data['module_ageconfirm_border_color'] = '#444444';
		}

		if (isset($this->request->post['module_ageconfirm_background_color'])) {
			$data['module_ageconfirm_background_color'] = $this->request->post['module_ageconfirm_background_color'];
		} else if(isset($module_info['module_ageconfirm_background_color'])) {
			$data['module_ageconfirm_background_color'] = $module_info['module_ageconfirm_background_color'];
		} else {
			$data['module_ageconfirm_background_color'] = '';
		}

		if (isset($this->request->post['module_ageconfirm_text_color'])) {
			$data['module_ageconfirm_text_color'] = $this->request->post['module_ageconfirm_text_color'];
		} else if(isset($module_info['module_ageconfirm_text_color'])) {
			$data['module_ageconfirm_text_color'] = $module_info['module_ageconfirm_text_color'];
		} else {
			$data['module_ageconfirm_text_color'] = '';
		}

		if (isset($this->request->post['module_ageconfirm_link'])) {
			$data['module_ageconfirm_link'] = $this->request->post['module_ageconfirm_link'];
		} else if(isset($module_info['module_ageconfirm_link'])) {
			$data['module_ageconfirm_link'] = $module_info['module_ageconfirm_link'];
		} else {
			$data['module_ageconfirm_link'] = '';
		}

		if (isset($this->request->post['module_ageconfirm_circle'])) {
			$data['module_ageconfirm_circle'] = $this->request->post['module_ageconfirm_circle'];
		} else if(isset($module_info['module_ageconfirm_circle'])) {
			$data['module_ageconfirm_circle'] = $module_info['module_ageconfirm_circle'];
		} else {
			$data['module_ageconfirm_circle'] = 'CIRCLE';
		}

		if (isset($this->request->post['module_ageconfirm_effect'])) {
			$data['module_ageconfirm_effect'] = $this->request->post['module_ageconfirm_effect'];
		} else if(isset($module_info['module_ageconfirm_effect'])) {
			$data['module_ageconfirm_effect'] = $module_info['module_ageconfirm_effect'];
		} else {
			$data['module_ageconfirm_effect'] = '1';
		}

		if (isset($this->request->post['module_ageconfirm_redirect'])) {
			$data['module_ageconfirm_redirect'] = $this->request->post['module_ageconfirm_redirect'];
		} else if(isset($module_info['module_ageconfirm_redirect'])) {
			$data['module_ageconfirm_redirect'] = $module_info['module_ageconfirm_redirect'];
		} else {
			$data['module_ageconfirm_redirect'] = '';
		}

		if (isset($this->request->post['module_ageconfirm_logo'])) {
			$data['module_ageconfirm_logo'] = $this->request->post['module_ageconfirm_logo'];
		} else if(isset($module_info['module_ageconfirm_logo'])) {
			$data['module_ageconfirm_logo'] = $module_info['module_ageconfirm_logo'];
		} else {
			$data['module_ageconfirm_logo'] = '';
		}

		if (isset($data['module_ageconfirm_logo']) && is_file(DIR_IMAGE . $data['module_ageconfirm_logo'])) {
			$data['logo'] = $this->model_tool_image->resize($data['module_ageconfirm_logo'], 100, 100);
		} else {
			$data['logo'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}


		if (isset($this->request->post['module_ageconfirm_backimage'])) {
			$data['module_ageconfirm_backimage'] = $this->request->post['module_ageconfirm_backimage'];
		} else if(isset($module_info['module_ageconfirm_backimage'])) {
			$data['module_ageconfirm_backimage'] = $module_info['module_ageconfirm_backimage'];
		} else {
			$data['module_ageconfirm_backimage'] = '';
		}

		if (isset($data['module_ageconfirm_backimage']) && is_file(DIR_IMAGE . $data['module_ageconfirm_backimage'])) {
			$data['backimage'] = $this->model_tool_image->resize($data['module_ageconfirm_backimage'], 100, 100);
		} else {
			$data['backimage'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if (isset($this->request->post['module_ageconfirm_force'])) {
			$data['module_ageconfirm_force'] = $this->request->post['module_ageconfirm_force'];
		} else if(isset($module_info['module_ageconfirm_force'])) {
			$data['module_ageconfirm_force'] = $module_info['module_ageconfirm_force'];
		} else {
			$data['module_ageconfirm_force'] = '1';
		}

		if (isset($this->request->post['module_ageconfirm_status'])) {
			$data['module_ageconfirm_status'] = $this->request->post['module_ageconfirm_status'];
		} else if(isset($module_info['module_ageconfirm_status'])) {
			$data['module_ageconfirm_status'] = $module_info['module_ageconfirm_status'];
		} else {
			$data['module_ageconfirm_status'] = '';
		}

		if (isset($this->request->post['module_ageconfirm_css'])) {
			$data['module_ageconfirm_css'] = $this->request->post['module_ageconfirm_css'];
		} else if(isset($module_info['module_ageconfirm_css'])) {
			$data['module_ageconfirm_css'] = $module_info['module_ageconfirm_css'];
		} else {
			$data['module_ageconfirm_css'] = '';
		}

		if (isset($this->request->post['module_ageconfirm_description'])) {
			$data['module_ageconfirm_description'] = $this->request->post['module_ageconfirm_description'];
		} else if(isset($module_info['module_ageconfirm_description'])) {
			$data['module_ageconfirm_description'] = $module_info['module_ageconfirm_description'];
		} else {
			$data['module_ageconfirm_description'] = array();
		}

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$store_info = $this->model_setting_store->getStore($data['store_id']);
		if($store_info) {
			$data['store_name'] = $store_info['name'];
		}else{
			$data['store_name'] = $this->language->get('text_default');
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ageconfirm', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ageconfirm')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}


		if(!isset($this->request->post['module_ageconfirm_reopen'])) {
			$this->error['reopen'] = $this->language->get('error_reopen');
		}

		if((!isset($this->request->post['module_ageconfirm_days']) || (int)$this->request->post['module_ageconfirm_days'] < '0') && ( isset($this->request->post['module_ageconfirm_reopen']) && $this->request->post['module_ageconfirm_reopen'] == 0)) {
			$this->error['days'] = $this->language->get('error_days');
		}

		if (!empty($this->request->post['module_ageconfirm_redirect'])) {
			if (empty($this->request->post['module_ageconfirm_link'])) {
				$this->error['link'] = $this->language->get('error_link');
			}
		}

		return !$this->error;
	}
}