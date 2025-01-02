<?php 
$flavor_base = $_GET['flavor_base'] ?? '20';
$ml_factor = $_GET['ml_factor'] ?? 3.6;
$flavours_3_v = $_GET['flavours_3_v'] ?? '20';
$flavours_1_v = $_GET['flavours_1_v'] ?? '10,20,30,40';
$flavours_2_v = $_GET['flavours_2_v'] ?? '30,20,10,0';
$flavours_res = $_GET['flavours_res'] ?? '3.3,6.7,10,13.3';
$flavour_lbl0 = $_GET['flavour_lbl0'] ?? 'Pink Mule Nic Booster 10ml 100VG 20mg';
$flavour_lbl1 = $_GET['flavour_lbl1'] ?? 'Velvet Vape Nic Booster VG 18mg';
$flavour_lbl2 = $_GET['flavour_lbl2'] ?? 'Velvet Vape VG Base 100ml';
$flavour_lbl3 = $_GET['flavour_lbl3'] ?? 'Βάση PG και άρωμα';
$flavour_lblr = $_GET['flavour_lblr'] ?? 'Συνολικό αποτέλεσμα περιεκτικότητας νικοτίνης στα 60ml';

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>
<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
<link rel="stylesheet" href="css/bottle_app.css?v=1" />

<body style="background:white;">
	<div class="bottle_section">
		<div class="bottle_ui">
			<div class="bottle_shape"></div>
			<div class="flavour_0"></div>
			<div class="flavour_1"></div>
			<div class="flavour_2"></div>
			<div class="bottle_results">
				<div>
					<div class="flavour_0_lbl"><span></span> <p></p></div>
					<div class="flavour_2_lbl"><span></span> <p></p></div>
					<div class="flavour_1_lbl"><span></span> <p></p></div>
					<div class="flavour_3_lbl"><span></span> <p></p></div>
					<div class="flavour_r_lbl"><span></span> <p></p></div>
				</div>
			</div>
		</div>
		<div id="bottle_slider"></div>
	</div>
	<div style="margin-top:60px;">
	    <form>
	        <div>
    	        <label for="flavor_base">Flavor Base</label>
    	        <input type="text" name="flavor_base" value="<?php echo $flavor_base;?>">	            
	        </div>
	        <div>
    	        <label for="ml_factor">
    	            ML factor
    	        </label>
    	        <input type="text" name="ml_factor" value="<?php echo $ml_factor;?>">   
	        </div>	        
	        <div>
    	        <label for="flavours_res">
    	            Σκάλες slider χωρισμένες με κόμμα
    	        </label>
    	        <input type="text" name="flavours_res" value="<?php echo $flavours_res;?>">   
	        </div>
	        <hr>
	        <div>
    	        <label for="flavour_lbl1">
    	            Flavor 1 Label
    	        </label>
    	        <input type="text" name="flavour_lbl1" value="<?php echo $flavour_lbl1;?>">   
	        </div>		        
	        <div>
    	        <label for="flavours_1_v">
    	            Flavor 1 Τιμές χωρισμένες με κόμμα (όσες και οι σκάλες του slider)
    	        </label>
    	        <input type="text" name="flavours_1_v" value="<?php echo $flavours_1_v;?>">   
	        </div>
	        <hr>
	        <div>
    	        <label for="flavour_lbl2">
    	            Flavor 2 Label
    	        </label>
    	        <input type="text" name="flavour_lbl2" value="<?php echo $flavour_lbl2;?>">   
	        </div>	        
	        <div>
    	        <label for="flavours_2_v">
    	            Flavor 2 Τιμές χωρισμένες με κόμμα (όσες και οι σκάλες του slider)
    	        </label>
    	        <input type="text" name="flavours_2_v" value="<?php echo $flavours_2_v;?>">   
	        </div>
	        <hr>
	        <div>
    	        <label for="flavour_lbl3">
    	            Flavor 3 Label
    	        </label>
    	        <input type="text" name="flavour_lbl3" value="<?php echo $flavour_lbl3;?>">   
	        </div>	        
	        <div>
    	        <label for="flavours_3_v">
    	            Flavor 3 Τιμή
    	        </label>
    	        <input type="text" name="flavours_3_v" value="<?php echo $flavours_3_v;?>">   
	        </div>
	        <hr>
	        <div>
    	        <label for="flavour_lblr">
    	            Label Συνολικού Αποτελέσματος
    	        </label>
    	        <input type="text" name="flavour_lblr" value="<?php echo $flavour_lblr;?>">   
	        </div>	        
            <input type="submit" value="Υποβολή">
	    </form>
	</div>
	<script type="text/javascript">
		var flavor_base  = '<?php echo $flavor_base;?>';
		var flavours_3_v = '<?php echo $flavours_3_v;?>';
		var flavours_1_v = '<?php echo $flavours_1_v;?>';
		var flavours_2_v = '<?php echo $flavours_2_v;?>';
		var flavours_res = [
		    <?php foreach(explode(',', $flavours_res) as $key => $value) {?>
		    '<?php echo $value?>',
		    <?php }?>     
		];
		var flavour_lbl0 = '<?php echo $flavour_lbl0;?>';
		var flavour_lbl1 = '<?php echo $flavour_lbl1;?>';
		var flavour_lbl2 = '<?php echo $flavour_lbl2;?>';
		var flavour_lbl3 = '<?php echo $flavour_lbl3;?>';
		var flavour_lblr = '<?php echo $flavour_lblr;?>';

		var flavours_1_v = flavours_1_v.split(',');
		var flavours_2_v = flavours_2_v.split(',');
		var ml_factor 	 = <?php echo $ml_factor;?>;
	</script>
	<script src="js/bottle_app.js"></script>
</body>
</html>
