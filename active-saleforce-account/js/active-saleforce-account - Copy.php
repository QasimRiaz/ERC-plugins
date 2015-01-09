<?php
/*
  Plugin Name: Active Saleforce Account
  Plugin URI:
  Description: Connect Wordpress with saleforce account
  Version: 1.2
  Author: Qasim Riaz
  License: GPl23
 */




function saleforce_scripts_plug() {
    if (is_admin()) {
         wp_register_script('backcallscc', plugins_url('js/cussaleforce.js', __FILE__), array('jquery'));
         
          wp_enqueue_script('backcallscc');
          
        $file_for_jav = admin_url('admin-ajax.php');
        $tran_arr = array('jaxfile' => $file_for_jav);
        wp_localize_script('backcallscc', 'fromphp', $tran_arr);
    }
}


add_action('init', 'saleforce_scripts_plug');
add_action('admin_menu', 'register_my_saleforce_menu_page');
add_action('wp_ajax_give_mycallauthentication', 'wp_sf_authentication');
add_action('wp_ajax_give_myupadatevalue', 'addnewsettings');
add_action('user_register','wp_user_map_sf');
add_action( 'profile_update', 'my_profile_update_sf', 10, 2 );

    

function register_my_saleforce_menu_page() {
    add_menu_page('Saleforce', 'Saleforce Account', 'manage_options', 'my-custom-saleforce-account', 'my_custom_saleforce_account');
}

function addnewsettings(){
    require_once 'config.php';
    $CLIENT_ID=CLIENT_ID;
    $secretvalue=CLIENT_SECRET;
    $REDIRECT_URI=REDIRECT_URI;
    
     $datarry['access_token']="";
     $datarry['instance_url']="";
     $datarry['refresh_token']="";
     $datarry['keyvalue']=$CLIENT_ID;
     $datarry['secretvalue']=$secretvalue;
     $datarry['redirecturl']=$REDIRECT_URI;
     
     add_option("saleforce_activation_settings",$datarry,"test");
     return True;
     die();
}
function my_custom_saleforce_account(){
    
    $settings=get_option("saleforce_activation_settings");
   
    if(empty($settings['access_token'])){
    
    if(empty($_GET['accesstoken'])){
     
     $dashboard='<h2>Saleforce Account</h2><table style="margen-top:10px;">
     
    <tr><td >To retrieve accounts from Salesforce via REST/OAuth.</td></tr>
     <tr><td><a style="margin-top: 20px;" class="btn btn-large" onclick="callauthentication()">Click here to Connect</a></td></tr>
     </table>';    
     echo $dashboard;
    
    }elseif(!empty($_GET['accesstoken'])){ 
    
    
   
    $datarry['access_token']=$_GET['accesstoken'];
    $datarry['instance_url']=$_GET['instanceurl'];
    $datarry['refresh_token']=$_GET['refreshtoken'];
    $datarry['keyvalue']=$settings['keyvalue'];
    $datarry['secretvalue']=$settings['secretvalue'];
    $datarry['redirecturl']=$settings['redirecturl'];
    update_option("saleforce_activation_settings",$datarry);
     echo '<h2 style="width: 300px;background-color: lightgreen;padding: 10px;margin-top:50px;">Saleforce Activited</h2>';
    //print_r(get_option("saleforce_activation_settings"));
    
    }
    }else{
        //print_r($settings);
 //  my_function();
      
       echo '<h2>Saleforce Account</h2><h2 style="width: 300px;background-color: lightgreen;padding: 10px;margin-top:50px;">Saleforce Activited</h2>';
    }
    
}

function wp_sf_authentication(){
    
   require_once 'config.php';
   
    $LOGIN_URI="https://login.salesforce.com";
   
    $auth_url = $LOGIN_URI
        . "/services/oauth2/authorize?response_type=code&client_id="
        . CLIENT_ID . "&redirect_uri=" . urlencode(REDIRECT_URI);
    
    echo $auth_url;
    die();
    //header('Location: ' . $auth_url);
}


function wp_user_map_sf($user_id){
  //do your stuff
   $status=true;
   $data = create_wp_sf_mapping($user_id,$status);
   $accesstoken=refresh_token();
   $settings=get_option("saleforce_activation_settings");
   $instanceurl=$settings['instance_url'];
   create_contact($data,$instanceurl,$accesstoken);
   //$meta_key="saleforce_user_id";
   //add_user_meta( $user_id, $meta_key, $meta_value);
   
   
}

