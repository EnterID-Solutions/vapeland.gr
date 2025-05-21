<?php
class ControllerExtensionModuleProductOosn extends Controller {
	public function index() {
		//get all details
		$product_id 		= (int)$this->request->post['product_id'];
		$customer_email 	= $this->request->post['email'];
		$qty = (isset($this->request->post['stock_qty']))? (int)$this->request->post['stock_qty'] : 1;
		
		$selected_option_value = html_entity_decode($this->cleanstrings($this->request->post['selected_option_value'])); //id values
		
		$selected_option = strip_tags($this->cleanstrings($this->request->post['selected_option'])); //text value
		$selected_option = preg_replace('/(\s)+/', ' ', $selected_option);
		
		$all_selected_option = strip_tags($this->cleanstrings($this->request->post['all_selected_option'])); //text value
		$all_selected_option = preg_replace('/(\s)+/', ' ', $all_selected_option);

		
		if (isset($this->request->post['name'])){
			$fname = strip_tags($this->cleanstrings($this->request->post['name']));
		}else {
			$fname = '';
		}
		
		if (isset($this->request->post['phone'])){
			$phone = $this->request->post['phone'];
			$phone = preg_replace('/\D/', '', $phone);
		}else {
			$phone = '';
		}
		
		if (isset($this->request->post['stock_comment'])){
			$comment = strip_tags($this->request->post['stock_comment']);
			$comment = htmlentities($comment);
		}else {
			$comment = '';
		}

		$this->load->model('catalog/product');
		
		$product_info = $this->model_catalog_product->getProduct($product_id);
		
		$product_name = html_entity_decode($product_info['name'], ENT_QUOTES, 'UTF-8');	
		//date_default_timezone_set('Asia/Calcutta');  //time zone for India
		$datetime = date("Y-m-d H:i:s");

		$language_id 	= $this->config->get('config_language_id');
		$store_url 		= $this->config->get('config_url');
		$store_id 		= $this->config->get('config_store_id');
		$ip 			= ($this->request->server['REMOTE_ADDR'])? $this->request->server['REMOTE_ADDR'] : '';
		
		//form validation	
		$json = array();
			
		if ($product_id == 0) {
			$json['notify_error'] = html_entity_decode($this->config->get('hb_oosn_t_invalid'.$language_id));
		}
		
		if ((strlen(trim($customer_email)) < 5) || (utf8_strlen($customer_email) > 96) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
			$json['notify_error'] = html_entity_decode($this->config->get('hb_oosn_t_error_email'.$language_id));
		}
		
		if ($this->config->get('hb_oosn_name_enable') == 'y') {
			if ((utf8_strlen($fname) < 3) || (utf8_strlen($fname) > 20)) {
				$json['notify_error'] = html_entity_decode($this->config->get('hb_oosn_t_error_name'.$language_id));
			}
		}
		
		if ($this->config->get('hb_oosn_mobile_enable') == 'y') {
			if ((utf8_strlen($phone) < $this->config->get('hb_oosn_mobile_validation_min')) || (utf8_strlen($phone) > $this->config->get('hb_oosn_mobile_validation_max'))) {
				$json['notify_error'] = html_entity_decode($this->config->get('hb_oosn_t_error_phone'.$language_id));
			}
		}
		
		if ($this->config->get('hb_oosn_comments_enable') == 'y') {
			if (utf8_strlen($comment) > 3000) {
				$json['notify_error'] = html_entity_decode($this->config->get('hb_oosn_t_error_comment'.$language_id));
			}
		}
				
		//captcha
		if ($this->config->get('hb_oosn_enable_captcha') == 1){
			if(isset($this->request->post['gcaptcha']) && !empty($this->request->post['gcaptcha'])){
				$recaptcha = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($this->config->get('google_captcha_secret')) . '&response=' . $this->request->post['gcaptcha'] . '&remoteip=' . $this->request->server['REMOTE_ADDR']);
				$recaptcha = json_decode($recaptcha, true);
				if (!$recaptcha['success']) {
					$json['notify_error'] = html_entity_decode($this->config->get('hb_oosn_t_error_captcha'.$language_id));
				}
			}else{
				$json['notify_error'] = html_entity_decode($this->config->get('hb_oosn_t_warn_captcha'.$language_id));
			}
		}
		
