<?php

namespace App\DTO\OpenMeteo;

/**
 * Options for Open-Meteo forecast API requests.
 */
class ForecastOptions
{
    private ?array $elevation = null;
    private array $hourly = [];
    private array $daily = [];
    private array $current = [];
    private string $temperatureUnit = 'celsius';
    private string $windSpeedUnit = 'kmh';
    private string $precipitationUnit = 'mm';
    private string $timeformat = 'iso8601';
    private ?string $timezone = null;
    private ?int $pastDays = null;
    private ?int $forecastDays = null;
    private ?int $forecastHours = null;
    private ?int $forecastMinutely15 = null;
    private ?int $pastHours = null;
    private ?int $pastMinutely15 = null;
    private ?string $startDate = null;
    private ?string $endDate = null;
    private ?string $startHour = null;
    private ?string $endHour = null;
    private ?string $startMinutely15 = null;
    private ?string $endMinutely15 = null;
    private array $models = [];
    private string $cellSelection = 'land';

    /**
     * Set elevation in meters for statistical downscaling.
     */
    public function setElevation(float|array $elevation): self
    {
        $this->elevation = is_array($elevation) ? $elevation : [$elevation];
        return $this;
    }

    /**
     * Set hourly weather variables.
     *
     * @param array $variables E.g., ['temperature_2m', 'precipitation', 'wind_speed_10m']
     */
    public function setHourly(array $variables): self
    {
        $this->hourly = $variables;
        return $this;
    }

    /**
     * Set daily weather variable aggregations.
     *
     * @param array $variables E.g., ['temperature_2m_max', 'temperature_2m_min', 'precipitation_sum']
     */
    public function setDaily(array $variables): self
    {
        $this->daily = $variables;
        return $this;
    }

    /**
     * Set current weather conditions.
     *
     * @param array $variables E.g., ['temperature_2m', 'wind_speed_10m']
     */
    public function setCurrent(array $variables): self
    {
        $this->current = $variables;
        return $this;
    }

    /**
     * Set the temperature unit.
     *
     * @param string $unit 'celsius' or 'fahrenheit'
     */
    public function setTemperatureUnit(string $unit): self
    {
        $this->temperatureUnit = $unit;
        return $this;
    }

    /**
     * Set the wind speed unit.
     *
     * @param string $unit 'kmh', 'ms', 'mph', or 'kn'
     */
    public function setWindSpeedUnit(string $unit): self
    {
        $this->windSpeedUnit = $unit;
        return $this;
    }

    /**
     * Set a precipitation unit.
     *
     * @param string $unit 'mm' or 'inch'
     */
    public function setPrecipitationUnit(string $unit): self
    {
        $this->precipitationUnit = $unit;
        return $this;
    }

    /**
     * Set time format.
     *
     * @param string $format 'iso8601' or 'unixtime'
     */
    public function setTimeformat(string $format): self
    {
        $this->timeformat = $format;
        return $this;
    }

    /**
     * Set a timezone for timestamps.
     *
     * @param string $timezone Timezone name (e.g., 'Europe/Berlin', 'America/New_York') or 'auto'
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Include past days data.
     *
     * @param int $days Number of past days (0-92)
     */
    public function setPastDays(int $days): self
    {
        $this->pastDays = $days;
        return $this;
    }

    /**
     * Set the number of forecast days.
     *
     * @param int $days Number of forecast days (0-16)
     */
    public function setForecastDays(int $days): self
    {
        $this->forecastDays = $days;
        return $this;
    }

    /**
     * Set the number of forecast hours.
     */
    public function setForecastHours(int $hours): self
    {
        $this->forecastHours = $hours;
        return $this;
    }

    /**
     * Set the number of 15-minute forecast steps.
     */
    public function setForecastMinutely15(int $steps): self
    {
        $this->forecastMinutely15 = $steps;
        return $this;
    }

    /**
     * Set the number of past hours.
     */
    public function setPastHours(int $hours): self
    {
        $this->pastHours = $hours;
        return $this;
    }

    /**
     * Set the number of past 15-minute steps.
     */
    public function setPastMinutely15(int $steps): self
    {
        $this->pastMinutely15 = $steps;
        return $this;
    }

