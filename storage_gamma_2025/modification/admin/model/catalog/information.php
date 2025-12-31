<?php
class ModelCatalogInformation extends Model {
	public function addInformation($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "information SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "'");

		$information_id = $this->db->getLastId();

		foreach ($data['information_description'] as $language_id => $value) {
$this->db->query("INSERT INTO " . DB_PREFIX . "information_description_seo SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', custom_h1 = '" . $this->db->escape($value['custom_h1']) . "', custom_h2 = '" . $this->db->escape($value['custom_h2']) . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_description SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_store SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
		
		$this->db->query("UPDATE `" . DB_PREFIX . "information_description` SET 
            `instock_description` = '" . $this->db->escape($data['instock_description']) . "',
            `preorder_description` = '" . $this->db->escape($data['preorder_description']) . "',
            `coming_soon_description` = '" . $this->db->escape($data['coming_soon_description']) . "',
            `reordered_description` = '" . $this->db->escape($data['reordered_description']) . "',
            `restocked_description` = '" . $this->db->escape($data['restocked_description']) . "',
            `new_description` = '" . $this->db->escape($data['new_description']) . "',
            `like_new_description` = '" . $this->db->escape($data['like_new_description']) . "',
            `very_good_description` = '" . $this->db->escape($data['very_good_description']) . "',
            `good_description` = '" . $this->db->escape($data['good_description']) . "',
            `acceptable_description` = '" . $this->db->escape($data['acceptable_description']) . "'
            WHERE `information_id` = '" . (int)$information_id . "'
        ");

		// SEO URL
		if (isset($data['information_seo_url'])) {
			foreach ($data['information_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_layout SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
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
				
				if (isset($seopack_parameters['ext'])) { $ext = $seopack_parameters['ext'];}
					else {$ext = '';}
				
				if ((isset($seopack_parameters['autourls'])) && ($seopack_parameters['autourls']))
					{	
						require_once(DIR_APPLICATION . 'controller/extension/extension/seopack.php');
						$seo = new ControllerExtensionExtensionSeoPack('');
						
						$query = $this->db->query("SELECT id.information_id, id.title, id.language_id, l.code FROM ".DB_PREFIX."information i
							inner join ".DB_PREFIX."information_description id on i.information_id = id.information_id 
							inner join ".DB_PREFIX."language l on l.language_id = id.language_id
							where i.information_id = '" . (int)$information_id . "';");
						
						foreach ($query->rows as $info_row){
							
							if( strlen($info_row['title']) > 1 ){
								
								$slug = $seo->generateSlug($info_row['title']).$ext;
								$exist_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.query = 'information_id=" . $info_row['information_id'] . "'  and language_id=".$info_row['language_id']);
								
								if(!$exist_query->num_rows){
									
									$exist_keyword = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "'");
									if($exist_keyword->num_rows){ 
										$exist_keyword_lang = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "' AND " . DB_PREFIX . "seo_url.query <> 'information_id=" . $info_row['information_id'] . "'");
										if($exist_keyword_lang->num_rows){
												$slug = $seo->generateSlug($info_row['title']).'-'.rand();
											}
											else
											{
												$slug = $seo->generateSlug($info_row['title']).'-'.$info_row['code'];
											}
										}	
									
									$add_query = "INSERT INTO " . DB_PREFIX . "seo_url (query, keyword, language_id) VALUES ('information_id=" . $info_row['information_id'] . "', '" . $slug . "', " . $info_row['language_id'] . ")";
									$this->db->query($add_query);
									
								}
							}
						}										
					}
				
				
			
		$this->cache->delete('information');

		return $information_id;
	}

	public function editInformation($information_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "information SET sort_order = '" . (int)$data['sort_order'] . "', bottom = '" . (isset($data['bottom']) ? (int)$data['bottom'] : 0) . "', status = '" . (int)$data['status'] . "' WHERE information_id = '" . (int)$information_id . "'");

$this->db->query("DELETE FROM " . DB_PREFIX . "information_description_seo WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");

		foreach ($data['information_description'] as $language_id => $value) {
$this->db->query("INSERT INTO " . DB_PREFIX . "information_description_seo SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', custom_h1 = '" . $this->db->escape($value['custom_h1']) . "', custom_h2 = '" . $this->db->escape($value['custom_h2']) . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "information_description SET information_id = '" . (int)$information_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		
		$get_query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");

        if ($get_query->row['total'] > 0) {
            // Update the record with the description fields from the data array
            $this->db->query("UPDATE " . DB_PREFIX . "information_description SET 
                `instock_description` = '" . $this->db->escape($data['instock_description']) . "',
            `preorder_description` = '" . $this->db->escape($data['preorder_description']) . "',
            `coming_soon_description` = '" . $this->db->escape($data['coming_soon_description']) . "',
            `reordered_description` = '" . $this->db->escape($data['reordered_description']) . "',
            `restocked_description` = '" . $this->db->escape($data['restocked_description']) . "',
            `new_description` = '" . $this->db->escape($data['new_description']) . "',
            `like_new_description` = '" . $this->db->escape($data['like_new_description']) . "',
            `very_good_description` = '" . $this->db->escape($data['very_good_description']) . "',
            `good_description` = '" . $this->db->escape($data['good_description']) . "',
            `acceptable_description` = '" . $this->db->escape($data['acceptable_description']) . "'
            WHERE `information_id` = '" . (int)$information_id . "'
            ");
        }

		$this->db->query("DELETE FROM " . DB_PREFIX . "information_to_store WHERE information_id = '" . (int)$information_id . "'");

		if (isset($data['information_store'])) {
			foreach ($data['information_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "information_to_store SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'information_id=" . (int)$information_id . "'");

		if (isset($data['information_seo_url'])) {
			foreach ($data['information_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (trim($keyword)) {
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'information_id=" . (int)$information_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE information_id = '" . (int)$information_id . "'");

		if (isset($data['information_layout'])) {
			foreach ($data['information_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "information_to_layout` SET information_id = '" . (int)$information_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
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
				
				if (isset($seopack_parameters['ext'])) { $ext = $seopack_parameters['ext'];}
					else {$ext = '';}
				
				if ((isset($seopack_parameters['autourls'])) && ($seopack_parameters['autourls']))
					{	
						require_once(DIR_APPLICATION . 'controller/extension/extension/seopack.php');
						$seo = new ControllerExtensionExtensionSeoPack('');
						
						$query = $this->db->query("SELECT id.information_id, id.title, id.language_id, l.code FROM ".DB_PREFIX."information i
							inner join ".DB_PREFIX."information_description id on i.information_id = id.information_id 
							inner join ".DB_PREFIX."language l on l.language_id = id.language_id
							where i.information_id = '" . (int)$information_id . "';");
						
						foreach ($query->rows as $info_row){
							
							if( strlen($info_row['title']) > 1 ){
								
								$slug = $seo->generateSlug($info_row['title']).$ext;
								$exist_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.query = 'information_id=" . $info_row['information_id'] . "'  and language_id=".$info_row['language_id']);
								
								if(!$exist_query->num_rows){
									
									$exist_keyword = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "'");
									if($exist_keyword->num_rows){ 
										$exist_keyword_lang = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "' AND " . DB_PREFIX . "seo_url.query <> 'information_id=" . $info_row['information_id'] . "'");
										if($exist_keyword_lang->num_rows){
												$slug = $seo->generateSlug($info_row['title']).'-'.rand();
											}
											else
											{
												$slug = $seo->generateSlug($info_row['title']).'-'.$info_row['code'];
											}
										}	
									
									$add_query = "INSERT INTO " . DB_PREFIX . "seo_url (query, keyword, language_id) VALUES ('information_id=" . $info_row['information_id'] . "', '" . $slug . "', " . $info_row['language_id'] . ")";
									$this->db->query($add_query);
									
								}
							}
						}										
					}
				
				
			
		$this->cache->delete('information');
	}

	public function deleteInformation($information_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_description` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_store` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "information_to_layout` WHERE information_id = '" . (int)$information_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'information_id=" . (int)$information_id . "'");


				
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` like 'seopack%'");
			
				foreach ($query->rows as $result) {
						if (!$result['serialized']) {
							$data[$result['key']] = $result['value'];
						} else {
							if ($result['value'][0] == '{') {$data[$result['key']] = json_decode($result['value'], true);} else {$data[$result['key']] = unserialize($result['value']);}
						}
					}
					
				if (isset($data)) {$seopack_parameters = $data['seopack_parameters'];}
				
				if (isset($seopack_parameters['ext'])) { $ext = $seopack_parameters['ext'];}
					else {$ext = '';}
				
				if ((isset($seopack_parameters['autourls'])) && ($seopack_parameters['autourls']))
					{	
						require_once(DIR_APPLICATION . 'controller/extension/extension/seopack.php');
						$seo = new ControllerExtensionExtensionSeoPack('');
						
						$query = $this->db->query("SELECT id.information_id, id.title, id.language_id, l.code FROM ".DB_PREFIX."information i
							inner join ".DB_PREFIX."information_description id on i.information_id = id.information_id 
							inner join ".DB_PREFIX."language l on l.language_id = id.language_id
							where i.information_id = '" . (int)$information_id . "';");
						
						foreach ($query->rows as $info_row){
							
							if( strlen($info_row['title']) > 1 ){
								
								$slug = $seo->generateSlug($info_row['title']).$ext;
								$exist_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.query = 'information_id=" . $info_row['information_id'] . "'  and language_id=".$info_row['language_id']);
								
								if(!$exist_query->num_rows){
									
									$exist_keyword = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "'");
									if($exist_keyword->num_rows){ 
										$exist_keyword_lang = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "' AND " . DB_PREFIX . "seo_url.query <> 'information_id=" . $info_row['information_id'] . "'");
										if($exist_keyword_lang->num_rows){
												$slug = $seo->generateSlug($info_row['title']).'-'.rand();
											}
											else
											{
												$slug = $seo->generateSlug($info_row['title']).'-'.$info_row['code'];
											}
										}	
									
									$add_query = "INSERT INTO " . DB_PREFIX . "seo_url (query, keyword, language_id) VALUES ('information_id=" . $info_row['information_id'] . "', '" . $slug . "', " . $info_row['language_id'] . ")";
									$this->db->query($add_query);
									
								}
							}
						}										
					}
				
				
			
		$this->cache->delete('information');
	}

	public function getInformation($information_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "information WHERE information_id = '" . (int)$information_id . "'");

		return $query->row;
	}

	public function getInformations($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = array(
				'id.title',
				'i.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY id.title";
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
		} else {
			$information_data = $this->cache->get('information.' . (int)$this->config->get('config_language_id'));

			if (!$information_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information i LEFT JOIN " . DB_PREFIX . "information_description id ON (i.information_id = id.information_id) WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY id.title");

				$information_data = $query->rows;

				$this->cache->set('information.' . (int)$this->config->get('config_language_id'), $information_data);
			}

			return $information_data;
		}
	}

	public function getInformationDescriptions($information_id) {
		$information_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_description WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'instock_description'     => $result['instock_description'],
				'preorder_description'     => $result['preorder_description'],
				'coming_soon_description'   => $result['coming_soon_description'],
				'reordered_description'     => $result['reordered_description'],
				'restocked_description'     => $result['restocked_description'],
				'new_description'     => $result['new_description'],
				'like_new_description'     => $result['like_new_description'],
				'very_good_description'   => $result['very_good_description'],
				'good_description'     => $result['good_description'],
				'acceptable_description'     => $result['acceptable_description']
			);
		}


			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_description_seo WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_description_data[$result['language_id']] = array_merge($information_description_data[$result['language_id']] ,array(
				'custom_imgtitle' => $result['custom_imgtitle'],
				'custom_alt'      => $result['custom_alt'],
				'custom_h1'       => $result['custom_h1'],
				'custom_h2'		  => $result['custom_h2']
			));
		}
			
		return $information_description_data;
	}

	public function getInformationStores($information_id) {
		$information_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_store WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_store_data[] = $result['store_id'];
		}

		return $information_store_data;
	}

	public function getInformationSeoUrls($information_id) {
		$information_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'information_id=" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $information_seo_url_data;
	}

	public function getInformationLayouts($information_id) {
		$information_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "'");

		foreach ($query->rows as $result) {
			$information_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $information_layout_data;
	}

	public function getTotalInformations() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information");

		return $query->row['total'];
	}

	public function getTotalInformationsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
}