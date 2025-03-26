<?php

/*
* OLD Template - Both types use the account.php template
*/
extract( $args );
//convert posts in array

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


if( !empty( $args ) ):

    $alphabates = [ "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "#" ];

    $sorted_posts = array();
    foreach ( $alphabates as $key => $word ) {
        $sorted_posts[ $word ] = array();
        foreach ( $args as $key => $value ) {
            $post_title = strtolower( $value->title );
            $first_word = substr( sanitize_text_field( $post_title ) , 0, 1) ;
            
            if( !empty( $post_title ) && $first_word == $word ):
                $sorted_posts[ $word ][] = $value;
                unset( $args[ $key ] );
            elseif( $word == "#" &&  ( preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $first_word) || is_numeric( $first_word )  ) ):
                $sorted_posts[ $word ][] = $value;
            endif;
        }
        
    }
endif;

?>

<?php 
  if( !empty( $sorted_posts ) ):
        printf( "<div class='%s' >", "mail_alphabatic_container row  column-control" );
        
        foreach ($sorted_posts as $key => $value) {

            if( in_array( $key, array( 'a', 'j', 'r' ) ) ){
                printf( "<div class='%s'>", "col-md-4 col-12" );
            }

            if( !empty( $value ) ):
                printf( "<div class='%s'><h2 class='%s'>%s</h2>", "alphabate_word component title-text-component text text-left", "alphabate_label title title-2", $key );
                echo "<p>";
                foreach ($value as $k => $v) {
                    printf( "<a href='%s' target='_blank' >%s</a><br>", apply_filters( "cx_account_post_url", "https://bizinkcontent.com/accounting-terms/".$v->slug, $v) ,$v->title );
                }
                echo "</p>";
                printf( "</div>" );   
            endif;
            
            if( in_array( $key, array( 'i', 'q', '#' ) ) ){
                printf( "</div>" );
            }

        }
        
        printf( "</div>" );
  endif;  
?>
<style>
    div.mail_alphabatic_container h2.alphabate_label {
        text-transform:uppercase;
        font-size:25px;
        
    }
    div.mail_alphabatic_container {
        list-style:none;
    }
    div.mail_alphabatic_container div.posts {
        list-style:none;
        padding-left: 0px;
    }

    div.mail_alphabatic_container .post_title{
        font-size:15px !important;
        font-weight:normal;
        color:#00A3D3;
        margin-bottom: 0px;
        padding-bottom:0px;
    }
</style>
