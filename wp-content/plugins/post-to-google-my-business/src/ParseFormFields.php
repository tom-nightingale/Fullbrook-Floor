<?php

namespace PGMB;

use  Exception ;
use  InvalidArgumentException ;
use  PGMB\API\APIInterface ;
use  PGMB\Google\LocalPost ;
use  PGMB\Google\MediaItem ;
use  PGMB\Placeholders\PostPermalink ;
use  PGMB\Placeholders\PostVariables ;
use  PGMB\Placeholders\SiteVariables ;
use  PGMB\Placeholders\UserVariables ;
use  PGMB\Placeholders\LocationVariables ;
use  PGMB\Placeholders\VariableInterface ;
use  PGMB\Vendor\Cron\CronExpression ;
use  PGMB\Vendor\Rarst\WordPress\DateTime\WpDateTimeImmutable ;
use  PGMB\Vendor\Rarst\WordPress\DateTime\WpDateTimeInterface ;
use  PGMB\Vendor\Rarst\WordPress\DateTime\WpDateTimeZone ;
use  PGMB\Vendor\Rarst\WordPress\DateTime\WpDateTime ;
class ParseFormFields
{
    private  $form_fields ;
    public function __construct( $form_fields )
    {
        if ( !is_array( $form_fields ) ) {
            throw new InvalidArgumentException( 'ParseFormFields expects Form Fields array' );
        }
        $this->form_fields = $form_fields;
    }
    
    /**
     * Get DateTime object representing the when a post will be first published
     *
     * @return bool|WpDateTime|false DateTime when the post is first published, or false when the post isn't scheduled
     * @throws Exception Invalid DateTime
     */
    public function getPublishDateTime()
    {
        return false;
    }
    
    /**
     * Parse the form fields and return a LocalPost object
     *
     * @param APIInterface $api
     * @param $parent_post_id
     *
     * @param $location_name
     *
     * @return LocalPost
     * @throws Exception
     */
    public function getLocalPost( APIInterface $api, $parent_post_id, $location_name )
    {
        if ( !is_numeric( $parent_post_id ) ) {
            throw new InvalidArgumentException( 'Parent Post ID required for placeholder parsing' );
        }
        $location = $api->get_location( $location_name, false );
        $placeholder_variables = $this->generate_placeholder_variables( $parent_post_id, $location );
        $summary = stripslashes( $this->form_fields['mbp_post_text'] );
        if ( mbp_fs()->is_plan_or_trial( 'business' ) ) {
            $summary = \MBP_Spintax::Parse( $summary );
        }
        $summary = $this->parse_placeholder_variables( $placeholder_variables, $summary );
        $summary = mb_strimwidth(
            $summary,
            0,
            1500,
            "..."
        );
        $topicType = $this->form_fields['mbp_topic_type'];
        //Throw an error when the PRODUCT type is chosen
        if ( $topicType == 'PRODUCT' ) {
            throw new InvalidArgumentException( __( 'Product posts are no longer supported by the Google My Business API. Please choose a different GMB post type.', 'post-to-google-my-business' ) );
        }
        $localPost = new LocalPost( $location->languageCode, $summary, $topicType );
        //Set alert type
        if ( $topicType === 'ALERT' ) {
            $localPost->setAlertType( $this->form_fields['mbp_alert_type'] );
        }
        //Add image/video
        $mediaItem = $this->get_media_item( $parent_post_id );
        if ( !empty($mediaItem) && $topicType !== 'ALERT' ) {
            $localPost->addMediaItem( $mediaItem );
        }
        // mbp_content_image mbp_featured_image
        //Add button
        
        if ( isset( $this->form_fields['mbp_button'] ) && $this->form_fields['mbp_button'] ) {
            $buttonURL = $this->parse_placeholder_variables( $placeholder_variables, $this->form_fields['mbp_button_url'] );
            $callToAction = new \PGMB\Google\CallToAction( $this->form_fields['mbp_button_type'], $buttonURL );
            $localPost->addCallToAction( $callToAction );
        }
        
        //Add offer
        
        if ( $topicType == 'OFFER' ) {
            $localPostOffer = new \PGMB\Google\LocalPostOffer( $this->form_fields['mbp_offer_coupon'], $this->form_fields['mbp_offer_redeemlink'], $this->form_fields['mbp_offer_terms'] );
            $localPost->addLocalPostOffer( $localPostOffer );
        }
        
        //Add Event (used by Offer too)
        
        if ( $topicType == 'OFFER' || $topicType == 'EVENT' ) {
            $eventTitle = ( $topicType == 'OFFER' ? $this->form_fields['mbp_offer_title'] : $this->form_fields['mbp_event_title'] );
            //get the appropriate event title
            $startdate = new \DateTime( $this->form_fields['mbp_event_start_date'], WpDateTimeZone::getWpTimezone() );
            $enddate = new \DateTime( $this->form_fields['mbp_event_end_date'], WpDateTimeZone::getWpTimezone() );
            $startDate = new \PGMB\Google\Date( $startdate->format( 'Y' ), $startdate->format( 'm' ), $startdate->format( 'd' ) );
            $startTime = new \PGMB\Google\TimeOfDay( $startdate->format( 'H' ), $startdate->format( 'i' ) );
            $endDate = new \PGMB\Google\Date( $enddate->format( 'Y' ), $enddate->format( 'm' ), $enddate->format( 'd' ) );
            $endTime = new \PGMB\Google\TimeOfDay( $enddate->format( 'H' ), $enddate->format( 'i' ) );
            $timeInterval = new \PGMB\Google\TimeInterval(
                $startDate,
                $startTime,
                $endDate,
                $endTime
            );
            if ( isset( $this->form_fields['mbp_event_all_day'] ) && $this->form_fields['mbp_event_all_day'] ) {
                $timeInterval->setAllDay( true );
            }
            $localPostEvent = new \PGMB\Google\LocalPostEvent( $eventTitle, $timeInterval );
            $localPost->addLocalPostEvent( $localPostEvent );
        }
        
        return $localPost;
    }
    
