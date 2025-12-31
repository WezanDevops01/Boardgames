<?php
class ControllerCheckoutSuccess extends Controller {
	public function index() {
	 	$this->load->language('checkout/success');   

			if (isset($this->session->data['order_id'])) {
		    	$order_id = $this->session->data['order_id'];
			    $this->load->model('extension/module/google_ecommerce');
			    $google_purchase_script =  $this->model_extension_module_google_ecommerce->build_ecommerce($order_id);
			    $data['ecommerce_tracking_script'] = $google_purchase_script['google_purchase'].$google_purchase_script['pixel_script'];
			    
			    if ($this->config->get('ga_ecom_ads_conversion_status')){
				   $data['conversion_script'] = $google_purchase_script['ads_sale_conversion'];
			    }else{
			        $data['conversion_script'] = '';
			    }
			    
			} else {
			    $data['ecommerce_tracking_script'] = '';
			    $data['conversion_script'] = '';
			}
			
        
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
			 

        if ($this->config->get('module_marketplace_status') && $this->config->get('module_wk_crosssell_crosssell_status')) {
          $cart_data = array();
          if($this->cart->getProducts()) {
            $cart_data = $this->cart->getProducts();
          }

          $existing_cart_id = array();
          foreach ($cart_data as $value) {
            $existing_cart_id[] = $value['cart_id'];
          }

          $this->load->model('account/product_saved_option');

          foreach ($this->model_account_product_saved_option->getMappedCart() as $value) {
            $mapped_cart = json_decode($value['cart_id']);
            $total_mapped_cart = count($mapped_cart);

            $total_found = 0;

            foreach ($mapped_cart as $cart_id_value) {

              if(in_array($cart_id_value,$existing_cart_id)) {
                $total_found++;
              }
            }
            if($total_mapped_cart == $total_found) {
              $this->model_account_product_saved_option->substractQuantity($value['type'], $value['i_id']);
              $this->model_account_product_saved_option->deleteMapping($value['id']);
            }
          }
        }
        
			
                if ($this->config->get('hb_cart_status')) {
    				if (isset($this->session->data['order_id'])) {
    				    if (isset($this->session->data['hb_cart_log_id'])) {
    				        $get_order_total = $this->db->query("SELECT `total` FROM `".DB_PREFIX."order` WHERE `order_id` = '".$this->session->data['order_id']."' LIMIT 1");
        					$hb_total = (float)$get_order_total->row['total'];
        				    $this->db->query("UPDATE " . DB_PREFIX . "hb_cart_log SET responded = '1', `totals` = '".(float)$hb_total."', `order_id` = '".(int)$this->session->data['order_id']."', date_modified = now() WHERE id = '" . (int)$this->session->data['hb_cart_log_id'] . "'");
    						$this->db->query("DELETE FROM " . DB_PREFIX . "hb_cart WHERE hb_cart_log_id = '" . (int)$this->session->data['hb_cart_log_id'] . "' ");
        				    unset($this->session->data['hb_cart_log_id']);
    				    }else{
    				        $this->db->query("DELETE FROM " . DB_PREFIX . "hb_cart WHERE customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "'");
    				    }
    				}
				}
				
              //split cart
              if($this->config->get('module_wk_split_cart_status')){
              /**
               * Deletes the products from cart and split cart table for which order has been made
               * @param  {Int} $splitcartkey contains the index of the carts
               */
                foreach ($this->cart->getProducts() as $splitcartkey => $products) {

                // Store Pickup module code starts here
                unset($this->session->data['pickup_points']['cart_id']);
                // Store Pickup module code ends here
                    
                  $this->cart->remove($products['cart_id']);
                }


                // Store Pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status') && isset($this->session->data['pickup_points'])) {

                    foreach ($this->session->data['pickup_points'] as $cart_id => $value) {

                        $this->db->query("INSERT INTO " . DB_PREFIX . "pickup_order SET order_id = '" . (int)$this->session->data['order_id'] . "', product_id = '" . (int)$value['product_id'] . "',order_product_id = '" . (int)$value['order_product_id'] . "', pickup_point_id = '" . (int)$value['pickup_point_id'] . "'");
                    }

                    unset($this->session->data['pickup_points']);

                }
                // Store Pickup module code ends here
                    
                unset($this->session->data['wk_vouchers']);
                unset($this->session->data['voucher']);
                unset($this->session->data['wk_coupon']);
                unset($this->session->data['shipping_method']);
                unset($this->session->data['wk_shipping_method']);
                unset($this->session->data['split_id']);
              }else{

            //=== iSenseLabs Promotions
            if (!empty($this->session->data['isl_promotions_checkout'])) {
                $email = $this->customer->getEmail() ? $this->customer->getEmail() : '';
                if (isset($this->session->data['guest']['email'])) {
                    $email = $this->session->data['guest']['email'];
                }

                $meta = array(
                    'email'     => $email,
                );

                foreach ($this->session->data['isl_promotions_checkout'] as $promo) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "promotions_log`
                        SET `promotion_id` = " . (int)$promo['promotion_id'] . ",
                            `customer_id` = " . (int)$this->customer->getId() . ",
                            `order_id` = " . (int)$this->session->data['order_id'] . ",
                            `store_id` = " . (int)$this->config->get('config_store_id') . ",
                            `meta` = '" . $this->db->escape(json_encode($meta)) . "',
                            `date_added` = NOW()");
                }

                $this->session->data['isl_promotions_checkout'] = array();
            }
            //=== iSenseLabs Promotions :: end
            
	
                // Store Pickup module code starts here	
                if ($this->config->get('module_mp_store_pickup_status') && isset($this->session->data['pickup_points'])) {	
                    foreach ($this->session->data['pickup_points'] as $cart_id => $value) {	
                        $this->db->query("INSERT INTO " . DB_PREFIX . "pickup_order SET order_id = '" . (int)$this->session->data['order_id'] . "', product_id = '" . (int)$value['product_id'] . "',order_product_id = '" . (int)$value['order_product_id'] . "', pickup_point_id = '" . (int)$value['pickup_point_id'] . "'");	
                    }	
                    if(!$this->config->get('module_wk_split_cart_status')){
                        unset($this->session->data['pickup_points']);
                        }	
                }	
                // Store Pickup module code ends here	
                    
                $this->cart->clear();
              }
            

