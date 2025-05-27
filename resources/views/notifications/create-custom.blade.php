@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Kirim Notifikasi ke Email Custom') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('notifications.send-custom') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="emails" class="form-label">{{ __('Email Penerima') }}</label>
                            <input type="text" class="form-control @error('emails') is-invalid @enderror" 
                                   id="emails" name="emails" required value="{{ old('emails') }}"
                                   placeholder="email1@example.com, email2@example.com">
                            @error('emails')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <div class="form-text">Pisahkan beberapa email dengan tanda koma (,)</div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">{{ __('Judul Notifikasi') }}</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" required value="{{ old('title') }}">
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">{{ __('Isi Pesan') }}</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="4" required>{{ old('message') }}</textarea>
                            @error('message')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Kirim Notifikasi') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
