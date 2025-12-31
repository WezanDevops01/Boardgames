<?php
define('EXTENSION_VERSION','1.0.4');
class ControllerExtensionModuleHbProducts extends Controller {
		
	protected $registry;
	private $error = array(); 
	
	public function __construct($registry) {
		$this->registry = $registry;
		if (version_compare(VERSION,'3.0.0.0','>=' )) {
			$this->hb_template_folder 		= 'oc3';
			$this->hb_extension_base 		= 'marketplace/extension';
			$this->hb_token_name 			= 'user_token';
			$this->hb_template_extension 	= '';
			$this->hb_extension_route 		= 'extension/module';
		}else if (version_compare(VERSION,'2.2.0.0','<=' )) {
			$this->hb_template_folder 		= 'oc2';
			$this->hb_extension_base 		= 'extension/module';
			$this->hb_token_name 			= 'token';
			$this->hb_template_extension 	= '.tpl';
			$this->hb_extension_route 		= 'module';
		}else{
			$this->hb_template_folder 		= 'oc2';
			$this->hb_extension_base 		= 'extension/extension';
			$this->hb_token_name 			= 'token';
			$this->hb_template_extension 	= '';
			$this->hb_extension_route 		= 'extension/module';
		}

		if (!isset($_SESSION))  { 
			session_start(); 
		} 
		$_SESSION["hbfm_access_key"]  	= $this->session->data[$this->hb_token_name];
		$_SESSION["hbfm_store_url"]		= HTTPS_CATALOG;

		if ($this->config->get('hb_products_limit')){
			$this->list_limit = (int)$this->config->get('hb_products_limit');
		}else{
			$this->list_limit = 20;
		}

		if ($this->config->get('hb_products_image_w')){
			$this->image_width = (int)$this->config->get('hb_products_image_w');
		}else{
			$this->image_width = 40;
		}

		if ($this->config->get('hb_products_image_h')){
			$this->image_height = (int)$this->config->get('hb_products_image_h');
		}else{
			$this->image_height = 40;
		}

		$this->hb_products_qty_red 			= ($this->config->get('hb_products_qty_red')) ? (int)$this->config->get('hb_products_qty_red') : 1;
		$this->hb_products_qty_orange 		= ($this->config->get('hb_products_qty_orange')) ? (int)$this->config->get('hb_products_qty_orange') : 5;
		$this->hb_products_qty_red_color 	= ($this->config->get('hb_products_qty_red_color')) ? $this->config->get('hb_products_qty_red_color') : 'FF0000';
		$this->hb_products_qty_orange_color = ($this->config->get('hb_products_qty_orange_color')) ? $this->config->get('hb_products_qty_orange_color') : 'FFA011';

	}

