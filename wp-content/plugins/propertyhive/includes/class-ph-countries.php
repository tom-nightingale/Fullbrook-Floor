<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PropertyHive countries
 *
 * The PropertyHive countries class stores country data.
 *
 * @class       PH_Countries
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Countries {

	public function __construct() {

		add_action( 'template_redirect', array( $this, 'ph_check_currency_change' ) );

		add_action( 'propertyhive_update_currency_exchange_rates', array( $this, 'ph_update_currency_exchange_rates' ) );

	}

	/**
	 * Auto-load in-accessible properties on demand.
	 * @param  mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( 'countries' == $key ) {
			return $this->get_countries();
		}
	}

	public function ph_check_currency_change()
	{
		if ( is_post_type_archive('property') && isset($_GET['currency']) )
		{
			if ( $_GET['currency'] == '' )
			{
				// Set to blank to reset back to properties entered currency
				unset( $_COOKIE['propertyhive_currency'] );
  				setcookie( 'propertyhive_currency', '', time() - ( 15 * 60 ) );
				return true;
			}

			// TO DO: Make sure currency passed in is in list of countries they operate in
			// so we can get the exchange rate

			$currency = $this->get_currency( sanitize_text_field($_GET['currency']) );
			if ( $currency === FALSE )
			{
				$default_country = get_option( 'propertyhive_default_country', 'GB' );
				$default_country = $this->get_country( $default_country );

				$currency = $this->get_currency( (isset($default_country['currency_code'])) ? $default_country['currency_code'] : 'GBP' );
			}

			$currency['exchange_rate'] = 1;
			$exchange_rates = get_option( 'propertyhive_currency_exchange_rates', array() );
			if ( isset($exchange_rates[$_GET['currency']]) )
			{
				$currency['exchange_rate'] = $exchange_rates[sanitize_text_field($_GET['currency'])];
			}
			
			ph_setcookie( 'propertyhive_currency', htmlentities(serialize($currency)), time() + (30 * DAY_IN_SECONDS), is_ssl() );
		}
	}



	public function get_country( $country_code ) {

		$countries = $this->get_countries();

		if ( isset($countries[$country_code]) )
		{
			return $countries[$country_code];
		}
		
		return false;
	}

	public function get_currency( $currency_code ) {

		$countries = $this->get_countries();

		foreach ( $countries as $country )
		{
			if ( $country['currency_code'] == $currency_code )
			{
				$currency_symbol = apply_filters( 'propertyhive_currency_symbol', $country['currency_symbol'], $currency_code);
				$currency_prefix = apply_filters( 'propertyhive_currency_prefix', $country['currency_prefix'], $currency_code);
				
				return array(
					'currency_symbol' => $currency_symbol,
					'currency_prefix' => $currency_prefix
				);
			}
		}

		return false;
	}

	/**
	 * Get all countries.
	 * @return array
	 */
	private function get_countries() {
		$countries = array(
			'AU' => array(
				'name' => 'Australia',
				'currency_code' => 'AUD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'AT' => array(
				'name' => 'Austria',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'BB' => array(
				'name' => 'Barbados',
				'currency_code' => 'BBD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'BE' => array(
				'name' => 'Belgium',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'BG' => array(
				'name' => 'Bulgaria',
				'currency_code' => 'BGN',
				'currency_symbol' => 'лв',
				'currency_prefix' => true
			),
			'CA' => array(
				'name' => 'Canada',
				'currency_code' => 'CAD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
			'HR' => array(
				'name' => 'Croatia',
				'currency_code' => 'HRK',
				'currency_symbol' => 'kn',
				'currency_prefix' => false
			),
			'CY' => array(
				'name' => 'Cyprus',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'CZ' => array(
				'name' => 'Czech Republic',
				'currency_code' => 'CZK',
				'currency_symbol' => 'Kč',
				'currency_prefix' => false
			),
			'DK' => array(
				'name' => 'Denmark',
				'currency_code' => 'DKK',
				'currency_symbol' => 'kr',
				'currency_prefix' => false
			),
			'FI' => array(
				'name' => 'Finland',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'FR' => array(
				'name' => 'France',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'DE' => array(
				'name' => 'Germany',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'IE' => array(
				'name' => 'Ireland',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'IT' => array(
				'name' => 'Italy',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'JP' => array(
				'name' => 'Japan',
				'currency_code' => 'JPY',
				'currency_symbol' => '&yen;',
				'currency_prefix' => true
			),
			'LU' => array(
				'name' => 'Luxembourg',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'MT' => array(
				'name' => 'Malta',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'MU' => array(
				'name' => 'Mauritius',
				'currency_code' => 'MUR',
				'currency_symbol' => 'Rs',
				'currency_prefix' => false
			),
			'MA' => array(
				'name' => 'Morocco',
				'currency_code' => 'MAD',
				'currency_symbol' => 'د.م.',
				'currency_prefix' => false
			),
			'NO' => array(
				'name' => 'Norway',
				'currency_code' => 'NOK',
				'currency_symbol' => 'kr',
				'currency_prefix' => false
			),
			'PT' => array(
				'name' => 'Portugal',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'QA' => array(
				'name' => 'Qatar',
				'currency_code' => 'QAR',
				'currency_symbol' => 'QR',
				'currency_prefix' => false
			),
			'RU' => array(
				'name' => 'Russia',
				'currency_code' => 'RUB',
				'currency_symbol' => '₽',
				'currency_prefix' => true
			),
			'ZA' => array(
				'name' => 'South Africa',
				'currency_code' => 'ZAR',
				'currency_symbol' => 'R',
				'currency_prefix' => true
			),
			'ES' => array(
				'name' => 'Spain',
				'currency_code' => 'EUR',
				'currency_symbol' => '&euro;',
				'currency_prefix' => false
			),
			'SE' => array(
				'name' => 'Sweden',
				'currency_code' => 'SEK',
				'currency_symbol' => 'kr',
				'currency_prefix' => false
			),
			'TR' => array(
				'name' => 'Turkey',
				'currency_code' => 'TRY',
				'currency_symbol' => '‎₺',
				'currency_prefix' => true
			),
			'AE' => array(
				'name' => 'United Arab Emirates',
				'currency_code' => 'AED',
				'currency_symbol' => '‎د.إ',
				'currency_prefix' => false
			),
			'GB' => array(
				'name' => 'United Kingdom',
				'currency_code' => 'GBP',
				'currency_symbol' => '&pound;',
				'currency_prefix' => true
			),
			'US' => array(
				'name' => 'United States',
				'currency_code' => 'USD',
				'currency_symbol' => '$',
				'currency_prefix' => true
			),
		);
		
		return apply_filters( 'propertyhive_countries', $countries );
	}

	/**
	 * Outputs the list of countries for use in dropdown boxes.
	 * @param string $selected_country (default: '')
	 * @param bool   $escape (default: false)
	 */
	public function country_dropdown_options( $selected_country = '', $escape = false ) {
		if ( $this->countries ) 
		{
			foreach ( $this->countries as $key => $value )
			{				
				echo '<option';
				if ( $selected_country == $key || ( $selected_country == '' && $key == 'GB' ) ) {
					echo ' selected="selected"';
				}
				echo ' value="' . esc_attr( $key ) . '">' . ( $escape ? esc_js( $value['name'] ) : $value['name'] ) . '</option>';
			}
		}
	}

	public function convert_price_to_gbp( $price, $currency_code )
	{
		if ( trim($price) == '' )
		{
			return $price;
		}

		$exchange_rates = get_option( 'propertyhive_currency_exchange_rates', array() );

		if ( isset($exchange_rates[$currency_code]) )
		{
			$price = $price / $exchange_rates[$currency_code];
		}

		return $price;
	}

	public function update_property_price_actual( $postID )
	{
		$countries = $this->countries;

		$department = get_post_meta( $postID, '_department', true );
		$country = get_post_meta( $postID, '_address_country', true );

		if (isset($countries[$country]))
		{
			if ( $department == 'residential-sales' )
			{
				$currency = get_post_meta( $postID, '_currency', true );
				if ( $country == '' )
				{
					$country = get_option( 'propertyhive_default_country', 'GB' );
				}
				if ( $currency == '' )
				{
					$currency = $this->get_country($country);
					$currency = $currency['currency_code'];
				}

				$price = get_post_meta( $postID, '_price', true );
				
				$converted_price = $this->convert_price_to_gbp( $price, $currency );

				update_post_meta( $postID, '_price_actual', $converted_price );
			}
			elseif ( $department == 'residential-lettings' )
			{
				$currency = get_post_meta( $postID, '_currency', true );
				if ( $country == '' )
				{
					$country = get_option( 'propertyhive_default_country', 'GB' );
				}
				if ( $currency == '' )
				{
					$currency = $this->get_country($country);
					$currency = $currency['currency_code'];
				}

				$rent = get_post_meta( $postID, '_rent', true );
				$rent_frequency = get_post_meta( $postID, '_rent_frequency', true );

				$price = $rent; // Stored in pcm
	            switch ($rent_frequency)
	            {
                    case "pppw":
                    {
                        $bedrooms = get_post_meta( $postID, '_bedrooms', true );
                        if ( ( $bedrooms !== FALSE && $bedrooms != 0 && $bedrooms != '' ) && apply_filters( 'propertyhive_pppw_to_consider_bedrooms', true ) == true )
                        {
                            $price = (($rent * 52) / 12) * $bedrooms;
                        }
                        else
                        {
                            $price = ($rent * 52) / 12;
                        }
                        break;
                    }
                    case "pw": { $price = ($rent * 52) / 12; break; }
                    case "pcm": { $price = $rent; break; }
                    case "pq": { $price = ($rent * 4) / 12; break; }
                    case "pa": { $price = ($rent / 12); break; }
	            }

                $converted_price = $this->convert_price_to_gbp( $price, $currency );

	            update_post_meta( $postID, '_price_actual', $converted_price );
			}
			if ( $department == 'commercial' )
			{
				if ( get_post_meta( $postID, '_for_sale', true ) == 'yes' )
				{
					$currency = get_post_meta( $postID, '_commercial_price_currency', true );
					if ( $country == '' )
					{
						$country = get_option( 'propertyhive_default_country', 'GB' );
					}
					if ( $currency == '' )
					{
						$currency = $this->get_country($country);
						$currency = $currency['currency_code'];
					}

					$price = get_post_meta( $postID, '_price_from', true );

					$converted_price = $this->convert_price_to_gbp( $price, $currency );

					update_post_meta( $postID, '_price_from_actual', $converted_price );

					$price = get_post_meta( $postID, '_price_to', true );

					$converted_price = $this->convert_price_to_gbp( $price, $currency );

					update_post_meta( $postID, '_price_to_actual', $converted_price );
				}
				if ( get_post_meta( $postID, '_to_rent', true ) == 'yes' )
				{
					$currency = get_post_meta( $postID, '_commercial_rent_currency', true );
					if ( $country == '' )
					{
						$country = get_option( 'propertyhive_default_country', 'GB' );
					}
					if ( $currency == '' )
					{
						$currency = $this->get_country($country);
						$currency = $currency['currency_code'];
					}

					$rent_units = get_post_meta( $postID, '_rent_units', true );

					$price = get_post_meta( $postID, '_rent_from', true );
		            switch ($rent_units)
		            {
		                case "pw": { $price = ($price * 52) / 12; break; }
		                case "pcm": { $price = $price; break; }
		                case "pq": { $price = ($price * 4) / 12; break; }
		                case "pa": { $price = ($price / 12); break; }
		            }

		            $converted_price = $this->convert_price_to_gbp( $price, $currency );

		            update_post_meta( $postID, '_rent_from_actual', $converted_price );

		            if ( get_post_meta( $postID, '_for_sale', true ) != 'yes' )
					{
						update_post_meta( $postID, '_price_from_actual', $converted_price );
					}

		            $price = get_post_meta( $postID, '_rent_to', true );
		            switch ($rent_units)
		            {
		                case "pw": { $price = ($price * 52) / 12; break; }
		                case "pcm": { $price = $price; break; }
		                case "pq": { $price = ($price * 4) / 12; break; }
		                case "pa": { $price = ($price / 12); break; }
		            }

		            $converted_price = $this->convert_price_to_gbp( $price, $currency );

		            update_post_meta( $postID, '_rent_to_actual', $converted_price );
				}
			}
		}
	}

	public function ph_update_currency_exchange_rates()
	{
		global $wpdb;

		if ( $this->countries ) 
		{
			$countries = $this->countries;

			$exchange_rates = array();
			$previous_exchange_rates = get_option( 'propertyhive_currency_exchange_rates' );

			$default_country = get_option( 'propertyhive_default_country', 'GB' );
			$selected_countries = get_option( 'propertyhive_countries', array( $default_country ) );

			foreach ( $countries as $key => $value )
			{	
				if (!isset($exchange_rates[$value['currency_code']]) && in_array($key, $selected_countries) && $value['currency_code'] != 'GBP')
				{
					// we haven't got this exchange rate
					$from   = 'GBP'; 
					$to     = $value['currency_code'];

					$exchangeRate = '';
					
					$url = 'https://finance.google.com/finance/converter?a=1&from=' . $from . '&to=' . $to;

					$response = wp_remote_get( $url );

					if ( is_array( $response ) )
					{
						$body = wp_remote_retrieve_body( $response );

						preg_match("/<span class=bld>(.*)<\/span>/", $body, $converted);

						if ( isset($converted[1]) && $converted[1] != '' )
						{
							$converted = preg_replace("/[^0-9.]/", "", $converted[1]);

							if ( $converted != '' )
							{
								$exchangeRate = $converted;
								$exchange_rates[$to] = $exchangeRate;
							}
						}
					}
					else
					{

					}

					if ( $exchangeRate == '' && isset($previous_exchange_rates[$to]) )
					{
						// if for some reason we get here and don't have an exchange rate
						$exchange_rates[$to] = $previous_exchange_rates[$to];
					}
				}
			}
			$exchange_rates['GBP'] = "1.0000";
			update_option( 'propertyhive_currency_exchange_rates', $exchange_rates );
			update_option( 'propertyhive_currency_exchange_rates_updated', date("Y-m-d") );

			// Loop through all on market properties and update _actual_price meta value to be price in GBP
			$args = array(
				'post_type' => 'property',
				'fields' => 'ids',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => '_on_market',
						'value' => 'yes',
					)
				),
				'nopaging' => true,
			);
			$property_query =  new WP_Query($args);

			if ($property_query->have_posts())
			{
				while ($property_query->have_posts())
				{
					$property_query->the_post();

					$this->update_property_price_actual( get_the_ID() );
				}
			}

			wp_reset_postdata();

		}
	}

}