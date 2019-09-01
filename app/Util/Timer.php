<?php
declare(strict_types=1);

namespace App\Util;


/**
 * Class Timer
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   4/08/19 14:22
 * @package App\Util
 */
final class Timer
{

    public static $instance = null;
    static private $open_starts, $finished_timers, $end_without_start, $last_started_timer;

    protected function __construct(){
    }
    private function __clone(){}
    private function __wakeup(){}

    public static function getInstance()
    {
        if ( is_null(self::$instance) )
            self::$instance = new Timer();

        return self::$instance;
    }

    public function isStarted($timer_name) {
        return isset(self::$open_starts[$timer_name]);
    }

    public function start($timer_name = '') {
        self::$open_starts[$timer_name][] = microtime(true);
        if (!isset(self::$finished_timers[$timer_name])) {
            self::$finished_timers[$timer_name] = array();
        }
        self::$last_started_timer[] = $timer_name;
    }

    public function end($timer_name = null) {
        $timer_name = ($timer_name ? $timer_name : @array_shift(self::$last_started_timer));
        $start = @array_shift(self::$open_starts[$timer_name ? $timer_name : '']);
        if (!$start) {
            self::$end_without_start[$timer_name][] = 1;
        } else {
            $now = microtime(true);
            self::$finished_timers[$timer_name][] = $now - $start;
        }
        if (!@self::$finished_timers[$timer_name]) {
            self::$finished_timers[$timer_name] = array();
        }
    }

    public function results($verbose = false) {
        if ($verbose) {
            $return = self::$finished_timers;
        } else {
            $return = array();
            foreach (self::$finished_timers as $timer_name => $array) {
                if ($array) {
                    if (count($array) > 1) {
                        $sum = array_sum($array);
                        $count = count($array);

                        $return[$timer_name]['average'] = $sum / $count;
                        $return[$timer_name]['sum'] = $sum;
                        $return[$timer_name]['min'] = min($array);
                        $return[$timer_name]['max'] = max($array);
                        $return[$timer_name]['count'] = $count;
                    } else {
                        $return[$timer_name] = $array[0];
                    }
                }
            }
        }
        if (self::$end_without_start) {
            foreach (self::$end_without_start as $timer_name => $end_array) {
                $count = count($end_array);
                if ($verbose) {
                    $return[$timer_name][] = 'end() called without matching start()' . ($count > 1 ? ' (' . $count . ' times)' : '');
                } else {
                    if (!isset($return[$timer_name]['error'])) {
                        $return[$timer_name] = array();
                    }
                    $return[$timer_name]['error'][] = 'end() called without matching start()' . ($count > 1 ? ' (' . $count . ' times)' : '');
                }
            }
        }
        if (self::$open_starts) {
            foreach (self::$open_starts as $timer_name => $array) {
                $count = count($array);
                if ($count) {
                    if ($verbose) {
                        $return[$timer_name][] = 'start() called without matching end()' . ($count > 1 ? ' (' . $count . ' times)' : '');
                    } else {
                        if (!isset($return[$timer_name]['error'])) {
                            $return[$timer_name] = array();
                        }
                        $return[$timer_name]['error'][] = 'start() called without matching end()' . ($count > 1 ? ' (' . $count . ' times)' : '');
                    }
                }
            }
        }
        return $return;
    }
}