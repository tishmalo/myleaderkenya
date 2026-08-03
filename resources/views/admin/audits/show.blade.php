@extends('layouts.app')
@section('page_title','Audit Details')
@section('content') @include('components.audit-detail',['back'=>route('audits.index'),'actor'=>$audit->user?->name ?? 'System']) @endsection
