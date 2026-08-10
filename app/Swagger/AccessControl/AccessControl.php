<?php

namespace App\Swagger\AccessControl;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'RoleInput', required: ['name'], properties: [
    new OA\Property(property: 'name', type: 'string', example: 'sales_manager'),
    new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string'), example: ['ViewAny_Order', 'View_Order', 'Update_Order']),
])]
#[OA\Schema(schema: 'AdminUserInput', required: ['name', 'email', 'password', 'password_confirmation', 'role_id'], properties: [
    new OA\Property(property: 'name', type: 'string', example: 'Ada Manager'),
    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ada@example.com'),
    new OA\Property(property: 'password', type: 'string', format: 'password'),
    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
    new OA\Property(property: 'role_id', type: 'integer'),
])]
#[OA\Get(path: '/permissions', tags: ['Access Control'], security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Permission catalog'), new OA\Response(response: 403, description: 'Forbidden')])]
#[OA\Get(path: '/roles', tags: ['Access Control'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', maximum: 100))], responses: [new OA\Response(response: 200, description: 'Paginated roles')])]
#[OA\Post(path: '/roles', tags: ['Access Control'], security: [['sanctum' => []]], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/RoleInput')), responses: [new OA\Response(response: 201, description: 'Role created'), new OA\Response(response: 422, description: 'Validation error')])]
#[OA\Get(path: '/roles/{role}', tags: ['Access Control'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'Role details')])]
#[OA\Patch(path: '/roles/{role}', tags: ['Access Control'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: '#/components/schemas/RoleInput')), responses: [new OA\Response(response: 200, description: 'Role updated'), new OA\Response(response: 409, description: 'Protected system role')])]
#[OA\Put(path: '/roles/{role}/permissions', tags: ['Access Control'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['permissions'], properties: [new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string'))])), responses: [new OA\Response(response: 200, description: 'Permissions replaced')])]
#[OA\Delete(path: '/roles/{role}', tags: ['Access Control'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'role', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'Role deleted'), new OA\Response(response: 409, description: 'System or assigned role')])]
#[OA\Get(path: '/users', tags: ['Access Control'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'search', in: 'query', schema: new OA\Schema(type: 'string')), new OA\Parameter(name: 'role_id', in: 'query', schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', maximum: 100))], responses: [new OA\Response(response: 200, description: 'Paginated admin users')])]
#[OA\Post(path: '/users', tags: ['Access Control'], security: [['sanctum' => []]], requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/AdminUserInput')), responses: [new OA\Response(response: 201, description: 'User created'), new OA\Response(response: 403, description: 'Cannot assign super_admin')])]
#[OA\Get(path: '/users/{user}', tags: ['Access Control'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'User details')])]
#[OA\Patch(path: '/users/{user}', tags: ['Access Control'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], requestBody: new OA\RequestBody(content: new OA\JsonContent(ref: '#/components/schemas/AdminUserInput')), responses: [new OA\Response(response: 200, description: 'User updated'), new OA\Response(response: 409, description: 'Self or last-super-admin conflict')])]
#[OA\Delete(path: '/users/{user}', tags: ['Access Control'], security: [['sanctum' => []]], parameters: [new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'User deleted'), new OA\Response(response: 409, description: 'Self or last-super-admin conflict')])]
class AccessControl {}
