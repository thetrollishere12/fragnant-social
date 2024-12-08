<div>
	

@if(isset($param))

	@livewire($path,$param)

@else

	@livewire($path)

@endif

	
</div>