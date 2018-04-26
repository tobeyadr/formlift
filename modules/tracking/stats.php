<?php
/*

This class runs independently of any operations you do and does not hinder the user experience.
Sends usage stats once daily.

*/
class FormLift_Stats_Collector {

    const DEST = 'https://formlift.net/';
    const TRANSIENT = 'formlift_stats_loop';
    const PASSWORD  = 'formlift_stats_pw';

	function send()
    {
        $url = get_site_url();
        $imps = formlift_get_all_impressions( date('Y-m-d H:i:s', strtotime('-1 days') ), current_time('mysql') );
        $subs = formlift_get_all_submissions( date('Y-m-d H:i:s', strtotime('-1 days') ), current_time('mysql') );

        $args = array(
            'url'           => $url,
            'impressions'   => intval( $imps ),
            'submissions'   => intval( $subs )
        );

        $destination = add_query_arg( array( 'formlift_action' => 'log_usage' ) , static::DEST );

        wp_remote_post( $destination, array( 'body' => $args, 'sslverify' => true ) );
    }

    function loop()
    {
        if ( get_formlift_setting( 'opt_out_of_usage_stats', false ) )
            return;

        if ( get_transient( self::TRANSIENT ) )
            return;

        $this->send();

        set_transient( self::TRANSIENT, true,24 * HOUR_IN_SECONDS );
    }
}

$FormLiftStatsCollector = new FormLift_Stats_Collector();

add_action( 'init', array( $FormLiftStatsCollector, 'loop' ) );