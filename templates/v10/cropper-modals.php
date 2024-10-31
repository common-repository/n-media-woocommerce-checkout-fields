<?php
/**
 * Images Cropping modal html
 * */
if( ! defined('ABSPATH') ) die('Not Allowed.');

$modal_id = 'modalCrop_'.$file_id;
?>

<div class="modal cfom-modals fade" id="<?php echo esc_attr($modal_id)?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo esc_attr($image_title);?>" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo $image_title?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
         <img class="cfom-cropped-image" src="">   
      </div>
      
    </div>
  </div>
</div>