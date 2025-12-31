<?php
class ControllerProductProduct extends Controller {
	private $error = array();


			private function parseText($node, $keyword, $dom, $link, $target='', $tooltip = 0)
			{
				if (mb_strpos($node->nodeValue, $keyword) !== false)
					{
						$keywordOffset = mb_strpos($node->nodeValue, $keyword, 0, 'UTF-8');
						$newNode = $node->splitText($keywordOffset);
						$newNode->deleteData(0, mb_strlen($keyword, 'UTF-8'));
						$span = $dom->createElement('a', $keyword);
						if ($tooltip)
							{
								$span->setAttribute('href', '#');
								$span->setAttribute('style', 'text-decoration:none');
								$span->setAttribute('class', 'title');
								$span->setAttribute('title', $keyword.'|'.$link);
							}
							else
							{
								$span->setAttribute('href', $link);
								$span->setAttribute('target', $target);
								$span->setAttribute('style', 'text-decoration:none');
							}							
						
						$node->parentNode->insertBefore($span, $newNode);
						$this->parseText($newNode ,$keyword, $dom, $link, $target, $tooltip);
					}					
			}
			
			

			
	public function index() {

            if (defined('JOURNAL3_ACTIVE')) {
                $this->journal3->document->addStyle('catalog/view/theme/journal3/lib/imagezoom/imagezoom.min.css');
			    $this->journal3->document->addScript('catalog/view/theme/journal3/lib/imagezoom/jquery.imagezoom.min.js', 'footer');

                $this->journal3->document->addStyle('catalog/view/theme/journal3/lib/lightgallery/css/lightgallery.min.css');
                $this->journal3->document->addStyle('catalog/view/theme/journal3/lib/lightgallery/css/lg-transitions.min.css');
                $this->journal3->document->addScript('catalog/view/theme/journal3/lib/lightgallery/js/lightgallery-all.js', 'footer');

                $this->journal3->document->addStyle('catalog/view/theme/journal3/lib/swiper/swiper.min.css');
			    $this->journal3->document->addScript('catalog/view/theme/journal3/lib/swiper/swiper.min.js', 'footer');
            }
            
		$this->load->language('product/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$this->load->model('catalog/category');

		if (isset($this->request->get['path'])) {
			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path)
					);
				}
			}

			// Set the last category breadcrumb
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$url = '';

				if (isset($this->request->get['sort'])) {
					$url .= '&sort=' . $this->request->get['sort'];
				}

				if (isset($this->request->get['order'])) {
					$url .= '&order=' . $this->request->get['order'];
				}

				if (isset($this->request->get['page'])) {
					$url .= '&page=' . $this->request->get['page'];
				}

				if (isset($this->request->get['limit'])) {
					$url .= '&limit=' . $this->request->get['limit'];
				}

				$data['breadcrumbs'][] = array(
					'text' => $category_info['name'],
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
				);
			}
		}

		$this->load->model('catalog/manufacturer');

		if (isset($this->request->get['manufacturer_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_brand'),
				'href' => $this->url->link('product/manufacturer')
			);

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$data['breadcrumbs'][] = array(
					'text' => $manufacturer_info['name'],
					'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
				);
			}
		}

		if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
			$url = '';

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_search'),
				'href' => $this->url->link('product/search', $url)
			);
		}

		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

			   $this->load->model('extension/module/google_ecommerce');
			   if ($product_info and isset($data['breadcrumbs'])) {
			     $product_view_script =  $this->model_extension_module_google_ecommerce->build_product_view($product_info, $data['breadcrumbs']);
			     $data['ga_script'] = $product_view_script['ga_script'];
			     $data['px_script'] = $product_view_script['px_script'];
    		   }else{
    			  $data['ga_script'] = $data['px_script'] = '';
    		   }
			
		
		if ($product_info['product_rating']) {
            $data['product_rating'] = $product_info['product_rating'];
        } else {
            $data['product_rating'] =  '';
        }
		
		//check product page open from cateory page
		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
						
			if(empty($this->model_catalog_product->checkProductCategory($product_id, $parts))) {
				$product_info = array();
			}
		}

		//check product page open from manufacturer page
		if (isset($this->request->get['manufacturer_id']) && !empty($product_info)) {
			if($product_info['manufacturer_id'] !=  $this->request->get['manufacturer_id']) {
				$product_info = array();
			}
		}

		if ($product_info) {

            if (defined('JOURNAL3_ACTIVE')) {
                $this->load->language('product/compare');

                $data['text_weight'] = $this->language->get('text_weight');
                $data['text_dimension'] = $this->language->get('text_dimension');
                $data['product_quantity'] = $product_info['quantity'];
                $data['product_price_value'] = $product_info['special'] ? $product_info['special'] > 0 : $product_info['price'] > 0;
                $data['product_sku'] = $product_info['sku'];
                $data['product_upc'] = $product_info['upc'];
                $data['product_ean'] = $product_info['ean'];
                $data['product_jan'] = $product_info['jan'];
                $data['product_isbn'] = $product_info['isbn'];
                $data['product_mpn'] = $product_info['mpn'];
                $data['product_location'] = $product_info['location'];
                $data['product_dimension'] = (float)$product_info['length'] || (float)$product_info['width'] || (float)$product_info['height'];
                $data['product_length'] = $this->length->format($product_info['length'], $product_info['length_class_id']);
                $data['product_width'] = $this->length->format($product_info['width'], $product_info['length_class_id']);
                $data['product_height'] = $this->length->format($product_info['height'], $product_info['length_class_id']);
                $data['product_weight'] = (float)$product_info['weight'] ? $this->weight->format($product_info['weight'], $product_info['weight_class_id']) : false;

                $data['product_labels'] = $this->journal3->productLabels($product_info, $product_info['price'], $product_info['special']);
                $data['product_exclude_classes'] = $this->journal3->productExcludeButton($product_info, $product_info['price'], $product_info['special']);
                $data['product_extra_buttons'] = $this->journal3->productExtraButton($product_info, $product_info['price'], $product_info['special']);
                $data['product_blocks'] = array();

                foreach($this->journal3->productBlocks($product_info, $product_info['price'], $product_info['special']) as $module_id => $module_data) {
                    if ($module_data['position'] === 'quickview' && $this->journal3->document->isPopup()) {
                    	if ($block = $this->load->controller('journal3/product_blocks', array('module_id' => $module_id, 'module_type' => 'product_blocks', 'product_info' => $product_info))) {
							$data['product_blocks']['default'][] = $block;
						}
                    } else if ($module_data['position'] === 'quickview_details' && $this->journal3->document->isPopup()) {
                    	if ($block = $this->load->controller('journal3/product_blocks', array('module_id' => $module_id, 'module_type' => 'product_blocks', 'product_info' => $product_info))) {
							$data['product_blocks']['bottom'][] = $block;
						}
                    } else if ($module_data['position'] === 'quickview_image' && $this->journal3->document->isPopup()) {
                    	if ($block = $this->load->controller('journal3/product_blocks', array('module_id' => $module_id, 'module_type' => 'product_blocks', 'product_info' => $product_info))) {
							$data['product_blocks']['image'][] = $block;
						}
                    } else if (!$this->journal3->document->isPopup()) {
                    	if ($block = $this->load->controller('journal3/product_blocks', array('module_id' => $module_id, 'module_type' => 'product_blocks', 'product_info' => $product_info))) {
							$data['product_blocks'][$module_data['position']][] = $block;
						}
                    }
                }

                $product_tabs = array();

                foreach($this->journal3->productTabs($product_info, $product_info['price'], $product_info['special']) as $module_id => $module_data) {
                    if ($module_data['position'] === 'quickview' && $this->journal3->document->isPopup()) {
                    	if ($tab = $this->load->controller('journal3/product_tabs', array('module_id' => $module_id, 'module_type' => 'product_tabs', 'product_info' => $product_info))) {
							$product_tabs['default'][] = $tab;
						}
                    } else if ($module_data['position'] === 'quickview_details' && $this->journal3->document->isPopup()) {
                    	if ($tab = $this->load->controller('journal3/product_tabs', array('module_id' => $module_id, 'module_type' => 'product_tabs', 'product_info' => $product_info))) {
							$product_tabs['bottom'][] = $tab;
						}
                    } else if ($module_data['position'] === 'quickview_image' && $this->journal3->document->isPopup()) {
                    	if ($tab = $this->load->controller('journal3/product_tabs', array('module_id' => $module_id, 'module_type' => 'product_tabs', 'product_info' => $product_info))) {
							$product_tabs['image'][] = $tab;
						}
                    } else if (!$this->journal3->document->isPopup()) {
                    	if ($tab = $this->load->controller('journal3/product_tabs', array('module_id' => $module_id, 'module_type' => 'product_tabs', 'product_info' => $product_info))) {
							$product_tabs[$module_data['position']][] = $tab;
						}
                    }
                }

                foreach ($product_tabs as $position => &$items) {
                    $_items = array();

                    foreach ($items as $item) {
                        $_items[$item['display']][] = $item;
                    }

                    foreach ($_items as $items) {
                        $data['product_blocks'][$position][] = $this->load->controller('journal3/product_tabs/tabs', array('items' => $items, 'position' => $position));
                    }
                }

                $this->load->model('catalog/manufacturer');

                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);

                if ($manufacturer_info && $manufacturer_info['image']) {
                    $data['manufacturer_image'] = $this->model_journal3_image->resize($manufacturer_info['image'], $this->journal3->settings->get('image_dimensions_manufacturer_logo.width'), $this->journal3->settings->get('image_dimensions_manufacturer_logo.height'), $this->journal3->settings->get('image_dimensions_manufacturer_logo.resize'));
                    $data['manufacturer_image2x'] = $this->model_journal3_image->resize($manufacturer_info['image'], $this->journal3->settings->get('image_dimensions_manufacturer_logo.width') * 2, $this->journal3->settings->get('image_dimensions_manufacturer_logo.height') * 2, $this->journal3->settings->get('image_dimensions_manufacturer_logo.resize'));
                } else {
                    $data['manufacturer_image'] = false;
                }

                if ($product_info['special']) {
                    $data['date_end'] = $this->journal3->productCountdown($product_info);
                } else {
                    $data['date_end'] = false;
                }

                if ($this->journal3->document->isPopup()) {
                    $data['view_more_url'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);
                }
            }
            
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $product_info['name'],
				'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
			);

