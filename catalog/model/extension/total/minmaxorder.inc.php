<?php
if ($this->config->get('minmaxorder_status') && count($this->cart->getProducts())) {
	$classname = 'minmaxorder';
	$this->load->language('extension/total/' . $classname);

	// Get the current customer group
	if (method_exists($this->customer, 'getGroupId')) { // v2.x.x
		$cust_group_id = $this->customer->getGroupId();
	} else { // v1.5.x
		$cust_group_id = $this->customer->getCustomerGroupId();
	}

	if (!$cust_group_id) { $cust_group_id = 0; }

	// Check if the individual customer group status is enabled for the current customer group
	$status = TRUE;
	if (!$this->config->get($classname . '_status_' . $cust_group_id)) {
		$status = FALSE;
	}

	if (!$status) { return; }
	//

	$min_total 		= $this->config->get($classname . '_min_cart_total_' . $cust_group_id);
	$min_total 		= ($min_total) ? $this->currency->format($min_total, $this->config->get('config_currency'), FALSE, FALSE) : '';
	$max_total 		= $this->config->get($classname . '_max_cart_total_' . $cust_group_id);
	$max_total 		= ($max_total) ? $this->currency->format($max_total, $this->config->get('config_currency'), FALSE, FALSE) : '';
	$min_quantity 	= $this->config->get($classname . '_min_cart_quantity_' . $cust_group_id);
	$max_quantity 	= $this->config->get($classname . '_max_cart_quantity_' . $cust_group_id);
	$min_weight 	= $this->config->get($classname . '_min_cart_weight_' . $cust_group_id);
	$max_weight 	= $this->config->get($classname . '_max_cart_weight_' . $cust_group_id);

	if ($this->config->get($classname . '_use_subtotal_' . $cust_group_id)) {
		$cart_total = $this->currency->format($this->cart->getSubTotal(), $this->config->get('config_currency'), FALSE, FALSE);
	} else {
		$cart_total = $this->currency->format($this->cart->getTotal(), $this->config->get('config_currency'), FALSE, FALSE);
	}
	if ($this->config->get($classname . '_ignore_multiples_' . $cust_group_id)) {
		$cart_quantity 	= count($this->cart->getProducts()); // This allows multiple qty of a single item to count as "1" item
	} else {
		$cart_quantity 	= $this->cart->countProducts();
	}

	//Q: Get products and options for custom mod
	/*
	$certain_option_ids = explode(",", $this->config->get($classname . '_option_ids_' . $cust_group_id));
	$bFound = false;
	$total_weight = 0;
	foreach ($this->cart->getProducts() as $product) {
		foreach ($product['option'] as $option => $option_value) {
			if (in_array($option_value['option_value_id'], $certain_option_ids)) {
				if ($this->config->get($classname . '_min_cart_total2_' . $cust_group_id)) {
					$min_total 		= $this->config->get($classname . '_min_cart_total2_' . $cust_group_id);
					$bFound = true;
					break;
				}
			}
		}
		$total_weight += $product['weight'];
	}
	*/
	//


	$cart_weight 	= $this->cart->getWeight();
	//$cart_weight 	= $total_weight; // since cartweight doesn't count items that do not require shipping.

	$error = false;

	// Min Total
	if ($min_total && $min_total > $cart_total) { $error = sprintf($this->language->get('error_min_total'), $this->currency->format($min_total, $this->session->data['currency'], FALSE, TRUE)); }

	// Max Total
	if ($max_total && $max_total < $cart_total) { $error = sprintf($this->language->get('error_max_total'), $this->currency->format($max_total, $this->session->data['currency'], FALSE, TRUE)); }

	// Min Qty
	if ($min_quantity && $min_quantity > $cart_quantity) { $error = sprintf($this->language->get('error_min_quantity'), $min_quantity); }

	// Max Qty
	if ($max_quantity && $max_quantity < $cart_quantity) { $error = sprintf($this->language->get('error_max_quantity'), $max_quantity); }

	// Min Weight
	if ($min_weight && $min_weight > $cart_weight) { $error = sprintf($this->language->get('error_min_weight'), $min_weight); }

	// Max Weight
	if ($max_weight && $max_weight < $cart_weight) { $error = sprintf($this->language->get('error_max_weight'), $max_weight); }

	// Skip Limits for certain products if Location field for product is 'mmopass'
	if ($error) { // only run this if there is an error to bother with
		$skipCnt = 0;
		foreach ($this->cart->getProducts() as $product) {
			$product_qry = $this->db->query("SELECT location FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "'");
			if ($product_qry->row['location'] == 'mmopass') {
				$skipCnt += $product['quantity'];
			}
		}

		// if all items in the cart are mmopass, then bypass.
		if ($skipCnt == $this->cart->countProducts()) {
			$error = false;
		}
	}
	//

	if ($error) {
		$this->session->data['error'] = $error;
		if (!isset($minmaxcartpage) || !$minmaxcartpage) {
			//$this->error['warning'] = $error;
			$this->response->redirect($this->url->link('checkout/cart'));
		}
	} elseif (!empty($this->session->data['error'])) {
		unset($this->session->data['error']);
	}
}
?>