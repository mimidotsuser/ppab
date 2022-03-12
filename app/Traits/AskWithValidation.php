<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Exception\InvalidArgumentException;

trait AskWithValidation
{


    /**
     * @param string $question
     * @param string $rules
     * @param $attempts
     * @param $default
     * @return mixed
     */
    function askWithValidation(string $question, string $rules, $attempts = 3, $default = null)
    {

        $key = 'answer';

        $value = $this->ask($question, $default);
        $validator = Validator::make([$key => $value], [$key => $rules]);

        if (!$validator->fails()) {
            return $value;
        }

        //if attempts have exhausted, exit
        if ($attempts < 1) {
            throw new InvalidArgumentException('Invalid arguments provided');
        }

        //decrement max attempts
        --$attempts;

        //log errors
        foreach ($validator->errors()->get($key) as $error) {
            $this->error(str_replace('The', '', str_replace($key, '', $error)));
        }

        //re- prompt
        return $this->askWithValidation($question, $rules, $attempts, $default);
    }
}
