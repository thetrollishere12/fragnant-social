@section('title')
{{ env('APP_NAME') }} - Contact | Customer Services
@endsection

@section('description')
We're here to help with any questions & concerns you have have. Click here to email/chat with us & try to respond back asap. {{env('MAIL_CONTACT_ADDRESS')}}
@endsection

<x-app-layout>
    <div class="max-w-2xl mx-auto px-2">
        <form action="send-contact" method="POST">
        @csrf
        <h1 class="font-bold text-4xl py-4">Send Us A Message</h1>

        <x-validation-errors class="mb-4" />

        <x-validation-success class="my-2" />

        <input class="rounded w-full border border-gray-200 text-gray-500 p-2" type="email" @if(old('email')) value="{{ old('email') }}" @endif @auth value="{{ Auth::user()->email }}" @endauth name="email" placeholder="Email Address" required>

        <div>
            <select name="title" required class="rounded w-full p-2 my-2 border border-gray-200 text-gray-500">
                <option {{ old('title') == "General Inquiry" ? "selected" : "" }} value="General Inquiry">General Inquiry</option>
                <option {{ old('title') == "Order" ? "selected" : "" }} value="Subscription">Order</option>
                <option {{ old('title') == "Shipping" ? "selected" : "" }} value="Subscription">Shipping</option>
                <option {{ old('title') == "Terms & Contidion" ? "selected" : "" }} value="Subscription">Terms & Condition</option>
                <option {{ old('title') == "Subscription" ? "selected" : "" }} value="Subscription">Subscription</option>
                <option {{ old('title') == "Tech Issues" ? "selected" : "" }} value="Tech Issues">Tech Issues</option>
                <option {{ old('title') == "Account" ? "selected" : "" }} value="Account">Account</option>
                <option {{ old('title') == "Paymment Issues" ? "selected" : "" }} value="Paymment Issues">Paymment Issues</option>
                <option {{ old('title') == "Bug Report" ? "selected" : "" }} value="Bug Report">Bug Report</option>
                <option {{ old('title') == "Feedback" ? "selected" : "" }} value="Feedback">Feedback</option>
                <option {{ old('title') == "Copyright" ? "selected" : "" }} value="Copyright">Copyright</option>
                <option {{ old('title') == "Other" ? "selected" : "" }} value="Other">Other</option>
            </select>
        </div>

        <div>
        <textarea required name="message" class="w-full rounded resize-none h-80 border border-gray-200 text-gray-500 p-2">{{ old('message') }}</textarea>
        </div>

        <div class="text-xs text-gray-500">Please enter the details of your request and, if you have any questions regarding our Terms of Use, please include specific samples of the usage you wish to give our resouces. If you’re reporting a problem, make sure to include as much information as possible. Please include any screenshots or videos of issues since this will also help us resolve problems much sooner. Once your request is submitted, a member of our support staff will respond as soon as possible.</div>

        <div class="g-recaptcha my-2" data-callback="recaptchaCallback" data-sitekey="{{ env('GOOGLE_RECAPTCHA_SITE_KEY') }}"></div>

        <div>
            <x-button type="submit" class="main-bg-c w-full text-white text-base py-2.5 mb-2">Send Message</x-button>
        </div>

        </form>

    </div>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

</x-app-layout>