<?php
extract( $args );

if ( empty($responce) && empty($args['response']) ) {
	echo "<p>". __( 'Something went wrong. The data for this page could not be found.', 'bizink-client' ) ."</p>";
	if(defined('WP_DEBUG') && WP_DEBUG == true){
		_e('Got a Null for $responce in views/topics.php', 'bizink-client' );
	}
	return;
}


if ( $response->status == 0 ) {
	if(empty($response->message)){
		echo "<p>".__('Sorry there was an error. There was an error retreveing the data for this page.','bizink-client')."</p>";
	}
	else{
		echo "<p>{$response->message}</p>";
	}
	return;
}

$topics 		= $response->topics;
$posts 			= $response->posts;
$post_type 		= $response->post_type;

/**
 * Manage posts in alphabatical order where $args is a posts array
 */

$alphabates = [ "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "#" ];

$sorted_posts = array();
foreach ( $alphabates as $key => $word ) {
    $sorted_posts[ $word ] = array();
    foreach ( $posts as $key => $value ) {
        $post_title = strtolower( $value->title );
        $first_word = substr( sanitize_text_field( $post_title ) , 0, 1) ;
        if( !empty( $post_title ) && $first_word == $word ):
            $sorted_posts[ $word ][] = $value;
            unset( $posts[ $key ] );
        elseif( $word == "#" &&  ( preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $first_word) || is_numeric( $first_word )  ) ):
            $sorted_posts[ $word ][] = $value;
        endif;
    }
}

/**
 * Render posts in alphabatical order
 */
if( !empty( $sorted_posts ) ):
    printf( "<div class='%s' >", "bizpress-glossary" );
    
    foreach ($sorted_posts as $key => $value) {

        if( !empty( $value ) ):
            printf( "<div class='%s'><h2 class='%s'>%s</h2>", "component bizpress-glossary-component", "bizpress-glossary-title title", $key );
                echo '<p class="bizpress-glossary-paragraph">';
                    foreach ($value as $k => $v) {
                        $v->type = $post_type;
                        printf( "<a class='bizpress-glossary-link' href='%s'>%s</a><br>", filter_SSL(apply_filters( "cx_account_post_url", "https://bizinkcontent.com/accounting-terms/".$v->slug , $v )) , $v->title );
                    }
                echo "</p>";
            printf( "</div>" );
        endif;
        
    }
    
    printf( "</div>" );
endif;

echo '<div style="display:none;" class="bizpress-data" id="bizpress-data"
data-siteid="'.(bizpress_anylitics_get_site_id() ? bizpress_anylitics_get_site_id() : "false").'"
data-title="'.get_the_title( get_the_ID() ).'" 
data-url="'. get_permalink( get_the_ID() ) .'" 
data-posttype="'.$post_type.'"
data-topics="false"
data-types="false" ></div>';
?>