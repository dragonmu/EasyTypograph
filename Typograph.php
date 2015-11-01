<?php

/**
 * автор: Tandit Viktor
 * email: Dragonmuonline@mail.ru
 */
class Typograph {

    private $_htmlText = "";
    private $_text = "";
    private $_encoding = 'UTF-8';

    /**
     * Типы кавычек
     */
    public $QuoteFirsOpen = '&laquo;';
    public $QuoteFirsClose = '&raquo;';
    public $QuoteCrawseOpen = '&ldquo;';
    public $QuoteCrawseClose = '&ldquo;';

    /**
     * Возвращает из UTF-8 в выставленную ранее кодировку
     * @param string $str
     * @return string
     */
    public function decode($str) {
        return mb_convert_encoding($str, 'UTF-8', $this->_encoding);
    }

    /**
     * Возвращае текст UTF-8 из выставленной ранее кодировки
     * @param string $str
     * @return string
     */
    public function encode($str) {
        return mb_convert_encoding($str, $this->_encoding, 'UTF-8');
    }

    /**
     * Назначает кодировку, необходимо в случае, если кодировка по умолчанию не UTF-8.
     * http://php.net/manual/ru/mbstring.supported-encodings.php
     * @param string $encoding
     */
    public function setConvertFromEncoding($encoding) {
        $this->_encoding = $encoding;
    }

    /**
     * Функция применяет все форматирующие функции к тексту
     * @param string $text
     * @return string
     */
    public function process($text) {
        //Переведем текст в utf-8
        $this->_htmlText = $this->decode($text);
        //Превратим теги в коды, которые не будут мешать форматированию
        $this->_text = $this->saveTags($this->_htmlText);
        $this->_text = $this->processManySpacesToOne($this->_text);
        $this->_text = $this->processSuperNbsp($this->_text);
        $this->_text = $this->processThinspBetweenNoAndNumber($this->_text);
        $this->_text = $this->processQuotes($this->_text);
        $this->_text = $this->processNobrVtchItdItp($this->_text);
        $this->_text = $this->processAcronymSmIm($this->_text);
        $this->_text = $this->processVtchItdItp($this->_text);
        $this->_text = $this->processPsPps($this->_text);
        $this->_text = $this->processToLiboNibud($this->_text);
        $this->_text = $this->processNbspOrgAbbr($this->_text);
        $this->_text = $this->processThinspBetweenNumberTriads($this->_text);
        $this->_text = $this->processNbspMdashNbsp($this->_text);
        $this->_text = $this->processNobrDate($this->_text);
        $this->_text = $this->processSpacesNobrInSurnameAbbr($this->_text);
        $this->_text = $this->processMathChars($this->_text);
        $this->_text = $this->processNbspBeforeUnit($this->_text);
        $this->_text = $this->processNbspMoneyAbbr($this->_text);
        $this->_text = $this->processNoWrapFloat($this->_text);
        //Вернем теги в нормальное состоияние
        $this->_htmlText = $this->loadTags($this->_text);
        //Вернем кодировку текста
        $this->_htmlText = $this->encode($this->_htmlText);
        return $this->_htmlText;
    }

    /**
     * Уберает лишние пробелы
     * @param type $text
     * @return type
     */
    public function processManySpacesToOne($text) {
        return preg_replace('/(\040|\t)+/', ' ', $text);
    }

    /**
     * Заменяет пробел между частицами (1-2 символа), на спецсимвол пробела
     * @param type $text
     * @return type
     */
    public function processSuperNbsp($text) {
        return preg_replace_callback('/(\s|^|\&(la|bd)quo\;|\>|\(|\&mdash\;\&nbsp\;)([a-zа-яё]{1,2}\s+)([a-zа-яё]{1,2}\s+)?([a-zа-яё0-9\-]{2,}|[0-9])/iu', function($m) {
            return $m[1] . trim($m[3]) . "&nbsp;" . ($m[4] ? trim($m[4]) . "&nbsp;" : "") . $m[5];
        }, $text);
    }

