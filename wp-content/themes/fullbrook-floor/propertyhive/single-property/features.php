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

    
    <ul class="flex-wrap items-center p-4 font-bold lg:p-8 md:flex bg-primary-light bg-opacity-20 lg:w-4/5">
<?php
    foreach ($features as $feature)
    {
?>
        <li class="relative py-2 pl-5 md:pl-6 md:w-1/2 2xl:w-1/3"><?php icon('check', 'absolute top-4 left-0 text-xs text-secondary h-5 w-5 p-1 rounded-full border border-secondary'); ?> <?php echo $feature; ?></li>
<?php
    }
?>
    </ul>
<?php
}
?>