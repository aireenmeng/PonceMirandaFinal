@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-4 text-gray-800">Edit Patient</h1>
<div class="row justify-content-center">
    <div class="card shadow mb-4 col-md-6">
        <div class="card-body">
            <form action="{{ route('admin.patients.update', $patient->id) }}" method="POST">
                @csrf @method('PUT')
                <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="{{ $patient->name }}"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="{{ $patient->email }}"></div>
                <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control" value="{{ $patient->phone }}"></div>
                <div class="form-group"><label>Address</label><textarea name="address" class="form-control">{{ $patient->address }}</textarea></div>
                <button class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection