<?php

/**
 * Created by PhpStorm.
 * User: Adrian
 * Date: 2017-02-26
 * Time: 6:44 PM
 */

function formlift_track_impression( $formID )
{
    global $FormLiftUser;

//    if ( is_user_logged_in() && current_user_can( 'manage_options' ) )
//        return;

    if ( ! $FormLiftUser->hasImpression( $formID ) ){
	    $FormLiftUser->addImpression( $formID );
	    formlift_add_impression( $formID );
    }
}

add_action('formlift_after_get_form_code', 'formlift_track_impression' );

function formlift_track_submission( $formID )
{
    global $FormLiftUser;

//    if ( is_user_logged_in() && current_user_can( 'manage_options' ) )
//        return;

    if ( ! $FormLiftUser->hasSubmission( $formID ) ){
	    $FormLiftUser->addSubmission( $formID );
	    formlift_add_submission( $formID );
    }
}

add_action('formlift_success_submit', 'formlift_track_submission' );