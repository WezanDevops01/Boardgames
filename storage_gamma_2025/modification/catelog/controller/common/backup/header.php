<?php
class ControllerCommonHeader extends Controller {
	public function index() {
		// Analytics
		$this->load->model('setting/extension');

		$data['analytics'] = array();

		$analytics = $this->model_setting_extension->getExtensions('analytics');

		foreach ($analytics as $analytic) {
			if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
				$data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code'], $this->config->get('analytics_' . $analytic['code'] . '_status'));
			}
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink($server . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();

             //split cart
             if($this->config->get('module_wk_split_cart_status')){
                      $data['split_cart_status'] = true;
                      $data['page_redirect'] = $this->url->link('checkout/cart');
                  }
            if($this->config->get('module_wk_split_cart_status') && isset($this->session->data['split_cart'])){
                if($this->cart->hasProducts()){

                  if(isset($this->request->get['route']) && $this->request->get['route'] != 'checkout/checkout'){

                      if($this->request->get['route'] != 'checkout/cart'){
                        if(!isset($this->session->data['split_cart']) || ($this->cart->hasSplitProducts() == 0))
                          foreach ($this->session->data['split_cart'] as $new_cart) {
                            $this->cart->add($new_cart['product_id'], $new_cart['quantity'], $new_cart['option'], $new_cart['recurring']);
                          }
                        // $this->session->data['cart'] = $this->session->data['split_cart'];
                        unset($this->session->data['voucher']);
                        unset($this->session->data['wk_vouchers']);
                        unset($this->session->data['shipping_method']);
                        unset($this->session->data['wk_shipping_method']);
                        unset($this->session->data['shipping_methods']);
                        unset($this->session->data['current_seller_id']);
                        unset($this->session->data['coupon']);
                        unset($this->session->data['wk_coupon']);
                        unset($this->session->data['reward']);
                        unset($this->session->data['vouchers']);
                      }
                    }
                }
             }
          


				$data['alternate'] = '';
				$mlseo = $this->config->get('mlseo');
				if (isset($mlseo['hreflang'])) {	
					$this->load->model('localisation/language');
					$languages = $this->model_localisation_language->getLanguages();
					
					if (isset($this->request->get['route'])) {
						if ((isset($mlseo['hreflangproduct'])) & ($this->request->get['route'] == 'product/product')) {
							foreach($languages as $xlanguage) {
																
								
								$squery = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language'");
								if (isset($xlanguage['code']) && ($xlanguage['code'] != $squery->row['value'])) {$url = $xlanguage['code'].'/';}
									else {$url = '';} 
									
								$query = $this->db->query("select * from " . DB_PREFIX . "seo_url where store_id = 0 and CONCAT('product_id=', CAST(".(int)$this->request->get['product_id']." as CHAR)) = query and language_id = ".  $xlanguage['language_id']);
								if ($query->num_rows) {
									$url .= $query->row['keyword'];
								}
								
								$data['alternate'] .='<link rel="alternate" hreflang="'.$xlanguage['code'].'" href="'.HTTP_SERVER.$url.'" />';
								 
								
							}
						}
						
						if ((isset($mlseo['hreflangcategory'])) & ($this->request->get['route'] == 'product/category')) {
							$xcats = explode('_', $this->request->get['path']); $xcat = end($xcats);
							foreach($languages as $xlanguage) {
															
								
								$squery = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language'");
								if (isset($xlanguage['code']) && ($xlanguage['code'] != $squery->row['value'])) {$url = $xlanguage['code'].'/';}
									else {$url = '';} 
									
								$query = $this->db->query("select * from " . DB_PREFIX . "seo_url where store_id = 0 and CONCAT('category_id=', CAST(".(int)$xcat." as CHAR)) = query and language_id = ".  $xlanguage['language_id']);
								if ($query->num_rows) {
									$url .= $query->row['keyword'];
								}
								$extendedseo = $this->config->get('extendedseo');
								$data['alternate'] .='<link rel="alternate" hreflang="'.$xlanguage['code'].'" href="'.HTTP_SERVER.$url.((isset($extendedseo['slash']))?'/':'').'" />';
								 
								
							}
						}
						
						if ((isset($mlseo['hreflangbrand'])) & ($this->request->get['route'] == 'product/manufacturer/info')) {
							foreach($languages as $xlanguage) {
																
								
								$squery = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language'");
								if (isset($xlanguage['code']) && ($xlanguage['code'] != $squery->row['value'])) {$url = $xlanguage['code'].'/';}
									else {$url = '';} 
									
								$query = $this->db->query("select * from " . DB_PREFIX . "seo_url where store_id = 0 and CONCAT('manufacturer_id=', CAST(".(int)$this->request->get['manufacturer_id']." as CHAR)) = query and language_id = ".  $xlanguage['language_id']);
								if ($query->num_rows) {
									$url .= $query->row['keyword'];
								}
								$extendedseo = $this->config->get('extendedseo');
								$data['alternate'] .='<link rel="alternate" hreflang="'.$xlanguage['code'].'" href="'.HTTP_SERVER.$url.((isset($extendedseo['slash']))?'/':'').'" />';
								 
								
							}
						}
						
						if ((isset($mlseo['hreflanginfo'])) & ($this->request->get['route'] == 'information/information')) {
							foreach($languages as $xlanguage) {
															
								
								$squery = $this->db->query("SELECT `value` FROM `" . DB_PREFIX . "setting` WHERE `key` = 'config_language'");
								if (isset($xlanguage['code']) && ($xlanguage['code'] != $squery->row['value'])) {$url = $xlanguage['code'].'/';}
									else {$url = '';} 
									
								$query = $this->db->query("select * from " . DB_PREFIX . "seo_url where store_id = 0 and CONCAT('information_id=', CAST(".(int)$this->request->get['information_id']." as CHAR)) = query and language_id = ".  $xlanguage['language_id']);
								if ($query->num_rows) {
									$url .= $query->row['keyword'];
								}
								
								$data['alternate'] .='<link rel="alternate" hreflang="'.$xlanguage['code'].'" href="'.HTTP_SERVER.$url.'" />';
								 
								
							}
						}
						
						
					
					}
				}
				
				
		$data['base'] = $server;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();

				$data['robots'] = '';
				$extendedseo = $this->config->get('extendedseo');
				if (isset($extendedseo['robots'])) {
					$data['robots'] = '<meta name="robots" content="index">';
					}
				
				foreach ($data['links'] as $link) { 
					if ($link['rel']=='canonical') {$hasCanonical = true;
					if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])) {
					$this->db->query("insert into " . DB_PREFIX . "bots_report (link, bot, ip, `date`) values ('".$this->db->escape($link['href'])."','".$_SERVER['HTTP_USER_AGENT']."','".$_SERVER['REMOTE_ADDR']."',now());");
					}
					}
				}
				
				if (isset($this->request->get['route']) && !isset($hasCanonical) && (strpos($this->request->get['route'], 'error') === false)) {
					if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])) {
					$this->db->query("insert into " . DB_PREFIX . "bots_report (link, bot, ip, `date`) values ('".$this->db->escape($this->url->link($this->request->get['route']))."','".$_SERVER['HTTP_USER_AGENT']."','".$_SERVER['REMOTE_ADDR']."',now());");
					}
				}
				
				$data['canonical_link'] = '';
				$canonicals = $this->config->get('canonicals'); 
				if (!isset($hasCanonical) && isset($this->request->get['route']) && (isset($canonicals['canonicals_extended']))) {
					$data['canonical_link'] = $this->url->link($this->request->get['route']);					
					}
				
				
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts('header');
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$data['name'] = $this->config->get('config_name');

		if (is_file(DIR_IMAGE . $this->config->get('config_logo'))) {
			$data['logo'] = $server . 'image/' . $this->config->get('config_logo');
		} else {
			$data['logo'] = '';
		}


			    $ga_customer_id = false;
			    
			    if ($this->customer->isLogged()) {
			        $ga_customer_id =  (int)$this->customer->getId();
			    }elseif ($this->config->get('ga_ecom_ip_match')) {
			        if (isset($_SERVER['REMOTE_ADDR'])) {
			            $ip = $_SERVER['REMOTE_ADDR'];
			            $customer_ip = $this->db->query("SELECT customer_id FROM ".DB_PREFIX."customer_ip WHERE `ip` = '".$this->db->escape($ip)."' LIMIT 1");
			            if ($customer_ip->row) {
			                $ga_customer_id = $customer_ip->row['customer_id'];
			            }
			        }
			    }
			    
			    if ($ga_customer_id && $this->config->get('ga_ecom_user_tracking')) {
			        $data['ga_user_tracking_script'] = "<script>gtag('set', {'user_id': '".$ga_customer_id."'});</script>";
			    }else{
			        $data['ga_user_tracking_script'] = '';
			    }
			    
				$this->load->model('extension/module/google_ecommerce');
			    
			    if ($this->config->get('ga_ecom_tracking_code')){ 
			        $data['google_analytics_script'] = html_entity_decode($this->config->get('ga_ecom_tracking_code'), ENT_QUOTES, 'UTF-8');
			    }else{
			        $data['google_analytics_script'] = '';
			    }
			    
			    if ($this->config->get('ga_ecom_fb_px_id')){ 
			        $data['fb_pixel_script'] = $this->model_extension_module_google_ecommerce->pixel_base_code($this->config->get('ga_ecom_fb_px_id'));
			    }else{
			        $data['fb_pixel_script'] = '';
			    }
			    
			    if ($this->config->get('ga_ecom_event_language')){ 
			        $data['event_lang_script'] = true;
			    }else{
			        $data['event_lang_script'] = false;
			    }
			

        $data['notification'] = '';
        $data['sellmenu'] =  $this->load->controller('extension/module/marketplace/sellmenu');
        $this->load->model('account/customerpartner');
        $data['module_marketplace_status'] = $this->config->get('module_marketplace_status');
        if($this->config->get('module_marketplace_status')){
    				$data['chkIsPartner'] = $this->model_account_customerpartner->chkIsPartner();
    				$data['marketplace_seller_mode'] = isset($this->session->data['marketplace_seller_mode']) ? $this->session->data['marketplace_seller_mode'] : 1;
    				if ($data['chkIsPartner'] && $data['marketplace_seller_mode']) {
    					$data['notification'] = $this->load->controller('account/customerpartner/notification/notifications');

    					$data['notification_total'] = $this->model_account_notification->getTotalSellerActivity() + $this->model_account_notification->getSellerProductActivityTotal() + $this->model_account_notification->getSellerReviewsTotal()-$this->model_account_notification->getViewedNotifications();
    				}
    		}
      
		$this->load->language('common/header');

		// Wishlist
		if ($this->customer->isLogged()) {
			$this->load->model('account/wishlist');

			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
		} else {
			$data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		}

		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->url->link('account/account', '', true), $this->customer->getFirstName(), $this->url->link('account/logout', '', true));
		

			$extendedseo = $this->config->get('extendedseo'); 
			if ((isset($extendedseo['trim_descriptions'])) && ($data['description']) && (strlen($data['description']) > 160)) {
				$pos=(strpos($data['description'], ' ', 155) > 0)?(strpos($data['description'], ' ', 155)):155;
				$data['description'] = substr($data['description'],0,$pos). ' ...'; 
			}
			if ((isset($extendedseo['trim_titles'])) && ($data['title']) && (strlen($data['title']) > 60)) {
				$pos=(strpos($data['title'], ' ', 56) > 0)?(strpos($data['title'], ' ', 56)):56;
				$data['title'] = substr($data['title'],0,$pos). ' ...'; 
			}
			

				$data['richsnippets'] = $this->config->get('richsnippets');
				$richsnippets = $this->config->get('richsnippets');
				if (isset($richsnippets['googlepublisher']) && isset($richsnippets['googleid'])) {
					array_push($data['links'], array('href'=>'https://plus.google.com/'.$richsnippets['googleid'],'rel'=>'publisher'));
					}
				$data['socialseo'] = '';
				if (isset($richsnippets['ogsite']) & (!isset($this->request->get['route']) || ($this->request->get['route'] == 'common/home'))) {
					$data['socialseo'] .= '
<meta property="og:type" content="website"/>
<meta property="og:title" content="'.$data['title'].'"/>
<meta property="og:image" content="'.$data['logo'].'"/>
<meta property="og:site_name" content="'.$data['name'].'"/>
<meta property="og:url" content="'.$server.'"/>
<meta property="og:description" content="'.$data['description'].'"/>';
					}
				if (isset($richsnippets['twittersite']) & (!isset($this->request->get['route']) || ($this->request->get['route'] == 'common/home'))) {
					$data['socialseo'] .= '
<meta name="twitter:card" content="summary" />';
if (isset($richsnippets['twitteruser'])) { 
	$data['socialseo'] .= '
<meta name="twitter:site" content="'.$richsnippets['twitteruser'].'" />';
	} 
$data['socialseo'] .= '
<meta name="twitter:title" content="'.$data['title'].'" />
<meta name="twitter:description" content="'.$data['description'].'" />
<meta name="twitter:image" content="'.$data['logo'].'" />';
}
				if (isset($this->request->get['route']) && ($this->request->get['route'] == 'product/product')) {
					$data['socialseo'] .=  $this->document->getSocialSeo(); 
					}
					
			
		$data['home'] = $this->url->link('common/home');
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['logged'] = $this->customer->isLogged();
		$data['account'] = $this->url->link('account/account', '', true);
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['shopping_cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);
		$data['contact'] = $this->url->link('information/contact');
		$data['telephone'] = $this->config->get('config_telephone');
		
		
		$data['language'] = $this->load->controller('common/language');
		$data['currency'] = $this->load->controller('common/currency');
		$data['search'] = $this->load->controller('common/search');
		$data['cart'] = $this->load->controller('common/cart');
		$data['menu'] = $this->load->controller('common/menu');


      if ($this->config->get('webp_image_status') && $this->config->get('webp_image_header') && isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false && function_exists('imagewebp')) {
        $this->load->model('tool/webp_image');
        return $this->model_tool_webp_image->convertInHtml($this->load->view('common/header', $data));
      }
      
		return $this->load->view('common/header', $data);
	}
}
