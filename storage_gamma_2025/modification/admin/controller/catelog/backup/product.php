<?php
class ControllerCatalogProduct extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			
			
        //$product_id =
       
          /**
           * Marketplace Code Starts Here
           */
          if($this->config->get('module_marketplace_status')) {
                  $product_id = $this->model_catalog_product->addProduct($this->request->post);
                  $this->load->model("wkcustomfield/wkcustomfield");
                  $wkcustomFields = array();
                  if(isset($this->request->post['product_custom_field'])){
                      $this->model_wkcustomfield_wkcustomfield->addCustomFields($this->request->post['product_custom_field'],$product_id);
                  }
          }else{
              $this->model_catalog_product->addProduct($this->request->post);
          }
          /**
           * Marketplace Code Ends Here
           */
      
            

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

		if (isset($this->request->get['filter_cost'])) {
			$url .= '&filter_cost=' . $this->request->get['filter_cost'];
		}	
					
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}	
		
		if (isset($this->request->get['filter_manufacturer_id'])) {
			$url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
		}	
							
		if (isset($this->request->get['filter_supplier_id'])) {
			$url .= '&filter_supplier_id=' . $this->request->get['filter_supplier_id'];
		}		
            

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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


			if(isset($this->request->post['save_apply']) && $this->request->post['save_apply'] = 1){
				$this->response->redirect($this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $product_id . $url, true));
			}
		    
			
        /**
         * Marketplace Code Starts Here
         */
        if ($this->config->get('module_marketplace_status') && isset($this->request->get['mpcheck'])) {
            $this->response->redirect($this->url->link('customerpartner/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
        } else {
            $this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
         /**
         * Marketplace Code Ends Here
         */
      
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

        if($this->config->get('module_marketplace_status')) {
                $this->load->model("wkcustomfield/wkcustomfield");
                $wkcustomFields = array();
                if(isset($this->request->post['product_custom_field'])){
                    $data = $this->request->post['product_custom_field'];
                }else{
                    $data = '';
                }
                $this->model_wkcustomfield_wkcustomfield->editCustomFields($data,$this->request->get['product_id']);
        }
      
			$this->model_catalog_product->editProduct($this->request->get['product_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

		if (isset($this->request->get['filter_cost'])) {
			$url .= '&filter_cost=' . $this->request->get['filter_cost'];
		}	
					
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}	
		
		if (isset($this->request->get['filter_manufacturer_id'])) {
			$url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
		}	
							
		if (isset($this->request->get['filter_supplier_id'])) {
			$url .= '&filter_supplier_id=' . $this->request->get['filter_supplier_id'];
		}		
            

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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


			if(isset($this->request->post['save_apply']) && $this->request->post['save_apply'] = 1){
				$this->response->redirect($this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . $url, true));
			}
		    
			
        /**
         * Marketplace Code Starts Here
         */
        if ($this->config->get('module_marketplace_status') && isset($this->request->get['mpcheck'])) {
            $this->response->redirect($this->url->link('customerpartner/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
        } else {
            $this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
         /**
         * Marketplace Code Ends Here
         */
      
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->deleteProduct($product_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

		if (isset($this->request->get['filter_cost'])) {
			$url .= '&filter_cost=' . $this->request->get['filter_cost'];
		}	
					
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}	
		
		if (isset($this->request->get['filter_manufacturer_id'])) {
			$url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
		}	
							
		if (isset($this->request->get['filter_supplier_id'])) {
			$url .= '&filter_supplier_id=' . $this->request->get['filter_supplier_id'];
		}		
            

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			
        /**
         * Marketplace Code Starts Here
         */
        if ($this->config->get('module_marketplace_status') && isset($this->request->get['mpcheck'])) {
            $this->response->redirect($this->url->link('customerpartner/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
        } else {
            $this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
         /**
         * Marketplace Code Ends Here
         */
      
		}

		$this->getList();
	}

	public function copy() {
		$this->load->language('catalog/product');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/product');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $product_id) {
				$this->model_catalog_product->copyProduct($product_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_model'])) {
				$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_price'])) {
				$url .= '&filter_price=' . $this->request->get['filter_price'];
			}

		if (isset($this->request->get['filter_cost'])) {
			$url .= '&filter_cost=' . $this->request->get['filter_cost'];
		}	
					
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}	
		
		if (isset($this->request->get['filter_manufacturer_id'])) {
			$url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
		}	
							
		if (isset($this->request->get['filter_supplier_id'])) {
			$url .= '&filter_supplier_id=' . $this->request->get['filter_supplier_id'];
		}		
            

			if (isset($this->request->get['filter_quantity'])) {
				$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			
        /**
         * Marketplace Code Starts Here
         */
        if ($this->config->get('module_marketplace_status') && isset($this->request->get['mpcheck'])) {
            $this->response->redirect($this->url->link('customerpartner/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
        } else {
            $this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }
         /**
         * Marketplace Code Ends Here
         */
      
		}

		$this->getList();
	}

	protected function getList() {

		$data['prm_access_permission'] = $this->user->hasPermission('access', 'extension/module/adv_profit_module');
		$data['modify_permission'] = $this->user->hasPermission('modify', 'catalog/product');
		
		if (!$this->IsInstalled()) {		
			$this->session->data['error_prm'] = $this->language->get('error_installed');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));		
		}	
            
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_model'])) {
			$filter_model = $this->request->get['filter_model'];
		} else {
			$filter_model = '';
		}

		if (isset($this->request->get['filter_price'])) {
			$filter_price = $this->request->get['filter_price'];
		} else {
			$filter_price = '';
		}

		if (isset($this->request->get['filter_cost'])) {
			$filter_cost = $this->request->get['filter_cost'];
		} else {
			$filter_cost = '';
		}
					
		if (isset($this->request->get['filter_category_id'])) {
			$filter_category_id = $this->request->get['filter_category_id'];
		} else {
			$filter_category_id = '';
		}
		
		if (isset($this->request->get['filter_manufacturer_id'])) {
			$filter_manufacturer_id = $this->request->get['filter_manufacturer_id'];
		} else {
			$filter_manufacturer_id = '';
		}
		
		if (isset($this->request->get['filter_supplier_id'])) {
			$filter_supplier_id = $this->request->get['filter_supplier_id'];
		} else {
			$filter_supplier_id = '';
		}
            

		if (isset($this->request->get['filter_quantity'])) {
			$filter_quantity = $this->request->get['filter_quantity'];
		} else {
			$filter_quantity = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_cost'])) {
			$url .= '&filter_cost=' . $this->request->get['filter_cost'];
		}	
					
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}	
		
		if (isset($this->request->get['filter_manufacturer_id'])) {
			$url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
		}	
							
		if (isset($this->request->get['filter_supplier_id'])) {
			$url .= '&filter_supplier_id=' . $this->request->get['filter_supplier_id'];
		}		
            

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['copy'] = $this->url->link('catalog/product/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/product/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['products'] = array();

		$filter_data = array(
			'filter_name'	  => $filter_name,
			'filter_model'	  => $filter_model,
			'filter_price'	  => $filter_price,

			'filter_cost'   			=> $filter_cost,
			'filter_category_id'   		=> $filter_category_id,
			'filter_manufacturer_id'	=> $filter_manufacturer_id,
			'filter_supplier_id'   		=> $filter_supplier_id,
            
			'filter_quantity' => $filter_quantity,
			'filter_status'   => $filter_status,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');

		$product_total = $this->model_catalog_product->getTotalProducts($filter_data);

		
$results = $this->model_catalog_product->getProducts($filter_data);
            

			$this->load->model('extension/module/adv_profit_module');
			$data['categories'] = $this->model_extension_module_adv_profit_module->getProductsCategories(0);
		
			$this->load->model('catalog/manufacturer');
			$data['manufacturers'] = $this->model_catalog_manufacturer->getManufacturers();			
			
			$this->load->model('extension/adv_supplier');
			$data['suppliers'] = $this->model_extension_adv_supplier->getProductSuppliers();
            

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$special = false;

			$profit_special = false;
			$profit_margin_special = false;
			$profit_markup_special = false;
			
			$profit_margin = $this->config->get('adv_plist_profit_margin_status') ? ($result['price'] > 0 ? sprintf("%.2f",((($result['price']-$result['cost'])/$result['price'])*100)) : '0') : '';
			$profit_markup = $this->config->get('adv_plist_profit_markup_status') ? ($result['cost'] > 0 ? sprintf("%.2f",((($result['price']-$result['cost'])/$result['cost'])*100)) : '0') : '';			
            

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));

					$profit_special = $this->currency->format($this->config->get('adv_plist_profit_status') ? ($product_special['price']-$result['cost']) : '', $this->config->get('config_currency'));
					$profit_margin_special = $this->config->get('adv_plist_profit_margin_status') ? ($product_special['price'] > 0 ? sprintf("%.2f",((($product_special['price']-$result['cost'])/$product_special['price'])*100)) : '0') : '';
					$profit_markup_special = $this->config->get('adv_plist_profit_markup_status') ? ($result['cost'] > 0 ? sprintf("%.2f",((($product_special['price']-$result['cost'])/$result['cost'])*100)) : '0') : '';
            

					break;
				}
			}


			$this->load->model('catalog/product');
			$category = $this->model_catalog_product->getProductCategories($result['product_id']);
            
			$data['products'][] = array(
				'product_id' => $result['product_id'],
				'image'      => $image,
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $this->currency->format($result['price'], $this->config->get('config_currency')),

				'manufacturer'       	=> $this->config->get('adv_plist_manufacturer_status') ? $result['manufacturer'] : '',
				'category'  			=> $this->config->get('adv_plist_category_status') ? $category : '',
				'price_tax'  			=> $this->currency->format($this->config->get('adv_plist_price_tax_status') ? $this->model_catalog_product->calculate($result['tax_class_id'], $result['price'], $this->config->get('config_tax')) : '', $this->config->get('config_currency')),
				'special_tax'     		=> $this->currency->format(($this->config->get('adv_plist_price_tax_status') && $product_specials) ? $this->model_catalog_product->calculate($result['tax_class_id'], $product_special['price'], $this->config->get('config_tax')) : '', $this->config->get('config_currency')),				
				'cost'       			=> $this->currency->format($result['cost'], $this->config->get('config_currency')),
				'profit'     			=> $this->currency->format($this->config->get('adv_plist_profit_status') ? ($result['price']-$result['cost']) : '', $this->config->get('config_currency')),
				'profit_special'    	=> $profit_special,
				'profit_margin'     	=> $profit_margin,
				'profit_margin_special'	=> $profit_margin_special,
				'profit_markup'     	=> $profit_markup,
				'profit_markup_special'	=> $profit_markup_special,
				'supplier'       		=> $this->config->get('adv_plist_supplier_status') ? $result['supplier'] : '',				
				'sold'       			=> $this->config->get('adv_plist_sold_status') ? ($result['sold'] ? $result['sold'] : 0) : '',
            
				'special'    => $special,
				'quantity'   => $result['quantity'],
				'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'edit'       => $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['adv_plist_category_status'] = $this->config->get('adv_plist_category_status');
		$data['adv_plist_manufacturer_status'] = $this->config->get('adv_plist_manufacturer_status');
		$data['adv_plist_price_tax_status'] = $this->config->get('adv_plist_price_tax_status');
		$data['adv_plist_cost_status'] = $this->config->get('adv_plist_cost_status');
		$data['adv_plist_profit_status'] = $this->config->get('adv_plist_profit_status');
		$data['adv_plist_profit_margin_status'] = $this->config->get('adv_plist_profit_margin_status');
		$data['adv_plist_profit_markup_status'] = $this->config->get('adv_plist_profit_markup_status');
		$data['adv_plist_supplier_status'] = $this->config->get('adv_plist_supplier_status');
		$data['adv_plist_sold_status'] = $this->config->get('adv_plist_sold_status');
		$data['adv_ext_name'] = $this->language->get('adv_ext_name');
		$data['adv_ext_short_name'] = 'adv_profit_module';
		$data['adv_ext_version'] = $this->language->get('adv_ext_version');	
		$data['adv_ext_url'] = 'http://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=16601';		
		$data['entry_pcost'] = $this->language->get('entry_pcost');
		$data['entry_category'] = $this->language->get('entry_category');
		$data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
		$data['entry_supplier'] = $this->language->get('entry_supplier');
		$data['column_category'] = $this->language->get('column_category');		
		$data['column_manufacturer'] = $this->language->get('column_manufacturer');	
		$data['column_price_tax'] = $this->language->get('column_price_tax');	
		$data['column_cost'] = $this->language->get('column_cost');
		$data['column_profit'] = $this->language->get('column_profit');
		$data['help_profit'] = $this->language->get('help_profit');
		$data['column_profit_margin'] = $this->language->get('column_profit_margin');
		$data['help_profit_margin'] = $this->language->get('help_profit_margin');
		$data['column_profit_markup'] = $this->language->get('column_profit_markup');
		$data['help_profit_markup'] = $this->language->get('help_profit_markup');
		$data['column_supplier'] = $this->language->get('column_supplier');	
		$data['column_sold'] = $this->language->get('column_sold');	
            

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

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_cost'])) {
			$url .= '&filter_cost=' . $this->request->get['filter_cost'];
		}	
					
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}	
		
		if (isset($this->request->get['filter_manufacturer_id'])) {
			$url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
		}	
							
		if (isset($this->request->get['filter_supplier_id'])) {
			$url .= '&filter_supplier_id=' . $this->request->get['filter_supplier_id'];
		}		
            

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $url, true);
		$data['sort_model'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.model' . $url, true);
		$data['sort_price'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.price' . $url, true);

		$data['sort_category'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p2c.category_id' . $url, true);	
		$data['sort_manufacturer'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=manufacturer' . $url, true);	
		$data['sort_price_tax'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.price' . $url, true);
		$data['sort_cost'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=pc.cost' . $url, true);
		$data['sort_profit'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=profit' . $url, true);
		$data['sort_profit_margin'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=profit_margin' . $url, true);
		$data['sort_profit_markup'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=profit_markup' . $url, true);
		$data['sort_supplier'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=supplier' . $url, true);		
		$data['sort_sold'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=sold' . $url, true);
            
		$data['sort_quantity'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.quantity' . $url, true);
		$data['sort_status'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.status' . $url, true);
		$data['sort_order'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_cost'])) {
			$url .= '&filter_cost=' . $this->request->get['filter_cost'];
		}	
					
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}	
		
		if (isset($this->request->get['filter_manufacturer_id'])) {
			$url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
		}	
							
		if (isset($this->request->get['filter_supplier_id'])) {
			$url .= '&filter_supplier_id=' . $this->request->get['filter_supplier_id'];
		}		
            

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_model'] = $filter_model;
		$data['filter_price'] = $filter_price;

		$data['filter_cost'] = $filter_cost;
		$data['filter_category_id'] = $filter_category_id;
		$data['filter_manufacturer_id'] = $filter_manufacturer_id;
		$data['filter_supplier_id'] = $filter_supplier_id;
            
		$data['filter_quantity'] = $filter_quantity;
		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/product_list', $data));
	}


	public function filter_history() {
	  $data['product_id'] = isset($this->request->get['product_id']);
	  
	  if (isset($this->request->get['product_id'])) {
	    $data['product_id'] = isset($this->request->get['product_id']);
	    $data['product_ids'] = $this->request->get['product_id'];
		
		$json = array();

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['filter_history_date_start'])) {
			$filter_history_date_start = $this->request->get['filter_history_date_start'];
		} else {
			$filter_history_date_start = '';
		}

		if (isset($this->request->get['filter_history_date_end'])) {
			$filter_history_date_end = $this->request->get['filter_history_date_end'];
		} else {
			$filter_history_date_end = '';
		}
		
		$data['ranges_history'] = array();
		
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcustom'),
			'value' => 'custom',
			'style' => 'color:#666',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hweek'),
			'value' => 'week',
			'style' => 'color:#090',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hmonth'),
			'value' => 'month',
			'style' => 'color:#090',
			'id' 	=> '',
		);					
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hquarter'),
			'value' => 'quarter',
			'style' => 'color:#090',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hyear'),
			'value' => 'year',
			'style' => 'color:#090',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcurrent_week'),
			'value' => 'current_week',
			'style' => 'color:#06C',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcurrent_month'),
			'value' => 'current_month',
			'style' => 'color:#06C',
			'id' 	=> '',
		);	
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcurrent_quarter'),
			'value' => 'current_quarter',
			'style' => 'color:#06C',
			'id' 	=> '',
		);			
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcurrent_year'),
			'value' => 'current_year',
			'style' => 'color:#06C',
			'id' 	=> '',
		);			
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hlast_week'),
			'value' => 'last_week',
			'style' => 'color:#F90',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hlast_month'),
			'value' => 'last_month',
			'style' => 'color:#F90',
			'id' 	=> '',
		);	
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hlast_quarter'),
			'value' => 'last_quarter',
			'style' => 'color:#F90',
			'id' 	=> '',
		);			
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hlast_year'),
			'value' => 'last_year',
			'style' => 'color:#F90',
			'id' 	=> '',
		);			
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hall_time'),
			'value' => 'all_time',
			'style' => 'color:#F00',
			'id' 	=> 'chart_all_time',
		);
		
		if (isset($this->request->get['filter_history_range'])) {
			$filter_history_range = $this->request->get['filter_history_range'];
		} else {
			$filter_history_range = 'all_time';
		}

		if (isset($this->request->get['filter_history_option'])) {
			$filter_history_option = $this->request->get['filter_history_option'];
		} else {
			$filter_history_option = 0;
		}

		if (isset($this->request->get['filter_history_supplier'])) {
			$filter_history_supplier = $this->request->get['filter_history_supplier'];
		} else {
			$filter_history_supplier = '';
		}
		
		if (isset($this->request->get['sort_history'])) {
			$sort_history = $this->request->get['sort_history'];
		} else {
			$sort_history = 'psh_id';
		}

		if (isset($this->request->get['order_history'])) {
			$order_history = $this->request->get['order_history'];
		} else {
			$order_history = 'DESC';
		}
				
		if (isset($this->request->get['page_history']) && is_numeric($this->request->get['page_history'])) {
			$page_history = $this->request->get['page_history'];
		} else {
			$page_history = 1;
		}

		$json['histories'] = array();			
				
		$filter_data = array(
			'filter_history_date_start'		=> $filter_history_date_start, 
			'filter_history_date_end'	  	=> $filter_history_date_end, 
			'filter_history_range'	  	  	=> $filter_history_range, 
			'filter_history_option'	  	  	=> $filter_history_option, 
			'filter_history_supplier'	  	=> $filter_history_supplier, 			
			'sort_history'            		=> $sort_history,
			'order_history'           		=> $order_history,
			'start_history'           	  	=> ($page_history - 1) * $this->config->get('config_limit_admin'),
			'limit_history'           	  	=> $this->config->get('config_limit_admin')
		);
		
		$this->load->model('catalog/product');
		
		$prod_history_total = $this->model_catalog_product->getProductHistoryTotal($this->request->get['product_id'], $filter_data);
			
		$prod_history = $this->model_catalog_product->getProductHistory($this->request->get['product_id'], $filter_data);
		
		$data['option_histories'] = $this->model_catalog_product->getProductOptionsHistory($this->request->get['product_id'], $filter_data);

		$data['supplier_histories'] = $this->model_catalog_product->getProductSuppliersHistory($this->request->get['product_id'], $filter_data);
				
		foreach ($prod_history as $history) {
		
		$costing_method = strtoupper($history['costing_method']) == 1 ? 'AVCO' : 'FXCO';
		
		$profit = number_format(($history['price'] - $history['cost']), 4);
		$profit_margin = $history['price'] > 0 ? number_format(((($history['price'] - $history['cost']) / $history['price'])*100), 2) . '%' : '0%';
		$profit_markup = $history['cost'] > 0 ? number_format(((($history['price'] - $history['cost']) / $history['cost'])*100), 2) . '%' : '0%';
			
		if ($filter_history_option == 0) {	
			$json['histories'][] = array(
				'product_stock_history_id'     		 => $history['product_stock_history_id'],
				'nooption'     			=> true,
				'hproduct_id'     		=> $history['product_id'],
				'hdate_added' 			=> date($this->config->get('adv_date_format') == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($history['date_added'])) . ' ' . date($this->config->get('adv_hour_format') == '24' ? 'H:i:s' : 'g:i:s A', strtotime($history['date_added'])),
				'comment'     			=> $history['comment'],
				'hsupplier'     		=> $history['supplier'],
				'hrestock_quantity'     => $history['restock_quantity'],	
				'hstock_quantity'    	=> $history['stock_quantity'],
				'hcosting_method'    	=> $costing_method,
				'hrestock_cost'    		=> $history['restock_cost'],
				'hcost'    				=> $history['cost'],						
				'hprice'    			=> $history['price'],
				'hprofit'    			=> $profit,
				'hprofit_margin'    	=> $profit_margin,
				'hprofit_markup'    	=> $profit_markup
			);
		} else {
			$json['histories'][] = array(
				'product_option_stock_history_id'     => $history['product_option_stock_history_id'],
				'nooption'     			=> false,
				'hproduct_id'     		=> $history['product_id'],
				'hdate_added' 			=> date($this->config->get('adv_date_format') == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($history['date_added'])) . ' ' . date($this->config->get('adv_hour_format') == '24' ? 'H:i:s' : 'g:i:s A', strtotime($history['date_added'])),
				'comment'     			=> $history['comment'],
				'hsupplier'     		=> $history['supplier'],
				'hrestock_quantity'     => $history['restock_quantity'],	
				'hstock_quantity'    	=> $history['stock_quantity'],
				'hcosting_method'    	=> $costing_method,
				'hrestock_cost'    		=> $history['restock_cost'],
				'hcost'    				=> $history['cost'],						
				'hprice'    			=> $history['price'],
				'hprofit'    			=> $profit,
				'hprofit_margin'    	=> $profit_margin,
				'hprofit_markup'    	=> $profit_markup				
			);		
		}
		}

		$pagination_history = new Pagination();
		$pagination_history->total = $prod_history_total;
		$pagination_history->page = $page_history;
		$pagination_history->limit = $this->config->get('config_limit_admin');
		$pagination_history->url = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . '&page_history={page}', true);
			
		$json['pagination_history'] = $pagination_history->render();
			
		$json['results_history'] = sprintf($this->language->get('text_pagination'), ($prod_history_total) ? (($page_history - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page_history - 1) * $this->config->get('config_limit_admin')) > ($prod_history_total - $this->config->get('config_limit_admin'))) ? $prod_history_total : ((($page_history - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $prod_history_total, ceil($prod_history_total / $this->config->get('config_limit_admin')));
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	  }
	}
	
	public function filter_sale() {
	  $data['product_id'] = isset($this->request->get['product_id']);
	  
	  if (isset($this->request->get['product_id'])) {
	    $data['product_id'] = isset($this->request->get['product_id']);
	    $data['product_ids'] = $this->request->get['product_id'];
		
		$json = array();

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['filter_sale_date_start'])) {
			$filter_sale_date_start = $this->request->get['filter_sale_date_start'];
		} else {
			$filter_sale_date_start = '';
		}

		if (isset($this->request->get['filter_sale_date_end'])) {
			$filter_sale_date_end = $this->request->get['filter_sale_date_end'];
		} else {
			$filter_sale_date_end = '';
		}
		
		$data['ranges_sale'] = array();
		
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcustom'),
			'value' => 'custom',
			'style' => 'color:#666',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_today'),
			'value' => 'today',
			'style' => 'color:#090',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_yesterday'),
			'value' => 'yesterday',
			'style' => 'color:#090',
		);		
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hweek'),
			'value' => 'week',
			'style' => 'color:#090',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hmonth'),
			'value' => 'month',
			'style' => 'color:#090',
		);					
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hquarter'),
			'value' => 'quarter',
			'style' => 'color:#090',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hyear'),
			'value' => 'year',
			'style' => 'color:#090',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcurrent_week'),
			'value' => 'current_week',
			'style' => 'color:#06C',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcurrent_month'),
			'value' => 'current_month',
			'style' => 'color:#06C',
		);	
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcurrent_quarter'),
			'value' => 'current_quarter',
			'style' => 'color:#06C',
		);			
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcurrent_year'),
			'value' => 'current_year',
			'style' => 'color:#06C',
		);			
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hlast_week'),
			'value' => 'last_week',
			'style' => 'color:#F90',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hlast_month'),
			'value' => 'last_month',
			'style' => 'color:#F90',
		);	
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hlast_quarter'),
			'value' => 'last_quarter',
			'style' => 'color:#F90',
		);			
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hlast_year'),
			'value' => 'last_year',
			'style' => 'color:#F90',
		);			
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hall_time'),
			'value' => 'all_time',
			'style' => 'color:#F00',
		);
		
		if (isset($this->request->get['filter_sale_range'])) {
			$filter_sale_range = $this->request->get['filter_sale_range'];
		} else {
			$filter_sale_range = 'current_year';
		}

		if (isset($this->request->get['filter_sale_order_status'])) {
			$filter_sale_order_status = explode('_', $this->request->get['filter_sale_order_status']);
		} else {
			$filter_sale_order_status = 0;
		}

		if (isset($this->request->get['filter_sale_option'])) {
			$filter_sale_option = explode('_', $this->request->get['filter_sale_option']);
		} else {
			$filter_sale_option = 0;
		}
		
		if (isset($this->request->get['sort_sale'])) {
			$sort_sale = $this->request->get['sort_sale'];
		} else {
			$sort_sale = 'product_date_added';
		}

		if (isset($this->request->get['order_sale'])) {
			$order_sale = $this->request->get['order_sale'];
		} else {
			$order_sale = 'DESC';
		}
				
		if (isset($this->request->get['page_sale']) && is_numeric($this->request->get['page_sale'])) {
			$page_sale = $this->request->get['page_sale'];
		} else {
			$page_sale = 1;
		}
				
		$json['sales'] = array();
		
		$filter_data = array(
			'filter_sale_date_start'	  	=> $filter_sale_date_start, 
			'filter_sale_date_end'	  		=> $filter_sale_date_end, 
			'filter_sale_range'	  	  		=> $filter_sale_range, 
			'filter_sale_order_status'	  	=> $filter_sale_order_status,
			'filter_sale_option'	  		=> $filter_sale_option,
			'sort_sale'            			=> $sort_sale,
			'order_sale'           			=> $order_sale,
			'start_sale'           	  		=> ($page_sale - 1) * $this->config->get('config_limit_admin'),
			'limit_sale'           	  		=> $this->config->get('config_limit_admin')			
		);
		
		$this->load->model('catalog/product');
		
		$data['order_statuses'] = $this->model_catalog_product->getOrderStatuses(); 
		$data['order_options'] = $this->model_catalog_product->getOrderOptions($this->request->get['product_id'], $filter_data); 
		
		$prod_sale_total = $this->model_catalog_product->getProductSalesTotal($this->request->get['product_id'], $filter_data);
		
		$product_sales = $this->model_catalog_product->getProductSales($this->request->get['product_id'], $filter_data);
		
		foreach ($product_sales as $sale) {
		
			$product_profit_margin = $sale['product_revenue'] > 0 ? number_format(((($sale['product_revenue'] - $sale['product_cost']) / $sale['product_revenue'])*100), 2) . '%' : '0%';
			$product_profit_markup = $sale['product_cost'] > 0 ? number_format(((($sale['product_revenue'] - $sale['product_cost']) / $sale['product_cost'])*100), 2) . '%' : '0%';
			$product_profit_margin_total = $sale['product_revenue_total'] > 0 ? number_format(((($sale['product_revenue_total'] - $sale['product_cost_total']) / $sale['product_revenue_total'])*100), 2) . '%' : '0%';
			$product_profit_markup_total = $sale['product_cost_total'] > 0 ? number_format(((($sale['product_revenue_total'] - $sale['product_cost_total']) / $sale['product_cost_total'])*100), 2) . '%' : '0%';
				
			$json['sales'][] = array(
				'product_order_product_id'    	=> $sale['order_product_id'],
				'product_order_id'    			=> $sale['product_order_id'],
				'product_order_id_url' 			=> $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $sale['order_id'], true),
				'product_date_added' 			=> date($this->config->get('adv_date_format') == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($sale['date_added'])),
				'product_name'    				=> $sale['product_name'],
				'product_option'    			=> html_entity_decode($sale['product_option']),	
				'product_sold'    				=> $sale['product_sold'],
				'product_total_excl_vat'  		=> $this->currency->format($sale['product_total_excl_vat'], $this->config->get('config_currency')),
				'product_tax'    				=> $this->currency->format($sale['product_tax'], $this->config->get('config_currency')),
				'product_total_incl_vat'  		=> $this->currency->format($sale['product_total_incl_vat'], $this->config->get('config_currency')),
				'product_revenue'    			=> $this->currency->format($sale['product_revenue'], $this->config->get('config_currency')),
				'product_cost'    				=> $this->currency->format($sale['product_cost'], $this->config->get('config_currency')),
				'product_profit'    			=> $this->currency->format($sale['product_profit'], $this->config->get('config_currency')),
				'product_profit_raw'    		=> $sale['product_profit'],
				'product_profit_margin'    		=> $product_profit_margin,
				'product_profit_markup'    		=> $product_profit_markup,
				'product_sold_total'    		=> $sale['product_sold_total'],
				'product_total_excl_vat_total'	=> $this->currency->format($sale['product_total_excl_vat_total'], $this->config->get('config_currency')),
				'product_tax_total'    			=> $this->currency->format($sale['product_tax_total'], $this->config->get('config_currency')),
				'product_total_incl_vat_total'	=> $this->currency->format($sale['product_total_incl_vat_total'], $this->config->get('config_currency')),
				'product_revenue_total'    		=> $this->currency->format($sale['product_revenue_total'], $this->config->get('config_currency')),
				'product_cost_total'    		=> $this->currency->format($sale['product_cost_total'], $this->config->get('config_currency')),
				'product_profit_total'    		=> $this->currency->format($sale['product_profit_total'], $this->config->get('config_currency')),
				'product_profit_raw_total'    	=> $sale['product_profit_total'],
				'product_profit_margin_total'   => $product_profit_margin_total,
				'product_profit_markup_total'   => $product_profit_markup_total
			);
		}

		$pagination_sale = new Pagination();
		$pagination_sale->total = $prod_sale_total;
		$pagination_sale->page = $page_sale;
		$pagination_sale->limit = $this->config->get('config_limit_admin');
		$pagination_sale->url = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . '&page_sale={page}', true);
			
		$json['pagination_sale'] = $pagination_sale->render();
			
		$json['results_sale'] = sprintf($this->language->get('text_pagination'), ($prod_sale_total) ? (($page_sale - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page_sale - 1) * $this->config->get('config_limit_admin')) > ($prod_sale_total - $this->config->get('config_limit_admin'))) ? $prod_sale_total : ((($page_sale - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $prod_sale_total, ceil($prod_sale_total / $this->config->get('config_limit_admin')));
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	  }
	}	
            
	protected function getForm() {

	$data['prm_access_permission'] = $this->user->hasPermission('access', 'extension/module/adv_profit_module');
	$data['modify_permission'] = $this->user->hasPermission('modify', 'catalog/product');
	
	if (!$this->IsInstalled()) {		
		$this->session->data['error_prm'] = $this->language->get('error_installed');
		$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));		
	}	
				
	$data['product_id'] = isset($this->request->get['product_id']);
	
	if (isset($this->request->get['product_id'])) {
		$data['product_id'] = isset($this->request->get['product_id']);
		$data['product_ids'] = $this->request->get['product_id'];

		$data['user_token'] = $this->session->data['user_token'];
			
		if (isset($this->request->get['filter_history_date_start'])) {
			$filter_history_date_start = $this->request->get['filter_history_date_start'];
		} else {
			$filter_history_date_start = '';
		}

		if (isset($this->request->get['filter_history_date_end'])) {
			$filter_history_date_end = $this->request->get['filter_history_date_end'];
		} else {
			$filter_history_date_end = '';
		}
		
		$data['ranges_history'] = array();
		
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcustom'),
			'value' => 'custom',
			'style' => 'color:#666',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hweek'),
			'value' => 'week',
			'style' => 'color:#090',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hmonth'),
			'value' => 'month',
			'style' => 'color:#090',
			'id' 	=> '',
		);					
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hquarter'),
			'value' => 'quarter',
			'style' => 'color:#090',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hyear'),
			'value' => 'year',
			'style' => 'color:#090',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcurrent_week'),
			'value' => 'current_week',
			'style' => 'color:#06C',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcurrent_month'),
			'value' => 'current_month',
			'style' => 'color:#06C',
			'id' 	=> '',
		);	
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcurrent_quarter'),
			'value' => 'current_quarter',
			'style' => 'color:#06C',
			'id' 	=> '',
		);			
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hcurrent_year'),
			'value' => 'current_year',
			'style' => 'color:#06C',
			'id' 	=> '',
		);			
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hlast_week'),
			'value' => 'last_week',
			'style' => 'color:#F90',
			'id' 	=> '',
		);
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hlast_month'),
			'value' => 'last_month',
			'style' => 'color:#F90',
			'id' 	=> '',
		);	
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hlast_quarter'),
			'value' => 'last_quarter',
			'style' => 'color:#F90',
			'id' 	=> '',
		);			
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hlast_year'),
			'value' => 'last_year',
			'style' => 'color:#F90',
			'id' 	=> '',
		);			
		$data['ranges_history'][] = array(
			'text'  => $this->language->get('stat_hall_time'),
			'value' => 'all_time',
			'style' => 'color:#F00',
			'id' 	=> 'chart_all_time',
		);
		
		if (isset($this->request->get['filter_history_range'])) {
			$filter_history_range = $this->request->get['filter_history_range'];
		} else {
			$filter_history_range = 'all_time';
		}

		if (isset($this->request->get['filter_history_option'])) {
			$filter_history_option = $this->request->get['filter_history_option'];
		} else {
			$filter_history_option = 0;
		}

		if (isset($this->request->get['filter_history_supplier'])) {
			$filter_history_supplier = $this->request->get['filter_history_supplier'];
		} else {
			$filter_history_supplier = '';
		}
		
		if (isset($this->request->get['sort_history'])) {
			$sort_history = $this->request->get['sort_history'];
		} else {
			$sort_history = 'psh_id';
		}

		if (isset($this->request->get['order_history'])) {
			$order_history = $this->request->get['order_history'];
		} else {
			$order_history = 'DESC';
		}
				
		if (isset($this->request->get['page_history']) && is_numeric($this->request->get['page_history'])) {
			$page_history = $this->request->get['page_history'];
		} else {
			$page_history = 1;
		}

		$data['sort_history'] = $sort_history;
		$data['order_history'] = $order_history;
		$data['page_history'] = $page_history;
				
		$this->load->model('catalog/product');

		$data['histories'] = array();
		
		$filter_data = array(
			'filter_history_date_start'	  		=> $filter_history_date_start, 
			'filter_history_date_end'	  		=> $filter_history_date_end, 
			'filter_history_range'	  	  		=> $filter_history_range, 
			'filter_history_option'	  	  		=> $filter_history_option,
			'filter_history_supplier'	  	  	=> $filter_history_supplier,			
			'sort_history'            			=> $sort_history,
			'order_history'           			=> $order_history,
			'start_history'           	  		=> ($page_history - 1) * $this->config->get('config_limit_admin'),
			'limit_history'           	  		=> $this->config->get('config_limit_admin')			
		);

		$prod_history_total = $this->model_catalog_product->getProductHistoryTotal($this->request->get['product_id'], $filter_data);
		
		$prod_history = $this->model_catalog_product->getProductHistory($this->request->get['product_id'], $filter_data);

		$data['option_histories'] = $this->model_catalog_product->getProductOptionsHistory($this->request->get['product_id'], $filter_data);

		$data['supplier_histories'] = $this->model_catalog_product->getProductSuppliersHistory($this->request->get['product_id'], $filter_data);
							
		foreach ($prod_history as $history) {
		
		$costing_method = strtoupper($history['costing_method']) == 1 ? 'AVCO' : 'FXCO';

		$profit = number_format(($history['price'] - $history['cost']), 4);
		$profit_margin = $history['price'] > 0 ? number_format(((($history['price'] - $history['cost']) / $history['price'])*100), 2) . '%' : '0%';
		$profit_markup = $history['cost'] > 0 ? number_format(((($history['price'] - $history['cost']) / $history['cost'])*100), 2) . '%' : '0%';
		
		if ($filter_history_option == 0) {	
			$data['histories'][] = array(
				'product_stock_history_id'     		 => $history['product_stock_history_id'],
				'nooption'     			=> true,
				'hproduct_id'     		=> $history['product_id'],
				'hdate_added' 			=> date($this->config->get('adv_date_format') == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($history['date_added'])) . ' ' . date($this->config->get('adv_hour_format') == '24' ? 'H:i:s' : 'g:i:s A', strtotime($history['date_added'])),
				'comment'     			=> $history['comment'],
				'hsupplier'     		=> $history['supplier'],
				'hrestock_quantity'     => $history['restock_quantity'],	
				'hstock_quantity'    	=> $history['stock_quantity'],
				'hcosting_method'    	=> $costing_method,
				'hrestock_cost'    		=> $history['restock_cost'],
				'hcost'    				=> $history['cost'],						
				'hprice'    			=> $history['price'],
				'hprofit'    			=> $profit,
				'hprofit_margin'    	=> $profit_margin,
				'hprofit_markup'    	=> $profit_markup			
			);
		} else {
			$data['histories'][] = array(
				'product_option_stock_history_id'     => $history['product_option_stock_history_id'],
				'nooption'     			=> false,
				'hproduct_id'     		=> $history['product_id'],
				'hdate_added' 			=> date($this->config->get('adv_date_format') == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($history['date_added'])) . ' ' . date($this->config->get('adv_hour_format') == '24' ? 'H:i:s' : 'g:i:s A', strtotime($history['date_added'])),
				'comment'     			=> $history['comment'],
				'hsupplier'     		=> $history['supplier'],
				'hrestock_quantity'     => $history['restock_quantity'],	
				'hstock_quantity'    	=> $history['stock_quantity'],
				'hcosting_method'    	=> $costing_method,
				'hrestock_cost'    		=> $history['restock_cost'],
				'hcost'    				=> $history['cost'],						
				'hprice'    			=> $history['price'],
				'hprofit'    			=> $profit,
				'hprofit_margin'    	=> $profit_margin,
				'hprofit_markup'    	=> $profit_markup			
			);		
		}
		}

		$data['ghistories'] = array();
		
		$prod_ghistory = $this->model_catalog_product->getProductChartHistory($this->request->get['product_id'], $data);
		
		foreach ($prod_ghistory as $ghistory) {

			if ($ghistory['cost'] >= 0) {
				$gprofit = ($ghistory['price'] - $ghistory['cost']);
			} else {
				$gprofit = '0';
			}
					
			$data['ghistories'][] = array(
				'ghproduct_id'     		=> $ghistory['product_id'],
				'ghdate_added' 			=> date($this->config->get('adv_date_format') == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($ghistory['date_added'])) . ' ' . date($this->config->get('adv_hour_format') == '24' ? 'H:i:s' : 'g:i:s A', strtotime($ghistory['date_added'])),				
				'ghstock_quantity'    	=> $ghistory['stock_quantity'],
				'ghcost'    			=> $ghistory['cost'],						
				'ghprice'    			=> $ghistory['price'],
				'ghprofit'    			=> $gprofit
			);
		}
		
		$url = '';

		if (isset($this->request->get['filter_history_date_start'])) {
			$url .= '&filter_history_date_start=' . $this->request->get['filter_history_date_start'];
		}
		
		if (isset($this->request->get['filter_history_date_end'])) {
			$url .= '&filter_history_date_end=' . $this->request->get['filter_history_date_end'];
		}
		
		if (isset($this->request->get['filter_history_range'])) {
			$url .= '&filter_history_range=' . $this->request->get['filter_history_range'];
		}
		
		if (isset($this->request->get['filter_history_option'])) {
			$url .= '&filter_history_option=' . $this->request->get['filter_history_option'];
		}

		if (isset($this->request->get['filter_history_supplier'])) {
			$url .= '&filter_history_supplier=' . $this->request->get['filter_history_supplier'];
		}
										
		if ($order_history == 'ASC') {
			$url .= '&order_history=DESC';
		} else {
			$url .= '&order_history=ASC';
		}

		if (isset($this->request->get['page_history']) && is_numeric($this->request->get['page_history'])) {
			$url .= '&page_history=' . $this->request->get['page_history'];
		}
					
		$data['sort_history_date_added'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=psh_id' . $url, true);
		$data['sort_history_comment'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=psh.comment' . $url, true);
		$data['sort_history_supplier'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=supplier' . $url, true);
		$data['sort_history_costing_method'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=psh.costing_method' . $url, true);		
		$data['sort_history_restock_quantity'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=psh.restock_quantity' . $url, true);
		$data['sort_history_stock_quantity'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=psh.stock_quantity' . $url, true);
		$data['sort_history_restock_cost'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=psh.restock_cost' . $url, true);
		$data['sort_history_cost'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=psh.cost' . $url, true);
		$data['sort_history_price'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=psh.price' . $url, true);
		$data['sort_history_profit'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=profit' . $url, true);
		$data['sort_history_profit_margin'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=profit_margin' . $url, true);
		$data['sort_history_profit_markup'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_history=profit_markup' . $url, true);

		$pagination_history = new Pagination();
		$pagination_history->total = $prod_history_total;
		$pagination_history->page = $page_history;
		$pagination_history->limit = $this->config->get('config_limit_admin');
		$pagination_history->url = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . '&page_history={page}', true);
			
		$data['pagination_history'] = $pagination_history->render();
			
		$data['results_history'] = sprintf($this->language->get('text_pagination'), ($prod_history_total) ? (($page_history - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page_history - 1) * $this->config->get('config_limit_admin')) > ($prod_history_total - $this->config->get('config_limit_admin'))) ? $prod_history_total : ((($page_history - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $prod_history_total, ceil($prod_history_total / $this->config->get('config_limit_admin')));
							
		$data['filter_history_date_start'] = $filter_history_date_start; 
		$data['filter_history_date_end'] = $filter_history_date_end; 		
		$data['filter_history_range'] = $filter_history_range; 
		$data['filter_history_option'] = $filter_history_option; 	
		$data['filter_history_supplier'] = $filter_history_supplier; 
				
		$data['sort_history'] = $sort_history;
		$data['order_history'] = $order_history;

		file_put_contents('prm_json_array.txt', json_encode($data['ghistories']));

	   	$this->document->addScript('view/javascript/jquery/jquery.tmpl.min.js');
		$this->document->addScript('view/javascript/bootstrap/js/bootstrap-multiselect.js');
	   	$this->document->addStyle('view/javascript/bootstrap/css/bootstrap-multiselect.css');
		$this->document->addScript('view/javascript/bootstrap/js/bootstrap-select.min.js');
		$this->document->addStyle('view/javascript/bootstrap/css/bootstrap-select.css');
		
		if (isset($this->request->get['filter_sale_date_start'])) {
			$filter_sale_date_start = $this->request->get['filter_sale_date_start'];
		} else {
			$filter_sale_date_start = '';
		}

		if (isset($this->request->get['filter_sale_date_end'])) {
			$filter_sale_date_end = $this->request->get['filter_sale_date_end'];
		} else {
			$filter_sale_date_end = '';
		}
		
		$data['ranges_sale'] = array();
		
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcustom'),
			'value' => 'custom',
			'style' => 'color:#666',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_today'),
			'value' => 'today',
			'style' => 'color:#090',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_yesterday'),
			'value' => 'yesterday',
			'style' => 'color:#090',
		);		
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hweek'),
			'value' => 'week',
			'style' => 'color:#090',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hmonth'),
			'value' => 'month',
			'style' => 'color:#090',
		);					
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hquarter'),
			'value' => 'quarter',
			'style' => 'color:#090',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hyear'),
			'value' => 'year',
			'style' => 'color:#090',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcurrent_week'),
			'value' => 'current_week',
			'style' => 'color:#06C',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcurrent_month'),
			'value' => 'current_month',
			'style' => 'color:#06C',
		);	
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcurrent_quarter'),
			'value' => 'current_quarter',
			'style' => 'color:#06C',
		);			
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hcurrent_year'),
			'value' => 'current_year',
			'style' => 'color:#06C',
		);			
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hlast_week'),
			'value' => 'last_week',
			'style' => 'color:#F90',
		);
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hlast_month'),
			'value' => 'last_month',
			'style' => 'color:#F90',
		);	
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hlast_quarter'),
			'value' => 'last_quarter',
			'style' => 'color:#F90',
		);			
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hlast_year'),
			'value' => 'last_year',
			'style' => 'color:#F90',
		);			
		$data['ranges_sale'][] = array(
			'text'  => $this->language->get('stat_hall_time'),
			'value' => 'all_time',
			'style' => 'color:#F00',
		);
		
		if (isset($this->request->get['filter_sale_range'])) {
			$filter_sale_range = $this->request->get['filter_sale_range'];
		} else {
			$filter_sale_range = 'current_year';
		}

		if (isset($this->request->get['filter_sale_order_status'])) {
			$filter_sale_order_status = explode('_', $this->request->get['filter_sale_order_status']);
		} else {
			$filter_sale_order_status = 0;
		}

		if (isset($this->request->get['filter_sale_option'])) {
			$filter_sale_option = explode('_', $this->request->get['filter_sale_option']);
		} else {
			$filter_sale_option = 0;
		}
		
		if (isset($this->request->get['sort_sale'])) {
			$sort_sale = $this->request->get['sort_sale'];
		} else {
			$sort_sale = 'product_date_added';
		}

		if (isset($this->request->get['order_sale'])) {
			$order_sale = $this->request->get['order_sale'];
		} else {
			$order_sale = 'DESC';
		}
				
		if (isset($this->request->get['page_sale']) && is_numeric($this->request->get['page_sale'])) {
			$page_sale = $this->request->get['page_sale'];
		} else {
			$page_sale = 1;
		}

		$data['sort_sale'] = $sort_sale;
		$data['order_sale'] = $order_sale;
		$data['page_sale'] = $page_sale;
										
		$this->load->model('catalog/product');
				
		$data['sales'] = array();
				
		$filter_data = array(
			'filter_sale_date_start'	  	=> $filter_sale_date_start, 
			'filter_sale_date_end'	  		=> $filter_sale_date_end, 
			'filter_sale_range'	  	  		=> $filter_sale_range, 
			'filter_sale_order_status'	  	=> $filter_sale_order_status,
			'filter_sale_option'	  		=> $filter_sale_option,
			'sort_sale'            			=> $sort_sale,
			'order_sale'           			=> $order_sale,
			'start_sale'           	  		=> ($page_sale - 1) * $this->config->get('config_limit_admin'),
			'limit_sale'           	  		=> $this->config->get('config_limit_admin')	
		);
		
		$data['order_statuses'] = $this->model_catalog_product->getOrderStatuses();
		$data['order_options'] = $this->model_catalog_product->getOrderOptions($this->request->get['product_id'], $filter_data);

		$prod_sale_total = $this->model_catalog_product->getProductSalesTotal($this->request->get['product_id'], $filter_data);
		
		$product_sales = $this->model_catalog_product->getProductSales($this->request->get['product_id'], $filter_data);

		foreach ($product_sales as $sale) {
		
			$product_profit_margin = $sale['product_revenue'] > 0 ? number_format(((($sale['product_revenue'] - $sale['product_cost']) / $sale['product_revenue'])*100), 2) . '%' : '0%';
			$product_profit_markup = $sale['product_cost'] > 0 ? number_format(((($sale['product_revenue'] - $sale['product_cost']) / $sale['product_cost'])*100), 2) . '%' : '0%';
			$product_profit_margin_total = $sale['product_revenue_total'] > 0 ? number_format(((($sale['product_revenue_total'] - $sale['product_cost_total']) / $sale['product_revenue_total'])*100), 2) . '%' : '0%';
			$product_profit_markup_total = $sale['product_cost_total'] > 0 ? number_format(((($sale['product_revenue_total'] - $sale['product_cost_total']) / $sale['product_cost_total'])*100), 2) . '%' : '0%';
				
			$data['sales'][] = array(
				'product_order_product_id'    	=> $sale['order_product_id'],
				'product_order_id'    			=> $sale['product_order_id'],
				'product_order_id_url' 			=> $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $sale['order_id'], true),
				'product_date_added' 			=> date($this->config->get('adv_date_format') == 'DDMMYYYY' ? 'd/m/Y' : 'm/d/Y', strtotime($sale['date_added'])),
				'product_name'    				=> $sale['product_name'],
				'product_option'    			=> html_entity_decode($sale['product_option']),	
				'product_sold'    				=> $sale['product_sold'],
				'product_total_excl_vat'  		=> $this->currency->format($sale['product_total_excl_vat'], $this->config->get('config_currency')),
				'product_tax'    				=> $this->currency->format($sale['product_tax'], $this->config->get('config_currency')),
				'product_total_incl_vat'  		=> $this->currency->format($sale['product_total_incl_vat'], $this->config->get('config_currency')),
				'product_revenue'    			=> $this->currency->format($sale['product_revenue'], $this->config->get('config_currency')),
				'product_cost'    				=> $this->currency->format($sale['product_cost'], $this->config->get('config_currency')),
				'product_profit'    			=> $this->currency->format($sale['product_profit'], $this->config->get('config_currency')),
				'product_profit_raw'    		=> $sale['product_profit'],
				'product_profit_margin'		    => $product_profit_margin,
				'product_profit_markup'    		=> $product_profit_markup,
				'product_sold_total'    		=> $sale['product_sold_total'],
				'product_total_excl_vat_total'	=> $this->currency->format($sale['product_total_excl_vat_total'], $this->config->get('config_currency')),
				'product_tax_total'    			=> $this->currency->format($sale['product_tax_total'], $this->config->get('config_currency')),
				'product_total_incl_vat_total'	=> $this->currency->format($sale['product_total_incl_vat_total'], $this->config->get('config_currency')),
				'product_revenue_total'    		=> $this->currency->format($sale['product_revenue_total'], $this->config->get('config_currency')),
				'product_cost_total'    		=> $this->currency->format($sale['product_cost_total'], $this->config->get('config_currency')),
				'product_profit_total'    		=> $this->currency->format($sale['product_profit_total'], $this->config->get('config_currency')),
				'product_profit_raw_total'    	=> $sale['product_profit_total'],
				'product_profit_margin_total'   => $product_profit_margin_total,
				'product_profit_markup_total'   => $product_profit_markup_total
			);
		}
		
		$url = '';

		if (isset($this->request->get['filter_sale_date_start'])) {
			$url .= '&filter_sale_date_start=' . $this->request->get['filter_sale_date_start'];
		}
		
		if (isset($this->request->get['filter_sale_date_end'])) {
			$url .= '&filter_sale_date_end=' . $this->request->get['filter_sale_date_end'];
		}
		
		if (isset($this->request->get['filter_sale_range'])) {
			$url .= '&filter_sale_range=' . $this->request->get['filter_sale_range'];
		}
		
		if (isset($this->request->get['filter_sale_order_status'])) {
			$url .= '&filter_sale_order_status=' . $this->request->get['filter_sale_order_status'];
		}

		if (isset($this->request->get['filter_sale_option'])) {
			$url .= '&filter_sale_option=' . $this->request->get['filter_sale_option'];
		}
										
		if ($order_sale == 'ASC') {
			$url .= '&order_sale=DESC';
		} else {
			$url .= '&order_sale=ASC';
		}

		if (isset($this->request->get['page_sale']) && is_numeric($this->request->get['page_sale'])) {
			$url .= '&page_sale=' . $this->request->get['page_sale'];
		}
								
		$data['sort_sale_order_id'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_date_added' . $url, true);
		$data['sort_sale_date_added'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_date_added' . $url, true);
		$data['sort_sale_name'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_option' . $url, true);
		$data['sort_sale_quantity'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_sold' . $url, true);		
		$data['sort_sale_total_excl_tax'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_total_excl_vat' . $url, true);
		$data['sort_sale_tax'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_tax' . $url, true);		
		$data['sort_sale_total_incl_tax'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_total_incl_vat' . $url, true);
		$data['sort_sale_revenue'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_revenue' . $url, true);
		$data['sort_sale_cost'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_cost' . $url, true);		
		$data['sort_sale_profit'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_profit' . $url, true);
		$data['sort_sale_profit_margin'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_margin' . $url, true);
		$data['sort_sale_profit_markup'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&sort_sale=product_markup' . $url, true);

		$pagination_sale = new Pagination();
		$pagination_sale->total = $prod_sale_total;
		$pagination_sale->page = $page_sale;
		$pagination_sale->limit = $this->config->get('config_limit_admin');
		$pagination_sale->text = $this->language->get('text_pagination');
		$pagination_sale->url = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . '&page_sale={page}', true);
			
		$data['pagination_sale'] = $pagination_sale->render();

		$data['results_sale'] = sprintf($this->language->get('text_pagination'), ($prod_sale_total) ? (($page_sale - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page_sale - 1) * $this->config->get('config_limit_admin')) > ($prod_sale_total - $this->config->get('config_limit_admin'))) ? $prod_sale_total : ((($page_sale - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $prod_sale_total, ceil($prod_sale_total / $this->config->get('config_limit_admin')));
										
		$data['filter_sale_date_start'] = $filter_sale_date_start; 
		$data['filter_sale_date_end'] = $filter_sale_date_end; 		
		$data['filter_sale_range'] = $filter_sale_range; 
		$data['filter_sale_order_status'] = $filter_sale_order_status;
		$data['filter_sale_option'] = $filter_sale_option;		
		
		$data['sort_sale'] = $sort_sale;
		$data['order_sale'] = $order_sale;
	}
            
		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}

		if (isset($this->error['model'])) {
			$data['error_model'] = $this->error['model'];
		} else {
			$data['error_model'] = '';
		}


		$data['button_apply'] = $this->language->get('button_apply');
		$data['text_result_success'] = $this->language->get('text_result_success');
			
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
			
		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_model'])) {
			$url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_price'])) {
			$url .= '&filter_price=' . $this->request->get['filter_price'];
		}

		if (isset($this->request->get['filter_cost'])) {
			$url .= '&filter_cost=' . $this->request->get['filter_cost'];
		}	
					
		if (isset($this->request->get['filter_category_id'])) {
			$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
		}	
		
		if (isset($this->request->get['filter_manufacturer_id'])) {
			$url .= '&filter_manufacturer_id=' . $this->request->get['filter_manufacturer_id'];
		}	
							
		if (isset($this->request->get['filter_supplier_id'])) {
			$url .= '&filter_supplier_id=' . $this->request->get['filter_supplier_id'];
		}		
            

		if (isset($this->request->get['filter_quantity'])) {
			$url .= '&filter_quantity=' . $this->request->get['filter_quantity'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['product_id'])) {
			$data['action'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true);

        /**
         * Marketplace Code Starts Here
         */
        if ($this->config->get('module_marketplace_status') && isset($this->request->get['mpcheck'])) {
            if (!isset($this->request->get['product_id'])) {
                $data['action'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url . '&mpcheck=1', true);
            } else {
                $data['action'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&mpcheck=1&product_id=' . $this->request->get['product_id'] . $url, true);
            }

            $data['cancel'] = $this->url->link('customerpartner/product', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            if (!isset($this->request->get['product_id'])) {
                $data['action'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
            } else {
                $data['action'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . $url, true);
            }

            $data['cancel'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true);
        }
        if ($this->config->get('module_marketplace_status') && isset($this->request->get['topsearch'])) {
            if (!isset($this->request->get['product_id'])) {
                $data['action'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url . '&mpcheck=1', true);
            } else {
                $data['action'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&mpcheck=1&product_id=' . $this->request->get['product_id'] . $url, true);
            }
            $data['cancel'] = $this->url->link('customerpartner/topsearch', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            if (!isset($this->request->get['product_id'])) {
                $data['action'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
            } else {
                $data['action'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . $url, true);
            }
            $data['cancel'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true);
        }
         /**
         * Marketplace Code Ends Here
         */
      

		if (isset($this->request->get['product_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['product_description'])) {
			$data['product_description'] = $this->request->post['product_description'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_description'] = $this->model_catalog_product->getProductDescriptions($this->request->get['product_id']);
		} else {
			$data['product_description'] = array();
		}

		if (isset($this->request->post['model'])) {
			$data['model'] = $this->request->post['model'];
		} elseif (!empty($product_info)) {
			$data['model'] = $product_info['model'];
		} else {
			$data['model'] = '';
		}

		if (isset($this->request->post['sku'])) {
			$data['sku'] = $this->request->post['sku'];
		} elseif (!empty($product_info)) {
			$data['sku'] = $product_info['sku'];
		} else {
			$data['sku'] = '';
		}

		if (isset($this->request->post['upc'])) {
			$data['upc'] = $this->request->post['upc'];
		} elseif (!empty($product_info)) {
			$data['upc'] = $product_info['upc'];
		} else {
			$data['upc'] = '';
		}

		if (isset($this->request->post['ean'])) {
			$data['ean'] = $this->request->post['ean'];
		} elseif (!empty($product_info)) {
			$data['ean'] = $product_info['ean'];
		} else {
			$data['ean'] = '';
		}

		if (isset($this->request->post['jan'])) {
			$data['jan'] = $this->request->post['jan'];
		} elseif (!empty($product_info)) {
			$data['jan'] = $product_info['jan'];
		} else {
			$data['jan'] = '';
		}

		if (isset($this->request->post['isbn'])) {
			$data['isbn'] = $this->request->post['isbn'];
		} elseif (!empty($product_info)) {
			$data['isbn'] = $product_info['isbn'];
		} else {
			$data['isbn'] = '';
		}

		if (isset($this->request->post['mpn'])) {
			$data['mpn'] = $this->request->post['mpn'];
		} elseif (!empty($product_info)) {
			$data['mpn'] = $product_info['mpn'];
		} else {
			$data['mpn'] = '';
		}

		if (isset($this->request->post['location'])) {
			$data['location'] = $this->request->post['location'];
		} elseif (!empty($product_info)) {
			$data['location'] = $product_info['location'];
		} else {
			$data['location'] = '';
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

		if (isset($this->request->post['product_store'])) {
			$data['product_store'] = $this->request->post['product_store'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_store'] = $this->model_catalog_product->getProductStores($this->request->get['product_id']);
		} else {
			$data['product_store'] = array(0);
		}

		if (isset($this->request->post['shipping'])) {
			$data['shipping'] = $this->request->post['shipping'];
		} elseif (!empty($product_info)) {
			$data['shipping'] = $product_info['shipping'];
		} else {
			$data['shipping'] = 1;
		}

		if (isset($this->request->post['price'])) {
			$data['price'] = $this->request->post['price'];
		} elseif (!empty($product_info)) {
			$data['price'] = $product_info['price'];
		} else {
			$data['price'] = '';
		}

		$this->load->model('catalog/recurring');

		$data['recurrings'] = $this->model_catalog_recurring->getRecurrings();

		if (isset($this->request->post['product_recurrings'])) {
			$data['product_recurrings'] = $this->request->post['product_recurrings'];
		} elseif (!empty($product_info)) {
			$data['product_recurrings'] = $this->model_catalog_product->getRecurrings($product_info['product_id']);
		} else {
			$data['product_recurrings'] = array();
		}


		$data['adv_ext_name'] = $this->language->get('adv_ext_name');
		$data['adv_ext_short_name'] = 'adv_profit_module';
		$data['adv_ext_version'] = $this->language->get('adv_ext_version');	
		$data['adv_ext_url'] = 'http://www.opencart.com/index.php?route=marketplace/extension/info&extension_id=16601';
    	$data['tab_stock_history'] = $this->language->get('tab_stock_history');	
		$data['entry_hrange'] = $this->language->get('entry_hrange');			
		$data['entry_hdate_start'] = $this->language->get('entry_hdate_start');
		$data['entry_hdate_end'] = $this->language->get('entry_hdate_end');
		$data['entry_hoption'] = $this->language->get('entry_hoption');
		$data['text_nooption'] = $this->language->get('text_nooption');			
		$data['entry_hsupplier'] = $this->language->get('entry_hsupplier');
		$data['text_all_hsuppliers'] = $this->language->get('text_all_hsuppliers');			
		$data['button_hfilter'] = $this->language->get('button_hfilter');
		$data['button_hdelete'] = $this->language->get('button_hdelete');
		$data['button_hdownload'] = $this->language->get('button_hdownload');
		$data['column_hdate_added'] = $this->language->get('column_hdate_added');
		$data['column_comment'] = $this->language->get('column_comment');
		$data['help_comment'] = $this->language->get('help_comment');
		$data['column_hsupplier'] = $this->language->get('column_hsupplier');
		$data['column_hcosting_method'] = $this->language->get('column_hcosting_method');		
		$data['help_costing_methods'] = $this->language->get('help_costing_methods');		
		$data['column_hrestock_quantity'] = $this->language->get('column_hrestock_quantity');
		$data['column_hstock_quantity'] = $this->language->get('column_hstock_quantity');		
		$data['column_hrestock_cost'] = $this->language->get('column_hrestock_cost');	
		$data['column_hcost'] = $this->language->get('column_hcost');	
		$data['column_hprice'] = $this->language->get('column_hprice');
		$data['column_hprofit'] = $this->language->get('column_hprofit');		
		$data['text_no_results'] = $this->language->get('text_no_results');
    	$data['entry_gcost'] = $this->language->get('entry_gcost');	
    	$data['entry_gprice'] = $this->language->get('entry_gprice');
		$data['entry_gprofit'] = $this->language->get('entry_gprofit');
		$data['entry_gmargin'] = $this->language->get('entry_gmargin');
		$data['entry_gmarkup'] = $this->language->get('entry_gmarkup');	
		$data['entry_gstock_quantity'] = $this->language->get('entry_gstock_quantity');
		$data['text_confirm_delete_history'] = $this->language->get('text_confirm_delete_history');			
		$data['tab_sales_history'] = $this->language->get('tab_sales_history');	
		$data['entry_sstatus'] = $this->language->get('entry_sstatus');
		$data['entry_soption'] = $this->language->get('entry_soption');
		$data['text_all'] = $this->language->get('text_all');
		$data['text_selected'] = $this->language->get('text_selected');
		$data['text_all_sstatus'] = $this->language->get('text_all_sstatus');
		$data['text_all_soption'] = $this->language->get('text_all_soption');
		$data['column_prod_order_id'] = $this->language->get('column_prod_order_id');		
		$data['column_prod_date_added'] = $this->language->get('column_prod_date_added');
		$data['column_prod_name'] = $this->language->get('column_prod_name');
    	$data['column_prod_sold'] = $this->language->get('column_prod_sold');
		$data['column_prod_total_excl_vat'] = $this->language->get('column_prod_total_excl_vat');
		$data['column_prod_tax'] = $this->language->get('column_prod_tax');
		$data['column_prod_total_incl_vat'] = $this->language->get('column_prod_total_incl_vat');
		$data['column_prod_sales'] = $this->language->get('column_prod_sales');
		$data['column_prod_cost'] = $this->language->get('column_prod_cost');
		$data['column_prod_profit'] = $this->language->get('column_prod_profit');
		$data['column_prod_margin'] = $this->language->get('column_prod_margin');
		$data['column_prod_markup'] = $this->language->get('column_prod_markup');
		$data['column_prod_totals'] = $this->language->get('column_prod_totals');
    	$data['column_supplier'] = $this->language->get('column_supplier');
		$data['adv_price_tax'] = $this->config->get('adv_price_tax');		
		$data['entry_price_tax'] = $this->language->get('entry_price_tax');		
		$data['column_cost'] = $this->language->get('column_cost');	
		$data['column_profit'] = $this->language->get('column_profit');
		$data['column_profit_margin'] = $this->language->get('column_profit_margin');
		$data['column_profit_markup'] = $this->language->get('column_profit_markup');
		$data['entry_costing_method'] = $this->language->get('entry_costing_method');
		$data['entry_costing_method_doc'] = $this->language->get('entry_costing_method_doc');
		$data['help_costing_method'] = $this->language->get('help_costing_method');
		$data['entry_profit'] = $this->language->get('entry_profit');
		$data['help_product_profit'] = $this->language->get('help_product_profit');			
		$data['entry_cost'] = $this->language->get('entry_cost');
		$data['help_product_cost'] = $this->language->get('help_product_cost');	
    	$data['text_cost_fixed'] = $this->language->get('text_cost_fixed');
		$data['text_cost_average'] = $this->language->get('text_cost_average');
		$data['text_cost_fifo'] = $this->language->get('text_cost_fifo');
		$data['text_cost_average_set'] = $this->language->get('text_cost_average_set');
    	$data['text_cost'] = $this->language->get('text_cost');				
    	$data['text_cost_amount'] = $this->language->get('text_cost_amount');
    	$data['text_cost_or'] = $this->language->get('text_cost_or');		
    	$data['text_cost_percentage'] = $this->language->get('text_cost_percentage');	
    	$data['text_cost_additional'] = $this->language->get('text_cost_additional');
		$data['entry_option_sku'] = $this->language->get('entry_option_sku');				
		$data['text_option_price'] = $this->language->get('text_option_price');	
		$data['entry_option_price_tax'] = $this->language->get('entry_option_price_tax');
    	$data['text_cost_option_cost'] = $this->language->get('text_cost_option_cost');	
    	$data['text_cost_option'] = $this->language->get('text_cost_option');	
    	$data['entry_option_restock'] = $this->language->get('entry_option_restock');
    	$data['entry_option_cost'] = $this->language->get('entry_option_cost');
		$data['text_qty_by_option'] = $this->language->get('text_qty_by_option');
		$data['text_qty_set_by_option'] = $this->language->get('text_qty_set_by_option');
		$data['text_restock_cost'] = $this->language->get('text_restock_cost');
		$data['text_option_restock_cost'] = $this->language->get('text_option_restock_cost');
    	$data['text_restock_quantity'] = $this->language->get('text_restock_quantity');
    	$data['text_totalstock'] = $this->language->get('text_totalstock');
		$data['text_option_cost_average_set'] = $this->language->get('text_option_cost_average_set');
		$data['text_option_totalstock'] = $this->language->get('text_option_totalstock');
		$data['text_option_cost'] = $this->language->get('text_option_cost');
		$data['openbay_show_menu'] = $this->config->get('openbaymanager_show_menu');
            

		$this->load->model('extension/adv_supplier');

		$data['suppliers'] = $this->model_extension_adv_supplier->getProductSuppliers();

		if (isset($this->request->post['supplier_id'])) {
			$data['supplier_id'] = $this->request->post['supplier_id'];
		} elseif (!empty($product_info)) {
			$data['supplier_id'] = $product_info['supplier_id'];
		} else {
			$data['supplier_id'] = 0;
		}
							
    	if (isset($this->request->post['costing_method'])) {
      		$data['costing_method'] = $this->request->post['costing_method'];
    	} elseif (!empty($product_info)) {
      		$data['costing_method'] = $product_info['costing_method'];
    	} else {
			$data['costing_method'] = 0;
		}
							
		if (isset($this->request->post['cost'])) {
      		$data['cost'] = $this->request->post['cost'];
    	} else if (!empty($product_info)) {
			$data['cost'] = $product_info['cost'];
		} else {
      		$data['cost'] = 0;
    	}

		if (isset($this->request->post['restock_cost'])) {
      		$data['restock_cost'] = $this->request->post['restock_cost'];
    	} else if (!empty($product_info)) {
			$data['restock_cost'] = $product_info['cost_amount'] + (($product_info['cost_percentage'] / 100)*$product_info['price']) + $product_info['cost_additional'];	
		} else {
      		$data['restock_cost'] = 0;
    	}
		
		if (isset($this->request->post['cost_amount'])) {
      		$data['cost_amount'] = $this->request->post['cost_amount'];
    	} else if (!empty($product_info)) {
			$data['cost_amount'] = $product_info['cost_amount'];
		} else {
      		$data['cost_amount'] = 0;
    	}
		
		if (isset($this->request->post['cost_percentage'])) {
      		$data['cost_percentage'] = $this->request->post['cost_percentage'];
    	} else if (!empty($product_info)) {
			$data['cost_percentage'] = $product_info['cost_percentage'];
		} else {
      		$data['cost_percentage'] = 0;
    	}
		
		if (isset($this->request->post['cost_additional'])) {
      		$data['cost_additional'] = $this->request->post['cost_additional'];
    	} else if (!empty($product_info)) {
			$data['cost_additional'] = $product_info['cost_additional'];
		} else {
      		$data['cost_additional'] = 0;
    	}
				
		if (isset($this->request->post['restock_quantity'])) {
      		$data['restock_quantity'] = $this->request->post['restock_quantity'];
    	} elseif (isset($this->request->get['product_id'])) {
			$data['restock_quantity'] = 0;
		} else {
      		$data['restock_quantity'] = 0;
    	}
		
		if (isset($this->request->post['restock_quantity_temp'])) {
      		$data['restock_quantity_temp'] = $this->request->post['restock_quantity_temp'];
    	} elseif (isset($this->request->get['product_id'])) {
			$data['restock_quantity_temp'] = 0;
		} else {
      		$data['restock_quantity_temp'] = 0;
    	}

		if (isset($this->request->post['quantity_temp'])) {
      		$data['quantity_temp'] = $this->request->post['quantity_temp'];
    	} elseif (isset($this->request->get['product_id'])) {
			$data['quantity_temp'] = 0;
		} else {
      		$data['quantity_temp'] = 0;
    	}	
		
		if (isset($this->request->post['remove_temp'])) {
      		$data['remove_temp'] = $this->request->post['remove_temp'];
    	} elseif (isset($this->request->get['product_id'])) {
			$data['remove_temp'] = 0;
		} else {
      		$data['remove_temp'] = 0;
    	}	
            
		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['tax_class_id'])) {
			$data['tax_class_id'] = $this->request->post['tax_class_id'];
		} elseif (!empty($product_info)) {
			$data['tax_class_id'] = $product_info['tax_class_id'];
		} else {
			$data['tax_class_id'] = 0;
		}


		if ($data['price'] != '') {
	    	$data['price_tax'] = sprintf("%.4f",$this->model_catalog_product->calculate($data['tax_class_id'], $data['price'], $this->config->get('config_tax')));
		} else {
		    $data['price_tax'] = '';
    	}
		
    	$tax_rates = $this->model_catalog_product->getTaxRates($data['tax_class_id']);
    	usort($tax_rates, array( __CLASS__, "compare_tax_rates"));
    	$data['js_rates'] = json_encode($tax_rates);
    	$data['json_tax_rates'] = $this->url->link('catalog/product/json_taxrates', 'user_token=' . $this->session->data['user_token'] . $url, true);		
            
		if (isset($this->request->post['date_available'])) {
			$data['date_available'] = $this->request->post['date_available'];
		} elseif (!empty($product_info)) {
			$data['date_available'] = ($product_info['date_available'] != '0000-00-00') ? $product_info['date_available'] : '';
		} else {
			$data['date_available'] = date('Y-m-d');
		}


		if (isset($this->request->post['date_release'])) {
			$data['date_release'] = $this->request->post['date_release'];
		} elseif (!empty($product_info)) {
			$data['date_release'] = ($product_info['date_release'] != '0000-00-00') ? $product_info['date_release'] : '';
		} else {
			$data['date_release'] = date('Y-m-d');
		}
		
		$this->load->model('localisation/release_status');

		$data['release_statuses'] = $this->model_localisation_release_status->getReleaseStatuses();

		if (isset($this->request->post['release_status_id'])) {
			$data['release_status_id'] = $this->request->post['release_status_id'];
		} elseif (!empty($product_info)) {
			$data['release_status_id'] = $product_info['release_status_id'];
		} else {
			$data['release_status_id'] = 0;
		}
		
		if (isset($this->request->post['quantity'])) {
			$data['quantity'] = $this->request->post['quantity'];
		} elseif (!empty($product_info)) {
			$data['quantity'] = $product_info['quantity'];
		} else {
			
			$data['quantity'] = 0;
            
		}

		if (isset($this->request->post['minimum'])) {
			$data['minimum'] = $this->request->post['minimum'];
		} elseif (!empty($product_info)) {
			$data['minimum'] = $product_info['minimum'];
		} else {
			$data['minimum'] = 1;
		}

		if (isset($this->request->post['subtract'])) {
			$data['subtract'] = $this->request->post['subtract'];
		} elseif (!empty($product_info)) {
			$data['subtract'] = $product_info['subtract'];
		} else {
			$data['subtract'] = 1;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($product_info)) {
			$data['sort_order'] = $product_info['sort_order'];
		} else {
			$data['sort_order'] = 1;
		}


				$this->load->model('localisation/distributor_status');

				$data['distributor_statuses'] = $this->model_localisation_distributor_status->getDistributorStatuses();

				if (isset($this->request->post['distributor_status_id'])) {
					$data['distributor_status_id'] = $this->request->post['distributor_status_id'];
				} elseif (!empty($product_info)) {
					$data['distributor_status_id'] = $product_info['distributor_status_id'];
				} else {
					$data['distributor_status_id'] = 0;
				}
			
		$this->load->model('localisation/stock_status');

		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();

		if (isset($this->request->post['stock_status_id'])) {
			$data['stock_status_id'] = $this->request->post['stock_status_id'];
		} elseif (!empty($product_info)) {
			$data['stock_status_id'] = $product_info['stock_status_id'];
		} else {
			$data['stock_status_id'] = 0;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($product_info)) {
			$data['status'] = $product_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['weight'])) {
			$data['weight'] = $this->request->post['weight'];
		} elseif (!empty($product_info)) {
			$data['weight'] = $product_info['weight'];
		} else {
			$data['weight'] = '';
		}

		$this->load->model('localisation/weight_class');

		$data['weight_classes'] = $this->model_localisation_weight_class->getWeightClasses();

		if (isset($this->request->post['weight_class_id'])) {
			$data['weight_class_id'] = $this->request->post['weight_class_id'];
		} elseif (!empty($product_info)) {
			$data['weight_class_id'] = $product_info['weight_class_id'];
		} else {
			$data['weight_class_id'] = $this->config->get('config_weight_class_id');
		}

		if (isset($this->request->post['length'])) {
			$data['length'] = $this->request->post['length'];
		} elseif (!empty($product_info)) {
			$data['length'] = $product_info['length'];
		} else {
			$data['length'] = '';
		}

		if (isset($this->request->post['width'])) {
			$data['width'] = $this->request->post['width'];
		} elseif (!empty($product_info)) {
			$data['width'] = $product_info['width'];
		} else {
			$data['width'] = '';
		}

		if (isset($this->request->post['height'])) {
			$data['height'] = $this->request->post['height'];
		} elseif (!empty($product_info)) {
			$data['height'] = $product_info['height'];
		} else {
			$data['height'] = '';
		}

		$this->load->model('localisation/length_class');

		$data['length_classes'] = $this->model_localisation_length_class->getLengthClasses();

		if (isset($this->request->post['length_class_id'])) {
			$data['length_class_id'] = $this->request->post['length_class_id'];
		} elseif (!empty($product_info)) {
			$data['length_class_id'] = $product_info['length_class_id'];
		} else {
			$data['length_class_id'] = $this->config->get('config_length_class_id');
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->post['manufacturer_id'])) {
			$data['manufacturer_id'] = $this->request->post['manufacturer_id'];
		} elseif (!empty($product_info)) {
			$data['manufacturer_id'] = $product_info['manufacturer_id'];
		} else {
			$data['manufacturer_id'] = 0;
		}

		if (isset($this->request->post['manufacturer'])) {
			$data['manufacturer'] = $this->request->post['manufacturer'];
		} elseif (!empty($product_info)) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);

			if ($manufacturer_info) {
				$data['manufacturer'] = $manufacturer_info['name'];
			} else {
				$data['manufacturer'] = '';
			}
		} else {
			$data['manufacturer'] = '';
		}

		// Categories
		$this->load->model('catalog/category');

		if (isset($this->request->post['product_category'])) {
			$categories = $this->request->post['product_category'];
		} elseif (isset($this->request->get['product_id'])) {
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

		// Filters
		$this->load->model('catalog/filter');

		if (isset($this->request->post['product_filter'])) {
			$filters = $this->request->post['product_filter'];
		} elseif (isset($this->request->get['product_id'])) {
			$filters = $this->model_catalog_product->getProductFilters($this->request->get['product_id']);
		} else {
			$filters = array();
		}

		$data['product_filters'] = array();

		foreach ($filters as $filter_id) {
			$filter_info = $this->model_catalog_filter->getFilter($filter_id);

			if ($filter_info) {
				$data['product_filters'][] = array(
					'filter_id' => $filter_info['filter_id'],
					'name'      => $filter_info['group'] . ' &gt; ' . $filter_info['name']
				);
			}
		}

		// Attributes
		$this->load->model('catalog/attribute');

		if (isset($this->request->post['product_attribute'])) {
			$product_attributes = $this->request->post['product_attribute'];
		} elseif (isset($this->request->get['product_id'])) {
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

		// Options
		$this->load->model('catalog/option');

		if (isset($this->request->post['product_option'])) {
			$product_options = $this->request->post['product_option'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_options = $this->model_catalog_product->getProductOptions($this->request->get['product_id']);
		} else {
			$product_options = array();
		}

$data['entry_custom_alt'] = $this->language->get('entry_custom_alt');
$data['entry_custom_h1'] = $this->language->get('entry_custom_h1');
$data['entry_custom_h2'] = $this->language->get('entry_custom_h2');
$data['entry_custom_imgtitle'] = $this->language->get('entry_custom_imgtitle');
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

						'option_restock_quantity' => '0',
						'sku'                     => $product_option_value['sku'],	
						'price_tax'               => sprintf("%.4f",$this->model_catalog_product->calculate($data['tax_class_id'], $product_option_value['price'], $this->config->get('config_tax'), false)),	
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

		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		if (isset($this->request->post['product_discount'])) {
			$product_discounts = $this->request->post['product_discount'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);
		} else {
			$product_discounts = array();
		}

		$data['product_discounts'] = array();

		foreach ($product_discounts as $product_discount) {
			$data['product_discounts'][] = array(
				'customer_group_id' => $product_discount['customer_group_id'],
				'quantity'          => $product_discount['quantity'],
				'priority'          => $product_discount['priority'],
				'price'             => $product_discount['price'],
				'date_start'        => ($product_discount['date_start'] != '0000-00-00') ? $product_discount['date_start'] : '',
				'date_end'          => ($product_discount['date_end'] != '0000-00-00') ? $product_discount['date_end'] : ''
			);
		}

		if (isset($this->request->post['product_special'])) {
			$product_specials = $this->request->post['product_special'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_specials = $this->model_catalog_product->getProductSpecials($this->request->get['product_id']);
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

		// Image
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($product_info)) {
			$data['image'] = $product_info['image'];
		} else {
			$data['image'] = '';
		}
		
		// video
		if (isset($this->request->post['video'])) {
			$data['video'] = $this->request->post['video'];
		} elseif (!empty($product_info)) {
			$data['video'] = $product_info['video'];
		} else {
			$data['video'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($product_info) && is_file(DIR_IMAGE . $product_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($product_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		// Images
		if (isset($this->request->post['product_image'])) {
			$product_images = $this->request->post['product_image'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_images = $this->model_catalog_product->getProductImages($this->request->get['product_id']);
		} else {
			$product_images = array();
		}


		foreach ($data['product_discounts'] as &$discount) {
			if ($discount['price'] != '') {
				$discount['price_tax'] = sprintf("%.4f",$this->model_catalog_product->calculate($data['tax_class_id'], $discount['price'], $this->config->get('config_tax')));
				$discount['cost'] = '';
				$discount['profit'] = '';				
			} else {
				$discount['price_tax'] = '';
				$discount['cost'] = '';
				$discount['profit'] = '';				
			}
		}

		foreach ($data['product_specials'] as &$special) {
			if ($special['price'] != '') {
				$special['price_tax'] = sprintf("%.4f",$this->model_catalog_product->calculate($data['tax_class_id'], $special['price'], $this->config->get('config_tax')));
				$special['cost'] = '';
				$special['profit'] = '';
			} else {
				$special['price_tax'] = '';
				$special['cost'] = '';
				$special['profit'] = '';
			}
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

		// Downloads
		$this->load->model('catalog/download');

		if (isset($this->request->post['product_download'])) {
			$product_downloads = $this->request->post['product_download'];
		} elseif (isset($this->request->get['product_id'])) {
			$product_downloads = $this->model_catalog_product->getProductDownloads($this->request->get['product_id']);
		} else {
			$product_downloads = array();
		}

		$data['product_downloads'] = array();

		foreach ($product_downloads as $download_id) {
			$download_info = $this->model_catalog_download->getDownload($download_id);

			if ($download_info) {
				$data['product_downloads'][] = array(
					'download_id' => $download_info['download_id'],
					'name'        => $download_info['name']
				);
			}
		}

		if (isset($this->request->post['product_related'])) {
			$products = $this->request->post['product_related'];
		} elseif (isset($this->request->get['product_id'])) {
			$products = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
		} else {
			$products = array();
		}

		$data['product_relateds'] = array();

		foreach ($products as $product_id) {
			$related_info = $this->model_catalog_product->getProduct($product_id);

			if ($related_info) {
				$data['product_relateds'][] = array(
					'product_id' => $related_info['product_id'],
					'name'       => $related_info['name']
				);
			}
		}

		if (isset($this->request->post['points'])) {
			$data['points'] = $this->request->post['points'];
		} elseif (!empty($product_info)) {
			$data['points'] = $product_info['points'];
		} else {
			$data['points'] = '';
		}

		if (isset($this->request->post['product_reward'])) {
			$data['product_reward'] = $this->request->post['product_reward'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_reward'] = $this->model_catalog_product->getProductRewards($this->request->get['product_id']);
		} else {
			$data['product_reward'] = array();
		}

		if (isset($this->request->post['product_seo_url'])) {
			$data['product_seo_url'] = $this->request->post['product_seo_url'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_seo_url'] = $this->model_catalog_product->getProductSeoUrls($this->request->get['product_id']);
		} else {
			$data['product_seo_url'] = array();
		}

		if (isset($this->request->post['product_layout'])) {
			$data['product_layout'] = $this->request->post['product_layout'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['product_layout'] = $this->model_catalog_product->getProductLayouts($this->request->get['product_id']);
		} else {
			$data['product_layout'] = array();
		}


        /**Marketplace Code Starts Here
         * customfield
         */

         $data['mpcustomfield'] = $this->load->controller('extension/module/marketplace/customfield');

        /**Marketplace Code Ends Here
         * customfield
         */
      
		$this->load->model('design/layout');

		$data['layouts'] = $this->model_design_layout->getLayouts();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


        /**
         * Marketplace Code Starts Here
         */
        if($this->config->get('module_marketplace_status') && isset($this->request->get['product_id'])){
            $data['product_id_text'] = '&product_id='.$this->request->get['product_id'];
        }else{
            $data['product_id_text'] = '';
        }

        if($this->config->get('module_marketplace_status')){
            $data['module_marketplace_status'] = true;
        }else{
            $data['module_marketplace_status'] = false;
        }

        /**
         * Marketplace Code Ends Here
         */
      
		$this->response->setOutput($this->load->view('catalog/product_form', $data));
	}


	public function stock_chart($filter_data = array()) {
		$json = array();
		
		$this->load->language('catalog/product');

		$stock_data = json_decode(file_get_contents('prm_json_array.txt'), true);
		
		$chart_data = array(
			'results'		=> $stock_data
		);
		
		$json['ghstock_quantity'] = array();
		$json['ghcost'] = array();
		$json['ghprice'] = array();
		$json['ghprofit'] = array();
		$json['ghdate_added'] = array();

		$json['ghstock_quantity']['label'] = $this->language->get('entry_gstock_quantity');
		$json['ghcost']['label'] = $this->language->get('entry_gcost');
		$json['ghprice']['label'] = $this->language->get('entry_gprice');
		$json['ghprofit']['label'] = $this->language->get('entry_gprofit');
		$json['ghdate_added']['label'] = '';
		$json['ghstock_quantity']['data'] = array();
		$json['ghcost']['data'] = array();
		$json['ghprice']['data'] = array();
		$json['ghprofit']['data'] = array();
		$json['ghdate_added']['data'] = array();
		
		foreach ($chart_data['results'] as $key => $value) {
			$json['ghstock_quantity']['data'][] = array($key, $value['ghstock_quantity']);
			$json['ghcost']['data'][] = array($key, $value['ghcost']);
			$json['ghprice']['data'][] = array($key, $value['ghprice']);
			$json['ghprofit']['data'][] = array($key, ($value['ghprice'] - $value['ghcost']));
			$json['ghdate_added']['data'][] = array($key, $value['ghdate_added']);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function download_history() {
		$this->response->addheader('Pragma: public');
		$this->response->addheader('Expires: 0');
		$this->response->addheader('Content-Description: File Transfer');
		$this->response->addheader('Content-Type: application/octet-stream');
		$this->response->addheader('Content-Disposition: attachment; filename=stock_history_'.date("Y_m_d").'.sql');
		$this->response->addheader('Content-Transfer-Encoding: binary');
		
		$this->load->model('tool/backup');
		
		$backup = array(
			DB_PREFIX . "product_stock_history",

			DB_PREFIX . "product_option_stock_history"
		);
				
		$this->response->setOutput($this->model_tool_backup->backup($backup));
	}
				
	public function delete_history() {
		$this->load->language('catalog/product');
		$this->load->model('catalog/product');
		$this->model_catalog_product->deleteProductHistory($this->request->get['product_id']);
		$durl = '';
		$this->response->redirect($this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . $durl, true));
	}	

    public function saveProductStockHistoryComment() {
        $product_stock_history_id  = $this->request->get['product_stock_history_id'];
        $comment = $this->request->get['comment'];

        $this->load->model('catalog/product');

        $this->response->setOutput($this->model_catalog_product->saveProductStockHistoryComment($product_stock_history_id,$comment));
    }

    public function saveProductOptionStockHistoryComment() {
        $product_option_stock_history_id  = $this->request->get['product_option_stock_history_id'];
        $comment = $this->request->get['comment'];

        $this->load->model('catalog/product');

        $this->response->setOutput($this->model_catalog_product->saveProductOptionStockHistoryComment($product_option_stock_history_id,$comment));
    }
			
	public function IsInstalled() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE code = 'adv_profit_module'");
		if (empty($query->num_rows)) {
			return false;
		}
		return true;
	}	
	
	private static function compare_tax_rates ($a, $b) {
		return ($a['priority'] < $b['priority']) ? -1 : 1;
	}
	
	function json_taxrates () {
		header('Content-Type: application/json');
		$result = array();
		
		$class_id = (int) $this->request->post['tax_class_id'];
		
		if (!isset($class_id)) {
			$result['status'] = 'error';
			$result['error'] = 'Invalid input';
		} else {
			$this->load->model('catalog/product');
			$tax_rates = $this->model_catalog_product->getTaxRates($class_id);
			usort($tax_rates, array( __CLASS__, "compare_tax_rates"));
			$result['status'] = 'ok';
			$result['tax_rates'] = $tax_rates;
		}
		
		$this->response->setOutput(json_encode($result));
	}			
            
	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['product_description'] as $language_id => $value) {
			if ((utf8_strlen($value['name']) < 1) || (utf8_strlen($value['name']) > 255)) {
				$this->error['name'][$language_id] = $this->language->get('error_name');
			}

			if ((utf8_strlen($value['meta_title']) < 1) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if ((utf8_strlen($this->request->post['model']) < 1) || (utf8_strlen($this->request->post['model']) > 64)) {
			$this->error['model'] = $this->language->get('error_model');
		}

		if ($this->request->post['product_seo_url']) {
			$this->load->model('design/seo_url');

			foreach ($this->request->post['product_seo_url'] as $store_id => $language) {
				foreach ($language as $language_id => $keyword) {
					if (!empty($keyword)) {
						if (count(array_keys($language, $keyword)) > 1) {
							$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_unique');
						}

						$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

						foreach ($seo_urls as $seo_url) {
							if (($seo_url['store_id'] == $store_id) && (!isset($this->request->get['product_id']) || (($seo_url['query'] != 'product_id=' . $this->request->get['product_id'])))) {
								$this->error['keyword'][$store_id][$language_id] = $this->language->get('error_keyword');

								break;
							}
						}
					}
				}
			}
		}


        /**Marketplace Code Starts Here
         * customfield
         */
        $customfielddata = array();
        if(isset($this->request->post['product_custom_field'])){
            $customfielddata = $this->request->post['product_custom_field'];
        }
        foreach ($customfielddata as $key => $value) {
            if(isset($value['custom_field_is_required']) && (($value['custom_field_is_required'] == 'yes' && isset($value['custom_field_value']) && $value['custom_field_value'][0] == '' ) || ($value['custom_field_is_required'] == 'yes' && !isset($value['custom_field_value'])))) {
                $this->error['customFieldError'][] = $value['custom_field_id'];
            }
        }
        if(isset($this->error['customFieldError'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        /**Marketplace Code Ends Here
         * customfield
         */
      
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateCopy() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			
$results = $this->model_catalog_product->getProductsForAutocomplete($filter_data);
            

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

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

								'cost'                    => (float)$product_option_value['cost'] ? $this->currency->format($product_option_value['cost'], $this->config->get('config_currency')) : false,
								'cost_prefix'             => $product_option_value['cost_prefix'],
            
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

					'cost'       => $result['cost'],
            
					'price'      => $result['price']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
