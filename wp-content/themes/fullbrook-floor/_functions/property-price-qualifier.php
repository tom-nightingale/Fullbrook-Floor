<?php
    //custom price qualifier and price output
    function property_price_qualifier($propertyID) {
        $propertyID = $propertyID;
        $qualifier = wp_get_post_terms($propertyID, 'price_qualifier');
        if($qualifier) {
            $price_qualifier = $qualifier[0]->name;        
        }
        else {
            $price_qualifier = "";
        }
        return($price_qualifier);
    }
?>