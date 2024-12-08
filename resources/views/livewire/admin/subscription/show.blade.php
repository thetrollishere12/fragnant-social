<div>

<div class="max-w-5xl p-4 my-4 mx-auto bg-white shadow rounded-md">
    <div class="mb-4">
        <h2 class="text-2xl font-bold">Subscription Plan Details</h2>
    </div>


        

    <div class="grid grid-cols-1 gap-3">

        <div>
            
            <div class="w-20 pb-4">
                <img class="w-full" src='{{ Storage::disk("public")->url("image/subscription/".$icon_image) }}'>
            </div>

        </div>

        <div class="grid grid-cols-2 gap-2">

            <div>

                <x-label class="mb-2" label="Active Subscription"/>
                <x-toggle md wire:model.defer="status" />

            </div>

            <div></div>

        </div>


        <x-input wire:model.defer="plan_name" label="Plan Name" placeholder="Plan name"/>




        <div class="shadow-sm border p-2 rounded">

            <div>
                
                <div class="mb-1 flex items-center gap-2">
                    <x-label wire:target="addBenefits,generate_benefit" wire:loading.class="text-gray-400">Benefits</x-label>

                </div>

                @if($benefits)
                <div class="grid grid-cols-1 py-1" id="benefit-container">
                    @foreach($benefits as $key => $benefit)
                    <div class="text-sm rounded bg-black text-white pl-2 pr-7 py-2 m-0.5 relative"><span onclick="Livewire.emit('deleteBenefits',{{$key}});" style="font-size: 8px;" class="icon-close absolute top-0 right-0 p-1.5 rounded cursor-pointer"></span>- {{ $benefit }}</div>
                    @endforeach
                </div>
                @else
                <div>No Benefits Added Yet</div>
                @endif

            </div>

            <div class="pt-2">

                <x-input icon="pencil" wire:loading.class="bg-yellow-50" wire:loading.attr="disabled" wire:target="addBenefits" wire:keydown.enter="addBenefits" wire:model.defer="benefit" placeholder="Enter Benefit List" maxlength="200"/>


            </div>

        </div>

        <div wire:ignore class="border shadow-sm p-2 rounded">

            <x-select
                label="Bandwidth Type"
                placeholder="Select type"
                :options="['Second','Minute','Hour','Day','Month']"
                wire:model.defer="bandwidth_type"
            />
   
            <x-input wire:model.defer="bandwidth_amount" label="Bandwidth Amount" placeholder="Bandwidth Amount" />

        </div>


        <x-textarea wire:model.defer="plan_description" label="Plan Description" placeholder="write your description" />


        <x-input label="Exclusive To User" placeholder="Exclusive To User" wire:model.defer="exclusive_users"/>


        <x-errors/>

        <x-button primary label="Save Changes" class="w-full mt-2" wire:click="submit"/>
        
    </div>


</div>

</div>