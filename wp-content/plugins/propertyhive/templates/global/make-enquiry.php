<?php
/**
 * Make enquiry action, plus lightbox form
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;
?>

<li class="action-make-enquiry">
    
    <a data-fancybox data-src="#makeEnquiry<?php echo $post->ID; ?>" href="javascript:;"><?php _e( 'Make Enquiry', 'propertyhive' ); ?></a>

    <!-- LIGHTBOX FORM -->
    <div id="makeEnquiry<?php echo $post->ID; ?>" style="display:none;">
        
        <h2><?php _e( 'Make Enquiry', 'propertyhive' ); ?></h2>
        
        <p><?php _e( 'Please complete the form below and a member of staff will be in touch shortly.', 'propertyhive' ); ?></p>
        
        <?php propertyhive_enquiry_form(); ?>
        
    </div>
    <!-- END LIGHTBOX FORM -->
    
</li>

