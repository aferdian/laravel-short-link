@props([
    'disabled' => false,
    'rounding' => 'rounded-md'
])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm ' . $rounding]) }}>
