<?php

namespace Models\Adapters;

interface LeadComponentInterface
{
    public function getData(): array;
    public function setData(array $data): void;
    public function save(): bool;
}
