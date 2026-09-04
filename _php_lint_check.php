<?php

function lintPhpFile($filePath) {
    $command = "php -l " . escapeshellarg($filePath);
    $output = shell_exec($command);
    
    if (strpos($output, 'No syntax errors detected') !== false) {
        return ['status' => 'PASS', 'message' => ''];
    } else {
        return ['status' => 'FAIL', 'message' => $output];
    }
}

function findPhpFiles($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
    return $files;
}

$rootDir = 'E:\\SOFTWARE\\Xampp\\htdocs\\hungry-food';
$phpFiles = findPhpFiles($rootDir);

$results = [];
foreach ($phpFiles as $file) {
    $results[$file] = lintPhpFile($file);
}

header('Content-Type: application/json');
echo json_encode($results, JSON_PRETTY_PRINT);

?>