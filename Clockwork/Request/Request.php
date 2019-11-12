<?php namespace Clockwork\Request;

use Clockwork\Helpers\Serializer;

/**
 * Data structure representing a single application request
 */
class Request
{
	/**
	 * Unique request ID
	 */
	public $id;

	/**
	 * Data protocol version
	 */
	public $version = 1;

	// Request type
	public $type = 'request';

	/**
	 * Request time
	 */
	public $time;

	/**
	 * Request method
	 */
	public $method;

	/**
	 * Request URL
	 */
	public $url;

	/**
	 * Request URI
	 */
	public $uri;

	/**
	 * Request headers
	 */
	public $headers = [];

	/**
	 * Textual representation of executed controller
	 */
	public $controller;

	/**
	 * GET data array
	 */
	public $getData = [];

	/**
	 * POST data array
	 */
	public $postData = [];

	// Request data array
	public $requestData = [];

	/**
	 * Session data array
	 */
	public $sessionData = [];

	// Authenticated user
	public $authenticatedUser;

	/**
	 * Cookies array
	 */
	public $cookies = [];

	/**
	 * Response time
	 */
	public $responseTime;

	// Response processing time
	public $responseDuration;

	/**
	 * Response status code
	 */
	public $responseStatus;

	// Peak memory usage in bytes
	public $memoryUsage;

	// Executed middleware
	public $middleware = [];

	/**
	 * Database queries array
	 */
	public $databaseQueries = [];

	// Database queries count
	public $databaseQueriesCount;

	// Database slow queries count
	public $databaseSlowQueries;

	// Database query counts of a particular type
	public $databaseSelects;
	public $databaseInserts;
	public $databaseUpdates;
	public $databaseDeletes;
	public $databaseOthers;

	/**
	 * Cache queries array
	 */
	public $cacheQueries = [];

	/**
	 * Cache reads count
	 */
	public $cacheReads;

	/**
	 * Cache hits count
	 */
	public $cacheHits;

	/**
	 * Cache writes count
	 */
	public $cacheWrites;

	/**
	 * Cache deletes count
	 */
	public $cacheDeletes;

	/**
	 * Cache time
	 */
	public $cacheTime;

	// Redis commands
	public $redisCommands = [];

	// Dispatched queue jobs
	public $queueJobs = [];

	/**
	 * Timeline data array
	 */
	public $timelineData = [];

	/**
	 * Log messages array
	 */
	public $log = [];

	/**
	 * Fired events array
	 */
	public $events = [];

	/**
	 * Application routes array
	 */
	public $routes = [];

	/**
	 * Emails data array
	 */
	public $emailsData = [];

	/**
	 * Views data array
	 */
	public $viewsData = [];

	/**
	 * Custom user data (not used by Clockwork app)
	 */
	public $userData = [];

	public $subrequests = [];

	public $xdebug = [];

	// Command name
	public $commandName;

	// Command arguments passed in
	public $commandArguments = [];

	// Command arguments defaults
	public $commandArgumentsDefaults = [];

	// Command options passed in
	public $commandOptions = [];

	// Command options defaults
	public $commandOptionsDefaults = [];

	// Command exit code
	public $commandExitCode;

	// Command output
	public $commandOutput;

	/**
	 * Create a new request, if optional data array argument is provided, it will be used to populate the request object,
	 * otherwise empty request with autogenerated ID will be created
	 */
	public function __construct(array $data = null)
	{
		if ($data) {
			foreach ($data as $key => $val) {
				$this->$key = $val;
			}
		} else {
			$this->id = $this->generateRequestId();
		}
	}

	/**
	 * Compute and return sum of duration of all database queries
	 */
	public function getDatabaseDuration()
	{
		return array_reduce($this->databaseQueries, function ($total, $query) {
			return isset($query['duration']) ? $total + $query['duration'] : $total;
		}, 0);
	}

