<?php

namespace ZanySoft\LaravelExceptionMonitor\Exception;

use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FlattenException
{
    /** @var string */
    private $message;

    /** @var int|string */
    private $code;

    /** @var self|null */
    private $previous;

    /** @var array */
    private $trace;

    /** @var string */
    private $traceAsString;

    /** @var string */
    private $class;

    /** @var int */
    private $statusCode;

    /** @var string */
    private $statusText;

    /** @var array */
    private $headers;

    /** @var string */
    private $file;

    /** @var int */
    private $line;

    /**
     * @param \Exception $exception
     * @param int|null $statusCode
     * @param array $headers
     * @return self
     */
    public static function create($exception, $statusCode = null, array $headers = []): self
    {
        return static::createFromThrowable($exception, $statusCode, $headers);
    }

    /**
     * @param \Exception $exception
     * @param int|null $statusCode
     * @param array $headers
     * @return self
     */
    public static function createFromThrowable($exception, int $statusCode = null, array $headers = []): self
    {
        $e = new static();
        $e->setMessage($exception->getMessage());
        $e->setCode($exception->getCode());

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $headers = array_merge($headers, $exception->getHeaders());
        } elseif ($exception instanceof RequestExceptionInterface) {
            $statusCode = 400;
        }

        if (null === $statusCode) {
            $statusCode = 500;
        }

        if (class_exists(Response::class) && isset(Response::$statusTexts[$statusCode])) {
            $statusText = Response::$statusTexts[$statusCode];
        } else {
            $statusText = 'Whoops, looks like something went wrong.';
        }

        $e->setStatusText($statusText);
        $e->setStatusCode($statusCode);
        $e->setHeaders($headers);
        $e->setTraceFromThrowable($exception);
        $e->setClass(\get_class($exception));
        $e->setFile($exception->getFile());
        $e->setLine($exception->getLine());

        $previous = $exception->getPrevious();

        if ($previous instanceof \Throwable) {
            $e->setPrevious(static::createFromThrowable($previous));
        }

        return $e;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getStatusText(): string
    {
        return $this->statusText;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int|string int most of the time (might be a string with PDOException)
     */
    public function getCode()
    {
        return $this->code;
    }

    public function getPrevious(): ?self
    {
        return $this->previous;
    }

    /**
     * @return self[]
     */
    public function getAllPrevious(): array
    {
        $exceptions = [];
        $e = $this;
        while ($e = $e->getPrevious()) {
            $exceptions[] = $e;
        }

        return $exceptions;
    }

    public function getTrace(): array
    {
        return $this->trace;
    }

    public function getTraceAsString(): string
    {
        return $this->traceAsString;
    }

    public function toString(): string
    {
        $message = '';
        $next = false;

        foreach (array_reverse(array_merge([$this], $this->getAllPrevious())) as $exception) {
            if ($next) {
                $message .= 'Next ';
            } else {
                $next = true;
            }
            $message .= $exception->getClass();

            if ('' != $exception->getMessage()) {
                $message .= ': ' . $exception->getMessage();
            }

            $message .= ' in ' . $exception->getFile() . ':' . $exception->getLine() .
                "\nStack trace:\n" . $exception->getTraceAsString() . "\n\n";
        }

        return rtrim($message);
    }

    public function toArray(): array
    {
        $exceptions = [];
        foreach (array_merge([$this], $this->getAllPrevious()) as $exception) {
            $exceptions[] = [
                'message' => $exception->getMessage(),
                'class' => $exception->getClass(),
                'trace' => $exception->getTrace(),
            ];
        }

        return $exceptions;
    }


    /**
     * @return $this
     */
    private function setStatusCode(int $code): self
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * @return $this
     */
    private function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return $this
     */
    private function setClass(string $class): self
    {
        $this->class = false !== strpos($class, "@anonymous\0") ? (get_parent_class($class) ?: key(class_implements($class)) ?: 'class') . '@anonymous' : $class;

        return $this;
    }

    /**
     * @return $this
     */
    private function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return $this
     */
    private function setLine(int $line): self
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @return $this
     */
    private function setStatusText(string $statusText): self
    {
        $this->statusText = $statusText;

        return $this;
    }

    /**
     * @return $this
     */
    private function setMessage(string $message): self
    {
        if (false !== strpos($message, "@anonymous\0")) {
            $message = preg_replace_callback('/[a-zA-Z_\x7f-\xff][\\\\a-zA-Z0-9_\x7f-\xff]*+@anonymous\x00.*?\.php(?:0x?|:[0-9]++\$)[0-9a-fA-F]++/', function ($m) {
                return class_exists($m[0], false) ? (get_parent_class($m[0]) ?: key(class_implements($m[0])) ?: 'class') . '@anonymous' : $m[0];
            }, $message);
        }

        $this->message = $message;

        return $this;
    }


    /**
     * @param int|string $code
     *
     * @return $this
     */
    private function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return $this
     */
    private function setPrevious(?self $previous): self
    {
        $this->previous = $previous;

        return $this;
    }

    /**
     * @param \Exception $throwable
     * @return self
     */
    private function setTraceFromThrowable($throwable): self
    {
        $this->traceAsString = $throwable->getTraceAsString();

        return $this->setTrace($throwable->getTrace(), $throwable->getFile(), $throwable->getLine());
    }

    /**
     * @return $this
     */
    private function setTrace(array $trace, ?string $file, ?int $line): self
    {
        $this->trace = [];
        $this->trace[] = [
            'namespace' => '',
            'short_class' => '',
            'class' => '',
            'type' => '',
            'function' => null,
            'file' => $file,
            'line' => $line,
            'args' => [],
        ];
        foreach ($trace as $entry) {
            $class = '';
            $namespace = '';
            if (isset($entry['class'])) {
                $parts = explode('\\', $entry['class']);
                $class = array_pop($parts);
                $namespace = implode('\\', $parts);
            }

            $this->trace[] = [
                'namespace' => $namespace,
                'short_class' => $class,
                'class' => $entry['class'] ?? '',
                'type' => $entry['type'] ?? '',
                'function' => $entry['function'] ?? null,
                'file' => $entry['file'] ?? null,
                'line' => $entry['line'] ?? null,
                'args' => isset($entry['args']) ? $this->flattenArgs($entry['args']) : [],
            ];
        }

        return $this;
    }

    public function formatArgs(array $args): string
    {
        $result = [];
        foreach ($args as $key => $item) {
            if ('object' === $item[0]) {
                $formattedValue = sprintf('<em>object</em>(%s)', $item[1]);
            } elseif ('array' === $item[0]) {
                $formattedValue = sprintf('<em>array</em>(%s)', \is_array($item[1]) ? $this->formatArgs($item[1]) : $item[1]);
            } elseif ('null' === $item[0]) {
                $formattedValue = '<em>null</em>';
            } elseif ('boolean' === $item[0]) {
                $formattedValue = '<em>' . strtolower(var_export($item[1], true)) . '</em>';
            } elseif ('resource' === $item[0]) {
                $formattedValue = '<em>resource</em>';
            } else {
                $formattedValue = str_replace("\n", '', $this->escape(var_export($item[1], true)));
            }

            $result[] = \is_int($key) ? $formattedValue : sprintf("'%s' => %s", $this->escape($key), $formattedValue);
        }

        return implode(', ', $result);
    }

    private function escape(string $string): string
    {
        return htmlspecialchars($string, \ENT_COMPAT | \ENT_SUBSTITUTE, 'UTF-8');
    }

    private function flattenArgs(array $args, int $level = 0, int &$count = 0): array
    {
        $result = [];
        foreach ($args as $key => $value) {
            if (++$count > 1e4) {
                return ['array', '*SKIPPED over 10000 entries*'];
            }
            if ($value instanceof \__PHP_Incomplete_Class) {
                $result[$key] = ['incomplete-object', $this->getClassNameFromIncomplete($value)];
            } elseif (\is_object($value)) {
                $result[$key] = ['object', \get_class($value)];
            } elseif (\is_array($value)) {
                if ($level > 10) {
                    $result[$key] = ['array', '*DEEP NESTED ARRAY*'];
                } else {
                    $result[$key] = ['array', $this->flattenArgs($value, $level + 1, $count)];
                }
            } elseif (null === $value) {
                $result[$key] = ['null', null];
            } elseif (\is_bool($value)) {
                $result[$key] = ['boolean', $value];
            } elseif (\is_int($value)) {
                $result[$key] = ['integer', $value];
            } elseif (\is_float($value)) {
                $result[$key] = ['float', $value];
            } elseif (\is_resource($value)) {
                $result[$key] = ['resource', get_resource_type($value)];
            } else {
                $result[$key] = ['string', (string)$value];
            }
        }

        return $result;
    }

    private function getClassNameFromIncomplete(\__PHP_Incomplete_Class $value): string
    {
        $array = new \ArrayObject($value);

        return $array['__PHP_Incomplete_Class_Name'];
    }
}
