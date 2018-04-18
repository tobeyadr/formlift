<?php
/**
 * Created by PhpStorm.
 * User: Adrian
 * Date: 2018-04-01
 * Time: 9:57 AM
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class FormLift_Awards
{

	static $submissionAwards = array(
		1,
		10,
		50,
		100,
		500,
		1000,
		5000,
		10000
	);

	static function sendBigAward( $amount )
	{

		$to = get_bloginfo( 'admin_email' );
		$subject = '[FormLift] Congratulations! New Milestone Reached.';
		$body = "
<p>Your Business has accumulated over $amount leads using FormLift since installing it not that long ago!</p>
<p>How awesome is that?</p>
<p>Give yourselves a pat on the back, and if your happy with your results, how about <a href=\"https://wordpress.org/support/plugin/formlift/reviews/\">giving FormLift a pat on the back too?</p>";
		$headers = array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $to, $subject, $body, $headers );
	}

}