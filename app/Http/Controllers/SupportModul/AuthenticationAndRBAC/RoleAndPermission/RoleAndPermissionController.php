<?php

namespace App\Http\Controllers\SupportModul\AuthenticationAndRBAC\RoleAndPermission;

use App\Http\Controllers\Controller;
use App\Services\SupportModul\AuthenticationAndRBAC\RoleAndPermission\RoleAndPermissionService;
use App\Http\Requests\SupportModul\AuthenticationAndRBAC\RoleAndPermission\RoleAndPermissionRequest;
use App\Traits\ApiResponse;

class RoleAndPermissionController extends Controller
{
    use ApiResponse;

    protected $service;

    public function __construct(RoleAndPermissionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->getAll();
        return $this->successResponse($data, 'Data role dan permission berhasil diambil');
    }

    public function update(RoleAndPermissionRequest $request)
    {
        $data = $this->service->sync($request->validated());
        return $this->successResponse($data, 'Role dan permission berhasil diperbarui');
    }
}
