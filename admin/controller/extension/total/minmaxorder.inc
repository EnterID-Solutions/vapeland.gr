<?php

		$data['tab_class'] = 'vtabs';

		$data['tabs'][] = array(
			'id'		=> 'tab_general',
			'title'		=> $this->language->get('tab_general')
		);

		$data['tabs'][] = array(
			'id'		=> 'tab_support',
			'title'		=> $this->language->get('tab_support')
		);

		$data['fields'][] = array(
			'tab'			=> 'tab_general',
			'entry' 		=> $this->language->get('entry_status'),
			'type'			=> 'select',
			'name' 			=> 'status',
			'value' 		=> (isset($this->request->post['status'])) ? $this->request->post['status'] : $this->config->get($classname . '_status'),
			'required' 		=> false,
			'options'		=> array(
				'0' => $this->language->get('text_disabled'),
				'1' => $this->language->get('text_enabled')
			),
			'help'			=> ($this->language->get('help_status') != 'help_status' ? $this->language->get('help_status') : ''),
			'tooltip'		=> ($this->language->get('tooltip_status') != 'tooltip_status' ? $this->language->get('tooltip_status') : '')
		);

		foreach ($customer_groups as $id => $name) {

			$data['tabs'][] = array(
				'id'		=> 'tab_' . $id,
				'title'		=> $this->language->get('text_customer_group') . $name
			);

			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry' 		=> $this->language->get('entry_status'),
				'type'			=> 'select',
				'name' 			=> 'status_' . $id,
				'value' 		=> (isset($this->request->post['status_' . $id])) ? $this->request->post['status_' . $id] : $this->config->get($classname . '_status_' . $id),
				'required' 		=> false,
				'options'		=> array(
					'0' => $this->language->get('text_disabled'),
					'1' => $this->language->get('text_enabled')
				),
				'help'			=> ($this->language->get('help_status') != 'help_status' ? $this->language->get('help_status') : ''),
				'tooltip'		=> ($this->language->get('tooltip_status') != 'tooltip_status' ? $this->language->get('tooltip_status') : '')
			);

			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry'			=> $this->language->get('entry_min_cart_total'),
				'type'			=> 'text',
				'name' 			=> 'min_cart_total_' . $id,
				'value' 		=> (isset($this->request->post['min_cart_total_' . $id])) ? $this->request->post['min_cart_total_' . $id] : $this->config->get($classname . '_min_cart_total_' . $id),
				'required'		=> false,
				'help'			=> ($this->language->get('help_min_cart_total') != 'help_min_cart_total' ? $this->language->get('help_min_cart_total') : ''),
				'tooltip'		=> ($this->language->get('tooltip_min_cart_total') != 'tooltip_min_cart_total' ? $this->language->get('tooltip_min_cart_total') : '')
			);
