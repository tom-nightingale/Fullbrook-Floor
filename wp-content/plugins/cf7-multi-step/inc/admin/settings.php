<?php
if (!defined('ABSPATH')) {
    exit;
}
add_filter('wpcf7_editor_panels', 'cf7mls_admin_settings');
function cf7mls_admin_settings($panels)
{
    $panels['cf7mls-settings-panel'] = array(
        'title' => __('Multi-Step Settings', 'cf7mls'),
        'callback' => 'cf7mls_settings_func'
    );
    return $panels;
}
function cf7mls_settings_func($post)
{
    ?>
    <div class="cf7mls-el-disabled">
        <h2><?php echo esc_html(__('Color', 'cf7mls')); ?></h2>
        <fieldset>
            <legend><?php _e('You can change the background-color or text-color of Back, Next buttons here.', 'cf7mls'); ?></legend>

            <strong><?php _e('Back Button', 'cf7mls'); ?></strong>

            <?php _e('BG color', 'cf7mls'); ?>
            <input type="text" class="cf7mls-color-field" name="back-btn-bg-color" value="<?php echo $post->prop('cf7mls_back_bg_color'); ?>" />

            <?php _e('Text color', 'cf7mls'); ?>
            <input type="text" class="cf7mls-color-field" name="back-btn-text-color" value="<?php echo $post->prop('cf7mls_back_text_color'); ?>" />
            <br />

            <strong><?php _e('Next Button', 'cf7mls'); ?></strong>

            <?php _e('BG color', 'cf7mls'); ?>
            <input type="text" class="cf7mls-color-field" name="next-btn-bg-color" value="<?php echo $post->prop('cf7mls_next_bg_color'); ?>" />

            <?php _e('Text color', 'cf7mls'); ?>
            <input type="text" class="cf7mls-color-field" name="next-btn-text-color" value="<?php echo $post->prop('cf7mls_next_text_color'); ?>" />
        </fieldset>
        <h2><?php echo esc_html(__('Back Button', 'cf7mls')); ?></h2>
        <fieldset>
            <strong><?php _e('The Last Back Button Title', 'cf7mls'); ?></strong><br />
            <input type="text" class="regular-text" name="last-back-btn-title" value="<?php echo $post->prop('cf7mls_back_button_title'); ?>" />

        </fieldset>
    </div>
    <?php
    cf7mls_premium_only('button');
}
