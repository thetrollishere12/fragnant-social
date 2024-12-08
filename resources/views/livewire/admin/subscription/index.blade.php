<div class="w-full mx-auto text-sm">
    
    <x-button class="w-full my-1" green label="Add Product" wire:click="add_product"/>
    
    @if($products->count() == 0)
    <div class="py-3 text-xl font-bold text-center">No Products</div>
    @else

    <x-button class="w-full my-1" primary label="Add Plan" wire:click="add_plan"/>

    @foreach($products as $project)

    

        <div class="shadow rounded bg-white p-4 my-2">
            
            <div class="font-bold text-lg">Product</div>

            <div class="">
                <div class="flex gap-2">
                    @if(count($project->image) > 0)<div class="aspect-square bg-contain bg-no-repeat bg-center w-12 h-12 rounded" style="background-image:url('{{$project->image[0]}}');"></div>@endif

                    <div class="text-lg">{{ $project->name }} (Created On - {{ Carbon\Carbon::parse($project->created_at)->format('M d, Y') }})</div>

                </div>
            </div>



            @if($project->plan()->get()->count() > 0)

            <div class="font-bold pt-3 text-lg">Plans</div>

            <div class="grid grid-cols-10 gap-2 border-t border-b py-2">
                
                <div></div>
                <div>Status</div>
                <div>Public</div>
                <div>Name</div>
                <div>Price</div>
                <div>Description</div>
                <div>Benefit</div>
                <div>Metadata</div>
                <div>Attribute</div>
                <div>Created</div>

            </div>

            @foreach($project->plan()->get() as $plan)

            <a href="{{ url('admin/subscription-list/'.$plan->id) }}">

                <div class="py-2 grid grid-cols-10 items-center gap-2">

                <div class="w-8">
                    <img class="w-full" src='{{ Storage::disk("public")->url("image/subscription/".$plan->icon_image) }}'>
                </div>

                <div>
                    @if($plan->status == true)
                   <div class="flex items-center gap-2">Active<div class="rounded-full w-2 h-2 bg-green-500"></div></div>
                   @else
                   <div class="flex items-center gap-2">Inactive<div class="rounded-full w-2 h-2 bg-red-500"></div></div>
                   @endif
                </div>

                <div>
                    @if($plan->public == true)
                   <div class="flex items-center gap-2">Active<div class="rounded-full w-2 h-2 bg-green-500"></div></div>
                   @else
                   <div class="flex items-center gap-2">Inactive<div class="rounded-full w-2 h-2 bg-red-500"></div></div>
                   @endif
                </div>

                <div>{{ $plan->name }}</div>

                <div>
                    @php $effectivePrice = $plan->sale_price ?? $plan->price; @endphp
                    @if($effectivePrice == 0.00)
                        <b>FREE</b>
                    @else
                        @if($plan->sale_price)
                            <div><del>{{ $plan->price }} {{ $plan->currency }}</del></div> <b>{{ $plan->sale_price }} {{ $plan->currency }}</b>
                        @else
                            <b>{{ $plan->price }} {{ $plan->currency }}</b>
                        @endif
                        every {{ $plan->recurring_count }} {{ $plan->recurring_type }}{{ $plan->recurring_count > 1 ? 's' : '' }}
                    @endif
                </div>

                <div>{{ $plan->description }}</div>

                <div>
                    @foreach($plan->benefits as $benefit)
                    <div>- {{ $benefit }}</div>
                    @endforeach
                </div>


                <div>
                    @foreach($plan->plan_metadata as $key => $value)
                        @if(is_array($value))
                            <strong class="capitalize">{{ $key }}:</strong> {{ implode(', ', $value) }}<br>
                        @else
                            <strong class="capitalize">{{ $key }}:</strong> {{ $value }}<br>
                        @endif
                    @endforeach
                </div>

                <div>
                    @foreach($plan->attributes as $key => $value)
                        @if(is_array($value))
                            <strong class="capitalize">{{ $key }}:</strong> {{ implode(', ', $value) }}<br>
                        @else
                            <strong class="capitalize">{{ $key }}:</strong> {{ $value }}<br>
                        @endif
                    @endforeach
                </div>

                <div>{{ Carbon\Carbon::parse($plan->created_at)->format('M d, Y') }}</div>

                </div>

            </a>

            @endforeach

            @else

            <div class="font-bold pt-3 text-lg">No Plans</div>

            @endif

        </div>

 

    @endforeach

    @endif

    


