<?php
/*
	Plugin Name: Smarty
	Author: Tushar
	Author URI: http://localhost/test-tushar/
	Version: 1.0
	Description: This is custom plugin for adding custom data
	Plugin URI:http://localhost/test-tushar/
*/

function add_custom_plugin(){
	$labels = array(
		'name'                => __( 'Deals' ),
		'singular_name'       => __( 'Deal'),
		'menu_name'           => __( 'Deals'),
		'parent_item_colon'   => __( 'Parent Deal'),
		'all_items'           => __( 'All Deals'),
		'view_item'           => __( 'View Deal'),
		'add_new_item'        => __( 'Add New Deal'),
		'add_new'             => __( 'Add New'),
		'edit_item'           => __( 'Edit Deal'),
		'update_item'         => __( 'Update Deal'),
		'search_items'        => __( 'Search Deal'),
		'not_found'           => __( 'Not Found'),
		'not_found_in_trash'  => __( 'Not found in Trash')
	);
	$args = array(
		'label'               => __( 'deals'),
		'description'         => __( 'Best Crunchify Deals'),
		'labels'              => $labels,
		'supports'            => array( 'title', 'thumbnail', 'revisions',),
		'public'              => true,
		'hierarchical'        => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'has_archive'         => true,
		'can_export'          => true,
		'exclude_from_search' => false,
	        'yarpp_support'       => true,
		'taxonomies' 	      => array('post_tag'),
		'publicly_queryable'  => true,
		'capability_type'     => 'page'
);
	register_post_type( 'deals', $args );
	 register_taxonomy( 'categories', array('deals'), array(
        'hierarchical' => true, 
        'label' => 'Categories', 
        'singular_label' => 'Category', 
        'rewrite' => array( 'slug' => 'categories', 'with_front'=> false )
        )
    );
    register_taxonomy_for_object_type( 'categories', 'deals' );

}
add_action('init','add_custom_plugin');

function form_callback_action(){
	?>
<div id="postbox">
		<form id="new_post" name="new_post" method="post" action="" enctype="multipart/form-data">

		<!-- post name -->
		<p><label for="title">Title</label><br />
		<input type="text" id="title" value="" tabindex="1" size="20" name="title" />
		</p>

		<!-- post Category -->
		<p><label for="Category">Category:</label><br />
		<p><?php 
		 $my_tax_terms = get_terms( 'categories', array('hide_empty'=>false) );
		echo '<select required multiple="multiple" name="my_tax[]" id="my-tax" class="postform">';
		    foreach ($my_tax_terms as $tax_term) {
		        $selected = !empty($_POST['my_tax']) && in_array( $tax_term->term_id, $_POST['my_tax'] ) ? ' selected="selected" ' : '';
		        echo '<option value="'. $tax_term->term_id .'" '. $selected .'>'. $tax_term->name .'</option>';
		    }
		echo '</select>';
		//wp_dropdown_categories( 'show_option_none=Category&tab_index=4&taxonomy=categories' ); ?>
			
		</p>

		<p>
			<label for="contact">Contact</label><br />
			<input type='text' id='contact' name='contact' value='' tabindex="1" size="20" /> 
		</p>
		<!-- post Content -->
		<p><label for="address">Address</label><br />
		<textarea id="description" tabindex="3" name="description" cols="50" rows="6"></textarea>
		</p>

		<input type="hidden" name="action" value="new_post" />
		<?php wp_nonce_field( 'new-post' ); ?>
		    <input type="submit" name="submit" value="Insert" />

		</form>

</div>
<?php 

	if(isset($_POST['submit'])){
		if( 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) &&  $_POST['action'] == "new_post") {

		    // Do some minor form validation to make sure there is content
		    if (isset ($_POST['title'])) {
		        $title =  $_POST['title'];
		    } else {
		        echo 'Please enter a  title';
		    }
		    $terms = isset($_POST['my_tax']) ? (array) $_POST['my_tax'] : array();

			 //Cast array values as integers if $_POST['category'] contains IDs
			 $terms = array_map('intval',$terms);
		    $new_post = array(
		        'post_title'    => $title,		           
		        'post_status'   => 'publish', 
		        'tax_input' => array(
		            'categories' => $terms,
		        ),          // Choose: publish, preview, future, draft, etc.
		        'post_type' => 'deals'  //'post',page' or use a custom post type if you want to
		    );
		    //save the new post
		    $pid = wp_insert_post($new_post); 
			
		    update_field('contact', $_POST['contact'], $pid);
			update_field('address', $_POST['description'], $pid);
			//update_field('_file', $_POST['description'], $pid);
		}
	}
}
add_shortcode('form_data','form_callback_action');


add_shortcode('display_post','all_post');

function all_post(){

	$my_query = new WP_Query(array(
	'post_type' => 'deals',
	'post_status' => 'publish',
	
	
 ));	//creating wp_query instance
 
 if($my_query -> have_posts()){	//checking if post there or not
		
		while($my_query -> have_posts()){	//loop through all posts
			$my_query -> the_post();	//increment the loop
			//$term = get_term_by( 'term_id', get_query_var( 'types' ) ); 
			echo '<div id="inside" class="the-news main-content" style="text-align:center;">';
			echo '<h3>'; the_title(); echo '</h3>';
			echo '<h3>Contact:</h3><p>';the_field('contact');
			echo '</p>'; 
			
			echo '<h3> Address:</h3><p>';  the_field('address'); echo '</p>';
			
			
			
			echo '<strong> Category : </strong>';
				$terms = get_the_terms( $post->ID , 'categories' );
				foreach ( $terms as $term ) {
				echo $term->name;
				}
				the_category();
			echo '</div>';
			echo '<hr>';
			
		}
}
}

?>