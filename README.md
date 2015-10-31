# EasyTypograph #
**EasyTypograph** - просто типограф для сайта покрытый PHPUnit тестами.
# Пример использования #
```
  <?php
  
  //Базовый сценарий использования
  $typograph = new Typograph;
  $goodText = $typograph->process($badText);

  //Использование типографа с любой кодировкой
  $typograph = new Typograph;
  //http://php.net/manual/ru/mbstring.supported-encodings.php
  $typograph->setConvertFromEncoding($encoding);
  $goodText = $typograph->process($badText);
```
**Функция process** - пример функции для обработки текста.

# Тесты #
[![Build Status](https://travis-ci.org/dragonmu/EasyTypograph.svg?branch=master)](https://travis-ci.org/dragonmu/EasyTypograph)
[![Coverage Status](https://coveralls.io/repos/dragonmu/EasyTypograph/badge.svg?branch=master&service=github)](https://coveralls.io/github/dragonmu/EasyTypograph?branch=master)
