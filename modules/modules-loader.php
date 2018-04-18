<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

foreach ( glob( dirname( __FILE__ ) . "/*/*.php" ) as $filename)
{
    include $filename;
}

foreach ( glob( dirname( __FILE__ ) . "/settings/*/*.php" ) as $filename)
{
    include $filename;
}