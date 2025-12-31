<?php  
if (!defined('TEMPLATE_FOLDER')) {
	if (version_compare(VERSION,'3.0.0.0','>=' )) {
		define('TEMPLATE_FOLDER', 'oc3');
	}else{
		define('TEMPLATE_FOLDER', 'oc2');
	}
}

#this file is common to all 2xxx and 3xxx
class ModelExtensionModuleOrderEmail extends Controller {	
	public function compose($data){
		$order_id 				= $data['order_id'];
		$order_status_id 		= $data['order_status_id'];
		$comment	 			= $data['admin_comment'];
		$order_history_id		= (isset($data['order_history_id']))? $data['order_history_id'] : false;
		$notify_customer		= $data['notify']; // true or false
		$override_template_id	= (isset($data['override_template_id']))? $data['override_template_id'] : 0; //used for preview
		$email_type				= $data['email_type'];  //checks the email type: order confirmation / order update / order alert / preview
		
		$this->addlog('');//ADD EMPTY LINE
		$this->addlog('ORDER ID: '.$order_id.'. ORDER STATUS ID: '.$order_status_id);

		$this->load->model('checkout/order');
		$this->load->model('setting/setting');
		
		$order_info = $this->model_checkout_order->getOrder($order_id);
		// --- Linked Orders (orders linked FROM this order) ---
// --- Linked Orders (orders linked FROM this order) ---
$query = $this->db->query("
    SELECT linked_order_id 
    FROM " . DB_PREFIX . "link_orders 
    WHERE order_id = '" . (int)$order_id . "'
");

$linked_orders = [];
foreach ($query->rows as $row) {
    $linked_orders[] = $row['linked_order_id'];
} 

$data['linked_order_id'] = (!empty($linked_orders)) 
    ? implode(', ', $linked_orders) 
    : '';


// --- Orders linking TO this order ---
$query2 = $this->db->query("
    SELECT order_id 
    FROM " . DB_PREFIX . "link_orders 
    WHERE linked_order_id = '" . (int)$order_id . "'
");

$linked_from_orders = [];
foreach ($query2->rows as $row) {
    $linked_from_orders[] = $row['order_id'];
}

$data['linked_from_order_id'] = (!empty($linked_from_orders)) 
    ? implode(', ', $linked_from_orders) 
    : '';


// Build HTML block
$data['linked_orders_block'] = '';

if (!empty($data['linked_order_id'])) {
    $data['linked_orders_block'] .= '<b>Linked Order ID:</b> #' . $data['linked_order_id'] . '<br />';
}

if (!empty($data['linked_from_order_id'])) {
    $data['linked_orders_block'] .= '<b>Linked From Order:</b> #' . $data['linked_from_order_id'] . '<br />';
}


		$store_id 				= $order_info['store_id'];
		$language_id 			= $order_info['language_id'];
		$customer_id 			= $order_info['customer_id'];
		$customer_group_id 		= $order_info['customer_group_id'];
		$telephone 				= $order_info['telephone'];
		//if you want to format telephone number, do it here itself
		
		$extn_info 		= $this->model_setting_setting->getSetting('hb_ose', $store_id);
		$store_info 	= $this->model_setting_setting->getSetting('config', $store_id);
		
		$worklog_enable = (isset($extn_info['hb_ose_worklog']))? true:false;
		$sms_enable 	= (isset($extn_info['hb_ose_enable_sms']))? true:false;
		$sms_api 		= (isset($extn_info['hb_ose_sms_api']))? $extn_info['hb_ose_sms_api'] : '';	
		
		$hb_cart_template = isset($extn_info['hb_ose_cart'])?$extn_info['hb_ose_cart']:array();
			
		switch ($email_type) {
			case 'order_confirmation':
				$template_id = (isset($extn_info['hb_ose_oc_email'.$language_id]))? $extn_info['hb_ose_oc_email'.$language_id] : '0';
				$template_status = (isset($extn_info['hb_ose_oc_status'.$language_id]))? true : false;
				$sms = (isset($extn_info['hb_ose_oc_sms'.$language_id]))? $extn_info['hb_ose_oc_sms'.$language_id] : '';
				break;
			
			case 'order_alert':
				$template_id = (isset($extn_info['hb_ose_admin_template_id']))? $extn_info['hb_ose_admin_template_id'] : '0';
				$template_status = (isset($extn_info['hb_ose_admin_alert_status']))? true : false;
				$sms = (isset($extn_info['hb_ose_admin_alert_sms']))? $extn_info['hb_ose_admin_alert_sms'] : '';
				break;

			case 'preview':
				$template_id = $override_template_id;
				$template_status = true;
				$sms = '';
				break;

			default:
				$template_id = (isset($extn_info['hb_ose_ou_email'.$order_status_id.$language_id]))? $extn_info['hb_ose_ou_email'.$order_status_id.$language_id] : '0';
				$template_status = (isset($extn_info['hb_ose_ou_status'.$order_status_id.$language_id]))? true : false;
				$sms = (isset($extn_info['hb_ose_ou_sms'.$order_status_id.$language_id]))? $extn_info['hb_ose_ou_sms'.$order_status_id.$language_id] : '';
				break;
		}

		//ADD ADMIN ORDER HISTORY IF 3.x.x.x -- lets add the order history the normal way because I have disabled this in the main function addOrderHistory
		if (version_compare(VERSION,'3.0.0.0','>=')) {
			if (($email_type == 'order_confirmation') || ($email_type == 'order_update')) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify_customer . "', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
				$order_history_id = $this->db->getLastId();
			}
		}
		
		if ($template_status && $template_id != 0) {
			$this->addlog('Template for Email type "'.$email_type.'" is enabled for language ID '.$language_id.' and store ID '.$store_id);
			//GET TEMPLATE DATA
			$template = array();
			$template_data = $this->getTemplate($template_id);

			$email_subject 		= (isset($template_data['email_subject']))? $template_data['email_subject'] : 'Your order has been updated to the following status: {order_status}';
			$email_body 		= (isset($template_data['email_body']))? $template_data['email_body'] : '';
			$template_comment 	= (isset($template_data['comment']))? $template_data['comment'] : '';
			$master 			= (isset($template_data['master']))? true : false;
			
			$sender_name 	= (isset($template_data['sender_name']))? $template_data['sender_name'] : $store_info['config_name'];
			$sender_email 	= (isset($template_data['sender_email']))? $template_data['sender_email'] : $store_info['config_email'];
			$email_replyto 	= (isset($template_data['email_replyto']))? $template_data['email_replyto'] : '';
			$email_bcc 		= (isset($template_data['email_bcc']))? $template_data['email_bcc'] : '';
			$email_attachments = (isset($template_data['email_attachments']))? $template_data['email_attachments'] : array();

			//LETS COMPOSE EMAIL - LOADING THE LANGUAGE FILE
			if (version_compare(VERSION,'2.0.2.0','<=')) {
				$language = new Language($order_info['language_directory']);
				$language->load('default');
			}else if ((version_compare(VERSION,'2.0.3.1','>=')) and (version_compare(VERSION,'2.2.0.0','<'))){
				$language = new Language($order_info['language_directory']);
				$language->load($order_info['language_directory']);
			}else{
				$language = new Language($order_info['language_code']);
				$language->load($order_info['language_code']);
			}
			
			$language->load('extension/module/order_email');
			if (version_compare(VERSION,'3.0.0.0','>=')) {
				$language->load('mail/order_add');
			}else{
				$language->load('mail/order');
			}

			//GET LANGUAGE TEXT FROM LANGUAGE FILE
			$lang_data = array(
				'product' 	=> $language->get('text_ose_product'),
				'image' 	=> $language->get('text_ose_image'),
				'model' 	=> $language->get('text_ose_model'),
				'sku' 		=> $language->get('text_ose_sku'),
				'upc' 		=> $language->get('text_ose_upc'),
				'ean'	 	=> $language->get('text_ose_ean'),
				'jan' 		=> $language->get('text_ose_jan'),
				'isbn' 		=> $language->get('text_ose_isbn'),
				'mpn' 		=> $language->get('text_ose_mpn'),
				'weight' 	=> $language->get('text_ose_weight'),
				'dimension'	=> $language->get('text_ose_dimension'),
				'quantity' 	=> $language->get('text_ose_quantity'),
				'price' 	=> $language->get('text_ose_price'),
				'total' 	=> $language->get('text_ose_total')
			);

			$data['text_image'] = $language->get('text_image');

			$order_status 		= $this->getOrderStatusName($order_status_id, $language_id);
			$ordered_products 	= $this->getOrderProducts($order_id);
			$download_status 	= $this->hasAnyDownloads($ordered_products);
			
			$this->addlog('ORDER STATUS: '.$order_status);

			//MANIPULATED DATA
			$data['link'] 			= $order_info['store_url'] . 'index.php?route=account/order/info&order_id=' . $order_id;
			$data['date_added'] 	= date($language->get('date_format_short'), strtotime($order_info['date_added']));
			$data['order_status'] 	= $order_status;
			$data['order_total'] 	= $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);
			$data['date_now']		= date('m-d-Y');

			if (!empty($order_info['payment_address_format'])) {
			$format = $order_info['payment_address_format'];
		} else {
			// if custom field #1 exists and is non-empty, prepend it to address_1 (your US tweak)
			$apartment_prefix = '';
			if (!empty($order_info['payment_custom_field']) && isset($order_info['payment_custom_field'][1]) && trim($order_info['payment_custom_field'][1]) !== '') {
				$apartment_prefix = $order_info['payment_custom_field'][1] . ' - ';
			}
			$format = '{firstname} {lastname}' . "\n" .
					  '{company}' . "\n" .
					  $apartment_prefix . '{address_1}' . "\n" .
					  '{address_2}' . "\n" .
					  '{city} {postcode}' . "\n" .
					  '{zone}' . "\n" .
					  '{country}';
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
				'{zone_code}',
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
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

		if (!empty($order_info['payment_custom_field']) && is_array($order_info['payment_custom_field'])) {
			foreach ($order_info['payment_custom_field'] as $cf_key => $cf_value) {
				$find[] = '{' . $cf_key . '}';
				$replace[] = $cf_value;
				$find[] = '{custom_field_' . $cf_key . '}';
				$replace[] = $cf_value;
			
				if (strpos($format, '{apartment_num}') !== false && (string)$cf_key === '1') {
					$find[] = '{apartment_num}';
					$replace[] = $cf_value;
				}
			}
		}
	
		$data['payment_address'] = str_replace(
			array("\r\n", "\r", "\n"),
			'<br />',
			preg_replace(
				array("/\s\s+/", "/\r\r+/", "/\n\n+/"),
				'<br />',
				trim(str_replace($find, $replace, $format))
			)
		);

		if (!empty($order_info['shipping_address_format'])) {
			$format = $order_info['shipping_address_format'];
		} else {
			$apartment_prefix = '';
			if (!empty($order_info['shipping_custom_field']) && isset($order_info['shipping_custom_field'][1]) && trim($order_info['shipping_custom_field'][1]) !== '') {
				$apartment_prefix = $order_info['shipping_custom_field'][1] . ' - ';
			}
			$format = '{firstname} {lastname}' . "\n" .
					  '{company}' . "\n" .
					  $apartment_prefix . '{address_1}' . "\n" .
					  '{address_2}' . "\n" .
					  '{city} {postcode}' . "\n" .
					  '{zone}' . "\n" .
					  '{country}';
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
				'{zone_code}',
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
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

		if (!empty($order_info['shipping_custom_field']) && is_array($order_info['shipping_custom_field'])) {
			foreach ($order_info['shipping_custom_field'] as $cf_key => $cf_value) {
				$find[] = '{' . $cf_key . '}';
				$replace[] = $cf_value;

				$find[] = '{custom_field_' . $cf_key . '}';
				$replace[] = $cf_value;

				if (strpos($format, '{apartment_num}') !== false && (string)$cf_key === '1') {
					$find[] = '{apartment_num}';
					$replace[] = $cf_value;
				}
			}
		}

		$data['shipping_address'] = str_replace(
			array("\r\n", "\r", "\n"),
			'<br />',
			preg_replace(
				array("/\s\s+/", "/\r\r+/", "/\n\n+/"),
				'<br />',
				trim(str_replace($find, $replace, $format))
			)
		);
		
		
			$product_data 	= $this->getProductsData($order_id, $ordered_products, $order_info, $hb_cart_template);
			$voucher_data	= $this->getVoucherData($order_id, $order_info);
			$total_data	= $this->getTotalData($order_id, $order_info);

			$item_template_data = array();
			$item_template_data = array(
				'products'          => $product_data,
				'vouchers'          => $voucher_data,
				'totals'            => $total_data,
				'extra'				=> $hb_cart_template,
				'columns'			=> array('model','sku','upc','ean','jan','isbn','mpn','weight','dimension','quantity','price'),
				'lang'				=> $lang_data
			);	
		
			if (version_compare(VERSION,'2.2.0.0','<' )) {
				$products_table = $this->load->view('default/template/extension/module/oc2/product_table_templates/'.$hb_cart_template['template'].'.tpl', $item_template_data);
			}else{
				$products_table = $this->load->view('extension/module/'.TEMPLATE_FOLDER.'/product_table_templates/'.$hb_cart_template['template'], $item_template_data);
			}

			$data['products_table'] = $products_table;

			//DOWNLAODS INFO BLOCK
			if ($download_status) {
				$block['downloads_info'] = (isset($extn_info['hb_ose_dwnldtxt'.$language_id]))? $extn_info['hb_ose_dwnldtxt'.$language_id] : '';
			}else{
				$block['downloads_info'] = '';
			}

			//REGISTERED CUSTOMER INFO BLOCK
			if ($order_info['customer_id'] > 0) {
				$block['customer_info'] = (isset($extn_info['hb_ose_customertxt'.$language_id]))? $extn_info['hb_ose_customertxt'.$language_id] : '';
			}else{
				$block['customer_info'] = '';
			}

			//FIRST PURCHASE INFO BLOCK
			if ($this->isFirstPurchase($order_info['email'])) {
				$block['first_purchase_info'] = (isset($extn_info['hb_ose_firstbuy'.$language_id]))? $extn_info['hb_ose_firstbuy'.$language_id] : '';
			}else{
				$block['first_purchase_info'] = '';
			}

			//CUSTOMER GROUP BLOCK
			$block['customer_group_info'] = (isset($extn_info['hb_ose_cg'.$order_info['customer_group_id'].$language_id]))? $extn_info['hb_ose_cg'.$order_info['customer_group_id'].$language_id] : '';

			//PAYMENT BLOCK
			if (trim($order_info['payment_code']) != ''){
				$block['payment_info'] = (isset($extn_info['hb_ose_pi'.$order_info['payment_code'].$language_id]))? $extn_info['hb_ose_pi'.$order_info['payment_code'].$language_id] : '';
			}else{
				$block['payment_info'] = '';
			}
			
			//SHIPPING BLOCK
			if (trim($order_info['shipping_code']) != ''){
				$shipping_code = strstr($order_info['shipping_code'],'.',true);
				$block['shipping_info'] = (isset($extn_info['hb_ose_si'.$shipping_code.$language_id]))? $extn_info['hb_ose_si'.$shipping_code.$language_id] : '';
			}else{
				$block['shipping_info'] = '';
			}

			//GET MASTER TEMPLATE
			$master_layout = $this->getMasterTemplate($store_id, $language_id);
			if ($master_layout) {
				if ($master) {
					$master_html = $master_layout['head'].$master_layout['body'];
					$email_body = str_replace('{email_content}', $email_body, $master_html);	
				}

				if ($extn_info['hb_ose_cross_count'] > 0) {
					$this->addlog('Building Cross-selling Products Block');
					$cross_selling_info_data['order_id'] 			= $order_id;
					$cross_selling_info_data['customer_id'] 		= $customer_id;
					$cross_selling_info_data['featured_products'] 	= (isset($extn_info['hb_ose_products']))? $extn_info['hb_ose_products']:array();
					$cross_selling_info_data['template_style_data'] = $master_layout['x_selling'];
					$cross_selling_info_data['limit'] 				= $extn_info['hb_ose_cross_count'];
					$cross_selling_info_data['customer_group_id'] 	= $customer_group_id;
					$cross_selling_info_data['store_id'] 			= $store_id;
					$cross_selling_info_data['language_id'] 		= $language_id;

					$email_body = $this->renderCrossSellingProducts($cross_selling_info_data, $email_body);
				}
			}
			
			//PLACING THE DATA INTO THE TEMPLATES
			$custom_fields = $this->renderCustomFields($order_info, $email_body, $sms, $template_comment); //CUSTOM FIELD
			$email_body = $custom_fields['email'];
			$sms = $custom_fields['sms'];
			$template_comment = $custom_fields['comment'];
						
			$payment_custom_fields = $this->renderPaymentCustomFields($order_info, $email_body, $sms, $template_comment); //PAYMENT CUSTOM FIELD
			$email_body = $payment_custom_fields['email'];
			$sms = $payment_custom_fields['sms'];
			$template_comment = $payment_custom_fields['comment'];
			
			$shipping_custom_fields = $this->renderShippingCustomFields($order_info, $email_body, $sms, $template_comment); //SHIPPING CUSTOM FIELD
			$email_body = $shipping_custom_fields['email'];
			$sms = $shipping_custom_fields['sms'];
			$template_comment = $shipping_custom_fields['comment'];

			//COMPOSE ADMIN COMMENT FROM TEMPLATE

			//ORDER SHIPMENT EXTENSION INTEGRATION
			if ($this->isExtensionInstalled('order_shipment')){
				$shipment = $this->getShipmentInfo($order_id);
				foreach ($shipment as $key => $value) {
					$email_body 		= str_replace('{'.$key.'}',$value,$email_body);
					$sms 				= str_replace('{'.$key.'}',$value,$sms);
					$template_comment 	= str_replace('{'.$key.'}',$value,$template_comment);
				}
			}

			//DIRECT ORDER VIEW LINK INTEGRATION
			if ($this->isExtensionInstalled('order_view_link')){
				$this->load->model('extension/module/order_view');
				$data['link'] = $this->model_extension_module_order_view->getDirectLink($order_id);
			}

			foreach ($block as $key => $value) {
				$email_body 		= str_replace('{'.$key.'}',$value,$email_body);
				$sms 				= str_replace('{'.$key.'}',$value,$sms);
				$template_comment 	= str_replace('{'.$key.'}',$value,$template_comment);
			}

			foreach ($data as $key => $value) {
				$email_body        = str_replace('{'.$key.'}', $value, $email_body);
				$sms               = str_replace('{'.$key.'}', $value, $sms);
				$template_comment  = str_replace('{'.$key.'}', $value, $template_comment);
				$email_subject     = str_replace('{'.$key.'}', $value, $email_subject);
			}
// FIX: Insert linked order block after all variable replacements
if (isset($data['linked_orders_block'])) {
    $email_body = str_replace('{linked_orders_block}', $data['linked_orders_block'], $email_body);
}
			foreach ($order_info as $key => $value) {
				if (!is_array($value)) {
					$email_body 		= str_replace('{'.$key.'}',$value,$email_body);
					$sms 				= str_replace('{'.$key.'}',$value,$sms);
					$template_comment 	= str_replace('{'.$key.'}',$value,$template_comment);
					$email_subject 		= str_replace('{'.$key.'}',$value,$email_subject);
				}
			}

			foreach ($store_info as $key => $value) {
				if (!is_array($value)) {
					$email_body 		= str_replace('{'.$key.'}',$value,$email_body);
					$sms 				= str_replace('{'.$key.'}',$value,$sms);
					$template_comment 	= str_replace('{'.$key.'}',$value,$template_comment);
					$email_subject 		= str_replace('{'.$key.'}',$value,$email_subject);
				}
			}

			//REMOVE UNUSED CUSTOM FIELDS SHORT-CODES
			$custom_fields = $this->db->query("SELECT * FROM `".DB_PREFIX."custom_field` a, `".DB_PREFIX."custom_field_description` b WHERE a.custom_field_id = b.custom_field_id AND language_id = (SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($this->config->get('config_admin_language')) . "')");
			if ($custom_fields->num_rows > 0){
				$custom_fields = $custom_fields->rows;
				foreach ($custom_fields as $custom_field){
					if ($custom_field['location'] == 'account') {
						$email_body = str_replace('{custom_field_'.$custom_field['custom_field_id'].'}','',$email_body);
						$sms = str_replace('{custom_field_'.$custom_field['custom_field_id'].'}','',$sms);
						$template_comment = str_replace('{custom_field_'.$custom_field['custom_field_id'].'}','',$template_comment);
					}
					if ($custom_field['location'] == 'address') {
						$email_body = str_replace('{payment_custom_field_'.$custom_field['custom_field_id'].'}','',$email_body);
						$email_body = str_replace('{shipping_custom_field_'.$custom_field['custom_field_id'].'}','',$email_body);
						$sms = str_replace('{payment_custom_field_'.$custom_field['custom_field_id'].'}','',$sms);
						$sms = str_replace('{shipping_custom_field_'.$custom_field['custom_field_id'].'}','',$sms);
						$template_comment = str_replace('{payment_custom_field_'.$custom_field['custom_field_id'].'}','',$template_comment);
						$template_comment = str_replace('{shipping_custom_field_'.$custom_field['custom_field_id'].'}','',$template_comment);
					}
				}
			}

			$template_comment = trim($template_comment);
			if (!empty($template_comment) && empty($comment)) {
				$comment = $template_comment;
			}

			$email_body = str_replace('{admin_comment}',$comment,$email_body);
			$email_body = str_replace('{email_subject}',$email_subject,$email_body);
			$email_body =  html_entity_decode($email_body, ENT_QUOTES, 'UTF-8');

			if ($email_type == 'preview'){
				$this->addlog('Displaying in Preview Mode.');
				return $email_body;
			}else{
				//SEND EMAIL , SMS
				if ($notify_customer) {

					if ($order_history_id && $order_history_id > 0 && $email_type != 'order_alert') {
						$this->db->query("UPDATE `" . DB_PREFIX . "order_history` SET `comment` = '" . $this->db->escape($comment) . "', notify = '1' WHERE `order_history_id` = '".(int)$order_history_id."'");
					}

					if (version_compare(VERSION,'2.0.1.1','<=' )) {
						$mail = new Mail($this->config->get('config_mail'));
					}else {
						if (version_compare(VERSION,'2.3.0.2','>' )) {
							$mail = new Mail($this->config->get('config_mail_engine'));
						}else{
							$mail = new Mail();
						}
						$mail->protocol = $this->config->get('config_mail_protocol');
						$mail->parameter = $this->config->get('config_mail_parameter');
						$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
						$mail->smtp_username = $this->config->get('config_mail_smtp_username');
						$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
						$mail->smtp_port = $this->config->get('config_mail_smtp_port');
						$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');			
					}

					if ($email_type == 'order_alert'){			
						$to = $extn_info['hb_ose_admin_email'];
						$sms_receiver = $extn_info['hb_ose_admin_phone'];
					}else{
						$to = $order_info['email'];
						$sms_receiver = $telephone;
					}
					$mail->setTo($to);
					$mail->setFrom($sender_email);
					$mail->setSender(html_entity_decode($sender_name, ENT_QUOTES, 'UTF-8'));
					if (!empty($email_replyto)){
						$mail->setReplyTo($email_replyto);
					}
					$mail->setSubject(html_entity_decode($email_subject, ENT_QUOTES, 'UTF-8'));

					if ($email_attachments){
						foreach ($email_attachments as $email_attachment){
							$mail->addAttachment($email_attachment);
							$this->addlog('Adding attachments to email.');
						}
					}

					//ORDER ATTACHMENTS
					$order_attachments = $this->db->query("SELECT * FROM `".DB_PREFIX."order_attachments` oa LEFT JOIN `".DB_PREFIX."upload` u ON (oa.upload_id = u.upload_id) WHERE oa.order_id =  '".(int)$order_id."'");
					if ($order_attachments->rows){
						foreach ($order_attachments->rows as $attachment) {
							$order_attachment_file = DIR_UPLOAD.$attachment['filename'];
							if (file_exists($order_attachment_file)) {
								$mail->addAttachment($order_attachment_file);
								$this->addlog('Order attachment added : '.$order_attachment_file);
							}
						}
					}

					//PDF INVOICE EXTENSION INTEGRATION WITH EMAIL
					if ($this->isExtensionInstalled('hb_pdfinvoice')){
						$this->addlog('HuntBee PDF Invoice Extension Detected');
						$this->load->model('extension/module/hb_pdfinvoice');
						$pdf_config = $this->model_setting_setting->getSetting('hb_pdf', $store_id);
						
						$hb_pdf_iv_trigger_status   = isset($pdf_config['hb_pdf_iv_trigger_status']) ? $pdf_config['hb_pdf_iv_trigger_status'] : array();
						$hb_pdf_iv_secured_folder   = isset($pdf_config['hb_pdf_iv_secured_folder']) ? $pdf_config['hb_pdf_iv_secured_folder'] : 'invoice-files-storage';
						$hb_pdf_iv_admin_attach     = isset($pdf_config['hb_pdf_iv_admin_attach']) ? true : false;
						$hb_pdf_keep_backup         = isset($pdf_config['hb_pdf_iv_keep_backup']) ? true : false;
						
						$hb_pdf_sp_trigger_status = isset($pdf_config['hb_pdf_sp_trigger_status']) ? $pdf_config['hb_pdf_sp_trigger_status'] : array();
						$hb_pdf_sp_secured_folder = isset($pdf_config['hb_pdf_sp_secured_folder']) ? $pdf_config['hb_pdf_sp_secured_folder'] : 'packaging-files-storage';
						
						$pdf_auth 		= md5($hb_pdf_iv_secured_folder);
						$packaging_auth = md5($hb_pdf_sp_secured_folder);

						//packaging pdf 
						if (in_array($order_status_id, $hb_pdf_sp_trigger_status)) {
							$packaging_slip = $this->model_extension_module_hb_pdfinvoice->createPdf('sp', $order_id, false, $packaging_auth, false);
							$this->addlog('Passing Command for Packaging Slip PDF');
						}
						//packaging pdf

						if ($email_type == 'order_alert' && $hb_pdf_iv_admin_attach == true){ //if the job is for admim alert and PDF invoice for admin is set, attach pdf no matter what
							$attach_pdf = true;
						}else if ($email_type == 'order_update' || $email_type == 'order_confirmation'){
							if (in_array($order_status_id, $hb_pdf_iv_trigger_status)) { //if order status is set, attach invoice
								$attach_pdf = true;
							}else{
								$attach_pdf = false;
							}
						}else{
							$attach_pdf = false;
						}

						if ($attach_pdf == true) {
							$pdf_invoice =  $this->model_extension_module_hb_pdfinvoice->createPdf('iv', $order_id, false, $pdf_auth, false);
							if (file_exists($pdf_invoice)) {
								$mail->addAttachment($pdf_invoice);
								$this->addlog('PDF Invoice attached to email.');
							}
						}
					}

					$mail->setHtml(wordwrap($email_body,50));
					$mail->send();
					$this->addlog('Email is sent to '.$to);
					if (!empty($email_bcc)){
						$bccs = explode(',',$email_bcc);
						foreach ($bccs as $bcc) {
							$mail->setTo($bcc);
							$mail->send();
							$this->addlog('Email is sent to BCC '.$bcc.' ****',$worklog_enable);
						}
					}

					//DELETE INVOICE AFTER EMAIL IS SENT
					if (isset($hb_pdf_keep_backup) && isset($pdf_invoice) && $hb_pdf_keep_backup == false && (file_exists($pdf_invoice))) {
						unlink($pdf_invoice);
					}

					//SEND SMS + OPENCART SMS INTEGRATION
					if ($sms_enable && !empty($sms)) {
						if ($this->isExtensionInstalled('opencart_sms')){
							$sms_config = $this->model_setting_setting->getSetting('hb_sms', $store_id);
							$this->load->library('hbsms');
							$this->hbsms->call_api($sms_receiver, $sms, '1', $sms_config);
							$this->addlog('SMS API connected to HuntBee OpenCart SMS');
						}else{
							$this->sendSMS($sms_api, $sms_receiver, $sms);
						}
					}else{
						$this->addlog('SMS is not enabled or SMS template is empty');
					}
					return true;
				}//end if notify customer
			}
		}else{
			$this->addlog('Template is either disabled or template is not set');
			return false;
		}
	}

	public function sendSMS($sms_api, $telephone, $sms){
		//GET METHOD
		$sms_api = html_entity_decode($sms_api, ENT_QUOTES, 'UTF-8');
		$sms_api = str_replace('{to}', $telephone, $sms_api);
		$sms_api = str_replace('{msg}', urlencode($sms), $sms_api);

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
				$sms_output = 'ORDER SMS - Curl Error : ' . curl_error($ch);
				$this->addlog($sms_output);
				$this->log->write($sms_output);
			}else{
				$this->addlog($sms_api);
				$this->addlog($sms_output);
				$this->addlog('SMS Sent to '.$telephone);
			}
			curl_close ($ch);
		}else{
			$this->addlog('!!!! CURL function is disabled in your Server. SMS will not be Sent !!!!');
		}
	}

	public function isExtensionInstalled($code){
		$query = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."extension` WHERE `code` = '".$this->db->escape($code)."'");	
		if ($query->row['total'] > 0){
			return true;
		}else{
			return false;
		}
	}

