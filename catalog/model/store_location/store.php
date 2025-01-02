<?php
class ModelStoreLocationStore extends Model {

    public function getStores(){
      $q = $this->db->query("SELECT * FROM ".DB_PREFIX."store_location s LEFT JOIN ".DB_PREFIX."store_location_description sd ON sd.store_location_id = s.store_location_id WHERE s.status='1' AND sd.language_id='" . (int)$this->config->get('config_language_id') ."' ORDER BY s.sort_order ASC");

      return $q->rows;
    }

    public function getStoreTips($store_location_id){
      $q = $this->db->query("SELECT * FROM ".DB_PREFIX."store_location_tip where store_location_id='".(int)$store_location_id."' AND language_id='".$this->config->get('config_language_id')."'");

      if($q->num_rows > 0){
        return $q->rows;
      } else {
        return false;
      }
    }

    public function getStoreLocation($store_location_id){
      $q = $this->db->query("SELECT * FROM ".DB_PREFIX."store_location s LEFT JOIN ".DB_PREFIX."store_location_description sd ON sd.store_location_id = s.store_location_id WHERE s.store_location_id='".(int)$store_location_id."' AND sd.language_id='".$this->config->get('config_language_id')."' AND s.status='1'");

      return $q->row;
    }

    public function getOpeningTimes($store_location_id, $day){
      $q = $this->db->query("SELECT * FROM ".DB_PREFIX ."store_location_times WHERE store_location_id='".(int)$store_location_id."' AND day='".$this->db->escape($day)."'");

      if($q->num_rows > 0){
        return $q->rows;
      } else {
        return false;
      }
    }

    public function getStoreImages($store_loation_id){
      $q = $this->db->query("SELECT * FROM " .DB_PREFIX ."store_location_image WHERE store_location_id='".$store_loation_id."'  ORDER BY sort_order ASC");

      return $q->rows;
    }
}
?>
