<?php
class ModelExtensionEventsEvents extends Model {
    
    public function createTables() {
        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "extendons_events ("
                . "events_id INT(11) AUTO_INCREMENT, "
                . "name VARCHAR(111), "
                . "venue VARCHAR(111), "
                . "image text, "
                . "url_suffix VARCHAR(111), "
                . "video text, "
                . "start_date DATETIME, "
                . "end_date DATETIME, "
                . "status INT(1), "
                . "contact_name varchar(111) DEFAULT NULL, "
                . "contact_phone varchar(111) DEFAULT NULL, "
                . "contact_fax varchar(111) DEFAULT NULL, "
                . "contact_email varchar(111) DEFAULT NULL, "
                . "contact_add text, "
                . "date_created DATETIME, "
                . "date_modified DATETIME, "
                . "PRIMARY KEY (events_id))
            ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;"
        );

        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "extendons_events_description (
                  `events_id` int(11) NOT NULL,
                  `language_id` int(11) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `description` text NOT NULL,
                  `meta_description` varchar(255) NOT NULL,
                  `meta_keyword` varchar(255) NOT NULL,
                  `venue` varchar(255) DEFAULT NULL,
                  `latitude` varchar(255) NULL,
                  `longitude` varchar(255) NULL,
                  PRIMARY KEY (`events_id`,`language_id`),
                  KEY `name` (`name`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
        );