    public function get_media_items( $parent_post_id )
    {
        $mediaItems = [];
        if ( empty($this->form_fields['mbp_post_attachment']) || !is_array( $this->form_fields['mbp_post_attachment'] ) ) {
            return false;
        }
        foreach ( $this->form_fields['mbp_post_attachment'] as $type => $items ) {
            foreach ( $items as $item ) {
                $mediaItems[] = new MediaItem( $type, $item );
            }
        }
        return $mediaItems;
    }
    
    public function get_media_item( $parent_post_id )
    {
        
        if ( !empty($this->form_fields['mbp_post_attachment']) ) {
            $image_id = attachment_url_to_postid( $this->form_fields['mbp_post_attachment'] );
            
            if ( $image_id && wp_attachment_is_image( $image_id ) ) {
                $this->validate_wp_image_size( $image_id );
            } else {
                $this->validate_external_image_size( $this->form_fields['mbp_post_attachment'] );
            }
            
            return new \PGMB\Google\MediaItem( $this->form_fields['mbp_attachment_type'], $this->form_fields['mbp_post_attachment'] );
        } elseif ( isset( $this->form_fields['mbp_content_image'] ) && $this->form_fields['mbp_content_image'] && ($image_url = $this->get_content_image( $parent_post_id )) ) {
            return new \PGMB\Google\MediaItem( 'PHOTO', $image_url );
        } elseif ( isset( $this->form_fields['mbp_featured_image'] ) && $this->form_fields['mbp_featured_image'] && ($image_url = get_the_post_thumbnail_url( $parent_post_id, 'pgmb-post-image' )) ) {
            $image_id = get_post_thumbnail_id( $parent_post_id );
            $this->validate_wp_image_size( $image_id );
            return new \PGMB\Google\MediaItem( 'PHOTO', $image_url );
        }
        
        return false;
    }
    
    public function get_content_image( $post_id )
    {
        $images = get_attached_media( 'image', $post_id );
        if ( !($image = reset( $images )) ) {
            return false;
        }
        $image_details = wp_get_attachment_image_src( $image->ID, 'pgmb-post-image' );
        $this->validate_wp_image_size( $image->ID );
        return reset( $image_details );
        //Return the first item in the array (which is the url)
    }
    
    /**
     * Check whether a file uploaded within the WordPress Media Uploader matches the requirements for GMB
     *
     * @param int $image_id - ID of the image within WordPress
     */
    public function validate_wp_image_size( $image_id )
    {
        $path = get_attached_file( $image_id );
        list( $url, $width, $height, $is_intermediate ) = wp_get_attachment_image_src( $image_id, 'pgmb-post-image' );
        
        if ( $is_intermediate ) {
            $uploads_dir = wp_get_upload_dir();
            $intermediate = image_get_intermediate_size( $image_id, [ $width, $height ] );
            $path = $uploads_dir['basedir'] . "/" . $intermediate['path'];
            $url = $intermediate['url'];
            $width = $intermediate['width'];
            $height = $intermediate['height'];
        }
        
        $image_file_size = $this->get_local_file_size( $path, $url );
        if ( !$image_file_size ) {
            throw new InvalidArgumentException( __( 'Could not detect post image file size. Make sure the image file/url is accessible remotely.', 'post-to-google-my-business' ) );
        }
        $this->validate_image_props( $image_file_size, $width, $height );
    }
    
    /**
     * Get file size in bytes from a file on the local server
     *
     * @param $path
     *
     * @return false|int
     */
    public function get_file_size_from_path( $path )
    {
        return @filesize( $path );
    }
    
    /**
     * Try to determine file size by getting content-length from headers (not always available)
     *
     * @param $url
     *
     * @return bool|int
     */
    public function get_file_size_from_headers( $url )
    {
        $headers = wp_get_http_headers( $url );
        if ( !$headers || !isset( $headers['content-length'] ) ) {
            return false;
        }
        return intval( $headers['content-length'] );
    }
    
