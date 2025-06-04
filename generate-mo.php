<?php
/**
 * Генератор .mo файла для MLM Marketing
 * Запустите этот файл для создания marketing-ru_RU.mo
 */

// Простой парсер .po файлов и генератор .mo
class MoGenerator {
    
    public static function poToMo($poFile, $moFile) {
        $translations = self::parsePo($poFile);
        return self::writeMo($moFile, $translations);
    }
    
    private static function parsePo($poFile) {
        if (!file_exists($poFile)) {
            die("PO файл не найден: $poFile");
        }
        
        $translations = [];
        $lines = file($poFile, FILE_IGNORE_NEW_LINES);
        $currentMsgid = '';
        $currentMsgstr = '';
        $inMsgid = false;
        $inMsgstr = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Пропускаем комментарии и пустые строки
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            // msgid
            if (strpos($line, 'msgid "') === 0) {
                if ($currentMsgid && $currentMsgstr) {
                    $translations[$currentMsgid] = $currentMsgstr;
                }
                $currentMsgid = substr($line, 7, -1);
                $currentMsgstr = '';
                $inMsgid = true;
                $inMsgstr = false;
            }
            // msgstr
            elseif (strpos($line, 'msgstr "') === 0) {
                $currentMsgstr = substr($line, 8, -1);
                $inMsgid = false;
                $inMsgstr = true;
            }
            // Продолжение строки
            elseif ($line[0] === '"') {
                $content = substr($line, 1, -1);
                if ($inMsgid) {
                    $currentMsgid .= $content;
                } elseif ($inMsgstr) {
                    $currentMsgstr .= $content;
                }
            }
        }
        
        // Добавляем последний перевод
        if ($currentMsgid && $currentMsgstr) {
            $translations[$currentMsgid] = $currentMsgstr;
        }
        
        return $translations;
    }
    
    private static function writeMo($moFile, $translations) {
        // Удаляем пустой ключ если есть
        unset($translations['']);
        
        $ids = array_keys($translations);
        $strings = array_values($translations);
        
        $count = count($ids);
        
        // Вычисляем необходимые смещения
        $headerSize = 28;
        $tableSize = $count * 8;
        $idsOffset = $headerSize + 2 * $tableSize;
        $stringsOffset = $idsOffset;
        
        foreach ($ids as $id) {
            $stringsOffset += strlen($id) + 1;
        }
        
        // Открываем файл для записи
        $file = fopen($moFile, 'wb');
        if (!$file) {
            return false;
        }
        
        // Записываем заголовок
        // Magic number
        fwrite($file, pack('V', 0x950412de));
        // Version
        fwrite($file, pack('V', 0));
        // Number of strings
        fwrite($file, pack('V', $count));
        // Offset of original strings table
        fwrite($file, pack('V', $headerSize));
        // Offset of translated strings table
        fwrite($file, pack('V', $headerSize + $tableSize));
        // Hash table size
        fwrite($file, pack('V', 0));
        // Hash table offset
        fwrite($file, pack('V', $headerSize + 2 * $tableSize));
        
        // Записываем таблицу оригинальных строк
        $offset = $idsOffset;
        foreach ($ids as $id) {
            fwrite($file, pack('V', strlen($id)));
            fwrite($file, pack('V', $offset));
            $offset += strlen($id) + 1;
        }
        
        // Записываем таблицу переведенных строк
        $offset = $stringsOffset;
        foreach ($strings as $string) {
            fwrite($file, pack('V', strlen($string)));
            fwrite($file, pack('V', $offset));
            $offset += strlen($string) + 1;
        }
        
        // Записываем оригинальные строки
        foreach ($ids as $id) {
            fwrite($file, $id . "\0");
        }
        
        // Записываем переведенные строки
        foreach ($strings as $string) {
            fwrite($file, $string . "\0");
        }
        
        fclose($file);
        return true;
    }
}

