<?php
/**
 * Usually functions that return settings values
 */

if( ! function_exists( 'cxbc_site_url' ) ) :
function cxbc_site_url() {
	return get_bloginfo( 'url' );
}
endif;