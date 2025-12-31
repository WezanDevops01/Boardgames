<?php
class ControllerSaleOrder extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');

		$this->getList();
	}


				public function edit_delivery_list() {
					$this->document->setTitle($this->language->get('heading_title')); 
					$this->language->load('sale/order');

					$this->load->model('sale/order'); 

					if (isset($this->request->post['selected'])) {
						$url = '';

						$this->session->data['success'] = $this->language->get('text_success');

					
						foreach ($this->request->post['selected'] as $order_product_id) {
							
						$delivery_status_id_str = $order_product_id.'_status_id';
						$delivery_status_id = $this->request->post[$delivery_status_id_str];
						$product_id_str = $order_product_id.'_product_id';
						$product_id = $this->request->post[$product_id_str];
						$delivery_status_name_str = 'delivery_status_name_'.$delivery_status_id;
						$deliver_name = $this->request->post[$delivery_status_name_str];
					   
						$el_data = array('delivery_status_id' => $delivery_status_id);
						
						$this->load->model('catalog/product');
						
						$product = $this->model_catalog_product->getProduct($product_id);		

						$this->model_sale_order->editDeliveryList($order_product_id, $el_data,$this->request->post['order_dstatus_id'],$this->request->post['order_id'], $product['name'], $deliver_name);
					}
						$this->response->redirect($this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $this->request->post['order_id'] . $url, true));		
						
					}

					//$this->info();
				}
			
	public function add() {
		$this->load->language('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');

		$this->getForm();
	}

	public function edit() {
		$this->load->language('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('sale/order');

		$this->getForm();
	}
	
	public function delete() {
		$this->load->language('sale/order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->session->data['success'] = $this->language->get('text_success');

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
	
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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

		$this->response->redirect($this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true));
	}
	
	 public function customer_request() {
            // Load language file
            $this->load->language('customer/customer_request');
    
            // Set the title of the document
            $this->document->setTitle($this->language->get('heading_title'));
    
            // Load the necessary models
            $this->load->model('customer/customer_request');
            $this->load->model('sale/order');
            
            $this->getRequestForm();
            
        }
         public function insert() {
            $this->load->language('customer/customer_request');
    
            if ($this->request->server['REQUEST_METHOD'] == 'POST') {
                $formData = $this->request->post;
                
                 // echo "<pre>"; print_r($formData); die;
                $this->load->model('customer/customer_request');
                $this->model_customer_customer_request->addCustomerRequest($formData);
                
                $this->session->data['success'] = $this->language->get('text_success');
    
                // Uncomment the following line to redirect after successful insertion
                 $this->response->redirect($this->url->link('customer/customer_request', 'user_token=' . $this->session->data['user_token'], true));
            }
    
            $this->getForm();
        }
        
        protected function getRequestForm() {
            $data['text_form'] = !isset($this->request->get['customer_request_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
    
            if (isset($this->error['warning'])) {
                $data['error_warning'] = $this->error['warning'];
            } else {
                $data['error_warning'] = '';
            }
    
            $data['breadcrumbs'] = array();
    
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            );
    
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('customer/customer_request', 'user_token=' . $this->session->data['user_token'], true)
            );
    
            $data['action'] = $this->url->link('sale/order/insert', 'user_token=' . $this->session->data['user_token'], true);
    
            $data['cancel'] = $this->url->link('customer/customer_request', 'user_token=' . $this->session->data['user_token'], true);
    
            $data['user_token'] = $this->session->data['user_token'];
    
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            
            // Define the order ID
           // $order_id = 10784; 
            $order_id = $this->request->get['order_id'];
           
            $results = $this->model_sale_order->getRequestCustomerDetails($order_id);
            $data['customer_name'] = $results['fullname'];
            $data['customer_email'] = $results['email'];
            $data['order_id'] = $order_id; 
    
            $this->response->setOutput($this->load->view('sale/customer_request_form', $data));
        }
			
	protected function getList() {
		if (isset($this->request->get['filter_order_id'])) {
			$filter_order_id = $this->request->get['filter_order_id'];
		} else {
			$filter_order_id = '';
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}

		if (isset($this->request->get['filter_order_status'])) {
			$filter_order_status = $this->request->get['filter_order_status'];
		} else {
			$filter_order_status = '';
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = '';
		}
		
		if (isset($this->request->get['filter_total'])) {
			$filter_total = $this->request->get['filter_total'];
		} else {
			$filter_total = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$filter_date_modified = $this->request->get['filter_date_modified'];
		} else {
			$filter_date_modified = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'o.order_id';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
	
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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
			'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['invoice'] = $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'], true);
		$data['shipping'] = $this->url->link('sale/order/shipping', 'user_token=' . $this->session->data['user_token'], true);
		$data['add'] = $this->url->link('sale/order/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = str_replace('&amp;', '&', $this->url->link('sale/order/delete', 'user_token=' . $this->session->data['user_token'] . $url, true));

		$data['orders'] = array();

		$filter_data = array(
			'filter_order_id'        => $filter_order_id,
			'filter_customer'	     => $filter_customer,
			'filter_order_status'    => $filter_order_status,
			'filter_order_status_id' => $filter_order_status_id,
			'filter_total'           => $filter_total,
			'filter_date_added'      => $filter_date_added,
			'filter_date_modified'   => $filter_date_modified,
			'sort'                   => $sort,
			'order'                  => $order,
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);


                $this->load->model('setting/setting');
                $extension_settings = $this->model_setting_setting->getSetting('hb_pdf', $this->config->get('config_store_id'));
                $hb_pdf_iv_secured_folder = $extension_settings['hb_pdf_iv_secured_folder'];
                $hb_pdf_sp_secured_folder = $extension_settings['hb_pdf_sp_secured_folder'];
                $pdf_auth = md5($hb_pdf_iv_secured_folder);
                $packaging_auth = md5($hb_pdf_sp_secured_folder);
			
		$order_total = $this->model_sale_order->getTotalOrders($filter_data);

$this->load->model('extension/hbapps/order_shipment');
		$results = $this->model_sale_order->getOrders($filter_data);

		foreach ($results as $result) {
			$data['orders'][] = array(
				'order_id'      => $result['order_id'],
				'customer'      => $result['customer'],
				'order_status'  => $result['order_status'] ? $result['order_status'] : $this->language->get('text_missing'),
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),

                    'is_all_shipped'          => $this->model_extension_hbapps_order_shipment->is_all_shipped($result['order_id']),
    			
				'shipping_code' => $result['shipping_code'],

                'pdf'          => HTTPS_CATALOG.'index.php?route=extension/module/hbpdfinvoice/createinvoice&order_id='.$result['order_id'].'&authvalue='.$pdf_auth.'&download=admin',
                'packaging'    => HTTPS_CATALOG.'index.php?route=extension/module/hbpdfinvoice/create_packaging&order_id='.$result['order_id'].'&authvalue='.$packaging_auth.'&download=admin',
				'hb_pdf_sp_status' => $this->config->get('hb_pdf_sp_status')? true : false,
			
				'view'          => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url, true),
				'edit'          => $this->url->link('sale/order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'] . $url, true)
			);
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

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_order'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.order_id' . $url, true);
		$data['sort_customer'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
		$data['sort_status'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=order_status' . $url, true);
		$data['sort_total'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.total' . $url, true);
		$data['sort_date_added'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_added' . $url, true);
		$data['sort_date_modified'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_modified' . $url, true);
	    $data['customer_request'] = $this->url->link('sale/order/customer_request', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$data['filter_order_id'] = $filter_order_id;
		$data['filter_customer'] = $filter_customer;
		$data['filter_order_status'] = $filter_order_status;
		$data['filter_order_status_id'] = $filter_order_status_id;
		$data['filter_total'] = $filter_total;
		$data['filter_date_added'] = $filter_date_added;
		$data['filter_date_modified'] = $filter_date_modified;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		// API login
		$data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
		
		// API login
		$this->load->model('user/api');

		$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

		if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
			$session = new Session($this->config->get('session_engine'), $this->registry);
			
			$session->start();
					
			$this->model_user_api->deleteApiSessionBySessionId($session->getId());
			
			$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
			
			$session->data['api_id'] = $api_info['api_id'];

			$data['api_token'] = $session->getId();
		} else {
			$data['api_token'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/order_list', $data));
	}
		
	public function getForm() {
		$data['text_form'] = !isset($this->request->get['order_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$url = '';

		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
		}
		
		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}
			
		if (isset($this->request->get['filter_total'])) {
			$url .= '&filter_total=' . $this->request->get['filter_total'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['filter_date_modified'])) {
			$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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
			'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['cancel'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['user_token'] = $this->session->data['user_token'];

		$data['adv_ext_name'] = $this->language->get('adv_ext_name');
		$data['adv_ext_short_name'] = 'adv_profit_module';
		$data['adv_ext_version'] = $this->language->get('adv_ext_version');		
		$data['adv_ext_url'] = 'http://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=16601';
		$data['entry_extra_cost'] = $this->language->get('entry_extra_cost');				
		$data['entry_shipping_cost'] = $this->language->get('entry_shipping_cost');
		$data['entry_payment_cost'] = $this->language->get('entry_payment_cost');				
		$data['column_cost'] = $this->language->get('column_cost');
		$data['text_save'] = $this->language->get('text_save');
		$data['text_close'] = $this->language->get('text_close');
		$data['help_edit_cost'] = $this->language->get('help_edit_cost');
            

		if (isset($this->request->get['order_id'])) {
			$order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);
		}

		if (!empty($order_info)) {
			$data['order_id'] = (int)$this->request->get['order_id'];
			$data['store_id'] = $order_info['store_id'];
			$data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

			$data['customer'] = $order_info['customer'];
			$data['customer_id'] = $order_info['customer_id'];
			$data['customer_group_id'] = $order_info['customer_group_id'];
			$data['firstname'] = $order_info['firstname'];
			$data['lastname'] = $order_info['lastname'];
			$data['email'] = $order_info['email'];
			$data['telephone'] = $order_info['telephone'];
			$data['account_custom_field'] = $order_info['custom_field'];

			$this->load->model('customer/customer');

			$data['addresses'] = $this->model_customer_customer->getAddresses($order_info['customer_id']);

			$data['payment_firstname'] = $order_info['payment_firstname'];
			$data['payment_lastname'] = $order_info['payment_lastname'];
			$data['payment_company'] = $order_info['payment_company'];
			$data['payment_address_1'] = $order_info['payment_address_1'];
			$data['payment_address_2'] = $order_info['payment_address_2'];
			$data['payment_city'] = $order_info['payment_city'];
			$data['payment_postcode'] = $order_info['payment_postcode'];
			$data['payment_country_id'] = $order_info['payment_country_id'];
			$data['payment_zone_id'] = $order_info['payment_zone_id'];
			$data['payment_custom_field'] = $order_info['payment_custom_field'];
			$data['payment_method'] = $order_info['payment_method'];
			$data['payment_code'] = $order_info['payment_code'];

			$data['shipping_firstname'] = $order_info['shipping_firstname'];
			$data['shipping_lastname'] = $order_info['shipping_lastname'];
			$data['shipping_company'] = $order_info['shipping_company'];
			$data['shipping_address_1'] = $order_info['shipping_address_1'];
			$data['shipping_address_2'] = $order_info['shipping_address_2'];
			$data['shipping_city'] = $order_info['shipping_city'];
			$data['shipping_postcode'] = $order_info['shipping_postcode'];
			$data['shipping_country_id'] = $order_info['shipping_country_id'];
			$data['shipping_zone_id'] = $order_info['shipping_zone_id'];
			$data['shipping_custom_field'] = $order_info['shipping_custom_field'];
			$data['shipping_method'] = $order_info['shipping_method'];

                if ($this->config->get('shipping_canadapost_status')) {
					 $this->load->model('extension/shipping/canadapost');
                     $data['shipping_code'] = $order_info['shipping_code'];
                }
                
			$data['shipping_code'] = $order_info['shipping_code'];

			$data['payment_cost'] = $order_info['payment_cost'];
			$data['shipping_cost'] = $order_info['shipping_cost'];
			$data['extra_cost'] = $order_info['extra_cost'];
            

			// Products
			$data['order_products'] = array();

			$products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
			
			foreach ($products as $product) {

        /**
         * Marketplace code starts here
         */
        if ($this->config->get('module_marketplace_status')) {
            $seller_details = $this->db->query("SELECT c.customer_id,CONCAT(c.firstname,' ',c.lastname) name,c.email FROM ".DB_PREFIX."customerpartner_to_product c2p LEFT JOIN ".DB_PREFIX."customer c ON (c2p.customer_id = c.customer_id) WHERE c2p.product_id = '".(int)$product['product_id']."'")->row;

            if($seller_details AND isset($seller_details['name']) AND $seller_details['name'])
              $product['name'] = $product['name'].' by Seller <b>'.$seller_details['name'].'</b>';
         }
        /**
         * Marketplace code ends here
         */

				
				$data['order_products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']),
					'quantity'   => $product['quantity'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'reward'     => $product['reward'],
					'weight_kg'        => $product['weight_kg'],
					'size_l'           => $product['size_l'],
					'size_w'           => $product['size_w'],
					'size_h'           => $product['size_h'],
					'href'             => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $order_product['product_id'], true)
				);
			}


			// Vouchers
			$data['order_vouchers'] = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);

			$data['coupon'] = '';
			$data['voucher'] = '';
			$data['reward'] = '';

			$data['order_totals'] = array();

			$order_totals = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);

			foreach ($order_totals as $order_total) {
				// If coupon, voucher or reward points
				$start = strpos($order_total['title'], '(') + 1;
				$end = strrpos($order_total['title'], ')');

				if ($start && $end) {
					$data[$order_total['code']] = substr($order_total['title'], $start, $end - $start);
				}
			}

			$data['order_status_id'] = $order_info['order_status_id'];
			$data['comment'] = $order_info['comment'];
			$data['affiliate_id'] = $order_info['affiliate_id'];
			$data['affiliate'] = $order_info['affiliate_firstname'] . ' ' . $order_info['affiliate_lastname'];
			$data['currency_code'] = $order_info['currency_code'];
		} else {
			$data['order_id'] = 0;
			$data['store_id'] = 0;
			$data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
			
			$data['customer'] = '';
			$data['customer_id'] = '';
			$data['customer_group_id'] = $this->config->get('config_customer_group_id');
			$data['firstname'] = '';
			$data['lastname'] = '';
			$data['email'] = '';
			$data['telephone'] = '';
			$data['customer_custom_field'] = array();

			$data['addresses'] = array();

			$data['payment_cost'] = '0.0000';
			$data['shipping_cost'] = '0.0000';
			$data['extra_cost'] = '0.0000';	
            

			$data['payment_firstname'] = '';
			$data['payment_lastname'] = '';
			$data['payment_company'] = '';
			$data['payment_address_1'] = '';
			$data['payment_address_2'] = '';
			$data['payment_city'] = '';
			$data['payment_postcode'] = '';
			$data['payment_country_id'] = '';
			$data['payment_zone_id'] = '';
			$data['payment_custom_field'] = array();
			$data['payment_method'] = '';
			$data['payment_code'] = '';

			$data['shipping_firstname'] = '';
			$data['shipping_lastname'] = '';
			$data['shipping_company'] = '';
			$data['shipping_address_1'] = '';
			$data['shipping_address_2'] = '';
			$data['shipping_city'] = '';
			$data['shipping_postcode'] = '';
			$data['shipping_country_id'] = '';
			$data['shipping_zone_id'] = '';
			$data['shipping_custom_field'] = array();
			$data['shipping_method'] = '';
			$data['shipping_code'] = '';

			$data['order_products'] = array();
			$data['order_vouchers'] = array();
			$data['order_totals'] = array();

			$data['order_status_id'] = $this->config->get('config_order_status_id');
			$data['comment'] = '';
			$data['affiliate_id'] = '';
			$data['affiliate'] = '';
			$data['currency_code'] = $this->config->get('config_currency');

			$data['coupon'] = '';
			$data['voucher'] = '';
			$data['reward'] = '';
		}

		// Stores
		$this->load->model('setting/store');

		$data['stores'] = array();

		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);

		$results = $this->model_setting_store->getStores();

		foreach ($results as $result) {
			$data['stores'][] = array(
				'store_id' => $result['store_id'],
				'name'     => $result['name']
			);
		}

		// Customer Groups
		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		// Custom Fields
		$this->load->model('customer/custom_field');
		$this->load->model('tool/upload');

		$data['custom_fields'] = array();

		$custom_field_locations = array(
			'account_custom_field',
			'payment_custom_field',
			'shipping_custom_field'
		);

		$filter_data = array(
			'sort'  => 'cf.sort_order',
			'order' => 'ASC'
		);

		$custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);

		foreach ($custom_fields as $custom_field) {
			$data['custom_fields'][] = array(
				'custom_field_id'    => $custom_field['custom_field_id'],
				'custom_field_value' => $this->model_customer_custom_field->getCustomFieldValues($custom_field['custom_field_id']),
				'name'               => $custom_field['name'],
				'value'              => $custom_field['value'],
				'type'               => $custom_field['type'],
				'location'           => $custom_field['location'],
				'sort_order'         => $custom_field['sort_order']
			);

			if($custom_field['type'] == 'file') {
				foreach($custom_field_locations as $location) {
					if(isset($data[$location][$custom_field['custom_field_id']])) {
						$code = $data[$location][$custom_field['custom_field_id']];

						$upload_result = $this->model_tool_upload->getUploadByCode($code);

						$data[$location][$custom_field['custom_field_id']] = array();
						if($upload_result) {
							$data[$location][$custom_field['custom_field_id']]['name'] = $upload_result['name'];
							$data[$location][$custom_field['custom_field_id']]['code'] = $upload_result['code'];
						} else {
							$data[$location][$custom_field['custom_field_id']]['name'] = "";
							$data[$location][$custom_field['custom_field_id']]['code'] = $code;
						}
					}
				}
			}
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();

		$this->load->model('localisation/currency');

		$data['currencies'] = $this->model_localisation_currency->getCurrencies();

		$data['voucher_min'] = $this->config->get('config_voucher_min');

		$this->load->model('sale/voucher_theme');

		$data['voucher_themes'] = $this->model_sale_voucher_theme->getVoucherThemes();

		// API login
		$data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
		
		// API login
		$this->load->model('user/api');

		$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

		if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
			$session = new Session($this->config->get('session_engine'), $this->registry);
			
			$session->start();
					
			$this->model_user_api->deleteApiSessionBySessionId($session->getId());
			
			$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
			
			$session->data['api_id'] = $api_info['api_id'];

			$data['api_token'] = $session->getId();
		} else {
			$data['api_token'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('sale/order_form', $data));
	}
	
public function deleteLinkOrder() {
    if (isset($this->request->post['linked_order_ids'])) {
        $this->response->addHeader('Content-Type: application/json');
        $parent_order_id = $this->request->post['order_id'];
        $linked_order_ids = explode(',', $this->request->post['linked_order_ids']);
        
        $success = true;
        $message = '';
        
        if (!empty($linked_order_ids)) {
            $this->load->model('sale/order');
            foreach ($linked_order_ids as $linked_order_id) {
                $this->model_sale_order->RemoveLinkedOrder($parent_order_id, trim($linked_order_id));
            }
            $message = 'Linked orders removed successfully.';
        } else {
            $success = false;
            $message = 'Please enter order IDs.';
        }
        
        $data = array(
            'success' => $success,
            'message' => $message
        );
        
        $this->response->setOutput(json_encode($data));
        return;
    }
}



				public function orderdetail_status_edit() {
					$order_product_id= $this->request->get['product_id'];
					$order_status_id = $this->request->get['status_id'];
					
					$sql = "UPDATE `oc_order_product` SET `delivery_status_id` = '". $order_status_id ."' WHERE `order_product_id` = '". $order_product_id."'" ;
					
					$this->db->query($sql);
				}
			


        /**
   * MP canada post modification code ends here
   */

      public function getServices($country, $service) {

        $username = $this->config->get('shipping_wk_canadapost_api_key');
        $password = $this->config->get('shipping_wk_canadapost_api_password');

      // REST URL
      $service_url = 'https://ct.soa-gw.canadapost.ca/rs/ship/service/'.$service.'?country='.$country.'';

      $curl = curl_init($service_url); // Create REST Request
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($curl, CURLOPT_USERPWD, $username . ':' . $password);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept:application/vnd.cpc.ship.rate-v3+xml'));
      $cpcurl_response = curl_exec($curl); // Execute REST Request

           $responseCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
      curl_close($curl);

      if($responseCode == 200) {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->loadXml($cpcurl_response);
            $options = $dom->getElementsByTagName('options');
            foreach ($options as $event) {
            $occurrences = $event->getElementsByTagName('option');
            foreach ($occurrences as $occurrence) {
               $data['code'][] = $occurrence->getElementsByTagName('option-code')->item(0)->nodeValue;
               $data['qualifier'][] = $occurrence->getElementsByTagName('qualifier-required')->item(0)->nodeValue;
            }
        }

        for($i =0;$i<count($data['code']); $i++){
          if($data['qualifier'][$i]  == 'false' ) {
            $code = $data['code'][$i];
          }
        }

        if(isset($code)) {
         return  $code;
        }else {
          return 0;
        }
          } else {
            return 0;
          }
      }
   /**
   * MP canada post modification code ends here
    */
      
	public function info() {

		$data['prm_access_permission'] = $this->user->hasPermission('access', 'extension/module/adv_profit_module');
		$data['modify_permission'] = $this->user->hasPermission('modify', 'sale/order');	
            
		$this->load->model('sale/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}
		
		$data['highlight_payment_address'] = $this->model_sale_order->hasPendingRequest($order_id);

		$order_info = $this->model_sale_order->getOrder($order_id);
		$data['global_weight_kg'] = $order_info['global_weight_kg'];
		$data['global_size_l'] = $order_info['global_size_l'];
		$data['global_size_w'] = $order_info['global_size_w'];
		$data['global_size_h'] = $order_info['global_size_h'];
		
			if (isset($this->request->post['linked_order_ids'])) {
                $this->response->addHeader('Content-Type: application/json');
                $parent_order_id = $this->request->get['order_id'];
                $linked_order_ids = explode(',', $this->request->post['linked_order_ids']);
                $success = true;
                $message = '';
            
                foreach ($linked_order_ids as $linked_order_id) {
                    $linked_order_id = trim($linked_order_id); // Ensure no extra spaces
                    if (!empty($linked_order_id)) {
                        if (is_numeric($linked_order_id)) {
                            $result = $this->model_sale_order->addLinkedOrder($parent_order_id, (int)$linked_order_id);
                            if (!$result) {
                                $success = false;
                                $message .= "Order ID " . $linked_order_id . " already linked or couldn't be added. ";
                            }
                        } else {
                            $success = false;
                            $message .= "Order ID " . $linked_order_id . " is not a valid number. ";
                        }
                    } else {
                        $success = false;
                        $message .= "Please Enter order ID . ";
                    }
                }
            
                $json = array(
                    'success' => $success,
                    'message' => $message ?: 'All orders linked successfully'
                );
            
                $this->response->setOutput(json_encode($json));
                return;
            }

		if ($order_info) {
			$this->load->language('sale/order');

			$this->document->setTitle($this->language->get('heading_title'));

			$data['text_ip_add'] = sprintf($this->language->get('text_ip_add'), $this->request->server['REMOTE_ADDR']);
			$data['text_order'] = sprintf($this->language->get('text_order'), $this->request->get['order_id']);
			
			$linked_orders = $this->model_sale_order->getLinkedOrders($order_id);
            $list_linked_order_ids = array_column($linked_orders, 'linked_order_id');
            
            $parent_orders = $this->model_sale_order->getParentOrders($order_id);
            $list_parent_orders = array_column($parent_orders, 'order_id');
            
            if (!empty($parent_orders)) {
                $listAllOrder = array_merge($list_parent_orders, $list_linked_order_ids);
                $data['linked_order_ids'] = $listAllOrder;
            } else {
                $data['linked_order_ids'] = $list_linked_order_ids;
            }

			$url = '';

			if (isset($this->request->get['filter_order_id'])) {
				$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
			}

			if (isset($this->request->get['filter_customer'])) {
				$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_order_status'])) {
				$url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
			}
			
			if (isset($this->request->get['filter_order_status_id'])) {
				$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
			}
			
			if (isset($this->request->get['filter_total'])) {
				$url .= '&filter_total=' . $this->request->get['filter_total'];
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
			}

			if (isset($this->request->get['filter_date_modified'])) {
				$url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
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
				'href' => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true)
			);


				$data['edit_delivery_list'] = $this->url->link('sale/order/edit_delivery_list', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
			
			$data['shipping'] = $this->url->link('sale/order/shipping', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
			$data['invoice'] = $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
			$data['edit'] = $this->url->link('sale/order/edit', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$this->request->get['order_id'], true);
			$data['cancel'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . $url, true);

			$data['user_token'] = $this->session->data['user_token'];

			$data['order_id'] = (int)$this->request->get['order_id'];

			$data['store_id'] = $order_info['store_id'];
			$data['store_name'] = $order_info['store_name'];
			
			if ($order_info['store_id'] == 0) {
				$data['store_url'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
			} else {
				$data['store_url'] = $order_info['store_url'];
			}

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			$data['firstname'] = $order_info['firstname'];
			$data['lastname'] = $order_info['lastname'];

			if ($order_info['customer_id']) {
				$data['customer'] = $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $order_info['customer_id'], true);
			} else {
				$data['customer'] = '';
			}

			$this->load->model('customer/customer_group');

			$customer_group_info = $this->model_customer_customer_group->getCustomerGroup($order_info['customer_group_id']);

				$this->load->model('setting/setting');
                $extension_settings = $this->model_setting_setting->getSetting('hb_pdf', $order_info['store_id']);
                $hb_pdf_iv_secured_folder = $extension_settings['hb_pdf_iv_secured_folder'];
                $hb_pdf_sp_secured_folder = $extension_settings['hb_pdf_sp_secured_folder'];
                $pdf_auth = md5($hb_pdf_iv_secured_folder);
                $packaging_auth = md5($hb_pdf_sp_secured_folder);
				
				$data['download_pdf'] = HTTPS_CATALOG.'index.php?route=extension/module/hbpdfinvoice/createinvoice&order_id='.$this->request->get['order_id'].'&authvalue='.$pdf_auth.'&download=admin';
				$data['download_packaging'] = HTTPS_CATALOG.'index.php?route=extension/module/hbpdfinvoice/create_packaging&order_id='.$this->request->get['order_id'].'&authvalue='.$packaging_auth.'&download=admin';

			if ($customer_group_info) {
				$data['customer_group'] = $customer_group_info['name'];
			} else {
				$data['customer_group'] = '';
			}

			$data['email'] = $order_info['email'];
			$data['telephone'] = $order_info['telephone'];

			$data['shipping_method'] = $order_info['shipping_method'];

                if ($this->config->get('shipping_canadapost_status')) {
					 $this->load->model('extension/shipping/canadapost');
                     $data['shipping_code'] = $order_info['shipping_code'];
                }
                
			$data['payment_method'] = $order_info['payment_method'];

			// Payment Address
			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
			    if($order_info['payment_custom_field'][1] !=''){
				    //$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{apartment_num} - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				   $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . $order_info['payment_custom_field'][1].' - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';				 
				}else{    
				    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				}
			}
			
			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{apartment_num}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'apartment_num' => $order_info['payment_custom_field'][1],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			// Shipping Address
			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
			    if($order_info['shipping_custom_field'][1] !=''){
				    //$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{apartment_num} - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				   $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . $order_info['shipping_custom_field'][1].' - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';				    
			    }else{
			        $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			    }
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{apartment_num}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'apartment_num' => $order_info['shipping_custom_field'][1],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
            
             $data['shipping_customer_name'] = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
            $address = '';
            if (!empty($order_info['shipping_custom_field'][1])) {
                $address .= $order_info['shipping_custom_field'][1] . ' - ';
            }
            $address .= $order_info['shipping_address_1'];
            $data['shipping_customer_address'] = $address;
            $data['shipping_customer_city'] = $order_info['shipping_city'];
            $data['shipping_customer_post_code'] = $order_info['shipping_postcode'];
            $data['shipping_customer_country'] = $order_info['shipping_country'];
            
			// Uploaded files
			$this->load->model('tool/upload');


				$this->load->model('catalog/product');
			
			$data['products'] = array();

			$products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
			// Initialize totals and product count BEFORE the loop
			$total_weight = 0.0;
			$total_l = 0.0;
			$total_w = 0.0;
			$total_h = 0.0;

			$product_count = count($products);
			
			foreach ($products as $product) {
				// If values are NULL, convert to 0
				$w = (float)($product['weight_kg'] ?? 0);
				$l = (float)($product['size_l'] ?? 0);
				$w2 = (float)($product['size_w'] ?? 0);
				$h = (float)($product['size_h'] ?? 0);

				// For MULTIPLE products → sum
				if ($product_count > 1) {
					$total_weight += $w;
					$total_l += $l;
					$total_w += $w2;
					$total_h += $h;
				} else {
					// For SINGLE product → use its own values
					$total_weight = $w;
					$total_l = $l;
					$total_w = $w2;
					$total_h = $h;
				}
				$option_data = array();

				$product_id = $product['product_id'];
			

				$options = $this->model_sale_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => $option['value'],
							'type'  => $option['type']
						);
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$option_data[] = array(
								'name'  => $option['name'],
								'value' => $upload_info['name'],
								'type'  => $option['type'],
								'href'  => $this->url->link('tool/upload/download', 'user_token=' . $this->session->data['user_token'] . '&code=' . $upload_info['code'], true)
							);
						}
					}
				}


				$cost_data = array();
				$products_cost = $this->model_sale_order->getOrderProductsCost($this->request->get['order_id'], $product['order_product_id']);
				if ($products_cost) {
				foreach ($products_cost as $product_cost) {
					$cost_data[] = array(
						'cost'  => $product_cost['cost'],
					);
				}
				} else {
					$cost_data[] = array(
						'cost'  => '0.0000',
					);
				}
            

				$this->load->model('localisation/stock_status');

				$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
				
				if (isset($this->request->post['stock_status_id'])) {
					$data['stock_status_id'] = $this->request->post['stock_status_id'];
				} elseif (!empty($product_info)) {
					$data['stock_status_id'] = $product['stockstatus_id'];
				} else {
					$data['stock_status_id'] = 0;
				}
								
				$this->load->model('localisation/delivery_status');

				$data['delivery_statuses'] = $this->model_localisation_delivery_status->getDeliveryStatuses($order_info['order_status_id']);
				
				if (isset($this->request->post['delivery_status_id'])) {
					$data['delivery_status_id'] = $this->request->post['delivery_status_id'];
				} elseif (!empty($product_info)) {
					$data['delivery_status_id'] = $product['delivery_status_id'];
				} else {
					$data['delivery_status_id'] = $order_info['delivery_status_id'];				
				}
				
				//$data['delivery_status_id'] = $order_info['delivery_status_id'];				
			

                 if ($this->config->get('shipping_canadapost_status')) {
					 $shipment = $this->model_extension_shipping_canadapost->getShipment($order_id, $product['product_id']);
					 
					 if ($shipment) {
						$filepath =  DIR_IMAGE . 'canadapost_shipping/' . $order_id . '/' . $product['product_id'] . '/' . $shipment['shipping_label'];
						if (is_file($filepath)) {
							$shipping_label = '../image/canadapost_shipping/' . $order_id . '/' . $product['product_id'] . '/Label_' . $product['product_id'] . '.pdf';
							} else {
								$shipping_label = '';
							}
							$tracking_no = $shipment['tracking'] ? $shipment['tracking']: '';
					 } else {
						$shipping_label = '';
					 }
                 } 
                

        /**
         * Marketplace code starts here
         */
        if ($this->config->get('module_marketplace_status')) {

            $seller_details = $this->db->query("SELECT c.customer_id,CONCAT(c.firstname,' ',c.lastname) name,c.email FROM ".DB_PREFIX."customerpartner_to_product c2p LEFT JOIN ".DB_PREFIX."customer c ON (c2p.customer_id = c.customer_id) WHERE c2p.product_id = '".(int)$product['product_id']."'")->row;

            if($seller_details AND isset($seller_details['name']) AND $seller_details['name'])
              $product['name'] = $product['name'].'</a> by Seller <a href="'.$this->url->link('customerpartner/partner', 'user_token=' . $this->session->data['user_token'] . '&view_all=1&filter_email=' . $seller_details['email'], true).'"><b>'.$seller_details['name'].'</b></a>';

          }
        /**
         * Marketplace code ends here
         */

      


        $seller_details = $this->db->query("SELECT c.customer_id,CONCAT(c.firstname,' ',c.lastname) name,c.email FROM ".DB_PREFIX."customerpartner_to_product c2p LEFT JOIN ".DB_PREFIX."customer c ON (c2p.customer_id = c.customer_id) WHERE c2p.product_id = '".(int)$product['product_id']."'")->row;
        $sel = 0;
        if(!$seller_details && !isset($seller_details['name'])) {
            $sel = 1;
                     /**
                * MP canada post modification code ends here
                */

                  if (isset($this->request->get['order_id'])) {
                     $order_id = $this->request->get['order_id'];
                   } else {
                     $order_id = 0;
                   }
                   $this->load->model('sale/order');
                   $order_info = $this->model_sale_order->getOrder($order_id);

                       $data = array_merge($data, $this->load->language('sale/order'));
                       $shippingCode = explode(".",$order_info['shipping_code']);

                         $data['servicesDom'] = array(
                           'DOM.RP'  => $data['text_service'],
                           'DOM.EP'  => $data['text_service1'],
                           'DOM.XP'  => $data['text_service2'],
                           'DOM.XP.CERT'  => $data['text_service3'],
                           'DOM.PC'  => $data['text_service4'],
                           'DOM.DT'  => $data['text_service5'],
                           'DOM.LIB'  => $data['text_service6'],
                         );

                         $data['servicesUsa'] = array(
                           'USA.EP'  => $data['text_service7'],
                       'USA.PW.ENV'  => $data['text_service8'],
                       'USA.PW.PAK'  => $data['text_service9'],
                       'USA.PW.PARCEL'  => $data['text_service11'],
                       'USA.XP' => $data['text_service14'],
                       'USA.TP' => $data['text_service13'],
                         );

                         $data['servicesInn'] = array(
                           'INT.XP'  => $data['text_INT_XP'],
                       'INT.IP.AIR'  => $data['text_INT_IP_AIR'],
                       'INT.IP.SURF'  => $data['text_INT_IP_SURF'],
                       'INT.SP.AIR'  => $data['text_INT_SP_AIR'],
                       'INT.PW.PARCEL'  => $data['text_INT_PW_PARCEL'],
                       'INT.SP.SURF'  => $data['text_INT_SP_SURF'],
                       'INT.TP'  => $data['text_INT_TP'],
                         );

                         $data['services'] = array_merge($data['servicesDom'], $data['servicesUsa'],$data['servicesInn']);

                         foreach ($data['services'] as $key => $value) {

                           if($shippingCode[1] == $value) {
                             $serviceKey = $key;
                           }
                         }
                       $products = $this->model_sale_order->getOrderProducts($this->request->get['order_id']);
                       if($shippingCode[0] == 'wk_canadapost') {
                         $apiKey = $this->config->get('shipping_wk_canadapost_api_key');
                         $apiPassword = $this->config->get('shipping_wk_canadapost_api_password');
                             $customerNumber = $this->config->get('shipping_wk_canadapost_customer_number');
                             $companyName = $this->config->get('config_name');
                             $senderName = $this->config->get('config_owner');
                             $addressLine1 = $this->config->get('config_address');
                             $senderPhone = $this->config->get('config_telephone');
                             $senderCity = $this->config->get('config_country_id');
                             $senderState = $this->config->get('config_zone_id');
                             $senderZip = $this->config->get('config_geocode');
                             $contract_id = $this->config->get('shipping_wk_canadapost_contract_id');
                          
                          $this->load->model('localisation/zone');

                           $zone = $this->model_localisation_zone->getZone($this->config->get('config_zone_id'));

                           $dataOptions = $this->getServices($order_info['shipping_iso_code_2'],$serviceKey);


                           $originPostalCode = str_replace(" ", "", $this->config->get('config_geocode'));
                        $contract_type = $this->config->get('shipping_wk_canadapost_contract_shippment');
                           // REST URL
                          if($contract_type){
                            $service_url = 'https://ct.soa-gw.canadapost.ca/rs/' . $customerNumber . '/' . $customerNumber .'/shipment';
                          }else{
                            $service_url = 'https://ct.soa-gw.canadapost.ca/rs/' . $customerNumber . '/' . $customerNumber . '/ncshipment';
                          }

                           $totalWeight = 0;
                         $totalQuantity = 0;
                         $totalPrice = 0 ;

                             $productWeight = $this->model_sale_order->getProductWeight($product['product_id']);
                              $totalWeight =  $totalWeight +  $productWeight;
                             $productOrder = $this->model_sale_order->getProductShipp($product['product_id'],$this->request->get['order_id']);
                             $totalQuantity  = $totalQuantity + $productOrder['quantity'];
                             $totalPrice = $totalPrice + $productOrder['price'];

                         if ($contract_type) {
                            $xml = '';
                            $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
                            $xml .= '<shipment xmlns="http://www.canadapost.ca/ws/shipment-v8">';
                            $xml .= '<group-id>grp1</group-id>';
                            $xml .= '<requested-shipping-point>' . $originPostalCode . '</requested-shipping-point>';
                            $xml .= '<cpc-pickup-indicator>true</cpc-pickup-indicator>';
                            $xml .= '<delivery-spec>';
                            $xml .= '    <service-code>' . $serviceKey . '</service-code>';
                            $xml .= ' <sender>';
                            $xml .= '   <company>' . $companyName . '</company>';
                            $xml .= '   <contact-phone>' . $senderPhone . '</contact-phone>';
                            $xml .= '   <address-details>';
                            $xml .= '     <address-line-1>' . $addressLine1 . '</address-line-1>';
                            $xml .= '     <city>MONTREAL</city>';
                            $xml .= '     <prov-state>' . $zone['code'] . '</prov-state>';
                            $xml .= '     <country-code>' . $order_info['shipping_iso_code_2'] . '</country-code>';
                            $xml .= '     <postal-zip-code>' . $originPostalCode . '</postal-zip-code>';
                            $xml .= '   </address-details>';
                            $xml .= ' </sender>';
                            $xml .= ' <destination>';
                            $xml .= '   <name>' . $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'] . '</name>';
                            $xml .= '   <company>' . $order_info['shipping_company'] . ' </company>';
                            $xml .= '   <client-voice-number>' . $order_info['telephone'] . ' </client-voice-number>';
                            $xml .= '   <address-details>';
                            $xml .= '     <address-line-1>' . $order_info['shipping_address_1'] . '</address-line-1>';
                            $xml .= '     <city>' . $order_info['shipping_city'] . ' </city>';
                            $xml .= '     <prov-state>' . $order_info['shipping_zone_code'] . '</prov-state>';
                            $xml .= '     <country-code>' . $order_info['shipping_iso_code_2'] . '</country-code>';
                            $xml .= '     <postal-zip-code>' . $order_info['shipping_postcode'] . '</postal-zip-code>';
                            $xml .= '   </address-details>';
                            $xml .= ' </destination>';
                            $xml .= '    <options>';
                            $xml .= '        <option>';
                            $xml .= '           <option-code>' . $dataOptions . '</option-code>';
                            $xml .= '       </option>';
                            $xml .= '   </options>';
                            $xml .= '   <parcel-characteristics>';
                            $xml .= '        <weight>' . $totalWeight * $totalQuantity . '</weight>';
                            $xml .= '    </parcel-characteristics>';
                            $xml .= '   <print-preferences>';
                            $xml .= '       <output-format>8.5x11</output-format>';
                            $xml .= '    </print-preferences>';
                            $xml .= '    <preferences>';
                            $xml .= '       <show-packing-instructions>true</show-packing-instructions>';
                            $xml .= '    </preferences>';
                            $xml .= ' <customs>';
                            $xml .= '   <currency>CAD</currency>';
                            $xml .= '   <reason-for-export>SAM</reason-for-export>';
                            $xml .= '   <sku-list>';
                            $xml .= '      <item>';
                            $xml .= '           <customs-description>this item is less waight </customs-description>';
                            $xml .= '           <unit-weight>' . $totalWeight . '</unit-weight>';
                            $xml .= '           <customs-value-per-unit>' . $totalPrice . '</customs-value-per-unit>';
                            $xml .= '           <customs-number-of-units>' . $totalQuantity . '</customs-number-of-units>';
                            $xml .= '      </item>';
                            $xml .= '   </sku-list>';
                            $xml .= ' </customs>';
                            $xml .= '    <settlement-info>';
                            $xml .= '        <contract-id>'. $contract_id .'</contract-id>';
                            $xml .= '       <intended-method-of-payment>creditcard</intended-method-of-payment>';
                            $xml .= '    </settlement-info>';
                            $xml .= ' </delivery-spec>';
                            $xml .= '</shipment>';
                          } else {
                            $xml  = '';
                            $xml .= '<non-contract-shipment xmlns="http://www.canadapost.ca/ws/ncshipment-v8">';
                            $xml .= '<requested-shipping-point>' . $originPostalCode . '</requested-shipping-point>';
                            $xml .= '<delivery-spec>';
                            $xml .= ' <service-code>' . $serviceKey . '</service-code>';
                            $xml .= ' <sender>';
                            $xml .= '   <company>' . $companyName . '</company>';
                            $xml .= '   <contact-phone>' . $senderPhone . '</contact-phone>';
                            $xml .= '   <address-details>';
                            $xml .= '     <address-line-1>' . $addressLine1 . '</address-line-1>';
                            $xml .= '     <city>MONTREAL</city>';
                            $xml .= '     <prov-state>' . $zone['code'] . '</prov-state>';
                            $xml .= '     <postal-zip-code>' . $originPostalCode . '</postal-zip-code>';
                            $xml .= '   </address-details>';
                            $xml .= ' </sender>';
                            $xml .= ' <destination>';
                            $xml .= '   <name>' . $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'] . '</name>';
                            $xml .= '   <company>' . $order_info['shipping_company'] . ' </company>';
                            $xml .= '   <client-voice-number>' . $order_info['telephone'] . ' </client-voice-number>';
                            $xml .= '   <address-details>';
                            $xml .= '     <address-line-1>' . $order_info['shipping_address_1'] . ' </address-line-1>';
                            $xml .= '     <city>' . $order_info['shipping_city'] . ' </city>';
                            $xml .= '     <prov-state>' . $order_info['shipping_zone_code'] . ' </prov-state>';
                            $xml .= '     <country-code>' . $order_info['shipping_iso_code_2'] . '</country-code>';
                            $xml .= '     <postal-zip-code>' . $order_info['shipping_postcode'] . '</postal-zip-code>';
                            $xml .= '   </address-details>';
                            $xml .= ' </destination>';
                            $xml .= ' <options>';
                            $xml .= '    <option>';
                            $xml .= '      <option-code>' . $dataOptions . '</option-code>';
                            $xml .= '    </option>';
                            $xml .= '   </options>';
                            $xml .= ' <parcel-characteristics>';
                            $xml .= '   <weight>' . $totalWeight * $totalQuantity . '</weight>';
                            $xml .= ' </parcel-characteristics>';
                            $xml .= ' <preferences>';
                            $xml .= '   <show-packing-instructions>true</show-packing-instructions>';
                            $xml .= ' </preferences>';
                            $xml .= ' <customs>';
                            $xml .= '   <currency>CAD</currency>';
                            $xml .= '   <reason-for-export>SAM</reason-for-export>';
                            $xml .= '   <sku-list>';
                            $xml .= '      <item>';
                            $xml .= '           <customs-description>this item is less waight </customs-description>';
                            $xml .= '           <unit-weight>' . $totalWeight . '</unit-weight>';
                            $xml .= '           <customs-value-per-unit>' . $totalPrice . '</customs-value-per-unit>';
                            $xml .= '           <customs-number-of-units>' . $totalQuantity . '</customs-number-of-units>';
                            $xml .= '      </item>';
                            $xml .= '   </sku-list>';
                            $xml .= ' </customs>';
                            $xml .= '</delivery-spec>';
                            $xml .= '</non-contract-shipment>';
                          }

                          //curl for sending request to canada post shipping api (REST API)
                          $curl = curl_init($service_url);
                          curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                          curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                          //  curl_setopt($curl, CURLOPT_CAINFO, realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/includes/modules/shipping/canadapost/cert/cacert.pem');
                          curl_setopt($curl, CURLOPT_CAINFO, realpath(dirname($_SERVER['SCRIPT_FILENAME'])) . '/canadapost/cert/cacert.pem');
                          curl_setopt($curl, CURLOPT_POST, true);
                          curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
                          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                          curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                          curl_setopt($curl, CURLOPT_USERPWD, $apiKey . ':' . $apiPassword);

                          if($contract_type){
                            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/vnd.cpc.shipment-v8+xml', 'Accept: application/vnd.cpc.shipment-v8+xml'));
                          }else{
                            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/vnd.cpc.ncshipment-v8+xml', 'Accept: application/vnd.cpc.ncshipment-v8+xml'));
                          }

                          $curl_response = curl_exec($curl);
                          //Response code for Curl request
                          $responseCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);

                          curl_close($curl);

                        if($responseCode == 200) {
                            $dom = new DOMDocument('1.0', 'UTF-8');
                            $data['canada_post_shipping_status'] = 1;
                           $dom->loadXml($curl_response);
                           $data['tracking_pin'] = $dom->getElementsByTagName('tracking-pin')->item(0)->nodeValue;
                           $data['shippinigid'] = $dom->getElementsByTagName('shipment-id')->item(0)->nodeValue;
                           $links= $dom->getElementsByTagName('links')->item(0)->childNodes;
                                 $counter= 0;

                           foreach ($links as $value) {
                             $labels= $value->getAttribute('href');
                             ++$counter;
                           }

                                  $data['tracking_details'] = array(
                                 'trackingPin' => $data['tracking_pin'],
                                 'shippingId'  => $data['shippinigid'],
                                 'label'    => $labels,

                                 );
                             $data['counter_label'] = $counter-1;
                               } else if($responseCode == 8716){
                                 $data['error'] = 'This product requires a valid value for Non-Delivery Handling.';
                                 echo 'This product requires a valid value for Non-Delivery Handling.';
                               }

                     }

                /**
                * MP canada post modification code ends here
                 */
         }

      

                // Store pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status')) {

                    $this->load->model('mp_store_pickup/mp_store_pickup');

                    $this->load->language('mp_store_pickup/mp_store_pickup_point_product');

                    $data['pickup_address_text'] = $this->language->get('text_pickup_address');

                    $getPickupOrder = $this->model_mp_store_pickup_mp_store_pickup->getPickupOrder($this->request->get['order_id'], $product['order_product_id']);

                    if ($getPickupOrder) {

                        $pickup_address = explode('{space}', html_entity_decode($getPickupOrder['address']));
                        $pickup_point_name = $getPickupOrder['name'];
                    }

                }
                // Store pickup module code ends here
                    
				$data['products'][] = array(

                // store pickup code starts here
                'pickup_point_name' => isset($pickup_point_name) ? $pickup_point_name : '',
                'pickup_address' => isset($pickup_address) ? $pickup_address : '',
                // store pickup code starts here
                    
					'order_product_id' => $product['order_product_id'],

				//'delivery_status_id' => (($product['delivery_status_id']>0)?$product['delivery_status_id']: $order_info['delivery_status_id']),
'delivery_status_id' => $product['delivery_status_id'],
			
					'product_id'       => $product['product_id'],
					'name'    	 	   => $product['name'],
					'model'    		   => $product['model'],

				'cost'       => $cost_data,
            
					'option'   		   => $option_data,

				'stockstatus'       => $product['stockstatus_id'],
			
					'quantity'		   => $product['quantity'],

                'shipping_label'  =>    isset($shipping_label) ? $shipping_label : '',
                'tracking_no'  =>  isset($tracking_no) ? $tracking_no : '',
                
					'price'    		   => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    		   => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'weight_kg'        => $product['weight_kg'],
					'size_l'           => $product['size_l'],
					'size_w'           => $product['size_w'],
					'size_h'           => $product['size_h'],
					'href'     		   => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product['product_id'], true)
				);
			}
						// Send to Twig
			$data['order_weight'] = $total_weight;
			$data['order_size_l'] = $total_l;
			$data['order_size_w'] = $total_w;
			$data['order_size_h'] = $total_h;
			/*
			echo '<pre>';
			print_r($order_info);
			echo '</pre>';
			*/

			$data['vouchers'] = array();

			$vouchers = $this->model_sale_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
					'href'        => $this->url->link('sale/voucher/edit', 'user_token=' . $this->session->data['user_token'] . '&voucher_id=' . $voucher['voucher_id'], true)
				);
			}

			$data['totals'] = array();

			$totals = $this->model_sale_order->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			$data['comment'] = nl2br($order_info['comment']);

			$this->load->model('customer/customer');

			$data['reward'] = $order_info['reward'];

			$data['reward_total'] = $this->model_customer_customer->getTotalCustomerRewardsByOrderId($this->request->get['order_id']);

			$data['affiliate_firstname'] = $order_info['affiliate_firstname'];
			$data['affiliate_lastname'] = $order_info['affiliate_lastname'];

			if ($order_info['affiliate_id']) {
				$data['affiliate'] = $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $order_info['affiliate_id'], true);
			} else {
				$data['affiliate'] = '';
			}

			$data['commission'] = $this->currency->format($order_info['commission'], $order_info['currency_code'], $order_info['currency_value']);

			$data['commission_total'] = $this->model_customer_customer->getTotalTransactionsByOrderId($this->request->get['order_id']);

			$this->load->model('localisation/order_status');

			$order_status_info = $this->model_localisation_order_status->getOrderStatus($order_info['order_status_id']);

			if ($order_status_info) {
				$data['order_status'] = $order_status_info['name'];
			} else {
				$data['order_status'] = '';
			}

			$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

			$data['order_status_id'] = $order_info['order_status_id'];

			$data['account_custom_field'] = $order_info['custom_field'];

			// Custom Fields
			$this->load->model('customer/custom_field');

			$data['account_custom_fields'] = array();

			$filter_data = array(
				'sort'  => 'cf.sort_order',
				'order' => 'ASC'
			);

			$custom_fields = $this->model_customer_custom_field->getCustomFields($filter_data);

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'account' && isset($order_info['custom_field'][$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['custom_field'][$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$data['account_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_info['custom_field'][$custom_field['custom_field_id']])) {
						foreach ($order_info['custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$data['account_custom_fields'][] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$data['account_custom_fields'][] = array(
							'name'  => $custom_field['name'],
							'value' => $order_info['custom_field'][$custom_field['custom_field_id']]
						);
					}

					if ($custom_field['type'] == 'file') {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_info['custom_field'][$custom_field['custom_field_id']]);

						if ($upload_info) {
							$data['account_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $upload_info['name']
							);
						}
					}
				}
			}

			// Custom fields
			$data['payment_custom_fields'] = array();

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'address' && isset($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$data['payment_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_info['payment_custom_field'][$custom_field['custom_field_id']])) {
						foreach ($order_info['payment_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$data['payment_custom_fields'][] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name'],
									'sort_order' => $custom_field['sort_order']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$data['payment_custom_fields'][] = array(
							'name'  => $custom_field['name'],
							'value' => $order_info['payment_custom_field'][$custom_field['custom_field_id']],
							'sort_order' => $custom_field['sort_order']
						);
					}

					if ($custom_field['type'] == 'file') {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_info['payment_custom_field'][$custom_field['custom_field_id']]);

						if ($upload_info) {
							$data['payment_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $upload_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}
				}
			}

			// Shipping

			$data['payment_cost'] = $order_info['payment_cost'];
			$data['shipping_cost'] = $order_info['shipping_cost'];
			$data['extra_cost'] = $order_info['extra_cost'];
            
			$data['shipping_custom_fields'] = array();

			foreach ($custom_fields as $custom_field) {
				if ($custom_field['location'] == 'address' && isset($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
					if ($custom_field['type'] == 'select' || $custom_field['type'] == 'radio') {
						$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

						if ($custom_field_value_info) {
							$data['shipping_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $custom_field_value_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}

					if ($custom_field['type'] == 'checkbox' && is_array($order_info['shipping_custom_field'][$custom_field['custom_field_id']])) {
						foreach ($order_info['shipping_custom_field'][$custom_field['custom_field_id']] as $custom_field_value_id) {
							$custom_field_value_info = $this->model_customer_custom_field->getCustomFieldValue($custom_field_value_id);

							if ($custom_field_value_info) {
								$data['shipping_custom_fields'][] = array(
									'name'  => $custom_field['name'],
									'value' => $custom_field_value_info['name'],
									'sort_order' => $custom_field['sort_order']
								);
							}
						}
					}

					if ($custom_field['type'] == 'text' || $custom_field['type'] == 'textarea' || $custom_field['type'] == 'file' || $custom_field['type'] == 'date' || $custom_field['type'] == 'datetime' || $custom_field['type'] == 'time') {
						$data['shipping_custom_fields'][] = array(
							'name'  => $custom_field['name'],
							'value' => $order_info['shipping_custom_field'][$custom_field['custom_field_id']],
							'sort_order' => $custom_field['sort_order']
						);
					}

					if ($custom_field['type'] == 'file') {
						$upload_info = $this->model_tool_upload->getUploadByCode($order_info['shipping_custom_field'][$custom_field['custom_field_id']]);

						if ($upload_info) {
							$data['shipping_custom_fields'][] = array(
								'name'  => $custom_field['name'],
								'value' => $upload_info['name'],
								'sort_order' => $custom_field['sort_order']
							);
						}
					}
				}
			}

			$data['ip'] = $order_info['ip'];
			$data['forwarded_ip'] = $order_info['forwarded_ip'];
			$data['user_agent'] = $order_info['user_agent'];
			$data['accept_language'] = $order_info['accept_language'];

			// Additional Tabs
			$data['tabs'] = array();

			if ($this->user->hasPermission('access', 'extension/payment/' . $order_info['payment_code'])) {
				if (is_file(DIR_CATALOG . 'controller/extension/payment/' . $order_info['payment_code'] . '.php')) {
					$content = $this->load->controller('extension/payment/' . $order_info['payment_code'] . '/order');
				} else {
					$content = '';
				}

				if ($content) {
					$this->load->language('extension/payment/' . $order_info['payment_code']);

					$data['tabs'][] = array(
						'code'    => $order_info['payment_code'],
						'title'   => $this->language->get('heading_title'),
						'content' => $content
					);
				}
			}

			$this->load->model('setting/extension');

			$extensions = $this->model_setting_extension->getInstalled('fraud');

			foreach ($extensions as $extension) {
				if ($this->config->get('fraud_' . $extension . '_status')) {
					$this->load->language('extension/fraud/' . $extension, 'extension');

					$content = $this->load->controller('extension/fraud/' . $extension . '/order');

					if ($content) {
						$data['tabs'][] = array(
							'code'    => $extension,
							'title'   => $this->language->get('extension')->get('heading_title'),
							'content' => $content
						);
					}
				}
			}
			
			// The URL we send API requests to
			$data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
			
			// API login
			$this->load->model('user/api');

			$api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

			if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
				$session = new Session($this->config->get('session_engine'), $this->registry);
				
				$session->start();
				
				$this->model_user_api->deleteApiSessionBySessionId($session->getId());
				
				$this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
				
				$session->data['api_id'] = $api_info['api_id'];

				$data['api_token'] = $session->getId();
			} else {
				$data['api_token'] = '';
			}

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
            $data['text_order'] = sprintf($this->language->get('text_order'), $this->request->get['order_id']);
			$this->response->setOutput($this->load->view('sale/order_info', $data));
		} else {
			return new Action('error/not_found');
		}
	}
	public function getProductSizeWeight() {
    $this->load->language('sale/order');
    $json = array();

    if (isset($this->request->get['order_product_id'])) {
        $this->load->model('sale/order');

        $order_product_id = (int)$this->request->get['order_product_id'];
        $data = $this->model_sale_order->getProductSizeWeight($order_product_id);

        $json['weight_kg'] = $data['weight_kg'];
        $json['size_l']    = $data['size_l'];
        $json['size_w']    = $data['size_w'];
        $json['size_h']    = $data['size_h'];
    } else {
        $json['error'] = 'Missing order_product_id';
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}

public function saveProductSizeWeight() {
    $this->load->language('sale/order');
    $json = array();

    if (!$this->user->hasPermission('modify', 'sale/order')) {
        $json['error'] = $this->language->get('error_permission');
    } else {
        $this->load->model('sale/order');

        $order_product_id = isset($this->request->post['order_product_id']) ? (int)$this->request->post['order_product_id'] : 0;
        $data = array(
            'weight_kg' => isset($this->request->post['weight_kg']) ? $this->request->post['weight_kg'] : '',
            'size_l'    => isset($this->request->post['size_l']) ? $this->request->post['size_l'] : '',
            'size_w'    => isset($this->request->post['size_w']) ? $this->request->post['size_w'] : '',
            'size_h'    => isset($this->request->post['size_h']) ? $this->request->post['size_h'] : ''
        );

        if ($order_product_id) {
            $this->model_sale_order->saveProductSizeWeight($order_product_id, $data);
            $json['success'] = true;
        } else {
            $json['error'] = 'Invalid order_product_id';
        }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}
public function saveGlobalSizeWeight() {
    $this->load->model('sale/order');

    if ($this->request->server['REQUEST_METHOD'] == 'POST') {
        
        $order_id = (int)$this->request->post['order_id'];
        $weight = (float)$this->request->post['weight_kg'];
        $l = (float)$this->request->post['size_l'];
        $w = (float)$this->request->post['size_w'];
        $h = (float)$this->request->post['size_h'];

        // Save to database
        $this->model_sale_order->saveGlobalSizeWeight(
            $order_id, $weight, $l, $w, $h
        );

        $json['success'] = true;

        echo json_encode($json);
        exit();
    }
}
public function getGlobalSizeWeight() {

    $this->load->model('sale/order');

    $order_id = (int)$this->request->get['order_id'];

    $info = $this->model_sale_order->getGlobalSizeWeight($order_id);

    echo json_encode([
        'weight_kg' => $info['global_weight_kg'],
        'size_l'    => $info['global_size_l'],
        'size_w'    => $info['global_size_w'],
        'size_h'    => $info['global_size_h']
    ]);
    exit();
}

	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	

    public function saveCost() {
        $id  = $this->request->get['order_product_id'];
        $cost = $this->request->get['cost'];

        $this->load->model('sale/order');

        $this->response->setOutput($this->model_sale_order->saveCost($id,$cost));
    }
	
    public function savePaymentCost() {
        $order_id  = $this->request->get['order_id'];
        $payment_cost = $this->request->get['payment_cost'];

        $this->load->model('sale/order');

        $this->response->setOutput($this->model_sale_order->savePaymentCost($order_id,$payment_cost));
    }
	
    public function saveShippingCost() {
        $order_id  = $this->request->get['order_id'];
        $shipping_cost = $this->request->get['shipping_cost'];

        $this->load->model('sale/order');

        $this->response->setOutput($this->model_sale_order->saveShippingCost($order_id,$shipping_cost));
    }	
	
    public function saveExtraCost() {
        $order_id  = $this->request->get['order_id'];
        $extra_cost = $this->request->get['extra_cost'];

        $this->load->model('sale/order');

        $this->response->setOutput($this->model_sale_order->saveExtraCost($order_id,$extra_cost));
    }		
            
	public function createInvoiceNo() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} elseif (isset($this->request->get['order_id'])) {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$invoice_no = $this->model_sale_order->createInvoiceNo($order_id);

			if ($invoice_no) {
				$json['invoice_no'] = $invoice_no;
			} else {
				$json['error'] = $this->language->get('error_action');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addReward() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info && $order_info['customer_id'] && ($order_info['reward'] > 0)) {
				$this->load->model('customer/customer');

				$reward_total = $this->model_customer_customer->getTotalCustomerRewardsByOrderId($order_id);

				if (!$reward_total) {
					$this->model_customer_customer->addReward($order_info['customer_id'], $this->language->get('text_order_id') . ' #' . $order_id, $order_info['reward'], $order_id);
				}
			}

			$json['success'] = $this->language->get('text_reward_added');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeReward() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info) {
				$this->load->model('customer/customer');

				$this->model_customer_customer->deleteReward($order_id);
			}

			$json['success'] = $this->language->get('text_reward_removed');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function addCommission() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info) {
				$this->load->model('customer/customer');

				$affiliate_total = $this->model_customer_customer->getTotalTransactionsByOrderId($order_id);

				if (!$affiliate_total) {
					$this->model_customer_customer->addTransaction($order_info['affiliate_id'], $this->language->get('text_order_id') . ' #' . $order_id, $order_info['commission'], $order_id);
				}
			}

			$json['success'] = $this->language->get('text_commission_added');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function removeCommission() {
		$this->load->language('sale/order');

		$json = array();

		if (!$this->user->hasPermission('modify', 'sale/order')) {
			$json['error'] = $this->language->get('error_permission');
		} else {
			if (isset($this->request->get['order_id'])) {
				$order_id = $this->request->get['order_id'];
			} else {
				$order_id = 0;
			}

			$this->load->model('sale/order');

			$order_info = $this->model_sale_order->getOrder($order_id);

			if ($order_info) {
				$this->load->model('customer/customer');

				$this->model_customer_customer->deleteTransactionByOrderId($order_id);
			}

			$json['success'] = $this->language->get('text_commission_removed');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function history() {
		$this->load->language('sale/order');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['histories'] = array();

		$this->load->model('sale/order');

		$results = $this->model_sale_order->getOrderHistories($this->request->get['order_id'], ($page - 1) * 10, 10);
		
		foreach ($results as $result) {
			$data['histories'][] = array(
				'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
				'status'     => $result['status'],
				'comment'    => html_entity_decode($result['comment']),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}
        
		$history_total = $this->model_sale_order->getTotalOrderHistories($this->request->get['order_id']);
		
		

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('sale/order/history', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $this->request->get['order_id'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('sale/order_history', $data));
	}

	public function invoice() {
		$this->load->language('sale/order');
		
		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $order_info['store_url'].'/image/'.$this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}

		$data['title'] = $this->language->get('text_invoice');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		
		$data['lang'] = $this->language->get('code');

		$this->load->model('sale/order');

		$this->load->model('setting/setting');

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_sale_order->getOrder($order_id);
			
			$data['text_order'] = sprintf($this->language->get('text_order'), $order_id);
			
			if ($order_info) {
				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

				if ($store_info) {
					$store_address = $store_info['config_address'];
					$store_email = $store_info['config_email'];
					$store_telephone = $store_info['config_telephone'];
					/*$store_telephone = sprintf("%s-%s-%s",
                      substr($store_info['config_telephone'], 0, 3),
                      substr($store_info['config_telephone'], 3, 3),
                      substr($store_info['config_telephone'], 6));
                    */
                    $store_fax = $store_info['config_fax'];
					$store_fax = $store_info['config_fax'];
				} else {
					$store_address = $this->config->get('config_address');
					$store_email = $this->config->get('config_email');
					$store_telephone = $this->config->get('config_telephone');
					/*$store_telephone = sprintf("%s-%s-%s",
                      substr($store_info['config_telephone'], 0, 3),
                      substr($store_info['config_telephone'], 3, 3),
                      substr($store_info['config_telephone'], 6));
                    */
					$store_fax = $this->config->get('config_fax');
				}
				
				/*** Add Logo in Invoice ***/
				if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
        			$data['logo'] = $order_info['store_url'].'/image/'.$this->config->get('config_logo');
        		} else {
        			$data['logo'] = '';
        		}
        		/*** End of Add Logo in Invoice ***/
        		
				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}

				if ($order_info['payment_address_format']) {
					$format = $order_info['payment_address_format'];
				} else {
				    if($order_info['payment_custom_field'][1] !=''){
					    //$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{apartment_num} - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
					    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . $order_info['payment_custom_field'][1].' - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';				 
				    }else{
				        $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				    }
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{apartment_num}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['payment_firstname'],
					'lastname'  => $order_info['payment_lastname'],
					'company'   => $order_info['payment_company'],
					'apartment_num' => $order_info['payment_custom_field'][1],
					'address_1' => $order_info['payment_address_1'],
					'address_2' => $order_info['payment_address_2'],
					'city'      => $order_info['payment_city'],
					'postcode'  => $order_info['payment_postcode'],
					'zone'      => $order_info['payment_zone'],
					'zone_code' => $order_info['payment_zone_code'],
					'country'   => $order_info['payment_country']
				);

				$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
				    if($order_info['shipping_custom_field'][1] !=''){
					    //$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{apartment_num} - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
					  $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . $order_info['shipping_custom_field'][1].' - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';				    
				    }else{
				        $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				    }
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{apartment_num}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'apartment_num' => $order_info['shipping_custom_field'][1],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'zone_code' => $order_info['shipping_zone_code'],
					'country'   => $order_info['shipping_country']
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->load->model('tool/upload');

				$product_data = array();

				$products = $this->model_sale_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_data = array();

					$options = $this->model_sale_order->getOrderOptions($order_id, $product['order_product_id']);

					foreach ($options as $option) {
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
							'value' => $value
						);
					}
					
					$product_delivery_status = $this->model_sale_order->get_delivery_status($order_id, $product['order_product_id']);


                // Store pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status')) {

                    $this->load->model('mp_store_pickup/mp_store_pickup');
                    $this->load->language('mp_store_pickup/mp_store_pickup_point_product');

                    $data['pickup_address_text'] = $this->language->get('text_pickup_address');

                    $getPickupOrder = $this->model_mp_store_pickup_mp_store_pickup->getPickupOrder($this->request->get['order_id'], $product['order_product_id']);

                    if ($getPickupOrder) {

                        $pickup_address = explode('{space}', html_entity_decode($getPickupOrder['address']));
                        $pickup_point_name = $getPickupOrder['name'];
                    }

                }
                // Store pickup module code ends here
                    
					$product_data[] = array(

                // store pickup code starts here
                'pickup_point_name' => isset($pickup_point_name) ? $pickup_point_name : '',
                'pickup_address' => isset($pickup_address) ? $pickup_address : '',
                // store pickup code starts here
                    
						'name'     => $product['name'],
						'model'    => $product['model'],
						'option'   => $option_data,

				'stockstatus'   => $product['stockstatus_id'],
			
						'quantity' => $product['quantity'],
						 'deliverystatus' => $product_delivery_status,
						'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
						'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$voucher_data = array();

				$vouchers = $this->model_sale_order->getOrderVouchers($order_id);

				foreach ($vouchers as $voucher) {
					$voucher_data[] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				$total_data = array();

				$totals = $this->model_sale_order->getOrderTotals($order_id);

				foreach ($totals as $total) {
					$total_data[] = array(
						'title' => $total['title'],
						'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
					);
				}

				// $data['orders'][] = array(
					// 'order_id'	       => $order_id,
					// 'invoice_no'       => $invoice_no,
					// 'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					// 'store_name'       => $order_info['store_name'],
					// 'store_url'        => rtrim($order_info['store_url'], '/'),
					// 'store_address'    => nl2br($store_address),
					// 'store_email'      => $store_email,
					// 'store_telephone'  => $store_telephone,
					// 'store_fax'        => $store_fax,
					// 'email'            => $order_info['email'],
					// 'telephone'        => sprintf("%s-%s-%s", substr($order_info['telephone'], 0, 3), substr($order_info['telephone'], 3, 3), substr($order_info['telephone'], 6)),
					// 'shipping_address' => $shipping_address,
					// 'shipping_method'  => $order_info['shipping_method'],
					// 'payment_address'  => $payment_address,
					// 'payment_method'   => $order_info['payment_method'],
					// 'product'          => $product_data,
					// 'voucher'          => $voucher_data,
					// 'total'            => $total_data,
					// 'comment'          => nl2br($order_info['comment'])
				// );
				
	        	$customer_partner_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id = '" . (int)$product['product_id'] . "'");
				    if ($customer_partner_query->num_rows) {
                // Modify $data array if product_id exists
				$data['orders'][] = array(
					'order_id'	       => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'store_name'       => 'BoardGamesNMore Used Marketplace',
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => 'market@boardgamesnmore.com',
					'store_telephone'  => $store_telephone,
					'store_fax'        => $store_fax,
					'email'            => $order_info['email'],
					'telephone'        => sprintf("%s-%s-%s", substr($order_info['telephone'], 0, 3), substr($order_info['telephone'], 3, 3), substr($order_info['telephone'], 6)),
					'shipping_address' => $shipping_address,
					'shipping_method'  => $order_info['shipping_method'],
					'payment_address'  => $payment_address,
					'payment_method'   => $order_info['payment_method'],
					'product'          => $product_data,
					'voucher'          => $voucher_data,
					'total'            => $total_data,
					'comment'          => nl2br($order_info['comment'])
					);
				}else {
					$data['orders'][] = array(
					'order_id'	       => $order_id,
					'invoice_no'       => $invoice_no,
					'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					'store_name'       => $order_info['store_name'],
					'store_url'        => rtrim($order_info['store_url'], '/'),
					'store_address'    => nl2br($store_address),
					'store_email'      => $store_email,
					'store_telephone'  => $store_telephone,
					'store_fax'        => $store_fax,
					'email'            => $order_info['email'],
					'telephone'        => sprintf("%s-%s-%s", substr($order_info['telephone'], 0, 3), substr($order_info['telephone'], 3, 3), substr($order_info['telephone'], 6)),
					'shipping_address' => $shipping_address,
					'shipping_method'  => $order_info['shipping_method'],
					'payment_address'  => $payment_address,
					'payment_method'   => $order_info['payment_method'],
					'product'          => $product_data,
					'voucher'          => $voucher_data,
					'total'            => $total_data,
					'comment'          => nl2br($order_info['comment'])
					);
				}
			
			
			}
		}

		$this->response->setOutput($this->load->view('sale/order_invoice', $data));
	}

	public function shipping() {
		$this->load->language('sale/order');

		$data['title'] = $this->language->get('text_shipping');

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['direction'] = $this->language->get('direction');
		$data['lang'] = $this->language->get('code');

		$this->load->model('sale/order');

		$this->load->model('catalog/product');

		$this->load->model('setting/setting');

		$data['orders'] = array();

		$orders = array();

		if (isset($this->request->post['selected'])) {
			$orders = $this->request->post['selected'];
		} elseif (isset($this->request->get['order_id'])) {
			$orders[] = $this->request->get['order_id'];
		}

		foreach ($orders as $order_id) {
			$order_info = $this->model_sale_order->getOrder($order_id);

			// Make sure there is a shipping method
			if ($order_info && $order_info['shipping_code']) {
				$store_info = $this->model_setting_setting->getSetting('config', $order_info['store_id']);

				if ($store_info) {
					$store_address = $store_info['config_address'];
					$store_email = $store_info['config_email'];
					$store_telephone = $store_info['config_telephone'];
				} else {
					$store_address = $this->config->get('config_address');
					$store_email = $this->config->get('config_email');
					$store_telephone = $this->config->get('config_telephone');
				}
				
				/*** Add Logo in Invoice ***/
				if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
        			$data['logo'] = $order_info['store_url'].'/image/'.$this->config->get('config_logo');
        		} else {
        			$data['logo'] = '';
        		}
        		/*** End of Add Logo in Invoice ***/

				if ($order_info['invoice_no']) {
					$invoice_no = $order_info['invoice_prefix'] . $order_info['invoice_no'];
				} else {
					$invoice_no = '';
				}

				if ($order_info['shipping_address_format']) {
					$format = $order_info['shipping_address_format'];
				} else {
				    if($order_info['shipping_custom_field'][1] !=''){
					    //$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{apartment_num} - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
					    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . $order_info['shipping_custom_field'][1].' - {address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';				    
				    }else{
				        $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
				    }
				}

				$find = array(
					'{firstname}',
					'{lastname}',
					'{company}',
					'{apartment_num}',
					'{address_1}',
					'{address_2}',
					'{city}',
					'{postcode}',
					'{zone}',
					'{zone_code}',
					'{country}'
				);

				$replace = array(
					'firstname' => $order_info['shipping_firstname'],
					'lastname'  => $order_info['shipping_lastname'],
					'company'   => $order_info['shipping_company'],
					'apartment_num' => $order_info['shipping_custom_field'][1],
					'address_1' => $order_info['shipping_address_1'],
					'address_2' => $order_info['shipping_address_2'],
					'city'      => $order_info['shipping_city'],
					'postcode'  => $order_info['shipping_postcode'],
					'zone'      => $order_info['shipping_zone'],
					'zone_code' => $order_info['shipping_zone_code'],
					'country'   => $order_info['shipping_country']
				);

				$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

				$this->load->model('tool/upload');

				$product_data = array();

				$products = $this->model_sale_order->getOrderProducts($order_id);

				foreach ($products as $product) {
					$option_weight = 0;

					$product_info = $this->model_catalog_product->getProduct($product['product_id']);

					if ($product_info) {
						$option_data = array();

						$options = $this->model_sale_order->getOrderOptions($order_id, $product['order_product_id']);

						foreach ($options as $option) {
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
								'value' => $value
							);

							$product_option_value_info = $this->model_catalog_product->getProductOptionValue($product['product_id'], $option['product_option_value_id']);

							if (!empty($product_option_value_info['weight'])) {
								if ($product_option_value_info['weight_prefix'] == '+') {
									$option_weight += $product_option_value_info['weight'];
								} elseif ($product_option_value_info['weight_prefix'] == '-') {
									$option_weight -= $product_option_value_info['weight'];
								}
							}
						}
                        $product_delivery_status = $this->model_sale_order->get_delivery_status($order_id, $product['order_product_id']);

                // Store pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status')) {

                    $this->load->model('mp_store_pickup/mp_store_pickup');
                    $this->load->language('mp_store_pickup/mp_store_pickup_point_product');

                    $data['pickup_address_text'] = $this->language->get('text_pickup_address');

                    $getPickupOrder = $this->model_mp_store_pickup_mp_store_pickup->getPickupOrder($this->request->get['order_id'], $product['order_product_id']);

                    if ($getPickupOrder) {

                        $pickup_address = explode('{space}', html_entity_decode($getPickupOrder['address']));
                        $pickup_point_name = $getPickupOrder['name'];
                    }

                }
                // Store pickup module code ends here
                    
						$product_data[] = array(

                // store pickup code starts here
                'pickup_point_name' => isset($pickup_point_name) ? $pickup_point_name : '',
                'pickup_address' => isset($pickup_address) ? $pickup_address : '',
                // store pickup code starts here
                    
							'name'     => $product_info['name'],
							'model'    => $product_info['model'],
							'option'   => $option_data,

				'stockstatus'   => $product['stockstatus_id'],
			
							'quantity' => $product['quantity'],
							 'deliverystatus' => $product_delivery_status,
							'location' => $product_info['location'],
							'sku'      => $product_info['sku'],
							'upc'      => $product_info['upc'],
							'ean'      => $product_info['ean'],
							'jan'      => $product_info['jan'],
							'isbn'     => $product_info['isbn'],
							'mpn'      => $product_info['mpn'],
							'weight'   => $this->weight->format(($product_info['weight'] + (float)$option_weight) * $product['quantity'], $product_info['weight_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point'))
						);
					}
				}

			// $data['orders'][] = array(
					// 'order_id'	       => $order_id,
					// 'invoice_no'       => $invoice_no,
					// 'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
					// 'store_name'       => $order_info['store_name'],
					// 'store_url'        => rtrim($order_info['store_url'], '/'),
					// 'store_address'    => nl2br($store_address),
					// 'store_email'      => $store_email,
					// 'store_telephone'  => $store_telephone,
					// 'email'            => $order_info['email'],
					// 'telephone'        => $order_info['telephone'],
					// 'shipping_address' => $shipping_address,
					// 'shipping_method'  => $order_info['shipping_method'],
					// 'product'          => $product_data,
					// 'comment'          => nl2br($order_info['comment'])
					// );
					
					if ($customer_partner_query->num_rows) {
		                // Modify $data array if product_id exists

                    		$data['orders'][] = [
                    			'order_id'         => $order_id,
                    			'invoice_no'       => $invoice_no,
                    			'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                    			'store_name'       => 'BoardGamesNMore Used Marketplace',
                    			'store_url'        => rtrim($order_info['store_url'], '/'),
                    			'store_address'    => nl2br($store_address),
                    			'store_email'      => 'market@boardgamesnmore.com',
                    			'store_telephone'  => $store_telephone,
                    			'email'            => $order_info['email'],
                    			'telephone'        => $order_info['telephone'],
                    			'shipping_address' => $shipping_address,
                    			'shipping_method'  => $order_info['shipping_method'],
                    			'product'          => $product_data,
                    			'comment'          => nl2br($order_info['comment'])
                    		];
                    }else {
                    	$data['orders'][] = [
                    		'order_id'	       => $order_id,
                    		'invoice_no'       => $invoice_no,
                    		'date_added'       => date($this->language->get('date_format_short'), strtotime($order_info['date_added'])),
                    		'store_name'       => $order_info['store_name'],
                    		'store_url'        => rtrim($order_info['store_url'], '/'),
                    		'store_address'    => nl2br($store_address),
                    		'store_email'      => $store_email,
                    		'store_telephone'  => $store_telephone,
                    		'email'            => $order_info['email'],
                    		'telephone'        => $order_info['telephone'],
                    		'shipping_address' => $shipping_address,
                    		'shipping_method'  => $order_info['shipping_method'],
                    		'product'          => $product_data,
                    		'comment'          => nl2br($order_info['comment'])
                    	];
                    }
			}
		}

		$this->response->setOutput($this->load->view('sale/order_shipping', $data));
	}
}
