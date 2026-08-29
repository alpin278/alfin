<?php
echo "DIR: " . __DIR__ . "<br>";
echo "vendor exists: " . (file_exists(__DIR__.'/vendor/autoload.php') ? 'YES' : 'NO') . "<br>";
echo "bootstrap exists: " . (file_exists(__DIR__.'/bootstrap/app.php') ? 'YES' : 'NO') . "<br>";
echo "env exists: " . (file_exists(__DIR__.'/.env') ? 'YES' : 'NO') . "<br>";