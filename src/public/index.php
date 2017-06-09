<?php
require_once '../vendor/autoload.php';

use WindowsAzure\Common\ServicesBuilder;
use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;


function create_storage_connection()
{
    return "DefaultEndpointsProtocol=https;AccountName=".getenv('AZURE_ACCOUNT').";AccountKey=".getenv('AZURE_KEY');
}
$connectionString=create_storage_connection();
$blobRestProxy= ServicesBuilder::getInstance()->createBlobService($connectionString);
$container_name=getenv('AZURE_CONTAINER');

$data=file_get_contents('php://input');
$data=json_decode($data,true);
try{
  //Upload data
  $file_data=base64_decode($data['data']);
  $data['name']=uniqid().$data['name'];
  $blobRestProxy->createBlockBlob($container_name,$data['name'],$file_data);
  $blob = $blobRestProxy->getBlob($container_name, $data['name']);

  //Download url info
  $listBlobsOptions = new ListBlobsOptions();
  $listBlobsOptions->setPrefix($data['name']);
  $blob_list = $blobRestProxy->listBlobs($container_name, $listBlobsOptions);
  $blobs = $blob_list->getBlobs();

  foreach($blobs as $blob)
  {
    echo $blob->getUrl()."<br />";
  }

} catch(ServiceException $e) {
  $code = $e->getCode();
  $error_message = $e->getMessage();
  echo $code.": ".$error_message."<br />";
}

?>
