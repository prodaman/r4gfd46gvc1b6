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
    
    file_put_contents("merged_epg.xml", $finalXML);
    echo "EPG fusionado guardado en merged_epg.xml\n";
}

$urls = [
    "https://www.open-epg.com/files/spain1.xml.gz",
    "https://www.open-epg.com/files/spain2.xml.gz",
    "https://www.open-epg.com/files/spain3.xml.gz",
    "https://www.open-epg.com/files/spain4.xml.gz"
];

$tempFiles = [];
foreach ($urls as $url) {
    $tempFile = tempnam(sys_get_temp_dir(), "epg_") . ".gz";
    file_put_contents($tempFile, file_get_contents($url));
    $xmlFile = str_replace(".gz", "", $tempFile);
    $gz = gzopen($tempFile, "rb");
    $xmlContent = stream_get_contents($gz);
    gzclose($gz);
    file_put_contents($xmlFile, $xmlContent);
    $tempFiles[] = $xmlFile;
}

mergeEPG($tempFiles);

foreach ($tempFiles as $file) {
    unlink($file);
}

// modificar cosas en xml

$file_pointer = 'merged_epg.xml';

$open = file_get_contents($file_pointer);

$open = str_replace('.es</','</',$open);
$open = str_replace('>Hollywood<','>Canal Hollywood<',$open);


file_put_contents($file_pointer, $open);


?>

