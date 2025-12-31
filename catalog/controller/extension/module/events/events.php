<?php
class ControllerExtensionModuleEventsEvents extends Controller {

    public function index() {

        $this->language->load('extension/module/events/events');
        $this->load->model('extension/module/events/events');
        $this->load->model('tool/image');

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'e.start_date';
        }
		
		if (isset($this->request->get['start_date'])) {
            $start_date = $this->request->get['start_date'];
        } else {
            $start_date = date('Y-m-d');
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = $this->request->get['limit'];
        } else {
            //$limit = $this->config->get('config_product_limit');
			$limit = 50;
        }
    
       
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
            'separator' => false
        );

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
		
		if (isset($this->request->get['start_date'])) {
            $url .= '&start_date=' . $this->request->get['start_date'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/events/events', $url),
            'separator' => $this->language->get('text_separator')
        );

        $this->document->setTitle($this->config->get('events_module_meta_title'));
        $this->document->setDescription($this->config->get('events_module_meta_description'));
        $this->document->setKeywords($this->config->get('events_module_meta_keywords'));  
        
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_empty'] = $this->language->get('text_empty');

        $data['text_display'] = $this->language->get('text_display');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_grid'] = $this->language->get('text_grid');
        $data['text_calendar'] = $this->language->get('text_calendar');
        $data['url_calendar'] = $this->url->link('extension/module/events/events/calendar');
        $data['text_sort'] = $this->language->get('text_sort');
        $data['text_limit'] = $this->language->get('text_limit');
        $data['button_continue'] = $this->language->get('button_continue');
        $data['button_list'] = $this->language->get('button_list');
        $data['button_grid'] = $this->language->get('button_grid');

        $data['events'] = array();

        $filter_data = array(
            'sort' => $sort,
			'start_date' => $start_date,
            'order' => $order,
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        );

        $events_total = $this->model_extension_module_events_events->getTotalEvents($filter_data);
        $results = $this->model_extension_module_events_events->getEvents($filter_data);
         $data['games_list'] = $this->model_extension_module_events_events->getGames();
         $games_list = $this->model_extension_module_events_events->getGames();

        foreach ($games_list as $image_result) {
		    $data['images'][] = array(
                'galleryThumb'  => $this->model_journal3_image->resize($image_result['image'], $this->journal3->settings->get('image_dimensions_popup_thumb.width'), $this->journal3->settings->get('image_dimensions_popup_thumb.height'), $this->journal3->settings->get('image_dimensions_popup_thumb.resize')),
                'image'         => $this->model_journal3_image->resize($image_result['image'], $this->journal3->settings->get('image_dimensions_thumb.width'), $this->journal3->settings->get('image_dimensions_thumb.height'), $this->journal3->settings->get('image_dimensions_thumb.resize')),
                'image2x'       => $this->model_journal3_image->resize($image_result['image'], $this->journal3->settings->get('image_dimensions_thumb.width') * 2, $this->journal3->settings->get('image_dimensions_thumb.height') * 2, $this->journal3->settings->get('image_dimensions_thumb.resize')),
                'popup'         => $this->model_journal3_image->resize($image_result['image'], $this->journal3->settings->get('image_dimensions_popup.width'), $this->journal3->settings->get('image_dimensions_popup.height'), $this->journal3->settings->get('image_dimensions_popup.resize')),
                'thumb'         => $this->model_journal3_image->resize($image_result['image'], $this->journal3->settings->get('image_dimensions_additional.width'), $this->journal3->settings->get('image_dimensions_additional.height'), $this->journal3->settings->get('image_dimensions_additional.resize')),
                'thumb2x'       => $this->model_journal3_image->resize($image_result['image'], $this->journal3->settings->get('image_dimensions_additional.width') * 2, $this->journal3->settings->get('image_dimensions_additional.height') * 2, $this->journal3->settings->get('image_dimensions_additional.resize'))
		    );
	    }
	    
        $event_month = '';
		$event_year = '';
        foreach ($results as $row) {
            if ($row['image']) {
                $image = $this->model_tool_image->resize($row['image'], 380, 180);
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', 380, 180);
            }
            // if ($row['image']) {
            //     $image = $this->model_tool_image->resize($row['image'], $this->config->get('theme_' . $this->config->get('config_theme').'_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme').'_image_product_height'));
            // } else {
            //     $image = false;
            // }
            
			
			$product_data = $this->model_extension_module_events_events->getRelatedProduct($row['events_id']);
            $data['products'] = array();
            foreach ($product_data as $product) {
                $registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($row['events_id'], $product['product_id']);
               // echo '<pre>';print_r($product_data);echo '</pre>';
                
                $data['products'][] = array(
                    'events_id' => $row['events_id'],
                    'product_id' => $product['product_id'],
                    'maxplayer' => $registered_user_details['max_player'],
                    'existing_registered_users' => (($registered_user_details['existing_registered_users'] == '')?0:$registered_user_details['existing_registered_users']),
                    'name' => $product['name'],
                    'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'canonical'),
                );
                
            }
			
			$data['product_count'] = count($data['products']);
			if($data['product_count']<=0){
				$registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($row['events_id'], '99999999');
			
                    
				$data['products'][] = array(
					'product_id' => '99999999',
					'maxplayer' => ($registered_user_details['max_player']>0)?$registered_user_details['max_player']:'999',
					'existing_registered_users' => (($registered_user_details['existing_registered_users'] == '' or $registered_user_details['existing_registered_users'] <=0)?0:$registered_user_details['existing_registered_users']),
					'name' => 'None',
					'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'canonical')
				);
			}
			    
		 
           // echo '<pre>';print_r($data['products']);
            $sum_of_maxplayer = 0;
            foreach ($data['products'] as $row1) {
                $sum_of_maxplayer = $sum_of_maxplayer + $row1['maxplayer'];
            }
            $data['sum_of_maxplayer'] = $sum_of_maxplayer;
            //echo '<pre>';echo 'Maxplayer: ';print_r($data['sum_of_maxplayer']);echo '</pre>';
            
            $sum_of_existing_registered_users = 0;
            foreach ($data['products'] as $row1) {
                $sum_of_existing_registered_users = $sum_of_existing_registered_users + $row1['existing_registered_users'];
            }
            $data['sum_of_existing_registered_users'] = $sum_of_existing_registered_users;
            
             $geteventmaxplayer = $this->model_extension_module_events_events->geteventmaxplayer($events_id);
            if ($geteventmaxplayer) {
                $EventmaxRegister = $geteventmaxplayer['maxregister']; // Access 'maxregister' directly since only one row is expected
                $data['eventmaxRegister'] = $EventmaxRegister;
            } else {
                $data['eventmaxRegister'] = 999; // Set a default value if no maxregister is found
            }
            
               // Query to retrieve maxregister from extendons_events table
              $events_id = $row['events_id'];
                $maxregisterquery = $this->db->query("SELECT maxregister FROM " . DB_PREFIX . "extendons_events WHERE events_id = '" . $events_id . "'");
               $maxregister = $maxregisterquery->row['maxregister'];
            //	echo "<pre>";		print_r($maxregister);
			
            $data['events'][] = array(
                'events_id' => $row['events_id'],
                'thumb' => $image,
                'name' => $row['name'],
				'day' => date($this->language->get('event_date_format_small_day'), strtotime($row['start_date'])),
				'date' => date($this->language->get('event_date_format_small_date'), strtotime($row['start_date'])),
				'event_month' => ($event_month!=date('F', strtotime($row['start_date'])))?date('F', strtotime($row['start_date'])):'',
				'event_year' => ($event_year!=date('Y', strtotime($row['start_date'])))?date('Y', strtotime($row['start_date'])):'',
               'start_date' => date($this->language->get('event_date_format_long'), strtotime($row['start_date'])),
                'end_date' => date($this->language->get('event_date_format_long'), strtotime($row['end_date'])),
                'start_time' =>date($this->language->get('event_date_format_start_time'), strtotime($row['start_date'])),
                'end_time' =>date($this->language->get('event_date_format_end_time'), strtotime($row['end_date'])),
                 'calender_start_date' => date('Ymd', strtotime($row['start_date'])),
                'calender_end_date' => date('Ymd', strtotime($row['end_date'])),
                'calender_start_time' => date('Hs', strtotime($row['start_date'])),
                'calender_end_time' => date('Hs', strtotime($row['end_date'])),
                 'sum_of_maxplayer' => $sum_of_maxplayer,
                'sum_of_existing_registered_users' => $sum_of_existing_registered_users,
                'maxregister'=> $maxregister,
                'venue' => $row['venue'],
                'description' => utf8_substr(strip_tags(html_entity_decode($row['description'], ENT_QUOTES, 'UTF-8')), 0, 500) . '..',
                'href' => $this->url->link('extension/module/events/events/view', 'events_id=' . $row['events_id'] . $url)
            );
           $events = $data['events'];
           $this->response->setOutput($this->load->view('extension/module/events/events_detail', array('events' => $events)));
			if($event_month==''){
				$data['event_month'] = $event_month = date('F', strtotime($row['start_date']));
				$today = date('Y-m-d');
				$today=date('Y-m-d', strtotime($today));

				$stratDate 	= date('Y-m-d', strtotime($row['start_date']));
				$endDate 	= date('Y-m-d', strtotime($row['end_date']));

				if (($today >= $stratDate) && ($today <= $endDate)){
					$data['current_month'] = 'Now';
				}else{
					$data['current_month'] = '';  
				}
				//if((strtotime($row['start_date'])<= strtotime(date('Y-m-d'))) and (strtotime($row['end_date'])>= strtotime(date('Y-m-d'))) )
			}
            
            /*echo "<pre>";
            print_r($data['events']);
            echo "</pre>";*/
        }
       // echo "<pre>";print_r($data['events']);
        $url = '';

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['sorts'] = array();

        $data['sorts'][] = array(
            'text' => $this->language->get('text_default'),
            'value' => 'p.start_date-ASC',
            'href' => $this->url->link('extension/module/events/events', 'sort=e.start_date&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_name_asc'),
            'value' => 'ed.name-ASC',
            'href' => $this->url->link('extension/module/events/events', 'sort=ed.name&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_name_desc'),
            'value' => 'ed.name-DESC',
            'href' => $this->url->link('extension/module/events/events', 'sort=ed.name&order=DESC' . $url)
        );

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $data['limits'] = array();

        $limits = array_unique(array($this->config->get('config_product_limit'), 25, 50, 75, 100));

        sort($limits);

        foreach ($limits as $value) {
            $data['limits'][] = array(
                'text' => $value,
                'value' => $value,
                'href' => $this->url->link('extension/module/events/events', $url . '&limit=' . $value)
            );
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }
        $pagination = new Pagination();
        $pagination->total = $events_total;
        $pagination->page = $page;
      
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/module/events/events', $url . '&page={page}');

        $data['pagination'] = $pagination->render();
    
        if ($limit > 0) {
            $data['pageresults'] = sprintf($this->language->get('text_pagination'), ($events_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($events_total - $limit)) ? $events_total : ((($page - 1) * $limit) + $limit), $events_total, ceil($events_total / $limit));
        }else{
            $data['pageresults'] = 0;
        }

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['limit'] = $limit;
        $data['continue'] = $this->url->link('common/home');
        $data['is_logged'] = $this->customer->isLogged();
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/events/events.tpl')) {
            $this->response->setOutput($this->load->view('extension/module/events/events', $data));
        } else {
            $this->response->setOutput($this->load->view('extension/module/events/events', $data));
        }
    }

    public function view() {
        $this->language->load('extension/module/events/events');
        $this->load->model('extension/module/events/events');
        $this->load->model('tool/image');
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
            'separator' => false
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/events/events'),
            'separator' => $this->language->get('text_separator')
        );
        // get event detail
        if (isset($this->request->get['events_id'])) {
            $events_id = (int) $this->request->get['events_id'];
        } else if (isset($this->request->get['suffix'])) {
            $events_id = (int) $this->model_extension_module_events_events->getEventIdBySuffix($this->request->get['suffix']);
        } else {
            $events_id = 0;
        }
        $event_info = $this->model_extension_module_events_events->getEvent($events_id);
        
        /*echo '<pre>';
        print_r($event_info);
        echo '</pre>';
        
        
            // Get event data from the model
            $eventData = $this->model_extension_module_events_events->showEventData($events_id);

           
            $data['name'] = $eventData['name'];
            $data['registrations'] = $eventData['registrations'];
            $data['length'] = $eventData['length'];
            $data['game_type'] = $eventData['game_type'];
            $data['difficulty'] = $eventData['difficulty'];
            $data['start_time'] = $eventData['start_time'];
            $data['taught_by'] = $eventData['taught_by'];
            $data['video'] = $eventData['video'];          */
            
            $data['events'] = array();
          $eventData = $this->model_extension_module_events_events->showEventData($events_id);
           $geteventmaxplayer = $this->model_extension_module_events_events->geteventmaxplayer($events_id);
			    $EventmaxRegister = $geteventmaxplayer[0]['maxregister'];
			//	print_r($EventmaxRegister);		
			$data['eventmaxRegister'] = $EventmaxRegister;  
			
        // echo "<pre>"; print_r($eventData);
            foreach ($eventData as $event) {
                 $registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($event['events_id'], $event['product_id']);
                $data['events'][] = array(
                    'product_id' => $event['product_id'],
                    'name' => $event['name'],
                    'registrations' => $event['registrations'],
                    'length' => $event['length'],
                    'game_type' => $event['game_type'],
                    'difficulty' => $event['difficulty'],
                    'start_time' => $event['start_time'],
                    'taught_by' => $event['taught_by'],
                    'video' => $event['video'],
                    'maxplayer' => $registered_user_details['max_player'],
                    'existing_registered_users' => (($registered_user_details['existing_registered_users'] == '')?0:$registered_user_details['existing_registered_users']),
                );
            }
           // echo '<pre>';
            //print_r( $data['events']);
      
            
        if ($event_info) {
            $data['breadcrumbs'][] = array(
                'text' => $event_info['name'],
                'href' => $this->url->link('extension/module/events/events/view', 'events_id=' . $events_id),
                'separator' => $this->language->get('text_separator')
            );
            $this->document->setTitle($event_info['name']);
            $this->document->setDescription($event_info['meta_description']);
            $this->document->setKeywords($event_info['meta_keyword']);
            $this->document->addLink($this->url->link('extension/module/events/events/view', 'events_id=' . $events_id), 'canonical');
            $this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
            $this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
            // locale
            $lang_key = array(
                'text_start_date',
                'text_end_date',
                'text_share',
                'text_video',
                'text_venue',
                'tab_description',
                'tab_contact',
                'text_contact_name',
                'text_contact_phone',
                'text_contact_fax',
                'text_contact_email',
                'text_contact_add',
            );
            
            
            foreach ($lang_key as $key) {
                $data[$key] = $this->language->get($key);
            }
            $data['heading_title'] = $event_info['name'];
            
            $data['events_module_show_map'] = $this->config->get('events_module_show_map');
            $this->load->model('tool/image');
            if ($event_info['image']) {
                $data['popup'] = $this->model_tool_image->resize($event_info['image'] , 760, 360);
            } else {
                $data['popup'] = '';
            }
            if ($event_info['image']) {
                $data['thumb'] = $this->model_tool_image->resize($event_info['image'] , 760, 360);
            } else {
                $data['thumb'] = '';
            }
            if ($event_info['video']) {
                
                preg_match(
                    '/[\\?\\&]v=([^\\?\\&]+)/',
                    $event_info['video'],
                    $matches
                );
                
                $data['video'] = $matches[1];
            } else {
                $data['video'] = '';
            }
            $event_expired = '';
            $today = date("Y-m-d h:i:s");
            $expire = $event_info['end_date']; //from database
            $today_time = strtotime($today);
            $expire_time = strtotime($expire);
            if ($expire_time < $today_time) { $event_expired = 'Closed';  }
            
          
            
       
            $data['event'] = array(
                'name' => $event_info['name'],
                'description' => html_entity_decode($event_info['description'], ENT_QUOTES, 'UTF-8'),
                'events_id' => $event_info['events_id'],
                'start_date' => date($this->language->get('event_date_format_long'), strtotime($event_info['start_date'])),
                'end_date' => date($this->language->get('event_date_format_long'), strtotime($event_info['end_date'])),
                'start_time' => date($this->language->get('event_date_format_start_time'), strtotime($event_info['start_date'])),
                'end_time' => date($this->language->get('event_date_format_start_time'), strtotime($event_info['end_date'])),
                'calender_start_date' => date('Ymd', strtotime($event_info['start_date'])),
                'calender_end_date' => date('Ymd', strtotime($event_info['start_date'])),
                'calender_start_time' => date('Hs', strtotime($row['start_date'])),
                'calender_end_time' => date('Hs', strtotime($event_info['end_date'])),
                'venue' => stripslashes($event_info['venue']),
                'latitude' => stripslashes($event_info['latitude']),
                'longitude' => stripslashes($event_info['longitude']),
                'expired'=>$event_expired,
                'href' => $this->url->link('account/event_register', 'events_id=' . $event_info['events_id'])
            );
         
         
           
            /*
            echo '<pre>';
            print_r($data['event']);
            echo '</pre>';
            */
            $data['product'] = array();
            $data['text_product'] = $this->language->get('text_product');
            $data['text_no_product'] = $this->language->get('text_no_product');
            $product_data = array();
            $product_data = $this->model_extension_module_events_events->getRelatedProduct($event_info['events_id']);
            if ($product_data) {
                foreach ($product_data as $product) {
                    $registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($event_info['events_id'], $product['product_id']);
                     if ($product['product_id'] == 6394) {
                      $registered_user_details['max_player'] = 0;
                    }
                    
                    $data['products'][] = array(
                        'product_id' => $product['product_id'],
                        'maxplayer' => $registered_user_details['max_player'],
                        'existing_registered_users' => (($registered_user_details['existing_registered_users'] == '')?0:$registered_user_details['existing_registered_users']),
                        'name' => $product['name'],
                        'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'canonical')
                    );
                }
                // echo "<pre>"; print_r($data['products']);  
                $sum_of_maxplayer = 0;
                foreach ($data['products'] as $row) {
                    
                    $sum_of_maxplayer = $sum_of_maxplayer + $row['maxplayer'];
                }
                $data['sum_of_maxplayer'] = $sum_of_maxplayer;
                
                $sum_of_existing_registered_users = 0;
                foreach ($data['products'] as $row) {
                    $sum_of_existing_registered_users = $sum_of_existing_registered_users + $row['existing_registered_users'];
                }
                $data['sum_of_existing_registered_users'] = $sum_of_existing_registered_users;
                
            }
				$this->load->model('account/event_register');
			$event_data = $this->model_extension_module_events_events->getEvent($events_id);
                $event_cost = $event_data['cost'];
		 //echo "<pre>";print_r($event_data);
		        $data['cost'] = $event_data['cost'];
		       // print_r("event cost:".$event_cost);
			$data['product_count'] = count($data['products']);
			if($data['product_count']<=0){
				$registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($event_info['events_id'], '99999999');
				
				$data['products'][] = array(
					'product_id' => '99999999',
					'maxplayer' => ($registered_user_details['max_player']>0)?$registered_user_details['max_player']:'999',
					'existing_registered_users' => (($registered_user_details['existing_registered_users'] == '' or $registered_user_details['existing_registered_users'] <=0)?0:$registered_user_details['existing_registered_users']),
					'name' => 'None',
					'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'canonical')
				);
			}
            
            $sum_of_maxplayer = 0;
            
            
                   
            foreach ($data['products'] as $row1) {
               
                $sum_of_maxplayer = $sum_of_maxplayer + $row1['maxplayer'];
            }
           
            $data['sum_of_maxplayer'] = $sum_of_maxplayer;
            //echo '<pre>';echo 'Maxplayer: ';print_r($data['sum_of_maxplayer']);echo '</pre>';
            
            $sum_of_existing_registered_users = 0;
            foreach ($data['products'] as $row1) {
                $sum_of_existing_registered_users = $sum_of_existing_registered_users + $row1['existing_registered_users'];
            }
            $data['sum_of_existing_registered_users'] = $sum_of_existing_registered_users;
			
			
            // echo '<pre>';print_r($data['product']);echo '</pre>';
            $data['images'] = array();
            $results = $this->model_extension_module_events_events->getEventsImages($events_id);
            foreach ($results as $result) {
                $data['images'][] = array(
                    'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme').'_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme').'_image_popup_height')),
                    'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme').'_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme').'_image_additional_height'))
                );
            }
            
            $data['contact_info'] = array();
            $contact_info = array(
                'contact_name' => '',
                'contact_phone' => '',
                'contact_fax' => '',
                'contact_email' => '',
                'contact_add' => '',
            );
            
            $data['contact_info'] = array_intersect_key($event_info, $contact_info); 
        }
        $data['is_logged'] = $this->customer->isLogged();
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/events/events_detail.tpl')) {
            $this->response->setOutput($this->load->view('extension/module/events/events_detail', $data));
        } else {
            $this->response->setOutput($this->load->view('extension/module/events/events_detail', $data));
        }
    }

    public function genCalendar($month, $year, $events = array()) {

        $this->language->load('extension/module/events/events');
        $this->load->model('extension/module/events/events');

		/* draw table */
        $calendar = '<table cellpadding="0" cellspacing="0" class="calendar" width="100%">';

        /* table headings */
        $headings = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $calendar.= '<tr class="calendar-row"><td class="calendar-day-head">' . implode('</td><td class="calendar-day-head">', $headings) . '</td></tr>';

        /* days and weeks vars now ... */
        $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $days_in_this_week = 1;
        $day_counter = 0;
        $dates_array = array();

        /* row for week one */
        $calendar.= '<tr class="calendar-row">';

        /* print "blank" days until the first of the current week */
        for ($x = 0; $x < $running_day; $x++):
            $calendar.= '<td class="calendar-day-np">&nbsp;</td>';
            $days_in_this_week++;
        endfor;

        /* keep going with days.... */
        for ($list_day = 1; $list_day <= $days_in_month; $list_day++):
            $calendar.= '<td class="calendar-day"><div style="position:relative;height:100px;">';
            /* add in the day number */
            $calendar.= '<div class="day-number">' . $list_day . '</div>';

            $event_day = $year . '-' . $month . '-' . $list_day;
            $limit = (int)($this->config->get('events_module_tooltip'))? $this->config->get('events_module_tooltip'): 5;
            $events = $this->model_extension_module_events_events->getEventsByDate($event_day, $limit);
            $title = '&nbsp;';
            $links = '';
            $pfx = '';
            if (!empty($events)) {


                $calendar .= "<div id='event-" . $list_day . "'>";
                foreach ($events as $event) {

                    $calendar .= '<div class="item">';
                    $pfx = $event['url_suffix'];
                    $title = $event['name']; 

                    $desc = html_entity_decode($event['description'], ENT_QUOTES, 'UTF-8');
                    if (strlen($desc) > 615) {
                        $desc = substr($desc, 0, 615) . '...';
                        $desc .= "<a href='" . $this->url->link('extension/module/events/events/view', 'suffix=' . $pfx) . "'>Read More</a>";
                    }
                    $calendar .= $title;

                    $calendar .= stripslashes("<div class='tooltip_description' style='display:none;'><a href='" . $this->url->link('extension/module/events/events/view', 'suffix=' . $pfx) . "'><h1>" . $title . "</h1></a> <p>(<b>From:</b> " . date('M d, Y - h:i a', strtotime($event['start_date'])) . " <b>To:</b> " . date('M d, Y - h:i a', strtotime($event['end_date'])) . ")</p>{$desc}</div>");
                    $calendar .= '</div>';
                }
                //$calendar .= stripslashes("<br/><a href=".$this->url->link('extension/module/events/events').">Events</a>");
                $calendar .= "</div>";
            } else {
                $calendar.= str_repeat('<p>&nbsp;</p>', 2);
            }

            $calendar.= '</div></td>';
            if ($running_day == 6):
                $calendar.= '</tr>';
                if (($day_counter + 1) != $days_in_month):
                    $calendar.= '<tr class="calendar-row">';
                endif;
                $running_day = -1;
                $days_in_this_week = 0;
            endif;
            $days_in_this_week++;
            $running_day++;
            $day_counter++;
        endfor;

        /* finish the rest of the days in the week */
        if ($days_in_this_week < 8):
            for ($x = 1; $x <= (8 - $days_in_this_week); $x++):
                $calendar.= '<td class="calendar-day-np">&nbsp;</td>';
            endfor;
        endif;

        /* final row */
        $calendar.= '</tr>';


        /* end the table */
        $calendar.= '</table>';

        /** DEBUG * */
        $calendar = str_replace('</td>', '</td>' . "\n", $calendar);
        $calendar = str_replace('</tr>', '</tr>' . "\n", $calendar);


        return $calendar;
    }

    public function calendar() {

        $this->language->load('extension/module/events/events');
        $this->load->model('extension/module/events/events');
        //$this->load->model('setting/setting');
        
        $this->document->setTitle($this->config->get('events_module_meta_title'));
        $this->document->setDescription($this->config->get('events_module_meta_description'));
        $this->document->setKeywords($this->config->get('events_module_meta_keywords'));  

        $this->document->addStyle('catalog/view/theme/' . $this->config->get('config_theme') . '/stylesheet/events/events.css');
        $this->document->addStyle('catalog/view/javascript/events/tooltip/stylesheets/jquery.tooltip/jquery.tooltip.css');
        $this->document->addScript('catalog/view/javascript/events/tooltip/javascripts/jquery.tooltip.js');


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
            'separator' => false
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/events/events'),
            'separator' => $this->language->get('text_separator')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_calendar'),
            'href' => $this->url->link('extension/module/events/events/calendar'),
            'separator' => $this->language->get('text_separator')
        );

        /* date settings */
        $month = (int) (isset($_GET['month']) ? $_GET['month'] : date('m'));
        $year = (int) (isset($_GET['year']) ? $_GET['year'] : date('Y'));
        $events = array();
        
        if (isset($this->request->post['month'])) {
            $month = (int)$this->request->post['month'];
        }
        
        if (isset($this->request->post['year'])) {
            $year = (int)$this->request->post['year'];
        }
        
        /* select month control */
        $select_month_control = '<select name="month" id="month" class="form-control">';
        for ($x = 1; $x <= 12; $x++) {
            $select_month_control.= '<option value="' . $x . '"' . ($x != $month ? '' : ' selected="selected"') . '>' . date('F', mktime(0, 0, 0, $x, 1, $year)) . '</option>';
        }
        $select_month_control.= '</select>';

        /* select year control */
        $year_range = 7;
        $select_year_control = '<select name="year" id="year" class="form-control">';
        for ($x = ($year - floor($year_range / 2)); $x <= ($year + floor($year_range / 2)); $x++) {
            $select_year_control.= '<option value="' . $x . '"' . ($x != $year ? '' : ' selected="selected"') . '>' . $x . '</option>';
        }
        $select_year_control.= '</select>';

        /* "next month" control */
        $next_month_link = '<a href="' . $this->url->link('extension/module/events/events/calendar') . '&month=' . ($month != 12 ? $month + 1 : 1) . '&year=' . ($month != 12 ? $year : $year + 1) . '" class="control" style="font-size:20px;text-decoration:none;color:#29AAE3;"> Next Month<i class="fa fa-forward" aria-hidden="true"></i></a>';

        /* "previous month" control */
        $previous_month_link = '<a href="' . $this->url->link('extension/module/events/events/calendar') . '&month=' . ($month != 1 ? $month - 1 : 12) . '&year=' . ($month != 1 ? $year : $year - 1) . '" class="control" style="font-size:20px;text-decoration:none;color:#29AAE3;"><i class="fa fa-backward" aria-hidden="true"></i> Previous Month</a>&nbsp;&nbsp;&nbsp;&nbsp;';


        /* bringing the controls together */
        $controls = '<form action="' . $this->url->link('extension/module/events/events/calendar') . '" method="post" class="form-inline" style="float:left">' . $select_month_control . $select_year_control . '&nbsp;<input type="submit" name="submit" value="Go" class="btn btn-primary" /> </form>';

        $controls .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $previous_month_link . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $next_month_link;
        $calendar = '<h2 style="float:left; padding-right:30px;">' . date('F', mktime(0, 0, 0, $month, 1, $year)) . ' ' . $year . '</h2>';

		
        $calendar .= '<div style="float:left;">' . $controls . '</div>';
		/*
        $calendar .= '<br/><br/>';
        $calendar .= '<br/><br/>';
        $calendar .= '<div style="clear:both;"></div>';
        $calendar .= $this->genCalendar($month, $year);
        $calendar .= '<br /><br />';
		*/
		
		/* Added by sharief to list calendar as grid */
		
		$this->load->model('tool/image');

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'e.start_date';
        }
		
		$start_date = "$year-$month-01";

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = $this->request->get['limit'];
        } else {
            $limit = $this->config->get('config_product_limit');
        }

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addScript('catalog/view/javascript/jquery/jquery.total-storage.min.js');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
            'separator' => false
        );

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }
		
		if (isset($this->request->get['start_date'])) {
            $url .= '&start_date=' . $this->request->get['start_date'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/events/events', $url),
            'separator' => $this->language->get('text_separator')
        );

        $this->document->setTitle($this->config->get('events_module_meta_title'));
        $this->document->setDescription($this->config->get('events_module_meta_description'));
        $this->document->setKeywords($this->config->get('events_module_meta_keywords'));  
        
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_empty'] = $this->language->get('text_empty');

        $data['text_display'] = $this->language->get('text_display');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_grid'] = $this->language->get('text_grid');
        $data['text_calendar'] = $this->language->get('text_calendar');
        $data['url_calendar'] = $this->url->link('extension/module/events/events/calendar');
        $data['text_sort'] = $this->language->get('text_sort');
        $data['text_limit'] = $this->language->get('text_limit');
        $data['button_continue'] = $this->language->get('button_continue');
        $data['button_list'] = $this->language->get('button_list');
        $data['button_grid'] = $this->language->get('button_grid');

        $data['events'] = array();

        $filter_data = array(
            'sort' => $sort,
			'start_date' => $start_date,
			'month' => $month,
			'year'  => $year,
            'order' => $order,
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        );

        $events_total = $this->model_extension_module_events_events->getTotalEvents($filter_data);
        $results = $this->model_extension_module_events_events->getEvents($filter_data);
        $event_month = '';
		$event_year = '';
        foreach ($results as $row) {
            if ($row['image']) {
                $image = $this->model_tool_image->resize($row['image'], 380, 180);
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', 380, 180);
            }
            // if ($row['image']) {
            //     $image = $this->model_tool_image->resize($row['image'], $this->config->get('theme_' . $this->config->get('config_theme').'_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme').'_image_product_height'));
            // } else {
            //     $image = false;
            // }
			
			$product_data = $this->model_extension_module_events_events->getRelatedProduct($row['events_id']);
            $data['products'] = array();
            foreach ($product_data as $product) {
                $registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($row['events_id'], $product['product_id']);
                //echo '<pre>';print_r($registered_user_details);echo '</pre>';
                $data['products'][] = array(
                    'events_id' => $row['events_id'],
                    'product_id' => $product['product_id'],
                    'maxplayer' => $registered_user_details['max_player'],
                    'existing_registered_users' => (($registered_user_details['existing_registered_users'] == '')?0:$registered_user_details['existing_registered_users']),
                    'name' => $product['name'],
                    'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'canonical'),
                );
                //echo '<pre>';print_r($data['product']);echo '</pre>';
            }
			
			$data['product_count'] = count($data['products']);
			if($data['product_count']<=0){
				$registered_user_details = $this->model_extension_module_events_events->getRegisterDetails($row['events_id'], '99999999');
				
				$data['products'][] = array(
					'product_id' => '99999999',
					'maxplayer' => ($registered_user_details['max_player']>0)?$registered_user_details['max_player']:'999',
					'existing_registered_users' => (($registered_user_details['existing_registered_users'] == '' or $registered_user_details['existing_registered_users'] <=0)?0:$registered_user_details['existing_registered_users']),
					'name' => 'None',
					'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'], 'canonical')
				);
			}
            
            $sum_of_maxplayer = 0;
            foreach ($data['products'] as $row1) {
                $sum_of_maxplayer = $sum_of_maxplayer + $row1['maxplayer'];
            }
            $data['sum_of_maxplayer'] = $sum_of_maxplayer;
            //echo '<pre>';echo 'Maxplayer: ';print_r($data['sum_of_maxplayer']);echo '</pre>';
            
            $sum_of_existing_registered_users = 0;
            foreach ($data['products'] as $row1) {
                $sum_of_existing_registered_users = $sum_of_existing_registered_users + $row1['existing_registered_users'];
            }
            $data['sum_of_existing_registered_users'] = $sum_of_existing_registered_users;
			
            $data['events'][] = array(
                'events_id' => $row['events_id'],
                'thumb' => $image,
                'name' => $row['name'],
				'day' => date($this->language->get('event_date_format_small_day'), strtotime($row['start_date'])),
				'date' => date($this->language->get('event_date_format_small_date'), strtotime($row['start_date'])),
				'event_month' => ($event_month!=date('F', strtotime($row['start_date'])))?date('F', strtotime($row['start_date'])):'',
				'event_year' => ($event_year!=date('Y', strtotime($row['start_date'])))?date('Y', strtotime($row['start_date'])):'',
                'start_date' => date($this->language->get('event_date_format_long'), strtotime($row['start_date'])),
                'end_date' => date($this->language->get('event_date_format_long'), strtotime($row['end_date'])),
                'start_time' =>date($this->language->get('event_date_format_start_time'), strtotime($row['start_date'])),
                'end_time' =>date($this->language->get('event_date_format_end_time'), strtotime($row['end_date'])),
                'sum_of_maxplayer' => $sum_of_maxplayer,
                'sum_of_existing_registered_users' => $sum_of_existing_registered_users,
                'venue' => $row['venue'],
                'description' => utf8_substr(strip_tags(html_entity_decode($row['description'], ENT_QUOTES, 'UTF-8')), 0, 500) . '..',
                'href' => $this->url->link('extension/module/events/events/view', 'events_id=' . $row['events_id'] . $url)
            );
            
            
			if($event_month==''){
				$data['event_month'] = $event_month = date('F', strtotime($row['start_date']));
				$today = date('Y-m-d');
				$today=date('Y-m-d', strtotime($today));

				$stratDate 	= date('Y-m-d', strtotime($row['start_date']));
				$endDate 	= date('Y-m-d', strtotime($row['end_date']));

				if (($today >= $stratDate) && ($today <= $endDate)){
					$data['current_month'] = 'Now';
				}else{
					$data['current_month'] = '';  
				}
				//if((strtotime($row['start_date'])<= strtotime(date('Y-m-d'))) and (strtotime($row['end_date'])>= strtotime(date('Y-m-d'))) )
			}
            
            /*echo "<pre>";
            print_r($data['events']);
            echo "</pre>";*/
        }
        //echo "<pre>";print_r($data);exit();
        $url = '';

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['sorts'] = array();

        $data['sorts'][] = array(
            'text' => $this->language->get('text_default'),
            'value' => 'p.start_date-ASC',
            'href' => $this->url->link('extension/module/events/events', 'sort=e.start_date&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_name_asc'),
            'value' => 'ed.name-ASC',
            'href' => $this->url->link('extension/module/events/events', 'sort=ed.name&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_name_desc'),
            'value' => 'ed.name-DESC',
            'href' => $this->url->link('extension/module/events/events', 'sort=ed.name&order=DESC' . $url)
        );

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $data['limits'] = array();

        $limits = array_unique(array($this->config->get('config_product_limit'), 25, 50, 75, 100));

        sort($limits);

        foreach ($limits as $value) {
            $data['limits'][] = array(
                'text' => $value,
                'value' => $value,
                'href' => $this->url->link('extension/module/events/events', $url . '&limit=' . $value)
            );
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }
        $pagination = new Pagination();
        $pagination->total = $events_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('extension/module/events/events', $url . '&page={page}');

        $data['pagination'] = $pagination->render();

        if ($limit > 0) {
            $data['pageresults'] = sprintf($this->language->get('text_pagination'), ($events_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($events_total - $limit)) ? $events_total : ((($page - 1) * $limit) + $limit), $events_total, ceil($events_total / $limit));
        }else{
            $data['pageresults'] = 0;
        }

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['limit'] = $limit;
		
		
        $data['month'] = $month;
        $data['year'] = $year;
        $data['calendar'] = $calendar;


        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');
        /*
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/events/calendar.tpl')) {
            $this->response->setOutput($this->load->view('extension/module/events/calendar', $data));
        } else {
            $this->response->setOutput($this->load->view('extension/module/events/calendar', $data));
        }
		*/
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/extension/module/events/events.tpl')) {
            $this->response->setOutput($this->load->view('extension/module/events/calendar_list', $data));
        } else {
            $calendar = $this->response->setOutput($this->load->view('extension/module/events/calendar_list', $data));
        }
		
    }

}

