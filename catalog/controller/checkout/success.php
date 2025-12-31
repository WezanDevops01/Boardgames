<?php
class ControllerCheckoutSuccess extends Controller {
	public function index() {
	 	$this->load->language('checkout/success');   
        
               $cart_event_ids = $this->session->data['event_ids']; 
                $products = $this->cart->getProducts();
                
                foreach ($cart_event_ids as $cart_event_id) {
                    $cart_event_product_names = [];
                    $cart_payment_array = [];
                
                    foreach ($products as $product) {
                        if (isset($product['option']) && is_array($product['option'])) {
                            foreach ($product['option'] as $option) {
                                $name = $option['name'];
                                $value = $option['value'];
                
                                if ($name === 'Event Payment Code') {
                                    $cart_event_payment_code = $value;
                                } elseif ($name === 'Event Registeruser') {
                                    $cart_event_register_user = $value;
                                } elseif ($name === 'Event Product Name') {
                                    $cart_event_product_names = explode(',', $value);
                
                                    // Escape and process each product name individually
                                    $escaped_names = array_map([$this->db, 'escape'], $cart_event_product_names);
                                    $product_ids = [];
                
                                    foreach ($escaped_names as $escaped_name) {
                                        $query = "SELECT product_id FROM `oc_product_description` WHERE `name` = '" . $escaped_name . "'";
                                        $result = $this->db->query($query);
                
                                        if ($result->num_rows > 0) {
                                            // Collect all product IDs for this name
                                            foreach ($result->rows as $row) {
                                                $product_ids[] = $row['product_id'];
                                            }
                                        } else {
                                            // Log if no matching products are found for this name
                                            $log = fopen(DIR_LOGS . 'registration_log.log', 'a');
                                            fwrite($log, date('Y-m-d G:i:s') . ' - Missing product id data for Event ID: ' . $cart_event_id . " - product name: " . $escaped_name . "\n");
                                            fclose($log);
                                        }
                                    }
                                    // Store all product IDs as a comma-separated string
                                    $cart_event_product_id = implode(',', $product_ids);
                                } elseif ($name === 'Event Name') {
                                    $cart_event_name = $value;
                                } elseif ($name === 'Event Time') {
                                    $cart_event_date = $value;
                                }
                            }
                
                            $cart_event_payment_code = $this->session->data['random_strings'][$cart_event_id] ?? '';
                
                            if (isset($cart_event_id) && isset($cart_event_payment_code) && isset($this->session->data['random_strings'][$cart_event_id])) {
                                $this->load->model('account/customer');
                                $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
                                $email = $customer_info['email'];
                
                                $sql = "SELECT * FROM " . DB_PREFIX . "hb_email_list WHERE email = '" . $email . "' AND group_id = '4'";
                                $query = $this->db->query($sql);
                
                                if ($query->num_rows == 0) {
                                    $insert_sql = "INSERT INTO " . DB_PREFIX . "hb_email_list (email, group_id) VALUES ('" . $email . "', '4')";
                                    $this->db->query($insert_sql);
                                }
                                unset($this->session->data['random_strings'][$cart_event_id]);
                            }
                
                           if (isset($cart_event_id) && !empty($cart_event_payment_code)) {
                            $this->load->model('account/event_register');
                            $this->load->model('extension/module/events/events');
                            $this->load->model('account/customer');
                            $this->load->model('catalog/product');
                        
                            // Get event and customer data
                            $event_data = $this->model_extension_module_events_events->getEvent($cart_event_id);
                            $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
                            $event_cost = $event_data['cost'] ?? '';
                        
                            // Populate event data array
                            $event_data = array_merge($event_data, [
                                'cart_event_id' => $cart_event_id,
                                'cart_event_payment_code' => $cart_event_payment_code,
                                'cart_event_register_user' => $cart_event_register_user ?? '',
                                'cart_event_name' => $cart_event_name ?? '',
                                'cart_event_product_id' => $cart_event_product_id,
                                'cart_order_id' => $this->session->data['order_id'] ?? '',
                                'cart_event_date' => $cart_event_date ?? '',
                                'cart_event_cost' => $event_cost,
                            ]);
                        
                            // Log the event data for debugging
                            $log = fopen(DIR_LOGS . 'registration_log.log', 'a');
                            fwrite($log, date('Y-m-d G:i:s') . ' - Event Data: ' . json_encode($event_data) . "\n");
                            fclose($log);
                        
                            try {
                                $this->model_extension_module_events_events->addCustomer($event_data, $customer_info);
                        
                                // Send email
                                $this->model_extension_module_events_events->sendEmail($event_data, $customer_info);
                        
                                // Log success
                                $log = fopen(DIR_LOGS . 'registration_log.log', 'a'); // Reopen log file for writing
                                fwrite($log, date('Y-m-d G:i:s') . ' - User registered successfully. Order ID: ' . $event_data['cart_order_id'] . ' Event ID: ' . $event_data['cart_event_id'] . "\n");
                                fclose($log);
                        
                                // Remove the event_id from the session
                                unset($this->session->data['event_ids'][$key]);
                            } catch (Exception $e) {
                                // Log any errors
                                $log = fopen(DIR_LOGS . 'registration_log.log', 'a'); // Reopen log file for writing
                                fwrite($log, date('Y-m-d G:i:s') . ' - Error registering user: ' . $e->getMessage() . "\n");
                                fclose($log);
                            }
                        }

                        }
                    }
                }
                
               // $event_data = $this->model_extension_module_events_events->getEvent($cart_event_id);
                // $event_cost = $event_data['cost'];
                            
               
                if($this->session->data['random_strings']){
                    foreach($this->session->data['random_strings'] as $keys =>$cart_event_payment_code){

                        $this->db->query("DELETE FROM `" . DB_PREFIX . "event_registration` WHERE  `event_payment_code` = '" . $this->db->escape($cart_event_payment_code) . "' AND `payment_status` = '0' AND `event_order_id` = '0'");
                       
                    }
                    
                }

              

	
		if (isset($this->session->data['order_id'])) {
		     $this->load->model('tool/upload');
			 
			$this->cart->clear();

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['guest']);
			unset($this->session->data['comment']);
			unset($this->session->data['order_id']);
			unset($this->session->data['coupon']);
			unset($this->session->data['reward']);
			unset($this->session->data['voucher']);
			unset($this->session->data['vouchers']);
			unset($this->session->data['totals']);
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_basket'),
			'href' => $this->url->link('checkout/cart')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_checkout'),
			'href' => $this->url->link('checkout/checkout', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('checkout/success')
		);

		if ($this->customer->isLogged()) {
			$data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('account/download', '', true), $this->url->link('information/contact'));
		} else {
			$data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
		}

		$data['continue'] = $this->url->link('common/home');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}

}