<?php

if(!isset($argv[1])){
  echo "FIle nor provided\n";
  exit(-1);
}

$name=$argv[1];
$data=file_get_contents($name);

$data=[
  'data'=>base64_encode($data),
  'name'=>basename($name)
];

$data=json_encode($data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"http://localhost/base64Upload/");
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data))
);

$result = curl_exec($ch);
$response_complete=curl_getinfo($ch);
var_dump($response_complete);
echo "\n";
echo ($result);
echo "\n";
?>
