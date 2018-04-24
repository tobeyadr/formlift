<?php

function formlift_db_update()
{

    if ( version_compare( get_option( FORMLIFT_VERSION_KEY ), FORMLIFT_VERSION, '<' ) )
    {
        /*
         * 1. We need to change the meta name for FORMLIF_FIELDS to the new one.
         * 2. We need to change the settings options to the new naming conventions
         * 3. The field name type for captcha has been changed to reCaptcha
         * 4.
         */
        echo 'do something';
    }

}
