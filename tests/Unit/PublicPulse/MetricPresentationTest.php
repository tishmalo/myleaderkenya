<?php
namespace Tests\Unit\PublicPulse;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;
class MetricPresentationTest extends TestCase
{
    public function test_sentiment_fractions_render_as_percentages(): void
    {
        $this->assertStringContainsString('20.0%', Blade::render('<x-pulse-percentage :value="0.2" />'));
        $this->assertStringContainsString('10.0%', Blade::render('<x-pulse-percentage :value="0.1" />'));
        $this->assertStringContainsString('70.0%', Blade::render('<x-pulse-percentage :value="0.7" />'));
    }
    public function test_signed_score_formatting_and_styles(): void
    {
        $negative = Blade::render('<x-pulse-score :score="-51.58" />');
        $zero = Blade::render('<x-pulse-score :score="0" />');
        $positive = Blade::render('<x-pulse-score :score="42.5" />');
        $this->assertStringContainsString('data-pulse-score-sign="negative"', $negative);
        $this->assertStringContainsString('text-red-300', $negative);
        $this->assertStringContainsString('-51.58', $negative);
        $this->assertStringContainsString('data-pulse-score-sign="neutral"', $zero);
        $this->assertStringContainsString('0.00', $zero);
        $this->assertStringContainsString('data-pulse-score-sign="positive"', $positive);
        $this->assertStringContainsString('text-emerald-300', $positive);
        $this->assertStringContainsString('+42.50', $positive);
        $this->assertStringContainsString('Range: -100 negative, 0 neutral, +100 positive', $positive);
    }
}
