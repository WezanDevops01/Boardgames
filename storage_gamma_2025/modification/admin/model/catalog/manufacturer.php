<?php
class ModelCatalogManufacturer extends Model {
	public function addManufacturer($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

		$manufacturer_id = $this->db->getLastId();

			
			foreach ($data['manufacturer_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_description SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', custom_title = '" . $this->db->escape($value['custom_title']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
			}
			
			

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		}

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
				
		// SEO URL
		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		

				
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` like 'seopack%'");
			
				foreach ($query->rows as $result) {
						if (!$result['serialized']) {
							$data[$result['key']] = $result['value'];
						} else {
							if ($result['value'][0] == '{') {$data[$result['key']] = json_decode($result['value'], true);} else {$data[$result['key']] = unserialize($result['value']);}
						}
					}
					
				if (isset($data)) {$seopack_parameters = $data['seopack_parameters'];}
				
				if ((isset($seopack_parameters['autourls'])) && ($seopack_parameters['autourls']))
					{	
						require_once(DIR_APPLICATION . 'controller/extension/extension/seopack.php');
						$seo = new ControllerExtensionExtensionSeoPack('');
						
						$query = $this->db->query("SELECT l.language_id, m.name, m.manufacturer_id, l.code from ".DB_PREFIX."manufacturer m 
						join ".DB_PREFIX."language l
						where m.manufacturer_id = '" . (int)$manufacturer_id . "';");
						

						foreach ($query->rows as $manufacturer_row){	

							
							if( strlen($manufacturer_row['name']) > 1 ){
								
								$slug = $seo->generateSlug($manufacturer_row['name'].'-'.$manufacturer_row['code']);
								$exist_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.query = 'manufacturer_id=" . $manufacturer_row['manufacturer_id'] . "' and language_id=".$manufacturer_row['language_id']);
								
								if(!$exist_query->num_rows){
									
									$exist_keyword = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "'");
									if($exist_keyword->num_rows){ $slug = $seo->generateSlug($manufacturer_row['name']).'-'.rand();}
										
										
									$add_query = "INSERT INTO " . DB_PREFIX . "seo_url (query, keyword,language_id) VALUES ('manufacturer_id=" . $manufacturer_row['manufacturer_id'] . "', '" . $slug . "', " . $manufacturer_row['language_id'] . ")";
									$this->db->query($add_query);
									
								}
							}
						}									
					}
				
			
		$this->cache->delete('manufacturer');

		return $manufacturer_id;
	}

	public function editManufacturer($manufacturer_id, $data) {

			if (VERSION >= '2.3.0.0') {
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
			
			foreach ($data['manufacturer_description'] as $language_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_description SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', custom_title = '" . $this->db->escape($value['custom_title']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "'");
			}
			}
			
			
		$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_store'])) {
			foreach ($data['manufacturer_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		if (isset($data['manufacturer_seo_url'])) {
			foreach ($data['manufacturer_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}


				
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` like 'seopack%'");
			
				foreach ($query->rows as $result) {
						if (!$result['serialized']) {
							$data[$result['key']] = $result['value'];
						} else {
							if ($result['value'][0] == '{') {$data[$result['key']] = json_decode($result['value'], true);} else {$data[$result['key']] = unserialize($result['value']);}
						}
					}
					
				if (isset($data)) {$seopack_parameters = $data['seopack_parameters'];}
				
				if ((isset($seopack_parameters['autourls'])) && ($seopack_parameters['autourls']))
					{	
						require_once(DIR_APPLICATION . 'controller/extension/extension/seopack.php');
						$seo = new ControllerExtensionExtensionSeoPack('');
						
						$query = $this->db->query("SELECT l.language_id, m.name, m.manufacturer_id, l.code from ".DB_PREFIX."manufacturer m 
						join ".DB_PREFIX."language l
						where m.manufacturer_id = '" . (int)$manufacturer_id . "';");
						

						foreach ($query->rows as $manufacturer_row){	

							
							if( strlen($manufacturer_row['name']) > 1 ){
								
								$slug = $seo->generateSlug($manufacturer_row['name'].'-'.$manufacturer_row['code']);
								$exist_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.query = 'manufacturer_id=" . $manufacturer_row['manufacturer_id'] . "' and language_id=".$manufacturer_row['language_id']);
								
								if(!$exist_query->num_rows){
									
									$exist_keyword = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "'");
									if($exist_keyword->num_rows){ $slug = $seo->generateSlug($manufacturer_row['name']).'-'.rand();}
										
										
									$add_query = "INSERT INTO " . DB_PREFIX . "seo_url (query, keyword,language_id) VALUES ('manufacturer_id=" . $manufacturer_row['manufacturer_id'] . "', '" . $slug . "', " . $manufacturer_row['language_id'] . ")";
									$this->db->query($add_query);
									
								}
							}
						}									
					}
				
			
		$this->cache->delete('manufacturer');
	}

