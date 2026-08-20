<?php

declare(strict_types=1);

namespace App\Validation;

use CodeIgniter\Validation\ValidationInterface;

class BaseValidator
{
    protected ValidationInterface $validator;

    public function __construct()
    {
        $this->validator = service('validation');
    }

    public function validate(array $data, array $rules, array $messages = []): bool
    {
        $this->validator->reset();
        $this->validator->setRules($rules, $messages);

        return $this->validator->run($data);
    }

    public function getErrors(): array
    {
        return $this->validator->getErrors();
    }

    public function getFirstError(): string
    {
        $errors = $this->getErrors();
        
        if (!empty($errors)) {
            $first = reset($errors);
            if (is_array($first)) {
                return reset($first);
            }
            return $first;
        }

        return '';
    }
}
