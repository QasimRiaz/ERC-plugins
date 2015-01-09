<?php
/*
  Plugin Name: Active Saleforce Account
  Description: Connect Wordpress with saleforce account
  Author: Qasim Riaz

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
add_action('user_register','wp_user_map_sf',20, 2);
add_action( 'profile_update', 'my_profile_update_sf', 20, 2 );
register_deactivation_hook( __FILE__, 'on_deactivation'  );
    

function register_my_saleforce_menu_page() {
    add_menu_page('Saleforce', 'Saleforce Account', 'manage_options', 'my-custom-saleforce-account', 'my_custom_saleforce_account');
}
function on_deactivation(){
    delete_option( "saleforce_activation_settings" );

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
      $code=$_GET['accesstoken'];
      $responce = sf_get_access_token($code);
      
      if(empty($responce)){
      echo '<h2 style="width: 300px;background-color: lightgreen;padding: 10px;margin-top:50px;">Saleforce Activated</h2>';
      }else{
         echo '<h3>'.$responce.'</h3>'; 
      }
    }}else{
        echo '<h2 style="width: 300px;background-color: lightgreen;padding: 10px;margin-top:50px;">Saleforce Activated</h2>';
    }
   
    
}

function wp_sf_authentication(){
    
   $salesforcesettingskeys = get_option("sf_settings_client_keys"); 
   $auth_url = $salesforcesettingskeys['login_url']
        . "/services/oauth2/authorize?response_type=code&client_id="
        . $salesforcesettingskeys['client_id'] . "&redirect_uri=" . urlencode($salesforcesettingskeys['redirect_uri']);
    
    echo $auth_url;
    die();
    //header('Location: ' . $auth_url);
}


function wp_user_map_sf($user_id){
  //do your stuff
   $status=true;
   
   $account_array= create_wp_sf_mapping_account($user_id,$status);
   $accesstoken=refresh_token();
   $settings=get_option("saleforce_activation_settings");
   $instanceurl=$settings['instance_url'];
   $all_meta_for_user = get_user_meta($user_id);
   $companyname=$all_meta_for_user['Company_Name'][0];
   $account_id = create_Account($account_array,$instanceurl,$accesstoken);
   $Contact_array= create_wp_sf_mapping_contact($user_id,$status,$account_id);
   create_contact($Contact_array,$instanceurl,$accesstoken);
   
   
   //$meta_key="saleforce_user_id";
   //add_user_meta( $user_id, $meta_key, $meta_value);
   
   
}
function my_profile_update_sf( $user_id) {
 $status=false;
 $site_url =get_option('siteurl');; 
 $site_urll =  str_replace(".","-",$site_url);
 $new_site_urll =  str_replace("http://","",$site_urll);

 $account_array= create_wp_sf_mapping_account($user_id,$status);
 $all_meta_for_user = get_user_meta($user_id);
 

 $companyname=$all_meta_for_user['Company_Name'][0]; 
 $id =(string) $user_id.'-'.$new_site_urll;
 $accesstoken=refresh_token();
 
 if (strpos($accesstoken,"Error: call to URL") !== false) {
     
    return $accesstoken;
  }
 
 $settings=get_option("saleforce_activation_settings");
 $instanceurl=$settings['instance_url'];
 
    //show_accounts($id,$instanceurl, $accesstoken);
 
 $account_id = update_account($companyname,$account_array,$instanceurl, $accesstoken);

 $Contact_array= create_wp_sf_mapping_contact($user_id,$status,$account_id);
 update_Contact($id,$Contact_array,$instanceurl, $accesstoken);
    
}


function sf_get_access_token($code){
  
 $salesforcesettingskeys = get_option("sf_settings_client_keys");
 $token_url = $salesforcesettingskeys['login_url'] . "/services/oauth2/token";
 $params = "code=" . $code
    . "&grant_type=authorization_code"
    . "&client_id=" .  $salesforcesettingskeys['client_id']
    . "&client_secret=" . $salesforcesettingskeys['client_secret']
    . "&redirect_uri=" . urlencode($salesforcesettingskeys['redirect_uri']);

  $curl = curl_init($token_url);
  curl_setopt($curl, CURLOPT_HEADER, false);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

  $json_response = curl_exec($curl);
  $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

  if ( $status != 200 ) {
     $error_code = "Error: call to token URL $token_url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl);
     return  $error_code;
     
  }

curl_close($curl);

$response = json_decode($json_response, true);
$access_token = $response['access_token'];
$instance_url = $response['instance_url'];
$refresh_token = $response['refresh_token'];

if (!isset($access_token) || $access_token == "") {
    $error_code = "Error - access token missing from response!";
    return  $error_code;
}

if (!isset($instance_url) || $instance_url == "") {
    $error_code = "Error - instance URL missing from response!";
    return  $error_code;
}



    $datarry['access_token']=$access_token;
    $datarry['instance_url']=$instance_url;
    $datarry['refresh_token']=$refresh_token;
    add_option("saleforce_activation_settings",$datarry,"test");   
    
}
function refresh_token(){
    
    $settings=get_option("saleforce_activation_settings");
    $salesforcesettingskeys = get_option("sf_settings_client_keys"); 
    
    $token_url = $salesforcesettingskeys['login_url'] . "/services/oauth2/token";
    
    $params = "grant_type=refresh_token"
    . "&client_id=" . $salesforcesettingskeys['client_id']
    . "&client_secret=" . $salesforcesettingskeys['client_secret']
    . "&refresh_token=" . $settings['refresh_token'];



     $curl = curl_init($token_url);
     curl_setopt($curl, CURLOPT_HEADER, false);
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($curl, CURLOPT_POST, true);
     curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

     $json_response = curl_exec($curl);
     $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
     if ( $status != 200 ) {
          $error = "Error: call to token URL $token_url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl);
          return $error;
      }

       curl_close($curl);

        $response = json_decode($json_response, true);
        $access_token = $response['access_token'];
        $instance_url = $response['instance_url'];
        $datarry['access_token']=$access_token;
        $datarry['instance_url']=$instance_url;
        $datarry['refresh_token']=$settings['refresh_token'];
        
        
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

    return  $id ;
}
function create_Account($data, $instance_url, $access_token) {
    $url = "$instance_url/services/data/v20.0/sobjects/Account/";
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

    return $id;
}


function update_account($id,$data, $instance_url, $access_token) {
    
 
$url = "$instance_url/services/data/v20.0/sobjects/Account/Company_Name__c/$id";

$curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token",
                "Content-type: application/json"));
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

   $json_response = curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ( $status != 204 ) {
        $error = "Error: call to URL $url failed with status $status, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl);
        return $error;
        
        
    }
  curl_close($curl);
  $idd = show_account($id, $instance_url, $access_token);
  return  $idd;
}
function update_Contact($id,$data, $instance_url, $access_token) {
    
 
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
        $error = "Error: call to URL $url failed with status $status, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl);
        return $error;
        
        
    }

  

    curl_close($curl);

 
}
function show_account($id, $instance_url, $access_token) {
    
    $url = "$instance_url/services/data/v20.0/sobjects/Account/Company_Name__c/$id";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Authorization: OAuth $access_token"));

    $json_response = curl_exec($curl);

    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ( $status != 200 ) {
        die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
    }

   // echo "HTTP status $status reading account<br/><br/>";

    curl_close($curl);

    $response = json_decode($json_response, true);
    
    return $response['Id'];
}

function create_wp_sf_mapping_contact($user_id,$status,$account_id){
 $user_info = get_userdata($user_id);
 $all_meta_for_user = get_user_meta($user_id);
 $salesforcekeys=get_option("sf_um_contact_keys");
 foreach ($salesforcekeys as $salesforcekeys_name => $salesforcekeys_value) {
   
    $data[$salesforcekeys_name] = $all_meta_for_user[$salesforcekeys_value][0];  
     
 }
 $data['Email']=$user_info->user_email;
 $data['AccountId']=$account_id;
if($status == true ){
 $site_url =get_option('siteurl');; 
 $site_urll =  str_replace(".","-",$site_url);
 $new_site_urll =  str_replace("http://","",$site_urll);
 $data['external_src_id__c']= (string) $user_id.'-'.$new_site_urll ;
                 
}
   return  json_encode($data);
}

function create_wp_sf_mapping_account($user_id,$status){
 $user_info = get_userdata($user_id);
 $all_meta_for_user = get_user_meta($user_id);
 $salesforcekeys=get_option("sf_um_account_keys");
 foreach ($salesforcekeys as $salesforcekeys_name => $salesforcekeys_value) {
   
    $data[$salesforcekeys_name] = $all_meta_for_user[$salesforcekeys_value][0];  
     
 }
 $data['User_Role__c']=$user_info->roles[0];
 
 if($status == true){
 $data['Company_Name__c']=$all_meta_for_user['Company_Name'][0];
 }
 
 return  json_encode($data);
}




?>
