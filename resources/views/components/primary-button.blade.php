<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-violet-600 to-fuchsia-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-violet-700 hover:to-fuchsia-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition shadow-lg shadow-violet-500/25']) }}>
    {{ $slot }}
</button>
