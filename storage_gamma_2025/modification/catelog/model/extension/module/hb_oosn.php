<?php  
#this file is common to all 2xxx and 3xxx
class ModelExtensionModuleHbOosn extends Model {	

	public function product_notify_validation($product_id, $quantity, $option, $product_options){
		//this function decides whether to show the notify form or not
		$json = array();

		$product_not_in_stock 				= false;
		$customer_selected_all_options 		= array();
		$customer_selected_outstock_option 	= array();
		$user_selected_option_values 		= array();
		
		$hb_oosn_stock_status 	= $this->config->get('hb_oosn_stock_status');
		$hb_oosn_product_qty 	= $this->config->get('hb_oosn_product_qty');
		$language_id 			= $this->config->get('config_language_id');

		$hb_oosn_manual_rule 	= false;
		$hb_oosn_manual_notify 	= false;
		if ($this->config->get('hb_oosn_manual_rule')) {
			$hb_oosn_manual_rule = true;
			$hb_oosn_manual_notify = $this->is_alert_enabled($product_id);
		}

		if (!empty($option)) { //program first checks if the product has options
			foreach ($product_options as $product_option) {
				if (!empty($option[$product_option['product_option_id']])) {
					$product_option_id 			= $product_option['product_option_id']; //ID of the option
					//check qty of each option value of option
					$product_option_value 					= $option[$product_option_id]; //ID of the value of the option selected by the user ... this is an important unique property 
					$customer_selected_option_values 		= (array)$product_option_value;
					
					foreach ($customer_selected_option_values as $product_option_value_id){
						$result = $this->db->query("SELECT a.quantity, b.name, c.stock_status_id from ".DB_PREFIX."product_option_value a, ".DB_PREFIX."option_value_description b, ".DB_PREFIX."product c where a.option_value_id = b.option_value_id AND a.product_id = c.product_id AND a.product_id = '".(int)$product_id."' AND a.product_option_id = '".(int)$product_option_id."' AND a.product_option_value_id = '".(int)$product_option_value_id."' AND b.language_id = '" . (int)$language_id . "' LIMIT 1");
						if ($result->row){
							$option_qty 		= (int)$result->row['quantity'];
							$option_name		= $result->row['name'];
							$stock_status_id 	= $result->row['stock_status_id']; //this is overall product stock status id from product table
							
							if (empty($hb_oosn_stock_status)){ 
								$hb_oosn_stock_status = array(0);
								$stock_status_id = 0;
							}
							//VALIDATION PART
							$customer_selected_all_options[]  = $product_option['name'] . ' : '. $option_name;

							if (($option_qty < $hb_oosn_product_qty) && (in_array($stock_status_id, $hb_oosn_stock_status))){ 
								$product_not_in_stock = true;
								$customer_selected_outstock_option[] 	= $product_option['name'] . ' : '. $option_name;
							}
						}else{
							$customer_selected_all_options[]  = $product_option['name'] . ' : '. $product_option_value_id;
						}
						
						$array_selected_options 		= array('pi'=> $product_id, 'poi' => $product_option_id, 'povi' => $product_option_value_id);
						$user_selected_option_values[]  = json_encode($array_selected_options);
					}
				}
			}

			if ($hb_oosn_manual_rule) {
				$product_not_in_stock = $hb_oosn_manual_notify ? true : false;
			}
				
			if ($product_not_in_stock === true){
				$customer_selected_outstock_option_string   = implode(', ', $customer_selected_outstock_option);
				$customer_selected_all_options_string		= implode(', ', $customer_selected_all_options);		
				$user_selected_option_values_string 		= implode('|', $user_selected_option_values);
				
				$json['hberror']['selectedoption']  = html_entity_decode(str_replace('{selected_option}',$customer_selected_outstock_option_string,$this->config->get('hb_oosn_t_info_opt'.$language_id)));
				$json['hberror']['pid'] 			= $product_id;
				$json['hberror']['val'] 			= '<input type="hidden" id="option_values" value="'.htmlentities($user_selected_option_values_string, ENT_QUOTES).'"><input type="hidden" id="selected_option" value="'.htmlentities($customer_selected_outstock_option_string, ENT_QUOTES).'"><input type="hidden" id="all_selected_option" value="'.htmlentities($customer_selected_all_options_string, ENT_QUOTES).'">';
			}
		} //option check ends here
		if (!$json){

			if ($hb_oosn_manual_rule){
				if ($hb_oosn_manual_notify){
					$json['hberror']['oosn'] = $product_id;
				}
			}else{
				$result = $this->db->query("SELECT quantity, stock_status_id FROM `".DB_PREFIX."product` WHERE product_id = '".(int)$product_id."' LIMIT 1");
				$product_qty 		= $result->row['quantity'];
				$stock_status_id 	= $result->row['stock_status_id'];
				if (empty($hb_oosn_stock_status)){ 
					$hb_oosn_stock_status = array(0);
					$stock_status_id = 0;
				}

				if (($quantity > $product_qty) && (in_array($stock_status_id, $hb_oosn_stock_status))){
					$json['hberror']['oosn'] = $product_id;
				}

				if (($product_qty < $hb_oosn_product_qty) && (in_array($stock_status_id, $hb_oosn_stock_status))){
					$json['hberror']['oosn'] = $product_id;
				}
			}
		}

		if (!empty($json)){
			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			return $json;
		}else{
			return false;
		}
	}


