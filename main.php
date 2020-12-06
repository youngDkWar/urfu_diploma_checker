<?php
echo "jufri";
$zip = new ZipArchive();
if ($zip->open("test.docx")) {
    if (($index = $zip->locateName("word/document.xml")) !== false) {
        $content = $zip->getFromIndex($index);

    }
}