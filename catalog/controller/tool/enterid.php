<?php
class ControllerToolEnterid extends Controller {

  public function index(){
    echo "Enterid Controller";
  }

  public function update(){

    $pr = $this->db->query("SELECT product_id, quantity FROM ".DB_PREFIX."product_old");

    foreach ($pr->rows as $row) {
      //$this->db->query("UPDATE ".DB_PREFIX."product SET quantity='".(int)$row['quantity']."' WHERE product_id='".(int)$row['product_id']."'");
    }

    echo "Done";
  }
}
