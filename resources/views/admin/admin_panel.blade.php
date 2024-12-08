<x-admin-layout>

<div class="max-w-7xl px-2 py-10 mx-auto">
    
	<h1 class="text-4xl font-bold py-4">Social Media</h1>

	<!-- <a href="{{ url('admin/fb-login') }}"><x-button primary label="Facebook + Instagram"/></a> -->

	<h1 class="text-4xl font-bold py-4">Tools</h1>

	<div>

		<div>
	    	
	    	<h2 class="font-bold text-2xl py-4">Subscriptions</h2>

	    	<div class="grid md:grid-cols-4 gap-3 text-white text-center">
	    		
	    		<a href="{{ url('admin/subscription-list') }}"><div class="bg-indigo-500 font-bold rounded py-4">Subscriptions</div></a>

	    	</div>

	    </div>

	    <div>
	    	
	    	<h2 class="font-bold text-2xl py-4">Content/Article Writing</h2>

	    	<div class="grid md:grid-cols-4 gap-3 text-white text-center">
	    		
	    		<a href="{{ url('admin/article-faq-writer') }}"><div class="bg-blue-500 font-bold rounded py-4">FAQ/QNA Article</div></a>

	    		<a href="{{ url('admin/article-writer') }}"><div class="bg-blue-500 font-bold rounded py-4">Article</div></a>

	    		<a href="{{ url('admin/article-writer-upload') }}"><div class="bg-blue-500 font-bold rounded py-4">Article Upload</div></a>

	    	</div>

	    </div>


	    <div>
	    	
	    	<h2 class="font-bold text-2xl py-4">Social Media</h2>

	    	<div class="grid md:grid-cols-4 gap-3 text-white text-center">
	    		
	    		<a href="{{ url('sandbox/image-post') }}"><div class="bg-purple-500 font-bold rounded py-4">Instagram Info Maker</div></a>

	    		<a href="{{ url('sandbox/image-faq-post') }}"><div class="bg-purple-500 font-bold rounded py-4">Instagram FAQ Maker</div></a>

	    		<a href="{{ url('admin/marketing/instagram/comments') }}"><div class="bg-purple-500 font-bold rounded py-4">Instagram Commentor</div></a>

	    		<a href="{{ url('admin/marketing/reddit/species-experience') }}"><div class="bg-orange-500 font-bold rounded py-4">Reddit Plant Experience</div></a>

	    	</div>

	    </div>

	    <div>
	    	
	    	<h2 class="font-bold text-2xl py-4">Merchant/Customer Aquisition</h2>

	    	<div class="grid md:grid-cols-4 gap-3 text-white text-center">
	    		
	    		<a href="{{ url('admin/merchant/etsy') }}"><div class="bg-orange-500 font-bold rounded py-4">Etsy Sellers</div></a>

	    		<a href="{{ url('admin/merchant/google') }}"><div class="bg-green-500 font-bold rounded py-4">Google Sellers</div></a>

	    		<a href="{{ url('admin/merchant/send-email') }}"><div class="bg-amber-500 font-bold rounded py-4">Email Merchant</div></a>

	    		<a href="{{ url('admin/marketing/send-bulk-email') }}"><div class="main-bg-c font-bold rounded py-4">Bulk Email</div></a>

	    	</div>

	    </div>

	    <div>
	    	
	    	<h2 class="font-bold text-2xl py-4">Plant Species</h2>

	    	<div class="grid md:grid-cols-4 gap-3 text-white text-center">
	    		
	    		<a href="{{ url('admin/species/add-tags') }}"><div class="main-bg-c font-bold rounded py-4">Add Species Tag</div></a>

	    		<a href="{{ url('admin/species/add-image-description-alt') }}"><div class="main-bg-c font-bold rounded py-4">Add Species Image Description</div></a>

	    		<a href="{{ url('admin/species/add-event-months') }}"><div class="main-bg-c font-bold rounded py-4">Add Species Events</div></a>

	    		<a href="{{ url('admin/species/add-species-guide') }}"><div class="main-bg-c font-bold rounded py-4">Add Species Guide</div></a>

	    		<a href="{{ url('admin/species/add-species-nutrition') }}"><div class="main-bg-c font-bold rounded py-4">Add Species Nutrition</div></a>

	    		<a href="{{ url('admin/species/unprocessed-images') }}"><div class="main-bg-c font-bold rounded py-4">View Unprocessed Images</div></a>

	    		<a href="{{ url('admin/species/images') }}"><div class="main-bg-c font-bold rounded py-4">View Processed Images</div></a>


	    		<a href="{{ url('admin/species/image-picker-result?from=0&to=1000&key='.env('SITE_SECRET_ADMIN_KEY_1')) }}"><div class="main-bg-c font-bold rounded py-4">View Missing Images</div></a>


	    	</div>

	    </div>


	    <div>
	    	
	    	<h2 class="font-bold text-2xl py-4">Database Related</h2>

	    	<div class="grid md:grid-cols-4 gap-3 text-white text-center">
	    		
	    		<a href="{{ url('admin/disease/add-description') }}"><div class="main-bg-c font-bold rounded py-4">Add Disease Details</div></a>

	    		<a href="{{ url('admin/propagation/add-description') }}"><div class="main-bg-c font-bold rounded py-4">Add Propagation Details</div></a>
	    		
	    	</div>

	    </div>

	</div>






	<h1 class="text-4xl font-bold py-4">Current Roles</h1>

	<div>
		@foreach($roles as $key => $role)
		<div class="font-bold text-2xl py-4 capitalize">{{str_replace("_"," ",$key)}}</div>

		@if($role->count() > 0)
		<div class="flex gap-2">
		@foreach($role as $user)

			@if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
			<a href="{{ url('admin/users/'.$user->id.'/edit') }}">
	            <div class="shadow bg-white rounded px-4 py-1">
	                <button class="text-sm border-2 border-transparent rounded-full focus:outline-none transition">
	                    <img class="h-14 my-2 w-14 mx-auto rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" />
	                    <div class="user-profile-name self-center ml-2"><span class="username">{{ $user->name }}</span></div>
	                </button>
	            </div>
	        </a>
            @endif

		@endforeach
		</div>
		@else
		<a href="https://www.upwork.com/nx/wm/client/dashboard"><x-button primary label="Hire" /></a>
		@endif

		@endforeach
	</div>



</div>


</x-admin-layout>