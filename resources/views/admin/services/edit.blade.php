@extends('layouts.admin')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">Edit Service</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Update Details</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.services.update', $service->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Service Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $service->name }}" required>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Price (₱)</label>
                        <input type="number" name="price" class="form-control" step="0.01" value="{{ $service->price }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Duration (Minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="{{ $service->duration_minutes }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $service->description }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Service</button>
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection