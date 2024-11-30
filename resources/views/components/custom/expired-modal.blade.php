<a class="cursor-pointer" onclick="location.reload();">
    <div class="modal fade" id="expiredModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

          <div class="modal-body">
           
                <div class="text-center p-4">
                    <img class="m-auto" src="{{ Storage::disk('public')->url('image/error_asset/expired.png') }}">
                    <div class="py-3">
                        <h2 class="text-2xl font-bold pb-2">Your session has expired</h2>
                        <p class="m-auto text-sm">Please click anywhere on the page to refresh the page</p>
                    </div>
    
                </div>
            
          </div>

        </div>
      </div>
    </div>
</a>