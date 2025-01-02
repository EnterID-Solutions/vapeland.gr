<?php
class ControllerExtensionModuleStoreLocation extends Controller {
    private $error = array();

    public function index(){
      $this->load->language('extension/module/store_location');
      $this->document->setTitle(strip_tags($this->language->get('heading_title')));
      $this->load->model('setting/setting');
      if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
        $this->model_setting_setting->editSetting('module_store_location', $this->request->post);

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
        'text' => strip_tags($this->language->get('heading_title')),
        'href' => $this->url->link('extension/module/store_location', 'user_token=' . $this->session->data['user_token'], true)
      );

      $data['action'] = $this->url->link('extension/module/store_location', 'user_token=' . $this->session->data['user_token'], true);

      $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

      if (isset($this->request->post['module_store_location_status'])) {
  			$data['module_store_location_status'] = $this->request->post['module_store_location_status'];
  		} else {
  			$data['module_store_location_status'] = $this->config->get('module_store_location_status');
  		}



      $data['header'] = $this->load->controller('common/header');
      $data['column_left'] = $this->load->controller('common/column_left');
      $data['footer'] = $this->load->controller('common/footer');

      $this->response->setOutput($this->load->view('extension/module/store_location', $data));
    }

    private function validate() {
      if (!$this->user->hasPermission('modify', 'extension/module/store_location')) {
  			$this->error['warning'] = $this->language->get('error_permission');
  		}

		  return !$this->error;
    }

    public function install(){
      $this->load->model('user/user_group');
      $this->load->model('extension/module/store_location');
      $this->load->model('setting/event');
      $this->model_setting_event->addEvent('store_location_menu', 'admin/view/common/column_left/before', 'extension/module/store_location/eventMenu');
      $this->model_extension_module_store_location->install();
      $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/store_location');
  		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/store_location');

  }

    public function uninstall(){
        $this->load->model('user/user_group');
        $this->load->model('extension/module/store_location');
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('store_location_menu');
        $this->model_extension_module_store_location->uninstall();
        $this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/store_location');
    		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/store_location');


  }

    public function eventMenu($route, &$data) {
  		$store_locationmenu = array();

  		$this->load->language('extension/module/store_location');

  		if ($this->user->hasPermission('access', 'extension/module/store_location')) {
  			$store_locationmenu[] = array(
  				'name'	   => $this->language->get('text_store_location_menu'),
  				'href'     => $this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'], true),
  				'children' => array()
  			);


  			if ($store_locationmenu) {
  				$data['menus'][] = array(
  					'id'       => 'menu-store_location',
  					'icon'	   => 'fa fa-home',
  					'name'	   => $this->language->get('text_parent_menu'),
  					'href'     => '',
  					'children' => $store_locationmenu
  				);
  			}
  	}
  }

  public function store_location(){
    $this->load->model('extension/module/store_location');
    $this->load->language('extension/module/store_location');

    if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

    if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		} else {
			$filter_email = '';
		}

    if (isset($this->request->get['filter_phone'])) {
			$filter_phone = $this->request->get['filter_phone'];
		} else {
			$filter_phone = '';
		}

    if (isset($this->request->get['filter_address'])) {
			$filter_address = $this->request->get['filter_address'];
		} else {
			$filter_address = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

    if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

    if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

    if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_phone'])) {
			$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_address'])) {
			$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
		}

    if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

    if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

    $data['breadcrumbs'] = array();

    $data['breadcrumbs'][] = array(
        'text'      => $this->language->get('text_home'),
    'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        'separator' => false
    );

    $data['breadcrumbs'][] = array(
        'text'      => strip_tags($this->language->get('heading_title_store_location')),
    'href'      => $this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'], true),
        'separator' => ' :: '
    );

		$data['add'] = $this->url->link('extension/module/store_location/add_location', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/store_location/del_location', 'user_token=' . $this->session->data['user_token'] . $url, true);
    $this->document->setTitle(strip_tags($this->language->get('heading_title_store_location')));

    $filter_data = array(
			'filter_name'          => $filter_name,
			'filter_email'         => $filter_email,
			'filter_phone'         => $filter_phone,
			'filter_address'       => $filter_address,
			'filter_status'        => $filter_status,
			'sort'                 => $sort,
			'order'                => $order,
			'start'                => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                => $this->config->get('config_limit_admin')
		);
    $store_location_total = $this->model_extension_module_store_location->getTotalStoreLocations($filter_data);
    $results = $this->model_extension_module_store_location->getStoreLocations($filter_data);
    foreach ($results as $result) {
      $data['store_locations'][] = array(
				'store_location_id'    => $result['store_location_id'],
				'name'         => $result['name'],
				'email'             => $result['email'],
				'phone'            => $result['phone'],
				'address'            => $result['address'],
				'status'           => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'             => $this->url->link('extension/module/store_location/edit_location', 'user_token=' . $this->session->data['user_token'] . '&store_location_id=' . $result['store_location_id'] . $url, true)
			);
    }
    $data['user_token'] = $this->session->data['user_token'];

    if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

    if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

    if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

    if (isset($this->request->get['filter_phone'])) {
			$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_address'])) {
			$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

    if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_email'] = $this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'] . '&sort=email' . $url, true);
		$data['sort_phone'] = $this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'] . '&sort=phone' . $url, true);
		$data['sort_address'] = $this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'] . '&sort=address' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = '';

    if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

    if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

    if (isset($this->request->get['filter_phone'])) {
			$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_address'])) {
			$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $store_location_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($store_location_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($store_location_total - $this->config->get('config_limit_admin'))) ? $store_location_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $store_location_total, ceil($store_location_total / $this->config->get('config_limit_admin')));

		$data['$filter_name'] = $filter_name;
		$data['filter_email'] = $filter_email;
		$data['filter_phone'] = $filter_phone;
		$data['filter_address'] = $filter_address;
		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;



    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/module/store_location_list', $data));
  }


  private function store_location_form(){
    $this->load->language('extension/module/store_location');
    $this->document->setTitle(strip_tags($this->language->get('heading_title_store_location')));
    $this->load->model('extension/module/store_location');

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
    if(isset($this->request->get['store_location_id'])){
      $store_location_id = $this->request->get['store_location_id'];
    } else {
      $store_location_id= 0;
    }
    if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['phone'])) {
			$data['error_phone'] = $this->error['phone'];
		} else {
			$data['error_phone'] = '';
		}

		if (isset($this->error['address'])) {
			$data['error_address'] = $this->error['address'];
		} else {
			$data['error_address'] = '';
		}

    $data['fa_fonts'] = $this->model_extension_module_store_location->getFontAwesome();
    $data['store_tip_row'] = array();
    foreach($data['languages'] as $clang){
      $data['store_tip_row'][$clang['language_id']]=0;
    }

    if (isset($this->request->get['store_location_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$store_location_info = $this->model_extension_module_store_location->getStore_location($this->request->get['store_location_id']);
      $store_tips = $this->model_extension_module_store_location->getStoreTips($this->request->get['store_location_id']);
      $store_location_info['store_tips'] = $store_tips;
		}

    if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($store_location_info)) {
			$data['name'] = $store_location_info['name'];
		} else {
			$data['name'] = '';
		}

    if (isset($this->request->post['phone'])) {
			$data['phone'] = $this->request->post['phone'];
		} elseif (!empty($store_location_info)) {
			$data['phone'] = $store_location_info['phone'];
		} else {
			$data['phone'] = '';
		}

    if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($store_location_info)) {
			$data['email'] = $store_location_info['email'];
		} else {
			$data['email'] = '';
		}

    if (isset($this->request->post['address'])) {
			$data['address'] = $this->request->post['address'];
		} elseif (!empty($store_location_info)) {
			$data['address'] = $store_location_info['address'];
		} else {
			$data['address'] = '';
		}

    if (isset($this->request->post['google_map'])) {
			$data['google_map'] = $this->request->post['google_map'];
		} elseif (!empty($store_location_info)) {
			$data['google_map'] = $store_location_info['google_map'];
		} else {
			$data['google_map'] = '';
		}

    if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($store_location_info)) {
			$data['status'] = $store_location_info['status'];
		} else {
			$data['status'] = '';
		}

    if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($store_location_info)) {
			$data['sort_order'] = $store_location_info['sort_order'];
		} else {
			$data['sort_order'] = '';
		}

    if (isset($this->request->post['store_tips'])) {
			$data['store_tips'] = $this->request->post['store_tips'];
		} elseif (!empty($store_location_info)) {
			$data['store_tips'] = $store_location_info['store_tips'];
		} else {
			$data['store_tips'] = array();
		}

    if (isset($this->request->post['monday_hours'])) {
			$data['monday_hours'] = $this->request->post['monday_hours'];
		} elseif ($this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'monday')) {
			$data['monday_hours'] = $this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'monday');
		} else {
			$data['monday_hours'] = false;
		}

    if (isset($this->request->post['tuesday_hours'])) {
			$data['tuesday_hours'] = $this->request->post['tuesday_hours'];
		} elseif ($this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'tuesday')) {
			$data['tuesday_hours'] = $this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'tuesday');
		} else {
			$data['tuesday_hours'] = false;
		}

    if (isset($this->request->post['wednesday_hours'])) {
			$data['wednesday_hours'] = $this->request->post['wednesday_hours'];
		} elseif ($this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'wednesday')) {
			$data['wednesday_hours'] = $this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'wednesday');
		} else {
			$data['wednesday_hours'] = false;
		}

    if (isset($this->request->post['thursday_hours'])) {
			$data['thursday_hours'] = $this->request->post['thursday_hours'];
		} elseif ($this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'thursday')) {
			$data['thursday_hours'] = $this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'thursday');
		} else {
			$data['thursday_hours'] =false;
		}

    if (isset($this->request->post['friday_hours'])) {
			$data['friday_hours'] = $this->request->post['friday_hours'];
		} elseif ($this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'friday')) {
			$data['friday_hours'] = $this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'friday');
		} else {
			$data['friday_hours'] = false;
		}

    if (isset($this->request->post['saturday_hours'])) {
			$data['saturday_hours'] = $this->request->post['saturday_hours'];
		} elseif ($this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'saturday')) {
			$data['saturday_hours'] = $this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'saturday');
		} else {
			$data['saturday_hours'] = false;
		}

    if (isset($this->request->post['sunday_hours'])) {
			$data['sunday_hours'] = $this->request->post['sunday_hours'];
		} elseif ($this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'sunday')) {
			$data['sunday_hours'] = $this->model_extension_module_store_location->getOpeningTimes($store_location_id, 'sunday');
		} else {
			$data['sunday_hours'] = false;
		}


    if (isset($this->request->post['store_location_description'])) {
			$data['store_location_description'] = $this->request->post['store_location_description'];
		} elseif (isset($this->request->get['store_location_id'])) {
			$data['store_location_description'] = $this->model_extension_module_store_location->getStoreLocationDescriptions($this->request->get['store_location_id']);
		} else {
			$data['store_location_description'] = array();
		}

    // Image
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($store_location_info)) {
			$data['image'] = $store_location_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($store_location_info) && is_file(DIR_IMAGE . $store_location_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($store_location_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

    // Images
		if (isset($this->request->post['store_image'])) {
			$store_images = $this->request->post['store_image'];
		} elseif (isset($this->request->get['store_location_id'])) {
			$store_images = $this->model_extension_module_store_location->getStoreImages($this->request->get['store_location_id']);
		} else {
			$store_images = array();
		}

		$data['store_images'] = array();

		foreach ($store_images as $store_image) {
			if (is_file(DIR_IMAGE . $store_image['image'])) {
				$image = $store_image['image'];
				$thumb = $store_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['store_images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' => $store_image['sort_order']
			);
		}


    $data['cancel'] = $this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'], true);


    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');

    $this->response->setOutput($this->load->view('extension/module/store_location_form', $data));
  }

  public function add_location(){
    $this->load->language('extension/module/store_location');
    $this->document->setTitle(strip_tags($this->language->get('heading_title_store_location')));
    $this->load->model('extension/module/store_location');

    $this->document->addScript('view/javascript/mlselect.js');
		$this->document->addStyle('view/stylesheet/multiple-select.css');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate_store_location()) {
      $this->model_extension_module_store_location->addStoreLocation($this->request->post);
      $this->session->data['success'] = $this->language->get('text_success');
      $this->response->redirect($this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'], true));
    }
    $data['action'] = $this->url->link('extension/module/store_location/add_location', 'user_token=' . $this->session->data['user_token'], true);

    $this->store_location_form();
  }

  public function edit_location(){
    $this->load->language('extension/module/store_location');
    $this->document->setTitle(strip_tags($this->language->get('heading_title_store_location')));
    $this->load->model('extension/module/store_location');

    $this->document->addScript('view/javascript/mlselect.js');
		$this->document->addStyle('view/stylesheet/multiple-select.css');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate_store_location()) {
      $this->model_extension_module_store_location->editStoreLocation($this->request->get['store_location_id'], $this->request->post);
      $this->session->data['success'] = $this->language->get('text_success');
      $this->response->redirect($this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'], true));
    }

    $data['action'] = $this->url->link('extension/module/store_location/edit_location', 'user_token=' . $this->session->data['user_token'].'&store_location_id='.$this->request->get['store_location_id'], true);
    $this->store_location_form();
  }


  private function validate_store_location(){
    if (!$this->user->hasPermission('modify', 'extension/module/store_location')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}


    foreach ($this->request->post['store_location_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}
		}

    if ((utf8_strlen($this->request->post['phone']) < 1) || (utf8_strlen(trim($this->request->post['phone'])) > 20)) {
			$this->error['phone'] = $this->language->get('error_phone');
		}

    if ((utf8_strlen($this->request->post['address']) < 1) || (utf8_strlen(trim($this->request->post['address'])) > 64)) {
			$this->error['address'] = $this->language->get('error_address');
		}


    if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
  }

  private function validate_pistopoihsh(){
    if (!$this->user->hasPermission('modify', 'extension/module/store_location')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

    if ((utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen(trim($this->request->post['name'])) > 32)) {
			$this->error['name'] = $this->language->get('error_name');
		}

    if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
  }

  public function del_location(){
    $this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/store_location');

		if (isset($this->request->post['selected'])) {
			foreach ($this->request->post['selected'] as $store_location_id) {
				$this->model_extension_module_store_location->deleteStoreLocation($store_location_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_phone'])) {
				$url .= '&filter_phone=' . urlencode(html_entity_decode($this->request->get['filter_phone'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_address'])) {
				$url .= '&filter_address=' . urlencode(html_entity_decode($this->request->get['filter_address'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/module/store_location/store_location', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->store_location();
  }
}

?>
