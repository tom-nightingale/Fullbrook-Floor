<?php
/**
 * PropertyHive Form Functions
 *
 * Functions related to drawing forms on the frontend.
 *
 * @author      PropertyHive
 * @category    Core
 * @package     PropertyHive/Functions
 * @version     1.0.0
 */

/**
 * Main function for drawing entire property search form. We give the ability for an ID to be passed so differently formatted forms can be used
 * (ie. a homepage search form might be different from a search form on search results)
 *
 * @param string $id
 * @return void
 */
function ph_get_search_form( $id = 'default' ) {

    $form_controls = ph_get_search_form_fields();

    $form_controls = apply_filters( 'propertyhive_search_form_fields_' . $id, $form_controls );
    $form_controls = apply_filters( 'propertyhive_search_form_fields', $form_controls );

    // We 100% need department so make sure it exists. If it doesn't, set a hidden field
    if ( !isset($form_controls['department']) )
    {
        $original_form_controls = ph_get_search_form_fields();
        $original_department = $original_form_controls['department'];
        $original_department['type'] = 'hidden';

        $form_controls['department'] = $original_department;
    }

    // append hidden order and view fields so these are maintained should a new search be performed
    foreach ( $_REQUEST as $key => $value )
    {
        if ( isset($form_controls[$key]) )
            continue;

        if ( $key == 'officeID' && isset($form_controls['office']) )
            continue;

        if ( $key == 'paged' )
            continue;

        if ( 
            ( $key == 'minimum_price' || $key == 'maximum_price' ) && array_key_exists('price_slider', $form_controls) || 
            ( $key == 'minimum_rent' || $key == 'maximum_rent' ) && array_key_exists('rent_slider', $form_controls) || 
            ( $key == 'minimum_bedrooms' || $key == 'maximum_bedrooms' ) && array_key_exists('bedrooms_slider', $form_controls)
        )
            continue;

        // we've received a field that isn't a standard form control so let's store it in a hidden field so it's not lost
        if ( is_array($value) )
        {
            foreach ( $value as $i => $val )
            {
                $form_controls[$key . '-' . $i] = array('type' => 'hidden', 'name' => $key . '[]', 'value' => stripslashes( ph_clean( $val) ));
            }
        }
        else
        {
            $form_controls[$key] = array('type' => 'hidden', 'value' => stripslashes( ph_clean( $value) ));
        }
    }

    $form_controls = apply_filters( 'propertyhive_search_form_fields_after_' . $id, $form_controls );
    $form_controls = apply_filters( 'propertyhive_search_form_fields_after', $form_controls );

    ph_get_template( 'global/search-form.php', array( 'form_controls' => $form_controls, 'id' => $id ) );

}

/**
 * Get default fields to be shown on search forms
 *
 * @return array
 */
function ph_get_search_form_fields()
{
    $fields = array();

    $departments = ph_get_departments();

    $department_options = array();
    $default_value = '';

    foreach ( $departments as $key => $value )
    {
        if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
        {
            $department_options[$key] = $value;

            if ($default_value == '' && get_option( 'propertyhive_primary_department' ) == $key )
            {
                $default_value = $key;
            }
        }
    }

    $fields['department'] = array(
        'type' => 'radio',
        'options' => $department_options,
        'value' => $default_value
    );

    if ( array_key_exists('residential-sales', $departments) || array_key_exists('residential-lettings', $departments) )
    {
        if ( array_key_exists('residential-sales', $departments) )
        {
            $prices = array(
                '' => __( 'No preference', 'propertyhive' ),
                '100000' => '&pound;100,000',
                '150000' => '&pound;150,000',
                '200000' => '&pound;200,000',
                '250000' => '&pound;250,000',
                '300000' => '&pound;300,000',
                '500000' => '&pound;500,000',
                '750000' => '&pound;750,000',
                '1000000' => '&pound;1,000,000'
            );

            $fields['minimum_price'] = array(
                'type' => 'select',
                'show_label' => true,
                'label' => __( 'Min Price', 'propertyhive' ),
                'before' => '<div class="control control-minimum_price sales-only">',
                'options' => $prices
            );

            $fields['maximum_price'] = array(
                'type' => 'select',
                'show_label' => true,
                'label' => __( 'Max Price', 'propertyhive' ),
                'before' => '<div class="control control-maximum_price sales-only">',
                'options' => $prices
            );
        }

        if ( array_key_exists('residential-lettings', $departments) )
        {
            $prices = array(
                '' => __( 'No preference', 'propertyhive' ),
                '500' => '&pound;500 PCM',
                '600' => '&pound;600 PCM',
                '750' => '&pound;750 PCM',
                '1000' => '&pound;1000 PCM',
                '1250' => '&pound;1250 PCM',
                '1500' => '&pound;1500 PCM',
                '2000' => '&pound;2000 PCM'
            );

            $fields['minimum_rent'] = array(
                'type' => 'select',
                'show_label' => true,
                'label' => __( 'Min Rent', 'propertyhive' ),
                'before' => '<div class="control control-minimum_rent lettings-only">',
                'options' => $prices
            );

            $fields['maximum_rent'] = array(
                'type' => 'select',
                'show_label' => true,
                'label' => __( 'Max Rent', 'propertyhive' ),
                'before' => '<div class="control control-maximum_rent lettings-only">',
                'options' => $prices
            );
        }

        $fields['minimum_bedrooms'] = array(
            'type' => 'select',
            'show_label' => true,
            'label' => __( 'Min Beds', 'propertyhive' ),
            'before' => '<div class="control control-minimum_bedrooms residential-only">',
            'options' => array( '' => __( 'No preference', 'propertyhive' ), 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5)
        );

        $fields['property_type'] = array(
            'type' => 'property_type',
            'show_label' => true,
            'before' => '<div class="control control-property_type residential-only">',
            'label' => __( 'Type', 'propertyhive' ),
        );
    }

    if ( array_key_exists('commercial', $departments) )
    {
        $sizes = array(
            '' => __( 'No preference', 'propertyhive' ),
            '250' => '250 sq ft',
            '500' => '500 sq ft',
            '1000' => '1,000 sq ft',
            '2500' => '2,500 sq ft',
            '5000' => '5,000 sq ft',
            '10000' => '10,000 sq ft',
            '25000' => '25,000 sq ft',
            '50000' => '50,000 sq ft'
        );

        $fields['minimum_floor_area'] = array(
            'type' => 'select',
            'show_label' => true,
            'label' => __( 'Min Floor Area', 'propertyhive' ),
            'before' => '<div class="control control-minimum_floor_area commercial-only">',
            'options' => $sizes
        );

        $fields['maximum_floor_area'] = array(
            'type' => 'select',
            'show_label' => true,
            'label' => __( 'Max Floor Area', 'propertyhive' ),
            'before' => '<div class="control control-maximum_floor_area commercial-only">',
            'options' => $sizes
        );

        // Property Type
        $options = array( '' => __( 'No preference', 'propertyhive' ) );
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'commercial_property_type', $args );

        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;

                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( 'commercial_property_type', $args );

                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = '- ' . $term->name;
                    }
                }
            }
        }

        $fields['commercial_property_type'] = array(
            'type' => 'select',
            'show_label' => true,
            'before' => '<div class="control control-commercial_property_type commercial-only">',
            'label' => __( 'Type', 'propertyhive' ),
            'options' => $options
        );
    }

    return $fields;
}

