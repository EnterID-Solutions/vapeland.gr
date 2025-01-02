<?php
class ModelExtensionModuleCannedMessages extends Model {
    public function addMessage($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "canned_message SET name = '" . $this->db->escape($data['name']) . "', notify = '" . (isset($data['notify']) ? '1' : '0') . "', order_status_id = '" . (int)$data['order_status_id'] . "', comment = '" . $this->db->escape($data['comment']) . "', sort_order = '" . (int)$data['sort_order'] . "'");
    }
    
    public function editMessage($canned_message_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "canned_message SET name = '" . $this->db->escape($data['name']) . "', notify = '" . (isset($data['notify']) ? '1' : '0') . "', order_status_id = '" . (int)$data['order_status_id'] . "', comment = '" . $this->db->escape($data['comment']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE canned_message_id = '" . (int)$canned_message_id . "'");
    }
    
    public function deleteMessage($canned_message_id) {
        $this->db->query("DELETE FROM " . DB_PREFIX . "canned_message WHERE canned_message_id = '" . (int)$canned_message_id . "'");
    }
    
    public function getMessages() {
        $query = $this->db->query("SELECT *, (SELECT name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = cm.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status FROM " . DB_PREFIX . "canned_message cm ORDER BY sort_order, name");

        return $query->rows;
    }
    
    public function getMessage($canned_message_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "canned_message WHERE canned_message_id = '" . (int)$canned_message_id . "'");
        
        return $query->row;
    }
}