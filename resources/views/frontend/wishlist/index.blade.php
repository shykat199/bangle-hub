@extends('frontend.app')
@section('content')
<main class="main-wrapper">
<!-- Start Wishlist Area  -->
<div class="axil-wishlist-area axil-section-gap">
    <div class="container">
        <div class="product-table-heading">
            <h4 class="title">My Wish List on eTrade</h4>
        </div>
        <div class="table-responsive">
            <table class="table axil-product-table axil-wishlist-table">
                <thead>
                    <tr>
                        <th scope="col" class="product-remove"></th>
                        <th scope="col" class="product-thumbnail">Product</th>
                        <th scope="col" class="product-title"></th>
                        <th scope="col" class="product-price">Unit Price</th>
                        <th scope="col" class="product-stock-status">Stock Status</th>
                        <th scope="col" class="product-add-cart"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="product-remove"><a href="#" class="remove-wishlist"><i class="fal fa-times"></i></a></td>
                        <td class="product-thumbnail"><a href="single-product.php"><img src="./assets/images/product/electric/product-01.png" alt="Digital Product"></a></td>
                        <td class="product-title"><a href="single-product.php">Wireless PS Handler</a></td>
                        <td class="product-price" data-title="Price"><span class="currency-symbol">$</span>124.00</td>
                        <td class="product-stock-status" data-title="Status">In Stock</td>
                        <td class="product-add-cart"><a href="cart.php" class="axil-btn btn-outline">Add to Cart</a></td>
                    </tr>
                    <tr>
                        <td class="product-remove"><a href="#" class="remove-wishlist"><i class="fal fa-times"></i></a></td>
                        <td class="product-thumbnail"><a href="single-product-2.php"><img src="./assets/images/product/electric/product-02.png" alt="Digital Product"></a></td>
                        <td class="product-title"><a href="single-product-2.php">Gradient Light Keyboard</a></td>
                        <td class="product-price" data-title="Price"><span class="currency-symbol">$</span>124.00</td>
                        <td class="product-stock-status" data-title="Status">In Stock</td>
                        <td class="product-add-cart"><a href="cart.php" class="axil-btn btn-outline">Add to Cart</a></td>
                    </tr>
                    <tr>
                        <td class="product-remove"><a href="#" class="remove-wishlist"><i class="fal fa-times"></i></a></td>
                        <td class="product-thumbnail"><a href="single-product-3.php"><img src="./assets/images/product/electric/product-03.png" alt="Digital Product"></a></td>
                        <td class="product-title"><a href="single-product-3.php">HD CC Camera</a></td>
                        <td class="product-price" data-title="Price"><span class="currency-symbol">$</span>124.00</td>
                        <td class="product-stock-status" data-title="Status">In Stock</td>
                        <td class="product-add-cart"><a href="cart.php" class="axil-btn btn-outline">Add to Cart</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- End Wishlist Area  -->
</main>


<div class="service-area">
<div class="container">
    <div class="row row-cols-xl-4 row-cols-sm-2 row-cols-1 row--20">
        <div class="col">
            <div class="service-box service-style-2">
                <div class="icon">
                    <img src="./assets/images/icons/service1.png" alt="Service">
                </div>
                <div class="content">
                    <h6 class="title">Fast &amp; Secure Delivery</h6>
                    <p>Tell about your service.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="service-box service-style-2">
                <div class="icon">
                    <img src="./assets/images/icons/service2.png" alt="Service">
                </div>
                <div class="content">
                    <h6 class="title">Money Back Guarantee</h6>
                    <p>Within 10 days.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="service-box service-style-2">
                <div class="icon">
                    <img src="./assets/images/icons/service3.png" alt="Service">
                </div>
                <div class="content">
                    <h6 class="title">24 Hour Return Policy</h6>
                    <p>No question ask.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="service-box service-style-2">
                <div class="icon">
                    <img src="./assets/images/icons/service4.png" alt="Service">
                </div>
                <div class="content">
                    <h6 class="title">Pro Quality Support</h6>
                    <p>24/7 Live support.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection