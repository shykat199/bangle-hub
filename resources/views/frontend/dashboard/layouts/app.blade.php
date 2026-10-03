@extends('frontend.app')
@section('content')

  <style>
    .account-menu{
    border-right: 1px solid rgba(128, 128, 128, 0.295);
    transition: 0.3s linear;
}
.account-menu .title{
    font-family: roboto;
    font-weight: bold;
}
.account-menu .navbar-nav{
  margin-left: 0;
}
.account-menu .navbar-nav .nav-item{
    padding: 20px;
}
.account-menu .navbar-nav .nav-item:hover{
    background: rgba(128, 128, 128, 0.178);
    transition: 0.3s;
}
.account-menu .navbar-nav .nav-item.active{
    background: rgba(128, 128, 128, 0.178);
    transition: 0.3s;
}
.account-menu .navbar-nav .nav-item .nav-link{
    border: none;
}
.account-menu .navbar-nav .nav-item:hover .nav-link{
    border: none;
}

.account-box{
    height: 15rem;
    background: rgb(243, 243, 243);
    border-radius: 20px;
    box-shadow: 0px 0px 8px #8080808c;
    transition: 0.5s;
    align-items: center;
    flex-direction: column;
}
.account-box:hover{
    background: rgb(223, 223, 223);
}
.account-box .icon{
    font-size: 60px;
}
.account-box p{
    text-transform: uppercase;
    font-family: hind-semi;
}
.menu-details #account-sidebar{
    position: fixed;
    top: 40%;
    left: 0;
    border-radius: 0;
    background: rgb(218, 218, 218);
    border: 0;
    color: black;
    z-index: 1080;
}

.profile-image-box{
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}
.profile-image{
  height: 100px;
  width: 100px;
  border-radius: 50px;
  overflow: hidden;
}

.dashboard-box{
  position: relative;
  background: whitesmoke;
  flex-direction: column;
  transition: 0.5s;
  border-radius: 10px;
  height: 8rem;
}
.dashboard-box:hover{
  position: relative;
  background: rgb(255, 255, 255);
  box-shadow: 0 0 8px rgba(128, 128, 128, 0.425);
  flex-direction: column;
  cursor: pointer;
}

.dashboard-box::before{
  content: "";
  position: absolute;
  width: 10px;
  height: 100%;
  border-radius: 10px 0 0 10px;
  left: 0;
  background: #00ffff21;
}
.dashboard-box::after{
  content: "";
  position: absolute;
  width: 10px;
  height: 100%;
  border-radius: 0 10px 10px 0;
  right: 0;
  background: #00ffff21;
}
  </style>
    <section>
      <div class="container-fluid mt-4">
          <div class="row">
              <div class="col-lg-3 col-md-4 col-10 account-menu p-3 d-md-block d-lg-block" id="account-sidemenu">
                <button class="dismiss btn btn-light d-md-none d-lg-none d-block me-auto text-end" id="account-dismiss">
                  <i class="fas fa-times"></i>
              </button>
                  <div class="dashboard-menu">
                    <div class="profile-image-box">
                      <div class="profile-image">
                        <img src="{{ asset('frontend/img/pic1.jpg')}}" alt="Profile Image" class="col-12">
                      </div>
                      <div class="text-center">
                        <p class="text-muted">Sontos Sharma</p>
                        <h5 class="text-muted"><b>Web Designer</b></h5>
                      </div>
                    </div>
                    <div class="d-flex justify-content-between mt-5">
                        <h5 class="title">My Account</h5>
                        
                    </div>
                    <hr>
                    <ul class="navbar-nav">
                      <li class="nav-item {{(request()->is('dashboard')) ? 'active' : ''}}"><a href="{{ url('dashboard')}}" class="nav-link"><b>Dashboard</b></a></li>
                      <li class="nav-item {{(request()->is('orders')) ? 'active' : ''}}"><a href="{{ url('orders')}}" class="nav-link"><b>Orders</b></a></li>
                      <li class="nav-item {{(request()->is('account_details')) ? 'active' : ''}}"><a href="{{ url('account_details')}}" class="nav-link"><b>Account Details</b></a></li>
                      <li class="nav-item {{(request()->is('wishlist')) ? 'active' : ''}}"><a href="{{ url('wishlist')}}" class="nav-link"><b>Wishlist</b></a></li>
                      <li class="nav-item"><a href="{{ route('logout')}}" class="nav-link"><b>Logout</b></a></li>
                  </ul>
                  </div>
              </div>
              <div class="col-lg-9 col-md-8 col-12 p-3 text-muted menu-details">
                  <button class="d-block d-md-none d-lg-none btn btn-secondary sidebar" id="account-sidebar">
                      <i class="fas fa-arrow-right"></i>
                  </button>
                  
                  @yield("dashboard-content")
              </div>

          </div>
      </div>
  </section>

    

@endsection