    /**
     * текст после № не переносится на следующую строку
     * @param type $text
     * @return type
     */
    public function processThinspBetweenNoAndNumber($text) {
        return preg_replace('/(№|\&#8470\;)(\s|&nbsp;)*(\d)/iu', '&#8470;&thinsp;\3', $text);
    }

    /**
     * заменяет обычные ковычки на красивые
     * @global type $__ax
     * @global int $__ay
     * @param type $text
     * @return string
     */
    public function processQuotes($text) {
        //Глобальные потому внутри callback 157 строчка
        global $__ax, $__ay;
        $quotPosisionStack = array('0');
        $quotPosision = 0;
        $level = 0;
        $off = 0;
        //end нужна на случай закрытого тега
        $text = preg_replace('/(^|\(|\s|\>|end|-)(\"|\\\"|&quot;)(\S+)/iu', '\1' . $this->QuoteFirsOpen . '\3', $text);
        $text = preg_replace('/(\"\"|&quot;&quot;)/iu', $this->QuoteFirsClose . $this->QuoteFirsClose, $text);
        $text = preg_replace('/([a-zа-яё0-9]|\.|\&hellip\;|\!|\?|\>|\)|\:)((\"|\\\"|&quot;)+)(\.|\&hellip\;|\;|\:|\?|\!|\,|\s|\)|\<\/|<|$)/iu', '\1' . $this->QuoteFirsClose . '\4', $text);

        while (true) {
            $position = $this->strposEx($text, array("&laquo;", "&raquo;"), $off);
            if ($position === false)
                break;
            if ($position['str'] == "&laquo;") {
                if ($level > 0) {
                    $text = $this->injectIn($position['pos'], $this->QuoteCrawseOpen, $text);
                }
                $level++;
            }
            if ($position['str'] == "&raquo;") {
                $level--;
                if ($level > 0) {
                    $text = $this->injectIn($position['pos'], $this->QuoteCrawseClose, $text);
                }
            }
            $off = $position['pos'] + strlen($position['str']);
            if ($level == 0) {
                $quotPosision = $off;
                array_push($quotPosisionStack, $quotPosision);
            } elseif ($level < 0) { // уровень стал меньше нуля
                do {
                    $lockPosition = array_pop($quotPosisionStack);
                    $k = substr($text, $lockPosition, $off - $lockPosition);
                    $k = str_replace($this->QuoteCrawseOpen, $this->QuoteFirsOpen, $k);
                    $k = str_replace($this->QuoteCrawseClose, $this->QuoteFirsClose, $k);

                    $amount = 0;
                    $__ax = preg_match_all("/(^|[^0-9])([0-9]+)\&raquo\;/ui", $k, $m);
                    $__ay = 0;
                    if ($__ax) {
                        $k = preg_replace_callback("/(^|[^0-9])([0-9]+)\&raquo\;/ui", create_function('$m', 'global $__ax,$__ay; $__ay++; if($__ay==$__ax){ return $m[1].$m[2]."&Prime;";} return $m[0];'), $k);
                        $amount = 1;
                    }
                } while (($amount == 0) && count($quotPosisionStack));

                // успешно сделали замену
                if ($amount == 1) {
                    // заново просмотрим содержимое
                    $text = substr($text, 0, $lockPosition) . $k . substr($text, $off);
                    $off = $lockPosition;
                    $level = 0;
                    continue;
                }

                // иначе просто заменим последнюю явно на &quot; от отчаяния
                if ($amount == 0) {
                    // говорим, что всё в порядке
                    $level = 0;
                    $text = substr($text, 0, $position['pos']) . '&quot;' . substr($text, $off);
                    $off = $position['pos'] + strlen('&quot;');
                    $quotPosisionStack = array($off);
                    continue;
                }
            }
        }
        // не совпало количество, отменяем все подкавычки
        if ($level != 0) {

            // закрывающих меньше, чем надо
            if ($level > 0) {
                $k = substr($text, $quotPosision);
                $k = str_replace($this->QuoteCrawseOpen, $this->QuoteFirsOpen, $k);
                $k = str_replace($this->QuoteCrawseClose, $this->QuoteFirsClose, $k);
                $text = substr($text, 0, $quotPosision) . $k;
            }
        }
        return $text;
    }

