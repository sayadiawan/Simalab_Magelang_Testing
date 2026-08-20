@extends('masterweb::template.admin.layout')
@section('title')
    Customer Management
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
        <h4 class="card-title">Customer Management</h4>
        
        <form enctype="multipart/form-data" class="forms-sample" action="{{route('elits-customers.update', [$id])}}" method="POST">
            @csrf
            <input type="hidden" value="PUT" name="_method">

            <div class="form-group">
                
                <label for="name_customer">Nama</label>
                <input type="text" class="form-control" id="name_customer" name="name_customer" value="{{$customer->name_customer}}" placeholder="Isikan Nama" >
            </div>

            <div class="form-group">
                
                <label for="address_customer">Alamat</label>
                <textarea class="form-control" id="address_customer" name="address_customer" placeholder="Isikan Alamat" >{{$customer->address_customer}}</textarea>
            </div>

            <div class="form-group">
                <label for="email_customer">Email</label>
                <input type="email" class="form-control" id="email_customer" name="email_customer" value="{{$customer->email_customer}}" placeholder="Isikan Email" >
            </div>

            <div class="form-group">
                <label for="category_customer">Kategori</label>
                <select name="category_customer" class="form-control" id="category_customer" >
                    <option value="">Pilih Category</option>
                    @foreach ($categories as $category)
                        <option value="{{$category->id_industry}}" {{($category->id_industry==$customer->category_customer)?"selected":""}}>{{$category->name_industry}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="cp_customer">Contact Person</label>
                <textarea class="form-control" id="cp_customer" name="cp_customer" placeholder="Isikan Contact Person" >{{$customer->cp_customer}}</textarea>
            </div>

            
            <button type="submit" class="btn btn-primary mr-2">Simpan</button>
            <button onclick="goBack()" class="btn btn-light">Kembali</button>
        </form>
        </div>
    </div>
    <script>
    function goBack() {
          window.history.back();
    }
    </script>
@endsection