	public function getShipmentInfo($order_id){
		$shipment = array();
		$this->addlog('+ HuntBee Order Shipment Extension Installed.');
		$shipment_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "hb_shipment_order_info a , `" . DB_PREFIX . "hb_shipping_company` b where a.courier_id = b.id and a.order_id = '".(int)$order_id."' order by date_modified DESC LIMIT 1");
		if ($shipment_query->row){
			$shipment['tracking_id'] 		= $shipment_query->row['code'];
			$shipment['tracking_link']		= str_replace('{tracking_id}',$shipment_query->row['code'],$shipment_query->row['link']);
			$shipment['shipment_partner'] 	= $shipment_query->row['name'];
			$shipment['delivery_date'] 		= ($shipment_query->row['delivery_date'] == NULL || $shipment_query->row['delivery_date'] == '0000-00-00')? '' : date('d-m-Y', strtotime($shipment_query->row['delivery_date']));
			
			//now we need to get the list of products couriered
			$shipment['shipped_products'] = '';
			$shipped_products = $this->db->query("SELECT * FROM `" . DB_PREFIX . "hb_shipment_order_info` a, " . DB_PREFIX . "order_product b WHERE a.order_product_id = b.order_product_id AND a.code = '".$this->db->escape($shipment['tracking_id'])."'");
			
			if ($shipped_products->rows) {
				$shipment['shipped_products'] = '<ol>';
				foreach ($shipped_products->rows as $product) {
					$shipment['shipped_products'] .= '<li>'.$product['name'].' - '.$product['model'].'</li>';
					$this->db->query("UPDATE ".DB_PREFIX."hb_shipment_order_info SET mail = 1 WHERE order_id = '".(int)$order_id."' AND order_product_id = '".(int)$product['order_product_id']."'");
				}
				$shipment['shipped_products'] .= '</ol>';						
			}
		}else{
			$shipment['tracking_id'] 		= '';
			$shipment['tracking_link'] 		= '';
			$shipment['shipment_partner'] 	= '';
			$shipment['delivery_date']		= '';
			$shipment['shipped_products'] 	= '';
		}