	public function deleteManufacturer($manufacturer_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

			$this->db->query("DELETE FROM " . DB_PREFIX . "manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");			
			


				
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` like 'seopack%'");
			
				foreach ($query->rows as $result) {
						if (!$result['serialized']) {
							$data[$result['key']] = $result['value'];
						} else {
							if ($result['value'][0] == '{') {$data[$result['key']] = json_decode($result['value'], true);} else {$data[$result['key']] = unserialize($result['value']);}
						}
					}
					
				if (isset($data)) {$seopack_parameters = $data['seopack_parameters'];}
				
				if ((isset($seopack_parameters['autourls'])) && ($seopack_parameters['autourls']))
					{	
						require_once(DIR_APPLICATION . 'controller/extension/extension/seopack.php');
						$seo = new ControllerExtensionExtensionSeoPack('');
						
						$query = $this->db->query("SELECT l.language_id, m.name, m.manufacturer_id, l.code from ".DB_PREFIX."manufacturer m 
						join ".DB_PREFIX."language l
						where m.manufacturer_id = '" . (int)$manufacturer_id . "';");
						

						foreach ($query->rows as $manufacturer_row){	

							
							if( strlen($manufacturer_row['name']) > 1 ){
								
								$slug = $seo->generateSlug($manufacturer_row['name'].'-'.$manufacturer_row['code']);
								$exist_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.query = 'manufacturer_id=" . $manufacturer_row['manufacturer_id'] . "' and language_id=".$manufacturer_row['language_id']);
								
								if(!$exist_query->num_rows){
									
									$exist_keyword = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "'");
									if($exist_keyword->num_rows){ $slug = $seo->generateSlug($manufacturer_row['name']).'-'.rand();}
										
										
									$add_query = "INSERT INTO " . DB_PREFIX . "seo_url (query, keyword,language_id) VALUES ('manufacturer_id=" . $manufacturer_row['manufacturer_id'] . "', '" . $slug . "', " . $manufacturer_row['language_id'] . ")";
									$this->db->query($add_query);
									
								}
							}
						}									
					}
				
			
		$this->cache->delete('manufacturer');
	}


			public function getManufacturerDescriptions($manufacturer_id) {
				$manufacturer_description_data = array();
				
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
				
				foreach ($query->rows as $result) {
					$manufacturer_description_data[$result['language_id']] = array(
						'meta_keyword'     => $result['meta_keyword'],
						'meta_description' => $result['meta_description'],
						'description'      => $result['description'],
						'custom_title'     => $result['custom_title']
					);
				}
				
				return $manufacturer_description_data;
			}
			
	public function getManufacturer($manufacturer_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row;
	}

	public function getManufacturers($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "manufacturer";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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

	public function getManufacturerStores($manufacturer_id) {
		$manufacturer_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_store_data[] = $result['store_id'];
		}

		return $manufacturer_store_data;
	}
	
	public function getManufacturerSeoUrls($manufacturer_id) {
		$manufacturer_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $result) {
			$manufacturer_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $manufacturer_seo_url_data;
	}
	
	public function getTotalManufacturers() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer");

		return $query->row['total'];
	}
}
