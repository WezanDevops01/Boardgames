<?php
class ControllerExtensionModuleEventsEvents extends Controller {

    private $error = array();

    /*public function index() {
        // VARS
        $this->language->load('extension/module/events');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/events/events');
       
       

        if (!$this->model_extension_events_events->isModuleInstalled('events')) {
            $this->session->data['error'] = 'First install the module!';
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ':: '
        );

        $data['heading_title'] = $this->language->get('heading_title');

        $data['add'] = $this->url->link('extension/module/events/events/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/events/events/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['copy'] = $this->url->link('extension/module/events/events/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['repair'] = $this->url->link('extension/module/events/events/repair', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['PrintEvent'] = $this->url->link('extension/module/events/events/PrintEvent', 'user_token=' . $this->session->data['user_token']. $url, true);

        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['column_image'] = $this->language->get('column_image');
        $data['column_name'] = $this->language->get('column_name');
        $data['column_status'] = 'Status';
        $data['column_venue'] = $this->language->get('column_venue');
        $data['column_start_date'] = $this->language->get('column_start_date');
        $data['column_end_date'] = $this->language->get('column_end_date');
        $data['column_action'] = $this->language->get('column_action');

        $data['button_add'] = $this->language->get('button_add');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_copy'] = $this->language->get('button_copy');
        $data['button_edit'] = $this->language->get('button_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
    
        if (isset($this->request->post['selected'])) {
                $data['selected'] = (array)$this->request->post['selected'];
        } else {
                $data['selected'] = array();
        }

        $data['events'] = array();

        $filter_data = array(
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin'),
			'order_by' => 'start_date',
			'order' => 'DESC'
        );
       // echo "<pre>";print_r($filter_data);
        $this->load->model('tool/image');
        $events_total = $this->model_extension_events_events->getTotalEvents();
        $results = $this->model_extension_events_events->getEvents($filter_data);

        foreach ($results as $result) {
            
            if (is_file(DIR_IMAGE . $result['image'])) {
                $image = $this->model_tool_image->resize($result['image'], 40, 40);
            } else {
                $image = $this->model_tool_image->resize('no_image.png', 40, 40);
            }
            
            $event_id = $result['events_id'];
             	
            $data['events'][] = array(
                'events_id' => $result['events_id'],
                'image' => $image,
                'name' => $result['name'],
                'venue' => $result['venue'],
                'start_date' => $result['start_date'],
				'status' => (($result['status']==0)?'Disabled':'Enabled'),
                'end_date' => $result['end_date'],
                'maxregister' => $result['maxregister'],
                'edit' => $this->url->link('extension/module/events/events/update', 'user_token=' . $this->session->data['user_token'] . '&events_id=' . $result['events_id'] . $url, true),
                'event_details' => $this->url->link('extension/module/events/event_registration', 'user_token=' . $this->session->data['user_token'] . '&filter_event_id=' . $result['events_id'] . $url, true)
            );
        }
        $pagination = new Pagination();
        $pagination->total = $events_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
		// echo "<pre>";print_r($data['pagination']);
        $data['pageresults'] = sprintf($this->language->get('text_pagination'), ($events_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($events_total - $this->config->get('config_limit_admin'))) ? $events_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $events_total, ceil($events_total / $this->config->get('config_limit_admin')));

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
		
        $this->response->setOutput($this->load->view('extension/module/events/grid', $data));
       
        
    } */
    
