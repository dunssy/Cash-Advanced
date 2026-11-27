@extends('errors.layout')

@section('title', '403 Forbidden')
@section('badge', 'Akses Dibatasi')
@section('code', '403')
@section('heading_text', 'Forbidden')
@section('message', 'Anda tidak memiliki izin untuk mengakses halaman ini.')
@section('image', asset('assets/images/errors/403.png'))
@section('image_alt', 'Ilustrasi rakun penjaga dengan perisai gembok di hutan malam')
