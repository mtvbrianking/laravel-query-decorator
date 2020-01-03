<?php

namespace Bmatovu\QueryDecorator\Exceptions;

class InvalidJsonException extends \Exception
{
    /**
     * Json schema validation errors.
     *
     * @var array
     */
    protected $validationErrors;

    /**
     * Constructor.
     *
     * @param array  $validationErrors
     * @param string $message
     */
    public function __construct(array $validationErrors, string $message = 'The given data was invalid.')
    {
        parent::__construct($message);

        $this->validationErrors = $validationErrors;
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
