<?php

// Note we're using 'default' as the identifier. Update this accordingly (see the first part of this guide)
add_filter( 'propertyhive_search_form_fields_default', 'edit_default_property_search_form_fields' );

function edit_default_property_search_form_fields($fields)
{
    // Remove the minimum bedrooms dropdown
    unset($fields['department']);


    //add additional fields to the property maximum price
    $prices = array(
        '' => __( 'No preference', 'propertyhive' ),
        '100000' => '£100,000',
        '150000' => '£150,000',
        '200000' => '£200,000',
        '250000' => '£250,000',
        '300000' => '£300,000',
        '500000' => '£500,000',
        '750000' => '£750,000',
        '1000000' => '£1,000,000',
        '1250000' => '£1,250,000',
        '1500000' => '£1,500,000',
        '2000000' => '£2,000,000',
        '3000000' => '£3,000,000',
        '4000000' => '£4,000,000',
        '5000000' => '£5,000,000',
    );
    $fields['maximum_price']['options'] = $prices;

    return $fields; // return the fields
}


// Update the enquiry form to have a nice Telephone Number label
add_filter( 'propertyhive_property_enquiry_form_fields', 'edit_default_property_enquiry_form' );

function edit_default_property_enquiry_form($fields) {

    $fields['telephone_number']['label'] = "Telephone Number";

    return $fields; // return the fields
}