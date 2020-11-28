<?php

class OLEAutomationDateConverter
{
    /**
     * Get the OLE Automation Date epoch
     *
     * @return DateTimeImmutable
     */
    public static function BaseDate()
    {
        static $baseDate = null;

        if ($baseDate == null) {
            $baseDate = new DateTimeImmutable('1899-12-30 00:00:00');
        }

        return $baseDate;
    }

    /**
     * Convert a DateTime object to a float representing an OLE Automation Date
     *
     * @param DateTimeInterface $dateTime
     * @return float
     */
    public static function DateTimeToOADate(DateTimeInterface $dateTime)
    {
        $interval = self::BaseDate()->diff($dateTime);
        $mSecs = ($interval->h * 3600000)
            + ($interval->i * 60000)
            + ($interval->s * 1000)
            + floor($dateTime->format('u') / 1000);

        return $interval->days + ($mSecs / 86400000);
    }

    /**
     * Convert a float representing an OLE Automation Date to a DateTime object
     *
     * The returned value has a microsecond component, but resolution is millisecond and even
     * this should not be relied upon as it is subject to floating point precision errors
     *
     * @param float $oaDate
     * @return DateTime
     */
    public static function OADateToDateTime($oaDate)
    {
        $days = floor($oaDate);
        $msecsFloat = ($oaDate - $days) * 86400000;
        $msecs = floor($msecsFloat);
        $hours = floor($msecs / 3600000);
        $msecs %= 3600000;
        $mins = floor($msecs / 60000);
        $msecs %= 60000;
        $secs = floor($msecs / 1000);
        $msecs %= 1000;

        $dateTime = self::BaseDate()
            ->add(new DateInterval(sprintf('P%sDT%sH%sM%sS', $days, $hours, $mins, $secs)))
            ->format('Y-m-d H:i:s');

        return new DateTime("$dateTime.$msecs");
    }
}

?>