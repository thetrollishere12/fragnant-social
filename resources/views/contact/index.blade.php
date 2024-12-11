@section('title')
{{ env('APP_NAME') }} - Contact | Customer Services
@endsection

@section('description')
We're here to help with any questions & concerns you have have. Click here to email/chat with us & try to respond back asap. {{env('MAIL_CONTACT_ADDRESS')}}
@endsection

<x-app-layout>

	@livewire('contact')

</x-app-layout>