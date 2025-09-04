<?php

namespace Awtechs\DataSource\Exceptions;

use RuntimeException;

class ModelNotResolvableException extends RuntimeException
{
    public static function for(string $repositoryClass, string $hint = ''): self
    {
        $message = "[$repositoryClass] could not resolve an Eloquent model class. ".
                   "Either import the model with a top-level `use`, name the repo like {Model}Repository, ".
                   "place the model in App\\Models, or implement a static model(): string method.";
        if ($hint !== '') {
            $message .= " Hint: $hint";
        }
        return new self($message);
    }
}
