<?php
class ModelExtensionModuleHbOrders extends Model {
	public function install(){
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_orders_manager'");
		if ((version_compare(VERSION,'2.0.0.0','>=' )) and (version_compare(VERSION,'2.3.0.0','<' ))) {
			$ocmod_filename = 'ocmod_orders_manager_2000_2200.txt';
			$ocmod_name = 'Orders Manager PRO [2000 - 2200]';
		}else if ((version_compare(VERSION,'2.3.0.0','>=' )) and (version_compare(VERSION,'3.0.0.0','<' ))) {
			$ocmod_filename = 'ocmod_orders_manager_23xx.txt';
			$ocmod_name = 'Orders Manager PRO [23xx]';
		}else if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$ocmod_filename = 'ocmod_orders_manager_3xxx.txt';
			$ocmod_name = 'Orders Manager PRO [3.x.x.x]';
		}

		$ocmod_version = EXTENSION_VERSION;
		$ocmod_code = 'huntbee_orders_manager';	
		$ocmod_author = 'HuntBee OpenCart Services';
		$ocmod_link = 'https://www.huntbee.com';

		$file = DIR_APPLICATION . 'view/template/extension/module/ocmod/'.$ocmod_filename;
		if (file_exists($file)) {
			$ocmod_xml = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			$ocmod_xml = str_replace('{huntbee_version}',$ocmod_version,$ocmod_xml);
			$this->db->query("INSERT INTO " . DB_PREFIX . "modification SET code = '" . $this->db->escape($ocmod_code) . "', name = '" . $this->db->escape($ocmod_name) . "', author = '" . $this->db->escape($ocmod_author) . "', version = '" . $this->db->escape($ocmod_version) . "', link = '" . $this->db->escape($ocmod_link) . "', xml = '" . $this->db->escape($ocmod_xml) . "', status = '1', date_added = NOW()");
		}
		
