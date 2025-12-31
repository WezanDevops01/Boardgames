<?php
class ControllerSaleDelivery extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('sale/delivery');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('sale/delivery');
		$this->getList();
	}
			
	protected function getList() {
		// Consolidate filter variable assignments to avoid redundancy and potential bugs
		$filter_order_id      = isset($this->request->get['filter_order_id']) ? $this->request->get['filter_order_id'] : '';
		$filter_customer      = isset($this->request->get['filter_customer']) ? $this->request->get['filter_customer'] : '';
		$filter_products      = isset($this->request->get['filter_products']) ? $this->request->get['filter_products'] : '';
		$filter_productmodel  = isset($this->request->get['filter_productmodel']) ? $this->request->get['filter_productmodel'] : '';
		$filter_start_date    = isset($this->request->get['filter_start_date']) ? $this->request->get['filter_start_date'] : '';
		$filter_end_date      = isset($this->request->get['filter_end_date']) ? $this->request->get['filter_end_date'] : '';
		$filter_name          = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';

		// Get the delivery status name from the filter input
		$input_filter_delivery_status_name = isset($this->request->get['filter_order_status']) ? $this->request->get['filter_order_status'] : '';

		// Load the delivery statuses to find the corresponding ID
		$this->load->model('localisation/delivery_status');
		$delivery_statuses = $this->model_localisation_delivery_status->getDeliveryStatuses();
		
		$delivery_status_id = null;
		
		foreach ($delivery_statuses as $status) {
			if ($status['name'] === $input_filter_delivery_status_name) {
				$delivery_status_id = $status['delivery_status_id'];
				break;
			}
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

		// Build the URL for pagination and sorting
		$url = '';		
		
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_products'])) {
			$url .= '&filter_products=' . urlencode(html_entity_decode($this->request->get['filter_products'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_productmodel'])) {
			$url .= '&filter_productmodel=' . urlencode(html_entity_decode($this->request->get['filter_productmodel'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_start_date'])) {
			$url .= '&filter_start_date=' . $this->request->get['filter_start_date'];
		}
		if (isset($this->request->get['filter_end_date'])) {
			$url .= '&filter_end_date=' . $this->request->get['filter_end_date'];
		}
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		// The filter_order_status URL parameter is used for the delivery status
		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . urlencode(html_entity_decode($this->request->get['filter_order_status'], ENT_QUOTES, 'UTF-8'));
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
			'href' => $this->url->link('sale/delivery', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['invoice'] = $this->url->link('sale/delivery/invoice', 'user_token=' . $this->session->data['user_token'], true);
		$data['shipping'] = $this->url->link('sale/delivery/shipping', 'user_token=' . $this->session->data['user_token'], true);
		$data['add'] = $this->url->link('sale/delivery/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = str_replace('&amp;', '&', $this->url->link('sale/delivery/delete', 'user_token=' . $this->session->data['user_token'] . $url, true));

		$data['orders'] = array();

		$filter_data = array(
			'filter_order_id'       => $filter_order_id,
			'filter_name'           => $filter_name,
			'delivery_status_id'    => $delivery_status_id, // This is the key change
			'sort'                  => $sort,
			'order'                 => $order,
			'start'                 => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                 => $this->config->get('config_limit_admin'),
			'filter_customer'       => $filter_customer,
			'filter_products'       => $filter_products,
			'filter_productmodel'   => $filter_productmodel,
			'filter_start_date'     => $filter_start_date,
			'filter_end_date'       => $filter_end_date,
		);

		$order_total = $this->model_sale_delivery->getTotaldeliveryColumn($filter_data);
		$results = $this->model_sale_delivery->getdeliveryColumn($filter_data);
		
		foreach ($results as $result) {
			$order_ids = $this->model_sale_delivery->getOrderIds($result['product_id'], $result['delivery_status_id']);
			$order_links = array();
			foreach ($order_ids as $order_id_product){
				$order_links[] = "<a href='".$this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $order_id_product , 'SSL')."'>".$order_id_product."</a>";
			}
			$data['orders'][] = array(
				'order_id' => $order_links,
				'product_id' => $result['product_id'],
				'delivery_status_id' => $result['delivery_status_id'],
				'quantity' => $result['quantity'],
				'name' => $result['name'],
				'model' => $result['model'],
				'date_added' => $result['date_added'],
				'customer' => $result['firstname'] . ' ' . $result['lastname'],
				'total' => $result['total'],
			);
		}
		
		$this->load->model('localisation/delivery_status');
		$data['delivery_statuses'] = $this->model_localisation_delivery_status->getDeliveryStatuses();

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

		// Re-building the URL for sorting and pagination links
		$url = '';
		if (isset($this->request->get['filter_order_id'])) {
			$url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
		}
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_order_status'])) {
			$url .= '&filter_order_status=' . urlencode(html_entity_decode($this->request->get['filter_order_status'], ENT_QUOTES, 'UTF-8'));
		}
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_order'] = $this->url->link('sale/delivery', 'user_token=' . $this->session->data['user_token'] . '&sort=o.order_id' . $url, true);
		$data['sort_customer'] = $this->url->link('sale/delivery', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
		$data['sort_status'] = $this->url->link('sale/delivery', 'user_token=' . $this->session->data['user_token'] . '&sort=order_status' . $url, true);
		$data['sort_total'] = $this->url->link('sale/delivery', 'user_token=' . $this->session->data['user_token'] . '&sort=o.total' . $url, true);
		$data['sort_date_added'] = $this->url->link('sale/delivery', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_added' . $url, true);
		$data['sort_date_modified'] = $this->url->link('sale/delivery', 'user_token=' . $this->session->data['user_token'] . '&sort=o.date_modified' . $url, true);

		// Pagination
		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('sale/delivery', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		// Pass filter values to the view for display
		$data['filter_order_id'] = $filter_order_id;
		$data['filter_customer'] = $filter_customer;
		$data['filter_order_status'] = $input_filter_delivery_status_name; // This is the filter value displayed
		$data['filter_products'] = $filter_products;
		$data['filter_productmodel'] = $filter_productmodel;
		$data['filter_start_date'] = $filter_start_date;
		$data['filter_end_date'] = $filter_end_date;
		$data['filter_name'] = $filter_name;

		$data['delivery_status'] = $input_filter_delivery_status_name;
		$data['delivery_status_id'] = $delivery_status_id;
		
		// The following variables were not set in the original code but were used in $data['filter_...']
		$data['filter_order_status_id'] = null; 
		$data['filter_total'] = null;
		$data['filter_date_added'] = null;
		$data['filter_date_modified'] = null;
		
		$data['sort'] = $sort;
		$data['order'] = $order;

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		// API login
		$data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
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

		$this->response->setOutput($this->load->view('sale/delivery_list', $data));
	}
	
public function autocomplete() {
    $json = array();

    if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
        $this->load->model('catalog/product_search');
        $this->load->model('catalog/option');

        $filter_name  = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';
        $filter_model = isset($this->request->get['filter_model']) ? $this->request->get['filter_model'] : '';
        $limit        = isset($this->request->get['limit']) ? (int)$this->request->get['limit'] : 5;

        $filter_data = array(
            'filter_name'  => $filter_name,
            'filter_model' => $filter_model,
            'start'        => 0,
            'limit'        => $limit
        );

        $results = $this->model_catalog_product_search->getProducts($filter_data);

        foreach ($results as $result) {
            $option_data = array();
            $product_options = $this->model_catalog_product_search->getProductOptions($result['product_id']);

            foreach ($product_options as $product_option) {
                $option_info = $this->model_catalog_option->getOption($product_option['option_id']);

                if ($option_info) {
                    $product_option_value_data = array();

                    foreach ($product_option['product_option_value'] as $product_option_value) {
                        $option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

                        if ($option_value_info) {
                            $product_option_value_data[] = array(
                                'product_option_value_id' => $product_option_value['product_option_value_id'],
                                'option_value_id'         => $product_option_value['option_value_id'],
                                'name'                    => $option_value_info['name'],
                                'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
                                'price_prefix'            => $product_option_value['price_prefix']
                            );
                        }
                    }

                    $option_data[] = array(
                        'product_option_id'    => $product_option['product_option_id'],
                        'product_option_value' => $product_option_value_data,
                        'option_id'            => $product_option['option_id'],
                        'name'                 => $option_info['name'],
                        'type'                 => $option_info['type'],
                        'value'                => $product_option['value'],
                        'required'             => $product_option['required']
                    );
                }
            }

            $json[] = array(
                'product_id' => $result['product_id'],
                'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                'model'      => $result['model'],
                'option'     => $option_data,
                'price'      => $result['price']
            );
        }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}
}