		//check duplicate entry
		if (!$json){
			$counts = $this->db->query("SELECT count(*) as count FROM `" . DB_PREFIX . "out_of_stock_notify` WHERE product_id = '".(int)$product_id."' and selected_option_value = '".$this->db->escape($selected_option_value)."' and selected_option = '".$this->db->escape($selected_option)."' and all_selected_option = '".$this->db->escape($all_selected_option)."' and email = '".$this->db->escape($customer_email)."' and fname = '".$this->db->escape($fname)."' and phone = '".$this->db->escape($phone)."' and language_id = '".(int)$language_id."' and notified_date IS NULL");
			if ($counts->row['count'] > 0){
				$json['notify_warning'] = html_entity_decode($this->config->get('hb_oosn_t_duplicate'.$language_id));
			}
		}
		
		if (!$json){
			$this->db->query("INSERT INTO `" . DB_PREFIX . "out_of_stock_notify` (product_id, selected_option_value, selected_option, all_selected_option, email, fname, phone, qty, language_id, store_id, store_url, comment, ip, enquiry_date) 
			VALUES ('".(int)$product_id."','".$this->db->escape($selected_option_value)."','".$this->db->escape($selected_option)."','".$this->db->escape($all_selected_option)."','".$this->db->escape($customer_email)."','".$this->db->escape($fname)."','".$this->db->escape($phone)."', '".(int)$qty."','".(int)$language_id."','".(int)$store_id."','".$this->db->escape($store_url)."','".$this->db->escape($comment)."','".$this->db->escape($ip)."','".$this->db->escape($datetime)."')");
			
			if ($this->config->get('hb_oosn_admin_notify') == 'y'){ //hey admin, do you want to get notifed when customer subscribes?
				$data['product_id'] 			= $product_id;
				$data['product_url'] 			= $this->url->link('product/product', 'product_id=' . $product_id);
				$data['product_name'] 			= $product_name;
				$data['sku'] 					= $product_info['sku'];
				$data['model']					= $product_info['model'];
				$data['qty'] 					= $qty;
				$data['email'] 					= $customer_email;
				$data['fname'] 					= $fname;
				$data['phone'] 					= $phone;
				$data['comment']				= $comment;
				$data['selected_option'] 		= $selected_option;
				$data['all_selected_option'] 	= $all_selected_option;
				$data['language_id'] 			= $language_id;
				$data['store_id'] 				= $store_id;
				$data['store_url'] 				= $store_url;
				$data['enquiry_date']			= $datetime;
				$data['ip'] 					= $ip;
				$data['admin_email_subject'] 	= 'Customer '.$fname.' is looking for product '.$product_name;
				
				if (version_compare(VERSION,'3.0.0.0','>=' )) {
					$admin_email_body = $this->load->view('extension/module/oc3/notify_admin', $data);
				}else if (version_compare(VERSION,'2.2.0.0','<' )) {
					$admin_email_body = $this->load->view('default/template/extension/module/oc2/notify_admin.tpl', $data);
				}else{
					$admin_email_body = $this->load->view('extension/module/oc2/notify_admin', $data);
				}
			 	
				$this->hbemail($this->config->get('hb_oosn_admin_email'), $this->config->get('config_email'), $data['admin_email_subject'], $admin_email_body, $this->config->get('config_name')); //syntax : (to , from , subject , body, store name)
			
				if ($this->config->get('hb_oosn_confirm_email_enable')) {
					$this->send_confirmation_email($data);
				}
			
			}
			
			$json['success'] = html_entity_decode($this->config->get('hb_oosn_t_success'.$language_id));
		}
			
		if (isset($this->request->server['HTTP_ORIGIN'])) {
			$this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
			$this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
			$this->response->addHeader('Access-Control-Max-Age: 1000');
			$this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	private function send_confirmation_email($data){
		$this->load->model('tool/image');
		
		$store_id = $data['store_id'];
		$store_name 			= $this->config->get('config_name'); //Default value
		$from_email 			=  $this->config->get('config_email'); //Default value
		
		$store_query = $this->db->query("SELECT store_id, name from " . DB_PREFIX . "store WHERE `url` = '".$this->db->escape($data['store_url'])."' OR `store_id` = '".(int)$data['store_id']."' LIMIT 1");
		if ($store_query->row) {
			$store_name = $store_query->row['name'];
			$from_email_query = $this->db->query("SELECT `value` from " . DB_PREFIX . "setting WHERE `key` = 'config_email' and store_id = '".(int)$store_id."' LIMIT 1");
			if ($from_email_query->row){
				$from_email 	=  $from_email_query->row['value'];
			}
		}
		
		$this->load->model('catalog/product');
		$product_info = $this->model_catalog_product->getProduct($data['product_id']);
		
		if (!empty($product_info['image'])){
			$image 		= $this->model_tool_image->resize($product_info['image'],  $this->config->get('hb_oosn_product_image_w'), $this->config->get('hb_oosn_product_image_h'));
			$image 		= str_replace(' ', '%20', $image);
			$show_image = '<img src="'.$image.'" alt="'.$data['product_name'].'" />';
		} else{
			$image 		= $this->model_tool_image->resize('no_image.png',  $this->config->get('hb_oosn_product_image_w'), $this->config->get('hb_oosn_product_image_h'));
			$show_image = '<img src="'.$image.'" alt="Image Not Available" />';
		}
		
		$mail_body = $this->config->get('hb_oosn_confirm_email_body_'.$data['language_id']);
		
		$mail_body = str_replace("{product_name}",$data['product_name'],$mail_body);
		$mail_body = str_replace("{customer_name}",$data['fname'],$mail_body);
		$mail_body = str_replace("{model}",$data['model'],$mail_body);
		$mail_body = str_replace("{option}",$data['selected_option'],$mail_body);
		$mail_body = str_replace("{all_option}",$data['all_selected_option'],$mail_body);
		$mail_body = str_replace("{image_url}",$image,$mail_body);
		$mail_body = str_replace("{show_image}",$show_image,$mail_body);
		$mail_body = str_replace("{link}",$data['product_url'],$mail_body);
		$mail_body = str_replace("{store_name}",$store_name,$mail_body);
		$mail_body = str_replace("{store_url}",$data['store_url'],$mail_body);
		
		$mail_subject =  $this->config->get('hb_oosn_confirm_email_subject_'.$data['language_id']);
		$mail_subject = str_replace("{product_name}",$data['product_name'],$mail_subject);
		
		$message  = '<html dir="ltr" lang="en">' . "\n";
		$message .= '  <head>' . "\n";
		$message .= '    <title>' . $mail_subject . '</title>' . "\n";
		$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
		$message .= '  </head>' . "\n";
		$message .= '  <body>' . html_entity_decode($mail_body, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
		$message .= '</html>' . "\n";

		if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			$this->hbemail($data['email'], $from_email, $mail_subject, $message, $store_name);
			$this->addlog('Confirmation Email sent to '.$data['email']);
		}
	}
	
	public function autonotify(){
		$key = (isset($this->request->get['authkey']))? $this->request->get['authkey'] : '';
		if ($this->authenticate($key) === false)	{
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 403 Forbidden');
			die('You cannot access this page');
		}
		$product_in_stock = false; //initialize
		$this->load->model('extension/module/hb_oosn');

		$records = $this->model_extension_module_hb_oosn->getUniqueId(); //gets all null notified date records
				
		if ($records) { // records are there
			foreach ($records as $record){
				$oosn_id 				= $record['oosn_id'];	
				$product_id 			= $record['product_id'];
				$selected_option 		= $record['selected_option'];  // selected option text format 
				$selected_option_value 	= $record['selected_option_value'];// selected option values json
				
				$hb_oosn_stock_status 	= $this->config->get('hb_oosn_stock_status');
				$hb_oosn_product_qty 	= $this->config->get('hb_oosn_product_qty');
				
				$stockstatus = $this->model_extension_module_hb_oosn->getStockStatus($product_id); //checks the overall product quantity and stock status id
				
				if ($stockstatus){
					$qty 						= $stockstatus['quantity'];
					$stock_status_id_product 	= $stockstatus['stock_status_id'];
					$stock_status_id 			= ($hb_oosn_stock_status == '0') ? 0 : $stock_status_id_product;
					
					if (($selected_option == '0') or (empty($selected_option)) ) { //option check , options dont exists
						if (($qty >= $hb_oosn_product_qty) and ($stock_status_id == $hb_oosn_stock_status)){
							$this->send_notification_to_customer($oosn_id);
							$product_in_stock = true;
						}
					} else { //option exsists	
						$option_instock_flag = 1;	//initialize
						$options = explode('|',$selected_option_value);
						foreach ($options as $option){
							$json 			= $option;
							$obj 			= json_decode($json, true);
							
							$product_id 				= $obj['pi'];
							$product_option_id 			= $obj['poi'];
							$product_option_value_id 	= $obj['povi'];
							
							$no_selection_option = 0; //there could be options which has no option value / qty. eg. textfield
							
							$optionstockstatus = $this->model_extension_module_hb_oosn->getOptionStockStatus($product_id, $product_option_value_id, $product_option_id);
							
							if ($optionstockstatus){
								$optionstockstatus_qty = $optionstockstatus['quantity'];
							}else {
								$optionstockstatus_qty = 0;
								if ($this->model_extension_module_hb_oosn->validateOptionExists($product_id, $product_option_id) === true) {
									$no_selection_option = 1;
								}else {
									$this->addlog('Option not found! - product_id :'.$product_id.', product_option_value_id : '.$product_option_value_id.', product_option_id :'.$product_option_id);
								}
							}
							
							if (($optionstockstatus_qty >= $hb_oosn_product_qty) or ($no_selection_option == 1)) {
								$option_instock_flag = $option_instock_flag * 1; 
							}else{
								$option_instock_flag = 0; //setting flag to 0 as one of the customer selected option is out of stock
							}
						} //forach options loop end
						if ($option_instock_flag == 1) {
							$this->send_notification_to_customer($oosn_id);
							$product_in_stock = true;
						}
					}// option exists, end else
				}else {
					echo $deleteentry = $this->checkNdeleteEntry($oosn_id, $product_id); //$stockstatus query has empty record which means the product do not exists
				}
				
			}// end of foreach looping of all unique records
			
			if ($product_in_stock === false){
				echo 'No product in the list detected as In-Stock. Ensure product quantity is updated. <br>';
				$this->addlog('No product in the list detected as In-Stock. Ensure product quantity is updated.');
			}
		}else {// end of if $records	
			echo 'No pending record found! <br>';
			$this->addlog('No pending record found!');
		}
		echo '<b>>> JOB SUCCESSFULL!</b>';			
	}
	
	public function force_send_email(){
		$key = (isset($this->request->get['authkey']))? $this->request->get['authkey'] : '';
		
		if ($this->authenticate($key) === false)	{
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 403 Forbidden');
			die('You cannot access this page');
		}else{
		    if (isset($this->request->post['selected'])) {
		        foreach ($this->request->post['selected'] as $oosn_id) {
			        $this->send_notification_to_customer($oosn_id);
		        }
		    }
		}
		echo '>> Operation Successful! Check work log for details';
	}
	
	private function send_notification_to_customer($oosn_id){		
		$this->load->model('extension/module/hb_oosn');
		$this->load->model('tool/image');
		$record = $this->model_extension_module_hb_oosn->getRecord($oosn_id);
		
		if ($record) {
			$product_id 		= $record['product_id'];
			$customer_email 	= $record['email'];
			$customer_name 		= (empty($record['fname'])) ? '' : $record['fname'];
			$customer_phone 	= $record['phone'];
			
			if (strlen($record['selected_option']) > 3){
				$selected_option 		= $record['selected_option'];
				$all_selected_option 	= $record['all_selected_option'];
			}else {
				$selected_option = $all_selected_option = '';
			}
			
			$customer_language_id 	= (empty($record['language_id']) or $record['language_id'] == 0) ? 1 : $record['language_id']; 
			$store_url 				= (empty($record['store_url'])) ? $this->config->get('config_url') : $record['store_url'];
			$store_id				= (int)$record['store_id'];
			
			$store_name 			= $this->config->get('config_name'); //Default value
			$from_email 			=  $this->config->get('config_email'); //Default value
			
			$store_query = $this->db->query("SELECT store_id, name from " . DB_PREFIX . "store WHERE `url` = '".$this->db->escape($store_url)."' OR `store_id` = '".(int)$store_id."' LIMIT 1");
			if ($store_query->row) {
				$store_name = $store_query->row['name'];
				$from_email_query = $this->db->query("SELECT `value` from " . DB_PREFIX . "setting WHERE `key` = 'config_email' and store_id = '".(int)$store_id."' LIMIT 1");
				if ($from_email_query->row){
					$from_email 	=  $from_email_query->row['value'];
				}
			}
			
			$product_details 		= $this->model_extension_module_hb_oosn->getProductDetails($product_id, $customer_language_id);
			if ($product_details) {
				$pname 			= $product_details['name'];
				$campaign 		= $this->config->get('hb_oosn_campaign');
				$product_link 	= $this->url->link('product/product', 'product_id=' . $product_id.$campaign);
				
				$pmodel 		= $product_details['model'];
				
				if (!empty($product_details['image'])){
					$image 		= $this->model_tool_image->resize($product_details['image'],  $this->config->get('hb_oosn_product_image_w'), $this->config->get('hb_oosn_product_image_h'));
					$image 		= str_replace(' ', '%20', $image);
					
					$show_image = '<img src="'.$image.'" alt="'.$pname.'" />';
				} else{
					$image 		= $this->model_tool_image->resize('no_image.png',  $this->config->get('hb_oosn_product_image_w'), $this->config->get('hb_oosn_product_image_h'));
					$show_image = '<img src="'.$image.'" alt="Image Not Available" />';
				}
				
				$mail_body = $this->config->get('hb_oosn_customer_email_body_'.$customer_language_id);
				$mail_body = str_replace("{product_name}",$pname,$mail_body);
				$mail_body = str_replace("{customer_name}",$customer_name,$mail_body);
				$mail_body = str_replace("{model}",$pmodel,$mail_body);
				$mail_body = str_replace("{option}",$selected_option,$mail_body);
				$mail_body = str_replace("{all_option}",$all_selected_option,$mail_body);
				$mail_body = str_replace("{image_url}",$image,$mail_body);
				$mail_body = str_replace("{show_image}",$show_image,$mail_body);
				$mail_body = str_replace("{link}",$product_link,$mail_body);
				$mail_body = str_replace("{store_name}",$store_name,$mail_body);
				$mail_body = str_replace("{store_url}",$store_url,$mail_body);
				
				$mail_subject =  $this->config->get('hb_oosn_customer_email_subject_'.$customer_language_id);
				$mail_subject = str_replace("{product_name}",$pname,$mail_subject);
				
				$message  = '<html dir="ltr" lang="en">' . "\n";
				$message .= '  <head>' . "\n";
				$message .= '    <title>' . $mail_subject . '</title>' . "\n";
				$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
				$message .= '  </head>' . "\n";
				$message .= '  <body>' . html_entity_decode($mail_body, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
				$message .= '</html>' . "\n";
		
				if (filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
					$this->hbemail($customer_email, $from_email, $mail_subject, $message, $store_name);
					echo 'Notification Email sent to '.$customer_email.' <br>';
					$this->addlog('Notification Email sent to '.$customer_email);
				}
	
				if ($this->config->get('hb_oosn_sms_enable') == 'y'){ //SMS START
					$this->addlog('SMS Option is Enabled. Attempting to Send SMS.');
					$sms =  $this->config->get('hb_oosn_customer_sms_body_'.$customer_language_id);
					//$pname = (strlen($pname) > 20) ? substr($pname,0,20).'...' : $pname;
					//$pmodel = (strlen($pmodel) > 10) ? substr($pmodel,0,10).'...' : $pmodel;
					//$selected_option = (strlen($selected_option) > 15) ? substr($selected_option,0,14).'...' : $selected_option;
					//$all_selected_option = (strlen($all_selected_option) > 15) ? substr($all_selected_option,0,14).'...' : $all_selected_option;
	
					$sms = str_replace("{product_name}",$pname,$sms);
					$sms = str_replace("{link}",$product_link,$sms);
					$sms = str_replace("{model}",$pmodel,$sms);
					$sms = str_replace("{option}",$selected_option,$sms);
					$sms = str_replace("{all_option}",$all_selected_option,$sms);
					$sms = preg_replace('/\s+/', ' ',$sms);
					//$sms = rawurlencode($sms);
					
					$url = html_entity_decode($this->config->get('hb_oosn_sms_http_api'));
					$url = str_replace("{to}",$customer_phone,$url);
					$url = str_replace("{sms}",$sms,$url);
					$returnedoutput = file_get_contents($url);			
				} // SMS END
	
				$this->model_extension_module_hb_oosn->updatenotifieddate($oosn_id);				
			}else{
				$this->addlog('Product Details for Product ID '.$product_id.' Not Found!');
			}
		}else{
			$this->addlog('Record ID '.$oosn_id.' Not Found or the particular record is already notified!');
		}
	}

	public function checkstock() {
		$json = array();
	
		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if (isset($this->request->post['quantity']) && ((int)$this->request->post['quantity'] >= $product_info['minimum'])) {
				$quantity = (int)$this->request->post['quantity'];
			} else {
				$quantity = $product_info['minimum'] ? $product_info['minimum'] : 1;
			}

			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();
			}

			$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}

			$product_not_in_stock 				= false;
			$customer_selected_all_options 		= array();
			$customer_selected_outstock_option 	= array();
			$user_selected_option_values 		= array();
			
			$hb_oosn_stock_status 	= $this->config->get('hb_oosn_stock_status');
			$hb_oosn_product_qty 	= $this->config->get('hb_oosn_product_qty');
			$language_id 			= $this->config->get('config_language_id');

			if (!$json and !empty($option)) { //program first checks if the product has options
				foreach ($product_options as $product_option) {
					$product_option_id 			= $product_option['product_option_id']; //ID of the option
					//check qty of each option value of option
					$product_option_value 					= $option[$product_option_id]; //ID of the value of the option selected by the user ... this is an important unique property 
					$customer_selected_option_values 		= (array)$product_option_value;
					
					foreach ($customer_selected_option_values as $product_option_value_id){
						$result = $this->db->query("SELECT a.quantity, b.name, c.stock_status_id from ".DB_PREFIX."product_option_value a, ".DB_PREFIX."option_value_description b, ".DB_PREFIX."product c where a.option_value_id = b.option_value_id and a.product_id = c.product_id and a.product_id = '".(int)$product_id."' and a.product_option_id = '".(int)$product_option_id."' and a.product_option_value_id = '".(int)$product_option_value_id."' and b.language_id = '" . (int)$language_id . "' LIMIT 1");
						if ($result->row){
							$option_qty 		= (int)$result->row['quantity'];
							$option_name		= $result->row['name'];
							$stock_status_id 	= $result->row['stock_status_id']; //this is overall product stock status id from product table
							
							if ($hb_oosn_stock_status == '0'){ 
								$stock_status_id = 0;
							}
							//VALIDATION PART
							$customer_selected_all_options[]  = $product_option['name'] . ' : '. $option_name;

							if (($option_qty < $hb_oosn_product_qty) and ($stock_status_id == $hb_oosn_stock_status)){ 
								$product_not_in_stock = true;
								$customer_selected_outstock_option[] 	= $product_option['name'] . ' : '. $option_name;
							}
						}else{
							$customer_selected_all_options[]  = $product_option['name'] . ' : '. $product_option_value_id;
						}
						
						$array_selected_options 		= array('pi'=> $product_id, 'poi' => $product_option_id, 'povi' => $product_option_value_id);
						$user_selected_option_values[]  = json_encode($array_selected_options);
					}
				}
					
				if ($product_not_in_stock === true){
				    $customer_selected_outstock_option_string   = implode(', ', $customer_selected_outstock_option);
					$customer_selected_all_options_string		= implode(', ', $customer_selected_all_options);		
					$user_selected_option_values_string 		= implode('|', $user_selected_option_values);
					
					$json['hb_notify_option']['selectedoption']  = html_entity_decode(str_replace('{selected_option}',$customer_selected_outstock_option_string,$this->config->get('hb_oosn_t_info_opt'.$language_id)));
					$json['hb_notify_option']['pid'] 			= $product_id;
					$json['hb_notify_option']['val'] 			= '<input type="hidden" id="option_values_inlineform" value="'.htmlentities($user_selected_option_values_string, ENT_QUOTES).'"><input type="hidden" id="selected_option_inlineform" value="'.htmlentities($customer_selected_outstock_option_string, ENT_QUOTES).'"><input type="hidden" id="all_selected_option_inlineform" value="'.htmlentities($customer_selected_all_options_string, ENT_QUOTES).'">';
				}
			} //option check ends here
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));	
	}
	
	public function emaildemandedproducts(){
		$key = (isset($this->request->get['authkey']))? $this->request->get['authkey'] : '';
		if ($this->authenticate($key) === false)	{
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 403 Forbidden');
			die('You cannot access this page');
		}
		
		$this->load->model('extension/module/hb_oosn');
		$data['demands'] = $this->model_extension_module_hb_oosn->getDemandedList();
		
		$email_subject = 'Demanded Out-of-Stock Products in your store';
		
		if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$email_body = $this->load->view('extension/module/oc3/notify_demands', $data);
		}else if (version_compare(VERSION,'2.2.0.0','<' )) {
			$email_body = $this->load->view('default/template/extension/module/oc2/notify_demands.tpl', $data);
		}else{
			$email_body = $this->load->view('extension/module/oc2/notify_demands', $data);
		}
		
		$this->hbemail($this->config->get('hb_oosn_admin_email'), $this->config->get('config_email'), $email_subject, $email_body, $this->config->get('config_name'));
		$this->addlog('Demanded product email sent to Administrator');
		echo 'Demanded product email sent to Administrator';
	}
	
	private function hbemail($to, $from, $subject, $body, $store_name){
		if (version_compare(VERSION,'2.0.1.1','<=' )) {
			$mail = new Mail($this->config->get('config_mail'));
		}else {
			if (version_compare(VERSION,'2.3.0.2','>' )) {
				$mail = new Mail($this->config->get('config_mail_engine'));
			}else{
				$mail = new Mail();
			}
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');			
		}
		
		$mail->setTo($to);
		$mail->setFrom($from);
		$mail->setSender($store_name);
		$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
		$mail->setHtml(wordwrap($body,50));
		$mail->send();
	}
	
	private function cleanstrings($data){
		$data = str_replace('=','',$data);
		$data = str_replace('*','',$data);
		$data = str_replace('(','',$data);
		$data = str_replace(')','',$data);
		$data = str_replace("'","",$data);
		return $data;
	}
	
	private function authenticate($key){
		$authkey = $this->config->get('hb_oosn_authkey');
		if ($key == $authkey){
			return true;
		}else{		
			return false;
		}
	}
	
	private function checkNdeleteEntry($oosn_id = 0, $product_id = 0){
		$this->load->model('extension/module/hb_oosn');
		$is_product_exists = $this->model_extension_module_hb_oosn->checkproduct($product_id);
		if (!$is_product_exists){
			$this->model_extension_module_hb_oosn->deleterecord($oosn_id);
			$output = 'It seems the product with product ID '.$product_id.' does not exists anymore. Deleting the customer alert entry with ID '.$oosn_id.'<br>';
			$this->addlog($output);
			return $output;
		}else{
			$output = 'The product with product ID '.$product_id.' does exists. However the product status might have been disabled. Please check your product entry<br>';
			return $output;
		}
	}
	
	private function addlog($text = ''){
		if ($this->config->get('hb_oosn_logs')){
			if (!file_exists(DIR_LOGS . 'hb_notify')) {
				mkdir(DIR_LOGS . 'hb_notify', 0777, true);
			}
			
			$month = date('M').'-'.date('Y');
			$filename = strtolower($month).'.txt';
			$file = DIR_LOGS . 'hb_notify/'.$filename;
			
			$fp = fopen($file, 'a');
			fwrite($fp, "\r\n".date('Y-m-d G:i:s') . ' - ' .$text);
			fclose($fp);
		}
	}

}
?>