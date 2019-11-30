<?php
//if(isset($_POST["submit"])) {
//	if(isset($_FILES['fileToUpload'])){
    //$path = $_FILES['fileToUpload']['name'];
    //$ext = pathinfo($path, PATHINFO_EXTENSION);
        
		$file_name = $_FILES['fileToUpload']['name'];   
		$temp_file_location = $_FILES['fileToUpload']['tmp_name']; 
		require 'vendor/autoload.php';
		$s3 = new Aws\S3\S3Client([
			'region'  => 'eu-north-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "_id_",
				'secret' => "_secret_",
			]
		]);		
		$result = $s3->putObject([
			'Bucket' => 'storage-mencoder',
			'Key'    => $file_name,
			'SourceFile' => $temp_file_location			
		]);
		//var_dump($result);
        
        try {
        // Get the object.
        $answer = $s3->getObject([
            'Bucket' => 'storage-mencoder',
            'Key'    => $file_name
        ]);
        // Display the object in the browser.
        echo "worked";
        header("Content-Type: {$answer['ContentType']}");
        echo $answer['Body'];
        } catch (S3Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }   
//	}
?>
