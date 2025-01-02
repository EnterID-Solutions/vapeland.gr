<?php
class ControllerCommonAgeconfirm extends Controller {
	public function index() {
		if($this->config->get('module_ageconfirm_status') && (!$this->config->get('config_maintenance') || $this->user->isLogged())) {

			$this->document->addStyle('catalog/view/theme/default/stylesheet/ageverification/component.css');

			$this->load->language('common/ageconfirm');

			$this->load->model('tool/image');

			$data['heading_title'] = $this->language->get('heading_title');

			$data['text_or'] = $this->language->get('text_or');
			$data['btn_enter'] = $this->language->get('btn_enter');
			$data['btn_exit'] = $this->language->get('btn_exit');

			$data['link'] = $this->config->get('module_ageconfirm_link');
			$data['ageconfirm_description'] = $this->config->get('module_ageconfirm_description');

			$data['heading_title'] = '';
			$data['html'] = '';
			$data['agree'] = $this->language->get('text_agree');
			$data['decline'] = $this->language->get('text_decline');

			if (!empty($data['ageconfirm_description'][$this->config->get('config_language_id')]['title'])) {
				$data['heading_title'] = $data['ageconfirm_description'][$this->config->get('config_language_id')]['title'];

			} else{
				$data['heading_title'] = $data['ageconfirm_description'][$this->config->get('config_language_id')]['title'];
			}

			if (!empty($data['ageconfirm_description'][$this->config->get('config_language_id')]['description'])) {
				$data['html'] = html_entity_decode($data['ageconfirm_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
			} else{
				$data['html'] = '';
			}

			if (!empty($data['ageconfirm_description'][$this->config->get('config_language_id')]['error_message'])) {
				if(html_entity_decode($data['ageconfirm_description'][$this->config->get('config_language_id')]['error_message']) != '<p><br></p>') {
					$data['error_message'] = str_replace("'", "&#x27;", html_entity_decode($data['ageconfirm_description'][$this->config->get('config_language_id')]['error_message'], ENT_QUOTES, 'UTF-8'));				} else{
					$data['error_message'] = $this->language->get('error_message');
				}
			} else{
				$data['error_message'] = $this->language->get('error_message');
			}

			if(!empty($data['ageconfirm_description'][$this->config->get('config_language_id')]['agree'])){
				$data['agree'] = $data['ageconfirm_description'][$this->config->get('config_language_id')]['agree'];
			} else{
				$data['agree'] = $this->language->get('button_agree');
			}

			if(!empty($data['ageconfirm_description'][$this->config->get('config_language_id')]['decline'])){
				$data['decline'] = $data['ageconfirm_description'][$this->config->get('config_language_id')]['decline'];
			} else{
				$data['decline'] = $this->language->get('button_decline');
			}

			if ($this->request->server['HTTPS']) {
				$server = $this->config->get('config_ssl');
			} else {
				$server = $this->config->get('config_url');
			}


			if (is_file(DIR_IMAGE . $this->config->get('module_ageconfirm_logo'))) {
				$data['logo'] = $server . 'image/' . $this->config->get('module_ageconfirm_logo');
			} else {
				$data['logo'] = '';
			}

			$data['name'] = $this->config->get('config_name');


			if (is_file(DIR_IMAGE . $this->config->get('module_ageconfirm_backimage'))) {
				$data['backimage'] = $server . 'image/' . $this->config->get('module_ageconfirm_backimage');
			} else {
				$data['backimage'] = '';
			}

			$data['shape'] = $this->config->get('module_ageconfirm_circle');
			$data['force'] = $this->config->get('module_ageconfirm_force');
			$data['border_width'] = ($this->config->get('module_ageconfirm_border_width')) ? $this->config->get('module_ageconfirm_border_width') : 0;
			$data['border_color'] = ($this->config->get('module_ageconfirm_border_color')) ? $this->config->get('module_ageconfirm_border_color') : '';
			$data['background_color'] = ($this->config->get('module_ageconfirm_background_color')) ? $this->config->get('module_ageconfirm_background_color') : '';
			$data['text_color'] = ($this->config->get('module_ageconfirm_text_color')) ? $this->config->get('module_ageconfirm_text_color') : '';
			$data['popup_effect'] = ($this->config->get('module_ageconfirm_effect')) ? $this->config->get('module_ageconfirm_effect') : 1;
			$data['custom_css'] = $this->config->get('module_ageconfirm_css');
			$data['ageconfirm_redirect'] = $this->config->get('module_ageconfirm_redirect');

			$data['confirmed'] = false;
			// check if user is already confirmed as adult
			if(isset($this->request->cookie['isAnAdult']) && $this->request->cookie['isAnAdult']==true) {
					$data['confirmed'] = true;
			}

			if(!$data['confirmed']) {
				return $this->load->view('common/ageconfirm', $data);
			}
		}
	}

	public function confirmed() {
		$json = array();
		$this->load->language('common/ageconfirm');

		if (!isset($this->request->post['confirmed']) || empty($this->request->post['confirmed'])) {
			$json['error'] = $this->language->get('error_invalid');
		}

		if(!$json) {
			// Set a cookie:

			// check if use session cookie
			if($this->config->get('module_ageconfirm_reopen')) {
				setcookie('isAnAdult', 'true'); // Not ask until browser is open. Once browser closes ask again to confirm.
			} else {
				$ageconfirm_days = 30;
				if((int)$this->config->get('ageconfirm_days')) {
					$ageconfirm_days = (int)$this->config->get('ageconfirm_days');
				}
				setcookie('isAnAdult', 'true', time()+60*60*24* $ageconfirm_days); // Do Not ask for next (10) days
			}


			$json['success'] = true;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}