<x-wui-modal.card title="Add Product" blur max-width="5xl" wire:model.defer="addProduct">

    <div class="grid grid-cols-1 gap-4">

        <x-toggle label="Active" wire:model.defer="product_status"/>





        <div>

            <div class="font-bold" wire:loading.class="text-indigo-500">Subscription Photos</div>

            <div class="py-2">

                <input wire:loading.attr="disabled" type="file" wire:model="image" accept="image/*" id="image-upload" class="hidden" name="image"/>

                <div wire:loading.class="cursor-not-allowed" wire:target="image" class="upload-case cursor-pointer border-4 border-dotted border-indigo-500 rounded-md">
                    <img class="m-auto p-2" src="{{ Storage::disk('public')->url('image/download.svg') }}">
                    <div class="m-auto text-sm text-center pb-10 pt-4"><span class="text-blue-600">Select</span> your file(s) from your computer</div>
                </div>

            </div>

            <div class="text-sm" wire:loading>Processing Photos....</div>

            <div class="grid grid-cols-4 py-3.5 gap-3">
                @foreach($image_array as $key => $image)
                <div model:key="image_array.{{$key}}" class="shadow-sm aspect-square content-center relative bg-contain bg-no-repeat bg-center" style="background-image:url('{{ $image['displayUrl'] }}');">

                    <div class="absolute border-t bottom-0 grid grid-cols-1 text-sm w-full">
                        


                        <div class="text-white cursor-pointer py-2 text-center bg-red-500" wire:click="delete({{ $key }})">
                            <span>Delete</span>
                        </div>

                    </div>

                    @if($key == 0)
                        
                    <div class="bg-yellow-300 text-white text-center p-2 text-sm absolute top-0 right-0 icon-star-full"></div>

                    @endif

                </div>
                @endforeach

            </div>
        </div>





        <x-input wire:model.defer="product_name" label="Product Name" placeholder="Product Name" />


        <x-textarea wire:model.defer="product_description" label="Product Description" placeholder="Product Description" />

        <x-errors/>


    </div>
 
    <x-slot name="footer">
        <div class="flex justify-between gap-x-4">

            <x-button flat label="Cancel" x-on:click="close" /> 
            <div class="flex">
                <x-button primary label="Save" wire:click="save_product" />
            </div>
        </div>
    </x-slot>

</x-wui-modal.card>



