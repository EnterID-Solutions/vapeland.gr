<?php
class ModelExtensionModuleTracking extends Model {
  public function install(){
    $this->db->query("
    CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "courier` (
      `courier_id` int(11) NOT NULL AUTO_INCREMENT,
      `courier_name` varchar(255) NOT NULL,
      `courier_url` varchar(255) NOT NULL,
      PRIMARY KEY (`courier_id`)
    ) DEFAULT COLLATE=utf8_general_ci;");
    $this->db->query("
    CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "order_tracking` (
      `order_tracking_id` int(11) NOT NULL AUTO_INCREMENT,
      `order_id` int(11) NOT NULL,
      `courier_id` int(11) NOT NULL,
      `tracking_code` varchar(255) NOT NULL,
      PRIMARY KEY (`order_tracking_id`)
    ) DEFAULT COLLATE=utf8_general_ci;");
  }
  public function uninstall(){
    $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."courier`");
    $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."order_tracking`");
  }

  public function getCourier($courier_id){
    $q = $this->db->query("SELECT * from ".DB_PREFIX."courier where courier_id='".(int)$courier_id."'");
    if($q->num_rows > 0){
      return $q->row;
    } else {
      return false;
    }
  }

  public function getCouriers(){
    $q = $this->db->query("SELECT * from ".DB_PREFIX."courier");
    if($q->num_rows > 0){
      return $q->rows;
    } else {
      return false;
    }
  }

  public function saveCouriers($couriers=array()){
    foreach ($couriers as $courier) {
      if(isset($courier['courier_id'])){
        $this->db->query("UPDATE ".DB_PREFIX."courier set courier_name='".$this->db->escape($courier['courier_name'])."', courier_url='".$this->db->escape($courier['courier_url'])."' where courier_id='".(int)$courier['courier_id']."'");
      } else {
        $this->db->query("INSERT INTO ".DB_PREFIX."courier set courier_name='".$this->db->escape($courier['courier_name'])."', courier_url='".$this->db->escape($courier['courier_url'])."'");
      }
    }
  }

  public function deleteCourier($courier_id){
    $this->db->query("DELETE FROM ".DB_PREFIX."courier WHERE courier_id='".(int)$courier_id."'");
  }

  public function addOrderTracking($data){
    $q = $this->db->query("SELECT * FROM ".DB_PREFIX."order_tracking where order_id='".(int)$data['order_id']."'");
    if($q->num_rows > 0){
      $this->db->query("UPDATE ".DB_PREFIX."order_tracking set tracking_code='".$data['tracking_code']."', courier_id='".(int)$data['courier_id']."' where order_id='".(int)$data['order_id']."'");
    } else {
      $this->db->query("INSERT INTO ".DB_PREFIX."order_tracking set order_id='".(int)$data['order_id']."', courier_id='".(int)$data['courier_id']."', tracking_code='".$data['tracking_code']."'");
    }
  }

  public function getOrderTracking($order_id){
    $q = $this->db->query("SELECT * FROM ".DB_PREFIX."order_tracking WHERE order_id='".(int)$order_id."'");
    if($q->num_rows >0){
      return $q->row;
    } else {
      return false;
    }
  }
}
?>
