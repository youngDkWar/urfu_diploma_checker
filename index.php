<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Автопроверка ВКР</title>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather+Sans:wght@300&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="img-1"><img alt="Автопроверка ВКР" src="images/title.png"></div>
    <div class="info">
        <section class="box">
            <h3 class="box-content-name">Быстрый анализ текста</h3>
            <p class="box-content">Сервис очень быстро найдет все недочеты оформления вашей
                пояснительной записки, если такие имеются, и покажет вам их.</p>
        </section>
        <section class="box">
            <h3 class="box-content-name">Искусственный интелект</h3>
            <p class="box-content">Алгоритм проверки
                сам понимает тип текста каждой строки документа. Он запросто определит,
                что перед ним: заголовок, текст, список или вставка кода, и обработает соответствующим образом.</p>
        </section>
        <section class="box">
            <h3 class="box-content-name">Абсолютная безопасность</h3>
            <p class="box-content">Прикреплённый файл сохраняется на сервисе только на
            время выполнения скрипта обработки. Документ недоступен для всех, кроме вас. Поэтому вы можете быть
            уверены в сохранности ваших данных.</p>
        </section>
    </div>

    <div><a href='javascript: Doc()' style="text-decoration: none;">
            <img alt="плюсик" src="images/plus.jpeg" align=left id="img-2" onClick="chg(this.id,'check')">
            <div class="instruction" align=left onClick="chg(this.id,'check')">Возможности</div></a></div>
    <div id=doc style='text-indent:12pt; display:none'>
        <ul>
            <li class="description" align="left"><b>Проверка полей: </b>
                левое - 30 мм; правое - 15 мм; верхнее и нижнее – по 20 мм</li>
            <li class="description" align="left"><b>Нумерация документа: </b>внизу страницы по центру</li>
            <li class="description" align="left"><b>Гарнитура: </b>Times New Roman</li>
            <li class="description" align="left"><b>Кегль: </b>14 пт</li>
            <li class="description" align="left"><b>Интервал: </b>1,5</li>
            <li class="description" align="left"><b>Цвет шрифта: </b>чёрный</li>
            <li class="description" align="left"><b>Начертание: </b>прямое</li>
            <li class="description" align="left"><b>Отступ первой строки: </b>1,25 см</li>
            <li class="description" align="left">Полужирное начертание допустимо только для заголовков</li>
            <li class="description" align="left"><b>Листинг кода: </b>шрифт - Courier New, кегль - 12 пт</li>
        </ul>

    </div>

    <div style="margin: 0 0 50px 48px"><a href='javascript: Instruction()' style="text-decoration: none;">
            <img alt="плюсик" src="images/plus.jpeg" align=left id="img_1" onClick="chg(this.id,'check')">
            <div class="instruction" align=left onClick="chg(this.id,'check')">Инструкция</div></a></div>
    <div id=instruction style='text-indent:12pt;display:none'>
        <ol>
            <li class="description" align="left">Нажмите кнопку <b>"Загрузить файл"</b> и выберете документ формата
                <b>".docx"</b>, чтобы проверить его. Другие форматы недопустимы.</li>
            <li class="description" align="left">Нажмите кнопку <b>"Отправить"</b>, чтобы запустить проверку документа.</li>
        </ol>
        <img alt="manual" src="images/manual1.jpeg" class="manual">
        <img alt="manual" src="images/manual2.jpeg" class="manual">
        <ul>
            <li class="description"><b style="color: rgb(63, 72, 204)">Часть абзаца:</b> первые 15 символов абзаца в двойных
                кавычках. При помощи нее можно отыскать абзац в своём документе, например, при помощи "ctrl + f".</li>
            <li class="description"><b style="color: rgb(34, 177, 76)">Номер абзаца: </b>номер абзаца в документе.
             Позволяет примерно сориентироваться, где находится данный абзац.</li>
            <li class="description"><b style="color: rgb(255, 127, 39)">Тип текста: </b>показывает к какому типу
            относится данный абзац (заголовок, простой текст, список, картинка, вставка кода или надпись).</li>
            <li class="description"><b style="color: rgb(237, 28, 36)">Ошибки: </b>список всех найденных ошибок в
            этом абзаце.</li>
        </ul>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script src="scripts/field.js"></script>
    <div class="file_form">
        <form action="" enctype="multipart/form-data" method="post" class="form">
            <div class="fl_upld">
                <label><input type="file" name="f" multiple accept=".docx, .doc" id="fl_inp" class="file"></label>
                <input  type="submit" name="send" value=" " class="send">
                <div id="fl_nm">Файл не выбран</div>
            </div>
        </form>
    </div>
    <script src="scripts/help.js"></script>

<?PHP
$data = $_POST;
if(isset($data['send'])){
    require "main.php";
}
?>
</div>


<footer>
    <div id="fl_nm">Версия 1.0.1</div>
    <div id="fl_nm">© ️Powered by Shark and Skyshimmer</div>
</footer>
</div>
</body>
</html>