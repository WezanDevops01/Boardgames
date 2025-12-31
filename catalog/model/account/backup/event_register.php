<?php
class ModelAccountEventRegister extends Model {
    public function addCustomer($data) {
        if (isset($data['events_id'])) {
            $event_id = $data['events_id'];
        }
    
        if (isset($data['events_name'])) {
            $event_name = $data['events_name'];
        }
    
        if (isset($data['existing_customer_id'])) {
            $existing_customer_id = $data['existing_customer_id'];
        }
          foreach ($data['product_id'] as $key => $product_id) {
            $registerName = $data['registerName'][$key];
            $event_id = (int)$event_id;
            $event_payment_code_id = $data['event_payment_code_id']; 
            $event_payment_code = isset($data['option'][$event_payment_code_id]) ? $data['option'][$event_payment_code_id] : '';
             $payment_status = 0;
           $existingRecordQuery = "SELECT COUNT(*) AS count FROM " . DB_PREFIX . "event_registration 
                        WHERE events_id = '" . $event_id . "'
                        AND product_id = '" . $this->db->escape($product_id) . "'
                        AND registerName = '" . $this->db->escape($registerName) . "'
                        AND date_added = NOW() 
                        AND existing_customer_id = '" . $data['existing_customer_id'] . "'";

        
            $existingRecord = $this->db->query($existingRecordQuery)->row['count'];
            if ($existingRecord == 0) {
				        $deleteQuery = "DELETE FROM " . DB_PREFIX . "event_registration 
                        WHERE events_id = '" . $event_id . "' 
                        AND product_id = '" . $product_id . "' 
                        AND registerName = '" . $registerName . "' 
                        AND existing_customer_id = '" . $existing_customer_id . "'";
     
						$this->db->query($deleteQuery);
             $insertQuery = "INSERT INTO " . DB_PREFIX . "event_registration SET 
            existing_customer_id = '" . (int)$data['existing_customer_id'] . "',
            events_id = '" . $event_id . "',
            events_name = '" . $this->db->escape($data['events_name']) . "',
            product_id = '" . $this->db->escape($product_id) . "',
            registerName = '" . $this->db->escape($registerName) . "',
            event_cost = '" . $this->db->escape($data['cost'] ) . "',
            event_payment_code = '" . $this->db->escape($event_payment_code) . "',
            payment_status = '" . $this->db->escape($payment_status) . "',
            firstname = '" . $this->db->escape($data['firstname']) . "',
            lastname = '" . $this->db->escape($data['lastname']) . "',
            email = '" . $this->db->escape($data['email']) . "',
            telephone = '" . $this->db->escape($data['telephone']) . "', 
            registerPerson = '" . (int)$data['registerPerson'] . "', 
            ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "',
            date_added = NOW()";
                $this->db->query($insertQuery);
            }
            
           $removedata = "SELECT * FROM " . DB_PREFIX . "event_registration 
                   WHERE events_id = '" . $event_id . "'
                   AND registerName = ''
                   AND existing_customer_id = '".$data['existing_customer_id']."'
                   AND DATE(date_added) = CURDATE()";
 
            $existingremove = $this->db->query($removedata);
            
            // Check if there are existing records to remove
            if ($existingremove->num_rows > 0) {
                // Loop through each row in the result set
                foreach ($existingremove->rows as $row) {
                    $deleteQuery = "DELETE FROM " . DB_PREFIX . "event_registration 
                                    WHERE events_id = '" . $row['events_id'] . "'
                                    AND existing_customer_id = '" . $row['existing_customer_id'] . "'
                                    AND date_added = '".$row['date_added']."'";
                    $this->db->query($deleteQuery);
                }
            }
        }

		
        $customer_id = $this->db->getLastId();
    	
    		/*
    		if ($customer_group_info['approval']) {
    			$this->db->query("INSERT INTO `" . DB_PREFIX . "event_customer_approval` SET customer_id = '" . (int)$customer_id . "', type = 'customer', date_added = NOW()");
    		}*/
    		
        return $customer_id;
    }
	
	public function editCustomer($customer_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "event_registration SET firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "' WHERE customer_id = '" . (int)$customer_id . "'");
	}
    
    public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "event_registration WHERE customer_id = '" . (int)$customer_id . "'");
		
		return $query->row;
	}

	public function getCustomerByEmail($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "event_registration WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row;
	}

	public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "event_registration WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

		return $query->row['total'];
	}
    
    public function getTotalCustomersByEmailAndEvent($email, $event_id, $product_id='') {
        
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "event_registration WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND events_id = '" . (int)$event_id . "' and product_id='". (int)$product_id."'");
		
		//$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "event_registration WHERE LOWER(email) = '" . $this->db->escape(utf8_strtolower($email)) . "' AND events_id = '" . (int)$event_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalCustomersByEvent($event_id) {
        
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "event_registration WHERE events_id = '" . (int)$event_id . "'");
		
		/*$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "event_registration WHERE events_id = '" . (int)$event_id . "'";
		
		echo $sql;
		die;*/
		
		return $query->row['total'];
	}
	
	public function getTotalRegisteredGamesByEvent($event_id, $product_id) {
        
		$query = $this->db->query("SELECT COUNT(product_id) AS total FROM " . DB_PREFIX . "event_registration WHERE events_id = '" . (int)$event_id . "'");
		
		/*
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "event_registration WHERE events_id = '" . (int)$event_id . "'";
		echo $sql;
		die;
		*/
		
		return $query->row['total'];
	}
		 public function getRegistrationData($event_id, $email) {
        $query = $this->db->query("
            SELECT * 
            FROM " . DB_PREFIX . "event_registration 
            WHERE events_id = '" . (int)$event_id . "' 
            AND email = '" . $this->db->escape($email) . "'
        ");

        return $query->rows;
    }
    
   public function getProductName($product_id) {
		$query = $this->db->query("SELECT name FROM `oc_product_description` WHERE `product_id` = '" . (int)$product_id . "'");
		
		if ($query->num_rows) {
			return $query->row['name'];
		} else {
			return false;
		}
	}
}