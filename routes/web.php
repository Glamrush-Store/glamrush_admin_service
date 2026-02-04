<?php

use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/test-gcs', function () {
    try {
        // Test 1: Check if config is loaded
        $config = config('filesystems.disks.gcs');
        dump('Config loaded:', $config);

        // Test 2: Check if key file exists
        $keyFilePath = env('GOOGLE_APPLICATION_CREDENTIALS');
        var_dump($keyFilePath);
        dump('Key file exists:', file_exists($keyFilePath));

        // Test 3: Check if key file is readable
        if (file_exists($keyFilePath)) {
            $keyContent = json_decode(file_get_contents($keyFilePath), true);
            dump('Key file valid JSON:', !is_null($keyContent));
            dump('Project ID in key file:', $keyContent['project_id'] ?? 'NOT FOUND');
        }

        // Test 4: Try to connect directly
        $storageClient = new StorageClient([
            'projectId' => env('GCP_PROJECT_ID'),
            'keyFilePath' => $keyFilePath,
        ]);

        $bucketName = env('GCP_BUCKET');
        dump('Bucket name:', $bucketName);

        // Test 5: Check if bucket exists and is accessible
        $bucket = $storageClient->bucket($bucketName);
        dump('Bucket exists:', $bucket->exists());

        if ($bucket->exists()) {
            // Test 6: Try to list objects (limit to 1)
            $objects = $bucket->objects(['maxResults' => 1]);
            dump('Can list objects:', true);

            // Test 7: Try using Laravel Storage
            $t = \Illuminate\Support\Facades\Storage::disk('gcs')->put('test.txt', 'Hello World');
            dump('File uploaded successfully!>>>>>>>>>>' . $t);

            // Test 8: Check if file exists
            $exists = \Illuminate\Support\Facades\Storage::disk('gcs')->exists('test.txt');
            dump('File exists check:', $exists);

            // Test 9: Get the URL
            $url = $url = "https://storage.googleapis.com/" . env('GCP_BUCKET') . "/" . 'test.txt';
            dump('File URL:', $url);

            return response()->json([
                'status' => 'success',
                'message' => 'GCS is working correctly!',
                'url' => $url
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Bucket does not exist or is not accessible'
        ], 400);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});
