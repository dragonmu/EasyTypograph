<?php

/**
 * автор: Tandit Viktor
 * email: Dragonmuonline@mail.ru
 */
class TypographTest extends PHPUnit_Framework_TestCase {

    public function __construct() {
        //Волшебный путь для travis-ci.org
        require_once (str_replace('tests', '', __DIR__) . 'Typograph.php');
    }

    public function fullTestRule($rule, $result, $original) {
        $typograph = new Typograph;
        $this->assertEquals($result, $typograph->$rule($original));
        // проверяем что другие правила не влияют на результат
        $this->assertEquals($result, $typograph->process($original));
        // префиксы и постфиксы, которы не должны влиять на результат

        $dat = array(
            array('<b>', '</b>'),
            array('', ','),
            array('<b>', '.</b>'),
            array('(', ')'),
            array('>', '<'),
        );
        foreach ($dat as $val) {
            $this->assertEquals($val[0] . $result . $val[1], $typograph->process($val[0] . $original . $val[1]));
        }
    }

    public function testProcessManySpacesToOne() {
        $this->fullTestRule("processManySpacesToOne", "sda asfas sdfdsg sdfdsfsd fg .", "sda asfas    sdfdsg       sdfdsfsd   fg           .");
    }

    public function testProcessSuperNbsp() {
        $this->fullTestRule("processSuperNbsp", "доставка к&nbsp;дому, вася и&nbsp;петя, не&nbsp;с&nbsp;петя", "доставка к дому, вася и петя, не с петя");
    }

    public function testThinspBetweenNoAndNumber() {
        $this->fullTestRule("processThinspBetweenNoAndNumber", "&#8470;&thinsp;25 &#8470;&thinsp;25", "№25 &#8470;25");
    }

    public function testSingleQuotes() {
        $this->fullTestRule("processQuotes", "&laquo;тут тоже текст крутая ковычка&raquo;", "\"тут тоже текст крутая ковычка\"");
    }

    public function testQuotes() {
        $this->fullTestRule("processQuotes", "&laquo;тут тоже текст &ldquo;Эназвание&ldquo; крутая ковычка&raquo;", "\"тут тоже текст \"Эназвание\" крутая ковычка\"");
        $typograph = new Typograph;
        $typograph->processQuotes("&laquo;Онлайн-кинотеатр &ldquo;Аййо&ldquo;&raquo;", "\"Онлайн-кинотеатр \"Аййо\"\"");
    }

    public function testNobrVtchItdItp() {
        $this->fullTestRule("processNobrVtchItdItp", "<nobr>и т. д.</nobr>, <nobr>и т. п.</nobr>, <nobr>в т. ч.</nobr>", "и т.д., и т.п., в т.ч.");
    }

    public function testAcronymSmIm() {
        $this->fullTestRule("processAcronymSmIm", "см.&nbsp;им.г.ул.пер.д.гл.стр.рис.илл. им.&nbsp;Сироткина", "см.им.г.ул.пер.д.гл.стр.рис.илл. им. Сироткина");
    }

    public function testVtchItdItp() {
        $this->fullTestRule("processVtchItdItp", "привязка сокращений&nbsp;до&nbsp;н.э.,&nbsp;н.э. ккк предыдущему слову", "привязка сокращений до н.э., н.э. ккк предыдущему слову");
    }

    public function testPsPps() {
        $this->fullTestRule("processPsPps", "объединение сокращений <nobr>P. S.</nobr> объединение сокращений <nobr>P. P. S.</nobr>.", "объединение сокращений P.S. объединение сокращений P. P. S..");
    }

    public function testToLiboNibud() {
        $this->fullTestRule("processToLiboNibud", "объединение слов дефисом как то. всавпавп, ыфваыфвапвы <nobr>где-то</nobr> ваыва, <nobr>селфи-гусь</nobr> селфи гусь.)", "объединение слов дефисом как то. всавпавп, ыфваыфвапвы где-то ваыва, селфи-гусь селфи гусь.)");
    }