        $this->db->query("CREATE TABLE " . DB_PREFIX . "extendons_events_image (
                `events_image_id` int(11) NOT NULL AUTO_INCREMENT,
                `events_id` int(11) NOT NULL,
                `image` varchar(255) DEFAULT NULL,
                `sort_order` int(3) NOT NULL DEFAULT '0',
                PRIMARY KEY (`events_image_id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;"
        );

        $this->db->query("CREATE TABLE " . DB_PREFIX . "extendons_events_to_layout (
                `events_id` int(11) NOT NULL,
                `store_id` int(11) NOT NULL,
                `layout_id` int(11) NOT NULL,
                PRIMARY KEY (`events_id`,`store_id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
        );

        $this->db->query("CREATE TABLE " . DB_PREFIX . "extendons_events_to_store (
                `events_id` int(11) NOT NULL DEFAULT '0',
                `store_id` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`events_id`,`store_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;"
        );

        $this->db->query("CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "extendons_events_product (
                events_id INT(11), 
                product_id INT(11), 
                PRIMARY KEY (events_id, product_id))"
        );
    }

    public function deleteTables() {
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "extendons_events;");
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "extendons_events_product;");
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "extendons_events_description;");
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "extendons_events_image;");
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "extendons_events_to_layout;");
        $this->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "extendons_events_to_store;");
    }

 public function addEvent($data) {
       
      $event_data = $data['events_description'][1];
        $event_name = $event_data['name'];
        $venue = $event_data['venue'];
        $start_date = $data['start_date'];
        $new_start_date = date('Y-m-d H:i:s', strtotime($start_date . ' +30 minutes'));
        
        $data['start_date'] = $new_start_date;
        
        // Escape values before inserting into SQL query
        $event_name = $this->db->escape($event_name);
        $venue = $this->db->escape($venue);
        $new_start_date = $this->db->escape($new_start_date);
        
        $sql = "INSERT INTO " . DB_PREFIX . "extendons_events SET";        
        
        $sql .= " `name` = '{$event_name}', 
                  `venue` = '{$venue}', 
                  `start_date` = '{$new_start_date}', 
                  `end_date` = '{$data['end_date']}',
                  `image` = '{$data['image']}',
                  `status` = '{$data['status']}',
                  `url_suffix` = '{$data['keyword']}',
                  `video` = '{$data['video']}',
                  `maxregister` = '{$data['maxregister']}',
                  `cost` = '{$data['cost_hidden']}'";
        
        if (isset($data['contact_info'])) {
            foreach ($data['contact_info'] as $k => $v) {
                // Escape contact info values
                $v = $this->db->escape($v);
                $sql .= ", $k = '" . $v . "'";
            }
        }
        
        /* foreach ($data['events_layout'] as $k => $v) {
          $sql .= "`events_layout` = {$v['layout_id']}";
          } */
          
        $this->db->query($sql);

        $last_event_id = $this->db->getLastId(); //echo $last_event_id;exit;
       // print_r("add_event".$last_event_id."<br>");
          $data['event_id'] =  $last_event_id;
        $this->addEventProductDetails($data);
        
        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "extendons_events SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE events_id = '" . (int) $last_event_id . "'");
        }

        foreach ($data['events_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_description SET events_id = '" . (int) $last_event_id . "', language_id = '" . (int) $language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', venue = '" . $this->db->escape($value['venue']) . "', latitude = '" . $this->db->escape($value['latitude']) . "', longitude = '" . $this->db->escape($value['longitude']) . "'");
        }

        if (isset($data['events_store'])) {
            foreach ($data['events_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_to_store SET events_id = '" . (int) $last_event_id . "', store_id = '" . (int) $store_id . "'");
            }
        }

        // Set which layout to use with this category
        if (isset($data['events_layout'])) {
            foreach ($data['events_layout'] as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_to_layout SET events_id = '" . (int) $last_event_id . "', store_id = '" . (int) $store_id . "', layout_id = '" . (int) $layout['layout_id'] . "'");
                }
            }
        }

        if ($data['keyword']) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int) $store_id . "', language_id = '" . (int) $this->config->get('config_language_id') . "', query = 'events_id=" . (int) $last_event_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        // Set which product to use with this event
        if (isset($data['product_related'])) {
          /*  echo '<pre>';
            print_r($data['product_related']);
            echo '</pre>';*/
           // foreach ($data['product_related'] as $product_id) {
                foreach ($data['product_related'] as $index => $product_id) {
                //echo $product_id;
                //$this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events_product WHERE product_id = '" . (int) $product_id . "' AND events_id = '" . (int) $last_event_id . "'");
                
                
				$product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

				foreach ($product_attribute_group_query->rows as $product_attribute_group) {
					$product_attribute_data = array();

					$product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

					foreach ($product_attribute_query->rows as $product_attribute) {
						$product_attribute_data[] = array(
							'attribute_id' => $product_attribute['attribute_id'],
							'name'         => $product_attribute['name'],
							'text'         => $product_attribute['text']
						);
					}

					
				}

				$attribute_info = $product_attribute_data;
				
				//print_r( $attribute_info);
				   
				$attr_desc = $attribute_info[0]['text'];
				
				$attr_words_split = explode(' ', $attr_desc);
				
				$getmaxplayer = $attr_words_split[2];
				
				if($getmaxplayer<=0 or $getmaxplayer==''){
					$getmaxplayer = 2;
				}
				
				//echo "getmaxplayer: $getmaxplayer<br>";
				
				//$registered_player_count = $this->db->query("SELECT COUNT('max_player') FROM " . DB_PREFIX . "extendons_events_product");
				
				$this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_product SET product_id = '" . (int) $product_id . "', events_id = '" . (int) $last_event_id . "', max_player = '" . (int) $getmaxplayer . "'");

            }
           
		}
		
		if (isset($data['product_attributes'])) {
            foreach ($data['product_attributes'] as $product_attribute) {
                $this->db->query("
                    INSERT INTO " . DB_PREFIX . "event_product_details 
                    SET 
                        events_id = '" . (int) $last_event_id . "',
                        events_name = '" . $this->db->escape($product_attribute['events_name']) . "',
                        product_id = '" . (int) $product_attribute['product_id'] . "',
                        name = '" . $this->db->escape($product_attribute['name']) . "', 
                        registrations = '" . (int) $product_attribute['registrations'] . "',
                        length = '" . $this->db->escape($product_attribute['length']) . "',
                        game_type = '" . $this->db->escape($product_attribute['game_type']) . "',
                        difficulty = '" . $this->db->escape($product_attribute['difficulty']) . "',
                        start_time = '" . $this->db->escape($product_attribute['start_time']) . "',
                        taught_by = '" . $this->db->escape($product_attribute['taught_by']) . "',
                        video = '" . $this->db->escape($product_attribute['video']) . "'
                ");		
            }
            $this->db->query("
                DELETE FROM " . DB_PREFIX . "event_product_details 
                WHERE 
                    events_id = '" . (int) $last_event_id . "' 
                    AND 
                    name = ''
            ");
        }

		  
        if (isset($data['event_image'])) {
            foreach ($data['event_image'] as $event_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_image SET events_id = '" . (int) $last_event_id . "', image = '" . $this->db->escape(html_entity_decode($event_image['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int) $event_image['sort_order'] . "'");
            }
        }
        
    }

       public function copyEvents($events_id) {
       
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "extendons_events WHERE events_id = '" . (int) $events_id . "'");
        //echo "<pre>";  print_r($query); die;
        if ($query->num_rows) {
            $data = $query->row;
            $data['events_description'] = $this->getEventsDescriptions($events_id);
            $data['events_store'] = $this->getEventsStores($events_id);
			$data['events_layout'] = $this->getEventsLayouts($events_id);
			$product_related_data = $this->getcopyProductRelated($events_id);
            $data['product_related'] = $product_related_data;
            // echo"<pre>";print_r($product_related_data);
		    $data['event_image'] = $this->getEventImages($events_id);
		    $data['cost_hidden'] = $data['cost'] ;
            $data['status'] = 0;
            $data['product_attributes'] = $this->getExistingData($events_id);
        	
           
			$data['contact_info'] = array(
									'contact_name'=> $data['contact_name'],
									'contact_phone'=> $data['contact_phone'],
									'contact_fax'=> $data['contact_fax'],
									'contact_email'=> $data['contact_email'],
									'contact_add'=> $data['contact_add']
									);
			unset($data['contact_name']);
			unset($data['contact_phone']);
			unset($data['contact_fax']);
			unset($data['contact_email']);
			unset($data['contact_add']);
			unset($data['events_id']);
		
           	$this->addEvent($data);
         
        }
    }
    
    public function editEvent($events_id, $data) {
       
        $event_data = $data['events_description'][1];
        $event_name = $event_data['name'];
        $venue = $event_data['venue'];
         
    // Append data to existing arrays
	foreach ($data['name'] as $name) {
		$data['product_name'][] = $name;
	}
	foreach ($data['maxRegisterValue'] as $registrations) {
		$data['registrations'][] = $registrations;
	}
	foreach ($data['Length'] as $Length) {
		$data['length'][] = $Length;
	}
	foreach ($data['GameType'] as $game_type) {
		$data['game_type'][] = $game_type;
	}
	foreach ($data['Difficulty'] as $difficulty) {
		$data['difficulty'][] = $difficulty;
	}
	foreach ($data['StartTime'] as $start_time) {
		$data['start_time'][] = $start_time;
	}
	foreach ($data['TaughtBy'] as $taught_by) {
		$data['taught_by'][] = $taught_by;
	}
	foreach ($data['Product_Video'] as $Product_Video) {
		$data['Product_Video'][] = $Product_Video;
	}
		foreach ($data['cost_hidden'] as $cost) {
    		$data['cost_hidden'][] = $cost;
    	}
    	
        $start_date = $data['start_date'];
        $new_start_date = date('Y-m-d H:i:s', strtotime($start_date . ' +30 minutes'));
        $data['start_date'] = $new_start_date;
        
        $sql = "UPDATE " . DB_PREFIX . "extendons_events
            SET name = '" . $this->db->escape($event_name) . "', 
            venue = '" . $this->db->escape($venue) . "', 
            start_date = '" . $this->db->escape($start_date) . "',
            end_date = '" . $this->db->escape($data['end_date']) . "',
            status = '" . (int) $data['status'] . "',
            video = '" . $this->db->escape($data['video']) . "',
            url_suffix = '" . $this->db->escape($data['keyword']) . "',
            `maxregister` = '" . (int) $data['maxregister'] . "',
            `cost` = '" . (int) $data['cost_hidden'] . "'";

       


        if (isset($data['contact_info'])) {
            foreach ($data['contact_info'] as $k => $v) {
                $sql .= ", $k = '" . $v . "'";
            }
        }

        $sql .= ", date_modified = NOW() WHERE events_id = '" . (int) $events_id . "'";

        $this->db->query($sql);
        
         $this->updateEventData($events_id,$data);
         
        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "extendons_events SET image = '" . $this->db->escape(html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')) . "' WHERE events_id = '" . (int) $events_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events_description WHERE events_id = '" . (int) $events_id . "'");

        foreach ($data['events_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_description SET events_id = '" . (int) $events_id . "', language_id = '" . (int) $language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', venue = '" . $this->db->escape($value['venue']) . "', latitude = '" . $this->db->escape($value['latitude']) . "', longitude = '" . $this->db->escape($value['longitude']) . "'");
        }


        $this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events_to_store WHERE events_id = '" . (int) $events_id . "'");

        if (isset($data['events_store'])) {
            foreach ($data['events_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_to_store SET events_id = '" . (int) $events_id . "', store_id = '" . (int) $store_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events_to_layout WHERE events_id = '" . (int) $events_id . "'");

        if (isset($data['events_layout'])) {
            foreach ($data['events_layout'] as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_to_layout SET events_id = '" . (int) $events_id . "', store_id = '" . (int) $store_id . "', layout_id = '" . (int) $layout['layout_id'] . "'");
                }
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'events_id=" . (int) $events_id . "'");

        if ($data['keyword']) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int) $store_id . "', language_id = '" . (int) $this->config->get('config_language_id') . "', query = 'events_id=" . (int) $events_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

       if (isset($data['product_related'])) {
           $this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events_product WHERE events_id = '" . (int) $events_id . "'");
            foreach ($data['product_related'] as $product_id) {
                $product_attribute_data = array(); // Initialize the array here
        
                // Fetch product attributes for the current product
                $product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");
        
                foreach ($product_attribute_group_query->rows as $product_attribute_group) {
                    // Fetching attributes for each attribute group
                    $product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");
        
                    foreach ($product_attribute_query->rows as $product_attribute) {
                        $product_attribute_data[] = array(
                            'attribute_id' => $product_attribute['attribute_id'],
                            'name'         => $product_attribute['name'],
                            'text'         => $product_attribute['text']
                        );
                    }
                }
        
                // Assuming $attribute_info should hold all attributes of the product
                $attribute_info = $product_attribute_data;
        
                // Extracting max player from attributes
                $attr_desc = $attribute_info[0]['text'];
                $attr_words_split = explode(' ', $attr_desc);
                $getmaxplayer = isset($attr_words_split[2]) ? $attr_words_split[2] : 2; // Assuming default is 2 if not found or invalid
                // Insert into extendons_events_product table
                $this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_product SET product_id = '" . (int) $product_id . "', events_id = '" . (int) $events_id . "', max_player = '" . (int) $getmaxplayer . "'");
            }
        }


        $this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events_image WHERE events_id = '" . (int) $events_id . "'");

        if (isset($data['event_image'])) {
            foreach ($data['event_image'] as $event_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "extendons_events_image SET events_id = '" . (int) $events_id . "', image = '" . $this->db->escape(html_entity_decode($event_image['image'], ENT_QUOTES, 'UTF-8')) . "', sort_order = '" . (int) $event_image['sort_order'] . "'");
            }
        }

        $this->cache->delete('events');
    }
    
    public function getEventMaxPlayer($events_id) {
        
        $product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

		foreach ($product_attribute_group_query->rows as $product_attribute_group) {
			$product_attribute_data = array();

			$product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

			foreach ($product_attribute_query->rows as $product_attribute) {
				$product_attribute_data[] = array(
					'attribute_id' => $product_attribute['attribute_id'],
					'name'         => $product_attribute['name'],
					'text'         => $product_attribute['text']
				);
			}

			
		}

		$attribute_info = $product_attribute_data;
        
        //echo "loopover <br>";
           
        $attr_desc = $attribute_info[0]['text'];
        
        $attr_words_split = explode(' ', $attr_desc);
        
        $getmaxplayer = $attr_words_split[2]; 
    }

    public function deleteEvent($events_id) {
        /* $this->db->query("DELETE FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int) $category_id . "'");

          $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_path WHERE path_id = '" . (int) $category_id . "'");

          foreach ($query->rows as $result) {
          $this->deleteCategory($result['category_id']);
          } */

        $this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events WHERE events_id = '" . (int) $events_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events_description WHERE events_id = '" . (int) $events_id . "'");
        //this->db->query("DELETE FROM " . DB_PREFIX . "category_filter WHERE events_id = '" . (int) $events_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events_to_store WHERE events_id = '" . (int) $events_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "extendons_events_to_layout WHERE events_id = '" . (int) $events_id . "'");
        //$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE events_id = '" . (int) $events_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'events_id=" . (int) $events_id . "'");
           $this->db->query("DELETE FROM " . DB_PREFIX . "event_product_details WHERE events_id = '" . (int) $events_id . "'");
         

        $this->cache->delete('events');
    }

    public function getEvents($data) {

		$sql = "SELECT DISTINCT * FROM " . DB_PREFIX . "extendons_events e 
				LEFT JOIN " . DB_PREFIX . "extendons_events_description ed1 ON (e.events_id = ed1.events_id) 
				WHERE ed1.language_id = '" . (int) $this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND e." . $data['filter_name'] . " LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['start_date']) && !empty($data['end_date'])) {
			$sql .= " AND e.start_date >= '" . $this->db->escape($data['start_date']) . "' 
					  AND e.end_date <= '" . $this->db->escape($data['end_date']) . "'";
		}

		$sql .= " GROUP BY e.events_id ORDER BY e.start_date";

		if (isset($data['start']) && isset($data['limit'])) {
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

    public function getTotalEvents($filter_data = array()) {

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "extendons_events e ";

		$sql .= "LEFT JOIN " . DB_PREFIX . "extendons_events_description ed1 ON (e.events_id = ed1.events_id) ";

		$sql .= "WHERE ed1.language_id = '" . (int) $this->config->get('config_language_id') . "'";



		// Apply filters

		if (!empty($filter_data['start_date']) && !empty($filter_data['end_date'])) {

			$sql .= " AND e.start_date >= '" . $this->db->escape($filter_data['start_date']) . "' AND e.end_date <= '" . $this->db->escape($filter_data['end_date']) . "'";

		}



		if (!empty($filter_data['filter_name'])) {

			$sql .= " AND e." . $this->db->escape($filter_data['filter_name']) . " LIKE '" . $this->db->escape($filter_data['filter_name']) . "%'";

		}



		$query = $this->db->query($sql);

		return $query->row['total'];

	}

    public function getEvent($events_id) {
        //$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "extendons_events e  WHERE e.events_id = '" . (int) $event_id ."'");
        
        // echo "SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = 'events_id=" . (int) $events_id . "') AS keyword FROM " . DB_PREFIX . "extendons_events e LEFT JOIN " . DB_PREFIX . "extendons_events_description ed2 ON (e.events_id = ed2.events_id) WHERE e.events_id = '" . (int) $events_id . "' AND ed2.language_id = '" . (int) $this->config->get('config_language_id') . "'";

        $query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE `query` = 'events_id=" . (int) $events_id . "') AS keyword FROM " . DB_PREFIX . "extendons_events e LEFT JOIN " . DB_PREFIX . "extendons_events_description ed2 ON (e.events_id = ed2.events_id) WHERE e.events_id = '" . (int) $events_id . "' AND ed2.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getEventsDescriptions($events_id) {
        $events_description_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extendons_events_description WHERE events_id = '" . (int) $events_id . "'");

        foreach ($query->rows as $result) {
            $events_description_data[$result['language_id']] = array(
                'name' => $result['name'],
                'meta_keyword' => $result['meta_keyword'],
                'meta_description' => $result['meta_description'],
                'description' => $result['description'],
                'venue' => $result['venue'],
                'latitude' => $result['latitude'],
                'longitude' => $result['longitude']
            );
        }

        return $events_description_data;
    }

    public function getEventsFilters($events_id) {
        $events_filter_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "events_filter WHERE category_id = '" . (int) $events_id . "'");

        foreach ($query->rows as $result) {
            $events_filter_data[] = $result['filter_id'];
        }

        return $events_filter_data;
    }

    public function getEventsStores($events_id) {
        $events_store_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extendons_events_to_store WHERE events_id = '" . (int) $events_id . "'");

        foreach ($query->rows as $result) {
            $events_store_data[] = $result['store_id'];
        }

        return $events_store_data;
    }

    public function getEventsLayouts($events_id) {
        $events_layout_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extendons_events_to_layout WHERE events_id = '" . (int) $events_id . "'");

        foreach ($query->rows as $result) {
            $events_layout_data[$result['store_id']] = $result['layout_id'];
        }

        return $events_layout_data;
    }

    public function getProductRelated($events_id) {
        $product_related_data = array();

       $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "extendons_events_product
            WHERE events_id = '" . (int)$events_id . "'");


        foreach ($query->rows as $result) {
            $product_related_data[] = $result['product_id'];
        }

        return $product_related_data;
    }
    
    public function getcopyProductRelated($events_id) {
        $product_related_data = array();

       $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "extendons_events_product
            WHERE events_id = '" . (int)$events_id . "' AND product_id = '6394'
        ");


        foreach ($query->rows as $result) {
            $product_related_data[] = $result['product_id'];
        }

        return $product_related_data;
    }

    public function getEventImages($events_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extendons_events_image WHERE events_id = '" . (int) $events_id . "'");

        return $query->rows;
    }

    /**
     * check duplicate of the input in table
     * @param mix $input check duplicate value
     * @param string $table check for the table
     * @param string $column check for duplicate against the column
     * @param int $primary_id edit mode check
     * @param int $id current record id
     * @return bool $is_duplicate true if duplicate else false
     * */
    public function checkDuplicate($input, $table, $column, $primary_id = null, $id = null) {

        $is_duplicate = false;
        $sql = "SELECT * from " . DB_PREFIX . "" . $table . "
        WHERE " . $column . " = '" . $input . "'";

        if ($id != null) {
            $sql .= " AND " . $primary_id . " !=" . $id . "";
        }

        $query = $this->db->query($sql);

        if ($query->num_rows > 0) {
            //duplicate confirmed return true

            $is_duplicate = true;
        }

        return $is_duplicate;
    }

    /**
     * check if the module exists in the extension table
     * @param string $module_code locate code in table if exists
     * @return bool $return status true if code exists
     * */
    public function isModuleInstalled($module_code) {

        $flag = false;
        $result = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `code` = '" . $module_code . "'");
        if ($result->num_rows) {
            $flag = true;
        }

        return $flag;
    }

    public function copyProductEvent($event_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int) $product_id . "' AND pd.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        if ($query->num_rows) {
            $data = array();

            $data = $query->row;

            $data['sku'] = '';
            $data['upc'] = '';
            $data['viewed'] = '0';
            $data['keyword'] = '';
            $data['status'] = '0';


            $data = array_merge($data, array('product_description' => $this->getProductDescriptions($product_id)));


            $data = array_merge($data, array('product_image' => $this->getProductImages($product_id)));
            $data = array_merge($data, array('product_option' => $this->getProductOptions($product_id)));
            $data = array_merge($data, array('product_related' => $this->getProductRelated($product_id)));
            

            $data = array_merge($data, array('product_layout' => $this->getProductLayouts($product_id)));


            $this->addProduct($data);
        }
    }
    
public function getProductInfo($product_id) {
    if ($product_id == 0) {
        return array(); // Return an empty array or handle this case as needed
    }

    $query = $this->db->query("SELECT name, product_id FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

    return $query->row;
}

public function getProductAttribute($product_id, $attribute_id) {
    $query = $this->db->query("SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$attribute_id . "'");

    return $query->row['text'];
}

public function addEventProductDetails($data) {
    // Check if there's an event to process
    if ($data['event_id'] === 0) {
        return;
    }

    // Get the event ID
    $event_id = $data['event_id'];

    // Loop through each product
    foreach ($data['product_id'] as $index => $product_id) {
        // Extract product details
        $eventName = $data['name'][$index];
        $registrations = isset($data['maxRegisterValue'][$index]) ? (int)$data['maxRegisterValue'][$index] : 0;
        $length = isset($data['Length'][$index]) ? $data['Length'][$index] : '';
        $gameType = isset($data['GameType'][$index]) ? $data['GameType'][$index] : '';
        $difficulty = isset($data['Difficulty'][$index]) ? $data['Difficulty'][$index] : '';
        $startTime = isset($data['StartTime'][$index]) ? $data['StartTime'][$index] : '';
        $taughtBy = isset($data['TaughtBy'][$index]) ? $data['TaughtBy'][$index] : '';
        $productVideo = isset($data['Product_Video'][$index]) ? $data['Product_Video'][$index] : '';
     
        // Prepare and execute the SQL query to insert product details
        $sql = "INSERT INTO " . DB_PREFIX . "event_product_details 
                SET 
                    events_id = '" . (int)$event_id . "',
                    events_name = '" . $this->db->escape($data['events_description'][1]['name']) . "',
                    product_id = '" . (int)$product_id . "',
                    name = '" . $this->db->escape($eventName) . "', 
                    registrations = '" . (int)$registrations . "',
                    length = '" . $this->db->escape($length) . "',
                    game_type = '" . $this->db->escape($gameType) . "',
                    difficulty = '" . $this->db->escape($difficulty) . "',
                    start_time = '" . $this->db->escape($startTime) . "',
                    taught_by = '" . $this->db->escape($taughtBy) . "',
                    video = '" . $this->db->escape($productVideo) . "'";
                    
         $query = $this->db->query("SELECT video FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
            if ($query->num_rows) {
                $video = $query->row['video'];
                if ($productVideo !== $video) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET video = '" . $productVideo . "' WHERE product_id = '" . (int)$product_id . "'");
                }
            }
     
        $this->db->query($sql);
    }

    // Remove any entries with event_id equal to 0
    $this->db->query("DELETE FROM " . DB_PREFIX . "event_product_details WHERE events_id = 0");

    return true;
}

/*
public function duplicatedata($events_id) {
     $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "event_product_details WHERE `events_id` = '" . $last_event_id . "'");
echo ($query->num_rows > 0);
                  
}
*/
public function getEventData($eventsId) {
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "event_product_details WHERE events_id = '" . (int)$eventsId . "'");
   

    if ($query->num_rows) {
        return $query->rows; // Fetch all rows instead of just one
    } else {
        return array(); // Return an empty array if no rows found
    }
}

public function updateEventData($events_id, $data) {

    // Append data to existing arrays
	foreach ($data['name'] as $name) {
		$data['product_name'][] = $name;
	}
	foreach ($data['maxRegisterValue'] as $registrations) {
		$data['registrations'][] = $registrations;
	}
	foreach ($data['Length'] as $Length) {
		$data['length'][] = $Length;
	}
	foreach ($data['GameType'] as $game_type) {
		$data['game_type'][] = $game_type;
	}
	foreach ($data['Difficulty'] as $difficulty) {
		$data['difficulty'][] = $difficulty;
	}
	foreach ($data['StartTime'] as $start_time) {
		$data['start_time'][] = $start_time;
	}
	foreach ($data['TaughtBy'] as $taught_by) {
		$data['taught_by'][] = $taught_by;
	}
	foreach ($data['Product_Video'] as $Product_Video) {
		$data['Product_Video'][] = $Product_Video;
	}
//	echo "<pre>";print_r($data);
	
    // Delete existing data for the given events_id
    $this->db->query("DELETE FROM " . DB_PREFIX . "event_product_details WHERE events_id = '" . (int)$events_id . "'");
	
	  foreach ($data['product_id'] as $index => $product_id) {
      
        // Extract data from $data arrays
        $eventName = $data['product_name'][$index];
        $registrations = $data['registrations'][$index];
        $length = $data['length'][$index];
        $gameType = $data['game_type'][$index];
        $difficulty = $data['difficulty'][$index];
        $startTime = $data['start_time'][$index];
        $taughtBy = $data['taught_by'][$index];
        $productVideo = $data['Product_Video'][$index];
		
        // Insert event product details
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "event_product_details 
            SET 
                events_id = '" . (int)$events_id . "',
                events_name = '" . $this->db->escape($data['events_description'][1]['name']) . "',
                product_id = '" . (int)$product_id . "',
                name = '" . $this->db->escape($eventName) . "', 
                registrations = '" . (int)$registrations . "',
                length = '" . $this->db->escape($length) . "',
                game_type = '" . $this->db->escape($gameType) . "',
                difficulty = '" . $this->db->escape($difficulty) . "',
                start_time = '" . $this->db->escape($startTime) . "',
                taught_by = '" . $this->db->escape($taughtBy) . "',
                video = '" . $this->db->escape($productVideo) . "'
        ");	
        
        $query = $this->db->query("SELECT video FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        if ($query->num_rows) {
            $video = $query->row['video'];
            if ($productVideo !== $video) {
                $this->db->query("UPDATE " . DB_PREFIX . "product SET video = '" . $productVideo . "' WHERE product_id = '" . (int)$product_id . "'");
            }
        }
    }
	
    $this->db->query("DELETE FROM " . DB_PREFIX . "event_product_details WHERE events_id = 0");
    
    return true; 
}


public function getExistingData($eventsId) {
      $query = $this->db->query("
        SELECT * FROM " . DB_PREFIX . "event_product_details
        WHERE events_id = '" . (int)$eventsId . "' AND product_id = '6394'
    ");
    return $query->rows;
}

  public function getRelatedProduct($events_id) {

        $this->load->model('catalog/product');
        $product_data = array();
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extendons_events_product pr 
        LEFT JOIN " . DB_PREFIX . "product p ON (pr.product_id = p.product_id) 
        LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)
        WHERE pr.events_id = '" . (int) $events_id . "' AND p.status = '1' AND p2s.store_id = '" . (int) $this->config->get('config_store_id') . "'");
        
        foreach ($query->rows as $result) {
            $product_data[] = $this->model_catalog_product->getProduct($result['product_id']);
        }
        
        return $product_data;
    }
    
    public function getRegisterDetails($events_id, $product_id) {
		//$sql = "SELECT *, (SELECT count(*) from " . DB_PREFIX . "event_registration er where er.events_id=$events_id and er.product_id=$product_id group by er.events_id, er.product_id) AS existing_registered_users from " . DB_PREFIX . "extendons_events_product eep where eep.events_id=$events_id and eep.product_id=$product_id";
		if($product_id!='99999999'){
			$sql = "SELECT *, (SELECT count(*) from " . DB_PREFIX . "event_registration er where er.events_id=$events_id and er.product_id=$product_id group by er.events_id, er.product_id) AS existing_registered_users from " . DB_PREFIX . "extendons_events_product eep where eep.events_id=$events_id and eep.product_id=$product_id";
		} else {
			$sql = "SELECT count(*) as  existing_registered_users from oc_event_registration er where er.events_id=$events_id and er.product_id=$product_id group by er.events_id, er.product_id";
		}
		//echo $sql."<br/>";
		$query = $this->db->query($sql);
        return $query->row;
    }
    
    public function getProductVideo($product_id) {
        $query = $this->db->query("SELECT video FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        return $query->row;
    }
    

}

