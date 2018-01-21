@extends('admin/layout')

@section('head')
    @parent
    {{ Rapyd::head() }}
    <style>
        .btn-toolbar h2 {
            margin-top: 0px;
        }
    </style>
@show

@section('content')
    <div class="row">
        <div class="col-md-6">
            <br/>
            {{ $edit->header }}
            <div class="well">

                    {{ $edit->message }}

                    @if(!$edit->message)
                        Name: {{ $edit->field('name') }}
                        <p class="bg-danger">{{ $edit->field('name')->message }}</p>
                    @endif
                    {{ $edit->footer }}
            </div>
        </div>
    </div>
@stop