<?php 
/*
** CFOM New Form Meta
*/

/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH' ) ){ exit; }

// get class instance
$form_meta = CFOM_FIELDS_META();

$section_title		= '';
$is_active			= '';
$section_type	  	= '';
$cfom_css	 		= '';
$cfom_meta 			= '';
$cfom_meta_id		= 0;
$conditions			= '';
$show_title			= '';
$cfom_field_index = 1;

$all_input = $form_meta->fields_meta_array();

if (isset ( $_REQUEST ['cfom_id'] ) && $_REQUEST ['do_meta'] == 'edit') {
	
	$cfom_meta_id	= intval( $_REQUEST ['cfom_id'] );
	$cfom_settings	= cfom_get_settings_by_id( $cfom_meta_id	);

	$section_title 		= (isset($cfom_settings -> section_title) ? stripslashes($cfom_settings->section_title) : '');
	$is_active			= (isset($cfom_settings -> is_active) ? $cfom_settings -> is_active : 'no');
    $section_type	  	= (isset($cfom_settings -> section_type) ? $cfom_settings -> section_type : '');
    $cfom_css	 		= (isset($cfom_settings -> cfom_css	) ? $cfom_settings -> cfom_css	 : '');
	$conditions			= (isset($cfom_settings -> conditions) ? $cfom_settings -> conditions : '');
	$show_title			= (isset($cfom_settings -> show_title) ? $cfom_settings -> show_title : '');
	$cfom_meta 			= json_decode ( $cfom_settings->cfom_meta, true );

	$the_section_name = cfom_get_section_default_title($section_type);
}
$url_cancel = add_query_arg(array('action'=>false,'cfom_id'=>false, 'do_meta'=>false));
	
echo '<p><a class="btn btn-primary" href="'.$url_cancel.'">'.__('&laquo; All Sections', "cfom").'</a></p>';
// cfom_pa($cfom_settings);
?>