    /**
     * оборачивает в nobr и тп, и тд, в тч
     * @param type $text
     * @return type
     */
    public function processNobrVtchItdItp($text) {
        preg_match('/и( |\&nbsp\;)т\.?[ ]?д(\.|$|\s|\&nbsp\;)/u', $text, $m);
        if (!empty($m)) {
            $text = preg_replace('/и( |\&nbsp\;)т\.?[ ]?д(\.|$|\s|\&nbsp\;)/u', $this->tag("и т. д.", "nobr") . ($m[2] != "." ? $m[2] : "" ), $text);
        }
        preg_match('/и( |\&nbsp\;)т\.?[ ]?п(\.|$|\s|\&nbsp\;)/u', $text, $m);
        if (!empty($m)) {
            $text = preg_replace('/и( |\&nbsp\;)т\.?[ ]?п(\.|$|\s|\&nbsp\;)/u', $this->tag("и т. п.", "nobr") . ($m[2] != "." ? $m[2] : "" ), $text);
        }
        preg_match('/в( |\&nbsp\;)т\.?[ ]?ч(\.|$|\s|\&nbsp\;)/u', $text, $m);
        if (!empty($m)) {
            $text = preg_replace('/в( |\&nbsp\;)т\.?[ ]?ч(\.|$|\s|\&nbsp\;)/u', $this->tag("в т. ч.", "nobr") . ($m[2] != "." ? $m[2] : "" ), $text);
        }
        return $text;
    }

    /**
     * без переноса строки стр, рис ...
     * @param type $text
     * @return type
     */
    public function processAcronymSmIm($text) {
        $text = preg_replace('/(\s|^|\>|\()(гл|стр|рис|илл?|ст|п|с)\.(\040|\t)*(\d+)/iu', '\1\2.&nbsp;\4\5', $text);
        $text = preg_replace('/(\s|^|\>|\()(см|им)\.(\040|\t)*([а-яё0-9a-z]+)/iu', '\1\2.&nbsp;\4\5', $text);
        return $text;
    }

    /**
     * без переноса до нэ
     * @param type $text
     * @return type
     */
    public function processVtchItdItp($text) {
        $text = preg_replace('/(\s|\&nbsp\;)до( |\&nbsp\;)н\.?[ ]?э\./u', '&nbsp;до&nbsp;н.э.', $text);
        $text = preg_replace('/( |\&nbsp\;)н\.?[ ]?э\./u', '&nbsp;н.э.', $text);
        return $text;
    }

    /**
     * nobr ps pps
     * @param type $text
     * @return type
     */
    public function processPsPps($text) {
        $text = preg_replace_callback('/(^|\040|\t|\>|\r|\n)(p\.\040?)(p\.\040?)?(s\.)([^\<])/i', function($m) {
            return $m[1] . "<nobr>" . (trim($m[2]) . " " . ($m[3] ? trim($m[3]) . " " : "") . $m[4]) . "</nobr>" . $m[5];
        }, $text);
        return $text;
    }

    /**
     * без переноса строки слова через -
     * @param type $text
     * @return type
     */
    public function processToLiboNibud($text) {
        return preg_replace('/([а-яa-z]+\-[а-яa-z]+)/ui', '<nobr>\\1</nobr>', $text);
    }

    /**
     * без переноса строки организации
     * @param type $text
     * @return type
     */
    public function processNbspOrgAbbr($text) {
        return preg_replace('/(ООО|ЗАО|ОАО|НИИ|ПБОЮЛ) ([a-zA-Zа-яёА-ЯЁ]|\"|\&laquo\;|\&bdquo\;|<)/u', '\1&nbsp;\2', $text);
    }