        if ($this->customer->isLogged()) {

  			  $this->load->model('account/notification');

  			  $activity_data = array(
  			    'customer_id' => $this->customer->getId(),
  			    'name'        => $this->customer->getFirstName() . ' ' . $this->customer->getLastName(),
  			    'order_id'    => $this->session->data['order_id']
  			  );

  			  $this->model_account_notification->addActivity('order_account', $activity_data);
  			}
      
				

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

              //split cart
              /**
               * if products are left in the split cart then send back to cart page
               * @param  {Int} $this->cart->hasSplitProducts() &&            $this->config->get('module_wk_split_cart_status') contains split cart product count and split cart status
               */
              if($this->cart->hasSplitProducts() && $this->config->get('module_wk_split_cart_status')){
                $this->session->data['success'] = 'You have completed one Seller\'s order';
                $this->response->redirect($this->url->link('checkout/cart'));
              }
            
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
		
/* AbandonedCarts - Begin */
$this->load->model('setting/setting');
$this->load->model('extension/module/abandonedcarts');
if(!$this->model_extension_module_abandonedcarts->checkDbTable('abandonedcarts_settings')){
	$abandonedCartsSettings = $this->model_setting_setting->getSetting('abandonedcarts', $this->config->get('config_store_id'));
} else {
	$abandonedCartsSettings = $this->model_extension_module_abandonedcarts->getSetting('abandonedcarts', $this->config->get('config_store_id'));
}

if (isset($abandonedCartsSettings['abandonedcarts']['Enabled']) && $abandonedCartsSettings['abandonedcarts']['Enabled']=='yes') { 
    if (isset($this->session->data['abandonedCart_ID']) & !empty($this->session->data['abandonedCart_ID'])) {
        $id = $this->session->data['abandonedCart_ID'];
    } else if ($this->customer->isLogged()) {
        $id = (!empty($this->session->data['abandonedCart_ID'])) ? $this->session->data['abandonedCart_ID'] : $this->customer->getEmail();
    } else if (!empty($this->session->data["guest"]) && !empty($this->session->data["guest"]["email"])) {
        $id = $this->session->data["guest"]["email"];
    } else {
        $id = (!empty($this->session->data['abandonedCart_ID'])) ? $this->session->data['abandonedCart_ID'] : $this->session->getId();
    }

    $exists = $this->db->query("SELECT * FROM `" . DB_PREFIX . "abandonedcarts` WHERE `restore_id` = '$id' AND `ordered`=0");
    if (!empty($exists->rows)) {
        foreach ($exists->rows as $row) {
          if ($row['notified']!=0) {
              $this->db->query("UPDATE `" . DB_PREFIX . "abandonedcarts` SET `ordered` = 1, `store_id` = '" . (int)$this->config->get('config_store_id') . "' WHERE `id` = '".$row['id']."'");
          } else if ($row['notified']==0) {
              $this->db->query("DELETE FROM `" . DB_PREFIX . "abandonedcarts` WHERE `restore_id` = '$id' AND `id`='".$row['id']."'");
          }
        }
        $this->session->data['abandonedCart_ID']='';
        unset($this->session->data['abandonedCart_ID']);
    }
}
/* AbandonedCarts - End */
   			
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}

}