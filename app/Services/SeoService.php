<?php

namespace App\Services;

use Illuminate\Support\Str;

class SeoService
{
    protected string $title = 'My Leader Kenya | 2027 Kenya Elections';

    protected string $description = 'My Leader Kenya – Non-partisan platform for Kenya 2027 aspirants, candidates, voter registration (Niko Kadi), news, polls and campaign tools.';

    protected string $canonical = '';

    protected array $og = [];

    protected array $schemas = [];

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = Str::limit(trim($description), 160, '');
        return $this;
    }

    public function setCanonical(?string $url = null): self
    {
        $this->canonical = $url ?: url()->current();
        return $this;
    }

    public function setOg(array $data): self
    {
        $this->og = array_merge($this->og, $data);
        return $this;
    }

    public function addSchema(array $schema): self
    {
        $this->schemas[] = $schema;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCanonical(): string
    {
        return $this->canonical ?: url()->current();
    }

    public function getOg(): array
    {
        return $this->og;
    }

    public function getSchemas(): array
    {
        return $this->schemas;
    }

    public function getSchemasJson(): string
    {
        if (empty($this->schemas)) {
            return '';
        }

        return json_encode(
            count($this->schemas) === 1 ? $this->schemas[0] : ['@context' => 'https://schema.org', '@graph' => $this->schemas],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }
}