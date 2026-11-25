<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainMarkupSetting;
use Illuminate\Http\Request;

class DomainMarkupController extends Controller
{
    public function edit()
    {
        $markup = DomainMarkupSetting::firstOrCreate(
            [],
            ['default_markup_percent' => 40]
        );

        return view('admin.domain-markup.edit', compact('markup'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'default_markup_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $markup = DomainMarkupSetting::firstOrCreate([], ['default_markup_percent' => 40]);
        $markup->update($data);

        return redirect()->route('admin.domain-markup.edit')
            ->with('status', 'Default domain markup updated.');
    }
}
