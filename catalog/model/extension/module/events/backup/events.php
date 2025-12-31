<?php
class ModelExtensionModuleEventsEvents extends Model {

    public function getEvents($data = array()) {
        /*
          if ($this->customer->isLogged()) {
          $customer_group_id = $this->customer->getCustomerGroupId();
          } else {
          $customer_group_id = $this->config->get('config_customer_group_id');
          }
         */
        $sql = "SELECT DISTINCT * "
                //. "GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR ' &gt; ') AS name, c.parent_id, c.sort_order "
                . "FROM " . DB_PREFIX . "extendons_events e "
                //. "LEFT JOIN " . DB_PREFIX . "category c ON (cp.path_id = c.category_id) "
                . "LEFT JOIN " . DB_PREFIX . "extendons_events_description ed ON (e.events_id = ed.events_id) "
                //. "LEFT JOIN " . DB_PREFIX . "events_description cd2 ON (cp.category_id = cd2.category_id) "
                . "WHERE e.status=1 AND ed.language_id = '" . (int) $this->config->get('config_language_id') . "'"; //. "' AND cd2.language_id = '" . (int) $this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND e." . $data['filter_name'] . " LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }
		if(!empty($data['month']) and !empty($data['year'] )) {
			$mn = $data['year']."-".str_pad($data['month'],2,'0',STR_PAD_LEFT);
			$start_date = $data['year']."-".$data['month']."-01";
			$end_date = $data['year']."-".$data['month']."-".cal_days_in_month(CAL_GREGORIAN,$data['month'],$data['year']);
			$sql .= " AND (e.start_date like '$mn%' or e.end_date like '$mn%')";
		} else if (!empty($data['start_date'])) {
            $sql .= " AND ((e.start_date >= date('" . $this->db->escape($data['start_date']) . "') and  e.end_date <= date('" . $this->db->escape($data['start_date']) . "')) OR (e.end_date >= date('" . $this->db->escape($data['start_date']) . "'))  OR (e.start_date >= date('" . $this->db->escape($data['start_date']) . "')))";
        }

        $sort_data = array(
            'ed.name',
            'e.start_date'
        );

        $sql .= " GROUP BY e.events_id";
		
		

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if ($data['sort'] == 'ed.name') {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
        } else {
            $sql .= " ORDER BY e.start_date";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC, LCASE(ed.name) DESC";
        } else {
            $sql .= " ASC, LCASE(ed.name) ASC";
        }


        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        } 
		//echo $sql;

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalEvents($data) {
		$filter_date = '';
		if(!empty($data['month']) and !empty($data['year'] )) {
			$mn = $data['year']."-".str_pad($data['month'],2,'0',STR_PAD_LEFT);
			$start_date = $data['year']."-".$data['month']."-01";
			$end_date = $data['year']."-".$data['month']."-".cal_days_in_month(CAL_GREGORIAN,$data['month'],$data['year']);
			$filter_date = " AND (e.start_date like '$mn%' or e.end_date like '$mn%')";	
			
		} else if (!empty($data['start_date'])) {
            $filter_date = " AND ((e.start_date >= date('" . $this->db->escape($data['start_date']) . "') and  e.end_date <= date('" . $this->db->escape($data['start_date']) . "')) OR (e.end_date >= date('" . $this->db->escape($data['start_date']) . "'))  OR (e.start_date >= date('" . $this->db->escape($data['start_date']) . "')))";
        }
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "extendons_events e WHERE status=1 $filter_date";
		//echo $sql;
        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getEvent($events_id) {

        //(SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'events_id=" . (int) $events_id . "') AS keyword
        $query = $this->db->query("SELECT DISTINCT *
        FROM " . DB_PREFIX . "extendons_events e
        LEFT JOIN " . DB_PREFIX . "extendons_events_description ed2 ON (e.events_id = ed2.events_id)
        WHERE e.events_id = '" . (int) $events_id . "'
        AND ed2.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        return $query->row;
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

    public function getEventIdBySuffix($suffix) {
        //$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "extendons_events e  WHERE e.events_id = '" . (int) $event_id ."'");
        $events_id = 0;
        $query = $this->db->query("SELECT e.events_id
        FROM " . DB_PREFIX . "extendons_events e
        LEFT JOIN " . DB_PREFIX . "extendons_events_description ed2 ON (e.events_id = ed2.events_id)
        WHERE e.url_suffix = '" . $suffix . "'
        AND ed2.language_id = '" . (int) $this->config->get('config_language_id') . "'");

        if ($query->num_rows > 0) {
            $events_id = $query->row['events_id'];
        }

        return $events_id;
    }

    /**
     * get products attached to event.
     * if product found send array else false
     * @param int $events_id
     * @return mix $product_array
     * */
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
     

    public function getEventsImages($events_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extendons_events_image WHERE events_id = '" . (int) $events_id . "' ORDER BY sort_order ASC");
        return $query->rows;
    }

    /**
     * extract event by date string 
     * @param string $date (Y-m-d)
     * @return array $result rows
     * */
    public function getEventsByDate($date, $limit) {

        $events = array();

        $query = "SELECT DISTINCT e.*, ed2.* FROM " . DB_PREFIX . "extendons_events e
        LEFT JOIN " . DB_PREFIX . "extendons_events_description ed2 ON (e.events_id = ed2.events_id)
        WHERE DATE(e.start_date) <= '{$date}'
        AND DATE(e.end_date) >= '{$date}'
        AND ed2.language_id = '" . (int) $this->config->get('config_language_id') . "' LIMIT {$limit}";

        $result = $this->db->query($query); //echo '<pre>';print_r($result->rows);echo '</pre>';

        return $result->rows;
    }

    public function showEventData($events_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "event_product_details WHERE events_id = '" . (int)$events_id . "' AND product_id != '6394'");
        
        return $query->rows;  // Return all rows as an array
    }
    	public function geteventmaxplayer($events_id) {
        $query = $this->db->query("SELECT maxregister FROM " . DB_PREFIX . "extendons_events WHERE events_id = '" . (int)$events_id . "'");
        
        return $query->rows;  // Return all rows as an array
    }
    
    
  public function addCustomer($event_data,$customer_info) {
     $cart_event_id = $event_data['cart_event_id'];   
     $event_cost = $event_data['cost'];
    $event_maxregister = $event_data['maxregister'];
    $cart_event_payment_code = $event_data['cart_event_payment_code'];
    $cart_event_register_user = $event_data['cart_event_register_user'];
    $cart_event_name = $event_data['cart_event_name'];
    $cart_event_product_id = $event_data['cart_event_product_id'];
    $cart_event_register_user = explode(',', $cart_event_register_user);
    $cart_event_product_id = explode(',', $cart_event_product_id);
    $customer_id = $customer_info['customer_id'];
    $firstname = $customer_info['firstname'];
    $lastname = $customer_info['lastname'];
    $email = $customer_info['email'];
    $telephone = $customer_info['telephone'];
    $cart_order_id = $event_data['cart_order_id'];
    //$cart_existing_customer_id = $event_data['cart_existing_customer_id'];
    $cart_existing_customer_id = '';
    $payment_status = '1';
    
    foreach ($cart_event_product_id as $key => $product_id) {
        $registerName = $cart_event_register_user[$key];
       $insertQuery = "INSERT INTO " . DB_PREFIX . "event_registration SET
         existing_customer_id = '" . (int)$cart_existing_customer_id . "',
            events_id = '" . $cart_event_id . "',
            events_name = '" . $this->db->escape($cart_event_name) . "',
            product_id = '" . $this->db->escape($product_id) . "',
            registerName = '" . $this->db->escape($registerName) . "',
            event_cost = '" . $this->db->escape($event_cost) . "',
            event_payment_code = '" . $this->db->escape($cart_event_payment_code) . "',
            payment_status = '" . $this->db->escape($payment_status) . "',
            firstname = '" . $this->db->escape($firstname) . "',
            lastname = '" . $this->db->escape($lastname) . "',
            email = '" . $this->db->escape($email) . "',
            telephone = '" . $this->db->escape($telephone) . "',
            registerPerson = '" . (int)$event_maxregister . "',
            event_order_id = '" . (int)$cart_order_id . "',
            ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "',
            date_added = NOW()";
             $this->db->query($insertQuery);
    }
    
		
        $customer_id = $this->db->getLastId();
    		
        return $customer_id;
    }
    
    public function sendEmail($event_data, $customer_info){
	    
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
                                    $events_id = $event_data['cart_event_id'];
                                    $events_name = $event_data['cart_event_name'];
                                    $product_id_comma =  $event_data['cart_event_product_id'];
                            		$product_ids = explode(',',$product_id_comma);
                                  
                                    //print_r($product_id_comma);
                                     // print_r($product_ids);
                                    
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
                                    
                                    $cart_event_register_user_array = explode(',',$event_data['cart_event_register_user']);
                                    // Compose email content
                                    $data['event_name'] = $events_name;
                                    $data['product_names'] = $product_names;
                                    $data['registerName'] = $cart_event_register_user_array;
                                    
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
                                        
                                       // echo "<pre>";
                                        //print_r($product_names);
                                       // print_r($product_register_data);
                                       // echo "</pre>";
                            
                            	        //die;
                            		$this->load->language('mail/event_register');
                            		
                            	    //	var_dump($productData);
                            		
                            		$server = '';
                            		if ($this->request->server['HTTPS']) {
                            			$server = "https://www.boardgamesnmore.com/";
                            		} else {
                            			$server = "http://www.boardgamesnmore.com/";
                            		}
                            		//echo "Server ".$server;die;
                            		
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
                            	//	print($data['message']);
                            		
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
	//	  echo "<pre>";
	//	  print_r($data);
	//	  echo "</pre>";
		 // die;
		 
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
	
	public function getGames() {
       // $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "event_game_list WHERE product_id != 6394");
       $query = $this->db->query("SELECT egl.*, op.image FROM " . DB_PREFIX . "event_game_list egl LEFT JOIN " . DB_PREFIX . "product op ON egl.product_id = op.product_id WHERE egl.product_id != 6394");
        return $query->rows;
    }

}
