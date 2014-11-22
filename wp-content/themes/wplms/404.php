<?php
if(is_404()){
	$error404 = vibe_get_option('error404');
    if(isset($error404)){
        $page_id=  intval($error404);
        wp_redirect( get_permalink( icl_object_id($page_id, "page", false,ICL_LANGUAGE_CODE) ),301); 
        exit;
    }
    
}
?>