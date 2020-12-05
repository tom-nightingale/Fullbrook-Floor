<?php
/**
 * Single Property Actions (Make Enquiry etc)
 * Editable through use of the filter propertyhive_single_property_actions
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;
?>
<div class="property_actions">

	<?php do_action( 'propertyhive_property_actions_start' ); ?>
    
    <ul>
        
        <?php 
           /**
             * propertyhive_single_property_summary hook
             *
             * @hooked propertyhive_make_enquiry_button - 10
             * 
             */
            do_action( 'propertyhive_property_actions_list_start' ); 
        ?>
        
    	<?php
    	   foreach ($actions as $action)
           {
               $action['class'] = ( isset( $action['class'] ) ) ? $action['class'] : '';
               
               echo '
               <li class="' . $action['class'] . '"';
               if ( isset( $action['parent_attributes'] ) && ! empty( $action['parent_attributes'] ) )
               {
                   foreach ( $action['parent_attributes'] as $key => $value )
                   {
                       echo ' ' . $key . '="' . esc_attr($value) . '"';
                   }
               }
               echo '><a href="' . $action['href'] . '"';
               if ( isset( $action['attributes'] ) && ! empty( $action['attributes'] ) )
               {
                   foreach ( $action['attributes'] as $key => $value )
                   {
                       echo ' ' . $key . '="' . esc_attr($value) . '"';
                   }
               }
               echo '>' . $action['label'] . '</a></li>
               ';
           }
    	?>
    	
    	<?php do_action( 'propertyhive_property_actions_list_end' ); ?>

    </ul>

	<?php do_action( 'propertyhive_property_actions_end' ); ?>
    
</div>