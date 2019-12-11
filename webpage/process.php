<?php
$unique = $_SERVER["UNIQUE_ID"];
if(isset($_POST["submit"])) {
	if(isset($_FILES['fileToUpload'])){   
    $newExt = $_POST["outputExt"];
		$file_name = $_FILES['fileToUpload']['name'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if($ext == "avi" || $ext == "mp4" || $ext == "mpg")
        {  
		$temp_file_location = $_FILES['fileToUpload']['tmp_name']; 
		require 'vendor/autoload.php';
		$s3 = new Aws\S3\S3Client([
			'region'  => 'eu-north-1',
			'version' => 'latest',
			'credentials' => [
				'key'    => "AKIATZUDUIJ6MWJJEROO",
				'secret' => "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",
			]
		]);		
		$result = $s3->putObject([
			'Bucket' => 'storage-mencoder',
			'Key'    => "unconverted/$unique.$ext",
			'SourceFile' => $temp_file_location			
		]);
        
        $command = escapeshellcmd("/home/ubuntu/rabbitMQ/env/bin/python3 /home/ubuntu/rabbitMQ/sender.py $unique.$ext $newExt");
        $output = shell_exec($command);
        echo $output;
        
        sleep(10);
        $i=0;
        While($i<61):
            $response = $s3->doesObjectExist('storage-mencoder',"converted/$unique.$newExt");
            if($response) 
            {
                try {
                // Get the object.
                    $returnName = pathinfo($file_name, PATHINFO_FILENAME);
                    $returnFile = "$returnName.$newExt";
                    $answer = $s3->getObject(array(
                        'Bucket' => 'storage-mencoder',
                        'Key'    => "converted/$unique.$newExt",
                        'SaveAs' => "/tmp/$returnFile"
                    ));
                    
                    
                    if(file_exists("/tmp/$returnFile")) {
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="'.basename("/tmp/$returnFile").'"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize("/tmp/$returnFile"));
                        flush(); // Flush system output buffer
                        readfile("/tmp/$returnFile");
                        
                        $s3->deleteObject([
                            'Bucket' => 'storage-mencoder',
                            'Key'    => "converted/$unique.$newExt"
                            ]);
                        
                    }
                    exit;
                } catch (S3Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }
            }
        sleep(1);
        $i = $i+1;
        endwhile;   
	}
    else
    {
        echo "wrong file type";
    }
    
    }
}
?>