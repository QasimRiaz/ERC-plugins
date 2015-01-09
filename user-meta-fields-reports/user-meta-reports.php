<?php

/*
  Plugin Name: User Meta Fields Reports
  Description: User Meta Fields Reports
  Version: 1.0
  Author: Qasim Riaz
  Author URI:

 */


if (!empty($_GET['fieldname'])) {
    //  require_once('../../../wp-load.php');
    $filed_name = $_GET['fieldname'];
    $user_id = $_GET['userid'];
    $base_url = "http://" . $_SERVER['SERVER_NAME'];
    $user_last = get_user_meta($user_id, $filed_name);

    //echo '<pre>';
    //print_r($user_last);
    //exit;
    $download_array = $user_last[0]['file'];



    header('Location: ' . $base_url . '/wp-content/plugins/user-meta-fields-reports/download-lib-one.php?filename=' . $download_array);
}

function userreport_scripts_plug() {

    if (is_admin()) {
        wp_register_script('reportusermeta', plugins_url('js/reportsfield.js', __FILE__), array('jquery'));
        wp_register_script('backcalljsstable', plugins_url('js/jquery.dataTables.js', __FILE__), array('jquery'));
        wp_register_script('Collapsible', plugins_url('js/dataTables.tableTools.js', __FILE__), array('jquery'));
        wp_enqueue_script('reportusermeta');
        wp_enqueue_script('backcalljsstable');
        wp_enqueue_script('Collapsible');
        $file_for_jav = admin_url('admin-ajax.php');
        $tran_arr = array('jaxfile' => $file_for_jav);
        wp_localize_script('backcallsc', 'fromphp', $tran_arr);
    }
}

function my_adminr_report_theme_style() {


    wp_enqueue_style('my-admin-theme-table', plugins_url('css/jquery.dataTables.css', __FILE__));
    wp_enqueue_style('my-admin-theme-tablee', plugins_url('css/dataTables.tableTools.css', __FILE__));
}

add_action('admin_enqueue_scripts', 'my_adminr_report_theme_style');
add_action('init', 'userreport_scripts_plug');
add_action('wp_ajax_give_getreportresponce', 'sponsor_task_filter');
add_action('wp_ajax_give_create_zip', 'downloadfiles');

// download files for user

function downloadfiles() {

    $colvalue = $_POST['colVal'];
    $array_user_ids = $_POST['ids'];
    $z = 0;
    foreach ($array_user_ids as $id) {
        $file_url = get_user_meta($id, $colvalue);
        $user_lastt[$z] = $file_url[0]['file'];

        $z++;
    }
    //var_dump($user_lastt);
    echo json_encode($user_lastt);

    die();
}

// example custom dashboard widget
add_action('admin_menu', 'my_users_menu');

function my_users_menu() {
    add_users_page('Reports', 'Reports', 'read', 'my-sponsor-reports', 'my_sponsor_reports_function');
}

