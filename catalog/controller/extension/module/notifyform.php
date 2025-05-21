<?php
class ControllerExtensionModuleNotifyform extends Controller {
	public function index() {	
		if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$template_folder = 'oc3';
		}else{
			$template_folder = 'oc2';
		}
			
		$language_id = $this->config->get('config_language_id');
		
		if (version_compare(VERSION,'2.2.0.0','<' )) {
			$data['theme_directory'] = $this->config->get('config_template');
		}else if (version_compare(VERSION,'3.0.0.0','>' )) {
			$data['theme_directory'] = $this->config->get('config_theme');
		} else {
			$data['theme_directory'] = $this->config->get('theme_default_directory');
		}
		
		$data['hb_oosn_name_enable']		= $this->config->get('hb_oosn_name_enable');
		$data['hb_oosn_mobile_enable']		= $this->config->get('hb_oosn_mobile_enable');
		$data['hb_oosn_comments_enable']	= $this->config->get('hb_oosn_comments_enable');
		$data['hb_oosn_animation']  		= $this->config->get('hb_oosn_animation');
		$data['hb_oosn_css'] 				= $this->config->get('hb_oosn_css');
		$data['hb_oosn_incl_magnific'] 		= $this->config->get('hb_oosn_incl_magnific');
		
		if ($data['theme_directory'] == 'journal2') {
			$data['hb_oosn_incl_magnific'] = 0;
		}
		
		if (($data['theme_directory'] == 'journal3') or ($data['theme_directory'] == 'default')) {
			if (isset($this->request->get['route']) and $this->request->get['route'] == 'product/product') {
				$data['hb_oosn_incl_magnific'] = 0;
			}else{
				$data['hb_oosn_incl_magnific'] = 1;
			}
		}
		
		$data['notify_button'] 				= html_entity_decode($this->config->get('hb_oosn_notifybtn_f'.$language_id));
		$data['oosn_info_text'] 			= html_entity_decode($this->config->get('hb_oosn_t_info'.$language_id));
		$data['oosn_text_email'] 			= html_entity_decode($this->config->get('hb_oosn_t_email'.$language_id));
		$data['oosn_text_email_plh'] 		= html_entity_decode($this->config->get('hb_oosn_t_email_ph'.$language_id));
		$data['oosn_text_name'] 			= html_entity_decode($this->config->get('hb_oosn_t_name'.$language_id));
		$data['oosn_text_name_plh'] 		= html_entity_decode($this->config->get('hb_oosn_t_name_ph'.$language_id));
		$data['oosn_text_phone'] 			= html_entity_decode($this->config->get('hb_oosn_t_phone'.$language_id));
		$data['oosn_text_phone_plh'] 		= html_entity_decode($this->config->get('hb_oosn_t_phone_ph'.$language_id));
		$data['oosn_text_comment'] 			= html_entity_decode($this->config->get('hb_oosn_t_comment'.$language_id));
		$data['oosn_text_comment_plh'] 		= html_entity_decode($this->config->get('hb_oosn_t_comment_ph'.$language_id));
		
		if ($this->customer->isLogged()){
			$data['email'] = $this->customer->getEmail();
			$data['fname'] = $this->customer->getFirstName();
			$data['phone'] = $this->customer->getTelephone();
		}else {
			$data['email'] = $data['fname'] =  $data['phone'] = '';
		}
		
		// Captcha
		if ($this->config->get('hb_oosn_enable_captcha') == 1){
			$data['site_key'] = $this->config->get('google_captcha_key');
			$data['show_captcha'] = true;
		}else{
			$data['show_captcha'] = false;
		}
		
		if ($data['hb_oosn_incl_magnific'] == 1) {
			$this->document->addScript('catalog/view/javascript/magnific-popup-psa/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/magnific-popup-psa/magnific-popup.css');
		}
		$this->document->addStyle('catalog/view/javascript/magnific-popup-psa/popup-effect.css');
		$this->document->addStyle('catalog/view/javascript/magnific-popup-psa/notify-form.css');
					
		if (version_compare(VERSION,'2.2.0.0','<')){
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/module/'.$template_folder.'/notifyform.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/extension/module/'.$template_folder.'/notifyform.tpl', $data);
			} else {
				return $this->load->view('default/template/extension/module/'.$template_folder.'/notifyform.tpl', $data);
			}
		}else{
			return $this->load->view('extension/module/'.$template_folder.'/notifyform', $data);
		}
	}
}