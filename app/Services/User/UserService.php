<?php

namespace App\Services\User;

use App\Contracts\User\UserServiceInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService implements UserServiceInterface
{
    public function getAllUsers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->when(isset($filters['search']), function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            })
            ->paginate($perPage);
    }

    public function createUser(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ];

        $user = User::create($userData);

        if (!empty($data['roles'])) {
            $roles = Role::whereIn('name', $data['roles'])->get();
            $user->syncRoles($roles);
        } else {
            $defaultUserRole = Role::firstWhere('name', 'user');
            if ($defaultUserRole) {
                $user->assignRole($defaultUserRole);
            }
        }

        return $user;
    }

    public function getUserById(int $userId): ?User
    {
        return User::find($userId);
    }

    public function updateUser(User $user, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);

        if (isset($data['roles'])) { 
            $roles = Role::whereIn('name', $data['roles'])->get();
            $user->syncRoles($roles);
        }
        return $user->fresh();
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }
}