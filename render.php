<?php
if ($_FILES && $_FILES['f']['error']== UPLOAD_ERR_OK){
    $name = $_FILES['f']['name'];
    move_uploaded_file($_FILES['f']['tmp_name'], $name);
}
$filename = $_FILES['f']['name'];
$zip = new ZipArchive();

class Tag
{
    public $parameters = array();
    public $tagType = "";
    public $closeBracketType = "";
    public function __construct($tag)
    {
        $this->parameters = array();
        if (substr($tag,0 , 4) == "w:t>"){
            $this->tagType = "w:t>";
            $this->parameters["text"] = substr($tag, 4);
            return;
        }
        $explodedTag = explode(" ", $tag, 2);
        $this->tagType = $explodedTag[0];
        $tag = $explodedTag[1];
        while ($tag != "/>" and $tag != ">" and $tag != "?>" and $tag != "" and $tag != "/>\""){
            $tag = explode("=", $tag, 2);
            $parameterName = $tag[0];
            $tag = $tag[1];
            $tag = explode('"', $tag, 3);
            $parameterValue = $tag[1];
            $tag = trim($tag[2]);
            $this->parameters[$parameterName] = $parameterValue;
        }
        unset($this->parameters[""]);
        $this->closeBracketType = $tag;
        if (strlen($this->closeBracketType) > 2){
            $this->closeBracketType = "/>";
        }
    }
    public function __toString()
    {
        $result = "<";
        $result .= $this->tagType;
        if ($this->tagType == "w:t>") {
            $result .= $this->parameters["text"];
            $result .= $this->closeBracketType;
            return $result;
        }
        foreach (array_keys($this->parameters) as $key){
            $result .= " " . $key . "=\"" . $this->parameters[$key] . "\"";
        }
        $result .= $this->closeBracketType;
        return $result;
    }
}

class Paragraph
{
    public $paragraphProperties = array(); //просто ряд тегов (не древовидная структура!) от w:p до первого w:r
    public $regions = array(); // массив массивов тегов (не древовидных структур!) от w:r до /w:r - по количеству регионов в параграфе
    // Параграф всегда заканчивается /w:p без параметров, так что хранить его не надо
    //TODO: Сделать параграфу автоприсваивание замыкающих тегов /w:rPr, /w:pPr. То же самое с региональными тегами
    public function __toString()
    {
        $result = "";
        foreach ($this->paragraphProperties as $tag)
            $result .= strval($tag);
        foreach ($this->regions as $region)
            foreach ($region as $tag)
                $result .= $tag;
        return $result;
    }
}

class Bracket
{
    public $paragraphProperties = array();
    public $forbiddenParagraphProperties = array();
    public $regionProperties = array();
    public $forbiddenRegionProperties = array();
    public static function Merge($baseBracket, $newBracket){
        $result = new Bracket();
        $result->paragraphProperties = array_merge($baseBracket->paragraphProperties, $newBracket->paragraphProperties);
        $result->forbiddenParagraphProperties = array_merge($baseBracket->forbiddenParagraphProperties, $newBracket->forbiddenParagraphProperties);
        $result->regionProperties = array_merge($baseBracket->regionProperties, $newBracket->regionProperties);
        $result->forbiddenRegionProperties = array_merge($baseBracket->forbiddenRegionProperties, $newBracket->forbiddenRegionProperties);
        foreach (array_keys($result->paragraphProperties) as $property){
            unset($result->forbiddenParagraphProperties[$property]);
        }
        foreach (array_keys($result->regionProperties) as $property){
            unset($result->forbiddenRegionProperties[$property]);
        }
        return $result;
    }
}

