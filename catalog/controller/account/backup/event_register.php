<?php
class ControllerAccountEventRegister extends Controller {
	private $error = array();

	public function index() {
	    
	    if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/event_register', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}
    		
	    $this->load->language('account/event_register');
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
		$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
		$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

		$this->load->model('account/event_register');
		
		$this->load->model('extension/module/events/events');
		
		$this->load->model('account/customer');
		
		$this->load->model('catalog/product');
		
        $product_data = $this->model_extension_module_events_events->getRelatedProduct($data['events_id']);
	   
		$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
		
	     if (isset($this->request->get['events_id'])) {
            $event_id = (int)$this->request->get['events_id'];
            $event_data = $this->model_extension_module_events_events->getEvent($event_id);

            $data['event_name'] = $event_data['name'] ?? '';
            $data['event_date'] = isset($event_data['start_date'], $event_data['end_date'])
                ? date('Y-m-d h:i A', strtotime($event_data['start_date'])) . ' - ' . date('Y-m-d h:i A', strtotime($event_data['end_date']))
                : '';
        } else {
            $data['event_name'] = '';
            $data['event_date'] = '';
        }
		
		//$customer_info = $this->model_account_customer->getCustomer($this->request->get['customer_id']);
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

		    $data['events_id'] = (int)$this->request->post['events_id'];
		    
		    $data['events_name'] = $this->request->post['events_name'];
            
            $registerNames = $this->request->post['registerName'];
            $productIds = $this->request->post['product_id'];
            
            $arrayLength = count($registerNames);
            
            for ($i = 0; $i < $arrayLength; $i++) {
                $registerName = $registerNames[$i];
                $product_id = $productIds[$i];
            }

		    $event_data = $this->model_extension_module_events_events->getEvent($data['events_id']);
		    
		    $data['event_name'] = $event_data['name'];
		    
		    if (isset($event_data['start_date'], $event_data['end_date'])) {
				$start_date = date('Y-m-d h:i A', strtotime($event_data['start_date']));
				$end_date = date('Y-m-d h:i A', strtotime($event_data['end_date']));
				$data['event_date'] = $start_date . ' - ' . $end_date;
			
			} else {        
				$data['event_date'] = ''; 
			}
		    
		    $data['events_maxregister'] = $event_data['maxregister'];
    	    
    		$eventid_count = $this->model_account_event_register->getTotalCustomersByEvent($this->request->post['events_id']);
    		
			$product_ids = $this->request->post['product_id'];        
				// Load the necessary model
				$this->load->model('catalog/product');			
				// Get product names
				$product_names = array();
				foreach ($product_ids as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);
					if ($product_info) {
						$product_names[] = $product_info['name'];
						}
				}
    		
		    $this->model_account_event_register->getTotalCustomersByEvent($this->request->post['events_id']);

		    $registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($data['events_id'], $product_id);
			if(!isset($registered_user_details['max_player'])){
				$registered_user_details['max_player'] = 999;
				
			}			

            $event_cost = $event_data['cost'];
	        
	
			if($registered_user_details['max_player']> $registered_user_details['existing_registered_users']){
				$this->load->model('catalog/product');
				$postproduct_id = (int) $this->request->post['product_id'];
			
				$product_info = $this->model_catalog_product->getProduct($this->request->post['product_id']);
				if($event_cost == 0){
				$customer_id = $this->model_account_event_register->addCustomer($this->request->post);
			
				$res = $this->sendEmail($event_data, $customer_info, $product_info);
				
				 $this->load->model('account/customer');
                              
                             $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
                            $email = $customer_info['email'];
                            
                            $sql = "SELECT * FROM " . DB_PREFIX . "hb_email_list WHERE email = '" . $email . "' AND group_id = '4'";

                            $query = $this->db->query($sql);

                            if ($query->num_rows == 0) {
                                $insert_sql = "INSERT INTO " . DB_PREFIX . "hb_email_list (email, group_id) VALUES ('" . $email . "', '4')";
                                $this->db->query($insert_sql);
                                }
                                	$this->response->redirect($this->url->link('account/event_register_success'));
				}
			} elseif($registered_user_details['max_player'] == $registered_user_details['existing_registered_users']) {				
				$event_remaining_user = $registered_user_details['max_player'] - $registered_user_details['existing_registered_users'];
				  $error_message = "Error: " . implode(", ", $product_names) . " has a maximum limit (" . $registered_user_details['max_player'] . "); available slots (" . $event_remaining_user  . ").";
					$this->error['warning'] = $error_message;
			} elseif($registered_user_details['max_player'] < $registered_user_details['existing_registered_users']) {		
				$event_remaining_user = $registered_user_details['max_player'] - $registered_user_details['existing_registered_users'];			
				 $error_message = "Error: " . implode(", ", $product_names) . " has a maximum limit (" . $registered_user_details['max_player'] . "); available slots (" . $event_remaining_user  . ").";
					$this->error['warning'] = $error_message;					
				//$this->error['warning'] = $this->language->get('error_limit_register');
			}
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_events'),
			'href' => $this->url->link('extension/module/events/events', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_register'),
			'href' => $this->url->link('account/event_register', '', true)
		);
		
