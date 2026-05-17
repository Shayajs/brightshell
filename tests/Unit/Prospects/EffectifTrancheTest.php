<?php

declare(strict_types=1);

namespace Tests\Unit\Prospects;

use App\Services\Prospects\EffectifTranche;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EffectifTrancheTest extends TestCase
{
    #[Test]
    public function exceeds_detects_geants(): void
    {
        $this->assertFalse(EffectifTranche::exceeds('12', '12'));
        $this->assertFalse(EffectifTranche::exceeds('11', '12'));
        $this->assertTrue(EffectifTranche::exceeds('21', '12'));
        $this->assertTrue(EffectifTranche::exceeds('53', '12'));
    }

    #[Test]
    public function between_limits_pme_band(): void
    {
        $this->assertTrue(EffectifTranche::between('12', '12', '12'));
        $this->assertFalse(EffectifTranche::between('21', '12', '12'));
        $this->assertTrue(EffectifTranche::between('03', '01', '12'));
    }
}
