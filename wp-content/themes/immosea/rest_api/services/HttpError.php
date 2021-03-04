<?php
class HttpError implements ErrorService
{
    private $statusCode = 200;
    private $message = "OK";

    /**
     * Set the status code of the error
     *
     * @param int $code
     *
     * @return ErrorService
     */
    public function setStatusCode(int $code): ErrorService
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * Set the message of the error
     *
     * @param string $message
     *
     * @return ErrorService
     */
    public function setMessage(string $message): ErrorService
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the status code of the instance
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the message of the instance
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Report status code and the message in a nice JSON format
     * @return array
     */
    public function report(): array
    {
        return array(
            "status"  => $this->statusCode,
            "message" => $this->message
        );
    }
}
