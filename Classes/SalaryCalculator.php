<?php

Class SalaryCalculator
{
    /**
     * calculates take home wages based on a set of given inputs.
     * inputs can be based on 3 set lengths of time
     * week, month or year.
     *
     * when using $overtime a key value array should be used with the key as
     * the increase in salary and the value as the amount of hours in the given
     * time frame
     *
     * using the construct:
     * the construct should be passed with 5 arguments in order
     *
     * base rate:
     *      a float of your basic pay per hour.
     *
     * base hours:
     *      the number of hours you complete withing the period set in the 5th argument
     *
     * over time
     *      an array of key value pairs :
     *          key - a sting form of the float multiplier gained from doing overtime
     *                for example "1.3"
     *
     *          value - a float of the number of hours performed at that multiplier in the
     *                  given time period of argument 5
     *
     *          example - ["1.3" => 5, "2" => 2]
     *
     *      if no overtime is performed insert an empty array
     *
     * pension rate
     *      an integer representing the percent of pension you pay for example 2% would become 2
     *
     * time span
     *      a string wit one of three values based on the time span your are calculating from
     *      this will also determine the output of the calculator
     *      three options :
     *          "week"
     *          "month"
     *          "year"
     */

    protected $base_rate, $base_hours, $over_time, $pension_rate, $time_span;

    public $basic, $overtime, $pension_reduction, $tax, $ni, $reduction, $take_home;

    /**
     * @param $base_rate
     * @param $base_hours
     * @param array $over_time ["rate" => int hours]
     * @param $pension_rate
     * @param $time_span
     */
    public function __construct($base_rate, $base_hours, $over_time, $pension_rate, $time_span)
    {
        $this->base_rate     = $base_rate;
        $this->base_hours    = $base_hours;
        $this->over_time     = $over_time;
        $this->pension_rate  = $pension_rate;
        $this->time_span     = $time_span;
    }

    /**
     * @return float
     */
    public function calculate()
    {

        $this->basic = $this->base_hours * $this->base_rate;

        $this->overtime = 0;
        foreach ($this->over_time as $rate => $hours) {
            $earned = floatval($rate) * $this->base_rate * $hours;
            $this->overtime += $earned;
        }

        $before_tax = $this->basic += $this->overtime;

        switch ($this->time_span) {
            case "week" :
                $multiplier = 52;
                break;
            case "month":
                $multiplier = 12;
                break;
            case "year" :
                $multiplier = 1;
                break;
            default :
                return "Please specify time span";
        }


        $before_tax *= $multiplier;
        $after_pension = $before_tax - $this->pension($before_tax);
        $after_tax = $after_pension - $this->taxReduction($after_pension);
        $after_ni = $after_tax - $this->nationalInsurance($after_pension);

        $this->pension_reduction = $this->pension($before_tax)  / $multiplier;
        $this->tax =$this->taxReduction($after_pension) / $multiplier;
        $this->ni = $this->nationalInsurance($after_pension) / $multiplier;
        $this->reduction = $this->tax + $this->ni;

        $this->take_home = $after_ni / $multiplier;
        return [
            "basic"     => $this->basic,
            "overtime"  => $this->overtime,
            "pension_reduction"   => $this->pension_reduction,
            "tax"       => $this->tax,
            "ni"        => $this->ni,
            "reduction" => $this->reduction,
            "take_home" => $this->take_home
        ];
    }

    /**
     * @param int $income
     * @return int
     */
    public function taxReduction($income)
    {
        $tax = 0;
        if ($income < 11000) {
            return 0;
        } elseif ($income > 11000 && $income <= 43000) {
            $taxable = $income - 11000;
            $tax     = $taxable * 0.2;
        } elseif ($income > 43000 && $income <= 150000) {
            $taxable = $income - 43000;
            $tax     = $taxable * 0.4;
            $tax     += 6400;
        } elseif ($income > 15000) {
            $taxable = $income - 15000;
            $tax     = $taxable * 0.45;
            $tax     += 6400;
            $tax     += 428000;
        }
        return $tax;
    }

    /**
     * @param  int $income
     * @return int
     */
    public function nationalInsurance($income)
    {
        if ($income > 8060 && $income < 43000) {
            $reduce_from = $income - 8060;
            $reduction   = $reduce_from * 0.12;
        } elseif ($income > 43000) {
            $reduce_from = $income - 43000;
            $reduction   = $reduce_from * 0.02;
            $reduction   += 8060 * 0.12;
        } else {
            $reduction = 0;
        }

        return $reduction;
    }

    /**
     * @param int $income
     * @return float
     */
    public function pension($income)
    {
        $pension_rate = $this->pension_rate / 100;
        $reduction = $pension_rate * $income;
        return $reduction;
    }
}