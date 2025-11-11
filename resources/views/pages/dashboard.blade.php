@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="card card-body py-3">
  <div class="row align-items-center">
    <div class="col-12">
      <div class="d-sm-flex align-items-center justify-space-between">
        <h4 class="mb-4 mb-sm-0 card-title">Dashboard</h4>
        <nav aria-label="breadcrumb" class="ms-auto">
          <ol class="breadcrumb">
            <li class="breadcrumb-item" aria-current="page">
              <span class="badge fw-medium fs-2 bg-primary-subtle text-primary">Dashboard</span>
            </li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Welcome to Starter Template</h5>
        <p class="card-text">This is your Laravel starter template with MatDash Bootstrap Admin theme.</p>
        <a href="{{ route('chat.index') }}" class="btn btn-primary">Go to Chat</a>
      </div>
    </div>
  </div>
</div>
@endsection
