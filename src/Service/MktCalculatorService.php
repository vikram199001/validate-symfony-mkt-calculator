<?php

namespace App\Service;

use App\Entity\Dataset;
use App\Entity\TemperatureReading;

class MktCalculatorService
{
    private const DEFAULT_ACTIVATION_ENERGY = 83.144; // kJ/mol
    private const GAS_CONSTANT = 8.314; // J/(mol·K)

    /**
     * Calculate Mean Kinetic Temperature (MKT) for a dataset
     * 
     * Formula: MKT = -ΔH/R × (1/ln(Σ(e^(-ΔH/R × 1/T_i))/n))
     * Where:
     * - ΔH = Activation Energy (kJ/mol)
     * - R = Gas Constant (8.314 J/(mol·K))
     * - T_i = Temperature readings in Kelvin
     * - n = Number of temperature readings
     *
     * @param Dataset $dataset
     * @return float
     */
    public function calculateMkt(Dataset $dataset): float
    {
        $temperatureReadings = $dataset->getTemperatureReadings();

        if ($temperatureReadings->isEmpty()) {
            throw new \InvalidArgumentException('Dataset must have temperature readings to calculate MKT');
        }

        $activationEnergy = (float) $dataset->getActivationEnergy() ?: self::DEFAULT_ACTIVATION_ENERGY;
        $gasConstant = self::GAS_CONSTANT / 1000; // Convert to kJ/(mol·K)

        $sumExponentials = 0.0;
        $count = 0;

        foreach ($temperatureReadings as $reading) {
            $temperatureKelvin = $this->convertCelsiusToKelvin((float) $reading->getTemperature());
            $exponent = - ($activationEnergy / $gasConstant) * (1 / $temperatureKelvin);
            $sumExponentials += exp($exponent);
            $count++;
        }

        if ($count === 0) {
            throw new \InvalidArgumentException('No valid temperature readings found');
        }

        $averageExponential = $sumExponentials / $count;
        $mkt = - ($activationEnergy / $gasConstant) * (1 / log($averageExponential));

        return $this->convertKelvinToCelsius($mkt);
    }

    /**
     * Calculate MKT from array of temperature values
     *
     * @param array $temperatures Array of temperature values in Celsius
     * @param float $activationEnergy Activation energy in kJ/mol
     * @return float MKT value in Celsius
     */
    public function calculateMktFromArray(array $temperatures, float $activationEnergy = self::DEFAULT_ACTIVATION_ENERGY): float
    {
        if (empty($temperatures)) {
            throw new \InvalidArgumentException('Temperature array cannot be empty');
        }

        $gasConstant = self::GAS_CONSTANT / 1000; // Convert to kJ/(mol·K)
        $sumExponentials = 0.0;
        $count = count($temperatures);

        foreach ($temperatures as $temperature) {
            $temperatureKelvin = $this->convertCelsiusToKelvin((float) $temperature);
            $exponent = - ($activationEnergy / $gasConstant) * (1 / $temperatureKelvin);
            $sumExponentials += exp($exponent);
        }

        $averageExponential = $sumExponentials / $count;
        $mkt = - ($activationEnergy / $gasConstant) * (1 / log($averageExponential));

        return $this->convertKelvinToCelsius($mkt);
    }

    /**
     * Calculate statistical information for a dataset
     *
     * @param Dataset $dataset
     * @return array
     */
    public function calculateStatistics(Dataset $dataset): array
    {
        $temperatures = [];
        foreach ($dataset->getTemperatureReadings() as $reading) {
            $temperatures[] = (float) $reading->getTemperature();
        }

        if (empty($temperatures)) {
            return [
                'count' => 0,
                'min' => null,
                'max' => null,
                'average' => null,
                'standardDeviation' => null,
            ];
        }

        $count = count($temperatures);
        $sum = array_sum($temperatures);
        $min = min($temperatures);
        $max = max($temperatures);
        $average = $sum / $count;

        // Calculate standard deviation
        $sumSquares = 0;
        foreach ($temperatures as $temp) {
            $sumSquares += pow($temp - $average, 2);
        }
        $standardDeviation = sqrt($sumSquares / $count);

        return [
            'count' => $count,
            'min' => $min,
            'max' => $max,
            'average' => $average,
            'standardDeviation' => $standardDeviation,
        ];
    }

    /**
     * Convert Celsius to Kelvin
     *
     * @param float $celsius
     * @return float
     */
    private function convertCelsiusToKelvin(float $celsius): float
    {
        return $celsius + 273.15;
    }

    /**
     * Convert Kelvin to Celsius
     *
     * @param float $kelvin
     * @return float
     */
    private function convertKelvinToCelsius(float $kelvin): float
    {
        return $kelvin - 273.15;
    }

    /**
     * Validate temperature reading
     *
     * @param float $temperature Temperature in Celsius
     * @return bool
     */
    public function isValidTemperature(float $temperature): bool
    {
        // Check if temperature is above absolute zero in Celsius (-273.15°C)
        return $temperature > -273.15;
    }
}
