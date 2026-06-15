<?php

$resources = glob("app/Filament/Resources/*/*Resource.php");
foreach ($resources as $file) {
    $content = file_get_contents($file);
    
    $content = preg_replace('/protected static string\|\\\UnitEnum\|null/s', 'protected static string|\BackedEnum|null', $content);
    
    file_put_contents($file, $content);
    echo "Fixed type hints in $file\n";
}