    public function index() {
		// Load language
		$this->language->load('extension/module/events');
		
		// Set the document title
		$this->document->setTitle($this->language->get('heading_title'));
		
		// Load the events model
		$this->load->model('extension/events/events');
		
		// Load the image model
		$this->load->model('tool/image');
		
		// Check if the module is installed
		if (!$this->model_extension_events_events->isModuleInstalled('events')) {
			$this->session->data['error'] = 'First install the module!';
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
		}
			$data['add'] = $this->url->link('extension/module/events/events/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
			$data['delete'] = $this->url->link('extension/module/events/events/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
			$data['copy'] = $this->url->link('extension/module/events/events/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
			$data['repair'] = $this->url->link('extension/module/events/events/repair', 'user_token=' . $this->session->data['user_token'] . $url, true);
			$data['PrintEvent'] = $this->url->link('extension/module/events/events/PrintEvent', 'user_token=' . $this->session->data['user_token']. $url, true);
			$data['user_token'] = $this->session->data['user_token'];
			$data['text_no_results'] = $this->language->get('text_no_results');
			$data['text_list'] = $this->language->get('text_list');
			$data['text_confirm'] = $this->language->get('text_confirm');
			$data['column_image'] = $this->language->get('column_image');
			$data['column_name'] = $this->language->get('column_name');
			$data['column_status'] = 'Status';
			$data['column_venue'] = $this->language->get('column_venue');
			$data['column_start_date'] = $this->language->get('column_start_date');
			$data['column_end_date'] = $this->language->get('column_end_date');
			$data['column_action'] = $this->language->get('column_action');

			$data['button_add'] = $this->language->get('button_add');
			$data['button_delete'] = $this->language->get('button_delete');
			$data['button_copy'] = $this->language->get('button_copy');
			$data['button_edit'] = $this->language->get('button_edit');
			
			 if (isset($this->error['warning'])) {
				$data['error_warning'] = $this->error['warning'];
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}
		
			if (isset($this->request->post['selected'])) {
					$data['selected'] = (array)$this->request->post['selected'];
			} else {
					$data['selected'] = array();
			}
			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
				'separator' => false
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'], true),
				'separator' => ':: '
			);
		// Pagination setup
		$limit = $this->config->get('config_limit_admin');      
		
		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
		$start = ($page - 1) * $limit;
		
		$filter_data = array(
			'start_date'     => isset($this->request->get['filter_start_date']) ? $this->request->get['filter_start_date'] : '',
			'end_date'       => isset($this->request->get['filter_end_date']) ? $this->request->get['filter_end_date'] : '',
			'start'          => $start,
			'limit'          => $limit,
			'order_by'       => 'start_date',
			'order'          => 'DESC'
		);
		
		// Get the total number of events and the filtered events
		$events_total = $this->model_extension_events_events->getTotalEvents($filter_data);
		$results = $this->model_extension_events_events->getEvents($filter_data);
		
		$data['events'] = array();
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$data['events'][] = array(
				'events_id'     => $result['events_id'],
				'image'         => $image,
				'name'          => $result['name'],
				'venue'         => $result['venue'],
				'start_date'    => $result['start_date'],
				'status'        => ($result['status'] == 0 ? 'Disabled' : 'Enabled'),
				'end_date'      => $result['end_date'],
				'maxregister'   => $result['maxregister'],
				'edit'          => $this->url->link('extension/module/events/events/update', 'user_token=' . $this->session->data['user_token'] . '&events_id=' . $result['events_id'], true),
				'event_details' => $this->url->link('extension/module/events/event_registration', 'user_token=' . $this->session->data['user_token'] . '&filter_event_id=' . $result['events_id'], true)
			);
		}

		// Pagination
		$pagination = new Pagination();
		$pagination->total = $events_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->url = $this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true);
		$data['pagination'] = $pagination->render();
		$data['pageresults'] = sprintf($this->language->get('text_pagination'), ($events_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($events_total - $limit)) ? $events_total : ((($page - 1) * $limit) + $limit), $events_total, ceil($events_total / $limit));
		
		// Load the view template
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$this->response->setOutput($this->load->view('extension/module/events/grid', $data));
	}

     public function copy() {
    		//echo "Enter into copy here";die;
    		$this->language->load('extension/module/events');
    
            $this->document->setTitle($this->language->get('heading_title'));
    
            $this->load->model('extension/events/events');
    
    		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateCopy()) {
                //$this->model_extension_events_events->copyEvents($this->request->get['events_id'], $this->request->post);
    			
    			foreach ($this->request->post['selected'] as $events_id) {
    				//echo "Event id :".$events_id;
    			//	print_r($_POST);die;
    				if($events_id>0){
    					//echo "Event ID ".$events_id."<br/>";
    					$this->model_extension_events_events->copyEvents($events_id);
    				}
    			}
                
               
                
                $this->session->data['success'] = $this->language->get('text_success');
    
                $url = '';
    
                if (isset($this->request->get['page'])) {
                    $url .= '&page=' . $this->request->get['page'];
                }
    
                $this->response->redirect($this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'] . $url, true));
            }
    
    		$this->index();
    	}