		//$data['text_account_already'] = sprintf($this->language->get('text_account_already'), $this->url->link('account/event_login', '', true));
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['firstname'])) {
			$data['error_firstname'] = $this->error['firstname'];
		} else {
			$data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$data['error_lastname'] = $this->error['lastname'];
		} else {
			$data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}


		$data['action'] = $this->url->link('account/event_register', '', true);
		
	    if (isset($this->request->post['existing_customer_id'])) {
			$data['existing_customer_id'] = $this->request->post['existing_customer_id'];
		} elseif ($this->customer->isLogged())  {
			$data['existing_customer_id'] = $customer_info['customer_id'];
		} else {
			$data['existing_customer_id'] = '';
		}
		
		
		if (isset($this->request->post['firstname'])) {
			$data['firstname'] = $this->request->post['firstname'];
		} elseif ($this->customer->isLogged())  {
			$data['firstname'] = $customer_info['firstname'];
		} else {
			$data['firstname'] = '';
		}
		
		if (isset($this->request->post['lastname'])) {
			$data['lastname'] = $this->request->post['lastname'];
		} elseif ($this->customer->isLogged()) {
			$data['lastname'] = $customer_info['lastname'];
		} else {
			$data['lastname'] = '';
		}
		
		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif ($this->customer->isLogged()) {
			$data['email'] = $customer_info['email'];
		} else {
			$data['email'] = '';
		}
		
		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} elseif ($this->customer->isLogged()) {
			$data['telephone'] = $customer_info['telephone'];
		} else {
			$data['telephone'] = '';
		}
		
        if (isset($this->request->get['events_id'])) {
			$events_id = (int)$this->request->get['events_id'];
		} else {
			$events_id = 0;
		}
		
		if (isset($this->request->post['events_name'])) {
			$data['events_name'] = $this->request->post['events_name'];
		} else {
			$data['events_name'] = '';
		}
		
		if (isset($this->request->get['events_id'])) {
			$data['events_id'] = (int)$this->request->get['events_id'];
		} elseif (isset($this->request->post['events_id'])) {
			$data['events_id'] = (int)$this->request->post['events_id'];
		} else{
		    $this->response->redirect($this->url->link('extension/module/events/events', '', true));
		}

		$event_data = $this->model_extension_module_events_events->getEvent($data['events_id']);
    

        $data['event_venue'] = $event_data['venue'];
        
       
		$event_cost = $event_data['cost'];
		
		$data['cost'] = $event_data['cost'];
		
		//print_r($event_cost);
		if ($event_cost == 5) {
			$event_ticket_product_id = '5001';
		} elseif ($event_cost == 10) {
			$event_ticket_product_id = '5002';
		} elseif ($event_cost == 15) {
			$event_ticket_product_id = '5003';
		} elseif ($event_cost == 20) {
			$event_ticket_product_id = '5004';
		} elseif ($event_cost == 30) {
			$event_ticket_product_id = '5005';
		}

		$data['event_ticket_product_id'] = $event_ticket_product_id ;
		
	
        $this->load->model('catalog/product');
		
        $event_ticket_product_info = $this->model_catalog_product->getProduct($event_ticket_product_id);
            
            $event_ids = $data['events_id'];
            $length = 10;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomString = '';
            
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }

            if(!isset($this->session->data['random_strings'][$event_ids])){
                $this->session->data['random_strings'][$event_ids] = $randomString;
            }
        $data['random_strings'] = $this->session->data['random_strings'][$event_ids];
          
          
          if (!isset($this->session->data['event_ids'])) {
            	$this->session->data['event_ids'] = [];
            }
            if (!in_array($event_ids, $this->session->data['event_ids'])) {
            	$this->session->data['event_ids'][] = $event_ids;
            }


           // $this->session->data['event_id'] = $event_ids;
            $data['event_ids'] =  $this->session->data['event_id'];

            $data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($event_ticket_product_id) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			} 
			

		$data['product'] = array();
	
        $product_data = $this->model_extension_module_events_events->getRelatedProduct($data['events_id']);

        if (isset($this->request->post['product_id'])) {
			$data['product_id'] = $this->request->post['product_id'];
		} else {
			$data['product_id'] = '';
		}

		$expired = '';
		$today = date("Y-m-d h:i:s");
		$expire = $event_data['end_date']; //from database
		$today_time = strtotime($today);
		$expire_time = strtotime($expire);
		if ($expire_time < $today_time) { $expired = 'Closed';  }
        $data['expired'] = $expired;
	
		$data['products'] = array();
        if ($product_data) {
            foreach ($product_data as $product) {
				$registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($data['events_id'], $product['product_id']);	
               //echo "<pre>"; print_r($registered_user_details);
                $data['products'][] = array(
                    'product_id' => $product['product_id'],
					'maxplayer' => $registered_user_details['max_player'],
					'existing_registered_users' => (($registered_user_details['existing_registered_users'] == '' or $registered_user_details['existing_registered_users'] <=0)?0:$registered_user_details['existing_registered_users']),
                    'name' => $product['name'] ,
                    'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'canonical')
                );				
              
                $sumOfMaxPlayers += $registered_user_details['max_player'];
                 $sum_existing_registered_users += (($registered_user_details['existing_registered_users'] == '' or $registered_user_details['existing_registered_users'] <=0)?0:$registered_user_details['existing_registered_users']);
            }						
            $data['products'][] = $openRegister;	
            $data['products'] = array_filter($data['products']);
            
        }		
      
        $geteventmaxplayer = $this->model_extension_module_events_events->geteventmaxplayer($data['events_id']);
			    $EventmaxRegister = $geteventmaxplayer[0]['maxregister'];
         $data['eventmaxRegister'] = $EventmaxRegister;            
        
        $data['sumOfMaxPlayers'] = $sumOfMaxPlayers;
        $data['sum_existing_registered_users'] = $sum_existing_registered_users;
        
        if ($EventmaxRegister > 0){
        $sumOfMaxPlayers -= 4; 
        $maxRegister  = $EventmaxRegister - $sumOfMaxPlayers;
        $openRegister  = $EventmaxRegister - $sumOfMaxPlayers - $sum_existing_registered_users;
         $data['openRegister'] = $openRegister;
        $data['sumOfMaxPlayers'] = $sumOfMaxPlayers;
        $totalmax = max($openRegister, $sumOfMaxPlayers);
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
            

       // $data['maxplayer'] = !empty($data['products']) ? max(array_column($data['products'], 'maxplayer')) : 999; // Set a default value if there are no products or maxplayer values
      
		$data['product_count'] = count($data['products']);
		if($data['product_count']<=0){
			$registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($data['events_id'], '99999999');
			
			$data['products'][] = array(
				'product_id' => '99999999',
				'maxplayer' => ($registered_user_details['max_player']>0)?$registered_user_details['max_player']:'999',
				'existing_registered_users' => (($registered_user_details['existing_registered_users'] == '' or $registered_user_details['existing_registered_users'] <=0)?0:$registered_user_details['existing_registered_users']),
				'name' => 'None',
				'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'canonical')
			);
		}
        
        
        $data['events_name'] = $event_data['name'];

		$data['is_logged'] = $this->customer->isLogged();
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
        
        $this->response->setOutput($this->load->view('account/event_register', $data));

	}
	
	
	public function sendEmail($event_data, $customer_info, $product_info){

        	if (isset($event_data['cost'])) {
            $cost = $event_data['cost'];
           // print_r("cost:".$cost);
            if ($cost > 0) {
                $payment_status = "Paid";
            } else {
                $payment_status = "Free";
            }
        } 
        $data['payment_status'] = $payment_status;
        
        // Gather event details
        $events_id = (int)$this->request->post['events_id'];
        $events_name = $this->request->post['events_name'];
        $product_ids = $this->request->post['product_id'];
        
        
        
        // Load the necessary model
        $this->load->model('catalog/product');
        
        // Get product names
        $product_names = array();
        foreach ($product_ids as $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);
            if ($product_info) {
                $product_names[] = $product_info['name'];
            }
        }
        
        // Compose email content
        $data['event_name'] = $events_name;
        $data['product_names'] = $product_names;
        $data['registerName'] = !empty($this->request->post['registerName']) ? $this->request->post['registerName'] : array();
        
        
        
        // Combine product names and register usernames into a single array
        $product_register_data = [];
        $max_count = max(count($product_names), count($data['registerName']));
        
        for ($i = 0; $i < $max_count; $i++) {
            $product_name = isset($product_names[$i]) ? $product_names[$i] : '';
            $register_username = isset($data['registerName'][$i]) ? $data['registerName'][$i] : '';
            $product_register_data[] = [
                'product_name' => $product_name,
                'register_username' => $register_username
            ];
        }
        
        $data['product_register_data'] = $product_register_data;

		$this->load->language('mail/event_register');

		$server = '';
		if ($this->request->server['HTTPS']) {
			$server = "https://www.boardgamesnmore.com/";
		} else {
			$server = "http://www.boardgamesnmore.com/";
		}
		
		if ($this->customer->isLogged()) {
		    $subject = html_entity_decode(sprintf($this->language->get('text_subject'), $customer_info['firstname']), ENT_QUOTES, 'UTF-8'); 
		}else{
		    $subject = html_entity_decode(sprintf($this->language->get('text_subject'), $this->request->post['firstname']), ENT_QUOTES, 'UTF-8'); 
		}
		
		if(!empty($customer_info['firstname'])){
			$data['firstname'] = $customer_info['firstname'];
		}else{
			$data['firstname'] = '';
		}
		
		if(!empty($customer_info['email'])){
			$data['email'] = $customer_info['email'];
		}else{
			$data['email'] = '';
		}
		
		if ($this->customer->isLogged()) {
		    $data['text_customer_salutation'] = html_entity_decode(sprintf($this->language->get('text_customer_salutation'), $customer_info['firstname'], $customer_info['lastname']));
		} else {
		    $data['text_customer_salutation'] = html_entity_decode(sprintf($this->language->get('text_customer_salutation'), $this->request->post['firstname'], $this->request->post['lastname']));    
		}
		
		$message = html_entity_decode(sprintf($this->language->get('text_welcome'), $event_data['name']));
		
		$event_data['start_date_mod'] = date($this->language->get('date_format_without_time'), strtotime($event_data['start_date']));
		
		$data['start_date_mod'] = 	$event_data['start_date_mod'];
		
		$event_data['end_date_mod'] = date($this->language->get('date_format_without_time'), strtotime($event_data['end_date']));
		
		if($event_data['start_date_mod'] == $event_data['end_date_mod']){
		    $event_date = date($this->language->get('date_format_long_with_time'), strtotime($event_data['start_date'])) . " - " . date($this->language->get('date_format_end_time'), strtotime($event_data['end_date']));
		} else {
		    $event_date = date($this->language->get('date_format_long_with_time'), strtotime($event_data['start_date'])) . " - " . date($this->language->get('date_format_long_with_time'), strtotime($event_data['end_date']));
		}
		
		//$event_date = date($this->language->get('date_format_long_with_time'), strtotime($event_data['start_date'])) . " - " . date($this->language->get('date_format_long_with_time'), strtotime($event_data['end_date']));
		
		$data['event_date'] = $event_date;
		
		//$event_venue = html_entity_decode(sprintf($this->language->get('text_venue'), $event_data['venue']));
		
		$data['message'] = $message;
		
		$data['event_venue'] = $event_data['venue'];
		
		//$event_games = html_entity_decode(sprintf($this->language->get('text_games'), $product_info['name']));
		//$data['event_games'] = $event_games;
		
		$data['text_games'] = $this->language->get('text_games');
		
		$data['event_games'] = (isset($product_info['name']) and $product_info['name']!='')?$product_info['name']:'General Registration';	
		
		
      
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] =  $server .'image/'.$this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}
		
		if (is_file(DIR_IMAGE . 'email/banner.jpg')) {
			$data['link_banner_image'] =  $server . 'image/email/banner.jpg';
		} else {
			$data['link_banner_image'] = '';
		}
	    $mail = new Mail($this->config->get('config_mail_engine'));
		$mail->parameter = $this->config->get('config_mail_parameter');
		$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
		$mail->smtp_username = $this->config->get('config_mail_smtp_username');
		$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
		$mail->smtp_port = $this->config->get('config_mail_smtp_port');
		$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
		
		if ($this->customer->isLogged()) {
		    $mail->setTo($customer_info['email']);
		}else{
		    $mail->setTo($this->request->post['email']);
		}
		
		//$mail->setFrom($this->config->get('config_email'));
		$senderemail = 'events@boardgamesnmore.com' ;
		$mail->setFrom($senderemail);
		$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
		$mail->setSubject($subject);
		$mail->setHtml($this->load->view('mail/event_register', $data));
		$mail->send();
	
		//$mail->setTo($this->config->get('config_email'));
		//$mail->send();
		
		return true;
	}
	
	
	private function validate() {
		if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

		if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

		if ($this->model_account_event_register->getTotalCustomersByEmailAndEvent($this->request->post['email'], $this->request->post['events_id'], $this->request->post['product_id'])) {
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		return !$this->error;
	}

    
}