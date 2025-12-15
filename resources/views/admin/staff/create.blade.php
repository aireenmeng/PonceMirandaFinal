@extends('layouts.admin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Add New Doctor</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.staff.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Dr. John Doe">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <input type="hidden" name="role" value="doctor"> {{-- New staff are always doctors --}}
                        <div class="form-group col-md-12"> {{-- Changed to col-md-12 since role select is gone --}}
                            <label class="font-weight-bold">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Invite Doctor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection