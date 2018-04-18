<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function formlift_submitV2()
{
    if ( ! isset( $_POST['formlift_submit_nonce']) || ! wp_verify_nonce( $_POST['formlift_submit_nonce'], 'formlift_submit' ) )
    	wp_die( 'Failed to verify you took this action.' );

	global $FormLiftUser;

	$formId = $_POST["form_id"];

	do_action( 'formlift_before_submit', $formId );

	$fields = get_post_meta( $formId, FORMLIFT_FIELDS, true );
	$form = new FormLift_Form( $formId );
	$errors = array();

	$errors = apply_filters( 'formlift_before_field_validation', $errors );

    /**
     * @var $fieldValidation FormLift_Validator
     */

	foreach ( $fields as $field_options ) {
		
		$validator = new FormLift_Validator( $field_options, $formId );

		$isValid = $validator->isValid();

		if ( is_wp_error( $isValid ) ){
			$errors[ $validator->getId() ] = $isValid->get_error_message();
		} else {
			if ( $validator->dataExists() ){
				$FormLiftUser->set_user_data( $validator->getName(), $validator->getData() );
			}
		}

	}

	$FormLiftUser->update();

	//upload files ONLY IF the user passes other tests first.
	if ( empty( $errors ) ) {
		$errors = apply_filters( 'formlift_validate_uploads', $errors );
	}

	//Final check to see if should send data or not.

	if ( empty( $errors ) ){

		do_action( 'formlift_success_submit', $formId );
		
		//decode because it get's encoded by default
		$packet = array( 
			'url' => html_entity_decode( $form->get_form_setting( 'post_url' ) )
		);

		$xid = get_post_meta( $formId, 'inf_form_xid', true );
		//in case the form isn't an infusionsoft form.
		if ( !empty( $xid ) ){
			$packet['xid'] = $xid;
		}

		if ( $form->get_form_setting('submit_via_ajax', false ) ){

			$_POST[ 'inf_form_xid' ] = $packet['xid'];
			wp_remote_post( $packet['url'], array(
				'body' => $_POST
			));

			wp_die(  json_encode( array( 'msg' => 'success' ) ) );
		}
		
		wp_die( json_encode( $packet ) );

	} elseif ( !empty( $errors ) ) {

		do_action( 'formlift_failed_submit', $formId );
		wp_die( json_encode( $errors ) );
	
	}

	wp_die('massive failure');

}

add_action( 'wp_ajax_nopriv_formlift_submit_form', 'formlift_submitV2' );
add_action( 'wp_ajax_formlift_submit_form', 'formlift_submitV2' );