<div class="cfom-admin-fields-wrapper">

	<!-- Extra fields inputs model -->
	<div id="cfom_fields_model_id" class="cfom-modal-box cfom-fields-name-model">
	    <header> 
	        <h3><?php _e('Select Field', 'cfom'); ?></h3>
	    </header>
	    <div class="cfom-modal-body">
	        <ul class="list-group list-inline">
                <?php
                foreach ( $all_input as $field_type => $meta ) {

                	if( $meta != NULL ){
   	
                    	$fields_title = isset($meta['title']) ? $meta['title'] : '';
                    	$fields_icon  = isset($meta['icon']) ? $meta['icon'] : '';
                    ?> 
	                    <li class="cfom_select_field list-group-item"  data-field-type="<?php echo esc_attr($field_type); ?>" >
	                        <span class="cfom-fields-icon">
	                        	<?php echo $fields_icon;  ?>
	                        </span>
	                        <span>
	                            <?php echo $fields_title;  ?>
	                        </span>
	                    </li>
                    <?php 
            		}
                }
                ?>
            </ul>
	    </div>
	    <footer>
	    	<button type="button" class="btn btn-default close-model cfom-js-modal-close"><?php _e('Close' , 'cfom'); ?></button>
	    </footer>
	</div>
	
	
	<div class="cfom-main-field-wrapper">
		<form class="cfom-save-fields-meta">

			<?php if ($cfom_meta_id != 0){ ?>
			<input type="hidden" name="action" value="cfom_update_form_meta">
			<?php }else{ ?>
			<input type="hidden" name="action" value="cfom_save_form_meta">
			<?php } ?>
			<input type="hidden" name="cfom_id" value="<?php echo esc_attr($cfom_meta_id); ?>" >
			<input type="hidden" name="section_type" value="<?php echo esc_attr($section_type); ?>" >
			

			<div class="cfom-basic-setting-section">
				<h2 class="cfom-heading-style">
				<?php _e('CFOM Settings', "cfom"); ?>
				<span class="cfom-section-title"><?php echo '(' . $the_section_name . ')'; ?></span>
				</h2>
				<div class="row">
					<div class="col-md-6 col-sm-6">
						<div class="cfom-section-enabled">
							<label>
								<span><?php _e('Enable Section', "cfom"); ?></span>
	                         	<span class="cfom-helper-icon" data-cfom-tooltip="cfom_tooltip" title="<?php _e('Option to Enable/Disable Fields without Delete.', "cfom")?>" ><i class="dashicons dashicons-editor-help"></i></span>
							</label>
						</div>
						<div class="btn-group cfom-switch" data-toggle="buttons">
							<label class="btn btn-default btn-on btn-sm">
								<input type="radio" value="yes" name="is_active" class="cfom-toggle-switch-js" <?php checked($is_active, 'yes')?> > <?php _e('Yes' , 'cfom'); ?>
							</label>
							<label class="btn btn-default btn-off col-md-6 btn-sm">
								<input type="radio" value="no" name="is_active" class="cfom-toggle-switch-js" <?php checked($is_active, 'no')?> > <?php _e('NO' , 'cfom'); ?>
							</label>
					    </div>
					    
					    <!--<div class="cfom-section-is_active">
							<label>
								<span><?php _e('Show Title', "cfom"); ?></span>
	                         	<span class="cfom-helper-icon" data-cfom-tooltip="cfom_tooltip" title="<?php _e('Title before Section?', "cfom")?>" ><i class="dashicons dashicons-editor-help"></i></span>
							</label>
						</div>
						<div class="btn-group cfom-switch" data-toggle="buttons">
							<label class="btn btn-default btn-on btn-sm">
								<input type="radio" value="yes" name="show_title" class="cfom-toggle-switch-js" <?php checked($show_title, 'yes')?> > <?php _e('Yes' , 'cfom'); ?>
							</label>
							<label class="btn btn-default btn-off col-md-6 btn-sm">
								<input type="radio" value="no" name="show_title" class="cfom-toggle-switch-js" <?php checked($show_title, 'no')?> > <?php _e('NO' , 'cfom'); ?>
							</label>
					    </div>-->

					</div>
					<div class="col-md-6 col-sm-6">
						
						<div class="form-group">
							<label><?php _e('Meta group name', "cfom"); ?>
	                         	<span class="cfom-helper-icon" data-cfom-tooltip="cfom_tooltip" title="<?php _e('For your reference.', "cfom")?>" ><i class="dashicons dashicons-editor-help"></i></span>
	                     	</label>
							<input type="text" class="form-control" name="section_title" value="<?php echo $section_title?>">
						</div>
					</div>
					
					<div class="col-md-6 col-sm-6">
						<div class="form-group">
							<label><?php _e('Custom CSS', "cfom"); ?>
	                         	<span class="cfom-helper-icon" data-cfom-tooltip="cfom_tooltip" title="<?php _e('Add your own CSS.', "cfom")?>" ><i class="dashicons dashicons-editor-help"></i></span>
	                     	</label>
							<textarea id="cfom-css-editor" class="form-control" name="cfom_css"><?php echo stripslashes($cfom_css)?></textarea>
						</div>
					</div>
					
				</div>
				
			</div>

		    <!-- saving all fields via model -->
		    <div class="cfom_save_fields_model">
		        <?php 
		        if ( $cfom_meta ) {

		            $f_index = 1;
		            foreach ($cfom_meta as $field_index => $field_meta) {

		            	$field_type      = isset($field_meta['type']) ? $field_meta['type'] : '';
                        $the_title       = isset($field_meta['title']) ? $field_meta['title'] : '';
                        $the_field_id    = isset($field_meta['data_name']) ? $field_meta['data_name'] : '';
                        $the_placeholder = isset($field_meta['placeholder']) ? $field_meta['placeholder'] : '';
                        $enable_field    = isset($field_meta['enable_field']) ? $field_meta['enable_field'] : '';
                        $defualt_fields  = isset($all_input[$field_type]['field_meta']) ? $all_input[$field_type]['field_meta'] : array();
                        $is_core_fields  = cfom_is_checkout_core_field($the_field_id, $section_type);
                        $core_fields_key = ''
		        ?>
		                <!-- New CFOM Model  -->
		                <div id="cfom_field_model_<?php echo esc_attr($f_index); ?>" class="cfom-modal-box cfom-slider cfom_sort_id_<?php echo esc_attr($f_index); ?>">
						    <div class="cfom-model-content">
						    	
							    <header> 
							        <h3>
							        	<?php echo $field_type; ?>
							        	<span class="cfom-dataname-reader">(<?php echo $the_field_id; ?>)</span>
							        </h3>
							        <?php 
	                                if ($is_core_fields) {
	                                	$core_fields_key = 'core_field_meta';
	                                ?>
	                                <div class="cfom-checkboxe-style cfom-core-enable-fields">
										<label>
											<input type="checkbox" name="cfom[<?php echo esc_attr($f_index); ?>][enable_field]" <?php checked( $enable_field, 'on', true ); ?> >
											<span>Enable Field</span>
										</label>
				                    </div>
				                    <?php
				                    }
				                    ?>
							    </header>
							    <div class="cfom-modal-body">
							        <?php
			                            echo $form_meta->render_field_meta($defualt_fields, $field_type, $f_index, $field_meta, $section_title, $core_fields_key);
		                        	?>
							    </div>
							    <footer> 
							        <span class="cfom-req-field-id"></span>
	                                <button type="button" class="btn btn-default close-model cfom-js-modal-close"><?php _e('Close', 'cfom'); ?></button>
	                                <button class="btn btn-primary cfom-update-field" data-field-index='<?php echo esc_attr($f_index); ?>' data-field-type='<?php echo esc_attr($field_type); ?>' ><?php _e('Update Field', 'cfom'); ?></button> 
							    </footer>
						    <?php 
	                        $cfom_field_index = $f_index;
	                        $cfom_field_index++;
	                        $f_index++;
	                        ?> 
							</div>
						</div>
		            <?php
		            }
		        }

		        echo '<input type="hidden" id="field_index" value="'.esc_attr($cfom_field_index).'">';
		        ?>
		    </div>

		    <!-- all fields append on table -->
		    <div class="table-responsive"> 
		    	<h2 class="cfom-heading-style"><?php _e('Add CFOM Fields', "cfom"); ?></h2>  
		        <table class="table cfom_field_table  table-striped">
		            <thead>
		                <tr>            
		                    <th colspan="6">
		                        <button type="button" class="btn btn-primary" data-modal-id="cfom_fields_model_id"><?php _e('Add Fields', 'cfom'); ?></button>
		                        <button type="button" class="btn btn-danger cfom_remove_field"><?php _e('Remove', 'cfom'); ?></button>
		                    </th>  
		                </tr>
		                <tr class="cfom-thead-bg">
		                    <th></th>
		                     <th class="cfom-check-all-field cfom-checkboxe-style">
								<label>
									<input type="checkbox">
									<span></span>
								</label>
		                    </th>
		                    <th><?php _e('Data Name', 'cfom'); ?></th>
		                    <th><?php _e('Type', 'cfom'); ?></th>
		                    <th><?php _e('Title', 'cfom'); ?></th>
		                    <th><?php _e('Placeholder', 'cfom'); ?></th>
		                    <th><?php _e('Condition', 'cfom'); ?></th>
		                    <th><?php _e('Required', 'cfom'); ?></th>
		                    <th><?php _e('Actions', 'cfom'); ?></th> 
		                </tr>                       
		            </thead>
		            <tfoot>
		                <tr class="cfom-thead-bg">
		                    <th></th>
		                    <th class="cfom-check-all-field cfom-checkboxe-style">
								<label>
									<input type="checkbox">
									<span></span>
								</label>
		                    </th>
		                    <th><?php _e('Field ID', 'cfom'); ?></th>
		                    <th><?php _e('Type', 'cfom'); ?></th>
		                    <th><?php _e('Title', 'cfom'); ?></th>
		                    <th><?php _e('Placeholder', 'cfom'); ?></th>
		                    <th><?php _e('Condition', 'cfom'); ?></th>
		                    <th><?php _e('Required', 'cfom'); ?></th>
		                    <th><?php _e('Actions', 'cfom'); ?></th>
		                </tr>
		                <tr>            
		                    <th colspan="12">
		                        <div class="cfom-submit-btn text-right">
		                        	<span class="cfom-meta-save-notice"></span>
		                            <input type="submit" class="btn btn-primary" value="Save Settings">
		                        </div>
		                    </th>
		                </tr> 
		            </tfoot>
		            <tbody>
	                <?php 
	                if ( $cfom_meta ) {
						
	                    $f_index = 1;
	                    // cfom_pa($cfom_meta);
	                    foreach ($cfom_meta as $field_index => $field_meta) {
							$field_meta['user_code']  = 'JOY' ;
                            $field_type      = isset($field_meta['type']) ? $field_meta['type'] : '';
                            $the_title       = isset($field_meta['title']) ? $field_meta['title'] : '';
                            $the_field_id    = isset($field_meta['data_name']) ? $field_meta['data_name'] : '';
                            $the_placeholder = isset($field_meta['placeholder']) ? $field_meta['placeholder'] : '';
                            $the_required    = isset($field_meta['required']) ? $field_meta['required'] : '';
                            $enable_field    = isset($field_meta['enable_field']) ? $field_meta['enable_field'] : '';
                            $condition_enable = isset($field_meta['logic']) ? $field_meta['logic'] : '';
                            
                            if ($the_required == 'on' ) {
                                $_ok = 'Yes';
                            }else{
                                $_ok = 'No';
                            }
                            
                            if ($condition_enable == 'on' ) {
                                $condition_enable = 'Yes';
                            }else{
                                $condition_enable = 'No';
                            }
                            
                            $disabled_title = '';
                            if ($enable_field == 'on' && cfom_is_checkout_core_field($the_field_id, $section_type) ) {
                                $enable_field = 'Yes';
                            }else if(!cfom_is_checkout_core_field($the_field_id, $section_type)){
                                $enable_field = '';
                            }else{
                            	$enable_field   = 'No';
                            	$disabled_title = 'Field Disabled';
                            }
	                ?>
	                        
	                        <tr class="row_no_<?php echo esc_attr($f_index); ?>" id="cfom_sort_id_<?php echo esc_attr($f_index); ?>" data-disabled-field="<?php echo esc_attr($enable_field); ?>" title="<?php echo esc_attr($disabled_title); ?>">
                                <td class="cfom-sortable-handle">
                                    <i class="fa fa-arrows" aria-hidden="true"></i>
                                </td>
                                <td class="cfom-check-one-field cfom-checkboxe-style">
	                                <?php 
	                                if (!cfom_is_checkout_core_field($the_field_id, $section_type)) {
	                                ?>
                                	<label>
										<input type="checkbox" value="<?php echo esc_attr($f_index); ?>">
										<span></span>
									</label>
									<?php 
	                                }else{
	                                	?>
	                                	<span class="cfom-ban-core-fields">
	                                		<i class="fa fa-ban" aria-hidden="true"></i>
	                                	</span>
	                                	<?php
	                                }
									?>
                                </td>
                                
                                <td class="cfom_meta_field_id"><?php echo $the_field_id; ?></td>
                                <td class="cfom_meta_field_type"><?php echo $field_type; ?></td>
                                <td class="cfom_meta_field_title"><?php echo $the_title; ?></td>
                                <td class="cfom_meta_field_plchlder"><?php echo $the_placeholder; ?></td>
                                <td class="cfom_meta_field_condition"><?php echo $condition_enable; ?></td>
                                <td class="cfom_meta_field_req"><?php echo $_ok; ?></td> 
                                <td>
                                    <button class="btn cfom-edit-field" data-modal-id="cfom_field_model_<?php echo esc_attr($f_index); ?>" id="<?php echo esc_attr($f_index); ?>" title="<?php _e('Edit Field','cfom'); ?>"><i class="fa fa-pencil" aria-hidden="true"></i></button>
                                </td>
	                        </tr> 
	                        <?php   
	                        $cfom_field_index = $f_index;
	                        $cfom_field_index++;
	                        $f_index++;
	                    }
	                }
	            			?>
	            	</tbody>
		        </table>
		    </div>
		</form>
	</div>
</div>

<br><p><a class="btn btn-primary" href="<?php echo esc_url($url_cancel); ?>"><?php echo __('&laquo; All Section', "cfom"); ?></a></p>


<div class="checker" style="display: none;">
    <?php  $form_meta->render_field_settings( ); ?>
</div>