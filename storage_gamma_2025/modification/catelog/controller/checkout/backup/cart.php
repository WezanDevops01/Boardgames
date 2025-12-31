<?php
class ControllerCheckoutCart extends Controller {

/* AbandonedCarts - Begin */
private function update_abandonedCarts() {

    $this->load->model('setting/setting');
	$this->load->model('extension/module/abandonedcarts');
	if(!$this->model_extension_module_abandonedcarts->checkDbTable('abandonedcarts_settings')){
		$abandonedCartsSettings = $this->model_setting_setting->getSetting('abandonedcarts', $this->config->get('config_store_id'));
	} else {
		$abandonedCartsSettings = $this->model_extension_module_abandonedcarts->getSetting('abandonedcarts', $this->config->get('config_store_id'));
	}
    $abandonedCartsSettings = isset($abandonedCartsSettings['abandonedcarts']) ? $abandonedCartsSettings['abandonedcarts'] : array();
    
    if (!empty($abandonedCartsSettings['Enabled']) && $abandonedCartsSettings['Enabled']=='yes') {
        if (isset($this->session->data['abandonedCart_ID']) & !empty($this->session->data['abandonedCart_ID'])) {
            $id = $this->session->data['abandonedCart_ID'];
        } else if ($this->customer->isLogged()) {
            $id = (!empty($this->session->data['abandonedCart_ID'])) ? $this->session->data['abandonedCart_ID'] : $this->customer->getEmail();
        } else if (!empty($this->session->data["guest"]) && !empty($this->session->data["guest"]["email"])) {
            $id = $this->session->data["guest"]["email"];
        } else {
            $id = (!empty($this->session->data['abandonedCart_ID'])) ? $this->session->data['abandonedCart_ID'] : $this->session->getId();
        }

        $ABcart = $this->cart->getProducts();

        $exists = $this->db->query("SELECT * FROM `" . DB_PREFIX . "abandonedcarts` WHERE `restore_id` = '$id' AND `ordered`=0");

        if (!empty($exists->row) && empty($ABcart)) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "abandonedcarts` WHERE `restore_id` = '$id' AND `ordered`=0");
            $this->session->data['abandonedCart_ID']=''; 
            unset($this->session->data['abandonedCart_ID']);
        } else if (!empty($exists->row) && !empty($ABcart))	{
            $cart = json_encode($ABcart);
            $this->db->query("UPDATE `" . DB_PREFIX . "abandonedcarts` SET `cart` = '".$this->db->escape($cart)."', `date_modified`=NOW(), `store_id` = '" . (int)$this->config->get('config_store_id') . "' WHERE `restore_id`='$id' AND `ordered`=0");
        }
    }
}

