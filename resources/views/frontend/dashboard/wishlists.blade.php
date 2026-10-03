@extends('frontend.dashboard.layouts.app')
@section('dashboard-content')

                  <div class="row">
                      <div class="table-responsive ">
                        <table class="table">
                          <thead>
                            <tr>
                              <th scope="col">SL No.</th>
                              <th scope="col">Products</th>
                              <th scope="col">Price</th>
                              <th scope="col">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr class="">
                              <td scope="row">1</td>
                              <td>
                                <div class="d-flex align-item-center" style="overflow: hidden;">
                                  <div class="image pe-2">
                                    <img src="{{ asset('frontend/img/product1.jpg') }}" alt="" width="60px">
                                  </div>
                                    <div>
                                        <a href="#" class="product-text text-muted text-decoration-none">
                                            Floral Off white Pajama set</p>
                                            <button class="btn btn-light"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                              </td>
                              <td>
                                <p class="text-dark p-0" style="font-size: 15px; font-weight: bold;">$210</p>
                                <p class="text-info p-0" style="font-size: 12px;"><del class="text-muted">$700</del>-71%</p>
                                <p class="text-primary p-0" style="font-size: 12px;">Price Droped</p>
                              </td>
                              <td><button class="btn btn-warning" title="Add to cart"><i class="fas fa-shopping-cart"></i> <span class="ps-1 d-none d-lg-block d-md-block">Add To Cart</span></button></td>
                            </tr>
                            <tr class="">
                              <td scope="row">2</td>
                              <td>
                                <div class="d-flex align-item-center" style="overflow: hidden;">
                                  <div class="image pe-2">
                                    <img src="{{ asset('frontend/img/product1.jpg') }}" alt="" width="60px">
                                  </div>
                                    <div>
                                        <a href="#" class="product-text text-muted text-decoration-none">
                                            Floral Off white Pajama set</p>
                                            <button class="btn btn-light"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                              </td>
                              <td>
                                <p class="text-dark p-0" style="font-size: 15px; font-weight: bold;">$210</p>
                                <p class="text-info p-0" style="font-size: 12px;"><del class="text-muted">$700</del>-71%</p>
                                <p class="text-primary p-0" style="font-size: 12px;">Price Droped</p>
                              </td>
                              <td><button class="btn btn-warning" title="Add to cart"><i class="fas fa-shopping-cart"></i> <span class="ps-1 d-none d-lg-block d-md-block">Add To Cart</span></button></td>
                            </tr>
                            <tr class="">
                              <td scope="row">3</td>
                              <td>
                                <div class="d-flex align-item-center" style="overflow: hidden;">
                                  <div class="image pe-2">
                                    <img src="{{ asset('frontend/img/product1.jpg') }}" alt="" width="60px">
                                  </div>
                                    <div>
                                        <a href="#" class="product-text text-muted text-decoration-none">
                                            Floral Off white Pajama set</p>
                                            <button class="btn btn-light"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                              </td>
                              <td>
                                <p class="text-dark p-0" style="font-size: 15px; font-weight: bold;">$210</p>
                                <p class="text-info p-0" style="font-size: 12px;"><del class="text-muted">$700</del>-71%</p>
                                <p class="text-primary p-0" style="font-size: 12px;">Price Droped</p>
                              </td>
                              <td><button class="btn btn-warning" title="Add to cart"><i class="fas fa-shopping-cart"></i> <span class="ps-1 d-none d-lg-block d-md-block">Add To Cart</span></button></td>
                            </tr>
                            <tr class="">
                              <td scope="row">4</td>
                              <td>
                                <div class="d-flex align-item-center" style="overflow: hidden;">
                                  <div class="image pe-2">
                                    <img src="{{ asset('frontend/img/product1.jpg') }}" alt="" width="60px">
                                  </div>
                                    <div>
                                        <a href="#" class="product-text text-muted text-decoration-none">
                                            Floral Off white Pajama set</p>
                                            <button class="btn btn-light"><i class="fas fa-trash-alt"></i></button>
                                    </div>
                                </div>
                              </td>
                              <td>
                                <p class="text-dark p-0" style="font-size: 15px; font-weight: bold;">$210</p>
                                <p class="text-info p-0" style="font-size: 12px;"><del class="text-muted">$700</del>-71%</p>
                                <p class="text-primary p-0" style="font-size: 12px;">Price Droped</p>
                              </td>
                              <td><button class="btn btn-warning" title="Add to cart"><i class="fas fa-shopping-cart"></i> <span class="ps-1 d-none d-lg-block d-md-block">Add To Cart</span></button></td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      
                  </div>



@endsection