/*
			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry'			=> $this->language->get('entry_option_ids'),
				'type'			=> 'text',
				'name' 			=> 'option_ids_' . $id,
				'value' 		=> (isset($this->request->post['option_ids_' . $id])) ? $this->request->post['option_ids_' . $id] : $this->config->get($classname . '_option_ids_' . $id),
				'required'		=> false,
				'help'			=> ($this->language->get('help_option_ids') != 'help_option_ids' ? $this->language->get('help_option_ids') : ''),
				'tooltip'		=> ($this->language->get('tooltip_option_ids') != 'tooltip_option_ids' ? $this->language->get('tooltip_option_ids') : '')
			);
			
			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry'			=> $this->language->get('entry_min_cart_total2'),
				'type'			=> 'text',
				'name' 			=> 'min_cart_total2_' . $id,
				'value' 		=> (isset($this->request->post['min_cart_total2_' . $id])) ? $this->request->post['min_cart_total2_' . $id] : $this->config->get($classname . '_min_cart_total2_' . $id),
				'required'		=> false,
				'help'			=> ($this->language->get('help_min_cart_total2') != 'help_min_cart_total2' ? $this->language->get('help_min_cart_total2') : ''),
				'tooltip'		=> ($this->language->get('tooltip_min_cart_total2') != 'tooltip_min_cart_total2' ? $this->language->get('tooltip_min_cart_total2') : '')
			);
*/
			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry'			=> $this->language->get('entry_max_cart_total'),
				'type'			=> 'text',
				'name' 			=> 'max_cart_total_' . $id,
				'value' 		=> (isset($this->request->post['max_cart_total_' . $id])) ? $this->request->post['max_cart_total_' . $id] : $this->config->get($classname . '_max_cart_total_' . $id),
				'required'		=> false,
				'help'			=> ($this->language->get('help_max_cart_total') != 'help_max_cart_total' ? $this->language->get('help_max_cart_total') : ''),
				'tooltip'		=> ($this->language->get('tooltip_max_cart_total') != 'tooltip_max_cart_total' ? $this->language->get('tooltip_max_cart_total') : '')
			);

			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry' 		=> $this->language->get('entry_use_subtotal'),
				'type'			=> 'select',
				'name' 			=> 'use_subtotal_' . $id,
				'value' 		=> (isset($this->request->post['use_subtotal_' . $id])) ? $this->request->post['use_subtotal_' . $id] : $this->config->get($classname . '_use_subtotal_' . $id),
				'required' 		=> false,
				'options'		=> array(
					'0' => $this->language->get('text_disabled'),
					'1' => $this->language->get('text_enabled')
				),
				'help'			=> ($this->language->get('help_use_subtotal') != 'help_use_subtotal' ? $this->language->get('help_use_subtotal') : ''),
				'tooltip'		=> ($this->language->get('tooltip_use_subtotal') != 'tooltip_use_subtotal' ? $this->language->get('tooltip_use_subtotal') : '')
			);

			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry'			=> $this->language->get('entry_min_cart_quantity'),
				'type'			=> 'text',
				'name' 			=> 'min_cart_quantity_' . $id,
				'value' 		=> (isset($this->request->post['min_cart_quantity_' . $id])) ? $this->request->post['min_cart_quantity_' . $id] : $this->config->get($classname . '_min_cart_quantity_' . $id),
				'required'		=> false,
				'help'			=> ($this->language->get('help_min_cart_quantity') != 'help_min_cart_quantity' ? $this->language->get('help_min_cart_quantity') : ''),
				'tooltip'		=> ($this->language->get('tooltip_min_cart_quantity') != 'tooltip_min_cart_quantity' ? $this->language->get('tooltip_min_cart_quantity') : '')
			);

			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry'			=> $this->language->get('entry_max_cart_quantity'),
				'type'			=> 'text',
				'name' 			=> 'max_cart_quantity_' . $id,
				'value' 		=> (isset($this->request->post['max_cart_quantity_' . $id])) ? $this->request->post['max_cart_quantity_' . $id] : $this->config->get($classname . '_max_cart_quantity_' . $id),
				'required'		=> false,
				'help'			=> ($this->language->get('help_max_cart_quantity') != 'help_max_cart_quantity' ? $this->language->get('help_max_cart_quantity') : ''),
				'tooltip'		=> ($this->language->get('tooltip_max_cart_quantity') != 'tooltip_max_cart_quantity' ? $this->language->get('tooltip_max_cart_quantity') : '')
			);

			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry' 		=> $this->language->get('entry_ignore_multiples'),
				'type'			=> 'select',
				'name' 			=> 'ignore_multiples_' . $id,
				'value' 		=> (isset($this->request->post['ignore_multiples_' . $id])) ? $this->request->post['ignore_multiples_' . $id] : $this->config->get($classname . '_ignore_multiples_' . $id),
				'required' 		=> false,
				'options'		=> array(
					'0' => $this->language->get('text_disabled'),
					'1' => $this->language->get('text_enabled')
				),
				'help'			=> ($this->language->get('help_ignore_multiples') != 'help_ignore_multiples' ? $this->language->get('help_ignore_multiples') : ''),
				'tooltip'		=> ($this->language->get('tooltip_ignore_multiples') != 'tooltip_ignore_multiples' ? $this->language->get('tooltip_ignore_multiples') : '')
			);

			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry'			=> $this->language->get('entry_min_cart_weight'),
				'type'			=> 'text',
				'name' 			=> 'min_cart_weight_' . $id,
				'value' 		=> (isset($this->request->post['min_cart_weight_' . $id])) ? $this->request->post['min_cart_weight_' . $id] : $this->config->get($classname . '_min_cart_weight_' . $id),
				'required'		=> false,
				'help'			=> ($this->language->get('help_min_cart_weight') != 'help_min_cart_weight' ? $this->language->get('help_min_cart_weight') : ''),
				'tooltip'		=> ($this->language->get('tooltip_min_cart_weight') != 'tooltip_min_cart_weight' ? $this->language->get('tooltip_min_cart_weight') : '')
			);

			$data['fields'][] = array(
				'tab'			=> 'tab_' . $id,
				'entry'			=> $this->language->get('entry_max_cart_weight'),
				'type'			=> 'text',
				'name' 			=> 'max_cart_weight_' . $id,
				'value' 		=> (isset($this->request->post['max_cart_weight_' . $id])) ? $this->request->post['max_cart_weight_' . $id] : $this->config->get($classname . '_max_cart_weight_' . $id),
				'required'		=> false,
				'help'			=> ($this->language->get('help_max_cart_weight') != 'help_max_cart_weight' ? $this->language->get('help_max_cart_weight') : ''),
				'tooltip'		=> ($this->language->get('tooltip_max_cart_weight') != 'tooltip_max_cart_weight' ? $this->language->get('tooltip_max_cart_weight') : '')
			);

		}

		$data['fields'][] = array(
			'tab'			=> 'tab_support',
			'entry'			=> 'Troubleshooting Info: ',
			'type'			=> 'label',
			'name'			=> 'troubleshooting',
			'value'			=> '',
			'help'			=> $this->language->get('help_about'),
			'tooltip'		=> ''
		);

		$data['fields'][] = array(
			'tab'			=> 'tab_support',
			'entry'			=> 'Support Info:',
			'type'			=> 'label',
			'name'			=> 'support',
			'value'			=> 'For support questions, contact me at qphoria@gmail.com or on skype: taqmobile',
			'help'			=> '',
			'tooltip'		=> ''
		);

?>