<?php

/*
  Plugin Name: Salesforce Mapping with User Meta Production
  Description: Saleforce Fields Map with User meta fields.
  Author: Qasim Riaz
 
 */





add_action('admin_menu', 'register_my_saleforce_mapping');
// example custom dashboard widget
function register_my_saleforce_mapping() {
    add_menu_page('Salesforce Mapping', 'Salesforce Mapping', 'manage_options', 'my-custom-saleforce-mapping', 'my_saleforce_mapaing');
}
function my_saleforce_mapaing(){
    
   delete_option( "sf_settings_client_keys" );
   delete_option( "sf_um_contact_keys" );
   delete_option( "sf_um_account_keys" );
   $sf_contact= array(
       
        "user_login_name__c" =>"nickname",
        "LastName" =>"last_name",
        "FirstName" =>"first_name"
    );
   
   $sf_account= array(
        
        "Name"=>"Company_Name",
        "Phone"=>"Phone",
        "American_Rodeo_2014_booth_size__c"=>"American_Rodeo_2014_booth_size",
        "American_Rodeo_2014_Price__c"=>"American_Rodeo_2014_Price"
        
//        "task_add_twitter_handle__c" =>"task_add_twitter_handle",
//        "task_add_twitter_handle_status__c" =>"task_add_twitter_handle_status",
//        "task_company_name__c" =>"task_company_name",
//        "task_company_name_status__c" =>"task_company_name_status"
    );
    
    $sf_settings_keys = array(
        
        
        "client_id" =>"3MVG9fMtCkV6eLhcZRDxkfhX4nsDrfWFYWmWQhkdQBPkhRtLHPF4JRSbQp6m7MmpkuzIqWmgCJx.oDmboDSAX",
        "client_secret" =>"9202231034327064732",
        "redirect_uri" =>"https://srcdemo.wpengine.com/wp-content/plugins/active-saleforce-account/oAth-responce.php",
        "login_url" =>"https://login.salesforce.com"
        
        );
     
    
     add_option("sf_settings_client_keys",$sf_settings_keys,"test");    
     add_option("sf_um_contact_keys",$sf_contact,"test");
     add_option("sf_um_account_keys",$sf_account,"test");
     $salesforcekeys_contact=get_option("sf_um_contact_keys");
     $salesforcekeys_account=get_option("sf_um_account_keys");
     $salesforcesettingskeys = get_option("sf_settings_client_keys"); 
     $output = '<h1>Salesforce Mapping with User Meta Keys</h1><table class="mytable"> <tr><td><h3>Salesforce Key</h3></td><td><h3>User Meta Key</h3></td></tr>';
    
     foreach ($salesforcekeys_contact as $salesforcekeys_name => $salesforcekeys_value) {
         $output .= '<tr><td>'.$salesforcekeys_name.'</td><td>'.$salesforcekeys_value.'</td></tr>';
     }
     
     foreach ($salesforcekeys_account as $salesforce_name => $salesforce_value) {
         $output .= '<tr><td>'.$salesforce_name.'</td><td>'.$salesforce_value.'</td></tr>';
     }
     
     $output.='</table>';
      
     echo $output;
      
     $clientkeys = '<h1>Salesforce Account Setting Keys</h1> <table  class="mytable"><tr>
      <td>Client ID </td><td>'.$salesforcesettingskeys['client_id'].'</td></tr>
      <tr><td>Client Secret</td><td>'.$salesforcesettingskeys['client_secret'].'</td></tr>
      <tr><td>Redirect Url</td><td>'.$salesforcesettingskeys['redirect_uri'].'</td></tr>
      </table>';
      echo $clientkeys;
 }
 
?>