/**
 * Main function for drawing property enquiry form.
 *
 * @param string $id
 * @return void
 */
function propertyhive_enquiry_form( $property_id = '' )
{
    $form_controls = ph_get_property_enquiry_form_fields( $property_id );

    $form_controls = apply_filters( 'propertyhive_property_enquiry_form_fields', $form_controls );

    ph_get_template( 'global/make-enquiry-form.php',array( 'form_controls' => $form_controls ) );
}

/**
 * Get default fields to be shown on search forms
 *
 * @return array
 */
function ph_get_property_enquiry_form_fields( $property_id = '' )
{
    global $post;

    $fields = array();

    $fields['property_id'] = array(
        'type' => 'hidden',
        'value' => ( $property_id != '' ? $property_id : $post->ID )
    );

    $fields['name'] = array(
        'type' => 'text',
        'label' => __( 'Full Name', 'propertyhive' ),
        'required' => true
    );
    if ( is_user_logged_in() )
    {
        $current_user = wp_get_current_user();

        $fields['name']['value'] = $current_user->display_name;
    }

    $fields['email_address'] = array(
        'type' => 'email',
        'label' => __( 'Email Address', 'propertyhive' ),
        'required' => true
    );
    if ( is_user_logged_in() )
    {
        $current_user = wp_get_current_user();

        $fields['email_address']['value'] = $current_user->user_email;
    }

    $fields['telephone_number'] = array(
        'type' => 'text',
        'label' => __( 'Number', 'propertyhive' ),
        'required' => true
    );

    $fields['message'] = array(
        'type' => 'textarea',
        'label' => __( 'Message', 'propertyhive' ),
        'required' => true
    );

    if ( get_option( 'propertyhive_property_enquiry_form_disclaimer', '' ) != '' )
    {
        $disclaimer = get_option( 'propertyhive_property_enquiry_form_disclaimer', '' );

        $fields['disclaimer'] = array(
            'type' => 'checkbox',
            'label' => $disclaimer,
            'label_style' => 'width:100%;',
            'required' => true
        );
    }

    return $fields;
}

/**
 * Get default fields to be shown on applicant registration forms
 *
 * @return array
 */
function ph_get_user_details_form_fields()
{
    global $post;

    if ( is_user_logged_in() )
    {
        $current_user = wp_get_current_user();

        if ( $current_user instanceof WP_User )
        {
            $contact = new PH_Contact( '', $current_user->ID );
        }
    }

    $fields = array();

    $fields['name'] = array(
        'type' => 'text',
        'label' => __( 'Full Name', 'propertyhive' ),
        'required' => true
    );
    if ( is_user_logged_in() && $current_user instanceof WP_User )
    {
        $fields['name']['value'] = $current_user->display_name;
    }

    $fields['email_address'] = array(
        'type' => 'email',
        'label' => __( 'Email Address', 'propertyhive' ),
        'required' => true
    );
    if ( is_user_logged_in() && $current_user instanceof WP_User )
    {
        $fields['email_address']['value'] = $current_user->user_email;
    }

    $fields['telephone_number'] = array(
        'type' => 'text',
        'label' => __( 'Telephone Number', 'propertyhive' ),
        'required' => false
    );
    if ( is_user_logged_in() && $current_user instanceof WP_User )
    {
        $fields['telephone_number']['value'] = $contact->telephone_number;
    }

    if ( get_option( 'propertyhive_applicant_users', '' ) == 'yes' )
    {
        $fields['password'] = array(
            'type' => 'password',
            'label' => __( 'Password', 'propertyhive' ),
            'required' => true
        );

        $fields['password2'] = array(
            'type' => 'password',
            'label' => __( 'Confirm Password', 'propertyhive' ),
            'required' => true
        );
    }

    return $fields;
}