	public function index() {
		$data['extension_version'] = EXTENSION_VERSION;
		
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addStyle('view/javascript/hb_products/hb_products.css');

		$this->load->model('setting/setting');

		$extn_info = $this->model_setting_setting->getSetting('hb_products', 0);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_hb_products', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link($this->hb_extension_base, $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&type=module', true));
			
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$text_strings = array(
				'heading_title',
				'text_enabled','text_disabled','text_delete','text_clear','text_close',
				'text_edit','text_list','entry_status','text_confirm','text_default','text_action','text_setting','text_status','text_quick_add','text_export','text_import','text_copy',
				'button_save','button_cancel','text_add_remove_store_button',
				'text_special','text_discount','text_additional_image','text_category','text_manufacturer','text_filter','text_related','text_attribute','text_option',
				'text_quick','text_add_to_store','text_remove_from_store','text_apply_selection','text_apply_to_all','text_add_to_clipboard','text_remove_from_clipboard','text_open_clipboard'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link($this->hb_extension_base, $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->hb_extension_route.'/hb_products', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true)
		);
		
		$data['action'] = $this->url->link($this->hb_extension_route.'/hb_products', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true);
		$data['cancel'] = $this->url->link($this->hb_extension_base, $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&type=module', true);
		
		$data['link_setting'] = $this->url->link($this->hb_extension_route.'/hb_products/setting', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true);
		$data['csv_import'] = $this->url->link($this->hb_extension_route.'/hb_products/csv_import', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true);
		
		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;	

		$data['admin_language_id'] = (int)$this->config->get('config_language_id');

		$columns = $this->model_extension_module_hb_products->table_columns();

		foreach ($columns as $column) {
			$data['hb_products_show'][$column] = isset($extn_info['hb_products_show_'.$column])?$extn_info['hb_products_show_'.$column]:false;
		}
		
		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$total_clipboard_products = $this->model_extension_module_hb_products->getTotalClipboardItems(array());
		$data['total_clipboard_products'] = sprintf($this->language->get('text_total_clipboard_products'), $total_clipboard_products);
		$data['text_apply_all_checkbox'] = sprintf($this->language->get('text_apply_to_all'), $total_clipboard_products);

		$check_updates = $this->model_extension_module_hb_products->check_updates();

		if (!empty($check_updates)){
			$data['update_info'] = 'New version '.$check_updates['version'].' is available. Please install the new version by downloading it from <a href="'.$check_updates['access_link'].'" target="_blank">here</a>. For more details refer to <a href="'.$check_updates['changelog'].'" target="_blank">changelog</a>.';
		}else{
			$data['update_info'] = false;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products'.$this->hb_template_extension, $data));
	}
	
	public function setting() {
		$data['extension_version'] = EXTENSION_VERSION;
		$this->load->language('catalog/product');
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		$this->document->setTitle($this->language->get('heading_title_setting'));
		$this->document->addStyle('view/javascript/hb_products/hb_products.css');
		$this->load->model('setting/setting');

		$extn_info = $this->model_setting_setting->getSetting('hb_products', 0);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('hb_products', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link($this->hb_extension_route.'/hb_products/setting', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true));			
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$text_strings = array(
				'heading_title',
				'heading_title_setting',
				'text_name',
				'text_enabled',	'text_disabled',
				'entry_tax_class','text_none','entry_minimum','help_minimum','entry_subtract','entry_stock_status','help_stock_status','entry_shipping','text_yes','text_no','entry_store','text_default',
				'tab_table_setting', 'tab_quick_form_setting',
				'button_save',
				'button_cancel'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link($this->hb_extension_base, $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->hb_extension_route.'/hb_products', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_setting'),
			'href' => $this->url->link($this->hb_extension_route.'/hb_products/setting', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true)
		);
		
		$data['action'] = $this->url->link($this->hb_extension_route.'/hb_products/setting', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true);
		$data['cancel'] = $this->url->link($this->hb_extension_route.'/hb_products', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name], true);
		
		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;	
		
		$columns = $this->model_extension_module_hb_products->table_columns();

		foreach ($columns as $column) {
			$data['hb_products_show'][$column] = isset($extn_info['hb_products_show_'.$column])?$extn_info['hb_products_show_'.$column]:'';
			$data['product_columns'][] = array(
				'column_id'		=> $column,
				'column_name'	=> $this->language->get('text_'.$column),
			);
		}

		$sort_columns = $data['product_columns'];
		unset($sort_columns[1]); //removing category column
		$data['sort_columns'] = $sort_columns;

		//unset($data['product_columns'][29]); //removing date added column
		//unset($data['product_columns'][30]); //removing date modified column

		$data['hb_products_limit'] 	= isset($extn_info['hb_products_limit'])?$extn_info['hb_products_limit']:'50';

		$data['hb_products_sort_parameter'] 	= isset($extn_info['hb_products_sort_parameter'])?$extn_info['hb_products_sort_parameter']:'product_description.name';
		$data['hb_products_sort_order'] 	= isset($extn_info['hb_products_sort_order'])?$extn_info['hb_products_sort_order']:'ASC';

		$data['hb_products_image_w'] = isset($extn_info['hb_products_image_w'])?$extn_info['hb_products_image_w']:'40';
		$data['hb_products_image_h'] = isset($extn_info['hb_products_image_h'])?$extn_info['hb_products_image_h']:'40';

		$data['hb_products_qty_red'] = isset($extn_info['hb_products_qty_red'])?$extn_info['hb_products_qty_red']:'1';
		$data['hb_products_qty_orange'] = isset($extn_info['hb_products_qty_orange'])?$extn_info['hb_products_qty_orange']:'5';

		$data['hb_products_qty_red_color'] = isset($extn_info['hb_products_qty_red_color'])?$extn_info['hb_products_qty_red_color']:'FF0000';
		$data['hb_products_qty_orange_color'] = isset($extn_info['hb_products_qty_orange_color'])?$extn_info['hb_products_qty_orange_color']:'FFA011';

		//QUICK FORM SETTING
		$quick_form_fields = $this->model_extension_module_hb_products->quick_form_columns();

		foreach ($quick_form_fields as $column) {
			$data['hb_products_qf_show'][$column] = isset($extn_info['hb_products_qf_show_'.$column])?$extn_info['hb_products_qf_show_'.$column]:'';
			$data['quick_form_fields'][] = array(
				'column_id'		=> $column,
				'column_name'	=> $this->language->get('text_'.$column),
			);
		}

		$data['tables'] = $this->model_extension_module_hb_products->getTables();

		//QUICK FORM DEFAULTS
		$this->load->model('setting/store');
		
		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
		
		$data['hb_products_qf_product_store'] = isset($extn_info['hb_products_qf_product_store'])?$extn_info['hb_products_qf_product_store']:array(0);
		$data['hb_products_qf_tax_class_id'] = isset($extn_info['hb_products_qf_tax_class_id'])?$extn_info['hb_products_qf_tax_class_id']:'0';
		$data['hb_products_qf_minimum'] = isset($extn_info['hb_products_qf_minimum'])?$extn_info['hb_products_qf_minimum']:'1';
		$data['hb_products_qf_subtract'] = isset($extn_info['hb_products_qf_subtract'])?$extn_info['hb_products_qf_subtract']:'1';
		$data['hb_products_qf_shipping'] = isset($extn_info['hb_products_qf_shipping'])?$extn_info['hb_products_qf_shipping']:'1';
		$data['hb_products_qf_stock_status_id'] = isset($extn_info['hb_products_qf_stock_status_id'])?$extn_info['hb_products_qf_stock_status_id']:'0';
		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_setting'.$this->hb_template_extension, $data));
	}

	public function product_list() {  		
		$this->load->language($this->hb_extension_route.'/hb_products');

		$this->load->model('extension/module/hb_products');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		
		if ($this->config->get('hb_products_sort_parameter')){
			$sort_parameter = $this->config->get('hb_products_sort_parameter');
		}else{
			$sort_parameter = 'product_description.name';
		}

		if ($this->config->get('hb_products_sort_order')){
			$sort_order = $this->config->get('hb_products_sort_order');
		}else{
			$sort_order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		if (isset($this->request->get['query'])) {
			$query = $this->request->get['query'];
		} else {
			$query = '';
		}

		if (isset($this->request->get['add_table_query'])) {
			$add_table_query = $this->request->get['add_table_query'];
		} else {
			$add_table_query = '';
		}

		if (isset($this->request->get['add_table_filter'])) {
			$add_table_filter = $this->request->get['add_table_filter'];
		} else {
			$add_table_filter = '';
		}
		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		if (isset($this->request->get['search'])) {
			$url .= '&search=' . $this->request->get['search'];
		}

		if (isset($this->request->get['query'])) {
			$url .= '&query=' . $this->request->get['query'];
		}
		
		$data = array(
			'start' 	=> ($page - 1) * $this->list_limit,
			'limit' 	=> $this->list_limit,
			'search'	=> $search,
			'query'		=> html_entity_decode($query, ENT_QUOTES, 'UTF-8'),
			'add_table_query' => html_entity_decode($add_table_query, ENT_QUOTES, 'UTF-8'), 
			'add_table_filter' => html_entity_decode($add_table_filter, ENT_QUOTES, 'UTF-8'), 	
			'sort_parameter'	=> $sort_parameter,
			'sort_order'	=> $sort_order		
		);	
		
		$product_total 		= $this->model_extension_module_hb_products->getTotalRecords($data); 		
		$results 			= $this->model_extension_module_hb_products->getRecords($data);

		$data['products'] = array();
		
		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], $this->image_width, $this->image_height);
				$image_path = $result['image'];
			} else {
				$image = $this->model_tool_image->resize('no_image.png', $this->image_width, $this->image_height);
				$image_path = '';
			}

			$special = false;

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $product_special['price'];

					break;
				}
			}
			
			//$this->load->model('catalog/category');
			$categories = $this->model_catalog_product->getProductCategories($result['product_id']);

			$product_categories = array();

			if ($this->config->get('hb_products_show_category')) {
				foreach ($categories as $category_id) {
					$category_info = $this->model_extension_module_hb_products->getCategory($category_id);

					if ($category_info) {
						$product_categories[] = array(
							'category_id' => $category_info['category_id'],
							'name'        => ($category_info['path']) ? $category_info['path'] . ' / ' . $category_info['name'] : $category_info['name']
						);
					}
				}
			}

			$data['products'][] = array(
				'product_id' 	=> $result['product_id'],
				'image'      	=> $image,
				'image_path'	=> $image_path,
				'name'       	=> $result['name'],
				'product_categories' => $product_categories,
				'model'      	=> $result['model'],
				'sku'      		=> $result['sku'],
				'upc'      		=> $result['upc'],
				'ean'      		=> $result['ean'],
				'jan'      		=> $result['jan'],
				'isbn'      	=> $result['isbn'],
				'mpn'      		=> $result['mpn'],
				'location'      => $result['location'],
				'points'      	=> $result['points'],
				'date_available'=> $result['date_available'],
				'weight'      	=> $result['weight'],
				'length'      	=> $result['length'],
				'width'      	=> $result['width'],
				'height'      	=> $result['height'],
				'minimum'      	=> $result['minimum'],
				'sort_order'    => $result['sort_order'],
				'viewed'      	=> $result['viewed'],
				'date_added'    => $result['date_added'],
				'date_modified' => $result['date_modified'],
				'description'   => $result['description'],
				'meta_title'    => $result['meta_title'],
				'meta_description'  => $result['meta_description'],
				'meta_keyword'  => $result['meta_keyword'],
				'price'      	=> $result['price'],
				'special'    	=> $special,
				'quantity'   	=> $result['quantity'],
				'manufacturer'	=> $result['manufacturer'],
				'stock_status_id'=>$result['stock_status_id'],
				'tax_class_id'	=>$result['tax_class_id'],
				'length_class_id'=>$result['length_class_id'],
				'weight_class_id'=>$result['weight_class_id'],
				'status'     	=> $result['status'],//$result['status'] ? '<span style="color:green;"><i class="fa fa-check"></i></span>' : '<span style="color:red;"><i class="fa fa-ban"></i></span>',
				'date_release'  => $result['date_release'],
				'release_status_id' => $result['release_status_id'],
				'edit'       	=> $this->url->link('catalog/product/edit', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&product_id=' . $result['product_id'], true),
				'view'			=> HTTPS_CATALOG.'index.php?route=product/product&product_id='. $result['product_id']
			);
			
			/*echo '<pre>';
			print_r($data['products']);
			echo '</pre>';*/
		}
		
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->list_limit;
		$pagination->url = $this->url->link($this->hb_extension_route.'/hb_products/product_list', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&search='.$search.'&query='.$query.'&add_table_query='.$add_table_query.'&add_table_filter='.$add_table_filter.'&page={page}', true);

		$data['pagination'] = $pagination->render();
		$limit = $this->list_limit;

		$data['results'] = sprintf($this->language->get('text_pagination'), ($pagination->total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($pagination->total - $limit)) ? $pagination->total : ((($page - 1) * $limit) + $limit), $pagination->total, ceil($pagination->total / $limit));

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;	

		$data['column_set_1'] = array(
			array($this->language->get('text_model'),'model','left'),
			array($this->language->get('text_quantity'),'quantity','right'),
			array($this->language->get('text_sku'),'sku','left'),
			array($this->language->get('text_upc'),'upc','left'),
			array($this->language->get('text_jan'),'jan','left'),
			array($this->language->get('text_ean'),'ean','left'),
			array($this->language->get('text_isbn'),'isbn','left'),
			array($this->language->get('text_mpn'),'mpn','left'),
			array($this->language->get('text_location'),'location','left'),
			array($this->language->get('text_points'),'points','center'),
			array($this->language->get('text_minimum'),'minimum','center'),
			array($this->language->get('text_viewed'),'viewed','right'),
			array($this->language->get('text_sort_order'),'sort_order','center'),
			array($this->language->get('text_price'),'price','right'),			
			array($this->language->get('text_length'),'length','center'),
			array($this->language->get('text_width'),'width','center'),
			array($this->language->get('text_height'),'height','center'),
			array($this->language->get('text_weight'),'weight','center'),
		);

		$data['column_set_2'] = array(
			array($this->language->get('text_date_added'),'date_added','right'),
			array($this->language->get('text_date_modified'),'date_modified','right'),
			array($this->language->get('text_date_release'),'date_release','right')
		);

		$text_strings = array(
			'text_product_id','text_image','text_name','text_category','text_special','text_discount','text_manufacturer','text_shipping','text_stock_status','text_tax_class','text_length_class','text_weight_class','text_status','text_additional_image','text_action'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}
		
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', $this->image_width, $this->image_height);

		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
		
		$this->load->model('localisation/release_status');
		$data['release_statuses'] = $this->model_localisation_release_status->getReleaseStatuses();

		$this->load->model('localisation/weight_class');
		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		$this->load->model('localisation/length_class');
		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		$this->load->model('localisation/tax_class');
		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		$data['hb_products_qty_red'] = $this->hb_products_qty_red;
		$data['hb_products_qty_orange'] = $this->hb_products_qty_orange;

		$data['hb_products_qty_red_color'] = $this->hb_products_qty_red_color;
		$data['hb_products_qty_orange_color'] = $this->hb_products_qty_orange_color;

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_list'.$this->hb_template_extension, $data));
	}

	public function clipboard() {  	
		$this->load->language($this->hb_extension_route.'/hb_products');

		$this->load->model('extension/module/hb_products');
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		if (isset($this->request->get['search'])) {
			$search = $this->request->get['search'];
		} else {
			$search = '';
		}

		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		if (isset($this->request->get['search'])) {
			$url .= '&search=' . $this->request->get['search'];
		}
		
		$data = array(
			'start' 	=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' 	=> $this->config->get('config_limit_admin'),
			'search'	=> $search
		);

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] 		= $this->hb_extension_route;	
		
		$reports_total 		= $this->model_extension_module_hb_products->getTotalClipboardItems($data); 		
		$records 			= $this->model_extension_module_hb_products->getClipboardItems($data);
		$data['records'] 	= array();
		
		foreach ($records as $record) {
			$data['records'][] = array(
				'product_id' 	=> $record['product_id'],
				'name'			=> $record['name']
			);
		}
		
		$pagination = new Pagination();
		$pagination->total = $reports_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link($this->hb_extension_route.'/hb_products/clipboard', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . '&search='.$search.'&page={page}', true);

		$data['pagination'] = $pagination->render();
		$limit = $this->config->get('config_limit_admin');

		$data['results'] = sprintf($this->language->get('text_pagination'), ($pagination->total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($pagination->total - $limit)) ? $pagination->total : ((($page - 1) * $limit) + $limit), $pagination->total, ceil($pagination->total / $limit));

		$text_strings = array(
			'text_product_id','text_name','text_confirm','text_no_record'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_clipboard'.$this->hb_template_extension, $data));
	}

	public function add_to_clipboard(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		$count = 0;
				
		if (!isset($this->request->post['selected'])){
			$json['warning'] = $this->language->get('text_no_record_selected');
		}else{
			if ($this->validate()){
				foreach ($this->request->post['selected'] as $product_id) {
					$this->model_extension_module_hb_products->add_to_clipboard($product_id);
					$count = $count + 1;
				}
				$json['success'] = sprintf($this->language->get('text_records_added_clipboard'), $count);
			}else{
				$json['warning'] = $this->language->get('error_permission');
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function remove_from_clipboard(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		$count = 0;
				
		if (!isset($this->request->post['selected'])){
			$json['warning'] = $this->language->get('text_no_record_selected');
		}else{
			if ($this->validate()){
				foreach ($this->request->post['selected'] as $product_id) {
					$this->model_extension_module_hb_products->remove_from_clipboard($product_id);
					$count = $count + 1;
				}
				$json['success'] = sprintf($this->language->get('text_records_deleted_clipboard'), $count);
			}else{
				$json['warning'] = $this->language->get('error_permission');
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function clear_clipboard(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		if ($this->validate()){
			$this->model_extension_module_hb_products->clear_clipboard();
			$json['success'] = $this->language->get('text_records_cleared_clipboard');
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function find_data() {  		
		$this->load->model('extension/module/hb_products');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		if (isset($this->request->get['tablename'])) {
			$tablename = $this->request->get['tablename'];
		} else {
			$tablename = '';
		}

		if (isset($this->request->get['columnname'])) {
			$columnname = $this->request->get['columnname'];
		} else {
			$columnname = '';
		}

		if (isset($this->request->get['value'])) {
			$value = $this->request->get['value'];
		} else {
			$value = '';
		}
		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		if (isset($this->request->get['tablename'])) {
			$url .= '&tablename=' . $this->request->get['tablename'];
		}

		if (isset($this->request->get['columnname'])) {
			$url .= '&columnname=' . $this->request->get['columnname'];
		}

		if (isset($this->request->get['value'])) {
			$url .= '&value=' . $this->request->get['value'];
		}
		
		$data = array(
			'start' 	=> ($page - 1) * 20,
			'limit' 	=> 20,
			'tablename'	=> $tablename,
			'columnname'	=> $columnname,
			'value'		=> $value		
		);	
		
		$product_total 		= $this->model_extension_module_hb_products->getTotalRows($data); 		
		$results 			= $this->model_extension_module_hb_products->getRows($data);

		$data['rows'] = array();
		$data['columns'] = array();

		if ($results) {
			foreach ($results as $key => $value){
				$data['rows'][$key] = $value;			
			}
	
			$data['columns'] = $data['rows'][0];
		}		
		
		//print_r($data['rows'][0]);

		
		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = 20;
		$pagination->url = $this->url->link($this->hb_extension_route.'/hb_products/find_data', $this->hb_token_name.'=' . $this->session->data[$this->hb_token_name] . $url .'&page={page}', true);

		$data['pagination'] = $pagination->render();
		$limit = 20;

		$data['results'] = sprintf($this->language->get('text_pagination'), ($pagination->total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($pagination->total - $limit)) ? $pagination->total : ((($page - 1) * $limit) + $limit), $pagination->total, ceil($pagination->total / $limit));

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;	

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_table_data'.$this->hb_template_extension, $data));
	}


	public function quick_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->language('catalog/product');

		$this->load->model('extension/module/hb_products');
		$this->load->model('catalog/product');
		$this->load->model('setting/setting');

		$extn_info = $this->model_setting_setting->getSetting('hb_products', 0);

		$text_strings = array(
			'tab_general','tab_seo', 'tab_data','tab_category', 'entry_name','entry_description','entry_meta_title','entry_meta_description','entry_meta_keyword','entry_keyword','entry_tag',
			'button_save','text_enabled','text_disabled','help_keyword','text_none',
			'entry_model','entry_manufacturer','entry_sku','entry_upc','entry_ean','entry_jan','entry_isbn','entry_mpn','entry_location','entry_price','entry_quantity','entry_status','entry_category',
			'help_sku','help_upc','help_ean','help_jan','help_isbn','help_mpn'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		$data['product_id'] = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (count($data['languages']) == 1) {
			$data['multiple_language'] = 'na';
		}else{
			$data['multiple_language'] = '';
		}

		if (isset($this->request->get['product_id'])) {
			$data['product_description'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
		} else {
			$data['product_description'] = array();
		}

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;

		if ($data['product_id'] > 0) {
			$product_info = $this->model_catalog_product->getProduct($data['product_id']);
			$data['product_description'] = $this->model_catalog_product->getProductDescriptions($data['product_id']);
		}else{
			$data['product_description'] = array();
		}
		
		$this->load->model('setting/store');

		$data['stores'] = array();
		
		$data['stores'][] = array(
			'store_id' => 0,
			'name'     => $this->language->get('text_default')
		);
		
		$stores = $this->model_setting_store->getStores();

		foreach ($stores as $store) {
			$data['stores'][] = array(
				'store_id' => $store['store_id'],
				'name'     => $store['name']
			);
		}

		$this->load->model('catalog/manufacturer');

		$this->load->model('catalog/category');

		if ($data['product_id'] > 0) {
			$categories = $this->model_catalog_product->getProductCategories($this->request->get['product_id']);
		} else {
			$categories = array();
		}

		$data['product_categories'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['product_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		if (!empty($product_info)) {
			$data['model'] 		= $product_info['model'];
			$data['sku'] 		= $product_info['sku'];
			$data['upc'] 		= $product_info['upc'];
			$data['ean'] 		= $product_info['ean'];
			$data['jan'] 		= $product_info['jan'];
			$data['isbn'] 		= $product_info['isbn'];
			$data['mpn'] 		= $product_info['mpn'];
			$data['location'] 	= $product_info['location'];
			$data['price'] 		= $product_info['price'];
			$data['quantity'] 	= $product_info['quantity'];
			$data['status'] 	= $product_info['status'];
			
			$data['manufacturer_id'] = $product_info['manufacturer_id'];
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);

			if ($manufacturer_info) {
				$data['manufacturer'] = $manufacturer_info['name'];
			} else {
				$data['manufacturer'] = '';
			}

			if (version_compare(VERSION,'3.0.0.0','>=' )) {
				$data['product_seo_url'] = $this->model_catalog_product->getProductSeoUrls($this->request->get['product_id']);
			}else{
				$data['product_seo_url'] = array();
				$data['keyword'] = $product_info['keyword'];
			}
		} else {
			$data['model'] 		= '';
			$data['sku'] 		= '';
			$data['upc'] 		= '';
			$data['ean'] 		= '';
			$data['jan'] 		= '';
			$data['isbn'] 		= '';
			$data['mpn'] 		= '';	
			$data['location'] 	= '';
			$data['price'] 		= '';
			$data['quantity'] 	= '';
			$data['status'] 	= '';

			$data['manufacturer_id'] = '0';
			$data['manufacturer'] = '';

			$data['keyword'] 		 = '';
			$data['product_seo_url'] = array();
		}

		$columns = $this->model_extension_module_hb_products->quick_form_columns();

		foreach ($columns as $column) {
			$data['hb_products_show'][$column] = isset($extn_info['hb_products_qf_show_'.$column])?$extn_info['hb_products_qf_show_'.$column]:false;
		}


		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_quick_form'.$this->hb_template_extension, $data));
	}

	public function save_quick_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		if (isset($this->request->post['product_id']) && ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if ($this->request->post['product_id'] > 0) {
				$this->model_extension_module_hb_products->editProduct($this->request->post['product_id'], $this->request->post);
				$json['success'] = $this->language->get('text_updated');
			}else{
				$this->model_extension_module_hb_products->addProduct($this->request->post);
				$json['success'] = $this->language->get('text_added');
				$json['close'] = true;
			}
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function query_block(){
		$this->load->model('extension/module/hb_products');
		
		$product_description_columns = $this->model_extension_module_hb_products->getColumns('product_description');
		foreach ($product_description_columns as $column) {
			$data['columns'][] = array(
				'id'	=> 'product_description.'.$column,
				'name'	=>	'pd.'.$column
			);
		}

		$product_columns = $this->model_extension_module_hb_products->getColumns('product');
		
		foreach ($product_columns as $column) {
			$data['columns'][] = array(
				'id'	=> 'product.'.$column,
				'name'	=>	'p.'.$column
			);
		}

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_query_block'.$this->hb_template_extension, $data));
	}

	public function load_operator(){
		$this->load->model('extension/module/hb_products');
		$html = '';

		$column 	= $this->request->get['column_name'];

		if (!empty($column)){

			$column = explode('.',$column);
			$column_name 	= $column[1];
			$table_name 	= $column[0];

			$data_type 		= $this->model_extension_module_hb_products->getdatatype($table_name, $column_name);

			$data_type1 = array('int','tinyint','decimal','date','datetime');
			if (in_array($data_type,$data_type1)) {
				$operators =  array(
					'=' 	=> 'EQUALS TO',
					'!=' 	=> 'NOT EQUALS TO',
					'>' 	=> 'GREATER THAN',
					'<' 	=> 'LESS THAN',
					'>=' 	=> 'GREATER THAN AND EQUAL TO',
					'<=' 	=> 'LESS THAN AND EQUAL TO',
					'LIKE' 	=> 'LIKE',
					'NOT LIKE' 	=> 'NOT LIKE',
					'IN' 	=> 'IN',
					'NOT IN' 	=> 'NOT IN'
				);
			}else{
				$operators =  array(
					'LIKE' 	=> 'LIKE',
					'NOT LIKE' 	=> 'NOT LIKE',
					'=' 	=> 'EQUALS TO',
					'!=' 	=> 'NOT EQUALS TO',
					'IN' 	=> 'IN',
					'NOT IN' 	=> 'NOT IN'
				);
			}

		
			
			foreach ($operators as $key => $value) {
				$html .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}else{
			$html .= '<option value="">--SELECT COLUMN--</option>';
		}

		$json['operators'] = $html;

		$this->response->setOutput(json_encode($json));
	}

	public function load_column(){
		$this->load->model('extension/module/hb_products');
		$tablename 	= $this->request->get['tablename'];
		$columns 	= $this->model_extension_module_hb_products->getColumns($tablename);

		$html = '<option value="">--SELECT COLUMN--</option>';
		foreach ($columns as $column) {
			$html .= '<option value="'.$tablename.'.'.$column.'">'.$tablename.'.'.$column.'</option>';
		}

		$json['columns'] = $html;

		$this->response->setOutput(json_encode($json));
	}

	public function process_query_form(){
		//$this->log->write($this->request->post);
		$this->load->model('extension/module/hb_products');
		$query_data = $this->request->post;
		$filter_statement = '';

		foreach ($query_data['qfilter'] as $q){
			if (!empty($q['column'])){
				$filter_statement .= $q['condition'].' ';
				$filter_statement .= $q['column'].' ';
				$filter_statement .= $q['operator'].' ';
				$filter_statement .=  $this->model_extension_module_hb_products->value_statement($q['operator'], $q['value']);			
			}
		}

		$json['query'] = $filter_statement;

		//ADDON TABLE
		$json['add_table_query'] = '';
		$json['add_table_filter'] = '';

		$addon_data = $this->request->post['addon_table'];
		if (!empty($addon_data['table'])){
			$add_table_query = $addon_data['table']. ' '.$addon_data['table'].' ON (product.product_id = '.$addon_data['table'].'.product_id)';
			$json['add_table_query'] = $add_table_query;

			if (!empty($addon_data['column'])){
				$add_table_filter = '';
				$add_table_filter .= $addon_data['column'].' ';
				$add_table_filter .= $addon_data['operator'].' ';
				$add_table_filter .=  $this->model_extension_module_hb_products->value_statement($addon_data['operator'], $addon_data['value']);

				$json['add_table_filter'] = $add_table_filter;
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function export2csv(){		
		$this->load->model('extension/module/hb_products');
		if (isset($this->request->get['tablename'])) {
			$tablename = $this->request->get['tablename'];
		} else {
			$tablename = '';
		}

		if (isset($this->request->get['query'])) {
			$query = $this->request->get['query'];
		} else {
			$query = '';
		}

		if (isset($this->request->get['add_table_query'])) {
			$add_table_query = $this->request->get['add_table_query'];
		} else {
			$add_table_query = '';
		}

		if (isset($this->request->get['add_table_filter'])) {
			$add_table_filter = $this->request->get['add_table_filter'];
		} else {
			$add_table_filter = '';
		}

		$filename = $tablename.'_'.date("Y-m-d-H-i-s").'.csv';

		switch ($tablename) {
			case 'product':
				$sql = "SELECT product_description.name as product_name, product.* FROM " . DB_PREFIX . "product product LEFT JOIN " . DB_PREFIX . "product_description product_description ON (product.product_id = product_description.product_id)";
				break;
			
			case 'product_description':
				$sql = "SELECT product_description.* FROM " . DB_PREFIX . "product product LEFT JOIN " . DB_PREFIX . "product_description product_description ON (product.product_id = product_description.product_id)";
				break;
			
			default:
				$sql = "SELECT product_description.name as product_name, ".$tablename.".* FROM ".DB_PREFIX.$tablename." ".$tablename." LEFT JOIN " . DB_PREFIX . "product product ON (product.product_id = ".$tablename.".product_id) LEFT JOIN " . DB_PREFIX . "product_description product_description ON (product.product_id = product_description.product_id)";
				break;
		}

		$addon_table = DB_PREFIX.$tablename." ".$tablename;
		if (!empty($add_table_query) && (strpos($sql, $addon_table) === false)) {
			$sql .= " LEFT JOIN ".DB_PREFIX.$add_table_query;
		}

		if (!empty($add_table_query) && (strpos($sql, $add_table_query) === false) && (strpos($sql, $addon_table) === false)) {
			$sql .= " LEFT JOIN ".DB_PREFIX.$add_table_query;
		}

		if ($tablename != 'product_description'){
			$sql .= " WHERE product_description.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		}else{
			$sql .= " WHERE 1 = 1";
		}

		if (!empty($query)) {
			$sql .= " AND ".html_entity_decode($query, ENT_QUOTES, 'UTF-8');
		} 

		if (!empty($add_table_query) && !empty($add_table_filter)) {
			$sql .= " AND ".html_entity_decode($add_table_filter, ENT_QUOTES, 'UTF-8');
		} 
        //$this->log->write($sql);
        $fp = fopen('php://output', 'w');
        
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename='.$filename);

        fputs( $fp, "\xEF\xBB\xBF" );

		$query = $this->db->query($sql);
        $rows = $query->rows;

		if (!empty($rows)) {
			$first_row = $rows[0];
			
			foreach ($first_row as $key => $value){
				$columns[] = $key;
			}
			fputcsv($fp, $columns);

			foreach ($rows as $row) {	
				fputcsv($fp, $row);
			}	
		}else{
			$default_columns = array('product_name');
			$columns = $this->model_extension_module_hb_products->getColumns($tablename);
			$columns = array_merge($default_columns,$columns);
			fputcsv($fp, $columns);
		}
  
        fclose($fp);
        exit;
	}

	public function csv_import(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		if (!empty($this->request->files['file']['name']) && is_file($this->request->files['file']['tmp_name']) && $this->validate()) {
			$tablename = $this->request->post['upload_table'];
			$filename = $this->request->files['file']['tmp_name'];
			if (is_uploaded_file($this->request->files['file']['tmp_name'])) {
				$content = file_get_contents($this->request->files['file']['tmp_name']);
			} else {
				$content = false;
			}
			
			if ($content) {
				if ($this->request->files['file']['size'] > 0) {
					 $file = fopen($filename, "r");
					 $process_upload = $this->model_extension_module_hb_products->process_csv_upload($tablename, $file);
					 
					 if (isset($process_upload['success'])){
						$json['success'] = $process_upload['success'];
					 }else{
						$json['warning'] = $process_upload['warning'];
					 }
				}
				
			}else{
				$json['warning'] = 'File is empty';
			}			
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}

		$this->response->setOutput(json_encode($json));		
	}
	
	public function save_column(){	
		$this->load->model('extension/module/hb_products');

		$this->load->language($this->hb_extension_route.'/hb_products');

		$product_id 	= (int)$this->request->post['product_id'];
		$column_id 		= $this->request->post['column_id'];
		$table_name		= 'product';//$this->request->post['column_id'];
		$updated_value 	= $this->request->post['updated_value'];
		
		if ($this->validate()) {
			$this->model_extension_module_hb_products->update_column_value($table_name, $column_id, $product_id, $updated_value);
			$json['success'] = sprintf($this->language->get('text_product_column_updated'), $column_id, $product_id);

			if (isset($this->request->get['apply_all']) && $this->request->get['apply_all'] == 1){
				if ($this->model_extension_module_hb_products->get_all_clipboard_products()) {
					$products = $this->model_extension_module_hb_products->get_all_clipboard_products();
					foreach ($products as $product){
						$product_id = (int)$product['product_id'];
						$this->model_extension_module_hb_products->update_column_value($table_name, $column_id, $product_id, $updated_value);
					}

					$total_clipboard_products = $this->model_extension_module_hb_products->getTotalClipboardItems(array());
					$json['success'] .= '. '. sprintf($this->language->get('text_clipboard_changes_applied'), $total_clipboard_products);
				}
			}

		}else{
			$json['warning'] = $this->language->get('error_permission');
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function language_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');
		$this->load->model('catalog/product');
		$text_strings = array(
			'tab_general','tab_seo', 'entry_name','entry_description','entry_meta_title','entry_meta_description','entry_meta_keyword','entry_keyword','entry_tag','button_save'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		$data['product_id'] = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (count($data['languages']) == 1) {
			$data['multiple_language'] = 'na';
		}else{
			$data['multiple_language'] = '';
		}

		if (isset($this->request->get['product_id'])) {
			$data['product_description'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
		} else {
			$data['product_description'] = array();
		}

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_language_form'.$this->hb_template_extension, $data));
	}

	public function category_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');
		$this->load->model('catalog/product');
		$text_strings = array(
			'entry_category','button_save','text_close'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		$data['product_id'] = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		$this->load->model('catalog/category');

		if (isset($this->request->get['product_id'])) {
			$categories = $this->model_catalog_product->getProductCategories($data['product_id']);
		} else {
			$categories = array();
		}

		$data['product_categories'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['product_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_category_form'.$this->hb_template_extension, $data));
	}

	public function category_autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('catalog/category');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 10
			);

			$results = $this->model_catalog_category->getCategories($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function get_category(){
		$product_id = $this->request->get['product_id'];
		$this->load->model('extension/module/hb_products');
		$this->load->model('catalog/product');
		$categories = $this->model_catalog_product->getProductCategories($product_id);

		$product_categories = array();
		
		foreach ($categories as $category_id) {
			$category_info = $this->model_extension_module_hb_products->getCategory($category_id);

			if ($category_info) {
				$product_categories[] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' / ' . $category_info['name'] : $category_info['name']
				);
			}
		}

		$html = '<table class="table-category">';
		foreach ($product_categories as $product_category) { 
			$html .= '<tr><td><i class="fa fa-angle-double-right"></i></td><td>'.$product_category['name'].'</td></tr>';
		} 
		$html .= '</table>';

		$json['html'] = $html;
		
		$this->response->setOutput(json_encode($json));
	}

	public function special_price_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		$text_strings = array(
			'text_customer_group','text_priority','text_price','text_date_start','text_date_end','button_remove','button_special_add','button_save'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		if (version_compare(VERSION,'2.1.0.1','<')){
			$this->load->model('sale/customer_group');
			$data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
		}else{
			$this->load->model('customer/customer_group');
			$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
		}

		$data['product_id'] = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		if ($data['product_id'] > 0) {
			$product_specials = $this->model_extension_module_hb_products->getProductSpecials($data['product_id']);
		} else {
			$product_specials = array();
		}

		$data['product_specials'] = array();

		foreach ($product_specials as $product_special) {
			$data['product_specials'][] = array(
				'customer_group_id' => $product_special['customer_group_id'],
				'priority'          => $product_special['priority'],
				'price'             => $product_special['price'],
				'date_start'        => ($product_special['date_start'] != '0000-00-00') ? $product_special['date_start'] : '',
				'date_end'          => ($product_special['date_end'] != '0000-00-00') ? $product_special['date_end'] :  ''
			);
		}

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_price_form'.$this->hb_template_extension, $data));
	}

	public function attribute_form(){
		$this->load->language('catalog/product');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		$this->load->model('extension/module/hb_products');
		$this->load->model('catalog/product');

		$text_strings = array(
			'entry_attribute','entry_text','button_remove','button_attribute_add','button_save','text_close','text_same_content_copy'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		$data['product_id'] = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['languages_count'] =  count($data['languages']);

		$this->load->model('catalog/attribute');

		if (isset($this->request->get['product_id'])) {
			$product_attributes = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);
		} else {
			$product_attributes = array();
		}

		$data['product_attributes'] = array();

		foreach ($product_attributes as $product_attribute) {
			$attribute_info = $this->model_catalog_attribute->getAttribute($product_attribute['attribute_id']);

			if ($attribute_info) {
				$data['product_attributes'][] = array(
					'attribute_id'                  => $product_attribute['attribute_id'],
					'name'                          => $attribute_info['name'],
					'product_attribute_description' => $product_attribute['product_attribute_description']
				);
			}
		}

		$data['admin_language_id'] = (int)$this->config->get('config_language_id');

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_attribute_form'.$this->hb_template_extension, $data));
	}

	public function option_form(){
		$this->load->language('catalog/product');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		$this->load->model('extension/module/hb_products');
		$this->load->model('catalog/product');

		$text_strings = array(
		'entry_subtract','entry_option','entry_option_points','entry_required','text_yes','text_no','entry_option_value','entry_quantity','entry_price','entry_points','entry_weight','button_option_value_add','button_remove','button_attribute_add','button_save','text_close'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		$data['product_id'] = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('catalog/option');

		if (isset($this->request->post['product_option'])) {
			$product_options = $this->request->post['product_option'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);
		} else {
			$product_options = array();
		}

		$data['product_options'] = array();

		foreach ($product_options as $product_option) {
			$product_option_value_data = array();

			if (isset($product_option['product_option_value'])) {
				foreach ($product_option['product_option_value'] as $product_option_value) {
					$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'points'                  => $product_option_value['points'],
						'points_prefix'           => $product_option_value['points_prefix'],
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix']
					);
				}
			}

			$data['product_options'][] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => isset($product_option['value']) ? $product_option['value'] : '',
				'required'             => $product_option['required']
			);
		}

		$data['option_values'] = array();

		foreach ($data['product_options'] as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				if (!isset($data['option_values'][$product_option['option_id']])) {
					$data['option_values'][$product_option['option_id']] = $this->model_catalog_option->getOptionValues($product_option['option_id']);
				}
			}
		}

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_option_form'.$this->hb_template_extension, $data));
	}

	public function save_price_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_extension_module_hb_products->update_special_price($this->request->get['product_id'], $this->request->post);
			
			$json['success'] = $this->language->get('text_price_updated');

			if (isset($this->request->get['apply_all']) && $this->request->get['apply_all'] == 1){
				if ($this->model_extension_module_hb_products->get_all_clipboard_products()) {
					$products = $this->model_extension_module_hb_products->get_all_clipboard_products();
					foreach ($products as $product){
						$product_id = (int)$product['product_id'];
						$this->model_extension_module_hb_products->update_special_price($product_id, $this->request->post);
					}

					$total_clipboard_products = $this->model_extension_module_hb_products->getTotalClipboardItems(array());
					$json['success'] .= '. '. sprintf($this->language->get('text_clipboard_changes_applied'), $total_clipboard_products);
				}
			}
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function save_language_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		if (isset($this->request->post['product_id']) && ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_extension_module_hb_products->update_product_language($this->request->post['product_id'], $this->request->post);
			
			$json['success'] = $this->language->get('text_updated');
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function save_attribute_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_extension_module_hb_products->update_attribute($this->request->get['product_id'], $this->request->post, $this->request->get['same_content']);
			
			$json['success'] = $this->language->get('text_updated');

			if (isset($this->request->get['apply_all']) && $this->request->get['apply_all'] == 1){
				if ($this->model_extension_module_hb_products->get_all_clipboard_products()) {
					$products = $this->model_extension_module_hb_products->get_all_clipboard_products();
					foreach ($products as $product){
						$product_id = (int)$product['product_id'];
						$this->model_extension_module_hb_products->update_attribute($product_id, $this->request->post, $this->request->get['same_content']);
					}

					$total_clipboard_products = $this->model_extension_module_hb_products->getTotalClipboardItems(array());
					$json['success'] .= '. '. sprintf($this->language->get('text_clipboard_changes_applied'), $total_clipboard_products);
				}
			}
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function save_option_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$post_data = $this->request->post;
			$this->model_extension_module_hb_products->update_option($this->request->get['product_id'], $post_data);
			
			$json['success'] = $this->language->get('text_updated');

			if (isset($this->request->get['apply_all']) && $this->request->get['apply_all'] == 1){
				if ($this->model_extension_module_hb_products->get_all_clipboard_products()) {
					$products = $this->model_extension_module_hb_products->get_all_clipboard_products();
					foreach ($products as $product){
						$product_id = (int)$product['product_id'];
						$this->model_extension_module_hb_products->update_option($product_id, $post_data);
					}

					$total_clipboard_products = $this->model_extension_module_hb_products->getTotalClipboardItems(array());
					$json['success'] .= '. '. sprintf($this->language->get('text_clipboard_changes_applied'), $total_clipboard_products);
				}
			}
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function save_category_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_extension_module_hb_products->update_product_category($this->request->get['product_id'], $this->request->post);

			$json['success'] = $this->language->get('text_updated');

			if (isset($this->request->get['apply_all']) && $this->request->get['apply_all'] == 1){
				if ($this->model_extension_module_hb_products->get_all_clipboard_products()) {
					$products = $this->model_extension_module_hb_products->get_all_clipboard_products();
					foreach ($products as $product){
						$product_id = (int)$product['product_id'];
						$this->model_extension_module_hb_products->update_product_category($product_id, $this->request->post);
					}

					$total_clipboard_products = $this->model_extension_module_hb_products->getTotalClipboardItems(array());
					$json['success'] .= '. '. sprintf($this->language->get('text_clipboard_changes_applied'), $total_clipboard_products);
				}
			}
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function image_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');
		$this->load->model('tool/image');

		$text_strings = array(
			'entry_additional_image','entry_sort_order','button_remove','button_special_add','button_save','text_add_image','text_close'
		);
		
		foreach ($text_strings as $text) {
			$data[$text] = $this->language->get($text);
		}

		$data['product_id'] = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		if ($data['product_id'] > 0) {
			$product_images = $product_images = $this->model_extension_module_hb_products->getProductImages($data['product_id']);
		} else {
			$product_images = array();
		}

		$data['product_images'] = array();

		foreach ($product_images as $product_image) {
			if (is_file(DIR_IMAGE . $product_image['image'])) {
				$image = $product_image['image'];
				$thumb = $product_image['image'];
			} else {
				$image = '';
				$thumb = 'no_image.png';
			}

			$data['product_images'][] = array(
				'image'      => $image,
				'thumb'      => $this->model_tool_image->resize($thumb, 100, 100),
				'sort_order' => $product_image['sort_order']
			);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		$data[$this->hb_token_name] = $this->session->data[$this->hb_token_name];
		$data['base_route'] = $this->hb_extension_route;

		$this->response->setOutput($this->load->view('extension/module/'.$this->hb_template_folder.'/hb_products_image_form'.$this->hb_template_extension, $data));
	}

	public function save_image_form(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$this->load->model('extension/module/hb_products');

		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_extension_module_hb_products->update_images($this->request->get['product_id'], $this->request->post);
			
			$json['success'] = $this->language->get('text_image_updated');

			if (isset($this->request->get['apply_all']) && $this->request->get['apply_all'] == 1){
				if ($this->model_extension_module_hb_products->get_all_clipboard_products()) {
					$products = $this->model_extension_module_hb_products->get_all_clipboard_products();
					foreach ($products as $product){
						$product_id = (int)$product['product_id'];
						$this->model_extension_module_hb_products->update_images($product_id, $this->request->post);
					}

					$total_clipboard_products = $this->model_extension_module_hb_products->getTotalClipboardItems(array());
					$json['success'] .= '. '. sprintf($this->language->get('text_clipboard_changes_applied'), $total_clipboard_products);
				}
			}
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}
		$this->response->setOutput(json_encode($json));
	}

	public function add_images_to_block(){
		$this->load->model('tool/image');
		$placeholder = $this->model_tool_image->resize('no_image.png', 100, 100);
		$html = '';
		$images = $this->request->post['images'];
		$counter = $this->request->post['counter'];
		$images = explode(',',$images); 

		foreach ($images as $image){
			$image_src = $this->model_tool_image->resize($image, 100, 100);
			$html .= '<div class="col-sm-2 text-center image_item" id="row-'.$counter.'">';
			$html .= '<img src="'.$image_src.'" alt="" title="" data-placeholder="'.$placeholder.'" class="img-thumbnail" /><input type="hidden" name="product_image[]" value="'.$image.'" />';
			$html .= '<a onclick="$(\'#row-' . $counter  . '\').remove();" data-toggle="tooltip" title="Delete" class="btn btn-sm btn-danger image-delete"><i class="fa fa-minus-circle"></i></a>';
			$html .= '</div>';
			$counter++;
		}
			
		$json['success'] = $html;
		$json['counter'] = $counter;
		
		$this->response->setOutput(json_encode($json));
	}

	public function generate_thumb(){
		$this->load->model('tool/image');

		$image = $this->request->post['image'];
		if (!empty($image)){
			$thumb = $this->model_tool_image->resize($image, $this->image_width, $this->image_height);
		}else{
			$thumb = $this->model_tool_image->resize('no_image.png', $this->image_width, $this->image_height);
		}
		$json['thumb'] = $thumb;
		
		$this->response->setOutput(json_encode($json));
	}


	public function update_product_status(){
		$this->load->language($this->hb_extension_route.'/hb_products');
		$count = 0;
		
		$status = (int)$this->request->post['status'];
		
		if (!isset($this->request->post['selected'])){
			$json['warning'] = $this->language->get('text_no_record_selected');
		}else{
			if ($this->validate()){
				foreach ($this->request->post['selected'] as $id) {
					$this->db->query("UPDATE `" . DB_PREFIX . "product` SET `status` = '".(int)$status."' WHERE `product_id` = '".(int)$id."'");
					$count = $count + 1;
				}
				$json['success'] = sprintf($this->language->get('text_records_updated'), $count);
			}else{
				$json['warning'] = $this->language->get('error_permission');
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function delete_product(){
		$this->load->model('catalog/product');
		$this->load->language($this->hb_extension_route.'/hb_products');
		$count = 0;
				
		if (!isset($this->request->post['selected'])){
			$json['warning'] = $this->language->get('text_no_record_selected');
		}else{
			if ($this->validate()){
				foreach ($this->request->post['selected'] as $product_id) {
					$this->model_catalog_product->deleteProduct($product_id);
					$count = $count + 1;
				}
				$json['success'] = sprintf($this->language->get('text_records_deleted'), $count);
			}else{
				$json['warning'] = $this->language->get('error_permission');
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function copy_product(){
		$this->load->model('catalog/product');
		$this->load->language($this->hb_extension_route.'/hb_products');
		$count = 0;
				
		if (!isset($this->request->post['selected'])){
			$json['warning'] = $this->language->get('text_no_record_selected');
		}else{
			if ($this->validate()){
				foreach ($this->request->post['selected'] as $product_id) {
					$this->model_catalog_product->copyProduct($product_id);
					$count = $count + 1;
				}
				$json['success'] = sprintf($this->language->get('text_records_copied'), $count);
			}else{
				$json['warning'] = $this->language->get('error_permission');
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function add_to_store(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
				
		if (!isset($this->request->post['selected'])){
			$json['warning'] = $this->language->get('text_no_record_selected');
		}else{
			if ($this->validate()){
				foreach ($this->request->post['selected'] as $store_id) {
					$this->model_extension_module_hb_products->add_to_store($store_id);
				}
				$json['success'] = $this->language->get('text_updated');
			}else{
				$json['warning'] = $this->language->get('error_permission');
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function remove_from_store(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
				
		if (!isset($this->request->post['selected'])){
			$json['warning'] = $this->language->get('text_no_record_selected');
		}else{
			if ($this->validate()){
				foreach ($this->request->post['selected'] as $store_id) {
					$this->model_extension_module_hb_products->remove_from_store($store_id);
				}
				$json['success'] = $this->language->get('text_updated');
			}else{
				$json['warning'] = $this->language->get('error_permission');
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}

	public function add_category(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		$category_id = $this->request->get['category_id'];
		if ($this->validate()){
			$this->model_extension_module_hb_products->add_category($category_id);
			$json['success'] = $this->language->get('text_updated');
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function remove_category(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		$category_id = $this->request->get['category_id'];
		if ($this->validate()){
			$this->model_extension_module_hb_products->remove_category($category_id);
			$json['success'] = $this->language->get('text_updated');
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function add_filter(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		$filter_id = $this->request->get['filter_id'];
		if ($this->validate()){
			$this->model_extension_module_hb_products->add_filter($filter_id);
			$json['success'] = $this->language->get('text_updated');
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function remove_filter(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		$filter_id = $this->request->get['filter_id'];
		if ($this->validate()){
			$this->model_extension_module_hb_products->remove_filter($filter_id);
			$json['success'] = $this->language->get('text_updated');
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function add_related(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		$related_id = $this->request->get['related_id'];
		if ($this->validate()){
			$this->model_extension_module_hb_products->add_related($related_id);
			$json['success'] = $this->language->get('text_updated');
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function remove_related(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		$related_id = $this->request->get['related_id'];
		if ($this->validate()){
			$this->model_extension_module_hb_products->remove_related($related_id);
			$json['success'] = $this->language->get('text_updated');
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function update_manufacturer(){
		$this->load->model('extension/module/hb_products');
		$this->load->language($this->hb_extension_route.'/hb_products');
		
		$manufacturer_id = $this->request->get['manufacturer_id'];
		if ($this->validate()){
			$this->model_extension_module_hb_products->update_manufacturer($manufacturer_id);
			$json['success'] = $this->language->get('text_updated');
		}else{
			$json['warning'] = $this->language->get('error_permission');
		}

		$this->response->setOutput(json_encode($json));
	}

	public function manufacturers(){
		$manufacturers = array();

		$manufacturers[] = array('id'=>0,'name'=>'--None--');
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "manufacturer` ORDER BY `name`");

		if ($query->rows) {
			foreach ($query->rows as $row) {
				$manufacturers[] = array(
					'id' 			=> $row['manufacturer_id'],
					'name' 			=> $row['name']
					);
			}
		}
		
		$json = $manufacturers;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function get_manufacturer(){
		$product_id = $this->request->get['product_id'];

		$query = $this->db->query("SELECT b.name FROM " . DB_PREFIX . "product a, " . DB_PREFIX . "manufacturer b WHERE a.manufacturer_id = b.manufacturer_id AND a.product_id = '".(int)$product_id."' LIMIT 1");
		if ($query->row) {
			$manufacturer = $query->row['name'];
		}else{
			$manufacturer = '--';
		}
		$json['success'] = $manufacturer;
		
		$this->response->setOutput(json_encode($json));
	}

	public function get_special_price(){
		$this->load->model('extension/module/hb_products');

		$product_id = $this->request->get['product_id'];

		$product_info = $this->model_extension_module_hb_products->getProduct($product_id);
		if ($product_info) {
			$special = $product_info['special'];
		}else{
			$special = '--';
		}
		$json['html'] = $special;
		
		$this->response->setOutput(json_encode($json));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->hb_extension_route.'/hb_products')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install(){
		$this->load->model('extension/module/hb_products');
		$this->model_extension_module_hb_products->install();
		$data['success'] = 'Module has been installed successfully';
	}
	
	public function uninstall(){
		$this->load->model('extension/module/hb_products');
		$this->model_extension_module_hb_products->uninstall();
		$data['success'] = 'Module uninstalled Successfully!';
	}
	
}