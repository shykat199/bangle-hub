@extends('frontend.app')
@section('content')


<style>
  a.axil-btn.btn-bg-primary::before, button.axil-btn.btn-bg-primary::before {
  background-color: var(--color-primary);
  background: #c2050b !important;
}
</style>
<main class="main-wrapper">
  <div class="cart_other_details">
    @include('frontend.cart.other_details')
  </div>
</main>    
@endsection