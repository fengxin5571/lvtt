<?php
//dezend by  QQ:2172298892
namespace Monolog\Handler;

class SlackHandlerTest extends \Monolog\TestCase
{
	/**
     * @var resource
     */
	private $res;
	/**
     * @var SlackHandler
     */
	private $handler;

	public function setUp()
	{
		if (!extension_loaded('openssl')) {
			$this->markTestSkipped('This test requires openssl to run');
		}
	}

	public function testWriteHeader()
	{
		$this->createHandler();
		$this->handler->handle($this->getRecord(\Monolog\Logger::CRITICAL, 'test1'));
		fseek($this->res, 0);
		$content = fread($this->res, 1024);
		$this->assertRegexp('/POST \\/api\\/chat.postMessage HTTP\\/1.1\\r\\nHost: slack.com\\r\\nContent-Type: application\\/x-www-form-urlencoded\\r\\nContent-Length: \\d{2,4}\\r\\n\\r\\n/', $content);
	}

	public function testWriteContent()
	{
		$this->createHandler();
		$this->handler->handle($this->getRecord(\Monolog\Logger::CRITICAL, 'test1'));
		fseek($this->res, 0);
		$content = fread($this->res, 1024);
		$this->assertRegexp('/token=myToken&channel=channel1&username=Monolog&text=&attachments=.*$/', $content);
	}

	public function testWriteContentUsesFormatterIfProvided()
	{
		$this->createHandler('myToken', 'channel1', 'Monolog', false);
		$this->handler->handle($this->getRecord(\Monolog\Logger::CRITICAL, 'test1'));
		fseek($this->res, 0);
		$content = fread($this->res, 1024);
		$this->createHandler('myToken', 'channel1', 'Monolog', false);
		$this->handler->setFormatter(new \Monolog\Formatter\LineFormatter('foo--%message%'));
		$this->handler->handle($this->getRecord(\Monolog\Logger::CRITICAL, 'test2'));
		fseek($this->res, 0);
		$content2 = fread($this->res, 1024);
		$this->assertRegexp('/token=myToken&channel=channel1&username=Monolog&text=test1.*$/', $content);
		$this->assertRegexp('/token=myToken&channel=channel1&username=Monolog&text=foo--test2.*$/', $content2);
	}

	public function testWriteContentWithEmoji()
	{
		$this->createHandler('myToken', 'channel1', 'Monolog', true, 'alien');
		$this->handler->handle($this->getRecord(\Monolog\Logger::CRITICAL, 'test1'));
		fseek($this->res, 0);
		$content = fread($this->res, 1024);
		$this->assertRegexp('/icon_emoji=%3Aalien%3A$/', $content);
	}

	public function testWriteContentWithColors($level, $expectedColor)
	{
		$this->createHandler();
		$this->handler->handle($this->getRecord($level, 'test1'));
		fseek($this->res, 0);
		$content = fread($this->res, 1024);
		$this->assertRegexp('/color%22%3A%22' . $expectedColor . '/', $content);
	}

	public function testWriteContentWithPlainTextMessage()
	{
		$this->createHandler('myToken', 'channel1', 'Monolog', false);
		$this->handler->handle($this->getRecord(\Monolog\Logger::CRITICAL, 'test1'));
		fseek($this->res, 0);
		$content = fread($this->res, 1024);
		$this->assertRegexp('/text=test1/', $content);
	}

	public function provideLevelColors()
	{
		return array(
	array(\Monolog\Logger::DEBUG, '%23e3e4e6'),
	array(\Monolog\Logger::INFO, 'good'),
	array(\Monolog\Logger::NOTICE, 'good'),
	array(\Monolog\Logger::WARNING, 'warning'),
	array(\Monolog\Logger::ERROR, 'danger'),
	array(\Monolog\Logger::CRITICAL, 'danger'),
	array(\Monolog\Logger::ALERT, 'danger'),
	array(\Monolog\Logger::EMERGENCY, 'danger')
	);
	}

	private function createHandler($token = 'myToken', $channel = 'channel1', $username = 'Monolog', $useAttachment = true, $iconEmoji = NULL, $useShortAttachment = false, $includeExtra = false)
	{
		$constructorArgs = array($token, $channel, $username, $useAttachment, $iconEmoji, \Monolog\Logger::DEBUG, true, $useShortAttachment, $includeExtra);
		$this->res = fopen('php://memory', 'a');
		$this->handler = $this->getMock('\\Monolog\\Handler\\SlackHandler', array('fsockopen', 'streamSetTimeout', 'closeSocket'), $constructorArgs);
		$reflectionProperty = new \ReflectionProperty('\\Monolog\\Handler\\SocketHandler', 'connectionString');
		$reflectionProperty->setAccessible(true);
		$reflectionProperty->setValue($this->handler, 'localhost:1234');
		$this->handler->expects($this->any())->method('fsockopen')->will($this->returnValue($this->res));
		$this->handler->expects($this->any())->method('streamSetTimeout')->will($this->returnValue(true));
		$this->handler->expects($this->any())->method('closeSocket')->will($this->returnValue(true));
		$this->handler->setFormatter($this->getIdentityFormatter());
	}
}

?>