<x-wui-modal.card title="Add Subscription" blur max-width="5xl" wire:model.defer="addModal">
    <div class="grid grid-cols-1 gap-3 px-4">

        <div class="grid grid-cols-2 gap-2">

            <div>

                <x-toggle label="Active Subscription" md wire:model.defer="status" />

            </div>

            <div>

                <x-toggle label="Free Plan" md wire:model.defer="plan_free"/>

            </div>

            <div>

                <x-toggle label="Public" md wire:model.defer="public_plan"/>

            </div>

        </div>

        <div>
            
            <div class="font-bold" wire:loading.class="text-indigo-500">Subscription Photos</div>

            <div class="py-2">

                <input wire:loading.attr="disabled" type="file" wire:model="plan_image" accept="image/*" id="image-upload" class="hidden" name="plan_image"/>

                <div wire:loading.class="cursor-not-allowed" wire:target="plan_image" class="upload-case cursor-pointer border-4 border-dotted border-indigo-500 rounded-md">
                    <img class="m-auto p-2" src="{{ Storage::disk('public')->url('image/download.svg') }}">
                    <div class="m-auto text-sm text-center pb-10 pt-4"><span class="text-blue-600">Select</span> your file(s) from your computer</div>
                </div>

            </div>

            <div class="text-sm" wire:loading>Processing Photos....</div>

            <div class="grid grid-cols-4 py-3.5 gap-3">
                @foreach($plan_image_array as $key => $image)
                <div model:key="image_array.{{$key}}" class="shadow-sm aspect-square content-center relative bg-contain bg-no-repeat bg-center" style="background-image:url('{{ $image['displayUrl'] }}');">

                    <div class="absolute border-t bottom-0 grid grid-cols-1 text-sm w-full">
                        


                        <div class="text-white cursor-pointer py-2 text-center bg-red-500" wire:click="delete({{ $key }})">
                            <span>Delete</span>
                        </div>

                    </div>

                    @if($key == 0)
                        
                    <div class="bg-yellow-300 text-white text-center p-2 text-sm absolute top-0 right-0 icon-star-full"></div>

                    @endif

                </div>
                @endforeach

            </div>

        </div>

        <x-input wire:model.defer="plan_name" label="Plan Name" placeholder="Plan name"/>


        <x-select
            class="w-full"
            label="Select Product"
            placeholder="Select Product"
            wire:model.defer="product"
        >
            @foreach($products as $product)
            <x-select.option label="{{ $product->name }}" value="{{ $product->id }}" />
            @endforeach

        </x-select>



        @if($plan_free == false)
        <div>

            <div class="col-span-2">

                <x-label class="pb-2" label="Recurring Amount"/>

                <div class="grid grid-cols-5 gap-2">

                    <div class="py-2 pl-4 rounded-md col-span-1 shadow-sm border flex items-center">Every</div>

                    <div class="col-span-2">

                        <x-number text-sm wire:model.defer="recurring_count" placeholder="1" />

                    </div>

                    <div class="col-span-2">

                        <x-select
                            text-sm
                            placeholder="Day, Month Etc"
                            :options="['Day','Week','Month','Year']"
                            wire:model.defer="recurring_type"
                        />
                    
                    </div>

                </div>

            </div>

            <div></div>

        </div>

        <div class="grid grid-cols-2 gap-2">

            <div class="pt-2.5">
            <x-select 
                label="Payment Type"
                placeholder="Select type"
                :options="['Fixed','Volume','Tier']"
                wire:model.defer="payment_type"
            />
            </div>

        </div>

        <div class="grid grid-cols-2 gap-2">

            <div class="pt-2.5">
            <x-select 
                label="Currency"
                placeholder="Select Currency"
                :options="['usd','cad']"
                wire:model.defer="currency"
            />
            </div>

            <div class="grid grid-cols-2 gap-2">

                <x-input wire:model.defer="price" label="Price" placeholder="Price"/>

                <x-input wire:model.defer="sale_price" label="Sale Price" placeholder="Sale Price" />

            </div>

        </div>


        <x-number label="Trial Day Period" wire:model.defer="trial_period_days"/>
        @endif

        <div class="shadow-sm border p-2 rounded">

            <div>
                
                <div class="mb-1 flex items-center gap-2">
                    <x-label wire:target="addBenefits,generate_benefit" wire:loading.class="text-gray-400">Benefits</x-label>
       
                </div>

                <div>
                @if($benefits)
                <div class="grid grid-cols-1 py-1">
                    @foreach($benefits as $key => $benefit)
                    <div class="text-sm rounded bg-black text-white pl-2 pr-7 py-2 m-0.5 relative"><span wire:click="deleteBenefits({{$key}})" style="font-size: 8px;" class="icon-close absolute top-0 right-0 p-1.5 rounded cursor-pointer"></span>- {{ $benefit }}</div>
                    @endforeach
                </div>
                @else
                <div>No Benefits Added Yet</div>
                @endif

            </div>

            </div>





<div class="pt-2">
    <x-input 
        icon="pencil" 
        wire:loading.class="bg-yellow-50" 
        wire:loading.attr="disabled" 
        wire:keydown.enter="addBenefits" 
        wire:model.defer="benefit" 
        placeholder="Enter Benefit List" 
        maxlength="200"
    />
</div>

<button wire:click="addBenefits" class="btn btn-primary text-xs w-full mt-2">Add Benefit</button>


        </div>







        <div wire:ignore class="border shadow-sm p-2 rounded">

            <x-textarea wire:model.defer="plan_metadata" label="Plan Metadata" placeholder="write your metadata" />

        </div>



        <div wire:ignore class="border shadow-sm p-2 rounded">

            <x-textarea wire:model.defer="attribute" label="Attributes" placeholder="write your attribute" />

        </div>



        <x-textarea wire:model.defer="plan_description" label="Plan Description" placeholder="write your description" />


        <x-input label="Exclusive To User" placeholder="Exclusive To User" wire:model.defer="exclusive_users"/>


        <x-errors/>


        
    </div>
 
    <x-slot name="footer">
        <div class="flex justify-between gap-x-4">

            <x-button flat label="Cancel" x-on:click="close" /> 
            <div class="flex">
                <x-button primary label="Save" wire:click="save_plan" />
            </div>
        </div>
    </x-slot>

</x-wui-modal.card>






















        <script type="text/javascript">
            $('.upload-case').click(function(){
                $(this).prev().trigger('click');
            });

        </script>












</div>
