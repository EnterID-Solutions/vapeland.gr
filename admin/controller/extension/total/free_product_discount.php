<?php
class ControllerExtensionTotalFreeProductDiscount extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/free_product_discount');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('total_free_product_discount', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true));
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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/free_product_discount', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/total/free_product_discount', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true);

		// Source Category - products that customer must purchase
		if (isset($this->request->post['total_free_product_discount_source_category'])) {
			$data['total_free_product_discount_source_category'] = $this->request->post['total_free_product_discount_source_category'];
		} else {
			$data['total_free_product_discount_source_category'] = $this->config->get('total_free_product_discount_source_category');
		}

		// Target Category - free products category
		if (isset($this->request->post['total_free_product_discount_target_category'])) {
			$data['total_free_product_discount_target_category'] = $this->request->post['total_free_product_discount_target_category'];
		} else {
			$data['total_free_product_discount_target_category'] = $this->config->get('total_free_product_discount_target_category');
		}

		// Ratio - how many source products needed for one free product
		if (isset($this->request->post['total_free_product_discount_ratio'])) {
			$data['total_free_product_discount_ratio'] = $this->request->post['total_free_product_discount_ratio'];
		} else {
			$data['total_free_product_discount_ratio'] = $this->config->get('total_free_product_discount_ratio');
		}

		// Maximum free products per order
		if (isset($this->request->post['total_free_product_discount_max_free'])) {
			$data['total_free_product_discount_max_free'] = $this->request->post['total_free_product_discount_max_free'];
		} else {
			$data['total_free_product_discount_max_free'] = $this->config->get('total_free_product_discount_max_free');
		}

		// Status
		if (isset($this->request->post['total_free_product_discount_status'])) {
			$data['total_free_product_discount_status'] = $this->request->post['total_free_product_discount_status'];
		} else {
			$data['total_free_product_discount_status'] = $this->config->get('total_free_product_discount_status');
		}

		// Sort Order
		if (isset($this->request->post['total_free_product_discount_sort_order'])) {
			$data['total_free_product_discount_sort_order'] = $this->request->post['total_free_product_discount_sort_order'];
		} else {
			$data['total_free_product_discount_sort_order'] = $this->config->get('total_free_product_discount_sort_order');
		}

		// Load categories for dropdown selection
		$this->load->model('catalog/category');
		$data['categories'] = $this->model_catalog_category->getCategories(array('sort' => 'name'));

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/total/free_product_discount', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/free_product_discount')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		// Add any additional validation as needed
		if (!isset($this->request->post['total_free_product_discount_source_category']) || empty($this->request->post['total_free_product_discount_source_category'])) {
			$this->error['warning'] = $this->language->get('error_source_category');
		}

		if (!isset($this->request->post['total_free_product_discount_target_category']) || empty($this->request->post['total_free_product_discount_target_category'])) {
			$this->error['warning'] = $this->language->get('error_target_category');
		}

		if (!isset($this->request->post['total_free_product_discount_ratio']) || (int)$this->request->post['total_free_product_discount_ratio'] < 1) {
			$this->error['warning'] = $this->language->get('error_ratio');
		}

		return !$this->error;
	}

	public function install() {
		// Add install logic if needed
	}

	public function uninstall() {
		// Add uninstall logic if needed
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('total_free_product_discount');
	}
}
