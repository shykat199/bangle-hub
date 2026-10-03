<div class="modal-dialog modal-lg">
  <div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title" id="exampleModalLabel"> Combo Offer Details</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

        <div class="modal-body">
            
            <div class="row">
                <div class="col-lg-12">
                    
                    <div class="col-6">
                        
                        <div class="well">
                            
                            <p>Product : <b>{{ $item->product->name}}</b></p>
                            
                            <div>
                                <img src="{{ getImage('products',$item->product->image)}}" width="200">
                            </div>
                        </div>
                    </div>

                </div>
                
                <div class="col-lg-12">
                    
                    <table class="table table-centered table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>Produt</th>
                                <th>SIze</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        
                       <tbody>
                           
                           @forelse($item->products as $p)
                           
                           <tr>
                                <td> {{ $p->product->name}} </td>
                                
                                <td> {{ $p->size->title}}</td>
                                
                                <td> {{ $p->quantity}} </td>
                            </tr>
                            
                           @empty
                           
                
                            
                           @endforelse
                           
                            
                        
                       </tbody>
                    </table>
                </div>

                </div>
            </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary"> Update</button>
      </div>
  </div>
</div>