<?php

declare(strict_types=1);

namespace Sourceability\Instrumentation\Profiler;

use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Sdk\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Trace\Span;
use OpenTelemetry\Trace\Tracer;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use Throwable;

class ZipkinProfiler implements ProfilerInterface
{

    private ZipkinExporter $exporter;
    private Tracer $tracer;
    private Span $currentSpan;

    public function __construct(string $name, string $url)
    {
        $this->exporter = new ZipkinExporter(
            $name,
            $url,
            new Client(),
            new Psr17Factory(),
            new Psr17Factory()
        );
        $this->tracer = (new TracerProvider())
        ->addSpanProcessor(new SimpleSpanProcessor($this->exporter))
        ->getTracer('io.opentelemetry.contrib.php');
    }

    public function start(string $name, ?string $kind = null): void
    {
        $this->currentSpan = $this->tracer->startAndActivateSpan($name);
    }

    public function stop(?Throwable $exception = null): void
    {
        $this->tracer->endActiveSpan();
    }

    public function stopAndIgnore(): void
    {
        // Can´t find a method that supports this,
        // could be implemented with proper sampler,
        // but that would need to be defined beforehand
        $this->tracer->endActiveSpan();
    }
}
