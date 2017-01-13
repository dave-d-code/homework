<?php

namespace App\APIService\Modules\Storage\v1\Models\Uploads;

use App\APIService\Library\FileHandler\FileHandler;
use App\APIService\Library\FolderHandler\FolderHandler;
use App\APIService\Library\EncryptionHelper\EncryptionHelper;
use App\APIService\Library\Validator\Validator;

class UploadsModel
{

    private $app;
    private $db;
    private $uploadDir;
    private $fileHandler;
    private $folderHandler;
    private $user;
    private $chunkPath;
    private $metadata;
    private $file;
    private $response = array();
    private $requiredMetadataFields = array(
        'chunk_number',
        'total_chunks',
        'chunk_hash',
        'last_chunk',
        'file_size',
        'file_name',
        'file_hash',
        'file_type'
    );

    /**
     * Assign the all necessary variables in the class
     * 
     * @param array $data Array holding all POST variables passed from the Controller
     */
    public function __construct()
    {
        $this->app = \Yee\Yee::getInstance();
        $this->db = $this->app->db['cassandra'];
        $this->uploadDir = $this->app->config( 'upload.path' );
        $this->response['success'] = true;

        $this->fileHandler = new FileHandler();
        $this->folderHandler = new FolderHandler();

        //USER MUST BE PICKED FROM ELSEWHERE
        $this->user = "FS";
    }

    public function upload()
    {
        $this->getPostData();
        $this->postDataValidation();
        if ( $this->response['success'] === false ) {
            return;
        }

        //Check if the file already exists in the db
        if ( $this->checkFileExistsInServer() === true ) {
            $this->response['success'] = false;
            $this->response['message'][] = 'File already exists';
            return;
        }

        $this->moveFileToTempFolder();
        if ( $this->response['success'] === false ) {
            return;
        }

        $this->validateChunkHash();
        if ( $this->response['success'] === false ) {
            return;
        }

        if ( $this->metadata->last_chunk === false ) {
            $this->response['message'][] = 'Chunk ' . $this->metadata->chunk_number . '/' . $this->metadata->total_chunks .
                    ' has been successfully uploaded';
            return;
        }

        //Check if all chunks are present in the temp folder in the server
        $this->areAllChunksPresent();
        if ( $this->response['success'] === false ) {
            return;
        }

        //Check if the hash_sum of the file is equal to the hash_sum in the metadata
        /*$this->validateFileHash();
        if ( $this->response['success'] === false ) {
            return;
        }*/

        //Upload the metadata of the file in the db
        $this->uploadFileInformation();
        //$this->deleteChunks();

        //Generate a new token for the user
        //Return successful upload message + new token
		
		//Upload to Amazon WS S3
		$this->uploadToS3();
		
    }

    public function respond()
    {
        return json_encode( $this->response );
    }

    public function getPostData()
    {
        $this->metadata = json_decode( $this->app->request->post( 'metadata' ) );
        foreach ( $this->metadata as $field => $value ) {
            $this->metadata->$field = is_string( $value ) ? trim( $value ) : $value;
        }
        $this->file = $_FILES;
    }

    public function postDataValidation()
    {
        $isMetadataEmpty = empty( $this->metadata );
        $isFileEmpty = empty( $this->file );

        if ( $isMetadataEmpty ) {
            $this->response['success'] = false;
            $this->response['message'][] = "Field: 'metadata' is empty";
        }
        if ( $isFileEmpty ) {
            $this->response['success'] = false;
            $this->response['message'][] = "No file has been uploaded";
        }

        if ( $isMetadataEmpty === false && $isFileEmpty === false ) {

            $metadataFields = array_keys( (array) $this->metadata );
            $missingFields = array_diff( $this->requiredMetadataFields, $metadataFields );

            if ( count( $missingFields ) !== 0 ) {
                $this->response['success'] = false;
                $this->response['message'][] = 'The following fields from metadata is/are missing: ' . implode( ', ', $missingFields );
            } else {
                foreach ( $metadataFields as $metaField ) {
                    if ( Validator::areVariablesEmpty( $this->metadata->$metaField ) ) {
                        $this->response['success'] = false;
                        $this->response['message'][] = 'Field: ' . $metaField . ' is empty';
                    }
                }
            }
        }
    }

