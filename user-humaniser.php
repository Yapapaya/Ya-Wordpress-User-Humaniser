<?php
/*
Plugin Name: Ya! User Humaniser
Plugin URI: http://yapapayalabs.com
Description: Turns your users into people with multiple benefits
Version: 0.1
Author: Saurabh Shukla
Copyright: Saurabh Shukla

GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/
$human_content_structure= array(
    'people_taxonomy'    => array('People','Person'),
    'group_taxonomy'     => array('Groups','Group'),
    'people_profile'     => array('Profiles','Profile'),
    'people_activity'    => array('Activities','Activity')
);

class user_humaniser{
    
    var $content_structure=array();
    
    
    function __construct(){
        add_action('init',array($this,'human_setup_init'));
        add_action( 'user_register',array($this,'user_to_people') );
    }
    function user_humaniser(){
        $this->__construct();
    }
    
    function human_setup_init(){
        global $human_content_structure;
        $people_taxonomy    =   $human_content_structure['people_taxonomy'];
        $group_taxonomy     =   $human_content_structure['group_taxonomy'];
        $people_profile     =   $human_content_structure['people_profile'];
        $people_activity    =   $human_content_structure['people_activity'];
        $post_types=get_post_types();
        //global $wp_rewrite;
        
        register_taxonomy(
            strtolower($people_taxonomy[0]),
            $post_types,
            array(  
                'labels'                => array('name'=>$people_taxonomy[0],'singular_name'=>$people_taxonomy[1]),
                'show_tagcloud'         => true,
            )
        );
        register_taxonomy(
            strtolower($group_taxonomy[0]),
            NULL, // This will let us set this taxonomy for the profiles
            array(  
                'hierarchical'          => true,
                'labels'                => array('name'=>$group_taxonomy[0],'singular_name'=>$group_taxonomy[1]),
            )
        );
        define('EP_PROFILE', 1048576); //2^20
        register_post_type(
            strtolower($people_profile[0]),
            array(
                'labels'        => array('name'=>$people_profile[0],'singular_name'=>$people_profile[1]),
                'public'        => true,
                'supports'      => array('title','thumbnail'),
                'taxonomies'    => array(strtolower($group_taxonomy[0]),strtolower($people_taxonomy[0]))
                //'rewrite'       => array('ep_mask'=>EP_PROFILE),
                //'query_var'     => true
            )
        );
        register_post_type(
            strtolower($people_activity[0]),
            array(
                'labels'        => array('name'=>$people_activity[0],'singular_name'=>$people_activity[1]),
                'public'        => true,
                'supports'      => array('title','thumbnail'),
                'taxonomies'    => array(strtolower($group_taxonomy[0]),strtolower($people_taxonomy[0]))
                //'rewrite'       => array('ep_mask'=>EP_PROFILE),
                //'query_var'     => true
            )
        );
        //add_rewrite_endpoint('profile', EP_PROFILE);
    }
    function user_to_people($user_id=false){
        if(!$user_id){
           $people_raw=get_users();
        }else{
            $people_raw=get_users(array('include'=>array($user_id)));
        }
        foreach($people_raw as $people){
            $yup = wp_insert_term(
                $people->display_name, // the term 
                'people' // the taxonomy
            );
            $defaults = array(
                'post_status' => 'publish', 
                'post_type' => 'profiles',
                'post_title' => $people->display_name
            );
            $dup = wp_insert_post($defaults);
            $lup = wp_set_post_terms( $dup, sanitize_title($people->display_name), 'people');
        } 
        // Get all the existing users in the system
        
    }
}
global $user_humaniser;
$user_humaniser = new user_humaniser();
register_activation_hook(__FILE__,array('user_humaniser','user_to_people'));

/**add_action('activated_plugin','save_error');
function save_error(){
    delete_option('plugin_error');
    update_option('plugin_error',  ob_get_contents());
}
echo get_option('plugin_error');
 * 
 */
 
?>