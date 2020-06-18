<?php
namespace EasyTask\Cron;

use DateTime;
use DateTimeInterface;

/**
 * Class DayOfMonthField
 * @package EasyTask\Cron
 */
class DayOfMonthField extends AbstractField
{
    /**
     * {@inheritdoc}
     */
    protected $rangeStart = 1;

    /**
     * {@inheritdoc}
     */
    protected $rangeEnd = 31;

    /**
     * Get the nearest day of the week for a given day in a month.
     *
     * @param int $currentYear Current year
     * @param int $currentMonth Current month
     * @param int $targetDay Target day of the month
     *
     * @return \DateTime Returns the nearest date
     */
    private static function getNearestWeekday($currentYear, $currentMonth, $targetDay)
    {
        $tday = str_pad((string)$targetDay, 2, '0', STR_PAD_LEFT);
        $target = DateTime::createFromFormat('Y-m-d', "${currentYear}-${currentMonth}-${tday}");
        $currentWeekday = (int)$target->format('N');

        if ($currentWeekday < 6)
        {
            return $target;
        }

        $lastDayOfMonth = $target->format('t');
        foreach ([-1, 1, -2, 2] as $i)
        {
            $adjusted = $targetDay + $i;
            if ($adjusted > 0 && $adjusted <= $lastDayOfMonth)
            {
                $target->setDate($currentYear, $currentMonth, $adjusted);

                if ((int)$target->format('N') < 6 && (int)$target->format('m') === $currentMonth)
                {
                    return $target;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy($date, $value)
    {
        // ? states that the field value is to be skipped
        if ('?' === $value)
        {
            return true;
        }

        $fieldValue = $date->format('d');

        // Check to see if this is the last day of the month
        if ('L' === $value)
        {
            return $fieldValue === $date->format('t');
        }

        // Check to see if this is the nearest weekday to a particular value
        if (strpos($value, 'W'))
        {
            // Parse the target day
            $targetDay = (int)substr($value, 0, strpos($value, 'W'));
            // Find out if the current day is the nearest day of the week
            return $date->format('j') === self::getNearestWeekday(
                    (int)$date->format('Y'),
                    (int)$date->format('m'),
                    $targetDay
                )->format('j');
        }

        return $this->isSatisfied((int)$date->format('d'), $value);
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
            $date = $date->modify('previous day')->setTime(23, 59);
        }
        else
        {
            $date = $date->modify('next day')->setTime(0, 0);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value)
    {
        $basicChecks = parent::validate($value);

        // Validate that a list don't have W or L
        if (false !== strpos($value, ',') && (false !== strpos($value, 'W') || false !== strpos($value, 'L')))
        {
            return false;
        }

        if (!$basicChecks)
        {
            if ('L' === $value)
            {
                return true;
            }

            if (preg_match('/^(.*)W$/', $value, $matches))
            {
                return $this->validate($matches[1]);
            }

            return false;
        }

        return $basicChecks;
    }
}
