<?php
class ModelLocalisationDeliveryStatus extends Model {
	public function addDeliveryStatus($data) {
		foreach ($data['delivery_status'] as $language_id => $value) {
			if (isset($delivery_status_id)) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "delivery_status SET delivery_status_id = '" . (int)$delivery_status_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
			} else {
				$this->db->query("INSERT INTO " . DB_PREFIX . "delivery_status SET language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");

				$delivery_status_id = $this->db->getLastId();
			}
		}

		$this->cache->delete('delivery_status');
		
		return $delivery_status_id;
	}

	public function editDeliveryStatus($delivery_status_id, $data) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "delivery_status WHERE delivery_status_id = '" . (int)$delivery_status_id . "'");

		foreach ($data['delivery_status'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "delivery_status SET delivery_status_id = '" . (int)$delivery_status_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "'");
		}

		$this->cache->delete('delivery_status');
	}

	public function deleteDeliveryStatus($delivery_status_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "delivery_status WHERE delivery_status_id = '" . (int)$delivery_status_id . "'");

		$this->cache->delete('delivery_status');
	}

	public function getDeliveryStatus($delivery_status_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "delivery_status WHERE delivery_status_id = '" . (int)$delivery_status_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getDeliveryStatuses($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "delivery_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$sql .= " ORDER BY name";

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
		} else {
			$delivery_status_data = $this->cache->get('delivery_status.' . (int)$this->config->get('config_language_id'));

			if (!$delivery_status_data) {
				$query = $this->db->query("SELECT delivery_status_id, name FROM " . DB_PREFIX . "delivery_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY name");

				$delivery_status_data = $query->rows;

				$this->cache->set('delivery_status.' . (int)$this->config->get('config_language_id'), $delivery_status_data);
			}

			return $delivery_status_data;
		}
	}

	public function getDeliveryStatusDescriptions($delivery_status_id) {
		$delivery_status_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "delivery_status WHERE delivery_status_id = '" . (int)$delivery_status_id . "'");

		foreach ($query->rows as $result) {
			$delivery_status_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $delivery_status_data;
	}

	public function getTotalDeliveryStatuses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "delivery_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
		 public function getDeliveryStatusIdByText($text) {
        $sql = "SELECT delivery_status_id FROM " . DB_PREFIX . "delivery_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND LCASE(name) LIKE '%%" . $this->db->escape(mb_strtolower($text, 'UTF-8')) . "%%'";

        $query = $this->db->query($sql);

        // Return the delivery_status_id if a single match is found
        if ($query->num_rows > 0) {
            return $query->row['delivery_status_id'];
        }

        return null; // Return null if no match is found
    }
    
    // You also need this method if you followed the previous suggestion
    public function getDeliveryStatusNameById($delivery_status_id) {
        $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "delivery_status WHERE delivery_status_id = '" . (int)$delivery_status_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
        
        if ($query->num_rows) {
            return $query->row['name'];
        }
        
        return null;
    }
}