//<editor-fold desc="Brackets">
// Создание корзин проверки
// Базовая корзина
$baseBracket = new Bracket();
$baseBracket->paragraphProperties["w:ind"] = new Tag('w:ind w:firstLine="709"/>');
$baseBracket->paragraphProperties["w:jc"] = new Tag('w:jc w:val="both"/>"');
$baseBracket->paragraphProperties["w:spacing"] = new Tag('w:spacing w:line="360" w:lineRule="auto"/>');
$baseBracket->regionProperties["w:rFonts"] = new Tag('w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>');
$baseBracket->regionProperties["w:sz"] = new Tag('w:sz w:val="28"/>');
foreach (["w:b/>", "w:i/>", "w:strike/>", "w:u", "w:color", "w:highlight"] as $tag){
    $baseBracket->forbiddenRegionProperties[$tag] = null;
}

//Корзина заголовка
$headerBracket = new Bracket();
$headerBracket->regionProperties["w:b/>"] = new Tag("w:b/>");
$headerBracket = Bracket::Merge($baseBracket, $headerBracket);
unset($headerBracket->forbiddenRegionProperties["w:b/>"]);
//Корзина кода
$codeBracket = new Bracket();
$codeBracket->regionProperties["w:rFonts"] = new Tag('w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:cs="Courier New"/>');
$codeBracket->regionProperties["w:sz"] = new Tag('w:sz w:val="26"/>');
$codeBracket = Bracket::Merge($baseBracket, $codeBracket);
//Корзина подписи к картинке (не отличается от обычного параграфа, но на всякий случай выделен отдельным типом)
$captionBracket = new Bracket();
$captionBracket = Bracket::Merge($baseBracket, $captionBracket);
//Корзина картинки
$drawingBracket = new Bracket();
$drawingBracket->forbiddenRegionProperties["w:t>"] = null;
//Нижний колонтитул с нумерацией (особый тип)
$footerBracketArray = [];
$footerBracketArray["w:jc"] = new Tag('w:jc w:val="center"/>');
//Конструктор Tag ведёт себя неочевидным образом в подобных случаях. W:t был обработан отдельно; на этот случай нет
//желания (иначе стоило бы переделать и w:t, а это заденет всю рабочую версию проекта)
$footerBracketArray["w:instrText>PAGE"] = new Tag('w:instrText>PAGE   \* MERGEFORMAT');

//Все корзины в одном массиве - любой формат возможен и равнозначен
$brackets = array($baseBracket, $headerBracket, $codeBracket, $captionBracket, $drawingBracket);
//Описание корзин
$bracketsDescription = [0 => "простой абзац", 1 => "заголовок", 2 => "код", 3 => "подпись к картинке", 4 => "картинка"];

//Теги, относящиеся к разделу целиком (изменение производится ТОЛЬКО разрывом раздела)
//Отдельный метод и хранилище связано с тем, что процедура их проверки может отличаться от проверки абзацных тегов, а
//также с отдельным форматом вывода лога
$sectorTags = [];
$sectorTags["w:pgMar"] = new Tag('w:pgMar w:top="1134" w:right="850" w:bottom="1134" w:left="1701" w:header="709" w:footer="709" w:gutter="0"/>');
$sectorTags["w:pageSz"] = new Tag('w:pgSz w:w="11906" w:h="16838"/>');
//</editor-fold>

//Возвращает ссылку на последний элемент массива (в PHP такой функции нет)
function endKey($array){
    end($array);
    return key($array);
}

$pageCounter = 1;
$paragraphCounter = 1;
$previousParagraphType = -1;
$textWithMistake = "";
$log = [];