    /**
     * Set a start date for a time interval.
     *
     * @param string $date Format: yyyy-mm-dd
     */
    public function setStartDate(string $date): self
    {
        $this->startDate = $date;
        return $this;
    }

    /**
     * Set an end date for a time interval.
     *
     * @param string $date Format: yyyy-mm-dd
     */
    public function setEndDate(string $date): self
    {
        $this->endDate = $date;
        return $this;
    }

    /**
     * Set the start hour for a time interval.
     *
     * @param string $hour Format: yyyy-mm-ddThh:mm
     */
    public function setStartHour(string $hour): self
    {
        $this->startHour = $hour;
        return $this;
    }

    /**
     * Set end hour for a time interval.
     *
     * @param string $hour Format: yyyy-mm-ddThh:mm
     */
    public function setEndHour(string $hour): self
    {
        $this->endHour = $hour;
        return $this;
    }

    /**
     * Set start 15-minute interval.
     *
     * @param string $time Format: yyyy-mm-ddThh:mm
     */
    public function setStartMinutely15(string $time): self
    {
        $this->startMinutely15 = $time;
        return $this;
    }

    /**
     * Set end 15-minute interval.
     *
     * @param string $time Format: yyyy-mm-ddThh:mm
     */
    public function setEndMinutely15(string $time): self
    {
        $this->endMinutely15 = $time;
        return $this;
    }

    /**
     * Set weather models to use.
     *
     * @param array $models Array of model names
     */
    public function setModels(array $models): self
    {
        $this->models = $models;
        return $this;
    }

    /**
     * Set grid-cell selection preference.
     *
     * @param string $selection 'land', 'sea', or 'nearest'
     */
    public function setCellSelection(string $selection): self
    {
        $this->cellSelection = $selection;
        return $this;
    }

    /**
     * Convert options to array format for an API request.
     */
    public function toArray(): array
    {
        $options = [];

        if ($this->elevation !== null) {
            $options['elevation'] = $this->elevation;
        }
        if (!empty($this->hourly)) {
            $options['hourly'] = $this->hourly;
        }
        if (!empty($this->daily)) {
            $options['daily'] = $this->daily;
        }
        if (!empty($this->current)) {
            $options['current'] = $this->current;
        }
        if ($this->temperatureUnit !== 'celsius') {
            $options['temperature_unit'] = $this->temperatureUnit;
        }
        if ($this->windSpeedUnit !== 'kmh') {
            $options['wind_speed_unit'] = $this->windSpeedUnit;
        }
        if ($this->precipitationUnit !== 'mm') {
            $options['precipitation_unit'] = $this->precipitationUnit;
        }
        if ($this->timeformat !== 'iso8601') {
            $options['timeformat'] = $this->timeformat;
        }
        if ($this->timezone !== null) {
            $options['timezone'] = $this->timezone;
        }
        if ($this->pastDays !== null) {
            $options['past_days'] = $this->pastDays;
        }
        if ($this->forecastDays !== null) {
            $options['forecast_days'] = $this->forecastDays;
        }
        if ($this->forecastHours !== null) {
            $options['forecast_hours'] = $this->forecastHours;
        }
        if ($this->forecastMinutely15 !== null) {
            $options['forecast_minutely_15'] = $this->forecastMinutely15;
        }
        if ($this->pastHours !== null) {
            $options['past_hours'] = $this->pastHours;
        }
        if ($this->pastMinutely15 !== null) {
            $options['past_minutely_15'] = $this->pastMinutely15;
        }
        if ($this->startDate !== null) {
            $options['start_date'] = $this->startDate;
        }
        if ($this->endDate !== null) {
            $options['end_date'] = $this->endDate;
        }
        if ($this->startHour !== null) {
            $options['start_hour'] = $this->startHour;
        }
        if ($this->endHour !== null) {
            $options['end_hour'] = $this->endHour;
        }
        if ($this->startMinutely15 !== null) {
            $options['start_minutely_15'] = $this->startMinutely15;
        }
        if ($this->endMinutely15 !== null) {
            $options['end_minutely_15'] = $this->endMinutely15;
        }
        if (!empty($this->models)) {
            $options['models'] = $this->models;
        }
        if ($this->cellSelection !== 'land') {
            $options['cell_selection'] = $this->cellSelection;
        }

        return $options;
    }
}
