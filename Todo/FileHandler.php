<?php

namespace App\APIService\Library\FileHandler;

class FileHandler
{

    public function splitFileIntoChunks( $filePath, $buffer, $chunkPath = null, $chunkExtension = '.part-' )
    {
        $directory = trim( dirname( $filePath ) );
        $fileName = trim( basename( $filePath ) );

        $fileHandle = fopen( $filePath, 'r' );
        $fileSize = filesize( $filePath );
        $chunkTotalAmount = ceil( $fileSize / $buffer );
        $chunks = array();

        for ( $i = 0; $i < $chunkTotalAmount; $i++ ) {
            $fileChunk = fread( $fileHandle, $buffer );

            if ( $chunkPath === null ) {
                $fileChunkPath = $directory . '/' . $fileName . $chunkExtension . $i;
            } else {
                $fileChunkPath = $chunkPath . $fileName . $chunkExtension . $i;
            }

            $newChunk = fopen( $fileChunkPath, 'w+' );
            fwrite( $newChunk, $fileChunk );
            array_push( $chunks, $fileChunkPath );
            fclose( $newChunk );
        }
        fclose( $fileHandle );

        return $chunks;
    }

    public function cleanChunks( $baseFileName, $chunkPath, $chunkAmount, $chunkExtension = '.part-' )
    {
        for ( $i = 0; $i < $chunkAmount; $i++ ) {
            $chunkFile = $chunkPath . $baseFileName . $chunkExtension . $i;
            chmod( $chunkFile, 0644 );
            if ( unlink( $chunkFile ) === false ) {
                exit( 'Unable to delete the file' );
            }
        }
        return true;
    }

    public function mergeFile( $baseFileName, $chunkPath, $chunkAmount, $mergedFilePath, $chunkExtension = '.part-' )
    {
        $content = '';

        for ( $i = 0; $i < $chunkAmount; $i++ ) {
            $chunkFile = $chunkPath . $baseFileName . $chunkExtension . $i;
            $fileSize = filesize( $chunkFile );
            $handle = fopen( $chunkFile, 'rb' ) or die( "error opening file" );
            $content .= fread( $handle, $fileSize ) or die( "error reading file" );
        }
        $handle = fopen( $mergedFilePath . $baseFileName, 'wb' ) or die( "error creating/opening merged file" );
        fwrite( $handle, $content ) or die( "error writing to merged file" );

        return true;
    }

    public function doesFileExists( $fileName, $filePath )
    {
        if ( $filePath[strlen( $filePath ) - 1] != '/' ) {
            $filePath .= '/';
        }
        if ( is_file( $filePath . $fileName ) === false ) {
            return false;
        }
        if ( is_dir( $filePath ) === false ) {
            return false;
        }

        return file_exists( $filePath . $fileName );
    }
}
