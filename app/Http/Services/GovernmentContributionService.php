<?php   

namespace App\Http\Services;
// app/Services/GovernmentContributionService.php

class GovernmentContributionService
{
    // SSS 2024 contribution table (simplified brackets)
    private array $sssBrackets = [
        [4250,  180,  380],
        [6750,  270,  570],
        [9250,  360,  760],
        [11750, 450,  950],
        [14250, 540,  1140],
        [16750, 630,  1330],
        [19250, 720,  1520],
        [21750, 810,  1710],
        [24250, 900,  1900],
        [PHP_INT_MAX, 990, 2090],
    ];

    public function compute(float $basicSalary): array
    {
        // SSS
        [$sssEE, $sssER] = $this->computeSSS($basicSalary);

        // PhilHealth: 5% of salary, split equally, capped at ₱100k MSC
        $philBase = min($basicSalary, 100000);
        $philEE   = round(($philBase * 0.05) / 2, 2);
        $philER   = $philEE;

        // Pag-IBIG: 2% employee, capped at ₱100
        $pagEE = round(min($basicSalary * 0.02, 100), 2);
        $pagER = $pagEE;

        // Withholding tax (TRAIN Law, annualized)
        $tax = $this->computeWithholdingTax($basicSalary, $sssEE, $philEE, $pagEE);

        $totalEE = round($sssEE + $philEE + $pagEE + $tax, 2);

        return [
            'sss_ee'    => $sssEE,   'sss_er'   => $sssER,
            'phil_ee'   => $philEE,  'phil_er'  => $philER,
            'pagibig_ee'=> $pagEE,   'pagibig_er'=> $pagER,
            'tax'       => $tax,
            'total_ee'  => $totalEE,
            'net_pay'   => round($basicSalary - $totalEE, 2),
        ];
    }

    private function computeSSS(float $salary): array
    {
        foreach ($this->sssBrackets as [$cap, $ee, $er]) {
            if ($salary <= $cap) return [$ee, $er];
        }
        return [990, 2090];
    }

    private function computeWithholdingTax(
        float $salary, float $sss, float $phil, float $pagibig
    ): float {
        $taxable = ($salary - $sss - $phil - $pagibig) * 12;

        $annualTax = match(true) {
            $taxable <= 250000  => 0,
            $taxable <= 400000  => ($taxable - 250000)  * 0.20,
            $taxable <= 800000  => 30000  + ($taxable - 400000)  * 0.25,
            $taxable <= 2000000 => 130000 + ($taxable - 800000)  * 0.30,
            default             => 490000 + ($taxable - 2000000) * 0.35,
        };

        return round($annualTax / 12, 2);
    }
}