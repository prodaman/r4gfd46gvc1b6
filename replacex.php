<?php

$repoPath = __DIR__; // Directorio del repositorio local
$search = "https://raw.githubusercontent.com/prodaman/r4gfd46gvc1b6/refs/heads/main/merged_epg.xml.gz";
$replace = "https://raw.githubusercontent.com/davidmuma/EPG_dobleM/master/guiatv_sincolor.xml.gz";

// Buscar archivos .m3u en el repositorio
$m3uFiles = glob("$repoPath/*.m3u");

foreach ($m3uFiles as $file) {
    $content = file_get_contents($file);
    $newContent = str_replace($search, $replace, $content);
    
    if ($content !== $newContent) {
        file_put_contents($file, $newContent);
        echo "Modificado: $file\n";
    }
}

// Comandos Git para subir los cambios
echo shell_exec("git add .");
echo shell_exec("git commit -m 'Actualización de archivos .m3u' 2>&1");
echo shell_exec("git push origin main 2>&1");

?>
