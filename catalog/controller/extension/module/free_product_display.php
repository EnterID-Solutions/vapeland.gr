<?php
class ControllerExtensionModuleFreeProductDisplay extends Controller {
	public function index() {
		$this->load->language('extension/total/free_product_discount');
		$this->load->language('extension/module/free_product_display');
		
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		
		$data['heading_title'] = $this->language->get('heading_title');
		
		// Get eligible free products count from session
		$data['eligible_free_products'] = isset($this->session->data['eligible_free_products']) ? 
			(int)$this->session->data['eligible_free_products'] : 0;

		
		// Get configuration
		$source_category_id = $this->config->get('total_free_product_discount_source_category');
		$target_category_id = $this->config->get('total_free_product_discount_target_category');
		
		// If no categories configured, don't display anything
		if (!$source_category_id || !$target_category_id) {
			return '';
		}
		
		// Display currently applied free products
		$data['free_products_title'] = $this->language->get('text_free_products_applied');
		$data['free_products'] = array();
		
		if (isset($this->session->data['free_product_details']) && !empty($this->session->data['free_product_details'])) {
			foreach ($this->session->data['free_product_details'] as $product) {
				$data['free_products'][] = array(
					'name' => $product['name'],
					'count' => $product['count'],
					'text' => sprintf($this->language->get('text_free_product_item'), 
						$product['count'], $product['name'])
				);
			}
		}
		
		// Get products from the target category
		$data['available_products_title'] = $this->language->get('text_available_free_products');
		$data['available_products'] = array();
		
		$this->load->model('catalog/category');
		
		$filter_data = array(
			'filter_category_id' => $target_category_id,
			'filter_sub_category' => true,
			'sort'               => 'p.price',
			'order'              => 'ASC',
			'start'              => 0,
			'limit'              => 10 // Limit to 10 products
		);
		
		$products = $this->model_catalog_product->getProducts($filter_data);
		
		if ($products) {
			foreach ($products as $product) {
				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], 80, 80);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 80, 80);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}
				
				$data['available_products'][] = array(
					'product_id'  => $product['product_id'],
					'thumb'       => $image,
					'name'        => $product['name'],
					'price'       => $price,
					'href'        => $this->url->link('product/product', 'product_id=' . $product['product_id']),
					'add_to_cart' => $this->url->link('checkout/cart/add', 'product_id=' . $product['product_id'])
				);
			}
		}
		
		$data['add_to_cart_text'] = $this->language->get('button_add_to_cart');
		$data['text_no_products'] = $this->language->get('text_no_products');
		
		return $this->load->view('extension/module/free_product_display', $data);
	}
}