function my_sponsor_reports_function() {


    echo $title = "<h1>Sponsor Reports</h1>";

    $site_url = 'http://' . $_SERVER['SERVER_NAME'];


    $test = 'user_meta_manager_data';
    $result = get_option($test);
    // echo '<pre>';
    // print_r($result);
    $task_drop_down = '<select style="width:400px;" name="taske_fields" id="taske_fields" ><option value="">-----------   Select a Task    --------</option>';
    foreach ($result['profile_fields'] as $profile_field_name => $profile_field_settings) {

        if (strpos($profile_field_name, "status") !== false) {
            if ($profile_field_settings['type'] == "select") {
                $task_drop_down.='<option value="' . $profile_field_settings['label'] . '">' . $profile_field_settings['label'] . '</option>';
            }
        }
    }
    $task_drop_down.='</select>';


    /* foreach (get_editable_roles() as $role_name => $role_info) {
      $roles_array .= "<option value='".$role_name."'> ".$role_name."</option>";
      }
      $content= "<table>
      <tr>
      <td>Select Task Name</td>
      <td>".$inner_html."</td>
      </tr>
      <tr>
      <td>Select Task Status</td>
      <td><select name='status' style='width:150px;' id='status_val'><option value='Pending'>Pending</option><option value='Complete'>Complete</option></select></td>
      </tr>
      <tr>
      <td>Select Sponsor Role</td>
      <td><select name='sponsor_role'style='width:150px;' id='sponsor_role'>".$roles_array."</select></td>
      </tr>

      <tr>
      <td><input type='submit' class='btn btn-large btn-info' onclick='getuserresponce()'  value='Submit'/></td>
      <td></td>
      </tr>"  ;

      echo $content; */

    global $wpdb;
    $tasklable = $_POST['tasklabel'];
    $taskestatus = $_POST['taskestatus'];
    $sponsorrole = $_POST['sponsorrole'];


    $test = 'user_meta_manager_data';
    $get_keys_array_result = get_option($test);


    $query = "SELECT DISTINCT user_id
    FROM " . $wpdb->usermeta;

    $query_th = "SELECT meta_key
     FROM " . $wpdb->usermeta . " WHERE  `user_id` = 1 AND  `meta_key` LIKE  'task_%'";

    $table_head = $wpdb->get_results($query_th);

    $i = 4;
    $j = 1;
    $file_upload_list.='<select id="file_upload" ><option value="">Select a Download Field</option>';
    $meta_keys.='<select id="meta_keys_filter"  onchange="add_filter_input(this)"><option value="">Select a Field</option>';
    foreach ($get_keys_array_result['profile_fields'] as $profile_field_name => $profile_field_settings) {

        if (strpos($profile_field_name, "task") !== false) {

            $head_html .= '<th>' . $profile_field_settings['label'] . '</th>';
            if ($profile_field_settings['type'] == 'color') {
                $file_upload_list.='<option value="' . $profile_field_name . '">' . $profile_field_settings['label'] . '</option>';
            }
            if (strpos($profile_field_name, "status") !== false) {
                if ($profile_field_settings['type'] == "select") {
                    $meta_keys.=' <option value="col' . $i . '_filter" id="' . $i . '">' . $profile_field_settings['label'] . '</option>';
                }
            }
            $meta_kesy_filter_list.='<td class="meta_filter_box" style="display:none;" id="filter_col' . $i . '" data-column="' . $i . '">
               
                  <input type="text" class="column_filter" id="col' . $i . '_filter">
            </td>';

            if ($j <= 2) {

                $showhidefields.='<li><input type="checkbox" class="my-toggle" data-column="' . $i . '" onclick="check_box_value(this);" checked>' . $profile_field_settings['label'] . '</li>';
                $j++;
            } else {
                $showhidefields.='<li><input type="checkbox" class="my-toggle" data-column="' . $i . '" onclick="check_box_value(this);" >' . $profile_field_settings['label'] . '</li>';
                $j++;
            }
            $i++;
        }
    }
    $meta_keys.='</select>';
    $file_upload_list.='</select>';

    /* foreach ($table_head as $aidd) {

      $head_html .= '<th>' . $aidd->meta_key . '</th>';
      } */



    //echo $query;
    $result = $wpdb->get_results($query);
    foreach ($result as $aid) {
        $author_info = get_userdata($aid->user_id);
        $sponsrole = $author_info->roles[0];
        $all_meta_for_user = get_user_meta($aid->user_id);
        $inner_html .= '<tr id="' . $aid->user_id . '" class="rowselect"><td>' . $all_meta_for_user['first_name'][0] . ' ' . $all_meta_for_user['last_name'][0] . '</td><td>' . $author_info->user_email . '</td><td>' . $author_info->roles[0] . '</td><td>' . $all_meta_for_user['Company_Name'][0] . '</td>';


        /* foreach ($table_head as $aidd) {


          $inner_html .= '<td>' . $all_meta_for_user[$aidd->meta_key][0] . '</td>';
          } */

        foreach ($get_keys_array_result['profile_fields'] as $profile_field_name => $profile_field_settings) {
            if (strpos($profile_field_name, "task") !== false) {
                if ($profile_field_settings['type'] == 'color') {



                    $file_info = unserialize($all_meta_for_user[$profile_field_name][0]);
                    //$file_url = $file_info['file'];
                    $file_user_id = $file_info['user_id'];
                    if (!empty($file_info)) {
                        $inner_html .= '<td><a href="users.php?page=my-sponsor-reports&userid=' . $aid->user_id . '&fieldname=' . $profile_field_name . '" >Download</a></td>';
                    } else {
                        $inner_html .='<td></td>';
                    }
                } else {
                    $inner_html .= '<td>' . $all_meta_for_user[$profile_field_name][0] . '</td>';
                }
            }
        }




        $inner_html .="</tr>";
    }

    foreach (get_editable_roles() as $role_name => $role_info) {
        $roles_array .='<option value="' . $role_name . '">' . $role_name . '</option>';
    }
    $mydatatable = '<hr>
        <div class="">
        
 <div class="col-left" style="float:left;margin-top:22px;">
<input class="toggle-box" id="header2" type="checkbox" style="display:none;width:auto;" >
<label for="header2" class="my-filter-col" style="width:auto;" >Filter</label>
<div class="show-hide-div" style="width:auto;height:auto!important;"><table class="display" style="margin-bottom: 48px;"cellspacing="0" >
          <tbody>
          <tr>
          <td>&nbsp;&nbsp;&nbsp;</td>
          </tr>
          <tr>
          <td>Task</td><td id="status-eg" style="display:none;">(Status:e.g Pending)</td>
          </tr>
          <tr id="my_filter_row">
          <td>' . $meta_keys . '</td>' . $meta_kesy_filter_list . '
          </tr>
          <tr>
          <td>&nbsp;&nbsp;&nbsp;</td>
          </tr>
          <tr>
          <td>Role</td>
          </tr>
          <tr>
          <td id="filter_col2" data-column="2">
                <select id="role_array" class="column_filter" onchange="upload_filter_value()"><option value="">Select a Role</option>' . $roles_array . '</select>
                <input type="text" style="display:none"class="column_filter" id="col2_filter">
          </td>
          </tr>
          <tr>
          <td>&nbsp;&nbsp;&nbsp;</td>
          </tr>
          </tbody>
          </table></div>

</div>
<div class="col-right">
<input class="toggle-box" id="header1" type="checkbox" style="display:none;" >
<label for="header1" class="my-filter-col">Columns</label>
<div class="show-hide-div"><ul>
<li><input type="checkbox" data-column="0"  class="my-toggle" onclick="check_box_value(this);" checked>Sponsor Name</li>
<li><input type="checkbox" data-column="1"  class="my-toggle" onclick="check_box_value(this);" checked>Email</li>
<li><input type="checkbox" data-column="2"  class="my-toggle" onclick="check_box_value(this);" checked>Role</li>
<li><input type="checkbox" data-column="3"  class="my-toggle" onclick="check_box_value(this);" checked>Company</li>
' . $showhidefields . '</ul></div>

</div>
</div>

	 
<table id="example" class="display" cellspacing="0" width="100%">
        <thead>
            <tr style=" text-align: left; ">
                <th>Sponsor Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Company Name</th>
                ' . $head_html . '
              
            </tr>
        </thead>
 
        <tfoot>
            <tr>
                <th>Sponsor Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Company Name</th>
                 ' . $head_html . '
            </tr>
        </tfoot>
  
        <tbody>
                

            ' . $inner_html . '
             
        </tbody>
    </table>
<table>
<tbody>
<tr><td>Files Download</td></tr>
<tr><td>' . $file_upload_list . '</td><td><input type="submit" onclick="get_all_files()" value="Download"></td></tr>
</tbody>

</table>    
';

    echo $mydatatable;

    //download_files();
}
/*<div class="filter-left">
          <h1>Filter</h1>        
          <table class="display" style="margin-bottom: 48px;"cellspacing="0" >
          <tbody>
          <tr>
          <td>&nbsp;&nbsp;&nbsp;</td>
          </tr>
          <tr>
          <td>Task</td><td id="status-eg" style="display:none;">(Status:e.g Pending)</td>
          </tr>
          <tr id="my_filter_row">
          <td>' . $meta_keys . '</td>' . $meta_kesy_filter_list . '
          </tr>
          <tr>
          <td>&nbsp;&nbsp;&nbsp;</td>
          </tr>
          <tr>
          <td>Role</td>
          </tr>
          <tr>
          <td id="filter_col2" data-column="2">
                <select id="role_array" class="column_filter" onchange="upload_filter_value()"><option value="">Select a Role</option>' . $roles_array . '</select>
                <input type="text" style="display:none"class="column_filter" id="col2_filter">
          </td>
          </tr>
          <tr>
          <td>&nbsp;&nbsp;&nbsp;</td>
          </tr>
          </tbody>
          </table>
          </div>*/
?>