			public function product_notify_validation_2($product_id, $quantity, $option, $product_options){
		//this function decides whether to show the notify form or not
		$json = array();

		$product_not_in_stock 				= false;
		$customer_selected_all_options 		= array();
		$customer_selected_outstock_option 	= array();
		$user_selected_option_values 		= array();
		
		$hb_oosn_stock_status 	= $this->config->get('hb_oosn_stock_status');
		$hb_oosn_product_qty 	= $this->config->get('hb_oosn_product_qty');
		$language_id 			= $this->config->get('config_language_id');

		$hb_oosn_manual_rule 	= false;
		$hb_oosn_manual_notify 	= false;
		if ($this->config->get('hb_oosn_manual_rule')) {
			$hb_oosn_manual_rule = true;
			$hb_oosn_manual_notify = $this->is_alert_enabled($product_id);
		}

		if (!empty($option)) { //program first checks if the product has options
			foreach ($product_options as $product_option) {
				if (!empty($option[$product_option['product_option_id']])) {
					$product_option_id 			= $product_option['product_option_id']; //ID of the option
					//check qty of each option value of option
					$product_option_value 					= $option[$product_option_id]; //ID of the value of the option selected by the user ... this is an important unique property 
					$customer_selected_option_values 		= (array)$product_option_value;
					
					foreach ($customer_selected_option_values as $product_option_value_id){
						$result = $this->db->query("SELECT a.quantity, b.name, c.stock_status_id from ".DB_PREFIX."product_option_value a, ".DB_PREFIX."option_value_description b, ".DB_PREFIX."product c where a.option_value_id = b.option_value_id AND a.product_id = c.product_id AND a.product_id = '".(int)$product_id."' AND a.product_option_id = '".(int)$product_option_id."' AND a.product_option_value_id = '".(int)$product_option_value_id."' AND b.language_id = '" . (int)$language_id . "' LIMIT 1");
						if ($result->row){
							$option_qty 		= (int)$result->row['quantity'];
							$option_name		= $result->row['name'];
							$stock_status_id 	= $result->row['stock_status_id']; //this is overall product stock status id from product table
							
							if (empty($hb_oosn_stock_status)){ 
								$hb_oosn_stock_status = array(0);
								$stock_status_id = 0;
							}
							//VALIDATION PART
							$customer_selected_all_options[]  = $product_option['name'] . ' : '. $option_name;

							if (($option_qty < $hb_oosn_product_qty) && (in_array($stock_status_id, $hb_oosn_stock_status))){ 
								$product_not_in_stock = true;
								$customer_selected_outstock_option[] 	= $product_option['name'] . ' : '. $option_name;
							}
						}else{
							$customer_selected_all_options[]  = $product_option['name'] . ' : '. $product_option_value_id;
						}
						
						$array_selected_options 		= array('pi'=> $product_id, 'poi' => $product_option_id, 'povi' => $product_option_value_id);
						$user_selected_option_values[]  = json_encode($array_selected_options);
					}
				}
			}

			if ($hb_oosn_manual_rule) {
				$product_not_in_stock = $hb_oosn_manual_notify ? true : false;
			}
				
			if ($product_not_in_stock === true){
				$customer_selected_outstock_option_string   = implode(', ', $customer_selected_outstock_option);
				$customer_selected_all_options_string		= implode(', ', $customer_selected_all_options);		
				$user_selected_option_values_string 		= implode('|', $user_selected_option_values);
				
				$json['hberror']['selectedoption']  = html_entity_decode(str_replace('{selected_option}',$customer_selected_outstock_option_string,$this->config->get('hb_oosn_t_info_opt'.$language_id)));
				$json['hberror']['pid'] 			= $product_id;
				$json['hberror']['val'] 			= '<input type="hidden" id="option_values" value="'.htmlentities($user_selected_option_values_string, ENT_QUOTES).'"><input type="hidden" id="selected_option" value="'.htmlentities($customer_selected_outstock_option_string, ENT_QUOTES).'"><input type="hidden" id="all_selected_option" value="'.htmlentities($customer_selected_all_options_string, ENT_QUOTES).'">';
			}
		} //option check ends here
		if (!$json){
			$json['hberror']['oosn'] = $product_id;
		}

		if (!empty($json)){
			$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			return $json;
		}else{
			return false;
		}
	}
	
