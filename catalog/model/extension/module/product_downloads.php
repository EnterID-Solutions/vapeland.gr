<?php
class ModelExtensionModuleProductDownloads extends Model {
	private static $last_download_count = 0;

	public function updateDownloaded($download_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "download SET downloaded = (downloaded + 1) WHERE download_id = '" . (int)$download_id . "'");
		$this->db->query("INSERT INTO " . DB_PREFIX . "download_stats (download_date, download_id, customer_id, download_count) VALUES(UTC_DATE(), '" . (int)$download_id . "', '" . ($this->customer->isLogged() ? (int)$this->customer->getId() : '0') . "', 1) ON DUPLICATE KEY UPDATE download_count = download_count + 1");
	}

	public function getDownloadCustomerGroups($download_id) {
		$download_customer_group_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "download_to_customer_group WHERE download_id = '" . (int)$download_id . "'");

		foreach ($query->rows as $result) {
			$download_customer_group_data[] = $result['customer_group_id'];
		}

		return $download_customer_group_data;
	}

	// public function getDownloadStores($download_id) {
	// 	$download_store_data = array();

	// 	$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "download_to_store WHERE download_id = '" . (int)$download_id . "'");

	// 	foreach ($query->rows as $result) {
	// 		$download_store_data[] = $result['store_id'];
	// 	}

	// 	return $download_store_data;
	// }

	public function getFreeDownloadsCount($product_id=null) {
		if ($product_id) {
			$sql = "SELECT COUNT(d.download_id) AS total FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id) LEFT JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id) LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (d.download_id = d2cg.download_id) WHERE d.status = '1' AND d.is_free = '1' AND p2d.product_id = '" . (int)$product_id . "' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND d2cg.customer_group_id = '0'";
		} else {
			$sql = "SELECT COUNT(d.download_id) AS total FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id) LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (d.download_id = d2cg.download_id) WHERE d.status = '1' AND d.is_free = '1' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND d2cg.customer_group_id = '0'";
		}

		$query = $this->db->query($sql);

		if ($query->num_rows) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	public function getFreeDownload($download_id) {
		$query = $this->db->query("SELECT d.* FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id) WHERE d.download_id = '" . (int)$download_id . "' AND d.is_free = '1' AND d.status = '1' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		return $query->row;
	}

	public function getFilteredDownloadCount() {
		return $this->last_download_count;
	}

	/* Product downloads module */
	public function getProductDownloads($product_id, $data=array()) {
		$show_purchased_downloads = (int)$this->config->get('module_product_downloads_show_purchased_downloads') && $this->customer->isLogged();
		$show_purchasable_downloads = (int)$this->config->get('module_product_downloads_show_purchasable_downloads');
		$differentiate_customers = (int)$this->config->get('module_product_downloads_differentiate_customers');

		$sql = "SELECT SQL_CALC_FOUND_ROWS tbl.download_id, filename, mask, date_added, date_modified, name, file_size, login, is_free, downloaded";

		if ($show_purchased_downloads) {
			$sql .= ", order_product_download_id, order_option_download_id, `constraint`, end_time, remaining";
		}

		$sql .= " FROM (";

		$union_tabels = array();

		$union_sql = "SELECT d.download_id, d.filename, d.mask, d.date_added, d.date_modified, dd.name, d.file_size, d.login, d.is_free, d.downloaded";

		if ($show_purchased_downloads) {
			$union_sql .= ", '0' AS order_product_download_id, '0' AS order_option_download_id, '0' AS `constraint`, '' AS end_time, '0' AS remaining";
		}

		$union_sql .= " FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id) INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
		$union_sql .= " WHERE d.status = '1' AND d.is_free = '1' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2d.product_id = '" . (int)$product_id . "'";

		$union_tabels[] = $union_sql;

		if ($show_purchased_downloads) {
			$order_status = array();

			$order_statuses = $this->config->get('config_complete_status');

			foreach ($order_statuses as $order_status_id) {
				$order_status[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($order_status) {
				$union_sql = "SELECT d.download_id, d.filename, d.mask, d.date_added, d.date_modified, dd.name, d.file_size, d.login, d.is_free, d.downloaded, opd.order_product_download_id, '0' AS order_option_download_id, opd.constraint, opd.end_time, opd.remaining
						FROM " . DB_PREFIX . "order_product_download opd
						INNER JOIN " . DB_PREFIX . "order_product op ON (opd.order_product_id = op.order_product_id AND op.product_id = '" . (int)$product_id . "')
						INNER JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id AND o.customer_id = '" . (int)$this->customer->getId() . "')
						INNER JOIN " . DB_PREFIX . "download d ON (opd.download_id = d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')
						WHERE d.status = '1' AND (" . implode(" OR ", $order_status) . ") AND (CASE opd.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(opd.remaining > '0', '1', '0') WHEN '2' THEN IF(opd.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(opd.remaining > '0' AND opd.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'";

				$union_tabels[] = $union_sql;

				$union_sql = "SELECT d.download_id, d.filename, d.mask, d.date_added, d.date_modified, dd.name, d.file_size, d.login, d.is_free, d.downloaded, '0' AS order_product_download_id, ood.order_option_download_id, ood.constraint, ood.end_time, ood.remaining
						FROM " . DB_PREFIX . "order_option_download ood
						INNER JOIN " . DB_PREFIX . "order_option oo ON (ood.order_option_id = oo.order_option_id)
						INNER JOIN " . DB_PREFIX . "order_product op ON (oo.order_product_id = op.order_product_id AND op.product_id = '" . (int)$product_id . "')
						INNER JOIN `" . DB_PREFIX . "order` o ON (oo.order_id = o.order_id AND o.customer_id = '" . (int)$this->customer->getId() . "')
						INNER JOIN " . DB_PREFIX . "download d ON (ood.download_id = d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')
						WHERE d.status = '1' AND (" . implode(" OR ", $order_status) . ") AND (CASE ood.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(ood.remaining > '0', '1', '0') WHEN '2' THEN IF(ood.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(ood.remaining > '0' AND ood.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'";

				$union_tabels[] = $union_sql;
			}
		}

		if ($show_purchasable_downloads) {
			// Product downloads
			$union_sql = "SELECT d.download_id, d.filename, d.mask, d.date_added, d.date_modified, dd.name, d.file_size, d.login, d.is_free, d.downloaded";

			if ($show_purchased_downloads) {
				$union_sql .= ", '0' AS order_product_download_id, '0' AS order_option_download_id, '0' AS `constraint`, '' AS end_time, '0' AS remaining";
			}

			$union_sql .= " FROM " . DB_PREFIX . "download d INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')
							WHERE d.status = '1' AND d.is_free = '0' AND p2d.product_id = '" . (int)$product_id . "'";

			$union_tabels[] = $union_sql;

			// Product option downloads
			$union_sql = "SELECT d.download_id, d.filename, d.mask, d.date_added, d.date_modified, dd.name, d.file_size, d.login, d.is_free, d.downloaded";

			if ($show_purchased_downloads) {
				$union_sql .= ", '0' AS order_product_download_id, '0' AS order_option_download_id, '0' AS `constraint`, '' AS end_time, '0' AS remaining";
			}

			$union_sql .= " FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')
							INNER JOIN " . DB_PREFIX . "option_value ov ON (d.download_id = ov.download_id) INNER JOIN " . DB_PREFIX . "product_option po ON (ov.option_id = po.option_id)
							WHERE d.status = '1' AND d.is_free = '0' AND po.product_id = '" . (int)$product_id . "'";

			$union_tabels[] = $union_sql;
		}

		if ($union_tabels) {
			$sql .= implode(' UNION ', $union_tabels);
		}

		$sql .= ") AS tbl";

		if ($differentiate_customers) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (tbl.download_id = d2cg.download_id)";
		}

		if (!empty($data['filter_tag'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_to_tag d2t ON (tbl.download_id = d2t.download_id)";
		}

		$where = array();

		if ($this->customer->isLogged()) {
			if (!$show_purchasable_downloads) {
				if ($differentiate_customers) {
					if ($show_purchased_downloads) {
						$where[] = "(order_product_download_id <> '0' OR order_option_download_id <> '0' OR d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					} else {
						$where[] = "(d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					}
				} // else show everything
			} else {
				if ($differentiate_customers) {
					if ($show_purchased_downloads) {
						$where[] = "(order_product_download_id <> '0' OR order_option_download_id <> '0' OR is_free = '0' OR d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					} else {
						$where[] = "(is_free = '0' OR d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					}
				} // else show everything
			}
		} else { // ! logged_in
			if ($this->config->get('module_product_downloads_require_login') || ($this->config->get('module_product_downloads_require_login_free') && $this->config->get('module_product_downloads_require_login_commercial'))) {
				if ($this->config->get('module_product_downloads_show_download_without_link')) {
					$where[] = "is_free = '1'"; // Hides purchasable downloads

					if ($differentiate_customers) {
						$where[] = "d2cg.customer_group_id = '0'";
					}
				} else {
					return array();
				}
			} else { // ! global_login_required
				if (!$show_purchasable_downloads) {
					if ($this->config->get('module_product_downloads_show_download_without_link')) {
						// $where[] = "is_free = '1'"; // already in effect

						if ($differentiate_customers) {
							$where[] = "d2cg.customer_group_id = '0'";
						}
					} else if (!$this->config->get('module_product_downloads_require_login_free')) {
						// $where[] = "is_free = '1'"; // already in effect
						$where[] = "login = '0'";

						if ($differentiate_customers) {
							$where[] = "d2cg.customer_group_id = '0'";
						}
					} else { // do not show anything
						return array();
					}
				} else { //show_purchasable_downloads
					if (!$this->config->get('module_product_downloads_require_login_commercial')) {
						if ($this->config->get('module_product_downloads_show_download_without_link')) {
							$where[] = "(is_free = '0' AND login = '0' OR is_free = '1')"; // Hide commercial downloads that require login

							if ($differentiate_customers) {
								$where[] = "(is_free = '0' OR d2cg.customer_group_id = '0')";
							} // else show everything
						} else if ($this->config->get('module_product_downloads_require_login_free')) {
							$where[] = "is_free = '0'";
							$where[] = "login = '0'";
						} else {
							$where[] = "login = '0'";

							if ($differentiate_customers) {
								$where[] = "(is_free = '0' OR d2cg.customer_group_id = '0')";
							}
						}
					} else { // login_required_commercial
						$where[] = "is_free = '1'";

						if ($this->config->get('module_product_downloads_show_download_without_link')) {
							if ($differentiate_customers) {
								$where[] = "d2cg.customer_group_id = '0'";
							}
						} else if (!$this->config->get('module_product_downloads_require_login_free')) {
							$where[] = "login = '0'";

							if ($differentiate_customers) {
								$where[] = "d2cg.customer_group_id = '0'";
							}
						} else {
							return array();
						}
					}
				}
			}
		}

		if (isset($data['search']) && count((array)$data['search'])) {
			foreach ((array)$data['search'] as $keyword) {
				if ($keyword) {
					$where[] = "(name LIKE '%" . $this->db->escape($keyword) . "%' OR mask LIKE '%" . $this->db->escape($keyword) . "%')";
				}
			}
		}

		if (!empty($data['filter_tag'])) {
			$implode = array();

			$tags = (array)$data['filter_tag'];

			foreach ($tags as $tag) {
				$implode[] = "'" . (int)$tag . "'";
			}

			if ($implode) {
				$where[] = "d2t.download_tag_id IN (" . implode(",", $implode) . ")";
			}
		}

		if ($where) {
			$sql .= " WHERE " . implode(' AND ', $where);
		}

		if (!in_array(strtoupper($data['order']), array("DESC", "ASC"))) {
			$data['order'] = "ASC";
		}

		switch (strtolower($data['sort'])) {
			case "added":
				$data['sort'] = "date_added " . $data['order'];
				break;
			case "modified":
				$data['sort'] = "date_modified " . $data['order'];
				break;
			case "size":
				$data['sort'] = "file_size " . $data['order'];
				break;
			case "name":
			default:
				$data['sort'] = "name " . $data['order'];
				break;
		}

		if (!empty($data['filter_tag'])) {
			$sql .= " GROUP BY d2t.download_id HAVING COUNT(DISTINCT d2t.download_tag_id) = " . count($implode);
		} else {
			$sql .= " GROUP BY download_id";
		}

		$sql .= " ORDER BY " . $data['sort'];

		if ((int)$data['limit'] < 0) {
			$data['limit'] = 0;
		}

		if ((int)$data['start'] < 0) {
			$data['start'] = 0;
		}

		if ((int)$data['limit'] > 0 && (int)$data['start'] + (int)$data['per_page'] > (int)$data['limit'] || !(int)$data['per_page'] && (int)$data['limit'] > 0) {
			$count = (int)$data['limit'] - (int)$data['start'];
		} else {
			$count = (int)$data['per_page'];
		}

		if ($count > 0) {
			$sql .= " LIMIT " . (int)$data['start'] . "," . $count;
		}

		$query = $this->db->query($sql);

		$count = $this->db->query("SELECT FOUND_ROWS() AS count");
		$this->last_download_count = ($count->num_rows) ? ($data['limit'] && $data['limit'] < (int)$count->row['count'] ? $data['limit'] : (int)$count->row['count']) : 0;

		return $query->rows;
	}

	public function getProductDownloadsCount($product_id, $data=array()) {
		$show_purchased_downloads = (int)$this->config->get('module_product_downloads_show_purchased_downloads') && $this->customer->isLogged();
		$show_purchasable_downloads = (int)$this->config->get('module_product_downloads_show_purchasable_downloads');
		$differentiate_customers = (int)$this->config->get('module_product_downloads_differentiate_customers');
		$search = isset($data['search']) && count((array)$data['search']);

		$sql = "SELECT COUNT(*) AS total FROM (SELECT tbl.* FROM (";

		$union_tabels = array();

		$union_sql = "SELECT d.download_id, d.filename, d.mask" . ($search ? ", dd.name" : "") . ", d.date_added, d.date_modified, d.file_size, d.login, d.is_free, d.downloaded";

		if ($show_purchased_downloads) {
			$union_sql .= ", '0' AS order_product_download_id, '0' AS order_option_download_id, '0' AS `constraint`, '' AS end_time, '0' AS remaining";
		}

		$union_sql .= " FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id) INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id)";

		if ($search) {
			$union_sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
		}

		$union_sql .= " WHERE d.status = '1' AND d.is_free = '1' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2d.product_id = '" . (int)$product_id . "'";

		$union_tabels[] = $union_sql;

		if ($show_purchased_downloads) {
			$order_status = array();

			$order_statuses = $this->config->get('config_complete_status');

			foreach ($order_statuses as $order_status_id) {
				$order_status[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($order_status) {
				$union_sql = "SELECT d.download_id, d.filename, d.mask" . ($search ? ", dd.name" : "") . ", d.date_added, d.date_modified, d.file_size, d.login, d.is_free, d.downloaded, opd.order_product_download_id, '0' AS order_option_download_id, opd.constraint, opd.end_time, opd.remaining
					FROM " . DB_PREFIX . "order_product_download opd
					INNER JOIN " . DB_PREFIX . "order_product op ON (opd.order_product_id = op.order_product_id AND op.product_id = '" . (int)$product_id . "')
					INNER JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id AND o.customer_id = '" . (int)$this->customer->getId() . "')
					INNER JOIN " . DB_PREFIX . "download d ON (opd.download_id = d.download_id)";

				if ($search) {
					$union_sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
				}

				$union_sql .= " WHERE d.status = '1' AND (" . implode(" OR ", $order_status) . ") AND (CASE opd.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(opd.remaining > '0', '1', '0') WHEN '2' THEN IF(opd.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(opd.remaining > '0' AND opd.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'";

				$union_tabels[] = $union_sql;

				$union_sql = "SELECT d.download_id, d.filename, d.mask" . ($search ? ", dd.name" : "") . ", d.date_added, d.date_modified, d.file_size, d.login, d.is_free, d.downloaded, '0' AS order_product_download_id, ood.order_option_download_id, ood.constraint, ood.end_time, ood.remaining
					FROM " . DB_PREFIX . "order_option_download ood
					INNER JOIN " . DB_PREFIX . "order_option oo ON (ood.order_option_id = oo.order_option_id)
					INNER JOIN " . DB_PREFIX . "order_product op ON (oo.order_product_id = op.order_product_id AND op.product_id = '" . (int)$product_id . "')
					INNER JOIN `" . DB_PREFIX . "order` o ON (oo.order_id = o.order_id AND o.customer_id = '" . (int)$this->customer->getId() . "')
					INNER JOIN " . DB_PREFIX . "download d ON (ood.download_id = d.download_id)";

				if ($search) {
					$union_sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
				}

				$union_sql .= " WHERE d.status = '1' AND (" . implode(" OR ", $order_status) . ") AND (CASE ood.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(ood.remaining > '0', '1', '0') WHEN '2' THEN IF(ood.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(ood.remaining > '0' AND ood.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'";

				$union_tabels[] = $union_sql;
			}
		}

		if ($show_purchasable_downloads) {
			// Product downloads
			$union_sql = "SELECT d.download_id, d.filename, d.mask" . ($search ? ", dd.name" : "") . ", d.date_added, d.date_modified, d.file_size, d.login, d.is_free, d.downloaded";

			if ($show_purchased_downloads) {
				$union_sql .= ", '0' AS order_product_download_id, '0' AS order_option_download_id, '0' AS `constraint`, '' AS end_time, '0' AS remaining";
			}

			$union_sql .= " FROM " . DB_PREFIX . "download d INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id)";

			if ($search) {
				$union_sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
			}

			$union_sql .= "	WHERE d.status = '1' AND d.is_free = '0' AND p2d.product_id = '" . (int)$product_id . "'";

			$union_tabels[] = $union_sql;

			// Product option downloads
			$union_sql = "SELECT d.download_id, d.filename, d.mask" . ($search ? ", dd.name" : "") . ", d.date_added, d.date_modified, d.file_size, d.login, d.is_free, d.downloaded";

			if ($show_purchased_downloads) {
				$union_sql .= ", '0' AS order_product_download_id, '0' AS order_option_download_id, '0' AS `constraint`, '' AS end_time, '0' AS remaining";
			}

			$union_sql .= " FROM " . DB_PREFIX . "download d INNER JOIN " . DB_PREFIX . "option_value ov ON (d.download_id = ov.download_id) INNER JOIN " . DB_PREFIX . "product_option po ON (ov.option_id = po.option_id)";

			if ($search) {
				$union_sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
			}

			$union_sql .= "	WHERE d.status = '1' AND d.is_free = '0' AND po.product_id = '" . (int)$product_id . "'";

			$union_tabels[] = $union_sql;
		}

		if ($union_tabels) {
			$sql .= implode(' UNION ', $union_tabels);
		}

		$sql .= ") AS tbl";

		if ($differentiate_customers) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (tbl.download_id = d2cg.download_id)";
		}

		if (!empty($data['filter_tag'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_to_tag d2t ON (tbl.download_id = d2t.download_id)";
		}

		$where = array();

		if ($this->customer->isLogged()) {
			// Customer is logged in so count everything
			if (!$show_purchasable_downloads) {
				// ... except purchasable downloads (as they are not enabled)
				if ($differentiate_customers) {
					if ($show_purchased_downloads) {
						// ... but do include already purchased downloads and downloads that have no customer group set or the current customer group
						$where[] = "(order_product_download_id <> '0' OR order_option_download_id <> '0' OR d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					} else {
						// ... but do include downloads that have no customer group set or the current customer group
						$where[] = "(d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					}
				}
			} else {
				// ... starting from purchasable downloads
				if ($differentiate_customers) {
					if ($show_purchased_downloads) {
						// ... including all purchased downloads and free downloads that have no customer group set or the current customer group
						$where[] = "(order_product_download_id <> '0' OR order_option_download_id <> '0' OR is_free = '0' OR d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					} else {
						// ... including free downloads that have no customer group set or the current customer group
						$where[] = "(is_free = '0' OR d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					}
				}
			}
		} else { // ! logged_in
			// The customer is not logged in
			if ($this->config->get('module_product_downloads_require_login') || ($this->config->get('module_product_downloads_require_login_free') && $this->config->get('module_product_downloads_require_login_commercial'))) {
				// ... and global login is required (or both group level logins are required)
				if ($this->config->get('module_product_downloads_show_login_required_text')) {
					// ... and we must display the login required text (if there are some downloads available for logged in customer of no particular class)
					if (!$show_purchasable_downloads) {
						// ... so we count just the free downloads
						if ($differentiate_customers) {
							// ... that have no customer group set
							$where[] = "d2cg.customer_group_id = '0'";
						}
					} else { //show_purchasable_downloads
						// ... so we have to count
						if (!$this->config->get('module_product_downloads_show_download_without_link')) { // Free downloads are not shown without link, so we can count everything
							if ($differentiate_customers) { // Count only commercial downloads and those free downloads that have no customer group
								// ... all commercial downloads and those free downloads that have no customer group set
								$where[] = "(is_free = '0' OR d2cg.customer_group_id = '0')";
							} // else ... everything
						} else { // do not include purchasable downloads in count, if free downloads are shown without link for logged out clients
							// ... just the free downloads (as they are shown, just without the link)
							$where[] = "is_free = '1'";

							if ($differentiate_customers) {
								// ... that have no customer group set
								$where[] = "d2cg.customer_group_id = '0'";
							}
						}
					}
				} else { // !show_login_required
					// ... and we do not have to display the login required text
					if ($this->config->get('module_product_downloads_show_download_without_link')) {
						// ... so we count only the free downloads
						$where[] = "is_free = '1'";

						if ($differentiate_customers) {
							// ... that have no customer group set
							$where[] = "d2cg.customer_group_id = '0'";
						}
					} else {
						// ... so we do not have to count anything
						return 0;
					}
				}
			} else { // ! global_login_required
				// ... and no global login is required
				if (!$show_purchasable_downloads) {
					// ... so we have to count
					if ($this->config->get('module_product_downloads_show_download_without_link')) {
						// ... all free downloads
						// $where[] = "is_free = '1'"; // already in effect

						if ($differentiate_customers) {
							// ... that have no customer group set
							$where[] = "d2cg.customer_group_id = '0'";
						}
					} else if (!$this->config->get('module_product_downloads_require_login_free')) {
						// ... only those free downloads that do not require login
						// $where[] = "is_free = '1'"; // already in effect
						$where[] = "login = '0'";

						if ($differentiate_customers) {
							// ... and that have no customer group set
							$where[] = "d2cg.customer_group_id = '0'";
						}
					} else { // do not show anything
						// ... nothing
						return 0;
					}
				} else { //show_purchasable_downloads
					// ... so we can count
					if (!$this->config->get('module_product_downloads_require_login_commercial')) {
						// ... commercial downloads
						if ($this->config->get('module_product_downloads_show_download_without_link')) {
							// ... and all free downloads
							$where[] = "(is_free = '0' AND login = '0' OR is_free = '1')"; // Hide commercial downloads that require login

							if ($differentiate_customers) {
								// ... that have no customer group set (and commercial downloads that do not require login)
								$where[] = "(is_free = '0' OR d2cg.customer_group_id = '0')";
							}
						} else if ($this->config->get('module_product_downloads_require_login_free')) {
							// ... that do not require login
							$where[] = "is_free = '0'";
							$where[] = "login = '0'";
						} else {
							// ... and free downloads that do not require login
							$where[] = "login = '0'";

							if ($differentiate_customers) {
								// ... and have no customer group set (effective for free downloads)
								$where[] = "(is_free = '0' OR d2cg.customer_group_id = '0')";
							}
						}
					} else { // login_required_commercial
						if ($this->config->get('module_product_downloads_show_download_without_link')) {
							// ... only free downloads
							$where[] = "is_free = '1'";

							if ($differentiate_customers) {
								// ... that hve no customer group set
								$where[] = "d2cg.customer_group_id = '0'";
							}
						} else if (!$this->config->get('module_product_downloads_require_login_free')) {
							// ... only free downloads that do not require login
							$where[] = "is_free = '1'";
							$where[] = "login = '0'";

							if ($differentiate_customers) {
								// ... and have no customer group set
								$where[] = "d2cg.customer_group_id = '0'";
							}
						} else {
							// ... nothing
							return 0;
						}
					}
				}
			}
		}

		if ($search) {
			foreach ((array)$data['search'] as $keyword) {
				if ($keyword) {
					$where[] = "(name LIKE '%" . $this->db->escape($keyword) . "%' OR mask LIKE '%" . $this->db->escape($keyword) . "%')";
				}
			}
		}

		if (!empty($data['filter_tag'])) {
			$implode = array();

			$tags = $data['filter_tag'];

			foreach ($tags as $tag) {
				$implode[] = "'" . (int)$tag . "'";
			}

			if ($implode) {
				$where[] = "d2t.download_tag_id IN (" . implode(",", $implode) . ")";
			}
		}

		if ($where) {
			$sql .= " WHERE " . implode(' AND ', $where);
		}

		if (!empty($data['filter_tag'])) {
			$sql .= " GROUP BY d2t.download_id HAVING COUNT(DISTINCT d2t.download_tag_id) = " . count($implode);
		} else {
			$sql .= " GROUP BY tbl.download_id";
		}

		$sql .= ") AS tbl2";

		$query = $this->db->query($sql);

		if ($query->num_rows) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	public function getTotalProductDownloadsCount($product_id) {
		if ($this->config->get('module_product_downloads_show_purchasable_downloads')) {
			$sql = "SELECT COUNT(DISTINCT download_id) AS total FROM (SELECT d.download_id FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id) INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id) LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (d.download_id = d2cg.download_id) WHERE d.status = '1' AND p2d.product_id = '" . (int)$product_id . "' AND (d.is_free = '0' OR (d2cg.customer_group_id = '0' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "')) UNION SELECT d.download_id FROM " . DB_PREFIX . "download d INNER JOIN " . DB_PREFIX . "option_value ov ON (d.download_id = ov.download_id) INNER JOIN " . DB_PREFIX . "product_option po ON (ov.option_id = po.option_id) WHERE d.status = '1' AND d.is_free = '0' AND po.product_id = '" . (int)$product_id . "') AS tbl";
		} else {
			$sql = "SELECT COUNT(d.download_id) AS total FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id) INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id) LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (d.download_id = d2cg.download_id) WHERE d.status = '1' AND p2d.product_id = '" . (int)$product_id . "' AND d.is_free = '1' AND d2cg.customer_group_id = '0' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";
		}

		$query = $this->db->query($sql);

		if ($query->num_rows) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	public function getProductCommercialDownloadsCount($product_id) {
		$sql = "SELECT COUNT(DISTINCT download_id) AS total FROM (SELECT d.download_id FROM " . DB_PREFIX . "download d INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id) WHERE d.status = '1' AND p2d.product_id = '" . (int)$product_id . "' AND d.is_free = '0' UNION SELECT d.download_id FROM " . DB_PREFIX . "download d INNER JOIN " . DB_PREFIX . "option_value ov ON (d.download_id = ov.download_id) INNER JOIN " . DB_PREFIX . "product_option po ON (ov.option_id = po.option_id) WHERE d.status = '1' AND d.is_free = '0' AND po.product_id = '" . (int)$product_id . "') AS tbl";

		$query = $this->db->query($sql);

		if ($query->num_rows) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	public function getProductDownloadsTags($product_id, $data) {
		$show_purchased_downloads = (int)$this->config->get('module_product_downloads_show_purchased_downloads') && $this->customer->isLogged();
		$show_purchasable_downloads = (int)$this->config->get('module_product_downloads_show_purchasable_downloads');
		$differentiate_customers = (int)$this->config->get('module_product_downloads_differentiate_customers');
		$search = isset($data['search']) && count((array)$data['search']);

		$sql = "SELECT dt.download_tag_id, dtd.name FROM " . DB_PREFIX . "download_tag dt LEFT JOIN " . DB_PREFIX . "download_tag_description dtd ON (dt.download_tag_id = dtd.download_tag_id AND dtd.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN " . DB_PREFIX . "download_to_tag d2t ON (dt.download_tag_id = d2t.download_tag_id)";

		$sql .=	" INNER JOIN (SELECT d.download_id, d.is_free, d.login FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id) INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id)";

		if ($search) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
		}

		$sql .= " WHERE d.status = '1' AND d.is_free = '1' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p2d.product_id = '" . (int)$product_id . "'";

		if ($search) {
			$where = array();

			foreach ((array)$data['search'] as $keyword) {
				if ($keyword) {
					$where[] = "(dd.name LIKE '%" . $this->db->escape($keyword) . "%' OR d.mask LIKE '%" . $this->db->escape($keyword) . "%')";
				}
			}

			if ($where) {
				$sql .= " AND " . implode(' AND ', $where);
			}
		}

		if ($show_purchasable_downloads) {
			// Product downloads
			$sql .= " UNION SELECT d.download_id, d.is_free, d.login FROM " . DB_PREFIX . "download d INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (d.download_id = p2d.download_id)";

			if ($search) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
			}

			$sql .= " WHERE d.status = '1' AND d.is_free = '0' AND p2d.product_id = '" . (int)$product_id . "'";

			if ($search) {
				$where = array();

				foreach ((array)$data['search'] as $keyword) {
					if ($keyword) {
						$where[] = "(dd.name LIKE '%" . $this->db->escape($keyword) . "%' OR d.mask LIKE '%" . $this->db->escape($keyword) . "%')";
					}
				}

				if ($where) {
					$sql .= " AND " . implode(' AND ', $where);
				}
			}

			// Product option downloads
			$sql .= " UNION SELECT d.download_id, d.is_free, d.login FROM " . DB_PREFIX . "download d";

			if ($search) {
				$sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
			}

			$sql .= " INNER JOIN " . DB_PREFIX . "option_value ov ON (d.download_id = ov.download_id) INNER JOIN " . DB_PREFIX . "product_option po ON (ov.option_id = po.option_id)
				WHERE d.status = '1' AND d.is_free = '0' AND po.product_id = '" . (int)$product_id . "'";

			if ($search) {
				$where = array();

				foreach ((array)$data['search'] as $keyword) {
					if ($keyword) {
						$where[] = "(dd.name LIKE '%" . $this->db->escape($keyword) . "%' OR d.mask LIKE '%" . $this->db->escape($keyword) . "%')";
					}
				}

				if ($where) {
					$sql .= " AND " . implode(' AND ', $where);
				}
			}
		} else if ($show_purchased_downloads) {
			$order_status = array();

			$order_statuses = $this->config->get('config_complete_status');

			foreach ($order_statuses as $order_status_id) {
				$order_status[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($order_status) {
				$sql .= " UNION SELECT d.download_id, d.is_free, d.login FROM " . DB_PREFIX . "order_product_download opd
						INNER JOIN " . DB_PREFIX . "order_product op ON (opd.order_product_id = op.order_product_id AND op.product_id = '" . (int)$product_id . "')
						INNER JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id AND o.customer_id = '" . (int)$this->customer->getId() . "')
						INNER JOIN " . DB_PREFIX . "download d ON (opd.download_id = d.download_id)";

				if ($search) {
					$sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
				}

				$sql .= " WHERE d.status = '1' AND (" . implode(" OR ", $order_status) . ") AND (CASE opd.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(opd.remaining > '0', '1', '0') WHEN '2' THEN IF(opd.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(opd.remaining > '0' AND opd.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'";

				if ($search) {
					$where = array();

					foreach ((array)$data['search'] as $keyword) {
						if ($keyword) {
							$where[] = "(dd.name LIKE '%" . $this->db->escape($keyword) . "%' OR d.mask LIKE '%" . $this->db->escape($keyword) . "%')";
						}
					}

					if ($where) {
						$sql .= " AND " . implode(' AND ', $where);
					}
				}

				$sql .= " UNION SELECT d.download_id, d.is_free, d.login FROM " . DB_PREFIX . "order_option_download ood
						INNER JOIN " . DB_PREFIX . "order_option oo ON (ood.order_option_id = oo.order_option_id)
						INNER JOIN " . DB_PREFIX . "order_product op ON (oo.order_product_id = op.order_product_id AND op.product_id = '" . (int)$product_id . "')
						INNER JOIN `" . DB_PREFIX . "order` o ON (oo.order_id = o.order_id AND o.customer_id = '" . (int)$this->customer->getId() . "')
						INNER JOIN " . DB_PREFIX . "download d ON (ood.download_id = d.download_id)";

				if ($search) {
					$sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
				}

				$sql .= " WHERE d.status = '1' AND (" . implode(" OR ", $order_status) . ") AND (CASE ood.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(ood.remaining > '0', '1', '0') WHEN '2' THEN IF(ood.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(ood.remaining > '0' AND ood.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'";

				if ($search) {
					$where = array();

					foreach ((array)$data['search'] as $keyword) {
						if ($keyword) {
							$where[] = "(dd.name LIKE '%" . $this->db->escape($keyword) . "%' OR d.mask LIKE '%" . $this->db->escape($keyword) . "%')";
						}
					}

					if ($where) {
						$sql .= " AND " . implode(' AND ', $where);
					}
				}
			}
		}

		$sql .= " ) AS d ON (d2t.download_id = d.download_id)";


		if ($differentiate_customers) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (d2t.download_id = d2cg.download_id)";
		}

		$sql .= " WHERE dt.administrative = '0'";

		if ($this->customer->isLogged()) {
			if (!$show_purchasable_downloads) {
				if ($differentiate_customers) {
					if ($show_purchased_downloads) {
						$sql .= " AND (d.is_free = '0' OR d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					} else {
						$sql .= " AND (d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
					}
				}
			} else {
				if ($differentiate_customers) {
					$sql .= " AND (d.is_free = '0' OR d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
				} // else show everything
			}
		} else { // ! logged_in
			if ($this->config->get('module_product_downloads_require_login') || ($this->config->get('module_product_downloads_require_login_free') && $this->config->get('module_product_downloads_require_login_commercial'))) {
				if ($this->config->get('module_product_downloads_show_download_without_link')) {
					$sql .= " AND d.is_free = '1'";
					if ($differentiate_customers) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else {
					return array();
				}
			} else { // ! global_login_required
				if (!$show_purchasable_downloads) {
					if ($this->config->get('module_product_downloads_show_download_without_link')) {
						// $sql .= " AND d.is_free = '1'";
						if ($differentiate_customers) {
							$sql .= " AND d2cg.customer_group_id = '0'";
						}
					} else if (!$this->config->get('module_product_downloads_require_login_free')) {
						// $sql .= " AND d.is_free = '1' AND d.login = '0'";
						$sql .= " AND d.login = '0'";
						if ($differentiate_customers) {
							$sql .= " AND d2cg.customer_group_id = '0'";
						}
					} else { // do not show anything
						return array();
					}
				} else { // show_purchasable_downloads
					if (!$this->config->get('module_product_downloads_require_login_commercial')) {
						if ($this->config->get('module_product_downloads_show_download_without_link')) {
							if ($differentiate_customers) {
								$sql .= " AND ((d.is_free = '0' AND d.login = '0') OR (d.is_free = '1' AND d2cg.customer_group_id = '0'))";
							} else {
								$sql .= " AND (d.is_free = '1' OR d.login = '0')";
							}
						} else if ($this->config->get('module_product_downloads_require_login_free')) {
							$sql .= " AND d.is_free = '0' AND d.login = '0'";
						} else {
							$sql .= " AND d.login = '0'";
							if ($differentiate_customers) {
								$sql .= " AND (d.is_free = '0' OR d2cg.customer_group_id = '0')";
							}
						}
					} else { // login_required_commercial
						if ($this->config->get( 'module_product_downloads_show_download_without_link')) {
							$sql .= " AND d.is_free = '1'";
							if ($differentiate_customers) {
								$sql .= " AND d2cg.customer_group_id = '0'";
							}
						} else if (!$this->config->get('module_product_downloads_require_login_free')) {
							$sql .= " AND d.is_free = '1' AND d.login = '0'";
							if ($differentiate_customers) {
								$sql .= " AND d2cg.customer_group_id = '0'";
							}
						} else {
							return array();
						}
					}
				}
			}
		}

		$sql .= " GROUP BY (dt.download_tag_id) ORDER BY dt.sort_order ASC, dtd.name ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	/* Downloads page & custom module */
	public function getDownloadTags($data) {
		$sql = "SELECT dt.download_tag_id, dtd.name FROM " . DB_PREFIX . "download_tag dt LEFT JOIN " . DB_PREFIX . "download_tag_description dtd ON (dt.download_tag_id = dtd.download_tag_id AND dtd.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN " . DB_PREFIX . "download_to_tag d2t ON (dt.download_tag_id = d2t.download_tag_id) LEFT JOIN " . DB_PREFIX . "download d ON (d.download_id = d2t.download_id) LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id)";

		if (isset($data['search']) && count((array)$data['search'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
		}

		if ($this->config->get('module_product_downloads_differentiate_customers')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (d2t.download_id = d2cg.download_id)";
		}

		$sql .= " WHERE d.status = '1' AND dt.administrative = '0' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (isset($data['downloads']) && $data['downloads']) {
			$sql .= " AND d.download_id IN (" . $data['downloads'] . ")";
		}

		if ($this->customer->isLogged()) {
			$sql .= " AND d.is_free = '1'";
			if ($this->config->get('module_product_downloads_differentiate_customers')) {
				$sql .= " AND (d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
			}
		} else { // ! logged_in
			if ($this->config->get('module_product_downloads_require_login')) {
				if ($this->config->get('module_product_downloads_show_download_without_link')) {
					$sql .= " AND d.is_free = '1'";
					if ($this->config->get('module_product_downloads_differentiate_customers')) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else {
					return array();
				}
			} else { // ! global_login_required
				if ($this->config->get('module_product_downloads_show_download_without_link')) {
					$sql .= " AND d.is_free = '1'";
					if ($this->config->get('module_product_downloads_differentiate_customers')) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else if (!$this->config->get('module_product_downloads_require_login_free')) {
					$sql .= " AND d.is_free = '1' AND d.login = '0'";
					if ($this->config->get('module_product_downloads_differentiate_customers')) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else { // do not show anything
					return array();
				}
			}
		}

		if (isset($data['search']) && count((array)$data['search'])) {
			$where = array();

			foreach ((array)$data['search'] as $keyword) {
				if ($keyword) {
					$where[] = "(dd.name LIKE '%" . $this->db->escape($keyword) . "%' OR d.mask LIKE '%" . $this->db->escape($keyword) . "%')";
				}
			}

			if ($where) {
				$sql .= " AND " . implode(' AND ', $where);
			}
		}

		$sql .= " GROUP BY (dt.download_tag_id) ORDER BY dt.sort_order ASC, dtd.name ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getDownloads($data) {
		$sql = "SELECT SQL_CALC_FOUND_ROWS d.download_id, d.filename, d.mask, d.date_added, d.date_modified, dd.name, d.file_size, d.login, d.is_free, d.downloaded";

		$sql .= " FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "') LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id)";

		if ($this->config->get('module_product_downloads_differentiate_customers')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (d.download_id = d2cg.download_id)";
		}

		if (!empty($data['filter_tag'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_to_tag d2t ON (d.download_id = d2t.download_id)";
		}

		$sql .= " WHERE d.status = '1' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if (isset($data['downloads']) && $data['downloads']) {
			$sql .= " AND d.download_id IN (" . $data['downloads'] . ")";
		}

		if ($this->customer->isLogged()) {
			$sql .= " AND d.is_free = '1'";
			if ($this->config->get('module_product_downloads_differentiate_customers')) {
				$sql .= " AND (d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
			}
		} else { // ! logged_in
			if ($this->config->get('module_product_downloads_require_login')) {
				if ($this->config->get('module_product_downloads_show_download_without_link')) {
					$sql .= " AND d.is_free = '1'";
					if ($this->config->get('module_product_downloads_differentiate_customers')) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else {
					return array();
				}
			} else { // ! global_login_required
				if ($this->config->get('module_product_downloads_show_download_without_link')) {
					$sql .= " AND d.is_free = '1'";
					if ($this->config->get('module_product_downloads_differentiate_customers')) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else if (!$this->config->get('module_product_downloads_require_login_free')) {
					$sql .= " AND d.is_free = '1' AND d.login = '0'";
					if ($this->config->get('module_product_downloads_differentiate_customers')) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else { // do not show anything
					return array();
				}
			}
		}

		if (isset($data['search']) && count((array)$data['search'])) {
			$where = array();

			foreach ((array)$data['search'] as $keyword) {
				if ($keyword) {
					$where[] = "(dd.name LIKE '%" . $this->db->escape($keyword) . "%' OR d.mask LIKE '%" . $this->db->escape($keyword) . "%')";
				}
			}

			if ($where) {
				$sql .= " AND " . implode(' AND ', $where);
			}
		}

		if (!empty($data['filter_tag'])) {
			$implode = array();

			$tags = (array)$data['filter_tag'];

			foreach ($tags as $tag) {
				$implode[] = "'" . (int)$tag . "'";
			}

			if ($implode) {
				$sql .= " AND d2t.download_tag_id IN (" . implode(",", $implode) . ")";
			}
		}

		if (!in_array(strtolower($data['order']), array("desc", "asc"))) {
			$data['order'] = "ASC";
		}

		switch (strtolower($data['sort'])) {
			case "added":
				$data['sort'] = "date_added " . $data['order'];
				break;
			case "modified":
				$data['sort'] = "date_modified " . $data['order'];
				break;
			case "size":
				$data['sort'] = "file_size " . $data['order'];
				break;
			case "name":
			default:
				$data['sort'] = "name " . $data['order'];
				break;
		}

		if (!empty($data['filter_tag'])) {
			$sql .= " GROUP BY d2t.download_id HAVING COUNT(DISTINCT d2t.download_tag_id) = " . count($implode);
		} else {
			$sql .= " GROUP BY d.download_id";
		}

		$sql .= " ORDER BY " . $data['sort'];

		if ((int)$data['limit'] < 0) {
			$data['limit'] = 0;
		}

		if ((int)$data['start'] < 0) {
			$data['start'] = 0;
		}

		if ((int)$data['limit'] > 0 && (int)$data['start'] + (int)$data['per_page'] > (int)$data['limit'] || !(int)$data['per_page'] && (int)$data['limit'] > 0) {
			$count = (int)$data['limit'] - (int)$data['start'];
		} else {
			$count = (int)$data['per_page'];
		}

		if ($count < 0) {
			$count = 0;
		}

		if ((int)$data['start'] || $count) {
			$sql .= " LIMIT " . (int)$data['start'] . "," . $count;
		}

		$query = $this->db->query($sql);

		$count = $this->db->query("SELECT FOUND_ROWS() AS count");
		$this->last_download_count = ($count->num_rows) ? ($data['limit'] && $data['limit'] < (int)$count->row['count'] ? $data['limit'] : (int)$count->row['count']) : 0;

		return $query->rows;
	}

	public function getTotalDownloads() {
		$sql = "SELECT COUNT(DISTINCT d.download_id) AS total";

		$sql .= " FROM " . DB_PREFIX . "download d LEFT JOIN " . DB_PREFIX . "download_to_store d2s ON (d.download_id = d2s.download_id)";

		if ($this->config->get('module_product_downloads_differentiate_customers')) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "download_to_customer_group d2cg ON (d.download_id = d2cg.download_id)";
		}

		$sql .= " WHERE d.status = '1' AND d2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

		if ($this->customer->isLogged()) {
			$sql .= " AND d.is_free = '1'";
			if ($this->config->get('module_product_downloads_differentiate_customers')) {
				$sql .= " AND (d2cg.customer_group_id = '0' OR d2cg.customer_group_id = '" . (int)$this->customer->getGroupId() . "')";
			}
		} else { // ! logged_in
			if ($this->config->get('module_product_downloads_require_login')) {
				if ($this->config->get('module_product_downloads_show_download_without_link')) {
					$sql .= " AND d.is_free = '1'";
					if ($this->config->get('module_product_downloads_differentiate_customers')) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else {
					return array();
				}
			} else { // ! global_login_required
				if ($this->config->get('module_product_downloads_show_download_without_link')) {
					$sql .= " AND d.is_free = '1'";
					if ($this->config->get('module_product_downloads_differentiate_customers')) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else if (!$this->config->get('module_product_downloads_require_login_free')) {
					$sql .= " AND d.is_free = '1' AND d.login = '0'";
					if ($this->config->get('module_product_downloads_differentiate_customers')) {
						$sql .= " AND d2cg.customer_group_id = '0'";
					}
				} else { // do not show anything
					return array();
				}
			}
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	/* Download samples */
	public function updateDownloadSampleDownloaded($download_sample_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "download_sample SET remaining = IF(remaining > 0, remaining - 1, 0), last_accessed = UTC_TIMESTAMP() WHERE download_sample_id = '" . (int)$download_sample_id . "'");
		$this->cache->delete('pd.downloads.samples');
	}

	public function getDownloadSampleByHash($hash) {
		$query = $this->db->query("SELECT DISTINCT ds.*, d.*, dd.name AS download, (CASE ds.constraint WHEN '0' THEN IF(ds.remaining > '0', '0', '1') WHEN '1' THEN IF(ds.end_time > UTC_TIMESTAMP(), '0', '1') ELSE IF(ds.remaining > 0 AND ds.end_time > UTC_TIMESTAMP(), '0', '1') END) AS expired FROM " . DB_PREFIX . "download_sample ds JOIN " . DB_PREFIX . "download d ON (ds.download_id = d.download_id) LEFT JOIN " . DB_PREFIX . "download_description dd ON (ds.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "') WHERE ds.hash = '" . $this->db->escape($hash) . "'");
		return $query->row;
	}

	/* Account downloads */
	public function getOrderProductDownload($order_product_download_id) {
		$order_complete_statuses = $this->config->get('config_complete_status');

		foreach ($order_complete_statuses as $order_status_id) {
			$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT d.* FROM " . DB_PREFIX . "order_product_download opd INNER JOIN " . DB_PREFIX . "order_product op ON (opd.order_product_id = op.order_product_id) INNER JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) INNER JOIN " . DB_PREFIX . "download d ON (opd.download_id = d.download_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $implode) . ") AND opd.order_product_download_id = '" . (int)$order_product_download_id . "' AND d.status = '1' AND (CASE opd.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(opd.remaining > '0', '1', '0') WHEN '2' THEN IF(opd.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(opd.remaining > '0' AND opd.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'");

			return $query->row;
		} else {
			return null;
		}
	}

	public function getOrderOptionDownload($order_option_download_id) {
		$order_complete_statuses = $this->config->get('config_complete_status');

		foreach ($order_complete_statuses as $order_status_id) {
			$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT d.* FROM " . DB_PREFIX . "order_option_download ood INNER JOIN " . DB_PREFIX . "order_option oo ON (ood.order_option_id = oo.order_option_id) INNER JOIN `" . DB_PREFIX . "order` o ON (oo.order_id = o.order_id) INNER JOIN " . DB_PREFIX . "download d ON (ood.download_id = d.download_id) WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $implode) . ") AND ood.order_option_download_id = '" . (int)$order_option_download_id . "' AND d.status = '1' AND (CASE ood.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(ood.remaining > '0', '1', '0') WHEN '2' THEN IF(ood.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(ood.remaining > '0' AND ood.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'");

			return $query->row;
		} else {
			return null;
		}
	}

	public function updateOrderProductDownload($order_product_download_id) {
		$this->db->query("UPDATE  " . DB_PREFIX . "order_product_download SET remaining = IF(remaining > '0', remaining - 1, remaining), last_accessed = UTC_TIMESTAMP() WHERE order_product_download_id = '" . (int)$order_product_download_id . "'");
	}

	public function updateOrderOptionDownload($order_option_download_id) {
		$this->db->query("UPDATE  " . DB_PREFIX . "order_option_download SET remaining = IF(remaining > '0', remaining - 1, remaining), last_accessed = UTC_TIMESTAMP() WHERE order_option_download_id = '" . (int)$order_option_download_id . "'");
	}

	public function getAccountDownloads($data) {
		$show_expired = (int)$this->config->get('module_product_downloads_cadp_show_expired_downloads');
		$show_free = (int)$this->config->get('module_product_downloads_add_free_downloads_to_order');

		$order_status = array();

		$order_statuses = $this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$order_status[] = "o.order_status_id = '" . (int)$order_status_id . "'";
		}

		if ($order_status) {
			$sql = "SELECT SQL_CALC_FOUND_ROWS download_id, order_product_download_id, order_option_download_id, order_id, name, filename, mask, file_size, `constraint`, remaining, end_time, date_added, date_modified, expired
				FROM
				(SELECT d.download_id, opd.order_product_download_id, NULL AS order_option_download_id, o.order_id, dd.name, d.filename, d.mask, d.file_size, opd.constraint, opd.remaining, opd.end_time, o.date_added, d.date_modified, (CASE opd.constraint WHEN '0' THEN '0' WHEN '1' THEN IF(opd.remaining > '0', '0', '1') WHEN '2' THEN IF(opd.end_time > UTC_TIMESTAMP(), '0', '1') ELSE IF(opd.remaining > '0' AND opd.end_time > UTC_TIMESTAMP(), '0', '1') END) AS expired
					FROM " . DB_PREFIX . "order_product_download opd
					INNER JOIN " . DB_PREFIX . "order_product op ON (opd.order_product_id = op.order_product_id)
					INNER JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)
					INNER JOIN " . DB_PREFIX . "download d ON (opd.download_id = d.download_id)
					LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')
					WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $order_status) . ") AND d.status = '1'" . (!$show_free ? " AND d.is_free = '0'" : "") . (!$show_expired ? " AND (CASE opd.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(opd.remaining > '0', '1', '0') WHEN '2' THEN IF(opd.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(opd.remaining > '0' AND opd.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'" : "") . "
				UNION SELECT d.download_id, NULL AS order_product_download_id, ood.order_option_download_id, o.order_id, dd.name, d.filename, d.mask, d.file_size, ood.constraint, ood.remaining, ood.end_time, o.date_added, d.date_modified, (CASE ood.constraint WHEN '0' THEN '0' WHEN '1' THEN IF(ood.remaining > '0', '0', '1') WHEN '2' THEN IF(ood.end_time > UTC_TIMESTAMP(), '0', '1') ELSE IF(ood.remaining > '0' AND ood.end_time > UTC_TIMESTAMP(), '0', '1') END) AS expired
					FROM " . DB_PREFIX . "order_option_download ood
					INNER JOIN " . DB_PREFIX . "order_option oo ON (ood.order_option_id = oo.order_option_id)
					INNER JOIN `" . DB_PREFIX . "order` o ON (oo.order_id = o.order_id)
					INNER JOIN " . DB_PREFIX . "download d ON (ood.download_id = d.download_id)
					LEFT JOIN " . DB_PREFIX . "download_description dd ON (d.download_id = dd.download_id AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "')
					WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $order_status) . ") AND d.status = '1'" . (!$show_free ? " AND d.is_free = '0'" : "") . (!$show_expired ? " AND (CASE ood.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(ood.remaining > '0', '1', '0') WHEN '2' THEN IF(ood.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(ood.remaining > '0' AND ood.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'" : "") . "
				) AS tbl";

			if (isset($data['search']) && count((array)$data['search'])) {
				$where = array();

				foreach ((array)$data['search'] as $keyword) {
					if ($keyword) {
						$where[] = "(name LIKE '%" . $this->db->escape($keyword) . "%' OR mask LIKE '%" . $this->db->escape($keyword) . "%' OR order_id = '" . (int)$keyword . "')";
					}
				}

				if ($where) {
					$sql .= " WHERE " . implode(' AND ', $where);
				}
			}


			if (!in_array(strtolower($data['order']), array("desc", "asc"))) {
				$data['order'] = "ASC";
			}

			if (!in_array(strtolower($data['sort']), array("order_id", "name", "file_size", "date_added", "date_modified"))) {
				$data['sort'] = "date_added";
			}

			$sql .= " ORDER BY " . $data['sort'] . " " . $data['order'];

			if ((int)$data['limit'] < 0) {
				$data['limit'] = 0;
			}

			if ((int)$data['start'] < 0) {
				$data['start'] = 0;
			}

			if ((int)$data['limit'] > 0 && (int)$data['start'] + (int)$data['per_page'] > (int)$data['limit'] || !(int)$data['per_page'] && (int)$data['limit'] > 0) {
				$count = (int)$data['limit'] - (int)$data['start'];
			} else {
				$count = (int)$data['per_page'];
			}

			if ($count > 0) {
				$sql .= " LIMIT " . (int)$data['start'] . "," . $count;
			}

			$query = $this->db->query($sql);

			$count = $this->db->query("SELECT FOUND_ROWS() AS count");
			$this->last_download_count = ($count->num_rows) ? ($data['limit'] && $data['limit'] < (int)$count->row['count'] ? $data['limit'] : (int)$count->row['count']) : 0;

			return $query->rows;
		} else {
			return array();
		}
	}

	public function getAccountTotalDownloads() {
		$show_expired = (int)$this->config->get('module_product_downloads_cadp_show_expired_downloads');
		$show_free = (int)$this->config->get('module_product_downloads_add_free_downloads_to_order');

		$order_status = array();

		$order_statuses = $this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$order_status[] = "o.order_status_id = '" . (int)$order_status_id . "'";
		}

		if ($order_status) {
			$sql = "SELECT SUM(total) AS total
				FROM
				(SELECT COUNT(*) AS total
					FROM " . DB_PREFIX . "order_product_download opd
					INNER JOIN " . DB_PREFIX . "order_product op ON (opd.order_product_id = op.order_product_id)
					INNER JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id)
					INNER JOIN " . DB_PREFIX . "download d ON (opd.download_id = d.download_id)
					WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $order_status) . ") AND d.status = '1'" . (!$show_free ? " AND d.is_free = '0'" : "") . (!$show_expired ? " AND (CASE opd.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(opd.remaining > '0', '1', '0') WHEN '2' THEN IF(opd.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(opd.remaining > '0' AND opd.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'" : "") . "
				UNION SELECT COUNT(*) AS total
					FROM " . DB_PREFIX . "order_option_download ood
					INNER JOIN " . DB_PREFIX . "order_option oo ON (ood.order_option_id = oo.order_option_id)
					INNER JOIN `" . DB_PREFIX . "order` o ON (oo.order_id = o.order_id)
					INNER JOIN " . DB_PREFIX . "download d ON (ood.download_id = d.download_id)
					WHERE o.customer_id = '" . (int)$this->customer->getId() . "' AND (" . implode(" OR ", $order_status) . ") AND d.status = '1'" . (!$show_free ? " AND d.is_free = '0'" : "") . (!$show_expired ? " AND (CASE ood.constraint WHEN '0' THEN '1' WHEN '1' THEN IF(ood.remaining > '0', '1', '0') WHEN '2' THEN IF(ood.end_time > UTC_TIMESTAMP(), '1', '0') ELSE IF(ood.remaining > '0' AND ood.end_time > UTC_TIMESTAMP(), '1', '0') END) = '1'" : "") . "
				) AS tbl";

			$query = $this->db->query($sql);

			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function getModulesByCode($code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "module` WHERE `code` = '" . $this->db->escape($code) . "' ORDER BY `name`");

		return $query->rows;
	}

	public function addOrderDownloads($order_id) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "order_product_download (order_product_id, download_id, `constraint`, remaining, duration, end_time) SELECT op.order_product_id, d.download_id, d.constraint, IF(d.total_downloads = '-1', d.total_downloads, d.total_downloads * op.quantity), d.duration * op.quantity, UTC_TIMESTAMP + INTERVAL d.duration * op.quantity SECOND FROM " . DB_PREFIX . "order_product op INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (op.product_id = p2d.product_id) INNER JOIN " . DB_PREFIX . "download d ON (p2d.download_id = d.download_id) WHERE op.order_id = '" . (int)$order_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_option_download (order_option_id, download_id, `constraint`, remaining, duration, end_time) SELECT oo.order_option_id, d.download_id, d.constraint, IF(d.total_downloads = '-1', d.total_downloads, d.total_downloads * op.quantity), d.duration * op.quantity, UTC_TIMESTAMP + INTERVAL d.duration * op.quantity SECOND FROM " . DB_PREFIX . "order_option oo INNER JOIN " . DB_PREFIX . "order_product op ON (oo.order_product_id = op.order_product_id) INNER JOIN " . DB_PREFIX . "product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) INNER JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) INNER JOIN " . DB_PREFIX . "download d ON (ov.download_id = d.download_id) WHERE oo.order_id = '" . (int)$order_id . "' AND ov.download_id IS NOT NULL");
	}

	public function editOrderDownloads($order_id) {
		$this->db->query("DELETE opd FROM " . DB_PREFIX . "order_product_download opd LEFT JOIN " . DB_PREFIX . "order_product op ON (opd.order_product_id = op.order_product_id) WHERE op.order_product_id IS NULL OR op.order_id = '" . (int)$order_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_product_download (order_product_id, download_id, `constraint`, remaining, duration, end_time) SELECT op.order_product_id, d.download_id, d.constraint, IF(d.total_downloads = '-1', d.total_downloads, d.total_downloads * op.quantity), d.duration * op.quantity, UTC_TIMESTAMP + INTERVAL d.duration * op.quantity SECOND FROM " . DB_PREFIX . "order_product op INNER JOIN " . DB_PREFIX . "product_to_download p2d ON (op.product_id = p2d.product_id) INNER JOIN " . DB_PREFIX . "download d ON (p2d.download_id = d.download_id) WHERE op.order_id = '" . (int)$order_id . "'");

		$this->db->query("DELETE ood FROM " . DB_PREFIX . "order_option_download ood LEFT JOIN " . DB_PREFIX . "order_option oo ON (ood.order_option_id = oo.order_option_id) WHERE oo.order_option_id IS NULL OR oo.order_id = '" . (int)$order_id . "'");

		$this->db->query("INSERT INTO " . DB_PREFIX . "order_option_download (order_option_id, download_id, `constraint`, remaining, duration, end_time) SELECT oo.order_option_id, d.download_id, d.constraint, IF(d.total_downloads = '-1', d.total_downloads, d.total_downloads * op.quantity), d.duration * op.quantity, UTC_TIMESTAMP + INTERVAL d.duration * op.quantity SECOND FROM " . DB_PREFIX . "order_option oo INNER JOIN " . DB_PREFIX . "order_product op ON (oo.order_product_id = op.order_product_id) INNER JOIN " . DB_PREFIX . "product_option_value pov ON (oo.product_option_value_id = pov.product_option_value_id) INNER JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) INNER JOIN " . DB_PREFIX . "download d ON (ov.download_id = d.download_id) WHERE oo.order_id = '" . (int)$order_id . "' AND ov.download_id IS NOT NULL");
	}

	public function deleteOrderDownloads($order_id) {
		$this->db->query("DELETE opd FROM " . DB_PREFIX . "order_product_download opd INNER JOIN " . DB_PREFIX . "order_product op ON (opd.order_product_id = op.order_product_id) WHERE op.order_id = '" . (int)$order_id . "'");

		$this->db->query("DELETE ood FROM " . DB_PREFIX . "order_option_download ood INNER JOIN " . DB_PREFIX . "order_option oo ON (ood.order_option_id = oo.order_option_id) WHERE oo.order_id = '" . (int)$order_id . "'");
	}
}
