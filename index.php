<?php

error_reporting(0);
$file = fopen("redirect_list.txt", "r") or exit("Unable to open file!");
$filecsv = fopen('redirect.csv', 'w');
$find = array("rewrite","^","$","permanent");
$replace = array("","","","");
//Output a line of the file until the end is reached
while(!feof($file))
{

  $url =  str_replace($find,$replace,fgets($file));
  $explode = explode(' ', $url);
  $source_url = $explode[1];
  $destination_url = $explode[2];
  if(!empty($source_url)) {
    if (parse_url($source_url, PHP_URL_QUERY)) {
      $urls = $source_url;
    } else {
      $urls = str_replace('?','',$source_url);
    }
  }

  if(!empty($destination_url)) {

    if (parse_url ($destination_url, PHP_URL_QUERY)) {
      $urld = $destination_url;
    }
    else {
      $urld = str_replace ('?', '', $destination_url);
    }
  }
  if(!empty($urls) || !empty($urld)) {
    //echo stripslashes($urls) . " - " . stripslashes($urld) . "<br />";
    fputcsv($filecsv, array(stripslashes($urls),stripslashes($urld)));
  }

  //echo $source_url ." - ". $destination_url . "<br />";



}
fclose($filecsv);
fclose($file);

?>