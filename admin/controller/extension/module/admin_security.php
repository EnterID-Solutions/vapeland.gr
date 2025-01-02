<?php

class ControllerExtensionModuleAdminSecurity extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/module/admin_security');
        $this->load->model('extension/module/admin_security');
        $this->load->model('setting/setting');
        $this->load->model('setting/extension');

        $this->document->setTitle(strip_tags($this->language->get('heading_title')));

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_admin_security', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', true));
        }

        $data['heading_title'] = strip_tags($this->language->get('heading_title'));


        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', true),
            'separator' => ' :: '
        );

        $data['breadcrumbs'][] = array(
            'text' => strip_tags($this->language->get('heading_title')),
            'href' => $this->url->link('extension/module/admin_security', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: '
        );

        $data['action'] = $this->url->link('extension/module/admin_security', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=module', true);

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->post['module_admin_security_status'])) {
            $data['module_admin_security_status'] = $this->request->post['module_admin_security_status'];
        } else {
            $data['module_admin_security_status'] = $this->config->get('module_admin_security_status');
        }


        if (isset($this->request->post['module_admin_security_whitelist_ips'])) {
            $data['module_admin_security_whitelist_ips'] = $this->request->post['module_admin_security_whitelist_ips'];
        } elseif ($this->config->get('module_admin_security_whitelist_ips')) {
            $data['module_admin_security_whitelist_ips'] = $this->config->get('module_admin_security_whitelist_ips');
        } else {
            $data['module_admin_security_whitelist_ips'] = array();
        }


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/admin_security', $data));
    }

    public function install()
    {
        $this->load->model('extension/module/admin_security');
        $this->load->model('setting/event');
        $this->model_extension_module_admin_security->install();
        $this->model_setting_event->addEvent('admin_security_menu', 'admin/view/common/column_left/before', 'extension/module/admin_security/eventMenu');
    }

    public function uninstall()
    {
        $this->load->model('extension/module/admin_security');
        $this->load->model('setting/event');
        $this->model_extension_module_admin_security->uninstall();
        $this->model_setting_event->deleteEventByCode('admin_security_menu');
    }

    public function eventMenu($route, &$data)
    {
        $admin_security = array();

        $this->load->language('extension/module/admin_security');

        if ($this->user->hasPermission('access', 'extension/module/admin_security')) {
            $admin_security[] = array(
                    'name'	   => $this->language->get('text_admin_security_banned'),
                    'href'     => $this->url->link('extension/module/admin_security/banned', 'user_token=' . $this->session->data['user_token'], true),
                    'children' => array()
                  );

            if ($admin_security) {
                $data['menus'][] = array(
                      'id'       => 'menu-admin-security',
                      'icon'	   => 'fa fa-ban fw',
                      'name'	   => $this->language->get('text_admin_security_menu'),
                      'href'     => '',
                      'children' => $admin_security
                    );
            }
        }
    }

    public function banned(){
      $this->load->language('extension/module/admin_security');
      $this->load->model('extension/module/admin_security');

      $data['header'] = $this->load->controller('common/header');

      if (isset($this->request->get['filter_ip'])) {
  			$filter_ip = $this->request->get['filter_ip'];
  		} else {
  			$filter_ip = '';
  		}

      if (isset($this->request->get['page'])) {
  			$page = $this->request->get['page'];
  		} else {
  			$page = 1;
  		}

  		$url = '';

      if (isset($this->request->get['filter_ip'])) {
  			$url .= '&filter_ip=' . urlencode(html_entity_decode($this->request->get['filter_ip'], ENT_QUOTES, 'UTF-8'));
  		}

      if (isset($this->request->get['page'])) {
  			$url .= '&page=' . $this->request->get['page'];
  		}

      $data['breadcrumbs'][] = array(
  			'text' => $this->language->get('text_home'),
  			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
  		);

  		$data['breadcrumbs'][] = array(
  			'text' => $this->language->get('heading_title'),
  			'href' => $this->url->link('extension/module/admin_security', 'user_token=' . $this->session->data['user_token'], true)
  		);

  		$data['breadcrumbs'][] = array(
  			'text' => $this->language->get('heading_title_banned'),
  			'href' => $this->url->link('extension/module/admin_security/banned', 'user_token=' . $this->session->data['user_token'] . $url, true)
  		);

      if (isset($this->session->data['success'])) {
  			$data['success'] = $this->session->data['success'];

  			unset($this->session->data['success']);
  		} else {
  			$data['success'] = '';
  		}

      if (isset($this->session->data['banned_error'])) {
  			$data['error_warning'] = $this->session->data['banned_error'];

  			unset($this->session->data['banned_error']);
  		} else {
  			$data['error_warning'] = '';
  		}

  		$filter_data = array(
  			'filter_ip'	      => $filter_ip,
  			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
  			'limit'           => $this->config->get('config_limit_admin')
  		);

      $data['settings'] = $this->url->link('extension/module/admin_security', 'user_token='.$this->request->get['user_token'], true);

      $banned_ips_total = $this->model_extension_module_admin_security->getBannedTotal($filter_data);
      $banned_ips = $this->model_extension_module_admin_security->getBanned($filter_data);

      $data['banned_ips'] = array();
      foreach ($banned_ips as $banned_ip) {

        $data['banned_ips'][]=array(
          'ip_id'       =>  $banned_ip['user_ban_id'],
          'ip'          =>  $banned_ip['ip'],
          'date_added'  =>  $banned_ip['date_added']
        );
      }

      $data['user_token'] = $this->session->data['user_token'];
      $url = '';

  		if (isset($this->request->get['filter_ip'])) {
  			$url .= '&filter_ip=' . urlencode(html_entity_decode($this->request->get['filter_ip'], ENT_QUOTES, 'UTF-8'));
  		}

      if (isset($this->request->get['page'])) {
  			$url .= '&page=' . $this->request->get['page'];
  		}

      $data['delete'] = $this->url->link('extension/module/admin_security/delete', 'user_token='.$this->request->get['user_token'].$url, true);

      $url = '';

  		if (isset($this->request->get['filter_ip'])) {
  			$url .= '&filter_ip=' . urlencode(html_entity_decode($this->request->get['filter_ip'], ENT_QUOTES, 'UTF-8'));
  		}

      $pagination = new Pagination();
  		$pagination->total = $banned_ips_total;
  		$pagination->page = $page;
  		$pagination->limit = $this->config->get('config_limit_admin');
  		$pagination->url = $this->url->link('extension/module/admin_security/banned', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

  		$data['pagination'] = $pagination->render();

  		$data['results'] = sprintf($this->language->get('text_pagination'), ($banned_ips_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($banned_ips_total - $this->config->get('config_limit_admin'))) ? $banned_ips_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $banned_ips_total, ceil($banned_ips_total / $this->config->get('config_limit_admin')));

  		$data['filter_ip'] = $filter_ip;

      $data['column_left'] = $this->load->controller('common/column_left');
      $data['footer'] = $this->load->controller('common/footer');
      $this->response->setOutput($this->load->view('extension/module/admin_security_banned', $data));
    }

    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/admin_security')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }


    public function delete() {
  		$this->load->language('extension/module/admin_security');
      $this->load->model('extension/module/admin_security');

  		$this->document->setTitle($this->language->get('heading_title'));


  		if (isset($this->request->post['selected'])) {
  			foreach ($this->request->post['selected'] as $ip_id) {
  				$this->model_extension_module_admin_security->deleteIp($ip_id);
  			}
  			$this->session->data['success'] = $this->language->get('text_success_ips_removed');

  		} else {
        $this->session->data['banned_error'] = $this->language->get('text_failed_remove');
      }

      $url = '';

      if (isset($this->request->get['page'])) {
        $url .= '&page=' . $this->request->get['page'];
      }

      $this->response->redirect($this->url->link('extension/module/admin_security/banned', 'user_token=' . $this->session->data['user_token'] . $url, true));
  	}

    public function remove_ip(){
      $this->load->model('extension/module/admin_security');
      $this->load->language('extension/module/admin_security');
      if(!empty($this->request->post['ipid'])){
        $this->model_extension_module_admin_security->deleteIp($this->request->post['ipid']);
        $this->session->data['success'] = $this->language->get('text_success_ip_removed');
      } else {
        $this->session->data['banned_error'] = $this->language->get('text_faild_ip_removed');
      }
    }
}
