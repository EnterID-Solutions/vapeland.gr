<?php

class ControllerToolCgroup extends Controller {

  public function index(){
    echo "CGroup Controller";
  }

  public function update(){
    $p = $this->db->query("SELECT product_id FROM ".DB_PREFIX."product");

    foreach ($p->rows as $pd) {
      //$this->db->query("INSERT INTO ".DB_PREFIX."product_to_customer_group SET product_id='".(int)$pd['product_id']."', customer_group_id='27'");
    }
  }
}