	/**
	 * Compute and return response duration in milliseconds
	 */
	public function getResponseDuration()
	{
		return ($this->responseTime - $this->time) * 1000;
	}

	/**
	 * Return request data as an array
	 */
	public function toArray()
	{
		return [
			'id'                       => $this->id,
			'version'                  => $this->version,
			'type'                     => $this->type,
			'time'                     => $this->time,
			'method'                   => $this->method,
			'url'                      => $this->url,
			'uri'                      => $this->uri,
			'headers'                  => $this->headers,
			'controller'               => $this->controller,
			'getData'                  => $this->getData,
			'postData'                 => $this->postData,
			'requestData'              => $this->requestData,
			'sessionData'              => $this->sessionData,
			'authenticatedUser'        => $this->authenticatedUser,
			'cookies'                  => $this->cookies,
			'responseTime'             => $this->responseTime,
			'responseStatus'           => $this->responseStatus,
			'responseDuration'         => $this->responseDuration ?: $this->getResponseDuration(),
			'memoryUsage'              => $this->memoryUsage,
			'middleware'               => $this->middleware,
			'databaseQueries'          => $this->databaseQueries,
			'databaseQueriesCount'     => $this->databaseQueriesCount,
			'databaseSlowQueries'      => $this->databaseSlowQueries,
			'databaseSelects'          => $this->databaseSelects,
			'databaseInserts'          => $this->databaseInserts,
			'databaseUpdates'          => $this->databaseUpdates,
			'databaseDeletes'          => $this->databaseDeletes,
			'databaseOthers'           => $this->databaseOthers,
			'databaseDuration'         => $this->getDatabaseDuration(),
			'cacheQueries'             => $this->cacheQueries,
			'cacheReads'               => $this->cacheReads,
			'cacheHits'                => $this->cacheHits,
			'cacheWrites'              => $this->cacheWrites,
			'cacheDeletes'             => $this->cacheDeletes,
			'cacheTime'                => $this->cacheTime,
			'redisCommands'            => $this->redisCommands,
			'queueJobs'                => $this->queueJobs,
			'timelineData'             => $this->timelineData,
			'log'                      => array_values($this->log),
			'events'                   => $this->events,
			'routes'                   => $this->routes,
			'emailsData'               => $this->emailsData,
			'viewsData'                => $this->viewsData,
			'userData'                 => array_map(function ($data) {
				return $data instanceof UserData ? $data->toArray() : $data;
			}, $this->userData),
			'subrequests'              => $this->subrequests,
			'xdebug'                   => $this->xdebug,
			'commandName'              => $this->commandName,
			'commandArguments'         => $this->commandArguments,
			'commandArgumentsDefaults' => $this->commandArgumentsDefaults,
			'commandOptions'           => $this->commandOptions,
			'commandOptionsDefaults'   => $this->commandOptionsDefaults,
			'commandExitCode'          => $this->commandExitCode,
			'commandOutput'            => $this->commandOutput
		];
	}

	/**
	 * Return request data as a JSON string
	 */
	public function toJson()
	{
		return json_encode($this->toArray(), \JSON_PARTIAL_OUTPUT_ON_ERROR);
	}

	// Add database query, takes query, bindings, duration and additional data - connection (connection name), file
	// (caller file name), line (caller line number), trace (serialized trace), model (associated ORM model)
	public function addDatabaseQuery($query, $bindings = [], $duration = null, $data = [])
	{
		$this->databaseQueries[] = [
			'query'      => $query,
			'bindings'   => $bindings,
			'duration'   => $duration,
			'connection' => isset($data['connection']) ? $data['connection'] : null,
			'file'       => isset($data['file']) ? $data['file'] : null,
			'line'       => isset($data['line']) ? $data['line'] : null,
			'trace'      => isset($data['trace']) ? $data['trace'] : null,
			'model'      => isset($data['model']) ? $data['model'] : null,
			'tags'       => array_merge(
				isset($data['tags']) ? $data['tags'] : [], isset($data['slow']) ? [ 'slow' ] : []
			)
		];
	}

