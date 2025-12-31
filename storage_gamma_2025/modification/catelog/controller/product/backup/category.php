<?php
class ControllerProductCategory extends Controller {
	public function index() {
		$this->load->language('product/category');

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

                $language_id            = (int)$this->config->get('config_language_id');
				$hb_oosn_stock_status   = $this->config->get('hb_oosn_stock_status');
				$hb_oosn_product_qty    = (int)$this->config->get('hb_oosn_product_qty');
				
				if (empty($hb_oosn_stock_status)){ 
					$hb_oosn_stock_status = array(0);
				}
				
if (defined('JOURNAL3_ACTIVE')) {
                $this->load->model('journal3/image');
            }

		if (isset($this->request->get['filter'])) {
			$filter = $this->request->get['filter'];
		} else {
			$filter = '';
		}

		if (isset($this->request->get['sort'])) {
        $sort = $this->request->get['sort'];
		  //$sort = 'p.date_modified';
		} else {
			//
            if (defined('JOURNAL3_ACTIVE')) {
                $sort = $this->journal3->settings->get('productSort');
            } else {
                $sort = 'p.sort_order';
            }
            
			$sort = 'p.date_modified';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			
            if (defined('JOURNAL3_ACTIVE')) {
                $order = $this->journal3->settings->get('productOrder');
            } else {
                $order = 'ASC';
            }
            
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['path'])) {
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$path = '';

			$parts = explode('_', (string)$this->request->get['path']);

			$category_id = (int)array_pop($parts);

			foreach ($parts as $path_id) {
				if (!$path) {
					$path = (int)$path_id;
				} else {
					$path .= '_' . (int)$path_id;
				}

				$category_info = $this->model_catalog_category->getCategory($path_id);

				if ($category_info) {
					$data['breadcrumbs'][] = array(
						'text' => $category_info['name'],
						'href' => $this->url->link('product/category', 'path=' . $path . $url)
					);
				}
			}
		} else {
			$category_id = 0;
		}

		$category_info = $this->model_catalog_category->getCategory($category_id);

