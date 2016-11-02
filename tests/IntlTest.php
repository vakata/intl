<?php
namespace vakata\intl\test;

class IntlTest extends \PHPUnit_Framework_TestCase
{
	protected static $data = [ 'some' => [ 'more' => 'keys', 'even' => 'more' ], 'other' => [ 'key' => 'val' ] ];

	public static function setUpBeforeClass() {
	}
	public static function tearDownAfterClass() {
	}
	protected function setUp() {
		file_put_contents(__DIR__ . '/en.json', json_encode(static::$data));
		file_put_contents(__DIR__ . '/en.ini', "[some]\nmore = \"keys\"\neven = \"more\"\n\n[other]\nkey = \"val\"\n");
		if (is_file(__DIR__ . '/en_copy.json')) {
			unlink(__DIR__ . '/en_copy.json');
		}
	}
	protected function tearDown() {
	}

	public function testCode() {
		$intl = new \vakata\intl\Intl('en_US');
		$this->assertEquals('en_US', $intl->getCode());
		$this->assertEquals('en', $intl->getCode(true));
	}
	public function testFromArray() {
		$intl = new \vakata\intl\Intl('en_US');
		$data = [ 'test' => 'test', 'some' => [ 'more' => 'keys' ] ];
		$intl->fromArray($data);
		$this->assertEquals($data, $intl->toArray());
	}
	public function testAccess() {
		$intl = new \vakata\intl\Intl('en_US');
		$data = [ 'test' => 'test', 'some' => [ 'more' => 'keys' ] ];
		$intl->fromArray($data);
		$this->assertEquals('test', $intl('test'));
		$this->assertEquals('keys', $intl('some.more'));
		$this->assertEquals('nonexisting', $intl('nonexisting'));
		$this->assertEquals('some.nonexisting', $intl('some.nonexisting'));
		$this->assertEquals('default', $intl('some.nonexisting', [], 'default'));
		$this->assertEquals('', $intl(''));
	}
	public function testFromJSONFile() {
		$intl = new \vakata\intl\Intl('en_US');
		$intl->fromFile(__DIR__ . '/en.json', 'json');
		$this->assertEquals(static::$data, $intl->toArray());
	}
	public function testFromINIFile() {
		$intl = new \vakata\intl\Intl('en_US');
		$intl->fromFile(__DIR__ . '/en.ini', 'ini');
		$this->assertEquals(static::$data, $intl->toArray());
	}
	public function testToJSONFile() {
		$intl = new \vakata\intl\Intl('en_US');
		$intl->fromFile(__DIR__ . '/en.json', 'json');
		$intl->toFile(__DIR__ . '/en_copy.json', 'json');
		$this->assertEquals(static::$data, json_decode(file_get_contents(__DIR__ . '/en_copy.json'), true));
		unlink(__DIR__ . '/en_copy.json');
	}
	public function testFormatter() {
		$intl = new \vakata\intl\Intl('bg_BG');
		$intl->fromArray(['test' => 'Всичко е {0}']);
		$this->assertEquals('Всичко е ОК', $intl('test', ['ОК']));
	}
	public function testArrayKey() {
		$intl = new \vakata\intl\Intl('bg_BG');
		$intl->fromArray(['test1' => '1', 'test2' => '2']);
		$this->assertEquals('1', $intl('test1'));
		$this->assertEquals('2', $intl('test2'));
		$this->assertEquals('1', $intl(['test1', 'test2']));
		$this->assertEquals('2', $intl(['missing', 'test2']));
		$this->assertEquals('3', $intl(['missing', 'missing2'], [], '3'));
	}
}
