<?php

$resources = glob("app/Filament/Resources/*/*Resource.php");
foreach ($resources as $file) {
    $content = file_get_contents($file);
    
    // Fix navigationIcon
    $content = preg_replace('/protected static .*?\$navigationIcon =/s', 'protected static string|\BackedEnum|null $navigationIcon =', $content);
    
    // Fix navigationGroup
    $content = preg_replace('/protected static .*?\$navigationGroup =/s', 'protected static string|\UnitEnum|null $navigationGroup =', $content);
    
    file_put_contents($file, $content);
    echo "Fixed exact type hints in $file\n";
}
