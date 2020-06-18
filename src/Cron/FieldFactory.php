<?php
namespace EasyTask\Cron;

use InvalidArgumentException;

/**
 * Class FieldFactory
 * @package EasyTask\Cron
 */
class FieldFactory implements FieldFactoryInterface
{
    /**
     * @var array Cache of instantiated fields
     */
    private $fields = [];

    /**
     * Get an instance of a field object for a cron expression position.
     *
     * @param int $position CRON expression position value to retrieve
     *
     * @return mixed
     * @throws InvalidArgumentException if a position is not valid
     */
    public function getField($position)
    {
        if (!isset($this->fields[$position]))
        {
            $this->fields[$position] = $this->instantiateField($position);
        }
        return $this->fields[$position];
    }

    private function instantiateField($position)
    {
        switch ($position)
        {
            case CronExpression::MINUTE:
                return new MinutesField();
            case CronExpression::HOUR:
                return new HoursField();
            case CronExpression::DAY:
                return new DayOfMonthField();
            case CronExpression::MONTH:
                return new MonthField();
            case CronExpression::WEEKDAY:
                return new DayOfWeekField();
        }

        throw new InvalidArgumentException(
            ($position + 1) . ' is not a valid position'
        );
    }
}
