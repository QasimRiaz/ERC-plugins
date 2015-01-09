jQuery.noConflict();


jQuery(document).ready(function() {
var getUrl = window.location;
var baseUrl = getUrl .protocol + "//" + getUrl.host + "/";

    jQuery('#example').dataTable({
         dom: 'T<"clear">lfrtip',
         tableTools: {
            "sSwfPath": baseUrl+"wp-content/plugins/user-meta-fields-reports/js/copy_csv_xls_pdf.swf"
        }
        ,"iDisplayLength": 50});


   
    
    // export only what is visible right now (filters & paginationapplied)
    jQuery('#ToolTables_example_1').click(function(event) {
        event.preventDefault();
        table2csv(oTable, 'visible', 'table.display');
    });
    
    jQuery('#example_filter').hide();
    jQuery('input.global_filter').on('keyup click', function() {
        filterGlobal();
    });

    jQuery('input.column_filter').on('keyup click', function() {
        filterColumn(jQuery(this).parents('td').attr('data-column'));
    });
    jQuery('select.column_filter').on('change', function() {
        filterColumn(jQuery(this).parents('td').attr('data-column'));
    });
    var table = jQuery('#example').DataTable();


    jQuery.each(jQuery('.my-toggle'), function(index, value) {

        var column = table.column(jQuery(this).attr('data-column'));
        if (jQuery(this).is(':checked')) {

        } else {
            column.visible(!column.visible());          // do stuff....
        }
    });
    

    
   
});

function filterGlobal() {
    jQuery('#example').DataTable().search(
            jQuery('#global_filter').val(),
            jQuery('#global_regex').prop('checked'),
            jQuery('#global_smart').prop('checked')
            ).draw();
}

function filterColumn(i) {
    jQuery('#example').DataTable().column(i).search(
            jQuery('#col' + i + '_filter').val(),
            jQuery('#col' + i + '_regex').prop('checked'),
            jQuery('#col' + i + '_smart').prop('checked')
            ).draw();
}
function add_filter_input(elem) {
var id = jQuery(elem).attr("id");

    if(jQuery("#" + id+" option:selected").val() == ""){
      
      jQuery("#status-eg").hide();
      jQuery(".meta_filter_box").hide();
    
    }else{
     
    jQuery(".meta_filter_box").hide();
    jQuery("#status-eg").hide();
    jQuery(".column_filter").val('');
    var met_col_id = jQuery("#" + id).children(":selected").attr("id");
    jQuery("#status-eg").show();
    jQuery("#filter_col" + met_col_id).show();
    }
}
function check_box_value(current) {


    var table = jQuery('#example').DataTable();
    // current.preventDefault();
    var column = table.column(jQuery(current).attr('data-column'));
    if (jQuery(current).is(':checked')) {
        // do stuff....
        column.visible(!column.visible());

    } else {
        column.visible(!column.visible());          // do stuff....
    }
}


function upload_filter_value() {
    var thi_vall = jQuery("#role_array option:selected").val();
    jQuery("#col2_filter").val(thi_vall);
}

function get_all_files(){
    
    var colvalue = jQuery("#file_upload option:selected").val();
    if(colvalue != ""){
    
    var ids=[];
    var getUrl = window.location;
    var baseUrl = getUrl .protocol + "//" + getUrl.host + "/";
    jQuery('.rowselect').each(function () {
        ids.push(this.id);
    });
       //console.log(ids);
    var jaxrel = fromphp.jaxfile;
    var valset = {
        action: 'give_create_zip',
        colVal: colvalue,
        ids:ids
    };

    jQuery.post(jaxrel, valset, function(dat, status) {
        var url=baseUrl+"wp-content/plugins/user-meta-fields-reports/download-lib.php?data=";
        //console.log(dat);
        window.location.replace(url+dat);
        //window.open(url+dat, '_blank');
    });
    
   
 }
    
//jQuery('#filesurl').val();
    
    //var table = jQuery('#example').DataTable();
    //var mydata = [];
    //table.column(colvalue).data().each(function(value, index) {
     // if(value != ""){ mydata.push(value);}
    //});
    //jQuery('#filesurl').val(mydata);
    //console.log(mydata);

}