function my_profile_update_sf( $user_id) {
 $status=false;
 $site_url =get_option('siteurl');; 
 $site_urll =  str_replace(".","-",$site_url);
 $new_site_urll =  str_replace("http://","",$site_urll);
 $data = create_wp_sf_mapping($user_id,$status);
 $id =(string) $user_id.'-'.$new_site_urll;
         //
      
         //$site_url.'_'.intval($user_id);
 $accesstoken=refresh_token();
 $settings=get_option("saleforce_activation_settings");
 $instanceurl=$settings['instance_url'];
 
//show_accounts($id,$instanceurl, $accesstoken);
update_account($id,$data,$instanceurl, $accesstoken);
    
    
    
}


function refresh_token(){
    
    $settings=get_option("saleforce_activation_settings");
    $LOGIN_URI="https://login.salesforce.com";
    $token_url =$LOGIN_URI . "/services/oauth2/token";
    $params = "grant_type=refresh_token"
    . "&client_id=" . $settings['keyvalue']
    . "&client_secret=" . $settings['secretvalue']
    . "&refresh_token=" . $settings['refresh_token'];



     $curl = curl_init($token_url);
     curl_setopt($curl, CURLOPT_HEADER, false);
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($curl, CURLOPT_POST, true);
     curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

     $json_response = curl_exec($curl);
     $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
     if ( $status != 200 ) {
          die("Error: call to token URL $token_url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
      }

       curl_close($curl);

        $response = json_decode($json_response, true);
        $access_token = $response['access_token'];
        $instance_url = $response['instance_url'];
        $datarry['access_token']=$access_token;
        $datarry['instance_url']=$instance_url;
        $datarry['refresh_token']=$settings['refresh_token'];
        $datarry['keyvalue']=$settings['keyvalue'];
        $datarry['secretvalue']=$settings['secretvalue'];
        $datarry['redirecturl']=$settings['redirecturl'];
        
        update_option("saleforce_activation_settings",$datarry);
        
        return $access_token;
}
function create_contact($data, $instance_url, $access_token) {
    $url = "$instance_url/services/data/v20.0/sobjects/Contact/";
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token",
                "Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $json_response = curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ( $status != 201 ) {
        die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
        //call_refreshtoken();
    }
    
    //echo "HTTP status $status creating Contact<br/><br/>";

    curl_close($curl);

    $response = json_decode($json_response, true);

    $id = $response["id"];

    //echo "New record id $id<br/><br/>";

    return True;
}
function update_account($id,$data, $instance_url, $access_token) {
    
 
$url = "$instance_url/services/data/v20.0/sobjects/Contact/external_src_id__c/$id";

$curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token",
                "Content-type: application/json"));
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ( $status != 204 ) {
        die("Error: call to URL $url failed with status $status, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
    }

  

    curl_close($curl);

 
}


function create_wp_sf_mapping($user_id,$status){
 
 
 $user_info = get_userdata($user_id);
 $all_meta_for_user = get_user_meta($user_id);
 
 $data =array(
        
        "Email" =>$user_info->user_email,
        "user_login_name__c" =>$user_info->user_nicename,
        "User_Role__c" =>$user_info->roles[0],
        "task_add_twitter_handle__c" =>$all_meta_for_user['task_add_twitter_handle'][0],
        "task_add_twitter_handle_status__c" =>$all_meta_for_user['task_add_twitter_handle_status'][0],
        "task_company_name__c" =>$all_meta_for_user['task_company_name'][0],
        "task_company_name_status__c" =>$all_meta_for_user['task_company_name_status'][0],
        "LastName" =>$all_meta_for_user['last_name'][0],
        "FirstName" =>$all_meta_for_user['first_name'][0]
           
   );
      if($status == true ){
       
          $site_url =get_option('siteurl');; 
          $site_urll =  str_replace(".","-",$site_url);
          $new_site_urll =  str_replace("http://","",$site_urll);
          $data['external_src_id__c']= (string) $user_id.'-'.$new_site_urll ;
                 
      }
   return  json_encode($data);
}



?>