/**
 * Get default fields to be shown on applicant registration forms
 *
 * @return array
 */
function ph_get_applicant_requirements_form_fields()
{
    global $post;

    if ( is_user_logged_in() )
    {
        $current_user = wp_get_current_user();
        $applicant_profile = false;

        if ( $current_user instanceof WP_User )
        {
            $contact = new PH_Contact( '', $current_user->ID );

            if ( is_array($contact->contact_types) && in_array('applicant', $contact->contact_types) )
            {
                if (
                    $contact->applicant_profiles != '' &&
                    $contact->applicant_profiles > 0 &&
                    $contact->applicant_profile_0 != '' &&
                    is_array($contact->applicant_profile_0)
                )
                {
                    $applicant_profile = $contact->applicant_profile_0;
                }
            }
        }
    }

    $fields = array();

    $offices = array();
    $value = '';

    $args = array(
        'post_type' => 'office',
        'nopaging' => true,
        'orderby' => 'title',
        'order' => 'ASC'
    );

    $office_query = new WP_Query( $args );

    if ( $office_query->have_posts() )
    {
        while ( $office_query->have_posts() )
        {
            $office_query->the_post();

            $offices[get_the_ID()] = get_the_title();

            if ( get_post_meta(get_the_ID(), 'primary', TRUE) == 1 )
            {
                $value = get_the_ID();
            }
        }
    }
    wp_reset_postdata();

    $fields['office_id'] = array(
        'type' => ( (count($offices) <= 1) ? 'hidden' : 'select' ),
        'label' => __( 'Office', 'propertyhive' ),
        'required' => false,
        'show_label' => true,
        'value' => $value,
        'options' => $offices
    );

    $departments = array();
    $value = '';
    if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
    {
        $departments['residential-sales'] = __( 'Properties To Buy', 'propertyhive' );
        if ($value == '' && (get_option( 'propertyhive_primary_department' ) == 'residential-sales' || get_option( 'propertyhive_primary_department' ) === FALSE) )
        {
            $value = 'residential-sales';
        }
    }
    if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
    {
        $departments['residential-lettings'] = __( 'Properties For Rent', 'propertyhive' );
        if ($value == '' && get_option( 'propertyhive_primary_department' ) == 'residential-lettings')
        {
            $value = 'residential-lettings';
        }
    }
    if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
    {
        $departments['commercial'] = __( 'Commercial Properties', 'propertyhive' );
        if ($value == '' && get_option( 'propertyhive_primary_department' ) == 'commercial')
        {
            $value = 'commercial';
        }
    }
    $fields['department'] = array(
        'type' => 'radio',
        'label' => __( 'Looking For', 'propertyhive' ),
        'required' => true,
        'show_label' => true,
        'value' => $value,
        'options' => $departments
    );
    if ( is_user_logged_in() && isset($applicant_profile['department']) )
    {
        $fields['department']['value'] = $applicant_profile['department'];
    }
    if ( count($departments) == 1 )
    {
        $fields['department']['type'] = 'hidden';
    }

    if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' || get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
    {
        $fields['maximum_price'] = array(
            'type' => 'number',
            'label' => __( 'Maximum Price', 'propertyhive' ),
            'style' => 'max-width:150px;',
            'before' => '<div class="control control-minimum_price sales-only">',
            'required' => false
        );
        if ( is_user_logged_in() && isset($applicant_profile['max_price']) )
        {
            $fields['maximum_price']['value'] = $applicant_profile['max_price'];
        }

        $fields['maximum_rent'] = array(
            'type' => 'number',
            'label' => __( 'Maximum Rent', 'propertyhive' ) . ' (PCM)',
            'style' => 'max-width:150px;',
            'before' => '<div class="control control-minimum_price lettings-only">',
            'required' => false
        );
        if ( is_user_logged_in() && isset($applicant_profile['max_rent']) )
        {
            $fields['maximum_rent']['value'] = $applicant_profile['max_rent'];
        }

        $fields['minimum_bedrooms'] = array(
            'type' => 'number',
            'label' => __( 'Minimum Bedrooms', 'propertyhive' ),
            'style' => 'max-width:80px;',
            'before' => '<div class="control control-minimum_bedrooms residential-only">',
            'required' => false
        );
        if ( is_user_logged_in() && isset($applicant_profile['min_beds']) )
        {
            $fields['minimum_bedrooms']['value'] = $applicant_profile['min_beds'];
        }

        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'property_type', $args );

        $options = array();

        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            $options = array( '' => __( 'All Property Types', 'propertyhive' ) );

            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;

                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( 'property_type', $args );

                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = '- ' . $term->name;
                    }
                }
            }
        }

        if ( !empty($options) )
        {
            $fields['property_type'] = array(
                'type' => 'select',
                'label' => __( 'Property Type', 'propertyhive' ),
                'before' => '<div class="control control-property_type residential-only">',
                'required' => false,
                'options' => $options,
            );

            if ( is_user_logged_in() && isset($applicant_profile['property_types']) && is_array($applicant_profile['property_types']) && !empty($applicant_profile['property_types']) )
            {
                $fields['property_type']['value'] = $applicant_profile['property_types'][0];
            }
        }
    }

    if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
    {
        $fields['available_as_sale'] = array(
            'type' => 'checkbox',
            'label' => __( 'For Sale', 'propertyhive' ),
            'before' => '<div class="control control-available_as_sale commercial-only">',
            'required' => false,
        );
        if ( is_user_logged_in() && isset($applicant_profile['available_as']) && in_array('sale', $applicant_profile['available_as']) )
        {
            $fields['available_as_sale']['checked'] = true;
        }

        $fields['available_as_rent'] = array(
            'type' => 'checkbox',
            'label' => __( 'To Rent', 'propertyhive' ),
            'before' => '<div class="control control-available_as_rent commercial-only">',
            'required' => false,
        );
        if ( is_user_logged_in() && isset($applicant_profile['available_as']) && in_array('rent', $applicant_profile['available_as']) )
        {
            $fields['available_as_rent']['checked'] = true;
        }

        $fields['minimum_floor_area'] = array(
            'type' => 'number',
            'label' => __( 'Min Floor Area (Sq Ft)', 'propertyhive' ),
            'style' => 'max-width:150px;',
            'before' => '<div class="control control-minimum_floor_area commercial-only">',
            'required' => false
        );
        if ( is_user_logged_in() && isset($applicant_profile['min_floor_area']) )
        {
            $fields['minimum_floor_area']['value'] = $applicant_profile['min_floor_area'];
        }

        $fields['maximum_floor_area'] = array(
            'type' => 'number',
            'label' => __( 'Max Floor Area (Sq Ft)', 'propertyhive' ),
            'style' => 'max-width:150px;',
            'before' => '<div class="control control-maximum_floor_area commercial-only">',
            'required' => false
        );
        if ( is_user_logged_in() && isset($applicant_profile['max_floor_area']) )
        {
            $fields['maximum_floor_area']['value'] = $applicant_profile['max_floor_area'];
        }

        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'commercial_property_type', $args );

        $options = array();

        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            $options = array( '' => __( 'All Property Types', 'propertyhive' ) );

            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;

                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( 'commercial_property_type', $args );

                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = '- ' . $term->name;
                    }
                }
            }
        }

        if ( !empty($options) )
        {
            $fields['commercial_property_type'] = array(
                'type' => 'select',
                'label' => __( 'Property Type', 'propertyhive' ),
                'before' => '<div class="control control-commercial_property_type commercial-only">',
                'required' => false,
                'options' => $options,
            );

            if ( is_user_logged_in() && isset($applicant_profile['commercial_property_types']) && is_array($applicant_profile['commercial_property_types']) && !empty($applicant_profile['commercial_property_types']) )
            {
                $fields['commercial_property_type']['value'] = $applicant_profile['commercial_property_types'][0];
            }
        }
    }

    $args = array(
        'hide_empty' => false,
        'parent' => 0
    );
    $terms = get_terms( 'location', $args );

    $options = array();

    $selected_value = '';
    if ( !empty( $terms ) && !is_wp_error( $terms ) )
    {
        $options = array( '' => __( 'All Locations', 'propertyhive' ) );

        foreach ($terms as $term)
        {
            $options[$term->term_id] = $term->name;

            $args = array(
                'hide_empty' => false,
                'parent' => $term->term_id
            );
            $subterms = get_terms( 'location', $args );

            if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
            {
                foreach ($subterms as $term)
                {
                    $options[$term->term_id] = '- ' . $term->name;
                }
            }
        }
    }

    if ( !empty($options) )
    {
        $fields['location'] = array(
            'type' => 'select',
            'label' => __( 'Location', 'propertyhive' ),
            'required' => false,
            'options' => $options,
        );

        if ( is_user_logged_in() && isset($applicant_profile['locations']) && is_array($applicant_profile['locations']) && !empty($applicant_profile['locations']) )
        {
            $fields['location']['value'] = $applicant_profile['locations'][0];
        }
    }

    $fields['additional_requirements'] = array(
        'type' => 'textarea',
        'label' => __( 'Additional Requirements', 'propertyhive' ),
        'required' => false
    );
    if ( is_user_logged_in() && isset($applicant_profile['notes']) )
    {
        $fields['additional_requirements']['value'] = $applicant_profile['notes'];
    }

    return $fields;
}

