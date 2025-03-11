<?php

namespace Cchhay\LaravelSpellnumber;

use Cchhay\LaravelSpellnumber\Exceptions\SpellnumberException;

class Spellnumber
{
    protected $language;
    protected $currency;
    protected $positiveDigits = 18;
    protected $negativeDigits = 2;
    protected $letterCase = 'lower';
    protected $translate;

    public function __construct($config = [])
    {
        if ($config != null) {
            if (!$config['language']) {
                throw new SpellnumberException("Spellnumber's language are not set.");
            }
            if (!$config['positive_digits']) {
                throw new SpellnumberException("Spellnumber's positive_limit are not set.");
            }
            if (!$config['negative_digits']) {
                throw new SpellnumberException("Spellnumber's negative_limit are not set.");
            }

            $this->language = $config['language'];
            $this->currency = $config['currency'];
            $this->letterCase = $config['letter_case'];
            $this->positiveDigits = $config['positive_digits'];
            $this->negativeDigits = $config['negative_digits'];
            $this->translate = config("spellnumber.$this->language");
        }
    }

    public function spell($number)
    {
        $textDollar = '';
        $textCent = '';
        $textCantRead = $this->translate['cantread'];
        $textZero = $this->translate['zero'];
        $textNegative = '';
        $and = $this->translate['and'];
        $isUSD = $this->currency == 'usd';
        $isCurrency = !!$this->currency;
        $numberInLetter = '';

        # case not a number
        if (!is_numeric($number)) {
            return $textCantRead;
        }

        # case negative number, convert to positive
        if ($number < 0) {
            $number = abs($number);
            $textNegative = $this->translate['negative'];
        }

        # case number is too long than limit digits
        if ($this->getPositivePartLength($number) > $this->positiveDigits) {
            return $textCantRead;
        }

        # change cent and dollar to plural if it greater than 1
        $textCent = $this->getPlural('cent', $this->getNegativePartNumber($number));
        $textDollar = $this->getPlural('dollar', $this->getPositivePartNumber($number));

        # case zero number
        if ($number == 0) {
            return "$textZero $textDollar";
        }

        # read positive part
        $readPositive = $this->readPositivePart($number);
        # read negative part
        $readNegative = $this->readNegativePart($number);

        # case currency is USD
        if ($isCurrency && $isUSD) {
            $readNegative = $readNegative ? "$readNegative $textCent" : '';
            $readPositive = "$readPositive $textDollar";
        }

        # case currency is not USD
        if ($isCurrency && !$isUSD) {
            $readNegative = $readNegative ? "$readNegative $textDollar" : '';
            $readPositive = "$readPositive";
        }

        # case negative number
        if ($textNegative) {
            $numberInLetter = $readNegative ? "$textNegative $readPositive $and $readNegative" : "$textNegative $readPositive";
        }

        # case positive number
        if (!$textNegative) {
            $numberInLetter = $readNegative ? "$readPositive $and $readNegative" : $readPositive;
        }

        if (strtolower($this->letterCase) == 'upper') {
            $numberInLetter = strtoupper($numberInLetter);
        }

        if (strtolower($this->letterCase) == 'title') {
            $numberInLetter = ucwords($numberInLetter);
        }

        if (strtolower($this->letterCase) == 'sentence') {
            $numberInLetter = ucfirst($numberInLetter);
        }

        if (strtolower($this->letterCase) == 'lower') {
            $numberInLetter = strtolower($numberInLetter);
        }

        return $numberInLetter;
    }

    private function getPositivePartLength($number)
    {
        return strlen(substr($number, 0, strpos($number, '.')));
    }

    private function getPlural($key, $count)
    {
        $supportCurrency = $this->currency ? $this->translate['support_currency'][$this->currency] : [];

        if (count($supportCurrency) <= 0) {
            return $key;
        }

        if ($count == 1) {
            return $supportCurrency[$key][0];
        }

        return $supportCurrency[$key][1];
    }

    private function getNegativePartNumber($number)
    {
        return substr($number, strpos($number, '.') + 1);
    }

    private function getPositivePartNumber($number)
    {
        return substr($number, 0, strpos($number, '.'));
    }

    private function readPositivePart($number)
    {
        $number = explode('.', $number)[0];

        # case not positive number or small or equal to zero
        if (!$number) {
            return $this->translate['zero'];
        }

        return $this->read($number);
    }

    private function readNegativePart($number)
    {
        # read negative part with the limit decimal digits base on config
        $number = number_format($number, $this->negativeDigits, '.', '');
        $number = explode('.', $number)[1];
        $number = intval($number);

        if (!$number) {
            return '';
        }

        return $this->read($number);
    }

    private function read($n)
    {
        $numberInLetter = '';
        $thousand = $this->translate['thousand'];
        $thousandCount = 0;
        $sp = $this->translate['space'];

        # case not a number
        if (!is_numeric($n)) {
            return $this->translate['nan'];
        }

        if (strlen($n) == 1) {
            return $this->readOneDigit($n);
        }

        if (strlen($n) == 2) {
            return $this->readTwoDigit($n);
        }

        if (strlen($n) == 3) {
            return $this->readThreeDigit($n);
        }

        while ($n != '') {

            # read 3 digits on the right
            $readThreeDigitOnTheRight = $this->readThreeDigit(substr($n, -3));

            if ($readThreeDigitOnTheRight) {
                # case doesn't have thousand
                $thousandText = isset($thousand[$thousandCount]) ? $thousand[$thousandCount] : '';
                $numberInLetter = $readThreeDigitOnTheRight . $sp . $thousandText . $sp . $numberInLetter;
            }

            # remove 3 digits on the right until no digit left
            $n = strlen($n) > 3 ? substr($n, 0, (strlen($n) - 3)) : '';
            # increase thousand count
            $thousandCount = $thousandCount + 1;
        }

        return $numberInLetter;
    }

    private function readThreeDigit($n)
    {
        $hundred = $this->translate['hundred'];
        $sp = $this->translate['space'];

        # case small than 100
        if ($n < 100) {
            return $this->readTwoDigit($n);
        }

        # case equal 100
        if ($n === 100) {
            return $this->translate[1] . $sp . $hundred;
        }

        $readLessThanHundred = $this->readTwoDigit($n % 100);
        $readHundred = $this->readOneDigit(intval($n / 100)) . $sp . $hundred;

        return $readHundred . $sp . $readLessThanHundred;
    }

    private function readTwoDigit($n)
    {
        if ($n <= 20) {
            return $this->readOneDigit($n);
        }

        # case bigger than 20
        $firstDigit = $n % 10;
        $readFirstDigit = $this->readOneDigit($firstDigit);

        # case 30, 40, 50, 60, 70, 80, 90
        $readSecondDigit = $this->translate[$n - $firstDigit];

        return $readFirstDigit ? "$readSecondDigit{$this->translate['dash']}$readFirstDigit" : $readSecondDigit;
    }

    private function readOneDigit($n)
    {
        # case number equal to zero or not a number
        if (!$n) {
            return '';
        }

        return $this->translate[intval($n)];
    }
}
