<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SmtpController extends Controller
{
    public function index()
    {
        return view('admin.smtp');
    }

    public function update(Request $request)
    {
        $request->validate([
            'mail_host'       => 'required',
            'mail_port'       => 'required',
            'mail_username'   => 'required',
            'mail_password'   => 'required',
            'mail_encryption' => 'nullable',
            'mail_from_address' => 'required|email',
            'mail_from_name'  => 'required',
        ]);

        $data = $request->except('_token');

        // Update .env file
        $path = base_path('.env');
        foreach ($data as $key => $value) {
            $envKey = strtoupper($key);
            $this->setEnvironmentValue($path, $envKey, $value);
        }

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return back()->with('success', 'SMTP settings updated successfully!');
    }

    private function setEnvironmentValue($path, $key, $value)
    {
        if (file_exists($path)) {
            $value = '"' . trim($value) . '"';
            if (preg_match("/^{$key}=.*/m", file_get_contents($path))) {
                file_put_contents($path, preg_replace("/^{$key}=.*/m", "{$key}={$value}", file_get_contents($path)));
            } else {
                file_put_contents($path, file_get_contents($path) . "\n{$key}={$value}");
            }
        }
    }
}