private function register_abandonedCarts() {
    $this->load->model('setting/setting');
	$this->load->model('extension/module/abandonedcarts');
	if(!$this->model_extension_module_abandonedcarts->checkDbTable('abandonedcarts_settings')){
		$abandonedCartsSettings = $this->model_setting_setting->getSetting('abandonedcarts', $this->config->get('config_store_id'));
	} else {
		$abandonedCartsSettings = $this->model_extension_module_abandonedcarts->getSetting('abandonedcarts', $this->config->get('config_store_id'));
	}
    if (isset($abandonedCartsSettings['abandonedcarts']['Enabled']) && $abandonedCartsSettings['abandonedcarts']['Enabled']=='yes') { 
        $ip = (!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '*HiddenIP*';
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        
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
        $cart = $this->cart->getProducts();
        $store_id = (int)$this->config->get('config_store_id');
        $cart = (!empty($cart)) ? $cart : '';
        
        $lastpage = "$_SERVER[REQUEST_URI]";
        $checker = $this->customer->getId();
        if (!empty($checker)) {
            $customer = array(
            'id'        => $this->customer->getId(), 
            'email'     => $this->customer->getEmail(),		
            'telephone' => $this->customer->getTelephone(),
            'firstname' => $this->customer->getFirstName(),
            'lastname'  => $this->customer->getLastName(),
            'language'  => $this->session->data['language'],
            'currency'  => $this->session->data['currency']
            );
        } 
        
        if (empty($exists->row)) {
            if (!empty($cart)) {
                if (empty($customer)) {
                    $customer = array(
                        'language' => $this->session->data['language'],
                        'currency' => $this->session->data['currency']
                    );
                }

                $cart = json_encode($cart);
                $customer = json_encode($customer);
                $this->db->query("INSERT INTO `" . DB_PREFIX . "abandonedcarts` SET `cart`='".$this->db->escape($cart)."', `customer_info`='".$this->db->escape($customer)."', `last_page`='".$this->db->escape($lastpage)."', `ip`='$ip', `date_created`=NOW(), `date_modified`=NOW(), `restore_id`='".$id."', `store_id`='".$store_id."'");
                $this->session->data['abandonedCart_ID'] = $id;
            } 
        } else {
            if (!empty($cart)) {
				/* === Remove Duplicate Entry*/
				$this->db->query("DELETE ab1 FROM " . DB_PREFIX . "abandonedcarts ab1 INNER JOIN " . DB_PREFIX . "abandonedcarts ab2 WHERE ab1.id < ab2.id AND ab1.restore_id = ab2.restore_id AND ab1.ordered = 0 AND ab2.ordered = 0");

                $cart = json_encode($cart);
                $this->db->query("UPDATE `" . DB_PREFIX . "abandonedcarts` SET `cart` = '".$this->db->escape($cart)."', `last_page`='".$this->db->escape($lastpage)."', `date_modified`=NOW(), `store_id` = '" . (int)$this->config->get('config_store_id') . "' WHERE `restore_id`='$id' AND `ordered`=0");
            }
            if (isset($customer)) {
                $customer = json_encode($customer);
                $this->db->query("UPDATE `" . DB_PREFIX . "abandonedcarts` SET `customer_info` = '".$this->db->escape($customer)."', `last_page`='".$this->db->escape($lastpage)."', `date_modified`=NOW(), `store_id` = '" . (int)$this->config->get('config_store_id') . "' WHERE `restore_id`='$id' AND `ordered`=0");
            }
        }
    }
}
/* AbandonedCarts - End */
			
	public function index() {
		$this->load->language('checkout/cart');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home'),
			'text' => $this->language->get('text_home')
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('checkout/cart'),
			'text' => $this->language->get('heading_title')
		);


                           if($this->config->get('module_marketplace_status')) {
                                $data['module_marketplace_status'] = $this->config->get('module_marketplace_status');
                                if(isset($this->session->data['sellerProducts'])) {
                                    $data['error_warning_seller_product'] = " Warning: Please remove ".trim($this->session->data['sellerProducts'],', ')." from cart to checkout!";
                                    unset($this->session->data['sellerProducts']);
                                } else {
                                    $data['error_warning_seller_product'] = false;
                                }
                            }
                              

                if($this->config->get('module_wk_split_cart_status')){
                  $this->load->model('checkout/wk_split_cart');
                  $seller = $this->model_checkout_wk_split_cart->getDetails();

                  if (count($seller) > 1) {

                      $data['split_status'] = true;
                      $this->load->controller('checkout/mpcart');
                      return;
                  }
                }
           
		if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}


                 $this->load->model('account/customerpartner');

                  $product_quantity_restriction = $this->model_account_customerpartner->getProductRestriction();
                  if ($this->config->get('module_marketplace_status') && $product_quantity_restriction) {
                    $data['error_warning'] = sprintf($this->language->get('error_product_quantity_restriction'),(int)$this->config->get('marketplace_product_quantity_restriction'), $product_quantity_restriction['name']);
                  }

                  if ($this->config->get('module_marketplace_status') && (int)$this->config->get('marketplace_min_cart_value') && (int)$this->config->get('marketplace_min_cart_value') > $this->cart->getTotal()) {
                   $data['error_warning'] = sprintf($this->language->get('error_min_cart_value'), $this->currency->format($this->config->get('marketplace_min_cart_value'), $this->session->data['currency']));
                 }
               
			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			$data['action'] = $this->url->link('checkout/cart/edit', '', true);

			if ($this->config->get('config_cart_weight')) {
				$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$data['weight'] = '';
			}

			$this->load->model('tool/image');
			$this->load->model('tool/upload');

			$data['products'] = array();

			$products = $this->cart->getProducts();
		   


                // Store Pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status') && isset($this->session->data['pickup_points']) && $this->session->data['pickup_points']) {

                    $pickup_point_error = $this->load->controller('mp_store_pickup/mp_store_pickup/checkPickupPointStock');

                    if ($pickup_point_error) {

                        $data['error_warning'] = $this->language->get('error_stock');
                    }
                }
                // Store Pickup module code ends here
                    
			foreach ($products as $product) {

                // Store pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status') && isset($this->session->data['pickup_points'])) {

                    $this->load->model('mp_store_pickup/mp_store_pickup');
                    $this->load->language('mp_store_pickup/mp_store_pickup');

                    $data['pickup_address_text'] = $this->language->get('text_pickup_address');

                    foreach ($this->session->data['pickup_points'] as $key1 => $pickup_point) {

                        if ($product['cart_id'] == $key1) {

                            $pickup_point_detail = $this->model_mp_store_pickup_mp_store_pickup->getPickupPoint($pickup_point['pickup_point_id']);

                            if ($pickup_point_detail) {

                                $pickup_address = explode('{space}', html_entity_decode($pickup_point_detail['address']));
                                $pickup_point_name = $pickup_point_detail['name'];
                            }
                        }
                    }
                }

                if (isset($pickup_point_error) && $pickup_point_error && array_key_exists($product['cart_id'], $pickup_point_error['cart_id'])) {

                    $product['stock'] = '';
                }
                // Store pickup module code ends here
                    
				$product_total = 0;

				foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
				} else {
					$image = '';
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {

               if(isset($product['commission_amount']) && $product['commission_amount']){
                 $product['price'] += $product['commission_amount'];
               }
             
					$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
					
					$price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}


              // gift code
              if ($this->config->get('module_marketplace_status') && $this->config->get('module_wk_crosssell_crosssell_status') && isset($product['gift']) && $product['gift']) {
                $price = html_entity_decode($this->config->get('wk_gift_gift_label')[$this->config->get('config_language_id')]);
              }
              // gift code ends here
            
				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year')
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}

				$data['products'][] = array(

                // store pickup code starts here
                'pickup_point_name' => isset($pickup_point_name) ? $pickup_point_name : '',
                'pickup_address' => isset($pickup_address) ? $pickup_address : '',
                // store pickup code starts here
                    
					'cart_id'   => $product['cart_id'],
					'thumb'     => $image,
					'name'      => $product['name'],
					'model'     => $product['model'],
					'stockstatus'=>$product['stock_status_id'],
					'option'    => $option_data,
					'recurring' => $recurring,
					'quantity'  => $product['quantity'],
					'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'     => $price,
					'total'     => $total,
					'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			// Gift Voucher
			$data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$data['vouchers'][] = array(
						'key'         => $key,
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
						'remove'      => $this->url->link('checkout/cart', 'remove=' . $key)
					);
				}
			}

			// Totals
			$this->load->model('setting/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;
			
			// Because __call can not keep var references so we put them into an array. 			
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						
						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}

			$data['totals'] = array();

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
				);
			}

			$data['continue'] = $this->url->link('common/home');

			$data['checkout'] = $this->url->link('checkout/checkout', '', true);

			$this->load->model('setting/extension');

			$data['modules'] = array();
			
			$files = glob(DIR_APPLICATION . '/controller/extension/total/*.php');

			if ($files) {
				foreach ($files as $file) {
					$result = $this->load->controller('extension/total/' . basename($file, '.php'));
					
					if ($result) {
						$data['modules'][] = $result;
					}
				}
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('checkout/cart', $data));
		} else {
			$data['text_error'] = $this->language->get('text_empty');
			
			$data['continue'] = $this->url->link('common/home');

			unset($this->session->data['success']);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}


			public function add_notify() {
		$this->load->language('checkout/cart');

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if (isset($this->request->post['quantity'])) {
				$quantity = (int)$this->request->post['quantity'];
			} else {
				$quantity = 1;
			}

			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();
			}

			$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}

			//HUNTBEE CODE ADDED FOR OUT OF STOCK FORM POPUP #START_OOSN
			if ($this->config->get('module_hb_oosn_status') && file_exists(DIR_APPLICATION . 'model/extension/module/hb_oosn.php')) {
		    	$this->load->model('extension/module/hb_oosn');
			    if (!$json){
			        $json_oosn = $this->model_extension_module_hb_oosn->product_notify_validation_2($product_id, $quantity, $option, $product_options);
			        if ($json_oosn) {
			            $json = $json_oosn;
			        }
			    }
			}
			//#END_OOSN
			
			if (isset($this->request->post['recurring_id'])) {
				$recurring_id = $this->request->post['recurring_id'];
			} else {
				$recurring_id = 0;
			}

			$recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

			if ($recurrings) {
				$recurring_ids = array();

				foreach ($recurrings as $recurring) {
					$recurring_ids[] = $recurring['recurring_id'];
				}

				if (!in_array($recurring_id, $recurring_ids)) {
					$json['error']['recurring'] = $this->language->get('error_recurring_required');
				}
			}


            // Store pickup module code starts here

            if ($this->config->get('module_mp_store_pickup_status') && isset($this->request->get['pickup_point_id']) && $this->request->get['pickup_point_id']) {

                if (!$json) {

                   $this->load->model('mp_store_pickup/mp_store_pickup');

                   $pickup_status = true;

                   $getPickupProduct = $this->model_mp_store_pickup_mp_store_pickup->getProduct($product_id, $option, $this->request->get['pickup_point_id']);

                   if ($getPickupProduct['quantity'] < $quantity) {

                      $pickup_status = false;
                   }

                   if ($option) {

                        foreach ($option as $key => $value1) {

                            $check_option_type = $this->model_mp_store_pickup_mp_store_pickup->checkProductOptionType($key);

                            if ($check_option_type) {

                                if (isset($getPickupProduct['options']) && $getPickupProduct['options']) {

                                    // if the option is the checkbox type
                                    if (is_array($value1)) {

                                        foreach ($value1 as $key => $value2_product_option_value_id) {
                                            if (array_key_exists($value2_product_option_value_id, $getPickupProduct['options'])) {

                                                if ($getPickupProduct['options'][$value2_product_option_value_id] < $quantity) {

                                                    $pickup_status = false;
                                                }
                                            }else{
                                                $pickup_status = false;
                                            }
                                        }
                                    }else{

                                        // if the option is select and radio type
                                        if (array_key_exists($value1, $getPickupProduct['options'])) {
                                            if ($getPickupProduct['options'][$value1] < $quantity) {

                                                $pickup_status = false;
                                            }
                                        }else{
                                            $pickup_status = false;
                                        }
                                    }

                                }else{
                                    $pickup_status = false;
                                }
                            }
                        }
                   }

                   if (!$pickup_status) {

                      $json['pickup_point_error'] = 'You do not have desired quantity in this pick up point';
                   }
                }
            }
            // Store pickup module code ends here
                    
			if (!$json) {
				$this->cart->add($this->request->post['product_id'], $quantity, $option, $recurring_id);

                //Store pickup module code starts here and first get the cart id from the db and save in pickup points session
                if ($this->config->get('module_mp_store_pickup_status') && isset($this->request->get['pickup_point_id']) && $this->request->get['pickup_point_id']) {
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$this->request->post['product_id'] . "' AND recurring_id = '" . (int)$recurring_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'")->row;
                    $this->session->data['pickup_points'][$query['cart_id']] = array();
                    $this->session->data['pickup_points'][$query['cart_id']] = array(
                        'pickup_point_id' => $this->request->get['pickup_point_id'],
                        'product_id'      => $this->request->post['product_id'],
                        'option'          => $option
                    );
                }
                //Store pickup module code ends here
                    

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

				// Unset all shipping and payment methods
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				// Totals
				$this->load->model('setting/extension');

				$totals = array();
				$taxes = $this->cart->getTaxes();
				$total = 0;
		
				// Because __call can not keep var references so we put them into an array. 			
				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total
				);

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$sort_order = array();

					$results = $this->model_setting_extension->getExtensions('total');

					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get('total_' . $result['code'] . '_status')) {
							$this->load->model('extension/total/' . $result['code']);

							// We have to put the totals in an array so that they pass by reference.
							$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
						}
					}

					$sort_order = array();

					foreach ($totals as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $totals);
				}

				$json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
		
	public function add() {
		$this->load->language('checkout/cart');
		
		$json = array();		

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];			
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if (isset($this->request->post['quantity'])) {
				$quantity = (int)$this->request->post['quantity'];
			} else {
				$quantity = 1;
			}

			if (isset($this->request->post['option'])) {
				$option = array_filter($this->request->post['option']);
			} else {
				$option = array();
			}

			$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}


			//HUNTBEE CODE ADDED FOR OUT OF STOCK FORM POPUP #START_OOSN
			if ($this->config->get('module_hb_oosn_status') && file_exists(DIR_APPLICATION . 'model/extension/module/hb_oosn.php')) {
		    	$this->load->model('extension/module/hb_oosn');
			    if (!$json){
			        $json_oosn = $this->model_extension_module_hb_oosn->product_notify_validation($product_id, $quantity, $option, $product_options);
			        if ($json_oosn) {
			            $json = $json_oosn;
			        }
			    }
			}
			//#END_OOSN
		
			if (isset($this->request->post['recurring_id'])) {
				$recurring_id = $this->request->post['recurring_id'];
			} else {
				$recurring_id = 0;
			}

			$recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

			if ($recurrings) {
				$recurring_ids = array();

				foreach ($recurrings as $recurring) {
					$recurring_ids[] = $recurring['recurring_id'];
				}

				if (!in_array($recurring_id, $recurring_ids)) {
					$json['error']['recurring'] = $this->language->get('error_recurring_required');
				}
			}



        if ($this->config->get('module_marketplace_status') && !isset($json['error']['option'])) {
          if (isset($this->session->data['error_order_limit']) && $this->session->data['error_order_limit']) {
            $json['success']  = $this->session->data['error_order_limit'];
            $json['error']    = $this->session->data['common_cart_total'];
          }
       }

      

            // Store pickup module code starts here

            if ($this->config->get('module_mp_store_pickup_status') && isset($this->request->get['pickup_point_id']) && $this->request->get['pickup_point_id']) {

                if (!$json) {

                   $this->load->model('mp_store_pickup/mp_store_pickup');

                   $pickup_status = true;

                   $getPickupProduct = $this->model_mp_store_pickup_mp_store_pickup->getProduct($product_id, $option, $this->request->get['pickup_point_id']);

                   if ($getPickupProduct['quantity'] < $quantity) {

                      $pickup_status = false;
                   }

                   if ($option) {

                        foreach ($option as $key => $value1) {

                            $check_option_type = $this->model_mp_store_pickup_mp_store_pickup->checkProductOptionType($key);

                            if ($check_option_type) {

                                if (isset($getPickupProduct['options']) && $getPickupProduct['options']) {

                                    // if the option is the checkbox type
                                    if (is_array($value1)) {

                                        foreach ($value1 as $key => $value2_product_option_value_id) {
                                            if (array_key_exists($value2_product_option_value_id, $getPickupProduct['options'])) {

                                                if ($getPickupProduct['options'][$value2_product_option_value_id] < $quantity) {

                                                    $pickup_status = false;
                                                }
                                            }else{
                                                $pickup_status = false;
                                            }
                                        }
                                    }else{

                                        // if the option is select and radio type
                                        if (array_key_exists($value1, $getPickupProduct['options'])) {
                                            if ($getPickupProduct['options'][$value1] < $quantity) {

                                                $pickup_status = false;
                                            }
                                        }else{
                                            $pickup_status = false;
                                        }
                                    }

                                }else{
                                    $pickup_status = false;
                                }
                            }
                        }
                   }

                   if (!$pickup_status) {

                      $json['pickup_point_error'] = 'You do not have desired quantity in this pick up point';
                   }
                }
            }
            // Store pickup module code ends here
                    
			if (!$json) {
				$this->cart->add($this->request->post['product_id'], $quantity, $option, $recurring_id);

                //Store pickup module code starts here and first get the cart id from the db and save in pickup points session
                if ($this->config->get('module_mp_store_pickup_status') && isset($this->request->get['pickup_point_id']) && $this->request->get['pickup_point_id']) {
                    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "cart WHERE customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$this->request->post['product_id'] . "' AND recurring_id = '" . (int)$recurring_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'")->row;
                    $this->session->data['pickup_points'][$query['cart_id']] = array();
                    $this->session->data['pickup_points'][$query['cart_id']] = array(
                        'pickup_point_id' => $this->request->get['pickup_point_id'],
                        'product_id'      => $this->request->post['product_id'],
                        'option'          => $option
                    );
                }
                //Store pickup module code ends here
                    

				$json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