$extendedseo = $this->config->get('extendedseo');
			$this->document->setTitle(((isset($category_info['name']) && isset($extendedseo['categoryintitle']) )?($category_info['name'].' : '):'').($product_info['meta_title']?$product_info['meta_title']:$product_info['name']));
			$this->document->setDescription($product_info['meta_description']);
			$this->document->setKeywords($product_info['meta_keyword']);
			$this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
			$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
			$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
			$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

			$data['heading_title'] = ($product_info['custom_h1'] <> '')?$product_info['custom_h1']:$product_info['name'];

			$data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
			$data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

			$this->load->model('catalog/review');

			$data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

			$data['product_id'] = (int)$this->request->get['product_id'];
			$data['manufacturer'] = $product_info['manufacturer'];
			$data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
			$data['model'] = $product_info['model'];
			$data['reward'] = $product_info['reward'];
			$data['points'] = $product_info['points'];

				$data['mbreadcrumbs'] = array();

				$data['mbreadcrumbs'][] = array(
					'text'      => $this->language->get('text_home'),
					'href'      => $this->url->link('common/home')
				);
				
				if ($this->model_catalog_product->getFullPath($this->request->get['product_id'])) {
					
					$path = '';
			
					$parts = explode('_', (string)$this->model_catalog_product->getFullPath($this->request->get['product_id']));
					
					$category_id = (int)array_pop($parts);
											
					foreach ($parts as $path_id) {
						if (!$path) {
							$path = $path_id;
						} else {
							$path .= '_' . $path_id;
						}
						
						$category_info = $this->model_catalog_category->getCategory($path_id);
						
						if ($category_info) {
							$data['mbreadcrumbs'][] = array(
								'text'      => $category_info['name'],
								'href'      => $this->url->link('product/category', 'path=' . $path)								
							);
						}
					}
					
					$category_info = $this->model_catalog_category->getCategory($category_id);
					
					if ($category_info) {			
						$url = '';
											
						$data['mbreadcrumbs'][] = array(
							'text'      => $category_info['name'],
							'href'      => $this->url->link('product/category', 'path=' . $this->model_catalog_product->getFullPath($this->request->get['product_id']))						
						);
					}
			
				
				} else {
				$data['mbreadcrumb'] = false;
				}

				$data['mreviews'] = array();

				$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id']);

				foreach ($results as $result) {
					$data['mreviews'][] = array(
						'author'     => $result['author'],
						'text'       => nl2br($result['text']),
						'rating'     => (int)$result['rating'],
						'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
					);
				}

				
				$data['review_no'] = $product_info['reviews'];		
				$data['quantity'] = $product_info['quantity'];
				$data['sku'] = $product_info['sku']?$product_info['sku']:$this->request->get['product_id'];						
				$data['upc'] = $product_info['upc'];						
				$data['ean'] = $product_info['ean'];						
				$data['jan'] = $product_info['jan'];						
				$data['isbn'] = $product_info['isbn'];						
				$data['mpn'] = $product_info['mpn']?$product_info['mpn']:$this->request->get['product_id'];						
				$data['meta_description'] = $product_info['meta_description'];								
				$data['currency_code'] = $this->session->data['currency'];
				$data['richsnippets'] = $this->config->get('richsnippets');				
						
			
			
			$extendedseo = $this->config->get('extendedseo');
			$data['description'] = ((isset($extendedseo['productseo']))?'<h2>'.$product_info['name'].'</h2>':'').html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');
			
