@extends('frontend.app')
@section('content')

<div class="main-wrapper container-fluid">
  <div class="main-wrapper">
    <link rel="stylesheet" href="frontend/css/career.css">
    <section>
        <div class="career-banner" style="background-image: url({{ getImage('career','0906_hp_lux_takeover_c06_img1666696656.jpg')}});">
            <h2 class="title"> Career Page</h2>
        </div>
    </section>
    <br><br>
    <section>
        <div class="container mt-5">
            <div class="career mb-5">
                @foreach($items as $item)
                <div class="row career-row mb-5">
                    <div class="col-lg-5 col-md-5 col-12 m-auto career-item">
                        <img src="{{ getImage('career',$item->image)}}" alt="" width="500">
                    </div>
                    <div class="col-lg-7 col-md-7 col-12 m-auto ps-5 align-items-start career-item">
                        <h2 class="text-cap mb-5">
                            {{$item->title}}
                        </h2>
                        <p>{{$item->description}}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <br><br>
    </section>
  </div>
  @endsection