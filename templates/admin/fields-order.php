<?php 
/*
** CFOM Order Meta Template
*/

/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH' ) ){ exit; }

$cfom_admin_url = admin_url('admin-post.php');
$cfom_all_section_url = add_query_arg(array('action'=>'cfom_print_pdf','section'=>'all', 'order_id'=> $order_id), $cfom_admin_url);
?>

<div class="cfom-fields-order-wrapper">
	
<?php
/**
 * Rendering PDF meta against order
 * 
 **/
 
if ($order_meta) {
	foreach ($order_meta as $field_section => $order_field_meta) {
		
		// ppom_pa($order_field_meta);
		
		$cfom_settings = cfom_get_settings_by_type($field_section);
		
		$section_title = $cfom_settings->section_title;

		$cfom_admin_url = add_query_arg(array('action'=>'cfom_print_pdf','section'=>$field_section, 'order_id'=> $order_id), $cfom_admin_url);
	?>
	<div class="cfom-order-inner-table">

		<?php if ($context != 'checkout' && cfom_pro_is_installed() ): ?>
		<span class="cfom-print-pdf" title="<?php _e('Print PDF' , 'ppom'); ?>">
			<a href="<?php echo esc_url($cfom_admin_url);?>" class="cfom-print-js"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>
		</span>
		<?php endif ?>
		<h3 class="cfom-order-section-title"><?php echo $section_title?></h3>
		<table class="cfom-order-table table-hover" style="width:100%;">

			<thead class="thead-light">
				<tr>
					<th><?php _e('Field Title' , 'ppom'); ?></th>
					<th><?php _e('Field Value' , 'ppom'); ?></th>
				</tr>
			</thead>
			<tbody class="cfom-order-table-tbody">
			<?php
			foreach ($order_field_meta as $data_name => $meta) {
				
				$field_meta = cfom_get_field_meta_by_dataname( $data_name );
				
				$the_title = isset($meta['title']) ? $meta['title'] : '';
				$the_value = isset($meta['value']) ? $meta['value'] : '';
				$display_value = cfom_get_meta_display($data_name, $the_value, $field_meta, $order_id);
			?>
				<tr class="cfom-order-table-tbody-tr">
					<td class="cfom-order-table-tbody-td" style="border-top: 2px solid #cececeb0;"><?php echo $the_title; ?></td>
					<td class="cfom-order-table-tbody-td" style="border-top: 2px solid #cececeb0;"><?php echo $display_value; ?></td>
				</tr>
			<?php
			}
			?>
			</tbody>
		</table>
	</div>
	<?php
	}
	?>
	<?php if ($context != 'checkout' && cfom_pro_is_installed() ): ?>
	<span title="<?php _e('Print All Section PDF', 'ppom'); ?>">
		<a href="<?php echo esc_url($cfom_all_section_url); ?>" class="button" style="margin-top: 4px;"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a>
	</span>
	<div class="clearfix"></div>
	<?php endif ?>
<?php
	}else{
		?> <h2 class="cfom-section-not-available"><?php _e('No Section Detail Available' , 'ppom'); ?></h2>	<?php
	}
?>
</div>