$data['custom_imgtitle'] = $product_info['custom_imgtitle'];
$data['description'] = ($product_info['custom_h2'] != '')?'<h2>'.$product_info['custom_h2'].'</h2>'.$data['description']:$data['description'];
$data['custom_alt'] = $product_info['custom_alt'];
			
			$information_id = 20;
            $information_data = $this->model_catalog_product->getInformation($information_id);
           
             $data['notes'] = $information_data['status'];
            // Check if the information record is active
            if ($information_data['status'] == 1) {
                // Get the current language id (assuming it's stored in config)
                $language_id = $this->config->get('config_language_id');
                
                // Use the description for the current language or fallback to the first available language
                if (isset($information_data['information_descriptions'][$language_id])) {
                    $descriptions = $information_data['information_descriptions'][$language_id];
                } else {
                    $descriptions = reset($information_data['information_descriptions']);
                }
                
                 $instock_description = html_entity_decode($descriptions['instock_description'], ENT_QUOTES, 'UTF-8');
                $preorder_description = html_entity_decode($descriptions['preorder_description'], ENT_QUOTES, 'UTF-8');
                $coming_soon_description = html_entity_decode($descriptions['coming_soon_description'], ENT_QUOTES, 'UTF-8');
                $reordered_description = html_entity_decode($descriptions['reordered_description'], ENT_QUOTES, 'UTF-8');
                $restocked_description = html_entity_decode($descriptions['restocked_description'], ENT_QUOTES, 'UTF-8');
                
                $new_description = html_entity_decode($descriptions['new_description'], ENT_QUOTES, 'UTF-8');
                $like_new_description = html_entity_decode($descriptions['like_new_description'], ENT_QUOTES, 'UTF-8');
                $very_good_description = html_entity_decode($descriptions['very_good_description'], ENT_QUOTES, 'UTF-8');
                $good_description = html_entity_decode($descriptions['good_description'], ENT_QUOTES, 'UTF-8');
                $acceptable_description = html_entity_decode($descriptions['acceptable_description'], ENT_QUOTES, 'UTF-8');
                
                // Assign product status description based on product_info's stock_status_id
                if ($product_info['stock_status'] == 'Pre-Order') {
                     $data['stock_notes'] = "Pre-Order";
                    $data['product_status_description'] = $preorder_description;
                } elseif ($product_info['stock_status'] == 'On ReOrder') {
                     $data['stock_notes'] = "On Reorder";
                    $data['product_status_description'] = $reordered_description;
                } elseif ($product_info['stock_status'] == "Coming Soon") {
                     $data['stock_notes'] = "Coming Soon";
                    $data['product_status_description'] = $coming_soon_description;
                } elseif ($product_info['stock_status'] == 'New' || $product_info['stock_status'] == 'In Stock') {
                    $data['stock_notes'] = "In Stock";
                    $data['product_status_description'] = $instock_description;
                } elseif ($product_info['stock_status'] == 'Restock') {
                     $data['stock_notes'] = "ReStock";
                    $data['product_status_description'] = $restocked_description;
                }
                
                
              
                if($product_info['product_rating'] == 'new'){
                     $data['stock_notes'] = "New";
                    $data['product_status_description'] = $new_description;
                } elseif($product_info['product_rating'] == 'like_new'){
                     $data['stock_notes'] = "Like New";
                    $data['product_status_description'] = $like_new_description;
                } elseif($product_info['product_rating'] == 'very_good'){
                     $data['stock_notes'] = "Very Good";
                    $data['product_status_description'] = $very_good_description;
                } elseif($product_info['product_rating'] == 'good_description'){
                     $data['stock_notes'] = "Good";
                    $data['product_status_description'] = $good_description;
                } elseif($product_info['product_rating'] == 'acceptable'){
                     $data['stock_notes'] = "Acceptable";
                    $data['product_status_description'] = $acceptable_description;
                }
                
            }

			if ($product_info['quantity'] <= 0) {
				//$data['stock'] = $product_info['stock_status'];
				$data['stock'] = $this->language->get('text_outofstock');
			} elseif ($product_info['stock_status_id'] == 8){
			    $data['stock'] = $product_info['stock_status'];
			} elseif ($product_info['stock_status_id'] == 15){
			      $data['stock'] = "Pre-Order";
			} elseif ($product_info['stock_status_id'] == 13){
			     $data['stock'] = "Pre-Order";
			} elseif ($this->config->get('config_stock_display')) {
				$data['stock'] = $product_info['quantity'];
			}elseif ($product_info['stock_status'] == 'Pre-Order') {
                $data['stock'] = "Pre-Order";
			} elseif (isset($product_info['stock_status']) && $product_info['stock_status'] == 'Coming Soon') {
             $data['stock'] = "Pre-Order";
			} elseif (isset($product_info['stock_status']) && $product_info['stock_status'] == 'On ReOrder') {
             $data['stock'] = "Pre-Order";
             } 	else {
				
            if (defined('JOURNAL3_ACTIVE')) {
                $stylePrefix = $this->journal3->document->isPopup('quickview') ? 'quickviewPageStyle' : 'productPageStyle';
                $data['stock'] = $this->journal3->settings->get($stylePrefix . 'ProductInStockText');

                // some third party addons for in stock status
                if (isset($product_info['in_stock_status']) && $product_info['in_stock_status']) {
                    $data['stock'] = $product_info['in_stock_status'];
                }
            } else {
                $data['stock'] = $this->language->get('text_instock');
            }
            
			}
			
			/*
			if ($product_info['quantity'] <= 0) {
				$data['stock'] = $product_info['stock_status'];
			} else {
				
            if (defined('JOURNAL3_ACTIVE')) {
                $stylePrefix = $this->journal3->document->isPopup('quickview') ? 'quickviewPageStyle' : 'productPageStyle';
                $data['stock'] = $this->journal3->settings->get($stylePrefix . 'ProductInStockText');

                // some third party addons for in stock status
                if (isset($product_info['in_stock_status']) && $product_info['in_stock_status']) {
                    $data['stock'] = $product_info['in_stock_status'];
                }
            } else {
                $data['stock'] = $this->language->get('text_instock');
            }
            
			}*/

			$this->load->model('tool/image');

                $language_id            = (int)$this->config->get('config_language_id');
				$hb_oosn_stock_status   = $this->config->get('hb_oosn_stock_status');
				$hb_oosn_product_qty    = (int)$this->config->get('hb_oosn_product_qty');
				
				if (empty($hb_oosn_stock_status)){ 
					$hb_oosn_stock_status = array(0);
				}
				

			if ($product_info['image']) {
				$data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
			} else {
				$data['popup'] = '';
			}

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
			} else {
				$data['thumb'] = '';
			}

			$data['images'] = array();

			$results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

            if (defined('JOURNAL3_ACTIVE')) {
                array_unshift($results, array('image' => $product_info['image']));

                foreach ($results as $result) {
				    $data['images'][] = array(
                        'galleryThumb'  => $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_popup_thumb.width'), $this->journal3->settings->get('image_dimensions_popup_thumb.height'), $this->journal3->settings->get('image_dimensions_popup_thumb.resize')),
                        'image'         => $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_thumb.width'), $this->journal3->settings->get('image_dimensions_thumb.height'), $this->journal3->settings->get('image_dimensions_thumb.resize')),
                        'image2x'       => $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_thumb.width') * 2, $this->journal3->settings->get('image_dimensions_thumb.height') * 2, $this->journal3->settings->get('image_dimensions_thumb.resize')),
                        'popup'         => $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_popup.width'), $this->journal3->settings->get('image_dimensions_popup.height'), $this->journal3->settings->get('image_dimensions_popup.resize')),
                        'thumb'         => $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_additional.width'), $this->journal3->settings->get('image_dimensions_additional.height'), $this->journal3->settings->get('image_dimensions_additional.resize')),
                        'thumb2x'       => $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_additional.width') * 2, $this->journal3->settings->get('image_dimensions_additional.height') * 2, $this->journal3->settings->get('image_dimensions_additional.resize'))
				    );
			    }

			    $results = array();
            }
            

			foreach ($results as $result) {
				$data['images'][] = array(
					'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
					'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
				);
			}

			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				$tax_price = (float)$product_info['special'];
			} else {
				$data['special'] = false;
				$tax_price = (float)$product_info['price'];
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format($tax_price, $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			$discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

			$data['discounts'] = array();

			foreach ($discounts as $discount) {
				$data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
				);
			}

			$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || $option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							
            'image'                   => defined('JOURNAL3_ACTIVE') ? ($option_value['image'] ? $this->model_journal3_image->resize($option_value['image'], $this->journal3->settings->get('image_dimensions_options.width'), $this->journal3->settings->get('image_dimensions_options.height'), $this->journal3->settings->get('image_dimensions_options.resize')) : false) : $this->model_tool_image->resize($option_value['image'], 50, 50),
            
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}


                $language_id = (int)$this->config->get('config_language_id');
				$data['notify_button_p'] = $this->config->get('hb_oosn_notifybtn_p'.$language_id);
				$data['notify_button_o'] = $this->config->get('hb_oosn_notifybtn_o'.$language_id);
				$quantity = $data['quantity'] = $product_info['quantity'];
				
				$hb_oosn_stock_status = $this->config->get('hb_oosn_stock_status');
				$hb_oosn_product_qty = (int)$this->config->get('hb_oosn_product_qty');
				
				$notify_form_type = ($this->config->get('hb_oosn_form_type')) ? $this->config->get('hb_oosn_form_type') : 'popup';
				if ($notify_form_type == 'embed'){
				    $data['notify_embed_type'] = true;
				    $data['notifyform_inline'] = $this->load->controller('extension/module/hb_oosn/notify_embed');

                $data['notifyform_inline_2'] = $this->load->controller('extension/module/hb_oosn/notify_embed_2');
			
				}else{
				    $data['notify_embed_type'] = false;
				    $data['notifyform_inline'] = '';

                $data['notifyform_inline_2'] = '';
			
				}

				$query = $this->db->query("SELECT stock_status_id FROM `" . DB_PREFIX . "product` WHERE product_id = '".(int)$product_id."'");
				$stock_status_id = $data['stock_status_id'] = $query->row['stock_status_id'];
				
				if (empty($hb_oosn_stock_status)){ 
					$hb_oosn_stock_status = array(0);
					$stock_status_id = 0;
				}
				
                $hb_oosn_manual_rule = false;
				$hb_oosn_manual_notify = false;
				if ($this->config->get('hb_oosn_manual_rule')) {
				    $hb_oosn_manual_rule = true;
				    $is_notify = $this->db->query("SELECT count(*) as total FROM " . DB_PREFIX . "out_of_stock_product WHERE product_id = '".(int)$product_id."'");
				    if ($is_notify->row['total'] > 0) {
				        $hb_oosn_manual_notify = true;
				    }
				}
				
				if ((($quantity < $hb_oosn_product_qty) && (in_array($stock_status_id, $hb_oosn_stock_status)) && (!$hb_oosn_manual_rule)) || ($hb_oosn_manual_notify && (isset($data['options']) && !$data['options']) )) { 
					$data['hb_oosn_enable'] = true;
				}else{
					$data['hb_oosn_enable'] = false;
				}
				
			
			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			$data['review_status'] = $this->config->get('config_review_status');

                if (defined('JOURNAL3_ACTIVE')) {
                    $data['journal3_product_quantity'] = (int)\Journal3\Utils\Arr::get($this->request->get, 'product_quantity', 0);
                }
            

			if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
				$data['review_guest'] = true;
			} else {
				$data['review_guest'] = false;
			}

			if ($this->customer->isLogged()) {
				$data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
			} else {
				$data['customer_name'] = '';
			}

			$data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
			$data['rating'] = (int)$product_info['rating'];

			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
			} else {
				$data['captcha'] = '';
			}

			$data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);


				$autolinks = $this->config->get('autolinks'); 
				
				if (isset($autolinks) && (strpos($data['description'], 'iframe') == false) && (strpos($data['description'], 'object') == false)){
				$xdescription = mb_convert_encoding(html_entity_decode($data['description'], ENT_COMPAT, "UTF-8"), 'HTML-ENTITIES', "UTF-8"); 
				
				libxml_use_internal_errors(true);
				$dom = new DOMDocument; 			
				$dom->loadHTML('<div>'.$xdescription.'</div>');				
				libxml_use_internal_errors(false);

				
				$xpath = new DOMXPath($dom);
								
				foreach ($autolinks as $autolink)
				{	
					$keyword = $autolink['keyword'];
					$xlink = mb_convert_encoding(html_entity_decode($autolink['link'], ENT_COMPAT, "UTF-8"), 'HTML-ENTITIES', "UTF-8");
					$target = $autolink['target'];
					$tooltip = isset($autolink['tooltip']);
													
					$pTexts = $xpath->query(
						sprintf('///text()[contains(., "%s")]', $keyword)
					);
					
					foreach ($pTexts as $pText) {
						$this->parseText($pText, $keyword, $dom, $xlink, $target, $tooltip);
					}

									
				}
						
				$data['description'] = $dom->saveXML($dom->documentElement);
				
				}
				
			
			$data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

			$data['products'] = array();

			$results = defined('JOURNAL3_ACTIVE') ? array() : $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if (!is_null($result['special']) && (float)$result['special'] >= 0) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$tax_price = (float)$result['special'];
				} else {
					$special = false;
					$tax_price = (float)$result['price'];
				}
	
				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format($tax_price, $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}


                $hb_oosn_m_rule 	= false;
        		$hb_oosn_m_notify 	= false;
        		if ($this->config->get('hb_oosn_manual_rule')) {
        			$hb_oosn_m_rule = true;
        			$is_notify = $this->db->query("SELECT count(*) as total FROM `" . DB_PREFIX . "out_of_stock_product` WHERE product_id = '".(int)$result['product_id']."'");
        			if ($is_notify->row['total'] > 0) {
        				$hb_oosn_m_notify = true;
        			}
        		}
        		
        		$notify_enable = ((!$hb_oosn_m_rule && ($result['quantity'] < $hb_oosn_product_qty) && ((in_array($result['stock_status_id'], $hb_oosn_stock_status)) || (in_array(0,$hb_oosn_stock_status)) ) || $hb_oosn_m_notify))? true : false;
        		
				
				$data['products'][] = array(
					'product_id'  => $result['product_id'],

					'quantity'          => $result['quantity'],
					'stock_status_id'   => $result['stock_status_id'],
					'button_cart'	    => $notify_enable ? $this->config->get('hb_oosn_notifybtn_o'.$language_id):$this->language->get('button_cart'),
					'notify_enable'	    => $notify_enable ? true:false,
					
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}


                    if($this->config->get('module_marketplace_status')) {

                                    $this->load->model('account/customerpartner');

                                    $this->load->language('customerpartner/profile');

                                    if(isset($this->request->get['product_id']) && $this->request->get['product_id']) {
									                    $check_seller = $this->model_account_customerpartner->getProductSellerDetails($this->request->get['product_id']);
								                    } else {
									                    $check_seller = array();
                                    }

                                    if ($this->config->get('wk_custom_shipping_status') && isset($this->session->data['shipping_address']['postcode'])) {


                                        $seller_id = 0;

                                        if (isset($check_seller['customer_id']) && $check_seller['customer_id']) {
                                            $seller_id = $check_seller['customer_id'];
                                        }

                                        $data['text_seller_information'] = $this->language->get('text_seller_information');

                                        $this->load->model('customerpartner/information');

                                        $data['informations'] = array();

                                        $informations = $this->model_customerpartner_information->getSellerInformations($seller_id);

                                        if ($informations) {
                                          $count = 0;

                                          foreach ($informations as $result) {
                                            $data['informations'][] = array(
                                              'title' => $result['title'],
                                              'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
                                            );

                                            $count++;

                                            if ($count == 3) {
                                              break;
                                            }
                                          }
                                        }

                                        $weight = 0;

                                        if (isset($product_info['weight']) && $product_info['weight']) {
                                            $weight = $product_info['weight'];
                                        }

                                        $max_days = $this->model_account_customerpartner->getMinDays($seller_id,$this->session->data['shipping_address']['postcode'],$weight);

                                        if (isset($max_days['max_days']) && $max_days['max_days']) {
                                            $date = new DateTime(date('Y-m-d', strtotime("+".$max_days['max_days']." days")));

                                            $data['delivery_date'] = $date->format('Y-m-d');

                                            $data['text_delivery_date'] = $this->language->get('text_delivery_date');
                                        }
                                    }
                                    if(isset($check_seller) && isset($check_seller['customer_id'])) {
                                  $this->load->model('customerpartner/master');
									                $partner = $this->model_customerpartner_master->getProfile($check_seller['customer_id']);
									                if(isset($partner['google_analytic_id']) && $partner['google_analytic_id']) {
									                 	$data['seller_analytic_id'] = $partner['google_analytic_id'];
									                } else {
										                $data['seller_analytic_id'] = '';
                                  }
								                } 								

								                $data['admin_analytic_id'] = $this->config->get('marketplace_google_analytic_id');				
								
								                $data['product_analytic'] = false;
								                if($this->config->get('marketplace_google_analytic_allowed_page')) {
									                $analytic_allowed_pages = $this->config->get('marketplace_google_analytic_allowed_page');
									                if(isset($analytic_allowed_pages['product']) && $analytic_allowed_pages['product']) {
										                $data['product_analytic'] = true;
									                }									
								                }
                                        $data['showSellerInfo'] = false;
                                        $data['wk_custome_field_wkcustomfields'] = true;
                                        $customFields = array();
                                        $data['customFields'] = array();

                                        $customFields = $this->model_catalog_product->getProductCustomFields($this->request->get['product_id']);

                                        foreach ($customFields as $key => $value) {
                                            $customFieldsName = $this->model_catalog_product->getCustomFieldName($value['fieldId']);

                                            $customFieldsOptionId = $this->model_catalog_product->getCustomFieldOptionId($this->request->get['product_id'],$value['fieldId']);

                                            $customFieldValue = '';
                                            foreach ($customFieldsOptionId as $key => $option) {
                                                    if(is_numeric($option['option_id'])){
                                                        $customFieldValue .= $this->model_catalog_product->getCustomFieldOption($option['option_id']).", ";
                                                    }else{
                                                        $customFieldValue = $option['option_id'];
                                                    }
                                            }
                                            $data['customFields'][] = array(
                                                'fieldName' =>  $customFieldsName,
                                                'fieldValue'    =>  trim($customFieldValue,', '),
                                            );
                                        }

                                        $checkSellerOwnProduct = $this->model_account_customerpartner->checkSellerOwnProduct($this->request->get['product_id']);

                                        $this->load->language('extension/module/marketplace');
                                        if ($checkSellerOwnProduct && !$this->config->get('marketplace_sellerbuyproduct')) {
                                            $data['allowedProductBuy'] = false;
                                            $data['error_own_product'] = $this->language->get('error_own_product');
                                        }else{
                                            $data['allowedProductBuy'] = true;
                                            $data['error_own_product'] = false;
                                        }

                                        /**
                                         * add seller information on the product page through code end
                                         */

                                         if ($this->config->get('marketplace_seller_info_by_module') && !$this->config->get('marketplace_seller_info_hide')) {
                                             $data['showSellerInfo'] = true;
 																								$data['sellerprofile'] = $this->load->controller('extension/module/marketplace/sellerprofile');
                                             }
                                }else{
                                    $data['wk_custome_field_wkcustomfields'] = false;
                                    $data['allowedProductBuy'] = true;
                                    $data['showSellerInfo'] = false;
                                }
                  
			$data['tags'] = array();

			if ($product_info['tag']) {
				$tags = explode(',', $product_info['tag']);

				foreach ($tags as $tag) {
					$data['tags'][] = array(
						'tag'  => trim($tag),
						'href' => $this->url->link('product/search', 'tag=' . trim($tag))
					);
				}
			}


				$data['rprice'] = preg_replace( '/[^.0-9]/', '',($data['special'] ? $data['special'] : $data['price']));
				$richsnippets = $this->config->get('richsnippets');
				$socialseo = '';
				if (isset($richsnippets['ogsite'])) {
					$socialseo .= '
<meta property="og:type" content="product"/>
<meta property="og:title" content="'.$product_info['name'].'"/>
<meta property="og:image" content="'.$data['popup'].'"/>
<meta property="og:url" content="'.$this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id']).'"/>
<meta property="og:description" content="'.$product_info['meta_description'].'"/>
<meta property="product:price:amount" content="'.preg_replace( '/[^.0-9]/', '',($data['special'] ? $data['special'] : $data['price'])).'"/>
<meta property="product:price:currency" content="'.$this->session->data['currency'].'"/>';
					}
				if (isset($richsnippets['twittersite'])) {
					$socialseo .= '
<meta name="twitter:card" content="product" />';
if (isset($richsnippets['twitteruser'])) { 
	$socialseo .= '
<meta name="twitter:site" content="'.$richsnippets['twitteruser'].'" />';
	} 
$socialseo .= '
<meta name="twitter:title" content="'.$product_info['name'].'" />
<meta name="twitter:description" content="'.$product_info['meta_description'].'" />
<meta name="twitter:image" content="'.$data['popup'].'" />
<meta name="twitter:label1" content="Price">
<meta name="twitter:data1" content="'.preg_replace( '/[^.0-9]/', '',($data['special'] ? $data['special'] : $data['price'])).'">
<meta name="twitter:label2" content="Currency">
<meta name="twitter:data2" content="'.$this->session->data['currency'].'">
';
}
				$this->document->setSocialSeo($socialseo);
				
			$data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

			$this->model_catalog_product->updateViewed($this->request->get['product_id']);

                // Store pickup module code starts here
                if ($this->config->get('module_mp_store_pickup_status')) {
                    $data['customer_pickup_zipcode'] = '';
                    $data['module_mp_store_pickup_status'] = true;
                    $this->load->model('mp_store_pickup/mp_store_pickup');
                    $this->load->language('mp_store_pickup/mp_store_pickup');

                    $data['column_pickup_points'] = $this->language->get('column_pickup_points');
                    $data['column_pickup_address'] = $this->language->get('column_pickup_address');
                    $data['column_pickup_distance'] = $this->language->get('column_pickup_distance');
                    $data['column_action'] = $this->language->get('column_action');
                    $data['error_pickup_points'] = $this->language->get('error_pickup_points');

                    $data['chkPickupProduct'] = $this->model_mp_store_pickup_mp_store_pickup->checkPickupProduct($this->request->get['product_id']);
                    $data['module_mp_store_pickup_modal_header'] = $this->config->get('module_mp_store_pickup_modal_header')[$this->config->get('config_language_id')];
                    $data['module_mp_store_pickup_welcome_modal_body_header_text'] = $this->config->get('module_mp_store_pickup_welcome_modal_body_header_text')[$this->config->get('config_language_id')];
                    $data['module_mp_store_pickup_welcome_modal_body_content_text'] = $this->config->get('module_mp_store_pickup_welcome_modal_body_content_text')[$this->config->get('config_language_id')];
                    $data['module_mp_store_pickup_search_button_text'] = $this->config->get('module_mp_store_pickup_search_button_text')[$this->config->get('config_language_id')];

                    if ($this->customer->getId()) {
                        $getCustomerPickupZipcode = $this->model_mp_store_pickup_mp_store_pickup->getCustomerPickupZipcode($this->customer->getId());

                        if ($getCustomerPickupZipcode) {

                            $data['customer_pickup_zipcode'] = $getCustomerPickupZipcode['zipcode'];
                        }
                    }elseif(isset($_COOKIE['customer_pickup_point_zipcode']) && $_COOKIE['customer_pickup_point_zipcode']){

                        $data['customer_pickup_zipcode'] = $_COOKIE['customer_pickup_point_zipcode'];
                    }else{
                        $data['customer_pickup_zipcode'] = '';
                    }
                }else{
                    $data['module_mp_store_pickup_status'] = false;
                }
                // Store pickup module code ends here
                    

            if (defined('JOURNAL3_ACTIVE')) {
                $this->load->model('journal3/product');
                $this->model_journal3_product->addRecentlyViewedProduct($this->request->get['product_id']);

                $data['products_sold'] = $this->model_journal3_product->getProductsSold($this->request->get['product_id']);
                $data['product_views'] = $product_info['viewed'];
            }
            
			
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/product', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['search'])) {
				$url .= '&search=' . $this->request->get['search'];
			}

			if (isset($this->request->get['tag'])) {
				$url .= '&tag=' . $this->request->get['tag'];
			}

			if (isset($this->request->get['description'])) {
				$url .= '&description=' . $this->request->get['description'];
			}

			if (isset($this->request->get['category_id'])) {
				$url .= '&category_id=' . $this->request->get['category_id'];
			}

			if (isset($this->request->get['sub_category'])) {
				$url .= '&sub_category=' . $this->request->get['sub_category'];
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

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
			);

