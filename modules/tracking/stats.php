<?php
/*

This class runs independently of any opperations you do and does not hinder the user experience.
Sends usage stats twice daily.
Its hooked into the "Verify License" cron job as It already runs twice a day and We don't need another one.

*/
class FormLift_Stats_Collector {

	const SUBMISSION_PARAM = 'submissions';
	const CREATION_PARAM = 'creations';
	const IMPRESSION_PARAM = 'impressions';
	const OPTION = 'formlift_usage_stats';

	static $instance;

	function __construct()
	{
		if ( !get_option( static::OPTION ) ){

			$totals = self::get_stats_totals();

			add_option( static::OPTION, array(
				static::SUBMISSION_PARAM => $totals['submissions'],
				static::CREATION_PARAM => $totals['form_count'],
				static::IMPRESSION_PARAM => $totals['impressions']
			));
		}
	}

	public static function getInstance()
	{
		return static::$instance;
	}

	public static function send_submission()
	{
		$stats = get_option( static::OPTION );
		$stats[ static::SUBMISSION_PARAM ] += 1 ;
		update_option( static::OPTION, $stats );
	}

	public static function send_form_creation()
	{
		$stats = get_option( static::OPTION );
		$stats[ static::CREATION_PARAM ] += 1 ;
		update_option( static::OPTION, $stats );
	}

	public static function send_form_impression()
	{
		$stats = get_option( static::OPTION );
		$stats[ static::IMPRESSION_PARAM ] += 1 ;
		update_option( static::OPTION, $stats );
	}

	public static function send( $params )
	{
		if ( get_formlift_setting('opt_out_of_usage_stats') )
    		return $params;

		$stats = get_option( static::OPTION );
		$result = array_merge( $params, $stats );
		//reset for next job.
		$stats[static::SUBMISSION_PARAM] = 0;
		$stats[static::IMPRESSION_PARAM] = 0;
		$stats[static::CREATION_PARAM] = 0;
		update_option( static::OPTION, $stats );

		return $result;
	}

	public static function get_stats_totals()
    {

        $forms = get_all_formlift_forms();
        $impressions_total = 0;
        $submissions_total = 0;
        $form_count = 0;

        foreach ( $forms as $form ) {
            $impressions_total += intval( get_post_meta( $form->ID, 'num_impressions', true) );
            $submissions_total += intval( get_post_meta( $form->ID, 'num_submissions', true) );
            $form_count += 1;
        }

        return array( 'impressions' => $impressions_total, 'submissions' => $submissions_total, 'form_count' => $form_count );
    }

    public static function init()
    {
    	static::$instance = new FormLift_Stats_Collector();
    }
}

add_filter( 'formlift_verify_license_post_args', array( 'FormLift_Stats_Collector', 'send' ) );
add_action( 'formlift_success_submit', array('FormLift_Stats_Collector', 'send_submission' ) );
add_action( 'wp_ajax_nopriv_formlift_post_impression', array('FormLift_Stats_Collector', 'send_form_impression' ) );
add_action( 'formlift_before_form_importing', array('FormLift_Stats_Collector', 'send_form_creation' ) );
add_action( 'plugins_loaded', array( 'FormLift_Stats_Collector', 'init') );