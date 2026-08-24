<?php

/**
 * Descarga guiATV sincolor.xml.gz, elimina el contenido de todas
 * las etiquetas <desc> y vuelve a comprimir el XML.
 */

$sourceUrl = 'https://raw.githubusercontent.com/davidmuma/EPG_dobleM/master/guiatv_sincolor.xml.gz';

$inputGz  = 'guiatv_sincolor.xml.gz';
$outputXml = 'guiatv_limpio.xml';
$outputGz  = 'guiatv_limpio.xml.gz';

echo "Descargando EPG original...\n";

$data = file_get_contents($sourceUrl);

if ($data === false || strlen($data) === 0) {
    exit("ERROR: No se pudo descargar el archivo original.\n");
}

if (file_put_contents($inputGz, $data) === false) {
    exit("ERROR: No se pudo guardar el archivo descargado.\n");
}

echo "Archivo descargado: " . strlen($data) . " bytes\n";

/*
 * Descomprimir
 */
echo "Descomprimiendo...\n";

$xmlContent = gzdecode($data);

if ($xmlContent === false) {
    exit("ERROR: No se pudo descomprimir el archivo GZ.\n");
}

/*
 * Eliminar únicamente el contenido de <desc>.
 *
 * Se conserva la etiqueta:
 *
 * <desc></desc>
 *
 * También funciona si <desc> tiene atributos.
 */
$xmlContent = preg_replace(
    '/<desc(\s[^>]*)?>.*?<\/desc>/is',
    '<desc></desc>',
    $xmlContent
);

if ($xmlContent === null) {
    exit("ERROR: Falló el procesamiento de las etiquetas <desc>.\n");
}

/*
 * Guardar XML limpio
 */
echo "Guardando XML limpio...\n";

if (file_put_contents($outputXml, $xmlContent) === false) {
    exit("ERROR: No se pudo guardar $outputXml\n");
}

/*
 * Comprimir con máxima compresión.
 */
echo "Comprimiendo...\n";

$compressed = gzencode($xmlContent, 9);

if ($compressed === false) {
    exit("ERROR: No se pudo comprimir el XML.\n");
}

if (file_put_contents($outputGz, $compressed) === false) {
    exit("ERROR: No se pudo guardar $outputGz\n");
}

echo "\n";
echo "========================================\n";
echo " EPG LIMPIO GENERADO CORRECTAMENTE\n";
echo "========================================\n";
echo "XML : $outputXml\n";
echo "GZ  : $outputGz\n";
echo "Tamaño XML : " . filesize($outputXml) . " bytes\n";
echo "Tamaño GZ  : " . filesize($outputGz) . " bytes\n";
echo "========================================\n";

/*
 * El archivo original descargado solamente se utiliza
 * durante el proceso y no se conserva en el repositorio.
 */
if (file_exists($inputGz)) {
    unlink($inputGz);
}

?>