    public function insert() {
        $this->language->load('extension/module/events');
        $this->document->setTitle($this->language->get('heading_title'));
    
        $this->load->model('extension/events/events');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

               $maxregister =  $this->request->post['maxregister'] ;
                $cost =  $this->request->post['cost_hidden'] ;	
       
            $this->model_extension_events_events->addEvent($this->request->post);
    
            $this->session->data['success'] = $this->language->get('text_success');
    
            $url = '';
    
            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
    
            $this->response->redirect($this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
    
        $this->getForm();
    }

        public function getProductInfo() {
        $this->load->language('extension/module/events');
    
        $json = array();
    
        if (isset($this->request->post['product_ids']) && is_array($this->request->post['product_ids'])) {
            $product_ids = array_map('intval', $this->request->post['product_ids']);
            // Check if $ExistingProductId is not empty
            if (!empty($this->request->post['ExistingProductId']) && is_array($this->request->post['ExistingProductId'])) {
                $ExistingProductId = array_map('intval', $this->request->post['ExistingProductId']);
                
                // Compare two arrays
                $difference = array_diff($product_ids, $ExistingProductId);			
				
				// Remove values from $ExistingProductId that are not present in $product_ids
				$ExistingProductId = array_intersect($ExistingProductId, $product_ids);				
				$ExistingProductId = array_values($ExistingProductId); // Reindex the array if needed				
				//echo "<pre>";print_r($product_ids);print_r($ExistingProductId);print_r($difference);
				
                if (!empty($difference)) {
                    // Ensure that the model is properly loaded
                    $this->load->model('extension/events/events');

                    foreach ($difference as $product_id) {
                        $product_info = $this->model_extension_events_events->getProductInfo($product_id);
						
						$difficulty = $this->model_extension_events_events->getProductAttribute($product_id, 510);
						
						$video = $this->model_extension_events_events->getProductVideo($product_id);

						if ($difficulty !== null) {
							if ($difficulty >= 0 && $difficulty <= 1.5) {
								$difficultyText = "Easy ";
							} elseif ($difficulty >= 1.6 && $difficulty <= 3.5) {
								$difficultyText = "Medium";
							} elseif ($difficulty >= 3.6 && $difficulty <= 4) {
								$difficultyText = "Difficult";
							} elseif ($difficulty > 4) {
								$difficultyText = "Very Difficult";
							}
						} else {
							$difficultyText = "0";
						}				 
						                         
                        if ($product_info) {
                            $attributes = array(
                                'player' => $this->model_extension_events_events->getProductAttribute($product_id, 501),
                                'Length' => $this->model_extension_events_events->getProductAttribute($product_id, 503),
                                'GameType' => $this->model_extension_events_events->getProductAttribute($product_id, 509),
                                'Difficulty' => $difficultyText,
                                'StartTime' => $this->model_extension_events_events->getProductAttribute($product_id, 512),
                                'TaughtBy' => $this->model_extension_events_events->getProductAttribute($product_id, 513),
                               // 'Video' => $this->model_extension_events_events->getProductAttribute($product_id, 517)
                               'Video' =>$video['video']
                            );
    
                            // Filter out null values and replace them with empty strings
                            $attributes = array_map(function ($value) {
                                return $value !== null ? $value : '';
                            }, $attributes);
                       
                            $json[] = array_merge($product_info, $attributes);
                        } else {
                            $json[] = array('error' => $this->language->get('error_product_not_found'));
                        }
                    }
					
					
                } else {
                    echo "No new product IDs found."; // No new product IDs found after comparing
					$this->error['product_info_error'] = "Game attribute missing";
                }
            } else {
                // If $ExistingProductId is empty, proceed without comparison
                echo "No existing product IDs found. Proceeding without comparison.";
				
            }
        } else {
            $json['error'] = $this->language->get('error_missing_product_ids');
			
        }
    
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


    public function ShowProductInfo() {
        $this->load->language('extension/module/events/events');
        
        // Get events_id from the URL
        $eventsId = $this->request->post['events_id'];
        
        // Load the model for getEventData
        $this->load->model('extension/events/events');
        
        // Fetch data from the model
        $ShowProductInfo = $this->model_extension_events_events->getEventData($eventsId);
    
        // Sample response
        if ($ShowProductInfo) {
            $json['success'] = $this->language->get('text_success');
            
            $productData = array();
            foreach ($ShowProductInfo as $productInfo) {
                $productData[] = array(
                    'product_id' => $productInfo['product_id'],
                    'product_name' => $productInfo['name'],
                    'registrations' => $productInfo['registrations'],
                    'length' => $productInfo['length'],
                    'game_type' => $productInfo['game_type'],
                    'difficulty' => $productInfo['difficulty'],
                    'start_time' => $productInfo['start_time'],
                    'taught_by' => $productInfo['taught_by'],
                    'video' => $productInfo['video']
                );
            }
    
            $json['data'] = $productData;
        } else {
            $json['error'] = $this->language->get('error_no_data_found');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

		public function update() { 
		
            
            $this->language->load('extension/module/events');
    
            $this->document->setTitle($this->language->get('heading_title'));
    
            $this->load->model('extension/events/events');
	       
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

                // echo "<pre>";print_r($this->request->post);
               $maxregister =  $this->request->post['maxregister'] ;
               
                 $cost =  $this->request->post['cost_hidden'] ;
                 
                $events_id  = $this->request->get['events_id'];
                
                $this->model_extension_events_events->editEvent($this->request->get['events_id'], $this->request->post);
                
                 $existingData = $this->model_extension_events_events->getExistingData($events_id);
                 
                $this->session->data['success'] = $this->language->get('text_success');
                    
                 $this->response->setOutput(json_encode($existingData));
    
                $url = '';
    
                if (isset($this->request->get['page'])) {
                    $url .= '&page=' . $this->request->get['page'];
                }
    
                $this->response->redirect($this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'] . $url, true));
            }
            
            if ($ShowProductInfo) {
                $json['data'] = $productData;
            } else {
                $json['error'] = $this->language->get('error_no_data_found');
            }
    
            $this->getForm();
        }
        
        
    public function delete() {
		//echo "Enter into delete here";die;
        $this->language->load('extension/module/events');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/events/events');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            
           // print_r($this->request->post);
            
            foreach ($this->request->post['selected'] as $events_id) {
                $this->model_extension_events_events->deleteEvent($events_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->index();
    }
    

public function PrintEvent() {
    // Load language and model
    $this->load->language('extension/module/events');
    $this->document->setTitle($this->language->get('heading_title'));
    $this->load->model('extension/events/events');

    // Process POST request
    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
        
        if (!empty($this->request->post['selected'])) {
            // Array to store all registerData for selected events
            $allRegisterData = array();
        
            // Iterate through selected events
            foreach ($this->request->post['selected'] as $events_id) {
                // Sanitize event ID
                $events_id = (int)$events_id;
                
                if ($events_id > 0) {
                    
                    // Execute the SQL query
                    $sql = "SELECT ee.maxregister, er.email, er.telephone, er.registerName, er.product_id, pd.name AS product_name, er.events_name, ee.start_date, ee.end_date,ee.venue 
                            FROM oc_extendons_events ee 
                            LEFT JOIN oc_event_registration er ON ee.events_id = er.events_id 
                            LEFT JOIN oc_product_description pd ON er.product_id = pd.product_id 
                            WHERE ee.events_id = '$events_id' AND (er.payment_status = 1 OR er.event_cost = 0)";
        
                    $query = $this->db->query($sql);
        
                    // Fetch data if query returns rows
                    if ($query->num_rows) {
                        foreach ($query->rows as $row) {
                            // Access the data and store in the allRegisterData array
                            $allRegisterData[] = array(
                                'maxregister' => $row['maxregister'],
                                'email' => $row['email'],
                                'telephone' => $row['telephone'],
                                'registerName' => $row['registerName'],
                                'product_id' => $row['product_id'],
                                'product_name' => $row['product_name'],
                                'events_name' => $row['events_name'],
                                'venue' => $row['venue'],
                                'start_date' => $row['start_date'],
                                'end_date' => $row['end_date']
                            );
                        }
                        
                               // game attribute
                        $ShowProductInfo = $this->model_extension_events_events->getEventData($events_id);
                        
                        // Sample response
                        if ($ShowProductInfo) {
                            $json['success'] = $this->language->get('text_success');
                
                        $this->load->model('extension/events/events');
                            $productData = array();
                            foreach ($ShowProductInfo as $productInfo) {
                                
                             $registered_user_details = $this->model_extension_events_events->getRegisterDetails($events_id, $productInfo['product_id']);
                             
                          // echo "<pre>";print_r($registered_user_details['max_player']);
                             $maxplayer =  $registered_user_details['max_player'];
                            // Check if $registered_user_details is not empty
                            if (!empty($registered_user_details)) {
                                $existing_registered_users = isset($registered_user_details['existing_registered_users']) ? $registered_user_details['existing_registered_users'] : 0;
                               // echo "Existing registered users: $existing_registered_users<br>";
                            } else {
                              $existing_registered_users = 0;
                            }
                                
                                $productData[] = array(
                                    'product_id' => $productInfo['product_id'],
                                    'product_name' => $productInfo['name'],
                                    'eventmaxplayer' => $row['maxregister'],
                                    'maxplayer' => $maxplayer,
                                     'existing_registered_users' => $existing_registered_users,
                                    'length' => $productInfo['length'],
                                    'game_type' => $productInfo['game_type'],
                                    'difficulty' => $productInfo['difficulty'],
                                    'start_time' => $productInfo['start_time'],
                                    'taught_by' => $productInfo['taught_by'],
                                    'video' => $productInfo['video']
                                );
                            }
                            $data['productData'] = $productData;
                          
                            $json['data'] = $productData;
                        } else {
                            $json['error'] = $this->language->get('error_no_data_found');
                        } 
                    }
                }
         
            }
            // Pass allRegisterData to the template
            $data['registerData'] = $allRegisterData;
        }
      //  echo "<pre>"; print_r($data );
       
        // Load language for sale/order
        $this->load->language('sale/order');
        // Set logo path
        if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
            $data['logo'] = $order_info['store_url'].'/image/'.$this->config->get('config_logo');
        } else {
            $data['logo'] = '';
        }

        // Load PrintEvent.twig view with data
        $this->response->setOutput($this->load->view('extension/module/events/PrintEvent', $data));

        return;
    }

    // Redirect to the main page if no POST request
    $this->index();
}

    protected function getForm() {

        $this->load->model('catalog/product');
        $this->load->model('extension/events/events');
        
        $lang_keys = array(
            'heading_title',
            'text_add',
            'text_edit',
            'text_none',
            'text_default',
            'text_image_manager',
            'text_browse',
            'text_clear',
            'text_enabled',
            'text_disabled',
            'text_percent',
            'text_amount',
            'entry_name',
            'entry_start_date',
            'entry_end_date',
            'entry_venue',
            'entry_latitude',
            'entry_longitude',
            'entry_meta_keyword',
            'entry_meta_description',
            'entry_description',
            'entry_maxregister',
            'entry_parent',
            'entry_filter',
            'entry_store',
            'entry_keyword',
            'entry_image',
            'entry_top',
            'entry_column',
            'entry_sort_order',
            'entry_status',
            'entry_layout',
            'entry_related',
            'entry_video',
            'entry_contact_name',
            'entry_contact_phone',
            'entry_contact_fax',
            'entry_contact_email',
            'entry_contact_add',
            'button_save',
            'button_cancel',
            'button_image_add',
            'tab_general',
            'tab_data',
            'tab_design',
            'tab_products',
            'tab_contact',
        );

        $data['text_form'] = !isset($this->request->get['events_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        foreach ($lang_keys as $lkey) {
            $data[$lkey] = $this->language->get($lkey);
        }

        // Errors
        foreach ($this->error as $k => $v) {
            $data[$k] = $v;
        }
        
        // breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'], true),
            'separator' => ' :: '
        );

        if (!isset($this->request->get['events_id'])) {
            $data['action'] = $this->url->link('extension/module/events/events/insert', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/events/events/update', 'user_token=' . $this->session->data['user_token'] . '&events_id=' . $this->request->get['events_id'], true);
        }

        $data['cancel'] = $this->url->link('extension/module/events/events', 'user_token=' . $this->session->data['user_token'], true);
       
        if (isset($this->request->get['events_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $event_info = $this->model_extension_events_events->getEvent($this->request->get['events_id']);
            $data['event'] = $event_info;
        }

        $data['user_token'] = $this->session->data['user_token'];

        $this->load->model('localisation/language');

        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->post['events_description'])) {
            $data['events_description'] = $this->request->post['events_description'];
        } elseif (isset($this->request->get['events_id'])) {
            $data['events_description'] = $this->model_extension_events_events->getEventsDescriptions($this->request->get['events_id']);
        } else {
            $data['events_description'] = array();
        }
        /*
          if (isset($this->request->post['path'])) {
          $data['path'] = $this->request->post['path'];
          } elseif (!empty($event_info)) {
          $data['path'] = $event_info['path'];
          } else {
          $data['path'] = '';
          } */

        // if (isset($this->request->post['parent_id'])) {
        //      $data['parent_id'] = $this->request->post['parent_id'];
        // } elseif (!empty($category_info)) {
        //      $data['parent_id'] = $category_info['parent_id'];
        // } else {
        //      $data['parent_id'] = 0;
        // }

        $this->load->model('setting/store');

        $data['stores'] = $this->model_setting_store->getStores();
 
        if (isset($this->request->post['events_store'])) {
            $data['events_store'] = $this->request->post['events_store'];
        } elseif (isset($this->request->get['events_id'])) {
            $data['events_store'] = $this->model_extension_events_events->getEventsStores($this->request->get['events_id']);
        } else {
            $data['events_store'] = array(0);
        }

        if (isset($this->request->post['keyword'])) {
            $data['keyword'] = $this->request->post['keyword'];
        } elseif (!empty($event_info)) {
            $data['keyword'] = $event_info['keyword'];
        } else {
            $data['keyword'] = '';
        }
    
        if (isset($this->request->post['start_date'])) {
            $data['start_date'] = $this->request->post['start_date'];
        } elseif (!empty($event_info)) {
            $data['start_date'] = $event_info['start_date'];
        } else {
            $data['start_date'] = '';
        }

    
        if (isset($this->request->post['end_date'])) {
            $data['end_date'] = $this->request->post['end_date'];
        } elseif (!empty($event_info)) {
            $data['end_date'] = $event_info['end_date'];
        } else {
            $data['end_date'] = '';
        }

    
        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } elseif (!empty($event_info)) {
            $data['image'] = $event_info['image'];
        } else {
            $data['image'] = '';
        }
        
        if (isset($this->request->post['maxregister'])) {
            $data['maxregister'] = $this->request->post['maxregister'];
        } elseif (!empty($event_info)) {
            $data['maxregister'] = $event_info['maxregister'];
        } else {
            $data['maxregister'] = '';
        }
       
        if (isset($this->request->post['cost_hidden'])) {
            $data['cost_hidden'] = $this->request->post['cost_hidden'];
        } elseif (!empty($event_info)) {
            $data['cost_hidden'] = $event_info['cost'];
        } else {
            $data['cost_hidden'] = '';
        }

        $this->load->model('tool/image');

        if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
        } elseif (!empty($event_info) && is_file(DIR_IMAGE . $event_info['image'])) {
            $data['thumb'] = $this->model_tool_image->resize($event_info['image'], 100, 100);
        } else {
            $data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
        }

        $data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

       
        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($event_info)) {
            $data['status'] = $event_info['status'];
        } else {
            $data['status'] = 1;
        }
		
		
		
        
        /*
            if (isset($this->request->post['event_layout'])) {
                $data['events_layout'] = $this->request->post['events_layout'];
            } elseif (isset($this->request->get['events_id'])) {
                $data['events_layout'] = $this->model_extension_events_events->getEventsLayouts($this->request->get['events_id']);
            } else {
                $data['events_layout'] = array();
            }
        */
        // Images
        /* if (isset($this->request->post['product_image'])) {
          $product_images = $this->request->post['product_image'];
          } elseif (isset($this->request->get['product_id'])) {
          $product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
          } else {
          $product_images = array();
          }

          $data['product_images'] = array();

          foreach ($product_images as $product_image) {
          if ($product_image['image'] && file_exists(DIR_IMAGE . $product_image['image'])) {
          $image = $product_image['image'];
          } else {
          $image = 'no_image.jpg';
          }

          $data['product_images'][] = array(
          'image'      => $image,
          'thumb'      => $this->model_tool_image->resize($image, 100, 100),
          'sort_order' => $product_image['sort_order']
          );
          } */

        if (isset($this->request->post['video'])) {
                preg_match(
                    '/[\\?\\&]v=([^\\?\\&]+)/',
                    $this->request->post['video'],
                    $matches
                );
            $data['video'] = $this->request->post['video'];

        } elseif (!empty($event_info)) {

            $data['video'] = $event_info['video'];

        } else {

            $data['video'] = '';
        }
        
			
		  // Related
        if (isset($this->request->post['product_related'])) {
            $products = $this->request->post['product_related'];
        } elseif (isset($this->request->get['events_id'])) {
            $products = $this->model_extension_events_events->getProductRelated($this->request->get['events_id']);
        } else {
            $products = array();
        }
		
		

        $event_id = $this->request->get['events_id'];

        // Fetch registered_player from the database
        $sql = "SELECT registered_player FROM `oc_extendons_events_product` WHERE `events_id` = '$event_id'";
        $query = $this->db->query($sql);

        if ($query->num_rows) {
            $registered_player = $query->row['registered_player'];
        } else {
            $registered_player = 0; // Set a default value if the record is not found
        }

        // Add this line before rendering the HTML input field
        $data['registered_player'] = $registered_player;

        $data['product_related'] = array();

        $i = 0;
        foreach ($products as $product_id) {
            $related_info = $this->model_catalog_product->getProduct($product_id);

            $attribute_info = $this->model_catalog_product->getProductAttributes($product_id);

            $attr_desc = $attribute_info[0]['product_attribute_description'][1]['text'];
            $Length = $attribute_info[13]['product_attribute_description'][1]['text'];
            $game_type = $attribute_info[8]['product_attribute_description'][1]['text'];
            $difficulty = $attribute_info[9]['product_attribute_description'][1]['text'];
            $StartTime = $attribute_info[11]['product_attribute_description'][1]['text'];
            $TaughtBy = $attribute_info[12]['product_attribute_description'][1]['text'];
            $Video = $attribute_info[14]['product_attribute_description'][1]['text'];
            $attr_words_split = explode(' ', $attr_desc);

            $getmaxplayer = $attr_words_split[2];

            if ($related_info) {
                $data['product_related'][$i] = array(
                    'product_id' => $related_info['product_id'],
                    'name' => $related_info['name'],
                    'max_player' => $getmaxplayer,
                    'Length' => $Length,
                    'game_type' => $game_type,
                    'difficulty' => $difficulty,
                    'StartTime' => $StartTime,
                    'TaughtBy' => $TaughtBy,
                    'Video' => $Video,
                );
               
            }

            $i++;
        }
	

        $this->load->model('design/layout');

        $data['layouts'] = $this->model_design_layout->getLayouts();

        // Images
        $data['entry_image'] = $this->language->get('entry_image');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['tab_image'] = $this->language->get('tab_image');

        $data['button_add_image'] = $this->language->get('button_add_image');
        $data['button_remove'] = $this->language->get('button_remove');

        if (isset($this->request->post['event_image'])) {
            $event_images = $this->request->post['event_image'];
        } elseif (isset($this->request->get['events_id'])) {
            $event_images = $this->model_extension_events_events->getEventImages($this->request->get['events_id']);
        } else {
            $event_images = array();
        }

        $data['event_images'] = array();

        foreach ($event_images as $event_image) {
            if ($event_image['image'] && file_exists(DIR_IMAGE . $event_image['image'])) {
                $image = $event_image['image'];
            } else {
                $image = 'no_image.jpg';
            }

            $data['event_images'][] = array(
                'image' => $image,
                'thumb' => $this->model_tool_image->resize($image, 100, 100),
                'sort_order' => $event_image['sort_order']
            );
        }

        $data['no_image'] = $this->model_tool_image->resize('no_image.jpg', 100, 100);

        $data['contact_info'] = array();
        $contact_info = array(
            'contact_name' => '',
            'contact_phone' => '',
            'contact_fax' => '',
            'contact_email' => '',
            'contact_add' => '',
        );
        
        if (isset($this->request->post['contact_info']))  {
            $data['contact_info'] = $this->request->post['contact_info'];
        } elseif (!empty($event_info)) {
            $data['contact_info'] = array_intersect_key($event_info, $contact_info); 
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/events/events_form', $data));
    }

    protected function validateForm() {

        if (!$this->user->hasPermission('modify', 'extension/module/events/events')) {
            $this->error['var_error_warning'] = $this->language->get('error_permission');
        }

        $extendonslib = $this->extendons; // include library

        foreach ($this->request->post['events_description'] as $language_id => $value) {
            if ((utf8_strlen($value['name']) < 2) || (utf8_strlen($value['name']) > 255)) {
                $this->error['var_error_name'][$language_id] = $this->language->get('error_name');
            }

            if ((utf8_strlen($value['venue']) < 2) || (utf8_strlen($value['venue']) > 255)) {
                $this->error['var_error_venue'][$language_id] = $this->language->get('error_venue');
            }

            if ((strlen((string)$value['latitude']) < 2) || (strlen((string)$value['latitude']) > 20)) {
                $this->error['var_error_latitude'][$language_id] = $this->language->get('error_latitude');
            }

            if ((strlen((string)$value['longitude']) < 2) || (strlen((string)$value['longitude']) > 20)) {
                $this->error['var_error_longitude'][$language_id] = $this->language->get('error_longitude');
            }

            if ((utf8_strlen($value['name']) == '')) {
                $this->error['var_error_name'][$language_id] = $this->language->get('error_empty');
            }
            if ((utf8_strlen($value['venue']) == '')) {
                $this->error['var_error_venue'][$language_id] = $this->language->get('error_empty');
            }
            if ((utf8_strlen($value['latitude']) == '')) {
                $this->error['var_error_latitude'][$language_id] = $this->language->get('error_empty');
            }
            if ((utf8_strlen($value['longitude']) == '')) {
                $this->error['var_error_longitude'][$language_id] = $this->language->get('error_empty');
            }

            if (utf8_strlen($value['description']) == '') {
                $this->error['var_error_description'][$language_id] = $this->language->get('error_empty');
            }
        }
        // echo "<pre>";print_r($this->error);exit();

        // Date validation
        $start_date = $this->request->post['start_date'];
        $end_date = $this->request->post['end_date'];

        if ($start_date == '') {
            $this->error['var_error_start_date'] = $this->language->get('error_empty');
        }

        if ($end_date == '') {
            $this->error['var_error_end_date'] = $this->language->get('error_empty');
        }

        if ($start_date && $end_date) {

            if (strtotime($start_date) > strtotime($end_date)) {
                $this->error['var_error_start_date'] = $this->language->get('error_date_range');
            }
        }
		/*
        // check duplicate
        $this->load->model('extension/events/events');
        $seo_keyword = $this->request->post['keyword'];
        $events_id = null;
        $primary_id = null;
        if (isset($this->request->get['events_id'])) {
            $events_id = $this->request->get['events_id'];
            $primary_id = 'events_id';
        }

        if ($seo_keyword != '') {

            if ($this->model_extension_events_events->checkDuplicate($seo_keyword, 'extendons_events', 'url_suffix', $primary_id, $events_id)) {

                $this->error['text_duplicate_error_var'] = $this->language->get('text_duplicate_error');
            }
        } else {

            $this->error['text_duplicate_error_var'] = $this->language->get('error_empty');
        }
*/
        $vid = $this->request->post['video'];
        if ($vid != '' && !filter_var($vid, FILTER_VALIDATE_URL)) {
                    
            $this->error['text_invalid_url_var'] = $this->language->get('text_invalid_url');    
        }
        
        // Email        
        $email = isset($this->request->post['contact_info']['contact_email'])? $this->request->post['contact_info']['contact_email']: '';
        
        if ($email != '') {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error['var_error_email_validate_var'] = $this->language->get('error_email_validate');
            }
        }
        
        // warnings
        if ($this->error && !isset($this->error['var_error_warning'])) {
            $this->error['var_error_warning'] = $this->language->get('error_warning');
        }
		
		
		//echo "<pre>"; print_r($this->request->post);
		
		$product_related = $this->request->post['product_related'];
		
		$ExistingProductId = $this->request->post['product_id'];
		

		// Check for missing product IDs
		$missing_ids = array_diff($product_related, $ExistingProductId);

		if (!empty($missing_ids)) {
			$this->error['missing_products_error_warning'] = "Error: " . count($missing_ids) . " game attribute detail(s) missing. ";
		}

		
		//echo "<pre>"; print_r($product_related);print_r($ExistingProductId);
		

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
		
		
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'extension/module/events/events')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
    
    protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'extension/module/events/events')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	


}

?>
