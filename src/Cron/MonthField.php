<?php
namespace EasyTask\Cron;

/**
 * Class MonthField
 * @package EasyTask\Cron
 */
class MonthField extends AbstractField
{
    /**
     * {@inheritdoc}
     */
    protected $rangeStart = 1;

    /**
     * {@inheritdoc}
     */
    protected $rangeEnd = 12;

    /**
     * {@inheritdoc}
     */
    protected $literals = [1 => 'JAN', 2 => 'FEB', 3 => 'MAR', 4 => 'APR', 5 => 'MAY', 6 => 'JUN', 7 => 'JUL',
        8 => 'AUG', 9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC',];

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy($date, $value)
    {
        if ($value == '?')
        {
            return true;
        }

        $value = $this->convertLiterals($value);

        return $this->isSatisfied((int)$date->format('m'), $value);
    }

    /**
     * @inheritDoc
     *
     * @param \DateTime|\DateTimeImmutable &$date
     */
    public function increment(&$date, $invert = false)
    {
        if ($invert)
        {
            $date = $date->modify('last day of previous month')->setTime(23, 59);
        }
        else
        {
            $date = $date->modify('first day of next month')->setTime(0, 0);
        }

        return $this;
    }
}