/**
 * Output individual field
 *
 * @return void
 */
function ph_form_field( $key, $field )
{
    global $post;

    $output = '';

    switch ($field['type'])
    {
        case "text":
        case "email":
        case "date":
        case "number":
        case "password":
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['placeholder'] = isset( $field['placeholder'] ) ? $field['placeholder'] : ( ( $field['type'] == 'date' ) ? 'dd/mm/yyyy' : '' );
            $field['required'] = isset( $field['required'] ) ? $field['required'] : false;
            $field['style'] = isset( $field['style'] ) ? $field['style'] : '';

            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = sanitize_text_field( wp_unslash( $_GET[$key] ) );
            }
            else
            {
                if ( !is_post_type_archive('property') && !is_singular('property') && isset($post->ID) )
                {
                    $value = get_post_meta( $post->ID, '_' . $key, true );
                    if ( $value != '' )
                    {
                        $field['value'] = $value;
                    }
                }
            }

            $output .= $field['before'];

            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'];
                if ($field['required'])
                {
                    $output .= '<span class="required"> *</span>';
                }
                $output .= '</label>';
            }

            $output .= '<input
                    type="' . esc_attr( $field['type'] ) . '"
                    name="' . esc_attr( $key ) . '"
                    id="' . esc_attr( $key ) . '"
                    value="' . esc_attr( $field['value'] ) . '"
                    placeholder="' . esc_attr( $field['placeholder'] ) . '"
                    class="' . esc_attr( $field['class'] ) . '"
                    style="' . esc_attr( $field['style'] ) . '"
                    ' . ( ($field['required']) ? 'required' : '' ) . '
            >';

            $output .= $field['after'];

            break;
        }
        case "textarea":
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['placeholder'] = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
            $field['required'] = isset( $field['required'] ) ? $field['required'] : false;

            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = sanitize_textarea_field( wp_unslash( $_GET[$key] ) );
            }
            else
            {
                if ( !is_post_type_archive('property') && !is_singular('property') && isset($post->ID) )
                {
                    $value = get_post_meta( $post->ID, '_' . $key, true );
                    if ( $value != '' )
                    {
                        $field['value'] = $value;
                    }
                }
            }

            $output .= $field['before'];

            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'];
                if ($field['required'])
                {
                    $output .= '<span class="required"> *</span>';
                }
                $output .= '</label>';
            }

            $output .= '<textarea
                    name="' . esc_attr( $key ) . '"
                    id="' . esc_attr( $key ) . '"
                    placeholder="' . esc_attr(  $field['placeholder'] ) . '"
                    class="' . esc_attr( $field['class'] ) . '"
                    ' . ( ($field['required']) ? 'required' : '' ) . '
            >' . esc_attr(  $field['value'] ) . '</textarea>';

            $output .= $field['after'];

            break;
        }
        case "checkbox":
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['label_style'] = isset( $field['label_style'] ) ? $field['label_style'] : '';
            $field['value'] = isset( $field['value'] ) ? $field['value'] : 'yes';
            $field['checked'] = isset( $field['checked'] ) ? $field['checked'] : false;
            if ( isset( $_GET[$key] ) && sanitize_text_field(wp_unslash($_GET[$key])) == $field['value'] )
            {
                $field['checked'] = true;
            }
            else
            {
                if ( !is_post_type_archive('property') && !is_singular('property') && isset($post->ID) )
                {
                    $value = get_post_meta( $post->ID, '_' . $key, true );
                    if ( $value == 'yes' )
                    {
                        $field['checked'] = true;
                    }
                }
            }

            $output .= $field['before'];

            $output .= '<label style="' . esc_attr( $field['label_style'] ) . '"><input
                type="' . esc_attr( $field['type'] ) . '"
                name="' . esc_attr( $key ) . '"
                value="' . esc_attr( $field['value'] ) . '"
                class="' . esc_attr( $field['class'] ) . '"
                ' . checked( $field['checked'], true, false ) . '
            >';
            if ($field['show_label'])
            {
                $output .= ' <span>' . $field['label'] . '</span>';
            }
            $output .= '</label>';

            $output .= $field['after'];

            break;
        }
        case "radio":
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['before_option'] = isset( $field['before_option'] ) ? $field['before_option'] : '<label>';
            $field['after_option'] = isset( $field['after_option'] ) ? $field['after_option'] : '</label>';
            $field['before_input'] = isset( $field['before_input'] ) ? $field['before_input'] : '';
            $field['after_input'] = isset( $field['after_input'] ) ? $field['after_input'] : '';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : false;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';

            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = sanitize_text_field(wp_unslash($_GET[$key]));
            }

            $output .= $field['before'];

            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'] . '</label>';
            }

            foreach ( $field['options'] as $option_key => $value )
            {
                $id = esc_attr( $key ) . '_' . esc_attr( $option_key );
                $output .= str_replace("{id}", $id, $field['before_option']);
                $output .= str_replace("{id}", $id, $field['before_input']);
                $output .= '<input
                    type="' . esc_attr( $field['type'] ) . '"
                    name="' . esc_attr( $key ) . '"
                    id="' . $id . '"
                    value="' . esc_attr( $option_key ) . '"
                    class="' . esc_attr( $field['class'] ) . '"
                    ' . checked( esc_attr( $field['value'] ), esc_attr( $option_key ), false ) . '
                >';
                $output .= str_replace("{id}", $id, $field['after_input']);
                $output .= ' ' . esc_html( $value );
                $output .= str_replace("{id}", $id, $field['after_option']);
            }

            $output .= $field['after'];

            break;
        }
        case "select":
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['required'] = isset( $field['required'] ) ? $field['required'] : false;
            $field['options'] = ( isset( $field['options'] ) && is_array( $field['options'] ) ) ? $field['options'] : array();
            $field['multiselect'] = isset( $field['multiselect'] ) ? $field['multiselect'] : false;

            if ( $field['multiselect'] )
            {
                wp_enqueue_script( 'multiselect' );
            }

            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = sanitize_text_field(wp_unslash($_GET[$key]));
            }
            else
            {
                if ( !is_post_type_archive('property') && isset($post->ID) )
                {
                    $value = get_post_meta( $post->ID, '_' . $key, true );
                    if ( $value != '' )
                    {
                        $field['value'] = $value;
                    }
                }
            }

            $output .= $field['before'];

            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'];
                if ($field['required'])
                {
                    $output .= '<span class="required"> *</span>';
                }
                $output .= '</label>';
            }

            $blank_option = '';
            foreach ( $field['options'] as $option_key => $value )
            {
                if ( $field['multiselect'] && $option_key == '' )
                {
                    $blank_option = $value;
                    continue;
                }
            }

            $output .= '<select
                name="' . esc_attr( $key ) . ( $field['multiselect'] ? '[]' : '' ) . '"
                id="' . esc_attr( $key ) . '"
                class="' . esc_attr( $field['class'] ) . ( $field['multiselect'] ? ' ph-form-multiselect' : '' ) . '"
                ' . ( $field['multiselect'] ? ' multiple="multiple"' : '' ) . '
                data-blank-option="' . esc_attr($blank_option) . '"
             >';

            foreach ( $field['options'] as $option_key => $value )
            {
                if ( $field['multiselect'] && $option_key == '' )
                {
                    // Skip because we don't want a blank option in the multiselect. Instead use $value as the placeholder
                    continue;
                }

                $output .= '<option
                    value="' . esc_attr( $option_key ) . '"';
                if ( !$field['multiselect'] )
                {
                    $output .= selected( esc_attr( $field['value'] ), esc_attr( $option_key ), false );
                }
                else
                {
                    if ( isset($_REQUEST[$key]) && is_array($_REQUEST[$key]) && in_array($option_key, $_REQUEST[$key]) )
                    {
                        $output .= ' selected';
                    }
                }
                $output .= '>' . esc_html( $value ) . '</option>';
            }

            $output .= '</select>';

            $output .= $field['after'];

            break;
        }
        case "office":
        {
            $key = 'officeID';

            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['multiselect'] = isset( $field['multiselect'] ) ? $field['multiselect'] : false;

            if ( $field['multiselect'] )
            {
                wp_enqueue_script( 'multiselect' );
            }

            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = (int)$_GET[$key];
            }

            $output .= $field['before'];

            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'] . '</label>';
            }

            $output .= '<select
                name="' . esc_attr( $key ) . ( $field['multiselect'] ? '[]' : '' ) . '"
                id="' . esc_attr( $key ) . '"
                class="' . esc_attr( $field['class'] ) . ( $field['multiselect'] ? ' ph-form-multiselect' : '' ) . '"
                ' . ( $field['multiselect'] ? ' multiple="multiple"' : '' ) . '
                data-blank-option="' . esc_attr( __( 'No preference', 'propertyhive' ) ) . '"
            >';

            if ( !$field['multiselect'] )
            {
                $output .= '<option
                        value=""
                        ' . selected( esc_attr( $field['value'] ), esc_attr( '' ), false ) . '
                    >' . esc_html( __( 'No preference', 'propertyhive' ) ) . '</option>';
            }

            $args = array(
                'post_type' => 'office',
                'nopaging' => true,
                'orderby' => 'title',
                'order' => 'ASC'
            );
            $office_query = new WP_Query($args);

            if ($office_query->have_posts())
            {
                while ($office_query->have_posts())
                {
                    $office_query->the_post();

                    $output .= '<option
                        value="' . esc_attr( $post->ID ) . '" ';
                    if ( !$field['multiselect'] )
                    {
                        $output .= selected( esc_attr( $field['value'] ), esc_attr( $post->ID ), false );
                    }
                    else
                    {
                        if ( isset($_REQUEST[$key]) && is_array($_REQUEST[$key]) && in_array($post->ID, $_REQUEST[$key]) )
                        {
                            $output .= ' selected';
                        }
                    }
                    $output .= '>' . esc_html( get_the_title() ) . '</option>';

                }
            }
            wp_reset_postdata();

            $output .= '</select>';

            $output .= $field['after'];

            break;
        }
        case "country":
        {
            $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';

            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = sanitize_text_field(wp_unslash($_GET[$key]));
            }

            $output .= $field['before'];

            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'] . '</label>';
            }

            $output .= '<select
                name="' . esc_attr( $key ) . '"
                id="' . esc_attr( $key ) . '"
                class="' . esc_attr( $field['class'] ) . '"
             >';

             $output .= '<option
                        value=""
                        ' . selected( esc_attr( $field['value'] ), esc_attr( '' ), false ) . '
                    >' . esc_html( __( 'No preference', 'propertyhive' ) ) . '</option>';

            $countries = get_option( 'propertyhive_countries', array() );
            if ( is_array($countries) && !empty($countries) )
            {
                $ph_countries = new PH_Countries;

                foreach ( $countries as $country )
                {
                    $ph_country = $ph_countries->get_country( $country );

                    if ( $ph_country !== FALSE )
                    {
                        $output .= '<option
                        value="' . esc_attr( $country ) . '"
                        ' . selected( esc_attr( $field['value'] ), esc_attr( $country ), false ) . '
                        >' . esc_html( $ph_country['name'] ) . '</option>';
                    }
                }
            }

            $output .= '</select>';

            $output .= $field['after'];

            break;
        }
        case "slider":
        {   
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-slider');
            wp_enqueue_style( 'jquery-ui-style', PH()->plugin_url() . '/assets/css/jquery-ui/jquery-ui.css', array(), PH_VERSION );

            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
            $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
            $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
            $field['min'] = isset( $field['min'] ) ? $field['min'] : '';
            $field['max'] = isset( $field['max'] ) ? $field['max'] : '';
            $field['step'] = isset( $field['step'] ) ? $field['step'] : '1';

            $output .= $field['before'];

            if ($field['show_label'])
            {
                $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'];
                $output .= ' - <span id="search-form-slider-value-' . $key . '" class="search-form-slider-value"></span>';
                $output .= '</label>';
            }

            $output .= '<div id="search-form-slider-' . $key . '" class="search-form-slider" style="min-width:150px;"></div>';
            switch ( $key )
            {
                case "price_slider":
                {
                    $output .= '<input type="hidden" name="minimum_price" id="min_slider_value-' . $key . '" value="' . ( isset($_GET['minimum_price']) ? ph_clean($_GET['minimum_price']) : '' ) . '">';
                    $output .= '<input type="hidden" name="maximum_price" id="max_slider_value-' . $key . '" value="' . ( isset($_GET['maximum_price']) ? ph_clean($_GET['maximum_price']) : '' ) . '">';
                    break;
                }
                case "rent_slider":
                {
                    $output .= '<input type="hidden" name="minimum_rent" id="min_slider_value-' . $key . '" value="' . ( isset($_GET['minimum_rent']) ? ph_clean($_GET['minimum_rent']) : '' ) . '">';
                    $output .= '<input type="hidden" name="maximum_rent" id="max_slider_value-' . $key . '" value="' . ( isset($_GET['maximum_rent']) ? ph_clean($_GET['maximum_rent']) : '' ) . '">';
                    break;
                }
                case "bedrooms_slider":
                {
                    $output .= '<input type="hidden" name="minimum_bedrooms" id="min_slider_value-' . $key . '" value="' . ( isset($_GET['minimum_bedrooms']) ? ph_clean($_GET['minimum_bedrooms']) : '' ) . '">';
                    $output .= '<input type="hidden" name="maximum_bedrooms" id="max_slider_value-' . $key . '" value="' . ( isset($_GET['maximum_bedrooms']) ? ph_clean($_GET['maximum_bedrooms']) : '' ) . '">';
                    break;
                }
            }

            $output .= $field['after'];

            $value = '';
            $prefix = '';
            $suffix = '';

            if ( $key == 'price_slider' || $key == 'rent_slider' )
            {
                $prefix = '£';

                $search_form_currency = get_option( 'propertyhive_search_form_currency', 'GBP' );

                $ph_countries = new PH_Countries();
                $countries = $ph_countries->countries;

                foreach ( $countries as $country_code => $country )
                {
                    if ( isset($country['currency_code']) && $country['currency_code'] == $search_form_currency )
                    {
                        if ( $country['currency_prefix'] === true )
                        {
                            $prefix = $country['currency_symbol'];
                            $suffix = '';
                        }
                        else
                        {
                            $prefix = '';
                            $suffix = $country['currency_symbol'];
                        }
                        break;
                    }
                }
            }

            switch ( $key )
            {
                case "price_slider":
                {   
                    if ( $field['min'] != '' && $field['max'] != '' )
                    {
                        $value = 'values: [ ' . ( isset($_GET['minimum_price']) && $_GET['minimum_price'] != '' ? ph_clean($_GET['minimum_price']) : $field['min'] ) . ', ' . ( isset($_GET['maximum_price']) && $_GET['maximum_price'] != '' ? ph_clean($_GET['maximum_price']) : $field['max'] ) . ' ],';
                    }
                    break;
                }
                case "rent_slider":
                {   
                    if ( $field['min'] != '' && $field['max'] != '' )
                    {
                        $value = 'values: [ ' . ( isset($_GET['minimum_rent']) && $_GET['minimum_rent'] != '' ? ph_clean($_GET['minimum_rent']) : $field['min'] ) . ', ' . ( isset($_GET['maximum_rent']) && $_GET['maximum_rent'] != '' ? ph_clean($_GET['maximum_rent']) : $field['max'] ) . ' ],';
                    }
                    break;
                }
                case "bedrooms_slider":
                {   
                    if ( $field['min'] != '' && $field['max'] != '' )
                    {
                        $value = 'values: [ ' . ( isset($_GET['minimum_bedrooms']) && $_GET['minimum_bedrooms'] != '' ? ph_clean($_GET['minimum_bedrooms']) : $field['min'] ) . ', ' . ( isset($_GET['maximum_bedrooms']) && $_GET['maximum_bedrooms'] != '' ? ph_clean($_GET['maximum_bedrooms']) : $field['max'] ) . ' ],';
                    }
                    break;
                }
            }

            $output .= '<script>
                jQuery(document).ready(function()
                {
                    jQuery( "#search-form-slider-' . $key . '" ).slider({
                        range: ' . ( ( $field['min'] != '' && $field['max'] != '' ) ? 'true' : 'false' ) . ',
                        step: ' . $field['step'] . ',
                        ' . ( $field['min'] != '' ? 'min: ' . $field['min'] . ',' : '' ) . '
                        ' . ( $field['max'] != '' ? 'max: ' . $field['max'] . ',' : '' ) . '
                        ' . $value . '
                        slide: function( event, ui ) {
                            jQuery( "#search-form-slider-value-' . $key . '" ).html( "' . $prefix . '" + ui.values[ 0 ].toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") + "' . $suffix . '" + " - ' . $prefix . '" + ui.values[ 1 ].toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") + "' . $suffix . '" );
                            jQuery( "#min_slider_value-' . $key . '" ).val( ui.values[0] );
                            jQuery( "#max_slider_value-' . $key . '" ).val( ui.values[1] );
                        }
                    });
                    jQuery( "#search-form-slider-value-' . $key . '" ).html( "' . $prefix . '" + jQuery( "#search-form-slider-' . $key . '" ).slider( "values", 0 ).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") + "' . $suffix . '" + " - ' . $prefix . '" + jQuery( "#search-form-slider-' . $key . '" ).slider( "values", 1 ).toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") + "' . $suffix . '" );
                });
            </script>';

            break;
        }
        case "hidden":
        {
            $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
            $field['name'] = isset( $field['name'] ) ? $field['name'] : $key;
            if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
            {
                $field['value'] = sanitize_text_field(wp_unslash($_GET[$key]));
            }

            $output .= '<input type="hidden" name="' . esc_attr( $field['name'] ) . '" value="' . $field['value'] . '">';
            break;
        }
        case "html":
        {
            $field['html'] = isset( $field['html'] ) ? $field['html'] : '';
            $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
            $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';

            $output .= $field['before'];
            $output .= $field['html'];
            $output .= $field['after'];

            break;
        }
        case "recaptcha":
        {
            $field['site_key'] = isset( $field['site_key'] ) ? $field['site_key'] : '';

            $output .= '<script src="https://www.google.com/recaptcha/api.js"></script>
            <div class="g-recaptcha" data-sitekey="' . $field['site_key'] . '"></div>';
            break;
        }
        default:
        {
            if ( taxonomy_exists($field['type']) )
            {
                $field['class'] = isset( $field['class'] ) ? $field['class'] : '';
                $field['before'] = isset( $field['before'] ) ? $field['before'] : '<div class="control control-' . $key . '">';
                $field['after'] = isset( $field['after'] ) ? $field['after'] : '</div>';
                $field['show_label'] = isset( $field['show_label'] ) ? $field['show_label'] : true;
                $field['label'] = isset( $field['label'] ) ? $field['label'] : '';
                $field['blank_option'] = isset( $field['blank_option'] ) ? $field['blank_option'] : __( 'No preference', 'propertyhive' );
                $field['parent_terms_only'] = isset( $field['parent_terms_only'] ) ? $field['parent_terms_only'] : false;
                $field['multiselect'] = isset( $field['multiselect'] ) ? $field['multiselect'] : false;

                if ( $field['multiselect'] )
                {
                    wp_enqueue_script( 'multiselect' );
                }

                $field['value'] = isset( $field['value'] ) ? $field['value'] : '';
                if ( isset( $_GET[$key] ) && ! empty( $_GET[$key] ) )
                {
                    $field['value'] = sanitize_text_field(wp_unslash($_GET[$key]));
                }

                $output .= $field['before'];

                if ($field['show_label'])
                {
                    $output .= '<label for="' . esc_attr( $key ) . '">' . $field['label'] . '</label>';
                }

                $output .= '<select
                    name="' . esc_attr( $key ) . ( $field['multiselect'] ? '[]' : '' ) . '"
                    id="' . esc_attr( $key ) . '"
                    class="' . esc_attr( $field['class'] ) . ( $field['multiselect'] ? ' ph-form-multiselect' : '' ) . '"
                    ' . ( $field['multiselect'] ? ' multiple="multiple"' : '' ) . '
                    data-blank-option="' . esc_attr($field['blank_option']) . '"
                 >';

                $options = array( '' => $field['blank_option'] );
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( $field['type'], $args );

                $selected_value = '';
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ($terms as $term)
                    {
                        $options[$term->term_id] = $term->name;

                        if ( 
                            !isset($field['parent_terms_only'])
                            ||
                            (
                                isset($field['parent_terms_only']) &&
                                $field['parent_terms_only'] === false
                            )
                        )
                        {
                            $args = array(
                                'hide_empty' => false,
                                'parent' => $term->term_id
                            );
                            $subterms = get_terms( $field['type'], $args );

                            if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                            {
                                foreach ($subterms as $term)
                                {
                                    $options[$term->term_id] = '- ' . $term->name;

                                    $args = array(
                                        'hide_empty' => false,
                                        'parent' => $term->term_id
                                    );
                                    $subsubterms = get_terms( $field['type'], $args );

                                    if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                                    {
                                        foreach ($subsubterms as $term)
                                        {
                                            $options[$term->term_id] = '- ' . $term->name;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ( $options as $option_key => $value )
                {
                    if ( $field['multiselect'] && $option_key == '' )
                    {
                        // Skip because we don't want a blank option in the multiselect. Instead use $value as the placeholder
                        continue;
                    }

                    $output .= '<option
                        value="' . esc_attr( $option_key ) . '"';
                    if ( !$field['multiselect'] )
                    {
                        $output .= selected( esc_attr( $field['value'] ), esc_attr( $option_key ), false );
                    }
                    else
                    {
                        if ( isset($_REQUEST[$key]) && is_array($_REQUEST[$key]) && in_array($option_key, $_REQUEST[$key]) )
                        {
                            $output .= ' selected';
                        }
                    }
                    $output .= '>' . esc_html( $value ) . '</option>';
                }

                $output .= '</select>';

                $output .= $field['after'];

                if ( $field['type'] == 'availability' )
                {
                    $availability_departments = get_option( 'propertyhive_availability_departments', array() );
                    if ( !is_array($availability_departments) ) { $availability_departments = array(); }

                    if ( !empty($availability_departments) )
                    {
?>
<script>
var selected_availability = '<?php echo ( isset($_REQUEST[$key]) && $_REQUEST[$key] != '' ? (int)$_REQUEST[$key] : '' ); ?>';
var availability_departments = <?php echo json_encode($availability_departments); ?>;
var availabilities = <?php echo json_encode($options); ?>;
var availabilities_order = <?php echo json_encode(array_keys($options)); ?>;
</script>
<?php
                    }
                }
            }
        }
    }

    echo $output;
}