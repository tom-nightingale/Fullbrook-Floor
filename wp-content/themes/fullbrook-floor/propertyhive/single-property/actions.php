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


	<?php do_action( 'propertyhive_property_actions_start' ); ?>
    
    <ul class="flex-wrap w-full my-8 lg:flex">
        
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
               <li class="inline-block w-full p-2 lg:w-1/3 ' . $action['class'] . '"';
               if ( isset( $action['parent_attributes'] ) && ! empty( $action['parent_attributes'] ) )
               {
                   foreach ( $action['parent_attributes'] as $key => $value )
                   {
                       echo ' ' . $key . '="' . $value . '"';
                   }
               }
               echo '><a class="block p-4 font-bold text-center border border-primary-light hover:border-primary" href="' . $action['href'] . '"';
               if ( isset( $action['attributes'] ) && ! empty( $action['attributes'] ) )
               {
                   foreach ( $action['attributes'] as $key => $value )
                   {
                       echo ' ' . $key . '="' . $value . '"';
                   }
               }
               echo '>' . $action['label'] . '</a></li>
               ';
           }
    	?>
    	
    	<?php do_action( 'propertyhive_property_actions_list_end' ); ?>

    </ul>

	<?php do_action( 'propertyhive_property_actions_end' ); ?>