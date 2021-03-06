<?php
//dezend by  QQ:2172298892
namespace Monolog\Handler;

class WhatFailureGroupHandlerTest extends \Monolog\TestCase
{
	public function testConstructorOnlyTakesHandler()
	{
		new WhatFailureGroupHandler(array(new TestHandler(), 'foo'));
	}

	public function testHandle()
	{
		$testHandlers = array(new TestHandler(), new TestHandler());
		$handler = new WhatFailureGroupHandler($testHandlers);
		$handler->handle($this->getRecord(\Monolog\Logger::DEBUG));
		$handler->handle($this->getRecord(\Monolog\Logger::INFO));

		foreach ($testHandlers as $test) {
			$this->assertTrue($test->hasDebugRecords());
			$this->assertTrue($test->hasInfoRecords());
			$this->assertTrue(count($test->getRecords()) === 2);
		}
	}

	public function testHandleBatch()
	{
		$testHandlers = array(new TestHandler(), new TestHandler());
		$handler = new WhatFailureGroupHandler($testHandlers);
		$handler->handleBatch(array($this->getRecord(\Monolog\Logger::DEBUG), $this->getRecord(\Monolog\Logger::INFO)));

		foreach ($testHandlers as $test) {
			$this->assertTrue($test->hasDebugRecords());
			$this->assertTrue($test->hasInfoRecords());
			$this->assertTrue(count($test->getRecords()) === 2);
		}
	}

	public function testIsHandling()
	{
		$testHandlers = array(new TestHandler(\Monolog\Logger::ERROR), new TestHandler(\Monolog\Logger::WARNING));
		$handler = new WhatFailureGroupHandler($testHandlers);
		$this->assertTrue($handler->isHandling($this->getRecord(\Monolog\Logger::ERROR)));
		$this->assertTrue($handler->isHandling($this->getRecord(\Monolog\Logger::WARNING)));
		$this->assertFalse($handler->isHandling($this->getRecord(\Monolog\Logger::DEBUG)));
	}

	public function testHandleUsesProcessors()
	{
		$test = new TestHandler();
		$handler = new WhatFailureGroupHandler(array($test));
		$handler->pushProcessor(function($record) {
			$record['extra']['foo'] = true;
			return $record;
		});
		$handler->handle($this->getRecord(\Monolog\Logger::WARNING));
		$this->assertTrue($test->hasWarningRecords());
		$records = $test->getRecords();
		$this->assertTrue($records[0]['extra']['foo']);
	}

	public function testHandleException()
	{
		$test = new TestHandler();
		$exception = new ExceptionTestHandler();
		$handler = new WhatFailureGroupHandler(array($exception, $test, $exception));
		$handler->pushProcessor(function($record) {
			$record['extra']['foo'] = true;
			return $record;
		});
		$handler->handle($this->getRecord(\Monolog\Logger::WARNING));
		$this->assertTrue($test->hasWarningRecords());
		$records = $test->getRecords();
		$this->assertTrue($records[0]['extra']['foo']);
	}
}
class ExceptionTestHandler extends TestHandler
{
	public function handle(array $record)
	{
		parent::handle($record);
		throw new \Exception('ExceptionTestHandler::handle');
	}
}

?>
