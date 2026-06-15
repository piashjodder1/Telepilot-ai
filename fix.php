<?php

$resources = glob("app/Filament/Resources/*/*Resource.php");
foreach ($resources as $file) {
    $content = file_get_contents($file);
    
    // Replace ?string with string|BackedEnum|null which is usually what Filament expects. Wait, the error said UnitEnum.
    $content = preg_replace('/protected static \?string \$navigationGroup = \'(.*?)\';/s', 'protected static string|\UnitEnum|null $navigationGroup = \'$1\';', $content);
    $content = preg_replace('/protected static \?string \$navigationIcon = \'(.*?)\';/s', 'protected static string|\UnitEnum|null $navigationIcon = \'$1\';', $content);
    
    file_put_contents($file, $content);
    echo "Fixed type hints in $file\n";
}
