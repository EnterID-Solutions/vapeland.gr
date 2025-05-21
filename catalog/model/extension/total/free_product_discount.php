<?php
class ModelExtensionTotalFreeProductDiscount extends Model {
	/**
	 * Main method that calculates the discount
	 * @param array $total The cart totals
	 */
	public function getTotal(&$total) {
        $this->log->write("Free Product Discount - getTotal called");
		if (!$this->config->get('total_free_product_discount_status')) {
			return;
		}
		
		$this->load->language('extension/total/free_product_discount');
		
		// Get the configuration
		$source_category_id = $this->config->get('total_free_product_discount_source_category');
		$target_category_id = $this->config->get('total_free_product_discount_target_category');
		$ratio = (int)$this->config->get('total_free_product_discount_ratio');
		$max_free = (int)$this->config->get('total_free_product_discount_max_free');
		
		// Validate configuration
		if (!$source_category_id || !$target_category_id) {
			$this->log->write("Free Product Discount - Source or target category not configured");
			return;
		}
		
		if ($ratio < 1) $ratio = 1;
		
		// Get cart products
		$cart_products = $this->cart->getProducts();
		if (empty($cart_products)) {
			$this->log->write("Free Product Discount - Cart is empty");
			$this->session->data['eligible_free_products'] = 0;
			return;
		}
		
		// Get products by category
		$cart_product_ids = $this->getCartProductIds($cart_products);
		list($source_product_ids, $target_product_ids) = $this->getProductsByCategories(
			$cart_product_ids, $source_category_id, $target_category_id);
		
		// Exit if no qualifying products
		if (empty($source_product_ids) || empty($target_product_ids)) {
			$this->log->write("Free Product Discount - No qualifying products found");
			$this->session->data['eligible_free_products'] = 0;
			return;
		}
		
		// Count products from source category
		$source_product_count = $this->countSourceProducts($cart_products, $source_product_ids);
		
		// Calculate eligibility first
		$eligible_free_products = $this->calculateEligibleFreeProducts($source_product_count, $ratio, $max_free);
		
		// Exit early if customer is not eligible for any free products
		if ($eligible_free_products <= 0) {
			$this->log->write("Free Product Discount - Customer not eligible for any free products");
            $this->session->data['eligible_free_products'] = 0;
			return;
		}

        // Store eligible free products in session for display
        $this->session->data['eligible_free_products'] = $eligible_free_products;
		
		// Get target products sorted by price (cheapest first)
		$target_products = $this->getTargetProductsSortedByPrice($cart_products, $target_product_ids);
		
		// Apply discount for the cheapest products automatically
		$this->applyAutomaticDiscount($total, $target_products, $eligible_free_products);
	}
	/**
	 * Get all product IDs from the cart
	 * 
	 * @param array $cart_products Products in the cart
	 * @return array List of product IDs
	 */
	private function getCartProductIds($cart_products) {
		$product_ids = array();
		foreach ($cart_products as $product) {
			$product_ids[] = (int)$product['product_id'];
		}
		return $product_ids;
	}
	
