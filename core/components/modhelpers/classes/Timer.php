<?php

namespace modHelpers;


class Timer
{
    /** @var array */
    protected $format;
    /** @var array */
    protected $times = [];

    public function __construct()
    {
        $this->format = [
            'checkpoint' => "%.3f: %s.",
            'total' => "%.3f: %s.",
            'eol' => "\n",
        ];
    }

    /**
     * Get or set format templates.
     * @param array $data
     * @return array|$this
     */
    public function format($data = null)
    {
        if (array_notempty($data)) {
            $this->format = array_merge($this->format, $data);
            return $this;
        }
        return is_string($data) ? @$this->format[$data] : $this->format;
    }

    /**
     * Time checkpoint.
     * @param string $message
     * @return $this
     */
    public function checkpoint($message = 'Start')
    {
        $this->times[] = [
            'time' => microtime(true),
            'message' => $message,
        ];
        return $this;
    }

    public function cp($message = 'Start')
    {
        return $this->checkpoint($message);
    }

    public function log($message = 'Start')
    {
        return $this->checkpoint($message);
    }

    /**
     * Gets formatted time log.
     * @param string $message
     * @return string
     */
    public function total($message = 'Total time')
    {
        if ($this->isEmpty()) {
            return '';
        }
//        $total = $this->totalSec();
        $output = '';
        $diff = $prevTime = 0;
        foreach ($this->times as $key => $item) {
            if ($key > 0) {
                $diff = $item['time'] - $prevTime;
            }
            $prevTime = $item['time'];
            $output .= $this->formatString('checkpoint', $diff, $item['message']);
        }
        $output .= $this->formatString('total', $this->totalSec(), $message);
        return $output;
    }

    /**
     * Gets total difference in seconds.
     * @return mixed
     */
    public function totalSec()
    {
        return $this->isEmpty() ? 0 : end($this->times)['time'] - $this->times[0]['time'];
    }

    /**
     * Checks if the time log contains any checkpoints.
     * @return bool
     */
    public function isStarted()
    {
        return !empty($this->times);
    }

    /**
     * Checks if the time log contains any checkpoints.
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->times);
    }

    /**
     * Reset the time log.
     * @return $this
     */
    public function reset()
    {
        $this->times = [];
        return $this;
    }

    /**
     * @param $key
     * @param $time
     * @param $message
     * @return string
     */
    protected function formatString($key, $time, $message)
    {
        return empty($this->format[$key]) ? '' : (sprintf($this->format[$key], $time, $message) . $this->format['eol']);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->total();
    }
}