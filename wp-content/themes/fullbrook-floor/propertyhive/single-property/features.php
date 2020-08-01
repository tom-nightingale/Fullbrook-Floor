<?php
/**
 * Single Property Features
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post, $property;

$features = $property->get_features();

if ( !empty($features) )
{
?>

    
    <ul class="flex-wrap p-4 font-bold lg:flex bg-primary-light bg-opacity-20">
<?php
    foreach ($features as $feature)
    {
?>
        <li class="relative py-2 pl-6"><?php icon('check', 'absolute top-3 left-0 text-xs text-secondary h-5 w-5 p-1 rounded-full border border-secondary'); ?> <?php echo $feature; ?></li>
<?php
    }
?>
    </ul>
<?php
}
?>