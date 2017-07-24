<?php

function get_redirect_url ($url) {
  $redirect_url = NULL;

  $url_parts = @parse_url($url);
  if (!$url_parts) {
    return FALSE;
  }
  if (!isset($url_parts['host'])) {
    return FALSE;
  } //can't process relative URLs
  if (!isset($url_parts['path'])) {
    $url_parts['path'] = '/';
  }

  $sock = fsockopen ($url_parts['host'], (isset($url_parts['port']) ? (int) $url_parts['port'] : 80), $errno, $errstr, 30);
  if (!$sock) {
    return FALSE;
  }

  $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?' . $url_parts['query'] : '') . " HTTP/1.1\r\n";
  $request .= 'Host: ' . $url_parts['host'] . "\r\n";
  $request .= "Connection: Close\r\n\r\n";
  fwrite ($sock, $request);
  $response = '';
  while (!feof ($sock)) {
    $response .= fread ($sock, 8192);
  }
  fclose ($sock);

  if (preg_match ('/^Location: (.+?)$/m', $response, $matches)) {
    if (substr ($matches[1], 0, 1) == "/") {
      return $url_parts['scheme'] . "://" . $url_parts['host'] . trim ($matches[1]);
    }
    else {
      return trim ($matches[1]);
    }

  }
  else {
    return FALSE;
  }

}

/**
 * get_all_redirects()
 * Follows and collects all redirects, in order, for the given URL.
 *
 * @param string $url
 *
 * @return array
 */
function get_all_redirects ($url) {
  $redirects = array ();
  while ($newurl = get_redirect_url ($url)) {
    if (in_array ($newurl, $redirects)) {
      break;
    }
    $redirects[] = $newurl;
    $url = $newurl;
  }
  return $redirects;
}

/**
 * get_final_url()
 * Gets the address that the URL ultimately leads to.
 * Returns $url itself if it isn't a redirect.
 *
 * @param string $url
 *
 * @return string
 */
function get_final_url ($url) {
  $redirects = get_all_redirects ($url);
  if (count ($redirects) > 0) {
    return array_pop ($redirects);
  }
  else {
    return $url;
  }
}

$filecsv = fopen('redirects.csv', 'w');
$base_url = "http://blogmulesoft.debugme.in";
$f_pointer=fopen("redirects_list.csv","r");
while(! feof($f_pointer)) {
  $ar = fgetcsv ($f_pointer);
  $url = $ar[0];
  $final_url = $base_url.$url;
  $get_url = get_final_url($final_url);
  $destination_url = str_replace($base_url,'',$get_url);
  fputcsv($filecsv, array($url,$destination_url));
}
fclose($filecsv);