//TODO: Сделать вывод лога и внести передачу данных из paragraphChecker
function formLog($problemTagNames, $bracketType = 0){
    global $pageCounter, $paragraphCounter, $log, $bracketsDescription, $textWithMistake;
    if (count($problemTagNames) == 0)
        return;
    if ($textWithMistake == ""){
        if ($bracketType != 4)
            $log[$pageCounter][$paragraphCounter . " абзац: "] = "Предупреждение: эта строка пустая";
        return;
    }
    $result = "";
    foreach (array_keys($problemTagNames) as $problemTagName) {
        if ($problemTagName == "w:rFonts") {
            $result .= ", неверный шрифт";
        } elseif ($problemTagName == "w:sz") {
            $result .= ", неверный размер шрифта";
        } elseif ($problemTagName == "w:jc") {
            $result .= ", неправильное выравнивание по ширине";
        } elseif ($problemTagName == "w:ind") {
            $result .= ", неверный оступ (должны быть 1,25 слева и 1,5 справа)";
        } elseif ($problemTagName == "w:spacing") {
            $result .= ", неправильный междустрочный интервал (1,5; убедитесь, что интервал есть лишь после абзаца)";
        } elseif ($problemTagName == "w:b/>") {
            if ($problemTagNames[$problemTagName] === null)
                $result .= ", полужирный шрифт (его быть не должно)";
            else
                $result .= ", здесь должен быть полужирный шрифт";
        } elseif ($problemTagName == "w:i/>") {
            $result .= ", наклонный шрифт (его быть не должно)";
        } elseif ($problemTagName == "w:strike/>") {
            $result .= ", перечёркнутый текст (его быть не должно)";
        } elseif ($problemTagName == "w:u") {
            $result .= ", подчёркнутый текст (его быть не должно)";
        } elseif ($problemTagName == "w:color") {
            $result .= ", неправильный цвет текста (должен быть чёрный)";
        } elseif ($problemTagName == "w:highlight") {
            $result .= ", неправильная заливка фона текста (должна быть белой)";
        } elseif ($problemTagName == "w:t>") {
            $result .= ", в этом абзаце не должно быть текста";
        }
        else {
            $result .= ", НЕИЗВЕСТНАЯ ОШИБКА";
        }
    }
    $index = "Абзац ". $paragraphCounter. ' "' . mb_substr($textWithMistake, 0, 20). '"' . " определён как " . $bracketsDescription[$bracketType];
    $result = "Ошибки: " . substr($result, 2);
    $log[$pageCounter][$index] .= $result;
}

function formSectorLog($problemTagNames){
    global $log;
    if (count($problemTagNames) == 0)
        return;
    $result = "";
    foreach (array_keys($problemTagNames) as $problemTagName) {
        if ($problemTagName == "w:pgMar")
            $result .= ", неверные поля (должны быть: левое - 3 см, правое - 1,5 см, верхнее и нижнее - по 2 см)";
        elseif ($problemTagNames = "w:pgSz")
            $result .= ", ошибочный размер страниц (нужен A4)";
        else
            $result .= ", НЕИЗВЕСТНАЯ ОШИБКА";
    }
    if (!array_key_exists(0, $log))
        array_unshift($log, []);
    $log[0][''] = "Ошибки во всём документе: " . substr($result, 2);
}

function checkTags(&$paragraphTags, &$baseTags, $tagsMustBe){
    $problemTagNames = [];
    foreach (array_keys($baseTags) as $baseTagName) {
        $containsTag = array_key_exists($baseTagName, $paragraphTags);
        if ($containsTag and !$tagsMustBe) {
            $problemTagNames[$baseTagName] = $tagsMustBe;
        }
        if ($tagsMustBe and $baseTags[$baseTagName] != $paragraphTags[$baseTagName]) {
            $problemTagNames[$baseTagName] = $tagsMustBe;
        }
    }
    return $problemTagNames;
}

function checkSectorInformationTags(&$paragraphTags){
    global $sectorTags;
    $problemTagNames = [];
    foreach ($sectorTags as $sectorTag){
        if ($paragraphTags[$sectorTag->tagType] != $sectorTag) {
            array_push($problemTagNames, $sectorTag->tagType);
        }
    }
    return $problemTagNames;
}

function getParagraphTypeId(&$paragraph){
    global $previousParagraphType;
    if ($previousParagraphType == 4)
        return 3;
    foreach ($paragraph->regions as $region){
        if (array_key_exists("w:b/>", $region))
            return 1;
        if (array_key_exists("w:rFonts", $region) and $region["w:rFonts"]->parameters["w:ascii"] == "Courier New")
            return 2;
        if (array_key_exists("w:drawing>", $region))
            return 4;
    }
    return 0;
}

