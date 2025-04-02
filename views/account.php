<?php
extract($args);

if (empty($responce) && empty($args['response'])) {
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

$alphabates = ["a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "#"];
$sorted_posts = array();
?>
<div class="bizpress-glossary">
    <div class="bizpress-glossary-header">
        <ul class="bizpress-glossary-header-list">
            <?php
            foreach ($alphabates as $key => $word) {
                $sorted_posts[$word] = array();
                foreach ($posts as $key => $value) {
                    $post_title = strtolower($value->title);
                    $first_word = substr(sanitize_text_field($post_title), 0, 1);
                    if (!empty($post_title) && $first_word == $word):
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
                    printf("<li class='%s' data-tab='%s'>%s</li>", "bizpress-glossary-header-item " . $active, strtolower($word), strtoupper($word));
                endif;
            }
            ?>
        </ul>
    </div>
    <div class="bizpress-glossary-content">
        <?php

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
                    echo '<div class="component bizpress-glossary-components ' . $active . '" data-tab="' . strtolower($key) . '">';
                    foreach ($value as $k => $v) {
                        $v->type = $post_type;
                        if($v->empty == true){
                            continue;
                        }
                        ?>
                        <div class="bizpress-glossary-component" data-id="<?php echo $v->ID; ?>">
                            <h4 class="bizpress-glossary-component-title"><?php echo $v->title; ?></h4>
                            <p class="bizpress-glossary-component-text"><?php echo $v->excerpt; ?></p>
                            <a class='bizpress-glossary-link' href="<?php echo apply_filters("cx_account_post_url", "https://bizinkcontent.com/accounting-terms/" . $v->slug, $v); ?>"><?php _e('Read More', 'bizink-client'); ?></a>
                        </div>
                        <?php
                    }
                    echo "</div>";
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