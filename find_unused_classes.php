<?php

$directory = 'app';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$classes = [];

// Извлечение классов
foreach ($files as $file) {
    if ($file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        preg_match_all('/class\s+([a-zA-Z0-9_]+)/', $content, $matches);
        $classes = array_merge($classes, $matches[1]);
    }
}

print_r($classes);

$usedClasses = [];

// использование классов
foreach ($files as $file) {
    if ($file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        foreach ($classes as $class) {
            if (strpos($content, $class) !== false || preg_match('/use\s+namespace' . $class . '/', $content)) {
                $usedClasses[] = $class;
            }
        }
    }
}

$unusedClasses = array_diff($classes, array_unique($usedClasses));
print_r($unusedClasses); // Вывод неиспользуемых классов
