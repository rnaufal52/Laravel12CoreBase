<?php

namespace App\Services\SupportModul\AuthenticationAndRBAC\User;

use Illuminate\Support\Facades\Auth;
use App\Models\Support\Modul\AuthenticationAndRBAC\User;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Support\Facades\Cache;

class UserService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $users =Cache::remember('users', 60 * 60, function () use ($filters, $perPage) {
            $users = User::with(['roles:id,name'])->filter($filters)->paginate($perPage);
            $users->getCollection()->transform(function ($user) {
                $user->roles->makeHidden('pivot');
                return $user;
            });
            return $users;
        });
        return $users;
    }

    public function getOne($id)
    {
        $user = User::with(['roles:id,name'])->find($id);
        if ($user) {
            $user->roles->makeHidden('pivot');
        }
        return $user;
    }

    public function create($data)
    {
        $role = $data['role'];
        if($role == 'super-admin') {
            throw new UnauthorizedException(403, 'Role super-admin tidak dapat ditambahkan melalui API.');
        }
        unset($data['role']);
        
        $user = User::create($data);
        $user->assignRole($role);
        
        return $user;
    }

    public function update($id, $data)
    {
        $user = User::find($id);
        
        if (!$user) {
            return null;
        }
        
        if ($user->hasRole('super-admin') && !Auth::user()->hasRole('super-admin')) {
            throw new UnauthorizedException(403, 'Anda tidak memiliki akses untuk mengubah data super-admin.');
        }

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $user->update($data);
        return $user;
    }

    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return null;
        }

        if ($user->hasRole('super-admin') && !Auth::user()->hasRole('super-admin')) {
            throw new UnauthorizedException(403, 'Anda tidak memiliki akses untuk menghapus super-admin.');
        }

        return $user->delete();
    }
}