// Если файл запущен напрямую
if (php_sapi_name() === 'cli' || (!empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'generate-mo.php') !== false)) {
    
    echo "<h1>Генератор .mo файла для MLM Marketing</h1>";
    
    $pluginDir = dirname(__FILE__) . '/';
    $languagesDir = $pluginDir . 'languages/';
    
    // Создаем папку languages если её нет
    if (!file_exists($languagesDir)) {
        mkdir($languagesDir, 0755, true);
        echo "<p>Создана папка languages/</p>";
    }
    
    // Сначала создадим .po файл если его нет
    $poFile = $languagesDir . 'marketing-ru_RU.po';
    if (!file_exists($poFile)) {
        echo "<p>Создаю файл marketing-ru_RU.po...</p>";
        
        // Вставляем содержимое .po файла
        $poContent = '# Translation of MLM Marketing in Russian
# This file is distributed under the same license as the MLM Marketing package.
msgid ""
msgstr ""
"Project-Id-Version: MLM Marketing\n"
"POT-Creation-Date: 2025-01-01 12:00+0000\n"
"PO-Revision-Date: 2025-01-01 12:00+0600\n"
"Language-Team: Russian\n"
"Language: ru_RU\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Plural-Forms: nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);\n"

msgid "Overview"
msgstr "Обзор"

msgid "Distributor Panel"
msgstr "Панель дистрибьютора"

msgid "Commodity Circulation Panel"
msgstr "Панель товарооборота"

msgid "Circulation Panel"
msgstr "Панель циркуляции"

msgid "Structure Panel"
msgstr "Структурная панель"

msgid "Family Panel"
msgstr "Семейная панель"

msgid "Rewards History"
msgstr "История вознаграждений"

msgid "Settings"
msgstr "Настройки"

msgid "Settings saved!"
msgstr "Настройки сохранены!"

msgid "MLM Settings"
msgstr "Настройки MLM"

msgid "Default Sponsor"
msgstr "Спонсор по умолчанию"

msgid "Automatically process orders"
msgstr "Автоматически обрабатывать заказы"

msgid "Date of Rank\'s change"
msgstr "Дата изменения ранга"

msgid "Distributor panel"
msgstr "Панель дистрибьютора"

msgid "Distributor Register"
msgstr "Регистрация дистрибьютора"

msgid "Name"
msgstr "Имя"

msgid "Phone"
msgstr "Телефон"

msgid "Sponsor ID"
msgstr "ID спонсора"

msgid "Select Sponsor"
msgstr "Выбрать спонсора"

msgid "Current Users"
msgstr "Текущие пользователи"

msgid "Sl no"
msgstr "№ п/п"

msgid "Unique ID"
msgstr "Уникальный ID"

msgid "City Name"
msgstr "Название города"

msgid "Sponsor Name"
msgstr "Имя спонсора"

msgid "Action"
msgstr "Действие"

msgid "This user phone already exits!"
msgstr "Этот номер телефона уже зарегистрирован!"

msgid "New Distributor register!"
msgstr "Новый дистрибьютор зарегистрирован!"

msgid "Distributor update successful!"
msgstr "Дистрибьютор успешно обновлен!"

msgid "Search:"
msgstr "Поиск:"

msgid "Show _MENU_ entries"
msgstr "Показать _MENU_ записей"

msgid "Showing _START_ to _END_ of _TOTAL_ entries"
msgstr "Показано с _START_ по _END_ из _TOTAL_ записей"

msgid "Showing 0 to 0 of 0 entries"
msgstr "Показано с 0 по 0 из 0 записей"

msgid "No data available in table"
msgstr "В таблице нет данных"

msgid "First"
msgstr "Первая"

msgid "Previous"
msgstr "Предыдущая"

msgid "Next"
msgstr "Следующая"

msgid "Last"
msgstr "Последняя"

msgid "Are you sure?"
msgstr "Вы уверены?"

msgid "You won\'t be able to revert this!"
msgstr "Вы не сможете отменить это действие!"

msgid "Yes"
msgstr "Да"

msgid "City"
msgstr "Город"

msgid "Select City"
msgstr "Выберите город"

msgid "Submit"
msgstr "Отправить"

msgid "Circulation Commodity"
msgstr "Товарооборот"

msgid "Distributor ID"
msgstr "ID дистрибьютора"

msgid "Select Distributor"
msgstr "Выберите дистрибьютора"

msgid "Personal Circulation Commodity"
msgstr "Личный товарооборот"

msgid "Additional expenses"
msgstr "Дополнительные расходы"

msgid "Contribution"
msgstr "Взнос"

msgid "Distributor Name"
msgstr "Имя дистрибьютора"

msgid "Source"
msgstr "Источник"

msgid "site"
msgstr "сайт"

msgid "direct"
msgstr "напрямую"

msgid "Amount"
msgstr "Сумма"

msgid "Transaction Date"
msgstr "Дата транзакции"

msgid "Somethings wents wrong!"
msgstr "Что-то пошло не так!"

msgid "New Personal Circulation Commodity added!"
msgstr "Новый личный товарооборот добавлен!"

msgid "Do you wont to create Circulation Commodity"
msgstr "Вы хотите создать товарооборот?"

msgid "You wont be able to revert this!"
msgstr "Вы не сможете отменить это!"

msgid "List of reports (Pay)"
msgstr "Список отчетов (К оплате)"

msgid "File"
msgstr "Файл"

msgid "List of reports (Payed)"
msgstr "Список отчетов (Оплачено)"

msgid "Rank"
msgstr "Ранг"

msgid "PCC"
msgstr "PCC"

msgid "SCC"
msgstr "SCC"

msgid "Direct Reward"
msgstr "Прямое вознаграждение"

msgid "Structural Reward"
msgstr "Структурное вознаграждение"

msgid "Management Reward"
msgstr "Управленческое вознаграждение"

msgid "BR"
msgstr "BR"

msgid "BRC"
msgstr "BRC"

msgid "ALLR"
msgstr "ВСЕ"

msgid "Select All"
msgstr "Выбрать все"

msgid "Pay"
msgstr "Оплатить"

msgid "History of getting rewards by distributors"
msgstr "История получения вознаграждений дистрибьюторами"

msgid "Amount of rewards"
msgstr "Сумма вознаграждений"

msgid "Create"
msgstr "Создать"

msgid "Rewards history"
msgstr "История вознаграждений"

msgid "User ID"
msgstr "ID пользователя"

msgid "Payout Rewards"
msgstr "Выплаченные вознаграждения"

msgid "After account balance"
msgstr "Баланс после операции"

msgid "Payout Date and Time"
msgstr "Дата и время выплаты"

msgid "User Name"
msgstr "Имя пользователя"

msgid "Date and Time"
msgstr "Дата и время"

msgid "Search User"
msgstr "Поиск пользователя"

msgid "Search"
msgstr "Поиск"

msgid "Management"
msgstr "Управление"

msgid "MLM Marketing"
msgstr "MLM Маркетинг"

msgid "This plugin for multi lavel marketing and rank basis reward."
msgstr "Этот плагин для многоуровневого маркетинга и вознаграждений на основе рангов."

msgid "For Modern user interaction"
msgstr "Для современного взаимодействия с пользователем"

msgid "Login"
msgstr "Вход"

msgid "Login using"
msgstr "Вход используя"

msgid "shortcode or a widget."
msgstr "шорткод или виджет."

msgid "Registration"
msgstr "Регистрация"

msgid "Registration using"
msgstr "Регистрация используя"

msgid "Profile"
msgstr "Профиль"

msgid "Profile using"
msgstr "Профиль используя"

msgid "User Login"
msgstr "Вход пользователя"

msgid "Email or password field is empty."
msgstr "Поле email или пароль пустое."

msgid "Wrong credentials."
msgstr "Неверные учетные данные."

msgid "Email"
msgstr "Email"

msgid "Password"
msgstr "Пароль"

msgid "Welcome Back"
msgstr "С возвращением"

msgid "Go To Profile"
msgstr "Перейти в профиль"

msgid "Please enter a valid email address"
msgstr "Пожалуйста, введите корректный email адрес"

msgid "Please provide a password"
msgstr "Пожалуйста, введите пароль"

msgid "Your password must be at least 5 characters long"
msgstr "Ваш пароль должен содержать минимум 5 символов"

msgid "User Registration"
msgstr "Регистрация пользователя"

msgid "All fields is requird."
msgstr "Все поля обязательны."

msgid "Данный номер уже зарегистрирован"
msgstr "Данный номер уже зарегистрирован"

msgid "New user successfully registered!."
msgstr "Новый пользователь успешно зарегистрирован!"

msgid "Please enter phone number"
msgstr "Пожалуйста, введите номер телефона"

msgid "Please enter your name"
msgstr "Пожалуйста, введите ваше имя"

msgid "Please choose your sponsor"
msgstr "Пожалуйста, выберите вашего спонсора"

msgid "User Profile"
msgstr "Профиль пользователя"

msgid "User id"
msgstr "ID пользователя"

msgid "All Rewards"
msgstr "Все вознаграждения"

msgid "Personal Transaction Table"
msgstr "Таблица личных транзакций"

msgid "Sl no."
msgstr "№ п/п"

msgid "Family tree"
msgstr "Семейное древо"

msgid "PLease Login or register to view profile."
msgstr "Пожалуйста, войдите или зарегистрируйтесь для просмотра профиля."

msgid "Register"
msgstr "Регистрация"

msgid "Error to delete"
msgstr "Ошибка при удалении"

msgid "Successfully deleted!"
msgstr "Успешно удалено!"

msgid "Exist children, can not delete!"
msgstr "Существуют дочерние элементы, невозможно удалить!"

msgid "Phone already exists"
msgstr "Номер телефона уже существует"

msgid "Registration error: "
msgstr "Ошибка регистрации: "

msgid "Error to create"
msgstr "Ошибка при создании"

msgid "Successfully created"
msgstr "Успешно создано"

msgid "Error to add!"
msgstr "Ошибка при добавлении!"

msgid "Successfully added!"
msgstr "Успешно добавлено!"
';
        
        file_put_contents($poFile, $poContent);
        echo "<p class='success'>✓ Файл marketing-ru_RU.po создан</p>";
    }
    
    // Генерируем .mo файл
    $moFile = $languagesDir . 'marketing-ru_RU.mo';
    
    echo "<p>Генерирую файл marketing-ru_RU.mo...</p>";
    
    if (MoGenerator::poToMo($poFile, $moFile)) {
        echo "<p class='success'>✓ Файл marketing-ru_RU.mo успешно создан!</p>";
        echo "<p>Размер файла: " . filesize($moFile) . " байт</p>";
        
        // Копируем в альтернативные расположения
        $altLocations = [
            dirname(__FILE__) . '/marketing-ru_RU.mo',
            WP_CONTENT_DIR . '/languages/plugins/marketing-ru_RU.mo'
        ];
        
        foreach ($altLocations as $altLocation) {
            $altDir = dirname($altLocation);
            if (!file_exists($altDir)) {
                @mkdir($altDir, 0755, true);
            }
            if (@copy($moFile, $altLocation)) {
                echo "<p class='success'>✓ Скопировано в: $altLocation</p>";
            }
        }
        
    } else {
        echo "<p class='error'>✗ Ошибка при создании .mo файла</p>";
    }
    
    echo "<h2>Что делать дальше:</h2>";
    echo "<ol>";
    echo "<li>Убедитесь, что язык сайта установлен на Русский (Настройки → Общие)</li>";
    echo "<li>Очистите кэш WordPress и браузера</li>";
    echo "<li>Перезагрузите страницу админ-панели</li>";
    echo "</ol>";
}

?>
<style>
.success { color: green; font-weight: bold; }
.error { color: red; font-weight: bold; }
body { font-family: Arial, sans-serif; padding: 20px; }
</style>