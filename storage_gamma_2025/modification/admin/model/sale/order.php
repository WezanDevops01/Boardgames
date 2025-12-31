<?php
class ModelSaleOrder extends Model {


           public function getProductWeight($product_id) {
            $query = $this->db->query("SELECT weight FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");
            return $query->row['weight'];
          }
          public function getProductShipp($product_id, $order_id) {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE product_id = '" . (int)$product_id . "' AND order_id = '" . (int)$order_id . "' ");
            return $query->row;
          }
      
	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$order_id . "'");

		if ($order_query->num_rows) {

			$order_cost_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_cost WHERE order_id = '" . (int)$order_query->row['order_id'] . "'");

			if ($order_cost_query->num_rows) {
				$order_shipping_cost = $order_cost_query->row['shipping_cost'];
				$order_payment_cost = $order_cost_query->row['payment_cost'];
				$order_extra_cost = $order_cost_query->row['extra_cost'];
			} else {
				$order_shipping_cost = '0.0000';
				$order_payment_cost = '0.0000';
				$order_extra_cost = '0.0000';
			}
			
			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");

			if ($country_query->num_rows) {
				$payment_iso_code_2 = $country_query->row['iso_code_2'];
				$payment_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$payment_zone_code = $zone_query->row['code'];
			} else {
				$payment_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$order_query->row['shipping_country_id'] . "'");

			if ($country_query->num_rows) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");

			if ($zone_query->num_rows) {
				$shipping_zone_code = $zone_query->row['code'];
			} else {
				$shipping_zone_code = '';
			}

			$reward = 0;

			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

			foreach ($order_product_query->rows as $product) {
				$reward += $product['reward'];
			}
			
			$this->load->model('customer/customer');

			$affiliate_info = $this->model_customer_customer->getCustomer($order_query->row['affiliate_id']);

			if ($affiliate_info) {
				$affiliate_firstname = $affiliate_info['firstname'];
				$affiliate_lastname = $affiliate_info['lastname'];
			} else {
				$affiliate_firstname = '';
				$affiliate_lastname = '';
			}

			$this->load->model('localisation/language');

			$language_info = $this->model_localisation_language->getLanguage($order_query->row['language_id']);

			if ($language_info) {
				$language_code = $language_info['code'];
			} else {
				$language_code = $this->config->get('config_language');
			}

			return array(
				'order_id'                => $order_query->row['order_id'],
				'invoice_no'              => $order_query->row['invoice_no'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				'store_id'                => $order_query->row['store_id'],
				'store_name'              => $order_query->row['store_name'],
				'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query->row['customer_id'],
				'customer'                => $order_query->row['customer'],
				'customer_group_id'       => $order_query->row['customer_group_id'],
				'firstname'               => $order_query->row['firstname'],
				'lastname'                => $order_query->row['lastname'],
				'email'                   => $order_query->row['email'],
				'telephone'               => $order_query->row['telephone'],
				'custom_field'            => json_decode($order_query->row['custom_field'], true),
				'payment_firstname'       => $order_query->row['payment_firstname'],
				'payment_lastname'        => $order_query->row['payment_lastname'],
				'payment_company'         => $order_query->row['payment_company'],
				'payment_address_1'       => $order_query->row['payment_address_1'],
				'payment_address_2'       => $order_query->row['payment_address_2'],
				'payment_postcode'        => $order_query->row['payment_postcode'],
				'payment_city'            => $order_query->row['payment_city'],
				'payment_zone_id'         => $order_query->row['payment_zone_id'],
				'payment_zone'            => $order_query->row['payment_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['payment_country_id'],
				'payment_country'         => $order_query->row['payment_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['payment_address_format'],
				'payment_custom_field'    => json_decode($order_query->row['payment_custom_field'], true),
				'payment_method'          => $order_query->row['payment_method'],

				'shipping_cost'           => $order_shipping_cost,
				'payment_cost'            => $order_payment_cost,	
				'extra_cost'              => $order_extra_cost,				
			
				'payment_code'            => $order_query->row['payment_code'],
				'shipping_firstname'      => $order_query->row['shipping_firstname'],
				'shipping_lastname'       => $order_query->row['shipping_lastname'],
				'shipping_company'        => $order_query->row['shipping_company'],
				'shipping_address_1'      => $order_query->row['shipping_address_1'],
				'shipping_address_2'      => $order_query->row['shipping_address_2'],
				'shipping_postcode'       => $order_query->row['shipping_postcode'],
				'shipping_city'           => $order_query->row['shipping_city'],
				'shipping_zone_id'        => $order_query->row['shipping_zone_id'],
				'shipping_zone'           => $order_query->row['shipping_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['shipping_country_id'],
				'shipping_country'        => $order_query->row['shipping_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['shipping_address_format'],
				'shipping_custom_field'   => json_decode($order_query->row['shipping_custom_field'], true),
				'shipping_method'         => $order_query->row['shipping_method'],
				'shipping_code'           => $order_query->row['shipping_code'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'reward'                  => $reward,
				'order_status_id'         => $order_query->row['order_status_id'],
				'order_status'            => $order_query->row['order_status'],
				'affiliate_id'            => $order_query->row['affiliate_id'],
				'affiliate_firstname'     => $affiliate_firstname,
				'affiliate_lastname'      => $affiliate_lastname,
				'commission'              => $order_query->row['commission'],
				'language_id'             => $order_query->row['language_id'],
				'language_code'           => $language_code,
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency_code'],
				'currency_value'          => $order_query->row['currency_value'],
				'ip'                      => $order_query->row['ip'],
				'forwarded_ip'            => $order_query->row['forwarded_ip'],
				'user_agent'              => $order_query->row['user_agent'],
				'accept_language'         => $order_query->row['accept_language'],
				'date_added'              => $order_query->row['date_added'],
				'date_modified'           => $order_query->row['date_modified'],
				'global_weight_kg' => $order_query->row['global_weight_kg'],
				'global_size_l'    => $order_query->row['global_size_l'],
				'global_size_w'    => $order_query->row['global_size_w'],
				'global_size_h'    => $order_query->row['global_size_h']
			);
		} else {
			return;
		}
	}


			public function editDeliveryList($order_product_id, $el_data,$order_status_id,$order_id, $product_name, $delivery_name) {
				//echo $order_product_id; print_r($el_data);
				if (isset($el_data['delivery_status_id'])) {
					//echo "UPDATE " . DB_PREFIX . "order_product SET delivery_status_id = '" . (int)$el_data['delivery_status_id'] . "' WHERE order_product_id = '" . (int)$order_product_id . "'";
					//$comment = $product_name;
					$comment = $product_name.' - '.$delivery_name;					
					
					$this->db->query("UPDATE " . DB_PREFIX . "order_product SET delivery_status_id = '" . (int)$el_data['delivery_status_id'] . "' WHERE order_product_id = '" . (int)$order_product_id . "'");
					
					$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '0', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");		
				}
			}
		
	public function getOrders($data = array()) {
		$sql = "SELECT o.order_id, CONCAT(o.firstname, ' ', o.lastname) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status, o.shipping_code, o.total, o.currency_code, o.currency_value, o.date_added, o.date_modified FROM `" . DB_PREFIX . "order` o";

		if (!empty($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "o.order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(o.firstname, ' ', o.lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(o.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(o.date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND o.total = '" . (float)$data['filter_total'] . "'";
		}

		$sort_data = array(
			'o.order_id',
			'customer',
			'order_status',
			'o.date_added',
			'o.date_modified',
			'o.total'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY o.order_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getOrderProducts($order_id) {
		$sql = "SELECT *
				FROM `" . DB_PREFIX . "order_product`
				WHERE order_id = '" . (int)$order_id . "'";

		return $this->db->query($sql)->rows;
	}

	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}

	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}

	public function getOrderVoucherByVoucherId($voucher_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE voucher_id = '" . (int)$voucher_id . "'");

		return $query->row;
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}
	

	public function getOrderProductsCost($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product_cost WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}
	
    public function saveCost($order_product_id, $cost) {
        $product_cost_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product_cost WHERE order_product_id = '". (int)$order_product_id . "'");
    
    	if ($product_cost_query->num_rows) {
        	$this->db->query("UPDATE " . DB_PREFIX . "order_product_cost SET cost = '" . (float)$cost . "' WHERE order_product_id = '". (int)$order_product_id . "'");
		} else {
			$order_product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_product_id = '". (int)$order_product_id . "'");
        	$product_supplier_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_cost WHERE product_id = '". (int)$order_product_query->row['product_id'] . "'");
			if ($product_supplier_query->num_rows) {
			$supplier_id = $product_supplier_query->row['supplier_id'];
			} else {
			$supplier_id = 0;
			}
			$this->db->query("INSERT INTO " . DB_PREFIX . "order_product_cost SET order_product_id = '" . (int)$order_product_id . "', order_id = '" . (int)$order_product_query->row['order_id'] . "', product_id = '" . (int)$order_product_query->row['product_id'] . "', supplier_id = '" . (int)$supplier_id . "', cost = '" . (float)$cost . "', base_price = '" . (float)$order_product_query->row['price'] . "'");
		}
		
        $query = $this->db->query("SELECT cost FROM " . DB_PREFIX . "order_product_cost WHERE order_product_id = '". (int)$order_product_id . "'");
        $row = $query->row;

        return $row['cost'];
    }
	
    public function savePaymentCost($order_id, $payment_cost) {
		$payment_cost_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_cost WHERE order_id = '" . (int)$order_id . "'");
		
		if ($payment_cost_query->num_rows) {
        	$this->db->query("UPDATE `" . DB_PREFIX . "order_cost` SET payment_cost = '" . (float)$payment_cost . "' WHERE order_id = '" . (int)$order_id . "'");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "order_cost` SET order_id = '" . (int)$order_id . "', payment_cost = '" . (float)$payment_cost . "', shipping_cost = '0.0000', extra_cost = '0.0000'");
		}
		
        $query = $this->db->query("SELECT payment_cost FROM `" . DB_PREFIX . "order_cost` WHERE order_id = '" . (int)$order_id . "'");
        $row = $query->row;

        return $row['payment_cost'];
    }
	
    public function saveShippingCost($order_id, $shipping_cost) {
		$shipping_cost_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_cost WHERE order_id = '" . (int)$order_id . "'");
		
		if ($shipping_cost_query->num_rows) {
        	$this->db->query("UPDATE `" . DB_PREFIX . "order_cost` SET shipping_cost = '" . (float)$shipping_cost . "' WHERE order_id = '" . (int)$order_id . "'");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "order_cost` SET order_id = '" . (int)$order_id . "', payment_cost = '0.0000', shipping_cost = '" . (float)$shipping_cost . "', extra_cost = '0.0000'");
		}
		
        $query = $this->db->query("SELECT shipping_cost FROM `" . DB_PREFIX . "order_cost` WHERE order_id = '" . (int)$order_id . "'");
        $row = $query->row;

        return $row['shipping_cost'];
    }
	
    public function saveExtraCost($order_id, $extra_cost) {
		$extra_cost_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_cost WHERE order_id = '" . (int)$order_id . "'");
		
		if ($extra_cost_query->num_rows) {
        	$this->db->query("UPDATE `" . DB_PREFIX . "order_cost` SET extra_cost = '" . (float)$extra_cost . "' WHERE order_id = '" . (int)$order_id . "'");
		} else {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "order_cost` SET order_id = '" . (int)$order_id . "', payment_cost = '0.0000', shipping_cost = '0.0000', extra_cost = '" . (float)$extra_cost . "'");
		}

        $query = $this->db->query("SELECT extra_cost FROM `" . DB_PREFIX . "order_cost` WHERE order_id = '" . (int)$order_id . "'");
        $row = $query->row;

        return $row['extra_cost'];
    }
            
	public function getTotalOrders($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`";

		if (!empty($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND total = '" . (float)$data['filter_total'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalOrdersByStoreId($store_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE store_id = '" . (int)$store_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrdersByOrderStatusId($order_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE order_status_id = '" . (int)$order_status_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalOrdersByProcessingStatus() {
		$implode = array();

		$order_statuses = $this->config->get('config_processing_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE " . implode(" OR ", $implode));

			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function getTotalOrdersByCompleteStatus() {
		$implode = array();

		$order_statuses = $this->config->get('config_complete_status');

		foreach ($order_statuses as $order_status_id) {
			$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
		}

		if ($implode) {
			$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE " . implode(" OR ", $implode) . "");

			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function getTotalOrdersByLanguageId($language_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE language_id = '" . (int)$language_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}

	public function getTotalOrdersByCurrencyId($currency_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE currency_id = '" . (int)$currency_id . "' AND order_status_id > '0'");

		return $query->row['total'];
	}
	
	public function getTotalSales($data = array()) {
		$sql = "SELECT SUM(total) AS total FROM `" . DB_PREFIX . "order`";

		if (!empty($data['filter_order_status'])) {
			$implode = array();

			$order_statuses = explode(',', $data['filter_order_status']);

			foreach ($order_statuses as $order_status_id) {
				$implode[] = "order_status_id = '" . (int)$order_status_id . "'";
			}

			if ($implode) {
				$sql .= " WHERE (" . implode(" OR ", $implode) . ")";
			}
		} elseif (isset($data['filter_order_status_id']) && $data['filter_order_status_id'] !== '') {
			$sql .= " WHERE order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE order_status_id > '0'";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$sql .= " AND DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_total'])) {
			$sql .= " AND total = '" . (float)$data['filter_total'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function createInvoiceNo($order_id) {
		$order_info = $this->getOrder($order_id);

		if ($order_info && !$order_info['invoice_no']) {
			$query = $this->db->query("SELECT MAX(invoice_no) AS invoice_no FROM `" . DB_PREFIX . "order` WHERE invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "'");

			if ($query->row['invoice_no']) {
				$invoice_no = $query->row['invoice_no'] + 1;
			} else {
				$invoice_no = 1;
			}

			$this->db->query("UPDATE `" . DB_PREFIX . "order` SET invoice_no = '" . (int)$invoice_no . "', invoice_prefix = '" . $this->db->escape($order_info['invoice_prefix']) . "' WHERE order_id = '" . (int)$order_id . "'");

			return $order_info['invoice_prefix'] . $invoice_no;
		}
	}

	public function getOrderHistories($order_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalOrderHistories($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

	public function getTotalOrderHistoriesByOrderStatusId($order_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_history WHERE order_status_id = '" . (int)$order_status_id . "'");

		return $query->row['total'];
	}
	
	public function getEmailsByProductsOrdered($products, $start, $end) {
		$implode = array();

		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . (int)$product_id . "'";
		}

		$query = $this->db->query("SELECT DISTINCT email FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0' LIMIT " . (int)$start . "," . (int)$end);

		return $query->rows;
	}

	public function getTotalEmailsByProductsOrdered($products) {
		$implode = array();

		foreach ($products as $product_id) {
			$implode[] = "op.product_id = '" . (int)$product_id . "'";
		}

		$query = $this->db->query("SELECT COUNT(DISTINCT email) AS total FROM `" . DB_PREFIX . "order` o LEFT JOIN " . DB_PREFIX . "order_product op ON (o.order_id = op.order_id) WHERE (" . implode(" OR ", $implode) . ") AND o.order_status_id <> '0'");

		return $query->row['total'];
	}
	
		public function getRequestCustomerDetails($order_id) {
            $order_id = (int)$order_id; 
            $query = $this->db->query("SELECT CONCAT(firstname, ' ', lastname) AS fullname, email FROM `oc_order` WHERE `order_id` = $order_id;");
            return $query->row;
        }
        
     
    public function addLinkedOrder($parent_order_id, $linked_order_id) {
        // Ensure the linked_order_id is not zero and is an integer
        if ((int)$linked_order_id > 0) {
            // Check if the linked order already exists
            $query = $this->db->query("SELECT COUNT(*) as count FROM " . DB_PREFIX . "link_orders WHERE order_id = '" . (int)$parent_order_id . "' AND linked_order_id = '" . (int)$linked_order_id . "'");
            
            // If the count is 0, it means the link doesn't exist, so we can insert it
            if ($query->row['count'] == 0) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "link_orders SET order_id = '" . (int)$parent_order_id . "', linked_order_id = '" . (int)$linked_order_id . "'");
                return true; // Indicate that the insertion was successful
            }
        }
        
        return false; // Indicate that the insertion was not performed (link already exists or linked_order_id is invalid)
    }
    		
    public function getLinkedOrders($order_id) {
        $query = $this->db->query("SELECT linked_order_id FROM " . DB_PREFIX . "link_orders WHERE order_id = '" . (int)$order_id . "'");
        return $query->rows;
    }
            
    public function getParentOrders($linked_orders) {
        $query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "link_orders WHERE linked_order_id = '" . (int)$linked_orders . "'");
        return $query->rows;
    }
    	
     public function RemoveLinkedOrder($parent_order_id, $linked_order_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "link_orders WHERE order_id = '" . (int)$parent_order_id . "' AND linked_order_id = '" . (int)$linked_order_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "link_orders WHERE order_id = '" . (int)$linked_order_id . "' AND linked_order_id = '" . (int)$parent_order_id . "'");
    }

    
     public function getAllLinkedOrders($list_parent_orders) {
            $order_ids = implode(',', array_map('intval', $list_parent_orders));
            $query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "link_orders WHERE order_id IN (" . $order_ids . ")");
           
            return $query->rows;
        }
        public function get_delivery_status($order_id, $order_product_id) {
            $query = $this->db->query("SELECT ds.name FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "delivery_status ds ON op.delivery_status_id = ds.delivery_status_id WHERE op.order_id = '" . (int)$order_id . "' AND op.order_product_id = '" . (int)$order_product_id . "'");
    	
    		return $query->num_rows ? $query->row['name'] : '';
    	}
    	
    	
    	
    	public function hasPendingRequest($order_id) {
           $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_request WHERE order_id = '" . (int)$order_id . "' AND status = 'pending'");
           return $query->row['total'] > 0;
       }
	   public function getProductSizeWeight($order_product_id) {

    $order_product_id = (int)$order_product_id;

    // 1. Get product_id from order_product table
    $query = $this->db->query("SELECT product_id 
                               FROM `" . DB_PREFIX . "order_product` 
                               WHERE order_product_id = '" . $order_product_id . "'");

    if (!$query->num_rows) {
        return [
            'weight_kg' => '',
            'size_l'    => '',
            'size_w'    => '',
            'size_h'    => ''
        ];
    }

    $product_id = (int)$query->row['product_id'];

    // 2. Fetch weight and dimensions from product table
    $query2 = $this->db->query("SELECT weight AS weight_kg, 
                                       length AS size_l, 
                                       width AS size_w, 
                                       height AS size_h
                                FROM `" . DB_PREFIX . "product` 
                                WHERE product_id = '" . $product_id . "'");

    if ($query2->num_rows) {
        return $query2->row;
    }

    // 3. Return default values if not found
    return [
        'weight_kg' => '',
        'size_l'    => '',
        'size_w'    => '',
        'size_h'    => ''
    ];
}

public function saveProductSizeWeight($order_product_id, $data = []) {
    $order_product_id = (int)$order_product_id;
	$query = $this->db->query("SELECT product_id 
                               FROM `" . DB_PREFIX . "order_product` 
                               WHERE order_product_id = '" . (int)$order_product_id . "'");
    if (!$query->num_rows) {
        return false;
    }
 
    $product_id = (int)$query->row['product_id'];

    $weight = isset($data['weight_kg']) && $data['weight_kg'] !== '' ? (float)$data['weight_kg'] : null;
    $size_l  = isset($data['size_l']) && $data['size_l'] !== '' ? (float)$data['size_l'] : null;
    $size_w  = isset($data['size_w']) && $data['size_w'] !== '' ? (float)$data['size_w'] : null;
    $size_h  = isset($data['size_h']) && $data['size_h'] !== '' ? (float)$data['size_h'] : null;

    $this->db->query("UPDATE `" . DB_PREFIX . "product` SET
        weight = " . ($weight !== null ? (float)$weight : "NULL") . ",
        length = " . ($size_l !== null ? (float)$size_l : "NULL") . ",
        width = " . ($size_w !== null ? (float)$size_w : "NULL") . ",
        height = " . ($size_h !== null ? (float)$size_h : "NULL") . "
        WHERE product_id = '" . $product_id . "'
    ");

    return true;
}
public function saveGlobalSizeWeight($order_id, $weight, $l, $w, $h) {

    $this->db->query("UPDATE `" . DB_PREFIX . "order` 
        SET 
            global_weight_kg = '" . (float)$weight . "',
            global_size_l = '" . (float)$l . "',
            global_size_w = '" . (float)$w . "',
            global_size_h = '" . (float)$h . "'
        WHERE order_id = '" . (int)$order_id . "'
    ");
}
public function getGlobalSizeWeight($order_id) {
    $query = $this->db->query("SELECT global_weight_kg, global_size_l, global_size_w, global_size_h 
                               FROM `" . DB_PREFIX . "order`
                               WHERE order_id = '" . (int)$order_id . "'");

    return $query->row;
}

       
}
