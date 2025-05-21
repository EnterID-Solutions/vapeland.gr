<?php

class ControllerToolDemo extends Controller {

  public function index(){
    echo "Demo Controller";
  }

  public function update(){
    

    $data['pcustomer_groups'] = array();

    $data['pcustomer_groups'][] = array(
      'customer_group_id' => 0,
      'name'     => 'Guest'
    );

    $customer_groups = $this->getCustomerGroups();

    foreach ($customer_groups as $cg) {
      $data['pcustomer_groups'][] = array(
        'customer_group_id' => $cg['customer_group_id'],
        'name'     => $cg['name']
      );
    }

    $q = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product");

    foreach ($q->rows as $row) {
      foreach ($data['pcustomer_groups'] as $cg) {
        $this->db->query("INSERT INTO ".DB_PREFIX . "product_to_customer_group SET product_id='".(int)$row['product_id']."', customer_group_id='".(int)$cg['customer_group_id']."'");
      }
    }
  }

  private function getCustomerGroups($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "customer_group cg LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (cg.customer_group_id = cgd.customer_group_id) WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = array(
			'cgd.name',
			'cg.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY cgd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}


}
