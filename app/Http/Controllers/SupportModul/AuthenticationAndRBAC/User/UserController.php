<?php

namespace App\Http\Controllers\SupportModul\AuthenticationAndRBAC\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SupportModul\AuthenticationAndRBAC\User\UserService;
use App\Http\Requests\SupportModul\AuthenticationAndRBAC\User\UserRequest;
use App\Traits\ApiResponse;

class UserController extends Controller
{
    use ApiResponse;

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'role', 'sort', 'direction']);
        $users = $this->userService->getAll($filters, $request->input('per_page', 10));
        return $this->paginatedResponse($users, 'Data user berhasil diambil');
    }

    public function store(UserRequest $request)
    {
        $user = $this->userService->create($request->validated());
        return $this->successResponse($user, 'User berhasil ditambahkan', 201);
    }

    public function show($id)
    {
        $user = $this->userService->getOne($id);
        if (!$user) {
            return $this->errorResponse('User tidak ditemukan', 404);
        }
        return $this->successResponse($user, 'Data user berhasil diambil');
    }

    public function update(UserRequest $request, $id)
    {
        $user = $this->userService->update($id, $request->validated());
        if (!$user) {
            return $this->errorResponse('User tidak ditemukan', 404);
        }
        return $this->successResponse($user, 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $deleted = $this->userService->delete($id);
        if (!$deleted) {
            return $this->errorResponse('User tidak ditemukan', 404);
        }
        return $this->successResponse(null, 'User berhasil dihapus');
    }
}
