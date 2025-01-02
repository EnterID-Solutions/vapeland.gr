<?php
require_once(DIR_SYSTEM . 'library/equotix/canned_messages/equotix.php');
class ControllerExtensionModuleCannedMessages extends Equotix {
	protected $version = '2.0.1';
	protected $code = 'canned_messages';
	protected $extension = 'Canned Messages';
	protected $extension_id = '4';
	protected $purchase_url = 'canned-messages';
	protected $purchase_id = '21335';
	protected $error = array();

	public function index() {
		$this->load->language('extension/module/canned_messages');

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$data['heading_title'] = $this->language->get('heading_title');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_notify'] = $this->language->get('entry_notify');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_comment'] = $this->language->get('entry_comment');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_refresh'] = $this->language->get('text_refresh');
		$data['text_are_you_sure'] = $this->language->get('text_are_you_sure');

        $data['button_add'] = $this->language->get('button_add');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
		
		$data['tab_general'] = $this->language->get('tab_general');

		$data['button_cancel'] = $this->language->get('button_cancel');

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_module'),
			'href'      => $this->url->link('extension/marketplace', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/canned_messages', 'user_token=' . $this->session->data['user_token'], true)
   		);
        
        $this->load->model('extension/module/canned_messages');
        
        $messages = $this->model_extension_module_canned_messages->getMessages();
        
        $data['messages'] = array();
        
        foreach ($messages as $message) {
            $data['messages'][] = array(
                'canned_message_id' => $message['canned_message_id'],
                'name'              => $message['name'],
                'notify'            => $message['notify'],
                'notify_text'       => $message['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
                'order_status'      => $message['order_status'],
                'order_status_id'   => $message['order_status_id'],
                'comment'           => nl2br($message['comment']),
                'comment_raw'       => $message['comment'],
                'sort_order'        => $message['sort_order']
            );
        }
        
        $this->load->model('localisation/order_status');
        
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		$data['cancel'] = $this->url->link('extension/marketplace', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->generateOutput('extension/module/canned_messages', $data);
	}
    
    public function add() {
        if ($this->validate()) {
            $this->load->model('extension/module/canned_messages');
            
            $this->model_extension_module_canned_messages->addMessage($this->request->post);
        }
        
        $this->response->setOutput(json_encode(array('success' => true)));
    }
    
    public function edit() {
        if ($this->validate() && isset($this->request->get['canned_message_id'])) {
            $this->load->model('extension/module/canned_messages');
            
            $this->model_extension_module_canned_messages->editMessage($this->request->get['canned_message_id'], $this->request->post);
        }
        
        $this->response->setOutput(json_encode(array('success' => true)));
    }
    
    public function delete() {
        if ($this->validate() && isset($this->request->get['canned_message_id'])) {
            $this->load->model('extension/module/canned_messages');
            
            $this->model_extension_module_canned_messages->deleteMessage($this->request->get['canned_message_id']);
        }
        
        $this->response->setOutput(json_encode(array('success' => true)));
    }
    
    public function info() {
        $this->load->model('extension/module/canned_messages');
        
        $json = $this->model_extension_module_canned_messages->getMessage($this->request->get['canned_message_id']);
        
        if (isset($this->request->get['order_id']) && $this->validated()) {
            $this->load->model('sale/order');
            
            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
            
            $search = array(
                '{firstname}',
                '{lastname}',
                '{email}',
                '{telephone}',
                '{order_id}',
                '{date_added}',
                '{payment_method}',
                '{shipping_method}'
            );
            
            $replace = array(
                $order_info['firstname'],
                $order_info['lastname'],
                $order_info['email'],
                $order_info['telephone'],
                $order_info['order_id'],
                date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                $order_info['payment_method'],
                $order_info['shipping_method'],
            );
            
            $json['comment'] = str_replace($search, $replace, $json['comment']);
        }
        
        $this->response->setOutput(json_encode($json));
    }
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/canned_messages') || !$this->validated()) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
    
    public function install() {
		if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
			return;
		}
		
		$this->load->model('setting/setting');

		$data = array(
			'module_canned_messages_status' => true
		);

		$this->model_setting_setting->editSetting('module_canned_messages', $data); 
		
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "canned_message` (
			  `canned_message_id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(255) COLLATE utf8_bin NOT NULL,
			  `notify` tinyint(1) NOT NULL,
			  `order_status_id` int(11) NOT NULL,
			  `comment` TEXT COLLATE utf8_bin NOT NULL,
			  `sort_order` int(3) NOT NULL,
			  PRIMARY KEY (`canned_message_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;
		");
    }
    
    public function uninstall() {
		if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
			return;
		}
		
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "canned_message");
    }
}