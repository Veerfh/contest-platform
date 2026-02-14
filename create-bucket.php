<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

use Aws\S3\S3Client;

try {
    $client = new S3Client([
        'version' => 'latest',
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'endpoint' => env('AWS_ENDPOINT', 'http://localhost:9000'),
        'use_path_style_endpoint' => true,
        'credentials' => [
            'key' => env('AWS_ACCESS_KEY_ID', 'minioadmin'),
            'secret' => env('AWS_SECRET_ACCESS_KEY', 'minioadmin'),
        ],
    ]);
    
    // Проверяем, существует ли уже bucket
    $buckets = $client->listBuckets();
    $bucketExists = false;
    
    foreach ($buckets['Buckets'] as $bucket) {
        if ($bucket['Name'] === env('AWS_BUCKET', 'contests')) {
            $bucketExists = true;
            break;
        }
    }
    
    if (!$bucketExists) {
        // Создаем bucket
        $result = $client->createBucket([
            'Bucket' => env('AWS_BUCKET', 'contests'),
        ]);
        
        echo "✅ Bucket '" . env('AWS_BUCKET') . "' created successfully!\n";
        
        // Устанавливаем политику публичного доступа (опционально)
        try {
            $client->putBucketPolicy([
                'Bucket' => env('AWS_BUCKET'),
                'Policy' => json_encode([
                    'Version' => '2012-10-17',
                    'Statement' => [
                        [
                            'Effect' => 'Allow',
                            'Principal' => '*',
                            'Action' => ['s3:GetObject'],
                            'Resource' => ['arn:aws:s3:::' . env('AWS_BUCKET') . '/*'],
                        ],
                    ],
                ]),
            ]);
            echo "✅ Bucket policy set successfully!\n";
        } catch (Exception $e) {
            echo "⚠️ Could not set bucket policy: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✅ Bucket '" . env('AWS_BUCKET') . "' already exists.\n";
    }
    
    // Проверяем, что bucket доступен
    $result = $client->headBucket(['Bucket' => env('AWS_BUCKET')]);
    echo "✅ Bucket is accessible!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}