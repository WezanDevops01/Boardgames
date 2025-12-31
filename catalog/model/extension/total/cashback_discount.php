<?php
class ModelExtensionTotalCashbackDiscount extends Model {
	public function getTotal($total) {
		if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$variableprefix = 'total_';
		}else{
			$variableprefix = '';
		}
		
		if ($this->customer->isLogged()){
			$customer_id = $this->customer->getId();
			$customer_supercash = $this->getSupercash($customer_id);
			if ($customer_supercash > 0) {
				$discount_from_supercash = ($customer_supercash * $this->config->get($variableprefix.'cashback_discount_percent'))/100;
				
				if ($total['total'] < $discount_from_supercash) {
					$discount_from_supercash = $total['total'];
				}
				
				$this->load->language('extension/total/cashback_discount');
				
				if (($total['total'] > 0) && ($total['total'] >  $this->config->get($variableprefix.'cashback_discount_total'))) {
					$total['totals'][] = array(
						'code'       => 'cashback_discount',
						'title'      => $this->language->get('text_cashback_discount'),
						'value'      => -$discount_from_supercash,
						'sort_order' => $this->config->get($variableprefix.'cashback_discount_sort_order')
					);
				
					$total['total'] -= $discount_from_supercash;
				}
				
			}
		}
	}
	
	public function getSupercash($customer_id = 0){	
		$query = $this->db->query("SELECT sum(available) as supercash FROM ".DB_PREFIX."customer_supercash WHERE customer_id = '".(int)$customer_id."'");
		if ($query->row) {
			$customer_supercash = $query->row['supercash'];
		}else{
			$customer_supercash = 0;
		}
		
		return $customer_supercash;
	}
	
	public function confirm($order_info, $order_total) {
		$this->load->language('extension/total/cashback_discount');

		if ($order_info['customer_id']) {
			$amount = -$order_total['value'];
			$remaining = $amount;
			$history = array();
			$customer_id = $order_info['customer_id'];
			while ($remaining > 0) {
					$query_get_supercash = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_supercash WHERE customer_id = '" . (int)$customer_id . "' AND available > 0 ORDER BY expiry_date ASC LIMIT 1");
					$sc = $query_get_supercash->row;
					
					$available = $sc['available'];
					if ($available >= $remaining){
						$used = $sc['used'] + $remaining;
						$deduct = $available - $remaining;
						$removing = $remaining;
						$remaining = 0;
					}else{
						$used = $sc['used'] + $available;
						$deduct = $sc['amount'] - $used;
						$removing = $available;
						$remaining = $remaining - $used;
					}
					$this->db->query("UPDATE " . DB_PREFIX . "customer_supercash SET used = '".(float)$used."', available = '".(float)$deduct."' WHERE id = '" . (int)$sc['id'] . "'");
					$history[] = array(
						$sc['id'] => $removing
					);
				}
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_supercash SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', amount = '" . (float)$order_total['value'] . "', type = 'dr', debit_history = '".$this->db->escape(json_encode($history))."', date_added = NOW()");
			
			//send event alert
			$data['details'] = array(
				'type'			=> 'dr',
				'customer_id' 	=> $order_info['customer_id'],
				'language_id'	=> $order_info['language_id'],
				'store_id'		=> $order_info['store_id'],
				'amount'		=> $order_total['value'],	
				'description'	=> sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])	
			);
			$this->event_alert($data['details']);
		}
	}

	public function unconfirm($order_id) {
		$query = $this->db->query("SELECT debit_history FROM " . DB_PREFIX . "customer_supercash WHERE order_id = '" . (int)$order_id . "'");
		if ($query->row) {
			$debit_history = $query->row['debit_history'];
			$debit_history = json_decode($debit_history, true);
		
			foreach ($debit_history as $dh) {
				foreach ($dh as $key => $value) {
					$query_get_supercash = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_supercash WHERE id = '" . (int)$key . "'");
					$sc = $query_get_supercash->row;
					$used = $sc['used'] - $value;
					$available = $sc['available'] + $value;
					$this->db->query("UPDATE " . DB_PREFIX . "customer_supercash SET used = '".(float)$used."', available = '".(float)$available."' WHERE id = '" . (int)$key . "'");
				}
			}
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_supercash WHERE order_id = '" . (int)$order_id . "'");
	}
	
	public function supercashUsed($customer_id, $start_date, $end_date){
		$query = $this->db->query("SELECT sum(amount) as total FROM " . DB_PREFIX . "customer_supercash WHERE customer_id = '".(int)$customer_id."' AND (date(date_added) BETWEEN '".$this->db->escape($start_date)."' AND '".$this->db->escape($end_date)."') AND (type = 'dr' OR type = 'dr_exp' )");
		return $query->row['total'];
	}
	
	public function supercashCredited($customer_id, $start_date, $end_date){
		$query = $this->db->query("SELECT sum(amount) as total FROM " . DB_PREFIX . "customer_supercash WHERE customer_id = '".(int)$customer_id."' AND (date(date_added) BETWEEN '".$this->db->escape($start_date)."' AND '".$this->db->escape($end_date)."') AND type = 'cr' ");
		return $query->row['total'];
	}


	////////

	public function getSuperCashList($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "customer_supercash` WHERE customer_id = '" . (int)$this->customer->getId() . "'";

		$sort_data = array(
			'amount',
			'description',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
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

	public function getTotalSuperCashList() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_supercash` WHERE customer_id = '" . (int)$this->customer->getId() . "'");

		return $query->row['total'];
	}

	public function getTotalAmount() {
		$query = $this->db->query("SELECT SUM(available) AS total FROM `" . DB_PREFIX . "customer_supercash` WHERE customer_id = '" . (int)$this->customer->getId() . "' GROUP BY customer_id");

		if ($query->num_rows) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function datedisplay($date){
		if (($date == NULL) or ($date == '0000-00-00') or ($date == '9999-12-31')) {
			return '';
		}else {
			return date($this->language->get('date_format_short'), strtotime($date));
		}
	}

	//////

	public function isExtensionInstalled($code){
		$query = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."extension` WHERE `code` = '".$this->db->escape($code)."'");	
		if ($query->row['total'] > 0){
			return true;
		}else{
			return false;
		}
	}

	public function event_alert($data){
		$this->load->language('extension/total/cashback_discount');

		if ($this->isExtensionInstalled('email_templates')){
			$this->load->model('extension/module/email_builder');
			$use_email_designer_app = true;
		}else{
			$use_email_designer_app = false;
		}

		$this->load->model('setting/setting');
		
		$type 		 	= $data['type']; //in admin type can be only credit or debit. Expiring or expired must be triggered from catalog
		$customer_id 	= (int)$data['customer_id'];
		$amount 	 	= (float)$data['amount'];
		$data['amount']	= $this->currency->format($amount, $this->config->get('config_currency'));
		
		//STEP 1 : Get Customer language and store ID
		$customer_data = $this->db->query("SELECT * FROM `".DB_PREFIX."customer` WHERE customer_id = '".(int)$customer_id."' LIMIT 1");
		if ($customer_data->row) {
			$data['firstname'] 		= $customer_data->row['firstname'];
			$data['lastname'] 		= $customer_data->row['lastname'];
			$data['email'] 			= $customer_data->row['email'];
			$data['telephone'] 		= $customer_data->row['telephone'];

			if (!isset($data['language_id'])){
				$language_id 	= (isset($customer_data->row['language_id'])) ? (int)$customer_data->row['language_id'] : '9999';
			}else{
				$language_id	= $data['language_id'];
			}

			if (!isset($data['store_id'])){
				$store_id 	= $customer_data->row['store_id'];
			}else{
				$store_id	= $data['store_id'];
			}			
			
			if ($language_id == 0){
				$this->addSuperCashLog('Language ID is set to 0 in the database. Please fix language ID in customer table for the customer ID '. $customer_id .'. Sending Email in the default language!');
				$language_id = $this->config->get('config_language_id');
			}
			
			$extn_info = $this->model_setting_setting->getSetting('supercash', $store_id);
			
			switch ($type) {
				case 'cr':
					$email_enabled 		= isset($extn_info['supercash_alert_cr'])? true : false;
					$sms_enabled 		= isset($extn_info['supercash_sms_cr'])? true : false;
					$email_template_id 	= $extn_info['supercash_t_cr'.$language_id];
					$sms_template 		=  isset($extn_info['supercash_t_cr_sms'.$language_id])? $extn_info['supercash_t_cr_sms'.$language_id]:'';
					break;

				case 'expired':
					$email_enabled 		= isset($extn_info['supercash_alert_expd'])? true : false;
					$sms_enabled 		= isset($extn_info['supercash_sms_expd'])? true : false;
					$email_template_id 	= $extn_info['supercash_t_expd'.$language_id];
					$sms_template 		=  isset($extn_info['supercash_t_expd_sms'.$language_id])? $extn_info['supercash_t_expd_sms'.$language_id]:'';
					break;
				
				case 'expiring':
					$email_enabled 		= isset($extn_info['supercash_alert_expg'])? true : false;
					$sms_enabled 		= isset($extn_info['supercash_sms_expg'])? true : false;
					$email_template_id 	= $extn_info['supercash_t_expg'.$language_id];
					$sms_template 		= isset($extn_info['supercash_t_expg_sms'.$language_id])? $extn_info['supercash_t_expg_sms'.$language_id]:'';
					break;

				default:
					$email_enabled 	= false;
					$sms_enabled	= false;
					$this->addSuperCashLog('No credit or debit. Invalid type!');
					die('Aborting Event Alert');
					break;
			}			
			
			// STEP 2 EMAIL
			if ($email_enabled == true){
				$template_id 	= $email_template_id;

				if ($use_email_designer_app){
					$template_data 	= $this->model_extension_module_email_builder->builtTemplate($template_id);
				}else{
					$template_data 	= $this->getTemplateData($template_id);
				}
				
				$to = $data['email']; 
				
				$email_subject = $template_data['email_options']['email_subject'];
				$email_content = $template_data['email_content'];

				//replacing shortcode variables
				foreach ($data as $key => $value) {
					if (!is_array($value)) {
						$email_content 		= str_replace('{'.$key.'}',$value,$email_content);
						$email_subject 		= str_replace('{'.$key.'}',$value,$email_subject);
					}
				}
				
				$email_data = array(          
						'to'              => $to,             
						'from'            => $template_data['email_options']['sender_email'],
						'store_name'      => $template_data['email_options']['sender_name'],
						'email_replyto'   => $template_data['email_options']['email_replyto'],
						'subject'         => $email_subject,
						'content'         => $email_content,
						'attachments'     => (isset($template_data['email_options']['email_attachments']))? $template_data['email_options']['email_attachments']: '',
						'bcc'             => $template_data['email_options']['email_bcc'],
						'template_id'     => $template_id,
						'store_id'        => $template_data['store_id'],
						'type'            => 'account', 
						'cron'            => false
					);
				
				
				if ($use_email_designer_app){		
					$this->model_extension_module_email_builder->sendemail($email_data);
					$this->addSuperCashLog("Email (".$type.") sent to ".$to." via template designer extn");
				}else{	 //else normal email
					$this->sendemail($email_data);
					$this->addSuperCashLog("Email (".$type.") sent to ".$to);
				}
				
			}//email enable ends

			// STEP 3 SMS
			if ($sms_enabled == true) {
				$sms_api_url = $extn_info['supercash_sms_api'];

				//replacing shortcode variables
				foreach ($data as $key => $value) {
					if (!is_array($value)) {
						$sms_template 		= str_replace('{'.$key.'}',$value,$sms_template);
					}
				}
				
				$sms_api_url = str_replace('{msg}', $sms_template, $sms_api_url);
				$sms_api_url = str_replace('{to}', $data['telephone'], $sms_api_url);

				$sms_data = array(
					'sms_api' 	=> $sms_api_url,
					'telephone' => $data['telephone'],
					'sms'		=> $sms_template
				);
				
				if ($this->isExtensionInstalled('opencart_sms')){
					$sms_config = $this->model_setting_setting->getSetting('hb_sms', $store_id);
					$this->load->library('hbsms');
					$this->hbsms->call_api($data['telephone'], $sms_template, '1', $sms_config);
					$this->addSuperCashLog('SMS API connected to HuntBee OpenCart SMS. SMS will be sent via OpenCart SMS App.');
				}else{
					$this->sendSMS($sms_data);
				}
			}//sms enabled ends
			
		}else{
			$this->addSuperCashLog('No Customer Data found for '.$customer_id);
		}
	}

	public function isCreditedForOrder($order_id){
		$sql = "SELECT count(*) as count FROM ".DB_PREFIX."customer_supercash WHERE order_id = '".(int)$order_id."' AND type = 'cr'";
		$results = $this->db->query($sql);
		if ($results->row['count'] > 0){
			return true;
		}else{
			return false;
		}
	}
	
	public function totalCreditOnPurchase($order_info){
		$total_credit = 0;

		$order_id 		= $order_info['order_id'];
		$store_id 		= $order_info['store_id'];
		$customer_id 	= $order_info['customer_id'];

		if ($customer_id > 0){
			$this->load->model('setting/setting');
			$extn_info = $this->model_setting_setting->getSetting('supercash', $store_id);

			$rule 	= isset($extn_info['supercash_order_rules'])? $extn_info['supercash_order_rules'] : 'product';
			$factor = isset($extn_info['supercash_order_rule_factor'])? $extn_info['supercash_order_rule_factor'] : '0';
			$result = array();

			switch ($rule) {
				case 'sub_total':
					$result = $this->db->query("SELECT `value` * ".(float)$factor." as total FROM `".DB_PREFIX."order_total` WHERE order_id = '".(int)$order_id."' AND `code` = 'sub_total'");
					break;

				case 'total':
					$result = $this->db->query("SELECT `value` * ".(float)$factor." as total FROM `".DB_PREFIX."order_total` WHERE order_id = '".(int)$order_id."' AND `code` = 'total'");
					break;	
				
				default:
					$result = $this->db->query("SELECT SUM((SELECT supercash FROM ".DB_PREFIX."product WHERE product_id = a.product_id) * a.quantity) as total FROM `".DB_PREFIX."order_product` a WHERE a.order_id = '".(int)$order_id."'");
					break;
			}

			if ($result->row['total']) {
				$total_credit = $result->row['total'];
			}
		}

		return $total_credit;
	}

	public function getStore($store_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "store WHERE store_id = '" . (int)$store_id . "'");
		return $query->row;
	}

	public function getTemplateData($template_id){
		$template['email_content'] = 'Template content not set!';
		$results = $this->db->query("SELECT * FROM `".DB_PREFIX."hb_build_template` WHERE `id` = '".(int)$template_id."'");
		$template_data = $results->row;
		
		if (!empty($template_data)) {
			//GET DATA
			$store_id 			= $template_data['store_id'];
			$draft_head 		= $template_data['draft_head'];
			$draft_body 		= $template_data['draft_body'];
			$loaded_template_id = $template_data['loaded_template_id'];
			$editor				= $template_data['editor'];
			
			$current_date 		= date('d-M-Y');
			$draft_body 		= str_replace('{server_date}',$current_date,$draft_body);
			
			$email_options 			= json_decode($template_data['email_options'], true);
			
			$this->load->model('setting/setting');
			$store_config 		= $this->model_setting_setting->getSetting('config', $store_id);
			$config_currency 	= isset($store_config['config_currency']) ? $store_config['config_currency'] : $this->config->get('config_currency');
			$config_tax 		= isset($store_config['config_tax']) ? $store_config['config_tax'] : $this->config->get('config_tax');
			$config_review_status = isset($store_config['config_review_status']) ? $store_config['config_review_status'] : $this->config->get('config_review_status');
			$config_seo_url = isset($store_config['config_seo_url']) ? $store_config['config_seo_url'] : $this->config->get('config_seo_url');
			
			$store_name = isset($store_config['config_name']) ? $store_config['config_name'] : $this->config->get('config_name');
			$store_ssl = isset($store_config['config_url']) ? $store_config['config_url'] : HTTPS_SERVER;
			$store_email = isset($store_config['config_email']) ? $store_config['config_email'] : $this->config->get('config_email');

			$draft_body 		= str_replace('{store_email}',$store_email,$draft_body);			
			$draft_body 		= str_replace('{store_name}',$store_name,$draft_body);
			$draft_body 		= str_replace('{store_url}',$store_ssl,$draft_body);
			
			$allowed_store_config = array('config_meta_title','config_meta_description','config_meta_keyword','config_name','config_owner','config_address','config_geocode','config_email','config_telephone','config_fax','config_image','config_open','config_comment');
			foreach ($store_config as $key => $value) {
				if (!is_array($value) & in_array($key,$allowed_store_config)) {
					$draft_body      					= str_replace('{'.$key.'}',$value,$draft_body);
					$draft_head      					= str_replace('{'.$key.'}',$value,$draft_head);
					$email_options['email_subject']     = str_replace('{'.$key.'}',$value,$email_options['email_subject']);
				}
			}
			
			$content = $draft_body;

			$template['store_id']      = $store_id;
			
			if ($email_options) {
				$content = str_replace('{email_subject}', $email_options['email_subject'], $content);
				$template['email_content'] = $content;
				$template['email_options'] = $email_options;
				$template['label']         = $template_data['template_label'];
			}
			
		}	
		return $template;
	}

	public function sendemail($data){
		error_reporting(0);
		$result = false;
		try{
			if (version_compare(VERSION,'2.0.1.1','<=' )) {
				$mail = new Mail($this->config->get('config_mail'));
				$mail->protocol = $this->config->get('config_mail_protocol');
			}else {
				if (version_compare(VERSION,'2.3.0.2','>' )) {
					$mail = new Mail($this->config->get('config_mail_engine'));
				}else{
					$mail = new Mail();
					$mail->protocol = $this->config->get('config_mail_protocol');
				}				
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');			
			}
						
			$mail->setTo($data['to']);
			$mail->setFrom($data['from']);
			$mail->setSender($data['store_name']);
			if ($data['email_replyto']){
				$mail->setReplyTo($data['email_replyto']);
			}
			$mail->setSubject(html_entity_decode($data['subject'], ENT_QUOTES, 'UTF-8'));
			$mail->setHtml(wordwrap($data['content'],50));

			$mail->send();
			if (!empty($data['bcc'])){
				$bccs = explode(',',$data['bcc']);
				foreach ($bccs as $bcc) {
					$mail->setTo($bcc);
					$mail->send();
				}
			}
			$result = 'Email sent to '.$data['to'];
		}catch (Exception $e){
			$this->log->write('Extension - HuntBee SuperCash: Email failed sending to '.$data['to'].'. Issue: '.$e->getMessage());
			$result	= false;
		}

		return $result;
	}

	public function sendsms($data){
		$sms_api = html_entity_decode($data['sms_api'], ENT_QUOTES, 'UTF-8');
		$sms_api = str_replace('{to}', $data['telephone'], $sms_api);
		$sms_api = str_replace('{msg}', $data['sms'], $sms_api);

		$curl_enabled = function_exists('curl_version') ? true : false;
		if ($curl_enabled){
			$ch = curl_init();  
			curl_setopt($ch, CURLOPT_URL,$sms_api);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_HEADER, false); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$sms_output = curl_exec($ch);
			if (curl_error($ch)) {
				$sms_output = 'ORDER SMS - Curl Error : ' . curl_error($ch);
				$this->addSuperCashLog($sms_output);
				$this->log->write($sms_output);
			}else{
				$this->addSuperCashLog('SMS Sent to '.$data['telephone']);
			}
			curl_close ($ch);
		}else{
			$this->addSuperCashLog('!!!! CURL function is disabled in your Server. SMS will not be Sent !!!!');
		}
	}

	public function add_activity_supercash($data){
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_supercash SET customer_id = '" . (int)$data['customer_id'] . "', order_id = '".(int)$data['order_id']."', description = '" . $this->db->escape($data['description']) . "', amount = '" . (float)$data['amount'] . "', available = '" . (float)$data['amount'] . "', expiry_date = '" . $this->db->escape($data['expiry']) . "', type = 'cr', date_added = NOW()");	
		$this->addSuperCashLog($data['description']);	
		$data['type'] = 'cr';
		$this->event_alert($data);
	}

	public function add_order_supercash($order_info){
		$this->load->language('extension/total/cashback_discount');

		$this->load->model('setting/setting');
		$extn_info = $this->model_setting_setting->getSetting('supercash', (int)$order_info['store_id']);

		$order_id 		= $order_info['order_id'];

		if (!$this->isCreditedForOrder($order_id)){
			$customer_id 	= $order_info['customer_id'];
			$store_id 		= $order_info['store_id'];
			$language_id 	= $order_info['language_id'];
			$amount 		= $this->totalCreditOnPurchase($order_info);//Get total supercash of the order
			$expiry 		= date("Y-m-d", strtotime("+".$extn_info['supercash_exp_days']." day"));//get expiry date	
			$description 	= sprintf($this->language->get('text_order_supercash_credited'), $order_id);

			if ($amount > 0) {
				//add to customer
				$this->db->query("INSERT INTO " . DB_PREFIX . "customer_supercash SET customer_id = '" . (int)$customer_id . "', order_id = '".(int)$order_id."', description = '" . $this->db->escape($description) . "', amount = '" . (float)$amount . "', available = '" . (float)$amount . "', expiry_date = '" . $this->db->escape($expiry) . "', type = 'cr', date_added = NOW()");
				
				$this->addSuperCashLog($description);
				
				$data['details'] = array(
					'type'			=> 'cr',
					'customer_id' 	=> $customer_id,
					'language_id'	=> $language_id,
					'store_id'		=> $store_id,
					'amount'		=> $amount,	
					'description'	=> $description,	
					'expiry'		=> $expiry
				);

				$this->event_alert($data['details']);
			}
			return true;
		}else{
			return false;
		}
	}

	public function remove_order_supercash($order_id){
		$this->load->language('extension/total/cashback_discount');
		$this->db->query("DELETE FROM " . DB_PREFIX . "customer_supercash WHERE order_id = '".(int)$order_id."'");
		$this->addSuperCashLog(sprintf($this->language->get('text_order_supercash_removed'), $order_id));
	}

	public function addSuperCashLog($log){
		$this->db->query("INSERT INTO `".DB_PREFIX."customer_supercash_log` (`log`) VALUES ('".$this->db->escape($log)."')");
	}

	//SPECIAL FUNCTION FOR CATALOG
	public function supercash_order_auto($order_id){
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);

		$order_status_id 	= $order_info['order_status_id'];

		$this->load->model('setting/setting');
		$extn_info = $this->model_setting_setting->getSetting('supercash', (int)$order_info['store_id']);

		$supercash_order_auto_cr = isset($extn_info['supercash_order_auto_cr'])?$extn_info['supercash_order_auto_cr']:$this->config->get('supercash_order_auto_cr');
		$supercash_order_auto_dr = isset($extn_info['supercash_order_auto_dr'])?$extn_info['supercash_order_auto_dr']:$this->config->get('supercash_order_auto_dr');

		if (in_array($order_status_id, $supercash_order_auto_cr)){
			$this->add_order_supercash($order_info);
		}else if (in_array($order_status_id, $supercash_order_auto_dr)){
			$this->remove_order_supercash($order_id);
		}else{
			return false;
		}
	}
	
}