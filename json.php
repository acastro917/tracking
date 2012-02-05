<?php
include('wp-config.php');
global $wpdb;
$i = 0;
$mypost= $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");

foreach ( $mypost as $mypost) {
$json_result[$i]['flight_num'].= $mypost->post_title;
$json_result[$i]['id'].=  $mypost->ID;
$author= $wpdb->get_results("SELECT display_name FROM $wpdb->users WHERE ID  = $mypost->post_author");
foreach ( $author as $author) {
	$json_result[$i]['airline'].= $author->display_name;
}

$mycomment= $wpdb->get_results("SELECT comment_content, comment_date FROM $wpdb->comments WHERE  comment_post_ID  = $mypost->ID ORDER BY comment_date DESC LIMIT 0,1");
if(isset($mycomment) && $mycomment != NULL){
	foreach ( $mycomment as $mycomment) {
		$json_result[$i]['Remarks'].= $mycomment->comment_content;
		$temp = $mycomment->comment_date;
		$temp = explode(' ',$temp);
		$json_result[$i]['date'].= date("F d, Y",strtotime($temp[0]));
		$json_result[$i]['time'].= date("g:i a", strtotime($temp[1]));
	
	}
}else{
}

$custom_field= $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE meta_value != 'default' AND post_ID  = $mypost->ID Limit 2,1");
foreach ( $custom_field as $custom_field) {
	$json_result[$i]['Remarks'].= $custom_field->meta_value;
}
$custom_field= $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE  meta_value != 'default' AND post_ID  = $mypost->ID Limit 3,1");
foreach ( $custom_field as $custom_field) {
	$json_result[$i]['custom_field_2'].= $custom_field->meta_value;
}
$custom_field= $wpdb->get_results("SELECT meta_value FROM $wpdb->postmeta WHERE  meta_value != 'default' AND post_ID  = $mypost->ID Limit 4,1");
foreach ( $custom_field as $custom_field) {
	$json_result[$i]['custom_field_3'].= $custom_field->meta_value;
}

$i++;
}
?>
<?php echo json_encode($json_result);?>