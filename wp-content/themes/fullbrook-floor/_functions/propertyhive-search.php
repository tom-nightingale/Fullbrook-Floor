<?php

// Note we're using 'default' as the identifier. Update this accordingly (see the first part of this guide)
add_filter( 'propertyhive_search_form_fields_default', 'edit_default_property_search_form_fields' );

function edit_default_property_search_form_fields($fields)
{
    // Remove the minimum bedrooms dropdown
    unset($fields['department']);

    return $fields; // return the fields
}