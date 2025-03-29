<?php
namespace vakata\intl\test;

class IntlTest extends \PHPUnit\Framework\TestCase
{
    protected static $data = [
        'some.more' => 'keys',
        'some.even' => 'more',
        'other.key' => 'val'
    ];
    protected static $locale = [
        "_locale.code.short" => 'bg',
        "_locale.code.long"  => 'bg_BG',
        "_locale.days.short.1" => 'Пон',
        "_locale.days.short.2" => 'Вто',
        "_locale.days.short.3" => 'Сря',
        "_locale.days.short.4" => 'Чет',
        "_locale.days.short.5" => 'Пет',
        "_locale.days.short.6" => 'Съб',
        "_locale.days.short.7" => 'Нед',
        "_locale.days.long.1" => 'Понеделник',
        "_locale.days.long.2" => 'Вторник',
        "_locale.days.long.3" => 'Сряда',
        "_locale.days.long.4" => 'Четвъртък',
        "_locale.days.long.5" => 'Петък',
        "_locale.days.long.6" => 'Събота',
        "_locale.days.long.7" => 'Неделя',
        "_locale.months.short.1" => 'Яну',
        "_locale.months.short.2" => 'Фев',
        "_locale.months.short.3" => 'Мар',
        "_locale.months.short.4" => 'Апр',
        "_locale.months.short.5" => 'Май',
        "_locale.months.short.6" => 'Юни',
        "_locale.months.short.7" => 'Юли',
        "_locale.months.short.8" => 'Авг',
        "_locale.months.short.9" => 'Сеп',
        "_locale.months.short.10" => 'Окт',
        "_locale.months.short.11" => 'Ное',
        "_locale.months.short.12" => 'Дек',
        "_locale.months.long.1" => 'Януари',
        "_locale.months.long.2" => 'Февруари',
        "_locale.months.long.3" => 'Март',
        "_locale.months.long.4" => 'Април',
        "_locale.months.long.5" => 'Май',
        "_locale.months.long.6" => 'Юни',
        "_locale.months.long.7" => 'Юли',
        "_locale.months.long.8" => 'Август',
        "_locale.months.long.9" => 'Септември',
        "_locale.months.long.10" => 'Октомври',
        "_locale.months.long.11" => 'Ноември',
        "_locale.months.long.12" => 'Декември',
        "_locale.days.suffixes.1" => "ви",
        "_locale.days.suffixes.21" => "ви",
        "_locale.days.suffixes.31" => "ви",
        "_locale.days.suffixes.2" => "ри",
        "_locale.days.suffixes.22" => "ри",
        "_locale.days.suffixes.7" => "ми",
        "_locale.days.suffixes.27" => "ми",
        "_locale.days.suffixes.8" => "ми",
        "_locale.days.suffixes.28" => "ми",
        "_locale.days.suffixes.default" => "ти",
        "_locale.date.short" => "jS M Y",
        "_locale.date.long" => "jS F Y \г. H:i \ч\а\с\а",
        "_locale.numbers.decimal" => ",",
        "_locale.numbers.thousands" => " ",
    ];

    public function testCode() {
        $intl = new \vakata\intl\Intl();
        $this->assertEquals('en_US', $intl->getCode());
        $this->assertEquals('en', $intl->getCode(true));
    }
    public function testFromArray() {
        $intl = new \vakata\intl\Intl();
        $data = [ 'test' => 'test', 'some.more' => 'keys', 'dot.key' => 'dot.value' ];
        $intl->addTranslations('en', $data);
        $this->assertEquals($data, $intl->toArray());
        $this->assertEquals('dot.value', $intl('dot.key'));
    }
    public function testAccess() {
        $intl = new \vakata\intl\Intl();
        $data = [ 'test' => 'test', 'some.more' => 'keys' ];
        $intl->addTranslations('en', $data);
        $this->assertEquals('test', $intl('test'));
        $this->assertEquals('keys', $intl('some.more'));
        $this->assertEquals('nonexisting', $intl('nonexisting'));
        $this->assertEquals('some.nonexisting', $intl('some.nonexisting'));
        $this->assertEquals('default', $intl('some.nonexisting', [], 'default'));
        $this->assertEquals('', $intl(''));
    }
    public function testFormatter() {
        $intl = new \vakata\intl\Intl();
        $intl->addTranslations('en', ['test' => 'Всичко е {0}']);
        $this->assertEquals('Всичко е ОК', $intl('test', ['ОК']));
    }
    public function testArrayKey() {
        $intl = new \vakata\intl\Intl();
        $intl->addTranslations('en', ['test1' => '1', 'test2' => '2']);
        $this->assertEquals('1', $intl('test1'));
        $this->assertEquals('2', $intl('test2'));
        $this->assertEquals('1', $intl(['test1', 'test2']));
        $this->assertEquals('1', $intl(['TEST1', 'TEST2']));
        $this->assertEquals('2', $intl(['missing', 'test2']));
        $this->assertEquals('3', $intl(['missing', 'missing2'], [], '3'));
    }
    public function testDate() {
        $intl = new \vakata\intl\Intl();
        $intl->addTranslations('bg', static::$locale);
        $this->assertEquals('1ви Яну 2019', $intl->date('short', strtotime('01.01.2019')));
        $this->assertEquals('1ви Януари 2019 г. 00:00 часа', $intl->date('long', strtotime('01.01.2019')));
        $this->assertEquals('Януари', $intl->date('F', strtotime('01.01.2019')));
        $this->assertEquals('Вто', $intl->date('D', strtotime('01.01.2019')));
        $this->assertEquals('Вторник', $intl->date('l', strtotime('01.01.2019')));
    }
}