	/**
	 * Get products from specific categories
	 * 
	 * @param array $product_ids List of product IDs to check
	 * @param int $source_category_id Source category ID
	 * @param int $target_category_id Target category ID
	 * @return array Array containing [source_product_ids, target_product_ids]
	 */
	private function getProductsByCategories($product_ids, $source_category_id, $target_category_id) {
		if (empty($product_ids)) {
			return [[], []];
		}
		
		// Get all category assignments for these products at once
		$category_query = $this->db->query("SELECT product_id, category_id 
			FROM " . DB_PREFIX . "product_to_category 
			WHERE product_id IN (" . implode(',', $product_ids) . ")
			AND category_id IN (" . (int)$source_category_id . ", " . (int)$target_category_id . ")");
			
		// Organize products by category
		$source_product_ids = array();
		$target_product_ids = array();
		
		foreach ($category_query->rows as $row) {
			if ($row['category_id'] == $source_category_id) {
				$source_product_ids[] = $row['product_id'];
			}
			if ($row['category_id'] == $target_category_id) {
				$target_product_ids[] = $row['product_id'];
			}
		}
		
		$this->log->write("Free Product Discount - Found " . count($source_product_ids) . " source products and " 
			. count($target_product_ids) . " target products");
			
		return [$source_product_ids, $target_product_ids];
	}
	
	/**
	 * Count how many products from source category are in the cart
	 * 
	 * @param array $cart_products Products in the cart
	 * @param array $source_product_ids List of product IDs from source category
	 * @return int Total quantity of source products
	 */
	private function countSourceProducts($cart_products, $source_product_ids) {
		$source_product_count = 0;
		
		foreach ($cart_products as $product) {
			if (in_array($product['product_id'], $source_product_ids)) {
				$source_product_count += $product['quantity'];
				$this->log->write("Free Product Discount - Counting source product: " 
					. $product['name'] . ", quantity: " . $product['quantity']);
			}
		}
		
		return $source_product_count;
	}
	
	/**
	 * Get target products sorted by price (cheapest first)
	 * 
	 * @param array $cart_products Products in the cart
	 * @param array $target_product_ids List of product IDs from target category
	 * @return array Array of target products sorted by price
	 */
	private function getTargetProductsSortedByPrice($cart_products, $target_product_ids) {
		$target_products = array();
		
		// Extract all products from target category
		foreach ($cart_products as $product) {
			if (in_array($product['product_id'], $target_product_ids)) {
				// For each unit of the product
				for ($i = 0; $i < $product['quantity']; $i++) {
					$target_products[] = array(
						'cart_id' => $product['cart_id'],
						'product_id' => $product['product_id'],
						'name' => $product['name'],
						'price' => $product['price'],
						'tax_class_id' => $product['tax_class_id'],
						'unit_index' => $i // To track which unit of a product this is
					);
				}
				
				$this->log->write("Free Product Discount - Found target product: " 
					. $product['name'] . ", price: " . $product['price'] . ", quantity: " . $product['quantity']);
			}
		}
		
		// Sort by price (cheapest first)
		usort($target_products, function($a, $b) {
			return $a['price'] <=> $b['price'];
		});
		
		return $target_products;
	}
	
	/**
	 * Calculate how many free products the customer is eligible for
	 * 
	 * @param int $source_product_count Number of source products
	 * @param int $ratio How many source products needed for one free product
	 * @param int $max_free Maximum number of free products allowed
	 * @return int Number of eligible free products
	 */
	private function calculateEligibleFreeProducts($source_product_count, $ratio, $max_free) {
		// Calculate based on ratio
		$eligible_free_products = floor($source_product_count / $ratio);
		
		// Apply maximum limit if set
		if ($max_free > 0 && $eligible_free_products > $max_free) {
			$eligible_free_products = $max_free;
		}
		
		$this->log->write("Free Product Discount - Customer eligible for " 
			. $eligible_free_products . " free products");
			
		return $eligible_free_products;
	}
	
	/**
	 * Apply discount automatically to the cheapest products from target category
	 * 
	 * @param array $total Cart totals
	 * @param array $target_products Products from target category sorted by price
	 * @param int $eligible_free_count Number of products eligible to be free
	 */
	private function applyAutomaticDiscount(&$total, $target_products, $eligible_free_count) {
		// Only proceed if we have qualifying products and eligibility
		if ($eligible_free_count <= 0 || empty($target_products)) {
			return;
		}
		
		$discount_total = 0;
		$free_product_names = array();
		$free_product_details = array();
		
		// Apply discount to the cheapest products (they're already sorted)
		$products_to_discount = min(count($target_products), $eligible_free_count);
		
		for ($i = 0; $i < $products_to_discount; $i++) {
			$product = $target_products[$i];
			$discount_total += $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
			//$discount_total += $product['price'];
			
			$this->log->write("Free Product Discount - Automatically applying discount for: " 
				. $product['name'] . ", price: " . $product['price']);
				
			// Track products getting discounted for display
			$key = $product['name'];
			if (!isset($free_product_details[$key])) {
				$free_product_details[$key] = array(
					'name' => $product['name'],
					'count' => 1,
					'price' => $product['price']
				);
			} else {
				$free_product_details[$key]['count']++;
			}
		}
		
		// Store the details of free products for display
		if (!empty($free_product_details)) {
			$this->session->data['free_product_details'] = array_values($free_product_details);
		}
		
		// Only apply discount if we have qualifying products
		if ($discount_total > 0) {
			$this->log->write("Free Product Discount - Total automatic discount: " . $discount_total);
			
			// Create a better title showing which products are free
			$title = $this->language->get('text_free_product_discount');
			
			$total['totals'][] = array(
				'code'       => 'free_product_discount',
				'title'      => $title,
				'value'      => -$discount_total,
				'sort_order' => $this->config->get('total_free_product_discount_sort_order')
			);
			
			$total['total'] -= $discount_total;
		}
	}
}
