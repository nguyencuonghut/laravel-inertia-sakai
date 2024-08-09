<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get()->map(function ($user) {
            return collect($user)->only(['id', 'name', 'email', 'status']);
        });

        return Inertia::render('Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'status' => 'required',
            'password' => 'required|confirmed|min:6',
        ];

        $messages = [
            'name.required' => 'Bạn phải nhập tên.',
            'email.required' => 'Bạn phải nhập email.',
            'email.unique' => 'Email bị trùng với người dùng khác.',
            'status.required' => 'Bạn phải nhập trạng thái',
            'password.required' => 'Bạn phải nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải dài ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu không khớp.',
        ];

        $request->validate($rules, $messages);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->status = $request->status;
        $user->password = bcrypt($request->password);
        $user->save();

        return redirect('/users');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$id,
            'status' => 'required',
        ];

        $messages = [
            'name.required' => 'Bạn phải nhập tên.',
            'email.required' => 'Bạn phải nhập email.',
            'email.unique' => 'Email bị trùng với người dùng khác.',
            'status.required' => 'Bạn phải nhập trạng thái',
        ];

        $request->validate($rules, $messages);

        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->status = $request->status;
        $user->save();

        return redirect('/users');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        $user->destroy($id);
        return redirect('/users');
    }

    public function bulkDelete(Request $request)
    {
        $users = $request->users;
        foreach ($users as $user) {
            $deleted_user = User::findOrFail($user['id']);
            if ($deleted_user) {
                $deleted_user->destroy($deleted_user->id);
            }
        }

        return redirect('/users');
    }
}