$extendedseo = $this->config->get('extendedseo');
			$this->document->setTitle(((isset($category_info['name']) && isset($extendedseo['categoryintitle']) )?($category_info['name'].' : '):'').$this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function review() {
		$this->load->language('product/product');

		$this->load->model('catalog/review');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'rating'     => (int)$result['rating'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$data['pagination'] = $pagination->render();
			
				foreach ($pagination->prevnext() as $pagelink) {
					$this->document->addLink($pagelink['href'], $pagelink['rel']);
				}
				

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

		$this->response->setOutput($this->load->view('product/review', $data));
	}

	public function write() {
		$this->load->language('product/product');

		$json = array();

		if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
			if ($this->request->server['REQUEST_METHOD'] == 'POST') {
				if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
					$json['error'] = $this->language->get('error_name');
				}

				if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
					$json['error'] = $this->language->get('error_text');
				}
			
				if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
					$json['error'] = $this->language->get('error_rating');
				}

				// Captcha
				if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
					$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

					if ($captcha) {
						$json['error'] = $captcha;
					}
				}

				if (!isset($json['error'])) {
					$this->load->model('catalog/review');

					$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

					$json['success'] = $this->language->get('text_success');
				}
			}
		} else {
			$json['error'] = $this->language->get('error_product');
		} 

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getRecurringDescription() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');

		if (isset($this->request->post['product_id'])) {
			$product_id = $this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		if (isset($this->request->post['recurring_id'])) {
			$recurring_id = $this->request->post['recurring_id'];
		} else {
			$recurring_id = 0;
		}

		if (isset($this->request->post['quantity'])) {
			$quantity = $this->request->post['quantity'];
		} else {
			$quantity = 1;
		}

		$product_info = $this->model_catalog_product->getProduct($product_id);

			   $this->load->model('extension/module/google_ecommerce');
			   if ($product_info and isset($data['breadcrumbs'])) {
			     $product_view_script =  $this->model_extension_module_google_ecommerce->build_product_view($product_info, $data['breadcrumbs']);
			     $data['ga_script'] = $product_view_script['ga_script'];
			     $data['px_script'] = $product_view_script['px_script'];
    		   }else{
    			  $data['ga_script'] = $data['px_script'] = '';
    		   }
			
		
		$recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

		$json = array();

		if ($product_info && $recurring_info) {
			if (!$json) {
				$frequencies = array(
					'day'        => $this->language->get('text_day'),
					'week'       => $this->language->get('text_week'),
					'semi_month' => $this->language->get('text_semi_month'),
					'month'      => $this->language->get('text_month'),
					'year'       => $this->language->get('text_year'),
				);

				if ($recurring_info['trial_status'] == 1) {
					$price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					$trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
				} else {
					$trial_text = '';
				}

				$price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

				if ($recurring_info['duration']) {
					$text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				} else {
					$text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
				}

				$json['success'] = $text;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
