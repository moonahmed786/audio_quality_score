<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-violet-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition shadow-md']) }}>
    {{ $slot }}
</button>
