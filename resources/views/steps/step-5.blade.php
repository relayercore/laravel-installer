<div class="text-center py-8">
    <div class="text-7xl mb-6">🎉</div>
    <h2 class="text-3xl font-bold text-green-600 mb-3">Installation Complete!</h2>
    <p class="text-gray-600 mb-8 text-lg">{{ config('installer.name') }} has been installed successfully.</p>
    
    <div class="space-y-4 max-w-md mx-auto">
        <a href="{{ config('installer.redirect_after_install', '/admin') }}" 
           class="block w-full py-3 bg-primary text-white rounded-lg hover:bg-primary-dark font-semibold transition">
            Go to Dashboard
        </a>
        <a href="/" class="block w-full py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold transition">
            Visit Homepage
        </a>
    </div>
    
    <div class="mt-10 p-4 bg-amber-50 text-amber-800 rounded-lg text-sm max-w-md mx-auto">
        <strong>Security Tip:</strong> The <code class="bg-amber-200 px-1 rounded">/install</code> route is now automatically blocked.
    </div>
</div>
