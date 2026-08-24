<?php

/**
 * Limpia el EPG de guiATV:
 * - Descarga el XML.GZ original
 * - Lo descomprime
 * - Vacía el contenido de todas las etiquetas <desc>
 * - Mantiene títulos, horarios, canales y el resto del XML
 * - Vuelve a comprimirlo como guiatv_limpio.xml.gz
 */

$sourceUrl = 'https://raw.githubusercontent.com/davidmuma/EPG_dobleM/master/guiatv_sincolor.xml.gz';

$outputXml = 'guiatv_limpio.xml';
$outputGz  = 'guiatv_limpio.xml.gz';

echo "========================================\n";
echo "       LIMPIEZA EPG GUIATV\n";
echo "========================================\n\n";

echo "1. Descargando EPG original...\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 120,
        'user_agent' => 'GitHub-Actions-EPG-Cleaner'
    ]
]);

$data = file_get_contents($sourceUrl, false, $context);

if ($data === false || strlen($data) === 0) {
    exit("ERROR: No se pudo descargar el EPG original.\n");
}

echo "Descargado: " . strlen($data) . " bytes\n\n";


/*
 * Descomprimir GZ
 */
echo "2. Descomprimiendo...\n";

$xmlContent = gzdecode($data);

if ($xmlContent === false) {
    exit("ERROR: El archivo GZ no se pudo descomprimir.\n");
}

echo "XML descomprimido correctamente.\n\n";


/*
 * Vaciar todas las etiquetas <desc>.
 *
 * Ejemplo:
 *
 * <desc>Descripción del programa</desc>
 *
 * pasa a:
 *
 * <desc></desc>
 *
 * También funciona con:
 *
 * <desc atributo="valor">texto</desc>
 */
echo "3. Eliminando descripciones...\n";

$xmlClean = preg_replace(
    '/<desc(\s[^>]*)?>.*?<\/desc\s*>/is',
    '<desc></desc>',
    $xmlContent
);

if ($xmlClean === null) {
    exit("ERROR: No se pudieron procesar las etiquetas <desc>.\n");
}

$xmlContent = $xmlClean;

echo "Descripciones eliminadas.\n\n";


/*
 * Guardar XML limpio
 */
echo "4. Guardando XML limpio...\n";

if (file_put_contents($outputXml, $xmlContent) === false) {
    exit("ERROR: No se pudo guardar $outputXml\n");
}

echo "$outputXml creado correctamente.\n\n";


/*
 * Comprimir con máxima compresión
 */
echo "5. Comprimiendo XML...\n";

$compressed = gzencode($xmlContent, 9);

if ($compressed === false) {
    exit("ERROR: No se pudo comprimir el XML.\n");
}

if (file_put_contents($outputGz, $compressed) === false) {
    exit("ERROR: No se pudo guardar $outputGz\n");
}

echo "$outputGz creado correctamente.\n\n";


/*
 * Mostrar tamaños
 */
$xmlSize = filesize($outputXml);
$gzSize  = filesize($outputGz);

echo "========================================\n";
echo "          PROCESO COMPLETADO\n";
echo "========================================\n";
echo "XML : $xmlSize bytes\n";
echo "GZ  : $gzSize bytes\n";
echo "========================================\n";


/*
 * El XML sin comprimir es solamente temporal.
 *
 * No queremos subirlo al repositorio.
 */
if (file_exists($outputXml)) {
    unlink($outputXml);
}

echo "\nArchivo final: $outputGz\n";
