<?php
class ControllerExtensionModuleEventsEventRegistration extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/event_registration');
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/events/event_registration');
		
		$this->getList();
	}
	
	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}
		
		if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		} else {
			$filter_email = '';
		}
		
		if (isset($this->request->get['filter_event_id'])) {
			$filter_event_id = $this->request->get['filter_event_id'];
		} else {
			$filter_event_id = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}
		
		if (isset($this->request->get['filter_start_date'])) {
			$filter_start_date = $this->request->get['filter_start_date'];
		} else {
			$filter_start_date = '';
		}
		
		if (isset($this->request->get['filter_end_date'])) {
			$filter_end_date = $this->request->get['filter_end_date'];
		} else {
			$filter_end_date = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
		
         if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // Get the events_id from the POST data
            $events_id = $this->request->post['events_id'];

            // Process $events_id as needed
            $response_data = array('events_id' => $events_id);

            // Return JSON response
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($response_data));
        }

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_event_id'])) {
			$url .= '&filter_event_id=' . $this->request->get['filter_event_id'];
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}
		
		

		if (isset($this->request->get['filter_start_date'])) {
			$url .= '&filter_start_date=' . $this->request->get['filter_start_date'];
		}

		if (isset($this->request->get['filter_end_date'])) {
			$url .= '&filter_end_date=' . $this->request->get['filter_end_date'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/events/event_registration', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
		$data['add'] = $this->url->link('extension/module/events/event_registration/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['copy'] = $this->url->link('extension/module/events/event_registration/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('extension/module/events/event_registration/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		
		/*
		$data['add'] = $this->url->link('customer/wishlist/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('customer/wishlist/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		*/
        /////************view all event names code******************/////////
		$this->load->model('extension/events/events');
		$data['allevents'] = array();

		$allresults = $this->model_extension_events_events->getEvents($filter_data);
		
		foreach ($allresults as $result) {
			$data['allevents'][] = array(
				'filter_event_name' => $result['name'],
				'filter_events_id' => $result['events_id'],
				'filter_events_start' => date('d-m-Y', strtotime($result['start_date']))
			);
		}	
		
		/*
		echo '<pre>';
		print_r($data['allevents']);
		echo '</pre>';
		*/
		
		$data['entry_event_name'] = $this->language->get('entry_event_name');
		/////////////**************end of code *********///////
        
		$this->load->model('setting/store');

		$stores = $this->model_setting_store->getStores();

		$data['event_customers'] = array();
		
		
	    $filter_data = array(
			'filter_name'              => $filter_name,
			'filter_email'             => $filter_email,
			'filter_event_id'          => $filter_event_id,
			'filter_date_added'        => $filter_date_added,
			'filter_start_date'        => $filter_start_date,
			'filter_end_date'        =>   $filter_end_date,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                    => $this->config->get('config_limit_admin')
		);

		$events_customer_total = $this->model_extension_events_event_registration->getTotalEventCustomers($filter_data);
        $results = $this->model_extension_events_event_registration->getEventCustomers($filter_data);
       
        $event_cost = $result['event_cost'];
       
        foreach ($results as $result) {
             if ($result['event_cost'] == 0 || $result['payment_status'] == 1) {
        			$data['event_customers'][] = array(
        			    'events_id' => $result['events_id'],
        			    'customer_id'=> $result['customer_id'],
        				'name' => $result['name'],
                        'email' => $result['email'],
                        'telephone' => $result['telephone'],
                        'events_name' => $result['events_name'],
                        'date_added' => $result['date_added'],
                        'product_name'=> $result['product_name'],
                        'event_start_date'=> date($this->language->get('event_datetime_format'), strtotime($result['event_start_date'])),
                        'event_end_date'=> date($this->language->get('event_datetime_format'), strtotime($result['event_end_date'])),
                        'registerName' =>  $result['registerName'],
                        'event_cost' =>  $result['event_cost'],
                        'payment_status' =>  $result['payment_status'],
                        'href' => $this->url->link('extension/module/events/events/view', 'events_id=' . $row['events_id'] . $url)
        			);
             }
           // echo "<pre>";	print_r($data);
		}
	
		
		$data['user_token'] = $this->session->data['user_token'];

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

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_event_id'])) {
			$url .= '&filter_event_id=' . $this->request->get['filter_event_id'];
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if ($order == 'DESC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

        
		$data['sort_name'] = $this->url->link('extension/module/events/event_registration', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_events_name'] = $this->url->link('extension/module/events/event_registration', 'user_token=' . $this->session->data['user_token'] . '&sort=events_name' . $url, true);
		$data['sort_email'] = $this->url->link('extension/module/events/event_registration', 'user_token=' . $this->session->data['user_token'] . '&sort=email' . $url, true);
	    $data['sort_date_added'] = $this->url->link('extension/module/events/event_registration', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		
		if (isset($this->request->get['filter_event_id'])) {
			$url .= '&filter_event_id=' . $this->request->get['filter_event_id'];
		}
		
		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $event_customer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/events/event_registration', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($event_customer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($event_customer_total - $this->config->get('config_limit_admin'))) ? $event_customer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_total, ceil($event_customer_total / $this->config->get('config_limit_admin')));
       	$data['filter_name'] = $filter_name;
		$data['filter_email'] = $filter_email;
		$data['filter_date_added'] = $filter_date_added;
		$data['filter_event_id'] = $filter_event_id;
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/events/event_register', $data));
		$this->response->setOutput($this->load->view('extension/module/events/event_registration_list', $data));
	
	}
	
	public function delete() {
	    
		$this->load->language('extension/module/event_registration');
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/events/event_registration');

		if (isset($this->request->post['selected']) && $this->validateDeleteEventRegister()) {
		    
			foreach ($this->request->post['selected'] as $events_id) {
			    
				$this->model_extension_events_event_registration->delete($events_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

            if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
    		}
    		
    		if (isset($this->request->get['filter_event_id'])) {
    			$url .= '&filter_event_id=' . $this->request->get['filter_event_id'];
    		}
    		
    		if (isset($this->request->get['filter_email'])) {
    			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
    		}
    
    		if (isset($this->request->get['filter_date_added'])) {
    			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
    		}
            
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/module/events/event_registration', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}
    
             
    	public function geteventList() {
    	    
    	$this->document->setTitle($this->language->get('heading_title'));
        
    	$data['header'] = $this->load->controller('common/header');
    	$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
        
        $data['entry_event_name'] = $this->language->get('entry_event_name');
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/events/event_registration/event_register', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);
        $data['user_token'] = $this->session->data['user_token'];

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
			  
		$this->load->model('setting/store');
		$stores = $this->model_setting_store->getStores();
		
		$this->load->model('extension/events/events');
		$data['allevents'] = array();

		$allresults = $this->model_extension_events_events->getEvents($filter_data);
		foreach ($allresults as $result) {
			$data['allevents'][] = array(
				'filter_event_name' => $result['name'],
				'filter_events_id' => $result['events_id'],
				'filter_events_start' => date('d-m-Y', strtotime($result['start_date']))
			);
		}
		  // echo"<pre>"; print_r( $data);
          
            $this->response->setOutput($this->load->view('extension/module/events/event_register', $data));
            
            
    	}
	
        public function insert() {
        $this->load->model('extension/events/events');
        $this->load->model('extension/events/event_registration');
        $this->load->language('extension/module/event_registration');
       
        $data['products'] = array(); 
       
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['event_id'])) {
            $events_id = (int)$this->request->post['event_id']; // Ensure integer value
            $data['events_id'] = $events_id;
            
            /*
            $events = $this->model_extension_events_events->getEvents($events_id);
            foreach ($events as $event) {
             $maxregister = $event['maxregister'];
            } */
            
            $events = $this->model_extension_events_events->getEvents($events_id);
            $filteredEvent = null;
            foreach ($events as $event) {
                if ($event['events_id'] == $events_id) {
                    $filteredEvent = $event;
                    break; 
                }
            }
            
            $maxregister = $filteredEvent['maxregister'];
            $data['maxregister'] = $maxregister; 
            
            $results = $this->model_extension_events_events->getRelatedProduct($events_id);
            foreach ($results as $result) {
                $product_id = $result['product_id'];
                $product_name = $result['name'];
    
               // Assign default values
                $max_player = '';
                $existing_registered_users = '';
            	
            	if ($product_id != '99999999') {
                    $sql = "SELECT *, (SELECT COUNT(*) FROM " . DB_PREFIX . "event_registration er WHERE er.events_id = $events_id AND er.product_id = $product_id GROUP BY er.events_id, er.product_id) AS existing_registered_users FROM " . DB_PREFIX . "extendons_events_product eep WHERE eep.events_id = $events_id AND eep.product_id = $product_id";
                } else {
                    $sql = "SELECT COUNT(*) AS existing_registered_users FROM " . DB_PREFIX . "event_registration er WHERE er.events_id = $events_id AND er.product_id = $product_id GROUP BY er.events_id, er.product_id";
                }
            
                $query = $this->db->query($sql);
            	
                // Check if the query result exists and has the necessary fields
                if ($query->num_rows > 0) {
                    $max_player = $query->row['max_player'] ?? '';
                }
                
                $eventdatas = $this->model_extension_events_event_registration->getEventCustomers($events_id);
                //echo "<pre>";print_r($eventdatas);
                $event_cost = $eventdatas['event_cost'];
       
                foreach ($eventdatas as $eventdata) {
        			$data['event_customers'][] = array(
        			    'events_id' => $eventdata['events_id'],
        			    'customer_id'=> $eventdata['customer_id'],
        				'name' => $eventdata['name'],
                        'email' => $eventdata['email'],
                        'telephone' => $eventdata['telephone'],
                        'events_name' => $eventdata['events_name'],
                        'date_added' => $eventdata['date_added'],
                        'product_name'=> $eventdata['product_name'],
                        'event_start_date'=> date($this->language->get('event_datetime_format'), strtotime($eventdata['event_start_date'])),
                        'event_end_date'=> date($this->language->get('event_datetime_format'), strtotime($eventdata['event_end_date'])),
                        'registerName' =>  $eventdata['registerName'],
                        'event_cost' =>  $eventdata['event_cost'],
                        'payment_status' =>  $eventdata['payment_status']
        			);
                 }
                // Add the product details to the array
                $data['products'][] = array(
                    'events_id' => $events_id,
                    'product_id' => $product_id,
                    'name' => $product_name,
                    'maxplayer' => $max_player,
                    'maxregister' => $maxregister,
                   'existing_registered_users' => (($query->row['existing_registered_users'] == '' || $query->row['existing_registered_users'] <= 0) ? 0 : $query->row['existing_registered_users']),
                );
                
                $sumOfMaxPlayers += $max_player;
                 $sum_existing_registered_users += (($query->row['existing_registered_users'] == '' or $query->row['existing_registered_users'] <=0)?0:$query->row['existing_registered_users']);
            }
            $data['products'][] = $openRegister;	
            $data['products'] = array_filter($data['products']);
            
            $data['sumOfMaxPlayers'] = $sumOfMaxPlayers;
        $data['sum_existing_registered_users'] = $sum_existing_registered_users;
        
        if ($maxregister > 0){
        $sumOfMaxPlayers -= 4; //
        $maxRegister  = $maxregister - $sumOfMaxPlayers;
        $openRegister  = $maxregister - $sumOfMaxPlayers - $sum_existing_registered_users;
         $data['openRegister'] = $openRegister;
        $data['sumOfMaxPlayers'] = $sumOfMaxPlayers;
        $totalmax = max($maxRegister, $sumOfMaxPlayers);
        $data['totalmax'] = $totalmax;
        }else{
            $sumOfMaxPlayers -= 4;
             $openRegister  = $sumOfMaxPlayers - $sum_existing_registered_users;
             $data['sumOfMaxPlayers'] = '0';
        }
        if ($openRegister < 0){
        $data['openRegister'] = '0';
        } else {
             $data['openRegister'] = $openRegister;
        }
        
        $data['openRegister'] = $openRegister;
            
             $json_data = json_encode($data);
        // Send JSON response
        header('Content-Type: application/json');
       echo $json_data; 
       	$this->load->language('extension/module/event_registration');
              	$this->session->data['success'] = $this->language->get('text_success');

			$url = '';


    			$url .= '&filter_event_id=' . $events_id;
    	

			$this->response->redirect($this->url->link('extension/module/events/event_registration/insert', 'user_token=' . $this->session->data['user_token'] . $url, true));
      
            }
   
       $this->geteventList(); 
       	$this->session->data['success'] = $this->language->get('text_success');
    }

public function inserteventuser() {
    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
        $postData = $this->request->post;
        $postData['registerName'] = array_filter($postData['registerName']);
        $postData['registerName'] = array_values($postData['registerName']);
        $events_id = $this->request->post['filter_event_id'];
        $this->load->model('extension/events/events');
        $this->load->model('extension/events/event_registration');
       
        $events = $this->model_extension_events_events->getEvents($events_id);
        $filteredEvent = null;
        foreach ($events as $event) {
            if ($event['events_id'] == $events_id) {
                $filteredEvent = $event;
                break; 
            }
        }

        if ($filteredEvent) {
            $eventData = array();
            foreach ($postData['product_id'] as $index => $productId) {
                $productName = isset($postData['productname'][$index]) ? $postData['productname'][$index] : '';
                $registerName = isset($postData['registerName'][$index]) ? $postData['registerName'][$index] : '';
                $registerPerson = isset($postData['registerPerson']) ? $postData['registerPerson'] : '';
                $eventData[] = array(
                    'events_id' => $filteredEvent['events_id'],
                    'eventname' => $filteredEvent['name'],
                    'maxregister' => $filteredEvent['maxregister'],
                    'cost' => $filteredEvent['cost'],
                    'registerName' => $registerName,
                    'product_id' => $productId,
                    'productname' => $productName,
                    'registerPerson' => $registerPerson,
                );
            }

            $this->model_extension_events_event_registration->addCustomer($eventData);

            $this->load->language('extension/module/event_registration');
            $this->session->data['success'] = $this->language->get('text_success');
            $url = '&filter_event_id=' . $events_id;

            if (!empty($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (!empty($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (!empty($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }
          
        $response = $this->response->redirect($this->url->link('extension/module/events/event_registration/insert', 'user_token=' . $this->session->data['user_token'] . $url, true));

    
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($response));
        } else {
            // Handle case where the event is not found
            // You can add specific error handling or logging here
            // Redirect to an error page or display a message to the user
        }
    }
    
    $this->geteventList();
}





    protected function validateDeleteEventRegister() {
		if (!$this->user->hasPermission('modify', 'extension/module/events/event_registration')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
