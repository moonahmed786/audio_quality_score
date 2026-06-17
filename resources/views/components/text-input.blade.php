@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-200 focus:border-violet-500 focus:ring-violet-500 rounded-xl shadow-sm transition']) }}>
