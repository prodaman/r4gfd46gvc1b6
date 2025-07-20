<?php

$repoPath = __DIR__; // Directorio del repositorio local
$search = "covertv.lol";
$replace = "1434584545.xyz";

// Buscar archivos .m3u en el repositorio
$m3uFiles = glob("$repoPath/*.m3u");

if (empty($m3uFiles)) {
    die("No se encontraron archivos .m3u en el repositorio.\n");
}

foreach ($m3uFiles as $file) {
    $content = file_get_contents($file);
    if ($content === false) {
        echo "Error al leer el archivo: $file\n";
        continue;
    }

    $newContent = str_replace($search, $replace, $content);

    if ($content !== $newContent) {
        if (file_put_contents($file, $newContent) === false) {
            echo "Error al escribir en el archivo: $file\n";
        } else {
            echo "Modificado: $file\n";
        }
    }
}

// Comandos Git para subir los cambios
exec("git add .", $output, $returnVar);
if ($returnVar !== 0) {
    die("Error en 'git add'. Verifica permisos y configuración.\n");
}

exec("git commit -m 'Actualización de archivos .m3u' 2>&1", $output, $returnVar);
if ($returnVar !== 0) {
    echo "No hay cambios para hacer commit o hubo un error.\n";
} else {
    echo implode("\n", $output) . "\n";
}

exec("git push origin main 2>&1", $output, $returnVar);
if ($returnVar !== 0) {
    die("Error al hacer push. Verifica autenticación y permisos en GitHub.\n");
}

echo "Actualización completada con éxito.\n";

?>