    /**
     * без переноса строки числа 1 000 000 и тд
     * @param type $text
     * @return type
     */
    public function processThinspBetweenNumberTriads($text) {
        $text = preg_replace_callback('/([0-9]{1,3}( [0-9]{3}){1,})(.|$)/u', function($m) {
            return ($m[3] == "-" ? $m[0] : str_replace(" ", "&nbsp;", $m[1]) . $m[3]);
        }, $text);
        return $text;
    }

    /**
     * - на —
     * @param type $text
     * @return type
     */
    public function processNbspMdashNbsp($text) {
        return preg_replace('/( |&nbsp;|&thinsp;)(\-)( |&nbsp;|&thinsp;)/iu', '\1&mdash;\3', $text);
    }

    /**
     * без переноса строки числа месяца
     * @param type $text
     * @return type
     */
    public function processNobrDate($text) {
        $text = preg_replace('/([0-9]{2}\ ((январ|феврал|сентябр|октябр|ноябр|декабр)([ьяюе]|[её]м)|(апрел|июн|июл)([ьяюе]|ем)|(март|август)([ауе]|ом)?|ма[йяюе]|маем)\ [0-9]{4})/iu', '<nobr>\1</nobr>', $text);
        $text = preg_replace('/([0-9]{2}\.[0-9]{2}\.[0-9]{4})/iu', '<nobr>\1</nobr>', $text);
        return $text;
    }

    /**
     * без переноса строки инициалы
     * @param type $text
     * @return type
     */
    public function processSpacesNobrInSurnameAbbr($text) {
        preg_match('/([А-ЯЁ])(\.)(\s|\&nbsp\;)?([А-ЯЁ])(\.(\s|\&nbsp\;)?|(\s|\&nbsp\;))([А-ЯЁ][а-яё]+)(\s|$|\.|\,|\;|\:|\?|\!|\&nbsp\;)/u', $text, $m);
        if (!empty($m)) {
            $text = preg_replace('/([А-ЯЁ])(\.?)(\s|\&nbsp\;)?([А-ЯЁ])(\.(\s|\&nbsp\;)?|(\s|\&nbsp\;))([А-ЯЁ][а-яё]+)(\s|$|\.|\,|\;|\:|\?|\!|\&nbsp\;)/u', $this->tag($m[1] . ". " . $m[4] . ". " . $m[8], "nobr") . $m[9], $text);
        }
        preg_match('/(\s|^|\.|\,|\;|\:|\?|\!|\&nbsp\;)([А-ЯЁ][а-яё]+)(\s|\&nbsp\;)([А-ЯЁ])\.?(\s|\&nbsp\;)?([А-ЯЁ])\.?/u', $text, $m);
        if (!empty($m)) {
            $text = preg_replace('/(\s|^|\.|\,|\;|\:|\?|\!|\&nbsp\;)([А-ЯЁ][а-яё]+)(\s|\&nbsp\;)([А-ЯЁ])\.?(\s|\&nbsp\;)?([А-ЯЁ])\.?/u', $m[1] . $this->tag($m[2] . " " . $m[4] . ". " . $m[6] . ".", "nobr"), $text);
        }
        return $text;
    }

    /**
     * заменяеи +- на мнемонический код
     * @param type $text
     * @return type
     */
    public function processMathChars($text) {
        return preg_replace('/\+-/iu', '&plusmn;', $text);
    }

