<div class="max-w-2xl mx-auto px-2">
    <form wire:submit.prevent="send">
        <h1 class="font-bold text-4xl py-4">Send Us A Message</h1>

        @if (session()->has('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @error('email') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        @error('title') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        @error('message') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
        @error('gRecaptchaResponse') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror

        <input class="rounded w-full border border-gray-200 text-gray-500 p-2" 
               type="email" 
               wire:model.defer="email" 
               placeholder="Email Address" 
               required>

        <div>
            <select wire:model.defer="title" 
                    required 
                    class="rounded w-full p-2 my-2 border border-gray-200 text-gray-500">
                <option value="">Select a Topic</option>
                <option value="General Inquiry">General Inquiry</option>
                <option value="Order">Order</option>
                <option value="Shipping">Shipping</option>
                <option value="Terms & Condition">Terms & Condition</option>
                <option value="Subscription">Subscription</option>
                <option value="Tech Issues">Tech Issues</option>
                <option value="Account">Account</option>
                <option value="Payment Issues">Payment Issues</option>
                <option value="Bug Report">Bug Report</option>
                <option value="Feedback">Feedback</option>
                <option value="Copyright">Copyright</option>
                <option value="Other">Other</option>
            </select>
        </div>

        <div>
            <textarea wire:model.defer="message" 
                      required 
                      class="w-full rounded resize-none h-80 border border-gray-200 text-gray-500 p-2"></textarea>
        </div>

        <div class="text-xs text-gray-500">
            Please enter the details of your request...
        </div>

        <div class="g-recaptcha my-2" 
             wire:ignore 
             data-sitekey="{{ env('GOOGLE_RECAPTCHA_SITE_KEY') }}" 
             data-callback="recaptchaCallback"></div>

        <div>
            <button type="submit" 
                    class="main-bg-c w-full text-white text-base py-2.5 mb-2">
                Send Message
            </button>
        </div>
    </form>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    function recaptchaCallback(response) {
        Livewire.emit('recaptchaValidated', response);
    }
</script>