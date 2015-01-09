<?php 




 

//$files_urls_array = $_POST['filesurl'][0];
//$files = explode(',', $files_to_zip);

if(!empty($_GET['data'])){
    
$file_name = str_replace(array( '[', ']' ), '', $_GET['data']);
$file_name = str_replace('null', '', $file_name);
$file_name = str_replace('"', '', $file_name);
$files = explode(',', $file_name);
//echo '<pre>';Imagine_2015_Sponsor_task_lists.docx
//print_r($files);

//foreach($files as $file){
 
  //  echo $file = str_replace('"', '', $file).'<br>';
//}
//exit;

   
# create new zip opbject
$zip = new ZipArchive();
# create a temp file & open it
$tmp_file = tempnam('.','');
$zip->open($tmp_file, ZipArchive::CREATE);
# loop through each file
foreach($files as $file){
    # download file
   
    $filee = str_replace('\/', '/', $file);
    $download_file = file_get_contents($filee);
    #add it to the zip
    $zip->addFromString(basename($filee),$download_file);
}
# close zip
$zip->close();
# send the file to the browser as a download
header('Content-disposition: attachment; filename=data.zip');
header('Content-type: application/zip');


readfile($tmp_file);
}