<?php
class ControllerExtensionTotalCoupon extends Controller {
	public function index() {
		if ($this->config->get('total_coupon_status')) {
			$this->load->language('extension/total/coupon');

			if (isset($this->session->data['coupon'])) {
				$data['coupon'] = $this->session->data['coupon'];
			} else {
				$data['coupon'] = '';
			}


            //=== iSenseLabs Promotions
            if (!$data['coupon'] && !empty($this->session->data['coupon_promotions'])) {
                $data['coupon'] = $this->session->data['coupon_promotions'];
            }
            //=== iSenseLabs Promotions :: end
            
			return $this->load->view('extension/total/coupon', $data);
		}
	}

	public function coupon() {
		$this->load->language('extension/total/coupon');

		$json = array();

		$this->load->model('extension/total/coupon');

		if (isset($this->request->post['coupon'])) {
			$coupon = $this->request->post['coupon'];
		} else {
			$coupon = '';
		}

		$coupon_info = $this->model_extension_total_coupon->getCoupon($coupon);

		if (empty($this->request->post['coupon'])) {
			$json['error'] = $this->language->get('error_empty');

			unset($this->session->data['coupon']);
		} elseif ($coupon_info) {
			$this->session->data['coupon'] = $this->request->post['coupon'];

			$this->session->data['success'] = $this->language->get('text_success');

			$json['redirect'] = $this->url->link('checkout/cart');
		} else {
			$json['error'] = $this->language->get('error_coupon');
		}


            //=== iSenseLabs Promotions
            $this->session->data['coupon_promotions'] = '';
            if (!empty($json['error']) && $coupon) {
                $this->config->load('isenselabs/promotions');
                $this->isl_promotions = $this->config->get('promotions');
                $this->load->model($this->isl_promotions['path']);
                $this->load->language($this->isl_promotions['path']);

                // Only check if coupon code belong to active promo rule
                // Promo rule validation/ evaluation centralized in promotions_total
                $islpr_coupon = $this->{$this->isl_promotions['model']}->couponCheck($coupon);

                if ($islpr_coupon['active']) {
                    unset($this->session->data['coupon']);
                    unset($json['error']);

                    if ($islpr_coupon['valid']) {
                        $this->session->data['coupon_promotions'] = $coupon;
                        $this->session->data['success'] = sprintf($this->language->get('text_coupon_success'), $coupon, $islpr_coupon['url'], $islpr_coupon['title']);

                        $json['redirect'] = $this->url->link('checkout/cart');
                    } else {
                        $json['error'] = sprintf($this->language->get('text_coupon_error'), $coupon, $islpr_coupon['url'], $islpr_coupon['title']);
                    }
                }
            }
            //=== iSenseLabs Promotions :: end
            
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
