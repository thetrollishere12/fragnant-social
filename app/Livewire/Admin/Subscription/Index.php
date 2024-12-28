<?php

namespace App\Livewire\Admin\Subscription;

use Livewire\Component;

use App\Models\SubscriptionPlan;
use App\Models\SubscriptionProduct;
use Stripe;
use Livewire\WithFileUploads;
use Storage;
use Intervention\Image\Facades\Image;
use WireUi\Traits\WireUiActions;

use App\Helper\PaypalHelper;
use App\Helper\AppHelper;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use Intervention\Image\Encoders\AutoEncoder;


class Index extends Component
{
    use WireUiActions;
    use WithFileUploads;

    public $addModal = false;
    public $addProduct = false;

    public $product;

    public $status = false;
    public $plan_free = false;
    public $plan_name;

    public $recurring_type;

    public $recurring_count = 1;

    public $payment_type;
    public $price;
    public $sale_price;
    public $currency;

    public $plan_description;


    public $benefit;
    public $benefits = [];




    public $trial_period_days = 0;
    public $exclusive_users;



    public $image;
    public $image_array = [];

    public $plan_image;
    public $plan_image_array = [];

    public $product_name;
    public $product_status = false;



    public $product_description;

    public $thumbnail_size = 500;






    public $plan_metadata = "{}";


    public $attribute = "{}";



    public $public_plan;









public function updatedPlanImage()
{
    $limit = 8;

    $this->validate([
        'plan_image' => 'image|max:10240', // 10MB Max
    ]);

    if ($this->plan_image) {
        try {
            if (count($this->plan_image_array) < $limit) {
                $this->processImage(
                    $this->plan_image,
                    $this->plan_image_array,
                    $this->thumbnail_size,
                    'livewire-tmp/'
                );
            } else {
                $this->addError('tags', 'You can only have between 1-' . $limit . ' Images');
            }
        } catch (\Exception $e) {
            $this->addError('error', $e->getMessage());
        }
    }
}

public function updatedImage()
{
    $limit = 8;

    $this->validate([
        'image' => 'image|max:10240', // 10MB Max
    ]);

    if ($this->image) {
        try {
            if (count($this->image_array) < $limit) {
                $this->processImage(
                    $this->image,
                    $this->image_array,
                    $this->thumbnail_size,
                    'livewire-tmp/'
                );
            } else {
                $this->addError('tags', 'You can only have between 1-' . $limit . ' Images');
            }
        } catch (\Exception $e) {
            $this->addError('error', $e->getMessage());
        }
    }
}

private function processImage($imageFile, &$imageArray, $thumbnailSize, $path)
{
    try {
    $fullPath = $path . $imageFile->getFilename();
    if (!Storage::disk('local')->exists($fullPath)) {
        throw new \Exception("The file does not exist at: " . $fullPath);
    }

    $fileContent = Storage::disk('local')->get($fullPath);

    if (empty($fileContent)) {
        throw new \Exception("File content is empty or unreadable.");
    }

    $imageManager = new ImageManager(new Driver()); // Ensure GD driver is used
    $image = $imageManager->read($fileContent);

    $width = $image->width();
    $height = $image->height();
    $dimension = min($width, $height);

    if ($dimension > $thumbnailSize) {
        $image = $image->crop($dimension, $dimension) // Crop the image to a square
            ->resize($thumbnailSize, $thumbnailSize, function ($constraint) {
                $constraint->aspectRatio(); // Maintain aspect ratio
            });

        // Convert to binary data for storage
        $imageBinary = $image->encodeByMediaType('image/png', progressive: true, quality: 80);

        // Save the processed image
        Storage::disk('public')->put($path . 'iq-' . $imageFile->getFilename(), $imageBinary);

    } else {
        // Convert to binary data for storage
        $imageBinary = $image->encodeByMediaType('image/png', progressive: true, quality: 80);

        // Save the processed image
        Storage::disk('public')->put($path . 'iq-' . $imageFile->getFilename(), $imageBinary);
    }

    $imageArray[] = [
        "displayUrl" => Storage::disk('public')->url($path . 'iq-' . $imageFile->getFilename() . '?expires=' . uniqid()),
        "url" => $imageFile->temporaryUrl(),
        "name" => 'i-' . $imageFile->getFilename(),
        "original_name" => $imageFile->getFilename(),
        "default" => count($imageArray) == 0 ? true : false,
        "state" => "temporary"
    ];
} catch (\Exception $e) {
    throw new \Exception("Image processing failed: " . $e->getMessage());
}
}






    public function add_product(){

        $this->addProduct = true;

    }

    public function add_plan(){

        $this->addModal = true;

    }


    public function deleteBenefits($key){
        unset($this->benefits[$key]);
    }

public function addBenefits()
{
    // Debugging check
    if (!isset($this->benefit)) {
        $this->addError('benefit', 'Benefit cannot be empty.');
        return;
    }

    $limit = 15;

    // Validate the input
    $validatedData = $this->validate([
        'benefit' => 'required|string|max:200',
        'benefits' => 'array',
    ]);

    $this->benefit = trim($this->benefit);

    // Check for duplicates
    if (in_array($this->benefit, $this->benefits)) {
        $this->addError('benefits', 'Benefit already exists.');
    } elseif (count($this->benefits) < $limit) {
        // Add the benefit
        $this->benefits[] = $this->benefit;
    } else {
        $this->addError('benefits', "You can only have between 0 and $limit benefits.");
    }

    // Reset the benefit field
    $this->reset('benefit');
}

