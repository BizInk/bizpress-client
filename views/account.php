<?php
extract($args);

if(!empty($response) && gettype($response) == 'string'){
    echo "<p>" . __('Something went wrong. The data for this page could not be found.', 'bizink-client') . "</p>";
    if (defined('WP_DEBUG') && WP_DEBUG == true) {
        _e('Got a String for $responce in views/topics.php '.$response, 'bizink-client');
    }
}

if (empty($response) && empty($args['response'])) {
    echo "<p>" . __('Something went wrong. The data for this page could not be found.', 'bizink-client') . "</p>";
    if (defined('WP_DEBUG') && WP_DEBUG == true) {
        _e('Got a Null for $responce in views/topics.php', 'bizink-client');
    }
    return;
}


if ($response->status == 0) {
    if (empty($response->message)) {
        echo "<p>" . __('Sorry there was an error. There was an error retreveing the data for this page.', 'bizink-client') . "</p>";
    } else {
        echo "<p>{$response->message}</p>";
    }
    return;
}

$topics         = $response->topics;
$posts             = $response->posts;
$post_type         = $response->post_type;

/**
 * Manage posts in alphabatical order where $args is a posts array
 */

$alphabates = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "wxyz", "#"];
$sorted_posts = array();
$glossary_id = 'bizpress-' . $post_type.'-'.rand();

$item = 0;
$list_html = '';
foreach ($alphabates as $key => $word) {
    $sorted_posts[$word] = array();
    foreach ($posts as $key => $value) {
        $post_title = strtolower($value->title);
        $first_word = substr(sanitize_text_field($post_title), 0, 1);

        if (!empty($post_title) && $first_word == $word):
            $sorted_posts[$word][] = $value;
            unset($posts[$key]);
        elseif (!empty($post_title) && $word == 'wxyz' && ($first_word == 'w' || $first_word == 'x' || $first_word == 'y' || $first_word == 'z') ):
            $sorted_posts[$word][] = $value;
            unset($posts[$key]);
        elseif ($word == "#" &&  (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $first_word) || is_numeric($first_word))):
            $sorted_posts[$word][] = $value;
        endif;
    }
    // Display Alphabetical Letter Header
    
    if (!empty($sorted_posts[$word])):
        if ($word == "a"):
            $active = "active";
        else:
            $active = "";
        endif;
        $list_html .= "<li class='bizpress-glossary-header-item " . $active . "' data-tab='" . $item . "'>" . strtoupper($word) . "</li>";
        $item++;
    endif;
    
}

?>
<div id="<?php echo $glossary_id; ?>" class="bizpress-glossary" data-selected="0" data-last="<?php echo $item-1; ?>" data-id="<?php echo $glossary_id; ?>">
    <div class="bizpress-glossary-header">
        <div class="bizpress-glossary-header-btn bizpress-glossary-header-before">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l192 192c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L77.3 256 246.6 86.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-192 192z"/></svg>
        </div>
        <div class="bizpress-glossary-header-list-wrap">
            <ul class="bizpress-glossary-header-list">
                <?php echo $list_html; ?>
            </ul>
        </div>
        <div class="bizpress-glossary-header-btn bizpress-glossary-header-after">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>
        </div>
    </div>
    <div class="bizpress-glossary-content">
        <?php
        $item = 0;
        /**
         * Render posts in alphabatical order
         */
        if (!empty($sorted_posts)):
            foreach ($sorted_posts as $key => $value) {

                if (!empty($value)):
                    if ($key == "a"):
                        $active = "active";
                    else:
                        $active = "";
                    endif;
                    echo '<div class="component bizpress-glossary-components ' . $active . '" data-tab="' . $item . '">';
                    foreach ($value as $k => $v) {
                        $v->type = $post_type;
                        if(!empty($v->empty) && $v->empty == true){
                            continue;
                        }
                        if(!empty($v->hidden) && $v->hidden == true){
                            continue;
                        }
                        ?>
                        <div class="bizpress-glossary-component" data-id="<?php echo $v->ID; ?>">
                            <h4 class="bizpress-glossary-component-title"><?php echo $v->title; ?></h4>
                            <p class="bizpress-glossary-component-text"><?php echo $v->excerpt; ?></p>
                            <a class='bizpress-glossary-link' href="<?php echo apply_filters("cx_account_post_url", "https://bizinkcontent.com/accounting-terms/" . $v->slug, $v); ?>">
                            <?php _e('Read More', 'bizink-client'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/></svg></a>
                        </div>
                        <?php
                        
                    }
                    echo "</div>";
                    $item++;
                endif;
                
            }
        endif;
        ?>
    </div>
</div>
<?php

echo '<div style="display:none;" class="bizpress-data" id="bizpress-data"
data-siteid="' . (bizpress_anylitics_get_site_id() ? bizpress_anylitics_get_site_id() : "false") . '"
data-title="' . get_the_title(get_the_ID()) . '" 
data-url="' . get_permalink(get_the_ID()) . '" 
data-posttype="' . $post_type . '"
data-topics="false"
data-types="false" ></div>';
?>