@extends('masterweb::template.admin.layout')

@section('title')
    Contact
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
        <h4 class="card-title">Contact</h4>
        <p class="card-description">
            edit contact
        </p>
        @if(session('status'))
        <div class="alert alert-success">
            {{session('status')}}
        </div>
    @endif

        @foreach ($contacts as $contact)

        <form enctype="multipart/form-data" class="forms-sample" action="{{url('contact/'. $contact->id)}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                <label for="nama">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="Input Nama" value="{{$contact->nama}}">
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input type="text" class="form-control" id="alamat" name="alamat" placeholder="Input Alamat" value="{{$contact->alamat}}">
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="sevenmediatech@gmail.com" value="{{$contact->email}}">
            </div>
            
            <div class="form-group">
            <label for="phone"></label>
                <input type="text" class="form-control" id="phone" name="phone" placeholder="081xxx" value="{{$contact->phone}}">
            </div>
            
            <div class="form-group">
                <label for="other">Other</label>
            <textarea name="other" id="other" class="texteditor form-control" cols="30" rows="10">{{$contact->other}}</textarea>
            </div>

            @endforeach

            <button type="submit" class="btn btn-primary mr-2">Simpan</button>
            <button class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>
@endsection
