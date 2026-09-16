<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PageController extends Controller
{
    public function dashboard()
    {
        return view('admin.pages.dashboard');
    }

    public function datatable()
    {
        return view('admin.pages.datatable');
    }

    public function email()
    {
        return view('admin.pages.email');
    }

    public function compose()
    {
        return view('admin.pages.compose');
    }

    public function chat()
    {
        return view('admin.pages.chat');
    }

    public function calendar()
    {
        return view('admin.pages.calendar');
    }

    public function basicTable()
    {
        return view('admin.pages.basic-table');
    }

    public function buttons()
    {
        return view('admin.pages.buttons');
    }

    public function forms()
    {
        return view('admin.pages.forms');
    }

    public function charts()
    {
        return view('admin.pages.charts');
    }

    public function uiElements()
    {
        return view('admin.pages.ui');
    }

    public function vectorMaps()
    {
        return view('admin.pages.vector-maps');
    }

    public function googleMaps()
    {
        return view('admin.pages.google-maps');
    }

    public function blank()
    {
        return view('admin.pages.blank');
    }
}
