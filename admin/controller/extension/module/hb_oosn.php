<?php
if (version_compare(VERSION,'3.0.0.0','>=' )) {
	define('TEMPLATE_FOLDER', 'oc3');
	define('EXTENSION_BASE', 'marketplace/extension');
	define('TOKEN_NAME', 'user_token');
	define('TEMPLATE_EXTN', '');
	define('EXTN_ROUTE', 'extension/module');
}else if (version_compare(VERSION,'2.2.0.0','<=' )) {
	define('TEMPLATE_FOLDER', 'oc2');
	define('EXTENSION_BASE', 'extension/module');
	define('TOKEN_NAME', 'token');
	define('TEMPLATE_EXTN', '.tpl');
	define('EXTN_ROUTE', 'module');
}else{
	define('TEMPLATE_FOLDER', 'oc2');
	define('EXTENSION_BASE', 'extension/extension');
	define('TOKEN_NAME', 'token');
	define('TEMPLATE_EXTN', '');
	define('EXTN_ROUTE', 'extension/module');
}
define('EXTN_VERSION', '9.0.5');
class ControllerExtensionModuleHbOosn extends Controller {
	
	private $error = array(); 
	
	public function index() {   
		$data['extension_version'] = EXTN_VERSION;
		$this->load->language(EXTN_ROUTE.'/hb_oosn');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('extension/module/hb_oosn');
		
		if ($this->model_extension_module_hb_oosn->checkInstallation() === false) {
			$this->response->redirect($this->url->link(EXTENSION_BASE, TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&type=module', true));
		}
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('hb_oosn', $this->request->post);	
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_oosn', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true));
		}		
		
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		
		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
		
		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$data['demands'] = $this->model_extension_module_hb_oosn->getDemandedList();
		
		$text_strings = array(
				'heading_title',
				'button_save','button_delete','button_reset','button_export', 'button_cancel',
				'tab_records','tab_email','tab_setting','tab_statistics','tab_tools','tab_language','tab_log',
				'text_no_results','text_extension',
				'text_total_alert',
				'text_total_responded',
				'text_show_all_reports',
				'text_reset_all',
				'text_customers_awaiting_notification',
				'text_number_of_products_demanded',
				'text_show_awaiting_reports',
				'text_reset_awaiting',
				'text_archive_records',
				'text_customers_notified',
				'text_show_archive_reports',
				'text_reset_archive',
				'text_reports',
				'text_current_report_all',
				'text_current_report_awaiting',
				'text_current_report_archive',
				'text_authkey',
				'column_count',
				'entry_text',
				'entry_success_msg',
				'entry_store_subject',
				'entry_store_body',
				'entry_customer_subject',
				'entry_customer_body',
				'text_admin_notify','text_admin_email',
				'email_to_customer',
				'text_header_form',
				'entry_enable_name','entry_enable_mobile','entry_enable_comments','entry_enable_sms',
				'entry_animation',
				'text_success',
				'text_product_qty',
				'text_product_stock_status',
				'text_header_admin',
				'text_header_customer',
				'entry_subject','entry_body','sms_body',
				'text_header_condition',
				'text_header_email',
				'text_header_sms',
				'entry_css','entry_enable_captcha','entry_enable_logs','entry_include_magnific',
				'text_product_image_size','text_mobile_validation',
				'text_header_installation',
				'installation','text_header_analytics','text_campaign_source'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
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
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true)
   		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link(EXTENSION_BASE, TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&type=module', true)
		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link(EXTN_ROUTE.'/hb_oosn', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true)
   		);
		
		$data['action'] = $this->url->link(EXTN_ROUTE.'/hb_oosn', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true);
		
		$data['cancel'] = $this->url->link('common/dashboard', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true);
		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];
		$data['base_route'] = EXTN_ROUTE;
		
		$data['uninstall'] = $this->url->link(EXTN_ROUTE.'/hb_oosn/uninstallFromExtension', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true);
		
		$data['catalog'] = (HTTPS_CATALOG) ? HTTPS_CATALOG : HTTP_CATALOG;

		$data['export'] 				= $this->url->link(EXTN_ROUTE.'/hb_oosn/download', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] , true);
		$data['fix_notified_dates'] 	= $this->url->link(EXTN_ROUTE.'/hb_oosn/fixNotifiedDates', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] , true);
		$data['delete_bulk'] 			= $this->url->link(EXTN_ROUTE.'/hb_oosn/delete_bulk', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] , true);
		$data['upgrade'] 				= $this->url->link(EXTN_ROUTE.'/hb_oosn/upgrade', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] , true);

		foreach ($data['languages'] as $result){
	 		$language_id = $result['language_id'];	
			$data['hb_oosn_customer_email_subject'][$language_id] 	=  ($this->config->get('hb_oosn_customer_email_subject_'.$language_id))? $this->config->get('hb_oosn_customer_email_subject_'.$language_id) : 'Your Requested Product is now Available to purchase';
			$data['hb_oosn_customer_email_body'][$language_id] 		=  ($this->config->get('hb_oosn_customer_email_body_'.$language_id))? $this->config->get('hb_oosn_customer_email_body_'.$language_id) : $this->customer_email_template();
			$data['hb_oosn_confirm_email_subject'][$language_id] 	=  ($this->config->get('hb_oosn_confirm_email_subject_'.$language_id))? $this->config->get('hb_oosn_confirm_email_subject_'.$language_id) : 'You have successfully subscribed to product!';
			$data['hb_oosn_confirm_email_body'][$language_id] 		=  ($this->config->get('hb_oosn_confirm_email_body_'.$language_id))? $this->config->get('hb_oosn_confirm_email_body_'.$language_id) : $this->confirmation_email_template();
			
			$data['hb_oosn_customer_sms_body'][$language_id] 		=  ($this->config->get('hb_oosn_customer_sms_body_'.$language_id))? $this->config->get('hb_oosn_customer_sms_body_'.$language_id) : '{product_name} {model} {option} is back in stock. Visit website to buy it now.';
			
			$data['hb_oosn_notifybtn_p'][$language_id] 				=  ($this->config->get('hb_oosn_notifybtn_p'.$language_id))? $this->config->get('hb_oosn_notifybtn_p'.$language_id): 'Notify when available';
			$data['hb_oosn_notifybtn_f'][$language_id]				=  ($this->config->get('hb_oosn_notifybtn_f'.$language_id))? $this->config->get('hb_oosn_notifybtn_f'.$language_id): 'Subscribe to this product';
			$data['hb_oosn_notifybtn_o'][$language_id] 				=  ($this->config->get('hb_oosn_notifybtn_o'.$language_id))? $this->config->get('hb_oosn_notifybtn_o'.$language_id): 'Notify';
			$data['hb_oosn_t_info'][$language_id] 					=  ($this->config->get('hb_oosn_t_info'.$language_id))? $this->config->get('hb_oosn_t_info'.$language_id): 'The product is currently Out-of-Stock. Enter your email address below and we will notify you as soon as the product is available.';
			$data['hb_oosn_t_info_opt'][$language_id] 				=  ($this->config->get('hb_oosn_t_info_opt'.$language_id))? $this->config->get('hb_oosn_t_info_opt'.$language_id): '<h3>The Selected option {selected_option} is out-of-stock.</h3>Enter your email address below and we will notify you as soon as the product is available.';

			$data['hb_oosn_t_success'][$language_id] 				=  ($this->config->get('hb_oosn_t_success'.$language_id))? $this->config->get('hb_oosn_t_success'.$language_id): 'Thank you! You will be notified by email as soon as the product is available.';
			$data['hb_oosn_t_error_email'][$language_id] 			=  ($this->config->get('hb_oosn_t_error_email'.$language_id))? $this->config->get('hb_oosn_t_error_email'.$language_id): 'Please enter a valid email address.';
			$data['hb_oosn_t_error_name'][$language_id] 			=  ($this->config->get('hb_oosn_t_error_name'.$language_id))? $this->config->get('hb_oosn_t_error_name'.$language_id): 'Please enter your Name.';
			$data['hb_oosn_t_error_phone'][$language_id] 			=  ($this->config->get('hb_oosn_t_error_phone'.$language_id))? $this->config->get('hb_oosn_t_error_phone'.$language_id): 'Please enter a valid mobile number.';
			$data['hb_oosn_t_error_comment'][$language_id] 			=  ($this->config->get('hb_oosn_t_error_comment'.$language_id))? $this->config->get('hb_oosn_t_error_comment'.$language_id): 'Maximum allowed character for comment is 3000';
			
			$data['hb_oosn_t_error_captcha'][$language_id] 			=  ($this->config->get('hb_oosn_t_error_captcha'.$language_id))? $this->config->get('hb_oosn_t_error_captcha'.$language_id): 'Robot verification failed, please try again!';
			$data['hb_oosn_t_warn_captcha'][$language_id] 			=  ($this->config->get('hb_oosn_t_warn_captcha'.$language_id))? $this->config->get('hb_oosn_t_warn_captcha'.$language_id): 'Please click on the reCAPTCHA box!';

			$data['hb_oosn_t_name'][$language_id] 					=  ($this->config->get('hb_oosn_t_name'.$language_id))? $this->config->get('hb_oosn_t_name'.$language_id): 'Name';
			$data['hb_oosn_t_email'][$language_id] 					=  ($this->config->get('hb_oosn_t_email'.$language_id))? $this->config->get('hb_oosn_t_email'.$language_id): 'Email';
			$data['hb_oosn_t_phone'][$language_id] 					=  ($this->config->get('hb_oosn_t_phone'.$language_id))? $this->config->get('hb_oosn_t_phone'.$language_id): 'Phone';
			$data['hb_oosn_t_comment'][$language_id] 				=  ($this->config->get('hb_oosn_t_comment'.$language_id))? $this->config->get('hb_oosn_t_comment'.$language_id): 'Comments';
			$data['hb_oosn_t_name_ph'][$language_id] 				=  ($this->config->get('hb_oosn_t_name_ph'.$language_id))? $this->config->get('hb_oosn_t_name_ph'.$language_id): 'Enter your Name';
			$data['hb_oosn_t_email_ph'][$language_id] 				=  ($this->config->get('hb_oosn_t_email_ph'.$language_id))? $this->config->get('hb_oosn_t_email_ph'.$language_id): 'Enter your Email';
			$data['hb_oosn_t_phone_ph'][$language_id] 				=  ($this->config->get('hb_oosn_t_phone_ph'.$language_id))? $this->config->get('hb_oosn_t_phone_ph'.$language_id): 'Enter your Phone Number';
			$data['hb_oosn_t_comment_ph'][$language_id]				=  ($this->config->get('hb_oosn_t_comment_ph'.$language_id))? $this->config->get('hb_oosn_t_comment_ph'.$language_id): 'Mention comments (if any)';
			
			$data['hb_oosn_t_duplicate'][$language_id] 				=  ($this->config->get('hb_oosn_t_duplicate'.$language_id))? $this->config->get('hb_oosn_t_duplicate'.$language_id): 'You have already created an alert for this product. We will notify you by email once the product backs in stock.';
			$data['hb_oosn_t_invalid'][$language_id] 				=  ($this->config->get('hb_oosn_t_invalid'.$language_id))? $this->config->get('hb_oosn_t_invalid'.$language_id): 'Invalid Product ID!';
		}
		
		$data['hb_oosn_admin_notify'] 			= ($this->config->get('hb_oosn_admin_notify'))? $this->config->get('hb_oosn_admin_notify') : '0';
		$data['hb_oosn_admin_email'] 			= ($this->config->get('hb_oosn_admin_email'))? $this->config->get('hb_oosn_admin_email') : $this->config->get('config_email');
		$data['hb_oosn_product_image_h'] 		= ($this->config->get('hb_oosn_product_image_h'))? $this->config->get('hb_oosn_product_image_h') : '200';
		$data['hb_oosn_product_image_w'] 		= ($this->config->get('hb_oosn_product_image_w'))? $this->config->get('hb_oosn_product_image_w') : '200';
		
		$data['hb_oosn_name_enable'] 			= ($this->config->get('hb_oosn_name_enable'))? $this->config->get('hb_oosn_name_enable') : 'n';
		$data['hb_oosn_mobile_enable'] 			= ($this->config->get('hb_oosn_mobile_enable'))? $this->config->get('hb_oosn_mobile_enable') : 'n';
		$data['hb_oosn_comments_enable'] 		= ($this->config->get('hb_oosn_comments_enable'))? $this->config->get('hb_oosn_comments_enable') : 'n';
		$data['hb_oosn_sms_enable'] 			= ($this->config->get('hb_oosn_sms_enable'))? $this->config->get('hb_oosn_sms_enable') : 'n';
		$data['hb_oosn_sms_http_api'] 			= ($this->config->get('hb_oosn_sms_http_api'))? $this->config->get('hb_oosn_sms_http_api') : '';
		
		$data['hb_oosn_animation'] 				= ($this->config->get('hb_oosn_animation'))? $this->config->get('hb_oosn_animation') : 'mfp-newspaper';
		$data['hb_oosn_css'] 					= ($this->config->get('hb_oosn_css'))? $this->config->get('hb_oosn_css') : '#oosn_info_text, #opt_info{&#10;padding-top:0px;&#10;color:#F00;&#10;}&#10;&#10;#msgoosn{&#10;color:green;&#10;}&#10;&#10;.notify_form_success{&#10;color:green;&#10;}&#10;.notify_form_warning{&#10;color:orange;&#10;}&#10;.notify_form_error{&#10;color:red;&#10;}&#10;&#10;.notify-button {&#10;    background-color: #EE6514 !important;&#10;}&#10;&#10;.notify-button:hover {&#10;    background-color: #BB4A07  !important;&#10;}';
		$data['hb_oosn_enable_captcha'] 		= ($this->config->get('hb_oosn_enable_captcha'))? $this->config->get('hb_oosn_enable_captcha') : '0';
		$data['hb_oosn_product_qty'] 			= ($this->config->get('hb_oosn_product_qty'))? $this->config->get('hb_oosn_product_qty') : '1';
		$data['hb_oosn_stock_status'] 			= ($this->config->get('hb_oosn_stock_status'))? $this->config->get('hb_oosn_stock_status') : '0';
		$data['hb_oosn_mobile_validation_min']	= ($this->config->get('hb_oosn_mobile_validation_min'))? $this->config->get('hb_oosn_mobile_validation_min') : '9';
		$data['hb_oosn_mobile_validation_max'] 	= ($this->config->get('hb_oosn_mobile_validation_max'))? $this->config->get('hb_oosn_mobile_validation_max') : '11';
		
		$data['hb_oosn_campaign'] 				= ($this->config->get('hb_oosn_campaign'))? $this->config->get('hb_oosn_campaign') : '&utm_source=huntbee_extension&utm_medium=email&utm_campaign=product_stock_notification';
		$data['hb_oosn_orderstatus'] 			= ($this->config->get('hb_oosn_orderstatus'))? $this->config->get('hb_oosn_orderstatus'): '5';
		$data['hb_oosn_authkey'] 				= ($this->config->get('hb_oosn_authkey'))? $this->config->get('hb_oosn_authkey'):md5(rand());
		$data['google_captcha_status'] 			= $this->config->get('google_captcha_status');
		
		$data['hb_oosn_logs'] 					= ($this->config->get('hb_oosn_logs'))? $this->config->get('hb_oosn_logs') : '';
		$data['hb_oosn_incl_magnific'] 			= ($this->config->get('hb_oosn_incl_magnific'))? $this->config->get('hb_oosn_incl_magnific') : '';
		$data['hb_oosn_confirm_email_enable'] 	= ($this->config->get('hb_oosn_confirm_email_enable'))? $this->config->get('hb_oosn_confirm_email_enable') : '';
		
		$data['hb_oosn_license'] 					= ($this->config->get('hb_oosn_license'))? $this->config->get('hb_oosn_license') : '';
		
		$data['cron_notify'] = 'wget --quiet --delete-after "'.$data['catalog'].'index.php?route=extension/module/product_oosn/autonotify&authkey='.$data['hb_oosn_authkey'].'"';
		$data['cron_demand'] = 'wget --quiet --delete-after "'.$data['catalog'].'index.php?route=extension/module/product_oosn/emaildemandedproducts&authkey='.$data['hb_oosn_authkey'].'"';
		
		$data['total_alert'] 			= $this->model_extension_module_hb_oosn->getTotalAlert();
        $data['total_responded'] 		= $this->model_extension_module_hb_oosn->getTotalResponded();
        $data['customer_notified'] 		= $this->model_extension_module_hb_oosn->getCustomerNotified();
        $data['awaiting_notification'] 	= $this->model_extension_module_hb_oosn->getAwaitingNotification();
        $data['product_requested'] 		= $this->model_extension_module_hb_oosn->getTotalRequested();
		
		//LOGS
		if (!file_exists(DIR_LOGS . 'hb_notify')) {
			mkdir(DIR_LOGS . 'hb_notify', 0777, true);
		}
		if (isset($this->request->get['log'])){
			$data['filename'] = strtolower($this->request->get['log']);
		}else{
			$month = date('M').'-'.date('Y');
			$data['filename'] = strtolower($month).'.txt';
		}
		$data['all_files'] = array_diff(scandir(DIR_LOGS . 'hb_notify'), array('.', '..'));
		
		//License
		$data['fullpro_status'] = $this->model_extension_module_hb_oosn->scriptInstallStatus();
						
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/'.TEMPLATE_FOLDER.'/hb_oosn'.TEMPLATE_EXTN, $data));

	}
	
	public function product_report(){
		$this->load->model('extension/module/hb_oosn');
		$order_status_id = ($this->config->get('hb_oosn_orderstatus')) ? $this->config->get('hb_oosn_orderstatus') : '5';
		
		if (isset($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];
			
			$product_detail = $this->model_extension_module_hb_oosn->getProductDetail($product_id);
			$data['product_name'] = $product_detail['name'];
			
			$data['pending_customers'] 			= array();
			$data['pending_customer_count'] 	= 0;
			$pending_customers = $this->model_extension_module_hb_oosn->getCustomersOfProduct($product_id, 'not_notified');
			if ($pending_customers) {
				$data['pending_customer_count'] = count($pending_customers);
				foreach ($pending_customers as $record) {
					$data['pending_customers'][] = array(
						'email'					=> $record['email'],
						'name'					=> $record['fname'],
						'phone'					=> $record['phone'],
						'qty'					=> $record['qty'],
						'all_selected_option'   => ($record['all_selected_option'] == '0')?'NA':$record['all_selected_option'],
						'customer_type'			=> $this->model_extension_module_hb_oosn->validateCustomerAccount($record['email']),
						'customer_purchases'	=> $this->model_extension_module_hb_oosn->validateCustomerPurchases($record['email'],$order_status_id),
						'customer_since'		=> $this->model_extension_module_hb_oosn->getCustomerDays($record['email']),
						'enquiry_date' 			=> date("d M Y", strtotime($record['enquiry_date']))
					);
				}
			}
			
			$data['notified_customers'] 		= array();
			$data['notified_customer_count'] 	= 0;
			$notified_customers = $this->model_extension_module_hb_oosn->getCustomersOfProduct($product_id, 'notified');
			if ($notified_customers) {
				$data['notified_customer_count'] = count($notified_customers);
				foreach ($notified_customers as $record) {
					$data['notified_customers'][] = array(
						'email'					=> $record['email'],
						'name'					=> $record['fname'],
						'phone'					=> $record['phone'],
						'qty'					=> $record['qty'],
						'all_selected_option'   => ($record['all_selected_option'] == '0')?'NA':$record['all_selected_option'],
						'customer_type'			=> $this->model_extension_module_hb_oosn->validateCustomerAccount($record['email']),
						'customer_purchases'	=> $this->model_extension_module_hb_oosn->validateCustomerPurchases($record['email'],$order_status_id),
						'customer_since'		=> $this->model_extension_module_hb_oosn->getCustomerDays($record['email']),
						'enquiry_date' 			=> date("d M Y", strtotime($record['enquiry_date'])),
						'notified_date'			=> (!empty($record['notified_date']))? date("d M Y", strtotime($record['notified_date'])) : ''
					);
				}
			}
		}
		
		$data['allyears'] = $this->model_extension_module_hb_oosn->allyears($product_id);

		$data['total_alert'] = array();
		if (!empty($data['allyears'])) {
			foreach ($data['allyears'] as $year){
				for ($month = 1 ; $month <= 12 ; $month++){
					$data['total_alert'][$year][] = array(
						'month_number'		=> $month,
						'month'				=> date('M', mktime(0, 0, 0, $month, 10)),
						'year'				=> $year,
						'count'				=> $this->model_extension_module_hb_oosn->total_alert($product_id, $month, $year)
					);
				}
			}
		}

		$this->response->setOutput($this->load->view('extension/module/'.TEMPLATE_FOLDER.'/hb_oosn_product_report'.TEMPLATE_EXTN, $data));
	}
	
	public function chart_trend(){
	    $this->load->model('extension/module/hb_oosn');
		$data['daily_alerts_stat'] = $this->model_extension_module_hb_oosn->dailyAlertsStat();
		$data['graph_data'] = array();
		if ($data['daily_alerts_stat']) {
			foreach ($data['daily_alerts_stat'] as $stat) {
				$data['graph_data'][] = '["'.date('d-m-y',strtotime($stat['days'])).'", '.$stat['total'].']';
			}
			$data['graph_data'] = implode($data['graph_data'], ',');
		}
		$this->response->setOutput($this->load->view('extension/module/'.TEMPLATE_FOLDER.'/hb_oosn_chart_trend'.TEMPLATE_EXTN, $data));
	}
	
	public function chart_product_demand(){
	    $this->load->model('extension/module/hb_oosn');
		$data['demands'] = $this->model_extension_module_hb_oosn->getDemandedList();
		
		$data['graph_data'] = array();
		if ($data['demands']) {
			$i=0;
			foreach ($data['demands'] as $demand) {
				$data['graph_data'][] = '["'.$demand['name'].'", '.$demand['count'].']';
				$i++;
				if($i==20){
					break;
				}
			}
			$data['graph_data'] = implode($data['graph_data'], ',');
			
			if (count($data['demands']) >= 10) {
				$data['graph_height'] = '600';
			}else if (count($data['demands']) > 5 and count($data['demands']) < 10) {
				$data['graph_height'] = '500';
			}else{
				$data['graph_height'] = '300';
			}
		}
		$this->response->setOutput($this->load->view('extension/module/'.TEMPLATE_FOLDER.'/hb_oosn_chart_pid'.TEMPLATE_EXTN, $data));
	}
	
	public function chart_record_stat(){
	    $this->load->model('extension/module/hb_oosn');
		
		$data['total_alert'] 		= $this->model_extension_module_hb_oosn->getTotalAlert();
        $data['total_responded'] 	= $this->model_extension_module_hb_oosn->getTotalResponded();
		$data['total_pending']		= $data['total_alert'] - $data['total_responded'];

		$this->response->setOutput($this->load->view('extension/module/'.TEMPLATE_FOLDER.'/hb_oosn_chart_record_stat'.TEMPLATE_EXTN, $data));
	}
	
	public function alertlist(){
		$this->load->model('extension/module/hb_oosn');
		$this->load->model('localisation/language');
		
		$order_status_id = ($this->config->get('hb_oosn_orderstatus')) ? $this->config->get('hb_oosn_orderstatus') : '5';
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}
		
		if (isset($this->request->get['notified'])) {
    		$notified = $this->request->get['notified'];
		}else {
			$notified = 'all';
		}
		
		$data = array(
			'start' 		=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' 		=> $this->config->get('config_limit_admin'),
			'notified' 		=> $notified,
			'search'		=> $search
		);

		$data[TOKEN_NAME] = $this->session->data[TOKEN_NAME];
		$data['base_route'] = EXTN_ROUTE;	
		
		$reports_total = $this->model_extension_module_hb_oosn->getTotalReports($data); 		
		$records = $this->model_extension_module_hb_oosn->getReports($data);
		$data['records'] = array();
		foreach ($records as $record) {
			$data['records'][] = array(
				'id' 					=> $record['oosn_id'],
				'product_id'    		=> $record['product_id'],
				'product_qty'    		=> $this->model_extension_module_hb_oosn->getProductQty($record['product_id']),
				'product_stock_status'	=> $this->model_extension_module_hb_oosn->getProductStockStatus($record['product_id']),
				'name'   				=> $record['name'],
				'selected_option'  		=> ($record['selected_option'] == '0')?'NA':$record['selected_option'],
				'all_selected_option'   => ($record['all_selected_option'] == '0')?'NA':$record['all_selected_option'],
				'email'  				=> $record['email'],
				'fname'   				=> $record['fname'],
				'phone'   				=> $record['phone'],
				'customer_type'			=> $this->model_extension_module_hb_oosn->validateCustomerAccount($record['email']),
				'customer_purchases'	=> $this->model_extension_module_hb_oosn->validateCustomerPurchases($record['email'],$order_status_id),
				'language_id'   		=> $record['language_id'],
				'language_name' 	 	=> $this->model_localisation_language->getLanguage($record['language_id']),
				'store_id'   			=> $record['store_id'],
				'store_url'				=> $record['store_url'],
				'store_name'   			=> $this->model_extension_module_hb_oosn->getStoreName($record['store_id']),
				'comment'   			=> html_entity_decode($record['comment']),
				'qty'   				=> $record['qty'],
				'ip'   					=> $record['ip'],
				'enquiry_date' 			=> date("d M Y g:i A", strtotime($record['enquiry_date'])),
				'notify_date'  			=> (!empty($record['notified_date']))? date("d M Y g:i A", strtotime($record['notified_date'])) : '',
                'product_link' 			=> $this->url->link('catalog/product/edit&product_id='.$record['product_id'], TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true)
			);
		}
		
		$pagination = new Pagination();
		$pagination->total = $reports_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link(EXTN_ROUTE.'/hb_oosn/alertlist', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME] . '&search='.$search.'&page={page}&notified='.$notified, true);

		$data['pagination'] = $pagination->render();
		$limit = $this->config->get('config_limit_admin');

		$data['results'] = sprintf($this->language->get('text_pagination'), ($pagination->total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($pagination->total - $limit)) ? $pagination->total : ((($page - 1) * $limit) + $limit), $pagination->total, ceil($pagination->total / $limit));

		$this->response->setOutput($this->load->view('extension/module/'.TEMPLATE_FOLDER.'/hb_oosn_list'.TEMPLATE_EXTN, $data));	
	}
	
	public function logs(){
		if (!file_exists(DIR_LOGS . 'hb_notify')) {
			mkdir(DIR_LOGS . 'hb_notify', 0777, true);
		}
		
		if (isset($this->request->get['log'])){
			$data['filename'] = strtolower($this->request->get['log']);
		}else{
			$month = date('M').'-'.date('Y');
			$data['filename'] = strtolower($month).'.txt';
		}

		$file = DIR_LOGS . 'hb_notify/'.$data['filename'];
		if (file_exists($file)) {
			$data['log'] = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
		}else{
			$data['log'] = '';
		}
		$this->response->setOutput($this->load->view('extension/module/'.TEMPLATE_FOLDER.'/hb_oosn_worklog'.TEMPLATE_EXTN, $data));
	}
	
	public function delete_bulk(){
		if((isset($this->request->get['record_type'])) &&  $this->validate()){  
			$this->load->model('extension/module/hb_oosn');  
            $record_type = $this->request->get['record_type'];
			$this->model_extension_module_hb_oosn->deleteRecords($record_type);
			$this->session->data['success'] = 'Records Deleted Successfully!';
			$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_oosn', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true));
		}
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', EXTN_ROUTE.'/hb_oosn')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
	
	public function delete_selected() {
		$this->load->model('extension/module/hb_oosn');
		
		if (isset($this->request->post['selected']) && ($this->validate())) {
			foreach ($this->request->post['selected'] as $id) {
				$this->model_extension_module_hb_oosn->deleteSelected($id);
			}
			$json['success'] = 'Selected Records Deleted Successfully!';
		}else{
			$json['warning'] =	'Either you do not have permission to delete records or you have not selected any record!';
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function reset_selected() {
		$this->load->model('extension/module/hb_oosn');
		
		if (isset($this->request->post['selected']) && ($this->validate())) {
			foreach ($this->request->post['selected'] as $id) {
				$this->model_extension_module_hb_oosn->resetSelected($id);
			}
			$json['success'] = 'Selected Records Reset Successful';
		}else{
			$json['warning'] =	'Either you do not have permission to reset records or you have not selected any record!';
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function download(){		
		$filename = 'product_stock_alert_data_'.date("Y-m-d-H-i-s").'.xls';
		header("Content-type: application/vnd-ms-excel");
		 
		// Defines the name of the export file "codelution-export.xls"
		header("Content-Disposition: attachment; filename=".$filename);
		//header("Content-Disposition: attachment; filename=codelution-export.xls");
		header("Pragma: no-cache"); header("Expires: 0");
		
		$data['products'] = array();
		
		$this->load->model('extension/module/hb_oosn');
		$this->load->model('localisation/language');
		$results = $this->model_extension_module_hb_oosn->getReportsforExcel();

		foreach ($results as $result) {
			$data['products'][] = array(
				'id' 					=> $result['oosn_id'],
				'product_id'    		=> $result['product_id'],
				'name'   				=> $result['name'],
				'selected_option'  		=> ($result['selected_option'] == '0')?'NA':$result['selected_option'],
				'all_selected_option'   => ($result['all_selected_option'] == '0')?'NA':$result['all_selected_option'],
				'email'  				=> $result['email'],
				'fname'   				=> $result['fname'],
				'phone'   				=> $result['phone'],
				'language_id'   		=> $result['language_id'],
				'language_name' 	 	=> $this->model_localisation_language->getLanguage($result['language_id']),
				'store_id'   			=> $result['store_id'],
				'store_url'   			=> $result['store_url'],
				'comment'   			=> html_entity_decode($result['comment']),
				'qty'   				=> $result['qty'],
				'ip'   					=> $result['ip'],
				'enquiry_date' 			=> date("d M Y g:i A", strtotime($result['enquiry_date'])),
				'notify_date'  			=> (!empty($result['notified_date']))? date("d M Y g:i A", strtotime($result['notified_date'])) : ''
			);
		}
		
		$this->response->setOutput($this->load->view('extension/module/'.TEMPLATE_FOLDER.'/hb_oosn_export'.TEMPLATE_EXTN, $data));

	}
	
	public function fixNotifiedDates(){
		$this->db->query("UPDATE `" . DB_PREFIX . "out_of_stock_notify` SET `notified_date` = NULL WHERE `notified_date` = '0000-00-00 00:00:00';");
		$this->session->data['success'] = 'NOTIFIED DATE SET TO NULL WHERE 0000-00-00 00:00:00';
		$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_oosn', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true));
	}
	
	public function install(){
		$this->load->model('extension/module/hb_oosn');
		$this->model_extension_module_hb_oosn->install();
		$this->session->data['success'] = 'This extension has been installed successfully';
		//$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_oosn', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true));
	}
	
	public function uninstallFromExtension(){
		$this->load->model('extension/module/hb_oosn');
		$this->model_extension_module_hb_oosn->uninstall();
		$this->session->data['success'] =  'This extension is uninstalled successfully';
		$this->response->redirect($this->url->link('common/dashboard', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true));
	}
	
	public function upgrade(){
		$this->load->model('extension/module/hb_oosn');
		$this->model_extension_module_hb_oosn->upgrade();
		$this->session->data['success'] = 'Database table has been checked successfully';
		$this->response->redirect($this->url->link(EXTN_ROUTE.'/hb_oosn', TOKEN_NAME.'=' . $this->session->data[TOKEN_NAME], true));
	}
	
	public function install_script(){
		$data['curl_enabled'] = function_exists('curl_version') ? true : false;
		
		$hb_ose_license = $this->request->post['license'];
		$type 			= $this->request->post['type'];
		
		if (version_compare(VERSION,'2.2.0.0','<' )) {
			$theme = $this->config->get('config_template');
		}else if (version_compare(VERSION,'3.0.0.0','>' )) {
			$theme = $this->config->get('config_theme');
		} else {
			$theme = $this->config->get('theme_default_directory');
		}
		
		if ($data['curl_enabled']){
			$post = array(
					'license' 			=> $hb_ose_license,
					'domain'  			=> $_SERVER['HTTP_HOST'],
					'type'	  			=> $type,
					'opencart_version'	=> VERSION,
					'extn_version'		=> EXTN_VERSION,
					'theme'				=> $theme
					);
					
			$ch = curl_init('http://apps.huntbee.com/index.php?route=apps/product_stock_alert/install_style');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			$result = curl_exec($ch);
			curl_close($ch);
			
			$result = json_decode($result, true);
			if (isset($result['ocmod'])){
				$ocmod_version 		= $result['ocmod']['version'];
				$ocmod_name 		= $result['ocmod']['name'];
				$ocmod_code 		= $result['ocmod']['code'];	
				$ocmod_author 		= $result['ocmod']['author'];
				$ocmod_link 		= $result['ocmod']['link'];
				$ocmod_xml			= $result['ocmod']['xml'];
				
				$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = '".$this->db->escape($ocmod_code)."'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "modification SET code = '" . $this->db->escape($ocmod_code) . "', name = '" . $this->db->escape($ocmod_name) . "', author = '" . $this->db->escape($ocmod_author) . "', version = '" . $this->db->escape($ocmod_version) . "', link = '" . $this->db->escape($ocmod_link) . "', xml = '" . $this->db->escape($ocmod_xml) . "', status = '1', date_added = NOW()");
				
				$json['success'] = 'Installation Successful! Go to Extensions > Modification. Click on Refresh Modification button';
			}else if(isset($result['error'])){
				$json['error'] = $result['error'];
			}else{
				$json['error'] = 'Installation Failed. Please contact support!';
			}
		}else{
			$json['error'] = 'CURL function is not enabled in your server. Please contact your hosting support to enable curl function.';
		}
				
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function enable_script(){
		$this->db->query("UPDATE " . DB_PREFIX . "modification SET status = 1 WHERE `code` = 'huntbee_stock_notify_pro_style_ocmod'");
		$json['success'] = 'Add-on Modification Enabled Successfully';
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function uninstall_script(){
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_stock_notify_pro_style_ocmod'");
		$json['success'] = 'Add-on Modification Removed Successfully';
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function customer_email_template() {
		$template = '<table align="center" bgcolor="#f9f9f9" cellpadding="10px" width="90%">
	<tbody>
		<tr>
			<td>
			<p>Hi {customer_name},</p>

			<p>The product {product_name} {option} Model: {model}, you requested for is now available for ordering.</p>

			<table cellpadding="10">
				<tbody>
					<tr>
						<td>{show_image}</td>
						<td><b>{product_name}</b><br />
						Model: {model}<br />
						<br />
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tbody>
								<tr>
									<td align="center" bgcolor="#f9f9f9" style="padding: 15px 5px 15px 5px;">
									<table border="0" cellpadding="0" cellspacing="0">
										<tbody>
											<tr>
												<td align="center" bgcolor="#5ED0AA" style="border-radius: 3px;"><a href="#" style="font-size: 14px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 5px 8px; border-radius: 2px; border: 1px solid #4BA687; display: inline-block;" target="_blank" href="{link}">BUY NOW! Limited Stock !</a></td>
											</tr>
										</tbody>
									</table>
									</td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>

			<p>Regards,<br>{store_name}</p>
			</td>
		</tr>
	</tbody>
</table>';
		
		return $template;
	}
	
	public function confirmation_email_template() {
		$template = '<table align="center" bgcolor="#f9f9f9" cellpadding="10px" width="90%">
	<tbody>
		<tr>
			<td>
			<p>Hi {customer_name},</p>
			
			<p>You have successfully subscribed for the below product. You will get an email notification to this email address as soon as the product is back in stock.</p>

			<table cellpadding="10" align="center">
				<tbody>
					<tr>
						<td align="center"><center><a href="{link}">{show_image}</a><center></td>
					</tr>
					<tr>
						<td align="center"><center>
							<b><a href="{link}">{product_name}</a></b><br />
							{option} <br>
							Model: {model}<br /><br /></center>
						</td>
					</tr>
				</tbody>
			</table>
			
			<p>While we get your product back in stock, you can checkout our other amazing products.</p>
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
					<tr>
						<td align="center" bgcolor="#f9f9f9" style="padding: 20px 30px 20px 30px;">
						<table border="0" cellpadding="0" cellspacing="0">
							<tbody>
								<tr>
									<td align="center" bgcolor="#5ED0AA" style="border-radius: 3px;"><a href="#" style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 2px; border: 1px solid #4BA687; display: inline-block;" target="_blank">Continue Shopping</a></td>
								</tr>
							</tbody>
						</table>
						</td>
					</tr>
				</tbody>
			</table>
			
			<p>Regards,<br>{store_name}</p>
			</td>
		</tr>
	</tbody>
</table>';
		
		return $template;
	}
		
}
?>