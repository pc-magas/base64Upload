<?php
require_once '../vendor/autoload.php';

use WindowsAzure\Common\ServicesBuilder;
use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;

error_log("Method:".$_SERVER['REQUEST_METHOD'],0);
if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: POST');
  header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
  error_log("Options Called",0);
  exit;
} else {

  error_log("Post Called",0);

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

    $url=[];
    foreach($blobs as $blob)
    {
      $urls[]=$blob->getUrl();
    }
    error_log("Urls:\n".implode(" , ",$urls),0);
    header("Content-type: application/json");
    $result=json_encode(['files'=>"sent",'url'=>$urls]);
    error_log("Result: ".$result,0);
    echo $result;
  } catch(ServiceException $e) {
    $code = $e->getCode();
    $error_message = $e->getMessage();

    header("Content-type: application/json");
    echo json_encode(['code'=>$code,'message'=>$error_message]);

  }

}
?>
