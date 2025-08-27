<?php

namespace App\Exceptions\Custom;

use App\Exceptions\Handler\PDOExceptionHandler;
use App\Exceptions\Handler\Traits\ExceptionTrait;
use App\Exceptions\Handler\Traits\HandlerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CustomException extends Exception
{
	use PDOExceptionHandler, ExceptionTrait, HandlerTrait;
	
	private int $statusCode;
	
	/**
	 * @param string|null $message
	 * @param int $statusCode
	 * @param \Throwable|null $previous
	 * @param int|null $code
	 */
	public function __construct(string $message = null, int $statusCode = 500, \Throwable $previous = null, ?int $code = 0)
	{
		$this->statusCode = $statusCode;
		parent::__construct($message, $code, $previous);
	}
	
	/**
	 * Report the exception.
	 */
	public function report(): void
	{
		if (appInstallFilesExist()) {
			Log::error($this->getMessage());
		} else {
			// Clear PDO error log during installation
			if ($this->isPDOException($this)) {
				$this->clearLog();
			}
		}
	}
	
	/**
	 * Render the exception into an HTTP response.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
	 */
	public function render(Request $request): Response|JsonResponse
	{
		return $this->responseCustomError($this, $request);
	}
	
	/**
	 * @return int
	 */
	public function getStatusCode(): int
	{
		return $this->statusCode;
	}
}
