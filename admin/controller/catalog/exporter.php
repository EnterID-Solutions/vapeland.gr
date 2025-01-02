<?php
require_once(DIR_SYSTEM.'phpoffice/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControllerCatalogExporter extends Controller {

  public function index(){
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="product_list_'.date("Y-m-d_His").'.xlsx"');
    header("Expires: 0");

    if(isset($this->request->post['selected'])){
      $products = $this->getProducts($this->request->post['selected']);
    } else {
      $products = $this->getProducts();
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Product_id');
    $sheet->setCellValue('B1', 'Product Name');
    $sheet->setCellValue('C1', 'Model');
    $sheet->setCellValue('D1', 'Price');
    $sheet->setCellValue('E1', 'Special');
    $sheet->setCellValue('F1', 'Quantity');
    $sheet->setCellValue('G1', 'Status');

		 $i=2;
		 foreach ($products as $product){
       $sheet->setCellValue('A'.$i, $product['product_id']);
       $sheet->setCellValue('B'.$i, $product['name']);
       $sheet->setCellValue('C'.$i, $product['model']);
       $sheet->setCellValue('D'.$i, $product['price']);
       $sheet->setCellValue('E'.$i, $product['special']);
       $sheet->setCellValue('F'.$i, $product['quantity']);
       $sheet->setCellValue('G'.$i, (($product['status'])? 'Ενεργοποιημένο':'Απενεργοποιημένο'));
       $i++;
		 }
     $sheet->getColumnDimension('A')->setAutoSize(true);
     $sheet->getColumnDimension('B')->setAutoSize(true);
     $sheet->getColumnDimension('C')->setAutoSize(true);
     $sheet->getColumnDimension('D')->setAutoSize(true);
     $sheet->getColumnDimension('E')->setAutoSize(true);
     $sheet->getColumnDimension('F')->setAutoSize(true);
     $sheet->getColumnDimension('G')->setAutoSize(true);

     $writer = new Xlsx($spreadsheet);
     $writer->save('php://output');
  }


  private function getProducts($selected=array()){

    $sql = "SELECT p.product_id, pd.name, p.model,p.price, p.quantity, p.status,";
    $sql .= " (SELECT ps.price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special";
    $sql .= " FROM ".DB_PREFIX."product p LEFT JOIN " .DB_PREFIX."product_description pd ON (pd.product_id = p.product_id) WHERE pd.language_id = '".(int)$this->config->get('config_language_id')."' ORDER BY pd.name ASC";
    if($selected){
      $sql .= " AND p.product_id IN (".implode(',', $selected).")";
    }

    $query = $this->db->query($sql);
    return $query->rows;
  }
}
