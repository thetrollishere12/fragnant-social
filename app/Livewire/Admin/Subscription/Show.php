<?php

namespace App\Livewire\Admin\Subscription;

use Livewire\Component;

use App\Models\SubscriptionPlan;
use WireUi\Traits\Actions;

class Show extends Component
{

    use Actions;
    public $benefits;
    public $benefit;
    public $plan_id;

    public function mount($id){
            
        

        $this->plan_id = $id;

        $plan = SubscriptionPlan::find($this->plan_id);

        $this->plan_id = $plan->id;
        $this->plan_free = null;
        $this->benefits = $plan->benefits;
        $this->plan_name = $plan->name;
        $this->status = $plan->status;

        $this->icon_image = $plan->icon_image;

        $this->bandwidth_amount = $plan->bandwidth['amount'];
        $this->bandwidth_type = $plan->bandwidth['type'];

        $this->plan_description = $plan->plan_description;
        $this->exclusive_users = $plan->exclusive_to_user_id;

    }

    public function deleteBenefits($key){
        unset($this->benefits[$key]);
    }

    public function addBenefits(){

        $limit = 15;

        $validatedData = $this->validate([
            'benefit' => 'required|string|max:200',
            'benefits' => 'array'
        ]);

        $this->benefit = trim($this->benefit);

        if (in_array($this->benefit,$this->benefits)) {
            $this->addError('benefits','Benefits already exist');
        }elseif(count($this->benefits) < $limit){
            $this->benefits[] = $this->benefit;
        }else{
            $this->addError('benefits','You can only have between 0-'.$limit.' Benefits');
        }

        $this->reset('benefit');
        
    }

    public function submit(){

        $data = [
            'status'=>$this->status ? 1 : 0,
            'name'=>$this->plan_name,
            'benefits'=>$this->benefits,
            'bandwidth'=>[
                'type'=>$this->bandwidth_type,
                'amount'=>$this->bandwidth_amount
            ],
            'description'=>$this->plan_description
        ];

        SubscriptionPlan::where('id',$this->plan_id)->update($data);

        return $this->notification()->send([
            'title'       => 'Saved!',
            'description' => 'Plan was saved',
            'icon'        => 'success',
        ]);

    }

    public function render()
    {
        return view('livewire.admin.subscription.show');
    }
}
