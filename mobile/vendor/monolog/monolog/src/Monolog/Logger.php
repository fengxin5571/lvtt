<?php
//dezend by  QQ:2172298892
namespace Monolog;

class Logger implements \Psr\Log\LoggerInterface
{
	const DEBUG = 100;
	const INFO = 200;
	const NOTICE = 250;
	const WARNING = 300;
	const ERROR = 400;
	const CRITICAL = 500;
	const ALERT = 550;
	const EMERGENCY = 600;
	const API = 1;

	/**
     * Logging levels from syslog protocol defined in RFC 5424
     *
     * @var array $levels Logging levels
     */
	static protected $levels = array(self::DEBUG => 'DEBUG', self::INFO => 'INFO', self::NOTICE => 'NOTICE', self::WARNING => 'WARNING', self::ERROR => 'ERROR', self::CRITICAL => 'CRITICAL', self::ALERT => 'ALERT', self::EMERGENCY => 'EMERGENCY');
	/**
     * @var \DateTimeZone
     */
	static protected $timezone;
	/**
     * @var string
     */
	protected $name;
	/**
     * The handler stack
     *
     * @var HandlerInterface[]
     */
	protected $handlers;
	/**
     * Processors that will process all log records
     *
     * To process records of a single handler instead, add the processor on that specific handler
     *
     * @var callable[]
     */
	protected $processors;
	/**
     * @var bool
     */
	protected $microsecondTimestamps = true;

	public function __construct($name, array $handlers = array(), array $processors = array())
	{
		$this->name = $name;
		$this->handlers = $handlers;
		$this->processors = $processors;
	}

	public function getName()
	{
		return $this->name;
	}

	public function withName($name)
	{
		$new = clone $this;
		$new->name = $name;
		return $new;
	}

	public function pushHandler(Handler\HandlerInterface $handler)
	{
		array_unshift($this->handlers, $handler);
		return $this;
	}

	public function popHandler()
	{
		if (!$this->handlers) {
			throw new \LogicException('You tried to pop from an empty handler stack.');
		}

		return array_shift($this->handlers);
	}

	public function setHandlers(array $handlers)
	{
		$this->handlers = array();

		foreach (array_reverse($handlers) as $handler) {
			$this->pushHandler($handler);
		}

		return $this;
	}

	public function getHandlers()
	{
		return $this->handlers;
	}

	public function pushProcessor($callback)
	{
		if (!is_callable($callback)) {
			throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), ' . var_export($callback, true) . ' given');
		}

		array_unshift($this->processors, $callback);
		return $this;
	}

	public function popProcessor()
	{
		if (!$this->processors) {
			throw new \LogicException('You tried to pop from an empty processor stack.');
		}

		return array_shift($this->processors);
	}

	public function getProcessors()
	{
		return $this->processors;
	}

	public function useMicrosecondTimestamps($micro)
	{
		$this->microsecondTimestamps = (bool) $micro;
	}

	public function addRecord($level, $message, array $context = array())
	{
		if (!$this->handlers) {
			$this->pushHandler(new Handler\StreamHandler('php://stderr', static::DEBUG));
		}

		$levelName = static::getLevelName($level);
		$handlerKey = null;
		reset($this->handlers);

		while ($handler = current($this->handlers)) {
			if ($handler->isHandling(array('level' => $level))) {
				$handlerKey = key($this->handlers);
				break;
			}

			next($this->handlers);
		}

		if (null === $handlerKey) {
			return false;
		}

		if (!static::$timezone) {
			static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
		}

		if ($this->microsecondTimestamps) {
			$ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
		}
		else {
			$ts = new \DateTime(null, static::$timezone);
		}

		$ts->setTimezone(static::$timezone);
		$record = array(
			'message'    => (string) $message,
			'context'    => $context,
			'level'      => $level,
			'level_name' => $levelName,
			'channel'    => $this->name,
			'datetime'   => $ts,
			'extra'      => array()
			);

		foreach ($this->processors as $processor) {
			$record = call_user_func($processor, $record);
		}

		while ($handler = current($this->handlers)) {
			if (true === $handler->handle($record)) {
				break;
			}

			next($this->handlers);
		}

		return true;
	}

	public function addDebug($message, array $context = array())
	{
		return $this->addRecord(static::DEBUG, $message, $context);
	}

	public function addInfo($message, array $context = array())
	{
		return $this->addRecord(static::INFO, $message, $context);
	}

	public function addNotice($message, array $context = array())
	{
		return $this->addRecord(static::NOTICE, $message, $context);
	}

	public function addWarning($message, array $context = array())
	{
		return $this->addRecord(static::WARNING, $message, $context);
	}

	public function addError($message, array $context = array())
	{
		return $this->addRecord(static::ERROR, $message, $context);
	}

	public function addCritical($message, array $context = array())
	{
		return $this->addRecord(static::CRITICAL, $message, $context);
	}

	public function addAlert($message, array $context = array())
	{
		return $this->addRecord(static::ALERT, $message, $context);
	}

	public function addEmergency($message, array $context = array())
	{
		return $this->addRecord(static::EMERGENCY, $message, $context);
	}

	static public function getLevels()
	{
		return array_flip(static::$levels);
	}

	static public function getLevelName($level)
	{
		if (!isset(static::$levels[$level])) {
			throw new \Psr\Log\InvalidArgumentException('Level "' . $level . '" is not defined, use one of: ' . implode(', ', array_keys(static::$levels)));
		}

		return static::$levels[$level];
	}

	static public function toMonologLevel($level)
	{
		if (is_string($level) && defined('Monolog\\Logger' . '::' . strtoupper($level))) {
			return constant('Monolog\\Logger' . '::' . strtoupper($level));
		}

		return $level;
	}

	public function isHandling($level)
	{
		$record = array('level' => $level);

		foreach ($this->handlers as $handler) {
			if ($handler->isHandling($record)) {
				return true;
			}
		}

		return false;
	}

	public function log($level, $message, array $context = array())
	{
		$level = static::toMonologLevel($level);
		return $this->addRecord($level, $message, $context);
	}

	public function debug($message, array $context = array())
	{
		return $this->addRecord(static::DEBUG, $message, $context);
	}

	public function info($message, array $context = array())
	{
		return $this->addRecord(static::INFO, $message, $context);
	}

	public function notice($message, array $context = array())
	{
		return $this->addRecord(static::NOTICE, $message, $context);
	}

	public function warn($message, array $context = array())
	{
		return $this->addRecord(static::WARNING, $message, $context);
	}

	public function warning($message, array $context = array())
	{
		return $this->addRecord(static::WARNING, $message, $context);
	}

	public function err($message, array $context = array())
	{
		return $this->addRecord(static::ERROR, $message, $context);
	}

	public function error($message, array $context = array())
	{
		return $this->addRecord(static::ERROR, $message, $context);
	}

	public function crit($message, array $context = array())
	{
		return $this->addRecord(static::CRITICAL, $message, $context);
	}

	public function critical($message, array $context = array())
	{
		return $this->addRecord(static::CRITICAL, $message, $context);
	}

	public function alert($message, array $context = array())
	{
		return $this->addRecord(static::ALERT, $message, $context);
	}

	public function emerg($message, array $context = array())
	{
		return $this->addRecord(static::EMERGENCY, $message, $context);
	}

	public function emergency($message, array $context = array())
	{
		return $this->addRecord(static::EMERGENCY, $message, $context);
	}

	static public function setTimezone(\DateTimeZone $tz)
	{
		self::$timezone = $tz;
	}
}

?>