		return $shipment;
	}

	public function addlog($text = ''){
		if ($this->config->get('hb_ose_worklog')){
			if (!file_exists(DIR_LOGS . 'huntbee_order_status_email_logs')) {
				mkdir(DIR_LOGS . 'huntbee_order_status_email_logs', 0777, true);
			}

			$file = DIR_LOGS . 'huntbee_order_status_email_logs/ose_logs.txt';

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
	
	public function buildcrossproducts($data = array()){
		$this->load->model('tool/image');
		$htmlbuild = '';	
		$category_id = 0;
		$template_style_data = $data['template_style_data'];
		if ($data['type'] == 'latest_products'){
			$results = $this->getLatestProducts($category_id, $data);
		}
		if ($data['type'] == 'bestseller_products'){
			$results = $this->getBestSellerProducts($category_id, $data);
		}
		if ($data['type'] == 'popular_products'){
			$results = $this->getPopularProducts($category_id, $data);
		}
		if ($data['type'] == 'random_products'){
			$results = $this->getRandomProducts($category_id, $data);
		}
		if ($data['type'] == 'brand_products'){
			$results = $this->getProductsbyBrand($data);
		}
		if ($data['type'] == 'category_products'){
			$results = $this->getProductsbyCategory($data);
		}
		if ($data['type'] == 'related_products'){
			$results = $this->getProductsbyRelated($data);
		}
		if ($data['type'] == 'autorelated_products'){
			$results = $this->getAutoRelatedProducts($data);
		}
		if ($data['type'] == 'also_bought'){
			$results = $this->getAlsoBoughtProducts($data);
		}
		if ($data['type'] == 'featured_products'){
			foreach ($data['featured_products'] as $product_id) {
				$results[$product_id] = $this->getProduct($product_id,$data);
			}
		}
		
		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $template_style_data['x_product_width'],  $template_style_data['x_product_height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $template_style_data['x_product_width'],  $template_style_data['x_product_height']);
				}
				
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')),$this->config->get('config_currency'));

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')),$this->config->get('config_currency'));
				} else {
					$special = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$products[] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => str_replace(' ','%20',$image),
					'name'        => $result['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $template_style_data['x_description_length']) . '..',
					'price'       => $price,
					'special'     => $special,
					'rating'      => $rating,
					'rating_image' => HTTP_SERVER.'image/catalog/hbemail/stars/stars-'.$rating.'.png',
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'].html_entity_decode($template_style_data['x_tracking_parameters'], ENT_QUOTES, 'UTF-8'))
				);
			}
			
			$html_build_template = '';
			$chunk_size = (int)substr($template_style_data['x_template'],0, 1);
			if (!empty($products)){
				$product_chunk = array_chunk($products, $chunk_size);
				foreach ($product_chunk as $chunk) {
					$template_style_data['products'] = $chunk;
					
					if (version_compare(VERSION,'3.0.0.0','>=' )) {
						$htmlbuild = $this->load->view('extension/module/oc3/email_product_list/'.$template_style_data['x_template'], $template_style_data);
					}else if (version_compare(VERSION,'2.2.0.0','<' )) {
						$htmlbuild = $this->load->view('default/template/extension/module/oc2/email_product_list/'.$template_style_data['x_template'].'.tpl', $template_style_data);
					}else{
						$htmlbuild = $this->load->view('extension/module/oc2/email_product_list/'.$template_style_data['x_template'], $template_style_data);
					}
					
					$html_build_template = $html_build_template.$htmlbuild;
				}
			}//close - not empty of products 
				
		}else{
			$html_build_template = 'No Products found!';
		}//end if results
		
		return $html_build_template;
	}
	
	public function getProduct($product_id, $data) {
		$query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$data['customer_group_id'] . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$data['customer_group_id'] . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$data['customer_group_id'] . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$data['language_id'] . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$data['language_id'] . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$data['language_id'] . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$data['language_id'] . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['store_id'] . "'");

		if ($query->num_rows) {
			return array(
				'product_id'       => $query->row['product_id'],
				'name'             => $query->row['name'],
				'description'      => $query->row['description'],
				'image'            => $query->row['image'],
				'price'            => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
				'special'          => $query->row['special'],
				'tax_class_id'     => $query->row['tax_class_id'],
				'rating'           => round($query->row['rating']),
				'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0
			);
		} else {
			return false;
		}
	}
	
	public function getLatestProducts($category_id, $data) {
			$product_data = array();
			if ($category_id <> 0 ) {
				$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['store_id'] . "' AND p.product_id in (SELECT ptc.product_id FROM " . DB_PREFIX . "product_to_category ptc where ptc.category_id in (SELECT c.category_id from " . DB_PREFIX . "category c where c.category_id = '".(int)$category_id."')) ORDER BY p.date_added DESC LIMIT " . (int)$data['limit']);
			}else{
				$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['store_id'] . "' ORDER BY p.date_added DESC LIMIT " . (int)$data['limit']);
			}
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id'],$data);
			}

		return $product_data;
	}
	
	public function getBestSellerProducts($category_id, $data) {
			$product_data = array();
			if ($category_id <> 0 ) {
				$query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['store_id'] . "' AND op.product_id in (SELECT ptc.product_id FROM " . DB_PREFIX . "product_to_category ptc where ptc.category_id in (SELECT c.category_id from " . DB_PREFIX . "category c where c.category_id = '" . (int)$category_id . "')) GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$data['limit']);
			}else{
				$query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['store_id'] . "' GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$data['limit']);
			}
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id'],$data);
			}

		return $product_data;
	}
	
	public function getPopularProducts($category_id, $data) {
		$product_data = array();
		if ($category_id <> 0 ) {
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['store_id']  . "' AND p.product_id in (SELECT ptc.product_id FROM " . DB_PREFIX . "product_to_category ptc where ptc.category_id in (SELECT c.category_id from " . DB_PREFIX . "category c where c.category_id = '".(int)$category_id."')) ORDER BY p.viewed DESC, p.date_added DESC LIMIT " . (int)$data['limit']);
		}else{
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['store_id']  . "' ORDER BY p.viewed DESC, p.date_added DESC LIMIT " . (int)$data['limit']);
		}
		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id'],$data);
		}

		return $product_data;
	}
	
	public function getRandomProducts($category_id, $data) {
		$product_data = array();
		$order_id = $data['order_id'];
		//GET PRODUCTS FROM ORDER ID
		$my_products = $this->getProductsbyOrder($order_id);
		$my_products_string = implode(',',$my_products);
		
		if (!empty($my_products)) {
			if ($category_id <> 0 ) {
				$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id NOT IN ($my_products_string) AND p.status = '1' AND p.date_available <= NOW() AND p.quantity > 0 AND p2s.store_id = '" . (int)$data['store_id']  . "' AND p.product_id in (SELECT ptc.product_id FROM " . DB_PREFIX . "product_to_category ptc where ptc.category_id in (SELECT c.category_id from " . DB_PREFIX . "category c where c.category_id = '".(int)$category_id."')) ORDER BY RAND() DESC LIMIT " . (int)$data['limit']);
			}else{
				$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id NOT IN ($my_products_string) AND p.status = '1' AND p.date_available <= NOW() AND p.quantity > 0 AND p2s.store_id = '" . (int)$data['store_id']  . "' ORDER BY RAND() LIMIT " . (int)$data['limit']);
			}
		}else{
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p.quantity > 0 AND p2s.store_id = '" . (int)$data['store_id']  . "' ORDER BY RAND() LIMIT " . (int)$data['limit']);
		}

		foreach ($query->rows as $result) {
			$product_data[$result['product_id']] = $this->getProduct($result['product_id'],$data);
		}

		return $product_data;
	}
	
	public function getProductsbyBrand($data) {
		$product_data = array();
		$order_id = $data['order_id'];
		//GET PRODUCTS FROM ORDER ID
		$my_products = $this->getProductsbyOrder($order_id);
		$my_products_string = implode(',',$my_products);
		if (!empty($my_products)) {
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.manufacturer_id IN(SELECT manufacturer_id FROM " . DB_PREFIX . "product WHERE product_id IN ($my_products_string)) AND p.product_id NOT IN ($my_products_string) AND p.status = '1' AND p.quantity > 0 AND p.date_available <= NOW() AND p2s.store_id = '" .  (int)$data['store_id'] . "' ORDER BY RAND() LIMIT ".(int)$data['limit']);
	
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id'],$data);
			}
		}

		return $product_data;
	}
	
	public function getProductsbyCategory($data) {
		$product_data = array();
		$order_id = $data['order_id'];
		//GET PRODUCTS FROM ORDER ID
		$my_products = $this->getProductsbyOrder($order_id);
		$my_products_string = implode(',',$my_products);
		if (!empty($my_products)) {
			$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p2c.category_id IN (SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id IN ($my_products_string)) AND p.product_id NOT IN ($my_products_string) AND p.status = '1' AND p.quantity > 0 AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['store_id'] . "' ORDER BY RAND() LIMIT ".(int)$data['limit']);                       
	
			foreach ($query->rows as $result) {
				$product_data[$result['product_id']] = $this->getProduct($result['product_id'],$data);
			}
		}

		return $product_data;
	}
	
	public function getProductsbyRelated($data) {
		$product_data = array();
		$order_id = $data['order_id'];
		//GET PRODUCTS FROM ORDER ID
		$my_products = $this->getProductsbyOrder($order_id);
		$my_products_string = implode(',',$my_products);
		if (!empty($my_products)) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id IN ($my_products_string) AND pr.related_id NOT IN ($my_products_string) AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$data['store_id'] . "'");
	
			foreach ($query->rows as $result) {
				$product_data[$result['related_id']] = $this->getProduct($result['related_id'],$data);
			}
		}

		return $product_data;
	}
	
	public function getAutoRelatedProducts($data){
		$auto_related = $this->getProductsbyRelated($data);
		if (count($auto_related) < $data['limit']){
			$category_products = $this->getProductsbyCategory($data);
			$auto_related = array_merge($auto_related,$category_products);
			if (count($auto_related) < $data['limit']) {
				$brand_products = $this->getProductsbyBrand($data);
				$auto_related = array_merge($auto_related,$brand_products);
				if (count($auto_related) < $data['limit']) {
					$random_products = $this->getRandomProducts(0, $data);
					$auto_related = array_merge($auto_related,$random_products);
				}
			}
		}
		$auto_related = array_splice($auto_related, 0, $data['limit']);
		return $auto_related;
	}
	
	public function getProductsbyOrder($order_id){
		$product_query = $this->db->query("SELECT distinct(op.product_id) FROM " . DB_PREFIX . "order_product op LEFT JOIN `".DB_PREFIX."product` p ON (op.product_id = p.product_id) WHERE order_id = '".(int)$order_id."'");
		$product_query = $product_query->rows;
		$my_products = array_column($product_query, 'product_id');
		return $my_products;
	}
	
	public function getAlsoBoughtProducts($data) {
		$product_data = array();
		$order_id = $data['order_id'];
		$customer_id = $data['customer_id'];
		//GET PRODUCTS FROM ORDER ID
		if ($customer_id > 0){
			$product_query = $this->db->query("SELECT distinct(a.product_id) FROM `" . DB_PREFIX . "order_product` a, `" . DB_PREFIX . "order` b WHERE a.order_id = b.order_id AND b.customer_id = '" . (int)$customer_id . "'");
			$product_query = $product_query->rows;
			$my_products = array_column($product_query, 'product_id');
		}else{
			$my_products = array();
		}
		
		//FIND OTHER CUSTOMERS WHO BOUGHT THE SAME PRODUCTS
		if ($customer_id > 0){
			$customers = $this->db->query("SELECT distinct(b.customer_id) FROM `" . DB_PREFIX . "order_product` a, `" . DB_PREFIX . "order` b WHERE a.order_id = b.order_id AND b.customer_id <> '".(int)$customer_id."' AND a.product_id IN (SELECT product_id FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "')");
		}else{
			$customers = $this->db->query("SELECT distinct(b.customer_id) FROM `" . DB_PREFIX . "order_product` a, `" . DB_PREFIX . "order` b WHERE a.order_id = b.order_id AND a.product_id IN (SELECT product_id FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "')");
		}
		//GET PRODUCTS OF THESE CUSTOMERS
		$customers = $customers->rows;
		$customers = array_column($customers, 'customer_id');
		$customers_string = implode(',',$customers);
		
		if (!empty($customers_string)){
			$all_products = $this->db->query("SELECT distinct(a.product_id) FROM `" . DB_PREFIX . "order_product` a, `" . DB_PREFIX . "order` b WHERE a.order_id = b.order_id AND b.customer_id IN ($customers_string) ORDER BY b.date_added DESC LIMIT 100");
			$all_products = $all_products->rows;
			$all_products = array_column($all_products, 'product_id');
	
			//EXCLUDE PRODUCTS THAT I ALREADY PURCHASED
			$final_product_list = array_diff($all_products,$my_products);
			
			$final_product_list = array_splice($final_product_list, 0, $data['limit']);
			
			foreach ($final_product_list as $product_id) {
				$product_data[$product_id] = $this->getProduct($product_id,$data);
			}
		}
		if (empty($product_data)){
			return $this->getRandomProducts(0, $data);
		}else{
			return $product_data;
		}
	}
	
	// NEW FUNCTIONS 5.0.0
	public function get_order_attachments($order_id) {
		$query = $this->db->query("SELECT a.upload_id, b.name, b.code, b.date_added FROM `".DB_PREFIX."order_attachments` a LEFT JOIN `".DB_PREFIX."upload` b ON (a.upload_id = b.upload_id) LEFT JOIN `".DB_PREFIX."order` c ON (a.order_id = c.order_id) WHERE a.order_id =  '".(int)$order_id."' AND c.customer_id <> 0 AND c.customer_id = '".(int)$this->customer->getId()."'");
		if ($query->rows){
			return $query->rows;
		}else{
			return false;
		}
	}

	public function getTemplate($id){
		$template_data = array();
		$result = $this->db->query("SELECT * FROM `".DB_PREFIX."hb_ose_templates` WHERE id = '".(int)$id."' LIMIT 1");
		if ($result->row) {
			$template_data = trim(preg_replace('/\s+/', ' ', $result->row['template_data']));
			$template_data = json_decode($template_data, true);
		}
		return $template_data;
	}

	public function getMasterTemplate($store_id, $language_id){
		$master = array();
		$result = $this->db->query("SELECT * FROM `".DB_PREFIX."hb_ose_master_layouts` WHERE `store_id` = '".(int)$store_id."' AND `language_id` = '".(int)$language_id."' LIMIT 1");
		if ($result->row) {
			$master['head'] 		= $result->row['draft_head'];
			$master['body'] 		= $result->row['draft_body'];
			$master['x_selling'] 	= json_decode($result->row['cross_selling_options'],true);
		}else{
			$this->addlog('Master layout not found for store ID '.$store_id.', language ID'.$language_id);
		}

		return $master;
	}

	public function getRandomOrderId(){
		$query = $this->db->query("SELECT o.order_id FROM  `".DB_PREFIX."order` o LEFT JOIN `".DB_PREFIX."order_product` op ON (o.order_id = op.order_id) LEFT JOIN `".DB_PREFIX."product` p ON (op.product_id = p.product_id) WHERE o.order_status_id <> 0 ORDER BY RAND() LIMIT 1"); //Random order ID
		if ($query->row) {
			return $query->row['order_id'];
		}else{
			return false;
		}
	}
	
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}
	
	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");

		return $query->rows;
	}
	
	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_voucher WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}
	
	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}

	public function getOrderStatusName($order_status_id, $language_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$language_id . "'");
	
		if ($query->row) {
			$order_status = $query->row['name'];
		} else {
			$order_status = '';
		}

		return $order_status;
	}

	public function hasAnyDownloads($ordered_products = array()){
		$download_status = false;
		if ($ordered_products) {
			foreach ($ordered_products as $order_product) {
				$product_download_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_download` WHERE product_id = '" . (int)$order_product['product_id'] . "'");

				if ($product_download_query->row['total']) {
					$download_status = true;
				}
			}
		}

		return $download_status;
	}

	public function getProductsData($order_id, $ordered_products, $order_info, $hb_cart_template){
		$this->load->model('tool/upload');
		$this->load->model('tool/image');
		$this->load->model('catalog/product');

		$img_w	= isset($hb_cart_template['img_w'])?$hb_cart_template['img_w']:'50';
		$img_h	= isset($hb_cart_template['img_h'])?$hb_cart_template['img_h']:'50';

		$product_data = array();
		foreach ($ordered_products as $product) {
			$option_data = array();

			$options = $this->getOrderOptions($order_id, $product['order_product_id']);

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
			
			$product_info = $this->model_catalog_product->getProduct($product['product_id']);

			if ($product_info) {
				$image = $this->model_tool_image->resize($product_info['image'],$img_w,$img_h);
				
				if ($product['stock_status_id'] == 13 || $product['stock_status_id'] == 15) {
                    $product['name'] .= " [Preorder]";
                }
				
				$product_data[] = array(
					'name'     => $product['name'],
					'model'    => $product['model'],
					'sku'    	=> $product_info['sku'],

				'stockstatus'   => $product['stockstatus_id'],
			

				'stockstatus'   => $product['stockstatus_id'],
			
					'upc'    	=> $product_info['upc'],
					'ean'    	=> $product_info['ean'],
					'jan'    	=> $product_info['jan'],
					'isbn'    	=> $product_info['isbn'],
					'mpn'    	=> $product_info['mpn'],
					'weight'   	=> $this->weight->format($product_info['weight'], $product_info['weight_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point')),
					'dimension' => $this->length->format($product_info['length'], $product_info['length_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point')).' x '.$this->length->format($product_info['width'], $product_info['length_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point')).' x '.$this->length->format($product_info['height'], $product_info['length_class_id'], $this->language->get('decimal_point'), $this->language->get('thousand_point')),
					'image'    => str_replace(' ','%20',$image),
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'href'     => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}
		}

		return $product_data;
	}

	public function getVoucherData($order_id, $order_info){
		$voucher_data = array();
	
		$vouchers = $this->getOrderVouchers($order_id);

		foreach ($vouchers as $voucher) {
			$voucher_data[] = array(
				'description' => $voucher['description'],
				'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
			);
		}
		return $voucher_data;
	}

	public function getTotalData($order_id, $order_info){
		$total_data = array();
	
		$totals = $this->getOrderTotals($order_id);

		foreach ($totals as $total) {
			$total_data[] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value'])
			);
		}

		return $total_data;
	}

	public function isFirstPurchase($email){
		$query = $this->db->query("SELECT count(*) as total FROM `".DB_PREFIX."order` WHERE order_status_id <> 0 AND email = '".$this->db->escape($email)."'");
		if ($query->row['total'] ==  1) {
			return true;
		}else{
			return false;
		}
	}

	public function renderCrossSellingProducts($cross_selling_info_data, $content){
		$modules = array('latest_products','bestseller_products','popular_products','random_products','brand_products','category_products','related_products','autorelated_products','also_bought','featured_products');
		
		foreach ($modules as $module) {
			$pos = strpos($content, '{'.$module.'}');
			if ($pos !== false) {
				$cross_selling_info_data['type'] = $module;
				$composed_product_list = $this->buildcrossproducts($cross_selling_info_data);
				$content = str_replace('{'.$module.'}',$composed_product_list,$content);
			}
		}
		
		return $content;
	}

	public function renderCustomFields($order_info, $email, $sms, $comment){
		if (!empty($order_info['custom_field'])){
			foreach ($order_info['custom_field'] as $key => $value){
				if (!is_array($value)){
					if (is_numeric($value)) {
						$q_value_text = $this->db->query("SELECT name FROM ".DB_PREFIX."custom_field_value_description WHERE custom_field_value_id = '".(int)$value."' AND language_id = '".(int)$order_info['language_id']."' LIMIT 1");
						if ($q_value_text->row) {
							$value = $q_value_text->row['name'];
						}
					}
					$email = str_replace('{custom_field_'.$key.'}',$value,$email);
					$sms = str_replace('{custom_field_'.$key.'}',$value,$sms);
					$comment = str_replace('{custom_field_'.$key.'}',$value,$comment);
				}else{
					foreach ($value as $fkey => $fvalue) {
						if (is_numeric($fvalue)) {
							$q_value_text = $this->db->query("SELECT name FROM ".DB_PREFIX."custom_field_value_description WHERE custom_field_value_id = '".(int)$fvalue."' AND language_id = '".(int)$order_info['language_id']."' LIMIT 1");
							if ($q_value_text->row) {
								$fvalue = $q_value_text->row['name'];
							}
						}
						$email = str_replace('{custom_field_'.$fkey.'}',$fvalue,$email);
						$sms = str_replace('{custom_field_'.$fkey.'}',$fvalue,$sms);
						$comment = str_replace('{custom_field_'.$fkey.'}',$fvalue,$comment);
					}
				}
			}
		}

		$content['email'] 	= $email;
		$content['sms'] 	= $sms;
		$content['comment'] = $comment;

		return $content;
	}

	public function renderPaymentCustomFields($order_info, $email, $sms, $comment){
		if (!empty($order_info['payment_custom_field'])){
			foreach ($order_info['payment_custom_field'] as $key => $value){
				if (!is_array($value)){
					if (is_numeric($value)) {
						$q_value_text = $this->db->query("SELECT name FROM ".DB_PREFIX."custom_field_value_description WHERE custom_field_value_id = '".(int)$value."' AND language_id = '".(int)$order_info['language_id']."' LIMIT 1");
						if ($q_value_text->row) {
							$value = $q_value_text->row['name'];
						}
					}
					$email = str_replace('{payment_custom_field_'.$key.'}',$value,$email);
					$sms = str_replace('{payment_custom_field_'.$key.'}',$value,$sms);
					$comment = str_replace('{payment_custom_field_'.$key.'}',$value,$comment);
				}else{
					foreach ($value as $fkey => $fvalue) {
						if (is_numeric($fvalue)) {
							$q_value_text = $this->db->query("SELECT name FROM ".DB_PREFIX."custom_field_value_description WHERE custom_field_value_id = '".(int)$fvalue."' AND language_id = '".(int)$order_info['language_id']."' LIMIT 1");
							if ($q_value_text->row) {
								$fvalue = $q_value_text->row['name'];
							}
						}
						$email = str_replace('{payment_custom_field_'.$fkey.'}',$fvalue,$email);
						$sms = str_replace('{payment_custom_field_'.$fkey.'}',$fvalue,$sms);
						$comment = str_replace('{payment_custom_field_'.$fkey.'}',$fvalue,$comment);
					}
				}
			}
		}

		$content['email'] 	= $email;
		$content['sms'] 	= $sms;
		$content['comment'] = $comment;

		return $content;
	}

	public function renderShippingCustomFields($order_info, $email, $sms, $comment){
		if (!empty($order_info['shipping_custom_field'])){
			foreach ($order_info['shipping_custom_field'] as $key => $value){
				if (!is_array($value)){
					if (is_numeric($value)) {
						$q_value_text = $this->db->query("SELECT name FROM ".DB_PREFIX."custom_field_value_description WHERE custom_field_value_id = '".(int)$value."' AND language_id = '".(int)$order_info['language_id']."' LIMIT 1");
						if ($q_value_text->row) {
							$value = $q_value_text->row['name'];
						}
					}
					$email = str_replace('{shipping_custom_field_'.$key.'}',$value,$email);
					$sms = str_replace('{shipping_custom_field_'.$key.'}',$value,$sms);
					$comment = str_replace('{shipping_custom_field_'.$key.'}',$value,$comment);
				}else{
					foreach ($value as $fkey => $fvalue) {
						if (is_numeric($fvalue)) {
							$q_value_text = $this->db->query("SELECT name FROM ".DB_PREFIX."custom_field_value_description WHERE custom_field_value_id = '".(int)$fvalue."' AND language_id = '".(int)$order_info['language_id']."' LIMIT 1");
							if ($q_value_text->row) {
								$fvalue = $q_value_text->row['name'];
							}
						}
						$email = str_replace('{shipping_custom_field_'.$fkey.'}',$fvalue,$email);
						$sms = str_replace('{shipping_custom_field_'.$fkey.'}',$fvalue,$sms);
						$comment = str_replace('{shipping_custom_field_'.$fkey.'}',$fvalue,$comment);
					}
				}
			}
		}

		$content['email'] 	= $email;
		$content['sms'] 	= $sms;
		$content['comment'] = $comment;

		return $content;
	}
	
	public function preview_product_table(){	
		$this->load->model('checkout/order');
		$this->load->model('setting/setting');
		
		$order_id = $this->getRandomOrderId();
		
		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		if ($order_info) {
		
			$store_id = $order_info['store_id'];
			$extension_settings 	= $this->model_setting_setting->getSetting('hb_ose', $store_id);
			
			$hb_cart_template = isset($extension_settings['hb_ose_cart'])?$extension_settings['hb_ose_cart']:array();
			
			if ($hb_cart_template) {
				$product_data = array();
				$download_status = false;
		
				$ordered_products 	= $this->getOrderProducts($order_id);
				$product_data 		= $this->getProductsData($order_id, $ordered_products, $order_info, $hb_cart_template);
				$voucher_data  		= $this->getVoucherData($order_id, $order_info);
				$total_data 		= $this->getTotalData($order_id, $order_info);
				
				if (version_compare(VERSION,'2.0.2.0','<=')) {
					$language = new Language($order_info['language_directory']);
					$language->load('default');
				}else if ((version_compare(VERSION,'2.0.3.1','>=')) and (version_compare(VERSION,'2.2.0.0','<'))){
					$language = new Language($order_info['language_directory']);
					$language->load($order_info['language_directory']);
				}else{
					$language = new Language($order_info['language_code']);
					$language->load($order_info['language_code']);
				}
				
				$language->load('extension/module/order_email');
						
				$lang_data = array(
					'product' 	=> $language->get('text_ose_product'),
					'image' 	=> $language->get('text_ose_image'),
					'model' 	=> $language->get('text_ose_model'),
					'sku' 		=> $language->get('text_ose_sku'),
					'upc' 		=> $language->get('text_ose_upc'),
					'ean'	 	=> $language->get('text_ose_ean'),
					'jan' 		=> $language->get('text_ose_jan'),
					'isbn' 		=> $language->get('text_ose_isbn'),
					'mpn' 		=> $language->get('text_ose_mpn'),
					'weight' 	=> $language->get('text_ose_weight'),
					'dimension'	=> $language->get('text_ose_dimension'),
					'quantity' 	=> $language->get('text_ose_quantity'),
					'price' 	=> $language->get('text_ose_price'),
					'total' 	=> $language->get('text_ose_total')
				);
							
				$item_template_data = array();
				$item_template_data = array(
					'products'          => $product_data,
					'vouchers'          => $voucher_data,
					'totals'            => $total_data,
					'extra'				=> $hb_cart_template,
					'columns'			=> array('model','sku','upc','ean','jan','isbn','mpn','weight','dimension','quantity','price'),
					'lang'				=> $lang_data
				);
								
				
				if (version_compare(VERSION,'2.2.0.0','<' )) {
					$products_table = $this->load->view('default/template/extension/module/'.TEMPLATE_FOLDER.'/product_table_templates/'.$hb_cart_template['template'].'.tpl', $item_template_data);
				}else{
					$products_table = $this->load->view('extension/module/'.TEMPLATE_FOLDER.'/product_table_templates/'.$hb_cart_template['template'], $item_template_data);
				}
			}else{
				$products_table = 'Save extension settings!';
			}
			
		}else{
			$products_table = 'No Orders found to generate preview!';
		}
		
		return $products_table;
	
	}

	public function create_sample_template ($layout, $content, $type, $language_id){		
		$language_code = $this->db->query("SELECT `code` FROM `".DB_PREFIX."language` WHERE language_id = '".(int)$language_id."' LIMIT 1");
		$language_code = $language_code->row['code'];
		
		$language = new Language($language_code);
		$language->load($language_code);
		
		if (version_compare(VERSION,'3.0.0.0','>=' )) {
			switch ($type) {
				case 'confirmation':
					$language->load('mail/order_add');

					$text['text_greeting'] = $language->get('text_greeting');
					$text['text_order_detail'] = $language->get('text_order_detail');
					$text['text_order_id'] = $language->get('text_order_id');
					$text['text_date_added'] = $language->get('text_date_added');
					$text['text_order_status'] = $language->get('text_order_status');
					$text['text_payment_method'] = $language->get('text_payment_method');
					$text['text_shipping_method'] = $language->get('text_shipping_method');
					$text['text_email'] = $language->get('text_email');
					$text['text_telephone'] = $language->get('text_telephone');
					$text['text_ip'] = $language->get('text_ip');
					$text['text_payment_address'] = $language->get('text_payment_address');
					$text['text_shipping_address'] = $language->get('text_shipping_address');
					$text['text_linked_order_id'] = $language->get('text_linked_order_id');
					$text['text_linked_from_order_id'] = $language->get('text_linked_from_order_id');
					$text['text_linked_orders_block'] = $language->get('text_linked_orders_block');
					$text['text_footer'] = $language->get('text_footer');
					break;
				
				case 'alert':
					$language->load('mail/order_alert');
			
					$text['text_received'] = $language->get('text_received');
					$text['text_order_id'] = $language->get('text_order_id');
					$text['text_date_added'] = $language->get('text_date_added');
					$text['text_order_status'] = $language->get('text_order_status');
					$text['text_linked_order_id'] = $language->get('text_linked_order_id');
					$text['text_linked_from_order_id'] = $language->get('text_linked_from_order_id');
					$text['text_linked_orders_block'] = $language->get('text_linked_orders_block');
					break;

				default:
					$language->load('mail/order_edit');

					$text['text_order_id'] = $language->get('text_order_id');
					$text['text_date_added'] = $language->get('text_date_added');
					$text['text_order_status'] = $language->get('text_order_status');
					$text['text_link'] = $language->get('text_link');
					$text['text_footer'] = $language->get('text_footer');
					$text['text_linked_order_id'] = $language->get('text_linked_order_id');
					$text['text_linked_from_order_id'] = $language->get('text_linked_from_order_id');
					$text['text_linked_orders_block'] = $language->get('text_linked_orders_block');
					break;
			}
				
		}else{
			switch ($type) {
				case 'confirmation':
					$language->load('mail/order');

					$text['text_greeting'] = $language->get('text_new_greeting');
					$text['text_order_detail'] = $language->get('text_new_order_detail');
					$text['text_order_id'] = $language->get('text_new_order_id');
					$text['text_date_added'] = $language->get('text_new_date_added');
					$text['text_order_status'] = $language->get('text_new_order_status');
					$text['text_payment_method'] = $language->get('text_new_payment_method');
					$text['text_shipping_method'] = $language->get('text_new_shipping_method');
					$text['text_email'] = $language->get('text_new_email');
					$text['text_telephone'] = $language->get('text_new_telephone');
					$text['text_ip'] = $language->get('text_new_ip');
					$text['text_payment_address'] = $language->get('text_new_payment_address');
					$text['text_shipping_address'] = $language->get('text_new_shipping_address');
					$text['text_linked_order_id'] = $language->get('text_linked_order_id');
					$text['text_linked_from_order_id'] = $language->get('text_linked_from_order_id');
					$text['text_linked_orders_block'] = $language->get('text_linked_orders_block');
					$text['text_footer'] = $language->get('text_new_footer');
					break;
				
				case 'alert':
					$language->load('mail/order');

					$text['text_received'] = $language->get('text_new_received');
					$text['text_order_id'] = $language->get('text_new_order_id');
					$text['text_date_added'] = $language->get('text_new_date_added');
					$text['text_order_status'] = $language->get('text_new_order_status');
					$text['text_linked_order_id'] = $language->get('text_linked_order_id');
					$text['text_linked_from_order_id'] = $language->get('text_linked_from_order_id');
					$text['text_linked_orders_block'] = $language->get('text_linked_orders_block');
					break;

				default:
					$language->load('mail/order');

					$text['text_order_id'] = $language->get('text_update_order');
					$text['text_date_added'] = $language->get('text_update_date_added');
					$text['text_order_status'] = $language->get('text_update_order_status');
					$text['text_link'] = $language->get('text_update_link');
					$text['text_footer'] = $language->get('text_update_footer');
					$text['text_linked_order_id'] = $language->get('text_linked_order_id');
					$text['text_linked_from_order_id'] = $language->get('text_linked_from_order_id');
					$text['text_linked_orders_block'] = $language->get('text_linked_orders_block');
					break;
			}
				
		}
		
		foreach ($text as $key => $value) {
			$content = str_replace('{'.$key.'}',$value,$content);
		}

		$content = str_replace('{content}', $content, $layout);
			
		return $content;
	}

}
?>