	// Add cache query, takes type, key, value and additional data - connection (connection name), file
	// (caller file name), line (caller line number), trace (serialized trace), expiration
	public function addCacheQuery($type, $key, $value = null, $duration = null, $data = [])
	{
		$this->cacheQueries[] = [
			'type'       => $type,
			'key'        => $key,
			'value'      => (new Serializer)->normalize($value),
			'duration'   => $duration,
			'connection' => isset($data['connection']) ? $data['connection'] : null,
			'file'       => isset($data['file']) ? $data['file'] : null,
			'line'       => isset($data['line']) ? $data['line'] : null,
			'trace'      => isset($data['trace']) ? $data['trace'] : null,
			'expiration' => isset($data['expiration']) ? $data['expiration'] : null
		];
	}

	// Add event, takes event name, data, time and additional data - listeners, file (caller file name), line (caller
	// line number), trace (serialized trace)
	public function addEvent($event, $eventData = null, $time = null, $data = [])
	{
		$this->events[] = [
			'event'     => $event,
			'data'      => (new Serializer)->normalize($eventData),
			'time'      => $time,
			'listeners' => isset($data['listeners']) ? $data['listeners'] : null,
			'file'      => isset($data['file']) ? $data['file'] : null,
			'line'      => isset($data['line']) ? $data['line'] : null,
			'trace'     => isset($data['trace']) ? $data['trace'] : null
		];
	}

	// Add route, takes method, uri, action and additional data - name, middleware, before (before filters), after
	// (after filters)
	public function addRoute($method, $uri, $action, $data = [])
	{
		$this->routes[] = [
			'method'     => $method,
			'uri'        => $uri,
			'action'     => $action,
			'name'       => isset($data['name']) ? $data['name'] : null,
			'middleware' => isset($data['middleware']) ? $data['middleware'] : null,
			'before'     => isset($data['before']) ? $data['before'] : null,
			'after'      => isset($data['after']) ? $data['after'] : null
		];
	}

	// Add route, takes method, uri, action and additional data - name, middleware, before (before filters), after
	// (after filters)
	public function addEmail($subject, $to, $from = null, $headers = [])
	{
		$this->emailsData[] = [
			'data' => [
				'subject' => $subject,
				'to'      => $to,
				'from'    => $from,
				'headers' => (new Serializer)->normalize($headers)
			]
		];
	}

	// Add view, takes view name and data
	public function addView($name, $data = [])
	{
		$this->viewsData[] = [
			'data' => [
				'name' => $name,
				'data' => (new Serializer)->normalize($data)
			]
		];
	}

	// Add executed subrequest, takes the requested url, suvrequest Clockwork ID and additional data - path if non-default
	public function addSubrequest($url, $id, $data = [])
	{
		$this->subrequests[] = [
			'url'  => $url,
			'id'   => $id,
			'path' => isset($data['path']) ? $data['path'] : null
		];
	}

	public function setAuthenticatedUser($username, $id = null, $data = [])
	{
		$this->authenticatedUser = [
			'id'       => $id,
			'username' => $username,
			'email'    => isset($data['email']) ? $data['email'] : null,
			'name'     => isset($data['name']) ? $data['name'] : null
		];
	}

	// Add custom user data (presented as additional tabs in the official app)
	public function userData($key = null)
	{
		if ($key && isset($this->userData[$key])) {
			return $this->userData[$key];
		}

		$userData = (new UserData)->title($key);

		return $key ? $this->userData[$key] = $userData : $this->userData[] = $userData;
	}

	/**
	 * Generate unique request ID in form <current time>-<random number>
	 */
	protected function generateRequestId()
	{
		return str_replace('.', '-', sprintf('%.4F', microtime(true))) . '-' . mt_rand();
	}
}