$this->register_abandonedCarts();
			

            if (defined('JOURNAL3_ACTIVE')) {
                if (\Journal3\Utils\Arr::get($json, 'error.option')) {
                    $json['options_popup'] = $this->journal3->settings->get('globalOptionsPopupStatus', true);
                }

                $json['notification'] = $this->journal3->loadController('journal3/notification/cart', array('product_info' => $product_info, 'message' => $json['success']));
            }
            

				// Unset all shipping and payment methods

                if (defined('JOURNAL3_ACTIVE') && $this->journal3->settings->get('activeCheckout') === 'journal') {
                    $this->load->model('journal3/checkout');
                    $this->model_journal3_checkout->setCheckoutId();
                }
            
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				// Totals
				$this->load->model('setting/extension');

				$totals = array();
				$taxes = $this->cart->getTaxes();
				$total = 0;
		
				// Because __call can not keep var references so we put them into an array. 			
				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total
				);

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$sort_order = array();

					$results = $this->model_setting_extension->getExtensions('total');

					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get('total_' . $result['code'] . '_status')) {
							$this->load->model('extension/total/' . $result['code']);

							// We have to put the totals in an array so that they pass by reference.
							$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
						}
					}

					$sort_order = array();

					foreach ($totals as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $totals);
				}

				$json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

            if (defined('JOURNAL3_ACTIVE')) {
                $json['items_count'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
                $json['items_price'] = $this->currency->format($total, $this->session->data['currency']);
                switch ($this->journal3->settings->get('addToCartAction')) {
                    case 'redirect_cart':
                        $json['redirect'] = str_replace('&amp;', '&', $this->url->link('checkout/cart'));
                        break;

                    case 'redirect_checkout':
                        $json['redirect'] = str_replace('&amp;', '&', $this->url->link('checkout/checkout'));
                        break;
                }
            }
            
			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));

            if (defined('JOURNAL3_ACTIVE')) {


        if ($this->config->get('module_marketplace_status')) {
    			 if (isset($this->session->data['error_order_limit']) && $this->session->data['error_order_limit']) {
             $json['success'] = $this->session->data['error_order_limit'];
    				 $json['total']    = $this->session->data['common_cart_total'];
    			  	unset($json['redirect']);
    					unset($this->session->data['common_cart_total']);
    					unset($this->session->data['error_order_limit']);
    			 }
    		}

      
                $json['options_popup'] = $this->journal3->settings->get('globalOptionsPopupStatus', true);
            }
            
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));		
	}

	public function edit() {
		$this->load->language('checkout/cart');

		$json = array();

		// Update
		if (!empty($this->request->post['quantity'])) {

            // gift code
            if ($this->config->get('module_marketplace_status') && $this->config->get('module_wk_crosssell_crosssell_status')) {
            foreach ($this->request->post['quantity'] as $post_key => $post_quantity) {
              if (isset($this->session->data['gifts'][$post_key])) {
                $total_quantity = $this->session->data['gifts'][$post_key] + $post_quantity;
                $this->request->post['quantity'][$post_key] = $total_quantity;
              }
            }
            }
            // gift code ends here
            
			foreach ($this->request->post['quantity'] as $key => $value) {
				$this->cart->update($key, $value);
			}

			$this->session->data['success'] = $this->language->get('text_remove');


                if (defined('JOURNAL3_ACTIVE') && $this->journal3->settings->get('activeCheckout') === 'journal') {
                    $this->load->model('journal3/checkout');
                    $this->model_journal3_checkout->setCheckoutId();
                }
            
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);


$this->update_abandonedCarts();
			
			$this->response->redirect($this->url->link('checkout/cart'));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->load->language('checkout/cart');

		$json = array();
           
		// Remove
		if (isset($this->request->post['key'])) {
		 
			
            // gift code

            if (isset($this->session->data['gifts'][$this->request->post['key']])) {
                $this->cart->update($this->request->post['key'], $this->session->data['gifts'][$this->request->post['key']]);
                unset($this->session->data['gifts'][$this->request->post['key']]);
            } else {

			if (version_compare(VERSION,'2.1.0.1','>=' )) {
			    $key = $this->request->post['key'];
				$cart_query = $this->db->query("SELECT * FROM ".DB_PREFIX."cart WHERE `cart_id` = '".(int)$key."' LIMIT 1");
				if ($cart_query->row) {
				    $json['product_id'] = $cart_query->row['product_id'];
				}else{
				    $json['product_id'] = 0;
				}
			}	
			
                $this->cart->remove($this->request->post['key']);

$this->update_abandonedCarts();
			
            }

            // gift code ends here
            

			unset($this->session->data['vouchers'][$this->request->post['key']]);

			$json['success'] = $this->language->get('text_remove');


                if (defined('JOURNAL3_ACTIVE') && $this->journal3->settings->get('activeCheckout') === 'journal') {
                    $this->load->model('journal3/checkout');
                    $this->model_journal3_checkout->setCheckoutId();
                }
            
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			// Totals
			$this->load->model('setting/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array. 			
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);

						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
				
			        $key = $this->request->post['key'];
                    $cart_query = $this->db->query("SELECT * FROM ".DB_PREFIX."cart WHERE `cart_id` = '".(int)$key."' ");
                    if ($cart_query->row) {
                        $json['product_id'] = $cart_query->row['product_id'];
                        $json['option'] = json_decode($cart_query->row['option'], true); // Convert JSON string to array
                      //  echo json_encode($json['option']); // Convert array to JSON and output
                        $cart_event_id = $json['option']['3']; 
                        $cart_event_payment_code = $json['option']['27']; 
                        $concatenated_values = array($cart_event_id, $cart_event_payment_code);
                        $this->db->query("DELETE FROM `" . DB_PREFIX . "event_registration` WHERE  `event_payment_code` = '" . $this->db->escape($cart_event_payment_code) . "' AND `events_id` = '".(int)$cart_event_id."'");
                    }
    		
    		       
    			}

			$json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

            if (defined('JOURNAL3_ACTIVE')) {
                $json['items_count'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
                $json['items_price'] = $this->currency->format($total, $this->session->data['currency']);
                switch ($this->journal3->settings->get('addToCartAction')) {
                    case 'redirect_cart':
                        $json['redirect'] = str_replace('&amp;', '&', $this->url->link('checkout/cart'));
                        break;

                    case 'redirect_checkout':
                        $json['redirect'] = str_replace('&amp;', '&', $this->url->link('checkout/checkout'));
                        break;
                }
            }
            
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
