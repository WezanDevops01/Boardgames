<?php
class ModelExtensionModuleHbProducts extends Model {
	public function install(){
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."hbp_clipboard` (
			`product_id` int(11) NULL
			)DEFAULT CHARSET=utf8");

		if ((version_compare(VERSION,'2.0.0.0','>=' )) and (version_compare(VERSION,'2.3.0.0','<' ))) {
			$ocmod_filename = 'ocmod_product_manager_2000_2200.txt';
			$ocmod_name = 'Product Manager PRO [2000 - 2200]';
		}else if ((version_compare(VERSION,'2.3.0.0','>=' )) and (version_compare(VERSION,'3.0.0.0','<' ))) {
			$ocmod_filename = 'ocmod_product_manager_23xx.txt';
			$ocmod_name = 'Product Manager PRO [23xx]';
		}else if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$ocmod_filename = 'ocmod_product_manager_3xxx.txt';
			$ocmod_name = 'Product Manager PRO [3.x.x.x]';
		}

		$ocmod_version = EXTENSION_VERSION;
		$ocmod_code = 'huntbee_product_manager';	
		$ocmod_author = 'HuntBee OpenCart Services';
		$ocmod_link = 'https://www.huntbee.com';

		$file = DIR_APPLICATION . 'view/template/extension/module/ocmod/'.$ocmod_filename;
		if (file_exists($file)) {
			$ocmod_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			$ocmod_xml = str_replace('{huntbee_version}',$ocmod_version,$ocmod_xml);
			$this->db->query("INSERT INTO " . DB_PREFIX . "modification SET code = '" . $this->db->escape($ocmod_code) . "', name = '" . $this->db->escape($ocmod_name) . "', author = '" . $this->db->escape($ocmod_author) . "', version = '" . $this->db->escape($ocmod_version) . "', link = '" . $this->db->escape($ocmod_link) . "', xml = '" . $this->db->escape($ocmod_xml) . "', status = '1', date_added = NOW()");
		}
		
		$this->db->query("INSERT INTO `".DB_PREFIX."setting` (`code`, `key`, `value`, `serialized`) VALUES ('module_hb_products','module_hb_products_status', '1','0')");

	}
	
	public function uninstall(){
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hbp_clipboard`");
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_product_manager'");
		
		$this->db->query("DELETE FROM `".DB_PREFIX."setting` WHERE `key` = 'module_hb_products_status'");
	}

	public function check_updates(){
		$data =  array();
		$file = DIR_APPLICATION . 'view/template/extension/module/ocmod/updates_product_manager.json';
		if (file_exists($file)) {
			$data = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			$data = json_decode($data, true);

			if ($data['version'] <= EXTENSION_VERSION) {
				$data =  array();
			}
			return $data;
		}
	}

	public function table_columns(){
		$columns = array('product_id','category','model','sku','upc','ean','jan','isbn','mpn','location','quantity','stock_status_id','image','manufacturer_id','shipping','price','points','tax_class_id','weight','weight_class_id','length','width','height','length_class_id','subtract','minimum','sort_order','status','viewed','date_added','date_modified','date_release','release_status_id');
		return $columns;
	}

	public function quick_form_columns(){
		$columns = array('description','seo','category','model','sku','upc','ean','jan','isbn','mpn','location','quantity','manufacturer','price','status','keyword');
		return $columns;
	}
	
	public function getRecords($data){
		$sql = "SELECT *, (SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = product.manufacturer_id) as manufacturer FROM " . DB_PREFIX . "product product LEFT JOIN " . DB_PREFIX . "product_description product_description ON (product.product_id = product_description.product_id)";

		if (!empty($data['add_table_query'])) {
			$sql .= " LEFT JOIN ".DB_PREFIX.$data['add_table_query'];
		} 

		$sql .= " WHERE product_description.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['query'])) {
			$sql .= " AND ".html_entity_decode($data['query'], ENT_QUOTES, 'UTF-8');
		} 

		if (!empty($data['add_table_filter'])) {
			$sql .= " AND ".html_entity_decode($data['add_table_filter'], ENT_QUOTES, 'UTF-8');
		} 

		if (!empty($data['search'])) {
			$sql .= " AND (product.product_id LIKE '%".$this->db->escape($data['search'])."%' OR product_description.name LIKE '%".$this->db->escape($data['search'])."%' OR product.model LIKE '%".$this->db->escape($data['search'])."%' OR product.sku LIKE '%".$this->db->escape($data['search'])."%')";
		}
		
		$sql .= " GROUP BY product.product_id";
		
		if (isset($data['sort_parameter'])) {
			$sql .= " ORDER BY ".$data['sort_parameter'];
		}else{
			$sql .= " ORDER BY product.date_added";
		}

		if (isset($data['sort_order'])) {
			$sql .= " ".$data['sort_order'];
		}else{
			$sql .= " DESC";
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
		
		//$this->log->write($sql);
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getTotalRecords($data){
		$sql = "SELECT COUNT(DISTINCT product.product_id) as total FROM " . DB_PREFIX . "product product LEFT JOIN " . DB_PREFIX . "product_description product_description ON (product.product_id = product_description.product_id) ";

		if (!empty($data['add_table_query'])) {
			$sql .= " LEFT JOIN ".DB_PREFIX.$data['add_table_query'];
		} 

		$sql .= " WHERE product_description.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		
		if (!empty($data['query'])) {
			$sql .= " AND ".html_entity_decode($data['query'], ENT_QUOTES, 'UTF-8');
		} 

		if (!empty($data['add_table_filter'])) {
			$sql .= " AND ".html_entity_decode($data['add_table_filter'], ENT_QUOTES, 'UTF-8');
		}

		if (!empty($data['search'])) {
			$sql .= " AND (product.product_id LIKE '%".$this->db->escape($data['search'])."%' OR product_description.name LIKE '%".$this->db->escape($data['search'])."%' OR product.model LIKE '%".$this->db->escape($data['search'])."%' OR product.sku LIKE '%".$this->db->escape($data['search'])."%')";
		}

		$results = $this->db->query($sql);
		return $results->row['total'];
	}
	
	public function getTables() {
		$table_data = array();

		$query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

		foreach ($query->rows as $result) {
			if (isset($result['Tables_in_' . DB_DATABASE])) {
				$table_data[] = str_replace(DB_PREFIX,'',$result['Tables_in_' . DB_DATABASE]);
			}
		}

		return $table_data;
	}

	public function getColumns($tablename) {
		$column_data = array();

		$query = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" .DB_PREFIX. $tablename . "'");

		foreach ($query->rows as $result) {
			if (isset($result['COLUMN_NAME'])) {
				$column_data[] = $result['COLUMN_NAME'];
			}
		}

		return $column_data;
	}

	public function getdatatype($tablename, $column_name) {
		$query = $this->db->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" .DB_PREFIX. $tablename . "' AND COLUMN_NAME = '".$column_name."'");
		$data_type = $query->row['DATA_TYPE'];
		return $data_type;
	}

	public function getProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'reward'           => $query->row['reward'],
				'points'           => $query->row['points'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'date_available'   => $query->row['date_available'],
				'weight'           => $query->row['weight'],
				'weight_class_id'  => $query->row['weight_class_id'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'length_class_id'  => $query->row['length_class_id'],
				'subtract'         => $query->row['subtract'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
	}

	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

		return $query->rows;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT *, (SELECT GROUP_CONCAT(cd1.name ORDER BY level SEPARATOR '&nbsp;&#47;&nbsp;') FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id AND cp.category_id != cp.path_id) WHERE cp.category_id = c.category_id AND cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY cp.category_id) AS path FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (c.category_id = cd2.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function update_column_value($table_name, $column_id, $product_id, $updated_value){
		$this->db->query("UPDATE `" . DB_PREFIX . $table_name."` SET `".$column_id."` = '".$this->db->escape($updated_value)."' WHERE `product_id` = '".(int)$product_id."'");
		 // when quantity is 0 then stock_status_id is comming soon 
                if (($column_id == 'quantity' && $updated_value == 0) || 
                    ($column_id == 'stock_status_id' && $updated_value == 15)) {                    
					 //  Remove the product from the 'Coming Soon' module if it exists
                    $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
                    
                    if ($query->num_rows) {
                        $moduleData = json_decode($query->row['module_data'], true);
                        
                        // Check if 'products' exists in the filter and is an array
                        if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                            $products = &$moduleData['general']['filter']['products'];
                            
                            // Remove the product_id if it exists
                            $productIdString = (string)$product_id;
                            $key = array_search($productIdString, $products);
                            
                            if ($key !== false) {
                                unset($products[$key]);
                                $products = array_values($products); // Reindex the array
                                
                                // Update the module_data JSON in the database
                                $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                                $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                            }
                        }
                    }
                    
                                 // remove product from coming soon games section  
                    $query = $this->db->query("SELECT module_data FROM `" . DB_PREFIX . "journal3_module` WHERE module_name = 'Coming Soon Games'");
                    
                    if ($query->num_rows) {
                        $moduleData = json_decode($query->row['module_data'], true);
                    
                        if (isset($moduleData['items'][0]['filter']['products']) && is_array($moduleData['items'][0]['filter']['products'])) {
                            // Remove product ID
                            $moduleData['items'][0]['filter']['products'] = array_values(
                                array_filter(
                                    $moduleData['items'][0]['filter']['products'],
                                    function ($id) use ($product_id) {
                                        return $id != $product_id; // keep everything except this ID
                                    }
                                )
                            );
                        }
                    
                        // Save back into DB
                        $newModuleData = $this->db->escape(json_encode($moduleData, JSON_UNESCAPED_UNICODE));
                        $this->db->query("UPDATE `" . DB_PREFIX . "journal3_module` 
                                          SET module_data = '" . $newModuleData . "' 
                                          WHERE module_name = 'Coming Soon Games'");
                    }
                    // remove product from coming soon games section end code  
                } 
                
                // when quantity is 0 then stock_status_id is On reorder
                
                if (($column_id == 'quantity' && $updated_value == 0) || 
                    ($column_id == 'stock_status_id' && $updated_value == 13)) {                   
                    //  Remove the product from the 'On Reorder' module if it exists
						$query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
						if ($query->num_rows) {
							$moduleData = json_decode($query->row['module_data'], true);
					
							// Check if 'products' exists in the filter and is an array
							if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
								$products = $moduleData['general']['filter']['products'];
					
								// Remove the product_id if it exists
								if (($key = array_search((string)$product_id, $products)) !== false) {
									unset($products[$key]);
									$moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
					
									// Update the module_data JSON in the database
									$updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
									$this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
									
								   
									
								}
							}
						}
                } 
                
		if($column_id =='stock_status_id' ){
		    if (isset($updated_value)) {
			//$this->db->query("UPDATE " . DB_PREFIX . "product SET stock_status_id = '" . (int)$updated_value . "', subtract = '" . (int)$data['subtract'] . "' WHERE product_id = '" . (int)$product_id . "'");
			
			$ss_id = $updated_value;
			
			$query = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
            if ($query->num_rows > 0) {
                $quantity = $query->row['quantity'];
            }
            
			
							if ($ss_id != ''){
					if (in_array($ss_id, [8, 9, 10, 11, 12, 13, 15])) {
                        if($ss_id == 8) {
                            $c_id = 53;
                        } elseif($ss_id == 9) {
                            $c_id = 52;
                        } elseif($ss_id == 10) {
                            $c_id = 54;
                        } elseif($ss_id == 11) {
                            $c_id = 51;
                        } elseif($ss_id == 12) {
                            $c_id = 59;
                        } elseif($ss_id == 13) {
                            $c_id = 53;
                        } elseif($ss_id == 15) {
                            $c_id = 53;
                        }
                    
                        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category 
                            WHERE product_id = '" . (int)$product_id . "' 
                            AND category_id IN (51, 52, 53, 54, 59)");
                    
                        if ($query->num_rows > 0) {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_to_category 
                                SET category_id = '".$c_id."' 
                                WHERE product_id = '" . (int)$product_id . "' 
                                AND category_id IN (51, 52, 53, 54, 59)");
                        } else {
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category 
                                SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$c_id . "'");
                        }
                    } else {
                        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category 
                            WHERE product_id = '" . (int)$product_id . "' 
                            AND category_id IN (51, 52, 53, 54, 59)");
                    }

					
					
					if ($ss_id == 8) {
						$this->db->query("UPDATE " . DB_PREFIX . "product SET subtract = 0 WHERE product_id = '" . (int)$product_id . "'");
					}else{
						$this->db->query("UPDATE " . DB_PREFIX . "product SET subtract = 1 WHERE product_id = '" . (int)$product_id . "'");
					}
				}	
		    
		    }
		    
		    // product label -- preorder - coming soon  start
		    if (isset($updated_value) && $updated_value == 15  && $quantity != 0) {
		        // insert product to coming soon games section  
		       /* $query = $this->db->query("SELECT module_data FROM `" . DB_PREFIX . "journal3_module` WHERE module_name = 'Coming Soon Games'");

                if ($query->num_rows) {
                    $moduleData = json_decode($query->row['module_data'], true);
                
                    if (isset($moduleData['items'][0]['filter']['products']) && is_array($moduleData['items'][0]['filter']['products'])) {
                        // Append new product IDs
                        $newProducts = ["$product_id"];
                        $moduleData['items'][0]['filter']['products'] = array_values(array_unique(array_merge(
                            $moduleData['items'][0]['filter']['products'],
                            $newProducts
                        )));
                    }
                
                    // Save back into DB
                    $newModuleData = $this->db->escape(json_encode($moduleData, JSON_UNESCAPED_UNICODE));
                    $this->db->query("UPDATE `" . DB_PREFIX . "journal3_module` 
                                      SET module_data = '" . $newModuleData . "' 
                                      WHERE module_name = 'Coming Soon Games'");
                } */
                // insert product to coming soon games section  
                    $query = $this->db->query("SELECT module_data FROM `" . DB_PREFIX . "journal3_module` WHERE module_name = 'Coming Soon Games'");
                
                    if ($query->num_rows) {
                        $moduleData = json_decode($query->row['module_data'], true);
                
                        if (isset($moduleData['items'][0]['filter']['products']) && is_array($moduleData['items'][0]['filter']['products'])) {
                            // Append new product ID
                            $newProducts = ["$product_id"];
                            $allProducts = array_values(array_unique(array_merge(
                                $moduleData['items'][0]['filter']['products'],
                                $newProducts
                            )));
                
                            // Sort products by date_modified DESC from oc_product
                            $ids = implode(',', array_map('intval', $allProducts));
                            $sortQuery = $this->db->query("
                                SELECT product_id 
                                FROM `" . DB_PREFIX . "product` 
                                WHERE product_id IN ($ids) 
                                ORDER BY date_modified DESC
                            ");
                
                            $sortedProducts = array_column($sortQuery->rows, 'product_id');
                            $moduleData['items'][0]['filter']['products'] = $sortedProducts;
                        }
                
                        // Save back into DB
                        $newModuleData = $this->db->escape(json_encode($moduleData, JSON_UNESCAPED_UNICODE));
                        $this->db->query("UPDATE `" . DB_PREFIX . "journal3_module` 
                                          SET module_data = '" . $newModuleData . "' 
                                          WHERE module_name = 'Coming Soon Games'");
                    }

             // insert product to coming soon games section  end code 
                    // Step 1: Remove the product from the 'On Reorder' module if it exists
                    $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
                    if ($query->num_rows) {
                        $moduleData = json_decode($query->row['module_data'], true);
                
                        // Check if 'products' exists in the filter and is an array
                        if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                            $products = $moduleData['general']['filter']['products'];
                
                            // Remove the product_id if it exists
                            if (($key = array_search((string)$product_id, $products)) !== false) {
                                unset($products[$key]);
                                $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
                
                                // Update the module_data JSON in the database
                                $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                                $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
                            }
                        }
                    }
                
                    // Step 2: Check if the product exists in the 'Coming Soon' module and add it
                    $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
                    if ($query->num_rows) {
                        $moduleData = json_decode($query->row['module_data'], true);
                
                        // Check if 'products' exists in the filter and is an array
                        if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                            $products = $moduleData['general']['filter']['products'];
                
                            // Append the product_id if it doesn't already exist
                            if (!in_array((string)$product_id, $products)) {
                                $products[] = (string)$product_id;
                                $moduleData['general']['filter']['products'] = $products;
                
                                // Update the module_data JSON in the database
                                $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                                $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                                
                                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id IN (53)");
    			             $c_id = 53; //preorder
            			    if ($query->num_rows > 0) {
            				    $this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET category_id = '".$c_id."' WHERE product_id = '" . (int)$product_id . "' AND category_id IN (53)");
            				}else{
            					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$c_id . "'");
            				}
                            }
                        }
                    }
                    
                   
                } else {
                // Step 3: Remove the product from the 'Coming Soon' module if it exists
                $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
                if ($query->num_rows) {
                    $moduleData = json_decode($query->row['module_data'], true);
            
                    // Check if 'products' exists in the filter and is an array
                    if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                        $products = $moduleData['general']['filter']['products'];
            
                        // Remove the product_id if it exists
                        if (($key = array_search((string)$product_id, $products)) !== false) {
                            unset($products[$key]);
                            $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
            
                            // Update the module_data JSON in the database
                            $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                            $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                        }
                    }
                }
                // remove product from coming soon games section  
                    $query = $this->db->query("SELECT module_data FROM `" . DB_PREFIX . "journal3_module` WHERE module_name = 'Coming Soon Games'");
                    
                    if ($query->num_rows) {
                        $moduleData = json_decode($query->row['module_data'], true);
                    
                        if (isset($moduleData['items'][0]['filter']['products']) && is_array($moduleData['items'][0]['filter']['products'])) {
                            // Remove product ID
                            $moduleData['items'][0]['filter']['products'] = array_values(
                                array_filter(
                                    $moduleData['items'][0]['filter']['products'],
                                    function ($id) use ($product_id) {
                                        return $id != $product_id; // keep everything except this ID
                                    }
                                )
                            );
                        }
                    
                        // Save back into DB
                        $newModuleData = $this->db->escape(json_encode($moduleData, JSON_UNESCAPED_UNICODE));
                        $this->db->query("UPDATE `" . DB_PREFIX . "journal3_module` 
                                          SET module_data = '" . $newModuleData . "' 
                                          WHERE module_name = 'Coming Soon Games'");
                    }
                    // remove product from coming soon games section end code  

            }
            // product label -- preorder - coming soon  End
            
            //  preorder - On Reorder Product label start
            if (isset($updated_value) && $updated_value == 13  && $quantity != 0) {
                // Step 1: Remove the product from the 'Coming Soon' module if it exists
                $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
                if ($query->num_rows) {
                    $moduleData = json_decode($query->row['module_data'], true);
            
                    // Check if 'products' exists in the filter and is an array
                    if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                        $products = $moduleData['general']['filter']['products'];
            
                        // Remove the product_id if it exists
                        if (($key = array_search((string)$product_id, $products)) !== false) {
                            unset($products[$key]);
                            $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
            
                            // Update the module_data JSON in the database
                            $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                            $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                        }
                    }
                }
            
                // Step 2: Check if the product exists in the 'On Reorder' module and add it
                $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
                if ($query->num_rows) {
                    $moduleData = json_decode($query->row['module_data'], true);
            
                    // Check if 'products' exists in the filter and is an array
                    if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                        $products = $moduleData['general']['filter']['products'];
            
                        // Append the product_id if it doesn't already exist
                        if (!in_array((string)$product_id, $products)) {
                            $products[] = (string)$product_id;
                            $moduleData['general']['filter']['products'] = $products;
            
                            // Update the module_data JSON in the database
                            $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                            $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
                            
                            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id IN (53)");
    			             $c_id = 53; //preorder
            			    if ($query->num_rows > 0) {
            				    $this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET category_id = '".$c_id."' WHERE product_id = '" . (int)$product_id . "' AND category_id IN (53)");
            				}else{
            					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$c_id . "'");
            				}
            				
                        }
                        
                        
                    }
                }
            } else {
                // Step 3: Remove the product from the 'On Reorder' module if it exists
                $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
                if ($query->num_rows) {
                    $moduleData = json_decode($query->row['module_data'], true);
            
                    // Check if 'products' exists in the filter and is an array
                    if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                        $products = $moduleData['general']['filter']['products'];
            
                        // Remove the product_id if it exists
                        if (($key = array_search((string)$product_id, $products)) !== false) {
                            unset($products[$key]);
                            $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
            
                            // Update the module_data JSON in the database
                            $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                            $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
                        }
                    }
                }
            }
            //  preorder - On Reorder Product label End
            
		}
		 // update date_modified after all changes
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `date_modified` = NOW() WHERE `product_id` = '" . (int)$product_id . "'");
    
		$this->cache->delete('product');
	}

	public function update_special_price($product_id, $data){
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}
	}

	public function update_images($product_id, $data){
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_image'])) {
			$i = 1;
			foreach ($data['product_image'] as $image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($image) . "', sort_order = '" . (int)$i . "'");
				$i++;
			}
		}
	}

	public function update_product_language($product_id, $data){
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
	}

	public function update_product_category($product_id, $data){
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
	}

	public function update_attribute($product_id, $data, $same_content = false){
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");

		if (!empty($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
			
			if ($same_content == 'true') {
				$admin_language_id = (int)$this->config->get('config_language_id');
				$this->load->model('localisation/language');
				$languages = $this->model_localisation_language->getLanguages();

				foreach ($data['product_attribute'] as $product_attribute) {
					if ($product_attribute['attribute_id']) {
						$query_attribute_text = $this->db->query("SELECT `text` FROM `". DB_PREFIX ."product_attribute` WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$admin_language_id . "' LIMIT 1");
						$attribute_text = $query_attribute_text->row['text'];

						foreach ($languages as $language){
							$language_id = $language['language_id'];
		
							if ($language_id != $admin_language_id){
								$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");
								$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($attribute_text) . "'");
							}
						}
					}
				}
			}
		}

	}

	public function update_option($product_id, $data){
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}
	}

	public function value_statement($operator, $value){
		$operator_type1 = array('IN','NOT IN');
		$operator_type2 = array('LIKE','NOT LIKE');

		if (in_array($operator, $operator_type1)) {
			$statement = '('.$value.')';
		}elseif (in_array($operator, $operator_type2)) {
			$statement = '(\''.$value.'\')';
		}else{
			$statement = '\''.$value.'\' ';
		}	

		return $statement;
	}

	public function addProduct($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$this->config->get('hb_products_qf_minimum') . "', subtract = '" . (int)$this->config->get('hb_products_qf_subtract') . "', stock_status_id = '" . (int)$this->config->get('hb_products_qf_stock_status_id') . "', date_available = now(), manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$this->config->get('hb_products_qf_shipping') . "', price = '" . (float)$data['price'] . "', points = '0', weight = '', weight_class_id = '" . (int)$this->config->get('config_weight_class_id') . "', length = '', width = '', height = '', length_class_id = '" . (int)$this->config->get('config_length_class_id') . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$this->config->get('hb_products_qf_tax_class_id') . "', sort_order = '1', date_added = NOW(), date_modified = NOW()");

		$product_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if ($this->config->get('hb_products_qf_product_store')) {
			foreach ($this->config->get('hb_products_qf_product_store') as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
		
		// Coming Soon product label
        if (isset($data['stock_status_id']) && $data['stock_status_id'] == 15  && $data['quantity'] != 0) {
            // Step 1: Remove the product from the 'On Reorder' module if it exists
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Remove the product_id if it exists
                    if (($key = array_search((string)$product_id, $products)) !== false) {
                        unset($products[$key]);
                        $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
                    }
                }
            }
        
            // Step 2: Check if the product exists in the 'Coming Soon' module and add it
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Append the product_id if it doesn't already exist
                    if (!in_array((string)$product_id, $products)) {
                        $products[] = (string)$product_id;
                        $moduleData['general']['filter']['products'] = $products;
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                    }
                }
            }
        } else {
            // Step 3: Remove the product from the 'Coming Soon' module if it exists
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Remove the product_id if it exists
                    if (($key = array_search((string)$product_id, $products)) !== false) {
                        unset($products[$key]);
                        $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                    }
                }
            }
        }
        
         // On Reorder Product label 
        if (isset($data['stock_status_id']) && $data['stock_status_id'] == 13  && $data['quantity'] != 0) {
            // Step 1: Remove the product from the 'Coming Soon' module if it exists
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Remove the product_id if it exists
                    if (($key = array_search((string)$product_id, $products)) !== false) {
                        unset($products[$key]);
                        $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                    }
                }
            }
        
            // Step 2: Check if the product exists in the 'On Reorder' module and add it
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Append the product_id if it doesn't already exist
                    if (!in_array((string)$product_id, $products)) {
                        $products[] = (string)$product_id;
                        $moduleData['general']['filter']['products'] = $products;
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
                    }
                }
            }
        } else {
            // Step 3: Remove the product from the 'On Reorder' module if it exists
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Remove the product_id if it exists
                    if (($key = array_search((string)$product_id, $products)) !== false) {
                        unset($products[$key]);
                        $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
                    }
                }
            }
        }

		// SEO URL
		if ((version_compare(VERSION,'3.0.0.0','<' ))) {
			if ($data['keyword']) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
			}
		}else{		
			if (isset($data['product_seo_url'])) {
				foreach ($data['product_seo_url']as $store_id => $language) {
					foreach ($language as $language_id => $keyword) {
						if (!empty($keyword)) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
						}
					}
				}
			}
		}

		$this->cache->delete('product');

		return $product_id;
	}

	public function editProduct($product_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', price = '" . (float)$data['price'] . "', status = '" . (int)$data['status'] . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
		
		// Coming Soon product label
        if (isset($data['stock_status_id']) && $data['stock_status_id'] == 15  && $data['quantity'] != 0) {
            // Step 1: Remove the product from the 'On Reorder' module if it exists
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Remove the product_id if it exists
                    if (($key = array_search((string)$product_id, $products)) !== false) {
                        unset($products[$key]);
                        $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
                    }
                }
            }
        
            // Step 2: Check if the product exists in the 'Coming Soon' module and add it
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Append the product_id if it doesn't already exist
                    if (!in_array((string)$product_id, $products)) {
                        $products[] = (string)$product_id;
                        $moduleData['general']['filter']['products'] = $products;
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                    }
                }
            }
        } else {
            // Step 3: Remove the product from the 'Coming Soon' module if it exists
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Remove the product_id if it exists
                    if (($key = array_search((string)$product_id, $products)) !== false) {
                        unset($products[$key]);
                        $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                    }
                }
            }
        }
        
         // On Reorder Product label 
        if (isset($data['stock_status_id']) && $data['stock_status_id'] == 13  && $data['quantity'] != 0) {
            // Step 1: Remove the product from the 'Coming Soon' module if it exists
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'Coming Soon'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Remove the product_id if it exists
                    if (($key = array_search((string)$product_id, $products)) !== false) {
                        unset($products[$key]);
                        $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'Coming Soon'");
                    }
                }
            }
        
            // Step 2: Check if the product exists in the 'On Reorder' module and add it
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Append the product_id if it doesn't already exist
                    if (!in_array((string)$product_id, $products)) {
                        $products[] = (string)$product_id;
                        $moduleData['general']['filter']['products'] = $products;
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
                    }
                }
            }
        } else {
            // Step 3: Remove the product from the 'On Reorder' module if it exists
            $query = $this->db->query("SELECT `module_data` FROM `oc_journal3_module` WHERE `module_name` = 'On Reorder'");
            if ($query->num_rows) {
                $moduleData = json_decode($query->row['module_data'], true);
        
                // Check if 'products' exists in the filter and is an array
                if (isset($moduleData['general']['filter']['products']) && is_array($moduleData['general']['filter']['products'])) {
                    $products = $moduleData['general']['filter']['products'];
        
                    // Remove the product_id if it exists
                    if (($key = array_search((string)$product_id, $products)) !== false) {
                        unset($products[$key]);
                        $moduleData['general']['filter']['products'] = array_values($products); // Reindex the array
        
                        // Update the module_data JSON in the database
                        $updatedModuleData = json_encode($moduleData, JSON_UNESCAPED_SLASHES);
                        $this->db->query("UPDATE `oc_journal3_module` SET `module_data` = '" . $this->db->escape($updatedModuleData) . "' WHERE `module_name` = 'On Reorder'");
                    }
                }
            }
        }

		// SEO URL
		if ((version_compare(VERSION,'3.0.0.0','<' ))) {

			if ($data['keyword']) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
			}
		}else{			
			if (isset($data['product_seo_url'])) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
				foreach ($data['product_seo_url']as $store_id => $language) {
					foreach ($language as $language_id => $keyword) {
						if (!empty($keyword)) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
						}
					}
				}
			}
		}

		$this->cache->delete('product');
	}

	public function getClipboardItems($data){
		$sql = "SELECT * FROM `".DB_PREFIX."hbp_clipboard` a LEFT JOIN `".DB_PREFIX."product_description` b ON a.product_id = b.product_id WHERE b.language_id = '". (int)$this->config->get('config_language_id') ."'";
		if (!empty($data['search'])) {
			$sql .= " AND b.name LIKE '%".$this->db->escape($data['search'])."%'";
		}
		$sql .= " ORDER BY b.name";
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
	
	public function getTotalClipboardItems($data){
		$sql = "SELECT count(*) as total FROM `".DB_PREFIX."hbp_clipboard` a LEFT JOIN `".DB_PREFIX."product_description` b ON a.product_id = b.product_id WHERE b.language_id = '". (int)$this->config->get('config_language_id') ."'";
		if (!empty($data['search'])) {
			$sql .= " AND b.name LIKE '%".$this->db->escape($data['search'])."%'";
		}
		$results = $this->db->query($sql);
		return $results->row['total'];
	}

	public function add_to_clipboard($product_id){
		$this->db->query("DELETE FROM " . DB_PREFIX . "hbp_clipboard WHERE product_id = '" . (int)$product_id . "' ");
		$this->db->query("INSERT INTO " . DB_PREFIX . "hbp_clipboard (product_id) VALUES ('" . (int)$product_id . "')");
	}

	public function remove_from_clipboard($product_id){
		$this->db->query("DELETE FROM " . DB_PREFIX . "hbp_clipboard WHERE product_id = '" . (int)$product_id . "' ");
	}

	public function clear_clipboard(){
		$this->db->query("TRUNCATE " . DB_PREFIX . "hbp_clipboard");
	}

	public function get_all_clipboard_products(){
		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "hbp_clipboard");
		if ($query->rows){
			return $query->rows;
		}else{
			return false;
		}
	}

	public function add_to_store($store_id){
		if ($this->get_all_clipboard_products()) {
			$products = $this->get_all_clipboard_products();
			foreach ($products as $product){
				$product_id = $product['product_id'];
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "' AND store_id = '". (int)$store_id."'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
	}

	public function remove_from_store($store_id){
		if ($this->get_all_clipboard_products()) {
			$products = $this->get_all_clipboard_products();
			foreach ($products as $product){
				$product_id = $product['product_id'];
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "' AND store_id = '". (int)$store_id."'");
			}
		}
	}

	public function remove_category($category_id){
		if ($this->get_all_clipboard_products()) {
			$products = $this->get_all_clipboard_products();
			foreach ($products as $product){
				$product_id = $product['product_id'];
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '".(int)$category_id."'");
			}
		}
	}

	public function add_category($category_id){
		if ($this->get_all_clipboard_products()) {
			$products = $this->get_all_clipboard_products();
			foreach ($products as $product){
				$product_id = $product['product_id'];
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '".(int)$category_id."'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (product_id, category_id) VALUES ('" . (int)$product_id . "', '" . (int)$category_id . "')");
			}
		}
	}

	public function remove_filter($filter_id){
		if ($this->get_all_clipboard_products()) {
			$products = $this->get_all_clipboard_products();
			foreach ($products as $product){
				$product_id = $product['product_id'];
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "' AND filter_id = '".(int)$filter_id."'");
			}
		}
	}

	public function add_filter($filter_id){
		if ($this->get_all_clipboard_products()) {
			$products = $this->get_all_clipboard_products();
			foreach ($products as $product){
				$product_id = $product['product_id'];
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "' AND filter_id = '".(int)$filter_id."'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter (product_id, filter_id) VALUES ('" . (int)$product_id . "', '" . (int)$filter_id . "')");
			}
		}
	}

	public function remove_related($related_id){
		if ($this->get_all_clipboard_products()) {
			$products = $this->get_all_clipboard_products();
			foreach ($products as $product){
				$product_id = $product['product_id'];
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '".(int)$related_id."'");
			}
		}
	}

	public function add_related($related_id){
		if ($this->get_all_clipboard_products()) {
			$products = $this->get_all_clipboard_products();
			foreach ($products as $product){
				$product_id = $product['product_id'];
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '".(int)$related_id."'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related (product_id, related_id) VALUES ('" . (int)$product_id . "', '" . (int)$related_id . "')");
			}
		}
	}

	public function update_manufacturer($manufacturer_id){
		if ($this->get_all_clipboard_products()) {
			$products = $this->get_all_clipboard_products();
			foreach ($products as $product){
				$product_id = $product['product_id'];
				$this->db->query("UPDATE " . DB_PREFIX . "product SET manufacturer_id = '".(int)$manufacturer_id."' WHERE product_id = '" . (int)$product_id . "'");
			}
		}
	}

	public function process_csv_upload($tablename, $file){
		$row = 0;
		$fields = array();
		
		$actual_columns = $this->getColumns($tablename);
			
		switch ($tablename) {
			case 'product':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=1; $i < $field_count; $i++) { $fields[] = $column[$i]; }
						$table_fields = implode(',',$fields);
					}
					$row++;
					
					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}
					
					if($row == 1) continue;
					$values = array();
		
					for ($j=1; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_id = $column[1];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_id = '".(int)$product_id."'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";
					$date_update_sql = "UPDATE ".DB_PREFIX.$tablename." SET date_modified = now() WHERE product_id = '".(int)$product_id."'";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
					$this->db->query($date_update_sql);
				}
				break;
			
			case 'product_description':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=0; $i < $field_count; $i++) { 
							$column_name = preg_replace( '/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $column[$i]);
							$fields[] = $column_name; 
						}
						$table_fields = implode(',',$fields);
					}
					$row++;

					$this->log->write($fields);
					$this->log->write($actual_columns);

					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}
					
					if($row == 1) continue;
					$values = array();
		
					for ($j=0; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_id = $column[0];
					$language_id = $column[1];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_id = '".(int)$product_id."' AND language_id = '".(int)$language_id."'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;

			case 'product_attribute':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=1; $i < $field_count; $i++) { $fields[] = $column[$i]; }
						$table_fields = implode(',',$fields);
					}
					$row++;

					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}
					
					if($row == 1) continue;
					$values = array();
		
					for ($j=1; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_id = $column[1];
					$attribute_id = $column[2];
					$language_id = $column[3];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_id = '".(int)$product_id."' AND language_id = '".(int)$language_id."' AND attribute_id = '" .(int)$attribute_id. "'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;	

			case 'product_discount':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=1; $i < $field_count; $i++) { $fields[] = $column[$i]; }
						$table_fields = implode(',',$fields);
					}
					$row++;

					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}
					
					if($row == 1) continue;
					$values = array();
		
					for ($j=1; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_discount_id = $column[1];
					$product_id = $column[2];
					$customer_group_id = $column[3];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_discount_id = '".(int)$product_discount_id."' AND product_id = '".(int)$product_id."' AND customer_group_id = '" .(int)$customer_group_id. "'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;

			case 'product_special':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=1; $i < $field_count; $i++) { $fields[] = $column[$i]; }
						$table_fields = implode(',',$fields);
					}
					$row++;
					
					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}

					if($row == 1) continue;
					$values = array();
		
					for ($j=1; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_special_id = $column[1];
					$product_id = $column[2];
					$customer_group_id = $column[3];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_special_id = '".(int)$product_special_id."' AND product_id = '".(int)$product_id."' AND customer_group_id = '" .(int)$customer_group_id. "'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;

			case 'product_image':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=1; $i < $field_count; $i++) { $fields[] = $column[$i]; }
						$table_fields = implode(',',$fields);
					}
					$row++;
					
					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}

					if($row == 1) continue;
					$values = array();
		
					for ($j=1; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_image_id = $column[1];
					$product_id = $column[2];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_id = '".(int)$product_id."'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;
			
			case 'product_option':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=1; $i < $field_count; $i++) { $fields[] = $column[$i]; }
						$table_fields = implode(',',$fields);
					}
					$row++;

					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}
					
					if($row == 1) continue;
					$values = array();
		
					for ($j=1; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_option_id = $column[1];
					$product_id = $column[2];
					$option_id = $column[3];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_option_id = '".(int)$product_option_id."' AND product_id = '".(int)$product_id."' AND option_id = '" .(int)$option_id. "'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;
			
			case 'product_option_value':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=1; $i < $field_count; $i++) { $fields[] = $column[$i]; }
						$table_fields = implode(',',$fields);
					}
					$row++;

					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}
					
					if($row == 1) continue;
					$values = array();
		
					for ($j=1; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_option_value_id = $column[1];
					$product_option_id = $column[2];
					$product_id = $column[3];
					$option_id = $column[4];
					$option_value_id = $column[4];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_option_value_id = '".(int)$product_option_value_id."' AND product_option_id = '".(int)$product_option_id."' AND product_id = '".(int)$product_id."' AND option_id = '" .(int)$option_id. "' AND option_value_id = '" .(int)$option_value_id. "'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;

			case 'product_reward':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=1; $i < $field_count; $i++) { $fields[] = $column[$i]; }
						$table_fields = implode(',',$fields);
					}
					$row++;

					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}
					
					if($row == 1) continue;
					$values = array();
		
					for ($j=1; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_reward_id = $column[1];
					$product_id = $column[2];
					$customer_group_id = $column[3];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_reward_id = '".(int)$product_reward_id."' AND product_id = '".(int)$product_id."' AND customer_group_id = '" .(int)$customer_group_id. "'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;

			default:
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=1; $i < $field_count; $i++) { $fields[] = $column[$i]; }
						$table_fields = implode(',',$fields);
					}
					$row++;
					
					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}

					if($row == 1) continue;
					$values = array();
		
					for ($j=1; $j < $field_count; $j++) { $values[] ="'".$this->db->escape($column[$j])."'";}
					$table_values = implode(',',$values);

					$product_id = $column[1];
					$delete_sql = "DELETE FROM ".DB_PREFIX.$tablename." WHERE product_id = '".(int)$product_id."'";
					$insert_sql = "INSERT INTO ".DB_PREFIX.$tablename." (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;
		}

		$process_upload['success'] = 'CSV File Uploaded and tables updated';

		return $process_upload;
					
	}
}
?>