    public function testNbspOrgAbbr() {
        $this->fullTestRule("processNbspOrgAbbr", "ООО&nbsp;Кувшинка, ЗАО&nbsp;Сильные Люди, ОАО&nbsp;кит, НИи Стали, НИИ&nbsp;Стали, ПБОЮЛ&nbsp;ЗАОШКИН", "ООО Кувшинка, ЗАО Сильные Люди, ОАО кит, НИи Стали, НИИ Стали, ПБОЮЛ ЗАОШКИН");
    }

    public function testThinspBetweenNumberTriads() {
        $this->fullTestRule("processThinspBetweenNumberTriads", "100&nbsp;000, 25&nbsp;000&nbsp;000", "100 000, 25 000 000");
    }

    public function testNbspMdashNbsp() {
        $this->fullTestRule("processNbspMdashNbsp", "лето &mdash; это хорошо, зима &mdash; это хорошо", "лето - это хорошо, зима - это хорошо");
    }

    public function testNobrDate() {
        $this->fullTestRule("processNobrDate", "<nobr>30 июля 2015</nobr>, <nobr>11 августа 2014</nobr>, <nobr>23.03.2014</nobr> <nobr>25.03.2015</nobr> <nobr>26.07.2016</nobr> ", "30 июля 2015, 11 августа 2014, 23.03.2014 25.03.2015 26.07.2016 ");
    }

    public function testSpacesNobrInSurnameAbbr() {
        $this->fullTestRule("processSpacesNobrInSurnameAbbr", "<nobr>В. Н. Пупкин</nobr>, <nobr>Сталин И. В.</nobr>", "В.Н. Пупкин, Сталин И.В.");
    }

    public function testMathChars() {
        $this->fullTestRule("processMathChars", "тут текст &plusmn; еще немного текста &plusmn; еще чуть чуть", "тут текст +- еще немного текста +- еще чуть чуть");
    }

    public function testNbspBeforeUnit() {
        $this->fullTestRule("processNbspBeforeUnit", "10&nbsp;м&sup2;, 22&nbsp;см&sup3;", "10 м2, 22 см3");
    }

    public function testNbspMoneyAbbr() {
        $this->fullTestRule("processNbspMoneyAbbr", "10&nbsp;$, 100&nbsp;руб., 5000&nbsp;евро", "10$, 100 руб., 5000 евро");
    }

    public function testNoWrapFloat() {
        $this->fullTestRule("processNoWrapFloat", "<nobr>28.30</nobr> <nobr>55,22</nobr> <nobr>22.33</nobr>,<nobr>20,12</nobr>,<nobr>23.22</nobr>", "28.30 55,22 22.33,20,12,23.22");
    }

    public function testProcess() {
        $typograph = new Typograph;
        $this->assertEquals(file_get_contents(__DIR__ . '/dat/out.html'), $typograph->process(file_get_contents(__DIR__ . '/dat/in.html')));
    }

    public function testEmpty() {
        $typograph = new Typograph;
        $this->assertEquals("", $typograph->process(""));
        $this->assertEquals(" ", $typograph->process(" "));
        $this->assertEquals("\n", $typograph->process("\n"));
    }

    public function testTagsAreNotModified() {
        //return;
        $typograph = new Typograph;
        $this->assertEquals("<test-tag><b s=\">>>\" a=\"привет в коде вам всем\">в&nbsp;тесте</b></test-tag>", $typograph->process("<test-tag><b s=\">>>\" a=\"привет в коде вам всем\">в тесте</b></test-tag>"));
    }

    public function testLostNbsp() {
        $typograph = new Typograph;
        $this->assertEquals("<div class=\"details\"><span class=\"label\">Возраст:</span> 34 года</div>", $typograph->process("<div class=\"details\"><span class=\"label\">Возраст:</span> 34 года</div>"));
    }

}
