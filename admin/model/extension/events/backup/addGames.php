<?php

class ModelExtensionEventsAddGames extends Model {
     public function addGames($games) {
        // Delete all existing records from the event_game_list table.
        $this->db->query("DELETE FROM " . DB_PREFIX . "event_game_list");
    
        foreach ($games as $game) {
            // Skip if product_id is empty.
            if (empty($game['product_id'])) {
                continue;
            }

            // Insert the game record into event_game_list with the (possibly updated) video.
            $this->db->query("INSERT INTO " . DB_PREFIX . "event_game_list SET 
                product_id    = '" . (int)$game['product_id'] . "',
                name          = '" . $this->db->escape($game['product_name']) . "',
                registrations = '" . $this->db->escape($game['registrations']) . "',
                length        = '" . $this->db->escape($game['length']) . "',
                game_type     = '" . $this->db->escape($game['game_type']) . "',
                difficulty    = '" . $this->db->escape($game['difficulty']) . "',
                video         = '" . $this->db->escape($game['product_video']) . "'
            ");
            
            $query = $this->db->query("SELECT video FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$game['product_id'] . "'");
            if ($query->num_rows) {
                $productVideo = $query->row['video'];
                if ($game['product_video'] !== $productVideo) {
                    $this->db->query("UPDATE " . DB_PREFIX . "product SET video = '" . $game['product_video'] . "' WHERE product_id = '" . (int)$game['product_id'] . "'");
                }
            }
        }
    }

    public function getGames() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "event_game_list");
        return $query->rows;
    }
    
   public function getProductVideo($product_id) {
        $query = $this->db->query("SELECT video FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        return $query->row;
    }



}