function paragraphChecker(&$paragraph){
    global $brackets, $textWithMistake, $previousParagraphType;
    $textWithMistake = "";
    $bracketIndex = getParagraphTypeId($paragraph);
    foreach($paragraph->regions as $region){
        if (array_key_exists("w:t>", $region)){
            $textWithMistake .= $region["w:t>"]->parameters["text"];
            if (strlen($textWithMistake) >= 60)
                break;
        }
    }
    $problemTagNames = [];
    $problemTagNames += checkTags($paragraph->paragraphProperties, $brackets[$bracketIndex]->paragraphProperties, true);
    $problemTagNames += checkTags($paragraph->paragraphProperties, $brackets[$bracketIndex]->forbiddenParagraphProperties, false);
    foreach ($paragraph->regions as $region){
        $problemTagNames += checkTags($region, $brackets[$bracketIndex]->regionProperties, true);
        $problemTagNames += checkTags($region, $brackets[$bracketIndex]->forbiddenRegionProperties, false);
    }
    if (array_key_exists("w:sectPr", $paragraph->paragraphProperties))
        formSectorLog(checkSectorInformationTags($paragraph->paragraphProperties));
    else
        formLog($problemTagNames, $bracketIndex);
    $previousParagraphType = $bracketIndex;
}

if ($zip->open($filename)) {
    if (($index = $zip->locateName("word/document.xml")) !== false) {
        $content = $zip->getFromIndex($index);
        $tags = explode("<", $content);
        $currentParagraph = new Paragraph();
        $isOnRegions = false;
        for ($currentTagIndex = 0; $currentTagIndex < count($tags); $currentTagIndex++){
            $tag = new Tag($tags[$currentTagIndex]);
            if ($tag->tagType == "w:r" or $tag->tagType == "w:r>"){
                $isOnRegions = true;
                array_push($currentParagraph->regions, array($tag->tagType => $tag));
            }
            else if ($tag->tagType == "/w:p>" or count($tags) - $currentTagIndex == 1){
                array_push($currentParagraph->paragraphProperties, $tag);
                paragraphChecker($currentParagraph);
                $currentParagraph = new Paragraph();
                $paragraphCounter++;
                $isOnRegions = false;
            }
            else {
                if ($tag->tagType == "w:lastRenderedPageBreak/>"){
                    $pageCounter++;
                    $log[$pageCounter] = [];
                }
                if ($isOnRegions){
                    $currentParagraph->regions[endKey($currentParagraph->regions)][$tag->tagType] = $tag;
                }
                else {
                    $currentParagraph->paragraphProperties[$tag->tagType] = $tag;
                }
            }
        }
    }
}


$zip = new ZipArchive();
if ($zip->open($filename)) {
    if (($index = $zip->locateName("word/footer2.xml")) !== false) {
        $content = $zip->getFromIndex($index);
        $unparcedTags = explode("<", $content);
        $tags = [];
        for ($i = 0; $i < count($unparcedTags); $i++) {
            $tag = new Tag($unparcedTags[$i]);
            $tags[$tag->tagType] = $tag;
        }
        if (!array_key_exists(0, $log))
            array_unshift($log, []);
        if ($tags["w:instrText>PAGE"] == $footerBracketArray["w:instrText>PAGE"]){
            if ($tags["w:jc"] != $footerBracketArray["w:jc"])
                $log[0][" "] = "Нижний колонтитул: нумерация должна идти посередине!";
        }
        else {
            $log[0][" "] = "Нижний колонтитул: не реализована нумерация страниц снизу посередине";
        }
    }
    else
        $log[0][" "] = "Нижний колонтитул: не реализована нумерация страниц снизу посередине";

}

$zip->close();
unlink($filename);

foreach ($log as $page) {
    foreach ($page as $key => $value){
        echo $key . "\n" . $value ."\n" . "\n";
    }
}