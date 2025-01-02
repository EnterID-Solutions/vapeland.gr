<table border="1">
	<thead style="font-weight:bold;">
		<tr>
		<td><strong>ID</strong></td>
		<td><strong>Product ID</strong></td>
		<td><strong>Product Name</strong></td>
		<td><strong>Option Out of Stock</strong></td>
		<td><strong>Selected Options</strong></td>
		<td><strong>Quantity</strong></td>
		<td><strong>Customer Email</strong></td>
		<td><strong>Customer Name</strong></td>
		<td><strong>Customer Phone</strong></td>
		<td><strong>Customer IP</strong></td>
		<td><strong>Customer Comment</strong></td>
		<td><strong>Customer Language ID</strong></td>
		<td><strong>Customer Language</strong></td>
		<td><strong>Store ID</strong></td>
		<td><strong>Store URL</strong></td>
		<td><strong>Enquiry Date</strong></td>
		<td><strong>Notified Date</strong></td>
	  </tr>
	</thead>
	<tbody>
	  <?php if ($products) { ?>
	  <?php foreach ($products as $product) { ?>
	  <tr>
		<td><?php echo $product['id']; ?></td>
		<td><?php echo $product['product_id']; ?></td>
		<td><?php echo $product['name']; ?></td>
		<td><?php echo $product['selected_option']; ?></td>
		<td><?php echo $product['all_selected_option']; ?></td>
		<td><?php echo $product['qty']; ?></td>
		<td><?php echo $product['email']; ?></td>
		<td><?php echo $product['fname']; ?></td>
		<td><?php echo $product['phone']; ?></td>
		<td><?php echo $product['ip']; ?></td>
		<td><?php echo $product['comment']; ?></td>
		<td><?php echo $product['language_id']; ?></td>
		<td><?php echo $product['language_name']['name']; ?></td>
		<td><?php echo $product['store_id']; ?></td>
		<td><?php echo $product['store_url']; ?></td>
		<td><?php echo $product['enquiry_date']; ?></td>
		<td><?php echo $product['notify_date']; ?></td>
	  </tr>
	  <?php } ?>
	  <?php } else { ?>
	  <tr>
		<td class="center" colspan="17"><?php echo $text_no_results; ?></td>
	  </tr>
	  <?php } ?>
	</tbody>
</table>
	         