<?php
/**
 * Plugin Name: Server Timing Output Buffer
 * Plugin URI: https://gist.github.com/felixarntz/9c3d7150c74082e69bb426393b68b12e
 * Description: Enables output buffering in the Performance Lab plugin's Server Timing API.
 * Version: 0.1.0
 * Author: Felix Arntz
 * Author URI: https://felix-arntz.me
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* Enable output buffer to also capture Server-Timing metrics during template rendering. */
add_filter( 'perflab_server_timing_use_output_buffer', '__return_true' );
