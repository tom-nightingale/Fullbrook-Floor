<?php
/**
 * Single Property Summary Description
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;

$summary = get_the_excerpt();

if ( $summary != '' )
{
?>
    
    <?php echo apply_filters('the_content', $summary); ?>

<?php
}
?>