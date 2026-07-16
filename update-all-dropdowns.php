<?php

/**
 * Script untuk mengupdate semua dropdown di aplikasi
 * Menambahkan custom-select-wrapper ke semua element <select>
 */

$viewsPath = __DIR__ . '/resources/views';

// Fungsi untuk rekursif scan directory
function scanDirectory($dir) {
    $files = [];
    $items = scandir($dir);
    
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            $files = array_merge($files, scanDirectory($path));
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $files[] = $path;
        }
    }
    
    return $files;
}

// Fungsi untuk update file
function updateDropdowns($filePath) {
    $content = file_get_contents($filePath);
    $originalContent = $content;
    $lines = explode("\n", $content);
    $updated = false;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        // Cek jika ini baris select dengan form-select class
        if (preg_match('/<select\s+[^>]*class="[^"]*form-select[^"]*"[^>]*>/i', $line)) {
            // Cek jika sudah ada wrapper di baris sebelumnya
            $hasWrapper = false;
            if ($i > 0) {
                for ($j = $i - 1; $j >= max(0, $i - 3); $j--) {
                    if (strpos($lines[$j], 'custom-select-wrapper') !== false) {
                        $hasWrapper = true;
                        break;
                    }
                }
            }
            
            if (!$hasWrapper) {
                // Extract placeholder
                $placeholder = 'Pilih Opsi';
                
                // Cari option pertama untuk placeholder
                $selectContent = $line;
                $searchIndex = $i + 1;
                while ($searchIndex < count($lines) && strpos($lines[$searchIndex], '</select>') === false) {
                    $selectContent .= "\n" . $lines[$searchIndex];
                    $searchIndex++;
                }
                
                if (preg_match('/<option[^>]*value=["\']["\'][^>]*>([^<]+)<\/option>/i', $selectContent, $match)) {
                    $placeholder = trim($match[1]);
                }
                
                // Get indentation
                preg_match('/^(\s*)/', $line, $indentMatch);
                $indent = $indentMatch[1];
                
                // Insert wrapper before select
                $wrapperLine = $indent . '<div class="custom-select-wrapper" data-placeholder="' . htmlspecialchars($placeholder) . '">';
                array_splice($lines, $i, 0, [$wrapperLine]);
                $i++; // Skip the line we just inserted
                
                // Find closing </select> and add closing wrapper after it
                for ($j = $i; $j < count($lines); $j++) {
                    if (strpos($lines[$j], '</select>') !== false) {
                        $closingLine = $indent . '</div>';
                        array_splice($lines, $j + 1, 0, [$closingLine]);
                        $updated = true;
                        break;
                    }
                }
            }
        }
    }
    
    if ($updated) {
        $newContent = implode("\n", $lines);
        file_put_contents($filePath, $newContent);
        return true;
    }
    
    return false;
}

// Main execution
echo "🔍 Scanning for blade files...\n";
$files = scanDirectory($viewsPath);
echo "📁 Found " . count($files) . " PHP files\n\n";

$updatedCount = 0;
$processedFiles = [];

foreach ($files as $file) {
    $relativePath = str_replace($viewsPath . DIRECTORY_SEPARATOR, '', $file);
    $relativePath = str_replace('\\', '/', $relativePath);
    
    if (updateDropdowns($file)) {
        $updatedCount++;
        $processedFiles[] = $relativePath;
        echo "✅ Updated: $relativePath\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✨ Complete!\n";
echo "📊 Updated $updatedCount files\n";

if (count($processedFiles) > 0) {
    echo "\n📝 Updated files:\n";
    foreach ($processedFiles as $file) {
        echo "   - $file\n";
    }
}

echo "\n💡 Next steps:\n";
echo "   1. Review the changes with git diff\n";
echo "   2. Test the dropdowns in browser\n";
echo "   3. Commit the changes if everything works\n";