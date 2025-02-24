<?php
function mergeEPG($files) {
    $mergedChannels = [];
    $mergedProgrammes = [];
    
    foreach ($files as $file) {
        $xmlContent = file_get_contents($file);
        if ($xmlContent === false) {
            echo "Error al leer: $file\n";
            continue;
        }
        
        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            echo "Error al parsear: $file\n";
            continue;
        }
        
        foreach ($xml->channel as $channel) {
            $id = (string) $channel["id"];
            if (!isset($mergedChannels[$id])) {
                $mergedChannels[$id] = $channel->asXML();
            }
        }
        
        foreach ($xml->programme as $programme) {
            $start = (string) $programme["start"];
            $mergedProgrammes[$start][] = $programme->asXML();
        }
    }
    
    ksort($mergedProgrammes);
    
    $finalXML = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<tv>\n";
    $finalXML .= implode("\n", $mergedChannels) . "\n";
    foreach ($mergedProgrammes as $group) {
        $finalXML .= implode("\n", $group) . "\n";
    }
    $finalXML .= "</tv>\n";

    $finalXML = str_replace('.es</','</',$finalXML);
    $finalXML = str_replace('>Hollywood<','>Canal Hollywood<',$finalXML);
    
    // Guardar el archivo fusionado como .xml
    file_put_contents("merged_epg.xml", $finalXML);

    // Comprimir el archivo a .gz
    $gz = gzopen('merged_epg.xml.gz', 'wb9');
    gzwrite($gz, $finalXML);
    gzclose($gz);

    echo "EPG fusionado y comprimido guardado como merged_epg.xml.gz\n";
}

$urls = [
    "https://www.open-epg.com/files/spain1.xml",
    "https://www.open-epg.com/files/spain2.xml",
    "https://www.open-epg.com/files/spain3.xml",
    "https://www.open-epg.com/files/spain4.xml"
];

$tempFiles = [];
foreach ($urls as $url) {
    $tempFile = tempnam(sys_get_temp_dir(), "epg_") . ".xml";
    file_put_contents($tempFile, file_get_contents($url));
    $tempFiles[] = $tempFile;
}

mergeEPG($tempFiles);

foreach ($tempFiles as $file) {
    unlink($file);
}
?>
