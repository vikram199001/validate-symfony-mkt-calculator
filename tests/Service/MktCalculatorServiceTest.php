<?php

namespace App\Tests\Service;

use App\Service\MktCalculatorService;
use PHPUnit\Framework\TestCase;

class MktCalculatorServiceTest extends TestCase
{
    private MktCalculatorService $mktCalculator;

    protected function setUp(): void
    {
        $this->mktCalculator = new MktCalculatorService();
    }

    public function testCalculateMktFromConstantTemperature(): void
    {
        // Test with constant temperature - MKT should equal the temperature
        $temperatures = [25.0, 25.0, 25.0, 25.0, 25.0];
        $mkt = $this->mktCalculator->calculateMktFromArray($temperatures);

        $this->assertEqualsWithDelta(25.0, $mkt, 0.1, 'MKT for constant temperature should equal the temperature');
    }

    public function testCalculateMktFromVariableTemperatures(): void
    {
        // Test with variable temperatures - MKT should be higher than arithmetic mean due to exponential weighting
        $temperatures = [20.0, 25.0, 30.0];
        $mkt = $this->mktCalculator->calculateMktFromArray($temperatures);
        $arithmeticMean = array_sum($temperatures) / count($temperatures); // 25.0

        $this->assertGreaterThan($arithmeticMean, $mkt, 'MKT should be higher than arithmetic mean for variable temperatures');
        $this->assertLessThan(30.0, $mkt, 'MKT should be less than maximum temperature');
    }

    public function testCalculateMktWithTemperatureSpike(): void
    {
        // Test how temperature spikes affect MKT disproportionately
        $temperaturesWithoutSpike = [25.0, 25.0, 25.0, 25.0, 25.0];
        $temperaturesWithSpike = [25.0, 25.0, 40.0, 25.0, 25.0];

        $mktWithoutSpike = $this->mktCalculator->calculateMktFromArray($temperaturesWithoutSpike);
        $mktWithSpike = $this->mktCalculator->calculateMktFromArray($temperaturesWithSpike);

        $this->assertGreaterThan($mktWithoutSpike, $mktWithSpike, 'Temperature spike should significantly increase MKT');
        $this->assertGreaterThan(27.0, $mktWithSpike, 'MKT with spike should be noticeably higher than base temperature');
    }

    public function testCalculateMktWithCustomActivationEnergy(): void
    {
        $temperatures = [20.0, 25.0, 30.0];

        $mktStandard = $this->mktCalculator->calculateMktFromArray($temperatures, 83.144);
        $mktHigher = $this->mktCalculator->calculateMktFromArray($temperatures, 100.0);
        $mktLower = $this->mktCalculator->calculateMktFromArray($temperatures, 60.0);

        // Different activation energies should produce different MKT values
        $this->assertNotEquals($mktStandard, $mktHigher, 'Different activation energies should produce different MKT values');
        $this->assertNotEquals($mktStandard, $mktLower, 'Different activation energies should produce different MKT values');
        $this->assertGreaterThan(20.0, $mktStandard, 'MKT should be greater than minimum temperature');
        $this->assertLessThan(35.0, $mktStandard, 'MKT should be less than a reasonable upper bound');
    }

    public function testValidateTemperature(): void
    {
        $this->assertTrue($this->mktCalculator->isValidTemperature(25.0), 'Normal temperature should be valid');
        $this->assertTrue($this->mktCalculator->isValidTemperature(-200.0), 'Cold temperature should be valid');
        $this->assertFalse($this->mktCalculator->isValidTemperature(-300.0), 'Temperature below absolute zero should be invalid');
    }

    public function testCalculateMktFromEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Temperature array cannot be empty');

        $this->mktCalculator->calculateMktFromArray([]);
    }

    public function testMktAccuracy(): void
    {
        // Test against known MKT calculation
        // For temperatures [15, 20, 25, 30, 35] with standard activation energy
        $temperatures = [15.0, 20.0, 25.0, 30.0, 35.0];
        $mkt = $this->mktCalculator->calculateMktFromArray($temperatures);

        // The MKT should be greater than the arithmetic mean (25°C) due to exponential weighting
        $arithmeticMean = array_sum($temperatures) / count($temperatures); // 25.0
        $this->assertGreaterThan($arithmeticMean, $mkt, 'MKT should be higher than arithmetic mean');
        $this->assertLessThan(35.0, $mkt, 'MKT should be less than maximum temperature');
        $this->assertGreaterThan(25.0, $mkt, 'MKT should be greater than arithmetic mean for these temperatures');
    }

    public function testTemperatureConversion(): void
    {
        // Test with temperatures that need proper Kelvin conversion
        $temperatures = [0.0, 100.0]; // Freezing and boiling point of water
        $mkt = $this->mktCalculator->calculateMktFromArray($temperatures);

        $this->assertGreaterThan(50.0, $mkt, 'MKT should be greater than arithmetic mean due to exponential weighting');
        $this->assertLessThan(100.0, $mkt, 'MKT should be less than maximum temperature');
    }

    public function testMktPrecision(): void
    {
        // Test precision with very similar temperatures
        $temperatures = [24.9, 25.0, 25.1];
        $mkt = $this->mktCalculator->calculateMktFromArray($temperatures);

        $this->assertEqualsWithDelta(25.0, $mkt, 0.1, 'MKT for very similar temperatures should be close to their average');
    }
}