    public function delete($id){

        unset($this->image_array[$id]);
        
        $this->image_array = array_values($this->image_array);

        // Check if a default image exist. If not its going to set the first in the array as default
        // if (array_search(true, array_column($this->image_array, 'default')) === false) {
        //     $this->image_array[array_key_first($this->image_array)]['default'] = true;
        // }

    }


    public function save_product(){

        $this->validate([
            'product_name' => 'required'
        ]);

        $sub_images = [];

        foreach($this->image_array as $i => $image){

            // Move from temporary to permanent folder
            $random = AppHelper::random_id('i-'.$i.'-');

            

            // Move to new temporary folder
            $imagePath = Storage::disk('public')->move('livewire-tmp/iq-'.$image['original_name'],'image/subscription/'.$random.'.png');
            $sub_images[] = Storage::disk('public')->url('image/subscription/'.$random.'.png');

        }

        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $stripe = Stripe\Product::create([
            'name'=>$this->product_name,
            'active'=>$this->product_status,
            'description'=>$this->product_description,
            'images'=>$sub_images
        ]);

        $token = PaypalHelper::paypal_bearer_token();

        $paypal = PaypalHelper::paypal_create_product($token,
        (object)[
            'name'=>$this->product_name,
            'description'=>$this->product_description,
            'image'=>$sub_images
        ]);




        SubscriptionProduct::create([
            'name'=>$this->product_name,
            'status'=>$this->product_status ? 1 : 0,
            'description'=>$this->product_description,
            'stripe_product_id'=>$stripe->id,
            'paypal_product_id'=>$paypal->id,
            'image'=>$sub_images
        ]);

        $this->addProduct = false;

        $this->reset();

        return $this->notification()->send([
            'title'       => 'Created!',
            'description' => 'Product was created',
            'icon'        => 'success',
        ]);

    }

    public function save_plan(){

        $product = SubscriptionProduct::find($this->product);
        

        $sub_images = null;

        foreach($this->plan_image_array as $i => $image){

            // Move from temporary to permanent folder
            $random = AppHelper::random_id('i-'.$i.'-');

            

            // Move to new temporary folder
            $imagePath = Storage::disk('public')->move('livewire-tmp/iq-'.$image['original_name'],'image/subscription/'.$random.'.png');
            $sub_images = $random.'.png';

        }

        


        if ($this->plan_free == false) {
            
            $this->validate([
                'product'=>'required|numeric',
                'recurring_type' => 'required',
                'recurring_count'=>'required|numeric',
                'payment_type'=>'required',
                'price'=>'required',
                'currency'=>'required',
                'plan_name'=>'required'
            ]);

            $token = PaypalHelper::paypal_bearer_token();

            $paypal = PaypalHelper::paypal_create_plan($token,(object)[
                'product_id'=>$product->paypal_product_id,
                'name'=>$this->plan_name,
                'status' => ($this->status == 1) ? 'ACTIVE' : 'INACTIVE',
                'description' => $this->plan_description,
                "interval_unit" => strtolower($this->recurring_type),
                "interval_count" => $this->recurring_count,
                "value" => $this->sale_price ? $this->sale_price : $this->price,
                "currency" => strtoupper($this->currency)
            ]);


            Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $stripe = Stripe\Price::create([
                'currency'=>$this->currency,
                'product'=>$product->stripe_product_id,
                'unit_amount'=>$this->sale_price ? $this->sale_price*100 : $this->price*100,
                'active'=>$this->status,
                'recurring[interval]'=>strtolower($this->recurring_type),
                'recurring[interval_count]'=>$this->recurring_count,
                'nickname'=>$this->plan_description
            ]);


            


            $data = [
            'subscription_product_id'=>$product->id,
            'status'=>$this->status ? 1 : 0,
            'name'=>$this->plan_name,
            'icon_image'=>$sub_images,
            'recurring_count'=>$this->recurring_count,
            'recurring_type'=>$this->recurring_type,
            'payment_type'=>$this->payment_type,
            'price'=>$this->price,
            'sale_price'=>$this->sale_price,
            'currency'=>$this->currency,
            'stripe_plan_id'=>$stripe->id,
            'paypal_plan_id'=>$paypal->id,
            'benefits'=>$this->benefits,
            'description'=>$this->plan_description,
            'plan_metadata'=>json_decode($this->plan_metadata,true),
            'attributes'=>json_decode($this->attribute,true),
            'public'=>$this->public_plan

        ];

        }else{

            $this->validate([
                'plan_name'=>'required'
            ]);

            
            $data = [
                'subscription_product_id'=>$product->id,
                'status'=>$this->status ? 1 : 0,
                'name'=>$this->plan_name,
                'icon_image'=>$sub_images,
                'price'=>0.00,
                'benefits'=>$this->benefits,
                'description'=>$this->plan_description,
                'plan_metadata'=>json_decode($this->plan_metadata,true),
                'attributes'=>json_decode($this->attribute,true),
                'public'=>$this->public_plan
            ];

        }

        SubscriptionPlan::create($data);

        $this->addModal = false;

        $this->reset();

        return $this->notification()->send([
            'title'       => 'Created!',
            'description' => 'Plan was created',
            'icon'        => 'success',
        ]);

    }

    public function render()
    {
        return view('livewire.admin.subscription.index',[
            'products' => SubscriptionProduct::orderBy('created_at','DESC')->get()
        ]);
    }
}