		if ($category_info) {
			$this->document->setTitle($category_info['meta_title']?$category_info['meta_title']:$category_info['name']);
			$this->document->setDescription($category_info['meta_description']);
			$this->document->setKeywords($category_info['meta_keyword']);

			$data['heading_title'] = ($category_info['custom_h1'] <> '')?$category_info['custom_h1']:$category_info['name'];

			$data['custom_alt'] = $category_info['custom_alt'];
			$data['custom_imgtitle'] = $category_info['custom_imgtitle'];
			

			if (defined('JOURNAL3_ACTIVE')) {
                $data['text_compare'] = $this->journal3->countBadge($this->language->get('text_compare'), isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0);
            } else {
                $data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
            }

			// Set the last category breadcrumb
			$data['breadcrumbs'][] = array(
				'text' => $category_info['name'],
				'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'])
			);

			if ($category_info['image']) {
				if (defined('JOURNAL3_ACTIVE')) {
                $data['thumb'] = $this->model_journal3_image->resize($category_info['image'], $this->journal3->settings->get('image_dimensions_category.width'), $this->journal3->settings->get('image_dimensions_category.height'), $this->journal3->settings->get('image_dimensions_category.resize'));
                $data['thumb2x'] = $this->model_journal3_image->resize($category_info['image'], $this->journal3->settings->get('image_dimensions_category.width') * 2, $this->journal3->settings->get('image_dimensions_category.height') * 2, $this->journal3->settings->get('image_dimensions_category.resize'));
            } else {
                $data['thumb'] = $this->model_tool_image->resize($category_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_category_height'));
            }
			} else {
				$data['thumb'] = '';
			}

			$data['description'] = html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8');
$data['description'] = ($category_info['custom_h2'] != '')?'<h2>'.$category_info['custom_h2'].'</h2>'.$data['description']:$data['description'];
			$data['compare'] = $this->url->link('product/compare');

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

			$data['categories'] = array();

			if (defined('JOURNAL3_ACTIVE')) {
                if ($this->journal3->settings->get('refineCategories') !== 'none') {
                    if ($this->journal3->settings->get('subcategoriesDisplay') === 'carousel') {
                        $this->journal3->document->addStyle('catalog/view/theme/journal3/lib/swiper/swiper.min.css');
			            $this->journal3->document->addScript('catalog/view/theme/journal3/lib/swiper/swiper.min.js', 'footer');
                    }
                    $results = $this->model_catalog_category->getCategories($category_id);
                } else {
                    $results = array();
                }
            } else {
                $results = $this->model_catalog_category->getCategories($category_id);
            }

			foreach ($results as $result) {
				$filter_data = array(
					'filter_category_id'  => $result['category_id'],
					'filter_sub_category' => true
				);

				$data['categories'][] = array(
					'name' => defined('JOURNAL3_ACTIVE') ? $this->journal3->countBadge($result['name'], $this->config->get('config_product_count') ? $this->model_catalog_product->getTotalProducts($filter_data) : null) : $result['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
            'image' => defined('JOURNAL3_ACTIVE') ? $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_subcategory.width'), $this->journal3->settings->get('image_dimensions_subcategory.height'), $this->journal3->settings->get('image_dimensions_subcategory.resize')) : '',
            'image2x' => defined('JOURNAL3_ACTIVE') ? $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_subcategory.width') * 2, $this->journal3->settings->get('image_dimensions_subcategory.height') * 2, $this->journal3->settings->get('image_dimensions_subcategory.resize')) : '',
            'alt' => defined('JOURNAL3_ACTIVE') ? $result['name'] : '',
					'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
				);
			}

			$data['products'] = array();

			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_filter'      => $filter,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

			
            if (defined('JOURNAL3_ACTIVE')) {
                $this->load->model('journal3/filter');

                $filter_data = array_merge($this->model_journal3_filter->parseFilterData(), $filter_data);

                $this->model_journal3_filter->setFilterData($filter_data);

                \Journal3\Utils\Profiler::start('journal3/filter/total_products');

                $product_total = $this->model_journal3_filter->getTotalProducts();

                \Journal3\Utils\Profiler::end('journal3/filter/total_products');
            } else {
                $product_total = $this->model_catalog_product->getTotalProducts($filter_data);
            }
            

			
            if (defined('JOURNAL3_ACTIVE')) {
                \Journal3\Utils\Profiler::start('journal3/filter/products');

                $results = $this->model_journal3_filter->getProducts($filter_data);

			   $this->load->model('extension/module/google_ecommerce');
			   if ($results and isset($data['breadcrumbs'])) {
			     $data['product_impression_script'] =  $this->model_extension_module_google_ecommerce->build_product_impression($results, $page, $limit, $data['breadcrumbs'], 'Category Page');
    		   }else{
    			  $data['product_impression_script'] = '';
    		   }
			

                \Journal3\Utils\Profiler::end('journal3/filter/products');
            } else {
                $results = $this->model_catalog_product->getProducts($filter_data);

			   $this->load->model('extension/module/google_ecommerce');
			   if ($results and isset($data['breadcrumbs'])) {
			     $data['product_impression_script'] =  $this->model_extension_module_google_ecommerce->build_product_impression($results, $page, $limit, $data['breadcrumbs'], 'Category Page');
    		   }else{
    			  $data['product_impression_script'] = '';
    		   }
			
            }
            

            if (defined('JOURNAL3_ACTIVE')) {
                $this->load->model('journal3/product');

                $data['image_width'] = $this->journal3->settings->get('image_dimensions_product.width');
                $data['image_height'] = $this->journal3->settings->get('image_dimensions_product.height');

                if ($this->journal3->settings->get('performanceLazyLoadImagesStatus')) {
			        $data['dummy_image'] = $this->model_journal3_image->transparent($data['image_width'], $data['image_width']);
                }
            }
            

			foreach ($results as $result) {
				if ($result['image']) {
					if (defined('JOURNAL3_ACTIVE')) {
                $image = $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_product.width'), $this->journal3->settings->get('image_dimensions_product.height'), $this->journal3->settings->get('image_dimensions_product.resize'));
            } else {
                $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
            }
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
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
				
				$product_attributes = $this->model_catalog_product->getProductAttributes($result['product_id']);
              
               $player = $length = $product_rating = $difficulty = '-'; // Initialize variables
                //echo "<pre>";print_r($product_attributes); echo "</pre>";

                foreach ($product_attributes as $group) {
                    if (isset($group['attribute']) && is_array($group['attribute'])) {
                        foreach ($group['attribute'] as $attribute) {
                             $attribute_id = $attribute['attribute_id'];
                            $text = $attribute['text'];
                              if ($attribute_id == '501') {
                                  $player = trim(str_ireplace('players', '', $text));
                                  if($player == '? - ?'){
                                    $player = "-";
                                  }
                              }
                               if ($attribute_id == '503') {
                                  // Assuming the format is "min-maxmins", extract the max value
                                    $parts = explode('-', $text);
                                    if (count($parts) === 2) {
                                        // Remove non-digits from the second part (max value)
                                        $max_time = preg_replace('/\D/', '', $parts[1]);
                                        $length = $max_time;
                                    } else {
                                        // Fallback: use the original text if format is not as expected
                                        $length = $text;
                                    }
                              }
                              if ($attribute_id == '511') {
                                  $rating_parts = explode('/', $text);
                                $product_rating = trim($rating_parts[0]);
                              }
                               if ($attribute_id == '510') {
                                   $diff_parts = explode('/', $text);
                                    $difficulty = trim($diff_parts[0]);
                              }
                            
                            
                        }
                    }
                }
                
                // Get product IDs from oc_pickup_product where status = 1
	            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "pickup_product` WHERE `product_id` = '" . (int)$result['product_id'] . "' AND `status` = 1");
	            $pickup_count = $query->num_rows;
	

            if (defined('JOURNAL3_ACTIVE')) {
                if ($result['image']) {
                    $image2x = $this->model_journal3_image->resize($result['image'], $this->journal3->settings->get('image_dimensions_product.width') * 2, $this->journal3->settings->get('image_dimensions_product.height') * 2, $this->journal3->settings->get('image_dimensions_product.resize'));
                } else {
                    $image2x = $this->model_journal3_image->resize('placeholder.png', $this->journal3->settings->get('image_dimensions_product.width') * 2, $this->journal3->settings->get('image_dimensions_product.height') * 2, $this->journal3->settings->get('image_dimensions_product.resize'));
                }

                if ($this->journal3->document->isDesktop() && $this->journal3->settings->get('globalProductGridSecondImageStatus') && ($additional_image = $this->journal3->productSecondImage($result))) {
                    $second_image = $this->model_journal3_image->resize($additional_image, $this->journal3->settings->get('image_dimensions_product.width'), $this->journal3->settings->get('image_dimensions_product.height'), $this->journal3->settings->get('image_dimensions_product.resize'));
                    $second_image2x = $this->model_journal3_image->resize($additional_image, $this->journal3->settings->get('image_dimensions_product.width') * 2, $this->journal3->settings->get('image_dimensions_product.height') * 2, $this->journal3->settings->get('image_dimensions_product.resize'));
                } else {
                    $second_image = false;
                    $second_image2x = false;
                }
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

                'classes'        => array(
					defined('JOURNAL3_ACTIVE') ? $this->journal3->productExcludeButton($result, $price, $special) : null,
				),
                'quantity'       => defined('JOURNAL3_ACTIVE') ? $result['quantity'] : null,
				'stock_status'   => defined('JOURNAL3_ACTIVE') ? $result['stock_status'] : null,
				'thumb2x'        => defined('JOURNAL3_ACTIVE') ? $image2x : null,
				'second_thumb'   => defined('JOURNAL3_ACTIVE') ? $second_image : null,
				'second_thumb2x' => defined('JOURNAL3_ACTIVE') ? $second_image2x : null,
				'labels'         => defined('JOURNAL3_ACTIVE') ? $this->journal3->productLabels($result, $price, $special) : null,
				'extra_buttons'  => defined('JOURNAL3_ACTIVE') ? $this->journal3->productExtraButton($result, $price, $special) : null,
				'date_end'       => defined('JOURNAL3_ACTIVE') ? $this->journal3->productCountdown($result) : null,
				'price_value'    => defined('JOURNAL3_ACTIVE') ? ($result['special'] ? $result['special'] > 0 : $result['price'] > 0) : null,
				'stat1'          => defined('JOURNAL3_ACTIVE') ? $this->journal3->productStat($result, $this->journal3->settings->get('globalProductStat1')) : null,
				'stat2'          => defined('JOURNAL3_ACTIVE') ? $this->journal3->productStat($result, $this->journal3->settings->get('globalProductStat2')) : null,
            
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
					'rating'      => $result['rating'],
					'pickup_product_id'   => $pickup_count,
					 'player'         => empty($player) ? '-' : $player,
                    'length'         => empty($length) ? '-' : $length,
                    'product_rating' => empty($product_rating) ? '-' : $product_rating,
                    'difficulty'     => empty($difficulty) ? '-' : $difficulty,
					'href'        => $this->url->link('product/product', 'path=' . $this->request->get['path'] . '&product_id=' . $result['product_id'] . $url)
				);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}


            if (defined('JOURNAL3_ACTIVE')) {
                $url .= $this->model_journal3_filter->buildFilterData($filter_data);
            }
            
			$data['sorts'] = array();

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				//'value' => 'p.sort_order-ASC',
				'value' => 'p.date_modified-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_modified&order=DESC' . $url)
				//'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.sort_order&order=ASC' . $url)
			);
			/*$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.date_release-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_release&order=ASC' . $url)
			);*/

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=pd.name&order=DESC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.price&order=DESC' . $url)
			);

			if ($this->config->get('config_review_status')) {
				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_desc'),
					'value' => 'rating-DESC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=DESC' . $url)
				);

				$data['sorts'][] = array(
					'text'  => $this->language->get('text_rating_asc'),
					'value' => 'rating-ASC',
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=rating&order=ASC' . $url)
				);
			}

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_asc'),
				'value' => 'p.model-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=ASC' . $url)
			);

			$data['sorts'][] = array(
				'text'  => $this->language->get('text_model_desc'),
				'value' => 'p.model-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.model&order=DESC' . $url)
			);
			
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_release_asc'),
				'value' => 'p.date_release-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_release&order=ASC' . $url)
			);
			
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_release_desc'),
				'value' => 'p.date_release-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_release&order=DESC' . $url)
			);
			
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_dateadded_asc'),
				'value' => 'p.date_added-ASC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_added&order=ASC' . $url)
			);
			
			$data['sorts'][] = array(
				'text'  => $this->language->get('text_dateadded_desc'),
				'value' => 'p.date_added-DESC',
				'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '&sort=p.date_added&order=DESC' . $url)
			);

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			$data['limits'] = array();

			$limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

			sort($limits);

            if (defined('JOURNAL3_ACTIVE')) {
                $url .= $this->model_journal3_filter->buildFilterData($filter_data);
            }
            

			foreach($limits as $value) {
				$data['limits'][] = array(
					'text'  => $value,
					'value' => $value,
					'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&limit=' . $value)
				);
			}

			$url = '';

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
			}

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}


            if (defined('JOURNAL3_ACTIVE')) {
                $url .= $this->model_journal3_filter->buildFilterData($filter_data);
            }
            
			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url . '&page={page}');

			$data['pagination'] = $pagination->render();
			
				foreach ($pagination->prevnext() as $pagelink) {
					$this->document->addLink($pagelink['href'], $pagelink['rel']);
				}
				

			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			/* // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html 
			if ($page == 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id']), 'canonical');
			} else {
				$this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. $page), 'canonical');
			}
			
			if ($page > 1) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . (($page - 2) ? '&page='. ($page - 1) : '')), 'prev');
			}

			if ($limit && ceil($product_total / $limit) > $page) {
			    $this->document->addLink($this->url->link('product/category', 'path=' . $category_info['category_id'] . '&page='. ($page + 1)), 'next');
			}

			// */ 
			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

			$data['continue'] = $this->url->link('common/home');

			$data['column_left'] = $this->load->controller('common/column_left');

				$canonicals = $this->config->get('canonicals');
				if (isset($canonicals['canonicals_categories'])) {
					$this->document->removeLink('canonical');
					$pathx = explode('_', $this->request->get['path']);
					$pathx = end($pathx);
					$this->document->addLink($this->url->link('product/category', 'path=' . $pathx .((isset($page) && ($page > 1))?('&page='.$page):'') ), 'canonical');
					}
			
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/category', $data));
		} else {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['filter'])) {
				$url .= '&filter=' . $this->request->get['filter'];
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
				'href' => $this->url->link('product/category', $url)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');

				$canonicals = $this->config->get('canonicals');
				if (isset($canonicals['canonicals_categories'])) {
					$this->document->removeLink('canonical');
					$pathx = explode('_', $this->request->get['path']);
					$pathx = end($pathx);
					$this->document->addLink($this->url->link('product/category', 'path=' . $pathx .((isset($page) && ($page > 1))?('&page='.$page):'') ), 'canonical');
					}
			
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
}
