@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Notification Settings') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('users.update-notifications') }}">
                        @csrf
                        @method('PATCH')

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="notify_on_document_upload" 
                                    name="notify_on_document_upload" {{ Auth::user()->notify_on_document_upload ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_on_document_upload">
                                    {{ __('Notify me when documents are uploaded') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="notify_on_document_update" 
                                    name="notify_on_document_update" {{ Auth::user()->notify_on_document_update ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_on_document_update">
                                    {{ __('Notify me when documents are updated') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="notify_on_document_delete" 
                                    name="notify_on_document_delete" {{ Auth::user()->notify_on_document_delete ? 'checked' : '' }}>
                                <label class="form-check-label" for="notify_on_document_delete">
                                    {{ __('Notify me when documents are deleted') }}
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Save Preferences') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
