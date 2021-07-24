<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$readonly = isset($readonly) ? $readonly : false;

echo '<div class="propertyhive_meta_box">';
        
    echo '<div class="options_group">';

    echo '<p class="form-field">
    
        <label for="">' . __('Status', 'propertyhive') . '</label>
        
        ' . __( ucwords(str_replace("_", " ", $viewing->status)), 'propertyhive' );

        // Add text if this a second, third etc viewing
        $related_viewings = get_post_meta( $viewing->id, '_related_viewings', TRUE );
        if ( isset($related_viewings['previous']) && count($related_viewings['previous']) > 0 )
        {
            echo ' - ' . ph_ordinal_suffix(count($related_viewings['previous'])+1) . ' ' . __( 'Viewing', 'propertyhive' );
        }

    if ( $viewing->status == 'offer_made' )
    {
        if ( get_option('propertyhive_module_disabled_offers_sales', '') != 'yes' )
        {
            $offer_id = get_post_meta( $viewing->id, '_offer_id', TRUE );
            if ( $offer_id != '' && get_post_status($offer_id) != 'publish' )
            {
                $offer_id = '';
            }

            if ( $offer_id != '' )
            {
                echo ' (<a href="' . get_edit_post_link($offer_id) . '">' . __('View Offer', 'propertyhive') . '</a>)';
            }
        }
    }
    
    echo '</p>';

    if ( $viewing->status == 'cancelled' )
    {
        if ( $readonly )
        {
            echo '<p class="form-field">
    
                <label for="">' . __('Reason Cancelled', 'propertyhive') . '</label>

                ' . nl2br(esc_html($viewing->cancelled_reason)) . '

            </p>';
        }
        else
        {
            $args = array( 
                'id' => '_cancelled_reason', 
                'label' => __( 'Reason Cancelled', 'propertyhive' ), 
                'desc_tip' => false, 
                'class' => '',
                'value' => $viewing->cancelled_reason,
                'custom_attributes' => array(
                    'style' => 'width:95%; max-width:500px;'
                )
            );
            propertyhive_wp_textarea_input( $args );
        }
    }

    if ( $viewing->status == 'carried_out' || $viewing->status == 'offer_made' )
    {
        echo '<p class="form-field">
    
            <label for="">' . __('Applicant Feedback', 'propertyhive') . '</label>';

        switch ( $viewing->feedback_status )
        {
            case "interested":
            {
                echo __( 'Interested', 'propertyhive' );
                break;
            }
            case "not_interested":
            {
                echo __( 'Not Interested', 'propertyhive' );
                break;
            }
            case "not_required":
            {
                echo __( 'Feedback Not Required', 'propertyhive' );
                break;
            }
            default:
            {
                echo __( 'Awaiting Feedback', 'propertyhive' );
            }
        }

        echo '</p>';

        if ( $viewing->feedback_status == 'interested' || $viewing->feedback_status == 'not_interested' )
        {
            if ( $readonly )
            {
                echo '<p class="form-field">
        
                    <label for="">' . __('Feedback', 'propertyhive') . '</label>

                    ' . nl2br(esc_html($viewing->feedback)) . '

                </p>';
            }
            else
            {
                $args = array( 
                    'id' => '_feedback', 
                    'label' => __( 'Feedback', 'propertyhive' ), 
                    'desc_tip' => false, 
                    'class' => '',
                    'value' => $viewing->feedback,
                    'custom_attributes' => array(
                        'style' => 'width:95%; max-width:500px;'
                    )
                );
                propertyhive_wp_textarea_input( $args );
            }
        }
    }

    if ( ($viewing->status == 'carried_out' || $viewing->status == 'offer_made') && ( $viewing->feedback_status == 'interested' || $viewing->feedback_status == 'not_interested' ) )
    {
        $datetime_format = get_option('date_format')." \a\\t ".get_option('time_format');

        echo '<p class="form-field">

            <label for="">' . __('Date Feedback Received', 'propertyhive') . '</label>';

            echo ( !empty($viewing->feedback_received_date) ? date( $datetime_format, strtotime($viewing->feedback_received_date) ) : __( 'Unknown', 'propertyhive' ) );

        echo '</p>';

        echo '<p class="form-field">
    
            <label for="">' . __('Feedback Passed On', 'propertyhive') . '</label>';

            echo ( ($viewing->feedback_passed_on == 'yes') ? __( 'Yes', 'propertyhive' ) : __( 'No', 'propertyhive' ) );

        echo '</p>';
    }

    do_action('propertyhive_viewing_details_fields');
    
    echo '</div>';
    
echo '</div>';