    /**
     * Try to determine the file size by downloading the file
     *
     * @param $url
     *
     * @return bool|false|int
     */
    public function get_file_size_from_download( $url )
    {
        if ( !function_exists( 'download_url' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        $filepath = download_url( $url );
        if ( is_wp_error( $filepath ) ) {
            return false;
        }
        $file_size = filesize( $filepath );
        unlink( $filepath );
        return $file_size;
    }
    
    public function get_local_file_size( $path, $url )
    {
        $image_file_size = $this->get_file_size_from_path( $path );
        if ( $image_file_size ) {
            return $image_file_size;
        }
        $image_file_size = $this->get_remote_file_size( $url );
        if ( $image_file_size ) {
            return $image_file_size;
        }
        return false;
    }
    
    public function get_remote_file_size( $url )
    {
        $image_file_size = $this->get_file_size_from_headers( $url );
        if ( $image_file_size ) {
            return $image_file_size;
        }
        $image_file_size = $this->get_file_size_from_download( $url );
        if ( $image_file_size ) {
            return $image_file_size;
        }
        return false;
    }
    
    /**
     * Check if externally hosted image meets the GMB requirements
     *
     * @param string $url - URL of the image
     */
    public function validate_external_image_size( $url )
    {
        list( $width, $height ) = getimagesize( $url );
        $image_file_size = $this->get_remote_file_size( $url );
        if ( !$image_file_size ) {
            throw new InvalidArgumentException( __( 'Could not detect post image file size. Make sure the image file/url is accessible remotely.', 'post-to-google-my-business' ) );
        }
        $this->validate_image_props( $image_file_size, $width, $height );
    }
    
    public function validate_image_props( $image_file_size, $width, $height )
    {
        if ( $width < 250 || $height < 250 ) {
            throw new InvalidArgumentException( sprintf( __( 'Post image must be at least 250x250px. Selected image is %dx%dpx', 'post-to-google-my-business' ), $width, $height ) );
        }
        
        if ( $image_file_size < 10240 ) {
            throw new InvalidArgumentException( sprintf( __( 'Post image file too small, must be at least 10 KB. Selected image is %s', 'post-to-google-my-business' ), size_format( $image_file_size ) ) );
        } elseif ( $image_file_size > 5242880 ) {
            throw new InvalidArgumentException( sprintf( __( 'Post image file too big, must be 5 MB at most. Selected image is %s', 'post-to-google-my-business' ), size_format( $image_file_size ) ) );
        }
    
    }
    
    /**
     * Get array of locations to post to. Return default location if nothing is selected
     *
     * @param $default_location
     *
     * @return array Locations to post to
     */
    public function getLocations( $default_location )
    {
        if ( !isset( $this->form_fields['mbp_selected_location'] ) || empty($this->form_fields['mbp_selected_location']) ) {
            return [ $default_location ];
        }
        
        if ( !is_array( $this->form_fields['mbp_selected_location'] ) ) {
            return [ $this->form_fields['mbp_selected_location'] ];
        } elseif ( is_array( $this->form_fields['mbp_selected_location'] ) ) {
            return $this->form_fields['mbp_selected_location'];
        }
        
        throw new \UnexpectedValueException( __( "Could not parse post locations", 'post-to-google-my-business' ) );
    }
    
    public function get_link_parsing_mode()
    {
        $valid_modes = [
            'none',
            'inline',
            'nextline',
            'table'
        ];
        if ( !isset( $this->form_fields['mbp_link_parsing_mode'] ) || !in_array( $this->form_fields['mbp_link_parsing_mode'], $valid_modes ) ) {
            return 'inline';
        }
        return $this->form_fields['mbp_link_parsing_mode'];
    }
    
    public function generate_placeholder_variables( $parent_post_id, $location )
    {
        $decorators = [
            'post_permalink'     => new PostPermalink( $parent_post_id ),
            'post_variables'     => new PostVariables( $parent_post_id, $this->get_link_parsing_mode() ),
            'user_variables'     => new UserVariables( $parent_post_id ),
            'site_variables'     => new SiteVariables(),
            'location_variables' => new LocationVariables( $location ),
        ];
        $decorators = apply_filters(
            'mbp_placeholder_decorators',
            $decorators,
            $parent_post_id,
            $location
        );
        $variables = [];
        foreach ( $decorators as $decorator ) {
            if ( $decorator instanceof VariableInterface ) {
                $variables = array_merge( $variables, $decorator->variables() );
            }
        }
        $variables = apply_filters( 'mbp_placeholder_variables', $variables, $parent_post_id );
        return $variables;
    }
    
    public function parse_placeholder_variables( $variables, $text )
    {
        return str_replace( array_keys( $variables ), $variables, $text );
    }

}