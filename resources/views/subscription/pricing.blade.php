<x-app-layout>

	@section('title')

    @endsection
    @section('description')

    @endsection

    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pricing') }}
        </h1>
    </x-slot>

	<div>
		
		<div class="max-w-7xl mx-auto p-2">
			

			@foreach($products as $product)
				@livewire('subscription.pricing',['product_name'=>$product->name])
			@endforeach

		</div>

	</div>
</x-app-layout>


<style type="text/css">

	.subscription-box button{
		outline: none;
		color: white;
		border-radius: 20px;
		padding: 8px 35px;
		margin-top: 10px;
	}

</style>