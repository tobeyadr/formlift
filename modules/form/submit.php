<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function formlift_submitV2()
{
	if ( ! isset( $_POST['formlift_submit_nonce'] ) ){
		return;
	}

    if ( ! wp_verify_nonce( $_POST['formlift_submit_nonce'], 'formlift_submit' ) ){
    	if ( wp_doing_ajax() ){
		    wp_die( 'Failed to verify you took this action.' );
	    } else {
    		return;
	    }
    }

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
		    if ( isset( $field_options['name'] ) ) {
	            if ( $validator->dataExists() ){
		            $FormLiftUser->set_user_data( $validator->getName(), $validator->getData() );
	            } else {
		            /* honor blank field submission */
		            $FormLiftUser->remove_user_data( $validator->getName() );
	            }
            }
		}
	}

	$FormLiftUser->update();

	//upload files ONLY IF the user passes other tests first.
	if ( empty( $errors ) ) {
		$errors = apply_filters( 'formlift_pre_submit', $errors );
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

		/**
		 * doesn't work...
		 */
		if ( $form->get_form_setting('submit_via_ajax', false ) ){

			$_POST[ 'inf_form_xid' ] = $packet['xid'];

			$response = wp_remote_post( $packet['url'], array(
				'body' => $_POST
			));

			$response = wp_remote_retrieve_body( $response );

			wp_die(  json_encode( array( 'msg' => 'success', 'body' => $response ) ) );
		}

		/**
		 * this works...
		 */
		if ( wp_doing_ajax() ){
			wp_die( json_encode( $packet ) );
		} else {
			$_POST[ 'inf_form_xid' ] = $packet['xid'];
			$FormLiftUser->set_user_data( 'submission_packet', $packet );
			$FormLiftUser->set_user_data( 'data_packet', $_POST );
			add_action( 'template_redirect', 'submit_formlift_form_on_page_load' );
		}

	} elseif ( !empty( $errors ) ) {

		do_action( 'formlift_failed_submit', $formId );

		if ( wp_doing_ajax() ){
			//wp_die( json_encode( $packet ) );
			wp_die( json_encode( $errors ) );
		} else {
			return;
		}
	} else {
		wp_die('Something should have happened...');
	}
}

add_action( 'init', 'formlift_submitV2' );
add_action( 'wp_ajax_nopriv_formlift_submit_form', 'formlift_submitV2' );
add_action( 'wp_ajax_formlift_submit_form', 'formlift_submitV2' );

function submit_formlift_form_on_page_load()
{
	global $FormLiftUser;
	$packet = $FormLiftUser->get_user_data('submission_packet' );
	$data = $FormLiftUser->get_user_data('data_packet' );
	?>
	<p>Please wait...</p>
	<form id="formlift" method="post" action="<?php echo $packet['url']; ?>">
		<?php
		foreach ( $data as $name => $value ):
			?>
		<input type="hidden" name="<?php echo $name;?>" value="<?php echo $value;?>" />
		<?php
		endforeach;
		?>
	</form>
	<script>document.getElementById("formlift").submit();</script>
	<?php
	die();
}