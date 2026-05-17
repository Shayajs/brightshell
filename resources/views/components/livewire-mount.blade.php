@props([
    'name',
    'key' => null,
    /** @var array<string, mixed> */
    'params' => [],
])

{!! app('livewire')->mount($name, $params, $key) !!}
