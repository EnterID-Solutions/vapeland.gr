<?php

class Security
{
    private $ip;


    public function __construct($registry)
    {
        $this->config = $registry->get('config');
        $this->db = $registry->get('db');
        $this->request = $registry->get('request');
        if(isset($this->request->server['HTTP_X_FORWARDED_FOR'])){
          $this->ip = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } else {
          $this->ip = $this->request->server['REMOTE_ADDR'];
        }
    }


    public function checkIP()
    {
        $ip_query = $this->db->query("SELECT ip FROM ".DB_PREFIX."ip_ban WHERE ip='".$this->db->escape($this->ip)."'");
        if ($ip_query->num_rows) {
            header('HTTP/1.1 403 Forbidden');
            exit();
        }
    }

    public function checkCustomerIP()
    {
        if ($this->config->get('module_admin_security_status') && $this->ip != $this->request->server['SERVER_ADDR']) {
            $ip_query = $this->db->query("SELECT * FROM ".DB_PREFIX."fail_attempt WHERE ip='".$this->db->escape($this->ip)."'");
            if ($ip_query->num_rows) {
                $this->db->query("UPDATE ".DB_PREFIX."fail_attempt SET attempts = (attempts + 1) WHERE ip='".$this->db->escape($this->ip)."'");
                return array("ip" => $this->ip, "attempts" => ($ip_query->row['attempts'] + 1));
            } else {
                $this->db->query("INSERT INTO ".DB_PREFIX."fail_attempt SET ip='".$this->db->escape($this->ip)."', attempts='1', date_added=NOW()");
                return array("ip" => $this->ip, "attempts" => 1);
            }
        } else {
            return false;
        }
    }

    public function addBan($ip)
    {
      if(!in_array($this->ip, $this->config->get('module_admin_security_whitelist_ips'))){
        $this->db->query("DELETE FROM ".DB_PREFIX."ip_ban WHERE ip='".$this->db->escape($ip)."'");
        $this->db->query("INSERT INTO ".DB_PREFIX."ip_ban SET ip='".$this->db->escape($ip)."', date_added=NOW()");
      }
    }

    public function delBan($ip)
    {
        $this->db->query("DELETE FROM ".DB_PREFIX."ip_ban WHERE ip='".$this->db->escape($ip)."'");
    }

    public function delAttempt($ip)
    {
        $this->db->query("DELETE FROM ".DB_PREFIX."fail_attempt WHERE ip='".$this->db->escape($ip)."'");
    }

    public function getIp()
    {
      return $this->ip;
    }
}
