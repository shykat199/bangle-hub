@php $cart = session()->get('cart', []); @endphp       
 Start Cart Area  
        <div class="axil-product-cart-area axil-section-gap">
            <div class="container">
                <div class="axil-product-cart-wrap">
                    <div class="product-table-heading">
                        <h4 class="title">Your Cart</h4>
                        <a href="{{ route('front.carts.clearAll') }}" class="cart-clear" style="color: #c2050b !important;" onclick="return confirm('Are you sure?')">Clear Shoping Cart</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table axil-product-table axil-cart-table mb--40">
                            <thead>
                                <tr>
                                    <th scope="col" class="product-remove"></th>
                                    <th scope="col" class="product-thumbnail" style="font-family: 'Hind Siliguri', sans-serif">Product</th>
                                    <th scope="col" class="product-title"></th>
                                    <th scope="col" class="product-price" style="font-family: 'Hind Siliguri', sans-serif">Price</th>
                                    <th scope="col" class="product-quantity" style="font-family: 'Hind Siliguri', sans-serif">Quantity</th>
                                    <th scope="col" class="product-subtotal" style="font-family: 'Hind Siliguri', sans-serif;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $total=0;
                                @endphp
                                @if($cart)
                                
                                @foreach($cart as $key=>$item)
                                    @php
                                    $price =$item['price']*$item['quantity'];
                                    $total +=$price;
                                    @endphp
                                <tr>
                                    <td class="product-remove">
                                        <form method="POST" action="{{ route('front.carts.destroy',[$key])}}" id="cart_remove_form">
                                            <input type="hidden" name="segment" value="{{ request()->segment(1)}}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="remove-wishlist" type="submit"><i class="fas fa-times"></i></button> 
                                        </form>
                                    </td>
                                    <td class="product-thumbnail"><a href="single-product.php">
                                        <img src="{{ getThumbImage($item['image']) }}" alt="Digital Product"></a>
                                    </td>
                                    <td class="product-title"><a href="single-product.php"> {{ $item['name']}} {{ $item['size'] ??''}} {{ $item['color']??''}} </a></td>
                                    <td class="product-price" data-title="Price">{{ priceFormate($item['price'])}}</td>
                                    <td class="product-quantity" data-title="Qty">
                                        <div class="pro-qty" data-segment="{{ request()->segment(1)}}" data-href="{{ route('front.carts.edit',[$key])}}">
                                            <span class="dec qtybtn">-</span>
                                            <input type="number" class="quantity-input" value="{{ $item['quantity'] }}">
                                            <span class="inc qtybtn">+</span>
                                        </div>
                                    </td>
                                    <td class="product-subtotal" data-title="Subtotal">{{ priceFormate($price)}}</td>
                                </tr>
                                @endforeach
                                @endif
                                
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row">
                        <div class="col-xl-5 col-lg-7 offset-xl-7 offset-lg-5">
                            <div class="axil-order-summery mt--80">
                                <h5 class="title mb--20">Order Summary</h5>
                                <div class="summery-table-wrap">
                                    <table class="table summery-table mb--30">
                                        <tbody>
                                            <tr class="order-subtotal">
                                                <td>Subtotal</td>
                                                <td>{{ priceFormate($total)}}</td>
                                            </tr>
                                            <tr class="order-shipping">
                                                <td>Shipping</td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="radio" id="radio1" name="shipping" checked>
                                                        <label for="radio1">Free Shippping</label>
                                                    </div>                                                   
                                                    
                                                </td>
                                            </tr>
                                            <tr class="order-tax">
                                                <td>State Tax</td>
                                                <td>$0.00</td>
                                            </tr>
                                            <tr class="order-total">
                                                <td>Total</td>
                                                <td class="order-total-amount">{{ priceFormate($total)}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="{{ route('front.checkouts.index')}}" class="axil-btn btn-bg-primary checkout-btn">Process to Checkout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         End Cart Area  