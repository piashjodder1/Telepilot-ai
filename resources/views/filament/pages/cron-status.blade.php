<x-filament-panels::page>
    @php
        $lastHeartbeat = \App\Models\HeartbeatLog::latest('ticked_at')->first();
        $isAlive = $lastHeartbeat && $lastHeartbeat->ticked_at->diffInSeconds(now()) < 120;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-filament::section>
            <div class="flex items-center gap-x-3 mb-4">
                <h2 class="text-lg font-bold">Cron Status</h2>
            </div>
            
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200">
                <div class="flex items-center gap-x-3">
                    <x-heroicon-o-clock class="w-6 h-6 text-primary-500" />
                    <span class="font-semibold text-gray-700">Last Cron Run:</span>
                </div>
                <div class="text-sm font-medium text-gray-900">
                    {{ $lastHeartbeat ? $lastHeartbeat->ticked_at->diffForHumans() : 'Never' }}
                    @if($lastHeartbeat)
                        <span class="text-xs text-gray-400 block text-right">{{ $lastHeartbeat->ticked_at->format('M d, Y h:i A') }}</span>
                    @endif
                </div>
            </div>

            <div class="mt-6 p-4 bg-green-50 rounded-xl border border-green-200">
                <h3 class="font-bold text-green-800 mb-2">এই Cron কীভাবে কাজ করে?</h3>
                <ul class="list-disc pl-5 text-sm text-green-700 space-y-1">
                    <li>প্রথমে এটি আপনার সব <strong>Rule</strong> চেক করে।</li>
                    <li>সময় হলে <strong>AI</strong> দিয়ে কন্টেন্ট তৈরি করে।</li>
                    <li>সাথে সাথেই টেলিগ্রাম চ্যানেলে <strong>Post</strong> পাঠিয়ে দেয়।</li>
                    <li>অর্থাৎ, <strong>schedule:work</strong> এবং <strong>queue:work</strong> এর কাজ এই একটি কমান্ডই করে দেয়!</li>
                </ul>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex items-center gap-x-3 mb-4">
                <h2 class="text-lg font-bold">cPanel Web Cron Setup</h2>
            </div>
            
            <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                আপনার cPanel-এর <strong>Cron Jobs</strong> অপশনে গিয়ে নিচের কমান্ডটি হুবহু কপি করে বসিয়ে দিন। 
                টাইম সেটিংসে <strong>Every minute (* * * * *)</strong> সিলেক্ট করবেন।
            </p>

            <div class="space-y-4">
                <div class="bg-gray-50 border border-gray-200 p-4 rounded-xl">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-bold text-red-500 uppercase tracking-wider">All-in-One Command (Copy This)</div>
                    </div>
                    <code class="block bg-gray-900 border border-gray-800 p-4 rounded-lg text-sm text-green-400 font-mono overflow-x-auto shadow-sm select-all">wget -q -O - {{ url('/cron') }} >/dev/null 2>&1</code>
                </div>
            </div>

            <div class="mt-6 p-4 bg-primary-50 rounded-xl border border-primary-100 flex items-start gap-x-3">
                <x-heroicon-o-information-circle class="w-6 h-6 text-primary-500 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-primary-800">
                    আপনি চাইলে কমান্ড না বসিয়ে সরাসরি এই লিংকে ভিজিট করেও ম্যানুয়ালি Cron চালাতে পারেন:<br>
                    <a href="{{ url('/cron') }}" target="_blank" class="font-bold underline hover:text-primary-900 break-all">{{ url('/cron') }}</a>
                </p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