	public function is_alert_enabled($product_id){
		$hb_oosn_manual_notify = false;
		$query = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "out_of_stock_product WHERE product_id = '".(int)$product_id."'");
		if ($query->row['total'] > 0) {
			$hb_oosn_manual_notify = true;
		}
		return $hb_oosn_manual_notify;
	}

	public function form_data(){
		$language_id = $this->config->get('config_language_id');

		if (version_compare(VERSION,'2.2.0.0','<' )) {
			$data['theme_directory'] = $this->config->get('config_template');
		}else if (version_compare(VERSION,'3.0.0.0','>' )) {
			$data['theme_directory'] = $this->config->get('config_theme');
		} else {
			$data['theme_directory'] = $this->config->get('theme_default_directory');
		}
		
		$data['hb_oosn_name_show']			= $this->config->get('hb_oosn_name_show') ? true : false;
		$data['hb_oosn_email_show']			= $this->config->get('hb_oosn_email_show') ? true : false;
		$data['hb_oosn_phone_show']			= $this->config->get('hb_oosn_phone_show') ? true : false;
		$data['hb_oosn_comment_show']		= $this->config->get('hb_oosn_comment_show') ? true : false;

		$data['hb_oosn_animation']  		= $this->config->get('hb_oosn_animation');
		$data['hb_oosn_css'] 				= $this->config->get('hb_oosn_css');
		$data['hb_oosn_incl_magnific'] 		= $this->config->get('hb_oosn_incl_magnific');
		
		if ($data['theme_directory'] == 'journal2') {
			$data['hb_oosn_incl_magnific'] = 0;
		}
		
		if (($data['theme_directory'] == 'journal3') || ($data['theme_directory'] == 'default')) {
			if (isset($this->request->get['route']) && $this->request->get['route'] == 'product/product') {
				$data['hb_oosn_incl_magnific'] = 0;
			}else{
				$data['hb_oosn_incl_magnific'] = 1;
			}
		}

		//language data
		$data['notify_button'] 				= html_entity_decode($this->config->get('hb_oosn_notifybtn_f'.$language_id));
		$data['notify_button_p'] 			= html_entity_decode($this->config->get('hb_oosn_notifybtn_p'.$language_id));
		$data['oosn_info_text'] 			= html_entity_decode($this->config->get('hb_oosn_t_info'.$language_id));
		$data['oosn_text_email'] 			= html_entity_decode($this->config->get('hb_oosn_t_email'.$language_id));
		$data['oosn_text_email_plh'] 		= html_entity_decode($this->config->get('hb_oosn_t_email_ph'.$language_id));
		$data['oosn_text_name'] 			= html_entity_decode($this->config->get('hb_oosn_t_name'.$language_id));
		$data['oosn_text_name_plh'] 		= html_entity_decode($this->config->get('hb_oosn_t_name_ph'.$language_id));
		$data['oosn_text_phone'] 			= html_entity_decode($this->config->get('hb_oosn_t_phone'.$language_id));
		$data['oosn_text_phone_plh'] 		= html_entity_decode($this->config->get('hb_oosn_t_phone_ph'.$language_id));
		$data['oosn_text_comment'] 			= html_entity_decode($this->config->get('hb_oosn_t_comment'.$language_id));
		$data['oosn_text_comment_plh'] 		= html_entity_decode($this->config->get('hb_oosn_t_comment_ph'.$language_id));

		if ($this->customer->isLogged()){
			$data['email'] 		= $this->customer->getEmail();
			$data['name'] 		= $this->customer->getFirstName().' '.$this->customer->getLastName();
			$data['phone'] 		= $this->customer->getTelephone();
		}else {
			$data['email'] = '';
			$data['name'] = '';
			$data['phone'] = '';
		}

		// Captcha
		if ($this->config->get('hb_oosn_captcha_show')){
			if (version_compare(VERSION,'3.0.0.0','>=' )) {
				$data['site_key'] = $this->config->get('captcha_google_key');
			}else{
				$data['site_key'] = $this->config->get('google_captcha_key');
			}
			$data['show_captcha'] = true;
		}else{
			$data['show_captcha'] = false;
		}

		$data['hb_oosn_form_type'] 				= $this->config->get('hb_oosn_form_type')? $this->config->get('hb_oosn_form_type') : 'popup' ;

		$data['oosn_text_add_to_cart']			= $this->language->get('button_cart');

		return $data;
	}

	public function cleanstrings($string){
		// second level of string cleaning to prevent sql attack
		$search = array('=','*','(',')',"'");
		$string = str_replace($search, '', $string);
		return $string;
	}

	public function authenticate_key($given_key){
		$original_key = $this->config->get('hb_oosn_authkey');
		if ($given_key == $original_key){
			return true;
		}else{		
			return false;
		}
	}

	public function addlog($text = ''){
		if ($this->config->get('hb_oosn_worklog')){
			if (!file_exists(DIR_LOGS . 'huntbee_psa_logs')) {
				mkdir(DIR_LOGS . 'huntbee_psa_logs', 0777, true);
			}

			$file = DIR_LOGS . 'huntbee_psa_logs/psa_logs.txt';

			if (file_exists($file)) {
				$size = filesize($file);
				if ($size > 5242880){
					$handle = fopen($file, 'w+');
					fclose($handle);
				}
			}

			$fp = fopen($file, 'a');
			fwrite($fp, "\r\n".date('d-M-Y G:i:s A') . ' - ' .$text);
			fclose($fp);
		}
	}

	public function is_alert_duplicate($data){
		$query = $this->db->query("SELECT count(*) as count FROM `" . DB_PREFIX . "out_of_stock_notify` WHERE product_id = '".(int)$data['product_id']."' AND selected_option_value = '".$this->db->escape($data['selected_option_value'])."' AND selected_option = '".$this->db->escape($data['selected_option'])."' AND all_selected_option = '".$this->db->escape($data['all_selected_option'])."' AND email = '".$this->db->escape($data['customer_email'])."' AND phone = '".$this->db->escape($data['customer_phone'])."' AND language_id = '".(int)$data['language_id']."' AND notified_date IS NULL");
		if ($query->row['count'] > 0){ 
			return true;
		}else{
			return false;
		}
	}

	public function add_customer_alert($data){
		$this->db->query("INSERT INTO `" . DB_PREFIX . "out_of_stock_notify` (product_id, selected_option_value, selected_option, all_selected_option, email, fname, phone, qty, language_id, store_id, store_url, comment, ip, enquiry_date) VALUES ('".(int)$data['product_id']."','".$this->db->escape($data['selected_option_value'])."','".$this->db->escape($data['selected_option'])."','".$this->db->escape($data['all_selected_option'])."','".$this->db->escape($data['customer_email'])."','".$this->db->escape($data['customer_name'])."','".$this->db->escape($data['customer_phone'])."', '".(int)$data['qty']."','".(int)$data['language_id']."','".(int)$data['store_id']."','".$this->db->escape($data['store_url'])."','".$this->db->escape($data['comment'])."','".$this->db->escape($data['ip'])."','".$this->db->escape($data['current_local_datetime'])."')");	
		
		$this->addlog('Customer Subscribed for the Stock Alert: Email : ['.$data['customer_email'].'] - Name : ['.$data['customer_name'].'] - Phone - ['.$data['customer_phone'].']');
		return $this->db->getLastId();
	}

	public function getPendingRecords() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "out_of_stock_notify` oosn INNER JOIN `" . DB_PREFIX . "product` p ON (oosn.product_id = p.product_id) WHERE p.status = 1 AND oosn.notified_date IS NULL");
		if ($query->rows){
			return $query->rows;	
		}else {
			return false;
		}
	}

	public function getStockStatus($product_id) {
		$query = $this->db->query("SELECT quantity, stock_status_id FROM `" . DB_PREFIX . "product` WHERE product_id = '".(int)$product_id."' LIMIT 1");
		if ($query->row){
			return $query->row;	
		}else {
			return false;
		}
	}

	public function getOptionStockStatus($product_id, $product_option_value_id, $product_option_id) {
		$query = $this->db->query("SELECT quantity FROM " . DB_PREFIX . "product_option_value WHERE product_id = '".(int)$product_id."' AND product_option_id = '".(int)$product_option_id."' AND product_option_value_id = '".(int)$product_option_value_id."' LIMIT 1");
		if ($query->row){
			return $query->row;	
		}else {
			return false;
		}
	}
	
	public function validateOptionExists($product_id, $product_option_id, $product_option_value_id) {
		//$query = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "product_option WHERE product_id = '".(int)$product_id."' AND product_option_id = '".(int)$product_option_id."'");
		$query = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "product_option_value WHERE product_id = '".(int)$product_id."' AND product_option_id = '".(int)$product_option_id."' AND product_option_value_id = '".(int)$product_option_value_id."'");
		if ($query->row['total'] > 0){
			return true;	
		}else {
			return false;
		}
	}

	public function deleteRecord($oosn_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "out_of_stock_notify` WHERE oosn_id = '".(int)$oosn_id."'");
	}

	public function updateNotifiedDate($oosn_id) {
		$this->db->query("UPDATE " . DB_PREFIX . "out_of_stock_notify SET notified_date = now() WHERE oosn_id = '".(int)$oosn_id."'");
	}

	public function getStore($store_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "store WHERE store_id = '" . (int)$store_id . "'");

		return $query->row;
	}

	public function alert($data){
		$output_text = '';
		$this->load->model('tool/image');

		$this->load->model('setting/setting');
		$extn_info = $this->model_setting_setting->getSetting('hb_oosn', (int)$data['store_id']);

		$sms_api 		= (isset($extn_info['hb_oosn_sms_http_api']))? $extn_info['hb_oosn_sms_http_api'] : '';

		$product_id 	= $data['product_id'];
		$preview 		= (isset($data['preview']))? true : false;

		$language_id = $data['language_id'];
		
		if ($data['alert_type'] == 'ack') {
			$template_id 		= (isset($extn_info['hb_oosn_ack_template'.$language_id]))? (int)$extn_info['hb_oosn_ack_template'.$language_id] : '0';
			$to 				= $data['customer_email'];
			$email_enabled		= ($this->config->get('hb_oosn_ack_status'))? true:false;
			$sms_template 		= (isset($extn_info['hb_oosn_ack_sms'.$language_id]))? $extn_info['hb_oosn_ack_sms'.$language_id] : '';
			$sms_enabled 		= ($this->config->get('hb_oosn_ack_sms_status'))? true:false;
		}elseif ($data['alert_type'] == 'admin') {
			$template_id 		= (isset($extn_info['hb_oosn_admin_template']))? (int)$extn_info['hb_oosn_admin_template'] : '0';
			$to 				= (isset($extn_info['hb_oosn_admin_to']))? $extn_info['hb_oosn_admin_to'] : $this->config->get('config_email');
			$email_enabled		= ($this->config->get('hb_oosn_admin_notify'))? true:false;
			$sms_template 		= '';
			$sms_enabled 		= false;
		}elseif ($data['alert_type'] == 'notify') {
			$template_id 		= (isset($extn_info['hb_oosn_backstock_template'.$language_id]))? (int)$extn_info['hb_oosn_backstock_template'.$language_id] : '0';
			$to 				= $data['customer_email'];
			$email_enabled		= (isset($extn_info['hb_oosn_backstock_status']))? true:false;
			$sms_template 		= (isset($extn_info['hb_oosn_backstock_sms'.$language_id]))? $extn_info['hb_oosn_backstock_sms'.$language_id] : '';
			$sms_enabled		= (isset($extn_info['hb_oosn_backstock_sms_status']))? true:false;
		}elseif ($data['alert_type'] == 'preview') {
			$template_id 		= $data['template_id'];
			$to 				= $this->config->get('config_email');
			$email_enabled		= true;
			$sms_template 		= '';
			$sms_enabled		= false;
		}else{
			$template_id 	= 0;
			$to 			= false;
			$email_enabled 	= false;
			$sms_template 	= '';
			$sms_enabled 	= false;
		}

		$this->addlog('');//ADD EMPTY LINE
		if ($preview){
			$this->addlog('Previewing Email Template for Alert for Alert ID : '.$data['oosn_id'].' - Product ID '.$product_id);
		} else {
			$this->addlog('Initiated Alert for Alert ID : '.$data['oosn_id'].' - Product ID '.$product_id);
		}		

		if ($this->isExtensionInstalled('email_templates')){
			$this->load->model('extension/module/email_builder');
			$use_email_designer_app = true;
		}else{
			$use_email_designer_app = false;
		}

		//GET DATA
		$store_config 			= $this->model_setting_setting->getSetting('config', $data['store_id']);

		$config_seo_url = isset($store_config['config_seo_url']) ? $store_config['config_seo_url'] : $this->config->get('config_seo_url');

		$store_info = $this->getStore($data['store_id']);
		if ($store_info) {
			$store_name = $store_info['name'];
			$store_url = $store_info['url'];
			$store_ssl = (!empty($store_info['ssl'])) ? $store_info['ssl'] : $store_info['url'];
		} else {
			$store_name = $this->config->get('config_name');
			$store_url = HTTP_SERVER; 
			$store_ssl = $this->config->get('config_secure') ? HTTP_SERVER : HTTPS_SERVER;
		}

		$url = new Url($store_url, $store_ssl);


		$campaign 				= (isset($extn_info['hb_oosn_campaign']))? html_entity_decode($extn_info['hb_oosn_campaign'], ENT_QUOTES, 'UTF-8') : '';
		$data['product_link']   = $url->link('product/product', 'product_id=' . $product_id. $campaign);

		$product_info = $this->getProduct($product_id);

		if ($product_info){
			if ($product_info['image']) {
				$data['product_image'] = $this->model_tool_image->resize($product_info['image'], 200, 200);
			} else {
				$data['product_image'] = $this->model_tool_image->resize('placeholder.png', 200, 200);
			}
			$data['product_image'] 	= str_replace(' ', '%20', $data['product_image']);
			$data['product_image'] 	= '<img src="'.$data['product_image'].'"  alt="'.$product_info['name'].'">';

			if ((float)$product_info['special']) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
			}else{
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->config->get('config_currency'));
			}
		}else{
			$this->addlog('No product data found for product ID '.$product_id);
			return false;
		}

		if ($template_id > 0 && $email_enabled){
			if ($use_email_designer_app){
				$template_data 	= $this->model_extension_module_email_builder->builtTemplate($template_id);
			}else{
				$template_data 	= $this->getTemplateData($template_id);
			}

			if (isset($template_data['email_options'])) {
				$email_subject = $template_data['email_options']['email_subject'];
				$email_content = $template_data['email_content'];

				//replacing shortcode variables
				foreach ($data as $key => $value) {
					if (!is_array($value)) {
						$email_content 		= str_replace('{'.$key.'}',$value,$email_content);
						$email_subject 		= str_replace('{'.$key.'}',$value,$email_subject);
					}
				}

				//replacing shortcode variables
				foreach ($product_info as $key => $value) {
					if (!is_array($value) && $value != NULL) {
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
					'type'            => (isset($template_data['email_options']['email_type_id']))? $template_data['email_options']['email_type_id']: '2',
					'cron'            => false
				);
			
				if ($preview) {
					$this->addlog('Email Preview');
					return $email_content;
				}else{
					$output['text'] = '';
					$output['status']	= true;
					if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
						if ($use_email_designer_app){		
							$this->model_extension_module_email_builder->sendemail($email_data);
							$output['text'] = strtoupper($data['alert_type']).": Email sent to ".$to." via template designer extn. ";
						}else{	 //else normal email
							$this->sendemail($email_data);
							$output['text'] = strtoupper($data['alert_type']).": Email sent to ".$to.". ";
						}
						$this->addlog($output['text']);
					}
				}
			}else{
				$this->addlog('Email Options is Empty! Email Templates not set Properly!');
			}

			if ($sms_enabled && !empty($data['customer_phone']) && !empty($sms_template)){
				//replacing shortcode variables
				foreach ($data as $key => $value) {
					if (!is_array($value)) {
						$sms_template 		= str_replace('{'.$key.'}',$value,$sms_template);
					}
				}

				//replacing shortcode variables
				foreach ($product_info as $key => $value) {
					if (!is_array($value) && $value != NULL) {
						$sms_template 		= str_replace('{'.$key.'}',$value,$sms_template);
					}
				}

				if ($this->isExtensionInstalled('opencart_sms')){
					$sms_config = $this->model_setting_setting->getSetting('hb_sms', $data['store_id']);
					$this->load->library('hbsms');
					$this->hbsms->call_api($data['customer_phone'], $sms_template, '1', $sms_config);
					$this->addlog('SMS API connected to HuntBee OpenCart SMS');
				}else{
					if (!empty($sms_api)){
						$this->sendSMS($sms_api, $data['customer_phone'], $sms_template);
					}else{
						$this->addlog('SMS API URL is empty!');
					}
				}

				$output['text'] .= "SMS sent to ".$data['customer_phone'];
			}
		}else{
			$output['text'] 	= 'Template is not Selected or Enabled!';
			$output['status']	= false;
			$this->addlog($output['text']);
		}
		$this->addlog('');//ADD EMPTY LINE

		return $output;

	}

	public function getRecord($oosn_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "out_of_stock_notify` WHERE oosn_id = '".(int)$oosn_id."' LIMIT 1");
		if ($query->row) {
			return $query->row;
		}else{
			return false;
		}
	}

	public function notify_customer($oosn_id){
		$data['alert_type'] = 'notify';
		$record = $this->getRecord($oosn_id);
		if ($record) {
			$data['oosn_id']			= (int)$record['oosn_id'];
			$data['language_id'] 		= (empty($record['language_id']) || $record['language_id'] == 0) ? 1 : (int)$record['language_id']; 
			$data['store_id']			= (int)$record['store_id'];
			$data['product_id']			= $record['product_id'];
			$data['customer_email'] 	= $record['email'];
			$data['customer_name'] 		= (empty($record['fname'])) ? '' : $record['fname'];		
			$data['customer_phone'] 	= $record['phone'];

			if (strlen($record['selected_option']) > 3){
				$data['selected_option'] 		= html_entity_decode($record['selected_option'], ENT_QUOTES, 'UTF-8');
				$data['all_selected_option'] 	= html_entity_decode($record['all_selected_option'], ENT_QUOTES, 'UTF-8');
			}else {
				$data['selected_option'] 		= '';
				$data['all_selected_option']	= '';
			}

			$output = $this->alert($data);
			if (isset($output['status']) && $output['status'] == true){
				//$output_text = 'Customer Notified: Email ['.$data['customer_email'] .'] - Phone ['.$data['customer_phone'] .'] - Name - ['.$data['customer_name'] .']';
				$this->updateNotifiedDate($oosn_id);
			}
			
			return $output['text'];
		}else{
			$this->addlog('No record found for oosn_id = '.$oosn_id);
			return false;
		}
	}

	public function sendSMS($sms_api, $telephone, $sms){
		//GET METHOD
		$sms_api = html_entity_decode($sms_api, ENT_QUOTES, 'UTF-8');
		$sms_api = str_replace('{to}', $telephone, $sms_api);
		$sms_api = str_replace('{msg}', $sms, $sms_api);

		$curl_enabled = function_exists('curl_version') ? true : false;
		if ($curl_enabled){
			$ch = curl_init();  
			curl_setopt($ch,CURLOPT_URL,$sms_api);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HEADER, false); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			$sms_output = curl_exec($ch);
			if (curl_error($ch)) {
				$sms_output = 'PSA SMS - Curl Error : ' . curl_error($ch);
				$this->addlog($sms_output);
				$this->log->write($sms_output);
			}else{
				$this->addlog('SMS Sent to '.$telephone);
			}
			curl_close ($ch);
		}else{
			$this->addlog('!!!! CURL function is disabled in your Server. SMS will not be Sent !!!!');
		}
	}

	public function getDemandedList(){
		$sql = "SELECT *,  sum(oosn.qty) as total_qty, count(oosn.enquiry_date) as total_alerts, count(oosn.notified_date) as total_notified, (SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = p.manufacturer_id) as manufacturer, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status FROM `" . DB_PREFIX . "out_of_stock_notify` oosn LEFT JOIN `" . DB_PREFIX . "product` p ON (oosn.product_id = p.product_id) LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (oosn.product_id = pd.product_id) LEFT JOIN ".DB_PREFIX."product_to_category p2c ON (oosn.product_id = p2c.product_id) WHERE oosn.store_id = '".(int)$this->config->get('config_store_id')."' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		$sql .= " AND (oosn.notified_date IS NULL)";		
		$sql .= " GROUP BY oosn.product_id, oosn.selected_option";
		$sql .= " ORDER BY total_qty DESC";
		
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function isExtensionInstalled($code){
		$query = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."extension` WHERE `code` = '".$this->db->escape($code)."'");	
		if ($query->row['total'] > 0){
			return true;
		}else{
			return false;
		}
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
			$store_config 			= $this->model_setting_setting->getSetting('config', $store_id);
			$config_currency 		= isset($store_config['config_currency']) ? $store_config['config_currency'] : $this->config->get('config_currency');
			$config_tax 			= isset($store_config['config_tax']) ? $store_config['config_tax'] : $this->config->get('config_tax');
			$config_review_status 	= isset($store_config['config_review_status']) ? $store_config['config_review_status'] : $this->config->get('config_review_status');
			$config_seo_url 		= isset($store_config['config_seo_url']) ? $store_config['config_seo_url'] : $this->config->get('config_seo_url');
			
			$store_name 	= isset($store_config['config_name']) ? $store_config['config_name'] : $this->config->get('config_name');
			$store_ssl 		= isset($store_config['config_url']) ? $store_config['config_url'] : HTTPS_SERVER;
			$store_email 	= isset($store_config['config_email']) ? $store_config['config_email'] : $this->config->get('config_email');

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
			$this->addlog('Email failed sending to '.$data['to'].'. Issue: '.$e->getMessage());
			$result	= false;
		}

		return $result;
	}

	public function getProduct($product_id) {
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'meta_title'       => $query->row['meta_title'],
				'meta_description' => $query->row['meta_description'],
				'meta_keyword'     => $query->row['meta_keyword'],
				'tag'              => $query->row['tag'],
				'model'            => $query->row['model'],
				'sku'              => $query->row['sku'],
				'upc'              => $query->row['upc'],
				'ean'              => $query->row['ean'],
				'jan'              => $query->row['jan'],
				'isbn'             => $query->row['isbn'],
				'mpn'              => $query->row['mpn'],
				'location'         => $query->row['location'],
				'quantity'         => $query->row['quantity'],
				'stock_status'     => $query->row['stock_status'],
				'image'            => $query->row['image'],
				'manufacturer_id'  => $query->row['manufacturer_id'],
				'manufacturer'     => $query->row['manufacturer'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'points'           => $query->row['points'],
				'length'           => $query->row['length'],
				'width'            => $query->row['width'],
				'height'           => $query->row['height'],
				'subtract'         => $query->row['subtract'],
				'minimum'          => $query->row['minimum'],
				'sort_order'       => $query->row['sort_order'],
				'status'           => $query->row['status'],
				'date_added'       => $query->row['date_added'],
				'date_modified'    => $query->row['date_modified'],
				'viewed'           => $query->row['viewed']
			);
		} else {
			return false;
		}
	}

}
?>