<?php
require_once('./wp-config.php');//include wp-config.php file
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');

$url = 'product.json'; // path to your JSON file
$getFileData = file_get_contents($url); // put the contents of the file into a variable
$results = json_decode($getFileData); // decode the JSON feed
//echo '<pre>';
 foreach ($results as $result) {
    $post_id = wp_insert_post(array (
        'post_type' => 'product',
        'post_title'    => wp_strip_all_tags( $result->title ),
        'post_content'  => "$result->description",
        'post_status'   => 'publish',
        'comment_status' => 'closed',   
        "post_author" => 1,
        'ping_status' => 'closed'
    ));
    //echo  'POSTID=>'.$post_id;
    if($post_id){
      // insert post meta
        add_post_meta($post_id, 'product_highlight_1', $result->highlight1);
        add_post_meta($post_id, 'product_highlight_2', $result->highlight2);
        add_post_meta($post_id, 'product_highlight_3', $result->highlight3);
        add_post_meta($post_id, 'product_highlight_4', $result->highlight4);
        add_post_meta($post_id, 'product_highlight_5', $result->highlight5);
        add_post_meta($post_id, 'product_highlight_6', $result->highlight6);
        add_post_meta($post_id, 'product_highlight_7', $result->highlight7);
        add_post_meta($post_id, 'product_highlight_8', $result->highlight8);
    }
   
    $catId =  Array();
    foreach($result->tags as $tag){
        //Get term id by term_exists
        $term = term_exists( "$tag->tag_type", 'brands' );
        $catId[] = $term["term_id"]; 
        $termParent = term_exists( "services", 'brands' );
        $catId[] = $termParent["term_id"];  
    }
    
    add_post_meta($post_id, 'state_lists', $result->state);

    $catIDs = implode(",",array_unique($catId));
    $setpostmeta=wp_set_post_terms($post_id,$catIDs,'brands'); 
    //Enter data in repeater field
    $counter= 0;
    foreach($result->information as $resinfromation ){    //location
        $productkey1="product_name_".$counter."_product1";
        add_post_meta($post_id,$productkey1,$resinfromation->product_feature1);

        $productkey2="product_name_".$counter."_product2";
        add_post_meta($post_id,$productkey2,$resinfromation->product_feature2);

        $productkey3="product_name_".$counter."_product3";
        add_post_meta($post_id,$productkey3,$resinfromation->product_feature3);

        $productkey4="product_name_".$counter."_product4";
        add_post_meta($post_id,$productkey4,$resinfromation->product_feature4);

        $productkey5="product_name_".$counter."_product5";
        add_post_meta($post_id,$productkey5,$resinfromation->product_feature5);

        $productkey6="product_name_".$counter."_product6";
        add_post_meta($post_id,$productkey6,$resinfromation->product_feature6);  

        $productkey7="product_name_".$counter."_product7";
        add_post_meta($post_id,$productkey7,$resinfromation->product_feature7);

        $counter++;
    }
    add_post_meta($post_id,'product_name',$counter); 

    //echo '==========================Save featured Image in Admin ================================<br>';
    foreach($result->thumbnail as $image_url){
        //echo $image_url; 
        $image_name       = basename($image_url);
        $upload_dir       = wp_upload_dir();
        $image_data       = file_get_contents($image_url);
        $unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
        $filename         = basename( $unique_file_name );
        if( wp_mkdir_p( $upload_dir['path'] ) ) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        file_put_contents( $file, $image_data );
        $wp_filetype = wp_check_filetype( $filename, null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title'     => sanitize_file_name( $filename ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        set_post_thumbnail( $post_id, $attach_id );
    }

    //echo '==================Save Slider Images in ACF loop in admin ====================<br>';
    $counter=0;
    foreach($result->sliderImages as $value){
    //echo $value; 
    $image_value_name   = basename($value);
    $upload_dir         = wp_upload_dir();
    $images_data        = file_get_contents($value);
    $unique_file_name   = wp_unique_filename( $upload_dir['path'], $image_value_name );
    $file_name          = basename( $unique_file_name );
    if( wp_mkdir_p( $upload_dir['path'] ) ) {
        $files = $upload_dir['path'] . '/' . $file_name;
    } else {
        $files = $upload_dir['basedir'] . '/' . $file_name;
    }
    file_put_contents( $files, $images_data );
    $wp_filetype = wp_check_filetype( $file_name, null );
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name( $file_name ),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    $attach_id = wp_insert_attachment( $attachment, $files, $post_id );
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
    wp_update_attachment_metadata( $attach_id, $attach_data );  
    if(is_wp_error( $attach_data))  
        echo "Error in attachment";
    if(empty ($attach_data)) 
        echo "Unknown Resource"; 

    $key="add_more_product_image_".$counter."_images_url";   
    $counter++;
    add_post_meta($post_id,$key,$attach_id);      
    } 

    add_post_meta($post_id,'add_more_product_image',$counter);
                      
}