    /**
     * без переноса строки единицы измерения
     * @param type $text
     * @return type
     */
    public function processNbspBeforeUnit($text) {
        $text = preg_replace('/(\d+)( |\&nbsp\;)?(м|мм|см|дм|км|гм|km|dm|cm|mm)(\s|\.|\!|\?|\,|$|\&plusmn\;|\;|[32]|&sup3;|&sup2;)/iu', '\1&nbsp;\3\4', $text);
        $text = preg_replace_callback('/(\d+)( |\&nbsp\;)?(м|мм|см|дм|км|гм|km|dm|cm|mm)([32]|&sup3;|&sup2;)/iu', function($m) {
            return $m[1] . "&nbsp;" . $m[3] . ($m[4] == "3" || $m[4] == "2" ? "&sup" . $m[4] . ";" : $m[4] );
        }, $text);
        return $text;
    }

    /**
     * без переноса строки суммы денег
     * @param type $text
     * @return type
     */
    public function processNbspMoneyAbbr($text) {
        return preg_replace_callback('/(\d)((\040|\&nbsp\;)?(тыс|млн|млрд)\.?(\040|\&nbsp\;)?)?(\040|\&nbsp\;)?(руб\.|долл\.|евро|€|&euro;|\$|у[\.]? ?е[\.]?)/iu', function($m) {
            return $m[1] . ($m[4] ? "&nbsp;" . $m[4] . ($m[4] == "тыс" ? "." : "") : "") . "&nbsp;" . (!preg_match("#у[\\\\.]? ?е[\\\\.]?#iu", $m[7]) ? $m[7] : "у.е.");
        }, $text);
    }

    /**
     * без переноса строки дробные числа
     * @param type $text
     * @return type
     */
    public function processNoWrapFloat($text) {
        return preg_replace_callback('/\b(\d+(?:[\.\,])\d+)((?:[\.])\d+)?/iu', function($m) {
            return ((!isset($m[2])) ? "<nobr>" . $m[1] . "</nobr>" : $m[1] . $m[2]);
        }, $text);
    }

    /**
     * Оборачивает текс в тег с классом
     * @param string $text
     * @param string $tag
     * @param string $class
     * @return string
     */
    public function tag($text, $tag, $class = "") {
        return '<' . $tag . (($class != '') ? ' class="' . $class . '"' : '') . '>' . $text . '</' . $tag . '>';
    }

    /**
     * Функция кодирует все html теги
     * @param string $text
     * @return string
     */
    public function saveTags($text) {
        return preg_replace_callback('/(\<\/?)(\w+ \b(?:\'[^\']*\'|"[^"]*"|[^\>])*)?(\>)/iusx', function($m) {
            return $m[1] . base64_encode($m[2]) . '=' . $m[3];
        }, $text);
    }

    /**
     * Возвращает html теги
     * @param string $text
     * @return string
     */
    public function loadTags($text) {
        return preg_replace_callback('/(\<\/?)(.[^(><)]*)(=)(\>)/iu', function($m) {
            return $m[1] . base64_decode($m[2] . $m[3]) . $m[4];
        }, $text);
    }

    /**
     * Ищет символ в строке
     * @param type $haystack
     * @param type $needle
     * @param type $offset
     * @return boolean||array('pos'=>int,'str'=>string) 
     */
    public static function strposEx(&$haystack, $needle, $offset = null) {
        if (is_array($needle)) {
            $position = false;
            $searchedSymbol = false;
            foreach ($needle as $searchSymbol) {
                $result = strpos($haystack, $searchSymbol, $offset);
                if ($result === false)
                    continue;
                if ($position === false) {
                    $position = $result;
                    $searchedSymbol = $searchSymbol;
                    continue;
                }
                if ($result < $position) {
                    $position = $result;
                    $searchedSymbol = $searchSymbol;
                }
            }
            if ($position === false)
                return false;
            return array('pos' => $position, 'str' => $searchedSymbol);
        }
        return strpos($haystack, $needle, $offset);
    }

    /**
     * Заменяет тег в тексте по букве
     * @param type $pos
     * @param type $tag
     * @param type $text
     * @return type
     */
    public function injectIn($pos, $tag, $text) {
        for ($i = 0; $i < strlen($tag); $i++) {
            $text[$pos + $i] = $tag[$i];
        }
        return $text;
    }

}
