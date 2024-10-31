<?php
/*
** cfom Existing Meta Template
*/

/* 
**========== Direct access not allowed =========== 
*/ 
if( ! defined('ABSPATH') ) die('Not Allowed');

$all_forms = cfom_product_meta_all();
$howto_video = 'https://najeebmedia.com/cfom';
// cfom_pa($all_forms);
?>

<div class="wrapper">
	<h2 class="cfom-heading-style"><?php _e( 'CFOM Section & Meta', 'cfom'); ?></h2>
	
	<?php
	if( ! cfom_pro_is_installed() ) {
		echo '<a class="text-right" href="'.esc_url(cfom_pro_url()).'" target="_blank">Get PRO Version</a>';
	}
	
	// How to video:
	echo '<a class="text-right" href="'.esc_url($howto_video).'" target="_blank">Quick Video Tutorial</a>';
	?>
	
</div>


<div class="cfom-existing-meta-wrapper">
	
	<form method="post" action="admin-post.php" enctype="multipart/form-data">
		<input type="hidden" name="action" value="cfom_export_meta" />

		<div class="row cfom-product-table-header">
			<span class="pull-right"><strong><?php echo count($all_forms); ?> <?php _e( 'Items', 'cfom' ); ?></strong></span>
			<span class="clear"></span>
		</div>
		<div class="table-responsive">
			<table id="cfom-meta-table" class="table">
				<thead>
					<tr class="bg-info">
						<th><?php _e('Section ID', "cfom")?></th>
						<th><?php _e('Title', "cfom")?></th>
						<th><?php _e('Fields', "cfom")?></th>
						<th><?php _e('Actions', "cfom")?></th>
					</tr>
				</thead>
				<tfoot>
					<tr class="bg-info">
						<th><?php _e('Section ID', "cfom")?></th>
						<th><?php _e('Title', "cfom")?></th>
						<th><?php _e('Fields', "cfom")?></th>
						<th><?php _e('Actions', "cfom")?></th>
					</tr>
				</tfoot>
				
				<?php 
				
				foreach ($all_forms as $cfom_meta):
				// cfom_pa($cfom_meta);
				$url_edit   	= add_query_arg(array('cfom_id'=> $cfom_meta->section_id, 'do_meta'=>'edit'));
				$url_clone  	= add_query_arg(array('cfom_id'=> $cfom_meta->section_id, 'do_meta'=>'clone'));
				$url_products	= admin_url( 'edit.php?post_type=product', (is_ssl() ? 'https' : 'http') );
				$reset_url		= admin_url( 'admin-post.php?action=cfom_set_default_fields&cfom_reset_section='.$cfom_meta->section_type);
				
				$is_active		= $cfom_meta->is_active == 'yes' ? 'no' : 'yes';
				$activate_params= array('action'			=>'cfom_set_active_state',
										'cfom_is_active'	=> $is_active,
										'cfom_section_id'	=> $cfom_meta->section_id,
										'cfom_section_title'=> $cfom_meta->section_title,
										'cfom_section_type'	=> $cfom_meta->section_type);
										
				$active_url		= add_query_arg($activate_params, admin_url( 'admin-post.php'));
				$active_state	= $cfom_meta->is_active == 'yes' ? __('Enabled', 'cfom') : __('Disabled', 'cfom');
				$active_color	= $cfom_meta->is_active == 'yes' ? 'green' : 'red';
				
				// $title_state	= $cfom_meta->show_title == 'yes' ? __('Enabled', 'cfom') : __('Disabled', 'cfom');
				?>
				<tr>
					<td><?php echo $cfom_meta ->section_id; ?></td>
					<td>
						<a href="<?php echo $url_edit?>" style="display: block;">
							<?php echo stripcslashes($cfom_meta -> section_title);
							?>
						</a>
						
						<?php echo '<br>'.$cfom_meta ->section_type; ?>
					</td>
					
					<td><?php echo cfom_admin_simplify_meta($cfom_meta -> cfom_meta)?></td>
					<td>
						<a href="<?php echo esc_url($url_edit); ?>" title="<?php _e('Edit', "cfom")?>" class="button"><span class="dashicons dashicons-edit"></span></a>
						<a href="#" title="<?php _e('Reset', "cfom")?>" class="button cfom-reset-section-js" data-section-type="<?php echo esc_attr($cfom_meta->section_type); ?>" id="cfom-reset-loader-<?php echo esc_attr($cfom_meta->section_type); ?>"><span class="dashicons dashicons-controls-repeat"></span></a>
						<a href="<?php echo esc_url($active_url);?>" title="<?php echo esc_attr($active_state); ?>" class="button"><span style="color:<?php echo $active_color;?>" class="dashicons dashicons-visibility"></span></a>
					</td>
				</tr>
				<?php 
				endforeach;
				?>
			</table>
		</div>
	</form>
</div>