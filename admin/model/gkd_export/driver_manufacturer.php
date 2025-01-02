<?php
class ModelGkdExportDriverManufacturer extends Model {
  private $stores = array();
  
  public function getItems($data = array(), $count = false, $asArray = false) {
    $this->load->model('setting/store');
		$this->stores = array();
		$this->stores[0] = array(
			'store_id' => 0,
			'name'     => $this->config->get('config_name'),
			'url'     => HTTP_CATALOG,
      'ssl' => HTTPS_CATALOG,
		);

		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$action = array();

			$this->stores[$store['store_id']] = $store;
		}
    
    // store_id for use with URL and multistore, set to 0 if empty
    if (!empty($data['filter_store'])) {
      $store_id = $data['filter_store'];
    } else {
      $store_id = 0;
    }
    
    $store = $this->stores[$store_id];
    
    if ($count) {
      $select = 'COUNT(DISTINCT m.manufacturer_id) AS total';
    } else {
      if (empty($data['param_image_path'])) {
        $select = "*, CONCAT('".HTTP_CATALOG."image/', m.image) as image";
      }
    }
    
    $sql = "SELECT ".$select." FROM " . DB_PREFIX . "manufacturer m";
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id)";
    }
    
    // Where
    $sql .= " WHERE 1";
    
    if (isset($data['filter_store']) && $data['filter_store'] !== '') {
      $sql .= " AND m2s.store_id = '" . (int)$data['filter_store'] . "'";
    }
    
    if (!empty($data['filter_status'])) {
      $sql .= " AND m.status = '" . (int)$data['filter_status'] . "'";
    }
    
		if (!empty($data['filter_name'])) {
			$sql .= " AND m.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

    // return count
    if ($count) {
      return $this->db->query($sql)->row['total'];
    }
    
    $sql .= " ORDER BY m.manufacturer_id";
    
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

    foreach ($query->rows as &$row) {
      //$row['store'] = $store['name'];
      $row['store'] = $this->getManufacturerStores($row['manufacturer_id'], $asArray);
    }
    
		return $query->rows;
	}
  
  public function getManufacturerStores($manufacturer_id, $asArray = false) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
    
    if ($asArray) {
      $return = array();
      foreach ($query->rows as $item) {
        $return[] = isset($this->stores[$item['store_id']]['name']) ? $this->stores[$item['store_id']]['name'] : $item['store_id'];
      }
      
      return $return;
    }
    
    $res = '';
    
    // get formatted string for CSV
    foreach ($query->rows as $item) {
      $res .= ($res !== '') ? '|' : '';
      $res .= isset($this->stores[$item['store_id']]['name']) ? $this->stores[$item['store_id']]['name'] : $item['store_id'];
    }
    
		return $res;
  }
  
  public function getTotalItems($data = array()) {
    return $this->getItems($data, true);
  }
}