<?php
class ModelCatalogProduct extends Model {

	public function getProductChartHistory($product_id, $data = array()) {	
		
		$sql = "SELECT * FROM " . DB_PREFIX . "product_stock_history WHERE product_id = '" . (int)$product_id . "' ORDER BY product_stock_history_id ASC";
					
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
				
	public function getProductHistory($product_id, $data = array()) {		
		$user_token = $this->session->data['user_token'];
		
		if (isset($data['filter_history_date_start']) && $data['filter_history_date_start']) {
			$date_start = $data['filter_history_date_start'];
		} else {
			$date_start = '';
		}

		if (isset($data['filter_history_date_end']) && $data['filter_history_date_end']) {
			$date_end = $data['filter_history_date_end'];
		} else {
			$date_end = '';
		}

		if (isset($data['filter_history_range'])) {
			$range_history = $data['filter_history_range'];
		} else {
			$range_history = 'all_time';
		}
		
		switch($range_history) 
		{
			case 'custom';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape($data['filter_history_date_start']) . "'";
				$date_end = " AND DATE(psh.date_added) <= '" . $this->db->escape($data['filter_history_date_end']) . "'";				
				break;
			case 'week';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-7 day'))) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";	
				break;			
			case 'month';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-30 day'))) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";					
				break;			
			case 'quarter';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-91 day'))) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";						
				break;
			case 'year';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-365 day'))) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";					
				break;
			case 'current_week';
				$date_start = "DATE(psh.date_added) >= CURDATE() - WEEKDAY(CURDATE())";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";			
				break;	
			case 'current_month';
				$date_start = "YEAR(psh.date_added) = YEAR(CURDATE())";
				$date_end = " AND MONTH(psh.date_added) = MONTH(CURDATE())";			
				break;
			case 'current_quarter';
				$date_start = "QUARTER(psh.date_added) = QUARTER(CURDATE())";
				$date_end = " AND YEAR(psh.date_added) = YEAR(CURDATE())";					
				break;					
			case 'current_year';
				$date_start = "YEAR(psh.date_added) = YEAR(CURDATE())";
				$date_end = '';				
				break;					
			case 'last_week';
				$date_start = "DATE(psh.date_added) >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+5 DAY";
				$date_end = " AND DATE(psh.date_added) < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-2 DAY";				
				break;	
			case 'last_month';
				$date_start = "DATE(psh.date_added) >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y/%m/01')";
				$date_end = " AND DATE(psh.date_added) < DATE_FORMAT(CURRENT_DATE, '%Y/%m/01')";				
				break;
			case 'last_quarter';
				$date_start = "DATE(psh.date_added) >= CASE QUARTER(NOW()) WHEN 1 THEN DATE_FORMAT(NOW() - INTERVAL 1 YEAR, '%Y/10/01') WHEN 2 THEN DATE_FORMAT(NOW(), '%Y/01/01') WHEN 3 THEN DATE_FORMAT(NOW(), '%Y/04/01') WHEN 4 THEN DATE_FORMAT(NOW(), '%Y/07/01') END";
				$date_end = " AND DATE(psh.date_added) <= CASE QUARTER(NOW()) WHEN 1 THEN DATE_FORMAT(NOW() - INTERVAL 1 YEAR, '%Y/12/31') WHEN 2 THEN DATE_FORMAT(NOW(), '%Y/03/31') WHEN 3 THEN DATE_FORMAT(NOW(), '%Y/06/30') WHEN 4 THEN DATE_FORMAT(NOW(), '%Y/09/30') END";
				break;					
			case 'last_year';
				$date_start = "DATE(psh.date_added) >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 YEAR, '%Y/01/01')";
				$date_end = " AND DATE(psh.date_added) < DATE_FORMAT(CURRENT_DATE, '%Y/01/01')";				
				break;					
			case 'all_time';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d','0')) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";						
				break;	
		}

		$option_history = '';
		if (!empty($data['filter_history_option'])) {
			$option_history = " AND CONCAT(psh.product_option_id,psh.option_id,psh.option_value_id) = '" . $data['filter_history_option'] . "'";
		}

		$supplier_history = '';
		if (!empty($data['filter_history_supplier'])) {
			$supplier_history = " AND psh.supplier_id = '" . (int)$data['filter_history_supplier'] . "'";
		}
		
		if (isset($data['filter_history_option']) && $data['filter_history_option'] == 0) {			
			$sql = "SELECT psh.*, psh.product_stock_history_id AS psh_id, (psh.price-psh.cost) AS profit, (((psh.price-psh.cost)/psh.price)*100) AS profit_margin, (((psh.price-psh.cost)/psh.cost)*100) AS profit_markup, (SELECT s.name FROM `" . DB_PREFIX . "supplier` s WHERE s.supplier_id = psh.supplier_id) AS supplier FROM `" . DB_PREFIX . "product_stock_history` psh WHERE psh.product_id = '" . (int)$product_id . "' AND (" . $date_start . $date_end . ")" . $supplier_history;
		} else {
			$sql = "SELECT psh.*, psh.product_option_stock_history_id AS psh_id, (psh.price-psh.cost) AS profit, (((psh.price-psh.cost)/psh.price)*100) AS profit_margin, (((psh.price-psh.cost)/psh.cost)*100) AS profit_markup, (SELECT s.name FROM `" . DB_PREFIX . "supplier` s WHERE s.supplier_id = psh.supplier_id) AS supplier FROM `" . DB_PREFIX . "product_option_stock_history` psh WHERE psh.product_id = '" . (int)$product_id . "' AND (" . $date_start . $date_end . ")" . $option_history . $supplier_history;		
		}

		$sort_data = array(
			'psh.date_added',
			'psh_id',
			'psh.comment',
			'supplier',
			'psh.costing_method',			
			'psh.restock_quantity',
			'psh.stock_quantity',
			'psh.restock_cost',
			'psh.cost',
			'psh.price',
			'profit',
			'profit_margin',
			'profit_markup'
		);
		
		if (isset($data['sort_history']) && in_array($data['sort_history'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort_history'];	
		} else {
			$sql .= " ORDER BY psh_id";	
		}
			
		if (isset($data['order_history']) && ($data['order_history'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
					
		if (isset($data['start_history']) || isset($data['limit_history'])) {
			if ($data['start_history'] < 0) {
				$data['start_history'] = 0;
			}				

			if ($data['limit_history'] < 1) {
				$data['limit_history'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start_history'] . "," . (int)$data['limit_history'];
		}
					
		$query = $this->db->query($sql);
		
		return $query->rows;
	}
	
	public function getProductHistoryTotal($product_id, $data = array()) {
		if (isset($data['filter_history_date_start']) && $data['filter_history_date_start']) {
			$date_start = $data['filter_history_date_start'];
		} else {
			$date_start = '';
		}

		if (isset($data['filter_history_date_end']) && $data['filter_history_date_end']) {
			$date_end = $data['filter_history_date_end'];
		} else {
			$date_end = '';
		}

		if (isset($data['filter_history_range'])) {
			$range_history = $data['filter_history_range'];
		} else {
			$range_history = 'all_time';
		}

		switch($range_history) 
		{
			case 'custom';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape($data['filter_history_date_start']) . "'";
				$date_end = " AND DATE(psh.date_added) <= '" . $this->db->escape($data['filter_history_date_end']) . "'";				
				break;
			case 'week';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-7 day'))) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";	
				break;			
			case 'month';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-30 day'))) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";					
				break;			
			case 'quarter';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-91 day'))) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";						
				break;
			case 'year';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-365 day'))) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";					
				break;
			case 'current_week';
				$date_start = "DATE(psh.date_added) >= CURDATE() - WEEKDAY(CURDATE())";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";			
				break;	
			case 'current_month';
				$date_start = "YEAR(psh.date_added) = YEAR(CURDATE())";
				$date_end = " AND MONTH(psh.date_added) = MONTH(CURDATE())";			
				break;
			case 'current_quarter';
				$date_start = "QUARTER(psh.date_added) = QUARTER(CURDATE())";
				$date_end = " AND YEAR(psh.date_added) = YEAR(CURDATE())";					
				break;					
			case 'current_year';
				$date_start = "YEAR(psh.date_added) = YEAR(CURDATE())";
				$date_end = '';			
				break;					
			case 'last_week';
				$date_start = "DATE(psh.date_added) >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+5 DAY";
				$date_end = " AND DATE(psh.date_added) < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-2 DAY";				
				break;	
			case 'last_month';
				$date_start = "DATE(psh.date_added) >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y/%m/01')";
				$date_end = " AND DATE(psh.date_added) < DATE_FORMAT(CURRENT_DATE, '%Y/%m/01')";				
				break;
			case 'last_quarter';
				$date_start = "DATE(psh.date_added) >= CASE QUARTER(NOW()) WHEN 1 THEN DATE_FORMAT(NOW() - INTERVAL 1 YEAR, '%Y/10/01') WHEN 2 THEN DATE_FORMAT(NOW(), '%Y/01/01') WHEN 3 THEN DATE_FORMAT(NOW(), '%Y/04/01') WHEN 4 THEN DATE_FORMAT(NOW(), '%Y/07/01') END";
				$date_end = " AND DATE(psh.date_added) <= CASE QUARTER(NOW()) WHEN 1 THEN DATE_FORMAT(NOW() - INTERVAL 1 YEAR, '%Y/12/31') WHEN 2 THEN DATE_FORMAT(NOW(), '%Y/03/31') WHEN 3 THEN DATE_FORMAT(NOW(), '%Y/06/30') WHEN 4 THEN DATE_FORMAT(NOW(), '%Y/09/30') END";
				break;					
			case 'last_year';
				$date_start = "DATE(psh.date_added) >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 YEAR, '%Y/01/01')";
				$date_end = " AND DATE(psh.date_added) < DATE_FORMAT(CURRENT_DATE, '%Y/01/01')";				
				break;					
			case 'all_time';
				$date_start = "DATE(psh.date_added) >= '" . $this->db->escape(date('Y-m-d','0')) . "'";
				$date_end = " AND DATE(psh.date_added) <= DATE (NOW())";						
				break;	
		}

		$option_history = '';
		if (!empty($data['filter_history_option'])) {
			$option_history = " AND CONCAT(psh.product_option_id,psh.option_id,psh.option_value_id) = '" . $data['filter_history_option'] . "'";
		}

		$supplier_history = '';
		if (!empty($data['filter_history_supplier'])) {
			$supplier_history = " AND psh.supplier_id = '" . (int)$data['filter_history_supplier'] . "'";
		}
		
		if (isset($data['filter_history_option']) && $data['filter_history_option'] == 0) {				
			$sql = "SELECT COUNT(psh.product_id) AS total FROM " . DB_PREFIX . "product_stock_history psh WHERE psh.product_id = '" . (int)$product_id . "' AND (" . $date_start . $date_end . ")" . $supplier_history;
		} else {
			$sql = "SELECT COUNT(psh.product_id) AS total FROM " . DB_PREFIX . "product_option_stock_history psh WHERE psh.product_id = '" . (int)$product_id . "' AND (" . $date_start . $date_end . ")" . $option_history . $supplier_history;
		}
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}
	
	public function getProductSales($product_id, $data = array()) {		
		$user_token = $this->session->data['user_token'];
		
		if (isset($data['filter_sale_date_start']) && $data['filter_sale_date_start']) {
			$date_start = $data['filter_sale_date_start'];
		} else {
			$date_start = '';
		}

		if (isset($data['filter_sale_date_end']) && $data['filter_sale_date_end']) {
			$date_end = $data['filter_sale_date_end'];
		} else {
			$date_end = '';
		}

		if (isset($data['filter_sale_range'])) {
			$range_sale = $data['filter_sale_range'];
		} else {
			$range_sale = 'current_year';
		}
		
		switch($range_sale) 
		{
			case 'custom';
				$date_start = "DATE(date_added) >= '" . $this->db->escape($data['filter_sale_date_start']) . "'";
				$date_end = " AND DATE(date_added) <= '" . $this->db->escape($data['filter_sale_date_end']) . "'";				
				break;
			case 'today';
				$date_start = "DATE(date_added) = CURDATE()";
				$date_end = '';
				break;
			case 'yesterday';
				$date_start = "DATE(date_added) >= DATE_ADD(CURDATE(), INTERVAL -1 DAY)";
				$date_end = " AND DATE(date_added) < CURDATE()";
				break;					
			case 'week';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-7 day'))) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";	
				break;			
			case 'month';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-30 day'))) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";					
				break;			
			case 'quarter';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-91 day'))) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";						
				break;
			case 'year';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-365 day'))) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";					
				break;
			case 'current_week';
				$date_start = "DATE(date_added) >= CURDATE() - WEEKDAY(CURDATE())";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";			
				break;	
			case 'current_month';
				$date_start = "YEAR(date_added) = YEAR(CURDATE())";
				$date_end = " AND MONTH(date_added) = MONTH(CURDATE())";			
				break;
			case 'current_quarter';
				$date_start = "QUARTER(date_added) = QUARTER(CURDATE())";
				$date_end = " AND YEAR(date_added) = YEAR(CURDATE())";					
				break;					
			case 'current_year';
				$date_start = "YEAR(date_added) = YEAR(CURDATE())";
				$date_end = '';			
				break;					
			case 'last_week';
				$date_start = "DATE(date_added) >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+5 DAY";
				$date_end = " AND DATE(date_added) < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-2 DAY";				
				break;	
			case 'last_month';
				$date_start = "DATE(date_added) >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y/%m/01')";
				$date_end = " AND DATE(date_added) < DATE_FORMAT(CURRENT_DATE, '%Y/%m/01')";				
				break;
			case 'last_quarter';
				$date_start = "DATE(date_added) >= CASE QUARTER(NOW()) WHEN 1 THEN DATE_FORMAT(NOW() - INTERVAL 1 YEAR, '%Y/10/01') WHEN 2 THEN DATE_FORMAT(NOW(), '%Y/01/01') WHEN 3 THEN DATE_FORMAT(NOW(), '%Y/04/01') WHEN 4 THEN DATE_FORMAT(NOW(), '%Y/07/01') END";
				$date_end = " AND DATE(date_added) <= CASE QUARTER(NOW()) WHEN 1 THEN DATE_FORMAT(NOW() - INTERVAL 1 YEAR, '%Y/12/31') WHEN 2 THEN DATE_FORMAT(NOW(), '%Y/03/31') WHEN 3 THEN DATE_FORMAT(NOW(), '%Y/06/30') WHEN 4 THEN DATE_FORMAT(NOW(), '%Y/09/30') END";
				break;					
			case 'last_year';
				$date_start = "DATE(date_added) >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 YEAR, '%Y/01/01')";
				$date_end = " AND DATE(date_added) < DATE_FORMAT(CURRENT_DATE, '%Y/01/01')";				
				break;					
			case 'all_time';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d','0')) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";						
				break;	
		}

		$date = ' AND (' . $date_start . $date_end . ')';

		$order_status = '';
		if (!empty($data['filter_sale_order_status'])) {
			$order_status = " AND (";
			$implode = array();
			foreach ($data['filter_sale_order_status'] as $filter_sale_order_status) {
				$implode[] = "o.order_status_id = '" . (int)$filter_sale_order_status . "'";
			}

			if ($implode) {
				$order_status .= implode(" OR ", $implode) . "";
			}
			$order_status .= ")";
		} else {
		$order_status = ' AND o.order_status_id > 0';
		}

		$sale_option = '';
		if (!empty($data['filter_sale_option'])) {
			$sale_option = " AND (";
			$implode = array();
			foreach ($data['filter_sale_option'] as $filter_sale_option) {
				$implode[] = "(SELECT DISTINCT oo.order_id FROM `" . DB_PREFIX . "order_option` oo WHERE op.order_product_id = oo.order_product_id AND LCASE(CONCAT(oo.name, oo.value, oo.type)) = '" . $filter_sale_option . "')";
			}

			if ($implode) {
				$sale_option .= implode(" AND ", $implode) . "";
			}
			$sale_option .= ")";
		}
				
		$sql = "SELECT o.*, 
		op.product_id, 
		op.order_product_id, 
		op.order_id AS product_order_id, 
		o.date_added AS product_date_added, 
		op.name AS product_name, 
		(SELECT GROUP_CONCAT(CONCAT(oo.name,': ',oo.value) SEPARATOR '<br>') FROM `" . DB_PREFIX . "order_option` oo WHERE op.order_product_id = oo.order_product_id AND (oo.type != 'textarea' OR oo.type != 'file' OR oo.type != 'date' OR oo.type != 'datetime' OR oo.type != 'time') ORDER BY op.order_product_id) AS product_option,  
		SUM(op.quantity) AS product_sold, 
		SUM(op.total) AS product_total_excl_vat, 
		SUM(op.tax*op.quantity) AS product_tax, 
		SUM(op.total+(op.tax*op.quantity)) AS product_total_incl_vat, 
		SUM(op.total) AS product_revenue, 
		SUM(opc.cost*op.quantity) AS product_cost, 
		SUM(op.total - (opc.cost*op.quantity)) AS product_profit, 
		SUM(((op.total - (opc.cost*op.quantity)) / op.total)*100) AS product_margin, 
		SUM(((op.total - (opc.cost*op.quantity)) / (opc.cost*op.quantity))*100) AS product_markup, 		
		(SELECT SUM(op.quantity) FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) WHERE op.product_id = '" . (int)$product_id . "'" . $date . $order_status . $sale_option . ") AS product_sold_total, 
		(SELECT SUM(op.total) FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) WHERE op.product_id = '" . (int)$product_id . "'" . $date . $order_status . $sale_option . ") AS product_total_excl_vat_total, 
		(SELECT SUM(op.tax*op.quantity) FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) WHERE op.product_id = '" . (int)$product_id . "'" . $date . $order_status . $sale_option . ") AS product_tax_total, 
		(SELECT SUM(op.total+(op.tax*op.quantity)) FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) WHERE op.product_id = '" . (int)$product_id . "'" . $date . $order_status . $sale_option . ") AS product_total_incl_vat_total, 
		(SELECT SUM(op.total) FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) WHERE op.product_id = '" . (int)$product_id . "'" . $date . $order_status . $sale_option . ") AS product_revenue_total, 
		(SELECT SUM(opc.cost*op.quantity) FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) INNER JOIN `" . DB_PREFIX . "order_product_cost` opc ON (op.order_product_id = opc.order_product_id) WHERE op.product_id = '" . (int)$product_id . "'" . $date . $order_status . $sale_option . ") AS product_cost_total, 
		(SELECT SUM(op.total - (opc.cost*op.quantity)) FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) INNER JOIN `" . DB_PREFIX . "order_product_cost` opc ON (op.order_product_id = opc.order_product_id) WHERE op.product_id = '" . (int)$product_id . "'" . $date . $order_status . $sale_option . ") AS product_profit_total 
				
		FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) INNER JOIN `" . DB_PREFIX . "order_product_cost` opc ON (op.order_product_id = opc.order_product_id) WHERE op.product_id = '" . (int)$product_id . "'" . $date . $order_status . $sale_option;
			
		$sql .= " GROUP BY op.order_id, product_option";

		$sort_data = array(
			'product_order_id',
			'product_date_added',
			'product_option',
			'product_sold',
			'product_total_excl_vat',
			'product_tax',
			'product_total_incl_vat',			
			'product_revenue',
			'product_cost',												
			'product_profit',
			'product_margin',
			'product_markup'
		);	
			
		if (isset($data['sort_sale']) && in_array($data['sort_sale'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort_sale'];	
		} else {
			$sql .= " ORDER BY product_date_added";	
		}
			
		if (isset($data['order_sale']) && ($data['order_sale'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
					
		if (isset($data['start_sale']) || isset($data['limit_sale'])) {
			if ($data['start_sale'] < 0) {
				$data['start_sale'] = 0;
			}				

			if ($data['limit_sale'] < 1) {
				$data['limit_sale'] = 20;
			}	
		
			$sql .= " LIMIT " . (int)$data['start_sale'] . "," . (int)$data['limit_sale'];
		}
		
		$query = $this->db->query($sql);
		
		return $query->rows;
	}	

	public function getProductSalesTotal($product_id, $data = array()) {
		if (isset($data['filter_sale_date_start']) && $data['filter_sale_date_start']) {
			$date_start = $data['filter_sale_date_start'];
		} else {
			$date_start = '';
		}

		if (isset($data['filter_sale_date_end']) && $data['filter_sale_date_end']) {
			$date_end = $data['filter_sale_date_end'];
		} else {
			$date_end = '';
		}

		if (isset($data['filter_sale_range'])) {
			$range_sale = $data['filter_sale_range'];
		} else {
			$range_sale = 'current_year';
		}
		
		switch($range_sale) 
		{
			case 'custom';
				$date_start = "DATE(date_added) >= '" . $this->db->escape($data['filter_sale_date_start']) . "'";
				$date_end = " AND DATE(date_added) <= '" . $this->db->escape($data['filter_sale_date_end']) . "'";				
				break;
			case 'today';
				$date_start = "DATE(date_added) = CURDATE()";
				$date_end = '';
				break;
			case 'yesterday';
				$date_start = "DATE(date_added) >= DATE_ADD(CURDATE(), INTERVAL -1 DAY)";
				$date_end = " AND DATE(date_added) < CURDATE()";
				break;					
			case 'week';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-7 day'))) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";	
				break;			
			case 'month';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-30 day'))) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";					
				break;			
			case 'quarter';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-91 day'))) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";						
				break;
			case 'year';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d', strtotime('-365 day'))) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";					
				break;
			case 'current_week';
				$date_start = "DATE(date_added) >= CURDATE() - WEEKDAY(CURDATE())";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";			
				break;	
			case 'current_month';
				$date_start = "YEAR(date_added) = YEAR(CURDATE())";
				$date_end = " AND MONTH(date_added) = MONTH(CURDATE())";			
				break;
			case 'current_quarter';
				$date_start = "QUARTER(date_added) = QUARTER(CURDATE())";
				$date_end = " AND YEAR(date_added) = YEAR(CURDATE())";					
				break;					
			case 'current_year';
				$date_start = "YEAR(date_added) = YEAR(CURDATE())";
				$date_end = '';			
				break;					
			case 'last_week';
				$date_start = "DATE(date_added) >= CURDATE() - INTERVAL DAYOFWEEK(CURDATE())+5 DAY";
				$date_end = " AND DATE(date_added) < CURDATE() - INTERVAL DAYOFWEEK(CURDATE())-2 DAY";				
				break;	
			case 'last_month';
				$date_start = "DATE(date_added) >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 MONTH, '%Y/%m/01')";
				$date_end = " AND DATE(date_added) < DATE_FORMAT(CURRENT_DATE, '%Y/%m/01')";				
				break;
			case 'last_quarter';
				$date_start = "DATE(date_added) >= CASE QUARTER(NOW()) WHEN 1 THEN DATE_FORMAT(NOW() - INTERVAL 1 YEAR, '%Y/10/01') WHEN 2 THEN DATE_FORMAT(NOW(), '%Y/01/01') WHEN 3 THEN DATE_FORMAT(NOW(), '%Y/04/01') WHEN 4 THEN DATE_FORMAT(NOW(), '%Y/07/01') END";
				$date_end = " AND DATE(date_added) <= CASE QUARTER(NOW()) WHEN 1 THEN DATE_FORMAT(NOW() - INTERVAL 1 YEAR, '%Y/12/31') WHEN 2 THEN DATE_FORMAT(NOW(), '%Y/03/31') WHEN 3 THEN DATE_FORMAT(NOW(), '%Y/06/30') WHEN 4 THEN DATE_FORMAT(NOW(), '%Y/09/30') END";
				break;					
			case 'last_year';
				$date_start = "DATE(date_added) >= DATE_FORMAT(CURRENT_DATE - INTERVAL 1 YEAR, '%Y/01/01')";
				$date_end = " AND DATE(date_added) < DATE_FORMAT(CURRENT_DATE, '%Y/01/01')";				
				break;					
			case 'all_time';
				$date_start = "DATE(date_added) >= '" . $this->db->escape(date('Y-m-d','0')) . "'";
				$date_end = " AND DATE(date_added) <= DATE (NOW())";						
				break;	
		}

		$sql = "SELECT COUNT(op.order_product_id) AS total FROM `" . DB_PREFIX . "order` o INNER JOIN `" . DB_PREFIX . "order_product` op ON (o.order_id = op.order_id) WHERE op.product_id = '" . (int)$product_id . "'";

		if (!empty($data['filter_sale_order_status'])) {
			$sql .= " AND (";
			$implode = array();
			foreach ($data['filter_sale_order_status'] as $filter_sale_order_status) {
				$implode[] = "o.order_status_id = '" . (int)$filter_sale_order_status . "'";
			}

			if ($implode) {
				$sql .= implode(" OR ", $implode) . "";
			}
			$sql .= ")";
		} else {
			$sql .= " AND o.order_status_id > '0'";
		}
		
		if (!empty($data['filter_sale_option'])) {
			$sql .= " AND (";
			$implode = array();
			foreach ($data['filter_sale_option'] as $filter_sale_option) {
				$implode[] = "(SELECT DISTINCT oo.order_id FROM `" . DB_PREFIX . "order_option` oo WHERE op.order_product_id = oo.order_product_id AND HEX(CONCAT(oo.name, oo.value, oo.type)) = '" . $filter_sale_option . "')";
			}

			if ($implode) {
				$sql .= implode(" AND ", $implode) . "";
			}
			$sql .= ")";
		}
				
		$sql .= ' AND (' . $date_start . $date_end . ')';
				
		$query = $this->db->query($sql);
		
		return $query->row['total'];
	}
	
	public function calculate($tax_class_id, $value, $calculate = true, $fixed_taxes = true) {
		if ($tax_class_id == 0) return $value;
			if ($tax_class_id && $calculate) {
				$amount = $this->getTax($tax_class_id, $value, $fixed_taxes);
				return $amount;
			} else {
				return $value;
			}
	}
	
	public function getTax($tax_class_id, $value, $fixed_taxes = true) {
		$amount = 0;
		$tax_rates = $this->getRates($tax_class_id, $value);
	
		foreach ($tax_rates as $tax_rate) {
			if (!$fixed_taxes && $tax_rate['type'] == 'F') {
				
			} else {
				$amount += $tax_rate['amount'];
			}
		}
		
		return $value + $amount;
	
		return $amount;
	}
	
	public function getTaxRates($tax_class_id) {
		$tax_rates = array();
		
		$customer_group_id = $this->config->get('config_customer_group_id');
		
		if ($this->config->get('adv_price_tax_store_based') or !$this->config->get('adv_price_tax')) {
			$this->store_address = array(
				'country_id' => $this->config->get('config_country_id'),
				'zone_id'    => $this->config->get('config_zone_id')
			);
			$this->shipping_address = array(
				'country_id' => $this->config->get('config_country_id'),
				'zone_id'    => $this->config->get('config_zone_id')
			);
			$this->payment_address = array(
				'country_id' => $this->config->get('config_country_id'),
				'zone_id'    => $this->config->get('config_zone_id')
			);
		} else {
			$this->store_address = array(
				'country_id' => $this->config->get('config_country_id'),
				'zone_id'    => $this->config->get('config_zone_id')
			);
			$this->shipping_address = array(
				'country_id' => $this->config->get('adv_price_tax_country_id'),
				'zone_id'    => $this->config->get('adv_price_tax_zone_id')
			);
			$this->payment_address = array(
				'country_id' => $this->config->get('adv_price_tax_country_id'),
				'zone_id'    => $this->config->get('adv_price_tax_zone_id')
			);
		}
		
		if ($this->shipping_address) {
			$tax_query = $this->db->query("SELECT tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority FROM " . DB_PREFIX . "tax_rule tr1 LEFT JOIN " . DB_PREFIX . "tax_rate tr2 ON (tr1.tax_rate_id = tr2.tax_rate_id) INNER JOIN " . DB_PREFIX . "tax_rate_to_customer_group tr2cg ON (tr2.tax_rate_id = tr2cg.tax_rate_id) LEFT JOIN " . DB_PREFIX . "zone_to_geo_zone z2gz ON (tr2.geo_zone_id = z2gz.geo_zone_id) LEFT JOIN " . DB_PREFIX . "geo_zone gz ON (tr2.geo_zone_id = gz.geo_zone_id) WHERE tr1.tax_class_id = '" . (int)$tax_class_id . "' AND tr1.based = 'shipping' AND tr2cg.customer_group_id = '" . (int)$customer_group_id . "' AND z2gz.country_id = '" . (int)$this->shipping_address['country_id'] . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->shipping_address['zone_id'] . "') ORDER BY tr1.priority ASC");
				
			foreach ($tax_query->rows as $result) {
				$tax_rates[$result['tax_rate_id']] = array(
					'tax_rate_id' => $result['tax_rate_id'],
					'name'        => $result['name'],
					'rate'        => $result['rate'],
					'type'        => $result['type'],
					'priority'    => $result['priority']
				);
			}
		}
		
		if ($this->payment_address) {
			$tax_query = $this->db->query("SELECT tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority FROM " . DB_PREFIX . "tax_rule tr1 LEFT JOIN " . DB_PREFIX . "tax_rate tr2 ON (tr1.tax_rate_id = tr2.tax_rate_id) INNER JOIN " . DB_PREFIX . "tax_rate_to_customer_group tr2cg ON (tr2.tax_rate_id = tr2cg.tax_rate_id) LEFT JOIN " . DB_PREFIX . "zone_to_geo_zone z2gz ON (tr2.geo_zone_id = z2gz.geo_zone_id) LEFT JOIN " . DB_PREFIX . "geo_zone gz ON (tr2.geo_zone_id = gz.geo_zone_id) WHERE tr1.tax_class_id = '" . (int)$tax_class_id . "' AND tr1.based = 'payment' AND tr2cg.customer_group_id = '" . (int)$customer_group_id . "' AND z2gz.country_id = '" . (int)$this->payment_address['country_id'] . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->payment_address['zone_id'] . "') ORDER BY tr1.priority ASC");
				
			foreach ($tax_query->rows as $result) {
				$tax_rates[$result['tax_rate_id']] = array(
					'tax_rate_id' => $result['tax_rate_id'],
					'name'        => $result['name'],
					'rate'        => $result['rate'],
					'type'        => $result['type'],
					'priority'    => $result['priority']
				);
			}
		}
		
		if ($this->store_address) {
			$tax_query = $this->db->query("SELECT tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority FROM " . DB_PREFIX . "tax_rule tr1 LEFT JOIN " . DB_PREFIX . "tax_rate tr2 ON (tr1.tax_rate_id = tr2.tax_rate_id) INNER JOIN " . DB_PREFIX . "tax_rate_to_customer_group tr2cg ON (tr2.tax_rate_id = tr2cg.tax_rate_id) LEFT JOIN " . DB_PREFIX . "zone_to_geo_zone z2gz ON (tr2.geo_zone_id = z2gz.geo_zone_id) LEFT JOIN " . DB_PREFIX . "geo_zone gz ON (tr2.geo_zone_id = gz.geo_zone_id) WHERE tr1.tax_class_id = '" . (int)$tax_class_id . "' AND tr1.based = 'store' AND tr2cg.customer_group_id = '" . (int)$customer_group_id . "' AND z2gz.country_id = '" . (int)$this->store_address['country_id'] . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->store_address['zone_id'] . "') ORDER BY tr1.priority ASC");
		
			foreach ($tax_query->rows as $result) {
				$tax_rates[$result['tax_rate_id']] = array(
					'tax_rate_id' => $result['tax_rate_id'],
					'name'        => $result['name'],
					'rate'        => $result['rate'],
					'type'        => $result['type'],
					'priority'    => $result['priority']
				);
			}
		}
		
		return $tax_rates;
	}
	
	public function getRates($tax_class_id, $value) {
		$tax_rates = array();
	
		$customer_group_id = $this->config->get('config_customer_group_id');
		
		if ($this->config->get('adv_price_tax_store_based') or !$this->config->get('adv_price_tax')) {
			$this->store_address = array(
				'country_id' => $this->config->get('config_country_id'),
				'zone_id'    => $this->config->get('config_zone_id')
			);
			$this->shipping_address = array(
				'country_id' => $this->config->get('config_country_id'),
				'zone_id'    => $this->config->get('config_zone_id')
			);
			$this->payment_address = array(
				'country_id' => $this->config->get('config_country_id'),
				'zone_id'    => $this->config->get('config_zone_id')
			);
		} else {
			$this->store_address = array(
				'country_id' => $this->config->get('config_country_id'),
				'zone_id'    => $this->config->get('config_zone_id')
			);
			$this->shipping_address = array(
				'country_id' => $this->config->get('adv_price_tax_country_id'),
				'zone_id'    => $this->config->get('adv_price_tax_zone_id')
			);
			$this->payment_address = array(
				'country_id' => $this->config->get('adv_price_tax_country_id'),
				'zone_id'    => $this->config->get('adv_price_tax_zone_id')
			);
		}
		
		if ($this->shipping_address) {
			$tax_query = $this->db->query("SELECT tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority FROM " . DB_PREFIX . "tax_rule tr1 LEFT JOIN " . DB_PREFIX . "tax_rate tr2 ON (tr1.tax_rate_id = tr2.tax_rate_id) INNER JOIN " . DB_PREFIX . "tax_rate_to_customer_group tr2cg ON (tr2.tax_rate_id = tr2cg.tax_rate_id) LEFT JOIN " . DB_PREFIX . "zone_to_geo_zone z2gz ON (tr2.geo_zone_id = z2gz.geo_zone_id) LEFT JOIN " . DB_PREFIX . "geo_zone gz ON (tr2.geo_zone_id = gz.geo_zone_id) WHERE tr1.tax_class_id = '" . (int)$tax_class_id . "' AND tr1.based = 'shipping' AND tr2cg.customer_group_id = '" . (int)$customer_group_id . "' AND z2gz.country_id = '" . (int)$this->shipping_address['country_id'] . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->shipping_address['zone_id'] . "') ORDER BY tr1.priority ASC");
				
			foreach ($tax_query->rows as $result) {
				$tax_rates[$result['tax_rate_id']] = array(
					'tax_rate_id' => $result['tax_rate_id'],
					'name'        => $result['name'],
					'rate'        => $result['rate'],
					'type'        => $result['type'],
					'priority'    => $result['priority']
				);
			}
		}
		
		if ($this->payment_address) {
			$tax_query = $this->db->query("SELECT tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority FROM " . DB_PREFIX . "tax_rule tr1 LEFT JOIN " . DB_PREFIX . "tax_rate tr2 ON (tr1.tax_rate_id = tr2.tax_rate_id) INNER JOIN " . DB_PREFIX . "tax_rate_to_customer_group tr2cg ON (tr2.tax_rate_id = tr2cg.tax_rate_id) LEFT JOIN " . DB_PREFIX . "zone_to_geo_zone z2gz ON (tr2.geo_zone_id = z2gz.geo_zone_id) LEFT JOIN " . DB_PREFIX . "geo_zone gz ON (tr2.geo_zone_id = gz.geo_zone_id) WHERE tr1.tax_class_id = '" . (int)$tax_class_id . "' AND tr1.based = 'payment' AND tr2cg.customer_group_id = '" . (int)$customer_group_id . "' AND z2gz.country_id = '" . (int)$this->payment_address['country_id'] . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->payment_address['zone_id'] . "') ORDER BY tr1.priority ASC");
				
			foreach ($tax_query->rows as $result) {
				$tax_rates[$result['tax_rate_id']] = array(
					'tax_rate_id' => $result['tax_rate_id'],
					'name'        => $result['name'],
					'rate'        => $result['rate'],
					'type'        => $result['type'],
					'priority'    => $result['priority']
				);
			}
		}
		
	
		if ($this->store_address) {
			$tax_query = $this->db->query("SELECT tr2.tax_rate_id, tr2.name, tr2.rate, tr2.type, tr1.priority FROM " . DB_PREFIX . "tax_rule tr1 LEFT JOIN " . DB_PREFIX . "tax_rate tr2 ON (tr1.tax_rate_id = tr2.tax_rate_id) INNER JOIN " . DB_PREFIX . "tax_rate_to_customer_group tr2cg ON (tr2.tax_rate_id = tr2cg.tax_rate_id) LEFT JOIN " . DB_PREFIX . "zone_to_geo_zone z2gz ON (tr2.geo_zone_id = z2gz.geo_zone_id) LEFT JOIN " . DB_PREFIX . "geo_zone gz ON (tr2.geo_zone_id = gz.geo_zone_id) WHERE tr1.tax_class_id = '" . (int)$tax_class_id . "' AND tr1.based = 'store' AND tr2cg.customer_group_id = '" . (int)$customer_group_id . "' AND z2gz.country_id = '" . (int)$this->store_address['country_id'] . "' AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$this->store_address['zone_id'] . "') ORDER BY tr1.priority ASC");
				
			foreach ($tax_query->rows as $result) {
				$tax_rates[$result['tax_rate_id']] = array(
					'tax_rate_id' => $result['tax_rate_id'],
					'name'        => $result['name'],
					'rate'        => $result['rate'],
					'type'        => $result['type'],
					'priority'    => $result['priority']
				);
			}
		}
	
		$tax_rate_data = array();
	
		foreach ($tax_rates as $tax_rate) {
			if (isset($tax_rate_data[$tax_rate['tax_rate_id']])) {
				$amount = $tax_rate_data[$tax_rate['tax_rate_id']]['amount'];
			} else {
				$amount = 0;
			}
				
			if ($tax_rate['type'] == 'F') {
				$amount += $tax_rate['rate'];
			} elseif ($tax_rate['type'] == 'P') {
				$amount += (($value / 100) * $tax_rate['rate']);
			}
	
			$tax_rate_data[$tax_rate['tax_rate_id']] = array(
				'tax_rate_id' => $tax_rate['tax_rate_id'],
				'name'        => $tax_rate['name'],
				'rate'        => $tax_rate['rate'],
				'type'        => $tax_rate['type'],
				'amount'      => $amount
			);
		}
	
		return $tax_rate_data;
	}	
            
	public function addProduct($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', date_release = '" . $data['date_release'] . "', release_status_id = '" . (int)$data['release_status_id'] . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");
        
		$product_id = $this->db->getLastId();

		$this->db->query("INSERT INTO " . DB_PREFIX . "product_cost SET product_id = '" . (int)$product_id . "', supplier_id = '" . (int)$data['supplier_id'] . "', cost = '" . (float)$data['cost'] . "', cost_amount = '" . (float)$data['cost_amount'] . "', cost_percentage = '" . (float)$data['cost_percentage'] . "', cost_additional = '" . (float)$data['cost_additional'] . "', costing_method = '" . (int)$data['costing_method'] . "'");
            

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
		if (isset($data['video'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET video = '" . $this->db->escape($data['video']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		

		$restock_cost = $data['costing_method'] == '1' ? (float)$data['restock_cost'] : (float)$data['cost'];
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_stock_history (product_id, restock_quantity, stock_quantity, supplier_id, costing_method, restock_cost, cost, price, comment, date_added) SELECT '" . (int)$product_id . "', '0', '" . (int)$data['quantity'] . "', '0', '" . (int)$data['costing_method'] . "', '" . $restock_cost . "', '" . (float)$data['cost'] . "', '" . (float)$data['price'] . "', 'Purchase from supplier', NOW() FROM DUAL WHERE NOT EXISTS (SELECT product_id FROM " . DB_PREFIX . "product_stock_history WHERE product_id = '" . (int)$product_id . "') OR EXISTS (SELECT p1.product_id FROM " . DB_PREFIX . "product_stock_history AS p1 LEFT JOIN " . DB_PREFIX . "product_stock_history AS p2 ON p1.product_id = p2.product_id AND p1.date_added < p2.date_added WHERE p2.product_id IS NULL AND p1.product_id = '" . (int)$product_id . "' AND (p1.cost <> '" . (float)$data['cost'] . "' OR p1.price <> '" . (float)$data['price'] . "' OR p1.stock_quantity <> '" . (int)$data['quantity'] . "'))");
            
		foreach ($data['product_description'] as $language_id => $value) {
$this->db->query("INSERT INTO " . DB_PREFIX . "product_description_seo SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', custom_imgtitle = '" . $this->db->escape($value['custom_imgtitle']) . "', custom_alt = '" . $this->db->escape($value['custom_alt']) . "',  custom_h1 = '" . $this->db->escape($value['custom_h1']) . "', custom_h2 = '" . $this->db->escape($value['custom_h2']) . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}


				if (isset($data['distributor_status_id'])) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product SET distributor_status_id = '" . (int)$data['distributor_status_id'] . "'");
				}
			
		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {

							if ($product_option_value['cost'] > 0 && $product_option_value['cost_amount'] == 0) {			
								$cost_amount = (float)$product_option_value['cost'];
							} else {
								$cost_amount = (float)$product_option_value['cost_amount'];
							}	
            
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");

							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_cost SET product_option_value_id = (SELECT MAX(product_option_value_id) FROM " . DB_PREFIX . "product_option_value), product_id = '" . (int)$product_id . "', cost = '" . (float)$product_option_value['cost'] . "', cost_amount = '" . $cost_amount . "', cost_prefix = '" . $this->db->escape($product_option_value['cost_prefix']) . "', costing_method = '" . (int)$product_option_value['costing_method'] . "', sku = '" . $this->db->escape($product_option_value['sku']) . "'");
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_stock_history (product_option_id, product_id, option_id, option_value_id, stock_quantity, supplier_id, costing_method, cost, price, comment, date_added) SELECT '" . (int)$product_option_id . "', '" . (int)$product_id . "', '" . (int)$product_option['option_id'] . "', '" . (int)$product_option_value['option_value_id'] . "', '" . (int)$product_option_value['quantity'] . "', '" . (int)$data['supplier_id'] . "', '" . (int)$product_option_value['costing_method'] . "', '" . (float)$product_option_value['cost'] . "', '" . (float)$product_option_value['price'] . "', 'Purchase from supplier', NOW() FROM DUAL WHERE NOT EXISTS (SELECT product_id, option_id, option_value_id FROM " . DB_PREFIX . "product_option_stock_history WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] . "' AND option_value_id = '" . (int)$product_option_value['option_value_id'] . "') OR EXISTS (SELECT p1.product_id, p1.option_id, p1.option_value_id FROM " . DB_PREFIX . "product_option_stock_history AS p1 LEFT JOIN " . DB_PREFIX . "product_option_stock_history AS p2 ON p1.product_id = p2.product_id AND p1.option_id = p2.option_id AND p1.option_value_id = p2.option_value_id AND p1.date_added < p2.date_added WHERE p2.product_id IS NULL AND p2.option_id IS NULL AND p2.option_value_id IS NULL AND p1.product_id = '" . (int)$product_id . "' AND p1.option_id = '" . (int)$product_option['option_id'] . "' AND p1.option_value_id = '" . (int)$product_option_value['option_value_id'] . "' AND (p1.cost <> '" . (float)$product_option_value['cost'] . "' OR p1.price <> '" . (float)$product_option_value['price'] . "' OR p1.stock_quantity <> '" . (int)$product_option_value['quantity'] . "'))");
            
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}

		if (isset($data['product_recurring'])) {
			foreach ($data['product_recurring'] as $recurring) {

				$query = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "product_recurring` WHERE `product_id` = '" . (int)$product_id . "' AND `customer_group_id = '" . (int)$recurring['customer_group_id'] . "' AND `recurring_id` = '" . (int)$recurring['recurring_id'] . "'");

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = '" . (int)$product_id . "', customer_group_id = '" . (int)$recurring['customer_group_id'] . "', `recurring_id` = '" . (int)$recurring['recurring_id'] . "'");
				}
			}
		}
		
		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				if ((int)$product_reward['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
				}
			}
		}
		
		// SEO URL
		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
        
        $video = $data['video'];
	$module_name = "Video BGNM" . $product_id;
	
	if (empty($video)) {
		// Check if the module with the same name already exists
		$query = $this->db->query("SELECT module_id FROM " . DB_PREFIX . "journal3_module WHERE module_name = '" . $this->db->escape($module_name) . "'");
	
		if ($query->num_rows > 0) {
			// Delete the existing record if it exists
			$delete_sql = "DELETE FROM " . DB_PREFIX . "journal3_module WHERE module_name = '" . $this->db->escape($module_name) . "'";
			$this->db->query($delete_sql);
		}
	} else {
		// Convert the YouTube URL to an embeddable format if it's not already
		if (strpos($video, 'youtube.com') !== false) {
			parse_str(parse_url($video, PHP_URL_QUERY), $query_params);
			$video_id = isset($query_params['v']) ? $query_params['v'] : '';
			$embed_url = "https://www.youtube.com/embed/" . $this->db->escape($video_id);
		} else {
			$embed_url = $this->db->escape($video);
		}
		
	$content = '<iframe width="560" height="315" src="' . $embed_url . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                    
    // Prepare the module_data with dynamic content, product ID, and other static data as in the example
	$module_data = '{
		"general": {
			"itemContentWidth": "",
			"expandHeight": "70",
			"display": "tabs",
			"itemBoxStylesShadowHover": {
				"offsetX": "",
				"offsetY": "",
				"blur": "",
				"spread": "",
				"color": "",
				"inner": "false",
				"none": "false"
			},
			"contentShadowHover": {
				"offsetX": "",
				"offsetY": "",
				"blur": "",
				"spread": "",
				"color": "",
				"inner": "false",
				"none": "false"
			},
			"blocksTitleVisibility": "true",
			"sortTablet": "",
			"expandIconHoverUp": "",
			"contentWidgetHeight": "",
			"positionPhone": "",
			"contentBorder": {
				"border-width": "",
				"border-top-width": "",
				"border-right-width": "",
				"border-bottom-width": "",
				"border-left-width": "",
				"border-style": "",
				"border-color": ""
			},
			"expandButtonStyle": "",
			"itemBoxStylesBorder": {
				"border-width": "",
				"border-top-width": "",
				"border-right-width": "",
				"border-bottom-width": "",
				"border-left-width": "",
				"border-style": "",
				"border-color": ""
			},
			"blocksColumnDividerColor": "",
			"itemBoxStylesBackground": {
				"backgroundPositionX": "",
				"backgroundPositionY": "",
				"background-attachment": "",
				"background-color": "",
				"background-origin": "",
				"background-position": "",
				"backgroundSizeW": "",
				"overlay": "",
				"gradient": "",
				"backgroundSizeUnit": "px",
				"none": "false",
				"backgroundSizeH": "",
				"background-repeat": "",
				"backgroundPositionUnit": "px",
				"background-image": "",
				"background-blend-mode": ""
			},
			"displayTablet": "",
			"contentTypography": "",
			"itemBoxStylesDisplay": "",
			"itemBoxStylesMargin": {
				"margin": "",
				"margin-top": "",
				"margin-right": "",
				"margin-bottom": "",
				"margin-left": ""
			},
			"itemBoxStylesFont": {
				"word-spacing": "",
				"line-height": "",
				"font-family": "",
				"color": "",
				"text-align": "",
				"text-transform": "",
				"text-decoration": "",
				"font-style": "",
				"textShadowBlur": "",
				"font-weight": "",
				"textShadowColor": "",
				"textShadowOffsetX": "",
				"textShadowOffsetY": "",
				"word-break": "",
				"letter-spacing": "",
				"font-size": ""
			},
			"blocksColumnDividerWidth": "1",
			"position": "bottom",
			"expandIconHover": "",
			"name": "' . $this->db->escape($module_name) . '",
			"displayPhone": "accordion",
			"contentAlign": "",
			"expandButton": "false",
			"expandOverlayColor": "",
			"itemBoxStylesOffset": {
				"first": "",
				"second": ""
			},
			"expandIconUp": {
				"size": "",
				"color": "",
				"offsetX": "",
				"offsetY": "",
				"flip": "",
				"margin": {
					"margin": "",
					"margin-top": "",
					"margin-right": "",
					"margin-bottom": "",
					"margin-left": ""
				},
				"none": "false",
				"icon": {
					"code": "",
					"name": ""
				},
				"type": "",
				"image": ""
			},
			"tabType": "content",
			"positionTablet": "",
			"sortPhone": "",
			"expandButtonTextLess": {
				"lang_1": ""
			},
			"status": {
				"customer_group_12": "true",
				"params": "",
				"admin": "false",
				"customer_group_1": "true",
				"customer_group_2": "true",
				"store_0": "true",
				"status": "true",
				"store_1": "true",
				"device": ["desktop", "phone", "tablet"],
				"customer": ""
			},
			"expandIcon": {
				"size": "",
				"color": "",
				"offsetX": "",
				"offsetY": "",
				"flip": "",
				"margin": {
					"margin": "",
					"margin-top": "",
					"margin-right": "",
					"margin-bottom": "",
					"margin-left": ""
				},
				"none": "false",
				"icon": {
					"code": "",
					"name": ""
				},
				"type": "",
				"image": ""
			},
			"itemBoxStylesFontHover": {
				"word-spacing": "",
				"line-height": "",
				"font-family": "",
				"color": "",
				"text-align": "",
				"text-transform": "",
				"text-decoration": "",
				"font-style": "",
				"textShadowBlur": "",
				"font-weight": "",
				"textShadowColor": "",
				"textShadowOffsetX": "",
				"textShadowOffsetY": "",
				"word-break": "",
				"letter-spacing": "",
				"font-size": ""
			},
			"contentType": "html",
			"itemBoxStylesBackgroundHover": {
				"backgroundPositionX": "",
				"backgroundPositionY": "",
				"background-attachment": "",
				"background-color": "",
				"background-origin": "",
				"background-position": "",
				"backgroundSizeW": "",
				"gradient": "",
				"backgroundSizeUnit": "px",
				"none": "false",
				"backgroundSizeH": "",
				"background-repeat": "",
				"backgroundPositionUnit": "px",
				"background-image": ""
			},
			"blocksColumnDividerStyle": "solid",
			"contentTable": "",
			"itemBoxStylesBorderHover": "",
			"itemBoxStylesPadding": {
				"padding": "",
				"padding-top": "",
				"padding-right": "",
				"padding-bottom": "",
				"padding-left": ""
			},
			"schedule": {
				"from": "",
				"to": "",
				"between": "true"
			},
			"contentBorderRadius": {
				"border-radius": "",
				"border-top-left-radius": "",
				"border-top-right-radius": "",
				"border-bottom-right-radius": "",
				"border-bottom-left-radius": "",
				"borderRadiusUnit": "px",
				"custom": ""
			},
			"blocksColumns": "initial",
			"contentPadding": {
				"padding": "",
				"padding-top": "",
				"padding-right": "",
				"padding-bottom": "",
				"padding-left": ""
			},
			"itemBoxStylesBorderRadius": {
				"border-radius": "",
				"border-top-left-radius": "",
				"border-top-right-radius": "",
				"border-bottom-right-radius": "",
				"border-bottom-left-radius": "",
				"borderRadiusUnit": "px",
				"custom": ""
			},
			"title": {
				"lang_1": "Video"
			},
			"filter": {
				"manufacturers": [],
				"products": ["' . $this->db->escape($product_id) . '"],
				"max_quantity": "",
				"max_price": "",
				"order": "ASC",
				"attributes": [],
				"min_quantity": "",
				"special": "false",
				"limit": "5",
				"filters": [],
				"min_price": "",
				"categories": [],
				"sort": "p.sort_order",
				"options": [],
				"preset": "custom"
			},
			"content": {
				"lang_1": "' . $this->db->escape($content) . '"
			},
			"expandButtonText": {
				"lang_1": ""
			},
			"contentShadow": {
				"offsetX": "",
				"offsetY": "",
				"blur": "",
				"spread": "",
				"color": "",
				"inner": "false",
				"none": "false"
			},
			"blocksTitleAlignment": "center",
			"blocksSpacing": "20"
		}
	}';
	
		// Check if the module already exists for updating
		$query = $this->db->query("SELECT module_id FROM " . DB_PREFIX . "journal3_module WHERE module_name = '" . $this->db->escape($module_name) . "'");
	
		if ($query->num_rows > 0) {
			// Update existing record
			$update_sql = "UPDATE " . DB_PREFIX . "journal3_module 
							SET module_data = '" . $this->db->escape($module_data) . "' 
							WHERE module_name = '" . $this->db->escape($module_name) . "'";
			$this->db->query($update_sql);
		} else {
			// Insert new record
			$insert_sql = "INSERT INTO " . DB_PREFIX . "journal3_module (`module_id`, `module_type`, `module_name`, `module_data`) 
							VALUES (NULL, 'product_tabs', '" . $this->db->escape($module_name) . "', '" . $this->db->escape($module_data) . "')";
			$this->db->query($insert_sql);
		}
	}


				
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` like 'seopack%'");
			
				foreach ($query->rows as $result) {
						if (!$result['serialized']) {
							$data[$result['key']] = $result['value'];
						} else {
							if ($result['value'][0] == '{') {$data[$result['key']] = json_decode($result['value'], true);} else {$data[$result['key']] = unserialize($result['value']);}
						}
					}
					
				if (isset($data)) {$seopack_parameters = $data['seopack_parameters'];}
					else {
						$seopack_parameters['keywords'] = '%c%p';
						$seopack_parameters['tags'] = '%c%p';
						$seopack_parameters['metas'] = '%p - %f';
						}
				
				
				if (isset($seopack_parameters['ext'])) { $ext = $seopack_parameters['ext'];}
					else {$ext = '';}
					
				if ((isset($seopack_parameters['autokeywords'])) && ($seopack_parameters['autokeywords']))
					{	
						$query = $this->db->query("select pd.name as pname, cd.name as cname, pd.language_id as language_id, pd.product_id as product_id, p.sku as sku, p.model as model, p.upc as upc, m.name as brand  from " . DB_PREFIX . "product_description pd
								left join " . DB_PREFIX . "product_to_category pc on pd.product_id = pc.product_id
								inner join " . DB_PREFIX . "product p on pd.product_id = p.product_id
								left join " . DB_PREFIX . "category_description cd on cd.category_id = pc.category_id and cd.language_id = pd.language_id
								left join " . DB_PREFIX . "manufacturer m on m.manufacturer_id = p.manufacturer_id
								where p.product_id = '" . (int)$product_id . "';");
					
								
								//die('z');
						foreach ($query->rows as $product) {
														
							$bef = array("%", "_","\"","'","\\");
							$aft = array("", " ", " ", " ", "");
							
							$included = explode('%', str_replace(array(' ',','), '', $seopack_parameters['keywords']));
							
							$tags = array();
							
							if (in_array("p", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['pname']))))));}
							if (in_array("c", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['cname']))))));}
							if (in_array("s", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['sku']))))));}
							if (in_array("m", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['model']))))));}
							if (in_array("u", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['upc']))))));}
							if (in_array("b", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['brand']))))));}
							
							$keywords = '';
							
							foreach ($tags as $tag)
								{
								if (strlen($tag) > 2) 
									{
									
									$keywords = $keywords.' '.strtolower($tag);
									
									}
								}
								
							
							$exists = $this->db->query("select count(*) as times from " . DB_PREFIX . "product_description where product_id = ".$product['product_id']." and language_id = ".$product['language_id']." and meta_keyword like '%".$keywords."%';");
							
									foreach ($exists->rows as $exist)
										{
										$count = $exist['times'];
										}
							$exists = $this->db->query("select length(meta_keyword) as leng from " . DB_PREFIX . "product_description where product_id = ".$product['product_id']." and language_id = ".$product['language_id'].";");
							
									foreach ($exists->rows as $exist)
										{
										$leng = $exist['leng'];
										}

							if (($count == 0) && ($leng < 255)) {$this->db->query("update " . DB_PREFIX . "product_description set meta_keyword = concat(meta_keyword, '". htmlspecialchars($keywords) ."') where product_id = ".$product['product_id']." and language_id = ".$product['language_id'].";");}	
								
														
							}
					}
				if ((isset($seopack_parameters['autometa'])) && ($seopack_parameters['autometa']))
					{
						$query = $this->db->query("select pd.name as pname, p.price as price, cd.name as cname, pd.description as pdescription, pd.language_id as language_id, pd.product_id as product_id, p.model as model, p.sku as sku, p.upc as upc, m.name as brand from " . DB_PREFIX . "product_description pd
								left join " . DB_PREFIX . "product_to_category pc on pd.product_id = pc.product_id
								inner join " . DB_PREFIX . "product p on pd.product_id = p.product_id
								left join " . DB_PREFIX . "category_description cd on cd.category_id = pc.category_id and cd.language_id = pd.language_id
								left join " . DB_PREFIX . "manufacturer m on m.manufacturer_id = p.manufacturer_id
								where p.product_id = '" . (int)$product_id . "';");

						foreach ($query->rows as $product) {
														
							$bef = array("%", "_","\"","'","\\", "\r", "\n");
							$aft = array("", " ", " ", " ", "", "", "");
							
							$ncategory = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['cname']))));
							$nproduct = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['pname']))));
							$model = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['model']))));
							$sku = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['sku']))));
							$upc = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['upc']))));
							$content = strip_tags(html_entity_decode($product['pdescription']));
							$pos = strpos($content, '.');							   
							if($pos === false) {}
								else { $content =  substr($content, 0, $pos+1);	}
							$sentence = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft, $content))));
							$brand = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['brand']))));
							$price = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft, number_format($product['price'], 2)))));
							
							$bef = array("%c", "%p", "%m", "%s", "%u", "%f", "%b", "%$");
							$aft = array($ncategory, $nproduct, $model, $sku, $upc, $sentence, $brand, $price);
							
							$meta_description = str_replace($bef, $aft,  $seopack_parameters['metas']);
							
							$exists = $this->db->query("select count(*) as times from " . DB_PREFIX . "product_description where product_id = ".$product['product_id']." and language_id = ".$product['language_id']." and meta_description not like '%".htmlspecialchars($meta_description)."%';");
							
									foreach ($exists->rows as $exist)
										{
										$count = $exist['times'];
										}
							
							if ($count) {$this->db->query("update " . DB_PREFIX . "product_description set meta_description = concat(meta_description, '". htmlspecialchars($meta_description) ."') where product_id = ".$product['product_id']." and language_id = ".$product['language_id'].";");}			
														
							}
					}
				if ((isset($seopack_parameters['autotags'])) && ($seopack_parameters['autotags']))
					{
					$query = $this->db->query("select pd.name as pname, pd.tag, cd.name as cname, pd.language_id as language_id, pd.product_id as product_id, p.sku as sku, p.model as model, p.upc as upc, m.name as brand from " . DB_PREFIX . "product_description pd
							inner join " . DB_PREFIX . "product_to_category pc on pd.product_id = pc.product_id
							inner join " . DB_PREFIX . "product p on pd.product_id = p.product_id
							inner join " . DB_PREFIX . "category_description cd on cd.category_id = pc.category_id and cd.language_id = pd.language_id
							left join " . DB_PREFIX . "manufacturer m on m.manufacturer_id = p.manufacturer_id
							where p.product_id = '" . (int)$product_id . "';");
					
					foreach ($query->rows as $product) {
						
						$newtags ='';
						
						$included = explode('%', str_replace(array(' ',','), '', $seopack_parameters['tags']));
						
						$tags = array();
						
						
						$bef = array("%", "_","\"","'","\\");
						$aft = array("", " ", " ", " ", "");
						
							if (in_array("p", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['pname']))))));}
							if (in_array("c", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['cname']))))));}
							if (in_array("s", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['sku']))))));}
							if (in_array("m", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['model']))))));}
							if (in_array("u", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['upc']))))));}
							if (in_array("b", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['brand']))))));}
							
							foreach ($tags as $tag)
								{
								if (strlen($tag) > 2) 
									{
									if ((strpos($product['tag'], strtolower($tag)) === false) && (strpos($newtags, strtolower($tag)) === false))
										{
											$newtags .= ' '.strtolower($tag).',';											
										}			
									}
								}
							
														
							if ($product['tag']) {
								$newtags = trim($this->db->escape($product['tag']) . $newtags,' ,');
								$this->db->query("update " . DB_PREFIX . "product_description set tag = '$newtags' where product_id = '". $product['product_id'] ."' and language_id = '". $product['language_id'] ."';");
								}
								else {
								$newtags = trim($newtags,' ,');
								$this->db->query("update " . DB_PREFIX . "product_description set tag = '$newtags' where product_id = '". $product['product_id'] ."' and language_id = '". $product['language_id'] ."';");
								}
																				
						}
						
					}
				if ((isset($seopack_parameters['autourls'])) && ($seopack_parameters['autourls']))
					{
						require_once(DIR_APPLICATION . 'controller/extension/extension/seopack.php');
						$seo = new ControllerExtensionExtensionSeoPack('');
						
						$query = $this->db->query("SELECT pd.product_id, pd.name, pd.language_id ,l.code FROM ".DB_PREFIX."product p 
								inner join ".DB_PREFIX."product_description pd ON p.product_id = pd.product_id 
								inner join ".DB_PREFIX."language l on l.language_id = pd.language_id 
								where p.product_id = '" . (int)$product_id . "';");

						
						foreach ($query->rows as $product_row ){	

							
							if( strlen($product_row['name']) > 1 ){
							
								$slug = $seo->generateSlug($product_row['name']).$ext;
								$exist_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.query = 'product_id=" . $product_row['product_id'] . "' and language_id=".$product_row['language_id']);
								
								if(!$exist_query->num_rows){
									
									$exist_keyword = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "'");
									if($exist_keyword->num_rows){ 
										$exist_keyword_lang = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "' AND " . DB_PREFIX . "seo_url.query <> 'product_id=" . $product_row['product_id'] . "'");
										if($exist_keyword_lang->num_rows){
												$slug = $seo->generateSlug($product_row['name']).'-'.rand().$ext;
											}
											else
											{
												$slug = $seo->generateSlug($product_row['name']).'-'.$product_row['code'].$ext;
											}
										}
										
									
									$add_query = "INSERT INTO " . DB_PREFIX . "seo_url (query, keyword, language_id) VALUES ('product_id=" . $product_row['product_id'] . "', '" . $slug . "', " . $product_row['language_id'] . ")";
									$this->db->query($add_query);
									
								}
							}
						}
					}
				
			

                // Store Pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status')) {

                   $this->db->query("DELETE FROM `" . DB_PREFIX . "pickup_product` WHERE product_id = '" . (int)$product_id . "'");
                   $this->db->query("DELETE FROM `" . DB_PREFIX . "pickup_product_option` WHERE product_id = '" . (int)$product_id . "'");
                }
                // Store Pickup module code ends here
                    
		$this->cache->delete('product');

		return $product_id;
	}

	public function editProduct($product_id, $data) {

        /**
         * Marketplace
         */
        if ($this->config->get('module_marketplace_status')) {
            $checkSellerProduct = $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id = '" . (int)$product_id."' ORDER BY id ASC")->row;
            if ($checkSellerProduct) {
                $updatePrice = $this->currency->convert($data['price'],$this->config->get('config_currency'),$checkSellerProduct['currency_code']);

                $this->db->query("UPDATE " . DB_PREFIX . "customerpartner_to_product SET price = '".(float)$data['price']."',seller_price = '".(float)$updatePrice."'  WHERE product_id = '".$product_id."' ORDER BY id ASC");
            }
        }
        /**
         * Marketplace
         */
        

		$this->db->query("UPDATE " . DB_PREFIX . "product_cost SET supplier_id = '" . (int)$data['supplier_id'] . "', cost = '" . (float)$data['cost'] . "', cost_amount = '" . (float)$data['cost_amount'] . "', cost_percentage = '" . (float)$data['cost_percentage'] . "', cost_additional = '" . (float)$data['cost_additional'] . "', costing_method = '" . (int)$data['costing_method'] . "' WHERE product_id = '" . (int)$product_id . "'");
            
	    
	   //$this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', date_release = '" . $data['date_release'] . "', release_status_id = '" . (int)$data['release_status_id'] . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");
	    
	    $this->db->query("UPDATE " . DB_PREFIX . "product SET model = '" . $this->db->escape($data['model']) . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', date_release = '" . $data['date_release'] . "', release_status_id = '" . (int)$data['release_status_id'] . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");
	   

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
	     if (isset($data['video'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET video = '" . $this->db->escape($data['video']) . "' WHERE product_id = '" . (int)$product_id . "'");
	    }

$this->db->query("DELETE FROM " . DB_PREFIX . "product_description_seo WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");


		$restock_cost = $data['costing_method'] == '1' ? (float)$data['restock_cost'] : (float)$data['cost'];
		$this->db->query("INSERT INTO " . DB_PREFIX . "product_stock_history (product_id, restock_quantity, stock_quantity, supplier_id, costing_method, restock_cost, cost, price, comment, date_added) SELECT '" . (int)$product_id . "', '" . (int)$data['restock_quantity'] . "', '" . (int)$data['quantity'] . "', '" . (int)$data['supplier_id'] . "', '" . (int)$data['costing_method'] . "', '" . $restock_cost . "', '" . (float)$data['cost'] . "', '" . (float)$data['price'] . "', 'Purchase from supplier', NOW() FROM DUAL WHERE NOT EXISTS (SELECT product_id FROM " . DB_PREFIX . "product_stock_history WHERE product_id = '" . (int)$product_id . "') OR EXISTS (SELECT p1.product_id FROM " . DB_PREFIX . "product_stock_history AS p1 LEFT JOIN " . DB_PREFIX . "product_stock_history AS p2 ON p1.product_id = p2.product_id AND p1.date_added < p2.date_added WHERE p2.product_id IS NULL AND p1.product_id = '" . (int)$product_id . "' AND (p1.cost <> '" . (float)$data['cost'] . "' OR p1.price <> '" . (float)$data['price'] . "' OR p1.stock_quantity <> '" . (int)$data['quantity'] . "'))");
            
		foreach ($data['product_description'] as $language_id => $value) {
$this->db->query("INSERT INTO " . DB_PREFIX . "product_description_seo SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', custom_imgtitle = '" . $this->db->escape($value['custom_imgtitle']) . "', custom_alt = '" . $this->db->escape($value['custom_alt']) . "',  custom_h1 = '" . $this->db->escape($value['custom_h1']) . "', custom_h2 = '" . $this->db->escape($value['custom_h2']) . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");


				if (isset($data['distributor_status_id'])) {
					$this->db->query("UPDATE " . DB_PREFIX . "product SET distributor_status_id = '" . (int)$data['distributor_status_id'] . "' WHERE product_id = '" . (int)$product_id . "'");
				}
			
		if (!empty($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					// Removes duplicates
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_cost WHERE product_id = '" . (int)$product_id . "'");		
            

		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					if (isset($product_option['product_option_value'])) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

						$product_option_id = $this->db->getLastId();

						foreach ($product_option['product_option_value'] as $product_option_value) {

							if ($product_option_value['cost'] > 0 && $product_option_value['cost_amount'] == 0) {			
								$cost_amount = (float)$product_option_value['cost'];
							} else {
								$cost_amount = (float)$product_option_value['cost_amount'];
							}	
            
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");

							if ($product_option_value['product_option_value_id'] > 0) {			
								$pov_id = (int)$product_option_value['product_option_value_id'];
							} else {
								$pov_id = "(SELECT MAX(product_option_value_id) FROM " . DB_PREFIX . "product_option_value)";
							}
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_cost SET product_option_value_id = " . $pov_id . ", product_id = '" . (int)$product_id . "', cost = '" . (float)$product_option_value['cost'] . "', cost_amount = '" . $cost_amount . "', cost_prefix = '" . $this->db->escape($product_option_value['cost_prefix']) . "', costing_method = '" . (int)$product_option_value['costing_method'] . "', sku = '" . $this->db->escape($product_option_value['sku']) . "'");
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_stock_history (product_option_id, product_id, option_id, option_value_id, restock_quantity, stock_quantity, supplier_id, costing_method, restock_cost, cost, price, comment, date_added) SELECT '" . (int)$product_option_id . "', '" . (int)$product_id . "', '" . (int)$product_option['option_id'] . "', '" . (int)$product_option_value['option_value_id'] . "', '" . (int)$product_option_value['option_restock_quantity'] . "', '" . (int)$product_option_value['quantity'] . "', '" . (int)$data['supplier_id'] . "', '" . (int)$product_option_value['costing_method'] . "', '" . (float)$product_option_value['cost_amount'] . "', '" . (float)$product_option_value['cost'] . "', '" . (float)$product_option_value['price'] . "', 'Purchase from supplier', NOW() FROM DUAL WHERE NOT EXISTS (SELECT product_id, option_id, option_value_id FROM " . DB_PREFIX . "product_option_stock_history WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$product_option['option_id'] . "' AND option_value_id = '" . (int)$product_option_value['option_value_id'] . "') OR EXISTS (SELECT p1.product_id, p1.option_id, p1.option_value_id FROM " . DB_PREFIX . "product_option_stock_history AS p1 LEFT JOIN " . DB_PREFIX . "product_option_stock_history AS p2 ON p1.product_id = p2.product_id AND p1.option_id = p2.option_id AND p1.option_value_id = p2.option_value_id AND p1.date_added < p2.date_added WHERE p2.product_id IS NULL AND p2.option_id IS NULL AND p2.option_value_id IS NULL AND p1.product_id = '" . (int)$product_id . "' AND p1.option_id = '" . (int)$product_option['option_id'] . "' AND p1.option_value_id = '" . (int)$product_option_value['option_value_id'] . "' AND (p1.cost <> '" . (float)$product_option_value['cost'] . "' OR p1.price <> '" . (float)$product_option_value['price'] . "' OR p1.stock_quantity <> '" . (int)$product_option_value['quantity'] . "'))");
            
						}
					}
				} else {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = " . (int)$product_id);

		if (isset($data['product_recurring'])) {
			foreach ($data['product_recurring'] as $product_recurring) {
				$query = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "product_recurring` WHERE `product_id` = '" . (int)$product_id . "' AND `customer_group_id` = '" . (int)$product_recurring['customer_group_id'] . "' AND `recurring_id` = '" . (int)$product_recurring['recurring_id'] . "'");

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$product_recurring['customer_group_id'] . "', `recurring_id` = '" . (int)$product_recurring['recurring_id'] . "'");
				}				
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_filter'])) {
			foreach ($data['product_filter'] as $filter_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $value) {
				if ((int)$value['points'] > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
				}
			}
		}
		
		// SEO URL
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
		
		if (isset($data['product_seo_url'])) {
			foreach ($data['product_seo_url']as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
			}
		}
		
			/** Start of 26-02-2022 **/
		
		if (isset($data['stock_status_id']) && isset($data['subtract'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET stock_status_id = '" . (int)$data['stock_status_id'] . "', subtract = '" . (int)$data['subtract'] . "' WHERE product_id = '" . (int)$product_id . "'");
			
			$ss_id = $data['stock_status_id'];
			
			if ($ss_id != ''){
			    if (($ss_id == 8)  OR ($ss_id == 9) OR ($ss_id == 10) OR ($ss_id == 11)){
    			    
    				if($ss_id == 8) {
    				    $c_id = 53;
    				}elseif($ss_id == 9) {
    				    $c_id = 52;
    				}elseif($ss_id == 10) {
    				    $c_id = 54;
    				}elseif($ss_id == 11) {
    				    $c_id = 51;
    				}
    				
    				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id IN (51, 52, 53, 54)");
    			    
    			    if ($query->num_rows > 0) {
    				    $this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET category_id = '".$c_id."' WHERE product_id = '" . (int)$product_id . "' AND category_id IN (51, 52, 53, 54)");
    				}else{
    					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$c_id . "'");
    				}
    			}else{
    			    $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id IN (51, 52, 53, 54)");
    			}
    			
				
				if ($ss_id == 8) {
				    $this->db->query("UPDATE " . DB_PREFIX . "product SET subtract = 0 WHERE product_id = '" . (int)$product_id . "'");
				}else{
					$this->db->query("UPDATE " . DB_PREFIX . "product SET subtract = 1 WHERE product_id = '" . (int)$product_id . "'");
				}
				
				if (($ss_id == 8) && ($data['quantity'] <= 0)) {
				    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = 1 WHERE product_id = '" . (int)$product_id . "'");
				}else{
				    $this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . (int)$data['quantity'] . "' WHERE product_id = '" . (int)$product_id . "'");
				}
			}
		}
		
		/** End of 26-02-2022 **/
		
		$video = $data['video'];
	$module_name = "Video BGNM" . $product_id;
	
	if (empty($video)) {
		// Check if the module with the same name already exists
		$query = $this->db->query("SELECT module_id FROM " . DB_PREFIX . "journal3_module WHERE module_name = '" . $this->db->escape($module_name) . "'");
	
		if ($query->num_rows > 0) {
			// Delete the existing record if it exists
			$delete_sql = "DELETE FROM " . DB_PREFIX . "journal3_module WHERE module_name = '" . $this->db->escape($module_name) . "'";
			$this->db->query($delete_sql);
		}
	} else {
		// Convert the YouTube URL to an embeddable format if it's not already
		if (strpos($video, 'youtube.com') !== false) {
			parse_str(parse_url($video, PHP_URL_QUERY), $query_params);
			$video_id = isset($query_params['v']) ? $query_params['v'] : '';
			$embed_url = "https://www.youtube.com/embed/" . $this->db->escape($video_id);
		} else {
			$embed_url = $this->db->escape($video);
		}
		
	$content = '<iframe width="560" height="315" src="' . $embed_url . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                    
    // Prepare the module_data with dynamic content, product ID, and other static data as in the example
	$module_data = '{
		"general": {
			"itemContentWidth": "",
			"expandHeight": "70",
			"display": "tabs",
			"itemBoxStylesShadowHover": {
				"offsetX": "",
				"offsetY": "",
				"blur": "",
				"spread": "",
				"color": "",
				"inner": "false",
				"none": "false"
			},
			"contentShadowHover": {
				"offsetX": "",
				"offsetY": "",
				"blur": "",
				"spread": "",
				"color": "",
				"inner": "false",
				"none": "false"
			},
			"blocksTitleVisibility": "true",
			"sortTablet": "",
			"expandIconHoverUp": "",
			"contentWidgetHeight": "",
			"positionPhone": "",
			"contentBorder": {
				"border-width": "",
				"border-top-width": "",
				"border-right-width": "",
				"border-bottom-width": "",
				"border-left-width": "",
				"border-style": "",
				"border-color": ""
			},
			"expandButtonStyle": "",
			"itemBoxStylesBorder": {
				"border-width": "",
				"border-top-width": "",
				"border-right-width": "",
				"border-bottom-width": "",
				"border-left-width": "",
				"border-style": "",
				"border-color": ""
			},
			"blocksColumnDividerColor": "",
			"itemBoxStylesBackground": {
				"backgroundPositionX": "",
				"backgroundPositionY": "",
				"background-attachment": "",
				"background-color": "",
				"background-origin": "",
				"background-position": "",
				"backgroundSizeW": "",
				"overlay": "",
				"gradient": "",
				"backgroundSizeUnit": "px",
				"none": "false",
				"backgroundSizeH": "",
				"background-repeat": "",
				"backgroundPositionUnit": "px",
				"background-image": "",
				"background-blend-mode": ""
			},
			"displayTablet": "",
			"contentTypography": "",
			"itemBoxStylesDisplay": "",
			"itemBoxStylesMargin": {
				"margin": "",
				"margin-top": "",
				"margin-right": "",
				"margin-bottom": "",
				"margin-left": ""
			},
			"itemBoxStylesFont": {
				"word-spacing": "",
				"line-height": "",
				"font-family": "",
				"color": "",
				"text-align": "",
				"text-transform": "",
				"text-decoration": "",
				"font-style": "",
				"textShadowBlur": "",
				"font-weight": "",
				"textShadowColor": "",
				"textShadowOffsetX": "",
				"textShadowOffsetY": "",
				"word-break": "",
				"letter-spacing": "",
				"font-size": ""
			},
			"blocksColumnDividerWidth": "1",
			"position": "bottom",
			"expandIconHover": "",
			"name": "' . $this->db->escape($module_name) . '",
			"displayPhone": "accordion",
			"contentAlign": "",
			"expandButton": "false",
			"expandOverlayColor": "",
			"itemBoxStylesOffset": {
				"first": "",
				"second": ""
			},
			"expandIconUp": {
				"size": "",
				"color": "",
				"offsetX": "",
				"offsetY": "",
				"flip": "",
				"margin": {
					"margin": "",
					"margin-top": "",
					"margin-right": "",
					"margin-bottom": "",
					"margin-left": ""
				},
				"none": "false",
				"icon": {
					"code": "",
					"name": ""
				},
				"type": "",
				"image": ""
			},
			"tabType": "content",
			"positionTablet": "",
			"sortPhone": "",
			"expandButtonTextLess": {
				"lang_1": ""
			},
			"status": {
				"customer_group_12": "true",
				"params": "",
				"admin": "false",
				"customer_group_1": "true",
				"customer_group_2": "true",
				"store_0": "true",
				"status": "true",
				"store_1": "true",
				"device": ["desktop", "phone", "tablet"],
				"customer": ""
			},
			"expandIcon": {
				"size": "",
				"color": "",
				"offsetX": "",
				"offsetY": "",
				"flip": "",
				"margin": {
					"margin": "",
					"margin-top": "",
					"margin-right": "",
					"margin-bottom": "",
					"margin-left": ""
				},
				"none": "false",
				"icon": {
					"code": "",
					"name": ""
				},
				"type": "",
				"image": ""
			},
			"itemBoxStylesFontHover": {
				"word-spacing": "",
				"line-height": "",
				"font-family": "",
				"color": "",
				"text-align": "",
				"text-transform": "",
				"text-decoration": "",
				"font-style": "",
				"textShadowBlur": "",
				"font-weight": "",
				"textShadowColor": "",
				"textShadowOffsetX": "",
				"textShadowOffsetY": "",
				"word-break": "",
				"letter-spacing": "",
				"font-size": ""
			},
			"contentType": "html",
			"itemBoxStylesBackgroundHover": {
				"backgroundPositionX": "",
				"backgroundPositionY": "",
				"background-attachment": "",
				"background-color": "",
				"background-origin": "",
				"background-position": "",
				"backgroundSizeW": "",
				"gradient": "",
				"backgroundSizeUnit": "px",
				"none": "false",
				"backgroundSizeH": "",
				"background-repeat": "",
				"backgroundPositionUnit": "px",
				"background-image": ""
			},
			"blocksColumnDividerStyle": "solid",
			"contentTable": "",
			"itemBoxStylesBorderHover": "",
			"itemBoxStylesPadding": {
				"padding": "",
				"padding-top": "",
				"padding-right": "",
				"padding-bottom": "",
				"padding-left": ""
			},
			"schedule": {
				"from": "",
				"to": "",
				"between": "true"
			},
			"contentBorderRadius": {
				"border-radius": "",
				"border-top-left-radius": "",
				"border-top-right-radius": "",
				"border-bottom-right-radius": "",
				"border-bottom-left-radius": "",
				"borderRadiusUnit": "px",
				"custom": ""
			},
			"blocksColumns": "initial",
			"contentPadding": {
				"padding": "",
				"padding-top": "",
				"padding-right": "",
				"padding-bottom": "",
				"padding-left": ""
			},
			"itemBoxStylesBorderRadius": {
				"border-radius": "",
				"border-top-left-radius": "",
				"border-top-right-radius": "",
				"border-bottom-right-radius": "",
				"border-bottom-left-radius": "",
				"borderRadiusUnit": "px",
				"custom": ""
			},
			"title": {
				"lang_1": "Video"
			},
			"filter": {
				"manufacturers": [],
				"products": ["' . $this->db->escape($product_id) . '"],
				"max_quantity": "",
				"max_price": "",
				"order": "ASC",
				"attributes": [],
				"min_quantity": "",
				"special": "false",
				"limit": "5",
				"filters": [],
				"min_price": "",
				"categories": [],
				"sort": "p.sort_order",
				"options": [],
				"preset": "custom"
			},
			"content": {
				"lang_1": "' . $this->db->escape($content) . '"
			},
			"expandButtonText": {
				"lang_1": ""
			},
			"contentShadow": {
				"offsetX": "",
				"offsetY": "",
				"blur": "",
				"spread": "",
				"color": "",
				"inner": "false",
				"none": "false"
			},
			"blocksTitleAlignment": "center",
			"blocksSpacing": "20"
		}
	}';
	
		// Check if the module already exists for updating
		$query = $this->db->query("SELECT module_id FROM " . DB_PREFIX . "journal3_module WHERE module_name = '" . $this->db->escape($module_name) . "'");
	
		if ($query->num_rows > 0) {
			// Update existing record
			$update_sql = "UPDATE " . DB_PREFIX . "journal3_module 
							SET module_data = '" . $this->db->escape($module_data) . "' 
							WHERE module_name = '" . $this->db->escape($module_name) . "'";
			$this->db->query($update_sql);
		} else {
			// Insert new record
			$insert_sql = "INSERT INTO " . DB_PREFIX . "journal3_module (`module_id`, `module_type`, `module_name`, `module_data`) 
							VALUES (NULL, 'product_tabs', '" . $this->db->escape($module_name) . "', '" . $this->db->escape($module_data) . "')";
			$this->db->query($insert_sql);
		}
	}
		

				
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` like 'seopack%'");
			
				foreach ($query->rows as $result) {
						if (!$result['serialized']) {
							$data[$result['key']] = $result['value'];
						} else {
							if ($result['value'][0] == '{') {$data[$result['key']] = json_decode($result['value'], true);} else {$data[$result['key']] = unserialize($result['value']);}
						}
					}
					
				if (isset($data)) {$seopack_parameters = $data['seopack_parameters'];}
					else {
						$seopack_parameters['keywords'] = '%c%p';
						$seopack_parameters['tags'] = '%c%p';
						$seopack_parameters['metas'] = '%p - %f';
						}
				
				
				if (isset($seopack_parameters['ext'])) { $ext = $seopack_parameters['ext'];}
					else {$ext = '';}
					
				if ((isset($seopack_parameters['autokeywords'])) && ($seopack_parameters['autokeywords']))
					{	
						$query = $this->db->query("select pd.name as pname, cd.name as cname, pd.language_id as language_id, pd.product_id as product_id, p.sku as sku, p.model as model, p.upc as upc, m.name as brand  from " . DB_PREFIX . "product_description pd
								left join " . DB_PREFIX . "product_to_category pc on pd.product_id = pc.product_id
								inner join " . DB_PREFIX . "product p on pd.product_id = p.product_id
								left join " . DB_PREFIX . "category_description cd on cd.category_id = pc.category_id and cd.language_id = pd.language_id
								left join " . DB_PREFIX . "manufacturer m on m.manufacturer_id = p.manufacturer_id
								where p.product_id = '" . (int)$product_id . "';");
					
								
								//die('z');
						foreach ($query->rows as $product) {
														
							$bef = array("%", "_","\"","'","\\");
							$aft = array("", " ", " ", " ", "");
							
							$included = explode('%', str_replace(array(' ',','), '', $seopack_parameters['keywords']));
							
							$tags = array();
							
							if (in_array("p", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['pname']))))));}
							if (in_array("c", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['cname']))))));}
							if (in_array("s", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['sku']))))));}
							if (in_array("m", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['model']))))));}
							if (in_array("u", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['upc']))))));}
							if (in_array("b", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['brand']))))));}
							
							$keywords = '';
							
							foreach ($tags as $tag)
								{
								if (strlen($tag) > 2) 
									{
									
									$keywords = $keywords.' '.strtolower($tag);
									
									}
								}
								
							
							$exists = $this->db->query("select count(*) as times from " . DB_PREFIX . "product_description where product_id = ".$product['product_id']." and language_id = ".$product['language_id']." and meta_keyword like '%".$keywords."%';");
							
									foreach ($exists->rows as $exist)
										{
										$count = $exist['times'];
										}
							$exists = $this->db->query("select length(meta_keyword) as leng from " . DB_PREFIX . "product_description where product_id = ".$product['product_id']." and language_id = ".$product['language_id'].";");
							
									foreach ($exists->rows as $exist)
										{
										$leng = $exist['leng'];
										}

							if (($count == 0) && ($leng < 255)) {$this->db->query("update " . DB_PREFIX . "product_description set meta_keyword = concat(meta_keyword, '". htmlspecialchars($keywords) ."') where product_id = ".$product['product_id']." and language_id = ".$product['language_id'].";");}	
								
														
							}
					}
				if ((isset($seopack_parameters['autometa'])) && ($seopack_parameters['autometa']))
					{
						$query = $this->db->query("select pd.name as pname, p.price as price, cd.name as cname, pd.description as pdescription, pd.language_id as language_id, pd.product_id as product_id, p.model as model, p.sku as sku, p.upc as upc, m.name as brand from " . DB_PREFIX . "product_description pd
								left join " . DB_PREFIX . "product_to_category pc on pd.product_id = pc.product_id
								inner join " . DB_PREFIX . "product p on pd.product_id = p.product_id
								left join " . DB_PREFIX . "category_description cd on cd.category_id = pc.category_id and cd.language_id = pd.language_id
								left join " . DB_PREFIX . "manufacturer m on m.manufacturer_id = p.manufacturer_id
								where p.product_id = '" . (int)$product_id . "';");

						foreach ($query->rows as $product) {
														
							$bef = array("%", "_","\"","'","\\", "\r", "\n");
							$aft = array("", " ", " ", " ", "", "", "");
							
							$ncategory = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['cname']))));
							$nproduct = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['pname']))));
							$model = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['model']))));
							$sku = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['sku']))));
							$upc = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['upc']))));
							$content = strip_tags(html_entity_decode($product['pdescription']));
							$pos = strpos($content, '.');							   
							if($pos === false) {}
								else { $content =  substr($content, 0, $pos+1);	}
							$sentence = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft, $content))));
							$brand = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['brand']))));
							$price = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft, number_format($product['price'], 2)))));
							
							$bef = array("%c", "%p", "%m", "%s", "%u", "%f", "%b", "%$");
							$aft = array($ncategory, $nproduct, $model, $sku, $upc, $sentence, $brand, $price);
							
							$meta_description = str_replace($bef, $aft,  $seopack_parameters['metas']);
							
							$exists = $this->db->query("select count(*) as times from " . DB_PREFIX . "product_description where product_id = ".$product['product_id']." and language_id = ".$product['language_id']." and meta_description not like '%".htmlspecialchars($meta_description)."%';");
							
									foreach ($exists->rows as $exist)
										{
										$count = $exist['times'];
										}
							
							if ($count) {$this->db->query("update " . DB_PREFIX . "product_description set meta_description = concat(meta_description, '". htmlspecialchars($meta_description) ."') where product_id = ".$product['product_id']." and language_id = ".$product['language_id'].";");}			
														
							}
					}
				if ((isset($seopack_parameters['autotags'])) && ($seopack_parameters['autotags']))
					{
					$query = $this->db->query("select pd.name as pname, pd.tag, cd.name as cname, pd.language_id as language_id, pd.product_id as product_id, p.sku as sku, p.model as model, p.upc as upc, m.name as brand from " . DB_PREFIX . "product_description pd
							inner join " . DB_PREFIX . "product_to_category pc on pd.product_id = pc.product_id
							inner join " . DB_PREFIX . "product p on pd.product_id = p.product_id
							inner join " . DB_PREFIX . "category_description cd on cd.category_id = pc.category_id and cd.language_id = pd.language_id
							left join " . DB_PREFIX . "manufacturer m on m.manufacturer_id = p.manufacturer_id
							where p.product_id = '" . (int)$product_id . "';");
					
					foreach ($query->rows as $product) {
						
						$newtags ='';
						
						$included = explode('%', str_replace(array(' ',','), '', $seopack_parameters['tags']));
						
						$tags = array();
						
						
						$bef = array("%", "_","\"","'","\\");
						$aft = array("", " ", " ", " ", "");
						
							if (in_array("p", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['pname']))))));}
							if (in_array("c", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['cname']))))));}
							if (in_array("s", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['sku']))))));}
							if (in_array("m", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['model']))))));}
							if (in_array("u", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['upc']))))));}
							if (in_array("b", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['brand']))))));}
							
							foreach ($tags as $tag)
								{
								if (strlen($tag) > 2) 
									{
									if ((strpos($product['tag'], strtolower($tag)) === false) && (strpos($newtags, strtolower($tag)) === false))
										{
											$newtags .= ' '.strtolower($tag).',';											
										}			
									}
								}
							
														
							if ($product['tag']) {
								$newtags = trim($this->db->escape($product['tag']) . $newtags,' ,');
								$this->db->query("update " . DB_PREFIX . "product_description set tag = '$newtags' where product_id = '". $product['product_id'] ."' and language_id = '". $product['language_id'] ."';");
								}
								else {
								$newtags = trim($newtags,' ,');
								$this->db->query("update " . DB_PREFIX . "product_description set tag = '$newtags' where product_id = '". $product['product_id'] ."' and language_id = '". $product['language_id'] ."';");
								}
																				
						}
						
					}
				if ((isset($seopack_parameters['autourls'])) && ($seopack_parameters['autourls']))
					{
						require_once(DIR_APPLICATION . 'controller/extension/extension/seopack.php');
						$seo = new ControllerExtensionExtensionSeoPack('');
						
						$query = $this->db->query("SELECT pd.product_id, pd.name, pd.language_id ,l.code FROM ".DB_PREFIX."product p 
								inner join ".DB_PREFIX."product_description pd ON p.product_id = pd.product_id 
								inner join ".DB_PREFIX."language l on l.language_id = pd.language_id 
								where p.product_id = '" . (int)$product_id . "';");

						
						foreach ($query->rows as $product_row ){	

							
							if( strlen($product_row['name']) > 1 ){
							
								$slug = $seo->generateSlug($product_row['name']).$ext;
								$exist_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.query = 'product_id=" . $product_row['product_id'] . "' and language_id=".$product_row['language_id']);
								
								if(!$exist_query->num_rows){
									
									$exist_keyword = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "'");
									if($exist_keyword->num_rows){ 
										$exist_keyword_lang = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "' AND " . DB_PREFIX . "seo_url.query <> 'product_id=" . $product_row['product_id'] . "'");
										if($exist_keyword_lang->num_rows){
												$slug = $seo->generateSlug($product_row['name']).'-'.rand().$ext;
											}
											else
											{
												$slug = $seo->generateSlug($product_row['name']).'-'.$product_row['code'].$ext;
											}
										}
										
									
									$add_query = "INSERT INTO " . DB_PREFIX . "seo_url (query, keyword, language_id) VALUES ('product_id=" . $product_row['product_id'] . "', '" . $slug . "', " . $product_row['language_id'] . ")";
									$this->db->query($add_query);
									
								}
							}
						}
					}
				
			

                // Store Pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status')) {

                   $this->db->query("DELETE FROM `" . DB_PREFIX . "pickup_product` WHERE product_id = '" . (int)$product_id . "'");
                   $this->db->query("DELETE FROM `" . DB_PREFIX . "pickup_product_option` WHERE product_id = '" . (int)$product_id . "'");
                }
                // Store Pickup module code ends here
                    
		$this->cache->delete('product');
	}

	public function copyProduct($product_id) {
		
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_cost pc ON (p.product_id = pc.product_id) WHERE p.product_id = '" . (int)$product_id . "'");
            

		if ($query->num_rows) {
			$data = $query->row;

			$data['sku'] = '';
			$data['upc'] = '';
			$data['viewed'] = '0';
			$data['keyword'] = '';
			$data['status'] = '0';

			$data['restock_quantity'] = '0';
			$data['costing_method'] = '0';
			$data['restock_quantity_temp'] = '0';
			$data['quantity_temp'] = $data['quantity'];
			$data['remove_temp'] = '0';
            

			$data['product_attribute'] = $this->getProductAttributes($product_id);
			$data['product_description'] = $this->getProductDescriptions($product_id);
			$data['product_discount'] = $this->getProductDiscounts($product_id);
			$data['product_filter'] = $this->getProductFilters($product_id);
			$data['product_image'] = $this->getProductImages($product_id);
			$data['product_option'] = $this->getProductOptions($product_id);
			$data['product_related'] = $this->getProductRelated($product_id);
			$data['product_reward'] = $this->getProductRewards($product_id);
			$data['product_special'] = $this->getProductSpecials($product_id);
			$data['product_category'] = $this->getProductCategories($product_id);
			$data['product_download'] = $this->getProductDownloads($product_id);
			$data['product_layout'] = $this->getProductLayouts($product_id);
			$data['product_store'] = $this->getProductStores($product_id);
			$data['product_recurrings'] = $this->getRecurrings($product_id);

			$this->addProduct($data);
		}
	}

	public function deleteProduct($product_id) {
$this->db->query("DELETE FROM " . DB_PREFIX . "hb_cart WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_cost WHERE product_id = '" . (int)$product_id . "'");		
            
$this->db->query("DELETE FROM " . DB_PREFIX . "product_description_seo WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_cost WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_stock_history WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_stock_history WHERE product_id = '" . (int)$product_id . "'");		
            
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_recurring WHERE product_id = " . (int)$product_id);
		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");

        /**
         * Marketplace code starts here
         */
        if ($this->config->get('module_marketplace_status')) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id = '" . (int)$product_id . "'");
            //commented because it's also important for order
            //$this->db->query("DELETE FROM " . DB_PREFIX . "customerpartner_sold_tracking WHERE product_id = '" . (int)$product_id . "'");
        }
        /**
         * Marketplace code Ends here
         */
      
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE product_id = '" . (int)$product_id . "'");


				
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `key` like 'seopack%'");
			
				foreach ($query->rows as $result) {
						if (!$result['serialized']) {
							$data[$result['key']] = $result['value'];
						} else {
							if ($result['value'][0] == '{') {$data[$result['key']] = json_decode($result['value'], true);} else {$data[$result['key']] = unserialize($result['value']);}
						}
					}
					
				if (isset($data)) {$seopack_parameters = $data['seopack_parameters'];}
					else {
						$seopack_parameters['keywords'] = '%c%p';
						$seopack_parameters['tags'] = '%c%p';
						$seopack_parameters['metas'] = '%p - %f';
						}
				
				
				if (isset($seopack_parameters['ext'])) { $ext = $seopack_parameters['ext'];}
					else {$ext = '';}
					
				if ((isset($seopack_parameters['autokeywords'])) && ($seopack_parameters['autokeywords']))
					{	
						$query = $this->db->query("select pd.name as pname, cd.name as cname, pd.language_id as language_id, pd.product_id as product_id, p.sku as sku, p.model as model, p.upc as upc, m.name as brand  from " . DB_PREFIX . "product_description pd
								left join " . DB_PREFIX . "product_to_category pc on pd.product_id = pc.product_id
								inner join " . DB_PREFIX . "product p on pd.product_id = p.product_id
								left join " . DB_PREFIX . "category_description cd on cd.category_id = pc.category_id and cd.language_id = pd.language_id
								left join " . DB_PREFIX . "manufacturer m on m.manufacturer_id = p.manufacturer_id
								where p.product_id = '" . (int)$product_id . "';");
					
								
								//die('z');
						foreach ($query->rows as $product) {
														
							$bef = array("%", "_","\"","'","\\");
							$aft = array("", " ", " ", " ", "");
							
							$included = explode('%', str_replace(array(' ',','), '', $seopack_parameters['keywords']));
							
							$tags = array();
							
							if (in_array("p", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['pname']))))));}
							if (in_array("c", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['cname']))))));}
							if (in_array("s", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['sku']))))));}
							if (in_array("m", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['model']))))));}
							if (in_array("u", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['upc']))))));}
							if (in_array("b", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['brand']))))));}
							
							$keywords = '';
							
							foreach ($tags as $tag)
								{
								if (strlen($tag) > 2) 
									{
									
									$keywords = $keywords.' '.strtolower($tag);
									
									}
								}
								
							
							$exists = $this->db->query("select count(*) as times from " . DB_PREFIX . "product_description where product_id = ".$product['product_id']." and language_id = ".$product['language_id']." and meta_keyword like '%".$keywords."%';");
							
									foreach ($exists->rows as $exist)
										{
										$count = $exist['times'];
										}
							$exists = $this->db->query("select length(meta_keyword) as leng from " . DB_PREFIX . "product_description where product_id = ".$product['product_id']." and language_id = ".$product['language_id'].";");
							
									foreach ($exists->rows as $exist)
										{
										$leng = $exist['leng'];
										}

							if (($count == 0) && ($leng < 255)) {$this->db->query("update " . DB_PREFIX . "product_description set meta_keyword = concat(meta_keyword, '". htmlspecialchars($keywords) ."') where product_id = ".$product['product_id']." and language_id = ".$product['language_id'].";");}	
								
														
							}
					}
				if ((isset($seopack_parameters['autometa'])) && ($seopack_parameters['autometa']))
					{
						$query = $this->db->query("select pd.name as pname, p.price as price, cd.name as cname, pd.description as pdescription, pd.language_id as language_id, pd.product_id as product_id, p.model as model, p.sku as sku, p.upc as upc, m.name as brand from " . DB_PREFIX . "product_description pd
								left join " . DB_PREFIX . "product_to_category pc on pd.product_id = pc.product_id
								inner join " . DB_PREFIX . "product p on pd.product_id = p.product_id
								left join " . DB_PREFIX . "category_description cd on cd.category_id = pc.category_id and cd.language_id = pd.language_id
								left join " . DB_PREFIX . "manufacturer m on m.manufacturer_id = p.manufacturer_id
								where p.product_id = '" . (int)$product_id . "';");

						foreach ($query->rows as $product) {
														
							$bef = array("%", "_","\"","'","\\", "\r", "\n");
							$aft = array("", " ", " ", " ", "", "", "");
							
							$ncategory = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['cname']))));
							$nproduct = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['pname']))));
							$model = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['model']))));
							$sku = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['sku']))));
							$upc = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['upc']))));
							$content = strip_tags(html_entity_decode($product['pdescription']));
							$pos = strpos($content, '.');							   
							if($pos === false) {}
								else { $content =  substr($content, 0, $pos+1);	}
							$sentence = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft, $content))));
							$brand = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['brand']))));
							$price = trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft, number_format($product['price'], 2)))));
							
							$bef = array("%c", "%p", "%m", "%s", "%u", "%f", "%b", "%$");
							$aft = array($ncategory, $nproduct, $model, $sku, $upc, $sentence, $brand, $price);
							
							$meta_description = str_replace($bef, $aft,  $seopack_parameters['metas']);
							
							$exists = $this->db->query("select count(*) as times from " . DB_PREFIX . "product_description where product_id = ".$product['product_id']." and language_id = ".$product['language_id']." and meta_description not like '%".htmlspecialchars($meta_description)."%';");
							
									foreach ($exists->rows as $exist)
										{
										$count = $exist['times'];
										}
							
							if ($count) {$this->db->query("update " . DB_PREFIX . "product_description set meta_description = concat(meta_description, '". htmlspecialchars($meta_description) ."') where product_id = ".$product['product_id']." and language_id = ".$product['language_id'].";");}			
														
							}
					}
				if ((isset($seopack_parameters['autotags'])) && ($seopack_parameters['autotags']))
					{
					$query = $this->db->query("select pd.name as pname, pd.tag, cd.name as cname, pd.language_id as language_id, pd.product_id as product_id, p.sku as sku, p.model as model, p.upc as upc, m.name as brand from " . DB_PREFIX . "product_description pd
							inner join " . DB_PREFIX . "product_to_category pc on pd.product_id = pc.product_id
							inner join " . DB_PREFIX . "product p on pd.product_id = p.product_id
							inner join " . DB_PREFIX . "category_description cd on cd.category_id = pc.category_id and cd.language_id = pd.language_id
							left join " . DB_PREFIX . "manufacturer m on m.manufacturer_id = p.manufacturer_id
							where p.product_id = '" . (int)$product_id . "';");
					
					foreach ($query->rows as $product) {
						
						$newtags ='';
						
						$included = explode('%', str_replace(array(' ',','), '', $seopack_parameters['tags']));
						
						$tags = array();
						
						
						$bef = array("%", "_","\"","'","\\");
						$aft = array("", " ", " ", " ", "");
						
							if (in_array("p", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['pname']))))));}
							if (in_array("c", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['cname']))))));}
							if (in_array("s", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['sku']))))));}
							if (in_array("m", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['model']))))));}
							if (in_array("u", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['upc']))))));}
							if (in_array("b", $included)) {$tags = array_merge($tags, explode(' ',trim($this->db->escape(htmlspecialchars_decode(str_replace($bef, $aft,$product['brand']))))));}
							
							foreach ($tags as $tag)
								{
								if (strlen($tag) > 2) 
									{
									if ((strpos($product['tag'], strtolower($tag)) === false) && (strpos($newtags, strtolower($tag)) === false))
										{
											$newtags .= ' '.strtolower($tag).',';											
										}			
									}
								}
							
														
							if ($product['tag']) {
								$newtags = trim($this->db->escape($product['tag']) . $newtags,' ,');
								$this->db->query("update " . DB_PREFIX . "product_description set tag = '$newtags' where product_id = '". $product['product_id'] ."' and language_id = '". $product['language_id'] ."';");
								}
								else {
								$newtags = trim($newtags,' ,');
								$this->db->query("update " . DB_PREFIX . "product_description set tag = '$newtags' where product_id = '". $product['product_id'] ."' and language_id = '". $product['language_id'] ."';");
								}
																				
						}
						
					}
				if ((isset($seopack_parameters['autourls'])) && ($seopack_parameters['autourls']))
					{
						require_once(DIR_APPLICATION . 'controller/extension/extension/seopack.php');
						$seo = new ControllerExtensionExtensionSeoPack('');
						
						$query = $this->db->query("SELECT pd.product_id, pd.name, pd.language_id ,l.code FROM ".DB_PREFIX."product p 
								inner join ".DB_PREFIX."product_description pd ON p.product_id = pd.product_id 
								inner join ".DB_PREFIX."language l on l.language_id = pd.language_id 
								where p.product_id = '" . (int)$product_id . "';");

						
						foreach ($query->rows as $product_row ){	

							
							if( strlen($product_row['name']) > 1 ){
							
								$slug = $seo->generateSlug($product_row['name']).$ext;
								$exist_query = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.query = 'product_id=" . $product_row['product_id'] . "' and language_id=".$product_row['language_id']);
								
								if(!$exist_query->num_rows){
									
									$exist_keyword = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "'");
									if($exist_keyword->num_rows){ 
										$exist_keyword_lang = $this->db->query("SELECT query FROM " . DB_PREFIX . "seo_url WHERE store_id = 0 and " . DB_PREFIX . "seo_url.keyword = '" . $slug . "' AND " . DB_PREFIX . "seo_url.query <> 'product_id=" . $product_row['product_id'] . "'");
										if($exist_keyword_lang->num_rows){
												$slug = $seo->generateSlug($product_row['name']).'-'.rand().$ext;
											}
											else
											{
												$slug = $seo->generateSlug($product_row['name']).'-'.$product_row['code'].$ext;
											}
										}
										
									
									$add_query = "INSERT INTO " . DB_PREFIX . "seo_url (query, keyword, language_id) VALUES ('product_id=" . $product_row['product_id'] . "', '" . $slug . "', " . $product_row['language_id'] . ")";
									$this->db->query($add_query);
									
								}
							}
						}
					}
				
			

                // Store Pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status')) {

                   $this->db->query("DELETE FROM `" . DB_PREFIX . "pickup_product` WHERE product_id = '" . (int)$product_id . "'");
                   $this->db->query("DELETE FROM `" . DB_PREFIX . "pickup_product_option` WHERE product_id = '" . (int)$product_id . "'");
                }
                // Store Pickup module code ends here
                    
		$this->cache->delete('product');
	}

	public function getProduct($product_id) {
		
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_cost pc ON (p.product_id = pc.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
            

		return $query->row;
	}


	public function getProductsForAutocomplete($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_cost pc ON (p.product_id = pc.product_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
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
            
	public function getProducts($data = array()) {
		$sql = "SELECT *";
if ($this->config->get('adv_plist_sold_status')) {
	if ($this->config->get('adv_sold_order_status')) {
		$sql .= ", (SELECT SUM(op.quantity) FROM `" . DB_PREFIX . "order_product` op, `" . DB_PREFIX . "order` o WHERE op.product_id = p.product_id AND op.order_id = o.order_id AND o.order_status_id IN (" . implode(",", $this->config->get('adv_sold_order_status')) . ")) AS sold";
	} else {
		$sql .= ", (SELECT SUM(op.quantity) FROM `" . DB_PREFIX . "order_product` op, `" . DB_PREFIX . "order` o WHERE op.product_id = p.product_id AND op.order_id = o.order_id AND o.order_status_id > 0) AS sold";
	}
}

if ($this->config->get('adv_plist_profit_status')) {
	$sql .= ", (p.price-pc.cost) AS profit";
}
if ($this->config->get('adv_plist_profit_margin_status')) {	
	$sql .= ", (((p.price-pc.cost)/p.price)*100) AS profit_margin";
}
if ($this->config->get('adv_plist_profit_markup_status')) {	
	$sql .= ", (((p.price-pc.cost)/pc.cost)*100) AS profit_markup";
}
if ($this->config->get('adv_plist_manufacturer_status')) {	
	$sql .= ", (SELECT m.name FROM `" . DB_PREFIX . "manufacturer` m WHERE m.manufacturer_id = p.manufacturer_id) AS manufacturer";
}
if ($this->config->get('adv_plist_supplier_status')) {	
	$sql .= ", (SELECT s.name FROM `" . DB_PREFIX . "supplier` s WHERE s.supplier_id = pc.supplier_id) AS supplier";
}	

$sql .= " FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_cost pc ON (p.product_id = pc.product_id)";
	
if ($this->config->get('adv_plist_category_status')) {	
if (!empty($data['filter_category_id'])) {
	$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
}
}
$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (!empty($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_cost']) && !is_null($data['filter_cost'])) {
			$sql .= " AND pc.cost LIKE '" . $this->db->escape($data['filter_cost']) . "%'";
		}
					
		if (!empty($data['filter_category_id'])) {
			$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
		}
					
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}	
					
		if (!empty($data['filter_supplier_id'])) {
			$sql .= " AND pc.supplier_id = '" . (int)$data['filter_supplier_id'] . "'";
		}	
            

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',

			'p2c.category_id',
			'manufacturer',
			'pc.cost',
			'profit',
			'profit_margin',
			'profit_markup',
			'sold',	
			'supplier',		
            
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
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
		//echo '<pre>' . $sql . '</pre>';

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getProductDescriptions($product_id) {
		$product_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword'],
				'tag'              => $result['tag']
			);
		}


			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description_seo WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array_merge($product_description_data[$result['language_id']] ,array(
				'custom_imgtitle' => $result['custom_imgtitle'],
				'custom_alt'      => $result['custom_alt'],
				'custom_h1'       => $result['custom_h1'],
				'custom_h2'		  => $result['custom_h2']
			));
		}
			
		return $product_description_data;
	}

	public function getProductCategories($product_id) {
		$product_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getProductFilters($product_id) {
		$product_filter_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_filter_data[] = $result['filter_id'];
		}

		return $product_filter_data;
	}

	public function getProductAttributes($product_id) {
		$product_attribute_data = array();

		$product_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' GROUP BY attribute_id");

		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();

			$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}

			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}

		return $product_attribute_data;
	}


	public function getProductOptionsHistory($product_id, $data = array()) {
		$query = $this->db->query("SELECT CONCAT(poh.product_option_id,poh.option_id,poh.option_value_id) AS options, od.name AS option_name, ovd.name AS option_value FROM `" . DB_PREFIX . "product_option_stock_history` poh, `" . DB_PREFIX . "product_option_value` pov, `" . DB_PREFIX . "option_description` od, `" . DB_PREFIX . "option_value_description` ovd WHERE poh.product_id = '" . (int)$product_id . "' AND poh.product_id = pov.product_id AND poh.option_value_id = pov.option_value_id AND poh.option_id = od.option_id AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND poh.option_value_id = ovd.option_value_id AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY od.name, ovd.name ORDER BY od.name, ovd.name ASC");		

		return $query->rows;
	}	

	public function getProductSuppliersHistory($product_id, $data = array()) {
		$query = $this->db->query("SELECT psh.supplier_id AS supplier_id, s.name AS supplier_name FROM `" . DB_PREFIX . "product_stock_history` psh, `" . DB_PREFIX . "supplier` s WHERE psh.product_id = '" . (int)$product_id . "' AND psh.supplier_id = s.supplier_id GROUP BY psh.supplier_id ORDER BY s.name ASC");		

		return $query->rows;
	}
	
	public function getOrderStatuses($data = array()) {
		$query = $this->db->query("SELECT os.name, os.order_status_id FROM `" . DB_PREFIX . "order_status` os WHERE os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY LCASE(os.name) ASC");
		
		return $query->rows;	
	}

	public function getOrderOptions($product_id, $data = array()) {
		$query = $this->db->query("SELECT DISTINCT LCASE(CONCAT(oo.name, oo.value, oo.type)) AS options, oo.name AS option_name, oo.value AS option_value FROM `" . DB_PREFIX . "order` o, `" . DB_PREFIX . "order_product` op, `" . DB_PREFIX . "order_option` oo WHERE o.order_id = op.order_id AND op.order_product_id = oo.order_product_id AND op.product_id = '" . (int)$product_id . "' AND o.order_status_id > '0' AND (oo.type = 'radio' OR oo.type = 'checkbox' OR oo.type = 'select' OR oo.type = 'image' OR oo.type = 'colour' OR oo.type = 'size' OR oo.type = 'multiple') GROUP BY oo.name, oo.value, oo.type ORDER BY oo.name, oo.value, oo.type ASC");		

		return $query->rows;
	}
			
	public function deleteProductHistory($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_stock_history WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_stock_history WHERE product_id = '" . (int)$product_id . "'");	
		return true;
	}	
	
    public function saveProductStockHistoryComment($product_stock_history_id, $comment) {
        $this->db->query("UPDATE `" . DB_PREFIX . "product_stock_history` SET comment = '" . $comment . "' WHERE product_stock_history_id = '" . (int)$product_stock_history_id . "'");

        $query = $this->db->query("SELECT comment FROM `" . DB_PREFIX . "product_stock_history` WHERE product_stock_history_id = '" . (int)$product_stock_history_id . "'");
        $row = $query->row;

        return $row['comment'];
    }	
	
    public function saveProductOptionStockHistoryComment($product_option_stock_history_id, $comment) {
		$this->db->query("UPDATE `" . DB_PREFIX . "product_option_stock_history` SET comment = '" . $comment . "' WHERE product_option_stock_history_id = '" . (int)$product_option_stock_history_id . "'");

		$query = $this->db->query("SELECT comment FROM `" . DB_PREFIX . "product_option_stock_history` WHERE product_option_stock_history_id = '" . (int)$product_option_stock_history_id . "'");
        $row = $query->row;

        return $row['comment'];
    }	
            
	public function getProductOptions($product_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order ASC");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			
			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "product_option_cost poc ON (pov.product_option_value_id = poc.product_option_value_id) LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) WHERE pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY ov.sort_order ASC");
            

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],

						'sku'                     => $product_option_value['sku'],	
						'cost'                    => $product_option_value['cost'],	
						'cost_amount'             => $product_option_value['cost_amount'],							
						'cost_prefix'             => $product_option_value['cost_prefix'],
						'costing_method'     	  => $product_option_value['costing_method'],
            
					'points'                  => $product_option_value['points'],
					'points_prefix'           => $product_option_value['points_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix']
				);
			}

			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}

		return $product_option_data;
	}

	public function getProductOptionValue($product_id, $product_option_value_id) {
		$query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row;
	}

	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

		return $query->rows;
	}

	public function getProductSpecials($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

		return $query->rows;
	}

	public function getProductRewards($product_id) {
		$product_reward_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}

		return $product_reward_data;
	}

	public function getProductDownloads($product_id) {
		$product_download_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}

		return $product_download_data;
	}

	public function getProductStores($product_id) {
		$product_store_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}

		return $product_store_data;
	}
	
	public function getProductSeoUrls($product_id) {
		$product_seo_url_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
		}

		return $product_seo_url_data;
	}
	
	public function getProductLayouts($product_id) {
		$product_layout_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_layout_data[$result['store_id']] = $result['layout_id'];
		}

		return $product_layout_data;
	}

	public function getProductRelated($product_id) {
		$product_related_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}

		return $product_related_data;
	}

	public function getRecurrings($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = '" . (int)$product_id . "'");

		return $query->rows;
	}

	public function getTotalProducts($data = array()) {
		
$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_cost pc ON (p.product_id = pc.product_id)";

if ($this->config->get('adv_plist_category_status')) {	
if (!empty($data['filter_category_id'])) {
	$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			
}
}		 
            

		$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_cost']) && !is_null($data['filter_cost'])) {
			$sql .= " AND pc.cost LIKE '" . $this->db->escape($data['filter_cost']) . "%'";
		}
					
		if (!empty($data['filter_category_id'])) {
			$sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
		}
					
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}	
					
		if (!empty($data['filter_supplier_id'])) {
			$sql .= " AND pc.supplier_id = '" . (int)$data['filter_supplier_id'] . "'";
		}	
            

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotalProductsByTaxClassId($tax_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByStockStatusId($stock_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		return $query->row['total'];
	}


			public function getTotalProductsByReleaseStatusId($release_status_id) {
				$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE release_status_id = '" . (int)$release_status_id . "'");
				return $query->row['total'];
			}		  
		
	public function getTotalProductsByWeightClassId($weight_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLengthClassId($length_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByDownloadId($download_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByManufacturerId($manufacturer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByAttributeId($attribute_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByOptionId($option_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByProfileId($recurring_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_recurring WHERE recurring_id = '" . (int)$recurring_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}
}
