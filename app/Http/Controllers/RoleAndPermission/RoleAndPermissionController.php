<?php

namespace App\Http\Controllers\RoleAndPermission;

use App\Http\Controllers\Controller;
use App\Services\RoleAndPermission\RoleAndPermissionService;
use App\Http\Requests\RoleAndPermission\RoleAndPermissionRequest;

class RoleAndPermissionController extends Controller
{
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
