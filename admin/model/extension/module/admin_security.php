<?php

class ModelExtensionModuleAdminSecurity extends Model
{

  public function install(){
    $this->db->query("DROP TABLE IF EXISTS ".DB_PREFIX."ip_ban");
    $this->db->query("DROP TABLE IF EXISTS ".DB_PREFIX."fail_attempt");
    
    $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ip_ban` (
      `user_ban_id` int(11) NOT NULL AUTO_INCREMENT,
      `ip` varchar(255) NOT NULL DEFAULT '',
      `date_added` datetime,
      PRIMARY KEY (`user_ban_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1");

    $this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."fail_attempt` (
      `fail_attempt_id` int(11) NOT NULL AUTO_INCREMENT,
      `ip` varchar(255) NOT NULL,
      `attempts` smallint(2) NOT NULL DEFAULT 0,
      `date_added` datetime DEFAULT NULL,
      PRIMARY KEY (`fail_attempt_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1");

  }

  public function uninstall(){
    //$this->db->query("DROP TABLE IF EXISTS ".DB_PREFIX."ip_ban");
    //$this->db->query("DROP TABLE IF EXISTS ".DB_PREFIX."fail_attempt");
  }


  public function getBanned($data){
    $q = "SELECT * FROM ".DB_PREFIX."ip_ban";

    if(isset($data['filter_ip'])){
      $q .= " WHERE `ip` LIKE '".$this->db->escape($data['filter_ip'])."%'";
    }

    if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$q .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
    $query = $this->db->query($q);

    return $query->rows;
  }

  public function getBannedTotal($data){

    $q = "SELECT COUNT(user_ban_id) AS total FROM ".DB_PREFIX."ip_ban";

    if(!empty($data['filter_ip'])){
      $q .= " WHERE `ip` LIKE '".$this->db->escape($data['filter_ip'])."%'";
    }

    $query = $this->db->query($q);

    return $query->row['total'];
  }


  public function deleteIp($ip_id){
    $ip = $this->db->query("SELECT ip FROM ".DB_PREFIX."ip_ban WHERE user_ban_id='".(int)$ip_id."'");
    $this->db->query("DELETE FROM " . DB_PREFIX . "fail_attempt WHERE ip = '" . $this->db->escape($ip->row['ip']) . "'");
    $this->db->query("DELETE FROM " . DB_PREFIX . "ip_ban WHERE ip = '" . $this->db->escape($ip->row['ip']) . "'");
  }

}
