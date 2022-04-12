<?php

if (!function_exists('moneyInWords')) {
    function moneyInWords($amount, $currencyCode = 'usd', $locale = 'en')
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::SPELLOUT);
        $formatter->setTextAttribute(NumberFormatter::DEFAULT_RULESET,
            "%spellout-numbering-verbose"
        );

        $value = $formatter->formatCurrency(+$amount, $currencyCode);

        return ucwords($value);
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $currencyCode = null, $locale = 'en')
    {

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
        $formatter->setSymbol(NumberFormatter::CURRENCY_SYMBOL, '');
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);

        if (isset($currencyCode)) {
            return $formatter->formatCurrency(+$amount, $currencyCode);
        }
        return $formatter->format(+$amount);

    }
}
