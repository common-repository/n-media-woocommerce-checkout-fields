<?php 
/**
 * Print PDF Settings Template
**/


/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH' ) ){ exit; }

$header_html = get_option('cfom_pdf_header_meta') != '' ? get_option('cfom_pdf_header_meta') : '';
$footer_html = get_option('cfom_pdf_footer_meta') != '' ? get_option('cfom_pdf_footer_meta') : '';

?>

<div class="cfom-pdf-setting-wrapper">

	<button type="button" class="btn btn-info" data-modal-id="cfom_pdf_setting_modal"><?php _e('PDF Settings', 'cfom'); ?></button>

	<div id="cfom_pdf_setting_modal" class="cfom-modal-box" style="display: none;">
	    <header> 
	        <h3><?php _e('PDF Settings', 'cfom'); ?></h3>
	    </header>

	    <div class="cfom-modal-body">
	    	<form class="cfom-save-pdf-settings">
	    		<div class="cfom-pdf-setting-box">
	    			
	    			<input type="hidden" name="action" value="cfom-save-pdf-setting-meta">
	    			
	    			<div class="form-group">
		    			<label><?php _e('PDF Header', 'cfom'); ?>
		    				<span class="cfom-helper-icon" data-cfom-tooltip="cfom_tooltip" title="<?php _e('Add HTML code here for pdf header and add inline css for styling.', "cfom")?>" ><i class="dashicons dashicons-editor-help"></i></span>
		    			</label>
						<textarea class="form-control" name="cfom-pdf-header-html"><?php echo $header_html ?></textarea>
					</div>

					<div class="form-group">
						<label><?php _e('PDF Footer', 'cfom'); ?>
							<span class="cfom-helper-icon" data-cfom-tooltip="cfom_tooltip" title="<?php _e('Add HTML code here for pdf footer and add inline css for styling.', "cfom")?>" ><i class="dashicons dashicons-editor-help"></i></span>
						</label>
						<textarea name="cfom-pdf-footer-html" class="form-control"><?php echo $footer_html ?></textarea>
					</div>

					<div class="cfom-pdf-save-btn text-right">
						<span class="cfom-pdf-setting-alert"></span>
						<input type="submit" value="Save Settings" class="btn btn-primary">
					</div>
	    		</div>
	    	</form>
	    </div>
	    
	    <footer>
	    	<button type="button" class="btn btn-default close-model cfom-js-modal-close"><?php _e('Close' , 'cfom'); ?></button>
	    </footer>
	</div>
</div>