    public function moveFileToTempFolder()
    {
        $this->folderHandler->createFolder( $this->user, $this->uploadDir );

        $this->chunkPath = $this->uploadDir . $this->user . '\\' . $this->file['file']['name'];

        if ( move_uploaded_file( $this->file['file']['tmp_name'], $this->chunkPath ) === false ) {
            $this->response['success'] = false;
            $this->response['message'][] = "Chunk upload has failed";
        }
    }

    public function validateChunkHash()
    {
        $chunkHash = EncryptionHelper::hashFile( $this->chunkPath );
        if ( $chunkHash !== $this->metadata->chunk_hash ) {
            $this->response['success'] = false;
            $this->response['message'][] = 'The chunk file: ' . $this->file['file']['name'] . ' has been modified';
        }
    }

    public function checksValidation()
    {
        $userPath = $this->checkDir();
        $response = 'yes';

        if ( !$this->checkFileExistsInDB() && $this->checkFileExistsInServer( $userPath ) ) {
            return true;
        }
        return false;
    }

    public function checkFileExistsInDB()
    {
        return $this->db->where( 'owner', $this->owner )->where( 'checksum', $this->checksum )->where( 'filename', $this->filesname )->get( 'uploaded_files_info', null, 'checksum' );
    }

    public function checkFileExistsInServer()
    {
        return $this->fileHandler->doesFileExists( $this->metadata->file_name, $this->uploadDir . $this->user );
    }

    public function areAllChunksPresent()
    {
        $chunkPathDir = $this->uploadDir . $this->user;
        for ( $i = 0; $i < $this->metadata->total_chunks; $i++ ) {
            if ( $this->fileHandler->doesFileExists( $this->metadata->file_name . '.part-' . $i, $chunkPathDir ) === false ) {
                $this->response['success'] = false;
                $this->response['message'][] = 'Chunk ' . $i . ' is missing';
            }
        }
    }

    public function uploadFileInformation()
    {
        $data = array(
            'owner' => $this->user,
            'filename' => $this->metadata->file_name,
            'checksum' => $this->metadata->file_hash,
            'filetype' => $this->metadata->file_type,
            'filesize' => $this->metadata->file_size,
            'location' => $this->uploadDir . $this->user,
            'date_modified' => date( "Y-m-d H:i:s" ),
            'date_uploaded' => date( "Y-m-d H:i:s" ),
        );

        return $this->db->insert( 'uploaded_files_info', $data );
    }

    public function validateFileHash()
    {
        $fileHash = EncryptionHelper::hashFile( $this->uploadDir . $this->user . '/' . $this->metadata->file_name );

        if ( $this->metadata->file_hash != $fileHash ) {
            $this->response['success'] = false;
            $this->response['message'][] = 'The file has been modified';
        }
    }

    public function deleteChunks()
    {
        $this->fileHandler->cleanChunks( $this->metadata->file_name, $this->uploadDir . $this->user . '/chunks/', $this->metadata->total_chunks );
    }
	
	public function uploadToS3(){
		$s3 = $this->app->container->s3Client;
		try {
			$s3->putObject([
			'Bucket' => 'motherfuckingbucket',
			'Key'    => 'UserBenchMark.exe.part-2',
			'Body'   => fopen('C:\wamp64\www\api-storage.local\Uploaded\FS\UserBenchMark.exe.part-2', 'r'),
			'ACL'    => 'public-read',
			]);
		} catch (Aws\Exception\S3Exception $e) {
			echo "There was an error uploading the file.\n";
		}
	}
}