		$this->db->query("INSERT INTO `".DB_PREFIX."setting` (`code`, `key`, `value`, `serialized`) VALUES ('module_hb_orders','module_hb_orders_status', '1','0')");

	}
	
	public function uninstall(){
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE `code` = 'huntbee_orders_manager'");
		
		$this->db->query("DELETE FROM `".DB_PREFIX."setting` WHERE `key` = 'module_hb_orders_status'");
	}

	public function check_updates(){
		$data =  array();
		$file = DIR_APPLICATION . 'view/template/extension/module/ocmod/updates_hb_orders.json';
		if (file_exists($file)) {
			$data = file_get_contents($file, FILE_USE_INCLUDE_PATH, null);
			$data = json_decode($data, true);

			if ($data['version'] <= EXTENSION_VERSION) {
				$data =  array();
			}
			return $data;
		}
	}

	public function table_columns(){
		$columns = array('items','invoice','store_name','customer_group','email','telephone','fax','payment_name','payment_address','payment_city','payment_postcode','payment_country','payment_zone','payment_method','shipping_name','shipping_address','shipping_city','shipping_postcode','shipping_country','shipping_zone','shipping_method','comment','reward','affiliate','commission','marketing_id','tracking','language','currency_code','user_agent');
		return $columns;
	}

	public function customer_table_columns(){
		$columns = array('email','telephone','customer_since','total_purchases','pending_purchases','ip');
		return $columns;
	}

	public function opacity_array(){
		$opacity[] = array('0%','00');
		$opacity[] = array('10%','1A');
		$opacity[] = array('20%','33');
		$opacity[] = array('30%','4D');
		$opacity[] = array('40%','66');
		$opacity[] = array('50%','80');
		$opacity[] = array('60%','99');
		$opacity[] = array('70%','B3');
		$opacity[] = array('80%','CC');
		$opacity[] = array('90%','E6');
		$opacity[] = array('100%','FF');

		return $opacity;
	}

	public function getRecords($data){
		$sql = "SELECT o.*, CONCAT(o.firstname, ' ', o.lastname) AS customer, CONCAT(o.payment_firstname, ' ', o.payment_lastname) AS payment_name, CONCAT(o.shipping_firstname, ' ', o.shipping_lastname) AS shipping_name, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status FROM `" . DB_PREFIX . "order` `o`";

		if (!empty($data['add_table_query'])) {
			$sql .= " LEFT JOIN ".DB_PREFIX.$data['add_table_query'];
		} 

		if (empty($data['query']) AND ($data['search_order_status_id'] === false )) {
			$sql .= " WHERE o.order_status_id <> 0 ";
		}else{
			$sql .= " WHERE 1=1";
		}
		
		if (!empty($data['query'])) {
			$sql .= " AND ".html_entity_decode($data['query'], ENT_QUOTES, 'UTF-8');
		} 

		if (!empty($data['add_table_filter'])) {
			$sql .= " AND ".html_entity_decode($data['add_table_filter'], ENT_QUOTES, 'UTF-8');
		} 

		if (!empty($data['search'])) {
			$sql .= " AND (o.order_id LIKE '%".$this->db->escape($data['search'])."%' OR o.firstname LIKE '%".$this->db->escape($data['search'])."%' OR o.lastname LIKE '%".$this->db->escape($data['search'])."%' OR o.email LIKE '%".$this->db->escape($data['search'])."%' OR o.telephone LIKE '%".$this->db->escape($data['search'])."%' OR o.invoice_no LIKE '%".$this->db->escape($data['search'])."%')";
		}
				
		if (!empty($data['search_order_id'])) {
			$sql .= " AND o.order_id LIKE '%".$this->db->escape($data['search_order_id'])."%'";
		}

		if ($data['search_order_status_id'] != '') {
			$sql .= " AND o.order_status_id = '".(int)$data['search_order_status_id']."'";
		}

		if (isset($data['sort_parameter'])) {
			$sql .= " ORDER BY ".$data['sort_parameter'];
		}else{
			$sql .= " ORDER BY o.order_id";
		}

		if (isset($data['sort_order'])) {
			$sql .= " ".$data['sort_order'];
		}else{
			$sql .= " DESC";
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
		
		//$this->log->write($sql);
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getTotalRecords($data){
		$sql = "SELECT count(*) as total FROM `" . DB_PREFIX . "order` `o`";

		if (!empty($data['add_table_query'])) {
			$sql .= " LEFT JOIN ".DB_PREFIX.$data['add_table_query'];
		} 

		if (empty($data['query']) AND ($data['search_order_status_id'] === false )) {
			$sql .= " WHERE o.order_status_id <> 0 ";
		}else{
			$sql .= " WHERE 1=1";
		}
		
		if (!empty($data['query'])) {
			$sql .= " AND ".html_entity_decode($data['query'], ENT_QUOTES, 'UTF-8');
		} 

		if (!empty($data['add_table_filter'])) {
			$sql .= " AND ".html_entity_decode($data['add_table_filter'], ENT_QUOTES, 'UTF-8');
		}

		if (!empty($data['search'])) {
			$sql .= " AND (o.order_id LIKE '%".$this->db->escape($data['search'])."%' OR o.firstname LIKE '%".$this->db->escape($data['search'])."%' OR o.lastname LIKE '%".$this->db->escape($data['search'])."%' OR o.email LIKE '%".$this->db->escape($data['search'])."%' OR o.telephone LIKE '%".$this->db->escape($data['search'])."%' OR o.invoice_no LIKE '%".$this->db->escape($data['search'])."%')";
		}

		if (!empty($data['search_order_id'])) {
			$sql .= " AND o.order_id LIKE '%".$this->db->escape($data['search_order_id'])."%'";
		}

		if ($data['search_order_status_id'] != '') {
			$sql .= " AND o.order_status_id = '".(int)$data['search_order_status_id']."'";
		}

		$results = $this->db->query($sql);
		return $results->row['total'];
	}

	public function payment_address($order_info){
		if ($order_info['payment_address_format']) {
			$format = $order_info['payment_address_format'];
		} else {
			$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['payment_firstname'],
			'lastname'  => $order_info['payment_lastname'],
			'company'   => $order_info['payment_company'],
			'address_1' => $order_info['payment_address_1'],
			'address_2' => $order_info['payment_address_2'],
			'city'      => $order_info['payment_city'],
			'postcode'  => $order_info['payment_postcode'],
			'zone'      => $order_info['payment_zone'],
			'country'   => $order_info['payment_country']
		);

		$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		return $payment_address;
	}

	public function shipping_address($order_info){
		if ($order_info['shipping_address_format']) {
			$format = $order_info['shipping_address_format'];
		} else {
		$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
		}

		$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{country}'
		);

		$replace = array(
			'firstname' => $order_info['shipping_firstname'],
			'lastname'  => $order_info['shipping_lastname'],
			'company'   => $order_info['shipping_company'],
			'address_1' => $order_info['shipping_address_1'],
			'address_2' => $order_info['shipping_address_2'],
			'city'      => $order_info['shipping_city'],
			'postcode'  => $order_info['shipping_postcode'],
			'zone'      => $order_info['shipping_zone'],
			'country'   => $order_info['shipping_country']
		);

		$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

		return $shipping_address;
	}

	public function getOrderItems($order_info){
		$order_id = $order_info['order_id'];
		$items_table = '';
		
		$data['products'] = array();
		$products = $this->model_sale_order->getOrderProducts($order_id);

		foreach ($products as $product) {
			$option_data = array();

			$options = $this->model_sale_order->getOrderOptions($order_id, $product['order_product_id']);

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
							'href'  => $this->url->link('tool/upload/download', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&code=' . $upload_info['code'], true)
						);
					}
				}
			}

			$data['products'][] = array(
				'order_product_id' => $product['order_product_id'],
				'product_id'       => $product['product_id'],
				'name'    	 	   => $product['name'],
				'model'    		   => $product['model'],
				'option'   		   => $option_data,
				'quantity'		   => $product['quantity'],
				'price'    		   => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
				'total'    		   => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
				'href'     		   => $this->url->link('catalog/product/edit', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&product_id=' . $product['product_id'], true)
			);
		}

		$data['vouchers'] = array();

		$vouchers = $this->model_sale_order->getOrderVouchers($order_id);

		foreach ($vouchers as $voucher) {
			$data['vouchers'][] = array(
				'description' => $voucher['description'],
				'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']),
				'href'        => $this->url->link('sale/voucher/edit', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&voucher_id=' . $voucher['voucher_id'], true)
			);
		}

		$data['totals'] = array();

		$totals = $this->model_sale_order->getOrderTotals($order_id);

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		$items_table = $this->load->view('extension/module/'.$this->hb_template_folder.'/hb_orders_items'.$this->hb_template_extension, $data);

		return $items_table;
	}
	
	public function getCustomerDays($email){
		$query = $this->db->query("SELECT datediff(now(), date_added) as days FROM  `".DB_PREFIX."customer` WHERE `email` = '".$this->db->escape($email)."'");
		if ($query->row) {
			$days = $query->row['days'];
			$start_date = new DateTime();
			$end_date = (new $start_date)->add(new DateInterval("P{$days}D"));
			$dd = date_diff($start_date, $end_date);
			return $dd->y." years ". $dd->m." months ". $dd->d." days";
		}else{
			return "Guest";
		}
	}

	public function getCustomerTotalPurchases($email){
		$query = $this->db->query("SELECT sum(total) as total FROM `".DB_PREFIX."order` WHERE `email` = '".$this->db->escape($email)."' AND order_status_id IN ('".implode("','",$this->config->get('config_complete_status'))."')");	
		$amount = $query->row['total'];

		return $this->currency->format($amount, $this->config->get('config_currency'));
	}

	public function getCustomerTotalOrders($email){
		$query = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."order` WHERE `email` = '".$this->db->escape($email)."' AND order_status_id IN ('".implode("','",$this->config->get('config_complete_status'))."')");
		$total = $query->row['total'];
		return $total;
	}

	public function getCustomerIncompletePurchases($email){
		$query = $this->db->query("SELECT sum(total) as total FROM `".DB_PREFIX."order` WHERE `email` = '".$this->db->escape($email)."' AND order_status_id <> 0 AND order_status_id NOT IN ('".implode("','",$this->config->get('config_complete_status'))."')");	
		$amount = $query->row['total'];

		return $this->currency->format($amount, $this->config->get('config_currency'));
	}

	public function getCustomerIncompleteOrders($email){
		$query = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."order` WHERE `email` = '".$this->db->escape($email)."' AND order_status_id <> 0 AND order_status_id NOT IN ('".implode("','",$this->config->get('config_complete_status'))."')");
		$total = $query->row['total'];
		return $total;
	}

	public function getTotalOrders($order_status_id){
		$sql = "SELECT count(*) as total FROM `".DB_PREFIX."order`";
		if ($order_status_id == '0'){
			$sql .= " WHERE order_status_id = 0";
		}elseif ($order_status_id != 'ALL' && $order_status_id > 0){
			$sql .= " WHERE order_status_id = '".(int)$order_status_id."'";
		}else{
			$sql .= " WHERE order_status_id <> 0";
		}
		
		$query = $this->db->query($sql);
		$total = $query->row['total'];
		return $total;
	}

	public function getTotalOrdersValue($order_status_id){
		$sql = "SELECT sum(total) as total FROM `".DB_PREFIX."order`";
		if ($order_status_id == '0'){
			$sql .= " WHERE order_status_id = 0";
		}elseif ($order_status_id != 'ALL' && $order_status_id > 0){
			$sql .= " WHERE order_status_id = '".(int)$order_status_id."'";
		}else{
			$sql .= " WHERE order_status_id <> 0";
		}
		
		$query = $this->db->query($sql);
		$amount = $query->row['total'];
		return $this->currency->format($amount, $this->config->get('config_currency'));
	}

	public function getOrderHistories($order_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT oh.order_history_id, oh.order_status_id, oh.date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function delete_order_history($order_history_id){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "order_history` WHERE order_history_id = '" . (int)$order_history_id . "' ");
	}

	public function getTables() {
		$table_data = array();

		$query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");

		foreach ($query->rows as $result) {
			if (isset($result['Tables_in_' . DB_DATABASE])) {
				$table_data[] = str_replace(DB_PREFIX,'',$result['Tables_in_' . DB_DATABASE]);
			}
		}

		return $table_data;
	}

	public function getColumns($tablename) {
		$column_data = array();

		$query = $this->db->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" .DB_PREFIX. $tablename . "'");

		foreach ($query->rows as $result) {
			if (isset($result['COLUMN_NAME'])) {
				$column_data[] = $result['COLUMN_NAME'];
			}
		}

		return $column_data;
	}

	public function getdatatype($tablename, $column_name) {
		$query = $this->db->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" .DB_PREFIX. $tablename . "' AND COLUMN_NAME = '".$column_name."'");
		$data_type = $query->row['DATA_TYPE'];
		return $data_type;
	}

	public function value_statement($operator, $value){
		$operator_type1 = array('IN','NOT IN');
		$operator_type2 = array('LIKE','NOT LIKE');

		if (in_array($operator, $operator_type1)) {
			$statement = '('.$value.')';
		}elseif (in_array($operator, $operator_type2)) {
			$statement = '(\''.$value.'\')';
		}else{
			$statement = '\''.$value.'\' ';
		}	

		return $statement;
	}

	public function process_csv_upload($tablename, $file){
		$row = 0;
		$fields = array();
		
		$actual_columns = $this->getColumns($tablename);
			
		switch ($tablename) {
			
			case 'order':
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=0; $i < $field_count; $i++) { 
							$column_name = preg_replace( '/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $column[$i]);
							$fields[] = $column_name; 
						}
						$table_fields = implode(',',$fields);
					}
					$row++;
					
					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}

					$date_added_column_key = array_search('date_added', $fields);

					

					if($row == 1) continue;
					
					$order_id = $column[0];
					
					$values = array();
		
					for ($j=0; $j < $field_count; $j++) { 
						if ($date_added_column_key && $j == $date_added_column_key){
							$real_date_added = $this->db->query("SELECT date_added FROM `".DB_PREFIX."order` WHERE order_id = '".(int)$order_id."' LIMIT 1");
							$values[] ="'".$real_date_added->row['date_added']."'";
						}else{
							$values[] ="'".$this->db->escape($column[$j])."'";
						}
					}
					$table_values = implode(',',$values);
					
					$delete_sql = "DELETE FROM `".DB_PREFIX."order` WHERE order_id = '".(int)$order_id."'";
					$insert_sql = "INSERT INTO `".DB_PREFIX."order` (".$table_fields.") VALUES (".$table_values.");";
					$date_update_sql = "UPDATE `".DB_PREFIX."order` SET date_modified = now() WHERE order_id = '".(int)$order_id."'";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
					$this->db->query($date_update_sql);
				}
				break;

			default:
				while (($column = fgetcsv($file, 100000, ",")) !== FALSE) {
					$field_count = count($column);
					if ($row == 0) {
						for ($i=0; $i < $field_count; $i++) { 
							$column_name = preg_replace( '/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $column[$i]);
							$fields[] = $column_name; 
						}
						$table_fields = implode(',',$fields);
					}
					$row++;
					
					if ($fields != $actual_columns){
						$process_upload['warning'] = 'CSV Column Mismatch with the selected table!';
						return $process_upload;
						exit;
					}

					$column_id = $fields[0];
					$date_added_column_key = array_search('date_added', $fields);

					$order_id = $column[1];

					if($row == 1) continue;
					$values = array();
		
					for ($j=0; $j < $field_count; $j++) { 
						if ($date_added_column_key && $j == $date_added_column_key){
							$real_date_added = $this->db->query("SELECT date_added FROM `".DB_PREFIX.$tablename."` WHERE `".$column_id."` = '".$column[0]."' AND order_id = '".(int)$order_id."' LIMIT 1");
							$values[] ="'".$real_date_added->row['date_added']."'";
						}else{
							$values[] ="'".$this->db->escape($column[$j])."'";
						}
					}
					$table_values = implode(',',$values);

					$delete_sql = "DELETE FROM `".DB_PREFIX.$tablename."` WHERE `".$column_id."` = '".$column[0]."' AND order_id = '".(int)$order_id."'";
					$insert_sql = "INSERT INTO `".DB_PREFIX.$tablename."` (".$table_fields.") VALUES (".$table_values.");";

					$this->db->query($delete_sql);
					$this->db->query($insert_sql);
				}
				break;
		}

		$process_upload['success'] = 'CSV File Uploaded and tables updated';

		return $process_upload;
					
	}

	public function isExtensionInstalled($code){
		$query = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."extension` WHERE `code` = '".$this->db->escape($code)."'");	
		if ($query->row['total'] > 0){
			return true;
		}else{
			return false;
		}
	}
}
?>