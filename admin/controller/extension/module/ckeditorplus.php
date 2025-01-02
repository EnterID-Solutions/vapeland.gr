<?php
/**
 * @total-module	CKEditor 4+ (4.8.0)
 * @author-name 	◘ Dotbox Creative
 * @copyright		Copyright (C) 2018 ◘ Dotbox Creative www.dotbox.eu
 */
class ControllerExtensionModuleCkeditorplus extends Controller {
	private $error = array(); 
	
	public function index() {   
		$this->load->language('extension/module/ckeditorplus');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		$this->load->model('module/ckeditorplus');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ckeditorplus', $this->request->post);	

			//oc list fix
			$this->request->post['module_ckeditorplus_status'] = $this->request->post['ckeditorplus_status'];
			$this->model_setting_setting->editSetting('module_ckeditorplus', $this->request->post);		
					
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
		
	$language_info = array(
	'button_save','button_cancel','heading_title','text_module','text_success','tab_general','tab_info','text_enabled','text_disabled','text_edit',
	'entry_language','entry_language_info','entry_skin','entry_skin_info','entry_status','entry_enhanced','entry_enhanced_info', 'entry_height' ,'entry_height_info','entry_enhanced_image','entry_enhanced_image_info'
	);
		
		
		foreach ($language_info as $language) {
			$data[$language] = $this->language->get($language); 
		}

	

		$data['user_token'] = $this->session->data['user_token'];
    
 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
 		if (isset($this->error['folder'])) {
			$data['error_folder'] = $this->error['folder'];
		} else {
			$data['error_folder'] = '';
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
			'href' => $this->url->link('extension/module/ckeditorplus', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/ckeditorplus', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		// simple imput fields
		$imput_fields = array('ckeditorplus_status','ckeditorplus_enhanced','ckeditorplus_en_img');
		
		foreach ($imput_fields as $imput_field) {
			if (isset($this->request->post[$imput_field])) {
				$data[$imput_field] = $this->request->post[$imput_field];
			} else {
				$data[$imput_field] = $this->config->get($imput_field);
			}		
		}

		// special imput fields
		$imput_fields_special = array('ckeditorplus_skin' => 'kama', 'ckeditorplus_language' => 'en', 'ckeditorplus_height' => 300, );
		$data['dotbox'] = $this->model_module_ckeditorplus->getplist();
		foreach ($imput_fields_special as $imput_fields_special => $value) {
			if (isset($this->request->post[$imput_fields_special])) {
			$data[$imput_fields_special] = $this->request->post[$imput_fields_special];
			} else if($this->config->get($imput_fields_special)){
			$data[$imput_fields_special] = $this->config->get($imput_fields_special);
			} else {
			$data[$imput_fields_special] = $value;	
			}	
		}

	

		// get the languages
		$data['languages'] = array();
		$ignore = array('en');
		$files = glob(DIR_APPLICATION . 'view/javascript/ckeditor/lang/*.js');
		
		foreach ($files as $file) {		
			$languages = basename($file, '.js');
			if (!in_array($languages, $ignore)) { $data['languages'][] = $languages; }
		}	

		// get skins
		$data['skin'] = array();
		$ignore_skin = array('kama');
		$skin_files = glob(DIR_APPLICATION . 'view/javascript/ckeditor/skins/*');

		foreach ($skin_files as $file) {		
			
			$skin_refined = basename($file);
			if (!in_array($skin_refined , $ignore_skin)) { $data['skin'][] = $skin_refined ; }		
		}

		// RENDER
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ckeditorplus', $data));

	}
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ckeditorplus')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

	

}
?>